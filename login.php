<?php
/**
 * docs https://developers.facebook.com/docs/graph-api/using-graph-api
 * docs https://developers.facebook.com/docs/graph-api/reference/ # User
 * Test tool https://developers.facebook.com/tools/explorer?method=GET&path=me%3Ffields%3Did%2Cname%2Cpicture&version=v2.10
 */
include('function.php');

define('HOSTNAME', 'YOUR_DOMAIN');

// Facebook need argument
$state = filter_input(INPUT_GET, 'state', FILTER_SANITIZE_SPECIAL_CHARS);
$code  = filter_input(INPUT_GET, 'code', FILTER_SANITIZE_SPECIAL_CHARS);

$from  = '/';
$redirect_uri = 'https://'. HOSTNAME. '/login.php?from=' . urlencode($from);
$helper       = $fb->getRedirectLoginHelper();

$fbsession = false;
if (!empty($code) && !empty($state)) {
    try {
          $access_token = $helper->getAccessToken();
    } catch(Facebook\Exceptions\FacebookResponseException $e) {
        // When Graph returns an error
        error_log('Graph returned an error: ' . $e->getMessage());
        header('Location: https://' . HOSTNAME . $from); // login failed
        exit;
    } catch(Facebook\Exceptions\FacebookSDKException $e) {
        // When validation fails or other local issues
        error_log('Facebook SDK returned an error: ' . $e->getMessage());
        header('Location: https://' . HOSTNAME . $from); // login failed
        exit;
    }

    if (!isset($access_token)) { // login failed
        if ($helper->getError()) {
            header('HTTP/1.0 401 Unauthorized');
            error_log("Error: " . $helper->getError() . "\n");
            error_log("Error Code: " . $helper->getErrorCode() . "\n");
            error_log("Error Reason: " . $helper->getErrorReason() . "\n");
            error_log("Error Description: " . $helper->getErrorDescription() . "\n");
        }
        header('Location: http://' . HOSTNAME . $from);
        exit;
    }

    $_SESSION['fb_access_token'] = (string) $access_token;

    // 延長 access_token 時間，延長失敗就算了
    if (!$access_token->isLongLived()) {
        // Exchanges a short-lived access token for a long-lived one
        try {
            $oAuth2Client = $fb->getOAuth2Client();
            // $tokenMetadata = $oAuth2Client->debugToken($access_token);
            // $tokenMetadata->validateAppId(FB_APP_ID);
            // $tokenMetadata->validateExpiration();

            $access_token = $oAuth2Client->getLongLivedAccessToken($access_token);
            $_SESSION['fb_access_token'] = (string) $access_token;
        } catch (Facebook\Exceptions\FacebookSDKException $e) {
            error_log("Error getting long-lived access token: " . $e->getMessage() . "\n");
        }
        // var_dump($access_token->getValue());
    }

    if ($_SESSION['fb_access_token']) { // facebook login verification
        try {
            // Returns a `Facebook\FacebookResponse` object
            $response = $fb->get('/me?fields=id,name,email', $_SESSION['fb_access_token']);
        } catch(Facebook\Exceptions\FacebookResponseException $e) {
            error_log('Graph returned an error: ' . $e->getMessage());
            header('Location: https://' . HOSTNAME . $from); // failed
            exit;
        } catch(Facebook\Exceptions\FacebookSDKException $e) {
            error_log('Facebook SDK returned an error: ' . $e->getMessage());
            header('Location: https://' . HOSTNAME . $from); // failed
            exit;
        }

        $profile = $response->getGraphUser();
        // echo $profile['name'];
        // echo $profile['email'];
        // echo $profile['picture']['url'];

        $uid = $profile['id'];
        echo $profile['name'];
        echo $profile['email'];

        $_SESSION['login'] = $uid;
    }
}

// HTML layout
if (!isset($_SESSION['login']) || empty($_SESSION['login'])) {
    // generator facebook login url
    $fb_login_url = $helper->getLoginUrl($redirect_uri, ['email']);

    echo "<a href=\"$fb_login_url\">Facebook Login</a>";
} else {
    // Logout ..
    echo "<a href=\"/logout.php\">Facebook Logout</a>";
}
?>
