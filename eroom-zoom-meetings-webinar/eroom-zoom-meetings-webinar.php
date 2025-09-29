<?php

/**
 * Plugin Name: eRoom - Zoom Meetings & Webinars
 * Plugin URI: https://eroomwp.com/
 * Description: eRoom Zoom Meetings & Webinars WordPress Plugin provides you with great functionality of managing Zoom meetings, scheduling options, and users directly from your WordPress dashboard.
 * The plugin is a free yet robust and reliable extension that enables direct integration of the world's leading video conferencing tool Zoom with your WordPress website.
 * Text Domain: eroom-zoom-meetings-webinar
 * Author: WPCenter
 * Author URI: https://profiles.wordpress.org/wpcenter/
 * Version:         1.5.0
 * Requires at least: 5.8
 * Requires PHP:      7.4
 *
 */
if ( !defined( 'ABSPATH' ) ) {
    exit;
}
//Exit if accessed directly
// Create a helper function for easy SDK access.
if ( !function_exists( 'eroom_fs' ) ) {
    function eroom_fs() {
        global $eroom_fs;
        if ( !isset( $eroom_fs ) ) {
            // Include Freemius SDK.
            require_once dirname( __FILE__ ) . '/vendor/freemius/wordpress-sdk/start.php';
            $eroom_fs = fs_dynamic_init( array(
                'id'             => '6034',
                'slug'           => 'eroom-zoom-meetings-webinar',
                'premium_slug'   => 'eroom-zoom-meetings-webinar-pro',
                'type'           => 'plugin',
                'public_key'     => 'pk_91f2676674910675990e30b3fa84e',
                'is_premium'     => false,
                'premium_suffix' => 'pro',
                'has_addons'     => false,
                'has_paid_plans' => true,
                'menu'           => [
                    'slug'       => 'stm_zoom',
                    'first-path' => 'admin.php?page=stm_zoom_settings',
                    'support'    => false,
                    'contact'    => true,
                    'pricing'    => true,
                ],
                'is_live'        => true,
            ) );
        }
        return $eroom_fs;
    }

    // Init Freemius.
    eroom_fs();
    // Signal that SDK was initiated.
    do_action( 'eroom_fs_loaded' );
    if ( !defined( 'STM_ZOOM_VERSION' ) ) {
        define( 'STM_ZOOM_VERSION', '1.5.0' );
    }
    if ( !defined( 'STM_ZOOM_FILE' ) ) {
        define( 'STM_ZOOM_FILE', __FILE__ );
    }
    if ( !defined( 'STM_ZOOM_DIR' ) ) {
        define( 'STM_ZOOM_DIR', __DIR__ );
    }
    if ( !defined( 'STM_ZOOM_PATH' ) ) {
        define( 'STM_ZOOM_PATH', dirname( STM_ZOOM_FILE ) );
    }
    if ( !defined( 'STM_ZOOM_URL' ) ) {
        define( 'STM_ZOOM_URL', plugin_dir_url( STM_ZOOM_FILE ) );
    }
    if ( !defined( 'EROOM_PLUGIN_FILE' ) ) {
        define( 'EROOM_PLUGIN_FILE', __FILE__ );
    }
    if ( !defined( 'EROOM_PLUGIN_DIR' ) ) {
        define( 'EROOM_PLUGIN_DIR', __DIR__ );
    }
    if ( !defined( 'EROOM_PLUGIN_URL' ) ) {
        define( 'EROOM_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
    }
    // Load composer autoloader for Freemius and other dependencies
    if ( file_exists( STM_ZOOM_PATH . '/vendor/autoload.php' ) ) {
        require_once STM_ZOOM_PATH . '/vendor/autoload.php';
    }
    require_once STM_ZOOM_PATH . '/zoom-app/vendor/autoload.php';
    require_once STM_ZOOM_PATH . '/includes/helpers.php';
    require_once STM_ZOOM_PATH . '/nuxy/NUXY.php';
    require_once STM_ZOOM_PATH . '/zoom-conference/init.php';
    require_once STM_ZOOM_PATH . '/vc/main.php';
    if ( did_action( 'elementor/loaded' ) ) {
        require STM_ZOOM_PATH . '/elementor/StmZoomElementor.php';
    }
    if ( is_admin() ) {
        require_once STM_ZOOM_PATH . '/includes/item-announcements.php';
        require_once STM_ZOOM_PATH . '/includes/conflux.php';
        require_once STM_ZOOM_PATH . '/includes/migration/migration.php';
        require_once STM_ZOOM_PATH . '/admin_templates/notices/required_fields.php';
        require_once STM_ZOOM_PATH . '/google-meet/StmERoomGoogleMeet.php';
        new StmERoomGoogleMeet();
    }
}