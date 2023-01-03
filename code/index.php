<?php 

/*
    =======================================
    ====
    ====  Text processor for bank import 
    ====
    ====  php & javascript
    ====
    ====
    =======================================
    ====
    ====  2022-12-27
    ====
    ====  v 0.902
    ====
    ====
    ====
    ====
    ==== (c) 2022 JF Lemay (FR) (contact via github Jeff42820)
    ====
    =======================================
*/


$m = NULL;
$CR="<br>\n";

// == 1 lib ==
include './src/utils.php';                      // lib_php some php utilities
include './src/cmodules.php';                   // lib_php class CModules
include './src/cmainphp.php';                   // lib_php class CMainPhp (base for CPrjMain)

// == 2 lib modules ==
include './src/lib-utils-js.mod.php';           // lib_js some js  utilities
include './src/lib-application-class.mod.php';  // lib_js class Application, class DmcBase

// == division modules used in prj ==
include './src/div-framework.mod.php';          // division module 
include './src/div-modal.mod.php';              // division module for modal windows
include './src/div-menu.mod.php';               // division module for a std menu
include './src/div-containers.mod.php';         // division module for some containers
include './src/div-contextmenu.mod.php';        // division module for context menus
include './src/div-dropfile.mod.php';           // division module for a div to drag-drop files
// include './src/div-icons-1.mod.php';             // division module : some embeded svg icons
// include './src/div-toolbar.mod.php';             //
include './src/div-accounting.mod.php';         // 

// == 4 project modules ==
include './src/prj-header-html.mod.php';                // prj header_html
include './src/prj-utils-css.mod.php';                  // prj css_main
include './src/prj-utils-css-icons.mod.php';            // prj icons 
include './src/prj-wgtlistbox.mod.php';                 // Widget Listbox
include './src/prj-wgtlogin.mod.php';                   // Widget Login




CModules::include_begin(__FILE__, 'This is index.php');


CModules::append( 'mod_css_index_php', <<<EOLONGTEXT


:root{
  /*  _m is for memory : used to recall initial value  */
  --main_color:        #ae1f21;
  --main_color_hover:  #e2282b;
}

textarea.txt_file_src  {
    display: block;
    /* width: 100%;  */

    background-color: #c0c0c0;
    border: 0px solid grey;
    font-size: 9pt; 

    overflow: auto;
    resize: vertical;
}

table.txt_file_dest  {

    background-color: #eec; 
    border: 1px solid grey;
    border-radius: 3px;
    font-size: 9pt;
    border-collapse: collapse;
    width: 100%;

    /* overflow: auto; */
    /* resize: none;   */
}

table.txt_file_dest  tbody  tr:nth-of-type(2n+1) {
    background-color: #00000015;         
}

table.txt_file_dest tr td  {
    vertical-align: top;
    padding: 0.0em 0.3em 0.1em 0.3em;
    border-left: solid 1px #888;
    overflow-x: hidden;
    overflow-y: hidden;
  /*  white-space: nowrap;  */
    max-width: 60em;
}



@media only screen and (min-width: 401px) and (max-width: 900px) {
    table.txt_file_dest tr td  {
        max-width: 20em;
    }
}

@media only screen and (min-width: 901px) and (max-width: 1200px) {
    table.txt_file_dest tr td  {
        max-width: 35em;
    }
}


.rounded_div {
    border-radius: 1.2em;
    padding: 0.5em;
    margin: 0.4em;
}


textarea.cmdslist {
    min-width: 22em;
    min-height: 5em;
    background-color: #ecf;
}


EOLONGTEXT );  // mod_css_index_php   
CModules::include_end(__FILE__);



class CPrjMain extends CMainPhp {

    public function __construct() {
        parent::__construct();
    }


} // CPrjMain





$js_script = <<<EOLONGTEXT

class ApplicationTest extends Application {

    name='txtcompute•';
    defaultLSCookies = {
        'file_src.value' :      'Please paste some text here',
        'elt_file_src.height' : '300px',
        'test1' :               'default value',
        'columnsOrder' :        '1;2;3;4;5;6;7;8;9;10',
        'CRinQuotesChar' :      '¶',
        'internationalParams' : '{"decimalSeparator":",","columnSeparator":";","currencySymbol":"€","dateFormat":"dd/mm/yyyy"}'
    };

    constructor() {
        super();
        this.elt_file_src = null;
        this.undo = [];
        this._i=0;
    }

    async timer () {
        // const sec =  Math.floor(Date.now() / 1000.0);

        let lastuser  = app._getLSCookie('LSC_username');
        let watermark = app._getLSCookie('LSC_watermark');
        let wm_time   = app._getLSCookie('LSC_wm_time'); 

        let json =  await this.post_cmd( 'post_tic', { user:lastuser, 
                    watermark:watermark, wm_time:wm_time } );
        if (json && json.return) {
            // app.log('ApplicationTest::timer post_tic return=' + json.return );
        }

        if (json && json.news != '')
            app.log('ApplicationTest::timer post_tic news=' + json.news );
    }

    // ========= hack ===========
    // ==========================
    log (str)               {    DmcFramework.log(str);                         }
    error (str)             {    DmcModal.show( "id_dlg_empty", str,  5 );      }
    popup (str, t=5)        {    DmcModal.show( "id_dlg_empty", str,  t );      }
    popupHtml (str, t=5)    {    DmcModal.showHtml( "id_dlg_empty", str,  t );  }
    // ==========================

    log_html(str) {
        DmcModal.hide();
        DmcModal.showHtml( "id_dlg_empty", str ); 
    }

    // ========= onload ===========
    // ============================
    async onload_after_modules() {    // window.onload = async function() 

        super.onload_after_modules();
        this.elt_file_src  = document.getElementById("id_file_src");
        this.elt_cmdlst_textarea = document.getElementById("id_cmdlst_textarea");

        // this.set_p_value( 'id_js_symbols', escapeHtml('Symbols=')+this.symbols()+"<br>\\n" );
        // calcRowHeight(this.elt_file_src);        // calc real rowHeight and store it into elt.rowHeight 

        // LSCookie stocks each cross-session var
        // ======================================
        let h = this._getLSCookie('elt_file_src.height');
        if (h != null) this.elt_file_src.style.height = h;

        h = this._getLSCookie('file_src.value');
        if (h != null) {
            this.elt_file_src.value = h;
        } 

        DmcFramework.connect_statusmsg( document.body );
        Application.connect_signals();

        this._connectLSCookies();

    } // onload_after_modules


    // =======================================
    // =======================================
    symbols() {
        return `<span>&otimes; &osol; &odot; &ocir; &oast; &plusb; &timesb; &sdotb; &vltri; &vrtri; &diamond; &sdot; 
        &Star; &bowtie; &vellip; &ctdot; &utdot; &dtdot; &bull; &hellip; &nldr; &#8228; &#8231; &#8251; &#8258; &#8270; &#8277; &#8278; &#8286; &#8285; &#8451; &#9728; &#9729; &#9730; &starf; &star; &#9737; &#9745; &#9744; &phone; &#9776; &#9783; &#9788; &#9842; &#9850; &#9851; &#9872; &#9881; &#10005; &#10006; &#10010; &#10041; &#10042; &#10052; &#10112; &#10122; </span>`;
    }



    run_cmd(cmd){
        // id_cmdlst_textarea   cmdslist
        if (cmd.startsWith('evtsig_'))
            cmd = cmd.substring(7);
        if (this.elt_cmdlst_textarea.value) this.elt_cmdlst_textarea.value += "\\n";
        this.elt_cmdlst_textarea.value += cmd;
    }

    // ========= evtsig ===========
    // ===========================


    evtsig_prog_save(event) {
        let newv = document.getElementById( 'id_cmdlst_title' ).value;
        let text  = document.getElementById( 'id_cmdlst_textarea' ).value;
        // app.log('evtsig_prog_save ['+title+']');

                //   <fieldset id="id_lsbx_processes"  mwgtclass="WgtListbox">
        let elt_list = document.getElementById( 'id_lsbx_processes' );    
        let list = WgtListbox.getLines ( elt_list );
        let listdata = WgtListbox.get_wgt_listdata( elt_list );

        if (list.includes( newv )) {
            app.error('['+newv+'] already exists, please choose another name, or delete the original.');
            return;
        } 

        list.push(newv);
        listdata[newv] = { textarea: text };
        WgtListbox.setLines (elt_list, list, listdata);        
    }

    evtsig_prog_help() {
        let method = `
Voici un exemple de programme pour créer un état personnalisé : 
---------------------------------------------- 

                              | :columns             
Bilan                         | :title               
Immobilisations incorporelles | 20>DC + 280>DC       
Immobilisations corporelles   | 21>DC + 281>DC       
Immobilisations financières   | 26>DC + 27>DC        
Stock                         | 3>DC                
Avances et acomptes           | 4091>D             
Créances clients              | 411>D+413>D+416>D+418>D+491>D 
Créances autres               | 467>D + 44551>D     
Banques                       | 512>DC               
Caisses                       | 53>DC               
Total                         | :total              
                              | :separator       
                              | :negative           
Capital social                | 101>DC              
Réserve légale                | 106>DC              
Report à nouveau              | 11>DC               
Résultat                      | 6>DC + 7>DC         
Dettes Fournisseurs           | 401>C               
Autres dettes                 | 4>C                 
Total                         | :total              
 
 
Voici un exemple de données (export écritures) 
---------------------------------------------- 
 
NumEcr;DatEcr;Journal;Compte;Libelle;Debit;Credit
9304;01/01/16;"AD";"6263";"Frais Internet";5,99;0
9305;01/01/16;"AD";"4456611";"Frais Internet";1,2;0
9306;01/01/16;"AD";"512101";"Frais Internet";0;7,19
9592;01/01/16;"OD";"110";"Report à nouveau bénéfice 94.18";0;94,18
9593;01/01/16;"OD";"120";"Report à nouveau bénéfice 94.18";94,18;0
 
 
Les écritures comptables sont un tableau séparé par des ";". 
La première ligne de ce tableau doit être le nom des colonnes. 
Voici la liste des champs reconnus : 
[NumEcr;DatEcr;Journal;Compte;Libelle;Debit;Credit] 
`;

            app.popupHtml( '<pre style="font-size:8pt;">'+escapeHtml(method)+'</pre>', 0 );

        //     'Créances autres   467102      | 467102>DC          
        // 4096>DC+1097>DC+4098>DC+425>DC+4287>DC+4387>DC+441>DC+443>DC+444>DC+4452>DC+4456>DC+44581>DC+
        // 44582>DC+4583>DC+44586>DC+4487>DC+451>DC+455>DC+456>DC+458>DC+462>DC+465>DC+467>DC+4687>DC+478>DC 

    }


    evtsig_prog_run() {  // evtsig_etatparametrable
        let method  = document.getElementById( 'id_cmdlst_textarea' ).value;

        let acc = new DmcAccounting();
        let dbEntries = acc.loadEntries( this.elt_file_src.value );

        let inner = acc.etatParametrable(dbEntries, method );
        let elt_file_dest = document.getElementById("id_file_dest"); 
        let tbody = elt_file_dest.getElementsByTagName('tbody')[0]; 
        tbody.innerHTML = inner;
        let thead_th = elt_file_dest.querySelector('thead th');
        thead_th.innerText = 'Etat paramétrable';
        DmcModal.show( "id_dlg_calcTable", 'calcTable' ); 

    }

    hideMenu(event) {
        DmcMenu.hideMenu(event.target);
    }

    // ========= slots ===========
    // ===========================
    // == file_src


    event_file_src_onpaste(event) {
        let pasteTxt = (event.clipboardData || window.clipboardData).getData('text');
        app.log('event_file_src_onpaste len=' + pasteTxt.length );        
    }

    event_file_src_onmouseup(event) {
        let elt_file_src = event.target;
        let h = elt_file_src.style.height;   if (h == '') h = elt_file_src.clientHeight+'px'; 
        this._setLSCookie('elt_file_src.height', h); 
    }

    event_file_src_oninput(event){
        let elt_file_src = event.target;
        this._setLSCookie('file_src.value', elt_file_src.value);
    }


    async slot_dropfile( files ) {
        for (let i=0; i<files.length; i++) {
            let file_obj = files[i];
            if (file_obj === undefined || file_obj === null) continue;
            app.log( 'Trying to load file ['+ file_obj.name+']');
            let upl_maxsize = 2048;  // Kb
            if (file_obj.size > upl_maxsize*1024) {  // max 64Kb
                let fs = file_obj.size/1024;
                app.error( 'Error file size = '+ fs.toFixed(0) + 'Kb > '+ upl_maxsize.toFixed(0) +'Kb is too big...' );
                return;
            }
            let json = await DmcDropfile.post_file(file_obj);
            if (json.error)
                app.error( json.msg );
            else
                app.log( 'File ['+file_obj.name+'] loaded in '+json.delay+' size='+DmcDropfile.format_size(file_obj.size)+' ' );
            let txt = atob( json['file'] );             //  atob  <=>   b64_to_utf8    <=>   binaryUtf8_toString
            let enc = find_encoding(txt);
            if (enc)  txt = binaryUtf8_toString(txt, enc);    

            this.elt_file_src.value = txt; 
            return;
        }
    }


    msig_flexvert_resize( signal ) {         // a msignal

        let h = this.elt_file_src.style.height;   
        if (!h || h=='')  h = signal.value;
        this._setLSCookie('elt_file_src.height', h); 

    }


    msig_lsbx_processes_dblclick( signal ) {         // a msignal

        let elt_list = document.getElementById( 'id_lsbx_processes' );    
        let listdata = WgtListbox.get_wgt_listdata( elt_list );
        let txt = '';

        if (signal.value in listdata) {
            txt = listdata[signal.value];
        } 

        // app.log( 'msig_lsbx_processes_dblclick = ' + signal.value );

        let elt_title = document.getElementById( 'id_cmdlst_title' );
        let elt_text  = document.getElementById( 'id_cmdlst_textarea' );

        elt_title.value = signal.value;
        elt_text.value = txt.textarea;
    }


    msig_lsbx_processes_cntxtmnu( signal ) {      // a msignal 
        app.log( 'msig_lsbx_processes_cntxtmnu = ' + signal.value );
        DmcContextmenu.showContext( signal, { line: signal.elt } );
        event.preventDefault();
        return false;
    }

    slot_fill_contextmenu( event, elt, details ) {          // a Application.signal

        /*  ----------------------------- 
            the signal 'slot_fill_contextmenu' is sent by DmcContextmenu with these parameters :
            details = {
                sender:     
                value:          
                elt_target:     
                elt_origin:     
                elt_container:  
            }
            return true if you want context menu to appear
            -----------------------------*/

        let html = '<ul id="id_mnu_context" class="dmc_contextmenu rounded_ctxmenu" '+
                'style="flex-direction:column;">' +
                '<li id="id_mnu_1" class="ul_ctxmenu_title"><p>'+ escapeHtml(details.value) +'</p></li>' +
                '<li id="id_mnu_2"><a signal="click››evtsig_ctx_removeLine">remove line</a></li>' +
                '<li id="id_mnu_2"><a signal="click››evtsig_ctx_run">run</a></li>' +
               '</ul>';

        details.sender.fillHTML( html );
        // details.sender.fill( details.value );
        app.log( 'slot_fill_contextmenu = ' + details.value );
        return true;
    }

    evtsig_ctx_run(event, elt, details) {
        let elt_list = document.getElementById( 'id_lsbx_processes' );
        let mnu_details = DmcContextmenu.getDetails();
        let line = mnu_details.line.innerText;
        DmcContextmenu.hide();
        app.log( 'evtsig_ctx_run = '+line );
    }

    evtsig_ctx_removeLine(event, elt, details) {
        let elt_list = document.getElementById( 'id_lsbx_processes' );
        let mnu_details = DmcContextmenu.getDetails();
        let line = mnu_details.line.innerText;
        WgtListbox.deleteLine (elt_list, line);
        DmcContextmenu.hide();
    }

/*
    // ================ cmds ======================
    // ============================================
*/


    async evtsig_test1() {
        DmcModal.show( "id_dlg_test1", 'This is a form test' );
    }

    async evtsig_test2() {

        let txt = 'a,b,â¬,d,Ã©,f,g';
        DmcModal.show( "id_dlg_test2", binaryUtf8_toString(txt) );
        
    }

    async evtsig_test3() {

        DmcModal.resetForms( "id_dlg_test3" );
        let v = await DmcModal.showAsync( "id_dlg_test3", 'This is a form test' );
        if (v && v.data) {
            app.log('evtsig_test3 with result='+v.result);
            app.log('  data='+ JSON.stringify(v.data) );
        } else {
            app.log('evtsig_test3 v is null');            
        }
    }

    async evtsig_test4() {
        let json =  await this.post_cmd( 'post_get_php_errors', 'testCookie', '42' );
        app.popup ( 'msg = [' + json.msg + ']' );
    }


    async evtsig_test5() {
        let json =  await this.post_cmd( 'post_test', 'testCookie', '42' );
        app.popup ( 'msg = \\n' + json.msg + '\\n' + '' );
    }


    evtsig_journal() {
        let journal = this._getLSCookie('Journal');
        let acc = new DmcAccounting();
        let dbEntries = acc.loadEntries( this.elt_file_src.value );
        let inner = acc.journal( dbEntries, journal );

        let elt_file_dest = document.getElementById("id_file_dest"); 
        let tbody = elt_file_dest.getElementsByTagName('tbody')[0]; 
        tbody.innerHTML = inner;
        let thead_th = elt_file_dest.querySelector('thead th');
        thead_th.innerText = 'Journal '+journal;
        DmcModal.show( "id_dlg_calcTable", 'calcTable' ); 

    }

    evtsig_about(){
        app.popupHtml(`
<h2>txt-processor</h2>
<p><br></p>

<ul>
<li>&copy; 2023-01  JF Lemay (FR) (contact via github Jeff42820)
    <ul>
    <li><a href="https://github.com/Jeff42820">github.com/Jeff42820</a></li>
    </ul>
    <br>
</li>
<li>MIT License
<pre style="font-size:7pt;">Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
SOFTWARE.</pre>
<br>
</li>
<li>Many thanks to 
    <ul>
    <li><a href="https://stackoverflow.com/">
        <img src="icons/stackoverflow.svg" width="32" height="32" />stackoverflow.com</a></li>
    <li><a href="https://github.com/">
        <img src="icons/github.svg"        width="32" height="32" />github.com</a></li>
    <li><a href="https://framework7.io/icons/">
        <img src="icons/framework7.svg"    width="32" height="32" />framework7.io/icons/</a></li>
    </ul>
</li>
</ul>


        `, 30);
    }

    evtsig_compte() {
        let compte = this._getLSCookie('Compte');
        let acc = new DmcAccounting();
        let dbEntries = acc.loadEntries( this.elt_file_src.value );
        let inner = acc.journal( dbEntries, null, compte );

        let elt_file_dest = document.getElementById("id_file_dest"); 
        let tbody = elt_file_dest.getElementsByTagName('tbody')[0]; 
        tbody.innerHTML = inner;
        let thead_th = elt_file_dest.querySelector('thead th');
        thead_th.innerText = 'Compte '+compte;
        DmcModal.show( "id_dlg_calcTable", 'calcTable' ); 
    }

    evtsig_grandJournal() {
        let acc = new DmcAccounting();
        let dbEntries = acc.loadEntries( this.elt_file_src.value );
        let inner = acc.journal(dbEntries);

        let elt_file_dest = document.getElementById("id_file_dest"); 
        let tbody = elt_file_dest.getElementsByTagName('tbody')[0]; 
        tbody.innerHTML = inner;
        let thead_th = elt_file_dest.querySelector('thead th');
        thead_th.innerText = 'Grand journal';
        DmcModal.show( "id_dlg_calcTable", 'calcTable' ); 
    }



    evtsig_balance() {
        let acc = new DmcAccounting();
        let dbEntries = acc.loadEntries( this.elt_file_src.value );
        let inner = acc.balance(dbEntries);

        let elt_file_dest = document.getElementById("id_file_dest"); 
        let tbody = elt_file_dest.getElementsByTagName('tbody')[0]; 
        tbody.innerHTML = inner;
        let thead_th = elt_file_dest.querySelector('thead th');
        thead_th.innerText = 'Balance';
        DmcModal.show( "id_dlg_calcTable", 'calcTable' ); 
    }

    evtsig_calcTable() {

        let elt_file_dest = document.getElementById("id_file_dest"); 
        let tbody = elt_file_dest.getElementsByTagName('tbody')[0]; 
        let thead_th = elt_file_dest.querySelector('thead th');
        thead_th.innerText = 'Table';
        let lines = this.elt_file_src.value.split('\\n');
        let inner='';
        for (let row=0; row<lines.length; row++) {
            let line = '<tr>';
            let cols = columnSplit( lines[row] );
            for (let col=0; col<cols.length; col++) {
                let style='';
                if (isNumeric(cols[col]))
                    style='style="text-align: right;"';
                if (isDate(cols[col]))
                    style='style="text-align: center;"';
                line += '<td '+style+'>' + cols[col] + '</td>';
            }
            line += '</tr>\\n';
            inner += line;
        }
        tbody.innerHTML = inner;
        DmcModal.show( "id_dlg_calcTable", 'calcTable' ); 

    }




    async evtsig_reset_log_php() {
        let json =  await this.post_cmd( 'post_reset_php_errors' );
        if (json && !json.error) {
            app.popupHtml('<i class="f7-icons">hand_thumbsup</i> reset_log_php success', 2);
        } else {
            app.popup('reset_log_php error', 5);            
        }
    }


    async evtsig_log_php() {

        let elt_file_dest = document.getElementById("id_file_dest"); 
        let tbody = elt_file_dest.getElementsByTagName('tbody')[0]; 

        let json =  await this.post_cmd( 'post_get_php_errors' );
        let lines = (json.log_lines).split('\\n');
        let inner = '';
        for (let row=0; row<lines.length; row++) {
            let line = '<tr>';
            line += '<td>' + (row+1) + '</td>';
            line += '<td>' + lines[row] + '</td>';
            line += '</tr>\\n';
            inner += line;
        }
        tbody.innerHTML = inner;
        DmcModal.show( "id_dlg_calcTable", 'calcTable' ); 

    }


    evtsig_reset_cookies() {
        this._resetLSCookie();
        app.log('evtsig_reset_cookies');
    }


    evtsig_copy( ) {
        this.elt_file_src.focus();
        this.elt_file_src.select();
        document.execCommand('copy');
        this.elt_file_src.setSelectionRange(0, 0);
        // this.elt_file_src.select();
        app.log('copy');
        this.hideMenu(event);
    }
    
    async evtsig_parameters( event ) {
        let np = JSON.parse( this.defaultLSCookies.internationalParams );
        let str = this._getLSCookie('internationalParams');  
        let p = {}; 
        try {
            if (str) p = JSON.parse(str);
        } catch(e) {
        }

        this.setModalFormValues( 'id_dlg_parameters', p );
        let v = await DmcModal.showAsync( 'id_dlg_parameters' );
        if (v.result == 'SUBMIT') {
            for (let key in np)   p[key] = v.data[key];
            str = JSON.stringify(p);
            app.log('  p='+ str );
            this._setLSCookie('internationalParams', str );  
        }
    }


    async evtsig_empty( event ) {
        let v = await DmcModal.showAsync( "id_dlg_yes_no", 'Are you sure ?' );
        app.log ( 'id_dlg_yes_no result = '+ v.result);
        if (v.result != 'SUBMIT') return;
        this.undo.push( this.elt_file_src.value );
        this.elt_file_src.focus();
        this.elt_file_src.value = '';
        this.elt_file_src.select();
        app.log('empty');
        this.hideMenu(event);
    }
    
    evtsig_undo( event, elt, details ) {
        if (this.undo.length == 0) {
            app.log('Error : nothing to undo');
            return;
        }
        this.elt_file_src.value = this.undo.pop();
        app.log('undo');
    }


    evtsig_decodeFromISO_8859_15(event, elt, details) {
        this.run_cmd(details.slotName); 
        this.undo.push( this.elt_file_src.value );
        this.elt_file_src.value = from_ISO_8859_15( this.elt_file_src.value );
        app.log('decode from ISO_8859_15');
        this.hideMenu(event);
    }

    evtsig_changeColumnToSemicolonFromComma(event, elt, details) {
        this.run_cmd(details.slotName); 
        this.undo.push( this.elt_file_src.value );
        this.elt_file_src.value = changeColumn(this.elt_file_src.value, ',', ';');
        app.log('change column [tab] to [;]');
        this.hideMenu(event);
    }

    evtsig_changeColumnToSemicolon(event, elt, details) {
        this.run_cmd(details.slotName); 
        this.undo.push( this.elt_file_src.value );
        this.elt_file_src.value = changeColumn(this.elt_file_src.value, '\t', ';');
        app.log('change column [tab] to [;]');
        this.hideMenu(event);
    }

    evtsig_changeColumnToTab(event, elt, details){
        this.run_cmd(details.slotName); 
        this.undo.push( this.elt_file_src.value );
        this.elt_file_src.value = changeColumn(this.elt_file_src.value, ';', '\t' );        
        app.log('change column [;] to [tab]');
        this.hideMenu(event);
    }

    evtsig_changeCRinQuotes( event, elt, details ) {
        let newChar = this._getLSCookie('CRinQuotesChar');  // '¶'
        let cmd = details.slotName + "('"+escapeBackslashChars(newChar)+"')";
        this.run_cmd(cmd); 

        app.log("run "+cmd);
        this.undo.push( this.elt_file_src.value );
        this.elt_file_src.value = changeCRinQuotes( this.elt_file_src.value, newChar);
        app.log('change CR in Quotes');
        this.hideMenu(event);
    }

    evtsig_changeColumnsOrder(event, elt, details) {
        let order = this._getLSCookie('columnsOrder');  // '¶'
        let cmd = details.slotName + "('"+order+"')";
        this.run_cmd(cmd); 

        this.undo.push( this.elt_file_src.value );
        let newOrder = order.split(';');
        let lines = this.elt_file_src.value.split('\\n');
        let txt='';
        for (let row=0; row<lines.length; row++) {
            txt += changeOrder( lines[row], newOrder ) + '\\n';
        }
        this.elt_file_src.value = txt;

        app.log('change columns order');
        this.hideMenu(event);        
    }

    evtsig_removeExtraSpaces(event, elt, details) {
        this.run_cmd(details.slotName); 
        this.undo.push( this.elt_file_src.value );
        this.elt_file_src.value = removeRepeatedChar(this.elt_file_src.value, '\\u0020');
        app.log('remove extra spaces');
        this.hideMenu(event);
    }

    evtsig_removeExtraTabs(event, elt, details) {
        this.run_cmd(details.slotName); 
        this.undo.push( this.elt_file_src.value );
        this.elt_file_src.value = removeRepeatedChar(this.elt_file_src.value, '\t');
        app.log('remove extra spaces');
        this.hideMenu(event);
    }


    evtsig_removeNoBreakSpaces(event, elt, details) {
        this.run_cmd(details.slotName); 
        this.undo.push( this.elt_file_src.value );
        this.elt_file_src.value = removeNoBreakSpaces( this.elt_file_src.value );
        app.log('remove no-break spaces');
        this.hideMenu(event);
    }

    evtsig_changeDecimalSepFromPoint(event, elt, details){
        this.run_cmd(details.slotName); 
        this.undo.push( this.elt_file_src.value );
        this.elt_file_src.value = changeDecimalSepFromPoint( this.elt_file_src.value );
        app.log('change decimal separator [.] &gt; [,]');
        this.hideMenu(event);        
    }

    // ================ end of cmds ===============
    // ============================================


}

let app = new ApplicationTest(); 

EOLONGTEXT;     

/*


*/

$html_dialogs = <<<EOLONGTEXT

<div class="dm_modal_container">

    <div id="id_dlg_test1"    class="dmc_modal fade-in" style="display:none;">
      <div class="dm_modal_content">
        <span class="btn_modal_close" data-modal="btn_close">&times;</span>
            <p data-modal="p_text" data-title="title_bar">Title</p>
      </div>
    </div>

    <dialog id="id_dlg_test2"    class="dmc_modal fade-in" style="display:none;">
      <div class="dm_modal_content">
        <span class="btn_modal_close" data-modal="btn_close">&times;</span>
            <p data-modal="p_text" data-title="title_bar">Title</p>
      </div>
    </dialog>

    <dialog id="id_dlg_test3" class="dmc_modal fade-in" style="display:none;">
      <div id="id_dlg_content" class="dm_modal_content">
        <span class="btn_modal_close" data-modal="btn_close">&times;</span>
          <form method="dialog">
            <p data-modal="p_text" data-title="title_bar"></p>
            <table>
            <tr><td><label for="favAnimal">Favorite animal:</label></td>
                <td><select id="favAnimal" name="favAnimal">
                <option></option>
                <option>Brine shrimp</option>
                <option>Red panda</option>
                <option>Spider monkey</option>
                </select></td>
            </tr>
            <tr><td><label for="favCar">Favorite car:</label></td>
                <td><input type="text" id="favCar" name="favCar" value="" placeholder="Peugeot for example" /></td>
            </tr>
            <tr><td><label for="favCar">Favorite monster:</label></td>
                <td>
<fieldset name="favMonster">
    <legend>Choose your favorite monster</legend>
    <input type="radio" id="kraken" name="monster" value="K"    /><label for="kraken">Kraken</label><br>
    <input type="radio" id="sasquatch" name="monster" value="S" /><label for="sasquatch">Sasquatch</label><br>
    <input type="radio" id="mothman" name="monster" value="M"   /><label for="mothman">Mothman</label>
</fieldset>
                </td>
            </tr>
            </table>
            <div>
              <button type="reset">Reset</button>
              <button type="reset"  value="cancel" onclick="DmcModal.do_cancel(this);">Cancel</button>
              <button type="submit" value="submit">Ok</button>
            </div>
          </form>
      </div>
    </dialog>


    <dialog id="id_dlg_parameters" class="dmc_modal fade-in" style="display:none;">
      <div id="id_dlg_content" class="dm_modal_content">
        <span class="btn_modal_close" data-modal="btn_close">&times;</span>
          <p data-modal="p_text" data-title="title_bar">Parameters</p>
          <form method="dialog" style="display:flex; justify-content:center;">
            <table>
            <tr><td><label for="decimalSeparator">decimalSeparator :</label></td>
                <td><input type="text" id="id_decimalSeparator" name="decimalSeparator" value="," 
                    size="3" minlength="1" required /></td>
            </tr>
            <tr><td><label for="columnSeparator">columnSeparator :</label></td>
                <td><input type="text" id="id_columnSeparator" name="columnSeparator" value=";" 
                    size="3" minlength="1" required /></td>
            </tr>
            <tr><td><label for="currencySymbol">currencySymbol :</label></td>
                <td><input type="text" id="id_currencySymbol" name="currencySymbol" value="€" 
                    size="3" minlength="1" required /></td>
            </tr>
            <tr><td><label for="dateFormat">dateFormat :</label></td>
                <td><input type="text" id="id_dateFormat" name="dateFormat" value="dd/mm/yyyy" 
                    size="10" required /></td>
            </tr>
            <tr><td colspan="100%">
            <div class="dm_modal_buttons">
              <span></span>
              <button type="reset">Reset</button>
              <button type="reset"  value="retCancel" onclick="DmcModal.do_cancel(this);">Cancel</button>
              <button type="submit" value="retSubmit">Ok</button>
            </div>
        </td></tr>
            </table>
          </form>
          </div>
    </dialog>




    <dialog id="id_dlg_empty" class="dmc_modal fade-in">
      <div class="dm_modal_content">
        <span class="btn_modal_close" data-modal="btn_close">&times;</span>
            <p data-modal="p_text"></p>
      </div>
    </dialog>



    <dialog id="id_dlg_yes_no" class="dmc_modal fade-in">
      <div class="dm_modal_content">
        <span class="btn_modal_close" data-modal="btn_close">&times;</span>
        <form method="dialog"  style="display:flex; justify-content:center;">
            <p data-modal="p_text"></p>
            <p style="display:flex;justify-content:flex-end;">
                <button id="id_modal_yes" type="submit" value="submit">Yes</button>
                <button id="id_modal_no"  type="reset"  value="no" onclick="DmcModal.do_cancel(this);">No</button>
            </p>
        </form>
      </div>
    </dialog>


    <dialog id="id_dlg_calcTable" class="dmc_modal fade-in">
      <div id="id_dlg_content" class="dm_modal_content">
        <span class="btn_modal_close" data-modal="btn_close">&times;</span>
        <table id="id_file_dest" class="txt_file_dest rounded_div">
            <thead><tr><th scope="col" colspan="100%">-----</th></tr></thead>
            <tbody>
            </tbody>
        </table>
      </div>
    </dialog>

</div> <!-- class="dm_modal_container" -->


EOLONGTEXT;     



$html_menu = <<<EOLONGTEXT


<ul class="dmc_menu rounded_div">
    <li id="id_mnu_filters"><a>Filters</a>
        <div class="div_menu">
        <a signal="click››evtsig_decodeFromISO_8859_15" 
            statusmsg="if you have È instead of [é], Ù [ô], Ä [€], ∞ [°] ...">correct Mac Roman -  ISO_8859_15 errors</a>
        <a signal="click››evtsig_changeColumnToSemicolonFromComma"
            statusmsg="change column delimiter [,] =&gt; [;] (but not inside &quot;strings&quot;)">change column delimiter [,] =&gt; [;]</a>
        <a signal="click››evtsig_changeColumnToSemicolon"
            statusmsg="change column delimiter [tab] =&gt; [;] (but not inside &quot;strings&quot;)">change column delimiter [tab] =&gt; [;]</a>
        <a signal="click››evtsig_changeColumnToTab"
            statusmsg="change column delimiter [;] =&gt; [tab] (but not inside &quot;strings&quot;)">change column delimiter [;] =&gt; [tab]</a>
        <form LSCookie="y"><table>
            <tr><td><label signal="click››evtsig_changeCRinQuotes" style="cursor:pointer;"
                statusmsg="inside &quot;strings&quot; change char (LF) (\\n) (0x0a) to something else">change in quotes '\\n' to :</label></td>
                <td><input type="text" LSCookie="CRinQuotesChar" style="width:2em;" /></td></tr>
        </table></form>
        <form LSCookie="y"><table>
            <tr><td><label signal="click››evtsig_changeColumnsOrder" style="cursor:pointer;"
                statusmsg="example of syntax :    1;2;if+3;if-3;4>5;7>">change columns order</label></td>
                <td><input type="text" LSCookie="columnsOrder" style="width:5em;" /></td></tr>
        </table></form>
        <a signal="click››evtsig_removeExtraSpaces"
                statusmsg="many spaces are changed in only one space (0x20)">remove extra spaces</a>
        <a signal="click››evtsig_removeExtraTabs"
                statusmsg="many tabs are changed in only one tab (\\t) (0x09) char">remove extra tabs</a>
        <a signal="click››evtsig_removeNoBreakSpaces"
                statusmsg="char 'no-break space' (&amp;nbsp;) (0xa0) are removed">remove no-break spaces</a>
        <a signal="click››evtsig_changeDecimalSepFromPoint"
                statusmsg="from 123.50 to 123,50 [.] =&gt; [,] (no change in quotes)">change decimal separator [.] =&gt; [,]</a>
        </div>
    </li>

    <li id="id_mnu_tools"><a>Tools</a>
        <div class="div_menu">
        <a signal="click››evtsig_undo">undo</a>
        <a signal="click››evtsig_copy">copy</a>
        <a signal="click››evtsig_empty">empty</a>
      <!--  <a signal="click››evtsig_parameters">parameters</a>  -->
        </div>
    </li>
    
    <li id="id_mnu_tests"><a>Tests</a>
        <div class="div_menu">
        <ul>
            <li><a>tests &vrtri;</a>
                <div>
                <a signal="click››evtsig_test1">evtsig_test1</a>
                <a signal="click››evtsig_test2">evtsig_test2</a>
                <a signal="click››evtsig_test3">evtsig_test3</a>
                <a signal="click››evtsig_test4">evtsig_test4</a>
                <a signal="click››evtsig_test5">evtsig_test5</a>
                </div>
            </li>
        </ul>
        <a signal="click››evtsig_log_php">show log_php</a>
        <a signal="click››evtsig_reset_log_php">clear log_php</a>
        <a signal="click››evtsig_reset_cookies">reset cookies</a>
        </div>
    </li>

    <li id="id_mnu_calcs"><a>Calcs</a>
        <div class="div_menu">
        <a  signal="click››evtsig_calcTable">Show table</a>
        <a  signal="click››evtsig_grandJournal">Grand Journal</a>
        <a  signal="click››evtsig_balance">Balance</a>
        <form LSCookie="y"><table>
            <tr><td><label signal="click››evtsig_journal" style="cursor:pointer;"
                statusmsg="keywords : NumEcr, DatEcr, Journal, Compte, NumDoc, Libelle, Piece, Debit, Credit, Poste, DatSai">Journal : </label></td>
                <td><input type="text" LSCookie="Journal" style="width:5em;" /></td></tr>
        </table></form>
        <form LSCookie="y"><table>
            <tr><td><label signal="click››evtsig_compte" style="cursor:pointer;"
                statusmsg="keywords : NumEcr, DatEcr, Journal, Compte, NumDoc, Libelle, Piece, Debit, Credit, Poste, DatSai">Compte : </label></td>
                <td><input type="text" LSCookie="Compte" style="width:5em;" /></td></tr>
        </table></form>
        </div>
    </li>

    <li id="id_mnu_calcs"><a signal="click››evtsig_about">About</a>
    </li>


</ul>


<ul class="dmc_menu rounded_div">
    <li id="id_mnu_login"><a id="id_a_login" onclick="WgtLogin.showDialog(this);">login</a>
    </li>
</ul>

EOLONGTEXT;     









function main() {
    global $CR, $m; 
    $dm = CModules::$_this;
    $m = new CPrjMain();
    $m->init_session();
    $m->read_post_data();
    $m->header();
    $m->head();

    $m->body_begin();

    $dm->mod_echo_jsmain();
    global $js_script;   echo "<script>\n", $js_script, "</script>\n";
    $dm->mod_echo_html('div-contextmenu.mod.php',  'mod_html_div_contextmenu');
    $dm->mod_echo_html('prj-wgtlogin.mod.php',     'mod_html_div_wgtlogin');


    global $html_dialogs;  
    $dm->mod_echo_html('div-framework.mod.php', 'mod_html_div_fw_dialog', $html_dialogs."\n");

    global $html_menu;      
    $dm->mod_echo_html('div-framework.mod.php', 'mod_html_div_fw_menu', $html_menu."\n");



    $dm->mod_echo_elt_begin('div-framework.mod.php', 'mod_html_div_fw_main');



        // ======================
        // div-toolbar.mod.php
        // ======= can be removed
        /*
            $dm->mod_echo_elt_begin('div-toolbar.mod.php', 'mod_html_div_toolbar');

            echo html_btn_svg( 'div-icons-1.mod.php', 'mod_svg_message', '2.5em', [
                ['id', 'btn_message'], 
                ['class', 'dmc_tb_icon tr_rotateover'],  
                ['onclick', 'app.evtsig_changeCRinQuotes()'], 
                ['statusmsg', 'change CR in quotes'] ]), "\n";
            echo html_btn_svg( 'div-icons-1.mod.php', 'mod_svg_message', '2.5em', [
                ['id', 'btn_set_pcookie'], 
                ['class', 'dmc_tb_icon tr_rotateover'],  
                ['statusmsg', '== btn_set_pcookie =='] ]), "\n";
            echo html_btn_svg( 'div-icons-1.mod.php', 'mod_svg_message', '2.5em', [
                ['id', 'btn_reset_cookies'], 
                ['class', 'dmc_tb_icon tr_rotateover'],  
                ['onclick', 'app.evtsig_reset_cookies()'], 
                ['statusmsg', 'btn_reset_cookies'] ]), "\n";
            $dm->mod_echo_html('div-toolbar.mod.php', 'mod_html_div_message');
            $dm->mod_echo_elt_end('div-toolbar.mod.php', 'mod_html_div_toolbar');
        // div-toolbar.mod.php
        // ======= can be removed
        */
        // ======================



        echo $dm->sml_mustache( <<<EOLONGTEXT

        <div class="dmc_flexvert" style="flex:1;">

        <textarea id="id_file_src" class="dmc_flexvert_child_fix txt_file_src rounded_div" 
            onpaste="app.event_file_src_onpaste(event)" 
            oninput="app.event_file_src_oninput(event)" 
            onmouseup="app.event_file_src_onmouseup(event)"
            msignal="resize_mouseup››msig_flexvert_resize">    
        </textarea>

        <div id="id_flexvert_for_file_src" class="dmc_flexvert_child_separator" > 
        </div>

        <div class="dmc_flexvert_child_expand" > 
          <div class="dmc_respcardcontainer">
            <div id="id_card1" class="dmc_respcard">
                <p>Programme</p>
                <input id="id_cmdlst_title" type="text" class="cmdslist" name="title" 
                    style="display:block; margin:0 auto; width:97%;" value=""/>
                <textarea id="id_cmdlst_textarea" class="cmdslist"></textarea>
                <p><button type="button" signal="click››evtsig_prog_save">
                 <i class="f7-icons" style="font-size:1.8em; color:#333;">tray_arrow_down_fill</i>
                 Sauve</button>
                <button type="button" signal="click››evtsig_prog_run">
                 <i class="f7-icons" style="font-size:1.8em; color:#333;">play_rectangle_fill</i>
                 Lance</button>
                <button type="button" signal="click››evtsig_prog_help">
                 <i class="f7-icons" style="font-size:1.8em; color:#333;">question_square_fill</i>
                 Exemple</button></p>
            </div>

            <div id="id_card3" class="dmc_respcard">
                <fieldset id="id_lsbx_processes"  mwgtclass="WgtListbox" 
                    msignal="item_dblclick››msig_lsbx_processes_dblclick,item_contextmenu››msig_lsbx_processes_cntxtmnu"  
                    data-LSCookie="lsbx_processes_content"
                    class="wgt_listbox wgt_listbox_oneonly" style="max-height:5em;">
                    <legend>
                     <i class="f7-icons" style="font-size:1.8em; color:#333;">tray_fill</i>
                     Liste des programmes</legend> 
                </fieldset>
                <p>(double-click pour le charger)</p>
                <p>(click-droite pour le menu ctx)</p>
            </div>

            <div class="dmc_respcard">
                {{mod_html_div_dropfile}}
            </div>
          </div>
        </div>
        </div>

        EOLONGTEXT, [ 
            'mod_html_div_dropfile' => $dm->mod_get_elt( 'div-dropfile.mod.php', 'mod_html_div_dropfile' ) 
        ] );

/*

    <!-- 
            <div class="dmc_respcard">
                <p>test <i class="f7-icons">person</i> test</p>
                <p>test <i class="f7-icons" style="font-size:0.8em; color:white;">person</i> test</p>
                <p>test</p>
                <p>test</p>
            </div>
    &#x2794; 
    -->

*/

    $dm->mod_echo_elt_end('div-framework.mod.php', 'mod_html_div_fw_main');
    $dm->mod_echo_html('div-framework.mod.php',    'mod_html_fw_status');

    $m->send_phpMsg_to_jsMsg();
    $m->body_end();
}


main();



?>