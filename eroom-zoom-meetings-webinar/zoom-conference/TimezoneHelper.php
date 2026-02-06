<?php
/**
 * Timezone Helper Class
 *
 * Handles timezone display formatting for meetings and webinars
 */
class TimezoneHelper {

	/**
	 * Get formatted timezone display based on settings
	 *
	 * @param string $timezone_identifier The timezone identifier (e.g., 'America/New_York')
	 * @param string $timezone_offset The GMT offset (e.g., '-05:00')
	 * @return string Formatted timezone display
	 */
	public static function get_display( $timezone_identifier, $timezone_offset ) {
		$settings = get_option( 'stm_zoom_settings', array() );
		$format = isset( $settings['timezone_display_format'] ) ? $settings['timezone_display_format'] : 'offset_only';

		// Default to offset only
		$display = '(GMT' . $timezone_offset . ')';

		// Add timezone name based on format
		if ( 'offset_short' === $format || 'offset_full' === $format ) {
			try {
				$date = new DateTime( 'now', new DateTimeZone( $timezone_identifier ) );

				if ( 'offset_short' === $format ) {
					// Get timezone abbreviation (e.g., "EST", "PST", "ICT")
					$name = $date->format( 'T' );
				} else {
					// Get full timezone identifier and convert to readable name
					$timezone_options = stm_zoom_get_timezone_options();
					if ( isset( $timezone_options[ $timezone_identifier ] ) ) {
						$full_option = $timezone_options[ $timezone_identifier ];
						$name = preg_replace( '/^\(GMT[+-]\d{2}:\d{2}\)/', '', $full_option );
						$name = trim( $name );
					} else {
						$name = '';
					}
				}

				if ( ! empty( $name ) && $name !== $timezone_identifier ) {
					$display .= ' ' . $name;
				}
			} catch ( Exception $e ) {
				// If timezone is invalid, just use offset
			}
		}

		return $display;
	}
}
