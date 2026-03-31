<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('travel_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('destination');
            $table->date('departure_date');
            $table->date('return_date');
            $table->enum('status', [
                'requested',
                'approved',
                'canceled',
                'awaiting_cancellation_confirmation',
                'pending_cancellation',
            ])->default('requested');
            $table->text('cancellation_reason')->nullable();
            $table->string('cancellation_token')->nullable()->unique();
            $table->timestamp('cancellation_requested_at')->nullable();
            $table->timestamp('cancellation_confirmed_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('travel_requests');
    }
};
