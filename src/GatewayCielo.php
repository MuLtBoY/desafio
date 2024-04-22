<?php

namespace multboy\desafio;

use Exception;

use Ramsey\Uuid\Uuid;
use Cielo\API30\Merchant;
use Cielo\API30\Ecommerce\Sale;
use Cielo\API30\Ecommerce\Payment;
use Cielo\API30\Ecommerce\CreditCard;
use Cielo\API30\Ecommerce\Environment;
use Cielo\API30\Ecommerce\CieloEcommerce;

use multboy\desafio\models\GatewayConfig;

class GatewayCielo extends GatewaysAbstract implements GatewaysInterface
{
    /**
     * Cielo configs and objects
     */
    private $merchantId;
    private $merchantKey;
    private $environment;
    private $cieloEcommerce;

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
     * Starts that's object populating properties and defining Environment.
     *
     * @param String        $gatewayFlag        String that represents the gateway name flag.
     * @param Bool          $devMode            Boolean that defines if run in sandbox.
     * @param null|String   $token              String that allows to persist the token.
     * 
     * @return void
     */
    public function __construct(string $gatewayFlag, bool $devMode, string $token = null)
    {
        try
        {
            self::$gatewayFlag = $gatewayFlag;

            if(!$devMode)
                $this->environment = Environment::production();
            else
                $this->environment = Environment::sandbox();
        } catch (Throwable $th) {
            $this->logFails($th);
        }
    }

    /**
     * Authenticates on the gateway before transactions
     * 
     * @return Array ['success', 'status', 'token', 'gateway_flag']
     */
    public function auth(): array
    {
        $success = true;

        try 
        {
            $this->merchantId    =   GatewayConfig::getByKey('merchant_id', self::$gatewayFlag);
            $this->merchantKey   =   GatewayConfig::getByKey('merchant_key', self::$gatewayFlag);
            if(!$this->merchantId || !$this->merchantKey)
                throw new Exception('Auth gateway ' . self::$gatewayFlag . ' fail');

            $merchant = new Merchant($this->merchantId->value, $this->merchantKey->value);
            if(!$merchant)
                throw new Exception('Auth gateway ' . self::$gatewayFlag . ' fail');

            $this->cieloEcommerce = new CieloEcommerce($merchant, $this->environment);
            if(!$this->cieloEcommerce)
                throw new Exception('Auth gateway ' . self::$gatewayFlag . ' fail');

        } catch (Throwable $th) {
            $success = false;
            $this->logFails($th);
        }

        return $this->gatewayResponse($success, ['token' => null]);
	}

    /**
     * Tokenizes a card
     *
     * @param String        $cardNumber         String that represents the number card.
     * @param String        $expirationMonth    String that represents the month of expiration date.
     * @param String        $expirationYear     String that represents the year of expiration date.
     * @param String        $cvc                String that represents security number cvc.
     * @param String        $holderName         String that represents the name of holder card.
     *
     * @return Array       ['success', 'card_token', 'card_type', 'gateway_flag']
     */
    public function cardCreate(string $cardNumber, string $expirationMonth, string $expirationYear, string $cvc, string $holderName): array
    {
        $this->auth();

        try
        {
            $cardType = $this->detectCardType($cardNumber);
            $cardType = $cardType == "mastercard" ? 'master' : $cardType;

            if($expirationYear / 100 < 1)
                $expirationYear += 2000;

            $card = new CreditCard();
            $card->setCustomerName($holderName);
            $card->setCardNumber($cardNumber);
            $card->setHolder($holderName);
            $card->setExpirationDate(
                str_pad($expirationMonth,2,'0',STR_PAD_LEFT) . "/" . $expirationYear
            );
            $card->setBrand(
                $cardType
            );

            $tokenizeCard = $this->cieloEcommerce->tokenizeCard($card);

            $cardToken = $tokenizeCard->getCardToken();

            $success = isset($cardToken);

        } catch (Throwable $th) {
            $success = false;
            $this->logFails($th);
        }

        return $this->gatewayResponse(
            $success, ['card_token' => $cardToken, 'card_type' => $cardType]
        );
    }

    /**
     * Creates a transaction authorized or capture
     *
     * @param Float         $amount         Float that represents amount for the transaction.
     * @param String        $cardType       String that represents card brand.
     * @param String        $cardToken      String that represents card tokenized on gateway valt.
     * @param String        $cvc            String that represents security number cvc.
     * @param Boolean       $capture        Boolean that represents config to capture on charge.
     *
     * @return Array       ['success', 'transaction_id', 'status', 'gateway_flag']
     */
    public function cardCharge(float $amount, string $cardType, string $cardToken, string $cvv, bool $capture = false): array
    {
        $this->auth();
        $transactionId = $captureStatus = null;
        $chargeStatus  = self::PAYMENT_VOIDED;

        try
        {
            $amount = floor($amount * 100);

            // Creates a instance of Sale, using uuid to genetare a identification.
            $sale = new Sale(Uuid::uuid4()->toString());

            $payment = $sale->payment($amount);

            $payment->setType(Payment::PAYMENTTYPE_CREDITCARD)
                ->creditCard($cvv, $cardType)
                ->setCardToken($cardToken);

            $sale = $this->cieloEcommerce->createSale($sale);

            $transactionId  =   $sale->getPayment()->getPaymentId();
            $chargeStatus   =   $sale->getPayment()->getStatus();

            if($chargeStatus == self::CODE_AUTHORIZED && $capture == true){
                $sale           =   $this->cieloEcommerce->captureSale($transactionId, $amount, 0);
                $captureStatus  =   $sale->getStatus();
                $captureStatus  =   $this->getStatusString($captureStatus);
            }

            if($chargeStatus != self::CODE_AUTHORIZED || ($captureStatus != self::PAYMENT_CONFIRMED  && $capture == true))
                throw new Exception('Charge or capture payment card gateway ' . self::$gatewayFlag . ' fail');
            else
                $success = true;

            $chargeStatus  =  $this->getStatusString($chargeStatus);

        } catch (Throwable $th) {
            $success = false;
            $this->logFails($th);
        }

        return $this->gatewayResponse(
            $success, ['transaction_id' => $transactionId, 'status' => $captureStatus ?? $chargeStatus]
        );
    }

    /**
     * Capture a previously charged card transaction
     *
     * @param String        $transactionId  String that represents transaction previously charged.
     * @param Float         $amount         Float that represents amount for the transaction.
     *
     * @return Array       ['success', 'transaction_id', 'status', 'gateway_flag']
     */
    public function cardCapture(string $transactionId, float $amount): array
	{
        $this->auth();
        $captureStatus = self::PAYMENT_VOIDED;

        try
        {
            $amount = floor($amount * 100);

            $sale           =   $this->cieloEcommerce->captureSale($transactionId, $amount, 0);
            $captureStatus  =   $sale->getStatus();
            $captureStatus  =   $this->getStatusString($captureStatus);

            if($captureStatus != self::PAYMENT_CONFIRMED)
                throw new Exception('Capture payment card gateway ' . self::$gatewayFlag . ' fail');
            else
                $success = true;

        } catch (Throwable $th) {
            $success = false;
            $this->logFails($th);
        }

        return $this->gatewayResponse(
            $success, ['transaction_id' => $transactionId, 'status' => $captureStatus]
        );
    }

}
