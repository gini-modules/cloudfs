/*

require(['cloudfs'], function(CloudFS) {
    CloudFS.dropbox({
        server: 'xxx',
        container: element,
        progress: function() {},
        success: function() {},
        error: function() {},
        always: function() {}
    });
});
*/
define('cloudfs', ['jquery'], function($) {

    var upload = function(data, config, handler) {

        var that = this;

        var form = new FormData();
        var k;
        for (k in data) {
            form.append(k, data[k]);
        }

        if ($.isPlainObject(config.params)) {
            for (k in config.params) {
                form.append(k, config.params[k]);
            }
        }

        var xhr = new XMLHttpRequest();

        xhr.upload.addEventListener('progress', function(evt) {
            var info = false;
            if (evt.lengthComputable) {
                info = {
                    total: evt.total
                    ,percent: Math.round(evt.loaded * 100 / evt.total)
                };
            }
            if (handler.progress) handler.progress(info, xhr);
        }, false);

        xhr.addEventListener('load', function(evt) {
            var status = evt.target.status;
            if (status == 200) {
                var data = JSON.parse(xhr.responseText);
                $.post('ajax/cloudfs/uploaded', {
                    server: that.server
                    ,data: data
                }, function(data) {
                    if (handler.success) handler.success(data, xhr);
                    if (handler.always) handler.always(evt, xhr);
                });
            }
        }, false);

        xhr.addEventListener('error', function(evt) {
            if (handler.error) handler.error(evt, xhr);
            if (handler.always) handler.always(evt, xhr);
        }, false);

        xhr.addEventListener('abort', function(evt) {
            if (handler.abort) handler.abort(xhr);
            if (handler.always) handler.always(evt, xhr);
        }, false);

        xhr.open('POST', config.url);
        xhr.send(form);
    };

    var CloudFS = function(server) {
        this.configURL = 'ajax/cloudfs/config';
        this.server = server || '';
        this.handlers = {};
    };

    CloudFS.prototype.upload = function(data, handler) {
        var that = this;
        $.get(that.configURL, {
            server: that.server
            ,file: {
                name: data.file.name
                ,size: data.file.size
                ,type: data.file.type
            }
        }, function(config) {
            var mHandlers = handler || {};
            var tHandlers = that.handlers || {};
            var rHandlers = $.extend(tHandlers, mHandlers);
            upload.call(that, data, config || {}, rHandlers);
        });

        return this;
    };

    CloudFS.prototype.progress = function(method) {
        this.handlers.progress = method;
    };

    CloudFS.prototype.abort = function(method) {
        this.handlers.abort = method;
    };

    CloudFS.prototype.error = function(method) {
        this.handlers.error = method;
    };

    CloudFS.prototype.success = function(method) {
        this.handlers.success = method;
    };

    CloudFS.prototype.always = function(method) {
        this.handlers.always = method;
    };

    function _supportDragAndDrop() {
        var div = document.createElement('div');
        return ('draggable' in div) || ('ondragstart' in div && 'ondrop' in div);
    }

    return {
        upload: function(server, file, handlers) {
            var cfs = new CloudFS(server);
            return cfs.upload(file, handlers);
        }
        ,dropbox: function(opt) {
            if (!_supportDragAndDrop()) return;

            opt = opt || {};
            opt.server = opt.server || '';

            function uploadFiles(files) {
                if (!files.length) return;
                var cfs = new CloudFS(opt.server);
                if (opt.start) {
                    var canContinue = opt.start.call(that);
                    if (canContinue===false) return;
                }
                for (var i = 0; i < files.length; i++) {
                    var file = files[i];
                    cfs.upload({
                        file: file
                    }, {
                        progress: opt.progress
                        ,abort: opt.abort
                        ,error: opt.error
                        ,success: opt.success
                        ,always: opt.always
                    });
                }
            }

            var that = opt.container;
            var $el = $(opt.container);

            var $eleFile = $('<input type="file"/>');
            $eleFile.hide();
            $eleFile.on('change', function(evt) {
                uploadFiles($(this).get(0).files);
            });
            $el.before($eleFile);

            var clickHandler = opt.clickHandler;
            if (!clickHandler) {
                $el.on('click', function(evt) {
                    $eleFile.click();
                });
            }
            else {
                $el.find(clickHandler).on('click', function(evt) {
                    $eleFile.click();
                });
            }

            $el.on('dragover', function(evt) {
                evt.preventDefault();
                evt.stopPropagation();
                opt.dragover && opt.dragover.call(that, evt);
            }).on('dragenter', function(evt) {
                evt.preventDefault();
                evt.stopPropagation();
                opt.dragenter && opt.dragenter.call(that, evt);
            }).on('dragleave', function(evt) {
                evt.preventDefault();
                evt.stopPropagation();
                opt.dragleave && opt.dragleave.call(that, evt);
            }).on('drop', function(evt) {
                evt.preventDefault();
                evt.stopPropagation();
                opt.leave && opt.leave.call(that, evt);

                var files = evt.originalEvent.dataTransfer.files;
                uploadFiles(files);
            });
        }
    };

});

