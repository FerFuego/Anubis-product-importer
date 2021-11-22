<?php

class Anubis_Importer_Process extends Anubis_Importer_Settings {

    use Errors;

    private $anubis_products = array();
    private $count_products = 0;
    private $count_updated = 0;
    private $count_created = 0;
    private $count_error = array();
    public $log_error = array();

    public function __construct() {
        $this->settings = get_option('anubis_importer_settings');
    }

    /**
    * Get External Content
    */
    public function anubis_importer_process_init() {

        if ( !empty($this->settings['anubis_importer_field']) ):
            $anubis_products_json = file_get_contents($this->settings['anubis_importer_field']);
            $this->anubis_products = json_decode( $anubis_products_json, true );
        endif;
        
        if ( !empty($this->anubis_products) ):
            $this->count_products = @count($this->anubis_products);
            $this->anubis_importer_process_products();
        endif;

        wp_send_json_success( $this->log_resume() );
    }

    /**
    * Process Products
    */
    public function anubis_importer_process_products() {

        foreach ( $this->anubis_products as $anubis_product ):

            $product = $this->anubis_importer_process_product_id($anubis_product);

            if ( $product ):
                // Upload product
                $this->update_anubis_products($product, $anubis_product);
                $this->count_updated++;
            else:
                // Insert Product
                $product = $this->create_anubis_products($anubis_product);
                if ( $product && $product->get_id() ):
                    $this->count_created++;
                else:
                    $this->count_error[] = $anubis_product['sku'];
                endif;
            endif;

            if ( $product && $product->get_id() ):
                // Add Categories
                $this->anubis_importer_process_taxonomies($anubis_product, $product);

                if ( !empty($this->settings['anubis_importer_field_2'] ) ){
                    // Add Images
                    new Anubis_Importer_Images($product);
                }
            endif;

            // Clean vars
            $product = null;

        endforeach;
    }

    /**
    * Get Product existence
    */
    public function anubis_importer_process_product_id($anubis_product) {
        global $wpdb;
        $product_id = $wpdb->get_var( $wpdb->prepare( "SELECT post_id FROM $wpdb->postmeta WHERE meta_key='_sku' AND meta_value='%s' LIMIT 1", $anubis_product['sku'] ) );
        if ( $product_id ) return new WC_Product( $product_id );
        return null;
    }

    /**
    * Create Products
    */
    public function create_anubis_products($product) {

        try {

            if ($product['stock_quantity'] > 0):
                $product["manage_stock"] = true;
            endif;
    
            $request = new WP_REST_Request( 'POST' );
            $request->set_body_params( $product );
            $products_controller = new WC_REST_Products_Controller;
            $response = $products_controller->create_item( $request );
    
            return new WC_Product( $response->data['id'] );

        } catch (\Throwable $th) {
            $this->log_error[] = 'Error adding product: ' . $th->getMessage();
            $this->log_error[] = 'Product ID: ' . $product->get_sku();
            $this->log_errors();
        }
    }

    /**
    * Update Products
    */
    public function update_anubis_products($product, $anubis_product) {

        try {

            if (0 === $anubis_product['stock_quantity']):
                $anubis_product['stock_status'] = 'outofstock';
            else:
                $anubis_product['stock_status'] = 'instock';
            endif;

            $product->set_name( $anubis_product['name'] );
            $product->set_description( $anubis_product['description'] );
            $product->set_short_description( $anubis_product['description'] );
            $product->set_price( $anubis_product['regular_price'] );
            $product->set_regular_price( $anubis_product['regular_price'] );
            $product->set_manage_stock( true );
            $product->set_stock_quantity( $anubis_product['stock_quantity'] );
            $product->set_stock_status( $anubis_product['stock_status'] );
            //$product->set_status( $anubis_product['status'] );
            //$product->set_sale_price( $anubis_product['sale_price'] );
            //$product->set_date_on_sale_from( $anubis_product['date_on_sale_from'] );
            //$product->set_date_on_sale_to( $anubis_product['date_on_sale_to'] );
            //$product->set_total_sales( $anubis_product['total_sales'] );
            //$product->set_tax_status( $anubis_product['tax_status'] );
            //$product->set_tax_class( $anubis_product['tax_class'] );
            //$product->set_stock_status( $anubis_product['stock_status'] );
            //$product->set_backorders( $anubis_product['backorders'] );
            //$product->set_sold_individually( $anubis_product['sold_individually'] );    
            //$product->set_weight( $anubis_product['weight'] );
            //$product->set_length( $anubis_product['length'] );
            //$product->set_width( $anubis_product['width'] );
            //$product->set_height( $anubis_product['height'] );
            //$product->set_virtual( $anubis_product['virtual'] );
            //$product->set_downloadable( $anubis_product['downloadable'] );
            //$product->set_downloads( $anubis_product['downloads'] );
            //$product->set_download_limit( $anubis_product['download_limit'] );
            //$product->set_download_expiry( $anubis_product['download_expiry'] );
            //$product->set_upsell_ids( $anubis_product['upsell_ids'] );
            //$product->set_cross_sell_ids( $anubis_product['cross_sell_ids'] );
            //$product->set_parent_id( $anubis_product['parent_id'] );
            //$product->set_purchase_note( $anubis_product['purchase_note'] );
            //$product->set_default_attributes( $anubis_product['default_attributes'] );
            //$product->set_menu_order( $anubis_product['menu_order'] );
            //$product->set_category_ids( $anubis_product['category_ids'] );
            //$product->set_tag_ids( $anubis_product['tag_ids'] );
            //$product->set_shipping_class_id( $anubis_product['shipping_class_id'] );
            //$product->set_images( $anubis_product['images'] );
            //$product->set_attributes( $anubis_product['attributes'] );
            //$product->set_default_attributes( $anubis_product['default_attributes'] );
            //$product->set_downloads( $anubis_product['downloads'] );
            //$product->set_download_limit( $anubis_product['download_limit'] );
            //$product->set_download_expiry( $anubis_product['download_expiry'] );
            //$product->set_download_type( $anubis_product['download_type'] );
            //$product->set_purchase_note( $anubis_product['purchase_note'] );
            //$product->set_total_sales( $anubis_product['total_sales'] );
            //$product->set_tax_status( $anubis_product['tax_status'] );
            //$product->set_tax_class( $anubis_product['tax_class'] );
            $product->save();

        } catch (\Throwable $th) {
            $this->log_error[] = 'Error updating product: ' . $th->getMessage();
            $this->log_error[] = 'Product ID: ' . $product->get_sku();
            $this->log_errors();
        }
    }

    /**
     * Insert Categories 
     */
    public function anubis_importer_process_taxonomies($anubis_product, $product) {
        try {
            wp_set_object_terms($product->get_id(), $anubis_product['categories'], 'product_cat');
        } catch (\Throwable $th) {
            $this->log_error[] = 'Error adding category: ' . $th->getMessage();
            $this->log_error[] = 'Product ID: ' . $product->get_sku();
            $this->log_errors();
        }
    }

    /**
     * Resume Log
     */
    public function log_resume() {
        $log = date("F j, Y, g:i a", current_time( 'timestamp', 0 )).PHP_EOL.
        "Productos Encontrados: " . $this->count_products.PHP_EOL.
        "Productos Creados: " . $this->count_created.PHP_EOL.
        "Productos Actualizados: " . $this->count_updated.PHP_EOL.
        "Cantidad de Errores: " . count($this->count_error).PHP_EOL;
        if (count($this->count_error) > 0) {
            $log .= "Productos Error: " . implode(", ", $this->count_error).PHP_EOL;
        }
        $log .= "---------------------------------------------".PHP_EOL;
        file_put_contents(ANUBIS_IMPORTER_LOG_FILE, $log, FILE_APPEND);

        return $log;
    }

}