<?php

// Exit if accessed directly
if (!defined('ABSPATH')) exit;


// ================DB REQUESTS================ 


function ccpfa_get_fonction($fonction_id){
    global $wpdb;
    $table_fonctions = $wpdb->prefix . CCPFA_TABLE_FONCTIONS;
    $table_filieres  = $wpdb->prefix . CCPFA_TABLE_FILIERES;

    $sql = $wpdb->prepare("
        SELECT f.*, fi.nom AS filiere_nom, fi.slug AS filiere_slug
        FROM $table_fonctions f
        LEFT JOIN $table_filieres fi ON fi.id = f.filiere_id
        WHERE f.id = %s
    ", $fonction_id);

    $results = $wpdb->get_results($sql);
    return $results;    
}

function ccpfa_get_all_fonctions(){
    global $wpdb;
    $table_fonctions = $wpdb->prefix . CCPFA_TABLE_FONCTIONS;
    $table_filieres  = $wpdb->prefix . CCPFA_TABLE_FILIERES;
    $sql = $wpdb->prepare("
        SELECT f.*, fi.nom AS filiere_nom, fi.slug AS filiere_slug
        FROM $table_fonctions f
        LEFT JOIN $table_filieres fi ON fi.id = f.filiere_id
    ");

    $results = $wpdb->get_results($sql);
    return $results;
}
function ccpfa_get_all_fonction_items(){
    global $wpdb;
    $table_fonctions = $wpdb->prefix . CCPFA_TABLE_FONCTIONS;
    return $wpdb->get_results("SELECT fonction,id FROM $table_fonctions ORDER BY id DESC");
}

/**
 * Get a filière object by its slug
 *
 * @param string $slug
 * @return object|null
 */
function ccpfa_get_filiere_by_slug($slug) {
    global $wpdb;
    $table = $wpdb->prefix . CCPFA_TABLE_FILIERES;

    return $wpdb->get_row(
        $wpdb->prepare("SELECT * FROM $table WHERE slug = %s", $slug)
    );
}
/**
 * Get a filière object by its id
 *
 * @param int $filiere_id
 * @return object|null
 */
function ccpfa_get_filiere($filiere_id) {
    global $wpdb;
    $table = $wpdb->prefix . CCPFA_TABLE_FILIERES;

    return $wpdb->get_row(
        $wpdb->prepare("SELECT * FROM $table WHERE id = %s", $filiere_id)
    );
}

/**
 * Get all fonctions linked to a filière ID
 *
 * @param int $filiere_id
 * @return array
 */
function ccpfa_get_fonctions_by_filiere($filiere_id) {
    global $wpdb;
    $fonctions_table = $wpdb->prefix . CCPFA_TABLE_FONCTIONS;
    $filieres_table  = $wpdb->prefix . CCPFA_TABLE_FILIERES;

    return $wpdb->get_results(
        $wpdb->prepare("
            SELECT f.*, fi.nom AS filiere_nom, fi.slug AS filiere_slug
            FROM $fonctions_table f
            INNER JOIN $filieres_table fi ON fi.id = f.filiere_id
            WHERE f.filiere_id = %d
            ORDER BY salaire_brut_mensuel DESC
        ", $filiere_id)
    );
}

function get_average_monthly_salary($fonctions){
    $sum = 0;
    $divide_by = 1;
    foreach($fonctions as $f){
        if(empty($f->salaire_brut_mensuel)){
            continue;
        }
        $divide_by+=1;
        $sum+=$f->salaire_brut_mensuel;
    }
    return round($sum/$divide_by);
    
}

function convert_to_net($_brut){
    return round($_brut*0.821);
}

function get_average_daily_salary($fonctions){
    $sum = 0;
    $divide_by = 1;
    foreach($fonctions as $f){
        if(empty($f->salaire_brut_journalier)){
            continue;
        }
        $divide_by+=1;
        $sum+=$f->salaire_brut_journalier;
    }
    
    return round($sum/$divide_by);
}


// ================ASSETS================ 

function ccpfa_enqueue_frontend_assets() {
    global $post;
    wp_enqueue_style(
        'ccpfa-filiere-css', // handle
        CCPFA_MODULES_URL . '/fonctiosn/fonction_results_view.css', // URL to the CSS file
        [], // dependencies (none here)
        '1.0.0' // version
    );
    if (is_a($post, 'WP_Post') && has_shortcode($post->post_content, 'ccpfa_fonction_table')) {
        wp_enqueue_style('bootstrap-css', 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css');
        wp_enqueue_style('datatables-css', 'https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css');
        wp_enqueue_script('datatables-js', 'https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js', array('jquery'), null, true);
        wp_enqueue_script('datatables-bootstrap-js', 'https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js', array('datatables-js'), null, true);
        
    }    
    if (is_a($post, 'WP_Post') && has_shortcode($post->post_content, 'ccpfa_fonction_search')) {
        wp_enqueue_style('bootstrap-css', 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css');
        wp_enqueue_script('bootstrap-js', 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js', [], null, true);

    }
    // --- For filière short code ---
    if (is_a($post, 'WP_Post') && has_shortcode($post->post_content, 'ccpfa_filiere_search')) {
        wp_enqueue_style('bootstrap-css', 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css');
        wp_enqueue_script('bootstrap-js', 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js', [], null, true);

    }
}
add_action('wp_enqueue_scripts', 'ccpfa_enqueue_frontend_assets');





// ================FILIERES================ 

function ccpfa_add_filiere_rewrite_rules() {
    add_rewrite_rule(
        '^filiere/([^/]*)/?',
        'index.php?pagename=filiere&ccpfa_filiere=$matches[1]',
        'top'
    );
    // Register query var
    add_rewrite_tag('%ccpfa_filiere%', '([^&]+)');
}
add_action('init', 'ccpfa_add_filiere_rewrite_rules');

function ccpfa_add_filiere_query_vars($vars) {
    $vars[] = 'ccpfa_filiere';
    return $vars;
}
add_filter('query_vars', 'ccpfa_add_filiere_query_vars');

function ccpfa_render_filiere_href($row,$classes="") {
    // Defensive checks
    if (empty($row->filiere_slug) || empty($row->filiere_nom)) {
        return '-'; // Always return a placeholder to avoid empty <td>
    }

    // Build clean permalink to the filière page using home_url()
    $base = trailingslashit(home_url('filiere')); // ensures trailing slash
    $url  = $base . urlencode($row->filiere_slug) . '/';

    // Return a clean, escaped HTML link
    return sprintf(
        '<a href="%s" class="'.$classes.'">%s</a>',
        esc_url($url),
        esc_html($row->filiere_nom)
    );
}

function ccpfa_filiere_search_shortcode($atts) {

    global $wpdb;
    $table_name = $wpdb->prefix . CCPFA_TABLE_FILIERES;

    // Get all filières
    $filieres = $wpdb->get_results("SELECT * FROM $table_name ORDER BY nom ASC");

    // Try to get selected filière from query var or from $_GET
    $selected_slug = get_query_var('ccpfa_filiere');

    echo "<!---$selected_slug ---->";

    if (empty($selected_slug) && isset($_GET['ccpfa_filiere'])) {
        $selected_slug = sanitize_text_field($_GET['ccpfa_filiere']);
    }

    // Find matching filière
    $selected_filiere = null;
    if ($selected_slug) {
        foreach ($filieres as $f) {
            if ($f->slug === $selected_slug) {
                $selected_filiere = $f;
                break;
            }
        }
    }
    wp_enqueue_script('filiere_search_js', CCPFA_MODULES_URL . '/fonctions/filiere_search_view.js', array('jquery'), null, true);
    wp_localize_script('filiere_search_js', 'ccpfa_filiere_Ajax', array(
        'ajax_url' => admin_url('admin-ajax.php'),
    ));

    ob_start(); 
    include CCPFA_MODULES_FOLDER .'/fonctions/filiere_search_view.php';
    return ob_get_clean();
}
add_shortcode('ccpfa_filiere_search', 'ccpfa_filiere_search_shortcode');

add_action('wp_ajax_search_filiere_fonctions', 'ccpfa_search_filiere_fonctions_callback');
add_action('wp_ajax_nopriv_search_filiere_fonctions', 'ccpfa_search_filiere_fonctions_callback');
function ccpfa_search_filiere_fonctions_callback() {

    $filiere_id = intval($_POST['filiere_id'] ?? 0);
    if (empty($filiere_id)) {
        wp_send_json_error('Veuillez saisir une filière.');
    }

    $results = ccpfa_get_fonctions_by_filiere($filiere_id);
    if (!$results) {
        wp_send_json_error('Aucune fonction trouvée.');
    }



    ob_start();
    include CCPFA_MODULES_FOLDER . '/fonctions/fonctions_results_view.php';
    $html = ob_get_clean();
    wp_send_json_success($html);
}




// ================FONCTIONS================

// === [ccpfa_fonction_table] ===
add_shortcode('ccpfa_fonction_table', 'ccpfa_fonctions_table_shortcode');
function ccpfa_fonctions_table_shortcode() {
    // ===Fonctions List ===
    global $wpdb;
    $table_name = $wpdb->prefix . CCPFA_TABLE_FONCTIONS;
    $results = ccpfa_get_all_fonctions();

    wp_enqueue_script('fonctions_table_js',CCPFA_MODULES_URL.'/fonctions/fonctions_table_view.js', array('jquery', 'datatables-js'), null, true);

    ob_start();
    include CCPFA_MODULES_FOLDER .'/fonctions/fonctions_table_view.php';
    return ob_get_clean();
}


// === [ccpfa_fonction_search] ===
add_shortcode('ccpfa_fonction_search', 'ccpfa_fonction_search_shortcode');
function ccpfa_fonction_search_shortcode() {
    // ===Fonctions List ===
    $fonction_options = ccpfa_get_all_fonction_items();
    wp_enqueue_script('fonction_search_js',CCPFA_MODULES_URL.'/fonctions/fonctions_search_view.js', ['jquery'], null, true);
    wp_localize_script('fonction_search_js', 'ccpfa_fonction_Ajax', array(
        'ajax_url' => admin_url('admin-ajax.php'),
    ));
    
    ob_start();
    include CCPFA_MODULES_FOLDER .'/fonctions/fonctions_search_view.php';
    return ob_get_clean();
}



// Register AJAX handler for logged-in and logged-out users
add_action('wp_ajax_fonction_search', 'ccpfa_fonction_search_callback');
add_action('wp_ajax_nopriv_fonction_search', 'ccpfa_fonction_search_callback');
function ccpfa_fonction_search_callback() {

    $fonction_id = sanitize_text_field($_POST['fonction_id'] ?? '');
    if (empty($fonction_id)) {
        wp_send_json_error('Veuillez saisir une fonction.');
    }

    $results = ccpfa_get_fonction($fonction_id);
    if (!$results) {
        wp_send_json_error('Aucune fonction trouvée.');
    }

    ob_start();
    include CCPFA_MODULES_FOLDER . '/fonctions/fonctions_results_view.php';
    $html = ob_get_clean();

    wp_send_json_success($html);
}


function ccpfa_add_query_fonction_vars($vars) {
    $vars[] = 'ccpfa_fonction';
    return $vars;
}
add_filter('query_vars', 'ccpfa_add_query_fonction_vars');
function ccpfa_add_fonction_rewrite_rules() {
    add_rewrite_rule(
        '^fonction/([^/]+)/?$',
        'index.php?ccpfa_filiere=$matches[1]',
        'top'
    );
}
add_action('init', 'ccpfa_add_fonction_rewrite_rules');

function ccpfa_render_fonction_href($obj):string{
    if (!empty($obj->filiere_nom)) {
        $filiere_url = home_url('/filiere/' . $obj->filiere_slug . '/'); 
        ob_start();
        ?>
            <a href="<?= esc_url($filiere_url); ?>" target="_blank">
                <?= esc_html($obj->filiere_nom); ?>
            </a>
        <?php
        $html = ob_get_clean();
        return $html;
    }else{
        ob_start();
        ?>
        -
        <?php
        $html = ob_get_clean();
        return $html;
    }

}

