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
        Schema::table('users', function (Blueprint $table) {
            $table->bigInteger('id_time_token')->unsigned()->after('remenber_token');
            $table->bigInteger('id_expiry_month')->unsigned()->after('id_time_token');

            $table->foreign('id_time_token')->references('id_time_token')->on('time_tokens');
            $table->foreign('id_expiry_month')->references('id_expiry_month')->on('expiry_months');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            //
        });
    }
};
