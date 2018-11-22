
/**
 * Webkul_MpAuction autoauc.event
 * @category  Webkul
 * @package   Webkul_MpAuction
 * @author    Webkul
 * @copyright Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
 
 /*jshint jquery:true*/
define([
    "jquery",
    "jquery/ui"
], function ($) {
    "use strict";
    $.widget('autoauc.event', {
        options: {
            autoAuctionOpt: $('#wkauction_auto_auction_opt'),
            reservePriceElm: $('#wkauction_reserve_price'),
            reservePriceParentElm: $('#wkauction_reserve_price').parents('.field-reserve_price')
        },
        _create: function () {
            // var reservePriceElm = this.options.reservePriceElm,
            //     reservePriceParentElm = this.options.reservePriceParentElm;
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
        }
    });
    return $.autoauc.event;
});