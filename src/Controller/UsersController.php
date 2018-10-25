<?php
namespace App\Controller;


use Cake\Core\Configure;
use Cake\Core\Configure\Engine\PhpConfig;
use Cake\Utility\Security;
use Cake\Utility\Text;
use Cake\Routing\Router;
use Cake\Mailer\Email;
use Cake\Auth\DefaultPasswordHasher;
use Cake\Datasource\ConnectionManager;
use App\Controller\AppController;
class UsersController extends AppController
{

     public $helpers = [
         'Paginator' => ['templates' => 'paginatortemplates']
         ];
       public function initialize()
        {
            parent::initialize();
			$this->Auth->allow('forgetPassword');
			$this->Auth->allow('reset');
             $active = Configure::read('active');
            $visaOptions = Configure::read('visa_type');// code added by Inder
            $countryOptions = Configure::read('uk_non_uk');
            //$this->set('active', $status);
            $this->set(compact('active'));
            $this->set(compact('visaOptions','countryOptions'));
			$this->loadModel('Profiles');
			$this->loadModel('Attachments');
            $this->loadModel('UserAttendances');
            $this->loadmodel('KioskTimings');
            $this->loadModel('Settings');
			$this->loadModel('Groups');
			
        }
		
		public $uploadErrorArr = array(
					UPLOAD_ERR_OK => 'No Error', //0
					UPLOAD_ERR_INI_SIZE => 'The uploaded file exceeds the upload_max_filesize',
					UPLOAD_ERR_FORM_SIZE => 'The uploaded file exceeds the MAX_FILE_SIZE',
					UPLOAD_ERR_PARTIAL => 'The uploaded file was only partially uploaded',
					UPLOAD_ERR_NO_FILE => 'No file was uploaded.', //5
					UPLOAD_ERR_NO_TMP_DIR => 'Missing a temporary folder' //6
					);
	public $imageMimes = array('image/jpeg','image/png','image/bmp','image/jpeg');
	public $documentMimes = array('application/pdf');
	
     public function index(){
		  $loggedInUser =  $this->request->session()->read('Auth.User.username');
		  if (!preg_match('/'.QUOT_USER_PREFIX.'/',$loggedInUser)){
			   if($this->request->session()->read('Auth.User.group_id') == ADMINISTRATORS){
					$active = Configure::read('active');
					$this->paginate = [
						 'conditions' => [
							  "username NOT LIKE" => "%".QUOT_USER_PREFIX."%",
						 ],
						'contain' => ['Groups'],
						'order' => ['Users.level Asc'],
					];
					$users = $this->paginate($this->Users);  
				 }else{
					  $user_id = $this->request->session()->read('Auth.User.id');
					  $all_ids = $this->getChildren($user_id);
					  if(empty($all_ids)){
						   $all_ids = array(0 => null);
					  }
					  $active = Configure::read('active');
					$this->paginate = [
					  'conditions' => array(
						   'Users.id IN' => $all_ids,
						   "username NOT LIKE" => "%".QUOT_USER_PREFIX."%",
					  ),
						'contain' => ['Groups'],
						'order' => ['Users.level Asc'],
					];
					$users = $this->paginate($this->Users);
				 }
		  }else{
			   if($this->request->session()->read('Auth.User.group_id') == ADMINISTRATORS){
					$active = Configure::read('active');
					$this->paginate = [
						'contain' => ['Groups'],
						'order' => ['Users.level Asc'],
					];
					$users = $this->paginate($this->Users);  
				 }else{
					  $user_id = $this->request->session()->read('Auth.User.id');
					  $all_ids = $this->getChildren($user_id);
					  if(empty($all_ids)){
						   $all_ids = array(0 => null);
					  }
					  $active = Configure::read('active');
					$this->paginate = [
					  'conditions' => array(
						   'Users.id IN' => $all_ids,
					  ),
						'contain' => ['Groups'],
						'order' => ['Users.level Asc'],
					];
					$users = $this->paginate($this->Users);
				 }
		  }
	 
	 
        
        
        $this->set(compact('users','active'));
        $this->set('_serialize', ['users']);
    }

    public function view($id = null) {
	  if($this->request->session()->read('Auth.User.group_id') == KIOSK_USERS){
		   $this->Flash->success(__('You are not authorized to access that location.'));
           return $this->redirect(['controller' => 'home','action' => 'dashboard']);
		  
	  }else{
        $user = $this->Users->get($id, [
            'contain' => ['Groups','Profiles','Attachments'=> function ($q) {
																				return $q->order(['sr_no'=>'ASC']);
																			 }]
        ]);

        $this->set('user', $user);
        $this->set('_serialize', ['user']);
	  }
       
    }

    
    public function add() {
		  if($this->request->session()->read('Auth.User.group_id') == KIOSK_USERS){
			   $this->Flash->success(__('You are not authorized to access that location.'));
			   return $this->redirect(['controller' => 'home','action' => 'dashboard']);
			  
		  }else{
			  $this->loadModel('Profiles');
			  $active = Configure::read('active');
			  $visaOptions = Configure::read('visa_type');
			  $countryOptions = Configure::read('uk_non_uk');
			  $this->set(compact('active'));
			  $this->set(compact('visaOptions','countryOptions'));
			  $kiosk_list_query = $this->Kiosks->find('list',[
													  'keyField' => 'id',
													  'valueField' => 'name',
													   
												 ]
										 );
			 
			  $kiosk_list_query= $kiosk_list_query->hydrate(false);
			  if(!empty($kiosk_list_query)){
				  $kiosk_list = $kiosk_list_query->toArray();
			  }else{
				  $kiosk_list = array();
			  }								    
			  //pr($kiosk_list);
			  $this->set('kiosk_list',$kiosk_list);
			  
			  $groups_level_arr = $this->Groups->find("list",['keyField' => 'id',
										  'valueField' => 'level',
										  ])->toArray();
			  
			  $user = $this->Users->newEntity();
			  if ($this->request->is('post')) {
				//pr($this->request->data);die;
				   if(array_key_exists('kiosk_assigned',$this->request->data['User']) &&
					  is_array($this->request->data['User']['kiosk_assigned'])){
				
						   $assignkiosk = array_filter($this->request->data['User']['kiosk_assigned']);
						   $assign_kiosk = implode("|",$assignkiosk);
						   $this->request->data['User']['kiosk_assigned'] = $assign_kiosk;
					   
				   } else{
						  
						  $this->request->data['User']['kiosk_assigned'] = '';
				   }
				   $this->request->data['User']['parent_id'] = $this->request->session()->read('Auth.User.id');
				   if(array_key_exists('selectall',$this->request->data['User'])){
						 // $this->request->data['User']['kiosk_assigned'] = '-1';
				   }
				   
				   $this->request->data['User']['level'] = $groups_level_arr[$this->request->data['User']['group_id']];
				   // for de5 user create extra user
					
				   // for de5 user create extra user
				   $user = $this->Users->patchEntity($user, $this->request->data['User']);
				   //pr($user->errors());die;
				   if ($this->Users->save($user)) {
					$user_id = $user->id;
					 
						
						$profileData = array(
								'user_id' => $user_id,
								'date_of_birth' => $this->request['data']['Profile']['date_of_birth'],
								'national_insurance' => $this->request['data']['Profile']['national_insurance'],
								'visa_type' => $this->request['data']['Profile']['visa_type'],
								'visa_expiry_date' => $this->request['data']['Profile']['visa_expiry_date'],
								'memo' => $this->request['data']['Profile']['memo']
								);
						
						$ProfilesEntity = $this->Profiles->newEntity($profileData,['validate' => false]);
						$ProfilesEntity = $this->Profiles->patchEntity($ProfilesEntity,$profileData,['validate' => false]);
						$this->Profiles->save($ProfilesEntity);
						unset($profileData['user_id']);
						$this->create_user_for_dr5($this->request->data['User'],$profileData);
						
						$id = $user->id;
						 $this->Flash->success(__('The user has been saved.'));
						 return $this->redirect(array('controller'=>'users','action' => 'step_two',$id));
	
					//return $this->redirect(['action' => 'index']);
				   }else{
						$errors = $user->errors();
					  
						$err = array();
						foreach($errors as $error){
							 foreach($error as $key){
								  $err[] = $key;
							 }
							 //$err[] = $key." already in use";
						}
						$this->Flash->error(implode("</br>",$err),['escape' => false]);
				   }
					$this->Flash->error(__('The user could not be saved. Please, try again.'));
			  }
			  $groups = $this->Users->Groups->find('list', ['limit' => 200]);
			  $this->set(compact('user', 'groups','profile'));
			  $this->set('_serialize', ['user']);
		  }	
    }
	
	
	public function edit($id = null) {
	  if($this->request->session()->read('Auth.User.group_id') == KIOSK_USERS){
		   $this->Flash->success(__('You are not authorized to access that location.'));
           return $this->redirect(['controller' => 'home','action' => 'dashboard']);
		  
	  }else{
		  
		  if (!$this->Users->exists($id)) {
			  throw new NotFoundException(__('Invalid user'));
		  }else{
			$userEntity = $this->Users->get($id);
			$this->set('user',$userEntity);
		  }
		  $att_arr = array();
		  $attachment_res = $this->Attachments->find("all",["conditions" => ["foreign_key" => $id]])->toArray();
		  if(!empty($attachment_res)){
			   foreach($attachment_res as $att_k => $att_value){
					$att_arr[$att_value->sr_no-1]["name"] =  $att_value->attachment;
					$att_arr[$att_value->sr_no-1]["type"] =  $att_value->type;
			   }
		  }
		  
		  $this->set(compact("att_arr"));
		  
		  $old_kiosk_assigned = $userEntity['kiosk_assigned'];
		  $old_kiosk = explode("|",$old_kiosk_assigned);
		  //pr($old_kiosk);
		  //pr($this->request);die;
		  if($userEntity['group_id'] == KIOSK_USERS || $userEntity['group_id'] == SALESMAN || $userEntity['group_id'] == REPAIR_TECHNICIANS ||
			 $userEntity['group_id'] == UNLOCK_TECHNICIANS
			 ){
			 $parent_id =   $userEntity['parent_id'];
			 $user_kiosk_list_data = $this->Users->find('list',[
													   'conditions' => ['id' => $parent_id],
													'keyField' => 'id',
													'valueField' => 'kiosk_assigned',
													 
											   ]
									   )->toArray();
			 if(!empty($user_kiosk_list_data)){
					$kiosk_ids = explode("|",$user_kiosk_list_data[$parent_id]);
					$kiosk_list_query = $this->Kiosks->find('list',[
											 			   'conditions' => ['id IN' => $kiosk_ids],
														 'keyField' => 'id',
														 'valueField' => 'name',
														  
													]
											);	
			 }else{
			   $kiosk_ids = array(0 => null);
			   $kiosk_list_query = $this->Kiosks->find('list',[
											 			   'conditions' => ['id IN' => $kiosk_ids],
														 'keyField' => 'id',
														 'valueField' => 'name',
														  
													]
											);
			 }
			 
			 
		  }else{
			   $related_kiosks = $this->get_related_kiosks($userEntity);
			   
			   if(empty($related_kiosks)){
					if($userEntity['group_id'] == ADMINISTRATORS){ //$this->request->session()->read('Auth.User.group_id')
						 $kiosk_list_query = $this->Kiosks->find('list',[
														 'keyField' => 'id',
														 'valueField' => 'name',
														  
													]
											);	
					}else{
						 $kiosk_list_query = $this->Kiosks->find('list',[
															'conditions' => ['id IN' => array(0)],
														 'keyField' => 'id',
														 'valueField' => 'name',
														  
													]
											);	
					}
				   
			   }else{
					$kiosk_list_query = $this->Kiosks->find('list',[
															'conditions' => ['id IN' => $related_kiosks],
														 'keyField' => 'id',
														 'valueField' => 'name',
														  
													]
											);
			   }   
		  }
		  
		  
		   
		  $kiosk_list_query= $kiosk_list_query->hydrate(false);
		  if(!empty($kiosk_list_query)){
			  $kiosk_list = $kiosk_list_query->toArray();
		  }else{
			  $kiosk_list = array();
		  }								    
		  $this->set('kiosk_list',$kiosk_list);
		  $all_ids = $this->getChildren($id);
		  if ($this->request->is(array('post', 'put'))) {
			 if(array_key_exists("user_assigned",$this->request->data['User']) && is_array($this->request->data['User']['user_assigned'])){
			   $assigned_user = array_filter($this->request->data['User']['user_assigned']);
			   
			   foreach($all_ids as $k => $v_ids){
					if(!in_array($v_ids,$assigned_user)){
						 //echo $v_ids;
						 $v_id = $this->Users->get($v_ids);
						 $data_to_save = array('parent_id' => $userEntity['parent_id']);
						 $usersEntity_assigend_v = $this->Users->patchEntity($v_id,$data_to_save);
						 if($this->Users->save($usersEntity_assigend_v)){
							  
						 }
					}
			   }
			   foreach($assigned_user as $key => $assigned_user_id){
					$get_id = $this->Users->get($assigned_user_id);
					if(!in_array($assigned_user_id,$all_ids)){
						 $data_to_save = array('parent_id' => $id);
						 $usersEntity_assigend = $this->Users->patchEntity($get_id,$data_to_save);
						 if($this->Users->save($usersEntity_assigend)){
							  
						 }	 
					}
					
			   }
			 }else{
					foreach($all_ids as $k => $v_ids){
						 //if(!array_key_exists($v_ids,$assigned_user)){
							  $v_id = $this->Users->get($v_ids);
							  $data_to_save = array('parent_id' => $userEntity['parent_id']);
							  $usersEntity_assigend_v = $this->Users->patchEntity($v_id,$data_to_save);
							  if($this->Users->save($usersEntity_assigend_v)){
								   
							  }
						 //}
					}
			 }
			   if(array_key_exists('kiosk_assigned',$this->request->data['User']) &&
					   is_array($this->request->data['User']['kiosk_assigned'])){
					
							$selected_kiosk = $assignkiosk = array_filter($this->request->data['User']['kiosk_assigned']);
							$assign_kiosk = implode("|",$assignkiosk);
							$this->request->data['User']['kiosk_assigned'] = $assign_kiosk;
							$unselected_kiosks = array_diff($old_kiosk,$selected_kiosk);
							
						    foreach($all_ids as $k => $v_ids){
								   $v_id = $this->Users->get($v_ids);
								   if(!empty($v_id['kiosk_assigned'])){
										$kioks = explode("|",$v_id['kiosk_assigned']);
										$data_of_kiosk = array();
										foreach($kioks as $k_key => $k_value){
											 if(in_array($k_value,$unselected_kiosks)){
												  //unset($selected_kiosk[$k_value]);
											 }else{
												  $data_of_kiosk[$k_key] = $k_value;
											 }
											 
										}
										$data_to_save = array('kiosk_assigned' => implode("|",$data_of_kiosk));
										$usersEntity_assigend_v = $this->Users->patchEntity($v_id,$data_to_save);
										if($this->Users->save($usersEntity_assigend_v)){
											 
										}
								   }else{
										
								   }
							}
					   } else{
						  foreach($all_ids as $k => $v_ids){
							  $v_id = $this->Users->get($v_ids);
							  if(!empty($v_id['kiosk_assigned'])){
								   $data_to_save = array('kiosk_assigned' => "");
										$usersEntity_assigend_v = $this->Users->patchEntity($v_id,$data_to_save);
										if($this->Users->save($usersEntity_assigend_v)){
											 
										}
							  }
						  }
						   $this->request->data['User']['kiosk_assigned'] = '';
					   }
			  $images = $fileErrors = $fileSuccess = array();
			  if (!empty($this->request['data']['Image'][0])) {				
				  foreach ($this->request['data']['Image'] as $i => $image) {
					  if($image['attachment']['error'] == UPLOAD_ERR_OK){
						  if(in_array($image['attachment']['type'],$this->imageMimes)){
							  if (is_array($this->request['data']['Image'][$i])) {
								   $docDir = $this->is_user_image_directory($id);
								  // if(file_exists($docDir.DS.$image['attachment']['name'])){
									//	$fileSuccess[] = $image['attachment']['name']." Allready Exists!"; 
								  // }else{
								  $sr_no = $i+1;
										if(move_uploaded_file($image['attachment']['tmp_name'], $docDir.DS.$image['attachment']['name'])){
											 $pdfData = array(
											   'foreign_key' => $id,
												'model' => 'User',
												'attachment' => $image['attachment']['name'],
												'type' => $image['attachment']['type'],
												"sr_no" => $sr_no,
												);
											 $att_res_exist = $this->Attachments->find("all",['conditions' => [
																									"foreign_key" => $id,
																									"attachment" => "'".$image['attachment']['name']."'",
																							  ]])->toArray();
											 if(empty($att_res_exist)){
												  
												 $att_res = $this->Attachments->find("all",['conditions' => [
																									"foreign_key" => $id,
																									"sr_no" => $sr_no,
																									"attachment <>" => "'".$image['attachment']['name']."'",
																							  ]])->toArray();
												  if(!empty($att_res)){
													   foreach($att_res as $att_key => $att_val){
															$AttachmentEntity = $this->Attachments->get($att_val->id);
															
															$this->Attachments->delete($AttachmentEntity);
													   }
												  } 
											 }
											
											 
											 $AttachmentEntity = $this->Attachments->newEntity($pdfData);
											 $AttachmentEntity = $this->Attachments->patchEntity($AttachmentEntity,$pdfData);
											 if ($this->Attachments->save($AttachmentEntity)) {
												  $image['model'] = 'User';
												  // ***Unset the foreign_key if the user tries to specify it****
												  if (isset($image['foreign_key'])){unset($image['foreign_key']);}
												  $images[] = $image;
												  $fileSuccess[] = $image['attachment']['name']." uploaded successfully!"; 
											 }else{
												  //pr($AttachmentEntity->errors());die;
												  $fileErrors[] = $image['attachment']['name']." already existing. Not uploaded!";
											 }
										}
								  // }
								   
								   
								 
							  }
						  }elseif(in_array($image['attachment']['type'],$this->documentMimes)){
							  $docDir = $this->is_user_directory($id);
							  //creating directory in above step will avoid creating blank directory
							 // if(file_exists($docDir.$image['attachment']['name'])){
								//   $fileErrors[] = $image['attachment']['name']." already existing. Not uploaded!";
							  //}else{
							  $sr_no = $i+1;
								  if(move_uploaded_file($image['attachment']['tmp_name'], $docDir.DS.$image['attachment']['name'])){
										if(is_dir($docDir)){
											//echo "Directory Existing!!!";
										}
										//echo $docDir.DS.$image['attachment']['name'];die;
										//not working on server.
										$pdfData = array(
												'foreign_key' => $id,
												 'model' => 'User',
												 'attachment' => $image['attachment']['name'],
												 'type' => $image['attachment']['type'],
												 "sr_no" => $sr_no,
												 );
										
										try{
											 
											 $att_res_exist = $this->Attachments->find("all",['conditions' => [
																									"foreign_key" => $id,
																									"attachment" => "'".$image['attachment']['name']."'",
																							  ]])->toArray();
											 if(empty($att_res_exist)){
												 $att_res = $this->Attachments->find("all",['conditions' => [
																									"foreign_key" => $id,
																									"sr_no" => $sr_no,
																									"attachment <>" => "'".$image['attachment']['name']."'",
																							  ]])->toArray();
												  if(!empty($att_res)){
													   foreach($att_res as $att_key => $att_val){
															$AttachmentEntity = $this->Attachments->get($att_val->id);
															
															$this->Attachments->delete($AttachmentEntity);
													   }
												  } 
											 }
											 
											 $AttachmentEntity = $this->Attachments->newEntity($pdfData);
											 $AttachmentEntity = $this->Attachments->patchEntity($AttachmentEntity,$pdfData);
											 if ($this->Attachments->save($AttachmentEntity)) {
												  $success = 1;
												  $fileSuccess[] = $image['attachment']['name']." uploaded successfully!";
											  }else{
												  //pr($AttachmentEntity->errors());die;
												  $failure = 1;
												  $fileErrors[] = $image['attachment']['name']." already existing. Not uploaded!";
											  }
										}catch(Exception $e){
											  $fileErrors[] = $image['attachment']['name']." already existing. Not uploaded!";
										}
										
									}else{
										$fileErrors[] = "Failed to upload ".$image['attachment']['name'];
									} 
							 // }
							  
							  
						  }
					  }else{
						  if($image['attachment']['error'] == UPLOAD_ERR_NO_FILE){continue;}
						  $fileErrors[] = $this->uploadErrorArr[$image['attachment']['error']];
					  }
				  }
			  }
			  // code added by Inder, starts here
			  $date_of_birth = array();
				  $national_insurance = array();
				  $visa_type = array();
				  $visa_expiry_date = array();
				  $memo = array();
			  foreach($this->request['data']['Profile'] as $key => $profile){
				  if (is_array($this->request['data']['Profile'][$key])){
					  $date_of_birth = $this->request['data']['Profile']['date_of_birth'];
					  $national_insurance = $this->request['data']['Profile']['national_insurance'];
					  $visa_type = $this->request['data']['Profile']['visa_type'];
					  $visa_expiry_date = $this->request['data']['Profile']['visa_expiry_date'];
					  $memo = $this->request['data']['Profile']['memo'];
					  
					  $profileData = array(
							  'user_id' => $id,
							  'date_of_birth' => $date_of_birth,
							  'national_insurance' => $national_insurance,
							  'visa_type' => $visa_type,
							  'visa_expiry_date' => $visa_expiry_date,
							  'memo' => $memo
							  );
					  
					  if(array_key_exists('profile_id', $this->request['data']['Profile'])){
						  $profile_id = $this->request['data']['Profile']['profile_id'];
						  $profileData['id'] = $profile_id;
						  $ProfileEntity = $this->Profiles->get($profile_id);
						  
					  }else{
						 
						   $ProfileEntity = $this->Profiles->newEntity($profileData,['validate' => false]);
					  }
					  $ProfileEntity = $this->Profiles->patchEntity($ProfileEntity,$profileData);
					  if($this->Profiles->save($ProfileEntity)){
						// echo "hi";die;
					  }else{
						// pr($ProfileEntity->errors());die;
					  }
					  break;
				  }
			  }
			  // code added by Inder, ends here
			  if(count($images)){
			  $data = array(
						'User' => $this->request['data']['User'],
						'Image' => $images
				  );
			  }else{
				  $data = array(
						'User' => $this->request['data']['User']
				  );
			  }
			  $group_id = $this->request['data']['User']['group_id'];
			  $groups_level = $this->Groups->find('list',array('conditions' => array(
																	 'id' => $group_id,
																	 ),
											   'keyField' => 'id',
											   'valueField' => 'level',
											   ))->toArray();
			  
			  $data['User']['level'] = $groups_level[$group_id];
			  //pr($data);die;
			  /*
			  $data = array(
				  'Article' => array('title' => 'My first article'),
				  'Comment' => array(
					  array('body' => 'Comment 1', 'user_id' => 1),
					  array('body' => 'Save a new user as well','User' => array('first' => 'mad', 'last' => 'coder')
					  )
				  ),
			  );
			  $this->SomeModel->saveAll($data, array('deep' => true));
			  */
			  //adding images to data
			  $fileErrorStr = implode("<br/>",$fileErrors);
			  $fileSuccessStr = implode("<br/>",$fileSuccess);
			  if(!empty($fileErrorStr))$fileErrorStr = "<br/>$fileErrorStr";
			  if(!empty($fileSuccessStr))$fileSuccessStr = "<br/>$fileSuccessStr";
			  $UsersEntity = $this->Users->get($id);
			  //pr($data);
			  $UsersEntity = $this->Users->patchEntity($UsersEntity,$data['User']);
			  
		   // pr($UsersEntity);die;
			  if ($this->Users->save($UsersEntity) || !empty($success) || !empty($failure)) {
				  $this->Flash->success(__("The user has been saved.{$fileErrorStr}{$fileSuccessStr}",['escape' => false]));
				  return $this->redirect(array('action' => 'index'));
			  } else {
				  $errors = $UsersEntity->errors();
				  //pr($errors);die;
				  $err = array();
				  foreach($errors as $error){
					  foreach($error as $key){
						   $err[] = $key;
					  }
					  //$err[] = $key." already in use";
				 }
				 $this->Flash->error(implode("</br>",$err),['escape' => false]);
				  $this->Flash->error(__("The user could not be saved. Please, try again.{$fileErrorStr}{$fileSuccessStr}"));
			  }
		  } else {
			  $options = array('conditions' => array('Users.id' => $id),
							   'contain' => array('Profiles')
							   );
			  $user_query = $this->Users->find('all', $options);
			  $user_query = $user_query->hydrate(false);
			  if(!empty($user_query)){
				 $user = $user_query->first();
			  }else{
				 $user = array();
			  }
			  $this->request->data = $user;
			  $this->request->data['password'] = "";
		  }
		  
		  
		  
		  $all_ids = array();
		   $all_ids = $this->getChildren($id);
		   //pr($all_ids);
		   
		  $this->set(compact('all_ids'));
		  $groups_query= $this->Users->Groups->find('list');
		  $groups_query = $groups_query->hydrate(false);
		  if(!empty($groups_query)){
			$groups = $groups_query->toArray();
		  }else{
			$groups = array();
		  }
		  $condition_arr  = array();
		//  $condition_arr['id NOT IN'] = $id;
		if(!isset($user)){
		  $user = $userEntity;
		}
		
		if(isset($user) && !empty($user)){
		    $condition_arr['level >'] = $user['level'];
		}else{
		  $condition_arr['level >'] = $userEntity['level'];
		}
		
		
		  if(!empty($all_ids)){
			   $parent_id_assigned = $user['parent_id'];
			   if($this->request->session()->read('Auth.User.group_id') == ADMINISTRATORS){//
					if($user['group_id'] == ADMINISTRATORS){
						 $all_ids[] = 0;
					}
					//$condition_arr['group_id >'] = $user['group_id'];
			   }else{
					//$condition_arr['group_id >'] = $user['level'];
			   }
			   $all_ids[] = $user['parent_id'];
			   $all_ids[] = $id;
			   $condition_arr['parent_id IN'] = $all_ids;
		  }else{
			   if($this->request->session()->read('Auth.User.group_id') == ADMINISTRATORS){//
					if($user['group_id'] == ADMINISTRATORS){
						 $condition_arr['parent_id IN'] = array(0,$id,$user['parent_id']);   	 
					}else{
						 $condition_arr['parent_id IN'] = array($id,$user['parent_id']);   	 
					}
				//	$condition_arr['group_id >'] = $user['group_id'];
			   }else{
					//$condition_arr['level >'] = $user['level'];
					$condition_arr['parent_id IN'] = array($id,$user['parent_id']);   
			   }
			   
		  }
		  //pr($condition_arr);die;
		  
		  
		  
		  $all_user = $all_groups_query = array();
		  $all_groups_query = $this->Groups->find('list',array(//'conditions' => $condition_arr,
															'keyField' => 'id',
															'valueField' => 'name',
															))->toArray();
		 // pr($condition_arr);
		  $all_user_query = $this->Users->find('all',array('conditions' => $condition_arr,
															//'keyField' => 'id',
															//'valueField' => 'username',
															));
		  //pr($all_user_query);die;
		  $all_user_query = $all_user_query->hydrate(false);
			  if(!empty($all_user_query)){
				 $all_user_data = $all_user_query->toArray();
			  }else{
				 $all_user_data = array();
			  }
			  foreach($all_user_data as $key => $value_data){
			   $all_user[$value_data['id']] = $value_data['username']."(".$all_groups_query[$value_data['group_id']].")";
			  }
			  
			  
		  $parent_ids_data = $this->Users->find('list',array('conditions' => array('parent_id' => $id),
															'keyField' => 'id',
															'valueField' => 'id',
															))->toArray();
		  //pr($parent_ids_data);
		  $names = $this->Users->find('list',array(
																  'keyField' => 'id',
													 'valueField' => 'username'
																 )
													 )->toArray();
		  $user_group_data = $this->Users->find('list',array('keyField' => 'id',
										  'valueField' => 'group_id',
										  ))->toArray();
		  
		  //------ same group other users-----
		  $assigned_to_other_data = $this->same_group_other_user($user);
		  //------ same group other users-----
		//die;  
		  //---- showing parents----------
		  
		  $herrarcy = $this->getParent($user['id'],$data = array());
		  
		  if(!empty($herrarcy)){
			   $herrarcy_data = array();
			   foreach(array_reverse($herrarcy) as $hrr_key => $hrr_val){
					if(array_key_exists($hrr_val,$names)){
						 $names_to_add = $names[$hrr_val];	 
					}else{
						 $names_to_add = "";
					}
					if(array_key_exists($hrr_val,$user_group_data)){
						 $herrarcy_data[$hrr_val] = $groups[$user_group_data[$hrr_val]]." (".$names_to_add .")";	 
					}else{
						 $herrarcy_data[$hrr_val] = "";
					}
					
			   }   
		  }else{
			   $herrarcy_data = array();
		  }
		  //---- showing parents----------
		  
		  $user_level_data = $this->Users->find('list',array('keyField' => 'id',
										  'valueField' => 'level',
										  ))->toArray();
		  $external_site_arry = $CURRENCY_TYPE = Configure::read('external_sites');
		  $path = dirname(__FILE__);
		  $ext_site = 0;
		  if(!empty($external_site_arry)){
			   foreach($external_site_arry as $k=>$v){
					$isboloRam = strpos($path,$v);
					if($isboloRam){
						$ext_site = 1;
					}
			  }   
		  }
		  
			//  pr($parent_ids_data);die;
		  $this->set(compact('groups','all_user','parent_ids_data','names','assigned_to_other_data','user_group_data','user_level_data','herrarcy_data','ext_site'));
	  }
	}

    public function delete($id = null) {
	  if($this->request->session()->read('Auth.User.group_id') == KIOSK_USERS){
		   $this->Flash->success(__('You are not authorized to access that location.'));
           return $this->redirect(['controller' => 'home','action' => 'dashboard']);
		  
	  }else{
		  $this->request->allowMethod(['post', 'delete']);
		  $user = $this->Users->get($id);
		  if ($this->Users->delete($user)) {
			  $this->Flash->success(__('The user has been deleted.'));
		  } else {
			  $this->Flash->error(__('The user could not be deleted. Please, try again.'));
		  }
  
		  return $this->redirect(['action' => 'index']);
	  }
    }
    
   public function update_attendence(){
		//this function is allowing only one entry for `log in` in a day
		//and prohibiting to run $this->UserAttendance->save($loginData); again.
        $userID = $this->request->session()->read('Auth.User.id'); 
        $logged_time = date('Y-m-d');
        $today = "SELECT * FROM `user_attendances` WHERE DATE(`logged_in`) = CURDATE() AND `user_id`= '$userID'";
        $conn1 = ConnectionManager::get('default');
        $logged_in_query = $conn1->execute($today);
        $logged_in = $logged_in_query ->fetchAll('assoc');
      //  pr($logged_in);die;
        if(!empty($logged_in)){
            // return $this->redirect(['action' => 'index']);
             $kioskType = $this->request->session()->read('kiosk_type');
			   if($kioskType == SERVICE_CENTER){
				return $this->redirect(array('controller' => 'home','action' => 'index'));
			   }elseif($kioskType == UNLOCKING_CENTER){
				  return $this->redirect(array('controller' => 'mobile-unlocks','action' => 'index'));  
			   }elseif($this->request->session()->read('Auth.User.group_id') == inventory_manager){
			      return $this->redirect(array('controller' => 'kiosk-product-sales','action' => 'all-kiosk-sale')); 
			   }elseif($this->request->session()->read('Auth.User.group_id') == ADMINISTRATORS ||
					$this->request->session()->read('Auth.User.group_id') == FRANCHISE_OWNER){
					 return $this->redirect(array('controller' => 'home','action' => 'dashboard'));
					}else{
					if($this->request->session()->read('Auth.User.user_type') == 'retail'){
						return $this->redirect(array('controller' => 'retail_customers ','action' => 'index'));
					}else{
						return $this->redirect(array('controller' => 'customers','action' => 'index'));	
					   }
				  //return $this->redirect(array('controller' => 'customers','action' => 'index'));  
			   }
        }
	}
    public function check_login(){
		//Created By: Rajju
		//Note: admin can login any number of times
		//we can use this code if(AuthComponent::user('group_id') == ADMINISTRATORS){
		$userID = $this->request->session()->read('Auth.User.id');
		$dayof = date('Y-m-d');
		$dayoff_query = $this->UserAttendances->find('all',array(
												'conditions' => array(
																	  'UserAttendances.user_id' => $userID,
																	  'Date(UserAttendances.day_off)' => $dayof
																	  ),
																'fields' => array('id','day_off' ),
																//'recursive' => -1
																)
											  );
		//pr($dayoff_query);die;
         $dayoff_query = $dayoff_query->hydrate(false);
        if(!empty($dayoff_query)){
            $dayoff = $dayoff_query->first();
        }else{
            $dayoff = array();
        }
		
        //pr($dayoff);die;
		if($this->request->session()->read('Auth.User.group_id') != ADMINISTRATORS && !empty($dayoff)){
			$this->Flash->success(__('Your have already logged off for the day. Please try tomorrow!')); 
			$this->redirect($this->Auth->logout());
		}
	}
     public function login(){
       $kioskArray_query = $this->Kiosks->find('all',array(
							'fields'=>array('Kiosks.code','Kiosks.name','Kiosks.kiosk_type'),
							
								
								)
						   );
        $kioskArray_query = $kioskArray_query->hydrate(false);
        if(!empty($kioskArray_query)){
            $kioskArray = $kioskArray_query->toArray();
        }else{
            $kioskArray = array();
        }
        $kiosks_query = $this->Kiosks->find('list',
                                      [
                                        'keyField' => 'id',
                                        'valueField' => 'name',
                                        
                                    ]);
        if(!empty($kiosks_query)){
            $kiosks = $kiosks_query->toArray();
        }
        unset($kiosks['10000']);
		$this->set(compact('kiosks'));
     	$this->set(compact('kioskArray'));
         $serverNameArr = explode(".", $_SERVER['SERVER_NAME']);
         if($this->request->is('post')){
            $serverNameArr = explode(".", $_SERVER['SERVER_NAME']);
            if($this->request->data['request_auth'] == 'yes'){
                //checking the url, if it is for admin or subdomain, 3 is for main, 4 for subdomain
				 $kioskName = $serverNameArr['0'];
				$jsonData = json_encode($this->request->data);
				return $this->redirect(array('controller' => 'devices', 'action' => 'request_authorization', '?' => array('kioskName' => $kioskName, 'jsonData' => $jsonData)));
			}
             $user = $this->Auth->identify();
             if ($user) {
             $username = $user['username'];
			  $user_id = $user['id'];
             $statusActiveData_query = $this->Users->find('all',array(
																'fields' => array('id', 'username', 'user_type', 'group_id', 'active'),
																'conditions' => array('Users.username'=>$username),
																//'recursive' => -1
														)
												   );
            $statusActiveData_query = $statusActiveData_query->hydrate(false);
             if(!empty($statusActiveData_query)){
              $statusActiveData = $statusActiveData_query->first();   
             }else{
                 $statusActiveData = array();
             }
            // pr($statusActiveData);die;
             $statusActive = 0;
             if(array_key_exists('active',$statusActiveData )){
                $statusActive = $statusActiveData['active'];
             }
             
             if($statusActive == 0){
                 $this->delete_sesssions();
                 $this->Flash->success(__('Inactive users are not allowed to login to this sub-domain'));
				 return $this->redirect(array('controller' => 'Users','action' => 'login'));
				 die;
                 //$this->redirect($this->Auth->logout());
             }
            //if ($user) {
                 $this->Auth->setUser($user);
                if( $this->request->session()->read('Auth.User.group_id') != ADMINISTRATORS && $this->request->session()->read('Auth.User.group_id') != MANAGERS){
                
                }
				//pr($_SESSION);die;
                $all_auth_data = $this->request->session()->read('Auth');
				$kioskID = $this->request->session()->read('kiosk_id');	 	
				$kioskType = $this->request->session()->read('kiosk_type');
			// echo $kioskType;die;
				//pr($all_auth_data);die;
               // $this->Auth->redirectUrl();
              // die;
              
                if($this->request->session()->read('Auth.User.group_id') == ADMINISTRATORS){
					if((int)$kioskID){
						$this->delete_sesssions();
                        $this->Flash->success(__('Administrators users are not allowed to login from this sub-domain'));
						return $this->redirect($this->Auth->logout());
					}else{
					
					}
 				}elseif($kioskType == KIOSK){
					//echo $kioskType;die;
                    $loginkiosk_query = $this->Users->find('all',array(
																 'fields' => array('id', 'kiosk_assigned'),
																 'conditions' => array('Users.id'=>$user_id),
																)
												   );
					$loginkiosk = $loginkiosk_query->hydrate(false);
					 if(!empty($loginkiosk_query)){
					  $loginkiosk = $loginkiosk_query->first();   
					 }else{
						 $loginkiosk = array();
					 }
					$kiosk_assigned = array();
					if(!empty($loginkiosk)){
//						 if($loginkiosk['kiosk_assigned'] == -1){
//							   $kiosks_query = $this->Kiosks->find('list',[
//                                                'keyField' => 'id',
//                                                'valueField' => 'name',
//                                             ]);
//                            if(!empty($kiosks_query)){
//                                $kiosks = $kiosks_query->toArray();
//                            }else{
//                                $kiosks = array();
//                            }
//							$kioskIDs = array_keys($kiosks);//die;
//							$loginkiosk['kiosk_assigned'] = implode('|', $kioskIDs);	 
//						 }
						 if(is_array(explode('|',$loginkiosk['kiosk_assigned']))){
							   $kioskids = explode('|',$loginkiosk['kiosk_assigned']);
						}
					}
					if(in_array($kioskID,$kioskids)){
						if($kioskID && $kioskType == KIOSK && (
															   $this->request->session()->read('Auth.User.group_id') == KIOSK_USERS // ||
															 //   $this->request->session()->read('Auth.User.group_id') == MANAGERS
						)){
							// echo "kiosk"
							  
							 //redirect user according to kiosk selected
							 //header("location:{$subdomain}.".WEBSITE."/dashboard");
							 //On hold:get list of kiosks where kiosk type == 1 in array
						 }else{
							 $this->delete_sesssions();
							   $this->Flash->success(__('Kiosk users are not allowed to login from this sub-domain'));
							 return $this->redirect($this->Auth->logout());
						 }
					}else{
						  $external_site_arry = $CURRENCY_TYPE = Configure::read('external_sites');
							  $path = dirname(__FILE__);
							  $ext_site = 0;
							  if(!empty($external_site_arry)){
								foreach($external_site_arry as $k=>$v){
									  $isboloRam = strpos($path,$v);
									  if($isboloRam){
										  $ext_site = 1;
									  }
								}
							  }
							  if($ext_site == 1){
										$this->delete_sesssions();
										$this->Flash->success(__('This users are not allowed to login from this sub-domain'));
										return $this->redirect($this->Auth->logout());		   
							  }
						 
						 
					}
				}elseif($kioskType == SERVICE_CENTER){
					//we should have kiosk id and $kioskType should be SERVICE_CENTER and user group should be REPAIR_TECHNICIANS
					if($kioskID && $kioskType == SERVICE_CENTER && $this->request->session()->read('Auth.User.group_id') == REPAIR_TECHNICIANS){
						;//allow user
					}else{
						$this->delete_sesssions();
						 $this->Flash->success(__('Only service center users are allowed to login from service centers'));
						return $this->redirect($this->Auth->logout());
					}
				}elseif($kioskType == UNLOCKING_CENTER){
					//we should have kiosk id and $kioskType should be UNLOCKING_CENTER and user group should be UNLOCK_TECHNICIANS
					if($kioskID && $kioskType == UNLOCKING_CENTER && $this->request->session()->read('Auth.User.group_id') == UNLOCK_TECHNICIANS){
						;//allow user
					}else{
						$this->delete_sesssions();
						 $this->Flash->success(__('Only unlock technician are allowed to login from unlocking centers'));
						return $this->redirect($this->Auth->logout());
					}
				}else{
					 
					if(
						$this->request->session()->read('Auth.User.group_id') == ADMINISTRATORS ||
						$this->request->session()->read('Auth.User.group_id') == MANAGERS ||
						$this->request->session()->read('Auth.User.group_id') == SALESMAN ||
						$this->request->session()->read('Auth.User.group_id') == inventory_manager ||
						$this->request->session()->read('Auth.User.group_id') == FRANCHISE_OWNER
					   ){	
                         
							  if($this->request->session()->read('Auth.User.group_id') == MANAGERS){
								   $loginkiosk_query = $this->Users->find('all',array(
																 'fields' => array('id', 'kiosk_assigned'),
																 'conditions' => array('Users.id'=>$user_id),
																)
												   );
								   $loginkiosk = $loginkiosk_query->hydrate(false);
								   if(!empty($loginkiosk_query)){
											 $loginkiosk = $loginkiosk_query->first();   
								   }else{
											 $loginkiosk = array();
								   }
								   $kiosk_assigned = array();
								   if(!empty($loginkiosk)){
										//if($loginkiosk['kiosk_assigned'] == -1){
										//		  $kiosks_query = $this->Kiosks->find('list',[
										//			   'keyField' => 'id',
										//			   'valueField' => 'name',
										//			]);
										//		  if(!empty($kiosks_query)){
										//			  $kiosks = $kiosks_query->toArray();
										//		  }else{
										//			  $kiosks = array();
										//		  }
										//		  $kioskIDs = array_keys($kiosks);//die;
										//		  $loginkiosk['kiosk_assigned'] = implode('|', $kioskIDs);	 
										//}
										if(is_array(explode('|',$loginkiosk['kiosk_assigned']))){
											  $kioskids = explode('|',$loginkiosk['kiosk_assigned']);
									   }
								   }
								   // pr($kioskids);die;
								   if(!empty($kioskids['0'])){
										if($kioskType == '' && ($this->request->session()->read('Auth.User.group_id') == MANAGERS)){
											 ;//allow user
										}else{
											 $this->delete_sesssions();
											 $this->Flash->success(__('Only manager center users are allowed to login '));
											return $this->redirect($this->Auth->logout());
										}
						 
								   }else{
										 $external_site_arry = $CURRENCY_TYPE = Configure::read('external_sites');
										$path = dirname(__FILE__);
										$ext_site = 0;
										foreach($external_site_arry as $k=>$v){
											  $isboloRam = strpos($path,$v);
											  if($isboloRam){
												  $ext_site = 1;
											  }
										}
										if($ext_site == 1){
											 $this->delete_sesssions();
											 $this->Flash->success(__('You Have No Kiosk Assigned... Please contact Admin'));
											 return $this->redirect($this->Auth->logout());
										}
						 
								   }
							  }
						 }else{
							  $this->delete_sesssions();
							  $this->Flash->success(__('Only Administrator/Managers/Salesman/Inventory Managers are allowed to login from main domain'));
							 return $this->redirect($this->Auth->logout());
						 }
				}
				$user_attendence =   $this->update_attendence(); //rajju
                $check_login = $this->check_login();	//rajju
				
                if(!empty($user_attendence)){
                    return $user_attendence;
                } 
				$session_id = session_id();
				$user_id = $user['id'];//$this->request->session()->read('Auth.User.id');
                $this->request->session()->read('Auth.User.group_id');
               if(empty($kioskID)){
                 $kioskID = 0;
               }
             
               $loginData = array(
                        'kiosk_id' => $kioskID,
                        'user_id' => $user_id,
                        'session_ide' => $session_id,
                        'logged_in' => date('Y-m-d H:i:s')
                    );
                $UserAttendancesEntity = $this->UserAttendances->newEntity($loginData,['validate' => false]);
                $UserAttendancespatchEntity = $this->UserAttendances->patchEntity($UserAttendancesEntity,$loginData,['validate' => false]);
               // pr($UserAttendancespatchEntity);die;
                if ($this->UserAttendances->save($UserAttendancespatchEntity,['validate' => false])) {
                  
                if($this->request->session()->read('Auth.User.group_id') != MANAGERS ||
                   $this->request->session()->read('Auth.User.group_id') != ADMINISTRATORS){
						$result_query  = $this->KioskTimings->find('all',array('conditions' => array('kiosk_id' => $kioskID)));
                        $result_query = $result_query->hydrate(false);
                        if(!empty($result_query)){
                         $result = $result_query->first();   
                        }else{
                            $result = array();
                        }
						$emailTo = "rajjukaura@gmail.com";
						$email_result_query = $this->Settings->find('all',array('conditions' => array(
																							'attribute_name' => 'attendence_email'),
																							'fields' => array('attribute_value'),
																						   ));
                         $email_result_query = $email_result_query->hydrate(false);
                        if(!empty($email_result_query)){
                         $email_result = $email_result_query->first();   
                        }else{
                            $email_result = array();
                        }
					   $emails = array();
					   if(!empty($email_result)){
						   $emailTo = $email_result['attribute_value'];
						   $emails =  explode(',', $emailTo);
					   }
                       //pr($result);die;
						if(!empty($result)){
							if(date('N') == 1){
								$time_to_check = $result['mon_time_in'];
							}elseif(date('N') == 2){
								$time_to_check = $result['tues_time_in'];
							}elseif(date('N') == 3){
								$time_to_check = $result['wed_time_in'];
							}elseif(date('N') == 4){
								$time_to_check = $result['thrus_time_in'];
							}elseif(date('N') == 5){
								$time_to_check = $result['fri_time_in'];
							}elseif(date('N') == 6){
								$time_to_check = $result['sat_time_in'];
							}elseif(date('N') == 7){
								$time_to_check = $result['sun_time_in'];
							}
							$min = 0; $status = "";
							$ldeal_time1 = strtotime($time_to_check);
							$ldeal_time2 = date("H:i:s", strtotime('+10 minutes', $ldeal_time1));
							$ldeal_time = strtotime($ldeal_time2);
							$current_time = strtotime(date("H:i:00"));
							if($ldeal_time == $current_time){
								;// do nothing
							}else{
								if($ldeal_time < $current_time){
									$status = " Late ";
									$diff = $current_time - $ldeal_time1;
								}else{
									//$status = "before";
									//$diff = $ldeal_time - $current_time;
								}
								if(!empty($diff)){
									$min = $diff/60;
								}
								
								//$status;
							}
							if($min >0 && !empty($status)){
                                 $kiosks_query = $this->Kiosks->find('list',[
                                                'keyField' => 'id',
                                                'valueField' => 'name',
											]);
                                    if(!empty($kiosks_query)){
                                        $kiosk = $kiosks_query->toArray();
                                    }else{
                                        $kiosk = array();
                                    }
							$send_by_email = Configure::read('send_by_email');
							$emailSender = Configure::read('EMAIL_SENDER');
								foreach($emails as $email){
									$email = trim($email);
									if (!filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
                                       	$Email = new Email();
										$Email->config('default');
                                      	$Email->viewVars(array(
											'kiosk' => $kiosk,
											'kiosk_id' =>  $kioskID,
											'min' => $min,
											'log' => 'opened by',
											'username' => $username,
											'status' => $status,
										   ));
										$Email->template('attaendence_email');
										$Email->emailFormat('html');
										$Email->to($email);
										 $Email->transport(TRANSPORT);
										 $Email->from([$send_by_email => $send_by_email]);
										//$Email->sender('sales@oceanstead.co.uk','Sales Team');
										//This should be added in config file
										$sub = $kiosk[$kioskID]." , ".$username." ".$status." opened by ".$min." minutes";
										$Email->subject($sub);
										$Email->send();
									}
								}
							}
						}
					}
				}else{
                    pr($UserAttendancespatchEntity->errors());die;
                }
              
//                 //if($this->Auth->setUser($user)){
//                 //   echo "hh";
//                 //} else{
//                 //   echo "kk";
//                 //}

		  $kioskType = $this->request->session()->read('kiosk_type');
			if($kioskType == SERVICE_CENTER){
             return $this->redirect(array('controller' => 'home','action' => 'index'));
			}elseif($kioskType == UNLOCKING_CENTER){
			   return $this->redirect(array('controller' => 'mobile-unlocks','action' => 'index'));  
		    }elseif($this->request->session()->read('Auth.User.group_id') == inventory_manager){
			   return $this->redirect(array('controller' => 'kiosk-product-sales','action' => 'all-kiosk-sale'));  
	        }elseif($this->request->session()->read('Auth.User.group_id') == ADMINISTRATORS ||
					$this->request->session()->read('Auth.User.group_id') == FRANCHISE_OWNER
					){
			   
			    return $this->redirect(array('controller' => 'home','action' => 'dashboard'));
			}else{
			   
			   if($this->request->session()->read('Auth.User.user_type') == 'retail'){
						return $this->redirect(array('controller' => 'retail_customers ','action' => 'index'));
					}else{
						return $this->redirect(array('controller' => 'customers','action' => 'index'));	
					   }
			  ///return $this->redirect(array('controller' => 'customers','action' => 'index'));  
			}

            return $this->redirect(array('controller' => 'home','action' => 'dashboard'));
                 
               
            } else {
                $this->Flash->error(__('Username or password is incorrect'), [
                    'key' => 'auth'
                ]);
            }
        }
    }
    private function delete_sesssions(){
		$this->request->Session()->delete('kiosk_id');
		$this->request->Session()->delete('kiosk_type');
		$this->request->Session()->delete('kiosk_title');
		$this->request->Session()->delete('sessionSubDomain');		
	}
    public function logout(){
        $session_id = session_id();
		$logged_out = date('Y-m-d H:i:s');
        $query1 = "UPDATE user_attendances SET logged_out='$logged_out' WHERE session_ide='$session_id'";
        $conn1 = ConnectionManager::get('default');
        $stmt = $conn1->execute($query1);
		 
		$this->delete_sesssions();
		$this->request->Session()->destroy();
		$this->Flash->success(__('Good-Bye'));
        return $this->redirect($this->Auth->logout());
    }
    function search($keyword = ""){
         $active = Configure::read('active');
		$searchKW = $this->request->query['search_kw'];
        $query = $this->Users->find()->where(['OR' => [
                                                        'Users.username like' => "%$searchKW%",
                                                        'Users.email like' => "%$searchKW%",
                                                        'Users.f_name like' => "%$searchKW%",
                                                                        ],
                                              ]
                                             );
        //->contain()
        $query->contain(['Groups']);
        
        $users =  $this->paginate($query);
        //pr($users);die;
		$this->set(compact('users','active'));
        $this->viewBuilder()->templatePath('Users');
		//$this->viewPath = 'Users';
		$this->render('index');
	}

    public function kioskUsers($search = ""){
	 
	//echo'hi';die;
		if(array_key_exists('search',$this->request->query)){
			$search = strtolower($this->request->query['search']);
		}
		$userArr = array();
		if(!empty($search)){
		  $loggedInUser =  $this->request->session()->read('Auth.User.username');
		  if (!preg_match('/'.QUOT_USER_PREFIX.'/',$loggedInUser)){
			   $query = $this->Users->find('all',array(
										'fields' => array('id','username'),
										'conditions' => array("LOWER(`Users`.`username`) like '%$search%'",
															"username NOT LIKE" => "%".QUOT_USER_PREFIX."%",  
															  ),
										//'recursive' => -1,
										  ));
		  }else{
			  $query = $this->Users->find('all',array(
										'fields' => array('id','username'),
										'conditions' => array("LOWER(`Users`.`username`) like '%$search%'"),
										//'recursive' => -1,
										  )); 
		  }
           
          
            if(!empty($query)){
                $users = $query->toArray();
            }
        }
       
		foreach($users as $user){
			$userArr[] = array('id' => $user->id, 'username' => $user->username);
		}
		//pr($userArr);die;
		echo json_encode($userArr);
		$this->viewBuilder()->layout(false);
		die;
	}
	
	function forgetPassword(){
		//$this->User->recursive = -1;
		if(!empty($this->request->data)){
			if( empty($this->request->data['User']['email']) || empty($this->request->data['User']['mobile']) ){
				$this->Flash->error('Please Provide your email adress and mobile that you used to register with Us');
			}else{
				$email = $this->request->data['User']['email'];
				$mobile = $this->request->data['User']['mobile'];
				$fu_query = $this->Users->find('all', array('conditions' => array(
											     array(
												'OR' => array(
													'Users.email' => $email,
													'Users.username' => $email
													)
												),
											     'OR' => array(
												'Users.mobile' => "$mobile"
											     )
											)
								       )
							);
				$fu_query = $fu_query->hydrate(false);
				if(!empty($fu_query)){
					$fu = $fu_query->first();
				}else{
					$fu = array();
				}
				
				if($fu){
					//debug($fu);
					if($fu['active']){
						$key = Security::hash(Text::uuid(),'sha512',true);
						$hash = sha1($fu['username'].rand(0,100));
						$url = "http://hpwaheguru.co.uk".Router::url(['controller' => 'users', 'action' => 'reset', 'token' => $key.'#'.$hash]);
						//$url = Router::url( array('controller'=>'users','action'=>'reset'), true ).'/'.$key.'#'.$hash;
						$ms = $url;
						$ms = wordwrap($ms,1000);
						//debug($url);
						$send_by_email = Configure::read('send_by_email');
						$emailSender = Configure::read('EMAIL_SENDER');
						$fu['tokenhash'] = $key.'#'.$hash;
						$UsersEntity = $this->Users->get($fu['id']);
						
						$data = array('tokenhash' => $fu['tokenhash']);
						$UsersEntity = $this->Users->patchEntity($UsersEntity,$data,['validate' => false]);
						//pr($UsersEntity);die;
						if($this->Users->save($UsersEntity)){
						    $Email = new Email();
							$Email->config('default');
							$Email->viewVars(array('ms' => $ms));
							$Email->template('resetpw');
							$Email->emailFormat('both');
							$Email->to($fu['email']);
							 $Email->transport(TRANSPORT);
							  $Email->from([$send_by_email => $emailSender]);
							//$Email->sender("sales@oceanstead.co.uk");
							$Email->subject('Reset '.ADMIN_DOMAIN.' Password');
							//$Email->send();	
							if($Email->send()){
								//$this->set('smtp_errors', $this->Email->smtpError);
								$this->Flash->success(__('Check your email(Inbox/Spam Folder) To reset your password.', true));
							}
							
							$this->redirect($this->Auth->logout());
							//$this->redirect(array('action' => 'index','controller' => 'users'));
							//============EndEmail=============//
						}else{
							$this->Flash->error("Error Generating Reset link. Please try again!");
							$this->redirect(array('action' => 'forget_password','controller' => 'users'));
						}
					}else{
						$this->Flash->error('This Account is not Active yet. Check Your mail to activate it');
						$this->redirect(array('action' => 'forget_password','controller' => 'users'));
					}
				}else{
				    $this->Flash->error('Email or Mobile Number does Not Exist');
				    $this->redirect(array('action' => 'forget_password','controller' => 'users'));
				}
			}
		}
	}
	
	function reset($token = null){
		//$this->layout="Login";
		//$this->User->recursive = -1;
		if($this->request->is('post')){
		  if(array_key_exists('token',$this->request->query)){
			   $token = $this->request->query['token'];
		  }
			if(!empty($token)){
				$u_query = $this->Users->findByttokenhash($token);
				//pr($u_query);
				//$u_query = $this->Users->findBytokenhash(array('conditions' => array('token' => $token)));
				$u_query = $u_query->hydrate(false);
				if(!empty($u_query)){
					$u = $u_query->toArray();
				}else{
					$u = array();
				}
				//pr($u);die;
				//pr($this->request);die;
				if($u){
				    $UsersEntity = $this->Users->get($u[0]['id']);
				    if(!empty($this->request->data)){
						 $data = $this->request->data['User'];
						 $data['username'] = $u[0]['username'];
						 $new_hash = sha1($u[0]['username'].rand(0,100));//created token
						 $data['tokenhash'] = $new_hash;
						 //pr($data);die;
						 $key = Security::salt();
						 if($data['password'] == $data['confirm_password']){
							 // $data['password'] = Security::encrypt($data['password'],$key);
							  unset($data['confirm_password']);
						 }else{
							  $this->Flash->error('password mismatch.');
							  return $this->redirect(array('controller' => 'users','action' => 'reset','token' => $token));
						 }
						 $UsersEntity = $this->Users->patchEntity($UsersEntity,$data);
						 //if($this->User->validates(array('fieldList' => array('password','password_confirm')))){
							  if($this->Users->save($UsersEntity)){
								   $this->Flash->success('Password Has been Updated');
								   $this->redirect(array('controller' => 'users','action' => 'login'));
							  }	 else{
								   $errors = $UsersEntity->errors();
								   $error_str = "";
								   if(!empty($errors)){
										$count = 0;
										foreach($errors as $error ){
											 foreach($error as $error_key => $error_value){
												  $count ++;
													   if($count > 1){
															$error_str .= "</br>".$error_value;
													   }else{
															$error_str .= $error_value;
													   }		  
											 }
										}
										$this->Flash->error($error_str,array('escape' => false));
								   }
							  }
						 //}else{	 
							//  $this->set('errors',$this->User->invalidFields());
						 //}
				    }
				}else{
					$this->Flash->error('Token Corrupted,,Please Retry.the reset link work only for once.');
				}
			}
		}else{
		  //echo "hi";die;
		   // $this->redirect('/');
		}
	}
	
	public function stepTwo($id=null) {
		if ($this->request->is('post')){
		  //pr($this->request);die;
			if($this->request['data']['redirect']=='Y'){
                //echo'hi';die;
			return $this->redirect(array('controller'=>'users','action'=>'addDocuments',$id));	
			}else{
                //echo'bye';die;
				return $this->redirect(array('action'=>'index'));
			}	
		}		
	}
	
	public function addDocuments($id=null){
        //echo'hi';die;
		//pr($this->request['data']['Image']);die;
		$user_query = $this->Users->find('all',array(
						'conditions' => array('Users.id'=>$id)
						)
					  );
		$user_query = $user_query->hydrate(false);
		if(!empty($user_query)){
		  $user = $user_query->first();
		}else{
		  $user = array();
		}
			$images = array();
			$fileSuccess = array();
			$fileErrors  = array();
			$data  = array();
			$docDir = $this->is_user_directory($id);
			if (!empty($this->request['data']['Image'][0])) {
                //pr($this->request);die;
				foreach ($this->request['data']['Image'] as $i => $image) {
					//echo $image['attachment']['type'];
					if($image['attachment']['error'] == UPLOAD_ERR_OK){						
						if(in_array($image['attachment']['type'],$this->imageMimes)){
							if (is_array($this->request['data']['Image'][$i])) {
							  
								   $docDir = $this->is_user_image_directory($id);
								   $sr_no = $i + 1;
								   if(move_uploaded_file($image['attachment']['tmp_name'], $docDir.DS.$image['attachment']['name'])){
										$pdfData = array(
										  'foreign_key' => $id,
										   'model' => 'User',
										   'attachment' => $image['attachment']['name'],
										   'type' => $image['attachment']['type'],
										   'sr_no' => $sr_no,
										   );
										$AttachmentEntity = $this->Attachments->newEntity($pdfData);
										$AttachmentEntity = $this->Attachments->patchEntity($AttachmentEntity,$pdfData);
										if ($this->Attachments->save($AttachmentEntity)) {
											 $image['model'] = 'User';
											 // ***Unset the foreign_key if the user tries to specify it****
											 if (isset($image['foreign_key'])){unset($image['foreign_key']);}
											 $images[] = $image;
											 $fileSuccess[] = $image['attachment']['name']." uploaded successfully!"; 
										}
								   }
							}
							//pr($images);
						}elseif(in_array($image['attachment']['type'],$this->documentMimes)){
						 $docDir = $this->is_user_directory($id);
							if(move_uploaded_file($image['attachment']['tmp_name'], $docDir.DS.$image['attachment']['name'])){
								if(is_dir($docDir)){
									//echo "Directory Existing!!!";
								}
								$sr_no = $i + 1;
								//echo $docDir.DS.$image['attachment']['name'];die;
								//not working on server.
								$pdfData = array(
										'foreign_key' => $id,
										 'model' => 'User',
										 'attachment' => $image['attachment']['name'],
										 'type' => $image['attachment']['type'],
										  'sr_no' => $sr_no,
										 );
								$attachmentsEntity = $this->Attachments->newEntity($pdfData);
								$attachmentsEntity = $this->Attachments->patchEntity($attachmentsEntity,$pdfData);
                                //pr($attachmentsEntity);die;
								if ($this->Attachments->save($attachmentsEntity)) {
									$success = 1;
									$fileSuccess[] = $image['attachment']['name']." uploaded successfully!";									
								}else{
									$failure = 1;
									$fileErrors[] = $image['attachment']['name']." already existing. Not uploaded!";
								}								
							}else{
								$fileErrors[] = "Failed to upload ".$image['attachment']['name'];
							}
						}
					}else{
						if($image['attachment']['error'] == UPLOAD_ERR_NO_FILE){continue;}
						$fileErrors[] = $this->uploadErrorArr[$image['attachment']['error']];
					}
				}
                if(count($images)){				
                $data = array(
                          'User' => $user,
                          'Image' => $images
                    );
                }else{
                    $data = array(
                          'User' => $user
                    );
                }
                $fileErrorStr = implode("<br/>",$fileErrors);
                $fileSuccessStr = implode("<br/>",$fileSuccess);
                if(!empty($fileErrorStr))$fileErrorStr = "<br/>$fileErrorStr";
                if(!empty($fileSuccessStr))$fileSuccessStr = "<br/>$fileSuccessStr";
                
                $UsersEntity = $this->Users->get($id);
                $UsersEntity = $this->Users->patchEntity($UsersEntity,$data['User']);
                //pr($UsersEntity);die;
                
                if ($this->Users->save($UsersEntity) || !empty($success) || !empty($failure)) {
                    $this->Flash->success(__("The User has been saved.{$fileErrorStr}{$fileSuccessStr}"),['escape' => false]);
                    return $this->redirect(array('action' => 'index'));
                }
                
			}
			
			
	}
	
	private function is_user_directory($userID = null){
		if(!$userID)return false;
		$filesDir = WWW_ROOT.'files';
		$documentsDir = $filesDir.DS.'documents';
		$usersDir = $filesDir.DS.'documents'.DS.$userID;
		$dirArr = array($filesDir,$documentsDir,$usersDir);
		foreach($dirArr as $directory){
			if (!is_dir($directory)) {
				if(mkdir($directory)){
					if(!chmod($directory, 0755)){return false;}				
				}else{
					return false;
				}
			}
		}
		return $usersDir;		
	}
	
	private function is_user_image_directory($userID = null){
		if(!$userID)return false;
		$filesDir = WWW_ROOT.'files';
		$documentsDir = $filesDir.DS.'image';
		$usersDir = $filesDir.DS.'image'.DS.'attachment'.DS.$userID;
		$dirArr = array($filesDir,$documentsDir,$usersDir);
		
		foreach($dirArr as $directory){
		  
			if (!is_dir($directory)) {
				if(mkdir($directory)){
					if(!chmod($directory, 0755)){return false;}				
				}else{
					return false;
				}
			}
		}
		return $usersDir;		
	}
	
	
	function changePwd(){
		$userID = $this->request->session()->read('Auth.User.id');
		if ($this->request->is(array('post', 'put'))) {
			//$this->User->set(array('username' => ''));
			//$this->User->validates(array('fieldList' => array('username' => ''))); //no need
			//print_r($errors = $this->User->invalidFields());
			$user_query = $this->Users->find('all',array('fields' => array('password','username'),'conditions' => array('Users.id' => $userID)));
			$user_query = $user_query->hydrate(false);
			if(!empty($user_query)){
			   $user = $user_query->first();
			}else{
			   $user = array();
			}
			//echo "<pre>";
			//echo $user['User']['password']."\n";
			$checkpass = $this->encryptPassword($this->request->data['old_password'],trim($user['password']));
			
			if($checkpass){
				//Matching old password in the database with old_password submitted by user
				if($this->request->data['password'] == $this->request->data['confirm_password']){
					//password and confirm password matching
					$user['password'] = $this->request['data']['password'];
					$user['confirm_password'] = $this->request['data']['confirm_password'];
					$UsersEntity = $this->Users->get($userID);
					if ($user['password'] == $user['confirm_password']) {//array('fieldList' => array('password','confirm_password')
						//$errors = $this->User->invalidFields();
						unset($user['confirm_password']);
						$UsersEntity = $this->Users->patchEntity($UsersEntity,$user);
						if ($this->Users->save($UsersEntity)) {
                            //echo "hi";die;
							$this->Flash->success(__('Password updated.'));
							return $this->redirect(array('action' => 'change_pwd'));
						}else{
                            $errors = array();
                            if(!empty($UsersEntity->errors())){
                                //  pr($UsersEntity->errors());die;
                                foreach($UsersEntity->errors() as $errorkey => $errorvalue){
                                    foreach($errorvalue as $errorValueKey => $errorValuedata){
                                        $errors[] = $errorValuedata;
                                    }
                                }
                            }
                            $this->set('id', $userID);
                            if(!empty($errors)){
                                $error_data = implode(" ,",$errors);
                                $this->Flash->error(__($error_data));
                            }else{
                             $this->Flash->error(__('Password couldn\'t be updated. Please, try again.'));   
                            }
                            //echo "bye";die;
							
						}
					}else{
						//$errors = $this->User->validationErrors;
						$this->Flash->error(__('Password Mismatch. Please, try again.'));
					}
				}else{
					//flash message password confirm password mismatch
					//flash message to user that old password not matching
					$this->Flash->error(__('Password, confirm password mismatch'));
					return $this->redirect(array('action' => 'change_pwd'));
				}
			}else{
				//flash message to user that old password not matching
				$this->Flash->error(__('Old password mismatch'));
				return $this->redirect(array('action' => 'change_pwd'));
			}		
		} else {
			$options = array('conditions' => array('Users.id' => $userID));
			$user_query = $this->Users->find('all', $options);
			$user_query = $user_query->hydrate(false);
			if(!empty($user_query)){
			   $user = $user_query->first();
			}else{
			   $user = array();
			}
			$this->request->data = $user;
			$this->request->data['password'] = "";
			//echo $userID;die;
			$this->set('id', $userID);
			$this->set('data',$this->request->data);
		}
	}
	public function encryptPassword($password = null,$hashedPassword="") {
		   return $res = (new DefaultPasswordHasher)->check($password,$hashedPassword) ;
		 //return (new DefaultPasswordHasher)->hash($password);
	}
	
	function changePassword($id = null){
		if (!$this->Users->exists($id)) {
			throw new NotFoundException(__('Invalid user'));
		}
		$user_entity = $this->Users->get($id);
		if ($this->request->is(array('post', 'put'))) {
			$user_query1 = $this->Users->find('all',array('conditions' => array('id' => $id)));
			$user_query1 = $user_query1->hydrate(false);
			if(!empty($user_query1)){
			   $user1 = $user_query1->first();
			}else{
			   $user1 = array();
			}
			$user1['password'] = $this->request->data['password'];
			$user1['confirm_password'] = $this->request->data['confirm_password'];			
			if ($user1['password'] == $user1['confirm_password']) {//array('fieldList' => array('password','confirm_password')
			   unset($user1['confirm_password']);
			   $user_entity = $this->Users->patchEntity($user_entity,$user1);//,['validate' => false]
				if ($this->Users->save($user_entity)) {
					$this->Flash->success(__('The user has been saved.'));
					return $this->redirect(array('action' => 'index'));
				}else{
					$this->Flash->error(__('The user could not be saved. Please, try again.'));
				}
			}else{
				
				$errorArr[] = 'Password Mismatch';
				$errorStr = implode("<br />",$errorArr);
				$this->Flash->error("$errorStr. Please, try again.",['validate' => false]);
			}
		} else {
			$options = array('conditions' => array('id' => $id));
			   $user_query = $this->Users->find('all', $options);
			   $user_query = $user_query->hydrate(false);
			   if(!empty($user_query)){
					$user = $user_query->first();
			   }else{
					$user = array();
			   }
			$this->request->data = $user;
			$this->request->data['User']['password'] = "";
		}
	}
	
	function getChildren($parent_id) {
		  $tree = Array();
		  if (!empty($parent_id)) {
			  $tree = $this->getOneLevel($parent_id);
			  foreach ($tree as $key => $val) {
				  $ids = $this->getChildren($val);
					$tree = array_merge($tree, $ids);
			  }
		  }
		  return $tree;
	 }
	 
	 function getOneLevel($catId){
		  $cat_id = array();
		  $res = $this->Users->find('all',array('conditions' => array('parent_id' => $catId)))->toArray();
		  if(!empty($res)){
			   foreach($res as $key => $value){
					$cat_id[] = $value->id;	
			   }
		  }
		  return $cat_id;
	  }
	  
	   public function get_related_kiosks($userEntity){
		  $id = $userEntity->id;
		  $parent_id = $userEntity->parent_id;
		  $group_id = $userEntity->level;
		  $childs = $this->getChildren($parent_id);
		  //pr($childs);
		  if(array_key_exists($id,$childs)){
			   unset($childs[$id]);
		  }
		  if(empty($childs)){
			   $childs = array(0 => null);
		  }
		  $same_level_users = $this->Users->find('list',array('conditions' => array(
																'id IN' => $childs,
																'level' => $group_id,
																)))->toArray();
		  $id_kiosk_array = array();
		  //pr($same_level_users);die;
		  foreach($same_level_users as $key => $value){
			   if($value == $id){
					continue;
			   }
			   $get_kiosks = $this->Users->find('all',array('conditions' => array(
																	  'id' => $value,
																	  )))->first();
			   if(!empty($get_kiosks)){
					if(!empty($get_kiosks->kiosk_assigned)){
							  $id_kiosk_array[$value] = explode("|",$get_kiosks->kiosk_assigned);	 	  
					}
			   }
		  }
		  //pr($id_kiosk_array);die;
		  $parent_kiosks = $this->Users->find('all',array('conditions' => array(
																	  'id' => $parent_id,
																	  )))->first();
		  $parent_kiosks_assigned = array();
		  if(!empty($parent_kiosks)){
			   if(!empty($parent_kiosks->kiosk_assigned)){
					$parent_kiosks_assigned = explode("|",$parent_kiosks->kiosk_assigned);	 		
			   }
		  }
		  
		  
		  $tem_arr = array();
		  if(empty($id_kiosk_array)){
			   $tem_arr = $parent_kiosks_assigned;
		  }else{
			   foreach($id_kiosk_array  as $s_key => $s_value){
					if(empty($tem_arr)){
						 $tem_arr = array_diff($parent_kiosks_assigned,$s_value);	
					}else{
						 $tem_arr = array_diff($tem_arr,$s_value);
					}
				 
			   }   
		  }
		  
		  return $tem_arr;
		  //pr($tem_arr);die;   
	  }
	  
	  public function same_group_other_user($user){
		  if(!empty($user)){
			   $group_id = $user['level'];
			   $id = $user['id'];
			   $user_parent_id = $user['parent_id'];
			   $parent_id_not_in = array($id,0,$user_parent_id);
			//   $other_group_ids = $this->Users->find('list',array('conditions' => array(
			//														'group_id >' => $group_id,
			//														'parent_id NOT IN'=> $parent_id_not_in,
			//														),
			//													  'keyField' => 'id',
			//													 'valueField' => 'username'
			//													 )
			//										 )->toArray();
			   $other_group_ids = $this->Users->find('list',array('conditions' => array(
																	'level' => $group_id,
																	'id NOT IN' => array($id),
																	//'parent_id NOT IN'=> $parent_id_not_in,
																	),
																  'keyField' => 'id',
																 'valueField' => 'username'
																 )
													 )->toArray();
			   
			  $childs =  $other_ids = array();
			   foreach($other_group_ids as $user_id => $value){
					$childs[] = $this->getChildren($user_id);
			   }
			   foreach($childs as $s => $value){
					foreach($value as $k=>$id_to_add){
						$other_ids[$id_to_add] = $id_to_add; 
					}
			   }
			   //pr($other_ids);die;
			   //pr($other_ids);die;
			   if(empty($other_ids)){
					$other_ids = array(0 => null);
			   }
			   $parent_id_data = $this->Users->find('list',array('conditions' => array(
																	'id IN ' => $other_ids,
																	),
																  'keyField' => 'id',
																 'valueField' => 'parent_id'
																 )
													 )->toArray();
			   return $parent_id_data;
		  }
	}
	
	 function getParent($id,$data = array()) {
		  if (!empty($id)) {
			  if($parentID = $this->getOneParent($id)){
					$data[] = $parentID;
					return $this->getParent($parentID,$data);
			  }else{
					if(is_array($data)){
						 return $data;	
					}
			  }
		  }else{
			   return $data;
		  }
	 }
	 
	 
	 function getOneParent($catId){
		  $cat_id = 0;
		  $res = $this->Users->find('all',array('conditions' => array('id' => $catId)))->toArray();
		  if(!empty($res)){
			   foreach($res as $key => $value){
					$cat_id =  $value->parent_id;	
			   }
		  }
		  return $cat_id;
	  }
	  
	   public function revert(){
		  if(!empty($_REQUEST)){
			   if(array_key_exists('id',$_REQUEST)){
					$id = $_REQUEST['id'];
			   }else{
					$id = "";
			   }
			   if(array_key_exists('assigned_user_id',$_REQUEST)){
					$assigned_user_id = $_REQUEST['assigned_user_id'];
			   }else{
					$assigned_user_id = "";
			   }
			   if(!empty($id) && !empty($assigned_user_id)){
					 $v_id = $this->Users->get($assigned_user_id);
					 $assosited_ids = $this->getChildren($assigned_user_id);
					 if(!empty($assosited_ids)){
						 foreach($assosited_ids as $key => $value){
							  $get_id = $this->Users->get($value);
							  $data_to_save = array('parent_id' => $v_id['parent_id']);
							  $usersEntity_assigend = $this->Users->patchEntity($get_id,$data_to_save);
							  $this->Users->save($usersEntity_assigend);
						  }	 
					 }
					$data_to_save = array('parent_id' => $id);
					$usersEntity_assigend_v = $this->Users->patchEntity($v_id,$data_to_save);
					if($this->Users->save($usersEntity_assigend_v)){
						$success = array("msg" => "success");
						echo json_encode($success);die;
					}
			   }else{
					$success = array("msg" => "id or assigned id is missing");
					echo json_encode($success);die;
			   }
			   
		  }else{
			   $success = array("msg" => "no parem found");
			   echo json_encode($success);die;
		  }
	  }
	  
	  public function create_user_for_dr5($user_data,$profileData){
		  $username = $user_data['username'];
		  $email = $user_data['email'];
		  if (strpos($username, QUOT_USER_PREFIX) !== false) {
			   $without_dr5_username =  str_replace(QUOT_USER_PREFIX,"",$username);
			   $string_pos = strpos($email,"@");
			   $newstr = substr_replace($email, "_1", $string_pos, 0);
			   
			   $check_data = $this->Users->find("all",['conditions' => [
														  'username' => $without_dr5_username,
														  ]])->toArray();
			   if(empty($check_data)){
					$user_data['username'] = $without_dr5_username;
					$user_data['email'] = $newstr;
					$user_entity = $this->Users->newEntity();
					$user_entity = $this->Users->patchEntity($user_entity, $user_data);
					if($this->Users->save($user_entity)){
						 $user_id = $user_entity->id;
						 $profileData['user_id'] = $user_id;
						 $ProfilesEntity = $this->Profiles->newEntity($profileData,['validate' => false]);
						 $ProfilesEntity = $this->Profiles->patchEntity($ProfilesEntity,$profileData,['validate' => false]);
						 $this->Profiles->save($ProfilesEntity);
						 return true;
					}else{
						 pr($user_entity->errors());
					}
			   }else{
					$id = $check_data[0]->id;
					$users = $this->Users->get($id);
					$data = ["password" => $user_data['password']];
					$user_entity = $this->Users->patchEntity($users, $data);
					if($this->Users->save($user_entity)){
						 return true;
					}else{
						 pr($user_entity->errors());
					}
			   }
		  }else{
			return true;	 
		  }  
	  }
	  
	  
	  public function deleteImage(){
		  $user_id = $this->request->query["user_id"];
		  $attachment_id = $this->request->query["att_id"];
		  $get_data = $this->Attachments->get($attachment_id);
		  $type = $get_data->type;
		  $name = $get_data->attachment;
		  
		  if($type == "image/jpeg"){
			   $filesDir = WWW_ROOT.'files';
			   $usersDir = $filesDir.DS.'image'.DS.'attachment'.DS.$user_id;
			   if (is_dir($usersDir)) {
					unlink($usersDir.DS.$name);
			   }
		  }else if($type =="application/pdf"){
			   $filesDir = WWW_ROOT.'files';
			   $usersDir = $filesDir.DS.'documents'.DS.$user_id;
			   if (is_dir($usersDir)) {
					unlink($usersDir.DS.$name);
			   }
		  }
		  if($this->Attachments->delete($get_data)){
			   $msg = array("msg" => "Deleted Successfully");
		  }else{
			   $msg = array("msg" => "Delete Operation Failed");
		  }
			   echo json_encode($msg);
		  die;
	  }
	  
	  
}



