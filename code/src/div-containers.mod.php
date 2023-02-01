<?php
        // -------------------
        // div-containers.mod.php
        // -------------------


/*
  =====================================
  ====== some containers samples ==========
  ====== 
  ====== 1) dmc_respcardcontainer & dmc_respcard
  ======    this is a responsive card system, using flex
  ====== 
  ====== 2) dmc_flexvert (dmc_flexhori)
  ======    (and dmc_flexvert_child_fix dmc_flexvert_child_expand dmc_flexvert_child_separator )
  ======    this is a vertical positioning of divs
  ======    one has a fixed height, the others will expand to fill up the rest of the space
  ======    the separator can be used to change the height of the child_fix 
  ======
  =====================================
*/



CModules::include_begin( __FILE__ , 'This is division module containers (dmc_flexvert, dmc_respcard)' );


CModules::append_onload( __FILE__, 'new DmcContainers();' );




/*
  ============================================
  ====== division module : div-containers.mod
  ====== js  class       : DmcContainers   <==
  ====== css class       : dmc_containers
  ============================================
*/

CModules::append( 'mod_js_class_Containers', <<<EOLONGTEXT



class DmcContainers extends DmcBase {

    static _this = null;

    constructor() {
        super();
        this.constructor._this = this;  
    }

    static onload( parent ){
        super.onload( parent );
        this.connect_events( parent );
    }

    static connect_events( parent ) {
        super.connect_events( parent );
        if (!parent)  parent = document.body;

        let elts = parent.querySelectorAll('.dmc_flexvert');
        for (let i=0; i < elts.length; i++) {
            let sep = elts[i].querySelector('.dmc_flexvert_child_separator');            
            if (sep) {            
                if (!sep.onmousedown)  {  sep.onmousedown = DmcContainers_globalevents; }
            }
        }
        elts = parent.querySelectorAll('.dmc_flexhori');
        for (let i=0; i < elts.length; i++) {
            let sep = elts[i].querySelector('.dmc_flexhori_child_separator');            
            if (sep) {            
                if (!sep.onmousedown)  {  sep.onmousedown = DmcContainers_globalevents; }
            }
        }
    }


    static get_root_elt_selector() {
        return  'div.dmc_respcardcontainer, div.dmc_respcard, '+
                '.dmc_flexvert, .dmc_flexvert_child_fix, .dmc_flexvert_child_expand' +
                '.dmc_flexhori, .dmc_flexhori_child_fix, .dmc_flexhori_child_expand';
    }


    static onmousedown_sep(elt, event) { 

        let m = {};
        m._elt = elt;
        m._vertical = event.target.classList.contains('dmc_flexvert_child_separator');
        m._parent = elt.parentElement;
        if (m._vertical)
            m._eltFix = elt.parentElement.querySelector('.dmc_flexvert_child_fix');
        else
            m._eltFix = elt.parentElement.querySelector('.dmc_flexhori_child_fix');
        m._pt = { x: event.clientX, y: event.clientY };

        let eltHeight = pxToInt(m._eltFix.style.height);
        if (eltHeight === undefined) {
            let cs = getComputedStyle(m._eltFix);
            eltHeight = pxToInt(cs.height);
            // eltHeight = m._eltFix.offsetHeight - (pxToInt(cs.paddingTop) + pxToInt(cs.paddingBottom)); 
            if (eltHeight === undefined) {
                let clientRect = m._eltFix.getBoundingClientRect();
                eltHeight = clientRect.bottom - clientRect.top;
            }
        }

        let eltWidth = pxToInt(m._eltFix.style.width);
        if (eltWidth === undefined) {
            let cs = getComputedStyle(m._eltFix);
            eltWidth = pxToInt(cs.width);
            // eltWidth = m._eltFix.offsetWidth - (pxToInt(cs.paddingLeft) + pxToInt(cs.paddingRight)); 
            if (eltWidth === undefined) {
                let clientRect = m._eltFix.getBoundingClientRect();
                eltWidth = clientRect.right - clientRect.left;
            }
        }

        m._initialHeight = eltHeight;
        m._initialWidth  = eltWidth;

        DmcContainers.moveWindow = m;
        // app.log('onmousedown_sep elt='+m._eltFix.id+', newHeight='+m._initialHeight);

        document.addEventListener('mousemove', DmcContainers_globalevents );
        document.addEventListener('mouseup',   DmcContainers_globalevents );
        return false;
    }

    static onmouseup_sep(elt, event) { 
        document.removeEventListener('mousemove', DmcContainers_globalevents );
        document.removeEventListener('mouseup',   DmcContainers_globalevents );

        let m = DmcContainers.moveWindow;
        let pt = { x: m._pt.x - event.clientX, y: m._pt.y - event.clientY };

        let signal = {};
        if (m._vertical) {
            let newHeight = (m._initialHeight-pt.y) + 'px';
            m._eltFix.style.height = newHeight;
            signal = {
                type:   'resize_mouseup',
                value:   newHeight,
                elt:     m._parent,
                event:   event,
                details: {},
                target:  m._eltFix, 
                sender:  m._elt
            };
        } else {
            let newWidth = (m._initialWidth-pt.x) + 'px';
            m._eltFix.style.width = newWidth;
            signal = {
                type:   'resize_mouseup',
                value:   newWidth,
                elt:     m._parent,
                event:   event,
                details: {},
                target:  m._eltFix, 
                sender:  m._elt
            };
        }

        Application.emit(  signal  );

        DmcContainers.moveWindow = null;   //     DmcModal
        event.preventDefault();      
        return false;
    }

    static onmousemove_sep(elt, event) { 
        let m = DmcContainers.moveWindow;
        let pt = { x: m._pt.x - event.clientX, y: m._pt.y - event.clientY };


        if (m._vertical) {
            let newHeight = (m._initialHeight-pt.y) + 'px';
            m._eltFix.style.height = newHeight;
        } else {
            let newWidth = (m._initialWidth-pt.x) + 'px';
            m._eltFix.style.width = newWidth;            
        }

        DmcContainers.moveWindow = m;    //     DmcModal
        event.preventDefault();      
        return false;
    }


    static globalevents(elt, event) {
        if (event.type == 'mousedown') { return this.onmousedown_sep(elt, event); }
        if (event.type == 'mouseup')   { return this.onmouseup_sep(elt, event); }
        if (event.type == 'mousemove') {
            if (event.buttons !== 1) { return this.onmouseup_sep(elt, event); }
            return this.onmousemove_sep(elt, event);
        }
        return true;
    }


} // class DmcContainers



function DmcContainers_globalevents(e) {    return DmcContainers.globalevents(this, e);     }


EOLONGTEXT );  // mod_js_class_Containers 





/*
  ============================================
  ====== division module : div-containers.mod
  ====== js  class       : DmcContainers   
  ====== css class       : dmc_containers   <==
  ============================================
*/


CModules::append( 'mod_css_dmc_containers', <<<EOLONGTEXT




/* ============== responsive cards ============ */
/* =========================== */
/* =========================== */
/* =========================== */
/* =========================== */


div.dmc_respcardcontainer {
    display: flex;
    flex-wrap: wrap;
    justify-content: flex-start;
/*    background-color: #eee;  */
    overflow-y:auto;
}


div.dmc_respcard {
    background-color: #e0e0e0;
    min-width: 10em;
    border-radius: 0.8em;
    margin: 0.5em;
    padding: 0.4em;
}


/* ============== vertical flex ============ */
/* =========================== */
/* =========================== */
/* =========================== */
/* =========================== */



.dmc_flexvert {
    display:flex; 
    flex-direction:column; 

}

.dmc_flexvert_child_fix {
    flex:none;
}

.dmc_flexvert_child_expand {
    flex:1;
}

.dmc_flexvert_child_separator {
    flex:none;
    background-color: lightgray;
    min-height: 7px;
    border-radius: 2px;
  /*  border: 2px outset gray;  */
    outline: 0;
    cursor: ns-resize;
}


/* ============== horizontal flex ============ */
/* =========================== */
/* =========================== */
/* =========================== */
/* =========================== */


.dmc_flexhori {
    display:flex; 
    flex-direction:row; 

}

.dmc_flexhori_child_fix {
    flex:none;
}

.dmc_flexhori_child_expand {
    flex:1;
}

.dmc_flexhori_child_separator {
    flex:none;
    background-color: lightgray;
    min-width: 7px;
    border-radius: 2px;
  /*  border: 2px outset gray;  */
    outline: 0;
    cursor: ew-resize;
}



EOLONGTEXT ); // mod_css_dmc_containers     


/*
    =====================================================================

                Example

<div class="dmc_respcardcontainer">

    <div id="id_card1" class="dmc_respcard">
        <p>test</p>
        <p>test</p>
    </div>

</div>


    =====================================================================
    =====================================================================
*/



CModules::include_end( __FILE__ );

?>