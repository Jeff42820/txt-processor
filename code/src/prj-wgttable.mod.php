<?php
        // -------------------
        // prj-wgttable.mod.php
        // -------------------


CModules::include_begin( __FILE__ , 'prj css_wgttable' );



CModules::append( 'mod_html_div_wgttable', <<<EOLONGTEXT


<!--   mod_html_div_wgttable -->


EOLONGTEXT );  // mod_html_div_wgttable



CModules::append( 'mod_js_class_WgtTable', <<<EOLONGTEXT


class WgtTable extends DmcBase {

    static _this = null;


    constructor() {
        super();
        this.constructor._this = this;
        this.elements_class =  {};
    }

    static get_root_elt_selector() {
        return  'table.wgt_table';          //   table[mwgtclass='WgtTable']  ??
    }

    static onload( parent ) {
        super.onload( parent );

        if (parent == undefined)  parent = document.body;
        let elts = parent.querySelectorAll( this.get_root_elt_selector() );
        for (let e=0; e<elts.length; e++) {
            let table = elts[e];
            WgtTable._this.elements_class[table.id] = { elt: table, data: {} };
        }

        this.connect_events( parent );        
    }

    static connect_events( parent ) {
        super.connect_events( parent );

        if (parent == undefined)  parent = document.body;
        let elts = parent.querySelectorAll( this.get_root_elt_selector() );
        for (let e=0; e<elts.length; e++) {
            let table = elts[e];
        }
    }


    slot_search_keydown( event, elt, details ) {
        let table = elt.closest( WgtTable.get_root_elt_selector() );
        if (event.key === 'Enter' || event.keyCode === 13) {
            // document.activeElement.blur();
            WgtTable._searchAll(table, elt.value);
        }
    }



    static _searchAll(table, searchString) {

        function filterRow(cols, searchString) {
            if (!searchString || searchString=='') return true;
            for (let col=0; col<cols.length; col++) {
                if ( cols[col].includes(searchString) ) 
                    return true; 
            }
            return false;
        }

        let data = WgtTable.getData( table );
        if ('csv' in data) {
            WgtTable.importCsv ( table, data.csv, filterRow, searchString );
        } else {
            app.log('WgtTable::_searchAll error : no csv data available');
        }

    }


    slot_search_change( event, elt, details ) {
        let table = elt.closest( WgtTable.get_root_elt_selector() );
        app.log("slot_search_change");
    }


    static connect_events_wdgt (table) {
        // if (js_debug)  console.log('WgtTable::connect_events_wdgt for '+table.id);

        let elts = table.querySelectorAll('[mwgtsubclass]');
        for (let i=0; i < elts.length; i++) {
            let wgtSubClass = elts[i].getAttribute('mwgtsubclass');
            if (wgtSubClass=='WgtTableInputSearch') {
                this.addEventListener_(elts[i], 'change',  'slot_search_change',  {});
                this.addEventListener_(elts[i], 'keydown', 'slot_search_keydown', {});
            }
        }  

    }


    static set_innerHTML (table, txt) {
        table.innerHTML =  txt;
        this.connect_events_wdgt (table);  
    }

    static set_innerTBODY ( table, inner ) {
        let tbody = table.querySelector('tbody');
        tbody.innerHTML =  inner;
    }

    static set_innerTHEAD ( table, inner ) {
        let thead = table.querySelector('thead');
        thead.innerHTML =  inner;
    }


    static importCsv ( table, txt, filterRow = undefined, details = undefined ) {
        let data = this.getData( table );
        data['csv'] = txt;

        let inner='', maxcols = 0;
        let lines = txt.split('\\n');

        for (let row=0; row<lines.length; row++) {
            let cols = columnSplit( lines[row] );
            if (cols.length > maxcols) maxcols = cols.length;
        }

        for (let col=0; col<maxcols; col++) {
            inner += '<td>'+(col+1)+'</td>';
        }

        for (let row=0; row<lines.length; row++) {
            let cols = columnSplit( lines[row] );
            if (filterRow && !filterRow(cols, details))
                continue;
            let line = '<tr>';
            for (let col=0; col<maxcols; col++) {
                if (col<cols.length) {
                    let style='';
                    if (isNumeric(cols[col]))
                        style='style="text-align: right;"';
                    if (isDate_slash(cols[col]))
                        style='style="text-align: center;"';
                    line += '<td '+style+'>' + cols[col] + '</td>';
                } else {
                    line += '<td></td>';
                }
            }
            line += '</tr>\\n';
            inner += line;
        }

        // ad the end, because will reconnect events
        this.set_innerTBODY(table, '\\n'+inner+'\\n');
    }


    static set_th ( table ) {
        let thead = '<tr>';
        thead += '<th scope="col" colspan="100%">';
        thead += 'Search: <input type="text" mwgtsubclass="WgtTableInputSearch" LSCookie="WgtTable.input" />\\n';
        thead += '</th></tr>\\n';
        this.set_innerTHEAD ( table, thead );
    }


} // class WgtTable



EOLONGTEXT );  // mod_js_class_WgtTable 



CModules::append_onload( __FILE__, 'new WgtTable();' );


CModules::append( 'mod_css_div_wgttable', <<<EOLONGTEXT



/* ================================== 
   =
   =
   =       css for WgtTable
   =
   =
   =
   ================================== */

:root{
}


/* ============== wgt_table ============ */
/* =========================== */
/* =========================== */
/* =========================== */
/* =========================== */


table.wgt_table {
    background-color: #9df; 
    border: 1px solid grey;
    font-size: 9pt;
    border-collapse: collapse;
    width: 100%;
}

table.wgt_table tr td {
    padding: 0.0em 0.3em 0.1em 0.3em;
    border-left: solid 1px #888;
    overflow-x: hidden;
    overflow-y: hidden;
    white-space: nowrap;
    vertical-align: top;
    max-width: 20em;
}


table.wgt_table  tbody  tr:nth-of-type(2n+1) {
    background-color: #00000015;         
}




EOLONGTEXT ); // mod_css_div_wgttable     



CModules::include_end( __FILE__ );

?>