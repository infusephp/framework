<!DOCTYPE HTML>
<html>
<head>
	<title>Infuse Framework Setup</title>
	
	<meta name="robots" content="noindex,nofollow" />

	<link href="http://netdna.bootstrapcdn.com/twitter-bootstrap/2.3.1/css/bootstrap-combined.min.css" rel="stylesheet" type="text/css" />
	<style type="text/css">
		* {
			-moz-box-sizing: border-box;
			-webkit-box-sizing: border-box;
			box-sizing: border-box;
		}
		
		h1 {
			margin: 45px 0;
		}
		
		#sidebar {
			text-align: right;
		}
		
		#sidebar li {
			line-height: 45px;
			color: #999;
		}
		
		#sidebar li.active {
			font-weight: bold;
			color: #000;
		}
		
		#sidebar li.complete {
			text-decoration: line-through;
		}
	
		#content {
			border-left: 1px solid #ccc;
			padding: 0 15px 15px 30px;
		}
		
		input[type=text],input[type=password] {
			height: 30px;
		}
		
		.form-actions {
			background: none;
			padding-left: 0;
			border: none;
		}
		
		#config-file {
			width: 100%;
			height: 500px;
		}
		
		footer {
			border-top: 1px solid #ccc;
			color: #999;
			padding: 15px;
		}
	</style>
	
	<script type="text/javascript" src="//ajax.googleapis.com/ajax/libs/jquery/1/jquery.min.js"></script>
	<script src="//netdna.bootstrapcdn.com/twitter-bootstrap/2.3.1/js/bootstrap.min.js"></script>
	<script type="text/javascript">
		
		
	</script>
</head>
<body>
	<div class="container">
		<div class="row">
			<div class="span2"></div>
			<div class="span10">
				<h1>Infuse Framework Setup</h1>
			</div>
		</div>
		
		<div class="row">
			<div class="span2" id="sidebar">
				<ul class="nav nav-list">
					<li class="<?php if($step==1) echo 'active';?> <?php if($step>1) echo 'complete';?>">
						Checklist
					</li>
					<li class="<?php if($step==2) echo 'active';?> <?php if($step>2) echo 'complete';?>">
						Database
					</li>
					<li class="<?php if($step==3) echo 'active';?> <?php if($step>3) echo 'complete';?>">
						Administrator
					</li>
					<li class="<?php if($step==4) echo 'active';?> <?php if($step>4) echo 'complete';?>">
						Config
					</li>
					<li class="<?php if($step==5) echo 'active';?> <?php if($step>5) echo 'complete';?>">
						Finish
					</li>
				</ul>
			</div>
			<div class="span10" id="content">
			<?php if( $step == 1 ) { ?>
				<h2>Welcome!</h2>
				
				<p class="lead">This installer will guide you through the setup of Infuse Framework.</p>
				
				<h3>Checklist</h3>
				
				<?php foreach( $checklist as $check ) { ?>
					<?php if( $check[ 'success' ] ) { ?>
						<?php if( val( $check, 'warning' ) ) { ?>
							<p class="alert alert-warning"><i class="icon-remove"></i> <?php echo $check['error_message'];?></p>						
						<?php } else { ?>
							<p class="alert alert-success"><i class="icon-ok"></i> <?php echo $check['success_message'];?></p>
						<?php } ?>
					<?php } else { ?>
						<p class="alert alert-error"><i class="icon-remove"></i> <?php echo $check['error_message'];?></p>
					<?php } ?>
				<?php } ?>
				
				<div class="form-actions">
					<a href="/install/database" class="btn btn-primary btn-large" <?php if($disabled) echo 'disabled="disabled"';?>>Next &rarr;</a>
				</div>
			<?php } else if( $step == 2 ) { ?>
				<a href="/install/checklist" class="btn">&larr; Previous Step</a>

				<h2>Database Setup</h2>
				
				<p class="lead">Infuse Framework requires a data store to function. Create a blank database and enter the credentials below to have the database setup.</p>
				
				<?php if($error) { ?><div class="alert alert-error"><?php echo $error; ?></div><?php } ?>
				
				<form action="/install/database" method="post">
					<label class="contol-label">Type</label>
					<select name="type">
						<option value="mysql">MySQL</option>
						<option value="sqlite">SQLite</option>
						<option value="pgsql">PostgreSQL</option>
						<option value="sqlsrv">Microsoft SQL Server</option>
						<option value="oci">Oracle</option>
					</select>
					
					<label class="control-label">Host</label>
					<input type="text" name="host" value="<?php echo $req->request('host');?>" />
					
					<label class="control-label">Username</label>
					<input type="text" name="user" value="<?php echo $req->request('user');?>" />
					
					<label class="control-label">Password</label>
					<input type="password" name="password" value="<?php echo $req->request('password');?>" />
					
					<label class="control-label">Database Name</label>
					<input type="text" name="name" value="<?php echo $req->request('name');?>" />
					
					<div class="form-actions">
						<input type="submit" class="btn btn-primary btn-large" value="Setup Database" />
						<a href="/install/administrator" class="btn btn-large">Skip Step</a>
					</div>
				</form>
			<?php } else if( $step == 3 ) { ?>
				<a href="/install/database" class="btn">&larr; Previous Step</a>
				
				<h2>Setup Administrator</h2>
				
				<?php foreach( $signupErrors as $error) { ?>
					<div class="alert alert-error">
						<?php echo $error['message']; ?>
					</div>
				<?php } ?>
				
				<form action="/install/administrator" method="post">
					<label class="control-label">Name</label>
					<input type="text" name="name" value="<?php echo $req->request('name');?>" />
					
					<label class="control-label">E-mail Address</label>
					<input type="text" name="user_email" value="<?php echo $req->request('user_email');?>" />
					
					<label class="control-label">Password</label>
					<input type="password" name="user_password[]" />
					
					<label class="control-label">Confirm Password</label>
					<input type="password" name="user_password[]" />
					
					<div class="form-actions">
						<input type="submit" class="btn btn-primary btn-large" value="Add Administrator" />
						<a href="/install/config" class="btn btn-large">Skip Step</a>
					</div>
				</form>
				
			<?php } else if( $step == 4 ) { ?>
				<a href="/install/database" class="btn">&larr; Previous Step</a>
			
				<h2>Site Configuration</h2>
				
				<form action="/install/config" method="post">
					<label class="control-label">Title</label>
					<input type="text" name="site[title]" value="<?php echo $tempConfig['site']['title'];?>" />
					
					<label class="control-label">E-mail</label>
					<input type="text" name="site[email]" value="<?php echo $tempConfig['site']['email'];?>" />
					
					<label class="control-label">Host Name</label>
					<input type="text" name="site[host-name]" value="<?php echo $tempConfig['site']['host-name'];?>" />
					
					<label class="control-label">Has SSL</label>
					<input type="checkbox" name="site[ssl-enabled]" value="1" <?php if($tempConfig['site']['ssl-enabled']) echo 'checked="checked';?> />
					<br/><br/>
					<label class="control-label">Production Site</label>
					<input type="checkbox" name="site[production-level]" value="1" <?php if($tempConfig['site']['production-level']) echo 'checked="checked';?> />
					<br/><br/>
					<label class="control-label">Time Zone</label>
					<?php echo get_tz_options($currentTimezone, 'site[time-zone]'); ?>
										
					<h3>E-mail</h3>
					<p class="lead">Supply SMTP credentials to send e-mail from the site (optional).</p>
					
					<label class="control-label">Default From Address</label>
					<input type="text" name="smtp[from]" value="<?php echo $tempConfig['smtp']['from']; ?>" />

					<label class="control-label">SMTP Username</label>
					<input type="text" name="smtp[username]" value="<?php echo $tempConfig['smtp']['username']; ?>" />

					<label class="control-label">SMTP Password</label>
					<input type="password" name="smtp[password]" />

					<label class="control-label">SMTP Port</label>
					<input type="text" name="smtp[port]" value="<?php echo $tempConfig['smtp']['port']; ?>" />
					
					<label class="control-label">SMTP Host</label>
					<input type="text" name="smtp[host]" value="<?php echo $tempConfig['smtp']['host']; ?>" />

					<div class="form-actions">
						<input type="submit" class="btn btn-primary btn-large" value="Generate Configuration" />
						<a href="/install/finish" class="btn btn-large">Skip Step</a>
					</div>
				</form>

			<?php } else if( $step == 5 ) { ?>
				<a href="/install/config" class="btn">&larr; Previous Step</a>

				<h2>Almost There</h2>
				
				<p class="lead">Below is the configuration file that we generated for you. Paste it into <strong>config.yml</strong> in the base directory to get started. You 
				may continue on to your site when this has been done.</p>
				
				<p class="alert alert-info">Also, delete the temporary copy in <strong>/temp/config.yml</strong> when you are finished saving your config.yml.</p>
				
				<textarea id="config-file"><?php echo $config;?></textarea>

				<div class="form-actions">
					<a href="/install/finish" class="btn btn-large btn-primary">I have setup config.yml</a>
				</div>
				
			<?php } ?>

				<footer>
					<p>Powered by <a href="https://github.com/jaredtking/infuse" target="_blank">Infuse Framework</a></p>
				</footer>
			</div>
		</div>
	</div>
</body>
</html>