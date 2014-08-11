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
  
  var endInput = $('#ginsberg_transportationbundle_reservation_end');
  
  //var today = moment().format("YYYY-MM-DD");
  //$('#ginsberg_transportationbundle_reservation_dateToShow').val(today);
  $('#ginsberg_transportationbundle_reservation_dateToShow').datetimepicker({
    timepicker: false,
    inline: false,
    mindate: 0,
    weekends: ['06.01.2014', '07.01.2014','08.01.2014','09.01.2014','10.01.2014','11.01.2014','12.01.2014','01.01.2015','02.01.2015','03.01.2015','04.01.2015','05.01.2015','06.01.2015'],
    yearStart: '2014',
    yearEnd: '2016',
    roundTime: 'round',
    format: 'Y-m-d',
    scrollInput: false,
    lang: 'en',
    closeOnDateSelect: true
  });
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
  $('#ginsberg_transportationbundle_reservation_checkout').datetimepicker({
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
  $('#ginsberg_transportationbundle_reservation_checkin').datetimepicker({
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
    lang: 'en',
    closeOnDateSelect: true
  });
  
  var startToday = moment().set('hour', 8).set('minute', 0);
  if ($('#ginsberg_transportationbundle_reservation_start').val() == '') {
    $('#ginsberg_transportationbundle_reservation_start').val(startToday.format('YYYY-MM-DD hh:mm a'));
  }
  $('#ginsberg_transportationbundle_reservation_start').on("change", function(event) {
    if ($('#ginsberg_transportationbundle_reservation_end').val() == '') {
      $('#ginsberg_transportationbundle_reservation_end').val($('#ginsberg_transportationbundle_reservation_start').val());
    }
  })
  
  
  // We are using jQuery to set the value of the dateToShow field to the 
  // value of the dateToShow item in the query string. I think this is necessary
  // because the embedded searchCriteria controller doesn't seem to have a way
  // to hold on to the value of the dateToShow across requests. In creating the
  // query string, Symfony uses array notation, which I didn't feel like parsing,
  // so I borrowed this script from StackOverflow: 
  // http://stackoverflow.com/questions/901115/how-can-i-get-query-string-values-in-javascript/23401756#23401756
  
  getQueryStringKey = function(key) {
    return getQueryStringAsObject()[key];
  };


  getQueryStringAsObject = function() {
    var b, cv, e, k, ma, sk, v, r = {},
        d = function (v) { return decodeURIComponent(v).replace(/\+/g, " "); }, //# d(ecode) the v(alue)
        q = window.location.search.substring(1),
        s = /([^&;=]+)=?([^&;]*)/g //# original regex that does not allow for ; as a delimiter:   /([^&=]+)=?([^&]*)/g
    ;

    //# ma(make array) out of the v(alue)
    ma = function(v) {
        //# If the passed v(alue) hasn't been setup as an object
        if (typeof v != "object") {
            //# Grab the cv(current value) then setup the v(alue) as an object
            cv = v;
            v = {};
            v.length = 0;

            //# If there was a cv(current value), .push it into the new v(alue)'s array
            //#     NOTE: This may or may not be 100% logical to do... but it's better than loosing the original value
            if (cv) { Array.prototype.push.call(v, cv); }
        }
        return v;
    };

    //# While we still have key-value e(ntries) from the q(uerystring) via the s(earch regex)...
    while (e = s.exec(q)) { //# while((e = s.exec(q)) !== null) {
        //# Collect the open b(racket) location (if any) then set the d(ecoded) v(alue) from the above split key-value e(ntry) 
        b = e[1].indexOf("[");
        v = d(e[2]);

        //# As long as this is NOT a hash[]-style key-value e(ntry)
        if (b < 0) { //# b == "-1"
            //# d(ecode) the simple k(ey)
            k = d(e[1]);

            //# If the k(ey) already exists
            if (r[k]) {
                //# ma(make array) out of the k(ey) then .push the v(alue) into the k(ey)'s array in the r(eturn value)
                r[k] = ma(r[k]);
                Array.prototype.push.call(r[k], v);
            }
            //# Else this is a new k(ey), so just add the k(ey)/v(alue) into the r(eturn value)
            else {
                r[k] = v;
            }
        }
        //# Else we've got ourselves a hash[]-style key-value e(ntry) 
        else {
            //# Collect the d(ecoded) k(ey) and the d(ecoded) sk(sub-key) based on the b(racket) locations
            k = d(e[1].slice(0, b));
            sk = d(e[1].slice(b + 1, e[1].indexOf("]", b)));

            //# ma(make array) out of the k(ey) 
            r[k] = ma(r[k]);

            //# If we have a sk(sub-key), plug the v(alue) into it
            if (sk) { r[k][sk] = v; }
            //# Else .push the v(alue) into the k(ey)'s array
            else { Array.prototype.push.call(r[k], v); }
        }
    }

    //# Return the r(eturn value)
    return r;
  };
  
  var queryString = getQueryStringKey('ginsberg_transportationbundle_reservation[dateToShow]');
  var dateInGet = queryString;
  if (!$('#ginsberg_transportationbundle_reservation_dateToShow').val() && dateInGet) {
    $("#ginsberg_transportationbundle_reservation_dateToShow").val(dateInGet);
  }
  
});


