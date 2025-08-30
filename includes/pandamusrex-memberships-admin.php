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
        echo '<th scope="col" class="manage-column">User</th>';
        echo '<th scope="col" class="manage-column">Product</th>';
        echo '<th scope="col" class="manage-column">Order</th>';
        echo '<th scope="col" class="manage-column">Started</th>';
        echo '<th scope="col" class="manage-column">Ends/Ending</th>';
        echo '<th scope="col" class="manage-column">Notes</th>';
        echo '</tr>';
        echo '</thead>';

        $memberships = PandamusRex_Memberships_Db::getAllMemberships();

        if ( empty( $memberships ) ) {
            echo '<tr class="no-items">';
            echo '<td class="colspanchange" colspan="6">';
            esc_html_e( 'No memberships found.', 'pandamusrex-memberships' );
            echo '</td>';
            echo '</tr>';
        } else {
            foreach ( $memberships as $membership ) {
                echo '<tr>';
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
        echo '<th scope="col" class="manage-column">User</th>';
        echo '<th scope="col" class="manage-column">Product</th>';
        echo '<th scope="col" class="manage-column">Order</th>';
        echo '<th scope="col" class="manage-column">Started</th>';
        echo '<th scope="col" class="manage-column">Ends/Ending</th>';
        echo '<th scope="col" class="manage-column">Notes</th>';
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
        $membership_ends = $membership_ends = $ends_dt->format( "Y-m-d" );
        $note = __( 'Membership added manually', 'pandamusrex-memberships' );

        // If we have been passed a membership ID in $_GET[ 'edit_id' ]
        // let's grab its details and allow for editing
        if ( ( isset( $_GET[ 'membership_id' ] ) ) ) {
            $id = sanitize_text_field( $_GET[ 'membership_id' ] );
            $id = intval( $id );
            $membership = PandamusRex_Memberships_Db::getMembershipByID( $id );
            if ( ! empty( $membership ) ) {
                $id = $membership->id;
                $user_id = $membership->user_id;
                $product_id = $membership->product_id;
                $order_id = $membership->order_id;
                $membership_starts = $membership->membership_starts;
                $membership_ends = $membership->memberships_ends;
                $note = $membership->note;
            }
        }

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
        echo '</td>';
        echo '</tr>';

        echo '<tr class="form-field">';
        echo '<th>';
        echo '<label for="">';
        esc_html_e( 'User', 'pandamusrex-memberships' );
        echo '</label>';
        echo '</th>';
        echo '<td>';
        $users = get_users();
        foreach ( $users as $user ) {
            echo '<select>';
            echo '<option value="' . esc_attr( $user->ID ) . '">';
            echo esc_html( $user->display_name ) . ' ' . esc_html( $user->email );
            echo '</option>';
            echo '</select>';
        }
        echo '</td>';
        echo '</tr>';

        echo '<tr class="form-field">';
        echo '<th>';
        echo '<label for="">';
        esc_html_e( 'Product', 'pandamusrex-memberships' );
        echo '</label>';
        echo '</th>';
        echo '<td>';
        // TODO product picker
        echo '</td>';
        echo '</tr>';

        echo '<tr class="form-field">';
        echo '<th>';
        echo '<label for="">';
        esc_html_e( 'Order', 'pandamusrex-memberships' );
        echo '</label>';
        echo '</th>';
        echo '<td>';
        // TODO order picker
        echo '</td>';
        echo '</tr>';

        echo '<tr class="form-field">';
        echo '<th>';
        echo '<label for="">';
        esc_html_e( 'Membership Starts/Started', 'pandamusrex-memberships' );
        echo '</label>';
        echo '</th>';
        echo '<td>';
        // TODO membership starts picker
        echo '</td>';
        echo '</tr>';

        echo '<tr class="form-field">';
        echo '<th>';
        echo '<label for="">';
        esc_html_e( 'Membership Ends/Ended', 'pandamusrex-memberships' );
        echo '</label>';
        echo '</th>';
        echo '<td>';
        // TODO membership ends picker
        echo '</td>';
        echo '</tr>';

        echo '<tr class="form-field">';
        echo '<th>';
        echo '<label for="">';
        esc_html_e( 'Note', 'pandamusrex-memberships' );
        echo '</label>';
        echo '</th>';
        echo '<td>';
        echo '<input name="note" type="text" id="note" value="">';
        echo '</td>';
        echo '</tr>';

        echo '</tbody>';
        echo '</table>';

        echo '<p class="submit">';
        echo '<input type="submit" name="createmembership" id="createmembershipsub" class="button button-primary" value="' .
            esc_attr_e( 'Add Membership', 'pandamusrex-memberships' ) .
            '">';
        echo '</p>';

        echo '</div>';
    }
}

PandamusRex_Memberships_Admin::get_instance();