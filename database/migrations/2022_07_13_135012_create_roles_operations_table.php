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
        Schema::create('roles_operations', function (Blueprint $table) {
            $table->uuid('role_id')->nullable(false);
            $table->uuid('operation_id')->nullable(false);
            $table->timestamps();

            $table->primary(['role_id', 'operation_id']);

            $table->foreign('role_id')
                ->references('id')->on('roles')
                ->onDelete('cascade');

            $table->foreign('operation_id')
                ->references('id')->on('operations')
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
        Schema::dropIfExists('roles_operations');
    }
};
