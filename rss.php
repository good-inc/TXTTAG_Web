<?php
	include_once($_SERVER['DOCUMENT_ROOT'].'/includes/functions.php');
	$db = connectDB();
	
	$state = $_GET["state"];
	$plate = $_GET["plate"];
	
	$plat3 = prettyPlate($plate);
	$year = date("Y");
	
	header("Content-Type: application/rss+xml; charset=ISO-8859-1");
 
    $rssfeed = '<?xml version="1.0" encoding="ISO-8859-1"?>';
    $rssfeed .= '<rss version="2.0">';
    $rssfeed .= '<channel>';
    $rssfeed .= "<title>TXT TAG: $state $plat3</title>";
    $rssfeed .= "<link>http://www.txttag.me/includes/process.php?action=view&amp;state=$state&amp;plate=$plate</link>";
    $rssfeed .= "<description>TXT TAG messages for the license plate $state $plat3. TXT TAG is a free service which allows one to message another knowing only their license plate.</description>";
    $rssfeed .= '<language>en-us</language>';
    $rssfeed .= "<copyright>{c} $year GOOD Inc. http://buygood.us</copyright>";
    
	$q="SELECT public FROM tags WHERE state='$state' AND tag='$plate'";
	$r=mysqli_query($db,$q) or die(mysqli_error($db)." Q=".$q);
	
	$r1 = $r->fetch_assoc();
	$public = $r1['public'];
	
	if ($public){		
		$q="SELECT * FROM txts WHERE state='$state' AND tag='$plate' ORDER BY `id` DESC";
		$r=mysqli_query($db,$q) or die(mysqli_error($db)." Q=".$q);
		
		if($r->num_rows>0){
			while($row = $r->fetch_array()){
				$rows[] = $row;
			}
				
			foreach($rows as $row)
			{
				$date = date("D, d M Y H:i:s O", strtotime($row['date']));
				$rssfeed .= '<item>';
		        $rssfeed .= '<title>' . $row['msg'] . '</title>';
		        //$rssfeed .= '<description>' . $description . '</description>';
		        //$rssfeed .= '<link>' . $link . '</link>';
		        $rssfeed .= '<pubDate>' . $date . '</pubDate>';
		        //$rssfeed .= '<guid>' . $row['id'] . '</guid>';
		        $rssfeed .= '</item>';
			}
		} else { 
			$rssfeed .= '<item>';
	        $rssfeed .= '<title>No Messages Yet</title>';
	        $rssfeed .= '<description>Send the first message!</description>';
	        $rssfeed .= '<link>http://txttag.me</link>';
	        //$rssfeed .= '<pubDate>' . $date . '</pubDate>';
	        $rssfeed .= '</item>';
		}
	} else { 
		$rssfeed .= '<item>';
	    $rssfeed .= '<title>These messages are private</title>';
	    $rssfeed .= '<description>Send some feedback if you\'d like to see private feeds be possible.</description>';
	    $rssfeed .= '<link>http://txttag.me</link>';
	    //$rssfeed .= '<pubDate>' . $date . '</pubDate>';
	    $rssfeed .= '</item>'; 
	}
 
    $rssfeed .= '</channel>';
    $rssfeed .= '</rss>';
 
    echo xmlIt($rssfeed);
?>