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

class WarehouseStocksController extends AppController{
     public $helpers = [
         'Paginator' => ['templates' => 'paginatortemplates']
         ];
    public function initialize()
    {
        parent::initialize();
        $this->loadComponent('SessionRestore');
        $this->loadComponent('ScreenHint');
        $this->loadComponent('CustomOptions');
        $this->loadModel('Categories');
        $this->loadModel('Products');
        $this->loadModel('WarehouseVendors');
        $this->loadModel('Users');
        $this->loadModel('WarehouseStock');
		$this->loadModel('Settings');
		
        $CURRENCY_TYPE = Configure::read('CURRENCY_TYPE');
        $this->set(compact('CURRENCY_TYPE' ));
    }
    
    public function index() {
		//pr($_SESSION);die;
		$categories_query = $this->Categories->find('all',array(
								'fields' => array('id', 'category','id_name_path'),
                                                                'conditions' => array('Categories.status' => 1),
								'order' => 'Categories.category asc',
								));
        $categories_query = $categories_query->hydrate(false);
        if(!empty($categories_query)){
            $categories = $categories_query->toArray();
        }else{
            $categories = array();
        }
		$categories = $this->CustomOptions->category_options($categories,true);
		$warehouseVendors_query = $this->WarehouseVendors->find('list',
                                                                [
                                                                    'keyField' => 'id',
                                                                    'valueField' => 'vendor',
                                                                    'conditions' => ['WarehouseVendors.status' => 1]
                                                                ]
								);
		$warehouseVendors_query = $warehouseVendors_query->hydrate(false);
        if(!empty($warehouseVendors_query)){
            $warehouseVendors = $warehouseVendors_query->toArray();
        }else{
            $warehouseVendors = array();
        }
        
        //pr($this->request);die;
		if(!empty($this->request->query)){
			if(array_key_exists('display_type',$this->request->query)){
				$displayType = $this->request->query['display_type'];
				//echo'hi';die;
				$queryStr = $this->searchUrl();
				$categories = $queryStr[0];
				$conditionArr = $queryStr[1];
				//pr($queryStr);
				//pr($conditionArr);die;
				$this->Products->find('all',array('conditions' => $conditionArr));
				
				$this->paginate = array(
								   'limit' => ROWS_PER_PAGE,
									'order' => ['modified' => 'desc'],
								   'conditions' => $conditionArr);
				//pr($this->paginate);die;
			}
		}elseif ($this->request->is(array('get', 'put'))) {
			//echo'bye';die;
			if(array_key_exists('display_type',$this->request->query)){
				$displayType = $this->request->query['display_type'];
				if($displayType=="show_all"){
					$this->paginate = array(
									    'limit' => ROWS_PER_PAGE,
										'order' => ['modified' => 'desc'],
									    );
					//$this->Product->recursive = 0;
				}elseif($displayType=="more_than_zero"){
					$this->paginate= array('limit' => ROWS_PER_PAGE,
										'order' => ['modified' => 'desc'],
									   'conditions'=>array('NOT'=>array('Products.quantity'=>0))
									   );
					$this->Product->recursive = 0;
				}
			}	
		}
		$warehouseBasket = $this->request->Session()->read('WarehouseBasket');
		//pr($warehouseBasket);
		
		$basketStrDetail = '';
		$productInOut = array('1'=>'In','0'=>'Out');
		if(is_array($warehouseBasket)){
			$productCodeArr = array();
			//$warehouseBasket = array_reverse($warehouseBasket,true);
			foreach($warehouseBasket as $key => $basketItem){
                $productCode_query = $this->Products->find('all',array('conditions'=>array('Products.id'=>$key),
                                                                        'fields'=>array('id','product_code')
                                                                        ));
                $productCode_query = $productCode_query->hydrate(false);
                $productCodeArr[] = $productCode_query->first();
			}
			$productCode = array();
			if(!empty($productCodeArr)){
				foreach($productCodeArr as $k=>$productCodeData){
					$productCode[$productCodeData['id']]=$productCodeData['product_code'];
				}
			}
			foreach($warehouseBasket as $productId=>$productDetails){
				$productName_query = $this->Products->find('all',array('conditions'=>array('Products.id'=>$productId),
                                                                  'fields'=>array('id','product')
                                                                  ));
                $productName_query = $productName_query->hydrate(false);
                $productName = $productName_query->first();
				$basketStrDetail.= "<tr>
				<td>".$productCode[$productId]."</td>
				<td>".$productName['product']."</td>
				<td>".$productDetails['new_rcp']."</td>
				<td>".$productDetails['new_rsp']."</td>
				<td>".$productDetails['price']."</td>
				<td>".$productDetails['new_selling_price']."</td>
				<td>".$productDetails['quantity']."</td>
				<td>".$productInOut[$productDetails['in_out']]."</td>
				<td>".$productDetails['remarks']."</td>
				</tr>";
			}
			
		}
		
			if(!empty($basketStrDetail)){
				$basketStr = "<table>
				<tr>
					<th style='width: 128px;'>Product code</th>
					<th style='width: 600px;'>Product</th>
					<th style='width: 40px;'>New RCP</th>
					<th style='width: 45px;'>New RSP</th>
					<th style='width: 40px;'>New CP</th>
					<th style='width: 41px;'>New SP</th>
					<th style='width: 36px;'>Qty</th>
					<th style='width: 45px;'>Type</th>
					<th style='width: 80px;'>Remarks</th>
				</tr>".$basketStrDetail."
				</table>";
			}
			
			$totalItems = count($warehouseBasket);
			if($totalItems){
				//pr($_SESSION);die;
				$flashMessage = "Total item Count:$totalItems<br/>$basketStr";
				$this->Flash->success($flashMessage,array('escape' => false));
			}
		$hint = $this->ScreenHint->hint('warehouse_stocks','index');
        if(!$hint){
            $hint = "";
        }
//		$this->paginte = [
//						'limit' => ROWS_PER_PAGE,
//						'order' => ['modified' => 'desc'],
//                         ];
		$warehouseStocks_query = $this->paginate('Products');
        $warehouseStocks = $warehouseStocks_query->toArray();
		//pr($warehouseStocks);die;
		//$this->set('warehouseStocks', $this->Paginator->paginate());
		$this->set(compact('hint','categories','warehouseVendors','displayType','warehouseStocks'));
	}
    
    public function search($keyword = "",$displayCondition = ""){
		//pr($_SESSION);die;
		 $warehouseBasket = $this->request->Session()->read('WarehouseBasket');
		 $warehouseBasket = $this->show_session($warehouseBasket);
		//$warehouseBasket = array_reverse($warehouseBasket,true);
		$displayType = "";
		if(array_key_exists('display_type',$this->request->query)){
			$displayType = $this->request->query['display_type'];
		}
		if(array_key_exists('search_kw',$this->request->query)){
			$search_kw = $this->request->query['search_kw'];
		}
		extract($this->request->query);
		$categories_query = $this->Categories->find('all',array(
								'fields' => array('id', 'category','id_name_path'),
                                                                'conditions' => array('Categories.status' => 1),
								'order' => 'Categories.category asc',
								));
        $categories_query = $categories_query->hydrate(false);
        if(!empty($categories_query)){
            $categories = $categories_query->toArray();
        }else{
            $categories = array();
        }
		$conditionArr = array();
		//----------------------
		if(!empty($search_kw)){
			$conditionArr['OR']['LOWER(product) like '] =  strtolower("%$search_kw%");
			$conditionArr['OR']['LOWER(product_code) like '] =  strtolower("%$search_kw%");
		}
		if(array_key_exists('category',$this->request->query) && !empty($this->request->query['category'][0])){
			$conditionArr['category_id IN'] = $this->request->query['category'];
		}
		if($displayType=="more_than_zero"){
			$conditionArr['NOT']['`Products`.`quantity`'] = 0;
		}
		
		$this->Products->find('all',array('conditions' => $conditionArr));
		//--------code for reading cake query---
		/*$dbo = $this->Product->getDatasource();
		$logData = $dbo->getLog();
		$getLog = end($logData['log']);
		echo $getLog['query'];*/
                //--------code for reading cake query---
		$this->paginate = array(
						   'limit' => ROWS_PER_PAGE,
						   'conditions' => $conditionArr,
						   'order' => array('modified' => 'desc'),
						   );
		$selectedCategoryId=array();
		if(array_key_exists('category_id IN',$conditionArr) && !empty($conditionArr['category_id IN'][0])){
			$selectedCategoryId=$conditionArr['category_id IN'];
		}
		$categories = $this->CustomOptions->category_options($categories,true,$selectedCategoryId);
		$warehouseVendors = $this->WarehouseVendors->find('list',
                                                                    [
                                                                        'keyField' => 'id',
                                                                        'valueField' => 'vendor',
                                                                        'conditions' => ['WarehouseVendors.status' => 1]
                                                                    ]);
		$warehouseStocks = $this->paginate('Products');
		//$this->set('Product', $this->Paginator->paginate());
		$hint = $this->ScreenHint->hint('warehouse_stocks','index');
					if(!$hint){
						$hint = "";
					}
		$this->set(compact('hint','categories','warehouseVendors','displayType','warehouseStocks'));
		$this -> render('index');
	}
    
    
    private function show_session($warehouseBasket){
		$basketStrDetail = '';
		$productInOut = array('1'=>'In','0'=>'Out');
		if(is_array($warehouseBasket)){
			$productCodeArr = array();
			//$warehouseBasket = array_reverse($warehouseBasket,true);
			foreach($warehouseBasket as $key => $basketItem){
                $productCode_query = $this->Products->find('all',
                                                          array('conditions'=>array('Products.id'=>$key),
                                                                'fields'=>array('id','product_code')
                                                          ));
                $productCode_query = $productCode_query->hydrate(false);
                $productCodeArr[] = $productCode_query->first();
			}
			$productCode = array();
			if(!empty($productCodeArr)){
				foreach($productCodeArr as $k=>$productCodeData){
					$productCode[$productCodeData['id']]=$productCodeData['product_code'];
				}
			}
			foreach($warehouseBasket as $productId=>$productDetails){
				$productName_query = $this->Products->find('all',
                                                     array('conditions'=>array('Products.id'=>$productId),
                                                           'fields'=>array('id','product')
                                                           )
                                                     );
                $productName_query = $productName_query->hydrate(false);
                $productName = $productName_query->first();
				$basketStrDetail.= "<tr>
				<td>".$productCode[$productId]."</td>
				<td>".$productName['product']."</td>
				<td>".$productDetails['new_rcp']."</td>
				<td>".$productDetails['new_rsp']."</td>
				<td>".$productDetails['price']."</td>
				<td>".$productDetails['new_selling_price']."</td>
				<td>".$productDetails['quantity']."</td>
				<td>".$productInOut[$productDetails['in_out']]."</td>
				<td>".$productDetails['remarks']."</td>
				</tr>";
			}
			
		}
		
			if(!empty($basketStrDetail)){
				$basketStr = "<table>
				<tr>
					<th style='width: 128px;'>Product code</th>
					<th style='width: 600px;'>Product</th>
					<th style='width: 40px;'>New RCP</th>
					<th style='width: 45px;'>New RSP</th>
					<th style='width: 40px;'>New CP</th>
					<th style='width: 41px;'>New SP</th>
					<th style='width: 36px;'>Qty</th>
					<th style='width: 45px;'>Type</th>
					<th style='width: 80px;'>Remarks</th>
				</tr>".$basketStrDetail."
				</table>";
			}
			 
			$totalItems = count($warehouseBasket);
			if($totalItems){
				//pr($_SESSION);die;
				$flashMessage = "Total item Count:$totalItems<br/>$basketStr";
				$this->Flash->success($flashMessage,array('escape' => false));
			}
	}
    
    function updateStock(){
		//pr($this->request);//die;
		//pr($_SESSION);die;
		$sessionKioskId = "10000";
		$current_page='';
		if(array_key_exists('current_page',$this->request['data'])){
			$current_page = $this->request['data']['current_page'];
		}
		if(!isset($current_page)){$this->redirect(array('action' => 'index'));}
		
		$counter = 0;
		$warehouseBasket = $failedStock = $successfullySaved = $stock = array();
		$failedStockErr = $warehouse_vendor_id = $reference_number = '';
		
		if(array_key_exists('searchQueryUrl',$this->request['data'])){
			$searchQueryUrl = $this->request['data']['searchQueryUrl'];
		}
		
		if(array_key_exists('WarehouseStock',$this->request['data'])){
			$warehouse_vendor_id = $this->request['data']['WarehouseStock']['warehouse_vendor_id'];
			$reference_number = $this->request['data']['WarehouseStock']['reference_number'];
		}
		
		if(!isset($current_page)){$this->redirect(array('action' => 'index'));}
		if(array_key_exists('add_2_basket',$this->request['data'])){
			if(!empty($warehouse_vendor_id)){
				$this->request->Session()->write('warehouse_vendor_id', $warehouse_vendor_id);
			}
			if(!empty($reference_number)){
				$this->request->Session()->write('reference_number', $reference_number);
			}
			$warehouseSessionData = array();
			foreach($this->request['data']['WarehouseStock']['quantity'] as $key=>$quantity){
				$inOut = $this->request['data']['WarehouseStock']['in_out'][$key];
				$remarks = $this->request['data']['WarehouseStock']['remarks'][$key];
				$price = $this->request['data']['WarehouseStock']['price'][$key];
				$product_id = $this->request['data']['WarehouseStock']['product_id'][$key];
				$new_selling_price = $this->request['data']['WarehouseStock']['new_selling_price'][$key];
				$new_rcp = $this->request['data']['WarehouseStock']['new_rcp'][$key];
				$new_rsp = $this->request['data']['WarehouseStock']['new_rsp'][$key];
				if($new_rcp == '--'){$new_rcp='';}if(empty($new_rcp)) $new_rcp = '';
				if($new_rsp == '--'){$new_rsp='';}if(empty($new_rsp)) $new_rsp = '';
				if($price == 0 || $price == ""){continue;}
				//if($new_rcp == 0 || $new_rcp == ""){continue;}
				//if($new_rsp == 0 || $new_rsp == ""){continue;}
				$isboloRam = false;
				$path = dirname(__FILE__);
						$sites = Configure::read('sites');
						 foreach($sites  as $key => $value){
							  $isboloRam = strpos($path,$value);
							  if($isboloRam){
								   break;
							  }
						 }
						if($isboloRam != false){
							;//if code running on boloram server	
						}else{
							if($new_rcp == 0 || $new_rcp == ""){
								$error_array[] = $product_id;
								continue;
								}
							if($new_rsp == 0 || $new_rsp == ""){
								$error_array[] = $product_id;
								continue;
							}
						}
				
				if(!empty($quantity)){
					$warehouseSessionData[$product_id] = array(
																'quantity' => $quantity,
																'price' => $price,
																'new_selling_price'=> $new_selling_price,
																'new_rcp' => $new_rcp,
																'new_rsp'=> $new_rsp,
																'in_out' => $inOut,
																'remarks' => $remarks,
																'warehouse_vendor_id' => $warehouse_vendor_id,
																'reference_number' => $reference_number
															);
				}
			}
			$warehouseBasket = $this->request->Session()->read('WarehouseBasket');
			//pr($warehouseBasket);
			if(count($warehouseBasket) >= 1){
				$sum_total = $this->add_arrays(array($warehouseSessionData,$warehouseBasket));
				//pr($sum_total);
				$this->request->Session()->write('WarehouseBasket',$sum_total);
			}elseif(count($warehouseSessionData) > 0){
				//pr($warehouseSessionData);die;
				//$warehouseBasket = array_reverse($warehouseSessionData,true);
				$this->request->Session()->write('WarehouseBasket',$warehouseSessionData);
			}
			$warehouseBasket = $this->request->Session()->read('WarehouseBasket');
		 
			
			$basketStrDetail = '';
			$productInOut = array('1'=>'In','0'=>'Out');
			if(is_array($warehouseBasket)){
				$productCodeArr = array();
				foreach($warehouseBasket as $key => $basketItem){
					
                    $productCode_query = $this->Products->find('all', array(
																			'conditions' => array('Products.id' => $key),
																			'fields' => array('id','product_code'),
																			));
                    $productCode_query = $productCode_query->hydrate(false);
                    $productCodeArr[] = $productCode_query->first();
				}
				$productCode = array();
				if(!empty($productCodeArr)){
					foreach($productCodeArr as $k=>$productCodeData){
						$productCode[$productCodeData['id']]=$productCodeData['product_code'];
					}
				}
				foreach($warehouseBasket as $productId=>$productDetails){
					$productName_query = $this->Products->find('all', array(
																	   'conditions' => array('Products.id'=>$productId),
																	   'fields' => array('id','product'),
																	   ));
                    $productName_query = $productName_query->hydrate(false);
                    $productName = $productName_query->first();
					$basketStrDetail.= "<tr>
					<td>".$productCode[$productId]."</td>
					<td>".$productName['product']."</td>
					<td>".$productDetails['new_rcp']."</td>
					<td>".$productDetails['new_rsp']."</td>
					<td>".$productDetails['price']."</td>
					<td>".$productDetails['new_selling_price']."</td>
					<td>".$productDetails['quantity']."</td>
					<td>".$productInOut[$productDetails['in_out']]."</td>
					<td>".$productDetails['remarks']."</td>
					</tr>";
				}
			}
			if(!empty($basketStrDetail)){
				$basketStr = "<table>
				<tr>
					<th style='width: 128px;'>Product code</th>
					<th style='width: 600px;'>Product</th>
					<th style='width: 40px;'>New RCP</th>
					<th style='width: 45px;'>New RSP</th>
					<th style='width: 40px;'>New CP</th>
					<th style='width: 41px;'>New SP</th>
					<th style='width: 36px;'>Quantity</th>
					<th style='width: 45px;'>Type</th>
					<th style='width: 80px;'>Remarks</th>
				</tr>".$basketStrDetail."
				</table>";
			}
				
			$totalItems = count($warehouseBasket);
			$productCounts = count($warehouseSessionData);
			if($productCounts){
				$this->SessionRestore->update_session_backup_table($this->request->params['controller'], 'index', 'warehouse_vendor_id', $warehouse_vendor_id, $sessionKioskId);
				$this->SessionRestore->update_session_backup_table($this->request->params['controller'], 'index', 'reference_number', $reference_number, $sessionKioskId);
				$this->SessionRestore->update_session_backup_table($this->request->params['controller'], 'index', 'WarehouseBasket', $warehouseBasket, $sessionKioskId);
				//die;
				$flashMessage = "Total item Count:$totalItems<br/>$basketStr";
			}else{
				$flashMessage = "No item added to the cart. Item Count:$productCounts";
				$this->Flash->error($flashMessage,array('escape' => false));
			}
			
			//$this->Flash->success($flashMessage,array('escape' => false));
			
			if(!empty($searchQueryUrl)){
				return $this->redirect(array('action'=>"index/page:$current_page"));
				//return $this->redirect(array('action'=>"index$searchQueryUrl"));
			}else{
				return $this->redirect(array('action'=>"index/page:$current_page"));
			}
		}elseif(array_key_exists('clear_basket',$this->request['data'])){
			$this->request->Session()->delete('WarehouseBasket');
			$this->request->Session()->delete('reference_number');
			$this->request->Session()->delete('warehouse_vendor_id');
			$this->SessionRestore->delete_from_session_backup_table($this->request->params['controller'], 'index', 'warehouse_vendor_id', $sessionKioskId);
			$this->SessionRestore->delete_from_session_backup_table($this->request->params['controller'], 'index', 'reference_number', $sessionKioskId);
			$this->SessionRestore->delete_from_session_backup_table($this->request->params['controller'], 'index', 'WarehouseBasket', $sessionKioskId);
			$this->Flash->success("Basket is empty; Add new items to cart!");
			return $this->redirect(array('action'=>"index/page:$current_page"));
		}elseif(array_key_exists('check_out',$this->request['data'])){
			if(isset($this->request['data']['WarehouseStock']['reference_number'])){
				$reference_number = $this->request['data']['WarehouseStock']['reference_number'];
				$this->request->Session()->write('reference_number', $reference_number);
			}
			if(isset($this->request['data']['WarehouseStock']['warehouse_vendor_id'])){
				$warehouse_vendor_id = $this->request['data']['WarehouseStock']['warehouse_vendor_id'];
				$this->request->Session()->write('warehouse_vendor_id', $warehouse_vendor_id);
			}
			return $this->redirect(array('action'=>'in_out_checkout'));
		}else{
			//pr($this->request);die;
			$flashMessage = "";
			$warehouseSessionData = array();
			if(array_key_exists('WarehouseStock',$this->request['data'])){
				if(isset($reference_number)){
				$this->request->Session()->write('reference_number', $reference_number);
				}
				if(isset($warehouse_vendor_id)){
					$this->request->Session()->write('warehouse_vendor_id', $warehouse_vendor_id);
				}
				//pr($this->request);
				$s_counter = 0;$error_array = array();
				foreach($this->request['data']['WarehouseStock']['quantity'] as $key => $quantity){
					$warehouseData = array();
					if(!empty($quantity)){
						$s_counter ++;
						$inOut = $this->request['data']['WarehouseStock']['in_out'][$key];
						$remarks = $this->request['data']['WarehouseStock']['remarks'][$key];
						$price = $this->request['data']['WarehouseStock']['price'][$key];
						$product_id = $this->request['data']['WarehouseStock']['product_id'][$key];
						$new_selling_price = $this->request['data']['WarehouseStock']['new_selling_price'][$key];
						$new_rcp = $this->request['data']['WarehouseStock']['new_rcp'][$key];
						$new_rsp = $this->request['data']['WarehouseStock']['new_rsp'][$key];
						if($price == "" || $price == 0){continue;}
						if($new_selling_price == "" || $new_selling_price == 0){continue;}
						$isboloRam = false;
						 $path = dirname(__FILE__);
						$sites = Configure::read('sites');
						 foreach($sites  as $key => $value){
							  $isboloRam = strpos($path,$value);
							  if($isboloRam){
								   break;
							  }
						 }
						if($isboloRam != false){
							;//if code running on boloram server	
						}else{
							if($new_rcp == 0 || $new_rcp == ""){
								$error_array[] = $product_id;
								continue;
								}
							if($new_rsp == 0 || $new_rsp == ""){
								$error_array[] = $product_id;
								continue;
							}
						}
						$warehouseSessionData[$product_id] = array(
																	'quantity' => $quantity,
																	'price' => $price,//cost price
																	'new_selling_price' => $new_selling_price,
																	'in_out' => $inOut,
																	'remarks' => $remarks,
																	'warehouse_vendor_id' => $warehouse_vendor_id, //missing
																	'reference_number' => $reference_number //addition
																);
						$warehouseSessionData[$product_id]['new_rcp'] = $new_rcp;
						$warehouseSessionData[$product_id]['new_rsp'] = $new_rsp;
						
						//if(!(int)$new_rcp || !(float)$new_rcp){
						//	;$warehouseSessionData[$product_id]['new_rcp'] = 0;
						//}else{
						//	$warehouseSessionData[$product_id]['new_rcp'] = $new_rcp;
						//}
						//	
						//if(!(int)$new_rsp || !(float)$new_rsp){
						//	;$warehouseSessionData[$product_id]['new_rsp'] = 0;
						//}else{
						//	$warehouseSessionData[$product_id]['new_rsp'] = $new_rsp;
						//}
						
						if($isboloRam != false){
							if(empty($warehouseSessionData[$product_id]['new_rcp'])){$warehouseSessionData[$product_id]['new_rcp'] = 0;}
							if(empty($warehouseSessionData[$product_id]['new_rsp'])){$warehouseSessionData[$product_id]['new_rsp'] = 0;}
						}
					}
				}
					if(!empty($error_array)){
						$error_string = implode(',',$error_array);
						$failedStock[] = 'zero cost and selling price for product id'.$error_string;
					}
			}
			$warehouseBasket = $this->request->Session()->read('WarehouseBasket');
			if(empty($warehouseBasket) && empty($warehouseSessionData) && $s_counter == 0){
				$failedStock[] = "no item selected";
			}
			if(!empty($failedStock)){
				$failedStockErr = implode("<br/>",$failedStock);
				$flashMessage.=$failedStockErr;
				$this->Flash->error($flashMessage,array('escape' => false));
				return $this->redirect(array('action' => "index/page:$current_page"));
			}
			$warehouseBasket = $this->request->Session()->read('WarehouseBasket');
			if(count($warehouseBasket) >= 1){
				$sum_total = $this->add_arrays(array($warehouseSessionData, $warehouseBasket));
				$this->request->Session()->write('WarehouseBasket', $sum_total);
			}elseif(count($warehouseSessionData) > 0){
				$this->request->Session()->write('WarehouseBasket',$warehouseSessionData);
			}
			
			$warehouseBasket = $this->request->Session()->read('WarehouseBasket');
	
			//pr($warehouseBasket);die;
			$warehouse_vendor_id = $this->request->Session()->read('warehouse_vendor_id');
			$reference_number = $this->request->Session()->read('reference_number');
			$counter = 0;
			$successfullySaved = array();
			
			if(!empty($warehouseBasket) && !empty($reference_number)){
				$this->SessionRestore->update_session_backup_table($this->request->params['controller'], 'index', 'warehouse_vendor_id', $warehouse_vendor_id, $sessionKioskId);
				$this->SessionRestore->update_session_backup_table($this->request->params['controller'], 'index', 'reference_number', $reference_number, $sessionKioskId);
				$this->SessionRestore->update_session_backup_table($this->request->params['controller'], 'index', 'WarehouseBasket', $warehouseBasket, $sessionKioskId);
				//getting the data before saving to products table for updating the last update fields
				$productIds = array_keys($warehouseBasket);
				$oldProductData_query = $this->Products->find('all',array(
																   'conditions' => array('Products.id IN' => $productIds),
																   'fields' => array('id','cost_price','retail_cost_price','selling_price','retail_selling_price')));
                $oldProductData_query = $oldProductData_query->hydrate(false);
                $oldProductData = $oldProductData_query->toArray();
				$oldPrdctInfo = array();
				if(!empty($oldProductData)){
					foreach($oldProductData as $key => $oldProducts){
						$oldPrdctInfo[$oldProducts['id']] = $oldProducts;
					}
				}
				//till here
				
				foreach($warehouseBasket as $productId=>$productSession){
					$new_selling_price = $productSession['new_selling_price'];
					$user_id = $this->request->Session()->read('Auth.User.id');
					if($productSession['in_out']==1){
						$productQuantity = $productSession['quantity'];
					}elseif($productSession['in_out']==0){
						$productQuantity = -$productSession['quantity'];
					}
					//starts
					
					$warehouseData = array(
											'quantity' => $productQuantity,
											'price' => $productSession['price'],
											'in_out' => $productSession['in_out'],
											'user_id' => $user_id,
											'remarks' => $productSession['remarks'],
											'product_id' => $productId,
											'warehouse_vendor_id' => $warehouse_vendor_id, //missing
											'reference_number' => $reference_number //addition
										);
					//pr($warehouseData);die;
                    $WarehouseStock = $this->WarehouseStock->newEntity();
                    
                    
					//$this->WarehouseStock->create($warehouseData);
					
					$product_query = $this->Products->find('all',array(
																	'fields' => array('qty_update_status','qty_update_time','quantity'),
																	'conditions' => array('id' => $productId),
																	'recursive' => -1
																)
													);
                    $product_query = $product_query->hydrate(false);
                    $product = $product_query->first();
					$qty_update_time = $qty_update_status = "";
                    if(!empty($product) && array_key_exists('quantity',$product)){
                        $prevQuantity = $product['quantity'];
						$qty_update_status = $product['qty_update_status'];
						$qty_update_time = $product['qty_update_time'];
                    }
					
					//------------------------------
					$currentDate = date("Y-m-d H:i:s");
					$productSessionQuantity = $productSession['quantity'];
					if($productSession['in_out'] == 1){
						//add stock
						$isboloRam = false;
						 $path = dirname(__FILE__);
						$sites = Configure::read('sites');
						 foreach($sites  as $key => $value){
							  $isboloRam = strpos($path,$value);
							  if($isboloRam){
								   break;
							  }
						 }
						if($isboloRam){
							if(empty($productSession['new_rcp'])){
								$productSession['new_rcp'] = 0;
							}
							if(empty($productSession['new_rsp'])){
								$productSession['new_rsp'] = 0;
							}
						}
						$this->Products->updateAll(
													array(
															//'products.quantity' => "products.quantity + $productSessionQuantity",
															'products.cost_price' => $productSession['price'],
															'products.selling_price' => $productSession['new_selling_price'],
															'products.retail_cost_price' => $productSession['new_rcp'],
															'products.retail_selling_price' => $productSession['new_rsp']
														),
													array('products.id' => $productId)
												);
						
						$warehouseData['current_stock'] = $prevQuantity + $productSessionQuantity;
						
						
						$settingArr = $this->Settings->find("list",[
													  'keyField' =>'attribute_name',
													  'valueField' => 'attribute_value',
													  ])->toArray(); 
						
						if($qty_update_status == 0){
						 $query = "UPDATE `products` SET modified='$currentDate',quantity = quantity + '$productSessionQuantity',`qty_update_status` = 1, `qty_update_time` = NOW() WHERE id='$productId'";
						}elseif($qty_update_status == 1){
						 $days = $settingArr['new_arrival_days_limit'];
						 $date = strtotime(date('Y-m-d', strtotime("+$days day", strtotime($qty_update_time))));
						 $current_date = strtotime(date("y-m-d"));
						 if($current_date > $date){
							  $query = "UPDATE `products` SET modified='$currentDate',`qty_update_status` = 2,quantity = quantity + '$productSessionQuantity' WHERE id='$productId'";		 	  
						 }else{
							  $query = "UPDATE `products` SET modified='$currentDate',quantity = quantity + '$productSessionQuantity' WHERE id='$productId'";		 	  
						 }
						 
						}else{
							  $query = "UPDATE `products` SET modified='$currentDate',quantity = quantity + '$productSessionQuantity' WHERE id='$productId'";
						}
						
                         
                        $conn = ConnectionManager::get('default');
                        $stmt = $conn->execute($query);
						$WarehouseStock = $this->WarehouseStock->patchEntity($WarehouseStock, $warehouseData,['validate' => false]);
						 // $this->WarehouseStocks->save($WarehouseStock,['validate' => false]);
						//---------------------------------------------
						if($this->WarehouseStock->save($WarehouseStock,['validate' => false])){
							$successfullySaved[$counter++] = $productId;
						}
						//--------code for reading cake query---
						//die;
						//--------code for reading cake query---
					}elseif($productSession['in_out'] == 0){
						if(($prevQuantity - $productSession['quantity']) >= 0){						
							//subtrack stock
							$this->Products->updateAll(
									array( //'products.quantity' => "products.quantity - $productSessionQuantity",
									'products.cost_price' => $productSession['price'],
									'products.selling_price' => $productSession['new_selling_price'],
									'products.retail_cost_price' => $productSession['new_rcp'],
									'products.retail_selling_price' => $productSession['new_rsp']),
									array('Products.id' => $productId)
								);
                            
                            $query1 = "UPDATE `products` SET modified='$currentDate',quantity = quantity - '$productSessionQuantity' WHERE id='$productId'";
                            $conn = ConnectionManager::get('default');
                             $stmt = $conn->execute($query1);
							 
							 $warehouseData['current_stock'] = $prevQuantity - $productSessionQuantity;
							 
							 $WarehouseStock = $this->WarehouseStock->patchEntity($WarehouseStock, $warehouseData,['validate' => false]);
							//---------------------------------------------
							if($this->WarehouseStock->save($WarehouseStock,['validate' => false])){
								$successfullySaved[$counter++] = $productId;
							}
						}else{
							$failedStock[] = "Failed to stock out for Product $productId";
						}
					}
						//ends
				}
			}else{
				$failedStock[] = 'Reference number is missing!';
			}
		}
		
		$failedStockErr = implode("<br/>",$failedStock);
		if (count($successfullySaved)) {
			//Code for updating last lu_rcp,lu_cp,lu_sp,lu_rsp
			$newProductData_query = $this->Products->find('all',array('conditions'=>array('Products.id IN'=>$successfullySaved),'fields'=>array('id','cost_price','retail_cost_price','selling_price','retail_selling_price')));
            $newProductData_query = $newProductData_query->hydrate(false);
            if(!empty($newProductData_query)){
                $newProductData = $newProductData_query->toArray();
            }else{
                $newProductData = array();
            }
            
			$newPrdctInfo = array();
			if(!empty($newProductData)){
				foreach($newProductData as $key=>$newProducts){
					$newPrdctInfo[$newProducts['id']]=$newProducts;
				}
			}
			
			$currentTimequery = "SELECT NOW() as created From products";
            $conn = ConnectionManager::get('default');
            $stmt = $conn->execute($currentTimequery);
            $currentTimeInfo = $stmt ->fetchAll('assoc');
            
			$currentTime = $currentTimeInfo[0]['created'];
			
			//pr($successfullySaved);//pr($newPrdctInfo);
			//die;
			foreach($successfullySaved as $key=>$savedProductid){
				$old_cost_price = $oldPrdctInfo[$savedProductid]['cost_price'];
				$old_retail_cost_price = $oldPrdctInfo[$savedProductid]['retail_cost_price'];
				$old_selling_price = $oldPrdctInfo[$savedProductid]['selling_price'];
				$old_retail_selling_price = $oldPrdctInfo[$savedProductid]['retail_selling_price'];
				
				$new_cost_price = $newPrdctInfo[$savedProductid]['cost_price'];
				$new_retail_cost_price = $newPrdctInfo[$savedProductid]['retail_cost_price'];
				$new_selling_price = $newPrdctInfo[$savedProductid]['selling_price'];
				$new_retail_selling_price = $newPrdctInfo[$savedProductid]['retail_selling_price'];
				
				if($old_cost_price!=$new_cost_price){
					$lu_cp_query = "UPDATE `products` SET `lu_cp`='$currentTime' WHERE `id`='$savedProductid'";
                    $conn = ConnectionManager::get('default');
                    $stmt = $conn->execute($lu_cp_query);
				}
				
				if($old_retail_cost_price!=$new_retail_cost_price){
                    $lu_rcp_query = "UPDATE `products` SET `lu_rcp`='$currentTime' WHERE `id`='$savedProductid'";
					$conn = ConnectionManager::get('default');
                    $stmt = $conn->execute($lu_rcp_query);
				}
				
				if($old_selling_price!=$new_selling_price){
                    $lu_sp_query = "UPDATE `products` SET `lu_sp`='$currentTime' WHERE `id`='$savedProductid'";
					$conn = ConnectionManager::get('default');
                    $stmt = $conn->execute($lu_sp_query);
				}
				
				if($old_retail_selling_price!=$new_retail_selling_price){
                    $lu_rsp_query = "UPDATE `products` SET `lu_rsp`='$currentTime' WHERE `id`='$savedProductid'";
					$conn = ConnectionManager::get('default');
                    $stmt = $conn->execute($lu_rsp_query);
				}
			}
			$flashMessage = "Stock for {$counter} item(s) udpated.";
			if(!empty($failedStockErr))$flashMessage.="<br/>".$failedStockErr;
			$this->Flash->success($flashMessage,array('escape' => false));			
		} else {
			$flashMessage = "Stock couldn't be updated. Please try again!'";
			if(!empty($failedStockErr))$flashMessage.="<br/>".$failedStockErr;
			$this->Flash->error($flashMessage,array('escape' => false));
		}
		if (count($successfullySaved)) {
			$this->request->Session()->delete('WarehouseBasket');
			$this->request->Session()->delete('reference_number');
			$this->request->Session()->delete('warehouse_vendor_id');
			$this->SessionRestore->delete_from_session_backup_table($this->request->params['controller'], 'index', 'warehouse_vendor_id', $sessionKioskId);
			$this->SessionRestore->delete_from_session_backup_table($this->request->params['controller'], 'index', 'reference_number', $sessionKioskId);
			$this->SessionRestore->delete_from_session_backup_table($this->request->params['controller'], 'index', 'WarehouseBasket', $sessionKioskId);
		}
		return $this->redirect(array('action' => "index/page:$current_page"));
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
	
		return $allValues;
    }
    
    public function inOutCheckout(){
		$warehouseBasket = $this->request->Session()->read('WarehouseBasket');
		$productCodeArr = array();
		foreach($warehouseBasket as $key => $basketItem){
		
         $productCode_query = $this->Products->find('all',
                                                  array('conditions'=>array('Products.id'=>$key),
                                                        'fields'=>array('id','product_code'))
                                                  );
         $productCode_query = $productCode_query->hydrate(false);
         $productCodeArr[]  = $productCode_query->first();
		}
		$productCode = array();
		if(!empty($productCodeArr)){
			foreach($productCodeArr as $k=>$productCodeData){
				$productCode[$productCodeData['id']]=$productCodeData['product_code'];
			}
		}
		$basketStrDetail = '';
		$productInOut = array('1'=>'In','0'=>'Out');
		if(is_array($warehouseBasket)){
			$productNameArr = array();
			foreach($warehouseBasket as $productId=>$productDetails){
                $productName_query = $this->Products->find('all',
                                                          array('conditions'=>array('Products.id'=>$productId),
                                                                'fields'=>array('id','product'),'recursive'=>-1)
                                                          );
                $productName_query = $productName_query->hydrate(false);
                $productNameArr[] =$productName_query->first();
			}
			foreach($productNameArr as $key=>$selectedProducts){
				$productArr[$selectedProducts['id']] = $selectedProducts['product'];
			}
			
			$this->set(compact('productArr'));
		}
		
		$this->set(compact('productInOut','productCode'));
	}
    
    public function deleteProductFromWarehousebasket($productId=''){
        
		//if($this->request->Session()->delete("WarehouseBasket[$productId]")){
            unset($_SESSION['WarehouseBasket'][$productId]);
			$sessionKioskId = '10000';
			$warehouseBasket = $this->request->Session()->read('WarehouseBasket');
			$this->SessionRestore->update_session_backup_table($this->request->params['controller'], 'index', 'WarehouseBasket', $warehouseBasket, $sessionKioskId);
			if(!empty($warehouseBasket)){
				return $this->redirect(array('action'=>'in_out_checkout'));
			}else{
				return $this->redirect(array('action'=>'index'));
			}
		//}else{
          //  die("--");
        //}
	}
    
    public function updateQuantityInWhBasket(){
		$sessionKioskId = '10000';
		$session_basket = $this->request->Session()->read('WarehouseBasket');
		$newArray = array();
		if(array_key_exists('warehouse_stocks',$this->request['data'])){
			$requestedData = $this->request['data']['warehouse_stocks'];
			foreach($requestedData as $product_id=>$requestedQtt){
				$rawArray = $session_basket[$product_id];
				foreach($rawArray as $key=>$value){
					if($key=='quantity'){
						$value = $requestedQtt;
					}
					$newArray[$product_id][$key]=$value;
				}
			}
			
			if(!empty($newArray)){
				$this->request->Session()->delete("WarehouseBasket");
				if($this->request->Session()->write("WarehouseBasket",$newArray)){
					$warehouseBasket = $this->request->Session()->read('WarehouseBasket');
					$this->SessionRestore->update_session_backup_table($this->request->params['controller'], 'index', 'WarehouseBasket', $warehouseBasket, $sessionKioskId);
					$this->Flash->success("Quantity has been successfully updated");
				}
			}
		}
		
		return $this->redirect(array('action'=>'in_out_checkout'));
	}
    
	public function restoreSession($currentController = '', $currentAction = '', $session_key = '', $kiosk_id = '', $redirectAction = ''){
		if(!$redirectAction){
		    $redirectAction = $currentAction;
		}
		$status = $this->SessionRestore->restore_from_session_backup_table($currentController, $currentAction, $session_key, $kiosk_id);
		if($currentAction == 'index' && $status == 'Success'){
		    //writing the reference number and warehouse_vendor_id as well to the session
		    $this->SessionRestore->restore_from_session_backup_table($currentController, $currentAction, 'reference_number', $kiosk_id);
			$this->SessionRestore->restore_from_session_backup_table($currentController, $currentAction, 'warehouse_vendor_id', $kiosk_id);
		}
		if($status == 'Success'){
		    $msg = "Session succesfully retreived!";
		}else{
		    $msg = "Session could not be retreived!";
		}
		$this->Flash->success($msg);
		return $this->redirect(array('action' => $redirectAction));
	}
	
	public function referenceStock(){
		$userName_query = $this->Users->find('list',
												  [
													   'keyField' => 'id',
													   'valueField' => 'username',
												  ]
									   );
		$userName_query = $userName_query->hydrate(false);
		$userName  = $userName_query ->toArray();
		
		$res_query = $this->WarehouseStock->find('all',[
											 'fields' => ['product_id','user_id','reference_number','modified'],
											 'order' => ['modified' => 'desc'],
											 'limit' => ROWS_PER_PAGE,
											 'group' => ['modified'],
										   ]);
		$res_query
					->select(['stock_value' => $res_query->func()->sum('WarehouseStock.price * WarehouseStock.quantity')]);
		//pr($res_query);die;
		
		$this->paginate = [
			'fields' => ['product_id','user_id','reference_number','modified'],
			'order' => ['modified' => 'desc'],
			'limit' => ROWS_PER_PAGE,
			'group' => ['modified'],
				];
		
		//'sum(`WarehouseStock`.`price` * `WarehouseStock`.`quantity`) as stock_value'
		//'group' => array('modified')
		$warehouse_stocks_query = $this->WarehouseStock->find('all');
		$warehouse_stocks_query
							     ->select(['stock_value' => $warehouse_stocks_query->func()->sum('`WarehouseStock`.`price` * `WarehouseStock`.`quantity`')])
								 ->group('modified');
		$warehouse_stocks_query = $warehouse_stocks_query->hydrate(false);
		if(!empty($warehouse_stocks_query)){
		  $warehouse_stocks = $warehouse_stocks_query->toArray();
		}else{
		  $warehouse_stocks = array();
		}
		//pr($warehouse_stocks);die;
		$totalStockValue = 0;
		foreach($warehouse_stocks as $warehouse_stock){
			$totalStockValue = $totalStockValue + $warehouse_stock['stock_value'];
		}
		$hint = $this->ScreenHint->hint('warehouse_stocks','reference_stock');
        if(!$hint){
            $hint = "";
        }
		$referenceStockData_query = $this->paginate($res_query);
		$referenceStockData = $referenceStockData_query->toArray();						   
		$this->set(compact('hint','referenceStockData','userName', 'totalStockValue'));
	}
	
	public function searchStock(){
		$searchKW = $this->request->query['search_kw'];
		if(array_key_exists('start_date',$this->request->query)){
			$this->set('start_date',$this->request->query['start_date']);
		}
		if(array_key_exists('end_date',$this->request->query)){
			$this->set('end_date',$this->request->query['end_date']);
		}
		$conditionArr = array();
		if(array_key_exists('start_date',$this->request->query) &&
		   array_key_exists('end_date',$this->request->query) &&
		   !empty($this->request->query['start_date']) &&
		   !empty($this->request->query['end_date'])){
			$conditionArr[] = array(
						"WarehouseStock.created >" => date('Y-m-d', strtotime($this->request->query['start_date'])),
						"WarehouseStock.created <" => date('Y-m-d', strtotime($this->request->query['end_date']. ' +1 Days')),			
					       );
		}
		if(array_key_exists('search_kw',$this->request->query)){
			if(!empty($searchKW))
				$conditionArr['LOWER(WarehouseStock.reference_number) like '] =  strtolower("%$searchKW%");
			$this->set('search_kw',$this->request->query['search_kw']);
		}
		
		$userName_query = $this->Users->find('list',
												  [
													   'keyField' => 'id',
													   'valueField' => 'username',
												  ]);
		$userName_query = $userName_query->hydrate(false);
		$userName = $userName_query->toArray();
		$res_query = $this->WarehouseStock->find('all',[
											 'conditions' => $conditionArr,
											 'fields' => ['product_id','user_id','reference_number','modified'],
											 'order' => ['modified' => 'desc'],
											 'limit' => ROWS_PER_PAGE,
											 'group' => ['modified'],
										   ]);
		$res_query
					->select(['stock_value' => $res_query->func()->sum('WarehouseStock.price * WarehouseStock.quantity')]);
					
		//$this->Paginator->settings = array(
		//	'fields' => array('product_id','user_id','reference_number','modified', 'sum(`WarehouseStock`.`price` * `WarehouseStock`.`quantity`) as stock_value'),
		//	'order' => array('modified' => 'desc'),
		//	'limit' => ROWS_PER_PAGE,
		//	'recursive' => -1,
		//	'group' => array('modified'),
		//	'conditions' => $conditionArr
		//		);
		
		$warehouse_stocks_query = $this->WarehouseStock->find('all',['conditions' => $conditionArr]);
		$warehouse_stocks_query
							     ->select(['stock_value' => $warehouse_stocks_query->func()->sum('`WarehouseStock`.`price` * `WarehouseStock`.`quantity`')])
								 ->group('modified');
		$warehouse_stocks_query = $warehouse_stocks_query->hydrate(false);
		if(!empty($warehouse_stocks_query)){
		  $warehouse_stocks = $warehouse_stocks_query->toArray();
		}else{
		  $warehouse_stocks = array();
		}
		
		//$warehouse_stocks = $this->WarehouseStock->find('all', array(
		//											'fields' => array('sum(`WarehouseStock`.`price` * `WarehouseStock`.`quantity`) as stock_value'),
		//											'recursive' => -1,
		//											'group' => array('modified'),
		//											'conditions' => $conditionArr
		//										));
		$totalStockValue = 0;
		foreach($warehouse_stocks as $warehouse_stock){
			$totalStockValue = $totalStockValue + $warehouse_stock['stock_value'];
		}
		$referenceStockData_query = $this->paginate($res_query);
		$referenceStockData = $referenceStockData_query->toArray();
		$hint = $this->ScreenHint->hint('warehouse_stocks','reference_stock');
					if(!$hint){
						$hint = "";
					}
		
		
		$this->set(compact('hint','referenceStockData','userName', 'totalStockValue'));
		//$this->layout = 'default';
		$this->render('reference_stock');
	}
	
	public function viewReferenceStock($modifiedDate = ""){
		$userName_query = $this->Users->find('list',
												  [
													   'keyField' => 'id',
													   'valueField' => 'username',
												  ]);
		$userName_query = $userName_query->hydrate(false);
		$userName = $userName_query->toArray();
		
		$vendorName_query = $this->WarehouseVendors->find('list',
															[
																 'keyField' => 'id',
																 'valueField' => 'vendor',
															]);
		
		$vendorName_query = $vendorName_query->hydrate(false);
		$vendorName = $vendorName_query->toArray();
		
		$this->paginate= array(
			'conditions' => array('WarehouseStock.modified'=>date("Y-m-d G:i:s",$modifiedDate))
				);
		$dateWiseStock_query = $this->WarehouseStock->find('all', array(
																	'conditions' => array('WarehouseStock.modified'=>date("Y-m-d G:i:s",$modifiedDate))
																  ));
		//pr($dateWiseStock_query);
		$dateWiseStock_query = $dateWiseStock_query->hydrate(false);
		$dateWiseStock = $dateWiseStock_query->toArray();
		  //pr($dateWiseStock);die;
		$products = $productIDs = array();
		foreach($dateWiseStock as $dateWiseStockInfo){
			$productIDs[] = $dateWiseStockInfo['product_id'];
		}
		
		if(count($productIDs) > 0){
			$products_query = $this->Products->find('all',array(
											  'conditions' => array('Products.id IN' => $productIDs),
											  'fields' => array('product','product_code','id')
											  )
								 );
			$products_query = $products_query->hydrate(false);
			if(!empty($products_query)){
			   $products = $products_query->toArray();
			}else{
			   $products = array();
			}
		}
		$productArr = array();
		foreach($products as $product){
			$productArr[$product['id']] = array(
														   'product_code' => $product['product_code'],
														   'product' => $product['product']
														   );
		}
		$dateAdded = date("jS \of F Y h:i:s A",$modifiedDate);
		$this->set(compact('dateWiseStock','dateAdded','userName','vendorName','productArr'));
	}
	
	private function searchUrl($keyword = "",$displayCondition = ""){
		$displayType = "";
		if(array_key_exists('display_type',$this->request->query)){
			$displayType = $this->request->query['display_type'];
		}
		if(array_key_exists('search_kw',$this->request->query)){
			$search_kw = $this->request->query['search_kw'];
		}
		extract($this->request->query);
		$categories_query = $this->Categories->find('all',array(
								'fields' => array('id', 'category','id_name_path'),
                                                                'conditions' => array('Categories.status' => 1),
								'order' => 'Categories.category asc',
								));
		$categories_query = $categories_query->hydrate(false);
		$categories = $categories_query->toArray();
		$conditionArr = array();
		//----------------------
		if(!empty($search_kw)){
			$conditionArr['OR']['LOWER(product) like '] =  strtolower("%$search_kw%");
			$conditionArr['OR']['LOWER(product_code) like '] =  strtolower("%$search_kw%");
		}
		if(array_key_exists('category',$this->request->query) && !empty($this->request->query['category'][0])){
			$conditionArr['category_id IN'] = $this->request->query['category'];
		}
		if($displayType=="more_than_zero"){
			$conditionArr['NOT']['`Products`.`quantity`'] = 0;
		}
		$selectedCategoryId=array();
		if(array_key_exists('category_id',$conditionArr) && !empty($conditionArr['category_id'][0])){
			$selectedCategoryId=$conditionArr['category_id'];
		}
		$categories = $this->CustomOptions->category_options($categories,true,$selectedCategoryId);
		
		return (array($categories,$conditionArr));
	}
    
    public function stockHistory($product_id = null){//to check the stock in-out history of a product, works from view of products
         $this->paginate = [
                               'conditions'=>  [
                                            'WarehouseStock.product_id'=>$product_id
                                        ],
                        
                        'order' =>  ['WarehouseStock.created desc'],
                        
                    ];
        // pr( $this->paginate);
		//$this->Paginator->settings = array(
		//	'WarehouseStock' => array(
		//		//'limit' => 20,
		//		'conditions' => array('WarehouseStock.product_id'=>$product_id),
		//		'order' => 'WarehouseStock.created desc',
		//		'recursive' =>-1,
		//	)
		//);
         $historyData = $this->paginate($this->WarehouseStock);
		//$historyData = $this->Paginator->paginate('WarehouseStock');
		$productName_query = $this->Products->find('list',[
                                                     'conditions'=>['Products.id'=>$product_id],
                                                     'keyField' => 'id',
                                                     'valueField' => 'product' 
                                                    ]);
        $productName_query = $productName_query->hydrate(false);
        if(!empty($productName_query)){
            $productName = $productName_query->toArray();
        }else{
            $productName = array();
        }
		
		$productcode_query = $this->Products->find('list',[
                                                     'conditions'=>['Products.id'=>$product_id],
                                                     'keyField' => 'id',
                                                     'valueField' => 'product_code' 
                                                    ]);
        $productcode_query = $productcode_query->hydrate(false);
        if(!empty($productcode_query)){
            $productCode = $productcode_query->toArray();
        }else{
            $productCode = array();
        }
		
       // pr($historyData);die;
		$userIdArr = array();
		foreach($historyData as $key=>$historyInfo){
			$userIdArr[$historyInfo->user_id] = $historyInfo->user_id;
		}
		//pr($userIdArr);die;
		if(empty($userIdArr)){
			$userIdArr = array('0'=>null);	
		}
		
		$userName_query = $this->Users->find('list',[
                                               'conditions'=>['Users.id IN'=>$userIdArr],
                                               'keyField' => 'id',
                                                'valueField' => 'username'
                                              ]);
         //pr($userName_query);die;
	    $userName_query = $userName_query->hydrate(false);
        if(!empty($userName_query)){
            $userName = $userName_query->toArray();
        }else{
            $userName = array();
        }
	   //pr($userName);die;
		$warehouseVendors_query = $this->WarehouseVendors->find('list',[
                                                                     'keyField' => 'id',
                                                                    'valueField' => 'vendor'
                                                                  ]) ;
        $warehouseVendors_query = $warehouseVendors_query->hydrate(false);
        if(!empty($productName_query)){
            $warehouseVendors = $warehouseVendors_query->toArray();
        }else{
            $warehouseVendors = array();
        }
		$inOut = array('1'=>'In','0'=>'Out');
		$this->set(compact('historyData','productName','userName','warehouseVendors','inOut','productCode'));		
	}
    
}
?>