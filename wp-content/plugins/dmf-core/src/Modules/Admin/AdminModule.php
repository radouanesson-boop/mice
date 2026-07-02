<?php
/**
 * Admin module — menu, settings, listing type manager, status.
 *
 * @package DMF
 */

namespace DMF\Modules\Admin;

use DMF\Modules\AbstractModule;
use DMF\Modules\Listings\ListingType;
use DMF\Support\Options;

defined( 'ABSPATH' ) || exit;

/**
 * One top-level "Destination" menu gathering the whole framework:
 * dashboard, every listing type, inquiries, listing types manager,
 * settings, status.
 */
final class AdminModule extends AbstractModule {

	public function register(): void {
		add_action( 'admin_menu', array( $this, 'menu' ) );
		add_action( 'admin_init', array( $this, 'settings' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'assets' ) );
		add_action( 'admin_post_dmf_save_type', array( $this, 'handle_save_type' ) );
	}

	public function menu(): void {
		add_menu_page(
			__( 'Destination', 'dmf' ),
			__( 'Destination', 'dmf' ),
			'manage_options',
			'dmf',
			array( $this, 'render_dashboard' ),
			'dashicons-palmtree',
			3
		);

		add_submenu_page( 'dmf', __( 'Dashboard', 'dmf' ), __( 'Dashboard', 'dmf' ), 'manage_options', 'dmf', array( $this, 'render_dashboard' ) );
		add_submenu_page( 'dmf', __( 'Listing Types', 'dmf' ), __( 'Listing Types', 'dmf' ), 'manage_options', 'dmf-types', array( $this, 'render_types' ) );
		add_submenu_page( 'dmf', __( 'Settings', 'dmf' ), __( 'Settings', 'dmf' ), 'manage_options', 'dmf-settings', array( $this, 'render_settings' ) );

		// Surface each listing type under the Destination menu.
		foreach ( dmf()->module( 'listings' )->registry()->all() as $type ) {
			add_submenu_page(
				'dmf',
				$type->plural,
				$type->plural,
				'edit_dmf_listings',
				'edit.php?post_type=' . $type->slug
			);
		}
	}

	public function assets( string $hook ): void {
		if ( str_contains( $hook, 'dmf' ) ) {
			wp_enqueue_style( 'dmf-admin', DMF_URL . 'assets/admin/admin.css', array(), DMF_VERSION );
		}
	}

	// -----------------------------------------------------------------
	// Settings API
	// -----------------------------------------------------------------

	public function settings(): void {
		register_setting(
			'dmf_settings',
			Options::OPTION,
			array(
				'type'              => 'array',
				'sanitize_callback' => array( $this, 'sanitize_settings' ),
			)
		);

		add_settings_section( 'dmf_maps', __( 'Maps', 'dmf' ), '__return_empty_string', 'dmf-settings' );
		add_settings_section( 'dmf_forms', __( 'Inquiries', 'dmf' ), '__return_empty_string', 'dmf-settings' );

		$fields = array(
			array( 'maps.provider', __( 'Map provider', 'dmf' ), 'dmf_maps', 'select', array( 'osm' => 'OpenStreetMap (Leaflet)', 'google' => 'Google Maps', 'mapbox' => 'Mapbox' ) ),
			array( 'maps.api_key', __( 'Map API key', 'dmf' ), 'dmf_maps', 'text', array() ),
			array( 'maps.center_lat', __( 'Default center latitude', 'dmf' ), 'dmf_maps', 'text', array() ),
			array( 'maps.center_lng', __( 'Default center longitude', 'dmf' ), 'dmf_maps', 'text', array() ),
			array( 'forms.recipient', __( 'Inquiry recipient email', 'dmf' ), 'dmf_forms', 'email', array() ),
		);

		foreach ( $fields as [ $key, $label, $section, $type, $choices ] ) {
			add_settings_field(
				'dmf_' . $key,
				$label,
				function () use ( $key, $type, $choices ) {
					$value = Options::get( $key, '' );
					$name  = esc_attr( Options::OPTION . '[' . $key . ']' );

					if ( 'select' === $type ) {
						echo '<select name="' . $name . '">'; // phpcs:ignore WordPress.Security.EscapeOutput
						foreach ( $choices as $choice => $label_choice ) {
							printf( '<option value="%s" %s>%s</option>', esc_attr( $choice ), selected( $value, $choice, false ), esc_html( $label_choice ) );
						}
						echo '</select>';
					} else {
						printf(
							'<input type="%s" class="regular-text" name="%s" value="%s">',
							esc_attr( $type ),
							$name, // phpcs:ignore WordPress.Security.EscapeOutput
							esc_attr( (string) $value )
						);
					}
				},
				'dmf-settings',
				$section
			);
		}
	}

	public function sanitize_settings( $input ): array {
		$clean = array();

		foreach ( (array) $input as $key => $value ) {
			$key = sanitize_text_field( (string) $key );

			$clean[ $key ] = match ( $key ) {
				'forms.recipient' => sanitize_email( (string) $value ),
				'maps.provider'   => in_array( $value, array( 'osm', 'google', 'mapbox' ), true ) ? $value : 'osm',
				default           => sanitize_text_field( (string) $value ),
			};
		}

		// Merge so unrelated keys (written via Options::set) survive.
		return array_merge( Options::all(), $clean );
	}

	// -----------------------------------------------------------------
	// Screens
	// -----------------------------------------------------------------

	public function render_dashboard(): void {
		$registry = dmf()->module( 'listings' )->registry();
		?>
		<div class="wrap dmf-admin">
			<h1><?php esc_html_e( 'Destination Management Framework', 'dmf' ); ?></h1>
			<p class="description">
				<?php
				printf(
					/* translators: %s: DMF version. */
					esc_html__( 'DMF Core %s — modular destination platform.', 'dmf' ),
					esc_html( DMF_VERSION )
				);
				?>
			</p>

			<div class="dmf-cards">
				<?php foreach ( $registry->all() as $type ) : ?>
					<?php $count = (int) ( wp_count_posts( $type->slug )->publish ?? 0 ); ?>
					<a class="dmf-card-stat" href="<?php echo esc_url( admin_url( 'edit.php?post_type=' . $type->slug ) ); ?>">
						<span class="dmf-card-stat__value"><?php echo esc_html( number_format_i18n( $count ) ); ?></span>
						<span class="dmf-card-stat__label"><?php echo esc_html( $type->plural ); ?></span>
					</a>
				<?php endforeach; ?>
			</div>
		</div>
		<?php
	}

	public function render_settings(): void {
		?>
		<div class="wrap dmf-admin">
			<h1><?php esc_html_e( 'DMF Settings', 'dmf' ); ?></h1>
			<form method="post" action="options.php">
				<?php
				settings_fields( 'dmf_settings' );
				do_settings_sections( 'dmf-settings' );
				submit_button();
				?>
			</form>
		</div>
		<?php
	}

	/**
	 * No-code listing type manager: list + create/edit form.
	 */
	public function render_types(): void {
		$registry = dmf()->module( 'listings' )->registry();
		$editing  = isset( $_GET['type'] ) ? $registry->get( sanitize_key( $_GET['type'] ) ) : null; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only view selection.
		?>
		<div class="wrap dmf-admin">
			<h1><?php esc_html_e( 'Listing Types', 'dmf' ); ?></h1>
			<p class="description"><?php esc_html_e( 'Create new listing types without code. Types defined in configuration or code are shown read-only here and can be overridden.', 'dmf' ); ?></p>

			<table class="widefat striped" style="max-width:760px;margin-top:16px">
				<thead>
					<tr>
						<th><?php esc_html_e( 'Type', 'dmf' ); ?></th>
						<th><?php esc_html_e( 'Slug', 'dmf' ); ?></th>
						<th><?php esc_html_e( 'Kind', 'dmf' ); ?></th>
						<th><?php esc_html_e( 'URL base', 'dmf' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ( $registry->all() as $type ) : ?>
						<tr>
							<td><a href="<?php echo esc_url( add_query_arg( 'type', $type->slug ) ); ?>"><?php echo esc_html( $type->plural ); ?></a></td>
							<td><code><?php echo esc_html( $type->slug ); ?></code></td>
							<td><?php echo esc_html( $type->kind ); ?></td>
							<td>/<?php echo esc_html( $type->rewrite ); ?>/</td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>

			<h2 style="margin-top:32px">
				<?php $editing ? esc_html_e( 'Edit listing type', 'dmf' ) : esc_html_e( 'Add listing type', 'dmf' ); ?>
			</h2>

			<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="max-width:760px">
				<?php wp_nonce_field( 'dmf_save_type', 'dmf_type_nonce' ); ?>
				<input type="hidden" name="action" value="dmf_save_type">

				<table class="form-table" role="presentation">
					<tr>
						<th><label for="dmf-slug"><?php esc_html_e( 'Slug', 'dmf' ); ?></label></th>
						<td>
							<input type="text" class="regular-text" id="dmf-slug" name="slug" required
								pattern="dmf_[a-z0-9_]{1,16}" placeholder="dmf_golf"
								value="<?php echo esc_attr( $editing->slug ?? '' ); ?>" <?php echo $editing ? 'readonly' : ''; ?>>
							<p class="description"><?php esc_html_e( 'Must start with dmf_ — max 20 characters.', 'dmf' ); ?></p>
						</td>
					</tr>
					<tr>
						<th><label for="dmf-singular"><?php esc_html_e( 'Singular label', 'dmf' ); ?></label></th>
						<td><input type="text" class="regular-text" id="dmf-singular" name="singular" required value="<?php echo esc_attr( $editing->singular ?? '' ); ?>"></td>
					</tr>
					<tr>
						<th><label for="dmf-plural"><?php esc_html_e( 'Plural label', 'dmf' ); ?></label></th>
						<td><input type="text" class="regular-text" id="dmf-plural" name="plural" required value="<?php echo esc_attr( $editing->plural ?? '' ); ?>"></td>
					</tr>
					<tr>
						<th><label for="dmf-rewrite"><?php esc_html_e( 'URL base', 'dmf' ); ?></label></th>
						<td><input type="text" class="regular-text" id="dmf-rewrite" name="rewrite" value="<?php echo esc_attr( $editing->rewrite ?? '' ); ?>" placeholder="golf-courses"></td>
					</tr>
					<tr>
						<th><label for="dmf-kind"><?php esc_html_e( 'Card / schema kind', 'dmf' ); ?></label></th>
						<td>
							<select id="dmf-kind" name="kind">
								<?php foreach ( array( 'generic', 'venue', 'hotel', 'restaurant', 'experience', 'event', 'supplier' ) as $kind ) : ?>
									<option value="<?php echo esc_attr( $kind ); ?>" <?php selected( $editing->kind ?? 'generic', $kind ); ?>><?php echo esc_html( $kind ); ?></option>
								<?php endforeach; ?>
							</select>
						</td>
					</tr>
					<tr>
						<th><label for="dmf-fields"><?php esc_html_e( 'Fields (JSON)', 'dmf' ); ?></label></th>
						<td>
							<textarea class="large-text code" rows="10" id="dmf-fields" name="fields"><?php echo esc_textarea( $editing ? wp_json_encode( $editing->fields, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES ) : '' ); ?></textarea>
							<p class="description"><?php esc_html_e( 'Field groups as JSON — see Destination → Listing Types docs for the schema. Leave empty for the kind defaults.', 'dmf' ); ?></p>
						</td>
					</tr>
				</table>

				<?php submit_button( $editing ? __( 'Update type', 'dmf' ) : __( 'Create type', 'dmf' ) ); ?>
			</form>
		</div>
		<?php
	}

	/**
	 * Persist a type from the manager form.
	 */
	public function handle_save_type(): void {
		if (
			! isset( $_POST['dmf_type_nonce'] )
			|| ! wp_verify_nonce( sanitize_key( $_POST['dmf_type_nonce'] ), 'dmf_save_type' )
			|| ! current_user_can( 'manage_options' )
		) {
			wp_die( esc_html__( 'Not allowed.', 'dmf' ) );
		}

		$fields_json = wp_unslash( $_POST['fields'] ?? '' ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput -- JSON decoded + re-validated below.
		$fields      = json_decode( (string) $fields_json, true );

		$type = ListingType::from_array(
			array(
				'slug'     => sanitize_key( wp_unslash( $_POST['slug'] ?? '' ) ),
				'singular' => sanitize_text_field( wp_unslash( $_POST['singular'] ?? '' ) ),
				'plural'   => sanitize_text_field( wp_unslash( $_POST['plural'] ?? '' ) ),
				'rewrite'  => sanitize_title( wp_unslash( $_POST['rewrite'] ?? '' ) ),
				'kind'     => sanitize_key( wp_unslash( $_POST['kind'] ?? 'generic' ) ),
				'fields'   => is_array( $fields ) ? $fields : array(),
			)
		);

		if ( '' !== $type->slug && str_starts_with( $type->slug, 'dmf_' ) ) {
			dmf()->module( 'listings' )->registry()->save( $type );
			flush_rewrite_rules();
		}

		wp_safe_redirect( admin_url( 'admin.php?page=dmf-types&saved=1' ) );
		exit;
	}
}
