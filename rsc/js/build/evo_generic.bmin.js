/* This file includes ALL generic files that may be used on any page of front-office and back-office */

function evo_prevent_key_enter(e){jQuery(e).keypress(function(e){if(13==e.keyCode)return!1})}function evo_render_star_rating(){jQuery("#comment_rating").each(function(e){var t=jQuery("span.raty_params",this);t&&jQuery(this).html("").raty(t)})}"undefined"!=typeof evo_link_attachment_window_config&&(window.link_attachment_window=function(e,t,i,n,o,r){return openModalWindow('<span class="loader_img loader_user_report absolute_center" title="'+evo_link_attachment_window_config.loader_title+'"></span>',"90%","80%",!0,evo_link_attachment_window_config.window_title,"",!0),jQuery.ajax({type:"POST",url:htsrv_url+"async.php",data:{action:"link_attachment",link_owner_type:e,link_owner_ID:t,crumb_link:evo_link_attachment_window_config.crumb_link,root:void 0===i?"":i,path:void 0===n?"":n,fm_highlight:void 0===o?"":o,prefix:void 0===r?"":r},success:function(e){openModalWindow(e,"90%","80%",!0,evo_link_attachment_window_config.window_title,"")}}),!1}),jQuery(document).ready(function(){if("undefined"!=typeof evo_init_datepicker&&jQuery(evo_init_datepicker.selector).datepicker(evo_init_datepicker.config),"undefined"!=typeof evo_link_position_config&&(window.displayInlineReminder=evo_link_position_config.display_inline_reminder,window.deferInlineReminder=evo_link_position_config.defer_inline_reminder,jQuery(document).on("change",evo_link_position_config.selector,{url:evo_link_position_config.url,crumb:evo_link_position_config.crumb},function(e){"inline"==this.value&&window.displayInlineReminder&&!window.deferInlineReminder&&(alert(evo_link_position_config.alert_msg),window.displayInlineReminder=!1),evo_link_change_position(this,e.data.url,e.data.crumb)})),"undefined"!=typeof evo_itemform_renderers__click&&jQuery("#itemform_renderers .dropdown-menu").on("click",function(e){e.stopPropagation()}),"undefined"!=typeof evo_commentform_renderers__click&&jQuery("#commentform_renderers .dropdown-menu").on("click",function(e){e.stopPropagation()}),"undefined"!=typeof evo_disp_download_delay_config&&(window.b2evo_download_timer=evo_disp_download_delay_config,window.downloadInterval=setInterval(function(){jQuery("#download_timer").html(window.b2evo_download_timer),0==window.b2evo_download_timer&&(clearInterval(window.downloadInterval),jQuery("#download_help_url").show()),window.b2evo_download_timer--},1e3),jQuery("#download_timer_js").show()),"undefined"!=typeof evo_skin_bootstrap_forum__quote_button_click&&jQuery(".quote_button").click(function(){var e=jQuery("form[id^=evo_comment_form_id_]");return 0==e.length||(e.attr("action",jQuery(this).attr("href")),e.submit(),!1)}),"undefined"!=typeof evo_ajax_form_config)for(var t=Object.values(evo_ajax_form_config),a=0;a<t.length;a++)!function(){var o=t[a];window["ajax_form_offset_"+o.form_number]=jQuery("#ajax_form_number_"+o.form_number).offset().top,window["request_sent_"+o.form_number]=!1,window["ajax_form_loading_number_"+o.form_number]=0;var r="get_form"+o.form_number;window[r]=function(){var n="#ajax_form_number_"+o.form_number;window["ajax_form_loading_number_"+o.form_number]++,jQuery.ajax({url:htsrv_url+"anon_async.php",type:"POST",data:o.json_params,success:function(e){jQuery(n).html(ajax_debug_clear(e)),"get_comment_form"==o.json_params.action&&evo_render_star_rating()},error:function(e,t,i){jQuery(".loader_ajax_form",n).after('<div class="red center">'+i+": "+e.responseText+"</div>"),window["ajax_form_loading_number_"+o.form_number]<3&&setTimeout(function(){jQuery(".loader_ajax_form",n).next().remove(),window[r]()},1e3)}})};var e="check_and_show_"+o.form_number;window[e]=function(e){if(!window["request_sent_"+o.form_number]){var t=null!=typeof e&&e;(t=t||jQuery(window).scrollTop()>=window["ajax_form_offset_"+o.form_number]-jQuery(window).height()-20)&&(window["request_sent_"+o.form_number]=!0,window[r]())}},jQuery(window).scroll(function(){window[e]()}),jQuery(window).resize(function(){window[e]()}),window[e](o.load_ajax_form_on_page_load)}();var e;"undefined"!=typeof evo_thread_form_config&&(window.check_multiple_recipients=function(){1<jQuery('input[name="thrd_recipients_array[login][]"]').length?jQuery("#multiple_recipients").show():jQuery("#multiple_recipients").hide()},window.check_form_thread=function(){return""==jQuery("input#token-input-thrd_recipients").val()||(alert(evo_thread_form_config.missing_username_msg),jQuery("input#token-input-thrd_recipients").focus(),!1)},evo_thread_form_config.token_input_config.tokenFormatter=function(e){return"<li>"+e[evo_thread_form_config.username_display]+'<input type="hidden" name="thrd_recipients_array[id][]" value="'+e.id+'" /><input type="hidden" name="thrd_recipients_array[login][]" value="'+e.login+'" /></li>'},evo_thread_form_config.token_input_config.resultsFormatter=function(e){var t=e.login;return null!=e.fullname&&void 0!==e.fullname&&(t+="<br />"+e.fullname),"<li>"+e.avatar+"<div>"+t+"</div><span></span></li>"},evo_thread_form_config.token_input_config.onAdd=function(){window.check_multiple_recipients()},evo_thread_form_config.token_input_config.onDelete=function(){window.check_multiple_recipients()},evo_thread_form_config.token_input_config.onReady=function(){evo_thread_form_config.thrd_recipients_has_error&&jQuery(".token-input-list-facebook").addClass("token-input-list-error"),jQuery("#thrd_recipients").removeAttr("required")},jQuery("#thrd_recipients").tokenInput(restapi_url+"users/recipients",evo_thread_form_config.token_input_config),window.check_multiple_recipients()),"undefined"!=typeof evo_comment_form_preview_button_config&&jQuery("input[type=submit].preview.btn-info").val(evo_comment_form_preview_button_config.button_value),"undefined"!=typeof evo_user_func__callback_filter_userlist&&(jQuery("#country").change(function(){jQuery(this);jQuery.ajax({type:"POST",url:htsrv_url+"anon_async.php",data:"action=get_regions_option_list&ctry_id="+jQuery(this).val(),success:function(e){jQuery("#region").html(ajax_debug_clear(e)),1<jQuery("#region option").length?jQuery("#region_filter").show():jQuery("#region_filter").hide(),load_subregions(0)}})}),jQuery("#region").change(function(){load_subregions(jQuery(this).val())}),jQuery("#subregion").change(function(){load_cities(jQuery("#country").val(),jQuery("#region").val(),jQuery(this).val())}),window.load_subregions=function(t){jQuery.ajax({type:"POST",url:htsrv_url+"anon_async.php",data:"action=get_subregions_option_list&rgn_id="+t,success:function(e){jQuery("#subregion").html(ajax_debug_clear(e)),1<jQuery("#subregion option").length?jQuery("#subregion_filter").show():jQuery("#subregion_filter").hide(),load_cities(jQuery("#country").val(),t,0)}})},window.load_cities=function(e,t,i){void 0===e&&(e=0),jQuery.ajax({type:"POST",url:htsrv_url+"anon_async.php",data:"action=get_cities_option_list&ctry_id="+e+"&rgn_id="+t+"&subrg_id="+i,success:function(e){jQuery("#city").html(ajax_debug_clear(e)),1<jQuery("#city option").length?jQuery("#city_filter").show():jQuery("#city_filter").hide()}})}),"undefined"!=typeof coll_activity_stats_widget_config&&(window.resize_coll_activity_stat_widget=function(){var e=[],t=[],i=[],n=coll_activity_stats_widget_config.time_period;if(null==plot){plot=jQuery("#canvasbarschart").data("plot"),i=plot.axes.xaxis.ticks.slice(0);for(var o=0;o<plot.series.length;o++)e.push(plot.series[o].data.slice(0));if(7==e[0].length)t=e;else for(o=0;o<e.length;o++){for(var r=[],a=7,_=1;0<a;a--,_++)r.unshift([a,e[o][e[o].length-_][1]]);t.push(r)}}if(jQuery("#canvasbarschart").width()<650){if("last_week"!=n){for(o=0;o<plot.series.length;o++)plot.series[o].data=t[o];plot.axes.xaxis.ticks=i.slice(-7),n="last_week"}}else if("last_month"!=n){for(o=0;o<plot.series.length;o++)plot.series[o].data=e[o];plot.axes.xaxis.ticks=i,n="last_month"}plot.replot({resetAxes:!0})},jQuery(window).resize(function(){clearTimeout(e),e=setTimeout(resize_coll_activity_stat_widget,100)}));if("undefined"!=typeof evo_link_sortable_js_config){var o=Object.values(evo_link_sortable_js_config);for(a=0;a<o.length;a++)jQuery("#"+o[a].fieldset_prefix+"attachments_fieldset_table table").sortable({containerSelector:"table",itemPath:"> tbody",itemSelector:"tr",placeholder:jQuery.parseHTML('<tr class="placeholder"><td colspan="5"></td></tr>'),onMousedown:function(e,t,i){if(!i.target.nodeName.match(/^(a|img|select|span)$/i))return i.preventDefault(),!0},onDrop:function(t,e,i){jQuery("#"+o[a].fieldset_prefix+"attachments_fieldset_table table tr").removeClass("odd even"),jQuery("#"+o[a].fieldset_prefix+"attachments_fieldset_table table tr:odd").addClass("even"),jQuery("#"+o[a].fieldset_prefix+"attachments_fieldset_table table tr:even").addClass("odd");var n="";jQuery("#"+o[a].fieldset_prefix+"attachments_fieldset_table table tr").each(function(){var e=jQuery(this).find(".link_id_cell > span[data-order]");0<e.length&&(n+=e.html()+",")}),n=n.slice(0,-1),jQuery.ajax({url:htsrv_url+"anon_async.php",type:"POST",data:{action:"update_links_order",links:n,crumb_link:o[a].crumb_link},success:function(e){link_data=JSON.parse(ajax_debug_clear(e)),jQuery("#"+o[a].fieldset_prefix+"attachments_fieldset_table table tr").each(function(){var e=jQuery(this).find(".link_id_cell > span[data-order]");0<e.length&&e.attr("data-order",link_data[e.html()])}),evoFadeSuccess(t)}}),t.removeClass(e.group.options.draggedClass).removeAttr("style")}})}if("undefined"!=typeof evo_link_initialize_fieldset_config){var i=Object.values(evo_link_initialize_fieldset_config);for(a=0;a<i.length;a++)evo_link_initialize_fieldset(i[a].fieldset_prefix)}if("undefined"!=typeof evo_display_attachments_fieldset_config){var n=Object.values(evo_display_attachments_fieldset_config);for(a=0;a<n.length;a++)jQuery("#"+n[a].fieldset_prefix+n[a].form_id).show()}}),jQuery(document).ready(function(){"undefined"!=typeof evo_skin_bootstrap_forums__post_list_header&&jQuery("#evo_workflow_status_filter").change(function(){var e=location.href.replace(/([\?&])((status|redir)=[^&]*(&|$))+/,"$1"),t=jQuery(this).val();""!==t&&(e+=(-1==e.indexOf("?")?"?":"&")+"status="+t+"&redir=no"),location.href=e.replace("?&","?").replace(/\?$/,"")})}),jQuery(document).ready(function(){"undefined"!=typeof evo_comment_rating_config&&evo_render_star_rating()}),jQuery(document).ready(function(){"undefined"!=typeof evo_widget_coll_search_form&&(jQuery(evo_widget_coll_search_form.selector).tokenInput(evo_widget_coll_search_form.url,evo_widget_coll_search_form.config),void 0!==evo_widget_coll_search_form.placeholder&&jQuery("#token-input-search_author").attr("placeholder",evo_widget_coll_search_form.placeholder).css("width","100%"))}),jQuery(document).ready(function(){"undefined"!=typeof evo_autocomplete_login_config&&(jQuery("input.autocomplete_login").on("added",function(){jQuery("input.autocomplete_login").each(function(){if(!jQuery(this).hasClass("tt-input")&&!jQuery(this).hasClass("tt-hint")){var t="";t=jQuery(this).hasClass("only_assignees")?restapi_url+evo_autocomplete_login_config.url:restapi_url+"users/logins",jQuery(this).data("status")&&(t+="&status="+jQuery(this).data("status")),jQuery(this).typeahead(null,{displayKey:"login",source:function(e,n){jQuery.ajax({type:"GET",dataType:"JSON",url:t,data:{q:e},success:function(e){var t=new Array;for(var i in e.list)t.push({login:e.list[i]});n(t)}})}})}})}),jQuery("input.autocomplete_login").trigger("added"),evo_prevent_key_enter(evo_autocomplete_login_config.selector))}),jQuery(document).ready(function(){"undefined"!=typeof evo_widget_poll_initialize&&(jQuery('.evo_poll__selector input[type="checkbox"]').on("click",function(){var e=jQuery(this).closest(".evo_poll__table"),t=jQuery(".evo_poll__selector input:checked",e).length>=e.data("max-answers");jQuery(".evo_poll__selector input[type=checkbox]:not(:checked)",e).prop("disabled",t)}),jQuery(".evo_poll__table").each(function(){var e=jQuery(this);e.width()>e.parent().width()&&(jQuery(".evo_poll__title",e).css("white-space","normal"),jQuery(".evo_poll__title label",e).css({width:Math.floor(e.parent().width()/2)+"px","word-wrap":"break-word"}))}))}),jQuery(document).ready(function(){if("undefined"!=typeof evo_plugin_auto_anchors_settings){jQuery("h1, h2, h3, h4, h5, h6").each(function(){if(jQuery(this).attr("id")&&jQuery(this).hasClass("evo_auto_anchor_header")){var e=location.href.replace(/#.+$/,"")+"#"+jQuery(this).attr("id");jQuery(this).append(' <a href="'+e+'" class="evo_auto_anchor_link"><span class="fa fa-link"></span></a>')}});var t=jQuery("#evo_toolbar").length?jQuery("#evo_toolbar").height():0;jQuery(".evo_auto_anchor_link").on("click",function(){var e=jQuery(this).attr("href");return jQuery("html,body").animate({scrollTop:jQuery(this).offset().top-t-evo_plugin_auto_anchors_settings.offset_scroll},function(){window.history.pushState("","",e)}),!1})}}),jQuery(document).ready(function(){if("undefined"!=typeof evo_plugin_table_contents_settings){var i=jQuery("#evo_toolbar").length?jQuery("#evo_toolbar").height():0;jQuery(".evo_plugin__table_of_contents a").on("click",function(){var e=jQuery("#"+jQuery(this).data("anchor"));if(0==e.length||!e.prop("tagName").match(/^h[1-6]$/i))return!0;var t=jQuery(this).attr("href");return jQuery("html,body").animate({scrollTop:e.offset().top-i-evo_plugin_table_contents_settings.offset_scroll},function(){window.history.pushState("","",t)}),!1})}}),jQuery(document).ready(function(){var n,i,o,r,a;"undefined"!=typeof evo_plugin_tinymce_config__toggle_switch_warning&&(n=evo_plugin_tinymce_config__toggle_switch_warning,window.toggle_switch_warning=function(t){var e=n.activate_link,i=n.deactivate_link;return jQuery.get(t?e:i,function(e){jQuery(document).trigger("wysiwyg_warning_changed",[t])}),!1}),"undefined"!=typeof evo_plugin_tinymce_config__quicksettings&&(i=evo_plugin_tinymce_config__quicksettings,o=jQuery("#"+i.item_id),jQuery(document).on("wysiwyg_warning_changed",function(e,t){o.html(t?i.deactivate_warning_link:i.activate_warning_link)})),"undefined"!=typeof evo_plugin_tinymce_config__toggle_editor&&(r=evo_plugin_tinymce_config__toggle_editor,window.displayWarning=r.display_warning,window.confirm_switch=function(){return jQuery("input[name=hideWarning]").is(":checked")&&window.toggle_switch_warning(!1),window.tinymce_plugin_toggleEditor(r.content_id),closeModalWindow(),!1},window.tinymce_plugin_toggleEditor=function(e){var t=jQuery("#"+r.content_id);if(jQuery("[id^=tinymce_plugin_toggle_button_]").removeClass("active").attr("disabled","disabled"),!window.tinymce_plugin_init_done)return window.tinymce_plugin_init_done=!0,void window.tinymce_plugin_init_tinymce(function(){window.tinymce_plugin_toggleEditor(null)});window.tinymce.get(e)?(window.tinymce.execCommand("mceRemoveEditor",!1,e),jQuery.get(r.save_editor_state_url),jQuery("#tinymce_plugin_toggle_button_html").addClass("active"),jQuery("#tinymce_plugin_toggle_button_wysiwyg").removeAttr("disabled"),jQuery('[name="editor_code"]').attr("value","html"),jQuery(".quicktags_toolbar, .evo_code_toolbar, .evo_prism_toolbar, .b2evMark_toolbar, .evo_mermaid_toolbar").show(),jQuery("#block_renderer_evo_code, #block_renderer_evo_prism, #block_renderer_b2evMark, #block_renderer_evo_mermaid").removeClass("disabled"),jQuery("input#renderer_evo_code, input#renderer_evo_prism, input#renderer_b2evMark, input#renderer_evo_mermaid").each(function(){jQuery(this).hasClass("checked")&&jQuery(this).attr("checked","checked").removeClass("checked"),jQuery(this).removeAttr("disabled")}),e&&t.attr("data-required")&&(t.removeAttr("data-required"),t.attr("required",!0))):(window.tinymce.execCommand("mceAddEditor",!1,e),jQuery.get(r.save_editor_state_url),jQuery("#tinymce_plugin_toggle_button_wysiwyg").addClass("active"),jQuery("#tinymce_plugin_toggle_button_html").removeAttr("disabled"),jQuery('[name="editor_code"]').attr("value",r.plugin_code),jQuery(".quicktags_toolbar, .evo_code_toolbar, .evo_prism_toolbar, .b2evMark_toolbar, .evo_mermaid_toolbar").hide(),jQuery("#block_renderer_evo_code, #block_renderer_evo_prism, #block_renderer_b2evMark, #block_renderer_evo_mermaid").addClass("disabled"),jQuery("input#renderer_evo_code, input#renderer_evo_prism, input#renderer_b2evMark, input#renderer_evo_mermaid").each(function(){jQuery(this).is(":checked")&&jQuery(this).addClass("checked"),jQuery(this).attr("disabled","disabled").removeAttr("checked")}),e&&t.prop("required")&&(t.attr("data-required",!0),t.removeAttr("required")))},jQuery(document).on("wysiwyg_warning_changed",function(e,t){window.displayWarning=t}),jQuery("[id^=tinymce_plugin_toggle_button_]").click(function(){"WYSIWYG"==jQuery(this).val()&&window.displayWarning?(evo_js_lang_close=r.cancel_btn_label,openModalWindow("<p>"+r.toggle_warning_msg+'</p><form><input type="checkbox" name="hideWarning" value="1"> '+r.wysiwyg_checkbox_label+'<input type="submit" name="submit" onclick="return confirm_switch();"></form>',"500px","",!0,'<span class="text-danger">'+r.warning_text+"</span>",[r.ok_btn_label,"btn-primary"],!0)):window.tinymce_plugin_toggleEditor(r.content_id)})),"undefined"!=typeof evo_plugin_tinymce_config__init&&(a=evo_plugin_tinymce_config__init,window.autocomplete_static_options=[],jQuery(".user.login").each(function(){var e=jQuery(this).text();""!=e&&-1==window.autocomplete_static_options.indexOf(e)&&("@"==e[0]&&(e=e.substr(1)),window.autocomplete_static_options.push(e))}),window.autocomplete_static_options=window.autocomplete_static_options.join(),window.tmce_init=a.tmce_init,window.tinymce_plugin_displayed_error=!1,window.tinymce_plugin_init_done=!1,window.tinymce_plugin_init_tinymce=function(t){void 0===window.tinymce?window.tinymce_plugin_displayed_error||(alert(a.display_error_msg),window.tinymce_plugin_displayed_error=!0):(void 0!==window.tmce_init.oninit&&(t=function(){window.tmce_init.oninit(),t()}),window.tmce_init.oninit=function(){t(),window.tinymce.get(a.content_id)&&"object"==typeof b2evo_Callbacks&&(b2evo_Callbacks.register_callback("get_selected_text_for_"+a.content_id,function(e){var t=window.tinymce.get(a.content_id);return t?t.selection.getContent():null},!0),b2evo_Callbacks.register_callback("wrap_selection_for_"+a.content_id,function(e){var t=window.tinymce.get(a.content_id);if(!t)return null;var i=t.selection.getContent();if(e.replace)var n=e.before+e.after;else n=e.before+i+e.after;return t.selection.setContent(n),!0},!0),b2evo_Callbacks.register_callback("str_replace_for_"+a.content_id,function(e){var t=window.tinymce.get(a.content_id);return t?(t.setContent(t.getContent().replace(e.search,e.replace)),!0):null},!0),b2evo_Callbacks.register_callback("insert_raw_into_"+a.content_id,function(e){return window.tinymce.execInstanceCommand(a.content_id,"mceInsertRawHTML",!1,e),!0},!0));var e=jQuery("#"+a.content_id);e.prop("required")&&(e.attr("data-required",!0),e.removeAttr("required"))},window.tmce_init.init_instance_callback=function(e){if(window.shortcut_keys)for(var t=0;t<window.shortcut_keys.length;t++){var i=window.shortcut_keys[t];e.shortcuts.add(i,"b2evo shortcut key: "+i,function(){window.shortcut_handler(i)})}},tmce_init.init_instance_callback=function(e){if(window.shortcut_keys)for(var t=0;t<window.shortcut_keys.length;t++){var i=window.shortcut_keys[t];e.shortcuts.add(i,"b2evo shortcut key: "+i,function(){window.shortcut_handler(i)})}},window.tmce_init.setup=function(e){e.on("init",window.tmce_init.oninit)},window.tinymce.on("AddEditor",function(t){var e=jQuery("#"+a.content_id);return e.val().match(/<(p\s?|br\s?\/?)[^>]*>/i)||jQuery.ajax({type:"POST",url:a.update_content_url,data:{content:e.val()},success:function(e){t.editor.setContent(e)}}),!1}),window.tinymce.init(window.tmce_init))},a.use_tinymce&&window.tinymce_plugin_toggleEditor(a.content_id),jQuery('[name="editor_code"]').attr("value",a.editor_code))}),jQuery(document).ready(function(){if("undefined"!=typeof evo_init_shortlinks_toolbar_config){var n=evo_init_shortlinks_toolbar_config;if(window.shortlinks_toolbar=function(e,t){var i=n.toolbar_title_before+e+n.toolbar_title_after+n.toolbar_group_before+'<input type="button" title="'+n.button_title+'" class="'+n.button_class+'" data-func="shortlinks_load_window|'+t+'" value="'+n.button_value+'" />'+n.toolbar_group_after;jQuery("."+t+n.plugin_code+"_toolbar").html(i)},"undefined"!=typeof evo_init_shortlinks_toolbar)for(var e=Object.values(evo_init_shortlinks_toolbar),t=0;t<e.length;t++)window.shortlinks_toolbar(e[t].title,e[t].prefix)}}),jQuery(document).ready(function(){if("undefined"!=typeof evo_init_polls_toolbar_config){var n=evo_init_polls_toolbar_config;if(window.polls_toolbar=function(e,t){var i=n.toolbar_title_before+e+n.toolbar_title_after+n.toolbar_group_before+'<input type="button" title="'+n.button_title+'" class="'+n.button_class+'" data-func="polls_load_window|'+t+'" value="'+n.button_value+'" />'+n.toolbar_group_after;jQuery("."+t+n.plugin_code+"_toolbar").html(i)},window.polls_load_window=function(e){return openModalWindow('<div id="poll_wrapper"></div>',"auto","",!0,n.modal_window_title,["Insert Poll"],!0),polls_load_polls(e),!1},window.polls_api_request=function(e,t,i){jQuery.ajax({url:restapi_url+e}).then(i,function(e){polls_api_print_error(t,e)})},window.polls_api_print_error=function(e,t){if("string"!=typeof t&&void 0===t.code&&(t=void 0===t.responseJSON?t.statusText:t.responseJSON),void 0===t.code)var i='<h4 class="text-danger">Unknown error: '+t+"</h4>";else{i='<h4 class="text-danger">'+t.message+"</h4>";n.debug&&(i+="<div><b>Code:</b> "+t.code+"</div><div><b>Status:</b> "+t.data.status+"</div>")}jQuery(e).html(i)},window.polls_load_polls=function(o){o=o||"",polls_api_request("polls","#poll_wrapper",function(e){var t='<div id="'+o+'polls_list">';for(var i in t+="<ul>",e.polls){var n=e.polls[i];t+='<li><a href="#" data-poll-id="'+n.pqst_ID+'" data-prefix="'+o+'">'+n.pqst_question_text+"</a></li>"}t+="</ul>",t+="</div>",jQuery("#poll_wrapper").html(t),jQuery(document).on("click","#"+o+"polls_list a[data-poll-id]",function(){"undefined"!=typeof tinyMCE&&void 0!==tinyMCE.activeEditor&&tinyMCE.activeEditor&&tinyMCE.execCommand("mceFocus",!1,tinyMCE.activeEditor.id);var e=jQuery(this).data("prefix")?jQuery(this).data("prefix"):"";return textarea_wrap_selection(window[e+"b2evoCanvas"],"[poll:"+jQuery(this).data("pollId")+"]","",0),closeModalWindow(),!1})})},"undefined"!=typeof evo_init_polls_toolbar)for(var e=Object.values(evo_init_polls_toolbar),t=0;t<e.length;t++)window.polls_toolbar(e[t].title,e[t].prefix)}}),jQuery(document).ready(function(){if("undefined"!=typeof evo_init_dragdrop_button_config){window.dndb={},window.init_uploader=function(prefix){var config=evo_init_dragdrop_button_config[prefix];if("draggable"in document.createElement("span"))var button_text=config.draggable_button_text,file_uploader_note_text=config.draggable_note_text;else var button_text=config.nondraggable_button_text,file_uploader_note_text=config.nondraggable_note_text;window.dndb[config.fieldset_prefix+"url"]=config.quickupload_url,window.dndb[config.fieldset_prefix+"root_and_path"]=config.root_and_path,jQuery("#fm_dirtree input[type=radio]").click(function(){window.dndb[config.fieldset_prefix+"url"]=config.quickupload_url+"&root_and_path="+this.value+"&"+config.crumb_file,window.dndb[config.fieldset_prefix+"root_and_path"]=this.value,window.dndb[config.fieldset_prefix+"uploader"].setParams({root_and_path:window.dndb[config.fieldset_prefix+"root_and_path"]})}),config.link_owner&&(window.dndb[config.fieldset_prefix+"url"]+="&link_owner="+config.link_owner),config.fm_mode&&"file_select"==config.fm_mode&&(window.dndb[config.fieldset_prefix+"url"]+="&fm_mode="+config.fm_mode),window.dndb[config.fieldset_prefix+"uploader"]=new qq.FineUploader({debug:!1,request:{endpoint:window.dndb[config.fieldset_prefix+"url"],params:{root_and_path:window.dndb[config.fieldset_prefix+"root_and_path"]}},template:document.getElementById(config.fieldset_prefix+"qq-template"),element:document.getElementById(config.fieldset_prefix+"file-uploader"),listElement:document.querySelector(config.list_element),dragAndDrop:{extraDropzones:eval(config.extra_dropzones)},list_style:config.list_style,action:window.dndb[config.fieldset_prefix+"url"],sizeLimit:config.size_limit,messages:{typeError:config.msg_type_error,sizeError:config.msg_size_error,minSizeError:config.msg_min_size_error,emptyError:config.msg_empty_error,onLeave:config.msg_on_leave},text:{formatProgress:config.msg_format_progress,sizeSymbols:[config.size_symbol_kb,config.size_symbol_mb,config.size_symbol_gb,config.size_symbol_tb,config.size_symbol_pb,config.size_symbol_eb]},validation:{sizeLimit:config.validation_size_limit,allowedExtensions:config.allowed_extensions},callbacks:{onSubmit:function(e,t,i){var n={root_and_path:window.dndb[config.fieldset_prefix+"root_and_path"]};if(jQuery(i).hasClass("link_attachment_dropzone")){qq.extend(n,{link_position:"inline"})}this.setParams(n);var o=jQuery("#"+config.fieldset_prefix+config.table_id+" tr.noresults");o.length&&(""!=config.table_headers&&o.parent().parent().prepend(config.table_headers),o.remove()),setTimeout(function(){evo_link_fix_wrapper_height(config.fieldset_prefix),config.resize_frame&&(window.dndb.update_iframe_height(config.fieldset_prefix),jQuery(document).on("load","#"+config.fieldset_prefix+config.table_id+" img",function(){window.dndb.update_iframe_height(config.fieldset_prefix)}))},10)},onProgress:function(e,t,i,n){var o=jQuery("#"+config.fieldset_prefix+config.table_id+" tr[qq-file-id="+e+"] .progress-bar"),r=Math.round(i/n*100)+"%";o.get(0).style.width=r,o.text(r),config.resize_frame&&window.dndb.update_iframe_height(config.fieldset_prefix)},onDropzoneDragOver:function(e){jQuery(".qq-upload-button").addClass("qq-upload-button-dragover")},onDropzoneDragOut:function(e){jQuery(".qq-upload-button").removeClass("qq-upload-button-dragover")},onDropzoneDragDrop:function(e){jQuery(".qq-upload-button").removeClass("qq-upload-button-dragover")},onComplete:function(e,t,i,n,o){var r;null!=i&&(i.data.text&&(r=1==i.specialchars?htmlspecialchars_decode(i.data.text):i.data.text),r=base64_decode(r),"list"==config.list_style&&null!=i.data.status&&"rename"==i.data.status&&jQuery("#"+config.fieldset_prefix+config.table_id+" #saveBtn").show());if("table"==config.list_style){var a=jQuery("#"+config.fieldset_prefix+config.table_id+" tr[qq-file-id="+e+"]");if(null==i||null==i.data||"error"==i.data.status||"fatal"==i.data.status)a.find(".qq-upload-status").html('<span class="red">'+config.msg_upload_error+"</span>"),i.error?r=i.error:void 0!==r&&""!=r||(r=config.msg_dropped_connection),a.find(".qq-upload-file-selector").append(': <span class="text-danger result_error">'+r+"</span>"),a.find(".qq-upload-image-selector, td.size").prepend(config.warning_icon);else{var _=void 0!==i.data.link_ID?"link":"file",l=config.filename_before;""!=l&&(l=l.replace("$file_path$",i.data.path));var d="";i.data.select_link_button&&(d=i.data.select_link_button);var s="";""!=i.data.warning&&(s='<div class="orange">'+i.data.warning+"</div>");var c=void 0!==i.data.link_url?i.data.link_url:i.data.formatted_name;if(a.find(".qq-upload-checkbox").html(i.data.checkbox),"success"==i.data.status)config.display_status_success?a.find(".qq-upload-status-text-selector").html('<span class="green">'+config.msg_upload_ok+"</span>"):a.find(".qq-upload-status-text-selector").html(""),a.find(".qq-upload-image").html(r),a.find(".qq-upload-file-selector").html(l+d+'<input type="hidden" value="'+i.data.newpath+'" /><span class="fname">'+c+"</span>"+s),a.find(".qq-upload-size-selector").html(i.data.filesize),i.data.filetype&&a.find(".qq-upload-file-type").html(i.data.filetype),i.data.creator&&a.find(".qq-upload-file-creator").html(i.data.creator),null!=i.data.downloads&&a.find(".qq-upload-downloads").html(i.data.downloads),i.data.owner&&a.find(".fsowner").html(i.data.owner),i.data.group&&a.find(".fsgroup").html(i.data.group),i.data.file_date&&a.find(".fsdate").html(i.data.file_date),i.data.file_actions&&a.find(".actions").html(i.data.file_actions),jQuery("#evo_multi_file_selector").length&&jQuery("#evo_multi_file_selector").show();else if("rename"==i.data.status){var u='<span class="orange">'+config.msg_upload_confict+"</span>";"default"==config.status_conflict_place?a.find(".qq-upload-status-text-selector").html(u):a.find(".qq-upload-status-text-selector").html(""),a.find(".qq-upload-image").html(i.data.file),a.find(".qq-upload-image-selector").append(htmlspecialchars_decode(i.data.file)),a.find(".qq-upload-file-selector").html(l+d+'<input type="hidden" value="'+i.data.newpath+'" /><span class="fname">'+c+"</span>"+("before_button"==config.status_conflict_place?" - "+u:"")+' - <a href="#" class="'+config.button_class+' roundbutton_text_noicon qq-conflict-replace" old="'+i.data.old_rootrelpath+'" new="'+i.data.new_rootrelpath+'"><div>'+config.msg_replace_file+'</div><div style="display:none">'+config.msg_revert+"</div></a>"+s);var f=jQuery("#"+config.fieldset_prefix+config.table_id+' input[type=hidden][value="'+i.data.oldpath+'"]');0<f.length&&f.parent().append(' <span class="orange">'+config.msg_old_file+"</span>")}"link"==_&&(a.find(".qq-upload-link-id").html('<span data-order="'+i.data.link_order+'">'+i.data.link_ID+"</span>"),a.find(".qq-upload-image").html(i.data.link_preview),a.find(".qq-upload-link-actions").prepend(i.data.link_actions),void 0!==i.data.link_position&&a.find(".qq-upload-link-position").html(i.data.link_position)),init_colorbox(a.find('.qq-upload-image a[rel^="lightbox"]')),evo_link_sort_list(config.fieldset_prefix)}}else jQuery(window.dndb[config.fieldset_prefix+"uploader"].getItemByFileId(e)).append(r),null==i.data&&""!=i&&jQuery(window.dndb[config.fieldset_prefix+"uploader"].getItemByFileId(e)).append(i);if(config.resize_frame&&(window.dndb.update_iframe_height(config.fieldset_prefix),jQuery(document).on("load","#"+config.fieldset_prefix+config.table_id+" img",function(){window.dndb.update_iframe_height(config.fieldset_preifx)})),jQuery(o).hasClass("link_attachment_dropzone"))switch(i.data.filetype){case"image":case"video":case"audio":textarea_wrap_selection(o,"["+i.data.filetype+":"+i.data.link_ID+"]","",0)}},onCancel:function(e,t){"table"==config.list_style&&setTimeout(function(){var e=jQuery("#"+config.fieldset_prefix+config.table_id+" .filelist_tbody");if(!e.find("tr").length){var t=config.no_results;e.append(t)}},10)}}}),jQuery("div.qq-upload-button-selector > div, div.qq-upload-drop-area > div").html(button_text),config.resize_frame&&(window.dndb.update_iframe_height=function(e){var t=jQuery("#"+e+config.table_id).height();jQuery("#"+e+"attachments_fieldset_wrapper").css({height:t,"max-height":t})}),"table"==config.list_style&&jQuery(document).on("click","#"+config.fieldset_prefix+config.table_id+" .qq-conflict-replace",function(){var _=jQuery(this),l=_.children("div:first").is(":visible"),e=_.attr("old"),d=jQuery("#"+config.fieldset_prefix+config.table_id+' input[type=hidden][value="'+e+'"]'),s=0<d.length;_.hide();var c=_.parent().parent().children("td");return s&&(c=c.add(d.parent().parent().children("td"))),c.css("background","#FFFF00"),c.find("span.error").remove(),jQuery.ajax({type:"POST",url:htsrv_url+"async.php",data:{action:"conflict_files",fileroot_ID:config.fileroot_ID,path:config.path,oldfile:e.replace(/^(.+[\/:])?([^\/]+)$/,"$2"),newfile:_.attr("new").replace(/^(.+[\/:])?([^\/]+)$/,"$2"),format:config.conflict_file_format,crumb_conflictfiles:config.crumb_conflictfiles},success:function(e){var t=jQuery.parseJSON(e);if(void 0===t.error){_.show();var i=_.parent().find("span.fname");if(l?(_.children("div:first").hide(),_.children("div:last").show()):(_.children("div:first").show(),_.children("div:last").hide()),s){var n=d.parent().find("span.fname"),o=n.html();n.html(i.html()),i.html(o);var r=n.prev();if(0!=r.length&&"A"==r.get(0).tagName||(r=n.parent().prev()),0<r.length&&"A"==r.get(0).tagName){var a=r.attr("href");r.attr("href",i.prev().attr("href")),i.prev().attr("href",a)}}else i.html(l?t.old:t.new)}else _.show(),_.parent().append('<span class="error"> - '+t.error+"</span>");c.css("background","")}}),!1}),config.display_support_msg&&document.write('<p class="note">'+file_uploader_note_text+"</p>")};for(var evo_init_dragdrop_button_configs=Object.keys(evo_init_dragdrop_button_config),i=0;i<evo_init_dragdrop_button_configs.length;i++)window.init_uploader(evo_init_dragdrop_button_configs[i])}});