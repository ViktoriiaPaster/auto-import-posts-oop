<?php

/*
Plugin Name: Auto Import Posts OOP
Description: A custom plugin to fetch and create posts from an Mockaroo API response.
Version: 1.0
Author: Viktoriia Paster
Text Domain: auto-import-posts
*/

namespace AutoImport;

if (!defined('ABSPATH')) {
    exit;
}

// Define plugin path
define('AIP_PLUGIN_URL', plugin_dir_url(__FILE__));
define('AIP_PLUGIN_PATH', plugin_dir_path(__FILE__));

require_once plugin_dir_path(__FILE__) . 'includes/class-create-shortcode.php';
require_once plugin_dir_path(__FILE__) . 'includes/class-fetch-posts.php';
require_once plugin_dir_path(__FILE__) . 'includes/class-insert-posts.php';

use AutoImport\CreateShortcode;
use AutoImport\FetchPosts;
use AutoImport\InsertPosts;

class InitPlugin
{
    private $fetcher;
    private $inserter;
    private $shortcode;

    public function __construct()
    {
        $this->loadDependencies();
    }

    private function loadDependencies()
    {
        $this->fetcher = new FetchPosts();
        $this->inserter = new InsertPosts($this->fetcher);
        $this->shortcode = new CreateShortcode();
    }

    public function runCron()
    {
        $this->inserter->autoInsertPosts();
    }

    public function registerShortcode()
    {
        add_shortcode('aip', [$this->shortcode, 'addShortcode']);
    }

    public function activate()
    {
        if (!wp_next_scheduled('aip_cron_hook')) {
            wp_schedule_event(time(), 'daily', 'aip_cron_hook');
        }
    }

    public function deactivate()
    {
        wp_clear_scheduled_hook('aip_cron_hook');
    }

    public function loadAdminFiles()
    {
        require_once ABSPATH . 'wp-admin/includes/post.php';
        require_once ABSPATH . 'wp-admin/includes/taxonomy.php';
        require_once ABSPATH . 'wp-admin/includes/media.php';
        require_once ABSPATH . 'wp-admin/includes/file.php';
        require_once ABSPATH . 'wp-admin/includes/image.php';
    }
}

$initPlugin = new InitPlugin();

// Load admin files
add_action('init', [$initPlugin, 'loadAdminFiles']);

// Handle shortcode adding
add_action('init', [$initPlugin, 'registerShortcode']);

// Create custom cron hook
add_action('aip_cron_hook', [$initPlugin, 'runCron']);

register_activation_hook(__FILE__, [$initPlugin, 'activate']);
register_deactivation_hook(__FILE__, [$initPlugin, 'deactivate']);
