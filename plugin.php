<?php
/**
 * Plugin Name: ACF Duplicate Cleaner
 * Plugin URI:
 * Description: This plugin cleans all duplicate fields from the WordPress database after ACF imports.
 * Version: 1
 * Author: Juhani Hakanen / Geniem Oy
 * Author URI:
 **/

namespace Geniem\ACFDuplicateCleaner;

initialize();

/**
 * Initializes WordPress hooks.
 */
function initialize() {
    add_action( 'admin_menu', __NAMESPACE__ . '\acf_duplicate_cleaner_page' );

    if ( $_GET['page'] == 'acf_duplicate_cleaner' && isset( $_GET['execute'] ) ) {
        acf_duplicate_cleaner_execute();
    }
}

/**
 * Tools submenu page.
 */
function acf_duplicate_cleaner_page() {
    add_submenu_page(
        'tools.php',
        'ACF Duplicate Cleaner',
        'ACF Duplicate Cleaner',
        'manage_options',
        'acf_duplicate_cleaner',
        __NAMESPACE__ . '\acf_duplicate_cleaner_page_source'
    );
}

/**
 * Tools submenu page source.
 */
function acf_duplicate_cleaner_page_source() {
    require_once( dirname( __FILE__ ) . '/classes/acf-duplicate-cleaner.php' );

    $acf_duplicate_cleaner = acf_duplicate_cleaner();
    $acf_duplicate_cleaner->page_source();
}

/**
 * Execute the plugin.
 */
function acf_duplicate_cleaner_execute() {
    require_once( dirname( __FILE__ ) . '/classes/acf-duplicate-cleaner.php' );

    $acf_duplicate_cleaner = acf_duplicate_cleaner();
    $acf_duplicate_cleaner->execute();
}
