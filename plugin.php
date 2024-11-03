<?php

/**
 * Plugin Name: Geo Dynamic Headings for Elementor
 * Description: Displays different heading text based on geo location cookies
 * Version: 1.0
 * Author: Dr. Raj Kavin
 * Requires Elementor: 3.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

class GeoDynamicHeadings
{
    private static $_instance = null;

    public static function instance()
    {
        if (is_null(self::$_instance)) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    public function __construct()
    {
        // Add settings page requirement
        require_once(plugin_dir_path(__FILE__) . 'includes/settings-page.php');

        // Existing actions remain the same
        if (!did_action('elementor/loaded')) {
            add_action('admin_notices', [$this, 'admin_notice_missing_elementor']);
            return;
        }

        add_action('elementor/widgets/register', [$this, 'register_widgets']);
        add_action('elementor/frontend/after_enqueue_styles', [$this, 'enqueue_styles']);
        add_action('elementor/frontend/after_enqueue_scripts', [$this, 'enqueue_scripts']);
        add_action('wp_ajax_set_geo_cookie', [$this, 'set_geo_cookie']);
        add_action('wp_ajax_nopriv_set_geo_cookie', [$this, 'set_geo_cookie']);
    }

    public function admin_notice_missing_elementor()
    {
        if (isset($_GET['activate'])) {
            unset($_GET['activate']);
        }

        $message = sprintf(
            esc_html__('"%1$s" requires "%2$s" to be installed and activated.', 'geo-dynamic-headings'),
            '<strong>' . esc_html__('Geo Dynamic Headings', 'geo-dynamic-headings') . '</strong>',
            '<strong>' . esc_html__('Elementor', 'geo-dynamic-headings') . '</strong>'
        );

        printf('<div class="notice notice-warning is-dismissible"><p>%1$s</p></div>', $message);
    }

    public function register_widgets($widgets_manager)
    {
        require_once(plugin_dir_path(__FILE__) . 'widgets/geo-heading.php');
        require_once(plugin_dir_path(__FILE__) . 'widgets/geo-selector.php');

        $widgets_manager->register(new \Geo_Heading_Widget());
        $widgets_manager->register(new \Geo_Selector_Widget());
    }

    public function enqueue_styles()
    {
        wp_enqueue_style(
            'geo-headings-style',
            plugins_url('assets/css/style.css', __FILE__),
            [],
            '1.0.0'
        );
    }

    public function enqueue_scripts()
    {
        wp_enqueue_script(
            'geo-headings-script',
            plugins_url('assets/js/script.js', __FILE__),
            ['jquery'],
            '1.0.0',
            true
        );

        wp_localize_script('geo-headings-script', 'geoHeadingsAjax', [
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('geo-headings-nonce')
        ]);
    }

    public function set_geo_cookie()
    {
        check_ajax_referer('geo-headings-nonce', 'nonce');

        $geo = sanitize_text_field($_POST['geo']);

        // Check if this location requires redirection
        $locations = get_option('gdh_locations');
        if (!empty($locations['locations'])) {
            foreach ($locations['locations'] as $location) {
                if ($location['code'] === $geo && $location['is_redirect'] === '1' && !empty($location['redirect_url'])) {
                    wp_send_json_success([
                        'redirect' => true,
                        'url' => $location['redirect_url']
                    ]);
                    return;
                }
            }
        }

        // If no redirect, set cookie and continue normally
        setcookie('GEO', $geo, time() + (86400 * 30), COOKIEPATH, COOKIE_DOMAIN);
        wp_send_json_success([
            'redirect' => false,
            'message' => 'Cookie set successfully'
        ]);
    }

    // Helper function to get available locations
    public static function get_locations()
    {
        $locations = get_option('gdh_locations');
        $formatted_locations = array();

        if (!empty($locations['locations'])) {
            // First find the default location
            $default_location = null;
            foreach ($locations['locations'] as $location) {
                if (isset($location['is_default']) && $location['is_default'] === '1') {
                    $default_location = $location;
                    break;
                }
            }

            // If no explicit default is set, use the first location
            if (!$default_location && !empty($locations['locations'])) {
                $default_location = reset($locations['locations']);
            }

            // Add locations to the formatted array
            foreach ($locations['locations'] as $location) {
                $formatted_locations[$location['code']] = $location['name'];
            }
        }

        return $formatted_locations;
    }
}

// Initialize the plugin
GeoDynamicHeadings::instance();
