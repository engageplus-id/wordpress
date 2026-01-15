<?php
/**
 * EngagePlus WordPress Widget
 *
 * @package EngagePlus
 * @since 1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * EngagePlus Widget Class
 */
class EngagePlus_Widget extends WP_Widget {
    
    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct(
            'engageplus_widget',
            __('EngagePlus Login', 'engageplus'),
            array(
                'description' => __('Add EngagePlus social login to your site.', 'engageplus'),
                'classname' => 'widget-engageplus',
            )
        );
    }
    
    /**
     * Front-end display of widget
     */
    public function widget($args, $instance) {
        $title = !empty($instance['title']) ? $instance['title'] : '';
        $container_id = !empty($instance['container_id']) ? $instance['container_id'] : 'engageplus-widget-' . $this->id;
        $button_text = !empty($instance['button_text']) ? $instance['button_text'] : '';
        $theme = !empty($instance['theme']) ? $instance['theme'] : '';
        $hide_logged_in = !empty($instance['hide_logged_in']);
        $show_logout = !empty($instance['show_logout']);
        
        // Hide for logged-in users if configured
        if ($hide_logged_in && is_user_logged_in()) {
            if ($show_logout) {
                echo $args['before_widget'];
                if ($title) {
                    echo $args['before_title'] . apply_filters('widget_title', $title) . $args['after_title'];
                }
                printf(
                    '<div class="engageplus-logout-wrapper"><a href="%s" class="engageplus-logout-btn">%s</a></div>',
                    esc_url(wp_logout_url(get_permalink())),
                    esc_html__('Logout', 'engageplus')
                );
                echo $args['after_widget'];
            }
            return;
        }
        
        // Build shortcode attributes
        $shortcode_atts = array(
            'id="' . esc_attr($container_id) . '"',
            'hide_logged_in="' . ($hide_logged_in ? 'true' : 'false') . '"',
            'show_logout="' . ($show_logout ? 'true' : 'false') . '"',
        );
        
        if ($button_text) {
            $shortcode_atts[] = 'button_text="' . esc_attr($button_text) . '"';
        }
        
        if ($theme) {
            $shortcode_atts[] = 'theme="' . esc_attr($theme) . '"';
        }
        
        echo $args['before_widget'];
        
        if ($title) {
            echo $args['before_title'] . apply_filters('widget_title', $title) . $args['after_title'];
        }
        
        echo do_shortcode('[engageplus ' . implode(' ', $shortcode_atts) . ']');
        
        echo $args['after_widget'];
    }
    
    /**
     * Back-end widget form
     */
    public function form($instance) {
        $title = !empty($instance['title']) ? $instance['title'] : '';
        $container_id = !empty($instance['container_id']) ? $instance['container_id'] : 'engageplus-widget';
        $button_text = !empty($instance['button_text']) ? $instance['button_text'] : '';
        $theme = !empty($instance['theme']) ? $instance['theme'] : '';
        $hide_logged_in = !empty($instance['hide_logged_in']);
        $show_logout = !empty($instance['show_logout']);
        ?>
        
        <p>
            <label for="<?php echo esc_attr($this->get_field_id('title')); ?>"><?php esc_html_e('Title:', 'engageplus'); ?></label>
            <input class="widefat" id="<?php echo esc_attr($this->get_field_id('title')); ?>" name="<?php echo esc_attr($this->get_field_name('title')); ?>" type="text" value="<?php echo esc_attr($title); ?>">
        </p>
        
        <p>
            <label for="<?php echo esc_attr($this->get_field_id('container_id')); ?>"><?php esc_html_e('Container ID:', 'engageplus'); ?></label>
            <input class="widefat" id="<?php echo esc_attr($this->get_field_id('container_id')); ?>" name="<?php echo esc_attr($this->get_field_name('container_id')); ?>" type="text" value="<?php echo esc_attr($container_id); ?>">
            <small><?php esc_html_e('Unique ID for this widget instance.', 'engageplus'); ?></small>
        </p>
        
        <p>
            <label for="<?php echo esc_attr($this->get_field_id('button_text')); ?>"><?php esc_html_e('Button Text:', 'engageplus'); ?></label>
            <input class="widefat" id="<?php echo esc_attr($this->get_field_id('button_text')); ?>" name="<?php echo esc_attr($this->get_field_name('button_text')); ?>" type="text" value="<?php echo esc_attr($button_text); ?>" placeholder="<?php esc_attr_e('Use global setting', 'engageplus'); ?>">
        </p>
        
        <p>
            <label for="<?php echo esc_attr($this->get_field_id('theme')); ?>"><?php esc_html_e('Theme:', 'engageplus'); ?></label>
            <select class="widefat" id="<?php echo esc_attr($this->get_field_id('theme')); ?>" name="<?php echo esc_attr($this->get_field_name('theme')); ?>">
                <option value="" <?php selected($theme, ''); ?>><?php esc_html_e('Use global setting', 'engageplus'); ?></option>
                <option value="light" <?php selected($theme, 'light'); ?>><?php esc_html_e('Light', 'engageplus'); ?></option>
                <option value="dark" <?php selected($theme, 'dark'); ?>><?php esc_html_e('Dark', 'engageplus'); ?></option>
            </select>
        </p>
        
        <p>
            <input class="checkbox" type="checkbox" <?php checked($hide_logged_in); ?> id="<?php echo esc_attr($this->get_field_id('hide_logged_in')); ?>" name="<?php echo esc_attr($this->get_field_name('hide_logged_in')); ?>">
            <label for="<?php echo esc_attr($this->get_field_id('hide_logged_in')); ?>"><?php esc_html_e('Hide for logged-in users', 'engageplus'); ?></label>
        </p>
        
        <p>
            <input class="checkbox" type="checkbox" <?php checked($show_logout); ?> id="<?php echo esc_attr($this->get_field_id('show_logout')); ?>" name="<?php echo esc_attr($this->get_field_name('show_logout')); ?>">
            <label for="<?php echo esc_attr($this->get_field_id('show_logout')); ?>"><?php esc_html_e('Show logout button for logged-in users', 'engageplus'); ?></label>
        </p>
        
        <?php
    }
    
    /**
     * Sanitize widget form values
     */
    public function update($new_instance, $old_instance) {
        $instance = array();
        $instance['title'] = !empty($new_instance['title']) ? sanitize_text_field($new_instance['title']) : '';
        $instance['container_id'] = !empty($new_instance['container_id']) ? sanitize_html_class($new_instance['container_id']) : 'engageplus-widget';
        $instance['button_text'] = !empty($new_instance['button_text']) ? sanitize_text_field($new_instance['button_text']) : '';
        $instance['theme'] = !empty($new_instance['theme']) && in_array($new_instance['theme'], array('light', 'dark')) ? $new_instance['theme'] : '';
        $instance['hide_logged_in'] = !empty($new_instance['hide_logged_in']);
        $instance['show_logout'] = !empty($new_instance['show_logout']);
        
        return $instance;
    }
}

