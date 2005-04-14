# -*- coding: UTF-8 -*-

from mod_python import apache
from transformation import Ttransformation
import imp
import ConfigParser
import traceback

# mod_python handler
def handler(req):
	base = ""
	if not conf.has_option("Chemins","cheminBase"):
		t = __file__
		base = t[:t[:t.rfind('/')].rfind('/')]
		conf.set("Chemins","cheminBase",base)
	else:
		base = conf.get("Chemins","cheminBase")
	rep = ''
	fichier = req.filename
	slash = req.filename.rfind('/')
	if slash != -1:
		fichier = req.filename[slash+1:-5]
		rep = req.filename[:slash]
	#apache.log_error("base=%s;rep=%s;fichier=%s;" %(base,rep,fichier))
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
	#apache.log_error("chercherClasse(%s, %s, %s)" %(repr(rep),repr(module),repr(classe)))
	try:
		importation = imp.find_module(module, [rep,])
                module = imp.load_module("dynamicpage", importation[0], importation[1], importation[2])
		t = eval("module.T"+classe+"(req,conf)")
	except (ImportError, AttributeError):
		f = file("/tmp/flst.exc","a")
		traceback.print_exc(file=f)
		return None
	return t
	

def error(req,message):
	req.content_type = "text/html"
	req.write("<html><body>")
	req.write(message.replace("\n","<br/>"))
	req.write("</body></html>")
	return apache.OK

#=- Module initialisation -=#
global conf
conf = ConfigParser.ConfigParser()
fichConf = __file__[:-2] + "conf"
#apache.log_error("FICHIER CONF: %s" %fichConf)
conf.read(fichConf)

