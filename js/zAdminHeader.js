/*

Header Params:
	name
	title
	type
	filter
	nosort
	nowrap
	truncate
	enum

Types:
	'system' - reserved for arbitrary fields (edit, delete, etc.)
	'hidden'
	'text'
	'longtext'
	'boolean'
	'enum' = [db value, string value, keys array, values array]
	'file' = [value, url]
	'password'
	'date'
	'custom' = [value, html]
	'html' - no form field, just static html

Filter i.e. <a href='mailto:![value[email_address]!'>![value[name]]!</a>

*/

var model = {};
var rows = [];
var no_wrap_fields = [];
var hidden_fields = 0;
var header_index = 0;
var truncate_length = 50;
var select_enabled = 0;
var pager_size = 25;
var sScrollX = "";
var sScrollY = "";
var dTable;
var url = '', base_url = '', sorting = [[0,'asc']];
var updated_fields = [];

function dt_loadData()
{
	var no_sort = [];
	
	// Append Header and Footer
	$('#dataTable').append('<thead id="dataHead"><tr></tr></thead><tbody id="dataBody"><tr></tr></tbody><tfoot id="dataFoot"><tr></tr></tfoot>');
	
	var data_headers = model.fields;
	
	var header_index = 0;

	if( model.permissions.edit == 1) {
		data_headers.splice(header_index,0,{name : 'edit', title : 'Edit', type : 'system', truncate : false});
		no_sort.push(header_index);
		header_index++;
		$("#dataHead").find("tr:first-child").append('<th>Edit</th>');
		$("#dataFoot").find("tr:first-child").append('<th>Edit</th>');
	}
	
	if (model.permissions.delete == 1) {
		data_headers.splice(header_index,0,{name : 'delete', title : 'Delete', type : 'system', truncate : false});
		no_sort.push(header_index);
		header_index++;
		$("#dataHead").find("tr:first-child").append('<th>Delete</th>');
		$("#dataFoot").find("tr:first-child").append('<th>Delete</th>');
	}
	
	for (i = 0; i < data_headers.length; i++) {
		if (typeof(data_headers[i]) == 'undefined')
			continue;
		if (data_headers[i].type == 'hidden' || data_headers[i].type == 'system')
			continue;

		width = null;
		if (typeof(data_headers[i].width) != 'undefined')
			width = 'style="width: '+data_headers[i].width+'"';
		
		if (data_headers[i]['nosort'])
			no_sort.push(i);
		
		if (data_headers[i]['nowrap'] || data_headers[i]['type'] == 'date')
			no_wrap_fields.push(i);

		$("#dataHead").find("tr:first-child").append('<th '+width+'>'+data_headers[i].title+'</th>');
		$("#dataFoot").find("tr:first-child").append('<th '+width+'>'+data_headers[i].title+'</th>');
		
	}

	if ((navigator.platform.indexOf("iPhone") != -1) || (navigator.platform.indexOf("iPod") != -1)) // Disable scrolling for iPhone/iPod users
	{
		sScrollX = "";
		sScrollY = "";
	}
	
	for (var x in sorting) // Shift the columns to be sorted to the right
		sorting[x][0] += header_index;
	
	// Initialize DataTables
	dTable = $('#dataTable').dataTable({
		"sAjaxSource": 			model.url,
		"bServerSide":			true,
		"bProcessing": 			true,
		"aoColumnDefs": 		[ { "bSortable": false, "aTargets": no_sort } ],
		"aaSorting":			sorting,
		"bJQueryUI": 			true,
		"bAutoWidth" : 			true,
		"iDisplayLength":		pager_size,
		"sScrollX":				sScrollX,
		"sScrollY":				sScrollY,
		"bScrollCollapse":		true,
		"sDom":					"<'row-fluid'<'span4'l><'span6'f>r>t<'row-fluid'<'span6'i><'span6'p>>",
		"sPaginationType":		"bootstrap",
		"fnServerData": function ( sSource, aoData, fnCallback ) {
			var params = {};
			
			var numSortColumns = 0;
			var sortOrder = {};
			var sortDirection = {};
			
			for( var i in aoData )
			{
				var name = aoData[i].name;
				var value = aoData[i].value;
								
				if( name == 'iDisplayStart' )
					params.start = value;
				else if( name == 'iDisplayLength' )
					params.limit = value;
				else if( name == 'sEcho' )
					params.sEcho = value;
				else if( name == 'sSearch' && value.length > 0 )
					params.search = value;
				else if( name == 'iSortingCols' )
					numSortColumns = value;
				else if( name.indexOf('iSortCol_') === 0 )
				{
					// which index?
					var index =  parseInt(name.substr(9, name.length - 9));
					
					sortOrder[index] = value;
				}
				else if( name.indexOf('sSortDir_') === 0 )
				{
					// which index?
					var index =  parseInt(name.substr(9, name.length - 9));
					
					sortDirection[index] = value;
				}
			}
			
			var sort = [];
			for( var i = 0; i < numSortColumns; i++ )
			{
				var property = model.fields[sortOrder[i] ]
				var direction = sortDirection[ i ];
				sort.push(property.name + ' ' + direction);
			}
			params.sort = sort.join(',');
			
			$.get(
				sSource,
				params,
				function(data) { processData(data, fnCallback); }
			);
		},
		"fnRowCallback":		formatTable 
	}).fnSetFilteringPressEnter();
	
}

function processData (data, callback)
{
	rows = data[model.jsonKey];

	var data = {
		aaData:[],
		iTotalDisplayRecords: data.filtered_count,
		iTotalRecords: data.total_count,
		sEcho: data.sEcho
	};
	
	for( var i in rows )
	{
		var row = [];

		// get model id
		var rid = rows[i][model.idFieldName];

		for( var j in model.fields )
		{
			var header = model.fields[j];
			
			if( header.name == 'edit' )
				row.push('<a class="edit-item" rid="'+rid+'" href="#">Edit</a>');
			else if( header.name == 'delete' )
				row.push('<a class="delete-item" rid="'+rid+'" href="#">Delete</a>');
			else if( header.name == 'select' )
				row.push('<a class="select-item" rid="'+rid+'" href="#">Select</a>');
			else
				row.push(generateCell(header, rows[i], false));
		}
		
		data.aaData[i] = row;
	}

	callback(data);
}

function generateCell(field, row, editing)
{
	var html = '';
	
	if( editing )
	{
		var value = '';
		if( row && typeof row == 'object' )
		{
			if( typeof row[field.name] != 'undefined' )
				value = row[field.name];
		}
		else if( field.default )
			value = field.default;
		
		// generate input
		switch (field.type) {
		case 'hidden':
			html = '<input type="hidden" name="' + field.name + '" value="' + value + '" />';
		break;
		case 'text':
			html = '<input class="input-xlarge" type="text" name="' + field.name + '" value="' + value + '" />';
		break;
		case 'longtext':
			html = '<textarea name="' + field.name + '">' + value + '</textarea>';
		break;
		case 'boolean':
			checked = ( value > 0 || value == 'y' ) ? 'checked="checked"' : '';
			disabled = ( checked != '' ) ? 'disabled="disabled"' : '';
			html = '<input type="hidden" value="0" name="' + field.name + '" ' + disabled + ' />';
			html += '<input type="checkbox" class="checkbox-field" value="1" name="' + field.name + '" ' + checked + ' />';
		break;
		case 'enum':
			html = '<select class="input-xlarge" name="' + field.name + '">';
			if( field.enum )
			{
				for( var j in field.enum )
				{
					selected = (value == j) ? 'selected="selected"' : '';
					html += '<option value="' + j + '" ' + selected + '>' + field.enum[j] + '</option>';
				}
			}
			html += '</select>';
		break;
		case 'file':
			html = 'not implemented';
		break;
		case 'password':
			if (row)
				html = '<a href="#" class="change-password" name="' + field.name + '">Change</a>';
			else
				html = '<input class="input-large" type="password" name="' + field.name + '" value="" autocomplete="off" /> ';
		break;
		case 'date':
			html = '<input name="' + field.name + '" class="date" value="' + value + '" />';
		break;
		case 'custom':
			html = value[1];
		break;
		case 'html':
			html = value;
		break;
		}
		
		return html;
	}
	else
	{
		// apply filter
		if (typeof(field.filter) == 'string')
		{
			html = field.filter;
	
			// replace placeholders with values
			for( var i in model.fields )
			{
				var field = model.fields[i];
				var s = '{'+field.name+'}';
				if( html.indexOf(s) != -1 )
				{
					var fieldValue = parseValue(row[field.name], field);
					html = html.replace(new RegExp(s, "g"), fieldValue);
				}
			}
		}
		else
		{
			html = parseValue(row[field.name], field);
		}
	
		return html.replace(/\n/g,'<br />');	
	}
}

function parseValue(value, field)
{
	if (typeof value == 'undefined')
		value = '';
	
	// boolean
	if (field.type == 'boolean')
	{
		value = (value > 0) ? 'Yes' : 'No';
	}
	// enum
	else if (field.type == 'enum')
	{
		if( field.enum )
		{
			if( field.enum[value] )
				value = field.enum[value];
			else if (field.default)
				value = field.enum[field.default];
			else
				value = field.enum[0];
		}
		else
			value = '';
	}
	// password
	else if (field.type == 'password')
	{
		value = '<em>hidden</em>';
	}
	// date
	else if (field.type == 'date')
	{
		var date = new Date(value*1000);
		var year = date.getFullYear();
		var month = date.getMonth();
		var day = date.getDate();
		var hours = date.getHours();
		var minutes = date.getMinutes();
		var seconds = date.getSeconds();
		
		// will display time in Jan 2, 2013 10:30:23 format
		value = year + '-' + month + '-' + day + ' ' + hours + ':' + minutes + ':' + seconds;	
	}
	else
		value = String(value);

	if (typeof value == 'undefined')
		value = '';

	// truncate value
	if (field.truncate && value.length > truncate_length)
		return value.substring(0,truncate_length) + '...';
	else
		return value;
}

function formatTable( nRow, aData, iDisplayIndex, iDisplayIndexFull ) {
	// BUG: incorrectly finds ids for cells if the hidden fields are after the field, hidden fields must be first
	for (var i in no_wrap_fields)
		$('td:eq('+(no_wrap_fields[i]-hidden_fields)+')',nRow).attr('nowrap','nowrap');
	return nRow;
}

$.fn.dataTableExt.oApi.fnStandingRedraw = function(oSettings) {
	//redraw to account for filtering and sorting
	// concept here is that (for client side) there is a row got inserted at the end (for an add) 
	// or when a record was modified it could be in the middle of the table
	// that is probably not supposed to be there - due to filtering / sorting
	// so we need to re process filtering and sorting
	// BUT - if it is server side - then this should be handled by the server - so skip this step
	if(oSettings.oFeatures.bServerSide === false){
		var before = oSettings._iDisplayStart;
		oSettings.oApi._fnReDraw(oSettings);
		//iDisplayStart has been reset to zero - so lets change it back
		oSettings._iDisplayStart = before;
		oSettings.oApi._fnCalculateEnd(oSettings);
	}
	
	//draw the 'current' page
	oSettings.oApi._fnDraw(oSettings);
};

jQuery.fn.dataTableExt.oApi.fnSetFilteringPressEnter = function (oSettings) {
    /*
    * Type:        Plugin for DataTables (www.datatables.net) JQuery plugin.
    * Name:        dataTableExt.oApi.fnSetFilteringPressEnter
    * Version:     2.2.1
    * Description: Enables filtration to be triggered by pressing the enter key instead of keyup or delay.
    * Inputs:      object:oSettings - dataTables settings object
    *             
    * Returns:     JQuery
    * Usage:       $('#example').dataTable().fnSetFilteringPressEnter();
    * Requires:   DataTables 1.6.0+
    *
    * Author:      Jon Ranes (www.mvccms.com)
    * Created:     4/17/2011
    * Language:    Javascript
    * License:     GPL v2 or BSD 3 point style
    * Contact:     jranes /AT\ mvccms.com
    */
    var _that = this;
 
    this.each(function (i) {
        $.fn.dataTableExt.iApiIndex = i;
        var $this = this;
        var anControl = $('input', _that.fnSettings().aanFeatures.f);
        anControl.unbind('keyup').bind('keypress', function (e) {
            if (e.which == 13) {
                $.fn.dataTableExt.iApiIndex = i;
                _that.fnFilter(anControl.val());
            }
        });
        return this;
    });
    return this;
}

/* Default class modification */
$.extend( $.fn.dataTableExt.oStdClasses, {
	"sSortAsc": "header headerSortDown",
	"sSortDesc": "header headerSortUp",
	"sSortable": "header"
} );

/* API method to get paging information */
$.fn.dataTableExt.oApi.fnPagingInfo = function ( oSettings )
{
	return {
		"iStart":         oSettings._iDisplayStart,
		"iEnd":           oSettings.fnDisplayEnd(),
		"iLength":        oSettings._iDisplayLength,
		"iTotal":         oSettings.fnRecordsTotal(),
		"iFilteredTotal": oSettings.fnRecordsDisplay(),
		"iPage":          Math.ceil( oSettings._iDisplayStart / oSettings._iDisplayLength ),
		"iTotalPages":    Math.ceil( oSettings.fnRecordsDisplay() / oSettings._iDisplayLength )
	};
}

/* Bootstrap style pagination control */
$.extend( $.fn.dataTableExt.oPagination, {
	"bootstrap": {
		"fnInit": function( oSettings, nPaging, fnDraw ) {
			var oLang = oSettings.oLanguage.oPaginate;
			var fnClickHandler = function ( e ) {
				e.preventDefault();
				if ( oSettings.oApi._fnPageChange(oSettings, e.data.action) ) {
					fnDraw( oSettings );
				}
			};

			$(nPaging).addClass('pagination').append(
				'<ul>'+
					'<li class="prev disabled"><a href="#">&larr; '+oLang.sPrevious+'</a></li>'+
					'<li class="next disabled"><a href="#">'+oLang.sNext+' &rarr; </a></li>'+
				'</ul>'
			);
			var els = $('a', nPaging);
			$(els[0]).bind( 'click.DT', { action: "previous" }, fnClickHandler );
			$(els[1]).bind( 'click.DT', { action: "next" }, fnClickHandler );
		},

		"fnUpdate": function ( oSettings, fnDraw ) {
			var iListLength = 5;
			var oPaging = oSettings.oInstance.fnPagingInfo();
			var an = oSettings.aanFeatures.p;
			var i, j, sClass, iStart, iEnd, iHalf=Math.floor(iListLength/2);

			if ( oPaging.iTotalPages < iListLength) {
				iStart = 1;
				iEnd = oPaging.iTotalPages;
			}
			else if ( oPaging.iPage <= iHalf ) {
				iStart = 1;
				iEnd = iListLength;
			} else if ( oPaging.iPage >= (oPaging.iTotalPages-iHalf) ) {
				iStart = oPaging.iTotalPages - iListLength + 1;
				iEnd = oPaging.iTotalPages;
			} else {
				iStart = oPaging.iPage - iHalf + 1;
				iEnd = iStart + iListLength - 1;
			}

			for ( i=0, iLen=an.length ; i<iLen ; i++ ) {
				// Remove the middle elements
				$('li:gt(0)', an[i]).filter(':not(:last)').remove();

				// Add the new list items and their event handlers
				for ( j=iStart ; j<=iEnd ; j++ ) {
					sClass = (j==oPaging.iPage+1) ? 'class="active"' : '';
					$('<li '+sClass+'><a href="#">'+j+'</a></li>')
						.insertBefore( $('li:last', an[i])[0] )
						.bind('click', function (e) {
							e.preventDefault();
							oSettings._iDisplayStart = (parseInt($('a', this).text(),10)-1) * oPaging.iLength;
							fnDraw( oSettings );
						} );
				}

				// Add / remove disabled classes from the static elements
				if ( oPaging.iPage === 0 ) {
					$('li:first', an[i]).addClass('disabled');
				} else {
					$('li:first', an[i]).removeClass('disabled');
				}

				if ( oPaging.iPage === oPaging.iTotalPages-1 || oPaging.iTotalPages === 0 ) {
					$('li:last', an[i]).addClass('disabled');
				} else {
					$('li:last', an[i]).removeClass('disabled');
				}
			}
		}
	}
} );

$.fn.dataTableExt.oApi.fnReloadAjax = function ( oSettings, sNewSource, fnCallback, bStandingRedraw )
{
    if ( sNewSource !== undefined && sNewSource !== null ) {
        oSettings.sAjaxSource = sNewSource;
    }
 
    // Server-side processing should just call fnDraw
    if ( oSettings.oFeatures.bServerSide ) {
        this.fnDraw();
        return;
    }
 
    this.oApi._fnProcessingDisplay( oSettings, true );
    var that = this;
    var iStart = oSettings._iDisplayStart;
    var aData = [];
 
    this.oApi._fnServerParams( oSettings, aData );
 
    oSettings.fnServerData.call( oSettings.oInstance, oSettings.sAjaxSource, aData, function(json) {
        /* Clear the old information from the table */
        that.oApi._fnClearTable( oSettings );
 
        /* Got the data - add it to the table */
        var aData =  (oSettings.sAjaxDataProp !== "") ?
            that.oApi._fnGetObjectDataFn( oSettings.sAjaxDataProp )( json ) : json;
 
        for ( var i=0 ; i<aData.length ; i++ )
        {
            that.oApi._fnAddData( oSettings, aData[i] );
        }
         
        oSettings.aiDisplay = oSettings.aiDisplayMaster.slice();
 
        that.fnDraw();
 
        if ( bStandingRedraw === true )
        {
            oSettings._iDisplayStart = iStart;
            that.oApi._fnCalculateEnd( oSettings );
            that.fnDraw( false );
        }
 
        that.oApi._fnProcessingDisplay( oSettings, false );
 
        /* Callback user function - for event handlers etc */
        if ( typeof fnCallback == 'function' && fnCallback !== null )
        {
            fnCallback( oSettings );
        }
    }, oSettings );
};

function checkEnter(e,id)
{
	var keycode = (e.keyCode ? e.keyCode : e.which);
	if(keycode == '13')
		submitChanges(id,1);	
}

function getKeyIndex(_array,_search) {
	var myPosition=-1
	for (i2=0;i2<_array.length;i2++) {
		if(_array[i2]==_search) {
			myPosition = i2;
			break;
		}
	}
	return myPosition;
}

function randStrings(howMany, howLong) {
	// define a string with valid characters
	var characters = "0123456789ABCDEFGHIJKLMNOPQRSTUVWXTZ"+"abcdefghiklmnopqrstuvwxyz";
	for (var i=0;i<howMany;i++) {
		var word="";
		for (var j=0;j<howLong;j++) {
			var rand = Math.floor(Math.random() * characters.length);
			word += characters.substring(rand,rand+1);
		}
	}
	return word;
}

$(function() {
	if( model.permissions && model.permissions.create )
		$('.new-item').removeClass('disabled').removeAttr('disabled');

	$("body").delegate('.new-item','click',function(e) {
	    e.preventDefault();
	
	    // populate the new item dialog with fields
	    var html = '<div class="item">';
	    
	    for( var j in model.fields )
	    {
		    var field = model.fields[j];
	    	if( ['edit','delete','select'].indexOf(field.name) == -1 )
	    	{
		    	html += '<h3>' + field.title + '</h3>';
		    	html += '<div class="field">' + generateCell(field, null, true) + '</div>';
		    }
	    }
	    
	    html += '</div>';
	    
	    $('#dialog-new-item .modal-body').html(html);
	    
	    $('#dialog-new-confirm').off().click(function(e) {
	    	e.preventDefault();
	    	
	    	$('#dialog-new-confirm').attr('disabled');
	    	
	    	// serialize the form
	    	var fields = $('#form-new-item').serialize();
			
	    	$.post(model.url, fields, function(response) {
	    		if (response.success)
	    		{
	    			// add the item to the field
	    			// TODO
	    			
	    			// refresh the table
					dTable.fnReloadAjax();

					$('#dialog-new-item').modal('hide');
				}
	    		else if (response.error && response.error instanceof Array)
	    		{
	    			for( var i in response.error )
	    				alert( response.error[i] );
	    		}

				$('#dialog-new-confirm').removeAttr('disabled');
	    	});
	    	
	    	return false;
	    });
	    
	    // show the create dialog
	    $('#dialog-new-item').modal();
	});

	$("#dataTable").delegate('a.edit-item','click',function(e) {
	    e.preventDefault();
	
	    var rid = $(this).attr('rid');
	    
	    // populate the edit dialog with fields
	    var html = '<div class="item">';
	    
	    var row;
	    for( var i in rows )
	    {
			if( rows[i][model.idFieldName] == rid )
			{
				row = rows[i];
				break;
			}
	    }
	    
	    for( var j in model.fields )
	    {
		    var field = model.fields[j];
	    	if( ['edit','delete','select'].indexOf(field.name) == -1 )
	    	{
		    	html += '<h3>' + field.title + '</h3>';
		    	html += '<div class="field">' + generateCell(field, row, true) + '</div>';
		    }
	    }
	    
	    html += '</div>';
	    
	    $('#dialog-edit-item .modal-body').html(html);
	    
	    $('#dialog-edit-confirm').off().click(function(e) {
	    	e.preventDefault();
	    	
	    	$('#dialog-edit-confirm').attr('disabled');
	    	
	    	// serialize the form
	    	var fields = $('#form-edit-item').serialize();

	    	$.put(model.url + '/' + rid, fields, function(response) {
	    		if (response.success)
	    		{
	    			// add the item to the field
	    			// TODO
	    			
	    			// refresh the table
					dTable.fnReloadAjax();

					$('#dialog-edit-item').modal('hide');	    	
	    		}
	    		else if (response.error && response.error instanceof Array)
	    		{
	    			for( var i in response.error )
	    				alert( response.error[i] );
	    		}

				$('#dialog-edit-confirm').removeAttr('disabled');
	    	});

	    	return false;
	    });
	    
	    // show the edit dialog
	    $('#dialog-edit-item').modal();    	    
	});
	
	$('#dataTable').delegate("a.delete-item",'click',function(e) {
	    e.preventDefault();
		
	    var rid = $(this).attr('rid');
	    var parent = $(this).parents('tr');
	    
		$('#dialog-delete-confirm').off().click(function(e) {
		   e.preventDefault();
		   
		   $('#dialog-delete-confirm').attr('disabled','disabled');
		   	
			// send request
			$.delete(model.url + '/' + rid, function(response) {
				if (response && response.success)
				{
					// remove row from local store
					for( var i in rows )
					{
						if( rows[i][model.idFieldName] == rid )
							rows.splice(i, 1);
					}

					// remove row from DOM
					$(parent).fadeOut(500, function() {
						if (typeof(dTable) == 'object')
							dTable.fnDeleteRow(this);
						else
							$(this).remove();
					});					

					$('#dialog-delete-item').modal('hide');
				}
				
				$('#dialog-delete-confirm').removeAttr('disabled');
		    });
		    
		    return false;
		}); 
	    
	    $('#dialog-delete-item').modal();
	    
	    return false;
	});	
	
	$('body').delegate('a.change-password','click',function(e) {
		e.preventDefault();
		
		var field_name = $(this).attr('name');
		
		html = '<input class="input-large" type="password" name="' + field_name + '" value="" autocomplete="off" /> ';

		$(this).before(html).removeClass('change-password').addClass('cancel-change-password').html('Cancel');
				
		return false;
	});
	
	$('body').delegate('a.cancel-change-password','click',function(e) {
		e.preventDefault();
		
		$(this).prev('input[type=password]').remove();
		
		$(this).removeClass('cancel-change-password').addClass('change-password').html('Change');
		
		return false;
	});
	
	$('body').delegate('.checkbox-field','click',function(e) {
    	val = this.checked;
    	if( this.checked )
	    	$(this).prev('input[type=hidden]').attr('disabled','disabled');
	    else
	    	$(this).prev('input[type=hidden]').removeAttr('disabled');
	});
});