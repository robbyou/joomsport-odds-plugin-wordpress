<?php
/**
 * Child-Theme functions and definitions
 */
 
function fcunited_enqueue_styles() {
    wp_enqueue_style( 'fcunited-parent-style', get_template_directory_uri() . '/style.css' );
		if ( is_rtl() ) {
		wp_enqueue_style( 'fcunited-parent-rtl-style', get_template_directory_uri() . '/rtl.css' );
	}
}
add_action( 'wp_enqueue_scripts', 'fcunited_enqueue_styles' );