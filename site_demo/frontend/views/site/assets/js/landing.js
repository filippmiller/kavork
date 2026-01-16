$(document).ready(function() {
  var $navbar = $("#mNavbar");
  
  AdjustHeader(); // Incase the user loads the page from halfway down (or something);
  $(window).scroll(function() {
      AdjustHeader();
  });
  
  function AdjustHeader(){
    if ($(window).scrollTop() > 50) {
      if (!$navbar.hasClass("navbar-fixed-top")) {
        $navbar.addClass("navbar-fixed-top");
      }
    } else {
      $navbar.removeClass("navbar-fixed-top");
    }
  } 
  });
//$(document).ready(function() {
//  $("#shop-link").click(function() {
//    $('#tabius a[href="#shop"]').tab('show');
//  });
//}
 //);  
 
 
 
//(function(){ 

//            document.onreadystatechange = () => {

 //             if (document.readyState === 'complete') {
//$(document).ready(function() {                        
                /**
                 * Setup your Lazy Line element.
                 * see README file for more settings
                 */

 //               let el = document.querySelector('#device');
 //               let myAnimation = new LazyLinePainter(el, {"ease":"easeInQuart","strokeWidth":0.4,"strokeOpacity":1,"strokeColor":"#222F3D","strokeCap":"round"}); 
//                myAnimation.paint(); 
				
//});				
 //             }
 //           }

//          })();





$(document).ready(function() {
    $("a.link,ul.functional a").click(function () {
        var elementClick = $(this).attr("href")
		 if ($(window).scrollTop() > 50){
        var destination = $(elementClick).offset().top - $('.navbar-header').height();
		}
		else{
		var destination = $(elementClick).offset().top - 124;
		}
        jQuery("html:not(:animated),body:not(:animated)").animate({scrollTop: destination}, 800);
        return false;
    });
$('ul.functional a.mintile.retro').hover(function(){
	$(this).children('.mintile span.brand').css('color','#ffffff');
	$(this).children('.mintile.retro span.tile-content.icon').css('color','#ffffff');
}, function() {
	$(this).children('.mintile span.brand').css('color','#454545');
	$(this).children('.mintile.retro span.tile-content.icon').css('color','rgba(154, 156, 160, 0.8)');
});
//$('a.mintile.retro').hover(function(){
//	$(this).children('.mintile.retro span.tile-content.icon').css('color','white');
//}, function() {
//	$(this).children('.mintile.retro span.tile-content.icon').css('color','grey');
//});
	});
$(function() {
	"use strict"; 
  // при нажатии на кнопку scrollup
  $('.scrollup').click(function() {
    // переместиться в верхнюю часть страницы
    $("html, body").animate({
      scrollTop:0
    },600);
  })
})
// при прокрутке окна (window)
$(window).scroll(function() {
  // если пользователь прокрутил страницу более чем на 200px
  if ($(this).scrollTop()>200) {
    // то сделать кнопку scrollup видимой
    $('.scrollup').fadeIn();
  }
  // иначе скрыть кнопку scrollup
  else {
    $('.scrollup').fadeOut();
  }
});
$( '#mNavbar .navbar-nav a' ).on( 'click', function () {
	$( '#mNavbar .navbar-nav' ).find( 'li.active' ).removeClass( 'active' );
	$( this ).parent( 'li' ).addClass( 'active' );
});

    // closes the responsive menu on menu item click
    $(".navbar-nav li a").on("click", function(event) {
    if (!$(this).parent().hasClass('dropdown'))
        $(".navbar-collapse").collapse('hide');
	});
	
	/* SOLUTIONS IMAGE GALLERY SWIPER */
	var MySwiper = new Swiper('.my-swiper-container', {
		pagination: '.swiper-pagination',
		paginationClickable: true,
		loop: true,
		spaceBetween: 30,
        /*effect: 'fade',*/
		autoplayDisableOnInteraction: false,
		autoplay: 4000
    });
	
	
	/* MAGNIFIC POPUP FOR SOLUTIONS IMAGE GALLERY SWIPER */
	//$('.popup-link').magnificPopup({
	//	removalDelay: 300,
	//	type: 'image',
	//	callbacks: {
	//		beforeOpen: function() {
	//			this.st.image.markup = this.st.image.markup.replace('mfp-figure', 'mfp-figure ' + this.st.el.attr('data-effect'));
	//		},
	//		
	//		beforeClose: function() {
	//			$('.mfp-figure').addClass('fadeOut');
	//		}
	//	},
	//	gallery:{
	//		enabled:true //enable gallery mode
	//	}
	//});
	
	
	/* MAGNIFIC POPUP FOR PRODUCT DETAILS */
	//$('.popup-with-move-anim').magnificPopup({
	//	type: 'inline',
		
	//	fixedContentPos: false, /* keep it false to avoid html tag shift with margin-right: 17px */
	//	fixedBgPos: true,

	//	overflowY: 'auto',

	//	closeBtnInside: true,
	//	preloader: false,
		
	//	midClick: true,
	//	removalDelay: 300,
	//	mainClass: 'my-mfp-slide-bottom'
	//});
	
	
	/* PORTFOLIO ISOTOPE INITIALIZATION */
	//$('.grid').imagesLoaded( function() {
	//	var $grid = $('.grid').isotope({
	//		// options
	//		itemSelector: '.element-item',
	//		layoutMode: 'fitRows'
	//	});
		
		// filter items on button click
	//	$('.filters-button-group').on( 'click', 'a', function() {
	//		var filterValue = $(this).attr('data-filter');
	//		$grid.isotope({ filter: filterValue });
	//	// change is-checked class on buttons
	//	$('.button-group').each( function( i, buttonGroup ) {
	//		var $buttonGroup = $( buttonGroup );
	//		$buttonGroup.on( 'click', 'a', function() {
	//			$buttonGroup.find('.is-checked').removeClass('is-checked');
	//			$( this ).addClass('is-checked');
	//		});	
	//	});
	//});
	
		
	/* COUNTERUP - STATISTICS */
    $('.counter').counterUp({
        delay: 10,
        time: 1200
    });
		
	/* REMOVES LONG FOCUS ON BUTTONS */
	$(".button, a, button").mouseup(function(){
		$(this).blur();
	});
	/* stop dropdown */
	$(document).on(
    'click.bs.dropdown.data-api', 
    '[data-toggle="mod"]', // тут прописываем селектор, который добавляем к тем .dropdown-menu, которые не должна закрываться по клику на внутренних элементах
    function (e) { e.stopPropagation() }
);