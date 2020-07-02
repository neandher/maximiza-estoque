$(document).ready(function () {
    orderItemInit();
});

function orderItemInit() {

    var $collectionHolder;

    var $addOrderItemLink = $('#btn_add_orderItem');
    var $newLinkPanel = $('#panel_add_orderItem');

    $collectionHolder = $('div#orderItem');

    $collectionHolder.find('div.m-portlet__body').each(function () {
        addOrderItemFormDeleteLink($(this));
    });

    var index = $collectionHolder.find('div.m-portlet').length;

    $collectionHolder.data('index', index);

    if (index === 0) {
        addOrderItemForm($collectionHolder, $newLinkPanel);
    } else {
        handleOrder($collectionHolder, true);
    }

    $addOrderItemLink.on('click', function (e) {
        e.preventDefault();
        addOrderItemForm($collectionHolder, $newLinkPanel);
    });
}

function addOrderItemForm($collectionHolder, $newLinkPanel) {

    var prototype = $collectionHolder.data('prototype');
    var index = $collectionHolder.data('index');

    var newForm = prototype.replace(/__name__/g, index);
    var new_index = index + 1;

    $collectionHolder.data('index', new_index);

    var $newFormPanelBody = $('<div class="m-portlet__body"></div>').append(newForm);
    var $newFormPanel = $('<div class="m-portlet m-portlet--mobile m-portlet--bordered" id="portlet-id-' + index + '"></div>').append($newFormPanelBody);
    $newLinkPanel.before($newFormPanel);

    var brandInfo = document.createElement('span');
    brandInfo.id = "brand_info_" + index;
    $("label[for='order_orderItems_" + index + "_referency']").append(brandInfo);

    // Inputmask.init();
    handleOrder($collectionHolder);
    addOrderItemFormDeleteLink($newFormPanelBody, $newFormPanel);
    $('#order-items').html(new_index);

    return new_index;
}

function addOrderItemFormDeleteLink($newFormPanelBody, $newFormPanel) {
    var $removeForm = $('<div class="row"><div class="col-md-3"><div class="form-group mb-0"><div class="btn btn-sm btn-danger m-btn m-btn--icon m-btn--air" style="cursor: pointer"><span><i class="la la-trash-o"></i><span>Remover Item</span></span></div></div></div></div>');
    $newFormPanelBody.append($removeForm);

    if ($newFormPanel == null) {
        $newFormPanel = $newFormPanelBody.parent('.m-portlet');
    }

    var $collectionHolder = $('div#orderItem');

    $removeForm.on('click', function (e) {
        e.preventDefault();
        $newFormPanel.remove();

        var index = $collectionHolder.find('div.m-portlet').length;

        $collectionHolder.removeData('index');
        $collectionHolder.data('index', index);

        handleOrder($collectionHolder, true);
    });
}

function handleOrder($collectionHolder, checkTotal = false) {

    let index = $collectionHolder.data('index') - 1;
    let $btnCheckRef = $('#portlet-id-' + index + ' #venda-btn-check-ref');
    let $referency = $('#order_orderItems_' + index + '_referency');
    let $quantity = $('#order_orderItems_' + index + '_quantity');
    let $price = $('#order_orderItems_' + index + '_price');
    let $subtotal = $('#order_orderItems_' + index + '_subtotal');
    let $total = $('#order_orderItems_' + index + '_total');
    let $totalView = $('#order_orderItems_' + index + '_totalView');

    if (!checkTotal) {
        $referency.focus();
    }

    let $discount = $('#order_discount');
    let $subtotalGeral = $('#order_subtotal');
    let $subtotalGeralView = $('#order_subtotalView');
    let $totalGeral = $('#order_total');
    let $totalGeralView = $('#order_totalView');

    if (!$discount.val()) {
        $discount.val('0,00');
    }

    $btnCheckRef.click(function (e) {
        e.preventDefault();

        let findAdded = 0;
        $collectionHolder.find('div.m-portlet__body').each(function (i) {
            if ($('#order_orderItems_' + i + '_referency').val().trim() === $referency.val().trim()) {
                findAdded++;
            }
        });

        if (findAdded > 1) {
            alert('Referência já adicionada!');
            $referency.val('');
            $referency.focus();
            return;
        }

        $btnCheckRef.attr('disabled', true);
        let referencyVal = $referency.val();

        $.get(RoutingManager.generate('admin_stock_verify_referency'), {referency: referencyVal, balance: 1})
            .done((stock) => {
                if (stock.balance <= 0) {
                    alert('Saldo indisponível: ' + stock.balance);
                    return;
                }
                $price.val(stock.unitPrice.toString().replace('.', ','));
                $quantity.val(1);
                handleTotal();

                $("#brand_info_" + index).html(` <span class="m-badge m-badge--secondary m-badge--wide">Marca: ${stock.brand.name}</span>`);
                addOrderItemForm($collectionHolder, $('#panel_add_orderItem'));
            })
            .fail(() => {
                alert('Referência não encontrada no estoque');
                $referency.val('');
                $price.val('');
                $referency.focus();
            })
            .always(function () {
                $btnCheckRef.attr('disabled', false);
            });
    });

    let handleTotalGeral = () => {
        let subtotalGeral = 0;
        let totalGeral = 0;
        let quantityTotal = 0;
        $collectionHolder.find('div.m-portlet__body').each(function (i) {
            subtotalGeral += Number($('#order_orderItems_' + i + '_subtotal').val());
            totalGeral += Number($('#order_orderItems_' + i + '_total').val());
            quantityTotal += Number($quantity.val());

            $subtotalGeral.val(subtotalGeral);
            $subtotalGeralView.val(formatCurrency(subtotalGeral));

            $('#order_orderItems_' + i + '_totalView').val(formatCurrency(Number($('#order_orderItems_' + i + '_total').val())));

            if ($subtotalGeral.val() && Number($subtotalGeral.val()) > 0 && $discount.val()) {
                let discount = Number($discount.val().toString().replace(',', '.'));
                $totalGeral.val(totalGeral - discount);
            } else {
                $totalGeral.val($subtotalGeral.val());
            }
            $totalGeralView.val(formatCurrency(Number($totalGeral.val())));
        });

        $('#order-quantity').html(quantityTotal);

        if (checkTotal) {
            $('#order-items').html($collectionHolder.find('div.m-portlet__body').length);
        }
    };

    let handleTotal = () => {
        let price = Number($price.val().toString().replace(',', '.'));

        if (price > 0 && $quantity.val() > 0) {
            $subtotal.val($quantity.val() * price);
            $total.val($subtotal.val());
            $totalView.val(formatCurrency(Number($subtotal.val())));
            handleTotalGeral();
            return;
        }

        $total.val(0);
        $totalView.val(formatCurrency(0));
        handleTotalGeral();
    };

    $quantity.keyup((e) => {
        e.preventDefault();
        handleTotal();
    });

    $price.keyup((e) => {
        e.preventDefault();
        handleTotal();
    });

    $discount.keyup((e) => {
        e.preventDefault();
        handleTotalGeral();
    });

    if (checkTotal) {
        handleTotal();
    }
}

function formatCurrency(value) {
    return value.toLocaleString('pt-BR', {style: 'currency', currency: 'BRL'});
}