		$(document).ready(function() {
      //Initialization: Save original "until" date so that we can toggle it
      var original_until_date = $('#until').val();
      //alert('original_until_date ' + original_until_date);
      // Initialization: Is this a Project Community reservation or not
      if ($('#Reservation_program_id').val() == '') {
        initialize_dests();
      } else if ($('#Reservation_program_id').val() == '2') {
        //code
        make_pc();
      } else {
        make_non_pc();
      }

      // Store destination and destination_id
      var dest = $('#Reservation_destination').val();
      var dest_id = $('#Reservation_destination_id').val();
      //alert('dest = ' + dest + ' and dest_id = ' + dest_id);

      // #Reservation_start and #Reservation_end are the POST variables that we are
      // calculating based on the dates and times set in #date_start
      $('#Reservation_start, #Reservation_end').css('display','none');
			$('#date_start').datepicker({minDate: new Date()});
      if (original_until_date == '') {
        $('#until').datepicker({minDate: new Date()});
      }
      //alert('until minDate = ' + $('#until').datepicker("option", "minDate"));

      // If we are editing an existing reservation, get the start and end dates and times
     if($('#Reservation_start').val() != undefined && $('#Reservation_start').val() != '') {
       var startdate = $('#Reservation_start').val();
       var startdatestart = startdate.indexOf('m') + 2;
        var startdatesubstring = startdate.substring(startdatestart);
        $('#date_start').val(startdatesubstring);

        // Get the time portion of the string and update the Start Time dropdown to show that value
        var starttimeend = startdate.indexOf('m') + 1;
        var starttimesubstring = startdate.substring(0,starttimeend);
        starttimestr = starttimesubstring.replace(/\s/g, '');
        $('#time_start option:selected').removeAttr('selected');
        $('#time_start option[value = "' + starttimestr + '"]').attr('selected', 'selected');
      }

      if($('#Reservation_end').val() != undefined && $('#Reservation_end').val() != "") {
        var enddate = $('#Reservation_end').val();
        var enddatestart = enddate.indexOf('m') + 2;
        var enddatesubstring = enddate.substring(enddatestart);
        $('#date_end').val(enddatesubstring);

        // Get the time portion of the string and update the End Time dropdown to show that value
        var endtimeend = enddate.indexOf('m') + 1;
        var endtimesubstring = enddate.substring(0,endtimeend);
        endtimestr = endtimesubstring.replace(/\s/g, '');
        $('#time_end option:selected').removeAttr('selected');
        $('#time_end option[value = "' + endtimestr + '"]').attr('selected', 'selected');
      }
      $('#date_end').datepicker({minDate: new Date()});
      //$('#until').datepicker({minDate: new Date()});

       $('#date_start').change(function(){
         $('#Reservation_start').val($(this).val() + ' ' + $('#time_start').val());
       });

       // If they selected a Saturday of Sunday for the start day, warn them and send them back to the
       // datepicker when they try to set the start time
			 $('#time_start').focus(function(){
         var is_saturday = false;
         var start_date = new Date($('#date_start').val());
         if (start_date.getDay() == 6 || start_date.getDay() == 0) {
            is_saturday = true;
            alert("The Ginsberg Center is closed on weekends. Please adjust your Start date.");
            $('#date_start').focus();
         }
			 });

			 $('#time_start').change(function() {
				$('#Reservation_start').val($('#date_start').val() + ' ' + $(this).val());
			 });

			 $('#date_end').change(function(){
				$('#Reservation_end').val($(this).val() + ' ' + $('#time_end').val());
			 });

			 $('#time_end').change(function() {
				$('#Reservation_end').val($('#date_end').val() + ' ' + $(this).val());
			 });

       // If they selected a Saturday or Sunday for the end day, warn them and send them back to the
       // datepicker when they try to set the end time
			 $('#time_end').focus(function(){
         var is_saturday = false;
         var end_date = new Date($('#date_end').val());
         if (end_date.getDay() == 6 || end_date.getDay() == 0) {
            is_saturday = true;
            alert("The Ginsberg Center is closed on weekends. Please adjust your End date.");
            $('#date_end').focus();
         }
			 });

       $('#edit_series').change(function() {
         if($('#edit_series').attr('checked') != 'checked') {
            $('#repeating').removeAttr('checked');
            $('#until').val('');
         } else {
            $('#repeating').attr('checked','checked');
            $('#until').val(original_until_date);
         }
       });

       // Handle different requirements for the Destination field, depending oon whether the reservation
       // is for Project Community or not. If PC, then use the destination_id field with a drop-down menu.
       // Otherwise use the old destination text field.
      $('#Reservation_program_id').change(function() {
        if ($('#Reservation_program_id').val() == '2') {
          dest = $('#Reservation_destination').val();
          make_pc();
          //$('#Reservation_destination').val('');

        } else {
          make_non_pc();
          dest_id = $('#Reservation_destination_id').val();
        }

      });

			 $('input[type=submit]').click(function(evt) {
				// Yii validation doesn't work right (so far) with repeating dates, so
        // do Javascript validation here.
        // TODO: why id validation working in public Site for repeating reservations?

        // If we're in the admin area, check for uniqname. In public area, uniqname is automatically suppplied.
        if ($('#Reservation_driver_uniqname').length > 0) {
         if ($('#Reservation_driver_uniqname').val() == '' || $('#Reservation_driver_uniqname').val() == undefined) {
            alert('You must enter a driver uniqname.');
            evt.preventDefault();
            evt.stopPropagation();
         }
        }

        var start_date = $('#date_start').val();
        var start_date = new Date($('#date_start').val());
         if (start_date.getDay() == 6 || start_date.getDay() ==0) {
            alert("The Ginsberg Center is closed on weekends. Please adjust your Start date.");
            $('#date_start').focus();
            evt.preventDefault();
            evt.stopPropagation();
         }

         var end_date = $('#date_end').val();
         var end_date = new Date($('#date_end').val());
         if (end_date.getDay() == 6 || end_date.getDay() == 0) {
            alert("The Ginsberg Center is closed on weekends. Please adjust your End date.");
            $('#date_end').focus();
            evt.preventDefault();
            evt.stopPropagation();
         }

        if ($('#Reservation_program_id').val() != '2') {
          //$('#Reservation_destination_id').val('');
          if ($('#Reservation_destination').val() == '' || $('#Reservation_destination').val() == undefined) {
            alert('You must enter a Destination.');
            evt.preventDefault();
            evt.stopPropagation();
          }
        } else {
          //$('#Reservation_destination').empty();
          if ($('#Reservation_destination_id').val() == '' || $('#Reservation_destination_id').val() == undefined) {
            alert('You must select a Destination.');
            evt.preventDefault();
            evt.stopPropagation();
          }
        }

        if ($('#Reservation_seats_required').val() == '' || $('#Reservation_seats_required').val() == undefined) {
         alert('You must enter the number of seats required.');
         evt.preventDefault();
         evt.stopPropagation();
        }

        if ($('#Reservation_program_id').val() == '0' || $('#Reservation_program_id').val() == '' || $('#Reservation_program_id').val() == undefined) {
         alert('You must select a program.');
         evt.preventDefault();
         evt.stopPropagation();
        }

        if($('#date_start').val() == "Select Start Day" || $('#date_start').val() == '' || $('#date_start').val() == undefined) {
         alert('You must select a Start Date');
         evt.preventDefault();
         evt.stopPropagation();
        }
        if($('#time_start option:selected').val() == "invalid") {
         alert('You must select a Start Time');
         evt.preventDefault();
         evt.stopPropagation();
        }
        if($('#date_end').val() == "Select End Day" || $('#date_end').val() == '' || $('#date_end').val() == undefined) {
         alert('You must select an End Date');
         evt.preventDefault();
         evt.stopPropagation();
        }
        if($('#time_end option:selected').val() == "invalid") {
         alert('You must select an End Time');
         evt.preventDefault();
         evt.stopPropagation();
        }

        // We need a Date object to compare start and end dates to be sure the start date comes first.
        var date_start = new Date($('#date_start').val());
        var startdatestr = $.datepicker.formatDate('MM d, yy', date_start);
        //alert("startdatestr = " + startdatestr);
				var starttime = $('#time_start').val();
				// Get the time without am or pm, but adjust to 24 hour numbers for the hour
				if (starttime.substr(-2) == 'pm') {
					starttime = starttime.split(':');
					var starthour = starttime[0];
					if (starthour != '12') {
						starthour = Number(starthour) + 12;
					}
					var startminute = starttime[1].substr(0,2);
				} else {
					starttime = starttime.split(':');
					var starthour = starttime[0];
					var startminute = starttime[1].substr(0,2);
				}
				startdatestring = startdatestr + ' ' + starthour + ':' + startminute +  ':00';
				// Create the Date object we need to compare start and end dates
				var startdateObject = new Date(startdatestring);

				// Do the same calculations as for start date, but on the end date
        var date_end = new Date($('#date_end').val());
				var enddatestr = $.datepicker.formatDate('MM d, yy', date_end);

				//enddate = enddate.split('/');
				var endtime = $('#time_end').val();
				//alert(endtime.substr(-2));
				if (endtime.substr(-2) == 'pm') {
					endtime = endtime.split(':');
					var endhour = endtime[0];
					if (endhour != '12') {
						endhour = Number(endhour) + 12;
					}
					var endminute = endtime[1].substr(0,2);
					//alert('endhour = ' + endhour + ' endminute = ' + endminute);
				} else {
					endtime = endtime.split(':');
					var endhour = endtime[0];
					var endminute = endtime[1].substr(0,2);
				}
				enddatestring = enddatestr + ' ' + endhour + ':' + endminute +  ':00';
				var enddateObject = new Date(enddatestring);

				// Do the actual comparison of start and end dates
				if (startdateObject >= enddateObject) {
					alert("The reservation start date must come before its end date.");
					evt.preventDefault();
					evt.stopPropagation();
				}

				// If they have checked off that this is a repeating event, they must provide a date for "Until"
				if ($('#repeating').is(':checked') && $('#until').val() == "") {
					alert("If this is a repeating reservation, you must enter an end date in the 'Until' field. Otherwise, uncheck the 'Repeats every week' checkbox.");
					evt.preventDefault();
					evt.stopPropagation();
				}
        // If they have provided a date for "Until" but didn't check "repeating", warn them
				if ($('#until').length > 0) {
            if ($('#until').val() != '' && !$('#repeating').is(':checked')) {
            alert("If this is a repeating reservation, you must check the 'Repeats every week' checkbox. Otherwise, delete the 'Until' date.");
            evt.preventDefault();
            evt.stopPropagation();
          }
        }
			});

      function initialize_dests() {
        //code
        $('.destination_id').addClass('hide');
        $('.destination_id').removeClass('show');
        $('.destination_text').addClass('hide');
        $('.destination_text').removeClass('show');
      }
      function make_pc() {
        //alert('I am PC');
        $('.destination_id').addClass('show');
        $('.destination_id').removeClass('hide');
        $('.destination_text').addClass('hide');
        $('.destination_text').removeClass('show');
      }
      function make_non_pc() {
        //code
        //alert('I am NOT PC');
        $('.destination_id').addClass('hide');
        $('.destination_text').addClass('show');
        $('.destination_id').removeClass('show');
        $('.destination_text').removeClass('hide');
      }

		});
