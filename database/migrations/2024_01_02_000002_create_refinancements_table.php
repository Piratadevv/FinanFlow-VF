<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('refinancements', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('libelle', 255);
            $table->decimal('montant_refinance', 12, 2);
            $table->decimal('taux_interet', 5, 2);
            $table->date('date_refinancement');
            $table->integer('duree_en_mois');
            $table->decimal('encours_refinance', 12, 2);
            $table->decimal('frais_dossier', 12, 2)->nullable()->default(0);
            $table->text('conditions')->nullable();
            $table->enum('statut', ['ACTIF', 'TERMINE', 'SUSPENDU'])->default('ACTIF');
            $table->decimal('total_interets', 12, 2)->default(0);
            $table->integer('ordre_saisie')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('refinancements');
    }
};
