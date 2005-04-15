# -*- coding: UTF-8 -*-
"""
Python-Falstelo -- Un moteur XML/XSLT en mod_pyhton pour Apache
Auteurs :
  - Nicolas Bouillon
  - Damien Boucard
Python-Falstelo est fourni sans aucune garantie.
Pour plus de détails, voyez le fichier LICENCE.txt.
Ce programme est libre et vous êtes encouragé à le redistribuer sous les conditions de la CECILL.
"""

from mod_python import apache
from transformation import Ttransformation
import imp
import ConfigParser
#~ import traceback

# mod_python handler
def handler(req):
	base = ""
	# on récupère le chemin de la racine du site
	if not conf.has_option("Chemins","cheminBase"):
		t = __file__
		base = t[:t[:t.rfind('/')].rfind('/')]
		conf.set("Chemins","cheminBase",base)
	else:
		base = conf.get("Chemins","cheminBase")

	# on sépare le nom de fichier et son répertoire
	rep = ''
	fichier = req.filename
	slash = req.filename.rfind('/')
	if slash != -1:
		fichier = req.filename[slash+1:-5]
		rep = req.filename[:slash]
	#~ apache.log_error("base=%s;rep=%s;fichier=%s;" %(base,rep,fichier))
	# Recherche d'une classe de page dans un script independant
	t = chercherClasse(req, rep, fichier, fichier)
	if t == None and len(base) <= len(rep) and rep[:len(base)] == base:
		if rep != base:
			# Recherche d'une classe de page dans un script generique
			slash = rep.rfind('/')
			t = chercherClasse(req, rep, rep[slash+1:], fichier)
		elif conf.has_option("Speciaux","nomBaseScript"):
			# Recherche d'une classe de page dans le script de base
			t = chercherClasse(req, base, conf.get("Speciaux","nomBaseScript"), fichier)
		while t == None and rep != base:
			# Recherche d'une classe generique dans chaque sous-rep
			slash = rep.rfind('/')
			t = chercherClasse(req, rep, rep[slash+1:], rep[slash+1:]+"transformation")
			rep = rep[:slash]
		if t == None and conf.has_option("Speciaux","nomBaseScript"):
			# Requerche de la classe generique dans le script de base
			module = conf.get("Speciaux","nomBaseScript")
			t = chercherClasse(req, base, module, module+"transformation")
	if t == None:
		# Utilisation de la classe generique par defaut
		#apache.log_error("Utilisation de la classe Ttransformation")
		t = Ttransformation(req, conf)
	# on lance la transformation
	t.proceder()
	if t.codeRetour == apache.HTTP_NOT_FOUND and conf.has_option("Speciaux","nomPage404"):
			# Recherche d'une page 404 personnalisee
			#apache.log_error("DEBUT page404")
			t = Ttransformation(req, conf, conf.get("Speciaux","nomPage404"))
			t.proceder()
	elif t.codeRetour == apache.HTTP_UNAUTHORIZED and conf.has_option("Speciaux","nomPage401"):
			# Recherche d'une page 401 personnalisee
                        t = Ttransformation(req, conf, conf.get("Speciaux","nomPage401"))       
                        t.proceder()
			if (t.codeRetour == apache.HTTP_NOT_FOUND):
				t.codeRetour = apache.HTTP_UNAUTHORIZED
		
	req.content_type = t.typeMime
	t.envoyer()
	return t.codeRetour

def chercherClasse(req, rep, module, classe):
	# cette fonction tente d'importer le module donne en parametre à partir
	# du chemin (rep) donne. elle tente ensuite de charger la classe donnée
	# en parametre (prefixee par un 'T').
	# si le module ou la classe retournent une exception, la fonction
	# revoie None, sinon l'instance de la classe est renvoyee.
	#~ apache.log_error("chercherClasse(%s, %s, %s)" %(repr(rep),repr(module),repr(classe)))
	try:
		# import du module
		importation = imp.find_module(module, [rep,])
                module = imp.load_module("dynamicpage", importation[0], importation[1], importation[2])
		# instanciation de la classe
		t = eval("module.T"+classe+"(req,conf)")
	except (ImportError, AttributeError):
		# décommentez les lignes suivantes pour activer le mode debug.
		# [TODO] gerer ce mode via un flag dans le fichier de conf.
		#~ f = file("/tmp/flst.exc","a")
		#~ import traceback
		#~ traceback.print_exc(file=f)
		#~ f.close()
		#~ del traceback, f
		return None
	return t
	

def error(req,message):
	# fonction depreciee, envoie un message d'erreur
	# servait au debut de l'ecriture du script
	# usage dans handler() : return error(req, "votre message")
	req.content_type = "text/html"
	req.write("<html><body>")
	req.write(message.replace("\n","<br/>"))
	req.write("</body></html>")
	return apache.OK

#=- Initialisation du module -=#
# Charge le fichier de configuration "falstelo.conf"
global conf
conf = ConfigParser.ConfigParser()
fichConf = __file__[:-2] + "conf"
#apache.log_error("FICHIER CONF: %s" %fichConf)
conf.read(fichConf)

