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


class ProductsController extends AppController
{
     public $helpers = [
         'Paginator' => ['templates' => 'paginatortemplates']
         ];
    var $components = array('CustomOptions');
	public $valuesToExclude = array('prefix', 'location', 'category_id', 'brand_id', 'vat_excluded_wholesale_price', 'vat_exclude_retail_price', 'image_id', 'image', 'image_dir', 'status', 'product_code', 'user_id', 'lu_cp', 'lu_rcp', 'lu_sp', 'lu_rsp','dead_stock_level', 'created', 'modified');
	public function initialize(){
        parent::initialize();
		
		$this->loadModel('Brands');
        $this->loadModel('settings');
	   $this->loadModel('DashboardData');
	   $this->loadModel('Kiosks');
        $this->loadModel('Categories');
        $this->loadModel('CsvProducts');
        $this->loadModel('ReorderLevels');
		$this->loadComponent('ScreenHint');
		$this->loadComponent('Barcode');
        $yesNoOptions=Configure::read('yes_no');
		$sites = Configure::read('sites');
        $this->set(compact('yesNoOptions'));
        $this->loadModel('ProductModels');
        $discountArr = [];
         for($i = 0; $i <= 50; $i++){
            if($i==0){
                $discountArr[0] = "None";
                continue;
            }
            $discountArr[$i] = "$i %";
         }
        $discountOptions = $discountArr;
		$this->loadComponent('Pusher');
		$featuredOptions = Configure::read('featured');
		$statusOptions = Configure::read('active');
		$colourOptions = Configure::read('colour');
        $CURRENCY_TYPE = Configure::read('CURRENCY_TYPE');
		$this->set(compact('discountOptions','CURRENCY_TYPE','featuredOptions','statusOptions','colourOptions'));
    }
	
    public function index(){
		$active = Configure::read('active');
		$CURRENCY_TYPE = Configure::read('CURRENCY_TYPE');
		$this->set(compact('active','CURRENCY_TYPE' ));
		$vat = $this->setting['vat'];
		$this->set('vat', $vat);
       	$displayType = "";
		$discount = "";
		if(array_key_exists('search_kw',$this->request->query)){
			$searchKW = trim(strtolower($this->request->query['search_kw']));
		}
        if ($this->request->is(array('get', 'put'))) {
		  
			$conditionArr = array();
			if(!empty($searchKW)){
				$conditionArr['OR']['LOWER(product) like '] =  strtolower("%$searchKW%");
				$conditionArr['OR']['LOWER(product_code) like '] =  strtolower("%$searchKW%");
				$conditionArr['OR']['LOWER(Products.description) like '] =  strtolower("%$searchKW%");
			}
            if(empty($conditionArr)){
                $conditionArr = array('0'=>null);
            }
			if(array_key_exists('display_type',$this->request->query) && array_key_exists('discount',$this->request->query)){
				$displayType = $this->request->query['display_type'];
				$discount = $this->request->query['discount'];
				if($displayType == "show_all" && $discount == "discount"){
					$this->paginate = [
                        'contain' => ['Categories', 'Brands', 'Users'],
                       'conditions'=>  [
                                            $conditionArr,
                                            'Products.discount_status'=>1,
                                        ],
                         'limit' => 50,
                        'order' =>  ['Products.created desc'],
                        
                    ];
 
				}elseif($displayType=="more_than_zero" && $discount == "discount"){
                    $this->paginate = [
                        'contain' => ['Categories', 'Brands', 'Users'],
                       'conditions'=>  [
                                            $conditionArr,
											['Products.quantity NOT IN'=>0],
											['Products.discount_status'=>  1  ]	
                                        ],
                        'limit' => 50,
                        'order' => ['Products.created desc'],
                        
                    ];
				}elseif($displayType == "show_all" && $discount == "not_discount"){
                    $this->paginate = [
                        'contain' => ['Categories', 'Brands', 'Users'],
                          'conditions'=>  [
                                            $conditionArr,
											'Products.discount_status'=>0,
                                        ],
                        'limit' => 50,
                        'order' => ['Products.created desc'],
                        
                    ];
                }elseif($displayType=="more_than_zero" && $discount == "not_discount"){
                   $this->paginate = [
                        'contain' => ['Categories', 'Brands', 'Users'],
                        'conditions'=>  [
                                            $conditionArr,
											'NOT'=>['Products.quantity'=>0],
                                            ['Products.discount_status'=>0]
                                        ],
                        'limit' => 50,
                        'order' => ['Products.created desc'],
                        
                    ];
					 
				}elseif($displayType=="more_than_zero" && $discount == "all"){
                  $this->paginate = [
                    'contain' => ['Categories', 'Brands', 'Users'],
                                         'conditions'=>  [
                                             $conditionArr,
 											 'NOT'=>['Products.quantity'=>'0'] 
                                             
                                        ],
                       'limit' =>50,
                        'order' => ['Products.created desc']                        
                    ] ;
                   }elseif($displayType=="show_all" && $discount == "all"){
				//echo"hi";die;
                  $this->paginate = [
                    'contain' => ['Categories', 'Brands', 'Users'],
                                         'conditions'=>  [
                                             $conditionArr,
 											 'NOT'=>['Products.quantity'=>'0'] 
                                             
                                        ],
                       'limit' =>50,
                        'order' => ['Products.created desc']                        
                    ] ;
                   } 
			}else{
				//echo"hi";die;
               $this->paginate = [
                     'contain' => ['Categories', 'Brands', 'Users'],
                                   'conditions'=>  [
                                          //  'Products.discount_status'=>1 
										],
					'order' => ['Products.created desc'],
                        'limit' => 50,
                        
                        
                    ];
			}
			 
		}
		//pr($this->paginate);die;
		//pr()
       //pr($this->paginate);die;
        //$products = $this->paginate();die;
        //$this->paginate = [
        //    'contain' => ['Categories', 'Brands', 'Users']
        //];
        $products = $this->paginate($this->Products);
        $brands_query = $this->Brands->find('list', [
                                                        'keyField' => 'id',
                                                        'valueField' => 'brand'
                                                    ],
                                                    ['Brands.status' => 1],
                                                    ['Brands.brand asc'] 
                                            );
        
        $brands = $brands_query->toArray();
        $Categories_list_query = $this->Categories->find('list', [
                                                        'keyField' => 'id',
                                                        'valueField' => 'category'
                                                    ],
                                                    ['Categories.status' => 1],
                                                    ['Categories.category asc'] 
                                            );
        if(!empty($Categories_list_query)){
             $category_list = $Categories_list_query->toArray();
        }
        $Category_query = $this->Categories->find('all',array(
								'fields' => array('id', 'category','id_name_path'),
								'conditions' => array('Categories.status' => 1),
								'order' => 'Categories.category asc' 
								));
        if(!empty($Category_query)){
              $category = $Category_query->toArray();
        }
      
       // pr($category);
        $categories = $this->CustomOptions->category_options($category,true);
        if($this->request->is('post')){
		  
			  $activationData = $this->request->data['data']['Product']['activate'];
			  $deActivationData = $this->request->data['data']['Product']['deactivate'];
              
			$actCounter = 0;
			$deactCounter = 0;
			foreach($activationData as $productId => $activationStatus){
                
               if($activationStatus > 0){
                     // echo $activationStatus;
                    $this->Products->updateAll(['status' =>1], ['id' => $productId]);
                    $actCounter++;
                    //$productTable = TableRegistry::get('Products');
                    //$product = $productTable->get($productId); // Return article with id 12
                    //
                    //$product->status = '1';
                    //if($productTable->save($product)){
                    //     $actCounter++;
                    //}
                    //$products= TableRegistry::get('Products');
                    //$product = $products->get($productId);
                    //$product->status = 1;
                    //if($products->save($product)){
                    //     $actCounter++;
                    //  }  
					 
				}
			}
			 
			foreach($deActivationData as $prodctId => $deActivationStatus){
				if($deActivationStatus > 0){
                  $this->Products->updateAll(['status' =>0], ['id' => $prodctId]);
                  $deactCounter++;
                  	 
				}
			}
			
			if($actCounter > 0 || $deactCounter > 0){
                $this->Flash->success(__("$actCounter products have been activated, $deactCounter products have been deactivated"));
			}
		}
        //pr($category);die;
        $this->set(compact('categories','displayType','discount'));
        $this->set(compact('brands','active','categories','category_list'));
        $this->set(compact('products'));
        $this->set('_serialize', ['products']);
     }
    private function generate_condition_array(){
		if(array_key_exists('search_kw',$this->request->query)){
			$searchKW = trim(strtolower($this->request->query['search_kw']));
		}
		//$searchKW = trim(strtolower($this->request->query['search_kw']));
		$conditionArr = array();
		if(!empty($searchKW)){
			$conditionArr['OR']['LOWER(product) like '] =  strtolower("%$searchKW%");
			$conditionArr['OR']['LOWER(product_code) like '] =  strtolower("%$searchKW%");
			$conditionArr['OR']['LOWER(Products.description) like '] =  strtolower("%$searchKW%");
		}
		if(array_key_exists('category',$this->request->query)){
			$conditionArr['category_id IN'] = $this->request->query['category'];
		}
		return $conditionArr;
	}
	
	public function search($keyword = ""){
         $this->loadModel('Brands');
        $this->loadModel('settings');
        $this->loadModel('Categories');
        $active = Configure::read('active');
        $CURRENCY_TYPE = Configure::read('CURRENCY_TYPE');
         $this->set(compact('active','CURRENCY_TYPE' ));
		$time_start = microtime(true);
		$displayType = $discount = "";
		$vat = $this->setting['vat'];
		$this->set('vat', $vat);
		//pr($this->request);
        
		if(array_key_exists('display_type',$this->request->query)){$displayType = $this->request->query['display_type'];}
		if(array_key_exists('discount',$this->request->query)){$discount = $this->request->query['discount'];}
		$conditionArr = $this->generate_condition_array();
		
		if(array_key_exists('search_kw',$this->request->query)){
			$searchKW = trim(strtolower($this->request->query['search_kw']));
			/*$searchKW = explode(" ", $searchKW, 5);
			array_pop($searchKW);
			$searchKW = implode(" ", $searchKW);echo "Rasu:".$searchKW;*/
			preg_match('/^(?>\S+\s*){1,5}/', $this->request->query['search_kw'], $match);
			if(is_array($match) and count($match)){
				$searchKW = $match[0];
			}
		}
		$conditionArr = array();
		if(!empty($searchKW)){
			$conditionArr['OR']['LOWER(product) like '] =  strtolower("%$searchKW%");
			$conditionArr['OR']['LOWER(product_code) like '] =  strtolower("%$searchKW%");
			$conditionArr['OR']['LOWER(Products.description) like '] =  strtolower("%$searchKW%");
		}
        //pr($this->request->query);die;
		if(array_key_exists('category',$this->request->query)&& !empty($this->request->query['category'][0])){
			$conditionArr['category_id IN'] = $this->request->query['category'];
		}
        //pr($this->request->query['category']);die;
		if($displayType=="more_than_zero"){$conditionArr['NOT']['`Products`.`quantity`'] = 0;}
		if($discount=="not_discount"){$conditionArr['`Products`.`discount_status`'] = 0;}
		if($discount=="discount"){$conditionArr['`Products`.`discount_status`'] = 1;}
		//pr($conditionArr);//die;
        
         $this->paginate = [
            'contain' => ['Categories','Brands','Users'],
                                         'conditions'=>  [$conditionArr],
                        'limit' => [50],
                        'order' => ['Products.created desc']                        
                    ] ;
                   
        $products_query = $this->paginate($this->Products);
        if(!empty($products_query)){
             $products = $products_query->toArray();
        }
        //pr($conditionArr);die;
		$selectedCategoryId = array();
        //pr($conditionArr);die;
		if(array_key_exists('category_id IN',$conditionArr)){
			$selectedCategoryId = $conditionArr['category_id IN'];
		}else{
            $selectedCategoryId = array(0 => 0);
        }
        $brands_query = $this->Brands->find('list', [
                                                        'keyField' => 'id',
                                                        'valueField' => 'brand'
                                                    ],
                                                    ['Brands.status' => 1],
                                                    ['Brands.brand asc'] 
                                            );
        if(!empty($brands_query)){
             $brands = $brands_query->toArray();
        }
        $Categories_list_query = $this->Categories->find('list', [
                                                        'keyField' => 'id',
                                                        'valueField' => 'category'
                                                    ],
                                                    ['Categories.status' => 1],
                                                    ['Categories.category asc'] 
                                            );
        if(!empty($Categories_list_query)){
             $category_list = $Categories_list_query->toArray();
        }
       //pr($brands);die;
        $this->set(compact('brands','category_list','active'));
		$this->set(compact('products','displayType','discount'));
		$Category_query = $this->Categories->find('all',
                                                  array(
								'fields' => array('id', 'category','id_name_path'),
								'conditions' => array('Categories.status' => 1),
								'order' => 'Categories.category asc' 
								));
        if(!empty($Category_query)){
             $category = $Category_query->toArray();
        }
        //pr($selectedCategoryId);die;
        $categories = $this->CustomOptions->category_options($category,true,$selectedCategoryId);
         //pr($categories);die;
        $this->set(compact('products'));
        $this->set('_serialize', ['products']);
         $this->set(compact('brands','active','categories'));
		//$categories = $this->CustomOptions->category_options($category,true,$selectedCategoryId);
		 $this->render('index');
         
		//$this->layout = 'default'; 
		
		//ConnectionManager::drop('default');
		$time_end = microtime(true);
		$execution_time = ($time_end - $time_start)/60;
		$rand = rand(10000,99999);
		//mail('kalyanrajiv@gmail.com', "Script execution time $rand ", $execution_time. " for keyword ".$this->request->query['search_kw'] );
        
	}

	 public function view($id = null) {
		  
		  if(($this->request->session()->read('Auth.User.group_id') != KIOSK_USERS) && ($this->request->session()->read('Auth.User.group_id') != ADMINISTRATORS)){
			   $this->Flash->error(__('You are not authorized to access that location.'));
			   return $this->redirect(array('controller'=>'home','action' => 'dashboard'));
		  }
	 	  
		  if (!$this->Products->exists($id)) {
			   throw new NotFoundException(__('Invalid product'));
		  }
		  
		   $product_query = $this->Products->get($id, [
                'contain' => ['Categories','ReorderLevels','Brands']
            ]);
		   
		//  $product = $this->Products->get($id,['contain' =>['Categories','ReorderLevels','Brands']]);
       // $product_query = $product_query->hydrate(false);
			   if(!empty($product_query)){
					 $product = $product_query->toArray();
			   }else{
					 $product = array();
			   }
               if(array_key_exists('additional_model_id',$product) && !empty($product['additional_model_id'])){
					$additionalModelIds = explode(',',$product['additional_model_id']);
					//foreach($additionalModelIds as $key => $addModelId){
						 $mobileModels_query = $this->ProductModels->find('list',array(
																						  'keyField' =>'id',
																						  'valueField' => 'model',
																						  'order'=>'model asc',
																						 'conditions'=>array(
																						  'id IN'=>$additionalModelIds,
																						  )
																						 )
																					);
						 $mobileModels_query = $mobileModels_query->hydrate(false);
						 if(!empty($mobileModels_query)){
							  $mobileModels = $mobileModels_query->toArray();
							  $product['additional_model'] = implode(' , ',$mobileModels);
						 }else{
							  $mobileModels = array();
							  $product['additional_model'] = '';
						 }
						 //pr($mobileModels);die;
					//}
			   }else{
					$product['additional_model'] = '';
			   }
			   $product_code = $product['product_code'];
			   
			   $barcode = $this->Barcode->generate_bar_code($product_code,"png"); // html,png,svg,jpg
		//  pr($product); 
		  $this->set(compact('product','barcode'));
		  $userNameDetails = '';
		  if(array_key_exists('user_id',$product)){
			   $user_id = $product['user_id'];
			   $userNameDetails_query = $this->Users->find('all',array('conditions'=>array('Users.id'=>$user_id),'fields'=>array('id','username')));
			   $userNameDetails_query = $userNameDetails_query->hydrate(false);
			   if(!empty($userNameDetails_query)){
					 $userNameDetails = $userNameDetails_query->first();
			   }else{
					 $userNameDetails = array();
			   }
			  //if(array_key_exists('User',$userNameDetails)){
			   $userName = $userNameDetails['username'];
			   $this->set(compact('userName'));
			  //}
		  }
			   $Users_query = $this->Users->find('list',array('keyField' => 'id',
											   'valueField' => 'username',
											   ));
			   $Users_query = $Users_query->hydrate(false);
			   if(!empty($Users_query)){
					$Users = $Users_query->toArray();
			   }else{
					$Users = array();
			   }
               //pr($Users);
			   $this->set(compact('Users'));
		  if($this->request->session()->read('Auth.User.group_id') == KIOSK_USERS){
			   $this->render('view_kiosk');
		  }elseif($this->request->session()->read('Auth.User.group_id') == ADMINISTRATORS || $this->request->session()->read('Auth.User.group_id') == FRANCHISE_OWNER){
			   $this->render('view');
		  }else{
			   //pr($_SESSION);die;
			   $this->Flash->error(__('You are not authorized to access that location.'));
			   return $this->redirect(array('controller'=>'home','action' => 'dashboard'));
		  }
	 }
	
    /**
     * Add method
     *
     * @return \Cake\Network\Response|null Redirects on successful add, renders view otherwise.
     */
    public function add1()
    {
        $product = $this->Products->newEntity();
        if ($this->request->is('post')) {
            $product = $this->Products->patchEntity($product, $this->request->data);
            if ($this->Products->save($product)) {
                $this->Flash->success(__('The product has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The product could not be saved. Please, try again.'));
        }
        $categories = $this->Products->Categories->find('list', ['limit' => 200]);
        $brands = $this->Products->Brands->find('list', ['limit' => 200]);
        $users = $this->Products->Users->find('list', ['limit' => 200]);
        //$images = $this->Products->Images->find('list', ['limit' => 200]);
        $this->set(compact('product', 'categories', 'brands', 'users', 'images'));
        $this->set('_serialize', ['product']);
    }
	
	public function add() {
	 $sites = Configure::read('sites');
	 $path = dirname(__FILE__);
		$isboloRam = strpos($path, ADMIN_DOMAIN);
		if($isboloRam == false){
            $this->Flash->error(__("This function works only on ". ADMIN_DOMAIN));
			return $this->redirect(array('controller' => 'home','action' => "dashboard"));die;
		}
		$vat = $this->setting['vat'];
		$this->set('vat', $vat);
		$kioskList_query = $this->Kiosks->find('list',
												  [
													   'keyField' => 'id',
													   'valueFiels' => 'name',
													   'conditions'=>['Kiosks.status'=>1]
												  ]
										 );
		$kioskList_query = $kioskList_query->hydrate(false);
		$kioskList = $kioskList_query->toArray();
		 $product = $this->Products->newEntity();
		if ($this->request->is(array('post', 'put'))) {
		    $res = $this->request->data['Product'];
			  //pr($this->request);die;
		  $this->request->data['Product']['discount_status'] = $this->request->data['discount_status'];
		  $this->request->data['Product']['rt_discount_status'] = $this->request->data['rt_discount_status'];
		  unset($this->request->data['discount_status']);
		  unset($this->request->data['rt_discount_status']);
		  
		//  pr($this->request->data);die;
			$ProductsEntity = $this->Products->newEntity();
			$this->request->data['Product']['user_id'] = $this->request->Session()->read('Auth.User.id');
			if(isset($this->request->data['Product']['image']['name']) && !empty($this->request->data['Product']['image']['name'])){
			   $imageName = $this->request->data['Product']['image']['name'];
			}else{
			   $imageName = "";
			}
			$ProductsEntity = $this->Products->patchEntity($ProductsEntity,$this->request->data['Product']);
			//if(!empty($ProductsEntity->errors())){
			//   $this->request->data = $res;
			//  //return $this->redirect(array('action' => 'add'));
			//}
			//die;
			//pr($ProductsEntity);die;
			//$this->Product->set($this->request->data);
			//if ($this->Product->validates()) {
			//	;
			//}else{
			//	// didn't validate logic
			//	$errors = $this->Product->validationErrors;
			//}
			
			//debug($this->Product->validationErrors);
			$isSaved = false;
			if(empty($ProductsEntity->errors())){
			   try{
					//die;
                   // pr($ProductsEntity);die;
				   if( $this->Products->save($ProductsEntity) ){
                    $boloram_data = $data = $this->request->data;
					//   $data = $this->request->data;
					   //saving the same in all the kiosk tables, updating cost price of retail table as retail_cost_price of main table.
					   $productId = $ProductsEntity->id;
					   $data['Product']['id'] = $productId;
					   if(!empty($imageName)){
						 if(mkdir(WWW_ROOT."files/Products/image/{$productId}")){
								if(rename(WWW_ROOT."files/Products/image/{$imageName}", WWW_ROOT."files/Products/image/{$productId}/{$imageName}")){
									 $query = "UPDATE products SET image_dir = {$productId} where id = {$productId}";
									 $query2 = "UPDATE products SET image = '$imageName' WHERE id = $productId";
									 $conn = ConnectionManager::get('default');
									 $stmt = $conn->execute($query);
									 $stmt = $conn->execute($query2);
								}
						 }
					   }
					   
					   //$data['Product']['cost_price'] = $this->request['data']['Product']['retail_cost_price'];
					   //$data['Product']['selling_price'] = $this->request['data']['Product']['retail_selling_price'];
					   //$data['Product']['discount_status'] = $this->request['data']['Product']['rt_discount_status'];
					   //$data['Product']['discount'] = $this->request['data']['Product']['retail_discount'];
					   
					   //if(array_key_exists('name',$data['Product']['image']) && !empty($data['Product']['image']['name'])){
					   //	unset($data['Product']['image']['tmp_name']);
					   //	unset($data['Product']['image']['size']);
					   //	unset($data['Product']['image']['error']);
					   //	$data['Product']['image'] = $this->request->data['Product']['image']['name'];
					   //}else{
					   //	unset($data['Product']['image']);
					   //}
					   unset($data['Product']['special_offer']);
					  // pr($data);die;
					   $count = 0;
					   foreach($kioskList as $kioskId=>$kioskName){
							$table_source = "kiosk_{$kioskId}_products";
                            $productTable = TableRegistry::get($table_source,[
																				'table' => $table_source,
							]);
						   $productEntity = $productTable->newEntity();
						   $productEntity = $productTable->patchEntity($productEntity,$data['Product']);
						   if($productTable->save($productEntity)){
                            $count++;
                           }
					   }
					   $boloram_data['Product']['id'] = $productId;
						 if($count > 0){
							  if(!empty($sites)){
								   foreach($sites as $site_id => $site_val){
										$this->add_on_boloram($boloram_data,$site_val);  		
								   }
								   
							  }
						 }
						 $count_k = 0;
					 $msg = "The product has been saved. ";
						 if($productId){
							  if(!empty($sites)){
								   
								   foreach($sites as $site_id => $site_val){
										$conn = ConnectionManager::get($site_val);
										$stmt = $conn->execute("SELECT id,product FROM products WHERE id = $productId");
										$product_data = $stmt ->fetchAll('assoc');
										if(empty($product_data)){
											 $count_k++; 
											 if($count_k == 1){
												  $msg .= " But Failed to create product on $site_val </br>";	  
											 }else{
												  $msg .= "Failed to create product on $site_val </br>";
											 }
											 
										}
								   }
								   if($count_k >= 1){
										 $msg .= "Please re-edit this product on main site and re-submit this product should fix this issue";
								   }
							  }
						 }
						 $this->Flash->success($msg,['escape' => false]);
						 if($count_k >= 1){
							  return $this->redirect(array('action' => 'edit',$productId));	  
						 }else{
							  return $this->redirect(array('action' => 'index'));
						 }
					   
					   
				   }else{
                   // debug($ProductsEntity->errors());die;
					if(!empty($ProductsEntity->errors())){
                        foreach($ProductsEntity->errors() as $key){
                             foreach($key as $value){
                                $errors[] = $value;  
                             }
                        }
                    }
                   // pr($errors);
					   $this->Flash->error(__('The product could not be saved. Please, try again.'));	
				   }
				   
			   }catch(Exception $e){
				   $product_code = $this->request->data['Product']['product_code'];
				   $this->Flash->error("<br/>Duplicate record found with product code {$product_code}.");
			   }
			
			}else{
			   //echo "hi";die;
			   $errors = array();
               //debug($ProductsEntity->errors());die;
			   if(!empty($ProductsEntity->errors())){
					foreach($ProductsEntity->errors() as $key){
						 foreach($key as $value){
							$errors[] = $value;  
						 }
					}
					$this->Flash->error(implode("</br>",$errors),['escape' => false]);
			   }
			   $this->request->data = $res;
			}
			
		}
		$categories_query = $this->Products->Categories->find('all',array('fields' => array('id', 'category','id_name_path'),
                                                                       'conditions' => array('Categories.status' => 1),
                                                                       'order'=>'category asc'));
		$categories_query = $categories_query->hydrate(false);
		if(!empty($categories_query)){
			   $categories = $categories_query->toArray(); 
		}else{
			   $categories = $categories_query->toArray();
		}
		
		
		$categories = $this->CustomOptions->category_options($categories);
		
		$brands_query = $this->Products->Brands->find('list',
															[
																 'keyField' => 'id',
																 'valueField' => 'brand',
																 'conditions' => ['Brands.status' => 1],
                                                                 'order'=>'brand asc',
															]);
		$brands_query = $brands_query->hydrate(false);
		if(!empty($brands_query)){
			   $brands = $brands_query->toArray(); 
		}else{
			   $brands = array();
		}
		
		$this->set(compact('categories', 'brands','statusOptions','featuredOptions','discountOptions','product'));
	}

    
     public function add_on_boloram($boloram_data,$site_val){
		  
	  $external_sites = Configure::read('EXT_RETAIL');
	  
	  $disnt_for_sites = Configure::read('disnt_for_diff_sites');
	  $disnt_for_diff_sites = 0;
	  if(!empty($disnt_for_sites)){
		  foreach($disnt_for_sites as $dis_key => $dis_value){
			   if($dis_value == $site_val){
					$disnt_for_diff_sites = 1;
			   }
		  }
	  }
	  
	  
	  $external_site_status = 0;
	  if(in_array($site_val,$external_sites)){
		  $external_site_status = 1;
	  }
       if(!array_key_exists("Product",$boloram_data)){
			return;
		}
		//pr($boloram_data['Product']);die;
		if(array_key_exists('image',$boloram_data['Product'])){
			$image_data = $boloram_data['Product']['image'];
			unset($boloram_data['Product']['image']);
			if(array_key_exists("name",$image_data)){
				$boloram_data['Product']['image'] = $image_data['name']; 
			}
		}
		
		if(array_key_exists("image_dir",$boloram_data["Product"])){
			unset($boloram_data["Product"]["image_dir"]);
		}
		
		if(array_key_exists("retail_cost_price",$boloram_data["Product"])){
			$boloram_data["Product"]["cost_price"] = $boloram_data["Product"]["retail_cost_price"];
			if($external_site_status == 1){
			   $boloram_data["Product"]["cost_price"] = $boloram_data["Product"]["selling_price"]/1.2;
			   $boloram_data["Product"]["retail_cost_price"] = $boloram_data["Product"]["selling_price"]/1.2;
			}
			//unset($boloram_data["Product"]["retail_cost_price"]);
		}
		if(array_key_exists("retail_selling_price",$boloram_data["Product"])){
			if($external_site_status == 1){
			   $without_vat = (($boloram_data["Product"]["selling_price"]/1.2));
			   $boloram_data["Product"]["selling_price"] = $boloram_data["Product"]["retail_selling_price"];//$without_vat + ($without_vat*(10/100));
			}else{
			   $boloram_data["Product"]["selling_price"] = $boloram_data["Product"]["retail_selling_price"];   
			}
			//unset($boloram_data["Product"]["retail_selling_price"]);
		}
		if(array_key_exists("retail_discount",$boloram_data["Product"])){
		  if($disnt_for_diff_sites == 1){
			   $boloram_data["Product"]["discount"] = 50;
		  }else{
			 $boloram_data["Product"]["discount"] = $boloram_data["Product"]["retail_discount"];  
		  }
			unset($boloram_data["Product"]["retail_discount"]);
		}
		if(array_key_exists("rt_discount_status",$boloram_data["Product"])){
		  if($disnt_for_diff_sites == 1){
			   $boloram_data["Product"]["discount_status"] = 1;
		  }else{
			 $boloram_data["Product"]["discount_status"] = $boloram_data["Product"]["rt_discount_status"];  
		  }
			
			unset($boloram_data["Product"]["rt_discount_status"]);
		}
		if(array_key_exists("retail_special_offer",$boloram_data["Product"])){
			$boloram_data["Product"]["special_offer"] = $boloram_data["Product"]["retail_special_offer"];
			unset($boloram_data["Product"]["retail_special_offer"]);
		}
		
		if(array_key_exists("Min Sale Amount",$boloram_data["Product"])){
			unset($boloram_data["Product"]["Min Sale Amount"]);
		}
		
		if(array_key_exists("Final Discount",$boloram_data["Product"])){
			unset($boloram_data["Product"]["Final Discount"]);
		}
        if(array_key_exists("manufacturing_date",$boloram_data["Product"])){
			 $boloram_data["Product"]["manufacturing_date"] =  $boloram_data["Product"]["manufacturing_date"]["year"]."-".$boloram_data["Product"]["manufacturing_date"]["month"]."-".$boloram_data["Product"]["manufacturing_date"]["day"];
		}
        if(array_key_exists('location',$boloram_data["Product"])){
		  unset($boloram_data["Product"]['location']);
		}
      //  pr($boloram_data);die;
         unset($boloram_data["Product"]["null"]);
      
	  
	  
        $conn = ConnectionManager::get($site_val);
        $stmt = $conn->execute("SELECT id,name FROM kiosks");
        $kiosks_data = $stmt ->fetchAll('assoc');
        $kiosks = array();
        foreach($kiosks_data as $key => $value){
            $kiosks[$value['id']] = $value['name'];
		}
		$boloram_data['Product']['created'] = date("Y-m-d h-i-s");
		
       // $conn = ConnectionManager::get('mbwaheguru');
	   
        if($statement = $conn->insert('products',$boloram_data['Product']))  {
		  
            $data = $boloram_data;
            $productId =  $statement->lastInsertId('products');
            $res = $conn->update('products',['image_dir' => $productId ],['id' => $productId]);
            //$stmt = $conn->execute("update products set image_dir = $productId where id = $productId");
            //$result = $stmt->fetchAll('assoc');
             $data['Product']['id'] = $productId;
			unset($data["Product"]["lu_cp"]);
			unset($data["Product"]["lu_sp"]);
			unset($data["Product"]["special_offer"]);
           // pr($kiosks);die;
		  
            foreach($kiosks as $kiosk_id => $name){
                $table =  "kiosk_{$kiosk_id}_products";
                //$this->Product->setSource($table);//'Product.product_code' => $product_code
				//pr($data['Product']);
                $res = $conn->insert($table,$data['Product']);
                $productId =  $res->lastInsertId('products');
                $res1 = $conn->update($table,['image_dir' => $productId ],['id' => $productId]);
            }
			   //die;
		}else{
           //echo "else";die; 
			$errors = $this->Products->validationErrors;
			//pr($errors);die;
		}
	}
     
    public function edit($id = null) {
        //$this->request->Session()->read('Auth.User.id');die;
        $sites = Configure::read('sites');
        $path = dirname(__FILE__);
		$isboloRam = strpos($path, ADMIN_DOMAIN);
		if($isboloRam == false){
            $this->Flash->error(__("This function works only on ". ADMIN_DOMAIN));
			return $this->redirect(array('controller' => 'home','action' => "dashboard"));die;
		}
		$vat = $this->setting['vat'];
		$this->set('vat', $vat);
		$kioskList_query = $this->Kiosks->find('list',[
                                                    'keyField' => 'id',
                                                    'valueField' => 'name',
                                                    'conditions'=>['Kiosks.status'=>1]
                                                ]
                                        );
        $kioskList_query = $kioskList_query->hydrate(false);
        if(!empty($kioskList_query)){
            $kioskList = $kioskList_query->toArray();
        }else{
            $kioskList = array();
        }
		if (!$this->Products->exists($id)) {
			throw new NotFoundException(__('Invalid product'));
		}else{
		  $ProductEntity = $this->Products->get($id);
		  $this->set('product',$ProductEntity);
		}
		$editErrors = array();
		if ($this->request->is(array('post', 'put'))) {
         //    pr($this->request->data);die;
            if(array_key_exists('additional_model_id',$this->request->data['Product']) && !empty($this->request->data['Product']['additional_model_id'])){
			   $this->request->data['Product']['additional_model_id'] = implode(',',$this->request->data['Product']['additional_model_id']);
            }
		  if(array_key_exists('discount_status',$this->request->data)){
			   $this->request->data['Product']['discount_status'] = $this->request->data['discount_status'];
			   unset($this->request->data['discount_status']);
		  }
		  if(array_key_exists('rt_discount_status',$this->request->data)){
			   $this->request->data['Product']['rt_discount_status'] = $this->request->data['rt_discount_status'];
			   unset($this->request->data['rt_discount_status']);
		  }
			//pr($this->request->data);die;
			$boloramData = $this->request->data;
			 
			//pr($boloramData);die;
			try{
			   // pr($this->request);die;
			   if(!empty($this->request->data['Product']['image']['name'])){
					$image_name = $this->request->data['Product']['image']['name'];
					unset($boloramData['Product']['image']);
			   }else{
					//$image_name = "";
			   }
			   
				$oldProductData_query = $this->Products->find('all',array('conditions' => array('Products.id' => $id),'fields' => array('id','cost_price','retail_cost_price','selling_price','retail_selling_price'),'recursive' => -1));
				$oldProductData_query = $oldProductData_query->hydrate(false);
				if(!empty($oldProductData_query)){
					$oldProductData = $oldProductData_query->first();
				}else{
					$oldProductData = array();
				}
				
				$this->request->data['Product']['modified_by'] = $this->request->Session()->read('Auth.User.id');
				$this->request->data['Product']['last_updated'] = date("Y-m-d H-i-s");
				$ProductEntity = $this->Products->patchEntity($ProductEntity,$this->request->data['Product']);
				$new_price = $this->request->data['Product']['selling_price'];
				$old_price = $oldProductData['selling_price'];
				
				$new_retail_price = $this->request->data['Product']['retail_selling_price'];
				$old_retail_price = $oldProductData['retail_selling_price'];
				
                
				if ($this->Products->save($ProductEntity)) {
					if($new_price != $old_price){
						 $this->update_transient_order($id,$new_price);
					 }
					 if($new_retail_price != $old_retail_price){
						 $this->update_retail_transient_order($id,$new_retail_price);
					 }
					
                 //   echo "ll";die;
					$id = $ProductEntity->id;
                    $path =  WWW_ROOT."files".DS."Products".DS."image".DS.$id.DS;
                    if(array_key_exists('remove',$this->request->data['Product'])){
                        $remove = $this->request->data['Product']['remove'];
                        $image_delete = $this->request->data['Product']['image']['name'];  
                        if($remove ==  1 ){
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
                    }//
                  //  if(!empty($this->request->data['Product']['image'])){
					 if(!empty($image_name)){
                      // echo $image_name;die;
						 $query1 = "UPDATE products SET image_dir = {$id} where id = {$id}"; 
						  $query2 = "UPDATE products SET image = '$image_name' WHERE id = $id";
						   $boloramData['Product']['image'] = $image_name;
						 $conn1 = ConnectionManager::get('default');
						 $stmt = $conn1->execute($query1);
						 $stmt = $conn1->execute($query2);
                        //  echo "kk";die;
					}else{
						 if($remove ==  1 ){
						 $boloramData['Product']['image'] = "";
						 }else{
							  unset($boloramData['Product']['image']);
						 }
					}
                   //pr($this->request->data['Product']);die;
                    
                   
					//updating the same in kiosk products table
					//$this->request->data['Product']['cost_price'] = $this->request['data']['Product']['retail_cost_price'];
					//$this->request->data['Product']['selling_price'] = $this->request['data']['Product']['retail_selling_price'];
					//$this->request->data['Product']['discount_status'] = $this->request['data']['Product']['rt_discount_status'];
					//$this->request->data['Product']['discount'] = $this->request['data']['Product']['retail_discount'];
					$errorStr = "";
					$errorArr = array();
					$data = $this->request->data;
                   unset($data['Product']['special_offer']);
                    unset($data['Product']['remove']);
                   unset($data['Product']['retail_special_offer']);
                    //pr($data);die;
					if(array_key_exists('name',$data['Product']['image']) && !empty($data['Product']['image']['name'])){
						unset($data['Product']['image']['tmp_name']);
						unset($data['Product']['image']['size']);
						unset($data['Product']['image']['error']);
						unset($data['Product']['image']['remove']);
						$data['Product']['image'] = $this->request->data['Product']['image']['name'];
					}else{
						unset($data['Product']['image']);
					}
                    
					$counter = 0;
					// echo count($kioskList);die;
					foreach($kioskList as $kioskId => $kioskName){
						if($kioskId == 10000){
							break;
						}
						
						$tableSource = "kiosk_{$kioskId}_products";
						$productTable = TableRegistry::get($tableSource,[
                                                                                    'table' => $tableSource,
                                                                                ]);
						//pr($this->request['data']);die;
						$checkId_query = $productTable->find('all',array(
							'conditions' => array('id' => $this->request['data']['Product']['id'],
							'product_code' => $this->request['data']['Product']['product_code']
									),
							'fields' => array('id'))
						);
                        
						$checkId_query = $checkId_query->hydrate(false);
						if(!empty($checkId_query)){
							  $checkId = $checkId_query->first();
						}else{
							  $checkId = array();
						}
                        
						///pr($data);die("--");
						if( (count($checkId) == 0) || !empty($checkId['id'])){
							unset($data['Product']['quantity']);//removing quantity from array
							
							//pr($data);
							//$this->Product->set($data);
							//if ($this->Product->validates()) {
							//	//echo "kiosk_{$kioskId}_products";
                            //pr($data['Product']);die;
							  $productEntity = $productTable->get($data['Product']['id']);
                              $data['Product']['modified_by'] = $this->request->Session()->read('Auth.User.id');
							  $productEntity = $productTable->patchEntity($productEntity,$data['Product']);
                                //pr($data['Product']);pr($productEntity);die;
							  if(empty($productEntity->errors())){
                                 //echo "jjds";die;
								$productTable->save($productEntity);
							  }else{
                               $errors = array();
								   if(!empty($productEntity->errors())){
										foreach($productEntity->errors() as $key){
											 foreach($key as $value){
												$errors[] = $value;  
											 }
										}
										$this->Flash->error(implode("</br>",$errors),['escape' => false]);
								   }
							  }
							//}else{
							//	$editErrors["kiosk_{$kioskId}_products"] = $this->Product->validationErrors;					
							//}
						}else{
							$errorMsg = "Kiosk with id: {$kioskId} have different product code at id: {$id} for product code: ".$this->request['data']['Product']['product_code']."  than mains products table";
							$errorArr[] = $errorMsg;
						}
					}
                    
					//$this->Product->setSource("products");
					$productTable = TableRegistry::get("products",[
                                                                                    'table' => "products",
                                                                                ]);
					//
					$old_cost_price = $oldProductData['cost_price'];
					$old_retail_cost_price = $oldProductData['retail_cost_price'];
					$old_selling_price = $oldProductData['selling_price'];
					$old_retail_selling_price = $oldProductData['retail_selling_price'];
					
					$newProductData_query = $productTable->find('all',array('conditions'=>array('id'=>$id),'fields'=>array('id','cost_price','retail_cost_price','selling_price','retail_selling_price')));
					
					$newProductData_query = $newProductData_query->hydrate(false);
					if(!empty($newProductData_query)){
						 $newProductData = $newProductData_query->first();
					}else{
						 $newProductData = array();
					}
					$new_cost_price = $newProductData['cost_price'];
					$new_retail_cost_price = $newProductData['retail_cost_price'];
					$new_selling_price = $newProductData['selling_price'];
					$new_retail_selling_price = $newProductData['retail_selling_price'];
					
					//$currentTimeInfo = $this->Product->query("SELECT NOW() as created");
					
					$conn = ConnectionManager::get('default');
					$stmt = $conn->execute('SELECT NOW() as created'); 
					$currentTimeInfo = $stmt ->fetchAll('assoc');
					
					$currentTime = $currentTimeInfo[0]['created'];
					
					if($old_cost_price!=$new_cost_price){
						 $conn = ConnectionManager::get('default');
						 $stmt = $conn->execute("UPDATE `products` SET `lu_cp`='$currentTime' WHERE `id`='$id'"); 
						//$this->Product->query("UPDATE `products` SET `lu_cp`='$currentTime' WHERE `id`='$id'");
					}
					
					if($old_retail_cost_price!=$new_retail_cost_price){
						 $conn = ConnectionManager::get('default');
						 $stmt = $conn->execute("UPDATE `products` SET `lu_rcp`='$currentTime' WHERE `id`='$id'"); 
						//$this->Product->query("UPDATE `products` SET `lu_rcp`='$currentTime' WHERE `id`='$id'");
					}
					
					if($old_selling_price!=$new_selling_price){
						 $conn = ConnectionManager::get('default');
						 $stmt = $conn->execute("UPDATE `products` SET `lu_sp`='$currentTime' WHERE `id`='$id'"); 
						//$this->Product->query("UPDATE `products` SET `lu_sp`='$currentTime' WHERE `id`='$id'");
					}
					
					if($old_retail_selling_price!=$new_retail_selling_price){
						 $conn = ConnectionManager::get('default');
						 $stmt = $conn->execute("UPDATE `products` SET `lu_rsp`='$currentTime' WHERE `id`='$id'"); 
						//$this->Product->query("UPDATE `products` SET `lu_rsp`='$currentTime' WHERE `id`='$id'");
					}
					//echo "hi";
					 //echo "helo";die;
					//$this->Product->setDataSource('default');
					if(!empty($sites)){
						 foreach($sites as $site_id => $site_value){
							  $this->update_boloram($boloramData,$site_value);	  
						 }
					}
					if(count($errorArr)){
						$errorStr.=implode("<br/>",$errorArr);
					}
					if(!empty($errorStr)){$errorStr.="<br/>But we still saved for other kiosks<br/>Send this message to site support team to resolve this issue<br/>";}
					$this->Flash->success("{$errorStr}The product has been saved.",['escape' => false]);
					return $this->redirect(array('action' => 'index'));
				} else {
					 $errors = array();
					if(!empty($ProductEntity->errors())){
						 foreach($ProductEntity->errors() as $key){
							  foreach($key as $value){
								 $errors[] = $value;  
							  }
						 }
						 $this->Flash->error(implode("</br>",$errors),['escape' => false]);
					}
					$this->request->data['Product']['image'] = $this->request->data['Product']['image']['name'];
					$this->request->data = $this->request->data['Product'];
					$this->Flash->error(__('The product could not be saved. Please, try again.'));
				}
					
			}catch(Exception $e){
				//pr($editErrors);die;
				$this->Flash->error(__('Duplicate entry for product code. Please, try again.'));
			}
			
		} else {
			$options = array('conditions' => array('Products.id' => $id));
            $Product_query = $this->Products->find('all', $options);
            $Product_query = $Product_query->hydrate(false);
            if(!empty($Product_query)){
                $Product = $Product_query->first();
            }else{
                $Product = array();
            }
			($this->request->data = $Product);
		}
		//$categories = $this->Product->Category->find('list',array('fields' => array('id', 'category'),
		//							   'conditions' => array('Category.status' => 1)));
		$categories_query = $this->Products->Categories->find('all',array('fields' => array('id', 'category','id_name_path'),
                                                                       'conditions' => array('Categories.status' => 1),
                                                                       'order'=>'category asc'));
        $categories_query = $categories_query->hydrate(false);
        if(!empty($categories_query)){
            $categories = $categories_query->toArray();
        }else{
            $categories = array();
        }
		$categories = $this->CustomOptions->category_options($categories);
		$brands_query = $this->Products->Brands->find('list',[
                                                            'keyField' => 'id',
                                                            'valueField' => 'brand',
                                                            'conditions' => ['Brands.status' => 1],
                                                            'order'=>'brand asc'
                                                       ]
                                                );
        $brands_query = $brands_query->hydrate(false);
        if(!empty($brands_query)){
            $brands = $brands_query->toArray();
        }else{
            $brands = array();
        }
		$brand_id = $ProductEntity->brand_id;
		$mobileModels = $this->ProductModels->find('list',array(
																				'keyField' =>'id',
																				'valueField' => 'model',
																				'conditions' => ['brand_id' => $brand_id],
																				'order'=>'model asc',												
																	  )
															)->toArray();
		if(empty($mobileModels)){
		  $mobileModels = array(-1 => "No Option Available");
		}
		
		$this->set(compact('categories', 'brands','mobileModels'));
	}

     
    public function delete($id = null)
    {
        //$this->request->allowMethod(['post', 'delete']);
        //$product = $this->Products->get($id);
        //if ($this->Products->delete($product)) {
        //    $this->Flash->success(__('The product has been deleted.'));
        //} else {
        //    $this->Flash->error(__('The product could not be deleted. Please, try again.'));
        //}
			   $this->Flash->error("Can't Delete Product");
        return $this->redirect(['action' => 'index']);
    }
    public function export(){
		 $conditionArr = array();
		 if(array_key_exists('search_kw',$this->request->query)){
			$conditionArr = $this->generate_condition_array();
  
   
		}
		if(count($conditionArr)>=1){
			$count_query = $this->Products->find('all');
			 $count = $count_query->count();
			$products_query = $this->Products->find('all',array(
																	  'conditions' => $conditionArr,
																	  'limit' => $count	
																));
		}else{
			$products_query = $this->Products->find('all');
		}
		$products_query = $products_query->hydrate(false);
			if(!empty($products_query)){
			   $products = $products_query->toArray();
			}else{
			   $products = array();
			}
		//pr($products);
		//die;
		$brands_query = $this->Brands->find('list', array(
										'keyField' => 'id',
										'valueField' => 'brand',
									));
		$brands_query = $brands_query->hydrate(false);
		if(!empty($brands_query)){
		  $brands =  $brands_query->toArray();
		}else{
		  $brands = array();
		}
		//pr($brands);
		$Categories_query = $this->Categories->find('list', array(
										'keyField' => 'id',
										'valueField' => 'category',
								));
		$Categories_query = $Categories_query->hydrate(false);
		if(!empty($Categories_query)){
		  $Categories = $Categories_query->toArray();
		}else{
		  $Categories = array();
		}
		$tmpproducts = array();
		foreach($products as $key => $product){
			unset($product['lu_cp']);
			unset($product['lu_rcp']);
			unset($product['lu_sp']);
			unset($product['lu_rsp']);
			$brandID = $product['brand_id'];
			if(array_key_exists($brandID,$brands)){
				$product['brand_id'] = $brands[$brandID];
			}else{
				$product['brand_id'] = "--";
			}
			$categoryID = $product['category_id'];
			if(array_key_exists($categoryID,$Categories)){
				$product['category_id'] = $Categories[$categoryID];
			}else{
				$product['category_id'] = "--";
			}
			$tmpproducts[] = $product;
		}
		//pr($tmpproducts);die;
		$this->outputCsv('product_'.time().".csv" ,$tmpproducts);
		$this->autoRender = false;
	}
    public function adminData($search = ""){
		$origSearch = "";
		if(array_key_exists('search',$this->request->query)){
			$origSearch = $search = trim(strtolower($this->request->query['search']));
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
        
        
        $kiosk_id = $this->request->Session()->read('kiosk_id');
		if($kiosk_id < 10000 && $kiosk_id != 0 && !empty($kiosk_id)){
			$productTable_source = "kiosk_{$kiosk_id}_products";
            
            
			//$this->Product->setSource("kiosk_{$kiosk_id}_products");
		}else{
			$productTable_source = "products";
		}
        
        $productTable = TableRegistry::get($productTable_source,[
                                                                            'table' => $productTable_source,
                                                                        ]);
        
		//---------------------------------
		$catgoryArr = array();
		if(array_key_exists('category',$this->request->query)){
			$catgoryArr = explode(",",$this->request->query['category']);
		}
		 if(!empty($search)){
			 
			ob_start();
			preg_match('/^(?>\S+\s*){1,5}/', $search, $match);
			$search = $match[0];
			$this->pc_permute(explode(' ',$search));  //split(' ', $search)
			$permutation = ob_get_clean();
			$wordArray = explode("\n", $permutation);
			$searchArray = array();
			$newCatArr = array();
			foreach($catgoryArr as $value){
				if($value == '0' || empty($value)){continue;}else{
					$newCatArr[] = $value;
				}
			}
			//if(($key = array_search('0', $catgoryArr)) !== false) {
			//	unset($catgoryArr[$key]);
			//}
			//print_r($newCatArr);
			foreach($wordArray as $value){
				if(empty($value))continue;
				$searchArray['AND']['OR'][] = "LOWER(`product`) like '%".str_replace(" ","%",$value)."%'";
				//removing 0 value from array which is for all
				
			}
			if(count($newCatArr) >= 1){
				$searchArray['AND']['category_id IN'] = $newCatArr;
			}
			//$searchArray['AND']['quantity >'] = '0';
			$productList_query = $productTable->find('all',array(
															'fields'=> array('product','product_code','quantity'),
															'recursive'=> -1,
															'conditions' => $searchArray
												)
						    );
			$productList_query->hydrate(false);
			$productList = $productList_query->toArray();
		}else{
			$productList_query = $productTable->find('all',array(
													'fields'=> array('product','product_code','quantity'),
													'conditions' => array(),
														'recursive'=>-1
											)
						    );
			$productList_query->hydrate(false);
			$productList = $productList_query->toArray();
		}
		$customProductList = array();
		foreach($productList as $productRow){
			$customProductList[] = array(
										 'product' => $productRow['product'],
										 'product_code'=> $productRow['product']."-".$productRow['product_code']." (Qty:".$productRow['quantity'].")",
										 'code' => $productRow['product_code'],

										 );
		}
		echo json_encode($customProductList);
		$this->viewBuilder()->layout(false);
		die;
	}
    function exportWarehouseProducts(){
		$hint = $this->ScreenHint->hint('products','import_kiosk_products');
        if(!$hint){
            $hint = "";
        }
		$this->set('hint',$hint);
		$kiosks_query = $this->Kiosks->find('list',
											 [
												  'keyField' => 'id',
												  'valueField' => 'name',
												  'conditions'=>['Kiosks.status'=>1],
												  'order' => 'Kiosks.name asc'									  
											 ]);
		$kiosks_query = $kiosks_query->hydrate(false);
		$kiosks = $kiosks_query->toArray();
		$this->set(compact('kiosks'));
	}
	
	public function exportKioskProducts(){
	 
		$kiosks_query = $this->Kiosks->find('list',
											 [
												  'keyField' => 'id',
												  'valueField' => 'name',
												  'conditions'=>['Kiosks.status'=>1],
												  'order' => 'Kiosks.name asc'
											 ]);
		$kiosks_query = $kiosks_query->hydrate(false);
		$kiosks = $kiosks_query->toArray();
		$hint = $this->ScreenHint->hint('products','import_kiosk_products');
        if(!$hint){
            $hint = "";
        }
		$this->set('hint',$hint);
		$this->set(compact('kiosks'));
		$this->set('count_offer_notice',0);
		if($this->request->is('post')){
		  
			//get categories
			$categories_query = $this->Categories->find('list',
															[
																 'keyField' => 'id',
																 'valueField' => 'category',
															]);
			$categories_query = $categories_query->hydrate(false);
			$categories = $categories_query->toArray();
			
			$msg = '';
			$kiosk_id = $this->request->data['Product']['kiosk_id'];
			$productSource = "kiosk_{$kiosk_id}_products";
			$productTable = TableRegistry::get($productSource,[
                                                                                    'table' => $productSource,
                                                                                ]);
			$products_query = $productTable->find('all');
			$products_query = $products_query->hydrate(false);
			$products = $products_query->toArray();
			$tmpproducts = array(); //pr($products);die;
			
			$sites = Configure::read('sites');
			$path = dirname(__FILE__);
			$isboloRam = false;
			
			
			foreach($sites as $site_key => $site_val){
			   $isboloRam = strpos($path,$site_val);
			   if($isboloRam == true){
					break;
			   }
			}
			
			
			
			$fileName = 'product_'.time().'.csv';
			header('Content-Type: application/csv');
			header('Content-Disposition: attachment;filename=' . $fileName);
			$fp = fopen('php://output', 'w');
			$counter = 0;
			
			
				
				
			foreach($products as $key => $product){
			   
				
				if($isboloRam != false){
					unset($product['cost_price']);	
				}
				unset($product['retail_cost_price']);
				if( array_key_exists($product['category_id'], $categories) ){
					$product['category_name'] = $categories[$product['category_id']];
				}else{
					$product['category_name'] = "";
				}
				//pr($product['Product']);unset($products[$key]);continue;
				if($counter == 0){
					;
					fputcsv($fp, array_keys($product));
				}else{
					;
					fputcsv($fp, $product);
				}
				//$tmpproducts[] = $product['Product'];
				unset($products[$key]);
				$counter++;
			}
			fclose($fp);
			//$this->outputCsv('product_'.time().".csv" ,$tmpproducts);
			
			$this->autoRender = false;
			//$this->redirect(array('action' => 'export_kiosk_products'));
			die();
			
		}
	}
	
	public function update_boloram($boloramData,$site_value){
	 
	 //pr($boloramData);die;
	 $new_data = $boloramData;
	  $external_sites = Configure::read('EXT_RETAIL');
	  $external_site_status = 0;
	  if(in_array($site_value,$external_sites)){
		  $external_site_status = 1;
	  }
	  
	  $disnt_for_sites = Configure::read('disnt_for_diff_sites');
	  $disnt_for_diff_sites = 0;
	  if(!empty($disnt_for_sites)){
		  foreach($disnt_for_sites as $dis_key => $dis_value){
			   if($dis_value == $site_value){
					$disnt_for_diff_sites = 1;
			   }
		  }
	  }
	  
	  
	 //pr($boloramData);die;
		if(!array_key_exists("Product",$boloramData)){
			return;
		}
		if(array_key_exists("quantity",$boloramData["Product"])){
			unset($boloramData["Product"]["quantity"]);
		}
        
		//-----------5 retail fields------------
		if(array_key_exists("retail_cost_price",$boloramData["Product"])){
			$boloramData["Product"]["cost_price"] = $boloramData["Product"]["retail_cost_price"];
			if($external_site_status == 1){
			   $boloramData["Product"]["cost_price"] = $boloramData["Product"]["selling_price"]/1.2;
			   $boloramData["Product"]["retail_cost_price"] = $boloramData["Product"]["selling_price"]/1.2;
			}
			//unset($boloramData["Product"]["retail_cost_price"]);
		}
		if(array_key_exists("retail_selling_price",$boloramData["Product"])){
		  if($external_site_status == 1){
					//$without_vat = (($boloramData["Product"]["selling_price"]/1.2));
					//$boloramData["Product"]["selling_price"] = $without_vat + ($without_vat*(10/100));
			   $boloramData["Product"]["selling_price"] = $boloramData["Product"]["retail_selling_price"];   
			}else{
			   $boloramData["Product"]["selling_price"] = $boloramData["Product"]["retail_selling_price"];   
			}
			
			//unset($boloramData["Product"]["retail_selling_price"]);
		}
		if(array_key_exists("retail_discount",$boloramData["Product"])){
			$boloramData["Product"]["discount"] = $boloramData["Product"]["retail_discount"];
			if($disnt_for_diff_sites == 1){
			   $boloramData["Product"]["discount"] = 50;
			}
			unset($boloramData["Product"]["retail_discount"]);
		}
		if(array_key_exists("rt_discount_status",$boloramData["Product"])){
			$boloramData["Product"]["discount_status"] = $boloramData["Product"]["rt_discount_status"];
			if($disnt_for_diff_sites == 1){
			   $boloramData["Product"]["discount_status"] = 1;
			}
			unset($boloramData["Product"]["rt_discount_status"]);
		}
		if(array_key_exists("retail_special_offer",$boloramData["Product"])){
			$boloramData["Product"]["special_offer"] = $boloramData["Product"]["retail_special_offer"];
			unset($boloramData["Product"]["retail_special_offer"]);
		}
		///-----------5 retail fields------------
		if(array_key_exists("remove",$boloramData["Product"])){
			unset($boloramData["Product"]["remove"]);
		}
		if(array_key_exists("image",$boloramData["Product"])){
		//  $image_data = $boloramData["Product"]["image"];
		if(is_array($boloramData["Product"]["image"])){
		  unset($boloramData["Product"]["image"]);
		}
		  // if(array_key_exists('name',$image_data)){
		//	$boloramData["Product"]["image"] = $image_data['name'];
		  // }
		}
		if(array_key_exists("image_dir",$boloramData["Product"])){
			unset($boloramData["Product"]["image_dir"]);
		}
		
		$product_code = "";
		if(array_key_exists("product_code",$boloramData["Product"])){
			$product_code = $boloramData["Product"]["product_code"];
			//unset($boloramData["Product"]["product_code"]);
		}
		//pr($boloramData);die;
		 $id = $boloramData["Product"]['id'];
		
		$connection = ConnectionManager::get($site_value);
	    $stmt = $connection->execute("SELECT id,selling_price,cost_price FROM products where id = $id");
	    $checkId = $stmt ->fetchAll('assoc');
		
		
		if(count($checkId)>0){
		  if(array_key_exists('cost_price',$boloramData["Product"])){
			 if($checkId[0]["cost_price"] !=$boloramData["Product"]["cost_price"]){
			   $boloramData["Product"]["lu_cp"] = date("Y-m-d H:i:s");
			   }  
		  }
		  if(array_key_exists("selling_price",$boloramData["Product"])){
			   if($checkId[0]["selling_price"] != $boloramData["Product"]["selling_price"]){
				  $boloramData["Product"]["lu_sp"] = date("Y-m-d H:i:s");
			   }   
		  }
			//pr($boloramData);die;
			if(array_key_exists('manufacturing_date',$boloramData['Product']) && is_array($boloramData['Product']['manufacturing_date'])){
				if(array_key_exists('year',$boloramData['Product']['manufacturing_date'])){
					$year = $boloramData['Product']['manufacturing_date']['year'];	
				}
				if(array_key_exists('month',$boloramData['Product']['manufacturing_date'])){
					$month = $boloramData['Product']['manufacturing_date']['month'];	
				}
				if(array_key_exists('day',$boloramData['Product']['manufacturing_date'])){
					$day = $boloramData['Product']['manufacturing_date']['day'];	
				}
			   //$month = $boloramData['Product']['manufacturing_date']['month'];
			   //$day = $boloramData['Product']['manufacturing_date']['day'];
				if(isset($year) && isset($month) && isset($day)){
					$date = $year."-".$month."-".$day;
				}else{
					$date = "";
				}
			   $boloramData['Product']['manufacturing_date'] = $date;
			}
			unset($boloramData['Product']['null']);
          //  unset($boloramData['Product']['product_code']);
          //  pr($boloramData['Product']);die;
//			echo "dd";die;
	
			$res = $connection->update('products',$boloramData['Product'] ,['id' => $id]);
			unset($boloramData["Product"]["lu_cp"]);
			unset($boloramData["Product"]["lu_sp"]);
		}else{
		// if(array_key_exists("remove",$new_data['Product'])){
		//	  unset($new_data['Product']['remove']); 
		//  }
		  //pr($new_data);die;
		  //if($external_site_status != 1){
			   //$this->add_on_boloram($new_data,$site_value);
		 // }
		}
		
		if(array_key_exists('location',$boloramData["Product"])){
		  unset($boloramData["Product"]['location']);
		}
		
		
		$connection = ConnectionManager::get($site_value);
	    $stmt = $connection->execute("SELECT id,name FROM kiosks");
	    $kiosks_data = $stmt ->fetchAll('assoc');
		$kiosks = array();
		foreach($kiosks_data as $key => $value){
		  $kiosks[$value['id']] = $value['name'];
		}
		
		
        unset($boloramData['Product']['special_offer']);
        unset($boloramData['Product']['retail_special_offer']);
		unset($boloramData['Product']['qty_update_status']);
		unset($boloramData['Product']['qty_update_time']);
		unset($boloramData['Product']['back_stock_status']);
		unset($boloramData['Product']['festival_offer']);
		//echo "ll";die;
		
		foreach($kiosks as $kiosk_id => $name){
			$table =  "kiosk_{$kiosk_id}_products";
			//$this->Product->setSource($table);//'Product.product_code' => $product_code
			
		  $stmt = $connection->execute("SELECT id FROM $table where id = $id");
		  $result = $stmt ->fetchAll('assoc'); 
			if(count($result)>0){
			   $res = $connection->update($table,$boloramData['Product'] ,['id' => $id]);
				//$this->Product->save($boloramData);
			}

		}
	}
    
	public function update_other_sites($boloramData,$site_value){
	 
	 //pr($boloramData);die;
	 $new_data = $boloramData;
	  $external_sites = Configure::read('EXT_RETAIL');
	  $external_site_status = 0;
	  if(in_array($site_value,$external_sites)){
		  $external_site_status = 1;
	  }
	  
	  $disnt_for_sites = Configure::read('disnt_for_diff_sites');
	  $disnt_for_diff_sites = 0;
	  if(!empty($disnt_for_sites)){
		  foreach($disnt_for_sites as $dis_key => $dis_value){
			   if($dis_value == $site_value){
					$disnt_for_diff_sites = 1;
			   }
		  }
	  }
	  
	  
	 //pr($boloramData);die;
		if(!array_key_exists("Product",$boloramData)){
			return;
		}
		if(array_key_exists("quantity",$boloramData["Product"])){
			unset($boloramData["Product"]["quantity"]);
		}
        $id = $boloramData["Product"]["id"];
		
		
		
		//-----------5 retail fields------------
		if(array_key_exists("retail_cost_price",$boloramData["Product"])){
			$boloramData["Product"]["cost_price"] = $boloramData["Product"]["retail_cost_price"];
			if($external_site_status == 1){
			   unset($boloramData["Product"]["cost_price"]);
			   //$boloramData["Product"]["cost_price"] = $boloramData["Product"]["selling_price"]/1.2;
			   //$boloramData["Product"]["retail_cost_price"] = $boloramData["Product"]["selling_price"]/1.2;
			}
		}
		
		if(array_key_exists("selling_price",$boloramData["Product"])){
		  if($external_site_status == 1){
			   $boloramData["Product"]["cost_price"] = $boloramData["Product"]["selling_price"]/1.2;
			}
			unset($boloramData["Product"]["selling_price"]);
		}
		
		
		if(array_key_exists("retail_selling_price",$boloramData["Product"])){
		  if($external_site_status == 1){
			   $boloramData["Product"]["selling_price"] = $boloramData["Product"]["retail_selling_price"];   
			}else{
			   $boloramData["Product"]["selling_price"] = $boloramData["Product"]["retail_selling_price"];   
			}
		}
		if(array_key_exists("retail_discount",$boloramData["Product"])){
			$boloramData["Product"]["discount"] = $boloramData["Product"]["retail_discount"];
			if($disnt_for_diff_sites == 1){
			   $boloramData["Product"]["discount"] = 50;
			}
			unset($boloramData["Product"]["retail_discount"]);
		}
		if(array_key_exists("rt_discount_status",$boloramData["Product"])){
			$boloramData["Product"]["discount_status"] = $boloramData["Product"]["rt_discount_status"];
			if($disnt_for_diff_sites == 1){
			   $boloramData["Product"]["discount_status"] = 1;
			}
			unset($boloramData["Product"]["rt_discount_status"]);
		}
		if(array_key_exists("retail_special_offer",$boloramData["Product"])){
			$boloramData["Product"]["special_offer"] = $boloramData["Product"]["retail_special_offer"];
			unset($boloramData["Product"]["retail_special_offer"]);
		}
		///-----------5 retail fields------------
		if(array_key_exists("remove",$boloramData["Product"])){
			unset($boloramData["Product"]["remove"]);
		}
		if(array_key_exists("image",$boloramData["Product"])){
		
		  if(is_array($boloramData["Product"]["image"])){
			unset($boloramData["Product"]["image"]);
		  }
		}
		if(array_key_exists("image_dir",$boloramData["Product"])){
			unset($boloramData["Product"]["image_dir"]);
		}
		
		$product_code = "";
		if(array_key_exists("product_code",$boloramData["Product"])){
			$product_code = $boloramData["Product"]["product_code"];
		}
		
		 $id = $boloramData["Product"]['id'];
		
		$connection = ConnectionManager::get($site_value);
	    $stmt = $connection->execute("SELECT id,selling_price,cost_price FROM products where id = $id");
	    $checkId = $stmt ->fetchAll('assoc');
		
		
		if(count($checkId)>0){
		  if(array_key_exists('cost_price',$boloramData["Product"])){
			 if($checkId[0]["cost_price"] !=$boloramData["Product"]["cost_price"]){
			   $boloramData["Product"]["lu_cp"] = date("Y-m-d H:i:s");
			   }  
		  }
		  if(array_key_exists("selling_price",$boloramData["Product"])){
			   if($checkId[0]["selling_price"] != $boloramData["Product"]["selling_price"]){
				  $boloramData["Product"]["lu_sp"] = date("Y-m-d H:i:s");
			   }   
		  }
			
			if(array_key_exists('manufacturing_date',$boloramData['Product']) && is_array($boloramData['Product']['manufacturing_date'])){
				if(array_key_exists('year',$boloramData['Product']['manufacturing_date'])){
					$year = $boloramData['Product']['manufacturing_date']['year'];	
				}
				if(array_key_exists('month',$boloramData['Product']['manufacturing_date'])){
					$month = $boloramData['Product']['manufacturing_date']['month'];	
				}
				if(array_key_exists('day',$boloramData['Product']['manufacturing_date'])){
					$day = $boloramData['Product']['manufacturing_date']['day'];	
				}
			   
				if(isset($year) && isset($month) && isset($day)){
					$date = $year."-".$month."-".$day;
				}else{
					$date = "";
				}
			   $boloramData['Product']['manufacturing_date'] = $date;
			}
			unset($boloramData['Product']['null']);
          
	
			$res = $connection->update('products',$boloramData['Product'] ,['id' => $id]);
			unset($boloramData["Product"]["lu_cp"]);
			unset($boloramData["Product"]["lu_sp"]);
		}else{
		
		}
		
		if(array_key_exists('location',$boloramData["Product"])){
		  unset($boloramData["Product"]['location']);
		}
		
		
		$connection = ConnectionManager::get($site_value);
	    $stmt = $connection->execute("SELECT id,name FROM kiosks");
	    $kiosks_data = $stmt ->fetchAll('assoc');
		$kiosks = array();
		foreach($kiosks_data as $key => $value){
		  $kiosks[$value['id']] = $value['name'];
		}
		
		
        unset($boloramData['Product']['special_offer']);
        unset($boloramData['Product']['retail_special_offer']);
		unset($boloramData['Product']['qty_update_status']);
		unset($boloramData['Product']['qty_update_time']);
		unset($boloramData['Product']['back_stock_status']);
		unset($boloramData['Product']['festival_offer']);
		//echo "ll";die;
		
		foreach($kiosks as $kiosk_id => $name){
			   $table =  "kiosk_{$kiosk_id}_products";
			   $stmt = $connection->execute("SELECT id FROM $table where id = $id");
			   $result = $stmt ->fetchAll('assoc'); 
			   if(count($result)>0){
				  $res = $connection->update($table,$boloramData['Product'] ,['id' => $id]);
				   
			   }
		}
	}
	
    public function importProducts2Db(){
	 ini_set('memory_limit','1024M');
	 set_time_limit ( 0 );
	 $this->loadModel('Settings');
		$query = "DESCRIBE products";
        $conn = ConnectionManager::get('default');
        $stmt = $conn->execute($query);
        $schema = $stmt ->fetchAll('assoc');
        foreach($schema as $keys => $feilds){
            $productFields[] = $feilds['Field'];
        }
        //pr($feild);die;
        //pr($currentTimeInfo);die;
		//$productFields = array_keys($this->Products->schema());
		//pr($productFields);die;
		$queriesWaheguru = $queriesFired = array();
		$valus2exclude = $this->valuesToExclude;
		$productFields = array_diff($productFields, $valus2exclude);
		
		$hint = $this->ScreenHint->hint('products','import_products');
        if(!$hint){
            $hint = "";
        }
        
        $res_query = $this->CsvProducts->find('all');
            $res_query
                        ->select(['id' => $res_query->func()->MAX('id')])
                        ->select('created');
        //pr($res_query);die;
        $res_query = $res_query->hydrate(false);
        if(!empty($res_query)){
            $res = $res_query->toArray();
        }else{
            $res = array();
        }
        //pr($res);die;
		if(!empty($res)){
			//$res[0][0]['id'];
			$last_updated = "";
			$last_updated = $res[0]['created'];
			$this->set(compact('last_updated'));
		}
		$this->set('hint',$hint);
		$this->set('productFields',$productFields);
		if(isset($last_updated) && !empty($last_updated)){
		 $set_res_query = $this->Settings->find('all',array('conditions' => array(
																			 'attribute_name' => 'allowed_array'
																			 )));
		  $set_res_query = $set_res_query->hydrate(false);
		  if(!empty($set_res_query)){
			   $set_res = $set_res_query->first();
		  }else{
			   $set_res = array();
		  }
		  $this->set('selected_fields',$set_res['attribute_value']);
		}
		
		if($this->request->is('post')){
			if(array_key_exists('truncate',$this->request->data)){
                $conn = ConnectionManager::get('default');
                $stmt = $conn->execute('TRUNCATE `csv_products`');
				if($stmt){   
					$this->Flash->success("Table truncated");
					return $this->redirect(array('action' => 'import_products_2_db'));
				}
			}
			if(array_key_exists('import_table',$this->request->data)){
			   
			   $set_res_query = $this->Settings->find('all',array('conditions' => array(
																			 'attribute_name' => 'allowed_array'
																			 )));
			   $set_res_query = $set_res_query->hydrate(false);
			   if(!empty($set_res_query)){
					$set_res = $set_res_query->first();
			   }else{
					$set_res = array();
			   }
			   $all_arr_s =  explode(",",$set_res['attribute_value']);
				$res_retured = $this->import($all_arr_s);
				if(!empty($res_retured)){
					return($res_retured);
				}
				die;
			}
			
			if(!array_key_exists('import', $this->request->data["Product"])){
				$this->Flash->error("Please choose some fields or all fields for import!");
				return $this->redirect(array('action' => 'import_products_2_db'));
			}
			$allowed = $this->request->data["Product"]["import"];
			foreach($allowed as $key => $value){
				if(array_key_exists($value,$productFields)){
					$allowed_array[$key] = $productFields[$value];
				}
			}
			
			$msg = '';
			if(!array_key_exists('import_data', $this->request->data["Product"])){
				
				if(empty($this->request->data['Product']['import_data']['tmp_name'])){
					$this->Flash->error("Please choose filename!");
					return $this->redirect(array('action' => 'import_products_2_db'));
				}
				
			}
			if(empty($this->request->data['Product']['import_data']['error'])){
			   
			}else{
			   //echo phpinfo();die;
			   $this->Flash->error("Some Error In File Upload.!");
			   return $this->redirect(array('action' => 'import_products_2_db'));
			}
			$fileName = $this->request->data['Product']['import_data']['tmp_name'];
			$file = fopen($fileName,"r");
			
			$count = 0;
			$finalProductArr = $productIds = $errors = array();
			
			while(! feof($file)){
				$count++;
				if($count == 1){
					$headingsArr = fgetcsv($file);
					 $countHeadings = count($headingsArr);//for comparing the number of headings with corresponding data in else case
					//below we are checking if the required field names input by user are correct
					//below will output an array with the name of field that is in $productFields and not in $headingsArr
					//pr($productFields);
					//pr($headingsArr);
					$validateFieldArr = array_diff($productFields, $headingsArr);
					//pr($validateFieldArr);die;
					if(count($validateFieldArr)){
					  $msg = "Please add correct headings for ".implode(', ',$validateFieldArr);
					  $this->Flash->error($msg);
					  return $this->redirect(array('import_products_2_db'));
					}
					
				}else{
					$inputFieldArr = fgetcsv($file);
					if($countHeadings == count($inputFieldArr)){
						$productArray = array();	 
						foreach($headingsArr as $key => $heading){
							if(in_array($heading, $productFields)){
								if($heading == 'id' && is_numeric($inputFieldArr[$key]) == false){
									break;
								}else{
									if($heading == 'id'){
										$productIds[] = $inputFieldArr[$key];
									}
									$productArray['Product'][$heading] = $inputFieldArr[$key];
								}
							}
						}
						foreach($productArray as $key1){
							foreach($key1 as $name => $value1){
								if(!in_array($name,$allowed_array) || $value1 == ""){
									if($name == "id"){
										continue;
									}
									unset($productArray["Product"][$name]);
								}
							}
						}
						
						if(array_key_exists("Product",$productArray)){
							  $productID = $productArray['Product']['id'];
							  $getID = $this->Products->get($productID);
							  $patchEntity = $this->Products->patchEntity($getID,$productArray);
							   
							  if (!empty($patchEntity->errors())) {
								  foreach($patchEntity->errors() as $key){
										 foreach($key as $value){
											$errors[] = $value;  
										 }
									}
								  $errors[$productArray['Product']['id']] = implode("</br>",$errors);
								   
							  }else{
								  //saving the product array in a new array
								  
								  $finalProductArr[] = $productArray;
							  } 
						}
					}
				}
			}
			if(count($errors)){
				$errorArr = array();
				foreach($errors as $productId => $error){
					foreach($error as $key => $error_str){
						$errorArr[] = "Product Id ".$productId.": ".$error_str['0'];
					}
				}
				$errorStr = implode("<br/>", $errorArr);
				$this->Flash->error($errorStr);
				return $this->redirect(array('action' => 'import_products_2_db'));
			}else{
				$error_count = $count = 0;
				if(count($finalProductArr)){
					foreach($finalProductArr as $p_key => $p_value){
						if(empty($p_value)){
							continue;
						}
						$randum_no = rand(10,100000000);
						$timestamp = strtotime(date('Y-m-d H:i:s:u'));
						$randum_no1 = rand(90,990000000);
						$number = $randum_no.$timestamp.$randum_no1;
						$p_value['Product']['product_code'] = $number;
						$p_value['Product']['created'] = date('Y-m-d H:i:s');
						$new_entity = $this->CsvProducts->newEntity($p_value['Product'],array('accessibleFields' => ['id' => true]));
						$patch_entity = $this->CsvProducts->patchEntity($new_entity,$p_value['Product'],['validates' => false]);
						if($this->CsvProducts->save($patch_entity)){	
							$count++;
						}else{
							$error_product[] = $p_value['Product']['id'];
							$error_count ++;
						}
					}
				}else{
					$this->Flash->error("Products could not be save. Please try again!");
					return $this->redirect(array('action' => 'import_products_2_db'));
				}
			}
			if($error_count > 0){
				$er_res = "";
				if(!empty($error_product)){
					$er_res = implode(",",$error_product);
				}
				$msg = "Products could not be save. Please try again for ".$er_res;
				$this->Flash->error($msg);
				return $this->redirect(array('action' => 'import_products_2_db'));
			}else{
				if($count > 0){
					$set_res_query = $this->Settings->find('all',array('conditions' => array(
																			 'attribute_name' => 'allowed_array'
																			 )));
					$set_res_query = $set_res_query->hydrate(false);
					if(!empty($set_res_query)){
						 $set_res = $set_res_query->first();
					}else{
						 $set_res = array();
					}
					if(!empty($set_res)){
						 $setting_id = $set_res['id'];
					}
					$data_s = implode(",",$allowed_array);
					$data_to_save = array('attribute_value' => $data_s);
					
					$set_res = $this->Settings->get($setting_id);
					$set_res = $this->Settings->patchEntity($set_res,$data_to_save,['validate' => false]);
					$this->Settings->save($set_res);
					
					$this->Flash->success("Product have been saved!");
					return $this->redirect(array('action' => 'import_products_2_db'));
				}else{
					$this->Flash->error('Products could not be save. Please try again !');
				}
			}
		}
		
	}
    
    public function importProducts(){
		ini_set('max_execution_time', 3000);
        $query = "DESCRIBE products";
        $conn = ConnectionManager::get('default');
        $stmt = $conn->execute($query);
        $schema = $stmt ->fetchAll('assoc');
        foreach($schema as $keys => $feilds){
            $productFields[] = $feilds['Field'];
        }
		//pr($productFields);die;
		$queriesWaheguru = $queriesFired = array();
		$valus2exclude = $this->valuesToExclude;
		$productFields = array_diff($productFields, $valus2exclude);
		
		$hint = $this->ScreenHint->hint('products','import_products');
        if(!$hint){
            $hint = "";
        }
        $res_query = $this->CsvProducts->find('all');
            $res_query
                        ->select(['id' => $res_query->func()->MAX('id')])
                        ->select('created');
        //pr($res_query);die;
        $res_query = $res_query->hydrate(false);
        if(!empty($res_query)){
            $res = $res_query->toArray();
        }else{
            $res = array();
        }
		//pr($res);die;
		if(!empty($res)){
			//$res[0][0]['id'];
			$last_updated = "";
			$last_updated = $res[0]['created'];
			$this->set(compact('last_updated'));
		}
		$this->set('hint',$hint);
		$this->set('productFields',$productFields);
		if($this->request->is('post')){
		  //pr($this->request);die;
			if(array_key_exists('truncate',$this->request->data)){
                $conn = ConnectionManager::get('default');
                $stmt = $conn->execute('TRUNCATE `csv_products`');
				if($stmt){
					$this->Flash->success("Table truncated");
					return $this->redirect(array('action' => 'import_products'));
				}
			}
			if(!array_key_exists('import', $this->request->data["Product"])){
				$this->Flash->error("Please choose some fields or all fields for import!");
				return $this->redirect(array('action' => 'import_products'));
			}
			$allowed = $this->request->data["Product"]["import"];
			$allowed_array = array();
			
			foreach($allowed as $key => $value){
				if(array_key_exists($value,$productFields)){
					$allowed_array[$key] = $productFields[$value];
				}
			}
			//if(!empty($allowed_array)){
			//   $allowed_array = array_merge($allowed_array,array("selling_price","retail_selling_price","cost_price","retail_cost_price"));
			//}else{
			//   $allowed_array = array("selling_price","retail_selling_price","cost_price","retail_cost_price");   
			//}
			
			if(array_key_exists('import_table',$this->request->data)){
				$this->import($allowed_array);die;
			}
			$msg = '';
			if(!array_key_exists('import_data', $this->request->data["Product"])){
				$this->Flash->error("Please choose filename!");
				return $this->redirect(array('action' => 'import_products'));
			}
			
			$fileName = $this->request->data['Product']['import_data']['tmp_name'];
			$file = fopen($fileName,"r");
			$count = 0;
			$finalProductArr = $productIds = $errors = array();
			
			while(! feof($file)){
				$count++;
				if($count == 1){
					$headingsArr = fgetcsv($file);
					$countHeadings = count($headingsArr);//for comparing the number of headings with corresponding data in else case
					//below we are checking if the required field names input by user are correct
					//below will output an array with the name of field that is in $productFields and not in $headingsArr
					if(!in_array("id",$headingsArr)){
						   $msg = "Id Field is missing";
						   $this->Flash->error($msg);
						   return $this->redirect(array('import_products'));
					}
					foreach($headingsArr as $k => $v){
						if(!in_array($v,$productFields)){
							  unset($headingsArr[$k]);
						 }
					}
				}else{
					$inputFieldArr = fgetcsv($file);
					
					//filtering the incorrect arrays that do not have proper structure like headings array
					//example: an array with only 0 key was created during testing, so not considering such arrays for update in db table
					if($countHeadings == count($inputFieldArr)){
						$productArray = array();
						foreach($headingsArr as $key => $heading){
							if(in_array($heading, $productFields)){
								if($heading == 'id' && is_numeric($inputFieldArr[$key]) == false){
									break;
								}else{
									if($heading == 'id'){
										$productIds[] = $inputFieldArr[$key];
									}
									$productArray['Product'][$heading] = $inputFieldArr[$key];
								}
							}
						}
						
						foreach($productArray as $key1){
							foreach($key1 as $name => $value1){
								if(!in_array($name,$allowed_array) || $value1 == ""){
									if($name == "id"){
										continue;
									}
									unset($productArray["Product"][$name]);
								}
							}
						}
						//pr($productArray['Product']);die;
						//model validation
						$entity = $this->Products->get($productArray['Product']['id']);
						$entity = $this->Products->patchEntity($entity,$productArray['Product']);
						if (!empty($entity->errors())) {
							  foreach($entity->errors() as $key){
								   foreach($key as $value){
									  $errors[] = $value;  
								   }
							  }
							  //pr($productArray['Product']);
							$errors[$productArray['Product']['id']] = implode("</br>",$errors);
						}else{
							//saving the product array in a new array
							$finalProductArr[] = $productArray;
						}
					}
				}
			}
			
			//sleep(20);
			if(count($errors)){
				$errorArr = array();
				if(array_key_exists(0,$errors)){
					unset($errors[0]);
				}
				foreach($errors as $productId => $error){
					if(is_array($error)){
						foreach($error as $key => $error_str){
							  $errorArr[] = "Product Id ".$productId.": ".$error_str['0'];
						 } 
					}else{
						 $errorArr[] = "Product Id ".$productId.": ".$error;
					}
					
				}
				$errorStr = implode("<br/>", $errorArr);
				$this->Flash->error($errorStr,['escape' => false]);
				return $this->redirect(array('import_products'));
			}else{
				$saveCount = 0;
			 	if(count($finalProductArr)){
					if(empty($productIds)){
                        $productIds = array(0 => null);
                    }
                    $lastproducts_query = $this->Products->find('list',[
                                                                    'conditions'=>['Products.id IN'=> $productIds],
                                                                    'keyField' => 'id',
                                                                    'valueField' => 'selling_price',
                                                                    'order' => ['Products.lu_sp desc']
                                                                ]
                                                        );
                    $lastproducts_query = $lastproducts_query->hydrate(false);
                    if(!empty($lastproducts_query)){
                        $lastproducts = $lastproducts_query->toArray();
                    }else{
                        $lastproducts = array();
                    }
					//$product_code = "";
					//pr($finalProductArr);die;
					$productExistingInWaheguru = array();
					foreach($finalProductArr as $k => $finalProducts){
						//pr($finalProducts);die;
						if(!array_key_exists('Product',$finalProducts)){
							continue;
						}
						$id =  $finalProducts['Product']['id'];
						//pr($finalProducts);continue;
						if(array_key_exists('selling_price', $finalProducts['Product'])){
							$selling_price = $finalProducts['Product']['selling_price'] ;
							if($selling_price != $lastproducts[$id]){
								$finalProducts['Product']['lu_sp'] = date("Y-m-d H:i:s");
							}else{
								unset($finalProducts['Product']['lu_sp']);
							}
						}
						if(array_key_exists('product_code', $finalProducts['Product'])){
							unset($finalProducts['Product']['product_code']);
						}
						//pr($finalProducts);die;
						//$this->Product->setDataSource('default');
                        $products_source = "products";
                        $productsTable = TableRegistry::get($products_source,[
                                                                                'table' => $products_source,
                                                                            ]);
						//$this->Product->setSource("products");
						//For ADMIN_DOMAIN
						$result_query = $productsTable->find('all',array(
																	 'conditions' => array('id' => $id),
																	 'fields' => array('id')
																	 )
													   );
                        $result_query = $result_query->hydrate(false);
                        if(!empty($result_query)){
                            $result = $result_query->first();
                        }else{
                            $result = array();
                        }
						$finalProducts['Product']["modified_by"] = $this->request->session()->read('Auth.User.id');
						//echo $id;echo count($result);
						if(count($result) > 0){
							$productExistingInWaheguru[$id] = $id;
							$get_id = $productsTable->get($id);
                            $patchEntity = $productsTable->patchEntity($get_id,$finalProducts['Product'],['validates' => false]);
							
							if($productsTable->save($patchEntity)){ //update
								
								if(array_key_exists("quantity",$finalProducts["Product"])){
									unset($finalProducts["Product"]["quantity"]);
								}
								if(array_key_exists("stock_level",$finalProducts["Product"])){
									unset($finalProducts["Product"]["stock_level"]);
								}
								if(array_key_exists("cost_price",$finalProducts["Product"])){
									unset($finalProducts["Product"]["cost_price"]);
								}
								
								if(array_key_exists("modified_by",$finalProducts["Product"])){
									unset($finalProducts["Product"]["modified_by"]);
								}
								//$this->Kiosk->setDataSource('default');	//$this->Kiosk->setSource("products");
								$kiosks_query = $this->Kiosks->find('list');
                                $kiosks_query = $kiosks_query->hydrate(false);
                                if(!empty($kiosks_query)){
                                    $kiosks = $kiosks_query->toArray();
                                }else{
                                    $kiosks = array();
                                }
								//waheguru kiosks
								foreach($kiosks as $kiosk_id => $kiosk){
									$table =  "kiosk_{$kiosk_id}_products";
                                    $ProductTable = TableRegistry::get($table,[
                                                                                    'table' => $table,
                                                                                ]);
									//$this->Product->setSource($table);
									$result1_query = $this->Products->find('all',array(
																				  'conditions' => array('Products.id' => $id),
																				  'fields' => array('id'),
																				  )
																	);
                                    $result1_query = $result1_query->hydrate(false);
                                    if(!empty($result1_query)){
                                        $result1 = $result1_query->first();
                                    }else{
                                        $result1 = array();
                                    }
									if(count($result1) > 0){
										//Saving product for ADMIN_DOMAIN kiosk if existing
										$g_id = $productsTable->get($finalProducts["Product"]["id"]);
                                        $EntityPatch = $productsTable->patchEntity($g_id,$finalProducts["Product"],['validates' => false]);
										$productsTable->save($EntityPatch);//update
									}
								}
								$saveCount++;
							}
						}
					}
					$sites = Configure::read('sites');
					if(!empty($sites)){
						 foreach($sites as $site_id => $site_value){
							  $this->update_other_sites($finalProducts,$site_value);	  
						 }
					}
					//$this->save_boloram_products($finalProductArr,$lastproducts, $productExistingInWaheguru);
				}
			}
			//die;
			
			if($saveCount > 0){
				//echo implode("<br/>", $queriesWaheguru);
				//die;
				$this->Flash->success("$saveCount rows have been updated!");
			}else{
				$this->Flash->error("Products could not be updated. Please try again!");
			}
			return $this->redirect(array('action' => 'import_products'));
		}
	}
    
    private function save_boloram_products($finalProductArr, $lastproducts, $productExistingInWaheguru){
		$saveCount = 0;
		$queriesBoloram = array();
		$this->Product->setDataSource('boloram_db');
		$this->Kiosk->setDataSource('boloram_db');//pr($productExistingInWaheguru);
		//Run loop for boloram products
		foreach($finalProductArr as $k => $finalProducts){
			if(!array_key_exists('Product',$finalProducts)){
				continue;
			}
			$id =  $finalProducts['Product']['id'];
			
			if(array_key_exists('selling_price', $finalProducts['Product'])){
				$selling_price = $finalProducts['Product']['selling_price'] ;
				if($selling_price != $lastproducts[$id]){
					$finalProducts['Product']['lu_sp'] = date("Y-m-d H:i:s");
				}else{
					unset($finalProducts['Product']['lu_sp']);
				}
			}
			
			if(array_key_exists('product_code', $finalProducts['Product'])){
				unset($finalProducts['Product']['product_code']);
			}
			
			if(in_array($id,$productExistingInWaheguru)){//pr($finalProducts);
				//Note: if product $id existing in ADMIN_DOMAIN database only than save products in boloram
				$this->Product->setSource("products");
				unset($finalProducts['Product']['quantity']);
				//On Inder's request we have unset quantity to save it in boloram server.
				
				if($this->Product->save($finalProducts)){ //update
					$dbo = $this->Product->getDatasource();
					$logData = $dbo->getLog();
					$getLog = end($logData['log']);
					//echo "<br/>Line #1026:".$getLog['query'];
					$queriesBoloram[] = $getLog['query'];//$queriesFired[] = 
					//Saving data for ADMIN_DOMAIN
					if(array_key_exists("quantity",$finalProducts["Product"])){
						unset($finalProducts["Product"]["quantity"]);
					}
					if(array_key_exists("stock_level",$finalProducts["Product"])){
						unset($finalProducts["Product"]["stock_level"]);
					}
					if(array_key_exists("cost_price",$finalProducts["Product"])){
						unset($finalProducts["Product"]["cost_price"]);
					}
					
					
					//Note: Code below is for boloram
					if(array_key_exists("retail_cost_price",$finalProducts["Product"])){
						$finalProducts["Product"]["cost_price"] = $finalProducts["Product"]["retail_cost_price"];
						unset($finalProducts["Product"]["retail_cost_price"]);
					}
					if(array_key_exists("retail_selling_price",$finalProducts["Product"])){
						$finalProducts["Product"]["selling_price"] = $finalProducts["Product"]["retail_selling_price"];
						unset($finalProducts["Product"]["retail_selling_price"]);
					}
					if(array_key_exists("retail_discount",$finalProducts["Product"])){
						$finalProducts["Product"]["discount"] = $finalProducts["Product"]["retail_discount"];
						unset($finalProducts["Product"]["retail_discount"]);
					}
					if(array_key_exists("rt_discount_status",$finalProducts["Product"])){
						$finalProducts["Product"]["discount_status"] = $finalProducts["Product"]["rt_discount_status"];
						unset($finalProducts["Product"]["rt_discount_status"]);
					}
					if(array_key_exists("retail_special_offer",$finalProducts["Product"])){
						$finalProducts["Product"]["special_offer"] = $finalProducts["Product"]["retail_special_offer"];
						unset($finalProducts["Product"]["retail_special_offer"]);
					}
					if(array_key_exists("quantity",$finalProducts["Product"])){
						unset($finalProducts["Product"]["quantity"]); //Added on July 7, 2016 on Inder's request
					}
					//------------------------------------
					$this->Product->clear();
					$this->Product->id = $finalProducts["Product"]["id"];
					$result2 = $this->Product->find('first',array('conditions' => array('Product.id' => $id),
																  'recursive'=> -1,
																  'fields' => array('id'),
																  )
													);
					//pr($finalProducts);
					if(count($result2) > 0){
						//Note: if product $id existing in boloram database only than save products for boloram kiosks
						
						$updateStrArr = array();
						foreach($finalProducts['Product'] as $fieldName => $fieldValue){
							$fieldValue = str_replace('"',"'",$fieldValue);
							$updateStrArr[]= "`$fieldName` = \"{$fieldValue}\"";
						}
						$setString  = implode(", ", $updateStrArr);
						$tProductID = $finalProducts['Product']['id'];
						$this->Product->clear();
						$this->Product->setSource("products");
						$this->Product->setDataSource('boloram_db');
						$updateBoloramQry = "UPDATE  `admin_boloram_15jun`.`products` SET $setString WHERE `admin_boloram_15jun`.`products`.`id` = $tProductID";
						//echo "<br/>Line #1089:".$updateBoloramQry;
						
						if(true){//$this->Product->query($updateBoloramQry);
							$this->Product->query($updateBoloramQry);
							//-----------------------------------
							$dbo = $this->Product->getDatasource();
							$logData = $dbo->getLog();
							$getLog = end($logData['log']);
							$queriesBoloram[] = $getLog['query'];
							//-----------------------------------
							
							$kiosks = $this->Kiosk->find('list');
							
							//-----------------------------------
							$dbo = $this->Kiosk->getDatasource();
							$logData = $dbo->getLog();
							$getLog = end($logData['log']);
							$queriesBoloram[] = $getLog['query'];
							//-----------------------------------
							foreach($kiosks as $kiosk_id1 => $kiosk){//echo "3";
								$table =  "kiosk_{$kiosk_id1}_products";
								$this->Product->setSource($table);
								$result3 = $this->Product->find('first',array(
																			  'conditions' => array('Product.id' => $id),
																			  'recursive' => -1,
																			  'fields' => array('id'),
																			  )
																);
								//-----------------------------------
								$dbo = $this->Product->getDatasource();
								$logData = $dbo->getLog();
								$getLog = end($logData['log']);
								$queriesBoloram[] = $getLog['query'];
								//-----------------------------------
								if(count($result3) > 0){
									//pr($setString);continue;
									
									$updateBoloramQry = "UPDATE  `admin_boloram_15jun`.`".$table."` SET $setString WHERE `admin_boloram_15jun`.`".$table."`.`id` = $tProductID";
									//echo "<br/>Line #1122:";
									$this->Product->id = $finalProducts["Product"]["id"];
									$this->Product->query($updateBoloramQry);
									//-----------------------------------
									$dbo = $this->Product->getDatasource();
									$logData = $dbo->getLog();
									$getLog = end($logData['log']);
									$queriesBoloram[] = $getLog['query'];
									//-----------------------------------
								}
							}
							//die("Finsished saving product in boloram");
							
						}
					}
					$saveCount++;
				}
			}
		}
		//End of loop for boloram products
		//echo "<br/><br/><br/>";
		//echo implode("<br/><br/><br/>", $queriesBoloram);
	}
    
    public function importKioskProducts(){
		$kiosks_query = $this->Kiosks->find('list',[
                                                'conditions' => ['Kiosks.status' => 1],
                                                'keyField' => 'id',
                                                'valueField' => 'name',
                                                'order' => ['Kiosks.name asc']
                                            ]
                                      );
        $kiosks_query = $kiosks_query->hydrate(false);
        if(!empty($kiosks_query)){
            $kiosks = $kiosks_query->toArray();
        }else{
            $kiosks = array();
        }
		$hint = $this->ScreenHint->hint('products','import_kiosk_products');
        if(!$hint){
            $hint = "";
        }
		$this->set('hint',$hint);
		$this->set(compact('kiosks'));
		//$productFields = array_keys($this->Product->schema());
		$productFields = array('id','product_code');
		
		if($this->request->is('post')){
			//pr($this->request);die;
			$msg = '';
			$kiosk_id = $this->request->data['Product']['kiosk_id'];
			$productSource = "kiosk_{$kiosk_id}_products";
            $productTable = TableRegistry::get($productSource,[
                                                                'table' => $productSource,
                                                            ]);
           // pr($this->request->data);die;
			$fileName = $this->request->data['Product']['import_data']['tmp_name'];
			$file = fopen($fileName,"r");
			$count = 0;
			$errors = array();
			$productIds = array();
			$finalProductArr = array();
			//This variable is collecting all product data rows from CSV file
			$prodKeys = array();
			//This variable is collecting heading of CSV file
			$unsetKeys = array();
			//This variable will collect extra fields other than 'id','product_code','selling_price','quantity'
			$rowCount = 0;
			$queriesFired = array();
			while(!feof($file)){
                
				$count++;
				if($count == 1){
					$headingsArr = fgetcsv($file);
					$countHeadings = count($headingsArr);//for comparing the number of headings with corresponding data in else case
					//below we are checking if the required field names input by user are correct
					//below will output an array with the name of field that is in $productFields and not in $headingsArr
					$validateFieldArr = array_diff($productFields, $headingsArr);
					// pr($validateFieldArr);die;
					if(count($validateFieldArr)){
						$msg = "Please add correct headings for (".implode(', ',$validateFieldArr).") Either id or product_code is missing.";
						$this->Flash->error($msg,['escape' => false]);
						return $this->redirect(array("action" => 'import_products'));
					}
				}else{
                  
					$inputFieldArr = fgetcsv($file);
					//filtering the incorrect arrays that do not have proper structure like headings array
					//example: an array with only 0 key was created during testing, so not considering such arrays for update in db table
					$blankcount = 0;
					foreach($headingsArr as $fieldId => $fieldValue){
						if($fieldValue == "id" && $inputFieldArr[$fieldId] == ""){
							$blankcount++;
						}
						if($fieldValue == "selling_price" && $inputFieldArr[$fieldId] == ""){
							$blankcount++;
						}
						if($fieldValue == "quantity" && $inputFieldArr[$fieldId] == ""){
							$blankcount++;
						}
					}
					if($blankcount>=3){
						continue;
					}
					if($countHeadings == count($inputFieldArr)){
						//pr($headingsArr);
						//pr($inputFieldArr);die;
						//$allowedKeys = array('id','selling_price','quantity');
						$productArray = array();
						foreach($headingsArr as $key => $heading){
							$rowCount+=1;
							if($heading == 'id' && is_numeric($inputFieldArr[$key]) == false){
								//
								$errors[$productArray['Product']['id']] = "id is not numberic for row = {$rowCount}";
								//break;
							}else{
								if($heading == 'id'){
									$productIds[] = $inputFieldArr[$key];
								}
								$productArray['Product'][$heading] = $inputFieldArr[$key];
								//pushing respective column values to respective column headings
							}
						}
       
						//model validation
						if(trim($productArray['Product']['selling_price']) == ""){
							unset($productArray['Product']['selling_price']);
						}
						
						$allowedKeys = array('id','selling_price','quantity'); // sorb  ,'product_code'
						$prodKeys = array_keys($productArray["Product"]);
						
						$unsetKeys = array_diff($prodKeys, $allowedKeys);
						
						foreach($productArray as $key => $value){
							foreach($value as $key1 => $value1){
								if(in_array($key1, $unsetKeys)){
									unset($productArray["Product"][$key1]);
								}
							}
						}
						//pr($productArray);die;
						$entity = $this->Products->get($productArray['Product']['id']);
						$entity = $this->Products->patchEntity($entity,$productArray['Product']);
						if (!empty($entity->errors())) {
							  foreach($ProductsEntity->errors() as $key){
								   foreach($key as $value){
									  $errors[] = $value;  
								   }
							  }
							  if($productArray['Product']['id'] == 0){
								   continue;
							  }
							$errors[$productArray['Product']['id']] = implode("</br>",$errors);
							
                        }else{
							$finalProductArr[] = $productArray;
						}
					}
				}
			}
			
			//pr($finalProductArr);
			if(count($errors)){
				$errorArr = array();
				foreach($errors as $productId => $error){
					foreach($error as $key => $error_str){
						$errorArr[] = "Product Id ".$productId.": ".$error_str['0'];
					}
				}
				$errorStr = implode("<br/>", $errorArr);
				$this->Flash->error($errorStr,['escape' => false]);
				return $this->redirect(array("controller" => "products","action" => 'import_kiosk_products'));
			}else{
				$saveCount = 0;
				if(count($finalProductArr)){
                    if(empty($productIds)){
                        $productIds = array(0 => null) ;
                    }
					$lastproducts_query = $this->Products->find('list',[
                                                                    'conditions'=>['Products.id IN'=> $productIds],
                                                                    'keyField' => 'id',
                                                                    'valueField' => 'id',
                                                                    'order' => ['Products.id'],
                                                                ]
														);
                    $lastproducts_query = $lastproducts_query->hydrate(false);
                    if(!empty($lastproducts_query)){
                        $lastproducts = $lastproducts_query->toArray();
                    }else{
                        $lastproducts = array();
                    }
					//variable $lastproducts have product id as key and product_code as value
					
					if(count($finalProductArr) >= 1){
						$prodKeys = array_keys($finalProductArr[0]['Product']);
					}
					
					$allowedKeys = array('id','selling_price','quantity'); // sorb  ,'product_code'
					$unsetKeys = array_diff($prodKeys, $allowedKeys);
					//pr($unsetKeys);
					$productsSaved = 0;
					foreach($finalProductArr as $k => $finalProducts){
						foreach($finalProducts['Product'] as $key => $tVal){
							if(in_array($key, $unsetKeys)){
								//if extra not allowed fields are removed from $finalProducts['Product'] array
								unset($finalProducts['Product'][$key]);
							}
						}
						//pr($finalProducts['Product']);die;
						$id =  $finalProducts['Product']['id'];
						//$product_code = $finalProducts['Product']['product_code'] ; // sorb
						//unset all fields other than selling price, product_code and id
						$prodKeys = array_keys($finalProducts);
						
						if(array_key_exists($id, $lastproducts) ){  //$id == $lastproducts[$id] ||
							//&& $product_code == $lastproducts[$id]
							if(empty($id))continue;
							
							//unset($finalProducts['Product']['product_code']);
							if(array_key_exists('selling_price', $finalProducts['Product']) && trim($finalProducts['Product']['selling_price']) == ""){
								unset($finalProducts['Product']['selling_price']);
							}
							if(array_key_exists('quantity', $finalProducts['Product']) && trim($finalProducts['Product']['quantity']) == ""){
								unset($finalProducts['Product']['quantity']);
							}
                            $Id_get = $this->Products->get($id);
                            $Patch_entity = $this->Products->patchEntity($Id_get,$finalProducts,['validates' => false]);
							if($this->Products->save($Patch_entity)){
								//if($this->Product->query("UPDATE `{$productSource}` SET `selling_price` ")){
								$saveCount++;
								//--------code for reading cake query---
								
								//--------code for reading cake query---
							}else{
								die("-----");
							}
						}else{
							$this->Flash->error("Products could not be updated. Please try again!");
							return $this->redirect(array('action' => 'import_kiosk_products'));
						}
					}
				}
			}
   
			if($saveCount > 0){
				//echo implode("<br/>",$queriesFired);die;
				$this->Flash->success("$saveCount rows have been updated!");
			}else{
				$this->Flash->error("Products could not be updated. Please try again!");
			}
			return $this->redirect(array('action' => 'import_kiosk_products'));
		}
    }
    
      public function newProductPushNotification(){
		$countModified_query = $this->Products->find('all',array(
				'conditions' => array('DATE(Products.created)' => date('Y-m-d'))
			)
		);
        $countModified = $countModified_query->count();
		if($countModified > 0){
			$pushStr = "$countModified new products were added today to the inventory";
			$this->Pusher->push1($pushStr);//created in components
		}
		return $this->redirect(array('action' => 'index'));
    }
    
    public function productPriceChangePushNotification(){
		$countModified_query = $this->Products->find('all',array(
				'conditions' => array('DATE(Products.lu_sp)' => date('Y-m-d'))
			)
		);
        $countModified = $countModified_query->count();
		if($countModified > 0){
			$pushStr = "Price of $countModified products have been updated today";
			$this->Pusher->push1($pushStr);//created in components
		}
		return $this->redirect(array('action' => 'index'));
    }
    public function cloneProduct($id = null) {
	 $sites = Configure::read('sites');
	 $path = dirname(__FILE__);
		$isboloRam = strpos($path, ADMIN_DOMAIN);
		if($isboloRam == false){
            $this->Flash->error(__("This function works only on ". ADMIN_DOMAIN));
			return $this->redirect(array('controller' => 'home','action' => "dashboard"));die;
		}
         $productEntity = $this->Products->newEntity();
        $this->set(compact('productEntity'));
		$vat = $this->setting['vat'];
		$this->set('vat', $vat);
        $kioskslist_query = $this->Kiosks->find('list',[
                                             'conditions'=>['Kiosks.status'=>1],
                                             'keyField' => 'id',
                                             'valueField' => 'name'
                                             ]
                                     );
        if(!empty($kioskslist_query)){
          $kioskList = $kioskslist_query->toArray();
        }
       	if($this->request->session()->read('Auth.User.group_id') != ADMINISTRATORS){
            $this->Flash->error(__('You are not authorized to access that location.'));
			return $this->redirect(array('action' => 'index'));
		}
		#pr($this->request);die;
		if (!$this->Products->exists($id)) {
			throw new NotFoundException(__('Invalid product'));
		}
		if ($this->request->is(array('post', 'put'))) {
           //pr($this->request);die;
		 $this->request->data['user_id'] = $this->request->session()->read('Auth.User.id');
		 $this->request->data['created'] = date('Y-m-d h:i:s');
		 $this->request->data['modified'] = date('Y-m-d h:i:s');
			$countDuplicateCode_query = $this->Products->find('all',array(
                                                                          'conditions' => array(
                                                                                                'Products.product_code' => $this->request->data['product_code']
                                                                                                )
                                                                          )
                                                              );
            if(!empty($countDuplicateCode_query)){
                $countDuplicateCode = $countDuplicateCode_query->count();
            }else{
                $countDuplicateCode = '';
            }
        //  pr($countDuplicateCode); 
			/*if($countDuplicateCode >= 1){
                $this->Flash->error("The entered product code already exists!");
				//$this->Session->setFlash(__('The entered product code already exists!'));
				return $this->redirect(array('action' => 'clone_product',$id));
				die;
			}*/
          //  pr($this->request->data);die;
            if(isset($this->request->data['image']['name']) && !empty($this->request->data['image']['name'])){
			     $imageName = $this->request->data['image']['name'];
			}else{
			   $imageName = "";
			}
             
         //  pr($this->request->data);die;
            
            $productpatchEntity = $this->Products->patchEntity($productEntity, $this->request->data);
           //  pr($productpatchEntity);die;
           	if ($this->Products->save($productpatchEntity)) {
                $data = $this->request->data;
                 //$data_query = $data_query->hydrate(false);
                //saving the same in all the kiosk tables, updating cost price of retail table as retail_cost_price of main table.
                $productId = $productpatchEntity->id; 
                $data['id'] = $productId;
                    
                 //pr($data);die;
					//$data['Product']['cost_price'] = $this->request['data']['Product']['retail_cost_price'];
					//$data['Product']['selling_price'] = $this->request['data']['Product']['retail_selling_price'];
					//$data['Product']['discount_status'] = $this->request['data']['Product']['rt_discount_status'];
					//$data['Product']['discount'] = $this->request['data']['Product']['retail_discount'];
                   
					if(array_key_exists('name',$data['image']) && !empty($data['image']['name'])){
                        //unset($data['Product']['image']['tmp_name']);
						//unset($data['Product']['image']['size']);
						//unset($data['Product']['image']['error']);
						$data['image'] = $this->request->data['image']['name'];
					}else{
                          $data['image'] = '';
						//unset($data['Product']['image']);
					}
                    if(!empty($data['image'])){
                        if(mkdir(WWW_ROOT."files/Products/image/{$productId}")){
                            $imageName = $data['image'];
                            // WWW_ROOT."files".DS.'Products'.DS.'image'.DS.$product->id.DS;
								  if(rename(WWW_ROOT."files".DS.'Products'.DS.'image'.DS.$imageName  ,  WWW_ROOT."files".DS.'Products'.DS.'image'.DS.$productId.DS.$imageName)){
									 $query = "UPDATE products SET image_dir = {$productId} where id = {$productId}";
									 $query2 = "UPDATE products SET image = '$imageName' WHERE id = $productId";
									 $conn = ConnectionManager::get('default');
									 $stmt = $conn->execute($query);
									 $stmt = $conn->execute($query2);
								  }
						 }
					} 
                     
					$counter = 0;
					foreach($kioskList as $kioskId=>$kioskName){
                        $KioskProduct_source = "kiosk_{$kioskId}_products";
                        $KioskProductTable = TableRegistry::get($KioskProduct_source,[
																				'table' => $KioskProduct_source,
																			]);
						 
						$KioskProductEntity = $KioskProductTable->newEntity();
                        $KioskProductpatchEntity = $KioskProductTable->patchEntity($KioskProductEntity, $data);
                       // pr($KioskProductpatchEntity);die;
						if($KioskProductTable->save($KioskProductpatchEntity)){
                            $product_id = $KioskProductpatchEntity->id; 
                            $query = "UPDATE {$KioskProduct_source} SET image_dir = {$product_id} where id = {$product_id}";
									 
									 $conn = ConnectionManager::get('default');
									 $stmt = $conn->execute($query);
									 
							$counter++;
						}
					}
                     if($counter > 0){
						 if(!empty($sites)){
							  foreach($sites as $site_id => $site_value){
								   $this->addclone_on_mb($data,$site_value);	   
							  }
                            
						 }
                        }
					$msg = "The product has been saved. ";
						 if($productId){
							  if(!empty($sites)){
								   $count_k = 0;
								   foreach($sites as $site_id => $site_val){
										$conn = ConnectionManager::get($site_val);
										$stmt = $conn->execute("SELECT id,product FROM products WHERE id = $productId");
										$product_data = $stmt ->fetchAll('assoc');
										if(empty($product_data)){
											 $count_k++; 
											 if($count_k == 1){
												  $msg .= " But Failed to create product on $site_val </br>";	  
											 }else{
												  $msg .= "Failed to create product on $site_val </br>";
											 }
											 
										}
								   }
								   if($count_k >= 1){
										 $msg .= "Please re-edit this product on main site and re-submit this product should fix this issue";
								   }
							  }else{
								   $count_k = 0;
							  }
						 }
						 
					   $this->Flash->success($msg,['escape' => false]);
					if($count_k >= 1){
						 return $this->redirect(array('action' => 'edit',$productId));
					}else{
						 return $this->redirect(array('action' => 'index'));		 
					}
				
			} else {
                    //pr($this->request);die;
                    $errors = $productpatchEntity->errors();
                 //   pr($errors); 
                    $err = array();
                    foreach($errors as $error){
                        foreach($error as $key){
                             $err[] = $key;
                        }
                    //$err[] = $key." already in use";
                    }
			   $this->Flash->error(implode("</br>",$err),['escape' => false]);
               $this->Flash->error(__('The product could not be saved. Please, try again.'));
 
			}
		} else { //if request submitted of type post/put
            $options = array('conditions' => array('Products.id' => $id) 
							 
							 );
			$productData_query = $this->Products->find('all', $options);
			$productData_query = $productData_query->hydrate(false);
			if(!empty($productData_query)){
			   $productData = $productData_query->first();
			}else{
			   $productData = array();
			}
			//pr($productData);die;
			unset($productData['product_code']);
			unset($productData['image']);
			unset($productData['image_dir']);
			unset($productData['color']);
			//pr($productData);
			$this->request->data = $productData;
		}
		//$categories = $this->Product->Category->find('list',array('fields' => array('id', 'category'),
		//							   'conditions' => array('Category.status' => 1)));
         $Categories_list_query = $this->Categories->find('list', [
                                                        'keyField' => 'id',
                                                        'valueField' => 'category'
                                                    ],
                                                    ['Categories.status' => 1],
                                                    ['Categories.category asc'] 
                                            );
        if(!empty($Categories_list_query)){
             $category_list = $Categories_list_query->toArray();
        }
        $Category_query = $this->Categories->find('all',array(
								'fields' => array('id', 'category','id_name_path'),
								'conditions' => array('Categories.status' => 1),
								'order' => 'Categories.category asc' 
								));
        if(!empty($Category_query)){
              $categories = $Category_query->toArray();
        }
      
       // pr($category);
//        $categories = $this->CustomOptions->category_options($category,true);
//		$categories = $this->Products->Category->find('all',array('fields' => array('id', 'category','id_name_path'),
//                                                                       'conditions' => array('Category.status' => 1)));
		$categories = $this->CustomOptions->category_options($categories);
          $brandName_query = $this->Brands->find('list',
												[
													'keyField' =>'id',
													'valueField' => 'brand',
                                                     'conditions' => ['Brands.status' => 1],
                                                     'order'=>'brand asc',
												]
										 );
		//$brandName_query = $brandName_query->hydrate(false);
		if(!empty($brandName_query)){
			$brands = $brandName_query->toArray();
		}else{
			$brands = array();
		}
		
		if(isset($productData) && array_key_exists('brand_id',$productData)){
		  $brand_id = $productData['brand_id'];  
		}else{
		  $brand_id = $this->request->data['brand_id'];
		}
		
		$mobileModels = $this->ProductModels->find('list',array(
																				'keyField' =>'id',
																				'valueField' => 'model',
																				'conditions' => ['brand_id' => $brand_id],
																				'order'=>'model asc',												
																	  )
															)->toArray();
		if(empty($mobileModels)){
		  $mobileModels = array(-1 => "No Option Available");
		}
		
		//$brands = $this->Brands->find('list',array('fields' => array('id', 'brand'),
		//							   'conditions' => array('Brand.status' => 1)));
		$this->set(compact('categories', 'brands','mobileModels'));
	}
    
   public function addclone_on_mb($boloram_data,$site_value){
	  $external_sites = Configure::read('EXT_RETAIL');//changed from external_sites
	  $external_site_status = 0;
	  if(in_array($site_value,$external_sites)){
		  $external_site_status = 1;
	  }
	  
	  $disnt_for_sites = Configure::read('disnt_for_diff_sites');
	  $disnt_for_diff_sites = 0;
	  if(!empty($disnt_for_sites)){
		  foreach($disnt_for_sites as $dis_key => $dis_value){
			   if($dis_value == $site_value){
					$disnt_for_diff_sites = 1;
			   }
		  }
	  }
        //echo "ll";
        //pr($boloram_data);die;
//       if(!array_key_exists("Product",$boloram_data)){
//			return;
//		}
		if(array_key_exists('image',$boloram_data)){
			$image_data = $boloram_data['image'];
			unset($boloram_data['image']);
			if(is_array($image_data)){
					if(array_key_exists("name",$image_data)){
						 $boloram_data['image'] = $image_data['name']; 
					}   
			}else{
			   $boloram_data['image'] = $image_data; 
			}
		}
		
		if(array_key_exists("image_dir",$boloram_data)){
			unset($boloram_data["image_dir"]);
		}
		
		if(array_key_exists("retail_cost_price",$boloram_data)){
			$boloram_data["cost_price"] = $boloram_data["retail_cost_price"];
			if($external_site_status == 1){
			   $boloram_data["cost_price"] = $boloram_data["selling_price"]/1.2;
               $boloram_data["retail_cost_price"] = $boloram_data["selling_price"]/1.2;
			}
			//unset($boloram_data["retail_cost_price"]);
		}
		if(array_key_exists("retail_selling_price",$boloram_data)){
			
			if($external_site_status == 1){
			   // $without_vat = (($boloram_data["selling_price"]/1.2));
			   //$boloram_data["selling_price"] = $without_vat + ($without_vat*(10/100));
			   $boloram_data["selling_price"] = $boloram_data["retail_selling_price"];
			}else{
			   $boloram_data["selling_price"] = $boloram_data["retail_selling_price"];
			}
			//unset($boloram_data["retail_selling_price"]);
		}
		if(array_key_exists("retail_discount",$boloram_data)){
			$boloram_data["discount"] = $boloram_data["retail_discount"];
			if($disnt_for_diff_sites == 1){
			   $boloram_data["discount"] = 50;
			}
			unset($boloram_data["retail_discount"]);
		}
		if(array_key_exists("rt_discount_status",$boloram_data)){
			$boloram_data["discount_status"] = $boloram_data["rt_discount_status"];
			if($disnt_for_diff_sites == 1){
			   $boloram_data["discount_status"] = 1;
			}
			unset($boloram_data["rt_discount_status"]);
		}
		if(array_key_exists("retail_special_offer",$boloram_data)){
			$boloram_data["special_offer"] = $boloram_data["retail_special_offer"];
			unset($boloram_data["retail_special_offer"]);
		}
		
		if(array_key_exists("Min Sale Amount",$boloram_data)){
			unset($boloram_data["Min Sale Amount"]);
		}
		
		if(array_key_exists("Final Discount",$boloram_data)){
			unset($boloram_data["Final Discount"]);
		}
        if(array_key_exists("manufacturing_date",$boloram_data)){
			 $boloram_data["manufacturing_date"] =  $boloram_data["manufacturing_date"]["year"]."-".$boloram_data["manufacturing_date"]["month"]."-".$boloram_data["manufacturing_date"]["day"];
		}
        if(array_key_exists('location',$boloram_data)){
		  unset($boloram_data['location']);
		}
		$boloram_data['created'] = date('Y-m-d h:i:s');
		$boloram_data['modified'] = date('Y-m-d h:i:s');
      //  pr($boloram_data);die;
         unset($boloram_data["null"]);
      
        $conn = ConnectionManager::get($site_value);
        $stmt = $conn->execute("SELECT id,name FROM kiosks");
        $kiosks_data = $stmt ->fetchAll('assoc');
        $kiosks = array();
        foreach($kiosks_data as $key => $value){
            $kiosks[$value['id']] = $value['name'];
		}
		
	//	 pr($kiosks);die;
       // $conn = ConnectionManager::get('mbwaheguru');
        if($statement = $conn->insert('products',$boloram_data))  {
         //   echo "kk";die;
            $data = $boloram_data;
            $productId =  $statement->lastInsertId('products');
            $res = $conn->update('products',['image_dir' => $productId ],['id' => $productId]);
            //$stmt = $conn->execute("update products set image_dir = $productId where id = $productId");
            //$result = $stmt->fetchAll('assoc');
             $data['id'] = $productId;
			unset($data["lu_cp"]);
			unset($data["lu_sp"]);
			unset($data["special_offer"]);
           // pr($kiosks);die;
            foreach($kiosks as $kiosk_id => $name){
                $table =  "kiosk_{$kiosk_id}_products";
                //$this->Product->setSource($table);//'Product.product_code' => $product_code
                $res = $conn->insert($table,$data);
                $productId =  $res->lastInsertId('products');
                $res1 = $conn->update('products',['image_dir' => $productId ],['id' => $productId]);
            }
 
		}else{
           //echo "else";die; 
			$errors = $this->Products->validationErrors;
			 pr($errors);die;
		}
	}
	
	
	public function import(){
		  ini_set('max_execution_time', 3000);
		  $cmd = "sh /var/www/vhosts/".ADMIN_DOMAIN."/kiosk_repo/csv_product_import.sh > result.txt";
		  $output = shell_exec($cmd);
		  echo "Processing Import";
		  $descriptorspec = array(
			   0 => array("pipe", "r"),   // stdin is a pipe that the child will read from
			   1 => array("pipe", "w"),   // stdout is a pipe that the child will write to
			   2 => array("pipe", "w")    // stderr is a pipe that the child will write to
			);
			flush();
			ob_implicit_flush(true);ob_end_flush();
			$process = proc_open('watch tail /var/www/vhosts/'.ADMIN_DOMAIN.'/kiosk_repo/result.txt', $descriptorspec, $pipes, realpath('./'), array());
			echo "<pre>";
			if (is_resource($process)) {
				while ($s = fgets($pipes[1])) {
					print $s;
					flush();
				}
			}
			echo "</pre>";
			proc_close();
		  //set_time_limit(0);
		  /*ob_end_flush();
		  ini_set("output_buffering", "0");
		  ob_implicit_flush(true);
		  header('Content-Type: text/event-stream');
		  header('Cache-Control: no-cache');
		  $returend_array = array();
		  
		  //$res = exec("sh /var/www/vhosts/".ADMIN_DOMAIN."/kiosk_repo/csv_product_import.sh");
		  //pr($res);die;
		  //echo shell_exec("/var/www/vhosts/".ADMIN_DOMAIN."/kiosk_repo/csv_product_import.sh 2>&1 | tee -a /tmp/mylog 2>/dev/null >/dev/null &");
		  while (@ ob_end_flush()); // end all output buffers if any

		  $proc = popen($cmd, 'r');
		  
		  while($b = fgets($proc, 2048)) { 
			   echo $b."<br>\n"; 
			   //@ob_flush();
			   flush(); 
		  } 
		  pclose($proc);*/
		  /*
		  echo '<pre>';
		  while (!feof($proc))
		  {
			  echo fread($proc, 4096);
			  @ flush();
		  }
		  echo '</pre>';
		  */
	}
	
	public function import1($allowed_array){
	 ini_set('memory_limit','1024M');
	 set_time_limit ( 0 );
		/*
		 SELECT * FROM `products` WHERE id in(2151, 2155, 2388, 2678, 3207,3682, 3288, 3682, 4131, 4753, 5287, 5289, 5396, 5397, 5399, 5401, 5403, 5404, 5411, 5412, 5435, 5525, 6492, 6497, 6499)
		 */
		$query = "DESCRIBE products";
        $conn = ConnectionManager::get('default');
        $stmt = $conn->execute($query);
        $schema = $stmt ->fetchAll('assoc');
        foreach($schema as $keys => $feilds){
            $productFields[] = $feilds['Field'];
        }
		
		
		$valus2exclude = $this->valuesToExclude;
		$productFields = array_diff($productFields, $valus2exclude);
		foreach($productFields as $p_no => $p_value){
			if(!in_array($p_value,$allowed_array)){
				if($productFields[$p_no] == 'id'){
					continue;
				}
				unset($productFields[$p_no]);
			}
		}
		//products from CSV Table
		$csv_products_query = $this->CsvProducts->find('all',array(
															'fields' => $productFields,
															'recursive'=> -1
															)
												);
		$csv_products_query = $csv_products_query->hydrate(false);
		if(!empty($csv_products_query)){
		  $csv_products = $csv_products_query->toArray();
		}else{
		  $csv_products = array();
		}
		$csv_productids = array();
		foreach($csv_products as $csv_product){
			$csv_productids[] = $csv_product['id'];
		}
		$saveCount = 0; 
		if(count($csv_productids)){
			//Products from main table
			$lastproducts_query = $this->Products->find('list',array('conditions' => array(
																					'id IN'=> $csv_productids),
																					//'fields' => array('id','selling_price'),
																					'keyField' => 'id',
																					'valueField' => 'selling_price',
																					'order' => 'lu_sp desc',
																					//'recursive' => -1
																				)
																			);
			$lastproducts_query = $lastproducts_query->hydrate(false);
			if(!empty($lastproducts_query)){
			   $lastproducts = $lastproducts_query->toArray();
			}else{
			   $lastproducts = array();
			}
			
			$all_query = array();
			$recordsProcessed = array();
			$recordsProcessed['all_processed'] = false;
			$kiosks_query = $this->Kiosks->find('list', array(
														'keyField' => 'id',
														'valueField' => 'name',
														));
			$kiosks_query = $kiosks_query->hydrate(false);
			if(!empty($kiosks_query)){
			   $kiosks = $kiosks_query->toArray();
			}else{
			   $kiosks = array();
			}
			$allowedKiosks = array(1,2,3 ,4, 5, 10000);
			
			foreach($kiosks as $kiosk_id => $kioskName){
				//first we should loop ADMIN_DOMAIN kiosk(s) and than boloram kiosks, may be similar foreach loop after this loop
				//first we should update all records of one kiosk not one product for all kiosks and than starting 2nd product for all kiosks 
				if(!array_key_exists($kiosk_id, $allowedKiosks)){
					continue;
				}
				//$table =  "kiosk_{$kiosk_id}_products";
				$table_source = "kiosk_{$kiosk_id}_products";
			    $productTable = TableRegistry::get($table_source,[
																   'table' => $table_source,
			    ]);
				//$this->Product->setSource($table);
				//pr($csv_products);die;
				$kioskProccessedRecs = 0;
				foreach($csv_products as $k => $csvproduct){
					$kioskProccessedRecs++;
					//loop products from CSV table
					$id =  $csvproduct['id'];
					if(array_key_exists('selling_price', $csvproduct)){
						$selling_price = $csvproduct['selling_price'] ;
						if($selling_price != $lastproducts[$id]){
							$csvproduct['lu_sp'] = date("Y-m-d H:i:s");
						}else{
							unset($csvproduct['lu_sp']);
						}
					}
					foreach($csvproduct as $s_key => $s_value){
						//Removing empty fields from $csvprodut array. We will not save anything for empty fields.
						//foreach($s_key as $key => $value){
							if(empty($csvproduct[$s_key])){
								unset($csvproduct[$s_key]);
							}
						//}
					}
					
					$result_query = $productTable->find('all',array(
																	'conditions' => array('id' => $id),
																	'fields' => array('id'),
																));
					$result_query = $result_query->hydrate(false);
					if(!empty($result_query)){
						 $result = $result_query->first();
					}else{
						 $result = array();
					}
					//Above query is fired to ensure product id from csv_prodcts table is present in main product table
					//Only than action will be taken, otherwise nothing will be done.
					if(count($result) > 0){
						$product_id =  $result['id'];
						//$this->Product->id = $product_id;
						$get_entity = $productTable->get($product_id);
						$ProdArr = $csvproduct;
						$table_source =  "kiosk_{$kiosk_id}_products";
						 $productTable = TableRegistry::get($table_source,[
																			'table' => $table_source,
						 ]);
						//$this->Product->setSource($table);
						$result1_query = $productTable->find('all',array(
																		'conditions' => array('id' => $product_id),
																		//'keyField' => 'id',
																		//'valueField' => 'id',
																		'fields' => array('id'),
																		)
																);
						$result1_query = $result1_query->hydrate(false);
						if(!empty($result1_query)){
							  $result1 = $result1_query->first();
						}else{
							  $result1 = array();
						}
						if(count($result1) > 0){
							if(array_key_exists("quantity",$ProdArr)){
								unset($ProdArr["quantity"]);
							}
							if(array_key_exists("stock_level",$ProdArr)){
								unset($ProdArr["stock_level"]);
							}
							if(array_key_exists("cost_price",$ProdArr)){
								unset($ProdArr["cost_price"]);
							}
							//Saving product for ADMIN_DOMAIN kiosk if existing
							$get_entity = $productTable->get($product_id);	
							if(array_key_exists('id',$ProdArr)){
								unset($ProdArr['id']);
							}
							$get_entity = $productTable->patchEntity($get_entity,$ProdArr,['validate' => false]);
							if($productTable->save($get_entity)){//update
							  $saveCount++;
							}else{
							  pr($get_entity->errors());die;
							}
							
							//$recordsProcessed['data'][$kiosk_id] = array($kioskName, $kioskProccessedRecs);
							//$this->write_to_file(json_encode($recordsProcessed));
							//$dbo = $this->Product->getDatasource();
							//$logData = $dbo->getLog();
							//$getLog = end($logData['log']);
							///$all_query[] = $getLog['query'];
						}
					}
				}
			}
			//$recordsProcessed['all_processed'] = true;
			//$this->write_to_file(json_encode($recordsProcessed));
		}
		if($saveCount > 0){
			$msg =  $saveCount.'product has been saved.';
			$this->Flash->success(__($msg));
			return $this->redirect(array('action' => 'import_products_2_db'));
		}else{
		  $msg =  'Some error in import.';
			$this->Flash->error(__($msg));
		  return $this->redirect(array('action' => 'import_products_2_db'));
		}die;
	}
	
	public function dashboardData(){
	 $session_kiosk_id = $this->request->Session()->read('kiosk_id');
		//echo"hi";die;
		//pr($this->request);die;
		if($this->request->is(['post'])){
		  if(array_key_exists('kiosk',$this->request->data)){
			$kioskId = $this->request->data['kiosk'];
			   if($kioskId == 10000){
				   $kioskId = 0;
			   }   
		  }else{
			   $kioskId = "";
		  }
			
			if(!empty($this->request->data['date'])){
				$created = date('Y-m-d',strtotime($this->request->data['date']));
			}else{
				$created = date('Y-m-d');
			}
			if(array_key_exists('end_date',$this->request->data)){
			   $end_date = $this->request->data['end_date'];
			}else{
			   $end_date = date('Y-m-d');
			}
			$conditionArr[] = array(
						"date >=" => date('Y-m-d', strtotime($created)),
						"date <" => date('Y-m-d', strtotime($end_date. ' +1 Days')),			
					       );
			if($session_kiosk_id){
			   $conditionArr['kiosk_id'] = $session_kiosk_id;
			}else{
			   $conditionArr['kiosk_id'] = $kioskId;  
			}
			
			
		}else{
		  $kioskId = 0;
		  $created = date('Y-m-d');
		  $end_date = date('Y-m-d');
		  $conditionArr[] = array(
						"date >=" => date('Y-m-d', strtotime($created)),
						"date <" => date('Y-m-d', strtotime($end_date. ' +1 Days')),			
					       );
			if($session_kiosk_id){
			   $conditionArr['kiosk_id'] = $session_kiosk_id;
			   $kioskId = $session_kiosk_id;
			}else{
			   $conditionArr['kiosk_id'] = $kioskId;  
			}
		}
		
		
		$dashboardData_query = $this->DashboardData->find('all',[
														'conditions'=>$conditionArr,
														'order'=>['id desc'],
														//'limit'=>2
													]
												);
		//pr($dashboardData_query);die;
		$dashboardData_query = $dashboardData_query->hydrate(false);
		if(!empty($dashboardData_query)){
			$dashboardData = $dashboardData_query->toArray();
		}else{
			$dashboardData = array();
		}
		//pr($dashboardData);die;
		$normalUserData = array();
		$otherUserData = array();
		foreach($dashboardData as $key => $dashboard){
			if($dashboard['user_type'] == 'normal'){
				$normalUserData[] = $dashboard;
			}
			if($dashboard['user_type'] == 'other'){
				$otherUserData[] = $dashboard;
			}
		}
		//if(empty($dashboardData)){
			$otherUserDataSum = $normalUserDataSum = array( //= $otherUserData = $normalUserData = array(
								'repair_sale' =>0,
								'repair_sale_desc' =>[],
								'repair_refund' =>0,
								'repair_refund_desc' =>[],
								'unlock_sale' =>0,
								'unlock_sale_desc' =>[],
								'unlock_refund' =>0,
								'unlock_refund_desc' =>[],
								'product_sale' =>0,
								'product_sale_desc' =>[],
								'quotation' =>0,
								'quotation_desc' =>[],
								'credit_note' =>0,
								'credit_note_desc' =>[],
								'credit_quotation' =>0,
								'credit_quotation_desc' =>[],
								'product_refund' =>0,
								'product_refund_desc' =>[],
								'bulk_mobile_sale' =>0,
								'bulk_mobile_sale_desc' =>[],
								'bulk_mobile_refund' =>0,
								'bulk_mobile_refund_desc' =>[],
								'mobile_sale' =>0,
								'mobile_sale_desc' =>[],
								'mobile_purchase' =>0,
								'mobile_purchase_desc' =>[],
								'mobile_refund' =>0,
								'mobile_refund_desc' =>[],
								'total_sale' =>0,
								'total_sale_desc' =>[],
								'total_refund' =>0,
								'total_refund_desc' =>[],
								'net_sale' =>0,
								'net_sale_desc' =>[],
								'net_card' =>0,
								'net_card_desc' =>[],
								'net_credit' =>0,
								'net_credit_desc' =>[],
								'net_bnk_tnsfer' =>0,
								'net_bnk_tnsfer_desc' =>[],
								'net_cheque_payment' =>0,
								'net_cheque_payment_desc' =>[],
								'cash_in_hand' =>0,
								'cash_in_hand_desc' =>[],
								'credit_to_cash' =>0,
								'credit_to_cash_desc' =>[],
								'credit_to_other_payment' =>0,
								'credit_to_other_payment_desc' =>[],
							   );
		//}
		//pr($dashboardData);die;
		$skip_date_arr1 = array();
		foreach($dashboardData as $key => $dashboard){
		  $adddata1 = "'".$dashboard['date']."'";
            $user_type1 = $dashboard['user_type'];
            if(array_key_exists($adddata1,$skip_date_arr1)){
                if(array_key_exists($user_type1,$skip_date_arr1[$adddata1])){
                    continue;    
                }else{
                    $skip_date_arr1[$adddata1][$user_type1] = $dashboard['date'];    
                }
            }else{
                $skip_date_arr1[$adddata1][$user_type1] = $dashboard['date'];    
            }
		  
		  //pr($dashboard);die;
			if($dashboard['user_type'] == 'normal'){
				$normalUserDataSum['repair_sale'] += $dashboard['repair_sale'];
				$repair_sale_desc = unserialize($dashboard['repair_sale_desc']);
				if(!array_key_exists('cash',$normalUserDataSum['repair_sale_desc'])){
					$normalUserDataSum['repair_sale_desc'] = ['cash' => $repair_sale_desc['cash'],
													  'card' => $repair_sale_desc['card'],
													  ];
					//$normalUserDataSum['repair_sale_desc']['card'] = $repair_sale_desc['card'];
				}else{
					$normalUserDataSum['repair_sale_desc']['cash'] += $repair_sale_desc['cash'];
					$normalUserDataSum['repair_sale_desc']['card'] += $repair_sale_desc['card'];
				}
				$unlock_sale_desc = unserialize($dashboard['unlock_sale_desc']);
				if(!array_key_exists('cash',$normalUserDataSum['unlock_sale_desc'])){
					$normalUserDataSum['unlock_sale_desc'] = ['cash' => $unlock_sale_desc['cash'],
													  'card' => $unlock_sale_desc['card']
													  ];
					//$normalUserDataSum['unlock_sale_desc']['card'] = $unlock_sale_desc['card'];
				}else{
					$normalUserDataSum['unlock_sale_desc']['cash'] += $unlock_sale_desc['cash'];
					$normalUserDataSum['unlock_sale_desc']['card'] += $unlock_sale_desc['card'];
				}
				
				$product_sale_desc = unserialize($dashboard['product_sale_desc']);
				if(!array_key_exists('cash',$normalUserDataSum['product_sale_desc'])){
					$normalUserDataSum['product_sale_desc'] = ['cash' => $product_sale_desc['cash'],
													   'card' => $product_sale_desc['card'],
													   'credit' => $product_sale_desc['credit'],
													   'bank_transfer' => $product_sale_desc['bank_transfer'],
													   'cheque' => $product_sale_desc['cheque'],
													   ];
					//$normalUserDataSum['product_sale_desc']['card'] = $product_sale_desc['card'];
					//$normalUserDataSum['product_sale_desc']['credit'] = $product_sale_desc['credit'];
					//$normalUserDataSum['product_sale_desc']['bank_transfer'] = $product_sale_desc['bank_transfer'];
					//$normalUserDataSum['product_sale_desc']['cheque'] = $product_sale_desc['cheque'];
				}else{
					$normalUserDataSum['product_sale_desc']['cash'] += $product_sale_desc['cash'];
					$normalUserDataSum['product_sale_desc']['card'] += $product_sale_desc['card'];
					$normalUserDataSum['product_sale_desc']['credit'] += $product_sale_desc['credit'];
					$normalUserDataSum['product_sale_desc']['bank_transfer'] += $product_sale_desc['bank_transfer'];
					$normalUserDataSum['product_sale_desc']['cheque'] += $product_sale_desc['cheque'];
				}
				
				$quotation_desc = unserialize($dashboard['quotation_desc']);
				if(!array_key_exists('cash',$normalUserDataSum['quotation_desc'])){
					$normalUserDataSum['quotation_desc'] = ['cash' => $quotation_desc['cash'],
													'card' => $quotation_desc['card'],
													'credit' => $quotation_desc['credit'],
													'bank_transfer' => $quotation_desc['bank_transfer'],
													'cheque' => $quotation_desc['cheque'],
													];
					//$normalUserDataSum['quotation_desc']['card'] = $quotation_desc['card'];
					//$normalUserDataSum['quotation_desc']['credit'] = $quotation_desc['credit'];
					//$normalUserDataSum['quotation_desc']['bank_transfer'] = $quotation_desc['bank_transfer'];
					//$normalUserDataSum['quotation_desc']['cheque'] = $quotation_desc['cheque'];
				}else{
					$normalUserDataSum['quotation_desc']['cash'] += $quotation_desc['cash'];
					$normalUserDataSum['quotation_desc']['card'] += $quotation_desc['card'];
					$normalUserDataSum['quotation_desc']['credit'] += $quotation_desc['credit'];
					$normalUserDataSum['quotation_desc']['bank_transfer'] += $quotation_desc['bank_transfer'];
					$normalUserDataSum['quotation_desc']['cheque'] += $quotation_desc['cheque'];
				}
				
				$credit_note_desc = unserialize($dashboard['credit_note_desc']);
				if(!array_key_exists('cash',$normalUserDataSum['credit_note_desc'])){
					$normalUserDataSum['credit_note_desc'] = ['cash' => $credit_note_desc['cash'],
													  'card' => $credit_note_desc['card'],
													  'credit' => $credit_note_desc['credit'],
													  'bank_transfer' => $credit_note_desc['bank_transfer'],
													  'cheque' => $credit_note_desc['cheque'],
													  ];
					//$normalUserDataSum['credit_note_desc']['card'] = $credit_note_desc['card'];
					//$normalUserDataSum['credit_note_desc']['credit'] = $credit_note_desc['credit'];
					//$normalUserDataSum['credit_note_desc']['bank_transfer'] = $credit_note_desc['bank_transfer'];
					//$normalUserDataSum['credit_note_desc']['cheque'] = $credit_note_desc['cheque'];
				}else{
					$normalUserDataSum['credit_note_desc']['cash'] += $credit_note_desc['cash'];
					$normalUserDataSum['credit_note_desc']['card'] += $credit_note_desc['card'];
					$normalUserDataSum['credit_note_desc']['credit'] += $credit_note_desc['credit'];
					$normalUserDataSum['credit_note_desc']['bank_transfer'] += $credit_note_desc['bank_transfer'];
					$normalUserDataSum['credit_note_desc']['cheque'] += $credit_note_desc['cheque'];
				}
				
				
				$credit_quotation_desc = unserialize($dashboard['credit_quotation_desc']);
				if(!array_key_exists('cash',$normalUserDataSum['credit_quotation_desc'])){
					$normalUserDataSum['credit_quotation_desc'] = ['cash' => $credit_quotation_desc['cash'],
														  'card' => $credit_quotation_desc['card'],
														  'credit' => $credit_quotation_desc['credit'],
														  'bank_transfer' => $credit_quotation_desc['bank_transfer'],
														  'cheque' => $credit_quotation_desc['cheque'],
														  ];
					//$normalUserDataSum['credit_quotation_desc']['card'] = $credit_quotation_desc['card'];
					//$normalUserDataSum['credit_quotation_desc']['credit'] = $credit_quotation_desc['credit'];
					//$normalUserDataSum['credit_quotation_desc']['bank_transfer'] = $credit_quotation_desc['bank_transfer'];
					//$normalUserDataSum['credit_quotation_desc']['cheque'] = $credit_quotation_desc['cheque'];
				}else{
					$normalUserDataSum['credit_quotation_desc']['cash'] += $credit_quotation_desc['cash'];
					$normalUserDataSum['credit_quotation_desc']['card'] += $credit_quotation_desc['card'];
					$normalUserDataSum['credit_quotation_desc']['credit'] += $credit_quotation_desc['credit'];
					$normalUserDataSum['credit_quotation_desc']['bank_transfer'] += $credit_quotation_desc['bank_transfer'];
					$normalUserDataSum['credit_quotation_desc']['cheque'] += $credit_quotation_desc['cheque'];
				}
				
				
				$bulk_mobile_sale_desc = unserialize($dashboard['bulk_mobile_sale_desc']);
				if(!array_key_exists('cash',$normalUserDataSum['bulk_mobile_sale_desc'])){
					$normalUserDataSum['bulk_mobile_sale_desc'] = ['cash' => $bulk_mobile_sale_desc['cash'],
														  'card' => $bulk_mobile_sale_desc['card'],
														  ];
					//$normalUserDataSum['bulk_mobile_sale_desc']['card'] = $bulk_mobile_sale_desc['card'];
				}else{
					$normalUserDataSum['bulk_mobile_sale_desc']['cash'] += $bulk_mobile_sale_desc['cash'];
					$normalUserDataSum['bulk_mobile_sale_desc']['card'] += $bulk_mobile_sale_desc['card'];
				}
				
				$mobile_sale_desc = unserialize($dashboard['mobile_sale_desc']);
				if(!array_key_exists('cash',$normalUserDataSum['mobile_sale_desc'])){
					$normalUserDataSum['mobile_sale_desc'] = ['cash' => $mobile_sale_desc['cash'],
													  'card' => $mobile_sale_desc['card'],
													  ];
					//$normalUserDataSum['mobile_sale_desc']['card'] = $mobile_sale_desc['card'];
				}else{
					$normalUserDataSum['mobile_sale_desc']['cash'] += $mobile_sale_desc['cash'];
					$normalUserDataSum['mobile_sale_desc']['card'] += $mobile_sale_desc['card'];
				}
				
				
				$net_card_desc = unserialize($dashboard['net_card_desc']);
				if(!array_key_exists('repair',$normalUserDataSum['net_card_desc'])){
					//pr($net_card_desc);
					$normalUserDataSum['net_card_desc'] = ['repair' => $net_card_desc['repair'],
												    'Unlock' => $net_card_desc['Unlock'],
												    'Product' => $net_card_desc['Product'],
												    'Blk' => $net_card_desc['Blk'],
												    'Mobile' => $net_card_desc['Mobile'],
												    'credit_note' => $net_card_desc['credit_note'],
													'prev_recpts_sale' => $net_card_desc['prev_recpts_sale'],
												    ];
					//$normalUserDataSum['net_card_desc']['Unlock'] = $net_card_desc['Unlock'];
					//$normalUserDataSum['net_card_desc']['Product'] = $net_card_desc['Product'];
					//$normalUserDataSum['net_card_desc']['Blk'] = $net_card_desc['Blk'];
					//$normalUserDataSum['net_card_desc']['Mobile'] = $net_card_desc['Mobile'];
					////$normalUserDataSum['net_card_desc']['special'] = $net_card_desc['special'];
					////$normalUserDataSum['net_card_desc']['prev_recpts_sale'] = $net_card_desc['prev_recpts_sale'];
					//$normalUserDataSum['net_card_desc']['credit_note'] = $net_card_desc['credit_note'];
					//$normalUserDataSum['net_card_desc']['special_credit_note'] = $net_card_desc['special_credit_note'];
					
				}else{
					
					$normalUserDataSum['net_card_desc']['repair'] += $net_card_desc['repair'];
					$normalUserDataSum['net_card_desc']['Unlock'] += $net_card_desc['Unlock'];
					$normalUserDataSum['net_card_desc']['Product'] += $net_card_desc['Product'];
					$normalUserDataSum['net_card_desc']['Blk'] += $net_card_desc['Blk'];
					$normalUserDataSum['net_card_desc']['Mobile'] += $net_card_desc['Mobile'];
					//$normalUserDataSum['net_card_desc']['special'] += $net_card_desc['special'];
					//$normalUserDataSum['net_card_desc']['prev_recpts_sale'] += $net_card_desc['prev_recpts_sale'];
					$normalUserDataSum['net_card_desc']['credit_note'] += $net_card_desc['credit_note'];
					//$normalUserDataSum['net_card_desc']['special_credit_note'] += $net_card_desc['special_credit_note'];
				}
				
				
				$net_bnk_tnsfer_desc = unserialize($dashboard['net_bnk_tnsfer_desc']);
				if(!array_key_exists('0',$normalUserDataSum['net_bnk_tnsfer_desc'])){
					$normalUserDataSum['net_bnk_tnsfer_desc'] = [0 => $net_bnk_tnsfer_desc[0],
														1 => $net_bnk_tnsfer_desc[1],
														];
					//$normalUserDataSum['net_bnk_tnsfer_desc'][1] = $net_bnk_tnsfer_desc[1];
				}else{
					$normalUserDataSum['net_bnk_tnsfer_desc'][0] += $net_bnk_tnsfer_desc[0];
					$normalUserDataSum['net_bnk_tnsfer_desc'][1] += $net_bnk_tnsfer_desc[1];
				}
				
				$net_cheque_payment_desc = unserialize($dashboard['net_cheque_payment_desc']);
				if(!array_key_exists('0',$normalUserDataSum['net_cheque_payment_desc'])){
					$normalUserDataSum['net_cheque_payment_desc'] = [0 => $net_cheque_payment_desc[0],
														    1 => $net_cheque_payment_desc[1],
														    ];
					//$normalUserDataSum['net_cheque_payment_desc'][1] = $net_cheque_payment_desc[1];
				}else{
					$normalUserDataSum['net_cheque_payment_desc'][0] += $net_cheque_payment_desc[0];
					$normalUserDataSum['net_cheque_payment_desc'][1] += $net_cheque_payment_desc[1];
				}
				
				
				$cash_in_hand_desc = unserialize($dashboard['cash_in_hand_desc']);
				if(!array_key_exists('sale',$normalUserDataSum['cash_in_hand_desc'])){
					$normalUserDataSum['cash_in_hand_desc'] = ['sale' =>['Repair' => $cash_in_hand_desc['sale']['Repair'],
															   'Unlock' => $cash_in_hand_desc['sale']['Unlock'],
															   'Product' => $cash_in_hand_desc['sale']['Product'],
															   'Blk' => $cash_in_hand_desc['sale']['Blk'],
															   'Repair' => $cash_in_hand_desc['sale']['Repair'],
															   'Mobile' => $cash_in_hand_desc['sale']['Mobile'],
															   'prv_recpit_amt' => $cash_in_hand_desc['sale']['prv_recpit_amt'],
															],
													   'refund' =>['Repair' => $cash_in_hand_desc['refund']['Repair'],
															   'Unlock' => $cash_in_hand_desc['refund']['Unlock'],
															   'Product' => $cash_in_hand_desc['refund']['Product'],
															   'Blk' => $cash_in_hand_desc['refund']['Blk'],
															   'Repair' => $cash_in_hand_desc['refund']['Repair'],
															   'Mobile' => $cash_in_hand_desc['refund']['Mobile'],
															   'Credit_Note' => $cash_in_hand_desc['refund']['Credit_Note'],
															   'Mobile_Purchase' => $cash_in_hand_desc['refund']['Mobile_Purchase'],
															]
													   ];
					//$normalUserDataSum['cash_in_hand_desc']['sale']['Unlock'] = $cash_in_hand_desc['sale']['Unlock'];
					//$normalUserDataSum['cash_in_hand_desc']['sale']['Product'] = $cash_in_hand_desc['sale']['Product'];
					//$normalUserDataSum['cash_in_hand_desc']['sale']['Blk'] = $cash_in_hand_desc['sale']['Blk'];
					//$normalUserDataSum['cash_in_hand_desc']['sale']['Mobile'] = $cash_in_hand_desc['sale']['Mobile'];
					//$normalUserDataSum['cash_in_hand_desc']['sale']['special'] = $cash_in_hand_desc['sale']['special'];
//					$normalUserDataSum['cash_in_hand_desc']['sale']['prev_recpts_sale'] = $cash_in_hand_desc['sale']['prev_recpts_sale'];
					//$normalUserDataSum['cash_in_hand_desc']['sale']['credit_note'] = $cash_in_hand_desc['sale']['credit_note'];
					//$normalUserDataSum['cash_in_hand_desc']['sale']['special_credit_note'] = $cash_in_hand_desc['sale']['special_credit_note'];
					
					//$normalUserDataSum['cash_in_hand_desc']['refund']['Repair'] = $cash_in_hand_desc['refund']['Repair'];
					//$normalUserDataSum['cash_in_hand_desc']['refund']['Unlock'] = $cash_in_hand_desc['refund']['Unlock'];
					//$normalUserDataSum['cash_in_hand_desc']['refund']['Product'] = $cash_in_hand_desc['refund']['Product'];
					//$normalUserDataSum['cash_in_hand_desc']['refund']['Blk'] = $cash_in_hand_desc['refund']['Blk'];
					//$normalUserDataSum['cash_in_hand_desc']['refund']['Mobile'] = $cash_in_hand_desc['refund']['Mobile'];
					//$normalUserDataSum['cash_in_hand_desc']['refund']['Mobile'] = $cash_in_hand_desc['refund']['Credit_Note'];
					//$normalUserDataSum['cash_in_hand_desc']['refund']['Mobile'] = $cash_in_hand_desc['refund']['Mobile_Purchase'];
					
					//$normalUserDataSum['cash_in_hand_desc']['sale']['special'] = $cash_in_hand_desc['sale']['special'];
					//$normalUserDataSum['cash_in_hand_desc']['refund']['prev_recpts_sale'] = $cash_in_hand_desc['refund']['prev_recpts_sale'];
					
					
				}else{
					//pr($normalUserDataSum);die;
					$normalUserDataSum['cash_in_hand_desc']['sale']['Repair'] += $cash_in_hand_desc['sale']['Repair'];
					$normalUserDataSum['cash_in_hand_desc']['sale']['Unlock'] += $cash_in_hand_desc['sale']['Unlock'];
					$normalUserDataSum['cash_in_hand_desc']['sale']['Product'] += $cash_in_hand_desc['sale']['Product'];
					$normalUserDataSum['cash_in_hand_desc']['sale']['Blk'] += $cash_in_hand_desc['sale']['Blk'];
					$normalUserDataSum['cash_in_hand_desc']['sale']['Mobile'] += $cash_in_hand_desc['sale']['Mobile'];
					//$normalUserDataSum['cash_in_hand_desc']['sale']['special'] += $cash_in_hand_desc['sale']['special'];
					//$normalUserDataSum['cash_in_hand_desc']['sale']['prev_recpts_sale'] += $cash_in_hand_desc['sale']['prev_recpts_sale'];
					//$normalUserDataSum['cash_in_hand_desc']['sale']['credit_note'] = $cash_in_hand_desc['sale']['credit_note'];
					//$normalUserDataSum['cash_in_hand_desc']['sale']['special_credit_note'] = $cash_in_hand_desc['sale']['special_credit_note'];
					
					
					$normalUserDataSum['cash_in_hand_desc']['refund']['Repair'] += $cash_in_hand_desc['refund']['Repair'];
					$normalUserDataSum['cash_in_hand_desc']['refund']['Unlock'] += $cash_in_hand_desc['refund']['Unlock'];
					$normalUserDataSum['cash_in_hand_desc']['refund']['Product'] += $cash_in_hand_desc['refund']['Product'];
					$normalUserDataSum['cash_in_hand_desc']['refund']['Blk'] += $cash_in_hand_desc['refund']['Blk'];
					$normalUserDataSum['cash_in_hand_desc']['refund']['Mobile'] += $cash_in_hand_desc['refund']['Mobile'];
					$normalUserDataSum['cash_in_hand_desc']['refund']['Credit_Note'] += $cash_in_hand_desc['refund']['Credit_Note'];
					$normalUserDataSum['cash_in_hand_desc']['refund']['Mobile_Purchase'] += $cash_in_hand_desc['refund']['Mobile_Purchase'];
				}
				
				
				
				$credit_to_cash_desc = unserialize($dashboard['credit_to_cash_desc']);
				if(!array_key_exists('invoice_cash',$normalUserDataSum['credit_to_cash_desc'])){
					$normalUserDataSum['credit_to_cash_desc'] = ['invoice_cash' => $credit_to_cash_desc['invoice_cash'],
														'Quotation_cash' => $credit_to_cash_desc['Quotation_cash'],
														'credit_cash' => $credit_to_cash_desc['credit_cash'],
														'credit_quotation_cash' => $credit_to_cash_desc['credit_quotation_cash'],
														];
					//$normalUserDataSum['credit_to_cash_desc']['Quotation_cash'] = $credit_to_cash_desc['Quotation_cash'];
					//$normalUserDataSum['credit_to_cash_desc']['credit_cash'] = $credit_to_cash_desc['credit_cash'];
					//$normalUserDataSum['credit_to_cash_desc']['credit_quotation_cash'] = $credit_to_cash_desc['credit_quotation_cash'];
					
					
				}else{
					$normalUserDataSum['credit_to_cash_desc']['invoice_cash'] += $credit_to_cash_desc['invoice_cash'];
					$normalUserDataSum['credit_to_cash_desc']['Quotation_cash'] += $credit_to_cash_desc['Quotation_cash'];
					$normalUserDataSum['credit_to_cash_desc']['credit_cash'] += $credit_to_cash_desc['credit_cash'];
					$normalUserDataSum['credit_to_cash_desc']['credit_quotation_cash'] += $credit_to_cash_desc['credit_quotation_cash'];
				}
				
				$credit_to_other_payment_desc = unserialize($dashboard['credit_to_other_payment_desc']);
				if(!array_key_exists('total_bank_transfer',$normalUserDataSum['credit_to_other_payment_desc'])){
					$normalUserDataSum['credit_to_other_payment_desc'] = ['total_bank_transfer' => $credit_to_other_payment_desc['total_bank_transfer'],
															    'total_card_Payment' => $credit_to_other_payment_desc['total_card_Payment'],
															    'total_cheque_payment' => $credit_to_other_payment_desc['total_cheque_payment'],
															    ];
					//$normalUserDataSum['credit_to_other_payment_desc']['total_card_Payment'] = $credit_to_other_payment_desc['total_card_Payment'];
					//$normalUserDataSum['credit_to_other_payment_desc']['total_cheque_payment'] = $credit_to_other_payment_desc['total_cheque_payment'];
				}else{
					$normalUserDataSum['credit_to_other_payment_desc']['total_bank_transfer'] += $credit_to_other_payment_desc['total_bank_transfer'];
					$normalUserDataSum['credit_to_other_payment_desc']['total_card_Payment'] += $credit_to_other_payment_desc['total_card_Payment'];
					$normalUserDataSum['credit_to_other_payment_desc']['total_cheque_payment'] += $credit_to_other_payment_desc['total_cheque_payment'];
				}
				
				
				
				
				
				
				$normalUserDataSum['repair_refund'] += $dashboard['repair_refund'];
				$normalUserDataSum['unlock_sale'] += $dashboard['unlock_sale'];
				$normalUserDataSum['unlock_refund'] += $dashboard['unlock_refund'];
				$normalUserDataSum['product_sale'] += $dashboard['product_sale'];
				$normalUserDataSum['quotation'] += (float)$dashboard['quotation'];
				$normalUserDataSum['credit_note'] += $dashboard['credit_note'];
				$normalUserDataSum['credit_quotation'] += $dashboard['credit_quotation'];
				$normalUserDataSum['product_refund'] += $dashboard['product_refund'];
				$normalUserDataSum['bulk_mobile_sale'] += $dashboard['bulk_mobile_sale'];
				$normalUserDataSum['bulk_mobile_refund'] += $dashboard['bulk_mobile_refund'];
				$normalUserDataSum['mobile_sale'] += $dashboard['mobile_sale'];
				$normalUserDataSum['mobile_purchase'] += $dashboard['mobile_purchase'];
				$normalUserDataSum['mobile_refund'] += $dashboard['mobile_refund'];
				$normalUserDataSum['total_sale'] += $dashboard['total_sale'];
				$normalUserDataSum['total_refund'] += $dashboard['total_refund'];
				$normalUserDataSum['net_sale'] += $dashboard['net_sale'];
				
				$normalUserDataSum['net_card'] += $dashboard['net_card'];
				$normalUserDataSum['net_credit'] += $dashboard['net_credit'];
				$normalUserDataSum['net_bnk_tnsfer'] += $dashboard['net_bnk_tnsfer'];
				$normalUserDataSum['net_cheque_payment'] += $dashboard['net_cheque_payment'];
				$normalUserDataSum['cash_in_hand'] += $dashboard['cash_in_hand'];
				
				$normalUserDataSum['credit_to_cash'] += $dashboard['credit_to_cash'];
				$normalUserDataSum['credit_to_other_payment'] += $dashboard['credit_to_other_payment'];
			}
			if($dashboard['user_type'] == 'other'){
			   
			   $otherUserDataSum['repair_sale'] += $dashboard['repair_sale'];
				$repair_sale_desc = unserialize($dashboard['repair_sale_desc']);
				//pr($repair_sale_desc);die;
				if(!array_key_exists('cash',$otherUserDataSum['repair_sale_desc'])){
					$otherUserDataSum['repair_sale_desc'] = ['cash' => $repair_sale_desc['cash'],
                                                             'card' => $repair_sale_desc['card'],
                                                             ]; 
					//$otherUserDataSum['repair_sale_desc']['cash'] = $repair_sale_desc['cash'];
					//$otherUserDataSum['repair_sale_desc']['card'] = $repair_sale_desc['card'];
				}else{
					$otherUserDataSum['repair_sale_desc']['cash'] += $repair_sale_desc['cash'];
					$otherUserDataSum['repair_sale_desc']['card'] += $repair_sale_desc['card'];
				}
				//pr($otherUserDataSum);die;
				$unlock_sale_desc = unserialize($dashboard['unlock_sale_desc']);
				if(!array_key_exists('cash',$otherUserDataSum['unlock_sale_desc'])){
					$otherUserDataSum['unlock_sale_desc'] = ['cash' =>$unlock_sale_desc['cash'],
													 'card' =>$unlock_sale_desc['card']
													];
					//$otherUserDataSum['unlock_sale_desc']['card'] = $unlock_sale_desc['card'];
				}else{
					$otherUserDataSum['unlock_sale_desc']['cash'] += $unlock_sale_desc['cash'];
					$otherUserDataSum['unlock_sale_desc']['card'] += $unlock_sale_desc['card'];
				}
				
				$product_sale_desc = unserialize($dashboard['product_sale_desc']);
				if(!array_key_exists('cash',$otherUserDataSum['product_sale_desc'])){
					$otherUserDataSum['product_sale_desc'] = ['cash' => $product_sale_desc['cash'],
													  'card' => $product_sale_desc['card'],
													  'credit' => $product_sale_desc['credit'],
													  'bank_transfer' => $product_sale_desc['bank_transfer'],
													  'cheque' => $product_sale_desc['cheque'],
													  ];
					//$otherUserDataSum['product_sale_desc']['card'] = $product_sale_desc['card'];
					//$otherUserDataSum['product_sale_desc']['credit'] = $product_sale_desc['credit'];
					//$otherUserDataSum['product_sale_desc']['bank_transfer'] = $product_sale_desc['bank_transfer'];
					//$otherUserDataSum['product_sale_desc']['cheque'] = $product_sale_desc['cheque'];
				}else{
					$otherUserDataSum['product_sale_desc']['cash'] += $product_sale_desc['cash'];
					$otherUserDataSum['product_sale_desc']['card'] += $product_sale_desc['card'];
					$otherUserDataSum['product_sale_desc']['credit'] += $product_sale_desc['credit'];
					$otherUserDataSum['product_sale_desc']['bank_transfer'] += $product_sale_desc['bank_transfer'];
					$otherUserDataSum['product_sale_desc']['cheque'] += $product_sale_desc['cheque'];
				}
				//pr($otherUserDataSum);die;
				$quotation_desc = unserialize($dashboard['quotation_desc']);
				if(!array_key_exists('cash',$otherUserDataSum['quotation_desc'])){
					$otherUserDataSum['quotation_desc'] = ['cash' => $quotation_desc['cash'],
												    'card' => $quotation_desc['card'],
												    'credit' => $quotation_desc['credit'],
												    'bank_transfer' => $quotation_desc['bank_transfer'],
												    'cheque' => $quotation_desc['cheque'],
												    ];
					//$otherUserDataSum['quotation_desc']['card'] = $quotation_desc['card'];
					//$otherUserDataSum['quotation_desc']['credit'] = $quotation_desc['credit'];
					//$otherUserDataSum['quotation_desc']['bank_transfer'] = $quotation_desc['bank_transfer'];
					//$otherUserDataSum['quotation_desc']['cheque'] = $quotation_desc['cheque'];
				}else{
					$otherUserDataSum['quotation_desc']['cash'] += $quotation_desc['cash'];
					$otherUserDataSum['quotation_desc']['card'] += $quotation_desc['card'];
					$otherUserDataSum['quotation_desc']['credit'] += $quotation_desc['credit'];
					$otherUserDataSum['quotation_desc']['bank_transfer'] += $quotation_desc['bank_transfer'];
					$otherUserDataSum['quotation_desc']['cheque'] += $quotation_desc['cheque'];
				}
				
				$credit_note_desc = unserialize($dashboard['credit_note_desc']);
				if(!array_key_exists('cash',$otherUserDataSum['credit_note_desc'])){
					$otherUserDataSum['credit_note_desc'] = ['cash' => $credit_note_desc['cash'],
												   'card' => $credit_note_desc['card'],
												   'credit' => $credit_note_desc['credit'],
												   'bank_transfer' => $credit_note_desc['bank_transfer'],
												   'cheque' => $credit_note_desc['cheque'],
												   ];
					//$otherUserDataSum['credit_note_desc']['card'] = $credit_note_desc['card'];
					//$otherUserDataSum['credit_note_desc']['credit'] = $credit_note_desc['credit'];
					//$otherUserDataSum['credit_note_desc']['bank_transfer'] = $credit_note_desc['bank_transfer'];
					//$otherUserDataSum['credit_note_desc']['cheque'] = $credit_note_desc['cheque'];
				}else{
					$otherUserDataSum['credit_note_desc']['cash'] += $credit_note_desc['cash'];
					$otherUserDataSum['credit_note_desc']['card'] += $credit_note_desc['card'];
					$otherUserDataSum['credit_note_desc']['credit'] += $credit_note_desc['credit'];
					$otherUserDataSum['credit_note_desc']['bank_transfer'] += $credit_note_desc['bank_transfer'];
					$otherUserDataSum['credit_note_desc']['cheque'] += $credit_note_desc['cheque'];
				}
				
				
				$credit_quotation_desc = unserialize($dashboard['credit_quotation_desc']);
				if(!array_key_exists('cash',$otherUserDataSum['credit_quotation_desc'])){
					$otherUserDataSum['credit_quotation_desc'] = ['cash' => $credit_quotation_desc['cash'],
														 'card' => $credit_quotation_desc['card'],
														 'credit' => $credit_quotation_desc['credit'],
														 'bank_transfer' => $credit_quotation_desc['bank_transfer'],
														 'cheque' => $credit_quotation_desc['cheque'],
														];
					//$otherUserDataSum['credit_quotation_desc']['card'] = $credit_quotation_desc['card'];
					//$otherUserDataSum['credit_quotation_desc']['credit'] = $credit_quotation_desc['credit'];
					//$otherUserDataSum['credit_quotation_desc']['bank_transfer'] = $credit_quotation_desc['bank_transfer'];
					//$otherUserDataSum['credit_quotation_desc']['cheque'] = $credit_quotation_desc['cheque'];
				}else{
					$otherUserDataSum['credit_quotation_desc']['cash'] += $credit_quotation_desc['cash'];
					$otherUserDataSum['credit_quotation_desc']['card'] += $credit_quotation_desc['card'];
					$otherUserDataSum['credit_quotation_desc']['credit'] += $credit_quotation_desc['credit'];
					$otherUserDataSum['credit_quotation_desc']['bank_transfer'] += $credit_quotation_desc['bank_transfer'];
					$otherUserDataSum['credit_quotation_desc']['cheque'] += $credit_quotation_desc['cheque'];
				}
				
				
				$bulk_mobile_sale_desc = unserialize($dashboard['bulk_mobile_sale_desc']);
				if(!array_key_exists('cash',$otherUserDataSum['bulk_mobile_sale_desc'])){
					$otherUserDataSum['bulk_mobile_sale_desc'] = ['cash' => $bulk_mobile_sale_desc['cash'],
														 'card' => $bulk_mobile_sale_desc['card'],
														 
														];
					//$otherUserDataSum['bulk_mobile_sale_desc']['card'] = $bulk_mobile_sale_desc['card'];
				}else{
					$otherUserDataSum['bulk_mobile_sale_desc']['cash'] += $bulk_mobile_sale_desc['cash'];
					$otherUserDataSum['bulk_mobile_sale_desc']['card'] += $bulk_mobile_sale_desc['card'];
				}
				
				$mobile_sale_desc = unserialize($dashboard['mobile_sale_desc']);
				if(!array_key_exists('cash',$otherUserDataSum['mobile_sale_desc'])){
					$otherUserDataSum['mobile_sale_desc'] = ['cash' => $mobile_sale_desc['cash'],
													 'card' => $mobile_sale_desc['card']
													];
					//$otherUserDataSum['mobile_sale_desc']['card'] = $mobile_sale_desc['card'];
				}else{
					$otherUserDataSum['mobile_sale_desc']['cash'] += $mobile_sale_desc['cash'];
					$otherUserDataSum['mobile_sale_desc']['card'] += $mobile_sale_desc['card'];
				}
				
				
				$net_card_desc = unserialize($dashboard['net_card_desc']);
				if(!array_key_exists('repair',$otherUserDataSum['net_card_desc'])){
					$otherUserDataSum['net_card_desc'] = ['repair' => $net_card_desc['repair'],
												   'Unlock' => $net_card_desc['Unlock'],
												   'Product' => $net_card_desc['Product'],
												   'Blk' => $net_card_desc['Blk'],
												   'Mobile' => $net_card_desc['Mobile'],
												   'special' => $net_card_desc['special'],
												   'prev_recpts_sale' => $net_card_desc['prev_recpts_sale'],
												   'credit_note' => $net_card_desc['credit_note'],
												   'special_credit_note' => $net_card_desc['special_credit_note'],
												   ];
					//$otherUserDataSum['net_card_desc']['Unlock'] = $net_card_desc['Unlock'];
					//$otherUserDataSum['net_card_desc']['Product'] = $net_card_desc['Product'];
					//$otherUserDataSum['net_card_desc']['Blk'] = $net_card_desc['Blk'];
					//$otherUserDataSum['net_card_desc']['Mobile'] = $net_card_desc['Mobile'];
					//$normalUserDataSum['net_card_desc']['special'] = $net_card_desc['special'];
					//$normalUserDataSum['net_card_desc']['prev_recpts_sale'] = $net_card_desc['prev_recpts_sale'];
					//$otherUserDataSum['net_card_desc']['credit_note'] = $net_card_desc['credit_note'];
					//$normalUserDataSum['net_card_desc']['special_credit_note'] = $net_card_desc['special_credit_note'];
					
				}else{
					//pr($otherUserDataSum);die;
					$otherUserDataSum['net_card_desc']['repair'] += $net_card_desc['repair'];
					$otherUserDataSum['net_card_desc']['Unlock'] += $net_card_desc['Unlock'];
					$otherUserDataSum['net_card_desc']['Product'] += $net_card_desc['Product'];
					$otherUserDataSum['net_card_desc']['Blk'] += $net_card_desc['Blk'];
					$otherUserDataSum['net_card_desc']['Mobile'] += $net_card_desc['Mobile'];
					$otherUserDataSum['net_card_desc']['special'] += $net_card_desc['special'];
					$otherUserDataSum['net_card_desc']['prev_recpts_sale'] += $net_card_desc['prev_recpts_sale'];
					$otherUserDataSum['net_card_desc']['credit_note'] += $net_card_desc['credit_note'];
					$otherUserDataSum['net_card_desc']['special_credit_note'] += $net_card_desc['special_credit_note'];
				}
				
				
				$net_bnk_tnsfer_desc = unserialize($dashboard['net_bnk_tnsfer_desc']);
				if(!array_key_exists('0',$otherUserDataSum['net_bnk_tnsfer_desc'])){
					$otherUserDataSum['net_bnk_tnsfer_desc'] = [0 => $net_bnk_tnsfer_desc[0],
													    1 => $net_bnk_tnsfer_desc[1]
													    ];
					//$otherUserDataSum['net_bnk_tnsfer_desc'][1] = $net_bnk_tnsfer_desc[1];
				}else{
					$otherUserDataSum['net_bnk_tnsfer_desc'][0] += $net_bnk_tnsfer_desc[0];
					$otherUserDataSum['net_bnk_tnsfer_desc'][1] += $net_bnk_tnsfer_desc[1];
				}
				
				$net_cheque_payment_desc = unserialize($dashboard['net_cheque_payment_desc']);
				if(!array_key_exists('0',$otherUserDataSum['net_cheque_payment_desc'])){
					$otherUserDataSum['net_cheque_payment_desc'] = [0 => $net_cheque_payment_desc[0],
														   1 => $net_cheque_payment_desc[1]
														   ];
					//$otherUserDataSum['net_cheque_payment_desc'][1] = $net_cheque_payment_desc[1];
				}else{
					$otherUserDataSum['net_cheque_payment_desc'][0] += $net_cheque_payment_desc[0];
					$otherUserDataSum['net_cheque_payment_desc'][1] += $net_cheque_payment_desc[1];
				}
				
				
				$cash_in_hand_desc = unserialize($dashboard['cash_in_hand_desc']);
				//pr($cash_in_hand_desc);die;
				if(!array_key_exists('sale',$otherUserDataSum['cash_in_hand_desc'])){
					$otherUserDataSum['cash_in_hand_desc'] = ['sale' => ['Repair' => $cash_in_hand_desc['sale']['Repair'],
															   'Unlock' => $cash_in_hand_desc['sale']['Unlock'],
															   'Product' => $cash_in_hand_desc['sale']['Product'],
															   'Blk' => $cash_in_hand_desc['sale']['Blk'],
															   'Mobile' => $cash_in_hand_desc['sale']['Mobile'],
															   'special' => $cash_in_hand_desc['sale']['special'],
															   'prv_recpit_amt' => $cash_in_hand_desc['sale']['prv_recpit_amt'],
															   'prv_credit_to_cash' => $cash_in_hand_desc['sale']['prv_credit_to_cash'],
															],
													  'refund' =>[
														   'Repair' => $cash_in_hand_desc['refund']['Repair'],
															   'Unlock' => $cash_in_hand_desc['refund']['Unlock'],
															   'Product' => $cash_in_hand_desc['refund']['Product'],
															   'Blk' => $cash_in_hand_desc['refund']['Blk'],
															   'Mobile' => $cash_in_hand_desc['refund']['Mobile'],
															   'Credit_Note' => $cash_in_hand_desc['refund']['Credit_Note'],
															   'special_credit_note' => $cash_in_hand_desc['refund']['special_credit_note'],
															   'Mobile_Purchase' => $cash_in_hand_desc['refund']['Mobile_Purchase'],
														  ]
													  ];
					//$otherUserDataSum['cash_in_hand_desc']['sale']['Unlock'] = $cash_in_hand_desc['sale']['Unlock'];
					//$otherUserDataSum['cash_in_hand_desc']['sale']['Product'] = $cash_in_hand_desc['sale']['Product'];
					//$otherUserDataSum['cash_in_hand_desc']['sale']['Blk'] = $cash_in_hand_desc['sale']['Blk'];
					//$otherUserDataSum['cash_in_hand_desc']['sale']['Mobile'] = $cash_in_hand_desc['sale']['Mobile'];
					//$normalUserDataSum['cash_in_hand_desc']['sale']['special'] = $cash_in_hand_desc['sale']['special'];
//					$normalUserDataSum['cash_in_hand_desc']['sale']['prev_recpts_sale'] = $cash_in_hand_desc['sale']['prev_recpts_sale'];
					//$normalUserDataSum['cash_in_hand_desc']['sale']['credit_note'] = $cash_in_hand_desc['sale']['credit_note'];
					//$normalUserDataSum['cash_in_hand_desc']['sale']['special_credit_note'] = $cash_in_hand_desc['sale']['special_credit_note'];
					
					//$otherUserDataSum['cash_in_hand_desc']['refund']['Repair'] = $cash_in_hand_desc['refund']['Repair'];
					//$otherUserDataSum['cash_in_hand_desc']['refund']['Unlock'] = $cash_in_hand_desc['refund']['Unlock'];
					//$otherUserDataSum['cash_in_hand_desc']['refund']['Product'] = $cash_in_hand_desc['refund']['Product'];
					//$otherUserDataSum['cash_in_hand_desc']['refund']['Blk'] = $cash_in_hand_desc['refund']['Blk'];
					//$otherUserDataSum['cash_in_hand_desc']['refund']['Mobile'] = $cash_in_hand_desc['refund']['Mobile'];
					//$otherUserDataSum['cash_in_hand_desc']['refund']['Mobile'] = $cash_in_hand_desc['refund']['Credit_Note'];
					//$otherUserDataSum['cash_in_hand_desc']['refund']['Mobile'] = $cash_in_hand_desc['refund']['Mobile_Purchase'];
					
					//$normalUserDataSum['cash_in_hand_desc']['sale']['special'] = $cash_in_hand_desc['sale']['special'];
					//$normalUserDataSum['cash_in_hand_desc']['refund']['prev_recpts_sale'] = $cash_in_hand_desc['refund']['prev_recpts_sale'];
					
					
				}else{
					//pr($otherUserDataSum);die;
					$otherUserDataSum['cash_in_hand_desc']['sale']['Repair'] += $cash_in_hand_desc['sale']['Repair'];
					$otherUserDataSum['cash_in_hand_desc']['sale']['Unlock'] += $cash_in_hand_desc['sale']['Unlock'];
					$otherUserDataSum['cash_in_hand_desc']['sale']['Product'] += $cash_in_hand_desc['sale']['Product'];
					$otherUserDataSum['cash_in_hand_desc']['sale']['Blk'] += $cash_in_hand_desc['sale']['Blk'];
					$otherUserDataSum['cash_in_hand_desc']['sale']['Mobile'] += $cash_in_hand_desc['sale']['Mobile'];
					
					$otherUserDataSum['cash_in_hand_desc']['sale']['special'] += $cash_in_hand_desc['sale']['special'];
					$otherUserDataSum['cash_in_hand_desc']['sale']['prv_recpit_amt'] += $cash_in_hand_desc['sale']['prv_recpit_amt'];
					$otherUserDataSum['cash_in_hand_desc']['sale']['prv_credit_to_cash'] += $cash_in_hand_desc['sale']['prv_credit_to_cash'];
					
					//$normalUserDataSum['cash_in_hand_desc']['sale']['special'] += $cash_in_hand_desc['sale']['special'];
					//$otherUserDataSum['cash_in_hand_desc']['sale']['prev_recpts_sale'] += $cash_in_hand_desc['sale']['prev_recpts_sale'];
					//$normalUserDataSum['cash_in_hand_desc']['sale']['credit_note'] = $cash_in_hand_desc['sale']['credit_note'];
					//$normalUserDataSum['cash_in_hand_desc']['sale']['special_credit_note'] = $cash_in_hand_desc['sale']['special_credit_note'];
					
					
					$otherUserDataSum['cash_in_hand_desc']['refund']['Repair'] += $cash_in_hand_desc['refund']['Repair'];
					$otherUserDataSum['cash_in_hand_desc']['refund']['Unlock'] += $cash_in_hand_desc['refund']['Unlock'];
					$otherUserDataSum['cash_in_hand_desc']['refund']['Product'] += $cash_in_hand_desc['refund']['Product'];
					$otherUserDataSum['cash_in_hand_desc']['refund']['Blk'] += $cash_in_hand_desc['refund']['Blk'];
					$otherUserDataSum['cash_in_hand_desc']['refund']['Mobile'] += $cash_in_hand_desc['refund']['Mobile'];
					$otherUserDataSum['cash_in_hand_desc']['refund']['Credit_Note'] += $cash_in_hand_desc['refund']['Credit_Note'];
					$otherUserDataSum['cash_in_hand_desc']['refund']['Mobile_Purchase'] += $cash_in_hand_desc['refund']['Mobile_Purchase'];
					$otherUserDataSum['cash_in_hand_desc']['refund']['special_credit_note'] += $cash_in_hand_desc['refund']['special_credit_note'];
				}
				
				
				
				$credit_to_cash_desc = unserialize($dashboard['credit_to_cash_desc']);
				if(!array_key_exists('invoice_cash',$otherUserDataSum['credit_to_cash_desc'])){
					$otherUserDataSum['credit_to_cash_desc'] = ['invoice_cash' => $credit_to_cash_desc['invoice_cash'],
													    'Quotation_cash' => $credit_to_cash_desc['Quotation_cash'],
													    'credit_cash' => $credit_to_cash_desc['credit_cash'],
													    'credit_quotation_cash' => $credit_to_cash_desc['credit_quotation_cash'],
													    ];
					//$otherUserDataSum['credit_to_cash_desc']['Quotation_cash'] = $credit_to_cash_desc['Quotation_cash'];
					//$otherUserDataSum['credit_to_cash_desc']['credit_cash'] = $credit_to_cash_desc['credit_cash'];
					//$otherUserDataSum['credit_to_cash_desc']['credit_quotation_cash'] = $credit_to_cash_desc['credit_quotation_cash'];
					
					
				}else{
					$otherUserDataSum['credit_to_cash_desc']['invoice_cash'] += $credit_to_cash_desc['invoice_cash'];
					$otherUserDataSum['credit_to_cash_desc']['Quotation_cash'] += $credit_to_cash_desc['Quotation_cash'];
					$otherUserDataSum['credit_to_cash_desc']['credit_cash'] += $credit_to_cash_desc['credit_cash'];
					$otherUserDataSum['credit_to_cash_desc']['credit_quotation_cash'] += $credit_to_cash_desc['credit_quotation_cash'];
				}
				
				$credit_to_other_payment_desc = unserialize($dashboard['credit_to_other_payment_desc']);
				if(!array_key_exists('total_bank_transfer',$otherUserDataSum['credit_to_other_payment_desc'])){
					$otherUserDataSum['credit_to_other_payment_desc'] = ['total_bank_transfer' => $credit_to_other_payment_desc['total_bank_transfer'],
															   'total_card_Payment' => $credit_to_other_payment_desc['total_card_Payment'],
															   'total_cheque_payment' => $credit_to_other_payment_desc['total_cheque_payment'],
															   ];
					//$otherUserDataSum['credit_to_other_payment_desc']['total_card_Payment'] = $credit_to_other_payment_desc['total_card_Payment'];
					//$otherUserDataSum['credit_to_other_payment_desc']['total_cheque_payment'] = $credit_to_other_payment_desc['total_cheque_payment'];
				}else{
					//pr($otherUserDataSum);die;
					$otherUserDataSum['credit_to_other_payment_desc']['total_bank_transfer'] += $credit_to_other_payment_desc['total_bank_transfer'];
					$otherUserDataSum['credit_to_other_payment_desc']['total_card_Payment'] += $credit_to_other_payment_desc['total_card_Payment'];
					$otherUserDataSum['credit_to_other_payment_desc']['total_cheque_payment'] += $credit_to_other_payment_desc['total_cheque_payment'];
				}
			   
			   
			   
				$otherUserDataSum['repair_sale'] += $dashboard['repair_sale'];
				$otherUserDataSum['repair_refund'] += $dashboard['repair_refund'];
				$otherUserDataSum['unlock_sale'] += $dashboard['unlock_sale'];
				$otherUserDataSum['unlock_refund'] += $dashboard['unlock_refund'];
				$otherUserDataSum['product_sale'] += $dashboard['product_sale'];
				$otherUserDataSum['quotation'] += $dashboard['quotation'];
				$otherUserDataSum['credit_note'] += $dashboard['credit_note'];
				$otherUserDataSum['credit_quotation'] += $dashboard['credit_quotation'];
				$otherUserDataSum['product_refund'] += $dashboard['product_refund'];
				$otherUserDataSum['bulk_mobile_sale'] += $dashboard['bulk_mobile_sale'];
				$otherUserDataSum['bulk_mobile_refund'] += $dashboard['bulk_mobile_refund'];
				$otherUserDataSum['mobile_sale'] += $dashboard['mobile_sale'];
				$otherUserDataSum['mobile_purchase'] += $dashboard['mobile_purchase'];
				$otherUserDataSum['mobile_refund'] += $dashboard['mobile_refund'];
				$otherUserDataSum['total_sale'] += $dashboard['total_sale'];
				$otherUserDataSum['total_refund'] += $dashboard['total_refund'];
				$otherUserDataSum['net_sale'] += $dashboard['net_sale'];
				
				$otherUserDataSum['net_card'] += $dashboard['net_card'];
				$otherUserDataSum['net_credit'] += $dashboard['net_credit'];
				$otherUserDataSum['net_bnk_tnsfer'] += $dashboard['net_bnk_tnsfer'];
				$otherUserDataSum['net_cheque_payment'] += $dashboard['net_cheque_payment'];
				$otherUserDataSum['cash_in_hand'] += $dashboard['cash_in_hand'];
				
				$otherUserDataSum['credit_to_cash'] += $dashboard['credit_to_cash'];
				$otherUserDataSum['credit_to_other_payment'] += $dashboard['credit_to_other_payment'];
			}
		}
		//pr($otherUserDataSum);
		//die;
		$kiosks_query = $this->Kiosks->find('list',
                                                [
                                                'keyField' => 'id',
                                                'valueField' => 'code',
                                                'conditions' => ['Kiosks.status' => 1],
									   'order'=>['name ASC']
                                                ]);
		$kiosks_query = $kiosks_query->hydrate(false);
		if(!empty($kiosks_query)){
			$kiosks = $kiosks_query->toArray();
		}else{
			$kiosks = array();
		}
		$users = array('Both','Normal','Other');
		//pr($kiosks);die;
		$this->set(compact('normalUserDataSum','otherUserDataSum','normalUserData','otherUserData','kiosks','users','dashboardData'));
		//pr($dashboard);die;
	}
	
	public function printLabel(){
		  $hidden_print_label_price = $this->request->data['selling_price_for_label'];
		  $print_label_price = $this->request->data['print_label_price'];
		  $id = $this->request->data['id'];
		  $product_data = $this->Products->find("all",['conditions'=>['id' => $id]])->toArray();
		  if(empty(trim($print_label_price))){
			   $print_label_price = $hidden_print_label_price;
		  }
		  $product_code = "";
		  if(!empty($product_data)){
			   $product_code = $product_data[0]->product_code;
		  }
		  $barcode = $this->Barcode->generate_bar_code($product_code,"png"); // html,png,svg,jpg
		  $setting_arr = $this->Settings->find("list",['keyField' => "attribute_name",
													   'valueField' => "attribute_value"
													   ])->toArray();
		  //$this->viewBuilder()->setLayout(false); 
		  $this->set(compact("product_data","print_label_price","product_code","barcode","setting_arr"));
	}
	
	public function getProductModels(){
        $additionalModel = 0;
        if(array_key_exists('model',$this->request->query)){$additionalModel = 1;}
		$brandId = $this->request->query('id');
		//$this->request->onlyAllow('ajax');
		$mobileModels_query = $this->ProductModels->find('list',array(
																				'keyField' =>'id',
																				'valueField' => 'model',
									'order'=>'model asc',
																				
								   'conditions'=>array(
									'brand_id'=>$brandId,
									//'MobileModel.status'=>1,
									//'MobileModel.id' => $activeModels
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
		if($additionalModel == 1){$this->render('get_product_additional_models');}
	}
    
    function deleteImage(){
		  if(array_key_exists('product_id',$_REQUEST)){
			   $productId = $_REQUEST['product_id'];
			   //$productId = '0101';
			   if(!empty($productId)){
					$msg = array();
					$kioskList_query = $this->Kiosks->find('list',
												  [
													   'keyField' => 'id',
													   'valueFiels' => 'name',
													   'conditions'=>['Kiosks.status'=>1]
												  ]
										 );
					$kioskList_query = $kioskList_query->hydrate(false);
					if(!empty($kioskList_query)){
						 $kioskList = $kioskList_query->toArray();
					}else{
						 $kioskList = array();
					}
					
					$tableNames = array('0'=>'products');
					foreach($kioskList as $kioskID => $kioskName){
						 $tableNames[] = "kiosk_{$kioskID}_products";
					}
					
					foreach($tableNames as $key => $tableName){
						 $conn = ConnectionManager::get('default');
						 $updateQry = "UPDATE `$tableName` SET `image`='',`image_dir`='' WHERE `id`=$productId";
						 $stmt = $conn->execute($updateQry);
						 if($stmt){
							  $msg[] = "sucess";	  
						 }else{
							  $msg[] = "fail";
						 }
						 
					}
                    //---------------------
                    $sites = Configure::read('sites');
                    if(!empty($sites)){
						 foreach($sites as $site_id => $site_value){
                            if(!empty($productId) && !empty($site_value)){
                                $this->delete_img_from_external_sites($productId, $site_value);
                            }
						 }
                        //reset connection to default
                        $conn = ConnectionManager::get('default');
                        $stmt = $conn->execute("SELECT `id`, `name` FROM `kiosks`");
                        $kiosks_data = $stmt ->fetchAll('assoc');
					}
                    //---------------------
					$directory = WWW_ROOT.'files/Products/image/'.$productId;
					if(file_exists($directory)){
						 $dir_name = scandir($directory);
						 if(!empty($dir_name)){
							  foreach($dir_name as $key => $value){
								   if($value !="." && $value !=".."){
										//if (preg_match("/vga_/", $value) || preg_match("/mini_/", $value) || preg_match("/thumb_/", $value) || preg_match("/xvga_/", $value)) {
											 $fileName = $directory."/".$value;
											 //echo $fileName;
											 if(file_exists($fileName)){
												  unlink($fileName);
												  $msg[] = "sucess";
											 }else{
												  $msg[] = "fail";
											 }
										 //}
								   }
							  }
						 }
						 //echo json_encode($msg);die;
					}else{
						 $msg = 'Directory not found';
					}
			   }
			   
		  }
		  echo json_encode($msg);die;
	 }
     
     private function delete_img_from_external_sites($productId, $site_value){
        $conn = ConnectionManager::get($site_value);
        $stmt = $conn->execute("SELECT `id`, `name` FROM `kiosks`");
        $kiosks_data = $stmt ->fetchAll('assoc');
        $msg = $kiosks = array();
        $tableNames = array('0' => 'products');
        foreach($kiosks_data as $key => $value){
            $kioskID = $value['id'];
            $kioskName = $value['name'];
            $tableNames[] = "kiosk_{$kioskID}_products";
		}
					
        foreach($tableNames as $key => $tableName){
             $updateQry = "UPDATE `$tableName` SET `image`='',`image_dir`='' WHERE `id` = $productId";
             $stmt = $conn->execute($updateQry);
             if($stmt){
                  $msg[$tableName] = "sucess";	  
             }else{
                  $msg[$tableName] = "fail";
             }
        }
        return $msg;
     }
	 
	 
	 public function update_transient_order($id,$price){
		  $update_query = "UPDATE `stock_transfer` SET `sale_price` = $price WHERE `product_id` = $id";
		  $conn = ConnectionManager::get('default');
		  $stmt = $conn->execute($update_query); 
	 }
	 
	 public function update_retail_transient_order($id,$price){
		  $sites = Configure::read('sites');
		  $update_query = "UPDATE `stock_transfer` SET `sale_price` = $price WHERE `product_id` = $id";
		  if(!empty($sites)){
			   foreach($sites as $key => $value){
					$conn = ConnectionManager::get($value);
					$stmt = $conn->execute($update_query); 
			   }
		  }
		  
	 }
}