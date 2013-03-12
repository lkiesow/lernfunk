#!/bin/env python26
# -*- coding: utf-8 -*-

import urllib2
import os
import re
import time
import datetime
import MySQLdb as mdb

# List of matterhorn search services:
services = [
		'http://video.virtuos.uos.de:8080/search/series.xml?limit=999999&episodes=true&series=true',
		'http://video2.virtuos.uos.de:8080/search/series.xml?limit=999999&episodes=true&series=true'
	]

__DBSERVER__ = 'mysql5.serv.uni-osnabrueck.de'
__DBUSER__   = 'vader'
__DBPASSWD__ = 'vr2go!_st'
__DBNAME__   = 'lernfunk'

__dir__         = os.path.dirname(__file__)
__seriesdir__   = os.path.join(__dir__,"series/")
__episodesdir__ = os.path.join(__dir__,"episodes/")


def _main():
	for url in services:
		f = urllib2.urlopen(url)
		seriesxml = f.read()

		results = seriesxml.split('<result ')
		results.pop(0)
		results[-1] = results[-1].split('</ns2:search-results>')[0]

		resultdict = {}
		for r in results:
			resultdict[ r.split('id="',1)[1].split('"', 1)[0] ] = '<result ' + r

		con    = None
		try:
			con = mdb.connect( __DBSERVER__, __DBUSER__, __DBPASSWD__, __DBNAME__ )

			cur = con.cursor()
			for k,v in resultdict.items():
				id = k.replace('"','').replace(';','')
				if '<mediapackage ' in v:
					cur.execute('SELECT access_id FROM prepared_mediaobject WHERE lrs_object_id = "%s"' % id)
					access = cur.fetchone()
					access = str(access[0]) if access else str(access)
					if not os.path.isdir(__episodesdir__+access):
						os.makedirs(__episodesdir__+access)
					modified = re.search('<modified>(.*)<\/modified>', v)
					try:
						modified = modified.group(1)
					except IndexError:
						modified = '1970-01-01T00:00:00.000+00:00'
					f = open(__episodesdir__+access+'/'+modified+'_'+k,"w")
					f.write(v)
					f.close()
				else:
					cur.execute('SELECT access_id FROM series WHERE lrs_series_id = "%s"' % id )
					access = cur.fetchone()
					access = str(access[0]) if access else str(access)
					if not os.path.isdir(__seriesdir__+access):
						os.makedirs(__seriesdir__+access)
					modified = re.search('<modified>(.*)<\/modified>', v)
					try:
						modified = modified.group(1)
					except IndexError:
						modified = '1970-01-01T00:00:00.000+00:00'
					f = open(__seriesdir__+access+'/'+modified+'_'+k,"w")
					f.write(v)
					f.close()
		finally:    
			if con:    
				con.close()


	# delete old files
	for level in os.listdir(__seriesdir__):
		for id in os.listdir(__seriesdir__ + level):
			# Check if file is older than 10 minutes:
			if time.time() - os.path.getmtime(__seriesdir__ + level + '/' + id) > 600:
				os.remove( __seriesdir__ + level + '/' + id )

	# Same for episodes
	for level in os.listdir(__episodesdir__):
		for id in os.listdir(__episodesdir__ + level):
			# Check if file is older than 10 minutes:
			if time.time() - os.path.getmtime(__episodesdir__ + level + '/' + id) > 600:
				os.remove( __episodesdir__ + level + '/' + id )


def index(req):
	req.content_type = 'text/plain'
	# Get file date
	last_modified = 0
	for level in os.listdir(__seriesdir__):
		for id in os.listdir(__seriesdir__ + level):
			last_modified = max( last_modified, os.path.getmtime(__seriesdir__ + level + '/' + id) )
	for level in os.listdir(__episodesdir__):
		for id in os.listdir(__episodesdir__ + level):
			last_modified = max( last_modified, os.path.getmtime(__episodesdir__ + level + '/' + id) )
	return 'Last update:\n' \
			+ datetime.datetime.fromtimestamp(last_modified).strftime('%Y-%m-%d %H:%M:%S')


if __name__ == "__main__":
	_main()
