<?php
        // -------------------
        // div-accounting2.mod.php
        // -------------------


CModules::include_begin( __FILE__ , 'This is division module  accounting2' );



CModules::append( 'mod_js_class_Accounting2', <<<EOLONGTEXT

class DmcAccountingFilter  extends DmcAccounting {


    calcBalanceFilter_( dbEntries, searchCompte, op, filter = [] )  {
        let bal = {};
        let col = this.getColIndexes();

        for (let row=0; row<dbEntries.length; row++) {
            let entry = dbEntries[row];
            let entryCompte = entry[col.Compte];
            if ( searchCompte != '' && !entryCompte.startsWith(searchCompte) ) continue;
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



    calcBalanceFilter__( dbEntries, searchCompte, op, filter = '', subarr = undefined )  {

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

        return result;
    }



    debug(dbEntries, searchCompte) {
        let innerHtml = '';

        innerHtml +=  '<tr><td>debug</td></tr>\\n';

        let col = this.getColIndexesTotal();

        for (let row=0; row<dbEntries.length; row++) {
            let entry = dbEntries[row];
            if ( entry[col.Compte].startsWith(searchCompte) ) {
                innerHtml +=  '<tr><td>'+entry[col.DatEcr]+'</td>' +
                    '<td>'+entry[col.Compte]+'</td>' + 
                    '<td>'+entry[col.Site]+'</td>' + 
                    '<td>'+entry[col.Libelle]+'</td>' + 
                    '<td>'+entry[col.Credit]+'</td>' +
                    '<td>'+entry[col.Debit]+'</td></tr>\\n';
            } 
        }

        innerHtml +=  '<tr><td>debug</td></tr>\\n';

        return innerHtml;
    }




    // subfct
        etatParametrableCmd( bal, searchCompte, op, dc ) {
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


    // subfct

        etatParametrable_1( bal, cmd, res ) {

            let regexp = /(!?)([0-9]+[0-9a-zA-Z]*)([>=])(DC|D|C)/;
            let m1 = cmd.match(regexp);
            if (m1 !== null && m1.length >= 1) {
                let r= this.etatParametrableCmd(bal, m1[2], m1[3], m1[4]);
                res[cmd] = r;
                return r;
            } else {
                app.log('DmcAccounting::etatParametrable error : bad cmd '+cmds[i]);   
                return null;
            }
        }

    // subfct

        rowSeparator (soldeonly) {
            if (soldeonly)
                return '<tr><td> &nbsp; </td><td> &nbsp; </td></tr>\\n';
            return '<tr><td> &nbsp; </td><td colspan="3"> &nbsp; </td></tr>\\n';
        }


/*    =======================================================

        Beta test : try filters

      =======================================================
*/
    etatParametrableFilter( dbEntries, method ) {
        let _this = this;  // keep for sub function
        let col = this.getColIndexes();
        let methods = method.split('\\n');
        let soldeonly = false;
        let db = [].concat(dbEntries);




        let res = {};
        for (let i=0; i<methods.length; i++){
            let cmdText = substrbefore( methods[i], '|').trim();
            let cmd     = substrafter( methods[i], '|').replace(/\s/g,'');  // 1
            if (cmd == '' || cmdText.startsWith('//')) continue;
            let cmds = cmd.split('+');
            for (let j=0; j<cmds.length; j++) {
                if (cmd.startsWith('!') || cmd.startsWith(':')) continue;
                let bal = this.calcBalanceFilter_( db, '', '' );
                this.etatParametrable_1( bal, cmds[j].trim(), res );
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
                this.etatParametrable_1( bal, cmds[j].trim(), res );
            }
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
                        innerHtml += this.rowSeparator (soldeonly);
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
        let bal = this.calcBalanceFilter_( db, '', '' );
        for (let compte in bal) {
            let c = bal[compte];
            if ( c.Debit == 0 && c.Credit == 0 ) continue;
            if ( needSep ) {  
                innerHtml += this.rowSeparator(soldeonly);  needSep = false;   
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
    } // etatParametrableFilter



} // class DmcAccountingFilter



EOLONGTEXT );  // mod_js_class_Accounting2     




CModules::include_end( __FILE__ );

?>