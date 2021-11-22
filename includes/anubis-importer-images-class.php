<?php

class Anubis_Importer_Images extends Anubis_Importer_Settings {

    use Errors;

    public $product_id = 0;
    public $product_sku = 0;
    public $image_id = 0;
    public $base_url;
    public $log_error = array();


    public function __construct($product) {
        require_once( ABSPATH . 'wp-admin/includes/image.php' );

        $this->product_id = $product->get_id();
        $this->product_sku = $product->get_sku();
        $this->settings = get_option('anubis_importer_settings');
        $this->base_url = $this->settings['anubis_importer_field_2'];

        $this->process();
    }

    public function process() {
        // if no image are upload
        if ( !$this->get_the_product_thumbnail_url($this->product_id) ) {
            // If return content upload image
            if ($content = @file_get_contents($this->get_image_url()) ) {
                // Upload image
                $this->store_image($this->product_id, $content);
            }
        }
    }

    public function get_the_product_thumbnail_url($product_id) {
        return get_the_post_thumbnail_url( $product_id, 'full' );
    }

    public function get_image_url() {
        return $this->base_url . $this->product_sku . '.jpg';
    }

    /**
     * Upload and store image
     */
    public function store_image( $product_id, $content ) {
        $name = $this->product_sku . '.jpg';
        $upload_file = wp_upload_bits( $name, null, $content );
        if ( ! $upload_file['error'] ) {
            
            $wp_filetype = wp_check_filetype( basename( $name ), null );
            
            $attachment = array(
                'post_mime_type' => $wp_filetype['type'],
                'post_parent'    => $product_id,
                'post_title'     => preg_replace( '/\.[^.]+$/', '', basename( $name ) ),
                'post_content'   => '',
                'post_status'    => 'inherit'
            );
            
            $attachment_id = wp_insert_attachment( $attachment, $upload_file['file'], $product_id );
            
            if ( ! is_wp_error( $attachment_id ) ) {            
                $this->add_image_to_product( $attachment_id, $product_id, $upload_file );
            }
        }
    }


    public function add_image_to_product($attachment_id, $product_id, $upload_file) {
        try {

            $attachment_data = wp_generate_attachment_metadata( $attachment_id, $upload_file['file'] );
            wp_update_attachment_metadata( $attachment_id,  $attachment_data );
            set_post_thumbnail( $product_id, $attachment_id );

        } catch (\Throwable $th) {
            $this->log_error[] = 'Error adding image to product: ' . $th->getMessage();
            $this->log_error[] = 'Product ID: ' . $product_id;
            $this->log_error[] = 'Attachment ID: ' . $attachment_id;
            $this->log_error[] = 'Upload File: ' . $upload_file['file'];
            $this->log_errors();
        }
    }
        
}