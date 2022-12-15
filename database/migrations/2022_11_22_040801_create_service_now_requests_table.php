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
    public function up()
    {
        Schema::create('service_now_requests', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('template');
            $table->text('description');
            $table->string('requestor_mail');
            $table->string('requestor_name');
            $table->string('ritm_number');
            $table->string('subject');
            $table->string('opened_by');
            $table->boolean('status')->default(false);
            $table->string('slug')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('service_now_requests');
    }
};
