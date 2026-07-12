<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chats', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable()->index();
            $table->string('title')->default('New Chat');
            $table->text('summary')->nullable(); // rolling conversation summary = cheap AI memory
            $table->string('model')->nullable();
            $table->unsignedInteger('messages_count')->default(0);
            $table->unsignedInteger('total_tokens')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chats');
    }
};
