# -*- coding: UTF-8 -*-

from mod_python import apache
import os
import lib.ttransformation

def handler(req):
	t = lib.ttransformation.TTransformation(req)
	t.proceder()
	req.content_type = t.typeMime
	req.write(t.resultat)
	return t.codeRetour
