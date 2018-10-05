<?php
namespace App\Controller;

use App\Controller\AppController;
use Cake\Event\Event;
use Cake\Core\Configure;
use Cake\Core\Configure\Engine\PhpConfig;
use Cake\Network\Request;
use Cake\ORM\TableRegistry;
use Cake\Mailer\Email;
use Cake\I18n\Time;

use Cake\Datasource\ConnectionManager;

class MessagesController extends AppController
{

     public $helpers = [
         'Paginator' => ['templates' => 'paginatortemplates']
         ];
      public function initialize(){
        parent::initialize();
	 	$this->loadComponent('Pusher');
     }
    public function index(){
        // $pushStr = "Email<br/>From: anjna Subject: pusher";
        //$this->Pusher->push1($pushStr);
        $loggedInUser = $this->request->session()->read('Auth.User.id'); 
        $warehouseKioskId = Configure::read('WAREHOUSE_KIOSK_ID');
        $kiosks_query = $this->Kiosks->find('list',[
                                                'keyField' => 'id',
                                                'valueField' => 'name',
                                             ]);
        if(!empty($kiosks_query)){
            $kioskname = $kiosks_query->toArray();
        }else{
            $kioskname = array();
        }
        $userID = $this->request->session()->read('Auth.User.id');
        $kiosk_id = $this->request->session()->read('kiosk_id');
        //echo $userID;
        $user_query = $this->Users->find('list',[
                                                'keyField' => 'id',
                                                'valueField' => 'username',
                                             ]);
        if(!empty($user_query)){
            $users = $user_query->toArray();
        }else{
            $users = array();
        }
        $idList = array();
        if(empty($kiosk_id)){
            $kiosk_id = $warehouseKioskId;
        }
		if($this->request->is(array('post', 'put'))) {
                 // pr($this->request->data); 
                    //below if block is for getting the message ids of group messages selected for deletion or marking as read/unread
                    if(array_key_exists('subject', $this->request->data['data']['Message'])){
                     //   echo "hello";
                            foreach($this->request->data['data']['Message']['subject'] as $subjectKey => $subjectData){
                                
                              // echo  $subjectKey;
                                $idList_query = $this->Messages->find('list',[
                                                    'keyField' => 'id',
                                                    // 'valueField' => 'id',
                                                     'conditions' => array(
												'AND' => array('date(Messages.created)' => $subjectData['created'][0],
																							 'Messages.subject' => $subjectKey
																							 ),
												'OR' => array(
														array(
															'AND' => array('Messages.sender_id' => $kiosk_id),
														array('Messages.sent_by IS null')
															),
														array(
															'AND' => array('Messages.sent_by' => $loggedInUser),
														array('Messages.sender_id IS null'),
														)
													),
															//'Message.sender_id'=>$kiosk_id,
															'Messages.sender_status' =>2
															),
                                                 ]
                                        ); 
           
        
                                if(!empty($idList_query)){
                                     $idList = $idList_query->toArray();
                                }
                              //  pr($repairusers);die;
                                
                                    
                            }
                    }
                   
       
                   // pr($idList); 
                    //below we are putting all the ids of groups in a single array
                    $groupMessageIds = array();
                    if(count($idList)){
                            foreach($idList as $kl => $id_list){
                                //pr($id_list);
                                    //foreach($id_list as $idVal){
                                   //     echo $idVal;
                                            if(in_array($id_list, $groupMessageIds)){
                                                    ;
                                            }else{
                                                    $groupMessageIds[] = $id_list;
                                            }
                                    //}
                            }
                    }
                            
                    if(array_key_exists('Delete',$this->request->data)){
                        
                            $messages = $this->request->data['data']['Message'];
                            $message_id = array();
                           // pr($messages);
                            if(array_key_exists('id',$messages)){
                                
                                $message_id = $messages['id'];
                            }
                            $sender_id = ''; $sent_by = '';
                            if(array_key_exists('sender_id',$messages)){
                                    $sender_id = $messages['sender_id'];
                            }
                            if(array_key_exists('sent_by',$messages)){
                                    $sent_by = $messages['sent_by'];
                            }
                            
                            if(count($groupMessageIds)){
                                    $message_id = array_merge($groupMessageIds,$message_id);
                            }
                            
                            //update message status
                            if(count($message_id) && ($sender_id==$kiosk_id || $sent_by==$loggedInUser)){
                            $status = $this->Messages->updateAll(array('messages.sender_status'=>3),
                                            array('messages.id IN'=> $message_id)
                                            );
                            }

                    }
                    elseif(array_key_exists('submit',$this->request->data)){
                      //echo "submit";
                     // pr($this->request->data['data']);//die;
                            $messages = $this->request->data['data']['Message'];
  //pr($messages); 
                            $message_id = array();
                            if(array_key_exists('id',$messages)){
                                    $message_id = $messages['id'];
                            }
                          //  pr($message_id);
                            $sender_id = ''; $sent_by = '';
                            if(array_key_exists('sender_id',$messages)){
                                    $sender_id = $messages['sender_id'];
                            }
                            if(array_key_exists('sent_by',$messages)){
                                    $sent_by = $messages['sent_by'];
                            }
                            $type = $messages['type'];

                            //update message type read/unread
                            //update for kiosk and admin messsage type
                        // pr($groupMessageIds);die;
                            if(count($groupMessageIds)){
                                   $message_id = array_merge($groupMessageIds,$message_id);
                            }

                            if(count($message_id) && ($sender_id==$kiosk_id || $sent_by==$loggedInUser)){
                               
                                    $this->Messages->updateAll(
                                    array('messages.sender_read'=> $type),
                                    array('messages.id IN' => $message_id)
                                    );
                            }
                    }
				}
				//find message and kiosk and admin
               
               //  echo $kiosk_id;
              // echo $loggedInUser;
                 $query = $this->Messages->find('all', array(
                                                              'fields' => array(   'id' => 'id',
                                                                            'receiver_id'=> 'receiver_id',
                                                                            'sender_id'=> 'sender_id',
                                                                            'user_id'=> 'user_id',
                                                                            'sent_by'=> 'sent_by',
                                                                            'sent_to_id'=> 'sent_to_id',
                                                                            'read_by'=> 'read_by',
                                                                            'read_by_user'=> 'read_by_user',
                                                                             'subject' => 'subject',
                                                                            'count' => 'COUNT(Messages.subject)',
                                                                            'message'=> 'message',
                                                                            'date'=> 'date',
                                                                            'type'=> 'type',
                                                                            'receiver_status'=> 'receiver_status',
                                                                            'sender_status'=> 'sender_status',
                                                                            'receiver_read'=> 'receiver_read',
                                                                            'sender_read'=> 'sender_read',
                                                                            'created'=> 'created',
                                                                            'modified'=> 'modified' 
                                                                       ),
                                                            'conditions' => array(
                                                                        'OR' => array(
                                                                                    array(
                                                                                          'AND' => array('sender_id' => $kiosk_id),
                                                                                                  array('sent_by  IS null')
                                                                                      ),
                                                                                    array(
                                                                                      'AND' => array('sent_by' => $loggedInUser),
                                                                                      array('sender_id IS null'),
                                                                                    )
                                                                            ),
                                                                              //'Message.sender_id'=>$kiosk_id,
                                                                              'sender_status' =>2
                                    								
                                    								      ),
                                                          'order' => array('Messages.id' => 'DESC'),
                                                           'group' => array('Date(Messages.created)', 'Messages.subject'),
                                                           'limit' => ROWS_PER_PAGE
            
             
         
                           ));
               //echo $query;
              // pr($query);die;
                $message_query = $this->paginate($query);
                
                if(!empty($message_query)){
                       $message = $message_query->toArray();
                }
 				$sentTo = array();
				//  pr($message);//die;
				foreach($message as $sngmessage){
					 $sentTo[] =$sngmessage->receiver_id;
				}
				$sentArr = array();
				if( count($sentTo) >= 1){
					$inStr = "'".implode("','",$sentTo)."'";
					$query = "SELECT * FROM kiosks where id in ($inStr);";
					 $conn = ConnectionManager::get('default');
                    $stmt = $conn->execute($query);
                    $sentToRS = $stmt ->fetchAll('assoc');
                   // pr($sentToRS);
					foreach($sentToRS as $sngUser){
						$sentArr[$sngUser['id']] =  $sngUser['name'];
					}
				}
				//$message = $this->Paginator->paginate('Message');

				//action list for kiosk and admin
				$this->getNavigationCounts($kiosk_id);
				$this->set('users',$users);
				$this->set('message',$message);
				$this->set('sentArr',$sentArr);
				$this->set('kiosk_id',$kiosk_id);
				$this->set('userID',$userID);
				$this->set('kioskname',$kioskname);
    }
    
    public function trash(){
		    //$this->Message->query('TRUNCATE messages');
		    //pr($this->Message->find('all',array('order' => 'Message.id desc')));
		    $loggedInUser = $this->request->session()->read('Auth.User.id');
		    $user_query = $this->Users->find('list',[
                                                'keyField' => 'id',
                                                'valueField' => 'username',
                                             ]);
            if(!empty($user_query)){
                $users = $user_query->toArray();
            }else{
                $users = array();
            }
             $kiosks_query = $this->Kiosks->find('list',[
                                                'keyField' => 'id',
                                                'valueField' => 'name',
                                             ]);
            if(!empty($kiosks_query)){
                $kiosks = $kiosks_query->toArray();
            }else{
                $kiosks = array();
            }
		    $warehouseKioskId = Configure::read('WAREHOUSE_KIOSK_ID');
		    $kiosk_id = $this->request->session()->read('kiosk_id');
		    if(empty($kiosk_id)){
				    $kiosk_id = $warehouseKioskId;
		    }

		    $userID = $id1 = $this->request->session()->read('Auth.User.id');
		    //pr($userID);
		    //find message admin and kiosk
              $query = $this->Messages->find('all', array(
                                                              'conditions' => array(
				    'OR' => array(
						array('OR' => array(
							array(
							  'AND' => array('Messages.receiver_id' => $kiosk_id),
							      array('Messages.sent_to_id IS null')
							  ),
							array(
							  'AND' => array('Messages.sent_to_id' => $loggedInUser),
							      array('Messages.receiver_id IS null'),
							)
						  )
						),
						array(
						    'OR' => array(
							array(
							  'AND' => array('Messages.sender_id' => $kiosk_id),
							      array('Messages.sent_by IS null')
							  ),
							array(
							  'AND' => array('Messages.sent_by' => $loggedInUser),
							      array('Messages.sender_id IS null'),
							)
						    )
						)
					    ),
					array(
					      'OR' => array(
							array('receiver_id' => $kiosk_id,
									'receiver_status' => 3),
							array('sender_id' => $kiosk_id,
									'sender_status' => 3),
							array('sent_by' => $loggedInUser,
									'sender_status' => 3),
							array('sent_to_id' => $loggedInUser,
									'receiver_status' => 3)
							)
					      )
				),
				  'limit' => ROWS_PER_PAGE,
				'order' => array('Messages.id' => 'DESC') 
                                                          
            
             
         
                           ));
               //echo $query;
              // pr($query);die;
                $message_query = $this->paginate($query);
                
                if(!empty($message_query)){
                       $messages = $message_query->toArray();
                }
             
		    $sentTo = array();
		    foreach($messages as $sngmessage){
			     $sentTo[] =$sngmessage->sender_id;
			      $sentTo[] =$sngmessage->receiver_id;

		    }
		    $sentArr = array();
		    if( count($sentTo) >= 1){
			    $inStr = "'".implode("','",$sentTo)."'";
			    $query = "SELECT * FROM kiosks where id in ($inStr);";
			   $conn = ConnectionManager::get('default');
                    $stmt = $conn->execute($query);
                    $sentToRS = $stmt ->fetchAll('assoc');
			   //pr($sentToRS);//die;
			    foreach($sentToRS as $sngUser){
                    // pr($sngUser);
				    $sentArr[$sngUser['id']] =  $sngUser['name'];
			    }
		    }

		    $this->set(compact('sentArr','kiosk_id','kiosks','users'));
		    $this->getNavigationCounts($kiosk_id);
		    $this->set('userID',$userID);
		    $this->set('sentTo',$sentTo);
		    $this->set('$userID', $userID);
		    $this->set('messages',$messages);
		  //  $data = $this->paginate('Message');
		   // $this->set('data',$data);
		}

    public function inbox() {
				//pr($this->Message->find('all',array('order' => 'Message.id desc')));
                $loggedInUser = $this->request->session()->read('Auth.User.id');
				$warehouseKioskId = Configure::read('WAREHOUSE_KIOSK_ID');
                $userID = $this->request->session()->read('Auth.User.id');
				$kiosk_id = $this->request->session()->read('kiosk_id');
                $kiosks_query = $this->Kiosks->find('list',[
                                                'keyField' => 'id',
                                                'valueField' => 'name',
                                             ]);
                if(!empty($kiosks_query)){
                    $kioskname = $kiosks_query->toArray();
                }else{
                    $kioskname = array();
                }
				 $user_query = $this->Users->find('list',[
                                                'keyField' => 'id',
                                                'valueField' => 'username',
                                             ]);
                if(!empty($user_query)){
                    $users = $user_query->toArray();
                }else{
                    $users = array();
                }
                if(empty($kiosk_id)){
						$kiosk_id = $warehouseKioskId;
					}
				if ($this->request->is(array('post', 'put'))) {
						if(array_key_exists('Delete',$this->request->data)){
								$messages = $this->request->data['data']['Message'];
								$receiver_id = ''; $sent_to_id = '';
								if(array_key_exists('receiver_id',$messages)){
										$receiver_id = $messages['receiver_id'];
								}

								if(array_key_exists('sent_to_id',$messages)){
										$sent_to_id = $messages['sent_to_id'];
								}

								$message_id = array();
								if(array_key_exists('id',$messages)){
										$message_id = $messages['id'];
								}
									//update status
								if(!empty($message_id) && ($receiver_id==$kiosk_id || $sent_to_id==$loggedInUser)){
							  $status = $this->Messages->updateAll(array('receiver_status' => '3'),
												array('Message.id IN' => $message_id )
									 );
								}


						}elseif(array_key_exists('submit',$this->request->data)){
                           
								$messages = $this->request->data['data']['Message'];
                               //pr($messages);die;
                              	$receiver_id = ''; $sent_to_id = '';
								if(array_key_exists('receiver_id',$messages)){
										$receiver_id = $messages['receiver_id'];
								}
								if(array_key_exists('sent_to_id',$messages)){
										$sent_to_id = $messages['sent_to_id'];
								}
								$message_id = array();
								if(array_key_exists('id',$messages)){
										$message_id = $messages['id'];
								}
                                $type = $this->request->data['data']['Message']['type']; 
								//update for kiosk and admin messsage type
								if(!empty($message_id) && ($receiver_id==$kiosk_id || $sent_to_id==$loggedInUser)){
								$this->Messages->updateAll(
										array('messages.receiver_read'=>$type),
										array('messages.id IN ' => $message_id)
										); 
								}
						}
				}
				//find message for kiosk and admin
                 $this->paginate = [
                                
                                    'conditions' => [
                                                        'OR' => [
                                                            [
                                                               'AND' => ['Messages.receiver_id' => $kiosk_id],
                                                                        ['Messages.sent_to_id IS NULL']
                                                            ],
                                                            [
                                                              'AND' => ['Messages.sent_to_id' => $loggedInUser],
                                                              ['Messages.receiver_id IS NULL'],
                                                           ]
                                                        ],
                                                        'Messages.receiver_status' => 0
                                                    ],
                                    'limit' => ROWS_PER_PAGE,
                                    'order' => ['Messages.id desc'],
                                ];
				//pr($this->paginate);die; 
                $messages_query = $this->paginate($this->Messages);
				if(!empty($messages_query)){
                    $message = $messages_query->toArray();
                }else{
                    $message = array();
                }
                 
				$sentTo = array();
				foreach($message as $sngmessage){
                    //pr($sngmessage);die;
					$sentTo[] =$sngmessage->sender_id;
					 $sentTo[] =$sngmessage->receiver_id;
                }
               $sentArr = array();
				if( count($sentTo) >= 1){
					$inStr = "'".implode("','",$sentTo)."'";
					$query = "SELECT * FROM kiosks where id in ($inStr);";
                    $conn = ConnectionManager::get('default');
                    $stmt = $conn->execute($query);
                    $sentToRS = $stmt ->fetchAll('assoc');
					//$sentToRS = $this->Kiosk->query($query);
        			foreach($sentToRS as $sngUser){
						$sentArr[$sngUser['id']] =  $sngUser['name'];
					}
				}
				$result = $this->getNavigationCounts($kiosk_id);
				$this->set('message',$message);
				$this->set('sentTo',$sentTo);
				$this->set('sentArr',$sentArr);
				$this->set('userID',$userID);
				$this->set('kiosk_id',$kiosk_id);
				$this->set(compact('kioskname','users'));
	}
    private function getNavigationCounts($kiosk_id = 0){
		    $loggedInUser =$this->request->session()->read('Auth.User.id');
				$inboxread_query = $this->Messages->find('all', array(
                                                                'conditions' =>
                                                                array(
                                                                      'OR' => array(
                                                                                        array(
                                                                                                 'AND' => array('Messages.receiver_id' => $kiosk_id),
                                                                                                   array('Messages.sent_to_id IS NULL')
                                                                                          ),
                                                                                        array(
                                                                                                'AND' => array('Messages.sent_to_id' => $loggedInUser),
                                                                                                array('Messages.receiver_id IS NULL'),
                                                                                        )
                                                                                    ),
                                                                      'Messages.receiver_read' =>2
                                                                      )
                                                                )
                                                   );
                $inboxread = $inboxread_query->count();
				$kioskEmailCount_query = $this->Messages->find('all', array(
				'conditions' => array('OR' => array(
								    array(
								      'AND' => array('Messages.receiver_id' => $kiosk_id),
									  array('Messages.sent_to_id IS NULL')
								      ),
								    array(
								      'AND' => array('Messages.sent_to_id' => $loggedInUser),
									  array('Messages.receiver_id IS NULL'),
								    )
								  ),
						      array('OR'=>array(array('Messages.receiver_read'=>1),
								  array('Messages.receiver_read'=>0))),
						      array('OR'=>array(array('Messages.receiver_status'=>0),
								  array('Messages.receiver_status'=>1)))
						      )
						)
				);
                $kioskEmailCount = $kioskEmailCount_query->count();
 				$inbox = $kioskEmailCount;
				$sentbox_query = $this->Messages->find('all', array('conditions' => array(
								'OR' => array(
									  array(
									    'AND' => array('Messages.sender_id' => $kiosk_id),
										array('Messages.sent_by IS NULL')
									    ),
									  array(
									    'AND' => array('Messages.sent_by' => $loggedInUser),
										array('Messages.sender_id IS NULL'),
									  )
									),
								      //'Message.sender_id'=>$kiosk_id,
								      'Messages.sender_status' =>2))
								);
                 $sentbox = $sentbox_query->count();
				$trash_query = $this->Messages->find('all',array('conditions' => array(
						    'OR' => array(
								array('OR' => array(
									array(
									  'AND' => array('Messages.receiver_id' => $kiosk_id),
									      array('Messages.sent_to_id IS NULL')
									  ),
									array(
									  'AND' => array('Messages.sent_to_id' => $loggedInUser),
									      array('Messages.receiver_id IS NULL'),
									)
								  )
								),
								array(
								    'OR' => array(
									array(
									  'AND' => array('Messages.sender_id' => $kiosk_id),
									      array('Messages.sent_by IS NULL')
									  ),
									array(
									  'AND' => array('Messages.sent_by' => $loggedInUser),
									      array('Messages.sender_id IS NULL'),
									)
								    )
								)
							    ),
							array(
							      'OR' => array(
									array('receiver_id' => $kiosk_id,
											'receiver_status' => 3),
									array('sender_id' => $kiosk_id,
											'sender_status' => 3),
									array('sent_by' => $loggedInUser,
											'sender_status' => 3),
									array('sent_to_id' => $loggedInUser,
											'receiver_status' => 3)
									)
							      )
						)
				    )//conditions
				);
             $trash = $trash_query->count(); 
				//}
				$this->set('sentbox', $sentbox);
				$this->set('inbox', $inbox);
				$this->set('trash', $trash);
				$this->set('inboxread', $inboxread);
		}
    public function view($id = null){
        $loggedInUser =$this->request->session()->read('Auth.User.id');
        $conn = ConnectionManager::get('default');
        $stmt = $conn->execute('SELECT NOW() as created'); 
        $currentTime = $stmt ->fetchAll('assoc');
        $presentDateTime = $currentTime[0]['created'];
        $warehouseKioskId = Configure::read('WAREHOUSE_KIOSK_ID');
        if (!$this->Messages->exists($id)) {
            throw new NotFoundException(__('Invalid email'));
        }
        $user_query = $this->Users->find('list',[
                                                'keyField' => 'id',
                                                'valueField' => 'username',
                                             ]);
        if(!empty($user_query)){
            $users = $user_query->toArray();
        }else{
            $users = array();
        }
      
        $kiosk_id = $this->request->session()->read('kiosk_id');
        if(empty($kiosk_id)){
                $kiosk_id = $warehouseKioskId;
        }
        $userID = $this->request->session()->read('Auth.User.id');
       // $options = array('conditions' => array('Messages.' . $this->Messages->primaryKey => $id));
        $kiosks_query = $this->Kiosks->find('list',[
                                                'keyField' => 'id',
                                                'valueField' => 'name',
                                             ]);
        if(!empty($kiosks_query)){
            $kioskname = $kiosks_query->toArray();
        }else{
            $kioskname = array();
        }
        $this->set('kioskname',$kioskname);
        $message_query = $this->Messages->get($id);
       //  $message_query = $message_query->hydrate(false);
      //  $message_query = $message_query->hydrate(false);
       if(!empty($message_query)){
        $message = $message_query->toArray();
       }else{
        $message = array();
       }
       //pr($message);
        //$message = $this->Messages->find('first', $options);
        if(!empty($message['sent_to_id'])){
            if($message['sent_to_id'] != $loggedInUser &&
               $message['sent_by'] != $loggedInUser){
                  $this->Flash->success(__('Sorry, you can not read the message sent to the other user!"'));
                  return $this->redirect(array('action' => 'inbox'));
            }
        }
        $this->set(compact('message','users'));

        //update type kiosk and admin
        if($kiosk_id == $message['sender_id']){
                  //changing the type to read in case of sender
                //it does not have much relevance though, as we do not show the time when it was readby sender
            $this->Messages->updateAll(
                array('messages.sender_read' => 2),
                array('messages.id'=> $id)
                        );
        }
 
        //changing the type to read in case of receiver
        if($kiosk_id == $message['receiver_id']){
            if(!(int)$message['read_by_user']){
                $this->Messages->updateAll(
                array(
                      'messages.receiver_read' =>2,
                      'messages.read_by'=>$kiosk_id,
                      'messages.read_by_user'=>$userID,
                      'messages.date'=>$presentDateTime
                      ),
                array('messages.id'=> $id)
                        );
                }else{
                    $this->Messages->updateAll(
                                array('messages.receiver_read' =>2),
                                array('messages.id'=> $id)
                        );
                }
        }elseif($loggedInUser == $message['sent_to_id']){
            if(!(int)$message['read_by_user']){
                $this->Messages->updateAll(
                array('messages.receiver_read' =>2,
                      'messages.read_by'=>$kiosk_id,
                      'messages.read_by_user'=>$userID,
                      'messages.date'=>$presentDateTime
                      ),
                array('messages.id'=> $id)
                        );
                }else{
                    $this->Messages->updateAll(
                                array('messages.receiver_read' => 2),
                                array('messages.id'=> $id)
                        );
                }
        }
        $this->getNavigationCounts($kiosk_id);
    }
    public function test(){
        $subject = "pusher test";
        $pushStr = "Email<br/>From: anjna Subject: $subject";
        $this->Pusher->push1($pushStr);//created in components
        $this->render('inbox');
    }
    public function add() {
		    //$this->Message->query('TRUNCATE messages');
		    $userID =  $this->request->session()->read('Auth.User.id');
			$user_query = $this->Users->find('list',[
                                                'keyField' => 'id',
                                                'valueField' => 'username',
                                             ]);
            if(!empty($user_query)){
                $users = $user_query->toArray();
            }else{
                $users = array();
            }
			//capturing email ids of the managers for sending email to their real emails below
            $managerEmails_query = $this->Users->find('list',[
                                                'keyField' => 'id',
                                                'valueField' => 'email',
                                                'conditions' => ['Users.group_id' => MANAGERS]
                                             ]);
            if(!empty($managerEmails_query)){
                $managerEmails = $managerEmails_query->toArray();
            }else{
                $managerEmails = array();
            }
             $managers_query = $this->Users->find('list',[
                                                'keyField' => 'id',
                                                'valueField' => 'username',
                                                'conditions' => ['Users.group_id' => MANAGERS]
                                             ]);
            if(!empty($managers_query)){
                $managers = $managers_query->toArray();
            }else{
                $managers = array();
            } 
			unset($managers[$userID]);
            $admins_query = $this->Users->find('list',[
                                                'keyField' => 'id',
                                                'valueField' => 'username',
                                                'conditions' => ['Users.group_id' => ADMINISTRATORS]
                                             ]);
            if(!empty($admins_query)){
                $admins = $admins_query->toArray();
            }else{
                $admins = array();
            } 
		   unset($admins[$userID]);
			// $salesRep = $this->User->find('list',array('fields' => array('id','username'),'conditions' => array('User.group_id' => SALESMAN)));
             $salesRep_query = $this->Users->find('list',[
                                                'keyField' => 'id',
                                                'valueField' => 'username',
                                                'conditions' => ['Users.group_id' => inventory_manager]
                                             ]);
            if(!empty($salesRep_query)){
                $salesRep = $salesRep_query->toArray();
            }else{
                $salesRep = array();
            } 
			unset($salesRep[$userID]);
            $allUsers_query = $this->Users->find('list',[
                                                'keyField' => 'id',
                                                'valueField' => 'username',
                                             ]);
            if(!empty($allUsers_query)){
                $allUsers = $allUsers_query->toArray();
            }else{
                $allUsers = array();
            }
		    //removing managers , admins and the logged in user from the list
		    foreach($managers as $m => $manager){
				    unset($allUsers[$m]);
		    }
		    foreach($admins as $a => $admin){
				    unset($allUsers[$a]);
		    }
			foreach($salesRep as $sp => $sales){
				    unset($allUsers[$sp]);
		    }
		    unset($allUsers[$userID]);

		    $warehouseKioskId = Configure::read('WAREHOUSE_KIOSK_ID');

		    $kiosk_id = $this->request->session()->read('kiosk_id');
		    if(empty($kiosk_id)){$kiosk_id = $warehouseKioskId;}
              $kiosks_query = $this->Kiosks->find('list',[
                                                'keyField' => 'id',
                                                'valueField' => 'name',
                                                'order'=>'Kiosks.name asc'
                                             ]);
            if(!empty($kiosks_query)){
                $kioskname = $kiosks_query->toArray();
            }else{
                $kioskname = array();
            }
		   
		    if ($this->request->is('post')) {
				$counter = 0;
				$errorArr = array();
               // pr($this->request->data);die;
                if(empty($this->request->data['subject'])){
						$errorArr[] = "Please select subject";
				}
              // pr($this->request->data);die;
				$this->request->data['user_id'] = $userID;//this is common for both cases
				//this below code is for messaging from kiosk to kiosk
				if($this->request->data['message_type'] == 'kiosk'){
					//$msg = $this->request->data['data']['message'];
					//$this->request->data['message'] = $msg;
					//unset($this->request->data['data']);
					
					 if(empty($this->request->data['message'])){
						$this->Flash->error('Please Enter Your Message');
						return $this->redirect(array('controller'=>'messages','action'=>'add'));
					}
					if(empty($this->request->data['subject'])){
						$this->Flash->error('Please Enter Subject');
						return $this->redirect(array('controller'=>'messages','action'=>'add'));
					}
					$reciver_ids = array();
					if(isset($this->request->data['receiver_id'])){
						//saving the data in case of kiosks from here in case of if
						 $reciver_ids = $this->request->data['receiver_id'];
						$sender_id = $kiosk_id;
                       
						$this->request->data['sender_id'] = $sender_id;
		
						if(in_array('-1',$reciver_ids)){
                            //this is in case of all selected in kiosk dropdown
                              $kiosks_query = $this->Kiosks->find('list',[
                                                'keyField' => 'id',
                                                'valueField' => 'name',
                                             ]);
                            if(!empty($kiosks_query)){
                                $kiosks = $kiosks_query->toArray();
                            }else{
                                $kiosks = array();
                            }
							
							$kioskIDs = array_keys($kiosks);//die;
		
							foreach($kioskIDs as $kioskID){
								if($kioskID == $sender_id)continue;
								$this->request->data['user_id'] = $this->request->session()->read('Auth.User.id');
								$this->request->data['receiver_id'] =  $kioskID;
									//$this->request->data['Message']['user_id'] = $this->Auth->User('id');
                                $message = $this->Messages->newEntity();
                                $message = $this->Messages->patchEntity($message, $this->request->data,['validate' => false]);
                             	 if ($this->Messages->save($message,['validate' => false])) {
                                   $counter++;
                                   $sender = $kioskname[$sender_id];
                                    $subject = $this->request->data['subject'];
                                    //  echo "kk";die;
                                    $pushStr = "Email<br/>From: $sender Subject: $subject";
                                     $this->Pusher->push1($pushStr);//created in components
                                } 
							}
							
						}else{
							foreach($reciver_ids as $key =>  $reciver_id1){
                                $reciver_ids1[] = $reciver_id1;
								$this->request->data['user_id'] = $this->request->session()->read('Auth.User.id');
                                $this->request->data['receiver_id'] =  $reciver_id1;
                                $message = $this->Messages->newEntity();
                               // pr($this->request->data);
                                $message = $this->Messages->patchEntity($message, $this->request->data,['validate' => false]);
                                // pr($message);die;
                                if ($this->Messages->save($message,['validate' => false])){
                                        $sender = $kioskname[$kiosk_id];
										$subject = $this->request->data['subject'];
										$pushStr = "Email<br/>From: $sender Subject: $subject";
									 	$this->Pusher->email_kiosk_push($pushStr,$reciver_id1);//created in components
 								$counter++;
								}
							}
                           
									 
						}
					}else{
						//will not save and will show error
						$errorArr[] = "Please select email addresses from dropdown";
					}
				}//**this below code is for messaging from user to user
				elseif($this->request->data['message_type']  == 'personal'){
					//pr($this->request->data);die;
					//$msg = $this->request->data['data']['message'];
					//$this->request->data['message'] = $msg;
					//unset($this->request->data['data']);
					//pr($this->request->data);die;
					if(empty($this->request->data['message'])){
						$this->Flash->error('Please Enter Your Message');
						return $this->redirect(array('controller'=>'messages','action'=>'add'));
					}
					if(empty($this->request->data['subject'])){
						$this->Flash->error('Please Enter Subject');
						return $this->redirect(array('controller'=>'messages','action'=>'add'));
					}
					$sent_to_ids = array();
					//here check if sent to id is not included then return to message
					if(isset($this->request->data['sent_to_id'])){
					//saving user data for personal message in case of if
					$sent_to_ids = $this->request->data['sent_to_id'];
					$this->request->data['sent_by'] = $userID;
	
					if(in_array('-2',$sent_to_ids) ||//case when all is selected in any of the dropdowns for personal msg
						in_array('-3',$sent_to_ids) ||
						in_array('-4',$sent_to_ids) ||
						in_array('-5',$sent_to_ids)){
						if(in_array('-2',$sent_to_ids)){
                           	$allManagers = array_search('-2',$sent_to_ids);
							// echo "2";die;
                           
							foreach($managers as $m => $manager){
                              //  echo "meeanager";die;
                              $ms[] = $m;
							 	$this->request->data['sent_to_id'] = $m; 
								$message = $this->Messages->newEntity();
                                $message = $this->Messages->patchEntity($message, $this->request->data,['validate' => false]);
						  $send_by_email = Configure::read('send_by_email');
						  $emailSender = Configure::read('EMAIL_SENDER');
                             	 if ($this->Messages->save($message,['validate' => false])) {
										//code for sending emails to the email ids of the managers
										//$managerEmails[$m]
                                       
										$Email = new Email();
										$Email->config('default');
                                       // pr($this->request->data);die;
										$Email->viewVars(array('content' => $this->request->data));
										//$Email->config(array('from' => 'me@example.org', 'transport' => 'MyCustom'));
										//$Email = new CakeEmail(array('from' => 'me@example.org', 'transport' => 'MyCustom'));
										 $emailTo =  $managerEmails[$m];
										$Email->template('email_message');
										$Email->emailFormat('both');
										$Email->to($emailTo);
										//$Email->sender($this->fromemail);
										$Email->transport(TRANSPORT);
										$Email->from([$send_by_email => $emailSender]);
                                        //$Email->sender("sales@oceanstead.co.uk");
										$Email->subject($this->request->data['subject']);
										$Email->send();
										$counter++;
										$sender = $users[$userID];
										$subject = $this->request->data['subject'];
										$pushStr = "Email<br/>From: $sender Subject: $subject";
                                        $this->Pusher->email_user_push($pushStr,$m);//created in components
								}
							}
                            //$sender = $users[$userID];
                            //$subject = $this->request->data['subject'];
                            //$pushStr = "Email<br/>From: $sender Subject: $subject";
                            //$this->Pusher->email_user_push($pushStr,$ms);//created in components
                            //
							//removing the -2 key from the sent_to_ids array
							//unset($sent_to_ids[$allManagers]);
						}
						if(in_array('-3',$sent_to_ids)){//all   	Administrators:users 
							$allAdmins = array_search('-3',$sent_to_ids);
                          // echo "3";die;
							foreach($admins as $a => $admin){
                                $as[] = $a;
								$this->request->data['sent_to_id'] = $a;
								$message = $this->Messages->newEntity();
                                $message = $this->Messages->patchEntity($message, $this->request->data,['validate' => false]);
                                if ($this->Messages->save($message,['validate' => false])){
								$counter++;
                                $sender = $users[$userID];
                                $subject = $this->request->data['subject'];
                                $pushStr = "Email<br/>From: $sender Subject: $subject";
                                  $this->Pusher->email_user_push($pushStr,$a);//created in components
								}
							}
                            //$sender = $users[$userID];
                            //$subject = $this->request->data['subject'];
                            //$pushStr = "Email<br/>From: $sender Subject: $subject";
                            //$this->Pusher->email_user_push($pushStr,$as);//created in components
							//removing the -3 key from the sent_to_ids array
							//unset($sent_to_ids[$allAdmins]);
						}
						if(in_array('-4',$sent_to_ids)){
							//$all_users = array_search('-4',$sent_to_ids);
                           //   echo "-4";die;
							foreach($allUsers as $u => $user){
                                $us[] = $u;
								$this->request->data['sent_to_id'] = $u;
								$message = $this->Messages->newEntity();
                                $message = $this->Messages->patchEntity($message, $this->request->data,['validate' => false]);
                                if ($this->Messages->save($message,['validate' => false])){
								$counter++;
								$sender = $users[$userID];
							 	$subject = $this->request->data['subject']; 
								$pushStr = "Email<br/>From: $sender Subject: $subject";
							  $this->Pusher->email_user_push($pushStr,$u);//created in components
								}
							}
//                            $sender = $users[$userID];
//                            $subject = $this->request->data['subject']; 
//                            $pushStr = "Email<br/>From: $sender Subject: $subject";
//                            $this->Pusher->email_user_push($pushStr,$us);//created in components
//							removing the -4 key from the sent_to_ids array
							//unset($sent_to_ids[$allUsers]);
						}
						if(in_array('-5',$sent_to_ids)){
							//$all_users = array_search('-4',$sent_to_ids);
							// echo "5";die;
							foreach($salesRep as $sp => $sales_rep){
                                $sps[] = $sp;
								$this->request->data['sent_to_id'] = $sp;
								$message = $this->Messages->newEntity();
                                $message = $this->Messages->patchEntity($message, $this->request->data,['validate' => false]);
                                if ($this->Messages->save($message,['validate' => false])){
                                    $counter++;
                                    $sender = $users[$userID];
                                    
                                    $subject = $this->request->data['subject'];
                                    $pushStr = "Email<br/>From: $sender Subject: $subject";
                                    $this->Pusher->email_user_push($pushStr,$sp);//created in components
								}
							}
                            //$sender = $users[$userID];
                            //$subject = $this->request->data['subject'];
                            //$pushStr = "Email<br/>From: $sender Subject: $subject";
                            //$this->Pusher->email_user_push($pushStr,$sps);//created in components
							//removing the -4 key from the sent_to_ids array
							//unset($sent_to_ids[$allUsers]);
						}
	
						//saving the remaining individual ids
						//case if all is not selected in any of the dropdown
						$send_by_email = Configure::read('send_by_email');
                            if(count($sent_to_ids) && is_array($sent_to_ids)){
                             //   echo "6";die;
                                foreach($sent_to_ids as $s => $sent_to_id){
                                    if($sent_to_id != -2 ||
                                       $sent_to_id != -3 ||
                                       $sent_to_id != -4 ||
                                       $sent_to_id != -5){
                                        if($sent_to_id < 0){continue;}//above check was not checking the -tive values, so put this
                                        $this->request->data['sent_to_id'] =  $sent_to_id;
                                        $message = $this->Messages->newEntity();
                                        $message = $this->Messages->patchEntity($message, $this->request->data,['validate' => false]);
								$emailSender = Configure::read('EMAIL_SENDER');
                                        if ($this->Messages->save($message,['validate' => false])){
                                            if(array_key_exists($sent_to_id,$managers)){
                                                    //code for sending emails to the email ids of the managers
                                                    //$managerEmails[$m]
                                                    $Email = new Email();
                                                    $Email->config('default');
                                                    $Email->viewVars(array('content' => $this->request->data));
                                                    //$Email->config(array('from' => 'me@example.org', 'transport' => 'MyCustom'));
                                                    //$Email = new CakeEmail(array('from' => 'me@example.org', 'transport' => 'MyCustom'));
                                                    $emailTo =   $managerEmails[$sent_to_id];
                                                    $Email->template('email_message');
                                                    $Email->emailFormat('both');
                                                    $Email->to($emailTo);
													$Email->transport(TRANSPORT);
												   $Email->from([$send_by_email => $emailSender]);
                                                    $Email->sender($send_by_email);
                                                    $Email->subject($this->request->data['subject']);
                                                    $Email->send();
                                            }
                                            $counter++;
                                            $sender = $users[$userID];
                                            $subject = $this->request->data['subject'];
                                            $pushStr = "Email<br/>From: $sender Subject: $subject";
                                            $this->Pusher->email_user_push($pushStr,$sent_to_id);//created in components
                                        }
                                    }
                                }
                                //$sender = $users[$userID];
                                //$subject = $this->request->data['subject'];
                                //$pushStr = "Email<br/>From: $sender Subject: $subject";
                                //$this->Pusher->email_user_push($pushStr,$sent_to_id);//created in components
                            }
					}else{
                           //echo "case if all is not selected in all of the dropdown";die;
					  $send_by_email = Configure::read('send_by_email');
                            if(count($sent_to_ids) && is_array($sent_to_ids)){
                                foreach($sent_to_ids as $s => $sent_to_id){
                                    if($sent_to_id != -2 &&
                                       $sent_to_id != -3 &&
                                       $sent_to_id != -4 &&
                                       $sent_to_id != -5){
                                            $this->request->data['sent_to_id'] =  $sent_to_id;
                                            $message = $this->Messages->newEntity();
                                            $message = $this->Messages->patchEntity($message, $this->request->data,['validate' => false]);
                                            if ($this->Messages->save($message,['validate' => false])){
                                                    //checking if id belongs to the manager, if yes, will send email message to the real email address as well
										  $emailSender = Configure::read('EMAIL_SENDER');
                                                if(array_key_exists($sent_to_id,$managers)){
                                                        //code for sending emails to the email ids of the managers
                                                        //$managerEmails[$m]
                                                        $Email = new Email();
                                                                $Email->config('default');
                                                                $Email->viewVars(array('content' => $this->request->data));
                                                                //$Email->config(array('from' => 'me@example.org', 'transport' => 'MyCustom'));
                                                                //$Email = new CakeEmail(array('from' => 'me@example.org', 'transport' => 'MyCustom'));
                                                               $emailTo =  $managerEmails[$sent_to_id];
                                                                $Email->template('email_message');
                                                                $Email->emailFormat('both');
                                                                $Email->to($emailTo);
																$Email->transport(TRANSPORT);
																 $Email->from([$send_by_email => $emailSender]);
                                                                // $Email->sender("sales@oceanstead.co.uk");
                                                                $Email->subject($this->request->data['subject']);
                                                                $Email->send();
                                                }
                                                $counter++;
									   //if(array_key_exists($userID,$users)){
										$sender = $users[$userID];
									  // }else{
										//$sender = '1';
									   //}
                                                $subject = $this->request->data['subject'];
                                                $pushStr = "Email<br/>From: $sender Subject: $subject";
                                                $this->Pusher->email_user_push($pushStr,$sent_to_id);//created in components
                                            }
                                    }
                                }
                                //$sender = $users[$userID];
                                //$subject = $this->request->data['subject'];
                                //$pushStr = "Email<br/>From: $sender Subject: $subject";
                                //$this->Pusher->email_user_push($pushStr,$sent_to_ids);//created in components
                            }
					}
				}else{
					//will not save the data and will flash error
					$errorArr[] = "Please select email addresses from dropdown";
					}
				}//** till here
	
	
				if($counter>0){
                     $this->Flash->success(__($counter." Message(s) have been sent."));
					return $this->redirect(array('action' => 'inbox'));
				}else{
					$errorStr = "The Message could not be saved. Please, try again.";
	
					if(count($errorArr) && is_array($errorArr)){
							$errorStr = "The Message could not be saved for the following reasons:<br/>";
							$errorStr.= implode("<br/>",$errorArr);
					}
                      $this->Flash->success(__($errorStr));
					 
				}
		    }
			
		    $this->getNavigationCounts($kiosk_id);
		    $this->set(compact('managers', 'admins', 'allUsers', 'salesRep'));
		    $this->set('userID',$userID);
		    $this->set('kiosk_id',$kiosk_id);
		    $this->set('kioskname',$kioskname);
		}
    
 
     public function reply($id){
				$userID =  $this->request->session()->read('Auth.User.id');
                 $managers_query = $this->Users->find('list',[
                                                'keyField' => 'id',
                                                'valueField' => 'username',
                                                'conditions' => ['Users.group_id' => MANAGERS]
                                             ]);
                if(!empty($managers_query)){
                    $managers = $managers_query->toArray();
                }else{
                    $managers = array();
                } 
				unset($managers[$userID]);
				 $admins_query = $this->Users->find('list',[
                                                'keyField' => 'id',
                                                'valueField' => 'username',
                                                'conditions' => ['Users.group_id' => ADMINISTRATORS]
                                             ]);
                if(!empty($admins_query)){
                    $admins = $admins_query->toArray();
                }else{
                    $admins = array();
                } 
				unset($admins[$userID]);
                 $salesRep_query = $this->Users->find('list',[
                                                'keyField' => 'id',
                                                'valueField' => 'username',
                                                'conditions' => ['Users.group_id' => SALESMAN]
                                             ]);
                if(!empty($salesRep_query)){
                    $salesRep = $salesRep_query->toArray();
                }else{
                    $salesRep = array();
                } 
				unset($salesRep[$userID]);
				 $allUsers_query = $this->Users->find('list',[
                                                'keyField' => 'id',
                                                'valueField' => 'username',
                                             ]);
                if(!empty($allUsers_query)){
                    $allUsers = $allUsers_query->toArray();
                }else{
                    $allUsers = array();
                }
				//removing managers , admins and the logged in user from the list
				foreach($managers as $m => $manager){
						unset($allUsers[$m]);
				}
				foreach($admins as $a => $admin){
						unset($allUsers[$a]);
				}
				foreach($salesRep as $sp => $sales){
				    unset($allUsers[$sp]);
				}
				unset($allUsers[$userID]);
				 $message = $this->Messages->get($id, [
          
                ]);
				//$options = array('conditions' => array('Message.' . $this->Message->primaryKey => $id));
				//$message = $this->Message->find('first', $options);
	
				$warehouseKioskId = Configure::read('WAREHOUSE_KIOSK_ID');
	
				$kiosk_id = $this->request->session()->read('kiosk_id');
				if(empty($kiosk_id)){$kiosk_id = $warehouseKioskId;}
                 $kiosks_query = $this->Kiosks->find('list',[
                                                'keyField' => 'id',
                                                'valueField' => 'name',
                                             ]);
                if(!empty($kiosks_query)){
                    $kioskname = $kiosks_query->toArray();
                }else{
                    $kioskname = array();
                }
				$this->getNavigationCounts($kiosk_id);
				$this->set(compact('managers', 'admins', 'allUsers', 'message', 'salesRep'));
				$this->set('userID',$userID);
				$this->set('kiosk_id',$kiosk_id);
				$this->set('kioskname',$kioskname);
		}
		
		public function groupMessageDetails($createdDate = '', $subject = ''){
				$loggedInUser = $this->request->session()->read('Auth.User.id'); 
				$warehouseKioskId = Configure::read('WAREHOUSE_KIOSK_ID');
                 $kiosks_query = $this->Kiosks->find('list',[
                                                'keyField' => 'id',
                                                'valueField' => 'name',
                                             ]);
                if(!empty($kiosks_query)){
                    $kioskname = $kiosks_query->toArray();
                }else{
                    $kioskname = array();
                }
			
				$userID = $this->request->session()->read('Auth.User.id'); 
				//echo $userID;
                $user_query = $this->Users->find('list',[
                                                'keyField' => 'id',
                                                'valueField' => 'username',
                                             ]);
                if(!empty($user_query)){
                    $users = $user_query->toArray();
                }else{
                    $users = array();
                }
				
				$kiosk_id = $this->request->session()->read('kiosk_id');
				if(empty($kiosk_id)){
					$kiosk_id = $warehouseKioskId;
				}
				//find message and kiosk and admin
                  $this->paginate = [
                                
                                    'conditions' => [
                                                        'AND' => ['date(Messages.created)' => date("Y-m-d",$createdDate),
																			 "Messages.subject like " => "%$subject%",
																			 //commented on Juy 27, 2016
                                                                 ],
                                                        'OR' => [
                                                                    [
                                                                        'AND' => ['Messages.sender_id' => $kiosk_id],
                                                                        ['Messages.sent_by IS NULL']
                                                                    ],
                                                              [
                                                                'AND' => ['Messages.sent_by' => $loggedInUser],
                                                                ['Messages.sender_id IS NULL'],
                                                              ]
                                                            ],
								      //'Message.sender_id'=>$kiosk_id,
                                                        'Messages.sender_status' =>2
                                                    ],
                                    'limit' => ROWS_PER_PAGE,
                                    'order' => ['Messages.id desc'],
                                ];
				//pr($this->paginate);die; 
                $messages_query = $this->paginate($this->Messages);
				if(!empty($messages_query)){
                    $message = $messages_query->toArray();
                }else{
                    $message = array();
                }
				$sentTo = array();
				// pr($message);
				foreach($message as $sngmessage){
					 $sentTo[] =$sngmessage->receiver_id;
				}
				$sentArr = array();
				if( count($sentTo) >= 1){
					$inStr = "'".implode("','",$sentTo)."'";
					$query = "SELECT * FROM kiosks where id in ($inStr);";
                    $conn = ConnectionManager::get('default');
                    $stmt = $conn->execute($query);
                    $sentToRS = $stmt ->fetchAll('assoc');
					//$sentToRS = $this->Kiosk->query($query);
        			foreach($sentToRS as $sngUser){
						$sentArr[$sngUser['id']] =  $sngUser['name'];
					}
				}
				//$message = $this->Paginator->paginate('Message');

				//action list for kiosk and admin
				$this->getNavigationCounts($kiosk_id);
				$this->set('users',$users);
				$this->set('message',$message);
				$this->set('sentArr',$sentArr);
				$this->set('kiosk_id',$kiosk_id);
				$this->set('userID',$userID);
				$this->set('kioskname',$kioskname);
				$this->render('index');
		}

    public function productDemand() {
		    //$this->Message->query('TRUNCATE messages');
		    $userID =  $this->request->session()->read('Auth.User.id');
			$user_query = $this->Users->find('list',[
                                                'keyField' => 'id',
                                                'valueField' => 'username',
                                             ]);
            if(!empty($user_query)){
                $users = $user_query->toArray();
            }else{
                $users = array();
            }
             $managerEmails_query = $this->Users->find('list',[
                                                'keyField' => 'id',
                                                'valueField' => 'email',
                                                'conditions' => ['Users.group_id' => MANAGERS]
                                             ]);
            if(!empty($managerEmails_query)){
                $managerEmails = $managerEmails_query->toArray();
            }else{
                $managerEmails = array();
            }
			$adminEmails_query = $this->Users->find('list',[
                                                'keyField' => 'id',
                                                'valueField' => 'email',
                                                'conditions' => ['Users.group_id' => ADMINISTRATORS]
                                             ]);
            if(!empty($adminEmails_query)){
                $adminEmails = $adminEmails_query->toArray();
            }else{
                $adminEmails = array();
            } 
			$warehouseKioskId = Configure::read('WAREHOUSE_KIOSK_ID');
		    $kiosk_id = $this->request->session()->read('kiosk_id');
		    if(empty($kiosk_id)){$kiosk_id = $warehouseKioskId;}
              $kiosks_query = $this->Kiosks->find('list',[
                                                'keyField' => 'id',
                                                'valueField' => 'name',
                                                'order'=>'Kiosks.name asc'
                                             ]);
            if(!empty($kiosks_query)){
                $kioskname = $kiosks_query->toArray();
            }else{
                $kioskname = array();
            }
		   if ($this->request->is('post')) {
          //  pr($this->request->data);
            
				if($this->request->session()->read('Auth.User.group_id') != KIOSK_USERS){
                   // echo "ff";die;
                      $this->Flash->success(__('You are not authorized to demand new products!'));
						return $this->redirect(array('controller' => 'home', 'action' => 'dashboard'));
						die;
				}
				if(!empty($this->setting['product_request_users'])){
						if(is_array(explode('|',$this->setting['product_request_users']))){
								$this->request->data['sent_to_id'] = explode('|',$this->setting['product_request_users']);
						}else{
                             $this->Flash->success(__('Message could not be sent, please contact Administrator!'));
								
								//comes in case the attribute_name product_request_users is not properly set
								return $this->redirect(array('action' => 'product_demand'));
								die;
						}
				}else{
                     $this->Flash->success(__('Message not sent, please contact Administrator!'));
					//comes in case the attribute_name product_request_users is empty
						return $this->redirect(array('action' => 'product_demand'));
						die;
				}
				$counter = 0;
				$errorArr = array();
                
				$this->request->data['user_id'] = $userID;//this is common for both cases
				$sent_to_ids = array();
				//here check if sent to id is not included then return to message
				$send_by_email = Configure::read('send_by_email');
				if(isset($this->request->data['sent_to_id'])){
                   
						//saving user data for personal message in case of if
						$sent_to_ids = $this->request->data['sent_to_id'];
						$this->request->data['sent_by'] = $userID;

						//case if all is not selected in all of the dropdown
						if(count($sent_to_ids) && is_array($sent_to_ids)){
								foreach($sent_to_ids as $s => $sent_to_id){
									$this->request->data['sent_to_id'] =  $sent_to_id;
									 $message = $this->Messages->newEntity();
                                    $message = $this->Messages->patchEntity($message, $this->request->data,['validate' => false]);
                                    if ($this->Messages->save($message,['validate' => false])) {
								$emailSender = Configure::read('EMAIL_SENDER');
                                      	//checking if id belongs to the manager, if yes, will send email message to the real email address as well
										if(array_key_exists($sent_to_id,$managerEmails)){
                                           
												//code for sending emails to the email ids of the managers
												//$managerEmails[$m]
                                                $Email = new Email();
                                                $Email->config('default');
                                              // pr($this->request->data);die;
                                                $Email->viewVars(array('content' => $this->request->data));
                                                //$Email->config(array('from' => 'me@example.org', 'transport' => 'MyCustom'));
                                                //$Email = new CakeEmail(array('from' => 'me@example.org', 'transport' => 'MyCustom'));
                                                 $emailTo = $managerEmails[$sent_to_id];
                                                $Email->template('email_message');
                                                $Email->emailFormat('both');
                                                $Email->to($emailTo);
												$Email->transport(TRANSPORT);
										$Email->from([$send_by_email => $emailSender]);
                                                //$Email->sender($this->fromemail);
                                                //$Email->sender("sales@oceanstead.co.uk");
                                                $Email->subject($this->request->data['subject']);
                                                $Email->send();
												
										}
										
										if(array_key_exists($sent_to_id,$adminEmails)){
												//code for sending emails to the email ids of the managers
												//$managerEmails[$m]
                                                 $Email = new Email();
                                                $Email->config('default');
                                               // pr($this->request->data);die;
                                                $Email->viewVars(array('content' => $this->request->data));
                                                //$Email->config(array('from' => 'me@example.org', 'transport' => 'MyCustom'));
                                                //$Email = new CakeEmail(array('from' => 'me@example.org', 'transport' => 'MyCustom'));
                                                $emailTo =  $adminEmails[$sent_to_id];
                                                $Email->template('email_message');
                                                $Email->emailFormat('both');
                                                $Email->to($emailTo);
												$Email->transport(TRANSPORT);
											 $Email->from([$send_by_email => $emailSender]);
                                                //$Email->sender($this->fromemail);
                                                //$Email->sender("sales@oceanstead.co.uk");
                                                $Email->subject($this->request->data['subject']);
                                                $Email->send();
												
										}
										
										$counter++;
										$sender = $users[$userID];
                                       // pr($this->request->data);die;
										 $subject = $this->request->data['subject'];
										 $pushStr = "Email<br/>From: $sender Subject: $subject";
										 $this->Pusher->email_user_push($pushStr,$sent_to_id);//created in components
									}
								}
						}
				}else{
						//will not save the data and will flash error
						$errorArr[] = "Please select email addresses from dropdown";
				}
	
	
				if($counter>0){
						$settingTempEmails = explode(",",trim($this->setting['product_request_email']));
						$settingEmails = array();
						foreach($settingTempEmails as $settingTempEmail){
								if(!empty($settingTempEmail)){
										$settingEmails[] = trim($settingTempEmail);
								}
						}
						$emailSender = Configure::read('EMAIL_SENDER');
						if(is_array($settingEmails)){
								foreach($settingEmails as $ks => $settingEmail){
										//code for sending emails to the email ids of the managers
										//$managerEmails[$m]
                                          $Email = new Email();
                                                $Email->config('default');
                                               // pr($this->request->data);die;
                                                $Email->viewVars(array('content' => $this->request->data));
                                                //$Email->config(array('from' => 'me@example.org', 'transport' => 'MyCustom'));
                                                //$Email = new CakeEmail(array('from' => 'me@example.org', 'transport' => 'MyCustom'));
                                                $emailTo = $settingEmail;
                                                $Email->template('email_message');
                                                $Email->emailFormat('both');
                                                $Email->to($emailTo);
                                                //$Email->sender($this->fromemail);
												$Email->transport(TRANSPORT);
											 $Email->from([$send_by_email => $emailSender]);
                                                $Email->sender($send_by_email);
                                                $Email->subject($this->request->data['subject']);
                                                $Email->send();
										
								}
						}
                          $this->Flash->success(__('Message have been sent.'));
						return $this->redirect(array('controller' => 'home', 'action' => 'dashboard'));
				}else{
					$errorStr = "The Message could not be saved. Please, try again.";
	
					if(count($errorArr) && is_array($errorArr)){
							$errorStr = "The Message could not be saved for the following reasons:<br/>";
							$errorStr.= implode("<br/>",$errorArr);
					}
                     $this->Flash->success(__($errorStr));
				}
		    }
			
		    $this->getNavigationCounts($kiosk_id);
		    $this->set(compact('users'));
		    $this->set('userID',$userID);
		    $this->set('kiosk_id',$kiosk_id);
		    $this->set('kioskname',$kioskname);
		}
		
    public function delete($id = null)
    {
        $this->request->allowMethod(['post', 'delete']);
        $message = $this->Messages->get($id);
        if ($this->Messages->delete($message)) {
            $this->Flash->success(__('The message has been deleted.'));
        } else {
            $this->Flash->error(__('The message could not be deleted. Please, try again.'));
        }

        return $this->redirect(['action' => 'index']);
    }
    
    public function auth1()
    {
        $this->autoRender = false;
     
        //custom authentication logic
        $channelName =   $this->request->data('channel_name');
          $socketId =   $this->request->data('socket_id');
            $result = $this->pusher->socket_auth($channelName, $socketId);
        $this->response->type('json');
        $this->response->body($result);
    }
}
