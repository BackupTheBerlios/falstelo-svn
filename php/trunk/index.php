<?php
/*
 * (c) Nicolas Bouillon
 * Ce fichier est distribué selon la licence GNU GPL.
 */

/*
 * Fichier par défaut du répertoire.
 */

require_once("lib/tprincipal.php");

// Si la page a été appellée sans arguments
global $HTTP_GET_VARS;
if ( ! $HTTP_GET_VARS['page'])
{
  $page_demandee = "accueil";
}
else
{
  $page_demandee = $HTTP_GET_VARS['page'];
}

$iPrincipal = new Tprincipal();

if ($HTTP_GET_VARS['erreur'])
{
  $iPrincipal->servir_page_erreur($page_demandee, $HTTP_GET_VARS['erreur']);
}
else
{
  $iPrincipal->servir_page($page_demandee);
}



?>
