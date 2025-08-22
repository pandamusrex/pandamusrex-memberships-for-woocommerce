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

    // Database stores as YYYY-MM-DD
    // We want the GUI to show MM/DD/YYYY
    protected static function convertYYYYMMDDToMMDDYYYY( $yyyy_mm_dd ) {
        if ( strlen( $yyyy_mm_dd ) < 10 ) {
            return $yyyy_mm_dd;
        }

        // 0123456789
        // YYYY-MM-DD
        $mm = substr( $yyyy_mm_dd, 5, 2 );
        $dd = substr( $yyyy_mm_dd, 8, 2 );
        $yy = substr( $yyyy_mm_dd, 0, 4 );

        return $mm . '/' . $dd . '/' . $yy;
    }

    public static function getAllMembershipsByUser( $user_id ) {
        global $wpdb;

        $sql = 'SELECT * FROM %i WHERE user_id = %s ORDER BY membership_ends DESC';
        $vars = [ self::getTableName(), $user_id ];
        $results = $wpdb->get_results( $wpdb->prepare( $sql, $vars ), ARRAY_A );

        // Keep just the date for start, end
        // DB has YYYY-MM-DD HH:MM:SS.ffffff, so convert it to MM/DD/YYYY
        foreach ( $results as &$result ) {
            if ( array_key_exists( 'membership_starts', $result ) ) {
                $yyyy_mm_dd = $result[ 'membership_starts' ];
                $result[ 'membership_starts' ] = self::convertYYYYMMDDToMMDDYYYY( $yyyy_mm_dd );
            }
            if ( array_key_exists( 'membership_ends', $result ) ) {
                $yyyy_mm_dd = $result[ 'membership_ends' ];
                $result[ 'membership_ends' ] = self::convertYYYYMMDDToMMDDYYYY( $yyyy_mm_dd );
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

        $data[ 'id' ] = $membership_id;

        return $data;
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
