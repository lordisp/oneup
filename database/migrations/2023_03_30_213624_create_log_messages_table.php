<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('log_messages', function (Blueprint $table) {
            $table->id();
            $table->string('level_name');
            $table->smallInteger('level');
            $table->string('message');
            $table->dateTime('logged_at');
            $table->json('context');
            $table->json('extra');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('log_messages');
    }
};
