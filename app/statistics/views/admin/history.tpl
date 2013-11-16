{extends file="$adminViewsDir/parent.tpl"}
{block name=header}
<link rel="stylesheet" href="//ajax.googleapis.com/ajax/libs/jqueryui/1.9.2/themes/base/jquery-ui.css" type="text/css" media="all" />
<style type="text/css">
	.stat-toolbar {
		margin: 18px 0 0;
	}

	#theChart {
		background: #eee;
		border-radius: 1em;
		padding: 10px;
	}
</style>
<script type="text/javascript" src="https://www.google.com/jsapi"></script>
<script type="text/javascript">
	var history = {json_encode($history)};
	
	function refreshView()
	{
		window.location = '/admin/statistics/history/' + $('#metric').val() + '?start=' + $('#start').val() + '&end=' + $('#end').val();
	}
	
	$(function() {
		$('.datepicker').datepicker();
		
		$('#metric').change(refreshView);
		$('.datepicker').change(refreshView);		
	});
	
	google.load("visualization", "1", { packages:["corechart"] });
	google.setOnLoadCallback(drawChart);

	function drawChart() {
		var raw = [['Date', '{$metric}']];

		// parse time series data
		var start = moment('{$start}').startOf('day');
		var end = moment('{$end}').endOf('day');
		var d = start.clone();
		
		for (var i = 0; i <= end.diff(start, 'days'); i++) {
			
			// look up numbers
			var key = d.format('MM/DD/YYYY');
			var value = (typeof history[key] != 'undefined') ? parseFloat(''+history[key]) : 0;
			if (isNaN(value)) value = 0;

			raw.push([d.format('M/D'), value]);
			
			// add one day
			d.add('days', 1);
		}
		
		var data = google.visualization.arrayToDataTable(raw);
	
		var options = {
			title: '{$metric}',
			legend: {
				position: 'none'
			},
			colors: ['#08c'],
			theme: 'maximized',
			hAxis: {
				baselineColor: 'none'
			}
		};
		
		var chart = new google.visualization.LineChart(document.getElementById('theChart'));
		chart.draw(data, options);
	}
	
	$(window).resize(drawChart);
</script>
{/block}
{block name=main}

<div class="btn-toolbar stat-toolbar pull-right">
	<div class="btn-group">
		<a href="/admin/statistics" class="btn btn-default">Overview</a>
		<a href="/admin/statistics/history" class="btn btn-default active">History</a>
	</div>
</div>

<h1>Statistics</h1>
<hr/>

<form>
	<div class="row">
		<div class="col-md-2">
		 	<select name="metric" id="metric" class="form-control">
				{foreach from=$metrics item=m}
				<option value="{$m}" {if $m==$metric}selected="selected"{/if}>{$m}</option>
				{/foreach}
			</select>
		</div>
		<div class="col-md-2">
			<div class="input-group">
				<span class="input-group-addon">
					<span class="glyphicon glyphicon-calendar"></span>
				</span>
				<input id="start" class="form-control datepicker" type="text" value="{$start}" />
			</div>
		</div>
		<div class="col-md-2">
			<div class="input-group">
				<span class="input-group-addon">
					<span class="glyphicon glyphicon-calendar"></span>
				</span>
				<input id="end" class="form-control datepicker" type="text" value="{$end}" />
			</div>
		</div>
	</div>
</form>

{if $history}
	<div id="theChart"></div>
{else}
	<br/>
	<div class="alert alert-danger">No history exists for the metric <strong>{$metric}</strong>.</div>
{/if}

{/block}