/**
 * Webkul_MpAuction Auction list Js
 * @category  Webkul
 * @package   Webkul_MpAuction
 * @author    Webkul
 * @copyright Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
define([
    "jquery",
    "jquery/ui"
], function ($) {
    "use strict";
    $.widget('mpauction.prolist', {
        _create: function () {
            var configTz = this.options.zone;
            var timeoffset = this.options.timeOffset;
            var zone = Intl.DateTimeFormat().resolvedOptions().timeZone;
            $('#mpauctionselecctall').change(function () {
                if ($(this).is(":checked")) {
                    $('.wk-row-view  .mpcheckbox').each(function () {
                        $(this).prop('checked', true);
                    });
                } else {
                    $('.wk-row-view  .mpcheckbox').each(function () {
                        $(this).prop('checked', false);
                    });
                }
            });
            $('#form-productlist-massdisable').submit(function () {
                if ($('.wk-mp-list-table .mpcheckbox:checked').length == 0) {
                    alert('Select auction which you want to cancel');
                    return false;
                } else {
                    if (!confirm('Selected auctions will cancel.')) {
                        return false;
                    }
                }
                
            });
            $('.mpcheckbox').change(function () {
                if ($(this).is(":checked")) {
                    var totalCheck = $('.wk-row-view  .mpcheckbox').length,
                        totalCkecked = $('.wk-row-view  .mpcheckbox:checked').length;
                    if (totalCheck == totalCkecked) {
                        $('#mpauctionselecctall').prop('checked', true);
                    }
                } else {
                    $('#mpauctionselecctall').prop('checked', false);
                }
            });
            $.each(timeoffset, function (key, data) {
                var stopTime = converttoTz($('#stop_auction_time'+key).text(), data.stop_auction_time);
                var startTime = converttoTz($('#start_auction_time'+key).text(), data.start_auction_time);
                $('#stop_auction_time'+key).text('');
                $('#start_auction_time'+key).text('');
                $('#stop_auction_time'+key).text(stopTime);
                $('#start_auction_time'+key).text(startTime);
            });
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
                if (parseInt(m) < 10) {
                        m = "0"+m;
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
                return (parseInt(m)+1)+'/'+d+'/'+date.getFullYear()+' '+h+':'+i+':'+s;
            }
        }
    });
    return $.mpauction.prolist;
});