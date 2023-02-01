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
include './src/div-icons-1.mod.php';            // division module : some embeded svg icons
// include './src/div-toolbar.mod.php';             //
include './src/div-accounting.mod.php';         // comptabilité
include './src/div-tooltips.mod.php';           // tooltips

// == 4 project modules ==
include './src/prj-header-html.mod.php';                // prj header_html
include './src/prj-utils-css.mod.php';                  // prj css_main
include './src/prj-utils-css-icons.mod.php';            // prj icons 
include './src/prj-wgtlistbox.mod.php';                 // Widget Listbox
include './src/prj-wgttable.mod.php';                   // Widget Table
include './src/prj-wgtlogin.mod.php';                   // Widget Login




CModules::include_begin(__FILE__, 'This is index.php');


CModules::append( 'mod_css_index_php', <<<EOLONGTEXT


:root{
  /*  _m is for memory : used to recall initial value  */
  --main_color:        #ae1f21;
  --main_color_hover:  #e2282b;
}

textarea.std_textarea  {
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


textarea.program_edit {
    min-width: 22em;
    min-height: 5em;
    background-color: #ecf;
    width: calc(100% - 1.5em);
    resize: vertical;  
    padding-left: 0.5em;
    padding-right: 0.5em;
    margin: 0.1em;
}

input[type="text"].program_edit  {
    max-width: 10em;
}

button[type="button"].program_edit {
    background-color: var(--main_color);
    padding:  0.5em;
    border-radius: 1em;
    border: 0;
}

button[type="button"].program_edit  i{
    color: white;
}

.appli_std_color {
    color: white;
    background-color: var(--main_color);  
}

a.help_link {
    text-decoration-style: dotted;
}

div.dlg_bnk_btns button[type="button"] {
    color: #eee;
    background-color: var(--main_color);  
    padding:  0.5em;
    margin:  0.5em;
    border-radius: 1em;
}

div.dlg_bnk_dwnld {
    color: #eee;
    background-color: var(--main_color);  
    padding:  0.5em;
    margin:  0.5em;
    border-radius: 1em;    
}


div.dlg_bnk_dwnld a,            div.dlg_bnk_dwnld a:link, 
div.dlg_bnk_dwnld a:visited,    div.dlg_bnk_dwnld a:active {
  /*  border: solid 1px #eee;   */
    color: #eee;
    border-radius: 0.4em;
    margin: 0.4em;
    padding: 0.2em;
    text-decoration: none;
    cursor: pointer;
}

div.dlg_bnk_dwnld a:hover {

    background-color: var(--main_color_hover);  

 /*   background-color: #fff3;  */
    text-decoration: underline;
  /*  border: solid 1px #eee8;  */
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
        'lst_textarea.value' :  '',
        'elt_file_src.height' : '300px',
        'elt_prog_list.width' : '150px',
        'test1' :               'default value',
        'columnsOrder' :        '1;2;3;4;5;6;7;8;9;10',
        'reportCellIfNext' :    '1',
        'removeRowIfNoDate' :   '1',
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
        const sec =  Math.floor(Date.now() / 1000.0);
        let icon = (sec % 2) == 0;

        let lastuser  = app._getLSCookie('LSC_username');
        let watermark = app._getLSCookie('LSC_watermark');
        let wm_time   = app._getLSCookie('LSC_wm_time'); 

        let json =  await this.post_cmd( 'post_tic', { user:lastuser, 
                    watermark:watermark, wm_time:wm_time } );

        if (!json) {
            app.log( 'ApplicationTest::timer post_tic returns null json' );
            return;
        }

        // heart beat is ok : 
        let elt_php_connected = document.getElementById("id_php_connected");
        elt_php_connected.style.color = icon ? 'black' : 'white';

        // signal a slow communication :
        if (json.return && json._duration > 200) {
            app.log('ApplicationTest::timer post_tic returns=' + 
                    json.return + ', duration=' + 
                    json._duration );
        }

        // if there are news ....
        if (json.news != '') {
            // app.log('ApplicationTest::timer post_tic news=' + json.news );
        }
        
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
        this.elt_prog_list = document.getElementById('id_prog_list'); 


        // add drop capabilities to elt_cmdlst_textarea
        this.elt_file_src.ondrop =       DmcDropfile._this.ev_dropfile;
        this.elt_file_src.ondragover =   DmcDropfile._this.ev_dragover; 
        this.elt_file_src.ondragleave =  DmcDropfile._this.ev_dragleave;        



        // this.set_p_value( 'id_js_symbols', escapeHtml('Symbols=')+this.symbols()+"<br>\\n" );
        // calcRowHeight(this.elt_file_src);        // calc real rowHeight and store it into elt.rowHeight 

        // LSCookie stocks each cross-session var
        // ======================================
        let h = this._getLSCookie('elt_file_src.height');
        if (h != null) this.elt_file_src.style.height = h;

        h = this._getLSCookie('elt_prog_list.width');
        if (h != null) this.elt_prog_list.style.width = h;

        h = this._getLSCookie('file_src.value');
        if (h != null) this.elt_file_src.value = h;
        
        /*  h = this._getLSCookie('elt_cmdlst_textarea.width');
            if (h != null) this.elt_cmdlst_textarea.style.width = h;  */

        h = this._getLSCookie('elt_cmdlst_textarea.height');
        if (h != null) this.elt_cmdlst_textarea.style.height = h;

        h  = this._getLSCookie('lst_textarea.value');
        if (h != null) this.elt_cmdlst_textarea.value = h;

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




    // ========= evtsig ===========
    // ===========================


    async evtsig_prog_save(event) {
        let newv = document.getElementById( 'id_cmdlst_title' ).value;
        let text  = document.getElementById( 'id_cmdlst_textarea' ).value;

        let elt_list = document.getElementById( 'id_lsbx_processes' );    
        let list = WgtListbox.getLines ( elt_list );
        let listdata = WgtListbox.get_wgt_listdata( elt_list );

        if (list.includes( newv )) {
            // app.error('['+newv+'] already exists, do you want to replace it ?');
            let v = await DmcModal.showAsync( "id_dlg_yes_no", '['+newv+'] already exists, do you want to replace it ?' );
            if (v.result != 'SUBMIT') return;
            listdata[newv] = { textarea: text };            
        } else {
            list.push(newv);
            listdata[newv] = { textarea: text };            
        }

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





    pcmd_decodeFromISO_8859_15( src ) {
        return from_ISO_8859_15( src );  
    }

    pcmd_removeExtraSpaces( src ) {
        return trimEachCell( removeRepeatedChar( src, '\\u0020') );
    }

    pcmd_removeExtraTabs( src ) {
        return removeRepeatedChar( src, '\t');
    }

    pcmd_removeNoBreakSpaces( src ) {
        return removeNoBreakSpaces( src );
    }

    pcmd_removeQuotes( src ) {
        return removeQuotes1( src );
    }

    pcmd_removeMoneySign( src ) {
        return removeMoneySign( src );
    }

    pcmd_dateToComptaDate( src ) {
        return dateToComptaDate( src );
    }

    pcmd_changeCRinQuotes( src, newChar ) {
        return changeCRinQuotes( src, newChar );
    }

    pcmd_changeColumnToSemicolonFromComma( src ) {
        return changeColumn(src, ',', ';');
    }

    pcmd_changeColumnTabToSemicolon( src ) {
        return changeColumn(src, '\t', ';');
    }

    pcmd_changeDelimiterSemicolumnToTab( src ) {
        return changeColumn(src, ';', '\t' );        
    }

    pcmd_changeDecimalSepFromPoint( src ) {
        return changeDecimalSepFromPoint( src );
    }

    pcmd_changeColumnsOrder( src, order ) {
        let newOrder = order.split(';');
        let txt='', lines = src.split('\\n');
        for (let row=0; row<lines.length; row++) {
            txt += changeOrder( lines[row], newOrder ) + '\\n';
        }
        return txt;
    }

    pcmd_reportCellIfNextIsEmpty( src, colNum ) {
        if (typeof colNum == 'string')  colNum = parseInt( colNum, 10 );
        return reportCellIfNoNext(this.elt_file_src.value, colNum);
    }

    pcmd_removeRowIfNoDate( src, colNum ) {
        if (typeof colNum == 'string')  colNum = parseInt(colNum, 10);
        return removeRowIfNoDate( src, colNum );
    }

    pcmd_addRowAtTop( src, row ) {
        return row + '\\n' + src;
    }




    evtsig_changeCRinQuotes( event, elt, details ) {
        this.hideMenu(event);
        this.undo.push( this.elt_file_src.value );
        let newChar = this._getLSCookie('CRinQuotesChar');
        // newChar = escapeBackslashChars(newChar); 
        this.elt_file_src.value = this.pcmd_changeCRinQuotes( this.elt_file_src.value, newChar );
        // this.add_cmd_to_prog('changeCRinQuotes', "'"+newChar+"'"); 
    }

    evtsig_decodeFromISO_8859_15(event, elt, details) {
        this.hideMenu(event);
        this.undo.push( this.elt_file_src.value );
        this.elt_file_src.value = this.pcmd_decodeFromISO_8859_15( this.elt_file_src.value );
        // this.add_cmd_to_prog('decodeFromISO_8859_15'); 
    }

    evtsig_removeExtraSpaces(event, elt, details) {
        this.hideMenu(event);
        this.undo.push( this.elt_file_src.value );
        this.elt_file_src.value = this.pcmd_removeExtraSpaces( this.elt_file_src.value  );        
        // this.add_cmd_to_prog('removeExtraSpaces'); 
    }

    evtsig_removeExtraTabs(event, elt, details) {
        this.hideMenu(event);
        this.undo.push( this.elt_file_src.value );
        this.elt_file_src.value = this.pcmd_removeExtraTabs( this.elt_file_src.value  );        
        // this.add_cmd_to_prog('removeExtraTabs'); 
    }

    evtsig_removeNoBreakSpaces(event, elt, details) {
        this.hideMenu(event);
        this.undo.push( this.elt_file_src.value );
        this.elt_file_src.value = 
            this.pcmd_removeNoBreakSpaces( this.elt_file_src.value  );        
        // this.add_cmd_to_prog('removeNoBreakSpaces'); 
    }

    evtsig_removeQuotes(event, elt, details) {
        this.hideMenu(event);
        this.undo.push( this.elt_file_src.value );
        this.elt_file_src.value = 
            this.pcmd_removeQuotes( this.elt_file_src.value  );        
        // this.add_cmd_to_prog('removeQuotes'); 
    }

    evtsig_removeMoneySign(event, elt, details) {
        this.hideMenu(event);
        this.undo.push( this.elt_file_src.value );
        this.elt_file_src.value = 
            this.pcmd_removeMoneySign( this.elt_file_src.value  );        
        // this.add_cmd_to_prog('removeMoneySign'); 
    }

    evtsig_dateToComptaDate(event, elt, details) {
        this.hideMenu(event);
        this.undo.push( this.elt_file_src.value );
        this.elt_file_src.value = 
            this.pcmd_dateToComptaDate( this.elt_file_src.value  );        
        // this.add_cmd_to_prog('dateToComptaDate'); 
    }

    evtsig_changeColumnToSemicolonFromComma(event, elt, details) {
        this.hideMenu(event);
        this.undo.push( this.elt_file_src.value );
        this.elt_file_src.value = 
            this.pcmd_changeColumnToSemicolonFromComma( this.elt_file_src.value  );        
        // this.add_cmd_to_prog('changeColumnToSemicolonFromComma'); 
    }

    evtsig_changeColumnTabToSemicolon(event, elt, details) {
        this.hideMenu(event);
        this.undo.push( this.elt_file_src.value );
        this.elt_file_src.value = 
            this.pcmd_changeColumnTabToSemicolon( this.elt_file_src.value  );        
        // this.add_cmd_to_prog('changeColumnTabToSemicolon'); 
    }

    evtsig_changeDelimiterSemicolumnToTab(event, elt, details){
        this.hideMenu(event);
        this.undo.push( this.elt_file_src.value );
        this.elt_file_src.value = 
            this.pcmd_changeDelimiterSemicolumnToTab( this.elt_file_src.value  );        
        // this.add_cmd_to_prog('changeDelimiterSemicolumnToTab'); 
    }

    evtsig_changeDecimalSepFromPoint(event, elt, details){
        this.hideMenu(event);        
        this.undo.push( this.elt_file_src.value );
        this.elt_file_src.value = 
            this.pcmd_changeDecimalSepFromPoint( this.elt_file_src.value  );        
        // this.add_cmd_to_prog('changeDecimalSepFromPoint'); 
    }

    evtsig_changeColumnsOrder(event, elt, details) {
        this.hideMenu(event);        
        this.undo.push( this.elt_file_src.value );
        let order = this._getLSCookie('columnsOrder');  // '¶'
        this.elt_file_src.value = this.pcmd_changeColumnsOrder( this.elt_file_src.value, order );
        // this.add_cmd_to_prog('changeColumnsOrder', "'"+order+"'"); 
    }

    evtsig_reportCellIfNextIsEmpty(event, elt, details) {
        this.hideMenu(event); 
        this.undo.push( this.elt_file_src.value );
        let colNum = this._getLSCookie('reportCellIfNext');
        this.elt_file_src.value = this.pcmd_reportCellIfNextIsEmpty( this.elt_file_src.value, colNum );
        // this.add_cmd_to_prog('reportCellIfNextIsEmpty', ''+colNum );
    }

    evtsig_removeRowIfNoDate(event, elt, details) {
        this.hideMenu(event);
        this.undo.push( this.elt_file_src.value );
        let colNum = this._getLSCookie('removeRowIfNoDate');
        this.elt_file_src.value = this.pcmd_removeRowIfNoDate( this.elt_file_src.value, colNum );
        // this.add_cmd_to_prog('removeRowIfNoDate', colNum); 
    }

    evtsig_addRowAtTop(event, elt, details) {
        this.hideMenu(event);
        this.undo.push( this.elt_file_src.value );
        let row = this._getLSCookie('addRowAtTop');
        this.elt_file_src.value = this.pcmd_addRowAtTop( this.elt_file_src.value, colNum );
        // this.add_cmd_to_prog('addRowAtTop', row); 
    }





    run_cmd_with_params(cmd, intxt, params) {      // '1;2;6;8;10;19;15/\"([CD]) ([^\/]*)\/(.*)\"/;26;31')

        if ( !method_exists(this, 'pcmd_'+cmd) ) {
            app.error('error : cmd '+'pcmd_'+cmd+' not found');
            return;
        }

        params = params.split(',');

        for (let i=0; i<params.length; i++) {
            let p = params[i];
            let res = p.replace(/^'([^']*)'\$/, '\$1');
            params[i] = res;
        }

        return this['pcmd_'+cmd]( intxt, ...params );
    }


    add_cmd_to_prog(cmd, params){                     
        if (cmd.startsWith('evtsig_')) cmd = cmd.substring(7);
        if (this.elt_cmdlst_textarea.value) this.elt_cmdlst_textarea.value += "\\n";
        if (params)
            this.elt_cmdlst_textarea.value += cmd+'('+params+')';
        else
            this.elt_cmdlst_textarea.value += cmd+'()';
    }



    evtsig_btn_run_prog_filter() {       

        this.show_workingmsg( true );
        // DmcModal.show( "id_dlg_empty", 'Program is running...' ); 
        let needSave = false;

        this.undo.push( this.elt_file_src.value );
        let t0 = Date.now(); 
        let method  = document.getElementById( 'id_cmdlst_textarea' ).value;
        let lines = method.split('\\n');
        for (let row=0; row<lines.length; row++) {
            if (lines[row] == '{{Compta') {
                let progEtatPar = '';
                for (let r=row+1; r<lines.length; r++) {
                    if (lines[r] == '}}Compta') {
                        this.etatParametrableFilter(progEtatPar);
                        row=r;
                        break;
                    }
                    progEtatPar += lines[r] + '\\n';
                }
                continue;
            }
            // app.log('row '+row+' = '+lines[row]);
            let regexp = /([a-zA-Z_][a-zA-Z0-9_]*)\((.*)\)/;
            let m1 = lines[row].match(regexp);
            if (m1 && m1.length==3) {
                needSave = true;
                app.log( 't='+(Date.now() - t0)+'ms, start cmd='+m1[1] );
                this.run_cmd_with_params( m1[1], null, m1[2] );
                app.log( 't='+(Date.now() - t0)+'ms, end cmd='+m1[1] );
            }
        }

        if (needSave)
            this._setLSCookie('file_src.value', this.elt_file_src.value);
        // DmcModal.hide();
        this.show_workingmsg( false );
    }

    run_prog(method, intxt) {

        let outtxt = null;
        let needSave = false;
        let t0 = Date.now(); 
        let lines = method.split('\\n');
        for (let row=0; row<lines.length; row++) {
            if (lines[row] == '{{Compta') {
                let progEtatPar = '';
                for (let r=row+1; r<lines.length; r++) {
                    if (lines[r] == '}}Compta') {
                        this.etatParametrable(progEtatPar);
                        row=r;
                        break;
                    }
                    progEtatPar += lines[r] + '\\n';
                }
                continue;
            }
            // app.log('row '+row+' = '+lines[row]);
            let regexp = /([a-zA-Z_][a-zA-Z0-9_]*)\((.*)\)/;
            let m1 = lines[row].match(regexp);
            if (m1 && m1.length==3) {
                // needSave = true;
                app.log( 't='+(Date.now() - t0)+'ms, start cmd='+m1[1] );
                let res = this.run_cmd_with_params( m1[1], intxt, m1[2] );
                if (res) {
                    outtxt = res;
                    intxt = res;
                }
                app.log( 't='+(Date.now() - t0)+'ms, end cmd='+m1[1] );
            }
        }
        return outtxt;

    }

    evtsig_btn_run_prog() {       

        this.show_workingmsg( true );
        // DmcModal.show( "id_dlg_empty", 'Program is running...' ); 
        this.undo.push( this.elt_file_src.value );
        let method  = document.getElementById( 'id_cmdlst_textarea' ).value;

        /* let needSave = false;
        let lines = method.split('\\n');
        for (let row=0; row<lines.length; row++) {
            if (lines[row] == '{{Compta') {
                let progEtatPar = '';
                for (let r=row+1; r<lines.length; r++) {
                    if (lines[r] == '}}Compta') {
                        this.etatParametrable(progEtatPar);
                        row=r;
                        break;
                    }
                    progEtatPar += lines[r] + '\\n';
                }
                continue;
            }
            // app.log('row '+row+' = '+lines[row]);
            let regexp = /([a-zA-Z_][a-zA-Z0-9_]*)\((.*)\)/;
            let m1 = lines[row].match(regexp);
            if (m1 && m1.length==3) {
                needSave = true;
                app.log( 't='+(Date.now() - t0)+'ms, start cmd='+m1[1] );
                this.run_cmd_with_params( m1[1], null, m1[2] );
                app.log( 't='+(Date.now() - t0)+'ms, end cmd='+m1[1] );
            }
        }
        */

        let outtxt = this.run_prog( method, this.elt_file_src.value );
        if (outtxt != null)  {
            this.elt_file_src.value = outtxt;
            this._setLSCookie('file_src.value', this.elt_file_src.value);
        }
        // DmcModal.hide();
        this.show_workingmsg( false );
    }

    show_workingmsg( on ){
        let elt_working  = document.getElementById( 'id_mnu_working' );
        elt_working.style.display = on ? '' : 'none';
    }



    etatParametrable(method) {  // evtsig_etatparametrable

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



    etatParametrableFilter(method) {  // evtsig_etatparametrable

        let acc = new DmcAccounting();
        let dbEntries = acc.loadEntries( this.elt_file_src.value );
        let inner = acc.etatParametrableFilter(dbEntries, method );
        let elt_file_dest = document.getElementById("id_file_dest"); 
        let tbody = elt_file_dest.getElementsByTagName('tbody')[0]; 
        tbody.innerHTML = inner;
        let thead_th = elt_file_dest.querySelector('thead th');
        thead_th.innerText = 'Etat paramétrable';
        DmcModal.show( "id_dlg_calcTable", 'calcTable' ); 

    }


/*
    etatParametrable(method) {  

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
*/






    hideMenu(event) {
        DmcMenu.hideMenu(event.target);
    }

    // ========= slots ===========
    // ===========================
    // == file_src


    event_file_src_onpaste(event) {
        // let pasteTxt = (event.clipboardData || window.clipboardData).getData('text');
        // app.log('event_file_src_onpaste len=' + pasteTxt.length );        
    }

    // event_file_src_onmouseup   id_cmdlst_textarea  event_cmdlst_textarea_onmouseup
    event_cmdlst_textarea_onmouseup(event) {
        let elt_cmdlst_textarea = event.target;
        let h = elt_cmdlst_textarea.style.height;  if (h == '') h = elt_cmdlst_textarea.clientHeight+'px'; 
        let w = elt_cmdlst_textarea.style.width;   if (w == '') w = elt_cmdlst_textarea.clientWidth+'px'; 
        this._setLSCookie('elt_cmdlst_textarea.height', h); 
        this._setLSCookie('elt_cmdlst_textarea.width',  w); 
    }

    event_file_src_onmouseup(event) {
        let elt_file_src = event.target;
        let h = elt_file_src.style.height;   if (h == '') h = elt_file_src.clientHeight+'px'; 
        this._setLSCookie('elt_file_src.height', h); 
    }

    event_cmdlst_textarea_oninput( event ) {
        let elt_lst_textarea = event.target;
        this._setLSCookie('lst_textarea.value', elt_lst_textarea.value);        
    }

    event_file_src_oninput(event){
        let elt_file_src = event.target;
        this._setLSCookie('file_src.value', elt_file_src.value);
    }



    async slot_dropfile( ev, files ) {

        if (ev) {
            let wgt = ev.currentTarget.closest( DmcDropfile.get_root_elt_selector() );
            let modal = ev.currentTarget.closest( DmcModal.get_root_elt_selector() );
            if (modal && modal.id == 'id_dlg_bnks') {
                this.slot_dropfile_bnk_cagr( ev, files, modal );
                return;
            }
        }

        this.show_workingmsg( true );

        this.elt_file_src.value = '';

        for (let i=0; i<files.length; i++) {
            let file_obj = files[i];
            if (file_obj === undefined || file_obj === null) continue;
            app.log( 'Trying to load file ['+ file_obj.name+']');
            let upl_maxsize = 100;  // let's say 100 Mb
            if (file_obj.size > upl_maxsize*1024*1024) { 
                let fs = file_obj.size/1024/1024;
                app.error( 'Error file size = '+ fs.toFixed(0) + 'Mb > '+ upl_maxsize.toFixed(0) +'Mb is too big...' );
                this.show_workingmsg( false );
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

            if (i>0)  this.elt_file_src.value += '\\n'; 
            this.elt_file_src.value += txt; 
        }
        this.show_workingmsg( false );
    }


    msig_flexvert_resize( signal ) {         // a msignal

        // let h = this.elt_file_src.style.height;   
        let h = signal.target.style.height;   
        if (!h || h=='')  h = signal.value;
        this._setLSCookie('elt_file_src.height', h); 

    }


    msig_flexhori_resize( signal ) {  

        // let elt = document.getElementById('id_prog_list');  //  the dmc_flexhori_child_fix div is resized
        let elt = signal.target;
        let w = elt.style.width;   
        if (!w || w=='')  w = signal.value;
        this._setLSCookie('elt_prog_list.width', w); 

    }


    msig_lsbx_processes_dblclick( signal ) {         // a msignal 
        this.do_lsbx_processes_editLine( signal.value );
    }



    do_lsbx_processes_editLine(progName) {
        app.log('do_lsbx_processes_editLine');
        let fieldset = document.getElementById( 'id_lsbx_processes' );    
        let listdata = WgtListbox.get_wgt_listdata( fieldset );
        let txt = '';
        if (progName in listdata) 
            txt = listdata[progName];
        let elt_title = document.getElementById( 'id_cmdlst_title' );
        let elt_text  = document.getElementById( 'id_cmdlst_textarea' );
        elt_title.value = progName;
        elt_text.value = txt.textarea;
    }



    msig_lsbx_processes_cntxtmnu( signal ) {      // a msignal 
        app.log( 'msig_lsbx_processes_cntxtmnu = ' + signal.value );
        DmcContextmenu.showContext( signal, { line: signal.elt } );
        event.preventDefault();
        return false;
    }


    slot_event_login( event, elt, details ) {  
        let icon_user =  app.icon('icons/svg', 'user.svg');
        let link = document.getElementById("id_a_login");
        if (details.check)
            link.innerHTML = icon_user +   escapeHtml( details.user );
        else
            link.innerHTML = icon_user +   escapeHtml( 'login' );
    }


    slot_event_logout( event, elt, details ) {  
        let link = document.getElementById("id_a_login");
        link.innerHTML = app.icon('icons/svg', 'user.svg') + 'Login';
    }


    slot_progress(event, elt, details ) {          // a Application.signal
        app.log('Application::slot_progress: txt='+details.txt);
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
                '<li class="ul_ctxmenu_title"><p>'+ escapeHtml(details.value) +'</p></li>' +
                '<li><a signal="click››evtsig_ctx_run">run</a></li>' +
                '<li><a signal="click››evtsig_ctx_deleteLine">delete</a></li>' +
                '<li><a signal="click››evtsig_ctx_editLine">edit</a></li>' +
               '</ul>';

        details.sender.fillHTML( html );
        // details.sender.fill( details.value );
        app.log( 'slot_fill_contextmenu = ' + details.value );
        return true;
    }


    evtsig_ctx_editLine(event) {
        let mnu_details = DmcContextmenu.getDetails();
        let progName = mnu_details.line.innerText;
        this.do_lsbx_processes_editLine(progName);
        DmcContextmenu.hide();
    }


    evtsig_ctx_run(event, elt, details) {   

        let elt_list_fieldset = document.getElementById( 'id_lsbx_processes' );  
        let mnu_details = DmcContextmenu.getDetails();
        let progName = mnu_details.line.innerText;

        let listdata = WgtListbox.get_wgt_listdata( elt_list_fieldset );
        let program_src = '';
        if (progName in listdata) {  program_src = listdata[progName].textarea;  } 
        DmcContextmenu.hide();

        let acc = new DmcAccounting();
        let dbEntries = acc.loadEntries( this.elt_file_src.value );
        if (!dbEntries) {
            app.log('Error loadEntries');
            return;
        }
        let inner = acc.etatParametrable(dbEntries, program_src );

        let elt_file_dest = document.getElementById("id_file_dest"); 
        let tbody = elt_file_dest.getElementsByTagName('tbody')[0]; 
        tbody.innerHTML = inner;
        let thead_th = elt_file_dest.querySelector('thead th');
        thead_th.innerText = 'Etat paramétrable';
        DmcModal.show( "id_dlg_calcTable", 'calcTable' ); 
    }




    async evtsig_ctx_deleteLine(event, elt, details) {
        let elt_list = document.getElementById( 'id_lsbx_processes' );
        let mnu_details = DmcContextmenu.getDetails();
        let line = mnu_details.line.innerText;

        let v = await DmcModal.showAsync( "id_dlg_yes_no", 'Delete "'+line+'" : are you sure ?' );
        if (v.result != 'SUBMIT') return;

        WgtListbox.deleteLine (elt_list, line);
        DmcContextmenu.hide();
    }



/*
    // ================ cmds ======================
    // ============================================
*/


    async evtsig_test1() {
        DmcModal.show( "id_dlg_test1", 'Using DmcModal.show with a <div>, not a <dialog>' );
    }


    async evtsig_test3_form() {

        DmcModal.resetForms( "id_dlg_test3" );
        let v = await DmcModal.showAsync( "id_dlg_test3", 'This is a form test' );
        if (v && v.data) {
            app.log('evtsig_test3 with result='+v.result);
            app.log('  data='+ JSON.stringify(v.data) );
        } else {
            app.log('evtsig_test3 v is null');            
        }
    }

// ===================================
// ===================================



    async evtsig_bnk_convert() {
        this.reset_dlg_bnks();   
        let v = await DmcModal.showAsync( "id_dlg_bnks", '' );
        if (v && v.data) {
            app.log('evtsig_bnk_convert with result='+v.result);
            app.log('  data='+ JSON.stringify(v.data) );
        } else {
            app.log('evtsig_bnk_convert v is null');            
        }

    }

    evtsig_dlg_bnk_end (event, elt, details) {
        this.reset_dlg_bnks();   
    }

    evtsig_dlg_bnk_copy (event, elt, details) {
        let modal = document.getElementById('id_dlg_bnks'); 
        let elt_textarea = modal.querySelector('#'+'id_dlg_bnk_src');  
        elt_textarea.focus();
        elt_textarea.select();
        document.execCommand('copy');
        elt_textarea.setSelectionRange(0, 0);
    }

    async evtsig_bnk_cnvrt_prg (event, elt, details) {
        let modal = document.getElementById('id_dlg_bnks'); 
        let elt_textarea = modal.querySelector('#'+'id_dlg_bnk_src');  
        let elt_btns =     modal.querySelector('#'+'id_dlg_bnk_btns');  
        let elt_dwnld =    modal.querySelector('#'+'id_dlg_bnk_dwnld');  

        let method = '';
        switch (elt.innerText) {
            case 'Crédit Agricole':  
                method =    "decodeFromISO_8859_15()\\n"+
                            "changeCRinQuotes(' ')\\n"+
                            "removeExtraSpaces()\\n"+
                            "removeExtraTabs()\\n"+
                            "removeNoBreakSpaces()\\n"+
                            "removeMoneySign()\\n"+
                            "removeRowIfNoDate(1)\\n"+
                            "changeColumnsOrder('1;2;5;3;4')\\n";
                break;
            case 'LCL':  
                method =    "changeColumnsOrder('1;2;3>')\\n"+
                            "removeExtraSpaces()\\n";
                break;
            case 'Banque postale':  
                method =    "changeColumnTabToSemicolon()\\n"+
                            "removeRowIfNoDate(1)\\n"+
                            "changeColumnsOrder('1;2;if+3;if-3')\\n";
                break;
        }

        let outtxt = this.run_prog( method, elt_textarea.value );
        if (!outtxt)  {
            app.log('evtsig_bnk_cnvrt_prg error during prog');
            return;
        }

        elt_textarea.value = outtxt;
        elt_btns.style.display = 'none';
        elt_dwnld.style.display = 'flex';

        let json =  await this.post_cmd( 'post_prepare_download', outtxt );
        if (json.msg != "") app.popup ( 'msg = \\n' + json.msg + '\\n' + '' );
        
    }

    reset_dlg_bnks(){
        let modal = document.getElementById('id_dlg_bnks'); 
        let elt_p =        modal.querySelector('#'+'id_dlg_bnk_p');  
        let elt_textarea = modal.querySelector('#'+'id_dlg_bnk_src');  
        let elt_btns =     modal.querySelector('#'+'id_dlg_bnk_btns');  
        let elt_dwnld =    modal.querySelector('#'+'id_dlg_bnk_dwnld');  
        let elt_dropfile = modal.querySelector( DmcDropfile.get_root_elt_selector() );

        elt_p.innerText = "Conversion d'un fichier venant d'une banque";
        elt_textarea.style.display = 'none';
        elt_dropfile.style.display = 'block';
        elt_btns.style.display = 'none';
        elt_dwnld.style.display = 'none';
    }


    async slot_dropfile_bnk_cagr( ev, files, modal ) {

        if (files.length != 1) {
            app.error('Please drop only one file');
            return;
        }

        let elt_textarea = modal.querySelector('#'+'id_dlg_bnk_src');  
        let elt_dropfile = modal.querySelector( DmcDropfile.get_root_elt_selector() );
        let elt_btns =     modal.querySelector('#'+'id_dlg_bnk_btns');  

        let txt = await DmcDropfile.convfile(files[0]);
        elt_textarea.value = txt;
        elt_textarea.style.display = 'block';
        elt_dropfile.style.display = 'none';
        elt_btns.style.display = 'block';

        // app.log('slot_dropfile_bnk_cagr');
    }

// ===================================
// ===================================


    async evtsig_test5_post_test() {
        let json =  await this.post_cmd( 'post_test', 'testCookie', '42' );
        app.popup ( 'msg = \\n' + json.msg + '\\n' + '' );
    }



    evtsig_test_worker() {

        //  if (this.worker1) {    this.worker1.terminate();    this.worker1 = null;    }

        if (!this.worker1) {
            this.worker1 = new Worker('src/worker1.js');
            this.worker1.onmessage = function(e) {
              app.log('worker1.js says : '+e.data);
            }
        }


        // this.worker1.addEventListener("message",  
        //     function(msg) {   app.log('The worker says : '+msg.data);  }  );


        this.worker1.postMessage([15, 16]);
    }


    evtsig_wgttable() {
        let elt_wgttable = document.getElementById("id_wgt_table1");
        WgtTable.resetData( elt_wgttable );
        WgtTable.set_th ( elt_wgttable );
        let txt = this.elt_file_src.value; 
        WgtTable.importCsv ( elt_wgttable, txt );
        WgtTable.connect_events_wdgt (elt_wgttable);  

        DmcModal.show( "id_dlg_WgtTable", 'evtsig_wgttable' ); 
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
<ul>
<li>&copy; 2023-01  JF Lemay (FR) (contact via github Jeff42820)
    <ul>
    <li><a href="https://github.com/Jeff42820" class="help_link" target="_blank">
        <span class="icon_inline"><img class="icon_inline_scale1_75p" src="icons/github.svg" /> &nbsp; &nbsp;  &nbsp;  &nbsp; </span>
        github.com/Jeff42820</a>
    </li>
    <!--
    <li><a href="https://jflemay.synology.me/doc" class="help_link" target="_blank">
        <span class="icon_inline"><img class="icon_inline_scale1_75p" src="icons/server.svg" /> &nbsp; &nbsp;  &nbsp;  &nbsp; </span>
        jflemay.synology.me/doc</a>
    </li>  -->
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
    <li><a href="https://stackoverflow.com/" class="help_link" target="_blank">
        <span class="icon_inline"><img class="icon_inline_scale_75p" src="icons/stackoverflow.svg" /> &nbsp; &nbsp;  &nbsp;  &nbsp; </span>
        stackoverflow.com</a></li>
    <li><a href="https://github.com" class="help_link" target="_blank">
        <span class="icon_inline"><img class="icon_inline_scale1_75p" src="icons/github.svg" /> &nbsp; &nbsp;  &nbsp;  &nbsp; </span>
        github.com</a></li> 
    <li><a href="https://framework7.io/icons/" class="help_link" target="_blank">
        <span class="icon_inline"><img class="icon_inline_scale1_75p" src="icons/framework7.svg" /> &nbsp; &nbsp;  &nbsp;  &nbsp; </span>
        framework7.io/icons/</a></li> 
    </ul>
</li>
</ul>


        `, 0);
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
        acc.sort( dbEntries );
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
        let thead_tr_colnums = document.getElementById('id_colnums')

        let inner='', maxcols = 0;
        let lines = this.elt_file_src.value.split('\\n');
        for (let row=0; row<lines.length; row++) {
            let cols = columnSplit( lines[row] );
            if (cols.length > maxcols) maxcols = cols.length;
        }
        for (let col=0; col<maxcols; col++) {
            inner += '<td>'+(col+1)+'</td>';
        }
        // thead_tr_colnums.innerHTML = '<tr>'+inner+'</tr>';

        for (let row=0; row<lines.length; row++) {
            let line = '<tr>';
            let cols = columnSplit( lines[row] );
            for (let col=0; col<cols.length; col++) {
                let style='';
                if (isNumeric(cols[col]))
                    style='style="text-align: right;"';
                if (isDate_slash(cols[col]))
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
            app.popupHtml(  escapeHtml('reset_log_php success ') +  
                            app.icon('f7-icons', 'hand_thumbsup_fill'), 2);
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

    evtsig_logout () {
        WgtLogin._this.evtsig_logout();
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
        // app.log ( 'id_dlg_yes_no result = '+ v.result);
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

    async evtsig_prepare_download() {
        this.hideMenu(event);
        let json =  await this.post_cmd( 'post_prepare_download', this.elt_file_src.value );
        if (json.msg != "") app.popup ( 'msg = \\n' + json.msg + '\\n' + '' );
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
            <thead>
            <tr><th scope="col" colspan="100%">-----</th></tr>
            <tr id="id_colnums"></tr>
            </thead>
            <tbody>
            </tbody>
        </table>
      </div>
    </dialog>


    <dialog id="id_dlg_WgtTable" class="dmc_modal fade-in">
      <div id="id_dlg_content" class="dm_modal_content">
        <span class="btn_modal_close" data-modal="btn_close">&times;</span>
        <table id="id_wgt_table1"  mwgtclass="WgtTable"  class="wgt_table" style="">
        <thead></thead>
        <tbody></tbody>
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

        <ul><li><a>Remove extra : &vrtri;</a>
            <div>
            <a signal="click››evtsig_removeExtraSpaces"
                    statusmsg="many spaces are changed in only one space (0x20)">remove extra spaces</a>
            <a signal="click››evtsig_removeExtraTabs"
                    statusmsg="many tabs are changed in only one tab (\\t) (0x09) char">remove extra tabs</a>
            <a signal="click››evtsig_removeNoBreakSpaces"
                    statusmsg="char 'no-break space' (&amp;nbsp;) (0xa0) are removed">remove no-break spaces</a>
            <a signal="click››evtsig_removeQuotes"
                    statusmsg="Quotes [&quot;] are removed, semicolons [;] &amp; quotes [&quot;] in quotes are changed into spec chars">remove quotes</a>
            <a signal="click››evtsig_removeMoneySign"
                    statusmsg="char '€£\$' are removed">remove money signs € £ \$</a>
            <a signal="click››evtsig_dateToComptaDate"
                    statusmsg="transform 25/12/2022 to 2022-12-25">date to comptaDate</a>
            </div>
        </li></ul>


        <ul><li><a>Change column delimiter : &vrtri;</a>
            <div>
            <a signal="click››evtsig_changeColumnToSemicolonFromComma"
                statusmsg="change column delimiter [,] =&gt; [;] (but not inside &quot;strings&quot;)">change column delimiter [,] =&gt; [;]</a>
            <a signal="click››evtsig_changeColumnTabToSemicolon"
                statusmsg="change column delimiter [tab] =&gt; [;] (but not inside &quot;strings&quot;)">change column delimiter [tab] =&gt; [;]</a>
            <a signal="click››evtsig_changeDelimiterSemicolumnToTab"
                statusmsg="change column delimiter [;] =&gt; [tab] (but not inside &quot;strings&quot;)">change column delimiter [;] =&gt; [tab]</a>
            </div>
        </li></ul>


        <form LSCookie="y"><table>
            <tr><td><label signal="click››evtsig_changeCRinQuotes" style="cursor:pointer;"
                statusmsg="inside &quot;strings&quot; change char (LF) (\\n) (0x0a) to something else">change in quotes '\\n' to :</label></td>
                <td><input type="text" LSCookie="CRinQuotesChar" style="width:2em;" /></td></tr>
        </table></form>
        <a signal="click››evtsig_changeDecimalSepFromPoint"
                statusmsg="from 123.50 to 123,50 [.] =&gt; [,] (no change in quotes)">change decimal separator [.] =&gt; [,]</a>
        <form LSCookie="y"><table>
            <tr><td><label signal="click››evtsig_changeColumnsOrder" style="cursor:pointer;"
                statusmsg="example of syntax :    1;2;if+3;if-3;4>5;7&gt;if|3=Some|+4  or  1;15&sol;(D )(.*)&sol;">change columns order</label></td>
                <td><input type="text" LSCookie="columnsOrder" style="width:8em;" /></td></tr>
        </table></form>
        <form LSCookie="y"><table>
            <tr><td><label signal="click››evtsig_reportCellIfNextIsEmpty" style="cursor:pointer;"
                statusmsg="For column number : (columnNum)">report cell if next is empty</label></td>
                <td><input type="text" LSCookie="reportCellIfNext" style="width:3em;" /></td></tr>
        </table></form>
        <form LSCookie="y"><table>
            <tr><td><label signal="click››evtsig_removeRowIfNoDate" style="cursor:pointer;"
                statusmsg="For column number : (columnNum)">remove line if not date in column </label></td>
                <td><input type="text" LSCookie="removeRowIfNoDate" style="width:3em;" /></td></tr>
        </table></form>
        <form LSCookie="y"><table>
            <tr><td><label signal="click››evtsig_addRowAtTop" style="cursor:pointer;"
                statusmsg="Add a row (on top position)">add this at the top of the text </label></td>
                <td><input type="text" LSCookie="addRowAtTop" style="width:8em;" /></td></tr>
        </table></form>

        </div>
    </li>



    <li id="id_mnu_tools"><a signal="click››evtsig_bnk_convert">Banques</a>
    </li>



    <li id="id_mnu_tools"><a>Tools</a>
        <div class="div_menu">
        <a onclick="DmcDropfile.ev_choosefile(event)">open file
            <form class="form_dropfile_select">
            <input type="file" id="DmcDropfile_selectfile" style="display:none;"/>
            </form>
        </a>

        <a signal="click››evtsig_prepare_download">prepare download</a>
        <a href="?do=download" target="_blank" rel="noopener noreferrer">download</a>
        <a signal="click››evtsig_undo">undo</a>
        <a signal="click››evtsig_copy">copy</a>
        <a signal="click››evtsig_empty">empty</a>
      <!--  <a signal="click››evtsig_parameters">parameters</a>  -->
        <ul>
            <li><a>tests &vrtri;</a>
                <div>
                <a signal="click››evtsig_test1">test1 : DmcModal.show with a div</a>
                <a signal="click››evtsig_test3_form">test3 : DmcModal.showAsync with form</a>
                <a signal="click››evtsig_test5_post_test">test5 : post_test</a>
                <a signal="click››evtsig_test_worker">test worker</a>
                </div>
            </li>
        </ul>
        <a signal="click››evtsig_log_php">show log_php</a>
        <a signal="click››evtsig_reset_log_php">clear log_php</a>
        <a signal="click››evtsig_reset_cookies">reset cookies</a>
        </div>
    </li>

    <li id="id_mnu_calcs"><a  signal="click››evtsig_calcTable">Table</a>
        <div class="div_menu">
        <a signal="click››evtsig_calcTable">Show table</a>
        <a signal="click››evtsig_wgttable">Show table with search possibilities</a>
        </div>
    </li>

    <li id="id_mnu_calcs"><a>Compta</a>
        <div class="div_menu">
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

    <li id="id_mnu_calcs"><a>?</a>
        <div class="div_menu">
        <a signal="click››evtsig_about">About</a>
        <a signal="click››evtsig_prog_help">Aide compta</a>
        </div>
    </li>

    <li id="id_mnu_working" style="display: none;"><a>Working....</a>
    </li>

</ul>


<ul class="dmc_menu rounded_div">
    <li id="id_mnu_login"><i id="id_php_connected" class="f7-icons" style="font-size:14px">link</i>
        &nbsp; <a id="id_a_login" onclick="WgtLogin.showDialog(this);">login</a>
        <div class="div_menu">
        <a signal="click››evtsig_logout">Logout</a>
        </div>
    </li>
</ul>

EOLONGTEXT;     









function main() {
    global $CR, $m; 
    $dm = CModules::$_this;
    $m = new CPrjMain();
    $m->init_session();

    if ( isset($_GET['do']) &&  $_GET['do']=='download' )  {
        header("Content-type: text/csv");
        header("Content-Disposition: attachment; filename=file.csv");
        header("Pragma: no-cache");
        header("Expires: 0");

        $filepath = 'rw/' . $m->session_hash . '.csv';
        try {
            $txt = file_get_contents($filepath);
        } catch (Exception $e){
            // $data['error'] = 'CMainPhp::post_prepare_download error : Exception '.$e->getMessage();
        }
        echo $txt;
        return;
    }

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
        <dialog id="id_dlg_bnks" class="dmc_modal fade-in" style="display:none;position:fixed;">
          <div id="id_dlg_bnk_content" class="dm_modal_content">
            <span class="btn_modal_close" data-modal="btn_close">&times;</span>
            <p id="id_dlg_bnk_p"></p>
            {{mod_html_div_dropfile}}
            <textarea id="id_dlg_bnk_src" class="std_textarea"
                style="width:100%; height:10em;" >    
            </textarea>
            <div id="id_dlg_bnk_btns" class="dlg_bnk_btns">
                <button type="button"   signal="click››evtsig_bnk_cnvrt_prg" 
                 statusmsg="Fichier csv du crédit agricole">Crédit Agricole</button>
                <button type="button"   signal="click››evtsig_bnk_cnvrt_prg" 
                 statusmsg="Fichier csv  LCL">LCL</button>
                <button type="button"   signal="click››evtsig_bnk_cnvrt_prg" 
                 statusmsg="Fichier tsv la Banque Postale">Banque postale</button>
            </div>
            <div id="id_dlg_bnk_dwnld" class="dlg_bnk_dwnld" style="display:flex; justify-content:space-around;">
                <a href="?do=download" target="_blank" rel="noopener noreferrer">Télécharger</a>
                <a signal="click››evtsig_dlg_bnk_copy">Copier dans le presse-papier</a>
                <a signal="click››evtsig_dlg_bnk_end">Terminer</a>
            </div>
          </div>
        </dialog>
        EOLONGTEXT, [ 
            'mod_html_div_dropfile' => $dm->mod_get_elt( 'div-dropfile.mod.php', 'mod_html_div_dropfile' ) 

        ] );


        echo $dm->sml_mustache( <<<EOLONGTEXT

        <div class="dmc_flexvert" style="flex:1;">

        <textarea id="id_file_src" class="dmc_flexvert_child_fix std_textarea rounded_div div_dropfile" 
            onpaste="app.event_file_src_onpaste(event)" 
            oninput="app.event_file_src_oninput(event)" 
            onmouseup="app.event_file_src_onmouseup(event)"
            msignal="resize_mouseup››msig_flexvert_resize">    
        </textarea>

        <div id="id_flexvert_for_file_src" class="dmc_flexvert_child_separator" > 
        </div>

        <div class="dmc_flexvert_child_expand" > 




        <div class="dmc_flexhori">

            <div id="id_prog_list" class="dmc_flexhori_child_fix" style="padding:0.5em;" 
            msignal="resize_mouseup››msig_flexhori_resize">
                <p><span style="display:inline-block; border-radius:1em;" class="appli_std_color"> &nbsp; <i class="f7-icons" >tray_fill</i> &nbsp;</span> Liste des programmes</p>
                <fieldset id="id_lsbx_processes"  mwgtclass="WgtListbox" 
                    msignal="item_dblclick››msig_lsbx_processes_dblclick,item_contextmenu››msig_lsbx_processes_cntxtmnu"  
                    data-LSCookie="lsbx_processes_content"
                    class="wgt_listbox wgt_listbox_oneonly">
                    <!-- legend > style="max-height:5em;"  </legend --> 
                </fieldset>
                <p>(double-click pour le charger)</p>
                <p>(click-droite pour le menu ctx)</p>
            </div>

            <div class="dmc_flexhori_child_separator" > 
            </div>

            <div class="dmc_flexhori_child_expand" style="padding:0.5em;">
                <p>Programme</p>
                <p>Nom: <input id="id_cmdlst_title" type="text" class="program_edit" name="title" 
                    style=" margin:0 auto; " value=""/>
                <button type="button" class="program_edit dmcss_tooltip"
                 signal="click››evtsig_prog_save" 
                 statusmsg="Save this program."
                 data-tooltip="Save it into the programs list">
                 <i class="f7-icons" >tray_arrow_down_fill</i>
                 </button>
                <button type="button" class="program_edit dmcss_tooltip left_bottom" signal="click››evtsig_btn_run_prog"
                 statusmsg="Run the program and show the result (no filter possibilities)"
                 data-tooltip="Run the programme (no filter possibilities)">
                 <i class="f7-icons" >play_rectangle_fill</i>
                 </button>
                <button type="button" class="program_edit dmcss_tooltip right_bottom" signal="click››evtsig_btn_run_prog_filter"
                    statusmsg="Run the program, keyword filter is possible,  (beta test)"
                    data-tooltip="Run the program, keyword filter is possible,  (beta test)"">
                 <i class="f7-icons" >play_rectangle_fill</i>
                 </button>
                </p>
                <textarea id="id_cmdlst_textarea" class="program_edit"  
                    onmouseup="app.event_cmdlst_textarea_onmouseup(event)"
                    oninput="app.event_cmdlst_textarea_oninput(event)">
                </textarea>
                <p></p>
            </div>


        </div>


          <div class="dmc_respcardcontainer">
          </div>

        </div>
        </div>

        EOLONGTEXT, [ 
         //   'mod_html_div_dropfile' => $dm->mod_get_elt( 'div-dropfile.mod.php', 'mod_html_div_dropfile' ) 

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