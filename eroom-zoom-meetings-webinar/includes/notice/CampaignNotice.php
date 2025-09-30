<?php
namespace ERoom\Notice;

defined('ABSPATH') or exit;

/**
 * This class is responsible to receive properties values through constructor arguments
 * and create notices in WP Admin from external data
 * 
 * @package ERoom
 */
class CampaignNotice extends NoticeBase{
    /**
     * Unique name of the notice
     *
     * @var string
     */
    protected $name;

    /**
     * Title to show top of the notice
     *
     * @var string
     */
    protected $title;

    /**
     * Content to show inside notice body
     *
     * @var string
     */
    protected $description;

    /**
     * Array of buttons, which will appear to the bottom of the notice
     *
     * @var array
     */
    protected $option_buttons;

    /**
     * Additional properties, like start and end date
     *
     * @var array
     */
    protected $props;

    public function __construct(string $name, string $title, string $description, array $option_buttons, array $props = [])
    {
        $this->name = $name;
        $this->title = $title;
        $this->description = $description;
        $this->option_buttons = $option_buttons;
        $this->props = $props;

        parent::__construct();
    }

    public function get_name() : string
    {
        return $this->name;
    }

    public function get_title() : string
    {
        return $this->title;
    }

    public function get_description() : string
    {
        return $this->description;
    }

    public function get_option_buttons() : array
    {
        return $this->option_buttons;
    }

    /**
     * Check if notice is applicable
     * 
     * Apply Logic: If start and end date is set and current time is between them, it'll be displayed
     *
     * @return boolean
     */
    public function is_applicable() : bool
    {
        $start_date = isset($this->props['start']) ? $this->props['start'] : null;
        $end_date = isset($this->props['end']) ? $this->props['end'] : null;
        
        if(is_null($start_date) || is_null($end_date)){
            return false;
        }
        
        if(strtotime($start_date) >= time() || strtotime($end_date) <= time()){
            return false;
        }

        return true;
    }
}