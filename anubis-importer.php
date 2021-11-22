<?php 
/**
 * Plugin Name: Anubis Software Importer
 * Plugin URI: http://www.anubis-software.com.ar/wp/
 * Description: Importacion de produtos de Anubis Software a WooCommerce
 * Version: 1.0
 */

if( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

define('ANUBIS_GF_FIELD_VERSION', '1.0');
define('ANUBIS_PLUGIN_DIR', dirname( __FILE__ ) );
define('ANUBIS_IMPORTER_LOG_FILE', ANUBIS_PLUGIN_DIR .'/logs/log_'.date("j.n.Y", current_time( 'timestamp', 0 )).'.log' );
define('ANUBIS_IMPORTER_LOG_ERROR_FILE', ANUBIS_PLUGIN_DIR .'/logs/log_error_'.date("j.n.Y", current_time( 'timestamp', 0 )).'.log' );

if ( !class_exists( 'Anubis_Importer_Class' ) ) {
    require_once( ANUBIS_PLUGIN_DIR . '/includes/anubis-importer-class.php' );
}

/**
 * Load plugin
 */
$anubis = new Anubis_Importer();
$anubis->init();

/**
 * Activation
 */
register_activation_hook( __FILE__, array( $anubis, 'anubis_importer_cron_activation' ) );

/**
 * Deactivation
 */
register_deactivation_hook( __FILE__, array( $anubis, 'anubis_importer_cron_deactivation' ) );