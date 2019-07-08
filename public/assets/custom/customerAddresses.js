$(document).ready(function () {
    customerAddressInit();
});

function customerAddressInit() {

    var $collectionHolder;

    var $addCustomerAddressLink = $('#btn_add_customerAddress');
    var $newLinkPanel = $('#panel_add_customerAddress');

    $collectionHolder = $('div#customerAddress');

    $collectionHolder.find('div.m-portlet__body').each(function () {
        addCustomerAddressFormDeleteLink($(this));
    });

    var index = $collectionHolder.find('div.m-portlet').length;

    $collectionHolder.data('index', index);

    if (index === 0) {
        addCustomerAddressForm($collectionHolder, $newLinkPanel);
    }

    $addCustomerAddressLink.on('click', function (e) {
        e.preventDefault();
        addCustomerAddressForm($collectionHolder, $newLinkPanel);
    });
}

function addCustomerAddressForm($collectionHolder, $newLinkPanel) {

    var prototype = $collectionHolder.data('prototype');
    var index = $collectionHolder.data('index');

    var newForm = prototype.replace(/__name__/g, index);
    var new_index = index + 1;

    $collectionHolder.data('index', new_index);

    var $newFormPanelBody = $('<div class="m-portlet__body"></div>').append(newForm);
    var $newFormPanel = $('<div class="m-portlet m-portlet--mobile"></div>').append($newFormPanelBody);
    $newLinkPanel.before($newFormPanel);

    Inputmask.init();

    addCustomerAddressFormDeleteLink($newFormPanelBody, $newFormPanel);

    return new_index;
}

function addCustomerAddressFormDeleteLink($newFormPanelBody, $newFormPanel) {
    var $removeForm = $('<div class="row"><div class="col-md-3"><div class="form-group"><label class="form-control-label">Ações</label><br><div class="btn btn-sm btn-danger m-btn m-btn--icon m-btn--air" style="cursor: pointer"><span><i class="la la-trash-o"></i><span>Remover</span></span></div></div></div></div>');
    $newFormPanelBody.append($removeForm);

    if ($newFormPanel == null) {
        $newFormPanel = $newFormPanelBody.parent('.m-portlet');
    }

    var $collectionHolder = $('div#customerAddress');

    $removeForm.on('click', function (e) {
        e.preventDefault();
        $newFormPanel.remove();

        var index = $collectionHolder.find('div.m-portlet').length;

        $collectionHolder.removeData('index');
        $collectionHolder.data('index', index);
    });
}
