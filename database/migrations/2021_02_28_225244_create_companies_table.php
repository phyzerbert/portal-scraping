<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCompaniesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('companies', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('username')->nullable();
            $table->string('name')->nullable();
            $table->string('slug')->nullable();
            $table->string('email')->nullable();
            $table->string('password')->nullable();
            $table->text('avatar_image')->nullable();
            $table->text('web_url')->nullable();
            $table->integer('role_id')->nullable();
            $table->longtext('description')->nullable();
            $table->string('address')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('postal')->nullable();
            $table->string('timezone')->nullable();
            $table->string('phone_number')->nullable();
            $table->string('package_level')->nullable();
            $table->string('latitude')->nullable();
            $table->string('longitude')->nullable();
            $table->string('website_url')->nullable();
            $table->string('facebook_url')->nullable();
            $table->string('twitter_url')->nullable();
            $table->string('instagram_url')->nullable();
            $table->string('yourube_url')->nullable();
            $table->string('suite')->nullable();
            $table->integer('store_type')->nullable();
            $table->boolean('recreational')->nullable();
            $table->boolean('medical')->nullable();
            $table->boolean('atm')->nullable();
            $table->boolean('security')->nullable();
            $table->string('state_license')->nullable();
            $table->string('mon_open')->nullable();
            $table->string('mon_close')->nullable();
            $table->integer('mon_closed')->nullable();
            $table->string('tue_open')->nullable();
            $table->string('tue_close')->nullable();
            $table->integer('tue_closed')->nullable();
            $table->string('wed_open')->nullable();
            $table->string('wed_close')->nullable();
            $table->integer('wed_closed')->nullable();
            $table->string('thu_open')->nullable();
            $table->string('thu_close')->nullable();
            $table->integer('thu_closed')->nullable();
            $table->string('fri_open')->nullable();
            $table->string('fri_close')->nullable();
            $table->integer('fri_closed')->nullable();
            $table->string('sat_open')->nullable();
            $table->string('sat_close')->nullable();
            $table->integer('sat_closed')->nullable();
            $table->string('sun_open')->nullable();
            $table->string('sun_close')->nullable();
            $table->integer('sun_closed')->nullable();
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
        Schema::dropIfExists('companies');
    }
}
