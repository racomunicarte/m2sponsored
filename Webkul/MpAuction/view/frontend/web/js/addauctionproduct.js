/**
 * Webkul_MpAuction Add Deal On Product Js
 * @category  Webkul
 * @package   Webkul_MpAuction
 * @author    Webkul
 * @copyright Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
define([
    "jquery",
    "mage/translate",
    "jquery/ui",
    "mage/calendar"
], function ($, $t) {
    "use strict";
    $.widget('auction.addauction', {
        options: {
            autoAuctionOpt: $('#auto_auction_type'),
            reservePriceElm: $('#reserve_price'),
            reservePriceParentElm: $('#reserve_price').parents('.field')
        },
        _create: function () {
            var countKey = this.options.count_key;
            console.log(countKey);
            var countKey = this.options.count_key;
            var configTz = this.options.config_time_zone;
            var startoffset = this.options.startOffset;
            var stopoffset = this.options.stopOffset;
            console.log(this.options);
            var zone = Intl.DateTimeFormat().resolvedOptions().timeZone;
            if ($('#stop_auction_time').val()) {
                var stopTime = converttoTz($('#stop_auction_time').val(), stopoffset);
                var startTime = converttoTz($('#start_auction_time').val(), startoffset);
                $('#stop_auction_time').val(stopTime);
                $('#start_auction_time').val(startTime);
            }
            function converttoTz(time, offset)
            {
                var currentoffset = new Date().getTimezoneOffset() * -1 * 60000;
                var date = new Date(new Date(time).getTime() + currentoffset + (-1000 * parseInt(offset)));
                var d,m,h,i,s;
                d = date.getDate();
                m = date.getMonth();
                h = date.getHours();
                i = date.getMinutes();
                s = date.getSeconds();
                if (parseInt(d) < 10) {
                        d = "0"+d;
                }
                if ((parseInt(m)+1) < 10) {
                        m = "0"+(parseInt(m)+1);
                }
                if (parseInt(h) < 10) {
                    h = "0"+h;
                }
                if (parseInt(i) < 10) {
                    i = "0"+i;
                }
                if (parseInt(s) < 10) {
                    s = "0"+s;
                }
                return m+'/'+d+'/'+date.getFullYear()+' '+h+':'+i+':'+s;
            }
            $("#seller_time_zone").val(zone);
            $("#start_auction_time").datetimepicker({
                'dateFormat':'mm/dd/yy',
                'timeFormat':'HH:mm:ss',
                'minDate': new Date(),
                onClose: function ( selectedDate ) {
                    $("#stop_auction_time").datetimepicker(
                        'option',
                        'minDate',
                        $('#start_auction_time').datetimepicker("getDate")
                    );
                }
            });

            var reservePriceElm = this.options.reservePriceElm,
                reservePriceParentElm = this.options.reservePriceParentElm;
            // if (this.options.autoAuctionOpt.val()==1) {
            //     reservePriceElm.addClass('required-entry');
            //     reservePriceParentElm.addClass('_required');
            // }
            // this.options.autoAuctionOpt.on('change', function () {
            //     if ($(this).val()==1) {
            //         reservePriceElm.addClass('required-entry');
            //         reservePriceParentElm.addClass('_required');
            //     } else {
            //         reservePriceElm.removeClass('required-entry');
            //         reservePriceParentElm.removeClass('_required');
            //     }
            // });
            
            $("#stop_auction_time").datetimepicker({
                'dateFormat':'mm/dd/yy',
                'timeFormat':'HH:mm:ss',
                'minDate': new Date(),
            });
            
            $('#inc-price-contener').on('click', '#button_addrow', function () {
                countKey++;
                $('#inc-price-contener .wk-mp-body').append($('#increment_row').clone().html());
                $('#inc-price-contener .wk-mp-body tr:last input').each(function () {
                    var name = $(this).attr('name')+'['+countKey+']';
                    $(this).attr('name', name);
                });
            });

            $('.wk-show-incremental-rule-btn').click(function () {
                $('.wk-seller-auction-incremental-rule').toggleClass('show');
                $('.wk-seller-auction-incremental-rule').slideToggle();
                if ($('.wk-seller-auction-incremental-rule').hasClass('show')) {
                    $(this).text($t("Hide Admin Increment Bid Rule"));
                } else {
                    $(this).text($t("Show Admin Increment Bid Rule"));
                }
            });

            $('.wk-mp-body').on('click', '.delete-option', function () {
                $(this).parents('tr').remove();
            });

            $('#max_qty').on('change', function () {
                if (parseInt($(this).val(), 10) < parseInt($('#min_qty').val(), 10)) {
                    alert($t('Enter max quantity equal or greater than min quantity'));
                    $(this).val('');
                }
            });

            $('#reserve_price').on('change', function () {
                if (parseInt($(this).val(), 10) < parseInt($('#starting_price').val(), 10)) {
                    alert($t('Enter reserve price greater than starting price.'));
                    $(this).val('');
                }
            });

            $('#increment_opt').on('change', function () {
                if ($(this).val()==1) {
                    $('#inc-price-contener, #admin-inc-price-contener, .label.table, .show-incremental-rule').addClass('show');
                } else {
                    $('#inc-price-contener, #admin-inc-price-contener, .label.table, .show-incremental-rule').removeClass('show');
                }
            });
        }
    });
    return $.auction.addauction;
});