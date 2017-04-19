<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<title>Show Thread</title>

<link href="../styles/deldev.css" rel="stylesheet" type="text/css" />
<link href="sparkui.css" rel="stylesheet" type="text/css" />
<link href="spark_example.css" rel="stylesheet" type="text/css" />
</head>
<body>
	<p>
		<a href="">Start over</a>
	</p>
	<?php

	ini_set('include_path',  ini_get('include_path') . PATH_SEPARATOR . './etc');
	require_once 'config_inc.php';
	require_once 'utils.php';

	require_once 'StaffDB.php';

	#print "form data: ";
	#print_r($_POST);

	// filter arguments
	$max_items = 10;
	$end_message_id = '';
	$match_value = '';

	$show_wiki_text = 0;
	$show_json_text = 0;

	$begin_date = '';
	$end_days = '';
	
	/*
	 * args
	*/
	if (isset($_POST['room_id'])){
		$room_id = $_POST['room_id'];

	}
	// search for messaes before this message
	$end_message_id;
	if (isset($_POST['end_message_id']) && $_POST['end_message_id'] != ''){
		$end_message_id = $_POST['end_message_id'];
		$show_thread = 1;
	}
	if (isset($_POST['end_date'])){
		$end_date = $_POST['end_date'];
		$show_thread = 1;
	}
	if (isset($_POST['end_days'])){
		$end_days = $_POST['end_days'];
		$show_thread = 1;
	}
	if (isset($_POST['match_value'])){
		$match_value = $_POST['match_value'];
		$show_thread = 1;
	}
	if (isset($_POST['max_items'])){
		$max_items = $_POST['max_items'];
		$show_thread = 1;
	}

	$wiki_ch = '';
	if (isset($_POST['show_wiki'])){
		#$show_wiki_text = 1;
		$wiki_ch = 'CHECKED';
	}
	$html_ch = '';
	if (isset($_POST['show_html'])){
		#$show_json_text = 1;
		$html_ch = 'CHECKED';
	}
	$json_ch = '';
	if (isset($_POST['show_json'])){
		#$show_json_text = 1;
		$json_ch = 'CHECKED';
	}
	$assign_ch = '';
	if (isset($_POST['assign_users'])){
		$assign_users = 1;
		$assign_ch = 'CHECKED';
	}
	$download_ch = '';
	if (isset($_POST['save_download'])){
		
		$download_ch = 'CHECKED';
	}
	$list_ch = '';
	if (isset($_POST['show_list'])){
		
		$list_ch = 'CHECKED';
	}
	

	require_once 'SparkThread.php';
	if ( isset($user_access_token)){
		$t = $user_access_token;
	}
	elseif( isset($spark_token)){
		$t = $spark_token;
	}
	$spt = new SparkThread($t);
	dprint("t=$t");
	if ( $spt->error ){
		raise_error("thread: failed " . $spt->error);
	}
	
//	$spt->setRoom($room_id, $room_name);
	$spt->setRoom($room_id);

	if ( $end_message_id) {
		$spt->setEndMessage($end_message_id);
	}
	elseif ( $end_date) {
		$spt->setDateRange(null, $end_date);
	}
	elseif ( $end_days) {
		$spt->setDaysRange(null, $end_days);
	}

	if ( $spt->error ) {
		#die("startup failed: " . $spt->error);
		raise_error("startup failed: " . $spt->error);
	}
	
	if ( $show_thread ){

		$room_name = $spt->getRoomName();
		print "<h3>Thread for '$room_name' </h3>";

		#$spt->getMessages( array( roomId => $room_id) );
		$spt->max_messages = $max_items;
		$spt->getThread();

		if ( $spt->num_messages > 0){
			
		$k = $spt->num_messages - 1;
		$m = $spt->messages[$k];
		$d = $m->created;
		$first_message_id = $m->id;
		$t = gettype($m);
		print "<p>" . $spt->num_messages . " Messages(before) type=$t d=$d  id=$first_message_id </p>";
		
		}
		$spt->reviewThread( $match_value);
		print "<p>" . $spt->num_messages . " Messages(after)</p>";

		if ( $spt->error ) {
			#			die("get failed: " . $spt->error);
			raise_error("get failed: " . $spt->error);
		}

		/*
		 * summary
		*/
		
		if ( $spt->messageOrderOK() ){
			print "<p>Order OK </p>";
		}
		else {

			print "<p>NOT in Order </p>";
		}

		$msg = $spt->getMessageRange();
		// print "<p> meesage ID: " . $msg[0] . "  -- " . $msg[1];
		
		print "<p>" . $spt->num_messages . " Messages</p>";
		print "<p>Begin: " . $spt->begin_date 
		. '<input type=text name=begin_id size=10 value="' . $msg[0] . '" />'
		. " <br/>End:" . $spt->end_date 
		. '<input type=text name=end_id size=10 value="' . $msg[1] . '" />'
		
		. "</p>";
		print "<p>Days Begin: " . $spt->begin_days. " End:" . $spt->end_days . "</p>";

		
		if ( $assign_users){
			
		$staff = new StaffDB();

		//
		$staff->prepareQuery( array( 'email'),  array( 'active' => 1) );
		$i = 0;
		foreach( array_keys($spt->members) as $spt_user_id ){

			$i++;
			$email = 'xxx';
			$email = $spt->members[$spt_user_id]['email'];

			$rows = $staff->executeQuery( array(':email' => $email) );
			if ( $staff->lastCount() == 1) {
				$row = $rows[0];
				$name = $row['name'];
				$name .= ' / ' . $row['bincID'];

			}
			else {

				$name = '???';
			}

			$spt->members[$spt_user_id]['name'] =  $row['name'];
			$spt->members[$spt_user_id]['user_id'] =  $row['bincID'];
			$spt->members[$spt_user_id]['label'] = $i;
			if ( $row['wiki_name']){

				$spt->members[$spt_user_id]['wiki_name'] = $row['wiki_name'];
			}
			else {

				$spt->members[$spt_user_id]['wiki_name'] = '';
			}

			#print " type=" . gettype($rows);
			#print "row $row ";
			#print_r($row);
			#print " type=" . gettype($row);
			#print " n=" . $staff->lastCount();
			#print " q=" . $staff->getSQL();
			#			print "<p>" . $name . " ($email)  [$spt_user_id] </p>";
			print "<p>" . $spt->getMemberName($spt_user_id) . " </p>";
			print "<p>Wiki " . $spt->getMemberName($spt_user_id, 'wiki') . " </p>";
		}
		} // assign users 


		if ( $wiki_ch ) {
			$nr = $spt->num_messages * 2;
			$nc = 100;
			dprint("nr=$nr nc=$nc");
			$spt->heading = "Example";
			print "<div class='raw_content'><textarea rows=$nr cols=$nc >";
			#print_r( $spt->rooms);
			print $spt->asText('wiki');
			print "</textarea></div>";
		}


		if ( $html_ch) {
			print "<div class='raw_content'>";
			#print_r( $spt->rooms);
			#print $spt->asText();
			#print $spt->asText('html');
			print $spt->asText('json');
			print "</div>";
		}
		if ( $list_ch ){

			print "<div class='raw_content'><pre>";


			print $spt->asText('list');
			print "</pre></div>";
			
		}
		
		if ($download_ch){
			$n = rand(1, 1000);
			$jfile = sprintf("message_%04d.json",  $n);
			
			$jfile = 'message.json';
			$jfile = "$download_dir/$jfile";
			dprint("save as $jfile");
			$fh = fopen($jfile,'w');
			if ($fh == false){
				raise_error("can't open $jfile");
			}
			
			fwrite($fh, $spt->asJSON());
			fclose($fh);
			
			$link = '<a href="' . $jfile . '">Downlaod JSON</a>';
			print "<p>Saved as: $jfile  $link </p>";
		}
		if ( $json_ch) {
			
			print "<div class='raw_content'><pre>";
			print  $spt->asJSON();
			print "</pre></div>";
			
			print "<div class='raw_content'>";
			print "<textarea name=thread_json rows=5 cols=120 >" . $spt->asJSON() . "</textarea>";
			// print "<textarea name=thread_json rows=5 cols=120 >" . $spt->asText('json') . "</textarea>";
			#print_r( $spt->rooms);
			#print $spt->asJSON();
			print "</div>";
		}
		

		/*
		 *
		print '<form method="post" action="">';
		print "<ul>";
		$n = 0;
		foreach ($spt->messages as $messages ){
			if ($n >= $max_items ){
				break;
		}
			
		}
		print "</ul>";
		print '</form>';
		*/

	} // get

	?>

	<hr>
	<form method="post" action="">

		<div>
			Room ID: <input type=text name="room_id"
				value="<?php echo $room_id; ?>" />
		</div>

		<div>
		 <?php $d = 0;
		 
		 ?>
		 Messages more than :
		  <input
			type=text name=end_days value="<?php echo $end_days; ?>" /> Days ago
		</div>
		Match: <input type=text name=match_value
			value="<?php echo $match_value; ?>" /> Max: <input type=test
			name="max_items" value="<?php echo $max_items; ?>" />

		
		<div>
			<span>Show as Wiki text<input type=checkbox name="show_wiki"
			<?php echo $wiki_ch; ?> /> </span>
			
			<span>Show as HTML text<input type=checkbox
				name="show_html" <?php echo $html_ch; ?> /></span>
			<span>Show as JSON text<input type=checkbox
				name="show_json" <?php echo $json_ch; ?> /></span>
  <span>Assign users<input type=checkbox
				name="assign_users" <?php echo $assign_ch; ?> /></span>
				
			<span>Show list<input type=checkbox
				name="show_list" <?php echo $list_ch; ?> /></span>
				
			<span>Save as file<input type=checkbox
				name="save_download" <?php echo $download_ch; ?> /></span>
		</div>

		<div>
			<input type="submit" name="list_messages" value="Show thread" />
		</div>
	</form>

	
	<hr>
	<?php 

	$days_ago = '';
	$message_date = '';
	$begin_date = '';
	$before_message_id = '';
	if (isset($spt)){
		
		$msg = $spt->getMessageRange();
		$before_message_id = $msg[0];
		$before_message_id = $first_message_id;
		
		$days_ago = $spt->begin_days;

		$begin_date = $spt->begin_date;

}
	
	?>
	<form method="post" action="">

		<div>
			Room ID: <input type=text name="room_id"
				value="<?php echo $room_id; ?>" />
		</div>

		<div>
			Before message ID: <input type=text name=end_message_id
				value="<?php echo $before_message_id; ?>" />
				
				<span><?php echo $begin_date; ?> date </span>
				<span><?php echo $days_ago;  ?> days ago</span>
		</div> 
		<div>
		Match: <input type=text name=match_value
			value="<?php echo $match_value; ?>" /></div>Max: <input type=test
			name="max_items" value="<?php echo $max_items; ?>" />

		<div>
			<span>Show as Wiki text<input type=checkbox name="show_wiki"
			<?php echo $wiki_ch; ?> /> </span>
			
			<span>Show as HTML text<input type=checkbox
				name="show_html" <?php echo $html_ch; ?> /></span>
				
			<span>Show as JSON text<input type=checkbox
				name="show_json" <?php echo $json_ch; ?> /></span>
				
			<span>Show list<input type=checkbox
				name="show_list" <?php echo $list_ch; ?> /></span>
				
			<span>Save as file<input type=checkbox
				name="save_download" <?php echo $download_ch; ?> /></span>
				
  <span>Assign users<input type=checkbox
				name="assign_users" <?php echo $assign_ch; ?> /></span>
		</div>

		<div>
			<input type="submit" name="list_messages" value="Show thread" />
		</div>
	</form>
	
</body>
</html>
