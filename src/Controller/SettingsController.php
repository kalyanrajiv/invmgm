<?php
namespace App\Controller;
use App\Controller\AppController;
use Cake\ORM\Table;
use Cake\Core\Configure;
use Cake\Core\Configure\Engine\PhpConfig;
use Cake\Datasource\ConnectionManager;
class SettingsController extends AppController{

     public $helpers = [
         'Paginator' => ['templates' => 'paginatortemplates']
         ];
    public function index() {
         $this->loadModel('Users');
         $this->loadModel('Kiosks');
         $query = $this->Users->find('list', [
            'keyField' => 'id',
            'valueField' => 'username'
         ]);
        
        $users = $query->toArray();
		 $this->paginate = [
						  'conditions' => [
							  'internal_setting' => 0
						 ],
                          'limit' => 50,
                    ];
        $settings = $this->paginate($this->Settings);
        $this->set(compact('settings'));
         $this->set(compact('users'));
        $this->set('_serialize', ['settings']);
    }

    public function view($id = null){
        $setting = $this->Settings->get($id, [
            'contain' => []
        ]);

        $this->set('setting', $setting);
        $this->set('_serialize', ['setting']);
    }

    public function add(){
        $setting = $this->Settings->newEntity();
        if ($this->request->is('post')) {
           // pr($this->request);die;
            $setting = $this->Settings->patchEntity($setting, $this->request->data);
            if ($this->Settings->save($setting)) {
                $this->Flash->success(__('The setting has been saved.'));

                return $this->redirect(['action' => 'index']);
            }else{
			   
			}
            $this->Flash->error(__('The setting could not be saved. Please, try again.'));
        }
        $this->set(compact('setting'));
        $this->set('_serialize', ['setting']);
    }

    public function edit($id = null) {
	$this->loadModel('Brands');
	$this->loadModel('MobileModels');
		if (!$this->Settings->exists($id)) {
			throw new NotFoundException(__('Invalid setting'));
		}else{
          $set_res = $this->Settings->get($id);
          $this->set('set_entity', $set_res);
        }
		if ($this->request->is(array('post', 'put'))) {
			if($this->request['data']['attribute_name']=='internal_repair_default_cost' ){
				$brandId_query = $this->Brands->find('list', array('conditions' => array('Brands.brand' => 'Internal Repair/Unlock'),
                                                                   'keyField' => 'id',
                                                                   'valueField' => 'id',
                                                                   ));
                $brandId_query = $brandId_query->hydrate(false);
                if(!empty($brandId_query)){
                    $brandId  = $brandId_query->toArray();
                 }else{
                    $brandId = array();
                }
				$modelId_query = $this->MobileModels->find('list', array('conditions' => array('MobileModels.model' => 'Internal Repair/Unlock'),
                                                                   'keyField' => 'id',
                                                                   'valueField' => 'id',
                                                                   ));
                $modelId_query = $modelId_query->hydrate(false);
                if(!empty($modelId_query)){
                    $modelId = $modelId_query->toArray();
                }else{
                    $modelId = array();
                }
				$conn = ConnectionManager::get('default');
                $stmt = $conn->execute('SELECT NOW() as created'); 
                $currentTimeInfo = $stmt ->fetchAll('assoc');
                
				$currentTime = $currentTimeInfo[0]['created'];
				
				$date_time = date('Y-m-d H:i:s',strtotime($currentTime));
				$repairCost = $this->request['data']['attribute_value'];
				if($this->MobileRepairPrices->updateAll(array('repair_cost' => "'$repairCost'", 'modified' => "'$date_time'"), array('brand_id IN' => $brandId, 'mobile_model_id IN' => $modelId))){
					$SettingsEntity = $this->Settings->get($id);
                    $data = array('attribute_value' => $repairCost);
                    $SettingsEntity = $this->Settings->patchEntity($SettingsEntity,$data,['validate' => false]);
					$this->Settings->save($SettingsEntity);
					$this->Flash->success("Cost price for internal repair has been updated");
					return $this->redirect(array('action'=>'index'));
				}
			}
			if($this->request['data']['attribute_name']=='internal_unlock_default_cost' ){
				$brandId_query = $this->Brands->find('list', array('conditions' => array('Brands.brand' => 'Internal Repair/Unlock'),
                                                                   'keyField' => 'id',
                                                                   'valueField' => 'id',
                                                                   ));
                $brandId_query = $brandId_query->hydrate(false);
                if(!empty($brandId_query)){
                    $brandId  = $brandId_query->toArray();
                 }else{
                    $brandId = array();
                }
				$modelId_query = $this->MobileModels->find('list', array('conditions' => array('MobileModels.model' => 'Internal Repair/Unlock'),
                                                                   'keyField' => 'id',
                                                                   'valueField' => 'id',
                                                                   ));
                $modelId_query = $modelId_query->hydrate(false);
                if(!empty($modelId_query)){
                    $modelId = $modelId_query->toArray();
                }else{
                    $modelId = array();
                }
				$conn = ConnectionManager::get('default');
                $stmt = $conn->execute('SELECT NOW() as created'); 
                $currentTimeInfo = $stmt ->fetchAll('assoc');
                
				$currentTime = $currentTimeInfo[0]['created'];
				$date_time = date('Y-m-d H:i:s',strtotime($currentTime));
				$unlockCost = $this->request['data']['attribute_value'];
				if($this->MobileUnlockPrices->updateAll(array('unlocking_cost' => "'$unlockCost'", 'modified' => "'$date_time'"), array('brand_id IN' => $brandId, 'mobile_model_id IN' => $modelId))){
					$SettingEntity = $this->Settings->get($id);
                    $data = array('attribute_value', $unlockCost);
                    $SettingEntity = $this->Settings->patchEntity($SettingEntity,$data,['validate' => false]);
					$this->Settings->save($SettingEntity);
					$this->Flash->success("Cost price for internal unlock has been updated");
					return $this->redirect(array('action'=>'index'));
				}
			}
			if($this->request['data']['attribute_name']=='function_test_notification' || $this->request['data']['attribute_name']=='phone_condition_notification' ){
			   
				if($this->request['data']['attribute_value'] == 'active'){
					
						$this->Settings->updateAll(
									array('attribute_value' => "active",
										  'comment' => $this->request['data']['comment']
										  ),
									array('id'=> $id)
								);
						
						$this->Flash->success("Notification is activated");
						return $this->redirect(array('action'=>'index'));
				}else{
					$this->Settings->updateAll(
					array('attribute_value' => "inactive"),
					array('id'=> $id)
							);
					$this->Flash->success("Notification is deactivated");
					return $this->redirect(array('action'=>'index'));
				}
			}
			if($this->request['data']['attribute_name']=='logo_image'){
				$webroot = WWW_ROOT;//basepath
				$dir = $webroot."img".DS;
				if(!empty($this->request->data['upload']['name'])){
					$fileName = $this->request->data['upload']['name'];
					$targetFile = $dir.$fileName;
					//if(file_exists($targetFile)){
					//	$this->Session->setFlash('Image already exists!');
					//	return $this->redirect(array('action'=>'edit',$id));
					//}
					if (move_uploaded_file($this->request->data['upload']['tmp_name'], $targetFile)) {
						// File and new size
						$imageFileType = pathinfo($targetFile,PATHINFO_EXTENSION);
						$imageSizeDetail = getimagesize($targetFile);
						$width = $imageSizeDetail[0];
						$height = $imageSizeDetail[1];
						$filename = $targetFile;
						
						// Load
						$newwidth = "200";
						$newheight = "141";
						
						$thumb = imagecreatetruecolor($newwidth, $newheight);
						$save = 0;
						// Content type
						if($imageFileType=="png"){
							header('Content-Type: image/png');
							$source = imagecreatefrompng($filename);
							imagecopyresampled($thumb, $source, 0, 0, 0, 0, $newwidth, $newheight, $width, $height);
							//output
							imagepng($thumb,$filename);//, ,
                            $conn = ConnectionManager::get('default');
                            $stmt = $conn->execute("UPDATE `settings` SET `attribute_value`='$fileName' WHERE `id`='$id'"); 
							//$this->Setting->query("UPDATE `settings` SET `attribute_value`='$fileName' WHERE `id`='$id'");
							$save = 1;
						}elseif($imageFileType=="jpg" || $imageFileType=="jpeg"){
							header('Content-Type: image/jpeg');
							$source = imagecreatefromjpeg($filename);
							imagecopyresampled($thumb, $source, 0, 0, 0, 0, $newwidth, $newheight, $width, $height);
							//output
							imagejpeg($thumb, $filename, 100);
                            
                            $conn = ConnectionManager::get('default');
                            $stmt = $conn->execute("UPDATE `settings` SET `attribute_value`='$fileName' WHERE `id`='$id'"); 
							$save = 1;
						}else{
							$this->Flash->error('Please upload image with either "jpg" or "png" extension.');
							return $this->redirect(array('action'=>'edit',$id));
						}
						
						if($save==1){
							$this->Flash->error("The file ". $fileName. " has been uploaded.");
							return $this->redirect(array('action'=>'edit',$id));
						}
					} else {
						$this->Flash->error('Sorry, there was an error uploading your file!');
						return $this->redirect(array('action'=>'edit',$id));
					}
				}
			}
			
			if($this->request['data']['attribute_name']=='product_request_users' ){
				$data = $this->request->data;
				//pr($data);die;
				if(count($data['data']['Message']['sent_to_id']) == 0){
					$this->Flash->error('Please choose users from dropdown!');
					return $this->redirect(array('action' => 'edit', $id));
				}else{
					$strToSave = implode('|',$data['data']['Message']['sent_to_id']);
					$this->request->data['attribute_value'] = $strToSave;
				}
			}
				//continue;
                $set_res = $this->Settings->patchEntity($set_res,$this->request->data,['validate' => false]);
			if ($this->Settings->save($set_res)) {
				if($this->request['data']['attribute_name']=='warehouse_target'){
					$weeklyTarget = $this->request['data']['attribute_value'];
					$kiosk_data = array(
							'id' => '10000', // id for warehouse
							'target' => $weeklyTarget,
							'target_mon' => 16,
							'target_tue' => 16,
							'target_wed' => 17,
							'target_thu' => 17,
							'target_fri' => 17,
							'target_sat' => 0,
							'target_sun' => 17,
							'contact' => '99999999999'//data does not get saved without contact number
							    );
					$KiosksEntity = $this->Kiosks->newEntity($kiosk_data,['validate' => false]);
                    $KiosksEntity = $this->Kiosks->patchEntity($KiosksEntity,$kiosk_data,['validate' => false]);
					$this->Kiosks->save($KiosksEntity);
				}
				$this->Flash->success('The setting has been saved.');
				return $this->redirect(array('action' => 'index'));
			} else {
				$this->Flash->success(__('The setting could not be saved. Please, try again.'));
			}
		} else {
			$options = array('conditions' => array('Settings.id' => $id));
            $res_query = $this->Settings->find('all', $options);
            $res_query = $res_query->hydrate(false);
            if(!empty($res_query)){
               $res = $res_query->first();
            }else{
               $res = array();
            }
			$this->request->data = $res;
			if($this->request['data']['attribute_name']=='product_request_users'){
				$userEmails = $this->Users->find('list',array(
                                                             'keyField' => 'id',
                                                             'valueField' => 'username',
                                                             //'fields' => array('id','username'),
                                                  'conditions' => array('Users.group_id IN' => array(MANAGERS, ADMINISTRATORS, inventory_manager))
                                                  ));
				$this->set(compact('userEmails'));
			}
			//print_r($this->request->data);
		}
	}

    public function delete($id = null){
        $this->request->allowMethod(['post', 'delete']);
        $setting = $this->Settings->get($id);
        if ($this->Settings->delete($setting)) {
            $this->Flash->success(__('The setting has been deleted.'));
        } else {
            $this->Flash->error(__('The setting could not be deleted. Please, try again.'));
        }

        return $this->redirect(['action' => 'index']);
    }
}
