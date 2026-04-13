<?php

class StmZoomAdminMenus {

	/**
	 * @return StmZoomAdminMenus constructor.
	 */
	public function __construct() {
		add_action( 'admin_menu',
			function () {
				$menu_capability = PostTypeCapabilities::get_required_capability();
				add_menu_page( 'eRoom', 'eRoom', $menu_capability, 'stm_zoom', 'admin_pages', 'dashicons-video-alt2', 40 );
				self::admin_submenu_pages();
			},
			100
		);

		// Register our custom pricing/upgrade page early so the hook exists
		add_action( 'admin_menu', function () {
			add_submenu_page(
				'', // hidden - Freemius already adds the visible "Upgrade" link
				esc_html__( 'Upgrade to eRoom PRO', 'eroom-zoom-meetings-webinar' ),
				esc_html__( 'Upgrade', 'eroom-zoom-meetings-webinar' ),
				'manage_options',
				'stm_zoom_upgrade',
				'stm_zoom_upgrade_page'
			);
		}, 101 );

		// Tell Freemius to point the "Upgrade" menu link to our custom page
		add_filter( 'fs_pricing_url_eroom-zoom-meetings-webinar', function () {
			return admin_url( 'admin.php?page=stm_zoom_upgrade' );
		} );

		// Catch anyone navigating directly to the old Freemius pricing URL
		add_action( 'admin_init', function () {
			if ( isset( $_GET['page'] ) && $_GET['page'] === 'stm_zoom-pricing' ) { // phpcs:ignore WordPress.Security.NonceVerification
				wp_safe_redirect( admin_url( 'admin.php?page=stm_zoom_upgrade' ) );
				exit;
			}
		} );

		if ( is_admin() ) {
			self::admin_settings_page();
			add_filter(
				'stm_wpcfto_autocomplete_stm_alternative_hosts',
				array(
					$this,
					'get_autocomplete_users_options',
				),
				100
			);
		}

		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue' ), 100 );

		add_action( 'admin_head', array( $this, 'admin_head' ) );
		add_action( 'admin_footer', array( $this, 'override_contact_link' ) );

		add_filter( 'plugin_action_links_' . plugin_basename( STM_ZOOM_FILE ), array( $this, 'plugin_action_links' ) );

		add_action( 'admin_head-edit.php', array( $this, 'admin_meetings_webinars_scripts' ) );
		add_action( 'wpcfto_settings_saved', array( $this, 'clear_zoom_cache_on_credentials_change' ), 10, 2 );
		add_action( 'admin_notices', array( $this, 'show_invalid_credentials_notice' ) );
	}

	/**
	 * Get Users for Autocomplete
	 *
	 * @return array
	 */
	public static function get_autocomplete_users_options() {
		$users  = StmZoom::get_users_options();
		$result = array();
		foreach ( $users as $id => $user ) {
			$result[] = array(
				'id'        => $id,
				'title'     => $user,
				'post_type' => '',
			);
		}

		return $result;
	}

	/**
	 * Show a persistent upgrade notice at the top of eRoom admin pages
	 */
	public function show_upgrade_to_pro_notice() {
		// Don't show if PRO is active
		if ( defined( 'STM_ZOOM_PRO_PATH' ) ) {
			return;
		}
		?>
		<div class="notice notice-info eroom-upgrade-notice" style="display:flex;align-items:center;gap:12px;padding:12px 16px;border-left-color:#157ffc;">
			<img src="<?php echo esc_url( STM_ZOOM_URL . 'assets/images/zoom_icon.png' ); ?>" width="32" height="32" alt="eRoom" style="flex-shrink:0;">
			<p style="margin:0;font-size:14px;">
				<strong><?php esc_html_e( '🔓 Unlock Premium Features with eRoom PRO!', 'eroom-zoom-meetings-webinar' ); ?></strong>
				<?php esc_html_e( 'Get Purchasable Meetings, Recurring Meetings, Google Meet, Microsoft Teams integration, and more.', 'eroom-zoom-meetings-webinar' ); ?>
				&nbsp;<a href="https://eroomwp.com/" target="_blank" style="color:#157ffc;font-weight:600;text-decoration:none;">
					<?php esc_html_e( '→ Get eRoom PRO at eroomwp.com', 'eroom-zoom-meetings-webinar' ); ?>
				</a>
			</p>
		</div>
		<?php
	}

	/**
	 * Creating Submenu Pages under Zoom menu
	 */
	public static function admin_submenu_pages() {
		// Get required capability for menu access
		$menu_capability = PostTypeCapabilities::get_required_capability();

		$pages = array(
			array(
				'slug'      => 'stm_zoom_users',
				'menu_slug' => 'stm_zoom_users',
				'label'     => esc_html__( 'Users', 'eroom-zoom-meetings-webinar' ),
			),
			array(
				'slug'      => 'stm_zoom_add_user',
				'menu_slug' => 'stm_zoom_add_user',
				'label'     => esc_html__( 'Add user', 'eroom-zoom-meetings-webinar' ),
				'hidden'    => true,
			),
			array(
				'slug'      => 'stm_zoom_reports',
				'menu_slug' => 'stm_zoom_reports',
				'label'     => esc_html__( 'Statistics', 'eroom-zoom-meetings-webinar' ),
			),
			array(
				'slug'      => 'stm_zoom_assign_host_id',
				'menu_slug' => 'stm_zoom_assign_host_id',
				'label'     => esc_html__( 'Assign host id', 'eroom-zoom-meetings-webinar' ),
				'hidden'    => true,
			),
		);

		// Add Webinars submenu
		add_submenu_page(
			'stm_zoom',
			esc_html__( 'Webinars', 'eroom-zoom-meetings-webinar' ),
			esc_html__( 'Webinars', 'eroom-zoom-meetings-webinar' ),
			$menu_capability,
			'edit.php?post_type=stm-zoom-webinar',
			false
		);

		foreach ( $pages as $page ) {
			/* Create Submenu */
			add_submenu_page(
				isset( $page['hidden'] ) && $page['hidden'] ? '' : 'stm_zoom',
				$page['label'],
				$page['label'],
				$menu_capability,
				$page['menu_slug'],
				'admin_pages'
			);
		}

		/* Remove original Submenu */
		remove_submenu_page( 'stm_zoom', 'stm_zoom' );

		// Add "eRoom PRO Addons" submenu (locked in free version)
		if ( ! defined( 'STM_ZOOM_PRO_PATH' ) ) {
			add_submenu_page(
				'stm_zoom',
				esc_html__( 'eRoom PRO Addons', 'eroom-zoom-meetings-webinar' ),
				esc_html__( 'eRoom PRO Addons', 'eroom-zoom-meetings-webinar' ),
				'manage_options',
				'stm_zoom_pro',
				'stm_zoom_pro_addons_locked_page'
			);
		}

		add_submenu_page(
			'stm_zoom',
			esc_html__( 'Help', 'eroom-zoom-meetings-webinar' ),
			esc_html__( 'Help', 'eroom-zoom-meetings-webinar' ),
			$menu_capability,
			'stm_zoom_help',
			'stm_zoom_help_page'
		);

		do_action( 'stm_zoom_admin_submenu_pages' );
	}

	/**
	 * Creating Plugin Settings
	 */
	public static function admin_settings_page() {
		add_filter(
			'wpcfto_options_page_setup',
			function ( $setup ) {
				$fields = array(
					'general_settings' => array(
						'name'   => esc_html__( 'General Settings', 'eroom-zoom-meetings-webinar' ),
						'fields' => array(
							'allowed_roles' => array(
								'type'        => 'multiselect',
								'label'       => esc_html__( 'Allowed User Roles', 'eroom-zoom-meetings-webinar' ),
								'value'       => array(),
								'options'     => PostTypeCapabilities::get_user_roles_options(),
								'description' => esc_html__( 'Select which user roles can create and manage meetings and webinars. Administrators always have full access. Selected roles will not have access to the Settings page.', 'eroom-zoom-meetings-webinar' ),
							),
							'timezone_display_format' => array(
								'type'        => 'select',
								'label'       => esc_html__( 'Timezone Display Format', 'eroom-zoom-meetings-webinar' ),
								'value'       => 'offset_only',
								'options'     => array(
									'offset_only'  => '(GMT-05:00)',
									'offset_short' => '(GMT-05:00) EST',
									'offset_full'  => '(GMT-05:00) Eastern Time (US and Canada)',
								),
								'description' => esc_html__( 'Choose how timezone information is displayed on the frontend', 'eroom-zoom-meetings-webinar' ),
							),
						),
					),
					'tab_1'      => array(
						'name'   => esc_html__( 'Zoom', 'eroom-zoom-meetings-webinar' ),
						'fields' => array(
							'auth_account_id'    => array(
								'type'        => 'text',
								'group_title' => esc_html__( 'Server to Server Oauth Credentials', 'eroom-zoom-meetings-webinar' ),
								'label'       => esc_html__( 'Account ID', 'eroom-zoom-meetings-webinar' ),
								'value'       => '',
								'group'       => 'started',
							),
							'auth_client_id'     => array(
								'type'        => 'text',
								'label'       => esc_html__( 'Client ID', 'eroom-zoom-meetings-webinar' ),
								'value'       => '',
								'description' => sprintf( '%1s <a href="https://eroomwp.com/docs/how-to-obtain-apis#meeting-sdk-credentials" target="_blank">%2s</a>  %3s ', esc_html__( 'Please follow this ', 'eroom-zoom-meetings-webinar' ), esc_html__( 'guide', 'eroom-zoom-meetings-webinar' ), esc_html__( ' to generate API values from Zoom App Marketplace using your Zoom account.', 'eroom-zoom-meetings-webinar' ) ),
							),
							'auth_client_secret' => array(
								'type'  => 'text',
								'label' => esc_html__( 'Client Secret', 'eroom-zoom-meetings-webinar' ),
								'value' => '',
								'group' => 'ended',
							),
							'sdk_key'            => array(
								'group_title' => esc_html__( 'Meeting SDK credentials', 'eroom-zoom-meetings-webinar' ),
								'group'       => 'started',
								'type'        => 'text',
								'label'       => esc_html__( 'Client ID', 'eroom-zoom-meetings-webinar' ),
								'value'       => '',
								'description' => sprintf( '%1s <a href="https://eroomwp.com/docs/how-to-obtain-apis#meeting-sdk-credentials" target="_blank">%2s</a> ', esc_html__( 'To make Join in Browser option work please generate API following this', 'eroom-zoom-meetings-webinar' ), esc_html__( 'guide', 'eroom-zoom-meetings-webinar' ) ),
							),
							'sdk_secret'         => array(
								'type'  => 'text',
								'label' => esc_html__( 'Client Secret', 'eroom-zoom-meetings-webinar' ),
								'value' => '',
								'group' => 'ended',
							),
							'generate_password'  => array(
								'type'        => 'checkbox',
								'label'       => esc_html__( 'Generate password', 'eroom-zoom-meetings-webinar' ),
								'value'       => false,
								'description' => esc_html__( 'Auto-generation of the password for meeting/webinar if left empty', 'eroom-zoom-meetings-webinar' ),
							),
						),
					),
					'shortcodes' => array(
						'name'   => esc_html__( 'Shortcodes', 'eroom-zoom-meetings-webinar' ),
						'fields' => array(
							'sc_meeting'                  => array(
								'label'       => esc_html__( 'Single Meeting', 'eroom-zoom-meetings-webinar' ),
								'type'        => 'text',
								'readonly'    => 'false',
								'value'       => '[stm_zoom_conference post_id="{post_id}"]',
								'group_title' => esc_html__( 'Zoom', 'eroom-zoom-meetings-webinar' ),
								'group'       => 'started',
							),
							'sc_webinar'                  => array(
								'label'    => esc_html__( 'Single Webinar', 'eroom-zoom-meetings-webinar' ),
								'type'     => 'text',
								'readonly' => 'false',
								'value'    => '[stm_zoom_webinar post_id="{post_id}"]',
							),
							'sc_meetings_grid'            => array(
								'label'    => esc_html__( 'Meetings Grid', 'eroom-zoom-meetings-webinar' ),
								'type'     => 'text',
								'readonly' => 'false',
								'value'    => '[stm_zoom_conference_grid post_type="stm-zoom" count="3" per_row="3"]',
							),
							'sc_webinar_grid'             => array(
								'label'    => esc_html__( 'Webinar Grid', 'eroom-zoom-meetings-webinar' ),
								'type'     => 'text',
								'readonly' => 'false',
								'value'    => '[stm_zoom_conference_grid post_type="stm-zoom-webinar" count="3" per_row="3"]',
							),
							'sc_recurring_meeting'        => array(
								'label'    => esc_html__( 'Recurring Meeting Grid (Pro version)', 'eroom-zoom-meetings-webinar' ),
								'type'     => 'text',
								'readonly' => 'false',
								'value'    => '[stm_zoom_conference_grid post_type="product" recurring="1" count="3" per_row="3"]',
							),
							'sc_product_grid'             => array(
								'label'    => esc_html__( 'Meeting Product Grid (Pro version)', 'eroom-zoom-meetings-webinar' ),
								'type'     => 'text',
								'readonly' => 'false',
								'value'    => '[stm_zoom_conference_grid post_type="product" count="3" per_row="3"]',
							),
							'sc_product_grid_by_category' => array(
								'label'    => esc_html__( 'Meeting Product Grid by category (Pro version)', 'eroom-zoom-meetings-webinar' ),
								'type'     => 'text',
								'readonly' => 'false',
								'value'    => '[stm_zoom_conference_grid post_type="product" category="{category_id}, {category_id}" count="3" per_row="3"]',
							),
							'sc_general_grid'             => array(
								'label'    => esc_html__( 'General Grid (stm-zoom, stm-zoom-webinar, product in a single grid)', 'eroom-zoom-meetings-webinar' ),
								'type'     => 'text',
								'readonly' => 'false',
								'value'    => '[stm_zoom_conference_grid post_type="stm-zoom, stm-zoom-webinar, product" count="3" per_row="3"]',
								'group'    => 'ended',
							),
						),
					),
				);

				if ( defined( 'BOOKIT_VERSION' ) ) {
					$fields['tab_1']['fields']['bookit_integration'] = array(
						'type'        => 'checkbox',
						'label'       => esc_html__( 'Bookit Appointment Integration', 'eroom-zoom-meetings-webinar' ),
						'value'       => false,
						'description' => esc_html__( 'Meeting will be created when someone books an Appointment', 'eroom-zoom-meetings-webinar' ),
					);
				}

				$setup[] = array(
					'option_name' => 'stm_zoom_settings',
					'page'        => array(
						'page_title'  => 'Settings',
						'menu_title'  => 'Settings',
						'menu_slug'   => 'stm-settings',
						'icon'        => 'dashicons-video-alt2',
						'position'    => 40,
						'parent_slug' => 'stm_zoom',
					),
					'fields'      => apply_filters( 'stm_zoom_settings_fields', $fields ),
				);

				return $setup;
			},
			200
		);
	}

	/**
	 * Enqueue Admin Styles & Scripts
	 */
	public function admin_enqueue() {
		wp_enqueue_style( 'stm_zoom_admin', STM_ZOOM_URL . 'assets/css/admin/main.css', false, STM_ZOOM_VERSION );

		if ( isset( $_GET['page'] ) && 'stm-settings' === $_GET['page'] && defined( 'STM_WPCFTO_URL' ) && defined( 'STM_WPCFTO_VERSION' ) ) { // phpcs:ignore WordPress.Security.NonceVerification
			$base   = STM_WPCFTO_URL . 'metaboxes/assets/';
			$assets = STM_WPCFTO_URL . 'metaboxes/assets/';
			$ver    = STM_WPCFTO_VERSION;

			// Fallback enqueue for NUXY core settings assets on the eRoom settings page.
			wp_enqueue_style( 'stm_zoom_nuxy_core', $base . 'css/main.css', array(), $ver );
			wp_enqueue_style( 'stm_zoom_nuxy_linear_icons', $base . 'css/linear-icons.css', array( 'stm_zoom_nuxy_core' ), $ver );
			wp_enqueue_style( 'stm_zoom_nuxy_font_awesome', $assets . 'vendors/font-awesome.min.css', array(), $ver );
			wp_enqueue_style( 'stm_zoom_nuxy_multiselect', $assets . 'vendors/vue-multiselect.min.css', array(), $ver );

			if ( is_rtl() ) {
				wp_enqueue_style( 'stm_zoom_nuxy_rtl', $base . 'css/rtl.css', array( 'stm_zoom_nuxy_core' ), $ver );
			}
		}

		if ( ! defined( 'STM_ZOOM_PRO_PATH' ) ) {
			wp_enqueue_style( 'stm_zoom_admin_gopro', STM_ZOOM_URL . 'assets/css/admin/gopro.css', false, STM_ZOOM_VERSION );

			// tiny slider Admin Popup Pro features
			wp_enqueue_style( 'stm_zoom_admin_slider', STM_ZOOM_URL . 'assets/css/admin/admin-style.css', false, STM_ZOOM_VERSION );
			wp_enqueue_script( 'stm_zoom_admin_slider', STM_ZOOM_URL . 'assets/js/admin/admin-script.js', array(), STM_ZOOM_VERSION, false );
		}

		wp_enqueue_script( 'stm_zoom_admin', STM_ZOOM_URL . 'assets/js/admin/main.js', array( 'jquery' ), STM_ZOOM_VERSION, false );
		wp_localize_script(
			'stm_zoom_admin',
			'zoom_sync',
			array(
				'nonce' => wp_create_nonce( 'zoom-sync-nonce' ),
			)
		);
	}

	/**
	 * Define WP Admin Ajax URL
	 */
	public function admin_head() { ?>
		<script type="text/javascript">
			var stm_zoom_ajaxurl = "<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>";
			var eroom_notice_nonce = "<?php echo wp_create_nonce( 'eroom_ajax_nonce' ); ?>";
		</script>
		<?php
	}

	/**
	 * Override the Freemius "Contact Us" menu link to use our own support email.
	 * Runs in admin_footer so it executes after Freemius JS sets data-fs-external-url hrefs.
	 */
	public function override_contact_link() { ?>
		<script type="text/javascript">
			(function ($) {
				$(document).ready(function () {
					$('.fs-submenu-item.eroom-zoom-meetings-webinar.contact').each(function () {
						$(this).parent('a').attr('href', 'mailto:support@eroomwp.com').removeAttr('target rel');
					});
				});
			})(jQuery);
		</script>
		<?php
	}

	/**
	 * Add Custom Links to Plugins page
	 *
	 * @param $links
	 *
	 * @return mixed
	 */
	public function plugin_action_links( $links ) {
		$settings_link = sprintf( '<a href="%1$s">%2$s</a>', admin_url( 'admin.php?page=stm-settings' ), esc_html__( 'Settings', 'eroom-zoom-meetings-webinar' ) );
		array_unshift( $links, $settings_link );

		return $links;
	}

	/**
	 * Add Meetings & Webinars Synchronize Scripts
	 */
	public function admin_meetings_webinars_scripts() {
		global $current_screen;

		if ( ! in_array( $current_screen->post_type, array( 'stm-zoom', 'stm-zoom-webinar' ), true ) ) {
			return;
		}

		wp_enqueue_script( 'stm_zoom_admin_meetings_webinars', STM_ZOOM_URL . 'assets/js/admin/meetings_webinars.js', array( 'jquery' ), STM_ZOOM_VERSION, true );
		wp_localize_script(
			'stm_zoom_admin_meetings_webinars',
			'zoom_sync',
			array(
				'nonce' => wp_create_nonce( 'zoom-sync-nonce' ),
			)
		);
	}

	public function show_invalid_credentials_notice() {
		$oauth_data = get_transient( 'stm_eroom_global_oauth_data' );

		if ( is_wp_error( $oauth_data ) ) {
			$screen = get_current_screen();

			if ( ! $screen || strpos( $screen->id, 'stm_zoom' ) === false && strpos( $screen->post_type ?? '', 'stm-zoom' ) === false ) {
				return;
			}

			$error_code    = $oauth_data->get_error_code();
			$error_message = $oauth_data->get_error_message();
			?>
			<div class="notice notice-error">
				<p>
					<strong>
					<?php
					printf(
						esc_html__( 'Zoom API Error: %s - %s', 'eroom-zoom-meetings-webinar' ),
						esc_html( $error_code ),
						esc_html( $error_message )
					);
					?>
					</strong>
				</p>
				<p>
					<?php
					printf(
						esc_html__( 'Looks like your Zoom API credentials not correct, please update your credentials in %1$s or see %2$s for help.', 'eroom-zoom-meetings-webinar' ),
						'<a href="' . esc_url( admin_url( 'admin.php?page=stm-settings' ) ) . '">' . esc_html__( 'settings', 'eroom-zoom-meetings-webinar' ) . '</a>',
						'<a href="https://eroomwp.com/docs/how-to-obtain-apis/" target="_blank">' . esc_html__( 'documentation', 'eroom-zoom-meetings-webinar' ) . '</a>'
					);
					?>
				</p>
			</div>
			<?php
		}
	}

	public function clear_zoom_cache_on_credentials_change( $option_name, $settings ) {
		if ( 'stm_zoom_settings' !== $option_name ) {
			return;
		}

		$old_settings        = get_option( 'stm_zoom_settings', array() );
		$credentials_changed = false;
		$credential_keys     = array( 'auth_account_id', 'auth_client_id', 'auth_client_secret' );

		foreach ( $credential_keys as $key ) {
			$old_value = $old_settings[ $key ] ?? '';
			$new_value = $settings[ $key ] ?? '';

			if ( $old_value !== $new_value ) {
				$credentials_changed = true;
				break;
			}
		}

		if ( $credentials_changed ) {
			delete_transient( 'stm_eroom_global_oauth_data' );
			delete_transient( 'stm_zoom_users' );
			delete_transient( 'stm_eroom_api_error' );
		}
	}

}
