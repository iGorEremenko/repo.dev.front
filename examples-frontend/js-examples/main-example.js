// Добавление класса при скролле 
jQuery(window).scroll(function() {
    var the_top = jQuery(document).scrollTop();
    if (the_top > 100) {
        jQuery('.top_mnu').addClass('fixed');
    }
    else {
        jQuery('.top_mnu').removeClass('fixed');
    }
});
