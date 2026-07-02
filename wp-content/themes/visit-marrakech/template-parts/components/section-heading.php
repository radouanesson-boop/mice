<?php
/**
 * Section heading — design board 1A: eyebrow with 26px dash + light display
 * title, optionally an intro and a "View all →" link on the baseline.
 *
 * @package MCB
 */

defined( 'ABSPATH' ) || exit;

$args = wp_parse_args(
	$args ?? array(),
	array(
		'kicker'    => '',
		'title'     => '',
		'tag'       => 'h2',
		'intro'     => '',
		'align'     => 'left',
		'link_text' => '',
		'link_url'  => '',
	)
);

if ( ! $args['title'] ) {
	return;
}

$mcb_tag = in_array( $args['tag'], array( 'h1', 'h2', 'h3', 'h4' ), true ) ? $args['tag'] : 'h2';
?>
<div class="<?php echo mcb_bem( 'mcb-section-heading', array( 'center' === $args['align'] ? 'center' : null ) ); ?>">
	<div class="mcb-section-heading__main">
		<?php if ( $args['kicker'] ) : ?>
			<p class="mcb-eyebrow"><?php echo esc_html( $args['kicker'] ); ?></p>
		<?php endif; ?>

		<<?php echo $mcb_tag; // phpcs:ignore ?> class="mcb-section-heading__title"><?php echo esc_html( $args['title'] ); ?></<?php echo $mcb_tag; // phpcs:ignore ?>>

		<?php if ( $args['intro'] ) : ?>
			<p class="mcb-section-heading__intro"><?php echo esc_html( $args['intro'] ); ?></p>
		<?php endif; ?>
	</div>

	<?php if ( $args['link_text'] && $args['link_url'] ) : ?>
		<a class="mcb-section-heading__link" href="<?php echo esc_url( $args['link_url'] ); ?>">
			<?php echo esc_html( $args['link_text'] ); ?>
			<?php mcb_icon( 'arrow-right' ); ?>
		</a>
	<?php endif; ?>
</div>
