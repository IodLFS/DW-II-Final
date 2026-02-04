<?php
/**
 * Middleware CORS para Laravel
 * [RNF08] Permitir pedidos de origens cruzadas
 * 
 * Adicionar ao ficheiro config/cors.php do Laravel
 */

return [

    /*
    |--------------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS) Configuration
    |--------------------------------------------------------------------------
    |
    | Here you are able to configure the settings for Cross-Origin Resource
    | Sharing (CORS). This determines what cross-origin requests are allowed
    | to execute in this application.
    |
    */

    'paths' => ['api/*'],

    'allowed_methods' => ['*'],

    'allowed_origins' => ['*'],

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => false,

];
?>
