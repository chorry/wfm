returnMyName = function() {
  for (var o in this) {
    if (arguments.callee===this[o]) return o;
  }
};

ajaxRequest = function (action, type, name) {
    dataIn = [];
    return $.ajax({
        url:fmUrl + '?action=' + action + '&type=' + type + '&selected_dir=' + selectedDir + '&name=' + name,
        data:dataIn,
        type:'GET'
    });
}

FileManager = function FileManager() {

    var filelistData;
    var _self = this;

    this.getSelf = function(){
        return _self;
    }
    /**
     * Lazy signals
     * @param signalName
     */
    this.getSignal = function(signalName) {
        if (!(signalObj[signalName] instanceof signals.Signal))
        {
            signalObj[signalName] = new signals.Signal();
        }
        return signalName;
    }

//used for uniq signals id (where needed)
    this.getUniqId = function getUniqId() {
        try {
            return (arguments.callee.caller.name + Math.random() ).replace(/[\."']/g, '_');
        } catch (err) {
            return (Math.random() + Math.random() + "").replace(/[\."']/g, '_');
            //fucking ie doesnt know about my function
        }
    }


    this.doDelete = function (e) {
        if (selectedFile.name != null) {
            new Modal({
                header:lang.modal_deletefile_header,
                text: lang.modal_deletefile_body + selectedFile.name + ' ?',
                actionEvent:manager.fileDelete,
                hideInput: true,
                inputValue:selectedFile.name
            });
        }
        else
        {
            new Modal({
                header:lang.modal_deletedir_header,
                text: lang.modal_deletefile_body + currentDir + ' ?',
                actionEvent:manager.dirDelete,
                hideInput: true,
                inputValue:currentDir
            });
        }
    }

    this.dirDelete = function () {
        var result = ajaxRequest('delete', 'dir', currentDir);
        result.done(function (data) {
            $('#myModal').modal('hide'); //lame
            data = $.parseJSON(data);
            if (data.data == false)
            {
                notes.create({noteContent:data.error, className:'alert-error'});
            }
            else
            {
                currentDir = makeDir(currentDir, '..');
                manager.dirList(currentDir); //go up one level
            }
        });
        result.fail(function (data, xhrObject) {
            notes.create({noteContent:lang.note_error, className:'alert-error'});
        });
    }

    /**
     * Get file web-link
     * @param fName filename<br>
     * @optparam [suppress error]
     */
    this.doFileGetLink = function(fName) {
        var result = ajaxRequest('getlink', 'file', currentDir + '/' + fName);
        result.done(function (data) {
            try {
              var r = $.parseJSON(data);
              if (r.hasOwnProperty('data') && r.data != false && r.data.link != false)
              {
                  signalObj.gotFileLink.dispatch( r );
                  return stripslashes(r.data.link);
              } else {
                throw 'err';
              }
            } catch (e) {
                if (arguments[2] == '')
                {
                    notes.create({noteContent:r.error, className:'alert-error'});
                }
                signalObj.gotFileLink.dispatch( null );
            }
        });
        result.fail(function (data, xhrObject) {
            notes.create({noteContent:lang.note_error, className:'alert-error'});
            signalObj.gotFileLink.dispatch( null );
            return false;
        });
    }

    this.fileDelete = function (fName) {
        var result = ajaxRequest('delete', 'file', currentDir + '/' + fName);
        result.done(function (data) {
            $('#myModal').modal('hide'); //lame
            data = $.parseJSON(data);
            if (data.data == false)
            {
                notes.create({noteContent:data.error, className:'alert-error'});
            }
            else
            {
                manager.dirList( currentDir );
            }
         });
         result.fail(function (data, xhrObject) {
                         notes.create({noteContent:lang.note_error, className:'alert-error'});
         });
    }

    this.doRename = function () {
        if (selectedFile.name != '') {
            new Modal({
                header:lang.modal_renamefile_header,
                text:lang.modal_renamefile_body( selectedFile.name ),
                inputValue: selectedFile.name,
                actionEvent:manager.fileRename
            });
        }
        else {
            new Modal({
                header:lang.modal_renamedir_header,
                text:lang.modal_renamedir_body( currentDir ),
                inputValue: currentDir,
                actionEvent:manager.dirRename,
                inputValue:currentDir
            });
        }
    }

    this.fileRename = function (fNameNew) {
       var result = ajaxRequest('rename', 'file', makeDir(currentDir, selectedFile.name) + '&name2='+ makeDir(currentDir, fNameNew));
       result.done(function (data) {
           manager.dirList(currentDir);
           $('#myModal').modal('hide'); //lame
        });
        result.fail(function (data, xhrObject) {
           notes.create({noteContent:lang.note_error, className:'alert-error'});
        });
    }

    this.fileEdit = function (fName) {
        var result = ajaxRequest('getContent', 'file', makeDir(currentDir, fName));
        result.done(function (data) {
            try
            {
                //json only if error
                var datap = $.parseJSON(data);
                if (datap == data) {
                    throw 'no error :)';
                }
                notes.create({noteContent:datap.error, className:'alert-error'});
            } catch(err) {
                new ModalEditor({
                    header:fName,
                    //text: stripslashes(data),
                    text: data,
                    actionEvent:'manager.saveFileContent'
                });
            }

        });
        result.fail(function (data, xhrObject) {
            notes.create({noteContent:lang.note_error, className:'alert-error'});
        });
    }

    this.fileEditSave = function(fContent) {
        var action = 'saveContent';
        var type = 'file';
        var name = selectedFile.name; //makeDir(currentDir, selectedFile);

        var ajaxRequest = $.ajax({
                url:fmUrl + '?action=' + action + '&type=' + type + '&name=' + name + '&dir_id='+selectedDir+'&current_dir='+currentDir,
                data:{file_content: fContent},
                type:'POST'
            });

        ajaxRequest.done(function (data) {
            data = $.parseJSON(data);

            if (data.data == true)
            {
                notes.create({noteContent:lang.note_ok, className:'alert-success'});
                $('#myModal').modal('hide');
            }
            else
            {
                notes.create({noteContent:data.error, className:'alert-error'});
            }

        });

        ajaxRequest.fail(function (data, xhrObject) {
            notes.create({noteContent:lang.note_error, className:'alert-error'});
        });
    }

    /**
     * gets directory content
     * @param data
     * @return {*}
     */
    this.getDirList = function (data) {
        return this.dirList('');
    }

    /**
     * updates directory tree with provided data
     * @param data
     */
    this.updateDirList = function (data) {
        //todo: see how its done on http://www.thecssninja.com/demo/css_tree/
        var dirList = '<li><a href="#" class="fm_dir" dirname="..">..</a></li>';
        if (data != null) {
            if (typeof data == 'object')
            {
                $.each(data.sort(), function (k, v) {
                    dirList += '<li><a href="#" class="fm_dir" dirname="' + v + '"><i class="icon-folder-open"></i>&nbsp;' + v + '</a></li>';
                })
            }
        }
        $('#directory_tree ul').html(dirList);
    }

    /**
     * Accepts registry.filelistData
     * @param data
     */
    this.updateFileList = function (data) {
        signalObj[this.getSignal('updateFileList')].dispatch(data);
    }
}


FileManager.prototype.getBaseDirList = function getBaseDirList() {
        var th = this,
            ctx = arguments.callee.name,
            result = ajaxRequest('init', 'dir');
        var sigId = this.getUniqId();

        signalObj[signalObj.getSignal(ctx)].dispatch( 'start', sigId );


        result.done(function (data) {
            dataObject = $.parseJSON(data);
            if (dataObject != null && dataObject.hasOwnProperty('data') && dataObject.data !== null) {
                var dirList = '',
                    dir_id = 0;

                if (dataObject.data.dir != null) {
                    selectedDir = {};
                    $.each(dataObject.data.dir, function (k, v) {
                        selectedDirDesc[dir_id] = v;
                        dirList += '<li><a href="#" class="fm_dir" dirname="" dir_id="'+ dir_id +'"><i class="icon-folder-open"></i>&nbsp;' + v + '</a></li>';
                        dir_id  += 1;
                    })
                }
                signalObj[signalObj.getSignal(ctx)].dispatch('done', sigId);
                $('#directory_tree ul').html(dirList); //TODO: refactor
                th.updateFileList(); //
            } else {
                signalObj[signalObj.getSignal(ctx)].dispatch('fail', sigId);
            }
        });
    }

FileManager.prototype.setDirNameHeader = function(headerId) {
    if ( parseInt(selectedDir) >= 0 )
    {
        t = selectedDirDesc[selectedDir] + ":" + currentDir;
    }
    else
    {
        t = "ROOT";
    }
    $('#' + headerId).html(t);
}

FileManager.prototype.dirCreate = function dirCreate(dirName) {
        var result = ajaxRequest('create', 'dir', makeDir(currentDir, dirName)),
            sigId = manager.getSelf().getUniqId(),
            ctx = arguments.callee.name;

        signalObj[signalObj.getSignal(ctx)].dispatch( 'start', sigId );

        result.done(function (data) {
            //hide modal
            $('#myModal').modal('hide');
            data = $.parseJSON(data);

            if (data.data == false)
            {
                signalObj[signalObj.getSignal(ctx)].dispatch( 'fail', sigId, data.error );
            }
            else
            {
                signalObj[signalObj.getSignal(ctx)].dispatch( 'done', sigId );
                notes.create({noteContent:lang.note_dir_create_ok});
            }
            //update dirlist
            manager.dirList(currentDir);
        });
        result.fail(function (data, xhrObject) {
            signalObj[signalObj.getSignal(ctx)].dispatch( 'fail', sigId );
        });
    }

/**
 * Gets dirName listing via ajax
 * @param  dirName string
 */
FileManager.prototype.dirList = function dirList(dirName) {
        var result = ajaxRequest('list', 'dir', dirName),
            sigId = manager.getSelf().getUniqId(),
            ctx = arguments.callee.name,
            th = manager.getSelf();
        if (ctx == undefined) {
            //focking IE
            var matches =  ( arguments.callee.toString(0).match(/function\s+([^\s\(]+)/) ); ctx = matches[1];
        }

        signalObj[signalObj.getSignal(ctx)].dispatch( 'start', sigId );
        //var noteId = notes.create({noteContent:lang.note_dir_load_list});

        //reset file
        selectedFile = {};
        result.done(function (data) {
            dataObject = $.parseJSON(data);
            if (dataObject != null && dataObject.data != false)
            {
                currentDir = dirName;
                signalObj[signalObj.getSignal(ctx)].dispatch('done', sigId, dataObject.data);
            }
            else
            {
                signalObj[signalObj.getSignal(ctx)].dispatch('error', sigId);
                selectedDir = '';
            }
        });
        result.fail(function() {
            signalObj[signalObj.getSignal(ctx)].dispatch('fail', sigId);
        })
    }

FileManager.prototype.dirRename = function dirRename() {
        var sigId = manager.getSelf().getUniqId(),
            result = ajaxRequest('rename', 'dir', currentDir + '&name2='+ $('#modalInput').val());

        result.done(function (data) {
            signalObj[signalObj.getSignal(ctx)].dispatch('done', sigId);
            $('#myModal').modal('hide'); //lame
            data = $.parseJSON(data);
            if (data.data == false)
            {
                signalObj[signalObj.getSignal(ctx)].dispatch('error', sigId);
                notes.create({noteContent:data.error, className:'alert-error'});
            }
            else
            {
                signalObj[signalObj.getSignal(ctx)].dispatch('success', sigId);
                manager.dirList(makeDir(currentDir,'..'));
            }
         });
         result.fail(function (data, xhrObject) {
             signalObj[signalObj.getSignal(ctx)].dispatch('fail', sigId);
                         notes.create({noteContent:lang.note_error, className:'alert-error'});
         });
    }

FileManager.prototype.fileCreate = function fileCreate(fName) {
        var sigId = manager.getSelf().getUniqId(), //TODO: diz iz baaad
            result = ajaxRequest('create', 'file', makeDir(currentDir, fName)),
            ctx = arguments.callee.name,
            th = manager.getSelf();

        result.done(function (data) {
            //hide modal
            signalObj[signalObj.getSignal(ctx)].dispatch('gotResponse', sigId);
            data = $.parseJSON(data);
            $('#myModal').modal('hide'); //lame
            if (data.data == true)
            {
                signalObj[signalObj.getSignal(ctx)].dispatch('done', sigId);
            }
            else
            {
                signalObj[signalObj.getSignal(ctx)].dispatch('error', sigId);
            }
        });
        result.fail(function (data, xhrObject) {
            signalObj[signalObj.getSignal(ctx)].dispatch('fail', sigId);
        });
    }
