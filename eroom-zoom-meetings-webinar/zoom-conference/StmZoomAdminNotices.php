<?php

class StmZoomAdminNotices {

	/**
	 * @return StmZoomAdminNotices constructor.
	 */
	public function __construct() {
		add_action( 'admin_notices', array( $this, 'admin_notices' ) );

		add_action( 'wp_ajax_stm_zoom_ajax_add_feedback', array( $this, 'add_feedback' ) );

		add_action( 'stm_zoom_after_create_meeting', array( $this, 'stm_zoom_after_create_meeting' ) );

		add_action( 'stm_admin_notice_rate_eroom-zoom-meetings-webinar_single', array( $this, 'stm_zoom_admin_notice_single' ) );
	}

	/**
	 * Show Pro Notices
	 */
	public function admin_notices() {
		if ( ! empty( $_GET['post_type'] ) && ( 'stm-zoom' === $_GET['post_type'] || 'stm-zoom-webinar' === $_GET['post_type'] ) ) {
			include STM_ZOOM_PATH . '/admin_templates/notices/feedback.php';
			include STM_ZOOM_PATH . '/admin_templates/notices/pro_popup.php';
			include STM_ZOOM_PATH . '/admin_templates/notices/top_bar.php';
		}

		// Show missing invitation scope warning
		if ( get_transient( 'stm_zoom_missing_invitation_scope' ) ) {
			$this->show_missing_scope_notice();
		}

		// Show eRoom API error notice.
		if ( get_transient( 'stm_eroom_api_error' ) ) {
			$this->show_eroom_api_error_notice();
		}
	}

	/**
	 * Show missing invitation scope notice
	 */
	public function show_missing_scope_notice() {
		$screen = get_current_screen();
		if ( ! $screen || ( 'stm-zoom' !== $screen->post_type && 'stm-zoom-webinar' !== $screen->post_type ) ) {
			return;
		}

		?>
		<div class="notice notice-warning is-dismissible stm-zoom-missing-scope-notice">
			<p>
				<strong><?php esc_html_e( 'eRoom - Zoom OAuth Scope Missing', 'eroom-zoom-meetings-webinar' ); ?></strong>
			</p>
			<p>
				<?php
				echo wp_kses_post(
					sprintf(
						/* translators: %s: link to documentation */
						__( 'Your Zoom app is missing the required OAuth scope to retrieve meeting invitation details. Calendar exports will use a basic format instead of Zoom\'s official invitation text with dial-in numbers. To fix this, please add the <code>meeting:read:invitation</code> scope to your Zoom app. <a href="%s" target="_blank">Learn more about required scopes</a>.', 'eroom-zoom-meetings-webinar' ),
						'https://eroomwp.com/docs/how-to-obtain-apis/#required-scopes'
					)
				);
				?>
			</p>
		</div>
		<?php
	}

	/**
	 * Show eRoom API error notice
	 */
	public function show_eroom_api_error_notice() {
		$screen = get_current_screen();
		if ( ! $screen || ( 'stm-zoom' !== $screen->post_type && 'stm-zoom-webinar' !== $screen->post_type ) ) {
			return;
		}

		// Don't show if OAuth error is already being displayed.
		$oauth_data = get_transient( 'stm_eroom_global_oauth_data' );
		if ( is_wp_error( $oauth_data ) ) {
			return;
		}

		$error_message = get_transient( 'stm_eroom_api_error' );
		if ( empty( $error_message ) ) {
			return;
		}

		?>
		<div class="notice notice-error is-dismissible stm-eroom-api-error-notice">
			<p>
				<strong><?php esc_html_e( 'eRoom - API Error', 'eroom-zoom-meetings-webinar' ); ?></strong>
			</p>
			<p>
				<?php echo esc_html( $error_message ); ?>
			</p>
			<p>
				<?php
				echo wp_kses_post(
					sprintf(
						/* translators: %1$s: link to settings, %2$s: link to documentation */
						__( 'Please check your API credentials in %1$s or see %2$s for help.', 'eroom-zoom-meetings-webinar' ),
						'<a href="' . esc_url( admin_url( 'admin.php?page=stm-settings#tab_1' ) ) . '">' . esc_html__( 'settings', 'eroom-zoom-meetings-webinar' ) . '</a>',
						'<a href="https://eroomwp.com/docs/how-to-obtain-apis/" target="_blank">' . esc_html__( 'documentation', 'eroom-zoom-meetings-webinar' ) . '</a>'
					)
				);
				?>
			</p>
		</div>
		<?php
	}

	/**
	 * Add Feedback
	 */
	public function add_feedback() {
		update_option( 'stm_zoom_feedback_added', true );
	}

	public function stm_zoom_after_create_meeting() {

		$created = get_option( 'stm_eroom_meeting_created', false );

		if ( ! $created ) {
			$data = array(
				'show_time'   => time(),
				'step'        => 0,
				'prev_action' => '',
			);
			set_transient( 'stm_eroom-zoom-meetings-webinar_single_notice_setting', $data );
			update_option( 'stm_eroom_meeting_created', true );
		}
	}

	public static function stm_zoom_admin_notice_single( $data ) {
		if ( is_array( $data ) ) {
			$data['title']   = 'Hooray!';
			$data['content'] = 'The first meeting has been created successfully. We are asking you to do a favor by rating <strong>eRoom 5 Stars up!</strong>';
		}

		return $data;
	}

}
