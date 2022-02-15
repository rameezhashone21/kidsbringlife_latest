<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateParticipantGuardiansTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('participant_guardians', function (Blueprint $table) {
            $table->id();
            $table->string('participant_id');
            $table->string('guardian_firstname');
            $table->string('guardian_lastname');
            $table->string('guardian_role');
            $table->text('guardian_address');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('participant_guardians');
    }
}
