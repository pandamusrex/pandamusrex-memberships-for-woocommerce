<?php
/**
 * Plugin Name: PandamusRex Memberships for WooCommerce
 * Version: 1.0.0
 * Plugin URI: https://github.com/pandamusrex/pandamusrex-memberships-for-woocommerce
 * Description: Buying this product gets you a membership!
 * Author: PandamusRex
 * Author URI: https://www.github.com/pandamusrex/
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Requires at least: 6.4
 * Requires PHP: 7.0
 * Tested up to: 6.8
 *
 * Text Domain: pandamusrex-memberships
 * Domain Path: /lang/
 *
 * @package WordPress
 * @author PandamusRex
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class PandamusRex_Memberships {
    private static $instance;

    public static function get_instance() {
        if ( null == self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __clone() {}

    public function __wakeup() {}

    public function __construct() {
        add_action( 'add_meta_boxes', array( $this, 'add_meta_box' ) );
        add_action( 'save_post', array( $this, 'save_postdata' ) );
    }

    function add_meta_box() {
        add_meta_box( 'pandamusrex_memberships_sectionid', __( 'Memberships', 'pandamusrex-memberships' ), array( $this, 'meta_box' ), 'product', 'side', 'high' );
    }

    function meta_box( $product ) {
        echo '<input type="hidden" name="pandamusrex_memberships_nonce" id="pandamusrex_memberships_nonce" value="' . esc_attr( wp_create_nonce( 'pandamusrex_memberships-' . $product->ID ) ) . '" />';

        $prod_incl_membership = get_post_meta( $product->ID, '_pandamusrex_prod_incl_membership', false );

        echo '<input type="checkbox" id="_pandamusrex_prod_incl_membership" name="_pandamusrex_prod_incl_membership" ' .
            ( $prod_incl_membership ? "checked" : "" ) .
            '/>';
        echo '<label for="_pandamusrex_prod_incl_membership">' .
            esc_html__( 'Product includes membership', 'pandamusrex-memberships' ) .
            '</label>';
    }

    function save_postdata( $product_id ) {
        if ( ! isset ( $_POST['pandamusrex_memberships_nonce'] ) )
            return $product_id;

        $nonce = sanitize_text_field( wp_unslash( $_POST['pandamusrex_memberships_nonce'] ) );
        if ( ! wp_verify_nonce( $nonce, 'pandamusrex_memberships-' . $product_id ) )
            return $product_id;

        // verify if this is an auto save routine. If it is our form has not been submitted, so we dont want
        // to do anything
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
            return $product_id;

        if (! isset( $_POST['post_type'] ) )
            return $product_id;

        // Check permissions
        if ( 'page' == $_POST['post_type'] ) {
            if ( ! current_user_can( 'edit_page', $product_id ) )
                return $product_id;
        } else {
            if ( ! current_user_can( 'edit_post', $product_id ) )
                return $product_id;
        }

        $prod_incl_membership = FALSE;
        if ( isset( $_POST['_pandamusrex_prod_incl_membership']) ) {
            $prod_incl_membership = TRUE;
        }

        if ( empty( $prod_incl_membership ) ) {
            delete_post_meta( $product_id, '_pandamusrex_prod_incl_membership' );
        } else {
            update_post_meta( $product_id, '_pandamusrex_prod_incl_membership', TRUE );
        }

        return $product_id;
    }
}

PandamusRex_Memberships::get_instance();
