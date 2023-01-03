<?php
        // -------------------
        // prj-utils-css.mod.php
        // -------------------


CModules::include_begin( __FILE__ , 'This is division module prj-utils-css (root css)' );




CModules::append( 'mod_css_dmc_utils', <<<EOLONGTEXT

html {
    height: 100%;    
    margin: 0;
    padding: 0;
}

body {
    font-family: sans-serif;
    font-size: 12pt;
    display: flex;
    flex-direction: column;
}

p {
    margin: 0;   /* 16px by default */
}


input[type="text"], input[type="password"] {
    outline: 0;
    border:  2px inset #ddd; 
    border-radius: 2px;
}

textarea:focus-visible,  input:focus-visible {
    outline: 2px solid #DC143C80;
    outline-offset: 0px;
    border-radius: 3px;
}

input[readonly]:focus-visible 
{
    outline: 2px solid #0000;
    border:  1px solid #0000; 
}

table {
    border-collapse: collapse;
    border: 1px solid #888;
}

thead {
    border: 1px solid #5555;
}


.tr_rotateover {
    transition: transform .2s ease-in-out;  
}

.tr_rotateover:hover {
  /*  transform:rotate(15deg);  */
    transform:scale(1.2); 
}


EOLONGTEXT ); // mod_css_dmc_utils     



CModules::include_end( __FILE__ );

?>