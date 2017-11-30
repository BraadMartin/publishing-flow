<script type="text/html" id="tmpl-pf-post-info">
	<div class="pf-post-info-section">
		<div class="pf-post-info-publish-date">
			<h3>{{ data.publishDateLabel }}</h3>
			<span class="dashicons dashicons-calendar-alt pf-calendar-icon"></span>
			<p class="pf-post-info pf-publish-date">
				{{ data.publishedOnLabel }}
				<strong>{{ data.dateLabel }}</strong>
			</p>
		</div>
		<# if ( data.previewContexts ) { #>
		<div class="pf-post-info-preview-contexts-wrap">
			<h3>{{ data.previewContextsLabel }}</h3>
			<p class="pf-post-info pf-preview-contexts-info">
				{{ data.previewContextsInfoLabel }}
			</p>
			<select class="pf-post-info-preview-contexts">
				<# _.each( data.previewContexts, function( value, key, index ) { #>
				<option value="{{ key }}">{{ value.label }}</option>
				<# } ); #>
			</select>
		</div>
		<# } #>
	</div>
</script>
