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
    <p class="tooltip" data-tooltip="Thanks for hovering! I'm a tooltip">test WgtTable</p>


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


/*  ========= tooltip  ========== */

magic_opacity{
    animation-name: opacity_0_to_80;
    animation-duration: 2s;
}

@keyframes opacity_0_to_80 {
    from {opacity: 0;  }
    to   {opacity: 0.8;}
}

.tooltip {
  position:relative; 
  border-bottom:1px dashed #000; 
}

.tooltip:before {
  content: attr(data-tooltip); /* here's the magic */
  position:absolute;

  opacity: 0;
  animation: 0.4s 1.5s opacity_0_to_80 forwards;     /* duration delay anim fill-mode */


  top:100%;    
  left:50%;         
  transform:translateX(-30%); 

  margin-top:5px; /* and add a small left margin */
  margin-left:5px; /* and add a small left margin */
  
  width:200px;
  padding:10px;
  border-radius:10px;
  background:#eee;
  color: #333;
  text-align:center;

  display:none; 
}

.tooltip.left:before {
  left:initial;         /* reset defaults */
  margin:initial;

  right:100%;           /* set new values */
  margin-right:15px;
}

.tooltip.right:before {
  left:initial;         /* reset defaults */
  margin:initial;

  left:100%;
  margin-left:15px; /* and add a small left margin */
}

.tooltip:hover:before {
  display:block;
}



/*  ========= tooltip  ========== */



EOLONGTEXT ); // mod_css_div_tooltips     



CModules::include_end(__FILE__);




?>