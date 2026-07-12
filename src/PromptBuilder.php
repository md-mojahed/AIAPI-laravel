<?php

namespace Mojahed\AIAPI;

class PromptBuilder
{
    protected string $behaviour = '';
    protected string $knowledge = '';
    protected string $summary   = '';

    public function setBehaviour(string $behaviour): static
    {
        $this->behaviour = trim($behaviour);
        return $this;
    }

    public function setKnowledge(string|array $knowledge): static
    {
        if (is_array($knowledge)) {
            $parts = [];
            foreach ($knowledge as $item) {
                $parts[] = $this->resolveKnowledge($item);
            }
            $this->knowledge = implode("\n\n", array_filter($parts));
        } else {
            $this->knowledge = $this->resolveKnowledge($knowledge);
        }
        return $this;
    }

    public function setSummary(string $summary): static
    {
        $this->summary = trim($summary);
        return $this;
    }

    /**
     * Build the messages array to send to the provider.
     * Returns OpenAI-style messages array.
     */
    public function build(string $question): array
    {
        $system = $this->buildSystemPrompt();
        $user   = $this->buildUserPrompt($question);

        return [
            ['role' => 'system', 'content' => $system],
            ['role' => 'user',   'content' => $user],
        ];
    }

    /**
     * Build system prompt combining behaviour + knowledge + summary + JSON instruction.
     */
    protected function buildSystemPrompt(): string
    {
        $parts = [];

        // 1. JSON output instruction (always — for answer + summary)
        $parts[] = implode("\n", [
            'You MUST respond with a valid JSON object only. No extra text, no markdown, no code fences.',
            'The JSON must have exactly these two keys:',
            '{"answer": "your answer here", "summary": "updated summary of entire conversation so far in 2 sentences max"}',
            'The summary should absorb previous context and include this exchange.',
        ]);

        // 2. Behaviour
        if ($this->behaviour !== '') {
            $parts[] = $this->behaviour;
        }

        // 3. Knowledge
        if ($this->knowledge !== '') {
            $parts[] = "--- KNOWLEDGE BASE ---\n" . $this->knowledge . "\n--- END KNOWLEDGE BASE ---";
            $parts[] = "Answer questions using ONLY the knowledge base above. If the answer is not found in the knowledge base, say \"I don't have information about that.\"";
        }

        // 4. Previous conversation summary
        if ($this->summary !== '') {
            $parts[] = "--- CONVERSATION CONTEXT ---\n" . $this->summary . "\n--- END CONTEXT ---";
        }

        return implode("\n\n", $parts);
    }

    /**
     * Build user prompt — just the question.
     */
    protected function buildUserPrompt(string $question): string
    {
        return $question;
    }

    /**
     * Resolve knowledge — accepts file path or raw string.
     */
    protected function resolveKnowledge(string $item): string
    {
        $item = trim($item);

        // Treat as a file path only if it points to an existing file
        if (is_file($item)) {
            $contents = file_get_contents($item);

            return $contents === false ? '' : $contents;
        }

        // Raw string
        return $item;
    }
}
