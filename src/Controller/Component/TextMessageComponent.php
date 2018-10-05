<?php
    namespace App\Controller\Component;
    use Cake\Controller\Component;
    use Cake\ORM\TableRegistry;
    use Cake\Network\Session;
	use Cake\Core\Configure;
	use Cake\Core\Configure\Engine\PhpConfig; 
    //usage e.g : $this->TableDefinition->get_table_defination('product_table',$kiosk_id);
    class TextMessageComponent extends Component {
        
        private function sendSMS($content) {
            $ch = curl_init('https://api.smsbroadcast.co.uk/api-adv.php');
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $content);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $output = curl_exec ($ch);
            curl_close ($ch);
            return $output;    
	}
        
        public function test_text_message($destination = '', $text = ''){
			$text_message_credentials = Configure::read('text_message_credentials');
			
			$username =  $text_message_credentials['username'];
			$password = $text_message_credentials['password'];
			$source   = $text_message_credentials['source'];
			
            //$username = 'mobilebooth';
            //$password = 'bn1469';
            //$destination = '7904717175'; //Multiple numbers can be entered, separated by a comma
            //$source    = 'MBooth';
            //$text = 'This is our test message.';
            $ref = 'abc123';
                
            $content =  'username='.rawurlencode($username).
                        '&password='.rawurlencode($password).
                        '&to='.rawurlencode($destination).
                        '&from='.rawurlencode($source).
                        '&message='.rawurlencode($text).
                        '&ref='.rawurlencode($ref);
          
            $smsbroadcast_response = $this->sendSMS($content);
            //$response_lines = explode("\n", $smsbroadcast_response);
            //
            //foreach( $response_lines as $data_line){
            //        $message_data = "";
            //        $message_data = explode(':',$data_line);
            //        if($message_data[0] == "OK"){
            //            echo "The message to ".$message_data[1]." was successful, with reference ".$message_data[2]."\n";
            //        }elseif( $message_data[0] == "BAD" ){
            //            echo "The message to ".$message_data[1]." was NOT successful. Reason: ".$message_data[2]."\n";
            //        }elseif( $message_data[0] == "ERROR" ){
            //            echo "There was an error with this request. Reason: ".$message_data[1]."\n";
            //        }
            //}
	}
        
    }
?>