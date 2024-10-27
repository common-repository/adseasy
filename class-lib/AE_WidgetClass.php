<?php

/**
 *
 * Class Ads Easy Widget
 *
 * @ Ads Easy
 *
 * building the actual widget
 *
 */
class Ads_Easy_Widget extends A5_Widget {
	
	private static $options;
	
	function __construct() {
			
		$widget_opts = array( 'description' => __('You can show ads in your sidebars and other widgetareas with this widget. Define, on what kind of pages they will show up.', 'adseasy') );
		$control_opts = array ( 'width' => 400 );
		
		parent::__construct(false, $name = 'Ads Easy', $widget_opts, $control_opts);
		
		self::$options = get_option('ae_options');
	
	}

	function form($instance) {
		
		$defaults = array(
			'title' => NULL,
			'name' => NULL,
			'adblock' => NULL,
			'style' => NULL,
			'homepage' => 1,
			'frontpage' => false,
			'page' => false,
			'category' => 1,
			'single' => false,
			'date' => false,
			'archive' => false,
			'tag' => false,
			'attachment' => false,
			'taxonomy' => false,
			'author' => false,
			'search' => false,
			'not_found' => false,
			'login_page' => false,
			'logged_in' => 1,
			'search_engine' => 1,
			'normal' => 1
		);
		
		$instance = wp_parse_args( (array) $instance, $defaults );
		
		$title = esc_attr($instance['title']);
		$name = esc_attr($instance['name']);
		$adblock = $instance['adblock'];
		$style=esc_attr($instance['style']);
		$homepage = $instance['homepage'];
		$frontpage = $instance['frontpage'];
		$page = $instance['page'];
		$category = $instance['category'];
		$single = $instance['single'];
		$date = $instance['date'];
		$archive = $instance['archive'];
		$tag = $instance['tag'];
		$attachment = $instance['attachment'];
		$taxonomy = $instance['taxonomy'];
		$author = $instance['author'];
		$search = $instance['search'];
		$not_found = $instance['not_found'];
		$login_page = $instance['login_page'];
		$logged_in = $instance['logged_in'];
		$search_engine = $instance['search_engine'];
		$normal = $instance['normal'];
		
		$base_id = 'widget-'.$this->id_base.'-'.$this->number.'-';
		$base_name = 'widget-'.$this->id_base.'['.$this->number.']';
		
		a5_text_field($base_id.'name', $base_name.'[name]', $name, __('Title (will be displayed in blog):', 'adseasy'), array('space' => true, 'class' => 'widefat'));
		a5_text_field($base_id.'title', $base_name.'[title]', $title, __('Adname (internal widgettitle):', 'adseasy'), array('space' => true, 'class' => 'widefat'));
		parent::page_checkgroup($instance);
		a5_checkbox($base_id.'logged_in', $base_name.'[logged_in]', $logged_in, __('Show to logged in users.', 'adseasy'), array('space' => true));
		a5_checkbox($base_id.'search_engine', $base_name.'[search_engine]', $search_engine, __('Show to visitors, who come from search engines.', 'adseasy'), array('space' => true));
		a5_checkbox($base_id.'normal', $base_name.'[normal]', $normal, __('Show to other visitors.', 'adseasy'), array('space' => true));
		a5_textarea($base_id.'adblock', $base_name.'[adblock]', $adblock, __('Just paste the code of your ad here.', 'adseasy'), array('space' => true, 'style' => 'height: 60px;', 'class' => 'widefat'));
		if (empty(self::$options['ae_css'])) a5_textarea($base_id.'style', $base_name.'[style]', $style, sprintf(__('Here you can finally style the widget. Simply type something like%1$s%2$sborder: 1px solid;%1$sborder-color: #000000;%3$s%1$sto get just a black line around the widget. If you leave that section empty, your theme will style the widget.', 'adseasy'), '<br />', '<strong>', '</strong>'), array('space' => true, 'style' => 'height: 60px;', 'class' => 'widefat'));
		a5_resize_textarea(array($base_id.'adblock', $base_id.'style'), true);
		
	}
	
	function update($new_instance, $old_instance) {
		 
		$instance = $old_instance;
		
		$instance['title'] = strip_tags($new_instance['title']);
		$instance['name'] = strip_tags($new_instance['name']);
		$instance['adblock'] = trim($new_instance['adblock']);
		$instance['style'] = strip_tags($new_instance['style']);
		$instance['homepage'] = @$new_instance['homepage'];
		$instance['frontpage'] = @$new_instance['frontpage'];
		$instance['page'] = @$new_instance['page'];
		$instance['category'] = @$new_instance['category'];
		$instance['single'] = @$new_instance['single'];
		$instance['date'] = @$new_instance['date'];
		$instance['archive'] = @$new_instance['archive'];
		$instance['tag'] = @$new_instance['tag'];
		$instance['attachment'] = @$new_instance['attachment'];
		$instance['taxonomy'] = @$new_instance['taxonomy'];
		$instance['author'] = @$new_instance['author'];
		$instance['search'] = @$new_instance['search'];
		$instance['not_found'] = @$new_instance['not_found'];
		$instance['login_page'] = @$new_instance['login_page'];
		$instance['logged_in'] = @$new_instance['logged_in'];
		$instance['search_engine'] = @$new_instance['search_engine'];
		$instance['normal'] = @$new_instance['normal'];
		
		return $instance;
		
	}
	 
	function widget($args, $instance) {
		
	$visitor = $this->get_visitor();
	
	if (!empty($instance[$visitor])) :
		
		$show_widget = parent::check_output($instance);
	
		if ($show_widget) :
			
			// the widget is displayed
			
			extract( $args );
			
			$title = apply_filters('widget_title', $instance['name']);	
			
			if (!empty($instance['style'])) :
			
				$style=str_replace(array("\r\n", "\n", "\r"), ' ', $instance['style']);
				
				$before_widget = str_replace('>', 'style="'.$style.'">', $before_widget);
			
			endif;
			
			echo $before_widget;
			
			if ( $title ) echo $before_title . $title . $after_title;
		 
			/* This is the actual function of the plugin, it fills the widget with the ad */
				
			echo $instance['adblock'];
			
			echo $after_widget;
		
		endif;
	
	endif;
	
	} // function widget
	
	/**
	 *
	 * Checking ip address to store in case, visitor comes from search engine
	 *
	 */
	function get_ip_address() {
		
    	foreach (array('HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 'HTTP_X_CLUSTER_CLIENT_IP', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 'REMOTE_ADDR') as $key) :
	
			if (array_key_exists($key, $_SERVER) === true) :
			
				foreach (explode(',', $_SERVER[$key]) as $ip) :
					
					if (filter_var($ip, FILTER_VALIDATE_IP) !== false) return $ip;
				
				endforeach;
			
			endif;
		
    	endforeach;
	
	}
	
	/**
	 *
	 * Trying to find out, what kind of visitor we have
	 *
	 * there are three possible types:
	 *
	 * 'normal' visitor (just surfing the site)
	 * logged in user
	 * visitor that comes from a search engine
	 *
	 */
	function get_visitor() {
		
		$options = get_option('ae_options');
		
		$visitor = (is_user_logged_in()) ? 'logged_in' : false;
		
		if (false === $visitor) : // not logged in
		
			$ip_address = $this->get_ip_address();
			
			$visitor = (is_multisite()) ? get_site_transient($ip_address) : get_transient($ip_address); // unknown ip
			
			if (false === $visitor) :
			
				if (!isset($_SERVER['HTTP_REFERER'])) :
				
					$visitor = false;
					
				else :
	
					$search_engines = array('/search?', 'web.info.com', 'search.', 'del.icio.us/search', 'soso.com', '/search/', '.yahoo.', 'google');
					
					foreach ($search_engines as $engine) :
					
						if (strpos($_SERVER['HTTP_REFERER'], $engine) !== false ) :
						
							$visitor = 'search_engine';
							
							// setting transient for half an hour, when coming from search engine
							
							if (is_multisite()) :
							
								set_site_transient($ip_address, 'search_engine', 60 * $options['ae_time']);
								
							else:
							
								set_transient($ip_address, 'search_engine', 60 * $options['ae_time']);
								
							endif;
							
						else :
						
							$vistor = false;
						
						endif;
						
					endforeach;
				
				endif;
				
			endif;
			
		endif;
		
		if (false === $visitor) $visitor = 'normal'; // not logged in and not from search engine
		
		return $visitor;
		
	}

} // class widget

add_action('widgets_init', create_function('', 'return register_widget("Ads_Easy_Widget");'));


?>