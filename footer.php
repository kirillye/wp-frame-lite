<?php
/**
 * The template for displaying the footer
 *
 * Contains the closing of the #content div and all content after.
 *
 * @link https://developer.wordpress.org/themes/basics/template-files/#template-partials
 *
 * @package wp-frame-lite
 */

?>

	<footer id="colophon" class="site-footer">
		<div class="container">
			<div class="footer-widgets">
				<div class="footer-col">
					<h3 class="footer-col__title"><?php bloginfo( 'name' ); ?></h3>
					<?php
					$footer_description = get_bloginfo( 'description', 'display' );
					if ( $footer_description ) :
						?>
						<p class="footer-col__desc"><?php echo esc_html( $footer_description ); ?></p>
					<?php endif; ?>
				</div><!-- .footer-col -->

				<div class="footer-col">
					<h3 class="footer-col__title"><?php esc_html_e( 'Навигация', 'wp-frame-lite' ); ?></h3>
					<?php
					wp_nav_menu(
						array(
							'theme_location' => 'menu-1',
							'menu_id'        => 'footer-menu',
							'depth'          => 1,
							'fallback_cb'    => false,
						)
					);
					?>
				</div><!-- .footer-col -->

				<div class="footer-col">
					<h3 class="footer-col__title"><?php esc_html_e( 'Контакты', 'wp-frame-lite' ); ?></h3>
					<p><?php esc_html_e( 'Есть вопросы? Свяжитесь с нами.', 'wp-frame-lite' ); ?></p>
				</div><!-- .footer-col -->
			</div><!-- .footer-widgets -->

			<div class="footer-bottom">
				<p class="site-info">
					&copy; <?php echo esc_html( date( 'Y' ) ); ?> <?php bloginfo( 'name' ); ?>. <?php esc_html_e( 'Все права защищены.', 'wp-frame-lite' ); ?>
				</p>
			</div><!-- .footer-bottom -->
		</div><!-- .container -->
	</footer><!-- #colophon -->
</div><!-- #page -->

<?php wp_footer(); ?>

</body>
</html>
