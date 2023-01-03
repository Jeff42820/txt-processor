<?php
        // -------------------
        // cmodules.php
        // -------------------

// ===============================
// ==
// ==  class CModules
// ==
// ==
// ===============================


// = the index.php file starts with :
// =      
// =      include './src/utils.php';                      // lib_php some php utilities
// =      include './src/cmodules.php';                   // lib_php class CModules
// =      include './src/cmainphp.php';                   // lib_php class CMainPhp (base for CPrjMain)
// =      include './src/lib-utils-js.mod.php';           // lib_js some js  utilities
// =      include './src/lib-application-class.mod.php';  // lib_js class Application
// =
// =
// =
// =    modules type lib :
// == 1 lib ==  cmodules.php    this file, module mechanism, class CModules
// ==           cmainphp.php    class CMainPhp (base for CPrjMain, the php projet class)
// ==                           this will create the html code sent by apache (nginx) to the browser
// ==                           with class derivation, your code will be in your CPrjMain class.
// == 
// == Now CModules syntax is required :
// == 
// == 2 lib     lib-utils-js.mod.php            lib_js  some js  utilities
// ==           lib-application-class.mod.php   lib_js  class Application
// =
// =
// =



class CModules {
    public $modules = [];
    private $arr = [];
    public $onload = NULL;
    public static $_this;

    protected string $file = '';
    protected string $comment = '';

    function __construct() {
        self::$_this = $this;
    }

    static function include_begin( $__FILE__ , $comment='' ) {
        self::$_this->mod_include_begin( $__FILE__ , $comment );
    }

    static function include_end( $__FILE__ ){
        self::$_this->mod_include_end( $__FILE__ );
    }

    static function append( $name, $txt ){
        self::$_this->arr[$name] = $txt;
    }

    static function append_onload( $__FILE__, $txt ){
        self::$_this->onload = $txt;
    }

    private function mod_include_begin( $__FILE__ , $comment ) {
        $this->arr = [];
        $this->onload = NULL;
        $this->file = basename($__FILE__);
        $this->comment = $comment;
    }

    private function mod_include_end( $__FILE__ ) {
        $file = basename($__FILE__);
        if ($file != $this->file) {
            echo 'CModules::error : fileName begin <> end', $CR;
            exit(0);
        }
        $module = [];
        $module['comment'] = $this->comment;
        $module['onload'] = $this->onload;
        $module['code'] = $this->arr;
        $this->file = '';
        $this->comment = '';        
        $this->arr = [];
        $this->modules[$file] = $module;
    }

    function dump() {
        global $CR;
        echo '<table>', "\n";
        foreach( $this->modules as $k => $module) {
            echo '<tr><td><b>'.htmlspecialchars($module['comment']).'</b></td><td>('.htmlspecialchars($k).')', "</td></tr>\n";
        }
        echo '</table>', "\n";
    }

    function mod_echo_comment() {
        $s="<!-- Modules to load : \n";
        $s.="==================\n";
        foreach( $this->modules as $k => $module) {
            $s .= $k."\n";
        }
        $s.="==================\n";
        $s.="-->\n";
        echo $s;
    }

    function mod_echo_head() {
        global $CR;
        foreach( $this->modules as $k => $module) {
            foreach( array_keys($module['code']) as $k1 => $v1 ) {
                if (str_starts_with($v1, 'mod_header_')) {
                    // echo "<!-- mod_echo_head ".htmlspecialchars($v1)." -->\n";
                    echo $module['code'][$v1], "\n";
                } 
            }
        }
        foreach( $this->modules as $k => $module) {
            foreach( array_keys($module['code']) as $k1 => $v1 ) {
                if (str_starts_with($v1, 'mod_css_')) {
                    echo "\n<!-- <style> ".htmlspecialchars($v1)." --><style>\n";
                    echo $module['code'][$v1], "\n";
                    echo "</style><!-- ".htmlspecialchars($v1)." -->\n";
                }
            }
        }
    }

    function mod_echo_body() {
        foreach( $this->modules as $k => $module) {
            foreach( array_keys($module['code']) as $k1 => $v1) {
                if (false && str_starts_with($v1, 'mod_html_')) {
                    // ?? not autolocation ; explicit location in main.php :
                    // CModules::$_this->mod_echo_html('mod_html_div_something');
                    echo "<!--  module $k -- global $v1  begin -->\n";
                    echo $module['code'][$v1], "\n";
                    echo "<!--  module $k -- global $v1  end -->\n";
                } elseif (str_starts_with($v1, 'mod_js_')) {
                    echo "<!--  module $k -- global $v1  begin -->\n";
                    echo "<script>\n";
                    echo $module['code'][$v1], "\n";                    
                    echo "</script>\n";
                    echo "<!--  module $k -- global $v1  end -->\n";
                } else {
                }
            }
        }
    }


    function mod_get_elt( $moduleName, $elt ) {
        global $CR;
        if (!array_key_exists($moduleName, $this->modules)) {
            $msg =  "CModules::error : looking for [$moduleName], you have to include this module";
            php_log_to_js_console( $msg );
            echo $msg, $CR;
            return null;
        }

        $module =  $this->modules[ $moduleName ];
        if (!array_key_exists($elt, $module['code'])) {
            echo "CModules::error : looking for [$elt] in [$moduleName]", $CR;
            return null;
        }
        return $module['code'][$elt];                    
    }




    function mod_echo_html( $moduleName, $elt, $contents = null ) {
        if ($contents == null) {
            echo $this->mod_get_elt( $moduleName, $elt );                    
            return;
        }

        $this->mod_echo_elt_begin( $moduleName, $elt );
        echo $contents;
        $this->mod_echo_elt_end  ( $moduleName, $elt );
    }

    function mod_echo_elt_begin( $moduleName, $elt ) {        
        $str = $this->mod_get_elt( $moduleName, $elt );

        $r = preg_match("/(.+)(^<!--content-->)(.+)/ms", $str, $matches);
        if ($r !== 1) {
            return;   // null;
        }
        echo $matches[1];   //    return $matches[1];


        /*        
        // print_r(), var_dump() var_export()    $s  = "r=$r \n";        $s .= print_r( $matches, true );
        $r = '';
        $separator = "\r\n";
        $line = strtok($s, $separator);
        while ($line !== false) {
            if (trim($line) == '<!-- content -->') {
                return $r;
            }
            $r .= $line;
            $line = strtok( $separator );
        } */

    }

    function mod_echo_elt_end( $moduleName, $elt ) {
        $str = $this->mod_get_elt( $moduleName, $elt );

        $r = preg_match("/(.+)(^<!--content-->)(.+)/ms", $str, $matches);
        if ($r !== 1) {
            return;  // null;
        }
        echo $matches[3];
        // return $matches[3];
    }


    function sml_mustache( $str, $vars ) {
        foreach ($vars as $n => $v) 
            $str = str_replace( "{{".$n."}}", $v, $str);
        return $str;
    }


    function mod_echo_jsmain() {
        $append_onload = '';
        foreach( $this->modules as $k => $module) {
            if ($module['onload'] != NULL) {
                if ($append_onload != '')    $append_onload .= "\n";
                $append_onload .= $module['onload'];
            }
        }
        foreach( $this->modules as $k => $module) {
            foreach( array_keys($module['code']) as $k1 => $v1) {
                if (str_starts_with($v1, 'mod_jsmain')) {
                    echo "<!--  module $k -- global $v1  begin -->\n";
                    echo "<script>\n";
                    $t = $module['code'][$v1];
                    $vars = [];    $vars['append_onload'] = $append_onload;
                    echo $this->sml_mustache($t, $vars), "\n";
                    echo "</script>\n";
                    echo "<!--  module $k -- global $v1  end -->\n";
                } else {
                }
            }
        }
    }

}
new CModules();



?>