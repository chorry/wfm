    FileListRender = function() {
        //templates
        this.renderPrefix = 'render_';

        this.templates = {
            render_thumb :'<ul class="thumbnails">{{#files}}<li class=""><div class="thumbnail fm_file" value="{{filename}}"><div class="file_name_thumbs"><p>{{filename}}</p></div><div class="caption">{{filesize}} bytes</div></div></li>{{/files}}</ul>',
            render_list  : '<div><ul>{{#files}}<div class="fm_file filelist" value="{{filename}}"><div class="file_name_lists">{{filename}}</div><div class="file_list_size">[{{filesize}} bytes]</div></div>{{/files}}</ul></div>'
        };

        //main stuff
        this.getRender = function() {
            return this.renderType;
        }

        this.setRender = function(renderType) {
            this.renderType = renderType;
        }

        /**
         * Checks if render has defined template
         * @return {Boolean}
         */
        this.renderHasTemplate = function() {
            if ( this.templates.hasOwnProperty(this.renderPrefix + this.getRender()) ){
                return true;
            }
            return false;
        }

        /**
         * Returns current render template
         * @return {*}
         */
        this.getRenderTemplate  = function() {
            return this.templates[ this.renderPrefix +this.getRender() ];
        }


        this.doRender = function(jsonData) {
            if ( this.renderHasTemplate() ) {
                return (
                    Mustache.render(
                        this.getRenderTemplate(),
                        jsonData
                    )
                );
            }
            throw 'no template for render '+ this.getRender();
        }

        this.sortObjects = function(jsonData, sortKey) {

        }

    }