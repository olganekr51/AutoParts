$(document).ready(function () {
    let $thumbsElem = $('.product-thumbs-slider');
    let thumbsSlider = null;

    if ($thumbsElem.length) {
        thumbsSlider = new Swiper('.product-thumbs-slider', {
            spaceBetween: 10,
            slidesPerView: 4,
            freeMode: true,
            watchSlidesProgress: true,
        });
    }

    if ($('.product-main-slider').length) {
        let mainSlider = new Swiper('.product-main-slider', {
            spaceBetween: 10,
            navigation: {
                nextEl: '.swiper-button-next',
                prevEl: '.swiper-button-prev',
            },
            thumbs: thumbsSlider ? {swiper: thumbsSlider} : false,
        });
    }

    let $qtyInput = $('#detail-qty');
    let $buyBtn = $('#detail-buy-btn');
    let $btnMinus = $('.js-qty-minus');
    let $btnPlus = $('.js-qty-plus');

    function checkLimits(val) {
        let min = parseInt($qtyInput.attr('min')) || 1;
        let max = parseInt($qtyInput.attr('max')) || 999;

        $btnMinus.toggleClass('disabled', val <= min);
        $btnPlus.toggleClass('disabled', val >= max);
    }

    $btnMinus.on('click', function () {
        let val = parseInt($qtyInput.val()) || 1;
        let min = parseInt($qtyInput.attr('min')) || 1;
        if (val > min) {
            $qtyInput.val(val - 1).trigger('change');
        }
    });

    $btnPlus.on('click', function () {
        let val = parseInt($qtyInput.val()) || 1;
        let max = parseInt($qtyInput.attr('max')) || 999;
        if (val < max) {
            $qtyInput.val(val + 1).trigger('change');
        }
    });

    $qtyInput.on('change keyup', function () {
        let currentVal = parseInt($(this).val()) || 1;
        let max = parseInt($(this).attr('max')) || 999;
        let min = parseInt($(this).attr('min')) || 1;

        if (currentVal > max) currentVal = max;
        if (currentVal < min) currentVal = min;

        $(this).val(currentVal);
        $buyBtn.attr('data-quantity', currentVal);
        checkLimits(currentVal);
    });

    checkLimits(parseInt($qtyInput.val()) || 1);
});
