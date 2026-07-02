<?php
/**
 * Footer template override — counterpart of header.php.
 *
 * @package MCB
 */

defined( 'ABSPATH' ) || exit;

	/**
	 * Fires after the main content, inside <main>.
	 */
	do_action( 'mcb/after_content' );
	?>
	</main><!-- #mcb-content -->

	<?php mcb_render_footer(); ?>

</div><!-- #page -->

<?php wp_footer(); ?>
</body>
</html>
