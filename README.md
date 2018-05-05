# Office 365 PHP Auth

`microsoftgraph/php-connect-sample` could have been a good reference for authentication with Office 365 in Laravel. But it seemed to have been based on an earlier version of Laravel, and doesn't require PHP 7.1.3.  When trying out in an environment that's set up for Laravel 5.6, that requires PHP 7.1.3, errors are reported due to deprecated `mcrypt_module_open()`.

## Installation

First, run `composer require singingfox/o365auth` under Laraval application root directory.

Add the following to `config/app.php`

`Singingfox\O365Auth\O365AuthServiceProvider::class,`

## Dependencies

similarly to [microsoftgraph/php-connect-sample](https://github.com/microsoftgraph/php-connect-sample), this package uses 
[thephpleague/oauth2-client](https://github.com/thephpleague/oauth2-client).

Microsoft Graph is of course needed too.

So adding to composer.json of this package:

```
"league/oauth2-client": "^2.3",
"microsoft/microsoft-graph": "^1.3"
```

## Office 365 parameters needed in Laravel .env

Add the following in .env file 

```
O365_CLIENT_ID=YOUR-APPLICATION-ID-OR-CLIENT-ID-IN-CREATED-MICROSOFT-APPLICATION
O365_CLIENT_SECRET=YOUR-CLIENT-SECRETE-OR-CLIENT-PASSWORD-IN-CREATED-MICROSOFT-APPLICATION
O365_REDIRECT_URL=YOUR-REDIRECT-URL-IN-CREATED-MICROSOFT-APPLICATION

O365_AFTER_AUTH_URL=
```

## What to do after authentication

The following is a sample of what can be done after successful authentication:

1. Retrieve `access_token` as stored in session
2. Initialize a `Graph` object, and assign the token to it
3. Now we should be able to call all kinds of Graph API endpoints and do whatever we need to do
4. Optionally, `O365_AFTER_AUTH_URL` can be specified in `.env` under Laraval application root, then a successful authentication process would be followed by some immediate actions, such as setting authenticated user locally, etc.

```php
Route::get("/o365-user/email", function () {
    if (session_status() == PHP_SESSION_NONE)
        session_start();

    $graph = new \Microsoft\Graph\Graph();
    $graph->setAccessToken($_SESSION['access_token']);

    $me = $graph->createRequest("get", "/me")
        ->setReturnType(\Microsoft\Graph\Model\User::class)
        ->execute();

    return $me->getMail();
});
```
## Error custom pages

HTTP 500 errors could be raised.  If custom views are defined in Laravel application, such as `resources/views/errors/500.blade.php`, an error page with more detailed information would be given, instead of a generic page with no specifics.