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
        Schema::create('courriers_entrants', function (Blueprint $table) {
            $table->id();
            $table->string('nim')->unique(); // ARR-YYYY-XXXXX
            $table->string('provenance'); // Expéditeur externe
            $table->foreignId('destinataire_service_id')->constrained('services')->onDelete('cascade');
            $table->foreignId('destinataire_user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->enum('type_courrier', ['ordinaire', 'urgent', 'confidentiel', 'secret_defense'])->default('ordinaire');
            $table->string('personne_apporteur');
            $table->dateTime('date_arrivee');
            $table->text('qr_code')->nullable(); // Contenu du QR
            $table->string('qr_code_hash')->nullable(); // Hash pour vérification
            $table->enum('statut', ['enregistre', 'transmis', 'recu', 'en_retard', 'non_retire'])->default('enregistre');
            $table->enum('niveau_confidentialite', ['ordinaire', 'urgent', 'confidentiel', 'secret_defense'])->default('ordinaire');
            $table->string('fichier_joint')->nullable(); // Chemin du fichier
            $table->text('observations')->nullable();
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->timestamps();
            
            // Index pour améliorer les performances
            $table->index('nim');
            $table->index('statut');
            $table->index('date_arrivee');
            $table->index('niveau_confidentialite');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('courriers_entrants');
    }
};
