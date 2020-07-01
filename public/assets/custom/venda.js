function delay(fn, ms) {
    let timer = 0;
    return function (...args) {
        clearTimeout(timer);
        timer = setTimeout(fn.bind(this, ...args), ms || 0);
    }
}

let venda = {
    id: null,
    subtotal: 0,
    discount: 0,
    total: 0,
    client: '',
    paymentMethod: '',
    orderItems: []
};

let vendaItemsCount = 1;

$('#modalVenda').on('shown.bs.modal', function (event) {

    let button = $(event.relatedTarget);
    let id = button.data('id');

    if (id) {
        $.get(RoutingManager.generate('admin_order_details', {id}))
            .done((order) => {
                order.orderItems.forEach(el => {
                    vendaAddItem(el);
                });

                venda.id = order.id;
                venda.discount = Number(order.discount);
                venda.total = Number(order.total);

                $('#venda_cliente').val(order.client);
                $('#venda_forma_pagamento').val(order.paymentMethod.split(','));
                $('#venda_exibe_desconto').html(formatCurrency(venda.discount));
                $('#venda_desconto').val(venda.discount.toString().replace('.', ','));
                $('#venda_exibe_total').html(formatCurrency(venda.total));
            })
    }
    else{
        $('#venda_forma_pagamento').prop("selectedIndex", 0).val();
    }

    let $referency = $('#venda_referencia');
    $referency.focus();
    $('#venda-btn-check-ref').click(function (e) {
        e.preventDefault();

        $('#venda-btn-check-ref').attr('disabled', true);

        let referencyVal = $referency.val();
        $('#venda_btn_add').attr('disabled', true);
        $.get(RoutingManager.generate('admin_stock_verify_referency'), {referency: referencyVal, balance: 1})
            .done((stock) => {
                if (stock.balance <= 0) {
                    alert('Saldo indisponível: ' + stock.balance);
                    return;
                }
                $('#venda_marca_referencia').val(stock.brand.name);
                $("#venda_valor").val(stock.unitPrice.toString().replace('.', ','));
            })
            .fail(() => {
                alert('Referência não encontrada no estoque');
                $('#venda_referencia').val('');
                $('#venda_marca_referencia').val('');
                $("#venda_valor").val('');
                $referency.focus();
            })
            .always(function () {
                $referency.attr('disabled', false);
                $('#venda_btn_add').attr('disabled', false);
                $('#venda-btn-check-ref').attr('disabled', false);
            });
    });

    $('#venda_desconto').keyup((e) => {
        e.preventDefault();
        let descontoValor = $(e.currentTarget).val();

        $('#venda_exibe_desconto').html('RS 0,00');

        if (venda.subtotal && venda.subtotal > 0 && descontoValor) {
            const descontoNumber = Number(descontoValor.toString().replace(',', '.'));
            venda.discount = descontoNumber;
            venda.total = venda.subtotal - descontoNumber;
        } else {
            venda.total = venda.subtotal;
        }

        $('#venda_exibe_desconto').html(formatCurrency(descontoValor));
        $('#venda_exibe_total').html(formatCurrency(venda.total));
    });
});


function vendaAddItem(orderItem = null) {
    console.log(orderItem);
    let vendaItem = {
        referency: '',
        quantity: 1,
        subtotal: 0,
        // discount: 0,
        total: 0,
        price: 0
    };

    const referency = orderItem ? orderItem.referency : $('#venda_referencia').val();
    const quantity = orderItem ? Number(orderItem.quantity) : Number($('#venda_quantidade').val());
    const price = orderItem ? Number(orderItem.price) :
        Number($('#venda_valor').val().toString().replace(',', '.'));
    // const discount = Number($('#venda_desconto').val().toString().replace(',', '.'));

    if (!referency || !quantity || !price) {
        return;
    }

    vendaItem.quantity = quantity;
    vendaItem.referency = referency;
    vendaItem.price = price;
    vendaItem.subtotal = orderItem ? Number(orderItem.subtotal) : price * quantity;
    vendaItem.total = orderItem ? Number(orderItem.total) : Number(vendaItem.subtotal);
    vendaItem.identity = vendaItemsCount;

    if(orderItem && orderItem.id){
        vendaItem.id = orderItem && orderItem.id ? orderItem.id : null;
        // vendaItem.orderEntity = orderItem && orderItem.orderEntity ? orderItem.orderEntity : null;
    }

    venda.subtotal += vendaItem.subtotal;
    venda.total += vendaItem.total;
    venda.orderItems.push(vendaItem);

    $vendaTableBody = $('#vendaTableBody');
    // $vendaTableBody.append('<tr id=\'venda_item_'+vendaItemsCount+'\'><td>' + referency + '</td><td>' + formatCurrency(price) + '</td><td>' + quantity + '</td><td>' + formatCurrency(vendaItem.total) + '</td><td><i class=\'fa fa-trash\' onclick=\'vendaRemoveItem('+vendaItemsCount+')\'></td></tr>');
    $vendaTableBody.prepend('<tr id=\'venda_item_' + vendaItemsCount + '\'><td><a href=\'javascript:;\' title="Remover Item" onclick="vendaRemoveItem(' + vendaItemsCount + ')"><i class=\'fa fa-trash\'></i></a></td><td>' + referency + '</td><td>' + formatCurrency(price) + '</td><td>' + quantity + '</td><td>' + formatCurrency(vendaItem.total) + '</td></tr>');

    $('#venda_exibe_subtotal').html(formatCurrency(venda.subtotal));
    $('#venda_exibe_total').html(formatCurrency(venda.total));
    $('#venda_exibe_quantidade_itens').html(venda.orderItems.length);

    $('#venda_referencia').val('');
    $('#venda_quantidade').val(1);
    $('#venda_valor').val('');
    $('#venda_marca_referencia').val('');
    $('#venda_referencia').focus();

    vendaItemsCount++;
}

function vendaRemoveItem(item) {
    $('#venda_item_' + item).remove();
    vendaItemsCount++;

    const findItem = venda.orderItems.find(el => el.identity === item);
    venda.subtotal -= findItem.subtotal;
    venda.total -= findItem.total;
    venda.orderItems = venda.orderItems.filter(el => el.identity !== item);

    $('#venda_exibe_subtotal').html(formatCurrency(venda.subtotal));
    $('#venda_exibe_total').html(formatCurrency(venda.total));
    $('#venda_exibe_quantidade_itens').html(venda.orderItems.length);
}

function finalizaVenda() {
    $('#venda_btn_finaliza').attr('disabled', true);

    venda.client = $('#venda_cliente').val() === '' ? '-' : $('#venda_cliente').val();
    venda.paymentMethod = $('#venda_forma_pagamento').val() ? $('#venda_forma_pagamento').val().join(',') : null;
    venda.subtotal = (venda.subtotal).toString();
    venda.discount = venda.discount.toString();
    venda.total = typeof venda.total === 'string' ? venda.total : (venda.total).toString();

    if (!venda.id) {
        delete venda.id;
    }

    venda.orderItems.forEach(el => {
        el.price = el.price.toString();
        el.subtotal = el.subtotal.toString();
        el.total = el.total.toString();
    });

    $.post(RoutingManager.generate('admin_order_new'), JSON.stringify(venda))
        .done((res) => {
            let msg = 'Venda realizada com sucesso.';
            if (venda.id) {
                msg = 'Venda alterada com sucesso.';
            }

            alert(msg);

            $('#vendaTableBody').html('');
            $('#venda_referencia').val('');
            $('#venda_quantidade').val(1);
            $('#venda_valor').val('');
            $('#venda_marca_referencia').val('');
            $('#venda_desconto').val('0,00');
            $('#venda_forma_pagamento').prop("selectedIndex", 0).val();
            $('#venda_cliente').val('');
            $('#venda_exibe_subtotal').html('R$ 0,00');
            $('#venda_exibe_total').html('R$ 0,00');
            $('#venda_exibe_desconto').html('0,00');
            $('#venda_referencia').focus();
            $('#venda_exibe_quantidade_itens').html('0');
            vendaItemsCount = 0;

            venda = {
                id: null,
                subtotal: 0,
                discount: 0,
                total: 0,
                client: '',
                orderItems: []
            };
        })
        .fail((err) => {
            if (err.responseJSON && err.responseJSON.message !== 'Error') {
                alert(err.responseJSON.message);
                return;
            }
            alert('Erro ao realizar operação.');
        })
        .always(function () {
            $('#venda_btn_finaliza').attr('disabled', false);
        });
}

function formatCurrency(value) {
    return value.toLocaleString('pt-BR', {style: 'currency', currency: 'BRL'});
}

$('#modalVenda').on('hide.bs.modal', function (event) {
    $('#vendaTableBody').html('');
    $('#venda_referencia').val('');
    $('#venda_quantidade').val(1);
    $('#venda_valor').val('');
    $('#venda_marca_referencia').val('');
    $('#venda_desconto').val('0,00');
    $('#venda_forma_pagamento').prop("selectedIndex", 0).val();
    $('#venda_cliente').val('');
    $('#venda_exibe_subtotal').html('R$ 0,00');
    $('#venda_exibe_total').html('R$ 0,00');
    $('#venda_exibe_desconto').html('0,00');
    $('#venda_referencia').focus();
    $('#venda_exibe_quantidade_itens').html('0');
    vendaItemsCount = 0;

    venda = {
        id: null,
        subtotal: 0,
        discount: 0,
        total: 0,
        client: '',
        orderItems: []
    };
});