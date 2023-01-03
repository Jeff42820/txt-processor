<?php
        // -------------------
        // div-dropfile.mod.php
        // -------------------


CModules::include_begin(__FILE__, 'This is division module  dropfile');



CModules::append( 'mod_html_div_dropfile', <<<EOLONGTEXT

<div id="id_dropfile" class="div_dropfile"
    ondrop="DmcDropfile._this.ev_dropfile(event)" 
    ondragover="DmcDropfile._this.ev_dragover(event)" 
    ondragleave="DmcDropfile._this.ev_dragleave(event)">
    <p> &nbsp; </p>
    <p>
    <span class="icon_inline"  onclick="DmcDropfile.ev_choosefile()" 
        style="cursor:pointer;"><img class="icon_inline_scale1" src="icons/dropfile.svg" /> &nbsp; &nbsp; </span> &nbsp; 
        Drag-drop your file here
    </p>
    <form class="div_dropfile_select">
        <!-- input type="button" value="Select a file" onclick="DmcDropfile.ev_choosefile()" / -->
        <input type="file" id="DmcDropfile_selectfile" style="display:none;"/>
    </form>
    <p> &nbsp; </p>
</div>

EOLONGTEXT );  // mod_html_div_dropfile




CModules::append( 'mod_js_class_Dropfile', <<<EOLONGTEXT

class DmcDropfile extends DmcBase {

    static _this = null;

    constructor() {
        super();
        this.constructor._this = this;      //        DmcDropfile._this = this;
    }

    ev_dragleave(ev) {
        ev.currentTarget.classList.remove("drag_style_over");
    }

    ev_dragover(ev) {
        ev.preventDefault();
        ev.currentTarget.classList.add("drag_style_over");
        return false;
    }

    ev_dropfile(ev) {
        ev.preventDefault();
        ev.currentTarget.classList.remove("drag_style_over");
        if (!method_exists(app, 'slot_dropfile')) {
            app.error('Error : class ' + app.constructor.name + ' missing method [slot_dropfile]');
            return;
        }
        app.slot_dropfile(ev.dataTransfer.files);
    }

    static ev_choosefile(ev) {
        let input = document.getElementById('DmcDropfile_selectfile');
        input.click();
        input.onchange = function() {
            DmcDropfile.upload_post_file( document.getElementById('DmcDropfile_selectfile').files );
        };
    }

    static upload_post_file( files ){
        app.slot_dropfile( files );
    }

    static async post_file(file_obj) {

        let r = await DmcDropfile_post_file_promise(file_obj, app.session_hash, 

                // ==== onprogress
                function (timeFromStart, totalTime, percentComplete) {
                    if (percentComplete == '100%') {
                        return;
                    }
                    let txt = Math.round(percentComplete).toFixed(0)+'%';    // div.style.width = txt;
                }, 
                
                // ==== onload
                function (status, json) {
                    if (status !== true) {
                        app.log( "Error " + status + " occurred when trying to upload your file" );
                    }
                }        );

        app.log('DmcDropfile::post_file end ok r='+r);
        return r;
    }

    static format_size(size) {
        if (size < 1024)    return size+'b';
        let ssize = size / 1024;
        if (ssize < 1024) ssize = ssize.toFixed(0) + 'Kb';
        else              ssize = (ssize / 1024).toFixed(0) + 'Mb';
        return ssize;
    }

} // class DmcDropfile




function DmcDropfile_post_file_promise(file_obj, session_hash, onprogress, onload) {
    return new Promise((resolve, reject) => {  
        post_file_( file_obj, session_hash, onprogress,
            (json) => {     onload( true, json );     resolve(json);            },
            (err)  => {     onload( status );         reject(status);           }   );
    });
}




function post_file_(file_obj, session_hash, onprogress=null, resolve=null, reject=null ) {

        function format_percent(loaded, total) {
            let percentComplete = (loaded / total) * 100;
            if ( percentComplete < 0 ) percentComplete = 0;
            if ( percentComplete > 100 ) percentComplete = 100;
            return percentComplete;
        }

        function format_sec( _time ){
            if (_time < 100)        {  _time = _time.toFixed(0)+'ms ';          }  
            else if (_time < 5000)  {  _time = (_time / 1000).toFixed(1)+'s ';  } 
            else                    {  _time = (_time / 1000).toFixed(0)+'s ';  }
            return _time;
        }



    let form_data = new FormData();
    form_data.append('submit', 'post_Upload_XMLHttp');
    form_data.append('file_obj',  file_obj );
    form_data.append('hash',  session_hash );
    let xhttp = new XMLHttpRequest();

    xhttp.open("POST", "", true);
    xhttp.upload.onprogress = function(ev) {  
        if (!ev.lengthComputable)  return;
        let timeStamp =  Date.now() - xhttp._timeStart;  
        let timeFromStart = ( timeStamp / 1000 ).toFixed(0);
        let total = xhttp._file_obj.size;
        let loaded = xhttp._file_obj.size * (ev.loaded / ev.total);
        let totalTime = '';
        if ( loaded >0 ) {  totalTime = ' / '+(  total * timeStamp / loaded /1000  ).toFixed(0)+' sec';   }
        onprogress(timeFromStart, totalTime, format_percent(ev.loaded, ev.total));
    };
    xhttp.onload = async function(ev) {
        onprogress('', '', '100%');
        if (xhttp.status == 200) {
                let json = {};
                try {
                    if (xhttp.response != '')
                        json = JSON.parse(xhttp.response);
                    else
                        json['error'] = 'DmcDropfile::post_file_ error : xhttp.response is empty';
                    json['delay'] = format_sec(Date.now() - xhttp._timeStart);
                } catch (error) {
                    app.log( error.message );
                    app.log_html( xhttp.response );
                }
                resolve( json );
        } else {
                app.log_error( 'DmcDropfile::post_file_ reject, status='+xhttp.status+'\\n' );
                reject( xhttp.status );
        }
    }
    xhttp._file_obj = file_obj;
    xhttp._timeStart =  Date.now();
    xhttp.send(form_data);
}




EOLONGTEXT );  // mod_js_class_Dropfile     



CModules::append_onload( __FILE__, 'new DmcDropfile();' );



CModules::append( 'mod_css_dmc_dropfile', <<<EOLONGTEXT

div.drag_style_over {
    border: #555 0.4em dashed !important;   
}

div.div_dropfile {
  width:20em;
  text-align:center;
  color: white;
  background-color: var(--main_color);   /*  #ff6b30;  */
  border: #ddd 0.4em solid;   
  margin: 0.2em auto 0.2em auto;
  border-radius: 1em;
  /*  cursor: copy;  */
}

div.div_dropfile:hover {
    border: #ddd 0.4em dashed;   
}

EOLONGTEXT );  // mod_css_dmc_dropfile   




CModules::include_end(__FILE__);
?>