/*!
 *   \file util2.ts
 *   \brief fw / util2.ts Some JS usefull functions
 *   \details
 *   \author JF Lemay
 *   \version 0.1
 *   \date  2021-01-01
 */
/*!
 *   \brief remove all lines except the n last lines
 *   \details
 *   \param   str (string) the input lines
 *   \param   n (optional) the number of lines to keep (actually fixed at 10)
 *   \return  the last lines kept (in a string)
*/
function keepLastTenLines(str, n = 10) {
    let m = str.match(/(?:[\n\r][^\n\r]*){10}$/);
    if (m === null)
        return str;
    return m[0];
}
/*!
 *   \brief   show message into the log zone (maybe the TextArea called '_logarea1')
 *   \param   str (string) the message
*/
function __log(str) {
    const area = document.getElementById('app•' + '_logarea1');
    if (area === null) {
        console.log(str);
        return;
    }
    let txt = keepLastTenLines(area.value);
    area.value = (txt + str);
    area.setSelectionRange(area.value.length, area.value.length);
}
/*!
 *   \brief find position of an element  NB: bug if used in a TABLE with a CAPTION
 *   \details
 *   \param   elt     the element
 *   \param   parent  the return value refers to this parent element
 *   \return  { left:(int), top:(int) }
*/
function findPos(elt, parent) {
    if (elt.offsetParent == null)
        return { left: 0, top: 0 };
    let curleft = 0, curtop = 0;
    do {
        curleft += elt.offsetLeft; // - elt.scrollLeft;
        curtop += elt.offsetTop; // - elt.scrollTop;
        elt = elt.offsetParent;
    } while (elt != null && elt != parent);
    return { left: curleft, top: curtop };
}
/*!
 *   \brief find position of an element, relative to another
 *   \details
 *   \param   elt     the element
 *   \param   destElt  - element that will be moved with
 *              destElt.left=res.left+'px';
 *              destElt.top=res.top+'px';
 *   \return  { left:(int), top:(int) }
*/
function findPosRel(elt, destElt) {
    let memLeft = destElt.style.left;
    let memTop = destElt.style.top;
    // let isHidden = (destElt.position != 'fixed' && destElt.offsetParent === null);
    destElt.style.left = '0';
    destElt.style.top = '0';
    let offDest = destElt.getBoundingClientRect(); // getOffset( destElt );
    let offElt = elt.getBoundingClientRect(); // getOffset( elt );
    destElt.style.left = memLeft;
    destElt.style.top = memTop;
    return { left: (offElt.left - offDest.left), top: (offElt.top - offDest.top) };
}
/*!
 *   \brief encode URI the string
 *
 *   \code{.js}
  //  javascript : encodeURI(uri)
  //  php :  rawurlencode()
  // ' ' > %20 or +  // "   > %22 or "
  // <   > %3C or <  // [   >     or [
  // >   > %3E or >  // ]   >     or ]
  // +   > %2B       // :   > %3A
  // /   > %2F       // ?   > %3F
  // %   > %25       // #   > %23
  // @   > %40
 *   \endcode
 *   \return  string
 *   \sa unescapeHtml  htmlspecialchars
 */
function escapeHtml(s) {
    var MAP = {
        '&': '&amp;', '<': '&lt;', '>': '&gt;',
        '"': '&#34;', "'": '&#39;' //    "&quot;"
    };
    if (s == null)
        return '';
    /// \cond show_reg
    return s.replace(/[&<>"']/g, function (c) { return MAP[c]; });
    /// \endcond     
}
/*!
 *   \brief decode URI the string
 *   \code{.js}
  //  javascript : encodeURI(uri)
  //  php :  rawurlencode()
  // ' ' > %20 or +  // "   > %22 or "
  // <   > %3C or <  // [   >     or [
  // >   > %3E or >  // ]   >     or ]
  // +   > %2B       // :   > %3A
  // /   > %2F       // ?   > %3F
  // %   > %25       // #   > %23
  // @   > %40
 *   \endcode
 *   \return  string
 *   \sa escapeHtml
 */
function unescapeHtml(s) {
    // '&amp;'   : '&',    '&lt;'   : '<', '  &gt;' : '>',   
    //  '&#34;'  : '"',    '&#39;'  : "'",   '<br>' : "\n":    // .replace(/<br>/g, "\n")
    /// \cond show_reg
    s = s.replace(/&amp;/g, "&").replace(/&lt;/g, "<").replace(/&gt;/g, ">");
    s = s.replace(/&#34;/g, '"').replace(/&#39;/g, "'");
    /// \endcond 
    return s;
}
/*!
 *   \brief find the parent object (ex type parent of an INPUT is FORM)
 *   \param elt the child object
 *   \param type a string, the type of the parent (ex : FORM TABLE...)
 *   \return  element or null
 */
function findParent(elt, type) {
    var i = 0;
    do {
        if (elt == null)
            return null;
        if (elt.nodeName == type)
            return elt;
        elt = elt.parentElement;
        i++;
    } while (i < 100); // useful ?
    return null;
}
/*!
*   \brief find the child object with a specific type
*   \param elt the parent object
*   \param type : a string (ex INPUT, THEAD, TABLE...)
*   \return  element or null
*/
function findChild(elt, type) {
    let i;
    for (i = 0; i < elt.children.length; i++) {
        let child = elt.children[i];
        if (child.nodeName == type)
            return child;
    }
    for (i = 0; i < elt.children.length; i++) {
        let child = elt.children[i];
        let c = findChild(child, type);
        if (c != null)
            return c;
    }
    return null;
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
/****f* util.js/getHtmlFromElt
 * NAME
 *   getHtmlFromElt(elt)
 * FUNCTION
 *   get html code of an element (without inside html)
 * INPUTS
 *   elt (element)
 * RESULT
 *   html code
 ******
 */
function getHtmlFromElt(elt) {
    let tag = elt.outerHTML;
    if (elt.innerHTML.length > 0) {
        let pos = elt.outerHTML.indexOf(elt.innerHTML);
        tag = elt.outerHTML.slice(0, pos);
        tag += "\n" + elt.outerHTML.slice(pos + elt.innerHTML.length);
    }
    return tag; //  .replace(/\n/g, '<br>');
}
/****f* util.js/fwEncodeURI
 * NAME
 *   fwEncodeURI(uri)
 * FUNCTION
 *   encode URI to be able to send specialChars
 * INPUTS
 *   uri
 * RESULT
 *   acceptable uri
 ******
 */
function fwEncodeURI(v) {
    let MAP = {
        ' ': '%20', '"': '%22', '<': '%3C', '>': '%3E',
        '+': '%2B', ':': '%3A', '/': '%2F', '?': '%3F',
        '%': '%25', '#': '%23', '@': '%40'
    };
    let s = String(v);
    /// \cond show_reg
    return s.replace(/[ "<>+:\/?%#@]/g, function (c) { return MAP[c]; });
    /// \endcond     
}
/****f* util.js/setLSCookie
 * NAME
 *   setLSCookie(cname, cvalue)
 * FUNCTION
 *   stock Large cookie
 * INPUTS
 *   cname : text, cvalue : can be number, boolean, string
 * RESULT
 *   none
 ******
 */
function setLSCookie(cname, cvalue) {
    let typ = typeof cvalue;
    if (typ == 'number') {
        cvalue = '(float)' + cvalue;
    }
    if (typ == 'boolean') {
        cvalue = '(boolean)' + (cvalue ? 'true' : 'false');
    }
    localStorage[cname] = cvalue;
}
/****f* util.js/getLSCookie
* NAME
*   getLSCookie(cname)
* FUNCTION
*   get Large cookie
* INPUTS
*   cname : text
* RESULT
*   cvalue : can be number (float), boolean, string
******
*/
function getLSCookie(cname) {
    let r = localStorage[cname];
    if (r === undefined)
        return undefined;
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
var global_var = [];
function setGlobalVar(varName, varValue, varType) {
    global_var[varName] = { value: varValue, type: varType };
}
function getGlobalVar(varName) {
    let v = global_var[varName];
    return v.value;
}
/****f* util.js/setCookie
 * NAME
 *   setCookie(cname, cvalue, [exdays])
 * FUNCTION
 *   set sameSite cookie
 * INPUTS
 *   cname : text
 *   cvalue : float, boolean, string
 *   exdays : number
 * RESULT
 *   none
 ******
 */
function setCookie(cname, cvalue, exdays = undefined) {
    let jsp = php_main_js();
    if (jsp === undefined)
        jsp = '';
    let typ = typeof cvalue;
    if (typ == 'number') {
        cvalue = '(float)' + cvalue;
    }
    if (typ == 'boolean') {
        cvalue = '(boolean)' + (cvalue ? 'true' : 'false');
    }
    let size = cvalue.length;
    if (size > 4000) {
        console.log("error setCookie() : cvalue length sup to max");
        cvalue = '';
    }
    if (exdays == undefined)
        exdays = 360;
    let expires = "";
    let url = php_script_name();
    url = url.substring(0, url.lastIndexOf('/'));
    let path = "path=" + url;
    if (jsp != '')
        path += '/' + encodeURIComponent(jsp);
    let d = new Date();
    d.setTime(d.getTime() + (exdays * 24 * 60 * 60 * 1000));
    expires = "expires=" + d.toUTCString();
    // let d = new Date();      let minutes = 30;      d.setTime(d.getTime() + (minutes * 60 * 1000));
    cvalue = encodeURIComponent(cvalue); // cvalue.replace(/;/g, "&#x3b;")
    // 'httponly=TRUE'  
    // This option has nothing to do with JavaScript, but we have to mention it for completeness.
    // The web-server uses Set-Cookie header to set a cookie. And it may set the httpOnly option.
    // This option forbids any JavaScript access to the cookie. We can’t see such cookie or manipulate it using document.cookie.
    let confid = 'sameSite=Strict;'; // your website needs a very secure environment
    // let confid = 'sameSite=Lax;';             // a social community website 
    // let confid = 'sameSite=None;Secure;';     // your website offers retargeting, advertising  
    // NB : in http sites, sameSite=None won't do the job 
    if (typeof php_https === "function") {
        if (!php_https())
            confid = 'sameSite=Lax;';
    }
    let str = cname + "=" + cvalue + ";" + expires + ";" + path + ";" + confid; // + ";" + 'Secure'
    document.cookie = str;
    // $options = array ('expires' => time () + 6800, 'path' => '/', 'secure' => FALSE, 'httponly' => TRUE, 'samesite' => 'Strict');
    // SetCookie ('cookie', 'cookie valide pour Firefox', $options);
    // $.cookie("example", "foo", { expires: d });
}
/****f* util.js/deleteCookie
 * NAME
 *   deleteCookie(cname)
 * FUNCTION
 *   delete sameSite cookie
 * INPUTS
 *   cname : text
 * RESULT
 *   none
 ******
 */
function deleteCookie(cname) {
    setCookie(cname, '', -3600);
}
/****f* util.js/getCookie
 * NAME
 *   getCookie(cname)
 * FUNCTION
 *   get sameSite cookie
 * INPUTS
 *   cname : text
 * RESULT
 *   can be boolean, number, or string
 *   if error, returns undefined
 ******
 */
function getCookie(cname) {
    // let jsp = php_main_js();    if (jsp === undefined)  jsp='';
    let name = cname + "=";
    let ca = decodeURIComponent(document.cookie).split(';');
    for (let i = 0; i < ca.length; i++) {
        let c = ca[i];
        // while (c.charAt(0) == ' ') {  c = c.substring(1);  }
        /// \cond show_reg
        c = c.replace(/^\s+/g, '');
        /// \endcond 
        if (c.indexOf(name) == 0) {
            let r = c.substring(name.length, c.length);
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
    }
    return undefined;
}
/****f* util.js/secToString
 * NAME
 *   secToString(sec)
 * FUNCTION
 *   convert (int) second to (string) mn:sec
 * INPUTS
 *   sec : integer
 * RESULT
 *   string : seconds to string  0:01  (mn:sec)
 ******
 */
function secToString(sec) {
    let n_h = Math.floor(sec / 3600.0);
    let n_mn = Math.floor((sec - 3600.0 * n_h) / 60.0);
    let n_s = Math.floor((sec - 3600.0 * n_h) - (60.0 * n_mn));
    let s_mn = ((n_mn >= 10) ? '' : '0') + String(n_mn);
    let s_s = ((n_s >= 10) ? '' : '0') + String(n_s);
    return String(n_h) + ':' + s_mn + ':' + s_s;
}
/****f* util.js/msToSeconds
 * NAME
 *   msToSeconds(ms)
 * FUNCTION
 *   convert (int) milliseconds to (rounded number) seconds
 * INPUTS
 *   ms : integer
 * RESULT
 *   number : seconds
 ******
 */
function msToSeconds(delay) {
    return Math.round((delay + 300) / 1000.0);
}
/****f* util.js/secondsToDhms
 * NAME
 *   secondsToDhms(sec)
 * FUNCTION
 *   convert (int or float) seconds to (string) days, hours, mn, sec
 * INPUTS
 *   sec : integer
 * RESULT
 *   string
 ******
 */
function secondsToDhms(seconds) {
    seconds = Number(seconds);
    let d = Math.floor(seconds / (3600 * 24));
    let h = Math.floor(seconds % (3600 * 24) / 3600);
    let m = Math.floor(seconds % 3600 / 60);
    let s = Math.floor(seconds % 60);
    let dDisplay = d > 0 ? d + (d == 1 ? " day, " : " days, ") : "";
    let hDisplay = h > 0 ? h + (h == 1 ? " hour, " : " hours, ") : "";
    let mDisplay = m > 0 ? m + (m == 1 ? " minute, " : " minutes, ") : "";
    let sDisplay = s > 0 ? s + (s == 1 ? " second" : " seconds") : "";
    return dDisplay + hDisplay + mDisplay + sDisplay;
}
/****f* util.js/sleep
 * NAME
 *   sleep(ms)
 * FUNCTION
 *   wait, doing nothing
 * INPUTS
 *   ms : duration
 * RESULT
 *   a Promise
 * SOURCE
      async function test() {
        console.log("Hello");
        await sleep(2000);
        console.log("World!");
      }
 ******
 */
function sleep(ms) {
    return new Promise(resolve => setTimeout(resolve, ms));
}
/****f* util.js/getRandomInt
 * NAME
 *   getRandomInt(max)
 * FUNCTION
 *   find a random integer
 * INPUTS
 *   max : integer
 * RESULT
 *   a number >=0 and <max
 * SOURCE
   console.log(getRandomInt(3));
   // expected output: 0, 1 or 2
 ******
 */
function getRandomInt(max) {
    return Math.floor(Math.random() * Math.floor(max));
}
/****f* util.js/setCharAt
 * NAME
 *   setCharAt(str,index,chr)
 * FUNCTION
 *   set a char (in a string, at specific pos) to a specific value
 * INPUTS
 *   str : the string to modify
 *   index : the position of the char
 *   chr : new value for the char
 * RESULT
 *   new string
 *   (note : if pos is not in the string, return the same string)
 ******
 */
function setCharAt(str, index, chr) {
    if (index > str.length - 1)
        return str;
    return str.substr(0, index) + chr + str.substr(index + 1);
}
/****f* util.js/getStyleRuleValue
 * NAME
 *   getStyleRuleValue(str,index,chr)
 * FUNCTION
 *   function that traverses the stylesheets on the document
 *   looking for the matched selector, then style.
 * INPUTS
 *   style :
 *   selector :
 *   sheet :
 * RESULT
 *   the style
 ******
 */
function getStyleRuleValue(style, selector, sheet = undefined) {
    let sheets = typeof sheet !== 'undefined' ? [sheet] : document.styleSheets;
    for (let i = 0, l = sheets.length; i < l; i++) {
        let sheet = sheets[i];
        if (!sheet.cssRules) {
            continue;
        }
        for (let j = 0, k = sheet.cssRules.length; j < k; j++) {
            let _rule = sheet.cssRules[j];
            if (!(_rule instanceof CSSStyleRule)) {
                continue;
            }
            let rule = _rule;
            if (rule.selectorText && rule.selectorText.split(',').indexOf(selector) !== -1) {
                return rule.style[style];
            }
        }
    }
    return null;
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
/****f* util.js/listAllCssProperties
 * NAME
 *   listAllCssProperties(fileName, className)
 * FUNCTION
 *   create a string text with the properties
 * INPUTS
 *   fileName : the file css name
 *   className  : string the class
 * RESULT
 *   a string (multiline : \n)
 * SOURCE
     s = listAllCssProperties('default.css', ':root');
 ******
 */
function listAllCssProperties(fileName, className) {
    let s = '';
    let rr = Array.from(document.styleSheets).filter(sheet => sheet.href.startsWith(window.location.origin) && sheet.href.endsWith('/' + fileName));
    if (rr.length == 0)
        return s;
    let classes = rr[0].rules || rr[0].cssRules;
    for (let x = 0; x < classes.length; x++) {
        let _rule = classes[x];
        if (!(_rule instanceof CSSStyleRule)) {
            continue;
        }
        let rule = _rule;
        if (rule.selectorText == className) {
            for (let y in rule.style) {
                let propName = rule.style[y];
                let propVal = rule.style.getPropertyValue(propName);
                s += propName + ' = ' + propVal + "\n";
            }
        }
    }
    return s;
}
class cTest {
}
/****f* util.js/inheritsClass
 * NAME
 *   inheritsClass( classA, classB )
 * FUNCTION
 *   test if a class is extended from another
 * INPUTS
 *   classA : class to test (child)
 *   classB : may be parent class
 * RESULT
 *   boolean
 * SOURCE
    class A {}
    class B extends A {}
    console.log( inheritsClass(B, A) ); // true
******
 */
function inheritsClass(classA, classB) {
    if (classB === classA)
        return true;
    return classA.prototype instanceof classB;
}
function test_type(v, strTyp) {
    if (strTyp === undefined) {
        if (v === undefined)
            return;
        console.log('error test_type ' + v + ' should be undefined ');
        return;
    }
    if (v === undefined) {
        console.log('error test_type undefined (not expected...) ');
        return;
    }
    if (typeof v == 'object' && strTyp.prototype !== undefined) {
        let vClass = v.constructor.name;
        if (vClass == '')
            vClass = typeof v;
        let r = inheritsClass(v.constructor, strTyp);
        if (!r)
            console.log('error test_type ' + vClass + ' inheritsClass : ' + r);
        return;
    }
}
/****f* util.js/createArray
 * NAME
 *   createArray(length, fillw)
 * FUNCTION
 *   create an array filled with the value fillw
 * INPUTS
 *   length : array size
 *   fillw  : each item will be set with this value
 * RESULT
 *   an array [0..length-1]
 ******
 */
function createArray(length, fillw) {
    // let arr = [];    for(let i=0;i<length;i++)  {    arr.push(fillw);    }    return arr;
    return Array(length).fill(fillw);
}

function getDateFromString(str) {
    var re = /([12][0-9][0-9][0-9])(-)?([01][0-9])?(-?)([0123][0-9])?/;
    let d = { day: undefined, month: undefined, year: undefined };
    // d.year  = undefined;  d.month = undefined;  d.day   = undefined;
    var found = str.match(re);
    // expected output: > Array ["2017-03-17", "2017", "-", "03", "-", "17"]
    if (found.length == 6) {
        if (found[1] !== undefined)
            d.year = parseInt(found[1], 10);
        if (found[3] !== undefined)
            d.month = parseInt(found[3], 10);
        if (found[5] !== undefined)
            d.day = parseInt(found[5], 10);
    }
    if (d.day && (d.day < 1 || d.day > 31))
        d.day = null;
    if (d.month && (d.month < 1 || d.month > 12))
        d.month = null;
    if (d.year && (d.year < -6000 || d.year > 3000))
        d.year = null;
    return d;
}
function colorToString(c) {
    c.r = Math.round(c.r);
    c.v = Math.round(c.v);
    c.b = Math.round(c.b);
    if (c.r > 255)
        c.r = 255;
    if (c.r < 0)
        c.r = 0;
    if (c.v > 255)
        c.v = 255;
    if (c.v < 0)
        c.v = 0;
    if (c.b > 255)
        c.b = 255;
    if (c.b < 0)
        c.b = 0;
    let c_r, c_v, c_b;
    c_r = c.r.toString(16);
    if (c_r.length == 1)
        c_r = '0' + c.r;
    c_v = c.v.toString(16);
    if (c_v.length == 1)
        c_v = '0' + c.v;
    c_b = c.b.toString(16);
    if (c_b.length == 1)
        c_b = '0' + c.b;
    return '#' + c.r + c.v + c.b;
}
function changeLight(c, v) {
    let r;
    r.r = c.r * v;
    if (r.r > 255)
        r.r = 255;
    r.v = c.v * v;
    if (r.v > 255)
        r.v = 255;
    r.b = c.b * v;
    if (r.b > 255)
        r.b = 255;
    return r;
}
function getColorFromString(str) {
    var re = /(\#)([0-9a-f][0-9a-f])([0-9a-f][0-9a-f])([0-9a-f][0-9a-f])?/;
    let d = { r: undefined, v: undefined, b: undefined };
    var found = str.match(re);
    // expected output: > Array ["2017-03-17", "2017", "-", "03", "-", "17"]
    if (found.length == 5) {
        if (found[2] !== undefined)
            d.r = parseInt(found[2], 16);
        if (found[3] !== undefined)
            d.v = parseInt(found[3], 16);
        if (found[4] !== undefined)
            d.b = parseInt(found[4], 16);
    }
    return d;
}
