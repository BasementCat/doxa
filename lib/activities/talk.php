<?php
	if(!$page) throw new Exception('You cannot talk on a page that does not exist.', 404);
	$_THEME['Title']='Talk: '.$page->Title;
	$uparser=new ZydecoParser();
	$uparser->HrefBase=dx_link('user');
	$mparser=new ZydecoParser();
	$mparser->HrefBase=dx_link('/');
	if($page->Talk){
		foreach($page->Talk as $talk){
			list($author, $message)=$talk;
			$uparser->setText($author);
			$mparser->setText($message);
			printf('<div class="talk-author">%s</div><div class="talk-message">%s</div>',
				$uparser->getHTML(),
				$mparser->getHTML()
			);
		}
	}
	?>
	<hr />
	