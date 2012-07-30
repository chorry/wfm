signalObj[signalObj.getSignal('fileCreate')].add(function (e) {
    switch (e) {
        case 'start':
            break;
        case 'done':
            notes.create({noteContent:lang.note_file_create_ok});
            manager.dirList(currentDir);
            break;
        case 'fail':
            break;
        case 'error':
            notes.create({noteContent:lang.note_error, className:'alert-error'});
            break;
    }
});

signalObj.sortFileList.add(function (e) {
    switch (e) {
        case 'update':
            if (null != registry.filelistData.files) {
                registry.filelistData.files = FLsorter.sortObjects(registry.filelistData.files, registry.filesorting);
            }
            break;
        case 'error':
            notes.create({noteContent:lang.note_error, className:'alert-error'});
            break;
    }
})

signalObj[signalObj.getSignal('gotFileList')].add(function () {
    if (registry.filelistData != null) {
        registry.filelistData.files = FLsorter.sortObjects(registry.filelistData.files, registry.filesorting);
    } else {
        //console.log('no sorting :(');
    }
});


signalObj[signalObj.getSignal('updateFileList')].add(
    function (data) {
        $('#file_list').html(render.doRender(data));
    }
);

signalObj[signalObj.getSignal('gotFileLink')].add(function (e) {
    $('.fm_download').die('click');
    switch (e) {
        case null:
            //file link is unavailable: show message?
            selectedFile.link = ''; //reset file
            selectedFile.path = ''; //reset file
            $('.fm_download button').addClass('disabled');
            break;
        default:
            selectedFile.link = stripslashes(e.data.link);
            selectedFile.path = stripslashes(e.data.path);
            $('.fm_download button').removeClass('disabled');
            $('.fm_download').live('click', function (e) {
                    window.open("http://"+selectedFile.link);
            });
            break;
    }
    signalObj.updateFileDesc.dispatch();
});
