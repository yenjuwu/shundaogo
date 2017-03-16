!function($) {
    "use strict";
    var wp_shortcode = {
        // ### Find the next matching shortcode
        //
        // Given a shortcode `tag`, a block of `text`, and an optional starting
        // `index`, returns the next matching shortcode or `undefined`.
        //
        // Shortcodes are formatted as an object that contains the match
        // `content`, the matching `index`, and the parsed `shortcode` object.
        next: function(tag, text, index) {
            var re = wp_shortcode.regexp(tag),
                    match, result;

            re.lastIndex = index || 0;
            match = re.exec(text);

            if (!match) {
                return;
            }

            // If we matched an escaped shortcode, try again.
            if ('[' === match[1] && ']' === match[7]) {
                return wp_shortcode.next(tag, text, re.lastIndex);
            }

            result = {
                index: match.index,
                content: match[0],
                shortcode: wp_shortcode.fromMatch(match)
            };

            // If we matched a leading `[`, strip it from the match
            // and increment the index accordingly.
            if (match[1]) {
                result.content = result.content.slice(1);
                result.index++;
            }

            // If we matched a trailing `]`, strip it from the match.
            if (match[7]) {
                result.content = result.content.slice(0, -1);
            }

            return result;
        },
        // ### Replace matching shortcodes in a block of text
        //
        // Accepts a shortcode `tag`, content `text` to scan, and a `callback`
        // to process the shortcode matches and return a replacement string.
        // Returns the `text` with all shortcodes replaced.
        //
        // Shortcode matches are objects that contain the shortcode `tag`,
        // a shortcode `attrs` object, the `content` between shortcode tags,
        // and a boolean flag to indicate if the match was a `single` tag.
        replace: function(tag, text, callback) {
            return text.replace(wp_shortcode.regexp(tag), function(match, left, tag, attrs, slash, content, closing, right) {
                // If both extra brackets exist, the shortcode has been
                // properly escaped.
                if (left === '[' && right === ']') {
                    return match;
                }

                // Create the match object and pass it through the callback.
                var result = callback(wp_shortcode.fromMatch(arguments));

                // Make sure to return any of the extra brackets if they
                // weren't used to escape the shortcode.
                return result ? left + result + right : match;
            });
        },
        // ### Generate a string from shortcode parameters
        //
        // Creates a `wp_shortcode` instance and returns a string.
        //
        // Accepts the same `options` as the `wp_shortcode()` constructor,
        // containing a `tag` string, a string or object of `attrs`, a boolean
        // indicating whether to format the shortcode using a `single` tag, and a
        // `content` string.
        string: function(options) {
            return new wp_shortcode(options).string();
        },
        // ### Generate a RegExp to identify a shortcode
        //
        // The base regex is functionally equivalent to the one found in
        // `get_shortcode_regex()` in `wp-includes/shortcodes.php`.
        //
        // Capture groups:
        //
        // 1. An extra `[` to allow for escaping shortcodes with double `[[]]`
        // 2. The shortcode name
        // 3. The shortcode argument list
        // 4. The self closing `/`
        // 5. The content of a shortcode when it wraps some content.
        // 6. The closing tag.
        // 7. An extra `]` to allow for escaping shortcodes with double `[[]]`
        regexp: _.memoize(function(tag) {
            return new RegExp('\\[(\\[?)(' + tag + ')(?![\\w-])([^\\]\\/]*(?:\\/(?!\\])[^\\]\\/]*)*?)(?:(\\/)\\]|\\](?:([^\\[]*(?:\\[(?!\\/\\2\\])[^\\[]*)*)(\\[\\/\\2\\]))?)(\\]?)', 'g');
        }),
        // ### Parse shortcode attributes
        //
        // Shortcodes accept many types of attributes. These can chiefly be
        // divided into named and numeric attributes:
        //
        // Named attributes are assigned on a key/value basis, while numeric
        // attributes are treated as an array.
        //
        // Named attributes can be formatted as either `name="value"`,
        // `name='value'`, or `name=value`. Numeric attributes can be formatted
        // as `"value"` or just `value`.
        attrs: _.memoize(function(text) {
            var named = {},
                    numeric = [],
                    pattern, match;

            // This regular expression is reused from `shortcode_parse_atts()`
            // in `wp-includes/shortcodes.php`.
            //
            // Capture groups:
            //
            // 1. An attribute name, that corresponds to...
            // 2. a value in double quotes.
            // 3. An attribute name, that corresponds to...
            // 4. a value in single quotes.
            // 5. An attribute name, that corresponds to...
            // 6. an unquoted value.
            // 7. A numeric attribute in double quotes.
            // 8. An unquoted numeric attribute.
            pattern = /([\w-]+)\s*=\s*"([^"]*)"(?:\s|$)|([\w-]+)\s*=\s*'([^']*)'(?:\s|$)|([\w-]+)\s*=\s*([^\s'"]+)(?:\s|$)|"([^"]*)"(?:\s|$)|(\S+)(?:\s|$)/g;

            // Map zero-width spaces to actual spaces.
            text = text.replace(/[\u00a0\u200b]/g, ' ');

            // Match and normalize attributes.
            while ((match = pattern.exec(text))) {
                if (match[1]) {
                    named[ match[1].toLowerCase() ] = match[2];
                } else if (match[3]) {
                    named[ match[3].toLowerCase() ] = match[4];
                } else if (match[5]) {
                    named[ match[5].toLowerCase() ] = match[6];
                } else if (match[7]) {
                    numeric.push(match[7]);
                } else if (match[8]) {
                    numeric.push(match[8]);
                }
            }

            return {
                named: named,
                numeric: numeric
            };
        }),
        // ### Generate a Shortcode Object from a RegExp match
        // Accepts a `match` object from calling `regexp.exec()` on a `RegExp`
        // generated by `wp_shortcode.regexp()`. `match` can also be set to the
        // `arguments` from a callback passed to `regexp.replace()`.
        fromMatch: function(match) {
            var type;

            if (match[4]) {
                type = 'self-closing';
            } else if (match[6]) {
                type = 'closed';
            } else {
                type = 'single';
            }

            return new wp_shortcode({
                tag: match[2],
                attrs: match[3],
                type: type,
                content: match[5]
            });
        }
    };
    // Shortcode Objects
    // -----------------
    //
    // Shortcode objects are generated automatically when using the main
    // `wp_shortcode` methods: `next()`, `replace()`, and `string()`.
    //
    // To access a raw representation of a shortcode, pass an `options` object,
    // containing a `tag` string, a string or object of `attrs`, a string
    // indicating the `type` of the shortcode ('single', 'self-closing', or
    // 'closed'), and a `content` string.
    wp_shortcode = _.extend(function(options) {
        _.extend(this, _.pick(options || {}, 'tag', 'attrs', 'type', 'content'));

        var attrs = this.attrs;

        // Ensure we have a correctly formatted `attrs` object.
        this.attrs = {
            named: {},
            numeric: []
        };

        if (!attrs) {
            return;
        }

        // Parse a string of attributes.
        if (_.isString(attrs)) {
            this.attrs = wp_shortcode.attrs(attrs);

            // Identify a correctly formatted `attrs` object.
        } else if (_.isEqual(_.keys(attrs), ['named', 'numeric'])) {
            this.attrs = attrs;

            // Handle a flat object of attributes.
        } else {
            _.each(options.attrs, function(value, key) {
                this.set(key, value);
            }, this);
        }
    }, wp_shortcode);
    _.extend(wp_shortcode.prototype, {
        // ### Get a shortcode attribute
        //
        // Automatically detects whether `attr` is named or numeric and routes
        // it accordingly.
        get: function(attr) {
            return this.attrs[ _.isNumber(attr) ? 'numeric' : 'named' ][ attr ];
        },
        // ### Set a shortcode attribute
        //
        // Automatically detects whether `attr` is named or numeric and routes
        // it accordingly.
        set: function(attr, value) {
            this.attrs[ _.isNumber(attr) ? 'numeric' : 'named' ][ attr ] = value;
            return this;
        },
        // ### Transform the shortcode match into a string
        string: function() {
            var text = '[' + this.tag;

            _.each(this.attrs.numeric, function(value) {
                if (/\s/.test(value)) {
                    text += ' "' + value + '"';
                } else {
                    text += ' ' + value;
                }
            });

            _.each(this.attrs.named, function(value, name) {
                text += ' ' + name + '="' + value + '"';
            });

            // If the tag is marked as `single` or `self-closing`, close the
            // tag and ignore any additional content.
            if ('single' === this.type) {
                return text + ']';
            } else if ('self-closing' === this.type) {
                return text + ' /]';
            }

            // Complete the opening tag.
            text += ']';

            if (this.content) {
                text += this.content;
            }

            // Add the closing tag.
            return text + '[/' + this.tag + ']';
        }
    });
    function makeid() {
        var text = "";
        var possible = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789";
        for (var i = 0; i < 5; i++)
            text += possible.charAt(Math.floor(Math.random() * possible.length));
        return text;
    }
    function default_get_code() {
        if ($(this).data('get_code')) {
            return $(this).data('get_code').call(this);
        } else {
            var code = '';
            $(this).children().each(function() {
                code = code + default_get_code.call(this);
            });
            return code;
        }
    }
    function htmlDecode(value) {
        return $("<textarea/>").html(value).text();
    }
    function htmlEncode(value) {
        return $('<textarea/>').text(value).html();
    }
    function remove_shortcode_instances(code) {
        if (Object.keys(azh.shortcodes).length) {
            var tags = Object.keys(azh.shortcodes).join('|'),
                    reg = wp_shortcode.regexp(tags),
                    matches = code.match(reg);
            var match = null;
            code = code.replace(reg, function(str, open, name, args, self_closing, content, closing, close, offset, s) {
                var id = name + '-' + makeid();
                azh.shortcode_instances[id] = {
                    raw: str,
                    atts_raw: wp_shortcode.attrs(args),
                    sub_content: content,
                    shortcode: name
                };
                return id;
            });
        }
        return code;
    }
    function make_line(key, indent) {
        var line = $('<div class="azh-line" data-number="' + key + '"><span class="indent">' + Array(indent).join("&nbsp;") + '</span></div>');
        $(line).data('indent', indent);
        $(line).data('get_code', function() {
            var code = '';
            $(this).children().each(function() {
                code = code + default_get_code.call(this);
            });
            return Array($(this).data('indent')).join(' ') + code + "\n";
        });
        return line;
    }
    function make_text(code_line) {
        var text = $('<span class="azh-text"></span>').html(htmlEncode(code_line));
        $(text).data('code_line', code_line);
        $(text).data('get_code', function() {
            return $(this).data('code_line');
        });
        $(text).appendTo(this);
        return text;
    }
    function create_field(settings, value) {
        function set_image(field, url, id) {
            var preview = $(field).find('.azh-image-preview');
            $(preview).empty();
            $('<img src="' + url + '">').appendTo(preview);
            $(preview).data('id', id);
            $(field).trigger('change');
            $('<a href="#" class="remove"></a>').appendTo(preview).on('click', function(event) {
                $(preview).empty();
                $(preview).data('id', '');
                $(field).trigger('change');
                return false;
            });

        }
        function add_images(field, images) {
            var previews = $(field).find('.azh-images-preview');

            for (var i = 0; i < images.length; i++) {
                var preview = $('<div class="azh-image-preview"></div>').appendTo(previews);
                $('<img src="' + images[i]['url'] + '">').appendTo(preview);
                $(preview).data('id', images[i]['id']);
                (function(preview) {
                    $('<a href="#" class="remove"></a>').appendTo(preview).on('click', function(event) {
                        $(preview).remove();
                        $(field).trigger('change');
                        return false;
                    });
                })(preview);
            }

            $(previews).sortable();

            $(field).trigger('change');
        }
        var field = $('<p data-param-name="' + settings['param_name'] + '"></p>');
        $(field).data('settings', settings);
        settings['heading'] = (typeof settings['heading'] == 'undefined' ? '' : settings['heading']);

        if ('dependency' in settings) {
            setTimeout(function() {
                $('[data-param-name="' + settings['dependency']['element'] + '"]').on('change', function() {
                    if ($(this).css('display') == 'none') {
                        $(field).hide();
                        $(field).trigger('change');
                        return;
                    }
                    var value = $(this).data('get_value').call(this);
                    if ('is_empty' in settings['dependency']) {
                        if (value == '') {
                            $(field).show();
                        } else {
                            $(field).hide();
                        }
                    }
                    if ('not_empty' in settings['dependency']) {
                        if (value == '') {
                            $(field).hide();
                        } else {
                            $(field).show();
                        }
                    }
                    if ('value' in settings['dependency']) {
                        var variants = settings['dependency']['value'];
                        if (typeof variants == 'string') {
                            variants = [variants];
                        }
                        if (variants.indexOf(value) >= 0) {
                            $(field).show();
                        } else {
                            $(field).hide();
                        }
                    }
                    if ('value_not_equal_to' in settings['dependency']) {
                        var variants = settings['dependency']['value_not_equal_to'];
                        if (typeof variants == 'string') {
                            variants = [variants];
                        }
                        if (variants.indexOf(value) >= 0) {
                            $(field).hide();
                        } else {
                            $(field).show();
                        }
                    }
                    $(field).trigger('change');
                });
            }, 0);
        }

        switch (settings['type']) {
            case 'textfield':
                $(field).append('<label>' + settings['heading'] + '</label>');
                var textfield = $('<input type="text">').appendTo(field);
                if (value != '') {
                    $(textfield).val(value);
                } else {
                    $(textfield).val(settings['value']);
                }
                $(textfield).on('change', function() {
                    $(field).trigger('change');
                });
                $(field).data('get_value', function() {
                    return $(this).find('input[type="text"]').val();
                });
                break;
            case 'textarea':
            case 'textarea_html':
            case 'textarea_raw_html':
                $(field).append('<label>' + settings['heading'] + '</label>');
                var textarea = $('<textarea></textarea>').appendTo(field);
                if (value != '') {
                    $(textarea).val(value);
                } else {
                    $(textarea).val(settings['value']);
                }
                if (settings['type'] == 'textarea_html') {
                    azh.get_rich_text_editor(textarea);
                }
                if (settings['type'] == 'textarea_raw_html') {
                }
                $(textarea).on('change', function() {
                    $(field).trigger('change');
                });
                $(field).data('get_value', function() {
                    return $(this).find('textarea').val();
                });
                break;
            case 'dropdown':
                $(field).append('<label>' + settings['heading'] + '</label>');
                var select = $('<select></select>');
                if ($.isArray(settings['value'])) {
                    for (var i = 0; i < settings['value'].length; i++) {
                        $(select).append('<option value="' + settings['value'][i][0] + '" ' + (value == settings['value'][i][0] ? 'selected' : '') + '>' + settings['value'][i][1] + '</option>');
                    }
                } else {
                    for (var label in settings['value']) {
                        $(select).append('<option value="' + settings['value'][label] + '" ' + (value == settings['value'][label] ? 'selected' : '') + '>' + label + '</option>');
                    }
                }
                $(select).on('change', function() {
                    $(field).trigger('change');
                });
                $(field).data('get_value', function() {
                    return $(this).find('select option:selected').attr('value');
                });
                $(select).appendTo(field);
                break;
            case 'checkbox':
                var checkbox = $('<fieldset class="ui-widget ui-widget-content"><legend>' + settings['heading'] + '</legend></fieldset>').appendTo(field);
                var values = value.split(',');
                for (var label in settings['value']) {
                    var id = makeid();
                    $(checkbox).append('<input id="' + id + '" type="checkbox" ' + (values.indexOf(settings['value'][label]) >= 0 ? 'checked' : '') + ' value="' + settings['value'][label] + '">');
                    $(checkbox).on('change', function() {
                        $(field).trigger('change');
                    });
                    $(checkbox).append('<label for="' + id + '">' + label + '</label>');
                }
                $(field).data('get_value', function() {
                    var values = $.makeArray($(this).find('input[type="checkbox"]:checked')).map(function(item) {
                        return $(item).attr('value')
                    });
                    return values.join(',');
                });
                break;
            case 'param_group':
                var param_group = $('<fieldset class="ui-widget ui-widget-content"><legend>' + settings['heading'] + '</legend></fieldset>').appendTo(field);
                var table = $('<table></table>').appendTo(param_group);
                var values = JSON.parse(decodeURIComponent(settings['value']));
                if (value != '') {
                    values = JSON.parse(decodeURIComponent(value));
                }
                for (var i = 0; i < values.length; i++) {
                    var row = $('<tr></tr>').appendTo(table);
                    for (var j = 0; j < settings['params'].length; j++) {
                        var column = $('<td></td>');
                        $(column).append(create_field(settings['params'][j], (settings['params'][j]['param_name'] in values[i] ? values[i][settings['params'][j]['param_name']] : '')));
                        row.append(column);
                    }
                    $('<a href="#" class="button">' + azh.remove + '</a>').appendTo($('<td></td>').appendTo(row)).on('click', function() {
                        $(this).closest('tr').remove();
                        return false;
                    });
                }
                $('<a href="#" class="button">' + azh.add + '</a>').appendTo(param_group).on('click', function() {
                    var row = $('<tr></tr>').appendTo(table);
                    for (var j = 0; j < settings['params'].length; j++) {
                        var column = $('<td></td>');
                        $(column).append(create_field(settings['params'][j], ''));
                        row.append(column);
                    }
                    $('<a href="#" class="button">' + azh.remove + '</a>').appendTo($('<td></td>').appendTo(row)).on('click', function() {
                        $(this).closest('tr').remove();
                        return false;
                    });
                    return false;
                });
                $(field).data('get_value', function() {
                    var values = $.makeArray($(this).find('tr')).map(function(item) {
                        var params = {};
                        $(item).find('[data-param-name]').each(function() {
                            if ($(this).data('get_value')) {
                                params[$(this).data('param-name')] = $(this).data('get_value').call(this);
                            }
                        })
                        return(params);
                    });
                    return encodeURIComponent(JSON.stringify(values));
                });
                break;
            case 'attach_image':
                $(field).append('<label>' + settings['heading'] + '</label>');
                var preview = $('<div class="azh-image-preview"></div>').appendTo(field);
                $('<a href="#" class="button">' + azh.set + '</a>').appendTo(field).on('click', function(event) {
                    azh.open_image_select_dialog.call(field, event, function(url, id) {
                        set_image(this, url, id);
                    });
                    return false;
                });
                $(field).data('get_value', function() {
                    return $(this).find('.azh-image-preview').data('id');
                });
                if (value != '') {
                    azh.get_image_url(value, function(url) {
                        set_image(field, url, value);
                    });
                }
                break;
            case 'attach_images':
                $(field).append('<label>' + settings['heading'] + '</label>');
                var previews = $('<div class="azh-images-preview"></div>').appendTo(field);
                $('<a href="#" class="button">' + azh.add + '</a>').appendTo(field).on('click', function(event) {
                    azh.open_image_select_dialog.call(field, event, function(images) {
                        add_images(this, images);
                    }, true);
                    return false;
                });
                $(field).data('get_value', function() {
                    return $.makeArray($(this).find('.azh-images-preview .azh-image-preview')).map(function(item) {
                        return $(item).data('id');
                    }).join(',');
                });
                if (value != '') {
                    var images = value.split(',').map(function(item) {
                        return {id: item};
                    });
                    for (var i = 0; i < images.length; i++) {
                        (function(i) {
                            azh.get_image_url(images[i]['id'], function(url) {
                                images[i]['url'] = url;
                                var all = true;
                                for (var j = 0; j < images.length; j++) {
                                    if (!('url' in images[j])) {
                                        all = false;
                                        break;
                                    }
                                }
                                if (all) {
                                    add_images(field, images);
                                }
                            });
                        })(i);
                    }
                }
                break;
            case 'vc_link':
                $(field).append('<label>' + settings['heading'] + '</label>');
                var wrapper = $('<div class="azh-link-field"></div>').appendTo(field);
                var link = {};
                if (value != '') {
                    value.split('|').map(function(item) {
                        link[item.split(':')[0]] = decodeURIComponent(item.split(':')[1]);
                    });
                }
                $(field).data('link', link);
                var button = $('<a href="#" class="button">' + azh.select_url + '</a>').appendTo(wrapper).on('click', function(event) {
                    var link = $(field).data('link');
                    azh.open_link_select_dialog.call(this, event, function(url, target, title) {
                        var link = {
                            url: url,
                            target: target,
                            title: title,
                            rel: 'nofollow'
                        };
                        $(field).data('link', link);
                        $(title_span).text(title);
                        $(url_span).text(url);
                    }, ('url' in link ? link['url'] : ''), ('target' in link ? link['target'] : ''), ('title' in link ? link['title'] : ''));
                    return false;
                });
                $(wrapper).append('<label>' + azh.title + '</label>');
                var title_span = $('<span>' + ('title' in link ? link['title'] : '') + '</span>').appendTo(wrapper);
                $(wrapper).append('<label>' + azh.url + '</label>');
                var url_span = $('<span>' + ('url' in link ? link['url'] : '') + '</span>').appendTo(wrapper);
                $(field).data('get_value', function() {
                    return $.map($(this).data('link'), function(value, index) {
                        return [index + ':' + encodeURIComponent(value)];
                    }).join('|');
                });
                break;
            case 'iconpicker':
                $(field).append('<label>' + settings['heading'] + '</label>');
                var textfield = $('<input type="text">').appendTo(field);
                $(textfield).val(value);
                azh.icon_select_dialog(function(icon) {
                    $(textfield).val(icon);
                }, settings['settings']['type']).appendTo(field);
                $(field).data('get_value', function() {
                    return  $(this).find('input[type="text"]').val();
                });
                break;
            case 'autocomplete':
                $(field).append('<label>' + settings['heading'] + '</label>');
                var textfield = $('<input type="text">').appendTo(field);
                $(field).data('value', value);
                var shortcode_settings = {};
                setTimeout(function() {
                    shortcode_settings = $(field).closest('.azh-from').data('settings');
                    if ($.trim(value) != '') {
                        $.post(ajaxurl, {
                            'action': 'azh_autocomplete_labels',
                            'shortcode': shortcode_settings['base'],
                            'param_name': settings['param_name'],
                            'values': value
                        }, function(data) {
                            $(textfield).val(Object.keys(data).map(function(item) {
                                return data[item]
                            }).join(', '));
                            $(field).data('value', Object.keys(data).join(','));
                        }, 'json');
                    }
                });

                $(textfield).on("keydown", function(event) {
                    if (event.keyCode === $.ui.keyCode.TAB && $(this).autocomplete("instance").menu.active) {
                        event.preventDefault();
                    }
                }).autocomplete({
                    minLength: 0,
                    source: function(request, response) {
                        if (request.term.split(/,\s*/).pop() != '') {
                            $.post(ajaxurl, {
                                'action': 'azh_autocomplete',
                                'shortcode': shortcode_settings['base'],
                                'param_name': settings['param_name'],
                                'exclude': $(field).data('value'),
                                'search': request.term.split(/,\s*/).pop()
                            }, function(data) {
                                response(data);
                            }, 'json');
                        } else {
                            response();
                        }
                    },
                    focus: function() {
                        return false;
                    },
                    select: function(event, ui) {
                        if (ui.item) {
                            var labels = this.value.split(/,\s*/);
                            labels.pop();
                            labels.push(ui.item.label);
                            labels.push('');
                            this.value = labels.join(', ');

                            var values = $(field).data('value').split(/,\s*/);
                            values.push(ui.item.value);
                            $(field).data('value', values.join(',').replace(/,\s*$/, '').replace(/^\s*,/, ''));

                        }
                        return false;
                    }
                }).on("keydown keyup blur", function(event) {
                    if ($(textfield).val() == '') {
                        $(field).data('value', '');
                    }
                });
                $(textfield).autocomplete('instance')._create = function() {
                    this._super();
                    this.widget().menu('option', 'items', '> :not(.ui-autocomplete-group)');
                };
                $(textfield).autocomplete('instance')._renderMenu = function(ul, items) {
                    var that = this, currentGroup = '';
                    $.each(items, function(index, item) {
                        var li;
                        if ('group' in item && item.group != currentGroup) {
                            ul.append('<li class="ui-autocomplete-group">' + item.group + '</li>');
                            currentGroup = item.group;
                        }
                        li = that._renderItemData(ul, item);
                        if ('group' in item && item.group) {
                            li.attr('aria-label', item.group + ' : ' + item.label);
                        }
                    });
                };
                $(field).data('get_value', function() {
                    return  $(this).data('value');
                });
                break;
        }
        if ($(field).data('get_value')) {
            if ('description' in settings) {
                $(field).append('<em>' + settings['description'] + '</em>');
            }
        }
        return field;
    }
    function create_form(settings, values) {
        if ('params' in settings) {
            var form = $('<div class="azh-from" title="' + settings['name'] + '"></div>');
            $(form).data('settings', settings);
            var groups = {'General': []};
            for (var i = 0; i < settings['params'].length; i++) {
                if ('group' in settings['params'][i]) {
                    groups[settings['params'][i]['group']] = [];
                }
            }
            for (var i = 0; i < settings['params'].length; i++) {
                if ('group' in settings['params'][i]) {
                    groups[settings['params'][i]['group']].push(settings['params'][i]);
                } else {
                    groups['General'].push(settings['params'][i]);
                }
            }
            var tabs = $('<div></div>').appendTo(form);
            var tabs_buttons = $('<ul></ul>').appendTo(tabs);
            var ids = {};
            for (var group in groups) {
                var id = makeid();
                ids[group] = id;
                $('<li><a href="#' + id + '">' + group + '</a></li>').appendTo(tabs_buttons);
            }
            for (var group in groups) {
                var tab = $('<div id = "' + ids[group] + '"></div>').appendTo(tabs);
                for (var i = 0; i < groups[group].length; i++) {
                    create_field(groups[group][i], (groups[group][i]['param_name'] in values ? values[groups[group][i]['param_name']] : '')).appendTo(tab);
                }
            }
            $(tabs).tabs();
            setTimeout(function() {
                $(form).find('.ui-tabs-panel > [data-param-name]').trigger('change');
            }, 0);
            return form;
        }
    }
    function create_dialog(form, callback) {
        $.ui.dialog.prototype._focusTabbable = $.noop;
        var dialog = $(form).dialog({
            autoOpen: true,
            height: 600,
            width: 600,
            modal: true,
            buttons: {
                'OK': function() {
                    var attrs = {};
                    $(form).find('.ui-tabs-panel > [data-param-name]').each(function() {
                        if ($(this).data('get_value')) {
                            attrs[$(this).data('param-name')] = $(this).data('get_value').call(this);
                        }
                    })
                    var settings = $(form).data('settings');
                    var shortcode = '[' + settings['base'];
                    var content = false;
                    if ('content_element' in settings && settings['content_element']) {
                        content = ' ';
                    }
                    if ('content' in attrs) {
                        content = attrs['content'];
                    }
                    shortcode += Object.keys(attrs).map(function(item) {
                        if (item == 'content') {
                            return '';
                        } else {
                            return ' ' + item + '="' + attrs[item] + '"';
                        }
                    }).join('');
                    shortcode += ']';
                    if (content) {
                        shortcode += content + '[/' + settings['base'] + ']';
                    }
                    callback(shortcode, attrs);
                    dialog.dialog("close");
                },
                'Cancel': function() {
                    dialog.dialog("close");
                }
            },
            close: function() {
            }
        });
        return dialog;
    }
    function create_shortcode(settings) {
        var shortcode = $('<div class="azh-element"><h4>' + settings.name + '</h4><em>' + ('description' in settings ? settings.description : '') + '</em></div>').data('settings', settings);
        return shortcode;
    }
    function create_elements_dialog(callback) {
        $.ui.dialog.prototype._focusTabbable = $.noop;
        var form = $('<div class="azh-elements" title="Shortcodes and Elements"></div>');
        var categories = {'General': []};
        for (var tag in azh.shortcodes) {
            if ('category' in azh.shortcodes[tag]) {
                categories[azh.shortcodes[tag]['category']] = [];
            }
        }
        for (var tag in azh.shortcodes) {
            if ('category' in azh.shortcodes[tag]) {
                categories[azh.shortcodes[tag]['category']].push(azh.shortcodes[tag]);
            } else {
                categories['General'].push(azh.shortcodes[tag]);
            }
        }
        if (categories['General'].length == 0) {
            delete categories['General'];
        }
        if ($('.azh-library .azh-elements .azh-element').length) {
            categories['Elements'] = [];
        }
        var tabs = $('<div></div>').appendTo(form);
        var tabs_buttons = $('<ul></ul>').appendTo(tabs);
        var ids = {};
        for (var category in categories) {
            var id = makeid();
            ids[category] = id;
            $('<li><a href="#' + id + '">' + category + '</a></li>').appendTo(tabs_buttons);
        }
        for (var category in categories) {
            var tab = $('<div id = "' + ids[category] + '"></div>').appendTo(tabs);
            for (var i = 0; i < categories[category].length; i++) {
                create_shortcode(categories[category][i]).appendTo(tab).on('click', function() {
                    if ('content_element' in $(this).data('settings') && $(this).data('settings')['content_element']) {
                        callback('[' + $(this).data('settings')['base'] + '] [/' + $(this).data('settings')['base'] + ']');
                    } else {
                        callback('[' + $(this).data('settings')['base'] + ']');
                    }
                    dialog.dialog("close");
                });
            }
            if (category == 'Elements') {
                var select = $('.azh-library .azh-elements .azh-categories').clone();
                $(select).appendTo(tab);
                $(select).on('change', function() {
                    $('.azh-elements .azh-element').hide();
                    if ($(this).val() == '') {
                        $('.azh-elements .azh-element').show();
                    } else {
                        $('.azh-elements .azh-element[data-path^="' + $(this).val() + '"]').show();
                    }
                });
                $('.azh-library .azh-elements .azh-element').each(function() {
                    var button = $(this).clone();
                    button.appendTo(tab).on('click', function() {
                        $.get($(button).data('url'), function(data) {
                            data = data.replace(/{{azh-uri}}/g, $(button).data('dir-uri'));
                            data = html_beautify(data);
                            callback(data);
                            dialog.dialog("close");
                        });
                    });
                });
            }
        }
        $(tabs).tabs();
        var dialog = $(form).dialog({
            autoOpen: true,
            height: 600,
            width: 800,
            modal: true,
            buttons: {
                'Cancel': function() {
                    dialog.dialog("close");
                }
            },
            close: function() {
            }
        });
        return dialog;
    }
    function parse_url(url) {
        var a = document.createElement('a');
        a.href = url;
        return a;
    }
    function remove_description(text) {
        var match = null;
        if ((match = /\[\[([^\]]+)\]\]/gi.exec(text)) != null && match.length == 2) {
            return text.replace(match[0], '');
        }
        return text;
    }
    function try_default_description(element, text) {
        if (!$(element).data('description')) {
            $(azh.options.description_patterns).each(function() {
                var description_pattern = null;
                if (typeof this == 'string') {
                    description_pattern = new RegExp(this, 'gi');
                } else {
                    description_pattern = new RegExp(this);
                }
                var match = null;
                if ((match = description_pattern.exec(text)) != null && match.length == 2) {
                    $(element).data('description', match[1]);
                }
            });
        }
    }
    function wrap_code(code, match, index, wrapper) {
        //this - DOM element with code
        //code - string which need to be splitted
        //match - string which need to be wrapped
        //index - number of first character of match in code
        if ($(this).data('get_code') && match != '') {
            $(this).empty();
            $(this).data('get_code', false);

            var left_text = make_text.call(this, code.slice(0, index));
            var wrapped_code = $(wrapper).html(htmlEncode(remove_description(match))).appendTo(this);
            var right_text = make_text.call(this, code.slice(index + match.length));

            $(document).trigger('azh-wrap-code', {element: left_text});
            $(document).trigger('azh-wrap-code', {element: right_text});

            var m = null;
            if ((m = /\[\[([^\]]+)\]\]/gi.exec(match)) != null && m.length == 2) {
                $(wrapped_code).data('description', m[1]);
            }

            return wrapped_code;
        }
        return null;
    }
    function wrap_content(element, code, match, index) {
        var content = wrap_code.call(element, code, match, index, '<span class="azh-content"></span>');
        $(content).addClass('azh-editable');

        if (match in azh.shortcode_instances) {
            $(content).addClass('azh-shortcode');
            $(content).data('description', azh.shortcodes[azh.shortcode_instances[match].shortcode].description);
            $(content).empty();
            $(content).append('<strong>' + azh.shortcodes[azh.shortcode_instances[match].shortcode].name + '</strong><br>');
            var atts = Object.keys(azh.shortcode_instances[match].atts_raw.named).map(function(item) {
                if (item == 'content') {
                    return '';
                } else {
                    return ' ' + item + '="' + azh.shortcode_instances[match].atts_raw.named[item] + '"';
                }
            }).join('');
            $(content).append('<em>' + atts + '</em>');
            $(content).data('content', azh.shortcode_instances[match].raw);
            $(content).data('shortcode_instance', azh.shortcode_instances[match]);
            $(content).data('get_code', function() {
                return $(this).data('content');
            });

            $(content).on('click', function() {
                var content = this;
                var instance = $(content).data('shortcode_instance');
                var values = instance.atts_raw.named;
                if (instance.sub_content != '') {
                    values['content'] = instance.sub_content;
                }
                create_dialog(create_form(azh.shortcodes[instance.shortcode], values), function(shortcode, attrs) {
                    var instance = $(content).data('shortcode_instance');
                    instance.atts_raw.named = attrs;
                    if ('content' in attrs) {
                        instance.sub_content = attrs['content'];
                    }
                    $(content).data('shortcode_instance', instance);
                    $(content).data('content', shortcode);
                    $(content).empty();
                    $(content).append('<strong>' + azh.shortcodes[instance.shortcode].name + '</strong><br>');
                    var atts = Object.keys(instance.atts_raw.named).map(function(item) {
                        if (item == 'content') {
                            return '';
                        } else {
                            return ' ' + item + '="' + instance.atts_raw.named[item] + '"';
                        }
                    }).join('');
                    $(content).append('<em>' + atts + '</em>');
                    azh.store();
                });

                return false;
            });

        } else {
            $(content).data('content', remove_description(match));
            $(content).data('get_code', function() {
                if ($(this).data('description')) {
                    return $(this).data('content') + '[[' + $(this).data('description') + ']]';
                } else {
                    return $(this).data('content');
                }
            });
            $(content).attr('contenteditable', 'true');
            $(content).on('blur keyup paste input', function() {
                var content = this;
                $(content).data('content', $(content).text());
                azh.store();
            });
        }

        return content;
    }
    function html_beautify(html) {
        var results = '';
        var level = 0;
        HTMLParser(html, {
            start: function(tag, attrs, unary) {
                results += Array(level * azh.indent_size).join(' ') + "<" + tag;
                for (var i = 0; i < attrs.length; i++) {
                    results += " " + attrs[i].name + '="' + attrs[i].escaped + '"';
                }
                results += (unary ? "/" : "") + ">\n";
                if (!unary) {
                    level++;
                }
            },
            end: function(tag) {
                level--;
                results += Array(level * azh.indent_size).join(' ') + "</" + tag + ">\n";
            },
            chars: function(text) {
                if ($.trim(text)) {
                    results += Array(level * azh.indent_size).join(' ') + $.trim(text) + "\n";
                }
            },
        });
        return results;
    }
    var shortcodes_regexp = _.memoize(function(tags) {
        return new RegExp('\\[(\\[?)(' + tags + ')(?![\\w-])([^\\]\\/]*(?:\\/(?!\\])[^\\]\\/]*)*?)(?:(\\/)\\]|\\](?:([^\\[]*(?:\\[(?!\\/\\2\\])[^\\[]*)*)(\\[\\/\\2\\]))?)(\\]?)');
    });
    $(document).off('azh-wrap-code.base').on('azh-wrap-code.base', function(sender, data) {
        if ($(data.element).data('get_code')) {
            var code = $(data.element).data('get_code').call(data.element);

            $(azh.options.id_patterns).each(function() {
                var id_pattern = null;
                if (typeof this == 'string') {
                    id_pattern = new RegExp(this, 'gi');
                } else {
                    id_pattern = new RegExp(this);
                }
                var match = null;
                while ((match = id_pattern.exec(code)) != null && match.length == 2) {
                    var content = wrap_content(data.element, code, match[1], match.index + match[0].lastIndexOf(match[1]));
                    $(content).addClass('azh-id');
                }
            });

            $(azh.options.link_patterns).each(function() {
                var link_pattern = null;
                if (typeof this == 'string') {
                    link_pattern = new RegExp(this, 'gi');
                } else {
                    link_pattern = new RegExp(this);
                }
                var match = null;
                while ((match = link_pattern.exec(code)) != null && match.length == 2) {
                    var link = wrap_code.call(data.element, code, match[1], match.index + match[0].lastIndexOf(match[1]), '<span class="azh-link"></span>');
                    $(link).addClass('azh-editable');
                    $(link).text(remove_description(match[1]));
                    if (match[1][0] != '#') {
                        if (/^(?:[\w]+:)?\/\//i.test(remove_description(match[1]))) {
                            $(link).text('...' + parse_url(remove_description(match[1])).pathname + parse_url(remove_description(match[1])).search);
                        }
                    }
                    $(link).data('url', remove_description(match[1]));
                    $(link).data('get_code', function() {
                        if ($(this).data('description')) {
                            return $(this).data('url') + '[[' + $(this).data('description') + ']]';
                        } else {
                            return $(this).data('url');
                        }
                    });
                    $(link).on('click', function(event) {
                        var link = this;
                        azh.open_link_select_dialog.call(link, event, function(url) {
                            $(this).data('url', url);
                            $(link).text('...' + parse_url(url).pathname + parse_url(url).search);
                            azh.store();
                        });
                    });
                }
            });

            $(azh.options.image_patterns).each(function() {
                var image_pattern = null;
                if (typeof this == 'string') {
                    image_pattern = new RegExp(this, 'gi');
                } else {
                    image_pattern = new RegExp(this);
                }
                var match = null;
                while ((match = image_pattern.exec(code)) != null && match.length == 2) {
                    var image = wrap_code.call(data.element, code, match[1], match.index + match[0].lastIndexOf(match[1]), '<span class="azh-image"></span>');
                    try_default_description(image, match[0]);
                    $(image).addClass('azh-editable');
                    $(image).text('.../' + parse_url(remove_description(match[1])).pathname.split('/').pop());
                    $(image).on('mouseenter', function() {
                        var image = this;
                        var url = $(image).data('url');
                        var hover = $('<div class="azh-hover"></div>').css('background-image', 'url("' + url + '")').appendTo('body');
                        $(image).data('hover', hover);
                        $(image).on('mousemove', function(e) {
                            $(hover).css('left', e.clientX + 'px');
                            $(hover).css('top', e.clientY + 'px');
                        });
                    });
                    $(image).on('mouseleave', function() {
                        var image = this;
                        $(image).off('mousemove');
                        var hover = $(image).data('hover');
                        $(hover).remove();
                    });
                    $(image).data('url', remove_description(match[1]));
                    $(image).data('get_code', function() {
                        if ($(this).data('description')) {
                            return $(this).data('url') + '[[' + $(this).data('description') + ']]';
                        } else {
                            return $(this).data('url');
                        }
                    });
                    $(image).on('click', function(event) {
                        var image = this;
                        azh.open_image_select_dialog.call(image, event, function(url) {
                            $(this).data('url', url);
                            $(this).text('.../' + parse_url(url).pathname.split('/').pop());
                            azh.store();
                        });
                    });
                }
            });

            $(azh.options.icon_patterns).each(function() {
                var icon_pattern = null;
                if (typeof this == 'string') {
                    icon_pattern = new RegExp(this, 'gi');
                } else {
                    icon_pattern = new RegExp(this);
                }
                var match = null;
                while ((match = icon_pattern.exec(code)) != null && match.length == 2) {
                    var icon = wrap_code.call(data.element, code, match[1], match.index + match[0].lastIndexOf(match[1]), '<span class="azh-icon"></span>');
                    try_default_description(icon, match[0]);
                    $(icon).addClass('azh-editable');
                    $(icon).data('class', remove_description(match[1]));
                    $(icon).data('get_code', function() {
                        if ($(this).data('description')) {
                            return $(this).data('class') + '[[' + $(this).data('description') + ']]';
                        } else {
                            return $(this).data('class');
                        }
                    });
                    $(icon).on('mouseenter', function() {
                        var icon = this;
                        var hover = $('<div class="azh-hover ' + $(icon).data('class') + '"></div>').appendTo('body');
                        $(icon).data('hover', hover);
                        $(icon).on('mousemove', function(e) {
                            $(hover).css('left', e.clientX + 'px');
                            $(hover).css('top', e.clientY + 'px');
                        });
                    });
                    $(icon).on('mouseleave', function() {
                        var icon = this;
                        $(icon).off('mousemove');
                        var hover = $(icon).data('hover');
                        $(hover).remove();
                    });

                    $(icon).on('click', function(event) {
                        var icon = this;
                        azh.open_icon_select_dialog.call(icon, event, function(icon_class) {
                            if (icon_class != '') {
                                $(this).data('class', icon_class);
                                $(this).text(icon_class);
                                azh.store();
                            }
                        });
                    });
                }
            });

            $(azh.options.checkbox_patterns).each(function() {
                var checkbox_pattern = null;
                if (typeof this == 'string') {
                    checkbox_pattern = new RegExp(this, 'gi');
                } else {
                    checkbox_pattern = new RegExp(this);
                }
                var match = null;
                while ((match = checkbox_pattern.exec(code)) != null && match.length == 2) {
                    var checkbox = wrap_code.call(data.element, code, match[1], match.index + match[0].lastIndexOf(match[1]), '<span class="azh-checkbox"></span>');
                    try_default_description(checkbox, match[0]);
                    $(checkbox).addClass('azh-editable');
                    $(checkbox).empty();
                    var input = $('<input type="checkbox">').appendTo(checkbox);
                    $(checkbox).data('content', remove_description(match[1]));
                    var positive = ['true', 'yes', '1'];
                    var negative = ['false', 'no', '0'];
                    if (positive.indexOf(remove_description(match[1])) >= 0) {
                        $(input).prop('checked', true);
                    } else {
                        $(input).prop('checked', false);
                    }
                    $(input).on('change', function() {
                        if ($(this).prop('checked')) {
                            var i = negative.indexOf($(this).closest('.azh-checkbox').data('content'));
                            if (i >= 0) {
                                $(this).closest('.azh-checkbox').data('content', positive[i]);
                            }
                        } else {
                            var i = positive.indexOf($(this).closest('.azh-checkbox').data('content'));
                            if (i >= 0) {
                                $(this).closest('.azh-checkbox').data('content', negative[i]);
                            }
                        }
                        azh.store();
                    });
                    $(checkbox).data('get_code', function() {
                        if ($(this).data('description')) {
                            return $(this).data('content') + '[[' + $(this).data('description') + ']]';
                        } else {
                            return $(this).data('content');
                        }
                    });
                }
            });

            $(azh.options.dropdown_patterns).each(function() {
                var dropdown_pattern = null;
                if (typeof this.pattern == 'string') {
                    dropdown_pattern = new RegExp(this.pattern, 'gi');
                } else {
                    dropdown_pattern = new RegExp(this.pattern);
                }
                var match = null;
                while ((match = dropdown_pattern.exec(code)) != null && match.length == 2) {
                    var dropdown = wrap_code.call(data.element, code, match[1], match.index + match[0].lastIndexOf(match[1]), '<span class="azh-dropdown"></span>');
                    try_default_description(dropdown, match[0]);
                    $(dropdown).addClass('azh-editable');
                    $(dropdown).empty();
                    var select = $('<select></select>').appendTo(dropdown);
                    for (var value in this.options) {
                        $(select).append('<option value="' + value + '" ' + (value == remove_description(match[1]) ? 'selected' : '') + '>' + this.options[value] + '</option>');
                    }
                    $(dropdown).data('content', remove_description(match[1]));
                    $(select).on('change', function() {
                        $(this).closest('.azh-dropdown').data('content', $(this).find('option:selected').attr('value'));
                        azh.store();
                    });
                    $(dropdown).data('get_code', function() {
                        if ($(this).data('description')) {
                            return $(this).data('content') + '[[' + $(this).data('description') + ']]';
                        } else {
                            return $(this).data('content');
                        }
                    });
                }
            });


            $(azh.options.column_patterns).each(function() {
                var column_pattern = null;
                if (typeof this == 'string') {
                    column_pattern = new RegExp(this, 'gi');
                } else {
                    column_pattern = new RegExp(this);
                }
                var match = null;
                while ((match = column_pattern.exec(code)) != null && match.length == 2 && $.isNumeric(match[1])) {
                    var column_width = wrap_code.call(data.element, code, match[1], match.index + match[0].lastIndexOf(match[1]), '<span class="azh-column-width azh-hide"></span>');
                    $(column_width).data('pattern', match[0]);
                    $(column_width).data('column-width', match[1]);
                    $(column_width).data('get_code', function() {
                        return $(this).data('column-width');
                    });
                }
            });

            $(azh.options.text_patterns).each(function() {
                var content_pattern = null;
                if (typeof this == 'string') {
                    content_pattern = new RegExp(this, 'gi');
                } else {
                    content_pattern = new RegExp(this);
                }
                var match = null;
                while ((match = content_pattern.exec(code)) != null && match.length == 2) {
                    var content = wrap_content(data.element, code, match[1], match.index + match[0].lastIndexOf(match[1]));
                    try_default_description(content, match[0]);
                }
            });
            var line = $(data.element).closest('.azh-line');
            var line_code = $(line).data('get_code').call(line);
            if (line_code.trim() == code.trim() && code.match(/^([^><]+)$/gi)) {
                wrap_content(data.element, code, code, 0);
            }

            $(azh.options.group_patterns).each(function() {
                var group_pattern = null;
                if (typeof this == 'string') {
                    group_pattern = new RegExp(this, 'gi');
                } else {
                    group_pattern = new RegExp(this);
                }
                var match = null;
                while ((match = group_pattern.exec(code)) != null && match.length == 2) {
                    var group = wrap_content(data.element, code, match[1], match.index + match[0].lastIndexOf(match[1]));
                    $(group).addClass('azh-group-title');
                }
            });

            $(azh.options.hide_patterns).each(function() {
                var hide_pattern = null;
                if (typeof this == 'string') {
                    hide_pattern = new RegExp(this, 'gi');
                } else {
                    hide_pattern = new RegExp(this);
                }
                var match = null;
                while ((match = hide_pattern.exec(code)) != null && match.length == 2) {
                    var content = wrap_code.call(data.element, code, match[1], match.index + match[0].lastIndexOf(match[1]), '<span class="azh-hide"></span>');
                    $(content).data('content', match[1]);
                    $(content).data('get_code', function() {
                        return $(this).data('content');
                    });
                }
            });
        }
    });
    $(document).off('azh-lines.base').on('azh-lines.base', function(sender, data) {
        var path = [data.tree];
        var current_level = 0;
        $(data.lines).each(function() {
            var level = Math.round($(this).data('indent') / azh.indent_size);
            var code_line = $(this).data('get_code').call(this);
            var close_pattern = /^\s*<\/\w+>\s*$/i;
            var match = close_pattern.exec(code_line);
            if (match) {
                if (level < current_level) {
                    path[current_level].close_line = this;
                    path.pop();
                    current_level = level;
                } else {
                    path[current_level].children[path[current_level].children.length - 1].close_line = this;
                }
            } else {
                if (level > current_level) {
                    path.push(path[current_level].children[path[current_level].children.length - 1]);
                    current_level = level;
                }
                if (level < current_level) {
                    path.pop();
                    current_level = level;
                }
                var node = {open_line: this, close_line: false, children: []};
                path[current_level].children.push(node);
            }
        });
    });
    $(document).off('azh-tree.base').on('azh-tree.base', function(sender, data) {
        function wrap_lines(node) {
            if (node.open_line && node.close_line && !('wrapper' in node)) {
                var lines = $(node.open_line).nextUntil(node.close_line);
                lines = $(node.open_line).add(lines).add(node.close_line);
                node.wrapper = lines.wrapAll("<div class='azh-wrapper' />").parent();
                $(node.open_line).addClass('azh-open-line');
                $(node.close_line).addClass('azh-close-line');
                $(node.wrapper).data('node', node); // not correct after cloning - because tree is not rebuild
            }
            $(node.children).each(function() {
                wrap_lines(this);
            });
        }
        wrap_lines(data.tree);
    });
    $(document).off('azh-wrap-tree.base').on('azh-wrap-tree.base', function(sender, data) {
        function cloneable(horizontal) {
            var cloneable = $(data.node.open_line).nextUntil(data.node.close_line).wrapAll("<div class='azh-cloneable' />").parent();
            if (horizontal) {
                $(cloneable).addClass('azh-inline');
                setTimeout(function() {
                    $(cloneable).children().each(function() {
                        $(this).css('white-space', 'nowrap');
                        if ($(this).width() > $(cloneable).width()) {
                            $(this).width($(cloneable).width());
                            $(this).css('white-space', 'normal');
                        }
                    });
                }, 0);
            }
            var indent = $(data.node.open_line).data('indent');
            $(data.node.wrapper).find('.azh-line').each(function() {
                $(this).find('.indent').html(Array($(this).data('indent') - indent).join("&nbsp;"));
            });
            var collapsed = $(cloneable).height() > 500;
            $(cloneable).children().each(function() {
                function get_linked_element(element) {
                    var linked_element = false;
                    if ($(element).find('.azh-id').length) {
                        var handle_id = $(element).find('.azh-id');
                        var id = $(handle_id).data('get_code').call(handle_id);
                        if (id in azh.ids && azh.ids[id]) {
                            for (var i = 0; i < azh.ids[id].length; i++) {
                                var linked_id = azh.ids[id][i];
                                if (!$(linked_id).is($(element).find('.azh-id'))) {
                                    if ($(linked_id).closest('.azh-cloneable').length) {
                                        $(linked_id).closest('.azh-cloneable').children().each(function() {
                                            if ($(this).find(linked_id).length) {
                                                linked_element = this;
                                            }
                                        });
                                    }
                                }
                            }
                        }
                    }
                    return linked_element;
                }
                var element = this;
                if ($(element).is('.azh-wrapper')) {
                    $(element).data('node').matched = true;
                }
                var controls = $('<div class="azh-controls"></div>').prependTo(element).on('click', function(e) {
                    e.stopPropagation();
                });
                $('<div class="azh-move" title="' + azh.move + '"></div>').appendTo(controls);
                $('<div class="azh-clone" title="' + azh.clone + '"></div>').appendTo(controls).on('click', function() {
                    var element = $(this).parent().parent();
                    var cloneable = $(this).closest('.azh-cloneable');
                    var new_element = $(element).clone(true);
                    $(element).after(new_element);

                    if ($(new_element).find('.azh-id').length) {
                        var handle_id = $(new_element).find('.azh-id');
                        var id = $(handle_id).data('get_code').call(handle_id);
                        if (id in azh.ids && azh.ids[id]) {
                            var unoque_id = makeid();
                            var new_linked_element = false;
                            $(handle_id).data('content', unoque_id);
                            $(handle_id).text(unoque_id);
                            for (var i = 0; i < azh.ids[id].length; i++) {
                                var linked_id = azh.ids[id][i];
                                if (!$(linked_id).is($(element).find('.azh-id'))) {
                                    if ($(linked_id).closest('.azh-cloneable').length) {
                                        $(linked_id).closest('.azh-cloneable').children().each(function() {
                                            if ($(this).find(linked_id).length) {
                                                new_linked_element = $(this).clone(true);
                                                $(this).after(new_linked_element);
                                                var new_linked_id = $(new_linked_element).find('.azh-id');
                                                $(new_linked_id).data('content', unoque_id);
                                                $(new_linked_id).text(unoque_id);
                                            }
                                        });
                                    }
                                }
                            }
                            if (new_linked_element) {
                                azh.ids[unoque_id] = [$(new_element).find('.azh-id'), $(new_linked_element).find('.azh-id')];
                            }
                        }
                    }
                    if ($(cloneable).children().length > 1) {
                        $(cloneable).children().find('> .azh-controls > .azh-remove').show();
                    }
                    azh.store();
                    return false;
                });
                $('<div class="azh-remove" title="' + azh.remove + '"></div>').appendTo(controls).on('click', function() {
                    var element = $(this).parent().parent();
                    var cloneable = $(this).closest('.azh-cloneable');

                    var linked_element = get_linked_element(element);
                    if (linked_element) {
                        $(linked_element).remove();
                    }
                    $(element).remove();
                    if ($(cloneable).children().length == 1) {
                        $(cloneable).children().find('> .azh-controls > .azh-remove').hide();
                    } else {
                        $(cloneable).children().find('> .azh-controls > .azh-remove').show();
                    }
                    azh.store();
                    return false;
                });
                if (collapsed && !horizontal) {
                    if ($(element).is('.azh-wrapper')) {
                        $(element).addClass('azh-collapsed');
                    }
                }
                $(element).off('click').on('click', function(event) {
                    var element = this;
                    var linked_element = get_linked_element(element);
                    if (collapsed || linked_element) {
                        if ($(element).is('.azh-collapsed')) {
                            $(element).closest('.azh-cloneable:not(.azh-inline)').find('> .azh-wrapper').addClass('azh-collapsed');
                            $(element).removeClass('azh-collapsed');
                        }
                        if (linked_element) {
                            $(linked_element).closest('.azh-cloneable:not(.azh-inline)').find('> .azh-wrapper').addClass('azh-collapsed');
                            $(linked_element).removeClass('azh-collapsed');
                        }
                        event.stopPropagation();
                        return false;
                    }
                });

            });
            if ($(cloneable).children().length === 1) {
                $(cloneable).children().find('> .azh-controls > .azh-remove').hide();
            }
            $(cloneable).sortable({
                handle: '> .azh-controls > .azh-move',
                placeholder: 'azh-placeholder',
                forcePlaceholderSize: true,
                update: function(event, ui) {
                    azh.store();
                },
                over: function(event, ui) {
                    ui.placeholder.attr('class', ui.helper.attr('class'));
                    ui.placeholder.removeClass('ui-sortable-helper');

                    ui.placeholder.attr('style', ui.helper.attr('style'));
                    ui.placeholder.css('position', 'relative');
                    ui.placeholder.css('z-index', 'auto');
                    ui.placeholder.css('left', 'auto');
                    ui.placeholder.css('top', 'auto');

                    ui.placeholder.addClass('azh-placeholder');
                }
            });
        }
        var code_line = $(data.node.open_line).data('get_code').call(data.node.open_line);

        $(azh.options.cloneable_patterns).each(function() {
            var cloneable_pattern = null;
            if (typeof this == 'string') {
                cloneable_pattern = new RegExp(this, 'gi');
            } else {
                cloneable_pattern = new RegExp(this);
            }
            var match = null;
            if ((match = cloneable_pattern.exec(code_line)) != null && match.length == 1) {
                cloneable(false);
            }
        });
        $(azh.options.cloneable_inline_patterns).each(function() {
            var cloneable_pattern = null;
            if (typeof this == 'string') {
                cloneable_pattern = new RegExp(this, 'gi');
            } else {
                cloneable_pattern = new RegExp(this);
            }
            var match = null;
            if ((match = cloneable_pattern.exec(code_line)) != null && match.length == 1) {
                cloneable(true);
            }
        });

        $(azh.options.row_patterns).each(function() {
            var row_pattern = null;
            if (typeof this == 'string') {
                row_pattern = new RegExp(this, 'gi');
            } else {
                row_pattern = new RegExp(this);
            }
            var match = null;
            while ((match = row_pattern.exec(code_line)) != null) {
                $(data.node.wrapper).addClass('azh-row');
            }
        });

        $(azh.options.column_patterns).each(function() {
            var column_pattern = null;
            if (typeof this == 'string') {
                column_pattern = new RegExp(this, 'gi');
            } else {
                column_pattern = new RegExp(this);
            }
            var match = null;
            while ((match = column_pattern.exec(code_line)) != null) {
                if (!$(data.node.wrapper).is('[class*="azh-col-"]')) {
                    if (match.length == 2 && $.isNumeric(match[1])) {
                        $(data.node.wrapper).addClass("azh-col-" + match[1]);
                    } else {
                        $(data.node.wrapper).addClass("azh-column");
                    }
                    var indent = $(data.node.open_line).data('indent');
                    $(data.node.wrapper).find('.azh-line').each(function() {
                        $(this).find('.indent').html(Array($(this).data('indent') - indent).join("&nbsp;"));
                    });
                }
            }
        });

        $(azh.options.column_offset_patterns).each(function() {
            var column_offset_pattern = null;
            if (typeof this == 'string') {
                column_offset_pattern = new RegExp(this, 'gi');
            } else {
                column_offset_pattern = new RegExp(this);
            }
            var match = null;
            while ((match = column_offset_pattern.exec(code_line)) != null && match.length == 2) {
                if ($(data.node.wrapper).is('[class*="azh-col-"]')) {
                    $(data.node.wrapper).addClass("azh-col-offset-" + match[1]);
                }
            }
        });

        $(azh.options.section_patterns).each(function() {
            var section_pattern = null;
            if (typeof this == 'string') {
                section_pattern = new RegExp(this, 'gi');
            } else {
                section_pattern = new RegExp(this);
            }
            var match = null;
            while ((match = section_pattern.exec(code_line)) != null && match.length == 2) {
                $(data.node.wrapper).data('section', match[1]);
                $(data.node.wrapper).addClass('azh-section');
                var controls = $('<div class="azh-controls"></div>').prependTo(data.node.wrapper);
                $('<div class="azh-edit"  title="' + azh.edit_text + '"></div>').appendTo(controls).on('click', function() {
                    var section = $(this).closest('.azh-section');
                    $(section).addClass('edit');
                    var id = makeid();
                    var textarea = $('<textarea id="' + id + '"></textarea>');
                    $(textarea).val(default_get_code.call(section));
                    $(section).find('> :not(.azh-controls)').remove();
                    $(section).append(textarea);
                    $(this).closest('.azh-controls').find('.azh-done').show();
                    $(this).hide();

                    var aceeditor = ace.edit(id);
                    $(section).data('aceeditor', aceeditor);
                    aceeditor.setTheme("ace/theme/chrome");
                    aceeditor.getSession().setMode("ace/mode/html");
                    aceeditor.setOptions({
                        minLines: 10,
                        maxLines: 30,
                    });
                    return false;
                });
                $('<div class="azh-done"  title="' + azh.done + '"></div>').appendTo(controls).on('click', function() {
                    var section = $(this).closest('.azh-section');
                    $(section).removeClass('edit');
                    azh.add_code($(section).data('aceeditor').getSession().getValue(), $(section).prev(), $(section).next());
                    $(section).find('.azh-id').each(function() {
                        var id = $(this).data('get_code').call(this);
                        if (id in azh.ids) {
                            azh.ids[id] = undefined;
                        }
                    });
                    $(section).remove();
                    return false;
                }).hide();
            }
        });

        $(azh.options.group_patterns).each(function() {
            var group_pattern = null;
            if (typeof this == 'string') {
                group_pattern = new RegExp(this, 'gi');
            } else {
                group_pattern = new RegExp(this);
            }
            var match = null;
            while ((match = group_pattern.exec(code_line)) != null && match.length == 2) {
                $(data.node.wrapper).data('group', match[1]);
                $(data.node.wrapper).addClass('azh-group');
            }
        });

        $(azh.options.wrapper_hide_patterns).each(function() {
            var wrapper_hide_pattern = null;
            if (typeof this == 'string') {
                wrapper_hide_pattern = new RegExp(this, 'gi');
            } else {
                wrapper_hide_pattern = new RegExp(this);
            }
            var match = null;
            while ((match = wrapper_hide_pattern.exec(code_line)) != null && match.length == 1) {
                if (!('matched' in data.node)) {
                    if ($(data.node.open_line).find('.azh-editable').length == 0) {
                        $(data.node.open_line).hide();
                        $(data.node.close_line).hide();
                        var indent = $(data.node.open_line).data('indent');
                        if (indent) {
                            $(data.node.wrapper).find('.azh-line').each(function() {
                                if ($(this).data('indent')) {
                                    if (($(this).data('indent') - indent) >= 0) {
                                        $(this).find('.indent').html(Array($(this).data('indent') - indent).join("&nbsp;"));
                                    }
                                }
                            });
                        }
                    }
                }
            }
        });

        $(azh.options.element_wrapper_patterns).each(function() {
            var element_wrapper = null;
            if (typeof this == 'string') {
                element_wrapper = new RegExp(this, 'gi');
            } else {
                element_wrapper = new RegExp(this);
            }
            var match = null;
            if ((match = element_wrapper.exec(code_line)) != null) {
                $(data.node.wrapper).addClass('azh-element-wrapper');
                if ($(data.node.wrapper).find('.azh-shortcode, .azh-editable, .azh-element-wrapper').length == 0) {
                    $(data.node.wrapper).addClass('azh-empty');
                }
                $(data.node.wrapper).on('click', function() {
                    if ($(this).is('.azh-empty')) {
                        var wrapper = this;
                        var open_line = $(wrapper).find('> .azh-open-line');
                        create_elements_dialog(function(element) {
                            azh.append_code(element, undefined, open_line);
                            $(wrapper).removeClass('azh-empty');
                            azh.store();
                        });
                        return false;
                    }
                });
                var controls = $('<div class="azh-controls"></div>').appendTo(data.node.wrapper);
                $('<div class="azh-element-clear" title="' + azh.clear + '"></div>').appendTo(controls).on('click', function() {
                    $(this).closest('.azh-element-wrapper').addClass('azh-empty');
                    var wrapper = $(this).closest('.azh-element-wrapper');
                    $(wrapper).find('> .azh-open-line').nextUntil($(wrapper).find('> .azh-close-line')).remove();
                    azh.store();
                    return false;
                });
                $('<div class="azh-element-copy" title="' + azh.copy + '"></div>').appendTo(controls).on('click', function() {
                    var wrapper = $(this).closest('.azh-element-wrapper');
                    var lines = $(wrapper).find('> .azh-open-line').nextUntil($(wrapper).find('> .azh-close-line'));
                    var code = default_get_code.call(lines);
                    $.post(ajaxurl, {
                        'action': 'azh_copy',
                        'code': code,
                    }, function(data) {
                    });
                    return false;
                });
                $('<div class="azh-element-paste" title="' + azh.paste + '"></div>').appendTo(controls).on('click', function() {
                    var wrapper = $(this).closest('.azh-element-wrapper');
                    var open_line = $(wrapper).find('> .azh-open-line');
                    $.post(ajaxurl, {
                        'action': 'azh_paste',
                    }, function(data) {
                        $(wrapper).find('> .azh-open-line').nextUntil($(wrapper).find('> .azh-close-line')).remove();
                        azh.append_code(data, undefined, open_line);
                        $(wrapper).removeClass('azh-empty');
                        azh.store();
                    });
                    return false;
                });
            }
        });
    });
    $(document).off('azh-process.base').on('azh-process.base', function(sender, data) {
        for (var i = data.added_from; i < data.tree.children.length; i++) {
            var added_node = data.tree.children[i];
            $(added_node.wrapper).find('[class*="azh-col-"]').each(function() {
                $(this).parentsUntil('.azh-row', '.azh-wrapper').each(function() {
                    var node = $(this).data('node');
                    if (!('matched' in node)) {
                        if ($(node.open_line).find('.azh-editable').length == 0) {
                            $(this).addClass('azh-clearfix');
                            $(node.open_line).hide();
                            $(node.close_line).hide();
                        }
                    }
                });
            });
            $(added_node.wrapper).find('.azh-wrapper:not(.azh-element-wrapper)').each(function() {
                var node = $(this).data('node');
                if (!('matched' in node)) {
                    if ($(this).find('.azh-shortcode, .azh-editable, .azh-element-wrapper').length == 0) {
                        $(this).hide();
                    }
                }
            });
            $(added_node.wrapper).find('.azh-row').each(function() {
                if ($(this).find('.azh-column').length) {
                    var width = (1 / $(this).find('.azh-column').length) * 100;
                    $(this).find('.azh-column').each(function() {
                        $(this).css('width', width + '%');
                    });
                }
            });

            $(added_node.wrapper).find('.azh-id').each(function() {
                var id = $(this).data('get_code').call(this);
                if (!(id in azh.ids) || (typeof azh.ids[id] == 'undefined')) {
                    azh.ids[id] = [];
                }
                azh.ids[id].push(this);
            });
            var ids = {};
            for (var id in azh.ids) {
                if ((typeof azh.ids[id] != 'undefined') && azh.ids[id].length > 1) {
                    var unoque_id = makeid();
                    for (var j = 0; j < azh.ids[id].length; j++) {
                        $(azh.ids[id][j]).data('content', unoque_id);
                        $(azh.ids[id][j]).text(unoque_id);
                        $(azh.ids[id][j]).removeClass('azh-editable');
                        $(azh.ids[id][j]).removeClass('azh-content');
                        $(azh.ids[id][j]).attr('contenteditable', 'false');
                    }
                    ids[unoque_id] = azh.ids[id];
                    azh.ids[id] = undefined;
                }
            }
            azh.ids = $.extend(azh.ids, ids);

            $('.azh-cloneable > .azh-collapsed:first-child').each(function() {
                if ($(this).is('.azh-element-wrapper')) {
                    $(this).removeClass('.azh-collapsed');
                } else {
                    $(this).click();
                }
            });
        }
        azh.store();
    });
    $(function() {
        azh = $.extend({
            ids: {}
        }, azh);
        azh.shortcodes = $.extend({}, azh.shortcodes);
        var description_pattern = /\[\[[^\]'"]+\]\]/i;
        azh.options = $.extend(true, {
            object_patterns: [
                /data-object[=> ]/i
            ],
            hide_patterns: [
            ],
            wrapper_hide_patterns: [
            ],
            section_patterns: [
                / data-section=['"]([^'"]+)['"]/gi
            ],
            group_patterns: [
                / data-group=['"]([^'"]+)['"]/gi,
                / data-section=['"]([^'"]+)['"]/gi
            ],
            row_patterns: [
                /[ '"]row[ '"]/gi,
                /[ '"]az-row[ '"]/gi,
                /<tr[> ]/gi
            ],
            column_patterns: [
                /[ '"]col-lg-([0-9]?[0-9])[ '"]/gi,
                /[ '"]col-md-([0-9]?[0-9])[ '"]/gi,
                /[ '"]col-sm-([0-9]?[0-9])[ '"]/gi,
                /[ '"]col-xs-([0-9]?[0-9])[ '"]/gi,
                /[ '"]az-col-lg-([0-9]?[0-9])[ '"]/gi,
                /[ '"]az-col-md-([0-9]?[0-9])[ '"]/gi,
                /[ '"]az-col-sm-([0-9]?[0-9])[ '"]/gi,
                /[ '"]az-col-xs-([0-9]?[0-9])[ '"]/gi,
                /[ '"]az-column[ '"]/gi,
                /<td[> ]/gi,
                /<th[> ]/gi
            ],
            column_offset_patterns: [
                /[ '"]col-lg-offset-([0-9]?[0-9])[ '"]/gi,
                /[ '"]col-md-offset-([0-9]?[0-9])[ '"]/gi,
                /[ '"]col-sm-offset-([0-9]?[0-9])[ '"]/gi,
                /[ '"]col-xs-offset-([0-9]?[0-9])[ '"]/gi,
                /[ '"]az-col-lg-offset-([0-9]?[0-9])[ '"]/gi,
                /[ '"]az-col-md-offset-([0-9]?[0-9])[ '"]/gi,
                /[ '"]az-col-sm-offset-([0-9]?[0-9])[ '"]/gi,
                /[ '"]az-col-xs-offset-([0-9]?[0-9])[ '"]/gi
            ],
            icon_patterns: [
                / class=['"]icon (az-oi [\w\d-_]+|az-oi [\w\d-_]+\[\[[^\]'"]+\]\])['"]/gi,
                / class=['"]icon (fa [\w\d-_]+|fa [\w\d-_]+\[\[[^\]'"]+\]\])['"]/gi,
                / class=['"]icon ([\w\d-_]+|[\w\d-_]+\[\[[^\]'"]+\]\])['"]/gi,
                /<i\s+class=['"]([\w\d-_ ]+|[\w\d-_ ]+\[\[[^\]'"]+\]\])['"]/gi
            ],
            image_patterns: [
                /background-image\: *url\(['"]?([^'"\)]+)['"]?\)/gi,
                / data-image-src=['"]([^'"]+)['"]/gi,
                / data-src=['"]([^'"]+)['"]/gi,
                / src=['"]([^'"]+)['"]/gi
            ],
            id_patterns: [
                / href=['"]#([\w\d-_]+)['"]/gi,
                / data-target=['"]#([\w\d-_]+|[\w\d-_]+\[\[[^\]'"]+\]\])['"]/gi,
                / data-id=['"]#([\w\d-_]+|[\w\d-_]+\[\[[^\]'"]+\]\])['"]/gi,
                / id=['"]([\w\d-_]+|[\w\d-_]+\[\[[^\]'"]+\]\])['"]/gi
            ],
            link_patterns: [
                / href=['"]([^'"]+)['"]/gi
            ],
            text_patterns: [
                / data-[\w\d-_]+=['"](\d+|\d+\[\[[^\]'"]+\]\])['"]/gi,
                / placeholder=['"]([^'"]+)['"]/gi,
                / title=['"]([^'"]+)['"]/gi,
                />([^><]+)</gi,
                />([^><]+)$/gi,
                /^([^><]+)</gi
            ],
            checkbox_patterns: [
                / data-[\w\d-_]+=['"](no|no\[\[[^\]'"]+\]\])['"]/gi,
                / data-[\w\d-_]+=['"](yes|yes\[\[[^\]'"]+\]\])['"]/gi,
                / data-[\w\d-_]+=['"](false|false\[\[[^\]'"]+\]\])['"]/gi,
                / data-[\w\d-_]+=['"](true|true\[\[[^\]'"]+\]\])['"]/gi
            ],
            dropdown_patterns: [
            ],
            cloneable_patterns: [
                /<tbody[> ]/gi,
                /data-cloneable[=> ]/i
            ],
            cloneable_inline_patterns: [
                /<tr[> ]/gi,
                /data-cloneable-inline[=> ]/i
            ],
            element_wrapper_patterns: [
                /data-element[=> ]/i
            ],
            description_patterns: [
                / data-([\w\d-_]+)=['"]/i,
                /(background-image)/i,
                / class=['"](icon) [\w\d-_]*['"]/i
            ]
        }, azh.options);
        azh.store = function() {
            $(azh.textarea).val(default_get_code.call(azh.lines));
            $(azh.textarea).change();
            setTimeout(function() {
                azh.structure_refresh();
                $(document).trigger('azh-store');
            }, 0);
        }
        azh.append_code = function(code, tree, after, before) {
            function wrap_tree(node) {
                if (node.open_line && node.close_line) {
                    $(document).trigger('azh-wrap-tree', {node: node});
                }
                $(node.children).each(function() {
                    wrap_tree(this);
                });
            }
            tree = (typeof tree !== 'undefined' ? tree : {open_line: false, close_line: false, children: []});
            after = (typeof after !== 'undefined' ? after : false);
            before = (typeof before !== 'undefined' ? before : false);
            if (after && $(after).length == 0) {
                after = false;
            }
            if (before && $(before).length == 0) {
                before = false;
            }

            var new_lines = [];
            var code = remove_shortcode_instances(code);
            code = html_beautify(code);

            var code_lines = code.split("\n");
            code_lines = code_lines.filter(Boolean);
            for (var key in code_lines) {
                var indent = 0;
                var code_line = code_lines[key];
                while (code_line.charAt(0) == ' ') {
                    indent++;
                    code_line = code_line.substr(1);
                }
                var line = make_line(key, indent);
                if (after) {
                    $(after).after(line);
                    after = line;
                } else {
                    if (before) {
                        $(before).before(line);
                        after = line;
                    } else {
                        $(line).appendTo(azh.lines);
                    }
                }
                new_lines.push(line);

                var text = make_text.call(line, code_line);
                $(document).trigger('azh-wrap-code', {element: text});
            }
            var data = {lines: new_lines};
            data.tree = tree;
            var added_from = tree.children.length;
            $(document).trigger('azh-lines', data); // make tree
            $(document).trigger('azh-tree', data); // wrap all tree by default wrappers            
            data.added_from = added_from;
            for (var i = data.added_from; i < data.tree.children.length; i++) {
                wrap_tree(data.tree.children[i]);
            }
            $(document).trigger('azh-process', data); // process
            return data;
        }
        azh.add_code = function(code, after, before) {
            azh.lines = $(azh.editor).find('.azh-lines');
            if (azh.lines.length == 0) {
                azh.lines = $('<div class="azh-lines"></div>').appendTo(azh.editor);
            }
            if (!('tree' in azh)) {
                azh.tree = {open_line: false, close_line: false, children: []};
            }
            azh.ids = {};
            azh.shortcode_instances = {};
            azh.append_code(code, azh.tree, after, before);
            azh.store();
        };
        azh.init = function(textarea, edit) {
            azh.textarea = textarea;
            $(textarea).hide();
            azh.editor = $('<div class="azexo-html-editor"></div>');
            azh.indent_size = 4;
            $(textarea).after(azh.editor);
            azh.edit = (typeof edit !== 'undefined' ? edit : true);
            var switcher = $('<a class="azh-switcher button">' + azh.switch_to_html + '</a>').on('click', function() {
                if (azh.edit) {
                    $(textarea).show();
                    $(azh.editor).empty();
                    $(azh.editor).hide();
                    $(switcher).text(azh.switch_to_customizer);
                    azh.edit = false;
                } else {
                    $(azh.textarea).hide();
                    azh.add_code($(azh.textarea).val());
                    $(azh.editor).show();
                    $(switcher).text(azh.switch_to_html);
                    azh.edit = true;
                }
                return false;
            });
            $(textarea).before(switcher);
            if (azh.edit) {
                azh.add_code($(textarea).val());
                if ($(textarea).val().trim() == '') {
                    $('<div class="azh-empty-html">' + azh.empty_html + '</div>').appendTo(azh.editor);
                }
            }
        };
        azh.structure_refresh = function() {
            $('.azh-structure').empty();
            $(azh.editor).find('.azh-section').each(function() {
                var section_path = $('<div class="azh-section-path">' + $(this).data('section') + '</div>').appendTo($('.azh-structure'));
                $(section_path).data('section', this);
                $('<div class="azh-remove"></div>').appendTo(section_path).on('click', function() {
                    $(section_path).data('section').remove();
                    $(section_path).remove();
                    azh.store();
                });
            });
            $('.azh-structure').sortable({
                placeholder: 'azh-placeholder',
                forcePlaceholderSize: true,
                update: function(event, ui) {
                    var section = $(ui.item).data('section');
                    $(section).detach();
                    if ($(ui.item).next().length) {
                        var next_section = $(ui.item).next().data('section');
                        $(next_section).before(section);
                    } else {
                        if ($(ui.item).prev().length) {
                            var prev_section = $(ui.item).prev().data('section');
                            $(prev_section).after(section);
                        }
                    }
                    azh.store();
                },
                over: function(event, ui) {
                    ui.placeholder.attr('class', ui.helper.attr('class'));
                    ui.placeholder.removeClass('ui-sortable-helper');

                    ui.placeholder.attr('style', ui.helper.attr('style'));
                    ui.placeholder.css('position', 'relative');
                    ui.placeholder.css('z-index', 'auto');
                    ui.placeholder.css('left', 'auto');
                    ui.placeholder.css('top', 'auto');

                    ui.placeholder.addClass('azh-placeholder');
                }
            });
            if ($('.azh-structure').length) {
                $('.azh-structure').scrollTop($('.azh-structure')[0].scrollHeight);
            }
        }
        azh.library_init = function() {
            $('.azh-add-section').off('click').on('click', function() {
                if ($('.azh-library').css('display') == 'none') {
                    $('.azh-structure').animate({
                        'max-height': "100px"
                    }, 400, function() {
                        $('.azh-structure').scrollTop($('.azh-structure')[0].scrollHeight);
                    });
                    $('.azh-library').slideDown();
                    $(this).text($(this).data('close'));
                } else {
                    $('.azh-structure').animate({
                        'max-height': "600px"
                    }, 400);
                    $('.azh-library').slideUp();
                    $(this).text($(this).data('open'));
                }
                return false;
            });
            $('.azh-categories').off('change').on('change', function() {
                $('.azh-sections .azh-section').hide();
                if ($(this).val() == '') {
                    $('.azh-sections .azh-section').show();
                } else {
                    $('.azh-sections .azh-section[data-path^="' + $(this).val() + '"]').show();
                }
            });
            $('.azh-sections .azh-section').off('click').on('click', function() {
                var preview = this;
                $(azh.editor).find('.azh-empty-html').remove();
                $.get($(preview).data('url'), function(data) {
                    if ('editor' in azh) {
                        var section_exists = false;
                        data = data.replace(/{{azh-uri}}/g, $(preview).data('dir-uri'));
                        $(azh.options.section_patterns).each(function() {
                            var section_pattern = null;
                            if (typeof this == 'string') {
                                section_pattern = new RegExp(this, 'gi');
                            } else {
                                section_pattern = new RegExp(this);
                            }
                            var match = null;
                            while ((match = section_pattern.exec(data)) != null && match.length == 2) {
                                section_exists = true;
                            }
                        });
                        if (section_exists) {
                            azh.add_code(data);
                        } else {
                            azh.add_code('<div data-section="' + $(preview).data('path') + '">' + data + '</div>');
                        }
                    }
                });
                return false;
            });
        }
        azh.library_init();
    });
    $(document).off('azh-process.dialog').on('azh-process.dialog', function(sender, data) {
        function hide(elements) {
            $(elements).each(function() {
                var visible = false;
                $(this).children().each(function() {
                    if ($(this).css('display') != 'none') {
                        visible = true;
                        return false;
                    }
                });
                if (!visible) {
                    $(this).hide();
                }
            });
        }
        for (var i = data.added_from; i < data.tree.children.length; i++) {
            var added_node = data.tree.children[i];
            $(added_node.wrapper).find('.azh-text').each(function() {
                if ($(this).children().length === 0) {
                    $(this).hide();
                }
            });
            hide($(added_node.wrapper).find('.azh-text'));
            hide($(added_node.wrapper).find('.indent'));
            hide($(added_node.wrapper).find('.azh-line'));

            $(added_node.wrapper).find('.azh-editable:not(.azh-group-title)').each(function() {
                var editable = this;
                var control = $(editable).wrap('<div class="azh-control"></div>').parent();
                $(control).prepend('<div class="azh-description">' + ($(editable).data('description') ? $(editable).data('description') : '') + '</div>');
                $(editable).on('mousedown', function(event) {
                    if (event.which == 2) {
                        var description = prompt(azh.description, $(this).closest('.azh-editable').data('description'));
                        if (description != null) {
                            $(this).closest('.azh-editable').data('description', description);
                            $(this).closest('.azh-control').find('.azh-description').text(description);
                            azh.store();
                        }
                        return false;
                    }
                });
            });
            $(added_node.wrapper).find('.azh-editable.azh-group-title').each(function() {
                var editable = this;
                var control = $(editable).wrap('<div class="azh-group-title-control"></div>').parent();
                $(control).prepend('<div class="azh-description">' + ($(editable).data('description') ? $(editable).data('description') : '') + '</div>');
                $(control).on('mousedown', function(event) {
                    if (event.which == 2) {
                        var description = prompt(azh.description, $(this).find('.azh-editable').data('description'));
                        if (description !== null) {
                            $(this).find('.azh-editable').data('description', description);
                            $(this).find('.azh-description').text(description);
                            azh.store();
                        }
                        return false;
                    }
                });
            });

        }
    });
    $(document).off('azh-process.grid').on('azh-process.grid', function(sender, data) {
        function grid_editor(grid) {
            var prev_column = false;
            $(grid).find('> [class*="azh-col-"]').each(function() {
                var column = this;
                var column_width = $(column).find('> .azh-line > .azh-text > .azh-column-width');
                if (prev_column) {
                    var resizer = $('<div class="azh-width-resizer"></div>').appendTo(prev_column);
                    $(resizer).data('next-column', column);
                    $(resizer).on('mousedown', function(e) {
                        $(resizer).addClass('drag');
                        $(resizer).data('pageX', e.pageX);
                        $(this).closest('.azh-grid').addClass('drag');
                        e.preventDefault();
                        e.stopPropagation();
                        return false;
                    });
                }
                prev_column = column;
            });
            $(grid).on('click', function(e) {
                if ($(this).is('.drag')) {
                    e.preventDefault();
                    e.stopPropagation();
                    return false;
                }
            });
            $(grid).on('mouseup', function(e) {
                $(this).removeClass('drag');
                $(this).find('.azh-width-resizer.drag').removeClass('drag');
                e.preventDefault();
                e.stopPropagation();
                return false;
            });
            $(grid).on('mousemove', function(e) {
                if (e.buttons == 0) {
                    $(this).removeClass('drag');
                }
                var resizer = $(this).find('.azh-width-resizer.drag');
                if ($(this).is('.drag') && resizer.length) {
                    var column = $(resizer).closest('[class*="azh-col-"]');
                    var current_width = parseInt($(column).attr('class').match(/azh-col-(\d+)/)[1], 10);
                    var column_width = false;
                    $(column).find('> .azh-line .azh-column-width').each(function() {
                        if ($(this).data('column-width') == current_width) {
                            column_width = this;
                        }
                    });
                    var next_column = $(resizer).data('next-column');
                    var next_current_width = parseInt($(next_column).attr('class').match(/azh-col-(\d+)/)[1], 10);
                    var next_column_width = false;
                    $(next_column).find('> .azh-line .azh-column-width').each(function() {
                        if ($(this).data('column-width') == next_current_width) {
                            next_column_width = this;
                        }
                    });
                    if (e.pageX < $(resizer).offset().left && e.pageX < $(resizer).data('pageX')) {
                        if (current_width > 1) {
                            $(column).removeClass('azh-col-' + current_width);
                            $(column).addClass('azh-col-' + (current_width - 1));
                            $(column_width).data('column-width', current_width - 1);

                            $(next_column).removeClass('azh-col-' + next_current_width);
                            $(next_column).addClass('azh-col-' + (next_current_width + 1));
                            $(next_column_width).data('column-width', next_current_width + 1);
                            azh.store();
                        }
                    } else {
                        if (e.pageX > ($(resizer).offset().left + $(resizer).width()) && e.pageX > $(resizer).data('pageX')) {
                            if (next_current_width > 1) {
                                $(column).removeClass('azh-col-' + current_width);
                                $(column).addClass('azh-col-' + (current_width + 1));
                                $(column_width).data('column-width', current_width + 1);

                                $(next_column).removeClass('azh-col-' + next_current_width);
                                $(next_column).addClass('azh-col-' + (next_current_width - 1));
                                $(next_column_width).data('column-width', next_current_width - 1);
                                azh.store();
                            }
                        }
                    }
                }
                $(resizer).data('pageX', e.pageX);
                e.preventDefault();
            });
        }
        for (var i = data.added_from; i < data.tree.children.length; i++) {
            var added_node = data.tree.children[i];
            $(added_node.wrapper).find('[class*="azh-col-"]').each(function() {
                $(this).parent().addClass('azh-grid');
            });
            if ($(added_node.wrapper).is('.azh-grid')) {
                grid_editor(added_node.wrapper);
            }
            $(added_node.wrapper).find('.azh-grid').each(function() {
                grid_editor(this);
            });
        }
    });
    $(document).off('azh-process.isotope').on('azh-process.isotope', function(sender, data) {
        function grid_filters_rebuild(gf) {
            var labels = {};
            $(gf.items_wrapper).find('.azh-item').each(function() {
                var item = this;
                var item_filters = $(item).data('item_filters') || {};
                for (var filter in item_filters) {
                    if (filter.length > 0) {
                        labels[filter] = gf.labels[filter];
                    }
                }
            });
            gf.labels = labels;
            for (var i = 0; i < gf.lines.length; i++) {
                $(gf.lines[i]).remove();
            }
            gf.lines = [];
            var current_line = gf.all;
            for (var c in gf.labels) {
                var filter = $(gf.all).clone(true);
                gf.lines.push(filter);
                $(current_line).after(filter);
                current_line = filter;

                $(filter).find('.azh-text').each(function() {
                    if ($(this).data('get_code')) {
                        var code_line = $(this).data('get_code').call(this);
                        if (code_line.match(/data-filter=['"]\*['"]/i)) {
                            code_line = code_line.replace(/data-filter=['"]\*['"]/i, 'data-filter=".' + c + '"');
                            $(this).data('code_line', code_line);
                            $(this).html(htmlEncode(code_line));
                        }
                    }
                });

                var label = $(filter).find('.azh-content');
                $(label).data('content', gf.labels[c]);
                $(label).text(gf.labels[c]);
            }
        }
        for (var i = data.added_from; i < data.tree.children.length; i++) {
            var added_node = data.tree.children[i];

            var grid_filters = {
                lines: [],
                labels: {}
            };
            $(added_node.wrapper).find('.azh-wrapper').each(function() {
                var wrapper = this;
                var code_line = $($(wrapper).data('node').open_line).data('get_code').call($(wrapper).data('node').open_line);
                if (code_line.match(/data-isotope-filters/i)) {
                    grid_filters.filters_wrapper = wrapper;
                    $(wrapper).find('.azh-line').each(function() {
                        var line = this;
                        var code_line = $(line).data('get_code').call(line);
                        var match = code_line.match(/data-filter=['"]\.([\w\d-_]+)['"]/i);
                        if (match) {
                            var w = $(line).parentsUntil(wrapper, '.azh-wrapper').last();
                            if (w.length > 0) {
                                grid_filters.lines.push(w);
                            } else {
                                grid_filters.lines.push(line);
                            }
                            var label = $(grid_filters.lines[grid_filters.lines.length - 1]).find('.azh-content');
                            $(label).attr('contenteditable', 'false');
                            grid_filters.labels[match[1]] = $(label).data('get_code').call(label);
                        } else {
                            if (code_line.match(/data-filter=['"]\*['"]/i)) {
                                var w = $(line).parentsUntil(wrapper, '.azh-wrapper').last();
                                if (w.length > 0) {
                                    grid_filters.all = w;
                                } else {
                                    grid_filters.all = line;
                                }
                                var label = $(grid_filters.all).find('.azh-content');
                                $(label).attr('contenteditable', 'false');
                            }
                        }
                    });
                    $(wrapper).find('.azh-controls .azh-clone, .azh-controls .azh-remove').hide();
                }
                if (code_line.match(/data-isotope-items/i)) {
                    $(wrapper).addClass('azh-items');
                    grid_filters.items_wrapper = wrapper;
                    $(wrapper).data('filters', grid_filters);
                    $(wrapper).find('.azh-wrapper').each(function() {
                        var item = this;
                        $(item).addClass('azh-item');
                        var code_line = $($(item).data('node').open_line).data('get_code').call($(item).data('node').open_line);
                        var match = code_line.match(/class=['"]([ \w-_]+)['"]/i);
                        if (match) {
                            var classes = match[1].split(' ');
                            for (var filter_class in grid_filters.labels) {
                                if (classes.indexOf(filter_class) >= 0) {
                                    var item_filters = $(item).data('item_filters') || {};
                                    item_filters[filter_class] = true;
                                    $(item).data('item_filters', item_filters);
                                }
                            }
                        }
                        if ($(item).data('item_filters') || $(item).parentsUntil(wrapper, '.azh-wrapper').length == 0) {
                            $('<div class="azh-filters"></div>').appendTo($(item).find('.azh-controls')).on('click', function(event) {
                                var item = $(this).closest('.azh-item');
                                if ($(item).find('.azh-tags-dialog').length) {
                                    $(item).find('.azh-tags-dialog button').click();
                                } else {
                                    var gf = $(this).closest('.azh-items').data('filters');
                                    var dialog = $('<div class="azh-tags-dialog"></div>').appendTo($(item).find('.azh-controls'));
                                    var tags = $('<textarea></textarea>').appendTo(dialog);
                                    var item_filters = $(item).data('item_filters') || {};
                                    $(tags).val($.map(Object.keys(item_filters), function(val, i) {
                                        return grid_filters.labels[val];
                                    }).join("\n"));

                                    var done = $('<button class="button">' + azh.done + '</button>').appendTo(dialog).on('click', function() {
                                        var item = $(this).closest('.azh-item');
                                        var item_filters = {};
                                        var filters = {};
                                        var labels = $(tags).val().split("\n");
                                        for (var i = 0; i < labels.length; i++) {
                                            var filter = $.trim(labels[i].toLowerCase());
                                            if (filter != '') {
                                                filter = filter.replace(/[^\w]/g, '-');
                                                item_filters[filter] = true;
                                                gf.labels[filter] = labels[i];
                                            }
                                        }
                                        var old_item_filters = $(item).data('item_filters') || {};
                                        $(item).data('item_filters', item_filters);


                                        $($(item).data('node').open_line).find('.azh-text, .azh-hide').each(function() {
                                            if ($(this).data('get_code')) {
                                                var code_line = $(this).data('get_code').call(this);
                                                var match = code_line.match(/class=['"]([ \w-_]+)['"]/i);
                                                if (match) {
                                                    var classes = match[1].split(' ');
                                                    var diff = $(classes).not(Object.keys(old_item_filters)).get();
                                                    classes = diff.concat(Object.keys(item_filters));
                                                    code_line = code_line.replace(match[0], 'class="' + classes.join(' ') + '"');
                                                    if ($(this).is('.azh-hide')) {
                                                        $(this).data('content', code_line);
                                                    } else {
                                                        if ($(this).is('.azh-text')) {
                                                            $(this).data('code_line', code_line);
                                                        }
                                                    }
                                                    $(this).html(htmlEncode(code_line));
                                                }
                                            }
                                        });

                                        $(dialog).remove();
                                        grid_filters_rebuild(gf);
                                        azh.store();
                                        return false;
                                    });
                                }
                                return false;
                            });
                        }
                    });
                }
                if (code_line.match(/class="grid-sizer"/i) || $(wrapper).find('.azh-editable').length == 0) {
                    $(wrapper).hide();
                }
            });
        }
    });
}(window.jQuery);