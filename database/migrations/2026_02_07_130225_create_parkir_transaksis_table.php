<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('parkir_transaksis', function (Blueprint $t) {
            $t->id();
            $t->string('card_id');
            $t->timestamp('checkin_time')->nullable();
            $t->timestamp('checkout_time')->nullable();
            $t->integer('duration')->nullable();
            $t->integer('fee')->nullable();
            $t->enum('status',['IN','OUT','DONE']);
            $t->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('parkir_transaksis');
    }
};
