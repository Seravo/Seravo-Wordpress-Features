<?php
/*
 * Description: Custom redirects to enforce primary domain or https
 *
 * This module should be called as early as possible so that the redirect
 * happens as quickly as possible and no other parts of the WordPress are
 * initialized in vain.
 */

namespace Seravo;

// Deny direct access to this file
if ( ! defined('ABSPATH') ) {
  die('Access denied!');
}

if ( ! class_exists('Security_Restrictions') ) {

  class Security_Restrictions {

    // NOTE! This function is executed on every page load

    public static function load() {

      if ( get_option( 'seravo-disable-xml-rpc' ) ) {
        /*
         * When this is active any request like:
         *   curl -d '<?xml version="1.0"?> <methodCall> <methodName>wp.getUsersBlogs</methodName> \
         *    <params> <param> <value>username</value> </param> <param> <value>password</value> </param>\
         *    </params> </methodCall>' https://<siteurl>/xmlrpc.php
         * will yield 'faultCode 405' and 'XML-RPC is not available'
         */
        add_filter( 'xmlrpc_enabled', '__return_false' );

        // Disable X-Pingback to header
        // since when XML-RPC is disabled pingbacks will not work anyway
        add_filter( 'wp_headers', array( __CLASS__, 'disable_x_pingback' ) );
      }

      if ( get_option( 'seravo-disable-json-user-enumeration' ) ) {
        /*
         * When this is active any request like
         *   curl -iL https://<siteurl>/wp-json/wp/v2/users/ -H Pragma:no-cache
         * will yield '{"code":"rest_no_route"..'
         */
        add_filter('rest_endpoints', array( __CLASS__, 'disable_user_endpoints' ), 1000);
      }

      if ( get_option( 'seravo-disable-get-author-enumeration' ) ) {
        /*
         * When this is active any request like
         *   curl -iL -H Pragma:no-cache https://<siteurl>/?author=7
         * will not redirect to /author/<name>/ and thus not translate used ids
         * to usernames, which some consider as a data leak.
         *
         * In the admin area this must still work otherwise user editing screens
         * stop working.
         */
        if ( ! is_admin() ) {
          add_filter(
            'query_vars',
            function ( $public_query_vars ) {
              $key = array_search( 'author', $public_query_vars, true );
              if ( false !== $key ) {
                unset( $public_query_vars[ $key ] );
              }
              return $public_query_vars;
            }
          );
        }
      }

    }

    public static function disable_x_pingback( $headers ) {
      unset( $headers['X-Pingback'] );
      return $headers;
    }

    public static function disable_user_endpoints( $endpoints ) {
      // Don't disable API for logged in users, otherwise e.g. the author change
      // dropdown in Gutenberg will emit JavaScript errors and fail to render.
      if ( is_user_logged_in() ) {
        // Bail out without filtering anything
        return $endpoints;
      }

      // Disable listing users
      if ( isset($endpoints['/wp/v2/users']) ) {
        unset($endpoints['/wp/v2/users']);
      }
      // Disable fetching a single user
      if ( isset($endpoints['/wp/v2/users/(?P<id>[\d]+)']) ) {
        unset($endpoints['/wp/v2/users/(?P<id>[\d]+)']);
      }
      return $endpoints;
    }

  }

  Security_Restrictions::load();
}
