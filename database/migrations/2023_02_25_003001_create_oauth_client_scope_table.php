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
        Schema::create('oauth_client_scope', function (Blueprint $table) {
            $table->uuid('oauth_client_id');
            $table->uuid('scope_id');
            $table->timestamp('approved_at')->nullable();
            $table->string('approved_by')->nullable();
            $table->primary(['oauth_client_id', 'scope_id']);

            $table->foreign('oauth_client_id')
                ->references('id')
                ->on('oauth_clients')
                ->onDelete('cascade');

            $table->foreign('scope_id')
                ->references('id')
                ->on('scopes')
                ->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('oauth_client_scope');
    }
};
