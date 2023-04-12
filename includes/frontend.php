<?php

if (!defined('WPINC')) {
    die;
}

if (!function_exists('bgc_template_redirect')) {
	add_action('template_redirect', 'bgc_template_redirect');

	function bgc_template_redirect() {
		if (session_status() === PHP_SESSION_NONE) {
			session_start();
		}

		if (isset($_REQUEST['bgc-generate'])) {
			$year = filter_input(INPUT_POST, '_year');
			$month = filter_input(INPUT_POST, '_month');
			$day = filter_input(INPUT_POST, '_day');
			$hour = filter_input(INPUT_POST, '_hour');
			$minutes = filter_input(INPUT_POST, '_minutes');
			$location = filter_input(INPUT_POST, '_location');
			$timezone = filter_input(INPUT_POST, '_timezone');

			$response = [
				'status' => 'error',
				'message' => __('Oops something went wrong, try again later.', 'bgc')
			];

			$data = wp_remote_get(add_query_arg([
				'api_key' => get_option('_bgc_api_key'),
				'date' => sprintf('%s-%s-%s %s:%s', $year, $month, $day, $hour, $minutes),
				'timezone' => $timezone
			], 'https://api.bodygraphchart.com/v210502/hd-data'));

			if (!is_wp_error($data)) {
				$data = json_decode(wp_remote_retrieve_body($data));

				if (isset($data->Properties)) {
					global $wpdb;

					$target_page = '/';

					$results = $wpdb->get_results("SELECT ID FROM {$wpdb->posts} WHERE post_content LIKE '%[bgc_chart]%' AND post_status = 'publish' LIMIT 1");

					if ($results) {
						$target_page = get_permalink($results[0]->ID);
					}

					$_SESSION['_bgc_data'] = $data;

					$response = [
						'status' => 'success',
						'redirect_to' => $target_page
					];
				}
			}

			wp_send_json($response);
		}
	}
}

if (!function_exists('bgc_form_shortcode')) {
	add_shortcode('bgc_form', 'bgc_form_shortcode');

	function bgc_form_shortcode() {
		ob_start();

		$api_key = get_option('_bgc_api_key');

		include_once BGC_PLUGIN_DIR . 'templates/form.php';

		$template = ob_get_contents();

		ob_end_clean();

		return $template;
	}
}

if (!function_exists('bgc_chart_shortcode')) {
	add_shortcode('bgc_chart', 'bgc_chart_shortcode');

	function bgc_chart_shortcode() {
		ob_start();

		$data = $_SESSION['_bgc_data'];

		if (!isset($data->Properties)) {
			wp_redirect(home_url('/'));

			exit;
		}

		$data = json_encode($data);

		include_once BGC_PLUGIN_DIR . 'templates/chart.php';

		$template = ob_get_contents();

		ob_end_clean();

		return $template;
	}
}

if (!function_exists('bgc_wp_enqueue_scripts')) {
	add_action('wp_enqueue_scripts', 'bgc_wp_enqueue_scripts');

	function bgc_wp_enqueue_scripts() {
		global $post;

		if (is_a($post, 'WP_Post') && (has_shortcode($post->post_content, 'bgc_form') || has_shortcode($post->post_content, 'bgc_chart'))) {
			wp_enqueue_style('bgc-selectize', BGC_PLUGIN_URL . 'assets/css/selectize.css');
			wp_enqueue_style('bgc-typeahead', BGC_PLUGIN_URL . 'assets/css/typeahead.css');
			wp_enqueue_style('bgc-hd', BGC_PLUGIN_URL . 'assets/css/hd.css');

			wp_enqueue_script('bgc-selectize', BGC_PLUGIN_URL . 'assets/js/selectize.js', array('jquery'));
			wp_enqueue_script('bgc-typeahead', BGC_PLUGIN_URL . 'assets/js/typeahead.bundle.js');
			wp_enqueue_script('bgc-hd', BGC_PLUGIN_URL . 'assets/js/hd.js');
		}
	}
}
