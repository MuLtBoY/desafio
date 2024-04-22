<?php

namespace multboy\desafio\models;

use \Illuminate\Database\Eloquent\Model as Eloquent;

class GatewayConfig extends Eloquent
{
    /**
	 * The database table used on this model.
	 *
	 * @var string
	 */
    protected $table = 'gateway_config';

    /**
	 * The fillable columns used on this model.
	 *
	 * @var array
	 */
    protected $fillable = [
        "gateway_flag",
        "key",
        "value"
    ];

    /**
     * Gets the first line where key value equals key parameter
     *
     * @param  String   $key            Strings with the name of the key to be searched.
     * @param  String   $gatewayFlag    Strings with the gateway flag that are related to the key.
     * 
     * @return Object   GatewayConfig
     */
    public static function getByKey(string $key, string $gatewayFlag)
    {
        return self::whereKey($key)->firstOrFail();
    }
}