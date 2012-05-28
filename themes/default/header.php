<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<?php global $_THEME, $_CONFIG; ?>
<!-- ==========================================================	-->
<!--	Created by Devit Schizoper                          	-->
<!--	Created HomePages http://LoadFoo.starzonewebhost.com   	-->
<!--	Created Day 01.12.2006                              	-->
<!-- ========================================================== -->
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
	<head>
		<meta http-equiv="content-type" content="text/html;charset=utf-8" />
		<meta name="author" content="LoadFoO" />
		<title><?php echo $_THEME['Title'], ' - ', $_CONFIG['SiteName']; ?></title>
		<link rel="stylesheet" type="text/css" href="<?php echo dx_theme('css/style.css'); ?>" media="screen" />
		<link rel="stylesheet" type="text/css" href="<?php echo dx_theme('css/addon.css'); ?>" media="screen" />
	</head>

	<body>
		<div id="wrap">
			<div id="top">
				<h2><a href="<?php echo dx_link(''); ?>" title="<?php echo $_CONFIG['SiteName']; ?>"><?php echo $_CONFIG['SiteName']; ?></a></h2>
				<div id="menu">
					<ul>
						<?php
						if(dx_page_type()=='page'){
							$activities=array(
								'view'=>'View',
								'edit'=>'Edit',
								'history'=>'History',
								'talk'=>'Talk'
							);
							foreach($activities as $act_url=>$act){
								printf('<li><a href="%s" class="%s">%s</a></li>',
									dx_link(dx_url(), array('a'=>$act_url)),
									$act_url==dx_act()?'current':'',
									$act
								);
							}
						}
						?>
					</ul>
				</div>
			</div>
		<div id="content">
			<div id="left">
				<h1><?php echo $_THEME['Title']; ?></h1>
				<div id="notecontainer">
					<?php foreach(array('error', 'warning', 'info') as $notetype){
						$notes=dx_note($notetype);
						if($notes){
							printf('<div class="notes note_%s"><ul>', $notetype);
							foreach($notes as $note){
								printf('<li>%s</li>', $note);
							}
							printf('</ul></div>');
						}
					} ?>
				</div>