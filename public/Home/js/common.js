var TT_NS=function(a,i){return a.dialog={init:function(){var a=this;a.globalCloseDialog(),a.operationDialog()},toastTime:{oToastTime:null,oCountdown:null},toast:function(o){var l=o||{},t=l.content||"There is no incoming message!",n=l.countdown||!1,s=l.time||3;if(0==i("#bm_toast").length){var c=i('<div id="bm_toast"><i></i><p></p></div>');i("body").append(c)}i("#bm_toast").find("i").text(s),i("#bm_toast").find("p").text(t),i("#bm_toast").animate({},function(){i("#bm_toast").addClass("show")}),n?(i("#bm_toast i").css("display","block"),clearInterval(a.dialog.oCountdown),a.dialog.oCountdown=setInterval(function(){i("#bm_toast i").text(--s)},1e3)):i("#bm_toast i").css("display","none"),clearTimeout(a.dialog.oToastTime),a.dialog.oToastTime=setTimeout(function(){i("#bm_toast").removeClass("show"),clearInterval(a.dialog.oCountdown),clearInterval(a.dialog.oToastTime)},1e3*s)},openDialogBg:function(a){var o=a||{},l=i('<div class="bm_dialogBg"></div>');o.css&&l.css(o.css),i("body").append(l)},dialogBg:function(o,l){!1===o?i(".bm_dialogBg").last().remove():!0===o?a.dialog.openDialogBg(l):0==i(".bm_dialogBg").length?a.dialog.openDialogBg(l):i(".bm_dialogBg").last().remove()},callbackFn:{callback:!1,fn:function(){}},alert:function(o,l){var t=o||{},n=t.addClass||"",s=t.content||"没有内容",c=t.btnText||"OK",e=t.shake||!1,d=i('<div class="bm_dialog alert dialog_show '+n+'"><i></i><div class="dialog_c"><div class="dialog_body">'+s+'</div><div class="dialog_foot"><input type="button" value="'+c+'" class="bm_btn confirm cancel" /></div></div></div>');e&&d.addClass("shakeX"),a.dialog.dialogBg(!0),i("body").append(d),a.dialog.callbackFn.callback=!1,l&&(a.dialog.callbackFn.callback=!0,a.dialog.callbackFn.fn=l)},confirm:function(o,l){var t=o||{},n=t.addClass||"",s=t.title||!1,c=t.content||"没有内容",e=t.btnTextA||"YES",d=t.btnTextB||"OK",g=t.shake||!1,r=null,r=i('<div class="bm_dialog confirm dialog_show '+n+'"><i></i><div class="dialog_c"><div class="dialog_head">'+s+'</div><div class="dialog_body">'+c+'</div><div class="dialog_foot clearfix"><input type="button" value="'+e+'" class="bm_btn_A minor cancel false" /><input type="button" value="'+d+'" class="bm_btn confirm true" /></div></div>');s||r.find(".dialog_head").remove(),g&&r.addClass("shakeX"),a.dialog.dialogBg(!0),i("body").append(r),a.dialog.callbackFn.callback=!1,l&&(a.dialog.callbackFn.callback=!0,a.dialog.callbackFn.fn=l)},prompt:function(o,l){var t=o||{},n=t.addClass||"",s=t.title||!1,c=t.content||"请输入...",e=t.btnText||"Submit",d=t.shake||!1,g=i('<div class="bm_dialog prompt dialog_show '+n+'"><i></i><div class="dialog_c"><div class="dialog_head">'+s+'</div><div class="dialog_body"><textarea>'+c+'</textarea></div><div class="dialog_foot"><input type="button" value="'+e+'" class="bm_btn caution" /></div><i class="icon pc-icon-close close_dialog"></i></div></div>');s||g.find(".dialog_head").remove(),d&&g.addClass("shakeX"),a.dialog.dialogBg(!0),i("body").append(g),a.dialog.callbackFn.callback=!1,l&&(a.dialog.callbackFn.callback=!0,a.dialog.callbackFn.fn=l)},custom:function(o,l){var t=o||{},n=t.addClass||"",s=t.content||"请输入...",c=i('<div class="bm_dialog custom dialog_show '+n+'"><i></i><div class="dialog_c">'+s+'<i class="icon icon-close close_dialog"></i></div></div>');a.dialog.dialogBg(!0),i("body").append(c)},closeDialog:function(i){i.hasClass("alert")||i.hasClass("confirm")||i.hasClass("prompt")||i.hasClass("custom")?(i.remove(),a.dialog.callbackFn.callback&&a.dialog.callbackFn.fn(!1)):i.removeClass("dialog_show"),a.dialog.dialogBg(!1)},globalCloseDialog:function(){i(document).on("click",".bm_dialog",function(){a.dialog.closeDialog(i(this))}),i(document).on("click",".close_dialog",function(){var o=i(this).parents(".bm_dialog");a.dialog.closeDialog(o)}),i(document).on("click",".dialog_c",function(a){a.stopPropagation()}),i(document).on("click",".bm_dialogBg",function(){i(".hide_l,.hide_t,.hide_r,.hide_b").removeClass("show"),a.dialog.dialogBg(!1)}),i(document).on("click",".close_show",function(){i(".hide_l,.hide_t,.hide_r,.hide_b").removeClass("show"),a.dialog.dialogBg(!1)})},operationDialog:function(){i(document).on("click",".alert .dialog_foot .bm_btn",function(){i(this).parents(".bm_dialog").remove(),a.dialog.dialogBg(!1),a.dialog.callbackFn.callback&&a.dialog.callbackFn.fn(!0)}),i(document).on("click",".bm_dialog .true",function(){if(i(this).parents(".bm_dialog").remove(),a.dialog.dialogBg(!1),i(this).parents(".bm_dialog").hasClass("confirm")){var o=i(this).parents(".confirm");a.dialog.callbackFn.callback&&a.dialog.callbackFn.fn(!0,o)}else a.dialog.callbackFn.callback&&a.dialog.callbackFn.fn(!0)}),i(document).on("click",".bm_dialog .false",function(){i(this).parents(".bm_dialog").remove(),a.dialog.dialogBg(!1),a.dialog.callbackFn.callback&&a.dialog.callbackFn.fn(!1)}),i(document).on("click",".prompt .dialog_foot .bm_btn",function(){i(this).parents(".bm_dialog").remove(),a.dialog.dialogBg(!1);var o=i(this).parents(".bm_dialog").find("textarea").val();a.dialog.callbackFn.callback&&a.dialog.callbackFn.fn(o)})}},a.dialog.init(),a}(window.TT_NS||{},jQuery);!function(a,i,o,l){var t=function(a){this.$element=a,this.settings={trigger:a.attr("data-trigger")&&"hover"==a.attr("data-trigger")?"mouseenter":"click",switchType:a.attr("data-switch")&&"fade"==a.attr("data-switch")?"fade":"hide"}};t.prototype={construct:t,show:function(){console.dir(this),console.log(this.settings.trigger+":"+this.settings.switchType);var i=this,o=this.$element,l=o.children("ul").children("li"),t=o.children(".tab_content").children(".item");return"click"==i.settings.trigger?l.click(function(){var o=a(this).index(),l=t.eq(o);i.switch(a(this),l)}):l.mouseenter(function(){var o=a(this).index(),l=t.eq(o);i.switch(a(this),l)}),this},switch:function(a,i){"hide"==this.settings.switchType?(a.addClass("active").siblings().removeClass("active"),i.addClass("active").siblings().removeClass("active")):(a.addClass("active").siblings().removeClass("active"),i.fadeIn().siblings().fadeOut())}},a.fn.Tab=function(){return this.each(function(){return new t(a(this)).show()})},a('[data-toggle="tab"]').Tab()}(jQuery,window,document);var head={init:function(){this.showHint(),this.iconShow(".m_head_left",".m_head_left_ul"),this.iconShow(".m_head_right",".m_head_right_div"),this.crossClose(".m_head_right_div"),this.crossClose(".m_head_left_ul"),this.crossClose(".m_forum_map"),this.showSearch()},showHint:function(){$(".head_hint").on("mouseover",function(){$(this).css("cursor","pointer"),$(".head_hint_main").show()}).on("mouseleave",function(){$(".head_hint_main").hide()})},iconShow:function(a,i,o){$(a).on("click",function(){$(".mask_layer").show(),$(o).removeClass("show"),$(i).addClass("show")})},crossClose:function(a){$(".icon_cross").on("click",function(){$(a).removeClass("show"),$(".mask_layer").hide()}),$(".mask_layer").on("click",function(){$(a).removeClass("show"),$(".mask_layer").hide()})},showSearch:function(){$(".search_par").on("click",function(){$(".searchBox").show()}),$("body").on("click",function(a){"search_par"!=a.target.id&&"searchBox"!=a.target.id&&"search_icon"!=a.target.id&&"search_btn"!=a.target.id&&"search"!=a.target.id&&$(".searchBox").hide()})}};$(document).ready(function(){head.init()});