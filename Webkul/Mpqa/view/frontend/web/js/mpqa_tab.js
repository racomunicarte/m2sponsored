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
    'mage/mage',
    "mage/template",
    "Magento_Ui/js/modal/modal",
    'Webkul_Mpqa/js/model/authentication-popup'
], function ($,mage,template,modal,authenticationPopup) {
    'use strict';
    $.widget('mage.mpqa_tab', {
        
        _create: function () {
            var self = this;
    /** ------question search */
            $(document).on('keypress','#searchqa', function (e) {
                if (e.which == 13) {
                    search();
                }
            });

            $(document).on('click','.search_button',function(e) {
                search();
            });

            function search(){
                var query = $('#searchqa').val();
                if ($.trim(query)!='') {
                    $("body").append(jQuery("<div/>").addClass("filterurl_loader").append(jQuery("<div/>")));
                    $('.wk-qa-action').css('display','none');
                    $.ajax({
                        url     :   self.options.search_url,
                        type    :   "POST",
                        data    :   {pid:self.options.product_id,
                                    query:$('#searchqa').val()},
                        dataType:   "json",
                        success:function (data1) {
                            $('.all-questions').html('');
                            var ask_ques=template('#ask-question-template');
                            $.each(data1, function () {
                                var templateData = template('#question-answertemplate');
                                var questions = templateData({
                                                data: {
                                                    question_id: this['question_id'],
                                                    subject: this['subject'],
                                                    content: this['content'],
                                                    qa_nickname:this['qa_nickname'],
                                                    qa_date:this['qa_date'],
                                                    answer:this['answer'],
                                                    answer_id:this['answer_id'],
                                                    nickname:this['nickname'],
                                                    likes:this['likes'],
                                                    dislikes:this['dislikes'],
                                                    createdat:this['createdat'],
                                                    answer_count:this['count']
                                                }
                                            });
                                $('.all-questions').append(questions);
                            });
                            
                            $('.all-questions').append(ask_ques);
                            if (data1.length==0) {
                                $(".no-result").show();
                            }
                            $('.wk-qa-action-label-search').html($("<span/>").html(query).text());
                            $('#wk-qa-action-search').css('display','inline-block');
                            $("body").find('.filterurl_loader').remove();
                            $(".pager").remove();
                        }
                    });
                }
            }
    /** ---- recent url */
            $(document).on('click','#recent', function () {
                $('.wk-qa-action').css('display','none');
                $("body").append(jQuery("<div/>").addClass("filterurl_loader").append(jQuery("<div/>")));
                $.ajax({
                    url     :   self.options.recent_url,
                    type    :   "POST",
                    data    :   {pid:self.options.product_id},
                    dataType:   "json",
                    success:function (data1) {
                        $('.all-questions').html('');
                        var ask_ques=template('#ask-question-template');
                        $.each(data1, function () {
                            var employeeTemplate = template('#question-answertemplate');
                            var employee = employeeTemplate({
                                                data: {
                                                    question_id: this['question_id'],
                                                    subject: this['subject'],
                                                    content: this['content'],
                                                    qa_nickname:this['qa_nickname'],
                                                    qa_date:this['qa_date'],
                                                    answer:this['answer'],
                                                    answer_id:this['answer_id'],
                                                    nickname:this['nickname'],
                                                    likes:this['likes'],
                                                    dislikes:this['dislikes'],
                                                    createdat:this['createdat'],
                                                    answer_count:this['count']
                                                }
                                            });
                            
                            $('.all-questions').append(employee);
                        });
                         $('.all-questions').append(ask_ques);
                         $("body").find('.filterurl_loader').remove();
                         $(".pager").remove();
                         $('#wk-qa-action-recent').css('display','inline-block');                         
                    }
                });
                
            });
    /** ---- most helpful */
            $(document).on('click','#helpful', function () {
                $('.wk-qa-action').css('display','none');
                $("body").append(jQuery("<div/>").addClass("filterurl_loader").append(jQuery("<div/>")));
                $.ajax({
                    url     :   self.options.helpful_url,
                    type    :   "POST",
                    data    :   {pid:self.options.product_id},
                    dataType:   "json",
                    success:function (data1) {
                        $('.all-questions').html('');
                        var ask_ques=template('#ask-question-template');
                        $.each(data1, function () {
                            var employeeTemplate = template('#question-answertemplate');
                            var employee = employeeTemplate({
                                                data: {
                                                    question_id: this['question_id'],
                                                    subject: this['subject'],
                                                    content: this['content'],
                                                    qa_nickname:this['qa_nickname'],
                                                    qa_date:this['qa_date'],
                                                    answer:this['answer'],
                                                    answer_id:this['answer_id'],
                                                    nickname:this['nickname'],
                                                    likes:this['likes'],
                                                    dislikes:this['dislikes'],
                                                    createdat:this['createdat'],
                                                    answer_count:this['count'],
                                                    rating: this['rating']
                                                }
                                            });
                            
                            $('.all-questions').append(employee);
                        });
                         $('.all-questions').append(ask_ques);
                         $("body").find('.filterurl_loader').remove();
                         $(".pager").remove();
                         $('#wk-qa-action-helpful').css('display','inline-block');
                    }
                });
            });
            
            $(document).on('click','.wk-qa-action-button',function() {
                location.reload();
            });
    /** ------ more answer */
            $(document).on('click','.qa-ansmore', function () {
                var questionid =    $(this).attr('dataid');
                var this_this=$(this);
                var logid = self.options.buyer_id;
                
                $("body").append(jQuery("<div/>").addClass("filterurl_loader").append(jQuery("<div/>")));
                $.ajax({
                    url: self.options.viewall_ans_url,
                    data:{quesid:questionid,custid:logid},
                    dataType:'json',
                    success:function (content) {
                        $(this_this).parent().hide();
                        $(this_this).parent().after(content['answer']);
                        $("body").find('.filterurl_loader').remove();
                    }
                });
            });
    /** ------ like answer */
            $(document).on('click','.like', function () {

                var this_this=$(this);
                var login=self.options.islogin;
                if (login==0) {
                    authenticationPopup.showModal();
                    return false;
                }
                $("body").append($("<div/>").addClass("filterurl_loader").append($("<div/>")));
                var ansid = $(this).attr('dataid');
                var logid = self.options.buyer_id;
                $.ajax({
                    url:self.options.reviewanswer_url,
                    data:{ansid:ansid,custid:logid,action:'like'},
                    success:function () {
                        var count=$(this_this).next('span').html();
                        count++;
                        $(this_this).next('span').text(count);
                        $(this_this).addClass("liked").removeClass('like');
                        $(this_this).siblings('.dislike').addClass("disliked").removeClass("dislike");
                        $("body").find('.filterurl_loader').remove();
                    }
                });
            });

    /** ------ dislike answer */
            $(document).on('click','.dislike', function () {
                var ansid = $(this).attr('dataid');
                var logid = self.options.buyer_id;
                var this_this=$(this);
                var login=self.options.islogin;
                if (login==0) {
                    authenticationPopup.showModal();
                    return false;
                }
                $("body").append(jQuery("<div/>").addClass("filterurl_loader").append(jQuery("<div/>")));
                $.ajax({
                    url:self.options.reviewanswer_url,
                    data:{ansid:ansid,custid:logid,action:'dislike'},
                    success:function () {
                        var count=$(this_this).next('span').html();
                        count++;
                        $(this_this).next('span').text(count);
                        $(this_this).addClass("disliked").removeClass('dislike');
                        $(this_this).siblings('.like').addClass("liked").removeClass("like");
                        $("body").find('.filterurl_loader').remove();
                    }
                });
                
            });

            var qaAnsForm = $('#qa-ans-form');
                qaAnsForm.mage('validation', {});
            var qaQuesForm= $('#qa-ques-form');
                qaQuesForm.mage('validation', {});
   
            /** question modal */
            var options_ques = {
                type: 'popup',responsive: true,innerScroll: true,title: 'Have Any Query?',
                buttons: [{
                        text: 'Reset',
                        class:'',
                        click: function () {
                            $('#qa-ques-form input,#qa-ques-form textarea').removeClass('mage-error');
                            $('#qa-ques-form')[0].reset();
                        } /** handler on button click */
                    },{
                        text: 'Submit Query',
                        class: 'wk-question-submit',
                        click: function () {
                            /** -----save question */
                            var su = $('#sub').val();
                            var cn = $('#content').val();
                            var nickname=$('#qa_nickname').val();
                            var seller_id=self.options.seller_id ;
                            var adurl = $('#adminurl').val();
                            var nm = "";
                            if (qaQuesForm.valid()!=false) {
                                var thisthis = $(this);
                                $.ajax({
                                    url     :   self.options.question_url,
                                    type    :   "POST",
                                    data    :   {pid:self.options.product_id,
                                                subj:$('#sub').val(), con:cn,aurl:adurl,nickname:nickname,seller_id:seller_id},
                                    dataType:   "html",
                                    showLoader: true,
                                    success :   function (content) {
                                        $('#qa-ques-form')[0].reset();
                                        alert("Your query has been submitted");
                                        location.reload();
                                    }
                                });
                                this.closeModal();
                            }
                        } /** handler on button click */
                    }
                ]
            };
            var popup = modal(options_ques, $('#wk-qa-ask-qa'));

            $(document).on('click','.qa-question', function () {
                var login=self.options.islogin;
                if (login==0) {
                    authenticationPopup.showModal();
                    return false;
                }
                
                $('#wk-qa-ask-qa').modal('openModal');
            });
    
            /** answer modal */
            var options_ans = {
                type: 'popup',responsive: true,innerScroll: true,title: 'Submit Answer',
                buttons: [{
                        text: 'Reset',
                        class:'',
                        click: function () {
                            $('#qa-ans-form input,#qa-ans-form textarea').removeClass('mage-error');
                            $('#qa-ans-form')[0].reset();
                        }
                    },{
                        text: 'Submit Answer',
                        class: 'wk-answer-submit',
                        click: function () {
                            /** -----save answer */
                            if (qaAnsForm.valid()!=false) {
                                var thisthis = $(this);
                                $.ajax({
                                    url:self.options.submitanswer_url,
                                    data:$('#qa-ans-form').serialize(),
                                    type:'post',
                                    showLoader: true,
                                    dataType:'json',
                                    success:function (d) {
                                        if (d.status) {
                                            alert("Your answer has been submitted");
                                            $('#qa-ans-form')[0].reset();
                                            location.reload();
                                        } else {
                                            alert("You are not authorised to answer this question.");
                                        }
                                    }
                                });
                                this.closeModal();
                            }
                        }
                    }
                ]
            };
            var popup1 = modal(options_ans, $('#wk-qa-ask-data'));
            $(document).on('click','.qa-ans', function () {
                var login=self.options.islogin;
                if (login==0) {
                    authenticationPopup.showModal();
                    return false;
                }
                            
                var q_id=$(this).parent().siblings('.alogo').attr('id');
                q_id=q_id.substring(2);
                
                $("#question-id").val(q_id);
                $('#wk-qa-ask-data').modal('openModal');
            });
            
            $(document).on('click','.action-close', function () {
                $('#qa-ques-form')[0].reset();
                $('#qa-ans-form')[0].reset();
            });
     /** pager link */
            var listItems = $("ul.items.pages-items li.item a");
            listItems.each(function (idx, a) {

                var link=$(a).attr("href");
                link=link+'#mpqa.tab';
                $(a).attr("href",link);
            });

            $('.search-form').submit(function() {
                var query = $('#wk-searchqa').val();
                if ($.trim(query) =='') {
                    event.preventDefault();
                }
            });
        },
        
    });
    return $.mage.mpqa_tab;
});