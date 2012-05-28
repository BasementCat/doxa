<?php
	function dx_conf($key, $default=null, $except=false){
		global $_CONFIG;
		if(!isset($_CONFIG[$key])){
			if($except) throw new Exception('Missing configuration key: '.$key);
			return $default;
		}
		return $_CONFIG[$key];
	}
	function dx_link($internal_url, $qs=array()){
		$url=dx_conf('Location');
		if(!dx_conf('CleanURLs')) $url.='index.php/';
		$internal_url=preg_replace('#^/+#', '', $internal_url);
		if(preg_match('#^(.*?)/+$#', $internal_url, $matches)){
			$internal_url=$matches[1].'/';
		}
		$url.=$internal_url;
		if(URL_SUFFIX){
			if(!preg_match('#/$#', $url)&&!preg_match('#\.'.URL_SUFFIX.'$#', $url)){
				$url.='.'.URL_SUFFIX;
			}
		}
		if($qs){
			$url.='?'.http_build_query($qs);
		}
		return $url;
	}

	function dx_path(){
		if(isset($_SERVER['PATH_INFO'])) return trim($_SERVER['PATH_INFO']);
		return '';
	}

	function dx_user_address(){
		if(isset($_SERVER['REMOTE_HOST'])){
			return $_SERVER['REMOTE_HOST'];
		}else{
			//TODO: via config, allow forcing DNS lookups here
			return $_SERVER['REMOTE_ADDR'];
		}
	}

	function dx_act(){
		$acts=array('view', 'edit', 'talk', 'history');
		if(isset($_GET['a'])){
			if(in_array($_GET['a'], $acts)) return $_GET['a'];
		}
		return $acts[0];
	}

	function dx_theme($file='', $http=true){
		$u=$http?dx_conf('Location'):'./';
		return $u.'themes/'.dx_conf('Theme').'/'.$file;
	}

	function dx_url($idx=null){
		$url=str_rtrim(dx_path(), URL_SUFFIX);
		if($idx!==null){
			$url=explode('/', $url);
			return $url[$idx];
		}else{
			return $url;
		}
	}

	function dx_page_type(){
		$url=dx_url();
		if(preg_match('#^special(/.*)$#', $url)) return 'special';
		return 'page';
	}

	function dx_activity(){
		switch(dx_page_type()){
			case 'page':
				return $_GET['a'];
				break;
			case 'special':
				return null;
				default;
			default:
				throw new Exception('Unrecognized page type: '.dx_page_type());
		}
	}

	function dx_go($to=null, $setHeader=false){
		static $go_to=null;
		if($to!==null) $go_to=$to;
		if($setHeader&&$go_to) header('Location: '.$go_to);
		return $go_to;
	}

	function dx_note(){
		static $notes=array();
		$args=func_get_args();
		if(count($args)==0) return $notes;
		$type=array_shift($args);
		if(!isset($notes[$type])) $notes[$type]=array();
		if(!count($args)) return $notes[$type];
		$message=array_shift($args);
		$notes[$type][]=vsprintf($message, $args);
	}