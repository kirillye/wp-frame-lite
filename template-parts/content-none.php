<?php
/**
 * Template part for displaying a message that posts cannot be found
 *
 * @package wp-frame-lite
 */

?>

<section class="no-results not-found">
	<header class="page-header">
		<h1 class="page-title"><?php esc_html_e( 'Ничего не найдено', 'wp-frame-lite' ); ?></h1>
	</header><!-- .page-header -->

	<div class="page-content">
		<?php if ( is_home() && current_user_can( 'publish_posts' ) ) : ?>
			<p>
				<?php
				printf(
					wp_kses(
						/* translators: 1: link to WP admin new post page. */
						__( 'Готовы опубликовать первый пост? <a href="%1$s">Начните здесь</a>.', 'wp-frame-lite' ),
						array( 'a' => array( 'href' => array() ) )
					),
					esc_url( admin_url( 'post-new.php' ) )
				);
				?>
			</p>
		<?php else : ?>
			<p><?php esc_html_e( 'Здесь пока ничего нет.', 'wp-frame-lite' ); ?></p>
		<?php endif; ?>
	</div><!-- .page-content -->
</section><!-- .no-results -->
