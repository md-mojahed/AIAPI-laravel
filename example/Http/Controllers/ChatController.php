<?php

namespace App\Http\Controllers;

use App\Models\Chat;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Mojahed\AIAPI\AI;

class ChatController extends Controller
{
    /**
     * Create a new chat session.
     */
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'title'   => ['nullable', 'string', 'max:120'],
            'user_id' => ['nullable', 'integer'],
        ]);

        $chat = Chat::create([
            'title'   => $data['title'] ?? 'New Chat',
            'user_id' => $data['user_id'] ?? null,
            'model'   => config('aiapi.model'),
        ]);

        return response()->json($chat, Response::HTTP_CREATED);
    }

    /**
     * Send a message to an existing chat.
     *
     * Optimization: only the rolling {summary} is sent to the LLM, NOT the
     * full message history. The full history stays in the database for the UI.
     * This keeps token cost roughly flat per turn instead of growing with the
     * length of the conversation.
     */
    public function ask(Request $request, Chat $chat): JsonResponse
    {
        $data = $request->validate([
            'message' => ['required', 'string', 'max:8000'],
        ]);

        $wasFirstMessage = ! $chat->messages()->exists();

        // 1. Persist the user's message immediately (even if the AI call fails).
        $userMessage = $chat->messages()->create([
            'role'    => 'user',
            'content' => $data['message'],
        ]);

        // 2. Call the AI with the stored summary as memory.
        $ai = (new AI())
            ->setSummary((string) $chat->summary)
            ->setBehaviour(config('aiapi.behaviour'));

        // Optional RAG: inject a knowledge base per chat.
        // ->setKnowledge(resource_path('docs/knowledge.md'))

        try {
            $response = $ai->ask($data['message']);
        } catch (RequestException $e) {
            return response()->json([
                'error'  => 'The AI service is unavailable right now. Please try again.',
                'status' => $e->response?->status(),
            ], Response::HTTP_SERVICE_UNAVAILABLE);
        }

        // 3. Persist the assistant reply.
        $assistantMessage = $chat->messages()->create([
            'role'    => 'assistant',
            'content' => $response->answer,
            'model'   => $response->model,
            'tokens'  => $response->tokensUsed,
        ]);

        // 4. Roll the summary forward — this is the cheap long-term memory.
        $chat->update([
            'summary'        => $response->summary,
            'messages_count' => $chat->messages()->count(),
            'title'          => $wasFirstMessage
                ? $this->deriveTitle($data['message'])
                : $chat->title,
        ]);

        $chat->increment('total_tokens', $response->tokensUsed);

        return response()->json([
            'answer'            => $response->answer,
            'summary'           => $response->summary,
            'tokens_used'       => $response->tokensUsed,
            'total_tokens'      => $chat->total_tokens,
            'user_message'      => $userMessage,
            'assistant_message' => $assistantMessage,
        ]);
    }

    /**
     * Full history of a chat (for rendering the UI).
     */
    public function history(Chat $chat): JsonResponse
    {
        return response()->json(
            $chat->messages()->oldest()->get(['id', 'role', 'content', 'tokens', 'created_at'])
        );
    }

    /**
     * Delete a chat (cascades to its messages).
     */
    public function destroy(Chat $chat): Response
    {
        $chat->delete();

        return response()->noContent();
    }

    private function deriveTitle(string $message): string
    {
        return mb_strlen($message) > 60
            ? trim(mb_substr($message, 0, 60)) . '...'
            : trim($message);
    }
}
