<?php
require_once("lib/ttransformation.php");

class Tusrtransformation extends Ttransformation
{
  function Tusrtransformation($page, $page_demandee){
    Ttransformation::Ttransformation($page, $page_demandee);
    //Creer ici la session et les parties XML fixes, ou encore des redirections pour un support du multilinguisme.
  }

  function get(){
    //parametres de cache et autres...
    return Ttransformation::get();
  }
  
  function transformer(){
    //mettre ici par exemple les parametres XSLT permanents
    return Ttransformation::transformer();
  }
}
?>