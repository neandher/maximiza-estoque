$(document).ready(function () {
    const messageExist = document.createElement('span');
    messageExist.id = "message_exist";
    $("label[for='bill_referency']").append(messageExist);

    $("#bill_referency").keyup((event) => {

        const referencyCode = $(event.currentTarget).val();
        if (referencyCode.length >= 6) {
            $.get(RoutingManager.generate('admin_stock_verify_referency'), {referency: referencyCode})
                .done((stock) => {
                    $("#bill_quantity").val('1');
                    $("#bill_amount").val(stock.unitPrice.toString().replace('.', ','));
                    $("#bill_amountPaid").val(stock.unitPrice.toString().replace('.', ','));

                    const d = new Date();
                    const ye = new Intl.DateTimeFormat('pt', { year: 'numeric' }).format(d);
                    const mo = new Intl.DateTimeFormat('pt', { month: '2-digit' }).format(d);
                    const da = new Intl.DateTimeFormat('pt', { day: '2-digit' }).format(d);

                    $("#bill_dueDate").val(`${da}/${mo}/${ye}`);
                    $("#bill_paymentDate").val(`${da}/${mo}/${ye}`);

                    $("#message_exist").html('');
                })
                .fail(() => {
                    $("#message_exist").html(` <span class="m-badge m-badge--warning m-badge--wide">Referência não encontrada</span>`);
                    $("#bill_amount").val('');
                    $("#bill_amountPaid").val('');
                    $("#bill_dueDate").val('');
                    $("#bill_paymentDate").val('');
                    $("#bill_quantity").val('');
                });
        }
    });
});
