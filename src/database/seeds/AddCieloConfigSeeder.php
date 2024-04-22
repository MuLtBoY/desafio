
<?php

use Illuminate\Database\Seeder;
use multboy\desafio\models\GatewayConfig;

class AddCieloConfigSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        GatewayConfig::updateOrCreate(array(
            'gateway_flag'  => 'gateway_cielo',
            'key'           => 'merchant_id', 
            'value'         => '3eab5d44-7e8b-4205-9827-8fc556c42fe1', 
        ));

        GatewayConfig::updateOrCreate(array(
            'gateway_flag'  => 'gateway_cielo',
            'key'           => 'merchant_key', 
            'value'         => 'BSKLPSOSMTLRZFCUWTWIWHCMPDOPAUFFAIPUMVIZ', 
        ));
    }
}