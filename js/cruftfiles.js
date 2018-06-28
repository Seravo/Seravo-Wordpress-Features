'use strict';

jQuery(document).ready(function($) {
  function seravo_ajax_delete_file(filepath, callback) {
    $.post(
      ajaxurl,
      { type: 'POST',
        'action': 'seravo_delete_file',
        'deletefile': filepath },
      function( rawData ) {
        var data = JSON.parse(rawData);
        data.forEach(function( fileinfo ) {
          if ( fileinfo.success ) {
            callback();
          }
        });
      });
  }

  // Generic ajax report loader function
  function seravo_load_report(section) {
    $.post(
      ajaxurl,
      { 'action': 'seravo_cruftfiles',
        'section': section },
      function(rawData) {
        if (rawData.length == 0) {
          $('#' + section).html(seravo_cruftfiles_loc.no_data);
        }
        $('#' + section + '_loading').fadeOut();
        var data = JSON.parse(rawData);
        var filecount = 0;
        $.each( data, function( i, file){
          if (file != '') {
            filecount++;
            $( '#cruftfiles_entries' ).append('<tr class="cruftfile"><td class="cruftfile-delete"><input data-file-name="' + file + '" class="cruftfile-check" type="checkbox"></td><td class="cruftfile-path">'
                                                + file + '</td></tr>');
          }
        });
        if (filecount == 0) {

          $( '#cruftfiles_status' ).append('<b>' + seravo_cruftfiles_loc.no_cruftfiles + '</b>');
        } else {

          $( '#cruftfiles_entries' ).parents(':eq(0)').prepend('<thead><tr><td><input class="cruftfile-select-all" type="checkbox" ></td><td class="cruft-tool-selector"><b>Select all files</b></td></tr></thead>');
          $( '#cruftfiles_entries' ).parents(':eq(0)').append('<tfoot class="less-than"><tr><td><input class="cruftfile-select-all" type="checkbox" ></td><td class="cruft-tool-selector"><b>Select all files</b></td></tr></tfoot>');
          $( '#cruftfiles_status' ).append('<button class="cruftfile-delete-button button" type="button">' + seravo_cruftfiles_loc.delete + '</b>');
          if ( filecount < 30 ) {
            $( '.less-than' ).hide();
          }
        }
        $('.cruftfile-select-all').click(function(event) {
          if (this.checked) {
              // Iterate each checkbox
              $('.cruftfile-check, .cruftfile-select-all').each(function() {
                  this.checked = true;
              });
          } else {
              $('.cruftfile-check, .cruftfile-select-all').each(function() {
                  this.checked = false;
              });
          }
        });
        $('.cruftfile-check').click(function() {
          if ( $('.cruftfile-check:checked').length == $('.cruftfile-check').length ) {
            $('.cruftfile-select-all').each(function() {
              this.checked = true;
            });
          } else {
            $('.cruftfile-select-all').each(function() {
              this.checked = false;
            });
          }

        });
        $( '#cruftfiles_status_loading img' ).fadeOut
        $('.cruftfile-delete-button').click(function(event) {
          event.preventDefault();
          var is_user_sure = confirm(seravo_cruftfiles_loc.confirm);
          if ( ! is_user_sure) {
            return;
          }
          var cruft_list = new Array();
          var remove_rows = new Array();
          $('.cruftfile-check').each(function(){
            if ( $(this).is(":checked") ) {
              remove_rows.push( $(this).parents(':eq(1)') );
              cruft_list.push( $(this).attr('data-file-name') );
            }
          });
          if ( cruft_list.length > 0 ) {
            seravo_ajax_delete_file(cruft_list, function() {
              remove_rows.forEach(function( row ) {
                row.animate({
                  opacity: 0
                }, 600, function() {
                  row.remove();
                  if ( $('#cruftfiles_entries').children().length < 30 ) {
                    $( '.less-than' ).hide(400);
                  }
                  if ( $('#cruftfiles_entries').children().length == 0 ) {
                    $( '#cruftfiles_status' ).children().remove();
                    $( '#cruftfiles_status' ).append('<b>' + seravo_cruftfiles_loc.no_cruftfiles + '</b>');
                  }
                });
              });
            });
          }
        });
      }
    ).fail(function() {
      $('#' + section + '_loading').html(seravo_cruftfiles_loc.fail);
    });
  }

  // Load on page load
  seravo_load_report('cruftfiles_status');
    // Postbox toggle script
  jQuery('.ui-sortable-handle').on('click', function () {
    jQuery(this).parent().toggleClass("closed");
    if (jQuery(this).parent().hasClass("closed")) {
      jQuery(this).parents().eq(3).height(60);
    } else {
      jQuery(this).parents().eq(3).height('auto');
    }
  });
  jQuery('.toggle-indicator').on('click', function () {
    jQuery(this).parent().parent().toggleClass("closed");
    if (jQuery(this).parent().hasClass("closed")) {
      jQuery(this).parents().eq(4).height(60);
    } else {
      jQuery(this).parents().eq(4).height('auto');
    }
  });
});
