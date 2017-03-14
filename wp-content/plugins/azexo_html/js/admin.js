(function($) {
    $(function() {
        function makeid() {
            var text = "";
            var possible = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789";
            for (var i = 0; i < 5; i++)
                text += possible.charAt(Math.floor(Math.random() * possible.length));
            return text;
        }
        function remove_azh_meta() {
            if ($('body.post-type-page').length) {
                $('#list-table input[value="azh"]').each(function(){
                    $(this).parent().find('input.deletemeta').click();
                });                
            }
        }
        function add_azh_meta() {
            if ($('body.post-type-page').length) {
                if ($('#list-table input[value="azh"]').closest('tr[id]:visible').length == 0) {
                    $('#metakeyinput').val('azh');
                    $('#metavalue').val('azh');
                    $('#newmeta-submit').click();
                }
            }
        }
        azh.azh_show_hide = function() {
            setTimeout(function() {
                if ($('#wp-content-wrap, #wp-customize-posts-content-wrap').is('.tmce-active')) {
                    $('#wp-content-editor-container #content, #wp-customize-posts-content-editor-container #customize-posts-content').show();
                }
            }, 0);
            setTimeout(function() {
                if ($('#wp-content-wrap, #wp-customize-posts-content-wrap').is('.html-active')) {
                    if (azh.edit) {
                        $('#wp-content-editor-container .azh-switcher, #wp-customize-posts-content-editor-container .azh-switcher').css('left', '0');
                        //$('#wp-content-editor-container .azh-switcher, #wp-customize-posts-content-editor-container .azh-switcher').show();
                        $('#wp-content-editor-container .azexo-html-editor, #wp-customize-posts-content-editor-container .azexo-html-editor').show();
                        $('#wp-content-editor-container #content, #wp-customize-posts-content-editor-container #customize-posts-content').hide();
                        $('#ed_toolbar, #qt_customize-posts-content_toolbar').hide();
                        $('#wp-content-media-buttons, #wp-customize-posts-content-media-buttons').hide();
                        add_azh_meta();
                    } else {
                        $('#wp-content-editor-container .azh-switcher, #wp-customize-posts-content-editor-container .azh-switcher').css('left', '110px');
                        $('#wp-content-editor-container .azexo-html-editor, #wp-customize-posts-content-editor-container .azexo-html-editor').hide();
                        $('#wp-content-editor-container #content, #wp-customize-posts-content-editor-container #customize-posts-content').show();
                        $('#ed_toolbar, #qt_customize-posts-content_toolbar').show();
                        $('#wp-content-media-buttons, #wp-customize-posts-content-media-buttons').show();
                        remove_azh_meta();
                    }
                } else {
                    $('#wp-content-editor-container .azh-switcher, #wp-customize-posts-content-editor-container .azh-switcher').css('left', '110px');
                    $('#wp-content-editor-container .azexo-html-editor, #wp-customize-posts-content-editor-container .azexo-html-editor').hide();
                    $('#wp-content-editor-container .mce-tinymce, #wp-customize-posts-content-editor-container .mce-tinymce').show();
                    $('#ed_toolbar, #qt_customize-posts-content_toolbar').show();
                    $('#wp-content-media-buttons, #wp-customize-posts-content-media-buttons').show();
                    remove_azh_meta();
                }
            }, 0);
        }
        function focus(target, duration) {
            var focus_padding = 0;
            if ($('.az-focus').length == 0) {
                $('<div class="az-focus"><div class="top"></div><div class="right"></div><div class="bottom"></div><div class="left"></div></div>').appendTo('body').on('click', function() {
                    $('.az-focus').remove();
                    return false;
                });
                $('.az-focus .top, .az-focus .right, .az-focus .bottom, .az-focus .left').css({
                    'z-index': '999999',
                    'position': 'fixed',
                    'background-color': 'black',
                    'opacity': '0.4'
                });
            }
            var top = $('.az-focus .top');
            var right = $('.az-focus .right');
            var bottom = $('.az-focus .bottom');
            var left = $('.az-focus .left');
            var target_top = $(target).offset()['top'] - focus_padding - $('body').scrollTop();
            var target_left = $(target).offset()['left'] - focus_padding;
            var target_width = $(target).outerWidth() + focus_padding * 2;
            var target_height = $(target).outerHeight() + focus_padding * 2;
            $(top).stop().animate({
                top: 0,
                left: 0,
                right: 0,
                height: target_top,
            }, duration, 'linear');
            $(right).stop().animate({
                top: target_top,
                left: target_left + target_width,
                right: 0,
                height: target_height,
            }, duration, 'linear');
            $(bottom).stop().animate({
                top: target_top + target_height,
                left: 0,
                right: 0,
                bottom: 0,
            }, duration, 'linear');
            $(left).stop().animate({
                top: target_top,
                left: 0,
                height: target_height,
                width: target_left,
            }, duration, 'linear', function() {
            });
            if (duration > 0) {
                setTimeout(function() {
                    $(window).on('scroll.focus', function() {
                        $('.az-focus').remove();
                        $(window).off('scroll.focus');
                    });
                    $('.az-focus .top, .az-focus .right, .az-focus .bottom, .az-focus .left').stop().animate({
                        'opacity': '0'
                    }, duration * 10);
                    setTimeout(function() {
                        $(window).trigger('scroll');
                    }, duration * 10);
                }, duration);
            }
        }
        window.azh = $.extend({}, window.azh);
        azh.icon_select_dialog = function(callback, type) {
            function show_icons() {
                var keyword = $(search).val().toLowerCase();
                $(icons).empty();
                for (var key in azh.icons[type]) {
                    if (azh.icons[type][key].toLowerCase().indexOf(keyword) >= 0) {
                        $('<span class="' + key + '"></span>').appendTo(icons).on('click', {icon: icon}, function(event) {
                            callback.call(event.data.icon, $(this).attr('class'));
                        });
                    }
                }
            }
            var icon = this;
            var icon_class = '';
            var dialog = $('<div class="azh-icon-select-dialog"></div>').appendTo('body');
            var controls = $('<div class="type-search"></div>').appendTo(dialog);
            var search = $('<input type="text"/>').appendTo(controls).on('change keyup', function() {
                show_icons();
            });
            var icons = $('<div class="azh-icons"></div>').appendTo(dialog);
            show_icons();
            return dialog;
        };
        azh.open_icon_select_dialog = function(event, callback) {
            function show_icons() {
                var type = $(types).find('option:selected').val();
                var keyword = $(search).val().toLowerCase();
                $(icons).empty();
                for (var key in azh.icons[type]) {
                    if (azh.icons[type][key].toLowerCase().indexOf(keyword) >= 0) {
                        $('<span class="' + key + '"></span>').appendTo(icons).on('click', {icon: icon}, function(event) {
                            $(dialog).remove();
                            $(document).off('click.azh-dialog');
                            callback.call(event.data.icon, $(this).attr('class'));
                        });
                    }
                }
            }
            var icon = this;
            var icon_class = '';
            $('.azh-icon-select-dialog').remove();
            var dialog = $('<div class="azh-icon-select-dialog"></div>').appendTo('body');
            var controls = $('<div class="type-search"></div>').appendTo(dialog);
            var types = $('<select></select>').appendTo(controls).on('change', function() {
                show_icons();
            });
            var search = $('<input type="text"/>').appendTo(controls).on('change keyup', function() {
                show_icons();
            });
            for (var type in azh.icons) {
                var option = $('<option value="' + type + '">' + type + '</option>').appendTo(types);
            }
            var icons = $('<div class="azh-icons"></div>').appendTo(dialog);
            show_icons();
            $(document).on('click.azh-dialog', {icon: icon}, function(event) {
                if (!$(event.target).closest('.azh-icon-select-dialog').length) {
                    $(dialog).remove();
                    $(document).off('click.azh-dialog');
                    callback.call(event.data.icon, icon_class);
                }
            })
            $(dialog).css('top', event.clientY);
            $(dialog).css('left', event.clientX);
            event.stopPropagation();
        };
        azh.get_image_url = function(id, callback) {
            var attachment = wp.media.model.Attachment.get(id);
            attachment.fetch().done(function() {
                callback(attachment.attributes.url);
            });
            ;
        };
        azh.open_image_select_dialog = function(event, callback, multiple) {
            var image = this;
            multiple = (typeof multiple == 'undefined' ? false : multiple);
            // check for media manager instance
            if (wp.media.frames.azh_frame) {
                wp.media.frames.azh_frame.image = image;
                wp.media.frames.azh_frame.callback = callback;
                wp.media.frames.azh_frame.options.multiple = multiple;
                wp.media.frames.azh_frame.open();
                return;
            }
            // configuration of the media manager new instance            
            wp.media.frames.azh_frame = wp.media({
                multiple: multiple,
                library: {
                    type: 'image'
                }
            });
            wp.media.frames.azh_frame.image = image;
            wp.media.frames.azh_frame.callback = callback;
            // Function used for the image selection and media manager closing            
            var azh_media_set_image = function() {
                var selection = wp.media.frames.azh_frame.state().get('selection');
                // no selection
                if (!selection) {
                    return;
                }
                // iterate through selected elements
                if (wp.media.frames.azh_frame.options.multiple) {
                    wp.media.frames.azh_frame.callback.call(wp.media.frames.azh_frame.image, selection.map(function(attachment) {
                        return {url: attachment.attributes.url, id: attachment.attributes.id};
                    }));
                } else {
                    selection.each(function(attachment) {
                        wp.media.frames.azh_frame.callback.call(wp.media.frames.azh_frame.image, attachment.attributes.url, attachment.attributes.id);
                    });
                }
            };
            // closing event for media manger
            wp.media.frames.azh_frame.on('close', function() {
            });
            // image selection event
            wp.media.frames.azh_frame.on('select', azh_media_set_image);
            // showing media manager
            wp.media.frames.azh_frame.open();
        }
        azh.open_link_select_dialog = function(event, callback, url, target, text) {
            url = (typeof url == 'undefined' ? '' : url);
            target = (typeof target == 'undefined' ? '' : target);
            text = (typeof text == 'undefined' ? '' : text);
            var link = this;
            if ($(link).data('url')) {
                url = $(link).data('url');
            }
            var original = wpLink.htmlUpdate;
            $(document).on('wplink-close.azh', function() {
                wpLink.htmlUpdate = original;
                $('#wp-link-cancel').off('click.azh');
                $(input).remove();
                $(document).off('wplink-close.azh');
            });
            wpLink.htmlUpdate = function() {
                var attrs = wpLink.getAttrs();
                if (!attrs.href) {
                    return;
                }
                callback.call(link, attrs.href, attrs.target, $('#wp-link-text').val());
                wpLink.close('noReset');
            };
            $('#wp-link-cancel').on('click.azh', function(event) {
                wpLink.close('noReset');
                event.preventDefault ? event.preventDefault() : event.returnValue = false;
                event.stopPropagation();
                return false;
            });
            window.wpActiveEditor = true;
            var id = makeid();
            var input = $('<input id="' + id + '" />').appendTo('body').hide();
            wpLink.open(id);
            $('#wp-link-url').val(url);
            $('#wp-link-target').val(target);
            $('#wp-link-text').val(text);
        }
        azh.get_rich_text_editor = function(textarea) {
            function init_textarea_html($element) {
                var $wp_link = $("#wp-link");
                $wp_link.parent().hasClass("wp-dialog") && $wp_link.wpdialog("destroy");
                $element.val($(textarea).val());
                try {
                    _.isUndefined(tinyMCEPreInit.qtInit[textfield_id]) && (window.tinyMCEPreInit.qtInit[textfield_id] = _.extend({}, window.tinyMCEPreInit.qtInit[window.wpActiveEditor], {
                        id: textfield_id
                    }));
                    window.tinyMCEPreInit && window.tinyMCEPreInit.mceInit[window.wpActiveEditor] && (window.tinyMCEPreInit.mceInit[textfield_id] = _.extend({}, window.tinyMCEPreInit.mceInit[window.wpActiveEditor], {
                        resize: "vertical",
                        height: 200,
                        id: textfield_id,
                        setup: function(ed) {
                            "undefined" != typeof ed.on ? ed.on("init", function(ed) {
                                ed.target.focus(), window.wpActiveEditor = textfield_id
                            }) : ed.onInit.add(function(ed) {
                                ed.focus(), window.wpActiveEditor = textfield_id
                            })
                            ed.on('change', function(e) {
                                $(textarea).val(ed.getContent());
                                $(textarea).trigger('change');
                            });
                        }
                    }), window.tinyMCEPreInit.mceInit[textfield_id].plugins = window.tinyMCEPreInit.mceInit[textfield_id].plugins.replace(/,?wpfullscreen/, ""), window.tinyMCEPreInit.mceInit[textfield_id].wp_autoresize_on = !1);
                    quicktags(window.tinyMCEPreInit.qtInit[textfield_id]);
                    QTags._buttonsInit();
                    window.tinymce && (window.switchEditors && window.switchEditors.go(textfield_id, "tmce"), "4" === tinymce.majorVersion && tinymce.execCommand("mceAddEditor", !0, textfield_id));
                    window.wpActiveEditor = textfield_id
                    setUserSetting('editor', 'html');
                } catch (e) {
                }
            }
            var textfield_id = makeid();
            $.ajax({
                type: 'POST',
                url: window.ajaxurl,
                data: {
                    action: 'azh_get_wp_editor',
                    id: textfield_id,
                },
                cache: false,
            }).done(function(data) {
                $(textarea).hide();
                $(textarea).after(data);
                init_textarea_html($('#' + textfield_id));
            });
        }
        if ($('#wp-content-editor-container #content').length) {
            var edit = true;
            if ($('body.post-type-azh_widget').length == 0) {
                if ($('#list-table input[value="azh"]').length == 0) {
                    edit = false;
                }
            }
            if ($('#azh').length) {
                edit = true;
            }
            azh.init($('#wp-content-editor-container #content'), edit);
            azh.azh_show_hide();
            if ($('#wp-content-wrap, #wp-customize-posts-content-wrap').is('.tmce-active')) {
                $('.azh-switcher').text(azh.switch_to_customizer);
            }
            $('#wp-content-editor-container .azh-switcher').on('click', function() {
                if ($('#wp-content-wrap, #wp-customize-posts-content-wrap').is('.tmce-active')) {
                    $('#content-html').click();
                }
                setTimeout(function() {
                    azh.azh_show_hide();
                }, 0);
            });
            $('#content-tmce').on('click', azh.azh_show_hide);
            $('#content-html').on('click', azh.azh_show_hide);
        }
        azh.parse_query_string = function(a) {
            if (a == "")
                return {};
            var b = {};
            for (var i = 0; i < a.length; ++i)
            {
                var p = a[i].split('=');
                if (p.length != 2)
                    continue;
                b[p[0]] = decodeURIComponent(p[1].replace(/\+/g, " "));
            }
            return b;
        };
        $.QueryString = azh.parse_query_string(window.location.search.substr(1).split('&'));
        if ('post' in $.QueryString && 'action' in $.QueryString && 'section' in $.QueryString) {
            $(document).one('azh-store', function() {
                setTimeout(function() {
                    $('.azh-group-title:contains("' + $.QueryString['section'] + '")').each(function() {
                        if ($(this).text() == $.QueryString['section']) {
                            var section = $(this).closest('.azh-section');
                            if ($(section).length) {
                                $('body, html').stop().animate({
                                    'scrollTop': $(section).offset().top - $(window).height() / 2 + $(section).height() / 2
                                }, 300);
                                setTimeout(function() {
                                    focus('body', 0);
                                    setTimeout(function() {
                                        focus(section, 300);
                                    }, 0);
                                }, 300);
                            }
                        }
                    });
                }, 100);
            });
        }
    });
})(window.jQuery);