/*****************************************************************************/
/*****************************************************************************/
/* Playlisteditor                                                            */
/*****************************************************************************/
/*****************************************************************************/

playlisteditor = Class.create();

/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */
/* Member variable to stroe series and assigned mediaobjects for search      */
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */
playlisteditor.series_mediaobjects = new Object();

/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */
/* Open playlisteditor for a new playlist. Remove the sections for adding    */
/* and modifying of playlistentries.                                         */
/*                                                                           */
/* params:                                                                   */
/*   series_id {identifier of the series the playlist should be assigned to} */
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */
playlisteditor.add = function(series_id) {
    editortype = 'playlist';
    $('#stage').innerHTML = loadingHTML;
    new Ajax.Request( lf_url, {
                      method: 'post',
                      onSuccess: function(r) {
                          // first: remove entryeditor
                          var endEd = r.responseText.indexOf('<input type="button" id="deletebutton"');
                          if (endEd > 0)
                              r.responseText = r.responseText.truncate(endEd, '') + '</td></tr></table></form>';

                          // second: replace button text
                          r.responseText = r.responseText.replace(/save playlist \(not entries\)/, 'add playlist');

                          // third: change action (load playlisteditor after saving)
                          r.responseText = r.responseText.replace(/onclick=\"playlisteditor.save\(\)\"/, 
                              'onclick="playlisteditor.save(); serieseditor.load(' + series_id + ');"');

                          // last: insert editor html
                          $('#stage').innerHTML = r.responseText;
                      },
                      parameters: {cmd:'get_empty_playlisteditor', series_id:series_id}
    });
    playlisteditor.get_series_mediaobjects();
}

/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */
/* Load editor for secified playlist                                         */
/*                                                                           */
/* params:                                                                   */
/*   playlist_id  {identifier of the playlist to load}                       */
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */
playlisteditor.load = function(playlist_id) {
    editortype = 'playlist';
    $('#stage').innerHTML = loadingHTML;
    new Ajax.Request( lf_url, {
                      method: 'post',
                      onSuccess: function(r) {
                          $('#stage').innerHTML = r.responseText;
                      },
                      parameters: {cmd:'get_playlisteditor', playlist_id:playlist_id}
    });
    playlisteditor.get_series_mediaobjects();
}

/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */
/* Save playlist, but without entries                                        */
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */
playlisteditor.save = function() {

    var series_id = $('#reciever_id').value;
    var old_series_id = $('#old_series_id').innerHTML;
    var keepSeries = (series_id == old_series_id) || (old_series_id = '');
    if (!old_series_id) 
        old_series_id = series_id;
    if (keepSeries || confirm('Are you shure you want to change the series?')) {
        new Ajax.Request( lf_url, {
                          method: 'post',
                          onSuccess: function(r) {
                              if ($('#playlist_response'))
                                  $('#playlist_response').innerHTML = r.responseText;
                              try {
                                  trigger_result(old_series_id);
                                  trigger_result(series_id);
                              } catch (err) {}
                          },
                          parameters: {cmd:'save_playlist', record: r = Object.toJSON($('#fields').serialize(true))}
        });
        $('#playlist_response').innerHTML = '<pre>saving all playlist data...</pre>';
        $('#savebutton').disabled = true;
    }

}

/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */
/* Close opened editor for new playlistentries and reset it                  */
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */
playlisteditor.closeentryeditor = function() {
    
    $('#add_entry_response').innerHTML = '';
    $('#new_entry_editor').style.display = 'none';
    $('#new_entry_fields').reset();
    $('#new_index_position').value = $('#max_index_position').innerHTML;
    playlisteditor.generate_seriesoptions(playlisteditor.series_mediaobjects, 'new_object_id', '', '');
    
}

/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */
/* Add a new entry to the opened playlist                                    */
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */
playlisteditor.addentry = function() {
    // check input
    if ($('#new_index_position').value == '') {
        alert('You must specify a position!');
        $('#new_index_position').focus();
        return;
    }
    if ($('#new_object_id').value == '') {
        alert('You must specify a mediaobject!');
        $('#new_object_id').focus();
        return;
    }

    // get index for next editorcall
    new_max_index = 1 + parseInt(
            $('#max_index_position').innerHTML > $('#new_index_position').value 
                ? $('#max_index_position').innerHTML 
                : $('#new_index_position').value
        );

    // send data
    new Ajax.Request( lf_url, {
                      method: 'post',
                      onSuccess: function(r) {
                              if (r.responseText.match(/<tr /)) {
                                  newEntry = document.createElement('table');
                                  newEntry.innerHTML = r.responseText;
                                  trNode = newEntry.getElementsByTagName('tr')[0];
                                  $('#entry_list_end').parentNode.insertBefore(trNode, $('#entry_list_end'));
                                  // hide and reset editor
                                  playlisteditor.closeentryeditor();
                                  $('#max_index_position').innerHTML = new_max_index;
                                  $('#new_index_position').value = new_max_index;
                              } else {
                                  $('#add_entry_response').innerHTML = r.responseText;
                              }
                          },
                      parameters: {cmd:'add_playlistentry', record: r = Object.toJSON($('#new_entry_fields').serialize(true))}
    });
    $('#add_entry_response').innerHTML = '<pre>inserting playlistentry...</pre>';
}

/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */
/* Save playlist including all entries                                       */
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */
playlisteditor.saveall = function() {
    // save playlist data
    playlisteditor.save();
    
    // Entries -> JSON
    var entries = $('#playlist_entries').getElementsByTagName('tr');
    var es = new Array();
    for (var i = 0; i < entries.length; i++) {
        var entry_fields = entries[i].getElementsByTagName('input');
        if (entry_fields.length > 0) {
            var e = new Hash();
            for (var j = 0; j < entry_fields.length; j++) {
                if ( entry_fields[j].parentNode.getElementsByTagName('div')[0]
                  && (entry_fields[j].parentNode.getElementsByTagName('div')[0].innerHTML != entry_fields[j].value) 
                  && (entry_fields[j].name) )
                    e.set(entry_fields[j].name, entry_fields[j].value);
                else if (entry_fields[j].name == 'object_id')
                    e.set('object_id', entry_fields[j].value);
            }
            entry_fields = entries[i].getElementsByTagName('select');
            for (var j = 0; j < entry_fields.length; j++) {
                if ( entry_fields[j].parentNode.getElementsByTagName('div')[0]
                  && (entry_fields[j].parentNode.getElementsByTagName('div')[0].innerHTML != entry_fields[j].value) )
                    e.set(entry_fields[j].name, entry_fields[j].value);
            }
            if (e.values().size() > 1)
                es.push(e);
        }
    }
    entries = Object.toJSON( new Hash( { playlist_id:$('#playlist_id').value, entries:es } ) );
    
    // send data
    new Ajax.Request( lf_url, {
                      method: 'post',
                      onSuccess: function(r) {
                              $('#playlist_entry_response').innerHTML = r.responseText;
                              trigger_result($('#old_series_id').innerHTML);
                              trigger_result($('#reciever_id').value);
                      },
                      parameters: { cmd: 'save_all_playlist', 
                                    entries: entries }
    });
    $('#playlist_entry_response').innerHTML = '<pre>saving all playlist entries...</pre>';
}

/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */
/* Delete a specified playlist entry                                         */
/*                                                                           */
/* params:                                                                   */
/*   playlist_id  {identifier of the playlist the entry is assigned to}      */
/*   object_id    {identifier of the mediaobject the entry is assigned to}   */
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */
playlisteditor.delete_entry = function(playlist_id, object_id) {
     if (confirm('Are you shure you want to delete this playlistentry?')) {
        new Ajax.Request( lf_url, {
                          method: 'post',
                          onSuccess: function(r) {
                                  if (r.responseText == 'SUCCESS') {
                                      var entry = $('#entry_' + object_id);
                                      entry.parentNode.removeChild(entry);
                                  } else
                                      alert(r.responseText);
                          },
                          parameters: {cmd:'delete_playlistentry', playlist_id:playlist_id, object_id:object_id}
        });
    }   
}

/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */
/* Delete playlist including all entries                                     */
/*                                                                           */
/* params:                                                                   */
/*   playlist_id  {identifier of the playlist to remove}                     */
/*   series_id    {Identifier of the series the playlist is assigned to}     */
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */
playlisteditor.remove = function(playlist_id, series_id) {
    if (confirm('Are you shure you want to delete this playlist including all entries?')) {
        new Ajax.Request( lf_url, {
                          method: 'post',
                          onSuccess: function(r) {
                                  $('#stage').innerHTML = r.responseText;
                                  trigger_result(series_id);
                                  trigger_result(series_id);
                          },
                          parameters: {cmd:'delete_playlist', playlist_id:playlist_id}
        });
        $('#playlist_entry_response').innerHTML = '<pre>deleting playliat...</pre>';
    }
}

/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */
/* apply search pattern on series-mediaobjects-structure and                 */
/* fill a HTML-select with the result.                                       */
/*                                                                           */
/* params:                                                                   */
/*   data           {series_mediaobjects-structure},                         */
/*   select_id      {identifier of HTML-Select-object to fill},              */
/*   ser_search_str {search pattern for series},                             */
/*   obj_search_str {search pattern for mediaobjects}                        */
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */
playlisteditor.generate_seriesoptions = function(data, select_id, ser_search_str, obj_search_str) {
    
    if (!$(select_id))
        return;

    if (!data)
        return;

    $(select_id).innerHTML = '';

    var ser_regex = ser_search_str ? new RegExp( RegExp.escape(ser_search_str) , 'i') : new RegExp();
    var obj_regex = obj_search_str ? new RegExp( RegExp.escape(obj_search_str) , 'i') : new RegExp();
    for(var s in data) {
        if( typeof data[s] != 'function' ) {
            if ( !ser_search_str || ser_regex.test(data[s].name) ) {
                var grp = document.createElement('optgroup');
                var opt_count = 0;
                grp.setAttribute('label', data[s].name);
                grp.setAttribute('title', data[s].name);
                for(var o in data[s].obj) {
                    if( typeof data[s].obj[o] != 'function' ) {
                        if ( !obj_search_str || obj_regex.test(data[s].obj[o].title) ) {
                            var opt = document.createElement('option');
                            opt.setAttribute('value', o);
                            opt.setAttribute('title', data[s].obj[o].desc);
                            opt.appendChild(document.createTextNode(data[s].obj[o].title));
                            grp.appendChild(opt);
                            opt_count++;
                        }
                    }
                }
                if (opt_count > 0)
                    $(select_id).appendChild(grp);
            }
        }
    }

    
    
}

/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */
/* Get series-mediaobjects-structure from database and write it to the       */
/* playlisteditor member variable series_mediaobjects.                       */
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */
playlisteditor.get_series_mediaobjects = function() {
    new Ajax.Request( lf_url, {
                      method: 'post',
                      onSuccess: function(r) {
                           playlisteditor.series_mediaobjects = r.responseText.evalJSON();
                           /*
                                STRUCTURE:
                                'series_mediaobjects' -> series_id -> 'name'
                                                                   -> 'obj' -> object_id -> 'title'
                                                                                         -> 'desc'
                                                                                         -> 'date'
                           */
                           playlisteditor.generate_seriesoptions(playlisteditor.series_mediaobjects, 'new_object_id', '', '');
                      },
                      parameters: {cmd:'get_series_mediaobjects'}
                    } );
}

/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */
/* Trigger mediaobject-search in 200ms and cancel old searches.              */
/* Basically this is to prevent the browser to start x searches if the user  */
/* enters a word with x characters but to do one search after the word is    */
/* typed completely.                                                         */
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */
playlisteditor.trigger_new_object_series_search = function() {

    try {
        window.clearInterval(new_object_series_search_thread);
    } catch(e) {}
    new_object_series_search_thread = window.setInterval('playlisteditor.new_object_series_search();', 200);

}

/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */
/* Start the mediaobject-search with the values of the editor for new        */
/* playlistentries.                                                          */
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */
playlisteditor.new_object_series_search = function() {
    
    playlisteditor.generate_seriesoptions(
            playlisteditor.series_mediaobjects,
            'new_object_id',
            $('#new_object_series_search').value,
            $('#new_object_search').value
        );
    window.clearInterval(new_object_series_search_thread);
    new_object_series_search_thread = null;
        
}
