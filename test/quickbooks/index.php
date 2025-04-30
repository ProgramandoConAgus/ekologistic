<?php
session_start();

// Ajusta la ruta a tu autoloader y a config.php usando __DIR__
require __DIR__ . '/../../vendor/autoload.php';
$config = require __DIR__ . '/config.php';

// 1) Verifica que $config es el array que esperas:
//echo '<pre>CONFIG: '; 
//var_dump($config);
//echo '</pre>';
//exit;

// 2) Si llega bien, comenta el var_dump/exit y sigue con esto:
use QuickBooksOnline\API\Core\OAuth\OAuth2\OAuth2LoginHelper;

$oauth2Helper = new OAuth2LoginHelper($config['ClientID'], $config['ClientSecret']);
$authUrl = $oauth2Helper->getAuthorizationCodeURL(
    $config['RedirectURI'],
    $config['scope']
);

header('Location: ' . $authUrl);
exit;
