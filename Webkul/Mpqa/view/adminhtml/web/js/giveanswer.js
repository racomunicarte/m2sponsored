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
/** ------save answer */
            var qid=self.options.question_id;
            $('button.button.wk-mp-btn.btn').on('click',function () {
                var content=$('#maincont').val();
                if (content!='') {
                    $.ajax({
                        url: self.options.submitanswer_url,
                        data:{  pid:qid,content:content},
                        type:'post',
                        dataType:   "json",
                        showLoader: true,
                        success:function (data) { 
                             $('#'+qid).after("<div class='respond newres' id=''> <div class='marrem'><button class='delet wk-button' id='"+data.answer_id+"'><span class='delet'>X</span></button></div>   <div><label class='rlabl'>"+data.nickname+"("+data.respond_type+"):</label></div><div class='conten'><span class='wk_prewrap'>"+data.content+"</span></div><div class='dte'>"+data.time +" </div></div> ");
                             $('#maincont').val('');
                             $('.txtcomment1').append('<div id="success_msg"><span>Response added successfully<span></div>');
                             $("#success_msg").fadeOut(5000, function(){
                                $("#success_msg").remove();
                             })
                        }
                    });
                }
            });
/** delete answer*/
            $('body').on('click','.wk-button',function () {
                var result = confirm("Are you sure you want to delete this answer?");
                if (result) {
                    var answid=$(this).attr('id');
                    var thiss = $(this);
                    
                    $.ajax({
                        url     :   self.options.delete_url,
                        type    :   "post",
                        data    :   {ans:answid},
                        dataType:   "json",
                        showLoader: true,
                        success :   function (content) {
                            if (content.msg == 'yes') {
                                thiss.parent().parent().slideUp(500, function () {
                                    thiss.parent().parent().after('<span class="removing"> Respond is successfully deleted </span>');
                                    thiss.parent().parent().remove();
                                    $('.removing').delay(2000).hide(0);
                                });
                            }
                        }
                    });
                }
            });
        },
    });
    return $.mage.giveanswer;
});