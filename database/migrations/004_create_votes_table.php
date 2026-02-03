<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('votes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('poll_id')->constrained()->onDelete('cascade');
            $table->foreignId('option_id')->constrained('poll_options')->onDelete('cascade');
            $table->string('ip_address', 45);
            $table->boolean('is_released')->default(false);
            $table->timestamps();
            
            // Index for fast IP lookup per poll
            $table->index(['poll_id', 'ip_address']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('votes');
    }
};
