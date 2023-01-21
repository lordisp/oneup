<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create('roles_groups', function (Blueprint $table) {
            $table->foreignUuid('role_id')
                ->constrained('roles')
                ->cascadeOnDelete();

            $table->foreignUuid('group_id')
                ->constrained('groups')
                ->cascadeOnDelete();

            $table->primary(['group_id', 'role_id']);
            $table->timestamps();
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
