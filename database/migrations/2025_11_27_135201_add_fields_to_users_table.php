<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->enum('role', ['admin', 'agent_courrier', 'chef_service', 'directeur'])->default('agent_courrier')->after('email');
            $table->unsignedBigInteger('service_id')->nullable()->after('role');
            $table->unsignedBigInteger('direction_id')->nullable()->after('service_id');
            $table->json('permissions')->nullable()->after('direction_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['role', 'service_id', 'direction_id', 'permissions']);
        });
    }
};
