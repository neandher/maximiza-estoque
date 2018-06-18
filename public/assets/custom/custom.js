$(document).ready(function () {
    showTabError();
});

$('#modalConfirmation').on('show.bs.modal', function (event) {
    var button = $(event.relatedTarget);
    var path = button.data('path');
    var crf = button.data('crf');
    var delBtn = $('.delete-resource-form');

    delBtn.attr({
        'data-path': path,
        'data-crf': crf
    });
});

$('.delete-resource-form').on('click', function () {

    var $form = $(document.createElement('form'));

    $form.attr({
        'action': $(this).data('path'),
        'method': 'POST'
    });

    $form.append($('<input/>', {
        type: 'hidden',
        name: '_method'
    }).val('DELETE'));

    $form.append($(this).data('crf'));

    $(this).parent().append($form);

    $form.submit();

    return false;
});

function showTabError() {

    var $tab_content = $(".tab-content");

    $tab_content.find("div.tab-pane:has(span.invalid-feedback)").each(function (index, tab) {

        var id = $(tab).attr("id");
        $('a[href="#' + id + '"]').tab('show');

        $(tab).find('span.invalid-feedback').each(function (_index, _field) {
            $('html, body').animate({
                scrollTop: $(_field).offset().top
            }, 2000);
            return false;
        });

        return false;
    });
}