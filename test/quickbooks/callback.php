<?php
require  '../../vendor/autoload.php';

use QuickBooksOnline\API\Core\OAuth\OAuth2\OAuth2LoginHelper;
use QuickBooksOnline\API\DataService\DataService;

session_start();
$config = require 'config.php';

if (!isset($_GET['code']) || !isset($_GET['realmId'])) {
    die('Error: no se recibió el código de autorización.');
}

$authCode = $_GET['code'];
$realmId  = $_GET['realmId'];

$oauth2Helper = new OAuth2LoginHelper($config['ClientID'], $config['ClientSecret']);
$tokenObj = $oauth2Helper->exchangeAuthorizationCodeForToken(
    $authCode,
    $config['RedirectURI']
);

// Extraemos tokens
$accessToken  = $tokenObj->getAccessToken();
$refreshToken = $tokenObj->getRefreshToken();
$expireTime   = $tokenObj->getAccessTokenExpiresIn();

// **Guarda estos valores en tu base de datos o sesión**:
$_SESSION['accessToken']  = $accessToken;
$_SESSION['refreshToken'] = $refreshToken;
$_SESSION['realmId']      = $realmId;

// Configuramos el DataService
$dataService = DataService::Configure([
    'auth_mode'       => 'oauth2',
    'ClientID'        => $config['ClientID'],
    'ClientSecret'    => $config['ClientSecret'],
    'accessTokenKey'  => $accessToken,
    'refreshTokenKey' => $refreshToken,
    'QBORealmID'      => $realmId,
    'baseUrl'         => $config['baseUrl']
]);

// Ahora podemos hacer llamadas. Por ejemplo:
$companyInfo = $dataService->getCompanyInfo();
echo '<h2>¡Conexión exitosa!</h2>';
echo '<p>Empresa: ' . $companyInfo->CompanyName . '</p>';
echo '<p>Ambiente: Sandbox</p>';
