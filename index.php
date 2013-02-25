<?php include('./includes/header.php'); ?>
<h2>Send messages via license plates.</h2>

<? printForm("text"); ?>

<p><a href="./claim.php">Claim Tag</a> | <a href="./view.php">View Messages</a></p>
<p><a href="./remind.php">Remind me to claim my tag</a></p>

<?php 	fbFacePile();
		include('./includes/footer.php'); ?>