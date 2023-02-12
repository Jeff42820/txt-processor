<?php
        // -------------------
        // div-tooltips.mod.php
        // -------------------


/*
  ============================================
  ====== tooltips     ==========
  ====== 
  ======
  ============================================
*/


CModules::include_begin(__FILE__ , 'This is division module for tooltips');



CModules::append( 'mod_html_div_tooltips', <<<EOLONGTEXT


<!--   mod_html_div_tooltips 

example :                 
    <p class="dmcss_tooltip" data-tooltip="Thanks for hovering! I'm a tooltip">test WgtTable</p>


-->


EOLONGTEXT );  // mod_html_div_tooltips



/*
  ============================================
  ====== division module : div-tooltips.mod
  ====== css class       : dmc_containers
  ============================================
*/


CModules::append( 'mod_css_div_tooltips', <<<EOLONGTEXT



/* ================================== 
   =
   =
   =       css for tooltips
   =
   =
   =
   ================================== */


/*  ========= dmcss_tooltip  ========== */

:root{
  --dmcss_tooltip_position_offset:   0.2em;
}


magic_opacity{
    animation-name: opacity_0_to_80;
    animation-duration: 2s;
}

@keyframes opacity_0_to_80 {
    from {opacity: 0;  }
    to   {opacity: 0.8;}
}

.dmcss_tooltip {
  position:relative; 
  border-bottom:1px dashed #000; 
}

.dmcss_tooltip:before {
  content: attr(data-tooltip); /* here's the magic */
  position:absolute;
  display:none; 

  opacity: 0;
  animation: 0.4s 0s opacity_0_to_80 forwards;     /* duration delay anim fill-mode */

    /* put that before the  [[top:100%;      left:50%;]]   specs      */
    /* default  position : small offset  to right-bottom */
  margin: 0;

  top:100%;    
  left:50%;         
  /* transform:translateX(-30%); */

  
  min-width:   3em;
  max-width:   10em;
  width:       max-content;
  padding:     0.3em; 
  border: 1px solid darkgrey;
  background:#eee;
  color: #333;
  text-align: left;      // center;
}

.dmcss_tooltip.left_bottom:before {
  left:   initial; 
  right:  100%; 
  /* transform:translateX(-30%); */

  margin: initial;
  margin-top: 0;
  margin-left: 0;
  margin-right:  0;
  margin-bottom: 0;
}

.dmcss_tooltip.right_bottom:before {
  right:initial;    
  left:100%;
  /* transform:translateX(-30%); */

  margin:initial;
  margin-top: 0;
  margin-left: 0;
  margin-right:  0;
  margin-bottom: 0;
}

.dmcss_tooltip:hover:before {
  display:block;
}



/*  ========= dmcss_tooltip  ========== */



EOLONGTEXT ); // mod_css_div_tooltips     



CModules::include_end(__FILE__);




?>