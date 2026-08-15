<?php
/**
 * Template part for displaying a message that posts cannot be found
 *
 * @package wp-frame-lite
 */

?>

<section class="no-results not-found">
	<header class="page-header">
		<h1 class="page-title">Ничего не найдено</h1>
	</header><!-- .page-header -->

	<div class="page-content">
		<?php if ( is_home() && current_user_can( 'publish_posts' ) ) : ?>
			<p>
				Готовы опубликовать первый пост?
				<a href="<?php echo esc_url( admin_url( 'post-new.php' ) ); ?>">Начните здесь</a>.
			</p>
		<?php else : ?>
			<p>Здесь пока ничего нет.</p>
		<?php endif; ?>
	</div><!-- .page-content -->
</section><!-- .no-results -->
