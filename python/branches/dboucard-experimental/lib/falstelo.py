# -*- coding: UTF-8 -*-

from mod_python import apache
import os, sys, sre, imp, string
import libxml2
import traceback
import ConfigParser

# mod_python handler
def handler(req):
	rep = ''
	fichier = req.filename
	slash = req.filename.rfind('/')
	if slash != -1:
		fichier = req.filename[slash+1:-5]
		rep = req.filename[:slash]
	try:
		importation = imp.find_module(fichier, [rep,])
 		module = imp.load_module("dynamicpage", importation[0], importation[1], importation[2])
	except:
		raise
		t = Ttransformation(req,conf)
	else:
		t = eval("module.T"+fichier+"(req,conf)")
	t.proceder()
	if t.codeRetour == apache.HTTP_NOT_FOUND and conf.has_option("Speciaux","nomPage404"):
		# Recherche d'une page 404 personnalis�e
		t = Ttransformation(req, conf, conf.get("Speciaux","nomPage404"))
		t.proceder()
	req.content_type = t.typeMime
	t.envoyer()
	return t.codeRetour


def error(req,message):
	req.content_type = "text/html"
	req.write("<html><body>")
	req.write(message.replace("\n","<br/>"))
	req.write("</body></html>")
	return apache.OK


class Ttransformation:
	def __init__(self, req, config, redirect=None):
		self.requete = req
		self.conf = config
		self.typeMime = self.conf.get("Prefs","typeMime")

		#on extrait du fichier demandé le repertoire dans lequel on travaille (le lien vers la feuille de style XSLT sera en fonction de ce repertoire
		self.repertoireTravail = sre.match("(/.*/)", req.filename).group(0)
		
		if redirect==None:
			#le fichier demandé est forcement un .html
			#on lui ote donc ce ".html" final
			self.fichierDemande = self.requete.filename[0:len(self.requete.filename)-5]
		else:
			self.fichierDemande = self.repertoireTravail + redirect
		
		self.codeRetour = apache.OK
		self.stream = False
		self.fichiersXML = []
		self.noeudsXML = []
		self.requetesSQL = []
		self.fichierXSLT = None
		self._resultat = []
		return

	def proceder(self):
		#on teste tout d'abord si le fichier existe demandé vraiement et est lisible
		if (os.access(self.fichierDemande + ".html", os.R_OK) == True):
			#Fichier existe... il faut le retourner directement
			self.ecrire(self.retournerContenuFichierExistant(self.fichierDemande + ".html"))
			self.typeMime = "text/html"
			self.codeRetour = apache.OK
			return

		elif (os.access(self.fichierDemande + ".xml", os.R_OK) == True):
			#si le fichier html existe pas, alors on regarde s'il existe un fichier xml
			# le fichier XML existe, on extrait le nom de sa feuille de style
			fichierXSLT = self. __extraireNomFeuilleStyle(self.fichierDemande + ".xml")
			if fichierXSLT == None:
				#aucune feuille de style, alors on ressort le fichier XML directement
				self.ecrire(self.retournerContenuFichierExistant(self.fichierDemande + ".xml"))
				self.typeMime = "text/xml"
				self.codeRetour = apache.OK
				return
				
			elif (os.access(self.repertoireTravail + fichierXSLT, os.R_OK) == True):
				#on a trouvé le nom de la feuille de style... reste plus qu'a effectuer la transformation
				#on verrifie quand meme que le fichier XSLT existe
				#ajout du fichier en question dans la liste des fichiers a traiter
				self.fichiersXML = self.fichiersXML + [self.fichierDemande + ".xml"]
				self.fichierXSLT = self.repertoireTravail + fichierXSLT
				#et effectuer la transformation, comme si ton avait des fichiers multiples et des requetes
				self.ecrire(self.effectuerTransformation())
				self.typeMime = "text/xml"
				self.codeRetour = apache.OK
				return
					
			else:
				self.ecrire("Erreur, fichier %s inexistant" % (self.repertoireTravail  + fichierXSLT))
				self.typeMime = "text/plain"
				self.codeRetour = apache.OK
				return
			
		else:
			#sinon (le fichier XML n'existe pas), alors ont retourne l'erreur 404
			self.codeRetour = apache.HTTP_NOT_FOUND
			return

	def retournerContenuFichierExistant(self, fichier):
		f = file(fichier)
		contenu = f.read()
		f.close()
		return contenu

	def effectuerTransformation(self):
		#A Faire : agrégation des fichiers XML, des requetes et du reste dans un seul document XML
		doc = self.agregation()
		import libxslt
		styleDoc = libxml2.parseFile(self.fichierXSLT)
		style = libxslt.parseStylesheetDoc(styleDoc)
		result = style.applyStylesheet(doc, None)
		stringval = style.saveResultToString(result)
		#style.saveResultToFilename("foo", result, 0)
		style.freeStylesheet()
		doc.freeDoc()
		result.freeDoc()
		return stringval
		
	def agregation(self):
		#faire agregation de fichiersXML, noeudsXML et requetesSQL dans un seul document DOM
		domDocument = libxml2.newDoc("1.0")
		racine = domDocument.newChild(None, u"page", None);
		
		if len(self.fichiersXML) > 0:
			racineFichiers = racine.newChild(None, u"fichiers", None)
			for fichier in self.fichiersXML:
				domFichier = libxml2.parseFile(fichier)
				racineFichier = domFichier.getRootElement()
				racineFichiers.addChild(racineFichier.docCopyNode(domDocument, True))
			return domDocument
		
		if len(self.requetesSQL) > 0:
			pass
			
		if len(self.noeudsXML) > 0:
			pass

	def __extraireNomFeuilleStyle(self, fichierXML):
		"""A partir d'un fichier XML, extrait le nom de la feuille de style XSLT associée via l'instruction de traitement"""
		def extraire_instruction_trairement_dom(doc):
			ctxt = doc.xpathNewContext()
			res = ctxt.xpathEval("//self::processing-instruction()");
			return res
		doc = libxml2.parseFile(fichierXML)
		pis = extraire_instruction_trairement_dom(doc)
		if (len(pis) > 0 and pis[0] != None):
			match = sre.match("href=['\"](.+?)['\"]", pis[0].getContent())
			return match.group(1);
		else:
			return None

	def ecrire(self, str):
		if self.stream:
			self.requete.write(str+"\n")
		else:
			self._resultat.append(str)

	def envoyer(self):
		if not self.stream:
			self.requete.write(string.join(self._resultat, "\n"))
	


#=- Module initialisation -=#
global conf
conf = ConfigParser.ConfigParser()
fichConf = __file__[:-2] + "conf"
conf.read(fichConf)

