<?php
/*
Plugin Name: YOURLS Logo Suite
Plugin URI: https://github.com/gioxx/YOURLS-LogoSuite
Description: Customize the YOURLS logo and page title from one plugin.
Version: 1.3.4
Author: Gioxx
Author URI: https://gioxx.org
Text Domain: yourls-logo-suite
Domain Path: /languages
*/

if ( !defined( 'YOURLS_ABSPATH' ) ) die();

define( 'LOGO_SUITE_VERSION',    '1.3.4' );
define( 'LOGO_SUITE_GITHUB_API', 'https://api.github.com/repos/gioxx/YOURLS-LogoSuite/releases/latest' );
define( 'LOGO_SUITE_GITHUB_URL', 'https://github.com/gioxx/YOURLS-LogoSuite/releases/latest' );
define( 'LOGO_SUITE_PLUGIN_DIR', dirname( __FILE__ ) );

$ls_inc = LOGO_SUITE_PLUGIN_DIR . '/inc/';
require_once $ls_inc . 'helpers.php';
require_once $ls_inc . 'update-check.php';
require_once $ls_inc . 'logo-hooks.php';
require_once $ls_inc . 'admin-page.php';

yourls_add_filter( 'plugin_page_title_logo_suite', 'logo_suite_page_title_with_badge' );
