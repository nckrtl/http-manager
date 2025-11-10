<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('http_providers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('base_url');
            $table->json('credential_config');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('http_providers');
    }
};
