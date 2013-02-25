<?php

function connectDB(){
	$server="localhost";
	$user="root";
	$password="L0ckTh1$@8";
	$database="good_tt";

	$db = new mysqli($server,$user,$password,$database);
	if ($db->connect_errno) {
    	printf("Connect failed: %s\n", $mysqli->connect_error);
    	exit();
	}

	return $db;
}

function getStats($db)
{
	$q = 'SELECT COUNT(*) FROM txts';
	$r=mysqli_query($db,$q) or die(mysqli_error($db)." Q=".$q);
	$r = mysqli_fetch_assoc($r);
	$count["msgs"] = $r['COUNT(*)'];
	
	$q = 'SELECT COUNT(*) FROM tags';
	$r=mysqli_query($db,$q) or die(mysqli_error($db)." Q=".$q);
	$r = mysqli_fetch_assoc($r);
	$count["tags"] = $r['COUNT(*)'];
	
	return $count;
}

function printForm($action){
// Actions include: text, view, claim, remind, leave, contact
?>
	<form action="<?php echo "./includes/process.php"; ?>" method="post">
	<input type="hidden" name="action" value="<? echo $action ?>" />
	<input type="hidden" name="page_instance_id" value="<?php echo end($_SESSION['page_instance_ids']) ?>" />
	<span class="confirmation-field"><input type="text" name="robot" value="" /></span>
	
	<? if ($action != "remind" && $action != "contact") {?>
	<p><select id="state" name="state" size="1">
	<option value="AL">Alabama</option>
	<option value="AK">Alaska</option>
	<option value="AZ">Arizona</option>
	<option value="AR">Arkansas</option>
	<option value="CA">California</option>
	<option value="CO">Colorado</option>
	<option value="CT">Connecticut</option>
	<option value="DE">Delaware</option>
	<option value="FL" selected>Florida</option>
	<option value="GA">Georgia</option>
	<option value="HI">Hawaii</option>
	<option value="ID">Idaho</option>
	<option value="IL">Illinois</option>
	<option value="IN">Indiana</option>
	<option value="IA">Iowa</option>
	<option value="KS">Kansas</option>
	<option value="KY">Kentucky</option>
	<option value="LA">Louisiana</option>
	<option value="ME">Maine</option>
	<option value="MD">Maryland</option>
	<option value="MA">Massachusetts</option>
	<option value="MI">Michigan</option>
	<option value="MN">Minnesota</option>
	<option value="MS">Mississippi</option>
	<option value="MO">Missouri</option>
	<option value="MT">Montana</option>
	<option value="NE">Nebraska</option>
	<option value="NV">Nevada</option>
	<option value="NH">New Hampshire</option>
	<option value="NJ">New Jersey</option>
	<option value="NM">New Mexico</option>
	<option value="NY">New York</option>
	<option value="NC">North Carolina</option>
	<option value="ND">North Dakota</option>
	<option value="OH">Ohio</option>
	<option value="OK">Oklahoma</option>
	<option value="OR">Oregon</option>
	<option value="PA">Pennsylvania</option>
	<option value="RI">Rhode Island</option>
	<option value="SC">South Carolina</option>
	<option value="SD">South Dakota</option>
	<option value="TN">Tennessee</option>
	<option value="TX">Texas</option>
	<option value="UT">Utah</option>
	<option value="VT">Vermont</option>
	<option value="VA">Virginia</option>
	<option value="WA">Washington</option>
	<option value="WV">West Virginia</option>
	<option value="WI">Wisconsin</option>
	<option value="WY">Wyoming</option></select></p>

	<p><input type="text" size="10" name="plate" id="plate" style="text-align:center;" placeholder="ADGT09" /></p>
	<? } ?>
	<? if ($action == "text"){ ?>
	<p><input size="50" name="text" style="text-align:center;" placeholder="I really dig your car!"></p>
	<? } ?>
	
	<? if ($action == "contact") { ?>
	<p><input type='text' name='Name' size=20 style="text-align:center;" placeholder="John Smith" /></p>
	<? } ?>
	
	<? if ($action == "claim" || $action == "leave" || $action == "remind" || $action == "contact"){ ?>
		<p><input type="text" name="email" id="mail" size="30" style="text-align:center;"  placeholder="example@gmail.com"></p>
	<? } ?>
	
	<? if ($action == "contact") { ?>
	<p><textarea name='text' rows=5 cols=40 placeholder="I just wanted to say..." /></textarea></p>
	<? } ?>
	
	<? if ($action == "claim"){ ?>
		<p><input type="checkbox" name="custom" id="custom" value="false">
		<label for="custom"> Only receive TXT TAG's preset messages</label><br><i>Not yet functional.</i></p>
		<p><input type="checkbox" name="public" id="public" value="true" checked>
		<label for="public"> Continue to list my messages publicly</label></p>
		<p><input type="checkbox" name="share" id="share" value="true" checked>
		<label for="share"> Share my messages with similar tags.<br><b><i>Highly Recommended.</i></b></label></p>
	<? } ?>
	
	<? if ($action == "claim" || $action == "leave" || $action == "contact"){ 
		$first = rand(1,5);
		$second = rand(1,10-$first);
		$answer = $first + $second;
		?>
		<p><? echo $first .' + '.$second.' = ' ?>
		<input type=text name='Guess' size=2 placeholder="?" style="text-align:center;" >
		<input type=hidden name='Answer' value=<? echo $answer; ?>></p>
	<? } ?>
	
	<p><input type="submit" value="Submit"></p>
	</form>
	
	<script type="text/javascript" > 
	<? if ($action != "remind") { ?>
	document.getElementById('state').value = geoplugin_region();
	retrieve_zip("example_callback"); // Alert the User's Zipcode
	document.getElementById('plate').focus();
	<? } else { ?>
	document.getElementById('mail').focus();
	<? } ?>
	</script>
<?php
return;
}

function effective_tag($plate) {
	// http://www.deadlyroads.com/licenseplates.shtml
	// Untouched: C, E, F, R, Y
	// Remaining: Untouched, 0, 1, 2, 4, 5, 8, H, V
	
	$plate = str_replace('O', '0', $plate);
	$plate = str_replace('Q', '0', $plate);
	$plate = str_replace('G', '0', $plate);
	$plate = str_replace('D', '0', $plate);
	
	$plate = str_replace('I', '1', $plate);
	$plate = str_replace('L', '1', $plate);
	$plate = str_replace('T', '1', $plate);
	$plate = str_replace('J', '1', $plate);
	
	$plate = str_replace('Z', '2', $plate);
	$plate = str_replace('7', '2', $plate); // Because 7 -> Z -> 2
	
	$plate = str_replace('A', '4', $plate);
	
	$plate = str_replace('6', '5', $plate);
	$plate = str_replace('S', '5', $plate);
	
	$plate = str_replace('B', '8', $plate);
	$plate = str_replace('9', '8', $plate);
	$plate = str_replace('P', '8', $plate);
	$plate = str_replace('R', '8', $plate);
	
	$plate = str_replace('M', 'H', $plate);
	$plate = str_replace('N', 'H', $plate);
	$plate = str_replace('W', 'H', $plate);
	
	$plate = str_replace('U', 'V', $plate);
	$plate = str_replace('X', 'V', $plate);
	$plate = str_replace('K', 'V', $plate); // Because K -> X -> V
	
	return $plate;
}

function txtTag($db,$email,$state,$plate,$text)
{
	$plat3 = prettyPlate($plate);
	// Format the message to prevent an SQL or HTML attack...
	$t3xt = $db->real_escape_string($text);
	
	$subject = "Message for $state $plat3";
	sendMsg($email, $subject, "<h2>$text</h2>");
	
	$q="INSERT INTO log (state, tag, msg, email) VALUES ('$state','$plate','$t3xt','$email')";
	$r=mysqli_query($db,$q) or die(mysqli_error($db)." Q=".$q);
			
	$q="UPDATE tags SET numMsg = numMsg + 1 WHERE state='$state' AND tag='$plate'";
	$r=mysqli_query($db,$q) or die(mysqli_error($db)." Q=".$q);
	
	return;
}

function txtEffectiveTag($db,$email,$state,$plate_original,$plate_similar,$text)
{
	$plat3 = prettyPlate($plate_similar);
	// Format the message to prevent an SQL or HTML attack...
	$t3xt = $db->real_escape_string($text);
	
	$subject = "Message (kinda) for $state $plat3";
	sendMsg($email, $subject, "<p>You're receiving this message because your tag ($plate_similar) is kinda sorta similar to $plate_original.</p><h2>$text</h2>");
	
	$q="INSERT INTO log (state, tag, msg, email) VALUES ('$state','$plate_similar','$t3xt','$email')";
	$r=mysqli_query($db,$q) or die(mysqli_error($db)." Q=".$q);
			
	$q="UPDATE tags SET numMsg = numMsg + 1 WHERE state='$state' AND tag='$plate_similar'";
	$r=mysqli_query($db,$q) or die(mysqli_error($db)." Q=".$q);
	
	return;
}

function sendMsg($email, $subject, $message){
	$message = "<html><body><center><p><a href=\"http://www.txttag.me\" alt=\"TXT TAG\"><img src=\"http://www.txttag.me/images/txttag.png\"></a></p><hr><p>$message</p><hr><p>http://www.TXTTAG.me</p><p><small><a href='http://txttag.me/leave.php'>You can unsubscribe at any time.</a></small></p></center></body></html>";

	$headers  = 'MIME-Version: 1.0' . "\r\n";
	$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
	$headers .= 'From: "TXT TAG" <info@txttag.me>' . "\r\n";
	if(!mail($email,$subject,$message,$headers,'-finfo@txttag.me')) { 
		echo "ERROR! The message did not send properly, please alert info@txttag.us about this error! Our apologies.";
	}
}

function justClaimed($db,$email,$state,$plate,$r)
{
	$plat3 = prettyPlate($plate);
	$num = $r->num_rows;
	
	ob_start();
	printMsgs($r);
	$msg = ob_get_clean();
	
	$subject = "Old messages for $state $plat3";
	sendMsg($email, $subject, $msg, "allow");
	
	$q="UPDATE tags SET numMsg = numMsg + $num WHERE state='$state' AND tag='$plate'";
	$r=mysqli_query($db,$q) or die(mysqli_error($db)." Q=".$q);
	
	$q="INSERT INTO log (state, tag, msg, email) VALUES ('$state','$plate','TXT TAG: Sent all existing messages.','$email')";
	$r=mysqli_query($db,$q) or die(mysqli_error($db)." Q=".$q);
	return;
}

function fbLikeBox() { ?>
	<div class="fb-like-box" data-href="http://www.facebook.com/txttag" data-width="500" data-show-faces="true" data-stream="true" data-header="false"></div>
<? }

function fbFacePile() { ?>
	<div class="fb-like" data-href="http://www.facebook.com/txttag" data-send="true" data-width="465" data-show-faces="true" data-font="segoe ui"></div>
<? }

function validEmail( $email ){
    return filter_var( $email, FILTER_VALIDATE_EMAIL );
}

function prettyPlate($plate)
{
	switch(strlen($plate))
	{
	case 6:
		$plat3 = substr($plate, 0, 3) . " " . substr($plate, 3); 
		break;
	case 8:
		$plat3 = substr($plate, 0, 4) . " " . substr($plate, 4); 
		break;
	default:
		$plat3 = $plate;
	}
	return $plat3;
}

function printMsgs($r) {
	while($row = $r->fetch_array())
		{ $rows[] = $row; }
		
	foreach($rows as $row)
	{
		$date = date('l, F j, Y @ g:i A', strtotime($row['date']) );
		echo "<p><b><i>" . $date . "</i></b><br>" . $row['msg'] . "</p>";
	}
	return;
}

function xmlIt($xml) {  
  
  // add marker linefeeds to aid the pretty-tokeniser (adds a linefeed between all tag-end boundaries)
  $xml = preg_replace('/(>)(<)(\/*)/', "$1\n$2$3", $xml);
  
  // now indent the tags
  $token      = strtok($xml, "\n");
  $result     = ''; // holds formatted version as it is built
  $pad        = 0; // initial indent
  $matches    = array(); // returns from preg_matches()
  
  // scan each line and adjust indent based on opening/closing tags
  while ($token !== false) : 
  
    // test for the various tag states
    
    // 1. open and closing tags on same line - no change
    if (preg_match('/.+<\/\w[^>]*>$/', $token, $matches)) : 
      $indent=0;
    // 2. closing tag - outdent now
    elseif (preg_match('/^<\/\w/', $token, $matches)) :
      $pad--;
    // 3. opening tag - don't pad this one, only subsequent tags
    elseif (preg_match('/^<\w[^>]*[^\/]>.*$/', $token, $matches)) :
      $indent=1;
    // 4. no indentation needed
    else :
      $indent = 0; 
    endif;
    
    // pad the line with the required number of leading spaces
    $line    = str_pad($token, strlen($token)+$pad, ' ', STR_PAD_LEFT);
    $result .= $line . "\n"; // add to the cumulative result, with linefeed
    $token   = strtok("\n"); // get the next token
    $pad    += $indent; // update the pad size for subsequent lines    
  endwhile; 
  
  return $result;
}


?>