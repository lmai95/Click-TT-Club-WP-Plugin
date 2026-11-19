<?php
/**
 * Plugin Name: Click-TT Club Manager
 * Description: Liest Mannschaften, Tabellen, Spielpläne und Ergebnisse aus öffentlichen myTischtennis/click-TT-Seiten und stellt sie per Shortcode bereit.
 * Version: 0.1.0
 * Author: TSV Böbingen
 * License: GPL-2.0-or-later
 * Text Domain: clicktt-club-manager
 */
if (!defined('ABSPATH')) exit;

define('CTTCM_VERSION', '0.1.0');
define('CTTCM_FILE', __FILE__);
define('CTTCM_DIR', plugin_dir_path(__FILE__));
define('CTTCM_URL', plugin_dir_url(__FILE__));

require_once CTTCM_DIR . 'includes/class-clicktt-fetcher.php';
require_once CTTCM_DIR . 'includes/class-clicktt-parser.php';
require_once CTTCM_DIR . 'includes/class-clicktt-service.php';
require_once CTTCM_DIR . 'includes/class-clicktt-shortcodes.php';
require_once CTTCM_DIR . 'includes/class-clicktt-admin.php';

final class CTTCM_Plugin {
    public static function init(): void {
        CTTCM_Admin::init();
        CTTCM_Shortcodes::init();
        add_action('wp_enqueue_scripts', [__CLASS__, 'register_assets']);
    }
    public static function register_assets(): void {
        wp_register_style('cttcm-frontend', CTTCM_URL . 'assets/css/frontend.css', [], CTTCM_VERSION);
    }
    public static function activate(): void {
        if (!get_option('cttcm_settings')) {
            add_option('cttcm_settings', [
                'club_name' => 'TSV Böbingen',
                'club_url' => 'https://www.mytischtennis.de/click-tt/TTBW/26--27/verein/2061012/TSV_B%C3%B6bingen/mannschaften',
                'cache_minutes' => 30,
                'accent_mode' => 'elementor',
                'accent_color' => '#1b4f91',
                'manual_sources' => '',
            ]);
        }
    }
}
register_activation_hook(__FILE__, ['CTTCM_Plugin', 'activate']);
add_action('plugins_loaded', ['CTTCM_Plugin', 'init']);
