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
            sortList: [[1, 0],  [0, 0]],
            widgets: ['staticRow'],
            widgetOptions: {
                staticRow_class: ".tablesorter-static"
            }
          });
        });
      
      //appends an "active" class to .popup and .popup-content when the "Open" button is clicked
      $(".assign-employee.open").on("click", function() {
        var id = $(this).attr('value');
        var name = $('#name-'+ id).html();
        // Add Employee Info to popup
        $('#assign-employee-form #popup-employee-info').html(name);
        // Set employee id field to proper value
        $('form#assign-employee-form .form-item-employee-id input').val(id);
        // Activate overlay
        $(".popup-overlay, #assign-employee-form.popup-content").addClass("active");
      });
      
      $(".edit-employee.open").on("click", function() {
        var id = $(this).attr('value');
        var name = $('#name-'+ id).html();
        var callsheet_id = $('input[name="callsheet_id"]').val();
        
        // Add Employee Info to popup
        $('#popup-employee-info').html(name);
        // Set employee id field to proper value
        $('#edit-employee-id').val(id);
        // Request field info
        $.post(
          '/triune/callsheet/'+ callsheet_id +'/employee/'+ id +'/info',
          {},
          function(data) {
            var variables = JSON.parse(data);
            // Set fields
            $('#edit-employee-status').val(variables['status']);
            $('#edit-employee-notes').val(variables['notes__value']);
            // Activate overlay
            $(".popup-overlay, #edit-employee-form.popup-content").addClass("active");
          }
        )
      });
      
      //removes the "active" class to .popup and .popup-content when the "Close" button is clicked 
      $(".close").on("click", function() {
        $(".popup-overlay, .popup-content").removeClass("active");
      });
      
      $(document).on('click', '#add-employee', function() {
        $('#dialog-message').dialog('option', 'width', 350);
        $('#dialog-message').dialog('open');
      });
                
      $( "#dialog-message" ).dialog({
        modal: true,
        autoOpen: false,
      });

    }
  };
  
  
  
})(jQuery, Drupal, drupalSettings);


 