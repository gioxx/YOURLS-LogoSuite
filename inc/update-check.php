<?php
if ( !defined( 'YOURLS_ABSPATH' ) ) die();

function logo_suite_fetch_latest_release() {
    static $release = null;
    if ( $release === null ) {
        $response = logo_suite_remote_get( LOGO_SUITE_GITHUB_API );
        $release  = ( $response && isset( $response['tag_name'] ) ) ? $response : false;
    }
    return $release ?: null;
}

function logo_suite_show_update_notice() {
    $release = logo_suite_fetch_latest_release();
    if ( !$release ) return;
    $latest = ltrim( $release['tag_name'], 'v' );
    if ( version_compare( $latest, LOGO_SUITE_VERSION, '>' ) ) {
        echo '<div class="notice notice-info logo-suite-update-notice">&#x1F195; <strong>YOURLS Logo Suite</strong>: New version available: <strong>' . $latest . '</strong>! <a href="' . $release['html_url'] . '" target="_blank">View details on GitHub</a></div>';
    }
}

function logo_suite_page_title_with_badge( $title ) {
    $release = logo_suite_fetch_latest_release();
    if ( !$release ) return $title;
    $latest = ltrim( $release['tag_name'], 'v' );
    return version_compare( $latest, LOGO_SUITE_VERSION, '>' )
        ? $title . ' <span class="logo-suite-update-badge">' . yourls__( 'Update Available', 'yourls-logo-suite' ) . '</span>'
        : $title;
}
