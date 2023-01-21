<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create('groups_users', function (Blueprint $table) {
            $table->uuid('group_id')->nullable(false);
            $table->uuid('user_id')->nullable(false);
            $table->timestamps();

            $table->primary(['group_id', 'user_id']);

            $table->foreignUuid('group_id')
                ->constrained('groups')
                ->onDelete('cascade');

            $table->foreignUuid('user_id')
                ->constrained('users')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('groups_users');
    }
};
