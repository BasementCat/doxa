<?php
	if(!$page) throw new Exception(sprintf('The page that you requested does not exist. <a href="%s">Edit this page</a>', dx_link(dx_url(), array('a'=>'edit'))), 404);
	global $_THEME;
	$_THEME['Title']="History: ".$page->Title;
	if(isset($_GET['compare_new'])&&isset($_GET['compare_old'])){
		$r1=$page->revision($_GET['compare_old']);
		$r2=$page->revision($_GET['compare_new']);
		$diff=diff($r1->Data, $r2->Data);
		?>
		<table class="source">
			<thead>
				<tr>
					<th>1</th>
					<th>2</th>
					<th>Line</th>
				</tr>
			</thead>
			<tbody>
				<?php
					$r1_l=1;
					$r2_l=1;
					$r1_s=false;
					$r2_s=false;
					foreach(explode("\n", $diff) as $line){
						if(!$line) continue;
						$op=$line[0];
						$line=substr($line, 2);
						$lineclass="";
						if($op=='<'){
							$r2_s=true;
							$lineclass="removed";
						}elseif($op=='>'){
							$r1_s=true;
							$lineclass="added";
						}
						?>
						<tr>
							<td class="lineno"><?php echo $r1_s?'&nbsp;':$r1_l ?></td>
							<td class="lineno"><?php echo $r2_s?'&nbsp;':$r2_l ?></td>
							<td class="line <?php echo $lineclass; ?>"><?php echo $line; ?></td>
						</tr>
						<?php
						if(!$r1_s) $r1_l++;
						if(!$r2_s) $r2_l++;
						$r1_s=false;
						$r2_s=false;
					}
				?>
			</tbody>
		</table>
		<?php
		return;
	}
?>
<form method="get" action="<?php echo dx_link(dx_url()); ?>">
	<input type="hidden" name="a" value="history" />
	<input type="submit" value="Compare Selected Revisions" />
	<table>
		<tr>
			<th>&nbsp;</th>
			<th>&nbsp;</th>
			<th>Revision</th>
			<th>Editor</th>
			<th>Comment</th>
		</tr>
		<?php foreach($page->revisionList() as $rev=>$rd): ?>
			<tr>
				<td><input type="radio" name="compare_old" id="compare_old_<?php echo $rev; ?>" value="<?php echo $rev; ?>" <?php if($rev+1==$page->CurrentRevision) echo 'checked="checked"'; ?> /></td>
				<td><input type="radio" name="compare_new" id="compare_new_<?php echo $rev; ?>" value="<?php echo $rev; ?>" <?php if($rev==$page->CurrentRevision) echo 'checked="checked"'; ?> /></td>
				<td><?php printf("%d", $rev); ?></td>
				<td><?php echo $rd[0]; ?></td>
				<td><?php echo $rd[1]; ?></td>
			</tr>
		<?php endforeach; ?>
	</table>
	<input type="submit" value="Compare Selected Revisions" />
</form>