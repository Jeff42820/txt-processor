<?php
        // -------------------
        // utils.php
        // -------------------

// ===============================
// ==
// ==  some php utilities
// ==
// ==
// ===============================


if (!function_exists('str_starts_with')) {
  function str_starts_with($str, $start) {
    return (@substr_compare($str, $start, 0, strlen($start))==0);
  }
}

function str_php2js( $kk ) {
    if ( is_array($kk) && array() !== $kk ) {
        if ( array_keys($kk) === range(0, count($kk) - 1) ) 
          $json = json_encode( array_values($kk) );
        else
          $json = json_encode( array_values($kk), JSON_FORCE_OBJECT );
        $v = "JSON.parse( '{$json}' )";
    } else if ( is_int($kk) ) {
      $v = $kk;
    } else if ( is_float($kk) )  {
      $v = number_format($kk, 8, '.', '');
    } else if ( is_string($kk) ) {
      $v = str_replace("`", "\`", $kk);   // str_replace('"', '\\"', $kk);
      $v = '`'.$v.'`';            
    }
    return $v;
}



// ===============================================
// 
//      php_log_to_js_console
//  
// 
// (php.log  console.log  app.log  log_php  php_log)
// ===============================================
function php_log_to_js_console($data) {
    $output = $data;
    if (is_array($output))
        $output = implode(',', $output);

    $v = str_replace("`", '◊', $output);
    echo "<script>console.log(`php_log: ‹". $output . "›`);</script>";
}



function getClientIpAddr()  {
    $ip = '0.0.0.0';
    if     (!empty($_SERVER['REMOTE_ADDR']))           {  $ip=$_SERVER['REMOTE_ADDR'];           }
    elseif (!empty($_SERVER['HTTP_CLIENT_IP']))        {  $ip=$_SERVER['HTTP_CLIENT_IP'];        }
    elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR']))  {  $ip=$_SERVER['HTTP_X_FORWARDED_FOR'];  }
    return $ip;
}



/**
* Slightly modified version of http://www.geekality.net/2011/05/28/php-tail-tackling-large-files/
* @author Torleif Berger, Lorenzo Stanco
* @link http://stackoverflow.com/a/15025877/995958
* @license http://creativecommons.org/licenses/by/3.0/
*/
function tailCustom($filepath, $lines = 1, $adaptive = true) {

    $f = @fopen($filepath, "rb");
    if ($f === false) return false;
    if (!$adaptive) $buffer = 4096;
    else $buffer = ($lines < 2 ? 64 : ($lines < 10 ? 512 : 4096));
    fseek($f, -1, SEEK_END);
    if (fread($f, 1) != "\n") $lines -= 1;
    $output = '';
    $chunk = '';

    while (ftell($f) > 0 && $lines >= 0) {
        $seek = min(ftell($f), $buffer);
        fseek($f, -$seek, SEEK_CUR);
        $output = ($chunk = fread($f, $seek)) . $output;
        fseek($f, -mb_strlen($chunk, '8bit'), SEEK_CUR);
        $lines -= substr_count($chunk, "\n");
    }
    while ($lines++ < 0) {
        $output = substr($output, strpos($output, "\n") + 1);
    }
    
    fclose($f);
    return trim($output);
}



?>
