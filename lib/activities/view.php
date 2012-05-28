<?php
	if(!$page) throw new Exception(sprintf('The page that you requested does not exist. <a href="%s">Edit this page</a>', dx_link(dx_url(), array('a'=>'edit'))), 404);
	global $_THEME;
	$_THEME['Title']=$page->Title;

	echo dx_formatted_breadcrumbs($page), '<br />';

	$parser=new ZydecoParser($page->Data);
	$parser->HrefBase=dx_link('/');
	$parser->parse();
	echo $parser->getHTML();