<?php
/**
 * The template for displaying 404 pages (not found)
 *
 * @link https://codex.wordpress.org/Creating_an_Error_404_Page
 *
 * @package wp-frame-lite
 */

get_header();
?>

	<main id="primary" class="site-main">
		<div class="container">

			<section class="error-404 not-found">
				<header class="page-header">
					<h1 class="page-title">Страница не найдена</h1>
				</header><!-- .page-header -->

				<div class="page-content">
					<p>Возможно, она была удалена или перемещена.</p>
					<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="btn">Вернуться на главную</a>
				</div><!-- .page-content -->
			</section><!-- .error-404 -->

		</div><!-- .container -->
	</main><!-- #main -->

<?php
get_footer();
