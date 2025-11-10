<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('http_endpoint_configurations', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('http_endpoint_id')->constrained()->onDelete('cascade');
            $table->foreignId('http_credential_id')->constrained()->onDelete('cascade');
            $table->text('configuration');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('http_endpoint_configurations');
    }
};
