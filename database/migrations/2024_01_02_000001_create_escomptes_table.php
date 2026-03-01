<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('escomptes', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('numero_effet')->nullable();
            $table->string('nom_tireur')->nullable();
            $table->date('date_remise');
            $table->string('libelle', 255);
            $table->decimal('montant', 12, 2);
            $table->decimal('taux_escompte', 5, 2)->nullable();
            $table->decimal('frais_commission', 12, 2)->nullable();
            $table->decimal('montant_net', 12, 2)->nullable();
            $table->enum('statut', ['ACTIF', 'TERMINE', 'SUSPENDU'])->default('ACTIF');
            $table->integer('ordre_saisie')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('escomptes');
    }
};
