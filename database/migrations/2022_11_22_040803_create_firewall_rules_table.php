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
        Schema::create('firewall_rules', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('action');
            $table->string('type_destination')->nullable();
            $table->text('destination');
            $table->string('type_source')->nullable();
            $table->text('source');
            $table->string('service');
            $table->text('destination_port');
            $table->text('description');
            $table->string('no_expiry')->nullable();
            $table->date('end_date')->nullable();
            $table->boolean('pci_dss')->default(false);
            $table->string('nat_required')->nullable();
            $table->string('application_id')->nullable();
            $table->string('contact')->nullable();
            $table->string('business_purpose')->nullable();
            $table->string('status'); // 'open, review, extended, deleted'
            $table->string('business_service')->nullable();
            $table->timestamp('last_review')->nullable();
            $table->timestamps();

            $table->foreignUuid('service_now_request_id')
                ->constrained('service_now_requests')
                ->onUpdate('cascade')
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
        Schema::dropIfExists('firewall_rules');
    }
};
