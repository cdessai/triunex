(function ($, Drupal, settings) {
 
    "use strict";

    Drupal.behaviors.Crutch = { //the name of our behavior
        attach: function (context, settings) {
            
            
        // Document ready actions.
        $( document ).ready(function() {
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
        });
        
        $(document).on('change', '#difference-report-date', function() {
            var date = $('#difference-report-date').val();
            var destination = '/triune/reports/difference?date='+ date;
            location.replace(destination);
        });

        $(document).on('change', '#daily-report-date', function() {
            updateDailyReport();
        });
        $(document).on('change', '#daily-report-office', function() {
            updateDailyReport();
        });
        $(document).on('change', '#daily-report-shift', function() {
            updateDailyReport();
        });
        $(document).on('change', '#daily-report-customer', function() {
            updateDailyReport();
        });
        var updateDailyReport = function() {
            var date = $('#daily-report-date').val();
            var shift = $('#daily-report-shift').val();
            var office = $('#daily-report-office').val();
            var customer = $('#daily-report-customer').val();
            var destination = '/triune/reports/daily?date='+ date +'&shift='+ shift +'&office='+ office + '&customer='+ customer;
            location.replace(destination);
        }
        
        $(document).on('change', '#weekly-report-date', function() {
            updateWeeklyReport();
        });

        $(document).on('change', '#weekly-report-shift', function() {
            updateWeeklyReport();
        });

        $(document).on('change', '#weekly-report-office', function() {
            updateWeeklyReport();
        });

        var updateWeeklyReport = function() {
            var date = $('#weekly-report-date').val();
            var shift = $('#weekly-report-shift').val();
            var office = $('#weekly-report-office').val();
            var destination = '/triune/reports/weekly?date='+ date +'&shift='+ shift +'&office='+ office;
            location.replace(destination);
        }

        $(document).on('click', '#weekly-report-download', function() {
            var date = $('#weekly-report-date').val();
            var shift = $('#weekly-report-shift').val();
            var destination = '/triune/reports/weekly-download?date='+ date +'&shift='+ shift +'&csv=true';
            location.replace(destination);
        });

        }
    };
  })(jQuery, Drupal, drupalSettings);
  
  
   