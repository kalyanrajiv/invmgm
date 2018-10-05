<?php
namespace App\Controller;
use Cake\Core\Configure;
use Cake\Core\Configure\Engine\PhpConfig;
use App\Controller\AppController;
use Cake\I18n\Time;
use Cake\Datasource\ConnectionManager;
 
/**
 * MobileRepairPrices Controller
 *
 * @property \App\Model\Table\MobileRepairPricesTable $MobileRepairPrices
 */
class MobileRepairPricesController extends AppController{
 
      public $helpers = [
         'Paginator' => ['templates' => 'paginatortemplates']
         ];
        public function initialize()
        {
            parent::initialize();
            $this->loadModel('MobileModels');
            $this->loadModel('Brands');
            $this->loadModel('ProblemTypes');
            $this->loadComponent('Pusher');
            $activeOptions = Configure::read('active');
            $CURRENCY_TYPE = Configure::read('CURRENCY_TYPE');
         $this->set(compact('activeOptions','CURRENCY_TYPE' ));
        }
        
    public function index()
    {
        $active = Configure::read('active');
        $CURRENCY_TYPE = Configure::read('CURRENCY_TYPE');
        $this->set(compact('active','CURRENCY_TYPE' ));
        $this->paginate = [
            'contain' => ['Brands', 'MobileModels']
        ];
        $mobileRepairPrices = $this->paginate($this->MobileRepairPrices);
         $brands_query = $this->Brands->find('list', [
                                                        'keyField' => 'id',
                                                        'valueField' => 'brand',
                                                        'conditions' =>['Brands.status' => 1],
                                                        'order'=>['Brands.brand asc']
                                                    ]
                                            );
        if(!empty($brands_query)){
             $brands = $brands_query->toArray();
        }
         $model_query = $this->MobileModels->find('list', [
                                                        'keyField' => 'id',
                                                        'valueField' => 'model',
                                                         'conditions' =>['MobileModels.status' => 1],
                                                          'order'=>['MobileModels.model asc']
                                                    ] 
                                            );
        if(!empty($model_query)){
             $modelname = $model_query->toArray();
        }
         $problem_query = $this->ProblemTypes->find('list', [
                                                        'keyField' => 'id',
                                                        'valueField' => 'problem_type',
                                                          'conditions' =>['ProblemTypes.status' => 1],
                                                          'order'=>['ProblemTypes.problem_type asc']
                                                    ] 
                                                    
                                            );
        if(!empty($problem_query)){
             $problemtype = $problem_query->toArray();
        }
        $this->set(compact('mobileRepairPrices','brands','modelname','problemtype','active'));
        $this->set('_serialize', ['mobileRepairPrices']);
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
    public function search($keyword = ""){
        
		$conditionArr = $this->generate_condition_array();
		$this->paginate = [
							'conditions' => $conditionArr,
							'limit' => 50,
                            'contain' => ['Brands','MobileModels']
                          ];
		
		
		$mobileRepairPrices = $this->paginate('MobileRepairPrices');
         $CURRENCY_TYPE = Configure::read('CURRENCY_TYPE');
        
        $active = Configure::read('active');
		 $brands_query = $this->Brands->find('list', [
                                                        'keyField' => 'id',
                                                        'valueField' => 'brand',
                                                        'conditions' =>['Brands.status' => 1],
                                                        'order'=>['Brands.brand asc']
                                                    ]
                                            );
        if(!empty($brands_query)){
             $brands = $brands_query->toArray();
        }
        $model_query = $this->MobileModels->find('list', [
                                                        'keyField' => 'id',
                                                        'valueField' => 'model',
                                                         'conditions' =>['MobileModels.status' => 1],
                                                          'order'=>['MobileModels.model asc']
                                                    ] 
                                            );
        if(!empty($model_query)){
             $modelname = $model_query->toArray();
        }
          $problem_query = $this->ProblemTypes->find('list', [
                                                        'keyField' => 'id',
                                                        'valueField' => 'problem_type',
                                                          'conditions' =>['ProblemTypes.status' => 1],
                                                          'order'=>['ProblemTypes.problem_type asc']
                                                    ] 
                                                    
                                            );
        if(!empty($problem_query)){
             $problemtype = $problem_query->toArray();
        }
        $this->set(compact('active','CURRENCY_TYPE' ));
        $this->set(compact('brands','modelname','problemtype'));
		$this->set(compact('mobileRepairPrices','statusOptions'));
		//$this->layout = 'default';
		$this->render('index');
	}
     
     public function export1(){
		 $problem_query = $this->ProblemTypes->find('list', [
                                                        'keyField' => 'id',
                                                        'valueField' => 'problem_type',
                                                          'conditions' =>['ProblemTypes.status' => 1],
                                                          'order'=>['ProblemTypes.problem_type asc']
                                                    ] 
                                                    
                                            );
        if(!empty($problem_query)){
             $problemtype = $problem_query->toArray();
        } 
        $conditionArr = array();
        if(array_key_exists('search_kw',$this->request->query)){
           $conditionArr = $this->generate_condition_array();
       }
       
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
		
		if(count($conditionArr)>=1){
			$query = $this->MobileRepairPrices->find('all',
                                                     [ 
                                                           'conditions' =>  $conditionArr,
                                                          //  'limit' =>50,
                                                           'contain' => ['Brands','MobileModels'],
                                                          // 'recursive' => -1
                                                     ]
                                                     );
            $mobileRepairPrices_query = $query->hydrate(false);
             if(!empty($mobileRepairPrices_query)){
                $mobileRepairPrices = $mobileRepairPrices_query->toArray();
            }
            foreach($mobileRepairPrices as $key => $mobileRepairPrice){
                
                $id = $mobileRepairPrice['id'];
                //  $mobileRepairPrice_1['id']  = $id;
                $brandID = $mobileRepairPrice['brand']['brand'] ; 
                $mobileRepairPrice_1['brand_id']  = $brandID;
                $ModelID = $mobileRepairPrice['mobile_model']['model'] ;
                $mobileRepairPrice_1['mobile_model_id'] = $ModelID;
                $problemId = $mobileRepairPrice['problem_type'] ;
                $mobileRepairPrice_1['problem_type']  = $problemtype[$problemId];
               // $mobileRepairPrice_1['problem']  = $mobileRepairPrice['problem'];
                $mobileRepairPrice_1['repair_cost']  = $mobileRepairPrice['repair_cost'];
                $mobileRepairPrice_1['repair_price']  = $mobileRepairPrice['repair_price'];
                $mobileRepairPrice_1['repair_days']  = $mobileRepairPrice['repair_days'];
                $mobileRepairPrice_1['status']  = $mobileRepairPrice['status'];
                //$created = $mobileRepairPrice['created'] ;
                //$mobileRepairPrice_1['created']  = $created ;
                //$mobileRepairPrice_1['modified']   = $mobileRepairPrice['modified'] ;
                $tmpmobileRepairPrice_1[] = $mobileRepairPrice_1 ;
                  
                 
            }
        //  pr($tmpmobileRepairPrice_1);die;
           
            $this->outputCsv('MobileRepairPrices_'.time().".csv" ,$tmpmobileRepairPrice_1);
        }else{
            $query = $this->MobileRepairPrices->find('all');
            $mobileRepairPrices_query = $query->hydrate(false);
             if(!empty($mobileRepairPrices_query)){
                $mobileRepairPrices = $mobileRepairPrices_query->toArray();
            }
            foreach($mobileRepairPrices as $key => $mobileRepairPrice){
                $brandID = $mobileRepairPrice['brand_id'] ; 
                $mobileRepairPrice['brand_id']  = $brands[$brandID];
                $ModelID = $mobileRepairPrice['mobile_model_id'] ;
                $mobileRepairPrice['mobile_model_id'] =  $mobileModels[$ModelID];
                $problemId = $mobileRepairPrice['problem_type'] ;
                $mobileRepairPrice['problem_type']  = $problemtype[$problemId];
               // pr($mobileRepairPrice);die;
			    $created = $mobileRepairPrice['created'];
			   if(!empty($created)){
					 $created = date("d-m-y h:i a",strtotime($created));
					 $mobileRepairPrice['created'] = $created;
			   }
			   $modified = $mobileRepairPrice['modified'];
			   if(!empty($modified)){
					$modified = date("d-m-y h:i a",strtotime($modified));
					$mobileRepairPrice['modified'] = $modified;
			   }
                $tmpmobileRepairPrice[] = $mobileRepairPrice ;
            }
            $this->outputCsv('MobileRepairPrices_'.time().".csv" ,$tmpmobileRepairPrice);
            
           
		}
		$this->autoRender = false;
	}
	
	public function export(){
		$problemType = Configure::read('problem_type');
		$this->set(compact('problemType'));
		 $conditionArr = array();
		 if(array_key_exists('search_kw',$this->request->query)){
			$conditionArr = $this->generate_condition_array();
		}
		if(count($conditionArr)>=1){
			$count_query = $this->MobileRepairPrices->find('all');
			$count = $count_query->count();
			//$this->Paginator->settings =array(
			//				'conditions' => $conditionArr,
			//				'limit' => $count
			//				);
			$mobileRepairPrices_query = $this->MobileRepairPrices->find('all',array('conditions' => $conditionArr,
                                                                                    'contain' => array('Brands','MobileModels'),
																					'limit' => $count));
			//$mobileRepairPrices = $this->Paginator->paginate('MobileRepairPrice');
		}else{
			$mobileRepairPrices_query = $this->MobileRepairPrices->find('all',array('recursive' => -1));
		}
		$mobileRepairPrices_query = $mobileRepairPrices_query->hydrate(false);
		if(!empty($mobileRepairPrices_query)){
			$mobileRepairPrices = $mobileRepairPrices_query->toArray();
		}else{
			$mobileRepairPrices = array();
		}
		//pr($mobileRepairPrices);
		
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
		$tmpmobileRepairPrice = array();
		foreach($mobileRepairPrices as $key => $mobileRepairPrice){
			$brandID = $mobileRepairPrice['brand_id'];
			$mobileRepairPrice['brand_id'] = $brands[$brandID];
			$ModelID = $mobileRepairPrice['mobile_model_id'];
			if(array_key_exists($ModelID,$mobileModels)){
				$mobileRepairPrice['mobile_model_id'] = $mobileModels[$ModelID];  
			}else{
				  continue;
				  //$mobileRepairPrice['mobile_model_id'] = $mobileModels[$ModelID];
			}
			
			$problemId = $mobileRepairPrice['problem_type'];
			if(array_key_exists($problemId,$problemType)){
				$mobileRepairPrice['problem_type'] = $problemType[$problemId];  
			}else{
				  continue;
			}
			 $created = $mobileRepairPrice['created'];
			   if(!empty($created)){
					 $created = date("d-m-y h:i a",strtotime($created));
					 $mobileRepairPrice['created'] = $created;
			   }
			   $modified = $mobileRepairPrice['modified'];
			   if(!empty($modified)){
					$modified = date("d-m-y h:i a",strtotime($modified));
					$mobileRepairPrice['modified'] = $modified;
			   }
			//pr($mobileRepairPrice['MobileRepairPrice']);
			//die;
		 $tmpmobileRepairPrice[] = $mobileRepairPrice;
		}
		$this->outputCsv('MobileRepairPrice_'.time().".csv" ,$tmpmobileRepairPrice);
		$this->autoRender = false;
	}
	
	
    public function view($id = null)
    {
        $CURRENCY_TYPE = Configure::read('CURRENCY_TYPE');
        $active = Configure::read('active');
        $mobileRepairPrice = $this->MobileRepairPrices->get($id, [
            'contain' => ['Brands', 'MobileModels']
        ]);
         $brands_query = $this->Brands->find('list', [
                                                        'keyField' => 'id',
                                                        'valueField' => 'brand',
                                                        'conditions' =>['Brands.status' => 1],
                                                        'order'=>['Brands.brand asc']
                                                    ]
                                            );
        if(!empty($brands_query)){
             $brands = $brands_query->toArray();
        }
         $model_query = $this->MobileModels->find('list', [
                                                        'keyField' => 'id',
                                                        'valueField' => 'model',
                                                         'conditions' =>['MobileModels.status' => 1],
                                                          'order'=>['MobileModels.model asc']
                                                    ] 
                                            );
        if(!empty($model_query)){
             $modelname = $model_query->toArray();
        }
         $problem_query = $this->ProblemTypes->find('list', [
                                                        'keyField' => 'id',
                                                        'valueField' => 'problem_type',
                                                          'conditions' =>['ProblemTypes.status' => 1],
                                                          'order'=>['ProblemTypes.problem_type asc']
                                                    ] 
                                                    
                                            );
        if(!empty($problem_query)){
             $problemtype = $problem_query->toArray();
        }
        $this->set(compact('brands','modelname','problemtype','active','CURRENCY_TYPE'));
        $this->set('mobileRepairPrice', $mobileRepairPrice);
        $this->set('_serialize', ['mobileRepairPrice']);
    }
 
    public function add()
    {
       $problem_query = $this->ProblemTypes->find('list', [
                                                        'keyField' => 'id',
                                                        'valueField' => 'problem_type',
                                                          'conditions' =>['ProblemTypes.status' => 1],
                                                          'order'=>['ProblemTypes.problem_type asc']
                                                    ] 
                                                    
                                            );
        if(!empty($problem_query)){
             $problemType = $problem_query->toArray();
        }
       
        $brand_id = 0;
        $mobileRepairPrice = $this->MobileRepairPrices->newEntity();
        if($this->request->is('post') && $this->request['data']['hiddenController'] == 1){
			//onchange
            $brand_id = $this->request['data']['brand_id']; 
		}elseif($this->request->is('post')) {
           $counter = 0;
			$error = array();
           // pr($this->request['data']);
           $data = $successfullySaved = array();
           $counter = 0;
            foreach($this->request['data']['MobileRepairPrices']['mobile_model_id'] as $key => $mobileModel1){
				$brandId = $this->request['data']['brand_id'];
                $mobileRepairPrices = $this->request['data'];
                $mobileModel = $mobileRepairPrices['MobileRepairPrices']['mobile_model_id'][$key];
                $problemTypeVal = $mobileRepairPrices['MobileRepairPrices']['problem_type'][$key];
				$problem = $mobileRepairPrices['MobileRepairPrices']['problem'][$key];
				$repairPrice = $mobileRepairPrices['MobileRepairPrices']['repair_price'][$key];
				$repairDays = $mobileRepairPrices['MobileRepairPrices']['repair_days'][$key];
				$repairCost = $mobileRepairPrices['MobileRepairPrices']['repair_cost'][$key];
                
                if(!empty($repairCost) || !empty($repairPrice) || !empty($repairDays) ){
                    if(empty($mobileModel)){
						$error[]="Mobile model must be selected";
						break;
					}
					if(empty($repairCost)){
						$error[]="Please input the repair cost";					
						break;
					}
					if(empty($repairPrice)){
						$error[]="Please input the repair price";					
						break;
					}
					if(empty($repairDays)){
						$error[]="Please input the repair days";					
						break;
					}
                
                   // if(!empty($mobileModel1)){
                        //if(!in_array($mobileRepairPrices,$data)){
                            $data = array('brand_id' => $brandId, 'mobile_model_id' => $mobileModel, 'problem_type' => $problemTypeVal, 'problem' => $problem, 'repair_price' => $repairPrice, 'repair_days' => $repairDays, 'repair_cost' => $repairCost);
                        //}
                    //}
                            
                        $mobileRepairPrice = $this->MobileRepairPrices->newEntity();
                
                        $mobileRepairPrice = $this->MobileRepairPrices->patchEntity($mobileRepairPrice, $data);
                     // pr($mobileRepairPrice);die;
                       try{
                            if ($this->MobileRepairPrices->save($mobileRepairPrice)) {
                                $counter ++;	
                            }
                       }catch(\PDOException $e){
                            $error[] = "<br/>Record with this Brand:{$brandId}, Model:{$mobileModel} and problem type:{$problemType[$problemTypeVal]} already exist";
                       }    
                }
            }
            //foreach($data as $d_key => $d_value){
            //        $mobileRepairPrice = $this->MobileRepairPrices->newEntity();
            //        
            //        $mobileRepairPrice = $this->MobileRepairPrices->patchEntity($mobileRepairPrice, $d_value);
            //     // pr($mobileRepairPrice);die;
            //       try{
            //            if ($this->MobileRepairPrices->save($mobileRepairPrice)) {
            //                $counter ++;	
            //            }
            //       }catch(\PDOException $e){
            //            $error[] = "<br/>Record with this Brand:{$brandId}, Model:{$mobileModel} and problem type:{$problemType[$problemTypeVal]} already exist";
            //       }
            //    }
           $errorStr = "";
			if(count($error)){
				$errorStr = implode("<br/>", $error);
			}
            
            if ($counter > 0 ) {
                 $this->Flash->success(__("The mobile repair price for $counter records has been saved."),['escape' => false]);
				 return $this->redirect(['action' => 'index']);
            }else {
                $this->Flash->error(__("The mobile repair price could not be saved.{$errorStr}"),['escape' => false]);
				 
			}
		}
        $brands_query = $this->Brands->find('list', [
                                                        'keyField' => 'id',
                                                        'valueField' => 'brand',
                                                        'conditions' =>['Brands.status' => 1],
                                                        'order'=>['Brands.brand asc']
                                                    ]
                                            );
        if(!empty($brands_query)){
             $brands = $brands_query->toArray();
        }
        if(!$brand_id && !$this->request->is('post')){
			foreach($brands as $brand_id => $brand){break;}
		}else{
			if($this->request->is('post')){
				$brand_id = $this->request['data']['brand_id'];
			}
		}
        $mobileModels_query = $this->MobileModels->find('list',[
                                                                        'keyField' => 'id',
                                                                        'valueField' => 'model',
                                                                        'conditions' => ['MobileModels.brand_id' => $brand_id],
                                                                        'order'=>'MobileModels.model asc' 
                                                                    ] 
                                                            );
        if(!empty($mobileModels_query)){
             $mobileModels = $mobileModels_query->toArray();
        }
		 //pr($mobileModels);
        $this->set(compact('mobileRepairPrice', 'brands', 'mobileModels','problemType'));
        $this->set('_serialize', ['mobileRepairPrice']);
    }

    public function edit($id = null)
    {
        $active = Configure::read('active');
        $mobileRepairPrice = $this->MobileRepairPrices->get($id, [
            'contain' => []
        ]);
        if ($this->request->is(['patch', 'post', 'put'])) {
           // pr($this->request['data']);die;
               $brandId = $this->data["brand_id"];
               $countDuplicate_query = $this->MobileRepairPrices->find('all',
                                                        [
                                                         'conditions' => [
                                                                           'NOT'=>['MobileRepairPrices.id'=>$id],
                                                                           'MobileRepairPrices.brand_id'=>$brandId,
                                                                           'MobileRepairPrices.mobile_model_id'=>$this->data["mobile_model_id"],
                                                                           'MobileRepairPrices.problem_type'=>$this->data["problem_type"]
                                                                          ]
                                                        ]
                                                        );
            $countDuplicate = $countDuplicate_query->count();
            if($countDuplicate>=1){
                $this->Session->setFlash(__('This combination already exists'));
                return $this->redirect(array('action' => 'index'));
            }   
            $mobileRepairPrice = $this->MobileRepairPrices->patchEntity($mobileRepairPrice, $this->request->data);
            if ($this->MobileRepairPrices->save($mobileRepairPrice)) {
                $this->Flash->success(__('The mobile repair price has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The mobile repair price could not be saved. Please, try again.'));
        }
         $brands_query = $this->Brands->find('list', [
                                                        'keyField' => 'id',
                                                        'valueField' => 'brand',
                                                        'conditions' =>['Brands.status' => 1],
                                                        'order'=>['Brands.brand asc']
                                                    ]
                                            );
        if(!empty($brands_query)){
             $brands = $brands_query->toArray();
        }
        $model_query = $this->MobileModels->find('list', [
                                                        'keyField' => 'id',
                                                        'valueField' => 'model',
                                                         'conditions' =>['MobileModels.status' => 1],
                                                          'order'=>['MobileModels.model asc']
                                                    ] 
                                            );
        if(!empty($model_query)){
             $mobileModels = $model_query->toArray();
        }
        $problem_query = $this->ProblemTypes->find('list', [
                                                        'keyField' => 'id',
                                                        'valueField' => 'problem_type',
                                                          'conditions' =>['ProblemTypes.status' => 1],
                                                          'order'=>['ProblemTypes.problem_type asc']
                                                    ] 
                                                    
                                            );
        if(!empty($problem_query)){
             $problemtype = $problem_query->toArray();
        }
        
        $this->set(compact('problemtype','active')); 
        $this->set(compact('mobileRepairPrice', 'brands', 'mobileModels'));
        $this->set('_serialize', ['mobileRepairPrice']);
    }
   	
	 public function brandSuggestions($search = ""){
		if(array_key_exists('search',$this->request->query)){
			$search = strtolower($this->request->query['search']);
		}
		//--------modified code------------
		$digitsArr = array(0=>'0',1=>"1",2=>"2",3=>"3",4=>"4",5=>"5",6=>"6",7=>"7",8=>"8",9=>"9");
		$charsArr = str_split($search);
		foreach($charsArr as $char){
			if(trim($char) != ""){
				if(in_array($char, $digitsArr)){
					$search = str_replace($char, "%$char%",$search);
				}
			}
		}
		//---------------------------------
		$modelArr = array();
		if(!empty($search)){
            ob_start();
            preg_match('/^(?>\S+\s*){1,5}/', $search, $match);
			$search = $match[0];
			$this->pc_permute(explode(' ',$search));  //split(' ', $search)
			$permutation = ob_get_clean();
			$wordArray = explode("\n", $permutation);
			 
			
			foreach($wordArray as $value){
				if(empty($value))continue;
				$searchArray['OR'][] = "LOWER(`MobileModels`.`model`) like '%".str_replace(" ","%",$value)."%'";
			}
			
			$models_query = $this->MobileModels->find('all',array(
						'fields' => array('id','brand_id','model'),
						'conditions' => $searchArray,
						 
						  ));
            if(!empty($models_query)){
                $models = $models_query->toArray();
            }
            $brands_query = $this->Brands->find('list', [
                                                        'keyField' => 'id',
                                                        'valueField' => 'brand',
                                                       
                                                    ]
                                            );
            if(!empty($brands_query)){
                 $brands = $brands_query->toArray();
            }
			 
		}
		if($models){
			foreach($models as $model){
				$modelArr[] = array('id' => $model->id , 'brand' => $brands[$model->brand_id], 'model' => $model->model);
			}
		}
		/*
		 *if($models){//this code can be used in case we only need to show the models that are available in mobile repair prices table
			$modelIds = array();
			$existingModelIds = array();
			foreach($models as $model){
				$modelIds[$model['MobileModel']['id']] = $model['MobileModel']['id'];
			}
			$modelData = $this->MobileRepairPrice->find('all',array('conditions' => array('MobileRepairPrice.mobile_model_id' => $modelIds), 'fields' => array('mobile_model_id','brand_id')));
			
			$models = $this->MobileModel->find('list',array('conditions' => array('MobileModel.id' => $modelIds), 'fields' => array('id','model')));
			
			foreach($modelData as $key => $modelInfo){
				$existingModelIds[$modelInfo['MobileRepairPrice']['mobile_model_id']] = $modelInfo['MobileRepairPrice']['brand_id'];
			}
			
			foreach($existingModelIds as $existingModelId => $existingBrandId){
				$modelArr[] = array('id' => $existingModelId, 'brand' => $brands[$existingBrandId], 'model' => $models[$existingModelId]);
			}
		}
		 */
		echo json_encode($modelArr);
		$this->viewBuilder()->layout(false);
		die;
	}
	
    public function editGrid($id = null) {
        
		 $problemType = Configure::read('problem_type');
       // pr($problemType);
		 $mobileRepairPriceData_query = $this->MobileRepairPrices->find('all',
                                                                 [
                                                                  'conditions'=>['MobileRepairPrices.mobile_model_id'=>$id]
                                                                  ]
                                                                 );
         $mobileRepairPriceData_query = $mobileRepairPriceData_query->hydrate(false);
        
        if(!empty($mobileRepairPriceData_query)){
            $mobileRepairPriceData = $mobileRepairPriceData_query->toArray();
        }
       
		$brands_query = $this->Brands->find('list', [
                                                        'keyField' => 'id',
                                                        'valueField' => 'brand',
                                                        
                                                    ]
                                            );
        if(!empty($brands_query)){
             $brands = $brands_query->toArray();
        }
          $model_query = $this->MobileModels->find('list', [
                                                        'keyField' => 'id',
                                                        'valueField' => 'model',
                                                         'conditions' =>['MobileModels.id' => $id] 
                                                          
                                                    ] 
                                            );
        if(!empty($model_query)){
             $mobileModelName = $model_query->toArray();
        }
		 
	 
		if (empty($mobileRepairPriceData)) {
             $this->Flash->success(__('Invalid input!'));
		 	return $this->redirect(array('action' => 'index'));
			//NotFoundException(__('Invalid input!'));
		}
		 
		$problemRepairPriceArr = array();
		foreach($mobileRepairPriceData as $key => $mobileRepairPriceInfo){
			$problemRepairPriceArr[$mobileRepairPriceInfo['problem_type']] = $mobileRepairPriceInfo;
		}
        //pr($problemRepairPriceArr);die;
		if ($this->request->is(array('post', 'put'))) {
           
			if(array_key_exists('delete',$this->request->data)){
				$delArray = $this->request->data['MobileRepairPrice']['del'];
				$delIds = array();
				foreach($delArray as $key => $delId){
					if($delId > 0){
						$delIds[] = $delId;
					}
				}
				
				if(count($delIds)){
					$implodeIds = "('".implode("','",$delIds)."')";
					//DELETE FROM `mobile_repairs` WHERE `id` IN ('1','2')
					$query = "DELETE FROM `mobile_repair_prices` WHERE `id` IN $implodeIds";
                    $conn = ConnectionManager::get('default');
                    $stmt = $conn->execute($query);
					$this->Flash->success('Records deleted!');
					return $this->redirect(array('action' => 'index'));
				}else{
                      $this->Flash->success(__('Records could not be deleted!'));
					 
					return $this->redirect(array('action' => 'index'));
				}
				die;
			}
			$counter = 0;
			$error = array();
            //pr($this->request);die; 
			foreach($this->request['data']['MobileRepairPrice']['repair_cost'] as $key => $repair_cost){
				$repair_price = $this->request['data']['MobileRepairPrice']['repair_price'][$key];
				$repair_days = $this->request['data']['MobileRepairPrice']['repair_days'][$key];
				$mobileRepairPriceId = $this->request['data']['MobileRepairPrice']['id'][$key];
				$brand_id = $this->request['data']['MobileRepairPrice']['brand_id'][$key];
				$mobile_model_id = $this->request['data']['MobileRepairPrice']['mobile_model_id'][$key];
				$problem_type = $this->request['data']['MobileRepairPrice']['problem_type'][$key];
				//echo $key."->".$repair_price."&nbsp".$repair_cost."&nbsp".$repair_days."<br/>";
				if(isset($repair_price) || $repair_price == 0 || isset($repair_cost) || $repair_cost == 0 || isset($repair_days) || $repair_days == 0){
					if((!is_numeric($repair_cost) && !empty($repair_price)) ||
					   (!is_numeric($repair_cost) && !empty($repair_days))
					   ){
						$error[] = "Please input repair cost for the combination of: $brands[$brand_id], $mobileModelName[$mobile_model_id] and $problemType[$problem_type]";
					}
					if((!empty($repair_days) && !is_numeric($repair_price)) ||
					   (!empty($repair_cost) && !is_numeric($repair_price))){
                        //pr($problemType);die;
						$error[] = "Please input repair price for the combination of: $brands[$brand_id], $mobileModelName[$mobile_model_id] and $problemType[$problem_type]";
					}
					if((empty($repair_days) && is_numeric($repair_cost)) ||
					   (empty($repair_days) && is_numeric($repair_price)) ||
					   (!is_numeric($repair_days) && !empty($repair_days)) ||
					   (is_numeric($repair_days) && $repair_days == 0)
					   ){
						$error[] = "Please input repair days for the combination of: $brands[$brand_id], $mobileModelName[$mobile_model_id] and $problemType[$problem_type] in numbers & more than zero!";
					}
				}
			}
			
			$errorStr = "";
			if(count($error)){
				$errorStr = implode("<br/>",$error);
				$this->Flash->error("$errorStr. Please, try again.",['escape' => false]);
				//return $this->redirect(array('action' => 'edit_grid', $id));
			}else{
                //pr($this->request);die;
				foreach($this->request['data']['MobileRepairPrice']['repair_cost'] as $key => $repair_cost){
					//$this->MobileRepairPrice->clear();
					$orig_repair_cost = $this->request['data']['MobileRepairPrice']['orig_repair_cost'][$key];
					$orig_repair_price = $this->request['data']['MobileRepairPrice']['orig_repair_price'][$key];
					$repair_price = $this->request['data']['MobileRepairPrice']['repair_price'][$key];
					$orig_repair_days = $this->request['data']['MobileRepairPrice']['orig_repair_days'][$key];
					$repair_days = $this->request['data']['MobileRepairPrice']['repair_days'][$key];
					$mobileRepairPriceId = $this->request['data']['MobileRepairPrice']['id'][$key];
					$brand_id = $this->request['data']['MobileRepairPrice']['brand_id'][$key];
					$mobile_model_id = $this->request['data']['MobileRepairPrice']['mobile_model_id'][$key];
					$problem_type = $this->request['data']['MobileRepairPrice']['problem_type'][$key];
					//echo $repair_price."-".$repair_cost."-".$repair_days."-".$mobileRepairPriceId;echo "</br>";continue;
					if(is_numeric($repair_price) && is_numeric($repair_cost) && is_numeric($repair_days) && is_numeric($mobileRepairPriceId)){
                        
						if($orig_repair_cost != $repair_cost ||
						   $orig_repair_price != $repair_price ||
						   $orig_repair_days != $repair_days){
                            //echo'hi';
							$repairPriceData = array(
                                            'id' => $mobileRepairPriceId,
                                            'repair_cost' => $repair_cost,
                                            'repair_price' => $repair_price,
                                            'repair_days' => $repair_days
								 );
                             $mobile_prices_entity = $this->MobileRepairPrices->get($mobileRepairPriceId);
                            $mobile_prices_entity = $this->MobileRepairPrices->patchEntity($mobile_prices_entity, $repairPriceData,['validate' => false]);
							//pr($mobile_prices_entity);die;
                            if($this->MobileRepairPrices->save($mobile_prices_entity)){
							 	$counter++;
							}
						}
					}elseif(is_numeric($repair_price) && is_numeric($repair_cost) && is_numeric($repair_days) && empty($mobileRepairPriceId)){
                        //echo'dudeja';
						$repairPriceData = array(
							'brand_id' => $brand_id,
							'mobile_model_id' => $mobile_model_id,
							'problem_type' => $problem_type,
							'repair_cost' => $repair_cost,
							'repair_price' => $repair_price,
							'repair_days' => $repair_days
								 );
						//$this->MobileRepairPrice->create();
                         $mobile_prices_entity = $this->MobileRepairPrices->newEntity($repairPriceData,['validate' => false]);
                         $mobile_prices_entity = $this->MobileRepairPrices->patchEntity($mobile_prices_entity, $repairPriceData,['validate' => false]);
						if($this->MobileRepairPrices->save($mobile_prices_entity,['validate' => false])){
							$counter++;
						}else{
							  debug($mobile_prices_entity->errors());die;
						}
					}
				}
			}
			if($counter > 0){
                $this->Flash->success(__("$counter records have been saved."));
				 
				return $this->redirect(array('action' => 'index'));
			}else{
                $this->Flash->error(__("No record saved."));
            }
			
		}
		$brand_id = $mobileRepairPriceData[0]['brand_id'];
		$mobile_model_id = $mobileRepairPriceData[0]['mobile_model_id'];
      
		$this->set(compact('brands','problemType ','mobileModelName', 'brand_id', 'mobile_model_id'));
		$this->set(compact('mobileRepairPriceData', 'problemRepairPriceArr','problemType'));
	}

    
    public function delete($id = null)
    {
        $this->request->allowMethod(['post', 'delete']);
        $mobileRepairPrice = $this->MobileRepairPrices->get($id);
        if ($this->MobileRepairPrices->delete($mobileRepairPrice)) {
            $this->Flash->success(__('The mobile repair price has been deleted.'));
        } else {
            $this->Flash->error(__('The mobile repair price could not be deleted. Please, try again.'));
        }

        return $this->redirect(['action' => 'index']);
    }
	

    
    public function repairPricePushNotification(){
		/*$currency = $this->setting['currency_symbol'];
		Configure::load('common-arrays');
		$problemType = Configure::read('options.problem_type');
		$mobileRepairPrices = $this->MobileRepairPrice->find('all',array(
																		 'conditions' => array('DATE(MobileRepairPrice.modified) >= DATE_ADD(CURDATE(), INTERVAL -3 DAY)'),
																		 'order' => array('MobileRepairPrice.modified' => 'DESC'),
																		 'recursive' => -1));
		//pr($mobileRepairPrices);
		$brandName = $this->Brand->find('list',array('fields' => array('id','brand')));
		
		$repairMobileModelIds = $mobileRepairPriceNotification = array();
		foreach($mobileRepairPrices as $key=>$mobileRepairPrice){
		   $repairMobileModelIds[$mobileRepairPrice['MobileRepairPrice']['mobile_model_id']] = $mobileRepairPrice['MobileRepairPrice']['mobile_model_id'];
		}
		$repairMobileModelNames = $this->MobileModel->find('list', array('conditions' => array('MobileModel.id' => $repairMobileModelIds),'fields' => array('id','model')));
		$repairPush = array();
		if(count($mobileRepairPrices)){
		    foreach($mobileRepairPrices as $key => $mobileRepairPrice){
		    $repairPush[] = "<strong>{$currency}{$mobileRepairPrice['MobileRepairPrice']['repair_price']}</strong></span> - {$brandName[$mobileRepairPrice['MobileRepairPrice']['brand_id']]}: {$repairMobileModelNames[$mobileRepairPrice['MobileRepairPrice']['mobile_model_id']]} [Problem Type:{$problemType[$mobileRepairPrice['MobileRepairPrice']['problem_type']]}]</span><!--with in <span style='color: crimson'><strong> {$mobileRepairPrice['MobileRepairPrice']['repair_days']}</strong></span> days.-->";
		    }
		    
		    $explodeStr = implode('<br/> ',$repairPush);
		    $text = "Repair price update:<br/>".$explodeStr.".";
		    $filename = "/var/www/vhosts/".ADMIN_DOMAIN."/httpdocs/app/Vendor/pusher/autoload.php";
		    require($filename);
		    $this->Pusher->push($text);//created in components
		}*/
		//above commented code shows the detail of model, price and problem type that has been modified within last 3 days
		$countModified_query = $this->MobileRepairPrices->find('all',array(
				'conditions' => array('DATE(MobileRepairPrices.modified)' => date('Y-m-d'))
			)
		);
        $countModified = $countModified_query->count();
		if($countModified > 0){
			$pushStr = "$countModified repair prices were updated today";
			$this->Pusher->push($pushStr);//created in components
		}
		return $this->redirect(array('action' => 'index'));
        }
}
