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
        Schema::create('event', function (Blueprint $table) {
            $table->bigIncrements('id_event');
            $table->string('code_event', 20)->nullable();
            $table->string('name_event', 50);
            $table->string('type_event', 20);
            $table->text('place_event');
            $table->text('location_event');
            $table->timestamp("start_event");
            $table->timestamp("end_event");
            $table->text("information_event")->nullable();
            $table->string("image_event")->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('event');
    }
};
