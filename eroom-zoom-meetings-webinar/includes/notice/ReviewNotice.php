<?php
namespace ERoom\Notice;

defined('ABSPATH') or exit;

/**
 * ERoom_Review_Notice Class
 *
 * This class is extended from NoticeBase to display review notice to admin
 *
 * @package ERoom
 */
class ReviewNotice extends NoticeBase{

	public function get_name() : string
	{
		return 'review';
	}

	public function get_title() : string
	{
		return __('Help Us Grow', 'eroom-zoom-meetings-webinar');
	}

	public function get_description() : string
	{
		return sprintf(
			'<p>%s</p>',
			__('We noticed you\'ve been using eRoom - Zoom Meetings & Webinars for a while, and we hope it\'s making your life easier! Could you do us a <strong>huge favor</strong> and leave us a 5-star rating on WordPress? It would mean the world to us and help us reach even more users!', 'eroom-zoom-meetings-webinar')
		);
	}

	public function get_option_buttons(): array
	{
		return [
			[
				'title' => __('Ok, you deserve it', 'eroom-zoom-meetings-webinar'),
				'attributes' => [
					'href' => esc_url('https://wordpress.org/support/plugin/eroom-zoom-meetings-webinar/reviews/?filter=5#new-post'),
					'class' => 'eroom-notice__button',
					'target' => '_blank',
				]
			],
			[
				'title' => __('I already did', 'eroom-zoom-meetings-webinar'),
				'attributes' => [
					'class' => 'eroom-notice__link eroom-notice--dismiss'
				]
			],
			[
				'title' => __('No, not good enough', 'eroom-zoom-meetings-webinar'),
				'attributes' => [
					'class' => 'eroom-notice__link eroom-notice--dismiss'
				]
			]
		];
	}

	/**
	 * Get priority for review notice (lower priority than campaign notices)
	 *
	 * @return int
	 */
	public function get_priority() : int
	{
		return 10;
	}

	/**
	 * Check if notice is applicable
	 *
	 * Apply logic: it'll be displayed if user has more than 30 zoom meetings
	 *
	 * @return boolean
	 */
	public function is_applicable() : bool
	{
		// Count zoom meetings (custom post type: stm-zoom)
		$zoom_count = wp_count_posts('stm-zoom');
		$total_zoom = 0;

		if ($zoom_count) {
			// Sum all published zoom meetings
			$total_zoom = isset($zoom_count->publish) ? intval($zoom_count->publish) : 0;
		}

		return $total_zoom > 30;
	}
}
