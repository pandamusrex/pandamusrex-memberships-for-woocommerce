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
            [ $this, 'echo_memberships_page' ],
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

    public function echo_memberships_page() {
        if ( ! function_exists( 'wc_get_logger' ) ) {
            wp_admin_notice(
                __( 'PandamusRex Memberships for WooCommerce requires the WooCommerce plugin to be active.', 'pandamusrex-memberships' ),
                [ 'error' ]
            );
            return;
        }

        echo '<div class="wrap">';
        echo '<h1 class="wp-heading-inline">';
        esc_html_e( 'Memberships', 'pandamusrex-memberships' );
        echo '</h1>';
        echo '<hr class="wp-header-end">';

        echo '<p>&nbsp;</p>';

        echo '<table class="wp-list-table widefat fixed striped table-view-list">';
        echo '<thead>';
        echo '<tr>';
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
            echo '<td class="colspanchange" colspan="6">';
            esc_html_e( 'No memberships found.', 'pandamusrex-memberships' );
            echo '</td>';
            echo '</tr>';
        } else {
            foreach ( $memberships as $membership ) {
                echo '<tr>';
                echo '<td>';
                $user = get_user_by( 'id', $membership['user_id'] );
                $this->echo_user( $user );
                echo '<div class="row-actions">';
                echo '<span class="id">';
                echo esc_html__( 'ID:', 'pandamusrex-memberships' );
                echo ' ';
                echo esc_html( $membership['id'] );
                echo ' | ';
                echo '</span>';
                echo '<span class="edit">';
                $edit_url = "?page=pandamusrex_single_membership_page&action=edit&membership_id=" . $membership['id'];
                echo '<a href="' . $edit_url . '">';
                echo esc_html__( 'Edit', 'pandamusrex-memberships' );
                echo '</a>';
                echo ' | ';
                echo '<span class="delete">';
                $delete_url = "?page=pandamusrex_single_membership_page&action=delete&membership_id=" . $membership['id'];
                echo '<a href="' . $delete_url . '">';
                echo esc_html__( 'Delete', 'pandamusrex-memberships' );
                echo '</a>';
                echo '</span>';
                echo '</div>';
                echo '</td>';
                echo '<td>';
                $product_id = $membership['product_id'];
                if ( $product_id == 0 ) {
                    echo '-';
                } else {
                    echo esc_html( $product_id );
                }
                echo '</td>';
                echo '<td>';
                $order_id = $membership['order_id'];
                if ( $order_id == 0 ) {
                    echo '-';
                } else {
                    echo esc_html( $order_id );
                }
                echo '</td>';
                echo '<td>' . esc_html( $membership['membership_starts'] ) . '</td>';
                echo '<td>' . esc_html( $membership['membership_ends'] ) . '</td>';
                echo '<td>' . esc_html( $membership['note'] ) . '</td>';
                echo '</tr>';
            }
        }

        echo '<tfoot>';
        echo '<tr>';
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

    public function echo_delete_confirmation_page() {
        echo '<div class="wrap">';
        echo '<h1 class="wp-heading-inline">';
        esc_html_e( 'Delete Membership', 'pandamusrex-memberships' );
        echo '<p>';
        esc_html_e( 'Permanently delete a membership.', 'pandamusrex-memberships' );
        echo '</p>';
        echo '</h1>';
        echo '<hr class="wp-header-end">';

        echo '<form method="post">';

        $nonce =  wp_create_nonce( 'membership-' . $id );
        echo '<input type="hidden" name="membership_nonce" id="membership_nonce" value="' . esc_attr( $nonce ) . '" />';

        echo '<p class="submit">';
        $button_label = __( 'Delete Membership', 'pandamusrex-memberships' );
        echo '<input type="submit" name="delete_membership" id="delete_membership" class="button button-primary" value="' .
            $button_label .
            '" />';
        echo '</p>';

        echo '</form>';

        echo '</div>';
    }

    public function single_membership_page() {
        if ( ! function_exists( 'wc_get_logger' ) ) {
            wp_admin_notice(
                __( 'PandamusRex Memberships for WooCommerce requires the WooCommerce plugin to be active.', 'pandamusrex-memberships' ),
                [ 'error' ]
            );
            return;
        }

        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }

        // Get our POST action, if any
        // POST action can be do_create do_delete or do_update
        if ( isset( $_POST['action'] ) ) {
            $post_action = $_POST['action'];
            if ( $post_action == 'do_create' ) {
                $this->echo_do_create();
                return;
            }

            if ( $post_action == 'do_delete' ) {
                $this->echo_do_delete();
                return;
            }

            if ( $post_action == 'do_update' ) {
                $this->echo_do_update();
                return;
            }

            wp_admin_notice(
                __( 'Invalid POST action', 'pandamusrex-memberships' )
                [ 'error' ]
            );
            return;
        }

        // Get our GET action
        // If edit, echo edit form
        // If delete, echo delete confirmation form
        if ( isset( $_GET['action'] ) ) {
            $get_action = $_GET['action'];
            if ( $get_action == 'edit' ) {
                $this->echo_edit_form();
                return;
            }

            if ( $get_action == 'delete' ) {
                $this->echo_delete_confirmation_form();
                return;
            }

            wp_admin_notice(
                __( 'Invalid GET action', 'pandamusrex-memberships' )
                [ 'error' ]
            );
            return;
        }

        // If no POST action and no GET action, edit the membership creation form
        $this->echo_create_form();
    }

    public function echo_user( $user ) {
        if ( $user ) {
            echo esc_html( '#' .
                $user->ID .
                ' - ' .
                $user->display_name .
                ' - ' .
                $user->user_email
            );
        } else {
            echo '-';
        }
    }

    public function echo_user_selector( $preselect_user_id = 0 ) {
        echo '<select name="user_id" id="user_id">';
        $loop_users = get_users();
        foreach ( $loop_users as $loop_user ) {
            $loop_user_id = $loop_user->ID;
            $selected = ( $loop_user_id == $preselect_user_id ) ? 'selected' : '';
            echo '<option value="' . esc_attr( $loop_user_id ) . '" ' . $selected . '>';
            $this->echo_user( $loop_user );
            echo '</option>';
        }
        echo '</select>';
    }

    public function echo_order_selector( $preselect_order_id = 0 ) {
        echo '<select name="order_id" id="order_id">';
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
                $selected = ( $loop_order_id == $preselect_order_id ) ? 'selected' : '';
                echo '<option value="' . esc_attr( $loop_order_id ) . '" ' . $selected . '>';
                echo esc_html( '#' . $loop_order_id . ' - ' . $loop_customer_name . ' - ' . $loop_customer_email . ' - ' . $formatted_loop_order_date );
                echo '</option>';
            }
        }
        echo '</select>';
    }

    public function echo_product_selector( $preselect_product_id = 0 ) {
        echo '<select name="product_id" id="product_id">';
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
            $selected = ( $loop_product_id == $preselect_product_id ) ? 'selected' : '';
            echo '<option value="' . esc_attr( $loop_product_id ) . '" ' . $selected . '>';
            echo esc_html( '#' . $loop_product_id . ' - ' . $loop_product->get_title() );
            echo '</option>';
        }
        echo '</select>';
    }

    public function echo_create_form() {
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

        echo '<div class="wrap">';
        echo '<h1 class="wp-heading-inline">';
        esc_html_e( 'Add Membership', 'pandamusrex-memberships' );
        echo '<p>';
        esc_html_e( 'Manually enter a membership for a user.', 'pandamusrex-memberships' );
        echo '</p>';
        echo '</h1>';
        echo '<hr class="wp-header-end">';

        echo '<form method="post">';
        echo '<input type="hidden" name="action" id="action" value="do_create" />';
        $nonce =  wp_create_nonce( 'membership-0' );
        echo '<input type="hidden" name="membership_nonce" id="membership_nonce" value="' . esc_attr( $nonce ) . '" />';
        echo '<input name="id" type="hidden" id="id" value="0">';

        echo '<table class="form-table">';
        echo '<tbody>';

        echo '<tr class="form-field">';
        echo '<th>';
        echo '<label for="">';
        esc_html_e( 'User', 'pandamusrex-memberships' );
        echo '</label>';
        echo '</th>';
        echo '<td>';
        $this->echo_user_selector();
        echo '</td>';
        echo '</tr>';

        echo '<tr class="form-field">';
        echo '<th>';
        echo '<label for="">';
        esc_html_e( 'Product', 'pandamusrex-memberships' );
        echo '</label>';
        echo '</th>';
        echo '<td>';
        $this->echo_product_selector();
        echo '</td>';
        echo '</tr>';

        echo '<tr class="form-field">';
        echo '<th>';
        echo '<label for="">';
        esc_html_e( 'Order', 'pandamusrex-memberships' );
        echo '</label>';
        echo '</th>';
        echo '<td>';
        $this->echo_order_selector();
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

        echo '<p class="submit">';
        echo '<input type="submit" name="create_membership" id="create_membership" class="button button-primary" value="' .
            esc_attr__( 'Create Membership', 'pandamusrex-memberships' ) .
            '" />';
        echo '</p>';

        echo '</form>';

        echo '</div>';
    }

    public function echo_edit_form() {
        if ( ! isset( $_GET['membership_id'] ) ) {
            wp_admin_notice(
                __( 'Invalid GET request - edit action requires membership_id', 'pandamusrex-memberships' )
                [ 'error' ]
            );
            return;
        }

        $id = sanitize_text_field( $_GET['membership_id'] );
        
        if ( intval( $id ) == 0 ) {
            wp_admin_notice(
                __( 'Invalid GET request - edit action - non-numeric membership_id', 'pandamusrex-memberships' )
                [ 'error' ]
            );
            return;
        }

        $id = intval( $id );
        $membership = PandamusRex_Memberships_Db::getMembershipByID( $id );
        if ( empty( $membership ) ) {
            wp_admin_notice(
                __( 'Invalid GET request - edit action - unknown membership_id', 'pandamusrex-memberships' )
                [ 'error' ]
            );
            return;
        }

        $user_id = $membership['user_id'];
        $product_id = $membership['product_id'];
        $order_id = $membership['order_id'];
        $membership_starts = $membership['membership_starts'];
        $membership_ends = $membership['membership_ends'];
        $note = $membership['note'];

        echo '<div class="wrap">';
        echo '<h1 class="wp-heading-inline">';
        esc_html_e( 'Edit Membership', 'pandamusrex-memberships' );
        echo '<p>';
        esc_html_e( 'Edit an existing membership for a user.', 'pandamusrex-memberships' );
        echo '</p>';
        echo '</h1>';
        echo '<hr class="wp-header-end">';

        echo '<form method="post">';
        echo '<input type="hidden" name="action" id="action" value="do_update" />';
        $nonce =  wp_create_nonce( 'membership-' . $id );
        echo '<input type="hidden" name="membership_nonce" id="membership_nonce" value="' . esc_attr( $nonce ) . '" />';
        echo '<input name="id" type="hidden" id="id" value="' . esc_attr( $id ) . '">';

        echo '<table class="form-table">';
        echo '<tbody>';

        echo '<tr class="form-field">';
        echo '<th>';
        echo '<label for="">';
        esc_html_e( 'User', 'pandamusrex-memberships' );
        echo '</label>';
        echo '</th>';
        echo '<td>';
        $this->echo_user_selector( $user_id );
        echo '</td>';
        echo '</tr>';

        echo '<tr class="form-field">';
        echo '<th>';
        echo '<label for="">';
        esc_html_e( 'Product', 'pandamusrex-memberships' );
        echo '</label>';
        echo '</th>';
        echo '<td>';
        $this->echo_product_selector( $product_id );
        echo '</td>';
        echo '</tr>';

        echo '<tr class="form-field">';
        echo '<th>';
        echo '<label for="">';
        esc_html_e( 'Order', 'pandamusrex-memberships' );
        echo '</label>';
        echo '</th>';
        echo '<td>';
        $this->echo_order_selector( $order_id );
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

        echo '<p class="submit">';
        echo '<input type="submit" name="update_membership" id="update_membership" class="button button-primary" value="' .
            esc_attr__( 'Update Membership', 'pandamusrex-memberships' ) .
            '" />';
        echo '</p>';

        echo '</form>';

        echo '</div>';

    }

    public function echo_delete_confirmation_form() {
        if ( ! isset( $_GET['membership_id'] ) ) {
            wp_admin_notice(
                __( 'Invalid POST request - do_delete action requires membership_id in query string', 'pandamusrex-memberships' )
                [ 'error' ]
            );
            return;
        }

        $id = sanitize_text_field( $_GET['membership_id'] );
        if ( intval( $id ) == 0 ) {
            wp_admin_notice(
                __( 'Invalid POST request - do_delete action - non-numeric membership_id in query string', 'pandamusrex-memberships' )
                [ 'error' ]
            );
            return;
        }

        $id = intval( $id );
        $membership = PandamusRex_Memberships_Db::getMembershipByID( $id );
        if ( empty( $membership ) ) {
            wp_admin_notice(
                __( 'Invalid POST request - do_delete action - unknown membership_id in query string', 'pandamusrex-memberships' )
                [ 'error' ]
            );
            return;
        }

        // Grab some data from the membership
        $user_id = $membership['user_id'];
        $user = get_user_by( 'id', $user_id );
        $membership_starts = $membership['membership_starts'];
        $membership_ends = $membership['membership_ends'];

        echo '<div class="wrap">';
        echo '<h1 class="wp-heading-inline">';
        esc_html_e( 'Delete Membership', 'pandamusrex-memberships' );
        echo '<p>';
        esc_html_e( 'Permanently delete an existing membership for a user.', 'pandamusrex-memberships' );
        echo '</p>';
        echo '</h1>';
        echo '<hr class="wp-header-end">';

        echo '<form method="post">';
        echo '<input type="hidden" name="action" id="action" value="do_delete" />';
        $nonce =  wp_create_nonce( 'membership-' . $id );
        echo '<input type="hidden" name="membership_nonce" id="membership_nonce" value="' . esc_attr( $nonce ) . '" />';
        echo '<input name="id" type="hidden" id="id" value="' . esc_attr( $id ) . '">';

        echo '<table class="form-table">';
        echo '<tbody>';

        echo '<tr class="form-field">';
        echo '<th>';
        echo '<label for="">';
        esc_html_e( 'ID', 'pandamusrex-memberships' );
        echo '</label>';
        echo '</th>';
        echo '<td>';
        echo esc_html( $id );
        echo '</td>';
        echo '</tr>';

        echo '<tr class="form-field">';
        echo '<th>';
        echo '<label for="">';
        esc_html_e( 'User', 'pandamusrex-memberships' );
        echo '</label>';
        echo '</th>';
        echo '<td>';
        $this->echo_user( $user );
        echo '</td>';
        echo '</tr>';

        echo '<tr class="form-field">';
        echo '<th>';
        echo '<label for="">';
        esc_html_e( 'Membership Starts/Started', 'pandamusrex-memberships' );
        echo '</label>';
        echo '</th>';
        echo '<td>';
        echo esc_html( $membership_starts );
        echo '</td>';
        echo '</tr>';

        echo '<tr class="form-field">';
        echo '<th>';
        echo '<label for="">';
        esc_html_e( 'Membership Ends/Ended', 'pandamusrex-memberships' );
        echo '</label>';
        echo '</th>';
        echo '<td>';
        echo esc_html( $membership_ends );
        echo '</td>';
        echo '</tr>';

        echo '</tbody>';
        echo '</table>';

        echo '<p class="submit">';
        echo '<input type="submit" name="delete_membership" id="delete_membership" class="button button-primary" value="' .
            esc_attr__( 'Permanently Delete Membership', 'pandamusrex-memberships' ) .
            '" />';
        echo '</p>';

        echo '</form>';

        echo '</div>';
    }

    public function echo_do_create() {
        // Check membership_nonce in POST (membership-0)
        if ( ! wp_verify_nonce( $_POST['membership_nonce'], 'membership-0' ) ) {
            wp_admin_notice(
                __( 'Invalid POST request - do_create action - bad nonce in POST data', 'pandamusrex-memberships' )
                [ 'error' ]
            );
            return;
        }

        // Check for required fields
        $required_fields = [ 'user_id', 'product_id', 'order_id', 'membership_starts', 'membership_ends', 'note' ];
        foreach ( $required_fields as $required_field ) {
            if (! isset( $_POST[$required_field] ) ) {
                wp_admin_notice(
                    __( 'Invalid POST request - do_create action - incomplete POST data', 'pandamusrex-memberships' )
                    [ 'error' ]
                );
                return;
            }
        }

        // Get the data
        $user_id = intval( sanitize_text_field( $_POST['user_id'] ) );
        $product_id = intval( sanitize_text_field( $_POST['product_id'] ) );
        $order_id = intval( sanitize_text_field( $_POST['order_id'] ) );
        $membership_starts = sanitize_text_field( $_POST['membership_starts'] );
        $membership_ends = sanitize_text_field( $_POST['membership_ends'] );
        $note = sanitize_text_field( $_POST['note'] );

        // Create it
        $result = PandamusRex_Memberships_Db::addMembershipForUser(
            $user_id,
            $product_id,
            $order_id,
            $membership_starts,
            $membership_ends,
            $note
        );

        if ( is_wp_error( $result ) ) {
            wp_admin_notice(
                $result->get_error_message(),
                [ 'error' ]
            );
            return;
        }

        wp_admin_notice(
            __( 'Successfully added membership', 'pandamusrex-memberships' ),
            [ 'success' ]
        );
    
        // Wrap up by echoing the complete list
        $this->echo_memberships_page();
    }

    public function echo_do_delete() {
        // Get id from POST
        if ( ! isset( $_POST['id'] ) ) {
            wp_admin_notice(
                __( 'Invalid POST request - do_delete action - no id in POST data', 'pandamusrex-memberships' )
                [ 'error' ]
            );
            return;
        }

        $id = sanitize_text_field( $_POST['id'] );

        // Check membership_nonce in POST (membership-$id)
        if ( ! wp_verify_nonce( $_POST['membership_nonce'], 'membership-' . $id ) ) {
            wp_admin_notice(
                __( 'Invalid POST request - do_delete action - bad nonce in POST data', 'pandamusrex-memberships' )
                [ 'error' ]
            );
            return;
        }

        // Do the delete
        $result = PandamusRex_Memberships_Db::deleteMembership( $id );

        if ( is_wp_error( $result ) ) {
            wp_admin_notice(
                $result->get_error_message(),
                [ 'error' ]
            );
            return;
        }

        wp_admin_notice(
            __( 'Successfully deleted membership', 'pandamusrex-memberships' ),
            [ 'success' ]
        );
    
        // Wrap up by echoing the complete list
        $this->echo_memberships_page();
    }

    public function echo_do_update() {
        // Get id from POST
        if ( ! isset( $_POST['id'] ) ) {
            wp_admin_notice(
                __( 'Invalid POST request - do_update action - no id in POST data', 'pandamusrex-memberships' )
                [ 'error' ]
            );
            return;
        }

        $id = sanitize_text_field( $_POST['id'] );

        // Check membership_nonce in POST (membership-$id)
        if ( ! wp_verify_nonce( $_POST['membership_nonce'], 'membership-' . $id ) ) {
            wp_admin_notice(
                __( 'Invalid POST request - do_update action - bad nonce in POST data', 'pandamusrex-memberships' )
                [ 'error' ]
            );
            return;
        }

        $required_fields = [ 'user_id', 'product_id', 'order_id', 'membership_starts', 'membership_ends', 'note' ];
        foreach ( $required_fields as $required_field ) {
            if (! isset( $_POST[$required_field] ) ) {
                wp_admin_notice(
                    __( 'Invalid POST request - do_update action - incomplete POST data', 'pandamusrex-memberships' )
                    [ 'error' ]
                );
                return;
            }
        }

        // Get the data
        $user_id = intval( sanitize_text_field( $_POST['user_id'] ) );
        $product_id = intval( sanitize_text_field( $_POST['product_id'] ) );
        $order_id = intval( sanitize_text_field( $_POST['order_id'] ) );
        $membership_starts = sanitize_text_field( $_POST['membership_starts'] );
        $membership_ends = sanitize_text_field( $_POST['membership_ends'] );
        $note = sanitize_text_field( $_POST['note'] );

        // Save it
        $result = PandamusRex_Memberships_Db::updateMembershipForUser(
            $id,
            $user_id,
            $product_id,
            $order_id,
            $membership_starts,
            $membership_ends,
            $note
        );

        if ( is_wp_error( $result ) ) {
            wp_admin_notice(
                $result->get_error_message(),
                [ 'error' ]
            );
            return;
        }

        wp_admin_notice(
            __( 'Successfully updated membership', 'pandamusrex-memberships' ),
            [ 'success' ]
        );

        // Wrap up by echoing the complete list
        $this->echo_memberships_page();
    }
}

PandamusRex_Memberships_Admin::get_instance();