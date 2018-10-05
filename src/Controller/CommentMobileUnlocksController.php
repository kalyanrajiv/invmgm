<?php
namespace App\Controller;

use App\Controller\AppController;
use Cake\Event\Event;
use Cake\Core\Configure;
use Cake\Core\Configure\Engine\PhpConfig;
use Cake\Network\Request;
use Cake\ORM\TableRegistry;
use Cake\Mailer\Email;
use Cake\I18n;
use Cake\Datasource\ConnectionManager;


class CommentMobileUnlocksController extends AppController {
	//public $components = array('Paginator','Pusher');
	//public $uses = array('CommentMobileUnlock','MobileUnlockLog');
	
	
	 public function initialize(){
        parent::initialize();
		$this->loadComponent('Pusher');
     }
	public function index() {
		$this->set('commentMobileUnlocks', $this->paginate());
	}

	public function view($id = null) {
		if (!$this->CommentMobileUnlocks->exists($id)) {
			throw new NotFoundException(__('Invalid comment mobile unlock'));
		}
		$options = array('conditions' => array('CommentMobileUnlocks.id' => $id));
		$res_query = $this->CommentMobileUnlocks->find('all', $options);
		$res_query = $res_query->hydrate(false);
		if(!empty($res_query)){
			$res = $res_query->first();
		}else{
			$res = array();
		}
		$this->set('commentMobileUnlock', $res);
	}

	public function add() {
		//pr($this->request);die;
		$kiosk_id = $this->request->Session()->read('kiosk_id');
		if($this->request->session()->read('group_id') == ADMINISTRATORS || $this->request->session()->read('group_id') == MANAGERS) $kiosk_id = 10000;
		$mobile_unlock_id = $this->request->params['pass'][0]; //rasa
		$users = $this->CommentMobileUnlocks->Users->find('list');
		$mobileUnlockData_query = $this->CommentMobileUnlocks->MobileUnlocks->find('all',array('fields' => array('id','MobileUnlocks.customer_fname', 'MobileUnlocks.customer_lname', 'MobileUnlocks.customer_email'), 'conditions' => array('MobileUnlocks.id' => $mobile_unlock_id)));
		$mobileUnlockData_query = $mobileUnlockData_query->hydrate(false);
		if(!empty($mobileUnlockData_query)){
			$mobileUnlockData = $mobileUnlockData_query->toArray();
		}else{
			$mobileUnlockData = array();
		}
		
		if(!empty($mobileUnlockData)){
			 $name = $mobileUnlockData[0]['customer_fname']." ".$mobileUnlockData[0]['customer_lname']."(".$mobileUnlockData[0]['customer_email'].")";
		}else{
			$name = "";
		}
		foreach($mobileUnlockData as $key => $sngItem){
			$mobileUnlocks[$sngItem['id']] = $name;
		}
		if (empty($mobile_unlock_id) || !array_key_exists($mobile_unlock_id,$mobileUnlocks)) { //rasa
			$this->Flash->error(__('Mobile Unlock id is either missing or Invalid Mobile Unlock id'));
			return $this->redirect(array('controller' => 'mobile_unlocks', 'action' => 'edit', $mobile_unlock_id));
		}
		
		if ($this->request->is('post')) {
			$user_id = $this->request->session()->read('Auth.User.id');	//rasa	
			$this->request->data['user_id'] = $user_id ;	
			$CommentMobileUnlocksEntity = $this->CommentMobileUnlocks->newEntity($this->request->data,['validate' => false]);
			$CommentMobileUnlocksEntity = $this->CommentMobileUnlocks->patchEntity($CommentMobileUnlocksEntity,$this->request->data,['validate' => false]);
			if ($this->CommentMobileUnlocks->save($CommentMobileUnlocksEntity)) {
				//*****code for sending pusher pop up to booking kiosk, admin and unlock tech
				//for finding the kiosk id of the booking kiosk
				$kioskData_query = $this->CommentMobileUnlocks->MobileUnlocks->find('list', array('conditions' => array('MobileUnlocks.id' => $mobile_unlock_id), 'keyField' => 'id','valueField' => 'kiosk_id'));
				$kioskData_query = $kioskData_query->hydrate(false);
				if(!empty($kioskData_query)){
					$kioskData = $kioskData_query->toArray();
				}else{
					$kioskData = array();
				}
				$bookingKiosk = $kioskData[$mobile_unlock_id];
				//admin,unlock tech,kiosk
				$comment = $this->request->data['brief_history'];
				$pushStr = "Mobile Unlock Comment<br/>Id: $mobile_unlock_id Comment: $comment";
				 $this->Pusher->email_kiosk_push($pushStr,$bookingKiosk);//for sending pop up to booking kiosk, created in components
				 $this->Pusher->group_popup($pushStr,ADMINISTRATORS);//for sending pop up to admin
				 $this->Pusher->group_popup($pushStr,UNLOCK_TECHNICIANS);//for sending pop up to unlock technicians
				//till here*******
				$mobileUnlckData_query = $this->CommentMobileUnlocks->MobileUnlocks->find('list',array(
																								 'keyField' => 'id',
																								 'valueField' => 'status',
																								 ));
				$mobileUnlckData_query = $mobileUnlckData_query->hydrate(false);
				if(!empty($mobileUnlckData_query)){
					$mobileUnlckData =  $mobileUnlckData_query->toArray();
				}else{
					$mobileUnlckData = array();
				}
				foreach($mobileUnlckData as $key => $status){
					if($key==$mobile_unlock_id){
						$unlock_status = $status;
					}
				}				
				
				$mobileUnlockLogsData = array(
					'user_id' => $this->request->Session()->read('Auth.User.id'),
					'kiosk_id' => $kiosk_id,
					'mobile_unlock_id' => $mobile_unlock_id,					
					'comments' => $CommentMobileUnlocksEntity->id,					
					'unlock_status' => '-1'
					);
				$this->loadModel('MobileUnlockLogs');
				$MobileUnlockLogsEntity = $this->MobileUnlockLogs->newEntity($mobileUnlockLogsData);
				$MobileUnlockLogsEntity = $this->MobileUnlockLogs->patchEntity($MobileUnlockLogsEntity,$mobileUnlockLogsData);
				$this->MobileUnlockLogs->save($MobileUnlockLogsEntity);
				
				$this->Flash->error(__('The comment mobile unlock has been saved.'));
				return $this->redirect(array('controller' => 'mobile_unlocks','action' => 'edit',$mobile_unlock_id));
			} else {
				$this->Flash->error(__('The comment mobile unlock could not be saved. Please, try again.'));
			}
		}
		
		$this->set(compact('users', 'mobileUnlocks','mobile_unlock_id'));
	}

	public function edit($id = null) {
		$kiosk_id = $this->request->Session()->read('kiosk_id');
		if (!$this->CommentMobileUnlocks->exists($id)) {
			throw new NotFoundException(__('Invalid comment mobile unlock'));
		}
		if ($this->request->is(array('post', 'put'))) {
			$user_id = $this->request->session()->read('Auth.User.id');	//rasa	
			$this->request->data['user_id'] = $user_id ;
			$mobile_unlock_id = $this->request->data['mobile_unlock_id'];
			$CommentMobileUnlocksEntity = $this->CommentMobileUnlocks->get($id);
			$CommentMobileUnlocksEntity = $this->CommentMobileUnlocks->patchEntity($CommentMobileUnlocksEntity,$this->request->data);
			if ($this->CommentMobileUnlocks->save($CommentMobileUnlocksEntity)) {
				//*****code for sending pusher pop up to booking kiosk, admin and unlock tech
				//for finding the kiosk id of the booking kiosk
				$kioskData_query = $this->CommentMobileUnlocks->MobileUnlocks->find('list', array('conditions' => array('MobileUnlocks.id' => $mobile_unlock_id),
																							'keyField' => 'id',
																							'valueField' => 'kiosk_id'
																							));
				$kioskData_query = $kioskData_query->hydrate(false);
				if(!empty($kioskData_query)){
					$kioskData = $kioskData_query->toArray();
				}else{
					$kioskData = array();
				}
				$bookingKiosk = $kioskData[$mobile_unlock_id];
				//admin,unlock tech,kiosk
				$comment = $this->request->data['brief_history'];
				$pushStr = "Mobile Unlock Comment(updated)<br/>Id: $mobile_unlock_id Comment: $comment";
				 $this->Pusher->email_kiosk_push($pushStr,$bookingKiosk);//for sending pop up to booking kiosk, created in components
				 $this->Pusher->group_popup($pushStr,ADMINISTRATORS);//for sending pop up to admin
				 $this->Pusher->group_popup($pushStr,UNLOCK_TECHNICIANS);//for sending pop up to unlock technicians
				//till here*******
				
				$mobileUnlckData_query = $this->CommentMobileUnlocks->MobileUnlocks->find('list',array('keyField' => 'id',
																								 'valueField' => 'status',
																								 ));
				$mobileUnlckData_query = $mobileUnlckData_query->hydrate(false);
				if(!empty($mobileUnlckData_query)){
					$mobileUnlckData = $mobileUnlckData_query->toArray();
				}else{
					$mobileUnlckData = array();
				}
				foreach($mobileUnlckData as $key => $status){
					if($key==$mobile_unlock_id){
						$unlock_status = $status;
					}
				}				
				
				$mobileUnlockLogsData = array(
					'user_id' => $this->request->Session()->read('Auth.User.id'),
					'kiosk_id' => $kiosk_id,
					'mobile_unlock_id' => $mobile_unlock_id,					
					'comments' => $CommentMobileUnlocksEntity->id,					
					'unlock_status' => ''					
					);
				$this->loadModel("MobileUnlockLogs");
				$MobileUnlockLogsEntity = $this->MobileUnlockLogs->newEntity($mobileUnlockLogsData);
				$MobileUnlockLogsEntity = $this->MobileUnlockLogs->patchEntity($MobileUnlockLogsEntity,$mobileUnlockLogsData);
				$this->MobileUnlockLogs->save($MobileUnlockLogsEntity);
				
				$this->Flash->success(__('The comment mobile unlock has been saved.'));
				return $this->redirect(array('controller' => 'mobile_unlocks','action' => 'edit',$this->request->data['mobile_unlock_id']));
			} else {
				$this->Flash->error(__('The comment mobile unlock could not be saved. Please, try again.'));
			}
		} else {
			$user_id = $this->request->session()->read('Auth.User.id'); //rasa
			$options = array('conditions' => array('CommentMobileUnlocks.id'=> $id));
			$res_query = $this->CommentMobileUnlocks->find('all', $options);
			$res_query = $res_query->hydrate(false);
			if(!empty($res_query)){
				$res = $res_query->first();
			}else{
				$res = array();
			}
			$this->request->data = $res;
			$timeAdded = strtotime($this->request->data['created']);
			$currentTime = strtotime(date("Y-m-d H:i:s"));
			if($this->request->session()->read('group_id') == KIOSK_USERS){
				if($currentTime-$timeAdded>600){
					$this->Flash->error(__('Comment can only be edited within 10 minutes from its posted time'));
					return $this->redirect(array('controller' => 'mobile_unlocks','action' => 'edit',$this->request->data['CommentMobileUnlock']['mobile_unlock_id']));
				}
			}
			if($this->request->data['user_id'] != $user_id){
				//pr($this->request);die;
				$this->Flash->error(__('You can\'t edit comments posted by others'));
				return $this->redirect(array('controller' => 'mobile_unlocks','action' => 'edit',$this->request->data['mobile_unlock_id']));
			}
		}
		$users_query = $this->CommentMobileUnlocks->Users->find('list');
		$users_query = $users_query->hydrate(false);
		if(!empty($users_query)){
			$users = $users_query->toArray();
		}else{
			$users = array();
		}
		$mobileUnlocks_query = $this->CommentMobileUnlocks->MobileUnlocks->find('list');
		$mobileUnlocks_query = $mobileUnlocks_query->hydrate(false);
		if(!empty($mobileUnlocks_query)){
			$mobileUnlocks  =  $mobileUnlocks_query->toArray();
		}else{
			$mobileUnlocks = array();
		}
		$this->set(compact('users', 'mobileUnlocks'));
	}

	public function delete($id = null) {
		
		if (!$this->CommentMobileUnlock->exists()) {
			throw new NotFoundException(__('Invalid comment mobile unlock'));
		}else{
			$CommentMobileUnlocksEntity = $this->CommentMobileUnlocks->get($id);
		}
		$this->request->allowMethod('post', 'delete');
		if (false) {//Do not allow user to delete comments
			//$this->CommentMobileUnlock->delete()
			$this->Session->setFlash(__('The comment mobile unlock has been deleted.'));
		} else {
			$this->Session->setFlash(__('The comment mobile unlock could not be deleted. Please, try again.'));
		}
		return $this->redirect(array('action' => 'index'));
	}
}
