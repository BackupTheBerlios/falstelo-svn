<?php
/*
 * Ecrasez ici les parametres par défaut définis dans lib/variables.php
 */

$GLOBALS["USE_SABLOTRON"] = false;

/*
Type mime envoyé par défaut
 */
$GLOBALS["TYPE_MIME"] = "application/xhtml+xml"; //type mime envoyé par défaut

/*
Durée de validité du cache, en secondes. Si "0", aucune utilisation du cache, les fichiers cache ne seront pas générés.
*/
$GLOBALS["TEMPS_CACHE"] = 43200;

/*
Afficher en bas de la page, en commenaire XML (<!-- ... -->), le temps mis par Falstelo pour générer la page.
*/
$GLOBALS["AFFICHER_TEMPS"] = true;
?>
