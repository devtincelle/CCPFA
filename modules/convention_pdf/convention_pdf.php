<?php


add_action('wp_ajax_ccpfa_get_pdf', 'ccpfa_get_pdf');
add_action('wp_ajax_nopriv_ccpfa_get_pdf', 'ccpfa_get_pdf');

function ccpfa_get_pdf() {

    // Get PDF filename and page from GET or POST
    $file = sanitize_file_name($_GET['file'] ?? '');
    $page = intval($_GET['page'] ?? 1);

    // Construct full path to your plugin folder
    $full_path = CCPFA_DOCS_URL . '/' . $file;
    error_log($full_path);

    if (!file_exists($full_path)) {
        wp_die('File not found', 404);
    }

    // Optional: force download headers
    header('Content-Type: application/pdf');
    header('Content-Disposition: inline; filename="' . basename($full_path) . '"');
    header('Content-Length: ' . filesize($full_path));
    
    // Serve the file
    readfile($full_path);
    exit;
}


// CONV PAGES LINKS 
function ccpfa_generate_page_link($page_number):string{
    $pdf_url = add_query_arg(
        array(
            'action' => 'ccpfa_get_pdf',
            'file'   => 'CCN_production_animation_consolidee_01032015.pdf'
        ),
        admin_url('admin-ajax.php')
    );
    return esc_url($pdf_url . '#page='.$page_number );   
}
function ccpfa_generate_page_ahref($page_number):string{
    $pdf_url = add_query_arg(
        array(
            'action' => 'ccpfa_get_pdf',
            'file'   => 'CCN_production_animation_consolidee_01032015.pdf'
        ),
        admin_url('admin-ajax.php')
    );

    ob_start();
    ?>
        <a href="<?php echo ccpfa_generate_page_link($page_number )?>" target="_blank">
            <br>Page de la convetion > <?= nl2br(esc_html($page_number ?? '-')); ?>
        </a>
    <?php
    $html = ob_get_clean();
    return $html;   
}