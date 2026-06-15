<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('email_verifications', function (Blueprint $table) {
            $table->id('id_verifikasi');
            $table->unsignedBigInteger('id_user');
            $table->string('email');
            $table->string('token', 64);
            $table->timestamp('expires_at');
            $table->timestamps();

            $table->index('token');
            $table->index('email');
            $table->foreign('id_user')->references('id_user')->on('users')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('email_verifications');
    }
};
