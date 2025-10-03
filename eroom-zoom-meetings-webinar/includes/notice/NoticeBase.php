<?php
namespace ERoom\Notice;

defined('ABSPATH') || exit;

/**
 * Notice Base class to create a new admin notice
 *
 * @package ERoom
 */
abstract class NoticeBase {
	/**
	 * Notice constructor to init action hooks
	 */
	public function __construct()
	{
		add_action('admin_notices', [$this, 'render']);
		add_action('wp_ajax_eroom_notice_dismissed', [$this, 'dismiss_notice']);
	}

	/**
	 * Get notice priority (lower number = higher priority)
	 * Override this method in child classes to set custom priority
	 *
	 * @return int
	 */
	public function get_priority() : int
	{
		return 10; // Default priority
	}

	/**
	 * Return the unique notice name
	 *
	 * @return string
	 */
	abstract public function get_name() : string;

	/**
	 * Return notice title
	 *
	 * @return string
	 */
	abstract public function get_title() : string;

	/**
	 * Return notice description, HTML tags could be include
	 *
	 * @return string
	 */
	abstract public function get_description() : string;

	/**
	 * Return array of option buttons
	 *
	 * @return array
	 */
	abstract public function get_option_buttons() : array;

	/**
	 * Make conditional logic to check if notice is applicable
	 * If it's applicable it'll return true, and it will be displayed
	 *
	 * @return boolean
	 */
	abstract public function is_applicable() : bool;

	/**
	 * Sanitize and parse default attributes of a button element
	 *
	 * @param array $props {
	 *  array of option buttons properties
	 *
	 *  @type string $name
	 *  @type string $tag_name
	 *  @type array $attributes
	 * }
	 */
	public function sanitize_option_button( array $props = [] ) : array
	{
		$defaults = [
			'title' => '',
			'tag_name' => 'a',
			'attributes' => [],
		];

		$default_attributes = [
			'href' => 'javascript:void(0)',
			'class' => 'eroom-notice__link',
		];

		$option_button = wp_parse_args($props, $defaults);
		$option_button['attributes'] = wp_parse_args($props['attributes'], $default_attributes);

		return $option_button;
	}

	/**
	 * Check if the notice was dismissed previously
	 *
	 * @return bool
	 */
	public function is_dismissed() : bool
	{
		$dismissed_notices = get_option('eroom_dismissed_notices', []);

		return in_array($this->get_name(), $dismissed_notices);
	}

	/**
	 * Render notice from template and valiadtion check if it was dismissed or it is applicable now
	 *
	 * @return void
	 */
	public function render() : void
	{
		if($this->is_dismissed() || ! $this->is_applicable()){
			return;
		}

		// Check if a notice with higher priority should be shown instead
		if($this->should_skip_for_higher_priority()){
			return;
		}

		include plugin_dir_path( EROOM_PLUGIN_FILE ) . '/templates/admin/notices/notice-default.php';
	}

	/**
	 * Check if this notice should be skipped because a higher priority notice is applicable
	 *
	 * @return bool
	 */
	private function should_skip_for_higher_priority() : bool
	{
		static $notices_checked = [];

		// Get all registered notices
		$all_notices = apply_filters('eroom_all_notices', []);

		// If no notices registered yet, allow this one
		if(empty($all_notices)){
			return false;
		}

		// Check if any notice with higher priority (lower number) is applicable
		foreach($all_notices as $notice){
			// Skip self
			if($notice === $this){
				continue;
			}

			// Skip if already checked to prevent infinite loops
			$notice_key = get_class($notice) . '_' . $notice->get_name();
			if(in_array($notice_key, $notices_checked)){
				continue;
			}
			$notices_checked[] = $notice_key;

			// If this notice has higher priority (lower number) and is applicable and not dismissed
			if($notice->get_priority() < $this->get_priority()
				&& !$notice->is_dismissed()
				&& $notice->is_applicable()){
				return true; // Skip current notice
			}
		}

		return false;
	}

	/**
	 * Generate notice option buttons in HTML
	 *
	 * @return string
	 */
	private function render_option_buttons() : string
	{
		$options_html = '';

		foreach($this->get_option_buttons() as $option){
			$option = $this->sanitize_option_button($option);

			if(empty($option['title'])){
				continue;
			}
			
			$attributes = isset($option['attributes']) && is_array($option['attributes']) ? $option['attributes'] : [];
			$attributes_string = implode(' ', array_map(function($key) use ($attributes){
				return sprintf('%s="%s"', $key, esc_attr($attributes[$key]));
			},
				array_keys($attributes)
			));
			
			$options_html .= sprintf(
				'<%1$s %2$s >%3$s</%1$s>',
				$option['tag_name'] ?? 'div',
				$attributes_string,
				esc_html($option['title'])
			);
		}

		return $options_html;
	}

	/**
	 * Store dismissed notices in DB, received from AJAX
	 *
	 * @return void
	 */
	public function dismiss_notice() : void
	{
		check_ajax_referer('eroom_ajax_nonce', 'security' );

		$hidden_notices = get_option('eroom_dismissed_notices', []);
		$notice_name =  isset($_POST['notice']) ? sanitize_text_field($_POST['notice']) : null;

		if($notice_name && ! in_array($notice_name, $hidden_notices)){
			$hidden_notices[] = $notice_name;
			update_option('eroom_dismissed_notices', $hidden_notices);
		}

		wp_send_json_success();
	}
}