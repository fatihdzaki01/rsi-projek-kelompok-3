<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('laporan_campaign', function (Blueprint $table) {
            $table->id('id_laporan');
            $table->unsignedBigInteger('id_campaign')->nullable();
            $table->unsignedBigInteger('id_update')->nullable();
            $table->unsignedBigInteger('id_user')->nullable();
            $table->string('guest_ip', 45)->nullable();
            $table->string('alasan_laporan', 255);
            $table->text('deskripsi_laporan')->nullable();
            $table->string('status', 50)->default('menunggu_review');
            $table->timestamps();

            $table->foreign('id_campaign')->references('id_campaign')->on('campaign')->onDelete('cascade');
            $table->foreign('id_user')->references('id_user')->on('users')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('laporan_campaign');
    }
};
