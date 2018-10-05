<?php
namespace App\Controller\Component;
use Cake\Controller\Component;
 
 //usage e.g : $this->TableDefinition->get_table_defination('product_table',$kiosk_id);
    class PusherComponent extends Component {
            public function push1($text){
            $path = dirname(__FILE__); 
            $isboloRam = strpos($path,"mbwaheguru");
            if($isboloRam != false){
               //echo "mbwaheguru";die;
                if (class_exists('Pusher\Pusher')) {
                }else{
                    $filename = "/var/www/vhosts/mbwaheguru.co.uk/httpdocs/vendor/pusher/pusher-php-server/src/Pusher.php";
                    require($filename);
                }
                $pusher = new \Pusher\Pusher(
                                          '8716876ee9a41890dec1',
                                        '5f9fb3d8e27539a73157',
                                        '403286',
                                        array('cluster' => 'eu','encrypted' => true));
               
            }else{
                 if (class_exists('Pusher\Pusher')) {
                }else{
                    $filename = "/var/www/vhosts/mbwaheguru.co.uk/httpdocs/vendor/pusher/pusher-php-server/src/Pusher.php";
                    require($filename);
                }   
                $pusher = new \Pusher\Pusher(
                                        '6d915fc6ee5916612811',
                                        'f9e4ee2857b3298a56cb',
                                        '350851',
                                        array('cluster' => 'eu','encrypted' => true));
                
            }
            if($text){
                    $data['message'] = $text;
                 //   pr($data);die;
                    $pusher->trigger('mychannel', 'myevent', $data);
            }
            
            
         }
        public function push($text){
            $path = dirname(__FILE__); 
            $isboloRam = strpos($path,"mbwaheguru");
            if($isboloRam != false){
               //echo "mbwaheguru";die;
                if (class_exists('Pusher\Pusher')) {
                }else{
                    $filename = "/var/www/vhosts/mbwaheguru.co.uk/httpdocs/vendor/pusher/pusher-php-server/src/Pusher.php";
                    require($filename);
                }
                $pusher = new \Pusher\Pusher(
                                          '8716876ee9a41890dec1',
                                        '5f9fb3d8e27539a73157',
                                        '403286',
                                        array('cluster' => 'eu','encrypted' => true));
               
            }else{
                 if (class_exists('Pusher\Pusher')) {
                }else{
                    $filename = "/var/www/vhosts/mbwaheguru.co.uk/httpdocs/vendor/pusher/pusher-php-server/src/Pusher.php";
                    require($filename);
                }   
                $pusher = new \Pusher\Pusher(
                                        '6d915fc6ee5916612811',
                                        'f9e4ee2857b3298a56cb',
                                        '350851',
                                        array('cluster' => 'eu','encrypted' => true));
                
            }
            if($text){
                $data['message'] = $text;
                $pusher->trigger('mychannel', 'myevent', $data);
            }
        }
        
        public function email_kiosk_push($text,$kioskId){//for sending pop up to a particular kiosk
            $path = dirname(__FILE__); 
            $isboloRam = strpos($path,"mbwaheguru");
            if($isboloRam != false){
               //echo "mbwaheguru";die;
                if (class_exists('Pusher\Pusher')) {
                }else{
                     $filename = "/var/www/vhosts/mbwaheguru.co.uk/httpdocs/vendor/pusher/pusher-php-server/src/Pusher.php";
                    require($filename);
                }
                $pusher = new \Pusher\Pusher(
                                          '8716876ee9a41890dec1',
                                        '5f9fb3d8e27539a73157',
                                        '403286',
                                        array('cluster' => 'eu','encrypted' => true));
               
            }else{
                 if (class_exists('Pusher')) {
                }else{
                    $filename = "/var/www/vhosts/mbwaheguru.co.uk/httpdocs/vendor/pusher/pusher-php-server/src/Pusher.php";
                    require($filename);
                }   
                $pusher = new \Pusher\Pusher(
                                        '6d915fc6ee5916612811',
                                        'f9e4ee2857b3298a56cb',
                                        '350851',
                                        array('cluster' => 'eu','encrypted' => true));
                
            }
           if($text){
               $event = $kioskId."_kiosk_email";//event will only be triggered on the kiosks where kiosk id matches with the session
                    //see default file in layout for js
                    $data['message'] = $text;
                    $pusher->trigger('mychannel',$event,$data);
            }
        }
        
        public function email_user_push($text,$userId){//for sending pop up to a particular kiosk
             $path = dirname(__FILE__); 
            $isboloRam = strpos($path,"mbwaheguru");
            if($isboloRam != false){
               //echo "mbwaheguru";die;
                if (class_exists('Pusher\Pusher')) {
                }else{
                    $filename = "/var/www/vhosts/mbwaheguru.co.uk/httpdocs/vendor/pusher/pusher-php-server/src/Pusher.php";
                    require($filename);
                }
                $pusher = new \Pusher\Pusher(
                                          '8716876ee9a41890dec1',
                                        '5f9fb3d8e27539a73157',
                                        '403286',
                                        array('cluster' => 'eu','encrypted' => true));
               
            }else{
                 if (class_exists('Pusher\Pusher')) {
                }else{
                    $filename = "/var/www/vhosts/hpwaheguru.co.uk/httpdocs/vendor/pusher/pusher/pusher-php-server/lib/Pusher.php";
                    require($filename);
                }   
                $pusher = new \Pusher\Pusher(
                                        '6d915fc6ee5916612811',
                                        'f9e4ee2857b3298a56cb',
                                        '350851',
                                        array('cluster' => 'eu','encrypted' => true));
                
            }
            // trigger on my_channel' an event called 'my_event' with this payload:
            if($text){
                $event = $userId."_user_email";//event will only be triggered on the kiosks where user id matches with the session
                    //see default file in layout for js
                    $data['message'] = $text;
                    $pusher->trigger('mychannel',$event,$data);
            }
        }
        
        public function group_popup($text,$authGroup){//for sending pop up to a admin
             $path = dirname(__FILE__); 
            $isboloRam = strpos($path,"mbwaheguru");
            if($isboloRam != false){
               //echo "mbwaheguru";die;
               // echo "sdxcxcvsf";die;
                if (class_exists('Pusher\Pusher')) {
                }else{
                     
                    $filename = "/var/www/vhosts/mbwaheguru.co.uk/httpdocs/vendor/pusher/pusher-php-server/src/Pusher.php";
                    require($filename);
                    // echo "sdssdsdsf";die;
                }
                $pusher = new \Pusher\Pusher(
                                          '8716876ee9a41890dec1',
                                        '5f9fb3d8e27539a73157',
                                        '403286',
                                        array('cluster' => 'eu','encrypted' => true));
               
            }else{
                // echo "sdsf";die;
                 if (class_exists('Pusher\Pusher')) {
                }else{
                   
                    $filename = "/var/www/vhosts/mbwaheguru.co.uk/httpdocs/vendor/pusher/pusher-php-server/src/Pusher.php";
                    require($filename);
                }   
                $pusher = new \Pusher\Pusher(
                                        '6d915fc6ee5916612811',
                                        'f9e4ee2857b3298a56cb',
                                        '350851',
                                        array('cluster' => 'eu','encrypted' => true));
                
            }
             // trigger on my_channel' an event called 'my_event' with this payload:
            if($text){
                    $event = $authGroup."_auth_group";//event will only be triggered on the kiosks where auth group id matches with the session
                                                    //see default file in layout for js
                    $data['message'] = $text;
                    $pusher->trigger('mychannel',$event,$data);
            }
        }
    }
?>