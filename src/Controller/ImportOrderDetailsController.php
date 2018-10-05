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

class ImportOrderDetailsController extends AppController
{
    public $helpers = [
         'Paginator' => ['templates' => 'paginatortemplates']
         ];
    public function initialize()
    {
        parent::initialize();
        $this->loadComponent('ScreenHint');
        $this->loadComponent('SessionRestore');
        $this->loadModel('Categories');
        $this->loadModel('Products');
        $this->loadModel('DefectiveCentralProducts');
        $this->loadModel('Users');
        $this->loadModel('ImportOrderReferences');
        $this->loadModel('ImportOrderDetails');
        $this->loadModel('DefectiveBinReferences');
        $this->loadModel('DefectiveBinTransients');
        $this->loadModel('DefectiveCentralProducts');
        $this->loadModel('DefectiveBin');
        $this->loadModel('DefectiveBinTransients');
    }
    
    public function index()
    {
        if($this->request->session()->read('Auth.User.group_id') == ADMINISTRATORS || $this->request->session()->read('Auth.User.group_id') == FRANCHISE_OWNER){ 
			$categories = $this->Categories->find('all',array(
									'fields' => array('id', 'category','id_name_path'),
																	'conditions' => array('Categories.status' => 1),
									'order' => 'Categories.category asc',
									'recursive' => -1
									));
            $categories->hydrate(false);
            if(!empty($categories)){
                $categories = $categories->toArray();
            }
			$categories = $this->CustomOptions->category_options($categories,true);
			
			$ImportStocks =  $this->DefectiveCentralProducts->find('all',array(
								'conditions' => array('DefectiveCentralProducts.original_quantity > 0'),
									'order' => 'id desc',
									'recursive' => -1
									)
								);
            $ImportStocks->hydrate(false);
            if(!empty($ImportStocks)){
                $ImportStocks = $ImportStocks->toArray();
            }
			$productIds = array();
			$original_quantities = array();
			foreach($ImportStocks as $ImportStock){
                //pr($ImportStock);die;
				$productIds[] = $ImportStock['product_id'];
				$original_quantities[$ImportStock['product_id']] = $ImportStock['original_quantity'];
					
			}
            //pr($original_quantities);die;
			if(empty($productIds)){
				$productIds = array(0 => null);
			}
			$this->paginate = 
                                [
                                    'conditions' =>['Products.id IN' => $productIds] ,
                                    'limit' => 20,
                                    //'recursive'=>-1
                                ];
				 
		
			$import_basket = $this->request->Session()->read('import');
			$importStrDetail = '';
			if(is_array($import_basket)){
				$productCodeArr = array();
				foreach($import_basket as $key => $importItem){
					$productCodeArr_query = $this->Products->find('all',array('conditions'=>array('Products.id IN'=>$key),'fields'=>array('id','product_code'),'recursive'=>-1));
                    $productCodeArr_result = $productCodeArr_query->first();
                    if(!empty($productCodeArr_result)){
                        $productCodeArr[] = $productCodeArr_result->toArray();
                    }
				}
				$productCode = array();
				if(!empty($productCodeArr)){
					foreach($productCodeArr as $k=>$productCodeData){
						$productCode[$productCodeData['id']]=$productCodeData['product_code'];
					}
				}
				foreach($import_basket as $productId=>$productDetails){
					$query = $this->Products->find('all',array('conditions'=>array('Products.id IN'=>$productId),'fields'=>array('id','product'),'recursive'=>-1));
                    $productName = $query->first();
                    if(!empty($productName)){
                        $productName = $productName->toArray();
                    }
					$importStrDetail.= "<tr>
					<td>".$productCode[$productId]."</td>
					<td>".$productName['product']."</td>
					<td>".$productDetails['quantity']."</td>
					</tr>";
				}
			
			}
			if(!empty($importStrDetail)){
				$importStr = "<table>
				<tr>
					<th style='width: 128px;'>Product code</th>
					<th style='width: 1200px;'>Product</th>
					<th>Quantity</th>
					</tr>".$importStrDetail."
				</table>";
			}
			
			$totalimportItems = count($import_basket);
			$reference = $this->request->Session()->read('session_reference');
			$referenceError = $this->request->Session()->read('referenceError');
			$flashMessage = '';
			if(!empty($referenceError)){
				$flashMessage = $this->request->Session()->read('referenceError');
			}elseif($totalimportItems){
				$flashMessage = "Total item Count:$totalimportItems<br/>
				Reference For :$reference<br/>
				$importStr";
			}
			
			if(!empty($flashMessage)){
				$this->Flash->success($flashMessage,['escape' => false]);
				$this->request->Session()->delete('referenceError');
			}
			
			$products = $this->paginate('Products');
			$categoryIdArr = array();
			foreach($products as $key=>$product){
                //pr($product);die;
				$categoryIdArr[] = $product->category_id;
			}
			
			$hint = $this->ScreenHint->hint('import_order_details','index');
			if(!$hint){
				$hint = "";
			}
			if(empty($categoryIdArr)){
				$categoryIdArr = array(0 => null);
			}
			$categoryName = $this->Categories->find('list',
                                                    ['conditions'=>['Categories.id IN'=>$categoryIdArr],
                                                           'keyField' => 'id',
                                                            'valueField' => 'category'
                                                          ]);
            $categoryName = $categoryName->toArray();
			$this->set(compact('hint','categories','kiosks','displayType','products','categoryName','original_quantities'));
			$this -> render('index');
		}
		else{
			return $this->redirect(array('action' => 'dashboard', 'controller' => 'home'));
		}
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
    
    public function importStock()
    {
        if(array_key_exists('kiosk_id',$this->request->Session()->read())){
			$sessionKioskId = $this->request->Session()->read('kiosk_id');
		}else{
			$sessionKioskId = "10000";
		}
		$productsName_query = $this->Products->find('list',
                                              [
                                                'keyField' => 'id',
                                                'valueField' => 'category'
                                               ]
                                              );
        $productsName_query = $productsName_query->hydrate(false);
        if($productsName_query){
            $productsName = $productsName_query->toArray();
        }else{
            $productsName = array();
        }
		$current_page = '';
		$reference = '';
		if(array_key_exists('current_page',$this->request['data'])){
			$current_page = $this->request['data']['current_page'];
		}
		
		if(!isset($current_page)){$this->redirect(array('action' => 'index'));}
		
		$importproductCounts = 0;
        //pr($this->request['data']['data']);die;
		if(array_key_exists('Product',$this->request['data'])){
			$reference = $this->request['data']['Product']['reference'];
		}
		if(array_key_exists('basket',$this->request['data'])){
			$productArr = array();
			$prdctIdArr = array();
			if(!empty($reference)){
				$this->request->Session()->write('session_reference', $reference);
			}
			foreach($this->request['data']['Product']['quantity'] as $key => $quantity){
				$checkedQtt = $this->request['data']['Product']['checked'][$key];
				if($checkedQtt > 0){
					$currentQuantity = $this->request['data']['Product']['p_quantity'][$key];
					$productID = $this->request['data']['Product']['product_id'][$key];
					$remrk = $this->request['data']['Product']['remarks'][$key];
					if($quantity > 0 && $quantity <= $currentQuantity){
						$productArr[$productID] = array(
										'quantity' => $quantity,
										'remarks' => $remrk
									);
						$importproductCounts++;
					}
				}
								
			}
			$import_basket = $this->request->Session()->read('import');
			if(count($import_basket) >= 1){
				//adding item to the the existing session
				$importsum_total = $this->add_arrays(array($productArr,$import_basket));
				$this->request->Session()->write('import', $importsum_total);
				$import_basket = $this->request->Session()->read('import');
			}else{
				//adding item first time to session
				if($importproductCounts > 0)$this->request->Session()->write('import', $productArr);
			}
			$totalimportItems = count($this->request->Session()->read('import'));
			 //pr($totalimportItems);die;
			 
			$import_basket = $this->request->Session()->read('import');
			if($importproductCounts > 0){
				if(is_array($import_basket) && count($import_basket)){
					//storing the session in session_backups table
					$this->SessionRestore->update_session_backup_table($this->request->params['controller'], 'index', 'import', $import_basket, $sessionKioskId);
					$this->SessionRestore->update_session_backup_table($this->request->params['controller'], 'index', 'session_reference', $this->request->Session()->read('session_reference'), $sessionKioskId);
				}
				$flashMessage = "$importproductCounts product(s) added to the stock. Total item Count:$totalimportItems";
			}else{
				$flashMessage = "No item added to the stock. Item Count:$importproductCounts";
			}
			$this->Flash->success($flashMessage);
			 
		}elseif(array_key_exists('empty_basket',$this->request['data'])){
			if($this->request->Session()->delete('import')){
				$this->request->Session()->delete('session_reference');
				$this->SessionRestore->delete_from_session_backup_table($this->request->params['controller'], 'index', 'import', $sessionKioskId);
				$this->SessionRestore->delete_from_session_backup_table($this->request->params['controller'], 'index', 'session_reference', $sessionKioskId);
			}
			
			$flashMessage = "Basket is empty; Add Fresh Stock!";
			$this->Flash->success($flashMessage);
			return $this->redirect(array('action' => "index/page:$current_page"));			
		}elseif(array_key_exists('check_out',$this->request['data'])){
			return $this->redirect(array('action' => "import_checkout"));			
		}elseif(array_key_exists('move_to_bin',$this->request['data'])){
            
			 $productArr = array();
			 $productCounts = 0;
			 $countBinTransientSaved = 0;
			 $sum_total = array();
			 if($this->request['data']['move_to_bin'] != 1){
            	//we are not directing to add_to_session in this case when the request is coming from checkout page";die;
				$productCounts = $this->add_to_session( $this->request['data'],$current_page, $sessionKioskId);
				$session_data = $this->check_session($productCounts, $current_page, $sessionKioskId);
                if(!empty($session_data)){
                    return $session_data;
                }
                //pr($session_data);die;
			 }
             
			 $sessionQuantity = $this->session_quantity();
			 $this->check_session($sessionQuantity, $current_page, $sessionKioskId);
			 $import_basket = $this->request->Session()->read('import');
			 if(array_key_exists('Product',$this->request['data'])){
				$reference = $this->request['data']['Product']['reference'];
			 }
				
			 if(is_array($import_basket) && count($import_basket)){
				//storing the session in session_backups table
				$this->SessionRestore->update_session_backup_table($this->request->params['controller'], 'index', 'import', $import_basket, $sessionKioskId);
				$this->SessionRestore->update_session_backup_table($this->request->params['controller'], 'index', 'reference', $this->request->Session()->read('session_reference'), $sessionKioskId);
			 }
			 $datetime = $this->get_time();
			 //checking if reference already exists in defectivebinreference table
			 $this->check_duplicate_reference('DefectiveBinReferences', $reference, "index/page:$current_page?$sessionKioskId");
			
			 //create reference id in reference table
			 //get reference id
			 $binReferenceData = array(
						'reference' => $reference,
						'kiosk_id' => $sessionKioskId,
						'user_id' => $this->request->session()->read('Auth.User.id')
							 );
				//pr($binReferenceData);die;
			 $create_new = $this->DefectiveBinReferences->newEntity($binReferenceData,['validate' => false]);
             $create_patch = $this->DefectiveBinReferences->patchEntity($create_new,$binReferenceData,['validate' => false]);
             //pr($create_patch);die;
			 $get_defectivebinreference_id = $this->DefectiveBinReferences->save($create_patch);
             //pr($get_defectivebinreference_id);die;
			 $binReferenceId = $get_defectivebinreference_id->id; 
			 $productCostlist_query = $this->Products->find('list', [
											'conditions' => ['Products.id IN' => array_keys($import_basket)],
											'keyField' => 'id',
                                            'valueField' => 'cost_price'
											]);
             $productCostlist_query = $productCostlist_query->hydrate(false);
             if(!empty($productCostlist_query)){
                $productCostlist = $productCostlist_query->toArray();
             }else{
                $productCostlist = array();
				$faulty_quantity = array();
             }
             
			 foreach($import_basket as $productID => $product_data){
				 $faulty_quantity[$productID] = $product_data['quantity'];
			 }
			 foreach($faulty_quantity as $productID => $faultyQtt){
				$singleProductCost = $productCostlist[$productID]; 
				$totalProductCost = $productCostlist[$productID]*$faultyQtt;  
				$transientBinProductData = array(
							 'kiosk_id' => $sessionKioskId,
							 'defective_bin_reference_id' => $binReferenceId,
							 'user_id' => $this->Auth->user('id'),
							 'product_id' => $productID,
							 'quantity' => $faultyQtt,
							 'total_product_cost' => $totalProductCost,
							 'single_product_cost' => $singleProductCost,
							 'status' => 1,//now we are saving the products directly to the central bin along with transient. so not saving the status 0
									 );
                 $new_create = $this->DefectiveBinTransients->newEntity($transientBinProductData,['validate' => false]);
				 $new_patch = $this->DefectiveBinTransients->patchEntity($new_create,$transientBinProductData,['validate' => false]);
				 $this->DefectiveBinTransients->save($new_patch);
                 
                 $updatequery = "UPDATE defective_central_products SET original_quantity = original_quantity-$faultyQtt WHERE product_id = $productID";
                        $updateconn = ConnectionManager::get('default');
                        $updatestmt = $updateconn->execute($updatequery);
                 
				
				 //echo "saving status to 2 which is for moved to bin transient";die;
				 //$this->DefectiveKioskProduct->saveField('date_of_movement',$transientCreated);
				 $countBinTransientSaved = 1;
				 if($countBinTransientSaved == 1){
				 //sending to central bin from here
				 $this->send_to_central_bin($productID, $faultyQtt);
				}
				
			 }
			 if($countBinTransientSaved > 0){
				//sending to central bin from here
				$totalCostData_query = $this->DefectiveBinTransients->find('all', array(
													 'conditions' => array('DefectiveBinTransients.defective_bin_reference_id' => $binReferenceId)));
				$totalCostData_query
										->select(['totalCost' => $totalCostData_query->func()->sum('total_product_cost')]);
				$totalCostData_query = $totalCostData_query->hydrate(false);
				if(!empty($totalCostData_query)){
					$totalCostData = $totalCostData_query->first();
				}else{
					$totalCostData = array();
				}
				$totalCostSum = $totalCostData['totalCost'];
				$bin_entity = $this->DefectiveBinReferences->get($binReferenceId);
				$data = array('total_cost' => $totalCostSum);
				$bin_entity = $this->DefectiveBinReferences->patchEntity($bin_entity,$data,['validate' => false]);
				$this->DefectiveBinReferences->save($bin_entity);
			 }
             
			 $sessionProductTable = $this->flash_cart();
			 $flashMessage = $countBinTransientSaved." products added to Bin under reference: $reference<br/>$sessionProductTable";
				if($this->request->Session()->delete('import')){
				 $this->request->Session()->delete('session_reference');
				 //deleting the session from session_backups table
				 //$this->SessionRestore->delete_from_session_backup_table($this->params['controller'], $this->params['action'], 'index', $sessionKioskId);
				 //$this->SessionRestore->delete_from_session_backup_table($this->params['controller'], $this->params['action'], 'reference', $sessionKioskId);
						$this->SessionRestore->delete_from_session_backup_table($this->request->params['controller'], 'index', 'import', $sessionKioskId);
						$this->SessionRestore->delete_from_session_backup_table($this->request->params['controller'], 'index', 'session_reference', $sessionKioskId);
				}
										$this->Flash->success($flashMessage,['escape' => false]);
										return $this->redirect(array('action' => "index? $sessionKioskId"));
    }else{
        //echo'hi';die;
			$productArr = array();
			 
			if(!empty($this->request->data) &&
			array_key_exists('Product',$this->request['data'])){
				if(isset($reference)){
					$this->request->Session()->write('session_reference', $reference);
				}
				foreach($this->request['data']['Product']['quantity'] as $key => $quantity){
					$checkedQtt = $this->request['data']['Product']['checked'][$key];
					if($checkedQtt > 0){
						$currentQuantity = $this->request['data']['Product']['p_quantity'][$key];
						$productID = $this->request['data']['Product']['product_id'][$key];
						$remrk = $this->request['data']['Product']['remarks'][$key];
						if($quantity > 0 && $quantity <= $currentQuantity){
							$productArr[$productID] = array(
											'quantity' => $quantity,
											'remarks' => $remrk
										);
							$importproductCounts++;
						}
					}
				}
				$import_basket = $this->request->Session()->read('import');
				$importsum_total = $this->add_arrays(array($productArr,$import_basket));
				if(count($importsum_total) == 0){
					$flashMessage = "Failed to place order. <br />Please select quantity atleast for one product!";
					$this->Flash->success($flashMessage);
					$this->redirect(array('action' => "index/page:$current_page"));
					die;
				}
				if(count($import_basket) >= 1){
					//adding item to the the existing session
					$importsum_total = $this->add_arrays(array($productArr,$import_basket));
					$this->request->Session()->write('import', $importsum_total);
					$import_basket = $this->request->Session()->read('import');
				}else{
					//adding item first time to session
					if(count($importsum_total))$this->request->Session()->write('import', $productArr);
					$import_basket = $this->request->Session()->read('import');
				}
			}
		 
			$datetime = date('Y-m-d H:i:s');
			$import_basket = $this->request->Session()->read('import');
			$reference = $this->request->Session()->read('session_reference');
			
			//check for reference to be unique	
			$countDuplicate = $this->check_duplicate_reference('ImportOrderReferences', $reference);
			$referenceError = '';
			if($countDuplicate > 0){
				$referenceError = 'Entered reference already exists. Please enter a unique reference';
				$this->request->Session()->write('referenceError',$referenceError);
				return $this->redirect(array('action' => "index/page:$current_page"));
			}
			
			if(!empty($import_basket) && !empty($reference)){
				 	$importOrder_ref = array(
						'user_id' => $this->request->Session()->read('Auth.User.id'),
						'country' =>'China',
						//'received_date' =>$datetime,
						'reference' =>$reference,
						//'received_by' => $this->Session->read('Auth.User.id'),
						//'status' => 1
						);
                $newentity = $this->ImportOrderReferences->newEntity($importOrder_ref,['validate' => false]);
				$patchentity = $this->ImportOrderReferences->patchEntity($newentity,$importOrder_ref,['validate' => false]);
				$import_last_insertid = $this->ImportOrderReferences->save($patchentity);
                //pr($import_last_insertid);die;
                //pr($this->ImportOrderReference);die;
				$import_order_reference_id = $import_last_insertid->id;
				$import_basket = $this->request->Session()->read('import');
            
				foreach($import_basket as $productID => $importData){
					
					$quantity = $importData['quantity'];
					$remrks = $importData['remarks'];
					$importdatadetails = array(
								'import_order_id' => $import_order_reference_id,
								'product_id' => $productID,
								'quantity' => $quantity,
								'remarks' => $remrks,
								//'status' => '1'
								);
					$new_entity = $this->ImportOrderDetails->newEntity($importdatadetails,['validate' => false]);
					$patch_entity = $this->ImportOrderDetails->patchEntity($new_entity,$importdatadetails,['validate' => false]);
					if($this->ImportOrderDetails->save($patch_entity)){
                        //echo'error';die;
                        $query = "UPDATE defective_central_products SET original_quantity = original_quantity-$quantity WHERE product_id = $productID";
                        $conn = ConnectionManager::get('default');
                        $stmt = $conn->execute($query); 
					}
				}
                
				if($this->request->Session()->delete('import')){
					$this->request->Session()->delete('session_reference');
					$this->SessionRestore->delete_from_session_backup_table($this->request->params['controller'], 'index', 'import', $sessionKioskId);
					$this->SessionRestore->delete_from_session_backup_table($this->request->params['controller'], 'index', 'session_reference', $sessionKioskId);
				}
				$flashMessage = count($import_basket)." products ordered under the order id $import_order_reference_id";
				$this->Flash->success($flashMessage);
			}else{
				$flashMessage =  "Reference number is missing!";
				$this->Flash->success($flashMessage);
			}
				
			 
		}		
		return $this->redirect(array('action' => "index/page:$current_page"));
    }
    
    private function send_to_central_bin($productId = '', $unreceived_quantity = ''){
				$productListBin = $this->DefectiveBin->find('list',
                                                             [
                                                                 'keyField' => 'product_id',
                                                                 'valueField' => 'quantity'
                                                             ]);
                $productListBin = $productListBin->toArray();
				if(array_key_exists($productId,$productListBin)){
			
				$date_time = $this->get_time();
						$this->DefectiveBin->updateAll(array('defective_bin.quantity' => "defective_bin.quantity + $unreceived_quantity",
								'defective_bin.modified' => "'$date_time'"),
								array('defective_bin.product_id' => $productId)
						);
				} else{
						$create_new_entity = $this->DefectiveBin->newEntity();
						$binData = array(
								'product_id' => $productId,
								'quantity' => $unreceived_quantity
						);
			 
						$create_patch_entity = $this->DefectiveBin->patchEntity($create_new_entity,$binData);
						$this->DefectiveBin->save($create_patch_entity);
				} 
		}
    
    private function add_to_session($queryVar = array(), $current_page, $kioskId){
				//pr($this->request['data']); 
				$productCounts = 0;
				$reference = $this->request['data']['Product']['reference'];
				if(array_key_exists('reference', $this->request['data']['Product'])){
						foreach($this->request['data']['Product']['quantity'] as $key => $quantity){
								$checkedQtt = $this->request['data']['Product']['checked'][$key];
								if($checkedQtt > 0){
										$currentQuantity = $this->request['data']['Product']['p_quantity'][$key]; 
										$productID = $this->request['data']['Product']['product_id'][$key];
										$remrk = $this->request['data']['Product']['remarks'][$key];
										if($quantity > 0 && $quantity <= $currentQuantity){
												$productArr[$productID] = array(
														'quantity' => $quantity,
														'remarks' => $remrk
												);
												$productCounts++;
										}
								}
						}
				} 
				$import_basket = $this->request->Session()->read('import');
				if($productCounts > 0){
				  //adding item to the the existing session
						foreach($productArr as $productId => $info){
								if(is_array($import_basket) && array_key_exists($productId, $import_basket)){
									//push the array to existing product_id
									//$session_basket[$productId] = $info;
									foreach($info as $faultyId => $faultyQtt){
										$import_basket[$productId][$faultyId] = $faultyQtt;
									}
								}else{
									//push array with new product_id as key
									$import_basket[$productId] = $info;
								}
						}
						//$sum_total = $this->add_arrays(array($productArr,$session_basket));
						$this->request->Session()->write('import', $import_basket);
						$import_basket = $this->request->Session()->read('import');
				}
				return $productCounts;
		}
        
        private function check_session($sessionQuantity = '', $current_page = '', $sessionKioskId = ''){
				if($sessionQuantity == 0){
						$flashMessage = "Failed to proceed. <br />Please select quantity atleast for one product!";
						$this->Flash->success($flashMessage,['escape' => false]);
						return $this->redirect(array('action' => "index/page:$current_page?$sessionKioskId"));
				}
		}
        
        private function session_quantity(){
				$sessionQuantity = 0;
				if(is_array($this->request->Session()->read('import'))){
						foreach($this->request->Session()->read('import') as $key => $sessionInfo){
								$sessionQuantity+=count($sessionInfo['quantity']);
						}
				}
				return $sessionQuantity;
		}
        
    private function check_duplicate_reference($modelName = '', $reference = ''){
        //echo $modelName;die;
		$countDuplicate_query = $this->$modelName->find('all',
                                                        ['conditions' => [$modelName.'.reference' => $reference]
                                                        ]);
        $countDuplicate = $countDuplicate_query->count();
		return $countDuplicate;
	}
    
    private function get_time(){
        $conn = ConnectionManager::get('default');
        $stmt = $conn->execute("SELECT NOW() as created from defective_central_products");
        $currentTimeInfo = $stmt ->fetchAll('assoc');
        //$currentTimeInfo = $currentTimeInfo->toArray();
		$currentTime = $currentTimeInfo[0]['created'];
		$date_time = date('Y-m-d H:i:s',strtotime($currentTime));
		return $date_time;
	}
    
    public function importCheckout()
    {
        if(array_key_exists('kiosk_id',$this->request->Session()->read())){
			$sessionKioskId = $this->request->Session()->read('kiosk_id');
		}else{
			$sessionKioskId = "10000";
		}
		$import_basket = $this->request->Session()->read('import');
		//pr($import_basket);die;	
		if(is_array($import_basket)){
			$product_ids = array_keys($import_basket);
			$productCodeArr = array();
			$productCodeArr = $this->Products->find('all',array('conditions'=>array('Products.id IN'=>$product_ids),'fields'=>array('id','product_code','quantity'),'recursive'=>-1));
            $productCodeArr->hydrate(false);
            if(!empty($productCodeArr)){
                $productCodeArr = $productCodeArr->toArray();
            }
			foreach($import_basket as $key => $basketItem){
			if($key == 'error')continue;
			}
			$productCode = array();
			if(!empty($productCodeArr)){
				foreach($productCodeArr as $k=>$productCodeData){
					$productIds[$productCodeData['id']] = $productCodeData['product_code'];
					$productCodes[$productCodeData['product_code']] = $productCodeData['quantity'];
				
				}
			}
			$productNameArr = array();
		 
			foreach($import_basket as $productId=>$productDetails){
				$productNameArr_query = $this->Products->find('all',array('conditions'=>array('Products.id IN'=>$productId),'fields'=>array('id','product'),'recursive'=>-1));
                $productNameArr_result = $productNameArr_query->hydrate(false);
                if(!empty($productNameArr_result))
                {
                    $productNameArr[] = $productNameArr_result->first();
                }else{
                    $productNameArr[] = array();
                }
			}
			foreach($productNameArr as $key=>$selectedProducts){
                //pr($selectedProducts);die;
				$productArr[$selectedProducts['id']] = $selectedProducts['product'];
			}
			if($this->request->is('post')){
				$error = array();
				if(array_key_exists('update_quantity',$this->request->data)){
					//pr($this->request->data);die;
					$productIds = array_keys($import_basket);
					$lessProducts = array();
					$lowProducts = array();
					$quantityList_query = $this->DefectiveCentralProducts->find('list',[
                                                                                'conditions' => ['DefectiveCentralProducts.product_id IN' => $productIds],
                                                                                'keyField' => 'product_id',
                                                                                'valueField' => 'original_quantity'
                                                                                 ]
                                                                          );
                    $quantityList_query = $quantityList_query->hydrate(false);
                    if(!empty($quantityList_query)){
                        $quantityList = $quantityList_query->toArray();
                    }else{
                        $quantityList = array();
                    }
                    //pr($this->request->data);die;
					foreach($this->request->data['CheckOut'] as $productID => $quantity){
						if($quantity == 0 || !(int)$quantity){
								$lowProducts[] = $productArr[$productID];
						}
						//pr($productID);
                        //pr($quantityList);die;
                        if($quantity > $quantityList[$productID]){
							$lessProducts[] = $productArr[$productID];
						}	
					}
					if(count($lessProducts) >= 1){
						$this->Flash->error("Please choose quantity less than or equal to available stock for products: ".implode(",",$lessProducts));
						return $this->redirect(array('action'=>'import_checkout'));
					}
					if(count($lowProducts) > 0){
						 $this->Flash->error("Please choose quantity more than 0 for product : ".implode(",",$lowProducts) );
						return $this->redirect(array('action'=>'import_checkout'));
					}else{
						$requestedQuantity = $this->request->data['CheckOut'];
						//pr($requestedQuantity);
						$newArray = array();
						 //pr($session_basket);
						foreach($import_basket as $productID => $productData){
							$qty =  $requestedQuantity[$productID];
							$newimport[$productID] = array(
										'quantity' => $qty,
										'remarks' => $import_basket[$productID]['remarks']
										);
						}
						$this->request->Session()->delete('import');
						if($this->request->Session()->write('import',$newimport)){
							$this->SessionRestore->update_session_backup_table($this->params['controller'], 'index', 'import', $newimport, $sessionKioskId);
						}
						$this->Flash->success("Quantity has been basket successfully updated");
						return $this->redirect(array('action'=>'import_checkout'));
					}
				}elseif(array_key_exists('edit_basket',$this->request->data)){
					return $this->redirect(array('action'=>"index/$kiosk_id"));
				}elseif(array_key_exists('Move_to_bin',$this->request->data)){
					$reference = $this->request->Session()->read('session_reference');
					$requestedQuantity = $this->request->data['CheckOut'];
						//pr($requestedQuantity);
						$updateimport = array();
						 //pr($session_basket);
						foreach($import_basket as $productID => $productData){
							$qty =  $requestedQuantity[$productID];
							$updateimport[$productID] = array(
										'quantity' => $qty,
										'remarks' => $import_basket[$productID]['remarks']
										);
						}
						$binReferenceData = array(
								'reference' => $reference,
								'kiosk_id' => $sessionKioskId,
								'user_id' => $this->Auth->user('id'),
										 );
						 
                        $new_entity = $this->DefectiveBinReferences->newEntity($binReferenceData,['validate' => false]);
                        $patch_entity = $this->DefectiveBinReferences->patchEntity($new_entity,$binReferenceData,['validate' => false]);
						$this->DefectiveBinReferences->save($patch_entity);
						$binReferenceId = $patch_entity->id;
						$productCostlist_query = $this->Products->find('list',[
                                                                                'conditions' => ['Products.id IN' => array_keys($import_basket)],
                                                                                'keyField' => 'id',
                                                                                'valueField' => 'cost_price'
                                                                              ]
                                                                       );
                        $productCostlist_query =  $productCostlist_query->hydrate(false);
                        if(!empty($productCostlist_query)){
                            $productCostlist = $productCostlist_query->toArray();
                        }else{
                            $productCostlist = array();
                        }
						$faulty_quantity = array();
						foreach($import_basket as $productID => $product_data){
							 $faulty_quantity[$productID] = $product_data['quantity'];
						}	//mark the same item in raw_faulty_product table as moved with the date_of_action added
						foreach($faulty_quantity as $productID => $faultyQtt){
							$singleProductCost = $productCostlist[$productID]; 
							$totalProductCost = $productCostlist[$productID]*$faultyQtt;  
							$transientBinProductData = array(
														'kiosk_id' => $sessionKioskId,
														'defective_bin_reference_id' => $binReferenceId,
														'user_id' => $this->Auth->user('id'),
														'product_id' => $productID,
														'quantity' => $faultyQtt,
														'total_product_cost' => $totalProductCost,
														'single_product_cost' => $singleProductCost,
														'status' => 1,//now we are saving the products directly to the central bin along with transient. so not saving the status 0
																  );
                                							
                                $newEntity = $this->DefectiveBinTransients->newEntity($transientBinProductData,['validate' => false]);		
								$patchEntity = $this->DefectiveBinTransients->patchEntity($newEntity,$transientBinProductData,['validate' => false]);
								$this->DefectiveBinTransients->save($patchEntity);
                                
                                $updatequery = "UPDATE defective_central_products SET original_quantity = original_quantity-$faultyQtt WHERE product_id = $productID";
                                $updateconn = ConnectionManager::get('default');
                                $updatestmt = $updateconn->execute($updatequery);
                                
								$countBinTransientSaved = 1;
								if($countBinTransientSaved == 1){
									//sending to central bin from here
									$this->send_to_central_bin($productID, $faultyQtt);
								}
						}
						if($countBinTransientSaved > 0){
							//sending to central bin from here
                            
                            $totalCostData_query = $this->DefectiveBinTransients->find('all',array('conditions' => array('DefectiveBinTransients.defective_bin_reference_id' => $binReferenceId)));
                            $totalCostData_query
                                      ->select(['totalCost' => $totalCostData_query->func()->sum('total_product_cost')]);
                            $totalCostData_query = $totalCostData_query->hydrate(false);
                            if(!empty($totalCostData_query)){
                                $totalCostData = $totalCostData_query->first();
                            }else{
                                $totalCostData = array();
                            }
                           
							$totalCostSum = $totalCostData['totalCost'];
                            $dataArr = array('total_cost' => $totalCostSum);
                            $id_get = $this->DefectiveBinReferences->get($binReferenceId);
							$patch_e = $this->DefectiveBinReferences->patchEntity($id_get,$dataArr,['validate' => false]);
                            $this->DefectiveBinReferences->save($patch_e);
						}
						$sessionProductTable = $this->flash_cart();
						$flashMessage = $countBinTransientSaved." products added to Bin under reference: $reference<br/>$sessionProductTable";
						if($this->request->Session()->delete('import')){
							$this->request->Session()->delete('session_reference');
							$this->SessionRestore->delete_from_session_backup_table($this->params['controller'], 'index', 'import', $sessionKioskId);
							$this->SessionRestore->delete_from_session_backup_table($this->params['controller'], 'index', 'session_reference', $sessionKioskId);
						}
						 $this->Flash->error($flashMessage,['escape' => false]);
						return $this->redirect(array('action' => "index"));
				}
			}
			$this->set(compact('productArr','productCode','productIds'));
		}
    }
    
    public function search($keyword = "")
    {
        if(array_key_exists('search_kw',$this->request->query)){
			$search_kw = $this->request->query['search_kw'];
		}
		extract($this->request->query);
		$categories = $this->Categories->find('all',array(
								'fields' => array('id', 'category','id_name_path'),
                                                                'conditions' => array('Categories.status' => 1),
								'order' => 'Categories.category asc',
								'recursive' => -1
								));
        $categories->hydrate(false);
        if(!empty($categories)){
            $categories = $categories->toArray();
        }
        //pr($categories);die;
		$conditionArr = array();
		//----------------------
		if(!empty($search_kw)){
			$conditionArr['OR']['LOWER(product) like '] =  strtolower("%$search_kw%");
			$conditionArr['OR']['LOWER(product_code) like '] =  strtolower("%$search_kw%");
		}
		//----------------------
		if(array_key_exists('category',$this->request->query) && !empty($this->request->query['category'][0])){
			$conditionArr['category_id IN'] = $this->request->query['category'];
		}
		$ImportStocks =  $this->DefectiveCentralProducts->find('all',array(
								'conditions' => array('DefectiveCentralProducts.original_quantity > 0'),
								'order' => 'id desc',
								'recursive' => -1
							)
						);
        $ImportStocks->hydrate(false);
        if(!empty($ImportStocks)){
            $ImportStocks = $ImportStocks->toArray();
        }
		$productIds = array();
		$original_quantities = array();
        //pr($conditionArr);die;
        //pr($ImportStocks);die;
		foreach($ImportStocks as $ImportStock){
            //pr($ImportStock);die;
			$productIds[] = $ImportStock['product_id'];
			$original_quantities[$ImportStock['product_id']] = $ImportStock['original_quantity'];
				
		}
        //pr($productIds);die;
        //$conditionArr = array();
        $conditionArr['Products.id IN'] = $productIds;
		$this->paginate =
                            [
                                'conditions' =>$conditionArr,
                                'limit' => 20,
                                //'recursive'=>-1
                            ];
                            //pr($this->paginate);
		$selectedCategoryId=array();
        
		if(array_key_exists('category_id IN',$conditionArr) && !empty($conditionArr['category_id IN'][0])){
			$selectedCategoryId = $conditionArr['category_id IN'];
		}
        //pr($conditionArr['category_id IN']);die;
        //pr($selectedCategoryId);die;
		$categories = $this->CustomOptions->category_options($categories,true,$selectedCategoryId);
		 
		$products = $this->paginate('Products');
        $products = $products->toArray();
		//pr($products);die;
		$categoryIdArr = array();
        if(!empty($products)){
            foreach($products as $key=>$centralStock){
                //pr($centralStock);die;
                $categoryIdArr[] = $centralStock->category_id;
            }
        }
		$hint = $this->ScreenHint->hint('import_order_details','index');
            if(!$hint){
                $hint = "";
            }   
		if(!empty($categoryIdArr)){
            $categoryName = $this->Categories->find('list',
                                                    ['conditions'=>['Categories.id IN'=>$categoryIdArr],
                                                          [ 'keyField' => 'id',
                                                            'valueField' => 'category']
                                                    ]);
            $categoryName = $categoryName->toArray();
        }
		$this->set(compact('hint','categories','kiosks','displayType','products','categoryName','original_quantities'));
		$this -> render('index');
    }
    
    public function importOrdersList()
    {
        $status = array('0' => 'Not Received', '1' => 'Received');
        //['order' => 'DefectiveKioskReferences.id desc'],
		$importOrderReferences = $this->ImportOrderReferences->find('all');
        //pr($importOrderReferences);die;
        $importOrderReferences->hydrate(false);
        if(!empty($importOrderReferences)){
            $importOrderReferences = $importOrderReferences->toArray();
        }
		$userIds = array();
		foreach($importOrderReferences as $key => $importOrderReference){
            //pr($importOrderReference);die;
		    $userIds[$importOrderReference['user_id']] = $importOrderReference['user_id'];
		    $userIds[$importOrderReference['received_by']] = $importOrderReference['received_by'];
		}
		if(empty($userIds)){
			$userIds = array(0 => null);
		}
		
		$users = $this->Users->find('list',
                                        ['conditions' => ['Users.id IN' => $userIds],
                                         'keyField' => 'id',
                                        'valueField' => 'username'
                                    ]);
        //pr($users);die;
		$users  = $users->toArray();
        //pr($users);die;
		$this->paginate = 
                [
				    'limit' => ROWS_PER_PAGE,
					'order' => ['ImportOrderReferences.id desc']
					//'recursive' => -1
				];
		
		$importOrderReferences = $this->paginate('ImportOrderReferences');
		$importOrderReferences = $importOrderReferences->toArray();
		$hint = $this->ScreenHint->hint('import_order_details','import_orders_list');
			if(!$hint){
				$hint = "";
			}
		$this->set(compact('hint','importOrderReferences', 'users', 'status'));
    }
    
    public function searchImportedReferences()
    {
        $conditionArr = array();
		if(!empty($this->request->query['reference'])){
			$conditionArr[] = array("ImportOrderReferences.reference" => $this->request->query['reference']);
			$reference = trim(strtolower($this->request->query['reference']));
		}
		if(!empty($this->request->query['from_date']) && !empty($this->request->query['to_date'])){
			$conditionArr[] = array("date(ImportOrderReferences.created) >=" => date('Y-m-d', strtotime($this->request->query['from_date'])),
				"date(ImportOrderReferences.created) <" => date('Y-m-d', strtotime("+1 day", strtotime($this->request->query['to_date']))));
			$startDate = $this->request->query['from_date'];
			$endDate = $this->request->query['to_date'];
		}
		$status = array('0' => 'Not Received', '1' => 'Received');
		$importOrderReferences = $this->ImportOrderReferences->find('all');
        $importOrderReferences->hydrate(false);
        if(!empty($importOrderReferences)){
            $importOrderReferences = $importOrderReferences->toArray();
        }
		$userIds = array();
		foreach($importOrderReferences as $key => $importOrderReference){
            //pr($importOrderReference);die;
		    $userIds[$importOrderReference['user_id']] = $importOrderReference['user_id'];
		    $userIds[$importOrderReference['received_by']] = $importOrderReference['received_by'];
		}
		$users = $this->Users->find('list',
                                        [
                                         'conditions' => ['Users.id IN' => $userIds],
                                         'keyField' => 'id',
                                         'valueField' => 'username'
                                        ]);
		$users = $users->toArray();
		$this->paginate = [
                            'conditions' => $conditionArr,
                            'limit' => ROWS_PER_PAGE,
                            'order' => ['ImportOrderReferences.id desc']
                            //'recursive' => -1
                        ];
		//pr($this->paginate);
		$importOrderReferences = $this->paginate('ImportOrderReferences');
        $importOrderReferences = $importOrderReferences->toArray();
        //pr($importOrderReferences);die;
		$hint = $this->ScreenHint->hint('import_order_details','import_orders_list');
            if(!$hint){
                $hint = "";
            } 
		$this->set(compact('hint','importOrderReferences', 'users', 'status', 'reference', 'startDate', 'endDate'));
		$this->render('import_orders_list');
    }
    
    public function viewImportedProducts($id = '')
    {
        $referenceArr_query = $this->ImportOrderReferences->find('all', array('conditions' => array('ImportOrderReferences.id' => $id)));
        $referenceArr = $referenceArr_query->hydrate(false);
        if(!empty($referenceArr)){
            $referenceArr = $referenceArr->first();
        }
		$users_query = $this->Users->find('list',
                                        [
                                        'conditions' => ['Users.id IN' => $referenceArr['user_id']],
                                         'keyField' => 'id',
                                         'valueField' => 'username'
                                        ]);
        $users_query = $users_query->hydrate(false);
        if(!empty($users_query)){
            $users = $users_query->toArray();
        }else{
            $users = array();
        }
		$productIdList_query = $this->ImportOrderDetails->find('list',
                                                         [
                                                          'conditions' => ['ImportOrderDetails.import_order_id' => $id],                                                            'keyField' => 'id',
                                                            'valueField' => 'product_id'
                                                         ]);
        $productIdList_query = $productIdList_query->hydrate(false);
        if(!empty($productIdList_query)){
            $productIdList = $productIdList_query->toArray();
        }else{
            $productIdList = array();
        }
        if(empty($productIdList)){
            $productIdList = array(0 => null);
        }
		$productDetail_query = $this->Products->find('all', array('conditions' => array('Products.id IN' => $productIdList), 'fields' => array('id', 'product', 'product_code', 'color', 'image')));
        $productDetail_query = $productDetail_query->hydrate(false);
        if(!empty($productDetail_query)){
            $productDetail = $productDetail_query->toArray();
        }else{
            $productDetail = array();
        }
		$productArr = array();
		foreach($productDetail as $key => $products){
		    $productArr[$products['id']] = $products;
		}
		if($this->request->is('post')){
			$errorStr = '';
			//code for validating received quantities
            //pr($this->request->data);
			$error = $this->validate_received_products($this->request->data['ImportOrderDetail']);
			if(count($error)){
				$errorStr = implode('<br/>',$error);
				$this->Flash->error($errorStr);
				return $this->redirect(array('action' => 'view_imported_products', $id));
			}else{
				//pr($this->request->data);die;
				/*here we need to
				 *1. update the table import_order_details with the received_quantity
				 *2. update the status in table import_order_details to 1 (for received)
				 *3. move the unreceived quantity to bin
				 *4. move the quantity of unchecked products to bin and update the status to 1 and received quantity to 0
				 *5. update the status to 1 (for received), received_by and date_received of import_order_references
				 *
				 *change on 25.03.2016
				 *now the quantities that we are moving directly to bin will be going first to transient bin table
				 *with kiosk_id of warehouse and reference as 'refused by manufacturer'
				 */
				$saveCounter = 0;
				$importOrderDetail = $this->request->data['ImportOrderDetail'];
				$productListBin_query = $this->DefectiveBin->find('list',[
                                                                        'keyField' => 'product_id',
                                                                        'valueField' => 'quantity'
                                                                    ]
                                                            );
                $productListBin_query = $productListBin_query->hydrate(false);
                if(!empty($productListBin_query)){
                    $productListBin = $productListBin_query->toArray();
                }else{
                    $productListBin = array();
                }
				
				//added on 25.03.2016
				//checking if unreceived quantity exists for any product, if yes, we will create a reference
				//in bin transient table with reference as refused by manufacturer
				$totalUnreceivedQtt = 0;
				foreach($importOrderDetail['received_quantity'] as $key => $received_quantity){
					$orderDetailId = $importOrderDetail['id'][$key];
					$original_quantity = $importOrderDetail['original_quantity'][$key];
					$productId = $importOrderDetail['product_id'][$key];
					if(!array_key_exists($key, $importOrderDetail['received_checkbox'])){
						$received_quantity = 0;
					}
					
					$unreceivedQty = $original_quantity - $received_quantity;
					$totalUnreceivedQtt+= $unreceivedQty;
				}
				
				$binReferenceId = 0;
				//only creating reference in bin transient if total unreceived quantity is more than 0
				if($totalUnreceivedQtt > 0){
					//create reference id in reference table
					//get reference id
					$reference = "Refused by Manfacturer_".time();
					$kioskId = 10000;
					$binReferenceData = array(
							    'reference' => $reference,
							    'kiosk_id' => $kioskId,
							    'user_id' => $this->Auth->user('id'),
								     );
					$new_entity = $this->DefectiveBinReferences->newEntity($binReferenceData,['validate' => false]);
                    $patch_entity = $this->DefectiveBinReferences->patchEntity($new_entity,$binReferenceData,['validate' => false]);
					$this->DefectiveBinReferences->save($patch_entity);
					$binReferenceId = $patch_entity->id;
				}
				
				
				foreach($importOrderDetail['received_quantity'] as $key => $received_quantity){
					$orderDetailId = $importOrderDetail['id'][$key];
					$original_quantity = $importOrderDetail['original_quantity'][$key];
					$productId = $importOrderDetail['product_id'][$key];
					if(!array_key_exists($key, $importOrderDetail['received_checkbox'])){
						$received_quantity = 0;
					}
					
					$unreceived_quantity = $original_quantity - $received_quantity;
					
					$importOrderData = array(
						'id' => $orderDetailId,
						'quantity_received' => $received_quantity,
						'status' => 1
						);
					//saving the data in import order details table
					$countBinTransientSaved = 0;
					$get_id = $this->ImportOrderDetails->get($orderDetailId);
                    $patchEntity = $this->ImportOrderDetails->patchEntity($get_id,$importOrderData,['validate' => false]);
					if($this->ImportOrderDetails->save($patchEntity)){
						//**Done on 25.03.2016 for creating an entry of unreceived products in defectivebintransients
						//**Saving in bin transients from here
						//it will only be used in case of warehouse so sending kioskid = 10000
						if($unreceived_quantity > 0 && $binReferenceId > 0){
							$kioskId = 10000;
							
							$productCostlist_query = $this->Products->find('list',[
                                                                                'conditions' => ['Products.id' => $productId],
                                                                                'keyField' => 'id',
                                                                                'valueField' => 'cost_price'
                                                                            ]
                                                                    );
                            $productCostlist_query = $productCostlist_query->hydrate(false);
                            if(!empty($productCostlist_query)){
                                $productCostlist = $productCostlist_query->toArray();
                            }else{
                                $productCostlist = array();
                            }
							
							$singleProductCost = $productCostlist[$productId];
							$totalProductCost = $productCostlist[$productId]*$unreceived_quantity;
							
							
							$transientBinProductData = array(
									    'kiosk_id' => $kioskId,
									    'defective_bin_reference_id' => $binReferenceId,
									    'user_id' => $this->Auth->user('id'),
									    'product_id' => $productId,
									    'quantity' => $unreceived_quantity,
									    'total_product_cost' => $totalProductCost,
									    'single_product_cost' => $singleProductCost,
									    'status' => 1,//now we are saving the products directly to the central bin along with transient. so not saving the status 0
										      );
                            $entity_new = $this->DefectiveBinTransients->newEntity($transientBinProductData,['validate' => false]);
							$entity_patch = $this->DefectiveBinTransients->patchEntity($entity_new,$transientBinProductData,['validate' => false]);
							if($this->DefectiveBinTransients->save($entity_patch)){
							    $countBinTransientSaved++;
							}
							//**Saving in bin transients till here
						
							//move to central bin
							if(array_key_exists($productId,$productListBin)){
								//updating the quantity
								$date_time = $this->get_time();
                                $query = "UPDATE DefectiveBin SET DefectiveBin.quantity = DefectiveBin.quantity + $unreceived_quantity,DefectiveBin.modified = '$date_time' WHERE DefectiveBin.product_id = $productId";
                                $conn = ConnectionManager::get('default');
                                $stmt = $conn->execute($query); 
                                
								//$this->DefectiveBin->updateAll(array('DefectiveBin.quantity' => "DefectiveBin.quantity + $unreceived_quantity", 'DefectiveBin.modified' => "'$date_time'"), array('DefectiveBin.product_id' => $productId));
							}else{
								//insert a row
								$binData = array(
									'product_id' => $productId,
									'quantity' => $unreceived_quantity
								);
                                $id_get = $this->DefectiveBin->newEntity($binData,['validate' => false]);
								$E_patch = $this->DefectiveBin->patchEntity($id_get,$binData,['validate' => false]);
								$this->DefectiveBin->save($E_patch);
							}
						}
						$saveCounter++;
					}
				}
				
				//if data saved in transient bin, updating total cost in reference table
				if($countBinTransientSaved > 0){
					
                    $totalCostData_query = $this->DefectiveBinTransients->find('all',array('conditions' => array('DefectiveBinTransients.defective_bin_reference_id' => $binReferenceId)));
                            $totalCostData_query
                                      ->select(['totalCost' => $totalCostData_query->func()->sum('total_product_cost')]);
                            $totalCostData_query = $totalCostData_query->hydrate(false);
                            if(!empty($totalCostData_query)){
                                $totalCostData = $totalCostData_query->first();
                            }else{
                                $totalCostData = array();
                            }
                    
					$totalCostSum = $totalCostData['totalCost'];
					$dataArr = array('total_cost' => $totalCostSum);
                    $getID = $this->DefectiveBinReferences->get($binReferenceId);
                    $patch_E = $this->DefectiveBinReferences->patchEntity($getID,$dataArr,['validate' => false]);
					$this->DefectiveBinReferences->save($patch_E);
				}
				
				if($saveCounter > 0){
					//updating the ImportOrderReference table
					$date_time = $this->get_time();
					$orderReferenceData = array(
							'id' => $id,
							'status' => 1,//for received
							'received_date' => $date_time,
							'received_by' => $this->Auth->user('id')
								    );
                    $g_id = $this->ImportOrderReferences->get($id);
                    $p_entity = $this->ImportOrderReferences->patchEntity($g_id,$orderReferenceData,['validate' => false]);
					if($this->ImportOrderReferences->save($p_entity)){
						$msg = "Items have been successfully received";
					}else{
						$msg = "Items could not be received. Please try again!";
					}
				}else{
					$msg = "Items could not be received. Please try again!";
				}
				
				$this->Flash->success($msg,['escape' => false]);
				return $this->redirect(array('action' => 'import_orders_list'));
			}
		}
		$this->paginate =
                        [
                            'conditions' => ['ImportOrderDetails.import_order_id' => $id],
                            'limit' => '1000',//kept as we need to show all the products on one page
                            'order' => ['ImportOrderDetails.id desc'],
					    //'recursive' => -1
                        ];
		$importedProducts = $this->paginate('ImportOrderDetails');
		$this->set(compact('importedProducts','productArr','id','referenceArr','users'));
    }
    
    private function flash_cart( ){
				$import_basket = $this->request->Session()->read('import');
				$sessionPrdctInfo = array();
				$product_ids = array_keys($import_basket);
				$sessionProducts_query = $this->Products->find('all',array(
						'conditions'=>array('Products.id IN'=>$product_ids),
						'fields'=>array('id','product','product_code')
						)
				);
                $sessionProducts_query = $sessionProducts_query->hydrate(false);
                if(!empty($sessionProducts_query)){
                    $sessionProducts = $sessionProducts_query->toArray();
                }else{
                    $sessionProducts = array();
                }
				
				foreach($sessionProducts as $key=>$sessionProduct){
						$sessionPrdctInfo[$sessionProduct['id']] = $sessionProduct;
				}
				
				$flashTable = '';
				
				foreach($import_basket as $productID => $product_data){
						$faulty_quantity[$productID] = $product_data['quantity'];
				}
				
				foreach($faulty_quantity as $faultyId => $faultyQtt){
						$flashTable.= "<tr>
						<td>".$sessionPrdctInfo[$faultyId]['product_code']."</td>
						<td>".$sessionPrdctInfo[$faultyId]['product']."</td>
						<td>".$faultyQtt."</td>
						<tr>";
				}
				
				$sessionProductTable = '';
				if(!empty($flashTable)){
						$sessionProductTable = "<table>
						<tr>
								<th style='width: 128px;'>Product code</th>
								<th style='width: 1200px;'>Product</th>
								<th>Quantity</th>
						</tr>$flashTable
						</table>";
				}
				return $sessionProductTable;
		}
        
    public function restoreSession($currentController = '', $currentAction = '', $session_key = '', $kiosk_id = '', $redirectAction = ''){
		if(!$redirectAction){
		    $redirectAction = $currentAction;
		}
		$status = $this->SessionRestore->restore_from_session_backup_table($currentController, $currentAction, $session_key, $kiosk_id);
		if($currentAction == 'index' && $status == 'Success'){
		    //writing the reference number as well to the session
		    $status = $this->SessionRestore->restore_from_session_backup_table($currentController, $currentAction, 'session_reference', $kiosk_id);
		}
		if($status == 'Success'){
		    $msg = "Session succesfully retreived!";
		}else{
		    $msg = "Session could not be retreived!";
		}
		$this->Flash->success($msg);
		return $this->redirect(array('action' => $redirectAction));
	}
    
    public function deleteImportBasket($productId=''){
        //pr($_SESSION);
        $sessionKioskId = $_SESSION['kioskID'];
        unset($_SESSION["import"][$productId]);
        //pr($_SESSION);die;
		if(true){
			$session_basket = $this->request->Session()->read('import');
			$this->SessionRestore->update_session_backup_table($this->request->params['controller'], 'index', 'import', $session_basket, $sessionKioskId);
			if(!empty($session_basket)){
				return $this->redirect(array('action'=>'import_checkout'));
			}else{
				return $this->redirect(array('action'=>'index'));
			}
		}
	}
    
    private function validate_received_products($importOrderDetail = array()){
		$chosenMoreError = array();
		$notNumericError = array();
		$error = array();
		//pr($importOrderDetail);die;
		if(array_key_exists('received_checkbox', $importOrderDetail)){
			foreach($importOrderDetail['received_checkbox'] as $key => $product_Id){
				$original_quantity = $importOrderDetail['original_quantity'][$key];
				$received_quantity = $importOrderDetail['received_quantity'][$key];
				
				if($product_Id > 0){
					if((int)($received_quantity) && $received_quantity > $original_quantity){
						$chosenMoreError[] = $product_Id;
					}
					
					if($received_quantity == 0){continue;}
					if(!(int)($received_quantity)){
						$notNumericError[] = $product_Id;
					}
				}
			}
			
			if(count($chosenMoreError)){
				if(empty($chosenMoreError)){
                    $chosenMoreError = array(0 => null);
                }
                $chosenMoreProducts_query = $this->Products->find('list',[
                                                                        'conditions' => ['Products.id IN' => $chosenMoreError],
                                                                        'keyField' => 'id',
                                                                        'valueField' => 'product'
                                                                   ]
                                                            );
                $chosenMoreProducts_query = $chosenMoreProducts_query->hydrate(false);
                if(!empty($chosenMoreProducts_query)){
                    $chosenMoreProducts = $chosenMoreProducts_query->toArray();
                }else{
                    $chosenMoreProducts = array();
                }
				$error[] = "Please choose quantity equal to or less than the ordered quantity for product(s): ".implode(', ', $chosenMoreProducts);
			}
			
			if(count($notNumericError)){
				if(empty($notNumericError)){
                    $notNumericError = array(0 => null);
                }
                $notNumericProducts_query = $this->Products->find('list',[
                                                                        'conditions' => ['Products.id IN' => $notNumericError],
                                                                        'keyField' => 'id',
                                                                        'valueField' => 'product'
                                                                        ]
                                                                    );
                $notNumericProducts_query = $notNumericProducts_query->hydrate(false);
                if(!empty($notNumericProducts_query)){
                    $notNumericProducts = $notNumericProducts_query->toArray();
                }else{
                    $notNumericProducts = array();
                }
				$error[] = "Please choose a number(integer) as quantity for product(s): ".implode(', ', $notNumericProducts);
			}
		}else{
			$error[] = "Please choose atleast one product to receive";
		}
		
		return $error;
	}
}
?>