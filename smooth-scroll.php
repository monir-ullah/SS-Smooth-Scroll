<?php
/**
 * Plugin Name: SS Smooth Scroll
 * Plugin URI:  https://wordpress.org/plugins/smooth-scrolls/
 * Description: A lightweight plugin that enables smooth scrolling with adjustable speed using Lenis and GSAP.
 * Version:     1.0.2
 * Author:      Monir Ullah
 * Author URI:  https://yourwebsite.com/
 * License:     GPL-2.0+
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: smooth-scrolls
 * Domain Path: /languages
 */

if (!defined('ABSPATH')) exit; // Prevent direct access

class SS_Smooth_Scroll {
    private static $instance = null;

    private function __construct() {
        add_action('admin_init', [$this, 'register_settings']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_scripts']);
        add_action('wp_enqueue_scripts', [$this, 'enqueue_scripts']);
        add_action('wp_ajax_save_smooth_scroll_settings', [$this, 'save_settings']);
        add_action('plugins_loaded', [$this, 'load_plugin_textdomain']);
    }

    public function load_plugin_textdomain() {
        load_plugin_textdomain('smooth-scrolls', false, dirname(plugin_basename(__FILE__)) . '/languages');
    }

    public static function get_instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function register_settings() {
        register_setting('general', 'ss_smooth_scroll_enabled', [
            'type' => 'string',
            'sanitize_callback' => 'sanitize_text_field',
            'default' => 'yes',
        ]);
        register_setting('general', 'ss_smooth_scroll_speed', [
            'type' => 'string',
            'sanitize_callback' => 'sanitize_text_field',
            'default' => '1.0',
        ]);

        add_settings_section('ss_smooth_scroll_section', '', '__return_null', 'general');
        add_settings_field('ss_smooth_scroll_enabled', 'Enable Smooth Scroll', [$this, 'settings_field'], 'general', 'ss_smooth_scroll_section');
    }

    public function settings_field() {
        $scroll_speed = get_option('ss_smooth_scroll_speed', '1.0');
        $enabled = get_option('ss_smooth_scroll_enabled', 'yes');
        ?>
        <h2>SS Smooth Scroll Settings</h2>
        <table class="form-table">
            <tr>
                <th><label for="ss_smooth_scroll_enabled">Enable Smooth Scrolling</label></th>
                <td>
                    <input type="checkbox" id="ss_smooth_scroll_enabled" <?php checked($enabled, 'yes'); ?>>
                    <p class="description">Check this box to enable smooth scrolling.</p>
                </td>
            </tr>
            <tr>
                <th><label for="ss_smooth_scroll_speed">Scroll Speed (0.5 - Slow, 1.0 - Normal, 2.0 - Fast)</label></th>
                <td>
                    <input type="number" step="0.1" min="0.1" max="5" id="ss_smooth_scroll_speed" value="<?php echo esc_attr($scroll_speed); ?>" class="regular-text">
                    <p class="description">Adjust the smooth scroll speed.</p>
                    <p>
                      If you need any professional help, feel free to Connect with me:  
                        <a href="https://www.facebook.com/wpDeveloperMonir" target="_blank">Facebook</a> | 
                        <a href="https://www.linkedin.com/in/monirullah/" target="_blank">LinkedIn</a> | 
                        or email me at <a href="mailto:mullah725@gmail.com">mullah725@gmail.com</a>.
                    </p>
                </td>
            </tr>
        </table>
        <?php
    }

    public function enqueue_admin_scripts() {
        wp_enqueue_script('jquery');
        wp_enqueue_script('ss-smooth-scroll-admin', plugin_dir_url(__FILE__) . 'assets/js/admin.js', ['jquery'], '1.0.0', true);
        wp_localize_script('ss-smooth-scroll-admin', 'ss_smooth_scroll_ajax', [
            'ajaxurl'  => admin_url('admin-ajax.php'),
            'security' => wp_create_nonce('ss_smooth_scroll_nonce')
        ]);
    }

    public function enqueue_scripts() {
        if (get_option('ss_smooth_scroll_enabled', 'yes') !== 'yes') return;

        // Enqueue local copies of scripts
        wp_enqueue_script('ss-lenis', plugin_dir_url(__FILE__) . 'assets/js/lenis.min.js', [], '1.2.3', true);
        wp_enqueue_script('ss-gsap', plugin_dir_url(__FILE__) . 'assets/js/gsap.min.js', [], '3.12.5', true);
        wp_enqueue_script('ss-scrolltrigger', plugin_dir_url(__FILE__) . 'assets/js/ScrollTrigger.min.js', ['ss-gsap'], '3.12.5', true);

        // Initialize Lenis on frontend
        $scroll_speed = esc_attr(get_option('ss_smooth_scroll_speed', '1.0'));
        wp_add_inline_script('ss-lenis', "
            console.log('SS Smooth Scroll Frontend Script Loaded');
            document.addEventListener('DOMContentLoaded', function () {
                const lenis = new Lenis({ duration: {$scroll_speed} });

                function raf(time) {
                    lenis.raf(time);
                    requestAnimationFrame(raf);
                }
                requestAnimationFrame(raf);
            });
        ");
    }

    public function save_settings() {
        check_ajax_referer('ss_smooth_scroll_nonce', 'security');

        if (isset($_POST['field']) && isset($_POST['value'])) {
            $field = sanitize_text_field(wp_unslash($_POST['field']));
            $value = sanitize_text_field(wp_unslash($_POST['value']));
            update_option($field, $value);
            echo json_encode(['success' => true, 'message' => 'Settings saved!']);
            wp_die();
        }
        echo json_encode(['success' => false, 'message' => 'Invalid request.']);
        wp_die();
    }
}

SS_Smooth_Scroll::get_instance();
