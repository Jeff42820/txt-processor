<?php
        // -------------------
        // prj-wgtlogin.mod.php
        // -------------------


CModules::include_begin( __FILE__ , 'prj css_wgtlogin' );



CModules::append( 'mod_html_div_wgtlogin', <<<EOLONGTEXT



    <dialog id="id_dlg_login" mwgtclass="WgtLogin" class="dmc_modal fade-in" style="display:none;">
      <div id="id_dlg_content" class="dm_modal_content">
        <span class="btn_modal_close" data-modal="btn_close">&times;</span>
          <p id="id_login_title" data-modal="p_text" data-title="title_bar">Login</p>
          <form id="id_form_login" method="dialog" 
                style="display:flex; justify-content:center; flex-direction:row;">
            <table style="background-color:lightgrey">
            <tr><td><label for="for_username">username :</label></td>
                <td><input type="text" id="username" name="for_login" value="" 
                    size="16" minlength="1" required /></td>
            </tr>
            <tr><td><label for="for_password">password :</label></td>
                <td><input type="password" id="password" name="for_password" value="" 
                    size="16" minlength="1" required /></td>
                <td><img class="icon_inline_scale1" src="icons/eye.svg" width="18" height="18" 
                    style="transform:scale(1.2);" msignal="click››evtsig_do_showpsw"  />
                </td>
            </tr>
            <tr><td></td>
                <td  style="text-align:right;">
                <input type="button" id="id_dld_login_do" value="Sign in" msignal="click››evtsig_do_login" /></td>
            </tr>


            <tr><td colspan="100%" style="background-color:#fffd;">&nbsp;</td>
            </tr>

            <tr><td>
                New user ?<br>
                <input type="button" id="id_dld_login_do" value="Create account" msignal="click››evtsig_do_create" />
            </td>
            </tr>

            </table>
          </form><!-- "id_form_login" -->
          <p id="id_login_result"></p>


          <form id="id_form_logout" method="dialog" style="display:flex; justify-content:center;">
            <table  style="background-color:lightgrey">
            <tr><td><label >username :</label></td>
                <td><input type="text" id="id_dlg_username" name="for_login" value="" 
                        size="16" minlength="1" readonly /></td>
            </tr>
            <tr><td></td>
                <td  style="text-align:right;">
                <input type="button" id="id_dld_login_do" value="Logout" msignal="click››evtsig_logout" /></td>
            </tr>
            </table>
          </form><!-- "id_form_logout" -->



          <form id="id_form_createaccount" method="dialog" style="display:flex; justify-content:center;">
            <table>
            <tr><td><label for="for_username">username :</label></td>
                <td><input type="text" id="username" name="for_login" value="" 
                    size="16" minlength="1" required /></td>
            </tr>
            <tr><td><label for="for_password1">password :</label></td>
                <td><input type="password" id="password1" name="for_password1" value="" 
                    size="16" minlength="1" required /></td>
                <td><img class="icon_inline_scale1" src="icons/eye.svg" width="18" height="18" 
                    style="transform:scale(1.2);" msignal="click››evtsig_do_showpsw1"  />
                </td>
            </tr>
            <tr><td><label for="for_password2">confirm :</label></td>
                <td><input type="password" id="password2" name="for_password2" value="" 
                    size="16" minlength="1" required /></td>
            </tr>
            <tr><td><label for="for_random">suggestion :</label></td>
                <td><input type="text" id="id_random" name="for_random" value="" 
                    size="16" minlength="1" required /></td>
                <td><img class="icon_inline_scale1" src="icons/refresh.svg" width="16" height="16" 
                    style="transform:scale(1.0);" msignal="click››evtsig_do_refresh_suggest"  />
                </td>
                <td><img class="icon_inline_scale1" src="icons/copy.svg" width="16" height="16" 
                    style="transform:scale(1.0);" msignal="click››evtsig_do_refresh_copy"  />
                </td>
            </tr>
            <tr><td></td>
                <td  style="text-align:right;">
                <input type="button" id="id_dld_do_create" value="Create" msignal="click››evtsig_do_create" /></td>
            </tr>
            </table>
          </form><!-- "id_form_createaccount" -->


      </div>
    </dialog>  <!-- id_dlg_login  -->


EOLONGTEXT );  // mod_html_div_wgtlogin


CModules::append( 'mod_js_class_WgtLogin', <<<EOLONGTEXT





class WgtLogin extends DmcBase {

    static _this = null;

    constructor() {
        super();
        this.constructor._this = this;
        this.icon_user='<span class="icon_inline"><img class="icon_inline_scale1" src="icons/user.svg" /> &nbsp; &nbsp; </span> &nbsp; ';
    }


    static onload ( parent ){
        super.onload( parent );
        this.connect_events( parent );
        this.do_login_onload();
    } 

    static async do_login_onload() {
        let lastuser  = app._getLSCookie('LSC_username');
        let watermark = app._getLSCookie('LSC_watermark');
        let wm_time   = app._getLSCookie('LSC_wm_time'); 
        let link = document.getElementById("id_a_login");

        //let wdgt_class =  DmcBase.get_class( 'WgtLogin' );
        //let icon_user = (wdgt_class) ? wdgt_class.icon_user : '◊◊◊◊ ';
        let icon_user =  WgtLogin._this.icon_user;
        if (lastuser && watermark && watermark!='') {
            let json =  await app.post_cmd( 'post_check_watermark', { user:lastuser, 
                    watermark:watermark, wm_time:wm_time } );
            if (json && json.return) {
                app.log(json.msg + ' age='+ json.age+'s'+ ', post_cmd duration='+json._duration+'ms');
                link.innerHTML = icon_user + escapeHtml( lastuser );
            } else {
                if (json)  app.log(json.msg);
                app._setLSCookie('LSC_watermark', undefined);
                app._setLSCookie('LSC_wm_time',   0);
                link.innerHTML = icon_user + 'Login';
            }
        } else {
            link.innerHTML = icon_user + 'Login';
        }
    }


    static connect_events( parent ) {
        super.connect_events( parent );

        if (parent == undefined)  parent = document.body;
        let elts = parent.querySelectorAll('[mwgtclass]');
        for (let i=0; i < elts.length; i++) {
            let wgtClass = elts[i].getAttribute('mwgtclass');
            this._connect_msignals( elts[i] );
        }  
    }

    evtsig_do_showpsw() {
        let elt_password =  document.getElementById("password");
        if (elt_password.type == 'password')
            elt_password.type = 'text';
        else
            elt_password.type = 'password';
    }

    evtsig_do_showpsw1() {
        let elt_password1 =  document.getElementById("password1");
        let elt_password2 =  document.getElementById("password2");
        if (elt_password1.type == 'password') {
            elt_password1.type = 'text';
            elt_password2.type = 'text';
        }
        else {
            elt_password1.type = 'password';
            elt_password2.type = 'password';
        }
    }

    evtsig_do_refresh_suggest( event, elt ) {
        let elt_random = document.getElementById("id_random");
        if (elt_random)
            elt_random.value = cryptoUniquePassword(12);
    }

    evtsig_do_refresh_copy( event, elt ) {
        let elt_random = document.getElementById("id_random");
        elt_random.select();
        document.execCommand("copy");  
    }

    async evtsig_do_login( event, elt ) {
        let elt_username = document.getElementById("username");
        let elt_password = document.getElementById("password");
        let dlg = DmcModal.getContainer(elt_username);

        let lastuser = elt_username.value;
        if (lastuser != '') {
            let elt_result = document.getElementById("id_login_result");
            let json =  await app.post_cmd( 'post_login', { user:lastuser, password:elt_password.value } );

            if (json && json.watermark != ''){
                app.log( JSON.stringify( json ) );
                elt_result.innerText = 'Login successfull !';  // \\n watermark='+json.watermark
                app._setLSCookie('LSC_username',  lastuser);
                app._setLSCookie('LSC_watermark', json.watermark );   // cryptoUniquePassword()
                app._setLSCookie('LSC_wm_time',   json.wm_time ); 

                DmcModal.resetForms( dlg.id );
                elt_username.value = lastuser;
                let link = document.getElementById("id_a_login");
                link.innerHTML = this.icon_user +   escapeHtml( lastuser );
                DmcModal._setTimeout( dlg, 2 );
            }
            else {
                if (!json){
                    app.log( 'json is null' );
                    elt_result.innerText = 'Login error';
                } else {
                    elt_result.innerText = json.msg;
                }
            }
        }

        // app.log('evtsig_do_login user='+lastuser);        
    }

    evtsig_do_create() {
        document.getElementById("id_login_title").innerText = "Create an account";
        let id_form_login  = document.getElementById("id_form_login");
        let id_form_logout = document.getElementById("id_form_logout");
        let id_form_create = document.getElementById("id_form_createaccount");

        id_form_login.style.display = 'none';
        id_form_logout.style.display = 'none';
        id_form_create.style.display = 'flex';
        
        /*
        let lastuser =  document.getElementById("username").value;
        let password =  document.getElementById("password").value;
        let random   = document.getElementById("id_random");

        app._getLSCookie('LSC_username');
        app._getLSCookie('LSC_watermark');
        */
    }

    async evtsig_logout( event, elt ) {
        let elt_username = document.getElementById("username");
        let lastuser = elt_username.value;

        let dlg = DmcModal.getContainer(elt_username);
        app._setLSCookie('LSC_watermark', undefined );

        DmcModal.resetForms( dlg.id );
        DmcModal.resetForms( dlg.id );
        elt_username.value = '';
        DmcModal._setTimeout( dlg, 2 );  // DmcModal.hide( dlg.id, null );


        let link = document.getElementById("id_a_login");
        // let lastuser = link.innerText.trim();
        link.innerHTML = this.icon_user + 'Login';

        let json =  await app.post_cmd( 'post_logout', { user:lastuser } );
        app.log('evtsig_logout user='+lastuser);        
    }



    /*
=    evtsig_do_showpsw  
=    evtsig_do_showpsw1  
=    evtsig_do_refresh_suggest  
=    evtsig_do_refresh_copy  
=    evtsig_do_login 
=    evtsig_do_create 
=    evtsig_logout 
    */


    static showDialog(elt) {

        let elt_result = document.getElementById("id_login_result");
        elt_result.innerText = '';

        let lastuser = app._getLSCookie('LSC_username');
        let watermark = app._getLSCookie('LSC_watermark');

        document.getElementById("id_dlg_username").value = lastuser; 
        let id_form_login  = document.getElementById("id_form_login");
        let id_form_logout = document.getElementById("id_form_logout");
        let id_form_create = document.getElementById("id_form_createaccount");

        if (lastuser && watermark) {
            document.getElementById("id_login_title").innerText = "Logout";
            id_form_login.style.display = 'none';
            id_form_logout.style.display = 'flex';     
            id_form_create.style.display = 'none';
        } else {
            document.getElementById("id_login_title").innerText = "Login";
            document.getElementById("password").type == 'password';
            id_form_login.style.display = 'flex';
            id_form_logout.style.display = 'none';
            id_form_create.style.display = 'none';
            let elt_random = document.getElementById("id_random");
            if (elt_random)
                elt_random.value = '';  // cryptoUniquePassword(12);
        }

        DmcModal.show( "id_dlg_login" ); 
        if (lastuser) {
            let elt_username = document.getElementById("username");
            elt_username.value = lastuser;
        }
    }



} // class WgtLogin



EOLONGTEXT );  // mod_js_class_WgtLogin 



CModules::append_onload( __FILE__, 'new WgtLogin();' );


CModules::append( 'mod_css_div_wgtlogin', <<<EOLONGTEXT



/* ================================== 
   =
   =
   =       css for WgtLogin
   =
   =
   =
   ================================== */

:root{
}


/* ============== WgtLogin ============ */
/* =========================== */
/* =========================== */
/* =========================== */
/* =========================== */





EOLONGTEXT ); // mod_css_div_wgtlogin     



CModules::include_end( __FILE__ );

?>