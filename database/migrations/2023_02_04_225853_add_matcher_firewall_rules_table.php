<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('firewall_rules', function (Blueprint $table) {
            $table->string('hash')->nullable();
            $table->text('business_purpose')->nullable()->change();
            $table->json('destination')->change();
            $table->json('destination_port')->change();
            $table->json('source')->change();
        });
    }

    public function down()
    {
        Schema::table('firewall_rules', function (Blueprint $table) {
            $table->dropColumn('hash');
            $table->string('business_purpose')->nullable()->change();
        });
    }
};
