<?php
namespace ERoom\Notice;

defined('ABSPATH') || exit;

// Load all notification system dependencies
require_once __DIR__ . '/NoticeBase.php';
require_once __DIR__ . '/CampaignNotice.php';
require_once __DIR__ . '/CampaignNoticeHandler.php';
require_once __DIR__ . '/ReviewNotice.php';

/**
 * This class handles all necessary functionalities for admin notices,
 * like enqueue assets to admin panel to handle notices from JS side,
 * initializes all notices, vice versa.
 *
 * @package ERoom
 */
class NoticeHandler{

	/**
	 * Store all notice instances
	 *
	 * @var array
	 */
	private $notices = [];

    public function __construct()
    {
        add_action('admin_init', [$this, 'set_first_applied_time']);
        add_action('admin_init', [$this, 'register_notices'], 5);

		add_filter('eroom_all_notices', [$this, 'get_all_notices']);

        new CampaignNoticeHandler();
    }

	/**
	 * Register all notice instances
	 *
	 * @return void
	 */
	public function register_notices() : void
	{
		// Register ReviewNotice with lower priority (will be shown after campaign notices)
		$this->notices[] = new ReviewNotice();
	}

	/**
	 * Get all registered notices
	 *
	 * @return array
	 */
	public function get_all_notices() : array
	{
		return $this->notices;
	}

    /**
     * Set first initiation time
     *
     * @return void
     */
    public function set_first_applied_time() : void
    {
        $first_initiated_at = get_option( 'eroom_notice_initiated' );

		if ( ! $first_initiated_at ) {
			update_option( 'eroom_notice_initiated', time() );
		}
    }

}

new NoticeHandler();