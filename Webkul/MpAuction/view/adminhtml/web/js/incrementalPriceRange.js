/**
 * Webkul_MpAuction Incremental Price js
 * @category  Webkul
 * @package   Webkul_MpAuction
 * @author    Webkul
 * @copyright Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
 
 /*jshint jquery:true*/
define([
    "jquery",
    "Magento_Ui/js/modal/modal",
    "jquery/ui"
], function ($, modal) {
    "use strict";
    $.widget('console.js', {
        _create: function () {
            var saveIncPrice = this.options.saveurl;
            $('#html-body').mouseover(function () {
                $('.delete-option').on('click',function () {
                    $(this).parents('tr').remove();
                });
            });
            var options = {
                        type: 'popup',
                        title: $.mage.__('Set Incremental Price Range'),
                        autoOpen: true,
                        buttons: [{
                            text:'Add Option',
                            attr:{
                                'data-action':'na'
                            },
                            'class': 'action-primary',
                            click: function () {
                                $('#incremental_price_container')
                                    .append($('#increment_row').clone().html());
                                $('.delete-option').on('click',function () {
                                    $(this).parents('tr').remove();
                                });
                            }
                        },{
                            text: 'Save',
                            attr: {
                                'data-action': 'cancel',
                                'id':'save-auc-data'
                            },
                            'class': 'action-primary',
                            click: function () {
                                var priceContainerInput = $('#incremental_price_container input');
                                priceContainerInput.removeClass('mage-error');
                                priceContainerInput.each(function () {
                                    if (!$(this).val()) {
                                        $(this).addClass('mage-error');
                                    }
                                });
                                if (priceContainerInput.hasClass('mage-error')) {
                                    return false;
                                }
                                var loader = $('#wk-loader').clone().html();
                                var modelFooter = $('#save-auc-data').parent('.modal-footer');
                                modelFooter.find('img').remove();
                                modelFooter.prepend(loader);
                                $.ajax({
                                    url: saveIncPrice,
                                    data: $('#price_range_info').serialize(),
                                    type: 'POST',
                                    dataType:'html',
                                    success: function (transport) {
                                        var response = $.parseJSON(transport);
                                        modelFooter.find('img').remove();
                                        if (response.msg) {
                                            $('<div />').html(response.msg)
                                                .modal({
                                                    title: $.mage.__('Attention'),
                                                    autoOpen: true,
                                                    buttons: [{
                                                        text: 'OK',
                                                        attr: {
                                                            'data-action': 'cancel',
                                                            'id':'auc-data-sussess-ok'
                                                        },
                                                        'class': 'action-primary',
                                                        click: function () {
                                                            $('#auc-data-sussess-ok')
                                                                .parents('.modal-inner-wrap')
                                                                .find('.action-close')
                                                                .trigger("click");
                                                            location.reload();
                                                        }
                                                    }]
                                                });
                                        }
                                    }
                                });
                            }
                        }]
                    }; 
            $("#"+this.options.blockId).on("click", function () {
                $('.modal-popup').remove();
                var cont = $('<div />').html($('#datatemo').html());
                modal(options, cont);
                cont.modal('openModal');
            });
        }
    });
    return $.console.js;
});