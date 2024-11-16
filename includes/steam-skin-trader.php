<?php

function steam_login_enqueue_scripts()
{
    wp_enqueue_script('jquery');
    wp_enqueue_script('steam-login-js', plugins_url('assets/js/steam-login.js', __FILE__), array('jquery'), null, true);
    wp_localize_script('steam-login-js', 'steamLogin', array(
        'callback_url' => site_url('/wp-json/steam-login/callback'),
    ));
}
add_action('wp_enqueue_scripts', 'steam_login_enqueue_scripts');

// Create a shortcode for Steam login button
function steam_login_button_shortcode()
{
    return '<div id="login-btn-container">
    <a href="#" id="steam-login-button">Login with Steam</a>
    </div>';
}
add_shortcode('steam_login_button', 'steam_login_button_shortcode');

// Create a REST API endpoint for Steam login callback
function steam_login_callback()
{
    if (isset($_GET['openid_claimed_id'])) {
        $steamID = basename($_GET['openid_claimed_id']);
        $apiKey = '18C10DA3399B5F489056F9B773BE3599';
        $profileUrl = "https://api.steampowered.com/ISteamUser/GetPlayerSummaries/v2/?key=$apiKey&steamids=$steamID";

        $userProfile = file_get_contents($profileUrl);
        $profileData = json_decode($userProfile, true);

        if ($profileData && isset($profileData['response']['players'][0])) {
            $player = $profileData['response']['players'][0];

            // Store user profile details in cookies for 1 hour (3600 seconds)
            setcookie('steam_username', $player['personaname'], time() + 3600, "/");
            setcookie('steam_avatar', $player['avatarfull'], time() + 3600, "/");
            setcookie('steam_steamID', $steamID, time() + 3600, "/");

            // Redirect to homepage
            wp_redirect(home_url());
            exit;
        }
    }
    wp_redirect(home_url());
    exit;
}


add_action('rest_api_init', function () {
    register_rest_route('steam-login', '/callback', array(
        'methods' => 'GET',
        'callback' => 'steam_login_callback',
    ));
});


function steam_login_details_shortcode()
{
    include STEAM_PLUGIN_PATH . '/includes/templates/steam-userinfo.php';
}
add_shortcode('steam_user_info', 'steam_login_details_shortcode');


function enqueue_steam_custom_css()
{
    wp_enqueue_style('steam-skin-trader', STEAM_PLUGIN_URL . 'includes/assets/css/main-style.css');
}
add_action('wp_enqueue_scripts', 'enqueue_steam_custom_css');


function steam_load_font_awesome()
{
    wp_enqueue_style('font-awesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css');
}
add_action('wp_enqueue_scripts', 'steam_load_font_awesome');
