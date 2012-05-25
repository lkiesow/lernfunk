#!/bin/env python

import os
import shutil
import io
import datetime
import sys

os.chdir( os.getcwd() )

print( 'cleaning up release folder...' )
try:
	shutil.rmtree( 'release' )
except OSError:
	pass

os.makedirs( 'release' )

print( 'cleaning up JavaScript bin folder...' )
try:
	shutil.rmtree( 'dev/js/bin' )
except OSError:
	pass

os.makedirs( 'dev/js/bin' )


jsfile =  '/*****************************************************************************/\n'
jsfile = '/* IMPORTANT: DO NOT EDIT THIS FILE!                                         */\n'
jsfile = '/* EDIT THE FILES IN THE SRC FOLDER INSTEAD AND USE THE BUILD SCRIPT TO      */\n'
jsfile = '/* GENERATE THIS FILE                                                        */\n'
jsfile = '/*****************************************************************************/\n\n'

f = io.open( 'dev/js/header', 'r' )
jsfile += f.read()
f = io.open( 'dev/js/src/general.js', 'r' )
jsfile += f.read()
f = io.open( 'dev/js/src/feededitor.js', 'r' )
jsfile += f.read()
f = io.open( 'dev/js/src/mediaobjecteditor.js', 'r' )
jsfile += f.read()
f = io.open( 'dev/js/src/playlisteditor.js', 'r' )
jsfile += f.read()
f = io.open( 'dev/js/src/recordeditor.js', 'r' )
jsfile += f.read()
f = io.open( 'dev/js/src/serieseditor.js', 'r' )
jsfile += f.read()
f = io.open( 'dev/js/src/serieseditor.js', 'r' )
jsfile += f.read()

f = open( 'dev/js/bin/lfadmin.js', 'wb' )
if sys.version_info < (3, 0):
	f.write( jsfile )
else:
	f.write( bytes(jsfile, 'UTF-8') )

print( 'copy template files...' )
shutil.copytree( 'dev/templates', 'release/templates' )

print( 'copy gfx files...' )
shutil.copytree( 'dev/gfx', 'release/gfx' )

print( 'copy php files...' )
shutil.copytree( 'dev/php', 'release/php' )

print( 'copy js files...' )
os.makedirs( 'release/js' )

shutil.copytree( 'dev/js/imports', 'release/js/imports' )
shutil.copytree( 'dev/js/bin',     'release/js/bin' )

print( 'copy general files...' )
shutil.copy( 'dev/index.php', 'release' )

print( 'writing build time...' )
f = open( 'release/buildtime.txt', 'wb' )
if sys.version_info < (3, 0):
	f.write( str(datetime.datetime.now()) )
else:
	f.write( bytes(str(datetime.datetime.now()), 'UTF-8') )

print( "Please remember to \033[1mupdate the php/config.php!\033[0m" )
