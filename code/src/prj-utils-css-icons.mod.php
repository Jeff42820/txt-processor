<?php
        // -------------------
        // prj-utils-css-icons.mod.php
        // -------------------


CModules::include_begin( __FILE__ , 'This is division module css-icons (use embeded svg icons as <span> elements)' );


CModules::append( 'mod_css_dmc_icons', <<<EOLONGTEXT



/* ================================== 
   =
   =
   =       css
   =
   =
   =
   ================================== */


span.icon_inline {
    display: inline;
    position: relative;
    overflow-y: hidden;  
}


span.icon_inline img {
    position: absolute;
    width: auto;
    height: 1.2em;
    transform: translateX(-50%) translateY(-50%) translateY(-0.1em);  
    top: 50%;
    left: 50%;
}


span.icon_inline img.icon_inline_scale1_2 {
    transform: translateX(-50%) translateY(-50%) translateY(-0.1em) scale(0.5);  
}

span.icon_inline img.icon_inline_scale1 {
    transform: translateX(-50%) translateY(-50%) translateY(-0.1em) scale(1.5);  
}

span.icon_inline img.icon_inline_scale2 {
    transform: translateX(-50%) translateY(-50%) translateY(-0.1em) scale(2);  
}




/* ================================== 
   =
   =
   =       icons
   =
   =
   =
   ================================== */



span.icon_user::before      {    content: url("icons/user.svg");        }
span.icon_refresh::before   {    content: url("icons/refresh.svg");     }



EOLONGTEXT ); // mod_css_dmc_icons     

/*
    =====================================================================

                Example

    <span class="icon_inline"><img class="icon_inline_scale1" src="icons/user.svg" /></span>

    =====================================================================
                    Please note that you may remove these in the svg file :
                        width="32"
                        height="32"
                        class="something"

                    And the following may be ok :
                        viewBox="0 0 100 100"
    =====================================================================
*/

/*

What is the order of precedence for CSS?
========================================

  1  inline css ( html style attribute ) overrides css rules in style tag and css file
  2  a more specific selector takes precedence over a less specific one
  3  rules that appear later in the code override earlier rules if both have the same specificity.
  4  A css rule with !important always takes precedence.

*/


CModules::include_end( __FILE__ );

?>