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
/**
 * MobileUnlockPrices Controller
 *
 * @property \App\Model\Table\MobileUnlockPricesTable $MobileUnlockPrices
 */
class MobileUnlockPricesController extends AppController
{
   public $helpers = [
         'Paginator' => ['templates' => 'paginatortemplates']
         ];
   public function initialize(){
        parent::initialize();
        $paymentType=Configure::read('payment_type');
        $this->set(compact('paymentType'));
        $this->loadComponent('ScreenHint');
        $this->loadModel('Brands');
        $this->loadModel('MobileModels');
        $this->loadModel('Networks');
        $this->loadModel('Users');
		$this->loadModel('KioskProductSales');
        $this->loadModel('ProductPayments');
        $this->loadComponent('Pusher');
        
        //Configure::load('common-arrays');
		$statusOptions = Configure::read('active');
        //pr($statusOptions);die;
		//$this->Auth->allow('export','search','edit_grid','unlock_price_push_notification');
		$this->set(compact('statusOptions'));
    }
    public function index()
    {
        $this->paginate = [
            'contain' => ['Brands', 'MobileModels', 'Networks']
        ];
        $mobileUnlockPrices = $this->paginate($this->MobileUnlockPrices);
        $statusOptions = Configure::read('active');
        $this->set(compact('mobileUnlockPrices','statusOptions'));
        $this->set('_serialize', ['mobileUnlockPrices']);
    }

    /**
     * View method
     *
     * @param string|null $id Mobile Unlock Price id.
     * @return \Cake\Network\Response|null
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view($id = null)
    {
        $mobileUnlockPrice_query = $this->MobileUnlockPrices->get($id, [
            'contain' => ['Brands', 'MobileModels', 'Networks']
        ]);
       
        if(!empty($mobileUnlockPrice_query)){
            $mobileUnlockPrice = $mobileUnlockPrice_query->toArray();
        }else{
            $mobileUnlockPrice = array();
        }
        $this->set('mobileUnlockPrice', $mobileUnlockPrice);
        $this->set('_serialize', ['mobileUnlockPrice']);
    }

    /**
     * Add method
     *
     * @return \Cake\Network\Response|null Redirects on successful add, renders view otherwise.
     */
    public function add() {
        $mobileUnlockPrices = $this->MobileUnlockPrices->newEntity();
        $this->set(compact('mobileUnlockPrices'));
		$brand_id = 0;
        //pr($this->request);die;
		if($this->request->is('post') && $this->request['data']['hiddenController'] == 1){
			//onchange
			$brand_id = $this->request['data']['brand_id'];			
		}elseif ($this->request->is('post')){
			$brand_id = $brandId = $this->request['data']['brand_id'];
			$successfullySaved = array();
			$counter = 0;
			$error = array();
			//pr($this->request['data']['MobileUnlockPrice']);die;
			foreach($this->request['data']['MobileUnlockPrice']['mobile_model_id'] as $key => $mobileModel){
                //pr($mobileModel);die;
				//$this->MobileUnlockPrice->clear;				
				$mobileUnlockPrice = $this->request['data']['MobileUnlockPrice'];
				
				//------------------------------------
				$unlockingPrice = $mobileUnlockPrice['unlocking_price'][$key];
				$unlockingDays = $mobileUnlockPrice['unlocking_days'][$key];
				$unlockingCost = $mobileUnlockPrice['unlocking_cost'][$key];
				$networkID =  $mobileUnlockPrice['network_id'][$key];
				$unlockingMinutes =  $mobileUnlockPrice['unlocking_minutes'][$key];
				if(!empty($unlockingPrice) || !empty($unlockingCost) || !empty($unlockingDays) ){
					if(empty($mobileModel)){
						$error[]="Mobile model must be selected";
						break;
					}
					if(empty($unlockingCost)){
						$error[]="Please input the unlocking cost";					
						break;
					}
					if(empty($unlockingPrice)){
						$error[]="Please input the unlock costing price";
						break;
					}					
					if(empty($unlockingDays) && empty($unlockingMinutes)){
						$error[]="Please input the unlock days or unlocking minutes";					
						break;
					}else{
						if(empty($unlockingDays) && !empty($unlockingMinutes)){
							$unlockingDays = 0;
						}
						if(empty($unlockingMinutes) && !empty($unlockingDays)){
							$unlockingMinutes = 0;
						}
					}				
					$data = array(
							'brand_id' => $brandId,
							'mobile_model_id' => $mobileModel,
							'network_id' => $networkID,
							'unlocking_cost' => $unlockingCost,
							'unlocking_price' => $unlockingPrice,
							'unlocking_days' => $unlockingDays,
							'unlocking_minutes' => $unlockingMinutes,
							);
					//$this->MobileUnlockPrice->set($data);
					//if (!$this->MobileUnlockPrice->validates()) {
					//	$errors = $this->MobileUnlockPrice->validationErrors;
					//	//pr($data);pr($errors);
					//}
					$patch_entity = $this->MobileUnlockPrices->patchEntity($mobileUnlockPrices,$data,['validate' => false]);
                    //pr($patch_entity);die;
					try{
						if($this->MobileUnlockPrices->save($patch_entity)){
							$counter++;
						}						
					}catch(Exception $e){
						$error[] = "<br/>Record with this Brand:{$brandId}, Model:{$mobileModel} and Network:{$networkID} already existing";
					}
					
				}
			}
			//$this->MobileUnlockPrice->id;				
			$errorStr = "";
			if(count($error)){$errorStr = implode("<br/>", $error);}
			if (!empty($counter)) {					
				$this->Flash->success("The mobile unlocking price for $counter records has been saved.");
				return $this->redirect(array('action' => 'index'));
			}else{				
				$this->Flash->error("The mobile unlocking price could not be saved. {$errorStr}");
			}
		}
		
		$brands_query = $this->MobileUnlockPrices->Brands->find('list',[
                                                                    'keyField' => 'id',
                                                                    'valueField' => 'brand',
                                                                    'conditions' => ['Brands.status' => 1]
                                                                ]
                                                          );
        $brands_query = $brands_query->hydrate(false);
        if(!empty($brands_query)){
            $brands = $brands_query->toArray();
        }else{
            $brands = array();
        }
		if(!$brand_id){foreach($brands as $brand_id => $brand){break;}}
		$mobileModels_query = $this->MobileUnlockPrices->MobileModels->find('list',[
                                                                                'keyField' => 'id',
                                                                                'valueField' => 'model',
                                                                                'order' => ['MobileModels.model asc'],
                                                                                'conditions' => ['MobileModels.brand_id' => $brand_id]
                                                                            ]
                                                                    );
        $mobileModels_query = $mobileModels_query->hydrate(false);
        if(!empty($mobileModels_query)){
            $mobileModels = $mobileModels_query->toArray();
        }else{
            $mobileModels = array();
        }
		$networks_query = $this->MobileUnlockPrices->Networks->find('list',[
                                                                        'keyField' => 'id',
                                                                        'valueField' => 'name',
                                                                        'order' => ['Networks.name asc']
                                                                    ]
                                                            );
        $networks_query = $networks_query->hydrate(false);
        if(!empty($networks_query)){
            $networks = $networks_query->toArray();
        }else{
            $networks = array();
        }
		//pr($networks);
		$this->set(compact('brands', 'mobileModels', 'networks'));
	}

    /**
     * Edit method
     *
     * @param string|null $id Mobile Unlock Price id.
     * @return \Cake\Network\Response|null Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Network\Exception\NotFoundException When record not found.
     */
    public function edit($id = null)
    {
        $mobileUnlockPrice = $this->MobileUnlockPrices->get($id, [
            'contain' => []
        ]);
        if ($this->request->is(['patch', 'post', 'put'])) {
            $mobileUnlockPrice = $this->MobileUnlockPrices->patchEntity($mobileUnlockPrice, $this->request->data);
            if ($this->MobileUnlockPrices->save($mobileUnlockPrice)) {
                $this->Flash->success(__('The mobile unlock price has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The mobile unlock price could not be saved. Please, try again.'));
        }
        
        $brands_query = $this->MobileUnlockPrices->Brands->find('list',[
                                                                    'keyField' => 'id',
                                                                    'valueField' => 'brand',
                                                                    'conditions' => ['Brands.status' => 1]
                                                                ]
                                                          );
        $brands_query = $brands_query->hydrate(false);
        if(!empty($brands_query)){
            $brands = $brands_query->toArray();
        }else{
            $brands = array();
        }
		//if(!$brand_id){foreach($brands as $brand_id => $brand){break;}}
		$mobileModels_query = $this->MobileUnlockPrices->MobileModels->find('list',[
                                                                                'keyField' => 'id',
                                                                                'valueField' => 'model',
                                                                                'order' => ['MobileModels.model asc'],
                                                                               // 'conditions' => ['MobileModels.brand_id' => $brand_id]
                                                                            ]
                                                                    );
        $mobileModels_query = $mobileModels_query->hydrate(false);
        if(!empty($mobileModels_query)){
            $mobileModels = $mobileModels_query->toArray();
        }else{
            $mobileModels = array();
        }
		$networks_query = $this->MobileUnlockPrices->Networks->find('list',[
                                                                        'keyField' => 'id',
                                                                        'valueField' => 'name',
                                                                        'order' => ['Networks.name asc']
                                                                    ]
                                                            );
        $networks_query = $networks_query->hydrate(false);
        if(!empty($networks_query)){
            $networks = $networks_query->toArray();
        }else{
            $networks = array();
        }
		//pr($networks);
		$this->set(compact('brands', 'mobileModels', 'networks'));
        
        //$brands = $this->MobileUnlockPrices->Brands->find('list', ['limit' => 200]);
        //$mobileModels = $this->MobileUnlockPrices->MobileModels->find('list', ['limit' => 200]);
        //$networks = $this->MobileUnlockPrices->Networks->find('list', ['limit' => 200]);
        $this->set(compact('mobileUnlockPrice', 'brands', 'mobileModels', 'networks'));
        $this->set('_serialize', ['mobileUnlockPrice']);
    }

    /**
     * Delete method
     *
     * @param string|null $id Mobile Unlock Price id.
     * @return \Cake\Network\Response|null Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete($id = null)
    {
        $this->request->allowMethod(['post', 'delete']);
        $mobileUnlockPrice = $this->MobileUnlockPrices->get($id);
        if ($this->MobileUnlockPrices->delete($mobileUnlockPrice)) {
            $this->Flash->success(__('The mobile unlock price has been deleted.'));
        } else {
            $this->Flash->error(__('The mobile unlock price could not be deleted. Please, try again.'));
        }

        return $this->redirect(['action' => 'index']);
    }
    
    private function generate_condition_array(){
		$searchKW = trim(strtolower($this->request->query['search_kw']));
		$conditionArr = array();
		if(!empty($searchKW)){
			
			$conditionArr['or']['Brands.brand like']  = strtolower("%$searchKW%");
			$conditionArr['or']['MobileModels.model like'] =  strtolower("%$searchKW%");
			
		}
		return $conditionArr;
		
	}
	
	
	function search($keyword = ""){
		$conditionArr = $this->generate_condition_array();
		//pr($mobileModels);
		$searchKW = $this->request->query['search_kw'];
		$this->paginate = [
							'conditions' => $conditionArr,
							'limit' => 50,
                            'contain' => ['Brands','MobileModels']
                          ];
		
		
		$mobileUnlockPrices = $this->paginate('MobileUnlockPrices');
        
        $statusOptions = Configure::read('active');
		$this->set(compact('mobileUnlockPrices','statusOptions'));
		//$this->layout = 'default'; 
		$this->render('index');
	}
    
    
    public function editGrid($id = null) {
         $this->loadModel('Brands');
         $this->loadModel('Networks');
         $this->loadModel('MobileModels');
		$mobileUnlockPriceData_query = $this->MobileUnlockPrices->find('all',array('conditions'=>array('MobileUnlockPrices.mobile_model_id'=>$id),'recursive'=>-1));
		//pr($mobileUnlockPriceData_query);die;
        $mobileUnlockPriceData_query = $mobileUnlockPriceData_query->hydrate(false);
        if(!empty($mobileUnlockPriceData_query)){
            $mobileUnlockPriceData = $mobileUnlockPriceData_query->toArray();
        }
		$brands_query = $this->Brands->find('list',[
                                                    'keyField' => 'id',
                                                    'valueField' => 'brand',
                                                ]
                                      );
        $brands_query = $brands_query->hydrate(false);
        if(!empty($brands_query)){
            $brands = $brands_query->toArray();
        }
		$networks_query = $this->Networks->find('list',[
                                                 'keyField' => 'id',
                                                    'valueField' => 'name',
                                                 ]);
        $networks_query = $networks_query->hydrate(false);
        if(!empty($networks_query)){
            $networks = $networks_query->toArray();
        }
		$mobileModelName_query = $this->MobileModels->find('list',[ 'keyField' => 'id',
                                                            'valueField' => 'model',
                                                            'conditions' => ['MobileModels.id'=>$id]
                                                            ]
                                                    );
        $mobileModelName_query = $mobileModelName_query->hydrate(false);
        if(!empty($mobileModelName_query)){
            $mobileModelName = $mobileModelName_query->toArray();
        }
		//pr($mobileUnlockPriceData);
		if (empty($mobileUnlockPriceData)) {
			$this->Flash->error('Invalid input!');
			return $this->redirect(array('action' => 'index'));
			//throw new NotFoundException(__('Invalid input!'));
		}
		foreach($mobileUnlockPriceData as $key => $mobileUnlockPriceInfo){
			$networkUnlockPriceArr[$mobileUnlockPriceInfo['network_id']] = $mobileUnlockPriceInfo;
		}
		if ($this->request->is(array('post', 'put'))) {
			//code for deleting the rows from the mobile unlock price table
            //pr($this->request);die;
			if(array_key_exists('delete',$this->request->data)){
				$delArray = $this->request->data['MobileUnlockPrice']['del'];
				$delIds = array();
				foreach($delArray as $key => $delId){
					if($delId > 0){
						$delIds[] = $delId;
					}
				}
				if(count($delIds)){
					$implodeIds = "('".implode("','",$delIds)."')";
					//DELETE FROM `mobile_repairs` WHERE `id` IN ('1','2')
                    $query = "DELETE FROM `mobile_unlock_prices` WHERE `id` IN $implodeIds";
                    $conn = ConnectionManager::get('default');
                    $stmt = $conn->execute($query);
					$this->Flash->success('Records deleted!');
					return $this->redirect(array('action' => 'index'));
				}else{
					$this->Flash->error('Records could not be deleted!');
					return $this->redirect(array('action' => 'index'));
				}
				die;
			}
			
			$counter = 0;
			$error = array();
			foreach($this->request['data']['MobileUnlockPrice']['unlocking_cost'] as $key=>$unlocking_cost){
				$unlocking_price = $this->request['data']['MobileUnlockPrice']['unlocking_price'][$key];
				$unlocking_days = $this->request['data']['MobileUnlockPrice']['unlocking_days'][$key];
				$unlocking_minutes = $this->request['data']['MobileUnlockPrice']['unlocking_minutes'][$key];
				$mobileUnlockPriceId = $this->request['data']['MobileUnlockPrice']['id'][$key];
				$brand_id = $this->request['data']['MobileUnlockPrice']['brand_id'][$key];
				$mobile_model_id = $this->request['data']['MobileUnlockPrice']['mobile_model_id'][$key];
				$network_id = $this->request['data']['MobileUnlockPrice']['network_id'][$key];
				$status = $this->request['data']['MobileUnlockPrice']['status'][$key];
				
				if(isset($unlocking_price) || $unlocking_price == 0 || isset($unlocking_cost) || $unlocking_cost == 0 || isset($unlocking_days)){//|| $unlocking_days == 0  days cannot be empty nor zero, unlocking price and cost can be zero
					if((!is_numeric($unlocking_cost) && !empty($unlocking_price)) ||
					   (!is_numeric($unlocking_cost) && !empty($unlocking_days))
					   ){
						$error[] = "Please input unlocking cost for the combination of: $brands[$brand_id], $mobileModelName[$mobile_model_id] and $networks[$network_id]";
					}
					if((!empty($unlocking_days) && !is_numeric($unlocking_price)) ||
					   (!empty($unlocking_cost) && !is_numeric($unlocking_price))){
						$error[] = "Please input unlocking price for the combination of: $brands[$brand_id], $mobileModelName[$mobile_model_id] and $networks[$network_id]";
					}
					/*if((empty($unlocking_days) && is_numeric($unlocking_cost)) ||
					   (empty($unlocking_days) && is_numeric($unlocking_price)) 
					   ){//(is_numeric($unlocking_days) && $unlocking_days == 0)  // || (!is_numeric($unlocking_days) && !empty($unlocking_days))
						$error[] = "Please input unlocking days for the combination of: $brands[$brand_id], $mobileModelName[$mobile_model_id] and $networks[$network_id] in numbers & more than zero!";
					}*/
					if(empty($unlocking_days) && empty($unlocking_minutes) && !empty($unlocking_price) && !empty($unlocking_cost)){
						$error[] = "Please input unlocking days or unlocking minutes for the combination of: $brands[$brand_id], $mobileModelName[$mobile_model_id] and $networks[$network_id] in numbers";
					}
						
					
					
				}/*else{
					echo "here";die;
				}*/
			}
			
			$errorStr = "";
			if(count($error)){
				$errorStr = implode("<br/>",$error);
				$this->Flash->error(__("$errorStr. Please, try again."),array('escape'=>false));
			}else{
               
				//pr($this->request['data']['MobileUnlockPrice']);die;
				foreach($this->request['data']['MobileUnlockPrice']['unlocking_cost'] as $key=>$unlocking_cost){
					//$this->MobileUnlockPrice->clear();
					$orig_unlocking_cost = $this->request['data']['MobileUnlockPrice']['orig_unlocking_cost'][$key];
					$unlocking_price = $this->request['data']['MobileUnlockPrice']['unlocking_price'][$key];
					$orig_unlocking_price = $this->request['data']['MobileUnlockPrice']['orig_unlocking_price'][$key];
					$unlocking_days = $this->request['data']['MobileUnlockPrice']['unlocking_days'][$key];
					$orig_unlocking_days = $this->request['data']['MobileUnlockPrice']['orig_unlocking_days'][$key];
					
					$unlocking_minutes = $this->request['data']['MobileUnlockPrice']['unlocking_minutes'][$key];
					$orig_unlocking_minutes = $this->request['data']['MobileUnlockPrice']['orig_unlocking_minutes'][$key];
					
					$mobileUnlockPriceId = $this->request['data']['MobileUnlockPrice']['id'][$key];
					$brand_id = $this->request['data']['MobileUnlockPrice']['brand_id'][$key];
					$mobile_model_id = $this->request['data']['MobileUnlockPrice']['mobile_model_id'][$key];
					$network_id = $this->request['data']['MobileUnlockPrice']['network_id'][$key];
					$status = $this->request['data']['MobileUnlockPrice']['status'][$key];
					$orig_status = $this->request['data']['MobileUnlockPrice']['orig_status'][$key];
					
					if(empty($unlocking_days) && !empty($unlocking_minutes)){
								$unlocking_days = 0;
							}
							if(!empty($unlocking_days) && empty($unlocking_minutes)){
								$unlocking_minutes=0;
							}
							
					
					if(is_numeric($unlocking_price) && is_numeric($unlocking_cost) && is_numeric($unlocking_days) && is_numeric($mobileUnlockPriceId) && is_numeric($unlocking_minutes)){//days cannot be empty nor zero, unlocking price and cost can be zero
						if($orig_unlocking_cost != $unlocking_cost ||
						   $orig_unlocking_price != $unlocking_price ||
						   $orig_unlocking_days != $unlocking_days ||
						   $orig_status != $status||
						   $orig_unlocking_minutes != $unlocking_minutes){
				
						
							//echo "id = ".$mobileUnlockPriceId." orig_unlocking_cost = ".$orig_unlocking_cost."<br/>unlocking_cost".$unlocking_cost;
							//die;
							//updtaing only the changed fields by comparing with the original ones that are set hidden
							$unlockPriceData = array(
							'id' => $mobileUnlockPriceId,
							'unlocking_cost' => $unlocking_cost,
							'unlocking_price' => $unlocking_price,
							'unlocking_days' => $unlocking_days,
							'unlocking_minutes' => $unlocking_minutes,
							'status' => $status
								 );
                            
                            $mobile_prices_entity = $this->MobileUnlockPrices->get($mobileUnlockPriceId);
                            $mobile_prices_entity = $this->MobileUnlockPrices->patchEntity($mobile_prices_entity, $unlockPriceData);
							if($this->MobileUnlockPrices->save($mobile_prices_entity)){
								if($orig_status != $status){
                                    $conn = ConnectionManager::get('default');
                                    $stmt = $conn->execute('SELECT NOW() as currentTime');
                                    $currentDateTime = $stmt ->fetchAll('assoc'); 
                                    $current = $currentDateTime[0]['currentTime'];
								$this->MobileUnlockPrices->updateAll(array('status_change_date' => "'$current'"),array('MobileUnlockPrice.id' => $mobileUnlockPriceId));
							}
								$counter++;
							}
						}
					}elseif(is_numeric($unlocking_price) && is_numeric($unlocking_cost) && is_numeric($unlocking_days) && empty($mobileUnlockPriceId) && is_numeric($unlocking_minutes)){
                        
						
						$unlockPriceData = array(
							'brand_id' => $brand_id,
							'mobile_model_id' => $mobile_model_id,
							'network_id' => $network_id,
							'unlocking_cost' => $unlocking_cost,
							'unlocking_price' => $unlocking_price,
							'unlocking_days' => $unlocking_days,
							'unlocking_minutes'=>$unlocking_minutes,
							'status' => $status
								 );
						 $mobile_prices_entity = $this->MobileUnlockPrices->newEntity();
                         $mobile_prices_entity = $this->MobileUnlockPrices->patchEntity($mobile_prices_entity, $unlockPriceData);
						if($this->MobileUnlockPrices->save($mobile_prices_entity)){
							$counter++;
						}
					}
				}
			}
			
			if($counter > 0){
				$this->Flash->success(__("$counter records have been saved."));
				return $this->redirect(array('action' => 'index'));
			}
		}
		$brand_id = $mobileUnlockPriceData[0]['brand_id'];
		$mobile_model_id = $mobileUnlockPriceData[0]['mobile_model_id'];
		$status = array('0'=>'Inactive','1'=>'Active');
		$this->set(compact('brands','mobileModelName','status','networks','brand_id','mobile_model_id'));
		$this->set(compact('mobileUnlockPriceData','networkUnlockPriceArr'));
	}
    
    public function export(){
		 $conditionArr = array();
		if(array_key_exists('search_kw',$this->request->query)){
			$conditionArr = $this->generate_condition_array();
		}
		if(count($conditionArr)>=1){
			$count = $this->MobileUnlockPrices->find('all');
            $count = $count->count();
            
            $mobileUnlockPrices_query = $this->MobileUnlockPrices->find('all',[
                                                                               'conditions' => $conditionArr,
                                                                               'contain' => ['Brands','MobileModels']
                                                                               ]);
            $mobileUnlockPrices_query = $mobileUnlockPrices_query->hydrate(false);
            if(!empty($mobileUnlockPrices_query)){
                $mobileUnlockPrices = $mobileUnlockPrices_query->toArray();
            }else{
                $mobileUnlockPrices = array();
            }
		}else{
			$mobileUnlockPrices_query = $this->MobileUnlockPrices->find('all');
            $mobileUnlockPrices_query = $mobileUnlockPrices_query->hydrate(false);
            if(!empty($mobileUnlockPrices_query)){
                $mobileUnlockPrices = $mobileUnlockPrices_query->toArray();
            }else{
                $mobileUnlockPrices = array();
            }
		}
		//pr($mobileUnlockPrices);
		$brands_query = $this->Brands->find('list',[
                                                'keyField' => 'id',
                                                'valueField' => 'brand'
                                             ]
                                    );
        $brands_query = $brands_query->hydrate(false);
        if(!empty($brands_query)){
            $brands = $brands_query->toArray();
        }else{
            $brands = array();
        }
		//pr($brands);
		$mobileModels_query = $this->MobileModels->find('list',[
                                                            'keyField' => 'id',
                                                            'valueField' => 'model'
                                                         ]
                                                  );
        $mobileModels_query = $mobileModels_query->hydrate(false);
        if(!empty($mobileModels_query)){
            $mobileModels = $mobileModels_query->toArray();
        }else{
            $mobileModels = array();
        }
		$networks_query = $this->Networks->find('list',[
                                                            'keyField' => 'id',
                                                            'valueField' => 'name',
                                                        ]
                                          );
        $networks_query = $networks_query->hydrate(false);
        if(!empty($networks_query)){
            $networks = $networks_query->toArray();
        }else{
            $networks = array();
        }
		$tmpMobileUnlockPrice = array();
		foreach($mobileUnlockPrices as $key => $mobileUnlockPrice){
            //pr($mobileUnlockPrice);die;
			$brandID = $mobileUnlockPrice['brand_id'];
            //pr($brands);die;
            //pr($brandID);die;
            if(array_key_exists($brandID,$brands)){
                $mobileUnlockPrice['brand_id'] = $brands[$brandID];
            }
			$ModelID = $mobileUnlockPrice['mobile_model_id'];
            //pr($mobileModels);die;
            //pr($ModelID);die;
            if(array_key_exists($ModelID,$mobileModels)){
                $mobileUnlockPrice['mobile_model_id'] = $mobileModels[$ModelID];
            }
			$networkId= $mobileUnlockPrice['network_id'];
			if(array_key_exists($networkId, $networks)){
				//becasuse 14 index was missing
				$mobileUnlockPrice['network_id'] = $networks[$networkId];
			}else{
				$mobileUnlockPrice['network_id'] = '--';
			}
            unset($mobileUnlockPrice['mobile_model']);
            unset($mobileUnlockPrice['brand']);
			$tmpmobileUnlockPrices[] = $mobileUnlockPrice;
		}
        //pr($tmpmobileUnlockPrices);die;
		$this->outputCsv('MobileUnlockPrice_'.time().".csv" ,$tmpmobileUnlockPrices);
		$this->autoRender = false;
	}
    
    public function unlockPricePushNotification(){
       
		$countModified_query = $this->MobileUnlockPrices->find('all',array(
				'conditions' => array('DATE(MobileUnlockPrices.modified)' => date('Y-m-d'))
			)
		);
         $countModified = $countModified_query->count();
		if($countModified > 0){
			$pushStr = "$countModified unlock prices were updated today";
			 $this->Pusher->push($pushStr);//created in components
		} 
		return $this->redirect(array('action' => 'index'));
    }
    
}
