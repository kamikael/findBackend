<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('mobile_money_providers', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // MTN, Moov, Celtis
            $table->string('api_base_url')->nullable();
            $table->string('code')->unique();
            $table->string('country_iso', 3);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mobile_money_providers');
    }
};