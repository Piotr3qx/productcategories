$(document).ready(function () {
    $('.owl-carousel').owlCarousel({
        loop: false,
        responsiveClass: true,
        nav: true,
        dots: false,
        responsive: {
            0: {
                items: 2,
                margin: 15
            },
            576: {
                items: 3,
                margin: 30
            },
            992: {
                items: 4,
                margin: 30
            }
        }
    })

    let spinnerSelector = '.categories-products input[name="qty"]';
    $.each($(spinnerSelector), function (index, spinner) {
        $(spinner).TouchSpin({
            verticalbuttons: true,
            verticalupclass: 'material-icons touchspin-up',
            verticaldownclass: 'material-icons touchspin-down',
            buttondown_class: 'btn btn-touchspin js-touchspin js-increase-product-quantity',
            buttonup_class: 'btn btn-touchspin js-touchspin js-decrease-product-quantity',
            min: parseInt($(spinner).attr('min'), 10),
            max: 1000000
        });
    });
});