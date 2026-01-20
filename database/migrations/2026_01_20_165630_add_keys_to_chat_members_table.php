<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('chat_members', function (Blueprint $table) {
            $table->foreign(['chat_id'], 'chat_member1')->references(['id'])->on('chats')->onDelete('restrict')->onUpdate('restrict');
            $table->foreign(['user_id'], 'chat_member2')->references(['id'])->on('users')->onDelete('restrict')->onUpdate('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('chat_members', function (Blueprint $table) {
            $table->dropForeign('chat_member1');
            $table->dropForeign('chat_member2');
        });
    }
};
