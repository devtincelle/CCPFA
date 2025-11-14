
<?php
if (!class_exists('WP_List_Table')) {
    require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

/*
            'fonction' => $job['fonction'] ?? $key,
            'version_feminisee' => $job['version féminisée'] ?? $job['version_feminisee'] ?? '',
            '_category' => $category,
            '_definition' => $definition,
            'salaire_brut_mensuel' => $job['salaire_brut_mensuel'] ?? null,
            'salaire_brut_journalier' => $job['salaire_brut_journalier'] ?? null,
            'external_id' => $job['id'] ?? ''

*/

class CCPFA_Fonctions_List_Table extends WP_List_Table {

    function __construct() {
        parent::__construct([
            'singular' => 'ccpfa_job',
            'plural'   => 'ccpfa_jobs',
            'ajax'     => false
        ]);
    }

    /** Define table columns **/
    function get_columns() {
        return [
            'cb'                      => '<input type="checkbox" />',
            'fonction'                => 'Fonction',
            'version_feminisee'       => 'Version Féminisée',
            'filiere'                => 'filiere',
            '_category'               => 'Catégorie',
            '_definition'             => 'Définition',
            'salaire_brut_mensuel'    => 'Salaire Mensuel (€)',
            'salaire_brut_journalier' => 'Salaire Journalier (€)',
            'page_number'             => 'Page Source',
            'document_version'        => 'Source'
        ];
    }

    /** Make columns sortable **/
    function get_sortable_columns() {
        return [
            'id'                    => ['id', true],
            'fonction'              => ['fonction', false],
            '_category'             => ['_category', false],
            'filiere'               => ['filiere', false],
            'salaire_brut_mensuel'  => ['salaire_brut_mensuel', false],
            'salaire_brut_journalier' => ['salaire_brut_journalier', false],
            'page_number'           => ['page_number', false],
            'document_version'      => ['document_version', false],
        ];
    }

    /** Checkbox column for bulk actions **/
    function column_cb($item) {
        return sprintf('<input type="checkbox" name="bulk-delete[]" value="%s" />', $item['id']);
    }

    /** Default column rendering **/
    function column_default($item, $column_name) {
        switch ($column_name) {
            case 'id':
            case 'fonction':
            case 'version_feminisee':
            case '_category':
            case '_definition':
            case 'salaire_brut_mensuel':
            case 'salaire_brut_journalier':
            case 'page_number':
            case 'document_version':
            case 'external_id':
                return esc_html($item[$column_name]);

            case 'filiere': // map to filiere_nom from JOIN
                return esc_html($item['filiere_nom']);

            default:
                return print_r($item, true);
        }
    }

    /** Optional: add a link to “fonction” column **/
    function column_fonction($item) {
        $edit_url = admin_url('admin.php?page=ccpfa&action=edit&id=' . $item['id']);
        $actions = [
            'edit' => sprintf('<a href="%s">Edit</a>', $edit_url),
        ];
        return sprintf('%1$s %2$s', esc_html($item['fonction']), $this->row_actions($actions));
    }

    /** Load and paginate items **/
    function prepare_items() {
        global $wpdb;
        $fonctions_table = $wpdb->prefix . CCPFA_TABLE_FONCTIONS;
        $filieres_table  = $wpdb->prefix . CCPFA_TABLE_FILIERES;

        $per_page = 10;
        $current_page = $this->get_pagenum();
        $offset = ($current_page - 1) * $per_page;

        // Sorting (whitelist allowed columns)
        $allowed_orderby = ['id', 'fonction', '_category', 'filiere', 'salaire_brut_mensuel'];
        $allowed_order = ['ASC', 'DESC'];

        $orderby = !empty($_GET['orderby']) && in_array($_GET['orderby'], $allowed_orderby) ? $_GET['orderby'] : 'id';
        $order   = !empty($_GET['order']) && in_array(strtoupper($_GET['order']), $allowed_order) ? strtoupper($_GET['order']) : 'DESC';

        // 🔍 Handle search
        $where_sql = '';
        $params = [];
        if (!empty($_REQUEST['s'])) {
            $search = sanitize_text_field($_REQUEST['s']);
            $search_like = '%' . $wpdb->esc_like($search) . '%';
            $where_sql = "WHERE f.fonction LIKE %s OR f._category LIKE %s OR f.external_id LIKE %s OR fi.nom LIKE %s";
            $params = [$search_like, $search_like, $search_like, $search_like];
        }

        // Count total items
        if ($where_sql) {
            $total_items = (int) $wpdb->get_var(
                $wpdb->prepare(
                    "SELECT COUNT(*) 
                    FROM $fonctions_table f 
                    LEFT JOIN $filieres_table fi ON f.filiere_id = fi.id
                    $where_sql",
                    ...$params
                )
            );
        } else {
            $total_items = (int) $wpdb->get_var(
                "SELECT COUNT(*) 
                FROM $fonctions_table"
            );
        }

        // Fetch current page with JOIN
        if ($where_sql) {
            $query = $wpdb->prepare(
                "SELECT f.*, fi.nom AS filiere_nom 
                FROM $fonctions_table f
                LEFT JOIN $filieres_table fi ON f.filiere_id = fi.id
                $where_sql
                ORDER BY $orderby $order
                LIMIT %d OFFSET %d",
                ...array_merge($params, [$per_page, $offset])
            );
        } else {
            $query = $wpdb->prepare(
                "SELECT f.*, fi.nom AS filiere_nom
                FROM $fonctions_table f
                LEFT JOIN $filieres_table fi ON f.filiere_id = fi.id
                ORDER BY $orderby $order
                LIMIT %d OFFSET %d",
                $per_page, $offset
            );
        }

        $this->items = $wpdb->get_results($query, ARRAY_A);

        // Set up headers
        $columns  = $this->get_columns();
        $hidden   = [];
        $sortable = $this->get_sortable_columns();
        $this->_column_headers = [$columns, $hidden, $sortable];

        // Pagination
        $this->set_pagination_args([
            'total_items' => $total_items,
            'per_page'    => $per_page,
            'total_pages' => ceil($total_items / $per_page),
        ]);
    }

    /** Bulk actions **/
    function get_bulk_actions() {
        return [
            'bulk-delete' => 'Delete',
        ];
    }

}