<?php include('./includes/header.php'); ?>
<h2>Say your goodbyes.</h2>
<p>Leave the plate field blank to remove your email completely.<br>If you've claimed multiple tags, include your plate to stop messages for just one tag.<br>If you'd like to give us a piece of your mind, <a href="<? echo "$root/contact.php"?>">please do</a>.</p>

<? printForm("leave"); ?>

<p><a href="javascript:history.back()">&lt; Back</a></p>

<?php include('./includes/footer.php'); ?>