{extends file='module.tpl'}
{block name='content'}
	<table id="dataTable" cellpadding="0" cellspacing="0" border="0"></table>
	<script type="text/javascript">
	var edit_permission = 0;
	var delete_permission = 0;
	var sorting = [[2,'asc']]; // set sorting column and direction
	var length = 255;

	var data_headers = [
						{ name : 'status_color', title : '', type: 1 },
						{ name : 'link_name', title : '', type: 1 },
						{ name : 'link2_name', title : '', type: 1 },
						{ name : 'link', title : '', type : 10, filter : '<a href="![value[link]]!">![value[link_name]]!</a>', nosort : true, truncate : false },
						{ name : 'link2', title : '', type : 10, filter : '<a href="![value[link2]]!">![value[link2_name]]!</a>', nosort : true, truncate : false },
						{ name : 'name', title : 'Name', type : 10 },
						{ name : 'description', title : 'Description', type : 10, width : "400px" },
						{ name : 'author', title : 'Author', type : 10, nowrap : true },
						{ name : 'version', title : 'Version', type : 10 },
						{ name : 'required', title : 'Required', type : 10 },
						{ name : 'status', title : 'Status', type : 2, filter : '<span style="color: ![value[status_color]]!">![value[status]]!</span>' }
	];
			
	$(document).ready(dt_loadData);

	</script>
{/block}