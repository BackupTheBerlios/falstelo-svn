<?php
/*
 * (c) Nicolas Bouillon
 * Ce fichier est distribué selon la licence GNU GPL.
 */

require("./lib/ttransformation.php");

class Tproduit extends Ttransformation
{
  function get()
    {
      $this->fichier_xslt = "xslt/accueil.xsl";
      $this->requetes_sql = array(
				  "clients" => "SELECT * FROM clients;",
				  "produits" => "SELECT * FROM produits;",
				  );
      return $this->transformer();
    }
}
?>
