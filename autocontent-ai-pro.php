<?php
/**
 * Plugin Name: AutoContent AI Pro
 * Plugin URI: https://example.com/autocontent-ai-pro
 * Description: Automatically generate SEO-optimized articles using OpenRouter API with RankMath integration
 * Version: 1.0.0
 * Author: AutoContent AI Pro
 * License: GPL v2 or later
 * Text Domain: autocontent-ai-pro
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('AUTOCONTENT_AI_PRO_VERSION', '1.0.0');
define('AUTOCONTENT_AI_PRO_PLUGIN_URL', plugin_dir_url(__FILE__));
define('AUTOCONTENT_AI_PRO_PLUGIN_PATH', plugin_dir_path(__FILE__));

// Main plugin class
class AutoContentAIPro {
    
    private static $instance = null;
    
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        add_action('plugins_loaded', array($this, 'init'));
        register_activation_hook(__FILE__, array($this, 'activate'));
        register_deactivation_hook(__FILE__, array($this, 'deactivate'));
    }
    
    public function init() {
        // Load plugin textdomain
        load_plugin_textdomain('autocontent-ai-pro', false, dirname(plugin_basename(__FILE__)) . '/languages');
        
        // Include required files
        $this->include_files();
        
        // Initialize components
        $this->init_components();
    }
    
    private function include_files() {
        require_once AUTOCONTENT_AI_PRO_PLUGIN_PATH . 'includes/class-openrouter-api.php';
        require_once AUTOCONTENT_AI_PRO_PLUGIN_PATH . 'includes/class-content-generator.php';
        require_once AUTOCONTENT_AI_PRO_PLUGIN_PATH . 'includes/class-image-handler.php';
        require_once AUTOCONTENT_AI_PRO_PLUGIN_PATH . 'includes/class-seo-optimizer.php';
        require_once AUTOCONTENT_AI_PRO_PLUGIN_PATH . 'includes/class-logger.php';
        require_once AUTOCONTENT_AI_PRO_PLUGIN_PATH . 'admin/class-admin.php';
        require_once AUTOCONTENT_AI_PRO_PLUGIN_PATH . 'admin/class-settings.php';
    }
    
    private function init_components() {
        // Initialize admin interface
        if (is_admin()) {
            new AutoContentAIPro_Admin();
            new AutoContentAIPro_Settings();
        }
        
        // Initialize AJAX handlers
        add_action('wp_ajax_generate_content', array($this, 'ajax_generate_content'));
        add_action('wp_ajax_batch_generate_content', array($this, 'ajax_batch_generate_content'));
        add_action('wp_ajax_test_api_connection', array($this, 'ajax_test_api_connection'));
        add_action('wp_ajax_check_api_status', array($this, 'ajax_check_api_status'));
    }
    
    public function activate() {
        // Create database tables if needed
        $this->create_tables();
        
        // Set default options
        $this->set_default_options();
        
        // Flush rewrite rules
        flush_rewrite_rules();
    }
    
    public function deactivate() {
        // Clean up scheduled events
        wp_clear_scheduled_hook('autocontent_ai_pro_cleanup');
    }
    
    private function create_tables() {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'autocontent_ai_pro_logs';
        
        $charset_collate = $wpdb->get_charset_collate();
        
        $sql = "CREATE TABLE $table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            timestamp datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
            action varchar(100) NOT NULL,
            message text NOT NULL,
            data longtext,
            status varchar(20) NOT NULL,
            PRIMARY KEY (id)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }
    
    private function set_default_options() {
        $default_options = array(
            'openrouter_api_key' => '',
            'openai_api_key' => '',
            'claude_api_key' => '',
            'deepseek_api_key' => '',
            'default_model' => 'gpt-4',
            'default_publish_status' => 'draft',
            'enable_images' => 1,
            'enable_seo' => 1,
            'internal_links_count' => 2,
            'external_links_count' => 3
        );
        
        foreach ($default_options as $option => $value) {
            if (!get_option('autocontent_ai_pro_' . $option)) {
                add_option('autocontent_ai_pro_' . $option, $value);
            }
        }
    }
    
    public function ajax_generate_content() {
        check_ajax_referer('autocontent_ai_pro_nonce', 'nonce');
        
        if (!current_user_can('edit_posts')) {
            wp_die(__('You do not have sufficient permissions to access this page.'));
        }
        
        $topic = sanitize_text_field($_POST['topic']);
        $focus_keyword = sanitize_text_field($_POST['focus_keyword']);
        $publish_status = sanitize_text_field($_POST['publish_status']);
        
        $generator = new AutoContentAIPro_ContentGenerator();
        $result = $generator->generate_single_article($topic, $focus_keyword, $publish_status);
        
        wp_send_json($result);
    }
    
    public function ajax_batch_generate_content() {
        check_ajax_referer('autocontent_ai_pro_nonce', 'nonce');
        
        if (!current_user_can('edit_posts')) {
            wp_die(__('You do not have sufficient permissions to access this page.'));
        }
        
        $keywords = array_map('sanitize_text_field', $_POST['keywords']);
        $publish_status = sanitize_text_field($_POST['publish_status']);
        
        $generator = new AutoContentAIPro_ContentGenerator();
        $results = array();
        
        foreach ($keywords as $keyword) {
            $result = $generator->generate_single_article($keyword, $keyword, $publish_status);
            $results[] = $result;
        }
        
        wp_send_json($results);
    }
    
    public function ajax_test_api_connection() {
        check_ajax_referer('autocontent_ai_pro_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to access this page.'));
        }
        
        $api = new AutoContentAIPro_OpenRouterAPI();
        $result = $api->test_connection();
        
        wp_send_json($result);
    }
    
    public function ajax_check_api_status() {
        check_ajax_referer('autocontent_ai_pro_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to access this page.'));
        }
        
        $status = array(
            'openrouter_configured' => !empty(get_option('autocontent_ai_pro_openrouter_api_key', '')),
            'openai_configured' => !empty(get_option('autocontent_ai_pro_openai_api_key', '')),
            'claude_configured' => !empty(get_option('autocontent_ai_pro_claude_api_key', '')),
            'deepseek_configured' => !empty(get_option('autocontent_ai_pro_deepseek_api_key', ''))
        );
        
        wp_send_json($status);
    }
}

// Initialize the plugin
AutoContentAIPro::get_instance();
