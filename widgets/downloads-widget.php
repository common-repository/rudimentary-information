<?php
/*
================================================================================================
Rudimentary Information - theme-widget.php
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
 2.0 - Rudimentary Information Themes Widget Class Setup
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
 2.0 - Rudimentary Information Themes Widget Class Setup
================================================================================================
*/
class Rudimentary_Information_Downloads_Widget extends WP_Widget {
    /*
    ============================================================================================
     Constructor function that is used to add its widget form. This widget is used to ouput theme
     information obtained from the WordPress.org Themes API and formats nicely in presented table.
    ============================================================================================
    */
    public function __construct() {
        $widget_options = array(
            'classname' => 'download_widget',
            'description' => __('A widget to allow download if a slug is set through the meta box.', 'rudimentary-information'),
        );
        parent::__construct('download_widget', 'Downloads', $widget_options);
    }
    
    /*
    ============================================================================================
    A method to output a form for a widget in the widget admin area. This should only contain the
    title and nothin else. 
    ============================================================================================
    */
	public function form($instance) {
		$title = !empty($instance['title']) ? $instance['title'] : '';
		?>
		<p>
            <label for="<?php echo esc_attr($this->get_field_id('title')); ?>"><?php esc_html_e('Title: ', 'rudimentary-information'); ?></label><br />
            <input class="widefat" type="text" id="<?php echo esc_attr($this->get_field_id('title')); ?>" name="<?php echo esc_attr($this->get_field_name('title')); ?>" value="<?php echo esc_attr($title); ?>">
		</p>
		<?php 
	}
    
    /*
    ============================================================================================
    This is the main render function for the widget. It handles outputs for the markups and getting
    any data for use in that markup. Echos the $html once it is generated. Returns false if it fails.
    ============================================================================================
    */
	public function widget( $args, $instance ) {
        global $post;
        $slug = get_post_meta($post->ID, '_theme_slug', true);
        
        if (!$slug) {
            return false;
        }
        
        $downloads_info = Rudimentary_Information_Themes::get_theme_info($slug);
        
        if (is_object($downloads_info)) {
            $fields = array(
                'download_link'  => esc_html__('Downloaded', 'rudimentary-information'),
                'preview_url'   => esc_html__('Preview', 'rudimentary-information')
            );
        }
        
        $fields = apply_filters('theme_slug_field', $fields);
        
        ob_start();
        echo $args['before_widget'] . $args['before_title'] . apply_filters('widget_title', $instance['title']) . $args['after_title'];
        ?>
        <div class="download-preview">
            <?php foreach ($fields as $key => $field) { ?>
                <?php switch ($key) {
                    case 'preview_url':
                        $text = esc_html__('Preview', 'rudimentary-information');
                        break;
                    case 'homepage':
                        $text = $downloads_info->name;
                        break;
                    case 'download_link':
                        $text = esc_html__('Download', 'rudimentary-information');
                        break;
                    default:
                        $text = $downloads_info->name;
                }
                ?>
                <a class="download-preview-click" href="<?php echo esc_url($downloads_info->$key ); ?>"><?php esc_html_e(apply_filters('theme_slug_link_text', $text, $downloads_info, $key)); ?></a>
            <?php } ?>
        </div>
        <?php
        echo $args['after_widget'];
        // get all of the buffered content that we injected/echoed.
        $html = ob_get_clean();
        // echo the full $html.
        echo $html;
	}
    
	public function update($new_instance, $old_instance) {
		$instance = array();
		$instance['title'] = (!empty($new_instance['title'])) ? strip_tags($new_instance['title']) : '';
		return $instance;
	}
}
