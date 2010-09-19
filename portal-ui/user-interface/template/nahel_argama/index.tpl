<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN"
	"http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="de">
<head>
<meta http-equiv="Content-Type" content="application/xhtml+xml; charset=utf-8" />
<meta name="description" content="Lernfunk.de: Vorlesungsaufzeichnungen und Podcasts" />
<meta name="keywords" content="Lernfunk, Vorlesung, Vorlesungsaufzeichnung, Podcast, Dozent" />

<!-- autoinsert for includes (js-files, ...) -->
(:includes:)

<title>virtUOS::Lernfunk</title>
</head>
<body onload="init();">

<div id="wrapper">

<!-- ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~ header ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~ -->
	<div id="logobox">
		<a href="#"><img id="lflogo" src="(:tplpath:)/img/lflogo.png" alt="Lernfunk" /></a>
	</div>

	<div id="navbar">
		<div class="category" onclick="filterResults('series');">
			<div class="object_count" id="count_series" ></div>
			Veranstaltungen
		</div>
		<div class="category" onclick="filterResults('recordings');">
			<div class="object_count" id="count_recordings" ></div>
			Aufzeichnungen
		</div>
		<div class="category" onclick="filterResults('lecturer');">
			<div class="object_count" id="count_lecturer" ></div>
			Personen
		</div>
		<div class="category" onclick="filterResults('podcast');">
			<div class="object_count" id="count_podcast" ></div>
			Podcast
		</div>

		<div id="searchblock">
			<form action="javascript: triggerSearch();">
				<input type="text" id="search" name="search" value="Suche" 
					onfocus="clear_inp(this, 'Suche');" onblur="leave_inp(this, 'Suche');" />
				<input type="submit" value="suchen" id="search_submit" />
			</form>
		</div>
	</div>

<!-- ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~ title ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~ -->
	<h1 id="pagetitle">
		Willkommen auf Lernfunk.de
	</h1>

	<div id="titlebox_bottom"></div>

<div id="main">

<!-- ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~ content ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~ -->
	<div id="contentbox">
		<div id="toppager" class="pager"> </div>

		<div id="content">
			Startseite...
		</div>
		<div id="bottompager" class="pager"> </div>
	</div>
	</div>

<!-- ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~ footer ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~ -->
	<div id="footer">
		<div id="addrblock">
			<div class="virtuos_name">virtUOS</div>
			<div>Zentrum für Informationsmanagement</div>
			<div>und virtuelle Lehre</div>
			<div>Heger-Tor-Wall 12</div>
			<div>49074 Osnabrück</div>
		</div>
		<div id="bottomlinkblock">
			<div><a class="bottomlink" href="#cmd=static&amp;title=Das Projekt&amp;std=1&amp;p=about">Über das Projekt</a></div>
			<div><a class="bottomlink" href="#cmd=static&amp;title=Kontakt&amp;std=1&amp;p=kontakt">Kontakt</a></div>
			<div><a class="bottomlink" href="#cmd=static&amp;title=Impressum&amp;std=1&amp;p=impressum">Impressum</a></div>
			<div><a class="bottomlink" href="#">lernfunk im Blog</a></div>
			<div><a class="bottomlink" href="#cmd=static&amp;title=FAQ&amp;std=1&amp;p=faq">FAQ zu Vorlesungsaufzeichnungen</a></div>
		</div>
		<div id="bottomlogoblock">
			<a href="http://www.uos.de/" title="Uni Osnabrück"><img src="(:tplpath:)/img/unilogo.png" alt="Uni Osnabrück" /></a>
			<a href="http://www.fh-osnabrueck.de/" title="FH Osnabrück"><img src="(:tplpath:)/img/fhlogo.png" alt="FH Osnabrück" /></a>
		</div>
	</div>

</div>

</body>
</html>
