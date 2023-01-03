<?php
        // -------------------
        // prj-wgtlistbox.mod.php
        // -------------------


CModules::include_begin( __FILE__ , 'prj css_wgtlistbox' );



CModules::append( 'mod_html_div_wgtlistbox', <<<EOLONGTEXT


<!--   mod_html_div_wgtlistbox -->


EOLONGTEXT );  // mod_html_div_wgtlistbox



CModules::append( 'mod_js_class_WgtListbox', <<<EOLONGTEXT




class WgtListbox extends DmcBase {

    static _this = null;
    static _wgt_data = {};


    constructor() {
        super();
        this.constructor._this = this;
        this.constructor._wgt_data = {};
    }

    static onload( parent ) {
        super.onload( parent );
        this.load_wgt_data( parent );
        this.connect_events( parent );        
    }


    static get_root_elt_selector() {
        return  'fieldset.wgt_listbox';
    }

    static connect_events( parent ) {
        super.connect_events( parent );

        if (parent == undefined)  parent = document.body;
        let elts = parent.querySelectorAll('fieldset.wgt_listbox');
        for (let e=0; e<elts.length; e++) {
            let fieldset = elts[e];
            this.connect_events_wdgt (fieldset);
        }
    }


    static connect_events_wdgt (fieldset) {
        let lines = fieldset.querySelectorAll('p');
        for (let l=0; l<lines.length; l++) {
            let elt = lines[l];
            let details = { fieldset: fieldset };
            this.addEventListener_(elt, 'click',       'slot_click', details);
            this.addEventListener_(elt, 'dblclick',    'slot_dblclick', details);
            this.addEventListener_(elt, 'mousedown',   'slot_mousedown', details);
            this.addEventListener_(elt, 'contextmenu', 'slot_contextmenu', details);
        }
    }

    static set_innerHTML (fieldset, txt) {
        fieldset.innerHTML =  txt;
        this.connect_events_wdgt (fieldset);        
    }


    static deleteLine (fieldset, lineTxt) {
        let list =  this.getLines(fieldset);

        const index = list.indexOf(lineTxt);
        if (index == -1) {     return null;    }
        let listdata = this.get_wgt_listdata( fieldset );

        list.splice(index, 1); 
        if ( lineTxt in listdata ) {    delete listdata[lineTxt];    }

        this.setLines (fieldset, list, listdata);
        return true;
    }


    static setLines ( fieldset, list, listdata ) {

        if (listdata === undefined) {
            console.log('WgtListbox::setLines error : please give the data parameter (or null)');
            return;
        }

        let CR='', txt='';
        let legend = fieldset.querySelector('legend');
        if (legend)  txt += legend.outerHTML+CR;

        for (let l=0; l<list.length; l++) {
            let item = list[l];
            txt += "<p>" + item + "</p>"+CR;
        }

        let data = this.get_wgt_data( fieldset );
        data['listdata'] = listdata;
        this.set_wgt_data( fieldset, data );
        this.set_innerHTML( fieldset, txt );

        let np = JSON.stringify( listdata );
        let cookieName = data['LSCookie'];
        setLSCookie( app.name + cookieName, np );
    }




    static load_wgt_data_fieldset( fieldset ) {

        let cookieName = fieldset.getAttribute( 'data-LSCookie' );
        let data = { LSCookie: cookieName, listdata: {} };

        let json = null; 
        if (cookieName) 
            json = getLSCookie( app.name + cookieName );

        if (cookieName && json) {
            // a cookie exists : apply values found 
            // -----------
            let listdata = {}; 
            try {
                listdata = JSON.parse(json);
            } catch(e) {
                app.log('WgtListbox::load_wgt_data error bad cookie in data-LSCookie');
            }
            this.set_wgt_data( fieldset, data );
            let list = Object.keys(listdata);
            this.setLines ( fieldset, list, listdata );

        } else {
            // no cookie : get html elements
            // -----------
            let items = fieldset.querySelectorAll('p');
            for (let l=0; l<items.length; l++) {
                let item = items[l].innerText;
                data[item] = {};
            }
            this.set_wgt_data( fieldset, data );   // initialize wgt_data
        }
    }



    static load_wgt_data( parent ) {
        if (parent == undefined)  parent = document.body;
        let elts = parent.querySelectorAll('fieldset.wgt_listbox');
        for (let e=0; e<elts.length; e++) {
            this.load_wgt_data_fieldset( elts[e] );
        }
    }

    static set_wgt_data( fieldset, details = {} ) {
        this._wgt_data[fieldset.id] = details;
    }

    static get_wgt_data( fieldset ) {
        return this._wgt_data[fieldset.id];
    }


    static get_wgt_listdata( fieldset ) {
        return this._wgt_data[fieldset.id]['listdata'];
    }




    // get the items as an array of Strings
    static getLines (fieldset) {
        let arr = [];
        let elts = fieldset.querySelectorAll('p');
        for (let l=0; l<elts.length; l++) {
            let elt = elts[l];
            arr[l] = elt.innerText;
        }
        return arr;
    }

    getSelectedLines (fieldset) {
        let elts = fieldset.querySelectorAll('p.wgt_listbox_sel');
        return elts;
    }

    // for wgt_listbox_oneonly
    getSelectedLine (fieldset) {
        let lines = fieldset.querySelectorAll('p.wgt_listbox_sel');
        if (lines.length == 0) return null;
        let selected = null;
        for (let l=0; l<lines.length; l++) {
            if (!selected)  {
                selected = lines[i];        // take first
            } else {
                lines[i].classList.remove('wgt_listbox_sel');
            }
        }
        return selected;
    }


    // for wgt_listbox_oneonly
    setSelectedLine (fieldset, elt) {
        let lines = fieldset.querySelectorAll('p.wgt_listbox_sel');
        let selected = null;
        for (let l=0; l<lines.length; l++) {
            if (!selected)  {
                selected = lines[l];        // take first
            } else {
                lines[l].classList.remove('wgt_listbox_sel');
            }
        }
        if (selected != elt) {
            if (selected) selected.classList.remove('wgt_listbox_sel');
            elt.classList.add('wgt_listbox_sel');
        }
    }


    slot_click( event, elt, details ) {
        let oneonly = details.fieldset.classList.contains( 'wgt_listbox_oneonly' );
        if (oneonly) {
            this.setSelectedLine (details.fieldset, elt);
            return;
        }
        let isSelected = elt.classList.contains( 'wgt_listbox_sel' );
        if (isSelected)    
            elt.classList.remove('wgt_listbox_sel');
        else
            elt.classList.add('wgt_listbox_sel');
    }


    slot_dblclick( event, elt, details ) {
        let oneonly = details.fieldset.classList.contains( 'wgt_listbox_oneonly' );
        if (!oneonly) return;

        let signal = {
            type:   'item_dblclick',
            value:   elt.innerText,
            elt:     elt,
            event:   event,
            details: details,
            target:  details.fieldset,
            sender:  this
        };
        Application.emit(  signal  );
    }


    slot_mousedown( event, elt, details ) {
        if (event.detail == 2) {
            // 
            // prevent double click to select all on the <p>line</p>
            // 
            event.preventDefault();
        }
    }




    slot_contextmenu( event, elt, details ) {
        let signal = {
            type:   'item_contextmenu',
            value:   elt.innerText,
            elt:     elt,
            event:   event,
            details: details,
            target:  details.fieldset,
            sender:  this
        };
        let r = Application.emit(  signal  );
        if (r === false) event.preventDefault();
        return r;
    }


} // class WgtListbox



EOLONGTEXT );  // mod_js_class_WgtListbox 



CModules::append_onload( __FILE__, 'new WgtListbox();' );


CModules::append( 'mod_css_div_wgtlistbox', <<<EOLONGTEXT



/* ================================== 
   =
   =
   =       css for WgtListbox
   =
   =
   =
   ================================== */

:root{
}


/* ============== wgt_listbox ============ */
/* =========================== */
/* =========================== */
/* =========================== */
/* =========================== */


fieldset.wgt_listbox {
    border: 1px solid #777;
    background-color: #f5f5f5;      
    padding: 0.2em;
    margin: 0;
    overflow-y: auto;
}


fieldset.wgt_listbox  legend {
    background-color: #0000;      
}

fieldset.wgt_listbox_oneonly  legend {
    background-color: #0000;    
}

fieldset.wgt_listbox p {
    background-color: #f5f5f5;   
    cursor: pointer;   
  /*  user-select: none; */
}

fieldset.wgt_listbox p.wgt_listbox_sel {
    background-color: red;   
}

/* textarea:focus-visible,  input:focus-visible */

fieldset.wgt_listbox p:hover {
    outline: 2px solid #DC143C80;
    outline-offset: 0px;
    border-radius: 3px;
}



EOLONGTEXT ); // mod_css_div_wgtlistbox     



CModules::include_end( __FILE__ );

?>