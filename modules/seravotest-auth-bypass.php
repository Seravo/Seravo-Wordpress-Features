<?php
/**
 * Plugin name: Allow automated login for user 'seravotest'
 * Description: If normal login is prevented (e.g. captcha, external LDAP API etc)
 * then this module can be used by the 'seravotest' user to log in to a site anyway.
 */

namespace Seravo;

// Deny direct access to this file
if ( ! defined('ABSPATH') ) {
  die('Access denied!');
}

if ( ! class_exists('Seravotest_User_Login') ) {
  class Seravotest_Auth_Bypass {

    public static function load() {

      // Check for permission to enter only if flag is set
      if ( isset($_GET['seravotest-auth-bypass']) ) {
          add_action('login_init', array( __CLASS__, 'attempt_login' ), 10, 2);

          // Restrict Content Pro has it's own processing for login forms,
          // and it doesn't run login_init. This prevents auth bypass from working.
          // So, let's add override for this plugin.
          //add_action('rcp_login_form_errors', array( __CLASS__, 'attempt_login' ), 10, 2);
          // RCP hooks to init with priority 10, so now we catch request before RCP, and we're
          // able to do our magic
          add_action('init', array( __CLASS__, 'attempt_login' ), 9, 2);
      }

    }

    /**
     * Catch login attempt using seravotest-auth-bypass
     **/
    public static function attempt_login() {
      // If our key hasn't been set, stop processing here.
      if ( ! isset($_GET) || ! isset($_GET['seravotest-auth-bypass']) ) {
        return;
      }
      // If special authentication bypass key is found, check if a matching
      // key is found, and if soautomatically login user and redirect to wp-admin.
      $key = get_transient('seravotest-auth-bypass-key');

      if ( ! empty($key) && $key === $_GET['seravotest-auth-bypass'] ) {
        // Remove bypass key so it cannot be used again
        delete_transient('seravotest-auth-bypass-key');

        $user = get_user_by('login', 'seravotest');

        if ( ! is_wp_error($user) ) {
          wp_clear_auth_cookie();
          wp_set_current_user($user->ID);
          wp_set_auth_cookie($user->ID);
          $redirect_to = user_admin_url();
          wp_safe_redirect($redirect_to);
        }
      } else {
        error_log('Failed "seravotest" user authentication bypass attempt from IP ' . $_SERVER['REMOTE_ADDR']);
      }
    }

  }

  Seravotest_Auth_Bypass::load();
}
