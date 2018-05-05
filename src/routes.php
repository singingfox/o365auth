<?php
Route::get('/o365-sso', 'Singingfox\O365Auth\OAuthController@oauth');


// Routes for testing purpose only.  Remove when deploying.  Also remove view "login.blade.php".
Route::get('/test/login', function () {
    return '<html><body><a href="/o365-sso">Try to log in</a></body></html>';
});

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

Route::get("/o365-user/name", function () {
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

    return $me->getGivenName();
});

/**
 *

// Retrieve user info, then do whatever needed.  Could be a redirect url that was
public function userInfo()
{
if (session_status() == PHP_SESSION_NONE)
session_start();

$graph = new Graph();
$graph->setAccessToken($_SESSION['access_token']);

$me = $graph->createRequest("get", "/me")
->setReturnType(\Microsoft\Graph\Model\User::class)
->execute();

// save user info somewhere for some use.
// echo $me->getMail();
}
 *
 */