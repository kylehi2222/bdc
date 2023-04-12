<?php

if (!defined('WPINC')) {
    die;
}

if (!function_exists('bgc_plugin_action_links')) {
	add_filter('plugin_action_links_bodygraphchart/bodygraphchart.php', 'bgc_plugin_action_links');

	function bgc_plugin_action_links( $links ) {
		$url = esc_url(add_query_arg('page', 'bgc-settings', get_admin_url() . 'options-general.php'));

		array_push($links, '<a href="' . $url . '">' .  __('Settings', 'bgc') . '</a>');

		return $links;
	}
}

if (!function_exists('bgc_admin_menu')) {
	add_action('admin_menu', 'bgc_admin_menu');

	function bgc_admin_menu() {
		add_submenu_page('options-general.php', __('BodyGraphChart', 'wb'), __('BodyGraphChart', 'wb'), 'administrator', 'bgc-settings', 'bgc_settings_page');
	}
}

if (!function_exists('bgc_settings_page')) {
	function bgc_settings_page() {
		if (isset($_POST['submit'])) {
			update_option('_bgc_api_key', esc_attr($_POST['api_key']));

			$updated = true;
		}

		$api_key = get_option('_bgc_api_key');

		?>
		<div class="wrap">
			<h1><?php _e('BodyGraphChart Settings', 'wb'); ?></h1>
			<?php if (isset($updated)) : ?>
				<div id="setting-error-settings_updated" class="notice notice-success settings-error">
					<p><strong><?php _e('Settings saved.', 'wb'); ?></strong></p>
				</div>
			<?php endif; ?>
			<div style="float: left; width: 69%;">
				<form method="post" novalidate="novalidate">
					<table class="form-table" role="presentation">
						<tbody>
							<tr>
								<th scope="row">
									<label for="api_key"><?php _e('API Key', 'wb'); ?></label>
								</th>
								<td>
									<input type="password" name="api_key" value="<?php echo $api_key; ?>" id="api_key" class="regular-text">
									<p class="description">
										<a href="https://bodygraphchart.com/" target="_blank"><?php _e('Get your API key', 'bgc'); ?></a>
									</p>
								</td>
							</tr>
						</tbody>
					</table>
					<?php submit_button(); ?>
				</form>
			</div>
			<div style="float: right; width: 30%;">
				<h2><?php _e('Links', 'bgc'); ?></h2>
				<a href="https://bodygraphchart.com/" target="_blank">
					<?php _e('Homepage', 'bgc'); ?>
				</a>
				<br>
				<a href="https://bodygraphchart.com/docs/" target="_blank">
					<?php _e('Documentation', 'bgc'); ?>
				</a>
				<br>
				<a href="mailto:info@bodygraphchart.com" target="_blank">
					<?php _e('Support', 'bgc'); ?>
				</a>
				<br>
				<h2><?php _e('Shortcodes', 'bgc'); ?></h2>
				<strong><?php _e('Form Page:', 'bgc'); ?></strong>
				<br>
				<code>[bgc_form]</code>
				<br>
				<br>
				<strong><?php _e('Chart Page:', 'bgc'); ?></strong>
				<br>
				<code>[bgc_chart]</code>
			</div>
			<div class="clear"></div>
		</div>
		<?php
	}
}
