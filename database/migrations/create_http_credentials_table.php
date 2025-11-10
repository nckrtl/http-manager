<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('http_credentials', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('http_provider_id')->constrained()->onDelete('cascade');
            $table->json('config');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('http_credentials');
    }
};
