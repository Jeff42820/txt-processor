<?php
        // -------------------
        // div-contextmenu.mod.php
        // -------------------


/*
  =====================================
  ====== a context menu ==========
  ====== 
            the signal 'slot_fill_contextmenu' is sent by DmcContextmenu with these parameters :
            details = {
                sender:     
                value:          
                elt_target:     
                elt_origin:     
                elt_container:  
            }
            return true if you want context menu to appear
  ====== 
  ====== 
        msignal="item_contextmenu››msig_lsbx_processes_cntxtmnu"  
  ====== 
  ====== 
    Application::msig_lsbx_processes_cntxtmnu( signal ) {      
        DmcContextmenu.showContext( signal, { line: signal.elt } );  // this will send signal to 'slot_fill_contextmenu'
        event.preventDefault();
        return false;
    }
  ====== 
  ====== 
  =====================================
*/



CModules::include_begin( __FILE__ , 'This is division module contextmenu' );

CModules::append_onload( __FILE__, 'new DmcContextmenu();' );


/*
  ============================================
  ====== division module : div-contextmenu.mod
  ====== js  class       : DmcContextmenu   
  ====== css class       : dmc_contextmenu   
  ====== html elts       : dmc_contextmenu_container   <==
  ============================================
*/


CModules::append( 'mod_html_div_contextmenu', <<<EOLONGTEXT

<div id="id_contextmenu" class="dmc_contextmenu_container"  style="display: none;"  tabindex="0">
    <div id="id_contextmenu_inside"  class="contextmenu_inside">
    </div>
</div>

EOLONGTEXT );  // mod_html_div_contextmenu






/*
  ============================================
  ====== division module : div-contextmenu.mod
  ====== js  class       : DmcContextmenu             <==
  ====== css class       : dmc_contextmenu  
  ====== html elts       : dmc_contextmenu_container  
  ============================================
*/


CModules::append( 'mod_js_class_DmcContextmenu', <<<EOLONGTEXT


class DmcContextmenu extends DmcBase {

    static _this = null;
    static _onhide = {};

    constructor() {
        super();
        this.constructor._this = this;          //        DmcContextmenu._this = this;
        this.elt_contextmenu = document.getElementById( 'id_contextmenu' );
        this.elt_contextmenu_inside = document.getElementById( 'id_contextmenu_inside' );
    }


    static onload( parent ){
        super.onload( parent );
        // let elt = document.getElementById( 'id_contextmenu' );
        DmcContextmenu._onhide = {   millis : Math.floor(Date.now()),  element : DmcContextmenu._this.elt_contextmenu     };
        DmcContextmenu.observer_onhide( document.getElementById( 'id_contextmenu' ) );
        DmcContextmenu.addEventListener_( DmcContextmenu._this.elt_contextmenu, 'focusout', 'slot_context_focusout',  {} );
    }


    static get_root_elt_selector() {
        return  '.dmc_contextmenu_container';
    }
    

    fill ( str ) {
        this.elt_contextmenu_inside.innerText = str;        
    }

    fillHTML ( str ) {
        this.elt_contextmenu_inside.innerHTML = str;  
        Application.connect_signals( this.elt_contextmenu_inside );
    }

    static onhide( elt, from='', delay=999 ) {
        DmcContextmenu._this.details = null;

        // debug stuff
        if (false){
            const millis =  Math.floor(Date.now());
            console.log('DmcContextmenu::onhide targetID='+elt.id+' hide '+millis+' from='+from+' delay='+delay);            
        }
    }


    static _mut_onhide( elt, from ) {
            let time  = Math.floor(Date.now());
            let delay  = time -  DmcContextmenu._onhide.millis;
            // 5 millisec : the same event, do not send two times
            if ( delay > 5 )  {
                DmcContextmenu.onhide( DmcContextmenu._onhide.element, from, delay );
            }
            DmcContextmenu._onhide = {   millis : time,  element : elt     };
    }


    static hide() {
        DmcContextmenu._this.elt_contextmenu.style.display = 'none';
        DmcContextmenu._mut_onhide( DmcContextmenu._this.elt_contextmenu, 'hide' );   // only if mutation didnt goes ok
    }

    static show() {
        DmcContextmenu._this.elt_contextmenu.style.display = 'block';
    }


    static getDetails( ) {    return DmcContextmenu._this.details;    }

    static showContext( signal, details ) {    return DmcContextmenu._this._showContext( signal, details );    }

    _showContext( signal, d ) {
        this.coord = {'x':event.clientX, 'y':event.clientY };
        this.elt_origin =  signal.event.target; 
        this.details = d;

        /* this.previousFocusId = null;
        if (document.activeElement) 
            this.previousFocusId = document.activeElement.id; 
        */

        let details = {
            sender:         this,
            value:          signal.value,
            elt_target:     this.elt_contextmenu_inside, 
            elt_inside:     this.elt_contextmenu, 
            elt_origin:     this.elt_origin,
            elt_container:  signal.target
        };
        let r = Application.signal( 'slot_fill_contextmenu', signal.event, this.elt_contextmenu, details );

        if (r === true) {
            let invisible_border = 10;
            let offset = { x:15, y:20 };
            DmcContextmenu.show();         //    this.elt_contextmenu.style.display = 'block';
            setEltAbsPos( this.elt_contextmenu, { 'x':this.coord.x-invisible_border-offset.x, 'y':this.coord.y-invisible_border-offset.y }  ); 
            this.elt_contextmenu.focus();            
        }
    }


    slot_context_focusout( event, elt, details ) {
        let newFocus  = event.relatedTarget;  
        let oldFocus  = event.target;       
        if (newFocus == null)  newFocus = document.activeElement;

        if ( this.elt_contextmenu.contains(newFocus) )    return;

        // function isAncestorOf(parentElt, childElt) {    return parentElt.contains(childElt);    }
        // if ( isAncestorOf( this.elt_contextmenu, newFocus ) )     return;

        DmcContextmenu.hide();
        return false;
    }

    // trap each style changed for one element
    // (can be used to make onshow/onhide events)
    static observer_onhide( elt ) {
        var observer = new MutationObserver(function(mutations) { 
              let elt = null; 
              mutations.forEach(function(m) {
                if (elt != m.target) {
                    let new_show = m.target.style.display != 'none';
                    if (!new_show) DmcContextmenu._mut_onhide(m.target, 'from mut');
                    elt=m.target;
                }
              });
        });
        const config = { attributes: true, attributeFilter: ['style'] }; 
        observer.observe( elt, config );  // to stop : observer.disconnect();
    }


} // class DmcContextmenu


EOLONGTEXT );  // mod_js_class_DmcContextmenu 





/*
  ============================================
  ====== division module : div-contextmenu.mod
  ====== js  class       : DmcContextmenu   
  ====== css class       : dmc_contextmenu           <==
  ====== html elts       : dmc_contextmenu_container  
  ============================================
*/



CModules::append( 'mod_css_dmc_contextmenu', <<<EOLONGTEXT



/* ============== context menus ============ */
/* =========================== */
/* =========================== */
/* =========================== */
/* =========================== */

:root{
  /*  _m is for memory : used to recall initial value  */

    --ctxmenu_invisible_u: 1px;  
    --ctxmenu_invisible_border    : calc(10 * var(--ctxmenu_invisible_u));
    --ctxmenu_invisible_border_m  : calc(10 * var(--ctxmenu_invisible_u));

    --contextmenu_padding: var(--ctxmenu_invisible_border);
    --contextmenu_outline: calc(-10 * var(--ctxmenu_invisible_u));
}


.dmc_contextmenu_container {
   display:block;
   position:absolute;
   top:0;
   left:0;
   padding-top:     var(--contextmenu_padding);
   padding-right:   var(--contextmenu_padding);
   padding-bottom:  var(--contextmenu_padding);
   padding-left:    var(--contextmenu_padding);
   border:0;
   border-radius: 0.6em;
   z-index:1070;  /* bootstrap zindex-dropdown == 1000 */
   float:left;
   background-color: #0000;
}


div.dmc_contextmenu_container:focus-visible  {  
    outline: 2px solid #DC143C80;
    outline-offset:  var(--contextmenu_outline);
    border-radius: 0.9em;
}


.contextmenu_inside {
    min-width:  2.4em;
    min-height: 1.2em;
}

.contextmenu_button {
    outline: 0 none;  
    width: 100%;
    text-align: inherit;
}


/* =========================== */
/* =========================== */
/* =========================== */
/* =========================== */



ul.dmc_contextmenu {
    display: inline-flex;
    flex-direction: row;
    list-style: none;
    color: white;
    background-color: var(--main_color);  
}

ul.dmc_contextmenu li {   
    padding: 0.4em 1em 0.4em 1em;
    position: relative;
    background-color: var(--main_color);  
    border-radius: 0.2em;
}

ul.dmc_contextmenu li:hover {   
    background-color: var(--main_color_hover);  
}

ul.dmc_contextmenu li.ul_ctxmenu_title {
    background-color: #333;      
}   

ul.dmc_contextmenu li a {
    cursor: pointer;
}   

ul.dmc_contextmenu li div.div_menu {   
    display: none;

    position: absolute;
    left: 0%;
    top: 100%; 
    z-index: 1000;
    min-width: max-content;
    background-color: var(--main_color);  
    padding: 0.2em;
    border: 0.3em solid var(--main_color_hover);  
    border-radius: 0 0.8em;
}

ul.dmc_contextmenu li:hover div.div_menu {   
    display: block;
}

ul.dmc_contextmenu li div.div_menu a {   
    display: block;
    padding: 0.4em;
}

ul.dmc_contextmenu li div.div_menu a:hover {   
    background-color: var(--main_color_hover);  
}

ul.dmc_contextmenu li div.div_menu form:hover {   
    background-color: var(--main_color_hover);  
}

ul.dmc_contextmenu li div.div_menu table td {   
    padding: 0.4em;
}


ul.dmc_contextmenu li div.div_menu ul {   
    display: block;
    list-style: none;
}

ul.dmc_contextmenu li div.div_menu ul {   
    display: block;
    list-style: none;
    padding: 0;
}

ul.dmc_contextmenu li div.div_menu ul li {   
    padding: 0;
}

ul.dmc_contextmenu li div.div_menu ul li div {   
    padding: 0;
}

ul.dmc_contextmenu li div.div_menu ul a {   
    padding: 0.4em;
}

ul.dmc_contextmenu li div.div_menu ul li div {   
    display: none !important;

    position: absolute;
    left: 100%;
    top: 0%; 
    z-index: 1000;
    min-width: max-content;
    padding: 0.2em;
    background-color: var(--main_color);  
}

ul.dmc_contextmenu li div.div_menu ul li:hover div {   
    display: block !important;
}

.rounded_ctxmenu {
    border-radius: 0.2em;
    padding: 0.2em;
    margin: 0em;  
}



EOLONGTEXT ); // mod_css_dmc_contextmenu     



CModules::include_end( __FILE__ );

?>