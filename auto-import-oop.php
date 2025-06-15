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

require_once AIP_PLUGIN_PATH . 'includes/class-admin-settings.php';
require_once AIP_PLUGIN_PATH . 'includes/class-create-shortcode.php';
require_once AIP_PLUGIN_PATH . 'includes/class-fetch-posts.php';
require_once AIP_PLUGIN_PATH . 'includes/class-insert-posts.php';

use AutoImport\CreateShortcode;
use AutoImport\FetchPosts;
use AutoImport\InsertPosts;
use AutoImport\InitAdminSettings;

class InitPlugin
{
    private $fetcher;
    private $inserter;
    private $shortcode;
    private $admin;

    public function __construct()
    {
        $this->fetcher = new FetchPosts();
        $this->inserter = new InsertPosts($this->fetcher);
        $this->shortcode = new CreateShortcode();
        $this->admin = new InitAdminSettings();

        add_action('admin_menu', [$this, 'addOptionPage']);
        add_action('admin_init', [$this, 'addSettings']);
        add_action('init', [$this, 'loadAdminFiles']);
        add_action('init', [$this, 'registerShortcode']);
        add_action('aip_cron_hook', [$this, 'runCron']);
        register_activation_hook(__FILE__, [$this, 'activate']);
        register_deactivation_hook(__FILE__, [$this, 'deactivate']);
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

    public function addOptionPage()
    {
        $this->admin->addOptionPage();
    }

    public function addSettings()
    {
        $this->admin->addSettings();
    }
}

new InitPlugin();
