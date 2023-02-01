<?php
defined( 'ABSPATH' ) or die( 'You cannot run this script directly.' );

/**
 * Load our CSS and JS script files
 */
add_action( 'wp_enqueue_scripts', function(){
	global $post;
	if ( isset( $post ) ) {
		if ( $post->post_type == 'bkrv_book' || $post->ID == get_option( 'bkrv_books_page' ) ){
			wp_enqueue_style( 'bkrv_public_style', plugins_url( 'style.css', __FILE__ ) );
			wp_enqueue_script( 'bkrv_js', plugins_url( 'book-reviews.js', __FILE__ ), array( 'jquery' ) );
		}
	}
} );

/**
 * Render plugin-specific pages
 */
add_filter( 'the_content', function( $content ){
	global $post, $wpdb;
	$prefix = $wpdb->prefix.'bkrv_';
	$page_url_query = get_permalink();
	$query_prefix = strpos( $page_url_query, '?' ) > 0 ? '&' : '?';
	$page_url_query .= $query_prefix;

	if( !is_singular() || !is_main_query() || !in_the_loop() )
		return $content;
	remove_filter( current_filter(), __FUNCTION__ );

	if ( $post->post_type == 'bkrv_book' ){
		$short_description = get_post_meta( $post->ID, 'bkrv_short_description', true );
		if ( !empty( $short_description ) )
			$content = "<div class='bkrv-short-desc'>" . esc_html( $short_description ) . "</div>" . $content;
		$book_excerpt = get_post_meta( $post->ID, 'bkrv_excerpt', true );
		if ( $book_excerpt ){
			$content .= "<div class='bkrv-excerpt'><h3>" . esc_html__( 'Excerpt', 'book-reviews' ). "</h3>";
			if ( strpos( $book_excerpt, '<!--more-->' ) ){
				$pos = strpos( $book_excerpt, '<!--more-->' );
				$content .= substr( $book_excerpt, 0, $pos )."<span class='bkrv-excerpt-more' data-pop-id='book-".$post->ID."'>Continue reading...</span>";
				$content .= "<div id='book-".$post->ID."' class='bkrv-popup'><div class='bkrv-pop-outer'><div class='bkrv-close-popup'> X </div><div class='bkrv-pop-inner'>".$book_excerpt."</div></div></div>";
			}else
				$content .= wp_kses_post( $book_excerpt );
			$content .= "</div>";
		}
		$query = "SELECT review_id, review_title, review, reviewer, review_date, first_published"
            . " FROM " . $prefix . "reviews"
			. " WHERE book_id=" . $post->ID . " AND status='publish'"
			. " ORDER BY review_date DESC";
		$reviews = $wpdb->get_results($query);
		if (count( $reviews ) > 0){
			$content .= "<div class='bkrv-reviews-container'>";
			$content .= "<h3>" . esc_html__( 'Reviews', 'book-reviews' ) . "</h3>";
			foreach($reviews as $review){
				$content .= "<div class='bkrv-review-container'>";
				$content .= "<h4 class='bkrv-review-title'>" . esc_html( $review->review_title ). "</h4>";
				$content .= "<em class='bkrv-reviewer'>" . esc_html( $review->reviewer ) . "</em>";
				$content .= "<blockquote>" . wp_kses_post( $review->review ) . "</blockquote>";
				if (!empty( $review->first_published ))
					$content .= "<em class='bkrv-first-published'>First published on " . esc_html( $review->first_published ) . "</em>";
				$content .= "<div class='clear-fix'></div>";
				$content .= "</div>";
			}
			$content .= "</div>";
		}
	}
	return $content;
} );

/**
 * Change the Book Archives page title
 */
add_filter( 'get_the_archive_title', function( $title ){
	if ( is_archive('bkrv_book') ){
		$title = esc_html( sprintf( __( "%s&#8217;s Books", 'book-reviews' ), get_option( 'bkrv_author_name' ) ) );
		//			   Close Curly Quote = &#8217; It looks better than an apostrophy
	}
	return $title;
} );

/**
 * Change the excerpt for the Book archives page to display the Short Description instead of the standard excerpt.
 */
add_filter( 'get_the_excerpt', function( $post_excerpt, $post ){
	if ( is_archive('bkrv_book') ){
		$post_excerpt = esc_html( get_post_meta( $post->ID, 'bkrv_short_description', true ) );
	}
	return $post_excerpt;
}, 10, 2 );

/**
 * Add shortcode [bkrv_books] to print a list of all Books with status Published
 */
add_shortcode( 'bkrv_books', function( $attributes ){
	$books = get_posts( array(
		'post_type' => 'bkrv_book',
		'post_status' => 'publish'
	) );
	$result = '<ul>';
	foreach($books as $book){
		$result .= '<li><a href="' . get_post_permalink( $book ) . '">' . $book->post_title . '</a></li>';
	}
	$result .= '</ul>';
	return $result;
} );

/**
 * Add shortcode [bkrv_author] to print Author's name
 */
add_shortcode( 'bkrv_author', function( $attributes ){
    $author = get_option( 'bkrv_author_name' );
    return $author;
} );
