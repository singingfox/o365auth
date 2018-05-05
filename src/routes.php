<?php
Route::get('/o365-sso', 'Singingfox\O365Auth\OAuthController@oauth');

// Sample route
Route::get("/o365-user/email", function () {
    if (session_status() == PHP_SESSION_NONE)
        session_start();

    $accessToken = array_get($_SESSION, 'access_token');
    if (!$accessToken) {
        abort(500, "Office 365 access token doesn't exist.  Authentication aborted.");
    }

    $graph = new \Microsoft\Graph\Graph();
    $graph->setAccessToken($accessToken);

    $me = $graph->createRequest("get", "/me")
        ->setReturnType(\Microsoft\Graph\Model\User::class)
        ->execute();

    return $me->getMail();
});
