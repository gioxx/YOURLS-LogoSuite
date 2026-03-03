<?php
/*
Plugin Name: YOURLS Logo Suite
Plugin URI: https://github.com/gioxx/YOURLS-LogoSuite
Description: Customize the YOURLS logo and page title from one plugin.
Version: 1.3.0
Author: Gioxx
Author URI: https://gioxx.org
Text Domain: yourls-logo-suite
Domain Path: /languages
*/

if ( !defined( 'YOURLS_ABSPATH' ) ) die();

define('LOGO_SUITE_VERSION', '1.3.0');
define('LOGO_SUITE_GITHUB_API', 'https://api.github.com/repos/gioxx/YOURLS-LogoSuite/releases/latest');
define('LOGO_SUITE_GITHUB_URL', 'https://github.com/gioxx/YOURLS-LogoSuite/releases/latest');

// Load plugin translation files
yourls_add_action( 'plugins_loaded', 'logo_suite_load_textdomain' );
function logo_suite_load_textdomain() {
    $locale = yourls_get_locale(); // get current locale, e.g. it_IT
    $domain = 'yourls-logo-suite';
    $path = dirname(__FILE__) . '/languages/';

    // try load .mo first
    if ( file_exists( $path . "{$domain}-{$locale}.mo" ) ) {
        yourls_load_textdomain( $domain, $path . "{$domain}-{$locale}.mo" );
    } elseif ( file_exists( $path . "{$domain}-{$locale}.po" ) ) {
        // fallback try load .po
        yourls_load_textdomain( $domain, $path . "{$domain}-{$locale}.po" );
    }
}

// Register plugin settings page in the admin menu
yourls_add_action( 'plugins_loaded', 'logo_suite_add_page' );
function logo_suite_add_page() {
    yourls_register_plugin_page( 'logo_suite', yourls__( 'Branding Settings', 'yourls-logo-suite' ), 'logo_suite_config_page' );
}

function logo_suite_asset_url($relative_path) {
    $relative_path = ltrim((string) $relative_path, '/');
    $plugin_dir = dirname(__FILE__);

    if (function_exists('yourls_plugin_url')) {
        return rtrim((string) yourls_plugin_url($plugin_dir), '/') . '/' . $relative_path;
    }

    if (defined('YOURLS_PLUGINDIRURL')) {
        $slug = basename($plugin_dir);
        return rtrim((string) YOURLS_PLUGINDIRURL, '/') . '/' . $slug . '/' . $relative_path;
    }

    if (defined('YOURLS_SITE') && defined('YOURLS_ABSPATH')) {
        $rel_plugin_dir = str_replace('\\', '/', str_replace((string) YOURLS_ABSPATH, '', $plugin_dir));
        $rel_plugin_dir = trim($rel_plugin_dir, '/');
        return rtrim((string) YOURLS_SITE, '/') . '/' . $rel_plugin_dir . '/' . $relative_path;
    }

    return '';
}

function logo_suite_print_global_assets() {
    $logo_css = logo_suite_asset_url('assets/logo.css');
    if ($logo_css === '') {
        return;
    }

    $version = LOGO_SUITE_VERSION;
    $file = dirname(__FILE__) . '/assets/logo.css';
    if (file_exists($file)) {
        $version = (string) filemtime($file);
    }

    echo '<link rel="stylesheet" href="' . yourls_esc_attr($logo_css) . '?v=' . rawurlencode($version) . '">';
}

yourls_add_action('plugins_loaded', 'logo_suite_register_global_assets');
function logo_suite_register_global_assets() {
    yourls_add_action('admin_head', 'logo_suite_print_global_assets');
}

function logo_suite_print_admin_assets() {
    $admin_css = logo_suite_asset_url('assets/admin.css');
    if ($admin_css !== '') {
        $css_version = LOGO_SUITE_VERSION;
        $css_file = dirname(__FILE__) . '/assets/admin.css';
        if (file_exists($css_file)) {
            $css_version = (string) filemtime($css_file);
        }
        echo '<link rel="stylesheet" href="' . yourls_esc_attr($admin_css) . '?v=' . rawurlencode($css_version) . '">';
    }

    $admin_js = logo_suite_asset_url('assets/admin.js');
    if ($admin_js !== '') {
        $js_version = LOGO_SUITE_VERSION;
        $js_file = dirname(__FILE__) . '/assets/admin.js';
        if (file_exists($js_file)) {
            $js_version = (string) filemtime($js_file);
        }
        echo '<script src="' . yourls_esc_attr($admin_js) . '?v=' . rawurlencode($js_version) . '"></script>';
    }
}

// Plugin settings page content
function logo_suite_config_page() {
    $messages = [];

    // Save settings
    if ( isset($_POST['logo_suite_save_settings']) ) {
        yourls_verify_nonce('logo_suite_config');
        $save_result = logo_suite_save_settings();
        $messages[] = [
            'type' => $save_result['success'] ? 'success' : 'warning',
            'text' => $save_result['text'],
        ];
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
    $logo_width   = (int) yourls_get_option('logo_suite_display_width');
    $logo_height  = (int) yourls_get_option('logo_suite_display_height');
    $custom_title = yourls_get_option('logo_suite_custom_title');
    $keep_suffix  = yourls_get_option('logo_suite_keep_suffix') == 1 ? 'checked' : '';
    $keep_ratio_opt = yourls_get_option('logo_suite_keep_ratio');
    $keep_ratio = ((string) $keep_ratio_opt === '' || (int) $keep_ratio_opt === 1) ? 'checked' : '';
    $nonce_config = yourls_create_nonce('logo_suite_config');
    $nonce_reset  = yourls_create_nonce('logo_suite_reset');

    logo_suite_print_admin_assets();

    // Display plugin header
    echo '<div class="plugin-header">';
    echo '<h2 class="plugin-title">★ ' . yourls__('YOURLS Logo Suite', 'yourls-logo-suite') . '</h2>';
    echo '<p class="plugin-version">' . yourls__('Version:', 'yourls-logo-suite') . ' ' . LOGO_SUITE_VERSION . '</p>';
    echo '</div>';

    // Display admin messages
    foreach ($messages as $msg) {
        echo '<div class="notice notice-' . $msg['type'] . '"><p>' . $msg['text'] . '</p></div>';
    }

    // Begin form
    echo '<form method="post" class="logo-suite-form" enctype="multipart/form-data">';
    echo '<input type="hidden" name="nonce" value="' . $nonce_config . '" />';

    // Page title settings
    echo '<div class="logo-suite-panel">';
    echo '<h3 class="logo-suite-heading"><svg xmlns="http://www.w3.org/2000/svg" class="logo-icon" viewBox="0 0 24 24">
        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" stroke="currentColor" stroke-width="2" fill="none"/>
        <polyline points="14 2 14 8 20 8" stroke="currentColor" stroke-width="2" fill="none"/>
      </svg> ' . yourls__('Page Title Settings', 'yourls-logo-suite') . '</h3>';
    echo '<div class="logo-suite-panel-body">';
    echo '<div class="form-row"><label for="logo_suite_custom_title">' . yourls__('Custom Title', 'yourls-logo-suite') . '</label>';
    echo '<small>' . yourls__('Example: your custom page title.', 'yourls-logo-suite') . '</small>';
    echo '<input type="text" name="logo_suite_custom_title" id="logo_suite_custom_title" value="' . yourls_esc_attr($custom_title) . '" placeholder="' . yourls__('My YOURLS Panel', 'yourls-logo-suite') . '" />';
    echo '</div>';

    echo '<div class="form-row">';
    echo '<label for="logo_suite_keep_suffix"><input type="checkbox" name="logo_suite_keep_suffix" id="logo_suite_keep_suffix" value="1" ' . $keep_suffix . ' /> ';
    echo yourls__('Keep “(YOURLS)” after the custom title', 'yourls-logo-suite') . '</label>';
    echo '</div>';
    echo '</div>';
    echo '</div>';

    // Logo settings
    echo '<div class="logo-suite-panel">';
    echo '<h3 class="logo-suite-heading"><svg xmlns="http://www.w3.org/2000/svg" class="logo-icon" viewBox="0 0 24 24">
        <rect x="3" y="3" width="18" height="18" rx="2" ry="2" stroke="currentColor" stroke-width="2" fill="none"/>
        <path d="M3 9h18" stroke="currentColor" stroke-width="2"/>
    </svg> ' . yourls__('Logo Settings', 'yourls-logo-suite') . '</h3>';
    echo '<div class="logo-settings-layout">';
    echo '<div class="logo-settings-fields">';
    echo '<div class="form-row"><label for="logo_suite_image_url">' . yourls__('Image URL', 'yourls-logo-suite') . '</label>';
    echo '<small>' . yourls__('Example: a direct link to your logo image (PNG, JPG, SVG).', 'yourls-logo-suite') . '</small>';
    echo '<input type="text" name="logo_suite_image_url" id="logo_suite_image_url" value="' . yourls_esc_attr($logo_url) . '" placeholder="https://example.com/logo.png" />';
    echo '</div>';
    echo '<div class="form-row"><label for="logo_suite_image_file">' . yourls__('Upload image', 'yourls-logo-suite') . '</label>';
    echo '<small>' . yourls__('Optional: upload PNG, JPG, GIF or WEBP from your computer (max 5 MB).', 'yourls-logo-suite') . '</small>';
    echo '<input type="file" name="logo_suite_image_file" id="logo_suite_image_file" accept=".png,.jpg,.jpeg,.gif,.webp,image/png,image/jpeg,image/gif,image/webp" />';
    echo '<div id="logo-upload-alert" class="logo-upload-alert logo-upload-alert-hidden">📌 ' . yourls__('After selecting a file, click "Save Settings" to complete logo upload.', 'yourls-logo-suite') . '</div>';
    echo '</div>';

    echo '<div class="form-row"><label for="logo_suite_image_alt">' . yourls__('ALT Tag', 'yourls-logo-suite') . '</label>';
    echo '<small>' . yourls__('Example: descriptive text for accessibility.', 'yourls-logo-suite') . '</small>';
    echo '<input type="text" name="logo_suite_image_alt" id="logo_suite_image_alt" value="' . yourls_esc_attr($logo_alt) . '" placeholder="' . yourls__('My Custom Logo', 'yourls-logo-suite') . '" />';
    echo '</div>';

    echo '<div class="form-row"><label for="logo_suite_image_title">' . yourls__('Title Attribute', 'yourls-logo-suite') . '</label>';
    echo '<small>' . yourls__('Example: tooltip text shown on hover.', 'yourls-logo-suite') . '</small>';
    echo '<input type="text" name="logo_suite_image_title" id="logo_suite_image_title" value="' . yourls_esc_attr($logo_title) . '" placeholder="' . yourls__('Back to Dashboard', 'yourls-logo-suite') . '" />';
    echo '</div>';
    echo '</div>';

    echo '<div class="logo-settings-preview">';
    echo '<label class="logo-preview-title">' . yourls__('Logo Preview', 'yourls-logo-suite') . '</label>';
    echo '<div id="logo-preview-wrapper">';
    if ($logo_url) {
        echo '<img id="logo-preview" class="logo-preview-image" src="' . yourls_esc_url($logo_url) . '" alt="" onerror="logoPreviewError()" onload="logoPreviewSuccess()" />';
        echo '<div id="logo-preview-error" class="logo-preview-error logo-preview-hidden">' . yourls__('Unable to load the image. Please check the URL.', 'yourls-logo-suite') . '</div>';
    } else {
        echo '<img id="logo-preview" class="logo-preview-image logo-preview-hidden" src="" alt="" />';
        echo '<div id="logo-preview-error" class="logo-preview-error logo-preview-hidden">' . yourls__('Unable to load the image. Please check the URL.', 'yourls-logo-suite') . '</div>';
    }
    echo '</div>';
    echo '<div class="logo-preview-controls">';
    echo '<div class="logo-preview-size-row">';
    echo '<div class="logo-preview-control">';
    echo '<label for="logo_suite_display_width">' . yourls__('Width (px)', 'yourls-logo-suite') . '</label>';
    echo '<input type="number" name="logo_suite_display_width" id="logo_suite_display_width" min="1" step="1" value="' . ($logo_width > 0 ? (int) $logo_width : '') . '" />';
    echo '</div>';
    echo '<div class="logo-preview-control">';
    echo '<label for="logo_suite_display_height">' . yourls__('Height (px)', 'yourls-logo-suite') . '</label>';
    echo '<input type="number" name="logo_suite_display_height" id="logo_suite_display_height" min="1" step="1" value="' . ($logo_height > 0 ? (int) $logo_height : '') . '" />';
    echo '</div>';
    echo '</div>';
    echo '<div class="logo-preview-control-check">';
    echo '<label for="logo_suite_keep_ratio"><input type="checkbox" name="logo_suite_keep_ratio" id="logo_suite_keep_ratio" value="1" ' . $keep_ratio . ' /> ' . yourls__('Keep aspect ratio', 'yourls-logo-suite') . '</label>';
    echo '</div>';
    echo '<small class="logo-preview-control-help">' . yourls__('Tip: set one side only and keep aspect ratio enabled for proportional resize.', 'yourls-logo-suite') . '</small>';
    echo '</div>';
    echo '</div>';
    echo '</div>';
    echo '</div>';

    // Help/Info box
    echo '<div class="logo-suite-info-box">';
    echo '<h4 class="logo-suite-info-title"><span class="logo-suite-info-icon">i</span>' . yourls__('Notes', 'yourls-logo-suite') . '</h4>';
    echo '<ul class="logo-suite-info-list">';
    echo '<li><strong>' . yourls__('Save Settings', 'yourls-logo-suite') . '</strong>: ' . yourls__('Saves the changes you made to logo and title.', 'yourls-logo-suite') . '</li>';
    echo '<li><strong>' . yourls__('Reset to Default', 'yourls-logo-suite') . '</strong>: ' . yourls__('Restores original YOURLS logo and title, removing all customizations.', 'yourls-logo-suite') . '</li>';
    echo '</ul>';
    echo '</div>';

    // Buttons
    echo '<div class="logo-suite-actions">';
    echo '<input type="submit" name="logo_suite_save_settings" value="💾 ' . yourls__('Save Settings', 'yourls-logo-suite') . '" class="button button-primary" />';
    echo ' ';
    echo '<input type="submit" name="logo_suite_reset_settings" value="↩ ' . yourls__('Reset to Default', 'yourls-logo-suite') . '" class="button" onclick="return confirm(\'' . yourls__('Are you sure you want to reset all settings?', 'yourls-logo-suite') . '\');" formnovalidate />';
    echo '<input type="hidden" name="nonce_reset" value="' . $nonce_reset . '" />';
    echo '</div>';

    echo '<div class="plugin-footer">';
    echo '<a href="https://github.com/gioxx/YOURLS-LogoSuite" target="_blank" rel="noopener noreferrer">';
    echo '<img src="https://github.githubassets.com/favicons/favicon.png" class="github-icon" alt="GitHub Icon" />';
    echo 'YOURLS Logo Suite</a><br>';
    echo '❤️ Lovingly developed by the usually-on-vacation brain cell of ';
    echo '<a href="https://github.com/gioxx" target="_blank" rel="noopener noreferrer">Gioxx</a> – ';
    echo '<a href="https://gioxx.org" target="_blank" rel="noopener noreferrer">Gioxx\'s Wall</a>';
    echo '</div>';

    echo '</form>';
}

// Save settings to database
function logo_suite_save_settings() {
    $logo_url = trim($_POST['logo_suite_image_url'] ?? '');
    $upload_result = logo_suite_handle_logo_upload('logo_suite_image_file');

    if ($upload_result['status'] === 'error') {
        return ['success' => false, 'text' => $upload_result['message']];
    }

    if ($upload_result['status'] === 'uploaded') {
        $logo_url = $upload_result['url'];
    }

    yourls_update_option('logo_suite_image_url', $logo_url);
    yourls_update_option('logo_suite_image_alt', trim($_POST['logo_suite_image_alt'] ?? ''));
    yourls_update_option('logo_suite_image_title', trim($_POST['logo_suite_image_title'] ?? ''));
    $display_width = isset($_POST['logo_suite_display_width']) ? (int) $_POST['logo_suite_display_width'] : 0;
    $display_height = isset($_POST['logo_suite_display_height']) ? (int) $_POST['logo_suite_display_height'] : 0;
    yourls_update_option('logo_suite_display_width', $display_width > 0 ? $display_width : '');
    yourls_update_option('logo_suite_display_height', $display_height > 0 ? $display_height : '');
    yourls_update_option('logo_suite_keep_ratio', isset($_POST['logo_suite_keep_ratio']) ? 1 : 0);
    yourls_update_option('logo_suite_custom_title', trim($_POST['logo_suite_custom_title'] ?? ''));
    yourls_update_option('logo_suite_keep_suffix', isset($_POST['logo_suite_keep_suffix']) ? 1 : 0);

    if ($upload_result['status'] === 'uploaded') {
        return ['success' => true, 'text' => yourls__('Logo image uploaded successfully. URL updated automatically.', 'yourls-logo-suite')];
    }

    return ['success' => true, 'text' => yourls__('Settings updated successfully!', 'yourls-logo-suite')];
}

function logo_suite_upload_dir_path() {
    return rtrim((string) YOURLS_USERDIR, '/\\') . '/uploads/logo-suite';
}

function logo_suite_upload_base_url() {
    if (defined('YOURLS_USERURL')) {
        return rtrim((string) YOURLS_USERURL, '/') . '/uploads/logo-suite';
    }

    return rtrim((string) YOURLS_SITE, '/') . '/user/uploads/logo-suite';
}

function logo_suite_handle_logo_upload($field_name) {
    if (!isset($_FILES[$field_name]) || !is_array($_FILES[$field_name])) {
        return ['status' => 'none'];
    }

    $file = $_FILES[$field_name];
    if (!isset($file['error']) || $file['error'] === UPLOAD_ERR_NO_FILE) {
        return ['status' => 'none'];
    }

    if ($file['error'] !== UPLOAD_ERR_OK) {
        return ['status' => 'error', 'message' => yourls__('Upload failed: no valid file received.', 'yourls-logo-suite')];
    }

    if (!isset($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
        return ['status' => 'error', 'message' => yourls__('Upload failed: no valid file received.', 'yourls-logo-suite')];
    }

    $max_size = 5 * 1024 * 1024;
    if ((int) $file['size'] > $max_size) {
        return ['status' => 'error', 'message' => yourls__('Upload failed: file is too large (max 5 MB).', 'yourls-logo-suite')];
    }

    $img_info = @getimagesize($file['tmp_name']);
    if (!$img_info || !isset($img_info['mime'])) {
        return ['status' => 'error', 'message' => yourls__('Upload failed: unsupported image format. Allowed: PNG, JPG, GIF, WEBP.', 'yourls-logo-suite')];
    }

    $mime = strtolower((string) $img_info['mime']);
    $allowed_mimes = [
        'image/png'  => 'png',
        'image/jpeg' => 'jpg',
        'image/gif'  => 'gif',
        'image/webp' => 'webp',
    ];

    if (!isset($allowed_mimes[$mime])) {
        return ['status' => 'error', 'message' => yourls__('Upload failed: unsupported image format. Allowed: PNG, JPG, GIF, WEBP.', 'yourls-logo-suite')];
    }

    $upload_dir = logo_suite_upload_dir_path();
    if (!is_dir($upload_dir) && !@mkdir($upload_dir, 0755, true)) {
        return ['status' => 'error', 'message' => yourls__('Upload failed: could not create upload directory.', 'yourls-logo-suite')];
    }

    if (!is_writable($upload_dir)) {
        return ['status' => 'error', 'message' => yourls__('Upload failed: upload directory is not writable.', 'yourls-logo-suite')];
    }

    $filename = 'logo-suite-' . date('YmdHis') . '-' . substr(md5(uniqid('', true)), 0, 8) . '.' . $allowed_mimes[$mime];
    $destination = $upload_dir . '/' . $filename;

    if (!@move_uploaded_file($file['tmp_name'], $destination)) {
        return ['status' => 'error', 'message' => yourls__('Upload failed while moving the file to destination.', 'yourls-logo-suite')];
    }

    $url = logo_suite_upload_base_url() . '/' . rawurlencode($filename);
    return ['status' => 'uploaded', 'url' => $url];
}

// Reset settings to defaults
function logo_suite_reset_settings() {
    yourls_delete_option('logo_suite_image_url');
    yourls_delete_option('logo_suite_image_alt');
    yourls_delete_option('logo_suite_image_title');
    yourls_delete_option('logo_suite_display_width');
    yourls_delete_option('logo_suite_display_height');
    yourls_delete_option('logo_suite_keep_ratio');
    yourls_delete_option('logo_suite_custom_title');
    yourls_delete_option('logo_suite_keep_suffix');
}

function logo_suite_get_rendered_logo_style() {
    $width = (int) yourls_get_option('logo_suite_display_width');
    $height = (int) yourls_get_option('logo_suite_display_height');
    $keep_ratio_opt = yourls_get_option('logo_suite_keep_ratio');
    $keep_ratio = ((string) $keep_ratio_opt === '' || (int) $keep_ratio_opt === 1);
    $has_custom_size = ($width > 0 || $height > 0);

    $style = 'border:none;';

    if ($keep_ratio) {
        if ($width > 0 && $height > 0) {
            $style .= "width:{$width}px;height:{$height}px;object-fit:contain;";
        } elseif ($width > 0) {
            $style .= "width:{$width}px;height:auto;";
        } elseif ($height > 0) {
            $style .= "height:{$height}px;width:auto;";
        } else {
            $style .= 'width:auto;height:auto;';
        }
    } else {
        if ($width > 0) {
            $style .= "width:{$width}px;";
        }
        if ($height > 0) {
            $style .= "height:{$height}px;";
        }
        if ($width <= 0 && $height <= 0) {
            $style .= 'width:auto;height:auto;';
        }
    }

    $style .= $has_custom_size ? 'max-height:none;' : 'max-height:180px;';

    return $style;
}

// Inject a wrapper before the original logo (hidden with inline fallback)
yourls_add_filter('pre_html_logo', 'logo_suite_hide_original_logo');
function logo_suite_hide_original_logo() {
    $custom_logo = yourls_get_option('logo_suite_image_url');
    if ($custom_logo) {
        // Keep inline fallback to guarantee hiding on every admin page.
        echo '<span class="logo-suite-hidden" style="display:none;">';
    }
}

// Inject </span> and custom logo
yourls_add_filter('html_logo', 'logo_suite_custom_logo');
function logo_suite_custom_logo() {
    $custom_logo = yourls_get_option('logo_suite_image_url');
    if (!$custom_logo) {
        return; // It shows nothing if there is no custom logo
    }

    $alt        = yourls_esc_attr(yourls_get_option('logo_suite_image_alt'));
    $title_attr = yourls_esc_attr(yourls_get_option('logo_suite_image_title'));
    $admin_url  = yourls_admin_url('index.php');
    $logo_style = logo_suite_get_rendered_logo_style();
    echo '</span>';
    echo '<h1 id="yourls_logo_custom"><a href="' . $admin_url . '" class="logo-suite-custom-link" title="' . $title_attr . '">';
    echo '<img src="' . yourls_esc_url($custom_logo) . '" alt="' . $alt . '" title="' . $title_attr . '" class="logo-suite-custom-image" style="' . yourls_esc_attr($logo_style) . '" />';
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

// Check for updates and show notice if a new version is available
yourls_add_action( 'plugins_loaded', 'logo_suite_update_check_setup' );
function logo_suite_update_check_setup() {
    // Hook notification in all admin pages
    yourls_add_action( 'admin_notices', 'logo_suite_show_update_notice' );
    // Hook badge in plugin page title
    yourls_add_filter( 'plugin_page_title_logo_suite', 'logo_suite_page_title_with_badge' );
}

function logo_suite_show_update_notice() {
    static $checked = false;
    static $update_available = false;
    static $latest_version = '';
    static $release_url = '';

    if ($checked) {
        if ($update_available) {
            echo '<div class="notice notice-info logo-suite-update-notice">';
            echo '🆕 <strong>YOURLS Logo Suite</strong>: ' . yourls__('New version available:', 'yourls-logo-suite') . ' <strong>' . $latest_version . '</strong>! ';
            echo '<a href="' . $release_url . '" target="_blank">' . yourls__('View details on GitHub', 'yourls-logo-suite') . '</a>';
            echo '</div>';
        }
        return;
    }

    $checked = true;

    $response = logo_suite_remote_get(LOGO_SUITE_GITHUB_API);

    if (!$response || !isset($response['tag_name'])) return;

    $latest_version = ltrim($response['tag_name'], 'v');

    if ( version_compare($latest_version, LOGO_SUITE_VERSION, '>') ) {
        $update_available = true;
        $release_url = $response['html_url'];

        echo '<div class="notice notice-info logo-suite-update-notice">';
        echo '🆕 <strong>YOURLS Logo Suite</strong>: ' . yourls__('New version available:', 'yourls-logo-suite') . ' <strong>' . $latest_version . '</strong>! ';
        echo '<a href="' . $release_url . '" target="_blank">' . yourls__('View details on GitHub', 'yourls-logo-suite') . '</a>';
        echo '</div>';
    }
}

function logo_suite_page_title_with_badge( $title ) {
    // Show a small badge next to plugin title if update is available
    static $update_available = null;
    static $latest_version = '';

    if ( $update_available === null ) {
        $response = logo_suite_remote_get(LOGO_SUITE_GITHUB_API);
        if ( !$response || !isset($response['tag_name']) ) {
            $update_available = false;
        } else {
            $latest_version = ltrim($response['tag_name'], 'v');
            $update_available = version_compare($latest_version, LOGO_SUITE_VERSION, '>');
        }
    }

    if ( $update_available ) {
        $badge_text = yourls__('Update Available', 'yourls-logo-suite');
        $badge = ' <span class="logo-suite-update-badge">' . $badge_text . '</span>';
        return $title . $badge;
    }    

    return $title;
}

function logo_suite_remote_get($url) {
    $ch = curl_init();

    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_USERAGENT => 'YOURLS-LogoSuite',
        CURLOPT_TIMEOUT => 5,
    ]);

    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($http_code !== 200 || $response === false) {
        return null;
    }

    return json_decode($response, true);
}
