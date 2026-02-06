<?php
/**
 * Post Type Capabilities Manager
 *
 * Handles role-based capability management for eRoom meetings and webinars custom post types.
 * This class is responsible for granting and revoking WordPress capabilities to user roles
 * based on the plugin settings configuration.
 *
 * @package eRoom
 * @subpackage Capabilities
 * @since 1.5.5
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Class PostTypeCapabilities
 *
 * Manages WordPress role capabilities for eRoom meetings and webinars.
 * Implements the Single Responsibility Principle by focusing solely on capability management.
 */
class PostTypeCapabilities {

	/**
	 * List of all eRoom capabilities.
	 *
	 * @var array
	 */
	private $eroom_capabilities = array(
		'manage_eroom_meetings',
		'edit_eroom_meeting',
		'read_eroom_meeting',
		'delete_eroom_meeting',
		'edit_eroom_meetings',
		'edit_others_eroom_meetings',
		'edit_published_eroom_meetings',
		'publish_eroom_meetings',
		'read_private_eroom_meetings',
		'delete_eroom_meetings',
		'delete_published_eroom_meetings',
		'delete_others_eroom_meetings',
	);

	/**
	 * Constructor.
	 *
	 * Hooks into WordPress init action to setup capabilities.
	 *
	 * @since 1.5.5
	 */
	public function __construct() {
		add_action( 'init', array( $this, 'setup_capabilities' ), 20 );
	}

	/**
	 * Setup capabilities for managing meetings and webinars.
	 *
	 * This method reads the plugin settings to determine which user roles should have
	 * access to create and manage meetings/webinars, then grants or removes capabilities
	 * accordingly. Administrators always receive all capabilities regardless of settings.
	 *
	 * @since 1.5.5
	 * @return void
	 */
	public function setup_capabilities() {
		$allowed_roles = $this->get_allowed_roles_from_settings();
		$all_roles     = $this->get_all_wordpress_roles();

		foreach ( $all_roles as $role_key => $role_name ) {
			$role = get_role( $role_key );

			if ( ! $role ) {
				continue;
			}

			// Administrator always gets all capabilities
			if ( 'administrator' === $role_key ) {
				$this->grant_capabilities( $role );
				continue;
			}

			// Grant or remove capabilities based on settings
			if ( in_array( $role_key, $allowed_roles, true ) ) {
				$this->grant_capabilities( $role );
			} else {
				$this->remove_capabilities( $role );
			}
		}
	}

	/**
	 * Get allowed roles from plugin settings.
	 *
	 * Retrieves and normalizes the allowed_roles setting from the plugin options.
	 * Handles both string (JSON) and array formats for backward compatibility.
	 *
	 * @since 1.5.5
	 * @return array Array of role keys (slugs) that are allowed to manage meetings.
	 */
	private function get_allowed_roles_from_settings() {
		$settings      = get_option( 'stm_zoom_settings', array() );
		$allowed_roles = isset( $settings['allowed_roles'] ) ? $settings['allowed_roles'] : array();

		// Handle JSON string format
		if ( is_string( $allowed_roles ) ) {
			$decoded = json_decode( $allowed_roles, true );

			if ( JSON_ERROR_NONE === json_last_error() ) {
				$allowed_roles = $decoded;
			} else {
				$allowed_roles = array();
			}
		}

		// Extract role values from multiselect format
		if ( is_array( $allowed_roles ) && ! empty( $allowed_roles ) ) {
			return $this->extract_role_keys( $allowed_roles );
		}

		return array();
	}

	/**
	 * Extract role keys from multiselect format.
	 *
	 * The settings framework may return values in a specific format with 'value' keys.
	 * This method normalizes the data to return an array of role slugs.
	 *
	 * @since 1.5.5
	 * @param array $roles Raw roles data from settings.
	 * @return array Normalized array of role keys.
	 */
	private function extract_role_keys( $roles ) {
		$role_keys = array();

		foreach ( $roles as $role_item ) {
			if ( is_array( $role_item ) && isset( $role_item['value'] ) ) {
				$role_keys[] = $role_item['value'];
			} elseif ( is_string( $role_item ) ) {
				$role_keys[] = $role_item;
			}
		}

		return $role_keys;
	}

	/**
	 * Get all WordPress roles.
	 *
	 * Returns an associative array of all registered WordPress roles.
	 *
	 * @since 1.5.5
	 * @return array Associative array of role_key => role_name pairs.
	 */
	private function get_all_wordpress_roles() {
		global $wp_roles;

		if ( ! isset( $wp_roles ) ) {
			$wp_roles = new WP_Roles();
		}

		return $wp_roles->get_names();
	}

	/**
	 * Grant all eRoom capabilities to a role.
	 *
	 * Adds all necessary capabilities for managing meetings and webinars to the specified role.
	 * This includes creation, editing, publishing, and deletion capabilities.
	 *
	 * @since 1.5.5
	 * @param WP_Role $role The WordPress role object to grant capabilities to.
	 * @return void
	 */
	private function grant_capabilities( $role ) {
		// Grant all eRoom-specific capabilities
		foreach ( $this->eroom_capabilities as $capability ) {
			$role->add_cap( $capability );
		}

		// Grant upload capability for thumbnails
		$role->add_cap( 'upload_files' );
	}

	/**
	 * Remove all eRoom capabilities from a role.
	 *
	 * Removes all eRoom-specific capabilities from the specified role.
	 * Note: Does not remove 'upload_files' as other plugins/features may depend on it.
	 *
	 * @since 1.5.5
	 * @param WP_Role $role The WordPress role object to remove capabilities from.
	 * @return void
	 */
	private function remove_capabilities( $role ) {
		foreach ( $this->eroom_capabilities as $capability ) {
			$role->remove_cap( $capability );
		}
	}

	/**
	 * Get required capability for accessing eRoom menu.
	 *
	 * Determines the appropriate capability string needed to access eRoom menus
	 * based on the current plugin configuration.
	 *
	 * @since 1.5.5
	 * @return string Capability string ('manage_eroom_meetings' or 'manage_options').
	 */
	public static function get_required_capability() {
		$settings      = get_option( 'stm_zoom_settings', array() );
		$allowed_roles = isset( $settings['allowed_roles'] ) ? $settings['allowed_roles'] : array();

		// Normalize the allowed_roles value
		if ( is_string( $allowed_roles ) ) {
			$decoded = json_decode( $allowed_roles, true );
			if ( JSON_ERROR_NONE === json_last_error() ) {
				$allowed_roles = $decoded;
			}
		}

		// Extract actual role values if in multiselect format
		if ( is_array( $allowed_roles ) && ! empty( $allowed_roles ) ) {
			$role_keys = array();
			foreach ( $allowed_roles as $role_item ) {
				if ( is_array( $role_item ) && isset( $role_item['value'] ) ) {
					$role_keys[] = $role_item['value'];
				} elseif ( is_string( $role_item ) && ! empty( $role_item ) ) {
					$role_keys[] = $role_item;
				}
			}

			// If we have actual roles configured, use custom capability
			if ( ! empty( $role_keys ) ) {
				return 'manage_eroom_meetings';
			}
		}

		// Default to admin-only
		return 'manage_options';
	}

	/**
	 * Get user roles options for settings.
	 *
	 * Returns an array of user roles formatted for the multiselect field in settings.
	 * Excludes the administrator role as they always have access.
	 *
	 * @since 1.5.5
	 * @return array Array of role options with 'label' and 'value' keys.
	 */
	public static function get_user_roles_options() {
		global $wp_roles;

		if ( ! isset( $wp_roles ) ) {
			$wp_roles = new WP_Roles();
		}

		$roles   = $wp_roles->get_names();
		$options = array();

		// Exclude administrator role as they always have access
		unset( $roles['administrator'] );

		foreach ( $roles as $role_key => $role_name ) {
			$options[] = array(
				'label' => $role_name,
				'value' => $role_key,
			);
		}

		return $options;
	}

	/**
	 * Check if a user has permission to manage eRoom meetings.
	 *
	 * Utility method to check if a specific user has the capability to manage meetings.
	 *
	 * @since 1.5.5
	 * @param int $user_id User ID to check. Defaults to current user.
	 * @return bool True if user can manage meetings, false otherwise.
	 */
	public static function user_can_manage_meetings( $user_id = 0 ) {
		if ( 0 === $user_id ) {
			$user_id = get_current_user_id();
		}

		$user = get_user_by( 'id', $user_id );

		if ( ! $user ) {
			return false;
		}

		return $user->has_cap( 'manage_eroom_meetings' ) || $user->has_cap( 'manage_options' );
	}
}
