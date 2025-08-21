<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class PandamusRex_Memberships_Db {
    protected static function getTableName() {
        $table_name = $wpdb->prefix . 'pandamusrex_mbrship_prch';
    }

    public static function getAllMembershipsByUser( $user_id ) {
        global $wpdb;

        $sql = 'SELECT * FROM %s WHERE user_id = %s ORDER BY membership_ends DESC';
        $vrs = [ self::getTableName(), $user_id ];
        return $wpdb->get_results( $wpdb->prepare( $sql, $vars ), ARRAY_A );
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
            'notes' => $notes
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
            'notes' => $notes
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

function pandamusrex_memberships_create_tables() {
    error_log( 'in pandamusrex_memberships_create_tables' );
    global $wpdb;
    $table_name = PandamusRex_Memberships_Db::getTableName();
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table_name (
        id bigint(20) NOT NULL AUTO_INCREMENT,
        user_id bigint(20) NOT NULL,
        product_id bigint(20),
        order_id bigint(20),
        membership_starts datetime NOT NULL,
        membership_ends datetime NOT NULL,
        note varchar(255) NOT NULL,
        PRIMARY KEY (id)
    ) $charset_collate;";

    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' ); // Include dbDelta()
    dbDelta( $sql );
}

register_activation_hook( __FILE__, 'pandamusrex_memberships_create_tables' );