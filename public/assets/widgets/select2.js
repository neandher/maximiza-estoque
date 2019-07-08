var Select2 = {
    init: function () {
        $(".m-select2").select2({
            placeholder: "Select"
        });
    }
};
jQuery(document).ready(function () {
    Select2.init()
});