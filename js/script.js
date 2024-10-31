/*========================================
 =            CUSTOM FUNCTIONS            =
 ========================================*/
function toggleNav() {
    if ($('#site-wrapper').hasClass('show-nav')) {
        // Do things on Nav Close
        $('#site-wrapper').removeClass('show-nav');
    } else {
        // Do things on Nav Open
        $('#site-wrapper').addClass('show-nav');
    }

    //$('#site-wrapper').toggleClass('show-nav');
}

function hideNav() {
    $('#site-wrapper').removeClass('show-nav');
}


/*========================================
 =            ESC FUNCTION               =
 ========================================*/
$(document).keyup(function (e) {
    if (e.keyCode == 27) {
        if ($('#site-wrapper').hasClass('show-nav')) {
            // Assuming you used the function I made from the demo
            toggleNav();
        }
    }
});

/* Avoids dissapearing when a checkbox is checked */
$(function () {
    $('.dropdown').on('shown.bs.dropdown', function (e) {
        $('#searchbox').focus();
    });

    $('.dropdown-menu input[type=text], .dropdown-menu input[type=checkbox], .dropdown-menu ul li, .dropdown-menu ul, .dropdown-menu label, .dropdown-menu div').click(function (e) {
        e.stopPropagation();
    });
});

// Refreshes pending data when button refresh is clicked
$("#refresh").click(function () {


    $("#refresh span").toggleClass("fa-spin");
    $.ajax({
        type: "POST",
        url: sl_ajax_object.ajax_url,
        data: {endpoint: "refresh-data",
            _ajax_nonce: sl_ajax_object.nonce}
    }).done(function (result) {
        $.each(result, function (index, element) {
            $("#" + index).text(element.modified + element.deleted);
        });

        $("#refresh span").toggleClass("fa-spin");
    });
});

/*====================================
 =            ON DOM READY           =
 ====================================*/
document.addEventListener('DOMContentLoaded', function () {

    $('.toggle-nav').click(function () {
        // Calling a function in case you want to expand upon this.
        toggleNav();
    });
});