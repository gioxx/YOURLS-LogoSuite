<?php
if ( !defined( 'YOURLS_ABSPATH' ) ) die();

yourls_add_action( 'plugins_loaded', 'logo_suite_register_global_assets' );
function logo_suite_register_global_assets() {
    yourls_add_action( 'admin_head', 'logo_suite_print_global_assets' );
}

function logo_suite_print_global_assets() {
    $logo_css = logo_suite_asset_url( 'assets/logo.css' );
    if ( $logo_css === '' ) return;
    $file    = LOGO_SUITE_PLUGIN_DIR . '/assets/logo.css';
    $version = file_exists( $file ) ? (string) filemtime( $file ) : LOGO_SUITE_VERSION;
    echo '<link rel="stylesheet" href="' . yourls_esc_attr( $logo_css ) . '?v=' . rawurlencode( $version ) . '">';
}

function logo_suite_get_rendered_logo_style() {
    $width          = (int) yourls_get_option( 'logo_suite_display_width' );
    $height         = (int) yourls_get_option( 'logo_suite_display_height' );
    $keep_ratio_opt = yourls_get_option( 'logo_suite_keep_ratio' );
    $keep_ratio     = ( (string) $keep_ratio_opt === '' || (int) $keep_ratio_opt === 1 );
    $has_custom     = ( $width > 0 || $height > 0 );

    $style = 'border:none;';
    if ( $keep_ratio ) {
        if ( $width > 0 && $height > 0 )  $style .= "width:{$width}px;height:{$height}px;object-fit:contain;";
        elseif ( $width > 0 )             $style .= "width:{$width}px;height:auto;";
        elseif ( $height > 0 )            $style .= "height:{$height}px;width:auto;";
        else                              $style .= 'width:auto;height:auto;';
    } else {
        if ( $width > 0 )  $style .= "width:{$width}px;";
        if ( $height > 0 ) $style .= "height:{$height}px;";
        if ( $width <= 0 && $height <= 0 ) $style .= 'width:auto;height:auto;';
    }
    $style .= $has_custom ? 'max-height:none;' : 'max-height:180px;';
    return $style;
}

yourls_add_filter( 'pre_html_logo', 'logo_suite_hide_original_logo' );
function logo_suite_hide_original_logo() {
    if ( yourls_get_option( 'logo_suite_image_url' ) ) {
        // Inline fallback guarantees hiding even before admin.css loads
        echo '<span class="logo-suite-hidden" style="display:none;">';
    }
}

yourls_add_filter( 'html_logo', 'logo_suite_custom_logo' );
function logo_suite_custom_logo() {
    $custom_logo = yourls_get_option( 'logo_suite_image_url' );
    if ( !$custom_logo ) return;
    $alt        = yourls_esc_attr( yourls_get_option( 'logo_suite_image_alt' ) );
    $title_attr = yourls_esc_attr( yourls_get_option( 'logo_suite_image_title' ) );
    $admin_url  = yourls_admin_url( 'index.php' );
    $logo_style = logo_suite_get_rendered_logo_style();
    echo '</span>';
    echo '<h1 id="yourls_logo_custom"><a href="' . $admin_url . '" class="logo-suite-custom-link" title="' . $title_attr . '">';
    echo '<img src="' . yourls_esc_url( $custom_logo ) . '" alt="' . $alt . '" title="' . $title_attr . '" class="logo-suite-custom-image" style="' . yourls_esc_attr( $logo_style ) . '" />';
    echo '</a></h1>';
}

yourls_add_filter( 'html_title', 'logo_suite_custom_title' );
function logo_suite_custom_title( $title ) {
    $custom_title = yourls_get_option( 'logo_suite_custom_title' );
    if ( !$custom_title ) return $title;
    return yourls_get_option( 'logo_suite_keep_suffix' ) == 1
        ? $custom_title . ' (YOURLS)'
        : $custom_title;
}
