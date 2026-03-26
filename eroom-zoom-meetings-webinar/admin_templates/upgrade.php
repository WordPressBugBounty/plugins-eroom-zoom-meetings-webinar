<?php
// phpcs:ignoreFile
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<style>
	.eroom-upgrade-page {
		max-width: 960px;
		padding: 0 20px 60px;
		font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
	}
	.eroom-upgrade-page h1 {
		font-size: 28px;
		font-weight: 700;
		margin: 32px 0 6px;
		color: #1e293b;
	}
	.eroom-upgrade-page .eroom-upgrade-subtitle {
		color: #64748b;
		font-size: 15px;
		margin: 0 0 36px;
	}

	/* Billing toggle */
	.eroom-billing-toggle {
		display: flex;
		align-items: center;
		gap: 14px;
		margin-bottom: 32px;
	}
	.eroom-billing-toggle .toggle-label {
		font-size: 14px;
		font-weight: 600;
		color: #475569;
		cursor: pointer;
	}
	.eroom-billing-toggle .toggle-label.active {
		color: #157ffc;
	}
	.eroom-billing-toggle .toggle-switch {
		position: relative;
		width: 48px;
		height: 26px;
	}
	.eroom-billing-toggle .toggle-switch input {
		opacity: 0;
		width: 0;
		height: 0;
	}
	.eroom-billing-toggle .toggle-track {
		position: absolute;
		inset: 0;
		background: #157ffc;
		border-radius: 13px;
		cursor: pointer;
		transition: background 0.25s;
	}
	.eroom-billing-toggle .toggle-track:before {
		content: "";
		position: absolute;
		height: 20px;
		width: 20px;
		left: 3px;
		top: 3px;
		background: #fff;
		border-radius: 50%;
		transition: transform 0.25s;
	}
	.eroom-billing-toggle input:checked + .toggle-track {
		background: #157ffc;
	}
	.eroom-billing-toggle input:checked + .toggle-track:before {
		transform: translateX(22px);
	}
	.eroom-billing-toggle .save-badge {
		background: #dcfce7;
		color: #16a34a;
		font-size: 11px;
		font-weight: 700;
		padding: 2px 8px;
		border-radius: 20px;
		letter-spacing: 0.3px;
	}

	/* Pricing grid */
	.eroom-pricing-grid {
		display: flex;
		gap: 22px;
		flex-wrap: wrap;
	}
	.eroom-pricing-card {
		flex: 1;
		min-width: 240px;
		background: #fff;
		border: 2px solid #e2e8f0;
		border-radius: 16px;
		padding: 28px 28px 24px;
		box-sizing: border-box;
		position: relative;
		transition: border-color 0.2s, box-shadow 0.2s;
	}
	.eroom-pricing-card.popular {
		border-color: #157ffc;
		box-shadow: 0 8px 32px rgba(21,127,252,0.15);
	}
	.eroom-pricing-card .popular-badge {
		position: absolute;
		top: -13px;
		left: 50%;
		transform: translateX(-50%);
		background: #157ffc;
		color: #fff;
		font-size: 11px;
		font-weight: 700;
		padding: 3px 14px;
		border-radius: 20px;
		white-space: nowrap;
		letter-spacing: 0.4px;
	}
	.eroom-pricing-card .tier-label {
		font-size: 13px;
		font-weight: 700;
		color: #94a3b8;
		text-transform: uppercase;
		letter-spacing: 1px;
		margin-bottom: 14px;
	}

	/* Pricing display */
	.eroom-pricing-card .price-block {
		margin-bottom: 8px;
	}
	/* Monthly price (shown above annual) */
	.eroom-pricing-card .monthly-equiv {
		font-size: 13px;
		color: #94a3b8;
		margin-bottom: 4px;
		min-height: 18px;
	}
	.eroom-pricing-card .annual-price {
		display: flex;
		align-items: flex-end;
		gap: 4px;
		line-height: 1;
	}
	.eroom-pricing-card .currency {
		font-size: 20px;
		font-weight: 700;
		color: #1e293b;
		padding-bottom: 4px;
	}
	.eroom-pricing-card .amount {
		font-size: 48px;
		font-weight: 800;
		color: #1e293b;
		line-height: 1;
	}
	.eroom-pricing-card .per-period {
		font-size: 13px;
		color: #94a3b8;
		padding-bottom: 6px;
		padding-left: 2px;
	}
	.eroom-pricing-card .billing-note {
		font-size: 12px;
		color: #94a3b8;
		margin: 4px 0 20px;
	}

	/* Lifetime block */
	.eroom-pricing-card .lifetime-block {
		background: #f8fafc;
		border: 1px dashed #cbd5e1;
		border-radius: 10px;
		padding: 14px 16px;
		margin-bottom: 20px;
	}
	.eroom-pricing-card .lifetime-block .lt-label {
		font-size: 11px;
		font-weight: 700;
		text-transform: uppercase;
		color: #94a3b8;
		letter-spacing: 0.8px;
		margin-bottom: 6px;
	}
	.eroom-pricing-card .lifetime-block .lt-price {
		font-size: 22px;
		font-weight: 800;
		color: #1e293b;
	}
	.eroom-pricing-card .lifetime-block .lt-note {
		font-size: 11px;
		color: #94a3b8;
		margin-top: 2px;
	}

	.eroom-pricing-card .sites-note {
		font-size: 13px;
		color: #475569;
		margin-bottom: 22px;
		font-weight: 500;
	}
	.eroom-pricing-card .cta-btn {
		display: block;
		width: 100%;
		box-sizing: border-box;
		text-align: center;
		padding: 12px 0;
		border-radius: 8px;
		font-size: 15px;
		font-weight: 700;
		text-decoration: none;
		transition: opacity 0.2s;
	}
	.eroom-pricing-card .cta-btn:hover {
		opacity: 0.88;
		text-decoration: none;
	}
	.eroom-pricing-card.popular .cta-btn {
		background: #157ffc;
		color: #fff;
	}
	.eroom-pricing-card:not(.popular) .cta-btn {
		background: #f1f5f9;
		color: #157ffc;
	}

	/* Features list */
	.eroom-pricing-card .features-list {
		list-style: none;
		margin: 20px 0 0;
		padding: 0;
		border-top: 1px solid #f1f5f9;
		padding-top: 18px;
	}
	.eroom-pricing-card .features-list li {
		font-size: 13px;
		color: #475569;
		padding: 4px 0;
		display: flex;
		align-items: center;
		gap: 8px;
	}
	.eroom-pricing-card .features-list li:before {
		content: "✓";
		color: #22c55e;
		font-weight: 700;
		flex-shrink: 0;
	}

	/* Hidden state management */
	.eroom-pricing-card .monthly-equiv,
	.eroom-pricing-card .billing-note {
		display: none;
	}
	body.show-annual .eroom-pricing-card .monthly-equiv,
	body.show-annual .eroom-pricing-card .billing-note {
		display: block;
	}
	/* When showing annual billing, update the displayed amounts */
	body.show-annual .price-annual { display: flex; }
	body.show-annual .price-monthly { display: none; }
	body.show-monthly .price-annual { display: none; }
	body.show-monthly .price-monthly { display: flex; }
	.price-annual { display: flex; }
	.price-monthly { display: none; }
</style>

<div class="eroom-upgrade-page">
	<h1>🚀 <?php esc_html_e( 'Upgrade to eRoom PRO', 'eroom-zoom-meetings-webinar' ); ?></h1>
	<p class="eroom-upgrade-subtitle"><?php esc_html_e( 'Unlock powerful features: WooCommerce meetings, recurring schedules, Google Meet, Microsoft Teams and more.', 'eroom-zoom-meetings-webinar' ); ?></p>

	<div class="eroom-billing-toggle">
		<span class="toggle-label active" id="eroom-lbl-monthly"><?php esc_html_e( 'Monthly', 'eroom-zoom-meetings-webinar' ); ?></span>
		<label class="toggle-switch">
			<input type="checkbox" id="eroom-billing-switch">
			<span class="toggle-track"></span>
		</label>
		<span class="toggle-label" id="eroom-lbl-annual"><?php esc_html_e( 'Annual', 'eroom-zoom-meetings-webinar' ); ?></span>
		<span class="save-badge"><?php esc_html_e( 'Save ~26%', 'eroom-zoom-meetings-webinar' ); ?></span>
	</div>

	<div class="eroom-pricing-grid">

		<?php
		$plans = array(
			array(
				'tier'     => __( '1 Site', 'eroom-zoom-meetings-webinar' ),
				'popular'  => false,
				'monthly'  => 9.49,
				'annual'   => 85,
				'lifetime' => 229,
				'url'      => 'https://eroomwp.com/',
			),
			array(
				'tier'     => __( '3 Sites', 'eroom-zoom-meetings-webinar' ),
				'popular'  => true,
				'monthly'  => 24.99,
				'annual'   => 200,
				'lifetime' => 600,
				'url'      => 'https://eroomwp.com/',
			),
			array(
				'tier'     => __( '10 Sites', 'eroom-zoom-meetings-webinar' ),
				'popular'  => false,
				'monthly'  => 39.99,
				'annual'   => 320,
				'lifetime' => 960,
				'url'      => 'https://eroomwp.com/',
			),
		);

		$features = array(
			__( 'All free features included', 'eroom-zoom-meetings-webinar' ),
			__( 'WooCommerce purchasable meetings', 'eroom-zoom-meetings-webinar' ),
			__( 'Recurring meetings', 'eroom-zoom-meetings-webinar' ),
			__( 'Google Meet integration', 'eroom-zoom-meetings-webinar' ),
			__( 'Microsoft Teams integration', 'eroom-zoom-meetings-webinar' ),
			__( 'Priority support', 'eroom-zoom-meetings-webinar' ),
		);

		foreach ( $plans as $plan ) :
			$monthly_equiv = '$' . number_format( $plan['annual'] / 12, 2 );
		?>
		<div class="eroom-pricing-card <?php echo $plan['popular'] ? 'popular' : ''; ?>">
			<?php if ( $plan['popular'] ) : ?>
				<div class="popular-badge"><?php esc_html_e( '⭐ Most Popular', 'eroom-zoom-meetings-webinar' ); ?></div>
			<?php endif; ?>

			<div class="tier-label"><?php echo esc_html( $plan['tier'] ); ?></div>

			<div class="price-block">
				<!-- Monthly price display -->
				<div class="annual-price price-monthly">
					<span class="currency">$</span>
					<span class="amount"><?php echo number_format( $plan['monthly'], 2 ); ?></span>
					<span class="per-period">/<?php esc_html_e( 'mo', 'eroom-zoom-meetings-webinar' ); ?></span>
				</div>

				<!-- Annual price display -->
				<div class="monthly-equiv">
					<?php printf( esc_html__( '%s /mo when billed monthly', 'eroom-zoom-meetings-webinar' ), '$' . number_format( $plan['monthly'], 2 ) ); ?>
				</div>
				<div class="annual-price price-annual">
					<span class="currency">$</span>
					<span class="amount"><?php echo esc_html( $plan['annual'] ); ?></span>
					<span class="per-period">/<?php esc_html_e( 'yr', 'eroom-zoom-meetings-webinar' ); ?></span>
				</div>
				<div class="billing-note">
					<?php printf( esc_html__( 'That\'s %s/mo — billed annually', 'eroom-zoom-meetings-webinar' ), $monthly_equiv ); ?>
				</div>
			</div>

			<div class="lifetime-block">
				<div class="lt-label"><?php esc_html_e( 'Lifetime', 'eroom-zoom-meetings-webinar' ); ?></div>
				<div class="lt-price">$<?php echo esc_html( $plan['lifetime'] ); ?></div>
				<div class="lt-note"><?php esc_html_e( 'One-time payment, forever', 'eroom-zoom-meetings-webinar' ); ?></div>
			</div>

			<a href="<?php echo esc_url( $plan['url'] ); ?>" target="_blank" class="cta-btn">
				<?php esc_html_e( 'Get Started', 'eroom-zoom-meetings-webinar' ); ?> →
			</a>

			<ul class="features-list">
				<?php foreach ( $features as $feature ) : ?>
					<li><?php echo esc_html( $feature ); ?></li>
				<?php endforeach; ?>
			</ul>
		</div>
		<?php endforeach; ?>

	</div>
</div>

<script>
(function() {
	var $switch  = document.getElementById('eroom-billing-switch');
	var $lblMo   = document.getElementById('eroom-lbl-monthly');
	var $lblAn   = document.getElementById('eroom-lbl-annual');
	var $body    = document.body;

	$body.classList.add('show-monthly');

	$switch.addEventListener('change', function() {
		if (this.checked) {
			$body.classList.remove('show-monthly');
			$body.classList.add('show-annual');
			$lblMo.classList.remove('active');
			$lblAn.classList.add('active');
		} else {
			$body.classList.remove('show-annual');
			$body.classList.add('show-monthly');
			$lblAn.classList.remove('active');
			$lblMo.classList.add('active');
		}
	});
})();
</script>
