<?php
/*
 * (c) Nicolas Bouillon
 * Ce fichier est distribué selon la licence GNU GPL.
 */
 
/* 
 * NE PAS MODIFIER CE FICHIER ! MAIS SURCHARGER LES VARIABLES DANS usrlib/variables.php !
 */

/*
Si false: on utilise libxslt.
Si true: on utilise sablotron.
Vérifiez via phpinfo quelle processeur XSLT est disponible.
*/
$GLOBALS["USE_SABLOTRON"] = false; //sablotron n'est pas disponible partout, dans ce cas, on utilise libxml.

/*
Type mime envoyé par défaut
 */
$GLOBALS["TYPE_MIME"] = "text/html"; //type mime envoyé par défaut

/*
Durée de validité du cache, en secondes. Si "0", aucune utilisation du cache, les fichiers cache ne seront pas générés.
*/
$GLOBALS["TEMPS_CACHE"] = 0;

/*
Chemin pour le répertoire cache. Doit etre accessible en écriture par le processus qui execute le script (www-data sous Debian).
*/
$GLOBALS["CACHE_PATH"] = "cache/";

/*
Chemin de base pour les fichiers XML
*/
$GLOBALS["XML_PATH"] = "";

/*
Chemin de base pour les fichiers XSLT
 */
$GLOBALS["XSLT_PATH"] = "";

/*
Est ce que par défaut, on affiche le fichier XML tel que plutot que de faire la transformation XSLT
*/
$GLOBALS["VOIR_XML"] = false;


/*
Le nom de la page xml ou php utilisé pour l'erreur 404.
*/
$GLOBALS["PAGE_404"] = "404";

/*
Afficher en bas de la page, en commenaire XML (<!-- ... -->), le temps mis par Falstelo pour générer la page.
*/
$GLOBALS["AFFICHER_TEMPS"] = false;

/*
Paramètres de connexion à la base de donnée, si nécessaire.
*/
$GLOBALS["dbhost"] = "database_host";
$GLOBALS["dbname"] = "database_name";
$GLOBALS["dbuser"] = "database_user";
$GLOBALS["dbpass"] = "database_password";
$GLOBALS["dbtype"] = "database_type"; // "mysql", "postgres", "oracle"...
$GLOBALS["dbencoding"] = "UTF-8";

/*
Locale pour les chiffres et tout ça. 
TODO: Peut etre plus finement ?
*/
setlocale(LC_MONETARY, 'fr_FR');


/*
Definition de fonctions utlisateur
*/

// pour php <4.3.0
// utilisé pour afficher les fichiers du cache.
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
