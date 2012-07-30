/**
 * Simple sticky note
 */

(function ($) {
    $.fn.stickyNote = function (o) {
        var id = 0;
        var stickers = $('#jquery-stickers');
        var stickyNoteIdName = 'noteStickerId_';
        var defaultTtl = 1500;
        var defaultSpeed = 'slow';
        var objects = {};

        return {
            create:function (stick, noteType) {
                var stick = $.extend({
                    time:defaultTtl,
                    speed:defaultSpeed,
                    noteContent:null,
                    className:null,
                    header:null,
                    id:null, //айди дива с сообщением
                    extended: false, //жить неограниченное время, пока не убьют принудительно
                    sticked:false, // не выводить кнопку закрытия сообщения
                    position:{top:0, right:0} // позиция по умолчанию - справа сверху
                }, stick);

                if (noteType == 'error') stick.className="alert-error";

                if (stick.id == null) {
                   stick.id = id += 1;
                }

                //init storage div, if not exists
                if (!stickers.length) {
                    $('body').append('<div id="jquery-stickers"></div>');
                    stickers = $('#jquery-stickers');
                }

                //create sticker itself
                stickers.css('position', 'fixed').css({right:'auto', left:'auto', top:'auto', bottom:'auto'}).css(stick.position);
                var stickItem = $('<div class="alert"></div>');
                stickers.append(stickItem);
                if (stick.className) stickItem.addClass(stick.className);
                stickItem.attr('id', stickyNoteIdName + stick.id);
                stickItem.html(stick.noteContent);

                if (stick.extended)
                {
                    if (stick.sticked)
                    {
                        var exit = $('<a class="close">x</a>');
                        stick.prepend(exit);
                        exit.click(function () {
                            stickItem.fadeOut(stick.speed, function () {
                                $(this).remove();
                            })
                        });
                    }
                }
                else
                {
                    this.closeWithFadeOut(stick.id, stick.time)
                }
                return stick.id;
            },


            closeWithFadeOut:function(id, ttl)
            {
                if (typeof ttl == 'undefined')
                {
                   var ttl = defaultTtl;
                }
                var stickItem = $('#'+stickyNoteIdName+id);
                setTimeout(function () {
                    stickItem.fadeOut(defaultSpeed, function () {
                        $(this).remove();
                    });
                }, ttl);
            },

            /**
             * Updates content of existent sticker
             * @param id int
             * @param params object
             */
            update:function (id, params) {

                //get sticker by id
                var sticker = $('#'+stickyNoteIdName+id);
                if (!sticker.length) {
                    return false;
                }
                if (typeof params.noteContent != 'undefined')
                {
                    sticker.html(params.noteContent);
                }

                if (typeof params.extended != 'undefined')
                {
                    if (params.extended == false)
                    {
                        this.closeWithFadeOut(id);
                    }
                }
            },

            destroy:function (id) {
                var sticker = $('#'+stickyNoteIdName+id);
                if (!sticker.length) {
                    return false;
                }
                sticker.remove();
            }
        }
    };
})(jQuery);