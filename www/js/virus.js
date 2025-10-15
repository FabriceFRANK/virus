jQuery(document).ready(function() {
    filters();
    menu();
    jQuery(window).on('load', function() {
        loaderHide();
    });
});
function loaderHide() {
    jQuery('.loadingOverlay').css('display','none');
    jQuery('#mainContainer').css('opacity','1');
}
function filters() {
    if(getCookie('virus_perPage')) {            
        jQuery('#perPage').val(getCookie('virus_perPage'));
    }
    jQuery('#perPage').on('change', function() {
        setCookie('virus_perPage',jQuery('#perPage').val(),30);
        jQuery('#formFilters').submit();
    });    
    jQuery('#txtSearch').keyup(function(e) {
        if(e.keyCode==13)   {
            jQuery('#formFilters').submit();               
        }
    })
}
function setCookie(name,value,days) {
    var expires = "";
    if (days) {
        var date = new Date();
        date.setTime(date.getTime() + (days*24*60*60*1000));
        expires = "; expires=" + date.toUTCString();
    }
    document.cookie = name + "=" + (value || "")  + expires + "; path=/";
}
function getCookie(name) {
    var nameEQ = name + "=";
    var ca = document.cookie.split(';');
    for(var i=0;i < ca.length;i++) {
        var c = ca[i];
        while (c.charAt(0)==' ') c = c.substring(1,c.length);
        if (c.indexOf(nameEQ) == 0) return c.substring(nameEQ.length,c.length);
    }
    return null;
}
function menu() {
    jQuery('.menuToggle').click(function() {
        if(jQuery(this).hasClass('open')) {
            jQuery(this).removeClass('open');
            jQuery('#menu').removeClass('open');
        }
        else {
            jQuery(this).addClass('open');
            jQuery('#menu').addClass('open');
        }
    });
    jQuery('.menuArea').click(function(e) {
        e.stopPropagation();
    });
    jQuery('body').click(function() {
        jQuery('#menu').removeClass('open');
        jQuery('.menuToggle').removeClass('open');
    });
}
