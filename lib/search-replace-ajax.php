<?php
// Deny direct access to this file
if ( ! defined('ABSPATH') ) {
  die('Access denied!');
}

function seravo_search_replace( $from, $to, $options ) {
  $command = 'wp search-replace ';
  $output = [];

  $command .= seravo_search_replace_set_flags($options);

  $command .= "'" . $from . "' ";
  $command .= "'" . $to . "'";

  if ( $options['dry_run'] === 'false' && $options['skip_backup'] === 'false' ) {
    $backup = 'wp-backup';
    array_push($output, '<b>$ ' . $backup . '</b>');
    exec($backup . ' 2>&1', $output);
  }
  // Only way this is not true, is if the backups fail
  // wp-backup output line 4 should have "Success: Exported to '<name>_<id>.sql'."
  if ( $options['dry_run'] === 'true' ||
        $options['skip_backup'] === 'true' ||
        strpos( $output[3], 'Success:') !== false ) {
    array_push($output, '<b>$ ' . $command . '</b>');
    exec($command . ' 2>&1', $output);
  } else {
    array_push($output, 'Backup failed... Aborting');
  }
  return $output;
}

function seravo_search_replace_set_flags( $options ) {
  $flags = '';
  if ( $options['dry_run'] === 'true' ) {
    $flags .= '--dry-run ';
  }
  if ( $options['all_tables'] === 'true' ) {
    $flags .= '--all-tables ';
  }
  if ( $options['network'] === 'true' ) {
    $flags .= '--network ';
  } elseif ( is_multisite() ) {
    $flags .= '--url="' . get_site_url() . '" ';
  }
  return $flags;
}

function seravo_ajax_search_replace() {
  switch ( $_REQUEST['section'] ) {
    case 'search_replace':
      echo wp_json_encode(seravo_search_replace(
        $_REQUEST['from'],
        $_REQUEST['to'],
        $_REQUEST['options']
        )
      );
      break;

    default:
      error_log('ERROR: Section ' . $_REQUEST['section'] . ' not defined');
      break;
  }

  wp_die();
}
