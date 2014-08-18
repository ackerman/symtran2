$(document).ready(function() {
  
  // Initialization: Is this a Project Community reservation or not
  if ($('#ginsberg_transportationbundle_reservation_program').val() == '') {
    initialize_dests();
  } else if ($('#ginsberg_transportationbundle_reservation_program option:selected').text() == 'Project Community') {
    //code
    make_pc();
  } else {
    make_non_pc();
  }

  // Store values for destinationText and destination
  var destText = $('#ginsberg_transportationbundle_reservation_destinationText').val();
  var dest_id = $('#ginsberg_transportationbundle_reservation_desination').val();
  //alert('dest = ' + dest + ' and dest_id = ' + dest_id);
  
  
  // Handle different requirements for the Destination field, depending oon whether the reservation
  // is for Project Community or not. If PC, then use the destination_id field with a drop-down menu.
  // Otherwise use the old destination text field.
  $('#ginsberg_transportationbundle_reservation_program').change(function() {
    if ($('#ginsberg_transportationbundle_reservation_program option:selected').text() == 'Project Community') {
      destText = $('#ginsberg_transportationbundle_reservation_destinationText').val();
      make_pc();
      //$('#Reservation_destination').val('');

    } else {
      make_non_pc();
      dest_id = $('#ginsberg_transportationbundle_reservation_destination').val();
    }

  });
  
  // Destination-related functions
  function initialize_dests() {
      //code
      $('#destination').addClass('hide');
      $('#destination').removeClass('show');
      $('#destination-text').addClass('hide');
      $('#destination-text').removeClass('show');
    }
  function make_pc() {
    //alert('I am PC');
    $('#destination').addClass('show');
    $('#destination').removeClass('hide');
    $('#destination-text').addClass('hide');
    $('#destination-text').removeClass('show');
  }
  function make_non_pc() {
    //code
    //alert('I am NOT PC');
    $('#destination').addClass('hide');
    $('#destination-text').addClass('show');
    $('#destination').removeClass('show');
    $('#destination-text').removeClass('hide');
  }
  
   // Warn users who try to make one long reservation instead of many short 
  // repeating reservations.
  $("form").submit(function(event) {
    var start = $("#ginsberg_transportationbundle_reservation_start").val();
    var end = $("#ginsberg_transportationbundle_reservation_end").val();
    var isRepeating = $("#ginsberg_transportationbundle_reservation_isRepeating").val();
    var repeatsUntil = $("#ginsberg_transportationbundle_reservation_repeatsUntil").val();
    var startDate = new Date(start);
    var endDate = new Date(end);
    var returnValue;
    if (endDate.getTime() <= startDate.getTime()) {
        alert("The End date must come after the Start date.");
        event.preventDefault();
    }
    if (startDate.getTime() < new Date().getTime()) {
        alert("The reservation cannot be in the past.");
        event.preventDefault();
    }
    if ($("#ginsberg_transportationbundle_reservation_isRepeating").is(':checked') && $("#ginsberg_transportationbundle_reservation_repeatsUntil").val() == '') {
        alert("If this is a repeating reservation, you must provide an \"Until\" date. Otherwise, please uncheck the \"Repeats every week\" checkbox.");
        event.preventDefault();
    };
    if (!$("#ginsberg_transportationbundle_reservation_isRepeating").is(':checked') && !$("#ginsberg_transportationbundle_reservation_repeatsUntil").val() == '') {
        alert("If this is a repeating reservation, please check the \"Repeats every week\" checkbox. Otherwise, please delete the date from the \"Until\" field.");
        event.preventDefault();
    };
    
    if (endDate.getTime() - startDate.getTime() > 1000*60*60*24*10) {
     returnValue = confirm("This reservation is over 10 days long - are you trying to make a repeating reservation? If so, press the Cancel button and create the first reservation in the series, remembering to check the \"Repeats every week\" checkbox and to set the \"Until\" date.");
    }
    if (returnValue == false) {
      event.preventDefault();
    }
  });
  
});
