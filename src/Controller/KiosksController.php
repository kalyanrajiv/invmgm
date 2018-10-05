<?php
namespace App\Controller;
use Cake\Core\Configure;
use Cake\Core\Configure\Engine\PhpConfig;
use Cake\ORM\TableRegistry;
use Cake\ORM\Entity; 
use App\Controller\AppController;

 use Cake\Datasource\ConnectionManager;
class KiosksController extends AppController
{
 
     public $helpers = [
         'Paginator' => ['templates' => 'paginatortemplates']
         ];
    public function initialize(){
        parent::initialize();
		//Configure::load('common-arrays');
        $activeOptions = Configure::read('active');
		$featuredOptions = Configure::read('featured');
		$repairOptions = Configure::read('repair_statuses');
		$contractOptions = Configure::read('contract_type');
		$renewalweekOptions = Configure::read('renewal_weeks');
		$renewalmonthOptions = Configure::read('renewal_months');
		$kioskTypeOptions = Configure::read('kiosk_type');
		$countryOptions = Configure::read('uk_non_uk');
		$this->set(compact('activeOptions', 'featuredOptions', 'repairOptions', 'contractOptions'));
		$this->set(compact('renewalweekOptions','renewalmonthOptions','kioskTypeOptions','countryOptions'));
        $this->loadModel('Kiosks');
        $this->loadModel('KioskTimings');
		$this->loadModel('DailyTargets');
		$this->loadModel('Settings');
        $CURRENCY_TYPE = Configure::read('CURRENCY_TYPE');
		$this->set(compact('CURRENCY_TYPE'));
    }
    
    
    public function index()
    {
	 //$this->loadComponent('Barcode');
	 //$res = $this->Barcode->generate_bar_code('081231723897');
	 //echo $res;die;
        $kiosks_query = $this->paginate($this->Kiosks);
        if(!empty($kiosks_query)){
            $kiosks = $kiosks_query->toArray();
        }else{
            $kiosks = array();
        }
        $this->set(compact('kiosks'));
        $this->set('_serialize', ['kiosks']);
    }

    /**
     * View method
     *
     * @param string|null $id Kiosk id.
     * @return \Cake\Network\Response|null
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view($id = null)
    {
        $kiosk = $this->Kiosks->get($id, [
            'contain' => ['Customers','MobileRepairs','ReorderLevels']
        ]);
//pr($kiosk);die;
        $this->set('kiosk', $kiosk);
        $this->set('_serialize', ['kiosk']);
    }

     
    public function add()
    {
		  $setting_arr = $this->Settings->find("list",[
										'keyField' =>"attribute_name",
										'valueField' => "attribute_value",
										])->toArray();
		  
         $kiosks_query = $this->Kiosks->find('all',array(
									'conditions' => array('id NOT IN'=>'10000' ),
                                    'limit'=>1,
                                    'order' =>  'id DESC' 
                                  )  );
      //   pr($kiosks_query);
            $kiosks_query = $kiosks_query->hydrate(false);
            if(!empty($kiosks_query)){
                $kiosks = $kiosks_query->toArray();
            }else{
                $kiosks = array();
            }
      
       $kiosk = $this->Kiosks->newEntity( );
        if ($this->request->is('post')) {
		  
            if(!empty($kiosks)){
                $kiosks_id = $kiosks['0']['id']+1;
            }
            $this->request->data['id'] = $kiosks_id;
 
              $kiosk = $this->Kiosks->newEntity($this->request->data,array('accessibleFields' => ['id' => true]));
           // pr($kiosk);die;
             $kiosk_entity = $this->Kiosks->patchEntity($kiosk, $this->request->data);
            // pr($kiosk_entity);die;
		 if ($this->Kiosks->save($kiosk_entity)) {
		  $id = $kiosk_entity->id;
		  $imageName = $this->request->data['logo_image'];
			   if(!empty($imageName)){
						 if(mkdir(WWW_ROOT."logo/{$id}")){
							  if(move_uploaded_file($_FILES['logo_image']['tmp_name'],
													WWW_ROOT."logo/{$id}/".$_FILES['logo_image']['name'])){
								   //$query = "UPDATE products SET image_dir = {$productId} where id = {$productId}";
								   //$query2 = "UPDATE products SET image = '$imageName' WHERE id = $productId";
								   //$conn = ConnectionManager::get('default');
								   //$stmt = $conn->execute($query);
								   //$stmt = $conn->execute($query2);
							  }
						 }
					   }
                $this->Flash->success(__('The kiosk has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The kiosk could not be saved. Please, try again.'));
        }
        $this->set(compact('kiosk','setting_arr'));
        $this->set('_serialize', ['kiosk'],'kiosk_id');
    }

    /**
     * Edit method
     *
     * @param string|null $id Kiosk id.
     * @return \Cake\Network\Response|null Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Network\Exception\NotFoundException When record not found.
     */
    public function edit($id = null)
    {
	  $setting_arr = $this->Settings->find("list",[
										'keyField' =>"attribute_name",
										'valueField' => "attribute_value",
										])->toArray();
	 
	if($id == 10000 && $this->request->session()->read('Auth.User.group_id') != ADMINISTRATORS ){
      $this->Flash->error(__('You are not allowed to edit warehouse kiosk.'));
      return $this->redirect(['action' => 'index']);
    }
        $kiosk = $this->Kiosks->get($id, [
            'contain' => []
        ]);
        if ($this->request->is(['patch', 'post', 'put'])) {
		  $imageName = $this->request->data['logo_image'];
			   if(!empty($imageName)){
					if(file_exists(WWW_ROOT."logo/{$id}")){
						 if(move_uploaded_file($_FILES['logo_image']['tmp_name'],
													WWW_ROOT."logo/{$id}/".$_FILES['logo_image']['name'])){
							  }
					}else{
						 if(mkdir(WWW_ROOT."logo/{$id}")){
							  if(move_uploaded_file($_FILES['logo_image']['tmp_name'],
													WWW_ROOT."logo/{$id}/".$_FILES['logo_image']['name'])){
							  }
						 }
					}
						 
					   }
					   if(!empty($this->request->data['logo_image']['name'])){
							  $logo_img = $this->request->data['logo_image']['name'];
							  unset($this->request->data['logo_image']);
							  $this->request->data['logo_image'] = $logo_img;
					   }else{
							  $this->request->data['logo_image'] = $kiosk->logo_image;
					   }
            $kiosk = $this->Kiosks->patchEntity($kiosk, $this->request->data);
            if ($this->Kiosks->save($kiosk)) {
                $this->Flash->success(__('The kiosk has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The kiosk could not be saved. Please, try again.'));
        }
        $this->set(compact('kiosk','setting_arr'));
        $this->set('_serialize', ['kiosk']);
    }

    /**
     * Delete method
     *
     * @param string|null $id Kiosk id.
     * @return \Cake\Network\Response|null Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete($id = null)
    {
        $this->request->allowMethod(['post', 'delete']);
        $kiosk = $this->Kiosks->get($id);
        if ($this->Kiosks->delete($kiosk)) {
            $this->Flash->success(__('The kiosk has been deleted.'));
        } else {
            $this->Flash->error(__('The kiosk could not be deleted. Please, try again.'));
        }

        return $this->redirect(['action' => 'index']);
    }
    
    public function editTiming($id = null) {
		$kiosk = $this->KioskTimings->get($id);
        $this->set(compact('kiosk'));
		if ($this->request->is(array('post', 'put'))){
			$patchEntity = $this->KioskTimings->patchEntity($kiosk, $this->request->data);
			if ($this->KioskTimings->save($patchEntity)) {
				return $this->redirect(array('action' => 'index'));
			} else {
				$this->Flash->success(__('The kiosk could not be saved. Please, try again.'));
			}
		} else {
			$options = array('conditions' => array('KioskTimings.kiosk_id' => $id));
            $KioskTimings_query = $this->KioskTimings->find('all', $options);
            $KioskTimings_query = $KioskTimings_query->hydrate(false);
            if(!empty($KioskTimings_query)){
                $KioskTimings = $KioskTimings_query->toArray();
            }else{
                $KioskTimings = array();
            }
			$this->request->data = $KioskTimings;
		}
	}
    
    public function export(){
		 $conditionArr = array();
		if(array_key_exists('search_kw',$this->request->query)){
			$conditionArr = $this->generate_condition_array();
		}
		if(count($conditionArr)>=1){
			$kiosks_query = $this->Kiosks->find('all',array(
									'conditions' => $conditionArr));
            $kiosks_query = $kiosks_query->hydrate(false);
            if(!empty($kiosks_query)){
                $kiosks = $kiosks_query->toArray();
            }else{
                $kiosks = array();
            }
		}else{
			$kiosks_query = $this->Kiosks->find('all');
            $kiosks_query = $kiosks_query->hydrate(false);
            if(!empty($kiosks_query)){
                $kiosks = $kiosks_query->toArray();
            }else{
                $kiosks = array();
            }
		}
		
		$tmpkiosks = array();
		foreach($kiosks as $key => $kiosk){
		 $tmpkiosks[] = $kiosk;
		}
		$this->outputCsv('kiosks_'.time().".csv" ,$tmpkiosks);
		$this->autoRender = false;
	}
	
	function intializeDailyTargets4Month(){
		$daysInMonth = date('t');
		$kiosks_query = $this->Kiosks->find('list',array(
							'keyField' => 'id',
							'valueField' => 'code',
							'conditions' => array('Kiosks.status' => 1,'Kiosks.kiosk_type' => 1)
								     )
						     );
		$kiosks_query = $kiosks_query->hydrate(false);
		if(!empty($kiosks_query)){
			$kiosks = $kiosks_query->toArray();
		}else{
			$kiosks = array();
		}
		foreach($kiosks as $kioskId=>$kioskCode){
		    $kioskTargetData_query = $this->Kiosks->find('all',array('conditions'=>array('Kiosks.id'=>$kioskId),'fields'=>array('id','target','target_mon','target_tue','target_wed','target_thu','target_fri','target_sat','target_sun')));
		    $kioskTargetData_query = $kioskTargetData_query->hydrate(false);
			if(!empty($kioskTargetData_query)){
				$kioskTargetData = $kioskTargetData_query->first();
			}else{
				$kioskTargetData = array();
			}
			
			for($i = 1; $i <= $daysInMonth; $i++){
				//$this->DailyTargets->clear();
				$date = date("Y-m-d",strtotime(date("Y-m-$i")));
				$checkDuplicate_query = $this->DailyTargets->find('all',array('conditions'=>array('DATE(DailyTargets.target_date)'=>$date,'DailyTargets.kiosk_id'=>$kioskId),'fields'=>array('DailyTargets.id')));
				$checkDuplicate_query = $checkDuplicate_query->hydrate(false);
				if(!empty($checkDuplicate_query)){
					$checkDuplicate = $checkDuplicate_query->first();
				}else{
					$checkDuplicate = array();
				}
				$weekDay = date('l',strtotime("$date"));
				if($weekDay=='Monday'){
				    $target = "target_mon";
				}elseif($weekDay=='Tuesday'){
				    $target = "target_tue";
				}elseif($weekDay=='Wednesday'){
				    $target = "target_wed";
				}elseif($weekDay=='Thursday'){
				    $target = "target_thu";
				}elseif($weekDay=='Friday'){
				    $target = "target_fri";
				}elseif($weekDay=='Saturday'){
				    $target = "target_sat";
				}elseif($weekDay=='Sunday'){
				    $target = "target_sun";
				}
				
				$eachDayTarget = $kioskTargetData[$target]*$kioskTargetData['target']/100;
				if($checkDuplicate){
				//    $dailyTargetData = array(
				//		    'id' => $checkDuplicate['DailyTarget']['id'],
				//		    'target' => $eachDayTarget,
				//			 );
				
				$data = array('target'=>$eachDayTarget);
				$getId = $this->DailyTargets->get($checkDuplicate['id']);
				$patchEntity = $this->DailyTargets->patchEntity($getId,$data);
				$saveEntity = $this->DailyTargets->save($patchEntity);
				
				//$this->DailyTargets->updateAll(array('target' => "'$eachDayTarget'"),
				//			      array('id' => $checkDuplicate['id'])
				//			      );
				//pr($query);die;
				}else{
				    $dailyTargetData = array(
						    'kiosk_id' => $kioskId,
						    'target' => $eachDayTarget,
						    'target_date' => $date
							 );
				    
					$DailyTargetsEntity = $this->DailyTargets->newEntity($dailyTargetData,['validate' => false]);
					$DailyTargetsEntity = $this->DailyTargets->patchEntity($DailyTargetsEntity,$dailyTargetData,['validate' => false]);
					$this->DailyTargets->save($DailyTargetsEntity);
				}
			}
		}
		
		$this->Flash->error('Target for all kiosks have been updated');
		return $this->redirect(array('action'=>'index'));
	}
	
	public function search($keyword = ""){
		$conditionArr = $this->generate_condition_array();
		$searchKW = $this->request->query['search_kw'];
		$this->paginate = array(
					'conditions' => $conditionArr,
					'limit' => 10
			
		);
		
		$kiosks = $this->paginate('Kiosks');
		$this->set(compact('kiosks'));
		//$this->layout = 'default';
		//$this->viewPath = 'Kiosks';
		$this->render('index');
	}
	
	
	private function generate_condition_array(){
		$searchKW = trim(strtolower($this->request->query['search_kw']));
		$conditionArr = array();
		if(!empty($searchKW)){
			$conditionArr['Kiosks.name like '] =  strtolower("%$searchKW%");
		}
		return $conditionArr;
	}
	
}
