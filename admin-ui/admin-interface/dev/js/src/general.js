/*****************************************************************************/
/*****************************************************************************/
/* Non-editor-specific javascript functions                                  */
/*****************************************************************************/
/*****************************************************************************/

// Request URL
lf_url = "index.php?action=ajax";
searchthread = null;
editortype = '';
open_result = null;
selectwnd_ok_action = '';
current_id = '';
var cal_obj = null;

// HTML to display while loading something
loadingHTML = '<div style="width: 100px; margin-left: auto; margin-right: auto;">'
    + '<div style="height: 100px; position: absolute; margin-top: -50px; top: 50%;">'
    + '<img src="gfx/loading.gif" alt="loading..." />'
    + '</div></div>';

/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */
/* Initialize search, set window size and resize eventlistener               */
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */
function init() {
    search(); 
    setSize();
    window.addEventListener('resize', setSize, false);
}

/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */
/* Fit interface in window                                                   */
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */
function setSize() {
    $('navigation').style.height = (window.innerHeight - 50) + 'px'; 
    $('stage').style.height = (window.innerHeight - 50) + 'px'; 
    $('bottom_container').style.height = (window.innerHeight - 50) + 'px'; 
    $('main_table').style.width = (window.innerWidth - 10 ) + 'px';
}

/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */
/* set searchinterval (500ms til search)                                     */
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */
function trigger_search() {
    window.clearInterval(searchthread);
    searchthread = window.setInterval('search();', 500);
}

/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */
/* Search for series or lecturer                                             */
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */
function search() {
    searchtext = $('searchbox').value;
    new Ajax.Request( lf_url,{
                      method: "post",
                      onSuccess: function(r) {
                          $('navigation').innerHTML = r.responseText;
                          open_result = null;
                      },
                      parameters: {cmd:"search", search:searchtext}
                    });
    window.clearInterval(searchthread);
    searchthread = null;
}

/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */
/* Load editor for some kind of record                                       */
/*                                                                           */
/* params:                                                                   */
/*   t   {Type of the record. Types are specified in recordtypes.php.}       */
/*   id  {Optional: Identifier of a specified record to load}                */
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */
function load_editor(t, id) {
    $('stage').innerHTML = loadingHTML;
    new Ajax.Request( lf_url, {
                      method: 'post',
                      onSuccess: function(r) {
                          $('stage').innerHTML = r.responseText;
                          editortype = t;
                      },
                      parameters: {cmd:'get_editor', type:t, id:id}
                    });
}


/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */
/* Open a whole editor as dialog                                             */
/*                                                                           */
/* params:                                                                   */
/*   t   {                        }                                          */
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */
function load_editor_as_dialog(t, id) {
	new Ajax.Request( lf_url, {
		method: 'post',
		onSuccess: function(r) {
			Dialog.alert(r.responseText, {
				className: "alphacube",
				position: "absolute", 
				width: "700",
				height: "500",
				okLabel: "Close",
				id: "d2"});
			 editortype = t;
		 },
		 parameters: {cmd:'get_editor', type:t, id:id}
	});
                    
   
}


/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */
/* Open/close series attributes in search result                             */
/*                                                                           */
/* params:                                                                   */
/*   id  {identifier of the series}                                          */
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */
function trigger_result(id) {
    if (open_result != id) {
        if (open_result != null) {
            var element_id = 'info_'+open_result;
            $(element_id).style.display = 'none';
        }
        open_result = id;
        new Ajax.Request( lf_url, {
                          method: 'post',
                          onSuccess: function(r) {
                                var element_id = 'info_'+id;
                                elm = $(element_id);
                                elm.style.display = '';
                                elm.innerHTML = r.responseText;
                          },
                          parameters: {cmd:'get_result_info', id:id}
                         });
    } else if (open_result == id) {
        var element_id = 'info_'+id;
        $(element_id).style.display = 'none';
        open_result = null;
    }
}

/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */
/* Show datetime-picker                                                      */
/*                                                                           */
/* params:                                                                   */
/*   el   {element above which the calendar should be shown}                 */
/*   inp  {identifier of the HTML-input element in which the date is posted} */
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */
function show_cal(el, inp) {

    if (cal_obj) return;

    var format = '%Y-%m-%d %H:%i:%s';
    var text_field = document.getElementById(inp);

    cal_obj = new RichCalendar();
    cal_obj.show_time = true;
    cal_obj.user_onchange_handler = function(cal, object_code) {
            if (object_code == 'day') {
                text_field.value = cal.get_formatted_date(format);
                if (text_field.onchange)
                    text_field.onchange();
                cal.hide();
                cal_obj = null;
            }
        }

    cal_obj.user_onclose_handler = function(cal) {
            cal.hide();
            cal_obj = null;
        }
    cal_obj.user_onautoclose_handler = function(cal) {
            cal_obj = null;
        }

    cal_obj.parse_date(text_field.value, format);

    cal_obj.show_at_element(text_field, "adj_right-top");

}

/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */
/* ???                                                                       */
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */
function show_select_window(table, text, callback) {
    $('select_window').style.display = 'inline';
    $('select_window_caption').innerHTML = text;
    selectwnd_ok_action = callback + "($('select_window_selector').value);";
    new Ajax.Request( lf_url, {
                      method: 'post',
                      onSuccess: function(r) {
                          $('select_window_selector').innerHTML = r.responseText;
                          $('select_window_ok').disabled = false;
                      },
                      parameters: {cmd:'get_option_list', type:table, supress_none_option:true}
                    } );
}

/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */
/* ???                                                                       */
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */
function hide_select_window() {
    $('select_window_ok').disabled = true;
    $('select_window').style.display = 'none';
}

/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */
/* Check whether or not the specicied input contains a valid date            */
/*                                                                           */
/* params:                                                                   */
/*   input_id  {identifier of the input to check}                            */
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */
function checkDateFormat(input_id) {
    if (!$(input_id) || !/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/.test($(input_id).value)) {
    // if (isNaN(Date.parse($(input_id).value))) { // << Firefox hate this
        alert('Error: This is not a valid date format!\nCorrect format: YYYY-MM-DD hh:mm:ss');
        $(input_id).value = '';
        $(input_id).focus();
    }
}




