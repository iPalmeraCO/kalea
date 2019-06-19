/**
 * Slider
 * 
 * @author Slava Yurthev
 */
 define(['jquery', 'SY_Slider/js/bxslider'], function($){
    console.log("ACA22");
    $('.bxslider').bxSlider({
    mode: 'fade',
    captions: true,
    slideWidth: 800,
    preloadImages : "all",
     onSliderLoad: function(){
          $(".bxslider").css("visibility", "visible");
         //$('#preloader').fadeOut('slow');
          //$('body').css({'overflow':'visible'})
      }
  });

});
/*define(['jquery', 'SY_Slider/js/bxslider'], function(){
  return function(config, element){
    jQuery(element).bxSlider(config);
  }
});*/
/*
define(['jquery', 'https://cdn.jsdelivr.net/bxslider/4.2.12/jquery.bxslider.min.js'], function($){
  console.log("SLIDER");


$( document ).ready(function() {
    $('.bxslider').bxSlider({
    mode: 'fade',
    captions: true,
    slideWidth: 800
  });
});
  
});*/