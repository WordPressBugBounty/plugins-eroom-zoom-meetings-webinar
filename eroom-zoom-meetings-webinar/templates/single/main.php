<?php
get_header();

if ( have_posts() ) :
	while ( have_posts() ) :
		the_post();

		$_post_type = get_post_type();
		if ( 'stm-zoom' === $_post_type ) {
			$shortcode = '[stm_zoom_conference post_id="' . get_the_ID() . '" hide_content_before_start=""]';
		} elseif ( 'stm-zoom-webinar' === $_post_type ) {
			$shortcode = '[stm_zoom_webinar post_id="' . get_the_ID() . '" hide_content_before_start=""]';
		} else {
			$shortcode = '';
		}

		if ( $shortcode ) {
			echo do_shortcode( apply_filters( 'stm_zoom_single_zoom_template_shortcode', $shortcode, get_the_ID() ) );
		}

	endwhile;
endif;

get_footer();
