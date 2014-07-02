/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
jQuery(document).ready(function($) {
  $('#ginsberg_transportationbundle_reservation_start').datetimepicker({
    onGenerate:function( ct ){
      $(this).find('.xdsoft_date.xdsoft_weekend')
        .addClass('xdsoft_disabled');
    },
    timepicker: true,
    inline: false,
    mindate: 0,
    weekends: ['06.01.2014', '07.01.2014','08.01.2014','09.01.2014','10.01.2014','11.01.2014','12.01.2014','01.01.2015','02.01.2015','03.01.2015','04.01.2015','05.01.2015','06.01.2015'],
    step: 15,
    minTime: '08:00',
    maxTime: '20:00',
    yearStart: '2014',
    yearEnd: '2015',
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
    step: 15,
    minTime: '08:00',
    maxTime: '20:00',
    yearStart: '2014',
    yearEnd: '2015',
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
    step: 15,
    minTime: '08:00',
    maxTime: '20:00',
    yearStart: '2014',
    yearEnd: '2015',
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
    step: 15,
    minTime: '08:00',
    maxTime: '20:00',
    yearStart: '2014',
    yearEnd: '2015',
    roundTime: 'round',
    format: 'Y-m-d g:i a',
    scrollInput: false,
    lang: 'en'
  });
});



