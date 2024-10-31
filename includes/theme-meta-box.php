<?php
/*
================================================================================================
Rudimentary Information - theme-meta-box.php
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
 2.0 - Theme Info Meta Box
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
 2.0 - Theme Info Meta Box
================================================================================================
*/
class Rudimentary_Information_Themes_Meta_Box {
    public static $instance = null;
    
    public static function init() {
        if (null === self::$instance) {
            self::$instance = new Rudimentary_Information_Themes_Meta_Box;
        }
        return self::$instance;
    }
    
    private function __construct() {
        add_action('add_meta_boxes', array($this, 'add_theme_slug_meta_box'));
        add_action('save_post', array($this, 'save_theme_slug_meta_box'));
    }
    
    public function add_theme_slug_meta_box($post_type) {
        $post_types = array('backdrop-portfolio');
        $post_types = apply_filters('theme_slug_meta_box_post_type', $post_types);
        
        if (in_array($post_type, $post_types, true)) {
            add_meta_box(
                'theme_slug_meta_box', esc_html__('Theme Slug', 'rudimentary-information', 'theme_slug_meta_box_nonce'), array($this, 'theme_slug_meta_box_content'), $post_type, 'side', 'high'
            );
        }
    }
    
    public function save_theme_slug_meta_box($post_id) {
		if (!isset($_POST['theme_slug_meta_box_nonce'])) {
			return $post_id;
		}
        
        $nonce = $_POST['theme_slug_meta_box_nonce'];
		if (!wp_verify_nonce($nonce, 'theme_slug_meta_box_inner_nonce')) {
			return $post_id;
		}
        
		if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
			return $post_id;
		}
        
		if ('page' == $_POST['post_type']) {
			if (!current_user_can('edit_page', $post_id)) {
				return $post_id;
			}
		} else {
			if (!current_user_can('edit_post', $post_id)) {
				return $post_id;
			}
		}
        
		$data_slug = sanitize_text_field(
            $_POST['theme_slug_field']);
		update_post_meta($post_id, '_theme_slug', $data_slug);
    }
    
	public function theme_slug_meta_box_content($post) {
		wp_nonce_field('theme_slug_meta_box_inner_nonce', 'theme_slug_meta_box_nonce');
		$slug = get_post_meta($post->ID, '_theme_slug', true);
		?>
		<label for="theme_slug_field">
			<?php esc_html_e('Please enter a theme slug to be attach to the Jetpack Portfolio Custom Post Type.', 'rudimentary-information'); ?>
		</label>
        <p>
		<input class="widefat" type="text" id="theme_slug_field" name="theme_slug_field" value="<?php echo esc_attr($slug); ?>" />
        </p>
		<?php
	}
    
}