<?php
	define('LIB_DIR',			'lib/');
	define('DATA_DIR',			'data/');
	
	define('ZYDECO_DIR',		'../zydeco/src/');
	//define('URL_SUFFIX',		'html');
	define('URL_SUFFIX',		''); //DEPRECATED!
	define('INDEX_PAGE',		'index');

	include ZYDECO_DIR.'ZydecoNode.php';
	include ZYDECO_DIR.'ZydecoParser.php';

	include LIB_DIR.'helpers.php';
	include LIB_DIR.'diff.php';
	include LIB_DIR.'base.php';
	include LIB_DIR.'stem.php';
	include LIB_DIR.'search.php';
	include LIB_DIR.'Page.php';
	include LIB_DIR.'User.php';

	$_CONFIG['Location']=
		'http'.((isset($_SERVER['HTTPS'])&&$_SERVER['HTTPS']&&$_SERVER['HTTPS']!='off')?'s':'').'://'
		.$_SERVER['HTTP_HOST']
		.dirname($_SERVER['SCRIPT_NAME']).'/';
	$_CONFIG['CleanURLs']=false;
	$_CONFIG['Theme']='default';
	$_CONFIG['SiteName']='Doxa Test';
	$_CONFIG['PasswordHashAlgorithm']='BCrypt';
	$_CONFIG['PasswordHashRounds']=11; //leave null for default
	$_CONFIG['UserPageURL']='users';

	User::loadAll();

	ob_start();
	try{
		if(dx_page_type()=='special'){
			$special_page='lib/special/'.preg_replace('#[^A-Za-z0-9_-]#', '', dx_url(1)).'.php';
			if(!file_exists($special_page)) throw new Exception("The requested special page does not exist.", 404);
			include $special_page;
		}else{
			try{
				$page=Page::load(dx_url());
			}catch(Exception $e){
				if($e->getCode()!=404) throw $e;
				$page=null;
			}
			$act=dx_act();
			include 'lib/activities/'.$act.'.php';
		}
	}catch(Exception $e){
		switch($e->getCode()){
			case 404:
				header('HTTP/1.1 404 Not Found');
				$_THEME['Title']='Page Not Found';
				//echo 'Page not found';
				echo $e->getMessage();
				break;
			default:
				header('HTTP/1.1 500 Internal Server Error');
				$_THEME['Title']='Internal error';
				printf('Internal error: %s: %s (code: %d)', get_class($e), $e->getMessage(), $e->getCode());
				break;
		}
	}
	$out=ob_get_clean();
	if(dx_go()){
		dx_go(null, true);
		return;
	}
	include dx_theme('header.php', false);
	echo $out;
	include dx_theme('footer.php', false);

	User::saveAll();