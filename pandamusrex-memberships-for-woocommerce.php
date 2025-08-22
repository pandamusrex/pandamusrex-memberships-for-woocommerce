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

require_once( plugin_dir_path(__FILE__) . 'includes/pandamusrex-memberships-db.php' );
register_activation_hook( __FILE__, [ 'PandamusRex_Memberships_Db', 'create_tables' ] );

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
        add_action( 'add_meta_boxes', [ $this, 'add_meta_box' ] );
        add_action( 'save_post', [ $this, 'save_postdata' ] );
        add_filter( 'manage_users_columns', [ $this, 'manage_users_columns' ] );
        add_filter( 'manage_users_custom_column',  [ $this, 'manage_users_custom_column' ], 10, 3 );
        add_filter( 'woocommerce_account_menu_items', [ $this, 'add_memberships_my_account_tab' ] );
        add_action( 'woocommerce_account_memberships-tab_endpoint', [ $this, 'memberships_my_account_tab_content' ] );
        add_action( 'init', [ $this, 'add_memberships_tab_endpoint' ] );
        add_filter( 'query_vars', [ $this, 'add_custom_query_vars' ], 0 );
        add_action( 'woocommerce_payment_complete', [ $this, 'woocommerce_payment_complete' ] );
    }

    public function add_meta_box() {
        add_meta_box( 'pandamusrex_memberships_sectionid', __( 'Memberships', 'pandamusrex-memberships' ), array( $this, 'meta_box' ), 'product', 'side', 'high' );
    }

    public function meta_box( $product ) {
        echo '<input type="hidden" name="pandamusrex_memberships_nonce" id="pandamusrex_memberships_nonce" value="' . esc_attr( wp_create_nonce( 'pandamusrex_memberships-' . $product->ID ) ) . '" />';

        $prod_incl_membership = get_post_meta( $product->ID, '_pandamusrex_prod_incl_membership', false );

        echo '<input type="checkbox" id="_pandamusrex_prod_incl_membership" name="_pandamusrex_prod_incl_membership" ' .
            ( $prod_incl_membership ? "checked" : "" ) .
            '/>';
        echo '<label for="_pandamusrex_prod_incl_membership">' .
            esc_html__( 'Product includes membership', 'pandamusrex-memberships' ) .
            '</label>';
    }

    public function save_postdata( $product_id ) {
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

    public function manage_users_columns( $columns ) {
        $columns[ 'membership' ] = __( 'Membership Ended/Ends', 'pandamusrex-memberships' );
        return $columns;
    }

    public function manage_users_custom_column( $value, $column_name, $user_id ) {
        if ( 'membership' == $column_name ) {
            $most_recent = PandamusRex_Memberships_Db::getMostRecentMembershipForUser( $user_id );

            $link_text = '';
            if ( empty( $most_recent ) ) {
                $link_text = esc_html__( 'N/A', 'pandamusrex-memberships' );
            } else {
                $link_text = esc_html( $most_recent[ 'membership_ends' ] );
            }
            return $link_text;
        }

        return $value;
    }

    public function add_memberships_my_account_tab( $items ) {
        $new_array = [];
        $did_insert = false;

        foreach ( $items as $key => $value ) {
            $new_array[ $key ] = $value;
            if ( $key == 'orders' ) {
                $new_array[ 'memberships-tab' ] = __( 'Membership', 'pandamusrex-memberships' );
                $did_insert = true;
            }
        }

        // Just in case we never found Orders for some reason
        if ( ! $did_insert ) {
            $new_array[ 'memberships-tab' ] = __( 'Membership', 'pandamusrex-memberships' );
        }

        return $new_array;
    }

    public function memberships_my_account_tab_content() {
        echo '<h2>Membership</h2>';

        $user_id = get_current_user_id();
        $memberships = PandamusRex_Memberships_Db::getAllMembershipsByUser( $user_id );

        if ( empty( $memberships ) ) {
            echo '<p>';
            echo esc_html__( 'You have no membership... yet!', 'pandamusrex-memberships' );
            echo '</p>';
        } else {
            echo '<table class="shop_table">';
            echo '<tr>';
            echo '<th>';
            echo esc_html__( 'Order #', 'pandamusrex-memberships' );
            echo '</th>';
            echo '<th>';
            echo esc_html__( 'Membership Started', 'pandamusrex-memberships' );
            echo '</th>';
            echo '<th>';
            echo esc_html__( 'Membership Ended/Ends', 'pandamusrex-memberships' );
            echo '</th>';
            echo '</tr>';

            foreach( $memberships as $membership ) {
                echo '<tr>';
                echo '<td>';
                echo $membership[ 'order_id' ];
                echo '</td>';
                echo '<td>';
                echo $membership[ 'membership_starts' ];
                echo '</td>';
                echo '<td>';
                echo $membership[ 'membership_ends' ];
                echo '</td>';
                echo '</td>';
                echo '</tr>';
            }

            echo '</table>';
        }
    }

    public function add_memberships_tab_endpoint() {
        add_rewrite_endpoint( 'memberships-tab', EP_ROOT | EP_PAGES );
    }

    public function add_custom_query_vars( $vars ) {
        $vars[] = 'memberships-tab';
        return $vars;
    }

    public function woocommerce_payment_complete( $order_id ) {
        $order = wc_get_order( $order_id );

        $user_id = $order->get_user_id();
        if ( $user_id == 0 ) {
            // Guest?!
            return;
        }

        $found_product_id = 0;

        foreach ( $order->get_items() as $item ) {
            $product_id = $item->get_product_id();
            $prod_incl_membership = get_post_meta( $product_id, '_pandamusrex_prod_incl_membership', false );
            if ( $prod_incl_membership ) {
                $found_product_id = $product_id;
                break;
            }
        }

        $wp_tz = wp_timezone_string();
        $start_dt = new DateTime( "now", new DateTimeZone( $wp_tz ) );
        // Database expects YYYY-MM-DD
        $membership_starts = $start_dt->format( "Y-m-d" );

        $ends_dt = new DateTime( "now", new DateTimeZone( $wp_tz ) );
        $ends_dt->add( DateInterval::createFromDateString( '365 days' ) );
        $membership_ends = $ends_dt->format( "Y-m-d" );

        PandamusRex_Memberships_Db::addMembershipForUser(
            $user_id,
            $product_id,
            $order_id,
            $membership_starts,
            $membership_ends,
            ''
        );
    }
}

PandamusRex_Memberships::get_instance();
