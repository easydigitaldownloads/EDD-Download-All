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
 * Add download all row to receipt
 *
 * @since       1.0.0
 * @param       object $payment The purchase object
 * @param       array $receipt_args The receipt arguments
 * @return      void
 */
function edd_download_all_after_receipt( $payment, $receipt_args ) {
    $files = edd_download_all_get_files( $payment->ID );

    if( count( $files ) > 1 ) {
        echo '<tr>';
        echo '<td><strong>' . edd_get_option( 'edd_download_all_table_label', __( 'Download All Files:', 'edd-download-all' ) ) . '</strong></td>';
        echo '<td><a href="' . esc_url( add_query_arg( 'edd_action', 'download_all_files' ) ) . '">' . edd_get_option( 'edd_download_all_link_text', __( 'Download', 'edd-download-all' ) ) . '</a></td>';
        echo '</tr>';
    }
}
add_action( 'edd_payment_receipt_after', 'edd_download_all_after_receipt', 10, 2 );
