<?php
// phpcs:ignoreFile
?>

<div class="zoom_top_bar">
    <div class="free">
        <img src="<?php echo esc_url(STM_ZOOM_URL . 'assets/images/zoom_icon.png') ?>" width="40"/>
        <div class="zoom_title">eRoom</div>
        <div class="zoom_subtitle"><?php echo esc_html( sprintf( __( 'v %s', 'eroom-zoom-meetings-webinar' ), STM_ZOOM_VERSION ) ); ?></div>
    </div>
    <div class="pro">
        <?php if ( ! defined( 'STM_ZOOM_PRO_PATH' ) ) { ?>
            <a href="#" class="show_pro_features" target="_blank">Pro features</a>
        <?php } ?>
    </div>
</div>
<script>
    (function ($) {
        'use strict';
        $(document).ready(function () {
            $('.zoom_top_bar .pro .show_pro_features').on('click', function (e) {
                e.preventDefault();
                $('#stm_zoom_pro_popup').fadeIn();
            })
        })
    })(jQuery);
</script>