<?php
/*
 * (c) Nicolas Bouillon
 * Ce fichier est distribu� selon la licence GNU GPL.
 */

/**
 * Objet � stoquer comme variable de session afin d'identifier l'utilisateur 
 * loggu� et connaitre son panier (meme s'il n'est pas loggu� d'ailleurs)
 */

class Tsession
{
  
  var $login = null;
  var $panier = array(); // Tableau permettant de faire l'association entre l'identifiant du produit avec la quantit�e command�e.
  var $lang = 'fr'; // Langue par d�faut : francais.
  function Tsession()
    {
      //chercher la langue voulue par le navigateur si elle existe.
    }
  function login($login, $password)
    {
      //v�rification du mot de passe
      $this->login = $login;
    }
  function ajouter_panier($id, $quantite)
    {
      if ($this->panier[$id] == null)
	{
	  $this->panier[$id] = $quantite;
	}
      else
	{
	  $this->panier[$id] += $quantite;
	}
    }
  function vider_panier()
    {
      unset($this->panier);
    }
  function sauver_panier($nom)
    {
      //enregistre le panier avec un nom pour une visite future
    }
}

?>