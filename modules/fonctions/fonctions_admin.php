<?php
// Exit if accessed directly
if (!defined('ABSPATH')) exit;

// Add the menu item
add_action('admin_menu', 'ccpfa_fonctions_add_admin_menu');
function ccpfa_fonctions_add_admin_menu() {
    add_menu_page(
        'CCPFA Settings',       // Page title
        'CCPFA_fonctions',      // Menu title
        'manage_options',       // Capability
        'ccpfa',                // Menu slug
        'ccpfa_fonctions_admin_page', // Callback function
        'dashicons-admin-generic',    // Icon
        20                      // Position
    );
}

require_once(CCPFA_MODULES_FOLDER."/fonctions/classes/CCPFA_Fonctions_List_Table.php");

function ccpfa_fonctions_admin_page() {
    global $wpdb;
    $fonctions_table = $wpdb->prefix . CCPFA_TABLE_FONCTIONS;
    $filieres_table  = $wpdb->prefix . CCPFA_TABLE_FILIERES;

    // JSON export
    if (isset($_POST['download_json']) && current_user_can('manage_options')) {
        $results = $wpdb->get_results(
            "SELECT f.*, fi.nom AS filiere_nom 
             FROM $fonctions_table f
             LEFT JOIN $filieres_table fi ON f.filiere_id = fi.id",
            ARRAY_A
        );

        $json = json_encode($results, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

        if (ob_get_length()) ob_end_clean();
        header('Content-Description: File Transfer');
        header('Content-Type: application/json');
        header('Content-Disposition: attachment; filename="database_export.json"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . strlen($json));
        echo $json;
        exit;
    }

    // Editing
    $action = $_GET['action'] ?? '';
    $id     = isset($_GET['id']) ? intval($_GET['id']) : 0;

    $current_user_name = "";
    if (is_user_logged_in()) {
        $current_user = wp_get_current_user();
        $current_user_name = esc_html($current_user->user_login);
    }

    // Handle form submission
    if (isset($_POST['ccpfa_save_job'])) {
        $data = [
            'fonction'                => sanitize_text_field($_POST['fonction']),
            'version_feminisee'       => sanitize_text_field($_POST['version_feminisee']),
            '_category'               => sanitize_text_field($_POST['_category']),
            '_definition'             => sanitize_textarea_field($_POST['_definition']),
            'salaire_brut_mensuel'    => floatval($_POST['salaire_brut_mensuel']),
            'salaire_brut_journalier' => floatval($_POST['salaire_brut_journalier']),
            'external_id'             => sanitize_text_field($_POST['external_id']),
            'document_version'        => sanitize_text_field("Edited_by_" . $current_user_name),
            'filiere_id'              => intval($_POST['filiere_id']), // new relation
        ];

        if ($id > 0) {
            $wpdb->update($fonctions_table, $data, ['id' => $id]);
            echo '<div class="updated"><p>Job updated successfully!</p></div>';
        }
        $action = '';
    }

    echo '<div class="wrap">';

    // Edit form
    if ($action === 'edit' && $id > 0) {
        $job = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT f.*, fi.nom AS filiere_nom
                 FROM $fonctions_table f
                 LEFT JOIN $filieres_table fi ON f.filiere_id = fi.id
                 WHERE f.id = %d",
                $id
            ),
            ARRAY_A
        );

        if (!$job) {
            echo '<p>Job not found.</p>';
        } else {
            echo '<h1>Edit Job: ' . esc_html($job['fonction']) . '</h1>';
            include CCPFA_MODULES_FOLDER . '/fonctions/fonctions_admin_edit_form.php';
        }
    }

    // List table view
    else {
        echo '<h1>CCPFA Jobs</h1>';
        ?>
        <form method="post">
            <button type="submit" name="download_json" class="button button-primary">Download DB as JSON</button>
        </form>
        <form method="post" enctype="multipart/form-data">
            <input type="file" name="json_file" accept=".json" required>
            <input type="hidden" name="action" value="ccpfa_import_user_json_file">
            <button type="submit" class="button button-primary">Upload JSON</button>
        </form>
        <?php
        $list_table = new CCPFA_Fonctions_List_Table();
        $list_table->prepare_items();
        echo '<form method="get">';
        echo '<input type="hidden" name="page" value="ccpfa" />';
        $list_table->search_box('Search Jobs', 'ccpfa_search');
        $list_table->display();
        echo '</form>';
    }

    echo '</div>';
}
