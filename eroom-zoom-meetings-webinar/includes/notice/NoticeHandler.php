<?php
namespace ERoom\Notice;

defined('ABSPATH') || exit;

// Load all notification system dependencies
require_once __DIR__ . '/NoticeBase.php';
require_once __DIR__ . '/CampaignNotice.php';
require_once __DIR__ . '/CampaignNoticeHandler.php';

/**
 * This class handles all necessary functionalities for admin notices,
 * like enqueue assets to admin panel to handle notices from JS side,
 * initializes all notices, vice versa.
 *
 * @package ERoom
 */
class NoticeHandler{

    public function __construct()
    {
        add_action('admin_init', [$this, 'set_first_applied_time']);

        new CampaignNoticeHandler();
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