/*
	Editorial by HTML5 UP
	html5up.net | @ajlkn
	Free for personal and commercial use under the CCA 3.0 license (html5up.net/license)
*/

(function ($, Drupal, drupalSettings) {

  Drupal.behaviors.bookBaseMain = {
    attach: function (context, settings) {
      // Add class mobile links if  there is an admin panel.
      if ($('#toolbar-bar').length) {
        $('.toggle').addClass('toggle__admin');
        $('.mobile-logo').addClass('mobile-logo__admin');
        $('.toggle-wrap').addClass('toggle-wrap__admin');
        $('.sidebarfirst').addClass('sidebarfirst__admin');
        $('.mobile-logo-admin').addClass('mobile-logo-admin__admin');
      }
    }
  };

	$('.toggle').on('click', function(e) {
		e.preventDefault();
		$(this).toggleClass('toggle_active');
		$('.sidebarfirst').toggleClass('sidebarfirst_active');
		$('.mobile-logo').toggleClass('mobile-logo__active');
		$('.page-content').toggleClass('page-content_active');
		$('.toggle-wrap').toggleClass('toggle-wrap__active');
  });

})(jQuery, Drupal, drupalSettings);






