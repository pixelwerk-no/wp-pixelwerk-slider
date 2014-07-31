<?php
/**
 * @package   WP_Pixelwerk_Slider
 * @author    Tom Henrik Aadland <tom@pixelwerk.no>
 * @license   GPL-2.0+
 * @link      http://pixelwerk.no
 * @copyright 2014 Pixelwerk AS
 *
 * @wordpress-plugin
 * Plugin Name:       Pixelwerk Slider
 * Plugin URI:        @TODO
 * Description:       This is a simple plugin to upload images that the slider will use.
 * Version:           1.0.0
 * Author:            Tom Henrik Aadland
 * Author URI:        pixelwerk.no
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * GitHub Plugin URI: https://github.com/<owner>/<repo>
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/*----------------------------------------------------------------------------*
 * Public-Facing Functionality
 *----------------------------------------------------------------------------*/

/*
 *
 */
require_once( plugin_dir_path( __FILE__ ) . 'classes/pixelwerk-slider.php' );
require_once (plugin_dir_path(__FILE__) . 'classes/helpers.php');

/*
 * Register hooks that are fired when the plugin is activated or deactivated.
 * When the plugin is deleted, the uninstall.php file is loaded.
 *
 * @TODO:
 *
 * - replace Plugin_Name with the name of the class defined in
 *   `class-plugin-name.php`
 */
register_activation_hook( __FILE__, array( 'Pixelwerk_Slider', 'activate' ) );
register_deactivation_hook( __FILE__, array( 'Pixelwerk_Slider', 'deactivate' ) );

/*
 * @TODO:
 *
 * - replace Plugin_Name with the name of the class defined in
 *   `class-plugin-name.php`
 */
add_action( 'plugins_loaded', array( 'Pixelwerk_Slider', 'get_instance' ) );

/*----------------------------------------------------------------------------*
 * Dashboard and Administrative Functionality
 *----------------------------------------------------------------------------*/
//Register the handle for adding image to the slideshow.
add_action('admin_post_add_image', 'prefix_admin_add_image');
add_action('admin_post_remove_image', 'prefix_admin_remove_image');
add_action('admin_menu', 'my_plugin_menu');

function my_plugin_menu() {
  add_menu_page('Slideshow', 'Slideshow', 'administrator', 'slideshow', 'display_admin_content', get_template_directory_uri() . '/images/favicon.ico');
}

function prefix_admin_remove_image() {
  global $wpdb;
  $attachment_id = $_REQUEST['attachment_id'];
  $slider_image_id = $_REQUEST['slider_image_id'];
  
  $sql = "DELETE FROM pixelwerk_slides WHERE id='$slider_image_id'";
  $result = $wpdb->query($sql); 
  wp_delete_attachment($attachment_id);

  wp_redirect(admin_url('admin.php?page=slideshow'));
  exit();
}

function prefix_admin_add_image() {
  global $wpdb;
  status_header(200);

   if ( isset( $_FILES ) && ! empty( $_FILES ) ) {
    log_me("FILE UPLOADED");
    log_me($_FILES);
    
    //Lets let the media library handle the images...
    $attachment_id = media_handle_upload('image_file', 0);
    $rows_affected = $wpdb->insert('pixelwerk_slides', array('attachment_id'  => $attachment_id));
    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    dbDelta( $rows_affected );

    log_me("ID IS: ");
    log_me($attachment_id);
  }
  wp_redirect(admin_url('admin.php?page=slideshow'));
  exit();
  
}

function render_slider_images() {
  log_me("RENDER IMAGES");
  global $wpdb;
  $images = array();
  $attachment_ids = $wpdb->get_results("SELECT attachment_id FROM pixelwerk_slides");

  foreach ($attachment_ids as $ids) {
    foreach ($ids as $id) {
      $image = wp_get_attachment_url($id);
      array_push($images, $image);
    }
  }

  foreach ($images as $image) {
    echo "<div style='background-image:url($image)'></div>";
  }

}

function display_admin_content() {
  global $wpdb;
  $thumbs = array();
  $attachment_ids = $wpdb->get_results("SELECT attachment_id,id FROM pixelwerk_slides");
  log_me($attachment_ids);
  foreach ($attachment_ids as $ids) {
    log_me($ids->attachment_id);
    $thumb_file = wp_get_attachment_image($ids->attachment_id);
    log_me("THUMB: " . $thumb_file);
    array_push($thumbs, array($thumb_file, $ids->attachment_id, $ids->id));
  }

?>
 <div class="wrap">
  <h2>Slideshow images</h2>
<?php
  foreach ($thumbs as $thumb) {
    echo $thumb[0];
    echo "<a href='admin-post.php?action=remove_image&attachment_id=$thumb[1]&slider_image_id=$thumb[2]'>Slett</a>";
  }
?>
 <h2>Slideshow Settings</h2>
  <form action="admin-post.php" enctype="multipart/form-data" method="post">
  <input type="hidden" name="action" value="add_image"/>
  <input type="file" name="image_file"/>
  <input type="submit" value="Send File" />
  </form> 
 </div>

<?php
}


