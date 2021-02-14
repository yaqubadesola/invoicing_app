<?php

return [

	/*
	|--------------------------------------------------------------------------
	| Third Party Services
	|--------------------------------------------------------------------------
	|
	| This file is for storing the credentials for third party services such
	| as Stripe, Mailgun, Mandrill, and others. This file provides a sane
	| default location for this type of information, allowing packages
	| to have a conventional place to find your various credentials.
	|
	*/
    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
    ],
	'mandrill' => [
		'secret' => env('MANDRILL_SECRET'),
	],
	'ses' => [
		'key' => '',
		'secret' => '',
		'region' => 'us-east-1',
	],
	'stripe' => [
		'secret' => env('STRIPE_SECRET'),
		'status' => env('STRIPE_STATUS'),
		'key' => env('STRIPE_KEY'),
	],
    'paypal'=>[
		'client_id' => env('PAYPAL_CLIENT_ID'),
		'secret' => env('PAYPAL_SECRET'),
		'status' => env('PAYPAL_STATUS'),
		'account' => env('PAYPAL_ACCOUNT'),
		'mode' => env('PAYPAL_MODE'),
	],
    'license'=>[
        'is_verified' => env('IS_VERIFIED',true),
        'purchase_code' => env('PURCHASE_CODE'),
    ]
];
