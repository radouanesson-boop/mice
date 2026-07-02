<?php
/**
 * Section heading: kicker + title (+ optional intro), centered or left.
 *
 * @package MCB
 */

defined( 'ABSPATH' ) || exit;

$args = wp_parse_args(
	$args ?? array(),
	array(
		'kicker' => '',
		'title'  => '',
		'tag'    => 'h2',
		'intro'  => '',
		'align'  => 'center',
	)
);

if ( ! $args['title'] ) {
	return;
}

$mcb_tag = in_array( $args['tag'], array( 'h1', 'h2', 'h3', 'h4' ), true ) ? $args['tag'] : 'h2';
?>
<div class="<?php echo mcb_bem( 'mcb-section-heading', array( $args['align'] ) ); ?>">
	<?php if ( $args['kicker'] ) : ?>
		<p class="mcb-section-heading__kicker"><?php echo esc_html( $args['kicker'] ); ?></p>
	<?php endif; ?>

	<<?php echo $mcb_tag; // phpcs:ignore ?> class="mcb-section-heading__title"><?php echo esc_html( $args['title'] ); ?></<?php echo $mcb_tag; // phpcs:ignore ?>>

	<?php if ( $args['intro'] ) : ?>
		<p class="mcb-section-heading__intro"><?php echo esc_html( $args['intro'] ); ?></p>
	<?php endif; ?>
</div>
