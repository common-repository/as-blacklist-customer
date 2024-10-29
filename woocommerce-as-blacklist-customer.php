<?php

/**
    Plugin Name: AS Blacklist Customer
    Version: 1.0.0
    Plugin URI: https://wordpress.org/plugins/as-blacklist-customer/
    Description: Blacklist your woocommerce customer at admin order page. Plugin will add notice on all order of blacklisted customer.
    Author: Akshar Soft Solutions
    Author URI: http://aksharsoftsolutions.com/
    Requires at least: 4.4.0
    Tested up to: 4.6.0
    Text Domain: woo-as-blacklist-customer
    Domain Path: /languages
 */
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}


if (!class_exists('WooCommerce_AS_Blacklist_Customer')) {

    /**
     * Main Class.
     */
    class WooCommerce_AS_Blacklist_Customer {

        /**
         * Plugin version.
         *
         * @var string
         */
        const VERSION = '1.0.0';

        /**
         * Instance of this class.
         *
         * @var object
         */
        protected static $instance = null;

        /**
         * Return an instance of this class.
         *
         * @return object single instance of this class.
         */
        public static function get_instance() {
            if (null === self::$instance) {
                self::$instance = new self;
            }
            return self::$instance;
        }

        /**
         * Constructor
         */
        private function __construct() {
            if (!class_exists('WooCommerce')) {
                add_action('admin_notices', array($this, 'fallback_notice'));
            } else {
                $this->load_plugin_textdomain();
                $this->includes();
            }
        }

        /**
         * Plugin actication
         *
         */
        public static function activate() {
            include_once 'includes/woocommerce-as-blacklist-customer-activate.php';
            WooCommerce_AS_Blacklist_Customer_Activate::activate();
        }

        /**
         * Plugin deactivation
         *
         */
        public static function deactivate() {
            include_once 'includes/woocommerce-as-blacklist-customer-deactivate.php';
            WooCommerce_AS_Blacklist_Customer_Deactivate::deactivate();
        }

        /**
         * Core Business Logic
         *
         * @var string
         */
        public function includes() {
            include_once 'includes/woocommerce-as-blacklist-customer-function.php';
        }

        /**
         * Load the plugin text domain for translation.
         *
         * @access public
         * @return bool
         */
        public function load_plugin_textdomain() {
            load_plugin_textdomain('woo-as-blacklist-customer', false, dirname(plugin_basename(__FILE__)) . '/languages');

            return true;
        }

        /**
         * Fallback notice.
         *
         * We need some plugins to work, and if any isn't active we'll show you!
         */
        public function fallback_notice() {
            echo '<div class="error">';
            echo '<p>' . __('Woo Extension Plugin Boilerplate: Needs the WooCommerce Plugin activated.', 'woo-as-blacklist-customer') . '</p>';
            echo '</div>';
        }

    }

}

/**
 * Hook to run when your plugin is activated
 */
register_activation_hook(__FILE__, array('WooCommerce_AS_Blacklist_Customer', 'activate'));

/**
 * Hook to run when your plugin is deactivated
 */
register_deactivation_hook(__FILE__, array('WooCommerce_AS_Blacklist_Customer', 'deactivate'));

/**
 * Initialize the plugin.
 */
add_action('plugins_loaded', array('WooCommerce_AS_Blacklist_Customer', 'get_instance'));
