<?php
defined( 'ABSPATH' ) or die( 'You cannot run this script directly.' );

// Load text domain
function bkrv_load_text_domain(){
    load_plugin_textdomain( 'book-reviews', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
}
add_action( 'plugins_loaded', 'bkrv_load_text_domain' );

/**
 * Register a "book" post type
 */
function bkrv_create_book_post_type() {
	$labels = array(
		'name'                  => _x( 'Books', 'Post type general name', 'book-reviews' ),
		'singular_name'         => _x( 'Book', 'Post type singular name', 'book-reviews' ),
		'menu_name'             => _x( 'Books', 'Admin Menu text', 'book-reviews' ),
		'name_admin_bar'        => _x( 'Book', 'Add New on Toolbar', 'book-reviews' ),
		'add_new'               => __( 'Add New', 'book-reviews' ),
		'add_new_item'          => __( 'Add New Book', 'book-reviews' ),
		'new_item'              => __( 'New Book', 'book-reviews' ),
		'edit_item'             => __( 'Edit Book', 'book-reviews' ),
		'view_item'             => __( 'View Book', 'book-reviews' ),
		'all_items'             => __( 'All Books', 'book-reviews' ),
		'search_items'          => __( 'Search Books', 'book-reviews' ),
		'not_found'             => __( 'No books found.', 'book-reviews' ),
		'not_found_in_trash'    => __( 'No books found in Trash.', 'book-reviews' ),
		'featured_image'        => _x( 'Book Cover Image', 'Overrides the “Featured Image” phrase for this post type. Added in 4.3', 'book-reviews' ),
		'set_featured_image'    => _x( 'Set cover image', 'Overrides the “Set featured image” phrase for this post type. Added in 4.3', 'book-reviews' ),
		'remove_featured_image' => _x( 'Remove cover image', 'Overrides the “Remove featured image” phrase for this post type. Added in 4.3', 'book-reviews' ),
		'use_featured_image'    => _x( 'Use as cover image', 'Overrides the “Use as featured image” phrase for this post type. Added in 4.3', 'book-reviews' ),
		'archives'              => _x( 'Book archives', 'The post type archive label used in nav menus. Default “Post Archives”. Added in 4.4', 'book-reviews' ),
		'insert_into_item'      => _x( 'Insert into book', 'Overrides the “Insert into post”/”Insert into page” phrase (used when inserting media into a post). Added in 4.4', 'book-reviews' ),
		'uploaded_to_this_item' => _x( 'Uploaded to this book', 'Overrides the “Uploaded to this post”/”Uploaded to this page” phrase (used when viewing media attached to a post). Added in 4.4', 'book-reviews' ),
		'filter_items_list'     => _x( 'Filter books list', 'Screen reader text for the filter links heading on the post type listing screen. Default “Filter posts list”/”Filter pages list”. Added in 4.4', 'book-reviews' ),
		'items_list_navigation' => _x( 'Books list navigation', 'Screen reader text for the pagination heading on the post type listing screen. Default “Posts list navigation”/”Pages list navigation”. Added in 4.4', 'book-reviews' ),
		'items_list'            => _x( 'Books list', 'Screen reader text for the items list heading on the post type listing screen. Default “Posts list”/”Pages list”. Added in 4.4', 'book-reviews' ),
	);
	$args = array(
		'labels'             => $labels,
		'public'             => true,
		'publicly_queryable' => true,
		'show_ui'            => true,
		'show_in_menu'       => true,
		'query_var'          => true,
		'rewrite'            => array( 'slug' => 'book' ),
		'capability_type'    => 'post',
		'has_archive'        => true,
		'hierarchical'       => true,
		'menu_position'      => 20, // below Pages
		'supports'           => array( 'title', 'editor', 'thumbnail', 'revisions' ),
	);
	register_post_type( 'bkrv_book', $args );
}
add_action( 'init', 'bkrv_create_book_post_type' );

/**
 * On plugin activation flush rewrite rules to make permalink work with the new post type
 */
function bkrv_rewrite_flush() {
    bkrv_create_book_post_type();
    flush_rewrite_rules();
}
register_activation_hook( __FILE__, 'bkrv_rewrite_flush' );
