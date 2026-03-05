(function ($) {
	'use strict';
	$(document).ready(function () {
		$('.stm_zooom_countdown').each(function () {
			let $this = $(this);
			let ts = $this.data('timer');
			ts = parseInt(ts) * 1000;
			$this.countdown({
				timestamp: ts,
				callback: function (days, hours, minutes, seconds) {
					let summaryTime = days + hours + minutes + seconds;

					if (days === 0 && hours === 0 && minutes === 0 && seconds === 1) {
						setTimeout( () => {
							location.reload();
						}, 2000);
					} else if ( summaryTime === 0 ) {
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