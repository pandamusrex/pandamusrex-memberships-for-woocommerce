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
            6
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

    public function memberships_page(){
        esc_html_e( 'Memberships Page Test', 'pandamusrex-memberships' );	
    }

    public function add_membership_page(){
        esc_html_e( 'Add Membership Page Test', 'pandamusrex-memberships' );	
    }
}


PandamusRex_Memberships_Admin::get_instance();