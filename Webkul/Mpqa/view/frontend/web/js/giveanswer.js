/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_Mpqa
 * @author    Webkul
 * @copyright Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

define([
    "jquery",
    'mage/mage'
], function ($,mage) {
    'use strict';
    $.widget('mage.giveanswer', {
        
        _create: function () {
            var self = this;
// ------save answer
            $('button.button.wk-mp-btn.btn').on('click',function () {
                var ans=$("#maincont").val();
                var qid=self.options.question_id;
                if (ans!='') {
                    $("body").append(jQuery("<div/>").addClass("filterurl_loader").append(jQuery("<div/>")));
                    $.ajax({
                        url:self.options.submitanswer_url,
                        data:{ans:ans,qid:qid,cid:self.options.customer_id},
                        type:'post',
                        dataType:'json',
                        success:function (data) {
                            if (data.status) {
                                $('#'+qid).after("<div class='respond newres' id=''><div><label class='rlabl'>"+data.respond_type+":</label></div><div class='conten'><span class='wk_prewrap'>"+ans+"</span></div><div class='dt'>"+data.time+"</div></div> ");
                                $('#maincont').val('');
                            }
                            $("body").find('.filterurl_loader').remove();
                        }
                    })
                }
            });
        },
        
    });
    return $.mage.giveanswer;
});