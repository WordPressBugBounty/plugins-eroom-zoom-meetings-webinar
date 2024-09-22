<?php // Shortcode template ?>
<div class="shortcode_list">
	<div>
		<label>Single Meeting</label>
		<input type="text" disabled value='[stm_zoom_conference post_id="{post_id}"]'>
	</div>
	<div>
		<label>Single Webinar</label>
		<input type="text" disabled value='[stm_zoom_webinar post_id="{post_id}"]'>
	</div>
	<div>
		<label>Meetings Grid</label>
		<input type="text" disabled value='[stm_zoom_conference_grid post_type="stm-zoom" count="3" per_row="3"]'/>
	</div>
	<div>
		<label>Webinars Grid</label>
		<input type="text" disabled value='[stm_zoom_conference_grid post_type="stm-zoom-webinar" count="3" per_row="3"]'/>
	</div>
	<div>
		<label>Recurring Meeting Grid (Pro version)</label>
		<input type="text" disabled value='[stm_zoom_conference_grid post_type="product" recurring="1" count="3" per_row="3"]'/>
	</div>
	<div>
		<label>Meeting Product Grid (Pro version)</label>
		<input type="text" disabled value='[stm_zoom_conference_grid post_type="product" count="3" per_row="3"]'/>
	</div>
	<div>
		<label>Meeting Product Grid by category (Pro version)</label>
		<input type="text" disabled value='[stm_zoom_conference_grid post_type="product" category="{category_id}, {category_id}" count="3" per_row="3"]'/>
	</div>
	<div>
		<label>General Grid (stm-zoom, stm-zoom-webinar, product in a single grid)</label>
		<input type="text" disabled value='[stm_zoom_conference_grid post_type="stm-zoom, stm-zoom-webinar, product" count="3" per_row="3"]'/>
	</div>
</div>
