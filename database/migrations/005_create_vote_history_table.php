<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vote_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('poll_id')->constrained()->onDelete('cascade');
            $table->unsignedBigInteger('option_id');
            $table->string('ip_address', 45);
            $table->enum('action', ['voted', 'released']);
            $table->timestamps();
            
            $table->index(['poll_id', 'ip_address']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vote_history');
    }
};
