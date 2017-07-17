<?php
/**
 * Template overrides
 *
 * @package     EDD\DownloadAll\Templates
 * @since       1.0.0
 */


// Exit if accessed directly
if( ! defined( 'ABSPATH' ) ) {
    exit;
}


/**
 * Add download all rows to receipt
 *
 * @since       1.0.0
 * @param       object $payment The purchase object
 * @param       array $receipt_args The receipt arguments
 * @return      void
 */
function edd_download_all_after_receipt( $payment, $receipt_args ) {

    // Variables
    $files = edd_download_all_get_files( $payment->ID );
    $success_url = edd_get_success_page_uri();
    $download_url = esc_url( add_query_arg( array( 'payment_id' => $payment->ID, 'edd_action' => 'download_all_files' ), $success_url ) );

    // Make sure there's at least 2 downloads
    if ( count( $files ) < 2 ) return;

    // Generate downloads row
    $downloads =
        '<tr>' .
            '<td><strong>' . edd_get_option( 'edd_download_all_table_label', __( 'Download All Files:', 'edd-download-all' ) ) . '</strong></td>' .
            '<td><a href="' . $download_url . '">' . edd_get_option( 'edd_download_all_link_text', __( 'Download', 'edd-download-all' ) ) . '</a></td>' .
        '</tr>';

    echo $downloads;

}
add_action( 'edd_payment_receipt_after', 'edd_download_all_after_receipt', 10, 2 );
