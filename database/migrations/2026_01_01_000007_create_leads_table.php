<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('leads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained()->restrictOnDelete();
            $table->foreignId('city_id')->constrained()->restrictOnDelete();
            $table->string('name');
            $table->string('email');
            $table->string('phone', 32);
            $table->string('postal_code', 16);
            $table->text('details');
            $table->string('timeline')->nullable();
            $table->string('budget')->nullable();
            $table->boolean('marketing_consent')->default(false);
            $table->timestamp('consent_at');
            $table->string('consent_version', 24)->default('2026-01');
            $table->string('status')->default('new')->index();
            $table->timestamps();
            $table->index(['category_id', 'city_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('leads');
    }
};
