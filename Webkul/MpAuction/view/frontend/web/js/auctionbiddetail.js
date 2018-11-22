/**
 * Webkul_MpAuction Product View Js
 * @category  Webkul
 * @package   Webkul_MpAuction
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
    $.widget('auction.auctiondetail', {
        _create: function () {
            $(document).ready(function () {
                var token = true;
                if ((window.location.href).indexOf("#mp-bid-details") >= 0) {
                    setTimeout(function () {
                        $('#tab-label-normal-bid-record').addClass('active');
                        $('#normal-bid-record').css('display','block');
                    }, 1);
                }
                $('#tab-label-mp-bid-details-title').click(function () {
                    if (token) {
                        setTimeout(function () {
                            $('#tab-label-normal-bid-record').addClass('active');
                            $('#normal-bid-record').css('display','block');
                        }, 1);
                    }
                });
                $('#tab-label-normal-bid-record-title').click(function () {
                    token = false;
                    $('#tab-label-mp-bid-details-title').trigger('click');
                    setTimeout(function () {
                        $('#tab-label-mp-bid-details').addClass('active');
                        $('#mp-bid-details').css('display','block');
                        token = true;
                    }, 1);
                });
                $('#tab-label-automatic-bid-record-title').click(function () {
                    token = false;
                    $('#tab-label-mp-bid-details-title').trigger('click');
                    setTimeout(function () {
                        $('#tab-label-mp-bid-details').addClass('active');
                        $('#mp-bid-details').css('display','block');
                        token = true;
                    }, 1);
                });
            })
        }
    });
    return $.auction.auctiondetail;
});
