<script type="text/html" id="tmpl-pf-optional-meta">
	<div class="pf-optional-meta pf-field pf-key-{{ data.key }}" data-key="{{ data.key }}">
		<div class="pf-label">
			<h3>{{ data.label }}</h3>
			<# if ( data.value ) { #>
				<div class="pf-all-good pf-status">
					<span class="dashicons dashicons-yes"></span>
				</div>
			<# } else { #>
				<div class="pf-no-good pf-status">
					<span class="dashicons dashicons-no-alt"></span>
				</div>
			<# } #>
		</div>
		<# if ( data.value && data.showValue ) { #>
			<div class="pf-value pf-has-value">
				<p>{{ data.value }}</p>
			</div>
		<# } else if ( data.value && data.hasValue ) { #>
			<div class="pf-value pf-has-value">
				<p>{{ data.hasValue }}</p>
			</div>
		<# } else if ( data.noValue ) { #>
			<div class="pf-value pf-no-value">
				<p>{{ data.noValue }}</p>
			</div>
		<# } else if ( data.value ) { #>
			<div class="pf-value">
				<p>Value: {{ data.value }}</p>
			</div>
		<# } else { #>
			<div class="pf-value">
				<p>{{ data.value }}</p>
			</div>
		<# } #>
	</div>
</script>
