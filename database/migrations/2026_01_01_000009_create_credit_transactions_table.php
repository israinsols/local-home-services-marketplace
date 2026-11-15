<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('credit_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('provider_profile_id')->constrained()->cascadeOnDelete();
            $table->integer('amount');
            $table->unsignedInteger('balance_after');
            $table->string('type');
            $table->string('description');
            $table->string('reference')->nullable()->unique();
            $table->foreignId('lead_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();
            $table->index(['provider_profile_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('credit_transactions');
    }
};
