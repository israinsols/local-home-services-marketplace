<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('lead_unlocks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lead_id')->constrained()->cascadeOnDelete();
            $table->foreignId('provider_profile_id')->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('credits_spent');
            $table->timestamps();
            $table->unique(['lead_id', 'provider_profile_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lead_unlocks');
    }
};
