<?php
/**
 * Plugin Name: WP SMTP Service
 * Description: A simple WordPress plugin for configuring SMTP settings.
 * Version: 1.0
 * Author: &lt;fondly&gt;
 * Author URI: https://www.fondly.cz
 * Text Domain: wp-smtp-service
 */
require_once(plugin_dir_path(__FILE__) . '/vendor/autoload.php');

use FondlyCz\WpSmtpService\WPSMTPServicePlugin;

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['send_test_mail'])) {
    // Handle sending the test email
    header('location: ' . plugin_dir_url(__FILE__) . 'test-mail.php');
}

// Instantiate the plugin class
$WPSMTPService = new WPSMTPServicePlugin();
$WPSMTPService->init();
