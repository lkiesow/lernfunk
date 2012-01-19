#/bin/bash!

cd `dirname "$0"`

echo 'cleaning up release folder...'
rm -rf release
mkdir release

cd dev/js
./build.sh
cd ../..

echo 'copy template files...'
mkdir release/templates
cp dev/templates/feededitor.html          release/templates
cp dev/templates/main.html                release/templates
cp dev/templates/mediaobjecteditor.html   release/templates
cp dev/templates/playlisteditor.html      release/templates
cp dev/templates/playlistentryeditor.html release/templates
cp dev/templates/recordeditor.html        release/templates
cp dev/templates/searchresult.html        release/templates
cp dev/templates/serieseditor.html        release/templates
cp dev/templates/seriescreator.html       release/templates

echo 'copy gfx files...'
mkdir release/gfx
cp dev/gfx/*  release/gfx

echo 'copy php files...'
mkdir release/php
cp dev/php/*.php     release/php
#cp dev/php/config.php      release/php
#cp dev/php/recordtypes.php release/php
#cp dev/php/upload.php      release/php

echo 'copy js files...'
mkdir release/js
mkdir release/js/imports
mkdir release/js/bin
cp -r dev/js/imports/*   release/js/imports
cp dev/js/bin/lfadmin.js release/js/bin

echo 'copy general files...'
cp dev/index.php release
cp dev/.htaccess release
cp dev/.htusers  release

echo 'writing build time...'
date > release/buildtime.txt
