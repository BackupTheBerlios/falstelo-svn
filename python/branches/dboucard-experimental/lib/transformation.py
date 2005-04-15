# -*- coding: UTF-8 -*-
"""
Python-Falstelo -- Un moteur XML/XSLT en mod_pyhton pour Apache
Auteurs :
  - Nicolas Bouillon
  - Damien Boucard
Python-Falstelo est fourni sans aucune garantie.
Pour plus de details, voyez le fichier LICENCE.txt.
Ce programme est libre et vous etes encourage à le redistribuer sous les conditions de la CECILL.
"""

from mod_python import apache, util, Session
import os, sre, string
import libxml2, libxslt
#~ import traceback

class Ttransformation:
	"""
	Classe generique par defaut de transformation Falstelo.
	Cette classe doit etre un ascendant de toute classe de page dynamique
	ou de transformation.
	"""
	def __init__(self, req, config, redirect=None):
		# contient la requete fournie par apache
		self.requete = req
		# contient les parametres issus du fichier de conf
		self.conf = config
		# chargement du typeMime par defaut
		if self.conf.has_option("Prefs","typeMime"):
			self.typeMime = self.conf.get("Prefs","typeMime")
		else:
			self.typeMime = "text/plain"

		# on extrait du fichier demande le repertoire dans lequel on
		# travaille (le lien vers la feuille de style XSLT sera en
		# fonction de ce repertoire
		self.repertoireTravail = sre.match("(/.*/)", req.filename).group(0)
		self.session = {}
		self.variables = {}

		# chemin absolu de la racine du site
		self.base = self.conf.get("Chemins", "cheminBase")
		# chemin_relatif est le chemin vers la racine du site
		# a partir du repertoire du fichier demande
		self.cheminRelatif = "./" + ("../" * (self.repertoireTravail.count("/") - self.base.count("/") - 1))

		if redirect==None:
			#le fichier demande est forcement un .html
			#on lui ote donc ce ".html" final
			self.fichierDemande = self.requete.filename[0:len(self.requete.filename)-5]
			# on charge la session
			self.session = Session.Session(req)
			self.session.load()

			# on charge les variables de requete HTTP
			# post est prioritaire par rapport a get
			get_args = ""
			if req.args != None:
				get_args = req.args
			post_args = req.read()
			self.variables = util.parse_qs(get_args)
			self.variables.update(util.parse_qs(post_args))
		else:
			# une redirection a ete demandee par le handler
			self.repertoireTravail = self.base + "/"
			self.fichierDemande = self.base + "/" + redirect
		
		# code de retour HTTP (initialisation par defaut)
		self.codeRetour = apache.OK
		# indique si les donnees sont envoyees au fur et a mesure
		# ou tout d'un bloc
		self.stream = False
		# liste des fichiers XML statique a charger
		self.fichiersXML = []
		# liste des noeuds XML a agreger
		self.noeudsXML = []
		# dictionnaire des requetes SQL
		self.requetesSQL = {}
		# dictionnaire des resultats aux requetes SQL correspondantes
		self.resultatsSQL = {}
		# fichier XSLT a utiliser
		self.fichierXSLT = None
		# paramettres XSLT a envoyer a la moulinette XSLT
		self.parametresXSLT = {"chemin_relatif": "'%s'" % (self.cheminRelatif)}
		# nom de la balise racine du DOM
		self.racineXML = u"page"
		# tampon des donnees a envoyer
		self._resultat = []
		# module de base de donnees API-DB utilise
		self.bdd = None
		# connexion active a la base de donnees
		self.connexion = None

	def acceder(self):
		"""
		Indique si le moteur genere la page ou renvoie une erreur 401
		(acces non autorise). A surcharger pour gerer vos droits
		d'acces par exemple.
		"""
		return True

	def proceder(self):
		"""
		Methode indispensable car elle est directement appelee par le
		handler mod_python. C'est elle qui lance tout le processus de
		rendu. Il est deconseille de surcharger cette methode sauf
		pour des cas tres particuliers.
		"""
		if (self.acceder()):	
			self.ouvrirBDD()
			self.transformer()
			self.fermerBDD()
			if self.session!={}: self.session.save()
		else:
			self.codeRetour = apache.HTTP_UNAUTHORIZED

	def ouvrirBDD(self):
		"""
		Methode permettant de se connecter a la base de donnees et
		d'executer les requetes de self.requetesSQL. Il est deconseille
		de surcharger cette methode, mais il est conseille de l'appeler
		dans votre methode proceder() si vous surchagez cette derniere.
		"""
		sql = self.requetesSQL
		if len(sql) == 0 or not self.conf.has_option("Base","type"):
			return
		
		# import de module de base de donnees compatible API-DB
		bdd = __import__(self.conf.get("Base","type"), globals())
		bdd = __import__(self.conf.get("Base","type")+".cursors", globals())

		# recuperation des parametres de connexion
		host, user, passwd, db = [None] * 4
		port = 0
		if self.conf.has_option("Base","hote"):
			host = self.conf.get("Base","hote")
		if self.conf.has_option("Base","utilisateur"):
			user = self.conf.get("Base","utilisateur")
		if self.conf.has_option("Base","motpasse"):
			passwd = self.conf.get("Base","motpasse")
		if self.conf.has_option("Base","nombase"):
			db = self.conf.get("Base","nombase")
		if self.conf.has_option("Base","port"):
			port = int(self.conf.get("Base","port"))
		# connexion a la base de donnees
		self.connexion = bdd.connect(host, user, passwd, db, port)

		# creation d'un curseur de parcours de resultat SQL
		curseur = self.connexion.cursor(bdd.cursors.DictCursor)
		# boucle d'execution des requetes SQL
		for clef in sql.keys():
			curseur.execute(sql[clef])
			self.resultatsSQL[clef] = curseur.fetchall()
		# fermeture du curseur
		curseur.close()
		#~ self.ecrire(str(self.resultatsSQL))
		#~ self.envoyer()


	def fermerBDD(self):
		"""
		Permet de fermer la connexion active a la base de donnees. Vous
		pouvez utiliser cette methode si vous desirez par exemple
		conserver la connexion dans votre session.
		"""
		if (self.connexion != None):
			self.connexion.close()
		

	def transformer(self):
		"""
		Fonction de detection des fichiers statiques par defaut. A
		surcharger si vous desirer changer le traitement par defaut ou
		a garder pour conserver la detection classique des pages
		statiques HTML, XML et XSLT dans vos propres scripts de
		transformation.
		"""
		# on teste tout d'abord si le fichier existe demande vraiement
		# et est lisible
		if (os.access(self.fichierDemande + ".html", os.R_OK) == True):
			# le fichier existe... il faut le retourner directement
			self.ecrire(self.retournerContenuFichierExistant(self.fichierDemande + ".html"))
			self.typeMime = "text/html"
			self.codeRetour = apache.OK
			return

		# si le fichier html existe pas, alors on regarde s'il
		# existe un fichier xml
		elif (os.access(self.fichierDemande + ".xml", os.R_OK) == True):
			# le fichier XML existe, on extrait le nom de sa
			# feuille de style
			fichierXSLT = self. __extraireNomFeuilleStyle(self.fichierDemande + ".xml")
			if fichierXSLT == None:
				# aucune feuille de style, alors on ressort le
				# fichier XML directement
				self.ecrire(self.retournerContenuFichierExistant(self.fichierDemande + ".xml"))
				self.typeMime = "text/xml"
				self.codeRetour = apache.OK
				return
				
			# on a trouve le nom de la feuille de style...
			# reste plus qu'a effectuer la transformation
			# on verrifie quand meme que le fichier XSLT existe
			elif (os.access(self.repertoireTravail + fichierXSLT, os.R_OK) == True):
				# ajout du fichier en question dans la liste
				# des fichiers a traiter
				self.fichiersXML = self.fichiersXML + [self.fichierDemande + ".xml"]
				self.fichierXSLT = self.repertoireTravail + fichierXSLT
				# et effectuer la transformation, comme si on
				# avait des fichiers multiples et des requetes
				self.ecrire(self.effectuerTransformation())
				self.codeRetour = apache.OK
				return
					
			else:
				self.ecrire("Erreur, fichier %s inexistant" % (self.repertoireTravail  + fichierXSLT))
				self.typeMime = "text/plain"
				self.codeRetour = apache.OK
				return
			
		else:
			# sinon (le fichier XML n'existe pas), alors on
			# retourne l'erreur 404
			self.codeRetour = apache.HTTP_NOT_FOUND
			return

	def retournerContenuFichierExistant(fichier):
		"""
		Methode utilitaire. Lit le contenu d'un fichier pour etre
		envoye au client HTTP. Par exemple pour un fichier HTML.
		"""
		f = file(fichier)
		contenu = f.read()
		f.close()
		return contenu
	retournerContenuFichierExistant = staticmethod(retournerContenuFichierExistant)

	def effectuerTransformation(self):
		"""
		Envoie le document XML a la moulinette XSLT. Normalement cette
		methode n'a pas a etre surchargee.
		"""
		doc = self.agregation()
		if self.fichierXSLT !=None:
			#~ libxml2.debugMemory(1)
			styleDoc = libxml2.parseFile(self.fichierXSLT)
			style = libxslt.parseStylesheetDoc(styleDoc)
			result = style.applyStylesheet(doc, self.parametresXSLT)
			stringval = style.saveResultToString(result)
			# Mode debug pour afficher ce que retourne XSLT :
			#~ style.saveResultToFilename("/tmp/foo", result, 0)
			style.freeStylesheet()
			doc.freeDoc()
			result.freeDoc()
			return stringval
		else:
			return str(doc)
		
	def agregerSession(self, racine, domDocument):
		"""
		Integre le dictionnaire de session au document DOM.
		A surcharger pour en modifier le comportement.
		Par exemple si vous ne voulez pas de session dans votre DOM,
		mettez simplement :
		pass
		"""
		racineSession = racine.newChild(None, u"session", None)
		for var in self.session:
			racineSession.newChild(None, var, str(self.session[var]))

	def agregerFichiersXML(self, racine, domDocument):
		"""
		Integre les fichiers XML statiques au document DOM.
		Non necessaire a surcharger, sauf cas particuliers.
		"""
		racineFichiers = racine.newChild(None, u"fichiers", None)
		for fichier in self.fichiersXML:
			domFichier = libxml2.parseFile(fichier)
			racineFichier = domFichier.getRootElement()
			racineFichiers.addChild(racineFichier.docCopyNode(domDocument, True))

	def agregerResultatsSQL(self, racine, domDocument):
		"""
		Integre le resultats des requetes SQL au document DOM.
		A surcharger pour en modifier le comportement.
		"""
		racineRequetes = racine.newChild(None, u"requetes", None)
		racineRequetes.setProp(u"module",self.conf.get("Base","type"))
		for clef in self.resultatsSQL.keys():
			req = racineRequetes.newChild(None, u"resultat", None)
			req.setProp(u"nom", clef)
			req.newChild(None, u"sql",self.requetesSQL[clef])
			for ligne in self.resultatsSQL[clef]:
				enreg = req.newChild(None, u"enregistrement", None)
				for col in ligne.keys():
					coltype = str(type(ligne[col]))[7:-2]
					if (coltype == "datetime.datetime"):
						val = ligne[col]
						champ = enreg.newChild(None, col, None)
						champ.setProp(u"type","datetime")
						dt = champ.newChild(None, u"datetime", None)
						dt.setProp(u"annee","%d" %val.year)
						dt.setProp(u"mois","%02d" %val.month)
						dt.setProp(u"jour","%02d" %val.day)
						dt.setProp(u"heure","%02d" %val.hour)
						dt.setProp(u"minute","%02d" %val.minute)
						dt.setProp(u"seconde","%02d" %val.second)
					else:
						champ = enreg.newChild(None, col, str(ligne[col]))
						champ.setProp(u"type",coltype)

	def agregation(self):
		"""
		fait l'agregation de fichiersXML, noeudsXML, resultatsSQL et
		session dans un seul document DOM. Ne devrait pas etre
		surchargee.
		"""
		domDocument = libxml2.newDoc("1.0")
		racine = domDocument.newChild(None, self.racineXML, None);
		
		if len(self.session) > 0:
			self.agregerSession(racine, domDocument)
		if len(self.fichiersXML) > 0:
			self.agregerFichiersXML(racine, domDocument)
		if len(self.resultatsSQL) > 0:
			self.agregerResultatsSQL(racine, domDocument)
		if len(self.noeudsXML) > 0:
			# [TODO]
			pass
		# Decommenter cette ligne pour activer le mode debug
		# [TODO] gerer ce mode debug avec un flag dans la conf
		#~ res = domDocument.saveFile("/tmp/flstXML.xml")
		return domDocument		

	def __extraireNomFeuilleStyle(fichierXML):
		"""
		Methode utilitaire. A partir d'un fichier XML, extrait le nom
		de la feuille de style XSLT associee via l'instruction de
		traitement
		"""

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
	__extraireNomFeuilleStyle = staticmethod(__extraireNomFeuilleStyle)

	def ecrire(self, str, binary=False):
		"""
		Selon la valeur de self.stream, soit envoie de la donnee dans
		le tampon (self.__resultat), soit directement au client HTTP
		Le parametre optionnel binary indique si c'est un envoie
		binaire (dans ce cas, pas de retour chariot).
		"""
		if self.stream:
			if binary:
				self.requete.write(str)
			else:
				self.requete.write(str+"\n")
		else:
			self._resultat.append(str)

	def envoyer(self):
		"""
		Permet de vider le tampon et d'envoyer les donnees au client
		HTTP. Est appelee automatiquement par le handler mod_python,
		mais peut etre appelee explicitement pour eviter de surcharger
		le tampon ou pour limiter le temps d'attente devant une page
		blanche.
		"""
		if not self.stream:
			self.requete.write(string.join(self._resultat, "\n"))
			self._resultat = []

	def rediriger(self, location):
		"""
		Permet de faire une redirection de page. Utilise la fonction
		standard de mod_python mais est interfacee pour eviter
		d'importer le module nessesaire et pour penser a sauvegarder
		la session. Peut etre surchargee au besoin.
		"""
		if str(self.session)!=str({}):
			self.session.save()
		util.redirect(self.requete, location)
