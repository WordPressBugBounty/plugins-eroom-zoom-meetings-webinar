<?php
// phpcs:ignoreFile
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$addons = array(
	array(
		'slug'        => 'stm_zoom_woo',
		'name'        => esc_html__( 'Purchasable Meetings', 'eroom-zoom-meetings-webinar' ),
		'description' => esc_html__( 'Turn your meetings into digital products and make them purchasable for your customers.', 'eroom-zoom-meetings-webinar' ),
		'color'       => '#d3e5ff',
	),
	array(
		'slug'        => 'stm_zoom_recurring',
		'name'        => esc_html__( 'Recurring Meetings', 'eroom-zoom-meetings-webinar' ),
		'description' => esc_html__( 'Increase engagement and productivity with meetings repeatable on a daily, weekly, or monthly basis.', 'eroom-zoom-meetings-webinar' ),
		'color'       => '#d3ffdd',
	),
	array(
		'slug'        => 'stm_google_meet',
		'name'        => esc_html__( 'Google Meet Integration', 'eroom-zoom-meetings-webinar' ),
		'description' => esc_html__( 'Schedule and manage Google Meet video conferences directly from your WordPress dashboard.', 'eroom-zoom-meetings-webinar' ),
		'color'       => '#fef6e0',
	),
	array(
		'slug'        => 'stm_microsoft_teams',
		'name'        => esc_html__( 'Microsoft Teams Integration', 'eroom-zoom-meetings-webinar' ),
		'description' => esc_html__( 'Connect Microsoft Teams to your WordPress site and manage team meetings with ease.', 'eroom-zoom-meetings-webinar' ),
		'color'       => '#e8ebf5',
	),
);
?>

<style>
	.eroom-pro-addons-locked {
		max-width: 1100px;
		padding: 0 20px;
	}
	.eroom-pro-addons-locked h1 {
		padding: 30px 0 10px;
		margin: 0 0 8px;
		font-size: 26px;
		font-weight: 700;
	}
	.eroom-pro-addons-locked .eroom-pro-banner {
		display: flex;
		align-items: center;
		gap: 14px;
		background: linear-gradient(135deg, #157ffc 0%, #0d5fd4 100%);
		color: #fff;
		border-radius: 12px;
		padding: 20px 28px;
		margin-bottom: 30px;
		box-shadow: 0 4px 18px rgba(21,127,252,0.25);
	}
	.eroom-pro-banner .banner-text {
		flex: 1;
		font-size: 15px;
		line-height: 1.5;
	}
	.eroom-pro-banner .banner-text strong {
		display: block;
		font-size: 18px;
		margin-bottom: 4px;
	}
	.eroom-pro-banner a.banner-btn {
		display: inline-block;
		background: #fff;
		color: #157ffc;
		font-weight: 700;
		font-size: 14px;
		padding: 10px 22px;
		border-radius: 6px;
		text-decoration: none;
		white-space: nowrap;
		transition: opacity 0.2s;
	}
	.eroom-pro-banner a.banner-btn:hover {
		opacity: 0.85;
	}
	.eroom-addons-grid {
		display: flex;
		flex-wrap: wrap;
		gap: 20px;
		margin-top: 10px;
	}
	.eroom-addon-card {
		width: 280px;
		border-radius: 12px;
		box-shadow: 0 6px 20px rgba(0,0,0,0.09);
		overflow: hidden;
		background: #fff;
		position: relative;
	}
	.eroom-addon-card .card-header {
		height: 110px;
		display: flex;
		align-items: center;
		justify-content: center;
		font-size: 40px;
		position: relative;
	}
	.eroom-addon-card .card-body {
		padding: 22px 22px 18px;
	}
	.eroom-addon-card .addon-name {
		font-size: 17px;
		font-weight: 700;
		margin-bottom: 8px;
		color: #1e293b;
	}
	.eroom-addon-card .addon-desc {
		font-size: 13px;
		color: #55687a;
		line-height: 1.55;
		margin-bottom: 18px;
	}
	.eroom-addon-card .addon-locked-row {
		display: flex;
		align-items: center;
		gap: 8px;
	}
	.eroom-addon-card .lock-badge {
		display: inline-flex;
		align-items: center;
		gap: 5px;
		background: #f1f5f9;
		border: 1px solid #e2e8f0;
		border-radius: 6px;
		padding: 5px 10px;
		font-size: 12px;
		font-weight: 600;
		color: #64748b;
	}
	.eroom-addon-card .lock-badge svg {
		flex-shrink: 0;
	}
	.eroom-addon-card .unlock-link {
		font-size: 12px;
		color: #157ffc;
		text-decoration: none;
		font-weight: 600;
	}
	.eroom-addon-card .unlock-link:hover {
		text-decoration: underline;
	}
	/* Overlay lock on card header */
	.eroom-addon-card .card-header .lock-overlay {
		position: absolute;
		top: 10px;
		right: 10px;
		background: rgba(0,0,0,0.35);
		border-radius: 50%;
		width: 30px;
		height: 30px;
		display: flex;
		align-items: center;
		justify-content: center;
	}
	.eroom-addon-card .card-header .lock-overlay svg {
		color: #fff;
	}
</style>

<div class="eroom-pro-addons-locked">
	<h1><?php esc_html_e( 'eRoom PRO Addons', 'eroom-zoom-meetings-webinar' ); ?></h1>

	<div class="eroom-pro-banner">
		<img src="<?php echo esc_url( STM_ZOOM_URL . 'assets/images/zoom_icon.png' ); ?>" width="48" height="48" alt="eRoom PRO" style="border-radius:8px;flex-shrink:0;">
		<div class="banner-text">
			<strong><?php esc_html_e( 'Unlock all PRO Addons', 'eroom-zoom-meetings-webinar' ); ?></strong>
			<?php esc_html_e( 'Upgrade to eRoom PRO to enable and configure all addons below. Get advanced integrations, purchasable meetings, recurring schedules and much more.', 'eroom-zoom-meetings-webinar' ); ?>
		</div>
		<a href="https://eroomwp.com/" target="_blank" class="banner-btn">
			<?php esc_html_e( '🚀 Get eRoom PRO', 'eroom-zoom-meetings-webinar' ); ?>
		</a>
	</div>

	<div class="eroom-addons-grid">
		<?php foreach ( $addons as $addon ) : ?>
			<div class="eroom-addon-card">
				<div class="card-header" style="background-color:<?php echo esc_attr( $addon['color'] ); ?>;">
					<div class="lock-overlay">
						<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
							<path d="M18 8h-1V6c0-2.76-2.24-5-5-5S7 3.24 7 6v2H6c-1.1 0-2 .9-2 2v10c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V10c0-1.1-.9-2-2-2zm-6 9c-1.1 0-2-.9-2-2s.9-2 2-2 2 .9 2 2-.9 2-2 2zm3.1-9H8.9V6c0-1.71 1.39-3.1 3.1-3.1 1.71 0 3.1 1.39 3.1 3.1v2z"/>
						</svg>
					</div>
				</div>
				<div class="card-body">
					<div class="addon-name"><?php echo esc_html( $addon['name'] ); ?></div>
					<div class="addon-desc"><?php echo esc_html( $addon['description'] ); ?></div>
					<div class="addon-locked-row">
						<span class="lock-badge">
							<svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="currentColor">
								<path d="M18 8h-1V6c0-2.76-2.24-5-5-5S7 3.24 7 6v2H6c-1.1 0-2 .9-2 2v10c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V10c0-1.1-.9-2-2-2zm-6 9c-1.1 0-2-.9-2-2s.9-2 2-2 2 .9 2 2-.9 2-2 2zm3.1-9H8.9V6c0-1.71 1.39-3.1 3.1-3.1 1.71 0 3.1 1.39 3.1 3.1v2z"/>
							</svg>
							<?php esc_html_e( 'PRO Only', 'eroom-zoom-meetings-webinar' ); ?>
						</span>
						<a href="https://eroomwp.com/" target="_blank" class="unlock-link"><?php esc_html_e( 'Unlock →', 'eroom-zoom-meetings-webinar' ); ?></a>
					</div>
				</div>
			</div>
		<?php endforeach; ?>
	</div>
</div>
