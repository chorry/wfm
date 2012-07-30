/**
 * TODO:
 * отправлять куки;
 * ограничить количество доступных файлов для выбора
 * файлы собираются в группы до определенного количества мегабайт, или до определенного количества файлов в группе;
 * отправлять методом POST любые параметры вместе с файлом; [+]
 * предоставлять возможность выбора сразу нескольких файлов; [+]
 * отправлять файлы группами [+];
 * поддержка IE6 (via iframe) [+]
 *
 * TODO:
 * при отправке пачки толстых файлов - они улетают на сервер и грузятся какое-то время.
 * если отправить потом еще пачку файлов - то айдишники новых батчей перезапишут айдишники отправленных.
 * надо обьект с номерами сессий отправки
 *
 * Как пользоваться
 */

function FileUploader(options) {
    /*
     * checks if browser supports required html5 objects
     * @return bool
     */
    this.CheckBrowser = function () {
        if (window.File && window.FileList && this.mode == 'html5') return true; else return false;
        //fileReader and Blob are yet to be implemented
    }

    /**
     * file upload
     * @param e
     * @return {*}
     * @constructor
     */
    this.Upload = function (e) {
        //check for method
        if (this.CheckBrowser()) {
            return this.uploadHTML5(e);
        }
        else
        {
            return this.uploadIFrame(e);
        }
    }

    this.uploadHTML5 = function (e) {
        //this.setCallbacks();
        if (typeof this.callbacks == 'undefined') {
            //console.log('undef');
        }

        var maxFilesPerBatch = 2;
        if (this.fileList == null) {
            this.fileList = this.initFiles(e);
        }
        //remember session Id
        var thisSessionId = Math.random() * 1000;
        this.sessionBatches[thisSessionId] = 0;

        //upload files in batches, if any
        for (i = 0; i < this.fileList.length; i = i + maxFilesPerBatch)
        {
            //console.log('sending batch' + i);
            var batchFileList = this.fileList.slice(i, i + maxFilesPerBatch);
            var batchXhr = this.uploadBatch(batchFileList);
            this.registerCallbacks(
                this.callbacks,
                batchXhr);

            //register extra callback to count till last batch
            this.registerCallbacks({
                'load':this.updateBatchCount(thisSessionId)
            }, batchXhr);

        }
        this.fileList = null;
        return this;
    }

    this.uploadIFrame = function (e) {
        //setup ajax params
        var ajaxParams = {};
        $.each(this.formSettings, function (k, v) {
            ajaxParams[k] = v;
        });
        ajaxParams['url'] = this.uploadUrl;
        ajaxParams.success = function(data, status) {
            if(typeof(data.error) != 'undefined')
       		{
       		    if(data.error != '') { alert(data.error); } else { alert(data.msg); }
       		}
        };


        var id = new Date().getTime()

        //try to find file input
        var fileElementId;
        if ( $(e.target).attr('type') != 'submit')
        {
            if (typeof $(e.target).attr('uploadtarget') !== 'undefined')
            {
                fileElementId = $(e.target).attr('uploadtarget');
            }
            else
            {
                if ( typeof $(e.target).attr('id') == 'undefined')
                {
                    fileElementId = Math.floor(Math.random()*100) + 1;
                    $(e.target).attr('id',fileElementId);
                }
                else
                {
                    fileElementId = $(e.target).attr('id');
                }
            }
        }
        else
        {
            fileElementId = $(e.target).parent().find('input[type=file]').attr('id');
        }

        this.createUploadForm(id, fileElementId);
        this.createUploadIframe(id);

        var formId = this.formName + id;
        var frameId = this.frameName + id;

        document.getElementById(formId).onsubmit=function() {
      		document.getElementById(formId).target = frameId;
      	}

        var ieCallbacks = this.callbacks;
        var fThis = this;
        var fId = id;

        var iframe = $('#' + frameId).load( function(e) {
            var response = iframe.contents().find('body').html();

            iframe.unbind('load');
            /**
             * call load callback;
             */
            ieCallbacks.load(e);
            fThis.destroyFrameAndForm(fId);
            //destroy form and frame
        });

        var form = $('#' + formId);
     	$(form).attr('action', this.uploadUrl);
     	$(form).attr('method', 'POST');
     	//$(form).attr('target', frameId);
     	$(form).attr('encoding', 'multipart/form-data');
        $(form).submit();

    }

    /**
     * Sets custom callbacks for xhr events
     * @param customCallbacks object
     */
    this.setCallbacks = function (customCallbacks) {
        this.callbacks = $.extend({
            'loadstart':function (e) {
                console.log('loadstart');
            },
            'load':this.uploadComplete,
            'error':this.uploadFailed,
            'abort':this.uploadCancelled,
            'progress':this.trackUploadProgress
            //xhr.addEventListener("load", this.uploadComplete, false); //when last file is uploaded
        }, customCallbacks);
        return this;
    }

    /**
     * Attaches callback to specified events for _existant_ xhr object
     * @param callbackList like {event: callback, ...}
     */
    this.registerCallbacks = function (callbackList, xhrObject) {
        var fU = this;
        $.each(callbackList, function (event, callbackFunc) {
            if (event == 'progress') {
                xhrObject.upload.addEventListener(event, callbackFunc);
            }
            else {
                xhrObject.addEventListener(event, callbackFunc);
            }
        });
    }

    this.uploadBatch = function (fileList) {
        var xhr = new XMLHttpRequest();
        fd = this.makeFormData(fileList);

        xhr.open("POST", this.uploadUrl);
        xhr.fuFileList = fileList; //save filenames so we can access them later. TODO:DOESNT WORK IN FIREFOX
        xhr.upload.addEventListener("progress", function (e) {
        }, false); //register at least one event, so we can attach events later
        xhr.send(fd);
        return xhr;
    }

    this.trackUploadProgress = function (e) {
        if (e.lengthComputable) {
            var percentComplete = Math.round(e.loaded * 100 / e.total);
            return percentComplete.toString();
        }
    }

    this.updateBatchCount = function (e, text) {
        //console.log('----');
    }

    this.uploadComplete = function (e) {

        console.log('normal handling');
        return 'all ok';
    }

    this.uploadFailed = function (e) {
    }

    this.uploadCancelled = function (e) {
    }

    /**
     * returns filename of uploaded file
     * @param e
     */
    this.getLastUploadedFileName = function (e) {
        if (this.CheckBrowser())
        {
            return e.target.fuFileList[(e.target.fuFileList.length - 1)].name
        }
        else
        {
            //if its iframe
            return 'file';
        }
    }

    /**
     * sets filelist by input file type
     * @param fileInputId   id of file input element
     * @return {*}
     */
    this.setFileList = function (fileInputId) {
        this.fileList = [];

        if (this.CheckBrowser()) {
            if (fileInputId[0].files.length > 0) {
                var fileHolder = this.fileList;
                $.each(fileInputId[0].files, function (k, v) {
                    fileHolder.push(v);
                });
            }
            else {
                //console.log('bad file?');
                return false;
            }
        }
        else
        {
            var fName = $(fileInputId).val();
        }
        return this;
    }

    /**
     * Inits filelist on 'select' event. Used when uploader is attached to select button
     * @param e
     * @return {*}
     */
    this.initFiles = function (e) {
        var fileList = e.originalEvent.target.files;
        var fileHolder = [];
        if (fileList.length > 0) {
            //TODO: finish this part
            $.each(fileList, function (k, v) {
                fileHolder.push(v);
            });
        }
        else {
            console.log('bad file?');
            return false;
        }
        return fileHolder;
    }

    /**
     * creates FormData with provided data
     * @param data
     * @return {*}
     */
    this.makeFormData = function (data) {
        formdata = new FormData();

        $.each(data, function (k, v) {
            formdata.append(k, v);
        });

        //append nonf-file data
        $.each(this.formSettings, function (k, v) {
            formdata.append(k, v);
        });

        return formdata;
    }

    /**
     * sets form key=>value params
     * @param k
     * @param v
     * @return {*}
     */
    this.setParams = function (k, v) {
        if (typeof k == 'object') {
            var fileUploader = this; //create this reference for 'each' construction
            $.each(k, function (i, j) {
                fileUploader.formSettings[i] = j;
            });
        }
        else {
            this.formSettings[k] = v;
        }
        return this;
    }

    this.setUploadMode = function(val) {
        this.mode = val;
        return this;
    }

    this.setMultiple = function () {

    }

    this.resetParams = function () {
        this.formSettings = {};
    }

    this.setUploadUrl = function (url) {
        this.uploadUrl = url;
        return this;
    }

    /**
     * This part if for IE's iframe. U're gonna burn, MS!
     **/
    this.createUploadIframe = function (id)
    {
        //create frame
        var frameId = this.frameName + id;

		// Add the iframe.
		if (!$('#' + frameId).length)
		{
			$('body').append('<iframe id="' + frameId + '" name="' + frameId + '"  />');
            $('#' + frameId).hide();
		}
    }

    this.createUploadForm = function (id, fileElementId)
    {
        //create form
        var formId = this.formName + id;

        var formParams = '';
        $.each(this.formSettings, function(k,v) {
            formParams += '<input type="hidden" name="' + k + '" value="' + v + '">'
        })
        var form = $('<form  action="" method="POST" name="' + formId + '" id="' + formId + '" enctype="multipart/form-data">' + formParams + '</form>');

        var real = $('#' + fileElementId);
        var cloned = real.clone(true);
        cloned.insertAfter(real);
        if (typeof real.attr('name') == 'undefined')
        {
           real.attr('name',fileElementId);
        }
        real.appendTo(form);
        $(form).hide();
        $(form).appendTo('body');
    }

    this.destroyFrameAndForm = function(id)
    {
        $('#' + this.frameName + id).remove();
        $('#' + this.formName + id).remove();
        return;
    }

    this.getFormSettings = function()
    {
        return this.formSettings;
    }

    this.file = null; // file/filelist handler
    this.fileList = null;
    this.options = options;
    this.uploadUrl = '';
    this.formSettings = {}; //extra settings for form
    this.callbacks = null; //handle extra callbacks. you can add some more, or redefine default;
    this.sessionBatches = {}; //handle sessions for batches id

    this.mode = 'html5';
    this.frameName = 'chrFrame';
    this.formName = 'chrForm';
    this.fileName = 'chrFile';
};