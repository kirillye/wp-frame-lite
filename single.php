<?php
/**
 * The template for displaying all single posts
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/#single-post
 *
 * @package wp-frame-lite
 */

get_header();
?>

	<main id="primary" class="site-main site-content">
		<div class="container">

			<?php
			while ( have_posts() ) :
				the_post();

				get_template_part( 'template-parts/content', get_post_type() );

				the_post_navigation(
					array(
						'prev_text' => '<span class="nav-subtitle">Предыдущая:</span> <span class="nav-title">%title</span>',
						'next_text' => '<span class="nav-subtitle">Следующая:</span> <span class="nav-title">%title</span>',
					)
				);

			endwhile; // End of the loop.
			?>

		</div><!-- .container -->
	</main><!-- #main -->

<?php
get_footer();
