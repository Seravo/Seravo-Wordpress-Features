'use strict';

jQuery(document).ready(function($) {
  // Reports load
  // Generic ajax report loader function
  function seravo_load_report(section) {
    jQuery.post(
      ajaxurl, {
        'action': 'seravo_backups',
        'section': section
      },
      function (rawData) {
        if (rawData.length == 0) {
          jQuery('#' + section).html('No data returned for section.');
        }

        jQuery('#' + section + '_loading').fadeOut();
        var data = JSON.parse(rawData);
        jQuery('#' + section).append(data.join("\n"));
      }
    ).fail(function () {
      jQuery('#' + section + '_loading').html('Failed to load. Please try again.');
    });
  }

  // Load on page load
  seravo_load_report('backup_status');
  seravo_load_report('backup_exclude');

  // Load when clicked
  jQuery('#create_backup_button').click(function () {
    jQuery('#create_backup_loading img').show();
    jQuery('#create_backup_button').hide();
    seravo_load_report('create_backup');
  });

  // Postbox toggle-script
  jQuery('.ui-sortable-handle').on('click', function () {
    jQuery(this).parent().toggleClass("closed");
  });
  jQuery('.toggle-indicator').on('click', function () {
    jQuery(this).parent().parent().toggleClass("closed");
  });

});
