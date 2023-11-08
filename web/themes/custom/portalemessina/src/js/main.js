(function ($) {

  Drupal.behaviors.main = {
    attach: function (context, settings) {

      var controller = new ScrollMagic.Controller();

      // if ($('.primo-livello .nav-tabs').length > 0) {
      //   // @TODO: review tabs structure.
      //   $('<div class="tab-content"><div class="tab-pane fade show active" id="tab1" role="tabpanel" aria-labelledby="tab1-tab"></div><div class="tab-pane fade" id="tab2" role="tabpanel" aria-labelledby="tab2-tab"></div></div>').insertAfter('.nav-tabs');
      //   $('.view-servizi').prependTo('#tab1');
      //   $('.view-target').appendTo('#tab2');
      // }

      // Frontpage - form.
      // $('.block-pf-search .form-item').addClass('form-group');

      // Carousel - hero.
      $('.carousel-hero').not('.processed').each(function () {
        var $carousel = $(this).find('.carousel-wrapper');
        $carousel.slick({
          arrows: false,
          autoplaySpeed: 4000,
          autoplay: true,
        });
        $(this).addClass('processed');
      });

      // Carousel - card.
      $('.carousel-card').find('.carousel-wrapper').not('.processed').each(function () {
        var $carousel = $(this);

        $carousel.slick({
          arrows: false,
          slidesToShow: 1,
          infinite: false,
        });

        $(this).addClass('processed');

      });

      $('[href="#servizi"]').click(function (e) {
        $('.carousel-servizi').resize().slick("getSlick").refresh();
      });
      $('[href="#luoghi"]').click(function (e) {
        $('.carousel-luoghi').resize().slick("getSlick").refresh();
      });

      // Carousel - teaser.
      $('.carousel-teaser').not('.processed').each(function () {
        var $carousel = $(this).find('.carousel-wrapper');
        var $navPrev  = $(this).find('.carousel-prev');
        var $navNext  = $(this).find('.carousel-next');
        $carousel.slick({
          infinite: false,
          nextArrow: $navNext,
          prevArrow: $navPrev,
          slidesToShow: 1,
          mobileFirst: true,
          responsive: [
            {
              breakpoint: 768,
              settings: {
                slidesToShow: 2
              }
            },
            {
              breakpoint: 992,
              settings: {
                slidesToShow: 3
              }
            }
          ]
        });
        $(this).addClass('processed');
      });

      // Carousel - gallery.
      $('article .carousel-gallery').not('.processed').each(function () {
        var $carousel = $(this).find('.carousel-wrapper');
        var $navPrev  = $(this).find('.carousel-prev');
        var $navNext  = $(this).find('.carousel-next');
        $carousel.slick({
          infinite: false,
          nextArrow: $navNext,
          prevArrow: $navPrev,
          dots: true,
          appendDots: $('.carousel-dots'),
          slidesToShow: 1,
          mobileFirst: true,
          responsive: [
            {
              breakpoint: 576,
              settings: {
                slidesToShow: 2
              }
            },
            {
              breakpoint: 992,
              settings: {
                slidesToShow: 3
              }
            }
          ]
        });
        $(this).addClass('processed');
      });


      // Search.
      $('.search-link').click(function(e) {
        e.preventDefault();
        $('.search-overlay').fadeIn(200);
        $('body').addClass('h-noscroll');
      });

      $('button.close').click(function(e) {
        e.preventDefault();
        $('.search-overlay').fadeOut(200);
        $('body').removeClass('h-noscroll');
      });
      $('.search-overlay .form-text').attr('placeholder', 'Cerca nel sito');

      // Header.
      if (!$('body').hasClass('header-processed')) {

        var scene = new ScrollMagic.Scene({
          triggerElement: '.it-nav-wrapper',
          triggerHook: 0
        })
        .setPin('.it-nav-wrapper')
        .on("start end", function (e) {
          $('.it-header-wrapper').toggleClass('h-header-sticky');
        })
        .addTo(controller);
        $('body').addClass('header-processed');
      }

      // Sticky sidebar.
      if ($('.sticky-col').length > 0 && !$('body').hasClass('sidebar-processed')) {

        setTimeout(function(){

          var contentHeight = $('.col-cards').height();
          var mapHeight = $('.leaflet-container').height();
          var mapHeightNew = $('.geofield-google-map').height();
          var diffHeight = contentHeight - mapHeightNew;

          if (diffHeight > 0 ) {

            var scene = new ScrollMagic.Scene({
              triggerElement: '.sticky-col',
              triggerHook: 0,
              offset: -120,
              duration: diffHeight
            })
            .setPin('.sticky-col')
            .addTo(controller);

          }

        }, 2000);

        $('body').addClass('sidebar-processed');
      }


      // Card - guida.
      $('.card-view-more .btn').click(function(e) {
        $(this).parent().prev().toggleClass('h-show');
      });

      // Card - visura full.
      $('.card-visura-full .btn-collapse').click(function(e) {
        $(this).closest('.card-visura-full').toggleClass('h-collapsed');
      });

      // Gallery.
      $('.article.gallery').magnificPopup({
        delegate: '.gallery-element',
        type: 'image',
        removalDelay: 500, //delay removal by X to allow out-animation
        callbacks: {
          beforeOpen: function() {
            // just a hack that adds mfp-anim class to markup
            this.st.image.markup = this.st.image.markup.replace('mfp-figure', 'mfp-figure mfp-with-anim');
            this.st.mainClass = this.st.el.attr('data-effect');
          }
        },
        tLoading: 'Loading image #%curr%...',
        mainClass: 'mfp-img-mobile',
        gallery: {
          enabled: true,
          navigateByImgClick: true,
          preload: [0,1] // Will preload 0 - before current, and 1 after the current image
        },
        image: {
          tError: '<a href="%url%">The image #%curr%</a> could not be loaded.',
          titleSrc: function(item) {
            return item.el.attr('data-title');
          },
          markup: '<div class="mfp-figure">'+
          '<div class="mfp-counter"></div>'+
          '<div class="mfp-close"></div>'+
          '<div class="mfp-img"></div>'+
          '<div class="mfp-bottom-bar">'+
          '<div class="mfp-title"></div>'+
          '</div>'+
          '</div>',
        }
      });

      // Smooth scroll.
      $('.sidebar a[href^="#"]').off().click(function(e) {
        e.preventDefault();
        var position = $($(this).attr('href')).offset().top;

        $('body, html').animate({
          scrollTop: position
        });
      });

      // Temp calendar.

      if ($('.calendar-title').length === 0) {
        var $title = $('.paragraph--type--calendario-eventi').find('.field--name-field-titolo');
        $title.prependTo('.paragraph--type--calendario-eventi #calendar.processed').wrap('<h3 class="calendar-title" />').contents().unwrap();
      }


    }
  }

})(jQuery);
