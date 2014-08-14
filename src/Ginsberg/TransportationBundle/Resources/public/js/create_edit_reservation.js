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
  
});
