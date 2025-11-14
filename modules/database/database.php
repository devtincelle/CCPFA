<?php
function ccpfa_create_tables() {
    global $wpdb;
    $charset_collate = $wpdb->get_charset_collate();

    $filieres_table = $wpdb->prefix . CCPFA_TABLE_FILIERES;
    $fonctions_table = $wpdb->prefix . CCPFA_TABLE_FONCTIONS;

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

    // Drop tables first (if needed)
    $wpdb->query("DROP TABLE IF EXISTS $fonctions_table");
    $wpdb->query("DROP TABLE IF EXISTS $filieres_table");

    // Filières table
    $sql_filieres = "CREATE TABLE $filieres_table (
        id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        slug VARCHAR(191) NOT NULL UNIQUE,
        nom VARCHAR(255) NOT NULL,
        description TEXT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY  (id)
    ) $charset_collate";
    dbDelta($sql_filieres);

    // Fonctions table
    $sql_fonctions = "CREATE TABLE $fonctions_table (
        id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        fonction VARCHAR(255) NOT NULL,
        version_feminisee VARCHAR(255),
        slug VARCHAR(255),
        _category VARCHAR(255),
        _definition TEXT,
        filiere VARCHAR(255),
        filiere_id BIGINT(20) UNSIGNED,
        page_number INT(11),
        document_version VARCHAR(255),
        salaire_brut_mensuel DECIMAL(10,2),
        salaire_brut_journalier DECIMAL(10,2),
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY  (id),
        INDEX (filiere_id)
    ) $charset_collate";

    // Run dbDelta
    dbDelta($sql_fonctions);
    // Ensure MySQL commits table creation before inserts
    $wpdb->query('COMMIT');
}


function ccpfa_is_category(string $content): bool {
    // Trim and normalize
    $content = trim($content);

    // Empty string can't be a category
    if ($content === '') {
        return false;
    }

    // Regex for Roman numeral (I–XII, etc.) optionally followed by space + single uppercase letter
    $pattern = '/^(?=[IVXLCDM])M{0,3}(CM|CD|D?C{0,3})(XC|XL|L?X{0,3})(IX|IV|V?I{0,3})(?:\s*[A-Z])?$/i';

    // If it matches the Roman pattern, it's a category
    return (bool) preg_match($pattern, $content);
}

/**
 * Resolve mixed-up category and definition fields in a job array
 *
 * @param array $job Raw job array with keys 'category' and 'definition'
 * @return array ['category' => string, 'definition' => string]
 */
function ccpfa_resolve_category_definition(array $job): array {
    $definition = isset($job['definition']) ? trim($job['definition']) : '';
    $category   = isset($job['category']) ? trim($job['category']) : '';

    $_category   = '';
    $_definition = '';

    if ($definition && $category) {
        if (ccpfa_is_category($definition)) {
            $_category   = $definition;
            $_definition = $category;
        } elseif (ccpfa_is_category($category)) {
            $_category   = $category;
            $_definition = $definition;
        } else {
            $_category   = $category;
            $_definition = $definition;
        }
    } elseif ($definition) {
        if (ccpfa_is_category($definition)) {
            $_category = $definition;
        } else {
            $_definition = $definition;
        }
    } elseif ($category) {
        if (ccpfa_is_category($category)) {
            $_category = $category;
        } else {
            $_definition = $category;
        }
    }

    return [
        'category'   => $_category,
        'definition' => $_definition
    ];
}


function ccpfa_import_user_json_file() {
    if (
        isset($_POST['action']) &&
        $_POST['action'] === 'import_json' &&
        current_user_can('manage_options') &&
        !empty($_FILES['json_file']['tmp_name'])
    ) {
        $file = $_FILES['json_file']['tmp_name'];
        $json_content = file_get_contents($file);

        $data = json_decode($json_content, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            echo '<div class="notice notice-error"><p>Invalid JSON file.</p></div>';
            return;
        }
        print($data);

        ccpfa_upload_json($data);
    }
}


add_action('admin_post_upload_json', 'ccpfa_upload_json');
function ccpfa_upload_json(array $data) {
    if (!isset($data["jobs"], $data["categories"], $data["filieres"])) return;

    global $wpdb;
    $fonctions_table = $wpdb->prefix . CCPFA_TABLE_FONCTIONS;
    $filieres_table  = $wpdb->prefix . CCPFA_TABLE_FILIERES;

    $jobs       = array_values($data['jobs']); // all as objects/arrays
    $categories = $data["categories"];
    $filieres   = $data["filieres"];

    foreach ($jobs as $job) {
        $definition = $job['definition'] ?? '';
        $category   = $job['category'] ?? '';

        // Resolve category and definition (existing helper)
        $resolved = ccpfa_resolve_category_definition($job);

        // ---  Find or create filière ---
        $filiere_name = $job['filiere'] ?? '';
        $filiere_slug = sanitize_title($filiere_name);
        $filiere_id   = null;

        if (!empty($filiere_name)) {
            // Try to find existing filière by slug
            $filiere_id = $wpdb->get_var($wpdb->prepare(
                "SELECT id FROM $filieres_table WHERE slug = %s LIMIT 1",
                $filiere_slug
            ));

            // If not found, create it
            if (!$filiere_id) {
                $wpdb->insert($filieres_table, [
                    'slug' => $filiere_slug,
                    'nom'  => $filiere_name,
                ]);
                $filiere_id = $wpdb->insert_id;
            }
        }

        // --- Insert fonction ---
        $wpdb->insert($fonctions_table, [
            'fonction'               => $job['fonction'] ?? "",
            'version_feminisee'      => $job['version féminisée'] ?? $job['version_feminisee'] ?? '',
            '_category'              => $resolved['category'],
            '_definition'            => $resolved['definition'],
            'filiere_id'             => $filiere_id, // foreign key
            'slug'                   => $job['slug'] ?? null,
            'salaire_brut_mensuel'   => $job['salaire_brut_mensuel'] ?? null,
            'salaire_brut_journalier'=> $job['salaire_brut_journalier'] ?? null,
            'page_number'            => $job['page_number'] ?? null,
            'document_version'       => $job['document_version'] ?? null
        ]);
    }
}

function ccpfa_upload_local_json() {

    $json_path = CCPFA_FOLDER. 'data/conv_fonctions.json';

    if (!file_exists($json_path)) return;

    $data = json_decode(file_get_contents($json_path), true);
    if (!$data) return;

    if(!isset($data["jobs"])) return;
    if(!isset($data["categories"])) return;
    if(!isset($data["filieres"])) return;

    ccpfa_upload_json($data);
    
}

// the important part is admin_post_(download_json) --> that's the connection to the action= 
add_action('admin_post_download_json', 'ccpfa_download_json');
function ccpfa_download_json() {
    if (!current_user_can('manage_options')) {
        wp_die('Unauthorized user');
    }

    global $wpdb;
    $table_name = $wpdb->prefix . CCPFA_TABLE_FONCTIONS;
    $results = $wpdb->get_results("SELECT * FROM $table_name", ARRAY_A);
    $jobs = [];
    foreach ($results as &$row) {
        unset($row['_id']);
    }
    unset($row); // break the reference
    $final = new stdClass();
    $final->jobs = $results;
    $final->filieres =new stdClass();
    $final->categories = new stdClass();
    $json = json_encode($final, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

    $small_id = substr(uniqid(), -6); // last 6 characters of a unique ID
    $file_name = "ccpfa_fonctions-".$small_id.".json";


    // Force download
    if (ob_get_length()) ob_end_clean();
    header('Content-Description: File Transfer');
    header('Content-Type: application/json');
    header('Content-Disposition: attachment; filename="'.$file_name.'"');
    header('Expires: 0');
    header('Cache-Control: must-revalidate');
    header('Pragma: public');
    header('Content-Length: ' . strlen($json));

    echo $json;
    exit;
}