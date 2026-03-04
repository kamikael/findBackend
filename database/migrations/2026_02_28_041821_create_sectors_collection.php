<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use MongoDB\Laravel\Schema\Blueprint;

return new class extends Migration {
    public function up(): void
    {
        Schema::connection('mongodb')->create('sectors', function (Blueprint $collection) {
            $collection->index('name');

            $collection->string('name');
            $collection->text('description')->nullable();
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