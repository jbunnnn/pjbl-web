<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('campuses', function (Blueprint $table) {
            $table->id();

            // Identitas dari API eksternal
            $table->string('external_id')->nullable()->unique();

            // api atau manual
            $table->string('source', 20)->default('manual');

            // Informasi kampus
            $table->string('name');
            $table->string('short_name')->nullable();

            // Kategori kampus
            $table->string('institution_type', 50)->nullable();
            $table->string('institution_group', 20)->nullable();

            // Informasi wilayah
            $table->string('province_code', 10)->nullable();
            $table->string('province_name')->nullable();
            $table->string('regency_code', 10)->nullable();
            $table->string('regency_name')->nullable();

            // Informasi tambahan
            $table->text('address')->nullable();
            $table->string('postal_code', 10)->nullable();
            $table->string('website')->nullable();
            $table->string('accreditation')->nullable();

            $table->boolean('is_active')->default(true);
            $table->timestamp('last_synced_at')->nullable();

            $table->timestamps();

            // Index untuk pencarian dan analisis grafik
            $table->index(['institution_group', 'province_code']);
            $table->index(['institution_type', 'province_code']);
            $table->index(['province_code', 'regency_code']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('campuses');
    }
};