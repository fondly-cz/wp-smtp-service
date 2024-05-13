<?php


echo "test";
ini_set('display_errors', 1);
//require_once(__DIR__ . '/classes/WPSMTPServicePlugin.php');

require_once(__DIR__ . '/vendor/autoload.php');

use FondlyCz\WpSmtpService\WPSMTPServicePlugin;


$service = new WPSMTPServicePlugin();
$service->send_test_email();