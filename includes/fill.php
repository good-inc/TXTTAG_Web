<?php 
	include_once($_SERVER['DOCUMENT_ROOT'].'/includes/functions.php');
	$db = connectDB();
	
	echo "Let's do this!<br>";
	
	// Tables to update are 'tags' and 'txts'
	$table = 'txts';
	
	$q = "SELECT * FROM $table";
    $r = mysqli_query($db,$q) or die(mysqli_error($db)." Q=".$q);
    
    while($row = $r->fetch_array())
		{ $rows[] = $row; }
		
	foreach($rows as $row)
	{
       	$tag = $row['tag'];
        $id = $row['id'];

        $tag_effective = effective_tag($tag);
        
		echo "$tag -> $tag_effective<br>";
        
        $q = "UPDATE $table SET tag_effective = '$tag_effective' WHERE id='$id'";
    	mysqli_query($db,$q) or die(mysqli_error($db)." Q=".$q);
    }

?>