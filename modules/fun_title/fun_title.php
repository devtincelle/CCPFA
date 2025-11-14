<?php



function ccpfa_render_fun_title($text,$type){
    $fun_title_content = esc_html( $text);
    $fun_title_type = esc_html( $type);
    ob_start();
    include CCPFA_MODULES_FOLDER.'/fun_title/fun_title_view.php';
    return ob_get_clean();
}


// RETRO
function fun_title_enqueue_assets_retro() {
    global $post;
    // Make sure we're in a post/page context
    if ( is_a( $post, 'WP_Post' ) && has_shortcode( $post->post_content, 'ccpfa_fun_title_retro') ) {
        // Load your CSS
        wp_enqueue_style(
            'myplugin-styles',
            CCPFA_MODULES_URL . '/fun_title/fun_title_retro.css',
            array(),
            '1.0.0'
        );

        // (Optional) Load JS too
        wp_enqueue_script(
            'myplugin-script',
            CCPFA_MODULES_URL . '/fun_title/fun_title.js',
            array('jquery'),
            '1.0.0',
            true
        );
    }
}
add_action( 'wp_enqueue_scripts', 'fun_title_enqueue_assets_retro' );

//[ccpfa_fun_title_retro text=hello]
function ccpfa_fun_title_short_code_retro($atts) {
    // Set default attributes
    $atts = shortcode_atts([
        'text' => 'text',
    ], $atts, 'ccpfa_fun_title');
    $text  = esc_attr($atts['text']);    
    $type  = "fun_title_retro";
    return ccpfa_render_fun_title($text,$type);
}
add_shortcode('ccpfa_fun_title_retro', 'ccpfa_fun_title_short_code_retro');


// MASLED
$short_code = "ccpfa_fun_title_mask";
function fun_title_enqueue_assets_mask() {
    global $post;
    // Make sure we're in a post/page context
    if ( is_a( $post, 'WP_Post' ) && has_shortcode( $post->post_content,"ccpfa_fun_title_mask") ) {
        // Load your CSS
        wp_enqueue_style(
            'myplugin-styles',
            CCPFA_MODULES_URL . '/fun_title/fun_title_mask.css',
            array(),
            '1.0.0'
        );

        // (Optional) Load JS too
        wp_enqueue_script(
            'myplugin-script',
            CCPFA_MODULES_URL . '/fun_title/fun_title.js',
            array('jquery'),
            '1.0.0',
            true
        );
    }
}
add_action( 'wp_enqueue_scripts', 'fun_title_enqueue_assets_mask' );

//[ccpfa_fun_title_mask text=hello]
function ccpfa_fun_title_short_code_mask($atts) {
    // Set default attributes
    $atts = shortcode_atts([
        'text' => 'text',
    ], $atts, 'ccpfa_fun_title');
    $text  = esc_attr($atts['text']);    
    $type  = "fun_title_mask";
    return ccpfa_render_fun_title($text,$type);
}
add_shortcode("ccpfa_fun_title_mask", 'ccpfa_fun_title_short_code_mask');

