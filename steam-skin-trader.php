<?php

/**
 * Plugin Name: Steam Skin Trader
 * Description: Unlock the power of Steam with SteamSkinTrader. Allow users to easily sign in via Steam and buy or sell in-game skins with confidence, using Steam’s trusted authentication and marketplace integration.
 * Version: 1.0.0
 * Author: Usama Khan
 * Author URI: https://github.com/usamakhan143
 * Requires PHP: 7.4
 * Text Domain: translate-steam-skin-trader
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}
if (!class_exists('SteamSkinTrader')) {


    class SteamSkinTrader
    {

        public function __construct()
        {
            // Define a constant to initialize the plugin path.
            define('STEAM_PLUGIN_PATH', plugin_dir_path(__FILE__));

            // Define a constant to initialize the frontend plugin path.
            define('STEAM_PLUGIN_URL', plugin_dir_url(__FILE__));

            // Call the packages that you are using in the plugin to enhance the functionality.
            // require_once(STEAM_PLUGIN_PATH . '/vendor/autoload.php');
        }

        public function initialize()
        {
            include_once(STEAM_PLUGIN_PATH . '/includes/utilities.php');
            include_once(STEAM_PLUGIN_PATH . '/includes/options-page.php');
            include_once(STEAM_PLUGIN_PATH . '/includes/steam-skin-trader.php');
        }
    }
} else {
    die('This class is already exist!');
}
$SteamSkinTrader = new SteamSkinTrader();
$SteamSkinTrader->initialize();
