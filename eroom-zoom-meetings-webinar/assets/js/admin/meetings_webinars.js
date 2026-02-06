(function ($) {
    'use strict';
    $(document).ready(function () {
        var zoom_type = (typeof typenow != 'undefined' && typenow == 'stm-zoom-webinar') ? 'Webinars' : 'Meetings';

        var syncButtonHtml = '<div id="zoom_sync_wrapper">' +
            '<a id="sync_with_zoom_btn" class="page-title-action">🔄 Sync with Zoom ▾</a>' +
            '<div id="zoom_sync_dropdown">' +
                '<a href="#" id="push_to_zoom"><span>⬆</span> Push to Zoom</a>' +
                '<a href="#" id="pull_from_zoom"><span>⬇</span> Pull from Zoom</a>' +
            '</div>' +
        '</div>';

        $('.wrap h1.wp-heading-inline').after(syncButtonHtml);

        $('#sync_with_zoom_btn').on('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            $('#zoom_sync_dropdown').toggle();
        });

        $(document).on('click', function(e) {
            if (!$(e.target).closest('#zoom_sync_wrapper').length) {
                $('#zoom_sync_dropdown').hide();
            }
        });

        $(document).on('click', '#push_to_zoom', function(e) {
            e.preventDefault();
            $('#zoom_sync_dropdown').hide();

            if (confirm('Push to Zoom: This will update/create ' + zoom_type + ' on Zoom based on your WordPress posts. Continue?')) {
                $.ajax({
                    url: stm_zoom_ajaxurl,
                    method: 'post',
                    type: 'json',
                    data: {
                        action: 'stm_zoom_sync_meetings_webinars',
                        mode: 'push',
                        zoom_type: typenow,
                        nonce: zoom_sync.nonce
                    },
                    beforeSend: function() {
                        $('.wrap > .update-message').remove();
                        $('.wrap > hr.wp-header-end.extra').remove();
                        $('.wrap > hr.wp-header-end').after('<div class="update-message notice inline notice-warning notice-alt updating-message"><p>⬆ Pushing ' + zoom_type + ' to Zoom...</p></div><hr class="wp-header-end extra">');
                    },
                    success: function(response) {
                        $('.wrap > .update-message').remove();
                        $('.wrap > hr.wp-header-end.extra').remove();
                        if (response == 'done' || (response.success && response.data == 'done')) {
                            $('.wrap > hr.wp-header-end').after('<div class="update-message notice inline notice-alt updated-message notice-success"><p>✅ ' + zoom_type + ' pushed to Zoom successfully! <a href="' + window.location.href + '">Reload page</a> to see changes.</p></div><hr class="wp-header-end extra">');
                        } else {
                            if (response && response.data && response.data.length > 0) {
                                $('.wrap > hr.wp-header-end').after('<div class="update-message notice inline notice-alt notice-error"><p>' + response.data + '</p></div><hr class="wp-header-end extra">');
                            } else {
                                $('.wrap > hr.wp-header-end').after('<div class="update-message notice inline notice-alt notice-error"><p>❌ Push to Zoom failed. Please try again!</p></div><hr class="wp-header-end extra">');
                            }
                        }
                    },
                    error: function (request, status, error) {
                        $('.wrap > .update-message').remove();
                        $('.wrap > hr.wp-header-end.extra').remove();
                        $('.wrap > hr.wp-header-end').after('<div class="update-message notice inline notice-alt notice-error"><p>❌ Push to Zoom failed: ' + request.responseText + '</p></div><hr class="wp-header-end extra">');
                    }
                });
            }
        });

        $(document).on('click', '#pull_from_zoom', function(e) {
            e.preventDefault();
            $('#zoom_sync_dropdown').hide();

            if (confirm('Pull from Zoom: This will import/update ' + zoom_type + ' from Zoom to your WordPress. Continue?')) {
                $.ajax({
                    url: stm_zoom_ajaxurl,
                    method: 'post',
                    type: 'json',
                    data: {
                        action: 'stm_zoom_sync_meetings_webinars',
                        mode: 'pull',
                        zoom_type: typenow,
                        nonce: zoom_sync.nonce
                    },
                    beforeSend: function() {
                        $('.wrap > .update-message').remove();
                        $('.wrap > hr.wp-header-end.extra').remove();
                        $('.wrap > hr.wp-header-end').after('<div class="update-message notice inline notice-warning notice-alt updating-message"><p>⬇ Pulling ' + zoom_type + ' from Zoom...</p></div><hr class="wp-header-end extra">');
                    },
                    success: function(response) {
                        $('.wrap > .update-message').remove();
                        $('.wrap > hr.wp-header-end.extra').remove();
                        if (response == 'done' || (response.success && response.data == 'done')) {
                            $('.wrap > hr.wp-header-end').after('<div class="update-message notice inline notice-alt updated-message notice-success"><p>✅ ' + zoom_type + ' pulled from Zoom successfully! <a href="' + window.location.href + '">Reload page</a> to see changes.</p></div><hr class="wp-header-end extra">');
                        } else {
                            if (response && response.data && response.data.length > 0) {
                                $('.wrap > hr.wp-header-end').after('<div class="update-message notice inline notice-alt notice-error"><p>' + response.data + '</p></div><hr class="wp-header-end extra">');
                            } else {
                                $('.wrap > hr.wp-header-end').after('<div class="update-message notice inline notice-alt notice-error"><p>❌ Pull from Zoom failed. Please try again!</p></div><hr class="wp-header-end extra">');
                            }
                        }
                    },
                    error: function (request, status, error) {
                        $('.wrap > .update-message').remove();
                        $('.wrap > hr.wp-header-end.extra').remove();
                        $('.wrap > hr.wp-header-end').after('<div class="update-message notice inline notice-alt notice-error"><p>❌ Pull from Zoom failed: ' + request.responseText + '</p></div><hr class="wp-header-end extra">');
                    }
                });
            }
        });
    });
})(jQuery);