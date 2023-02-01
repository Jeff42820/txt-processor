<?php
        // -------------------
        // div-dropfile.mod.php
        // -------------------


/*

When file(s) are dropped into this div, the method [[ app.slot_dropfile(ev, files) ]]
is sent.



example 1 : 
        echo $dm->sml_mustache( <<<EOLONGTEXT
            <div class="dmc_respcard">
                {{mod_html_div_dropfile}}
            </div>
        EOLONGTEXT, [ 'mod_html_div_dropfile' => $dm->mod_get_elt( 'div-dropfile.mod.php', 'mod_html_div_dropfile' )   ] );


example 2 : 

    <li id="id_mnu_file"><a>File</a>
        <div class="div_menu">
        <a onclick="DmcDropfile.ev_choosefile(event)">open 
            <form class="form_dropfile_select">
            <input type="file" id="DmcDropfile_selectfile" style="display:none;"/>
            </form>
        </a>
        </div>
    </li>

*/


CModules::include_begin(__FILE__, 'This is division module  dropfile');



CModules::append( 'mod_html_div_dropfile', <<<EOLONGTEXT

<div id="id_dropfile" class="div_dropfile"
    ondrop="DmcDropfile._this.ev_dropfile(event)" 
    ondragover="DmcDropfile._this.ev_dragover(event)" 
    ondragleave="DmcDropfile._this.ev_dragleave(event)">
    <p> &nbsp; </p>
    <p>
    <span class="icon_inline"  onclick="DmcDropfile.ev_choosefile(event)" style="cursor:pointer;">
        <img class="icon_inline_scale1" src="icons/dropfile.svg" /> &nbsp; &nbsp; 
    </span>
        &nbsp; 
        Drag-drop your<br>file here
    </p>
    <p> &nbsp; </p>
    <form class="form_dropfile_select"><!-- must be inside onclick="DmcDropfile.ev_choosefile elt, or inside div_dropfile -->
        <input type="file" id="DmcDropfile_selectfile" style="display:none;"/>
    </form>
</div>

EOLONGTEXT );  // mod_html_div_dropfile




CModules::append( 'mod_js_class_Dropfile', <<<EOLONGTEXT

class DmcDropfile extends DmcBase {

    static _this = null;

    constructor() {
        super();
        this.constructor._this = this;      //        DmcDropfile._this = this;
    }

    static get_root_elt_selector() {
        return  '.div_dropfile';
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
        app.slot_dropfile(ev, ev.dataTransfer.files);
    }

    static ev_choosefile(ev) {
        let form = ev.target.querySelector('form');    
        if (!form) {
            let wdgt  = ev.target.closest( DmcDropfile.get_root_elt_selector() );
            if (wdgt)
                form = wdgt.querySelector('form');
            if (!form) 
                return;
        }
        let input = form.querySelector('input[type="file"]');
        input.click();
        input.onchange = function(ev) {
            DmcDropfile.upload_post_file( ev );
        };
    }

    static upload_post_file( ev, files ){
        app.slot_dropfile( ev, ev.target.files );
    }

    static async post_file(file_obj) {

        let r = await DmcDropfile_post_file_promise(file_obj, app.session_hash, 

                // ==== onprogress
                function (timeFromStart, totalTime, percentComplete) {
                    if (percentComplete == '100%') {
                        return;
                    }
                    let txt = Math.round(percentComplete).toFixed(0)+'%';    // div.style.width = txt;
                    let r = Application.signal( 'slot_progress', null, null, { txt:txt } );
                    if (r === true) {
                    }
                }, 
                
                // ==== onload
                function (status, json) {
                    if (status !== true) {
                        app.log( "Error " + status + " occurred when trying to upload your file" );
                    }
                }        );

        // app.log('DmcDropfile::post_file end ok r='+r);
        return r;
    }

    static format_size(size) {
        if (size < 1024)    return size+'b';
        let ssize = size / 1024;
        if (ssize < 1024) ssize = ssize.toFixed(0) + 'Kb';
        else              ssize = (ssize / 1024).toFixed(0) + 'Mb';
        return ssize;
    }

    static async convfile(file_obj) {
        if (file_obj === undefined || file_obj === null) return '';
        app.log( 'Trying to load file ['+ file_obj.name+']');
        let upl_maxsize = 100;  // let's say 100 Mb
        if (file_obj.size > upl_maxsize*1024*1024) { 
            let fs = file_obj.size/1024/1024;
            app.error( 'Error file size = '+ fs.toFixed(0) + 'Mb > '+ upl_maxsize.toFixed(0) +'Mb is too big...' );
            return '';
        }
        let json = await DmcDropfile.post_file(file_obj);
        if (json.error) {
            app.error( json.msg );
            return '';
        }
        else {
            app.log( 'File ['+file_obj.name+'] loaded in '+json.delay+' size='+DmcDropfile.format_size(file_obj.size)+' ' );
        }
        let txt = atob( json['file'] );             //  atob  <=>   b64_to_utf8    <=>   binaryUtf8_toString
        let enc = find_encoding(txt);
        if (enc)  txt = binaryUtf8_toString(txt, enc);    
        return txt;
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

.drag_style_over {
    border: #555 0.2em dashed !important;   
}

div.div_dropfile {
  min-width:10em;
  text-align:center;
  color: white;
  background-color: var(--main_color);   /*  #ff6b30;  */
  border: #ddd 0.2em solid;   
  margin: 0.2em auto 0.2em auto;
  border-radius: 1em;
  /*  cursor: copy;  */
}

div.div_dropfile:hover {
    border: #ddd 0.2em dashed;   
}

form.form_dropfile_select {
    display: none;
}

EOLONGTEXT );  // mod_css_dmc_dropfile   




CModules::include_end(__FILE__);
?>