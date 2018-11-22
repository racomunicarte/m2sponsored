/**
 * Webkul_Auction Category View Js
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
    $.widget('auction.categoryview', {
        _create: function () {
            var viewCategoryOpt = this.options;
            var days    = 24*60*60,
            hours   = 60*60,
            minutes = 60;
            $.fn.countdown = function (prop) {
                var options = $.extend({
                    callback    : function () {
                        alert("");
                    },
                    timestamp   : 0
                }, prop);
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
            $('.auction, .buy-it-now').each(function () {
                if ($(this).hasClass('auction')) {
                    var colckElm = $(this).find('.wk_cat_count_clock'),
                        timeStamp = new Date(2012, 0, 1),
                        stopTime = colckElm.attr('data-stoptime'),
                        newYear = true;
                    var heighestamount = colckElm.attr('data-highest-bid');
                    var heighestbidamount = colckElm.attr('data-highest-bid-amount');
                    var openbidamount = colckElm.attr('data-open-bid-amount');
                    var link = colckElm.parent().parent().find('a.product-item-link').attr('href');
                    if ((new Date()) > timeStamp) {
                        timeStamp = colckElm.attr('data-diff_timestamp')*1000;
                        timeStamp = (new Date()).getTime() + timeStamp;
                        newYear = false;
                    }
                    if (colckElm.length) {
                        colckElm.countdown({
                            timestamp : timeStamp,
                            callback : function (days, hours, minutes, seconds) {
                                var message = "",
                                    timez = "",
                                    distr = stopTime.split(' '),
                                    tzones =  distr[0].split('-'),
                                    months = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
                                if (hours < 10) {
                                    hours = "0"+hours;
                                }
                                if (minutes < 10) {
                                    minutes = "0"+minutes;
                                }
                                if (seconds < 10) {
                                    seconds = "0"+seconds;
                                }
                                if (heighestbidamount != 0) {
                                    message += "<span class='heighest_bid_amount' style='color:#1979c3;'>"+$t("Highest Bid :")+"<strong>"+heighestamount+"</strong></span><br>";
                                } else {
                                    message += "<a href='"+link+"'><span class='heighest_bid_amount' style='color:#1979c3;'>"+$t("Open Bid : ")+"<strong>"+openbidamount+"</strong></span></a><br>";
                                }
                                message += "<span class='wk_set_time' title='Days'>"+$t("Bid Ends In: ")+days +"</span>D, ";
                                message += "<span class='wk_set_time' title='Hours'>"+hours+"</span>H : ";
                                message += "<span class='wk_set_time' title='Minutes'>"+minutes+"</span>M : ";
                                message += "<span class='wk_set_time' title='Seconds'>"+seconds+"</span>S";
                                // message += "("+ tzones[2]+' '+months[tzones[1]-1]+', '+tzones[0]+' '+ timez +")";
                                colckElm.html(message);
                                if (hours == '00' && minutes == '00' && seconds == '00') {
                                    colckElm.remove();
                                }
                            }
                        });
                    }
                }
                var thisParent = $(this).parent('.product-item-details');
                if ($(this).hasClass('buy-it-now') && $(this).attr('data-winner') != 0) {
                    thisParent.find('.tocart.primary span').text(viewCategoryOpt.buyItNow);
                } else if ($(this).attr('data-winner') == 0) {
                    var cost = $(this).attr('data-winning-amt');
                    thisParent.find('.tocart.primary span').text('Buy with '+cost);
                } else if ($(this).attr('data-winner') == 1) {
                    var cost = $(this).attr('data-winning-amt');
                    thisParent.find('.actions-primary').html('<span>You have already bought this product on '+cost +'</span>');
                } else {
                    var proLink = thisParent.find('.product-item-link').attr('href');
                    var viewDetail = $('<a />').attr('title', viewCategoryOpt.viewDetail)
                                                .attr('href', proLink)
                                                .addClass('action primary auto-bid-show')
                                                .append($('<span />').text(viewCategoryOpt.viewDetail));

                    thisParent.find('.actions-primary').html(viewDetail);
                }
            });

            $('.action.tocart').on('click', function () {
                var thiscart = $(this);
                if ($(this).parents('.product-item-info').find('.buy-it-now').length) {
                    setTimeout(function () {
                        thiscart.text(viewCategoryOpt.buyItNow);
                    }, 2000);
                } else {
                    if ($(this).parents('.product-item-info').find('.auction').length && $(this).parents('.product-item-info').find('.auction').attr('data-winner') == 0) {
                        var cost = $(this).parents('.product-item-info').find('.auction').attr('data-winning-amt');
                        setTimeout(function () {
                            thiscart.text('Buy with '+cost);
                        }, 2000);
                    }
                }
            });
        }
    });
    return $.auction.categoryview;
});