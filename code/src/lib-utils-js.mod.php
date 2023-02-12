<?php
        // -------------------
        // lib-utils-js.mod.php
        // -------------------


CModules::include_begin(__FILE__, 'This is division module utils-js (some js utilities)');



CModules::append( 'mod_js_lib_util', <<<EOLONGTEXT


// ========================================
// ============ js util
// ========================================


function method_exists( _instance, _method ) {

    if (_method in _instance)    return true;
    // if (typeof _instance._method === "function")     return true;
    return false;

}

async function test_async() {

        await (new Promise(  function(resolve, reject) {
                console.log('promise []');
                setTimeout(function(){
                    console.log("in timer");
                    resolve('test');
                }
                , 1000); 
        })).then(
            function(response) {  
                console.log('resolve ['+response+']');
            },   
            function(error)    
            {        
                console.log('reject ['+error+']');
            }                    
        );

}


// watermark 
// code from {Ninh Pham} {stackoverflow.com}
// https://stackoverflow.com/questions/9719570/generate-random-password-string-with-requirements-in-javascript

function cryptoUniquePassword ( len=12, 
        charlist='0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz!@%-+;.#<>()[]\$') {
    let generatePassword = (
      length = len,
      wishlist = charlist
    ) =>
      Array.from(crypto.getRandomValues(new Uint32Array(length)))
        .map((x) => wishlist[x % wishlist.length])
        .join('')

    return generatePassword();
}


function createUniqueId ( len=8 ) {
    return cryptoUniquePassword ( len, 
        charlist='0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz'); 
} 


// watermark 
function createUniqueId_obsolete() {

        // window.crypto.getRandomValues() 

        function _createNumber() {
            let millisec = 
                (  Math.random() ).toString().substring(2, 30) +
                (  Math.random() ).toString().substring(3, 30) +
                (  Math.random() ).toString().substring(4, 30) +
                (  Math.random() ).toString().substring(5, 30);
            let m = parseFloat('0.'+millisec);
            let rand = Math.random() + m; 
            let s1 = rand.toString().substring(2,18); 
            while (s1.length < 16) s1 = '0'+s1;        
            return s1;
        }

    let s =  parseInt(_createNumber()).toString(36)
           + parseInt(_createNumber()).toString(36)
           + parseInt(_createNumber()).toString(36)
           + parseInt(_createNumber()).toString(36);
    return s.substring(2, 18);

}

// htmlspecialchars for js ?
function escapeHtml(s) {
    var MAP = {
        '&': '&amp;', '<': '&lt;', '>': '&gt;',
        '"': '&#34;', "'": '&#39;' //    "&quot;"
    };
    if (s == null)
        return '';
    return s.replace(/[&<>"']/g, function (c) { return MAP[c]; });
}

function today() {
    return date('Y-m-d H:i:s', time());    // date_default_timezone_get()
}


function substr_replace(str, replacement, index ) {
    return str.substring(0, index) + replacement + str.substring(index + replacement.length);
}

function count_substr(str, sub) {
    let backslash = '\\\\';
    if (typeof sub == 'string') 
        sub = sub.replace(backslash, backslash+backslash);
    return ( str.match( new RegExp(sub, "g") ) || [] ).length;
    // function count_substr(str, sub) { return ( str.match( new RegExp(sub, "g") ) || [] ).length; }
}


function isEven(n) {        // estPair
   return n % 2 == 0;
}



function changeCRinQuotes( txt, newChar='¶' ) {
    let line, last, first=0, num=1, s='', nbQuote=0, complete='';
    while (true) {
        last = txt.indexOf('\\n', first);
        line = (last == -1) ? txt.slice(first) : txt.slice(first, last);
        nbQuote += count_substr(line, '"') - count_substr(line, /\\\\\"/g);
        if (isEven(nbQuote)) {
            // s += '[' + num + ']=' + complete + line + 'ø\\n';    // •  ø ¶
            s += complete + line + '\\n';
            num++;
            complete='';
            line='';
            nbQuote=0;
        } else {
            complete = complete + line + newChar;
            line='';
        }
        if (last == -1) break;
        first = last+1;  
    }
    return s;
}


function columnSplit( txt, column=';', quote='"' ) {
    let r = [];

    let line, last, first, s='', inQuotes=false;
    let l1, l2, l3;
    for (first=0, last=0;  last != -1; first = last+1) {
        if (!inQuotes) {
            l1 = txt.indexOf(quote, first);
            l2 = txt.indexOf(column, first);
            last = l1;
            if (l2 != -1)     last = l2;
            if (l1 != -1 && l2 != -1)     last = (l1<l2) ? l1 : l2;
            inQuotes = (last == l1 && last != -1);
            line = (last == -1) ? txt.slice(first) : txt.slice(first, last);
            if (!inQuotes) {
                r.push(line);
            }
        } else {
            last = txt.indexOf(quote, first);
            l3=-1;
            if (last!=-1) l3=txt.indexOf(quote, last+1);
            while (l3!=-1 && last!=-1 && l3==last+1){
                last=txt.indexOf(quote, l3+1);
                l3=-1;
                if (last!=-1) l3=txt.indexOf(quote, last+1);
            }

            line = (last == -1) ? txt.slice(first) : txt.slice(first, last);
            //r.push( line );
            r.push( quote+line+quote );
            if (last != -1){
                l2 = txt.indexOf(column, last+1);
                if (l2 != -1) { 
                    let s = txt.slice(last+1, l2).trim();
                    if (s == '')     last=l2;
                }                
            }
            inQuotes = false;
        }
    }

    return r;
}


function changeColumn(txt, column='\t', newColumn=';') {
    
    function changeCell(s, colNum) {  return s;  }
    return _foreachLine( txt, column, newColumn, changeCell );

}


function removeQuotes1(txt) {
    // inQuotes     ‖  ⁏ 
    const newSemicolon = '‚';       // '⁏⁝'
    const newQuote     = '‖';       // '⁏'    

    const MAP = {  '"': newQuote, ';': newSemicolon  };
    const reg = /[";]/g;

    function changeCell(s, colNum) {  
        if (s.length<2) return s;
        if (s[0]!='"' || s[s.length-1]!='"') return s;

        let     ss = s.slice(1, s.length-1);
        ss = ss.replace( '""', newQuote );
        ss = ss.replace(reg, function (c) { return MAP[c]; });
        return ss;
    }

    return _foreachLine( txt, ';', ';', changeCell );

}


function removeQuotes2(s, quote='"') {  
    const newQuote     = '‖';  
    const MAP = {  quote: newQuote  };
    const reg =  new RegExp('['+quote+']', "g");
    if (s.length<2) return s;
    if (s[0]!=quote || s[s.length-1]!=quote) return s;
    let     ss = s.slice(1, s.length-1);
    ss = ss.replace( quote+quote, newQuote );
    ss = ss.replace(reg, function (c) {  return MAP[c];  });
    return ss;
}


function _foreachLine( txt, column, newColumn, fct, colNumber, nullRemoveRow = false ){

    //  for foreach line in string
    //  ===========================
    let line, last, first, num, s='';
    for (num=1, first=0, last=0;  last != -1; num++, first = last+1) {
        last = txt.indexOf('\\n', first);
        line = (last == -1) ? txt.slice(first) : txt.slice(first, last);

        let r = columnSplit( line, column ), ns='', nc;
        for (let i=0; i<r.length; i++){
            nc = fct(r[i], i);
            if (!nullRemoveRow && nc === null) continue;
            if (nullRemoveRow  && nc === null) break;
            if (i!=0) ns += newColumn;
            ns += nc;
        }
        if (nullRemoveRow && nc === null)
            continue;
        s += ns + '\\n';
    }
    return s;
}


function changeDecimalSepFromPoint(txt) {

    function changeCell(s, colNum) {
        if ( s=='' || isNaN(s) )  return s;
        return s.replace('.', ',');
    }
    return _foreachLine( txt, ';', ';', changeCell );

}



function removeRowIfNoDate(txt, colNumber){

    function _isDate(s) {
        const reg = /^(\d{1,2})\/(\d{1,2})\/(\d{1,4})$/; 
        const found = s.match( reg );
        if ( !found ) return false;
        let day   = parseInt( found[1] );
        let month = parseInt( found[2] );
        let year  = parseInt( found[3] );
        if (day<1   || day>31) return false;
        if (month<1 || month>12) return false;
        if (year >= 0     && year <= 99)   return true;       // year is 2 digits ?
        if (year >= 2000  && year <= 2100) return true;       // year is 4 digits ?
        return false;
    }

    function _removeRowIf(s, colNum) {
        if (colNum+1 != colNumber) return s;

        // if (s=='') return s;
        let ss = s;
        if (ss.length>=2 && ss[0]=='"' && ss[s.length-1]=='"') 
            ss = ss.slice(1, ss.length-1);

        //let start = ss.toLowerCase().slice(0,3);
        //if (start == 'dat') return s;

        if (!_isDate(ss))
            return null;
        return s;
    }

    return _foreachLine( txt, ';', ';', _removeRowIf, colNumber, true );
}





function dateToComptaDate(txt) {

    function digits2(n){
        if (n>2000) n = n-2000;
        return n > 9 ? "" + n: "0" + n;
    }

    function _comptaDate1(s, colNum) {
        if (s=='') return s;
        let ss = s;
        if (ss.length>=2 && ss[0]=='"' && ss[s.length-1]=='"') 
            ss = ss.slice(1, ss.length-1);
        const reg = /^(\d{1,2})\/(\d{1,2})\/(\d{1,4})$/; 
        const found = ss.match( reg );
        if ( !found ) return s;

        let day   = parseInt( found[1] );
        let month = parseInt( found[2] );
        let year  = parseInt( found[3] );
        if (day<1   || day>31) return s;
        if (month<1 || month>12) return s;
        return year+'-'+digits2(month)+'-'+digits2(day);
    }


    function _comptaDate(s, colNum) {
        if (s=='') return s;
        let ss = s;
        if (ss.length>=2 && ss[0]=='"' && ss[s.length-1]=='"') 
            ss = ss.slice(1, ss.length-1);

        const reg = /^(\d{1,2})\/(\d{1,2})\/(\d{1,4})$/; 
        const found = ss.match( reg );
        if ( !found ) return s;

        let day   = parseInt( found[1] );
        let month = parseInt( found[2] );
        let year  = parseInt( found[3] );
        if (day<1   || day>31) return s;
        if (month<1 || month>12) return s;
        return digits2(day)+'/'+digits2(month)+'/'+digits2(year);
    }

    return _foreachLine( txt, ';', ';', _comptaDate );
}



function trimEachCell(txt) {

    function trimCell(s, colNum) {
        if (s=='') return s;
        if (s.length>=2 && s[0]=='"' && s[s.length-1]=='"') {
            s = s.slice(1, s.length-1);
            s = '"' + s.trim() + '"';
            return s;
        }
        s = s.trim();
        return s;
    }

    return _foreachLine( txt, ';', ';', trimCell );
}


function floatToString(num) {
    return num.toFixed(2).replace('.', ',');
}



function reportCellIfNoNext(txt, colNumber) {
    let lastValue = '';

    function changeCell(s, colNum) {
        if ( colNum+1 != colNumber )  return s;

        if ( !s || s=='' ) {
            return lastValue;
        }
        else {
            lastValue = s;
            return s;
        }
    }

    return _foreachLine( txt, ';', ';', changeCell, colNumber );
}


function noQuotes(txt) {
    if (txt.length < 2) return txt;
    //let c1 = txt[0] ;
    //let c2 = txt[txt.length-1] ;
    if (txt[0] != '"' || txt[txt.length-1] != '"') return txt; 
    let t0 = txt.slice(1, txt.length-1);
    let t1 = t0.replaceAll(/""/g, '"');
    let t2 = t1.replaceAll(/\\"/g, '"');
    return t2;
}


//  syntax :    1;2;if+3;if-3;4>5;6/(D )(.*)/;7>
//              1;2;3;4;5
//              1;2;3;4;if|3=D|+4
//              changeColumnsOrder('1;2;6;8;10;15/\"([CD]) ([^\/]*)\/(.*)\"/;26;31')
function changeOrder( line, newOrder ){

    let arr =  columnSplit( line );
    let s = '';
    const regColumn     = /^\d+$/;
    const regColumnIf   = /^if[\\+\\-]\d+$/;
    const regColumnIf2  = /^if\\|(\d+)=([^|]+)\\|([\\+\\-])(\d+)$/;    //     if|3=Some|+4
    const regColumnSup  = /^(\d+)(\\>)(\d*)$/;
    const regColumnRegEx =   /^(\d+)(\/)(.*)(\/)$/;            // ex : 1/\"([CD]) ([^\/]*)\/(.*)\"/
    for (let i=0; i<newOrder.length; i++) {
        let op = newOrder[i];
        let v='';
        if ( regColumn.test(op) ) {
            let c = parseInt(op)-1;
            if (c>=0 && c<arr.length)
                v = arr[c];
        } else if (regColumnIf.test(op)) {
            let c = op.substring(3);
            let sign = op.substring(2,3);
            c = parseInt(c)-1;
            let num = NaN;
            if (c>=0 && c<arr.length && isNumeric(arr[c])) {
                num = parseFloatComma(arr[c]);
                if (num != NaN && sign == '+' && num>=0)   v = floatToString( num );
                if (num != NaN && sign == '-' && num< 0)   v = floatToString( num );
            }                
        } else if (regColumnIf2.test(op)) {
            const found = op.match(regColumnIf2);
            const colNumTest = parseInt( found[1] )-1;
            const colNumVal = parseInt( found[4] )-1;
            const txtEqual = found[2];
            const sign = found[3];
            let s='';
            if (arr[colNumTest] == txtEqual) {
                if (isNumeric(arr[colNumVal])) {
                    num = parseFloatComma(arr[colNumVal]);
                    if (num != NaN && sign == '+')   s = floatToString(  num );
                    if (num != NaN && sign == '-')   s = floatToString( -num );
                } else {
                    s = arr[colNumVal];
                }
            }
            v = s;
            // app.log('regColumnIf2');
        } else if (regColumnSup.test(op)) {
            const found = op.match(regColumnSup);
            let begin = 0, end = arr.length-1;
            if (found[1] != '') begin = parseInt( found[1] )-1;
            if (found[3] != '') end   = parseInt( found[3] )-1;
            for (let c=begin; c<=end; c++) {
                if (v == '') v += arr[c];        //  noQuotes(arr[c]);
                else         v += ' '+arr[c];    //  noQuotes(arr[c]);
            }
        } else if (regColumnRegEx.test(op)) {
            const found = op.match(regColumnRegEx);
            let str1='', match;
            if (found[1] != '') col = parseInt( found[1] )-1;
            if (found[3] != '') str1 = found[3];
            let re = new RegExp(str1, "");
            let search = arr[col];
            if (search) match = search.match( re );
            if (match != null){
                for (let i=1; i<match.length; i++){
                    if (v == '') v += match[i];        //  noQuotes(arr[c]);
                    else         v += ';'+match[i];    //  noQuotes(arr[c]);
                }
            } else {                
                let s = '';
                if (search)
                    s = 'regex('+op+') not found in '+search;
                else
                    s = '';
                if (v == '') v += s;        //  noQuotes(arr[c]);
                else         v += ';'+s;    //  noQuotes(arr[c]);                
            }
        } else {
            v = '?';
        }
        s += v+';';
    }

    let lastIndex = s.lastIndexOf(';');
    if (lastIndex == s.length-1) {
        s = s.slice(0, -1); 
    }
    return s;
}



function isNumeric(num) {
    // const reg = /^-?\d+\,?\d*$/;
    const reg = /^-?[\d\s]+\,?\d*[\s]*€?$/;
    return reg.test(num);
    // return !isNaN(num);
}

function parseFloatComma(num) {
    if (!isNumeric(num))
        return NaN;
    return parseFloat( num.replace(',', '.').replace(/[\s€]/g, '') );
}

function formatDate_YMD( str, datefmts = undefined ){
    const reg = /^(\d{1,2})\/(\d{1,2})\/(\d{1,4})$/; //   /^\d{1,2}\/\d{1,2}\/\d{1,4}$/;
    const found = str.match( reg );
    if ( !found ) return false;
    let day   = parseInt( found[1] );
    let month = parseInt( found[2] );
    let year  = parseInt( found[3] );
    if (year>=0 && year<=60) year = 2000+year;
    else if (year>60 && year<=99) year = 1900+year;
    if (day<1   || day>31) return false;
    if (month<1 || month>12) return false;

    day   = day.toString().padStart(2, '0');
    month = month.toString().padStart(2, '0');
    year  = year.toString().padStart(4, '0');
    return year+'-'+month+'-'+day;
}



function isDate_slash( str ){
    const reg = /^(\d{1,2})\/(\d{1,2})\/(\d{1,4})$/; //   /^\d{1,2}\/\d{1,2}\/\d{1,4}$/;
    const found = str.match( reg );
    if ( !found ) return false;
    let day   = parseInt( found[1] );
    let month = parseInt( found[2] );
    // let year  = parseInt( found[3] );
    // if (day<1   || day>31) return false;
    // if (month<1 || month>12) return false;
    return true;
    // return !isNaN(num);
}

function setCharAt(str,index,chr) {
    if(index > str.length-1) return str;
    return str.substring(0,index) + chr + str.substring(index+1);
}


function removeNoBreakSpaces(s) {    
    return s.replaceAll('\xc2\xa0', '');    
}


function array_splice ( arr, array_of_indexes, subarr=undefined ) {

    if (array_of_indexes.length == 0) return;
    let f = new Int32Array(array_of_indexes);   // By default, the sort method sorts elements alphabetically.
                                                // we use Int32Array to resolve that problem
    f.sort();
    for (let i=f.length-1; i>=0; i--) {
        if (subarr !== undefined) subarr.push( arr[f[i]] );
        arr.splice(f[i], 1);
    }
}

function removeMoneySign(s) {
    return s.replaceAll(/[€£₤₿¥\\\\$]/g, '');    
}

function from_ISO_8859_15(s) {          // in Mac's world
    if (s == null)    return '';
    let ISO_8859_15 = {
        'È' : 'é',      'Ë' : 'è',
        'Í' : 'ê',      'Î' : 'ë',
        '»' : 'È',      '‡' : 'à',
        '‚' : 'â',      '¿' : 'À',
        'Ô' : 'ï',      'Ä' : '€',
        '∞' : '°',      '†' : '\xc2\xa0',
        'Ù' : 'ô'     };
    return s.replace(/[ÈËÍÎ»‡‚¿ÔÄ∞†Ù]/g, function (c) { return ISO_8859_15[c]; });
}

function is_ISO_8859_15(txt) {          // in Mac's world
    let ISO_8859_15 = {
        'È' : 'é',      'Ë' : 'è',
        'Í' : 'ê',      'Î' : 'ë',
        '»' : 'È',      '‡' : 'à',
        '‚' : 'â',      '¿' : 'À',
        'Ô' : 'ï',      'Ä' : '€',
        '∞' : '°',      '†' : '\xc2\xa0',
        'Ù' : 'ô'     };
    let s = '';
    Object.entries(search_ISO_8859_15).forEach(entry => {
        const [k, v] = entry;
        if (txt.indexOf(k) != -1)
            s += 'found = ['+k+'] (0x'+k.charCodeAt(0).toString(16).toUpperCase()+') '+
                    'which may be ['+v+']\\n';
    });
    return s;
}


function listLSCookie() {
    let s='listLSCookie \\n';
    for (let i=0; i<localStorage.length; i++) {
        let cname = localStorage.key(i);
        let r = localStorage[cname];
        if (r === undefined || r === null)  r = 'null';
        s += cname + '=[' + r + ']\\n'; 
    }
    return s;
}


function resetLSCookie( prefix='' ) {
    if (prefix=='') {
        localStorage.clear();
        return;
    }

    let todelete = [];
    for (let i=0; i<localStorage.length; i++) {
        const cname = localStorage.key(i);
        if (cname.startsWith(prefix))
            todelete.push(cname);
    }

    for (i=0; i<todelete.length; i++) {
        localStorage.removeItem(todelete[i]);
    }
}

//  getLSCookie  (see also setLSCookie)
function getLSCookie(cname) {
    let r = localStorage[cname];
    if (r === undefined || r === null)
        return null;
    if (r.startsWith('(boolean)')) {
        return (r.substring(r.indexOf(')') + 1) == 'true');
    }
    if (r.startsWith('(float)')) {
        return parseFloat(r.substring(r.indexOf(')') + 1));
    }
    if (r.startsWith('(number)')) {
        return parseFloat(r.substring(r.indexOf(')') + 1));
    }
    return r;
}


//  setLSCookie  (see also getLSCookie)
function setLSCookie(cname, cvalue) {
    let typ = typeof cvalue;
    if (typ == 'undefined') {
        // not allowed !! delete localStorage[cname];
        localStorage.removeItem(cname);
        return;
    } 
    if (typ == 'number') 
        cvalue = '(float)' + cvalue;
    if (typ == 'boolean') 
        cvalue = '(boolean)' + (cvalue ? 'true' : 'false');
    localStorage[cname] = cvalue;
}


function removeRepeatedChar(txt1, n) {
    let txt2;
    let reg1 = new RegExp(n+n+n+n+n,"gm");
    let reg2 = new RegExp(n+n,"gm");
    while (true) {
        txt2 = txt1.replace( reg1, n );
        if (txt2.length == txt1.length) break;
        txt1=txt2;
    }
    while (true) {
        txt2 = txt1.replace( reg2, n );
        if (txt2.length == txt1.length) break;
        txt1=txt2;
    }

    // if ( n == '\\u0020' || n == '\t' ) {    txt2=txt2.trim();    }

    return txt2;
}


function str_nblines( str ){
    if (str=="") return 0;
    return (str.match(/\\n/g) || '').length + 1;
}



function calcRowHeight(textarea) {
    let rowHeight = 0;
    let old = textarea.value;
    textarea.value = '1\\n2\\n3\\n4\\n5\\n1\\n2\\n3\\n4\\n5';
    textarea.rowHeight = textarea.scrollHeight / 10.0;
    textarea.value = old;
    textarea.rowHeight = rowHeight;
    return rowHeight;

    // let nbLines = elt_file_src.clientHeight / elt_file_src.rowHeight;
}


function str_taillines( str, n = 10) {
  if ( str_nblines(str) <= n )  return str;
  return str.split('\\n').slice(-n).join('\\n');  
}


function str_firstlines( str, n = 10) {

    const regex = /[^\\n]*\\n/g;    //  RegExp('foo*', 'g');
    let arr, res = '', i=0;

    while ((arr = regex.exec(str)) !== null) {
        res += arr[0];
        i++;
        if (i>=n) break;
    }
    return res;   //  str.split('\\n').slice(n).join('\\n');  
}



function str_fromDate( d ) {

        // return ("000" + n).slice(-nb);
        function pad2(n) {  return (n < 10 ? '0' : '') + n;    }

        return d.getFullYear() + '-' +
               pad2(d.getMonth() + 1) + '-' + 
               pad2(d.getDate()) + '_' +
               pad2(d.getHours()) + ':' +
               pad2(d.getMinutes()) + ':' +
               pad2(d.getSeconds()) 
          //     + '.' + pad2(d.getMilliseconds(),3)
               ;

}


function escapeBackslashChars(s) {
    var MAP = {
        '\t'  :  '\\\\t',
        '\\n' :  '\\\\n',
        '\\r' :  '\\\\r',
        "'"   :  "\\\\'",
        '"'   :  '\\\\"', 
    };
    if (s == null)
        return '';
    let r = s.replace(/[\\r\\n\t"']/g, function (c) { 
        return MAP[c]; 
    });
    return r;
}


function string_to_uint8(str) {
    return Uint8Array.from( str.split(''), 
        function (elt) {     return elt.charCodeAt(0);    }
    );
}


// when you get a string from php base64_encode, this may help
// ---------------------

function find_encoding(txt, encodings_to_test=null) {

    // let enc = new TextEncoder(encoding);     ok only for utf-8, so don't use it (sad)
    if (!encodings_to_test) {
        encodings_to_test = {
            utf8:         [ 'utf-8',        '€ e2 82 ac', 
                                            'é c3 a9',      'è c3 a8', '° c2 b0'  ],
            macintosh:    [ 'macintosh',    '€ db',       
                                            'é 8e',         'è 8f',    '° a1'     ],
            iso885915:    [ 'iso-8859-15',  '€ a4',       
                                            'é e9',         'è e8',    '° b0'     ],
            windows1252:  [ 'windows-1252', '€ 80',       
                                            'é e9',         'è e8',    '° b0'     ]
        }
    }

    let maxlen = 0, bestenc = null;
    let found={};
    for (let enckey in encodings_to_test) {
        let encoding = encodings_to_test[enckey], nbfound=0;
        for (let i=1; i<encoding.length; i++) {
            let arr = encoding[i].split(' ');
            let search = '';
            for (let j=1; j<arr.length; j++) {
                search += String.fromCharCode( parseInt(arr[j], 16) );
            }
            if ( txt.includes(search) ){
                if (!found[encoding[0]]) found[encoding[0]] = '';
                found[encoding[0]] += arr[0];   nbfound++;
            }
        }
        if (nbfound > maxlen) {  maxlen = nbfound;  bestenc = encoding[0];  }
    }
    
    //console.log( JSON.stringify( found ) );
    return bestenc;
}


// when you get a string from php base64_encode, this may help
// ---------------------
/*
    sign    UTF-8           Unicode   
        €   E2 82 AC        20 AC      
        à   C3 A0           00 E0
        é   C3 A9           00 E9
    ex:
    binaryUtf8_toString(txt, 'iso-8859-15');    
    binaryUtf8_toString(txt, 'windows-1252');    
    binaryUtf8_toString(txt, 'macintosh');
    binaryUtf8_toString(txt, 'utf-8');

    ex:
    let txt    = 'a,b,â¬,d,Ã©,f,g';
    let result = 'a,b,€,d,é,f,g';
    let result = binaryUtf8_toString(txt);
*/
function binaryUtf8_toString( binaryString, encoding="utf-8" ) {

    try {
        let uint = string_to_uint8( binaryString );
        r = new TextDecoder(encoding).decode(uint);
    } catch(e) {
        return null;
    }

    return r;

}

/*
    css style : convertion from px, em to px

    NB: for em, you need to provide the font size of the element (in pt)

    pxToInt( '12px' )   returns  12 (int)
    pxToInt( '1em'  )   returns  16 (float)
    pxToInt(  15    )   returns  15 (number)

*/
function pxToInt( m, fontSizePt=12 ) {
    // NB : 12Pt == 16px
    if (typeof m === 'string' && m.endsWith('px')) 
        return parseInt(m.slice(0, -2), 10);
    if (typeof m === 'string' && m.endsWith('em')) 
        return Math.round( 10 * 16.0 / 12.0 * fontSizePt * parseFloat(m.slice(0, -2))) / 10.0;
    if (typeof m === "number")  return m;  
    return undefined;
}


/*
    transforms  '"hello"'   into  'hello'
    transforms  'hello'     into  'hello'
*/

function trimQuote( s ) {
    return s.replace(/^"(.+(?="\$))"\$/, '\$1');
}

function trimQuoteArray( _array ) {
    return _array.map( s => trimQuote(s) );
}

/*
        new 'typeof' function, which can handle instances & classes
*/
function toType(obj) {
    if (obj === undefined)
        return 'undefined';
    // .match(/\s([a-zA-Z]+)/)[1].toLowerCase();
    if ( typeof obj == 'function' && ({}).toString.call(obj) == '[object Function]')  
        return 'class '+obj.name;
    if ( typeof obj == 'object' && ({}).toString.call(obj) == '[object Object]') 
        return 'instance '+obj.constructor.name;
    return typeof obj;  
}


/****f* util.js/substrbefore
 * NAME
 *   substrbefore(str, limitStr)
 * FUNCTION
 *   returns the subtring before the limit
 * INPUTS
 *   str : initial string
 *   limitStr : the limit
 * RESULT
 *   a substring if limit is found, the initial str if not
 * SOURCE
     console.log( substrbefore('Hello world !/Coucou', '/') );
******
 */
function substrbefore(str, limitStr) {
    let i = str.indexOf(limitStr);
    if (i == -1)
        return str;
    return str.substring(0, i);
}
function substrafter(str, limitStr) {
    let i = str.indexOf(limitStr);
    if (i == -1)
        return '';
    return str.substring(i + limitStr.length);
}

/****f* util.js/eltGetPos
 * NAME
 *   eltGetPos(elt)
 * FUNCTION
 *   return an object point, with the position
 * INPUTS
 *   elt : the element
 * RESULT
 *   object point  { x: (int),  y: (int) }
 ******
 */
function eltGetPos(elt) {
    let x = elt.style.left;
    if (x == '')
        x = '0';
    let y = elt.style.top;
    if (y == '')
        y = '0';
    return { x: parseInt(x, 10), y: parseInt(y, 10) };
}


/****f* util.js/setEltAbsPos
* NAME
*   setEltAbsPos(elt, pt)
* FUNCTION
*   move the element to an absolute position (in pixels)
* INPUTS
*   elt : the element
*   pt  : position point object { x: (int),  y: (int) }
* RESULT
*   none
******
*/
function setEltAbsPos(elt, pt) {
    let rect = elt.getBoundingClientRect();
    let actu = eltGetPos(elt);
    elt.style.left = (pt.x - rect.left + actu.x) + 'px';
    elt.style.top = (pt.y - rect.top + actu.y) + 'px';
}


/*!
 *   \brief convert text to b64
 *   \param str the input text
 *   \return  a b64 encoded
 *   \sa     b64_to_utf8
 *   \details
 * NOTES
 *   note : unescape & escape are deprecated in JavaScript version 1.5
 *   maybe we have to rewrite them ??
 *   thanks to Johan Sundström ==  https://developer.mozilla.org/fr/docs/D%C3%A9coder_encoder_en_base64
 *   Pour JavaScript, il existe deux fonctions utilisées pour encoder et décoder des chaînes en base64 :
 *     atob()  btoa()
 */
function utf8_to_b64(str) {
    return window.btoa(unescape(encodeURIComponent(str)));
}


/*!
*   \brief convert  b64 to text
*   \param str the input b64 string
*   \return  a txt decoded
*   \sa     utf8_to_b64
*   \details
* NOTES
*   note : unescape & escape are deprecated in JavaScript version 1.5
*   maybe we have to rewrite them ??
*   thanks to Johan Sundström ==  https://developer.mozilla.org/fr/docs/D%C3%A9coder_encoder_en_base64
*   Pour JavaScript, il existe deux fonctions utilisées pour encoder et décoder des chaînes en base64 :
*     atob()  btoa()
*/
function b64_to_utf8(str) {
    return decodeURIComponent(escape(window.atob(str)));
}


EOLONGTEXT);  // mod_js_lib_util   

CModules::include_end(__FILE__);

?>