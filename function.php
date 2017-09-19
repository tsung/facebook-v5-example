<?php
define('FACEBOOK_SDK_V4_SRC_DIR', dirname(__FILE__) . '/facebook-php-sdk/src/Facebook/');
include(dirname(__FILE__) . '/lib/facebook-php-sdk/src/Facebook/autoload.php');
use Facebook\Facebook;

define('FB_APP_ID',     'YOUR_APP_ID');
define('FB_APP_SECRET', 'YOUR_APP_SECRET');

define('IS_CLI', is_cli());
if (!IS_CLI)
    session_start(); // for facebook

$fb_access_token = isset($_SESSION['fb_access_token']) ? $_SESSION['fb_access_token'] : '';
$fb = new \Facebook\Facebook([
    'app_id' => FB_APP_ID,
    'app_secret' => FB_APP_SECRET,
    'default_graph_version' => 'v2.10',
    'default_access_token' => $fb_access_token, // optional
]);

// {{{ function is_cli()
function is_cli()
{
    return (php_sapi_name() === 'cli') ? true : false;
}
// }}}
// {{{ function gen_facebook_login_url($from = '')
function gen_facebook_login_url($from = '')
{
    global $fb;
    $redirect_uri = 'https://' . YOUR_HOSTNAME . '/login.php?from=' . urlencode($from);
    $fbrdhelper   = $fb->getRedirectLoginHelper();
    $fb_login_url = $fbrdhelper->getLoginUrl($redirect_uri, ['email']);

    return $fb_login_url;
}
// }}}
?>
