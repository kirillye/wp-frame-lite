<?php
/**
 * Шаблон главной страницы.
 *
 * Используется в обоих случаях: и когда на главной статическая страница,
 * и когда лента записей. Содержимое hero редактируется в ACF на самой странице;
 * если главная — лента записей, hero берёт данные из настроек WordPress.
 *
 * @package wp-frame-lite
 */

get_header();

$front_id = is_home() ? 0 : (int) get_queried_object_id();

$hero_show     = true;
$hero_title    = get_bloginfo( 'name' );
$hero_subtitle = get_bloginfo( 'description', 'display' );
$hero_button   = array();
$hero_image    = array();

if ( $front_id ) {
	$hero_show     = wp_frame_lite_flag( 'hero_show', true, $front_id );
	$hero_title    = wp_frame_lite_field( 'hero_title', $hero_title, $front_id );
	$hero_subtitle = wp_frame_lite_field( 'hero_subtitle', $hero_subtitle, $front_id );
	$hero_button   = wp_frame_lite_field( 'hero_button', array(), $front_id );
	$hero_image    = wp_frame_lite_field( 'hero_image', array(), $front_id );
}

$hero_image_url = is_array( $hero_image ) ? ( $hero_image['url'] ?? '' ) : '';
?>

<?php if ( $hero_show ) : ?>
	<section class="hero<?php echo $hero_image_url ? ' hero--has-image' : ''; ?>"
		<?php if ( $hero_image_url ) : ?>
			style="background-image: url('<?php echo esc_url( $hero_image_url ); ?>');"
		<?php endif; ?>
	>
		<div class="container">
			<div class="hero-content">
				<h1 class="hero-title"><?php echo esc_html( $hero_title ); ?></h1>

				<?php if ( $hero_subtitle ) : ?>
					<p class="hero-subtitle"><?php echo esc_html( $hero_subtitle ); ?></p>
				<?php endif; ?>

				<?php if ( ! empty( $hero_button['url'] ) ) : ?>
					<a
						class="btn"
						href="<?php echo esc_url( $hero_button['url'] ); ?>"
						<?php if ( ! empty( $hero_button['target'] ) ) : ?>
							target="<?php echo esc_attr( $hero_button['target'] ); ?>" rel="noopener"
						<?php endif; ?>
					>
						<?php echo esc_html( ! empty( $hero_button['title'] ) ? $hero_button['title'] : 'Подробнее' ); ?>
					</a>
				<?php endif; ?>
			</div>
		</div>
	</section>
<?php endif; ?>

	<main id="primary" class="site-main site-content">
		<div class="container">

			<?php
			if ( is_home() ) :

				if ( have_posts() ) :

					while ( have_posts() ) :
						the_post();

						get_template_part( 'template-parts/content', get_post_type() );

					endwhile;

					the_posts_navigation();

				else :

					get_template_part( 'template-parts/content', 'none' );

				endif;

			else :

				while ( have_posts() ) :
					the_post();

					// Заголовок уже выведен в hero — здесь только контент страницы.
					the_content();

				endwhile;

			endif;
			?>

		</div><!-- .container -->
	</main><!-- #main -->

<?php
get_footer();
