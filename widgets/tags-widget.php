<?php
/*
================================================================================================
Rudimentary Information - tags-widget.php
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
class Rudimentary_Information_Themes_Tags_Widget extends WP_Widget {
    /*
    ============================================================================================
     Constructor function that is used to add its widget form. This widget is used to ouput theme
     information obtained from the WordPress.org Themes API and formats nicely in presented table.
    ============================================================================================
    */
    public function __construct() {
        $widget_options = array(
            'classname' => 'tag_widget',
            'description' => __('A widget to output theme tags if a slug is set through the meta box.', 'rudimentary-information'),
        );
        parent::__construct('tag_widget', 'Theme Tags', $widget_options);
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
        
        $theme_info = Rudimentary_Information_Themes::get_theme_info($slug);
        
        ob_start();
        echo $args['before_widget'] . $args['before_title'] . apply_filters('widget_title', $instance['title']) . $args['after_title'];
        ?>
        <table class="theme-tags">
            <tbody>
                <?php foreach ($theme_info->tags as $tags) { ?>
                    <tr>
                        <th><i class="fa fa-check-square" aria-hidden="true"></i></th>
                        <td><?php echo esc_html($tags); ?></td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
        <?php
        echo $args['after_widget'];
        $html = ob_get_clean();
        echo $html;
	}
    
	public function update($new_instance, $old_instance) {
		$instance = array();
		$instance['title'] = (!empty($new_instance['title'])) ? strip_tags($new_instance['title']) : '';
		return $instance;
	}
}
