<?php
require_once("./lib/ttransformation.php");

class Tmultiplexml extends Ttransformation
{
	function get()
		{
			$this->temps_cache = 0; //pas de cache pour ce fichier.

			$this->fichier_xslt = "./xslt/simple.xslt";
			
			array_push($this->fichiers_xml, "statique.xml");
			array_push($this->fichiers_xml, "statique2.xml");

			return ttransformation::get();
		}
}
?>