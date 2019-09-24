<?php
/**
 * Plugin Name: Tantra Banners
 * Plugin URI:
 * Description:
 * Version: 1.0
 * Author: Pvl
 * Author URI:
 */

 defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

/**
 *
 */
class WPBanners
{

  function __construct()
  {
    wp_register_script('banners', plugin_dir_url(__FILE__) . 'js/banners.js', array('jquery'), '1.0', true);

    wp_register_style('banners-css', plugin_dir_url(__FILE__) . 'css/banners.css', '1.0', true);
    wp_enqueue_script('banners');
    wp_enqueue_style('banners-css');


    /*
    * Register Post type and taxonomy for this type
    */
    add_action( 'init', [ $this, 'banners_type'] , 0 );
    add_action( 'init', [ $this,'banner_zones' ], 0 );


        // Add the fields to the "presenters" taxonomy, using our callback function
    add_action( 'banner_zones_edit_form_fields', [ $this,'banner_zones_taxonomy_custom_fields'], 10, 2 );
    // Save the changes made on the "presenters" taxonomy, using our callback function
    add_action( 'edited_banner_zones', [ $this,'save_taxonomy_custom_fields'], 10, 2 );

    /* Hooks to use with taxonomy actions */
    add_action( 'created_banner_zones', [ $this, 'wpse_created_term'] , 10, 3 );
    add_action( 'edited_banner_zones', [ $this, 'wpse_edited_term'] , 20, 3 );
    add_action( 'delete_banner_zones', [ $this, 'wpse_delete_term'] , 10, 3 );

    add_filter( 'manage_banner_zones_custom_column', [ $this,'wptc_banner_zones_column_content'], 10, 3 );
    add_filter('manage_banner_zones_place_custom_column', [ $this,'add_banner_zones_place_column_content'],10,3);
    add_filter( 'manage_edit-banner_zones_columns' , [ $this,'wptc_banner_zones_columns'] );


    /* Create Shortcode */
    add_shortcode( 'zone', [$this , 'zone'] );

  }



     // function misha_meta_box_add() {
     //   add_meta_box(
     //        'mishadiv', // meta box ID
     //       'More settings', // meta box title
     //       'misha_print_box', // callback function that prints the meta box HTML
     //       'tantra-banners', // post type where to add it
     //       'normal', // priority
     //       'high' ); // position
     // }
     // add_action( 'add_meta_box', 'misha_meta_box_add' );

     /*
      * Meta Box HTML
      */
     // function misha_print_box( $post ) {
     //     $meta_key = 'second_featured_img';
     //     // echo $this->misha_image_uploader_field( $meta_key, get_post_meta($post->ID, $meta_key, true) );
     //
     //     $image = ' button">Upload image';
     //     $image_size = 'full'; // it would be better to use thumbnail size here (150x150 or so)
     //     $display = 'none'; // display state ot the "Remove image" button
     //
     //     if( $image_attributes = wp_get_attachment_image_src( get_post_meta($post->ID, $meta_key, true), $image_size ) ) {
     //         // $image_attributes[0] - image URL
     //         // $image_attributes[1] - image width
     //         // $image_attributes[2] - image height
     //         $image = '"><img src="' . $image_attributes[0] . '" style="max-width:95%;display:block;" />';
     //         $display = 'inline-block';
     //     }
     //
     //
     //     echo '
     //     <div>
     //         <a href="#" class="misha_upload_image_button' . $image . '</a>
     //         <input type="hidden" name="' . $meta_key . '" id="' . $meta_key . '" value="' . get_post_meta($post->ID, $meta_key, true) . '" />
     //         <a href="#" class="misha_remove_image_button" style="display:inline-block;display:' . $display . '">Remove image</a>
     //     </div>';
     //
     //     return true;
     // }

     /*
      * Save Meta Box data
      */
     // function misha_save( $post_id ) {
     //     if ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE )
     //         return $post_id;
     //     $meta_key = 'second_featured_img';
     //     update_post_meta( $post_id, $meta_key, $_POST[$meta_key] );
     //     // if you would like to attach the uploaded image to this post, uncomment the line:
     //     // wp_update_post( array( 'ID' => $_POST[$meta_key], 'post_parent' => $post_id ) );
     //     return $post_id;
     // }






























  function banners_type() {
    $labels = array(
        'name'                => _x( 'Banners', 'Post Type General Name', 'twentythirteen' ),
        'singular_name'       => _x( 'Banner', 'Post Type Singular Name', 'twentythirteen' ),
        'menu_name'           => __( 'Banners', 'twentythirteen' ),
        'parent_item_colon'   => __( 'Parent Banner', 'twentythirteen' ),
        'all_items'           => __( 'All Banners', 'twentythirteen' ),
        'view_item'           => __( 'View Banner', 'twentythirteen' ),
        'add_new_item'        => __( 'Add New Banner', 'twentythirteen' ),
        'add_new'             => __( 'Add New', 'twentythirteen' ),
        'edit_item'           => __( 'Edit Banner', 'twentythirteen' ),
        'update_item'         => __( 'Update Banner', 'twentythirteen' ),
        'search_items'        => __( 'Search Banner', 'twentythirteen' ),
        'not_found'           => __( 'Not Found', 'twentythirteen' ),
        'not_found_in_trash'  => __( 'Not found in Trash', 'twentythirteen' ),
    );
    $args = array(
      'labels'             => $labels,
      'public'             => true,
      'publicly_queryable' => true,
      'show_ui'            => true,
      'show_in_menu'       => true,
      'query_var'          => true,
      'rewrite'            => array( 'slug' => 'tantra-banners' ),
      'capability_type'    => 'post',
      'has_archive'        => true,
      'hierarchical'       => false,
      'menu_position'      => 5,
      'supports'           => array( 'title', 'editor', 'author', 'thumbnail', 'excerpt', 'comments' ),
    );
    // Registering your Custom Post Type
    register_post_type( 'tantra-banners', $args );
  }

  // hook into the init action and call create_book_taxonomies when it fires
  // create two taxonomies, genres and writers for the post type "book"
  function banner_zones() {
  	// Add new taxonomy, make it hierarchical (like categories)
  	$labels = array(
  		'name'              => _x( 'Banner Zones', 'taxonomy general name', 'textdomain' ),
  		'singular_name'     => _x( 'Banner Zone', 'taxonomy singular name', 'textdomain' ),
  		'search_items'      => __( 'Search Banner Zones', 'textdomain' ),
  		'all_items'         => __( 'All Banner Zones', 'textdomain' ),
  		'parent_item'       => __( 'Parent Banner Zone', 'textdomain' ),
  		'parent_item_colon' => __( 'Parent Banner Zone:', 'textdomain' ),
  		'edit_item'         => __( 'Edit Banner Zone', 'textdomain' ),
  		'update_item'       => __( 'Update Banner Zone', 'textdomain' ),
  		'add_new_item'      => __( 'Add New Banner Zone', 'textdomain' ),
  		'new_item_name'     => __( 'New Banner Zones Name', 'textdomain' ),
  		'menu_name'         => __( 'Banner Zones', 'textdomain' ),
  	);
  	$args = array(
  		'hierarchical'      => true,
  		'labels'            => $labels,
  		'show_ui'           => true,
  		'show_admin_column' => true,
  		'query_var'         => true,
  		'rewrite'           => array( 'slug' => 'banner-zones' ),
  	);
  	register_taxonomy( 'banner_zones', array( 'tantra-banners' ), $args );

  }


  /*
  * Custom Column in list of all terms.
  */
  function wptc_banner_zones_columns( $columns ) {
  	// add carousel shortcode column
  	$columns['carousel_shortcode'] = __('Carousel Shortcode');
  	return $columns;
  }
  /*
   * Custom Column in list of all terms.
   * filter pattern: manage_{taxonomy}_custom_column
   * where {taxonomy} is the name of taxonomy e.g; 'carousel_category'
   * codex ref: https://codex.wordpress.org/Plugin_API/Filter_Reference/manage_$taxonomy_id_columns
   */
  function wptc_banner_zones_column_content( $content, $column_name, $term_id ) {
  	// get the term object
  	$term = get_term( $term_id, 'carousel_category' );
  	// check if column is our custom column 'carousel_shortcode'
  	if ( 'carousel_shortcode' == $column_name ) {
  		$shortcode = '<code>[zone id="'.$term_id.'"]</code>';
  		$content = $shortcode;
  	}
  	return $content;
  }

  //
  // Function adds custom field to taxonomy term edit screen
  //
  function banner_zones_taxonomy_custom_fields($tag) {
     // Check for existing taxonomy meta for the term you're editing
      $t_id = $tag->term_id; // Get the ID of the term you're editing
      $term_meta = get_option( "taxonomy_term_$t_id" ); // Do the check

      $shortcode_value = "[zone id='". $t_id ."']";
    ?>
      <tr class="form-field">
          <th scope="row" valign="top">
              <label for="banner_zone_id"><?php _e('Shortcode of banner zones'); ?></label>
          </th>
          <td>
              <input type="text" name="term_meta[banner_zone]" id="term_meta[banner_zone]" disabled size="25" style="width:60%; direction: ltr;" value="<?php echo $shortcode_value; ?>"><br />
              <span class="description"><?php _e('Banner zone code to place in the content page or widget.'); ?></span>
          </td>
      </tr>

    <?php
    }


  /* ZONE SHORTCODE */
  function zone( $atts ) {
    ob_start();

    $value = shortcode_atts( array(
      'id' => 300,
    ), $atts );

    $taxonomy = "banner_zones";
    $term = get_term( $value['id'], $taxonomy );

    $args = array(
      'post_type' => 'tantra-banners',
      'tax_query' => array (
        array (
          'taxonomy' => $taxonomy,
          'fields' => 'term_id',
          'terms' => $value['id']
        )
      )
    );

    $banners = new WP_Query($args);

    if ( $banners -> have_posts() ) {

      echo '<section id="banner-'.$value['id'].'" class="banner-zone carousel slide" data-ride="carousel">';
      echo '<ul class="carousel-inner">';
      $cc = 0;
      while ( $banners -> have_posts() ) { $banners -> the_post();
        $cc++;
        $banner_link = get_post_meta( get_the_ID(), 'banner-link', true );
        $banner_mobile_img = get_post_meta( get_the_ID(), 'second_featured_img', true );

        if ($cc == 1) {

          echo "<li class='item active'>";
          if ( ! empty( $banner_link ) ) { echo '<a href="'.$banner_link.'" targer="_blank" title="'.get_the_title().'" id="banner-link-'.$value['id'].'">'; }
              if ( wp_is_mobile() ) {
                if ( ! empty( $banner_mobile_img ) ) {
                  echo '<img src="'.$banner_mobile_img.'" alt="'.get_the_title().'">';
                } else {
                  the_post_thumbnail();
                }
              } else {
                the_post_thumbnail();
              }
              if ( ! empty( $banner_link ) ) { echo '</a>'; }

          echo "</li>";

        } else {

          echo "<li class='item'>";
          if ( ! empty( $banner_link ) ) { echo '<a href="'.$banner_link.'" target="_blank" title="'.get_the_title().'" id="banner-link-'.$value['id'].'">'; }
              if ( wp_is_mobile() ) {
                if ( ! empty( $banner_mobile_img ) ) {
                  echo '<img src="'.$banner_mobile_img.'" alt="'.get_the_title().'">';
                } else {
                  the_post_thumbnail();
                }
              } else {
                the_post_thumbnail();
              }
            if ( ! empty( $banner_link ) ) { echo '</a>'; }
          echo "</li>";
        }
      }
      echo '</ul>';
      echo "</section>";
    }
  	return ob_get_clean();
  }

}


$tantra_banners = new WPBanners();

abstract class WPOrg_Meta_Box
{
    public static function add()
    {
        add_meta_box(
          'banner-settings-image', // meta box ID
          'Mobile Banner', // meta box title
          [self::class, 'html'], // callback function that prints the meta box HTML
          'tantra-banners', // post type where to add it
          'normal', // priority
          'high'
        ); // position
        add_meta_box(
          'banner-settings-link', // meta box ID
          'Banner Link', // meta box title
          [self::class, 'link'], // callback function that prints the meta box HTML
          'tantra-banners', // post type where to add it
          'normal', // priority
          'high'
        ); // position

    }

    public static function save($post_id)
    {

      if ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ) { return $post_id; }

        update_post_meta(
            $post_id,
            'banner-link',
            $_POST['banner-link']
        );
        update_post_meta(
            $post_id,
            'second_featured_img',
            $_POST['second_featured_img']
        );
    }

    public static function link($post) {
      wp_nonce_field( 'myplugin_form_metabox_nonce', 'myplugin_form_metabox_process' );
      $value = get_post_meta($post->ID, '_wporg_meta_key1', true); ?>
      <input type="url" name="banner-link" value="<?php echo get_post_meta($post->ID, 'banner-link', true); ?>">
    <?php
    }


    public static function html($post)
    {
        wp_nonce_field( 'myplugin_form_metabox_nonce', 'myplugin_form_metabox_process' );
        $value = get_post_meta($post->ID, '_wporg_meta_key', true);
        $meta_key = 'second_featured_img';
        ?>
        <div>
            <input type="url" class="large-text banner-mobile-img-url" name="second_featured_img" id="second_featured_img" value="<?php echo get_post_meta($post->ID, $meta_key, true); ?>"><br>
            <div class="image-frame">
              <img src="<?php echo get_post_meta($post->ID, $meta_key, true); ?>" alt="">
            </div>
            <button type="button" class="button" id="events_video_upload_btn" data-media-uploader-target="#second_featured_img">Upload Media</button>
            <a href="#" class="remove-mobile-banner"> Delete Image </a>
        </div>
<?php
    }
}


function myplugin_load_admin_scripts( $hook ) {
  global $typenow;
  if( $typenow == 'tantra-banners' ) {
    wp_enqueue_media();
    // Registers and enqueues the required javascript.
    wp_register_script( 'meta-box-image', plugins_url( 'banners.js' , __FILE__ ), array( 'jquery' ) );
    wp_localize_script( 'meta-box-image', 'meta_image',
      array(
        'title' => __( 'Choose or Upload Media', 'events' ),
        'button' => __( 'Use this media', 'events' ),
      )
    );
    wp_enqueue_script( 'meta-box-image' );
  }
}
add_action( 'admin_enqueue_scripts', 'gmt_events_load_admin_scripts', 10, 1 );
add_action('add_meta_boxes', ['WPOrg_Meta_Box', 'add']);
add_action('save_post', ['WPOrg_Meta_Box', 'save']);
