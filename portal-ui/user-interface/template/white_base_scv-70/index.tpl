<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN"
	"http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="de"> <head>
<meta http-equiv="Content-Type" content="application/xhtml+xml; charset=utf-8" />
<meta name="description" content="Lernfunk.de: Vorlesungsaufzeichnungen und Podcasts" />
<meta name="keywords" content="Lernfunk, Vorlesung, Vorlesungsaufzeichnung, Podcast, Dozent" />


<!-- jQuery UI -->
<link type="text/css" href="./css/imports/jquery/jquery-ui-1.7.2.custom.css" rel="Stylesheet" />
<script type="text/javascript" src="./js/imports/jquery/jquery-1.3.2.min.js"></script>
<script type="text/javascript" src="./js/imports/jquery/jquery-ui-1.7.2.custom.min.js"></script>

<script type="text/javascript" src="./js/imports/jquery-json/jquery.json-2.2.min.js"></script>
<script type="text/javascript" src="./js/imports/jquery-hashchange/jquery.ba-hashchange.min.js"></script>
<script type="text/javascript" src="./js/imports/jquery-bbq/jquery.ba-bbq.min.js"></script>

<!-- fancybox -->
<script type="text/javascript" src="./js/imports/jquery-fancybox/jquery.mousewheel-3.0.2.pack.js"></script>
<script type="text/javascript" src="./js/imports/jquery-fancybox/jquery.fancybox-1.3.1.js"></script>
<link rel="stylesheet" type="text/css" href="./js/imports/jquery-fancybox/jquery.fancybox-1.3.1.css" media="screen" />

<link rel="stylesheet" type="text/css" href="css/style.css" />
<script type="text/javascript" src="./js/cfg.js"></script>
<script type="text/javascript" src="./js/tpl.js"></script>
<script type="text/javascript" src="./js/func.js"></script>

<title>virtUOS::Lernfunk</title>
</head>
<body onload="init();">

<div id="wrapper">

  <div id="topbar"><img id="tophr" src="./img/topbar.png" alt="topmost horizontal ruler" /></div>

<!-- ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~ header ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~ -->
  <div id="header">
		<div id="logobox">
			<a href="#"><img id="lflogo" src="./img/lflogo.png" alt="Lernfunk" /></a>
		</div>
		<div id="titlebox">
			<h1 id="pagetitle">
				Willkommen auf Lernfunk.de
			</h1>
			<div id="titlebox_bottom">
			</div>
		</div>
  </div>

	<div id="main">

<!-- ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~ left ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~ -->
		<div id="leftblock">
			<div id="searchblock">
				<form action="javascript: triggerSearch();">
					<div>
						<input type="text" id="search" name="search" value="Suche" 
							onfocus="clear_inp(this, 'Suche');" onblur="leave_inp(this, 'Suche');" />
					<!--
					</div>
					<div>
						<input type="text" id="department_search" name="department_search" value="Fachbereich" 
							onfocus="clear_inp(this, 'Fachbereich'); trigger_dep_select_hide(false);" 
							onblur="leave_inp(this, 'Fachbereich'); trigger_dep_select_hide(true);" />
						<input type="button" id="trigger_dep_select_btn" value="..." 
							onclick="trigger_department_selection();"
							onfocus="trigger_dep_select_hide(false)" 
							onblur=" trigger_dep_select_hide(true);" />
					-->
						<input type="submit" value="suchen" id="search_submit" />
					</div>
					<!--

					<div id="department_selection">
					-->
						<!--<div class="select_category">cat</div>-->
						<!--<div class="select" onclick=" select_department( this );">FB</div>-->
					<!--
						<div class="select" onclick="select_department( this );">Alle Fachbereiche</div>
					</div>
					-->
				</form>
			</div>
			<div id="searchcatblock">
				<div class="category" onclick="filterResults('series');">
					<div class="object_count" id="count_series" ></div>
					Veranstaltungen
				</div>
				<div class="category" onclick="filterResults('recordings');">
					<div class="object_count" id="count_recordings" ></div>
					Aufzeichnungen
				</div>
				<!-- Inhalte ( > v1.0 ) -->
				<!-- old:
				<div class="category" onclick="filterResults('video');">
					<div class="object_count" id="count_video" ></div>
					Videos
				</div>
				<div class="category" onclick="filterResults('slides');">
					<div class="object_count" id="count_slides" ></div>
					Folien
				</div>
				-->
				<div class="category" onclick="filterResults('lecturer');">
					<div class="object_count" id="count_lecturer" ></div>
					Personen
				</div>
				<div class="category" onclick="filterResults('podcast');">
					<div class="object_count" id="count_podcast" ></div>
					Podcast
				</div>
			</div>
			<!-- ENTFERNEN ENTFERNEN ENTFERNEN ENTFERNEN ENTFERNEN ENTFERNEN -->
			<!--
			<div id="leftlinkblock" style="display: none;">
				<div><a href="javascript: "  class="leftlink">Fachbereiche</a></div>
				<div><a href="javascript: "  class="leftlink">Vorlesungen</a></div>
				<div><a href="javascript: "  class="leftlink">Tagungen</a></div>
				<div><a href="javascript: "  class="leftlink">Titel Index (A-Z)</a></div>
				<div><a href="javascript: "  class="leftlink">Dozentenindex (A-Z)</a></div>
			</div>
			-->
			<!-- ENTFERNEN ENTFERNEN ENTFERNEN ENTFERNEN ENTFERNEN ENTFERNEN -->
		</div>

<!-- ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~ right ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~ -->
		<div id="rightblock">
			<div id="rightview_select">
				<h2>Kalender</h2>
				<div id="datepicker"></div>
				<h2>Tag-Cloud</h2>
				<div id="tagcloud"></div>
			</div>
			<div id="rightview_content">
				<h2>Filter</h2>
				<div id="rightbox_tabs">
					<!--
					<div class="rightbox_tab" onclick=" showSubfilter( 'format',     'video' ); ">Format</div>
					-->
				</div>
				<div id="rightview_filter">
				</div>
			</div>
		</div>

<!-- ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~ content ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~ -->
		<div id="contentbox">
			<div id="toppager" class="pager"> </div>

			<div id="content">
				Startseite...
			</div>
			<div id="bottompager" class="pager">
				<!--<div class="pagelink" id="prevpage">&larr;</div>
				<div class="pagelink" id="pagelink_1">1</div>
				<div class="pagelink" id="pagelink_2">2</div>
				<div class="pagedots">&hellip;</div>
				<div class="pagelink" id="nextpage">&rarr;</div>-->
			</div>
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
			<a href="http://www.uos.de/" title="Uni Osnabrück"><img src="./img/unilogo.png" alt="Uni Osnabrück" /></a>
			<a href="http://www.fh-osnabrueck.de/" title="FH Osnabrück"><img src="./img/fhlogo.png" alt="FH Osnabrück" /></a>
		</div>
	</div>

</div>

<!--
<div id="submenu" style="display: none;"
	onmouseout="menu_intervall = window.setInterval('hideSubmenu()', 100);"
	onmouseover="window.clearInterval( menu_intervall );"> </div>
-->

</body>
</html>
