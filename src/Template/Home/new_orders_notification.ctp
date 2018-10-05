<?php if($notificationStatement){?>
        <tr><td><?php foreach($notificationStatement as $notification){
            echo "&raquo; ".$notification."<br/>";
        }
}
    else{
			echo "<h4>No notification for today!</h4>";
	}?>