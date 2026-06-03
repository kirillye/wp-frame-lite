<?php
/**
 * Хлебные крошки.
 *
 * Использование:
 *   wp_frame_lite_breadcrumbs();
 *   get_template_part( 'template-parts/breadcrumbs' );
 *
 * @package wp-frame-lite
 */

defined( 'ABSPATH' ) || exit;

/**
 * @param array{
 *   separator:    string,
 *   home_label:   string,
 *   show_on_home: bool,
 *   before:       string,
 *   after:        string,
 * } $args
 */
function wp_frame_lite_breadcrumbs( array $args = array() ): void {
	$args = wp_parse_args(
		$args,
		array(
			'separator'    => '/',
			'home_label'   => __( 'Главная', 'wp-frame-lite' ),
			'show_on_home' => false,
			'before'       => '<nav class="breadcrumbs" aria-label="' . esc_attr__( 'Хлебные крошки', 'wp-frame-lite' ) . '">',
			'after'        => '</nav>',
		)
	);

	if ( is_front_page() && ! $args['show_on_home'] ) {
		return;
	}

	$crumbs = array();

	$crumbs[] = array(
		'label' => $args['home_label'],
		'url'   => home_url( '/' ),
	);

	if ( is_home() && ! is_front_page() ) {

		$crumbs[] = array( 'label' => get_the_title( (int) get_option( 'page_for_posts' ) ) );

	} elseif ( is_single() ) {

		$post_type = get_post_type();

		if ( 'post' === $post_type ) {
			$cats = get_the_category();
			if ( $cats ) {
				$chain = array();
				$cat   = $cats[0];
				while ( $cat ) {
					$chain[] = array(
						'label' => $cat->name,
						'url'   => get_category_link( $cat->term_id ),
					);
					$cat = $cat->parent ? get_term( $cat->parent, 'category' ) : null;
				}
				foreach ( array_reverse( $chain ) as $c ) {
					$crumbs[] = $c;
				}
			}
		} else {
			$pto = get_post_type_object( $post_type );
			if ( $pto && $pto->has_archive ) {
				$crumbs[] = array(
					'label' => $pto->labels->name,
					'url'   => get_post_type_archive_link( $post_type ),
				);
			}
		}

		$crumbs[] = array( 'label' => get_the_title() );

	} elseif ( is_page() ) {

		foreach ( array_reverse( get_post_ancestors( get_the_ID() ) ) as $ancestor_id ) {
			$crumbs[] = array(
				'label' => get_the_title( $ancestor_id ),
				'url'   => get_permalink( $ancestor_id ),
			);
		}
		$crumbs[] = array( 'label' => get_the_title() );

	} elseif ( is_category() ) {

		$term = get_queried_object();
		if ( $term->parent ) {
			$parent = get_term( $term->parent, 'category' );
			if ( $parent && ! is_wp_error( $parent ) ) {
				$crumbs[] = array(
					'label' => $parent->name,
					'url'   => get_category_link( $parent->term_id ),
				);
			}
		}
		$crumbs[] = array( 'label' => single_cat_title( '', false ) );

	} elseif ( is_tag() ) {

		$crumbs[] = array( 'label' => single_tag_title( '', false ) );

	} elseif ( is_tax() ) {

		$term     = get_queried_object();
		$taxonomy = get_taxonomy( $term->taxonomy );
		if ( $taxonomy ) {
			$crumbs[] = array( 'label' => $taxonomy->labels->name );
		}
		$crumbs[] = array( 'label' => $term->name );

	} elseif ( is_author() ) {

		$crumbs[] = array( 'label' => get_the_author_meta( 'display_name', get_queried_object_id() ) );

	} elseif ( is_post_type_archive() ) {

		$crumbs[] = array( 'label' => post_type_archive_title( '', false ) );

	} elseif ( is_day() ) {

		$year  = (int) get_query_var( 'year' );
		$month = (int) get_query_var( 'monthnum' );
		$day   = (int) get_query_var( 'day' );

		$crumbs[] = array( 'label' => (string) $year, 'url' => get_year_link( $year ) );
		$crumbs[] = array( 'label' => date_i18n( 'F', mktime( 0, 0, 0, $month, 1, $year ) ), 'url' => get_month_link( $year, $month ) );
		$crumbs[] = array( 'label' => (string) $day );

	} elseif ( is_month() ) {

		$year  = (int) get_query_var( 'year' );
		$month = (int) get_query_var( 'monthnum' );

		$crumbs[] = array( 'label' => (string) $year, 'url' => get_year_link( $year ) );
		$crumbs[] = array( 'label' => date_i18n( 'F', mktime( 0, 0, 0, $month, 1, $year ) ) );

	} elseif ( is_year() ) {

		$crumbs[] = array( 'label' => (string) get_query_var( 'year' ) );

	} elseif ( is_search() ) {

		$crumbs[] = array(
			'label' => sprintf(
				/* translators: %s: поисковый запрос */
				__( 'Результаты: «%s»', 'wp-frame-lite' ),
				get_search_query()
			),
		);

	} elseif ( is_404() ) {

		$crumbs[] = array( 'label' => __( 'Страница не найдена', 'wp-frame-lite' ) );

	}

	$total = count( $crumbs );

	// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- developer-controlled arg
	echo $args['before'];
	echo '<ol class="breadcrumbs__list" itemscope itemtype="https://schema.org/BreadcrumbList">';

	foreach ( $crumbs as $i => $crumb ) {
		$is_last  = ( $i === $total - 1 );
		$position = $i + 1;

		printf(
			'<li class="breadcrumbs__item%s" itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">',
			$is_last ? ' breadcrumbs__item--current' : ''
		);

		if ( ! $is_last && ! empty( $crumb['url'] ) ) {
			printf(
				'<a class="breadcrumbs__link" href="%s" itemprop="item"><span itemprop="name">%s</span></a>',
				esc_url( $crumb['url'] ),
				esc_html( $crumb['label'] )
			);
		} else {
			printf(
				'<span class="breadcrumbs__current" itemprop="name" aria-current="page">%s</span>',
				esc_html( $crumb['label'] )
			);
		}

		printf( '<meta itemprop="position" content="%d">', absint( $position ) );

		if ( ! $is_last ) {
			printf(
				'<span class="breadcrumbs__sep" aria-hidden="true">%s</span>',
				esc_html( $args['separator'] )
			);
		}

		echo '</li>';
	}

	echo '</ol>';
	// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- developer-controlled arg
	echo $args['after'];
}
