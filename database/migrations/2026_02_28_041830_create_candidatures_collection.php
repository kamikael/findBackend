<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use MongoDB\Laravel\Schema\Blueprint;

return new class extends Migration {
    public function up(): void
    {
        Schema::connection('mongodb')->create('candidatures', function (Blueprint $collection) {
            // Relations
            $collection->index('sector_id');
            $collection->index('payment_id');
            $collection->index('status');
            $collection->index('level');

            // Champs
            $collection->foreignId('sector_id');

            $collection->string('level'); // Licence | Master

            $collection->string('student_name');
            $collection->string('student_email');
            $collection->string('student_cv_url');

            $collection->string('partner_name')->nullable();
            $collection->string('partner_email')->nullable();
            $collection->string('partner_cv_url')->nullable();

            $collection->string('status')->default('pending'); // pending, paid, cancelled

            $collection->foreignId('payment_id')->nullable();

            $collection->timestamps();
        });
    }

    public function down(): void
    {
        Schema::connection('mongodb')->dropIfExists('candidatures');
    }
};