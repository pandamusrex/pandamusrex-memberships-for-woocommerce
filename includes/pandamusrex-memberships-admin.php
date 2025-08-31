<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class PandamusRex_Memberships_Admin {
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
        add_action( 'admin_menu', [ $this, 'admin_menu' ] );
    }

    public function admin_menu(){
        add_menu_page( 
            __( 'Memberships', 'pandamusrex-memberships' ),
            __( 'Memberships', 'pandamusrex-memberships' ),
            'manage_options',
            'pandamusrex_memberships_page',
            [ $this, 'memberships_page' ],
            'dashicons-pets',
            59 // below first separator
        );

        add_submenu_page(
            'pandamusrex_memberships_page',
            __( 'Add Membership', 'pandamusrex-memberships' ),
            __( 'Add Membership', 'pandamusrex-memberships' ),
            'manage_options',
            'pandamusrex_single_membership_page',
            [ $this, 'single_membership_page' ]
        );
    }

    public function memberships_page() {
        echo '<div class="wrap">';
        echo '<h1 class="wp-heading-inline">';
        esc_html_e( 'Memberships', 'pandamusrex-memberships' );
        echo '</h1>';
        echo '<a href="?page=pandamusrex_add_membership_page" class="page-title-action">';
        esc_html_e( 'Add Membership', 'pandamusrex-memberships' );
        echo '</a>';
        echo '<hr class="wp-header-end">';

        echo '<p>&nbsp;</p>';

        echo '<table class="wp-list-table widefat fixed striped table-view-list">';
        echo '<thead>';
        echo '<tr>';
        echo '<th scope="col" class="manage-column">' . esc_html__( 'ID', 'pandamusrex-memberships' ) . '</th>';
        echo '<th scope="col" class="manage-column">' . esc_html__( 'User', 'pandamusrex-memberships' ) . '</th>';
        echo '<th scope="col" class="manage-column">' . esc_html__( 'Product', 'pandamusrex-memberships' ) . '</th>';
        echo '<th scope="col" class="manage-column">' . esc_html__( 'Order', 'pandamusrex-memberships' ) . '</th>';
        echo '<th scope="col" class="manage-column">' . esc_html__( 'Started', 'pandamusrex-memberships' ) . '</th>';
        echo '<th scope="col" class="manage-column">' . esc_html__( 'Ends/Ending', 'pandamusrex-memberships' ) . '</th>';
        echo '<th scope="col" class="manage-column">' . esc_html__( 'Note', 'pandamusrex-memberships' ) . '</th>';
        echo '</tr>';
        echo '</thead>';

        $memberships = PandamusRex_Memberships_Db::getAllMemberships();

        if ( empty( $memberships ) ) {
            echo '<tr class="no-items">';
            echo '<td class="colspanchange" colspan="7">';
            esc_html_e( 'No memberships found.', 'pandamusrex-memberships' );
            echo '</td>';
            echo '</tr>';
        } else {
            foreach ( $memberships as $membership ) {
                echo '<tr>';
                echo '<td>' . esc_html( $membership['id'] ) . '</td>';
                echo '<td>' . esc_html( $membership['user_id'] ) . '</td>';
                echo '<td>' . esc_html( $membership['product_id'] ) . '</td>';
                echo '<td>' . esc_html( $membership['order_id'] ) . '</td>';
                echo '<td>' . esc_html( $membership['membership_starts'] ) . '</td>';
                echo '<td>' . esc_html( $membership['membership_ends'] ) . '</td>';
                echo '<td>' . esc_html( $membership['note'] ) . '</td>';
                echo '</tr>';
            }
        }

        echo '<tfoot>';
        echo '<tr>';
        echo '<th scope="col" class="manage-column">' . esc_html__( 'ID', 'pandamusrex-memberships' ) . '</th>';
        echo '<th scope="col" class="manage-column">' . esc_html__( 'User', 'pandamusrex-memberships' ) . '</th>';
        echo '<th scope="col" class="manage-column">' . esc_html__( 'Product', 'pandamusrex-memberships' ) . '</th>';
        echo '<th scope="col" class="manage-column">' . esc_html__( 'Order', 'pandamusrex-memberships' ) . '</th>';
        echo '<th scope="col" class="manage-column">' . esc_html__( 'Started', 'pandamusrex-memberships' ) . '</th>';
        echo '<th scope="col" class="manage-column">' . esc_html__( 'Ends/Ending', 'pandamusrex-memberships' ) . '</th>';
        echo '<th scope="col" class="manage-column">' . esc_html__( 'Note', 'pandamusrex-memberships' ) . '</th>';
        echo '</tr>';
        echo '</tfoot>';
        echo '</table>';

        echo '</div>';
    }

    public function single_membership_page() {
        $wp_tz = wp_timezone_string();
        $start_dt = new DateTime( "now", new DateTimeZone( $wp_tz ) );
        $ends_dt = new DateTime( "now", new DateTimeZone( $wp_tz ) );
        $ends_dt->add( DateInterval::createFromDateString( '365 days' ) );

        $id = 0;
        $user_id = 0;
        $product_id = 0;
        $order_id = 0;
        $membership_starts = $start_dt->format( "Y-m-d" );
        $membership_ends = $ends_dt->format( "Y-m-d" );
        $note = __( 'Membership added manually', 'pandamusrex-memberships' );

        // If we have been passed a membership ID in $_GET[ 'edit_id' ]
        // let's grab its details and allow for editing
        if ( ( isset( $_GET[ 'membership_id' ] ) ) ) {
            $id = sanitize_text_field( $_GET[ 'membership_id' ] );
            $id = intval( $id );
            $membership = PandamusRex_Memberships_Db::getMembershipByID( $id );
            if ( ! empty( $membership ) ) {
                $id = $membership['id'];
                $user_id = $membership['user_id'];
                $product_id = $membership['product_id'];
                $order_id = $membership['order_id'];
                $membership_starts = $membership['membership_starts'];
                $membership_ends = $membership['membership_ends'];
                $note = $membership['note'];
            } else {
                $id = 0;
            }
        }

        wc_get_logger()->debug( "ID: $id" );
        wc_get_logger()->debug( "User ID: $user_id" );
        wc_get_logger()->debug( "Product ID: $product_id" );
        wc_get_logger()->debug( "Order ID: $order_id" );
        wc_get_logger()->debug( "Starts: $membership_starts" );
        wc_get_logger()->debug( "Ends: $membership_ends" );
        wc_get_logger()->debug( "Note: $note" );

        echo '<div class="wrap">';
        if ( $id == 0 ) {
            echo '<h1 class="wp-heading-inline">';
            esc_html_e( 'Add Membership', 'pandamusrex-memberships' );
            echo '<p>';
            esc_html_e( 'Manually enter a membership for a user.', 'pandamusrex-memberships' );
            echo '</p>';
            echo '</h1>';
        } else {
            echo '<h1 class="wp-heading-inline">';
            esc_html_e( 'Edit Membership', 'pandamusrex-memberships' );
            echo '</h1>';
        }
        echo '<hr class="wp-header-end">';

        echo '<form method="post">';

        echo '<table class="form-table">';
        echo '<tbody>';

        echo '<tr class="form-field">';
        echo '<th>';
        echo '<label for="">';
        esc_html_e( 'ID', 'pandamusrex-memberships' );
        echo '</label>';
        echo '</th>';
        echo '<td>';
        if ( $id == 0 ) {
            esc_html_e( '(Automatically assigned)', 'pandamusrex-memberships' );
        } else {
            echo esc_html( $id );
        }
        echo '<input name="id" type="hidden" id="id" value="' . esc_attr( $id ) . '">';
        echo '</td>';
        echo '</tr>';

        echo '<tr class="form-field">';
        echo '<th>';
        echo '<label for="">';
        esc_html_e( 'User', 'pandamusrex-memberships' );
        echo '</label>';
        echo '</th>';
        echo '<td>';
        echo '<select>';
        $loop_users = get_users();
        foreach ( $loop_users as $loop_user ) {
            $loop_user_id = $loop_user->ID;
            $selected = ( $loop_user_id == $user_id ) ? 'selected' : '';
            echo '<option value="' . esc_attr( $loop_user_id ) . '" ' . $selected . '>';
            echo esc_html( '#' . $loop_user_id . ' - ' . $loop_user->display_name . ' - ' . $loop_user->user_email );
            echo '</option>';
        }
        echo '</select>';
        echo '</td>';
        echo '</tr>';

        echo '<tr class="form-field">';
        echo '<th>';
        echo '<label for="">';
        esc_html_e( 'Product', 'pandamusrex-memberships' );
        echo '</label>';
        echo '</th>';
        echo '<td>';
        echo '<select>';
        $args = array(
            'limit'      => -1,
            'status'     => 'publish',
            'meta_key'   => '_pandamusrex_prod_incl_membership'
        );
        $loop_products = wc_get_products( $args );
        echo '<option value="0">';
        echo esc_html( 'None', 'pandamusrex-memberships' );
        echo '</option>';
        foreach ( $loop_products as $loop_product ) {
            $loop_product_id = $loop_product->get_id();
            $selected = ( $loop_product_id == $product_id ) ? 'selected' : '';
            echo '<option value="' . esc_attr( $loop_product_id ) . '" ' . $selected . '>';
            echo esc_html( '#' . $loop_product_id . ' - ' . $loop_product->get_title() );
            echo '</option>';
        }
        echo '</select>';
        echo '</td>';
        echo '</tr>';

        echo '<tr class="form-field">';
        echo '<th>';
        echo '<label for="">';
        esc_html_e( 'Order', 'pandamusrex-memberships' );
        echo '</label>';
        echo '</th>';
        echo '<td>';
        echo '<select>';
        echo '<option value="0">';
        echo esc_html( 'None', 'pandamusrex-memberships' );
        echo '</option>';
        $args = array(
            'limit'      => -1,
        );
        $loop_orders = wc_get_orders( $args );
        foreach ( $loop_orders as $loop_order ) {
            $loop_customer_id = $loop_order->get_customer_id();
            if ( $loop_customer_id ) {
                $loop_order_id = $loop_order->get_id();
                $loop_order_date = $loop_order->get_date_created();
                $formatted_loop_order_date = $loop_order_date->date( 'd/m/Y' );
                $loop_customer = new WC_Customer( $loop_customer_id );
                $loop_customer_name = $loop_customer->get_first_name() . " " . $loop_customer->get_last_name();
                $loop_customer_email = $loop_customer->get_email();
                $selected = ( $loop_order_id == $order_id ) ? 'selected' : '';
                echo '<option value="' . esc_attr( $loop_order_id ) . '" ' . $selected . '>';
                echo esc_html( '#' . $loop_order_id . ' - ' . $loop_customer_name . ' - ' . $loop_customer_email . ' - ' . $formatted_loop_order_date );
                echo '</option>';
            }
        }
        echo '</select>';
        echo '</td>';
        echo '</tr>';

        echo '<tr class="form-field">';
        echo '<th>';
        echo '<label for="">';
        esc_html_e( 'Membership Starts/Started', 'pandamusrex-memberships' );
        echo '</label>';
        echo '</th>';
        echo '<td>';
        echo '<input name="membership_starts" type="date" value="' . esc_attr( $membership_starts ) . '" />';
        echo '</td>';
        echo '</tr>';

        echo '<tr class="form-field">';
        echo '<th>';
        echo '<label for="">';
        esc_html_e( 'Membership Ends/Ended', 'pandamusrex-memberships' );
        echo '</label>';
        echo '</th>';
        echo '<td>';
        echo '<input name="membership_ends" type="date" value="' . esc_attr( $membership_ends ) . '"/>';
        echo '</td>';
        echo '</tr>';

        echo '<tr class="form-field">';
        echo '<th>';
        echo '<label for="">';
        esc_html_e( 'Note', 'pandamusrex-memberships' );
        echo '</label>';
        echo '</th>';
        echo '<td>';
        echo '<input name="note" type="text" id="note" value="' . esc_attr( $note ) . '" />';
        echo '</td>';
        echo '</tr>';

        echo '</tbody>';
        echo '</table>';

        echo '</form>';

        echo '<p class="submit">';
        $button_label = __( 'Save Changes', 'pandamusrex-memberships' );
        if ( $id == 0 ) {
            $button_label = __( 'Create Membership', 'pandamusrex-memberships' );
        }
        echo '<input type="submit" name="add_edit_membership" id="add_edit_membership" class="button button-primary" value="' .
            $button_label .
            '" />';
        echo '</p>';

        echo '</div>';
    }
}

PandamusRex_Memberships_Admin::get_instance();