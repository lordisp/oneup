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
        Schema::table('users', function (Blueprint $table) {
            $table->uuid('provider_id')->index()->nullable()->unique();
            $table->string('provider')->nullable();
            $table->string('displayName')->nullable();
            $table->string('avatar')->nullable();
            $table->string('password')->default(Hash::make(now()))->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('provider_id');
            $table->dropColumn('provider');
            $table->dropColumn('displayName');
            $table->dropColumn('avatar');
            $table->string('password')->change();
        });
    }
};
