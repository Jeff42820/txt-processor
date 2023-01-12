<?php
        // -------------------
        // div-framework.mod.php
        // -------------------


/*
  ===============================================================
  ====== an old style framework (menu bar, status bar) ==========
  ====== 
*/


CModules::include_begin(__FILE__, 'This is division module framework (fw_dialog, fw_menu, fw_main, fw_status)');


CModules::append_onload(__FILE__,  'new DmcFramework();');


/*
  ============================================
  ====== division module : div-framework.mod
  ====== js  class       : DmcFramework   <==
  ====== css class       : 
  ============================================
*/



CModules::append( 'mod_js_class_div_framework', <<<EOLONGTEXT

class DmcFramework extends DmcBase {

    static _this = null;

    constructor() {
        super();
        this.constructor._this = this;          //        DmcFramework._this = this;
        
        let elt_fw_plus = document.getElementById("id_fw_plus");

        // === id_fw_plus / event onclick
        // =========================

        elt_fw_plus.onclick = function(event) {
            DmcFramework._this.status_expanded = !DmcFramework._this.status_expanded;
            let elt_fw_plus =    document.getElementById("id_fw_plus");
            if (elt_fw_plus.innerText == 'x')
                elt_fw_plus.innerText = 'o';
            return false;
        };

        // === id_fw_plus / event onmouseover
        // =========================

        elt_fw_plus.onmouseover = function(event) {
            if (DmcFramework._this.status_expanded) return;

            // ==== switch on in 1 sec
            let timeoutID = setTimeout(function(target) {

                  if (DmcFramework._this.status_expanded) return false;
                  DmcFramework._this.status_expanded = true;

                  // ==== switch off in 5sec
                  let timeoutID = setTimeout(function(target) {
                        if (!DmcFramework._this.status_expanded) return false;
                        DmcFramework._this.status_expanded = false;
                  }, 5000, target);

            }, 1000, event.target);

            return false;
        }

        // === id_fw_textarea set dynamic parameter
        // ===========================

        let elt_fw_textarea = document.getElementById("id_fw_textarea");
        elt_fw_textarea.rows = 10;

        let h = getLSCookie(app.name+'fw_status.expanded');
        if (h != null)  this.status_expanded = h;

        // === hack app.log method
        // ===========================

        app.log = function(str) {
          DmcFramework.log( str );
        };


    } // DmcFramework::constructor


    static get_root_elt_selector() {
        return 'div.dmc_fw_status, div.dmc_fw_main, div.dmc_fw_menu';
    }



    static log (str) {
          let elt_fw_text  = document.getElementById("id_fw_text");
          elt_fw_text.innerText = str;
          let elt  = document.getElementById("id_fw_textarea");
          let ntxt = elt.value;
          ntxt = ntxt + "\\n" + str;
          elt.value = str_taillines( ntxt, elt.rows );

          // uncomment bellow if you want auto-expand
          // DmcFramework._this.status_expanded_tempo();
    };


    // ex:         DmcFramework.setCssRootVar('--main_color', 'blue');
    // 
    static setCssRootVar(id, c) {
      document.documentElement.style.setProperty(id, c);
    }

    static getCssRootVar(id) {
        return document.documentElement.style.getPropertyValue(id);
    }


    status_expanded_tempo() {

        if (this.status_expanded) return false;
        this.status_expanded = true;

        // ==== switch off in 5sec
        this.timeoutID = setTimeout(function(target) {
              if (!DmcFramework._this.status_expanded) return false;
              DmcFramework._this.status_expanded = false;
        }, 5000, null);

    }

    get status_expanded() {
        let elt_fw_status2 = document.getElementById("id_fw_status2");
        return ( elt_fw_status2.style.display != 'none' );
    }

    set status_expanded( exp ) {
        let elt_fw_plus =    document.getElementById("id_fw_plus");
        let elt_fw_status2 = document.getElementById("id_fw_status2");
        let elt_fw_text =    document.getElementById("id_fw_text");

        let statusHeight = DmcFramework.getCssRootVar('--main_status_height'+'_m');        // recover std value
        DmcFramework.setCssRootVar('--main_status_height', exp ? '12em' : statusHeight);
        elt_fw_status2.style.display = exp ? 'flex' : 'none';
        elt_fw_plus.innerText =        exp ? 'x'    : '+';
        elt_fw_text.style.display =    exp ? 'none' : 'inline';

        setLSCookie(app.name+'fw_status.expanded', exp);
    }


    setStatus( str = null ) {
        // app.log( str ? str : 'null' );  return;
        if ( str == null || str == undefined )  str = '';
        let elt_fw_text = document.getElementById('id_fw_text');
        elt_fw_text.innerText = str;
        let exp = this.status_expanded;
        if (str == '') {
            elt_fw_text.style.display =    exp ? 'none' : 'inline';
        } else {
            elt_fw_text.style.display =    exp ? 'inline' : 'inline';
        }
    }


    static _setStatus( event ) {
        // carefull 'this' object is corrupt here
        // (instead of being DmcFramework class, it is the elt from event)
        let elt = event.target;
        let s = (event.type == 'mouseenter') ?  event.target.getAttribute('statusmsg') : null;
        // if (!s) { app.log('s is null');  }
        // app.log(event.type + '=' + elt.tagName + ', id=' + elt.id);
        DmcFramework._this.setStatus( s );
    }

    static connect_statusmsg( parent ) {
        // Connect slot setStatus to elements
        // ====================================== 
        // let elts = document.body.getElementsByTagName('svg');
        let elts = parent.querySelectorAll('[statusmsg]');

        for (let i=0; i < elts.length; i++) {
            // if (!elts[i].hasAttribute('statusmsg')) continue;
            let elt = elts[i];
            if (!elt.onmouseenter)   // onmouseover onmouseenter  // onmouseout onmouseleave
                elt.onmouseenter = DmcFramework._setStatus;
            if (!elt.onmouseleave)  
                elt.onmouseleave = DmcFramework._setStatus;
            /*
            elt.onmouseenter = function(ev){ app.log('onmouseenter=' + elt.tagName + ', id=' + elt.id);  DmcFramework._setStatus(ev);  }
            elt.onmouseleave = function(ev){ DmcFramework._setStatus(ev);    app.log('onmouseleave=' + elt.tagName + ', id=' + elt.id);  }
            */
        }
    }

}  //  class DmcFramework


EOLONGTEXT );  // mod_js_class_div_framework     



CModules::append( 'mod_html_fw_status', <<<EOLONGTEXT
    <div id="id_fw_status" class="dmc_fw_status">
        <p><span id="id_fw_plus">&plus;</span><span id="id_fw_text"></span></p>
        <div id="id_fw_status2" class="div_fw_status2" style="display:none;">
            <textarea id="id_fw_textarea"></textarea>
        </div>
    </div>
EOLONGTEXT );  // mod_html_fw_status
//     CModules::$_this->mod_echo_html('div-framework.mod.php', 'mod_html_fw_status');



CModules::append( 'mod_html_div_fw_main', <<<EOLONGTEXT
<div id="id_fw_main" class="dmc_fw_main">
<!--content-->
<div class="div_fw_status_magic"></div>
</div>
EOLONGTEXT );  // mod_html_div_fw_main
//     CModules::$_this->mod_echo_html('div-framework.mod.php', 'mod_html_div_fw_main');


CModules::append( 'mod_html_div_fw_dialog', <<<EOLONGTEXT

<!-- =====================================================  -->
<!-- CModules::append( 'mod_html_div_fw_dialog' )    begin  -->
<!-- =====================================================  -->

<!--content-->

<!-- =====================================================  -->
<!-- CModules::append( 'mod_html_div_fw_dialog' )    end  -->
<!-- =====================================================  -->

EOLONGTEXT );  // mod_html_div_fw_dialog
//     CModules::$_this->mod_echo_html('div-framework.mod.php', 'mod_html_div_fw_dialog');

CModules::append( 'mod_html_div_fw_menu', <<<EOLONGTEXT
<div id="id_fw_menu" class="dmc_fw_menu">
<!--content-->
</div>
EOLONGTEXT );  // mod_html_div_fw_menu
//     CModules::$_this->mod_echo_html('div-framework.mod.php', 'mod_html_div_fw_menu');





CModules::append( 'mod_css_dmc_framework', <<<EOLONGTEXT

:root{
  /*  _m is for memory : used to recall initial default value  */
  --main_status_height:   1.4em;
  --main_status_height_m: 1.4em;
}

body {
    padding: 0;
    margin: 0;
    height:     calc( 100% -  var(--main_status_height) - 0px );
}

div.dmc_fw_menu {
  /* background-color: #00c0c0; */
  display: flex;
  justify-content: space-between;
  margin: 0em;
  padding: 0em;
}

div.dmc_fw_main {
  /* background-color: #f5f5f5; */
  display: flex;
  flex-direction: column;
  flex: 1;
  margin: 0 0 0 0;
  padding: 0;
}

div.div_fw_status_magic{
    height:   var(--main_status_height);
    background-color: #00000000;
}

div.dmc_fw_status {
  display: flex;  
  flex-direction: column;
  position: fixed;
  background-color: #c0c0c0;
  margin: 0em;
  padding: 0em;
  height:     var(--main_status_height);
  bottom: 0;
  width: 100%;
}

div.dmc_fw_status p {
    display: flex;
    height: 1.2em;
    padding: 0.2em 0.2em 0.2em 0.2em;
}


div.dmc_fw_status span {
  background-color: #c0c0c0;
  flex: 1;
}

div.dmc_fw_status span:first-child {
  background: rgba(0,0,0,0.5);
  border-radius: 0.5em;
  flex: 0;
  min-width:  1.0em;
  min-height: 1.0em;
  text-align: center;
}

div.dmc_fw_status span:not(:first-child) {
    padding-left: 0.3em;
}


div.div_fw_status2 {
  display: flex; 
  background-color: white;
  margin: 0em;
  padding: 0em;
}

div.div_fw_status2 textarea {
  display: block;
  background: #c0c0c0;
  padding: 0;
  margin: 0;
  width: 100%;
  border: 0px solid grey;
  overflow: auto;
  resize: vertical;
}


EOLONGTEXT );  // mod_css_dmc_framework   





CModules::include_end(__FILE__);
?>