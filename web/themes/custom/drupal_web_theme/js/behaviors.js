(function (Drupal, once) {
  'use strict';

  /**
   * Marks the current page's nav link as active by comparing hrefs.
   * Kept deliberately tiny — no framework needed for 3 nav links.
   */
  Drupal.behaviors.drupalWebNavActive = {
    attach(context) {
      once('drupal-web-nav-active', '.site-nav a', context).forEach((link) => {
        if (link.pathname === window.location.pathname) {
          link.classList.add('is-active');
        }
      });
    },
  };
})(Drupal, once);
