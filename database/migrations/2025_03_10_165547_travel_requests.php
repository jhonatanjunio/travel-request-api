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
        Schema::create('travel_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('destination');
            $table->date('departure_date');
            $table->date('return_date');            
            $table->enum('status', ['requested', 'approved', 'canceled', 'rejected', 'pending_cancellation', 'awaiting_cancellation_confirmation'])->default('requested');
            $table->text('cancellation_reason')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->timestamp('cancellation_requested_at')->nullable();
            $table->timestamp('cancellation_confirmed_at')->nullable();
            $table->timestamp('cancellation_rejected_at')->nullable();
            $table->string('cancellation_token')->nullable()->unique();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('travel_requests');
    }
};