<?php
/**
 * The header for our theme
 *
 * @package wp-frame-lite
 */

?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="profile" href="https://gmpg.org/xfn/11">

	<?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<div id="page" class="site">
	<a class="skip-link screen-reader-text" href="#primary"><?php esc_html_e( 'Skip to content', 'wp-frame-lite' ); ?></a>

	<header id="masthead" class="site-header">
		<div class="container">
			<div class="header-inner">
				<div class="site-branding">
					<?php
					the_custom_logo();
					if ( is_front_page() && is_home() ) :
						?>
						<h1 class="site-title"><a href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home"><?php bloginfo( 'name' ); ?></a></h1>
						<?php
					else :
						?>
						<p class="site-title"><a href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home"><?php bloginfo( 'name' ); ?></a></p>
						<?php
					endif;
					$wp_frame_lite_description = get_bloginfo( 'description', 'display' );
					if ( $wp_frame_lite_description || is_customize_preview() ) :
						?>
						<p class="site-description"><?php echo $wp_frame_lite_description; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></p>
					<?php endif; ?>
				</div><!-- .site-branding -->

				<!-- Десктопная навигация (скрыта на мобильных) -->
				<nav id="site-navigation" class="main-navigation" aria-label="<?php esc_attr_e( 'Основная навигация', 'wp-frame-lite' ); ?>">
					<?php
					wp_nav_menu(
						array(
							'theme_location' => 'menu-1',
							'menu_id'        => 'primary-menu',
						)
					);
					?>
				</nav><!-- #site-navigation -->

				<!-- Кнопка бургера (только на мобильных) -->
				<button class="menu-toggle" aria-controls="mobile-sidebar" aria-expanded="false" aria-label="<?php esc_attr_e( 'Открыть меню', 'wp-frame-lite' ); ?>">
					<span class="burger-line"></span>
					<span class="burger-line"></span>
					<span class="burger-line"></span>
				</button>
			</div><!-- .header-inner -->
		</div><!-- .container -->
	</header><!-- #masthead -->

	<!-- Мобильный сайдбар — вне header, имеет собственный z-index выше overlay -->
	<aside id="mobile-sidebar" class="mobile-sidebar" aria-hidden="true" aria-label="<?php esc_attr_e( 'Мобильное меню', 'wp-frame-lite' ); ?>">
		<div class="mobile-sidebar__head">
			<span class="mobile-sidebar__brand">
				<a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php bloginfo( 'name' ); ?></a>
			</span>
			<button class="nav-close" aria-label="<?php esc_attr_e( 'Закрыть меню', 'wp-frame-lite' ); ?>">
				<span aria-hidden="true">&times;</span>
			</button>
		</div><!-- .mobile-sidebar__head -->

		<nav class="mobile-sidebar__nav" aria-label="<?php esc_attr_e( 'Мобильная навигация', 'wp-frame-lite' ); ?>">
			<?php
			wp_nav_menu(
				array(
					'theme_location' => 'menu-1',
					'menu_id'        => 'mobile-menu',
					'container'      => false,
				)
			);
			?>
		</nav><!-- .mobile-sidebar__nav -->

		<!-- Здесь можно добавить телефон, соц. сети и другие данные -->
		<div class="mobile-sidebar__footer">
		</div><!-- .mobile-sidebar__footer -->
	</aside><!-- #mobile-sidebar -->
