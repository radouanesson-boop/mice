<?php
/**
 * Document head + site header (design board 1A).
 *
 * @package VM
 */

defined( 'ABSPATH' ) || exit;
?><!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<a class="mcb-skip-link screen-reader-text" href="#vm-content"><?php esc_html_e( 'Skip to content', 'vm' ); ?></a>

<div id="page" class="mcb-site">

	<?php mcb_part( 'header/site-header' ); ?>

	<main id="vm-content" class="mcb-site__main">
	<?php
	/**
	 * Fires before the main content (inner page hero hooks here).
	 */
	do_action( 'vm/before_content' );
