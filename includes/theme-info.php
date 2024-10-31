<?php
/*
================================================================================================
Rudimentary Information - theme-info.php
================================================================================================
This holds the main class that can be used to get information about a theme that comes from the
wordpress.org themes API. It caches calls on a theme by bases.

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
 2.0 - Rudimentary Information Themes Class Setup
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
class Rudimentary_Information_Themes {
    /*
    ============================================================================================
    $transient_base is used to hold data as a transient for a short time.
    ============================================================================================
    */
    public static $transient_base = 'Rudimentary_Information_Themes_';
    
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
            self::$instance = new Rudimentary_Information_Themes;
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
        Adds shortcode for getting individual pieces of theme info.
        ========================================================================================
        */
        add_shortcode('theme-info', array($this, 'get_with_shortcode'));
    }
    
    /*
    ============================================================================================
    Function used to either retrieve some theme information from the WordPress.org themes API or
    return a transient with the data if it already exists.
    ============================================================================================
    */
    public static function get_theme_info($slug = '') {
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
                $url = esc_url_raw('https://api.wordpress.org/themes/info/1.1/?action=theme_information&request[slug]=' . esc_attr($slug));
                
                /*
                ================================================================================
                Get transient name the base and slug.
                ================================================================================
                */
                $theme_transient = Rudimentary_Information_Themes::$transient_base . $slug;
                
                /*
                ================================================================================
                Get the expiery time on the transient.
                ================================================================================
                */
                $data_timeout = get_option('_transient_timeout_' . $theme_transient);
                
                /*
                ================================================================================
                data_timeout will exist if a transient data exists for this theme slug. Also it
                will test if it is expired.
                ================================================================================
                */
                if ($data_timeout && !$data_timeout < time()) {
                    /*
                    ============================================================================
                    Get the Transient data as it is saved and not expired.
                    ============================================================================
                    */
                    $theme_info = get_transient($theme_transient);
                    
                    /*
                    ============================================================================
                    Transient should hold a a json object.
                    ============================================================================
                    */
                    if (is_object($theme_info)) {
                        return $theme_info;
                    }
                } else {
                    /*
                    ============================================================================
                    If we have a valid url and an existing transient data for the theme information
                    and doesn't exist, or is it expired, then we will use URL to get a GET request
                    for a theme information json format.
                    ============================================================================
                    */
                    $theme_info = Rudimentary_Information_Themes::get_remote_theme_info($url, $slug);
                    return $theme_info;
                }
            }
        } else {
            return false;
        }
    }
    
    /*
    ============================================================================================
    Use wp_remote_get to make a request to WordPress.org Theme's API and asks for information
    about a specific theme by slug.
    ============================================================================================
    */
    public static function get_remote_theme_info($url = '', $slug) {
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
                    This should be a json object with theme information.
                    ============================================================================
                    */
					$theme_info = $response['body'];

                    /*
                    ============================================================================
                    Decode the json.
                    ============================================================================
                    */
					$theme_info = json_decode($theme_info);

                    /*
                    ============================================================================
                    Save this information as a transient for 24 hours.
                    ============================================================================
                    */
					$saved = set_transient(Rudimentary_Information_Themes::$transient_base . $slug, $theme_info, 60 * 60 * 24);

                    /*
                    ============================================================================
                    Return the full json object.
                    ============================================================================
                    */
					return $theme_info;
				}
			}
		}
	}
    
    /*
    ============================================================================================
    Function to generate some markup for a shortcode to output various pieces of information
    about a theme from json object.
    ============================================================================================
    */
    public function get_with_shortcode($atts = array()) {
        $defaults = array('slug' => '', 'field' => 'name');
        
        $atts = wp_parse_args($atts, $defaults);
        
        if ('' !== $atts['slug']) {
            
            /*
            ====================================================================================
            Sinc we have a slug then we try getting the theme information. The call here should 
            return a json object containing the theme information. It will either be pulled from
            a transient data or a get request to pull the information needed from the remote API.
            ====================================================================================
            */
            $theme_info = $this->get_theme_info($atts['slug']);
            
            /*
            ====================================================================================
            $theme_info should be an object (json object).
            ====================================================================================
            */
            if (is_object($theme_info)) {
                
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
                    $data = $theme_info->$field;
        
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
                            $html = '<span class="theme-info">' . absint($data) . '</span>';
                            break;
                        case 'esc_url':
                            $html = '<a href="' . esc_url($data) . '" class="theme-info" alt="' . esc_attr($theme_info->sections->description) . '">' . esc_html($theme_info->name) . '</a>';
                            break;
                        case 'number_format':
                            $downloaded = absint($data);
                            $downloaded_format = number_format($downloaded);
                            
                            $html = '<span class="theme-info">' . esc_html($downloaded_format) . '</span>';
                            break;
                        case 'strtotime':
                            $time = strtotime($data);
                            $format_date = date(get_option('date_format'), $time);
                            
                            $html = '<span class="theme-info">' . esc_html($format_date) . '</span>';
                            break;
                        default:
                            $html = '<span class="theme-info">' . esc_html($data) . '</span>';

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
	private function validate_field_id($field) {
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