# Description to package 'desafio'

This package is a integration with payment gateways, standardizing all entries and outputs of data.

## Prerequisites

The main requirements for using this package have already been referenced in the composer.json file, however it is recommended to use PHP8.1 and LARAVEL10, as tests have not yet been carried out on other versions.

This code using mysql database to save configs. Version MYSQL8.0.36 tested.

## Instalation

1º Require this package by composer

composer require multboy/desafio

2º Run migration to create and populate a configuration table

php artisan migrate

3º For utilization this package, import and instance default gateway as:

<?php

use multboy\desafio\GatewaysResolve;

$resolver = new GatewaysResolve();

//update gateway settings
$resolver->updateGatewayConfig($resolver::GATEWAY_CIELO, 'merchant_id', '93fa44b4-fb61-476e-8cab-8950efed518e');
$resolver->updateGatewayConfig($resolver::GATEWAY_CIELO, 'merchant_key', 'RHNRVSJCXMNPOTKGOKMSOZLZJQXCQJVAMGDBVYDP');

$current = $resolver->resolveCurrent('gateway_cielo', true); //true represents devMode (sandbox) to test

$current->cardCreate('5171407457820511', '12', '30', '123', 'Teste Holder'); //tokenize card
$current->cardCharge(11, 'master', '00555af3-34a1-4e03-acb1-b02443b26a19', '123'); //creates card authorized payment
$current->cardCharge(15.10, 'master', '00555af3-34a1-4e03-acb1-b02443b26a19', '123', true);//creates card authorized payment and instant capture
$current->cardCapture('791c7e11-7904-4a22-aaea-9cdf485ea7c1', 11); //captures previously card payment authorized

4º The informations of sandbox Cielo are in https://developercielo.github.io/manual/'?shell#cart%C3%A3o-de-cr%C3%A9dito-em-sandbox, but you can use this:

Card number: 5171407457820511
Expiration month: 12
Expiration year: 2030 or 30
Security code: 123
Holder name: Teste Holder

5º This package has phpunit unit tests

Run `./vendor/bin/phpunit vendor/multboy/desafio/src/tests/unit/GatewaysTest.php` to execute this tests.