<?php
/*
Plugin Name: Widget - Posts By Category
Plugin URI: http://wordpress.org/plugins/widget-posts-by-category/
Description: Displays posts in the category, order, and quantity of your choosing.
Author: Vincent Maglione
Version: 1.0.4
Author URI: http://bigsweaterdesign.com
Text Domain: posts_by_cat_widget
Domain Path: /lang
*/

class Cats_Loop_Widget extends WP_Widget {

	/*--------------------------------------------------*/
	/* Constructor
	/*--------------------------------------------------*/
	
	/**
	 * Specifies the classname and description, instantiates the widget, 
	 * loads localization files, and includes necessary stylesheets and JavaScript.
	 */
	public function __construct() {
	
		// load plugin text domain
		add_action( 'init', array( $this, 'posts_by_cat_widget_textdomain' ) );
		
		// Create a new widget, name it, give it a description, IDs, and classes
		parent::__construct(
			'cats-loop-widget',
			__( 'Posts by Category', 'posts_by_cat_widget' ),
			array(
				'classname'		=>	'cats-loop-widget',
				'description'	=>	__( 'Creates a list of posts within the selected categories.', 'posts_by_cat_widget' )
			)
		);		
	} // end constructor

	/*--------------------------------------------------*/
	/* Widget API Functions
	/*--------------------------------------------------*/
	
	/**
	 * Outputs the content of the widget.
	 *
	 * @param	array	args		The array of form elements
	 * @param	array	instance	The current instance of the widget
	 */
	public function widget( $args, $instance ) {
	
		extract( $args, EXTR_SKIP );
		
		$before_widget = str_replace('class="', 'class="widget_posts-by-cat ', $before_widget);
		
		echo $before_widget;
    
		//	Parse the selected categories, store in an array		
		$included_cats = '';
		if ($instance['post_category']) {
			$post_category	= unserialize($instance['post_category']);
			$children		= array();

			foreach ($post_category as $cat_id) {
				$children = array_merge($children, get_term_children($cat_id, 'category'));
			}
			$included_cats = implode(",", array_merge($post_category, $children));
		}
		
		// Sanitize and store user inputs
		$title   = esc_html( $instance['title'] );
		$count   = intval( $instance['count'] );
		$order   = sanitize_key( $instance['order'] );
		$orderby = sanitize_key( $instance['orderby'] );
		

		// Arguments for the new query taken from stored inputs
		$cat_args = array( 
			'cat'            => $included_cats,
			'posts_per_page' => $count,
			'order'          => $order,
			'orderby'        => $orderby
		);
		
		// Create the new WP query
		$cat_query = new WP_Query( $cat_args ); 

		// Echo the widget's container
		if ( !empty( $title ) ) {
			echo $before_title . $title . $after_title;
		}
   		
   		// Include the front-end view
		$this -> posts_by_cat_widget_load_template(
			array( 'posts' => $cat_query )
		);
		
		// Rewind your posts
		wp_reset_postdata();
		
		// Close up the widget
		echo $after_widget;
		
	} // end widget
	
	/**
	 * Processes the widget's options to be saved.
	 *
	 * @param	array	new_instance	The previous instance of values before the update.
	 * @param	array	old_instance	The new instance of values to be generated via the update.
	 */
	public function update( $new_instance, $old_instance ) {
	
		$instance = $old_instance;
		
		$instance['title']		= strip_tags(stripslashes($new_instance['title']));
		$instance['count']		= $new_instance['count'];
		$instance['order']		= $new_instance['order'];
		$instance['orderby']	= $new_instance['orderby'];
		
		// Determine whether 'all categories' has been selected
		if (array_key_exists('all', $new_instance['post_category'])) {
			$instance['post_category'] = FALSE;
		} else {
			$instance['post_category'] = serialize($new_instance['post_category']);
		}
		
		return $instance;
		
	} // end update
	
	/**
	 * Generates the administration form for the widget.
	 *
	 * @param	array	instance	The array of keys and values for the widget.
	 */
	public function form( $instance ) {
	
    	// Define default values for your variables
		$instance = wp_parse_args(
			(array) $instance, array( 
				'title'			=> '',
				'count'			=> '10',
				'post_category'	=> '',
				'order'			=> 'DESC',
				'orderby'		=> 'date'
			)
		);
		
		$title			= esc_html( $instance['title'] );
		$count			= intval( $instance['count'] );
		$selected_cats	= ($instance['post_category'] != '') ? unserialize($instance['post_category']) : FALSE;
		$order			= sanitize_key( $instance['order'] );
		$orderby		= sanitize_key( $instance['orderby'] );
		
		// Display the admin form
		include( plugin_dir_path(__FILE__) . '/views/admin.php' );	
		
	} // end form

	/**
	 * Creates the categories checklist
	 *
	 * @param int $post_id
	 * @param int $descendants_and_self
	 * @param array $selected_cats
	 * @param array $popular_cats
	 * @param int $number
	 */
	function bsd_wp_category_checklist ($selected_cats, $number) {

		$walker = new BSD_Walker_Category_Checklist();
		$walker->number = $number;
		$walker->input_id = $this->get_field_id('post_category');
		$walker->input_name = $this->get_field_name('post_category');
		$walker->li_id = $this->get_field_id('category--1');

		$args = array (
			'taxonomy'             => 'category',
			'descendants_and_self' => 0,
			'selected_cats'        => $selected_cats,
			'popular_cats'         => array(),
			'walker'               => $walker,
			'checked_ontop'        => true,
			'popular_cats'         => array()
		);

		if ( is_array( $selected_cats ) )
			$args['selected_cats'] = $selected_cats;
		else
			$args['selected_cats'] = array();

		$categories     = $this -> bsd_getCategories();
		$_categories_id = $this -> bsd_getCategoriesId($categories);

		// Post process $categories rather than adding an exclude to the get_terms() query to keep the query the same across all posts (for any query cache)
		$checked_categories = array();

		foreach ($args['selected_cats'] as $key => $value) {
			if (isset($_categories_id[$key])) {
				$category_key = $_categories_id[$key];

				$checked_categories[] = $categories[$category_key];

				unset($categories[$category_key]);
			}
		}

		// Put checked cats on top
		echo $walker->walk_cats($checked_categories, 0, array( $args ));

		// Then the rest of them
		echo $walker->walk_cats($categories, 0, array( $args ));
	}

	/*--------------------------------------------------*/
	/* Public Functions
	/*--------------------------------------------------*/
	
	/**
	 * Loads the Widget's text domain for localization and translation.
	 */
	public function posts_by_cat_widget_textdomain() {
		load_plugin_textdomain( 'posts_by_cat_widget', false, plugin_dir_path( __FILE__ ) . '/lang/' );
		
	}
	
	/**
	 * Loads template from theme, if available.
	 */
	 function posts_by_cat_widget_load_template($_vars) {
		// Find the user's template first
		$_template = locate_template('catswidget.php', false, false);

		// If the user doesn't provide a view, load the default
		if ( !$_template )
			$_template = plugin_dir_path( __FILE__ ) . 'views/template.php';
		
		// Get the arguments and load the template
		extract($_vars);

		require($_template);
	}
	
	/**
	 * Gets category names
	 */
	 function bsd_getCategories() {
		static $_categories = NULL;

		if (NULL === $_categories) {
			$_categories = get_categories('get=all');
		}

		return $_categories;
	}

	/**
	 * Gets category IDs after the previous function returns names
	 */
	 function bsd_getCategoriesId($categories) {
		static $_categories_id = NULL;

		if (NULL == $_categories_id) {
			foreach ($categories as $key => $category) {
				$_categories_id[$category->term_id] = $key;
			}
		}

		return $_categories_id;
	}
} // end class

add_action( 'widgets_init', create_function( '', 'register_widget("Cats_Loop_Widget");' ) ); 


/**
 * Class that will display the categories
 *
 */
class BSD_Walker_Category_Checklist extends Walker {
	var $tree_type = 'category';
	var $db_fields = array ( 'parent' => 'parent', 'id' => 'term_id' ); 
	var $number;
	var $input_id;
	var $input_name;
	var $li_id;

	/**
	 * Display array of elements hierarchically.
	 *
	 * It is a generic function which does not assume any existing order of
	 * elements. max_depth = -1 means flatly display every element. max_depth =
	 * 0 means display all levels. max_depth > 0  specifies the number of
	 * display levels.
	 *
	 * @since 2.1.0
	 *
	 * @param array $elements
	 * @param int $max_depth
	 * @param array $args;
	 * @return string
	 */
	function walk_cats($elements, $max_depth, $args) {
		$output = '';

		if ($max_depth < - 1) //invalid parameter
			return $output;

		if (empty($elements)) //nothing to walk
			return $output;

		$id_field = $this->db_fields['id'];
		$parent_field = $this->db_fields['parent'];

		// flat display
		if (- 1 == $max_depth) {
			$empty_array = array();
			foreach ($elements as $e)
				$this->display_element($e, $empty_array, 1, 0, $args, $output);
			return $output;
		}

		/*
		 * need to display in hierarchical order
		 * separate elements into two buckets: top level and children elements
		 * children_elements is two dimensional array, eg.
		 * children_elements[10][] contains all sub-elements whose parent is 10.
		 */
		$top_level_elements = array();
		$children_elements = array();
		foreach ($elements as $e) {
			if (0 == $e->$parent_field)
				$top_level_elements[] = $e;
			else
				$children_elements[$e->$parent_field][] = $e;
		}

		/*
		 * when none of the elements is top level
		 * assume the first one must be root of the sub elements
		 */
		if (empty($top_level_elements)) {

			$first = array_slice($elements, 0, 1);
			$root = $first[0];

			$top_level_elements = array();
			$children_elements = array();
			foreach ($elements as $e) {
				if ($root->$parent_field == $e->$parent_field)
					$top_level_elements[] = $e;
				else
					$children_elements[$e->$parent_field][] = $e;
			}
		}

		foreach ($top_level_elements as $e)
			$this->display_element($e, $children_elements, $max_depth, 0, $args, $output);

		/*
		 * if we are displaying all levels, and remaining children_elements is not empty,
		 * then we got orphans, which should be displayed regardless
		 */
		if (($max_depth == 0) && count($children_elements) > 0) {
			$empty_array = array();
			foreach ($children_elements as $orphans)
				foreach ($orphans as $op)
					$this->display_element($op, $empty_array, 1, 0, $args, $output);
		}

		return $output;
	}

	function start_lvl ( &$output, $depth = 0, $args = array() ) {
		$indent = str_repeat("\t", $depth);
		$output .= $indent . '<ul class="children">' . "\n";
	}

	function end_lvl ( &$output, $depth = 0, $args = array() ) {
		$indent = str_repeat("\t", $depth);
		$output .= $indent . '</ul>' . "\n";
	}

	function start_el ( &$output, $category, $depth = 0, $args = array(), $id = 0 ) {
		extract($args);
		$input_id = $this->input_id . '-' . $category->term_id;
		$output .= "\n" . '<li id="' . $this->li_id . '">';
		$output .= '<label for="' . $input_id . '" class="selectit">';
		$output .= '<input value="' . $category->term_id . '" type="checkbox" name="' . $this->input_name . '[' . $category->term_id . ']" id="' . $input_id . '"' . (in_array($category->term_id, $selected_cats) ? ' checked="checked"' : "") . '/> ' . esc_html(apply_filters('the_category', $category->name)) . '</label>';
	}

	function end_el ( &$output, $category, $depth = 0, $args = array() ) {
		$output .= "</li>\n";
	}
}