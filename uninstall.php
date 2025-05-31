<?php
/**
 * Uninstall script for AutoContent AI Pro
 */

// If uninstall not called from WordPress, then exit
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

// Delete plugin options
$options_to_delete = array(
    'autocontent_ai_pro_openrouter_api_key',
    'autocontent_ai_pro_image_api_key',
    'autocontent_ai_pro_default_model',
    'autocontent_ai_pro_default_publish_status',
    'autocontent_ai_pro_enable_images',
    'autocontent_ai_pro_enable_seo',
    'autocontent_ai_pro_internal_links_count',
    'autocontent_ai_pro_external_links_count'
);

foreach ($options_to_delete as $option) {
    delete_option($option);
}

// Drop custom tables
global $wpdb;

$table_name = $wpdb->prefix . 'autocontent_ai_pro_logs';
$wpdb->query("DROP TABLE IF EXISTS {$table_name}");

// Clean up post meta created by the plugin
$wpdb->query("DELETE FROM {$wpdb->postmeta} WHERE meta_key LIKE '_autocontent_%'");

// Clear any cached data
wp_cache_flush();
