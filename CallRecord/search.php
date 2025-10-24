<?php 
include('includes/header.php'); 
?>
<?php 
if(!isset($_SESSION['login'])){	$model->redirect('login.php');} 
 ?>	
<link rel="stylesheet" href="//192.168.1.154/callrecord/ui/1.11.2/themes/smoothness/jquery-ui.css"><script src="//192.168.1.154/callrecord/jquery-1.10.2.js"></script><script src="//192.168.1.154/callrecord/ui/1.11.2/jquery-ui.js"></script><script>$(function() {$( "#datepicker" ).datepicker({dateFormat:'yy-mm-dd',});$( "#datepicker1" ).datepicker({dateFormat:'yy-mm-dd',});});</script>
   <div class="outerlayer">
		      <div class="outerlayer1">
			     <div class="header_botm">
				   <div class="header_btm_lft">
				    <h2><a href="index.php" style="color:#ced3d9;">Show all Records</a></h2>
				   </div>
				   <div class="header_btm_cntr">
				  <h2> <a href="search.php" style="color:#ced3d9;">Search</a></h2>
				   </div>
				   <div class="header_btm_cntr">
				    <h2><a style="color:#ced3d9;">Please enter the search criteria</a></h2>
				   </div>
				 </div>
				 <div class="content">
				 <form action="index.php" method="POST">
				    <table style="width:50%;padding:15px;">
					<tr><td>Description</td><td><input type="text" name="name"></td></tr>
					<tr><td>Date Start</td><td><input type="text" name="date" id="datepicker"></td></tr>
					<tr><td>Date End</td><td><input type="text" name="enddate" id="datepicker1"></td></tr>
					<tr><td>Other Party</td><td><input type="text" name="other_party"></td></tr>
					<tr><td>Service Group</td><td><input type="text" name="service_group"></td></tr>
					<tr><td>Call ID</td><td><input type="text" name="call_id"></td></tr>
					<tr><td></td><td><input type="submit" value="Search">
					<input type="hidden" name="action" value="search">
					</form>
					<div class="content_end"> 
					</div>
				 </div>
			  </div>
		   </div>


<?php include('includes/footer.php'); ?> 