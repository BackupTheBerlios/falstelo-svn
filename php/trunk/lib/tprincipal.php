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

require_once("variables.php");
if (file_exists("usrlib/variables.php"))
{
  require_once("usrlib/variables.php");
}

$temps_depart = gettimeofday();

class Tprincipal
{
  var $base_dir = ""; // contient le chemin sur le serveur de la base du site
  var $affiche_dir = ""; // contient le chemin de la page demandée
  var $PAGE_404 = "404";
  var $afficher_temps = true;

  /**
Constructeur
  */
  function Tprincipal()
  {
    $this->base_dir = getcwd();
    $this->PAGE_404 = $GLOBALS["PAGE_404"];
    $this->afficher_temps = $GLOBALS["AFFICHER_TEMPS"];
  }

  /**
Si l'on veux servir la page "page_demandee" comme une page d'erreur 404
  */
  function servir_page_erreur($page_demandee, $page_initialement_demandee)
  {
    $this->__servir_page($page_demandee, "HTTP/1.x 404 Not Found", $page_initialement_demandee);
  }

  /**
Pour servir la page "page_demandee" de maniere normale
  */
  function servir_page($page_affichee, $page_demandee=NULL)
  {
    if ($page_demandee == NULL)
    {
      $page_demandee = $page_affichee;
    }
    $this->__servir_page($page_affichee, "HTTP/1.x 200 OK", $page_demandee);
  }

  function __servir_page($page_affichee, $header_http, $page_demandee=NULL)
  {
    if ($page_demandee == NULL)
    {
      $page_demandee = $page_affichee;
    }
    $voir_xml = $_GET['voir_xml'];
    // Test d'existence du fichier correspondant à la page demandée
    if ( file_exists($page_affichee . ".php") )
    {
				
      // le fichier .php existe, on l'utilise alors (cas des pages dynamiques, avec accès à la bdd)
      require_once($page_affichee . ".php");
      chdir(dirname($page_affichee . ".php"));
      $nom_classe = "T" . basename($page_affichee);
      $iTransformation = new ${nom_classe}($page_affichee, $page_demandee);
      $iTransformation->simple_html = false;
    }
    else
    {
      // le fichier $page.php n'existe pas, peut etre qu'un fichier $page.xml existe (cas des pages statiques)
      if ( !file_exists($page_affichee . ".xml") )
      {
        // ni fichier .php, ni fichier .xml. La page demandée n'existe donc pas.
        // Affichage de la page statique d'erreur à la place
        $header_http = "HTTP/1.x 404 Not Found";
        $page_affichee = $this->PAGE_404;
      }
      // On teste pour voir si l'utilisateur a surchargé lui meme la classe TTransformation
      if (file_exists("usrlib/tusrtransformation.php"))
      {
        require_once("usrlib/tusrtransformation.php");
        $nom_classe = "Tusrtransformation";
      }
      else
      {
        require_once("lib/ttransformation.php");
        $nom_classe = "Ttransformation";
      }
      $iTransformation = new ${nom_classe}($page_affichee, $page_demandee);
      $iTransformation->simple_html = true;
      $iTransformation->fichiers_xml = array($page_affichee . ".xml");
    }

    $iTransformation->voir_xml = $voir_xml;
    $iTransformation->chemin_relatif = $this->calcule_chemin_relatif($page_demandee);
    $resultat = $iTransformation->get(); //affichage de la page.
    header($header_http);
    header("Content-type: $iTransformation->type_mime");
    print $resultat;

    if ($this->afficher_temps){
      //Ajoute le temps de génération en fin de page. Je ne suis pas sur de la fiabilité du résultat donné...
      $arrive = gettimeofday();
      print "<!--";
      $temps_total = ($arrive[usec] - $depart[usec]) / 1000 / 1000;
      print "Page produite en ". $temps_total ." secondes par php-falstelo.";
      print "-->\n";
    }
  }
  
  function calcule_chemin_relatif($page)
  {
      return "./" . str_repeat("../", substr_count($page, "/"));  
  }
  
}


?>
