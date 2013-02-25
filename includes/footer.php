<p><? echo "<small>" . $count["tags"] . " tags claimed &mdash; " . $count["msgs"] . " messages sent</small><br>"; ?>
<small><b>Use responsibly. Don't text and drive.</b></small><br>
<a href="<? echo "$root"?>">Home</a> | <a href="<? echo "$root/faq.php"?>">FAQ</a> | <a href="<? echo "$root/terms.php"?>">Terms</a> | <a href="<? echo "$root/contact.php"?>">Feedback</a><br>
&copy; <?php echo date("Y") ?> <a href="http://www.buygood.us">GOOD Inc.</a>
</p>
</div><!-- Wrapper -->
</div><!-- Outer -->
</body>
</html>
<? $db->close(); ?>