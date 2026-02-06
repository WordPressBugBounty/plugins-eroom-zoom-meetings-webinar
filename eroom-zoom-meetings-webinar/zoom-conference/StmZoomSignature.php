<?php
/**
 * Zoom Meeting Signature Generator
 *
 * Handles secure signature generation and meeting data retrieval for Zoom SDK integration.
 *
 * @package eroom-zoom-meetings-webinar
 */

class StmZoomSignature {

	/**
	 * Generate meeting signature and return meeting configuration.
	 *
	 * @param int    $post_id Post ID of the meeting/webinar.
	 * @param int    $role User role (0 = Attendee, 1 = Host).
	 * @param string $form_name Optional name from webinar registration form.
	 * @param string $form_email Optional email from webinar registration form.
	 *
	 * @return array Meeting configuration with signature or error.
	 */
	public static function generate_meeting_data( $post_id, $role = 0, $form_name = '', $form_email = '' ) {
		// Validate post.
		$post = get_post( $post_id );
		if ( ! $post || ! in_array( $post->post_type, array( 'stm-zoom', 'stm-zoom-webinar' ), true ) ) {
			return array(
				'success' => false,
				'message' => 'Invalid meeting.',
			);
		}

		// Check if post is published.
		if ( 'publish' !== $post->post_status ) {
			return array(
				'success' => false,
				'message' => 'Meeting not available.',
			);
		}

		// Get meeting data.
		$meeting_data     = get_post_meta( $post_id, 'stm_zoom_data', true );
		$meeting_password = get_post_meta( $post_id, 'stm_password', true );
		$enforce_login    = absint( ! empty( get_post_meta( $post_id, 'stm_enforce_login', true ) ) );

		if ( empty( $meeting_data ) || empty( $meeting_data['id'] ) ) {
			return array(
				'success' => false,
				'message' => 'Meeting data not found.',
			);
		}

		$meeting_number = sanitize_text_field( $meeting_data['id'] );

		// Get SDK credentials.
		$credentials = self::get_sdk_credentials();
		if ( ! $credentials['success'] ) {
			return $credentials;
		}

		// Get user information.
		$user_info = self::get_user_info( $form_name, $form_email );

		// Get registration token if enforce login is enabled.
		$tk = '';
		if ( $enforce_login && ! empty( $user_info['email'] ) ) {
			$tk = self::get_registration_token( $meeting_number, $user_info['email'] );
		}

		// Generate signature.
		$signature = self::generate_signature( $credentials['api_key'], $credentials['api_secret'], $meeting_number, $role );

		return array(
			'success'        => true,
			'signature'      => $signature,
			'sdkKey'         => $credentials['api_key'],
			'meetingNumber'  => $meeting_number,
			'password'       => $meeting_password,
			'userName'       => $user_info['username'],
			'userEmail'      => $user_info['email'],
			'leaveUrl'       => get_home_url( '/' ),
			'enforceLogin'   => $enforce_login,
			'tk'             => $tk,
		);
	}

	/**
	 * Get SDK credentials from settings.
	 *
	 * @return array SDK credentials or error.
	 */
	private static function get_sdk_credentials() {
		$settings   = get_option( 'stm_zoom_settings', array() );
		$api_key    = ! empty( $settings['sdk_key'] ) ? $settings['sdk_key'] : '';
		$api_secret = ! empty( $settings['sdk_secret'] ) ? $settings['sdk_secret'] : '';

		if ( empty( $api_key ) || empty( $api_secret ) ) {
			return array(
				'success' => false,
				'message' => 'SDK credentials not configured.',
			);
		}

		return array(
			'success'    => true,
			'api_key'    => $api_key,
			'api_secret' => $api_secret,
		);
	}

	/**
	 * Get user information (username and email).
	 *
	 * @param string $form_name Name from webinar form.
	 * @param string $form_email Email from webinar form.
	 *
	 * @return array User information.
	 */
	private static function get_user_info( $form_name = '', $form_email = '' ) {
		$username = esc_attr__( 'Guest', 'eroom-zoom-meetings-webinar' );
		$email    = '';

		// Prioritize form data for webinars.
		if ( ! empty( $form_name ) && ! empty( $form_email ) ) {
			$username = $form_name;
			$email    = $form_email;
		} elseif ( is_user_logged_in() ) {
			$user     = wp_get_current_user();
			$username = $user->user_login;
			$email    = $user->user_email;
		}

		return array(
			'username' => $username,
			'email'    => $email,
		);
	}

	/**
	 * Get registration token for enforce login meetings.
	 *
	 * @param string $meeting_number Meeting ID.
	 * @param string $email User email.
	 *
	 * @return string Registration token or empty string.
	 */
	private static function get_registration_token( $meeting_number, $email ) {
		if ( ! class_exists( '\Zoom\Endpoint\Meetings' ) ) {
			return '';
		}

		$meetings_api = new \Zoom\Endpoint\Meetings();
		$response     = $meetings_api->listRegistrants( $meeting_number );

		if ( ! is_array( $response ) || 200 !== $response['code'] || ! isset( $response['registrants'] ) ) {
			return '';
		}

		$registrant = array_reduce(
			$response['registrants'],
			function ( $carry, $user ) use ( $email ) {
				if ( $user['email'] === $email && 'approved' === $user['status'] ) {
					$carry = $user;
				}
				return $carry;
			},
			false
		);

		if ( empty( $registrant ) || empty( $registrant['join_url'] ) ) {
			return '';
		}

		$url_components = wp_parse_url( $registrant['join_url'] );
		if ( empty( $url_components['query'] ) ) {
			return '';
		}

		parse_str( $url_components['query'], $url_params );
		return ! empty( $url_params['tk'] ) ? $url_params['tk'] : '';
	}

	/**
	 * Generate Zoom SDK signature using JWT format.
	 *
	 * @param string $api_key SDK key.
	 * @param string $api_secret SDK secret.
	 * @param string $meeting_number Meeting ID.
	 * @param int    $role User role (0 = Attendee, 1 = Host).
	 *
	 * @return string Generated signature.
	 */
	private static function generate_signature( $api_key, $api_secret, $meeting_number, $role ) {
		$iat      = time() - 30;
		$exp      = $iat + 60 * 60 * 2;
		$oPayload = array(
			'sdkKey'   => $api_key,
			'mn'       => $meeting_number,
			'role'     => $role,
			'iat'      => $iat,
			'exp'      => $exp,
			'appKey'   => $api_key,
			'tokenExp' => $exp,
		);

		// phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode
		$header = base64_encode( wp_json_encode( array( 'alg' => 'HS256', 'typ' => 'JWT' ) ) );
		// phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode
		$payload   = base64_encode( wp_json_encode( $oPayload ) );
		$hash      = hash_hmac( 'sha256', $header . '.' . $payload, $api_secret, true );
		// phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode
		$signature = $header . '.' . $payload . '.' . base64_encode( $hash );
		$signature = rtrim( strtr( $signature, '+/', '-_' ), '=' );

		return $signature;
	}
}
