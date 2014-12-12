$(document).ready(function () {
    $(".list-params li").click(select_param);
    $(".tabs label:first").click()

    $(".add").click(function () {
        if (confirm("Вы уверены ?")) {
            $.ajax({
                type: "POST",
                data: "&action=addnew",
                cache: 'false',
                success: function (html) {
                    var obj = jQuery.parseJSON(html);
                    if (obj.operation == 1) {

                        $(".list-params").html(obj.list_params)
                        $(".list-params li").click(select_param);
                    }
                    else {
                        alert("error");
                    }
                }});
        }
    })
    $(".rem").click(function () {

        if (confirm("Вы уверены ? Отменить удаление не удасться")) {

            $.ajax({
                type: "POST",
                data: "&action=rem&id=" + $(".list-params .active").attr("value"),
                cache: 'false',
                success: function (html) {
                    var obj = jQuery.parseJSON(html);
                    if (obj.operation == 1) {
                        $(".list-params").html(obj.list_params)
                        $(".list-params li").click(select_param);
                        $(".tabs_cont").html('');
                    }
                    else {
                        alert("error");
                    }
                }});

        }
    })

    $(".save").click(function () {
        if ($("#richtext").prop('checked')) {
            $(".tabs_cont div textarea").each(function () {
                $(this).text(tinyMCE.get($(this).attr("name")).getContent());
            })
        }

        var data = $('.tabs_cont').serialize();
        $.ajax({
            type: "POST",
            data: "&action=save&richtext=" + $("#richtext").prop('checked') + "&" + data,

            cache: 'false',
            success: function (html) {
                var obj = jQuery.parseJSON(html);
                if (obj.operation) {
                    $(".list-params .active").html(obj.button);
                    alert('ok');
                }

                else {
                    alert("error");
                }

            }});
    })

    $("#richtext").change(function () {
        if ($(this).prop('checked')) {
            editorinit();
        } else {
            // remove tinyMCE
            $(".tabs_cont div textarea").each(function () {
                $(this).text(tinyMCE.get($(this).attr("name")).getContent()).attr('style', '');
            })

            $(".tabs_cont div span").html('')
        }

    })

})


function select_param() {
    // get data
    $(".list-params li").removeClass("active");
    $(this).addClass("active")
    $.ajax({
        type: "POST",
        data: "&action=getdata&id=" + $(this).attr('value'),
        cache: 'false',
        success: function (html) {
            var obj = jQuery.parseJSON(html);
            if (obj.operation == 1) {
                $(".tabs_cont").html(obj.descr);
                $(".tabs label:first").click()
                $("button").css('display', '')
                if (obj.richtext == 1) {
                    $('#richtext').attr('checked', 'checked');
                    editorinit();
                }
                else {
                    $("#richtext").removeAttr('checked')
                }
                $(".edit-area").attr('style', '');
            }
            else {
                alert("error");
            }
        }});

}

function editorinit() {
    tinyMCE.init({
        theme: 'advanced',
        skin: 'default',
        skin_variant: '',
        mode: 'textareas',
        elements: 'ta',
        width: "100%",
        height: '500',
        language: 'ru',
        element_format: 'xhtml',
        schema: 'html4',
        paste_text_use_dialog: true,
        document_base_url: $("base").attr("href"),
        relative_urls: true,
        remove_script_host: true,
        convert_urls: true,
        force_br_newlines: false,
        force_p_newlines: true,
        forced_root_block: 'p',
        valid_elements: mce_valid_elements,
        popup_css_add: 'assets/plugins/tinymce/style/popup_add.css',
        theme_advanced_source_editor_height: 500,
        accessibility_warnings: false,
        theme_advanced_toolbar_location: 'top',
        theme_advanced_statusbar_location: 'bottom',
        theme_advanced_toolbar_align: 'ltr',
        theme_advanced_font_sizes: '80%,90%,100%,120%,140%,160%,180%,220%,260%,320%,400%,500%,700%',
        content_css: 'assets/plugins/tinymce/style/content.css',
        formats: {
            alignleft: {selector: 'p,h1,h2,h3,h4,h5,h6,td,th,div,ul,ol,li,table,img', classes: 'justifyleft'},
            alignright: {selector: 'p,h1,h2,h3,h4,h5,h6,td,th,div,ul,ol,li,table,img', classes: 'justifyright'},
            alignfull: {selector: 'p,h1,h2,h3,h4,h5,h6,td,th,div,ul,ol,li,table,img', classes: 'justifyfull'}
        },
        apply_source_formatting: true,
        remove_linebreaks: false,
        convert_fonts_to_spans: true,
        plugins: 'visualblocks,autolink,inlinepopups,save,advlist,style,fullscreen,advimage,paste,advlink,media,contextmenu,table',
        theme_advanced_buttons1: 'undo,redo,|,bold,forecolor,backcolor,strikethrough,formatselect,fontsizeselect,pastetext,pasteword,code,|,fullscreen,help',
        theme_advanced_buttons2: 'link,unlink,anchor,|,justifyleft,justifycenter,justifyright,|,bullist,numlist,|,blockquote,outdent,indent,|,table,hr,|,visualblocks,styleprops,removeformat',
        theme_advanced_buttons3: '',
        theme_advanced_buttons4: '',
        theme_advanced_resize_horizontal: false,
        external_link_list_url: 'assets/plugins/tinymce/js/tinymce.linklist.php',
        template_external_list_url: 'assets/plugins/tinymce/js/get_template.php',
        template_popup_width: 550,
        template_popup_height: 350,
        theme_advanced_blockformats: 'p,h1,h2,h3,h4,h5,h6,div,blockquote,code,pre',
        theme_advanced_styles: 'left=justifyleft;right=justifyright',
        theme_advanced_disable: '',
        theme_advanced_resizing: true,
        fullscreen_settings: {
            theme_advanced_buttons1_add_before: 'save'
        },
        plugin_insertdate_dateFormat: '%d-%m-%Y',
        plugin_insertdate_timeFormat: '%H:%M:%S',
        entity_encoding: 'named',
        file_browser_callback: 'mceOpenServerBrowser',
        paste_text_sticky: true,
        setup: function (ed) {
            ed.onPostProcess.add(function (ed, o) {
                // State get is set when contents is extracted from editor
                if (o.get) {
                    o.content = o.content.replace('<p>{{', '{{');
                    o.content = o.content.replace('}}</p>', '}}');
                    o.content = o.content.replace(/<p>\[([\[\!\~\^])/g, '[$1');
                    o.content = o.content.replace(/([\]\!\~\^])\]<\/p>/g, '$1]');
                }
            });
        },
        onchange_callback: false,
        valid_elements: "*[*]"
    })
}