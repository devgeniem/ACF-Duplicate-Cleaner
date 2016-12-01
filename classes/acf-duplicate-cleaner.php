<?php
/**
 * This file contains the class ACFDuplicateCleaner, which cleans database data after ACF imports.
 */

namespace Geniem\ACFDuplicateCleaner;

/**
 * Cleans duplicate database data after ACF imports.
 */
final class ACFDuplicateCleaner {
    /**
     * Singleton object of the class.
     *
     * @var object
     */
    static $instance;

    /**
     * Prevent cloning.
     */
    private function __clone() {}

    /**
     * Prevent serialization.
     */
    private function __sleep() {}

    /**
     * Prevent unserialization.
     */
    private function __wakeup() {}

    /**
     * Prevent constructing this class.
     */
    private function __construct() {}

    /**
     * Returns singleton object of ACFDuplicateCleaner.
     *
     * @return ACFDuplicateCleaner
     */
    static function instance() {
        if ( ! isset( self::$instance ) ) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Removes all duplicate acf-field rows from the posts table.
     */
    public function execute() {
        global $wpdb;

        $results = $this->get_duplicates();

        if ( ! empty( $results ) ) {
            // List all acf-fields by their post_name.
            foreach ( $results as $key => $data ) {
                $acf_fields[ $data->post_name ][] = $data;
            }

            // Loop all acf-fields.
            foreach ( $acf_fields as $post_name => $fields ) {
                $most_recent_id = null;

                // Find out the most recent acf-field.
                foreach ( $fields as $key => $data ) {
                    if ( ! $most_recent_id || $most_recent_id < $data->ID ) {
                        $most_recent_id = $data->ID;
                    }
                }

                // Delete all but the most recent acf-fields.
                if ( ! empty( $most_recent_id ) ) {
                    $wpdb->query(
                        $wpdb->prepare("
                            DELETE 
                            FROM {$wpdb->prefix}posts 
                            WHERE post_name = %s 
                            AND ID != %d
                        ", $post_name, $most_recent_id
                        )
                    );
                }
            }
        }
    }

    /**
     * Finds all duplicate acf-fields from the posts table.
     *
     * @param  boolean $return_count    Changes return behavior.
     * @return mixed                    Depending on $count either returns array (list of acf-fields) or integer (number of acf-fields)
     */
    public function get_duplicates( $return_count = false ) {
        global $wpdb;

        $query_select = ($return_count) ? 'COUNT(*) AS count' : '*';

        // Find all duplicate rows.
        $results = $wpdb->get_results(
            $wpdb->prepare("
                SELECT $query_select
                FROM {$wpdb->prefix}posts 
                WHERE post_name IN (
                    SELECT post_name 
                    FROM {$wpdb->prefix}posts 
                    WHERE post_type=%s 
                    GROUP BY post_name 
                    HAVING COUNT(*) > 1
                )",
            'acf-field')
        );

        // If results were not found and count is requested, return 0.
        if ( $return_count ) {
            $results = $results[0]->count;
        }

        return $results;
    }

    /**
     * Prints the tools submenu page source.
     */
    public function page_source() {
        $duplicate_count = $this->get_duplicates( true );

        $html = "
            <div class='wrap'>
            <h1>ACF Duplicate Cleaner</h1>
            <p>You currently have $duplicate_count duplicate fields.</p>
        ";

        if ( $duplicate_count ) {
            $html .= "
                <p>
                    <a href='?page=acf_duplicate_cleaner&execute'>
                        <button>Click here to clean duplicates!</button>
                    </a>
                </p>
            ";
        }

        $html .= "</div>";

        echo $html;
    }
}

/**
 * Returns singleton object of ACFDuplicateCleaner.
 *
 * @return ACFDuplicateCleaner
 */
function acf_duplicate_cleaner() {
    return ACFDuplicateCleaner::instance();
}
