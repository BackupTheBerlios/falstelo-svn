<?php
/*
 * (c) Nicolas Bouillon
 * Ce fichier est distribué selon la licence GNU GPL.
 */

require("lib/ttransformation.php");

class Tcategorie extends Ttransformation
{
	function get()
	{
		$this->fichier_xslt = "xslt/accueil.xsl";
		$this->fichiers_xml = array("xml/categorie.xml");
		$this->requetes_sql = array("produits" => "SELECT * FROM produits");
		return $this->transformer();
	}
}

?>
