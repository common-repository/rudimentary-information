<?php
/*
================================================================================================
Rudimentary Information - plugin-meta-box.php
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
 2.0 - Theme Info Metabox
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
 2.0 - Theme Info Metabox
================================================================================================
*/
class Rudimentary_Information_Plugins_Meta_Box {
    public static $instance = null;
    
    public static function init() {
        if (null === self::$instance) {
            self::$instance = new Rudimentary_Information_Plugins_Meta_Box;
        }
        return self::$instance;
    }
    
    private function __construct() {
        add_action('add_meta_boxes', array($this, 'add_plugin_slug_meta_box'));
        add_action('save_post', array($this, 'save_plugin_slug_meta_box'));
    }
    
    public function add_plugin_slug_meta_box($post_type) {
        $post_types = array('backdrop-portfolio');
        $post_types = apply_filters('plugin_slug_meta_box_post_type', $post_types);
        
        if (in_array($post_type, $post_types, true)) {
            add_meta_box(
                'plugin_slug_meta_box', esc_html__('Plugin Slug', 'rudimentary-information', 'plugin_slug_meta_box_nonce'), array($this, 'plugin_slug_meta_box_content'), $post_type, 'side', 'high'
            );
        }
    }
    
    public function save_plugin_slug_meta_box($post_id) {
		if (!isset($_POST['plugin_slug_meta_box_nonce'])) {
			return $post_id;
		}
        
        $nonce = $_POST['plugin_slug_meta_box_nonce'];
		if (!wp_verify_nonce($nonce, 'plugin_slug_meta_box_inner_nonce')) {
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
            $_POST['plugin_slug_field']);
		update_post_meta($post_id, '_plugin_slug', $data_slug);
    }
    
	public function plugin_slug_meta_box_content($post) {
		wp_nonce_field('plugin_slug_meta_box_inner_nonce', 'plugin_slug_meta_box_nonce');
		$slug = get_post_meta($post->ID, '_plugin_slug', true);
		?>
		<label for="plugin_slug_field">
			<?php esc_html_e('Please enter a plugin slug to be attach to the Jetpack Portfolio Custom Post Type.', 'rudimentary-information'); ?>
		</label>
        <p>
		<input class="widefat" type="text" id="plugin_slug_field" name="plugin_slug_field" value="<?php echo esc_attr($slug); ?>" />
        </p>
		<?php
	}
    
}