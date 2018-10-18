<?php
namespace App\Controller;
use Cake\Core\Configure;
use Cake\Core\Configure\Engine\PhpConfig;
use Cake\ORM\TableRegistry;
use Cake\ORM\Entity;
use Cake\Datasource\ConnectionManager;
use App\Controller\AppController;
use Cake\Validation\Validator;
 
class MobilePurchasesController extends AppController{
     public $helpers = [
         'Paginator' => ['templates' => 'paginatortemplates']
         ];
     public function initialize()
    {
        parent::initialize();
         $CURRENCY_TYPE = Configure::read('CURRENCY_TYPE');
       
         $gradeType = Configure::read('grade_type');
		$purchasingOptions = Configure::read('purchase_statuses');
		$identificationOptions = Configure::read('identification');
        $discountArr = [];
         for($i = 0; $i <= 50; $i++){
         if($i==0){
             $discountArr[0] = "None";
             continue;
         }
                 $discountArr[$i] = "$i %";
     
         }
        $discountOptions = $discountArr;
		$colorOptions = Configure::read('color');
		$countryOptions = Configure::read('uk_non_uk');
		$this->set(compact('purchasingOptions','CURRENCY_TYPE', 'identificationOptions', 'colorOptions','countryOptions','gradeType','discountOptions'));
        $this->loadModel('Users');
        $this->loadModel('Kiosks');
        $this->loadModel('MobilePrices');
        $this->loadModel('MobileModels');
        $this->loadModel('Networks');
        $this->loadModel('MobileTransferLogs');
        $this->loadModel('MobileReSales');
        $this->loadModel('Brands');
        $this->loadModel('MobileConditions');
        $this->loadModel('FunctionConditions');
        $this->loadModel('RetailCustomers');
		$this->loadModel('MobilePurchases');
		$this->loadComponent('Barcode');
       $this->loadComponent('ScreenHint');
   }
   public function index(){
      $gradeType = Configure::read('grade_type');
      $purchasingOptions = Configure::read('purchase_statuses');
      $identificationOptions = Configure::read('identification');
      $discountOptions = Configure::read('discount');
      $colorOptions = Configure::read('color');
      $countryOptions = Configure::read('uk_non_uk');
      $this->set(compact('purchasingOptions', 'identificationOptions', 'colorOptions','countryOptions','gradeType','discountOptions'));
      
      if ($this->request->is('post')){
         if(
            array_key_exists('transfer_reserved',$this->request->data['TransferMobile']) &&
            !empty($this->request->data['TransferMobile']['transfer_reserved'])
         ){
            //pr($this->request->data['TransferMobile']);die;
				$transferReservedData = $this->request->data['TransferMobile']['transfer_reserved'];
				if(!empty($transferReservedData)){
					$reservedCount = 0;
					foreach($transferReservedData as $purchase_id => $selected_kiosk){
                  $tranBy = $this->request->session()->read('Auth.User.id'); 
                  $tranDate = $this->current_date_time();
                  $dataArr = ['status' => 0, 'receiving_status' => 1, 'transient_date' => $tranDate,'transient_by' => $tranBy];
                  if($selected_kiosk == 10000){
                     $dataArr["new_kiosk_id"] = 10000;
                  }
                  if($this->MobilePurchases->updateAll(
							$dataArr,
							['MobilePurchase.id' => $purchase_id])
                  ){
                     $mobilePurchaseData_query = $this->MobilePurchases->find('all',
                                                                                    ['conditions' =>
                                                                                     ['MobilePurchases.id' => $purchase_id]
                                                                                    ] );
							$mobilePurchaseData_reuslt = $mobilePurchaseData_query->first();
                     if(!empty($mobilePurchaseData_reuslt)){
                        $mobilePurchaseData  = $mobilePurchaseData_reuslt->toArray();
                     }
                     // pr($mobilePurchaseData);die;
                     //$this->MobileTransferLog->clear();
                     
							$mobileTransferLogData = array(
                                                      'mobile_purchase_reference' => $mobilePurchaseData['mobile_purchase_reference'],
                                                      'mobile_purchase_id' => $purchase_id,
                                                      'kiosk_id' => $mobilePurchaseData['kiosk_id'],
                                                      'network_id' => $mobilePurchaseData['network_id'],
                                                      'grade' => $mobilePurchaseData['grade'],
                                                      'type' => $mobilePurchaseData['type'],
                                                      'receiving_status' => 1,
                                                      'imei' => $mobilePurchaseData['imei'],
                                                      'user_id' => $this->request->session()->read('Auth.User.id'),
                                                      'status' => 0
                                                   );
							
                     //$this->MobileTransferLog->create();
                       
                     $MobileTransferLog = $this->MobileTransferLogs->newEntity();
                     $mobileTransferLogData = $this->MobileTransferLogs->patchEntity($MobileTransferLog, $mobileTransferLogData);
                     if($this->MobileTransferLogs->save($mobileTransferLogData)) {
                       $reservedCount++;
                     }
						}
					}
					if($reservedCount==1){
                  $this->Flash->success(__("Mobile with purchase id: $purchase_id has been transferred."));
                  return $this->redirect(['action' => 'index']);
					}
				}
			}
		}else{
         $query = $this->Users->find('list', [
                                                 'keyField' => 'id',
                                                 'valueField' => 'username'
                                         ]);
         if(!empty($query)){
            $users = $query->toArray();
         }
         $kiosks_query = $this->Kiosks->find('list',array('fields' =>  array('id', 'name'), 'order' => array('Kiosks.name asc')));
         if(!empty($kiosks_query)){
             $kiosks  = $kiosks_query->toArray();
         }
         $kiosk_id = $this->request->session()->read('kiosk_id'); 
         if($kiosk_id > 0){
            //mobile purchase status 0 => available, 1 => sold , 2 => reserved, 3 => sent for unlock 4 => under repair
            $this->paginate = [
                                 'contain' => ['Kiosks','Brands', 'MobileModels'],
                                 'conditions' => ['MobilePurchases.kiosk_id' => $kiosk_id,
                                                   'MobilePurchases.status NOT IN' => 1 
                                                  ],
                                 'limit' => ROWS_PER_PAGE  ,
                                 'order'=>['MobilePurchases.id desc'] 
                           ];            
         }else{
            $this->paginate = [
                                 'contain' => ['Kiosks','Brands', 'MobileModels'],
                                 'conditions' => [
                                                      'MobilePurchases.status NOT IN' => 1 
                                                   ],
                                 'limit' => ROWS_PER_PAGE,
                                 'order'=>['MobilePurchases.id desc'] 
                              ];
			}
            
         $mobilePurchases_query = $this->paginate($this->MobilePurchases);
         if(!empty($mobilePurchases_query)){
             $mobilePurchases = $mobilePurchases_query->toArray();
         }
         //pr($mobilePurchases);die;
         $modelIdsArr = array();
			foreach($mobilePurchases as $key => $mobilePurchase){
				$modelIdsArr[$mobilePurchase['mobile_model_id']]= $mobilePurchase['mobile_model_id'];
				$data_query = $this->MobilePrices->find("all",array('fields' => array('brand_id', 'mobile_model_id','locked','sale_price'),
													'conditions'=>array(
                                                            'MobilePrices.brand_id' => $mobilePurchase['brand_id'],
                                                            'MobilePrices.mobile_model_id'=>$mobilePurchase['mobile_model_id'],														            'MobilePrices.locked'=>$mobilePurchase['type'],
                                                            'MobilePrices.grade'=>$mobilePurchase['grade'] 
																		)
													)
									 );
               $data_query = $data_query->first();
               if(!empty($data_query)){
                  $data  = $data_query->toArray();
               }
                   
				if(!empty($data)){
					$salePrice[$mobilePurchase['id']] = $data['sale_price'];
				}
			}
			if(empty($modelIdsArr)){
			   $modelIdsArr = array(0 => null);
			}
         $model_query = $this->MobileModels->find('list', [
                                                     'keyField' => 'id',
                                                     'valueField' => 'model',
                               'order'=>'model asc',
                                                      'conditions' =>['MobileModels.status' => 1,
                                                    'MobileModels.id IN'=>$modelIdsArr
                                                    ],
                                                       
                                                 ] 
                                         );
         if(!empty($model_query)){
              $mobileModels = $model_query->toArray();
         }
         $network_query = $this->Networks->find('list', [
                                                       'keyField' => 'id',
                                                       'valueField' => 'name',
                                                        
                                                         
                                                   ] 
                                           );
         if(!empty($network_query)){
              $networks = $network_query->toArray();
         }
			$networks[0] = '--';
			$networks[""] = '--';
			$lockedUnlocked = array('1'=>'Locked','0'=>'Unlocked');
         // $mobilePurchases_query = $this->paginate($this->MobilePurchases);
         // pr($mobilePurchases);die;
         $this->set(compact('mobileModels','mobilePurchases','networks','lockedUnlocked','kiosks','users','salePrice','gradeType '));
         $this->set(compact('mobilePurchases'));
         $this->set('_serialize', ['mobilePurchases']);
      }
   }
    
   public function current_date_time(){
        $conn = ConnectionManager::get('default');
          $stmt = $conn->execute('SELECT NOW() as created');
          $currentTimeInfo = $stmt ->fetchAll('assoc');
          $currentTime = $currentTimeInfo[0]['created'];
		return $currentTime;
	}
   
    //public function view($id = null){
    //    $mobilePurchase = $this->MobilePurchases->get($id, [
    //        'contain' => ['Kiosks', 'Users','MobileModels','Brands']
    //    ]);
    //
    //    $this->set('mobilePurchase', $mobilePurchase);
    //    $this->set('_serialize', ['mobilePurchase']);
    //}
    
    public function view($id = null) {
		$users_query = $this->Users->find('list',[
                                                'keyField' => 'id',
                                                'valueField' => 'username'
                                           ]
                                    );
        $users_query = $users_query->hydrate(false);
        if(!empty($users_query)){
            $users = $users_query->toArray();
        }else{
            $users = array();
        }
		$this->set('users', $users);
                $mobileConditions_query = $this->MobileConditions->find('list',[
                                                                            'conditions' => ['MobileConditions.status' => 1],
                                                                            'keyField' => 'id',
                                                                            'valueField' => 'mobile_condition'
                                                                        ]
                                                                );
                $mobileConditions_query = $mobileConditions_query->hydrate(false);
                if(!empty($mobileConditions_query)){
                    $mobileConditions = $mobileConditions_query->toArray();
                }else{
                    $mobileConditions = array();
                }
                $functionConditions_query = $this->FunctionConditions->find('list',[
                                                                                'conditions' => [ 'FunctionConditions.status' => 1],
                                                                                'keyField' => 'id',
                                                                                'valueField' => 'function_condition'
                                                                             ]
                                                                      );
                $functionConditions_query = $functionConditions_query->hydrate(false);
                if(!empty($functionConditions_query)){
                    $functionConditions = $functionConditions_query->toArray();
                }else{
                    $functionConditions = array();
                }
                $this->set(compact( 'mobileConditions' ,'functionConditions'));
		$currency = $this->setting['currency_symbol'];
		$status = array( '0' => 'Available', '1' => 'sold' , '2' => 'Reserved', '3' => 'Sent for unlock', '4' => 'Sent for repair');
		$received = array( '1' =>'Transient', '0' =>'Received'  );
		$type = array('1'=> 'Locked', '0' => 'Unlocked');
		$discountOptions = Configure::read('discount');
		$gradeType = Configure::read('grade_type');
		$colorOptions = Configure::read('color');
		$countryOptions = Configure::read('uk_non_uk');
		$countryOptions[""]="--";
		$this->set(compact('status','colorOptions','discountOptions','countryOptions','gradeType','received','type','currency'));
		if (!$this->MobilePurchases->exists($id)) {
			throw new NotFoundException(__('Invalid mobile purchase'));
		}
		$options = array('conditions' => array('MobilePurchases.id' => $id),'contain' => array('Kiosks','Brands'));
		$mobilePurchase_query = $this->MobilePurchases->find('all', $options);
        //pr($mobilePurchase_query);die;
        $mobilePurchase_query = $mobilePurchase_query->hydrate(false);
        if(!empty($mobilePurchase_query)){
            $mobilePurchase = $mobilePurchase_query->first();
        }else{
            $mobilePurchase = array();
        }
        //pr($mobilePurchase);die;
        $this->set('mobilePurchase',$mobilePurchase );
		$mobileModels_query = $this->MobileModels->find('list',[
                                                            'keyField' => 'id',
                                                            'valueField' => 'model',
												'order'=>'model asc',
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
                                                    'valueField' => 'name'
                                                 ]
                                          );
        $networks_query = $networks_query->hydrate(false);
        if(!empty($networks_query)){
            $networks = $networks_query->toArray();
        }else{
            $networks = array();
        }
		$networks[""] = "--";
		$this->set(compact( 'mobileModels','networks' ));
	}

   public function add() {
        $new_entity = $this->MobilePurchases->newEntity();
        $this->set(compact('new_entity'));
		//capturing the mobile model id and brand ids from mobileunlockprice table with status 1 ie active
		$customerId ='';
		$customerdetail = array();
		if(!empty( $this->request->query)){
			$customerId = $this->request->query['customerId'] ;
		}
		$fieldArr = array('id','fname','lname','email','mobile','city','country','state','zip','address_1','address_2');
		$customerdetail_query = $this->RetailCustomers->find('all',array(
															'conditions' => array('RetailCustomers.id IN'=>$customerId),							 
															'fields' => $fieldArr,
															//'recursive' => -1,
														));
		$customerdetail_query = $customerdetail_query->hydrate(false);
        if(!empty($customerdetail_query)){
            $customerdetail = $customerdetail_query->toArray();
        }else{
            $customerdetail = array();
        }
        $mobileConditions_query = $this->MobileConditions->find('list',[
                                                                    'conditions' => ['MobileConditions.status' => 1],
                                                                    'keyField' => 'id',
                                                                    'valueField' => 'mobile_condition'
                                                                 ]
                                                          );
        $mobileConditions_query = $mobileConditions_query->hydrate(false);
        if(!empty($mobileConditions_query)){
            $mobileConditions = $mobileConditions_query->toArray();
        }else{
            $mobileConditions = array();
        }
		$functionConditions_query = $this->FunctionConditions->find('list',[
                                                                                'conditions' => ['FunctionConditions.status' => 1],
                                                                                'keyField' => 'id',
                                                                                'valueField' => 'function_condition'
                                                                     ]
                                                              );
        $functionConditions_query = $functionConditions_query->hydrate(false);
        if(!empty($functionConditions_query)){
            $functionConditions = $functionConditions_query->toArray();
        }else{
            $functionConditions = array();
        }
		$activeCombinations_query = $this->MobilePrices->find('all',array(
																   'conditions' => array('MobilePrices.status'=>1),
																	'fields' => array('mobile_model_id','brand_id'),
																	'group' => 'MobilePrices.mobile_model_id'
														));
        $activeCombinations_query = $activeCombinations_query->hydrate(false);
        if(!empty($activeCombinations_query)){
            $activeCombinations = $activeCombinations_query->toArray();
        }else{
            $activeCombinations = array();
        }
		$activeBrands = $activeModels = array();
		
		foreach($activeCombinations as $key => $activeCombination){
			$activeModels[$activeCombination['mobile_model_id']] = $activeCombination['mobile_model_id'];
			$activeBrands[$activeCombination['brand_id']] = $activeCombination['brand_id'];
		}
		if(empty($activeBrands)){
		  $activeBrands = array('0'=>null);
		}
		if(empty($activeModels)){
		  $activeModels = array('0'=>null);
		}
		$grades_description = $this->setting['grades_description'];
		//mobile purchase status 0 => available, 1 => sold , 2 => reserved, 3 => sent for unlock 4 => under repair
		if ($this->request->is('post')) {
		  //pr($this->request);
		  
			if($this->request->session()->read('Auth.User.group_id') == ADMINISTRATORS || $this->request->session()->read('Auth.User.group_id') == FRANCHISE_OWNER){
				$this->request->data['MobilePurchase']['kiosk_id'] = 10000;
			}
			$mobileModels_query = $this->MobileModels->find('list',[
                                                                'keyField' => 'id',
                                                                'valueField' => 'model',
												    'order'=>'model asc',
                                                                'conditions' => ['MobileModels.status' => 1,'MobileModels.id IN' => $activeModels]
															]
													 );
            $mobileModels_query = $mobileModels_query->hydrate(false);
            if(!empty($mobileModels_query)){
                $mobileModels = $mobileModels_query->toArray();
            }else{
                $mobileModels = array();
            }
			//$imei = $this->request->data['MobilePurchase']['imei'];
			if(array_key_exists('imei',$this->request->data['MobilePurchase'])){
				$imei = $this->request->data['MobilePurchase']['imei'].$this->request->data['MobilePurchase']['imei1'];
			}
			$this->request->data['MobilePurchase']['imei']= $imei;
			if(strlen($imei) < 14){
				$this->Flash->error("Imei must be atleast 14 digit long");
				$brandID = $this->request['data']['MobilePurchase']['brand_id'];
				$brands_query = $this->MobilePurchases->Brands->find('list',[
                                                                        'keyField' => 'id',
                                                                        'valueField' => 'brand',
														  'order'=>'brand asc',
																		'conditions' => array('Brands.status' => 1, 'Brands.id IN' => $activeBrands)
                                                                      ]
															 );
                $brands_query = $brands_query->hydrate(false);
                if(!empty($brands_query)){
                    $brands = $brands_query->toArray();
                }else{
                    $brands = array();
                }
				$networks_query = $this->Networks->find('list',[
                                                            'keyField' => 'id',
                                                            'valueField' => 'name'
                                                         ]
                                                  );
                $networks_query = $networks_query->hydrate(false);
                if(!empty($networks_query)){
                    $networks = $networks_query->toArray();
                }else{
                    $networks = array();
                }
				//foreach($brands as $brandID=>$brand)break;
				$mobileModels_query = $this->MobileModels->find('list',[
																	'keyField' => 'id',
                                                                    'valueField' => 'model',
													   'order'=>'model asc',
																	'conditions' => [
																						'MobileModels.status' => 1,
																						'MobileModels.brand_id' => $brandID,
																						'MobileModels.id IN' => $activeModels
                                                                                    ]
                                                                 ]
                                                          );
                $mobileModels_query = $mobileModels_query->hydrate(false);
                if(!empty($mobileModels_query)){
                    $mobileModels = $mobileModels_query->toArray();
                }else{
                    $mobileModels = array();
                }
				$this->set(compact('brands','mobileModels','brandID','networks','mobileConditions','functionConditions'));
				return;
			}
			$checkIfExist_query = $this->MobilePurchases->find('all',array(
																	  'conditions' => array(
																							'MobilePurchases.imei' => $imei,
																							'MobilePurchases.status' => 0)
																	  ));
            $checkIfExist_query = $checkIfExist_query->hydrate(false);
            if(!empty($checkIfExist_query)){
                $checkIfExist = $checkIfExist_query->first();
            }else{
                $checkIfExist = array();
            }
			if(!empty($checkIfExist)){
				$this->Flash->error(__('The mobile purchase could not be saved. This imei already exists in database. Please, try again.'));
				$brandID = $this->request['data']['MobilePurchase']['brand_id'];
				$brands_query = $this->MobilePurchases->Brands->find('list',[
																		'keyField' => 'id',
																		'valueField' => 'brand',
																		'order'=>'brand asc',
																		'conditions' => ['Brands.status' => 1,'Brands.id IN' => $activeBrands]
                                                                      ]
															 );
                $brands_query = $brands_query->hydrate(false);
                if(!empty($brands_query)){
                    $brands = $brands_query->toArray();
                }else{
                    $brands = array();
                }
				$networks_query = $this->Networks->find('list',[
                                                            'keyField' => 'id',
                                                            'valueField' => 'name'
                                                         ]
                                                  );
                $networks_query = $networks_query->hydrate(false);
                if(!empty($networks_query)){
                    $networks = $networks_query->toArray();
                }else{
                    $networks = array();
                }
				//foreach($brands as $brandID=>$brand)break;
				$mobileModels_query = $this->MobileModels->find('list',[
																	'keyField' => 'id',
                                                                    'valueField' => 'model',
													   'order'=>'model asc',
																	'conditions' => [
																					  'MobileModels.status' => 1,
																					  'MobileModels.brand_id' => $brandID,
																					  'MobileModels.id IN' => $activeModels]
                                                                 ]
                                                          );
                $mobileModels_query = $mobileModels_query->hydrate(false);
                if(!empty($mobileModels_query)){
                    $mobileModels = $mobileModels_query->toArray();
                }else{
                    $mobileModels = array();
                }
				//pr($this->request);die;
				$this->set(compact('brands','mobileModels','brandID','networks','mobileConditions','functionConditions'));
				return;
			}
			if($this->request->data['MobilePurchase']['type'] == 0){
				$this->request->data['MobilePurchase']['network_id'] = NULL;
			}
			//pr($this->request);die;
			//pr($this->request->data['MobilePurchase']);die;
			$this->request->data['MobilePurchase']['purchased_by_kiosk'] = $this->request->data['MobilePurchase']['kiosk_id'];
			$this->request->data['MobilePurchase']['user_id'] = $this->request->session()->read('Auth.User.id');
			
			if(array_key_exists('customer_identification_others',$this->request->data) &&
			   !empty($this->request->data['MobilePurchase']['customer_identification_others'])){
				$this->request->data['MobilePurchase']['customer_identification'] = $this->request->data['MobilePurchase']['customer_identification_others'];
			}
			
			if(array_key_exists('mobile_condition',$this->request->data['MobilePurchase'])){
				$this->request->data['MobilePurchase']['mobile_condition'] = implode("|",$this->request->data['MobilePurchase']['mobile_condition']);
			}
            //pr($this->request->data);die;
            $this->request->data['MobilePurchase']['mobile_condition_remark'] = $this->request->data['MobilePurchase']['mobile_condition_remark'];
			
            if(array_key_exists('function_condition',$this->request->data['MobilePurchase'])){
			    $this->request->data['MobilePurchase']['function_condition'] = implode("|",$this->request->data['MobilePurchase']['function_condition']);
			}
	 
            $newEntity = $this->MobilePurchases->newEntity($this->request->data['MobilePurchase'],['validate' => false]);
             $image_name = "";
            if(array_key_exists('MobilePurchase',$this->request->data)){
               // echo "die";
                 if(array_key_exists('image',$this->request->data['MobilePurchase'])){
                      $image_name = $this->request->data['MobilePurchase']['image']['name'];
                 }
            }
			   
			   $fname = $this->request['data']['MobilePurchase']['customer_fname'];
					$lname = $this->request['data']['MobilePurchase']['customer_lname'];
					$email = $this->request['data']['MobilePurchase']['customer_email'];
					$mobile = $this->request['data']['MobilePurchase']['customer_contact'];
					$address_1 = $this->request['data']['MobilePurchase']['customer_address_1'];
					$address_2 = $this->request['data']['MobilePurchase']['customer_address_2'];
					$city = $this->request['data']['MobilePurchase']['city'];
					$state = $this->request['data']['MobilePurchase']['state'];
					$zip = $this->request['data']['MobilePurchase']['zip'];
			   
			   
			
			   $countDuplicate_query = $this->RetailCustomers->find('all', array('conditions' => array('RetailCustomers.email' => $email)));
			   $countDuplicate_query = $countDuplicate_query->hydrate(false);
			   if(!empty($countDuplicate_query)){
				   $countDuplicate = $countDuplicate_query->first();
			   }else{
				   $countDuplicate = array();
			   }
			   $kiosk_id = $this->request->data['MobilePurchase']['kiosk_id'];
			   $userId = $this->request->Session()->read('Auth.User.id');
			   $customer_data = array(
										   'kiosk_id' =>  $kiosk_id,
										   'fname' => $fname,
										   'lname' => $lname,
										   'mobile' => $mobile,
										   'email' => $email,
										   'zip' => $zip,
										   'address_1' => $address_1,
										   'address_2' => $address_2,
										   'city' => $city,
										   'state' => $state,
										   'created_by' => $userId
									  );
			   
			   if(count($countDuplicate) == 0){
				   $retailCustomersEntity = $this->RetailCustomers->newEntity();
				   $retailCustomersEntity = $this->RetailCustomers->patchEntity($retailCustomersEntity,$customer_data,['validation' => false]);
				   $this->RetailCustomers->save($retailCustomersEntity);
			   }else{	
				   $custmor_id =  $countDuplicate["id"];
				   $retailCustomersEntity = $this->RetailCustomers->get($custmor_id);
				   $retailCustomersEntity = $this->RetailCustomers->patchEntity($retailCustomersEntity,$customer_data,['validate' => false]);
				   $this->RetailCustomers->save($retailCustomersEntity);
			   }
			
			//pr($this->request);die;
			if(array_key_exists("customer_identification_others",$this->request->data['MobilePurchase'])){
			   $this->request->data['MobilePurchase']['customer_identification'] = $this->request->data['MobilePurchase']['customer_identification_others'];
			   }
			   
			   
            //die;
			
            $patchEntity = $this->MobilePurchases->patchEntity($newEntity,$this->request->data['MobilePurchase']);
           //debug($patchEntity->errors());die;
			if ($this->MobilePurchases->save($patchEntity)) {
                //echo'bye';die;
				$mobilePurchaseId = $patchEntity->id;
                if(!empty($image_name)){
					 $mobilePurchaseId = $patchEntity->id;
					$path = $this->request->webroot;
					if(mkdir(WWW_ROOT."files/MobilePurchases/image/{$mobilePurchaseId}")){
						 if(rename(WWW_ROOT."files/MobilePurchases/image/{$image_name}", WWW_ROOT."files/MobilePurchases/image/{$mobilePurchaseId}/{$image_name}")){
							  $query = "UPDATE mobile_purchases SET image_dir = {$mobilePurchaseId} where id = {$mobilePurchaseId}";
							  $conn = ConnectionManager::get('default');
							  $stmt = $conn->execute($query);
						 }
					}
                }
				$mobileTransferLogData = array(
												'mobile_purchase_id' => $mobilePurchaseId,
												'kiosk_id' => $this->request->data['MobilePurchase']['kiosk_id'],
												'imei' => $this->request->data['MobilePurchase']['imei'],
												'user_id' => $this->request->Session()->read('Auth.User.id'),
												'network_id' => $this->request->data['MobilePurchase']['network_id'],
												'grade' => $this->request->data['MobilePurchase']['grade'],
												'type' => $this->request->data['MobilePurchase']['type']
											);
				$entity_N = $this->MobileTransferLogs->newEntity();
                $entity_P = $this->MobileTransferLogs->patchEntity($entity_N,$mobileTransferLogData);
				$this->MobileTransferLogs->save($entity_P);
				$this->Flash->success(__('The mobile purchase has been saved.'));
				$print_type = $this->setting['print_type'];
				if($print_type == 1){
					return $this->redirect(array('controller' =>'prints' ,'action' => 'mobile_purchases',$mobilePurchaseId));	
				}else{
					return $this->redirect(array('action' => 'index'));
				}
			} else {
			  
			   if(!empty($patchEntity->errors())){
					foreach($patchEntity->errors() as $key){
						 foreach($key as $value){
							  $error[] = $value;
						 }
					}
			   }
			   $this->Flash->error(implode("</br>",$error),['escape' => false]);
                //echo'hi';die;
				$brandID = $this->request['data']['MobilePurchase']['brand_id'];
				$brands_query = $this->MobilePurchases->Brands->find('list',[
                                                                        'keyField' => 'id',
                                                                        'valueField' => 'brand',
														  'order'=>'brand asc',
                                                                        'conditions' => ['Brands.status' => 1,'Brands.id IN' => $activeBrands]
                                                                      ]
                                                               );
                $brands_query = $brands_query->hydrate(false);
                if(!empty($brands_query)){
                    $brands = $brands_query->toArray();
                }else{
                    $brands = array();
                }
				$networks_query = $this->Networks->find('list',[
                                                            'keyField' => 'id',
                                                            'valueField' => 'name'
                                                         ]
                                                  );
                $networks_query = $networks_query->hydrate(false);
                if(!empty($networks_query)){
                    $networks = $networks_query->toArray();
                }else{
                    $networks = array();
                }
				//foreach($brands as $brandID=>$brand)break;
				$mobileModels_query = $this->MobileModels->find('list',[
																	  'keyField' => 'id',
																	  'valueField' => 'model',
																	  'order'=>'model asc',
                                                                    //'fields' => array('id', 'model'),
                                                                    'conditions'=>[
                                                                                    'MobileModels.status' => 1,
                                                                                    'MobileModels.brand_id' => $brandID,
                                                                                    'MobileModels.id IN' => $activeModels
                                                                                  ]
                                                                 ]
                                                          );
                $mobileModels_query = $mobileModels_query->hydrate(false);
                if(!empty($mobileModels_query)){
                    $mobileModels = $mobileModels_query->toArray();
                }else{
                    $mobileModels = array();
                }
				$grades_description = $this->setting['grades_description'];
				$this->set(compact('brands','mobileModels','brandID','networks','mobileConditions','functionConditions','grades_description'));
				$this->Flash->error(__('The mobile purchase could not be saved. Please, try again.'));
				return;
			}
			$this->set(compact('mobileModels'));
		}
		$kiosks_query = $this->MobilePurchases->Kiosks->find('list');
        $kiosks_query = $kiosks_query->hydrate(false);
        if(!empty($kiosks_query)){
            $kiosks = $kiosks_query->toArray();
        }else{
            $kiosks = array();
        }
		$brands_query = $this->MobilePurchases->Brands->find('list',[
                                                                'keyField' => 'id',
                                                                'valueField' => 'brand',
												    'order'=>'brand asc',
                                                                'conditions' => ['Brands.status' => 1,'Brands.id IN' => $activeBrands]
                                                              ]
                                                       );
        $brands_query = $brands_query->hydrate(false);
        if(!empty($brands_query)){
            $brands = $brands_query->toArray();
        }else{
            $brands = array();
        }
		$networks_query = $this->Networks->find('list',[
                                                    'keyField' => 'id',
                                                    'valueField' => 'name'
                                                 ]
                                          );
        $networks_query = $networks_query->hydrate(false);
        if(!empty($networks_query)){
            $networks = $networks_query->toArray();
        }else{
            $networks = array();
        }
		$brandID = 0;
		if(!empty($brands)){
			   foreach($brands as $brandID => $brand)break;
			   $mobileModels_query = $this->MobileModels->find('list',[
																   'keyField' => 'id',
																   'valueField' => 'model',
													   'order'=>'model asc',
																   'conditions'=>['MobileModels.status' => 1,'MobileModels.brand_id' => $brandID,'MobileModels.id IN' => $activeModels]
																]
														 );
			   $mobileModels_query = $mobileModels_query->hydrate(false);
		  }
        if(!empty($mobileModels_query)){
            $mobileModels = $mobileModels_query->toArray();
        }else{
            $mobileModels = array();
        }
		$this->set(compact('customerdetail'));
		$this->set(compact('kiosks', 'brands','mobileModels','brandID','networks','grades_description','mobileConditions','functionConditions'));
	}
    
    public function edit($id = null) {
        $mobileConditions_query = $this->MobileConditions->find('list',[
                                                                    'conditions' => ['MobileConditions.status' => 1],
																	'keyField' => 'id',
                                                                    'valueField' => 'mobile_condition'
                                                                 ]
                                                          );
        $mobileConditions_query = $mobileConditions_query->hydrate(false);
        if(!empty($mobileConditions_query)){
            $mobileConditions = $mobileConditions_query->toArray();
        }else{
            $mobileConditions = array();
        }
		$functionConditions_query = $this->FunctionConditions->find('list',[
																		'conditions' => ['FunctionConditions.status' => 1],
																		'keyField' => 'id',
                                                                        'valueField' => 'function_condition'
                                                                     ]
                                                              );
        $functionConditions_query = $functionConditions_query->hydrate(false);
        if(!empty($functionConditions_query)){
            $functionConditions = $functionConditions_query->toArray();
        }else{
            $functionConditions = array();
        }
		$activeCombinations_query = $this->MobilePrices->find('all',array(
																   'conditions' => array('MobilePrices.status' => 1),
																   'fields' => array('mobile_model_id','brand_id'),
																   'recursive' => -1,
																   'group' => 'MobilePrices.mobile_model_id',
																));
        $activeCombinations_query = $activeCombinations_query->hydrate(false);
        if(!empty($activeCombinations_query)){
            $activeCombinations = $activeCombinations_query->toArray();
        }else{
            $activeCombinations = array();
        }
		$activeBrands = $activeModels = array();
		foreach($activeCombinations as $key => $activeCombination){
			$activeModels[$activeCombination['mobile_model_id']] = $activeCombination['mobile_model_id'];
			$activeBrands[$activeCombination['brand_id']] = $activeCombination['brand_id'];
		}
		
		$grades_description = $this->setting['grades_description'];
		if (!$this->MobilePurchases->exists($id)) {
			throw new NotFoundException(__('Invalid mobile purchase'));
		}else{
			   $kiosk_id_to_chk = $this->request->Session()->read('kiosk_id');
            $mobile_purchase_entity = $this->MobilePurchases->get($id);
			$this->set(compact('mobile_purchase_entity'));
			//$mobile_purchase_data = $mobile_purchase_entity->toArray(false);
			$database_kiosk_id = $mobile_purchase_entity->purchased_by_kiosk;
			
			if($kiosk_id_to_chk == "" || $kiosk_id_to_chk == 10000 || $kiosk_id_to_chk == 0){
			   
			}else{
			   if($kiosk_id_to_chk != $database_kiosk_id){
					$this->Flash->error("You are Not allowed to edit this Purchase");
					return $this->redirect(array('action' => 'index'));
				 }   
			}
			
            $this->set(compact('mobile_purchase_entity'));
        }
		
		if ($this->request->is(array('post', 'put'))) {
            //pr($this->request);die;
			$options = array('conditions' => array('MobilePurchases.id' => $id));
			$mobilePurchaseData_query = $this->MobilePurchases->find('all', $options);
			$mobilePurchaseData_query = $mobilePurchaseData_query->hydrate(false);
			if(!empty($mobilePurchaseData_query)){
			   $mobilePurchaseData = $mobilePurchaseData_query->first();
			}else{
			   $mobilePurchaseData = array();
			}
			$brandId = $mobilePurchaseData['brand_id'];
			$mobileModelId = $mobilePurchaseData['mobile_model_id'];
			$locked = $mobilePurchaseData['type'];
			$grade = $mobilePurchaseData['grade'];
			$mobilePrice_query = $this->MobilePrices->find('all',array('conditions'=>array('MobilePrices.brand_id'=>$brandId,'MobilePrices.mobile_model_id'=>$mobileModelId,'MobilePrices.locked'=>$locked,'MobilePrices.grade'=>$grade)));
			$mobilePrice_query = $mobilePrice_query->hydrate(false);
			if(!empty($mobilePrice_query)){
			   $mobilePrice  = $mobilePrice_query->first();
			}else{
			   $mobilePrice = array();
			}
			
			if(empty($mobilePrice)){
				$maximum_topup = '';
			}else{
				$maximum_topup = $mobilePrice['maximum_topup'];
			}
			$mobileRequestData = $this->request->data['MobilePurchase'];
			$checkIfExist = array();
			if(array_key_exists('imei',$mobileRequestData)){
				$imei = $mobileRequestData['imei'] = $mobileRequestData['imei'].$mobileRequestData['imei1'];
				//$imei = $this->request->data['MobilePurchase']['imei'];
				$checkIfExist_query = $this->MobilePurchases->find('all', array(
																			'conditions' => array(
																								  'MobilePurchases.imei' => $imei,
																								  'MobilePurchases.status' => 0,
																								  'NOT' => array('MobilePurchases.id' => $id))
															));
				$checkIfExist_query = $checkIfExist_query->hydrate(false);
				if(!empty($checkIfExist_query)){
					$checkIfExist = $checkIfExist_query->first();
				}else{
					$checkIfExist =  array();
				}
			}
			
			if(!empty($checkIfExist)){
				$this->Flash->error(__('The mobile purchase could not be saved. This imei already exists in database. Please, try again.'));
				$brands_query = $this->MobilePurchases->Brands->find('list', array(
																		   'keyField' => 'id',
																		   'valueField' => 'brand',
																		   'order'=>'brand asc',
																			'conditions' => array('Brands.status' => 1,'Brands.id IN' => $activeBrands)
																			)
															);
				$brands_query = $brands_query->hydrate(false);
				if(!empty($brands_query)){
					$brands = $brands_query->toArray();
				}else{
					$brands = array();
				}
				$networks_query = $this->Networks->find('list',array(
																	 'keyField' => 'id',
																	 'valueField' => 'name',
																	 ));
				$networks_query = $networks_query->hydrate(false);
				if(!empty($networks_query)){
					$networks = $networks_query->toArray();
				}else{
					$networks = array();
				}
				
				foreach($brands as $brandID=>$brand)break;
				$mobileModels_query = $this->MobileModels->find('list',array(
																 'keyField' => 'id',
																 'valueField' => 'model',
																 'order'=>'model asc',
																'conditions' => array(
																					  'MobileModels.status' => 1,
																					  'MobileModels.brand_id' => $brandID,
																					  'MobileModels.id IN' => $activeModels)
														));
				$mobileModels_query = $mobileModels_query->hydrate(false);
				if(!empty($mobileModels_query)){
					$mobileModels = $mobileModels_query->toArray();
				}else{
					$mobileModels = array();
				}
				$this->set(compact('brands','mobileModels','brandID','networks','mobileConditions','functionConditions','maximum_topup'));
				return;
                // $this->redirect(array('action'=>'edit',$id));
			}
			
			if(empty($this->request->data['MobilePurchase']['mobile_purchase_reference'])){
				$this->request->data['MobilePurchase']['mobile_purchase_reference'] = "0";
				$mobileRequestData['mobile_purchase_reference'] = 0;
			}
			
			if(array_key_exists('mobile_condition',$mobileRequestData)){
				$mobileRequestData['mobile_condition'] = implode("|",$mobileRequestData['mobile_condition']);
			}else{
				$mobileRequestData['mobile_condition'] = '';
			}
			
            $mobileRequestData['mobile_condition_remark'] = $mobileRequestData['mobile_condition_remark'];
                        
			if(array_key_exists('function_condition',$mobileRequestData)){
				$mobileRequestData['function_condition'] = implode("|",$mobileRequestData['function_condition']);
			}
                        
			$this->request->data['MobilePurchase'] = $mobileRequestData;
			$MobilePurchasesEntity = $this->MobilePurchases->get($id);
           	$MobilePurchasesEntity = $this->MobilePurchases->patchEntity($MobilePurchasesEntity,$mobileRequestData);
			//pr($MobilePurchasesEntity);die;
			if ($this->MobilePurchases->save($MobilePurchasesEntity)) {
                $image_name = "";
		  
			   if(array_key_exists('image',$this->request->data['MobilePurchase'])){
					$image_name = $this->request->data['MobilePurchase']['image']['name'];
			   }
                $path =  WWW_ROOT."files".DS."MobilePurchases".DS."image".DS.$id.DS;
                   // pr($this->request->data['MobilePurchase']);die;
                    if(array_key_exists('remove',$this->request->data['MobilePurchase']['image'])){
                      
                        $remove = $this->request->data['MobilePurchase']['image']['remove'];
                        $image_delete = $this->request->data['MobilePurchase']['image']['name'];
                        if($remove == '1'){
                              $fullpath  = $path.$image_delete ; 
                             if($path){
                                  $fullpath  = $path.$image_delete ;  
                            if($path){
                                        $scanned_directory = array_diff(scandir($path), array('..', '.'));
                                        //  pr($scanned_directory);die;
                                         if(!empty($scanned_directory)){
                                            foreach($scanned_directory as  $sngscanned_directory){
                                               $fullimagepath = $path.$sngscanned_directory;
                                              unlink($fullimagepath);
                                              echo  $sngscanned_directory."  image Delete Succesfully !"; 
                                            } 
                                        }else{
                                           echo "No Image"; 
                                       }
                                } 
                            }
                        }
                    }
			   if(!empty($image_name)){
					$query1 = "UPDATE mobile_purchases SET image_dir = {$id} where id = {$id}";
                     $query2 = "UPDATE mobile_purchases SET image = '$image_name' WHERE id = $id";
					$conn1 = ConnectionManager::get('default');
					$stmt = $conn1->execute($query1);
			   }
               
				$mobileTransferLogData = array(
												'mobile_purchase_reference' => $mobileRequestData['mobile_purchase_reference'],
												'mobile_purchase_id' => $id,
												'kiosk_id' => $this->request->Session()->read('kiosk_id'),
												'imei' => $mobileRequestData['imei'],
												'user_id' => $this->request->Session()->read('Auth.User.id'),
												'network_id' => $mobileRequestData['network_id'],
												'grade' => $mobileRequestData['grade'],
												'type' => $mobileRequestData['type'],
											);
				$MobileTransferLogsEntity = $this->MobileTransferLogs->newEntity($mobileTransferLogData,['validate' => false]);
				$MobileTransferLogsEntity = $this->MobileTransferLogs->patchEntity($MobileTransferLogsEntity,$mobileTransferLogData,['validate' => false]);
				$this->MobileTransferLogs->save($MobileTransferLogsEntity);
				$this->Flash->error(__('The mobile purchase has been saved.'));
				return $this->redirect(array('action' => 'index'));
			}else{
			   
			   if(!empty($MobilePurchasesEntity->errors())){
					foreach($MobilePurchasesEntity->errors() as $key){
						 foreach($key as $value){
							  $error[] = $value;
						 }
					}
			   }
			   $this->Flash->error(implode("</br>",$error),['escape' => false]);
                //echo'hi';die;
				$brandID = $this->request['data']['MobilePurchase']['brand_id'];
				$brands_query = $this->MobilePurchases->Brands->find('list',[
                                                                        'keyField' => 'id',
                                                                        'valueField' => 'brand',
														  'order'=>'brand asc',
                                                                        'conditions' => ['Brands.status' => 1,'Brands.id IN' => $activeBrands]
                                                                      ]
                                                               );
                $brands_query = $brands_query->hydrate(false);
                if(!empty($brands_query)){
                    $brands = $brands_query->toArray();
                }else{
                    $brands = array();
                }
				$networks_query = $this->Networks->find('list',[
                                                            'keyField' => 'id',
                                                            'valueField' => 'name'
                                                         ]
                                                  );
                $networks_query = $networks_query->hydrate(false);
                if(!empty($networks_query)){
                    $networks = $networks_query->toArray();
                }else{
                    $networks = array();
                }
				//foreach($brands as $brandID=>$brand)break;
				$mobileModels_query = $this->MobileModels->find('list',[
																	  'keyField' => 'id',
																	  'valueField' => 'model',
																	  'order'=>'model asc',
                                                                    //'fields' => array('id', 'model'),
                                                                    'conditions'=>[
                                                                                    'MobileModels.status' => 1,
                                                                                    'MobileModels.brand_id' => $brandID,
                                                                                    'MobileModels.id IN' => $activeModels
                                                                                  ]
                                                                 ]
                                                          );
                $mobileModels_query = $mobileModels_query->hydrate(false);
                if(!empty($mobileModels_query)){
                    $mobileModels = $mobileModels_query->toArray();
                }else{
                    $mobileModels = array();
                }
				$grades_description = $this->setting['grades_description'];
				$this->request->data = $this->request->data['MobilePurchase'];
				$this->set(compact('brands','mobileModels','brandID','networks','maximum_topup','mobileConditions','functionConditions','grades_description'));
				$this->Flash->error(__('The mobile purchase could not be saved. Please, try again.'));
				return;
			   
			   
//				$this->Flash->error(__('The mobile purchase could not be saved. Please, try again.'));
//				$brands_query = $this->MobilePurchases->Brands->find('list', array(
//																		   'keyField' => 'id',
//																		   'valueField' => 'brand',
//																			'conditions' => array('Brands.status' => 1,'Brands.id IN' => $activeBrands)
//																			)
//															);
//				$brands_query = $brands_query->hydrate(false);
//				if(!empty($brands_query)){
//					$brands = $brands_query->toArray();
//				}else{
//					$brands = array();
//				}
//				$networks_query = $this->Networks->find('list',array(
//																	 'keyField' => 'id',
//																	 'valueField' => 'name',
//																	 ));
//				$networks_query = $networks_query->hydrate(false);
//				if(!empty($networks_query)){
//					$networks = $networks_query->toArray();
//				}else{
//					$networks = array();
//				}
//				
//				foreach($brands as $brandID=>$brand)break;
//				$mobileModels_query = $this->MobileModels->find('list',array(
//																 'keyField' => 'id',
//																 'valueField' => 'model',
//																'conditions' => array(
//																					  'MobileModels.status' => 1,
//																					  'MobileModels.brand_id' => $brandID,
//																					  'MobileModels.id IN' => $activeModels)
//														));
//				$mobileModels_query = $mobileModels_query->hydrate(false);
//				if(!empty($mobileModels_query)){
//					$mobileModels = $mobileModels_query->toArray();
//				}else{
//					$mobileModels = array();
//				}
//				$this->set(compact('brands','mobileModels','brandID','networks','mobileConditions','functionConditions','maximum_topup'));
//                                return;
			}
		}elseif($this->request->session()->read('Auth.User.group_id') == KIOSK_USERS){
			$kiosk_id = $this->request->Session()->read('kiosk_id');
			$options = array('conditions' => array('MobilePurchases.id' => $id));
            $MobilePurchase_query = $this->MobilePurchases->find('all', $options);
            $MobilePurchase_query = $MobilePurchase_query->hydrate(false);
            if(!empty($MobilePurchase_query)){
                $MobilePurchase = $MobilePurchase_query->first();
            }else{
                $MobilePurchase = array();
            }
			$mobilePurchaseData = $this->request->data = $MobilePurchase;
			$created = $mobilePurchaseData["created"];
            $conn = ConnectionManager::get('default');
            $stmt = $conn->execute('SELECT NOW() as timeDate'); 
            $currentTime = $stmt ->fetchAll('assoc');
			$curTime = strtotime($currentTime[0]['timeDate']);
			$diffOfTime = $curTime - strtotime($created);
			//echo $diffOfTime;die();
			//LOG: CODE updated after requect received from Inder
			if(false){// || $diffOfTime > 600
				$diffInMins = number_format($diffOfTime / 60,2);
				$this->Flash->error("You can only edit the sale within 10 mintues<br/>(Current Difference: $diffInMins mins)");
				//{$created}!".$currentTime[0][0]['timeDate']."<br/>$diffOfTime = $curTime - $created"
				return $this->redirect(array('action' => 'index'));
				die;
			}
			if($kiosk_id != $mobilePurchaseData['kiosk_id']){
				$this->Flash->error("You can only edit the mobiles that belong to your kiosk!");
				return $this->redirect(array('action'=>'index'));
				die;
			}
		}
		
		
		$options = array('conditions' => array('MobilePurchases.id' => $id));
        $MobilePurchase_query = $this->MobilePurchases->find('all', $options);
        $MobilePurchase_query = $MobilePurchase_query->hydrate(false);
        if(!empty($MobilePurchase_query)){
            $MobilePurchase = $MobilePurchase_query->first();
        }else{
            $MobilePurchase = array();
        }
		$mobilePurchaseData = $this->request->data = $MobilePurchase;
		if($this->request->session()->read('Auth.User.group_id') != KIOSK_USERS){
			if($mobilePurchaseData['purchase_status'] == 1 && $mobilePurchaseData['custom_grades'] == 1){
				$this->Flash->error(__('Bulk purchased phone with custom grade can not be edited from this screen!'));
				return $this->redirect(array('action' => 'index'));
			}
		}
		$createdTime = date('Y-m-d',strtotime($mobilePurchaseData['created'])); 
		$currentTime =  date('Y-m-d');
		if($currentTime > $createdTime && $this->request->session()->read('Auth.User.group_id')==KIOSK_USERS){
			$this->Flash->error("Data can only be edited within same day from the created time");
			return $this->redirect(array('action'=>'index'));
			die;
		}
  
		$brandId = $mobilePurchaseData['brand_id'];
		$mobileModelId = $mobilePurchaseData['mobile_model_id'];
		$locked = $mobilePurchaseData['type'];
		$grade = $mobilePurchaseData['grade'];
		$condArr = array(
						 'MobilePrices.brand_id' => $brandId,
						 'MobilePrices.mobile_model_id' => $mobileModelId,
						 'MobilePrices.locked' => $locked,
						 'MobilePrices.grade' => $grade
						);
		$mobilePrice_query = $this->MobilePrices->find('all',array(
															  'conditions' => $condArr,
															  )
												);
        $mobilePrice_query = $mobilePrice_query->hydrate(false);
        if(!empty($mobilePrice_query)){
            $mobilePrice = $mobilePrice_query->first();
        }else{
            $mobilePrice = array();
        }
		if(empty($mobilePrice)){
			$maximum_topup = '';
		}else{
			$maximum_topup = $mobilePrice['maximum_topup'];
		}
		
		$networks_query = $this->Networks->find('list',[
                                                    'keyField' => 'id',
                                                    'valueField' => 'name'
                                                 ]);
        $networks_query = $networks_query->hydrate(false);
        if(!empty($networks_query)){
            $networks = $networks_query->toArray();
        }else{
            $networks = array();
        }
		$mobileModels_query = $this->MobileModels->find('list',[
															//'fields' => array('id', 'model'),
                                                            'keyField' => 'id',
                                                            'valueField' => 'model',
												'order'=>'model asc',
															'conditions' => [
																				'MobileModels.status' => 1,
																				'MobileModels.brand_id' => $brandId,
																				'MobileModels.id In' => $activeModels
                                                                            ]
                                                         ]
                                                  );
        $mobileModels_query = $mobileModels_query->hydrate(false);
        if(!empty($mobileModels_query)){
            $mobileModels = $mobileModels_query->toArray();
        }else{
            $mobileModels = array();
        }
		$kiosks_query = $this->MobilePurchases->Kiosks->find('list');
        $kiosks_query = $kiosks_query->hydrate(false);
        if(!empty($kiosks_query)){
            $kiosks = $kiosks_query->toArray();
        }else{
            $kiosks = array();
        }
		$brands_query = $this->MobilePurchases->Brands->find('list',[
																'keyField' => 'id',
																'valueField' => 'brand',
																'order'=>'brand asc',
																'conditions' => [
                                                                                    'Brands.status' => 1,
                                                                                    'Brands.id IN' => $activeBrands
                                                                                ]
                                                              ]
                                                       );
        $brands_query = $brands_query->hydrate(false);
        if(!empty($brands_query)){
            $brands = $brands_query->toArray();
        }else{
            $brands = array();
        }
		$this->set(compact('kiosks', 'brands','mobileModels','networks','grades_description'));
		$this->set(compact('maximum_topup','mobileConditions','functionConditions'));
	}

    public function delete($id = null){
        $this->request->allowMethod(['post', 'delete']);
        $mobilePurchase = $this->MobilePurchases->get($id);
        if ($this->MobilePurchases->delete($mobilePurchase)) {
            $this->Flash->success(__('The mobile purchase has been deleted.'));
        } else {
            $this->Flash->error(__('The mobile purchase could not be deleted. Please, try again.'));
        }

        return $this->redirect(['action' => 'index']);
    }
    
    public function mobileReport(){
        $CURRENCY_TYPE = Configure::read('CURRENCY_TYPE');
        $this->set(compact('CURRENCY_TYPE' ));
		$start_date =  date("Y-m-d") ;
		$end_date =  date("Y-m-d") ;
		$this->set(compact('start_date','end_date'));
		
        $external_sites = Configure::read('external_sites');
		  $path = dirname(__FILE__);
		  $ext_site = 0;
		  foreach($external_sites as $site_id => $site_name){
				$isboloRam = strpos($path,$site_name);
				if($isboloRam != false){
					$ext_site = 1;
				}
		  }
		  if($ext_site == 1){
			   $kiosk_ids = array();
			   $managerKiosk = $this->get_kiosk();//pr($managerKiosk);die;
			   if(!empty($managerKiosk)){
				   $kiosk_ids = $managerKiosk;		
			   }
			   if(!empty($kiosk_ids)){
					$mobilePurchases_query = $this->MobilePurchases->find('all',array(
 															 'fields' => array(
                                                                               'kiosk_id','purchased_by_kiosk'                                                                              ),
															  'conditions'=>array("DATE(MobilePurchases.created)"=>$start_date,
																				  'MobilePurchases.purchase_status'=>0,
																				   'MobilePurchases.kiosk_id IN'=>$kiosk_ids
																				  ),
															 
															  'group' => 'kiosk_id',
															  'order' => 'MobilePurchases.id desc'
															  )
												  );	
			   }else{
					$mobilePurchases_query = $this->MobilePurchases->find('all',array(
 															 'fields' => array(
                                                                               'kiosk_id','purchased_by_kiosk'                                                                              ),
															  'conditions'=>array("DATE(MobilePurchases.created)"=>$start_date,
																				  'MobilePurchases.purchase_status'=>0,
														//						   'MobilePurchases.kiosk_id IN'=>$kiosk_ids
																				  ),
															 
															  'group' => 'kiosk_id',
															  'order' => 'MobilePurchases.id desc'
															  )
												  );
			   }
			   
		  }else{
			   $mobilePurchases_query = $this->MobilePurchases->find('all',array(
 															 'fields' => array(
                                                                               'kiosk_id','purchased_by_kiosk'                                                                              ),
															  'conditions'=>array("DATE(MobilePurchases.created)"=>$start_date,
																				  'MobilePurchases.purchase_status'=>0
																				  
																				  ),
															 
															  'group' => 'kiosk_id',
															  'order' => 'MobilePurchases.id desc'
															  )
												  );
		  }
		
        $mobilePurchases_query 
                                ->select(["count" => "COUNT(MobilePurchases.id)"])
                                ->select(['total_cost' => $mobilePurchases_query->func()->sum('MobilePurchases.topedup_price')]);
		 
        $mobilePurchases_query = $mobilePurchases_query->hydrate(false);
        if(!empty($mobilePurchases_query)){
            $mobilePurchases = $mobilePurchases_query->toArray();
        }else{
            $mobilePurchases = array();
        }
       $kiosks_query = $this->Kiosks->find('list',
                                      [
                                        'keyField' => 'id',
                                        'valueField' => 'name',
                                        'order' => 'Kiosks.name asc'
                                    ]);
        if(!empty($kiosks_query)){
            $kiosks = $kiosks_query->toArray();
        }
		 
		$this->set(compact('mobilePurchases','kiosks'));
	}
    
    public function mobileReportSearch(){
		//pr($this->request->query);
		$currentTime =  date("Y-m-d") ;
		 
        $kiosks_query = $this->Kiosks->find('all',array(
													'fields' =>  array('id'),
													));
         $kiosks_query = $kiosks_query->hydrate(false);
        if(!empty($kiosks_query)){
            $kiosksids = $kiosks_query->toArray();
        }
       
		 foreach($kiosksids as $kiosksid){
			$kioskIDs[] = $kiosksid['id'];
		 }
         
		if(array_key_exists('start_date',$this->request->query) && !empty($this->request->query['start_date'])){
			$start_date = $this->request->query['start_date'];
			$this->set('start_date',$start_date);
		}
		if(array_key_exists('end_date',$this->request->query) &&  !empty($this->request->query['end_date'])){
			$end_date = $this->request->query['end_date'];
			$this->set('end_date',$end_date);
		}
		if(array_key_exists('kiosk_id',$this->request->query['data']['MobilePurchase']) &&
			   !empty($this->request->query['data']['MobilePurchase']['kiosk_id'])){
				 $kioskId = $this->request->query['data']['MobilePurchase']['kiosk_id'];
				 $this->set('kioskId', $kioskId);
				 
		}
		if(array_key_exists('start_date',$this->request->query) && !empty($start_date) &&
		   array_key_exists('end_date',$this->request->query) &&  !empty($end_date)){
				$conditionArr[] = array(
						"MobilePurchases.created >" => date('Y-m-d', strtotime($this->request->query['start_date'])),
						"MobilePurchases.created <" => date('Y-m-d', strtotime($this->request->query['end_date']. ' +1 Days')),			
					       );
		}else{
			$conditionArr[] = array(
									array("DATE(MobilePurchases.created)"=>$currentTime)
					       );
 
		}
		if(array_key_exists('kiosk_id',$this->request->query['data']['MobilePurchase']) &&
			   !empty($this->request->query['data']['MobilePurchase']['kiosk_id'])){
				$conditionArr[] = array('MobilePurchases.purchased_by_kiosk' => $kioskId);
		}else{
			 $conditionArr[] = array('MobilePurchases.purchased_by_kiosk IN' => $kioskIDs);//all Kiosk
		}
		$mobilePurchases_query = $this->MobilePurchases->find('all',array(
							'conditions' => array($conditionArr,"MobilePurchases.purchase_status"=>0),
							'fields' => array( 'purchased_by_kiosk','kiosk_id'),
							'group' => 'purchased_by_kiosk',
							'order' => 'MobilePurchases.id desc'
						  ));
        $mobilePurchases_query 
                                ->select(["count" => "COUNT(MobilePurchases.id)"])
                                ->select(['total_cost' => $mobilePurchases_query->func()->sum('MobilePurchases.topedup_price')]);
		 
        $mobilePurchases_query = $mobilePurchases_query->hydrate(false);
        if(!empty($mobilePurchases_query)){
            $mobilePurchases = $mobilePurchases_query->toArray();
        }else{
            $mobilePurchases = array();
        }
 
        $kiosks_query = $this->Kiosks->find('list',
                                      [
                                        'keyField' => 'id',
                                        'valueField' => 'name',
                                        'order' => 'Kiosks.name asc'
                                    ]);
        if(!empty($kiosks_query)){
            $kiosks = $kiosks_query->toArray();
        }
		 $CURRENCY_TYPE = Configure::read('CURRENCY_TYPE');
        $this->set(compact('CURRENCY_TYPE' )); 
		$this->set(compact('mobilePurchases','kiosks'));
		////$this->viewPath = 'MobilePurchases';
		$this->render('mobile_report');
		
		
	}
    public function mobileReportDetail($kiosk_id = '',$str_start = "",$str_end = ""){
		$start_date =  date("Y-m-d",$str_start) ;
		$end_date =  date("Y-m-d",$str_end) ;
		$this->set(compact('start_date','end_date'));
		$conditionArr = array();
		if(!empty($start_date) && !empty($end_date)){
			$conditionArr[] = array(
						"MobilePurchases.created >" => date('Y-m-d', strtotime($start_date)),
						"MobilePurchases.created <" => date('Y-m-d', strtotime($end_date. ' +1 Days')),			
					       );
		}
		
		
		$mobilePurchases_query = $this->MobilePurchases->find('all',array(
															  'conditions'=>array(
                                                                        $conditionArr,
                                                                        "MobilePurchases.purchased_by_kiosk"=>$kiosk_id,
                                                                        "MobilePurchases.purchase_status"=>0,
                                                                       // "DATE(MobilePurchase.created)"=>$currentTime
                                                                        ),
															  
															  'order' => 'MobilePurchases.id desc'
															  )
												  );
        $mobilePurchases_query = $mobilePurchases_query->hydrate(false);
        if(!empty($mobilePurchases_query)){
            $mobilePurchases = $mobilePurchases_query->toArray();
        }else{
            $mobilePurchases = array();
        }
		$purchase_ids = array();
		if(!empty($mobilePurchases)){
			foreach($mobilePurchases as $key => $value){
				$purchase_ids[] = $value['id'];
			}
		}
		$selling_result_query = $this->MobileReSales->find('all',array(
                                                                      'conditions' => array(
                                                                                        'mobile_purchase_id IN' => $purchase_ids
                                                                                        )
																
																));
        $selling_result_query = $selling_result_query->hydrate(false);
        if(!empty($selling_result_query)){
            $selling_result = $selling_result_query->toArray();
        }else{
            $selling_result = array();
        }
     	$sell_data = array();
		if(!empty($selling_result)){
			foreach($selling_result as $s_key => $s_value){
				$sell_data[$s_value['mobile_purchase_id']] = $s_value['discounted_price'];
			}
		}
		$this->set(compact('sell_data'));
        $brands_query = $this->Brands->find('list', [
                                                        'keyField' => 'id',
                                                        'valueField' => 'brand',
											 'order'=>'brand asc',
                                                        'conditions' =>['Brands.status' => 1]
                                                       
                                                    ]
                                            );
        $brands_query = $brands_query->hydrate(false);
        if(!empty($brands_query)){
             $brands = $brands_query->toArray();
        }else{
            $brands = array();
        }
		$network_query = $this->Networks->find('list', [
                                                        'keyField' => 'id',
                                                        'valueField' => 'name' 
                                                    ]
                                            );
        $network_query = $network_query->hydrate(false);
        if(!empty($network_query)){
             $networks = $network_query->toArray();
        }else{
            $networks = array();
        } 
		$model_query = $this->MobileModels->find('list', [
                                                        'keyField' => 'id',
                                                        'valueField' => 'model',
											 'order'=>'model asc',
                                                         'conditions' =>['MobileModels.status' => 1]
                                                         
                                                    ] 
                                            );
         $model_query = $model_query->hydrate(false);
        if(!empty($model_query)){
             $mobileModels = $model_query->toArray();
        } 
		$types = array('1'=>"Locked",'0'=>"Unlocked");
        $user_query = $this->Users->find('list', [
                                                        'keyField' => 'id',
                                                        'valueField' => 'username' 
                                                    ] 
                                            );
         $user_query = $user_query->hydrate(false);
        if(!empty($user_query)){
             $users = $user_query->toArray();
        } 
	 
		$this->set(compact('brands','mobileModels' ,'networks','types','users'));
         $kiosks_query = $this->Kiosks->find('list',array('fields' =>  array('id', 'name'), 'order' => array('Kiosks.name asc')));
         $kiosks_query = $kiosks_query->hydrate(false);
        if(!empty($kiosks_query)){
            $kiosks  = $kiosks_query->toArray();
        }
		//$kiosks = $this->Kiosk->find('list',array('fields' =>  array('id', 'name'), 'order' => array('Kiosk.name asc')));
		$this->set(compact('mobilePurchases','kiosks','kiosk_id'));
		 
	}
	public function mobileDetailSearch(){
		$currentTime =  date("Y-m-d") ;
		$kiosk_id = $this->request->query['kiosk_id'];
		if(array_key_exists('start_date',$this->request->query) && !empty($this->request->query['start_date'])){
			$start_date = $this->request->query['start_date'];
			$this->set('start_date',$start_date);
		}
		if(array_key_exists('end_date',$this->request->query) &&  !empty($this->request->query['end_date'])){
			$end_date = $this->request->query['end_date'];
			$this->set('end_date',$end_date);
		}
		if(array_key_exists('start_date',$this->request->query) && !empty($start_date) &&
		   array_key_exists('end_date',$this->request->query) &&  !empty($end_date)){
			$conditionArr[] = array(
						"MobilePurchases.created >" => date('Y-m-d', strtotime($this->request->query['start_date'])),
						"MobilePurchases.created <" => date('Y-m-d', strtotime($this->request->query['end_date']. ' +1 Days')),			
					       );
		}else{
			$conditionArr[] = array(
									array("DATE(MobilePurchases.created)"=>$currentTime)
					       );
 
		}
		if(array_key_exists('kiosk_id',$this->request->query) &&
			   !empty($kiosk_id)){
				$conditionArr[] = array('MobilePurchases.kiosk_id' => $kiosk_id);
		}
		
		$searchKW = trim(strtolower($this->request->query['search_kw']));
		if(!empty($searchKW) && !empty($start_date) && !empty($end_date)){
			$conditionArr['OR'] = array(
										'MobileModels.model like' => "%$searchKW%",
										'Brands.brand like' => "%$searchKW%",
										'MobilePurchases.imei like' => "%$searchKW%"
									);
			 
			$conditionArr['AND'] = array(
						"MobilePurchases.created >" => date('Y-m-d', strtotime($this->request->query['start_date'])),
						"MobilePurchases.created <" => date('Y-m-d', strtotime($this->request->query['end_date']. ' +1 Days')),			
			       );
				
			
		}
		if(!empty($searchKW) && empty($start_date) && empty($end_date)){
			if(!empty($searchKW)){
				$conditionArr['OR'] = array(
										'Brands.brand like' => "%$searchKW%",
										'MobileModels.model like' => "%$searchKW%",
										'MobilePurchases.imei like' => "%$searchKW%"
									);
				}
			
		}
		$mobilePurchases_query = $this->MobilePurchases->find('all',array(
							'conditions' => array($conditionArr,"MobilePurchases.purchase_status"=>0),
							'order' => 'MobilePurchases.id desc',
                              'contain' => ['Brands','MobileModels'] 
						  ));
        $mobilePurchases_query = $mobilePurchases_query->hydrate(false);
        if(!empty($mobilePurchases_query)){
            $mobilePurchases = $mobilePurchases_query->toArray();
        }else{
            $mobilePurchases = array();
        }
		
		$purchase_ids = array();
		if(!empty($mobilePurchases)){
			foreach($mobilePurchases as $key => $value){
				$purchase_ids[] = $value['id'];
			}
		}
		$selling_result_query = $this->MobileReSales->find('all',array(
                                                                       'conditions' => array(
                                                                                             'mobile_purchase_id IN' => $purchase_ids) 
															 
																));
         $selling_result_query = $selling_result_query->hydrate(false);
        if(!empty($selling_result_query)){
            $selling_result = $selling_result_query->toArray();
        }else{
            $selling_result = array();
        }
		$sell_data = array();
		if(!empty($selling_result)){
			foreach($selling_result as $s_key => $s_value){
				$sell_data[$s_value['mobile_purchase_id']] = $s_value['discounted_price'];
			}
		}
		$this->set(compact('sell_data'));
		 $brands_query = $this->Brands->find('list', [
                                                        'keyField' => 'id',
                                                        'valueField' => 'brand',
											 'order'=>'brand asc',
                                                        'conditions' =>['Brands.status' => 1]
                                                       
                                                    ]
                                            );
        $brands_query = $brands_query->hydrate(false);
        if(!empty($brands_query)){
             $brands = $brands_query->toArray();
        }else{
            $brands = array();
        }
		$network_query = $this->Networks->find('list', [
                                                        'keyField' => 'id',
                                                        'valueField' => 'name' 
                                                    ]
                                            );
        $network_query = $network_query->hydrate(false);
        if(!empty($network_query)){
             $networks = $network_query->toArray();
        }else{
            $networks = array();
        } 
		$model_query = $this->MobileModels->find('list', [
                                                        'keyField' => 'id',
                                                        'valueField' => 'model',
											 'order'=>'model asc',
                                                         'conditions' =>['MobileModels.status' => 1]
                                                         
                                                    ] 
                                            );
         $model_query = $model_query->hydrate(false);
        if(!empty($model_query)){
             $mobileModels = $model_query->toArray();
        } 
		 
        $user_query = $this->Users->find('list', [
                                                        'keyField' => 'id',
                                                        'valueField' => 'username' 
                                                    ] 
                                            );
         $user_query = $user_query->hydrate(false);
        if(!empty($user_query)){
             $users = $user_query->toArray();
        } 
	 
	 
         $kiosks_query = $this->Kiosks->find('list',array('fields' =>  array('id', 'name'), 'order' => array('Kiosks.name asc')));
         $kiosks_query = $kiosks_query->hydrate(false);
        if(!empty($kiosks_query)){
            $kiosks  = $kiosks_query->toArray();
        }
		 
		$types = array('1'=>"Locked",'0'=>"Unlocked");
		
		$this->set(compact('brands','mobileModels' ,'networks','types','users'));
		
		$this->set(compact('mobilePurchases','kiosks','kiosk_id'));
		//$this->layout = 'default';
		//$this->viewPath = 'MobilePurchases';
		$this->render('mobile_report_detail');
	}
    public function transient_mobiles(){
		if($this->request->Session()->read('Auth.User.group_id')==ADMINISTRATORS || $this->request->Session()->read('Auth.User.group_id') == MANAGERS){
			$kiosk_id = 10000;
		}else{
			$kiosk_id = $this->request->Session()->read('kiosk_id');
		}
		
		$users_query = $this->Users->find('list', array(
												  'keyField' => 'id',
												  'valueField' => 'username'
												  ));
		$users_query = $users_query->hydrate(false);
		if(!empty($users_query)){
		  $users = $users_query->toArray();
		}else{
		  $users = array();
		}
		$this->set('users', $users);
		$transientMobiles_query = $this->MobilePurchases->find('all',array('conditions'=>array('MobilePurchases.new_kiosk_id'=>$kiosk_id,'MobilePurchases.receiving_status'=>1)));
       // pr($transientMobiles_query);die;
		$transientMobiles_query = $transientMobiles_query->hydrate(false);
		if(!empty($transientMobiles_query)){
		  $transientMobiles = $transientMobiles_query->toArray();
		}else{
		  $transientMobiles = array();
		}
		$kioskIdArr = $brandIdArr = $mobileIdArr = array();
		foreach($transientMobiles as $key => $transientMobile){
			$kioskIdArr[$transientMobile['kiosk_id']] = $transientMobile['kiosk_id'];
			$brandIdArr[$transientMobile['brand_id']] = $transientMobile['brand_id'];
			$mobileIdArr[$transientMobile['mobile_model_id']] = $transientMobile['mobile_model_id'];
		}
		$kioskName_query = $this->Kiosks->find('list',array('conditions'=>array('Kiosks.id IN'=>$kioskIdArr)));
		if(!empty($kioskName_query)){
		  $kioskName = $kioskName_query->toArray();
		}else{
		  $kioskName = array();
		}
		
		
		$brandName_query = $this->Brands->find('list',array(
							     'conditions'=>array('Brands.id IN'=>$brandIdArr),
								 'keyField' => 'id',
								 'valueField' => 'brand',
								 'order'=>'brand asc'
							     ));
		if(!empty($brandName_query)){
		  $brandName = $brandName_query->toArray();
		}else{
		  $brandName = array();
		}
		
		$modelName_query = $this->MobileModels->find('list',array('conditions'=>array('MobileModels.id IN'=>$mobileIdArr),
																  'keyField' => 'id',
																  'valueField' => 'model',
																  'order'=>'model asc'
																  ));
		
		if(!empty($modelName_query)){
		  $modelName = $modelName_query->toArray();
		}else{
		  $modelName = array();
		}
		
		if($this->request->is('post')){
			//pr($this->request->data['transientMobiles']['receive']);die;
			$primaryIdArr = array();
			$receivingData = $this->request->data['transientMobiles']['receive'];
			foreach($receivingData as $rd => $receivingId){
				if($receivingId > 0){
					$primaryIdArr[] = $receivingId;
				}
			}
			//foreach($transientMobiles as $key=>$transientMobile){
			//	$mobileIdArr[] = $transientMobile['MobilePurchase']['id'];
			//}
			
			if(count($primaryIdArr)){
				if($this->MobilePurchase->updateAll(
					array('status' => "'0'",'kiosk_id' => "'$kiosk_id'",'receiving_status' => "'0'",'new_kiosk_id' => NULL),
					array('MobilePurchase.id IN' => $primaryIdArr)
					)
				   ){
					$counter = 0;
					foreach($transientMobiles as $key=>$transientMobile){
						if(in_array($transientMobile['MobilePurchase']['id'],$primaryIdArr)){
							$counter++;
							$this->MobileTransferLog->clear();
							$mobileTransferLogData = array(
									'mobile_purchase_reference' => $transientMobile['MobilePurchase']['mobile_purchase_reference'],
									'mobile_purchase_id' => $transientMobile['MobilePurchase']['id'],
									'kiosk_id' => $kiosk_id,
									'network_id' => $transientMobile['MobilePurchase']['network_id'],
									'grade' => $transientMobile['MobilePurchase']['grade'],
									'type' => $transientMobile['MobilePurchase']['type'],
									'receiving_status' => 0,
									'imei' => $transientMobile['MobilePurchase']['imei'],
									'user_id' => $this->Session->read('Auth.User.id'),
									'status' => 0
									);
							
							$this->MobileTransferLog->create();
							$this->MobileTransferLog->save($mobileTransferLogData);
						}
					}
				}
			}else{
				$this->Session->setFlash('Please select atlease one mobile to receive');
				$this->set(compact('transientMobiles','kioskName','brandName','modelName'));
				return;
			}
			
			
			if($counter>0){
				$this->Session->setFlash("$counter mobile(s) have been successfully received");
				return $this->redirect(array('controller'=>'home','action'=>'dashboard'));
			}
		}
		$hint = $this->ScreenHint->hint('mobile_purchases','transient_mobiles');
					if(!$hint){
						$hint = "";
					}
		$this->set(compact('hint','transientMobiles','kioskName','brandName','modelName'));
	}
    
    public function globalSearch() {//global mobile search
        $user_query = $this->Users->find('list', [
                                                        'keyField' => 'id',
                                                        'valueField' => 'username' 
                                                    ] 
                                            );
        $user_query = $user_query->hydrate(false);
        if(!empty($user_query)){
             $users = $user_query->toArray();
        } 
		$this->set('users', $users);
		$colorOptions = Configure::read('color');
		//pr($this-request);
		//mobile purchase status 0 => available, 1 => sold , 2 => reserved, 3 => sent for unlock 4 => under repair
		$kiosk_id = $this->request->session()->read('kiosk_id');
		$selectedkioskArr = array();
		$selectedkioskIds = array();
		//$this->MobilePurchase->recursive = 0;
		$external_sites = Configure::read('external_sites');
		  $path = dirname(__FILE__);
		  $ext_site = 0;
		  foreach($external_sites as $site_id => $site_name){
				$isboloRam = strpos($path,$site_name);
				if($isboloRam != false){
					$ext_site = 1;
				}
		  }
		  $kiosk_ids = array();
		//  if($ext_site == 1){
		//	   $managerKiosk = $this->get_kiosk();//pr($managerKiosk);die;
		//	   if(!empty($managerKiosk)){
		//		   $kiosk_ids = $managerKiosk;		
		//	   }
		//  }
		  if(!empty($kiosk_ids)){
			   $this->paginate = [
                            'conditions' => ['NOT' => ['MobilePurchases.status' =>1 ],
											 'kiosk_id IN' => $kiosk_ids,
											 ],
                            'order' =>['MobilePurchases.id desc'],
                            'limit' => ROWS_PER_PAGE,
                            'contain' => ['Kiosks','Brands']
                          ];
		  }else{
			$this->paginate = [
                            'conditions' => ['NOT' => ['MobilePurchases.status' =>1 ]],
                            'order' =>['MobilePurchases.id desc'],
                            'limit' => ROWS_PER_PAGE,
                            'contain' => ['Kiosks','Brands']
                          ];   
		  }
		  $external_sites = Configure::read('external_sites');
		  $path = dirname(__FILE__);
		  $ext_site = 0;
		  foreach($external_sites as $site_id => $site_name){
				$isboloRam = strpos($path,$site_name);
				if($isboloRam != false){
					$ext_site = 1;
				}
		  }
		  if($ext_site == 1){
			   $managerKiosk = $this->get_kiosk();
			  // pr($managerKiosk);
			   if(!empty($managerKiosk)){
					 $this->paginate = [
                            'conditions' => ['NOT' => ['MobilePurchases.status' =>1 ],
											 'kiosk_id IN' => $managerKiosk,
											 ],
                            'order' =>['MobilePurchases.id desc'],
                            'limit' => ROWS_PER_PAGE,
                            'contain' => ['Kiosks','Brands']
                          ];
					 
					//$this->paginate['conditions']['kiosk_id IN'] = $managerKiosk;
			   }
		  }
			   
          
     
         $mobilePurchases_query = $this->paginate('MobilePurchases');//die;
         
		 if(!empty($mobilePurchases_query)){
            $mobilePurchases = $mobilePurchases_query->toArray();
        }else{
            $mobilePurchases = array();
        }
		//$mobilePurchases = $this->Paginator->paginate();
        
		$modelIdsArr = array();
		$salePrice = array();
		foreach($mobilePurchases as $key=>$mobilePurchase){
			$modelIdsArr[$mobilePurchase['mobile_model_id']]=$mobilePurchase['mobile_model_id'];
			$data_query = $this->MobilePrices->find("all",array('fields' => array('brand_id', 'mobile_model_id','locked','sale_price'),
								'conditions' => array(
														'MobilePrices.brand_id' => $mobilePurchase['brand_id'],
														'MobilePrices.mobile_model_id'=>$mobilePurchase['mobile_model_id'],
														'MobilePrices.locked'=>$mobilePurchase['type'],
														'MobilePrices.grade'=>$mobilePurchase['grade'],
													)
													)
									 );
             $data_query = $data_query->hydrate(false);
            if(!empty($data_query)){
                 $data = $data_query->toArray();
            }
            if(empty($modelIdsArr)){
                $modelIdsArr = array('0'=>null);
            }
          //  pr($data);
            if(!empty($data)){
				$salePrice[$mobilePurchase['id']] = $data['0']['sale_price'];
			}
		}
		if(empty($modelIdsArr)){
		  $modelIdsArr = array(0 => null);
		}
		$model_query = $this->MobileModels->find('list', [
                                                        'keyField' => 'id',
                                                        'valueField' => 'model',
											 'order'=>'model asc',
                                                         'conditions' =>[
                                                                         'MobileModels.status' => 1,
                                                                         'MobileModels.id IN'=>$modelIdsArr
                                                                         ] 
                                                           
                                                    ] 
                                            );
         $model_query = $model_query->hydrate(false);
        if(!empty($model_query)){
             $mobileModels = $model_query->toArray();
        } 
		// pr($this->request->params);die;
		$pageNumber = $this->request->params['paging']['MobilePurchases']['page'];
		$sessionChosenImeis = $this->request->session()->read('chosenImeis');
		//pr($sessionChosenImeis);die;
		if(!empty($sessionChosenImeis)){
			foreach($sessionChosenImeis as $id => $imei){
				$result_query = $this->MobilePurchases->find('all',array('fields' => array('imei','mobile_model_id', 'brand_id','color'),
								      'conditions'=>array('MobilePurchases.imei' => $imei)));
                $result_query = $result_query->hydrate(false);
                if(!empty($result_query)){
                    $result[] = $result_query->toArray();
                }else{
                    $result[] = array();
                }
			}
            
            //pr($result);die;
			foreach($result as $key => $value){
                //pr($value);die;
				//echo  $value["MobilePurchase"]["imei"];
				 $model_result_query = $this->MobileModels->find('all',array('fields' => array('brand_id', 'model'),
								      'conditions'=>array('MobileModels.id' => $value[0]["mobile_model_id"])));
                 $model_result_query = $model_result_query->hydrate(false);
                 if(!empty($model_result_query)){
                    $model_result = $model_result_query->toArray();
                 }else{
                    $model_result = array();
                 }
				 $brnad_result_query = $this->Brands->find('all',array('fields' => array('id', 'brand'),
								      'conditions'=>array('Brands.id' => $value[0]["brand_id"])));
                 $brnad_result_query = $brnad_result_query->hydrate(false);
                 if(!empty($brnad_result_query)){
                    $brnad_result = $brnad_result_query->toArray();
                 }else{
                    $brnad_result = array();
                 }
				 $colour[$value[0]["imei"]] = $value[0]["color"];
				 $brand_array[$value[0]["imei"]] =  $brnad_result[0]["brand"];
				 $model_array[$value[0]["imei"]] = $model_result[0]["model"];
			}
		}
        $kiosks_query = $this->Kiosks->find('list',
                                      [
                                        'keyField' => 'id',
                                        'valueField' => 'name',
                                        'order' => 'Kiosks.name asc' 
                                    ]);
          $kiosks_query = $kiosks_query->hydrate(false);
        if(!empty($kiosks_query)){
            $kiosks = $kiosks_query->toArray();
        }
		//$kiosks = $this->Kiosk->find('list',array('fields' =>  array('id', 'name'), 'order' => array('Kiosk.name asc')));
		//pr($kiosks);die;
		//pr($kiosks);
		//$kiosks['0'] = 'Warehouse';
		//unset($kiosks['10000']);
		//pr($sessionChosenImeis);
		if(is_array($sessionChosenImeis)){
			$serialNo = 0;
			$flashTable = "";
			$selectedKiosk = $this->request->session()->read('selectedKiosk');
			if(empty($selectedKiosk)){
			   $this->Flash->error("Please select a kiosk to transfer the phones!!");
						//return $this->redirect(array('action'=>'global_search'));
						 //die;
			}
            //pr($brand_array);die;
			foreach($sessionChosenImeis as $purchaseId=>$chosenImei){
                //pr($chosenImei);die;
				$serialNo++;
				$flashTable.= "<tr>
						<td>".$serialNo."</td>
						<td>".$chosenImei."</td>
						<td>".$brand_array[$chosenImei]."</td>
						<td>".$model_array[$chosenImei]."</td>
						<td>".$colorOptions[$colour[$chosenImei]]."</td>
					</tr>";
			}
			
			if(!empty($flashTable)){
				$flashTable = "<table>
					<tr>
					<th>Kiosk: ".$kiosks[$selectedKiosk]."</th>
					</tr>
					<tr>
						<th>Serial No.</th>
						<th>Imei</th>
						<th>Brand</th>
						<th>Model</th>
						<th>Color</th>
					</tr>".$flashTable."
					</table>";
			}
            $this->Flash->success($flashTable,array('escape' => false,'clear'=> true));
            //pr($_SESSION);die;
			//$this->Session->setFlash($flashTable);
		}
		
		$type = array('1'=>"Locked",'0'=>"Unlocked");
         $network_query = $this->Networks->find('list', [
                                                        'keyField' => 'id',
                                                        'valueField' => 'name',
                                                    ] 
                                            );
        if(!empty($network_query)){
             $networks = $network_query->toArray();
        }else{
            $networks = array();
        }
		 
		$networks['0'] = "--";
		
		if($this->request->is('post')){
			if(array_key_exists('edit_basket',$this->request->data)){
				return $this->redirect(array('action'=>'global_search'));
				die;
			}
			if(!empty($this->request->data)){
                //pr($this->request->data);die;
				if(array_key_exists('transfer_reserved',$this->request->data['TransferMobile']) &&
				!empty($this->request->data['TransferMobile']['transfer_reserved'])
					    )
				{
					
					$transferReservedData = $this->request->data['TransferMobile']['transfer_reserved'];
					if(!empty($transferReservedData)){
						$reservedCount = 0;
						foreach($transferReservedData as $purchase_id=>$selected_kiosk){
							$tranBy = $this->request->Session()->read('Auth.User.id');
							$tranDate = $this->current_date_time();
							if($this->MobilePurchases->updateAll(
								array('status' => "'0'",'receiving_status' => "'1'",'transient_date' => "'$tranDate'",'transient_by' => "'$tranBy'"),
								array('MobilePurchases.id' => $purchase_id)
							)){
								$mobilePurchaseData_query = $this->MobilePurchases->find('all', array('conditions' => array('MobilePurchases.id' => $purchase_id)));
                            $mobilePurchaseData_query = $mobilePurchaseData_query->hydrate(false);
                            if(!empty($mobilePurchaseData_query)){
                                $mobilePurchaseData = $mobilePurchaseData_query->first();
                            }else{
                                $mobilePurchaseData = array();
                            }
							$mobileTransferLogData = array(
									'mobile_purchase_reference' => $mobilePurchaseData['mobile_purchase_reference'],
									'mobile_purchase_id' => $purchase_id,
									'kiosk_id' => $mobilePurchaseData['kiosk_id'],
									'network_id' => $mobilePurchaseData['network_id'],
									'grade' => $mobilePurchaseData['grade'],
									'type' => $mobilePurchaseData['type'],
									'receiving_status' => 1,
									'imei' => $mobilePurchaseData['imei'],
									'user_id' => $this->request->Session()->read('Auth.User.id'),
									'status' => 0
									);
								$reservedCount++;
							}
						}
						if($reservedCount==1){
							$this->Flash->success("Mobile with purchase id: $purchase_id has been transferred.",array('clear'=> true));
							return $this->redirect(array('action'=>'global_search'));
						}
					}
				}elseif(array_key_exists('add_2_basket',$this->request->data)){
					$chosenImeiArray = array();
					if(array_key_exists('transfer',$this->request->data['TransferMobile'])){
						 foreach($this->request->data['TransferMobile']['transfer'] as $purchaseId=>$chosenImei){
							 if(!empty($chosenImei)){
								 $chosenImeiArray[$purchaseId] = $chosenImei;
							 }
						 }
					}
					
					$sessionChosenImeis = $this->request->Session()->read('chosenImeis');
					
					if(array_key_exists('kiosk',$this->request->data['TransferMobile']) &&
					   $this->request->data['TransferMobile']['kiosk'] != ""
					   ){
						$this->request->Session()->write('selectedKiosk',$this->request->data['TransferMobile']['kiosk']);
					}else{
						$this->Flash->error("Please select a kiosk to transfer the phones!!",array('clear'=> true));
						return $this->redirect(array('action'=>'global_search'));
						die;
					}
					
					if(count($chosenImeiArray) > 0){
						$chosenImeiStr = implode(", ",$chosenImeiArray);
						$sumArrays = $this->add_arrays(array($sessionChosenImeis,$chosenImeiArray));
						$this->request->Session()->write('chosenImeis',$sumArrays);
						$diffResult = array_diff($chosenImeiArray,$sumArrays);
						if(empty($diffResult)){
							$message = "$chosenImeiStr imei(s) have been added to the basket";
						}else{
							$message = "Phones could not be added to the basket, please try after some time!";
						}
						$this->Flash->success($message,array('clear'=> true));
						return $this->redirect(array('action'=>"global_search/page:$pageNumber"));
						$sessionChosenImeis = $this->request->Session()->read('chosenImeis');
					}
				}elseif(array_key_exists('clear_basket',$this->request->data)){
					$this->request->Session()->delete('chosenImeis');
					$this->request->Session()->delete('selectedKiosk');
					$this->Flash->success('Basket is empty, please add new phones!',array('clear'=> true));
				}elseif(array_key_exists('check_out',$this->request->data)){
					//die('yes');
					return $this->redirect(array('action'=>'mobile_transfer_checkout'));
				}else{
                    //pr($this->request->Session()->read());die;
					//pr($this->request);die;
					if(array_key_exists('TransferMobile', $this->request->data)){
						$chosenImeiArray = array();
						if(array_key_exists('transfer',$this->request->data['TransferMobile'])){
							foreach($this->request->data['TransferMobile']['transfer'] as $purchaseId => $chosenImei){
								if(!empty($chosenImei)){
									$chosenImeiArray[$purchaseId] = $chosenImei;
								}
							}
						}else{
							$this->Flash->error("Please choose phone to transfer!",array('clear'=> true));
							return $this->redirect(array('action'=>"global_search"));
							die;
						}
						//pr($this->request->data['TransferMobile']);
						//pr($chosenImeiArray);
						$sessionChosenImeis = array();
						if(array_key_exists('chosenImeis',$this->request->Session()->read())){
							$sessionChosenImeis = $this->request->Session()->read('chosenImeis');
						}
						if(count($chosenImeiArray) > 0){
							$sumArrays = $this->add_arrays(array($sessionChosenImeis, $chosenImeiArray));
                           // pr($sumArrays);die;
							$this->request->Session()->write('chosenImeis',$sumArrays);
							if(count($sumArrays) == 0){
								$message = "Please select atleast one mobile";
								$this->Flash->error($message,array('clear'=> true));
								return $this->redirect(array('action'=>"global_search/page:$pageNumber"));
							}
						}/*else{
							  $this->Flash->error("Please choose phone to transfer!");
							return $this->redirect(array('action'=>"global_search"));
							die;
						}*/
						if(
						   array_key_exists('kiosk',$this->request->data['TransferMobile']) &&
							$this->request->data['TransferMobile']['kiosk'] != ""
						){
							$this->request->Session()->write('selectedKiosk',$this->request->data['TransferMobile']['kiosk']);
							$selectedKiosk = $this->request->Session()->read('selectedKiosk');
						}elseif(
								array_key_exists('selectedKiosk',$this->request->Session()->read()) &&
								(int)$this->request->Session()->read('selectedKiosk')
						){
							$selectedKiosk = $this->request->Session()->read('selectedKiosk');
						}else{
							$this->Flash->error("Please choose kiosk to transfer the phones!",array('clear'=> true));
							unset($_SESSION['chosenImeis']);
							return $this->redirect(array('action'=>"global_search/page:$pageNumber"));
							die;
						}
					}
					//Start:------------Mobile transfer block-------------
					$selectedKiosk = $this->request->Session()->read('selectedKiosk');
					$sessionChosenImeis = $this->request->Session()->read('chosenImeis');
					$imeiArray = $purchaseIdArr = array();
					
					if(!empty($sessionChosenImeis)){
						foreach($sessionChosenImeis as $purchaseId => $chosenImei){
							$purchaseIdArr[] = $purchaseId;
						}
					}
					
					$check = array();
                    if(empty($purchaseIdArr)){
                        $purchaseIdArr = array('0'=>null);
                    }
					if(!empty($purchaseIdArr)){
						$check_query = $this->MobilePurchases->find('all',array(
																'conditions' => array(
																					  'MobilePurchases.id IN' => $purchaseIdArr,
																					  'MobilePurchases.kiosk_id' => $selectedKiosk),
                                                                ));
                        $check_query = $check_query->hydrate(false);
                        if(!empty($check_query)){
                            $check = $check_query->toArray();
                        }else{
                            $check = array();
                        }
					}
					
					if(!empty($check)){
						$dupMobileIdArr = array();
						foreach($check as $key=>$checkDup){
							$dupMobileIdArr[] = $checkDup['id'];
						}
					}
								
					//check if the selected mobile already belongs to the kiosk
					$dupMobileStr = "";
					if(!empty($dupMobileIdArr)){
						$selectedKioskName = $kiosks[$selectedKiosk];
						$dupMobileStr = implode(", ",$dupMobileIdArr);
						$this->Flash->error("Selected mobiles with id:$dupMobileStr already belong to $selectedKioskName!",array('clear'=> true));
						$this->request->Session()->delete('chosenImeis');
						return $this->redirect(array('action'=>'global_search'));
						die;
					}
								
					$mobilePurchaseData_query = $this->MobilePurchases->find('all',array(
																		'conditions' => array('MobilePurchases.id IN' => $purchaseIdArr),
                                                                        ));
                    $mobilePurchaseData_query = $mobilePurchaseData_query->hydrate(false);
                    if(!empty($mobilePurchaseData_query)){
                        $mobilePurchaseData = $mobilePurchaseData_query->toArray();
                    }else{
                        $mobilePurchaseData = array();
                    }
					$saveNumbers = 0;
					foreach($mobilePurchaseData as $key => $mobilePurchaseInfo){
						$geId = $this->MobilePurchases->get($mobilePurchaseInfo['id']);
                        $data_array = array();
                        $data_array['new_kiosk_id'] = $selectedKiosk;
                        $data_array['receiving_status'] = '1';
                        $data_array['transient_date'] = $this->current_date_time();
                        $data_array['transient_by'] = $this->request->Session()->read('Auth.User.id');
                        $data_array['status'] = '0';
						$patchEntity = $this->MobileTransferLogs->patchEntity($geId,$data_array);
						$mobileTransferLogData = array(
														'mobile_purchase_reference' => $mobilePurchaseInfo['mobile_purchase_reference'],
														'mobile_purchase_id' => $mobilePurchaseInfo['id'],
														'kiosk_id' => $kiosk_id,
														'network_id' => $mobilePurchaseInfo['network_id'],
														'grade' => $mobilePurchaseInfo['grade'],
														'type' => $mobilePurchaseInfo['type'],
														'receiving_status' => 1,
														'imei' => $mobilePurchaseInfo['imei'],
														'user_id' => $this->request->Session()->read('Auth.User.id'),
														'status' => 0
													);
						
						$new_entity = $this->MobileTransferLogs->newEntity();
                        $patch_entity = $this->MobileTransferLogs->patchEntity($new_entity,$mobileTransferLogData);
						$this->MobileTransferLogs->save($patch_entity);
						if($this->MobilePurchases->save($patchEntity)){
							//$this->MobilePurchase->saveField('receiving_status','1');
							//$this->MobilePurchase->saveField('transient_date',$this->current_date_time());
							//$this->MobilePurchase->saveField('transient_by',$this->Session->read('Auth.User.id'));
							//$this->MobilePurchase->saveField('status','0');
							$saveNumbers++;
						}
					}
								
					if($saveNumbers > 0){
						$this->request->Session()->delete('chosenImeis');
						$this->request->Session()->delete('selectedKiosk');
						$this->Flash->success("$saveNumbers mobiles(s) have been transferred.",array('clear'=> true));
						return $this->redirect(array('action'=>"global_search/page:$pageNumber"));
					}
					//pr($sessionChosenImeis);die("fsdfa");
					//End:--------------Mobile transfer block------------- 
				}
			}
			//----------block moved from here--------------------
			//---------------------------------------------------
		}
        //echo "jj";die;
        
		$hint = $this->ScreenHint->hint('mobile_purchases','global_search');
					if(!$hint){
						$hint = "";
					}
		$this->set(compact('hint','mobileModels','mobilePurchases','kiosks','type','networks','selectedkioskArr','salePrice'));
	}
    
    public function bulkMobilePurchase(){
		
		//$this->MobilePurchase->validationErrors['purchase_cost'] = array("time is less than 30");
		$activeCombinations_query = $this->MobilePrices->find('all',array('conditions' => array('MobilePrices.status'=>1),
							      'fields' => array('mobile_model_id','brand_id'),
							      'group' => 'MobilePrices.mobile_model_id'
							      ));
        $activeCombinations_query = $activeCombinations_query->hydrate(false);
        if(!empty($activeCombinations_query)){
            $activeCombinations = $activeCombinations_query->toArray();
        }else{
            $activeCombinations = array();
        }
		$activeBrands = array();
		$activeModels = array();
		foreach($activeCombinations as $key => $activeCombination){
			$activeModels[$activeCombination['mobile_model_id']] = $activeCombination['mobile_model_id'];
			$activeBrands[$activeCombination['brand_id']] = $activeCombination['brand_id'];
		}
		$iemis = array();
		$currency = $this->setting['currency_symbol'];
		$status = array( '0' => 'Available', '1' => 'sold' , '2' => 'Reserved');
		$received = array( '1' =>'Transient', '0' =>'Received'  );
		$type = array('1'=> 'Locked', '0' => 'Unlocked');
		$discountOptions = Configure::read('discount');
		$gradeType = Configure::read('grade_type');
		$colorOptions = Configure::read('color');
		$countryOptions = Configure::read('uk_non_uk');
		$this->set(compact('status','colorOptions','discountOptions','countryOptions','gradeType','received','type','currency','iemis'));
		if($this->request->session()->read('Auth.User.group_id') == ADMINISTRATORS || $this->request->session()->read('Auth.User.group_id') == FRANCHISE_OWNER || $this->request->session()->read('Auth.User.group_id') == MANAGERS){
			$this->request->data['MobilePurchase']['kiosk_id']=0;
		}
		if ($this->request->is('post')) {
			$randNum = mt_rand (10000,99999);
			//pr($this->request->data);die;
			$mobilepurchases = $this->request->data;
			$model_Id = $this->request->data['MobilePurchase']['mobile_model_id'];
			$iemis = $this->request->data['MobilePurchase']['imei'];
			$colors = $this->request->data['MobilePurchase']['color'];
			$purchases = $this->request->data['MobilePurchase']['purchase_cost'];
			$type = $this->request->data['MobilePurchase']['type'];
			$grade = $this->request->data['MobilePurchase']['grade'];
			$selling_price = $this->request->data['MobilePurchase']['selling_price']; //newly added on Aug 8, 2016
			$lowest_selling_price = $this->request->data['MobilePurchase']['lowest_selling_price']; //newly added on Aug 8, 2016
			$mobilePurchaseReference = $this->request->data['MobilePurchase']['mobile_purchase_reference'];
			$network_id = $this->request->data['MobilePurchase']['network_id'];
			if(empty($lowest_selling_price)){
			   $lowest_selling_price = $selling_price;
			}
			$this->set(compact('iemis'));
			
			$errorArr = array();
			$successArr = array();
			if(empty($grade)){$errorArr[] = "Please choose the grade";}
			if($type == ""){$errorArr[]= "Please choose type locked or unlocked";}
			if(empty($purchases)){
				$errorArr[] = "Please input cost price";
			}elseif($purchases <= 0){
				$errorArr[] = "Cost Price should be greater than 0";
			}
			if(empty($mobilePurchaseReference)){
			   $errorArr[] = "Please input Purchase Reference";
			}
			if(empty($selling_price)){
				$errorArr[] = "Please input selling price";
				//$this->MobilePurchases->invalidate('selling_price', 'Please input selling price');
			}elseif($selling_price <= 0){
				$errorArr[] = "Cost Price should be greater than 0";
				//$this->MobilePurchases->invalidate('selling_price', 'Cost Price should be greater than 0');
			}
			//echo "hi";die;
			if($lowest_selling_price < $purchases){
				$errorArr[] = "Lowest Selling Price can not be less than Selling Price";
				//$this->MobilePurchases->invalidate('lowest_selling_price', 'Lowest Selling Price can not be less than Cost Price');
			}elseif($lowest_selling_price > $selling_price){
				$errorArr[] = "Lowest Selling Price can not be greter than Selling Price";
				//$this->MobilePurchases->invalidate('lowest_selling_price', 'Lowest Selling Price can not be greter than Selling Price');
			}
			
			if($selling_price < $purchases){
				$errorArr[] = "Selling Price can not be less than Cost Price";
				//$this->MobilePurchases->invalidate('selling_price', 'Selling Price can not be less than Cost Price');
			}
			
			if(empty($grade)){$errorArr[]= "Please choose the grade";}
			if($model_Id == 0 || empty($model_Id)){
				$errorArr[] = "Please choose the Mobile Model";
			}
			
			$errStrn = '';
			if(count($errorArr) > 0){
			   //pr($this->request);die;
			   $errStrn = implode('<br/>',$errorArr);
			   $this->Flash->error($errStrn,array('escape' => false));
			   $brandId = $this->request->data['MobilePurchase']['brand_id'];
			   if(empty($activeModels)){
					$activeModels = array(0 => null);
			   }
			   $mobileModels_query = $this->MobileModels->find('list',[
																   'keyField' => 'id',
																   'valueField' => 'model',
													  'order'=>'model asc',
																   'conditions'=>[
																				   'MobileModels.status' => 1,
																				   'MobileModels.brand_id' => $brandId,
																				   //'MobileModels.id IN' => $activeModels
																				 ]
                                                                  ]
                                                           );
                $mobileModels_query = $mobileModels_query->hydrate(false);
                if(!empty($mobileModels_query)){
                    $mobileModels = $mobileModels_query->toArray();
                }else{
                    $mobileModels = array();
                }
				$this->global_search_set_compacts($brandId,false);
				return;
			}
			
			$checkIfExist = array();
			$imeiArr = array();
			foreach($this->request->data['MobilePurchase']['imei'] as $key =>  $iemi)
			{
				$imeiArr[]=$iemi;
			}
			
			$doubleEntry = array();
			if(count($imeiArr)){
				$checkIfExist_query = $this->MobilePurchases->find('list',array(
																		  'keyField' => 'id',
																		  'valueField' => 'imei',
																		   'conditions'=>array('MobilePurchases.imei IN'=>$imeiArr,'MobilePurchases.status'=>0)));
				$checkIfExist_query = $checkIfExist_query->hydrate(false);
				if(!empty($checkIfExist_query)){
					$checkIfExist = $checkIfExist_query->toArray();
				}else{
					$checkIfExist = array();
				}
			}
			
			//pr($checkIfExist);die;
			
			foreach($imeiArr as $ki => $imei){
				if(strlen($imei) < 15 && !empty($imei)){
					$doubleEntry[] = "Inserted imei: $imei should be 15 digits";//using the same array as below to minimize the code
				}
			}
			
			
			$checkDoubleEntry = array_count_values($imeiArr);
			if(!empty($checkDoubleEntry)){
				foreach($checkDoubleEntry as $imei=>$countImei){
					if(!empty($imei) && $countImei>1){
						$doubleEntry[] = "Imei:$imei has been entered $countImei times. Imei should be a unique number";
					}
				}
			}
			
			$doubleEntryStr = '';
			if(count($doubleEntry) > 0){
				$doubleEntryStr = implode('<br/>',$doubleEntry);
				$this->Flash->error($doubleEntryStr,['escape' => false]);
				$brandId = $this->request->data['MobilePurchase']['brand_id'];
				$this->global_search_set_compacts($brandId);
				return;
				die;
			}
			$duplicateImeis = array();
			if(!empty($checkIfExist)){
				$checkIfExist = array_keys(array_flip($checkIfExist));
				foreach($checkIfExist as $k=>$checkDuplicate){
					$duplicateImeis[] = $checkDuplicate;
				}
			}
			if(!empty($duplicateImeis)){
				$alreadyExistStr = implode(", ",$duplicateImeis);
				$this->Flash->error("$alreadyExistStr already exist in database. Please try again");
				$brandId = $this->request->data['MobilePurchase']['brand_id'];
				$this->global_search_set_compacts($brandId);
				return;
				die;
			}
			$validationErrorArr = array();
			$validationErrorStr = "";
            //pr($this->request->data);die;
			foreach($this->request->data['MobilePurchase']['imei'] as $key =>  $iemi){
				$errors = array();
				if(!empty($iemi)){
					$this->request->data['MobilePurchase']['imei'] =  $iemi;
					$this->request->data['MobilePurchase']['cost_price'] =  $purchases;
					$this->request->data['MobilePurchase']['color'] =  $colors;
					$this->request->data['MobilePurchase']['type'] =  $type;
					$this->request->data['MobilePurchase']['grade'] =  $grade;
					$this->request->data['MobilePurchase']['mobile_purchase_reference'] = $mobilePurchaseReference;
					$this->request->data['MobilePurchase']['network_id'] = $network_id;
					$this->request->data['MobilePurchase']['user_id'] = $this->request->session()->read('Auth.User.id');
					$this->request->data['MobilePurchase']['customer_contact'] = '11111111111'; //for validation as per model
					$this->request->data['MobilePurchase']['rand_num'] = $randNum;
					$this->request->data['MobilePurchase']['purchase_status'] = 1;//for bulk
					$this->request->data['MobilePurchase']['custom_grades'] = 1;//for bulk
					$this->request->data['MobilePurchase']['static_selling_price'] = $this->request->data['MobilePurchase']['selling_price'];
					$kiosk_id = $this->request->data['MobilePurchase']['kiosk_id'];
					if($kiosk_id == 0 || $kiosk_id == "" || empty($kiosk_id)){
                        $this->request->data['MobilePurchase']['kiosk_id'] = 10000;
                        $kiosk_id = 10000;
                    }
					if($this->request->data['MobilePurchase']['type']==0){
						$this->request->data['MobilePurchase']['network_id']=NULL;
						$network_id=NULL;
					}
					$this->request->data['MobilePurchase']['purchased_by_kiosk'] = $kiosk_id;
					$new_entity = $this->MobilePurchases->newEntity();
					$patch_entity = $this->MobilePurchases->patchEntity($new_entity,$this->request->data['MobilePurchase'],['validate' => false]);
					//pr($patch_entity->errors());die;
					foreach($errors as $error){//pr($error);
					   $err = $error['0'];
					   $validationErrorArr[] = "$iemi: $err";
					}
                   
					if ($this->MobilePurchases->save($patch_entity)) {
						$mobilePurchaseId = $patch_entity->id;
						$mobileTransferLogData = array(
								'mobile_purchase_reference' => $this->request->data['MobilePurchase']['mobile_purchase_reference'],
								'mobile_purchase_id' => $mobilePurchaseId,
								'kiosk_id' => $kiosk_id,
								'imei' => $iemi,
								'user_id' => $this->request->Session()->read('Auth.User.id'),
								'network_id' => $network_id,
								'grade' => $grade,
								'type' => $type
								);
						$newEntity = $this->MobileTransferLogs->newEntity();
						$patchEntity = $this->MobileTransferLogs->patchEntity($newEntity,$mobileTransferLogData);
						$this->MobileTransferLogs->save($patchEntity);
						$successArr[] = "Imei with value: $iemi has been saved";
					}else{
                        //pr($patch_entity->errors());die;
                    }
				}
			}			
			
			$successStr = "";
			if(count($successArr) > 0){
				$successStr = implode('<br/>',$successArr);
				$this->Flash->success($successStr,['escape' => false]);
				return $this->redirect(array('action' => 'index'));
			}else{
			   
				if(count($validationErrorArr)){
					$validationErrorStr = implode("<br/>",$validationErrorArr);
				}
				if(!empty($validationErrorStr)){
					$this->Flash->success("$validationErrorStr",['escape' => false]);
				}else{
					$this->Flash->error(__("The mobile purchase could not be saved. Please, try again."));
				}
				
				$brandId = $this->request->data['MobilePurchase']['brand_id'];
				$this->global_search_set_compacts($brandId);
				return;
			}
		}
		$purchaseDate = date('Y-m-d');
		$kiosks_query = $this->Kiosks->find('list');
        $kiosks_query = $kiosks_query->hydrate(false);
        if(!empty($kiosks_query)){
            $kiosks = $kiosks_query->toArray();
        }else{
            $kiosks = array();
        }
		$brands_query = $this->Brands->find('list',[
                                                'keyField' => 'id',
                                                'valueField' => 'brand',
									   'order'=>'brand asc',
                                                'conditions' => ['Brands.status' => 1]
                                           ]
                                      );
        $brands_query = $brands_query->hydrate(false);
        if(!empty($brands_query)){
            $brands = $brands_query->toArray();
        }else{
            $brands = array();
        }
		$networks_query = $this->Networks->find('list',[
                                                    'keyField' => 'id',
                                                    'valueField' => 'name'
                                                 ]
                                          );
        $networks_query = $networks_query->hydrate(false);
        if(!empty($networks_query)){
            $networks = $networks_query->toArray();
        }else{
            $networks = array();
        }
		if(empty($activeModels)){
		  $activeModels = array(0 => null);
		}
		
		foreach($brands as $brandId=>$brandName)break;
		//$mobileModels = $this->MobileModel->find('list',array('fields' => array('id', 'model'),
		//						      'conditions'=>array('MobileModel.status' => 1,'MobileModel.brand_id'=>$brandId)));
		 $mobileModels_query = $this->MobileModels->find('list' ,[
                                                             
                                                            'keyField' => 'id',
                                                            'valueField' => 'model',
												'order'=>'model asc',
                                                            'conditions'=>[
                                                                            'MobileModels.status' => 1,
                                                                            'MobileModels.brand_id' => $brandId,
                                                                            //'MobileModels.id IN' => $activeModels
                                                                          ]
                                                          ]
                                                   );
         $mobileModels_query = $mobileModels_query->hydrate(false);
         if(!empty($mobileModels_query)){
            $mobileModels = $mobileModels_query->toArray();
         }else{
            $mobileModels = array();
         }
		 //pr($mobileModels);
		$this->set(compact('kiosks', 'brands','reference_nos','mobileModels','purchaseDate','networks'));
	}
    
    private function global_search_set_compacts($brandId = '', $activeCom = true){
		$activeCombinations = $this->MobilePrices->find('all',array('conditions' => array('MobilePrices.status'=>1),
							      'fields' => array('mobile_model_id','brand_id'),
							      'group' => 'MobilePrices.mobile_model_id'
							      ));
        $activeCombinations = $activeCombinations->hydrate(false);
        if(!empty($activeCombinations)){
            $activeCombinations = $activeCombinations->toArray();
        }else{
            $activeCombinations = array();
        }
		//pr($activeCombinations);
		$activeBrands = array();
		$activeModels = array();
		foreach($activeCombinations as $key => $activeCombination){
            //pr($activeCombination);die;
			$activeModels[$activeCombination['mobile_model_id']] = $activeCombination['mobile_model_id'];
			$activeBrands[$activeCombination['brand_id']] = $activeCombination['brand_id'];
		}
		$brandId = $this->request->data['MobilePurchase']['brand_id'];
		$purchaseDate = date('Y-m-d');
		$kiosks_query = $this->Kiosks->find('list');
        $kiosks_query = $kiosks_query->hydrate(false);
        if(!empty($kiosks_query)){
            $kiosks = $kiosks_query->toArray();
        }else{
            $kiosks = array();
        }
		$brands_query = $this->Brands->find('list',[
                                                'keyField' => 'id',
                                                'valueField' => 'brand',
									   'order'=>'brand asc',
                                                'conditions' => ['Brands.status' => 1]
                                             ]
                                      );
        $brands_query = $brands_query->hydrate(false);
        if(!empty($brands_query)){
            $brands = $brands_query->toArray();
        }else{
            $brands = array();
        }
		$networks_query = $this->Networks->find('list',[
                                                    'keyField' => 'id',
                                                    'valueField' => 'name'
                                                ]
                                        );
        $networks_query = $networks_query->hydrate(false);
        if(!empty($networks_query)){
            $networks = $networks_query->toArray();
        }else{
            $networks = array();
        }
		//$mobileModels = $this->MobileModel->find('list',array('fields' => array('id', 'model'),
		//						      'conditions'=>array('MobileModel.status' => 1,'MobileModel.brand_id'=>$brandId)));
		if($activeCom){
			$mobileModels_query = $this->MobileModels->find('list',[
                                                                'keyField' => 'id',
                                                                'valueField' => 'model',
												    'order'=>'model asc',
                                                                'conditions'=>[
                                                                                'MobileModels.status' => 1,
                                                                                'MobileModels.brand_id' => $brandId,
                                                                                'MobileModels.id IN' => $activeModels
                                                                              ]
                                                             ]
                                                      );
            $mobileModels_query = $mobileModels_query->hydrate(false);
            if(!empty($mobileModels_query)){
                $mobileModels_query = $mobileModels_query->toArray();
            }else{
                $mobileModels = array();
            }
		}else{
			$mobileModels_query = $this->MobileModels->find('list',[
                                                                'keyField' => 'id',
                                                                'valueField' => 'model',
												    'order'=>'model asc',
                                                                'conditions'=>[
                                                                                'MobileModels.status' => 1,
                                                                                'MobileModels.brand_id' => $brandId,
                                                                                //'MobileModel.id' => $activeModels
                                                                            ]
                                                            ]
                                                      );
            $mobileModels_query = $mobileModels_query->hydrate(false);
            if(!empty($mobileModels_query)){
                $mobileModels = $mobileModels_query->toArray();
            }else{
                $mobileModels = array();
            }
		}
		$this->set(compact('kiosks', 'brands','reference_nos','mobileModels','purchaseDate','networks'));
	}
	
	public function getBulkModels(){
		$brandId = $this->request->query('id');
		//$this->request->onlyAllow('ajax');
		
		$activeCombinations = $this->MobilePrices->find('all',array('conditions' => array('MobilePrices.status'=>1),
							      'fields' => array('mobile_model_id','brand_id'),
							      'group' => 'MobilePrices.mobile_model_id'
							      ));
        $activeCombinations = $activeCombinations->hydrate(false);
        if(!empty($activeCombinations)){
            $activeCombinations = $activeCombinations->toArray();
        }else{
            $activeCombinations = array();
        }
		//pr($activeCombinations);
		$activeBrands = array();
		$activeModels = array();
		foreach($activeCombinations as $key => $activeCombination){
            //pr($activeCombination);die;
			$activeModels[$activeCombination['mobile_model_id']] = $activeCombination['mobile_model_id'];
			$activeBrands[$activeCombination['brand_id']] = $activeCombination['brand_id'];
		}
		
		if(empty($activeModels)){
			   $activeModels = array(0 => null);
		}
		
		$mobileModels_query = $this->MobilePurchases->MobileModels->find('list',array(
																				'keyField' =>'id',
																				'valueField' => 'model',
									'order'=>'model asc',
																				
								   'conditions'=>array(
									'MobileModels.brand_id'=>$brandId,
									//'MobileModel.status'=>1,
									//'MobileModels.id IN' => $activeModels
									)
								   )
						      );
		$mobileModels_query = $mobileModels_query->hydrate(false);
		if(!empty($mobileModels_query)){
		  $mobileModels = $mobileModels_query->toArray();
		}else{
		  $mobileModels = array();
		}
		$this->set(compact('mobileModels')); // Pass $data to the view
		//$this->layout = false;
	}
    
    public function transientMobiles(){
		if(
         $this->request->session()->read('Auth.User.group_id') == ADMINISTRATORS ||
         $this->request->session()->read('Auth.User.group_id') == MANAGERS ||
         $this->request->session()->read('Auth.User.group_id') == inventory_manager
      ){
			$kiosk_id = 10000;
		}else{
			$kiosk_id = $this->request->Session()->read('kiosk_id');
		}
		
		$users_query = $this->Users->find('list',[
                                                'keyField' => 'id',
                                                'valueField' => 'username'
                                           ]
                                    );
      $users_query = $users_query->hydrate(false);
      if(!empty($users_query)){
         $users = $users_query->toArray();
      }else{
         $users = array();
      }
		$this->set('users', $users);
      
      if($kiosk_id == 0 or $kiosk_id == 10000){
         $transientMobiles_query = $this->MobilePurchases->find('all',array('conditions' => array(
                                                                                                 //'IN' => array('MobilePurchases.new_kiosk_id' => [0,10000]),
                                                                                                 //'MobilePurchases.new_kiosk_id'=>$kiosk_id,
                                                                                                  'MobilePurchases.new_kiosk_id IN'=> [0, 10000],
                                                                                                 'MobilePurchases.receiving_status'=>1
                                                                                                 ),
                                                                            'order' => 'id asc'
                                                                            ));
      }else{
         $transientMobiles_query = $this->MobilePurchases->find('all',array('conditions'=>array(
                                                                                                'MobilePurchases.new_kiosk_id'=>$kiosk_id,
                                                                                                'MobilePurchases.receiving_status'=>1),
                                                                            'order' => 'id asc')
                                                               );  
      }
      //pr($transientMobiles_query);die;
      $transientMobiles_query = $transientMobiles_query->hydrate(false);
      if(!empty($transientMobiles_query)){
         $transientMobiles = $transientMobiles_query->toArray();
      }else{
         $transientMobiles = array();
      }
      
		$kioskIdArr = $brandIdArr = $mobileIdArr = array();
		foreach($transientMobiles as $key=>$transientMobile){
			$kioskIdArr[$transientMobile['kiosk_id']] = $transientMobile['kiosk_id'];
			$brandIdArr[$transientMobile['brand_id']] = $transientMobile['brand_id'];
			$mobileIdArr[$transientMobile['mobile_model_id']] = $transientMobile['mobile_model_id'];
		}
      
		if(empty($kioskIdArr)){$kioskIdArr = array(0 => null);}
      $kioskName_query = $this->Kiosks->find('list',[
                                                      'conditions'=>['Kiosks.id IN'=>$kioskIdArr]
                                                      ]
                                              );
      $kioskName_query = $kioskName_query->hydrate(false);
      if(!empty($kioskName_query)){
          $kioskName = $kioskName_query->toArray();
      }else{
          $kioskName = array();
      }
      if(empty($brandIdArr)){
          $brandIdArr = array(0 => null);
      }
      
      $brandName_query = $this->Brands->find('list',[
                                                 'conditions'=>['Brands.id IN'=>$brandIdArr],
                                                 'keyField' => 'id',
                                                 'valueField' => 'brand',
                                                   'order'=>'brand asc'
                                                ]
                                          );
      $brandName_query = $brandName_query->hydrate(false);
      if(!empty($brandName_query)){
          $brandName = $brandName_query->toArray();
      }else{
          $brandName = array();
      }
      if(empty($mobileIdArr)){
          $mobileIdArr = array(0 => null);
      }
      $modelName_query = $this->MobileModels->find('list',[
                                                     'conditions'=>['MobileModels.id IN'=>$mobileIdArr],
                                                     'keyField' => 'id',
                                                     'valueField' => 'model',
                               'order'=>'model asc'
                                                   ]
                                            );
      $modelName_query = $modelName_query->hydrate(false);
      if(!empty($modelName_query)){
         $modelName = $modelName_query->toArray();
      }else{
         $modelName = array();
      }
        
		if($this->request->is('post')){
			//pr($this->request->data['transientMobiles']['receive']);die;
			$primaryIdArr = array();
			$receivingData = $this->request->data['transientMobiles']['receive'];
			foreach($receivingData as $rd => $receivingId){
				if($receivingId > 0){
					$primaryIdArr[] = $receivingId;
				}
			}
			//foreach($transientMobiles as $key=>$transientMobile){
			//	$mobileIdArr[] = $transientMobile['MobilePurchase']['id'];
			//}
			//echo $kiosk_id;die;
			if(count($primaryIdArr)){
            if($kiosk_id == 0) $kiosk_id = 10000;
				if($this->MobilePurchases->updateAll(
					array('status' => "'0'",'kiosk_id' => "$kiosk_id",'receiving_status' => "'0'",'new_kiosk_id' => NULL),
					array('MobilePurchases.id IN' => $primaryIdArr)
					)
				   ){
					$counter = 0;
					foreach($transientMobiles as $key=>$transientMobile){
						if(in_array($transientMobile['id'],$primaryIdArr)){
							$counter++;
							$mobileTransferLogData = array(
									'mobile_purchase_reference' => $transientMobile['mobile_purchase_reference'],
									'mobile_purchase_id' => $transientMobile['id'],
									'kiosk_id' => $kiosk_id,
									'network_id' => $transientMobile['network_id'],
									'grade' => $transientMobile['grade'],
									'type' => $transientMobile['type'],
									'receiving_status' => 0,
									'imei' => $transientMobile['imei'],
									'user_id' => $this->request->Session()->read('Auth.User.id'),
									'status' => 0
									);
							
							$MobileTransferLogsEntity = $this->MobileTransferLogs->newEntity($mobileTransferLogData,['validate' => false]);
							$MobileTransferLogsEntity = $this->MobileTransferLogs->patchEntity($MobileTransferLogsEntity,$mobileTransferLogData,['validate' => false]);
							$res =  $this->MobileTransferLogs->save($MobileTransferLogsEntity);
							//pr($res);die;
						}
					}
				}
			}else{
				$this->Flash->error('Please select atlease one mobile to receive');
				$this->set(compact('transientMobiles','kioskName','brandName','modelName'));
				return;
			}
			
			
			if($counter>0){
				$this->Flash->success("$counter mobile(s) have been successfully received");
				return $this->redirect(array('controller'=>'home','action'=>'dashboard'));
			}
		}
		$hint = $this->ScreenHint->hint('mobile_purchases','transient_mobiles');
					if(!$hint){
						$hint = "";
					}
                    //pr($transientMobiles);die;
		$this->set(compact('hint','transientMobiles','kioskName','brandName','modelName'));
	}
    
    public function referenceNumberListing(){
	 
	 
	 $kiosk_id = $this->request->Session()->read('kiosk_id');
	 
		  $color = Configure::read('color');
		 if(!empty($kiosk_id) && $kiosk_id != 10000){
			   $MobilePurchases = $this->MobilePurchases->find('all',array('conditions' => array(
								   'MobilePurchases.purchased_by_kiosk' => $kiosk_id,
									'NOT' => array('MobilePurchases.mobile_purchase_reference IS NULL')
									),'group' => array('MobilePurchases.mobile_purchase_reference',
										 'MobilePurchases.rand_num'),'limit' => ROWS_PER_PAGE,'order'=>'MobilePurchases.created desc'));
			   $MobilePurchases
								 ->select('MobilePurchases.mobile_purchase_reference')
								 ->select('MobilePurchases.rand_num')
								 ->select('MobilePurchases.created')
								 ->select('MobilePurchases.color')
								 ->select('MobilePurchases.mobile_model_id')
								 ->select(['count' => $MobilePurchases->func()->count('MobilePurchases.mobile_purchase_reference')]);  
		 }else{
			   $MobilePurchases = $this->MobilePurchases->find('all',array('conditions' => array(
									'NOT' => array('MobilePurchases.mobile_purchase_reference IS NULL')
									),'group' => array('MobilePurchases.mobile_purchase_reference',
										 'MobilePurchases.rand_num'),'limit' => ROWS_PER_PAGE,'order'=>'MobilePurchases.created desc'));
			   $MobilePurchases
								 ->select('MobilePurchases.mobile_purchase_reference')
								 ->select('MobilePurchases.rand_num')
								 ->select('MobilePurchases.created')
								 ->select('MobilePurchases.color')
								 ->select('MobilePurchases.mobile_model_id')
								 ->select(['count' => $MobilePurchases->func()->count('MobilePurchases.mobile_purchase_reference')]);  
		 }
        
        //pr($MobilePurchases);die;
		//$this->paginate = [
		//						'conditions' => array(
		//							'NOT' => array('MobilePurchases.mobile_purchase_reference'=>NULL)
		//							),
		//						'fields' => array('MobilePurchases.mobile_purchase_reference','MobilePurchases.rand_num','MobilePurchases.created','count(MobilePurchases.mobile_purchase_reference) as count'),
		//						'group' => array('MobilePurchases.mobile_purchase_reference',
		//								 'MobilePurchases.rand_num'),
		//						'order'=>['MobilePurchases.created desc'],
		//						'limit' => ROWS_PER_PAGE,
		//				];
		$this->paginate = ['limit' => ROWS_PER_PAGE];
		$referenceNumbers_query = $this->paginate($MobilePurchases);
        if(!empty($referenceNumbers_query)){
            $referenceNumbers = $referenceNumbers_query->toArray();
        }else{
            $referenceNumbers = array();
        }
		
		$mobileModels_query = $this->MobileModels->find('list',array(
																					  'keyField' => 'id',
																					  'valueField' => 'model',
								   )
						      );
		$mobileModels = $mobileModels_query->hydrate(false);
		if(!empty($mobileModels_query)){
		  $mobileModels = $mobileModels_query->toArray();
		}else{
		 $mobileModels = array(); 
		}
		
		$hint = $this->ScreenHint->hint('mobile_purchases','reference_number_listing');
					if(!$hint){
						$hint = "";
					}
		$this->set(compact('hint','referenceNumbers','color','mobileModels'));
	}
    
    public function mobileListingPerReference($reference='',$randNum = ''){
	  $color = Configure::read('color');
		$status = array( '0' => 'Available', '1' => 'sold' , '2' => 'Reserved', '3' => 'Sent for unlock', '4' => 'Sent for repair');
		$type = array('1'=>"Locked",'0'=>"Unlocked");
		$networks_query = $this->Networks->find('list',[
                                                    'keyField' => 'id',
                                                    'valueField' => 'name'
                                                 ]
                                          );
        $networks_query = $networks_query->hydrate(false);
        if(!empty($networks_query)){
            $networks = $networks_query->toArray();
        }else{
            $networks = array();
        }
		$networks[0] = '--';
		$networks[""] = '--';
		$kiosks_query = $this->Kiosks->find('list');
        $kiosks_query = $kiosks_query->hydrate(false);
        if(!empty($kiosks_query)){
            $kiosks = $kiosks_query->toArray();
        }else{
            $kiosks = array();
        }
		//$kiosks['0'] = 'Warehouse';
		
		$external_sites = Configure::read('external_sites');
		  $path = dirname(__FILE__);
		  $ext_site = 0;
		  foreach($external_sites as $site_id => $site_name){
				$isboloRam = strpos($path,$site_name);
				if($isboloRam != false){
					$ext_site = 1;
				}
		  }
		  if($ext_site == 1){
			   $managerKiosk = $this->get_kiosk();
			   if(!empty($managerKiosk)){
					$this->paginate = [
							'conditions' => [
										      'MobilePurchases.mobile_purchase_reference'=>$reference,
											  'MobilePurchases.kiosk_id IN'=>$managerKiosk,
										      'MobilePurchases.rand_num'=>$randNum
                                            ],
						];
			   }else{
					$this->paginate = [
							'conditions' => [
										      'MobilePurchases.mobile_purchase_reference'=>$reference,
										      'MobilePurchases.rand_num'=>$randNum
                                            ],
						];
			   }
		  }else{
			   $this->paginate = [
							'conditions' => [
										      'MobilePurchases.mobile_purchase_reference'=>$reference,
										      'MobilePurchases.rand_num'=>$randNum
                                            ],
						];
		  }
		
		$mobileListing_query = $this->paginate('MobilePurchases');
        if(!empty($mobileListing_query)){
            $mobileListing = $mobileListing_query->toArray();
        }else{
            $mobileListing = array();
        }
		$brandArr = array();
		$modelArr = array();
		foreach($mobileListing as $key=>$data){
			$brandArr[] = $data->brand_id;
			$modelArr[] = $data->mobile_model_id;
		}
		
		if(!empty($modelArr)){
			$modelName_query = $this->MobileModels->find('list',[
                                                            'conditions'=>['MobileModels.id IN'=>$modelArr],
                                                            'keyField' => 'id',
                                                            'valueField' => 'model',
												'order'=>'model asc'
                                                          ]
                                                   );
            $modelName_query = $modelName_query->hydrate(false);
            if(!empty($modelName_query)){
                $modelName = $modelName_query->toArray();
            }else{
                $modelName = array();
            }
		}
		
		if(!empty($brandArr)){
			$brandName_query = $this->Brands->find('list',[
                                                        'conditions'=>['Brands.id IN'=>$brandArr],
                                                        'keyField' => 'id',
                                                        'valueField' => 'brand',
											 'order'=>'brand asc'
                                                    ]
                                            );
            $brandName_query = $brandName_query->hydrate(false);
            if(!empty($brandName_query)){
                $brandName = $brandName_query->toArray();
            }else{
                $brandName = array();
            }
		}
		$this->set(compact('mobileListing','reference','status','type','networks','kiosks','modelName','brandName','randNum','color'));
	}
    
    public function searchGlobally(){
		//mobile purchase status 0 => available, 1 => sold , 2 => reserved, 3 => sent for unlock 4 => under repair
		$searchKW = $this->request->query['search_kw'];
		
		$conditionArr = array();
		
		if(array_key_exists('transient_status',$this->request->query)){
			$transient_status = $this->request->query['transient_status'];
			if(trim($transient_status) != ""){
			$conditionArr['MobilePurchases.receiving_status'] = $transient_status;
			}
		}
		//highlight transient status
		
		$kioskIDs = array();
		if(array_key_exists('kiosk_ids',$this->request->query)){
			if(!in_array(-1,$this->request->query['kiosk_ids'])){
				  $kioskIDs = $this->request->query['kiosk_ids'];
			}
		}
		//pr($kioskIDs);
		$selectedkioskIds = array();
		if(array_key_exists('kiosk_ids',$this->request->query)){
			$selectedkioskIds = $this->request->query['kiosk_ids'];
		}
		//pr($selectedkioskIds);//die;
		$selectedkioskArr = array();
		foreach($kioskIDs as $key=>$kioskId){
			$selectedkioskArr[$kioskId] = $kioskId;
		}
		//pr($selectedkioskArr);//die;
		$this->set('kioskIDs',$kioskIDs);
		$this->set('selectedkioskArr',$selectedkioskArr);
		
		if(count($kioskIDs)){
			$conditionArr['MobilePurchases.kiosk_id IN'] = $kioskIDs;
		}else{
		  $external_sites = Configure::read('external_sites');
		  $path = dirname(__FILE__);
		  $ext_site = 0;
		  foreach($external_sites as $site_id => $site_name){
				$isboloRam = strpos($path,$site_name);
				if($isboloRam != false){
					$ext_site = 1;
				}
		  }
		  if($ext_site == 1){
			   $managerKiosk = $this->get_kiosk();
			 //  pr($managerKiosk);
			   if(!empty($managerKiosk)){
				 
					 $conditionArr['MobilePurchases.kiosk_id IN'] = $managerKiosk;
			   }
		  }
		}
		
		  
		
		//$reserve_status = $this->request->query['reserve_status'];
		
		if(array_key_exists('Reserve_status',$this->request->query)){
			$reserve_status = $this->request->query['Reserve_status'];
			if($reserve_status == 1){
				$conditionArr['MobilePurchases.status'] = 2;
			}elseif($reserve_status == 2){
				$conditionArr['MobilePurchases.status'] = 0;
				//$conditionArr['MobilePurchase.receiving_status'] = 0;
			}
		}
		if(!empty($searchKW)){
			$conditionArr['OR'] = array(
										'MobileModels.model like' => "%$searchKW%",
										//'Brand.brand like' => "%$searchKW%",
										'MobilePurchases.customer_fname like' => "%$searchKW%",
										'MobilePurchases.customer_email like' => "%$searchKW%",
										'MobilePurchases.imei like' => "%$searchKW%"
									);
		}
		$conditionArr['NOT'] = array('MobilePurchases.status'=>1);
		$this->paginate = [
                            'conditions' => $conditionArr,
                            'limit' => ROWS_PER_PAGE,
                            'contain' => ['Kiosks','Brands','MobileModels'],
							'order' => ['MobilePurchases.id desc']
                            //'recursive' => 1
                          ];
		//pr($this->paginate);die;
		//$kiosks = $this->Kiosk->find('list');
		$kiosks_query = $this->Kiosks->find('list',[
                                                'fields' =>  array('id', 'name'),
                                                'order' => ['Kiosks.name asc']
                                             ]
                                      );
        $kiosks_query = $kiosks_query->hydrate(false);
        if(!empty($kiosks_query)){
            $kiosks = $kiosks_query->toArray();
        }else{
            $kiosks = array();
        }
		//$kiosks['0'] = 'Warehouse';
		//unset($kiosks['10000']);
		$mobilePurchases_query = $this->paginate('MobilePurchases');
        if(!empty($mobilePurchases_query)){
            $mobilePurchases = $mobilePurchases_query->toArray();
        }else{
            $mobilePurchases = array();
        }
        //pr($mobilePurchases);die;
		$modelIdsArr = array();
		foreach($mobilePurchases as $key=>$mobilePurchase){
			$modelIdsArr[$mobilePurchase->mobile_model_id]=$mobilePurchase->mobile_model_id;
			$data_query = $this->MobilePrices->find("all",array('fields' => array('brand_id', 'mobile_model_id','locked','sale_price'),
										'conditions'=>array(
													'MobilePrices.brand_id' => $mobilePurchase->brand_id,
										'MobilePrices.mobile_model_id'=>$mobilePurchase->mobile_model_id,														'MobilePrices.locked'=>$mobilePurchase->type,
											'MobilePrices.grade'=>$mobilePurchase->grade,
															)
										)
						 );
            $data_query = $data_query->hydrate(false);
            if(!empty($data_query)){
                $data = $data_query->first();
            }else{
                $data = array();
            }
			if(!empty($data)){
				$salePrice[$mobilePurchase['id']] = $data['sale_price'];
			}
		}
        if(empty($modelIdsArr)){
            $modelIdsArr = array(0 => null);
        }
		$mobileModels_query = $this->MobileModels->find('list',[
                                                            'keyField' => 'id',
                                                            'valueField' => 'model',
												'order'=>'model asc',
                                                            'conditions'=>['MobileModels.status' => 1,'MobileModels.id IN'=>$modelIdsArr]
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
                                                    'valueField' => 'name'
                                                 ]
                                          );
        $networks_query = $networks_query->hydrate(false);
        if(!empty($networks_query)){
            $networks = $networks_query->toArray();
        }else{
            $networks = array();
        }
		$type = array('1'=>"Locked",'0'=>"Unlocked");
		$users_query = $this->Users->find('list',[
                                                'keyField' => 'id',
                                                'valueField' => 'username'
                                           ]
                                    );
        $users_query = $users_query->hydrate(false);
        if(!empty($users_query)){
            $users = $users_query->toArray();
        }else{
            $users = array();
        }
		$hint = $this->ScreenHint->hint('mobile_purchases','global_search');
					if(!$hint){
						$hint = "";
					}
		$this->set('users', $users);
		$this->set(compact('hint','mobilePurchases','mobileModels','kiosks','networks','type','salePrice'));
		//$this->layout = 'default';
		//$this->viewPath = 'MobilePurchases';
		$this->render('global_search');
	}
    
    public function reserve($purchaseId = "", $kioskId = ""){
		if($kioskId == ""){
			$this->Flash->error("No Kiosk Selected");
			return $this->redirect(array('action'=>'global_search'));
			die;
		}
		//mobile purchase status 0 => available, 1 => sold , 2 => reserved, 3 => sent for unlock 4 => under repair
		$checkIfDuplicate_query = $this->MobilePurchases->find('all',array('conditions'=>array('MobilePurchases.id'=>$purchaseId)));
      $checkIfDuplicate_query = $checkIfDuplicate_query->hydrate(false);
      if(!empty($checkIfDuplicate_query)){
          $checkIfDuplicate = $checkIfDuplicate_query->first();
      }else{
          $checkIfDuplicate = array();
      }
      
		if($kioskId == $checkIfDuplicate['kiosk_id']){
			$this->Flash->error("Chosen kiosk should be different from the current kiosk");
			return $this->redirect(array('action' => 'global_search'));
			die;
		}
      
		$new_entity = $this->MobilePurchases->get($purchaseId);
      $data_array = array();
      $data_array['status'] = '2';
      $data_array['new_kiosk_id'] = $kioskId;
      $data_array['reserve_date'] = $this->current_date_time();
      $data_array['reserved_by'] = $this->request->Session()->read('Auth.User.id');
      $patch_entity = $this->MobilePurchases->patchEntity($new_entity,$data_array);
      
		if($this->MobilePurchases->save($patch_entity)){
				$mobileTransferLogData = array(
						'mobile_purchase_reference' => $checkIfDuplicate['mobile_purchase_reference'],
						'mobile_purchase_id' => $checkIfDuplicate['id'],
						'kiosk_id' => $kioskId,
						'network_id' => $checkIfDuplicate['network_id'],
						'grade' => $checkIfDuplicate['grade'],
						'type' => $checkIfDuplicate['type'],
						'receiving_status' => NULL,
						'imei' => $checkIfDuplicate['imei'],
						'user_id' => $this->request->Session()->read('Auth.User.id'),
						'status' => 3
						);
				
				$newEntity = $this->MobileTransferLogs->newEntity();
                $patchEntity = $this->MobileTransferLogs->patchEntity($newEntity,$mobileTransferLogData);
				$this->MobileTransferLogs->save($patchEntity);
			$this->Flash->success('Phone has been reserved');
			return $this->redirect(array('action'=>'global_search'));
		}
	}
    
    public function mobileTransferCheckout(){
		$kiosks_query = $this->Kiosks->find('list');
        $kiosks_query = $kiosks_query->hydrate(false);
        if(!empty($kiosks_query)){
            $kiosks = $kiosks_query->toArray();
        }else{
            $kiosks = array();
        }
		$this->set(compact('kiosks'));
	}
    
    private function add_arrays($array = array()){
		$sumArray = array();
		foreach($array as $sngArr){
			if(is_array($sngArr)){
				foreach($sngArr as $key=>$value){
				if(!array_key_exists($key,$sumArray)){
						$sumArray[$key] = $value;
					}
				}
			}
		}
		return $sumArray;
	}
    
    public function deleteMobileFromSession($chosenImei = null){
		$chosenImeis = $this->request->Session()->read('chosenImeis');
		if($chosenImei>0){
            //pr($chosenImei);die;
            foreach($chosenImeis as $key => $value){
                if($value == $chosenImei){
                    unset($chosenImeis[$key]);
                }
            }
//			unset($chosenImeis[$chosenImei]);
//            pr($chosenImeis);die;
			$this->request->Session()->write('chosenImeis',$chosenImeis);
			return $this->redirect(array('action'=>'mobile_transfer_checkout'));
		}else{
			$this->Flash->error("Please choose a valid Imei!");
			return $this->redirect(array('action'=>'mobile_transfer_checkout'));
		}
	}
    
    public function mobileTransferLogs($imei = ""){
		$mobilePurchase_query = $this->MobilePurchases-> find('all', array(
			//'fields' => array('mobile_model_id', 'brand_id'),
			'conditions' => array('imei' =>$imei)
		));
        $mobilePurchase_query = $mobilePurchase_query->hydrate(false);
        if(!empty($mobilePurchase_query)){
            $mobilePurchase = $mobilePurchase_query->first();
        }else{
            $mobilePurchase = array();
        }
		
		$brand_id = $mobilePurchase['brand_id'];
		$mobile_model_id = $mobilePurchase['mobile_model_id'];
		$mobileModel_query = $this->MobileModels->find('all',array(
				'fields' => array('model'),
				'conditions'=>array('MobileModels.id' => $mobile_model_id)
			)
		);
        $mobileModel_query = $mobileModel_query->hydrate(false);
        if(!empty($mobileModel_query)){
            $mobileModel = $mobileModel_query->first();
        }else{
            $mobileModel = array();
        }
		
		$brand_query = $this->Brands->find('all', array(
			'fields' => array( 'brand'),
			'conditions' => array('Brands.id' => $brand_id)
				)
		);
        $brand_query = $brand_query->hydrate(false);
        if(!empty($brand_query)){
            $brand = $brand_query->first();
        }else{
            $brand = array();
        }
		$this->set(compact('brand','mobileModel'));
		
		
		
		if($mobilePurchase['purchase_status'] == 1){
		  $this->paginate = [
                            'conditions'=>['MobileBlkTransferLogs.imei' => $imei],
                            'order' => ['MobileBlkTransferLogs.id desc'],
                            'limit' => 20
                          ];
		  $mobileTransferLogs_query = $this->paginate('MobileBlkTransferLogs');
		  
		  
		}else{
		  $this->paginate = [
                            'conditions'=>['MobileTransferLogs.imei' => $imei],
                            'order' => ['MobileTransferLogs.id desc'],
                            'limit' => 20
                          ];
		  $mobileTransferLogs_query = $this->paginate('MobileTransferLogs');    
		}
		
        if(!empty($mobileTransferLogs_query)){
            $mobileTransferLogs = $mobileTransferLogs_query->toArray();
        }else{
            $mobileTransferLogs = array();
        }
		//pr($mobileTransferLogs);
		$kioskIdArr = $userIdArr = array();
		$counter = 0;
		$customGrade = "";
		foreach($mobileTransferLogs as $key => $mobileTransferLog){
			if($counter++ == 0){
				$purchaseID = $mobileTransferLog->mobile_purchase_id;
				$purSt_query = $this->MobilePurchases->find('all',array(
															 'conditions' => array('id' => $purchaseID),
															 'fields' => array('purchase_status'),
															 'recursive' => -1,
															 'order' => 'MobilePurchases.id desc',
															 ));
                $purSt_query = $purSt_query->hydrate(false);
                if(!empty($purSt_query)){
                    $purSt = $purSt_query->first();
                }else{
                    $purSt = array();
                }
				$customGrade = $purSt['purchase_status'];
				
			}
			$kioskIdArr[$mobileTransferLog->kiosk_id] = $mobileTransferLog->kiosk_id;
			$userIdArr[$mobileTransferLog->user_id] = $mobileTransferLog->user_id;
		}
		  if(empty($kioskIdArr)){
			   $kioskIdArr = array(0 => null);
		  }
		  if(empty($userIdArr)){
			   $userIdArr = array(0 => null);
		  }
		$this->set(compact('customGrade'));
		$kioskName_query = $this->Kiosks->find('list',[
                                                    'conditions'=>['Kiosks.id IN'=>$kioskIdArr],
                                                    'keyField' => 'id',
                                                    'valueField' => 'name'
                                                ]
                                                    );
        $kioskName_query = $kioskName_query->hydrate(false);
        if(!empty($kioskName_query)){
            $kioskName = $kioskName_query->toArray();
        }else{
            $kioskName = array();
        }
		$userName_query = $this->Users->find('list',[
                                                'conditions'=>['Users.id IN'=>$userIdArr],
                                                'keyField' => 'id',
                                                'valueField' => 'username'
                                              ]
                                       );
        $userName_query = $userName_query->hydrate(false);
        if(!empty($userName_query)){
            $userName = $userName_query->toArray();
        }else{
            $userName = array();
        }
		$networks_query = $this->Networks->find('list',[
                                                    'keyField' => 'id',
                                                    'valueField' => 'name'
                                                 ]
                                          );
        $networks_query = $networks_query->hydrate(false);
        if(!empty($networks_query)){
            $networks = $networks_query->toArray();
        }else{
            $networks = array();
        }
		$type = array('0'=>'Unlocked','1'=>'Locked',''=>'--');
		$receiving_status = array('1'=>'Transient','0'=>'Received',''=>'--');
		$status = array('0'=>'Available','1'=>'Sold','2'=>'Refunded','3'=>'Reserved','4'=>'Sent for unlock','5'=>'Sent for repair');
		$networks[0] = '--';
		$networks[""] = '--';
		$userName[0] = "Admin";
		$kioskName[""] = "Warehouse";
		$kioskName[0] = "Warehouse";
		$this->set(compact('mobileTransferLogs','kioskName','userName','networks','type','receiving_status','status'));
	}
	
	public function getModels(){
		//capturing the mobile model id and brand ids from mobileunlockprice table with status 1 ie active
		$activeCombinations_query = $this->MobilePrices->find('all',array('conditions' => array('MobilePrices.status'=>1),
							      'fields' => array('mobile_model_id','brand_id'),
							      'group' => 'MobilePrices.mobile_model_id'
							      ));
		$activeCombinations_query = $activeCombinations_query->hydrate(false);
		if(!empty($activeCombinations_query)){
		  $activeCombinations = $activeCombinations_query->toArray();
		}else{
		  $activeCombinations = array();
		}
		$activeBrands = array();
		$activeModels = array();
		foreach($activeCombinations as $key => $activeCombination){
			$activeModels[$activeCombination['mobile_model_id']] = $activeCombination['mobile_model_id'];
		}
		$brandId = $this->request->query('id');
		//$this->request->onlyAllow('ajax');
		$mobileModels_query = $this->MobilePurchases->MobileModels->find('list',array(
																					  'keyField' => 'id',
																					  'valueField' => 'model',
									'order'=>'model asc',
								   'conditions'=>array(
									'MobileModels.brand_id'=>$brandId,
									'MobileModels.status'=>1,
									'MobileModels.id IN' => $activeModels)
								   )
						      );
		$mobileModels = $mobileModels_query->hydrate(false);
		if(!empty($mobileModels_query)){
		  $mobileModels = $mobileModels_query->toArray();
		}else{
		 $mobileModels = array(); 
		}
		
		//return json_encode($mobileModels);
		//echo json_encode($mobileModels);
		$this->set(compact('mobileModels')); // Pass $data to the view
		//$this->layout = false;
	}
	
	public function getPrice(){
		$selectedModel = $this->request->query['model'];
		$selectedBrand = $this->request->query['brand'];
		$selectedGrade = $this->request->query['grade'];
		$selectedType = $this->request->query['type'];
		$mobilePriceInfo_query = $this->MobilePrices->find('all',array('conditions'=>array('MobilePrices.brand_id'=>$selectedBrand,
									   'MobilePrices.mobile_model_id'=>$selectedModel,
									   'MobilePrices.locked'=>$selectedType,
									   'MobilePrices.grade'=>$selectedGrade)
									  ));
		$mobilePriceInfo_query = $mobilePriceInfo_query->hydrate(false);
		if(!empty($mobilePriceInfo_query)){
		  $mobilePriceInfo = $mobilePriceInfo_query->first();
		}else{
		  $mobilePriceInfo = array();
		}
		if(!empty($mobilePriceInfo)){
			$maximum_discount = $mobilePriceInfo['maximum_topup'];
			
		  $discountArr = [];
		   for($i = 0; $i <= 50; $i++){
			   if($i==0){
			       $discountArr[0] = "None";
			       continue;
			   }
			   $discountArr[$i] = "$i %";
		   }
        
		   $discountOptions = $discountArr;					
			
			$allowedDis = array();
			foreach($discountOptions as $dis => $disoptions){
				if($dis > $maximum_discount)break;
				$allowedDis[$dis] = $disoptions;
			}
			/*for($i = 0; $i <= $maximum_discount; $i++){
				if($i==0){$allowedDis[$i] = 'None';continue;}
				$allowedDis[$i] = $i;
			}*/
			$mobilePriceInfo['discountOptions'] = $allowedDis;
			$mobilePriceInfo['err'] = 0;
		}else{
			$mobilePriceInfo['err'] = 1;
		}
		
		echo json_encode($mobilePriceInfo);
		die();
	}
	
	public function searchMobileReference(){
	 $color = Configure::read('color');
		$imei = $searchKW = '';
		if(array_key_exists('reference_number',$this->request->query)){
			$searchKW = $this->request->query['reference_number'];
		}
		if(array_key_exists('imei',$this->request->query)){
			$imei = $this->request->query['imei'];
		}
		$kiosk_id = $this->request->Session()->read('kiosk_id');
		
		if(!empty($kiosk_id) && $kiosk_id != 10000){
		  $MobilePurchases = $this->MobilePurchases->find('all',array('conditions' => array(
								   'MobilePurchases.mobile_purchase_reference like'	=> "%$searchKW%",
											'MobilePurchases.imei like' => "%$imei%",
											'MobilePurchases.purchased_by_kiosk' => $kiosk_id,
									'NOT' => array('MobilePurchases.mobile_purchase_reference IS NULL')
									),'group' => array('MobilePurchases.mobile_purchase_reference',
										 'MobilePurchases.rand_num'),'limit' => ROWS_PER_PAGE,'order'=>'MobilePurchases.created desc'));
        $MobilePurchases
                          ->select('MobilePurchases.mobile_purchase_reference')
                          ->select('MobilePurchases.rand_num')
                          ->select('MobilePurchases.created')
						  ->select('MobilePurchases.color')
						  ->select('MobilePurchases.mobile_model_id')
                          ->select(['count' => $MobilePurchases->func()->count('MobilePurchases.mobile_purchase_reference')]);
		}else{
		  $MobilePurchases = $this->MobilePurchases->find('all',array('conditions' => array(
								   'MobilePurchases.mobile_purchase_reference like'	=> "%$searchKW%",
											'MobilePurchases.imei like' => "%$imei%",
									'NOT' => array('MobilePurchases.mobile_purchase_reference IS NULL')
									),'group' => array('MobilePurchases.mobile_purchase_reference',
										 'MobilePurchases.rand_num'),'limit' => ROWS_PER_PAGE,'order'=>'MobilePurchases.created desc'));
		  $MobilePurchases
							->select('MobilePurchases.mobile_purchase_reference')
							->select('MobilePurchases.rand_num')
							->select('MobilePurchases.created')
							->select('MobilePurchases.color')
							->select('MobilePurchases.mobile_model_id')
							->select(['count' => $MobilePurchases->func()->count('MobilePurchases.mobile_purchase_reference')]); 
		}
		 
        
		$this->paginate = ['limit' => ROWS_PER_PAGE];
		$referenceNumbers_query = $this->paginate($MobilePurchases);
		 if(!empty($referenceNumbers_query)){
            $referenceNumbers = $referenceNumbers_query->toArray();
        }else{
            $referenceNumbers = array();
        }
		
		$mobileModels_query = $this->MobileModels->find('list',array(
																					  'keyField' => 'id',
																					  'valueField' => 'model',
								   )
						      );
		$mobileModels = $mobileModels_query->hydrate(false);
		if(!empty($mobileModels_query)){
		  $mobileModels = $mobileModels_query->toArray();
		}else{
		 $mobileModels = array(); 
		}
		
		$hint = $this->ScreenHint->hint('mobile_purchases','reference_number_listing');
					if(!$hint){
						$hint = "";
					}
		$this->set(compact('hint','referenceNumbers','color','imei','mobileModels'));
		$this->render('reference_number_listing');
	}
	
	public function search(){
		$kiosk_id = $this->request->Session()->read('kiosk_id');
		$searchKW = $this->request->query['search_kw'];
		$id = $this->request->query['id'];
		if(!empty($id)){
		  $this->paginate = array(
					'conditions' => array(
								'MobilePurchases.id' => $id, 
								'MobilePurchases.status NOT IN' => 1 
								//'NOT IN'=>array('MobilePurchases.status'=>1)
					),
					'limit' => ROWS_PER_PAGE,
					'contain' => ['Kiosks','Brands', 'MobileModels'],
		);
		}else{
			   $this->paginate = array(
						 'conditions' => array(
									 'OR' => array(
											 'MobileModels.model like' => "%$searchKW%",
											 'MobilePurchases.imei like' => "%$searchKW%",
											 'Brands.brand like' => "%$searchKW%"
										 ),
									 'MobilePurchases.status NOT IN' => 1 
									 //'NOT IN'=>array('MobilePurchases.status'=>1)
						 ),
						 'limit' => ROWS_PER_PAGE,
						 'contain' => ['Kiosks','Brands', 'MobileModels'],
			 );  
		}
		
		
		if($kiosk_id){//for retailers
		  if(!empty($id)){
			   $this->paginate = array(
						'conditions' => array(
									'MobilePurchases.id' => $id, 
									'MobilePurchases.status NOT IN' => 1 ,
									//'NOT IN'=>array('MobilePurchases.status'=>1),
									'MobilePurchases.kiosk_id'=>$kiosk_id
						),
						'limit' => ROWS_PER_PAGE,
						 'contain' => ['Kiosks','Brands', 'MobileModels'],
			);
		  }else{
			   $this->paginate = array(
						'conditions' => array(
									'OR' => array(
											'MobileModels.model like' => "%$searchKW%",
											'MobilePurchases.imei like' => "%$searchKW%",
											'Brands.brand like' => "%$searchKW%"
										),
									'MobilePurchases.status NOT IN' => 1 ,
									//'NOT IN'=>array('MobilePurchases.status'=>1),
									'MobilePurchases.kiosk_id'=>$kiosk_id
						),
						'limit' => ROWS_PER_PAGE,
						 'contain' => ['Kiosks','Brands', 'MobileModels'],
			);
		  }
			
		}
		//pr($this->paginate);die;
		$kiosks_query = $this->Kiosks->find('list',array(
												   'keyField' => 'id',
												   'valueField' => 'name',
												   'order' => array('Kiosks.name asc')));
		$kiosks_query = $kiosks_query->hydrate(false);
		if(!empty($kiosks_query)){
		  $kiosks = $kiosks_query->toArray();
		}else{
		  $kiosks = array();
		}
		$users_query = $this->Users->find('list', array(
												  'keyField' => 'id',
												  'valueField' => 'username',
												  ));
		$users_query = $users_query->hydrate(false);
		if(!empty($users_query)){
		  $users = $users_query->toArray();
		}else{
		  $users = array();
		}
		$mobilePurchases_query = $this->paginate('MobilePurchases');
		$mobilePurchases = $mobilePurchases_query->toArray();
		$lockedUnlocked = array('1'=>'Locked','0'=>'Unlocked');
		$networks_query = $this->Networks->find('list',array(
													   'keyField' => 'id',
													   'valueField' => 'name',
													   ));
		$networks_query = $networks_query->hydrate(false);
		if(!empty($networks_query)){
			   $networks = $networks_query->toArray();
		}else{
			   $networks = array(); 
		}
		
		$modelIdsArr = array();
		foreach($mobilePurchases as $key=>$mobilePurchase){
			$modelIdsArr[$mobilePurchase->mobile_model_id]=$mobilePurchase->mobile_model_id;
			$data_query = $this->MobilePrices->find("all",array('fields' => array('brand_id', 'mobile_model_id','locked','sale_price'),
										'conditions'=>array(
													'MobilePrices.brand_id' => $mobilePurchase->brand_id,
										'MobilePrices.mobile_model_id'=>$mobilePurchase->mobile_model_id,														'MobilePrices.locked'=>$mobilePurchase->type,
											'MobilePrices.grade'=>$mobilePurchase->grade,
															)
										)
						 );
			$data_query = $data_query->hydrate(false);
			if(!empty($data_query)){
			   $data = $data_query->first();
			}else{
			   $data = array();
			}
			//pr($data);continue;
			if(!empty($data)){
				$salePrice[$mobilePurchase->id] = $data['sale_price'];
			}
		}
		if(empty($modelIdsArr)){
		  $modelIdsArr = array(0 => null);
		}
		$mobileModels_query = $this->MobileModels->find('list',array(
															   'keyField' => 'id',
															   'valueField' => 'model',
															   'order'=>'model asc',
								      'conditions'=>array('MobileModels.status' => 1,'MobileModels.id IN'=>$modelIdsArr)));
		$mobileModels_query = $mobileModels_query->hydrate(false);
		if(!empty($mobileModels_query)){
		  $mobileModels = $mobileModels_query->toArray();
		}else{
		 $mobileModels = array(); 
		}
		
		$this->set(compact('users','kiosks','mobilePurchases','mobileModels','lockedUnlocked','networks','salePrice'));
		//$this->layout = 'default';
		//$this->viewPath = 'MobilePurchases';
		$this->render('index');
	}
	
	public function kioskReceipt($id = null) {
		//echo "kiosk_receipt";
		$refundOptions = Configure::read('refund_status');
		if (!$this->MobilePurchases->exists($id)) {
			throw new NotFoundException(__('Invalid mobile purchase'));
		}
		$countryOptions = Configure::read('uk_non_uk');
		$options = array('conditions' => array('MobilePurchases.id' => $id));
		$res_query = $this->MobilePurchases->find('all', $options);
		$res_query = $res_query->hydrate(false);
		if(!empty($res_query)){
		  $res = $res_query->first();
		}else{
		  $res = array();
		}
		$this->set('mobilePurchase',$res);
		$options = array(
			'conditions' => array('MobilePurchases.id'=> $id)
		);
		$mobilePurchase_query = $this->MobilePurchases->find('all', $options);
		$mobilePurchase_query = $mobilePurchase_query->hydrate(false);
		if(!empty($mobilePurchase_query)){
		  $mobilePurchase = $mobilePurchase_query->toArray();
		}else{
		  $mobilePurchase = array();
		}
		
		$kiosk_id = $mobilePurchase[0]['kiosk_id'];
		if($kiosk_id == 0){
		  $kiosk_id = 10000;
		}
		$kiosk_data = $this->Kiosks->find("all",[
								   'conditions' => ['id' => $kiosk_id]
								   ])->toArray();
		
		$settingArr = $this->setting;
		$currency = $settingArr['currency_symbol'];
		$kiosk_query = $this->Kiosks->find('list');
		$kiosk_query = $kiosk_query->hydrate(false);
		if(!empty($kiosk_query)){
		  $kiosk = $kiosk_query->toArray();
		}else{
		  $kiosk = array();
		}
		$brands_query = $this->MobilePurchases->Brands->find('list', array(
			   'keyField' => 'id',
			   'valueField' => 'brand',
			   'order'=>'brand asc'
		));
		$brands_query = $brands_query->hydrate(false);
		if(!empty($brands_query)){
		  $brands = $brands_query->toArray();
		}else{
		  $brands = array();
		}
		$mobileModels_query = $this->MobileModels->find('list',array(
																	'keyField' => 'id',
																	'valueField' => 'model',
																	'order'=>'model asc'
																	));
		$mobileModels_query = $mobileModels_query->hydrate(false);
		if(!empty($mobileModels_query)){
		  $mobileModels = $mobileModels_query->toArray();
		}else{
		  $mobileModels = array();
		}
		$networks_query = $this->Networks->find('list',array(
															 'keyField' => 'id',
															'valueField' => 'name',
															 ));
		$networks_query = $networks_query->hydrate(false);
		if(!empty($networks_query)){
		  $networks = $networks_query->toArray();
		}else{
		  $networks = array();
		}
		$vat = $this->VAT;
		$this->set(compact('mobilePurchase','kiosk_data','mobileModels','networks','settingArr','brands'));
	}
	
	public function unreserve($purchaseId = null){
		//mobile purchase status 0 => available, 1 => sold , 2 => reserved, 3 => sent for unlock 4 => under repair
		$kioskId = $this->request->Session()->read('kiosk_id');
		$MobilePurchasesEntity = $this->MobilePurchases->get($purchaseId);
		
		$data_query = $this->MobilePurchases->find('all',array('conditions'=>array('MobilePurchases.id'=>$purchaseId)));
		$data_query = $data_query->hydrate(false);
		if(!empty($data_query)){
		  $data = $data_query->first();
		}else{
		  $data = array();
		}
		$data1 = array('status' => '0',
					  'new_kiosk_id' => NULL,
					  );
		$MobilePurchasesEntity = $this->MobilePurchases->patchEntity($MobilePurchasesEntity,$data1,['validate' => false]);
		
		if($this->MobilePurchases->save($MobilePurchasesEntity)){
			$mobileTransferLogData = array(
						'mobile_purchase_reference' => $data['mobile_purchase_reference'],
						'mobile_purchase_id' => $data['id'],
						'kiosk_id' => $kioskId,
						'network_id' => $data['network_id'],
						'grade' => $data['grade'],
						'type' => $data['type'],
						'receiving_status' => NULL,
						'imei' => $data['imei'],
						'user_id' => $this->request->Session()->read('Auth.User.id'),
						'status' => 0
						);
				
				$MobileTransferLogsEntity = $this->MobileTransferLogs->newEntity($mobileTransferLogData,['validate' => false]);
				$MobileTransferLogsEntity = $this->MobileTransferLogs->patchEntity($MobileTransferLogsEntity,$mobileTransferLogData,['validate' => false]);
				$this->MobileTransferLogs->save($MobileTransferLogsEntity);
				$this->Flash->success('Phone has been unreserved');
			return $this->redirect(array('action'=>'global_search'));
		}
	}
    
    public function editMobileReferenceList($reference = '', $randNum = ''){
		$activeCombinations_query = $this->MobilePrices->find('all',array(
																   'conditions' => array('MobilePrices.status' => 1),
																	'fields' => array('mobile_model_id','brand_id'),
																	'recursive' => -1,
																	'group' => 'MobilePrices.mobile_model_id'
																	));
        $activeCombinations_query = $activeCombinations_query->hydrate(false);
        if(!empty($activeCombinations_query)){
            $activeCombinations = $activeCombinations_query->toArray();
        }else{
            $activeCombinations = array();
        }
		//pr($activeCombinations);
		$activeModels = $activeBrands = array();
		foreach($activeCombinations as $key => $activeCombination){
			$activeModels[$activeCombination['mobile_model_id']] = $activeCombination['mobile_model_id'];
			$activeBrands[$activeCombination['brand_id']] = $activeCombination['brand_id'];
		}
		
		$referenceNumber = $reference;
		$iemis = array();
		$currency = $this->setting['currency_symbol'];
		$status = array( '0' => 'Available', '1' => 'sold' , '2' => 'Reserved');
		$received = array( '1' =>'Transient', '0' =>'Received'  );
		$type = array('1'=> 'Locked', '0' => 'Unlocked');
		$discountOptions = Configure::read('discount');
		$gradeType = Configure::read('grade_type');
		$colorOptions = Configure::read('color');
		$countryOptions = Configure::read('uk_non_uk');
		//assigning to view file
		$this->set(compact('status','colorOptions','discountOptions','countryOptions','gradeType','received','type','currency','iemis'));
		$imeiData_query = $this->MobilePurchases->find('all',
												array(
													  'conditions' => array(
																			'MobilePurchases.mobile_purchase_reference' => $reference,
																			'MobilePurchases.rand_num' => $randNum
																			)
												));
        $imeiData_query = $imeiData_query->hydrate(false);
        if(!empty($imeiData_query)){
            $imeiData = $imeiData_query->toArray();
        }else{
            $imeiData = array();
        }
		//pr($imeiData);//die;
		if($this->request->session()->read('Auth.User.group_id') == ADMINISTRATORS || $this->request->session()->read('Auth.User.group_id') == FRANCHISE_OWNER){$this->request->data['MobilePurchase']['kiosk_id'] = 10000;}
		
		if ($this->request->is('post')) {
			//pr($this->request->data);die;
			$isCustomGradeRef = false;
			$mobPur = $this->request->data['MobilePurchase'];
			if($mobPur['purchase_status'] == 1 && $mobPur['custom_grades'] == 1){
				$isCustomGradeRef = true;
			}
			$customGrades = $mobPur['custom_grades'];
			$mobilepurchases = $this->request->data;
            //pr($this->request->data);die;
			$iemis = $this->request->data['MobilePurchase']['imei'];
			$colors = $this->request->data['MobilePurchase']['color'];
			$purchases = $this->request->data['MobilePurchase']['purchase_cost'];
			$sellingPrice = $this->request->data['MobilePurchase']['selling_price']; //newly added
			$lowestSellingPrice = $this->request->data['MobilePurchase']['lowest_selling_price']; //newly added
			$customGrade = $this->request->data['MobilePurchase']['custom_grade']; //newly added
			//$customGrade = 1; implies phone is purchased from bulk screen with custom grade if 0 than with normal grade
			$purchaseStatus = $this->request->data['MobilePurchase']['purchase_status']; //newly added
			$type = $this->request->data['MobilePurchase']['type'];
			$grade = $this->request->data['MobilePurchase']['grade'];
			$mobilePurchaseReference = $this->request->data['MobilePurchase']['mobile_purchase_reference'];
			$network_id = $this->request->data['MobilePurchase']['network_id'];
			$brand_id = $this->request->data['MobilePurchase']['brand_id'];
            //pr($this->request->data);die;
			$mobile_model_id = $this->request->data['MobilePurchase']['mobile_model_id'];
			$description = $this->request->data['MobilePurchase']['description'];
			$brief_history = $this->request->data['MobilePurchase']['brief_history'];
			$color = $this->request->data['MobilePurchase']['color'];
			//pr($this->request->data['MobilePurchase']);die;
			$this->set(compact('iemis'));
			
			$errorArr = $successArr = array();
			if(empty($grade)){$errorArr[]= "Please choose the grade";}
			if($type==""){$errorArr[]= "Please choose type locked or unlocked";}
			if(empty($purchases)){$errorArr[] = "Please input cost price";}
			if(empty($brief_history)){$errorArr[] = "Please input the purchase history";}
			if(empty($description)){$errorArr[] = "Please input the description";}
			if($purchaseStatus == 1){
				//implies phone is purchased by admin by using bulk purchase function
				if($sellingPrice < $purchases){$errorArr[] = "Selling price can not be less than Cost Price";}
				if($lowestSellingPrice > $sellingPrice){$errorArr[] = "Lowest selling price can not be greater than selling price";}
				if(empty($sellingPrice)){$errorArr[] = "Lowest selling price can not be greater than selling price";}
				if(!empty($lowestSellingPrice) && $lowestSellingPrice < $purchases){
					$errorArr[] = "Lowest selling price can not be greater than selling price";
				}
			}
			
			$errStrn = '';
			if(count($errorArr) > 0){
				$errStrn = implode('<br/>',$errorArr);
				$this->Flash->error($errStrn,array('escape' => false));
				$purchaseDate = date('Y-m-d');
				$kiosks_query = $this->Kiosks->find('list');
                $kiosks_query = $kiosks_query->hydrate(false);
                if(!empty($kiosks_query)){
                    $kiosks = $kiosks_query->toArray();
                }else{
                    $kiosks = array();
                }
				$brands_query = $this->Brands->find('list',[
                                                                'keyField' => 'id',
                                                                'valueField' => 'brand',
												    'order'=>'brand asc',
                                                                'conditions' => ['Brands.status' => 1]
                                                            ]
                                                    );
                $brands_query = $brands_query->hydrate(false);
                if(!empty($brands_query)){
                    $brands = $brands_query->toArray();
                }else{
                    $brands = array();
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
				foreach($brands as $brandId => $brandName)break;
				if(array_key_exists('brand_id',$this->request->data['MobilePurchase'])){
					$brandId = $this->request->data['MobilePurchase']['brand_id'];
					$networkId = $this->request->data['MobilePurchase']['network_id'];
					$grade = $this->request->data['MobilePurchase']['grade'];
					$type = $this->request->data['MobilePurchase']['type'];
					$description = $this->request->data['MobilePurchase']['description'];
					$brief_history = $this->request->data['MobilePurchase']['brief_history'];
				}
				
				$mobileModels_query = $this->MobileModels->find('list',[
																	'keyField' => 'id',
                                                                    'valueField' => 'model',
													   'order'=>'model asc',
																	'conditions' => [
                                                                                        'MobileModels.status' => 1,
																						'MobileModels.brand_id' => $brandId,
																						//'MobileModels.id IN' => $activeModels
																						],
                                                                       ]
														);
                $mobileModels_query = $mobileModels_query->hydrate(false);
                if(!empty($mobileModels_query)){
                    $mobileModels = $mobileModels_query->toArray();
                }else{
                    $mobileModels = array();
                }
				$this->set(compact('kiosks', 'brands','reference_nos','mobileModels','purchaseDate','networks','imeiData','grade','networkId','type','description','brief_history', 'isCustomGradeRef', 'purchaseStatus','customGrades','color'));
				return;
				die;
			}
			
			//$checkIfExist = array();
			$imeiArr = array();
			foreach($this->request->data['MobilePurchase']['imei'] as $key =>  $iemi){
				if(!empty($iemi)){$imeiArr[] = $iemi;}
			}
						
			$doubleEntry = array();
			
			foreach($imeiArr as $ki=>$imei){
				if(strlen($imei)<15 && !empty($imei)){
					$doubleEntry[] = "Inserted imei: $imei should be 15 digits";//using the same array as below to minimize the code
				}
			}
			
			$checkDoubleEntry = array_count_values($imeiArr);
			if(!empty($checkDoubleEntry)){
				foreach($checkDoubleEntry as $imei=>$countImei){
					if(!empty($imei) && $countImei>1){
						$doubleEntry[] = "Imei:$imei has been entered $countImei times. Imei should be a unique number";
					}
				}
			}
			
			$doubleEntryStr = '';
			if(count($doubleEntry) > 0){
				$doubleEntryStr = implode('<br/>',$doubleEntry);
				$this->Flash->error($doubleEntryStr,array('escape' => false));
				$purchaseDate = date('Y-m-d');
				$kiosks_query = $this->Kiosks->find('list');
                $kiosks_query = $kiosks_query->hydrate(false);
                if(!empty($kiosks_query)){
                    $kiosks = $kiosks_query->toArray();
                }else{
                    $kiosks = array();
                }
				$brands_query = $this->Brands->find('list',[
														'keyField' => 'id',
														'valueField' => 'brand',
														'order'=>'brand asc',
														'conditions' => ['Brands.status' => 1],
														//'recursive' => -1,
                                                     ]
											 );
                $brands_query = $brands_query->hydrate(false);
                if(!empty($brands_query)){
                    $brands = $brands_query->toArray();
                }else{
                    $brands = array();
                }
				$networks_query = $this->Network->find('list',[
                                                            'keyField' => 'id',
                                                            'valueField' => 'name'
                                                        ]
                                                );
                $networks_query = $networks_query->hydrate(false);
                if(!empty($networks_query)){
                    $networks = $networks_query->toArray();
                }else{
                    $networks = array();
                }
				foreach($brands as $brandId => $brandName)break;
				if(array_key_exists('brand_id',$this->request->data['MobilePurchase'])){
					$brandId = $this->request->data['MobilePurchase']['brand_id'];
					$networkId = $this->request->data['MobilePurchase']['network_id'];
					$grade = $this->request->data['MobilePurchase']['grade'];
					$type = $this->request->data['MobilePurchase']['type'];
					$description = $this->request->data['MobilePurchase']['description'];
					$brief_history = $this->request->data['MobilePurchase']['brief_history'];
				}
				$mobileModels_query = $this->MobileModels->find('list',[
																	'keyField' => 'id',
                                                                    'valueField' => 'model',
													   'order'=>'model asc',
																	'conditions' => [
																						'MobileModels.status' => 1,
																						'MobileModels.brand_id' => $brandId,
																						//'MobileModels.id IN' => $activeModels
                                                                                    ]
																 ]
														);
                $mobileModels_query = $mobileModels_query->hydrate(false);
                if(!empty($mobileModels_query)){
                    $mobileModels = $mobileModels_query->toArray();
                }else{
                    $mobileModels = array();
                }
				$this->set(compact('kiosks', 'brands','reference_nos','mobileModels','purchaseDate','networks','imeiData','grade','networkId','type','description','brief_history'));
				return;
				die;
			}
				
			$checkReference_query = $this->MobilePurchases->find('all', array(
											'conditions' => array(
																  'MobilePurchases.mobile_purchase_reference' => $mobilePurchaseReference,
																  'NOT' => array('MobilePurchases.id' => $mobilePurchaseReference)
																  ))
														  );
			$checkReference_query = $checkReference_query->hydrate(false);
            if(!empty($checkReference_query)){
                $checkReference = $checkReference_query->first();
            }else{
                $checkReference = array();
            }
             $connection_config = ConnectionManager::config('default');
            $host = $connection_config['host'];
            $username = $connection_config['username'];
            $password = $connection_config['password'];
            $database = $connection_config['database'];
            
            //pr($connection_config);die;   
            $con=mysqli_connect($host,$username,$password,$database);
			foreach($this->request->data['MobilePurchase']['imei'] as $key =>  $iemi){
				if(!empty($iemi)){	
					if($this->request->data['MobilePurchase']['type'] == 0){
						$this->request->data['MobilePurchase']['network_id'] = NULL;
						$network_id = NULL;
					}
                    
					$description = mysqli_real_escape_string($con,$description);
					$brief_history = mysqli_real_escape_string($con,$brief_history);
					$grade = mysqli_real_escape_string($con,$grade);
					
					
					$description = str_replace("\\","",$description);
					$brief_history = str_replace("\\","",$brief_history);
					$grade = str_replace("\\","",$grade);
					$dataArr = array(
									 //'mobile_purchases.mobile_purchase_reference' => mysqli_real_escape_string($con,$mobilePurchaseReference),
									 'mobile_purchases.mobile_purchase_reference' => str_replace("\\","",$mobilePurchaseReference),
									 'mobile_purchases.brand_id' => $brand_id,
									 'mobile_purchases.mobile_model_id' => $mobile_model_id,
									 'mobile_purchases.type' => $type,
									 'mobile_purchases.network_id' => $network_id,
									 'mobile_purchases.color' => $colors,
									 'mobile_purchases.grade' => "$grade",
									 'mobile_purchases.cost_price' => $purchases,
									 'mobile_purchases.selling_price' => $sellingPrice, //newly added
									 'mobile_purchases.lowest_selling_price' => $lowestSellingPrice, //newly added
									 'mobile_purchases.static_selling_price' => $sellingPrice, //newly added
									 'mobile_purchases.description' => "$description",
									 'mobile_purchases.brief_history' => "$brief_history",
									);
												 
					if($this->MobilePurchases->updateAll($dataArr,array('mobile_purchases.imei' => $iemi))){
						$mobilePurchaseId = $this->request->data['MobilePurchase']['id'][$key];;
						$mobileTransferLogData = array(
								'mobile_purchase_reference' => $this->request->data['MobilePurchase']['mobile_purchase_reference'],
								'mobile_purchase_id' => $mobilePurchaseId,
								'kiosk_id' => $this->request->data['MobilePurchase']['kiosk_id'],
								'imei' => $iemi,
								'user_id' => $this->request->Session()->read('Auth.User.id'),
								'network_id' => $network_id,
								'grade' => $grade,
								'type' => $type
								);
						$new_emtity = $this->MobileTransferLogs->newEntity($mobileTransferLogData,array('validate' => false));
						$patch_entity = $this->MobileTransferLogs->patchEntity($new_emtity,$mobileTransferLogData,array('validate' => false));
                        //pr($patch_entity);die;
						$this->MobileTransferLogs->save($patch_entity);
						$successArr[] = "Imei with value: $iemi has been saved";
					}
				}
			}			
			
			$successStr = "";
			if(count($successArr) > 0){
				$successStr = implode('<br/>',$successArr);
				$this->Flash->success($successStr,array('escape' => false));
				return $this->redirect(array('action' => 'reference_number_listing'));
			}else{
				$this->Flash->error(__("Either nothing is changed or if have changed the mobile purchase could not be saved. Please, try again."));
			}
		}
		$purchaseDate = date('Y-m-d');
		$kiosks_query = $this->Kiosks->find('list');
        $kiosks_query = $kiosks_query->hydrate(false);
        if(!empty($kiosks_query)){
            $kiosks = $kiosks_query->toArray();
        }else{
            $kiosks = array();
        }
		$brands_query = $this->Brands->find('list',[
												'keyField' => 'id',
												'valueField' => 'brand',
												'order'=>'brand asc',
												'conditions' => ['Brands.status' => 1],
												//'recursive' => -1,
                                             ]
                                      );
        $brands_query = $brands_query->hydrate(false);
        if(!empty($brands_query)){
            $brands = $brands_query->toArray();
        }else{
            $brands = array();
        }
		$networks_query = $this->Networks->find('list',[
                                                    'keyField' => 'id',
                                                    'valueField' => 'name'
                                                 ]
                                          );
		$networks_query = $networks_query->hydrate(false);
        if(!empty($networks_query)){
            $networks = $networks_query->toArray();
        }else{
            $networks = array();
        }
		$color = $modelId = $type = $grade = $costPrice = $networkId = $description = '';
		$isCustomGradeRef = false;
        //pr($imeiData);die;
		if(array_key_exists(0,$imeiData)){
            
			//if atleast we have one record
			$mobPur = $imeiData[0];
			$brandId = $mobPur['brand_id'];
			$modelId = $mobPur['mobile_model_id'];
			$type = $mobPur['type'];
			$grade = $mobPur['grade'];
			$color = $mobPur['color'];
			$costPrice = $mobPur['cost_price'];
			$sellingPrice = $mobPur['selling_price']; //newly added
			$lowestSellingPrice = $mobPur['lowest_selling_price']; //newly added
			$purchaseStatus = $mobPur['purchase_status']; //newly added
			$customGrades = $mobPur['custom_grades']; //newly added
			$networkId = $mobPur['network_id'];
			$description = $mobPur['description'];
			$brief_history = $mobPur['brief_history'];
			if($mobPur['purchase_status'] == 1 && $mobPur['custom_grades'] == 1){
				$isCustomGradeRef = true;
			}
			$brief_history = $mobPur['brief_history'];
		}else{
			foreach($brands as $brandId => $brandName)break;
		}
		
		$mobileModels_query = $this->MobileModels->find('list',[
															'keyField' => 'id',
                                                            'valueField' => 'model',
												'order'=>'model asc',
															'conditions' => [
																				'MobileModels.status' => 1,
																				'MobileModels.brand_id' => $brandId,
																				//'MobileModels.id IN' => $activeModels
                                                                            ]
                                                         ]
                                                  );
        $mobileModels_query = $mobileModels_query->hydrate(false);
        if(!empty($mobileModels_query)){
            $mobileModels = $mobileModels_query->toArray();
        }else{
            $mobileModels = array();
        }
		$this->set(compact('kiosks', 'brands','reference_nos','mobileModels','purchaseDate','networks','imeiData','referenceNumber','brandId','modelId','costPrice','grade','type','networkId','description','brief_history','isCustomGradeRef', 'sellingPrice', 'lowestSellingPrice','purchaseStatus','customGrades','colorOptions','color'));
	}
	
	
	public function printLabel(){
		  $hidden_print_label_price = $this->request->data['selling_price_for_label'];
		  $print_label_price = $this->request->data['print_label_price'];
		  $id = $this->request->data['id'];
		  $MobilePurchases_data = $this->MobilePurchases->find("all",['conditions'=>['id' => $id]])->toArray();
		  
		  if(empty(trim($print_label_price))){
			   $print_label_price = $hidden_print_label_price;
		  }
		  $imei = "";
		  if(!empty($MobilePurchases_data)){
			   $imei = $MobilePurchases_data[0]->imei;
		  }
		  $barcode = $this->Barcode->generate_bar_code($imei,"png"); // html,png,svg,jpg
		  $setting_arr = $this->Settings->find("list",['keyField' => "attribute_name",
													   'valueField' => "attribute_value"
													   ])->toArray();
		  $lockedUnlocked = array('1'=>'Locked','0'=>'Unlocked');
		  
		  $mobileModels_query = $this->MobileModels->find('list',[
                                                            'keyField' => 'id',
                                                            'valueField' => 'model',
												'order'=>'model asc',
                                                         ]
                                                  );
		  $mobileModels_query = $mobileModels_query->hydrate(false);
		  if(!empty($mobileModels_query)){
			  $mobileModels = $mobileModels_query->toArray();
		  }else{
			  $mobileModels = array();
		  }
		  
		  $network_query = $this->Networks->find('list', [
                                                        'keyField' => 'id',
                                                        'valueField' => 'name',
                                                         
                                                          
                                                    ] 
                                            );
            if(!empty($network_query)){
                 $networks = $network_query->toArray();
            }else{
			   $networks = array();
			   } 
		$this->viewBuilder()->setLayout(false); 
		  $this->set(compact("MobilePurchases_data","print_label_price","imei","barcode","setting_arr","mobileModels","lockedUnlocked",'networks')); 
	}
	
	public function updateImei(){
		  $id = $this->request->query['id'];
		  $imei = $this->request->query['imei'];
		  if(empty($imei) || empty($id)){
			   $result = array("msg" => "either no imei or no id");
					echo json_encode($result);die;
		  }
		  if(!empty($id) && !empty($imei)){
			   if(strlen($imei) < 14){
					$result = array("msg" => "Imei must be atleast 14 digit long");
					echo json_encode($result);die;
				 }
				 $res = $this->MobilePurchases->find("all",[
													 'conditions' => [
														 'imei' => $imei,
													 ]
													 ])->toArray();
				 if(empty($res)){
					  $query = "UPDATE `mobile_purchases` SET `imei` = $imei WHERE `id` = $id";
					  $conn = ConnectionManager::get('default');
					  $stmt = $conn->execute($query);
					  $result = array("msg" => "imei Updated");
					  echo json_encode($result);die;
				 }else{
					  $result = array("msg" => "imei Allready exists");
					  echo json_encode($result);die;
				 }   
		  }
		  
	}
}
