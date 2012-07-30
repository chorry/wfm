///////////////////////////**************************** USER INTERACTION BINDING

signalObj[signalObj.getSignal('UserClicksOnGetConfig')].add(function (obj) {
    var r = ajaxRequest('get', 'config');
    r.done(function(data){
        try
        {
            var confData = ($.parseJSON(data)).data;
            new ModalViewConfig(confData);
        }
        catch (err)
        {
            notes.create({noteContent:lang.note_error, className:'alert-error'});
        }

    });
    r.fail(function (data, xhrObject) {
        notes.create({noteContent:lang.note_ajax_error, className:'alert-error'});
    });
});

signalObj.UserClicksOnFileItem.add(function (obj) {
    var clickedDiv = $(obj).closest(".fm_file");
    selectedFile.name = $(clickedDiv).attr('value');
    $('.fm_file').removeClass('selected_item');
    $(clickedDiv).addClass('selected_item');

    //Get web filelink (if possible) and update filename
    var z = manager.doFileGetLink(selectedFile.name, 'no error');
});


//change dir
signalObj.UserClicksOnDirItem.add(function (obj) {
    if (typeof $(obj.target).attr('dir_id') != 'undefined' )
    {
        selectedDir = $(obj.target).attr('dir_id');
    }

    var chDir = makeDir(currentDir, $(obj.target).attr('dirname'));

    if (chDir !== '')
    {
        manager.dirList( chDir );
    }
    else
    {
        manager.getBaseDirList();
    }
    selectedFile = {};
    signalObj.updateFileDesc.dispatch();

});

signalObj.buttonToggleActive.add(function(e, clickItem){
    $(e).parent().find('button').each(function(k,v){
        $(v).removeClass('active');
    });
    $(e).addClass('active');
})

signalObj[signalObj.getSignal('updateFileDesc')].add(function(e, item) {
    var link, path;
    link = path = '';
    if (selectedFile.hasOwnProperty('link') && selectedFile.path != '' ) link = '<a href="http://' + selectedFile.link + '">' + selectedFile.link + '</a>';
    if (selectedFile.hasOwnProperty('path') && selectedFile.path != '' ) path = selectedFile.path;

    $('#id_fileurl').html(link);
    $('#id_filepath').html(path);
});

