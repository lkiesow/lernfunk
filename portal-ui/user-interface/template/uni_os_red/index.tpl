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
  <div id="header">
                <div id="logobox">
                        <a style="a:hover background:none;" href="#"><img id="lflogo" src="(:tplpath:)/img/lflogo.png" alt="Lernfunk" /></a>
                </div>
                <div id="titlebox">
                        <h1 class="previwe" id="pagetitle">
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
                                <div id="categorySelector-series"
                                        class="category categorySelector cat1"
                                        onclick="setFilterHash( 'series' );">
                                        <div class="object_count" id="count_series" ></div>
                                        Veranstaltungen
                                </div>
                                <div id="categorySelector-recordings"
                                        class="category categorySelector cat2"
                                        onclick="setFilterHash( 'recordings' );">
                                        <div class="object_count" id="count_recordings" ></div>
                                        Aufzeichnungen
                                </div>
                                <div id="categorySelector-lecturer"
                                        class="category categorySelector cat3"
                                        onclick="setFilterHash( 'lecturer' );">
                                        <div class="object_count" id="count_lecturer" ></div>
                                        Personen
                                </div>
                                <div id="categorySelector-podcast"
                                        class="category categorySelector cat4"
                                        onclick="setFilterHash( 'podcast' );">
                                        <div class="object_count" id="count_podcast" ></div>
                                        Podcast
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
                        <a href="http://www.uos.de/" title="Uni Osnabrück"><img class="logo_footer" src="(:tplpath:)/img/unilogo.png" alt="Uni Osnabrück" /></a>
                        <br/>
                        <br/>
                        <a href="http://www.fh-osnabrueck.de/" title="FH Osnabrück"><img class="logo_footer" src="(:tplpath:)/img/fhlogo.png" alt="FH Osnabrück" /></a>
                </div>
        </div>

</div>

</body>
</html>