<?php

namespace Tests\gateways;

use multboy\desafio\GatewaysInterface;

use Illuminate\Foundation\Testing\TestCase;
use multboy\desafio\GatewaysResolve;

class GatewaysTest extends TestCase
{

    public function testCielo()
    {
        $resolver = new GatewaysResolve();
        $current = $resolver->resolveCurrent($resolver::GATEWAY_CIELO, true);
        $this->testCardCreate($current);
        $this->testCardCharge($current);
        $this->testCardChargeCapture($current);
        $this->testCardCapture($current);
    }

    private function testCardCreate(GatewaysInterface $gateway)
    {
        $userData = $this->getUser();
        $cardData = $this->getCard();

		$response = $gateway->cardCreate(
            $cardData['card_number'],
            $cardData['card_expiration_month'],
            $cardData['card_expiration_year'],
            $cardData['card_cvv'],
            $userData['user_name']
        );

        echo "\n Card create response:";
        var_dump($response);
        $this->assertEquals(is_array($response), true);
        $this->assertTrue($response['success']);
		$this->assertInternalType('string', $response['card_token']);
		$this->assertInternalType('string', $response['card_type']);
		$this->assertInternalType('string', $response['gateway_flag']);
        echo "\n Card create " . $gateway::$gatewayFlag . " success";
    }

    private function testCardCharge(GatewaysInterface $gateway)
    {
        $userData = $this->getUser();
        $cardData = $this->getCard();

        $responseCreate = $this->testCardCreate($gateway);
		$response = $gateway->cardCharge(
            $userData['user_paid'],
            $responseCreate['card_type'],
            $responseCreate['card_token'],
            $cardData['card_cvv']
        );

        echo "\n Card charge response:";
        var_dump($response);
        $this->assertEquals(is_array($response), true);
        $this->assertTrue($response['success']);
		$this->assertInternalType('string', $response['transaction_id']);
		$this->assertInternalType('string', $response['status']);
		$this->assertInternalType('string', $response['gateway_flag']);
        echo "\n Card charge " . $gateway::$gatewayFlag . " success";
    }

    private function testCardChargeCapture(GatewaysInterface $gateway)
    {
        $userData = $this->getUser();
        $cardData = $this->getCard();

        $responseCreate = $this->testCardCreate($gateway);
		$response = $gateway->cardCharge(
            $userData['user_paid'],
            $responseCreate['card_type'],
            $responseCreate['card_token'],
            $cardData['card_cvv'],
            true //parameter that defines capture
        );

        echo "\n Card charge and capture response:";
        var_dump($response);
        $this->assertEquals(is_array($response), true);
        $this->assertTrue($response['success']);
		$this->assertInternalType('string', $response['transaction_id']);
		$this->assertInternalType('string', $response['status']);
		$this->assertInternalType('string', $response['gateway_flag']);
        echo "\n Card charge and capture " . $gateway::$gatewayFlag . " success";
    }

    private function testCardCapture(GatewaysInterface $gateway)
    {
        $userData = $this->getUser();

        $responseCreate = $this->testCardCreate($gateway);
        $responseCharge = $this->testCardCharge($gateway);
		$response = $gateway->cardCapture(
            $responseCharge['transaction_id'],
            $userData['user_paid']
        );

        echo "\n Card capture response:";
        var_dump($response);
        $this->assertEquals(is_array($response), true);
        $this->assertTrue($response['success']);
		$this->assertInternalType('string', $response['transaction_id']);
		$this->assertInternalType('string', $response['status']);
		$this->assertInternalType('string', $response['gateway_flag']);
        echo "\n Card capture " . $gateway::$gatewayFlag . " success";
    }

    private function getUser()
    {
        return [
            'user_name' => 'Test Holder',
            'user_paid' => 15.5
        ];
    }

    private function getCard()
    {
        return [
            'card_number' => '5171407457820511',
            'card_expiration_month' => '12',
            'card_expiration_year' => '30',
            'card_cvv' => '123'
        ];
    }
}
