/**
 * @file media.view.js
 */
(function ($, Drupal) {

  "use strict";

  /**
   * Registers behaviours related to view widget.
   */

  Drupal.behaviors.MediaLibraryView = {
    attach: function (context, settings) {
      var $view = $('.view-content', context);
      $view.once('media').prepend('<div class="grid-sizer"></div><div class="gutter-sizer"></div>');

      // Indicate that images are loading.
      $view.append('<div class="ajax-progress ajax-progress-fullscreen">&nbsp;</div>');
      $view.imagesLoaded(function () {
        $view.masonry({
          columnWidth: '.grid-sizer',
          gutter: '.gutter-sizer',
          itemSelector: '.grid-item',
          percentPosition: true,
          isFitWidth:true
        });
        // Add a class to reveal the loaded images, which avoids FOUC.
        $('.grid-item').addClass('item-style');
        $view.find('.ajax-progress').remove();
      });

      $('.grid-item').once('bind-click-event').click(function () {
        var input = $(this).find('.views-field-entity-browser-select input');
        input.prop('checked', !input.prop('checked'));
        if (input.prop('checked')) {
          $(this).addClass('checked');
          var render = $(this).find('.views-field-rendered-entity');
          $(render).css('opacity',0.3);
        }
        else {
          $(this).removeClass('checked');
          var render = $(this).find('.views-field-rendered-entity');
          $(render).css('opacity',1);
        }
      });
      /*For User Page*/

      var $viewuser = $('.view-content-library', context);
      $viewuser.once('media').prepend('<div class="grid-sizer-library"></div><div class="gutter-sizer-library"></div>');

      // Indicate that images are loading.
      $viewuser.append('<div class="ajax-progress ajax-progress-fullscreen">&nbsp;</div>');
      $viewuser.imagesLoaded(function () {
        $viewuser.masonry({
          columnWidth: '.grid-sizer-library',
          gutter: '.gutter-sizer-library',
          itemSelector: '.grid-item-library',
          percentPosition: true,
          isFitWidth:true
        });
        // Add a class to reveal the loaded images, which avoids FOUC.
        $('.grid-item-library').addClass('item-style');
        $viewuser.find('.ajax-progress').remove();
      });

    }
  };

}(jQuery, Drupal));
