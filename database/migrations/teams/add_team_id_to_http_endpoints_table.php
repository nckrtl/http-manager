<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('http_endpoints', function (Blueprint $table) {
            $table->unsignedBigInteger('team_id')->nullable()->after('id');
            $table->foreign('team_id')->references('id')->on('teams')->onDelete('cascade');
            $table->index('team_id');
        });
    }

    public function down(): void
    {
        Schema::table('http_endpoints', function (Blueprint $table) {
            $table->dropForeign(['team_id']);
            $table->dropIndex(['team_id']);
            $table->dropColumn('team_id');
        });
    }
};
