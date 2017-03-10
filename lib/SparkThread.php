<?php 
/*
 * Get and format a thread of messsage from a Cisco Spark Room
*
* Usage:
*   edit config_inc.php
*
* require_once 'SparkThread.php';
*
* $th = new SparkThread($spark_token);
*
* $th->setRoom(  $room_id, $room_name);
*
* $th->setDateRange( $begin_date, $end_date);
*
* $th->setDaysRange(  $begin_days, $end_days);
*
* $th->setDateRangeFind(  $match_text,  $num_days);
*
* $th->getMessages();
*
* ## display
* 
* $text = $th->getTextBlock();
*
* $thread_array = $th->getThread(  <FORMAT> );
*
*$text = $th->asText( <format> );
*
*  where <format> = [ html | wiki | plain ]
*  
*
*
*
*/

require_once 'SparkClient.php';

class SparkThread extends SparkClient {

	private $room_name;
	private $room_id;

	private $user_name;

	public $members = array();

	public	$begin_date, $end_date;  // createDate for first, last messages, in ISO 8601 format
	public $begin_ymd, $end_ymd; // simplify to Y-m-D format
	public $begin_days, $end_days;  // number of days before today for first, last messages
	protected $begin_message_id, $end_message_id; //  message ID for first, last messages

	#private $messages = array();

	public $subject = '';
	public $heading  = '';
	private $text_block;
	private $formatted_thread = array();

	private $message_order; // check

	public $message_display_attribute = '';
	public $message_separator_attribute = '';

	// documentation
	private $message_elements = array('id', 'roomId', 'roomType', 'toPersonId', 'text', 'markdown', 'files', 'personId', 'personEmail', 'created', 'mentionedPeople');

	public $max_messages;

	public function getRoomName(){
		return $this->room_name;
	}
	public function setRoom($room_id, $room_name = null) {
		// validate

		$this->room_id = $room_id;
		if ( $room_name == null){
			$r = $this->getRoom($this->room_id);
			if ( $r ){
				$room_name = $r->title;
			}
			else {
				$room_name = 'NO SUCH ROOM';
			}
		}
		$this->room_name = $room_name;
		return $this->room_name;

	}

	public function setDateRange($begin_date, $end_date) {
		// validate

		$this->begin_date = $begin_date;
		$this->end_date = $end_date;

		$date0 = new DateTime();
		// echo "now: " . $date0->format('Y-m-d') . "\n";

		#return;

		if ($begin_date != null ){

			$date = new DateTime($begin_date);
			$this->begin_ymd = $date->format('Y-m-d');

			###echo " got begin: " . $date->format('Y-m-d h-i-s') . "\n";

			if ( $date == null ) {
				$this->error = "invalid begin date $begin_date";
				return;
			}
			$diff = $date0->diff($date);
			#print_r($diff);
			$this->begin_days = $diff->days;
		}


		$date = new DateTime($end_date);
		$this->end_ymd = $date->format('Y-m-d');
		$diff = $date0->diff($date);
		$this->end_days = $diff->days;

	}

	public function setDaysRange( $n1, $n2){

		if ( $n1 == null ) {
			$this->begin_date = null;
			$this->begin_days = null;
		}
		else {

			$n1 = intval($n1);

			/*

			$date = new DateTime();
			###$n1 = 2;
			$int1 = new DateInterval( 'P' . $n1 . 'D');
			$date->sub( $int1);
			//
		 * ISO8601 format
		 *
		 * format: yyyy-MM-dd'T'HH:mm:ss.SSSXXX",
		 *
		 * example: 2016-11-29T16:39:22.899Z
		 * ISO 8601 Time zone
		 *   .899Z == (sign)  89 hours 9 minutes
		 *
		 * http://stackoverflow.com/questions/28041913/what-does-sssxxx-mean-in-a-java-simple-date-format-object
		 *
		 * http://docs.oracle.com/javase/7/docs/api/java/text/SimpleDateFormat.html
		 *
		 */
			$this->begin_date = $this->daysAgoToDate8601($n1);
			$this->begin_days = $n1;

			if ( $n2 >  $n1 ){
				$this->error = " Begin days must be greater than end days,  $n1 < $n2 ";
				return;
			}

		} # begin

		/*
		 *
		 if (! is_int($n2) || $n2 < 0){
			$this->error = "end days is not positive integer: $n2 ";
			return;
			}


			$date = new DateTime();
			//echo $date1->format('Y-m-d') . "\n";

			//echo $date1->format('Y-m-d') . "\n";

			#$n2 = 10;
			$int2 = new DateInterval( 'P' . $n2 . 'D');
			$date->sub( $int2);
			//echo $date2->format('Y-m-d') . "\n";
			*/
		$n2 = intval($n2);
		$this->end_date = $this->daysAgoToDate8601($n2);
		$this->end_days = $n2;
	}

	private function dateToDaysAgo( $date_string ){

		$date0 = new DateTime();

		$date1 = new DateTime($date_string);
		$diff = $date0->diff($date1);
		return $diff->days;

	}
	public function dateToYMD( $date_string ){
		$date = new DateTime( $date_string );
		return $date->format('Y-m-d') ;
	}
	private function daysAgoToDate8601( $n ){

		if (! is_int($n) || $n < 0){
			$this->error = "'$n' days is not positive integer";
			return;
		}


		$date = new DateTime();
		$interval = new DateInterval( 'P' . $n . 'D');
		$date->sub( $interval);
		//

		/*
		 * ISO8601 format
		*
		* format: yyyy-MM-dd'T'HH:mm:ss.SSSXXX",
		*
		* example: 2016-11-29T16:39:22.899Z
		* ISO 8601 Time zone
		*   .899Z == (sign)  89 hours 9 minutes
		*
		* http://stackoverflow.com/questions/28041913/what-does-sssxxx-mean-in-a-java-simple-date-format-object
		*
		* http://docs.oracle.com/javase/7/docs/api/java/text/SimpleDateFormat.html
		*
		*/
		return $date->format('Y-m-d\Th:i:s')  . '.000Z';

	}
	function getMessageRange() {
		return array( $this->begin_message_id, $this->end_message_id);
	}
	function setEndMessage( $end_message_id) {
		$this->end_message_id = $end_message_id;
	}
	function clearFilter() {
		$this->begin_date = null;
		$this->end_date = null;

		$this->max_messages = null;
		$this->begin_message_id = null;
		$this->end_message_id = null;
	}
	public function getThread() {

		$args = array();
		$args['roomId'] = $this->room_id;
		if ( isset($this->max_messages)){
			$args['max'] = $this->max_messages;
		}
		if ( isset($this->end_message_id)){
			$args['beforeMessage'] = $this->end_message_id;
		}
		if ( isset($this->end_date)){
			$args['before'] = $this->end_date;
		}
		$this->getMessages( $args );

		// filter


	}
	public function asJSON( ){
		$obj = array( 'items' => $this->messages);
			$text = json_encode($obj);
			return $text;
		return $this->response;
	}
	public function asText( $format = 'html') {
		
		if ( isset($this->text_block)){
			return $this->text_block;
		}

		$t = '';
		###$max_items = 2;
		##$t .= " format=$format ";

		if ( $format == 'wiki'){
			if ( $this->heading){

				$t .= '== ' . $this->heading . ' == ' . "\n";
			}

		}
		$n = 0;
		// messages are in reverse order (recent to oldest);
		// display in chronological
		foreach ( array_reverse($this->messages) as $message ){
			/*
			 *
			if ( isset($this->max_messages) && $n >= $this->max_messages ){
			dprint( "$n of " . $this->num_messages . " messages");
			break;
			}
			*/
			$n++;

			if ( $format == 'wiki'){
				$t .= $this->formatMessageWiki($message,$format);
			}
			elseif ( $format == 'json'){
				$t .= json_encode($message);
			}
			elseif ( $format == 'list'){
				$t .= implode("\t", array($message->id, $message->created, "\n"));
			}
			else {

				$t .= $this->formatMessage($message, $format);
			}
		}

		if ( $format == 'wiki'){
			$t .= "=== about === \n";
			$t .= "This text is from a conversation in the Spark Rom ''" . $this->room_name . "'',";
			$t .= " from " . $this->begin_ymd . " to " . $this->end_ymd . ". \n\n";
			$t .= "Contributors: ";
			$i = 0;
			foreach ( array_keys($this->members) as $id ) {
				$i++;
				if ( $i > 1){
					$t .= ', ';
				}

				#				$t .= $this->getMemberName( $id, 'wiki');
				$t .= '<span class=spark_user_' . $this->getMemberName($id, 'label') . '>' .$this->getMemberName( $id, 'wiki') . '</span>';
			}
			$t .= ". \n\n";
		}

		// $this->text_block = $t;
		return $t;
	}

	function formatMessageWiki($message, $format = 'wiki'){

		$m = '';
		$m .= '<div class=spark_user_' .  $this->getMemberName( $message->{'personId'}, 'label') . '>';

		#$m .= "* ''" .  $message->{'personEmail'} . "'' \n";
		#$m .= "  " .  $message->{'text'};
		#		$m .= $this->getMemberName( $message->{'personId'}, 'wiki') . ' ';
		$m .= $this->getMemberName( $message->{'personId'}, 'key') . ' ';

		$m .= $message->{'text'};
		$m .= '</div>';
		$m .= "\n\n";

		return $m;
	}
	function formatMessage($message, $format = 'html'){

		$days = $this->dateToDaysAgo(  $message->{'created'} );
		$m = '';

		#$m .= '<div class=message_separator ' . $this->message_separator_attribute . ' message_id="' . $message->{'id'} . '" >&nbsp;...</div>';

		$m .= '<div class=message ' . $this->message_display_attribute . ' >';

		if ( $format == 'brief'){

			$m .=
			'<div class=message_date>' . $this->dateToYMD($message->{'created'}) . '</div>';
			#$m .= '<div class=message_days>' . $days . '</div>';
			$m .= '<div class=message_user>(' . $message->{'personEmail'} . ')</div>';
			$m .=
			'<div class=message_text>' . $message->{'text'} . '</div>';
		}
		else {
			// html
			$m .=
			'<div class=message_date>' . $message->{'created'} . '</div>';
			$m .= '<div class=message_days>' . $days . '</div>';
			$m .= '<div class=message_user>' . $message->{'personEmail'} . '</div>';
			$m .=
			'<div class=message_text>' . $message->{'text'} . '</div>';
			#$m .= '<div class=message_id>' . $message->{'id'} . '</div>';
		}

		$m .= '</div>';

		return $m;
	}

	function reviewThread( $filter_string = null){

		$u = array();
		$begin_date ='';
		$end_date = '';

		$n = 0;
		$last_date  = '';
		$this->message_order = 1;

		// assume messages are in reverse order:  newest -> oldest
		$ilast = count($this->messages) - 1;
		if ( isset($this->max_messages) ){
			$liast = $this->max_messages -1;
		}
		$this->begin_message_id = $this->messages[$ilast]->{'id'};
		$this->end_message_id = $this->messages[0]->{'id'};

		$begin_date = $this->messages[$ilast]->{'created'};
		$end_date = $this->messages[0]->{'created'};

		//(" 0 .. $ilast begin=" . $begin_date . " end=$end_date");

		foreach ( $this->messages as $message ){
			if ( isset($filter_string)){
				$match = '/' . $filter_string . '/i';
				if ( ! preg_match( $match, $message->{'text'} )){
					//$message->{'text'} .= ' XXX';
					array_splice($this->messages, $n, 1);
					continue;
				}
				
				
			}
			
			/*
			 if ($n >  $ilast){
			break;
			}
			*/
			$n++;


			// $t .= $this->formatMessage($message);

			//			$u[ $message->{'personId'} ] = $message->{'personEmail'};
			if ( ! isset($u[ $message->{'personId'} ] )) {
				$u[ $message->{'personId'} ] = array();
				$u[ $message->{'personId'} ]['email'] = $message->{'personEmail'};
				$u[ $message->{'personId'} ]['name'] = $message->{'personEmail'};
				$u[ $message->{'personId'} ]['user_id'] = $message->{'personEmail'};
				$u[ $message->{'personId'} ]['wiki_name'] = '';
				#$u[ $message->{'personId'} ]['label'] = $message->{'personEmail'};
				$u[ $message->{'personId'} ]['label'] = $n;
			}

			// are we guaranteed that messages are returned in order?

			$date = $message->{'created'};

			// check: are messages in reverse order ? (this date should be earlier than the previous one
			if ( $last_date && $date > $last_date) {
				$message_order = 0;
			}


		}
		// reset if messages were filtered
		$this->num_messages = count($this->messages);

		// assume messages are in reverse order:  newest -> oldest
		$ilast = count($this->messages) - 1;
		if ( isset($this->max_messages) ){
			$liast = $this->max_messages -1;
		}
		$this->begin_message_id = $this->messages[$ilast]->{'id'};
		$this->end_message_id = $this->messages[0]->{'id'};
		
		$begin_date = $this->messages[$ilast]->{'created'};
		$end_date = $this->messages[0]->{'created'};
		
		$this->members = $u;
		$this->setDateRange($begin_date, $end_date);
	}

	public function getMemberList( $format = 'fulll'){
		$list = '';

		foreach ( array_keys($this->members) as $spark_user_id) {
			$list .= $this->getMemberName( $spark_user_id, $format);
		}
		return $list;
	}
	public function getMemberName( $spark_user_id, $format ='full'){
		if ( ! isset( $this->members[$spark_user_id])){
			// ERROR!

			return;
		}

		$m = '';
		$user = $this->members[$spark_user_id];

		if ( $format == 'label'){
			#			return  '(' . $user['label'] . ') ';
			return $user['label'];
		}
		if ( $format == 'key'){
			return  '(' . $user['label'] . ') ';

		}
		if ($format == 'wiki'){
			$m .= '(' . $user['label'] . ') ';

			if ( $user['wiki_name']){

				$m .= '[[User:' . $user['wiki_name'] . '|' . $user['name'] . ']]';
			}
			else{
				$m .= $user['name'];
			}
			return $m;
		}

		$m .= '(' . $user['label'] . ') ' . $user['name'];
		if ($user['name'] != $user['email']){
			$m .=  '/' . $user['email'];
		}
		return $m;


	}
	public function messageOrderOK() {
		return $this->message_order;
	}
}
?>