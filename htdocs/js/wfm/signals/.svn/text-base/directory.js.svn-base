/**
 * Signal for fetching dirList
 * (signalName, sigId, data)
 */
signalObj[signalObj.getSignal('dirList')].add(function (e) {
    switch (e) {
        case 'start':
            notes.create({noteContent:lang.note_dir_load_list, id:arguments[1]});
            break;
        case 'done' :
            registry.filelistData = arguments[2];
            signalObj.sortFileList.dispatch('update');
            manager.updateDirList(arguments[2].dir);
            manager.updateFileList(registry.filelistData);
            manager.setDirNameHeader('id_dirname');
            //currentDir = dirName;
            break;
        case 'error' :
            notes.create({noteContent:dataObject.error, className:'alert-error'});
            break;
        case 'fail' :
            break;
        default:
    }
});

signalObj[signalObj.getSignal('dirCreate')].add(function (e) {
    switch (e) {
        case 'start':
            notes.create({noteContent:lang.note_dir_load_list, id:arguments[1]});
            selectedFile = {}; //reset file
            break;
        case 'done':
            notes.destroy(arguments[1]);
            manager.setDirNameHeader('id_dirname');
            //$('#directory_tree ul').html(dirList);
            manager.updateFileList(); //
            break;
        case 'fail':
            notes.create({header:lang.note_dir_create_fail, noteContent: arguments[2], className:'alert-error'});
            break;
        default:
            break;
    }
});
