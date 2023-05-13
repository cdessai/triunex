(function ($, Drupal, settings) {
 
    "use strict";
   
    // aliases
    
    Drupal.behaviors.Crutch = { //the name of our behavior
      attach: function (context, settings) {
        
        $(document).ready(function() {

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
            $('table#msortTable2').tablesorter({
                sortList: [[2, 0]],
                widgets: ['staticRow'],
                widgetOptions: {
                    staticRow_class: ".tablesorter-static"
                }
            });
            $('table#msortTable3').tablesorter({
                sortList: [[1, 0], [3, 0]],
                widgets: ['staticRow'],
                widgetOptions: {
                    staticRow_class: ".tablesorter-static"
                }
            });
            $('table#msortTable4').tablesorter({
                sortList: [[1, 1]],
                widgets: ['staticRow'],
                widgetOptions: {
                    staticRow_class: ".tablesorter-static"
                }
            });

            // Listen for click on toggle checkbox
            $('#select-all').click(function(event) {
                if(this.checked) {
                    // Iterate each checkbox within the form
                    $('input[class="form-checkbox"]').each(function() {
                        this.checked = true;
                    });
                } else {
                    $('input[class="form-checkbox"]').each(function() {
                        this.checked = false;
                    });
                }
            });
        });
      }
    };
    
    
  })(jQuery, Drupal, drupalSettings);