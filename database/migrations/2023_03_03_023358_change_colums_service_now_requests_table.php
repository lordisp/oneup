<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('service_now_requests', function (Blueprint $table) {
            $table->dropColumn('template');
        });
        Schema::table('service_now_requests', function (Blueprint $table) {
            $table->string('cost_center')->nullable();
        });
        Schema::table('service_now_requests', function (Blueprint $table) {
            $table->dropColumn('status');
        });
    }

    public function down(): void
    {
        Schema::table('service_now_requests', function (Blueprint $table) {
            $table->string('template');
            $table->dropColumn('cost_center');
            $table->boolean('status')->default(false);
        });
    }
};
