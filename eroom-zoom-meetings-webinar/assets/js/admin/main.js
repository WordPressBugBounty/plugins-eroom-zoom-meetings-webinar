(function ($) {
	'use strict';
	$( document ).ready(
		function () {
			var classes = [
			'post-type-stm-zoom-webinar',
			];

			if ($( 'body' ).is( "." + classes.join( ', .' ) )) {
				$( '#adminmenu > li' ).removeClass( 'wp-has-current-submenu wp-menu-open' );

				$( '#toplevel_page_stm_zoom' )
				.addClass( 'wp-has-current-submenu wp-menu-open' )
				.removeClass( 'wp-not-current-submenu' );

				$( '.toplevel_page_stm_zoom' )
				.addClass( 'wp-has-current-submenu' )
				.removeClass( 'wp-not-current-submenu' );
			}

			/**
			 * Feedback Modal
			 */
			let body           = 'body';
			let feedback_modal = '#eroom-feedback-modal';

			$( body ).on(
				'click',
				'.feedback-modal-close',
				function (e) {
					e.preventDefault();
					$( feedback_modal ).fadeOut( 200 );
				}
			);

			$( body ).on(
				'click',
				function ( e ) {
					if ( e.target.id === 'eroom-feedback-modal' ) {
						$( feedback_modal ).fadeOut( 200 );
					}
				}
			);

			/**
			 * Feedback Review
			 */
			$( body ).on(
				'click',
				'#feedback-stars li',
				function (e) {
					var rating = parseInt( $( this ).data( 'value' ), 10 ),
					stars      = $( this ).parent().children( 'li.star' );

					stars.removeClass( 'selected' );

					for ( let i = 0; i < rating; i++ ) {
						$( stars[i] ).addClass( 'selected' );
					}

					$( '.feedback-rating-stars span.rating-text' ).text( $( this ).attr( 'title' ) );
					$( '.feedback-extra' ).toggle( rating < 4 );
					$( '.feedback-submit img' ).toggle( rating > 3 );
				}
			);

			$( body ).on(
				'click',
				'.feedback-submit',
				function (e) {
					var rating = parseInt( $( 'ul#feedback-stars li.selected' ).last().data( 'value' ), 10 ),
					review     = $( '#feedback-review' ).val();

					/** Send Feedback */
					if ( rating < 4 ) {
						e.preventDefault();
						$.ajax(
							{
								url: 'https://panel.stylemixthemes.com/api/item-review',
								dataType: 'json',
								method: 'POST',
								data: {
									'item': 'eroom-zoom-meetings-webinar',
									'type': 'plugin',
									rating,
									review
								},
								success: function(response) {}
							}
						);
					}

					/** Thank You */
					$( 'ul#feedback-stars li' ).addClass( 'disabled' ).prop( 'disabled', true );
					$( feedback_modal ).find( 'h2' ).text( 'Thank You for Feedback' );
					$( feedback_modal ).find( '.feedback-review-text' ).text( review );
					$( '.feedback-review-text, .feedback-thank-you' ).show();
					$( '.feedback-extra, .feedback-submit' ).hide();

				}
			);

			/**
			 * eRoom Notice System - Handle notice dismissal
			 */
			$( document ).on(
				'click',
				'.notice-eroom-plugin .eroom-notice--dismiss, .notice-eroom-plugin .notice-dismiss',
				function(e) {
					e.preventDefault();
					e.stopPropagation();
					
					const $notice = $( this ).closest( '.notice-eroom-plugin' );
					const noticeName = $notice.data( 'notice' );
					
					if ( typeof stm_zoom_ajaxurl !== 'undefined' && typeof eroom_notice_nonce !== 'undefined' ) {
						$.ajax({
							type: 'POST',
							url: stm_zoom_ajaxurl,
							data: {
								action: 'eroom_notice_dismissed',
								notice: noticeName,
								security: eroom_notice_nonce
							},
							dataType: 'JSON',
							success: function( response ) {
								if ( response.success === true ) {
									$notice.slideUp( 200, () => {
										$notice.remove();
									});
								}
							}
						});
					}

					return false;
				}
			);

		/**
		 * Delete from Zoom button handler
		 */
		$( document ).on(
			'click',
			'#delete_from_zoom_btn',
			function(e) {
				e.preventDefault();

				var $btn = $( this );
				var meetingId = $btn.data( 'meeting-id' );
				var postId = $btn.data( 'post-id' );
				var isWebinar = $btn.data( 'type' ) === 'webinar';
				var itemType = isWebinar ? 'webinar' : 'meeting';

				if ( typeof zoom_sync === 'undefined' ) {
					alert( 'Error: zoom_sync object not found. Please refresh the page.' );
					return;
				}

				if ( confirm( 'Are you sure you want to permanently delete this ' + itemType + ' from Zoom API? This action cannot be undone.' ) ) {
					$.ajax({
						url: stm_zoom_ajaxurl,
						method: 'post',
						dataType: 'json',
						data: {
							action: 'stm_zoom_delete_from_api',
							meeting_id: meetingId,
							post_id: postId,
							is_webinar: isWebinar,
							nonce: zoom_sync.nonce
						},
						beforeSend: function() {
							$btn.prop( 'disabled', true ).text( 'Deleting...' );
						},
						success: function(response) {
							if ( response.success ) {
								$btn.prop( 'disabled', false ).text( 'Delete from Zoom' );
								$btn.closest( '.inside' ).prepend( '<div class="notice notice-success inline"><p>✅ ' + itemType.charAt(0).toUpperCase() + itemType.slice(1) + ' deleted from Zoom successfully!</p></div>' );
								setTimeout( function() {
									location.reload();
								}, 1500 );
							} else {
								$btn.prop( 'disabled', false ).text( 'Delete from Zoom' );
								var errorMsg = response.data ? response.data : 'Failed to delete from Zoom. Please try again.';
								$btn.closest( '.inside' ).prepend( '<div class="notice notice-error inline"><p>❌ ' + errorMsg + '</p></div>' );
							}
						},
						error: function(request, status, error) {
							$btn.prop( 'disabled', false ).text( 'Delete from Zoom' );
							$btn.closest( '.inside' ).prepend( '<div class="notice notice-error inline"><p>❌ Failed to delete from Zoom: ' + request.responseText + '</p></div>' );
						}
					});
				}
			}
		);

		}
	);
})( jQuery );
