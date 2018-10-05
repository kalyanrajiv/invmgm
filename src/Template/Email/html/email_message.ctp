<?php  //pr($content);
echo "Subject:".$content['subject'];echo"<br/>";
echo "message Type:".$content['message_type'];echo"<br/>";
echo "message:".$content['message'];echo"<br/>";
if(array_key_exists('sender_status',$content)){
    echo "sender_status:".$content['sender_status'];echo"<br/>";
}
if(array_key_exists('type',$content)){
   echo "type:".$content['type'];echo"<br/>";
}
if(array_key_exists('sender_type',$content)){
 echo "sender_type:".$content['sender_type'];echo"<br/>";
}
 if(array_key_exists('sent_to_id',$content)){
 echo "sent_to_id:".$content['sent_to_id'];echo"<br/>";
}
if(array_key_exists('user_id',$content)){
 echo "user_id:".$content['user_id'];echo"<br/>";
}
if(array_key_exists('sent_by',$content)){
 echo "sent_by:".$content['sent_by'];echo"<br/>";
}
//echo "sent_to_id:".$content['Message']['sent_to_id'];echo"<br/>";
//echo "user_id:".$content['Message']['user_id'];echo"<br/>";
//echo "sent_by:".$content['Message']['sent_by'];echo"<br/>";
 
 ?>