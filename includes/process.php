<?php 

// To simplify this page, functions have been put into a separate file
include_once($_SERVER['DOCUMENT_ROOT'].'/includes/functions.php');

// Actions include: text, view, claim, remind, leave, contact
// Catch all of the inputs from the form, and assign convenient variable names
$action = $_POST["action"];
$state = $_POST["state"];
$plate = $_POST["plate"];
$email = $_POST["email"];
$text = $_POST["text"];
$robot = $_POST["robot"];

// The contact and claim forms have extra inputs, we'll grab those now.
if($action == "contact" || $action == "claim") {
	$Name = $_POST["Name"];
	$Guess = $_POST["Guess"];
	$Answer = $_POST["Answer"];
	
	$public = $_POST["public"];
	$custom = $_POST["custom"];
	$share = $_POST["share"];
	
	// Strip HTML tags on the comments so that our email doesn't execute foreign code
	$Comments = strip_tags($text);
}

// Get the tag all regular-like
$plate = preg_replace('/[^A-Za-z0-9-]/', '', $plate);
$plate = strtoupper($plate);

// Replace commonly-mistaken characters
$p1ate = effective_tag($plate);

// Form a nicely-spaced version of the plate. (ADGT09 => ADG T09)
$plat3 = prettyPlate($plate);

// Add a line to the header so that browsers can automatically detect our RSS feed
if($action == "view") {
	$head = '<link rel="alternate" type="application/rss+xml" title="RSS" href="http://www.txttag.me/rss.php?state=' . $state . '&plate=' . $plate . '" />';
}

// Load the header to give the page its form
include('./header.php');

// Format the message to prevent SQL & HTML injection...
$text = strip_tags($text);
$t3xt = $db->real_escape_string($text);

// These lines allows a form session to be submitted only once
$page_id_index = array_search($_POST['page_instance_id'], $_SESSION['page_instance_ids']); 
if ($page_id_index !== false) { 
    unset($_SESSION['page_instance_ids'][$page_id_index]);

// A "honeypot" technique is employed to ensure no robots are filling out the form.
// This was a hidden input, if *anything* exists in it, it was from a robot. Trash it.
if($robot != "") {
	// Don't make it obvious that we've detected the robot.
	echo "Thanks for your request.";
	
// Handle opt-out requests
} else if($action == "leave"){
	echo "<h2>We already miss you!</h2>";
	
	// We use an additional captcha technique for sensitive operations
	if($Guess == $Answer) {
		if(validEmail($email)){
		
			// If a plate is entered, delete just a single plate
			if(strlen($plate)>0){
				$q="DELETE FROM tags WHERE state='$state' AND tag='$plate' AND email='$email'";
				$r=mysqli_query($db,$q) or die(mysqli_error($db)." Q=".$q);
				
				$q="INSERT INTO log (state, tag, msg, email) VALUES ('$state','$plate','TXT TAG: Plate now unclaimed.','$email')";
				$r=mysqli_query($db,$q) or die(mysqli_error($db)." Q=".$q);
				
				echo "<p><b>$state &mdash; $plat3</b><br>$email</p>";
				echo "<p>It's done.</p>";
				
			// If no plate is entered, delete all tags associated with the email
			} else {
				$q="DELETE FROM tags WHERE email='$email'";
				$r=mysqli_query($db,$q) or die(mysqli_error($db)." Q=".$q);
				
				$q="INSERT INTO log (state, tag, msg, email) VALUES ('00','null','TXT TAG: User removed entire email.','$email')";
				$r=mysqli_query($db,$q) or die(mysqli_error($db)." Q=".$q);
				
				echo "<p><b>$email</b></p>";
				echo "<p>It's done.</p>";
			}
			
		// End up here if the email was not of proper form
		} else { echo "That email smells fishy."; }
		
	// End up here if the math operation didn't add up
	} else { echo "Either you're a robot, or you can't add."; }

// Send a reminder email
} else if($action == "remind"){
	if(validEmail($email)){
			$subject="Hey, claim your tag!";
			$message="</b>A great tip is to take a picture of your tag the next time you walk to your car.<br>This is also useful if somebody mentions your tag over the intercom!</p><p><a href=\"http://www.txttag.me/claim.php\"><b>When you're ready, claim your tag here.";
			sendMsg($email, $subject, $message);
			echo "<h2>Consider it done.</h2>";
			echo "<p>We've just sent you a message, don't delete it 'till you've claimed your tag.</p>";
			
		// End up here if the email was not of proper form
		} else { echo "<h2>I can't remind you there...</h2><p>That email smells fishy.</p>"; }

// Process the contact form
} else if($action == "contact") {
	echo "<h2>Drop us a line!</h2>";
	if(validEmail($email)){		
		// If not a bot, process the mailing
		if(strlen($Name)!=0 && htmlspecialchars($Name) == $Name && $Guess == $Answer){
			$message = '<p>Name: '.$Name.'</p><p>Email: '.$email.'</p><p>Comments:<br>'.$Comments.'</p>';
			$message = "<html><body><p><center><a href=\"http://www.txttag.me\" alt=\"TXT TAG\"><img src=\"http://www.txttag.me/images/txttag.png\"></a></center></p><hr>$message<hr><p><center>http://www.TXTTAG.me</center></p></body></html>";

			$to = '"GOOD Inc." <sales@buygood.us>';
			$subject = 'Message from TXTTAG.me!';
			
			$headers  = 'MIME-Version: 1.0' . "\r\n";
			$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
			$headers .= 'From: "'. $Name . '" <' . $email . '>' . "\r\n";
			if(!mail($to,'Message from TXTTAG.me!',$message,$headers)) { 
				echo "ERROR! The message did not send properly, please alert info@txttag.us about this error! Our apologies.";
			} else { echo "Hey, thanks for your message! We'll get back to you ASAP."; }
		} else { // No name, probably a bot, throw it away...
		echo "We think you're a robot... go back and check everything. If all else fails, send us a message at sales@buygood.us using your favorite email client.";
		}
	} else { echo "That email smells fishy."; }
	
// Done with contact form, all remaining actions require a plate.
// Check to make sure plate exists, and it's too long
} else if(strlen($plate)>0){
	if(strlen($plate)>8) {
		echo "You know license plates aren't that long.";

// Claim tags to emails
}else if($action == "claim"){
	echo "<h2>Claim your tag</h2>";
	
	if(validEmail($email)){
		// Verify he's not a robot
		if($Guess == $Answer) {
		
			// Format HTML checkbox input into SQL-ready data		
			if ($custom == 'false') { $custom = 0; } else { $custom = 1; }
			if ($public == 'true' ) { $public = 1 ; } else { $public = 0; }
			if ($share == 'true' ) { $share = 1 ; } else { $share = 0; }
		
			// Check if plate already exists
			$q="SELECT * FROM tags WHERE state='$state' AND tag='$plate'";
			$r=mysqli_query($db,$q) or die(mysqli_error($db)." Q=".$q);
			
			// If the plate isn't already registered...
			if($r->num_rows<1){
			
				// Insert the plate into the databse
				$q="INSERT INTO tags (state, tag, email, custom, public, share, tag_effective) VALUES ('$state','$plate','$email','$custom','$public','$share','$p1ate')";
				$r=mysqli_query($db,$q) or die(mysqli_error($db)." Q=".$q);
				
				$message = "TXT TAG: Just signed up original ($plate) with custom, public: $custom, $public";
						
				// Log that you've done so
				$q="INSERT INTO log (state, tag, msg, email) VALUES ('$state','$plate','$message','$email')";
				$r=mysqli_query($db,$q) or die(mysqli_error($db)." Q=".$q);
				
				// Check if there are messages waiting
				$q="SELECT date, msg FROM txts WHERE state='$state' AND tag='$plate'";
				$r=mysqli_query($db,$q) or die(mysqli_error($db)." Q=".$q);
				
				echo "<p><b>$state &mdash; $plate</b><br>$email</p>";
				
				// If there are messages, deliver them...
				if($r->num_rows>0){
					echo "<p>You're in, and you've got mail!</p>";
					justClaimed($db,$email,$state,$plate,$r);
				} else { 
					echo "<p>Consider it done.</p>";
				}
				
				echo "<p><i>Double check your plate and email above. If you've made a typo, <a href=\"../contact.php\">contact us</a>.</i></p>";
				echo "<p>Messages may have been intended for you, but sent to similar tags, <a href=\"../view.php\">check here</a>.</p>";
			} else {
				echo "<p><b>$state &mdash; $plat3</b></p>";
				echo "Sorry, that tag has already been claimed!<br><a href=\"../contact.php\">Contact us</a> to work this out.";}
			
			// End up here if the math operation didn't add up
			} else { echo "Either you're a robot, or you can't add."; }
		
		// End up here if the email was not of proper form
		} else { echo "That email smells fishy."; }
		
// Print list of messages publicly
}else if($action == "view"){
		echo "<h2>View Messages</h2>";
		echo "<p><b>$state &mdash; $plat3</b></p>";
		
		// Check the privacy setting on this tag
		$q="SELECT public FROM tags WHERE state='$state' AND tag='$plate'";
		$r=mysqli_query($db,$q) or die(mysqli_error($db)." Q=".$q);
		
		// Massage the desired value out of the result
		$r1 = $r->fetch_assoc();
		$public = $r1['public'];
		
		// If we're permitted to, begin to print the messages
		if ($public || $r->num_rows == 0){		
			
			// Pull all related messages from the database
			$q="SELECT * FROM txts WHERE state='$state' AND tag='$plate' ORDER BY `id` DESC";
			$r=mysqli_query($db,$q) or die(mysqli_error($db)." Q=".$q);
			
			// If a message exists, print it
			if($r->num_rows>0){
			
				// Offer our RSS feed as a link, then print the messages
				echo '<a href="http://www.txttag.me/rss.php?state=' . $state . '&plate=' . $plate . '"><img src="' . $root . '/images/rss.png"> Subscribe to an RSS feed</a>';
				printMsgs($r);
				
			// End up here if there are no messages for this tag
			} else { echo "Nobody has sent you anything yet."; }

		// This tag has specifically be set to not show the messages publicly
		} else  { echo "<p>The person who claimed this tag chose to keep these messages private.<br>Feel free to contact us if you have any concerns.</p>";}
		
		// Handle effective tags
		// Find all tags that are effectively the same
		$q="SELECT * FROM txts WHERE state='$state' AND tag_effective='$p1ate' AND tag != '$plate' ORDER BY `id` DESC";
		$r=mysqli_query($db,$q) or die(mysqli_error($db)." Q=".$q);
		
		if($r->num_rows>0){
			
			while($row = $r->fetch_array())
			{ $rows[] = $row; }
			
			foreach($rows as $row)
			{
				$plate_similar = $row['tag'];
				$msg = $row['msg'];
				
				// Check the privacy setting on this tag
				$q="SELECT share FROM tags WHERE state='$state' AND tag='$plate_similar'";
				$r=mysqli_query($db,$q) or die(mysqli_error($db)." Q=".$q);
				
				// Massage the desired value out of the result
				$r1 = $r->fetch_assoc();
				$share = $r1['share'];

				if($r->num_rows<1 || $share == '1') {
					$date = date('l, F j, Y @ g:i A', strtotime($row['date']) );
					$similar_messages .= "<p><b><i>" . $date . "</b><br>Sent to $plate_similar</i><br>$msg</p>";
				}
			}
		}
		
		if($similar_messages){
			echo "<p><b>Messages possibly meant for this tag:</b></p>" . $similar_messages;
		}			
		

// Send out messages
} else if($action == "text") {
	echo "<h2>Send messages to license plates.</h2>";
	
	// Verify the user actually typed a message
	if(strlen($text)<1) {
		echo "Why would you send anybody an empty message?";
	
	} else {
		// Save the message in our database
		$q="INSERT INTO txts (state, tag, msg, tag_effective) VALUES ('$state','$plate','$t3xt','$p1ate')";
		$r=mysqli_query($db,$q) or die(mysqli_error($db)." Q=".$q);
		
		// Find out if there's an email stored for this tag
		$q="SELECT * FROM tags WHERE state='$state' AND tag='$plate'";
		$r=mysqli_query($db,$q) or die(mysqli_error($db)." Q=".$q);
		
		// If there is no email address, we're done
		if($r->num_rows<1){
			echo "<p>That tag isn't in our database yet.<br>We'll hold onto your message and deliver it later.</p>";
			echo "<p><b>$state &mdash; $plat3</b></p><p>$text</p>";
			
		// else, if there is an email address, send a message
		} else {
			// Massage the result to get the email out
			$r1 = $r->fetch_assoc();
			$email = $r1['email'];
			
			//Send that message!
			txtTag($db,$email,$state,$plate,$text);
			
			echo "<h2>You texted that tag!</h2>";
			echo "<p><b>$state &mdash; $plat3</b></p><p>\"$text\"</p>";
		}
		
		// Handle effective tags
		if($r->num_rows<1 || $r1['share'] == true){
			// Send messages to all effective tags!
			
			$plate_original = $plate;

			// Find all tags that are effectively the same
			$q="SELECT * FROM tags WHERE state='$state' AND tag_effective='$p1ate' AND tag != '$plate'";
			$r=mysqli_query($db,$q) or die(mysqli_error($db)." Q=".$q);
			
			if($r->num_rows>0){
				echo "<p><b><i>Also sending your message to:</i></b>";
				while($row = $r->fetch_array())
				{ $rows[] = $row; }
				
				foreach($rows as $row)
				{
					$plate_similar = $row['tag'];
					$email = $row['email'];
					
					echo "<br>$plate_similar";
					
					txtEffectiveTag($db,$email,$state,$plate_original,$plate_similar,$text);
				}
				
				echo "</p>";
			}	
		}			
	}
}

// End up here if the plate is non-existant
} else {echo "Did you even enter a plate?";}
} else {
	echo "<h2>Uh oh!</h2>";
	echo "This form has already been submitted once, please start fresh!";
}
?>
<p><a href="javascript:history.back()">&lt; Back</a></p>
<?php 
	fbLikeBox();
	include('./footer.php'); 
?>