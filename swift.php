<?php
/*
    Plugin Name: Swift Slides
    Description: Build Smooth Image slider fast and easy with Swift Slider.It also allows you to set your image position in the slider.  Shortcode => [swift-slide] 
    Author: Sahil Surani
    Version: 1.0
*/

defined('ABSPATH') or die('Hey You can not access it');

 class swiftSlide
 {
      public function __construct() 
      {
              add_action( 'init', array($this,'sw_init') );
              add_shortcode( 'swift-slide',array($this,'sw_function') );
              add_image_size( 'sw_widget', 180, 100, true);
              add_image_size( 'sw_function', 600, 280, true); 
             
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
               
      }

      function activate()
      {
          flush_rewrite_rules();
      }

      function deactivate()
      {
   
      }
     // create custom post type 
    function sw_init() 
    {
        $args = array(
              'public' => true,
              'label' => 'Swift Images',
              'supports' => array(
                      'title',
                      'thumbnail'
                         )
                 );
              
               register_post_type('sw_images', $args);
               
  }
   // Register and Enqueue Nivo slider Scripts and sw_register_styles

    function sw_register_scripts() 
  {
        if (!is_admin()) 
        {
        // register
        wp_register_script('np_nivo-script', plugins_url('lib/nivo-slider/jquery.nivo.slider.js', __FILE__), array( 'jquery' ));
        wp_register_script('np_script', plugins_url('lib/script.js', __FILE__));
            
        // enqueue
        wp_enqueue_script('np_nivo-script');
        wp_enqueue_script('np_script');
     
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
          'sw_images',
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

// fetch image and title of every custom post and display by meta_value i.e position
function sw_function($type='sw_function',$post) 
{
    
    $args = array(
        'post_type' => 'sw_images',
        'posts_per_page' => 10,
        'orderby' => 'meta_value',
        'meta_key'=>'_custom_post_order',
        'order' => 'ASC'
        
    );
    $result = '<div class="slider-wrapper theme-default">';
    $result .= '<div id="slider" class="nivoSlider">';
  
 
    //the loop
    $loop = new WP_Query($args);
    while ($loop->have_posts()) {
        $loop->the_post();
        $the_url =wp_get_attachment_image_src(get_post_thumbnail_id($post),$type);
        $result .= '<img title="'.get_the_title().'" src="' . $the_url[0] . '" data-thumb="' . $the_url[0] . '" alt=""/>';
    }
    $result .= '</div>';
    $result .= '<div id = "htmlcaption" class = "nivo-html-caption">';
    $result .= '<strong>This</strong> is an example of a <em>HTML</em> caption with <a href = "#">a link</a>.';
    $result .= '</div>';
    $result .= '</div>';
    return $result;
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



 


