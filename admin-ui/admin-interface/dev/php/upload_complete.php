<?php
// filename: upload_complete.php
	$filename = $_FILES['uploadedfile']['name'];
	$info     = pathinfo( $_FILES['uploadedfile']['name'] ); 
	$field    = $_REQUEST['field'];


?><!DOCTYPE html>

<html lang="de">
    <head>
        <meta http-equiv="content-type" content="text/html; charset=utf-8">
        
        <link rel="stylesheet" type="text/css" href="../gfx/style.css">
        
        <script type="text/javascript" src="../js/imports/prototype.js" type="text/javascript"></script>
        <script type="text/javascript" src="../js/imports/scriptaculous-js-1.9.0/src/scriptaculous.js?load=effects,builder,dragdrop" type="text/javascript"></script>
        <script type="text/javascript" src="../js/imports/jsCropperUI-1.2.2/cropper/cropper.js" type="text/javascript"></script>
        <link rel="stylesheet" href="../gfx/lfadmin.css" type="text/css" media="screen" />
		    <link rel="stylesheet" href="../gfx/style.css" type="text/css" media="screen" />

        <script type="text/javascript" language="javascript">
          function onEndCrop( coords, dimensions ) {
	           $( 'x1' ).value = coords.x1;
	           $( 'y1' ).value = coords.y1;
	           $( 'width' ).value = dimensions.width;
	           $( 'height' ).value = dimensions.height;
          }
        </script>
        
        <script type="text/javascript" language="javascript">
          Event.observe( window, 'load', function() {
          new Cropper.Img(
            'uploadedImage',
            { 
              ratioDim: {
                  x: 10,
                  y: 7
              },
              minWidth: 50,
              minHeight: 35, 
              displayOnInit: true,
              onEndCrop: onEndCrop 
            }
          );
	      } );
        </script>
                                                                                                                                 
    <title>Fotoausschnitt wählen</title>
    
    </head>
    
    <body>
   
        <div class="itunestitle" style="padding-left: 48px; padding-bottom: 10px; font-weight: bold;">Vorschau auswählen</div>
           
      <div id="upload-container">
        <img id="uploadedImage" src="<?php echo $_GET['fileurl'].'?time='.time(); ?>" />
        
        <?php
          $size = getimagesize($_GET['fileurl']);
          $totalwidth = $size[0];
          $totalheight = $size[1];
        
        if ($totalheight <= 400) {
            $compressed_width = $totalwidth;
            $compressed_height = $totalheight;
        }
        /*elseif ($totalwidth <= 300) {
            $compressed_width = 300;
            $compressed_height = 300 / $totalwidth * $totalheight;
        } */       
        else {
            $compressed_width = 400 / $totalheight * $totalwidth;
            $compressed_height = 400;
        }        
        
/*      if ($size[2] != 2) {
            echo  '<!DOCTYPE html>'."\n\n".
                  '    <head>'."\n".
                  '        <style type="text/css">'."\n\n".
                  '         .forward {display:none;}'."\n\n".  
                  '         .navigation {margin-top:30px;}'."\n\n".        
                  '        </style>'."\n\n".
                  '    </head>'."\n\n".
                  '    <body>'."\n\n". 
                  '        <h1 style="color:#ff0000;">Bitte nur JPG-Dateien verwenden!</h1>'."\n\n". 
                  '    </body>'."\n\n".   
                  '</html>';
        }
        
        if ($totalwidth < 600) {
            echo  '<!DOCTYPE html>'."\n\n".
                  '    <head>'."\n".
                  '        <style type="text/css">'."\n\n".
                  '         .forward {display:none;}'."\n\n".  
                  '         .navigation {margin-top:30px;}'."\n\n".        
                  '        </style>'."\n\n".
                  '    </head>'."\n\n".
                  '    <body>'."\n\n". 
                  '        <h1 style="color:#ff0000;">Das Bild muss eine Breite von mindestens 600 Pixel haben!</h1>'."\n\n". 
                  '    </body>'."\n\n".   
                  '</html>';
        }
                  
        if ($filesize > 50000000) {
            echo  '<!DOCTYPE html>'."\n\n".
                  '    <head>'."\n".
                  '        <style type="text/css">'."\n\n".
                  '         .forward {display:none;}'."\n\n".  
                  '         .navigation {margin-top:30px;}'."\n\n".        
                  '        </style>'."\n\n".
                  '    </head>'."\n\n".
                  '    <body>'."\n\n". 
                  '        <h1 style="color:#ff0000;">Dein Bild ist '.round($filesizeMB, 2).' MB groß!<br/><br/>Erlaubt sind 5MB.</h1>'."\n\n". 
                  '    </body>'."\n\n".   
                  '</html>';
        }   */    
        ?>
                  
      </div>

        <form class="formular" method="POST" action="create.php">
          <input name="filename" id="filename" type="hidden" value="<?php echo basename($_GET['fileurl']) ?>">
          <input name="fileurl" id="fileurl" type="hidden" value="<?php echo $_GET['fileurl'] ?>">
          <input name="x1" id="x1" type="hidden">
          <input name="y1" id="y1" type="hidden">
          <input name="width" id="width" type="hidden">
          <input name="height" id="height" type="hidden">
          <input name="totalwidth" id="totalwidth" type="hidden" value="<?php echo $totalwidth ?>">
          <input name="totalheight" id="totalheight" type="hidden" value="<?php echo $totalheight ?>">
          <input name="field" id="field" type="hidden" value="<?php echo $field; ?>">
          <input name="compressed_width" id="compressed_width" type="hidden" value="<?php echo $compressed_width ?>">
          <input name="compressed_height" id="compressed_height" type="hidden" value="<?php echo $compressed_height ?>">
    
    <input class="button2" style="float:left; margin-left: 48px; margin-top: 10px; width: 80px;" type="button" onclick="history.back();" name="zurück" value="zurück" />        
    <input class="button2" style="float:right; margin-right: 48px; margin-top: 10px; width: 80px;" type="submit" name="weiter" value="weiter" />   
      
        </form> 
    
      <div style="clear:both;"></div>
    </body>
                                       
</html>
