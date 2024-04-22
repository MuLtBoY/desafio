<?php

namespace multboy\desafio;

interface GatewaysInterface
{
    /**
     * Authenticates on the gateway before transactions
     * 
     * @return Array ['success', 'status', 'token', 'gateway_flag']
     */
    public function auth(): array;

    /**
     * Tokenizes a card
     *
     * @param String        $cardNumber         String that represents the number card.
     * @param String        $expirationMonth    String that represents the month of expiration date.
     * @param String        $expirationYear     String that represents the year of expiration date.
     * @param String        $cvv                String that represents security number cvv.
     * @param String        $holderName         String that represents the name of holder card.
     *
     * @return Array       ['success', 'card_token', 'card_type', 'gateway_flag']
     */
    public function cardCreate(string $cardNumber, string $expirationMonth, string $expirationYear, string $cvv, string $holderName): array;

    /**
     * Creates a transaction authorized or capture
     *
     * @param Float         $amount         Float that represents amount for the transaction.
     * @param String        $cardType       String that represents card brand.
     * @param String        $cardToken      String that represents card tokenized on gateway valt.
     * @param String        $cvv            String that represents security number cvv.
     * @param Boolean       $capture        Boolean that represents config to capture on charge.
     *
     * @return Array       ['success', 'transaction_id', 'status', 'gateway_flag']
     */
    public function cardCharge(float $amount, string $cardType, string $cardToken, string $cvv, bool $capture = true): array;

    /**
     * Capture a previously charged card transaction
     *
     * @param String        $transactionId  String that represents transaction previously charged.
     * @param Float         $amount         Float that represents amount for the transaction.
     *
     * @return Array       ['success', 'transaction_id', 'status', 'gateway_flag']
     */
    public function cardCapture(string $transactionId, float $amount): array;

    /**
     * Mounts response
     *
     * @param 
     * 
     * @return Array ['success', ..., 'gateway_flag']
     */
    public function gatewayResponse(bool $success, array $middleParamns): array;
}
