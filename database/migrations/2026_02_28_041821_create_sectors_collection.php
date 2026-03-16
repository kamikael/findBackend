<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use MongoDB\Laravel\Schema\Blueprint;

return new class extends Migration {
    public function up(): void
    {
        Schema::connection('mongodb')->create('sectors', function (Blueprint $collection) {
            $collection->index('name');
            $collection->index('domain_id');
            $collection->index('status');
            $collection->index('level');

            $collection->string('name');
            $collection->text('description')->nullable();
            $collection->string('domain_id');
            $collection->string('status')->default('available');
            $collection->string('level')->default('license');
            $collection->integer('total_slots')->default(0);
            $collection->integer('available_slots')->default(0);

            $collection->timestamps();
        });
    }

    

    public function down(): void
    {
        Schema::connection('mongodb')->dropIfExists('sectors');
    }
};
