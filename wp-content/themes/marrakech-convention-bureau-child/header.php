<?php
/**
 * Header template override (standard WP child-theme mechanism).
 *
 * Replaces Brikk's header markup with the MCB header while keeping every
 * required WordPress hook (wp_head, wp_body_open) so Brikk/Routiz scripts,
 * SEO plugins and Elementor keep working untouched.
 *
 * @package MCB
 */

defined( 'ABSPATH' ) || exit;
?><!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="profile" href="https://gmpg.org/xfn/11">
	<?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<a class="mcb-skip-link screen-reader-text" href="#mcb-content"><?php esc_html_e( 'Skip to content', 'mcb' ); ?></a>

<div id="page" class="mcb-site">

	<?php mcb_render_header(); ?>

	<main id="mcb-content" class="mcb-site__main">
	<?php
	/**
	 * Fires before the main content, inside <main>.
	 * Used for the inner-page hero + breadcrumbs (inc/hooks/layout.php).
	 */
	do_action( 'mcb/before_content' );
