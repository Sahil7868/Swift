<?php
/*
    Plugin Name: Swift Slides
    Description: Build Smooth Image slider fast and easy with Swift Slider.It also allows you to set your image position in the slider.  Shortcode => [swift-slide] 
    Author: Sahil Surani
    Version: 1.0.0
*/

defined('ABSPATH') or die('Hey You can not access it');


 class swiftSlide
 {
      public function __construct() 
      {
             
             
              add_action( 'init', array($this,'rmcc_create_taxonomies'),0 );
              add_action( 'init', array($this,'sw2_init') );
              add_action( 'init', array($this,'registershortcode') );
            
           
              //add_image_size( 'sw2_function', 600, 280, true);
              add_filter('the_content',array($this,'sw_lazyscript_filter'));
             add_filter("manage_sw_category_custom_column",array($this,'sw_category_columns'), 10, 3);
            add_filter("manage_edit-sw_category_columns", array($this,'sw_category_manage_columns')); 

             
 
             
             if ( is_admin() ) 
             {
                add_action( 'load-post.php', array( $this, 'init_metabox' ) );
                add_action( 'load-post-new.php', array( $this, 'init_metabox' ) );
              
            }
              
     }
      function init_metabox() 
      {
                add_action( 'add_meta_boxes', array($this,'jpen_custom_post_sort') );
                add_action( 'save_post', array($this,'jpen_save_custom_post_order' ) );
                add_filter( 'manage_posts_columns', array($this,'jpen_add_custom_post_order_column') );
                add_action( 'manage_posts_custom_column', array($this,'jpen_custom_post_order_value') , 10 , 2  );
              
      }
      function register()
      {
                add_action( 'wp_print_scripts', array($this,'sw_register_scripts') );
                add_action( 'wp_print_styles', array($this,'sw_register_styles') );
                add_action('wp_enqueue_scripts',array($this,'sw_lazyscript'));
               
      }

       function registershortcode()
      {
                  add_shortcode('swift-slide',array($this,'rmcc_post_listing_parameters_shortcode'));
               
      }
      function activate()
      {
          flush_rewrite_rules();
      }

        function deactivate()
      {
   
      }

// lazy loading
        function sw_lazyscript()
        {
               wp_enqueue_script( 'intersection-observer-polyfill', 'path-to-intersection-observer.js', [], null, true );
               wp_enqueue_script( 'lozad', 'https://cdn.jsdelivr.net/npm/lozad@1.3.0/dist/lozad.min.js', ['intersection-observer-polyfill'], null, true );
               wp_add_inline_script( 'lozad', '
                            	lozad(".lazy-load", { 
	                            	rootMargin: "300px 0px", 
	                              	loaded: function (el) {
		                                	el.classList.add("is-loaded");
		                                    }
                            	}).observe()
                        ');
        }

 
   function sw_lazyscript_filter($content)
  { 
	//-- Change src/srcset to data attributes.
	$content = preg_replace("/<img(.*?)(src=|srcset=)(.*?)>/i", '<img$1data-$2$3>', $content);

	//-- Add .lazy-load class to each image that already has a class.
	$content = preg_replace('/<img(.*?)class=\"(.*?)\"(.*?)>/i', '<img$1class="$2 lazy-load"$3>', $content);

	//-- Add .lazy-load class to each image that doesn't have a class.
	$content = preg_replace('/<img(.*?)(?!\bclass\b)(.*?)/i', '<img$1 class="lazy-load"$2', $content);
	
	return $content;
}



 // gallery taxonomy
  function rmcc_create_taxonomies() {
    $labels = array(
        'name'              => _x( 'Sw_categorys', 'taxonomy general name' ),
        'singular_name'     => _x( 'Sw_category', 'taxonomy singular name' ),
        'search_items'      => __( 'Search Galleries' ),
        'all_items'         => __( 'All Galleries' ),
        'parent_item'       => __( 'Parent Gallery' ),
        'parent_item_colon' => __( 'Parent Gallery:' ),
        'edit_item'         => __( 'Edit Gallery' ),
        'update_item'       => __( 'Update Gallery' ),
        'add_new_item'      => __( 'Add New Gallery item' ),
        'new_item_name'     => __( 'New Gallery' ),
        'menu_name'         => __( 'Galleries' ),
    );
    register_taxonomy(
        'sw_category',
        'sw2_images',
        array(
            'hierarchical' => true,
            'labels' => $labels,
            'query_var' => true,
            'rewrite' => true,
            'show_admin_column' => true
        )
    );
   

}
// create custom post
        function sw2_init() 
    {
        $args = array(
              'public' => true,
              'label' => 'Swift Images Gallery',
              'supports' => array(
                      'title',
                      'thumbnail'
                         )
                 );
              
               register_post_type('sw2_images', $args);
               
  }

// Manage Category Shortcode columns
function sw_category_manage_columns($theme_columns) {
    $new_columns = array(
            'cb' => '<input type="checkbox" />',
            'name' => __('Name'),
            'slider_shortcode' => __( 'Slider Category Shortcode' ),
            'slug' => __('Slug'),
            'posts' => __('Posts')
			);

    return $new_columns;
}

function sw_category_columns($out, $column_name, $theme_id) {
      
    $theme = get_term($theme_id, 'sw_category');
    switch ($column_name) {      
        case 'title':
            echo get_the_title();
        break;
        case 'slider_shortcode':
			echo '[swift-slide type="sw2_images" sw_category="' . $theme->slug. '"]<br />';
		    break;
        default:
            break;
    }
    return $out;   

}


// fetch image and title of every custom post and display by meta_value i.e position
function rmcc_post_listing_parameters_shortcode( $atts,$post ) {
    ob_start();

    // define attributes and their defaults
    extract( shortcode_atts( array (
        'type' => '',
       'orderby' => 'meta_value',
        'order' => 'ASC',
        'posts' => -1,
        'sw_category' => '',
    ), $atts ) );
 
    // define query parameters based on attributes
    $query = new WP_Query( array(
         'post_type' => $type,
        'order' => $order,
        'orderby' => $orderby,
        'meta_key'=>'_custom_post_order',
        'posts_per_page' => $posts,
        'sw_category' => $sw_category,
    ) );

   $result = '<div class="slider-wrapper theme-default">';
    $result .= '<div id="slider2" class="nivoSlider">';
  
 
    //the loop
    
    while ($query->have_posts()) {
        $query->the_post();
        $the_url =wp_get_attachment_image_src(get_post_thumbnail_id($post),$type);
        $result .= '<img title="'.get_the_title().'" src="' . $the_url[0] . '" data-thumb="' . $the_url[0] . '" alt=""/>';
    }
    $result .= '</div>';
    $result .= '<div id = "htmlcaption" class = "nivo-html-caption">';
    $result .= '<strong>This</strong> is an example of a <em>HTML</em> caption with <a href = "#">a link</a>.';
    $result .= '</div>';
    $result .= '</div>';

     wp_reset_query();
     
    return $result;

}



   // Register and Enqueue Nivo slider Scripts and sw_register_styles

    function sw_register_scripts() 
  {
        if (!is_admin()) 
        {
        // register
        wp_register_script('np_nivo-script', plugins_url('lib/nivo-slider/jquery.nivo.slider.js', __FILE__), array( 'jquery' ));
        wp_register_script('np_script', plugins_url('lib/script.js', __FILE__));
        wp_register_script('np_script1', plugins_url('lib/script1.js', __FILE__));
        wp_register_script('np_script2', plugins_url('lib/script2.js', __FILE__));
      
        // enqueue
        wp_enqueue_script('np_nivo-script');
        wp_enqueue_script('np_script');
        wp_enqueue_script('np_script1');
        wp_enqueue_script('np_script2');
     
      }
  }
 
    function sw_register_styles()
  {
     // register
      wp_register_style('np_styles', plugins_url('lib/nivo-slider/nivo-slider.css', __FILE__));
      wp_register_style('np_styles_theme', plugins_url('lib/nivo-slider/themes/default/default.css', __FILE__));
    
      // enqueue
      wp_enqueue_style('np_styles');
      wp_enqueue_style('np_styles_theme');
    
  }
  // Add meta box for image position

    function jpen_custom_post_sort($post)
  {
      add_meta_box
        ( 
          'custom_post_sort_box', 
          'Position of image in the slider',
          array( 
            $this, 
            'jpen_custom_post_order' 
            ),
          array(
            $this,
            'sw2_images'
          ),
          'side',
          'high'
        );
  }
  // Add a field to the metabox 
    function jpen_custom_post_order($post) 
  {

    wp_nonce_field( basename( __FILE__ ), 'jpen_custom_post_order_nonce' );
    $current_pos = get_post_meta( $post->ID, '_custom_post_order', true); ?>
    <p>Enter the position at which you would like the Image to appear. For exampe, Image "1" will appear first, Image "2" second, and so forth.</p>
    <p><input type="number" name="pos" value="<?php echo $current_pos;?>" /></p>
    <?php

  }
  // Save the input to post_meta_data 
function jpen_save_custom_post_order( $post_id )
{
  if ( !isset( $_POST['jpen_custom_post_order_nonce'] ) || !wp_verify_nonce( $_POST['jpen_custom_post_order_nonce'], basename( __FILE__ ) ) ){
    return;
  } 
  if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ){
    return;
  }
  if ( ! current_user_can( 'edit_post', $post_id ) ){
    return;
  }
  if ( isset( $_REQUEST['pos'] ) ) {
    update_post_meta( $post_id, '_custom_post_order', sanitize_text_field( $_POST['pos'] ) );
  }
}
//  Add custom post order column to post list
function jpen_add_custom_post_order_column( $columns )
{
  return array_merge ( $columns,
    array( 'pos' => 'Position', ));
}

//  Display custom post order in the post list 
function jpen_custom_post_order_value( $column, $post_id )
{
  if ($column == 'pos' ){
    echo '<p>' . get_post_meta( $post_id, '_custom_post_order', true) . '</p>';
  }
}

 }

 if(class_exists('swiftSlide'))
 {
   $object=new swiftSlide();
   $object->register();
   $object->init_metabox();
 }

// activate
 register_activation_hook(__FILE__, array($object,'activate'));
 

 // deactivate
 register_deactivation_hook(__FILE__, array($object,'deactivate'));



 


