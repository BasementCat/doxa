<?php
	if(!$page){
		$page=new Page();
		$page->Author='Anonymous ('.dx_user_address().')';
		//$page->Title=str_replace('_', ' ', array_pop(explode('/', dx_url())));
		$_urlparts=explode('/', dx_url());
		$page->Title=str_replace('_', ' ', array_pop($_urlparts));
		if(!$page->Title) $page->Title='Index';
		$page->Comment='New page.';
	}else{
		$page->Comment='';
	}
	if($user=User::me()){
		$page->Editor=$user->Username;
	}else{
		$page->Editor='Anonymous ('.$_SERVER['REMOTE_ADDR'].')';
		dx_note('warning', "You are not logged in! Your changes will be attributed to %s", $page->Editor);
	}
	$_THEME['Title']='Edit Page: '.$page->Title;

	if(isset($_POST['do'])){
		$page->Title=$_POST['Title'];
		$page->Data=$_POST['Data'];
		$page->Comment=$_POST['Comment'];
		switch($_POST['do']){
			case 'Preview':
				dx_note('warning', 'This is only a preview!  Your changes have not yet been saved.');
				include 'lib/activities/view.php';
				echo '<hr />';
				break;
			case 'Save':
				if(!$page->Comment){
					dx_note("error", "Please enter a comment for your edit.");
				}else{
					if($page->save()){
						dx_note("info", "Your changes have been saved.");
					}else{
						dx_note("error", "There was a problem saving your changes.");
					}
				}
				break;
			case 'Cancel':
				dx_go(dx_link(dx_url()));
				return;
		}
	}
	?>
	<form method="post" action="<?php echo dx_link(dx_url(), array('a'=>'edit')); ?>">
		<label for="Title">
			Title
			<input type="text" name="Title" id="Title" value="<?php echo $page->Title; ?>" />
		</label>
		<label for="Data">
			Body
			<textarea name="Data" id="Data"><?php echo $page->Data; ?></textarea>
		</label>
		<label for="Comment">
			Comment
			<input type="text" name="Comment" id="Comment" value="<?php echo $page->Comment; ?>" />
		</label>
		<label for="Editor">
			Editor<br />
			<a href="<?php echo dx_link(sprintf("%s/%s", $_CONFIG['UserPageURL'], $page->Editor)); ?>"><?php echo $page->Editor; ?></a>
		</label>
		<label>
			Finish<br />
			<input type="submit" name="do" value="Preview" />
			<input type="submit" name="do" value="Save" />
			<input type="submit" name="do" value="Cancel" />
		</label>
	</form>