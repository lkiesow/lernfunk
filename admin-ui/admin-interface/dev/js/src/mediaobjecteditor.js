/*****************************************************************************/
/*****************************************************************************/
/* Mediaobjecteditor                                                         */
/*****************************************************************************/
/*****************************************************************************/

mediaobjecteditor = Class.create();

/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */
/* Load editor for a new feed                                                */
/*                                                                           */
/* params:                                                                   */
/*   series_id  {identifier of the series the feed sould be assigned to}     */
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */
mediaobjecteditor.add = function(series_id) {
    editortype = 'mediaobject';
    $('stage').innerHTML = loadingHTML;
    new Ajax.Request( lf_url, {
                      method: 'post',
                      onSuccess: function(r) {
                          $('stage').innerHTML = r.responseText;
                      },
                      parameters: {cmd:'get_empty_mediaobjecteditor', series_id:series_id}
    });
}

/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */
/* Load editor for specified mediaobject                                     */
/*                                                                           */
/* params:                                                                   */
/*   object_id  {identifier of the mediaobject to load}                      */
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */
mediaobjecteditor.load = function(object_id) {
    editortype = 'mediaobject';
    $('stage').innerHTML = loadingHTML;
    new Ajax.Request( lf_url, {
                      method: 'post',
                      onSuccess: function(r) {
                          $('stage').innerHTML = r.responseText;
                      },
                      parameters: {cmd:'get_mediaobjecteditor', object_id:object_id}
    });
}

/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */
/* Save currently opened mediaobject                                         */
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */
mediaobjecteditor.save = function() {
    var keepSeries = ($('series_id').value == $('old_series_id').value) || ($('old_series_id').value = '');
    if (keepSeries || confirm('Are you shure you want to change the series?')) {
        new Ajax.Request( lf_url, {
                          method: 'post',
                          onSuccess: function(r) {
                                  $('mediaobject_response').innerHTML = r.responseText;
                                  trigger_result($('old_series_id').value);
                                  trigger_result($('series_id').value);
                          },
                          parameters: {cmd:'save_mediaobject', record: r = Object.toJSON($('fields').serialize(true))}
        });
        $('mediaobject_response').innerHTML = '<pre>saving feed...</pre>';
    }
}

/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */
/* Convert Duration in hh:mm:ss                                              */
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */
function secondsToHms(duration) {
    d = Number(d);
    var h = Math.floor(d / 3600);
    var m = Math.floor(d % 3600 / 60);
    var s = Math.floor(d % 3600 % 60);
    return ((h > 0 ? h + ":" : "") + (m > 0 ? (h > 0 && m < 10 ? "0" : "") + m + ":" : "0:") + (s < 10 ? "0" : "") + s);
    }


/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */
/* Delete a specified mediaobject                                            */
/*                                                                           */
/* params:                                                                   */
/*   object_id  {identifier of the mediaobject to delete}                    */
/*   series_id  {identifier of the series, the mediaobject is assigned with} */
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */
mediaobjecteditor.remove = function(object_id, series_id) {
    if (confirm('Are you shure you want to delete this mediaobject?')) {
        new Ajax.Request( lf_url, {
                          method: 'post',
                          onSuccess: function(r) {
                                  $('stage').innerHTML = r.responseText;
                                  trigger_result(series_id);
                                  trigger_result(series_id);
                          },
                          parameters: {cmd:'delete_mediaobject', object_id:object_id}
        });
        $('mediaobject_response').innerHTML = '<pre>deleting mediaobject...</pre>';
    }
}

/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */
/* Load editor for all objects with specified cou_id                         */
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */
/*
>> Rigt now this is just an idea
>> Maybe something like this might be usefull sometimes...
mediaobjecteditor.load_cou = function(id) {
    new Ajax.Request( lf_url, {
                      method: 'post',
                      onSuccess: function(r) {
                          $('cou_editor_pane').innerHTML = r.responseText;
                      },
                      parameters: {cmd:'get_couobjecteditor', id:id}
                    });
}
*/

/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */
/* Save data for all mediaobjects with one cou_id                            */
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */
/*
>> Rigt now this is just an idea
>> Maybe something like this might be usefull sometimes...
mediaobjecteditor.save_cou = function() {
    new Ajax.Request( lf_url, {
                      method: 'post',
                      onSuccess: function(r) {
                            $('cousavebutton').disabled = true;
                      },
                      parameters: {cmd:'save_cou_object', 
                                   id:$('object_id').value, 
                                   record: r = Object.toJSON($('cou_object_editor').serialize(true))}
    });
}
*/



