//TODO: set deferred events to make code less messy

function stripslashes (str) {

    str = (str + '').replace(/\\(.?)/g, function (s, n1) {
        switch (n1) {
        case '\\':
            return '\\';
        case '0':
            return '\u0000';
        case '':
            return '';
        default:
            return n1;
        }
    });
    return (str+'').replace(/([/]+)/g,'/');

}

$(document).ready(function () {
    // ************* INIT SECTION

    baseUrl = '';
    fmUrl  = baseUrl + 'manageFiles/';
    notes = $.fn.stickyNote();

    registry = {
        filelistData: '',
        filesorting:'filename'
    };

    currentDir = ''; //remember current dir path
    selectedDir = ''; //remember base dir index
    selectedDirDesc = {}; //remember base dir description
    selectedFile = {
        name: '',
        link: ''
    }; //for current selected file


    //lame function for base_dir analog
    makeDir = function (dir, changeDir) {
        if (changeDir == '..' || changeDir == '.') {
            if (dir !== '/')
            {
                var r = dir.slice(0, dir.lastIndexOf('/'));
                if (r !== "")
                {
                    return r;
                }
                else
                {
                    return '/';
                }
            }
            else
            {
                return "";
            }
        }
        else {
            var sep = '';
            if (dir.charAt(dir.length - 1) != '/') {
                sep = '/';
            }

            return dir + sep + changeDir;
        }
    }

    uploader = new FileUploader({});
    render   = new FileListRender();
    manager  = new FileManager();

    FLsorter = new FileListSorter();

    render.setRender('list');
    manager.getBaseDirList(); //get base dir content
    registry.filesorting = 'filename';

    //ie compatibility
    $.ajaxSetup({ cache: false });

    $('#view_config').live('click', function(e){
        signalObj[signalObj.getSignal('UserClicksOnGetConfig')].dispatch(e);
    });

    $('.fm_dir').live('click', function (e) {
        signalObj.UserClicksOnDirItem.dispatch(e);
    })

    // UI interface bind file selection
    $('.fm_file').live('click', function (e) {
        signalObj.UserClicksOnFileItem.dispatch(this);
    })

    $('.fm_filecreate').live('click', function (e) {
        new Modal({
            header:lang.modal_header_createfile,
            actionEvent: manager.fileCreate
        })
    })

    $('.fm_dircreate').live('click', function (e) {
        //show popup
        new Modal({
            header: lang.modal_header_createdir ,
            actionEvent:manager.dirCreate
        });
        //manager.dirCreate();
    })


    $('.fm_rename').live('click', function (e) {
        manager.doRename(e);
    })

    $('.fm_edit').live('click', function (e) {
        manager.fileEdit(selectedFile.name);
    })

    $('.fm_delete').live('click', function (e) {
        manager.doDelete(e)
    })

    $('.fm_upload').live('click', function (e) {
        new ModalUpload();
    });



    //filelist display toggle
    $('.filelist_style').live('click', function(e) {
        signalObj.buttonToggleActive.dispatch($(this).closest('.filelist_style'));
        signalObj.renderFileList.dispatch( 'update', null, $(this).closest('.filelist_style').attr('value') );
    })

    $('.filelist_sorting').live('click', function(e) {
        if ($(this).closest('.filelist_sorting').attr('value') != null) {
            signalObj.buttonToggleActive.dispatch($(this).closest('.filelist_sorting'));
            registry.filesorting = $(this).closest('.filelist_sorting').attr('value');
            signalObj.sortFileList.dispatch  ( 'update' , null , $(this).closest('.filelist_sorting').attr('value') );
            signalObj.renderFileList.dispatch( 'update' );
        } else {
            signalObj.sortFileList.dispatch('error');
        }
    })

    //enable bootstrap dropdown
    $('.dropdown-toggle').dropdown()
    //some UI stuff - prevent hiding login form after click
    $('.dropdown-menu').find('form').click(function (e) {e.stopPropagation();});

    //login
    $('#login_form').submit(function(e) {
        return;
        e.preventDefault();
        var auth = $.ajax({
            url:baseUrl + '/login' ,
            data:$('#login_form').serializeArray(),
            type:'POST'
        });
        auth.success(function(data){
            if (data == 'true')
            {
                location.reload();
            }
            else
            {
                notes.create( {noteContent: "Auth failed", className:"alert-error" } );
            }
        })
        return false;
    });

    //change group
    $('.group-select-item').click(function(e) {
        var auth = $.ajax({
            url:baseUrl + 'group/'+ $(e.target).attr('group_id') + '/',
            type:'GET'
        });
        auth.success(function(data){
            if (data == 'true')
            {
                location.reload();
            }
            else
            {
                notes.create( {noteContent: "Group change failed", className:"alert-error" } );
            }
        })
        return false;
    });

});
