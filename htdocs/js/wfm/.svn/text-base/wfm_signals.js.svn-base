/**
 * SIGNAL BINDINGS
 */


    //custom object that dispatch signals
signalObj = {
    UserClicksOnFileItem:new signals.Signal(),
    UserClicksOnDirItem:new signals.Signal(),

    renderFileList:new signals.Signal(),
    sortFileList:new signals.Signal(),

    buttonToggleActive: new signals.Signal(),
    /**
     * Lazy signals
     * @param signalName
     */
    getSignal:function (signalName) {
        if (!(signalObj[signalName] instanceof signals.Signal)) {
            signalObj[signalName] = new signals.Signal();
        }
        return signalName;
    }
};


signalObj[signalObj.getSignal('getBaseDirList')].add(function (e) {
    switch (e) {
        case 'start':
            notes.create({noteContent:lang.note_dir_load_list, id:arguments[1]});
            selectedFile = {}; //reset file
            break;
        case 'done':
            notes.destroy(arguments[1]);
            manager.setDirNameHeader('id_dirname');
            registry.filelistData = null;
            //$('#directory_tree ul').html(dirList);
            manager.updateFileList(); //
            break;
        case 'fail':
            notes.destroy(arguments[1]);
            notes.create({noteContent:lang.note_error, className:'alert-error'});
            break;
        default:
            //console.log('unknown action for ' + e);
            break;
    }
});

signalObj.renderFileList.add(function (e) {
    switch (e) {
        case 'update':
            if (arguments[2] != null) {
                render.setRender(arguments[2]);
            }
            try {
                $('#file_list').html(render.doRender(registry.filelistData));
            } catch (e) {
                //console.log(e);
            }
            break;
    }
});