<?php
/*
================================================================================================
Rudimentary Information - plugin-info.php
================================================================================================
This holds the main class that can be used to get information about a plugin that comes from the
wordpress.org plugins API. It caches calls on a plugin by bases.

@package        Rudimentary Information Plugin
@copyright      Copyright (C) 2017. Benjamin Lu
@license        GNU General Public License v2 or later (http://www.gnu.org/licenses/gpl-2.0.html)
@author         Benjamin Lu (https://www.benjlu.com/)
================================================================================================
*/

/*
================================================================================================
Table of Content
================================================================================================
1.0 - Forbidden Access
2.0 - Rudimentary Information Plugins Class Setup
================================================================================================
*/

/*
================================================================================================
1.0 - Forbidden Access
================================================================================================
*/
if (!defined('ABSPATH')) { 
exit;
}

/*
================================================================================================
2.0 - Rudimentary Information Class Setup
================================================================================================
*/
class Rudimentary_Information_Plugins {
/*
============================================================================================
$transient_base is used to hold data as a transient for a short time.
============================================================================================
*/
public static $transient_base = 'Rudimentary_Information_Plugins_';

/*
============================================================================================
This should hold the current instance of this class.
============================================================================================
*/
public static $instance = null;

/*
============================================================================================
Initiates the class as singleton.
============================================================================================
*/
public static function init() {
    if (null === self::$instance) {
        self::$instance = new Rudimentary_Information_Plugins;
    }
    return self::$instance;
}

/*
============================================================================================
This construct function helps to add stuff such as scripts, shortcodes and widgets.
============================================================================================
*/
private function __construct() {
    /*
    ========================================================================================
    Adds shortcode for getting individual pieces of plugin info.
    ========================================================================================
    */
    add_shortcode('plugin-info', array($this, 'get_with_shortcode'));
}

/*
============================================================================================
Function used to either retrieve some plugin information from the WordPress.org plugins API or
return a transient with the data if it already exists.
============================================================================================
*/
public static function get_plugin_info($slug = '') {
    /*
    ========================================================================================
    To operate we need to have a slug that isn't an empty string.
    ========================================================================================
    */
    if ('' !== $slug) {
        if ($slug) {
            /*
            ================================================================================
            If we have a slug then form our url.
            ================================================================================
            */
            $url = esc_url_raw('https://api.wordpress.org/plugins/info/1.1/?action=plugin_information&request[slug]=' . esc_attr($slug));

            /*
            ================================================================================
            Get transient name the base and slug.
            ================================================================================
            */
            $plugin_transient = Rudimentary_Information_Plugins::$transient_base . $slug;

            /*
            ================================================================================
            Get the expiery time on the transient.
            ================================================================================
            */
            $data_timeout = get_option('_transient_timeout_' . $plugin_transient);

            /*
            ================================================================================
            data_timeout will exist if a transient data exists for this plugin slug. Also it
            will test if it is expired.
            ================================================================================
            */
            if ($data_timeout && !$data_timeout < time()) {
                /*
                ============================================================================
                Get the Transient data as it is saved and not expired.
                ============================================================================
                */
                $plugin_info = get_transient($plugin_transient);

                /*
                ============================================================================
                Transient should hold a a json object.
                ============================================================================
                */
                if (is_object($plugin_info)) {
                    return $plugin_info;
                }
            } else {
                /*
                ============================================================================
                If we have a valid url and an existing transient data for the plugin information
                and doesn't exist, or is it expired, then we will use URL to get a GET request
                for a plugin information json format.
                ============================================================================
                */
                $plugin_info = Rudimentary_Information_Plugins::get_remote_plugin_info($url, $slug);
                return $plugin_info;
            }
        }
    } else {
        return false;
    }
}

/*
============================================================================================
Use wp_remote_get to make a request to WordPress.org plugin's API and asks for information
about a specific plugin by slug.
============================================================================================
*/
public static function get_remote_plugin_info($url = '', $slug) {
    if ($url && $slug) {
        /*
        ====================================================================================
        Since we ahve a valid URL, make a get request.
        ====================================================================================
        */
        $response = wp_remote_get($url);

        /*
        ====================================================================================
        The response should be in an array.
        ====================================================================================
        */
        if (is_array($response)) {

            /*
            ================================================================================
            First check if we got a status code of 200 = success.
            ================================================================================
            */
            if (200 === $response['response']['code']) {
                /*
                ============================================================================
                This should be a json object with plugin information.
                ============================================================================
                */
                $plugin_info = $response['body'];

                /*
                ============================================================================
                Decode the json.
                ============================================================================
                */
                $plugin_info = json_decode($plugin_info);

                /*
                ============================================================================
                Save this information as a transient for 24 hours.
                ============================================================================
                */
                $saved = set_transient(Rudimentary_Information_Plugins::$transient_base . $slug, $plugin_info, 60 * 60 * 24);

                /*
                ============================================================================
                Return the full json object.
                ============================================================================
                */
                return $plugin_info;
            }
        }
    }
}

/*
============================================================================================
Function to generate some markup for a shortcode to output various pieces of information
about a plugin from json object.
============================================================================================
*/
public function get_with_shortcode($atts = array()) {
    $defaults = array('slug' => '', 'field' => 'name');

    $atts = wp_parse_args($atts, $defaults);

    if ('' !== $atts['slug']) {

        /*
        ====================================================================================
        Sinc we have a slug then we try getting the plugin information. The call here should 
        return a json object containing the plugin information. It will either be pulled from
        a transient data or a get request to pull the information needed from the remote API.
        ====================================================================================
        */
        $plugin_info = $this->get_plugin_info($atts['slug']);

        /*
        ====================================================================================
        $plugin_info should be an object (json object).
        ====================================================================================
        */
        if (is_object($plugin_info)) {

            /*
            ================================================================================
            This should confirm that we have a field string passed.
            ================================================================================
            */
            if ($atts['field']) {

                /*
                ============================================================================
                Check that the filled strings passed is a valid field key. 
                ============================================================================
                */
                $field = $atts['field'];
                if (!$this->validate_field_id($field)) {

                    /*
                    ========================================================================
                    If data is not valid, return false.
                    ========================================================================
                    */
                    return false;
                }

                /*
                ============================================================================
                Gets the specific fields contents for use directly later. 
                ============================================================================
                */
                $data = $plugin_info->$field;

                /*
                ============================================================================
                Depending on the type of the data getting output, we have a sanitization 
                function that allows to output methods may be different. 

                This switch statement decides on the appropriate sanitization. Defaults to
                esc_html.
                ============================================================================
                */
                switch ($atts['field']) {
                    case 'preview_url':
                    case 'screenshot_url':
                    case 'homepage':
                    case 'download_link':
                        $sanitizer = 'esc_url';
                        break;
                    case 'rating':
                    case 'num_ratings':
                        $sanitizer = 'absint';
                        break;
                    case 'downloaded':
                        $sanitizer = 'number_format';
                        break;
                    case 'last_updated':
                        $sanitizer = 'strtotime';
                        break;
                    default:
                        $sanitizer = 'esc_html';
                }

                /*
                ============================================================================
                The kind of sanitizer used to let us know the specific html markup and
                sanitization filters to use before returning the markup to the shortcode
                ============================================================================
                */
                switch ($sanitizer) {
                    case 'absint':
                        $html = '<span class="plugin-info">' . absint($data) . '</span>';
                        break;
                    case 'esc_url':
                        $html = '<a href="' . esc_url($data) . '" class="plugin-info" alt="' . esc_attr($plugin_info->sections->description) . '">' . esc_html($plugin_info->name) . '</a>';
                        break;
                    case 'number_format':
                        $downloaded = absint($data);
                        $downloaded_format = number_format($downloaded);

                        $html = '<span class="plugin-info">' . esc_html($downloaded_format) . '</span>';
                        break;
                    case 'strtotime':
                        $time = strtotime($data);
                        $format_date = date(get_option('date_format'), $time);

                        $html = '<span class="plugin-info">' . esc_html($format_date) . '</span>';
                        break;
                    default:
                        $html = '<span class="plugin-info">' . esc_html($data) . '</span>';

                }

                /*
                ============================================================================
                If we are able to generate a string of html then return it or else return
                false for failure.
                ============================================================================
                */
                if ($html) {
                    return $html;
                }
                else {
                    return false;
                }
            }
        }
    }
}

/*
============================================================================================
An array that supports field keys.
============================================================================================
*/
private function valid_field_ids() {

    /*
    ========================================================================================
    Adds the ability to get sections, descriptions, and tags in an array.
    ========================================================================================
    */
    $array = array(
        'name',
        'slug',
        'version',
        'preview_url',
        'author',
        'screenshot_url',
        'rating',
        'num_ratings',
        'downloaded',
        'last_updated',
        'homepage',
        'download_link',
    );
    return $array;
}

/*
============================================================================================
Validate a field key string against the array of valid keys.
============================================================================================
*/
private function validate_field_id( $field ) {
    /*
    ========================================================================================
    If the key is in the valid keys array then it is valid, otherwise it is invalid and should
    fail.
    ========================================================================================
    */
    if (in_array($field, $this->valid_field_ids(), true)) {
        return true;
    } else {
        return false;
    }
}
}