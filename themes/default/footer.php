			</div>
			<!--<div id="right">
				<ul id="nav">
					<?php
					if(dx_page_type()=='page'){
						$activities=array(
							'view'=>'View',
							'edit'=>'Edit',
							'talk'=>'Talk'
						);
						foreach($activities as $act_url=>$act){
							printf('<li><a href="%s">%s</a></li>',
								dx_link(dx_url(), array('a'=>$act_url)),
								$act
							);
						}
					}
					?>
				</ul>
				<div class="box">
					<h2 style="margin-top:17px">Recent Entries</h2>
					<ul>
						<li><a href="#">Recent Entries1</a> <i>01 Des 06</i></li>
						<li><a href="#">Recent Entries2</a> <i>01 Des 06</i></li>
						<li><a href="#">Recent Entries3</a> <i>01 Des 06</i></li>
						<li><a href="#">Recent Entries4</a> <i>01 Des 06</i></li>
						<li><a href="#">Recent Entries5</a> <i>01 Des 06</i></li>
					</ul>
				</div>
			</div>-->
			<div id="clear"></div></div>
			<div id="footer">
				<p>
					Design &copy; 2006 <a href="http://loadfoo.org/" rel="external">LoadFoO</a>.
					Valid <a href="http://jigsaw.w3.org/css-validator/check/referer" rel="external">CSS</a> &amp;
					<a href="http://validator.w3.org/check?uri=referer" rel="external">XHTML</a>
				</p>
			</div>
		</div>
	</body>
</html>