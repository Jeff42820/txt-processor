<?php
        // -------------------
        // div-toolbar.mod.php
        // -------------------


CModules::include_begin( __FILE__ , 'This is division module  toolbar' );


if (!function_exists('html_chg_value')) {

    function html_chg_value( $svg, $array_chg ) {
        // assume tags are formatted with double-quote like this:    tag="value"  (NB: no \" in value)
        $count=0;
        foreach ($array_chg as $v) {
            list($vname, $vv) = $v;
            $count = 0;
            $value = str_replace('"', '&quot;', $vv);
            $svg =  preg_replace( "/({$vname}=\")([^\"]*)(\")/", '${1}'."${value}".'${3}', $svg, 1, $count );
            if ($count == 0)
               $svg =  preg_replace( "/(<[a-zA-Z]+\ )/", "$1{$vname}=\"${value}\" ", $svg, 1 );
        }    
        return $svg;
    }

}



if (!function_exists('html_btn_svg')) {

    function html_btn_svg( $module_name, $svg_name, $svg_size, $array_chg = [] ) {
        $svg = CModules::$_this->mod_get_elt($module_name, $svg_name);

        $svg = str_replace (array("\r\n", "\n", "\r"), ' ', $svg);
        $svg = preg_replace('/\s+/', ' ', $svg);
        // php_log_to_js_console($svg);

        $array_chg =  array_merge($array_chg,  [ ['width', $svg_size], ['height', $svg_size] ] );
        $r = html_chg_value( $svg, $array_chg );
        return $r;
    }

}




CModules::append( 'mod_html_div_toolbar', <<<EOLONGTEXT
<div id="id_toolbar" class="div_toolbar">
<!--content-->
</div>
EOLONGTEXT );  // mod_html_div_toolbar



CModules::append( 'mod_html_div_message', <<<EOLONGTEXT

<div id="id_message" class="div_tb_message">
    <span id="id_msg_text"> &nbsp; </span>
</div>

EOLONGTEXT );  // mod_html_div_message



CModules::append( 'mod_css_dmc_toolbar', <<<EOLONGTEXT

div.div_toolbar {
  display: flex;    
}


div.div_tb_message {
  background-color: #c0c0c0;
  flex-grow: 4;
  margin: 0em;
  padding: 0em;
  display: flex;
  align-items: center;
  /* justify-content: center;  */
}

div.div_tb_message span {
  margin: 0em;
  padding: 0em;
}

svg.dmc_tb_icon {
    flex-grow: 0; 
    cursor: pointer;
}

EOLONGTEXT ); // mod_css_dmc_toolbar     





CModules::include_end( __FILE__ );

?>