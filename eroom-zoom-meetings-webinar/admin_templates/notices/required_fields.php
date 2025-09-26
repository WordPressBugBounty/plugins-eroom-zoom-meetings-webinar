<?php

$settings = get_option( 'stm_zoom_settings', array() );

if ( ! empty( $settings ) && ( empty( $settings['auth_account_id'] ) && empty( $settings['auth_client_id'] ) && empty( $settings['auth_client_secret'] ) ) ) {
	Migration::get_instance();
}
