$(document).ready(function () {
    customerObservationInit();
});

function customerObservationInit() {

    var $collectionHolder;

    var $addCustomerObservationLink = $('#btn_add_customerObservation');
    var $newLinkPanel = $('#panel_add_customerObservation');

    $collectionHolder = $('div#customerObservation');

    $collectionHolder.find('div.m-portlet__body').each(function () {
        addCustomerObservationFormDeleteLink($(this));
    });

    var index = $collectionHolder.find('div.m-portlet').length;

    $collectionHolder.data('index', index);

    $addCustomerObservationLink.on('click', function (e) {
        e.preventDefault();
        addCustomerObservationForm($collectionHolder, $newLinkPanel);
    });
}

function addCustomerObservationForm($collectionHolder, $newLinkPanel) {

    var prototype = $collectionHolder.data('prototype');
    var index = $collectionHolder.data('index');

    var newForm = prototype.replace(/__name__/g, index);
    var new_index = index + 1;

    $collectionHolder.data('index', new_index);

    var $newFormPanelBody = $('<div class="m-portlet__body"></div>').append(newForm);
    var $newFormPanel = $('<div class="m-portlet m-portlet--mobile"></div>').append($newFormPanelBody);
    $newLinkPanel.before($newFormPanel);

    addCustomerObservationFormDeleteLink($newFormPanelBody, $newFormPanel);

    return new_index;
}

function addCustomerObservationFormDeleteLink($newFormPanelBody, $newFormPanel) {
    var $removeForm = $('<div class="row"><div class="col-md-3"><div class="form-group"><label class="form-control-label">Ações</label><br><div class="btn btn-sm btn-danger m-btn m-btn--icon m-btn--air" style="cursor: pointer"><span><i class="la la-trash-o"></i><span>Remover</span></span></div></div></div></div>');
    $newFormPanelBody.append($removeForm);

    if ($newFormPanel == null) {
        $newFormPanel = $newFormPanelBody.parent('.m-portlet');
    }

    var $collectionHolder = $('div#customerObservation');

    $removeForm.on('click', function (e) {
        e.preventDefault();
        $newFormPanel.remove();

        var index = $collectionHolder.find('div.m-portlet').length;

        $collectionHolder.removeData('index');
        $collectionHolder.data('index', index);
    });
}
