import os
from Crypto.Cipher import AES
import base64
from time import time
import json
import Cookie

user = { 'student' : 'secret' }

# Time in seconds until the session expires
login_time = 60 * 60 * 2

__dir__         = os.path.dirname(__file__)
__seriesdir__   = os.path.join(__dir__,"series/")
__episodesdir__ = os.path.join(__dir__,"episodes/")


''' Crypto related stuff --------------------------------------------------- '''
''' Crypto related stuff --------------------------------------------------- '''
''' Crypto related stuff --------------------------------------------------- '''

# the block size for the cipher object; must be 16, 24, or 32 for AES
BLOCK_SIZE = 32

# the character used for padding--with a block cipher such as AES, the value
# you encrypt must be a multiple of BLOCK_SIZE in length.  This character is
# used to ensure that your value is always a multiple of BLOCK_SIZE
PADDING = '{'

# one-liner to sufficiently pad the text to be encrypted
pad = lambda s: s + (BLOCK_SIZE - len(s) % BLOCK_SIZE) * PADDING

# one-liners to encrypt/encode and decrypt/decode a string
# encrypt with AES, encode with base64
EncodeAES = lambda c, s: base64.b64encode(c.encrypt(pad(s)))
DecodeAES = lambda c, e: c.decrypt(base64.b64decode(e)).rstrip(PADDING)

# generate a random secret key
#secret = os.urandom(BLOCK_SIZE)
secret = '\xdc\xe3(\xce\x07\xf2p\xae\xd0\xe8\xf2\xd6E\x91\xb3\xd8\xc0\xd2\xcd\x83,\x8e\xd79:(m\xcan\xae\xa9\xfb'

# create a cipher object using the random secret
cipher = AES.new(secret)

''' Crypto related stuff --------------------------------------------------- '''
''' Crypto related stuff --------------------------------------------------- '''
''' Crypto related stuff --------------------------------------------------- '''

def get_xml(path,req,q=None,id=None,offset=0,limit=999999):
	logged_in = None
	cookie = req.headers_in.get('Cookie')
	if cookie:
		C = Cookie.SimpleCookie()
		C.load(cookie)
		cookie = C.get('JSESSIONID').value
		try:
			username, password, expires = json.loads(DecodeAES( cipher, cookie ))
			logged_in = ( username, password, expires ) if expires > time() else None
		except:
			pass

	limit  = int(limit)
	offset = int(offset)
	files = [ (f, '1/' + f) for f in os.listdir(path + '1/') ]
	if logged_in:
		files += [ (f, '2/' + f) for f in os.listdir(path + '2/') ]
	files.sort(reverse=True)

	head  = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
	head += '<ns2:search-results xmlns:ns2="http://search.opencastproject.org" ' \
			'xmlns="http://mediapackage.opencastproject.org" '
	body = ''

	if id:
		for n,s in files:
			if s.endswith('_' + id):
				f = open( path + s, 'r' )
				body += f.read()
				f.close()
		head += 'searchTime="1" total="%(total)d" limit="%(limit)d" offset="%(offset)d">' % \
				{ 'total': (1 if body else 0) , 'limit':limit, 'offset':0 }
	elif not q:
		head += 'searchTime="1" total="%(total)d" limit="%(limit)d" offset="%(offset)d">' % \
				{ 'total':len(files), 'limit':limit, 'offset':offset }
		limit = min( limit, len(files) - offset )
		for n,s in files[offset:offset+limit]:
			f = open( path + s, 'r' )
			body += f.read()
			f.close()
	else:
		count = 0
		for n,s in files:
			f = open( path + s, 'r' )
			res = f.read()
			f.close()
			if q.lower() in res.lower():
				if count >= offset and count < offset + limit:
					body += res
				count += 1
		limit = min( limit, count - offset )
		head += 'searchTime="1" total="%(total)d" limit="%(limit)d" offset="%(offset)d">' % \
				{ 'total':count, 'limit':limit, 'offset':offset }
		
	if logged_in:
		head += '<query>*:* AND (oc_acl_read:ROLE_USER)</query>'
	else:
		head += '<query>*:* AND (oc_acl_read:ROLE_ANONYMOUS)</query>'

	foot  = '</ns2:search-results>'

	req.content_type = 'application/xml'
	return head + body + foot


class series:
	def xml(self,req,q=None,offset=0,limit=999999,id=None):
		if id:
			q = ' id="' + id + '"'
		return get_xml(__seriesdir__,req,q,id,offset,limit)


class episode:
	def xml(self,req,q=None,offset=0,limit=999999,lfunk=None,id=None):
		if q and lfunk:
			q = '<series>' + q + '</series>'
		if id:
			q = ' id="' + id + '"'
		return get_xml(__episodesdir__,req,q,id,offset,limit)


class search:
	series  = series()
	episode = episode()


series  = series()
episode = episode()
search  = search()


def index(req):
	req.content_type = 'text/plain'
	return "success"

def j_spring_security_check(req,j_username, j_password):
	if not j_username in user.keys():
		req.status = 401
		return 'Invalid username'
	if user[j_username] != j_password:
		req.status = 401
		return 'Invalid password'

	# success

	# generate data
	expires = int(time()) + login_time
	data = json.dumps( [ j_username, j_password, expires ] )

	# encrypt data
	encdata = EncodeAES( cipher, data )
	req.headers_out['Set-Cookie'] = 'JSESSIONID=%s;Path=/' % encdata
	return ''
