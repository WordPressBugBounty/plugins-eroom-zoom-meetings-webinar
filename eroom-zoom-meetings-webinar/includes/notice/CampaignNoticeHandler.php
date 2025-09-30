<?php

namespace ERoom\Notice;

defined('ABSPATH') or exit;

/**
 * This class is responsible to pull notices from remote URL and create instance of @CampaignNotice from pulled data
 *
 * @package ERoom
 */
class CampaignNoticeHandler{
    public function __construct()
    {
        add_action('admin_init', [$this, 'pull_notices_from_server'], 10);
        add_action('admin_init', [$this, 'initiate_pulled_notices'], 11);
    }

    /**
     * Pull notices from JSON file from a remote URL
     * Then stores it in a transient for 24 hours
     *
     * @return void
     */
    public function pull_notices_from_server() : void
    {
        if(get_transient('eroom_campaign_notices') !== false){
            return;
        }

        // Update this URL to point to your eRoom notices JSON file
        $remote_url = 'https://eroomwp.com/notices.json';
        $response = wp_remote_get( $remote_url );

        if(is_wp_error($response)){
            return;
        }

        if(wp_remote_retrieve_response_code($response) !== 200){
            return;
        }

        $notices = wp_remote_retrieve_body($response);
        $notices = json_decode($notices, true);

        if(!is_array($notices) || json_last_error() !== JSON_ERROR_NONE){
            return;
        }

        $notices = $this->sanitize_notices($notices);
        set_transient('eroom_campaign_notices', $notices, DAY_IN_SECONDS);
    }

    /**
     * Sanitize each notice item by mapping each values inside the array
     *
     * @param array $notices
     * @return array
     */
    private function sanitize_notices(array $notices) : array
    {
        $notices = array_map(function($notice){
            $notice['name'] = isset($notice['name']) ? sanitize_text_field($notice['name']) : '';
            $notice['title'] = isset($notice['title']) ? sanitize_text_field($notice['title']) : '';
            $notice['description'] = isset($notice['description']) ? wp_kses_post($notice['description']) : '';
            $notice['option_buttons'] = isset($notice['option_buttons']) && is_array($notice['option_buttons']) ? $notice['option_buttons'] : [];
            $notice['props'] = isset($notice['props']) && is_array($notice['props']) ? $notice['props'] : [];

            $notice['props']['start'] = isset($notice['props']['start']) ? sanitize_text_field($notice['props']['start']) : '';
            $notice['props']['end'] = isset($notice['props']['end']) ? sanitize_text_field($notice['props']['end']) : '';
	        $notice['props']['enabled'] = isset($notice['props']['enabled']) ? (bool)sanitize_text_field($notice['props']['enabled']) : false;

            $notice['option_buttons'] = array_map(function($button){
                $button['title'] = isset($button['title']) ? sanitize_text_field($button['title']) : '';

                if(isset($button['tag_name'])){
                    $button['tag_name'] = sanitize_text_field($button['tag_name']);
                }

                if(!isset($button['attributes'])){
                    return $button;
                }

                $attributes = $button['attributes'];

                return array_merge($button, [
                    'attributes' => $attributes
                ]);
            }, $notice['option_buttons']);

            return $notice;

        }, $notices);

        return $notices;
    }

    /**
     * Create @CampaignNotice instance for each notice stored in database
     *
     * @return void
     */
    public function initiate_pulled_notices() : void
    {
        $notices = get_transient('eroom_campaign_notices') ? get_transient('eroom_campaign_notices') : [];

        foreach($notices as $notice){
            if(empty($notice['name']) || !isset($notice['props']['enabled']) || $notice['props']['enabled'] === false){
                return;
            }

            new CampaignNotice(
                $notice['name'],
                $notice['title'],
                $notice['description'],
                $notice['option_buttons'],
                $notice['props']
            );
        }
    }
}