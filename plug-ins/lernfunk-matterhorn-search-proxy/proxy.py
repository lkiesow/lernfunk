import os


__dir__         = os.path.dirname(__file__)
__seriesdir__   = os.path.join(__dir__,"series/1/")
__episodesdir__ = os.path.join(__dir__,"episodes/1/")


def get_xml(path,req,q=None,offset=0,limit=999999):
	limit  = int(limit)
	offset = int(offset)
	files = os.listdir(path)

	head  = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
	head += '<ns2:search-results xmlns:ns2="http://search.opencastproject.org" ' \
			'xmlns:ns3="http://mediapackage.opencastproject.org" '
	body = ''

	if not q:
		head += 'searchTime="1" total="%(total)d" limit="%(limit)d" offset="%(offset)d">' % \
				{ 'total':len(files), 'limit':limit, 'offset':offset }
		limit = min( limit, len(files) - offset )
		for s in files[offset:offset+limit]:
			f = open( path + s, 'r' )
			body += f.read()
			f.close()
	else:
		count = 0
		for s in files:
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
		
	head += '<query>*:*</query>';
	foot  = '</ns2:search-results>'

	req.content_type = 'application/xml'
	return head + body + foot


class series:
	def xml(self,req,q=None,offset=0,limit=999999,id=None):
		if id:
			q = ' id="' + id + '"'
		return get_xml(__seriesdir__,req,q,offset,limit)


class episode:
	def xml(self,req,q=None,offset=0,limit=999999,lfunk=None,id=None):
		if q and lfunk:
			q = '<series>' + q + '</series>'
		if id:
			q = ' id="' + id + '"'
		return get_xml(__episodesdir__,req,q,offset,limit)


class search:
	series  = series()
	episode = episode()


series  = series()
episode = episode()
search  = search()


def index(req):
	req.content_type = 'text/plain'
	return "success"
