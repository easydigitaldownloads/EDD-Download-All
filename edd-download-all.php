<?php
/**
 * Plugin Name:     Easy Digital Downloads - Download All
 * Plugin URI:      https://easydigitaldownloads.com/extensions/download-all/
 * Description:     Adds a download all option to multi-file downloads in EDD
 * Version:         1.0.0
 * Author:          Daniel J Griffiths
 * Author URI:      http://section214.com
 * Text Domain:     edd-download-all
 *
 * @package         EDD\DownloadAll
 * @author          Daniel J Griffiths <dgriffiths@section214.com>
 */


// Exit if accessed directly
if( ! defined( 'ABSPATH' ) ) exit;


if( ! class_exists( 'EDD_Download_All' ) ) {


    /**
     * Main EDD_Download_All class
     *
     * @since       1.0.0
     */
    class EDD_Download_All {


        /**
         * @var         EDD_Download_All $instance The one true EDD_Download_All
         * @since       1.0.0
         */
        private static $instance;


        /**
         * Get active instance
         *
         * @access      public
         * @since       1.0.0
         * @return      self::$instance The one true EDD_Download_All
         */
        public static function instance() {
            if( ! self::$instance ) {
                self::$instance = new EDD_Download_All();
                self::$instance->setup_constants();
                self::$instance->includes();
                self::$instance->load_textdomain();
                self::$instance->hooks();
            }

            return self::$instance;
        }


        /**
         * Setup plugin constants
         *
         * @access      private
         * @since       1.0.0
         * @return      void
         */
        private function setup_constants() {
            // Plugin version
            define( 'EDD_DOWNLOAD_ALL_VER', '1.0.0' );

            // Plugin path
            define( 'EDD_DOWNLOAD_ALL_DIR', plugin_dir_path( __FILE__ ) );

            // Plugin URL
            define( 'EDD_DOWNLOAD_ALL_URL', plugin_dir_url( __FILE__ ) );
        }


        /**
         * Include necessary files
         *
         * @access      private
         * @since       1.0.0
         * @return      void
         */
        private function includes() {
            require_once EDD_DOWNLOAD_ALL_DIR . 'includes/functions.php';
            require_once EDD_DOWNLOAD_ALL_DIR . 'includes/templates.php';
        }


        /**
         * Run action and filter hooks
         *
         * @access      private
         * @since       1.0.0
         * @return      void
         */
        private function hooks() {
            // Add our extension settings
            add_filter( 'edd_settings_extensions', array( $this, 'add_settings' ) );

            // Handle licensing
            if( class_exists( 'EDD_License' ) ) {
                $license = new EDD_License( __FILE__, 'Download All', EDD_DOWNLOAD_ALL_VER, 'Daniel J Griffiths' );
            }
        }


        /**
         * Add settings
         *
         * @access      public
         * @since       1.0.0
         * @param       array $settings The existing plugin settings
         * @return      array The modified plugin settings
         */
        public function add_settings( $settings ) {
            $new_settings = array(
                array(
                    'id'    => 'edd_download_all_settings',
                    'name'  => '<span class="field-section-title">' . __( 'Download All', 'edd-download-all' ) . '</span>',
                    'desc'  => '',
                    'type'  => 'header'
                ),
                array(
                    'id'    => 'edd_download_all_table_label',
                    'name'  => __( 'Receipt Row Label', 'edd-download-all' ),
                    'desc'  => __( 'Enter the title for the receipt table row', 'edd-download-all' ),
                    'type'  => 'text',
                    'std'   => __( 'Download All Files:', 'edd-download-all' )
                ),
                array(
                    'id'    => 'edd_download_all_link_text',
                    'name'  => __( 'Download Link Text', 'edd-download-all' ),
                    'desc'  => __( 'Enter the text to be used for download links', 'edd-download-all' ),
                    'type'  => 'text',
                    'std'   => __( 'Download', 'edd-download-all' )
                ),
                array(
                    'id'    => 'edd_download_all_cache',
                    'name'  => __( 'Cache Remote Files', 'edd-download-all' ),
                    'desc'  => __( 'This can help if your host disallows access to the temp directory!', 'edd-download-all' ),
                    'type'  => 'checkbox'
                )
            );

            return array_merge( $settings, $new_settings );
        }


        /**
         * Internationalization
         *
         * @access      public
         * @since       1.0.0
         * @return      void
         */
        public function load_textdomain() {
            // Set filter for language directory
            $lang_dir = dirname( plugin_basename( __FILE__ ) ) . '/languages/';
            $lang_dir = apply_filters( 'edd_download_all_language_directory', $lang_dir );

            // Traditional WordPress plugin locale filter
            $locale = apply_filters( 'plugin_locale', get_locale(), '' );
            $mofile = sprintf( '%1$s-%2$s.mo', 'edd-download-all', $locale );

            // Setup paths to current locale file
            $mofile_local   = $lang_dir . $mofile;
            $mofile_global  = WP_LANG_DIR . '/edd-download-all/' . $mofile;

            if( file_exists( $mofile_global ) ) {
                // Look in global /wp-content/languages/edd-download-all/ folder
                load_textdomain( 'edd-download-all', $mofile_global );
            } elseif( file_exists( $mofile_local ) ) {
                // Look in local /wp-content/plugins/edd-download-all/ folder
                load_textdomain( 'edd-download-all', $mofile_local );
            } else {
                // Load the default language files
                load_plugin_textdomain( 'edd-download-all', false, $lang_dir );
            }
        }
    }
}


/**
 * The main function responsible for returning the one true EDD_Download_All
 * instance to functions everywhere
 *
 * @since       1.0.0
 * @return      EDD_Download_All The one true EDD_Download_All
 */
function edd_download_all() {
    if( ! class_exists( 'Easy_Digital_Downloads' ) ) {
        if( ! class_exists( 'S214_EDD_Activation' ) ) {
            require_once 'includes/class.s214-edd-activation.php';
        }

        $activation = new S214_EDD_Activation( plugin_dir_path( __FILE__ ), basename( __FILE__ ) );
        $activation = $activation->run();

        return EDD_Download_All::instance();
    } else {
        return EDD_Download_All::instance();
    }
}
add_action( 'plugins_loaded', 'edd_download_all' );
