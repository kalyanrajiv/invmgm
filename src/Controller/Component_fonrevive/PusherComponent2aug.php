<?php
namespace App\Controller\Component;
use Cake\Controller\Component;
 
 //usage e.g : $this->TableDefinition->get_table_defination('product_table',$kiosk_id);
    class PusherComponent extends Component {
         public function push1($text){
           
           // $filename = "/var/www/vhosts/hpwaheguru.co.uk/httpdocs/vendor/pusher/autoload.php";
            // $filename = "/var/www/vhosts/hpwaheguru.co.uk/httpdocs/vendor/pusher/pusher/pusher-php-server/lib/Pusher.php";
             $filename = "/var/www/vhosts/hpwaheguru.co.uk/httpdocs/vendor/pusher/pusher/pusher-php-server/lib/Pusher.php";
              require($filename);
                $pusher = new \Pusher(
                                        '6d915fc6ee5916612811',
                                        'f9e4ee2857b3298a56cb',
                                        '350851',
                                        array('cluster' => 'eu','encrypted' => true));
                if($text){
                    $data['message'] = $text;
                    $pusher->trigger('mychannel', 'myevent', $data);
                }
            //   $data = array("message" => "helo");
            //   //$data1 = json_encode($data);
            //$pusher->trigger('mychannel', 'myevent', $data);
         }
        public function push($text){
            $filename = "/var/www/vhosts/hpwaheguru.co.uk/httpdocs/vendor/pusher/pusher/pusher-php-server/lib/Pusher.php";
            require($filename);
             $pusher = new \Pusher(
                                        '6d915fc6ee5916612811',
                                        'f9e4ee2857b3298a56cb',
                                        '350851',
                                        array('cluster' => 'eu','encrypted' => true));
           
            // trigger on my_channel' an event called 'my_event' with this payload:
             if($text){
                    $data['message'] = $text;
                    $pusher->trigger('mychannel', 'my-notifications', $data);
                }
            if($text){
                $data['message'] = $text;
               // pr($data);die;
               $pusher->trigger('my-channel','my-notifications',$data);
            }
        }
        
        public function email_kiosk_push($text,$kioskId){//for sending pop up to a particular kiosk
           $filename = "/var/www/vhosts/hpwaheguru.co.uk/httpdocs/vendor/pusher/pusher/pusher-php-server/lib/Pusher.php";
            require($filename);
             $pusher = new \Pusher(
                                        '6d915fc6ee5916612811',
                                        'f9e4ee2857b3298a56cb',
                                        '350851',
                                        array('cluster' => 'eu','encrypted' => true));
           // $pusher = new Pusher('2a940dff028f481cab29', '1149b3e948bbaa01865d', '345169');
            // trigger on my_channel' an event called 'my_event' with this payload:
            if($text){
                  $event = $kioskId."_kiosk_email";//event will only be triggered on the kiosks where kiosk id matches with the session
                 //see default file in layout for js
                 $data['message'] = $text;
                $pusher->trigger('mychannel',$event,$data);
            }
        }
        
        public function email_user_push($text,$userId){//for sending pop up to a particular kiosk
        // $filename = "/var/www/vhosts/hpwaheguru.co.uk/httpdocs/vendor/pusher/autoload.php";
         $filename = "/var/www/vhosts/hpwaheguru.co.uk/httpdocs/vendor/pusher/pusher/pusher-php-server/lib/Pusher.php";
            require($filename);
             $pusher = new \Pusher(
                                        '6d915fc6ee5916612811',
                                        'f9e4ee2857b3298a56cb',
                                        '350851',
                                        array('cluster' => 'eu','encrypted' => true));
            // trigger on my_channel' an event called 'my_event' with this payload:
            if($text){
                 $event = $userId."_user_email";//event will only be triggered on the kiosks where user id matches with the session
                                   //see default file in layout for js
                $data['message'] = $text;
                $pusher->trigger('mychannel',$event,$data);
            }
        }
        
        public function group_popup($text,$authGroup){//for sending pop up to a admin
         //$filename = "/var/www/vhosts/hpwaheguru.co.uk/httpdocs/vendor/pusher/autoload.php";
          $filename = "/var/www/vhosts/hpwaheguru.co.uk/httpdocs/vendor/pusher/pusher/pusher-php-server/lib/Pusher.php";
            require($filename);
             $pusher = new \Pusher(
                                        '6d915fc6ee5916612811',
                                        'f9e4ee2857b3298a56cb',
                                        '350851',
                                        array('cluster' => 'eu','encrypted' => true));
            // trigger on my_channel' an event called 'my_event' with this payload:
            if($text){
                $event = $authGroup."_auth_group";//event will only be triggered on the kiosks where auth group id matches with the session
                                                //see default file in layout for js
                $data['message'] = $text;
                $pusher->trigger('notifications',$event,$data);
            }
        }
    }
?>