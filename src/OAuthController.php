<?php

namespace Singingfox\O365Auth;

use App\Http\Controllers\Controller;
use League\OAuth2\Client\Provider\GenericProvider as OAuth2Provider;
use Microsoft\Graph\Graph;

class OAuthController extends Controller
{
    public function oauth()
    {
        // PHP session is used to keep username, id, and tokens
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // define these 3 in project_root/.env
        $clientId = env('O365_CLIENT_ID');
        $clientSecret = env('O365_CLIENT_SECRET');
        $redirectUrl = env('O365_REDIRECT_URL');
        if (!$clientId || !$clientSecret || !$redirectUrl) {
            abort(500, 'Office 365 authentication parameters are not provided. Authentication aborted.');
        }

        $oAuthProvider = new OAuth2Provider([
            'clientId'                => $clientId,
            'clientSecret'            => $clientSecret,
            'redirectUri'             => $redirectUrl,
            // these 3 are defined in config.php of this package project
            'urlAuthorize'            => config('O365Auth.baseUrl') . config('O365Auth.authorizeUrl'),
            'urlAccessToken'          => config('O365Auth.baseUrl') . config('O365Auth.tokenUrl'),
            'scopes'                  => config('O365Auth.scopes'),
            'urlResourceOwnerDetails' => ''
        ]);

        if ($_SERVER['REQUEST_METHOD'] === 'GET' && !isset($_GET['code'])) {
            $authorizationUrl = $oAuthProvider->getAuthorizationUrl();
            $_SESSION['state'] = $oAuthProvider->getState();
            // User often get redirected to here from a route that requires authentication.
            // In that case, save it to session, retrieve it when user is authenticated, then redirect user to this destination.
            $urlBeforeAuth = array_get($_SERVER, 'HTTP_REFERER');
            if ($urlBeforeAuth) {
                $_SESSION['url_before_auth'] = $urlBeforeAuth;
            }
            return redirect($authorizationUrl);
        } elseif ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['code'])) {
            // Validate the OAuth state parameter
            if (empty($_GET['state']) || ($_GET['state'] !== $_SESSION['state'])) {
                unset($_SESSION['state']);
                abort(500, 'State not matched.  Authentication aborted.');
            }

            // With the authorization code, we can retrieve access tokens and other data.
            try {
                $accessToken = $oAuthProvider->getAccessToken('authorization_code', ['code' => $_GET['code']]);
                $_SESSION['access_token'] = $accessToken->getToken();

                $idToken = $accessToken->getValues()['id_token'];
                $decoded = base64_decode(explode('.', $idToken)[1]);
                $payload = json_decode($decoded, true);

                // The following user properties are needed in the next page
                $_SESSION['preferred_username'] = $payload['preferred_username'];
                $_SESSION['given_name'] = $payload['name'];

                //  Redirecting in the end of a successful authentication process.
                //  First try what's specified in .env, then try the URL before authentication, last fall back to "/".
                $urlBeforeAuth = array_get($_SESSION, 'url_before_auth');
                $urlAfterAuth = env('O365_AFTER_AUTH_URL', $urlBeforeAuth ? $urlBeforeAuth : '/');
                return redirect($urlAfterAuth);
            } catch (\Exception $e) {
                abort(500, "Office 365 token not obtained.  Authentication aborted.  Error: " . $e->getMessage());
            }
        } else {
            abort(500, 'Unknown authentication failure.');
        }
    }
}
