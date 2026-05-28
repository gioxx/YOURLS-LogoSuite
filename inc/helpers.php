<?php
if ( !defined( 'YOURLS_ABSPATH' ) ) die();

function logo_suite_asset_url( $relative_path ) {
    $relative_path = ltrim( (string) $relative_path, '/' );
    $plugin_dir    = LOGO_SUITE_PLUGIN_DIR;

    if ( function_exists( 'yourls_plugin_url' ) ) {
        return rtrim( (string) yourls_plugin_url( $plugin_dir ), '/' ) . '/' . $relative_path;
    }
    if ( defined( 'YOURLS_PLUGINDIRURL' ) ) {
        return rtrim( (string) YOURLS_PLUGINDIRURL, '/' ) . '/' . basename( $plugin_dir ) . '/' . $relative_path;
    }
    if ( defined( 'YOURLS_SITE' ) && defined( 'YOURLS_ABSPATH' ) ) {
        $rel = trim( str_replace( '\\', '/', str_replace( (string) YOURLS_ABSPATH, '', $plugin_dir ) ), '/' );
        return rtrim( (string) YOURLS_SITE, '/' ) . '/' . $rel . '/' . $relative_path;
    }
    return '';
}

function logo_suite_remote_get( $url ) {
    $ch = curl_init();
    curl_setopt_array( $ch, [
        CURLOPT_URL            => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_USERAGENT      => 'YOURLS-LogoSuite',
        CURLOPT_TIMEOUT        => 5,
    ] );
    $response  = curl_exec( $ch );
    $http_code = curl_getinfo( $ch, CURLINFO_HTTP_CODE );
    curl_close( $ch );
    if ( $http_code !== 200 || $response === false ) return null;
    return json_decode( $response, true );
}

function logo_suite_upload_dir_path() {
    return rtrim( (string) YOURLS_USERDIR, '/\\' ) . '/uploads/logo-suite';
}

function logo_suite_upload_base_url() {
    if ( defined( 'YOURLS_USERURL' ) ) {
        return rtrim( (string) YOURLS_USERURL, '/' ) . '/uploads/logo-suite';
    }
    return rtrim( (string) YOURLS_SITE, '/' ) . '/user/uploads/logo-suite';
}

function logo_suite_handle_logo_upload( $field_name ) {
    if ( !isset( $_FILES[$field_name] ) || !is_array( $_FILES[$field_name] ) ) return ['status' => 'none'];
    $file = $_FILES[$field_name];
    if ( !isset( $file['error'] ) || $file['error'] === UPLOAD_ERR_NO_FILE )  return ['status' => 'none'];
    if ( $file['error'] !== UPLOAD_ERR_OK )                                    return ['status' => 'error', 'message' => yourls__( 'Upload failed: no valid file received.', 'yourls-logo-suite' )];
    if ( !isset( $file['tmp_name'] ) || !is_uploaded_file( $file['tmp_name'] ) ) return ['status' => 'error', 'message' => yourls__( 'Upload failed: no valid file received.', 'yourls-logo-suite' )];
    if ( (int) $file['size'] > 5 * 1024 * 1024 )                              return ['status' => 'error', 'message' => yourls__( 'Upload failed: file is too large (max 5 MB).', 'yourls-logo-suite' )];

    $img_info = @getimagesize( $file['tmp_name'] );
    if ( !$img_info || !isset( $img_info['mime'] ) ) return ['status' => 'error', 'message' => yourls__( 'Upload failed: unsupported image format. Allowed: PNG, JPG, GIF, WEBP.', 'yourls-logo-suite' )];

    $allowed = [ 'image/png' => 'png', 'image/jpeg' => 'jpg', 'image/gif' => 'gif', 'image/webp' => 'webp' ];
    $mime    = strtolower( (string) $img_info['mime'] );
    if ( !isset( $allowed[$mime] ) ) return ['status' => 'error', 'message' => yourls__( 'Upload failed: unsupported image format. Allowed: PNG, JPG, GIF, WEBP.', 'yourls-logo-suite' )];

    $upload_dir = logo_suite_upload_dir_path();
    if ( !is_dir( $upload_dir ) && !@mkdir( $upload_dir, 0755, true ) ) return ['status' => 'error', 'message' => yourls__( 'Upload failed: could not create upload directory.', 'yourls-logo-suite' )];
    if ( !is_writable( $upload_dir ) )                                   return ['status' => 'error', 'message' => yourls__( 'Upload failed: upload directory is not writable.', 'yourls-logo-suite' )];

    $filename    = 'logo-suite-' . date( 'YmdHis' ) . '-' . substr( md5( uniqid( '', true ) ), 0, 8 ) . '.' . $allowed[$mime];
    $destination = $upload_dir . '/' . $filename;
    if ( !@move_uploaded_file( $file['tmp_name'], $destination ) ) return ['status' => 'error', 'message' => yourls__( 'Upload failed while moving the file to destination.', 'yourls-logo-suite' )];

    return ['status' => 'uploaded', 'url' => logo_suite_upload_base_url() . '/' . rawurlencode( $filename )];
}
