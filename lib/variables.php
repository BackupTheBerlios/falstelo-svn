<?php
/*
 * (c) Nicolas Bouillon
 * Ce fichier est distribué selon la licence GNU GPL.
 */

error_reporting(E_ERROR |  E_PARSE | E_CORE_ERROR | E_COMPILE_ERROR | E_USER_ERROR );
$GLOBALS["dbhost"] = "localhost";
$GLOBALS["dbname"] = "falstelo";
$GLOBALS["dbuser"] = "falstelo";
$GLOBALS["dbpass"] = "noychi";
$GLOBALS["dbtype"] = "postgres";
setlocale(LC_MONETARY, 'fr_FR');

// pour php <4.3.0
if (!function_exists("file_get_contents")) 
{
  function file_get_contents($filename, $use_include_path = 0) 
    {
      $data = ""; // just to be safe. Dunno, if this is really needed
      $file = @fopen($filename, "rb", $use_include_path);
      if ($file) 
	{
	  while (!feof($file)) $data .= fread($file, 1024);
	  fclose($file);
	}
      return $data;
    }
}
?>
