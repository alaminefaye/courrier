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
        Schema::create('courrier_receptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('courrier_entrant_id')->nullable()->constrained('courriers_entrants')->onDelete('cascade');
            $table->foreignId('courrier_sortant_id')->nullable()->constrained('courriers_sortants')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade'); // Qui a reçu
            $table->enum('signature_type', ['qr_scan', 'signature_numerique'])->default('qr_scan');
            $table->text('signature_data')->nullable(); // Hash ou données signature
            $table->dateTime('date_reception');
            $table->string('ip_address')->nullable();
            $table->text('commentaire')->nullable();
            $table->timestamps();
            
            $table->index('courrier_entrant_id');
            $table->index('courrier_sortant_id');
            $table->index('date_reception');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('courrier_receptions');
    }
};
