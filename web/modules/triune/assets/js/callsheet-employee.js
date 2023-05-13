(function ($, Drupal, settings) {
 
  "use strict";
 
  Drupal.behaviors.Crutch = { //the name of our behavior
    attach: function (context, settings) {
        
       
      // Document ready actions.
      $( document ).ready(function() {
        $('table#msortTable').tablesorter({
          widgets: ['staticRow'],
          widgetOptions: {
              staticRow_class: ".tablesorter-static"
          }
        });
        $('table#msortTable0').tablesorter({
          sortList: [[0, 0]],
          widgets: ['staticRow'],
          widgetOptions: {
              staticRow_class: ".tablesorter-static"
          }
        });
        $('table#msortTable1').tablesorter({
          sortList: [[1, 0]],
          widgets: ['staticRow'],
          widgetOptions: {
              staticRow_class: ".tablesorter-static"
          }
        });
      });
      
      
      $(document).on('change', '#edit-shift-mon', function() {
        var shift = $(this).val();
        var link = $('#action-mon a').attr('href');
        var strlength = link.length;
        link = link.substr(0, strlength - 1) + shift;
        $('#action-mon a').attr('href', link);
      });
      
      $(document).on('change', '#edit-shift-tue', function() {
        var shift = $(this).val();
        var link = $('#action-tue a').attr('href');
        var strlength = link.length;
        link = link.substr(0, strlength - 1) + shift;
        $('#action-tue a').attr('href', link);
      });
      
      $(document).on('change', '#edit-shift-wed', function() {
        var shift = $(this).val();
        var link = $('#action-wed a').attr('href');
        var strlength = link.length;
        link = link.substr(0, strlength - 1) + shift;
        $('#action-wed a').attr('href', link);
      });
      
      $(document).on('change', '#edit-shift-thu', function() {
        var shift = $(this).val();
        var link = $('#action-thu a').attr('href');
        var strlength = link.length;
        link = link.substr(0, strlength - 1) + shift;
        $('#action-thu a').attr('href', link);
      });
      
      $(document).on('change', '#edit-shift-fri', function() {
        var shift = $(this).val();
        var link = $('#action-fri a').attr('href');
        var strlength = link.length;
        link = link.substr(0, strlength - 1) + shift;
        $('#action-fri a').attr('href', link);
      });

    }
  };
  
  
  
})(jQuery, Drupal, drupalSettings);


 