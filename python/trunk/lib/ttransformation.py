# -*- coding: UTF-8 -*-

import os
import sys
import sre
import libxml2
import imp
import new
from mod_python import apache

import configuration

class TTransformation:
	def __init__(self, req):
		#f = open("/tmp/falstelo.log","w")

		self.requeteApache = req
		self.typeMime = configuration.typeMime
		#le fichier demandé est forcement un .html
		#on lui ote donc ce ".html" final
		self.fichierDemande = self.requeteApache.filename[0:len(self.requeteApache.filename)-5]
		#on extrait du fichier demandé le repertoire dans lequel on travaille (le lien vers la feuille de style XSLT sera en fonction de ce repertoire
		#f.write("URI="+req.uri+"\nUnparsed URI="+req.unparsed_uri+"\nFilename="+req.filename+"\nrepertoireTravail="+sre.match("(/.*/)", req.filename).group(0)+"\n")
		#f.close()
		self.repertoireTravail = sre.match("(/.*/)", req.filename).group(0)
		
		self.fichiersXML = []
		self.noeudsXML = []
		self.requetesSQL = []
		self.fichierXSLT = None
		self.resultat = ""
		return

	def proceder(self):
	
		#on teste tout d'abord si le fichier existe demandé vraiement et est lisible
		if (os.access(self.fichierDemande + ".html", os.R_OK) == True):
			#Fichier existe... il faut le retourner directement
			self.resultat += self.retournerContenuFichierExistant(self.fichierDemande + ".html")
			self.typeMime = "text/html"
			self.codeRetour = apache.OK
			return

		elif (os.access(self.fichierDemande + ".xml", os.R_OK) == True):
			#si le fichier html existe pas, alors on regarde s'il existe un fichier xml
			#self.resultat += "Le fichier %s existe..." % (self.fichierDemande + ".xml")
			# le fichier XML existe, on extrait le nom de sa feuille de style
			fichierXSLT = self. __extraireNomFeuilleStyle(self.fichierDemande + ".xml")
			if fichierXSLT == None:
				#aucune feuille de style, alors on ressort le fichier XML directement
				self.resultat += self.retournerContenuFichierExistant(self.fichierDemande + ".xml")
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
				self.resultat += self.effectuerTransformation()
				self.typeMime = "text/xml"
				self.codeRetour = apache.OK
				return
					
			else:
				self.resultat += "Errreur, fichier %s inexistant" % (self.repertoireTravail  + fichierXSLT)
				self.typeMime = "text/plain"
				self.codeRetour = apache.OK
				return
			
		elif os.access(self.fichierDemande + ".py", os.R_OK) == True :
			#sinon (le fichier XML n'existe pas), alors on tente un dernier essai avec un fichier py, que l'on importe...
			self.resultat += "On a trouvé un fichier python... Ce travail reste à faire... : " + self.fichierDemande
			self.typeMime = "text/plain"
			self.codeRetour = apache.OK
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
