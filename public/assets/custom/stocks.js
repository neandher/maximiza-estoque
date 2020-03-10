$(document).ready(function () {
    stockInit();
});

function stockInit() {

    var $collectionHolder;

    var $addStockLink = $('#btn_add_stock');
    var $newLinkPanel = $('#panel_add_stock');

    $collectionHolder = $('div#stock');

    $collectionHolder.find('div.m-portlet__body').each(function () {
        addStockFormDeleteLink($(this));
    });

    var index = $collectionHolder.find('div.m-portlet').length;

    $collectionHolder.data('index', index);

    if (index == 0) {
        addStockForm($collectionHolder, $newLinkPanel);
    }

    $addStockLink.on('click', function (e) {
        e.preventDefault();
        addStockForm($collectionHolder, $newLinkPanel);
    });
}

function addStockForm($collectionHolder, $newLinkPanel) {

    var prototype = $collectionHolder.data('prototype');
    var index = $collectionHolder.data('index');

    var newForm = prototype.replace(/__name__/g, index);
    var new_index = index + 1;

    $collectionHolder.data('index', new_index);

    var $newFormPanelBody = $('<div class="m-portlet__body"></div>').append(newForm);
    var $newFormPanel = $('<div class="m-portlet m-portlet--rounded"></div>').append($newFormPanelBody);
    $newLinkPanel.before($newFormPanel);

    var messageExist = document.createElement('span');
    messageExist.id = "message_exist_" + index;
    $("label[for='stock_multiple_stocks_" + index + "_referency']").append(messageExist);

    $("#stock_multiple_stocks_" + index + "_quantity").val('1');
    $("#stock_multiple_stocks_" + index + "_referency").focus();

    $("#stock_multiple_stocks_" + index + "_referency").keyup((event) => {

        referencyCode = $(event.currentTarget).val();
        if (referencyCode.length >= 6) {
            $.get(RoutingManager.generate('admin_stock_verify_referency'), {referency: referencyCode})
                .done((stock) => {
                    $("#stock_multiple_stocks_" + index + "_unitPrice").val(stock.unitPrice.toString().replace('.', ','));
                    $("#message_exist_" + index).html(` <span class="m-badge m-badge--info m-badge--wide">Referência encontrada</span>`);
                })
                .fail(() => {
                    $("#message_exist_" + index).html(` <span class="m-badge m-badge--warning m-badge--wide">Referência não encontrada</span>`);
                    $("#stock_multiple_stocks_" + index + "_unitPrice").val('');
                });
        }
    });

    addStockFormDeleteLink($newFormPanelBody, $newFormPanel);

    return new_index;
}

function addStockFormDeleteLink($newFormPanelBody, $newFormPanel) {
    var $removeForm = $('<div class="row"><div class="col-md-3"><div class="form-group"><label class="form-control-label">Ações</label><br><div class="btn btn-sm btn-danger m-btn m-btn--icon m-btn--air" style="cursor: pointer"><span><i class="la la-trash-o"></i><span>Deletar</span></span></div></div></div></div>');
    $newFormPanelBody.append($removeForm);

    if ($newFormPanel == null) {
        $newFormPanel = $newFormPanelBody.parent('.m-portlet');
    }

    var $collectionHolder = $('div#stock');

    $removeForm.on('click', function (e) {
        e.preventDefault();
        $newFormPanel.remove();

        var index = $collectionHolder.find('div.m-portlet').length;

        $collectionHolder.removeData('index');
        $collectionHolder.data('index', index);
    });
}
