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
});



