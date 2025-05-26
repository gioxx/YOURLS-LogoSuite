<?php
/*
Plugin Name: YOURLS Logo Suite
Plugin URI: https://github.com/gioxx/YOURLS-LogoSuite
Description: Customize the YOURLS admin logo and page title from one plugin.
Version: 1.2.2
Author: Gioxx
Author URI: https://gioxx.org
Text Domain: yourls-logo-suite
Domain Path: /languages
*/

if ( !defined( 'YOURLS_ABSPATH' ) ) die();

define('LOGO_SUITE_VERSION', '1.2.2');
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
        .plugin-header {
            display: flex;
            flex-direction: column;
            align-items: flex-start;
        }
        .plugin-title {
            margin: 0;
            padding: 0;
            font-family: \'Arial\', sans-serif;
            font-size: 2em;
            font-weight: bold;
            background: -webkit-linear-gradient(#0073aa, #00a8e6);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        .plugin-version {
            margin: 0;
            padding: 0;
            font-size: 0.8em;
            color: #666;
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
        input[type="submit"].button {
            padding: 10px 18px;
            font-size: 12px;
            border-radius: 5px;
            cursor: pointer;
        }
        .plugin-footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
            font-size: 0.9em;
            color: #666;
            text-align: center;
            opacity: 0.85;
        }
        .plugin-footer a {
            color: #0073aa;
            text-decoration: none;
        }
        .plugin-footer a:hover {
            text-decoration: underline;
        }
        .plugin-footer .github-icon {
            vertical-align: middle;
            width: 16px;
            height: 16px;
            margin-right: 4px;
            display: inline-block;
        }
    </style>';

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
    echo '<form method="post">';
    echo '<input type="hidden" name="nonce" value="' . $nonce_config . '" />';

    // Logo settings
    echo '<div class="logo-suite-section">';
    echo '<h3><svg xmlns="http://www.w3.org/2000/svg" class="logo-icon" viewBox="0 0 24 24">
        <rect x="3" y="3" width="18" height="18" rx="2" ry="2" stroke="currentColor" stroke-width="2" fill="none"/>
        <path d="M3 9h18" stroke="currentColor" stroke-width="2"/>
    </svg> ' . yourls__('Logo Settings', 'yourls-logo-suite') . '</h3>';
    echo '<div class="form-row"><label for="logo_suite_image_url">' . yourls__('Image URL', 'yourls-logo-suite') . '</label>';
    echo '<input type="text" name="logo_suite_image_url" id="logo_suite_image_url" value="' . yourls_esc_attr($logo_url) . '" placeholder="https://example.com/logo.png" oninput="updateLogoPreview()" />';
    echo '<small>' . yourls__('Example: a direct link to your logo image (PNG, JPG, SVG).', 'yourls-logo-suite') . '</small></div>';

    // Logo preview
    echo '<div id="logo-preview-wrapper" style="margin-top:10px; margin-bottom:18px;">';
    if ($logo_url) {
        echo '<img id="logo-preview" src="' . yourls_esc_url($logo_url) . '" alt="" style="max-height:60px;border:1px solid #ccc;padding:5px;background:#fff;" onerror="logoPreviewError()" onload="logoPreviewSuccess()" />';
        echo '<div id="logo-preview-error" style="color:red;display:none;margin-top:8px;font-size:0.9em;">' . yourls__('Unable to load the image. Please check the URL.', 'yourls-logo-suite') . '</div>';
    } else {
        echo '<img id="logo-preview" src="" alt="" style="display:none;max-height:60px;border:1px solid #ccc;padding:5px;background:#fff;" />';
    }
    echo '</div>';

    echo '<div class="form-row"><label for="logo_suite_image_alt">' . yourls__('ALT Tag', 'yourls-logo-suite') . '</label>';
    echo '<input type="text" name="logo_suite_image_alt" id="logo_suite_image_alt" value="' . yourls_esc_attr($logo_alt) . '" placeholder="' . yourls__('My Custom Logo', 'yourls-logo-suite') . '" />';
    echo '<small>' . yourls__('Example: descriptive text for accessibility.', 'yourls-logo-suite') . '</small></div>';

    echo '<div class="form-row"><label for="logo_suite_image_title">' . yourls__('Title Attribute', 'yourls-logo-suite') . '</label>';
    echo '<input type="text" name="logo_suite_image_title" id="logo_suite_image_title" value="' . yourls_esc_attr($logo_title) . '" placeholder="' . yourls__('Back to Dashboard', 'yourls-logo-suite') . '" />';
    echo '<small>' . yourls__('Example: tooltip text shown on hover.', 'yourls-logo-suite') . '</small></div>';
    echo '</div>';

    // Page title settings
    echo '<div class="logo-suite-section">';
    echo '<h3><svg xmlns="http://www.w3.org/2000/svg" class="logo-icon" viewBox="0 0 24 24">
        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" stroke="currentColor" stroke-width="2" fill="none"/>
        <polyline points="14 2 14 8 20 8" stroke="currentColor" stroke-width="2" fill="none"/>
      </svg> ' . yourls__('Page Title Settings', 'yourls-logo-suite') . '</h3>';
    echo '<div class="form-row"><label for="logo_suite_custom_title">' . yourls__('Custom Title', 'yourls-logo-suite') . '</label>';
    echo '<input type="text" name="logo_suite_custom_title" id="logo_suite_custom_title" value="' . yourls_esc_attr($custom_title) . '" placeholder="' . yourls__('My YOURLS Panel', 'yourls-logo-suite') . '" />';
    echo '<small>' . yourls__('Example: your custom page title.', 'yourls-logo-suite') . '</small></div>';

    echo '<div class="form-row">';
    echo '<label for="logo_suite_keep_suffix"><input type="checkbox" name="logo_suite_keep_suffix" id="logo_suite_keep_suffix" value="1" ' . $keep_suffix . ' /> ';
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

    // Changelog section
    echo '<div class="logo-suite-section" style="margin-top: 40px;">';
    echo '<h3>' . yourls__('Changelog (Latest Release)', 'yourls-logo-suite') . '</h3>';
    echo logo_suite_get_latest_changelog();
    echo '</div>';

    echo '<div class="plugin-footer">';
    echo '<a href="https://github.com/gioxx/YOURLS-LogoSuite" target="_blank" rel="noopener noreferrer">';
    echo '<img src="https://github.githubassets.com/favicons/favicon.png" class="github-icon" alt="GitHub Icon" />';
    echo 'YOURLS Logo Suite</a><br>';
    echo '❤️ Lovingly developed by the usually-on-vacation brain cell of ';
    echo '<a href="https://github.com/gioxx" target="_blank" rel="noopener noreferrer">Gioxx</a> – ';
    echo '<a href="https://gioxx.org" target="_blank" rel="noopener noreferrer">Gioxx\'s Wall</a>';
    echo '</div>';

    echo <<<JS
    <script>
    function updateLogoPreview() {
        const input = document.getElementById('logo_suite_image_url');
        const preview = document.getElementById('logo-preview');
        const error = document.getElementById('logo-preview-error');

        const url = input.value.trim();
        if (url) {
            preview.src = url;
            preview.style.display = 'inline-block';
        } else {
            preview.src = '';
            preview.style.display = 'none';
            error.style.display = 'none';
        }
    }

    function logoPreviewError() {
        const error = document.getElementById('logo-preview-error');
        error.style.display = 'block';
    }

    function logoPreviewSuccess() {
        const error = document.getElementById('logo-preview-error');
        error.style.display = 'none';
    }
    </script>
    JS;

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

// Inject <span style="display:none"> before the original logo (hides it)
yourls_add_filter('pre_html_logo', 'logo_suite_hide_original_logo');
function logo_suite_hide_original_logo() {
    $custom_logo = yourls_get_option('logo_suite_image_url');
    if ($custom_logo) {
        echo '<span style="display:none">';
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

    static $style_printed = false;
    if (!$style_printed) {
        echo '<style>#yourls_logo_custom img { max-height: 180px; width: auto; }</style>';
        $style_printed = true;
    }

    echo '</span>';
    echo '<h1 id="yourls_logo_custom"><a href="' . $admin_url . '" title="' . $title_attr . '">';
    echo '<img src="' . yourls_esc_url($custom_logo) . '" alt="' . $alt . '" title="' . $title_attr . '" style="border: none;" />';
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
            echo '<div class="notice notice-info" style="margin:10px 0; padding:10px; border-left: 4px solid #0073aa;">';
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

        echo '<div class="notice notice-info" style="margin:10px 0; padding:10px; border-left: 4px solid #0073aa;">';
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
        $badge = ' <span style="background:#0073aa;color:#fff;font-size:0.8em;padding:2px 6px;border-radius:3px;vertical-align:middle;">' . $badge_text . '</span>';
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

// Fetch the latest changelog from GitHub
function logo_suite_get_latest_changelog() {
    $cache_key = 'logo_suite_changelog_cache';
    $cache_time_key = 'logo_suite_changelog_cache_time';
    $cache_duration = 21600; // 6h

    $cached_changelog = yourls_get_option($cache_key);
    $cache_time = yourls_get_option($cache_time_key);

    $now = time();

    // Use cache if available and not expired
    if ($cached_changelog && $cache_time && ($now - intval($cache_time) < $cache_duration)) {
        return $cached_changelog;
    }

    // If cache is expired or not available, fetch from GitHub
    $response = logo_suite_remote_get(LOGO_SUITE_GITHUB_API);

    // Check for GitHub rate limiting
    if (isset($response['message']) && $response['message'] === 'API rate limit exceeded') {
        $error_msg = '<p>' . yourls__('GitHub API rate limit exceeded. Please try again later.', 'yourls-logo-suite') . '</p>';
        yourls_update_option($cache_key, $error_msg);
        yourls_update_option($cache_time_key, $now);
        return $error_msg;
    }    

    // Fallback if no 'body' field present
    if (!$response || !isset($response['body'])) {
        if ($cached_changelog) {
            return $cached_changelog;
        }
        return '<p>' . yourls__('No changelog available at the moment.', 'yourls-logo-suite') . '</p>';
    }    

    $markdown = $response['body'] ?? '';
    $html = logo_suite_simple_markdown_to_html($markdown);

    // Update cache
    yourls_update_option($cache_key, $html);
    yourls_update_option($cache_time_key, $now);

    return $html;
}

function logo_suite_simple_markdown_to_html($md) {
    // Simple conversion: titles and lists
    $lines = explode("\n", $md);
    $html = '';
    $in_list = false;

    foreach ($lines as $line) {
        $line = trim($line);

        if (preg_match('/^### (.+)$/', $line, $matches)) {
            if ($in_list) { $html .= "</ul>"; $in_list = false; }
            $html .= '<h3>' . htmlspecialchars($matches[1]) . '</h3>';
        } elseif (preg_match('/^## (.+)$/', $line, $matches)) {
            if ($in_list) { $html .= "</ul>"; $in_list = false; }
            $html .= '<h2>' . htmlspecialchars($matches[1]) . '</h2>';
        } elseif (preg_match('/^# (.+)$/', $line, $matches)) {
            if ($in_list) { $html .= "</ul>"; $in_list = false; }
            $html .= '<h1>' . htmlspecialchars($matches[1]) . '</h1>';
        } elseif (preg_match('/^[-*+] (.+)$/', $line, $matches)) {
            if (!$in_list) {
                $html .= '<ul>';
                $in_list = true;
            }
            $html .= '<li>' . htmlspecialchars($matches[1]) . '</li>';
        } elseif ($line === '') {
            if ($in_list) { $html .= "</ul>"; $in_list = false; }
            $html .= '<br/>';
        } else {
            $html .= '<p>' . htmlspecialchars($line) . '</p>';
        }
    }
    if ($in_list) $html .= "</ul>";

    return $html;
}