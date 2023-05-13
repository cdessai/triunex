var my_id = 0;

(function ($, Drupal, settings) {
 
    "use strict";
   
    // aliases
    
    Drupal.behaviors.Crutch = { //the name of our behavior
      attach: function (context, settings) {

      }
    };
    
    $(document).ready(function() {
        $('#progress').hide();
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

        $('#import-employee-id').keyup(function() {
            var text = $('a#api-employee-import').html();
            if ($(this).val()) {
                text = text.replace('Bulk Import', 'Single Import');
            } else {
                text = text.replace('Single Import', 'Bulk Import');
            }
            $('a#api-employee-import').html(text);
        });
    });

    var lockImport = function() {
        $('#import-employee-id').hide();
        $('#api-employee-import').hide();
        $('#progress').show();
    }
    var unlockImport = function() {
        window.location.replace('/triune/employee/list');
    }

    $.fn.sendRequest = function(callback) {
        $.post(
            '/ascentis/import/request',
            {},
            function(data) {
                var variables = JSON.parse(data);
                if (variables.success) {
                    console.log('import request accepted');
                    my_id = variables.pid;
                } else {
                    console.log('import request rejected');
                    my_id = 0;
                }
                console.log(variables);
                callback();
            }
        );
    }

    $.fn.clientProgress = function() {
        
        if ($('#import-employee-id').val()) {
            // Single Import if Employee ID entered
            console.log($('#import-employee-id').val());
            $.post(
                '/ascentis/import/single/'+ $('#import-employee-id').val(),
                {},
                function(data) {
                    var variables = JSON.parse(data);
                    if (variables.success) {
                        console.log('import success');
                    } else {
                        console.log('import failure');
                    }
                    console.log(variables);
                    unlockImport();
                }
            );
        } else {
            // Bulk Import if no Employee ID entered
            $.post(
                '/ascentis/import/employees/'+ my_id,
                {},
                function(data) {
                    var variables = JSON.parse(data);
                    if (variables.success) {
                        console.log('import success');
                    } else {
                        console.log('import failure');
                    }
                    console.log(variables);
                    unlockImport();
                }
            );
        }
        
        
    }

    $(document).on('click', '#api-employee-import', function() {
        // Lock import button
        lockImport();

        if ($('#import-employee-id').val()) {
        // Single Import
            $.fn.clientProgress();
        } else {
        // Bulk Import
            // send import request
            $.fn.sendRequest(function() {
                // start progress sync
                if (my_id != 0) {
                    console.log(my_id);
                    $.fn.clientProgress();
                }
            });
        
        }
    });
    
})(jQuery, Drupal, drupalSettings);