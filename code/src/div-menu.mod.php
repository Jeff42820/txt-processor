<?php
        // -------------------
        // div-menu.mod.php
        // -------------------

/*
  =====================================================
  ====== an old style menu for the framework ==========
  ====== 
  ====== First menu level elements are like this :
            <ul id="id_menu" class="dmc_menu">
                <li id="id_mnu_file"><a>File</a>
                </li>
                <li id="id_mnu_edit"><a>Edit</a>
                </li>
            </ul>
  ====== 
  ====== 
  ====== Second menu level elements are like this :
            <ul id="id_menu" class="dmc_menu">
                <li id="id_mnu_file"><a>File</a>
                </li>
                <li id="id_mnu_edit"><a>Edit</a>
                </li>
                <li id="id_mnu_tools"><a>Tools</a>
                  <div class="div_menu">
                    <a>undo</a>
                    <a>copy</a>
                    <a>paste</a>
                  </div>
                </li>

            </ul>
  ====== 
  ====== 
  ====== 
*/



CModules::include_begin(__FILE__, 'This is division module menu');


CModules::append_onload(__FILE__, 'new DmcMenu();');


/*
  ============================================
  ====== division module : div-menu.mod
  ====== js  class       : DmcMenu   <==
  ====== css class       : dmc_menu  
  ============================================
*/


CModules::append( 'mod_js_class_DmcMenu', <<<EOLONGTEXT

class DmcMenu extends DmcBase {

    static _this = null;

    constructor() {
        super();
        this.constructor._this = this;       //        DmcMenu._this = this;
    }

    static onload( parent ){
        super.onload( parent );
        this.connect_events( parent );
    }

    static get_root_elt_selector() {
        return 'ul.dmc_menu';
    }

    static connect_events( parent ) {
        super.connect_events( parent );

        if (!parent)  parent = document.body;
        let elts = parent.querySelectorAll( this.get_root_elt_selector() );
        for (let i=0; i < elts.length; i++) {
            let elt = elts[i];
            if (!elt.onmouseleave)  {
                // elt.onmouseleave = DmcMenu.onmouseleave;
                // NB : ===================
                // this event connection is not good : 
                // in DmcMenu::onmouseleave the 'this' variable will be set to the element ul.dmc_menu
                // it should be the class DmcMenu (static class method)
                // NB : ===================
            }
        }
    }

    static hideMenu( target ) {
        let elt, hightDIV;
        hightDIV = target.closest('div.div_menu');

        /*
        for (elt=target; elt && !elt.classList.contains('dmc_menu'); elt=elt.parentElement ) {
            let tag = elt.tagName;
            if (elt.tagName == 'DIV')
                hightDIV = elt;
        } 
        */

        if (hightDIV) {
            hightDIV.style.visibility = 'hidden';   // only for 200ms !  ;-)
            setTimeout(function(){
                    if (hightDIV) {
                        hightDIV.style.visibility = '';
                        hightDIV = null;
                    }                
                }
                , 200); 
        }

    }

}      // class DmcMenu


EOLONGTEXT );  // mod_js_class_DmcMenu




/*
  ============================================
  ====== division module : div-menu.mod
  ====== js  class       : DmcMenu   
  ====== css class       : dmc_menu   <==    
  ============================================
*/


CModules::append( 'mod_css_dmc_menu', <<<EOLONGTEXT


ul.dmc_menu {
    display: inline-flex;
    flex-direction: row;
    list-style: none;
    color: white;
    background-color: var(--main_color);  
}

ul.dmc_menu li  form   {     
  /*  background-color: #0001;  */
}

ul.dmc_menu li  form  table  { 
    border: 0;  
}

ul.dmc_menu li {   
    padding: 0.4em 1em 0.4em 1em;
    position: relative;
    background-color: var(--main_color);  
    border-radius: 0.4em 0.4em 0 0;
}

ul.dmc_menu li:hover {   
    background-color: var(--main_color_hover);  
}

ul.dmc_menu li a {
    cursor: pointer;
}   

ul.dmc_menu li div.div_menu {   
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

ul.dmc_menu li:hover div.div_menu {   
    display: block;
}

ul.dmc_menu li div.div_menu a {   
    display: block;
    padding: 0.4em;
}

ul.dmc_menu li div.div_menu a:hover {   
    background-color: var(--main_color_hover);  
}

ul.dmc_menu li div.div_menu form:hover {   
    background-color: var(--main_color_hover);  
}

ul.dmc_menu li div.div_menu table td {   
    padding: 0.4em;
}


ul.dmc_menu li div.div_menu ul {   
    display: block;
    list-style: none;
}

ul.dmc_menu li div.div_menu ul {   
    display: block;
    list-style: none;
    padding: 0;
}

ul.dmc_menu li div.div_menu ul li {   
    padding: 0;
}

ul.dmc_menu li div.div_menu ul li div {   
    padding: 0;
}

ul.dmc_menu li div.div_menu ul a {   
    padding: 0.4em;
}

ul.dmc_menu li div.div_menu ul li div {   
    display: none !important;

    position: absolute;
    left: 100%;
    top: 0%; 
    z-index: 1000;
    min-width: max-content;
    padding: 0.2em;
    background-color: var(--main_color);  
}

ul.dmc_menu li div.div_menu ul li:hover div {   
    display: block !important;
}

EOLONGTEXT );  // mod_css_dmc_menu   




CModules::include_end(__FILE__);
?>