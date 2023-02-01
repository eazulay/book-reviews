<?php
global $book_reviews_db_version;
$book_reviews_db_version = "1.03";

function bkrv_install() {
	global $wpdb, $book_reviews_db_version;
	require_once( ABSPATH.'wp-admin/includes/upgrade.php' );
	$prefix = $wpdb->prefix.'bkrv_';

	$sql = "CREATE TABLE ".$prefix."reviews (
  review_id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  book_id bigint(20) unsigned NOT NULL,
  review_title text COLLATE utf8_unicode_ci DEFAULT NULL,
  review text COLLATE utf8_unicode_ci,
  reviewer varchar(500) COLLATE utf8_unicode_ci DEFAULT NULL,
  review_date date DEFAULT NULL,
  first_published varchar(200) COLLATE utf8_unicode_ci DEFAULT NULL,
  status varchar(20) COLLATE utf8_unicode_ci DEFAULT 'draft',
  created_date datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY  (review_id),
  KEY BOOK_REVIEW (book_id,review_id),
  KEY BOOK_DATE (book_id,review_date)
) DEFAULT CHARSET=utf8;";
	dbDelta( $sql );
	
	update_option( "book_reviews_db_version", $book_reviews_db_version );
}
