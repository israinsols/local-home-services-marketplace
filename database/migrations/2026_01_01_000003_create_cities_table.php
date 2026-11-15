<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('cities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('country_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('slug');
            $table->string('region')->nullable();
            $table->string('postal_prefix', 16)->nullable();
            $table->unsignedInteger('population')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->unique(['country_id', 'slug']);
            $table->index(['country_id', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cities');
    }
};
