<?php
/*
 * Plugin name: Database
 * Description: View database credentials and link to Adminer
 * Version: 1.0
 */

namespace Seravo;

if ( ! class_exists('Database') ) {
  class Database {

    /**
     * Load database features
     */
    public static function load() {

     if ( !is_multisite() ){
        $GLOBALS['sr_networkvisibility'] = false;
        $GLOBALS['sr_alltables'] = true;
      } else if ( current_user_can( 'manage_network' ) ) {
        $GLOBALS['sr_networkvisibility'] = true;
        $GLOBALS['sr_alltables'] = true;
      } else {
        $GLOBALS['sr_networkvisibility'] = false;
        $GLOBALS['sr_alltables'] = false;
      }

      add_action('admin_enqueue_scripts', array( __CLASS__, 'register_scripts' ));

      add_action( 'wp_ajax_seravo_search_replace' , function(){
        require_once(dirname( __FILE__ ) . '/../lib/search-replace-ajax.php' );
        wp_die();
      });

      add_action( 'wp_ajax_seravo_wp_db_info' , function(){
        require_once(dirname( __FILE__ ) . '/../lib/database-ajax.php' );
        wp_die();
      });

      add_action( 'admin_menu', array( __CLASS__, 'register_database_page' ) );

    }

    /**
     * Register scripts
     *
     * @param string $page hook name
     */
    public static function register_scripts( $page ) {

        wp_register_style('seravo_database', plugin_dir_url(__DIR__) . '/style/database.css');

        if ( $page === 'tools_page_database_page' ) {
            wp_enqueue_style('seravo_database');
        }

    }

    /**
     * Add admin menu item
     */
    public static function register_database_page() {
      add_submenu_page( 'tools.php', __('Database', 'seravo'), __('Database', 'seravo'), 'manage_options', 'database_page', array( __CLASS__, 'load_database_page' ) );
    }

    /**
     * Load options page
     */
    public static function load_database_page() {
      require_once(dirname( __FILE__ ) . '/../lib/database-page.php');
    }

  }

  Database::load();
}
