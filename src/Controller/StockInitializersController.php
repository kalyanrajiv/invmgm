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

class StockInitializersController extends AppController
{
    public $helpers = [
         'Paginator' => ['templates' => 'paginatortemplates']
         ];
    public function initialize()
    {
        parent::initialize();
        $this->loadComponent('ScreenHint');
        $this->loadComponent('TableDefinition');
        $this->loadComponent('SessionRestore');
        $this->loadModel('Categories');
        $this->loadModel('Products');
        $this->loadModel('UnderstockLevelOrders');
        $this->loadModel('Users');
        $this->loadModel('WarehouseStock');
		$this->loadModel('StockTakingReferences');
		$this->loadModel('StockTakingDetails');
		$this->loadModel('MobileModels');
		$this->loadModel('Brands');
		$this->loadModel('ProductModels');
		
		
        $CURRENCY_TYPE = Configure::read('CURRENCY_TYPE');
		$this->set(compact('CURRENCY_TYPE' ));
    }
    
    public function addToKiosk(){
        $CURRENCY_TYPE = Configure::read('CURRENCY_TYPE');
		if($this->request->is('post')){
			$selectedKioskId = $this->request->data['selectedKiosk'];
			$this->request->Session()->delete('StockInitBasket');
			$this->request->Session()->write('kioskID',$selectedKioskId);
			$this->SessionRestore->delete_from_session_backup_table($this->request->params['controller'], 'add_to_kiosk', 'StockInitBasket', '');
		}
	    $currencySymbol = $this->setting['currency_symbol'];
	    $categories_query = $this->Categories->find('all',array(
							    'fields' => array('id', 'category','id_name_path'),
							   'conditions' => array('Categories.status' => 1),
							    'order' => 'Categories.category asc',
							    'recursive' => -1
							    ));
        $categories_query = $categories_query->hydrate(false);
        if(!empty($categories_query)){
            $categories = $categories_query->toArray();
        }else{
            $categories = array();
        }
        
        
	    $categories = $this->CustomOptions->category_options($categories,true);
	    $kiosks_query = $this->Kiosks->find('list',
                                            [
                                                'keyField' => 'id',
                                                'valueField' => 'name',
                                                'conditions' => ['Kiosks.status' => 1],
                                                'order' => 'Kiosks.name asc',
                                            ]
					 );
        $kiosks_query = $kiosks_query->hydrate(false);
        if(!empty($kiosks_query)){
            $kiosks = $kiosks_query->toArray();
        }else{
            $kiosks = array();
        }
	    $session_basket = $this->request->Session()->read("StockInitBasket");
		
	    if(is_array($session_basket)){
			$basketStr = '';
			$productIdArr = array();
			if(!empty($session_basket)){
				$productIdArr = array_keys($session_basket);
			}
			$productCodes = array();
			if(!empty($productIdArr)){
				$productCodes_query = $this->Products->find('all',array('conditions'=>array('Products.id IN'=>$productIdArr),'fields'=>array('id','product_code','product','selling_price'),'recursive'=>-1));
                $productCodes_query = $productCodes_query->hydrate(false);
                $productCodes = $productCodes_query->toArray();
			}
			$products = array();
			foreach($productCodes as $key=>$productDetail){
				$products[$productDetail['id']] = $productDetail;
			}
			foreach($session_basket as $productId=>$productData){
				$basketStr.="<tr>
							<td>{$productId}</td>
							<td>{$products[$productId]['product_code']}</td>
							<td>".$products[$productId]['product']."</td>
							<td>".$CURRENCY_TYPE.number_format($productData['selling_price'],2)."</td>
							<td>".$productData['quantity']."</td></tr>";
			}
		
			if(!empty($basketStr)){
				$basketStr = "<table><tr>
							<th style='width: 99px;'>Product id</th>
							<th style='width: 125px;'>Product code</th>
							<th>Product name</th>
							<th style='width: 74px;'>SP</th>
							<th style='width: 46px;'>Qty</th>
							</tr>".$basketStr."
						</table>";
						
						
				$productCounts = count($this->request->Session()->read('StockInitBasket'));
		
				if($productCounts){
					//$productCounts product(s) added to the cart.
					$flashMessage = "Total item Count:$productCounts.<br/>$basketStr";
				}else{
					$flashMessage = "No item added to the cart. Item Count:$productCounts";
				}
				$this->Flash->success($flashMessage,['escape' => false]);
			}
	    }
	    
	    $this->paginate= [
                          'limit' => ROWS_PER_PAGE,
                          'order' => ['Products.id desc']
                        ];
		$centralStocks_query = $this->paginate("Products");
        $centralStocks  =$centralStocks_query->toArray();
        
		$product_Codes = array();
		$codeArr = array();
		foreach($centralStocks as $centralStock){
			$product_Codes[] = $centralStock->product_code;
			$codeArr[$centralStock->product_code] = $centralStock->quantity;
		}
		if(count($product_Codes) >= 1){
			if($this->request->Session()->read('kioskID')){
				if($this->request->Session()->read('kioskID') == 10000){
					$productTable = "products";
				}else{
					$productTable = "kiosk_".$this->request->Session()->read('kioskID')."_products";
				}
				
                
                
				$query = "SELECT `product_code`, `quantity` FROM `$productTable` WHERE `product_code` IN('".implode("','",$product_Codes)."')";
                $conn = ConnectionManager::get('default');
                $stmt = $conn->execute($query);
                $product_Codes = $stmt ->fetchAll('assoc');
				
				foreach($product_Codes as $product_code){
                    //pr($product_code);die;
					$codeArr[$product_code['product_code']] = $product_code['quantity'];
				}
			}
		}
		$hint = $this->ScreenHint->hint('stock_initializers','add_to_kiosk');
        if(!$hint){
            $hint = "";
        }
		
		
	    $this->set('centralStocks', $centralStocks);
	    $this->set(compact('hint','categories','kiosks','codeArr'));
	}
    
    
    function search(){
		//pr($this->request);die;
	    extract($this->request->query);
		$search_kw = "";
		if(array_key_exists('search_kw', $this->request->query)){
			$search_kw = $this->request->query['search_kw'];
		}
		
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
		$kiosk_id = $this->request->Session()->read("kioskID");
		//pr($_SESSION);die;
		if(!empty($kiosk_id)){
			if($this->request->Session()->read('kioskID') == 10000){
				$productTable_source = "products";
			}else{
				$productTable_source = "kiosk_".$this->request->Session()->read('kioskID')."_products";
			}
			$productTable = TableRegistry::get($productTable_source,[
																		'table' => $productTable_source,
																	]);
		}else{
			$productTable = TableRegistry::get("products",[
																		'table' => "products",
																	]);
		}
	    //----------------------
		if(array_key_exists('category',$this->request->query)&& !empty($this->request->query['category'][0])){
			$conditionArr['category_id IN'] = $this->request->query['category'];
		}
	//    if(isset($category) && count($category)){
	//		$conditionArr['category_id'] =  $category;
	//    }
		
	    $selectedCategoryId=array();
	    if(array_key_exists('category_id IN',$conditionArr)){
			$selectedCategoryId=$conditionArr['category_id IN'];
	    }
	    //--------code for reading cake query---
		if(!empty($conditionArr)){
			$this->paginate = array(
				'conditions' => $conditionArr,
				'limit' => ROWS_PER_PAGE
			);
		}else{
			$this->paginate = array(
				'limit' => ROWS_PER_PAGE
			);
		}
		
	    //$this->Product->recursive = 0;
	    $categories = $this->CustomOptions->category_options($categories,true,$selectedCategoryId);
	    $kiosks_query = $this->Kiosks->find('list',
                                                [
                                                    'keyField' => 'id',
                                                    'valueField' => 'name',
                                                    'conditions' => ['Kiosks.status' => 1],
                                                    'order' => 'Kiosks.name asc',
                                                ]);
        $kiosks_query = $kiosks_query->hydrate(false);
        if(!empty($kiosks_query)){
            $kiosks = $kiosks_query->toArray();
        }
		//pr($this->paginate);
	    $centralStocks_query = $this->paginate($productTable);
        $centralStocks = $centralStocks_query->toArray();
	    $this->set(compact('centralStocks'));
	    //pr($centralStocks);
	    $product_Codes = array();
	    $codeArr = array();
	    foreach($centralStocks as $centralStock){
		    $product_Codes[] = $centralStock->product_code;
		    $codeArr[$centralStock->product_code] = $centralStock->quantity;
	    }
		$hint = $this->ScreenHint->hint('stock_initializers','add_to_kiosk');
        if(!$hint){
            $hint = "";
        }
	    $this->set(compact('hint','categories','kiosks','search_kw','product_Codes','codeArr'));
	    $this -> render('add_to_kiosk');
	}
    
    public function initializeStock(){
        $currencySymbol = Configure::read('CURRENCY_TYPE');	   
	    //$currencySymbol = $this->setting['currency_symbol'];
	    $kiosks_query = $this->Kiosks->find('list',
                                                [
                                                    'keyField' => 'id',
                                                    'valueField' => 'name',
                                                    'conditions' => ['Kiosks.status' => 1],
                                                    'order' => 'Kiosks.name asc',
                                                ]);
        $kiosks_query = $kiosks_query->hydrate(false);
        $kiosks = $kiosks_query->toArray();
	    if(!empty($this->request->data)){
			$kiosk_id = $this->request['data']['KioskStock']['kiosk_id'];	    
			$current_page = $this->request['data']['current_page'];
	    }else{
			//in case if the request is coming through check out screen
			$kiosk_id = $this->request->Session()->read('kioskID');
			$current_page = "";
	    }
	    
	    $productCounts = 0;
		$appendToURL = "";
		if(isset($kiosk_id) && !empty($kiosk_id)){
			$appendToURL = "?kiosk_id=$kiosk_id";
		}
		
	    if(array_key_exists('basket',$this->request['data'])){
			//Case 1: if we are adding items to basket/cart
			if(empty($kiosk_id)){
			    $flashMessage = "Please choose a kiosk to initialize stock";
			    $this->Flash->error($flashMessage);
			    return $this->redirect(array('action' => "add_to_kiosk/page:$current_page{$appendToURL}"));
			}
			
			$addedProductIdArr = array();
			foreach($this->request['data']['KioskStock']['quantity'] as $key => $quantity){
			    if($quantity > 0){
					$addedProductIdArr[] = $this->request['data']['KioskStock']['product_id'][$key];
			    }
			}
			if(!empty($addedProductIdArr)){
			    $costPriceList_query = $this->Products->find('list',
                                                                [
                                                                    'conditions'=>['Products.id IN'=>$addedProductIdArr],
                                                                    'keyField' =>'id',
                                                                    'valueField' => 'cost_price',
                                                                ]);
                $costPriceList_query = $costPriceList_query->hydrate(false);
                $costPriceList = $costPriceList_query->toArray();
			}
			
			$sellingPriceErr = array();
			$productArr = array();
			foreach($this->request['data']['KioskStock']['quantity'] as $key => $quantity){
				$productId = $this->request['data']['KioskStock']['product_id'][$key];
				$sellingPrice = $this->request['data']['KioskStock']['selling_price'][$key];
				
				if($quantity > 0){
				    if($sellingPrice<$costPriceList[$productId]){
						$sellingPriceErr[] = "Selling price($sellingPrice) cannot be less than the cost price for product with Id: $productId";
				    }else{
					    $productArr[$productId] = array(
									    'quantity'=>$quantity,
									    'selling_price'=>$sellingPrice
									   );
					    $productCounts++;
				    }
				}
			}
			$sellingPriceErrStr = '';
			if(!empty($sellingPriceErr)){
			    $sellingPriceErrStr = implode('<br/>',$sellingPriceErr);
			    $this->Session->error($sellingPriceErrStr,array('escape' => false));
				return $this->redirect(array('action' => "add_to_kiosk/page:$current_page{$appendToURL}"));
			    die;
			}
			$session_basket = $this->request->Session()->read('StockInitBasket');
		
			//pr($productArr);
		
			if(count($session_basket) >= 1){
				//adding item to the the existing session
				$sum_total = $this->add_arrays(array($productArr,$session_basket));
				$this->request->Session()->write('StockInitBasket', $sum_total);
				$session_basket = $this->request->Session()->read('StockInitBasket');
			}else{
				if(count($productCounts)){$this->request->Session()->write('StockInitBasket', $productArr);}
			}
			
			$session_basket = $this->request->Session()->read('StockInitBasket');
			if(is_array($session_basket) && count($session_basket)){
				//storing the session in session_backups table
				$this->SessionRestore->update_session_backup_table($this->request->params['controller'], 'add_to_kiosk', 'StockInitBasket', $session_basket, '');
				$this->SessionRestore->update_session_backup_table($this->request->params['controller'], 'add_to_kiosk', 'kioskID', $kiosk_id, '');
				//die("------$$$$------");
			}
			
			//$this->Session->write('kiosk_id', $kiosk_id);
			$this->request->Session()->write('kioskID', $kiosk_id);
			$totalItems = count($this->request->Session()->read('StockInitBasket'));
			if($productCounts){
				$flashMessage = "$productCounts product(s) added to the Kiosk. Total item Count:$totalItems";
			}else{
				$flashMessage = "No item added to the stock. Item Count:$productCounts";
			}
		
			$this->Flash->success($flashMessage,array('escape' => false));
			return $this->redirect(array('action' => "add_to_kiosk/page:$current_page{$appendToURL}"));
	    }elseif(array_key_exists('empty_basket',$this->request['data'])){
			//Case 2: if we are emptying the cart
			$this->request->Session()->delete('StockInitBasket');
			$this->request->Session()->delete('kioskID'); //rasu
			$this->SessionRestore->delete_from_session_backup_table($this->request->params['controller'], 'add_to_kiosk', 'StockInitBasket', '');
			$flashMessage = "Kiosk is empty; Add products to the Kiosk!";
			$this->Flash->success($flashMessage);
			return $this->redirect(array('action' => "add_to_kiosk/page:$current_page{$appendToURL}"));
	    }elseif(array_key_exists('checkout',$this->request['data'])){
			//Case: checkout
			$session_basket = $this->request->Session()->read('StockInitBasket');
			if(count($session_basket) < 1){
			    $flashMessage = "Please add items to kiosk!";
			    $this->Flash->error($flashMessage);
			     return $this->redirect(array('action' => "add_to_kiosk/page:$current_page{$appendToURL}"));
			}
			
			if(empty($kiosk_id)){
			    $flashMessage = "Please choose a kiosk to initialize stock";
			    $this->Flash->error($flashMessage);
			    return $this->redirect(array('action' => "add_to_kiosk/page:$current_page"));
			}
			
			return $this->redirect(array('action'=>'stock_initializer_checkout'));    
	    }else{
			//case 3: if user is submitting to initializing the stock
			$productArr = array();
			if(empty($kiosk_id)){
				
				$flashMessage = "Failed to transfer stock. <br />Please select kiosk for stock transfer!";
				$this->Flash->error($flashMessage);
				return $this->redirect(array('action' => "add_to_kiosk/page:$current_page"));
				die;
			}
				if($kiosk_id == 10000){
					$productSource = "products";
                     $productTable = TableRegistry::get($productSource,[
                                                                            'table' => $productSource,
                                                                                ]);
				}else{
					$productSource = "kiosk_{$kiosk_id}_products";
                     $productTable = TableRegistry::get($productSource,[
                                                                            'table' => $productSource,
                                                                                ]);
				}
			
		    if(!empty($this->request->data) && array_key_exists('KioskStock',$this->request->data)){
			
			foreach($this->request['data']['KioskStock']['quantity'] as $key => $quantity){
			    if($quantity>0){
				$addedProductIdArr[] = $this->request['data']['KioskStock']['product_id'][$key];
			    }
			}
			if(!empty($addedProductIdArr)){
			    $costPriceList_query = $productTable->find('list',
                                                                [
                                                                    'keyField' => 'id',
                                                                    'valueField' => 'cost_price',
                                                                    'conditions'=>['id IN'=>$addedProductIdArr],
                                                                ]
                                                      );
                $costPriceList_query = $costPriceList_query->hydrate(false);
                $costPriceList = $costPriceList_query->toArray();
			}
			
			$sellingPriceErr = array();
			$productArr = array();
			foreach($this->request['data']['KioskStock']['quantity'] as $key => $quantity){
				$productId = $this->request['data']['KioskStock']['product_id'][$key];
				$sellingPrice = $this->request['data']['KioskStock']['selling_price'][$key];
				
				if($quantity>0){
				    if($sellingPrice<$costPriceList[$productId]){
					$sellingPriceErr[] = "Selling price($sellingPrice) cannot be less than the cost price for product with Id: $productId";
				    }else{
					    $productArr[$productId] = array(
									    'quantity'=>$quantity,
									    'selling_price'=>$sellingPrice
									   );
					    $productCounts++;
				    }
				}
			}
			$sellingPriceErrStr = '';
			if(!empty($sellingPriceErr)){
			    $sellingPriceErrStr = implode('<br/>',$sellingPriceErr);
			    $this->Flash->error($sellingPriceErrStr);
			    return $this->redirect(array('action'=>'add_to_kiosk{$appendToURL'));
			    die;
			}
		
			$session_basket = $this->request->Session()->read('StockInitBasket');
			$sum_total = $this->add_arrays(array($productArr, $session_basket));
			$this->request->Session()->write('StockInitBasket',$sum_total);
			
			if(count($sum_total) == 0){
				$flashMessage = "Failed to initialize Kiosk. <br />Please select quantity atleast for one product!";
				$this->Flash->error($flashMessage);
				return $this->redirect(array('action' => "add_to_kiosk/page:$current_page{$appendToURL}"));
				die;
			}
		    }else{
				//in case if the request is coming through check out screen
				$sum_total = $this->request->Session()->read('StockInitBasket');
		    }
		
			$productFailed = array();
			$productPrice_query = $productTable->find('list',
                                                            [
                                                                'keyField' => 'id',
                                                                'valueField' => 'selling_price',
                                                                'conditions'=>['id IN'=>array_keys($sum_total)],
                                                            ]
                                                  );
            $productPrice_query = $productPrice_query->hydrate(false);
            $productPrice = $productPrice_query->toArray();
			$productCode_query = $productTable->find('list',
                                                            [
                                                                'KeyField' => 'id',
                                                                'valueField' => 'product_code',
                                                                'conditions'=>['id IN'=>array_keys($sum_total)]
                                                            ]);
            $productCode_query = $productCode_query->hydrate(false);
            $productCode = $productCode_query->toArray();
			//$productSource = "kiosk_{$kiosk_id}_products";
			if($kiosk_id == 10000){
				$productSource = "products";
                $productTable = TableRegistry::get($productSource,[
                                                                            'table' => $productSource,
                                                                                ]);
			}else{
				$productSource = "kiosk_{$kiosk_id}_products";
                $productTable = TableRegistry::get($productSource,[
                                                                            'table' => $productSource,
                                                                                ]);
			}
			
			//$this->Product->setSource($productSource);
			$counter = 0;
			
			foreach($sum_total as $productID => $productData){
				//$this->Product->clear;
				$kioskProductData = array(
							'product_code' => $productCode[$productID],
							'quantity' => $productData['quantity'],
							'selling_price'=>$productData['selling_price'],
							);
				
				//$this->Product->id=$productID;
				$checkQuery = "SELECT `id` from `{$productSource}` WHERE `product_code` = '$productCode[$productID]'";
                
                $conn = ConnectionManager::get('default');
                $stmt = $conn->execute($checkQuery);
                $checkIfIdExists = $stmt ->fetchAll('assoc');
				//$checkIfIdExists = $this->Product->query($checkQuery);
				//print_r($checkIfIdExists);
				$productIDExist = false;
				if(array_key_exists(0,$checkIfIdExists)){
					if($kiosk_id == 10000){
						$productIDExist = $checkIfIdExists[0]['id'];
					}else{
						$productIDExist = $checkIfIdExists[0]['id'];
					}
				}
				if((int)$productIDExist){
                      $product_entity = $productTable->get($productIDExist);
                      $product_entity = $productTable->patchEntity($product_entity, $kioskProductData);
					if($productTable->save($product_entity)){
						$counter++;
					}
				}else{
					$flashMessage = "Failed to initialize Kiosk. Product with product code:{$productCode[$productID]} is missing in the selected Kiosk";
					$this->request->Session()->delete("StockInitBasket.$productID");
					unset($_SESSION['StockInitBasket'][$productID]);
					$productFailed[] = $flashMessage;
				}
				
				if(count($productFailed)){
					$this->Flash->error(implode("<br/>",$productFailed),array('escape' => false));
					return $this->redirect(array('action' => "add_to_kiosk/page:$current_page{$appendToURL}"));
				}
				
				if($counter > 0){
				    $session_basket = $this->request->Session()->read('StockInitBasket');
				    if(is_array($session_basket)){
                        $basketStr = '';
                        $productIdArr = array();
                        if(!empty($session_basket)){
                            $productIdArr = array_keys($session_basket);
                        }
                        $productCodes = array();
                        if(!empty($productIdArr)){
                            $productCodes_query = $productTable->find('all',
                                                                 array('conditions'=>array('id IN'=>$productIdArr),
                                                                       'fields'=>array('id','product_code','product','selling_price'),
                                                                       )
                                                                 );
                            $productCodes_query = $productCodes_query->hydrate(false);
                            $productCodes = $productCodes_query->toArray();
                        }
                        $products = array();
                        foreach($productCodes as $key=>$productDetail){
                            $products[$productDetail['id']] = $productDetail;
                        }
					
                        foreach($session_basket as $productId=>$productData){
                            $basketStr.="<tr>
                                            <td>{$productId}</td>
                                            <td>{$products[$productId]['product_code']}</td>
                                            <td>".$products[$productId]['product']."</td>
                                            <td>".$currencySymbol.number_format($productData['selling_price'],2)."</td>
                                            <td>".$productData['quantity']."</td></tr>";
                        }
                        
                        if(!empty($basketStr)){
                            $basketStr = "<table><tr>
                                        <th style='width: 99px;'>Product id</th>
                                        <th style='width: 125px;'>Product code</th>
                                        <th>Product name</th>
                                        <th style='width: 74px;'>SP</th>
                                        <th style='width: 46px;'>Qty</th>
                                        </tr>".$basketStr."
                                    </table>";
                                        
                                        
                            $productCounts = count($this->request->Session()->read('StockInitBasket'));
                        
                            if($productCounts){
                                //$productCounts product(s) added to the cart.
                                $flashMessage = $productCounts." products initialized for {$kiosks[$kiosk_id]}.<br/>$basketStr";
                            }
                            
                        }
                    }
                    
					//$flashMessage = $counter." products initialized for {$kiosks[$kiosk_id]}";
					//$this->Session->setFlash($flashMessage);
				}
			}
            $this->Flash->success($flashMessage,array('escape' => false));
			$this->request->Session()->delete('StockInitBasket');
			$this->request->Session()->delete('kioskID'); //rasu
			$this->SessionRestore->delete_from_session_backup_table($this->request->params['controller'], 'add_to_kiosk', 'StockInitBasket', '');
			//$flashMessage = count($sum_total)." products initialized for {$kiosk_id}";
			//$this->Session->setFlash($flashMessage);
	    }
	    return $this->redirect(array('action' => "add_to_kiosk/page:$current_page{$appendToURL}"));
	}
    
    
    private function add_arrays($arrays = array()){
        $allValues = array();
        $arrays = array_reverse($arrays ,true);
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
    }//end of custom code by rasa
    
    public function stockInitializerCheckout(){
		$productsTable_source = "products";
		$orgproductsTable = TableRegistry::get($productsTable_source,[
																	'table' => $productsTable_source,
																		]);
		$appendToURL = "";
		if($this->request->Session()->read('kioskID')){
			$appendToURL = "?kiosk_id=".$this->request->Session()->read('kioskID');
		}else{
			return $this->redirect(array('action'=>"add_to_kiosk{$appendToURL}"));
		}
	    $kiosks_query = $this->Kiosks->find('list',
                                                    [
                                                        'keyField' => 'id',
                                                        'valueField' => 'name',
                                                        'conditions' => ['Kiosks.status' => 1]
                                                    ]
											);
        $kiosks_query = $kiosks_query->hydrate(false);
        $kiosks = $kiosks_query->toArray();
        
	    $session_basket = $this->request->Session()->read('StockInitBasket');
	    $kioskID = $this->request->Session()->read('kioskID');
	    
	    $productIdArr = array();
	    if(!empty($session_basket)){$productIdArr = array_keys($session_basket);}
	    
	    if($kioskID){
			if($kioskID == 10000){
                $productsTable_source = "products";
                $productsTable = TableRegistry::get($productsTable_source,[
                                                                            'table' => $productsTable_source,
                                                                                ]);
			}else{
                $productsTable_source = "kiosk_{$kioskID}_products";
                $productsTable = TableRegistry::get($productsTable_source,[
                                                                            'table' => $productsTable_source,
                                                                                ]);
			}
		
		$kioskQuantityArray_query = $orgproductsTable->find('list',
                                                            [
                                                                'keyField' => 'id',
                                                                'valueField' => 'quantity',
                                                                'conditions'=>['id IN'=>$productIdArr]
                                                            ]);
        $kioskQuantityArray_query = $kioskQuantityArray_query->hydrate(false);
        $kioskQuantityArray = $kioskQuantityArray_query->toArray();
	    }
		
	    //$this->Product->setSource("products");
	    
	    $productCodes = array();
		//pr($productIdArr);die;
	    if(!empty($productIdArr)){
			$productCodes_query = $orgproductsTable->find('all',array('conditions'=>array('id IN'=>$productIdArr),
                                                             'fields'=>array('id','product_code','product','selling_price'),
                                                             )
                                                 );
            //pr($productCodes_query);die;
			$productCodes_query = $productCodes_query->hydrate(false);
            $productCodes = $productCodes_query->toArray();
	    }
		
	    $products = array();
		//pr($productCodes);die;
	    foreach($productCodes as $key=>$productDetail){
			$products[$productDetail['id']] = $productDetail;
	    }
	    
	    if($this->request->is('post')){
			$error = array();
			if(array_key_exists('update_quantity',$this->request->data)){
				foreach($this->request->data['StockInitializerCheckout'] as $productId => $quantity){
					if($quantity == 0 || !(int)$quantity){
						$error[] = "Please choose quantity more than 0 for product: ".$products[$productId]['product'];
					}
				}
		    
				$errorStr = "";
				if(count($error) > 0){
					$errorStr = implode('<br/>',$error);
					$this->Flash->error($errorStr,array('escape' => false));
					return $this->redirect(array('action'=>'stock_initializer_checkout'));
				}else{
					$requestedQuantity = $this->request->data['StockInitializerCheckout'];
					$newArray = array();
					foreach($session_basket as $productId=>$productData){
						$newArray[$productId] = array(
														'quantity' => $requestedQuantity[$productId],
														'selling_price' => $productData['selling_price']
													);
					}
					$this->request->Session()->delete('StockInitBasket');
					$this->request->Session()->write('StockInitBasket',$newArray);
					$this->SessionRestore->update_session_backup_table($this->request->params['controller'], 'add_to_kiosk', 'StockInitBasket', $newArray, '');
					$this->Flash->Success("Quantity has been successfully updated");
					return $this->redirect(array('action'=>'stock_initializer_checkout'));
				}
			}elseif(array_key_exists('edit_basket',$this->request->data)){
				return $this->redirect(array('action'=>"add_to_kiosk{$appendToURL}"));
			}elseif(array_key_exists('initialize_stock',$this->request->data)){
				return $this->redirect(array('action'=>"initialize_stock"));
			}
	    }
	    $this->set(compact('kiosks','products','kioskQuantityArray'));
	}
    
    public function deleteFromStockIntCheckout($productId = ''){
	    if(array_key_exists($productId,$this->request->Session()->read('StockInitBasket'))){
		//if($this->Session->delete("StockInitBasket.$productId")){
        unset($_SESSION['StockInitBasket'][$productId]);
			$newArray = $this->request->Session()->read('StockInitBasket');
			$this->SessionRestore->update_session_backup_table($this->request->params['controller'], 'add_to_kiosk', 'StockInitBasket', $newArray, '');
		    $this->Flash->success("Product with id:$productId has been successfully deleted");
		    if(count($this->request->Session()->read('StockInitBasket'))==0){
			return $this->redirect(array('action'=>'add_to_kiosk'));
		    }else{
			return $this->redirect(array('action'=>'stock_initializer_checkout'));
		    }
		//}
	    }else{
		$this->Flash->error("Chosen product does not exist in the basket");
		return $this->redirect(array('action'=>'stock_initializer_checkout'));
	    }
	}
	
	public function stockTaking($kioskId=""){
        $CURRENCY_TYPE = Configure::read('CURRENCY_TYPE');	   
	    $currencySymbol = $CURRENCY_TYPE;
	    $categories_query = $this->Categories->find('all',array(
							    'fields' => array('id', 'category','id_name_path'),
							    'conditions' => array('Categories.status' => 1),
							    'order' => 'Categories.category asc',
							    ));
		$categories_query  =$categories_query->hydrate(false);
		$categories = $categories_query->toArray();
	    $categories = $this->CustomOptions->category_options($categories,true);
	    $kiosks_query = $this->Kiosks->find('list',
													[
														'keyField' => 'id',
														'valueField' => 'name',
														'conditions' => ['Kiosks.status' => 1],
														'order' => 'Kiosks.name asc',
													]);
		$kiosks_query = $kiosks_query->hydrate(false);
		$kiosks = $kiosks_query->toArray();
	    if(!$kioskId){
		foreach($kiosks as $kioskId=>$kioskName){break;}
	    }
	    
	    if($this->request->is('post')){
			//pr($this->request);die;
		if(array_key_exists(0,$this->request->params['pass'])){
		    $current_page = $this->request['data']['current_page'];
		    $kioskId = $this->request->params['pass'][0];
			$session_basket = $this->request->Session()->read("stock_taking_basket");
			
			
			$this->request->Session()->delete('stock_taking_basket');
		    $this->request->Session()->delete('stock_taking_kiosk_id');
			
			if($kioskId == 10000){
				//$productSource = "kiosk_{$kioskId}_products";
				$productSource = "products";
				   $productTable = TableRegistry::get($productSource,[
																			'table' => $productSource,
																				]);
			}else{
				$productSource = "kiosk_{$kioskId}_products";
				$productTable = TableRegistry::get($productSource,[
																			'table' => $productSource,
																				]);
			}
			$new_session_basket = $session_basket;
			
			if(!empty($session_basket)){
				foreach($session_basket as $session_poduct_code => $session_product_val){
					
					$productQtyArr = $productTable->find('list',
															[
																'keyField' => 'product_code',
																'valueField' => 'quantity',
																'conditions'=>['product_code'=>$session_poduct_code],
															]
												  )->toArray();
					$new_session_basket[$session_poduct_code]['current_qtt'] = $productQtyArr[$session_poduct_code];
					
				}
				$this->request->Session()->write('stock_taking_basket',$new_session_basket);
				$this->request->Session()->write('stock_taking_kiosk_id',$kioskId);
			}
		    return $this->redirect(array('action' => "stock_taking/$kioskId/page:$current_page"));
		}
	    }
			
	    $session_basket = $this->request->Session()->read("stock_taking_basket");
	    if(is_array($session_basket) && !empty($session_basket)){
			$kioskId = $this->request->Session()->read('stock_taking_kiosk_id');
			if($kioskId == 10000){
				//$productSource = "kiosk_{$kioskId}_products";
				$productSource = "products";
				   $productTable = TableRegistry::get($productSource,[
																			'table' => $productSource,
																				]);
			}else{
				$productSource = "kiosk_{$kioskId}_products";
				$productTable = TableRegistry::get($productSource,[
																			'table' => $productSource,
																				]);
			}
			$val = array_keys($session_basket);
			if(empty($val)){
				$val = array(0 => null);
			}
			$productCodeArr_query = $productTable->find('list',
															[
																'keyField' => 'product_code',
																'valueField' => 'id',
																'conditions'=>['product_code IN'=>$val],
															]
												  );
			$productCodeArr_query = $productCodeArr_query->hydrate(false);
			if(!empty($productCodeArr_query)){
				$productCodeArr = $productCodeArr_query->toArray();
			}else{
				$productCodeArr = array();
			}
			
			
			$costSellingData_query = $productTable->find('all',array('fields'=>array('product_code','cost_price','selling_price','product','Quantity'),'conditions'=>array('product_code IN'=>$val),'recursive'=>-1));
			$costSellingData_query = $costSellingData_query->hydrate(false);
			$costSellingData = $costSellingData_query->toArray();
			//pr($costSellingData);die;
			$costArr = array();
			$sellingArr = array();
			$productNameArr = array();
			if($costSellingData){
				foreach($costSellingData as $key=>$costSellingInfo){
				$costArr[$costSellingInfo['product_code']] = $costSellingInfo['cost_price'];
				$sellingArr[$costSellingInfo['product_code']] = $costSellingInfo['selling_price'];
				$productNameArr[$costSellingInfo['product_code']] = $costSellingInfo['product'];
				$productQuantityArr[$costSellingInfo['product_code']] = $costSellingInfo['Quantity'];
				}
			}
			$basketStr = '';
			foreach($session_basket as $productCode=>$productData){
			$basketStr.="<tr>
							<td>{$productCodeArr[$productCode]}</td>
							<td>{$productCode}</td>
							<td>".$productNameArr[$productCode]."</td>
							<td>".$CURRENCY_TYPE.number_format($sellingArr[$productCode],2)."</td>
							<td>".$productQuantityArr[$productCode]."</td>
							<td>".$productData['quantity']."</td></tr>";
							//<td>".$productData['difference']."</td>
			}
			
			if(!empty($basketStr)){
				$basketStr = "<table><tr>
							<th style='width: 99px;'>Product id</th>
							<th style='width: 125px;'>Product code</th>
							<th>Product name</th>
							<th style='width: 74px;'>SP</th>
							<th style='width: 62px;'>Org</br>Qty</th>
							<th style='width: 46px;'>Qty</th>
							</tr>".$basketStr."
						</table>";
							//<th>Difference</th>
				//echo $basketStr;die;		    
				$productCounts = count($this->request->Session()->read('stock_taking_basket'));
			
				if($productCounts){
					//$productCounts product(s) added to the cart.
					$flashMessage = "Total item Count:$productCounts.<br/>$basketStr";
				}else{
					$flashMessage = "No item added to the cart. Item Count:$productCounts";
				}
				//$this->Flash->success($flashMessage,array('escape' => false));
			}
	    }
	    
	    if($kioskId>0){
			if($kioskId == 10000){
				//$productSource = "kiosk_{$kioskId}_products";
				$productSource = "products";
				 $productTable = TableRegistry::get($productSource,[
																			'table' => $productSource,
																				]);
			}else{
				$productSource = "kiosk_{$kioskId}_products";
				 $productTable = TableRegistry::get($productSource,[
																			'table' => $productSource,
																				]);
			}
	    }
		
	    //in case the option is changed from dropdown, kiosk id will be updated, else will show the details of first kiosk in the list
	    $this->paginate = [
						   'limit' => 100,
						   'order' => ['id desc']
						   ];
		$centralStocks_query = $this->paginate($productTable);
		$centralStocks = $centralStocks_query->toArray();
		$product_Codes = array();
		$codeArr = array();
		foreach($centralStocks as $centralStock){
			$product_Codes[] = $centralStock->product_code;
			$codeArr[$centralStock->product_code] = $centralStock->quantity;
		}
		if(count($product_Codes) >= 1){
			if($this->request->Session()->read('kioskID')){
				$session_kiosk_id = $this->request->Session()->read('kioskID');
				if($session_kiosk_id == 10000){
					$productTable = "products";
				}else{
					$productTable = "kiosk_".$this->request->Session()->read('kioskID')."_products";
				}
				
				$product_Codes_query = "SELECT `product_code`, `quantity` FROM `$productTable` WHERE `product_code` IN('".implode("','",$product_Codes)."')";
				$conn = ConnectionManager::get('default');
				$stmt = $conn->execute($product_Codes_query);
				$product_Codes = $stmt ->fetchAll('assoc');
				
				foreach($product_Codes as $product_code){
					$codeArr[$product_code['product_code']] = $product_code['quantity'];
				}
			}
		}
		
		$hint = $this->ScreenHint->hint('stock_initializers','stock_taking');
        if(!$hint){
            $hint = "";
        }
		
		 
		
		 $brand_query = $this->Brands->find('list',[
                                                            'keyField' => 'id',
                                                            'valueField' => 'brand',
												'order'=>'brand asc',
                                                         ]
                                                  );
		  $brand_query = $brand_query->hydrate(false);
		  if(!empty($brand_query)){
			  $brands = $brand_query->toArray();
		  }else{
			  $brands = array();
		  }
		  $brands = array(-1 => "All") + $brands;
		  if(!empty($brands)){
				$brand_id = array(key($brands));	
		  }else{
			$brand_id = array(0 => null);
		  }
		
		
		$mobileModels_query = $this->ProductModels->find('list',[
															'conditions' => ['brand_id IN' => $brand_id],
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
		  if(empty($mobileModels)){
				$mobileModels[0] = "No Option Available";	
		  }else{
			$mobileModels[0] = "Select Model";
		  }
		
		$mobileModels[0] = "No Option Available";	
	    $this->set('centralStocks', $centralStocks);
	    $this->set(compact('hint','categories','kiosks','codeArr','kioskId','mobileModels','brands'));
	}
	
	function searchStockTaking(){
		
		//echo"hi yamini";die;
		$CURRENCY_TYPE = Configure::read('CURRENCY_TYPE');
		 $currencySymbol = $CURRENCY_TYPE;
		 
	    extract($this->request->query);
		if(array_key_exists('search_kw',$this->request->query)){
			$search_kw = $this->request->query['search_kw'];
		}else{
			$search_kw = "";
		}
	    
	    $categories = $this->Categories->find('all',array(
	    'fields' => array('id', 'category','id_name_path'),
							    'conditions' => array('Categories.status' => 1),
	    'order' => 'Categories.category asc',
	    ));
	    $conditionArr = array();
	    //----------------------
	    if(!empty($search_kw)){
		$conditionArr['OR']['LOWER(product) like '] =  strtolower("%$search_kw%");
		$conditionArr['OR']['LOWER(product_code) like '] =  strtolower("%$search_kw%");
	    }
	    //----------------------
	    if(isset($category) && !empty($category[0]) && count($category)){
		$conditionArr['category_id IN'] =  $category;
	    }
	    $selectedCategoryId=array();
	    if(array_key_exists('category_id IN',$conditionArr)){
		$selectedCategoryId=$conditionArr['category_id IN'];
	    }
		
		if(array_key_exists("brand_id",$this->request->query)){
			$brand_id = $this->request->query['brand_id'];
			$this->set(compact('brand_id'));
			if(!empty($brand_id) && $brand_id != -1){
				$conditionArr['brand_id'] = $brand_id;
				$mobileModels = $mobileModels_query = $this->ProductModels->find('list',array(
																				'keyField' =>'id',
																				'valueField' => 'model',
																				'order'=>'model asc',
																				'conditions'=>array(
																				 'brand_id'=>$brand_id,
																				 
																				 )
								   )
						      )->toArray();
				
				if(empty($mobileModels)){
					$mobileModels[0] = "No Option Available";
				}else{
					$mobileModels = array(-1 => "All") + $mobileModels;
				}
				$this->set(compact('mobileModels'));
			}else{
				$mobileModels = array(0 => "No Option Available");
				$this->set(compact('mobileModels'));
			}
			
		}
		
		if(array_key_exists("model_id",$this->request->query)){
			$model_id = $this->request->query['model_id'];
			$this->set(compact('model_id'));
			if(!empty($model_id) && $model_id != -1){
				$conditionArr['model_id'] = $model_id;
			}
		}
		
		
	    //--------code for reading cake query---
	    $this->paginate = [
							'limit' => 100,
							'conditions' => $conditionArr
		];
		
	    $kioskId = $this->request->Session()->read('stock_taking_kiosk_id');
	     $kiosks_query = $this->Kiosks->find('list',
												[
													'keyField' => 'id',
													'valueField' => 'name',
													'conditions' => ['Kiosks.status' => 1],
													'order' => 'Kiosks.name asc',
												]
				);
		 $kiosks_query = $kiosks_query->hydrate(false);
		 $kiosks = $kiosks_query->toArray();
	    if(!$kioskId){
			if(array_key_exists(0,$this->request->params['pass'])){
				$kioskId = $this->request->params['pass'][0];
			}else{
				foreach($kiosks as $kioskId=>$kioskName){break;}
			}
	    }
		if($kioskId == 10000){
			//$this->Product->setSource("kiosk_{$kioskId}_products");
            $productsTable = TableRegistry::get("products",[
																	'table' => "products",
																		]);
		}else{
			$productsTable = TableRegistry::get("kiosk_{$kioskId}_products",[
																	'table' => "kiosk_{$kioskId}_products",
																		]);
		}
	    
	    $categories = $this->CustomOptions->category_options($categories,true,$selectedCategoryId);
		//pr($this->paginate);die;
	    $centralStocks_query = $this->paginate($productsTable);
		$centralStocks = $centralStocks_query->toArray();
	    $product_Codes = array();
	    $codeArr = array();
	    foreach($centralStocks as $centralStock){
		    $product_Codes[] = $centralStock->product_code;
		    $codeArr[$centralStock->product_code] = $centralStock->quantity;
	    }
		$hint = $this->ScreenHint->hint('stock_initializers','stock_taking');
					if(!$hint){
						$hint = "";
					}		
		
		$session_basket = $this->request->Session()->read("stock_taking_basket");
	    if(is_array($session_basket)){
			$kioskId = $this->request->Session()->read('stock_taking_kiosk_id');
			if($kioskId == 10000){
				//$productSource = "kiosk_{$kioskId}_products";
				$productSource = "products";
				 $productsTable = TableRegistry::get($productSource,[
																	'table' => $productSource,
																		]);
			}else{
				$productSource = "kiosk_{$kioskId}_products";
				 $productsTable = TableRegistry::get($productSource,[
																	'table' => $productSource,
																		]);
			}
			//pr($session_basket);die;
			$product_code_arr = array_keys($session_basket);
			if(empty($product_code_arr)){
				$product_code_arr = array(0 => null);
			}
			$productCodeArr_query = $productsTable->find('list',
															[
																'keyField' => 'product_code',
																'valueField' => 'id',
																'conditions'=>['product_code IN'=>$product_code_arr],
															]
												   );
			
			$productCodeArr_query  = $productCodeArr_query->hydrate(false);
			$productCodeArr = $productCodeArr_query->toArray();
			
			$costSellingData_query = $productsTable->find('all',array('fields'=>array('product_code','cost_price','selling_price','product','Quantity'),'conditions'=>array('product_code IN'=>$product_code_arr),'recursive'=>-1));
			$costSellingData_query = $costSellingData_query->hydrate(false);
			$costSellingData = $costSellingData_query->toArray();
			//pr($costSellingData);die;
			$costArr = array();
			$sellingArr = array();
			$productNameArr = array();
			if($costSellingData){
				foreach($costSellingData as $key=>$costSellingInfo){
				$costArr[$costSellingInfo['product_code']] = $costSellingInfo['cost_price'];
				$sellingArr[$costSellingInfo['product_code']] = $costSellingInfo['selling_price'];
				$productNameArr[$costSellingInfo['product_code']] = $costSellingInfo['product'];
				$productQuantityArr[$costSellingInfo['product_code']] = $costSellingInfo['Quantity'];
				}
			}
			$basketStr = '';
			foreach($session_basket as $productCode=>$productData){
			$basketStr.="<tr>
							<td>{$productCodeArr[$productCode]}</td>
							<td>{$productCode}</td>
							<td>".$productNameArr[$productCode]."</td>
							<td>".$currencySymbol.number_format($sellingArr[$productCode],2)."</td>
							<td>".$productQuantityArr[$productCode]."</td>
							<td>".$productData['quantity']."</td></tr>";
							//<td>".$productData['difference']."</td>
			}
			
			if(!empty($basketStr)){
				$basketStr = "<table><tr>
							<th style='width: 99px;'>Product id</th>
							<th style='width: 125px;'>Product code</th>
							<th>Product name</th>
							<th style='width: 74px;'>SP</th>
							<th style='width: 62px;'>Org</br>Qty</th>
							<th style='width: 46px;'>Qty</th>
							</tr>".$basketStr."
						</table>";
							//<th>Difference</th>
				//echo $basketStr;die;		    
				$productCounts = count($this->request->Session()->read('stock_taking_basket'));
			
				if($productCounts){
					//$productCounts product(s) added to the cart.
					$flashMessage = "Total item Count:$productCounts.<br/>$basketStr";
				}else{
					$flashMessage = "No item added to the cart. Item Count:$productCounts";
				}
				//$this->Flash->success($flashMessage,array('escape' => false));
			}
	    }
		
		 $brand_query = $this->Brands->find('list',[
                                                            'keyField' => 'id',
                                                            'valueField' => 'brand',
												'order'=>'brand asc',
                                                         ]
                                                  );
		  $brand_query = $brand_query->hydrate(false);
		  if(!empty($brand_query)){
			  $brands = $brand_query->toArray();
		  }else{
			  $brands = array();
		  }
		  $brands = array(-1 => "All") + $brands;
		  if(!empty($brands)){
				$brand_id = array(key($brands));	
		  }else{
			$brand_id = array(0 => null);
		  }
		
		
		
		
		
	    $this->set(compact('hint','categories','kiosks','search_kw','kioskId','centralStocks','codeArr','product_Codes','brands','mobileModels'));
	    $this -> render("stock_taking");
	}
	
	public function recordStock (){
		$CURRENCY_TYPE = Configure::read('CURRENCY_TYPE');
	    $currencySymbol = $CURRENCY_TYPE;
	    $kiosks_query = $this->Kiosks->find('list',
												[
													'keyField' => 'id',
													'valueField' => 'name',
													'conditions' => array('Kiosks.status' => 1),
													'order' => 'Kiosks.name asc',
												]
					 );
		$kiosks_query = $kiosks_query->hydrate(false);
		$kiosks = $kiosks_query->toArray();
	    if(!empty($this->request->data)){
			$kiosk_id = $this->request['data']['KioskStock']['selected_kiosk'];	    
			$current_page = $this->request['data']['current_page'];
	    }else{
			//in case if the request is coming through check out screen
			$kiosk_id = $this->request->Session()->read('stock_taking_kiosk_id');
			$current_page = "";
	    }
		
		$kskId = $this->request->Session()->read('kiosk_id');
		if(empty($kskId)){
			$kskId = 10000;
		}
	    
	    $productCounts = 0;
		
	    if(array_key_exists('basket',$this->request['data'])){
			//Case 1: if we are adding items to basket/cart
			$reference = $this->request->data['stock_taking_reference'];
			if(empty($kiosk_id)){
			    $flashMessage = "Please choose a kiosk to record the stock";
			    $this->Flash->error($flashMessage);
			    return $this->redirect(array('action' => "stock_taking/page:$current_page"));
			}
			if(empty($reference)){
			    $flashMessage = "Please input a reference to record the stock";
			    $this->Flash->error($flashMessage);
			    return $this->redirect(array('action' => "stock_taking/$kiosk_id/page:$current_page"));
			}
			
			$addedProductIdArr = array();
			$productArr = array();
			foreach($this->request['data']['KioskStock']['quantity'] as $key => $quantity){
			    if($quantity>=0 && $quantity !=''){
					$productCode = $this->request['data']['KioskStock']['product_code'][$key];
					$current_qtt = $this->request['data']['KioskStock']['p_quantity'][$key];
					
					$productArr[$productCode] = array(
									'quantity'=>$quantity,
									'current_qtt'=>$current_qtt
									   );
					$productCounts++;
			    }
			}
			
			$this->request->Session()->write('stock_taking_reference',$reference);
			$session_basket = $this->request->Session()->read('stock_taking_basket');
		
			if(count($session_basket) >= 1){
				//adding item to the the existing session
				$sum_total = $this->add_arrays(array($productArr,$session_basket));
				$this->request->Session()->write('stock_taking_basket', $sum_total);
				$session_basket = $this->request->Session()->read('stock_taking_basket');
			}else{
				if(count($productCounts)){$this->request->Session()->write('stock_taking_basket', $productArr);}
			}
		$session_basket = $this->request->Session()->read('stock_taking_basket');
			$this->request->Session()->write('stock_taking_kiosk_id', $kiosk_id);
			$totalItems = count($this->request->Session()->read('stock_taking_basket'));
			if($productCounts){
				if($totalItems > 0){
					$this->SessionRestore->update_session_backup_table($this->request->params['controller'], 'stock_taking', 'stock_taking_kiosk_id', $kiosk_id, $kskId);
					$this->SessionRestore->update_session_backup_table($this->request->params['controller'], 'stock_taking', 'stock_taking_reference', $reference, $kskId);
					$this->SessionRestore->update_session_backup_table($this->request->params['controller'], 'stock_taking', 'stock_taking_basket', $session_basket, $kskId);
				}
				$flashMessage = "$productCounts product(s) added to the Kiosk. Total item Count:$totalItems";
			}else{
				$flashMessage = "No item added to the stock. Item Count:$productCounts";
			}
			$this->Flash->error($flashMessage);
			return $this->redirect(array('action' => "stock_taking/$kiosk_id/page:$current_page"));
	    }elseif(array_key_exists('empty_basket',$this->request['data'])){
			//Case 2: if we are emptying the cart
			$this->request->Session()->delete('stock_taking_basket');
			$this->request->Session()->delete('stock_taking_kiosk_id'); //rasu
			$this->request->Session()->delete('stock_taking_reference');
			$this->SessionRestore->delete_from_session_backup_table($this->request->params['controller'], 'stock_taking', 'stock_taking_kiosk_id', $kskId);
			$this->SessionRestore->delete_from_session_backup_table($this->request->params['controller'], 'stock_taking', 'stock_taking_reference', $kskId);
			$this->SessionRestore->delete_from_session_backup_table($this->request->params['controller'], 'stock_taking', 'stock_taking_basket', $kskId);
			$flashMessage = "Kiosk is empty; Add products to the Kiosk!";
			$this->Flash->error($flashMessage);
			return $this->redirect(array('action' => "stock_taking/$kiosk_id/page:$current_page"));
	    }elseif(array_key_exists('checkout',$this->request['data'])){
			//Case: checkout
			$session_basket = $this->request->Session()->read('stock_taking_basket');
			if(count($session_basket) == 0){
			    $flashMessage = "Please add items to kiosk!";
			    $this->Flash->error($flashMessage);
			     return $this->redirect(array('action' => "stock_taking/$kiosk_id/page:$current_page"));
			}
			
			return $this->redirect(array('action'=>'stock_taking_checkout'));    
	    }else{
			//die('--');
			//case 3: if user is submitting to initializing the stock
		    $productArr = array();
		    if(empty($kiosk_id)){
			    $flashMessage = "Failed to transfer stock. <br />Please select kiosk for recording stock!";
			    $this->Flash->error($flashMessage,array('escape' => false));
			    $this->redirect(array('action' => "stock_taking/$kiosk_id/page:$current_page"));
			    die;
		    }
		    if(!empty($this->request->data) && array_key_exists('KioskStock',$this->request->data)){
				$reference = $this->request->data['stock_taking_reference'];
				if(empty($reference)){
					$flashMessage = "Please enter a reference to record stock";
					$this->Flash->error($flashMessage);
					return $this->redirect(array('action' => "stock_taking/$kiosk_id/page:$current_page"));
					die;
				}
				
				$process_form = 0;// now we are only processing session.. from submitted products are excluded
				if($process_form == 1){
					$productArr = array();
					foreach($this->request['data']['KioskStock']['quantity'] as $key => $quantity){
						if($quantity>=0 && $quantity !=''){
							$productCode = $this->request['data']['KioskStock']['product_code'][$key];
							$current_qtt = $this->request['data']['KioskStock']['p_quantity'][$key];
							
							
							$productArr[$productCode] = array(
											'quantity'=>$quantity,
											'current_qtt' => $current_qtt
											//'difference'=>$difference
											   );
							$productCounts++;
						}
					}
					
					$session_basket = $this->request->Session()->read('stock_taking_basket');
					$sum_total = $this->add_arrays(array($productArr, $session_basket));
					$this->request->Session()->write('stock_taking_basket',$sum_total);
				}
				$sum_total = $session_basket = $this->request->Session()->read('stock_taking_basket');
				
				
				if(count($sum_total) == 0){
					$flashMessage = "Failed to record stock. <br />Please select quantity atleast for one product!";
					$this->Flash->error($flashMessage,array('escape' => false));
					return $this->redirect(array('action' => "stock_taking/$kiosk_id/page:$current_page"));
					die;
				}
				
				$this->SessionRestore->update_session_backup_table($this->request->params['controller'], 'stock_taking', 'stock_taking_kiosk_id', $kiosk_id, $kskId);
				$this->SessionRestore->update_session_backup_table($this->request->params['controller'], 'stock_taking', 'stock_taking_reference', $reference, $kskId);
				$this->SessionRestore->update_session_backup_table($this->request->params['controller'], 'stock_taking', 'stock_taking_basket', $session_basket, $kskId);
		    }else{
				//in case if the request is coming through check out screen
				$sum_total = $this->request->Session()->read('stock_taking_basket');
				$reference = $this->request->Session()->read('stock_taking_reference');
		    }
		    	
			//creating stock reference id by saving data to stock_taking_references table
			$StockTakingReferenceData = array(
							'user_id'=>$this->request->Session()->read('Auth.User.id'),
							'kiosk_id'=>$kiosk_id,
							'reference'=>$reference
							  );
			$StockTakingReference_entity = $this->StockTakingReferences->newEntity();
			$StockTakingReference_entity = $this->StockTakingReferences->patchEntity($StockTakingReference_entity, $StockTakingReferenceData,['validate' => false]);
			if($this->StockTakingReferences->save($StockTakingReference_entity)){
			    $stockReferenceId = $StockTakingReference_entity->id;
			}
		
			$productFailed = array();
			if($kiosk_id == 10000){
				$productSource = "products";
				$productTable = TableRegistry::get($productSource,[
																		'table' => $productSource,
																			]);
			}else{
				$productSource = "kiosk_{$kiosk_id}_products";
				$productTable = TableRegistry::get($productSource,[
																		'table' => $productSource,
																			]);
			}
			
			//$this->Product->setSource($productSource);
			$productCodeArr_query = $productTable->find('list',
															[
																'keyField' => 'product_code',
																'valueField' => 'id',
																'conditions'=>['product_code IN'=>array_keys($sum_total)],
															]);
			
			$productCodeArr_query = $productCodeArr_query->hydrate(false);
			$productCodeArr = $productCodeArr_query->toArray();
			
			$costSellingData_query = $productTable->find('all',array('fields'=>array('product_code','cost_price','selling_price','product'),'conditions'=>array('product_code IN'=>array_keys($sum_total)),'recursive'=>-1));
			
			$costSellingData_query = $costSellingData_query->hydrate(false);
			$costSellingData = $costSellingData_query->toArray();
			
			$costArr = array();
			$sellingArr = array();
			$productNameArr = array();
			if($costSellingData){
			    foreach($costSellingData as $key=>$costSellingInfo){
				$costArr[$costSellingInfo['product_code']] = $costSellingInfo['cost_price'];
				$sellingArr[$costSellingInfo['product_code']] = $costSellingInfo['selling_price'];
				$productNameArr[$costSellingInfo['product_code']] = $costSellingInfo['product'];
			    }
			}
			
			
			$counter = 0;
			if($stockReferenceId){
			    foreach($sum_total as $productCode => $productData){
				$diffQtt = $productData['quantity']-$productData['current_qtt'];
				if($diffQtt<0){
				    $diff = -1;
				}else{
				    $diff = 1;
				}
				
				if($diffQtt!=0){
				    $stockTakingDetailData = array(
							    'user_id' => $this->request->Session()->read('Auth.User.id'),
							    'kiosk_id' => $kiosk_id,
							    'stock_taking_reference_id'=> $stockReferenceId,
							    'product_id' => $productCodeArr[$productCode],
							    'product_code' => $productCode,
							    'cost_price' => $costArr[$productCode],
							    'selling_price' => $sellingArr[$productCode],
							    'quantity' => $diffQtt,//difference
							    'difference' => $diff//-1 or 1
							    );
				    
				    //$qtty = $productData['quantity'];
				    $StockTakingDetail_entity = $this->StockTakingDetails->newEntity();
					$StockTakingDetail_entity = $this->StockTakingDetails->patchEntity($StockTakingDetail_entity, $stockTakingDetailData,['validate' => false]);
				    if($this->StockTakingDetails->save($StockTakingDetail_entity)){
						$query = "UPDATE $productSource SET quantity = quantity+$diffQtt WHERE product_code='$productCode'";
						 $conn = ConnectionManager::get('default');
						$stmt = $conn->execute($query);
					//if($this->Product->updateAll(array('quantity' => "quantity + $diffQtt"), array('Product.product_code' => $productCode))){
					    $counter++;
					//}
				    }
				}
				
			    }    
			    
			    if($counter > 0){
				$session_basket = $this->request->Session()->read('stock_taking_basket');
				
				$basketStr = '';
				if(is_array($session_basket)){
				    foreach($session_basket as $productCode=>$productData){
				    $basketStr.="<tr>
							    <td>{$productCodeArr[$productCode]}</td>
							    <td>{$productCode}</td>
							    <td>".$productNameArr[$productCode]."</td>
							    <td>".$currencySymbol.number_format($sellingArr[$productCode],2)."</td>
							    <td>".$productData['quantity']."</td></tr>";
				    }
				    //<td>".$productData['difference']."</td>
				    
				    if(!empty($basketStr)){
					    $basketStr = "<table><tr>
								    <th style='width: 99px;'>Product id</th>
								    <th style='width: 125px;'>Product code</th>
								    <th>Product name</th>
								    <th style='width: 74px;'>SP</th>
								    <th style='width: 46px;'>Qty</th>
								    </tr>".$basketStr."
							    </table>";
								    //<th>Difference</th>
					    //echo $basketStr;die;		    
					    $productCounts = count($this->request->Session()->read('stock_taking_basket'));
				    
					    if($productCounts){
						    //$productCounts product(s) added to the cart.
						    $flashMessage = $productCounts." products processed for stock taking ({$kiosks[$kiosk_id]}).<br/>$basketStr";
					    }
					    $this->Flash->success($flashMessage,array('escape' => false));
				    }
				}
				    //$flashMessage = $counter." products initialized for {$kiosks[$kiosk_id]}";
				    //$this->Session->setFlash($flashMessage);
			    }else{
				$flashMessage = "No data was saved, since there is no difference in the chosen products";
				$this->Flash->error($flashMessage);
				return $this->redirect(array('action' => "stock_taking/$kiosk_id/page:$current_page"));
				die;
			    }
			    
			}else{
			    $flashMessage = "Failed to save stock reference number to database";
			    $this->Flash->error($flashMessage);
			    return $this->redirect(array('action' => "stock_taking/$kiosk_id/page:$current_page"));
			    die;
			}
			
			$this->request->Session()->delete('stock_taking_basket');
			$this->request->Session()->delete('stock_taking_reference');
			$this->request->Session()->delete('stock_taking_kiosk_id');
			$this->SessionRestore->delete_from_session_backup_table($this->request->params['controller'], 'stock_taking', 'stock_taking_kiosk_id', $kskId);
			$this->SessionRestore->delete_from_session_backup_table($this->request->params['controller'], 'stock_taking', 'stock_taking_reference', $kskId);
			$this->SessionRestore->delete_from_session_backup_table($this->request->params['controller'], 'stock_taking', 'stock_taking_basket', $kskId);
			//$flashMessage = count($sum_total)." products initialized for {$kiosk_id}";
			//$this->Session->setFlash($flashMessage);
	    }
	    return $this->redirect(array('action' => "stock_taking/$kiosk_id/page:$current_page"));
	}
	
	public function stockTakingCheckout(){
		$kskId = $this->request->Session()->read('kiosk_id');
		if(empty($kskId)){
			$kskId = 10000;
		}
	    $session_basket = $this->request->Session()->read('stock_taking_basket');
	    $kiosk_id = $this->request->Session()->read('stock_taking_kiosk_id');
		if($kiosk_id == 10000){
			$productSource = "products";
            $productsTable = TableRegistry::get($productSource,[
																'table' => $productSource,
																	]);
		}else{
			$productSource = "kiosk_{$kiosk_id}_products";
			$productsTable = TableRegistry::get($productSource,[
																'table' => $productSource,
																	]);
		}
		
	    $productCodeArr_query = $productsTable->find('all',array('fields'=>array('product_code','id'),'conditions'=>array('product_code IN'=>array_keys($session_basket)),'recursive'=>-1));
		$productCodeArr_query  = $productCodeArr_query->hydrate(false);
		$productCodeArr = $productCodeArr_query->toArray();
		
	    $costSellingData_query = $productsTable->find('all',array('fields'=>array('id','product_code','cost_price','selling_price','product','quantity'),'conditions'=>array('product_code IN'=>array_keys($session_basket)),'recursive'=>-1));
		$costSellingData_query = $costSellingData_query->hydrate(false);
		$costSellingData = $costSellingData_query->toArray();
		
	    $costArr = array();
	    $sellingArr = array();
	    $productNameArr = array();
	    if($costSellingData){
			foreach($costSellingData as $key=>$costSellingInfo){
				$ProductIDs[$costSellingInfo['product_code']] = $costSellingInfo['id'];
				$costArr[$costSellingInfo['product_code']] = $costSellingInfo['cost_price'];
				$sellingArr[$costSellingInfo['product_code']] = $costSellingInfo['selling_price'];
				$productNameArr[$costSellingInfo['product_code']] = $costSellingInfo['product'];
				$productCodes[$costSellingInfo['product_code']] = $costSellingInfo['quantity'];
				 
			}
	    }	
		
	    $kiosks_query = $this->Kiosks->find('list',
											[
												'keyField' => 'id',
												'valueField' => 'name',
												'conditions' => ['Kiosks.status' => 1]
											]);
	    $kiosks_query =$kiosks_query->hydrate(false);
		$kiosks = $kiosks_query->toArray();
	    if($this->request->is('post')){
			$error = array();
			if(array_key_exists('update_quantity',$this->request->data)){
				$lessProducts = array();
				$lowProducts = array();
				
				foreach($this->request->data['StockTakingCheckout'] as $productCode => $quantity){
					  $availableQty = $productCodes[$productCode];
					  if($quantity < 0 || $quantity == ''){ //|| !(int)$quantity
								$lowProducts[] = $productCode;
						}
						if($quantity > $availableQty){
							$lessProducts[] = $productCode;
						}	
				}
				if(count($lessProducts) >= 1){
				}
				if(count($lowProducts) > 0){
						 $this->Flash->error("Please choose  more than 0 for product : ".implode(",",$lowProducts) );
						return $this->redirect(array('action'=>'stock_taking_checkout'));
				}else{
					$requestedQuantity = $this->request->data['StockTakingCheckout'];
					$newArray = array();
					foreach($session_basket as $productCode=>$productData){
						$newArray[$productCode] = array(
									    'quantity' => $requestedQuantity[$productCode],
									    'current_qtt' => $productData['current_qtt']
													);
					}
					$this->request->Session()->delete('stock_taking_basket');
					$this->request->Session()->write('stock_taking_basket',$newArray);
					$this->SessionRestore->update_session_backup_table($this->request->params['controller'], 'stock_taking', 'stock_taking_basket', $newArray, $kskId);
					$this->Flash->success("Quantity has been successfully updated");
					return $this->redirect(array('action'=>'stock_taking_checkout'));
				}
			}elseif(array_key_exists('edit_basket',$this->request->data)){
				return $this->redirect(array('action'=>"stock_taking/$kiosk_id"));
			}elseif(array_key_exists('initialize_stock',$this->request->data)){
				return $this->redirect(array('action'=>"record_stock"));
			}
	    }
	    $this->set(compact('kiosks','products','productCodeArr','costArr','sellingArr','productNameArr','productCodes','ProductIDs'));
	}
	
	public function deleteFromStockTknCheckout($productCode = ''){
		$kskId = $this->request->Session()->read('kiosk_id');
		if(empty($kskId)){
			$kskId = 10000;
		}
	    $session_basket = $this->request->Session()->read('stock_taking_basket');
	    $kiosk_id = $this->request->Session()->read('stock_taking_kiosk_id');
	    if(array_key_exists($productCode,$session_basket)){
		unset($session_basket["$productCode"]);
		if(true){ //$this->request->Session()->delete('stock_taking_basket')
		    $this->request->Session()->write('stock_taking_basket',$session_basket);
			$session_basket = $this->request->Session()->read('stock_taking_basket');
			$this->SessionRestore->update_session_backup_table($this->request->params['controller'], 'stock_taking', 'stock_taking_basket', $session_basket, $kskId);
		    $this->Flash->success("Product with code:$productCode has been successfully deleted");
		    if(count($this->request->Session()->read('stock_taking_basket'))==0){
			return $this->redirect(array('action'=>"stock_taking/$kiosk_id"));
		    }else{
			return $this->redirect(array('action'=>'stock_taking_checkout'));
		    }
		}else{
			if(count($this->request->Session()->read('stock_taking_basket'))==0){
			return $this->redirect(array('action'=>"stock_taking/$kiosk_id"));
		    }else{
			return $this->redirect(array('action'=>'stock_taking_checkout'));
		    }
		}
	    }else{
		$this->Flash->error("Chosen product does not exist in the basket");
		return $this->redirect(array('action'=>'stock_taking_checkout'));
	    }
	}
	
	public function stockTakingReferenceList(){
	    $kiosks_query = $this->Kiosks->find('list',
											[
												'keyField' => 'id',
												'valueField' => 'name',
												'conditions' => array('Kiosks.status' => 1),
                                                'order' => 'Kiosks.name asc',
											]);
		$kiosks_query = $kiosks_query->hydrate(false);
		$kiosks = $kiosks_query->toArray();
		
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
		  if($ext_site == 1){
			   $managerKiosk = $this->get_kiosk();
			   if(!empty($managerKiosk)){
				$this->paginate = [
					'conditions' => ['kiosk_id IN' => $managerKiosk],
				    'group' => ['kiosk_id','reference'],
					'limit' => 100,
					'order' => ['created desc'],
																			
						];
			   }else{
					$this->paginate = [
				    'group' => ['kiosk_id','reference'],
					'limit' => 100,
					'order' => ['created desc'],
																			
						];
			   }
		  }else{
			$this->paginate = [
				    'group' => ['kiosk_id','reference'],
					'limit' => 100,
					'order' => ['created desc'],
																			
						];	
		  }
		
		//pr($stockTakingRefData_query);die;
	    
	    
	    $stockTakingRefData_query = $this->paginate('StockTakingReferences');
		$stockTakingRefData = $stockTakingRefData_query->toArray();
		$stock_taking_refData_query = $this->StockTakingReferences->find('all',[
																			//'group' => ['StockTakingReferences.kiosk_id','StockTakingReferences.reference'=],
																			//'limit' => 100,
																			'order' => ['created desc'],
																			]
																	);
		$stock_taking_refData_query = $stock_taking_refData_query->hydrate(false);
		if(!empty($stock_taking_refData_query)){
			$stock_taking_refData = $stock_taking_refData_query->toArray();
		}else{
			$stock_taking_refData = array();
		}
		//pr($stockTakingRefData);die;
		$referenceIdList = array();
		foreach($stock_taking_refData as $key => $stockTakingData){
			$referenceIdList[] = $stockTakingData['id'];
			$references[$stockTakingData['reference']][] = $stockTakingData['id'];
		}
		//pr($reference);die;
		if(empty($referenceIdList)){
			$referenceIdList = array(0=>null);
		}
		$stock_res_query = $this->StockTakingDetails->find('all',
					array(
					    'conditions' =>array('stock_taking_reference_id IN'=>$referenceIdList),
						'order' => 'StockTakingDetails.created desc',
						//'limit' => 100,
					)
				);
		$stock_res_query = $stock_res_query->hydrate(false);
		if(!empty($stock_res_query)){
			$stock_res = $stock_res_query->toArray();
		}else{
			$stock_res = array();
		}
		//pr($stock_res);die;
		$kiosk_ref_data = $gainLoss = $gainLossArr = array();
		foreach($stock_res as $s_key => $s_value){
			$total_loss = $total_gain = 0;
			$selling_price = $s_value['selling_price'];
			$quantity = $s_value['quantity'];
			$diff = $s_value['difference'];
			$kiosk_ref_data[$s_value['stock_taking_reference_id']] = $s_value['kiosk_id'];
			if($diff == -1){
				$total_loss = (-1)*$selling_price*$quantity;
			}else{
				$total_gain = $selling_price*$quantity;
			}
			
			
			if(array_key_exists($s_value['stock_taking_reference_id'],$gainLossArr)){
				$gainLossArr[$s_value['stock_taking_reference_id']]['total_gain'] += $total_gain;
				$gainLossArr[$s_value['stock_taking_reference_id']]['total_loss'] += $total_loss;	
			}else{
				$gainLossArr[$s_value['stock_taking_reference_id']]['total_gain'] = $total_gain;
				$gainLossArr[$s_value['stock_taking_reference_id']]['total_loss'] = $total_loss;	
			}
			
		}
		//pr($kiosk_ref_data);die;
		if(isset($references)){
            foreach($references as $reference => $ref_ids){
                foreach($ref_ids as $key => $ref_id){
                    if(array_key_exists($ref_id,$gainLossArr)){
                        //pr($gainLossArr[$ref_id]);die;
                        if(array_key_exists($reference,$gainLoss)){
                            $kiosk_id_to_add = $kiosk_ref_data[$ref_id];
                            if(array_key_exists($kiosk_id_to_add,$gainLoss[$reference])){
                                $gainLoss[$reference][$kiosk_id_to_add]['total_gain'] += $gainLossArr[$ref_id]['total_gain'];
                                $gainLoss[$reference][$kiosk_id_to_add]['total_loss'] += $gainLossArr[$ref_id]['total_loss'];	
                            }else{
                                $gainLoss[$reference][$kiosk_id_to_add]['total_gain'] = $gainLossArr[$ref_id]['total_gain'];
                                $gainLoss[$reference][$kiosk_id_to_add]['total_loss'] = $gainLossArr[$ref_id]['total_loss'];
                            }
                            
                        }else{
                            $kiosk_id_to_add = $kiosk_ref_data[$ref_id];
                            //echo $gainLossArr[$ref_id]['total_gain'];die;
                            $gainLoss[$reference][$kiosk_id_to_add]['total_gain'] = $gainLossArr[$ref_id]['total_gain'];
                            $gainLoss[$reference][$kiosk_id_to_add]['total_loss'] = $gainLossArr[$ref_id]['total_loss'];
                        }
                    }
                }
                //pr($gainLoss);
            }
        }
		//pr($gainLoss);die;
	    $userIds = array();
	    $kioskIds = array();
	    foreach($stockTakingRefData as $key=>$stockTakingRef){
		$userIds[$stockTakingRef->user_id] = $stockTakingRef->user_id;
		$kioskIds[$stockTakingRef->kiosk_id] = $stockTakingRef->kiosk_id;
	    }
	    
	    $userName = array();
	    if(!empty($userIds)){
		$userName_query = $this->Users->find('list',
												[
													'keyField' => 'id',
													'valueField' => 'username',
													'conditions'=>['Users.id IN'=>$userIds]
												]);
		$userName_query = $userName_query->hydrate(false);
		$userName = $userName_query->toArray();
	    }
	    
	    $kioskName = array();
	    if(!empty($kioskIds)){
			$kioskName_query = $this->Kiosks->find('list',
													[
														'keyField' => 'id',
														'valueField' => 'name',
														'conditions'=>['Kiosks.id IN'=>$kioskIds],
														'order' => ['Kiosks.name asc'],
													]
											 );
			$kioskName_query = $kioskName_query->hydrate(false);
			$kioskName = $kioskName_query->toArray();
	    }
	    $hint = $this->ScreenHint->hint('stock_initializers','stock_taking_reference_list');
        if(!$hint){
            $hint = "";
        }
		$merged = 1;
	    $this->set(compact('hint','stockTakingRefData','userName','kioskName','kiosks','gainLossArr','gainLoss','merged'));
	}
	
	public function viewStockTakingDetails($kioskId = '', $reference = ''){
	    $referenceIdList_query = $this->StockTakingReferences->find('list',
																		[
																			'keyField' => 'id',
																			'valueField' => 'id',
																			'conditions'=>['StockTakingReferences.reference'=>$reference]
																		]
															  );
		$referenceIdList_query = $referenceIdList_query->hydrate(false);
		$referenceIdList = $referenceIdList_query->toArray();
		
	    $kioskList_query = $this->Kiosks->find('list',
												[
													'keyField' => 'id',
													'valueField' => 'name',
													'conditions'=>['Kiosks.id'=>$kioskId],
												]);
		$kioskList_query = $kioskList_query->hydrate(false);
		$kioskList = $kioskList_query->toArray();
		
	    $kioskName = $kioskList[$kioskId];
		
	    $lessQttData_query = $this->StockTakingDetails->find('all',array(
																   'conditions'=>array('StockTakingDetails.stock_taking_reference_id IN'=>$referenceIdList)));
		$lessQttData_query
							->select(['lessQuantityData' => $lessQttData_query->func()->sum('StockTakingDetails.quantity * StockTakingDetails.selling_price')])
							->first();
		$lessQttData_query = $lessQttData_query->hydrate(false);
		$lessQttData = $lessQttData_query->toArray();
	    
	    $moreQttData_query = $this->StockTakingDetails->find('all',array('conditions'=>array('StockTakingDetails.stock_taking_reference_id in'=>$referenceIdList)));
	    
		$moreQttData_query
							->select(['moreQuantityData' => $moreQttData_query->func()->sum('StockTakingDetails.quantity*StockTakingDetails.selling_price')])
							->first();
		$moreQttData_query = $moreQttData_query->hydrate(false);
		$moreQttData = $moreQttData_query->toArray();
	    $this->paginate = [
					    'conditions' =>['kiosk_id'=>$kioskId,'stock_taking_reference_id IN'=>$referenceIdList],
						'order' => ['StockTakingDetails.created desc'],
						'limit' => 100,
				];
		$stock_res_query = $this->StockTakingDetails->find('all',
					array(
					    'conditions' =>array('kiosk_id'=>$kioskId,'stock_taking_reference_id IN'=>$referenceIdList),
						'order' => 'StockTakingDetails.created desc',
						//'limit' => 100,
					)
				);
		$stock_res_query = $stock_res_query->hydrate(false);
		$stock_res = $stock_res_query->toArray();
	    $total_loss = $total_gain = 0;
		foreach($stock_res as $s_key => $s_value){
			$selling_price = $s_value['selling_price'];
			$quantity = $s_value['quantity'];
			$diff = $s_value['difference'];
			if($diff == -1){
				$total_loss += (-1)*$selling_price*$quantity;
			}else{
				$total_gain += $selling_price*$quantity;
			}
			
		}
		
		$this->set(compact('total_loss','total_gain'));
	    $stockTakingDetail_query= $this->paginate('StockTakingDetails');
	    $stockTakingDetail = $stockTakingDetail_query->toArray();
		
		
	    $userIds = array();
	    $productIds = array();
	    foreach($stockTakingDetail as $key=>$stockTaking){
		$userIds[] = $stockTaking->user_id;
		$productIds[] = $stockTaking->product_id;
	    }
	    
	    $userName = array();
	    if(!empty($userIds)){
		$userName_query = $this->Users->find('list',
												[
													'keyField' => 'id',
													'valueField' => 'username',
													'conditions'=>['Users.id IN'=>$userIds]
												]);
		$userName_query = $userName_query->hydrate(false);
		$userName = $userName_query->toArray();
	    }
	    
	    $productName = array();
	    if(!empty($productIds)){
		$productName_query = $this->Products->find('list',
													[
														'keyField' => 'id',
														'valueField' => 'product',
														'conditions'=>['Products.id IN'=>$productIds]
													]);
		$productName_query = $productName_query->hydrate(false);
		$productName = $productName_query->toArray();
	    }
	    
	    $this->set(compact('stockTakingDetail','reference','kioskName','lessQttData','moreQttData','productName','userName'));
	}
    
	public function searchStockTknRef(){
	    $kiosks_query = $this->Kiosks->find('list',
												[
													'keyField' => 'id',
													'valueField' => 'name',
													'conditions' => ['Kiosks.status' => 1],
													'order' => 'Kiosks.name asc',
												]);
	    $kiosks_query  = $kiosks_query->hydrate(false);
        if(!empty($kiosks_query)){
            $kiosks = $kiosks_query->toArray();
        }else{
            $kiosks = array();
        }
        //pr($this->request->query);die;
	    $key = $this->request->query['key'];
	    $kioskId = $this->request->query['kiosk'];
		$product_keyword = $this->request->query['product'];
		$merged = $this->request->query['merge'];
		$this->set(compact("merged"));
		$products_query = $this->Products->find('all',[
													   'conditions'=>['OR'=>[
																	  'product like'=>"%$product_keyword%",
																	  'product_code like'=>"%$product_keyword%",
																	  'description like'=>"%$product_keyword%",
																	  ]
																	  ]
													   ]);
		//pr($products_query);die;
		$products_query = $products_query->hydrate(false);
		if(!empty($products_query)){
			$products = $products_query->toArray();
		}else{
			$products = array();
		}
		
		$stock_taking_ids = $productIds = array();
		//pr($products);die;
		foreach($products as $keys => $product){
			$productIds[] = $product['id'];
		}
		//pr($productIds);die;
		
		if(empty($productIds)){
			$productIds = array(0=>null);
		}
		$stock_taking_details_data_query = $this->StockTakingDetails->find('all',[
																				  'conditions'=>['product_id IN'=>$productIds]
																				  ]);
		$stock_taking_details_data_query = $stock_taking_details_data_query->hydrate(false);
		if(!empty($stock_taking_details_data_query)){
			$stock_taking_details_data = $stock_taking_details_data_query->toArray();
		}else{
			$stock_taking_details_data = array();
		}
		//pr($stock_taking_details_data);die;
		foreach($stock_taking_details_data as $keys => $stock_taking_data){
			$stock_taking_ids[] = $stock_taking_data['stock_taking_reference_id'];
		}
		//pr($stock_taking_ids);die;
		if(empty($stock_taking_ids)){
			$stock_taking_ids = array(0=>null);
		}
        //pr($kioskId);die;
		
	    if($kioskId == -1){
			if(!empty($key) && empty($product_keyword)){
				if($merged == 1){
					$this->paginate = [
						'conditions' => [
							    'reference like' => "%$key%",
							    ],
						'group' => ['kiosk_id','reference'],
						'order' => ['created desc'],
						'limit' => 100
					      ];	
				}else{
					$this->paginate = [
						'conditions' => [
							    'reference like' => "%$key%",
							    ],
						//'group' => ['kiosk_id','reference'],
						'order' => ['created desc'],
						'limit' => 100
					      ];
				}
				
			}elseif(empty($key) && !empty($product_keyword)){
				if($merged == 1){
					$this->paginate = [
						'conditions' => [
							    'id IN'=>$stock_taking_ids,
							    ],
						'group' => ['kiosk_id','reference'],
						'order' => ['created desc'],
						'limit' => 100
					      ];	
				}else{
					$this->paginate = [
						'conditions' => [
							    'id IN'=>$stock_taking_ids,
							    ],
						//'group' => ['kiosk_id','reference'],
						'order' => ['created desc'],
						'limit' => 100
					      ];
				}
				
			}elseif(!empty($key) && !empty($product_keyword)){
				if($merged == 1){
					$this->paginate = [
						'conditions' => [
							    'id IN'=>$stock_taking_ids,
								'reference like' => "%$key%",
							    ],
						'group' => ['kiosk_id','reference'],
						'order' => ['created desc'],
						'limit' => 100
					      ];	
				}else{
					$this->paginate = [
						'conditions' => [
							    'id IN'=>$stock_taking_ids,
								'reference like' => "%$key%",
							    ],
						//'group' => ['kiosk_id','reference'],
						'order' => ['created desc'],
						'limit' => 100
					      ];
				}
				
			}else{
				if($merged == 1){
					$this->paginate = [
						'group' => ['kiosk_id','reference'],
						'order' => ['created desc'],
						'limit' => 100
							];	
				}else{
					$this->paginate = [
						//'group' => ['kiosk_id','reference'],
						'order' => ['created desc'],
						'limit' => 100
							];
				}
				
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
			   if(!empty($managerKiosk)){
				if(array_key_exists("conditions",$this->paginate)){
					$this->paginate['conditions'][] = ['kiosk_id IN' => $managerKiosk];	
				}else{
					$this->paginate['conditions'][] = ['kiosk_id IN' => $managerKiosk];
				}
			   }
			}
			//pr($this->paginate);die;
		}else{
			if($merged == 1){
				$this->paginate = [
						'conditions' => [
							    'reference like' => "%$key%",
							    'kiosk_id' => $kioskId,
								'id IN'=>$stock_taking_ids,
							    ],
						'group' => ['kiosk_id','reference'],
						'order' => ['created desc'],
						'limit' => 100
							];	
			}else{
				$this->paginate = [
						'conditions' => [
							    'reference like' => "%$key%",
							    'kiosk_id' => $kioskId,
								'id IN'=>$stock_taking_ids,
							    ],
						//'group' => ['kiosk_id','reference'],
						'order' => ['created desc'],
						'limit' => 100
							];
			}
			
		}
	    //pr($this->paginate);die;
	    $stockTakingRefData_query = $this->paginate('StockTakingReferences');
        //debug($stockTakingRefData_query);
		$stock_taking_refData_query = $this->StockTakingReferences->find('all',[
																			//'group' => ['StockTakingReferences.kiosk_id','StockTakingReferences.reference'=],
																			//'limit' => 100,
																			'order' => ['created desc'],
																			]
																	);
		$stock_taking_refData_query = $stock_taking_refData_query->hydrate(false);
		if(!empty($stock_taking_refData_query)){
			$stock_taking_refData = $stock_taking_refData_query->toArray();
		}else{
			$stock_taking_refData = array();
		}
		//pr($stockTakingRefData);die;
		$referenceIdList = array();
		foreach($stock_taking_refData as $key => $stockTakingData){
			$referenceIdList[] = $stockTakingData['id'];
			$references[$stockTakingData['reference']][] = $stockTakingData['id'];
		}
		//pr($reference);die;
		if(empty($referenceIdList)){
			$referenceIdList = array(0=>null);
		}
		$stock_res_query = $this->StockTakingDetails->find('all',
					array(
					    'conditions' =>array('stock_taking_reference_id IN'=>$referenceIdList),
						'order' => 'StockTakingDetails.created desc',
						//'limit' => 100,
					)
				);
		$stock_res_query = $stock_res_query->hydrate(false);
		if(!empty($stock_res_query)){
			$stock_res = $stock_res_query->toArray();
		}else{
			$stock_res = array();
		}
		//pr($stock_res);die;
		$gainLoss = $gainLossArr = array();
		foreach($stock_res as $s_key => $s_value){
			$total_loss = $total_gain = 0;
			$selling_price = $s_value['selling_price'];
			$quantity = $s_value['quantity'];
			$diff = $s_value['difference'];
			$kiosk_ref_data[$s_value['stock_taking_reference_id']] = $s_value['kiosk_id'];
			if($diff == -1){
				$total_loss = (-1)*$selling_price*$quantity;
			}else{
				$total_gain = $selling_price*$quantity;
			}
			
			
			if(array_key_exists($s_value['stock_taking_reference_id'],$gainLossArr)){
				$gainLossArr[$s_value['stock_taking_reference_id']]['total_gain'] += $total_gain;
				$gainLossArr[$s_value['stock_taking_reference_id']]['total_loss'] += $total_loss;	
			}else{
				$gainLossArr[$s_value['stock_taking_reference_id']]['total_gain'] = $total_gain;
				$gainLossArr[$s_value['stock_taking_reference_id']]['total_loss'] = $total_loss;	
			}
			
		}
		//pr($gainLossArr);die;
		//pr($references);die;
		foreach($references as $reference => $ref_ids){
			foreach($ref_ids as $key => $ref_id){
				if(array_key_exists($ref_id,$gainLossArr)){
					//pr($gainLossArr[$ref_id]);die;
					if(array_key_exists($reference,$gainLoss)){
						$kiosk_id_to_add = $kiosk_ref_data[$ref_id];
						//if($kiosk_id_to_add == 22 && $reference == "Harrogate jan"){
							//pr($gainLossArr[$ref_id]);
						//}
						if(array_key_exists($kiosk_id_to_add,$gainLoss[$reference])){
							$gainLoss[$reference][$kiosk_id_to_add]['total_gain'] += $gainLossArr[$ref_id]['total_gain'];
							$gainLoss[$reference][$kiosk_id_to_add]['total_loss'] += $gainLossArr[$ref_id]['total_loss'];	
						}else{
							$gainLoss[$reference][$kiosk_id_to_add]['total_gain'] = $gainLossArr[$ref_id]['total_gain'];
							$gainLoss[$reference][$kiosk_id_to_add]['total_loss'] = $gainLossArr[$ref_id]['total_loss'];
						}
						
					}else{
						$kiosk_id_to_add = $kiosk_ref_data[$ref_id];
						//echo $gainLossArr[$ref_id]['total_gain'];die;
						$gainLoss[$reference][$kiosk_id_to_add]['total_gain'] = $gainLossArr[$ref_id]['total_gain'];
						$gainLoss[$reference][$kiosk_id_to_add]['total_loss'] = $gainLossArr[$ref_id]['total_loss'];
					}
				}
			}
			
			//pr($gainLoss);
		}
		//die;
        if(!empty($stockTakingRefData_query)){
            $stockTakingRefData = $stockTakingRefData_query->toArray();
        }else{
            $stockTakingRefData = array();
        }
        //pr($stockTakingRefData);die;
	    $userIds = array();
	    $kioskIds = array();
	    foreach($stockTakingRefData as $key=>$stockTakingRef){
		$userIds[$stockTakingRef->user_id] = $stockTakingRef->user_id;
		$kioskIds[$stockTakingRef->kiosk_id] = $stockTakingRef->kiosk_id;
	    }
	    
	    $userName = array();
	    if(!empty($userIds)){
            $userName_query = $this->Users->find('list',
                                                    [
                                                        'keyField' => 'id',
                                                        'valueField' => 'username',
                                                        'conditions'=>['Users.id IN'=>$userIds],
                                                    ]
                                          );
            $userName_query = $userName_query->hydrate(false);
            if(!empty($userName_query)){
                $userName = $userName_query->toArray();
            }else{
                $userName = array();
            }
	    }
	    
	    $kioskName = array();
	    if(!empty($kioskIds)){
            $kioskName_query = $this->Kiosks->find('list',
                                                        [
                                                            'keyField' => 'id',
                                                            'valueField' => 'name',
                                                            'conditions'=>array('Kiosks.id IN'=>$kioskIds)
                                                        ]
                                            );
            $kioskName_query = $kioskName_query->hydrate(false);
            if(!empty($kioskName_query)){
                $kioskName = $kioskName_query->toArray();
            }else{
                $kioskName = array();
            }
	    }
	    $hint = $this->ScreenHint->hint('stock_initializers','stock_taking_reference_list');
					if(!$hint){
						$hint = "";
					}		
		
	    $this->set(compact('hint','stockTakingRefData','userName','kioskName','kiosks','gainLoss'));
	    $this->render('stock_taking_reference_list');
	}
	
	public function restoreSession($currentController = '', $currentAction = '', $session_key = '', $kioskId = '', $redirectAction = ''){
		if(!$redirectAction){
		    $redirectAction = $currentAction;
		}
		if($currentAction == 'search_stock_taking'){
			$currentAction = 'stock_taking';
		}
		$status = $this->SessionRestore->restore_from_session_backup_table($currentController, $currentAction, $session_key, $kioskId);
		if($currentAction == 'add_to_kiosk' && $status == 'Success'){
		    //writing the reference number and warehouse_vendor_id as well to the session
		    $this->SessionRestore->restore_from_session_backup_table($currentController, $currentAction, 'kioskID', 10000);
		}
		
		if($currentAction == 'stock_taking' && $status == 'Success'){
		    //writing the reference number and warehouse_vendor_id as well to the session
		    $this->SessionRestore->restore_from_session_backup_table($currentController, $currentAction, 'stock_taking_kiosk_id', 10000);
			$this->SessionRestore->restore_from_session_backup_table($currentController, $currentAction, 'stock_taking_reference', 10000);
		}
	
		if($status == 'Success'){
		    $msg = "Session succesfully retreived!";
		}else{
		    $msg = "Session could not be retreived!";
		}
		$this->Flash->success($msg);
		return $this->redirect(array('action' => $redirectAction));
	}
    
    public function syncAllKiosks(){
        $kiosks_query = $this->Kiosks->find('list',[
                                                'keyField' => 'id',
                                                'valueField' => 'name',
                                                'conditions' => ['Kiosks.status' => 1],
                                                'order' => ['Kiosks.name asc'],
                                                //'recursive' => -1
                                             ]
                                    );
        $kiosks_query = $kiosks_query->hydrate(false);
        if(!empty($kiosks_query)){
            $kiosks = $kiosks_query->toArray();
        }else{
            $kiosks = array();
        }
        $this->set(compact('kiosks'));
    }
    
    public function syncProducts($kiosk_id = null){
        ini_set('max_execution_time', 0);
        $kiosks_query = $this->Kiosks->find('all',[
                                                'keyField' => 'id',
                                                'valueField' => 'name',
                                                'conditions' => ['Kiosks.status' => 1],
                                                'order' => ['Kiosks.name asc'],
                                                //'recursive' => -1
                                            ]
                                    );
        $kiosks_query = $kiosks_query->hydrate(false);
        if(!empty($kiosks_query)){
            $kiosks = $kiosks_query->toArray();
        }else{
            $kiosks = array();
        }
        foreach($kiosks as $kiosk){
            $kiosk_id = $kiosk['id'];
            $this->process_sync_by_code($kiosk_id); //updated
        }
        $this->Flash->success(__('All Kiosk(s) products synchronized successfully!'));
        return $this->redirect(array('controller' => 'Products' , 'action' => 'index'));
    }
    
    private function process_sync_by_code($kiosk_id = 0){
		$productTable = "kiosk_{$kiosk_id}_products";
        $connection = ConnectionManager::get('default');
        //pr($connection);die;
        //$connection = $connection->hydrate(false);
        //$connection1 = $connection->toArray();
        //pr($connection1);die;
        $connection_config = ConnectionManager::config('default');
        $host = $connection_config['host'];
        $username = $connection_config['username'];
        $password = $connection_config['password'];
        $database = $connection_config['database'];
        //pr($connection_config);die;
        $con=mysqli_connect($host,$username,$password,$database);
		$tableQuery = $this->TableDefinition->get_table_defination('product_table',$kiosk_id);
		//$this->Product->query($tableQuery);
        $conn = ConnectionManager::get('default');
        $stmt = $conn->execute($tableQuery); 
        //$currentTimeInfo = $stmt ->fetchAll('assoc');
		
		$products_query = $this->Products->find('all');
        $products_query = $products_query->hydrate(false);
        if(!empty($products_query)){
            $products = $products_query->toArray();
        }else{
            $products = array();
        }
        //pr($products);die;
		$skipFields = array('id','quantity','cost_price','selling_price','stock_level','dead_stock_level','created','modified','user_id');
		foreach($products as $sngProduct){
			$setArr = array();
			foreach($sngProduct as $key => $value){
                //pr($key);
                if($key == 'location' || $key == 'vat_excluded_wholesale_price'|| $key == 'vat_exclude_retail_price'|| $key == 'retail_discount' || $key == 'rt_discount_status' || $key == 'special_offer' || $key == 'festival_offer' || $key == 'retail_special_offer'){
                    continue;
                }
        
				if(!in_array($key,$skipFields)){
					$setArr[] = "`$key` = '".mysqli_escape_string($con,$value)."'";
					//Take care of ' and " while adding rows to database using mysql_escape_string
				}
			}
            //pr($setArr);die;
			$productCode = $sngProduct['product_code'];//added
			$productID = $sngProduct['id'];
			$query = "SELECT `id` FROM `$productTable` WHERE `product_code` = '$productCode'"; //added
			//$productData = $this->Product->query($query);
            $productData_conn = ConnectionManager::get('default');
            $productData_stmt = $productData_conn->execute($query); 
            $productData = $productData_stmt ->fetchAll('assoc');
			$successfullyAdded = 0;
			$successfullyUpdated = 0;
            //pr($sngProduct);die;
			if(count($productData)){
				//update product
                
				$setStr = implode(",",$setArr);
				$updateQuery = "UPDATE `$productTable` SET
									$setStr WHERE `product_code` = '$productCode'
								"; //echo "\n".$updateQuery."\n";
				//$this->Product->query($updateQuery);
                $updateQuery_conn = ConnectionManager::get('default');
                $updateQuery_stmt = $updateQuery_conn->execute($updateQuery);
				$successfullyUpdated++;
			}else{
				//$setArr[] =  "`id` = '$productID'";
				$query = "SELECT `id` FROM `$productTable` WHERE `id` = '$productID'";
				//$idData = $this->Product->query($query);
                $idData_conn = ConnectionManager::get('default');
                $idData_stmt = $idData_conn->execute($query); 
                $idData = $idData_stmt ->fetchAll('assoc');
				if(count()){
				    ;
				}else{
				    $setArr[] =  "`id` = '$productID'";
				}
				$setStr = implode(",",$setArr);
				//add product
				$insertQuery = "INSERT INTO `$productTable` SET
									$setStr
								";  //echo "\n".$insertQuery."\n";
				//$this->Product->query($insertQuery);
                $insertQuery_conn = ConnectionManager::get('default');
                $insertQuery_stmt = $insertQuery_conn->execute($insertQuery);
				$successfullyAdded++;
			}                
		}
		if($successfullyAdded || $successfullyUpdated){
			return true;
		}else{
			return false;
		}
	}
    
    public function syncSingleKiosk(){
        $kiosks_query = $this->Kiosks->find('list',[
                                                'keyField' => 'id',
                                                'valueField' => 'name',
                                                'conditions' => ['Kiosks.status' => 1],
                                                'order' => ['Kiosks.name asc'],
                                                //'recursive' => -1
                                             ]
                                    );
        $kiosks_query = $kiosks_query->hydrate(false);
        if(!empty($kiosks_query)){
            $kiosks = $kiosks_query->toArray();
        }else{
            $kiosks = array();
        }
        $this->set(compact('kiosks'));
    }
	
    public function syncSingleKioskProducts($kiosk_id = null){
        ini_set('max_execution_time', 0);
        if(empty($kiosk_id)){
            $kiosk_id = $this->request['data']['kiosk_id'];
        }
        $this->process_sync_by_code($kiosk_id);
        $this->Flash->success(__('All Kiosk products synchronized successfully!'));
        return $this->redirect(array('action' => 'sync_single_kiosk'));
    }
	
	public function updateSessionAjax(){
		$CURRENCY_TYPE = Configure::read('CURRENCY_TYPE');
		$product_code = $this->request->query['prod_code'];
		$selected_qty = $this->request->query['qty'];
		$org_qty = $this->request->query['org_qty'];
		$reference = $this->request->query['reference'];
		$kiosk_id = $this->request->query['kiosk_id'];
		$productArr[$product_code] = array(
									'quantity'=>$selected_qty,
									'current_qtt'=>$org_qty
									   );
		
		$this->request->Session()->write('stock_taking_reference',$reference);
		$session_basket = $this->request->Session()->read('stock_taking_basket');
	
		if(count($session_basket) >= 1){
			//adding item to the the existing session
			$sum_total = $this->add_arrays(array($productArr,$session_basket));
			$this->request->Session()->write('stock_taking_basket', $sum_total);
			$session_basket = $this->request->Session()->read('stock_taking_basket');
		}else{
			$this->request->Session()->write('stock_taking_basket', $productArr);
		}
		$session_basket = $this->request->Session()->read('stock_taking_basket');
		$this->request->Session()->write('stock_taking_kiosk_id', $kiosk_id);
		
		
		$session_basket = $this->request->Session()->read("stock_taking_basket");
	    if(is_array($session_basket) && !empty($session_basket)){
			$kioskId = $this->request->Session()->read('stock_taking_kiosk_id');
			if($kioskId == 10000){
				//$productSource = "kiosk_{$kioskId}_products";
				$productSource = "products";
				   $productTable = TableRegistry::get($productSource,[
																			'table' => $productSource,
																				]);
			}else{
				$productSource = "kiosk_{$kioskId}_products";
				$productTable = TableRegistry::get($productSource,[
																			'table' => $productSource,
																				]);
			}
			$val = array_keys($session_basket);
			if(empty($val)){
				$val = array(0 => null);
			}
			$productCodeArr_query = $productTable->find('list',
															[
																'keyField' => 'product_code',
																'valueField' => 'id',
																'conditions'=>['product_code IN'=>$val],
															]
												  );
			$productCodeArr_query = $productCodeArr_query->hydrate(false);
			if(!empty($productCodeArr_query)){
				$productCodeArr = $productCodeArr_query->toArray();
			}else{
				$productCodeArr = array();
			}
			
			
			$costSellingData_query = $productTable->find('all',array('fields'=>array('product_code','cost_price','selling_price','product','Quantity'),'conditions'=>array('product_code IN'=>$val),'recursive'=>-1));
			$costSellingData_query = $costSellingData_query->hydrate(false);
			$costSellingData = $costSellingData_query->toArray();
			//pr($costSellingData);die;
			$costArr = array();
			$sellingArr = array();
			$productNameArr = array();
			if($costSellingData){
				foreach($costSellingData as $key=>$costSellingInfo){
				$costArr[$costSellingInfo['product_code']] = $costSellingInfo['cost_price'];
				$sellingArr[$costSellingInfo['product_code']] = $costSellingInfo['selling_price'];
				$productNameArr[$costSellingInfo['product_code']] = $costSellingInfo['product'];
				$productQuantityArr[$costSellingInfo['product_code']] = $costSellingInfo['Quantity'];
				}
			}
			$basketStr = '';
			foreach($session_basket as $productCode=>$productData){
			$basketStr.="<tr>
							<td>{$productCodeArr[$productCode]}</td>
							<td>{$productCode}</td>
							<td>".$productNameArr[$productCode]."</td>
							<td>".$CURRENCY_TYPE.number_format($sellingArr[$productCode],2)."</td>
							<td>".$productQuantityArr[$productCode]."</td>
							<td>".$productData['quantity']."</td></tr>";
							//<td>".$productData['difference']."</td>
			}
			
			if(!empty($basketStr)){
				$basketStr = "<table><tr><th style='width: 99px;'>Product id</th><th style='width: 125px;'>Product code</th><th>Product name</th><th style='width: 74px;'>SP</th><th style='width: 62px;'>Org</br>Qty</th><th style='width: 46px;'>Qty</th></tr>".$basketStr."</table>";
							//<th>Difference</th>
				//echo $basketStr;die;
				$basketStr = trim(str_replace(array("\n", "\r", "\t"), '', $basketStr));
				$kskId = $this->request->Session()->read('kiosk_id');
				$this->SessionRestore->update_session_backup_table($this->request->params['controller'], 'stock_taking', 'stock_taking_kiosk_id', $kiosk_id, $kskId);
					$this->SessionRestore->update_session_backup_table($this->request->params['controller'], 'stock_taking', 'stock_taking_reference', $reference, $kskId);
					$this->SessionRestore->update_session_backup_table($this->request->params['controller'], 'stock_taking', 'stock_taking_basket', $session_basket, $kskId);
				
				echo json_encode(array("basket" => $basketStr));
			}else{
				echo json_encode(array('basket' => 'No Items in the basket'));
			}
	    }else{
			echo json_encode(array('basket' => 'No Items in the basket'));
		}
		die;
	}
	
	
	public function unsetSessionAjax(){
		$CURRENCY_TYPE = Configure::read('CURRENCY_TYPE');
		$kiosk_id = $this->request->query['kiosk_id'];
		$reference = $this->request->query['reference'];
		$product_code = $this->request->query['prod_code'];
		$session_basket = $this->request->Session()->read("stock_taking_basket");
		if(!empty($session_basket)){
			if(array_key_exists($product_code,$session_basket)){
				unset($session_basket[$product_code]);
			}
		}
		$this->request->Session()->write('stock_taking_basket', $session_basket);
		$session_basket = $this->request->Session()->read('stock_taking_basket');
		
		
		if(is_array($session_basket) && !empty($session_basket)){
			$kioskId = $this->request->Session()->read('stock_taking_kiosk_id');
			if($kioskId == 10000){
				//$productSource = "kiosk_{$kioskId}_products";
				$productSource = "products";
				   $productTable = TableRegistry::get($productSource,[
																			'table' => $productSource,
																				]);
			}else{
				$productSource = "kiosk_{$kioskId}_products";
				$productTable = TableRegistry::get($productSource,[
																			'table' => $productSource,
																				]);
			}
			$val = array_keys($session_basket);
			if(empty($val)){
				$val = array(0 => null);
			}
			$productCodeArr_query = $productTable->find('list',
															[
																'keyField' => 'product_code',
																'valueField' => 'id',
																'conditions'=>['product_code IN'=>$val],
															]
												  );
			$productCodeArr_query = $productCodeArr_query->hydrate(false);
			if(!empty($productCodeArr_query)){
				$productCodeArr = $productCodeArr_query->toArray();
			}else{
				$productCodeArr = array();
			}
			
			
			$costSellingData_query = $productTable->find('all',array('fields'=>array('product_code','cost_price','selling_price','product','Quantity'),'conditions'=>array('product_code IN'=>$val),'recursive'=>-1));
			$costSellingData_query = $costSellingData_query->hydrate(false);
			$costSellingData = $costSellingData_query->toArray();
			//pr($costSellingData);die;
			$costArr = array();
			$sellingArr = array();
			$productNameArr = array();
			if($costSellingData){
				foreach($costSellingData as $key=>$costSellingInfo){
				$costArr[$costSellingInfo['product_code']] = $costSellingInfo['cost_price'];
				$sellingArr[$costSellingInfo['product_code']] = $costSellingInfo['selling_price'];
				$productNameArr[$costSellingInfo['product_code']] = $costSellingInfo['product'];
				$productQuantityArr[$costSellingInfo['product_code']] = $costSellingInfo['Quantity'];
				}
			}
			$basketStr = '';
			foreach($session_basket as $productCode=>$productData){
			$basketStr.="<tr>
							<td>{$productCodeArr[$productCode]}</td>
							<td>{$productCode}</td>
							<td>".$productNameArr[$productCode]."</td>
							<td>".$CURRENCY_TYPE.number_format($sellingArr[$productCode],2)."</td>
							<td>".$productQuantityArr[$productCode]."</td>
							<td>".$productData['quantity']."</td></tr>";
							//<td>".$productData['difference']."</td>
			}
			
			if(!empty($basketStr)){
				$basketStr = "<table><tr><th style='width: 99px;'>Product id</th><th style='width: 125px;'>Product code</th><th>Product name</th><th style='width: 74px;'>SP</th><th style='width: 62px;'>Org</br>Qty</th><th style='width: 46px;'>Qty</th></tr>".$basketStr."</table>";
							//<th>Difference</th>
				//echo $basketStr;die;
				$basketStr = trim(str_replace(array("\n", "\r", "\t"), '', $basketStr));
				
				$kskId = $this->request->Session()->read('kiosk_id');
				$this->SessionRestore->update_session_backup_table($this->request->params['controller'], 'stock_taking', 'stock_taking_kiosk_id', $kiosk_id, $kskId);
					$this->SessionRestore->update_session_backup_table($this->request->params['controller'], 'stock_taking', 'stock_taking_reference', $reference, $kskId);
					$this->SessionRestore->update_session_backup_table($this->request->params['controller'], 'stock_taking', 'stock_taking_basket', $session_basket, $kskId);
				
				echo json_encode(array("basket" => $basketStr));
			}else{
				echo json_encode(array('basket' => 'No Items in the basket'));
			}
	    }else{
			echo json_encode(array('basket' => 'No Items in the basket'));
		}
		die;
	}
	
	
	public function getProductModels(){
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
		//$this->layout = false;
	}
    
}

?>