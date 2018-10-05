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
use Cake\I18n\Time;
use Cake\Datasource\ConnectionManager;

class StockController extends AppController
{
     public $helpers = [
         'Paginator' => ['templates' => 'paginatortemplates']
         ];
    public function initialize()
    {
        parent::initialize();
		
		$this->loadComponent('SessionRestore');
        $this->loadComponent('ScreenHint');
        $this->loadModel('Categories');
        $this->loadModel('Products');
        $this->loadModel('UnderstockLevelOrders');
        $this->loadModel('Users');
        $this->loadModel('WarehouseStock');
		$this->loadModel('DailyStocks');
		$this->loadModel('DeadProducts');
		 $CURRENCY_TYPE = Configure::read('CURRENCY_TYPE');
		 $this->set(compact('CURRENCY_TYPE' ));
    }
    
    public function index() {
		$kiosk_id = $this->request->Session()->read('kiosk_id');		
		if(!empty($kiosk_id)){
			$productSource = "kiosk_{$kiosk_id}_products";
			$ProductTable = TableRegistry::get($productSource,[
														'table' => $productSource,
															]);
		}else{
			$productSource = "products";
			$ProductTable = TableRegistry::get($productSource,[
														'table' => $productSource,
															]);
		}
		//pr($ProductTable);die;
		$vat = $this->setting['vat'];
		$this->set('vat', $vat);
		$categories_query = $this->Categories->find('all',array(
								'fields' => array('id', 'category','id_name_path'),
								'conditions' => array('Categories.status' => 1),
								'order' => 'Categories.category asc',
								'recursive' => -1
								));
        $categories_query = $categories_query->hydrate(false);
        if(!empty($categories_query)){
         $categories  = $categories_query->toArray();
        }
		$displayType = "";
		if ($this->request->is(array('get', 'put'))) {
		  //pr($this->request);die;
			if(array_key_exists('display_type',$this->request->query)){
				$displayType = $this->request->query['display_type'];
				if($displayType=="show_all"){
					$this->paginate = array('limit' => ROWS_PER_PAGE);
					//$this->Product->recursive = 0;
				}elseif($displayType=="more_than_zero"){
					$this->paginate = array('limit' => ROWS_PER_PAGE,'conditions'=>array('NOT'=>array('quantity'=>0)));
					//$this->Product->recursive = 0;
				}
			}else{
			   $this->paginate = array('limit' => ROWS_PER_PAGE,'conditions' => array('NOT'=>array('quantity'=>0)));  
			}
		}else{
		  $this->paginate = array('limit' => ROWS_PER_PAGE,'conditions' => array('NOT'=>array('quantity'=>0)));  
		}
		
		$categories = $this->CustomOptions->category_options($categories,true);
        
		$products = $this->paginate($ProductTable);
		$categoryIdArr = array();
		foreach($products as $sngProduct){
			$categoryIdArr[] = $sngProduct['category_id'];
		}
		//pr($products);die;
        if(empty($categoryIdArr)){
		  $categoryIdArr = array(0 => null);
		}
		$categoryName = $this->Categories->find('list',[
                                                        'conditions' => ['Categories.id IN'=>$categoryIdArr],
                                                        'keyField'=> 'id',
                                                        'valueField' => 'category'
                                                       ]);
        if(!empty($categoryName)){
         $categoryName  = $categoryName->toArray();
        }
        //pr($categoryName);die;
		$hint = $this->ScreenHint->hint('stock','index');
        if(!$hint){
            $hint = "";
        }
		$this->set(compact('hint','categories','displayType','products','categoryName'));
	}
    
    public function search($keyword = "",$displayCondition = ""){
		$kiosk_id = $this->request->Session()->read('kiosk_id');		
		if(!empty($kiosk_id)){
			$productSource = "kiosk_{$kiosk_id}_products";
			$ProductTable = TableRegistry::get($productSource,[
														'table' => $productSource,
															]);
		}else{
			$productSource = "products";
			$ProductTable = TableRegistry::get($productSource,[
														'table' => $productSource,
															]);
		}
		$displayType = "";
		$vat = $this->setting['vat'];
		$this->set('vat', $vat);
		if(array_key_exists('display_type',$this->request->query)){
			$displayType = $this->request->query['display_type'];
		}
		if(array_key_exists('search_kw',$this->request->query)){
			$searchKW = trim(strtolower($this->request->query['search_kw']));
		}
		$conditionArr = array();
		if(!empty($searchKW)){
			$conditionArr['OR']['LOWER(product) like '] =  strtolower("%$searchKW%");
			$conditionArr['OR']['LOWER(product_code) like '] =  strtolower("%$searchKW%");
		}
		if(array_key_exists('category',$this->request->query) && !empty($this->request->query['category'][0])){
			$conditionArr['category_id IN'] = $this->request->query['category'];
		}
		if($displayType=="more_than_zero"){
			$conditionArr['NOT']['quantity'] = 0;
		}
        
        $this->paginate = [
                                'limit' => ROWS_PER_PAGE,
                                'conditions' => $conditionArr,
                                //'recursive' => -1
                        ];

		$selectedCategoryId = array();
		if(array_key_exists('category_id IN',$conditionArr) && !empty($conditionArr['category_id IN'][0])){
			$selectedCategoryId = $conditionArr['category_id IN'];
		}
		//pr($this->paginate);die;
		$products = $this->paginate($ProductTable);
		
		$categoryIdArr = array();
		foreach($products as $sngProduct){
			$categoryIdArr[] = $sngProduct['category_id'];
		}
		          
		if(!empty($categoryIdArr)){		  
		  $categoryName_query = $this->Categories->find('list',[
														  'conditions'=>['Categories.id IN'=>$categoryIdArr],
														  'keyField'=> 'id',
														  'valueField' => 'category'
														 ]);
		  //pr($categoryName);die;
		  if(!empty($categoryName_query)){
		   $categoryName  = $categoryName_query->toArray();
		  }else{
			   $categoryName = array();
		  }
		}else{
		  $categoryName = array();
		}
		
        //pr($categoryName);die;
		$this->set(compact('product','displayType'));
		$categories = $this->Categories->find('all',[
								'fields' => ['id', 'category','id_name_path'],
								'conditions' => ['Categories.status' => 1],
								'order' => 'Categories.category asc',
								'recursive' => -1
								]);
		
        //pr($categories);die;
        $categories->hydrate(false);
        if(!empty($categories)){
         $categories  = $categories->toArray();
        }
        //pr($categories);die;
		$categories = $this->CustomOptions->category_options($categories,true,$selectedCategoryId);
		$hint = $this->ScreenHint->hint('stock','index');
        if(!$hint){
            $hint = "";
        }
		
		$this->set(compact('hint','categories','categoryName','products'));
		//$this->layout = 'default'; 
		//$this->viewPath = 'Products';
		$this->render('index');
	}
    
    public function viewStockLevel()
    {
        $start = $end = $order_id = '';//these are being used in search
        //echo ROWS_PER_PAGE;die;
		
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
						// 'conditions' =>['UnderstockLevelOrders.kiosk_id IN' => $managerKiosk], 
                         'limit' => ROWS_PER_PAGE, 
                         'order'	=> ['UnderstockLevelOrders.created desc'], 
                         'group' => ['UnderstockLevelOrders.order_id']
                        ];			
			   }else{
					$this->paginate = [
                         'limit' => ROWS_PER_PAGE,
                         'order'	=> ['UnderstockLevelOrders.created desc'],
                         'group' => ['UnderstockLevelOrders.order_id']
                        ];
			   }
		  }else{
			   $this->paginate = [
                         'limit' => ROWS_PER_PAGE,
                         'order'	=> ['UnderstockLevelOrders.created desc'],
                         'group' => ['UnderstockLevelOrders.order_id']
                        ];
		  }
		  
		
		
		$viewStockData = $this->paginate('UnderstockLevelOrders');
		$dateCreatedArr = array();
		$userIdArr = array();
		$userName = array();
		$nameArr = array();
		//foreach($viewStockData as $key => $stockData){
		//	$dateCreatedArr[$stockData['UnderstockLevelOrder']['created']] = $stockData['UnderstockLevelOrder']['user_id'];
		//}
		foreach($viewStockData as $key => $viewStockInfo){
			$userIdArr[$viewStockInfo['user_id']] = $viewStockInfo['user_id'];
		}
		//foreach($userIdArr as $user_id){
		//	$userName[$user_id] = $this->User->find('first',array('conditions'=>array('User.id'=>$user_id),'fields'=>array('username'),'recursive'=>-1));
		//}
                $nameArr = $this->Users->find('list',
                                              //['conditions' => ['Users.id' => $userIdArr],
                                               [ 'keyField' => 'id','valueField' => 'username']
                                            );
                $nameArr = $nameArr->toArray();
		//foreach($userName as $user_id => $name){
		//	if(array_key_exists('User',$name) && array_key_exists('username',$name['User'])){
		//		$nameArr[$user_id] = $name['User']['username'];
		//	}
		//}
		$categories = $this->Categories->find('all',array(
								'fields' => array('id', 'category','id_name_path'),
								'conditions' => array('Categories.status' => 1),
								'order' => 'Categories.category asc',
								'recursive' => -1
								));
        $categories->hydrate(false);
        $categories = $categories->toArray();
		$categories = $this->CustomOptions->category_options($categories,true);
		$this->set(compact('categories'));
		
		$hint = $this->ScreenHint->hint('stock','view_stock_level');
        if(!$hint){
            $hint = "";
        }
		
		
		$this->set(compact('hint','viewStockData','dateCreatedArr','nameArr','start','end','order_id'));
    }
    
    public function searchStockLevel(){
        $conditionArray = array();
		$start = $this->request->query['start_date'];
		if(!empty($start)){
			$start_date = date("Y-m-d",strtotime($this->request->query['start_date']));
			$conditionArray[]= array("DATE(UnderstockLevelOrders.created) >= DATE('$start_date')");
			$this->set('start_date',$this->request->query['start_date']);
		}
		
		$end = $this->request->query['end_date'];
		if(!empty($end)){
			$end_date = date('Y-m-d',strtotime("+1 day", strtotime($this->request->query['end_date'])));
			$conditionArray[] = array("DATE(UnderstockLevelOrders.created) < DATE('$end_date')");
                        //['OR']['AND']
		}
		
		$order_id = $this->request->query['order_id'];
		if(!empty($order_id)){
			//$date = date("Y-m-d H:i:s",$order_id);//order id is timestamp
			//$conditionArray['OR'][] = array("UnderstockLevelOrder.created = DATE_FORMAT('$date','%Y-%m-%d %H:%i:%s')");
                        $conditionArray[] = array("UnderstockLevelOrders.order_id" => $order_id);
		}
		
		if(count($conditionArray)){
			$this->paginate = [
				'UnderstockLevelOrders' => [
                                            'conditions' => [$conditionArray],
                                            'order'	=> ['UnderstockLevelOrders.id desc'] ,
                                            'group' => ['UnderstockLevelOrders.order_id'] ,
                                            'limit' => ROWS_PER_PAGE  
                                        ]
						   ];
		}else{
			$this->paginate = [
				'UnderstockLevelOrders' => [
                                                'order'	=> ['UnderstockLevelOrders.id desc'] ,
                                                'group' => ['UnderstockLevelOrders.order_id'] ,
                                                'limit' => ROWS_PER_PAGE  
                                            ]
						   ];
		}
		
		$viewStockData = $this->paginate('UnderstockLevelOrders');
		$dateCreatedArr = array();
		$userIdArr = array();
		$userName = array();
		$nameArr = array();
                
        foreach($viewStockData as $key => $viewStockInfo){
			$userIdArr[$viewStockInfo['user_id']] = $viewStockInfo['user_id'];
		}
		if(empty($userIdArr)){
		  $userIdArr = array(0=>null);
		}
        //pr($userIdArr);die;
        $nameArr = $this->Users->find('list',
                                      ['conditions' => ['Users.id IN' => $userIdArr],
                                            'keyField'=> 'id',
                                            'valueField' => 'username'
                                            ]);
        //pr($nameArr);die;
        $nameArr->hydrate(false);
        $nameArr = $nameArr->toArray();
		$hint = $this->ScreenHint->hint('stock','view_stock_level');
        if(!$hint){
            $hint = "";
        }
		
		//pr($nameArr);die;
		$this->set(compact('hint','viewStockData','dateCreatedArr','nameArr','start','end','order_id'));
		$this->render('view_stock_level');
    }
    
    public function datewiseStockLevel($orderId = '')
    {
        if($orderId){
            $data_query = $this->UnderstockLevelOrders->find('all',array('conditions'=>array('UnderstockLevelOrders.order_id'=>$orderId)));
            $data_query = $data_query->hydrate(false);
            if(!empty($data_query)){
             $data  = $data_query->toArray();
            }else{
			   $data = array();
			}
			if(empty($data)){
				$this->Flash->error("No data found!");
				return $this->redirect("view_stock_level");
			}
        }else{
		  $data = array();
		}
        $rawDate = $orderId;
		$productIdArr = array();
		$product = array();
		foreach($data as $key =>$info){
			$productIdArr[] = $info['product_id'];
		}
		
		if(empty($productIdArr)){ $productIdArr = array(0=>null);}
		$product_query = $this->Products->find('all', array('conditions' => array('Products.id IN' => $productIdArr), 'recursive' => 0, 'order' => 'Products.category_id ASC'));
        $product_query = $product_query->hydrate(false);
        if(!empty($product_query)){
         $product  = $product_query->toArray();
        }else{
		  $product  = array();
		}
		$categories_query = $this->Categories->find('all',array(
								'fields' => array('id', 'category','id_name_path'),
								'conditions' => array('Categories.status' => 1),
								'order' => 'Categories.category asc',
								'recursive' => -1
								));
        $categories_query = $categories_query->hydrate(false);
        if(!empty($categories_query)){
         $categories  = $categories_query->toArray();
        }else{
		  $categories = array();
		}
		$categories = $this->CustomOptions->category_options($categories,true);
		$categoryNames_query = $this->Categories->find('list',
                                                 ['keyField'=> 'id',
                                                'valueField' => 'category'
                                            ]);
        $categoryNames_query = $categoryNames_query->hydrate(false);
        if(!empty($categoryNames_query)){
         $categoryNames  = $categoryNames_query->toArray();
        }
		$this->set(compact('categories','data','product','rawDate','categoryNames'));
    }
    
   public function stockLevel() {
		//pr($this->UnderstockLevelOrder->find('all', array('limit' => 10, 'recursive' => -1, 'order' => 'UnderstockLevelOrder.id desc')));
		$product_status = '';
		$categories_query = $this->Categories->find('all',array(
								'fields' => array('id', 'category','id_name_path'),
								'conditions' => array('Categories.status' => 1),
								'order' => 'Categories.category asc',
								'recursive' => -1
								));
		$categories_query = $categories_query->hydrate(false);
		$categories = $categories_query->toArray();
		
		$categories = $this->CustomOptions->category_options($categories,true);
		$kioskList_query = $this->Kiosks->find('all',array('conditions'=>array('Kiosks.status'=>1),'fields'=>array('id','code','name')));
		$kioskList_query = $kioskList_query->hydrate(false);
		$kioskList = $kioskList_query->toArray();
		
		$kioskIdArr = array();
		//getting list of kiosk products table
		$kioskName=array();
		foreach($kioskList as $key=>$kioskDetail){
			$kioskName[$kioskDetail['id']]=$kioskDetail['name'];
			$kioskTableArr[] = "kiosk_{$kioskDetail['id']}_products";
		}
		
		$this->set(compact('categories'));
		//$products = $this->Product->recursive = 0;
		$this->paginate = ['maxLimit' => 20,
									  'limit' => ROWS_PER_PAGE,
									  'conditions' => ['quantity < stock_level', 'Products.status' => 1],
									  'order' => ['Products.category_id ASC']
									  ];
		$products_query = $this->paginate('Products');
		$products = $products_query->toArray();
						
		//capturing the product ids of all the understock level products
		$productIdArr = array();
		$category_ids = array();
		foreach($products as $key =>$productDetail){
			$productIdArr[] = $productDetail->id;
			$category_ids[] = $productDetail->category_id;
		}
		$categoryNames_query = $this->Categories->find('list',
															[
																 'keyField' => 'id',
																 'valueField' => 'category'
															]
												 );
		$categoryNames_query = $categoryNames_query->hydrate(false);
		$categoryNames = $categoryNames_query->toArray();
		
		$orderData = array();
		//**for getting the order ids from understocklevel table to show them on fron end
		$orderData_query = $this->UnderstockLevelOrders->find('all',array('conditions'=>array('UnderstockLevelOrders.product_id IN'=>$productIdArr),'fields'=>array('product_id','order_id','created')));
		
		$orderData_query = $orderData_query->hydrate(false);
		$orderData = $orderData_query->toArray();
		
		$product_date_arr =$product_order_arr = array();
		if(count($orderData)){
            
			foreach($orderData as $key => $orderInfo){
                $product_order_arr[$orderInfo['product_id']][] = $orderInfo['order_id'];
                $created_date = $orderInfo['created'];
                $created = $created_date->i18nFormat(
                                       [\IntlDateFormatter::FULL, \IntlDateFormatter::FULL]
                                     );
				$product_date_arr[$orderInfo['order_id']][] = date("d-m-y",strtotime($created));
			}
		}
		$this->set(compact('product_order_arr', 'categoryNames','product_date_arr'));
		//***till here
		
		//checking the number of these products available in the other kiosks
		$productQuantity = array();
		foreach($kioskTableArr as $key=>$kioskTable){
		  $query = "SELECT `quantity`,`id` from $kioskTable WHERE `id` IN ('".implode("','",$productIdArr)."') AND `quantity`>0";
		  $conn = ConnectionManager::get('default');
		  $stmt = $conn->execute($query);
		  $productQuantity[$kioskTable] = $stmt ->fetchAll('assoc');
		}
		$productId_arr = array();
		$kioskWiseProduct = array();
		//pr($productQuantity);die;
		foreach($productQuantity as $key1=>$pQuantity){
			if(!empty($pQuantity)){
				foreach($pQuantity as $key=>$pquantity){
					//foreach($p_quantity as $tableName => $pquantity){
						if(!array_key_exists($pquantity['id'],$productId_arr)){
							$productId_arr[$pquantity['id']]=0;
						}
							$kioskWiseProduct[$pquantity['id']][$kioskName[preg_replace("/[^0-9,.]/", "", $key1)]] =$pquantity['quantity'];
							$productId_arr[$pquantity['id']]+=$pquantity['quantity'];
					//}
				}
			}
		}
		//pr($kioskWiseProduct);
		//pr($productId_arr);
		//checking if these ids exist in warehousestock with a condition that they were stocked in before the order placement, means
		//that they are ordered now however not stocked in till now so will be highlighted as in process
		
		$warehouseStockIn = array();
		
		$warehouseStockIn_query = $this->WarehouseStock->find('all',array('conditions'=>array('WarehouseStock.product_id IN'=>$productIdArr,'WarehouseStock.in_out'=>1),'fields'=>array('product_id','created')));
		$warehouseStockIn_query = $warehouseStockIn_query->hydrate(false);
		$warehouseStockIn[] = $warehouseStockIn_query->toArray();
		
		foreach($warehouseStockIn[0] as $key=>$warehouseStockProducts){
			$warehouseStockProductId = $warehouseStockProducts['product_id'];
			$warehouseStockdate = $warehouseStockProducts['created'];
		//checking if the product ids that exist in warehousestock were ordered recently in understock level table
			$understockLevelOrderData_query = $this->UnderstockLevelOrders->find('all',array('conditions'=>array('UnderstockLevelOrders.product_id'=>$warehouseStockProductId, 'UnderstockLevelOrders.created >'=>$warehouseStockdate),'fields'=>array('product_id','created')));
			$understockLevelOrderData_query = $understockLevelOrderData_query->hydrate(false);
			$understockLevelOrderData = $understockLevelOrderData_query->toArray();
			
		}
		
		$diff = array();
		$checkifordered = array();
		$underProcessProducts = array();
		if(!empty($understockLevelOrderData)){
			foreach($understockLevelOrderData as $key=>$understockLevelData){
				if(!empty($understockLevelData)){
					$underProcessProducts[$understockLevelData['product_id']] = "In process";
				}
			}
			
			//now the product ids in below array $diff are the ones that do not exist in warehouse table or those were stocked in before the order placement (means they have not been ordered now, so no need to highlight). along with the above $underProcessProducts array we also need to highlight the products that do not exist in warehouse table(as not necessarily all the products would exist in warehousestock table that are recently ordered) and were ordered recently. so we need to find the products that do not exist in warehouse table and exist in understocklevelorder table
			
			//getting the remaining ids in an array other than those exist in warehouse table with created date more than the stockin date
			//this array will also include the ids that do not exist in warehouse table
			if(count($underProcessProducts)){
				$diff = array_diff($productIdArr,array_keys($underProcessProducts));
			}
			
			//checking if these ids anyways exist in understocklevelorder
			if(count($diff)){
				$checkifordered_query = $this->UnderstockLevelOrders->find('list',
																				[
																					 'keyField' => 'id',
																					 'valueField' =>  'product_id',
																		   'conditions'=>['UnderstockLevelOrders.product_id IN'=>$diff]
																				]);
				$checkifordered_query = $checkifordered_query->hydrate(false);
				$checkifordered = $checkifordered_query->toArray();
			}
		}else{
			//case when no product id from page exists in warehouse stock table
			$checkifordered_query = $this->UnderstockLevelOrders->find('list',
																		 [
																					 'keyField' => 'id',
																					 'valueField' =>  'product_id',
																		   'conditions'=>['UnderstockLevelOrders.product_id IN'=>$productIdArr]
																				]);
			$checkifordered_query = $checkifordered_query->hydrate(false);
			$checkifordered = $checkifordered_query->toArray();
		}
		
		$checkIfStocked = array();
		//getting the ordered product ids that do exist in warehouse table
		if(count($checkifordered)){
			$checkIfStocked_query = $this->WarehouseStock->find('list',
																	  [
																		   'keyField' =>'id',
																		   'valueField' => 'product_id',
																 'conditions'=>['WarehouseStock.product_id IN'=>$checkifordered,
																					'WarehouseStock.in_out'=>1]
																	  ]
														  );
			$checkIfStocked_query = $checkIfStocked_query->hydrate(false);
			$checkIfStocked = $checkIfStocked_query->toArray();
			#pr($this->WarehouseStock->find('all',array('WarehouseStock.in_out'=>1,'fields'=>array('id','product_id','created'),'recursive'=> -1, 'limit' => 5, 'order'=>'WarehouseStock.id desc')));
		}
		
		if(count($checkIfStocked)){
			//capturing the product ids that do not exist in warehouse table
			$remainingDiff = array_diff($checkifordered,$checkIfStocked);
			//the ids that do not exist in warehouse table will be highlighted as they were ordered
			if(count($remainingDiff)){
				foreach($remainingDiff as $key => $remainingD){
					$underProcessProducts[$remainingD] = 'In process';
				}
			}
		}else{
			//if no ordered product is found in warehouse table then all will be highlighted, as all were ordered
			foreach($checkifordered as $key => $checkiforder){
				$underProcessProducts[$checkiforder] = 'In process';
			}
		}
	
		//pr($this->request);die;
		$page = $this->request->params['paging']['Products']['page'];
		if($page > 1){
			$action = "stock_level/page:$page";
		}else{
			$action = "stock_level";
		}
        //pr($this->request);die;
        //pr($this->request->Session()->read());die;
		if ($this->request->is(array('post', 'put')) ||//this condition is for checkout
		    (array_key_exists('chck_out',$this->request->Session()->read()) && $this->request->Session()->read('chck_out') == 'yes')) {
			//deleting chck_out from session as it was just put for passing this if condition
			if(array_key_exists('chck_out',$this->request->Session()->read())){
				$this->request->Session()->delete('chck_out');
			}
			$productAddedArr = array();
			$error = array();
			$stock_level_session = array();
			$errorStr = '';
			if(!empty($this->request->data)){
				$product_status = $this->request->data['product_status'];
				if(array_key_exists('current_page',$this->request->data)){
					$page = $this->request->data['current_page'];
					if($page > 1){
						$action = "stock_level/page:$page";
					}else{
						$action = "stock_level";
					}
				}
			}
			$this->request->Session()->write('product_status',$product_status);//for jquery purpose on the page
			//if(!array_key_exists('Stock',$this->request->data)){
			//			 $this->Flash->success("no item selected");
			//			 return $this->redirect(array('action' => "stock_level"));
			//		}
			
			if(array_key_exists('inactivate_products',$this->request->data)){
			   //pr($this->request);die;
				$deactivateProds = $this->request['data']['Stock']['activate'];
				$deactivateProds_codes = $this->request['data']['Stock']['activate_value'];
				//pr($deactivateProds);
				//pr($deactivateProds_codes);die;
				$deactivateProdArr = array();
				foreach($deactivateProds as $p_key => $deactivateProd){
					if($deactivateProd){$deactivateProdArr[] = $deactivateProds_codes[$p_key];}
				}
				$inStr = "('".implode("','", $deactivateProdArr )."')";
				
					$update_query = "UPDATE `products` SET `status` = '0' WHERE `product_code` IN $inStr";
					$conn = ConnectionManager::get('default');
					$stmt = $conn->execute($update_query);
				$this->Flash->success("Following products deactivated: $inStr");
				return $this->redirect(array('controller'=> 'stock','action' => 'stock_level'));
			}elseif(array_key_exists('add_to_basket',$this->request->data)){
				foreach($this->request->data['Stock']['checked_quantity'] as $key => $checked_quantity){
					if($checked_quantity == 1){//capturing the checked rows
						$quantity = $this->request->data['Stock']['quantity'][$key];
						$product_id = $this->request->data['Stock']['product_id'][$key];
						
						if($quantity > 0){
							$productAddedArr[$product_id] = $quantity;
						}else{
							foreach($products as $p => $prdct){
								if($prdct->id == $product_id){
									$prdctNme = $prdct->product;
								}
							}
							$error[] = $prdctNme;
						}
					}
				}
				
				if(count($error)){
					$errorStr = implode(', ',$error);
					$this->Flash->error("Please choose quantity more than zero for product: $errorStr. Total no. of products in basket: ".count($this->Session->read('stock_level_session'))."!");
					return $this->redirect(array('action' => $action));
				}else{
					$stock_level_session = $this->request->Session()->read('stock_level_session');
					$flashTable = '';
					if(count($productAddedArr)){
						if(count($stock_level_session)){
							$sum_total = $this->add_arrays(array($productAddedArr,$stock_level_session));
						}else{
							$sum_total = $productAddedArr;
						}
						
						$prdcts_query = $this->Products->find('all',array('conditions' => array(
																						 'Products.id IN' => array_keys($sum_total),
																						 'Products.status' => 1,
																						 ))); //rasu Feb 7, 16
						 $prdcts_query = $prdcts_query->hydrate(false);
						 $prdcts = $prdcts_query->toArray();
						//$productDetArr is being used below for showing name, product_code etc in flash
						$productDetArr = array();
						foreach($prdcts as $p => $prdct){
							$productDetArr[$prdct['id']] = $prdct;
						}
					 
						$rows = '';
						foreach($sum_total as $prdct_id => $prdctQtt){
							$rows.= "<tr>
									<td>".$productDetArr[$prdct_id]['product_code']."</td>
									<td>".$productDetArr[$prdct_id]['product']."</td>
									<td>".$categoryNames[$productDetArr[$prdct_id]['category_id']]."</td>
									<td>".$prdctQtt."</td>
								</tr>";
						}
						
						if(!empty($rows)){
							$flashTable = "<table>
										<tr>
											<th style='width: 131px;'>Product Code</th>
											<th>Product</th>
											<th style='width: 302px;'>Category</th>
											<th style='width: 44px;'>Qty</th>
										</tr>".$rows."
									</table>";
						}
						
						$this->request->Session()->write('stock_level_session',$sum_total);
						$session_basket = $this->request->Session()->read('stock_level_session');
						if(is_array($session_basket) && count($session_basket)){
							//storing the session in session_backups table
							$this->SessionRestore->update_session_backup_table($this->request->params['controller'], 'stock_level', 'stock_level_session', $session_basket, '');
						}
						$this->Flash->success(count($productAddedArr)." product(s) added to the basket. Total no. of products in basket: ".count($sum_total)."!</br>".$flashTable,array('escape' => false));
					}else{
						$this->Flash->error("Please choose products before adding them to the basket. Total no. of products in basket: ".count($stock_level_session)."!</br>".$flashTable,array('escape' => false));
					}
					return $this->redirect(array('action' => $action));
				}
			}elseif(array_key_exists('clear_basket',$this->request->data)){
				$this->request->Session()->delete('stock_level_session');
				$this->SessionRestore->delete_from_session_backup_table($this->request->params['controller'], 'stock_level', 'stock_level_session', '');
				$this->Flash->success("Basket has been cleared!");
				return $this->redirect(array('action' => $action));
			}elseif(array_key_exists('checkout',$this->request->data)){
				return $this->redirect(array('action' =>'stock_level_checkout'));
			}else{
				//case for save listing
				//pr($this->request);die;
				if(!empty($this->request->data) && array_key_exists('save_listing', $this->request->data)){
					
					foreach($this->request->data['Stock']['checked_quantity'] as $key => $checked_quantity){
						if($checked_quantity == 1){//capturing the checked rows
							$quantity = $this->request->data['Stock']['quantity'][$key];
							$product_id = $this->request->data['Stock']['product_id'][$key];
							
							if($quantity > 0){
								$productAddedArr[$product_id] = $quantity;
							}else{
								foreach($products as $p => $prdct){
									if($prdct['id'] == $product_id){
										$prdctNme = $prdct['product'];
									}
								}
								$error[] = $prdctNme;
							}
						}
					}
				}
				
				if(count($error)){
					$errorStr = implode(', ',$error);
					$this->Flash->error("Please choose quantity more than zero for product: $errorStr. Total no. of products in basket: ".count($this->Session->read('stock_level_session'))."!",array('escape' => false));
					return $this->redirect(array('action' => $action));
				}else{
					$stock_level_session = $this->request->Session()->read('stock_level_session');
					
					if(count($productAddedArr)){
						if(count($stock_level_session)){
							$sum_total = $this->add_arrays(array($productAddedArr,$stock_level_session));
						}else{
							$sum_total = $productAddedArr;
						}
						$this->request->Session()->write('stock_level_session',$sum_total);
						//pr($_SESSION)
						//$this->SessionRestore->update_session_backup_table($this->request->params['controller'], 'stock_level', 'stock_level_session', $session_basket, '');
					}elseif(!(array_key_exists('stock_level_session', $this->request->Session()->read())) || count($this->request->Session()->read('stock_level_session')) == 0){
						$this->Flash->error("Please choose a product to proceed. Nothing was chosen!");
						return $this->redirect(array('action' => $action));
					}
				}
				
				$count = 0;
				//below code will save the products that come through all the sources inluding checkout
				$savedIds = array();
				foreach($this->request->Session()->read('stock_level_session') as $prdctId => $qtty){
					$data = array(
						'kiosk_id' => "",
						'user_id' => $this->request->Session()->read('Auth.User.id'),
						'product_id' => $prdctId,
						'quantity' => $qtty,
						      );
					//$this->UnderstockLevelOrders->create();
					$UnderstockLevelOrders_entity = $this->UnderstockLevelOrders->newEntity();
					$UnderstockLevelOrders_entity = $this->UnderstockLevelOrders->patchEntity($UnderstockLevelOrders_entity, $data,['validate' => false]);
					if($this->UnderstockLevelOrders->save($UnderstockLevelOrders_entity)){			 
						$savedIds[] = $UnderstockLevelOrders_entity->id;
						$count++;
					}
				}
			}
			if($count>0){
				$understockId = $UnderstockLevelOrders_entity->id;
				$createdDate_query = $this->UnderstockLevelOrders->find('all',
																 array('conditions'=>array('UnderstockLevelOrders.id'=>$understockId),
																	   'fields'=>'created'));
				//pr($createdDate_query);die;
				$createdDate_query = $createdDate_query->hydrate(false);
				$createdDate = $createdDate_query->first();
				//saving time stamp as order_id for each saved record
				foreach($savedIds as $key => $savedId){
					$data = array('order_id' => strtotime($createdDate['created']));
					 $UnderstockLevelOrders_entity_2 = $this->UnderstockLevelOrders->get($savedId);
					 $UnderstockLevelOrders_entity_2 = $this->UnderstockLevelOrders->patchEntity($UnderstockLevelOrders_entity_2, $data);
					 $this->UnderstockLevelOrders->save($UnderstockLevelOrders_entity_2);
				}
				$this->Flash->success("List has been saved");
				//deleting session variables
				$this->request->Session()->delete('stock_level_session');
				$this->request->Session()->delete('product_status');
				$this->SessionRestore->delete_from_session_backup_table($this->request->params['controller'], 'stock_level', 'stock_level_session', '');
				return $this->redirect(array('action' => 'datewise_stock_level',strtotime($createdDate['created'])));
			}
		}
		
		 $hint = $this->ScreenHint->hint('stock','stock_level');
        if(!$hint){
            $hint = "";
        }
		$this->set(compact('hint','products','underProcessProducts','productId_arr','kioskWiseProduct','product_status'));
	}
    
    public function searchStockLevelMain()
    {
        $conditionArr = $this->generate_condition_array();
		$product_status = '';
		if(array_key_exists('product_status',$this->request->query)){
			$product_status = $this->request->query['product_status'];
		}
		
		if(array_key_exists('search_kw',$this->request->query)){
			$searchKW = trim(strtolower($this->request->query['search_kw']));
		}
		$conditionArr = array('Products.status' => 1);
		if(!empty($searchKW)){
			$conditionArr['OR']['LOWER(product) like '] =  strtolower("%$searchKW%");
			$conditionArr['OR']['LOWER(product_code) like '] =  strtolower("%$searchKW%");
			$conditionArr['OR']['LOWER(Products.description) like '] =  strtolower("%$searchKW%");
		}
		if(array_key_exists('category',$this->request->query)&& !empty($this->request->query['category'][0])){
			$conditionArr['category_id IN'] = $this->request->query['category'];
		}
		
		if(count($conditionArr)){
		 $this->paginate = [
                                            'conditions' => ['quantity < stock_level', $conditionArr],
                                            'limit' => ROWS_PER_PAGE,
                                            'recursive' => 0
                                        ];
            //pr($paginate);die;
		}else{
			$products = $this->Products->recursive=0;
            $this->paginate = [
                                'maxLimit' => 20,
                                'limit' => 20,
                                'conditions' => ['Products.status'=> '1']
                                //'recursive' => -1
                        ];
		}
		$selectedCategoryId=array();
		if(array_key_exists('category_id IN',$conditionArr)){
			$selectedCategoryId=$conditionArr['category_id IN'];
		}
		
		//**for highlighting and showing quantities like stock_level
		
		$kioskList = $this->Kiosks->find('all',array('conditions'=>array('Kiosks.status'=>1),'fields'=>array('id','code','name'),'recursive'=>-1));
        $kioskList->hydrate(false);
        if(!empty($kioskList)){
            $kioskList = $kioskList->toArray();
        }
		$kioskIdArr = array();
		//getting list of kiosk products table
		$kioskName=array();
		foreach($kioskList as $key=>$kioskDetail){
			$kioskName[$kioskDetail['id']]=$kioskDetail['name'];
			$kioskTableArr[] = "kiosk_{$kioskDetail['id']}_products";
		}
		//***
		$products = $this->paginate('Products');
		//THIS CODE IS COPIED FROM stock_level, it is not related to search, this is just to show quantities from other kiosks and highlighting
		//capturing the product ids of all the understock level products
		$productIdArr = array();
		
		foreach($products as $key=>$productDetail){
            //pr($productDetail);die;
			$productIdArr[] = $productDetail->id;
		}
		
		$orderData = array();
		//**for getting the order ids from understocklevel table to show them on fron end
		if(empty($productIdArr)){
		  $productIdArr = array(0 => null);
		}
		$orderData = $this->UnderstockLevelOrders->find('all',array('conditions'=>array('UnderstockLevelOrders.product_id IN'=>$productIdArr),'fields'=>array('product_id','order_id','created')));
		
		$orderData->hydrate(false);
        if(!empty($orderData)){
            $orderData = $orderData->toArray();
        }
		$product_date_arr = $product_order_arr = array();
		if(count($orderData)){
			foreach($orderData as $key => $orderInfo){
                //pr($orderInfo);die;
				$product_order_arr[$orderInfo['product_id']][] = $orderInfo['order_id'];
                if(!array_key_exists($orderInfo['order_id'],$product_date_arr)){
                    $created_date = $orderInfo['created'];
                    
                    $created = $created_date->i18nFormat(
                                               [\IntlDateFormatter::FULL, \IntlDateFormatter::FULL]
                                       );
					$product_date_arr[$orderInfo['order_id']][] = date("d-m-y",strtotime($created));
				}
			}
		}
		
		$this->set(compact('product_order_arr','product_date_arr'));
		//***till here
		
		//checking the number of these products available in the other kiosks
		$productQuantity = array();
		foreach($kioskTableArr as $key=>$kioskTable){
            
            $conn = ConnectionManager::get('default');
            $stmt = $conn->execute("SELECT `quantity`,`id` from $kioskTable WHERE `id` IN ('".implode("','",$productIdArr)."') AND `quantity`>0");
            $productQuantity[$kioskTable] = $stmt ->fetchAll('assoc');
            
			//$productQuantity[] = $this->Product->query("SELECT `quantity`,`id` from $kioskTable WHERE `id` IN ('".implode("','",$productIdArr)."') AND `quantity`>0");
		}
		$productId_arr = array();
		$kioskWiseProduct = array();
		foreach($productQuantity as $key=>$pQuantity){
			if(!empty($pQuantity)){
				foreach($pQuantity as $key1=>$p_quantity){
                    //pr($p_quantity);die;
					//foreach($p_quantity as $tableName => $pquantity){
						if(!array_key_exists($p_quantity['id'],$productId_arr)){
							$productId_arr[$p_quantity['id']]=0;
						}
							$kioskWiseProduct[$p_quantity['id']][$kioskName[preg_replace("/[^0-9,.]/", "", $key)]] =$p_quantity['quantity'];
							$productId_arr[$p_quantity['id']]+=$p_quantity['quantity'];
					//}
				}
			}
		}
		//pr($kioskWiseProduct);
		//pr($productId_arr);
		//checking if these ids exist in warehousestock with a condition that they were stocked in before the order placement, means
		//that they are ordered now however not stocked in till now so will be highlighted as in process
		
		$warehouseStockIn = array();
		
        $query = $this->WarehouseStock->find('all',array('conditions'=>array('WarehouseStock.product_id IN'=>$productIdArr,'WarehouseStock.in_out'=>1),'fields'=>array('product_id','created')));
        $query->hydrate(false);
        if(!empty($query)){
            $warehouseStockIn[] = $query;
        }
		foreach($warehouseStockIn[0] as $key=>$warehouseStockProducts){
            //pr($warehouseStockProducts);die;
			$warehouseStockProductId = $warehouseStockProducts['product_id'];
			$warehouseStockdate = $warehouseStockProducts['created'];
		//checking if the product ids that exist in warehousestock were ordered recently in understock level table
			$understockLevelOrderData = $this->UnderstockLevelOrders->find('all',array('conditions'=>array('UnderstockLevelOrders.product_id'=>$warehouseStockProductId, 'UnderstockLevelOrders.created >'=>$warehouseStockdate),'fields'=>array('product_id','created')));
			$understockLevelOrderData->hydrate(false);
            if(!empty($understockLevelOrderData)){
                $understockLevelOrderData = $understockLevelOrderData->toArray();
            }
		}
		
		$diff = array();
		$checkifordered = array();
		$underProcessProducts = array();
		if(!empty($understockLevelOrderData)){
			foreach($understockLevelOrderData as $key=>$understockLevelData){
                //pr($understockLevelData);die;
				if(!empty($understockLevelData)){
					$underProcessProducts[$understockLevelData['product_id']] = "In process";
				}
			}
			
			//now the product ids in below array $diff are the ones that do not exist in warehouse table or those were stocked in before the order placement (means they have not been ordered now, so no need to highlight). along with the above $underProcessProducts array we also need to highlight the products that do not exist in warehouse table(as not necessarily all the products would exist in warehousestock table that are recently ordered) and were ordered recently. so we need to find the products that do not exist in warehouse table and exist in understocklevelorder table
			
			//getting the remaining ids in an array other than those exist in warehouse table with created date more than the stockin date
			//this array will also include the ids that do not exist in warehouse table
			if(count($underProcessProducts)){
				$diff = array_diff($productIdArr,array_keys($underProcessProducts));
			}
			
			//checking if these ids anyways exist in understocklevelorder
            //pr($diff);die;
			if(count($diff)){
				$checkifordered = $this->UnderstockLevelOrders->find('list',
                                                                     ['conditions'=>['UnderstockLevelOrders.product_id IN'=>$diff],
                                                                      ['keyField' => 'id',
                                                                        'valueField' => 'username']
                                                                      ]);
                $checkifordered = $checkifordered->toArray();
			}
		}else{
			//case when no product id from page exists in warehouse stock table
			$checkifordered = $this->UnderstockLevelOrders->find('list',
                                                                 ['conditions'=>['UnderstockLevelOrders.product_id IN'=>$productIdArr]],
                                                                  ['keyField' => 'id',
                                                                        'valueField' => 'username']
                                                                 );
            $checkifordered = $checkifordered->toArray();
		}
		//pr($checkifordered);die;
		$checkIfStocked = array();
		//getting the ordered product ids that do exist in warehouse table
		if(count($checkifordered)){
			$checkIfStocked = $this->WarehouseStock->find('list',
                                                          ['conditions'=>['WarehouseStock.product_id IN'=>$checkifordered,'WarehouseStock.in_out'=>1]],
                                                           ['keyField' => 'id',
                                                            'valueField' => 'product_id']     
                                                        );
            $checkIfStocked = $checkIfStocked->toArray();
		}
		//pr($checkIfStocked);die;
		if(count($checkIfStocked)){
			//capturing the product ids that do not exist in warehouse table
			$remainingDiff = array_diff($checkifordered,$checkIfStocked);
			//the ids that do not exist in warehouse table will be highlighted as they were ordered
			if(count($remainingDiff)){
				foreach($remainingDiff as $key => $remainingD){
					$underProcessProducts[$remainingD] = 'In process';
				}
			}
		}else{
			//if no ordered product is found in warehouse table then all will be highlighted, as all were ordered
			foreach($checkifordered as $key => $checkiforder){
				$underProcessProducts[$checkiforder] = 'In process';
			}
		}
		
		//******TILL HERE
		
		$this->set(compact('products','product_status'));
		$categories = $this->Categories->find('all',array(
			'fields' => array('id', 'category','id_name_path'),
			'conditions' => array('Categories.status' => 1),
			'order' => 'Categories.category asc',
			'recursive' => -1
		));
        $categories->hydrate(false);
        $categories = $categories->toArray();
		$categories = $this->CustomOptions->category_options($categories,true,$selectedCategoryId);
		$categoryNames = $this->Categories->find('list',
                                                    ['keyField' => 'id',
                                                    'valueField' => 'category']
                                                  );
        
        $categoryNames = $categoryNames->toArray();
        //pr($categoryNames);die;
		$hint = $this->ScreenHint->hint('stock','stock_level');
        if(!$hint){
            $hint = "";
        }
		$this->set(compact('categories','categoryNames'));
		$this->set(compact('hint','underProcessProducts','productId_arr','kioskWiseProduct'));
		//$this->layout = 'default'; 
		$this->render('stock_level');
    }
    
    private function generate_condition_array(){
		$searchKW = trim(strtolower($this->request->query['search_kw']));
		$conditionArr = array();
		if(!empty($searchKW)){
			$conditionArr['OR']['LOWER(product) like '] =  strtolower("%$searchKW%");
			$conditionArr['OR']['LOWER(product_code) like '] =  strtolower("%$searchKW%");
			
		}
		if(array_key_exists('category IN',$this->request->query)){
			$conditionArr['category_id IN'] = $this->request->query['category IN'];
		}
		return $conditionArr;
	}
	
	public function updateStock() {
		$current_page = $this->request['data']['current_page'];
		$stock = array();
		$counter = 0;
		$successfullySaved = array();
		foreach($this->request['data']['Stock']['quantity'] as $key => $quantity){
			$stockArr = $this->request['data']['Stock']['product_id'];
			if(!empty($quantity)){
				$stock[$stockArr[$key]] = $quantity;
				$product_id = $stockArr[$key];
				$entity = $this->Products->get($product_id);
				$data = array('quantity' => $quantity);
				$entity = $this->Products->patchEntity($entity, $data,['validate' => false]);
				if($this->Products->save($entity)){
					$successfullySaved[$counter++] = $counter;
				}
			}
		}
		if (count($successfullySaved)) {
			$this->Flash->success("Stock for {$counter} item(s) udpated .");
			return $this->redirect(array('action' => "index/page:$current_page"));
		} else {
			$this->Flash->error(__('Stock couldn\'t be updated. Please try again!'));
		}
		$this->autoRender = false;
	}
	
	public function kioskDailyStock(){
		if(count($this->request->query)){
            //echo"bye";die;
            //pr($this->request->query);die;
			$conditionArr = array();
			$date = $this->request->query('start_date');
			$search = $this->request->query('search_kw');
			$kiosk_id = $this->request->query('kiosk_id');
			$display_type = $this->request->query('display_type');
			
			if($display_type == 'show_all'){
				;
			}else{
				$conditionArr[] = array('quantity >' => 0);
			}
			
			if($this->request->query['kiosk_id'] == -1){
				$this->redirect(array('action' => 'all_kiosk_stock_summary', '?' => array('search_kw' => $search, 'start_date' => $date)));
			}
			//$this->request->('search_kw')||$this->request->query('start_date')||$this->request->query('kiosk_id')
			
			if(!empty($kiosk_id)){
				if($kiosk_id == 10000 || $kiosk_id == -1){
					$data_source = "daily_stocks";
				}else{
						$data_source = "kiosk_".$kiosk_id."_daily_stocks";	
				}
			}else{
				$data_source = "daily_stocks";
			}
			if(!empty($search)){
				$result_query = $this->Products->find('list',
													   [
															'keyField' => 'id',
															'valueField' => 'id',
															'conditions'=>['or' => ['Products.product LIKE' => '%'.$search.'%',
																		   'Products.product_code LIKE' => '%'.$search.'%'	
																		]],
															'limit' => ROWS_PER_PAGE,
															
													   ]);
				$result_query = $result_query->hydrate(false);
				if(!empty($result_query)){
					$result = $result_query->toArray();
				}else{
					$result = array();
				}
				
			}
			
			if(!empty($date)){
				$date = date("Y-m-d",strtotime($date));
				$conditionArr[] = array('date(created)' => $date);
			}else{
				$conditionArr[] = array('date(created) = DATE_ADD(CURDATE(), INTERVAL -1 DAY)');
			}
			if(isset($result) && !empty($result)){
				$conditionArr['product_id IN'] = $result;	
			}
			
			//pr($conditionArr);
			//$this->DailyStock->setSource($data_source);
			
			$daily_stockTable = TableRegistry::get($data_source,[
														'table' => $data_source,
															]);
            //pr($conditionArr);die;
			$totalCost_query = $daily_stockTable->find('all',
												 array(
													   'conditions' => array($conditionArr))
												 );
			$totalCost_query
							   ->select(['total_cost' => $totalCost_query->func()->sum('cost_price*quantity')]);
		  
			   $totalCost_query = $totalCost_query->hydrate(false);
			   $totalCost = $totalCost_query->first();
		  
                $this->paginate = [
                                    'conditions'=>[$conditionArr],
                                    'limit' => ROWS_PER_PAGE,
                                    'order' => ['id desc']
                                 ];
				$result1_query = $this->paginate($daily_stockTable);
				$result1 = $result1_query->toArray();
				//pr($result1);die;
				$ids = array();
				foreach($result1 as $key => $value){
					$ids[] = $value->product_id;
				}
				//pr($ids);die;
				$kiosks_query = $this->Kiosks->find('list',
													   [
															'keyField' => 'id',
															'valueField' => 'name',
															'conditions' => ['Kiosks.status' => 1],
															'order' => ['Kiosks.name asc']
													   ]);
				$kiosks_query = $kiosks_query->hydrate(false);
				$kiosks = $kiosks_query->toArray();
				//pr($kiosks);
				if(empty($ids)){
					$ids = array(0 => null);
				}
				$product_name_query = $this->Products->find('list',
																 [
																	  'keyField' => 'id',
																	  'valueField' => 'product',
																	  'conditions' => ['Products.id IN' => $ids],
																	  'order' => 'Products.id asc'
																 ]);
				$product_name_query = $product_name_query->hydrate(false);
				$product_name = $product_name_query->toArray();
				$product_code_query = $this->Products->find('list',
																 [
																	  'keyField' => 'id',
																	  'valueField' => 'product_code',
																	  'conditions' => ['Products.id IN' => $ids],
																	  'order' => ['Products.id asc']
																 ]);
				$product_code_query = $product_code_query->hydrate(false);
				$product_code = $product_code_query->toArray();
				
				$hint = $this->ScreenHint->hint('stock','kiosk_daily_stock');
					if(!$hint){
						$hint = "";
					}
					$this->set('hint',$hint);
				
				
				//pr($result1);
				$this->set('dailyStocks',$result1);
				$this->set('kiosks',$kiosks);
				$this->set('product_name',$product_name);
				$this->set('product_code',$product_code);
				$this->set('totalCost',$totalCost);
		}else{
            //echo"hi";die;
			$totalCost_query = $this->DailyStocks->find('all',array('conditions' => array('date(created) = DATE_ADD(CURDATE(), INTERVAL -1 DAY)')));
			$totalCost_query
							  ->select(['total_cost' => $totalCost_query->func()->sum('cost_price*quantity')])
							   ->first();
			$totalCost_query  =$totalCost_query->hydrate(false);
			$totalCost = $totalCost_query->first();
			$this->paginate = [
										'limit' => ROWS_PER_PAGE,
										'conditions' => ['date(created) = DATE_ADD(CURDATE(), INTERVAL -1 DAY)',
															  'quantity > 0'],
										'order' => ['id desc']
							  ];
			$result_query = $this->paginate('DailyStocks');
			$result = $result_query->toArray();
			$ids = array();
			foreach($result as $key => $value){
				$ids[] = $value->product_id;
			}
			
			$kiosks_query = $this->Kiosks->find('list',
												  [
													   'keyField' => 'id',
													   'valueField' =>  'name',
													   'conditions' => ['Kiosks.status' => 1],
													   'order' => 'Kiosks.name asc'
												  ]);
			$kiosks_query = $kiosks_query->hydrate(false);
			$kiosks = $kiosks_query->toArray();
			if(empty($ids)){
			   $ids = array(0 => null);
			}
			$product_name_query = $this->Products->find('list',
															[
																 'keyField' => 'id',
																 'valueField' => 'product',
																 'conditions' => ['Products.id IN' => $ids],
																 'order' => 'Products.id asc'
															]);
			$product_name_query = $product_name_query->hydrate(false);
			$product_name = $product_name_query->toArray();
			$product_code_query = $this->Products->find('list',
															[
																 'keyField' => 'id',
																 'valueField' => 'product_code',
																 'conditions' => ['Products.id IN' => $ids],
																 'order' => 'Products.id asc'
															]);
			$product_code_query = $product_code_query->hydrate(false);
			$product_code = $product_code_query->toArray();
			
		$hint = $this->ScreenHint->hint('stock','kiosk_daily_stock');
			if(!$hint){
				$hint = "";
			}
			$this->set('hint',$hint);
			$this->set('dailyStocks',$result);
			$this->set('kiosks',$kiosks);
			$this->set('product_name',$product_name);
			$this->set('product_code',$product_code);
			$this->set('totalCost',$totalCost);
		}
	}
	
	public function viewDeadStock() {
        $kiosk_id = $this->request->Session()->read('kiosk_id');
        if($kiosk_id == "" || $kiosk_id == 10000){
            $product_table_source = "products";
        }else{
            $product_table_source = "kiosk_{$kiosk_id}_products";
        }
        
        $productsTable = TableRegistry::get($product_table_source,[
                                                            'table' => $product_table_source,
                                                        ]);
	 //pr($this->request);die;
		$kiskId = "";
		$kioskList_query = $this->Kiosks->find('all',array('conditions'=>array('Kiosks.status'=>1,'Kiosks.id != 10000'),'fields'=>array('id','code','name'),'recursive'=>-1,'order' => 'Kiosks.name asc'));
		$kioskList_query = $kioskList_query->hydrate(false);
		$kioskList = $kioskList_query->toArray();
		$kioskArr = array();
		foreach($kioskList as $key=>$kioskDetail){
			$kioskArr[$kioskDetail['id']]=$kioskDetail['name'];
		}
		 $selectedCategoryId = array();
    
		if ($this->request->is(array('get', 'put'))) {
			if(array_key_exists('kiosklist',$this->request->query)){
				$kiskId = $this->request->query['kiosklist'];
			}else{
                $kiskId = $this->request->Session()->read('kiosk_id');
            }
			$conditionArry = array();
			if(array_key_exists('search_kw',$this->request->query)){
				$search_kw = "";
				$search_kw = $this->request->query['search_kw'];
				if(!empty($search_kw)){
					$conditionArry['OR']['LOWER(product) like '] =  strtolower("%$search_kw%");
					$conditionArry['OR']['LOWER(product_code) like '] =  strtolower("%$search_kw%");
					$conditionArry['OR']['LOWER(description) like '] =  strtolower("%$search_kw%");
					$this->set(compact('search_kw'));
				}
			}
			$catgry = array();
			//pr($this->request->query);die;
			
			if(array_key_exists('category',$this->request->query)){
				$catgry = $this->request->query['category'];
				if(!empty($catgry)){
				   $selectedCategoryId = $catgry;
				}
				if(!empty($catgry)){
					if($catgry[0] ==0)
					$catgry = array();
				}
				if(!empty($catgry)){
					if(empty($conditionArry)){
						$conditionArry[] = array("category_id IN" => $catgry);
					}else{
						$conditionArry['AND'] =  array("category_id IN" => $catgry);
					}
					$this->set(compact('catgry'));
				}
			}
			if(!empty($kiskId)){
				$productSource = "kiosk_{$kiskId}_products";
			}else{
				$productSource = "products";
			}
			
			 $productsTable = TableRegistry::get($productSource,[
																 'table' => $productSource,
																	 ]);

		}
		
		if(array_key_exists('kiosk_id',$this->request->Session()->read())){
			$kiskId = $this->request->Session()->read('kiosk_id');
		}
		
		if(!empty($kiskId)){
			$kiosk_id = $kiskId;
		}else{
			$kiosk_id = 0;
		}
		
		$deadStockDetails_query = $this->DeadProducts->find('all',array('conditions'=>array('DeadProducts.kiosk_id'=>$kiosk_id),'recursive'=>-1, 'order' => 'DeadProducts.id desc'));
		$deadStockDetails_query = $deadStockDetails_query->hydrate(false);
		$deadStockDetails = $deadStockDetails_query->first();
		//pr($this->DeadProduct->find('all',array('conditions'=>array('DeadProduct.kiosk_id'=>$kiosk_id),'recursive'=>-1)));
		$productIdArr = array();
		if(!empty($deadStockDetails)){
			$deadStockDetail = $deadStockDetails['products'];
			$deadProductArr = explode("|",$deadStockDetail);
			
			$deadStockPercentageArr = array();
			$deadProducts = "";
			foreach($deadProductArr as $key=>$deadProduct){
				$deadProducts = (explode(":",$deadProduct));
				if(array_key_exists(1,$deadProducts)){
					$deadStockPercentageArr[]=$deadProducts[1];
				}
				$productIdArr[]= $deadProducts[0];
			}
			foreach($deadStockPercentageArr as $key=>$deadStockPer){
				//pr(explode("#",$deadStockPer)[0]);
			}
			if(empty($conditionArry)){
				$this->paginate = ['limit' => '20','conditions'=>array('id IN'=>$productIdArr)];
			}else{
				$this->paginate = array('limit' => '20','conditions'=>array($conditionArry,
																					   array('AND' => array('id IN'=>$productIdArr))));
			}
            //pr($productsTable);die;
			$products_query = $this->paginate($productsTable);
			$products = $products_query->toArray();
			$this->set('products', $products);
		}
		
		
		$categories_query = $this->Categories->find('all',array(
								'fields' => array('id', 'category','id_name_path'),
								'conditions' => array('Categories.status' => 1),
								'order' => 'Categories.category asc',
								));
		$categories_query = $categories_query->hydrate(false);
		$categories = $categories_query->toArray();
		
		$categories = $this->CustomOptions->category_options($categories,true,$selectedCategoryId);
		$hint = $this->ScreenHint->hint('Stock','view_dead_stock');
					if(!$hint){
						$hint = "";
					}
		$this->set(compact('hint','categories','kioskArr','kiskId'));
		
		if(empty($productIdArr)){
			$product = "";
		}
		
	}
	
	
	public function combinedDeadProducts($kioskIdArr=array()){
		$activeKioskList_query = $this->Kiosks->find('list',array('conditions'=>array('Kiosks.status'=>1),'recursive'=>-1,'fields'=>array('id','name'),'order' => 'Kiosks.name asc'));
		$activeKioskList_query  =$activeKioskList_query->hydrate(false);
		$activeKioskList = $activeKioskList_query->toArray();
		if ($this->request->is(array('get', 'put'))) {
			              //getting the date of recently added row
                        $dateInfo_query = $this->DeadProducts->find('all', array('fields' => array('created'), 'order' => 'DeadProducts.id desc', 'recursive' => -1));
						$dateInfo_query = $dateInfo_query->hydrate(false);
						$dateInfo = $dateInfo_query->first();
                        $date = $dateInfo['created'];
			if(array_key_exists('kioskList',$this->request->query)){
				$requestedKiosks = $this->request->query['kioskList'];
			}
			$conditionArray = array();
			$selectedCategoryId=array();
			if(array_key_exists('search_kw',$this->request->query)){
				$search_kw = "";
				$search_kw = $this->request->query['search_kw'];
				if(!empty($search_kw)){
					$conditionArray['OR']['LOWER(product) like '] =  strtolower("%$search_kw%");
					$conditionArray['OR']['LOWER(product_code) like '] =  strtolower("%$search_kw%");
					$conditionArray['OR']['LOWER(Products.description) like '] =  strtolower("%$search_kw%");
					//$conditionArray[] = array("Product.product" => $search_kw);
					$this->set(compact('search_kw'));
				}
			}
			
			$categories = array();
			if(array_key_exists('category',$this->request->query)){
				$catgries = $this->request->query['category'];
				$selectedCategoryId=array();
				//pr($conditionArray);
			   if(!empty($catgries)){
				   $selectedCategoryId = $catgries;
				}
				if(!empty($catgries)){
					if($catgries[0] ==0)
					$catgries = array();
				}
				// pr($catgries);
				if(!empty($catgries)){
					if(empty($conditionArray)){
						$conditionArray[] = array("Products.category_id IN" => $catgries);
					}else{
						$conditionArray['AND'] = array("Products.category_id IN" => $catgries);
					}
					$this->set(compact('catgries'));
				}
				
			}
			
			//pr($selectedCategoryId);
			$requestedKiosks['admin']=0;
				$deadProductDetails_query = $this->DeadProducts->find('all',array('conditions'=>array('DeadProducts.kiosk_id IN'=>$requestedKiosks, 'DATE(created)' => date('Y-m-d',strtotime($date))),'order'=>'DeadProducts.id desc'));
				$deadProductDetails_query = $deadProductDetails_query->hydrate(false);
				$deadProductDetails = $deadProductDetails_query->toArray();
				
                                //pr($deadProductDetails);
				$deadProductPerKiosk = array();
				$deadProductIdArr = array();
				
				foreach($deadProductDetails as $key=>$deadProductDetail){
					$deadProductPerKiosk = explode("|",$deadProductDetail['products']);
					
					$kioskArr[$deadProductDetail['kiosk_id']] = $deadProductPerKiosk;
					foreach($deadProductPerKiosk as $deadProduct){
						$deadProductIds = explode(":",$deadProduct);
						
						$deadProductIdArr[$deadProductIds[0]]=$deadProductIds[0];
					}
					
				}
				if(!empty($deadProductIdArr)){
					if(empty($conditionArray)){
						
						$this->paginate = ['limit' => '20','conditions'=>[
																						   ['AND' => ['Products.id IN'=>$deadProductIdArr]]
																		 ]
										  ];
					}else{
						//$conditionArray['AND'] = array('Product.id'=>$deadProductIdArr);
						$this->paginate= ['limit' => '20','conditions'=>[$conditionArray,
																						   ['AND' => ['Products.id IN'=>$deadProductIdArr]]
										 ]
										 ];
					}
					
					$products = $this->paginate("Products");
					$this->set('products', $products);

					$this->set(compact('deadProductIdArr','kioskArr'));
				}
		}
		$categories_query = $this->Categories->find('all',array(
												'fields' => array('id', 'category','id_name_path'),
												  'conditions' => array('Categories.status' => 1),
												'order' => 'Categories.category asc',
											));
		$categories_query = $categories_query->hydrate(false);
		$categories =$categories_query->toArray();
		
		$categories = $this->CustomOptions->category_options($categories,true,$selectedCategoryId);
		$this->set(compact('categories'));
		$hint = $this->ScreenHint->hint('Stock','combined_dead_products');
					if(!$hint){
						$hint = "";
					}
		$this->set(compact('hint','activeKioskList','requestedKiosks'));
	}
	
	private function add_arrays($arrays = array()){
            $allValues = array();
			$arrays = array_reverse($arrays,true);
            foreach($arrays as $sngArr){
				if(is_array($sngArr)){
					foreach($sngArr as $key => $value){
						if(!array_key_exists($key,$allValues)){
							$allValues[$key] = $value;
						}else{
							$allValues[$key] = $value;
						}
					}
				}
            }
            //sort($allValues,SORT_STRING);
            return $allValues;
        }
		
	public function stockLevelCheckout(){
		$stock_level_session = $this->request->Session()->read('stock_level_session');
		$productDetArr = array();
		if(!$stock_level_session){
			$this->Flash->error("Please add products to the basket before checkout!");
			return $this->redirect(array('action' => 'stock_level'));
		}else{
			$prdcts_query = $this->Products->find('all',
											array('conditions' => array('Products.id IN' => array_keys($stock_level_session))
												  )
											);
			$prdcts_query = $prdcts_query->hydrate(false);
			$prdcts = $prdcts_query->toarray();
			//$productDetArr is being used below for showing name, product_code etc in flash
			foreach($prdcts as $p => $prdct){
				$productDetArr[$prdct['id']] = $prdct;
			}
		}
		if($this->request->is('post','put')){
			if(array_key_exists('update_quantity',$this->request->data)){
				//updating the quantity
				$quantityArr = $this->request->data['quantity'];
				//$this->request->Session()->delete('stock_level_session');
                unset($_SESSION['stock_level_session']);
                $_SESSION['stock_level_session'] = $quantityArr;
				if(true){
					$this->Flash->success('Quantity has been updated!');
				}else{
					$this->Flash->error('Quantity could not be updated!');
				}
				return $this->redirect(array('action' => 'stock_level_checkout'));
			}else{
                $_SESSION['chck_out'] = 'yes';
				//redirect to stock level for saving
				//$this->request->Session()->write('chck_out','yes');
				return $this->redirect(array('action' => 'stock_level'));
			}
		}
		$categoryNames_query = $this->Categories->find('list',
													   [
															'keyField' => 'id',
															'valueField' => 'category',
													   ]
											   );
		$categoryNames_query = $categoryNames_query->hydrate(false);
		$categoryNames = $categoryNames_query->toArray();
		$this->set(compact('productDetArr','categoryNames'));
	}
	public function deleteStockLevelSession($id = ''){
	 unset($_SESSION['stock_level_session'][$id]);
		if(true){
			$this->Flash->success('Product has been deleted!');
			if(count($this->request->Session()->read('stock_level_session')) > 0){
				return $this->redirect(array('action' => 'stock_level_checkout'));
			}else{
				return $this->redirect(array('action' => 'stock_level'));
			}
		}
	}
	
	public function restoreSession($currentController = '', $currentAction = '', $session_key = '', $kioskId = '', $redirectAction = ''){
		if(!$redirectAction){
		    $redirectAction = $currentAction;
		}
		$status = $this->SessionRestore->restore_from_session_backup_table($currentController, $currentAction, $session_key, $kioskId);
		if($status == 'Success'){
		    $msg = "Session succesfully retreived!";
		}else{
		    $msg = "Session could not be retreived!";
		}
		$this->Flash->success($msg);
		return $this->redirect(array('action' => $redirectAction));
	}
	
	public function viewSellingPrice() {//for repair technician
		//$this->Product->setSource("products");
        $kiosk_id = $this->request->Session()->read('kiosk_id');
        if($kiosk_id == "" || $kiosk_id == 10000){
            $product_table_source = "products";
        }else{
            $product_table_source = "kiosk_{$kiosk_id}_products";
        }
        
        $product_table = TableRegistry::get($product_table_source,[
                                                            'table' => $product_table_source,
                                                        ]);
        //pr($product_table);die;
		$categories_query = $this->Categories->find('all',array(
								'fields' => array('id', 'category','id_name_path'),
								'conditions' => array('Categories.status' => 1),
								'order' => 'Categories.category asc'
								));
		$categories_query = $categories_query->hydrate(false);
		if(!empty($categories_query)){
		  $categories = $categories_query->toArray();
		}else{
		  $categories = array();
		}
		$displayType = "";
		if ($this->request->is(array('get', 'put'))) {
			if(array_key_exists('display_type',$this->request->query)){
				$displayType = $this->request->query['display_type'];
				if($displayType=="show_all"){
					$this->paginate = array('limit' => '20');
				}elseif($displayType=="more_than_zero"){
					$this->paginate = array('limit' => '20','conditions'=>array('NOT'=>array('quantity'=>0)));
				}
			}	
		}
		
		$categories = $this->CustomOptions->category_options($categories,true);
		$product_query = $this->paginate($product_table);
		$product = $product_query->toArray();
		$categoryIdArr = array();
		foreach($product as $sngProduct){
			$categoryIdArr[] = $sngProduct->category_id;
		}
		$categoryName_query = $this->Categories->find('list',
											  array('conditions'=>array('Categories.id IN'=>$categoryIdArr),
													'keyField' => 'id',
												     'valueField' => 'category',
													)
											  );
		$categoryName_query = $categoryName_query->hydrate(false);
		if(!empty($categoryName_query)){
		  $categoryName = $categoryName_query->toArray();
		}else{
		  $categoryName = array();
		}
		$this->set(compact('categories','displayType','product','categoryName'));
	}
    
    public function export(){
		$conditionArr = array();
		if(array_key_exists('search_kw',$this->request->query)){
			$conditionArr = $this->generate_condition_array();
        }
        
        //pr($conditionArr);die;
		if(count($conditionArr)>=1){
			$stocks_query = $this->Products->find('all',array(
									'fields' => array('product_code', 'product', 'color', 'quantity'),
									'conditions' => $conditionArr,
									'contain' => 'Brands'));
			$stocks_query = $stocks_query->hydrate(false);
            if(!empty($stocks_query)){
                $stocks = $stocks_query->toArray();
            }else{
                $stocks = array();
            }
		}else{
			$stocks_query = $this->Products->find('all',array(
														'fields' => array('product_code', 'product', 'color', 'quantity')));
            $stocks_query = $stocks_query->hydrate(false);
            if(!empty($stocks_query)){
                $stocks = $stocks_query->toArray();
            }else{
                $stocks = array();
            }
		}
		
		$tmpstocks = array();
		foreach($stocks as $key => $stock){
			$tmpstocks[] = $stock;
		}
		$this->outputCsv('stock_'.time().".csv" ,$tmpstocks);
		$this->autoRender = false;
	}
    
    public function exportproducts($rawDate =""){
		$rawDate = $this->request->query['date'];
		//$date = date("Y-m-d G:i:s", $rawDate);
		$data_query = $this->UnderstockLevelOrders->find('all',array('conditions'=>array('UnderstockLevelOrders.order_id'=>$rawDate)));
        $data_query = $data_query->hydrate(false);
        if(!empty($data_query)){
            $data = $data_query->toArray();
        }else{
            $data = array();
        }
		$productIdArr = array();
		$product = array();
		foreach($data as $key =>$info){
			$productIdArr[] = $info['product_id'];
			$productquantity[] = $info['quantity'];
		}
		
		foreach($productIdArr as $key=>$productId){
			$products_query = $this->Products->find('all',
			array('conditions'=>array('Products.id'=>$productId),
			'fields' => array('product', 'color','product_code')
			));
            $products_query = $products_query->hydrate(false);
            if(!empty($products_query)){
                $products[] = $products_query->first();
            }else{
                $products[] = array();
            }
		}
		
		$tmpproducts = array();
		
		foreach($products as $key=>$product){
            //pr($product);die;
			$tmpproducts[$key]['product_code'] = $product['product_code'];
			$tmpproducts[$key]['Product'] = $product['product'];
			$tmpproducts[$key]['color'] = $product['color'];
			$tmpproducts[$key]['quantity']  = $productquantity[$key];
		}
		
		$this->outputCsv('Product_'.time().".csv" ,$tmpproducts);
		$this->autoRender = false;
	}
    
    public function allKioskStockSummary(){
		$kiosks_query = $this->Kiosks->find('list',[
                                                'keyField' => 'id',
                                                'valueField' => 'name',
                                                'conditions' => ['Kiosks.status' => 1],
                                                'order' => ['Kiosks.name asc']
                                            ]
                                      );
        $kiosks_query = $kiosks_query->hydrate(false);
        if(!empty($kiosks_query)){
            $kiosks = $kiosks_query->toArray();
        }else{
            $kiosks = array();
        }
		if(empty($this->request->query['start_date'])){
			$dte = date("Y-m-d");
			$start_date = date("Y-m-d",strtotime("-1 day",strtotime($dte)));
		}else{
			$start_date = date("Y-m-d", strtotime($this->request->query['start_date']));
		}
		$search_kw = $this->request->query['search_kw'];
		
		if(!empty($search_kw)){
            //echo'hi';die;
			$productDetail_query = $this->Products->find('all', array('conditions' => array(
															'OR' => array('Products.product like' => "%$search_kw%",
																		  'Products.product_code like' => "%$search_kw%")
																	),
																	'fields' => array('id', 'product_code', 'product'),
																	)
												  );
            $productDetail_query = $productDetail_query->hydrate(false);
            if(!empty($productDetail_query)){
                $productDetail = $productDetail_query->toArray();
            }else{
                $productDetail = array();
            }
			$productIdArr = array();
			$productQuantityArr = array();
			$finalQuantityArr = array();
			$productDetArr = array();
			if(count($productDetail)){
				foreach($productDetail as $key => $productInfo){
					$productIdArr[$productInfo['id']] = $productInfo['id'];
					$productDetArr[$productInfo['id']] = $productInfo;
				}
				
				if(count($productIdArr)){
					foreach($kiosks as $kioskId => $kioskName){
						if($kioskId == 10000 || $kioskId == ""){
							$data_source = "daily_stocks";
						}else{
							$data_source = "kiosk_".$kioskId."_daily_stocks";	
						}
                        $DailyStocksTable = TableRegistry::get($data_source,[
                                                            'table' => $data_source,
                                                        ]);
						$productQuantityArr_query = $DailyStocksTable->find('list', array(
                                                                            'keyField' => 'product_id',
                                                                            'valueField' => 'quantity',
                                                                            //'fields' => array('product_id','quantity'),
                                                'conditions' => array('product_id IN' => $productIdArr, 'date(created)' => $start_date)
                                                                                          ));
                        $productQuantityArr_query = $productQuantityArr_query->hydrate(false);
                        if(!empty($productQuantityArr_query)){
                            $productQuantityArr[$kioskId] = $productQuantityArr_query->toArray();
                        }else{
                            $productQuantityArr[$kioskId] = array();
                        }
					}
					
					foreach($productQuantityArr as $kioskId => $productQuantities){
						foreach($productQuantities as $prduct_id => $productQuantity){
							if(array_key_exists($prduct_id, $finalQuantityArr)){
								$finalQuantityArr[$prduct_id]+=$productQuantity;
							}else{
								$finalQuantityArr[$prduct_id] = $productQuantity;
							}
						}
					}
				}
			}
			$this->set(compact('finalQuantityArr','productDetArr','kiosks'));
			$this->render('all_kiosk_product_quantity');
		}else{
            //echo'bye';die;
			$sumArray = array();
			foreach($kiosks as $kioskId => $kioskName){
				if($kioskId == 10000){
					$data_source = "daily_stocks";
				}else{
					$data_source = "kiosk_".$kioskId."_daily_stocks";	
				}
            $DailyStockTable = TableRegistry::get($data_source,[
                                                            'table' => $data_source,
                                                        ]);
				//$sumArray[$kioskId] = $DailyStockTable->find('first', array('fields' => array("SUM(cost_price*quantity) as total_cost"), 'conditions' => array('date(created)' => $start_date)));
                
                
                $sumArray_query = $DailyStockTable->find('all',['conditions' => ['date(created)' => $start_date]]);
                    $sumArray_query
                          ->select(['total_cost' => $sumArray_query->func()->sum('cost_price*quantity')]);
                if(!empty($sumArray_query)){
                    $res = $sumArray_query->first();
                   $sumArray[$kioskId] = $res->total_cost;
                }else{
                    $sumArray[$kioskId] = array();
                }
                
			}
			$this->set(compact('kiosks','sumArray'));
		}
	}
    
    public function searchSellingPrice($keyword = "",$displayCondition = ""){
        $vat = $this->setting['vat'];
		$this->set('vat', $vat);
        $Product_source = "products";
        $ProductTable = TableRegistry::get($Product_source,[
                                                                'table' => $Product_source,
                                                            ]);
		$displayType = "";
		if(array_key_exists('display_type',$this->request->query)){
			$displayType = $this->request->query['display_type'];
		}
		if(array_key_exists('search_kw',$this->request->query)){
			$searchKW = trim(strtolower($this->request->query['search_kw']));
		}
		$conditionArr = array();
		if(!empty($searchKW)){
			$conditionArr['OR']['LOWER(product) like '] =  strtolower("%$searchKW%");
			$conditionArr['OR']['LOWER(product_code) like '] =  strtolower("%$searchKW%");
		}
		if(array_key_exists('category',$this->request->query) && !empty($this->request->query['category'][0])){
			$conditionArr['category_id IN'] = $this->request->query['category'];
		}
		if($displayType=="more_than_zero"){
			$conditionArr['NOT']['`quantity`'] = 0;
		}
		$this->paginate = [
                            'conditions' => $conditionArr,
                            'limit' => 10
                          ];
		$selectedCategoryId=array();
		if(array_key_exists('category_id IN',$conditionArr) && !empty($conditionArr['category_id IN'][0])){
			$selectedCategoryId=$conditionArr['category_id IN'];
		}
		$product_query = $this->paginate($ProductTable);
        if(!empty($product_query)){
            $products = $product_query->toArray();           
        }else{
            $products = array();
        }
		$categoryIdArr = array();
		foreach($products as $sngProduct){
            //pr($sngProduct);die;
			$categoryIdArr[] = $sngProduct['category_id'];
		}
        if(empty($categoryIdArr)){
            $categoryIdArr = array(0 => null);
        }
		$categoryName_query = $this->Categories->find('list',[
                                                            'conditions'=>['Categories.id IN'=>$categoryIdArr],
                                                            'keyField' => 'id',
                                                            'valueField' => 'category'
                                                       ]
                                                );
        $categoryName_query = $categoryName_query->hydrate(false);
        if(!empty($categoryName_query)){
            $categoryName = $categoryName_query->toArray();
        }else{
            $categoryName = array();
        }
		$this->set(compact('products','displayType'));
		$categories_query = $this->Categories->find('all',array(
								'fields' => array('id', 'category','id_name_path'),
								'conditions' => array('Categories.status' => 1),
								'order' => 'Categories.category asc'
								));
        $categories_query = $categories_query->hydrate(false);
        if(!empty($categories_query)){
            $categories = $categories_query->toArray();
        }else{
            $categories = array();
        }
		$categories = $this->CustomOptions->category_options($categories,true,$selectedCategoryId);
		$this->set(compact('categories','categoryName'));
		//$this->layout = 'default'; 
		//$this->viewPath = 'Products';
		$this->render('index');
	}
    
    public function stockPerKiosk($id = null){
		$kioskList_query = $this->Kiosks->find('all',array('conditions'=>array('Kiosks.status'=>1),'fields'=>array('id','code','name')));
        $kioskList_query = $kioskList_query->hydrate(false);
        if(!empty($kioskList_query)){
            $kioskList = $kioskList_query->toArray();
        }else{
            $kioskList = array();
        }
		$kioskIdArr = array();
		foreach($kioskList as $key=>$kioskDetail){
			$kioskName[$kioskDetail['id']]=$kioskDetail['name'];
			$kioskTableArr[] = "kiosk_{$kioskDetail['id']}_products";
		}
		
		$kioskProduct_query = $this->Products->find('all',array('conditions'=>array('Products.id'=>$id),'fields'=>array('id','product_code','product')));
        $kioskProduct_query = $kioskProduct_query->hydrate(false);
        if(!empty($kioskProduct_query)){
            $kioskProduct = $kioskProduct_query->first();
        }else{
            $kioskProduct = array();
        }
		$product_query = $this->Products->find('all',array('conditions'=>array('quantity < stock_level')));
        $product_query = $product_query->hydrate(false);
        if(!empty($product_query)){
            $product = $product_query->toArray();
        }else{
            $product = array();
        }
		
		foreach($product as $key=>$productDetail){
			$productIdArr[] = $productDetail['id'];
		}
		
		$productQuantity = array();
		foreach($kioskTableArr as $key=>$kioskTable){
			//$productQuantity[] = $this->Product->query("SELECT `quantity`,`id` from $kioskTable WHERE `id` IN ('".implode("','",$productIdArr)."') AND `quantity`>0");
            $conn = ConnectionManager::get('default');
            $stmt = $conn->execute("SELECT `quantity`,`id` from $kioskTable WHERE `id` IN ('".implode("','",$productIdArr)."') AND `quantity`>0"); 
            $productQuantity[$kioskTable] = $stmt ->fetchAll('assoc');
		}
		$productId_arr = array();
		$kioskWiseProduct = array();
		//pr($productQuantity);die;
        foreach($productQuantity as $key=>$pQuantity){
			if(!empty($pQuantity)){
				foreach($pQuantity as $key1=>$p_quantity){
                    //pr($p_quantity);die;
					//foreach($p_quantity as $tableName => $pquantity){
						if(!array_key_exists($p_quantity['id'],$productId_arr)){
							$productId_arr[$p_quantity['id']]=0;
						}
							$kioskWiseProduct[$p_quantity['id']][$kioskName[preg_replace("/[^0-9,.]/", "", $key)]] =$p_quantity['quantity'];
							$productId_arr[$p_quantity['id']]+=$p_quantity['quantity'];
					//}
				}
			}
		}
        //pr($kioskWiseProduct);die;
		
		$this->set(compact('kioskWiseProduct','id','kioskProduct'));
	}
	
	public function adminData($search = ""){
		$kiosk_id = $this->request->Session()->read('kiosk_id');		
		if(!empty($kiosk_id)){
			$productSource = "kiosk_{$kiosk_id}_products";
			$ProductTable = TableRegistry::get($productSource,[
														'table' => $productSource,
															]);
		}else{
			$productSource = "products";
			$ProductTable = TableRegistry::get($productSource,[
														'table' => $productSource,
															]);
		}
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
			$productList_query = $ProductTable->find('all',array(
															'fields'=> array('product','product_code','quantity'),
															'conditions' => $searchArray
												)
						    );
			//pr($productList_query);die;
			$productList_query->hydrate(false);
			$productList = $productList_query->toArray();
		}else{
			$productList_query = $ProductTable->find('all',array(
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
}
?>