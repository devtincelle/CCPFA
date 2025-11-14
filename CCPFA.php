<?php
/*
Plugin Name: CCPFA
Description: Search and manage informations from the "Convention Collective du Cinéma d’Animation".
Version: 2.0
Author: Collectif Les Etincelles 
*/

define("CCPFA_TABLE_FONCTIONS","ccpfa_fonctions");
define("CCPFA_TABLE_ARTICLES","ccpfa_articles");
define("CCPFA_TABLE_FILIERES","ccpfa_filieres");
define("CCPFA_FOLDER",plugin_dir_path( __FILE__ ));
define("CCPFA_MODULES_FOLDER",plugin_dir_path( __FILE__ )."/modules");
define("CCPFA_MODULES_URL",plugin_dir_url(__FILE__)."/modules");
define("CCPFA_DOCS_URL",plugin_dir_path(__FILE__)."data/docs");

// database
require_once(CCPFA_MODULES_FOLDER."/database/database.php");
function ccpfa_activate() {

    ccpfa_create_tables();

    // Safety check
    global $wpdb;
    $fonctions_table = $wpdb->prefix . CCPFA_TABLE_FONCTIONS;
    $fonc_exists = $wpdb->get_var($wpdb->prepare("SHOW TABLES LIKE %s", $fonctions_table));    
    $filiere_table = $wpdb->prefix . CCPFA_TABLE_FILIERES;
    $fil_exists = $wpdb->get_var($wpdb->prepare("SHOW TABLES LIKE %s", $filiere_table));

    if ($fonc_exists === $fonctions_table && $fil_exists === $filiere_table) {
        // Now it’s safe to import
        ccpfa_upload_local_json();
    } else {
        error_log("CCPFA activation: table $fonctions_table or $filiere_table still not found after creation.");
    }
    flush_rewrite_rules(); // Important
}

// In your plugin main file or init hook
add_action('init', function() {
    add_rewrite_rule(
        '^filiere/([^/]*)/?',
        'index.php?pagename=filiere&filiere=$matches[1]',
        'top'
    );
});

// Allow 'filiere' as a recognized query variable
add_filter('query_vars', function($vars) {
    $vars[] = 'filiere';
    return $vars;
});

register_activation_hook(__FILE__, 'ccpfa_activate');

// pdf
require_once(CCPFA_MODULES_FOLDER."/convention_pdf/convention_pdf.php");

require_once(CCPFA_MODULES_FOLDER."/fonctions/fonctions_front.php");

// fonctions admin
require_once(CCPFA_MODULES_FOLDER."/fonctions/fonctions_admin.php");


function ccpfa_create_filiere_page_on_activation() {
    // Make sure rewrite rules are ready
    ccpfa_add_filiere_rewrite_rules();
    flush_rewrite_rules();
    
    // Check if the page already exists
    $page = get_page_by_path('filiere');
    
    if (!$page) {
        // Create the page if missing
        $page_data = array(
            'post_title'    => 'Filière',
            'post_name'     => 'filiere',
            'post_content'  => '[ccpfa_filiere_search]',
            'post_status'   => 'publish',
            'post_type'     => 'page',
        );
        wp_insert_post($page_data);
    }
}
register_activation_hook(__FILE__, 'ccpfa_create_filiere_page_on_activation');

// fonctions


// fun
//require_once(CCPFA_MODULES_FOLDER."/fun_title/fun_title.php");


