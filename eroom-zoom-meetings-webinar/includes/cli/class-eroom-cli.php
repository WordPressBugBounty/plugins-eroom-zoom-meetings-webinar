<?php
/**
 * WP-CLI Commands for eRoom Plugin
 *
 * @package eRoom
 */

if ( ! defined( 'WP_CLI' ) || ! WP_CLI ) {
	return;
}

/**
 * Manage eRoom plugin data and reset functionality
 */
class Eroom_CLI {

	/**
	 * Reset all eRoom plugin data from the database
	 *
	 * This command will delete:
	 * - All meetings and webinars (stm-zoom, stm-zoom-webinar posts)
	 * - All post meta data for these posts
	 * - All plugin options from wp_options
	 * - All custom database tables created by the plugin
	 *
	 * ## OPTIONS
	 *
	 * [--yes]
	 * : Skip confirmation prompt
	 *
	 * ## EXAMPLES
	 *
	 *     # Reset with confirmation
	 *     wp eroom reset
	 *
	 *     # Reset without confirmation (dangerous!)
	 *     wp eroom reset --yes
	 *
	 * @when after_wp_load
	 */
	public function reset( $args, $assoc_args ) {
		global $wpdb;

		// Confirmation prompt
		if ( ! isset( $assoc_args['yes'] ) ) {
			WP_CLI::confirm(
				WP_CLI::colorize( '%R⚠ WARNING: This will permanently delete all eRoom data including meetings, webinars, and settings. Are you sure?%n' ),
				$assoc_args
			);
		}

		WP_CLI::log( '🔄 Starting eRoom plugin reset...' );
		WP_CLI::log( '' );

		$stats = array(
			'posts_deleted'   => 0,
			'meta_deleted'    => 0,
			'options_deleted' => 0,
			'tables_dropped'  => 0,
		);

		// Step 1: Delete all posts (meetings and webinars)
		WP_CLI::log( '1️⃣  Deleting posts...' );

		$post_types = array( 'stm-zoom', 'stm-zoom-webinar' );
		foreach ( $post_types as $post_type ) {
			$posts = get_posts( array(
				'post_type'      => $post_type,
				'posts_per_page' => -1,
				'post_status'    => 'any',
				'fields'         => 'ids',
			) );

			if ( ! empty( $posts ) ) {
				foreach ( $posts as $post_id ) {
					wp_delete_post( $post_id, true ); // Force delete, skip trash
					$stats['posts_deleted']++;
				}
				WP_CLI::log( "   ✓ Deleted {$stats['posts_deleted']} {$post_type} posts" );
			} else {
				WP_CLI::log( "   • No {$post_type} posts found" );
			}
		}

		// Step 2: Delete orphaned post meta (in case any posts were deleted manually)
		WP_CLI::log( '' );
		WP_CLI::log( '2️⃣  Cleaning up orphaned post meta...' );

		$meta_keys = array(
			'stm_%',
			'zoom_%',
			'google_meet_%',
			'microsoft_teams_%',
			'gm_%',
			'mst_%',
		);

		foreach ( $meta_keys as $meta_key ) {
			$deleted = $wpdb->query(
				$wpdb->prepare(
					"DELETE FROM {$wpdb->postmeta}
					WHERE meta_key LIKE %s
					AND post_id NOT IN (SELECT ID FROM {$wpdb->posts})",
					$meta_key
				)
			);
			if ( $deleted ) {
				$stats['meta_deleted'] += $deleted;
			}
		}

		if ( $stats['meta_deleted'] > 0 ) {
			WP_CLI::log( "   ✓ Deleted {$stats['meta_deleted']} orphaned meta entries" );
		} else {
			WP_CLI::log( '   • No orphaned meta found' );
		}

		// Step 3: Delete plugin options
		WP_CLI::log( '' );
		WP_CLI::log( '3️⃣  Deleting plugin options...' );

		$option_patterns = array(
			'stm_zoom_%',
			'stm_eroom_%',
			'zoom_%',
			'google_meet_%',
			'google_access_token',
			'microsoft_teams_%',
			'eroom_%',
		);

		foreach ( $option_patterns as $pattern ) {
			$options = $wpdb->get_col(
				$wpdb->prepare(
					"SELECT option_name FROM {$wpdb->options} WHERE option_name LIKE %s",
					$pattern
				)
			);

			if ( ! empty( $options ) ) {
				foreach ( $options as $option_name ) {
					delete_option( $option_name );
					$stats['options_deleted']++;
				}
			}
		}

		WP_CLI::log( "   ✓ Deleted {$stats['options_deleted']} options" );

		// Step 3.5: Delete user meta for Google Meet (per-user credentials)
		WP_CLI::log( '' );
		WP_CLI::log( '3️⃣.5 Deleting user meta...' );

		$user_meta_keys = array(
			'stm_eroom_google_meet_config',
			'gm_consent_screen_url',
		);

		$user_meta_deleted = 0;
		foreach ( $user_meta_keys as $meta_key ) {
			$deleted = $wpdb->query(
				$wpdb->prepare(
					"DELETE FROM {$wpdb->usermeta} WHERE meta_key = %s",
					$meta_key
				)
			);
			if ( $deleted ) {
				$user_meta_deleted += $deleted;
			}
		}

		if ( $user_meta_deleted > 0 ) {
			WP_CLI::log( "   ✓ Deleted {$user_meta_deleted} user meta entries" );
		} else {
			WP_CLI::log( '   • No user meta found' );
		}

		// Step 4: Drop custom tables (if any exist)
		WP_CLI::log( '' );
		WP_CLI::log( '4️⃣  Checking for custom tables...' );

		$custom_tables = $wpdb->get_col(
			"SHOW TABLES LIKE '{$wpdb->prefix}stm_%'"
		);

		if ( ! empty( $custom_tables ) ) {
			foreach ( $custom_tables as $table ) {
				$wpdb->query( "DROP TABLE IF EXISTS `{$table}`" );
				$stats['tables_dropped']++;
				WP_CLI::log( "   ✓ Dropped table: {$table}" );
			}
		} else {
			WP_CLI::log( '   • No custom tables found' );
		}

		// Step 5: Flush rewrite rules
		WP_CLI::log( '' );
		WP_CLI::log( '5️⃣  Flushing rewrite rules...' );
		flush_rewrite_rules();
		WP_CLI::log( '   ✓ Rewrite rules flushed' );

		// Step 6: Clear transients
		WP_CLI::log( '' );
		WP_CLI::log( '6️⃣  Clearing transients...' );

		$transients = $wpdb->get_col(
			"SELECT option_name FROM {$wpdb->options}
			WHERE option_name LIKE '_transient_stm_%'
			OR option_name LIKE '_transient_timeout_stm_%'
			OR option_name LIKE '_transient_zoom_%'
			OR option_name LIKE '_transient_timeout_zoom_%'"
		);

		$transients_deleted = 0;
		if ( ! empty( $transients ) ) {
			foreach ( $transients as $transient_name ) {
				delete_option( $transient_name );
				$transients_deleted++;
			}
			WP_CLI::log( "   ✓ Deleted {$transients_deleted} transients" );
		} else {
			WP_CLI::log( '   • No transients found' );
		}

		// Summary
		WP_CLI::log( '' );
		WP_CLI::log( str_repeat( '=', 50 ) );
		WP_CLI::success( 'eRoom plugin reset completed!' );
		WP_CLI::log( '' );
		WP_CLI::log( 'Summary:' );
		WP_CLI::log( "  • Posts deleted:        {$stats['posts_deleted']}" );
		WP_CLI::log( "  • Meta entries cleaned: {$stats['meta_deleted']}" );
		WP_CLI::log( "  • Options deleted:      {$stats['options_deleted']}" );
		WP_CLI::log( "  • Tables dropped:       {$stats['tables_dropped']}" );
		WP_CLI::log( "  • Transients cleared:   {$transients_deleted}" );
		WP_CLI::log( '' );
		WP_CLI::log( '✨ Your database is now clean!' );
	}

	/**
	 * Display statistics about eRoom plugin data
	 *
	 * Shows count of meetings, webinars, options, and other data
	 *
	 * ## EXAMPLES
	 *
	 *     wp eroom stats
	 *
	 * @when after_wp_load
	 */
	public function stats( $args, $assoc_args ) {
		global $wpdb;

		WP_CLI::log( '📊 eRoom Plugin Statistics' );
		WP_CLI::log( str_repeat( '=', 50 ) );
		WP_CLI::log( '' );

		// Count posts
		$meetings = wp_count_posts( 'stm-zoom' );
		$webinars = wp_count_posts( 'stm-zoom-webinar' );

		WP_CLI::log( 'Posts:' );
		WP_CLI::log( "  • Meetings:  {$meetings->publish} published, {$meetings->draft} draft" );
		WP_CLI::log( "  • Webinars:  {$webinars->publish} published, {$webinars->draft} draft" );
		WP_CLI::log( '' );

		// Count options
		$options_count = $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->options}
			WHERE option_name LIKE 'stm_%'
			OR option_name LIKE 'zoom_%'
			OR option_name LIKE 'google_meet_%'
			OR option_name LIKE 'microsoft_teams_%'"
		);

		WP_CLI::log( "Options: {$options_count}" );
		WP_CLI::log( '' );

		// Check configured providers
		WP_CLI::log( 'Configured Providers:' );

		$zoom_settings = get_option( 'stm_zoom_settings' );
		$zoom_configured = ! empty( $zoom_settings['account_id'] ) || ! empty( $zoom_settings['api_key'] );
		WP_CLI::log( '  • Zoom:           ' . ( $zoom_configured ? '✓ Configured' : '✗ Not configured' ) );

		$google_settings = get_option( 'google_meet_settings' );
		$google_configured = ! empty( $google_settings['gm_client_id'] );
		WP_CLI::log( '  • Google Meet:    ' . ( $google_configured ? '✓ Configured' : '✗ Not configured' ) );

		$msteams_settings = get_option( 'microsoft_teams_settings' );
		$msteams_configured = ! empty( $msteams_settings['mst_client_id'] );
		WP_CLI::log( '  • Microsoft Teams: ' . ( $msteams_configured ? '✓ Configured' : '✗ Not configured' ) );

		WP_CLI::log( '' );
	}
}

WP_CLI::add_command( 'eroom', 'Eroom_CLI' );
