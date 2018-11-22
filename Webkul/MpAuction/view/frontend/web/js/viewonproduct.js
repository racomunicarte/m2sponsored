/**
 * Webkul_Auction Product View Js
 * @category  Webkul
 * @package   Webkul_Auction
 * @author    Webkul
 * @copyright Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

define([
    "jquery",
    "mage/translate",
    "jquery/ui"
], function ($, $t) {
    "use strict";
    $.widget('auction.productview', {
        _create: function () {
            $('.wk-auction-view-bid-link').click(function () {
                $('#tab-label-mp-bid-details-title').trigger('click');
            });
            $('#bidding_amount').keypress(function (e) {
                var key, keychar;
                if (window.event) {
                    key = window.event.keyCode;
                } else if (e) {
                    key = e.which;
                } else {
                    return true;
                }
                keychar = String.fromCharCode(key);
                if ((key==null) || (key==0) || (key==8) ||
                    (key==9) || (key==13) || (key==27) || (key==46)) {
                    return true;
                } else if ((("0123456789").indexOf(keychar) > -1)) {
                    return true;
                } else {
                    return false;
                }
            });
            var viewProductOpt = this.options,
            days    = 24*60*60,
            hours   = 60*60,
            minutes = 60;
            $.fn.countdown = function (prop) {
                var options = $.extend({
                    callback    : function () {
                        alert("");
                    },
                    timestamp   : 0
                },prop);
                var left, d, h, m, s, positions;
                positions = this.find('.position');
                var initialize =  setInterval(function () {
                    left = Math.floor((options.timestamp - (new Date())) / 1000);
                    if (left < 0) {
                        left = 0;
                    }
                    d = Math.floor(left / days);
                    left -= d*days;
                    h = Math.floor(left / hours);
                    left -= h*hours;
                    m = Math.floor(left / minutes);
                    left -= m*minutes;
                    s = left;
                    options.callback(d, h, m, s);
                    if (d==0 && h==0 && m==0 && s==0) {
                        clearInterval(initialize);
                    }
                }, 1000);
                return this;
            };

            var clockOnPrice = viewProductOpt.auctionData.pro_auction,
                buyIdNow = viewProductOpt.auctionData.pro_buy_it_now,
                allowforbuy = $('#winner-data-container')
                                .hasClass('allow-for-buy');
            if (buyIdNow == 1 && !allowforbuy) {
                $('#product-addtocart-button span').text(viewProductOpt.buyItNow);
            } else if (allowforbuy) {
                var bidWinnerCart = $('#winner-data-container').attr('data-cart-label');
                $('#product-addtocart-button span').text(bidWinnerCart);
            } else if (viewProductOpt.auctionType!="") {
                $('.product-add-form').remove();
            }
            $('.product-add-form').show();
            $('#product-addtocart-button').on('click', function () {
                setTimeout(function() {
                    if (buyIdNow == 1 && !allowforbuy) {
                        $('#product-addtocart-button span').text(viewProductOpt.buyItNow);
                    } else if (allowforbuy) {
                        var bidWinnerCart = $('#winner-data-container').attr('data-cart-label');
                        $('#product-addtocart-button span').text(bidWinnerCart);
                    } else if (viewProductOpt.auctionType!="") {
                        $('.product-add-form').remove();
                    }
                    $('.product-add-form').show();
                }, 7000);
            });
            // if (clockOnPrice == 1) {
            //     $('.product-info-price').after($('<p />',{'class':'wk-auction-clock'}));
            //     clockOnPrice = $('.wk-auction-clock');
            // }
            var note =  $('.wk_front_dd_countdownnew'),
                ts = new Date(2012, 0, 1),
                newYear = true;
            if (note.length) {
                if ((new Date()) > ts) {
                    var t = note.attr('data-diff-timestamp')*1000;
                    ts = (new Date()).getTime() + t;
                    newYear = false;
                }
                note.countdown({
                    timestamp : ts,
                    callback : function (days, hours, minutes, seconds) {
                        var message = "",
                            stopt = viewProductOpt.auctionData.stop_auction_time,
                            timez = "",
                            distr = stopt.split(' '),
                            tzones =  distr[0].split('-'),
                            months = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
                        if (days < 10) {
                            days = "0"+days;
                        }
                        if (hours < 10) {
                            hours = "0"+hours;
                        }
                        if (minutes < 10) {
                            minutes = "0"+minutes;
                        }
                        if (seconds < 10) {
                            seconds = "0"+seconds;
                        }
                        message += '<span class="wk_front_dd_set_time wk-auction-clock-span" id="wk-auction-dd" title="Days">'+days+' ,<span class="label wk-auction-clock-label-dd" for="wk-auction-dd"><span>'+$t("Days")+'</span></span></span>';
                        message += '<span class="wk_front_dd_set_time wk-auction-clock-span" id="wk-auction-hr" title="Hours"> '+hours+' :<span class="label wk-auction-clock-label-hr" for="wk-auction-hr"><span>'+$t("Hours")+'</span></span></span>';
                        message += '<span class="wk_front_dd_set_time wk-auction-clock-span" id="wk-auction-mi" title="Minutes"> '+minutes+' :<span class="label wk-auction-clock-label-mi" for="wk-auction-mi"><span>'+$t("Minutes")+'</span></span></span>';
                        message += '<span class="wk_front_dd_set_time wk-auction-clock-span" id="wk-auction-sec" title="Seconds"> '+seconds+' <span class="label wk-auction-clock-label-sec" for="wk-auction-sec"><span>'+$t("Seconds")+'</span></span></span>';
                        // message += "("+ tzones[2]+' '+months[tzones[1]-1]+', '+tzones[0]+' '+ timez +")";
                        note.html(message);
                        // if (clockOnPrice != 0) {
                        //     clockOnPrice.html(message);
                        // }
                        if (hours == '00' && minutes == '00' && seconds == '00') {
                            location.reload();
                        }
                    }
                });
            }
            $('.reviews-actions .action').on('click', function () {
                $('.wk-auction-bids-record .product').removeClass('items');
                setTimeout(function () {
                    $('.wk-auction-bids-record .product').addClass('items');
                }, 1000);
            });
        }
    });
    return $.auction.productview;
});