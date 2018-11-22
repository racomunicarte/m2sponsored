
/**
 * Webkul_MpAuction mpauction.settype
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
    $.widget('mpauction.settype', {
        _create: function () {
            var opts = this.options.auctiontype.split(',');
            setTimeout(function() {
                $('select[name="product[auction_type]').val(opts);
            }, 3000);
        }
    });
    return $.mpauction.settype;
});