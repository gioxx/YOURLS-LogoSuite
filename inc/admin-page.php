<?php
if ( !defined( 'YOURLS_ABSPATH' ) ) die();

yourls_add_action( 'plugins_loaded', 'logo_suite_load_textdomain' );
function logo_suite_load_textdomain() {
    $locale = yourls_get_locale();
    $domain = 'yourls-logo-suite';
    $path   = LOGO_SUITE_PLUGIN_DIR . '/languages/';
    if ( file_exists( $path . "{$domain}-{$locale}.mo" ) ) {
        yourls_load_textdomain( $domain, $path . "{$domain}-{$locale}.mo" );
    } elseif ( file_exists( $path . "{$domain}-{$locale}.po" ) ) {
        yourls_load_textdomain( $domain, $path . "{$domain}-{$locale}.po" );
    }
}

yourls_add_action( 'plugins_loaded', 'logo_suite_add_page' );
function logo_suite_add_page() {
    yourls_register_plugin_page( 'logo_suite', yourls__( 'Branding Settings', 'yourls-logo-suite' ), 'logo_suite_config_page' );
}

function logo_suite_print_admin_assets() {
    $admin_css = logo_suite_asset_url( 'assets/admin.css' );
    if ( $admin_css !== '' ) {
        $css_file    = LOGO_SUITE_PLUGIN_DIR . '/assets/admin.css';
        $css_version = file_exists( $css_file ) ? (string) filemtime( $css_file ) : LOGO_SUITE_VERSION;
        echo '<link rel="stylesheet" href="' . yourls_esc_attr( $admin_css ) . '?v=' . rawurlencode( $css_version ) . '">';
    }
    $admin_js = logo_suite_asset_url( 'assets/admin.js' );
    if ( $admin_js !== '' ) {
        $js_file    = LOGO_SUITE_PLUGIN_DIR . '/assets/admin.js';
        $js_version = file_exists( $js_file ) ? (string) filemtime( $js_file ) : LOGO_SUITE_VERSION;
        echo '<script src="' . yourls_esc_attr( $admin_js ) . '?v=' . rawurlencode( $js_version ) . '"></script>';
    }
}

function logo_suite_config_page() {
    $messages = [];

    if ( isset( $_POST['logo_suite_save_settings'] ) ) {
        yourls_verify_nonce( 'logo_suite_config' );
        $save_result = logo_suite_save_settings();
        $messages[]  = [
            'type' => $save_result['success'] ? 'success' : 'warning',
            'text' => $save_result['text'],
        ];
    }

    if ( isset( $_POST['logo_suite_reset_settings'] ) ) {
        yourls_verify_nonce( 'logo_suite_reset', $_POST['nonce_reset'] );
        logo_suite_reset_settings();
        $messages[] = ['type' => 'warning', 'text' => yourls__( 'Settings reset to default.', 'yourls-logo-suite' )];
    }

    $logo_url       = yourls_get_option( 'logo_suite_image_url' );
    $logo_alt       = yourls_get_option( 'logo_suite_image_alt' );
    $logo_title     = yourls_get_option( 'logo_suite_image_title' );
    $logo_width     = (int) yourls_get_option( 'logo_suite_display_width' );
    $logo_height    = (int) yourls_get_option( 'logo_suite_display_height' );
    $custom_title   = yourls_get_option( 'logo_suite_custom_title' );
    $keep_suffix    = yourls_get_option( 'logo_suite_keep_suffix' ) == 1 ? 'checked' : '';
    $keep_ratio_opt = yourls_get_option( 'logo_suite_keep_ratio' );
    $keep_ratio     = ( (string) $keep_ratio_opt === '' || (int) $keep_ratio_opt === 1 ) ? 'checked' : '';
    $nonce_config   = yourls_create_nonce( 'logo_suite_config' );
    $nonce_reset    = yourls_create_nonce( 'logo_suite_reset' );

    logo_suite_print_admin_assets();
    logo_suite_show_update_notice();

    echo '<div class="logo-suite-header">';
    echo '<h2 class="logo-suite-title">&#127912; <span class="logo-suite-title-text">' . yourls_apply_filters( 'plugin_page_title_logo_suite', yourls__( 'YOURLS Logo Suite', 'yourls-logo-suite' ) ) . '</span></h2>';
    echo '<p class="logo-suite-version">' . yourls__( 'Version:', 'yourls-logo-suite' ) . ' ' . LOGO_SUITE_VERSION . '</p>';
    echo '</div>';

    foreach ( $messages as $msg ) {
        echo '<div class="notice notice-' . $msg['type'] . '"><p>' . $msg['text'] . '</p></div>';
    }

    echo '<form method="post" class="logo-suite-form" enctype="multipart/form-data">';
    echo '<input type="hidden" name="nonce" value="' . $nonce_config . '">';

    // ── Page Title ──
    echo '<div class="logo-suite-panel">';
    echo '<h3 class="logo-suite-heading"><svg xmlns="http://www.w3.org/2000/svg" class="logo-icon" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" stroke="currentColor" stroke-width="2" fill="none"/><polyline points="14 2 14 8 20 8" stroke="currentColor" stroke-width="2" fill="none"/></svg> ' . yourls__( 'Page Title Settings', 'yourls-logo-suite' ) . '</h3>';
    echo '<div class="logo-suite-panel-body">';
    echo '<div class="form-row">';
    echo '<label for="logo_suite_custom_title">' . yourls__( 'Custom Title', 'yourls-logo-suite' ) . '</label>';
    echo '<small>' . yourls__( 'Example: your custom page title.', 'yourls-logo-suite' ) . '</small>';
    echo '<input type="text" name="logo_suite_custom_title" id="logo_suite_custom_title" value="' . yourls_esc_attr( $custom_title ) . '" placeholder="' . yourls__( 'My YOURLS Panel', 'yourls-logo-suite' ) . '">';
    echo '</div>';
    echo '<div class="form-row">';
    echo '<label for="logo_suite_keep_suffix"><input type="checkbox" name="logo_suite_keep_suffix" id="logo_suite_keep_suffix" value="1" ' . $keep_suffix . '> ';
    echo yourls__( 'Keep "(YOURLS)" after the custom title', 'yourls-logo-suite' ) . '</label>';
    echo '</div>';
    echo '</div></div>';

    // ── Logo Settings ──
    echo '<div class="logo-suite-panel">';
    echo '<h3 class="logo-suite-heading"><svg xmlns="http://www.w3.org/2000/svg" class="logo-icon" viewBox="0 0 24 24"><rect x="3" y="3" width="18" height="18" rx="2" ry="2" stroke="currentColor" stroke-width="2" fill="none"/><path d="M3 9h18" stroke="currentColor" stroke-width="2"/></svg> ' . yourls__( 'Logo Settings', 'yourls-logo-suite' ) . '</h3>';
    echo '<div class="logo-settings-layout">';
    echo '<div class="logo-settings-fields">';

    echo '<div class="form-row">';
    echo '<label for="logo_suite_image_url">' . yourls__( 'Image URL', 'yourls-logo-suite' ) . '</label>';
    echo '<small>' . yourls__( 'Example: a direct link to your logo image (PNG, JPG, SVG).', 'yourls-logo-suite' ) . '</small>';
    echo '<input type="text" name="logo_suite_image_url" id="logo_suite_image_url" value="' . yourls_esc_attr( $logo_url ) . '" placeholder="https://example.com/logo.png">';
    echo '</div>';

    echo '<div class="form-row">';
    echo '<label for="logo_suite_image_file">' . yourls__( 'Upload image', 'yourls-logo-suite' ) . '</label>';
    echo '<small>' . yourls__( 'Optional: upload PNG, JPG, GIF or WEBP from your computer (max 5 MB).', 'yourls-logo-suite' ) . '</small>';
    echo '<input type="file" name="logo_suite_image_file" id="logo_suite_image_file" accept=".png,.jpg,.jpeg,.gif,.webp,image/png,image/jpeg,image/gif,image/webp">';
    echo '<div id="logo-upload-alert" class="logo-upload-alert logo-upload-alert-hidden">&#128204; ' . yourls__( 'After selecting a file, click "Save Settings" to complete logo upload.', 'yourls-logo-suite' ) . '</div>';
    echo '</div>';

    echo '<div class="form-row">';
    echo '<label for="logo_suite_image_alt">' . yourls__( 'ALT Tag', 'yourls-logo-suite' ) . '</label>';
    echo '<small>' . yourls__( 'Example: descriptive text for accessibility.', 'yourls-logo-suite' ) . '</small>';
    echo '<input type="text" name="logo_suite_image_alt" id="logo_suite_image_alt" value="' . yourls_esc_attr( $logo_alt ) . '" placeholder="' . yourls__( 'My Custom Logo', 'yourls-logo-suite' ) . '">';
    echo '</div>';

    echo '<div class="form-row">';
    echo '<label for="logo_suite_image_title">' . yourls__( 'Title Attribute', 'yourls-logo-suite' ) . '</label>';
    echo '<small>' . yourls__( 'Example: tooltip text shown on hover.', 'yourls-logo-suite' ) . '</small>';
    echo '<input type="text" name="logo_suite_image_title" id="logo_suite_image_title" value="' . yourls_esc_attr( $logo_title ) . '" placeholder="' . yourls__( 'Back to Dashboard', 'yourls-logo-suite' ) . '">';
    echo '</div>';

    echo '</div>';

    echo '<div class="logo-settings-preview">';
    echo '<label class="logo-preview-title">' . yourls__( 'Logo Preview', 'yourls-logo-suite' ) . '</label>';
    echo '<div id="logo-preview-wrapper">';
    if ( $logo_url ) {
        echo '<img id="logo-preview" class="logo-preview-image" src="' . yourls_esc_url( $logo_url ) . '" alt="" onerror="logoPreviewError()" onload="logoPreviewSuccess()">';
        echo '<div id="logo-preview-error" class="logo-preview-error logo-preview-hidden">' . yourls__( 'Unable to load the image. Please check the URL.', 'yourls-logo-suite' ) . '</div>';
    } else {
        echo '<img id="logo-preview" class="logo-preview-image logo-preview-hidden" src="" alt="">';
        echo '<div id="logo-preview-error" class="logo-preview-error logo-preview-hidden">' . yourls__( 'Unable to load the image. Please check the URL.', 'yourls-logo-suite' ) . '</div>';
    }
    echo '</div>';
    echo '<div id="logo-size-warning" class="logo-size-warning logo-size-warning-hidden"></div>';
    echo '<div class="logo-preview-controls">';
    echo '<div class="logo-preview-size-row">';
    echo '<div class="logo-preview-control"><label for="logo_suite_display_width">' . yourls__( 'Width (px)', 'yourls-logo-suite' ) . '</label>';
    echo '<input type="number" name="logo_suite_display_width" id="logo_suite_display_width" min="1" step="1" value="' . ( $logo_width > 0 ? $logo_width : '' ) . '"></div>';
    echo '<div class="logo-preview-control"><label for="logo_suite_display_height">' . yourls__( 'Height (px)', 'yourls-logo-suite' ) . '</label>';
    echo '<input type="number" name="logo_suite_display_height" id="logo_suite_display_height" min="1" step="1" value="' . ( $logo_height > 0 ? $logo_height : '' ) . '"></div>';
    echo '</div>';
    echo '<div class="logo-preview-control-check">';
    echo '<label for="logo_suite_keep_ratio"><input type="checkbox" name="logo_suite_keep_ratio" id="logo_suite_keep_ratio" value="1" ' . $keep_ratio . '> ' . yourls__( 'Keep aspect ratio', 'yourls-logo-suite' ) . '</label>';
    echo '</div>';
    echo '<small class="logo-preview-control-help">' . yourls__( 'Tip: set one side only and keep aspect ratio enabled for proportional resize.', 'yourls-logo-suite' ) . '</small>';
    echo '</div>';
    echo '</div>';

    echo '</div></div>';

    // ── Info box ──
    echo '<div class="logo-suite-info-box">';
    echo '<h4 class="logo-suite-info-title"><span class="logo-suite-info-icon">i</span>' . yourls__( 'Notes', 'yourls-logo-suite' ) . '</h4>';
    echo '<ul class="logo-suite-info-list">';
    echo '<li><strong>' . yourls__( 'Save Settings', 'yourls-logo-suite' ) . '</strong>: ' . yourls__( 'Saves the changes you made to logo and title.', 'yourls-logo-suite' ) . '</li>';
    echo '<li><strong>' . yourls__( 'Reset to Default', 'yourls-logo-suite' ) . '</strong>: ' . yourls__( 'Restores original YOURLS logo and title, removing all customizations.', 'yourls-logo-suite' ) . '</li>';
    echo '</ul>';
    echo '</div>';

    // ── Actions ──
    echo '<div class="logo-suite-actions">';
    echo '<button type="submit" name="logo_suite_save_settings" class="button">&#128190; ' . yourls__( 'Save Settings', 'yourls-logo-suite' ) . '</button>';
    echo '<button type="submit" name="logo_suite_reset_settings" class="button" onclick="return confirm(\'' . yourls__( 'Are you sure you want to reset all settings?', 'yourls-logo-suite' ) . '\');" formnovalidate>&#128260; ' . yourls__( 'Reset to Default', 'yourls-logo-suite' ) . '</button>';
    echo '<input type="hidden" name="nonce_reset" value="' . $nonce_reset . '">';
    echo '</div>';

    echo '<div class="logo-suite-footer">';
    echo '<div class="plugin-footer-top">';
    echo '<div>';
    echo '<a href="https://yourls.gioxx.org/plugins/logo-suite" target="_blank" rel="noopener noreferrer">&#127912; YOURLS Logo Suite</a>';
    echo ' &nbsp;&middot;&nbsp; ';
    echo '<img src="https://github.githubassets.com/favicons/favicon.png" class="github-icon" alt="">';
    echo '<a href="https://github.com/gioxx/YOURLS-LogoSuite" target="_blank" rel="noopener noreferrer">GitHub</a>';
    echo '</div>';
    echo '<div><a href="#" onclick="window.scrollTo({top:0,behavior:\'smooth\'});return false;">&#8593; Back to top</a></div>';
    echo '</div>';
    echo '&#10084;&#65039; Lovingly developed by the usually-on-vacation brain cell of ';
    echo '<a href="https://github.com/gioxx" target="_blank" rel="noopener noreferrer">Gioxx</a> &ndash; ';
    echo '<a href="https://gioxx.org" target="_blank" rel="noopener noreferrer">Gioxx\'s Wall</a>';
    echo '</div>';

    echo '</form>';
}

function logo_suite_save_settings() {
    $logo_url     = trim( $_POST['logo_suite_image_url'] ?? '' );
    $upload_result = logo_suite_handle_logo_upload( 'logo_suite_image_file' );

    if ( $upload_result['status'] === 'error' )    return ['success' => false, 'text' => $upload_result['message']];
    if ( $upload_result['status'] === 'uploaded' ) $logo_url = $upload_result['url'];

    yourls_update_option( 'logo_suite_image_url',   $logo_url );
    yourls_update_option( 'logo_suite_image_alt',   trim( $_POST['logo_suite_image_alt']   ?? '' ) );
    yourls_update_option( 'logo_suite_image_title', trim( $_POST['logo_suite_image_title'] ?? '' ) );

    $display_width  = isset( $_POST['logo_suite_display_width'] )  ? (int) $_POST['logo_suite_display_width']  : 0;
    $display_height = isset( $_POST['logo_suite_display_height'] ) ? (int) $_POST['logo_suite_display_height'] : 0;
    yourls_update_option( 'logo_suite_display_width',  $display_width  > 0 ? $display_width  : '' );
    yourls_update_option( 'logo_suite_display_height', $display_height > 0 ? $display_height : '' );
    yourls_update_option( 'logo_suite_keep_ratio',     isset( $_POST['logo_suite_keep_ratio'] )   ? 1 : 0 );
    yourls_update_option( 'logo_suite_custom_title',   trim( $_POST['logo_suite_custom_title']    ?? '' ) );
    yourls_update_option( 'logo_suite_keep_suffix',    isset( $_POST['logo_suite_keep_suffix'] )  ? 1 : 0 );

    if ( $upload_result['status'] === 'uploaded' ) {
        return ['success' => true, 'text' => yourls__( 'Logo image uploaded successfully. URL updated automatically.', 'yourls-logo-suite' )];
    }
    return ['success' => true, 'text' => yourls__( 'Settings updated successfully!', 'yourls-logo-suite' )];
}

function logo_suite_reset_settings() {
    yourls_delete_option( 'logo_suite_image_url' );
    yourls_delete_option( 'logo_suite_image_alt' );
    yourls_delete_option( 'logo_suite_image_title' );
    yourls_delete_option( 'logo_suite_display_width' );
    yourls_delete_option( 'logo_suite_display_height' );
    yourls_delete_option( 'logo_suite_keep_ratio' );
    yourls_delete_option( 'logo_suite_custom_title' );
    yourls_delete_option( 'logo_suite_keep_suffix' );
}
