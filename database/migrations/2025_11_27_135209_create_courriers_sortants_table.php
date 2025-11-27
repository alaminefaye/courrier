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
        Schema::create('courriers_sortants', function (Blueprint $table) {
            $table->id();
            $table->string('nim')->unique(); // DEP-YYYY-XXXXX
            $table->string('destinataire_externe'); // Destinataire externe
            $table->foreignId('provenance_service_id')->constrained('services')->onDelete('cascade');
            $table->foreignId('provenance_user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->enum('type_courrier', ['ordinaire', 'urgent', 'confidentiel', 'secret_defense'])->default('ordinaire');
            $table->string('personne_livreur');
            $table->dateTime('date_depart');
            $table->text('qr_code')->nullable();
            $table->string('qr_code_hash')->nullable();
            $table->enum('statut', ['enregistre', 'transmis', 'livre', 'confirme'])->default('enregistre');
            $table->enum('niveau_confidentialite', ['ordinaire', 'urgent', 'confidentiel', 'secret_defense'])->default('ordinaire');
            $table->string('fichier_joint')->nullable();
            $table->text('observations')->nullable();
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->timestamps();
            
            // Index pour améliorer les performances
            $table->index('nim');
            $table->index('statut');
            $table->index('date_depart');
            $table->index('niveau_confidentialite');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('courriers_sortants');
    }
};
