<?php
/*
 * (c) Nicolas Bouillon
 * Ce fichier est distribué selon la licence GNU GPL.
 */
 
/*
 * Ce fichier est appellée à chaque appel d'une page .html de ce répertoire
 * grâce à une RewriteRule (voir le fichier .htaccess de ce répertoire)
 * Le nom de la page demandée (sans le .html) est récupéré dans la variable
 * $page
 */

$temps_depart = gettimeofday();

class Tprincipal
{
  function extract_processing_instruction(&$dom){
    $xpth = $dom->xpath_new_context();
    $pis = $xpth->xpath_eval("//self::processing-instruction()");
    return $pis;
  }

  function extract_stylesheet_filename($fichier_xml)
  {
	 // ! Le fichier xml ne doit avoir qu'une seule processing instruction

	 $dom = domxml_open_file($fichier_xml);
	 $pis = $this->extract_processing_instruction($dom);
	 $node = $pis->nodeset[0];
	 if ( $node != null)
		{
		  preg_match("/href=['\"](.+?)['\"]/", $node->data(), $match);
		  return $match[1];
		}
	 else
		{
		  return null;
		}
  }

  function servir_page_erreur($page_demandee)
  {
	 $this->__servir_page($page_demandee, "HTTP/1.x 404 Not Found");
  }

  function servir_page($page_demandee)
  {
	 $this->__servir_page($page_demandee, "HTTP/1.x 200 OK");
  }


  function __servir_page($page_demandee, $header_http)
  {
	 header($header_http);
	 $page = $page_demandee;
	 global $voir_xml;
	 // Test d'existence du fichier correspondant à la page demandée
	 if ( file_exists($page . ".php") )
		{
		  // le fichier .php existe, on l'utilise alors (cas des pages dynamiques, avec accès à la bdd)
		  require_once($page . ".php");
		  $nom_classe = "T" . $page;
		  $iTransformation = new ${nom_classe}($page, $page_demandee);
		}
	 else
		{
		  // le fichier $page.php n'existe pas, peut etre qu'un fichier $page.xml existe (cas des pages statiques)
		  if ( !file_exists($page . ".xml") ){
			 // ni fichier .php, ni fichier .xml. La page demandée n'existe donc pas.
			 // Affichage de la page statique d'erreur à la place
			 //header($header_http);
			 $page = "404";
		  }
		
		  // On teste pour voir si l'utilisateur a surchargé lui meme la classe TTransformation
		  if (file_exists("usrlib/tusrtransformation.php")){
			 require_once("usrlib/tusrtransformation.php");
			 $nom_classe = "Tusrtransformation";
		  }
		  else {
			 require_once("lib/ttransformation.php");
			 $nom_classe = "Ttransformation";
		  }
		  $iTransformation = new ${nom_classe}($page, $page_demandee);

		  // recherche le nom de la feuille de style
		  $fichiers_xml = array($page.".xml");
		  if ($voir_xml == false)
			 {
				$xml_path = dirname($fichiers_xml[0]);
				$fichier_xslt = $xml_path . "/" . $this->extract_stylesheet_filename($fichiers_xml[0]);  // le nom de fichier de la feuille de style est relatif au chemin du fichier xml.
				if (!file_exists($fichier_xslt) || filetype($fichier_xslt) != "file")
				  {
					 $iTransformation->fichier_xslt = null;
				  }
				else
				  {
					 $iTransformation->fichier_xslt = $fichier_xslt; 
				  }
			 }
		  $iTransformation->fichiers_xml = $fichiers_xml;
		}

	 $iTransformation->voir_xml = $voir_xml;
	 /*
      $iTransformation->page_demandee = $page_demandee;
      $iTransformation->page = $page;
	 */
		
	 $resultat = $iTransformation->get(); //affichage de la page.
	 header("Content-type: $iTransformation->type_mime");
	 print $resultat;

	 //Ajoute le temps de génération en fin de page. Je ne suis pas sur de la fiabilité du résultat donné...
	 $arrive = gettimeofday();
	 print "<!--";
	 $temps_total = ($arrive[usec] - $depart[usec]) / 1000 / 1000;
	 print "Page produite en ". $temps_total ." secondes par php-falstelo.";
	 print "-->\n";
  }
}


?>
