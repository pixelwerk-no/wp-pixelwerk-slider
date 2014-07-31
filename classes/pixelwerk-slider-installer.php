<?php

require_once (plugin_dir_path(__FILE__) . 'helpers.php');

class WP_Pixelwerk_Slider_Installer
{
  static function run_installer() {
    global $wpdb;
    WP_Pixelwerk_Slider_Installer::create_db_tables();
  }
  
  static function run_uninstaller() {
    global $wpdb;
    WP_Pixelwerk_Slider_Installer::remove_images();
    WP_Pixelwerk_Slider_Installer::drop_db_tables(); 
  }

  static function create_db_tables()
  {
    $sql = "CREATE TABLE pixelwerk_slides (
      id MEDIUMINT(9) NOT NULL AUTO_INCREMENT,
      attachment_id MEDIUMINT(9) NOT NULL,
      UNIQUE KEY id (id),
      UNIQUE (attachment_id));";
      
      require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
      dbDelta($sql);
  }
  
  static function remove_images() {
    global $wpdb;
    $attachment_ids = $wpdb->get_results("SELECT attachment_id FROM pixelwerk_slides");
    foreach ($attachment_ids as $ids) {
      foreach ($ids as $id) {
        log_me("REMOVING ATTACHMENT: " . $id);
        wp_delete_attachment($id);
      }
    }
  }

  static function drop_db_tables() {
    global $wpdb;
    $table = 'pixelwerk_slides';
    $wpdb->query("DROP TABLE IF EXISTS $table");
  }
}

