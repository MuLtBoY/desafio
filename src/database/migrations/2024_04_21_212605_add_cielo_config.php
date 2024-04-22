<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddCieloConfig extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('gateway_config', function (Blueprint $table) {
            DB::statement('INSERT INTO `gateway_config`(`gateway_flag`, `key`, `value`) VALUES 
                ("gateway_cielo","merchant_id","3eab5d44-7e8b-4205-9827-8fc556c42fe1"),
                ("gateway_cielo","merchant_key","BSKLPSOSMTLRZFCUWTWIWHCMPDOPAUFFAIPUMVIZ")
            ');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('gateway_config', function (Blueprint $table) {
            DB::statement('DELETE FROM `gateway_config` WHERE `gateway_flag` =  "gateway_cielo" AND `key` IN ("merchant_id","merchant_key");');
        });
    }
}
