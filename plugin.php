<?php
/*
Plugin Name: YOURLS Logo Suite
Plugin URI: https://github.com/gioxx/YOURLS-LogoSuite
Description: Customize the YOURLS admin logo and page title from one plugin.
Version: 1.0
Author: Gioxx
Author URI: https://gioxx.org
*/

if ( !defined( 'YOURLS_ABSPATH' ) ) die();

// Load plugin translation files
yourls_add_action( 'plugins_loaded', 'logo_suite_load_textdomain' );
function logo_suite_load_textdomain() {
    $locale = yourls_get_locale(); // get current locale, e.g. it_IT
    $domain = 'yourls-logo-suite';
    $path = dirname(__FILE__) . '/languages/';

    // try load .mo first
    if ( file_exists( $path . "{$domain}-{$locale}.mo" ) ) {
        yourls_load_textdomain( $domain, $path . "{$domain}-{$locale}.mo" );
    }
    // fallback try load .po (non compilato)
    elseif ( file_exists( $path . "{$domain}-{$locale}.po" ) ) {
        yourls_load_textdomain( $domain, $path . "{$domain}-{$locale}.po" );
    }
}

// Register plugin settings page in the admin menu
yourls_add_action( 'plugins_loaded', 'logo_suite_add_page' );
function logo_suite_add_page() {
    yourls_register_plugin_page( 'logo_suite', yourls__( 'YOURLS Logo Suite Settings', 'yourls-logo-suite' ), 'logo_suite_config_page' );
}

// Plugin settings page content
function logo_suite_config_page() {
    $messages = [];

    // Save settings
    if ( isset($_POST['logo_suite_save_settings']) ) {
        yourls_verify_nonce('logo_suite_config');
        logo_suite_save_settings();
        $messages[] = ['type' => 'success', 'text' => yourls__('Settings updated successfully!', 'yourls-logo-suite')];
    }

    // Reset settings
    if ( isset($_POST['logo_suite_reset_settings']) ) {
        yourls_verify_nonce('logo_suite_reset', $_POST['nonce_reset']);
        logo_suite_reset_settings();
        $messages[] = ['type' => 'warning', 'text' => yourls__('Settings reset to default.', 'yourls-logo-suite')];
    }

    // Retrieve stored options
    $logo_url     = yourls_get_option('logo_suite_image_url');
    $logo_alt     = yourls_get_option('logo_suite_image_alt');
    $logo_title   = yourls_get_option('logo_suite_image_title');
    $custom_title = yourls_get_option('logo_suite_custom_title');
    $keep_suffix  = yourls_get_option('logo_suite_keep_suffix') == 1 ? 'checked' : '';
    $nonce_config = yourls_create_nonce('logo_suite_config');
    $nonce_reset  = yourls_create_nonce('logo_suite_reset');

    // Start output
    echo '<style>
        h2.plugin-title {
            font-size: 2.2em;
            color: #2c3e50;
            font-weight: 700;
            letter-spacing: 1.5px;
            margin-bottom: 30px;
            font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
        }
        .logo-suite-section { margin: 30px 0; padding: 20px; border: 1px solid #ddd; background: #fafafa; border-radius: 5px; }
        .logo-suite-section h3 { margin-top: 0; font-weight: 600; }
        .form-row { margin-bottom: 15px; }
        .form-row label { display: block; font-weight: bold; margin-bottom: 5px; }
        input[type="text"] { width: 100%; max-width: 600px; }
        .form-row small {
            display: block;
            color: #666;
            margin-top: 3px;
            font-size: 0.9em;
        }
        .notice-success { background: #e6ffed; border-left: 4px solid #46b450; padding: 10px; }
        .notice-warning { background: #fff8e5; border-left: 4px solid #ffb900; padding: 10px; }
        svg.logo-icon {
            vertical-align: middle;
            margin-right: 8px;
            stroke: #444;
            stroke-width: 2;
            fill: none;
            stroke-linecap: round;
            stroke-linejoin: round;
            width: 20px;
            height: 20px;
        }
    </style>';

    echo '<h2 class="plugin-title">' . yourls__('YOURLS Logo Suite', 'yourls-logo-suite') . '</h2>';

    // Display admin messages
    foreach ($messages as $msg) {
        echo '<div class="notice notice-' . $msg['type'] . '"><p>' . $msg['text'] . '</p></div>';
    }

    // Begin form
    echo '<form method="post">';
    echo '<input type="hidden" name="nonce" value="' . $nonce_config . '" />';

    // Logo settings
    echo '<div class="logo-suite-section">';
    echo '<h3><svg xmlns="http://www.w3.org/2000/svg" class="logo-icon" viewBox="0 0 24 24">
        <rect x="3" y="3" width="18" height="18" rx="2" ry="2" stroke="currentColor" stroke-width="2" fill="none"/>
        <path d="M3 9h18" stroke="currentColor" stroke-width="2"/>
    </svg> ' . yourls__('Logo Settings', 'yourls-logo-suite') . '</h3>';
    echo '<div class="form-row"><label>' . yourls__('Image URL', 'yourls-logo-suite') . '</label>';
    echo '<input type="text" name="logo_suite_image_url" value="' . yourls_esc_attr($logo_url) . '" placeholder="https://example.com/logo.png" />';
    echo '<small>' . yourls__('Example: a direct link to your logo image (PNG, JPG, SVG).', 'yourls-logo-suite') . '</small></div>';

    echo '<div class="form-row"><label>' . yourls__('ALT Tag', 'yourls-logo-suite') . '</label>';
    echo '<input type="text" name="logo_suite_image_alt" value="' . yourls_esc_attr($logo_alt) . '" placeholder="' . yourls__('My Custom Logo', 'yourls-logo-suite') . '" />';
    echo '<small>' . yourls__('Example: descriptive text for accessibility.', 'yourls-logo-suite') . '</small></div>';

    echo '<div class="form-row"><label>' . yourls__('Title Attribute', 'yourls-logo-suite') . '</label>';
    echo '<input type="text" name="logo_suite_image_title" value="' . yourls_esc_attr($logo_title) . '" placeholder="' . yourls__('Back to Dashboard', 'yourls-logo-suite') . '" />';
    echo '<small>' . yourls__('Example: tooltip text shown on hover.', 'yourls-logo-suite') . '</small></div>';
    echo '</div>';

    // Page title settings
    echo '<div class="logo-suite-section">';
    echo '<h3><svg xmlns="http://www.w3.org/2000/svg" class="logo-icon" viewBox="0 0 24 24">
        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" stroke="currentColor" stroke-width="2" fill="none"/>
        <polyline points="14 2 14 8 20 8" stroke="currentColor" stroke-width="2" fill="none"/>
      </svg> ' . yourls__('Page Title Settings', 'yourls-logo-suite') . '</h3>';
    echo '<div class="form-row"><label>' . yourls__('Custom Title', 'yourls-logo-suite') . '</label>';
    echo '<input type="text" name="logo_suite_custom_title" value="' . yourls_esc_attr($custom_title) . '" placeholder="' . yourls__('My YOURLS Panel', 'yourls-logo-suite') . '" />';
    echo '<small>' . yourls__('Example: your custom page title.', 'yourls-logo-suite') . '</small></div>';

    echo '<div class="form-row">';
    echo '<label><input type="checkbox" name="logo_suite_keep_suffix" value="1" ' . $keep_suffix . ' /> ';
    echo yourls__('Keep “(YOURLS)” after the custom title', 'yourls-logo-suite') . '</label>';
    echo '</div>';
    echo '</div>';

    // Help/Info box
    echo '<div style="background-color:#f0f7ff;border:1px solid #b3d4fc;color:#31708f;padding:10px 15px;margin-bottom:20px;border-radius:4px;font-size:0.9em;">';
    echo '<strong>' . yourls__('Note:', 'yourls-logo-suite') . '</strong><br>';
    echo '- <em>' . yourls__('Save Settings', 'yourls-logo-suite') . '</em>: ' . yourls__('Saves the changes you made to logo and title.', 'yourls-logo-suite') . '<br>';
    echo '- <em>' . yourls__('Reset to Default', 'yourls-logo-suite') . '</em>: ' . yourls__('Restores original YOURLS logo and title, removing all customizations.', 'yourls-logo-suite');
    echo '</div>';

    // Buttons
    echo '<div style="margin-top:20px;">';
    echo '<input type="submit" name="logo_suite_save_settings" value="💾 ' . yourls__('Save Settings', 'yourls-logo-suite') . '" class="button button-primary" />';
    echo ' ';
    echo '<input type="submit" name="logo_suite_reset_settings" value="↩ ' . yourls__('Reset to Default', 'yourls-logo-suite') . '" class="button" onclick="return confirm(\'' . yourls__('Are you sure you want to reset all settings?', 'yourls-logo-suite') . '\');" formnovalidate />';
    echo '<input type="hidden" name="nonce_reset" value="' . $nonce_reset . '" />';
    echo '</div>';

    echo '</form>';
}

// Save settings to database
function logo_suite_save_settings() {
    yourls_update_option('logo_suite_image_url', trim($_POST['logo_suite_image_url'] ?? ''));
    yourls_update_option('logo_suite_image_alt', trim($_POST['logo_suite_image_alt'] ?? ''));
    yourls_update_option('logo_suite_image_title', trim($_POST['logo_suite_image_title'] ?? ''));
    yourls_update_option('logo_suite_custom_title', trim($_POST['logo_suite_custom_title'] ?? ''));
    yourls_update_option('logo_suite_keep_suffix', isset($_POST['logo_suite_keep_suffix']) ? 1 : 0);
}

// Reset settings to defaults
function logo_suite_reset_settings() {
    yourls_delete_option('logo_suite_image_url');
    yourls_delete_option('logo_suite_image_alt');
    yourls_delete_option('logo_suite_image_title');
    yourls_delete_option('logo_suite_custom_title');
    yourls_delete_option('logo_suite_keep_suffix');
}

// Override the admin logo
yourls_add_filter( 'html_logo', 'logo_suite_custom_logo' );
function logo_suite_custom_logo() {
    $logo_url = yourls_get_option('logo_suite_image_url');
    if ( !$logo_url )
        return;

    $logo_url   = yourls_esc_url( $logo_url );
    $alt        = yourls_esc_attr( yourls_get_option('logo_suite_image_alt') );
    $title_attr = yourls_esc_attr( yourls_get_option('logo_suite_image_title') );
    $admin_url  = yourls_admin_url('index.php');

    echo '<h1 id="yourls_logo"><a href="' . $admin_url . '" title="' . $title_attr . '">';
    echo '<img src="' . $logo_url . '" alt="' . $alt . '" title="' . $title_attr . '" style="border: none; max-width: 100px;" />';
    echo '</a></h1>';
}

// Override the page title
yourls_add_filter( 'html_title', 'logo_suite_custom_title' );
function logo_suite_custom_title( $title ) {
    $custom_title = yourls_get_option('logo_suite_custom_title');
    if ( !$custom_title )
        return $title;

    return yourls_get_option('logo_suite_keep_suffix') == 1
        ? $custom_title . ' (YOURLS)'
        : $custom_title;
}
