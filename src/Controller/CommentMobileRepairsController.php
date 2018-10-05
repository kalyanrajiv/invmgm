<?php
namespace App\Controller;

use Cake\Controller\Controller;
use Cake\Event\Event;
use Cake\Network\Request;
use Cake\ORM\TableRegistry;
use Cake\Mailer\Email;
use Cake\Routing\Router;
use Cake\Utility\Text;
use Cake\Core\Configure;
use Cake\Core\Configure\Engine\PhpConfig;
use Cake\Datasource\ConnectionManager;
use Cake\Database\Schema\Collection;
use Cake\Database\Schema\TableSchema;

class CommentMobileRepairsController extends AppController
{
    // var $components = array('Pusher');
     public function initialize(){
        parent::initialize();
		$this->loadComponent('Pusher');
     }
    public function index()
    {
        $this->paginate = [
            'contain' => ['Users', 'MobileRepairs']
        ];
        $commentMobileRepairs = $this->paginate($this->CommentMobileRepairs);

        $this->set(compact('commentMobileRepairs'));
        $this->set('_serialize', ['commentMobileRepairs']);
    }

    /**
     * View method
     *
     * @param string|null $id Comment Mobile Repair id.
     * @return \Cake\Network\Response|null
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view($id = null)
    {
        $commentMobileRepair = $this->CommentMobileRepairs->get($id, [
            'contain' => ['Users', 'MobileRepairs']
        ]);

        $this->set('commentMobileRepair', $commentMobileRepair);
        $this->set('_serialize', ['commentMobileRepair']);
    }

    /**
     * Add method
     *
     * @return \Cake\Network\Response|null Redirects on successful add, renders view otherwise.
     */
    /*public function add()
    {
        $commentMobileRepair = $this->CommentMobileRepairs->newEntity();
        if ($this->request->is('post')) {
            $commentMobileRepair = $this->CommentMobileRepairs->patchEntity($commentMobileRepair, $this->request->data);
            if ($this->CommentMobileRepairs->save($commentMobileRepair)) {
                $this->Flash->success(__('The comment mobile repair has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The comment mobile repair could not be saved. Please, try again.'));
        }
        $users = $this->CommentMobileRepairs->Users->find('list', ['limit' => 200]);
        $mobileRepairs = $this->CommentMobileRepairs->MobileRepairs->find('list', ['limit' => 200]);
        $this->set(compact('commentMobileRepair', 'users', 'mobileRepairs'));
        $this->set('_serialize', ['commentMobileRepair']);
    }*/
	
	
    /**
     * Edit method
     *
     * @param string|null $id Comment Mobile Repair id.
     * @return \Cake\Network\Response|null Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Network\Exception\NotFoundException When record not found.
     */

    /**
     * Delete method
     *
     * @param string|null $id Comment Mobile Repair id.
     * @return \Cake\Network\Response|null Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete($id = null)
    {
        $this->request->allowMethod(['post', 'delete']);
        $commentMobileRepair = $this->CommentMobileRepairs->get($id);
        if ($this->CommentMobileRepairs->delete($commentMobileRepair)) {
            $this->Flash->success(__('The comment mobile repair has been deleted.'));
        } else {
            $this->Flash->error(__('The comment mobile repair could not be deleted. Please, try again.'));
        }

        return $this->redirect(['action' => 'index']);
    }
	public function add(){
       // $this->loadComponent('PusherComponent');
		$this->loadModel('MobileRepairLogs');
		//$kiosk_id = $this->Session->read('kiosk_id');
		$kiosk_id = $this->request->session()->read('kiosk_id');
		if($this->request->session()->read('Auth.User.group_id') == ADMINISTRATORS || $this->request->session()->read('Auth.User.group_id') == FRANCHISE_OWNER || $this->request->session()->read('Auth.User.group_id') == MANAGERS) $kiosk_id = 10000;
		//pr($this->request);die;
		$mobile_repair_id = $this->request->query['id']; //rasa		
		$users_query = $this->CommentMobileRepairs->Users->find('list');
		$users_query = $users_query->hydrate(false);
		$users = $users_query->toArray();
		//pr($users);
	
		//$mobileRepairData = $this->CommentMobileRepairs->MobileRepairs->find('all',array('fields' => array('id','status','CONCAT(MobileRepair.customer_fname, " ", MobileRepair.customer_lname, " (", MobileRepair.customer_email, ")") as name'), 'conditions' => array('MobileRepair.id' => $mobile_repair_id)));				
		
		$mobileRepairData_query = $this->CommentMobileRepairs->MobileRepairs->find('all',[
									'fields' => ['id','status','MobileRepairs.customer_fname', 'MobileRepairs.customer_lname', 'MobileRepairs.customer_email'],
									'conditions' => ['MobileRepairs.id' => $mobile_repair_id]
									]);				
		$mobileRepairData_query = $mobileRepairData_query->hydrate(false);
		if(!empty($mobileRepairData_query)){
			$mobileRepairData = $mobileRepairData_query->toArray();
		}else{
			$mobileRepairData = array();
		}
		if(!empty($mobileRepairData)){
			 $name = $mobileRepairData[0]['customer_fname']." ".$mobileRepairData[0]['customer_lname']."(".$mobileRepairData[0]['customer_email'].")";
		}else{
			$name = "";
		}
		
		foreach($mobileRepairData as $key => $sngItem){
			$mobileRepairs[$sngItem['id']] = $name;//$sngItem[0]['name'];
		}
		if (empty($mobile_repair_id) || !array_key_exists($mobile_repair_id,$mobileRepairs)) { //rasa
			$this->Session->setFlash(__('Mobile Repair id is either missing or Invalid Mobile Repair id'));
			return $this->redirect(array('controller' => 'mobile_repairs', 'action' => 'edit', $mobile_repair_id));
		}
		
		if ($this->request->is('post')) {
			$user_id = $this->request->session()->read('Auth.User.id');	
			//pr($this->request);
			$this->request->data['user_id'] = $user_id ;			
			$entity_N = $this->CommentMobileRepairs->newEntity($this->request->data,['validate' => false ]);
			$entity_P = $this->CommentMobileRepairs->patchEntity($entity_N,$this->request->data,['validate' => false]);
			
			if ($this->CommentMobileRepairs->save($entity_P)) {
				//*****code for sending pusher pop up to booking kiosk, admin and repair tech
				//for finding the kiosk id of the booking kiosk
				
				$kioskData_query = $this->CommentMobileRepairs->MobileRepairs->find('list', [
										'conditions' => ['MobileRepairs.id' => $mobile_repair_id], 
										'keyField' => 'id',
										'valueField' => 'kiosk_id'
										]);
				$kioskData_query = $kioskData_query->hydrate(false);
				$kioskData = $kioskData_query->toArray();
				//pr($kioskData); die;
				
				$bookingKiosk = $kioskData[$mobile_repair_id];
               
				//admin,unlock tech,kiosk
				//$comment = $this->request->data['CommentMobileRepair']['brief_history'];
				$comment = $this->request->data['brief_history'];
				$pushStr = "Mobile Repair Comment<br/>Id: $mobile_repair_id Comment: $comment";
				  $this->Pusher->email_kiosk_push($pushStr,$bookingKiosk);//for sending pop up to booking kiosk, created in components
                 $this->Pusher->group_popup($pushStr,ADMINISTRATORS);//for sending pop up to admin
                //echo "kk";die;
                 $this->Pusher->group_popup($pushStr,REPAIR_TECHNICIANS); //for sending pop up to unlock technicians
				//till here*******
				
				$mobileReprData_query = $this->CommentMobileRepairs->MobileRepairs->find('list',[
														'keyField' => 'id',
														'valueField' => 'status'
												]);		
				$mobileReprData_query = $mobileReprData_query->hydrate(false);
				$mobileReprData = $mobileReprData_query->toArray();
																
				foreach($mobileReprData as $key => $status){
					if($key==$mobile_repair_id){
						$repair_status = $status;
					}
				}
				
				$mobileRepairLogsData = [
					
					//'user_id' => $this->Session->read('Auth.User.id'),
					'user_id' => $this->request->session()->read('Auth.User.id'),
					
					'kiosk_id' => $kiosk_id,
					'mobile_repair_id' => $mobile_repair_id,					
					'comments' => $entity_P->id,
					'repair_status' => '-1'
					];
				
				/*$this->MobileRepairLog->set($mobileRepairLogsData);
				if ($this->MobileRepairLog->validates()) {
					$this->MobileRepairLog->save($mobileRepairLogsData);
				}else{
					$errors = $this->MobileRepairLog->validationErrors;					
				}*/
				$entity_N = $this->MobileRepairLogs->newEntity($mobileRepairLogsData,['validate' => false]);
				$entity_N1 = $this->MobileRepairLogs->patchEntity($entity_N,$mobileRepairLogsData,['validate' => false]);
				$this->MobileRepairLogs->save($entity_N1);
				$this->Flash->success('The comment mobile repair has been saved.');
				return $this->redirect(array('controller' => 'mobile_repairs','action' => 'edit',$mobile_repair_id));
			} else {
				$this->Flash->success('The comment mobile repair could not be saved. Please, try again.');
			}
		}
		
		$this->set(compact('users', 'mobileRepairs','mobile_repair_id','repair_status'));
	}
	public function edit($id = null) {
        
	$this->loadModel('MobileRepairLogs');
		$kiosk_id = $this->request->Session()->read('kiosk_id');		
		if (!$this->CommentMobileRepairs->exists($id)) {
			throw new NotFoundException(__('Invalid comment mobile repair'));
		}
		if ($this->request->is(array('post', 'put'))) {
            //echo "gg";die;
			$user_id = $this->request->session()->read('Auth.User.id');	//rasa	
			$this->request->data['user_id'] = $user_id ;
			$mobile_repair_id = $this->request->data['mobile_repair_id'];
			$CommentMobileRepairsEntity = $this->CommentMobileRepairs->get($id);
			$CommentMobileRepairsEntity = $this->CommentMobileRepairs->patchEntity($CommentMobileRepairsEntity,$this->request->data,['validate' => false]);
			if ($this->CommentMobileRepairs->save($CommentMobileRepairsEntity)) {
				//*****code for sending pusher pop up to booking kiosk, admin and repair tech
				//for finding the kiosk id of the booking kiosk
				$kioskData_query = $this->CommentMobileRepairs->MobileRepairs->find('list', [
										'conditions' => ['MobileRepairs.id' => $mobile_repair_id], 
														'keyField' => 'id',
														'valueField' => 'kiosk_id'
											]);
				$kioskData_query = $kioskData_query->hydrate(false);
				$kioskData = $kioskData_query->toArray();
				$bookingKiosk = $kioskData[$mobile_repair_id];
               // $bookingKiosks[] = $bookingKiosk;
				//admin,unlock tech,kiosk
               // pr($bookingKiosks);die;
				$comment = $this->request->data['brief_history'];
				$pushStr = "Mobile Repair Comment(updated)<br/>Id: $mobile_repair_id Comment: $comment";
              //  echo "jj";die;
				 $this->Pusher->email_kiosk_push($pushStr,$bookingKiosk);//for sending pop up to booking kiosk, created in components
				  // $authGroups = array('1'=> 'ADMINISTRATORS','7'=>'REPAIR_TECHNICIANS');  
                 // $this->Pusher->group_popup($pushStr,$authGroups);
                $this->Pusher->group_popup($pushStr,ADMINISTRATORS);//for sending pop up to admin
                //echo "kk";die;
                 $this->Pusher->group_popup($pushStr,REPAIR_TECHNICIANS); //for sending pop up to unlock technicians
				//till here*******
				
				$mobileRepairData_query = $this->CommentMobileRepairs->MobileRepairs->find('list',[
														'keyField' => 'id',
														'valueField' => 'kiosk_id'
														]);
				$mobileRepairData_query = $mobileRepairData_query->hydrate(false);
				$mobileRepairData = $mobileRepairData_query->toArray();
														
				foreach($mobileRepairData as $key => $status){
					if($key==$mobile_repair_id){
						$repair_status = $status;
					}
				}				
					
				$mobileRepairLogsData = array(
					'user_id' => $this->request->Session()->read('Auth.User.id'),
					'kiosk_id' => $kiosk_id,
					'mobile_repair_id' => $mobile_repair_id,					
					'comments' => $CommentMobileRepairsEntity->id,
					'repair_status' => $repair_status
					);
				
				$MobileRepairLogsEntity = $this->MobileRepairLogs->newEntity($mobileRepairLogsData,['validate'=>false]);
				$MobileRepairLogsEntity = $this->MobileRepairLogs->patchEntity($MobileRepairLogsEntity,$mobileRepairLogsData,['validate'=>false]);
				$this->MobileRepairLogs->save($MobileRepairLogsEntity);
				
				$this->Flash->success('The comment mobile repair has been saved.');
				return $this->redirect(array('controller' => 'mobile_repairs','action' => 'edit',$this->request->data['mobile_repair_id']));
			} else {
				$this->Flash->success('The comment mobile repair could not be saved. Please, try again.');
			}
		} else {
			$user_id = $this->request->session()->read('Auth.User.id');; //rasa
			$options = array('conditions' => array('CommentMobileRepairs.id' => $id));
			$data_query = $this->CommentMobileRepairs->find('all', $options);
			$data_query = $data_query->hydrate(false);
			if(!empty($data_query)){
				$data = $data_query->first();
			}else{
				$data = [];
			}
			$this->request->data = $data;
			//pr($this->request->data);die;
			if($this->request->data['user_id']!= $user_id){
				$this->Flash->success('You can\'t edit comments posted by others');
                //pr($this->request->data);die;
				return $this->redirect(array('controller' => 'mobile_repairs','action' => 'edit',$this->request->data['id']));
			}
		}
		$users_query = $this->CommentMobileRepairs->Users->find('list');
		$users_query = $users_query->hydrate(false);
		$users = $users_query->toArray();
		$mobileRepairs_query = $this->CommentMobileRepairs->MobileRepairs->find('list');
		$mobileRepairs_query = $mobileRepairs_query->hydrate(false);
		$mobileRepairs = $mobileRepairs_query->toArray();
		$this->set(compact('users', 'mobileRepairs'));
	}
}
