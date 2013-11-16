<?php

/**
 * @package infuse\framework
 * @author Jared King <j@jaredtking.com>
 * @link http://jaredtking.com
 * @version 0.1.16
 * @copyright 2013 Jared King
 * @license MIT
 */

namespace infuse;

?>
<!DOCTYPE html>
<html>
<head>
	<title>Infuse Framework Setup</title>
	
	<meta name="robots" content="noindex,nofollow" />

	<link href="http://netdna.bootstrapcdn.com/bootstrap/3.0.2/css/bootstrap.min.css" rel="stylesheet" type="text/css" />
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
			<div class="col-md-2"></div>
			<div class="col-md-10">
				<h1>Infuse Framework Setup</h1>
			</div>
		</div>
		
		<div class="row">
			<div class="col-md-2" id="sidebar">
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
			<div class="col-md-10" id="content">
			<?php if( $step == 1 ) { ?>
				<h2>Welcome!</h2>
				
				<p class="lead">This installer will guide you through the setup of Infuse Framework.</p>
				
				<h3>Checklist</h3>
				
				<?php foreach( $checklist as $check ) { ?>
					<?php if( $check[ 'success' ] ) { ?>
						<?php if( Util::array_value( $check, 'warning' ) ) { ?>
							<p class="alert alert-warning"><i class="icon-remove"></i> <?php echo $check['error_message'];?></p>						
						<?php } else { ?>
							<p class="alert alert-success"><i class="icon-ok"></i> <?php echo $check['success_message'];?></p>
						<?php } ?>
					<?php } else { ?>
						<p class="alert alert-danger"><i class="icon-remove"></i> <?php echo $check['error_message'];?></p>
					<?php } ?>
				<?php } ?>
				
				<p>
					<a href="/install/database" class="btn btn-primary btn-large" <?php if($disabled) echo 'disabled="disabled"';?>>Next &rarr;</a>
				</p>
			<?php } else if( $step == 2 ) { ?>
				<a href="/install/checklist" class="btn btn-default">&larr; Previous Step</a>

				<h2>Database Setup</h2>
				
				<p class="lead">Infuse Framework needs a data store to function. Create a blank database and enter the credentials below.</p>
				
				<?php if($error) { ?><div class="alert alert-danger"><?php echo $error; ?></div><?php } ?>
				
				<form action="/install/database" method="post" class="form-horizontal">
					<div class="form-group">
						<label class="control-label col-md-2">Type</label>
						<div class="col-md-4">
							<select name="type" class="form-control">
								<option value="mysql">MySQL</option>
								<option value="sqlite">SQLite</option>
								<option value="pgsql">PostgreSQL</option>
								<option value="sqlsrv">Microsoft SQL Server</option>
								<option value="oci">Oracle</option>
							</select>
						</div>
					</div>

					<div class="form-group">
						<label class="control-label col-md-2">Host</label>
						<div class="col-md-4">
							<input type="text" name="host" class="form-control" value="<?php echo $req->request('host');?>" />
						</div>
					</div>
					
					<div class="form-group">
						<label class="control-label col-md-2">Username</label>
						<div class="col-md-4">
							<input type="text" name="user" class="form-control" value="<?php echo $req->request('user');?>" />
						</div>
					</div>
					 
					 <div class="form-group">
						<label class="control-label col-md-2">Password</label>
						<div class="col-md-4">
							<input type="password" name="password" class="form-control" value="<?php echo $req->request('password');?>" />
						</div>
					</div>
					
					<div class="form-group">
						<label class="control-label col-md-2">Database Name</label>
						<div class="col-md-4">
							<input type="text" name="name" class="form-control" value="<?php echo $req->request('name');?>" />
						</div>
					</div>
					
					<div class="form-group">
						<div class="col-sm-offset-2 col-sm-10">
							<input type="submit" class="btn btn-primary btn-large" value="Setup Database" />
							<a href="/install/administrator" class="btn btn-large">Skip Step</a>
						</div>
					</div>
				</form>
			<?php } else if( $step == 3 ) { ?>
				<a href="/install/database" class="btn btn-default">&larr; Previous Step</a>
				
				<h2>Setup Administrator</h2>
				
				<?php foreach( $signupErrors as $error) { ?>
					<div class="alert alert-danger">
						<?php echo $error['message']; ?>
					</div>
				<?php } ?>
				
				<form action="/install/administrator" method="post" class="form-horizontal">
					<div class="form-group">
						<label class="control-label col-md-2">Name</label>
						<div class="col-md-4">
							<input type="text" name="name" class="form-control" value="<?php echo $req->request('name');?>" />
						</div>
					</div>
					
					<div class="form-group">
						<label class="control-label col-md-2">E-mail Address</label>
						<div class="col-md-4">
							<input type="text" name="user_email" class="form-control" value="<?php echo $req->request('user_email');?>" />
						</div>
					</div>
					
					<div class="form-group">
						<label class="control-label col-md-2">Password</label>
						<div class="col-md-4">
							<input type="password" name="user_password[]" class="form-control" />
						</div>
					</div>
					
					<div class="form-group">
						<label class="control-label col-md-2">Confirm Password</label>
						<div class="col-md-4">
							<input type="password" name="user_password[]" class="form-control" />
						</div>
					</div>
					
					<div class="form-group">
						<div class="col-sm-offset-2 col-sm-10">
							<input type="submit" class="btn btn-primary btn-large" value="Add Administrator" />
							<a href="/install/config" class="btn btn-large">Skip Step</a>
						</div>
					</div>
				</form>
				
			<?php } else if( $step == 4 ) { ?>
				<a href="/install/administrator" class="btn btn-default">&larr; Previous Step</a>
			
				<h2>Site Configuration</h2>
				
				<form action="/install/config" method="post" class="form-horizontal">
					<div class="form-group">
						<label class="control-label col-md-2">Title</label>
						<div class="col-md-4">
							<input type="text" name="site[title]" class="form-control" value="<?php echo $tempConfig['site']['title'];?>" />
						</div>
					</div>
					
					<div class="form-group">
						<label class="control-label col-md-2">E-mail</label>
						<div class="col-md-4">
							<input type="text" name="site[email]" class="form-control" value="<?php echo $tempConfig['site']['email'];?>" />
						</div>
					</div>

					<div class="form-group">
						<label class="control-label col-md-2">Host Name</label>
						<div class="col-md-4">
							<input type="text" name="site[host-name]" class="form-control" value="<?php echo $tempConfig['site']['host-name'];?>" />
						</div>
					</div>

					<div class="form-group">
						<label class="control-label col-md-2">Has SSL</label>
						<div class="col-md-4">
							<input type="checkbox" name="site[ssl-enabled]" value="1" <?php if($tempConfig['site']['ssl-enabled']) echo 'checked="checked';?> />
						</div>
					</div>

					<div class="form-group">
						<label class="control-label col-md-2">Production Site</label>
						<div class="col-md-4">
							<input type="checkbox" name="site[production-level]" value="1" <?php if($tempConfig['site']['production-level']) echo 'checked="checked';?> />
						</div>
					</div>

					<div class="form-group">
						<label class="control-label col-md-2">Time Zone</label>
						<div class="col-md-4">
							<?php echo Locale::locale()->timezoneOptions( $currentTimezone, 'site[time-zone]' ); ?>
						</div>
					</div>
					
					<h3>Sessions</h3>
					<div class="form-group">
						<label class="control-label col-md-2">Adapter</label>
						<div class="col-md-4">
							<select name="session[adapter]" class="form-control">
								<option value="database" <?php if($tempConfig['session']['adapter']=='database') echo 'selected="selected"';?>>Database</option>
								<option value="php" <?php if($tempConfig['session']['adapter']=='php') echo 'selected="selected"';?>>PHP</option>
								<option value="redis" <?php if($tempConfig['session']['adapter']=='redis') echo 'selected="selected"';?>>Redis</option>
							</select>
						</div>
					</div>

					<h3>E-mail</h3>
					<p class="lead">Supply SMTP credentials to send e-mail from the site (optional).</p>
					
					<div class="form-group">
						<label class="control-label col-md-2">Default From Address</label>
						<div class="col-md-4">
							<input type="text" name="smtp[from]" class="form-control" value="<?php echo $tempConfig['smtp']['from']; ?>" />
						</div>
					</div>

					<div class="form-group">
						<label class="control-label col-md-2">SMTP Username</label>
						<div class="col-md-4">
							<input type="text" name="smtp[username]" class="form-control" value="<?php echo $tempConfig['smtp']['username']; ?>" />
						</div>
					</div>

					<div class="form-group">
						<label class="control-label col-md-2">SMTP Password</label>
						<div class="col-md-4">
							<input type="password" name="smtp[password]" class="form-control" />
						</div>
					</div>

					<div class="form-group">
						<label class="control-label col-md-2">SMTP Port</label>
						<div class="col-md-4">
							<input type="text" name="smtp[port]" value="<?php echo $tempConfig['smtp']['port']; ?>" />
						</div>
					</div>
					
					<div class="form-group">
						<label class="control-label col-md-2">SMTP Host</label>
						<div class="col-md-4">
							<input type="text" name="smtp[host]" class="form-control" value="<?php echo $tempConfig['smtp']['host']; ?>" />
						</div>
					</div>

					<div class="form-group">
						<div class="col-sm-offset-2 col-sm-10">
							<input type="submit" class="btn btn-primary btn-large" value="Generate Configuration" />
							<a href="/install/finish" class="btn btn-large">Skip Step</a>
						</div>
					</div>
				</form>

			<?php } else if( $step == 5 ) { ?>
				<a href="/install/config" class="btn btn-default">&larr; Previous Step</a>

				<h2>Almost There</h2>
				
				<p class="lead">Below is the configuration file that we generated for you. Paste it into <strong>config.php</strong> in the base directory to get started. You 
				may continue on to your site when this has been done.</p>
				
				<p class="alert alert-info">Also, delete the temporary copy in <strong>/temp/config.php</strong> when you are finished saving your config.php.</p>
				
				<div class="form-group">
					<textarea id="config-file" class="form-control"><?php echo $configPhp;?></textarea>
				</div>

				<p>
					<a href="/install/finish" class="btn btn-large btn-primary">I have setup config.php</a>
				</p>
				
			<?php } ?>

				<footer>
					<p>Powered by <a href="https://github.com/jaredtking/infuse" target="_blank">Infuse Framework</a></p>
				</footer>
			</div>
		</div>
	</div>
</body>
</html>