<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('logs', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->timestamp('timestamp')->useCurrent();
            $table->string('action');
            $table->string('category');
            $table->string('severity')->default('info');
            $table->text('message')->nullable();
            $table->text('description')->nullable();
            $table->string('entity_type')->nullable();
            $table->string('entity_id')->nullable();
            $table->string('user_id')->nullable();
            $table->json('changes')->nullable();
            $table->json('metadata')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('logs');
    }
};
