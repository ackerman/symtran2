/* 
 * Hack to make sure no one can select a different reservation from the one 
 * associated with this ticket.
 */
$(document).ready(function() {
  $('#ginsberg_transportationbundle_ticket_reservation option:not(:selected)', this).remove();
});


