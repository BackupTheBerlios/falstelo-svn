<?php
/*
 * (c) Nicolas Bouillon
 * Ce fichier est distribué selon la licence GNU GPL.
 */
 
/* 
 * NE PAS MODIFIER CE FICHIER ! MAIS SURCHARGER LES VARIABLES DANS usrlib/variables.php !
 */

$GLOBALS["USE_SABLOTRON"] = false; //sablotron n'est pas disponible partout, dans ce cas, on utilise libxml.
$GLOBALS["TYPE_MIME"] = "text/html"; //type mime envoyé par défaut
$GLOBALS["TEMPS_CACHE"] = 0; //secondes
$GLOBALS["PAGE_404"] = "404";

$GLOBALS["dbhost"] = "database_host";
$GLOBALS["dbname"] = "database_name";
$GLOBALS["dbuser"] = "database_user";
$GLOBALS["dbpass"] = "database_password";
$GLOBALS["dbtype"] = "database_type"; // "mysql", "postgres", "oracle"...
$GLOBALS["dbencoding"] = "UTF-8";

setlocale(LC_MONETARY, 'fr_FR');

error_reporting(E_ERROR |  E_PARSE | E_CORE_ERROR | E_COMPILE_ERROR | E_USER_ERROR );

// pour php <4.3.0
if (!function_exists("file_get_contents")) 
{
  function file_get_contents($filename, $use_include_path = 0){
    $data = ""; // just to be safe. Dunno, if this is really needed
    $file = @fopen($filename, "rb", $use_include_path);
    if ($file){
      while (!feof($file)) $data .= fread($file, 1024);
      fclose($file);
    }
    return $data;
  }
}
?>
