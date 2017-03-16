(function($) {
    "use strict";
    window.azh = $.extend({}, window.azh);
    $(function() {
        azh.edit_links_refresh = function() {
            function show_edit_link(element) {
                $($(element).data('edit-link-control')).css({
                    "top": $(element).offset().top,
                    "left": $(element).offset().left,
                    "width": $(element).outerWidth(),
                    "height": $(element).outerHeight(),
                }).show();
            }
            function hide_edit_link(element) {
                $($(element).data('edit-link-control')).hide();
            }
            function is_visible(element) {
                var visible = true;
                if ($(window).width() < $(element).offset().left + $(element).outerWidth()) {
                    visible = false;
                }
                if (!$(element).is(":visible")) {
                    visible = false;
                }
                $(element).parents().each(function() {
                    var parent = this;

                    var elements = $(parent).data('elements-with-azh-edit-link');
                    if (!elements) {
                        elements = [];
                    }
                    elements = elements.concat($(element).get());
                    elements = $.unique(elements);
                    $(parent).data('elements-with-azh-edit-link', elements);

                    if ($(parent).css("display") == 'none' || $(parent).css("opacity") == '0' || $(parent).css("visibility") == 'hidden') {
                        visible = false;
                        $(parent).off('click.azh mouseenter.azh mouseleave.azh').on('click.azh mouseenter.azh mouseleave.azh', function() {
                            var elements = $(parent).data('elements-with-azh-edit-link');
                            $(elements).each(function() {
                                if (is_visible(this)) {
                                    show_edit_link(this);
                                } else {
                                    hide_edit_link(this);
                                }
                            });
                        });
                    }
                });
                return visible;
            }
            for (var links_type in azh.edit_links) {
                var selectors = Object.keys(azh.edit_links[links_type].links);
                selectors.sort(function(a, b) {
                    return b.length - a.length;
                });
                for (var i = 0; i < selectors.length; i++) {
                    var selector = selectors[i];
                    $(selector).each(function() {
                        if (!$(this).data('edit-link-control')) {
                            var control = $('<div data-edit-link-control=""><a href="' + azh.edit_links[links_type].links[selector] + '" target="' + azh.edit_links[links_type].target + '">' + azh.edit_links[links_type].text + '</a></div>').appendTo('body').css({
                                "top": "0",
                                "left": "0",
                                "width": "0",
                                "height": "0",
                                "z-index": "9999999",
                                "pointer-events": "none",
                                "position": "absolute"
                            }).hide();
                            control.find('a').css({
                                "display": "inline-block",
                                "padding": "5px 10px",
                                "color": "black",
                                "font-weight": "bold",
                                "background-color": "white",
                                "box-shadow": "0px 5px 5px rgba(0, 0, 0, 0.1)",
                                "pointer-events": "all"
                            }).on('mouseenter', function() {
                                $(this).parent().css("background-color", "rgba(0, 255, 0, 0.1)");
                                azh.edit_links_refresh();
                            }).on('mouseleave', function() {
                                $(this).parent().css("background-color", "transparent");
                            });
                            $(this).data('edit-link-control', control);
                            $(control).data('element', this);
                        }
                        if (is_visible(this)) {
                            show_edit_link(this);
                        } else {
                            hide_edit_link(this);
                        }
                    });
                }
            }
        };
        if ('azh' in window && 'edit_links' in azh) {
            $(window).on('resize.azh scroll.azh', _.throttle(function() {
                azh.edit_links_refresh();
            }, 1000));
            setTimeout(function() {
                azh.edit_links_refresh();
            }, 100);
        }
        $('#wp-admin-bar-edit-links').off('click.azh').on('click.azh', function(event) {
            if ($(this).is('.active')) {
                $(window).off('resize.azh scroll.azh');
            } else {
                $(window).on('resize.azh scroll.azh', function() {
                    azh.edit_links_refresh();
                });
            }
            event.preventDefault();
        });
    });
})(window.jQuery);