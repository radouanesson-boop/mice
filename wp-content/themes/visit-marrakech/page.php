<?php
/**
 * Static page.
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
		</div>
	</div>

	<article <?php post_class( 'mcb-section' ); ?>>
		<div class="mcb-container mcb-container--narrow mcb-prose">
			<?php the_content(); ?>
		</div>
	</article>
	<?php
endwhile;

get_footer();
