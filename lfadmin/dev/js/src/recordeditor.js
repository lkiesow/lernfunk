/*****************************************************************************/
/*****************************************************************************/
/* Recordeditor                                                              */
/*****************************************************************************/
/*****************************************************************************/

recordeditor = Class.create();

/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */
/* Add a new record (this means: clear all fields)                           */
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */
recordeditor.add = function() {
    
    document.fields.reset();

}

/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */
/* Delete selected record                                                    */
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */
recordeditor.remove = function() {

    new Ajax.Request( lf_url, {
                      method: 'post',
                      onSuccess: function(r) {
                          document.fields.reset();
                          $('record_select').innerHTML = r.responseText;
                      },
                      parameters: {
                          cmd:'delete_record', 
                          type:editortype, 
                          record: r = Object.toJSON($('fields').serialize(true))
                      }
    });

}

/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */
/* Load selected record                                                      */
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */
recordeditor.dublicate = function() {

    id = $('record_select').value;
    new Ajax.Request( lf_url, {
                      method: 'post',
                      onSuccess: function(r) {
                          var obj = r.responseText.evalJSON();
                          for (var key in obj) {
                              var elm = document.getElementById(key);
                              elm.value = elm.getAttribute('readonly') ? '' : obj[key];
                          }
                          $('savebutton').disabled = false;
                      },
                      parameters: {cmd:'get_record', type:editortype, id:id}
    });

}

/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */
/* Load selected record                                                      */
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */
recordeditor.load_record = function() {

    id = $('record_select').value;
    new Ajax.Request( lf_url, {
                      method: 'post',
                      onSuccess: function(r) {
                          var obj = r.responseText.evalJSON();
                          for (var key in obj) {
                            var elm = document.getElementById(key);
                            elm.value = obj[key];
                          }
                          $('savebutton').disabled = true;
                      },
                      parameters: {cmd:'get_record', type:editortype, id:id}
    });

}

/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */
/* Save current record                                                       */
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */
recordeditor.save = function() {
    //$('stage').innerHTML = loadingHTML;
    new Ajax.Request( lf_url, {
                      method: 'post',
                      onSuccess: function(r) {
                              $('record_select').innerHTML = r.responseText;
                      },
                      parameters: {
                          cmd:'save_record', 
                          type:editortype, 
                          record: r = Object.toJSON($('fields').serialize(true))
                      }
    });
}

/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */
/* Enable savebutton                                                         */
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */
recordeditor.toggle_savebutton = function() {
    
    $('savebutton').disabled = false;

}

/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */
/* Reload current record                                                     */
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */
recordeditor.refresh_record_select = function() {
    
    new Ajax.Request( lf_url, {
                      method: 'post',
                      onSuccess: function(r) {
                          $('record_select').innerHTML = r.responseText;
                      },
                      parameters: {
                          cmd:'get_option_list', 
                          type:editortype, 
                          supress_none_option:true
                      }
    });

}
