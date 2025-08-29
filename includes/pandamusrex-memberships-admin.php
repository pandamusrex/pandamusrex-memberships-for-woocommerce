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
            __( 'Add', 'pandamusrex-memberships' ),
            __( 'Add', 'pandamusrex-memberships' ),
            'manage_options',
            'pandamusrex_add_membership_page',
            [ $this, 'add_membership_page' ]
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

        echo '<table class="wp-list-table widefat fixed striped table-view-list">';
        echo '<thead>';
        echo '<tr>';
        echo '<th scope="col" class="manage-column">ID</th>';
        echo '<th scope="col" id="membership_id" class="manage-column">User</th>';
        echo '<th scope="col" id="membership_id" class="manage-column">Product</th>';
        echo '<th scope="col" id="membership_id" class="manage-column">Order</th>';
        echo '<th scope="col" id="membership_id" class="manage-column">Started</th>';
        echo '<th scope="col" id="membership_id" class="manage-column">Ends/Ending</th>';
        echo '<th scope="col" id="membership_id" class="manage-column">Notes</th>';
        echo '</tr>';
        echo '</thead>';
        echo '</table>';

        echo '</div>';
    }

    public function add_membership_page() {
        echo '<div class="wrap">';
        echo '<h1 class="wp-heading-inline">';
        esc_html_e( 'Add Membership', 'pandamusrex-memberships' );
        echo '</h1>';
        echo '<hr class="wp-header-end">';



        echo '</div>';
    }
}

PandamusRex_Memberships_Admin::get_instance();