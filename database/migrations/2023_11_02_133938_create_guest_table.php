<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('guest', function (Blueprint $table) {
            $table->bigIncrements('id_guest');
            $table->string("name_guest", 60);
            $table->string("address_guest", 100)->nullable();
            $table->string("information_guest", 150)->nullable();
            $table->string("email_guest", 60);
            $table->string("phone_guest", 25);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('guest');
    }
};
