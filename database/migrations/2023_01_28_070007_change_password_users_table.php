<?php

use App\Models\User;
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
        $password = Hash::make(md5(config('app.key')));
        Schema::table('users', function (Blueprint $table) use ($password) {
            $table->string('password')->default($password)->change();
        });
        User::query()->update(['password' => $password]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        $password = Hash::make(now());
        Schema::table('users', function (Blueprint $table) use ($password) {
            $table->string('password')->default($password)->change();
        });
        User::query()->update(['password' => $password]);
    }
};
