<?php
	define('ZYDECO_DIR',		'../zydeco/src/');

	include ZYDECO_DIR.'ZydecoNode.php';
	include ZYDECO_DIR.'ZydecoParser.php';

	include 'lib/stem.php';

	echo '<pre>';

	$p=new ZydecoParser(file_get_contents('test-m.txt'));
	$p->MediaWikiTables=true;
	$root=$p->parse();
	/*$stems=array();
	foreach($root->getAll('_') as $textnode){
		foreach(preg_split("#\W+#", $textnode->Text) as $word){
			$stems[stem_porter2($word)]=true;
		}
	}
	echo implode("\n", array_unique(array_keys($stems)));*/
	echo $root->asText();
	//echo $root->render();