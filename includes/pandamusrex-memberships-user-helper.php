<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class PandamusRex_Memberships_User_Helper {
    public function find_or_create_user( $email ) {
        $userdata = WP_User::get_data_by( 'email', $email );
        if ( $userdata ) {
            $user = new WP_User();
            $user->init( $userdata );
            return $user->ID;
        }

        list( $username, $domain ) = explode( '@', $email );
        $username = sanitize_user( $username );
        $password = wp_generate_password( $length = 12, $include_standard_special_chars = false );

        $user_id = wp_create_user( $username, $password, $email );

        if ( is_wp_error( $user_id ) ) {
            if ( function_exists( 'wc_get_logger' ) ) {
                wc_get_logger()->debug( "Unable to create user for email: $email" );
            } else {
                error_log( "Unable to create user for email: $email" );
            }
            return -1;
        }

        return $user_id;
    }
}