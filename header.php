<?php
/**
 * Header template.
 *
 * @package wp-frame-lite
 */

?>
<!doctype html>
<html <?php language_attributes(); ?>>

<head>
	<meta charset="<?php echo esc_attr( get_bloginfo( 'charset' ) ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="profile" href="https://gmpg.org/xfn/11">

	<?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
	<?php wp_body_open(); ?>
	<div id="page" class="site">
		<a class="skip-link screen-reader-text" href="#primary">Перейти к содержимому</a>

		<header id="masthead" class="site-header">
			<div class="container">
				<div class="header-inner">
					<div class="site-branding">
						<?php
						the_custom_logo();

						$site_name = get_bloginfo( 'name' );
						$tag_name  = is_front_page() && is_home() ? 'h1' : 'p';
						?>

						<<?php echo esc_html( $tag_name ); ?> class="site-title">
							<a href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home"><?php echo esc_html( $site_name ); ?></a>
						</<?php echo esc_html( $tag_name ); ?>>

						<?php
						$site_description = get_bloginfo( 'description', 'display' );

						if ( $site_description || is_customize_preview() ) :
							?>
							<p class="site-description"><?php echo esc_html( $site_description ); ?></p>
						<?php endif; ?>
					</div>

					<nav id="site-navigation" class="main-navigation" aria-label="Основная навигация">
						<?php
						wp_nav_menu(
							array(
								'theme_location' => 'menu-1',
								'menu_id'        => 'primary-menu',
							)
						);
						?>
					</nav>

					<button class="menu-toggle" aria-controls="mobile-sidebar" aria-expanded="false" aria-label="Открыть меню">
						<span class="burger-line"></span>
						<span class="burger-line"></span>
						<span class="burger-line"></span>
					</button>
				</div>
			</div>
		</header>

		<aside id="mobile-sidebar" class="mobile-sidebar" aria-hidden="true" aria-label="Мобильное меню">
			<div class="mobile-sidebar__head">
				<span class="mobile-sidebar__brand">
					<a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php echo esc_html( $site_name ); ?></a>
				</span>
				<button class="nav-close" aria-label="Закрыть меню">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>

			<nav class="mobile-sidebar__nav" aria-label="Мобильная навигация">
				<?php
				wp_nav_menu(
					array(
						'theme_location' => 'menu-1',
						'menu_id'        => 'mobile-menu',
						'container'      => false,
					)
				);
				?>
			</nav>

			<div class="mobile-sidebar__footer"></div>
		</aside>
