<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('campus_import_errors', function (Blueprint $table) {
            $table->id();

            $table->foreignId('campus_import_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->unsignedInteger('row_number');

            $table->text('error_message');

            $table->json('row_data')->nullable();

            $table->timestamps();

            $table->index('row_number');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('campus_import_errors');
    }
};