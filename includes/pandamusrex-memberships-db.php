<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class PandamusRex_Memberships_Db {
    public static function getTableName() {
        global $wpdb;
        return $wpdb->prefix . 'pandamusrex_mbrship_prch';
    }

    public static function create_tables() {
        global $wpdb;
        $table_name = PandamusRex_Memberships_Db::getTableName();
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE $table_name (
            id BIGINT(20) NOT NULL AUTO_INCREMENT,
            user_id BIGINT(20) NOT NULL,
            product_id BIGINT(20),
            order_id BIGINT(20),
            membership_starts DATETIME NOT NULL,
            membership_ends DATETIME NOT NULL,
            note VARCHAR(255) NOT NULL,
            PRIMARY KEY (id)
        ) $charset_collate;";

        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' ); // Include dbDelta()
        dbDelta( $sql );
    }

    public static function getAllMemberships() {
        global $wpdb;

        $sql = 'SELECT * FROM %i ORDER BY membership_ends DESC, id DESC';
        $vars = [ self::getTableName() ];
        $results = $wpdb->get_results( $wpdb->prepare( $sql, $vars ), ARRAY_A );

        // Keep just the date for start, end
        // DB has YYYY-MM-DD HH:MM:SS.ffffff, so cut off the time
        foreach ( $results as &$result ) {
            if ( array_key_exists( 'membership_starts', $result ) ) {
                $yyyy_mm_dd = $result[ 'membership_starts' ];
                $result[ 'membership_starts' ] = substr( $yyyy_mm_dd, 0, 10 );
            }
            if ( array_key_exists( 'membership_ends', $result ) ) {
                $yyyy_mm_dd = $result[ 'membership_ends' ];
                $result[ 'membership_ends' ] = substr( $yyyy_mm_dd, 0, 10 );
            }
        }
        unset( $result );

        return $results;
    }

    public static function getAllMembershipsByUser( $user_id ) {
        global $wpdb;

        $sql = 'SELECT * FROM %i WHERE user_id = %s ORDER BY membership_ends DESC';
        $vars = [ self::getTableName(), $user_id ];
        $results = $wpdb->get_results( $wpdb->prepare( $sql, $vars ), ARRAY_A );

        // Keep just the date for start, end
        // DB has YYYY-MM-DD HH:MM:SS.ffffff, so cut off the time
        foreach ( $results as &$result ) {
            if ( array_key_exists( 'membership_starts', $result ) ) {
                $yyyy_mm_dd = $result[ 'membership_starts' ];
                $result[ 'membership_starts' ] = substr( $yyyy_mm_dd, 0, 10 );
            }
            if ( array_key_exists( 'membership_ends', $result ) ) {
                $yyyy_mm_dd = $result[ 'membership_ends' ];
                $result[ 'membership_ends' ] = substr( $yyyy_mm_dd, 0, 10 );
            }
        }
        unset( $result );

        return $results;
    }

    public static function getMostRecentMembershipForUser( $user_id ) {
        global $wpdb;

        $all = self::getAllMembershipsByUser( $user_id );

        if ( is_array( $all ) and ( count( $all ) > 0 ) ) {
            return $all[0];
        }

        return [];
    }

    public static function addMembershipForUser( $user_id, $product_id, $order_id, $starts, $ends, $note ) {
        global $wpdb;

        $data = [
            'user_id' => $user_id,
            'product_id' => $product_id,
            'order_id' => $order_id,
            'membership_starts' => $starts,
            'membership_ends' => $ends,
            'note' => $note
        ];

        $wpdb->insert(
            self::getTableName(),
            $data,
            [
                '%d',
                '%d',
                '%d',
                '%s',
                '%s',
                '%s'
            ]
        );

        $data[ 'id' ] = $wpdb->insert_id;
        $data[ 'last_error' ] = $wpdb->last_error;

        return $data;
    }

    public static function addMembershipForUserThatStartsNow( $user_id, $product_id, $order_id, $note ) {
        $wp_tz = wp_timezone_string();
        $start_dt = new DateTime( "now", new DateTimeZone( $wp_tz ) );
        // Database expects YYYY-MM-DD 00:00:00
        $membership_starts = $start_dt->format( "Y-m-d 00:00:00" );
        // wc_get_logger()->debug( "Starts: $membership_starts" );

        $ends_dt = new DateTime( "now", new DateTimeZone( $wp_tz ) );
        $ends_dt->add( DateInterval::createFromDateString( '365 days' ) );
        $membership_ends = $ends_dt->format( "Y-m-d 23:59:59" );
        // wc_get_logger()->debug( "Ends: $membership_ends" );

        $result = self::addMembershipForUser(
            $user_id,
            $product_id,
            $order_id,
            $membership_starts,
            $membership_ends,
            $note
        );
    }

    public static function updateMembershipForUser( $membership_id, $user_id, $product_id, $order_id, $starts, $ends, $note ) {
        global $wpdb;

        $data = [
            'user_id' => $user_id,
            'product_id' => $product_id,
            'order_id' => $order_id,
            'membership_starts' => $starts,
            'membership_ends' => $ends,
            'note' => $note
        ];

        $wpdb->update(
            self::getTableName(),
            $data,
            [
                'id' => $membership_id
            ],
            [
                '%d',
                '%d',
                '%d',
                '%s',
                '%s',
                '%s'
            ],
            [
                '%d'
            ]
        );

        $data[ 'last_error' ] = $wpdb->last_error;

        return $data;
    }

    public static function getMembershipByID( $membership_id ) {
        global $wpdb;

        $sql = 'SELECT * FROM %i WHERE id = %d';
        $vars = [ self::getTableName(), $membership_id ];
        $results = $wpdb->get_results( $wpdb->prepare( $sql, $vars ), ARRAY_A );

        // Keep just the date for start, end
        // DB has YYYY-MM-DD HH:MM:SS.ffffff, so cut off the time
        foreach ( $results as &$result ) {
            if ( array_key_exists( 'membership_starts', $result ) ) {
                $yyyy_mm_dd = $result[ 'membership_starts' ];
                $result[ 'membership_starts' ] = substr( $yyyy_mm_dd, 0, 10 );
            }
            if ( array_key_exists( 'membership_ends', $result ) ) {
                $yyyy_mm_dd = $result[ 'membership_ends' ];
                $result[ 'membership_ends' ] = substr( $yyyy_mm_dd, 0, 10 );
            }
        }
        unset( $result );

        if ( empty( $results ) ) {
            return [];
        }

        return $results[0];
    }

    public static function deleteMembership( $membership_id ) {
        global $wpdb;

        $wpdb->delete(
            self::getTableName(),
            [
                'id' => $membership_id
            ],
            [
                '%d'
            ]
        );

        return TRUE;
    }
}
