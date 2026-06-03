<?php
/**
 * Отключение поиска на сайте.
 *
 * @package wp-frame-lite
 */

defined( 'ABSPATH' ) || exit;

add_filter( 'get_search_form', '__return_empty_string' );

add_action(
	'parse_query',
	function ( WP_Query $query ): void {
		if ( ! is_admin() && $query->is_main_query() && $query->is_search() ) {
			wp_safe_redirect( home_url( '/' ), 301 );
			exit;
		}
	}
);
