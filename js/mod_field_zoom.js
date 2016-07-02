/**
 * @file
 * Handles zoom_image.
 */
(function ($, Drupal, drupalSettings) {
    'use strict';
    var setting_zoom = drupalSettings.views.ZoomViews;
    var selector = $("img[id^='zoom-formatter']");
    var settings_mode = {
        'zoomType' : setting_zoom.zoomType,
        'borderColour': setting_zoom.borderColour,
        'borderSize': setting_zoom.borderSize,
        'containLensZoom': (setting_zoom.containLensZoom == 1) ? true : false,
        'cursor' : setting_zoom.cursor,
        'easing' : (setting_zoom.easing == 1 ) ? true : false,
        'easingDuration' : setting_zoom.easingDuration,
        'responsive' : (setting_zoom.responsive == 1 ) ? true : false,
        'scrollZoom' : (setting_zoom.scrollzoom == 1 ) ? true : false,
        'gallery'    :'gallery-zoom',
        'galleryActiveClass':"active",
        'imageCrossfade': true,
    };

    switch (setting_zoom.zoomType){
        case'window':
            settings_mode.zoomTintFadeIn    = setting_zoom.zoomTintFadeIn;
            settings_mode.zoomTintFadeOut   = setting_zoom.zoomTintFadeOut;
            settings_mode.zoomWindowFadeIn  = setting_zoom.zoomWindowFadeIn;
            settings_mode.zoomWindowFadeOut = setting_zoom.zoomWindowFadeOut;
            if(setting_zoom.zoomWindowHeight !== ""){
                settings_mode.zoomWindowHeight = setting_zoom.zoomWindowHeight;
            }
            if(setting_zoom.zoomWindowWidth !== ""){
                settings_mode.zoomWindowWidth = setting_zoom.zoomWindowWidth;
            }
            if(setting_zoom.zoomWindowOffetx !== "") {
                settings_mode.zoomWindowOffetx = setting_zoom.zoomWindowOffetx;
            }
            if(setting_zoom.zoomWindowOffety !== "") {
                settings_mode.zoomWindowOffety = setting_zoom.zoomWindowOffety;
            }
                settings_mode.tint = (setting_zoom.tint == 1 ) ? true : false;
            if(settings_mode.tint == true){
                settings_mode.tintOpacity     = setting_zoom.tintOpacity;
                settings_mode.tintColour      = setting_zoom.tintColour;
                settings_mode.zoomTintFadeIn  = setting_zoom.zoomTintFadeIn;
                settings_mode.zoomTintFadeOut = setting_zoom.zoomTintFadeOut;
            }
            break;
        case'lens':
            settings_mode.lensColour    = setting_zoom.lensColour;
            settings_mode.lensFadeIn    = setting_zoom.lensFadeIn;
            settings_mode.lensFadeOut   = setting_zoom.lensFadeOut;
            settings_mode.lensOpacity   = setting_zoom.lensOpacity;
            settings_mode.lensShape     = setting_zoom.lensShape;
            settings_mode.lensSize      = setting_zoom.lensSize;
            break;
        default:
            break;

    }

    selector.elevateZoom(settings_mode);

    $('.gallery-zoom a').attr('href','#');

    $("#zoom-formatter").bind("click", function(e) {
        var ez =   $('#zoom-formatter').data('elevateZoom');
        ez.closeAll(); //NEW: This function force hides the lens, tint and window
        $.fancybox(ez.getGalleryList());
        return false;
    });
})(jQuery, Drupal, drupalSettings);;