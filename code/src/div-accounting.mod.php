<?php
        // -------------------
        // div-accounting.mod.php
        // -------------------


CModules::include_begin( __FILE__ , 'This is division module  accounting' );



CModules::append( 'mod_css_dmc_accounting', <<<EOLONGTEXT


EOLONGTEXT ); // mod_css_dmc_accounting     



CModules::append( 'mod_js_class_Accounting', <<<EOLONGTEXT

class DmcAccounting  {   // extends DmcBase

    constructor() {
        this.colNames = null;
        this.colTypes = null;
        // super();
    }


    getColumnNames( line ) {
        // let regexp2 = /(NumEcr|DatEcr|Journal|Compte|NumDoc|Libelle|Piece|Debit|Credit|Poste|DatSai|;)+/g;
        let regexp = /(([[a-zA-Z][a-zA-Z0-9]*|"[[a-zA-Z][a-zA-Z0-9]*");?)*/;
        line = line.trim();
        let m1 = line.match(regexp);
        if (m1 !== null && m1.length >= 1 && m1[0] == line) {
            return trimQuoteArray( line.split(';') );
        }
        return null;
    }

    chooseType( colName, ob ) {

        switch ( colName ) {
            case 'Compte':  return 'string';
            case 'DatEcr':  return 'date';
            case 'DatSai':  return 'date';
            case 'Libelle': return 'string';
            default: 
                if ( ob['string']  > 0 ) return 'string';
                if ( ob['numeric'] > 0 && ob['date'] == 0     ) return 'numeric';
                if ( ob['date']    > 0 && ob['numeric'] == 0  ) return 'date';
                if ( ob['date']   == 0 && ob['numeric'] == 0 && ob['empty'] > 0 ) return 'empty';
                break;
        }
        return 'unknown';

    }

    formatEntry( e, datefmts=undefined ) {
        let newEntry = [].concat(e);        // Object.assign({}, e);
        for (let c=0; c<this.colNames.length; c++) {
            let v = e[c];
            let t = this.chooseType( this.colNames[c], this.colTypes[c] );
            switch ( t ) {
                case 'string':  
                    newEntry[c] = trimQuote(v);                          break;
                case 'numeric':
                    newEntry[c] =  (v!='') ? parseFloat( v.replace(',', '.') ) : 0;    break;
                case 'date':
                    if (datefmts)
                        newEntry[c] =  formatDate_YMD( v, datefmts );
                    else  
                        newEntry[c] =  formatDate_YMD( v );
                    break;
                case 'empty': 
                    newEntry[c] =  '';                                   break;
                default: 
                    newEntry[c] =  '';                                   break;
            }
        }
        return newEntry;
    }

    recognizeType( str, datefmt = undefined ) {
        if (str == '')                  return 'empty';
        if (isDate_slash( str ))  {
            if (datefmt){
                const reg = /^(\d{1,2})\/(\d{1,2})\/(\d{1,4})$/; //   /^\d{1,2}\/\d{1,2}\/\d{1,4}$/;
                const f = str.match( reg );
                if ( f ) {
                    let f1 = parseInt( f[1] );
                    let f2 = parseInt( f[2] );
                    let f3 = parseInt( f[3] );
                    datefmt.f1.min = Math.min(datefmt.f1.min, f1);
                    datefmt.f1.max = Math.max(datefmt.f1.max, f1);
                    datefmt.f2.min = Math.min(datefmt.f2.min, f2);
                    datefmt.f2.max = Math.max(datefmt.f2.max, f2);
                    datefmt.f3.min = Math.min(datefmt.f3.min, f3);
                    datefmt.f3.max = Math.max(datefmt.f3.max, f3);
                }
            }
            return 'date';
        }
        if (isNumeric( str ))           return 'numeric';
        return                          'string';            
    }

    loadEntries( txt ) {
        let lines = txt.split('\\n');
        this.colNames = this.getColumnNames( lines[0] );
        if (this.colNames == null) {
            app.log('DmcAccounting::loadEntries needs colNames specifications, none found, so exit');
            return;
        }
        this.colTypes  = Array( this.colNames.length );
        this.colTypes_ = Array( this.colNames.length );
        for (let c=0; c<this.colTypes.length; c++){    this.colTypes[c] = {  empty:0, date:0, numeric:0, string:0 };  }

        let  datefmt = {
            f1 : { min: 9999999,  max: -9999999 },
            f2 : { min: 9999999,  max: -9999999 },
            f3 : { min: 9999999,  max: -9999999 }
        }
        for (let n=1; n<lines.length; n++) {
            if ( lines[n] == '' ) continue;
            if ( lines[n].startsWith('//') ) continue;
            let entry = columnSplit( lines[n] );
            let maxlen = this.colNames.length;
            if (entry.length<maxlen) maxlen = entry.length;
            for (let c=0; c<maxlen; c++){
                let ival = (c<entry.length) ? entry[c] : '';
                let itype = this.recognizeType(ival, datefmt);
                this.colTypes[c][itype]++;
            }
        }

        let datefmts = '';
        if ( datefmt.f1.max >  12 && datefmt.f1.max <= 31 ) {
            datefmts += 'dd/';
            datefmts += 'mm/';
        } else if ( datefmt.f2.max >  12 && datefmt.f2.max <= 31 ) {
            datefmts += 'mm/';
            datefmts += 'dd/';
        }
        if ( datefmt.f3.max >= 0 && datefmt.f3.max <= 99 ) {
            datefmts += 'yy';
        } else if ( datefmt.f3.max >= 2000 && datefmt.f3.max <= 2200 ) {
            datefmts += 'yyyy';
        }

        let s = '';
        for (let c=0; c<this.colNames.length; c++){
            this.colTypes_[c] = this.chooseType( this.colNames[c], this.colTypes[c] );
            s += this.colNames[c] + ' type=' + this.chooseType( this.colNames[c], this.colTypes[c] ) + '\\n';
        }

        let dbEntries = [];
        for (let n=1; n<lines.length; n++) {
            if ( lines[n] == '' ) continue;
            let entry = columnSplit( lines[n] );
            dbEntries.push( this.formatEntry( entry, datefmts ) );
        }

        return dbEntries;

    } // loadEntries


    _tdFmt( s, colType, showZero = false ) {
        let style='';
        switch (colType){
            case 'date':    style='style="text-align: center;"';    break;
            case 'numeric': 
                if ( Math.abs(s) < 0.00001 && !showZero )  s='';
                else {
                    s = s.toLocaleString('fr-FR');
                    let i = s.indexOf(',');
                    if (i==-1)  s +=',00';
                    else {
                        if (s.slice(i).length == 2) s += '0';
                    }
                }
                if (s == '-0,00')  s = '0,00';
                style='style="text-align: right;"';     
                break;
        }
        let td = '<td '+style+'>' + s + '</td>';
        return td;        
    }

    tdFmt( entry, colName ) {
        let colNum = this.colNames.indexOf(colName);
        if (colNum == -1) return '<td>err</td>';
        let colType = this.colTypes_[colNum];
        return this._tdFmt( entry[colNum], colType );
    } // tdFmt


    journalRow( entry, arr, solde = null, forceFmt = null ) {
        let row = '';
        arr.forEach(colName =>  row += this.tdFmt(entry, colName) );
        if (solde !== null) {
            row += this._tdFmt(solde, 'numeric', true);
        }
        return row;
    }

    createRow( entry, solde = null, forceFmt = null ) {
        let row = '';
        for (let i=0; i<entry.length; i++) {
            row += this._tdFmt(entry[i], forceFmt ? forceFmt[i] : 'string');
        }
        if (solde !== null) {
            row += this._tdFmt(solde, 'numeric', true);
        }
        return row;
    }

    getColIndexes() {
        return  {  
            NumEcr  : this.colNames.indexOf('NumEcr'),
            DatEcr  : this.colNames.indexOf('DatEcr'),
            Journal : this.colNames.indexOf('Journal'),
            Compte  : this.colNames.indexOf('Compte'),
            Libelle : this.colNames.indexOf('Libelle'),
            Credit  : this.colNames.indexOf('Credit'),
            Debit   : this.colNames.indexOf('Debit')
        };
    }

    getColIndexesTotal() {
        let res = {    };
        for (let i=0; i<this.colNames.length; i++) {
            res[this.colNames[i]] = i;
        }
        return res;
    }

    sort( dbEntries, colName = undefined ) {
        let col = this.getColIndexes();
        if (colName === undefined)
            colName = 'DatEcr';
        let colNum = col[colName];

        function compareFn(a, b) {
            if ( a[colNum] < b[colNum] ) return -1;
            if ( a[colNum] > b[colNum] ) return  1;
            return 0;
        }

        dbEntries.sort( compareFn );
    }


    journal( dbEntries, journalName = null, CompteName = null ) {

        let innerHtml='';
        let col = this.getColIndexes();
        let solde = 0.0, numEcr=1;
        let fmt = [ 'DatEcr', 'Journal', 'Compte', 'Libelle', 'Debit', 'Credit' ];
        let supcompte = ( CompteName && CompteName.endsWith('>') ) ? substrbefore(CompteName, '>') : false;

        innerHtml +=  '<tr>' + this.createRow( fmt.concat(['Solde']) ) + '</tr>\\n';

        for (let row=0; row<dbEntries.length; row++) {
            let entry = dbEntries[row];
            let style = '';
            if (journalName && entry[col.Journal]!=journalName) continue;
            if (supcompte   && !entry[col.Compte].startsWith(supcompte))  continue;
            if (CompteName && !supcompte  && entry[col.Compte] !=CompteName)  continue;
            let x = entry[col.Debit] - entry[col.Credit];
            solde += x;
            let modulo = numEcr % 2;
            if (numEcr % 2 == 0)
                style = ' style="background-color:#00000015;" ';
            else
                style = ' style="background-color:#00000000;" ';
            innerHtml +=  '<tr'+style+'>' + this.journalRow( entry, fmt, solde ) + '</tr>\\n';
            if (Math.abs(solde) < 0.00001)  numEcr ++;
        }
        
        let entry    = [ 'Total',  '',        '',       '',        '',       ''  ];
        let forceFmt = [ 'string', 'string',  'string', 'string',  'string', 'string'  ];

        innerHtml +=  '<tr>' + this.createRow( entry, solde, forceFmt ) + '</tr>\\n';
        
        return innerHtml;
    } // journal


    isCompteBilan(compte) {
        let root = compte[0];
        if ( ['1', '2', '3', '4', '5'].includes(root) ) return true;
        if (compte.length >= 2) {
            root = compte.slice(0,2);
            if ( ['89'].includes(root) ) return true;
        }
        return false;
    }

    isCompteResultat(compte) {
        let root = compte[0];
        if ( ['6', '7'].includes(root) ) return true;
        if (compte.length >= 2) {
            root = compte.slice(0,2);
            if ( ['88'].includes(root) ) return true;
        }
        return false;        
    }




/*    =======================================================
        Immobilisations incorporelles 010| 20>DC + 280>DC
        Immobilisations corporelles 010|   21>DC + 281>DC
        Reste 28|                          !28>DC
        Reste  512|                        !512>DC
        Banque 512101|                     512101=DC
      =======================================================
*/
    etatParametrable( dbEntries, method ) {
        let _this = this;  // keep for sub function
        let col = this.getColIndexes();
        let methods = method.split('\\n');
        let soldeonly = false;

        function etatParametrableCmd( balance, searchCompte, op, dc ) {
            let r = {  tDebit: 0, tCredit: 0, sDebit: 0, sCredit: 0  };
            for (let compte in balance) {
                if ( ( op=='>' && compte.startsWith(searchCompte) )  || 
                     ( op=='=' && compte == searchCompte ) 
                    ) {
                        let x, c = balance[compte];
                        x = c.Debit - c.Credit;
                        if (  dc=='DC' ||
                             (dc=='D'  && x>0) ||
                             (dc=='C'  && x<0)    )  {
                                //if (arr[compte] == undefined)  arr[compte] = { tDebit:0, tCredit:0, sDebit:0, sCredit:0 };
                            if (x>0) r.sDebit  += x;
                            else     r.sCredit += (-x);
                            r.tDebit  += c.Debit;
                            r.tCredit += c.Credit;
                            c.Debit = c.Credit = 0;
                        } // if                        
                } // if
            };  // for
            return r;
        }

        function etatParametrable_1( balance, cmd ) {
            let regexp = /(!?)([0-9]+[0-9a-zA-Z]*)([>=])(DC|D|C)/;
            let m1 = cmd.match(regexp);
            if (m1 !== null && m1.length >= 1) {
                let r= etatParametrableCmd(balance, m1[2], m1[3], m1[4]);
                res[cmd] = r;
                return r;
            } else {
                app.log('DmcAccounting::etatParametrable error : bad cmd '+cmds[i]);   
                return null;
            }
        }

        let balance = this.calcBalance( dbEntries );
        let res = {};
        for (let i=0; i<methods.length; i++){
            let cmdText = substrbefore( methods[i], '|').trim();
            let cmd     = substrafter( methods[i], '|').replace(/\s/g,'');
            if (cmd == '' || cmdText.startsWith('//')) continue;
            let cmds = cmd.split('+');
            for (let j=0; j<cmds.length; j++) {
                if (cmd.startsWith('!') || cmd.startsWith(':')) continue;
                etatParametrable_1( balance, cmds[j].trim() );
            }
        }
        for (let i=0; i<methods.length; i++){
            let cmdText = substrbefore( methods[i], '|').trim();
            let cmd     = substrafter( methods[i], '|').replace(/\s/g,'');
            if (cmd == '' || cmdText.startsWith('//')) continue;
            let cmds = cmd.split('+');
            for (let j=0; j<cmds.length; j++) {
                if (!cmd.startsWith('!') || cmd.startsWith(':')) continue;
                etatParametrable_1( balance, cmds[j].trim() );
            }
        }

        function rowSeparator (soldeonly) {
            if (soldeonly)
                return '<tr><td> &nbsp; </td><td> &nbsp; </td></tr>\\n';
            return '<tr><td> &nbsp; </td><td colspan="3"> &nbsp; </td></tr>\\n';
        }

        let innerHtml='';
        let positive = true;
        let total    = {  tDebit: 0, tCredit: 0, sDebit: 0, sCredit: 0  };
        let subtotal = {  tDebit: 0, tCredit: 0, sDebit: 0, sCredit: 0  };
        for (let i=0; i<methods.length; i++){
            let cmdText = substrbefore( methods[i], '|').trim();
            let cmd     = substrafter( methods[i], '|').replace(/\s/g,'');
            if (cmd == '' || cmdText.startsWith('//')) continue;
            if (cmd.startsWith(':')) {
                let cmd1    = substrbefore( cmd.slice(1), ':');
                let args1   = substrafter( cmd.slice(1), ':');
                switch (cmd1) {
                    case 'soldeonly' : 
                        soldeonly = !soldeonly;
                        break;
                    case 'positive' :
                        positive = true;
                        break;
                    case 'negative' :
                        positive = false;
                        break;
                    case 'separator' :
                        innerHtml += rowSeparator (soldeonly);
                        break;
                    case 'title' :
                        innerHtml +=  '<tr><td colspan="100%" style="text-align:center; font-size:150%"><b>'+
                            escapeHtml(cmdText)+'</b></td></tr>\\n';
                        break;
                    case 'title1' :
                        innerHtml +=  '<tr><td><b>'+
                            escapeHtml(cmdText)+'</b></td>';
                        if (!soldeonly) {
                            innerHtml += '<td > &nbsp; </td>';
                            innerHtml += '<td > &nbsp; </td>';
                        }
                        innerHtml += '<td > &nbsp; </td>';
                        innerHtml += '</tr>\\n';
                        break;
                    case 'subtotal' :
                        innerHtml += '<tr><td><b>'+ escapeHtml( cmdText )+'</b></td>';
                        if (!soldeonly) {
                            innerHtml += this._tdFmt( positive ? subtotal.tDebit : -subtotal.tDebit,  'numeric', true );
                            innerHtml += this._tdFmt( positive ? subtotal.tCredit : -subtotal.tCredit, 'numeric', true );
                        }
                        innerHtml += this._tdFmt( positive ? subtotal.sDebit - subtotal.sCredit : subtotal.sCredit - subtotal.sDebit,  'numeric', true );
                        innerHtml += '</tr>\\n';   
                        subtotal = {  tDebit: 0, tCredit: 0, sDebit: 0, sCredit: 0  };                     
                        break;
                    case 'columns' :
                        let style='style="text-align: center;"'; 
                        if (soldeonly) 
                            innerHtml +=  '<tr><td '+style+ '>Libellé</td><td '+style +'>Solde</td></tr>\\n';
                        else
                            innerHtml +=  '<tr><td '+style+ '>Libellé</td><td '+style+ '>Débit</td><td '+style+ '>Crédit</td><td '+style +'>Solde</td></tr>\\n';
                        break;
                    case 'total' :
                        innerHtml += '<tr><td><b>'+ escapeHtml( cmdText )+'</b></td>';
                        if (!soldeonly) {
                            innerHtml += this._tdFmt( positive ? total.tDebit : -total.tDebit,  'numeric', true );
                            innerHtml += this._tdFmt( positive ? total.tCredit : -total.tCredit, 'numeric', true );
                        } 
                        innerHtml += this._tdFmt( positive ? total.sDebit - total.sCredit : total.sCredit - total.sDebit,  'numeric', true );
                        innerHtml += '</tr>\\n';   
                        total = {  tDebit: 0, tCredit: 0, sDebit: 0, sCredit: 0  };                     
                        break;
                }
            } else {
                let t = {  tDebit: 0, tCredit: 0, sDebit: 0, sCredit: 0  };
                let cmds = cmd.split('+');
                for (let j=0; j<cmds.length; j++) {
                    let r=res[cmds[j]];
                    t.tDebit  += r.tDebit;    t.tCredit += r.tCredit;
                    t.sDebit  += r.sDebit;    t.sCredit += r.sCredit;
                }
                total.tDebit  += t.tDebit;    total.tCredit += t.tCredit;
                total.sDebit  += t.sDebit;    total.sCredit += t.sCredit;
                subtotal.tDebit  += t.tDebit;    subtotal.tCredit += t.tCredit;
                subtotal.sDebit  += t.sDebit;    subtotal.sCredit += t.sCredit;
                innerHtml += '<tr><td>'+ escapeHtml( cmdText )+'</td>';
                if (!soldeonly) {
                    innerHtml += this._tdFmt( positive ? t.tDebit   : -t.tDebit,   'numeric', false );
                    innerHtml += this._tdFmt( positive ? t.tCredit  : -t.tCredit , 'numeric', false );
                }
                innerHtml += this._tdFmt( positive ? t.sDebit - t.sCredit : t.sCredit - t.sDebit,  'numeric', false );
                innerHtml += '</tr>\\n';                
            } // else
        }


        let needSep = true;
        for (let compte in balance) {
            let c = balance[compte];
            if ( c.Debit == 0 && c.Credit == 0 ) continue;
            if ( needSep ) {  
                innerHtml += rowSeparator(soldeonly);  needSep = false;   
                innerHtml +=  '<tr><td><b>Comptes inutilisés</b></td><td colspan="3"> &nbsp; </td></tr>\\n';
            }
            innerHtml += '<tr><td>'+ escapeHtml( 'manquant: '+compte )+'</td>';
            if (!soldeonly) {
                innerHtml += this._tdFmt( c.Debit,  'numeric', true );
                innerHtml += this._tdFmt( c.Credit, 'numeric', true );
            }
            innerHtml += this._tdFmt( c.sDebit - c.sCredit,  'numeric', true );
            innerHtml += '</tr>\\n';

        };  // for


        return innerHtml;
    }



    calcBalance( dbEntries ) {
        let bal = {};
        let col = this.getColIndexes();

        for (let row=0; row<dbEntries.length; row++) {
            let entry = dbEntries[row];
            let entryCompte = entry[col.Compte];
            if (! (entryCompte in bal) ) 
                bal[entryCompte] = { Debit:0.0,  Credit:0.0 };
            bal[entryCompte].Debit  +=  entry[col.Debit];
            bal[entryCompte].Credit +=  entry[col.Credit];
        }

        let keys = Object.keys(bal).sort();

        let result={};
        for (let entryCompte of keys) {
            let x = bal[entryCompte].Debit - bal[entryCompte].Credit;
            result[entryCompte] = {   
                Debit:    bal[entryCompte].Debit,  
                Credit:   bal[entryCompte].Credit, 
                sDebit:   (x>0) ?   x  : 0,
                sCredit:  (x<0) ? (-x) : 0  };
        }
        return result;
    }



    balance( dbEntries ) {
        let bal = this.calcBalance(dbEntries);
        let col = this.getColIndexes();

        let innerHtml='', bilan_totDebit=0.0, bilan_totCredit=0.0;
        let resultat_totDebit=0.0, resultat_totCredit=0.0;
        let bilanSolde_totDebit=0.0, bilanSolde_totCredit=0.0;
        let resulSolde_totDebit=0.0, resulSolde_totCredit=0.0;
        let keys = Object.keys(bal).sort();
        for (let entryCompte of keys) {
            innerHtml +=  '<tr>' + 
                this._tdFmt( entryCompte,               'string'  ) +
                this._tdFmt( bal[entryCompte].Debit,    'numeric' ) +
                this._tdFmt( bal[entryCompte].Credit,   'numeric' ) +
                this._tdFmt( bal[entryCompte].sDebit,   'numeric' ) +
                this._tdFmt( bal[entryCompte].sCredit,  'numeric' ) +
                '</tr>\\n';
            if (this.isCompteBilan(entryCompte)) {
                bilan_totDebit       += bal[entryCompte].Debit;
                bilan_totCredit      += bal[entryCompte].Credit;                    
                bilanSolde_totDebit  += bal[entryCompte].sDebit;
                bilanSolde_totCredit += bal[entryCompte].sCredit;
            } else if (this.isCompteResultat(entryCompte)) {
                resultat_totDebit    += bal[entryCompte].Debit;
                resultat_totCredit   += bal[entryCompte].Credit;
                resulSolde_totDebit  += bal[entryCompte].sDebit;
                resulSolde_totCredit += bal[entryCompte].sCredit;
            }
        }
        let x = bilanSolde_totDebit - bilanSolde_totCredit;
        if (x>0) {  bilanSolde_totDebit = x;      bilanSolde_totCredit = 0.0;   }  
        else     {  bilanSolde_totDebit = 0.0;    bilanSolde_totCredit = -x;    }
        x = resulSolde_totDebit - resulSolde_totCredit;
        if (x>0) {  resulSolde_totDebit = x;      resulSolde_totCredit = 0.0;   }  
        else     {  resulSolde_totDebit = 0.0;    resulSolde_totCredit = -x;    }

        innerHtml +=  '<tr>' + 
            this._tdFmt( 'Total bilan',         'string'  ) +
            this._tdFmt( bilan_totDebit,        'numeric' ) +
            this._tdFmt( bilan_totCredit,       'numeric' ) +
            this._tdFmt( bilanSolde_totDebit,   'numeric' ) +
            this._tdFmt( bilanSolde_totCredit,  'numeric' ) +
            '</tr>\\n';
        innerHtml +=  '<tr>' + 
            this._tdFmt( 'Total résultat',      'string'  ) +
            this._tdFmt( resultat_totDebit,     'numeric' ) +
            this._tdFmt( resultat_totCredit,    'numeric' ) +
            this._tdFmt( resulSolde_totDebit,   'numeric' ) +
            this._tdFmt( resulSolde_totCredit,  'numeric' ) +
            '</tr>\\n';
        return innerHtml;
    }





    calcBalanceFilter__obsolete( dbEntries, searchCompte, op, filter = [] )  {
        let bal = {};
        let col = this.getColIndexes();

        for (let row=0; row<dbEntries.length; row++) {
            let entry = dbEntries[row];
            let entryCompte = entry[col.Compte];
            if (! (entryCompte in bal) ) 
                bal[entryCompte] = { Debit:0.0,  Credit:0.0 };
            bal[entryCompte].Debit  +=  entry[col.Debit];
            bal[entryCompte].Credit +=  entry[col.Credit];
        }

        let keys = Object.keys(bal).sort();

        let result={};
        for (let entryCompte of keys) {
            let x = bal[entryCompte].Debit - bal[entryCompte].Credit;
            result[entryCompte] = {   
                Debit:    bal[entryCompte].Debit,  
                Credit:   bal[entryCompte].Credit, 
                sDebit:   (x>0) ?   x  : 0,
                sCredit:  (x<0) ? (-x) : 0  };
        }
        return result;
    }



    calcBalanceFilter___obsolete( dbEntries, searchCompte, op, filter = '', subarr = undefined )  {

        let col = this.getColIndexesTotal();
        if (searchCompte != '') {
            if (filter!='')     filter += ';';
            filter +="Compte"+op+"'"+ searchCompte +"'";
        }
        let arr = filter.split(';');
        let farr = {};
        const regexp = /([^>=]*)([>=])'(.*)'/;
        for (let i=0;i<arr.length; i++) {
            let m1 = arr[i].match(regexp);
            farr[m1[1]] = { test:m1[2], value:m1[3] };
        }

        let filtered = [];
        for (let row=0; row<dbEntries.length; row++) {
            let entry = dbEntries[row];

            // see that later
            if (entry[col.Debit] === "")  entry[col.Debit]=0;
            if (entry[col.Credit] === "") entry[col.Credit]=0;

            let test = true;
            for (let prop in farr)  {
                let c = col[prop];
                let v = farr[prop];
                if (v.test == '=') {
                    if (entry[c]  != v.value) 
                        { test = false; break; }
                } else if (v.test == '>') {
                    if ( !entry[c].startsWith(v.value) ) 
                        { test = false; break; }
                }
            }
            if (!test) continue;
            filtered.push(row);
        }

        let result = {   
                tDebit:   0,    tCredit:  0, 
                sDebit:   0,    sCredit:  0  
        };
        for (let i=0; i<filtered.length; i++) {
            let entry = dbEntries[ filtered[i] ];
            result.tDebit  +=  entry[col.Debit];
            result.tCredit +=  entry[col.Credit];
            let x = entry[col.Debit] - entry[col.Credit]; 
            if (x>0) {
                result.sDebit  += x;
            } else {
                result.sCredit -= x;
            } 
        }

        // remove all used entries
        array_splice(dbEntries, filtered, subarr);


/*
    delete will delete the object property, but will not reindex the array or 
    update its length. This makes it appears as if it is undefined:

    myArray.splice(start, deleteCount) actually removes the element, 
    reindexes the array, and changes its length.
*/

        return result;
    }



/*    =======================================================

        Beta test : try filters

      =======================================================
*/
    etatParametrableFilter_obsolete( dbEntries, method ) {
        let _this = this;  // keep for sub function
        let col = this.getColIndexes();
        let methods = method.split('\\n');
        let soldeonly = false;
        let db = [].concat(dbEntries);


        function etatParametrableCmd( bal, searchCompte, op, dc ) {
            let r = {  tDebit: 0, tCredit: 0, sDebit: 0, sCredit: 0  };

            for (let compte in bal) {
                if ( ( op=='>' && compte.startsWith(searchCompte) )  || 
                     ( op=='=' && compte == searchCompte ) 
                    ) {
                        let x, c = bal[compte];
                        x = c.Debit - c.Credit;
                        if (  dc=='DC' ||
                             (dc=='D'  && x>0) ||
                             (dc=='C'  && x<0)    )  {
                                //if (arr[compte] == undefined)  arr[compte] = { tDebit:0, tCredit:0, sDebit:0, sCredit:0 };
                            if (x>0) r.sDebit  += x;
                            else     r.sCredit += (-x);
                            r.tDebit  += c.Debit;
                            r.tCredit += c.Credit;
                            c.Debit = c.Credit = 0;
                        } // if                        
                } // if
            };  // for
            return r;
        }

        function etatParametrable_1( bal, cmd ) {

            let regexp = /(!?)([0-9]+[0-9a-zA-Z]*)([>=])(DC|D|C)/;
            let m1 = cmd.match(regexp);
            if (m1 !== null && m1.length >= 1) {
                let r= etatParametrableCmd(bal, m1[2], m1[3], m1[4]);
                res[cmd] = r;
                return r;
            } else {
                app.log('DmcAccounting::etatParametrable error : bad cmd '+cmds[i]);   
                return null;
            }
        }



        let res = {};
        for (let i=0; i<methods.length; i++){
            let cmdText = substrbefore( methods[i], '|').trim();
            let cmd     = substrafter( methods[i], '|').replace(/\s/g,'');  // 1
            if (cmd == '' || cmdText.startsWith('//')) continue;
            let cmds = cmd.split('+');
            for (let j=0; j<cmds.length; j++) {
                if (cmd.startsWith('!') || cmd.startsWith(':')) continue;
                let bal = this.calcBalanceFilter_( db, '', '' );
                etatParametrable_1( bal, cmds[j].trim() );
            }
        }
        for (let i=0; i<methods.length; i++){
            let cmdText = substrbefore( methods[i], '|').trim();
            let cmd     = substrafter( methods[i], '|').replace(/\s/g,'');  // 2
            if (cmd == '' || cmdText.startsWith('//')) continue;
            let cmds = cmd.split('+');
            for (let j=0; j<cmds.length; j++) {
                if (!cmd.startsWith('!') || cmd.startsWith(':')) continue;
                let bal = this.calcBalanceFilter_( db, '', '' );
                etatParametrable_1( bal, cmds[j].trim() );
            }
        }

        function rowSeparator (soldeonly) {
            if (soldeonly)
                return '<tr><td> &nbsp; </td><td> &nbsp; </td></tr>\\n';
            return '<tr><td> &nbsp; </td><td colspan="3"> &nbsp; </td></tr>\\n';
        }

        let innerHtml='';
        let positive = true;
        let filter='';
        let total    = {  tDebit: 0, tCredit: 0, sDebit: 0, sCredit: 0  };
        let subtotal = {  tDebit: 0, tCredit: 0, sDebit: 0, sCredit: 0  };
        for (let i=0; i<methods.length; i++){
            let cmdText = substrbefore( methods[i], '|').trim();
            let cmd     = substrafter( methods[i], '|').trim();       // .replace(/\s/g,'')
            if (cmd == '' || cmdText.startsWith('//')) continue;
            if (cmd.startsWith(':')) {
                let cmd1    = substrbefore( cmd.slice(1), ':');
                let args1   = substrafter( cmd.slice(1), ':');
                switch (cmd1) {
                    case 'soldeonly' : 
                        soldeonly = !soldeonly;
                        break;
                    case 'positive' :
                        positive = true;
                        break;
                    case 'negative' :
                        positive = false;
                        break;
                    case 'separator' :
                        innerHtml += rowSeparator (soldeonly);
                        break;
                    case 'title' :
                        innerHtml +=  '<tr><td colspan="100%" style="text-align:center; font-size:150%"><b>'+
                            escapeHtml(cmdText)+'</b></td></tr>\\n';
                        break;
                    case 'filter' :
                        filter=args1;
                        // innerHtml +=  '<tr><td style="text-align:right; font-size:100%">'+
                        // '<b>filter :</b></td>'+
                        //    '<td>'+escapeHtml(args1)+'</td></tr>\\n';  
                        break;
                    case 'title1' :
                        innerHtml +=  '<tr><td><b>'+
                            escapeHtml(cmdText)+'</b></td>';
                        if (!soldeonly) {
                            innerHtml += '<td > &nbsp; </td>';
                            innerHtml += '<td > &nbsp; </td>';
                        }
                        innerHtml += '<td > &nbsp; </td>';
                        innerHtml += '</tr>\\n';
                        break;
                    case 'subtotal' :
                        innerHtml += '<tr><td><b>'+ escapeHtml( cmdText )+'</b></td>';
                        if (!soldeonly) {
                            innerHtml += this._tdFmt( positive ? subtotal.tDebit : -subtotal.tDebit,  'numeric', true );
                            innerHtml += this._tdFmt( positive ? subtotal.tCredit : -subtotal.tCredit, 'numeric', true );
                        }
                        innerHtml += this._tdFmt( positive ? subtotal.sDebit - subtotal.sCredit : subtotal.sCredit - subtotal.sDebit,  'numeric', true );
                        innerHtml += '</tr>\\n';   
                        subtotal = {  tDebit: 0, tCredit: 0, sDebit: 0, sCredit: 0  };                     
                        break;
                    case 'columns' :
                        let style='style="text-align: center;"'; 
                        if (soldeonly) 
                            innerHtml +=  '<tr><td '+style+ '>Libellé</td><td '+style +'>Solde</td></tr>\\n';
                        else
                            innerHtml +=  '<tr><td '+style+ '>Libellé</td><td '+style+ '>Débit</td><td '+style+ '>Crédit</td><td '+style +'>Solde</td></tr>\\n';
                        break;
                    case 'total' :
                        innerHtml += '<tr><td><b>'+ escapeHtml( cmdText )+'</b></td>';
                        if (!soldeonly) {
                            innerHtml += this._tdFmt( positive ? total.tDebit : -total.tDebit,  'numeric', true );
                            innerHtml += this._tdFmt( positive ? total.tCredit : -total.tCredit, 'numeric', true );
                        } 
                        innerHtml += this._tdFmt( positive ? total.sDebit - total.sCredit : total.sCredit - total.sDebit,  'numeric', true );
                        innerHtml += '</tr>\\n';   
                        total = {  tDebit: 0, tCredit: 0, sDebit: 0, sCredit: 0  };                     
                        break;
                }
            } else {
                let t = {  tDebit: 0, tCredit: 0, sDebit: 0, sCredit: 0  };
                let cmds = cmd.split('+');
                for (let j=0; j<cmds.length; j++) {
                    let regexp = /(!?)([0-9]+[0-9a-zA-Z]*)([>=])(DC|D|C)/;
                    let m1 = cmds[j].match(regexp);
                    if (m1 !== null && m1.length >= 1) {
                        let r=this.calcBalanceFilter__(db, m1[2], m1[3], filter);   // res[cmds[j]];
                        t.tDebit  += r.tDebit;      t.tCredit += r.tCredit;
                        t.sDebit  += r.sDebit;      t.sCredit += r.sCredit;
                    } 
                }
                total.tDebit  += t.tDebit;    total.tCredit += t.tCredit;
                total.sDebit  += t.sDebit;    total.sCredit += t.sCredit;
                subtotal.tDebit  += t.tDebit;    subtotal.tCredit += t.tCredit;
                subtotal.sDebit  += t.sDebit;    subtotal.sCredit += t.sCredit;
                innerHtml += '<tr><td>'+ escapeHtml( cmdText )+'</td>';
                if (!soldeonly) {
                    innerHtml += this._tdFmt( positive ? t.tDebit   : -t.tDebit,   'numeric', false );
                    innerHtml += this._tdFmt( positive ? t.tCredit  : -t.tCredit , 'numeric', false );
                }
                innerHtml += this._tdFmt( positive ? t.sDebit - t.sCredit : t.sCredit - t.sDebit,  'numeric', false );
                innerHtml += '</tr>\\n';                
            } // else
        }


        // list rests :
        // =============

        let needSep = true;
        let bal = this.calcBalanceFilter_( db, '60', '' );
        for (let compte in bal) {
            let c = bal[compte];
            if ( c.Debit == 0 && c.Credit == 0 ) continue;
            if ( needSep ) {  
                innerHtml += rowSeparator(soldeonly);  needSep = false;   
                innerHtml +=  '<tr><td><b>Comptes inutilisés</b></td><td colspan="3"> &nbsp; </td></tr>\\n';
            }
            innerHtml += '<tr><td>'+ escapeHtml( 'manquant: '+compte )+'</td>';
            if (!soldeonly) {
                innerHtml += this._tdFmt( c.Debit,  'numeric', true );
                innerHtml += this._tdFmt( c.Credit, 'numeric', true );
            }
            innerHtml += this._tdFmt( c.sDebit - c.sCredit,  'numeric', true );
            innerHtml += '</tr>\\n';

        };  // for


        return innerHtml;
    } // etatParametrableFilter_obsolete



} // class DmcAccounting



EOLONGTEXT );  // mod_js_class_Accounting     




CModules::include_end( __FILE__ );

?>