require([
    "jquery",
    "mage/mage"
], function ($) {
    $('#save').click(function () {
        setTimeout(function () {
            if (!$(".admin__field-control [id$='-error']").length || $(".admin__field-control [id$='-error'][style='display: none;']").length == $(".admin__field-control [id$='-error']").length) {
                $('#save').attr('disabled', 'disabled');
            }
        }, 1);
    })
});