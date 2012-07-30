    FileListSorter = function() {
        //templates
        this.defaultSort = 'name';

        //main stuff
        this.getSort = function() {
            return this.renderType;
        }

        this.setSort = function(v) {
            this.sort = v;
        }

        this.sortObjects = function(jsonData, sortKey) {
            if (sortKey == 'extension') return this.sortObjectsByExt(jsonData);
            if (jsonData == null) return jsonData;
            if ( jsonData[0].hasOwnProperty(sortKey) ) {
                jsonData.sort(function(a, b){
                 return (a[sortKey] < b[sortKey]) ? -1 : ( (a[sortKey] > b[sortKey]) ? 1 : 0 ) ;
                });
            }
            return jsonData;
        }
        this.sortObjectsByExt = function(jsonData) {
            jsonData.sort(function(a, b) {
                return ( a['filename'].split('.').pop() < b['filename'].split('.').pop() ? -1 : ( a['filename'].split('.').pop() > b['filename'].split('.').pop() ) ? 1 : 0 ) ;
            });

            return jsonData;
        }
    }