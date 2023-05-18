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

                    $target_page = 'https://humandesign.ai/onboarding/step-2/';

                    // Get the current user's ID
                    $user_id = get_current_user_id();

                    // Save the entire data object to user meta
                    update_user_meta($user_id, '_bgc_data', $data);

                    // Save individual form fields as separate user meta keys
                    update_user_meta($user_id, '_bgc_year', $year);
                    update_user_meta($user_id, '_bgc_month', $month);
                    update_user_meta($user_id, '_bgc_day', $day);
                    update_user_meta($user_id, '_bgc_hour', $hour);
                    update_user_meta($user_id, '_bgc_minutes', $minutes);
                    update_user_meta($user_id, '_bgc_location', $location);
                    update_user_meta($user_id, '_bgc_timezone', $timezone);

                    // Save individual properties to user meta
                    update_user_meta($user_id, '_bgc_birthdate', sprintf('%s-%s-%s %s:%s', $year, $month, $day, $hour, $minutes));

                    foreach ($data->Properties as $key => $value) {
                        // Check if the value is a string
                        if (is_string($value)) {
                            $value_to_store = $value;
                        } else {
                            // Convert the value to a JSON string
                            $value_to_store = json_encode($value);
                        }

                        // Save property as user meta
                        update_user_meta($user_id, '_bgc_' . strtolower($key), $value_to_store);
                    }

                    $response = [
                        'status' => 'success',
                        'redirect_to' => $target_page
                    ];
                }
            }

            wp_send_json($response);
            update_user_onboarding_status();
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

        // Get the current user's ID
        $user_id = get_current_user_id();

        // Retrieve the data from user meta
        $data = get_user_meta($user_id, '_bgc_data', true);

                if (!isset($data->Properties)) {
            wp_redirect(home_url('/'));
            exit;
        }

        // Decode individual properties from user meta
        foreach ($data->Properties as $key => $value) {
            $user_meta_value = get_user_meta($user_id, '_bgc_' . strtolower($key), true);

            if (!empty($user_meta_value)) {
                $decoded_value = is_string($user_meta_value) ? $user_meta_value : json_decode($user_meta_value, true);
                $data->Properties->{$key} = $decoded_value;
            }
        }

        $data = json_encode($data);

        include_once BGC_PLUGIN_DIR . 'templates/chart.php';

        $template = ob_get_contents();

        ob_end_clean();

        return $template;
    }
}

if (!function_exists('bgc_generate_chart')) {
    add_shortcode('bgc_generate_chart', 'bgc_generate_chart');

    function bgc_generate_chart($atts) {
    $atts = shortcode_atts(array(
        'year' => '',
        'month' => '',
        'day' => '',
        'hour' => '',
        'minutes' => '',
        'timezone' => ''
    ), $atts);

    $data = wp_remote_get(add_query_arg([
        'api_key' => get_option('_bgc_api_key'),
        'date' => sprintf('%s-%s-%s %s:%s', $atts['year'], $atts['month'], $atts['day'], $atts['hour'], $atts['minutes']),
        'timezone' => $atts['timezone']
    ], 'https://api.bodygraphchart.com/v210502/hd-data'));

    if (is_wp_error($data)) {
        return 'Error: API request failed. ' . $data->get_error_message();
    }

    $response = wp_remote_retrieve_body($data);

    if (empty($response)) {
        return 'Error: Empty API response.';
    }

    $data = json_decode($response);

    if (isset($data->Properties)) {


        $data = json_encode($data);
        ob_start();
        include_once BGC_PLUGIN_DIR . 'templates/chart.php';
        $template = ob_get_contents();
        ob_end_clean();

        return $template;
    } else {
        return 'Error: Could not fetch chart data. Response: ' . $response;
    }
}

}


// Human Design Property Shortcodes
function bgc_property_shortcode($atts, $content = null, $tag) {
    $atts = shortcode_atts(array(
        'user_id' => get_current_user_id(),
    ), $atts);

    $user_id = $atts['user_id'];
    $property_key = '_' . strtolower($tag);
    $property_value = get_user_meta($user_id, $property_key, true);

    return $property_value;
}

$bgc_properties = [
    'bgc_incarnationcross',
    'bgc_profile',
    'bgc_definition',
    'bgc_signature',
    'bgc_notselftheme',
    'bgc_innerauthority',
    'bgc_strategy',
    'bgc_type',
    'bgc_designdateutc',
    'bgc_birthdateutc',
    'bgc_birthdate',
    'bgc_birthdatelocal',
    'bgc_location',
    'bgc_environment',
    'bgc_channels',
    'bgc_sense',
    'bgc_digestion',
    'bgc_year',
    'bgc_month',
    'bgc_day',
    'bgc_timezone',
    'bgc_hour',
    'bgc_minutes',
];

foreach ($bgc_properties as $property) {
    add_shortcode($property, 'bgc_property_shortcode');
}
function bgc_check_type_and_redirect_shortcode($atts) {
    $atts = shortcode_atts(array(
        'user_id' => get_current_user_id(),
        'redirect_url' => 'https://humandesign.ai/onboarding/',
    ), $atts);

    $user_id = $atts['user_id'];
    $property_key = '_bgc_type';
    $property_value = get_user_meta($user_id, $property_key, true);

    if (empty($property_value)) {
        return '<script>window.location.href="' . esc_url($atts['redirect_url']) . '";</script>';
    }

    return '';
}
add_shortcode('bgc_check_type_and_redirect', 'bgc_check_type_and_redirect_shortcode');


if (!function_exists('bgc_wp_enqueue_scripts')) {
    add_action('wp_enqueue_scripts', 'bgc_wp_enqueue_scripts');

    function bgc_wp_enqueue_scripts() {
    // Enqueue styles and scripts unconditionally
    wp_enqueue_style('bgc-selectize', BGC_PLUGIN_URL . 'assets/css/selectize.css');
    wp_enqueue_style('bgc-typeahead', BGC_PLUGIN_URL . 'assets/css/typeahead.css');
    wp_enqueue_style('bgc-hd', BGC_PLUGIN_URL . 'assets/css/hd.css');

    wp_enqueue_script('bgc-selectize', BGC_PLUGIN_URL . 'assets/js/selectize.js', array('jquery'));
    wp_enqueue_script('bgc-typeahead', BGC_PLUGIN_URL . 'assets/js/typeahead.bundle.js');
    wp_enqueue_script('bgc-hd', BGC_PLUGIN_URL . 'assets/js/hd.js');
}
add_action('wp_enqueue_scripts', 'bgc_wp_enqueue_scripts');
}

?>