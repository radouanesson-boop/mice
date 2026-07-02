<?php
/**
 * Mega menu nav walker.
 *
 * Extends the default walker so the primary menu supports:
 *  - `mcb-mega` class on a top-level item → children render inside a
 *    full-width mega panel with column groups.
 *  - Description text under menu links (uses the native WP menu
 *    "Description" field, so editors control it — no code).
 *
 * Everything else falls through to core behaviour, which keeps the walker
 * compatible with Brikk's menu locations and future WP updates.
 *
 * @package MCB
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class MCB_Mega_Menu_Walker
 */
class MCB_Mega_Menu_Walker extends Walker_Nav_Menu {

	/**
	 * Whether the branch currently being walked is a mega menu.
	 *
	 * @var bool
	 */
	protected $in_mega = false;

	/**
	 * Open a sub-level.
	 *
	 * @param string   $output Output.
	 * @param int      $depth  Depth.
	 * @param stdClass $args   Args.
	 */
	public function start_lvl( &$output, $depth = 0, $args = null ) {
		$indent = str_repeat( "\t", $depth );

		if ( 0 === $depth && $this->in_mega ) {
			$output .= "\n{$indent}<div class=\"mcb-mega-panel\"><ul class=\"mcb-mega-panel__columns\">\n";
			return;
		}

		$output .= "\n{$indent}<ul class=\"mcb-nav__submenu\">\n";
	}

	/**
	 * Close a sub-level.
	 *
	 * @param string   $output Output.
	 * @param int      $depth  Depth.
	 * @param stdClass $args   Args.
	 */
	public function end_lvl( &$output, $depth = 0, $args = null ) {
		$indent = str_repeat( "\t", $depth );

		if ( 0 === $depth && $this->in_mega ) {
			$output .= "{$indent}</ul></div>\n";
			return;
		}

		$output .= "{$indent}</ul>\n";
	}

	/**
	 * Render a menu item.
	 *
	 * @param string   $output Output.
	 * @param WP_Post  $item   Menu item.
	 * @param int      $depth  Depth.
	 * @param stdClass $args   Args.
	 * @param int      $id     Item ID.
	 */
	public function start_el( &$output, $item, $depth = 0, $args = null, $id = 0 ) {
		$classes = empty( $item->classes ) ? array() : (array) $item->classes;

		if ( 0 === $depth ) {
			$this->in_mega = in_array( 'mcb-mega', $classes, true );
		}

		$has_children = in_array( 'menu-item-has-children', $classes, true );

		$li_classes = array( 'mcb-nav__item' );

		if ( $has_children ) {
			$li_classes[] = 0 === $depth && $this->in_mega ? 'mcb-nav__item--mega' : 'mcb-nav__item--parent';
		}

		if ( in_array( 'current-menu-item', $classes, true ) || in_array( 'current-menu-ancestor', $classes, true ) ) {
			$li_classes[] = 'is-current';
		}

		// Mega panel column heads (2nd level inside a mega branch).
		if ( 1 === $depth && $this->in_mega ) {
			$li_classes[] = 'mcb-mega-panel__column';
		}

		$output .= '<li class="' . esc_attr( implode( ' ', $li_classes ) ) . '">';

		$atts = array(
			'href'          => ! empty( $item->url ) ? $item->url : '#',
			'class'         => 0 === $depth ? 'mcb-nav__link' : 'mcb-nav__sublink',
			'aria-current'  => in_array( 'current-menu-item', $classes, true ) ? 'page' : '',
			'aria-expanded' => ( $has_children && $depth < 2 ) ? 'false' : '',
			'aria-haspopup' => ( $has_children && $depth < 2 ) ? 'true' : '',
		);

		$attributes = '';
		foreach ( $atts as $attr => $value ) {
			if ( '' !== $value ) {
				$attributes .= ' ' . $attr . '="' . esc_attr( $value ) . '"';
			}
		}

		$title = apply_filters( 'the_title', $item->title, $item->ID );

		$output .= '<a' . $attributes . '><span class="mcb-nav__label">' . esc_html( $title ) . '</span>';

		if ( ! empty( $item->description ) && $depth > 0 ) {
			$output .= '<span class="mcb-nav__desc">' . esc_html( $item->description ) . '</span>';
		}

		if ( $has_children && 0 === $depth ) {
			$output .= mcb_icon( 'chevron-down', array( 'echo' => false, 'class' => 'mcb-nav__caret' ) );
		}

		$output .= '</a>';
	}
}
