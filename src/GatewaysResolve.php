<?php

namespace multboy\desafio;

use Exception;
use ReflectionClass;
use Throwable;

use Illuminate\Support\Facades\Log;

use multboy\desafio\models\GatewayConfig;

class GatewaysResolve
{
    /**
     * Defines if run in sandbox
     *
     * @var bool
     */
    private static $devMode;

    /**
     * Allows to persist the token.
     *
     * @var string
     */
    private static $token;

    /**
     * Flags with gateways name
     */
    const GATEWAY_CIELO = 'gateway_cielo';

    /**
     * Sets a default gateway instance based on the parameter.
     *
     * @param String           $currentGateway     String that represents the gateway name flag.
     * @param Bool             $devMode            Boolean that defines if run in sandbox.
     * @param null|String      $token              String that allows to persist the token.
     * 
     * @return Object          GatewaysInterface
     */
    public static function resolveCurrent(string $currentGateway, bool $devMode = false, string $token = null)
    {
        try
        {
            self::$devMode = $devMode;
            self::$token = $token;

            switch($currentGateway)
            {
                case self::GATEWAY_CIELO:
                    return new GatewayCielo(self::GATEWAY_CIELO, self::$devMode, self::$token);
                default:
                    throw new Exception('Invalid gateway. Try it: ' . implode(', ',(new ReflectionClass(__CLASS__))->getConstants()));
            }

        } catch (Throwable $th) {
            Log::error([
                'message' => $th->getMessage(),
                'line' => $th->getLine(),
                'file' => $th->getFile(),
            ]);
        }
    }

    /**
     * Sets a default gateway instance based on the parameter.
     *
     * @param String           $gatewayFlag        String that represents the gateway name flag.
     * @param String           $devMode            String that represents the gateway set key.
     * @param String           $token              String that represents the gateway set value.
     * 
     * @return void
     */
    public static function updateGatewayConfig(string $gatewayFlag, string $key, string $value)
    {
        try
        {
            GatewayConfig::updateOrCreate(
                ['gateway_flag' => $gatewayFlag, 'key' => $key],['value' => $value]
            );
        } catch (Throwable $th) {
            Log::error([
                'message' => $th->getMessage(),
                'line' => $th->getLine(),
                'file' => $th->getFile(),
            ]);
        }
    }
}
