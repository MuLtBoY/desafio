<?php

namespace multboy\desafio;

use Throwable;
use Illuminate\Support\Facades\Log;

abstract class GatewaysAbstract
{
    /**
     * Flag gateway name
     */
    protected static $gatewayFlag;

    /**
     * Payment status code
     */
    const CODE_NOTFINISHED  =   0;
    const CODE_AUTHORIZED   =   1;
    const CODE_CONFIRMED    =   2;
    const CODE_DENIED       =   3;
    const CODE_VOIDED       =   10;
    const CODE_REFUNDED     =   11;
    const CODE_PENDING      =   12;
    const CODE_ABORTED      =   13;
    const CODE_SCHEDULED    =   20;

    /**
     * Payment status string
     */
    const PAYMENT_NOTFINISHED  =   'not_finished';
    const PAYMENT_AUTHORIZED   =   'authorized';
    const PAYMENT_CONFIRMED    =   'paid';
    const PAYMENT_DENIED       =   'denied';
    const PAYMENT_VOIDED       =   'voided';
    const PAYMENT_REFUNDED     =   'refunded';
    const PAYMENT_PENDING      =   'pending';
    const PAYMENT_ABORTED      =   'aborted';
    const PAYMENT_SCHEDULED    =   'scheduled';

    protected function detectCardType($num)
    {
        $re = array(
            "visa" => "/^4[0-9]{12}(?:[0-9]{3})?$/",
            "mastercard" => "/^(5[1-5][0-9]{14}|2(22[1-9][0-9]{12}|2[3-9][0-9]{13}|[3-6][0-9]{14}|7[0-1][0-9]{13}|720[0-9]{12}))$/",
            "amex" => "/^3[47][0-9]{13}$/",
            "discover" => "/^6(?:011|5[0-9]{2})[0-9]{12}$/",
            "dinersclub" => "/^3(?:0[0-5]|[68][0-9])[0-9]{11}$/",
            "jcb" => "/^(?:2131|1800|35\d{3})\d{11}$/",
        );

        if (preg_match($re['visa'], $num)) {
            return 'VISA';
        } else if (preg_match($re['mastercard'], $num)) {
            return 'MASTERCARD';
        } else if (preg_match($re['amex'], $num)) {
            return 'AMERICAN EXPRESS';
        } else if (preg_match($re['discover'], $num)) {
            return 'DISCOVER';
        } else if (preg_match($re['dinersclub'], $num)) {
            return 'DINERS CLUB';
        } else if (preg_match($re['jcb'], $num)) {
            return 'JCB';
        } else {
            return 'Unknown';
        }
    }

    /**
     * Logging the fails details
     *
     * @param Throwable     $th     Object of exception
     * 
     * @return void
     */
    protected function logFails(Throwable $th): void
    {
        Log::error([
            'message'   =>  $th->getMessage(),
            'line'      =>  $th->getLine(),
            'file'      =>  $th->getFile(),
        ]);
    }

    /**
     * Mounts response
     *
     * @param 
     * 
     * @return Array ['success', ..., 'gateway_flag']
     */
    public function gatewayResponse(bool $success, array $middleParamns): array
    {
        return ['success' => $success] + $middleParamns + ['gateway_flag' => $this->gatewayFlag];
    }

    /**
     * Returns status string based on status code
     *
     * @param Integer        $statusCode    Payment status code captured on gateway.
     *
     * @return String                       String related to the payment status code.
     *
     */
    protected function getStatusString($statusCode)
    {
        switch ($statusCode) {
            case self::CODE_NOTFINISHED:
                return self::PAYMENT_NOTFINISHED;
            case self::CODE_AUTHORIZED:
                return self::PAYMENT_AUTHORIZED;
            case self::CODE_CONFIRMED:
                return self::PAYMENT_CONFIRMED;
            case self::CODE_DENIED:
                return self::PAYMENT_DENIED;
            case self::CODE_VOIDED:
                return self::PAYMENT_VOIDED;
            case self::CODE_REFUNDED:
                return self::PAYMENT_REFUNDED;
            case self::CODE_PENDING:
                return self::PAYMENT_PENDING;
            case self::CODE_ABORTED:
                return self::PAYMENT_ABORTED;
            case self::CODE_SCHEDULED:
                return self::PAYMENT_SCHEDULED;
            default:
                return 'not_geted';
        }
    }
}