<?php
        // -------------------
        // prj-header-html.mod.php
        // -------------------


CModules::include_begin(__FILE__, 'This is module prj-header-html (here is the head of html : <title>, (c), <meta>....)');



CModules::append( 'mod_header_1', <<<EOLONGTEXT
<!--
    =======================================
    ====
    ====  Text processor for bank import 
    ====
    ====  php & javascript
    ====
    ====
    =======================================
    ====
    ====  2022-12-07
    ====
    ====  v 0.901
    ====
    ====
    ====
    ====
    ==== (c) 2022 JF Lemay (FR) (contact via github Jeff42820)
    ====
        MIT License
        Copyright (c) 2022 JF Lemay
        Permission is hereby granted, free of charge, to any person obtaining a copy
        of this software and associated documentation files (the "Software"), to deal
        in the Software without restriction, including without limitation the rights
        to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
        copies of the Software, and to permit persons to whom the Software is
        furnished to do so, subject to the following conditions:
        The above copyright notice and this permission notice shall be included in all
        copies or substantial portions of the Software.
        THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
        IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
        FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
        AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
        LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
        OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
        SOFTWARE.
    ====
    ====
    =======================================
-->

<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<link rel="icon" type="image/svg+xml" href="favicon.svg" />
<title>Text processor for bank import</title>
<link rel="stylesheet" href="icons/framework7-icons.css">  <!--  https://framework7.io/icons/  MIT License -->
EOLONGTEXT);     // $mod_header_1


CModules::include_end(__FILE__);

?>