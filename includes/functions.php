<?php
/**
 * Helper functions
 *
 * @package     EDD\DownloadAll\Functions
 * @since       1.0.0
 */


// Exit if accessed directly
if( ! defined( 'ABSPATH' ) ) {
    exit;
}


/**
 * Get an array of files for a given purchase
 *
 * @since       1.0.0
 * @param       int $payment_id The ID of a given purchase
 * @return      array $files The array of files
 */
function edd_download_all_get_files( $payment_id = 0 ) {
    $meta   = edd_get_payment_meta( $payment_id );
    $cart   = edd_get_payment_meta_cart_details( $payment_id, true );
    $email  = edd_get_payment_user_email( $payment_id );
    $files  = array();

    if( $cart ) {
        foreach( $cart as $key => $item ) {
            if( empty( $item['in_bundle'] ) ) {
                $price_id       = edd_get_cart_item_price_id( $item );
                $download_files = edd_get_download_files( $item['id'], $price_id );

                if( ! empty( $download_files ) && is_array( $download_files ) ) {
                    foreach( $download_files as $filekey => $file ) {
                        // Skip files that are just external webpages
                        if ( strpos( edd_get_file_extension( $file['file'] ), 'com/' ) !== false ) continue;
                        $filename = basename( $file['file'] );
                        $files[$filename] = array(
                            'url'       => edd_get_download_file_url( $meta['key'], $email, $filekey, $item['id'], $price_id ),
                            'direct'    => $file['file']
                        );
                    }
                } elseif( edd_is_bundled_product( $item['id'] ) ) {
                    $bundled_products = edd_get_bundled_products( $item['id'] );

                    foreach( $bundled_products as $bundle_item ) {
                        $download_files = edd_get_download_files( $bundle_item );

                        if( $download_files && is_array( $download_files ) ) {
                            foreach( $download_files as $filekey => $file ) {
                                // Skip files that are just external webpages
                                if ( strpos( edd_get_file_extension( $file['file'] ), 'com/' ) !== false ) continue;
                                $filename = basename( $file['file'] );
                                $files[$filename] = array(
                                    'url'       => edd_get_download_file_url( $meta['key'], $email, $filekey, $bundle_item, $price_id ),
                                    'direct'    => $file['file']
                                );
                            }
                        }
                    }
                }
            }
        }
    }

    return $files;
}


/**
 * Cache files for compression
 *
 * @since       1.0.0
 * @param       array $files The files to cache
 * @return      array $files The updated files array
 */
function edd_download_all_cache_files( $files ) {
    // Setup cache, if necessary
    if( edd_get_option( 'edd_download_all_cache' ) ) {
        $wp_upload_dir  = wp_upload_dir();
        $file_path      = $wp_upload_dir['basedir'] . '/edd-download-all-cache/';

        // Ensure that the cache directory is protected
        if( false === get_transient( 'edd_download_all_check_protection_files' ) ) {
            wp_mkdir_p( $file_path );

            // Top level blank index.php
            if( ! file_exists( $file_path . 'index.php' ) ) {
                @file_put_contents( $file_path . 'index.php', '<?php' . PHP_EOL . '// Silence is golden.' );
            }

            // Top level .htaccess
            if( file_exists( $file_path . '.htaccess' ) ) {
                $rules      = 'Options -Indexes';
                $contents   = @file_get_contents( $file_path . '.htaccess' );

                if( $contents !== $rules || ! $contents ) {
                    @file_put_contents( $file_path . '.htaccess', $rules );
                }
            }

            // Check for files daily
            set_transient( 'edd_download_all_check_protection_files', true, 3600 * 24 );
        }
    } else {
        // Set the output path to the system temp directory if caching is disabled
        $file_path = sys_get_temp_dir() . '/';
    }

    foreach( $files as $file_title => $file_data ) {
        $file_url   = $file_data['direct'];
        $hosted     = null;

        if( strpos( $file_url, site_url() ) !== false ) {
            $hosted = 'local';
        } elseif( strpos( $file_url, ABSPATH ) !== false ) {
            $hosted = 'local';
        } elseif( filter_var( $file_url, FILTER_VALIDATE_URL ) === FALSE && strpos( $file_url, 'edd-dbfs' ) !== false ) {
            $hosted = 'dropbox';
        } elseif( filter_var( $file_url, FILTER_VALIDATE_URL ) === FALSE && $file_url[0] !== '/' ) {
            $hosted = 'amazon';
        } elseif( strpos( $file_url, 'AWSAccessKeyID' ) !== false ) {
            $hosted = 'amazon';
        }

        if( $hosted != 'local' ) {
            if( $hosted == 'amazon' ) {
                // Handle S3
                $file_url = $file_data['url'];
                $file_name = $file_data['direct'];
                $file_name = basename( $file_name );
            } elseif( $hosted == 'dropbox' ) {
                // We can't work with Dropbox!
                edd_die( __( 'We can\'t currently bundle these files. Please download them individually.', 'edd-download-all' ), __( 'Oops!', 'edd-download-all' ) );
                exit;
            } else {
                $file_name = basename( $file_url );
            }

            $files[$file_title]['path'] = $file_path . $file_name;

            if( ! file_exists( $file_path . $file_name ) ) {
                // Remote files must be downloaded to the server!
                $response   = wp_remote_get( $file_url );
                $file       = wp_remote_retrieve_body( $response );

                file_put_contents( $file_path . $file_name, $file );
            }
        } else {
            $files[$file_title]['path'] = str_replace( WP_CONTENT_URL, WP_CONTENT_DIR, $file_url );
        }
    }

    return $files;
}


/**
 * Process the download action
 *
 * @since       1.0.0
 * @return      void
 */
function edd_download_all_files() {

    // Make sure a payment ID is provided
    if ( !isset( $_GET['payment_id'] ) ) return;

    // Variables
    $payment    = edd_get_payment( $_GET['payment_id'] );
    $files      = edd_download_all_get_files( $payment->ID );
    $files      = edd_download_all_cache_files( $files );

    // Setup file path
    if ( edd_get_option( 'edd_download_all_cache' ) ) {
        $wp_upload_dir  = wp_upload_dir();
        $file_path      = $wp_upload_dir['basedir'] . '/edd-download-all-cache/';
    } else {
        // Set the output path to the system temp directory if caching is disabled
        $file_path = sys_get_temp_dir() . '/';
    }

    $zip_name = apply_filters( 'edd_download_files_zip_name', strtolower( str_replace( ' ', '-', get_bloginfo( 'name' ) ) ) . '-bundle-' . $payment->ID . '.zip' );
    $zip_file = $file_path . $zip_name;

    if( class_exists( 'ZipArchive' ) ) {
        if( ! file_exists( $zip_file ) ) {
            $zip = new ZipArchive();

            if( $zip->open( $zip_file, ZIPARCHIVE::CREATE ) !== TRUE ) {
                edd_die( __( 'An unknown error occurred, please try again!', 'edd-free-downloads' ), __( 'Oops!', 'edd-free-downloads' ) );
                exit;
            }

            foreach( $files as $file_name => $file_data ) {
                $zip->addFile( $file_data['path'], $file_name );
            }

            $zip->close();
        }
    }

    // Deliver the file to the user
    header( 'Content-type: application/octet-stream' );
    header( 'Content-Disposition: attachment; filename="' . $zip_name . '"' );

    edd_deliver_download( $zip_file );
    exit;
}
add_action( 'edd_download_all_files', 'edd_download_all_files' );
