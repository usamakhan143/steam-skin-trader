<?php

function steam_login_enqueue_scripts()
{
    wp_enqueue_script(
        'steam-skin-trader-utilities', // Handle name for your script
        STEAM_PLUGIN_URL . 'includes/assets/js/utilities.js', // Path to your custom jQuery file
        array(), // Dependencies, leave empty if none
        '1.0.0', // Version number, update as needed
        true // Load in the footer
    );

    // Enqueue Styles
    wp_enqueue_style(
        'steam-skin-trader-bootstrap5',
        'https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css',
        array(),
        '5.0.2'
    );

    wp_enqueue_script(
        'steam-skin-trader-jquery', // Handle name for your script
        STEAM_PLUGIN_URL . 'node_modules/jquery/dist/jquery.min.js', // Path to your custom jQuery file
        array(), // Dependencies, leave empty if none
        '1.0.0', // Version number, update as needed
        true // Load in the footer
    );

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


// Marketplace Listing from Steam
function marketplace_listing_shortcode_handler($atts)
{
    // Define default values for attributes
    $default_atts = array(
        'count' => 100,
        'category' => '',
    );

    // Merge user-provided attributes with defaults
    $atts = shortcode_atts($default_atts, $atts, 'steam_listing');

    // Generate a unique container ID for this shortcode instance
    $container_id = 'skins_container_' . uniqid();

    // Add the container ID to the attributes
    $atts['container_id'] = $container_id;

    // Store data in a global array for all shortcode instances
    global $steam_listing_data;
    if (!isset($steam_listing_data)) {
        $steam_listing_data = [];
    }
    $steam_listing_data[] = $atts;

    // Include the template
    ob_start();
    include STEAM_PLUGIN_PATH . '/includes/templates/steam-marketplace-listing.php';
    return ob_get_clean();
}

// Hook to localize all data before rendering the page
add_action('wp_footer', function () {
    global $steam_listing_data;

    // If data exists, pass it to JavaScript
    if (!empty($steam_listing_data)) {
        wp_localize_script('steam-marketplace-listing-shortcode-script', 'steamListingData', $steam_listing_data);
    }
});

// Register the Steam Marketplace Listing shortcode
add_shortcode('steam_listing', 'marketplace_listing_shortcode_handler');




function steam_marketplace_enqueue_css()
{
    if ((has_shortcode(get_the_content(), 'steam_listing')) || (has_shortcode(get_the_content(), 'steam_my_items'))) {
        $steamSkinsListing = STEAM_PLUGIN_URL . 'includes/assets/css/listing.css';
        wp_register_style('steam-skin-Listing', $steamSkinsListing, array(), '1.0.0');

        wp_enqueue_style('steam-skin-Listing');
    }
}
add_action('wp_enqueue_scripts', 'steam_marketplace_enqueue_css', 100);

function steam_marketplace_listing_js()
{
    if ((has_shortcode(get_the_content(), 'steam_listing'))) {

        wp_enqueue_script(
            'steam-marketplace-listing-shortcode-script',
            STEAM_PLUGIN_URL . 'includes/assets/js/steam-market-listing.js',
            array(),
            '1.0.0',
            true
        );
    }
}

add_action('wp_enqueue_scripts', 'steam_marketplace_listing_js');



// Steam Market API Developed From Own Server
add_action('rest_api_init', function () {
    register_rest_route('custom-proxy/v1', '/steam-market', array(
        'methods' => 'GET',
        'callback' => 'fetch_steam_market_data',
        'args' => array(
            'count' => array(
                'required' => false,
                'default' => 100,
                'validate_callback' => function ($param) {
                    return is_numeric($param) && $param > 0;
                },
            ),
        ),
    ));
});

function fetch_steam_market_data($data)
{
    $count = $data['count']; // Retrieve the count parameter
    $api_url = "https://steamcommunity.com/market/search/render/?appid=730&norender=1&count={$count}";

    $response = wp_remote_get($api_url);

    if (is_wp_error($response)) {
        return new WP_Error('api_error', 'Unable to fetch data', array('status' => 500));
    }

    $body = wp_remote_retrieve_body($response);

    return rest_ensure_response(json_decode($body));
}

// Create a shortcode for logged in user Items. 
function steam_loggedin_user_inventory()
{
    include STEAM_PLUGIN_PATH . '/includes/templates/steam-loggedin-user-inventory.php';
}
add_shortcode('steam_my_items', 'steam_loggedin_user_inventory');

function steam_loginuser_inventory_items_js()
{
    if ((has_shortcode(get_the_content(), 'steam_my_items'))) {

        wp_enqueue_script(
            'steam-loggedin-useritems-shortcode-script',
            STEAM_PLUGIN_URL . 'includes/assets/js/steam-loggedin-user-inventory.js',
            array(),
            '1.0.0',
            true
        );
    }
}

add_action('wp_enqueue_scripts', 'steam_loginuser_inventory_items_js');


// Steam User Inventory API Developed From Own Server
add_action('rest_api_init', function () {
    register_rest_route('custom-proxy/v1', '/steam-user-items', array(
        'methods' => 'GET',
        'callback' => 'fetch_steam_user_items',
        'args' => array(
            'userid' => array(
                'required' => true,
                'default' => 0,
                'validate_callback' => function ($param) {
                    return is_numeric($param) && $param > 0;
                },
            ),
        ),
    ));
});

function fetch_steam_user_items($data)
{
    $steamId = $data['userid']; // Retrieve the count parameter
    $api_url = "https://steamcommunity.com/inventory/{$steamId}/730/2?l=english&count=5000";

    $response = wp_remote_get($api_url);

    if (is_wp_error($response)) {
        return new WP_Error('api_error', 'Unable to fetch data', array('status' => 500));
    }

    $body = wp_remote_retrieve_body($response);

    return rest_ensure_response(json_decode($body));
}
