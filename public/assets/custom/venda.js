function delay(fn, ms) {
    let timer = 0;
    return function (...args) {
        clearTimeout(timer);
        timer = setTimeout(fn.bind(this, ...args), ms || 0);
    }
}

$('#modalVenda').on('shown.bs.modal', function (event) {

    let $referency = $('#venda_referencia');
    $referency.focus();
    $($referency).keyup(delay(function (e) {
        e.preventDefault();
        let referencyVal = $(e.currentTarget).val();
        if (referencyVal.length >= 6) {
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
                })
                .always(function () {
                    $referency.attr('disabled', false);
                    $('#venda_btn_add').attr('disabled', false);
                });
        }
    }, 500));

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

let venda = {
    subtotal: 0,
    discount: 0,
    total: 0,
    client: '',
    paymentMethod: '',
    orderItems: []
};

let vendaItemsCount = 1;

function vendaAddItem() {
    let vendaItem = {
        referency: '',
        quantity: 1,
        subtotal: 0,
        // discount: 0,
        total: 0,
        price: 0
    };

    const referency = $('#venda_referencia').val();
    const quantity = Number($('#venda_quantidade').val());
    const price = Number($('#venda_valor').val().toString().replace(',', '.'));
    // const discount = Number($('#venda_desconto').val().toString().replace(',', '.'));

    if (!$('#venda_referencia').val() || !$('#venda_quantidade').val() || !$('#venda_valor').val()) {
        return;
    }

    vendaItem.quantity = quantity;
    vendaItem.referency = referency;
    vendaItem.price = price;
    vendaItem.subtotal = price * quantity;
    vendaItem.total = vendaItem.subtotal;
    vendaItem.identity = vendaItemsCount;

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
    venda.paymentMethod = $('#venda_forma_pagamento').val();
    venda.subtotal = venda.subtotal.toString();
    venda.discount = venda.discount.toString();
    venda.total = venda.total.toString();
    venda.orderItems.forEach(el => {
        el.price = el.price.toString();
        el.subtotal = el.subtotal.toString();
        el.total = el.total.toString();
    });

    $.post(RoutingManager.generate('admin_order_new'), JSON.stringify(venda))
        .done((res) => {
            alert('Venda realizada com sucesso.');

            $('#vendaTableBody').html('');
            $('#venda_referencia').val('');
            $('#venda_quantidade').val(1);
            $('#venda_valor').val('');
            $('#venda_marca_referencia').val('');
            $('#venda_desconto').val('R$ 0,00');
            $('#venda_forma_pagamento').prop("selectedIndex", 0).val();
            $('#venda_cliente').val('');
            $('#venda_exibe_subtotal').html('R$ 0,00');
            $('#venda_exibe_total').html('R$ 0,00');
            $('#venda_exibe_desconto').html('R$ 0,00');
            $('#venda_referencia').focus();

            venda = {
                subtotal: 0,
                discount: 0,
                total: 0,
                client: '',
                orderItems: []
            };
        })
        .fail((err) => {
            if(err.responseJSON && err.responseJSON.message !== 'Error'){
                alert(err.responseJSON.message);
                return;
            }
            alert('Erro ao realizar a venda');
        })
        .always(function () {
            $('#venda_btn_finaliza').attr('disabled', false);
        });
}

function formatCurrency(value) {
    return value.toLocaleString('pt-BR', {style: 'currency', currency: 'BRL'});
}