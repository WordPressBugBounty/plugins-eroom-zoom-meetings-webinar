(function ($) {
	'use strict';
	$(document).ready(function () {
		$('.stm_zooom_countdown').each(function () {
			let $this = $(this);
			let ts = $this.data('timer');
			ts = parseInt(ts) * 1000;

			// If meeting is already in the past, show zeroes and a notice immediately
			if (ts < Date.now()) {
				$this.countdown({
					timestamp: ts,
					callback: function (days, hours, minutes, seconds) {
						// Already past: keep zeroes (library clamps to 0), show message
					}
				});
				if ($this.find('.stm-meeting-passed').length === 0) {
					$this.after('<p class="stm-meeting-passed" style="text-align:center;color:#888;font-size:13px;margin-top:6px;">&#9888; ' + (window.eroom_i18n && eroom_i18n.meeting_passed ? eroom_i18n.meeting_passed : 'This meeting has already passed.') + '</p>');
				}
				return;
			}

			$this.countdown({
				timestamp: ts,
				callback: function (days, hours, minutes, seconds) {
					let summaryTime = days + hours + minutes + seconds;

					if (days === 0 && hours === 0 && minutes === 0 && seconds === 1) {
						setTimeout( () => {
							location.reload();
						}, 2000);
					} else if ( summaryTime === 0 ) {
						// Meeting just ended – show zeroes and add passed message
						if ($this.find('.stm-meeting-passed').length === 0) {
							$this.after('<p class="stm-meeting-passed" style="text-align:center;color:#888;font-size:13px;margin-top:6px;">&#9888; ' + (window.eroom_i18n && eroom_i18n.meeting_passed ? eroom_i18n.meeting_passed : 'This meeting has already passed.') + '</p>');
						}
						return;
					}

					if (days > 99) {
						days.toString().split('').slice(0, -2).reverse().forEach(function (el, i) {
							let daysNumHtml = '<span class="position h1 days-number-'+i+'"><span class="digit static h1">'+el+'</span></span>';
							if ($this.find('.days-number-'+i).length === 0) {
								$this.find('.countDays .countdown_label').after(daysNumHtml);
							} else {
								$this.find('.days-number-'+i+' span').text(el);
							}
						});
					}
				}
			});
		})
	});
})(jQuery);