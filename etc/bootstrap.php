<?php

date_default_timezone_set('UTC');

require __DIR__ . '/../vendor/autoload.php';

/** @var Pimple\Container $config */
$config = require __DIR__ . '/config.php';

$config['BASE_DIR'] = dirname(__DIR__);

$config['CODE_FILE'] = $config['BASE_DIR'] . '/var/' . date('Y-m-d');

$config['REGISTRATION_FILE'] = function() use ($config) {
    return $config['CODE_FILE'] . '.registrations';
};

$config['RAFFLE_FILE'] = function() use ($config) {
    return $config['CODE_FILE'] . '.raffle';
};

// Functions, I know this is ugly. :D

$config['is_code'] = $config->protect(function($code) use ($config) {
    exec('grep ' . escapeshellarg($code) . ' ' . $config['CODE_FILE']);
});

$config['has_code_registered'] = $config->protect(function($code) use ($config) {
    $cmd = 'grep ' . escapeshellarg($code . ' ') . ' ' . $config['REGISTRATION_FILE'];
    return (system($cmd) !== false);
});

$config['has_code_won'] = $config->protect(function($code) use ($config) {
    $cmd = 'grep ' . escapeshellarg($code . ' ') . ' ' . $config['RAFFLE_FILE'];
    return (system($cmd) !== false);
});

$config['mark_code_registered'] = $config->protect(function($code, $number) use ($config) {
    $cmd = 'echo ' . escapeshellarg($code. ' ' . $number) . ' >> ' . $config['REGISTRATION_FILE'];
    return (system($cmd) !== false);
});

$config['mark_code_won'] = $config->protect(function($code) use ($config) {
    $cmd = 'echo ' . escapeshellarg($code) . ' >> ' . $config['RAFFLE_FILE'];
    return (system($cmd) !== false);
});

$config['twilio.base_url'] = 'https://api.twilio.com/2010-04-01/Accounts/'.$config['TWILIO_ACCOUNT'];

$config['guzzle'] = function() use ($config) {
    return new GuzzleHttp\Client();
};

$config['send_sms'] = $config->protect(function($number, $message) use ($config) {
    $url = $config['twilio.base_url'] . '/Messages.json';

    $data = [
        'From' => $config['TWILIO_NUMBER'],
        'To' => $number,
        'Body' => $message
    ];

    /** @var GuzzleHttp\Client $client */
    $client = $config['guzzle'];
    $response = $client->post($url, [
        'auth' => [$config['TWILIO_ACCOUNT'], $config['TWILIO_SECRET']],
        'headers' => [
        ],
        'body' => $data
    ]);

    return ($response->getReasonPhrase() == 'CREATED');
});

return $config;
