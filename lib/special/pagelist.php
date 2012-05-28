<?php
	$pages=array();
	$dirs=array(DATA_DIR);

	while($dirs){
		$current_dir=array_shift($dirs);
		$new_dirs=glob($current_dir.'/*', GLOB_ONLYDIR);
		if($new_dirs){
			$dirs=array_merge($dirs?$dirs:array(), $new_dirs);
		}
		$new_files=glob($current_dir.'/META-*.json');
		foreach($new_files as $file){
			if(preg_match('#/META-(.*?)\.json$#', $file, $matches)){
				$page_url=preg_replace('#^'.DATA_DIR.'/*#', '', ($current_dir.'/'.$matches[1]));
				//$page_data=json_decode(file_get_contents($file), true);
				$pages[]=array($page_url, Page::load($page_url));
			}
		}
	}

	foreach($pages as $page){
		printf('<a href="%s">%s</a><br />', dx_link($page[0]), $page[1]->Title);
	}