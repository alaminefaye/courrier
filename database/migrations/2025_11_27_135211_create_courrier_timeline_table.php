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
        Schema::create('courrier_timeline', function (Blueprint $table) {
            $table->id();
            $table->morphs('courrier'); // courrier_id et courrier_type (entrant/sortant)
            $table->string('action'); // 'enregistre', 'transmis', 'recu', etc.
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->text('details')->nullable(); // Détails supplémentaires
            $table->string('ip_address')->nullable();
            $table->timestamp('created_at');
            
            $table->index(['courrier_id', 'courrier_type']);
            $table->index('action');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('courrier_timeline');
    }
};
