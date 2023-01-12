<?php
        // -------------------
        // lib-application-class.mod.php
        // -------------------


CModules::include_begin(__FILE__, 'This is division module application-class (classes Application, DmcBase)');



/*

    BASE CODE FOR THE DIVISION MODULES
    ==================================


    A widget instance is an Element (an html dom element like <div> <span> <button> etc...)


    example         <dialog id="id_dlg_empty" class="dmc_modal fade-in">
                        <p data-modal="p_text"></p>
                    </dialog>

    It is connected to a js class (DmcModal) by an html class (dmc_modal) :

    dialog #id_dlg_empty .dmc_modal

        is connected 
                with the js class       === DmcModal ===
                contained in the file   === div-modal.mod.php ===

        For this division module 'div-modal', the events are connected when you 'Show' the dialogBox
        the connection is done by the static method : DmcModal.connect_events( elt_dialog )

*/



CModules::append( 'mod_jsmain', <<<EOLONGTEXT


    // override console.log
    // ====================
    /*
    console.log = function() {
        this.apply(console, arguments);
    }.bind(console.log);

    console.log = function (...args) {
        let t='console.log=[ ';
        for (const arg of args) {    t += arg + ' ';   }
        t += ']';
        app.log(t);
    };
    */


    // window.onload
    // ====================
    window.onload = async function() {

        app.onload_before_modules();

        {{append_onload}}

        DmcBase.onload_for_all();
        app.onload_after_modules();
        // console.log( DmcBase.toString() );
        console.log('js onload ok');

    } // js window.onload


EOLONGTEXT );  // mod_jsmain





CModules::append( 'mod_js_class_Application', <<<EOLONGTEXT

    // ========= DmcBase ===========
    // =============================


class DmcBase {

    static registeredDmcClasses = {};

    constructor() {
        let cname = this.constructor.name;
        DmcBase.registeredDmcClasses[cname] = this;
        this.elements =  {};
    }

    static get_class( className ) {
        if ( className in this.registeredDmcClasses)
            return this.registeredDmcClasses[className];
        return null;
    }


    static get _inst() {        return this._this;        }

    static toString() {
        let s='List of registeredDmcClasses : '+'\\n';
        for (let className in DmcBase.registeredDmcClasses) {
            s += ' => '+className+'\\n';
            let cl =  DmcBase.registeredDmcClasses[className].constructor;
            for (let eltId in cl.elts) {
                let elt = cl.elts[eltId];
                s += '    - <'+elt.tagName+' id="'+elt.id+'" class="'+elt.className+'">\\n';
            }
        }
        return s;

    }

    static onload_for_all() {
        for (let className in DmcBase.registeredDmcClasses) {
            let cl = DmcBase.registeredDmcClasses[className];
            cl.constructor.onload();
        }
    }

    static onload( parent ) {
        // console.log('in DmcBase.onload() for class='+this.name);

        if (!parent)  parent = document.body;

        for (let className in DmcBase.registeredDmcClasses) {
            let cl = DmcBase.registeredDmcClasses[className].constructor;
            let sel = cl.get_root_elt_selector();
            if (!sel) continue;
            cl.elts = {};

            let elts = parent.querySelectorAll( sel );
            for (let i=0; i < elts.length; i++) {
                let elt = elts[i];
                if (!elt.id)  elt.id = 'id_'+createUniqueId();
                cl.elts[elt.id] = elt;
            }
        }

    }

    static connect_events( parent ) {
        // console.log('in DmcBase.connect_events() for class='+this.name);
    }

    static get_root_elt_selector() {
        return null;
    }

    static addEventListener_(elt, eventName, slotName, details = undefined) {
        let _class = this;
        // if (js_debug && eventName == 'dblclick')    console.log('addEventListener_ '+eventName+' slot='+slotName+' class='+this.name);
        elt.addEventListener(eventName, function(e){return _class.signal(slotName, e, this, details);} );
    }


    static signal( slot, event, this_ev, details=undefined ) {
        if ( typeof this._inst[slot] != 'function' ) {
            app.log('DmcBase::signal error unknown method '+slot);
            return;
        }
        if (details == undefined) details = {};
        return this._inst[slot]( event, this_ev, details );
    }

    static get_all_elements() {
        let arr=[];
        for (let className in DmcBase.registeredDmcClasses) {
            let cl = DmcBase.registeredDmcClasses[className];
            for(let id in cl.elements) {
                let elt = cl.elements[id];
                arr.push(elt);
            }
        }
        return arr;
    }

    static _connect_msignals( widget ) {
        let elts = widget.querySelectorAll('[msignal]');
        for (let i=0; i < elts.length; i++) {
            let elt = elts[i];
            let attr = elt.getAttribute('msignal');
            let attr1 = substrbefore(attr, ',').trim();
            attr = substrafter(attr, ',').trim();
            while (attr1 != '') {
                let eventName = substrbefore(attr1, '››');
                let slotCmd    = substrafter (attr1, '››');
                let slotName   = substrbefore(slotCmd, '(');
                let details = {};
                details.slotName = slotName;
                details.slotParams = substrafter  (slotCmd, '(');
                if (details.slotParams != '') {
                    let l = details.slotParams.length;
                    if (l == details.slotParams.lastIndexOf(')')+1) 
                        details.slotParams = details.slotParams.substring(0, l-1);
                }

                this.addEventListener_(elt, eventName, slotName, details);
                if (attr == '') break;
                attr1 = substrbefore(attr, ',').trim();
                attr = substrafter(attr, ',').trim();
            } // while
        } // for
    }  // _connect_msignals


}  // class DmcBase





    // ========= Application =========
    // ===============================



class Application {

    name='';
    static _this = null;
    static _interv = null;

    constructor() {
        Application._this = this;        
        this.constructor._this = this;      //        Application._this = this;
        this.lastDate =  Date.now();
        this.today = str_fromDate(new Date());
        this.constructor._interv = setInterval( Application._timer, 1000 * 5 );  // each 5sec
    }

    static _timer() {
        app.timer();
    }

    static signal( slot, event, this_ev, details=undefined ) {
        if ( typeof Application._this[slot] != 'function' ) {
            app.log('Application::signal error unknown method '+slot);
            return;
        }
        if (details == undefined) details = {};
        return Application._this[slot]( event, this_ev, details );
    }

    /*
     _____________________________________________________________________________________

     ========= doc =========
     static Application::addEventListener(elt, eventName, slotName, details = undefined) 

     https://developer.mozilla.org/en-US/docs/Web/API/EventTarget/addEventListener

     Please note that while anonymous and arrow functions are similar, they have different this bindings. 
     While anonymous create their own this bindings, arrow functions inherit the this binding of the containing function.
     That means that the variables and constants available to the containing function are also available 
     to the event handler when using an arrow function. 

      _____________________________________________________________________________________  


        document.addEventListener( 'mousemove', (event) =>      { return fct(this, event, 1); }  );
        document.addEventListener( 'mousemove', function(event) { return fct(this, event, 2); }  );

    
        <input type="button" value="Sign in" onclick="((event) => app.log('hi'))()" />
        <input type="button" value="Sign in" onclick="(function(event,this) { app.log('hi'); })()" />
        <input type="button" value="Sign in" onclick="Application.signal('do_login', event, this);" />
        <input type="button" value="Sign in" signal="click››do_login" />
        <input type="button" value="Sign in" onclick="((event) => app.log('hi'))()" />
        <input type="button" value="Sign in" onclick="(function(event,this) { app.log('hi'); })()" />
        <input type="button" value="Sign in" onclick="Application.signal('evtsig_do_login', event, this);" />
        <input type="button" value="Sign in" signal="click››evtsig_do_login" />
    

    */

    static addEventListener_(elt, eventName, slotName, details = undefined) {
        let _class = this;
        elt.addEventListener(eventName, function(e){return _class.signal(slotName, e, this, details);} );
    }

    static connect_signals( parent = undefined ) {
        if (parent == undefined)  parent = document.body;
        let elts = parent.querySelectorAll('[signal]');
        for (let i=0; i < elts.length; i++) {
            let elt = elts[i];
            let attr = elt.getAttribute('signal');
            let attr1 = substrbefore(attr, ',').trim();
            attr = substrafter(attr, ',').trim();
            while (attr1 != '') {
                let eventName = substrbefore(attr1, '››');
                let slotCmd    = substrafter (attr1, '››');
                let slotName   = substrbefore(slotCmd, '(');
                let details = {};
                details.slotName = slotName;
                details.slotParams = substrafter  (slotCmd, '(');
                if (details.slotParams != '') {
                    let l = details.slotParams.length;
                    if (l == details.slotParams.lastIndexOf(')')+1) 
                        details.slotParams = details.slotParams.substring(0, l-1);
                }

                this.addEventListener_(elt, eventName, slotName, details);
                // elt.addEventListener(eventName, function(e){_class.signal(slot, e, this, details);} );
                if (attr == '') break;
                attr1 = substrbefore(attr, ',').trim();
                attr = substrafter(attr, ',').trim();
            }
        }
    }


    /*
        let signal = {
            name:   'item_dblclick',    // for example
            value:   elt.innerText,     // ....
            elt:     elt,
            event:   event,
            details: details,
            target:  details.fieldset
        };
        Application.emit(  signal  );

        it is sent to a widget that has a msignal attribute
    */

    static emit( signal ){
        let attr = signal.target.getAttribute('msignal');
        if (attr === null) {
            app.log("Application::emit error: target elt id="+signal.target.id+" has no attribute 'msignal'");
            return;
        }
        let attr1 = substrbefore(attr, ',').trim();
        attr = substrafter(attr, ',').trim();
        while (attr1 != '') {
            let eventName = substrbefore(attr1, '››');
            if (eventName == signal.type) {
                let slotCmd    = substrafter (attr1, '››');
                let slotName   = substrbefore(slotCmd, '(');
                let slotParams = substrafter  (slotCmd, '(');
                if (slotParams != '') {
                    let l = slotParams.length;
                    if (l == slotParams.lastIndexOf(')')+1) 
                        slotParams = slotParams.substring(0, l-1);
                }                
                signal.method = slotName;
                // Application.modsignal( signal );
                if ( typeof Application._this[signal.method] != 'function' ) {
                    app.log('Application::emit error unknown method '+signal.method);
                } else {
                    Application._this[signal.method]( signal );
                }
            }
            if (attr == '') break;
            attr1 = substrbefore(attr, ',').trim();
            attr = substrafter(attr, ',').trim();
        }
    }



    // trap each style changed for one element
    // (can be used to make onshow/onhide events)
    static observer_onhide( elt ) {
        var observer = new MutationObserver(function(mutations) {      
              mutations.forEach(function(m) {
                const millis =  Math.floor(Date.now());
                console.log('targetID='+m.target.id+' display='+m.target.style.display+' '+millis);
              });
        });
        const config = { attributes: true, attributeFilter: ['style'] }; 
        observer.observe( elt, config );  // to stop : observer.disconnect();
    }



    _getLSCookie( cname ) {
        let c = getLSCookie( this.name + cname );
        if ( c == null ) {
            if ( cname in this.defaultLSCookies ){
                c = this.defaultLSCookies[ cname ];
                setLSCookie( this.name + cname, c );
                return c;
            }
        }
        return c;
    }

    _setLSCookie( cname, cvalue ) {
        return setLSCookie( this.name + cname, cvalue );
    }

    _resetLSCookie() {
        resetLSCookie(app.name);
    }

    _slotLSCookieChanged(event) {
        let input = event.target;
        let attr = input.getAttribute('LSCookie');
        let value = input.value;
        this._setLSCookie(attr, value);
        // app.log('_slotLSCookieChanged '+attr+' = '+value);
    }

    _keyOnMenuInput(event) {
        if (event.key === 'Enter' || event.keyCode === 13) {
            //app.log('evtsig_keyOnMenuInput '+event.type+' '+event.target.tagName);
            event.target.dispatchEvent( new Event("change") );
            let parentTd = event.target.parentElement.parentElement;
            let firstChildSpan = parentTd.firstChild.firstChild;
            event.preventDefault();
            firstChildSpan.dispatchEvent( new Event("click") );
            return false;
        }
    }

    _connectLSCookies() {
        // Connect slots to LSCookie elements
        // ====================================== 
        let elts = document.body.getElementsByTagName('form');      // root balise for LSCookie in menus is form
        for (let i=0; i < elts.length; i++) {
            if (!elts[i].hasAttribute('LSCookie')) continue;
            let form = elts[i];
            const collection = form.getElementsByTagName('*');
            for (let i=0; i<collection.length; i++) {
                let elt = collection[i];
                if (elt.tagName != 'INPUT') continue;

                let attr = elt.getAttribute('LSCookie');
                let value = this._getLSCookie(attr);
                if (value == null) value = '';
                elt.value = value;
                this.constructor.addEventListener_(elt, 'change',  '_slotLSCookieChanged');
                this.constructor.addEventListener_(elt, 'keydown', '_keyOnMenuInput');
            }
        }
    }


    setModalFormValues( id_dialog, p ) {
        let dlg  = document.getElementById(id_dialog);
        let elts = dlg.querySelectorAll('input[name]');
        for (let e=0; e<elts.length; e++) {
            if (!elts[e].name in p) continue;
            let elt = elts[e];
            if (elt.value != p[ elt.name ]) {
                let nvalue = p[ elt.name ];
                elt.value = nvalue;
            }
        }
    }


    dump() {
        this.log( 'today=['+this.today+']' );        
    }

    set_p_value( id, html ) {
        // let html = escapeHtml('Symbols=')+this.symbols()+"<br>\\n";
        let elt = document.getElementById( id );
        /* if (elt == null) {
            this.error('Application::set_p_value error id not found');
            return;
        } */
        elt.innerHTML = html;
    }

    async onload_before_modules() {    // window.onload = async function() 
    }

    async onload_after_modules() {    // window.onload = async function() 
    }

    error( str ){}
    log( str ){}
    popup( str ){}

    post_cmd_request( cmd, args, session_hash, resolve=null, ontimeout=null, timeout=null )
    {

        let form_data = new FormData();
        form_data.append('submit', cmd);
        form_data.append('hash',  session_hash );
        let json_args = JSON.stringify( args );
        form_data.append('args',  json_args );
        // for (let i=0; i<args.length; i++) {    form_data.append('p'+(i+1),  args[i] );  }

        let xhttp = new XMLHttpRequest();
        xhttp.open("POST", "", true);
            xhttp.ontimeout = function (ev) {
                let err = 'Application::post_cmd_request error timeout cmd '+cmd;
                app.log (err);       
                console.log("timeout timeStamp="+ev.timeStamp);
                if (ontimeout !== null) 
                    ontimeout(err);
            };     
            xhttp.onload = async function(ev) {
                let json = null;
                if (xhttp.status == 200) {
                    try {
                        if ( typeof xhttp.response === 'string' )
                            json = JSON.parse(xhttp.response);
                    } catch (error) {
                        app.log( 'Exception '+error+'\\n' );
                        app.log_html( xhttp.response );
                    }
                } else {
                   app.error( 'Application::post_cmd_request error '+cmd+' #'+xhttp.onload+'\\n'); 
                }
                if (resolve !== null) {
                    if (json){
                        json._duration=  Date.now() - xhttp._timeBegin;
                    }
                    resolve(json);
                }
            };
            xhttp.onerror = async function(ev) {
                app.log('Application::post_cmd_request onerror '+xhttp.readyState);
            };
            xhttp.onreadystatechange = function (ev) {
                // console.log("onreadystatechange readyState = "+xhttp.readyState+', timeStamp='+ev.timeStamp);
            };            
        xhttp.timeout = 1000;  // default 
        if (timeout) xhttp.timeout = timeout;
        xhttp._timeBegin =  Date.now();
        xhttp.send(form_data);

    }

    post_cmd( submit_cmd, ...args ) {
        
        let json = new Promise((resolve, reject) => {  
            app.post_cmd_request( submit_cmd, args, this.session_hash,
                (json) => {    resolve(json);      },       // resolve fct
                (err)  => {    reject(err);        },       // timeout fct
                1000  );  // timeout = 1sec
        });
        return json;

    }


    icon( _class, iconName ) {

        // toType( DmcIcons1 ) == 'class DmcIcons1' 

        if ( _class == 'f7-icons' ) 
            return '<i class="'+_class+'">'+iconName+'</i>';  

        if ( _class == 'icons/svg' ) 
            return  '<span class="icon_inline"><img class="icon_inline_scale1" src="icons/'+iconName+'" />'+
                    ' &nbsp; &nbsp; </span> &nbsp; ';

        return '';
    }


} // js class Application


EOLONGTEXT );  // mod_js_class_Application





CModules::include_end(__FILE__);
?>