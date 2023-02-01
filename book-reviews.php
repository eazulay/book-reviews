<?php
/**
 * Plugin Name: Book Reviews
 * Plugin URI:
 * Description: Store and display Books and Book Reviews
 * Version: 1.00
 * Author: Eyal Azulay
 * Author URI: https://get-it-write.com/about/
 * Text Domain: book-reviews
 * License: GPL-3.0+
 * License URI: https://www.gnu.org/licenses/gpl-3.0.html
*/
/*
Book Reviews is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
any later version.

Book Reviews is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with {Plugin Name}. If not, see {URI to Plugin License}.
*/
defined( 'ABSPATH' ) or die( 'You cannot run this script directly.' );

require_once( 'install.php' );
register_activation_hook( __FILE__, 'bkrv_install' ); // Create DB Table 'Reviews'

require_once( 'init.php' ); // Load Text Domand and register Post Type 'bkrv_book'

// Load either frontend or backend hooks
if ( is_admin() ){
	require_once( 'admin.php' );
}else{
	require_once( 'public.php' );
}
