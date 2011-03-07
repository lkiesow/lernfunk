/*****************************************************************************/
/*****************************************************************************/
/* Feededitor                                                                */
/*****************************************************************************/
/*****************************************************************************/

feededitor = Class.create();

/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */
/* Load editor for a new feed                                                */
/*                                                                           */
/* params:                                                                   */
/*   series_id  {identifier of the series the feed should be assigned to}    */
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */
feededitor.add = function(series_id) {
    editortype = 'feed';
    $('stage').innerHTML = loadingHTML;
    new Ajax.Request( lf_url, {
                      method: 'post',
                      onSuccess: function(r) {
                          $('stage').innerHTML = r.responseText;
                      },
                      parameters: {cmd:'get_feededitor', series_id:series_id}
                    });
}

/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */
/* Load editor for secified feed                                             */
/*                                                                           */
/* params:                                                                   */
/*   feed_id  {identifier of the feed to load}                               */
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */
feededitor.load = function(feed_id) {
    editortype = 'feed';
    $('stage').innerHTML = loadingHTML;
    new Ajax.Request( lf_url, {
                      method: 'post',
                      onSuccess: function(r) {
                          $('stage').innerHTML = r.responseText;
                      },
                      parameters: {cmd:'get_feededitor', feed_id:feed_id}
                    });
}

/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */
/* Save the currently opened feed                                            */
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */
feededitor.save = function() {
    var keepSeries = ($('series_id').value == $('old_series_id').value) || ($('old_series_id').value = '');
    if (keepSeries || confirm('Are you shure you want to change the series?')) {
        new Ajax.Request( lf_url, {
                          method: 'post',
                          onSuccess: function(r) {
                                  $('feed_response').innerHTML = r.responseText;
                                  trigger_result($('old_series_id').value);
                                  trigger_result($('series_id').value);
                          },
                          parameters: {cmd:'save_feed', feed_id:$('feed_id').value, feed_url:$('feed_url').value, 
                              series_id:$('series_id').value, feedtype_id:$('feedtype_id').value, 
                              itunes_status:$('itunes_status').value}
        });
        $('feed_response').innerHTML = '<pre>saving feed...</pre>';
    }
}

/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */
/* Delete a specified feed.                                                  */
/*                                                                           */
/* params:                                                                   */
/*   feed_id    {identifier of the feed}                                     */
/*   series_id  {identifier of the series, the feed is assigned to}          */
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */
feededitor.remove = function(feed_id, series_id) {
    if (confirm('Are you shure you want to delete this feed?')) {
        new Ajax.Request( lf_url, {
                          method: 'post',
                          onSuccess: function(r) {
                                  $('stage').innerHTML = r.responseText;
                                  trigger_result(series_id);
                                  trigger_result(series_id);
                          },
                          parameters: {cmd:'delete_feed', feed_id:feed_id}
        });
        $('feed_response').innerHTML = '<pre>deleting feed...</pre>';
    }
}


