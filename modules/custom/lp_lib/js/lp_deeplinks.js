(function ($, Drupal, drupalSettings) {
  "use strict";

  // Triggers a deeplink, which is set to the drupalSettings.mobileLink javascript property.
  // It can be triggered immediately on page load (documentReady), or when the user clicks a 
  // link to the dashboard (from the registration process). In the future it's possible that
  // there may be multiple deeplinks on a page, in which case a more complex approach would
  // be required.
  function triggerDeeplink($target) {
    var current = window.location;
    var opened = +new Date();
    var timeout = 500;

    // Sanity check.
    if (!($target && $target.data('deeplink')) && !drupalSettings.mobileLink) {
      return;
    }
    var deeplink = ($target && $target.data('deeplink')) ?
      $target.data('deeplink') : drupalSettings.mobileLink;

    // Add a timeout to reset the location if the deeplink doesn't work.
    // This works on the premise that if the mobile link doesn't work because
    // the app is not installed, the timeout will fail immediately and the
    // timing test inside will be below 500 ms, and so update the current location.
    // https://stackoverflow.com/a/2391031
    setTimeout(function() {
      
      if (+new Date - opened < timeout*2) {
        console.log('Deep link didn\'t work properly, '+ (+new Date - opened) + '.');
        // Reset the window location if the deeplink doesn't work.
        let $link = $target || $('#mobile_link_fallback');
        let url = new URL( ($link.length) ? $link.prop('href') : current);
        if (url.hash.indexOf('noapp') < 0) {
          url.hash = '#noapp';
        }
        
        window.location = url.href;
      }
    }, timeout);

    // If "lifepoints://" is registered the app will launch immediately and your
    // timer won't fire.
    window.location = deeplink;
  }

  // Hooks up any deeplinks found on the page for clicks or redirects to the mobile app.
  function hookupDeeplinks() {

    var is_Android = navigator.userAgent.toLowerCase().indexOf("android") > -1;
    var is_iOS = navigator.userAgent.toLowerCase().match(/iphone|ipad|ipod/) !== null;

    // if we are on one of the email confirmation screens, try redirecting to a deep link in the app.
    if ((is_iOS || is_Android) && (drupalSettings.mobileLink || $('[data-deeplink]').length) && location.hash.indexOf('noapp') == -1) {

      let $deepLinks = $("[data-deeplink]");
      if ($deepLinks.length) {
        // The link to the dashboard is on the page; hook it instead of opening automatically.
        $deepLinks.click(function (ev) {
          ev.preventDefault();
          triggerDeeplink($(ev.delegateTarget));
          return false;
        });
      }
      else{
        // Trigger deeplink immediately.
        triggerDeeplink();
      }
    }

  }
  // Add function to global scope.
  window.hookupDeeplinks = hookupDeeplinks;

  $(document).ready(hookupDeeplinks);

})(jQuery, Drupal, drupalSettings);
