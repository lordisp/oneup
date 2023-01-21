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
        Schema::create('roles_groups', function (Blueprint $table) {
            $table->uuid('role_id')->nullable(false);
            $table->uuid('group_id')->nullable(false);
            $table->timestamps();

            $table->primary(['group_id', 'role_id']);

            $table->foreignUuid('role_id')
                ->constrained('roles')
                ->onDelete('cascade');

            $table->foreignUuid('group_id')
                ->constrained('groups')
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
        Schema::dropIfExists('roles_groups');
    }
};
