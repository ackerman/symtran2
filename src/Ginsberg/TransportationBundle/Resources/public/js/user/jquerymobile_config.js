/**
 * jQuery Mobile doesn't allow Symfony to redirect to the correct route, because
 * it uses Ajax to "hijack" (their terminology) standard links and form 
 * submissions. This configuration file turns off that process of hijacking.
 * This is not really a good solution, since you frequently get a flash of
 * unstyled content before the page loads. For some reason, flash messages
 * are also being lost.
 */
$( document ).on("mobileinit", function() {
  $.mobile.ajaxEnabled = false;
});


