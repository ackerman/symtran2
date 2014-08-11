/* 
 * Enable the jQuery plugin datetimepicker on various fields in the
 * Reservation Add and Edit forms.
 * 
 * For details on using the plugin, see:
 * http://xdsoft.net/jqplugins/datetimepicker/. The version used here is
 * slightly modified from the original to provide AM/PM formatting in the
 * Timepicker.
 */
jQuery(document).ready(function($) {
  var allowTimesArray = [];
  for (var i = 8, j = 0; i < 20; i++) {
    for (j = 0; j < 60; j += 15) {
      h = (i < 10 ? '0' : '') + i;
      m = (j < 10 ? '0' : '') + j;
      var myTime = h + ':' + m;
      allowTimesArray.push(myTime);
    }
  }
  allowTimesArray.push('20:00');
  
  //var today = moment().format("YYYY-MM-DD");
  //$('#ginsberg_transportationbundle_reservation_dateToShow').val(today);
  $('#ginsberg_transportationbundle_reservation_start').datetimepicker({
    onGenerate:function( ct ){
      $(this).find('.xdsoft_date.xdsoft_weekend')
        .addClass('xdsoft_disabled');
    },
    timepicker: true,
    inline: false,
    mindate: 0,
    weekends: ['06.01.2014', '07.01.2014','08.01.2014','09.01.2014','10.01.2014','11.01.2014','12.01.2014','01.01.2015','02.01.2015','03.01.2015','04.01.2015','05.01.2015','06.01.2015'],
    allowTimes: allowTimesArray,
    yearStart: '2014',
    yearEnd: '2016',
    roundTime: 'round',
    format: 'Y-m-d g:i a',
    scrollInput: false,
    lang: 'en'
  });
  $('#ginsberg_transportationbundle_reservation_end').datetimepicker({
    onGenerate:function( ct ){
      $(this).find('.xdsoft_date.xdsoft_weekend')
        .addClass('xdsoft_disabled');
    },
    timepicker: true,
    inline: false,
    mindate: 0,
    weekends: ['06.01.2014', '07.01.2014','08.01.2014','09.01.2014','10.01.2014','11.01.2014','12.01.2014','01.01.2015','02.01.2015','03.01.2015','04.01.2015','05.01.2015','06.01.2015'],
    allowTimes: allowTimesArray,
    yearStart: '2014',
    yearEnd: '2016',
    roundTime: 'round',
    format: 'Y-m-d g:i a',
    scrollInput: false,
    lang: 'en'
  });
  $('#ginsberg_transportationbundle_reservation_repeatsUntil').datetimepicker({
    onGenerate:function( ct ){
      $(this).find('.xdsoft_date.xdsoft_weekend')
        .addClass('xdsoft_disabled');
    },
    timepicker: false,
    inline: false,
    mindate: 0,
    weekends: ['06.01.2014', '07.01.2014','08.01.2014','09.01.2014','10.01.2014','11.01.2014','12.01.2014','01.01.2015','02.01.2015','03.01.2015','04.01.2015','05.01.2015','06.01.2015'],
    yearStart: '2014',
    yearEnd: '2016',
    roundTime: 'round',
    format: 'Y-m-d 20:00',
    scrollInput: false,
    lang: 'en'
  });
  
  // Automatically set Start to today at 8am
  var startToday = moment().set('hour', 8).set('minute', 0);
  $('#ginsberg_transportationbundle_reservation_start').val(startToday.format('YYYY-MM-DD hh:mm a'));
  // As soon as the user selects a Start time, set the End time to the same date
  // and time (unless the user already set the End time.)
  $('#ginsberg_transportationbundle_reservation_start').on("change", function(event) {
    if (!$('#ginsberg_transportationbundle_reservation_end')) {
      $('#ginsberg_transportationbundle_reservation_end').val($('#ginsberg_transportationbundle_reservation_start').val());
    }
  })
  
  // Warn users who try to make one long reservation instead of many short 
  // repeating reservations.
  $("form").submit(function(event) {
    var start = $("#ginsberg_transportationbundle_reservation_start").val();
    var end = $("#ginsberg_transportationbundle_reservation_end").val();
    var startDate = new Date(start);
    var endDate = new Date(end);
    if (endDate.getTime() - startDate.getTime() > 1000*60*60*24*7) {
     var returnValue = confirm("This reservation is over a week long - are you trying to make a repeating reservation? If so, press the Cancel button and create the first reservation in the series, remembering to set the \"Repeats Until\" date.");
    }
    if (returnValue == false) {
      event.preventDefault();
    }
  });
});



