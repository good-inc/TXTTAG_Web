<?php 
	$root = "http://www.txttag.me";
	include_once($_SERVER['DOCUMENT_ROOT'].'/includes/functions.php');
	$db = connectDB();
	$count = getStats($db);
	
	session_start(); 
	if (!isset($_SESSION['page_instance_ids'])) { 
		$_SESSION['page_instance_ids'] = array(); 
	} 
	$_SESSION['page_instance_ids'][] = uniqid('', true); 
?>
<!DOCTYPE html>
<html>
	<head>
		<title>TXT TAG: Send messages via license plates.</title>
		
		<meta http-equiv="Content-Type" content="text/html;charset=utf-8" />
		<meta name="keywords" content="text tag, txt tag, text, txt, tag, license, license plate, license plate number, message, plate, car, vehicle, person, send, alert" />
		 <meta name="description" content="TXT TAG allows you to send messages to others when all you know is their license plate number." />
		 <link rel="icon" type="image/png" href="<?php echo $root ?>/images/favicon.ico">
		
		<link href="<?php echo $root ?>/includes/style.css?<?php echo time();?>" rel="stylesheet" />
		<script type="text/javascript" src="http://www.geoplugin.net/javascript.gp"></script>
		<?php echo $head ?>
	</head>	
	<body>
	<div id="fb-root"></div>
	<script>(function(d, s, id) {
	  var js, fjs = d.getElementsByTagName(s)[0];
	  if (d.getElementById(id)) return;
	  js = d.createElement(s); js.id = id;
	  js.src = "//connect.facebook.net/en_US/all.js#xfbml=1&appId=71255849496";
	  fjs.parentNode.insertBefore(js, fjs);
	}(document, 'script', 'facebook-jssdk'));</script>
	<script type="text/javascript">
	  var _gaq = _gaq || [];
	  _gaq.push(['_setAccount', 'UA-37749287-1']);
	  _gaq.push(['_trackPageview']);
	  (function() {
		var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
		ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
		var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
	  })();
	</script>
	
	<div id="outer">
	<div id="container1">
	<div id="triangle1"></div>
	</div><!-- container1 -->
	<div id="container2">
	<div class="triangle2"></div>
	</div><!-- container2 -->
	<div id="wrapper" style="text-align: center">
	

	
	<p><a href="<? echo $root ?>" style="text-decoration: none; color: black;"><img src="<?php echo $root ?>/images/txttag.png" alt="It really whips the llama's ass."></a></p>
