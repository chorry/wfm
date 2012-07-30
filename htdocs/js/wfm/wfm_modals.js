ModalViewConfig = function(configData) {
    var configText = '<table>';
    $.each(configData, function(k,v){
        configText += '<tr><td>' + k + ' </td><td> ' + v + '</td></tr>';
    });
    configText += '</table>';
    var modalHtml =
        '<div class="modal" id="myModal" style="display: block;"><div class="modal-header">' +
            '<h3>'+lang.modal_current_config_header+'</h3></div>' +
            '<div class="modal-body">' + configText +
            '</div><div class="modal-footer"><a class="btn" data-dismiss="modal" >'+ lang.button_ok +'</a></div></div>';
    $('body').append(modalHtml);

    $('#myModal .btn-primary').bind(
        'click', function (e) {
    });

    $('#myModal').modal({keyboard:true, backdrop: 'static'});
    $('#myModal').on('hidden', function () {
        $(this).remove();
    });

};

ModalUpload = function () {
    var modalHtml =
        '<div class="modal" id="myModal" style="display: block;"><div class="modal-header">' +
            '<h3>'+lang.modal_file_upload_header+'</h3></div>' +
            '<div class="modal-body">' +
            '<input type=file multiple  id="fileUploadContainer">' +
            '</div><div class="modal-footer"><a class="btn" data-dismiss="modal" >'+ lang.button_cancel +'</a><a href="#" class="btn btn-primary" uploadtarget = "fileUploadContainer" >'+lang.button_upload+'</a></div></div>';
    $('body').append(modalHtml);

    $('#fileUploadContainer').bind('change',
        function (e) {

        }
    );

    $('#myModal .btn-primary').bind(
        'click', function (e) {
            uploader.setParams({
                'dir_id':selectedDir,
                'current_dir': currentDir
            })
                .setUploadUrl(baseUrl + '/manageFiles/?action=upload&type=file&name=' + currentDir)
                .setFileList($('#fileUploadContainer'))
                .setCallbacks({
                    'load':function (e) {
                        //add uploaded file to filelist
                        manager.dirList(currentDir);
                        //oh, so lame:
                        $('#myModal').modal('hide');
                        var resp = '';
                        if (e.target.responseText) {
                            resp = e.target.responseText;
                        } else {
                            //suppose its iframe result
                            resp = $('#' + e.target.id).contents().find('body').html();
                        }

                        var result = $.parseJSON(resp);
                        if (result.data == 'true')
                        {
                            var noteId = notes.create({noteContent:'Upload complete for ' + uploader.getLastUploadedFileName(e), className:'alert-success'});
                        }
                        else
                        {
                            var noteId = notes.create({noteContent: result.error + uploader.getLastUploadedFileName(e), className:'alert-warning'});
                        }
                    },
                    'progress':function (e) {
                    }
                })
                .Upload(e)
        }
    );

    $('#myModal').modal({keyboard:true, backdrop: 'static'});
    //$('#myModal').modal({backdrop:false});
    $('#myModal').on('hidden', function () {
        $(this).remove();
    });
}

ModalEditor = function (params) {
    var modalParams = $.extend({
        header:'Default header',
        actionEvent:null,
        text:null
    }, params);

    //text manipulation:
    var text = ( modalParams.text.replace(/\\n?\\r|\\n/g, "<br />\n") ) ;
        text = text.replace(/\\t/g,"\t");

    var modalHtml =
        '<div class="modal modal_editor" id="myModal" style="display: block;"><div class="modal-header">' +
            '<h3>' + modalParams.header + '</h3></div>' +
            '<div class="modal-body modal_editor_body"><textarea id="file_content" rows=20 class="boxsizingBorder">' +
            (text == 'null' ? '' : text ) +
            '</textarea></div><div class="modal-footer"><a class="btn" data-dismiss="modal" >'+lang.button_cancel+'</a><a href="#" class="btn btn-primary">'+lang.button_save+'</a></div></div>';
    $('body').append(modalHtml);

    //handle tabs in editor
    $('#file_content').keydown(function (e) {
        if (e.keyCode == 9) {
            var myValue = "\t";
            var startPos = this.selectionStart;
            var endPos = this.selectionEnd;
            var scrollTop = this.scrollTop;
            this.value = this.value.substring(0, startPos) + myValue + this.value.substring(endPos,this.value.length);
            this.focus();
            this.selectionStart = startPos + myValue.length;
            this.selectionEnd = startPos + myValue.length;
            this.scrollTop = scrollTop;

            e.preventDefault();
        }
    });

    $('#myModal .btn-primary').bind(
        'click', function () {
            manager.fileEditSave($('#file_content').val());
        }
    );

    $('#myModal').modal({keyboard:true, backdrop: 'static'});
    $('#myModal').on('hidden', function () {
        $(this).remove();
    });
};

Modal = function (params) {
    var modalParams = $.extend({
        header:'Default header',
        text:'',
        actionEvent:null,
        inputValue:null,
        hideInput:null
    }, params);

    if (modalParams.hideInput == null)
    {
        modalParams.text += ' <input type="text" class="input-xlarge" id="modalInput">';
    }
    else {
        modalParams.text += '<input type="hidden" class="input-xlarge" id="modalInput" value="' + modalParams.inputValue + '">';
    }

    var modalHtml =
        '<div class="modal" id="myModal" style="display: block;"><div class="modal-header">' +
            '<h3>' + modalParams.header + '</h3></div>' +
            '<div class="modal-body">' +
            '<p>' + modalParams.text + '</p>' +
            '</div><div class="modal-footer"><a class="btn" data-dismiss="modal" >'+lang.button_cancel+'</a><a href="#" class="btn btn-primary">'+lang.button_save+'</a></div></div>';
    $('body').append(modalHtml);

    $('#myModal .btn-primary').bind(
        'click', function () {
            modalParams.actionEvent($('#modalInput').val());
        }
    );

    $('#myModal').modal({keyboard:true, backdrop: 'static'});
    $('#myModal').on('hidden', function () {
        $(this).remove();
    });
}