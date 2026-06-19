$(document).ready(function () {
    $(document).on('click', '.js-buy-btn', function (e) {
        e.preventDefault();

        let $button = $(this);

        if ($button.css('pointer-events') === 'none') {
            return false;
        }

        $button.css('pointer-events', 'none');

        let baseUrl = $button.attr('href');

        let $qtyInput = $button.closest('.js-product-item').find('.qty-input');

        let quantity = $qtyInput.length ? $qtyInput.val() : 1;

        let finalUrl = baseUrl + '&quantity=' + quantity;

        $.get(finalUrl, function () {
            $button.css('pointer-events', 'auto');

            if (window.BX && BX.PopupWindow) {
                let cartPopup = new BX.PopupWindow("bitrix_cart_modal", null, {
                    titleBar: false,
                    content: '<div class="cart-modal">' +
                        'Товар добавлен в корзину ' +
                        '<a href="#" class="go-to-cart">Перейти</a>' +
                        '</div>',
                    closeIcon: true,
                    lightShadow: true,
                    overlay: true,
                    closeByEsc: true,
                    events: {
                        onPopupClose: function () {
                            this.destroy();
                        }
                    }
                });

                cartPopup.show();
            }
        });
    });
});