<?php 

class Anubis_Importer_Settings {

    public $settings = array();

    public function __construct() {
        add_action('admin_menu', array( $this, 'anubis_importer_admin_menu' ) );
        add_action('admin_init', array($this, 'settings_init'));
        add_action('admin_notices', array($this, 'plugin_notice'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_scripts'));
        $this->settings = get_option('anubis_importer_settings');
    }

    /*--------------------------------*/
    /* Add Admin Menu
    /*--------------------------------*/

    public function anubis_importer_admin_menu() {
        add_options_page( 'Anubis Software Importer®', 'Anubis Importer®', 'manage_options', 'anubis-importer', array( $this, 'options_page' ) );
    }
    
    public function settings_init() {
        register_setting( 'anubis_importer_settings', 'anubis_importer_settings' );

        add_settings_section(
            'anubis_importer_section',
            '',
            array( $this, 'settings_section_callback' ),
            'anubis_importer_settings'
        );

        add_settings_field(
            'anubis_importer_field',
            'URL Productos',
            array( $this, 'anubis_importer_field_callback' ),
            'anubis_importer_settings',
            'anubis_importer_section'
        );

        add_settings_field(
            'anubis_importer_field_2',
            'URL Imagenes',
            array( $this, 'anubis_importer_field_2_callback' ),
            'anubis_importer_settings',
            'anubis_importer_section'
        );

    }

    public function settings_section_callback() {
        echo '<p>' . __( '', 'anubis-importer' ) . '</p>';
    }

    public function anubis_importer_field_callback() {
        ?>
        <input type="password" name="anubis_importer_settings[anubis_importer_field]" value="<?php echo $this->settings['anubis_importer_field']; ?>" style="width:100%;"/>
        <?php
    }

    public function anubis_importer_field_2_callback() {
        ?>
        <input type="password" name="anubis_importer_settings[anubis_importer_field_2]" value="<?php echo $this->settings['anubis_importer_field_2']; ?>" style="width:100%;"/>
        <?php
    }

    public function options_page() {
        ?>
        <div class="wrap">
            <div class="container">
                <div class="postbox-container">
                    <form action='options.php' method='post'>
                        <h1>Anubis Software Importer</h1>
                        <h4>Este plugin se encarga de sincronizar los productos de su Anubis Software a su tienda online con Woocommerce</h4>
                        <p>La sincronizacion de productos se hace automaticamente una vez por hora a través del Cronjob de Wordpress.</p>
                        <hr>
                        <?php
                        settings_fields( 'anubis_importer_settings' );
                        do_settings_sections( 'anubis_importer_settings' );
                        submit_button();
                        ?>
                    </form>

                    <hr>
                    <h4>Este botón activa la importación de forma manual.</h4>
                    <a href="#" onclick="anubis_importer_run();" class="button btn button-primary">Correr importación ahora</a>
                    <div id="response_import"></div>
                    <br>
                    <hr>
                    <b>Resumen</b>
                    <textarea class="form-control" rows="5" id="anubis_importer_field" name="log" style="width:100%; height:300px; background-color:#fff;" readonly><?php echo $this->read_log_file(); ?></textarea> 
                </div>
            </div>
        </div>
        <?php
    }

    /*--------------------------------*/
    /* Read Log File
    /*--------------------------------*/

    public function read_log_file() {
        $myfile = fopen(ANUBIS_IMPORTER_LOG_FILE, 'rt');
        flock($myfile, LOCK_SH);
        $read = file_get_contents(ANUBIS_IMPORTER_LOG_FILE);
        fclose($myfile);

        return $read;
    }

    /*--------------------------------*/
    /* Plugin Notice
    /*--------------------------------*/

    public function plugin_notice() {
        /* if ( !isset($this->settings['anubis_importer_field_3']) || empty($this->settings['anubis_importer_field_3']) ||
            !isset($this->settings['anubis_importer_field_4']) || empty($this->settings['anubis_importer_field_4']) ): ?>
            <div class="notice notice-error is-dismissible">
                <p>Para poder utilizar el plugin de Anubis Software Importer, debe ingresar la API Key de Woocommerce en la página de configuración del plugin.</p>
            </div>
            <?php
        endif; */

        if(!is_plugin_active('woocommerce/woocommerce.php')): ?>
            <div class="notice notice-error is-dismissible">
                <p>Para poder utilizar el plugin de Anubis Software Importer, debe activar el plugin de WooCommerce.</p>
            </div>
            <?php
        endif;
    }

    /*--------------------------------*/
    /* Enqueue Scripts
    /*--------------------------------*/

    public function enqueue_scripts() {

        wp_enqueue_script('anubis-importer-script', plugin_dir_url( __FILE__ ) . '../assets/js/anubis-importer.js', array('jquery'), '1.0.0', true);
        wp_localize_script('anubis_importer_script', 'anubis_importer_ajax', array(
            'ajaxurl' => admin_url( 'admin-ajax.php' ),
            'nonce' => wp_create_nonce('anubis_importer_nonce'),
        ));

        ?>
        <script type='text/javascript'>
        /* <![CDATA[ */
        var bms_vars = {"ajaxurl":"<?php echo bloginfo('url');?>\/wp-admin\/admin-ajax.php"};
        /* ]]> */
        </script>
    <?php }

}