(function ($, Drupal, settings) {
 
  "use strict";
 
  Drupal.behaviors.Crutch = { //the name of our behavior
    attach: function (context, settings) {

    $(document).ready(function() {
      $('table#msortTable').tablesorter({
        widgets: ['staticRow'],
        widgetOptions: {
            staticRow_class: ".tablesorter-static"
        }
      });
    });

    }
  }
})(jQuery, Drupal, drupalSettings);