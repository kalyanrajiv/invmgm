<?php 
namespace App\Controller;
use App\Controller\AppController;
use Cake\Routing\Router;
use Cake\ORM\TableRegistry;
use Cake\ORM\Entity;
use Cake\Core\Configure;
use Cake\Core\Configure\Engine\PhpConfig;
use Cake\Datasource\ConnectionManager;

    class MobilePricesController extends AppController{
         public $helpers = [
         'Paginator' => ['templates' => 'paginatortemplates']
         ];
        public function initialize()
        {
            parent::initialize();
            $this->loadModel('MobileRepairs');
            $this->loadModel('MobileUnlocks');
            $this->loadModel('MobileReSales');
            $this->loadModel('Customers');
            $this->loadModel('Kiosks');
            $this->loadModel('Receipts');
            $this->loadModel('Users');
            $this->loadModel('MobilePrices');
            $this->loadModel('MobileModels');
            $this->loadModel('Brands');
			$CURRENCY_TYPE = Configure::read('CURRENCY_TYPE');
			$this->set(compact('CURRENCY_TYPE'));
            
                        //Discount options
           $discountArr = array();
            for($i = 0; $i <= 50; $i++){
                if($i==0){
                    $discountArr[0] = "None";
                    continue;
                }
                $discountArr[$i] = "$i %";
            }
            $discountOptions = $discountArr;
            //pr($discountOptions);die;
            $gradeType = Configure::read('grade_type');
            $this->set(compact('discountOptions','gradeType'));
        }
        
        public function index(){
            
            $userName_query = $this->Users->find('list',[
                                                    'keyField' => 'id',
                                                    'valueField' => 'username'
                                                   ]);
            if(!empty($userName_query)){
                $userName = $userName_query->toArray();
            }
             $this->paginate = [
                                    'limit' => ROWS_PER_PAGE,
                                ];
            $mobilePrices_query = $this->paginate('MobilePrices');
            if(!empty($mobilePrices_query)){
                $mobilePrices = $mobilePrices_query->toArray();
            }
            //$this->set('mobilePrices', $this->Paginator->paginate());
            $brands_query = $this->MobilePrices->Brands->find('list',[
                                                                'keyField' => 'id',
                                                                'valueField' => 'brand'
                                                                ]);
            if(!empty($brands_query)){
                $brands = $brands_query->toArray();
            }
            $mobileModels_query = $this->MobilePrices->MobileModels->find('list',[
                                                                            'keyField' => 'id',
                                                                            'valueField' => 'model'
                                                                            ]);
            if(!empty($mobileModels_query)){
                $mobileModels = $mobileModels_query->toArray();
            }
            $lockedStatus = array('1'=>"Yes",'0'=>"No");
            $this->set(compact('brands','mobilePrices','mobileModels','userName','lockedStatus'));
        }
        
         private function generate_condition_array(){
            $searchKW = trim(strtolower($this->request->query['search_kw']));
            //pr($searchKW);
            $conditionArr = array();
            if(!empty($searchKW)){
                $conditionArr['OR']['Brands.brand like'] = strtolower("%$searchKW%");
                $conditionArr ['OR']['MobileModels.model like'] = strtolower("%$searchKW%");
            }
            return $conditionArr;
        }
            
        public function search($keyword = ""){
            $conditionArr = $this->generate_condition_array();
            $this->paginate  = [
                'conditions' => $conditionArr,
                'limit' => ROWS_PER_PAGE,
                'contain' => ['Brands','MobileModels']
            ];
            
            $mobilePrices_query = $this->paginate('MobilePrices');
            if(!empty($mobilePrices_query)){
                $mobilePrices = $mobilePrices_query->toArray();
            }
             $userName_query = $this->Users->find('list',[
                                                    'keyField' => 'id',
                                                    'valueField' => 'username'
                                                   ]);
            if(!empty($userName_query)){
                $userName = $userName_query->toArray();
            }
            $brands_query = $this->MobilePrices->Brands->find('list',[
                                                                'keyField' => 'id',
                                                                'valueField' => 'brand'
                                                                ]);
            if(!empty($brands_query)){
                $brands = $brands_query->toArray();
            }
            $mobileModels_query = $this->MobilePrices->MobileModels->find('list',[
                                                                            'keyField' => 'id',
                                                                            'valueField' => 'model'
                                                                            ]);
            if(!empty($mobileModels_query)){
                $mobileModels = $mobileModels_query->toArray();
            }
            $lockedStatus = array('1'=>"Yes",'0'=>"No");
            $this->set(compact('brands', 'mobileModels','userName','lockedStatus'));
            $this->set(compact('mobilePrices'));
            $this->render('index');
        }
        
        public function edit($id = null) {
            //echo ADMINISTRATORS;die;
            $mobile_price_table = TableRegistry::get("MobilePrices");
            
            $mobilePriceData_query = $this->MobilePrices->find('all',array('conditions'=>array('MobilePrices.mobile_model_id'=>$id),'recursive'=>-1));
            $mobilePriceData_query = $mobilePriceData_query->hydrate(false);
            if(!empty($mobilePriceData_query)){
                $mobilePriceData = $mobilePriceData_query->toArray();
            }
            if ($this->request->is(array('post', 'put'))) {
                $status = $this->request['data']['status'];
                $topup_status = $this->request['data']['topup_status'];
                $maximum_topup = $this->request['data']['maximum_topup'];
                $discount_status = $this->request['data']['discount_status'];
                $maximum_discount = $this->request['data']['maximum_discount'];
                $counter = 0;
                $error = array();
                foreach($this->request['data']['MobilePrice']['cost_price'] as $key=>$cost_price){
                    $sale_price = $this->request['data']['MobilePrice']['sale_price'][$key];
                    $mobilePriceId= $this->request['data']['MobilePrice']['id'][$key];
                    $mobileType = $this->request['data']['MobilePrice']['locked'][$key];
                    
                    if($cost_price>$sale_price){
                        $error[] = "Mobile price with mobile price id:$mobilePriceId could not be saved, cost price cannot be more than sale price.";
                    }
                    
                    if($mobileType==1){
                        $mobilePriceData = [
                                'id' => $mobilePriceId,
                                'discount_status' => $discount_status,
                                'maximum_discount' => $maximum_discount,
                                'topup_status' => $topup_status,
                                'maximum_topup' => $maximum_topup,
                                'cost_price' => $cost_price,
                                'sale_price' => $sale_price,
                                'status' => $status
                                        ];
                    }elseif($mobileType==0){
                        $mobilePriceData = [
                                'id' => $mobilePriceId,
                                'discount_status' => $discount_status,
                                'maximum_discount' => $maximum_discount,
                                'topup_status' => $topup_status,
                                'maximum_topup' => $maximum_topup,
                                'cost_price' => $cost_price,
                                'sale_price' => $sale_price,
                                'status' => $status
                                       ];
                    }
                    $mobile_price_id = $mobile_price_table->get($mobilePriceId);
                    $mobile_price_data = $this->MobilePrices->patchEntity($mobile_price_id, $mobilePriceData);
                    if($this->MobilePrices->save($mobile_price_data)){
                        $counter++; 
                    }
                }
                
                $errorStr = "";
                if(count($error)){
                    $errorStr = implode("<br/>",$error);
                }
                
                if($counter>1 && !empty($errorStr)){
                    $this->Flash->success("The mobile price could not be saved. $errorStr",['escape'=>false]);
                    return $this->redirect(array('action' => 'index'));
                }elseif($counter > 0){ //$counter==8
                    $this->Flash->success(__("The mobile price has been saved."));
                   return $this->redirect(['action' => 'index']);
                }elseif(!empty($errorStr)){
                    $this->Flash->error(__("$errorStr. Please, try again."));
                    return $this->redirect(array('action' => 'index'));
                }/*else{
                    $this->Session->setFlash(__("Data could not be saved. Please, try again."));
                    return $this->redirect(array('action' => 'index'));
                }*/
            }
            
            
            
            $brands_query = $this->MobilePrices->Brands->find('list',[
                                                                'keyField' => 'id',
                                                                'valueField' => 'brand'
                                                                ]);
            if(!empty($brands_query)){
                $brands = $brands_query->toArray();
            }
            
            $mobileModelName_query = $this->MobilePrices->MobileModels->find('list',[
                                                                            'keyField' => 'id',
                                                                            'valueField' => 'model'
                                                                            ]);
            if(!empty($mobileModelName_query)){
                $mobileModelName = $mobileModelName_query->toArray();
            }
            $type = array('1'=>'Locked','0'=>'Unlocked');
            $discountStatus = array('0'=>'Disabled','1'=>'Enabled');
            $this->set(compact('brands', 'mobileModelName','type'));
            $this->set(compact('mobilePriceData','discountStatus'));
        }
        
        
        public function editGrid($id = null) {
		$mobilePriceData_query = $this->MobilePrices->find('all',array('conditions'=>array('MobilePrices.mobile_model_id'=>$id)));
        $mobilePriceData_query = $mobilePriceData_query->hydrate(false);
        if(!empty($mobilePriceData_query)){
            $mobilePriceData = $mobilePriceData_query->toArray();
        }else{
            $mobilePriceData = array();
        }
		//if (!$this->MobilePrice->exists($id)) {
		//	throw new NotFoundException(__('Invalid mobile price'));
		//}
		
		if ($this->request->is(array('post', 'put'))) {
            //pr($this->request['data']);die;
			$status = $this->request['data']['status'];
            //echo $status;die;
			$topup_status = $this->request['data']['topup_status'];
			$maximum_topup = $this->request['data']['maximum_topup'];
			$discount_status = $this->request['data']['discount_status'];
			$maximum_discount = $this->request['data']['maximum_discount'];
			
			$counter = 0;
			$error = array();
			foreach($this->request['data']['MobilePrice']['cost_price'] as $key=>$cost_price){
				$sale_price = $this->request['data']['MobilePrice']['sale_price'][$key];
				$mobilePriceId= $this->request['data']['MobilePrice']['id'][$key];
				$mobileType = $this->request['data']['MobilePrice']['locked'][$key];
				
				if($cost_price>$sale_price){
					$error[] = "Mobile price with mobile price id:$mobilePriceId could not be saved, cost price cannot be more than sale price.";
				}
				
				if($mobileType==1){
					$mobilePriceData = array(
							'id' => $mobilePriceId,
							'discount_status' => $discount_status,
							'maximum_discount' => $maximum_discount,
							'topup_status' => $topup_status,
							'maximum_topup' => $maximum_topup,
							'cost_price' => $cost_price,
							'sale_price' => $sale_price,
							'status' => $status
							       );
				}elseif($mobileType==0){
					$mobilePriceData = array(
							'id' => $mobilePriceId,
							'discount_status' => $discount_status,
							'maximum_discount' => $maximum_discount,
							'topup_status' => $topup_status,
							'maximum_topup' => $maximum_topup,
							'cost_price' => $cost_price,
							'sale_price' => $sale_price,
							'status' => $status
							       );
				}
                //pr($mobilePriceData);die;
				$getId = $this->MobilePrices->get($mobilePriceId);
                $patchEntity = $this->MobilePrices->patchEntity($getId,$mobilePriceData);
                //pr($patchEntity);die;
				if($this->MobilePrices->save($patchEntity)){
                    //echo'hi';die;
					$counter++;
				}
			}
			
			$errorStr = "";
			if(count($error)){
				$errorStr = implode("<br/>",$error);
			}
			
			if($counter>1 && !empty($errorStr)){
				$this->Flash->success("The mobile price could not be saved. $errorStr",['escape'=>false]);
				return $this->redirect(array('action' => 'index'));
			}elseif($counter > 0){//$counter==8
				$this->Flash->success(__("The mobile price has been saved."));
				return $this->redirect(array('action' => 'index'));
			}elseif(!empty($errorStr)){
				$this->Flash->error(__("$errorStr. Please, try again."));
				return $this->redirect(array('action' => 'index'));
			}
		}
		
		
		
		$brands_query = $this->MobilePrices->Brands->find('list',[
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
		$mobileModelName_query = $this->MobilePrices->MobileModels->find('list',[
                                                                           'conditions'=>['MobileModels.id'=>$id],
                                                                           'keyField' => 'id',
                                                                           'valueField' => 'model'
                                                                          ]
                                                                   );
        $mobileModelName_query = $mobileModelName_query->hydrate(false);
        if(!empty($mobileModelName_query)){
            $mobileModelName = $mobileModelName_query->toArray();
        }else{
            $mobileModelName = array();
        }
		$type = array('1'=>'Locked','0'=>'Unlocked');
		$discountStatus = array('0'=>'Disabled','1'=>'Enabled');
		$this->set(compact('brands', 'mobileModelName','type'));
		$this->set(compact('mobilePriceData','discountStatus'));
	}
	
	public function export(){
       // pr($this->request->query);die;
		$conditionArr = array();
		if(array_key_exists('search_kw',$this->request->query)){
			$conditionArr = $this->generate_condition_array();
		}
       // pr($conditionArr);die;
		if(count($conditionArr)>=1){
			//$count_query = $this->MobilePrices->find('all');
			// $count = $count_query->count();
			  $mobilePrices_query = $this->MobilePrices->find('all',[
                                                                    'conditions' => $conditionArr,
                                                                  //  'limit' => $count,
                                                                    'contain'=> ['Brands','MobileModels']
                                                                    ]
                                                             );
             $mobilePrices_query =  $mobilePrices_query->hydrate(false);
            if(!empty($mobilePrices_query)){
                $mobilePrices = $mobilePrices_query->toArray();
            }else{
              $mobilePrices = array();
              
            }
           // pr($mobilePrices);die;
		}else{
			$mobilePrices_query = $this->MobilePrices->find('all');
            $mobilePrices_query =  $mobilePrices_query->hydrate(false);
            if(!empty($mobilePrices_query)){
                $mobilePrices = $mobilePrices_query->toArray();
            }else{
              $mobilePrices = array();
              
            }
          
		}
        // pr($mobilePrices);die;
		//$mobilePrices_query = $mobilePrices_query->hydrate(false);
		//if(!empty($mobilePrices_query)){
		//  $mobilePrices = $mobilePrices_query->toArray();
		//}else{
		//  $mobilePrices = array();
		//}
		//pr( $mobilePrices);die;
		$brands_query = $this->Brands->find('list', array(
										'keyField' => 'id',
										'valueField' => 'brand',
									));
		$brands_query = $brands_query->hydrate(false);
		if(!empty($brands_query)){
		  $brands = $brands_query->toArray();
		}else{
		  $brands = array();
		}
		//pr($brands);
		$mobileModels_query = $this->MobileModels->find('list',array(
										'keyField' => 'id',
										'valueField' => 'model',
										));
		$mobileModels_query = $mobileModels_query->hydrate(false);
		if(!empty($mobileModels_query)){
		  $mobileModels = $mobileModels_query->toArray();
		}else{
		  $mobileModels = array();
		}
      //  pr($mobileModels);die;
		$tmpMobilePrices = array();
		foreach($mobilePrices as $key => $mobilePrice){
			$brandID = $mobilePrice['brand_id'];
			$mobilePrice['brand_id'] = $brands[$brandID];
			$ModelID = $mobilePrice['mobile_model_id'];
            
            $mobilePrice['mobile_model_id'] = $mobileModels[$ModelID];
    		 
            unset($mobilePrice['mobile_model']);
            unset($mobilePrice['brand']);
			 $created = $mobilePrice['created'];
			   if(!empty($created)){
					 $created = date("d-m-y h:i a",strtotime($created));
					 $mobilePrice['created'] = $created;
			   }
			   $modified = $mobilePrice['modified'];
			   if(!empty($modified)){
					$modified = date("d-m-y h:i a",strtotime($modified));
					$mobilePrice['modified'] = $modified;
			   }
			$tmpMobilePrices[] = $mobilePrice;
		}
      // pr($tmpMobilePrices);die;
       $this->outputCsv('MobilePrice_'.time().".csv" ,$tmpMobilePrices);
	//	$this->outputCsv('MobilePrice_'.time().".csv" ,$tmpMobilePrices);
		$this->autoRender = false;
	}
    
    public function add() {
		
		$gradeType = Configure::read('grade_type');
		$brand_id = 0;
		$user_id = $this->request->Session()->read('Auth.User.id');
		if($this->request->is('post') && $this->request['data']['hiddenController'] == 1){
			//echo'hi';die;
            //pr($this->request);die;
			$brand_id = $this->request['data']['MobilePrice']['brand_id'];
		}elseif ($this->request->is('post')) {
			//echo'bye';die;
            //pr($this->request);die;
			$brandId = $this->request['data']['MobilePrice']['brand_id'];
			$counter = 0;
			$error = array();
			//pr($this->request['data']);die;
			
				$discount_status = $this->request['data']['MobilePrice']['discount_status'];
				$maximum_discount = $this->request['data']['MobilePrice']['maximum_discount'];
				$topup_status = $this->request['data']['MobilePrice']['topup_status'];
				$maximum_topup = $this->request['data']['MobilePrice']['maximum_topup'];
				
			foreach($this->request['data']['MobilePrice']['locked_purchase_price'] as $ki => $lockedPurchasePrice){
				$locked_mobile_model_id = $this->request['data']['MobilePrice']['locked_mobile_model_id'][$ki];
				$unlocked_mobile_model_id = $this->request['data']['MobilePrice']['unlocked_mobile_model_id'][$ki];
				$gradeLocked = $this->request['data']['MobilePrice']['grade_locked'][$ki];
				$gradeUnlocked = $this->request['data']['MobilePrice']['grade_unlocked'][$ki];
				$lockedSellingPrice = $this->request['data']['MobilePrice']['locked_selling_price'][$ki];;
				$unlockedPurchasePrice = $this->request['data']['MobilePrice']['unlocked_purchase_price'][$ki];;
				$unlockedSellingPrice = $this->request['data']['MobilePrice']['unlocked_selling_price'][$ki];
				
				if(!(int)$lockedPurchasePrice ||
				   !(int)$lockedSellingPrice ||
				   !(int)$unlockedPurchasePrice ||
				   !(int)$unlockedSellingPrice
				   ){
					$error[] = "Input value must be numeric";
				}
				if((int)($lockedPurchasePrice) || (int)($lockedSellingPrice) ||
				   (int)($unlockedPurchasePrice) || (int)($unlockedSellingPrice)
				   ){
					if($lockedPurchasePrice>$unlockedPurchasePrice){
						$error[] = "Purchase price for locked mobile cannot be more than unlocked mobile";
					break;
					}
					
					if($lockedPurchasePrice>$lockedSellingPrice){
						$error[] = "Purchase price for locked mobile cannot be more than selling price";
					break;
					}
					
					if($unlockedPurchasePrice>$unlockedSellingPrice){
						$error[] = "Purchase price for unlocked mobile cannot be more than selling price";
					break;
					}
					
					if(empty($lockedPurchasePrice)){
					$error[] = "Please input the purchase price for locked mobile";
					break;
					}
					
					if(empty($unlockedPurchasePrice)){
						$error[] = "Please input the selling price for locked mobile";
						break;
					}
					
					if(empty($lockedPurchasePrice)){
					$error[] = "Please input the purchase price for unlocked mobile";
					break;
					}
					
					if(empty($unlockedSellingPrice)){
						$error[] = "Please input the selling price for unlocked mobile";
						break;
					}
				
				
					$lockeddata = array(
						'user_id' => $user_id,
						'brand_id' => $brandId,
						'mobile_model_id' => $locked_mobile_model_id,
						'locked' => 1,
						'discount_status' => $discount_status,
						'maximum_discount' => $maximum_discount,
						'topup_status' => $topup_status,
						'maximum_topup' => $maximum_topup,
						'grade' => $gradeLocked,
						'cost_price' => $lockedPurchasePrice,
						'sale_price' => $lockedSellingPrice					
						      );
					
					$unlockeddata[] = array(
						'user_id' => $user_id,
						'brand_id' => $brandId,
						'mobile_model_id' => $unlocked_mobile_model_id,
						'locked' => 0,
						'discount_status' => $discount_status,
						'maximum_discount' => $maximum_discount,
						'topup_status' => $topup_status,
						'maximum_topup' => $maximum_topup,
						'grade' => $gradeUnlocked,
						'cost_price' => $unlockedPurchasePrice,
						'sale_price' => $unlockedSellingPrice					
						      );
					
					//pr($data);
					if(count($error == 0)){
                        //pr($lockeddata);die;
						try{
						 
						 $newEntity = $this->MobilePrices->newEntity($lockeddata,['validate' => false]);
						 $patchEntity = $this->MobilePrices->patchEntity($newEntity,$lockeddata,['validate' => false]);
							if($this->MobilePrices->save($patchEntity)){
								$counter++;
							}else{
                                debug($patchEntity);die;
                            }
						}catch(\PDOException $e){
							$error[] = "<br/>Record with this Brand:{$brandId}, Model:{$locked_mobile_model_id} and problem type:{$gradeType[$gradeLocked]} already exist";
						}
					}else{
						$error[] = "Record with Brand:{$brandId}, Model:{$locked_mobile_model_id} and problem type:{$gradeType[$gradeLocked]} could not be saved";
					}
				}
				
				
			}
			
			if($counter>0 && count($error == 0)){
				$count = 0;
				foreach($unlockeddata as $key=>$unlockInfo){
					try{
						 $new_entity = $this->MobilePrices->newEntity($unlockInfo,['validate' => false]);
						 $patch_entity = $this->MobilePrices->patchEntity($new_entity,$unlockInfo,['validate' => false]);
						if($this->MobilePrices->save($patch_entity)){
							$count++;
						}
					}catch(Exception $e){
						$error[] = "<br/>Record with this Brand:{$brandId}, Model:{$unlocked_mobile_model_id} and problem type:{$gradeType[$gradeUnlocked]} already exist";
					}
				}	
			}
				
			//pr($error);
			$errorStr = "";
			if(count($error)){
				$errorStr = implode("</br>",$error);	
			}
			//pr($errorStr);
			if((int)$counter){				
				$this->Flash->success(__($counter." record(s) for mobile price (locked) have been saved.<br/>$count record(s) for mobile price (unlocked) have been saved.{$errorStr}"),['escape' => false]);
				return $this->redirect(array('action' => 'index'));
			}else{
				$this->Flash->error(__("The mobile price could not be saved. {$errorStr}"),['escape' => false]);
			}
		}
		//die;
		$brands_query = $this->MobilePrices->Brands->find('list',[
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
		
		if(!$brand_id && !$this->request->is('post')){
			foreach($brands as $brand_id => $brand){break;}
		}else{
			if($this->request->is('post')){
				$brand_id = $this->request['data']['MobilePrice']['brand_id'];
			}
		}
		
		$mobileModels_query = $this->MobilePrices->MobileModels->find('list',[
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
		$this->set(compact('brands', 'mobileModels'));
	}
	
}
    
   
?>