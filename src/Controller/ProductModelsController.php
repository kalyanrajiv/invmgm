<?php
namespace App\Controller;
use App\Controller\AppController;
use Cake\Core\Configure;
use Cake\Core\Configure\Engine\PhpConfig;
use Cake\Event\Event;
class ProductModelsController extends AppController
{
    public $helpers = [
         'Paginator' => ['templates' => 'paginatortemplates']
         ];
    public function initialize()
    {
        parent::initialize();
         //$CURRENCY_TYPE = Configure::read('CURRENCY_TYPE');
       //Configure::load('common-arrays');
		$activeOptions = Configure::read('active');
		$this->set(compact('activeOptions'));
        $this->loadModel('ProductModels');
        $this->loadModel('Brands');

   }
    
    public function index() {
		  $this->loadModel('ProductModels');
		$this->ProductModels->recursive = 0;
		$this->paginate = [
							'limit' => ROWS_PER_PAGE,
                            'contain' => 'Brands'
                          ];
		$productModels = $this->paginate('ProductModels');
		 
		$brands_query = $this->Brands->find('list',[
                                                'keyField' => 'id',
                                                'valueField' => 'brand',
                                                'conditions' => ['Brands.status' => 1],
                                                'order' => ['Brands.brand asc'],
                                            ]
                                    );
        $brands_query = $brands_query->hydrate(false);
        if(!empty($brands_query)){
            $brands = $brands_query->toArray();
        }else{
            $brands = array();
        }
		//pr($brands);
		$this->set(compact('brands','productModels'));
	}
    
    private function generate_condition_array(){
        $searchKW = trim(strtolower($this->request->query['search_kw']));
		$conditionArr = array();
		if(!empty($searchKW)){
			$conditionArr['or']['ProductModels.model like'] =  strtolower("%$searchKW%");
		}
       // pr($this->request->query);
		if(array_key_exists('brand',$this->request->query)){
			$conditionArr['brand_id IN'] = $this->request->query['brand'];
			
		}
		return $conditionArr;
	}
    
    public function search($keyword = ""){
        $this->loadModel('Brands');
         $active = Configure::read('active');
        $conditionArr = $this->generate_condition_array();
        $brands_query = $this->Brands->find('list', [
                                                        'keyField' => 'id',
                                                        'valueField' => 'brand'
                                                    ],
                                                    ['Brands.status' => 1],
                                                    ['Brands.brand asc'] 
                                            );
        $brands = $brands_query->toArray();
        $selectedBrandIds = array();
		if(array_key_exists('brand_id IN',$conditionArr)){
			$selectedBrandIds = $conditionArr['brand_id IN'];
		}
        $selectedBrandsArr = array();
		foreach($selectedBrandIds as $key=>$brandId){
			$selectedBrandsArr[$brandId]=$brandId;
		}
        //  pr($conditionArr); 
        $query = $this->ProductModels->find('all', [
            'conditions' => $conditionArr,
             'limit' => 10
        ]);//die;
        $this->paginate = [
            'contain' => ['Brands']
        ];

        $results =    $this->paginate($query);
        $productModels = $results->toArray();
        $this->set(compact('brands','selectedBrandsArr','active'));
        $this->set(compact('productModels'));
		$this->render('index');
	}
    public function view($id = null){
        $mobileModel = $this->ProductModels->get($id, [
            'contain' => ['Brands']
        ]);
//pr($mobileModel);
        $this->set('mobileModel', $mobileModel);
        $this->set('_serialize', ['mobileModel']);
    }
	
	
	public function add()
    {
		$this->loadModel('Brands');
        $mobileModel = $this->ProductModels->newEntity();
        if ($this->request->is('post')) {
			$brandId = $this->request->data['brand_id'];
			$status = $this->request->data['status'];
			$data = $successfullySaved = array();
			$counter = 0;
			$error_data = array();
			$request_data = $this->request->data;
			
			foreach($this->request['data']['ProductModel']['model'] as $key => $mobileModel1){
				$briefDesc = $this->request->data['ProductModel']['brief_description'][$key];
				if(!empty($mobileModel1)){
					if(!in_array($mobileModel,$data)){
						$res_model_query = $this->ProductModels->find('all',array('conditions' => array(
																		'brand_id' =>$brandId,
																		'model' => $mobileModel1,
																		)));
						$res_model_query = $res_model_query->hydrate(false);
						if(!empty($res_model_query)){
							$res_model = $res_model_query->first();
						}else{
							$res_model = array();
						}
						if(!empty($res_model)){
							$error_data[] = array('brand_id' => $brandId, 'model' => $mobileModel1);	
						}else{
							$data[] = array('brand_id' => $brandId, 'model' => $mobileModel1,'brief_description' => $briefDesc,'status' => $status);	
						}
						
					}
				}
			}
			$msg = "";
			if(!empty($error_data)){
				foreach($error_data as $key => $value){
					$msg .= "Record Allready Exists for model ".$value['model']."</br>"; 
				}
			}
			if(!empty($msg)){
				$this->Flash->error($msg,['escape' => false]);
				$this->set('request_data',$request_data);
			}else{
				foreach($data as $d_key => $d_value){
					$mobileModel = $this->ProductModels->newEntity();
				   $mobileModel = $this->ProductModels->patchEntity($mobileModel, $d_value);
				   if ($this->ProductModels->save($mobileModel)) {
					   $counter ++;	
				   }
			   }
			   if ($counter > 0 ) {
				   $this->Flash->success(__($counter.' Product model has been saved.'));
			   
				   return $this->redirect(['action' => 'index']);
			   }
			   $this->Flash->error(__('The Product model could not be saved. Please, try again.'));	
			}
			
        }
		
		$brands_qry = $this->Brands->find('list', [
												   'keyField' => 'id',
												   'valueField' => 'brand',
												   ]);
        $brands = $brands_qry->toArray();
        $this->set(compact('mobileModel', 'brands'));
        $this->set('_serialize', ['mobileModel']);
    }



    public function edit($id = null){
        $mobileModel = $this->ProductModels->get($id, [
            'contain' => []
        ]);
        if ($this->request->is(['patch', 'post', 'put'])) {
            $mobileModel = $this->ProductModels->patchEntity($mobileModel, $this->request->data);
            if ($this->ProductModels->save($mobileModel)) {
                $this->Flash->success(__('The Product model has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The Product model could not be saved. Please, try again.'));
        }
        $brands_query = $this->ProductModels->Brands->find('list', [
                                                            'keyField' => 'id',
                                                            'valueField' => 'brand',
                                                           //  'limit' => 200
                                                             ]);
        $brands_query = $brands_query->hydrate(false);
        if(!empty($brands_query)){
            $brands = $brands_query->toArray();
        }else{
            $brands = array();
        }
        $this->set(compact('mobileModel', 'brands'));
        $this->set('_serialize', ['mobileModel']);
    }

    public function delete($id = null){
        $this->request->allowMethod(['post', 'delete']);
        $mobileModel = $this->ProductModels->get($id);
        if ($this->ProductModels->delete($mobileModel)) {
            $this->Flash->success(__('The Product model has been deleted.'));
        } else {
            $this->Flash->error(__('The Product model could not be deleted. Please, try again.'));
        }

        return $this->redirect(['action' => 'index']);
    }
    
    public function export(){
		$conditionArr = array();
		if(array_key_exists('search_kw',$this->request->query)){
			$conditionArr = $this->generate_condition_array();
		}
		if(count($conditionArr)>=1){			
			$productmodel_query = $this->ProductModels->find('all',array(
									'conditions' => $conditionArr,
                                    'contain' => 'Brands'));
            $productmodel_query = $productmodel_query->hydrate(false);
            if(!empty($productmodel_query)){
                $productModels = $productmodel_query->toArray();
            }else{
                $productModels = array();
            }
		}else{
			$productmodel_query = $this->ProductModels->find('all');
            $productmodel_query = $productmodel_query->hydrate(false);
            if(!empty($productmodel_query)){
                $productModels = $productmodel_query->toArray();
            }else{
                $productModels = array();
            }
		}
		// pr($ProductModels);die;
		$tmpProductModel = array();
		foreach($productModels as $key => $productModel){
            //pr($mobileModel);die;
            unset($productModel['brand']);
		 $tmpProductModel[] = $productModel;
		}
		//pr($tmpProductModel);die;
		$this->outputCsv('ProductModel_'.time().".csv" ,$tmpProductModel);
		$this->autoRender = false;
	}
}
