<?php
        // -------------------
        // div-modal.mod.php
        // -------------------

/*
  =====================================
  ====== some dialog samples ==========
  ====== 
  ====== First element MUST BE <dialog class="dmc_modal"> or <div class="dmc_modal">
  ====== 
  ====== Please note the optionnal
  ====== 
  ======    data-modal="btn_close"
  ======    data-modal="p_text"
  ======    data-title="title_bar"
  ====== 
  =====================================
*/



CModules::include_begin(__FILE__, 'This is division module modal');


CModules::append_onload( __FILE__, 'new DmcModal();' );


/*
  ============================================
  ====== division module : div-modal.mod
  ====== js  class       : DmcModal   <==
  ====== css class       : dmc_modal  
  ============================================
*/


CModules::append( 'mod_js_class_DmcModal', <<<EOLONGTEXT

class DmcModal extends DmcBase {

    static _this = null;
    static inAsync = null;
    static moveWindow = null;
    static interv = null;
    static time = 0;
    static last_dialog_id = null;

    constructor() {
        super();
        this.constructor._this = this;      //        DmcModal._this = this;
    }

    static getContainer(elt) {
        return elt.closest('.dmc_modal');
    }

    static get_root_elt_selector() {
        return 'dialog.dmc_modal, div.dmc_modal';
    }

    static title_mousedown(event) {
        let m = {};
        m.title = event.target;
        m.container = DmcModal.getContainer( event.target );
        m._pt = { x: event.clientX, y: event.clientY };
        m.title.style.background = 'var(--main_color)';
        m.title.onmouseup = DmcModal.title_mouseup;
        m.title.onmousemove = DmcModal.title_mousemove;      
        DmcModal.moveWindow = m;
        return false;
    }

    static title_mouseup(event) {
        let m = DmcModal.moveWindow;
        m.title.onmouseup = undefined;
        m.title.onmousemove = undefined;      
        m.title.style.background = '';
        m.container = undefined;
        m.title._pt = undefined;
        event.preventDefault();      
        DmcModal.moveWindow = undefined;
    }

    static title_mousemove(event) {
        let m = DmcModal.moveWindow;
        if (event.buttons !== 1) {  DmcModal.title_mouseup(event);  return true;  }
        let pt = { x: m._pt.x - event.clientX, y: m._pt.y - event.clientY };
        m._pt = { x: event.clientX, y: event.clientY };
        m.container.style.left = (m.container.offsetLeft - pt.x) + "px";
        m.container.style.top  = (m.container.offsetTop  - pt.y) + "px";
        event.preventDefault();      
        DmcModal.moveWindow = m;
    }

    static _submit_event(event, elt) {

        function getFieldsetType(elt){
            let elts = elt.querySelectorAll('input, select, textarea, fieldset');
            let s='';
            for (let e=0; e<elts.length; e++) {
                let tag = elts[e].tagName, type=undefined;
                if (tag == 'INPUT' && elts[e].type) {
                    tag += '/' + elts[e].type.toUpperCase();
                }
                if (!s.includes(tag)) 
                    s += ( s=='' ? '' : ',' ) + tag;
            }
            return s;
        }

        function getFieldset(form) {
            let fields = {};
            let elts = form.querySelectorAll('input, select, textarea, fieldset');
            for (let e=0; e<elts.length; e++) {
                if (elts[e].parentNode.tagName == 'FIELDSET') continue;
                let fieldType = getFieldsetType(elts[e]);
                if (elts[e].tagName == 'FIELDSET' && 
                        (fieldType == 'INPUT/RADIO' || fieldType == 'INPUT/CHECKBOX') ) {
                    let sub = elts[e].querySelectorAll('input');
                    let s='';
                    for (let j=0; j<sub.length; j++) {
                        if (sub[j].checked)
                            s += ( s=='' ? '' : ',' ) + sub[j].value;
                    }
                    fields[elts[e].name] = s;                    
                } else {
                    fields[elts[e].name] = elts[e].value;                    
                }
            }
            return fields;            
        }


        DmcModal.log('_submit_event');
        let container = DmcModal.getContainer( event.currentTarget );
        let submitter = event.submitter;
        let forms = container.querySelectorAll('form');
        let fields = {};
        for (let f=0; f<forms.length; f++) {
            let n = getFieldset(forms[f]);
            fields = {...fields, ...n};
        }
        container.submitResult = fields;
        if (DmcModal.inAsync) {
            DmcModal.inAsync.submitResult = fields;
            if (submitter)
                DmcModal.inAsync.result = submitter.type.toUpperCase();   
            else if (elt.value)
                DmcModal.inAsync.result = elt.value;   
            else if (elt.tagName == 'BUTTON' && elt.type)
                DmcModal.inAsync.result = elt.type.toUpperCase();   
            else
                DmcModal.inAsync.result = 'null';   
        }

        DmcModal.hide( container.id );        
    }


    static log(t){
        // let s = '';    if (DmcModal.inAsync)  s = ' uniqueId = '+ DmcModal.inAsync.uniqueId;
        // app.log(t+s);
    }

    static _close_event(event, elt) {
        DmcModal.log('_close_event');
        let container = event.target;     
        if (DmcModal.inAsync) {
            if ( DmcModal.inAsync.container != container ) {
                DmcModal.log('DmcModal::_close_event error : container has changed');
                DmcModal.inAsync.container = container;
            }
            DmcModal.inAsync.submitResult = container.submitResult;

            if (DmcModal.inAsync.container){
                DmcModal.inAsync.container.style.display = 'none';
            }

            if (DmcModal.inAsync.resolve)
                DmcModal.inAsync.resolve(DmcModal.inAsync);       

            // ?? DmcModal.inAsync = null;
        }
    }


    static do_cancel( elt ) {
        let container = DmcModal.getContainer( elt );
        if (elt.tagName == 'BUTTON' && elt.type && elt.type.toUpperCase() == 'RESET') {
            DmcModal._submit_event(event, elt);
        }
        DmcModal.hide( container.id, 'cancel' );
    }


    static _setTimeout( container, timeout ) {
        DmcModal.time = Date.now();
        DmcModal.timeout = timeout;

        let elt_btn_close = container.querySelector('[data-modal="btn_close"]');
        elt_btn_close.classList.remove("blink_me");

        if (DmcModal.interv && DmcModal.last_dialog_id && 
            DmcModal.last_dialog_id != container.id)   
            DmcModal.hide( DmcModal.last_dialog_id );

        DmcModal.interv = setInterval( DmcModal.timer, 500, container );
        DmcModal.last_dialog_id = container.id;
    }

    static connect_events( container ) {

        let elt_btn_close = container.querySelector('[data-modal="btn_close"]');
        if (elt_btn_close) {    
            elt_btn_close.onclick = function(ev) {    DmcModal._hide_event(ev, this);     }; 
        }  

        if (!container.onclose)  container.onclose = function(ev) {  
                DmcModal._close_event(ev, this);
            }
            
        if (!container.onclick)  container.onclick = function(ev) {  
                if (ev.target.id != this.id) return;
                DmcModal._hide_event(ev, this);   
            }


        let forms = container.querySelectorAll('form');
        for (let f=0; f<forms.length; f++) {
            if (!forms[f].onsubmit) {
                forms[f].onsubmit =  function(ev) {    DmcModal._submit_event(ev, this);     }; 
            }
        }

        let elt_title = container.querySelector('[data-title="title_bar"]');
        if (elt_title && container.tagName != 'DIALOG') {
            if (!elt_title.onmousedown)    elt_title.onmousedown = DmcModal.title_mousedown;
        }

    }

    static _show( container, timeout ) {

        DmcModal.connect_events( container );
        DmcModal._setTimeout( container, timeout );

        if (DmcModal.inAsync && DmcModal.inAsync.container){
            DmcModal.inAsync.container.style.display = 'none';
            if (DmcModal.inAsync)    DmcModal.log('_show must delete id ' );
            DmcModal.inAsync = null;
        }
        if (DmcModal.inAsync)    DmcModal.log('_show must delete id ' );

        DmcModal.inAsync =  {   uniqueId: createUniqueId()  };
        DmcModal.inAsync.container =  container;
        DmcModal.log('_show');

        if (container.tagName == 'DIALOG') {
            container.style.display = 'block';    
            container.showModal();   
        } else {
            container.style.display = 'block';   
        }

        return container;
    }



    static async showAsync( id_dialog, msg=undefined, timeout = 0 ) {
        let memo = { result:'', data:null };
        if ( DmcModal.inAsync ) {
            console.log('DmcModal::showAsync error : uniqueId exists');
            if (DmcModal.inAsync.container){
                DmcModal.inAsync.container.style.display = 'none';
            }
            if (DmcModal.inAsync)    DmcModal.log('showAsync must delete id ' );
            DmcModal.inAsync = null;
        }

        if (DmcModal.inAsync)    DmcModal.log('showAsync must delete id ' );
        DmcModal.inAsync =  {   uniqueId: createUniqueId()  };
        let container = DmcModal.show( id_dialog, msg, timeout );
        DmcModal.inAsync.container =  container;
        DmcModal.log('showAsync');

        await (new Promise(  function(resolve, reject) {
                DmcModal.inAsync.resolve = resolve;
                DmcModal.inAsync.reject  = reject;
                DmcModal.inAsync.result  = null;
        })).then(
            function(response) {  
                // console.log('response ['+response+']');
                memo.data = DmcModal.inAsync.submitResult;
                memo.result = DmcModal.inAsync.result;
                if (DmcModal.inAsync && DmcModal.inAsync.container){
                    DmcModal.inAsync.container.style.display = 'none';
                }

                if (DmcModal.inAsync)    DmcModal.log('resolve has delete id ' );
                DmcModal.inAsync = null;
            },   
            function(error)    
            {        
                // console.log('error ['+error+']');
                memo.data = DmcModal.inAsync.submitResult;
                memo.result = DmcModal.inAsync.result;
                if (DmcModal.inAsync && DmcModal.inAsync.container){
                    DmcModal.inAsync.container.style.display = 'none';
                }

                if (DmcModal.inAsync)    DmcModal.log('reject has delete id ' );
                DmcModal.inAsync = null;
            }                    
        );

        return memo;
    }


    static show( id_dialog, msg=undefined, timeout = 0) {
        let container  = document.getElementById(id_dialog);
        let elt_modal_text = container.querySelector('[data-modal="p_text"]');
        if (elt_modal_text && msg) {
            elt_modal_text.innerText = msg;
        }            
        return DmcModal._show(container, timeout);
    }

    static showHtml( id_dialog, msg=undefined, timeout = 0) {
        let container  = document.getElementById(id_dialog);
        let elt_modal_text = container.querySelector('[data-modal="p_text"]');
        if (elt_modal_text && msg) {
            elt_modal_text.innerHTML = msg;
        }            
        return DmcModal._show(container, timeout);
    }

    static resetForms( id_dialog ) {
        let container  = document.getElementById(id_dialog);
        let forms = container.querySelectorAll('form');
        for (let f=0; f<forms.length; f++) {
            forms[f].reset();
        }

    }

    static _hide_event( event ) {
        DmcModal.log('_hide_event');
        let container = DmcModal.getContainer(event.target);
        DmcModal.hide( container.id );
    }


    static hide( id_dialog = null, result = null ) {
        if ( !id_dialog )  id_dialog =  DmcModal.last_dialog_id;
        if ( !id_dialog )  return;
        if (DmcModal.isHidden( id_dialog )) return;
        let container  = document.getElementById(id_dialog);
        if (DmcModal.interv) {
            clearInterval(DmcModal.interv);
            DmcModal.interv = null;        

            let elt_btn_close = container.querySelector('[data-modal="btn_close"]');
            if (elt_btn_close) {
                elt_btn_close.classList.remove("blink_me");
            }            
        }

        if (container.tagName == 'DIALOG') {
            container.close(result);   
            container.style.display = 'none';      
        } else {
            container.style.display = 'none';      
        }

        // app.log('hide modal = ' + DmcModal.last_dialog_id);
        DmcModal.last_dialog_id = null;
    }

    static isHidden( id_dialog ) {
        let container  = document.getElementById(id_dialog);
        if (!container) return true;

        // if (container.tagName == 'DIALOG') {
        return container.style.display == 'none';      
    }

    static timer ( container ) {
        if (DmcModal.timeout == 0) return;
        let seconds = (Date.now() - DmcModal.time) / 1000;
        let elt_btn_close = container.querySelector('[data-modal="btn_close"]');

        if (elt_btn_close && seconds>(DmcModal.timeout / 2) && ! elt_btn_close.classList.contains("blink_me")) {
            elt_btn_close.classList.add("blink_me");
        }
        if (seconds > DmcModal.timeout) {
            DmcModal.hide(container.id);
            return;
        }
    }

} // class DmcModal


EOLONGTEXT );  // mod_js_class_DmcModal     



/*
  ============================================
  ====== division module : div-modal.mod
  ====== js  class       : DmcModal 
  ====== css class       : dmc_modal  <== 
  ============================================
*/


CModules::append( 'mod_css_dmc_modal', <<<EOLONGTEXT



/* ================= dm_modal_container ===

    The dmc_modal instances (elements) are written into a hidden div : div.dm_modal_container 

    for example :

    <div class="dm_modal_container">
        <dialog class="dmc_myclass">here</dialog>
    </div>

   ====================================== */

div.dm_modal_container {
}





/* ================= dmc_modal ===

    css for this division module

   ====================================== */

dialog.dmc_modal {
    width: auto; 
    height: auto; 
    background: rgba(0,0,0,.6); 
    border: 0;
}

/*  dialog::backdrop {  background: rgba(0,0,0,.25);  }  */

dialog.dmc_modal p {
    margin: 10px; 
}


dialog.dmc_modal  div.dm_modal_content {
  position: sticky;
  background-color: #fefefe;
  margin: auto;
  padding: 1em;
/*  border: 0.5em solid #888;  */
  border-radius: 1em;
/*  width: fit-content;  */
  max-width: 85%;
  min-width: 30em;
  animation: fade-in;
  /*  animation: modal-animation-dlg .4s ease-out;  */
  /* top property is after (see modal-animation) */
}

dialog.dmc_modal div.dm_modal_content {
   top: 5em;  
}

@keyframes modal-animation-dlg {
  from {top: -5em;}   
  to   {top:  5em;}
}


/* ================================= */
/* ================================= */

div.dmc_modal {
    position: fixed; 
    z-index: 1; 
    border: 0;
    left: -50%;
    top: -50%;
    width: 200%; 
    height: 200%; 
    background: rgba(0,0,0,.6); 
    overflow: auto; 
}


div.dmc_modal  div.dm_modal_content {
  position: sticky;
  background-color: #fefefe;
  margin: auto;
  padding: 1em;
/*  border: 0.5em solid #888;  */
  border-radius: 1em;
  width: fit-content;  
  max-width: 85%;
  min-width: 30em;
  animation: modal-animation .4s ease-out;
  /* top property is after (see modal-animation) */
}

div.dmc_modal p {
    margin: 10px; 
}


div.dmc_modal div.dm_modal_content {
  top: 40%;       
}

@keyframes modal-animation {
  from {top: -40%;}   
  to   {top:  40%;}
}

/* ================================= */
/* ================================= */

div.dm_modal_buttons {
    display: flex;
    justify-content: flex-end;    
}

div.dm_modal_buttons button {
    
}


/* ================================= */
/* ================================= */

.fade-in { 
    animation: fade-in 1s; 
}

@keyframes fade-in {
    0% { opacity: 0; }
  100% { opacity: 1; }
}


.blink_me {
  animation: blinker 1s linear infinite;
}

@keyframes blinker {
  50% {    opacity: 0;  }
}


span.btn_modal_close {
  color: #aaaaaa;
  float: right;
  font-size: 28px;
  font-weight: bold;
}

span.btn_modal_close:hover,
span.btn_modal_close:focus {
  color: #000;
  text-decoration: none;
  cursor: pointer;
}

EOLONGTEXT );  // mod_css_dmc_modal   






CModules::include_end(__FILE__);
?>