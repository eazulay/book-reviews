<?php
defined( 'ABSPATH' ) or die( 'You cannot run this script directly.' );
require_once('classes.php'); // extends WP_List_Table

/**
 * Enqueue scripts for all admin pages.
 *
 * @param string $hook_suffix The current admin page.
 */
add_action( 'admin_enqueue_scripts', function( $hook_suffix ){
	if ($hook_suffix == 'toplevel_page_bkrv-review');
		wp_enqueue_style( 'bkrv_admin_style', plugins_url( 'style-admin.css', __FILE__ ) );
} );

/**
 * Register our Options settings
 */
add_action( 'admin_init', function(){
	register_setting( 'bkrv_settings', 'bkrv_author_name' );
} );

/**
 * Add a meta box to the Book edit page
 */
add_action( 'add_meta_boxes', function($param){
	add_meta_box(
		'bkrv_book_more',
		'More about this book',
		'bkrv_display_book_meta_box',
		'bkrv_book',
		'normal',
		'high' );
} );

function bkrv_display_book_meta_box(){
	global $post;
	if ( !current_user_can( 'edit_post', $post->ID ) )
		return;
	?>
	<div class="form-wrap">
	<?php wp_nonce_field( 'bkrv-book-meta-fields', 'bkrv-book-meta-fields_wpnonce', false ); ?>
		<div class="form-field">
			<label for="bkrv_short_description"><b><?php _e( 'Short Description', 'book-reviews' ); ?></b></label>
			<textarea id="bkrv_short_description" name="bkrv_short_description"><?php echo get_post_meta( $post->ID, 'bkrv_short_description', true ); ?></textarea>
		</div>
		<div class="form-field">
			<label for="bkrv_excerpt"><b><?php _e( 'Excerpt', 'book-reviews' ); ?></b></label>
			<?php wp_editor(
					get_post_meta( $post->ID, 'bkrv_excerpt', true ),
					'book_excerpt_editor',
					array( // For the full list of options, see https://developer.wordpress.org/reference/classes/_wp_editors/parse_settings/
						'media_buttons' => false,
						'textarea_rows' => 12,
						'textarea_name' => 'bkrv_excerpt',
					)
				); ?>
		</div>
	</div><?php
}

/**
 * Handle saving Book post meta fields
 */
add_action( 'save_post', function( $post_id ){
	if ( !isset( $_POST[ 'bkrv-book-meta-fields_wpnonce' ] ) || !wp_verify_nonce( $_POST[ 'bkrv-book-meta-fields_wpnonce' ], 'bkrv-book-meta-fields' ) )
		return;
	if ( !current_user_can( 'edit_post', $post_id ) ){
		return;
	}
	if ( isset( $_POST[ 'bkrv_short_description' ] ) ){
		if ( !empty( $_POST[ 'bkrv_short_description' ] ))
			update_post_meta( $post_id, 'bkrv_short_description', sanitize_textarea_field( $_POST[ 'bkrv_short_description' ] ) );
		else
			delete_post_meta( $post_id, 'bkrv_short_description' );
	}
	if ( isset( $_POST[ 'bkrv_excerpt' ] ) ){
		if ( !empty( $_POST[ 'bkrv_excerpt' ] ) )
			update_post_meta( $post_id, 'bkrv_excerpt', wp_kses_post( wpautop( $_POST[ 'bkrv_excerpt' ] ) ) );
		else
			delete_post_meta( $post_id, 'bkrv_excerpt' );
	}
} );

/**
 * Create our menu items
 */
add_action( 'admin_menu', function(){
	add_options_page(
		__( 'Book Reviews Settings', 'book-reviews' ),
		__( 'Book Reviews', 'book-reviews' ),
		'manage_options',
		'bkrv-settings',
		'bkrv_settings',
		'',
		20 );
	add_menu_page(
		__( 'Book Reviews', 'books-reviews' ),
		__( 'Reviews', 'books-reviews' ),
		'edit_posts',
		'bkrv-review',
		'bkrv_reviews',
		'dashicons-format-quote',
		21 );
	add_submenu_page( 'bkrv-review',
		__( 'Book Reviews', 'books-reviews' ),
		__( 'All Reviews', 'books-reviews' ),
		'edit_posts',
		'bkrv-review',
		'bkrv_reviews');
	add_submenu_page( 'bkrv-review',
		__( 'Add Book Review', 'books-reviews' ),
		__( 'Add New', 'books-reviews' ),
		'edit_posts',
		'bkrv-review-new',
		'bkrv_review_new');
} );

/**
 * Render our admin settings page
 */
function bkrv_settings(){
?>
<div class="wrap">
	<h2><?php _e( 'Book Reviews Settings', 'book-reviews' ); ?></h2>
	<form method="post" action="options.php">
<?php	settings_fields( 'bkrv_settings' );
		do_settings_sections( 'bkrv_settings' ); ?>
		<table class="form-table">
			<tr valign="top">
				<th><label for="bkrv_author_name"><?php _e( 'Author Name', 'book-reviews' ); ?></label></th>
				<td><input name="bkrv_author_name" id="bkrv_author_name" value="<?php _e( get_option( 'bkrv_author_name' ) ); ?>"/></td>
			</tr>
		</table>
		<?php submit_button(); ?>
	</form>
</div>
<?php
}

function bkrv_reviews(){
	if ( !current_user_can( 'edit_posts' ) )
		return;
	if (isset( $_GET[ 'action' ] ) && isset( $_GET[ 'id' ] )){
		if ($_GET[ 'action' ] == 'edit'){
			bkrv_edit_review( $_GET[ 'id' ] );
		}elseif ($_GET[ 'action' ] == 'delete'){
			bkrv_delete_review($_GET[ 'id' ]);
			bkrv_list_reviews();
			//wp_redirect( get_admin_url() . 'admin?page=bkrv-review' );
		}
	}else{
		bkrv_list_reviews();
	}
}

function bkrv_list_reviews(){
?>
<div class="wrap">
	<h1 class="wp-heading-inline"><?php _e( 'Book Reviews', 'book-reviews' ); ?></h1>
	<a href="<?php get_admin_url(); ?>admin.php?page=bkrv-review-new" class="page-title-action"><?php _e( 'Add New', 'book-reviews' ); ?></a>
	<form id="bkrv-reviews-form" method="post">
<?php
	$list_table = new BKRV_Reviews_List_Table();
	$list_table->prepare_items();
	$list_table->display();
?>
	</form>
</div>
<?php
}

function bkrv_review_new(){
	if ( !current_user_can( 'edit_posts' ) )
		return;
	bkrv_edit_review(-1);
}

/**
 * Save data submitted in bkrv_edit_review
 */
add_action( 'wp_loaded', function( $wp ){
	global $wpdb;
	$prefix = $wpdb->prefix.'bkrv_';
	if (is_admin() && isset( $_REQUEST['save'] ) && isset( $_REQUEST[ '_wpnonce' ] ) && wp_verify_nonce( $_REQUEST[ '_wpnonce' ], 'edit_review' )){
		if ($_REQUEST[ 'page' ] == 'bkrv-review-new'){
			$wpdb->show_errors();
			$result = $wpdb->insert(
				$prefix . 'reviews',
				array(
					'review_title' => sanitize_text_field( $_REQUEST[ 'review_title' ] ),
					'book_id' => sanitize_text_field( $_REQUEST[ 'book_id' ] ),
					'review' => wp_kses_post( wpautop( $_REQUEST[ 'review' ] ) ),
					'reviewer' => sanitize_text_field( $_REQUEST[ 'reviewer' ] ),
					'review_date' => sanitize_text_field( $_REQUEST[ 'review_date' ] ),
					'first_published' => sanitize_text_field( $_REQUEST[ 'first_published' ] ),
					'status' => $_REQUEST[ 'status' ] == 'publish' ? 'publish' : 'draft',
				),
				array( '%s', '%d', '%s', '%s', '%s', '%s', '%s' )
			);
			if ( $result > 0 ){
				$wpdb->hide_errors();
				$new_id = $wpdb->insert_id;
				wp_redirect( get_admin_url() . "admin.php?page=bkrv-review&action=edit&id=" . $new_id );
			}else
				die();
		}elseif (isset( $_REQUEST[ 'id' ]) && is_numeric( $_REQUEST[ 'id' ] )){
			$wpdb->show_errors();
			$wpdb->update(
				$prefix . 'reviews',
				array(
					'review_title' => sanitize_text_field( $_REQUEST[ 'review_title' ] ),
					'book_id' => sanitize_text_field( $_REQUEST[ 'book_id' ] ),
					'review' => wp_kses_post( wpautop( $_REQUEST[ 'review' ] ) ),
					'reviewer' => sanitize_text_field( $_REQUEST[ 'reviewer' ] ),
					'review_date' => sanitize_text_field( $_REQUEST[ 'review_date' ] ),
					'first_published' => sanitize_text_field( $_REQUEST[ 'first_published' ] ),
					'status' => $_REQUEST[ 'status' ] == 'publish' ? 'publish' : 'draft',
				),
				array(
					'review_id' => $_REQUEST[ 'id' ]
				),
				array( '%s', '%d', '%s', '%s', '%s', '%s', '%s' ),
				array( '%d' )
			);
		}
	}
} );

function bkrv_edit_review( $review_id ){
	global $wpdb;
	$prefix = $wpdb->prefix.'bkrv_';
	$query = "SELECT r.review_id, r.review_title, r.book_id, p.post_title, r.review, r.reviewer, r.review_date, r.first_published, r.status, r.created_date"
		. " FROM " . $prefix . "reviews r"
		. " LEFT JOIN " . $wpdb->posts . " p ON p.ID=r.book_id"
		. " WHERE r.review_id = " . $review_id;
	$rec = $wpdb->get_row( $query );
?>
<div class="wrap">
	<h1 class="wp-heading-inline"><?php if ($review_id == -1)
			_e( 'Add Book Review', 'book-reviews' );
		else
			_e( 'Edit Book Review', 'book-reviews' );
?>	</h1>
	<form method="post" action="<?php get_admin_url(); ?>admin.php?<?php echo 'page=' . $_REQUEST[ 'page' ] . '&action=' . $_REQUEST[ 'action' ] ?>&id=<?php echo $review_id; ?>">
		<?php wp_nonce_field( 'edit_review' ); ?>
		<div id="poststuff">
			<div id="post-body" class="metabox-holder columns-2">
				<div id="post-body-content">
					<div id="titlediv">
						<div id="titlewrap">
							<input id="title" type="text" name="review_title" size="30" value="<?php echo $rec->review_title; ?>" placeholder="<?php _e( 'Review Title', 'book-reviews' ); ?>" spellcheck="true" autocomplete="off" />
						</div>
					</div>
				</div>
				<div id="postbox-container-1" class="postbox-container">
					<div id="side-sortables" class="meta-box-sortables">
						<div id="submitdiv" class="postbox">
							<div class="postbox-header">
								<h2 class="hndle ui-sortable-handle"><?php _e( 'Status', 'book-reviews' ); ?></h2>
							</div>
							<div class="inside">
								<div class="misc-pub-section misc-pub-post-status"">
									<label for="bkrv_status"><?php _e( 'Review Status', 'book-reviews' ); ?></label>
									<select id="post-status-display" name="status">
										<option value="draft" <?php selected('draft', $rec->status); ?>><?php _e( 'Draft' ); ?></option>
										<option value="publish" <?php selected('publish', $rec->status); ?>><?php _e( 'Published' ); ?></option>
									</select>
								</div>
								<div class="misc-pub-section curtime misc-pub-curtime">
									<span id="timestamp">Created on: <b><?php echo date( "d M Y \\a\\t h:i", strtotime( $rec->created_date ) ); ?></b></span>
								</div>
								<div id="major-publishing-actions">
									<div id="delete-action">
										<a class="submitdelete" href="<?php echo get_admin_url() . 'admin?page=' . $_REQUEST[ 'page' ] . '&action=delete&id=' . $rec->review_id . '&_wpnonce=' . wp_create_nonce( 'delete_review' ); ?>">Delete</a>
									</div>
									<div id="publishing-action">
										<input type="submit" name="save" id="publish" class="button button-primary button-large" value="Update">
									</div>
									<div class="clear"></div>
								</div>
							</div>
						</div>
					</div>
				</div>
				<div id="postbox-container-2" class="postbox-container">
					<div id="normal-sortables" class="meta-box-sortables">
						<div class="postbox">
							<div class="postbox-header">
								<h2 class="hndle ui-sortable-handle"><?php _e( 'Review Details', 'book-reviews' ); ?></h2>
							</div>
							<div class="inside">
								<div class="form-wrap">
									<div class="form-field">
										<label for="bkrv_book"><b><?php _e( 'Book', 'book-reviews' ); ?></b></label>
										<?php wp_dropdown_pages( array(
														'name' => 'book_id',
														'post_type' => 'bkrv_book',
														'selected' => $rec->book_id,
														'show_option_none' => '&lt;None&gt;' ) );?>
									</div>
									<div class="form-field">
										<label for="bkrv_review"><b><?php _e( 'Review', 'book-reviews' ); ?></b></label>
										<?php wp_editor(
												$rec->review,
												'review_editor',
												array( // For the full list of options, see https://developer.wordpress.org/reference/classes/_wp_editors/parse_settings/
													'media_buttons' => false,
													'textarea_rows' => 10,
													'textarea_name' => 'review',
												)
											); ?>
									</div>
									<div class="form-field">
										<label for="bkrv_reviewer"><b><?php _e( 'Reviewer', 'book-reviews' ); ?></b></label>
										<input type="text" id="bkrv_reviewer" name="reviewer" value="<?php echo esc_html( $rec->reviewer ); ?>" />
									</div>
									<div class="form-field">
										<label for="bkrv_review_date"><b><?php _e( 'Review Date', 'book-reviews' ); ?></b></label>
										<input type="date" id="bkrv_review_date" name="review_date" value="<?php echo $rec->review_date; ?>" />
									</div>
									<div class="form-field">
										<label for="bkrv_first_published"><b><?php _e( 'First Published', 'book-reviews' ); ?></b></label>
										<input type="text" id="bkrv_first_published" name="first_published" value="<?php echo esc_html( $rec->first_published ); ?>" />
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</form>
<?php
}

function bkrv_delete_review( $review_id ){
	global $wpdb;
	if (!wp_verify_nonce( $_REQUEST[ '_wpnonce' ], 'delete_review' ))
		die('Nonce failed');
	$prefix = $wpdb->prefix.'bkrv_';
	$wpdb->delete(
		$prefix . 'reviews',
		array( 'review_id' => $review_id ),
		array( '%d' )
	);
}