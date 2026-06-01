<?php
/**
 * The main template file
 *
 * This is the most generic template file in a WordPress theme
 * and one of the two required files for a theme (the other being style.css).
 * It is used to display a page when nothing more specific matches a query.
 * E.g., it puts together the home page when no home.php file exists.
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package wp-frame-lite
 */

get_header();
?>

<?php if ( is_front_page() ) : ?>
<section class="hero">
	<div class="container">
		<div class="hero-content">
			<h1 class="hero-title"><?php echo esc_html( get_bloginfo( 'name' ) ); ?></h1>
			<?php $hero_desc = get_bloginfo( 'description', 'display' ); ?>
			<?php if ( $hero_desc ) : ?>
			<p class="hero-subtitle"><?php echo esc_html( $hero_desc ); ?></p>
			<?php else : ?>
			<p class="hero-subtitle"><?php esc_html_e( 'Добро пожаловать на наш сайт', 'wp-frame-lite' ); ?></p>
			<?php endif; ?>
			<a href="#primary" class="btn"><?php esc_html_e( 'Узнать больше', 'wp-frame-lite' ); ?></a>
		</div>
	</div>
</section>
<?php endif; ?>

	<main id="primary" class="site-main site-content">
		<div class="container">

			<?php
			if ( have_posts() ) :

				if ( is_home() && ! is_front_page() ) :
					?>
					<header>
						<h1 class="page-title screen-reader-text"><?php single_post_title(); ?></h1>
					</header>
					<?php
				endif;

				/* Start the Loop */
				while ( have_posts() ) :
					the_post();

					get_template_part( 'template-parts/content', get_post_type() );

				endwhile;

				the_posts_navigation();

			else :

				get_template_part( 'template-parts/content', 'none' );

			endif;
			?>

		</div><!-- .container -->
	</main><!-- #main -->

<?php
get_footer();
