<?php

class Anubis_Importer {

    public function __construct() {
        require_once( ANUBIS_PLUGIN_DIR . '/includes/anubis-importer-errors-trait.php' );
        require_once( ANUBIS_PLUGIN_DIR . '/includes/anubis-importer-settings-class.php' );
        require_once( ANUBIS_PLUGIN_DIR . '/includes/anubis-importer-process-class.php' );
        require_once( ANUBIS_PLUGIN_DIR . '/includes/anubis-importer-images-class.php' );
        add_action( 'anubis_importer_cron_hook', array( $this, 'anubis_importer_cron_hook' ) );
        add_action('wp_ajax_nopriv_run_importer', array( $this, 'anubis_importer_cron_manually' ) );
        add_action('wp_ajax_run_importer', array( $this, 'anubis_importer_cron_manually' ) );
    }
    
    public function init() {    
        new Anubis_Importer_Settings;
    }

    public function anubis_importer_cron_activation() {
        $this->anubis_schedule_init();
    }

    /**
     * Actuivation Manual
     * @param $action
     */
    public static function anubis_importer_cron_manually() {
        do_action( 'anubis_importer_cron_hook' );
    }

    /**
     * Schedule init.
     */
    public function anubis_schedule_init() {
        if ( ! wp_next_scheduled( 'anubis_importer_cron_hook' ) ) {
            wp_schedule_event( time(), 'hourly', 'anubis_importer_cron_hook' );
        }
    }

    /**
     * Init Process of importing.
     */
    public function anubis_importer_cron_hook() {
        $anubis_importer_process = new Anubis_Importer_Process;
        $anubis_importer_process->anubis_importer_process_init();
    }

    /**
     * Desactive Schedule
     */
    public function anubis_importer_cron_deactivation() {
        wp_clear_scheduled_hook( 'anubis_importer_cron_hook' );
    }
}