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
      $('.item-container').css("display", "inline-block");
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
    }
  };

}(jQuery, Drupal));
