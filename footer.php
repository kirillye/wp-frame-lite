<?php
/**
 * Footer template.
 *
 * @package wp-frame-lite
 */

$footer_description = get_bloginfo( 'description', 'display' );
$footer_phones      = wp_frame_lite_rows( 'contact_phones' );
$footer_socials     = wp_frame_lite_rows( 'social_links' );
$footer_email       = wp_frame_lite_option( 'contact_email' );
$footer_schedule    = wp_frame_lite_option( 'contact_schedule' );
$footer_address     = wp_frame_lite_option( 'contact_address' );
$footer_map_url     = wp_frame_lite_option( 'contact_map_url' );
$footer_copyright   = wp_frame_lite_option( 'footer_copyright' );
$footer_legal       = wp_frame_lite_option( 'footer_legal' );

$has_contacts = $footer_phones || $footer_email || $footer_schedule || $footer_address;
?>

	<footer id="colophon" class="site-footer">
		<div class="container">
			<div class="footer-widgets">
				<div class="footer-col">
					<h3 class="footer-col__title"><?php echo esc_html( get_bloginfo( 'name' ) ); ?></h3>

					<?php if ( $footer_description ) : ?>
						<p class="footer-col__desc"><?php echo esc_html( $footer_description ); ?></p>
					<?php endif; ?>

					<?php if ( $footer_socials ) : ?>
						<ul class="footer-socials">
							<?php foreach ( $footer_socials as $social ) : ?>
								<?php if ( empty( $social['social_url'] ) ) : ?>
									<?php continue; ?>
								<?php endif; ?>
								<li>
									<a href="<?php echo esc_url( $social['social_url'] ); ?>" target="_blank" rel="noopener nofollow">
										<?php echo esc_html( $social['social_title'] ?? '' ); ?>
									</a>
								</li>
							<?php endforeach; ?>
						</ul>
					<?php endif; ?>
				</div>

				<div class="footer-col">
					<h3 class="footer-col__title">Навигация</h3>
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
				</div>

				<?php if ( $has_contacts ) : ?>
					<div class="footer-col">
						<h3 class="footer-col__title">Контакты</h3>

						<ul class="footer-contacts">
							<?php foreach ( $footer_phones as $phone ) : ?>
								<?php if ( empty( $phone['phone_number'] ) ) : ?>
									<?php continue; ?>
								<?php endif; ?>
								<li>
									<?php if ( ! empty( $phone['phone_label'] ) ) : ?>
										<span class="footer-contacts__label"><?php echo esc_html( $phone['phone_label'] ); ?></span>
									<?php endif; ?>
									<a href="tel:<?php echo esc_attr( wp_frame_lite_tel( $phone['phone_number'] ) ); ?>">
										<?php echo esc_html( $phone['phone_number'] ); ?>
									</a>
								</li>
							<?php endforeach; ?>

							<?php if ( $footer_email ) : ?>
								<li>
									<a href="mailto:<?php echo esc_attr( $footer_email ); ?>"><?php echo esc_html( $footer_email ); ?></a>
								</li>
							<?php endif; ?>

							<?php if ( $footer_address ) : ?>
								<li>
									<?php if ( $footer_map_url ) : ?>
										<a href="<?php echo esc_url( $footer_map_url ); ?>" target="_blank" rel="noopener nofollow">
											<?php echo wp_kses_post( $footer_address ); ?>
										</a>
									<?php else : ?>
										<address><?php echo wp_kses_post( $footer_address ); ?></address>
									<?php endif; ?>
								</li>
							<?php endif; ?>

							<?php if ( $footer_schedule ) : ?>
								<li><?php echo esc_html( $footer_schedule ); ?></li>
							<?php endif; ?>
						</ul>
					</div>
				<?php endif; ?>
			</div>

			<div class="footer-bottom">
				<p class="site-info">
					<?php
					if ( $footer_copyright ) {
						echo esc_html( $footer_copyright );
					} else {
						printf(
							'&copy; %1$s %2$s. Все права защищены.',
							esc_html( wp_date( 'Y' ) ),
							esc_html( get_bloginfo( 'name' ) )
						);
					}
					?>
				</p>

				<?php if ( $footer_legal ) : ?>
					<p class="site-legal"><?php echo wp_kses_post( $footer_legal ); ?></p>
				<?php endif; ?>
			</div>
		</div>
	</footer>
</div>

<?php wp_footer(); ?>

</body>
</html>
