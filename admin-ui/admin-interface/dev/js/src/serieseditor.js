/*****************************************************************************/
/*****************************************************************************/
/* Serieseditor                                                              */
/*****************************************************************************/
/*****************************************************************************/

serieseditor = Class.create();

/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */
/* Load serieseditor with specified series                                   */
/*                                                                           */
/* params:                                                                   */
/*   id  {identifier of the series to load}                                  */
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */
serieseditor.load = function(id) {
    editortype = 'series';
    current_id = id;
    $('#stage').innerHTML = loadingHTML;
    new Ajax.Request( lf_url, {
                      method: 'post',
                      onSuccess: function(r) {
                          $('#stage').innerHTML = r.responseText;
                      },
                      parameters: {
                          cmd:'get_serieseditor', 
                          id:id
                      }
    });
}

/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */
/* Load seriescreator                                                        */
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */
serieseditor.create = function(id) {
    editortype = 'series';
    current_id = id;
    $('#stage').innerHTML = loadingHTML;
    new Ajax.Request( lf_url, {
                      method: 'post',
                      onSuccess: function(r) {
                          $('#stage').innerHTML = r.responseText;
                          serieseditor.getLastRecordings(10, false);
                          $('#term_id').selectedIndex = $('#term_id').length - 1;
                      },
                      parameters: {
                          cmd:'get_seriescreator' 
                      }
    });
}

serieseditor.getLastRecordings = function( count, withoutSeries ) {
    new Ajax.Request( lf_url, {
                      method: 'post',
                      onSuccess: function(r) {
                          $('#mediaobjectselector').innerHTML = r.responseText;
                      },
                      parameters: {
                          cmd:'get_last_recordings',
                          count:count,
                          withoutseries:withoutSeries
                      }
    });
}

/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */
/* Save new mediaobject (seriescreator)                                      */
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */
serieseditor.save_new = function() {
    // alert( Object.toJSON($('#fields').serialize(true)) );
    new Ajax.Request( lf_url, {
                      method: 'post',
                      onSuccess: function(r) {
                          if (r.responseText == 'OK')
                              serieseditor.create();
                          else
                              alert( r.responseText );
                      },
                      parameters: {
                          cmd:'save_new_series', 
                          record: r = Object.toJSON($('#fields').serialize(true))
                      }
    });
}

/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */
/* Save currently opened series                                              */
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */
serieseditor.save = function() {
    new Ajax.Request( lf_url, {
                      method: 'post',
                      onSuccess: function(r) {
                            $('#savebutton').disabled = true;
                      },
                      parameters: {
                          cmd:'save_series', 
                          id:$('#series_id').value, 
                          record: r = Object.toJSON($('#fields').serialize(true))
                      }
    });
}

/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */
/* Remove a lecturer from the currently opened series                        */
/*                                                                           */
/* params:                                                                   */
/*   lecturer_id  {identifier of the lecturer to remove}                     */
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */
serieseditor.remove_lecturer = function(lecturer_id) {
    new Ajax.Request( lf_url, {
                      method: 'post',
                      onSuccess: function(r) {
                          $('#lecturers').innerHTML = r.responseText;
                      },
                      parameters: {
                          cmd:'series_remove_lecturer', 
                          id:lecturer_id
                      }
    });
}

/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */
/* Add lecturer to the currently opened series                               */
/*                                                                           */
/* params:                                                                   */
/*   lecturer_id  {identifier of the lecturer to add}                        */
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */
serieseditor.add_lecturer = function(lecturer_id) {
    new Ajax.Request( lf_url, {
                      method: 'post',
                      onSuccess: function(r) {
                          $('#lecturers').innerHTML = r.responseText;
                          hide_select_window();
                      },
                      parameters: {
                          cmd:'series_add_lecturer', 
                          lecturer_id:lecturer_id, 
                          series_id:current_id
                      }
    });
}
