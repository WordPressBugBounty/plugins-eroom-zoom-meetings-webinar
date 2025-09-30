<div class="notice is-dismissible notice-eroom-plugin" data-notice="<?php echo esc_attr($this->get_name()) ?>">
    <div class="eroom-notice">
        <span class="eroom-notice__icon">
            <img src="<?php echo esc_url(EROOM_PLUGIN_URL . 'assets/images/zoom_icon.png') ?>" alt="<?php _e('eRoom Logo', 'eroom-zoom-meetings-webinar') ?>" width="40" />
        </span>
        <div class="eroom-notice__content">
            <h2><?php echo esc_html($this->get_title()) ?></h2>
            <div class="eroom-notice__content_description">
                <?php echo wp_kses_post($this->get_description()) ?>
            </div>
            <div class="eroom-notice__options">
               <?php echo $this->render_option_buttons() ?>
            </div>
        </div>
    </div>
</div>