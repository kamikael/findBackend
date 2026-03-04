<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use MongoDB\Laravel\Schema\Blueprint;

return new class extends Migration {
    public function up(): void
    {
        Schema::connection('mongodb')->create('payments', function (Blueprint $collection) {
            $collection->index('candidature_id');
            $collection->index('transaction_id');
            $collection->index('status');

            $collection->foreignId('candidature_id');

            $collection->integer('amount');

            $collection->string('status')->default('initiated'); // initiated, paid, failed

            $collection->string('transaction_id')->nullable();

            $collection->timestamps();
        });
    }

    public function down(): void
    {
        Schema::connection('mongodb')->dropIfExists('payments');
    }
};