<?php
        // -------------------
        // cmainphp.php
        // -------------------

// ===============================
// ==
// ==  class CMainPhp (base for CPrjMain)
// ==
// ==
// ===============================


class CMainPhp {                    // base class for CPrjMain
    
    public  string $user='';
    public  string $session_hash='';
    public  string $session_id = '';
    public  string $client_ip = '';
    public  string $server_ip = '';
    public  string $server_name = '';
    public  string $path_root = '';
    public  string $path_src = '';
    public  string $script_name = '';
    public  string $date_start = '';
    protected  $passdb = null;

    public function __construct() {
        $this->date_start = date("Y-m-d_H:i:s");
        $_SESSION['news']  = [];
        $_SESSION['hour'] = date("Y-m-d_H:i");
        if ( !isset($_SESSION['wm_tic']) )   $_SESSION['wm_tic'] = 0;

        if ( isset($_SERVER['PHP_AUTH_USER']) ) 
            $this->user=$_SERVER['PHP_AUTH_USER'];
        // echo "DefTimezone = ".date_default_timezone_get(), $CR;
        date_default_timezone_set('Europe/Paris');
        $this->client_ip = getClientIpAddr();
        $this->server_ip = $_SERVER['SERVER_ADDR'];
        $this->server_name = $_SERVER['SERVER_NAME'];

        $this->path_root = $_SERVER['DOCUMENT_ROOT'];
        if ( str_starts_with($_SERVER['SCRIPT_FILENAME'], $_SERVER['DOCUMENT_ROOT']) ) {
            $this->path_src = substr($_SERVER['SCRIPT_FILENAME'], strlen($_SERVER['DOCUMENT_ROOT']));
        }
        $i =  strrpos($this->path_src, '/');
        if ($i !== false) {
            $this->script_name = substr($this->path_src, $i+1);
            $this->path_src = substr($this->path_src, 0, $i);
        }
        else
            $this->script_name = $this->path_src;
    }

    function dump() {
        global $CR;
        echo "user = [".$this->user."]", $CR;
        echo "session_id = ".$this->session_id, $CR;
        echo "YourIp = ".$this->client_ip, $CR;
        echo "Server = ".$this->server_name." (".$this->server_ip.")", $CR;
        echo "path_root = ".$this->path_root, $CR;
        echo "path_src = ".$this->path_src, $CR;
        echo "Script = ".$this->script_name, $CR;        
        echo "Start = ".$this->date_start, $CR;        
    }

    function header() {
        header('Content-Type: text/html; charset=UTF-8');
    }

    function head() {
        echo "<!DOCTYPE html>\n", "<html>\n";
        echo "<head>\n";
        // CModules::$_this->mod_echo_comment();
        CModules::$_this->mod_echo_head();
        echo "</head>\n\n\n";        
    }

    function body_begin() {
        echo "<body>\n";
        CModules::$_this->mod_echo_body();
    }

    function echo_p( $id, $msg = '' ) {
        $_id = '';
        if ($id != '')
            $_id = ' id="'.htmlspecialchars($id).'"';
        echo '<p'.$_id.'>'.htmlspecialchars($msg).'</p>', "\n";        
    }    

    function body_end() {
        echo "</body>\n";
        echo "</html>";
    }


    function init_session() {       
        $cookieParams = session_get_cookie_params();
        $cookieParams['samesite'] = 'Strict'; // None, Lax or Strict.
        $cookieParams['httponly'] = true;
        $cookieParams['secure'] = false; // $_SERVER['HTTPS'] ? true : false;
        $cookieParams['domain'] = $_SERVER['HTTP_HOST'];
        $cookieParams['path'] =  '/';
        session_set_cookie_params($cookieParams);
        // ini_set('session.use_strict_mode', 1);
        session_start();    
        $this->session_id = session_id();
        $this->session_hash = hash('sha256', $this->session_id.$this->user);
    }

    function send_phpMsg_to_jsMsg() { 
        echo "<script>\n";
        echo "app.session_hash=".str_php2js( $this->session_hash ).";\n";
        // echo "console.log('js init ok');\n";
        echo "</script>\n";
    }


    function eval_maxsize() {
        $upl_maxsize;

        $upl_maxsize = 999999999;
        $m = array();
        if ( preg_match('/([0-9]+)(M)/', ini_get("memory_limit"), $m) ) {
            $upl_maxsize = min ( $upl_maxsize, (int) $m[1] );
        }
        if ( preg_match('/([0-9]+)(M)/', ini_get("upload_max_filesize"), $m) ) {
            $upl_maxsize = min ( $upl_maxsize, (int) $m[1] );
        }
        if ( preg_match('/([0-9]+)(M)/', ini_get("post_max_size"), $m) ) {
            $upl_maxsize = min ( $upl_maxsize, (int) $m[1] );
        }

        $upl_maxsize = (int) ( $upl_maxsize * 0.8 );
        return $upl_maxsize;
    }


    function security_post_submit_value( $submit ) {

        if ( strlen($submit) > 40 )  return false;
        if (preg_match('/[^a-zA-Z0-9\_]/', $submit)) return false;
        return true; // security check passed : ok !

    }

    function read_post_data() {
        if ( !isset($_POST['submit']) || !isset($_POST['hash']) )  return;

        if ( $_POST['hash'] !=  $this->session_hash )  {
            header('Content-Type: text/plain; charset=UTF-8');
            $data = ['msg' => "CMainPhp::read_post_data error : session_hash"];
            echo json_encode($data, JSON_FORCE_OBJECT);
            exit(0);
        }

        $_POST['hash'] = '{-hidden-}';
        if (   !isset($_POST['submit']) 
            || !str_starts_with($_POST['submit'], 'post_') 
            || !$this->security_post_submit_value($_POST['submit'])      ) {
            header('Content-Type: text/plain; charset=UTF-8');
            $submit = $_POST['submit'];
            $data = ['msg' => "CMainPhp::read_post_data error :  bad \$_POST['${submit}']"];
            echo json_encode($data, JSON_FORCE_OBJECT);
            exit(0);
        }

        $method_name = $_POST['submit'];
        if ( !is_callable([$this, $_POST['submit']]) ) {
            header('Content-Type: text/plain; charset=UTF-8');
            $data = ['msg' => "CMainPhp::read_post_data error : \$_POST['submit']='${method_name}' is not callable"];
            echo json_encode($data, JSON_FORCE_OBJECT);
            exit(0);
        }

        $data = [];
        $args = [];
        if (isset($_POST['args']))   $args = json_decode( $_POST['args'] );   // base64_decode($_POST['args'])
        header('Content-Type: text/plain; charset=UTF-8');
        try {
            $this->$method_name( $data, $args );
        } catch (Exception $e){
            $data['error'] = 'CMainPhp::read_post_data error : Exception '.$e->getMessage();
        }
        echo json_encode($data, JSON_FORCE_OBJECT);
        exit(0);
    }


    /*

    password_hash
        PASSWORD_DEFAULT - Use the bcrypt algorithm (default as of PHP 5.5.0). 
        Note that this constant is designed to change over time as new and stronger algorithms are added to PHP. 
        For that reason, the length of the result from using this identifier can change over time. 
        Therefore, it is recommended to store the result in a database column that can expand beyond 60 characters (255 characters would be a good choice). 

    */


    function calcWaterMark( $user, $password, $time ) {     // length of watermark : choose 255 byte for security
        /*
        $watermark =  $user."{$time}".$password.$this->server_name;
        $watermark =  substr(md5($watermark), 2, 10);
        return $watermark;
        */
        $sentence =  $user."{$time}".$password.$this->server_name;
        $watermark = password_hash($sentence, PASSWORD_DEFAULT);
        return $watermark;
    }



    function checkWaterMark( $user, $password, $time, $wm ) {
        $sentence =  $user."{$time}".$password.$this->server_name;
        $check = password_verify($sentence, $wm);
        return $check;
    }



    function get_userdb( & $ob ) {

        if ($this->passdb !== null) {
            $ob = $this->passdb;
            return;
        };

        $ob =  [];
        try {
            $filePath = '../.passwords/passwords.txt';
            $separator = '»';
            $sepLen = strlen($separator);
            $handle = fopen($filePath, "r");
            if ($handle) {
                while (($line = fgets($handle)) !== false) {
                    $line = str_replace( ["\n", "\r"], ' ', $line);
                    $pos = strpos($line, $separator);
                    if ($pos !== false) {
                        $user =  trim(substr($line, 0, $pos));
                        $pass =  trim(substr($line, $pos+$sepLen));
                        // $ps .= '['.$user.'] ['.$pass.']'."\n";
                        $ob[$user] = $pass;       // $ob =  []; 
                        //$ob->{$user} = $pass;   // $ob =  (object) array(); 
                    }
                }
                fclose($handle);   $handle = false;
            }
        } catch (Exception $e){
            $data['error'] = 'CMainPhp::post_test error : Exception '.$e->getMessage();
        }
        $this->passdb = $ob;

    }

// ===============================
// ==
// ==  post_cmd from js
// ==
// ==
// ===============================


    function post_Upload_XMLHttp( & $data, $args ) {

        if ( !is_array($_FILES) ) { 
            $data = ['msg' => "CMainPhp::post_Upload_XMLHttp error : bad \$_FILES", 
                     'post' => $_POST ];
            return;
        }

        $data = [
                 'post'   => $_POST,
                 'files'  => print_r( $_FILES['file_obj'], true ),
                 'msg'    => ""
                ];

        $r = file_get_contents( $_FILES['file_obj']['tmp_name'] );
        // $r = iconv("UTF-8", "ISO-8859-15", $r);

        if ($r === false) {
            $data['msg'] = "CMainPhp::post_Upload_XMLHttp error : file_get_contents(tmp_name)";
            return;
        }

        $data['file'] = base64_encode($r);
        $data['msg'] = "CMainPhp::post_Upload_XMLHttp ok";
    }


    /*
        ====================================
        ====================================
        login variables usefull
        ====================================
        ====================================

    login
    ---------
        $_SESSION['user']       $data['user']
        $_SESSION['wm_time']    $data['wm_time']        // check password ok at this moment
                                $data['watermark']      // calc & sent when password is ok

    logout
    ---------
        $_SESSION['user']       = '';
        $_SESSION['wm_time']    = null;

    tic often check watermark
    ---------
        $_SESSION['tic']  = $time;
        $_SESSION['wm_tic']  = $time;                   // when tic check ok the watermark
                                
    check_watermark
    ---------
        if (watermark is ok)
            $data['age']      = $time - $args[0]->wm_time;
            $data['tic_age']  = $time - $_SESSION['wm_tic'];

        ====================================
        ====================================
    */



    function post_check_watermark( & $data, $args ) {

        $this->get_userdb( $ob );
        $data['wm_time']    = $args[0]->wm_time; 
        $data['watermark']  = $args[0]->watermark; 
        $data['user']       = $args[0]->user; 
        $data['return']     = false;
        usleep(300 * 1000);        // sleep 300ms env

        if (!array_key_exists( $args[0]->user, $ob ))  {
            $data['msg']        = "CMainPhp::post_check_watermark error: unknown user";
            return; 
        }

        $password = $ob[ $args[0]->user ];

        $t0 = time();
        $check   = $this->checkWaterMark( $args[0]->user, $password, $args[0]->wm_time, $args[0]->watermark );
        $data['_wmcost'] = time() - $t0;
        if ( !$check )  {
            $data['msg']        = "CMainPhp::post_check_watermark error: bad watermark";
            return;
        }

        $time = time();
        $data['age']      = $time - $args[0]->wm_time;

        // Undefined index: wm_tic 
        if (in_array('wm_tic', $_SESSION )) {
            $data['tic_age']  = $time - $_SESSION['wm_tic'];
        } else {
            $data['tic_age']  = 0;            
        }
        
        /*  ======== never too old
        if ($data['age']     > 12 * 60*60 ||        // 60sec * 60mn *12 = 12h
            $data['tic_age'] >      60*5      ) {   // 60sec * 5mn = 5mn
            $data['msg']  = "CMainPhp::post_check_watermark too old";
            return;
        } */

        $data['msg']        = "CMainPhp::post_check_watermark success";
        $data['return']     = true;
    }



    function post_login( & $data, $args ) {

        $this->get_userdb( $ob );
        $data['wm_time']    = time();       // check password ok at this moment
        $data['watermark']  = '';
        $data['user']       = $args[0]->user; 
        usleep(300 * 1000);        // sleep 300ms env

        if (!array_key_exists( $args[0]->user, $ob )) {
            $data['msg']        = "CMainPhp::post_login error : unknown user";
            return;
        }
        if ($ob[ $args[0]->user ] != $args[0]->password) {
            $data['msg']        = "CMainPhp::post_login error : bad password";
            return;
        }
        $data['watermark']  = $this->calcWaterMark( $args[0]->user, $args[0]->password, $data['wm_time'] );
        $data['msg']        = "CMainPhp::post_login success";
        $_SESSION['user']       = $data['user'];
        $_SESSION['wm_time']    = $data['wm_time']; // check password ok at this moment

    }



    function post_logout( & $data, $args ) {
        $_SESSION['user']       = '';
        $_SESSION['wm_time']    = null;
    }

    function post_get_php_errors( & $data, $args ) {
        // php error_log    /opt/homebrew/var/log/httpd/error_php.log
        // php error_log    rw/error_php.log

        $tail = tailCustom('rw/error_php.log', 30);
        $data['msg']        = print_r( $_POST, true );
        $data['log_lines']  = $tail;  
        
    }

    function post_reset_php_errors( & $data, $args ) {
        // php error_log    /opt/homebrew/var/log/httpd/error_php.log
        // php error_log    rw/error_php.log

        try {
            file_put_contents('rw/error_php.log', '');
        } catch (Exception $e){
            $data['error'] = 'CMainPhp::post_reset_php_errors error : Exception '.$e->getMessage();
        }
        $data['msg']        = print_r( $_POST, true );
        
    }


    function post_tic( & $data, $args ) {
        $time = time();
        $_SESSION['tic']  = $time;
        $data['news']  = '';


        // ======================
        //  check watermark
        // ======================
        $this->get_userdb( $ob );
        $passwd = array_key_exists($args[0]->user, $ob)   ?   $ob[$args[0]->user]    :   '';

        $check  = $this->checkWaterMark( $args[0]->user, $passwd, $args[0]->wm_time, $args[0]->watermark  );
        if ( $check )  {
            $data['return']  = true;
            $_SESSION['wm_tic']  = $time;
        }


        // =========================
        //  is it a new hh:mm ?
        // =========================
        $hour = date("Y-m-d_H:i");
        if ( !isset($_SESSION['hour']) )   $_SESSION['hour'] = $hour;
        if ( $_SESSION['hour'] != $hour ) {
            // create new information :
            $_SESSION['news'][$time] = [
                 'msg'    => "now it is ".$hour
                ];
            $data['news']  .= 'welcome in hour  = '.$hour.' before value was '.$_SESSION['hour'];
            $_SESSION['hour'] = $hour; 
        }

        // =========================
        //  check the php log file
        // =========================
        if (filesize('rw/error_php.log'))
            $data['news']  .= 'There is something in error_php.log';


        $data['msg']  = "CMainPhp::post_tic time={$time}";
    }


    function post_test( & $data, $args ) {

        $s = '';
        if (!property_exists($this, 'dbase')) {
            try {
                $this->dbase = new CDbase();
                $this->table = new CDbTable($this->dbase, 'users');
            } catch (Exception $e){
                unset($this->dbase);
                unset($this->table);
            }
        }

        // $fields = $this->table->_getFields();    echo "fields = " .print_r( $fields, true )."\n";
        /*
        $rec=[];
        $rec['email']  = 'jflemay@hotmail.com';
        $rec['passwd'] = 'faat';
        if (!$this->table->appendRecord($rec)) { $s .= $this->table->last_exception; }
        */
        
        $rec = $this->table->getRecord('email', 'jflemay@hotmail.com');

        if (property_exists($this, 'table')) {
            $data['msg'] = "table exists ! rec = " .print_r( $rec, true )."\n".$s;

        } else 
            $data['msg']  = "\$_POST var = ".print_r( $_POST, true );
        
    }

    function post_prepare_download( & $data, $args ) {

        $filepath = 'rw/' . $this->session_hash . '.csv';

        try {
            file_put_contents($filepath, $args[0]);
        } catch (Exception $e){
            $data['error'] = 'CMainPhp::post_prepare_download error : Exception '.$e->getMessage();
        }

        $data['msg']  = "";

    }


}



?>