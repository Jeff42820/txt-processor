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




    function calcWaterMark( $user, $password, $time ) {
        $watermark =  $user."{$time}".$password.$this->server_name;
        $watermark =  substr(md5($watermark), 2, 10);
        return $watermark;
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


            /*

              try {
                $rq['rq']  = json_decode( base64_decode($_POST['rq']) ); 
              } catch (Exception $e) {
                $rq['exception rq'] = $e->getMessage();
              }

                if ( file_exists($fileLib) ) {
                    try {
                        include_once( $fileLib );      
                        $rq['return'] = call_user_func_array( $fct, array( & $rq ) );
                    } catch (Throwable $e) {
                      $err = 'error in file:'.$e->getFile().' line:'.$e->getLine().' exception:'.$e->getMessage(); 
                            // ' file:'.$e->getFile();
                    }
                } 

                switch ($p3) {
                    case 'number':
                        $p2 = (float) $p2;
                        if (floor($p2) == $p2)  $p2 = (int) $p2;
                        break;
                    case 'boolean':
                        $p2 = $p2 == 'true' ? true : false;
                        break;
                }
                $typ1 = gettype($p2);

            */




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

        /*
            [name] => CA20221225_150649.csv
            [type] => text/csv
            [tmp_name] => /private/var/folders/bk/l9_hb9kj0cdc3gxrhb3_m1s4000102/T/phpUUEI4F
            [error] => 0
            [size] => 2518

            $fileExtension = strtolower( pathinfo($fileName, PATHINFO_EXTENSION) );
            $uploadPath    = get_wdir() ."/". basename($fileName);
        */                

    }




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
        $watermark  = $this->calcWaterMark( $args[0]->user, $password, $args[0]->wm_time );
        if ( $watermark != $args[0]->watermark )  {
            $data['msg']        = "CMainPhp::post_check_watermark error: bad watermark";
            return;
        }

            

        $time = time();
        $data['age']      = $time - $args[0]->wm_time;
        $data['tic_age']  = $time - $_SESSION['wm_tic'];
        if ($data['age']     > 12 * 60*60 ||        // 60sec * 60mn *12 = 12h
            $data['tic_age'] >      60*5      ) {   // 60sec * 5mn = 5mn
            $data['msg']  = "CMainPhp::post_check_watermark too old";
            return;
        }

        $data['msg']        = "CMainPhp::post_check_watermark success";
        $data['return']     = true;
    }

    function post_login( & $data, $args ) {

        $this->get_userdb( $ob );
        $data['wm_time']    = time();
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
        $_SESSION['wm_time']    = $data['wm_time'];

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

        // ======================
        //  check watermark
        // ======================
        $this->get_userdb( $ob );
        $watermark  = $this->calcWaterMark( $args[0]->user, $ob[ $args[0]->user ], $args[0]->wm_time );
        if ( $watermark == $args[0]->watermark )  {
            $data['return']  = true;
            $_SESSION['wm_tic']  = $time;
        }

        // =========================
        //  check the php log file
        // =========================
        $data['news']  = '';
        if (filesize('rw/error_php.log'))
            $data['news']  .= 'There is something in error_php.log';
        $data['msg']  = "CMainPhp::post_tic time={$time}";
    }

    function post_test( & $data, $args ) {

        $this->get_userdb( $ob );

        $data['file']  = print_r( $ob, true );
        $data['msg']  = print_r( $_POST, true );
        $data['msg']  = print_r( $_SESSION, true );
        
    }

}



?>