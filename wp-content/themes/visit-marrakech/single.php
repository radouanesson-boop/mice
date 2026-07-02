<?php
/**
 * Single post (insights / journal article).
 *
 * @package VM
 */

defined( 'ABSPATH' ) || exit;

get_header();

while ( have_posts() ) :
	the_post();
	?>
	<div class="mcb-page-hero">
		<div class="mcb-container">
			<?php mcb_breadcrumbs(); ?>
			<h1 class="mcb-page-hero__title"><?php the_title(); ?></h1>
			<p class="mcb-page-hero__desc">
				<time datetime="<?php echo esc_attr( get_the_date( 'c' ) ); ?>"><?php echo esc_html( get_the_date() ); ?></time>
			</p>
		</div>
	</div>

	<article <?php post_class( 'mcb-section' ); ?>>
		<div class="mcb-container mcb-container--narrow mcb-prose">
			<?php if ( has_post_thumbnail() ) : ?>
				<figure class="mcb-prose__hero"><?php the_post_thumbnail( 'mcb-hero', array( 'fetchpriority' => 'high' ) ); ?></figure>
			<?php endif; ?>
			<?php the_content(); ?>
		</div>
	</article>
	<?php
endwhile;

get_footer();
