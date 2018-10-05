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
class DefectiveKioskProductsController extends AppController
{
     public $helpers = [
         'Paginator' => ['templates' => 'paginatortemplates']
         ];
    public function initialize()
    {
        parent::initialize();
        $this->loadComponent('ScreenHint');
        $this->loadComponent('SessionRestore');
        
        $this->loadModel('FaultyConditions');
        $this->loadModel('Products');
        $this->loadModel('Categories');
        $this->loadModel('Kiosks');
        $this->loadModel('FaultyConditions');
        $this->loadModel('DefectiveBinTransients');
        $this->loadModel('DefectiveKioskProducts');
        $this->loadModel('DefectiveKioskReferences');
        $this->loadModel('DefectiveKioskTransients');
        $this->loadModel('Users');
        $this->loadModel('DefectiveBinReferences');
        $this->loadModel('DefectiveBin');
        $this->loadModel('DefectiveCentralProducts');
		$CURRENCY_TYPE = Configure::read('CURRENCY_TYPE');
		$this->set(compact('CURRENCY_TYPE'));
    }
    public function add() {
        //$faulty_conditions = $this->FaultyConditions->find('list', array('fields' => array('id', 'faulty_condition')));
        $faulty_conditions_query = $this->FaultyConditions->find('list', [
               'keyField' => 'id',
               'valueField' => 'faulty_condition'
         ]);
        $faulty_conditions_query = $faulty_conditions_query->hydrate(false);
		if(!empty($faulty_conditions_query)){
		  $faulty_conditions = $faulty_conditions_query->toArray();
		}else{
		  $faulty_conditions = array();
		}
		
        unset($faulty_conditions['1']); //Keeping 1 as reserved remark to be sent when user refunds a faulty product in kiosk product sales in defective_kiosk_products table, so not showing in frontend dropdown, so that no one can choose it
        //pr($this->SessionBackup->find('all'));
        $session_basket = $this->request->Session()->read('ch_raw_faulty_product_basket');//change rajju 13/12/17
        
        $kioskId = $this->request->Session()->read('kiosk_id');
        if($kioskId > 0){
            $productTable_source = "kiosk_{$kioskId}_products";
        }else{
            $productTable_source = "products";
        }
            $productTable = TableRegistry::get($productTable_source,[                                                                     			'table' => $productTable_source,
                                                                    ]);
        
        
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
        $this->paginate = array(
                                            'limit' => ROWS_PER_PAGE,
                                            'conditions'=>array('NOT'=>array('quantity'=>0)),
                                            'order' => ['id desc'],
                                    );
        
        $categories = $this->CustomOptions->category_options($categories,true);
        $kiosks_query = $this->Kiosks->find('list',array(
                                                        'fields' => array('id', 'name'),
                                                        'conditions' => array('Kiosks.status' => 1),
                                                        'order' => 'Kiosks.name asc',
                                                        'recursive' => -1
                                                )
                                     );
        $kiosks_query = $kiosks_query->hydrate(false);
        if(!empty($kiosks_query)){
         $kiosks  = $kiosks_query->toArray();
        }else{
		  $kiosks = array();
		}
        if(is_array($session_basket) && !empty($session_basket)){
                $sessionPrdctInfo = array();
                $product_ids = array_keys($session_basket);
                
                $sessionProducts_query = $productTable->find('all',array('conditions'=>array('id IN'=>$product_ids),'fields'=>array('id','product','product_code'),'recursive'=>-1));
                $sessionProducts_query = $sessionProducts_query->hydrate(false);
                if(!empty($sessionProducts_query)){
                 $sessionProducts  = $sessionProducts_query->toArray();
                }else{
					$sessionProducts = array();
				}
                foreach($sessionProducts as $key=>$sessionProduct){
                        $sessionPrdctInfo[$sessionProduct['id']] = $sessionProduct;
                }
				
                $flashTable = '';
                foreach($session_basket as $productId => $productInfo){
                        $flashTable.= "<tr>
                                        <td>".$sessionPrdctInfo[$productId]['product_code']."</td>
                                        <td>".$sessionPrdctInfo[$productId]['product']."</td>
                                        <td>".$productInfo['quantity']."</td>
                                        <td>".$productInfo['price']."</td>
                                        <td>".$faulty_conditions[$productInfo['remarks']]."</td>
                                <tr>";
                }
                
                
                if(!empty($flashTable)){
                        $sessionProductTable = "<table>
                                        <tr>
												<th style='width: 128px;'>Product code</th>
												<th style='width: 600px;'>Product</th>
                                                <th>Qty</th>
                                                <th>Selling Price</th>
                                                <th>Remarks</th>
                                        </tr>$flashTable
                                        </table>";
                         $this->Flash->success($sessionProductTable, ['escape' => false]);
                         //$this->Flash->render($sessionProductTable,['element' => 'default']);            
                        //$this->Flash->success(__($sessionProductTable));
                }
        }
        
        $hint = $this->ScreenHint->hint('defective_kiosk_products','add');
        if(!$hint){
            $hint = "";
        }
        
         $centralStocks_query = $this->paginate($productTable);
         if(!empty($centralStocks_query)){
            $centralStocks = $centralStocks_query->toArray();
         }
         //pr($centralStocks);die;
        $this->set('centralStocks', $centralStocks);

        $this->set(compact('hint','categories','kiosks','displayType','faulty_conditions'));
    }
    
    function search($keyword = "",$displayCondition = ""){
        $faulty_conditions_query = $this->FaultyConditions->find('list', [
               'keyField' => 'id',
               'valueField' => 'faulty_condition'
         ]);
        
        $faulty_conditions = $faulty_conditions_query->toArray();
        unset($faulty_conditions['1']); //Keeping 1 as reserved remark to be sent when user refunds a faulty product in kiosk product sales in defective_kiosk_products table, so not showing in frontend dropdown, so that no one can choose it
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
         $categories  = $categories->toArray();
        }
        $conditionArr = array();
        $conditionArr['NOT']['quantity'] = 0;
        //----------------------
        if(!empty($search_kw)){
                $conditionArr['OR']['LOWER(product) like '] =  strtolower("%$search_kw%");
                $conditionArr['OR']['LOWER(product_code) like '] =  strtolower("%$search_kw%");
        }
        //----------------------
        if(array_key_exists('category',$this->request->query) && !empty($this->request->query['category'][0])){
                $conditionArr['category_id IN'] = $this->request->query['category'];
        }
        $kioskId = $this->request->Session()->read('kiosk_id');
        
        if($kioskId > 0){
            $productTable_source = "kiosk_{$kioskId}_products";
        }else{
            $productTable_source = "products";
        }
        
        $productTable = TableRegistry::get($productTable_source,[
                                                                 'table' => $productTable_source,
                                                                ]);     
        //pr($conditionArr);die;
        $this->paginate = [
                            'limit' => ROWS_PER_PAGE,
                            'conditions' => $conditionArr,
                            'recursive' => -1
                            ];
        
        $selectedCategoryId=array();
        //pr($conditionArr);die;
        if(array_key_exists('category_id IN',$conditionArr) && !empty($conditionArr['category_id IN'][0])){
                $selectedCategoryId=$conditionArr['category_id IN'];
        }
        $productTable->recursive = 0;
        //pr($selectedCategoryId);die;
        $categories = $this->CustomOptions->category_options($categories,true,$selectedCategoryId);
       
        $hint = $this->ScreenHint->hint('defective_kiosk_products','consolidate_faulty');
        if(!$hint){
            $hint = "";
        }
        //$a = $this->paginate($productTable);
        //pr($a);die;
        $this->set('centralStocks', $this->paginate($productTable));
        //pr($categories);die;
        $this->set(compact('hint','categories','kiosks','displayType','faulty_conditions'));
        $this -> render('add');
    }
    
    public function consolidateFaulty(){
        
        $faulty_conditions_query = $this->FaultyConditions->find('list', array(
																		 'keyField' => 'id',
																		 'valueField' => 'faulty_condition',
																		 ));
        $faulty_conditions_query = $faulty_conditions_query->hydrate(false);
        if(!empty($faulty_conditions_query)){
         $faulty_conditions  = $faulty_conditions_query->toArray();
        }else{
		  $faulty_conditions = array();
		}
		//pr($faulty_conditions);
        $kiosks_query = $this->Kiosks->find('list',array(
													   'keyField' => 'id',
													   'valueField' => 'name',
                                                        'conditions' => array('Kiosks.status' => 1),
                                                        'order' => 'Kiosks.name asc'
                                                )
                                     );
        $kiosks_query = $kiosks_query->hydrate(false);
        if(!empty($kiosks_query)){
         $kiosks  = $kiosks_query->toArray();
        }else{
		  $kiosks = array();
		}
        if($this->request->is(array('get'))){//die("inside");
		  //pr($this->request);die;
            if($this->request->session()->read('Auth.User.group_id') == ADMINISTRATORS || $this->request->session()->read('Auth.User.group_id') == MANAGERS ||
			   $this->request->session()->read('Auth.User.group_id') == SALESMAN || $this->request->session()->read('Auth.User.group_id') ==inventory_manager){
                ;
            }else{
                $this->Flash->error(__("Only Manager/Admins are allowed to access this screen"));
                return $this->redirect(array('action' => 'dashboard', 'controller' => 'home'));
            }
            if(array_key_exists('formchange', $this->request->query) && $this->request->query['formchange'] == 1){
                //deleting the session in case of change of kiosk in the dropdown
                $this->request->Session()->delete('consolidate_faulty');
                $this->request->Session()->delete('reference');
            }
            if(array_key_exists('kiosk-dropdown', $this->request->query)){
                $current_page = '';
                if(array_key_exists('page',$this->request->query)){
                    $current_page = $this->request->query['page'];
                }
                $kioskId = $this->request->query['kiosk-dropdown'];
                if(array_key_exists('basket',$this->request->query)){
                    $productArr = array();
                    $productCounts = 0;
                    $productCounts = $this->add_to_session($this->request->query, $current_page, $kioskId);
					if(!is_int($productCounts)){
						return $productCounts;
					}
                    if($productCounts > 0){
                        $sessionQuantity = $this->session_quantity();
                        $sessionProductTable = $this->flash_cart();
                        $flashMessage = "$productCounts product(s) successfully added. Total item Count:$sessionQuantity<br/>$sessionProductTable";
                        $session_basket = $this->request->Session()->read('consolidate_faulty');
                        if(is_array($session_basket) && count($session_basket)){
                            //storing the session in session_backups table
                            $this->SessionRestore->update_session_backup_table($this->request->params['controller'], $this->request->params['action'], 'consolidate_faulty', $session_basket, $kioskId);
                            $this->SessionRestore->update_session_backup_table($this->request->params['controller'], $this->request->params['action'], 'reference', $this->request->Session()->read('reference'), $kioskId);
                        }
                    }else{
                        $sessionQuantity = $this->session_quantity();
                        //$totalItems = count(array_values($this->Session->read('consolidate_faulty')));
                        $flashMessage = "No item added. Item Count:$sessionQuantity";
                    }
                    $this->Flash->error($flashMessage,array('escape' => false));
                    return $this->redirect(array('action' => "consolidate_faulty?page=$current_page&kioskDropdown=$kioskId"));
                }elseif(array_key_exists('empty_basket',$this->request->query)){
                    if($this->request->Session()->delete('consolidate_faulty')){
                        $this->request->Session()->delete('reference');
                        //deleting the session from session_backups table
                        $this->SessionRestore->delete_from_session_backup_table($this->request->params['controller'], $this->request->params['action'], 'consolidate_faulty', $kioskId);
                        $this->SessionRestore->delete_from_session_backup_table($this->request->params['controller'], $this->request->params['action'], 'reference', $kioskId);
                    }
                    
                    $flashMessage = "Basket is empty; Add New Products!";
                    $this->Flash->error($flashMessage,['escape' => false]);
                    return $this->redirect(array('action' => "consolidate_faulty/page:$current_page?kioskDropdown=$kioskId"));
                }elseif(array_key_exists('checkout',$this->request->query)){
                    return $this->redirect(array('action' => "consolidate_check_out?kioskDropdown=$kioskId"));
                }elseif(array_key_exists('bin',$this->request->query)){
                    $productArr = array();
                    $productCounts = 0;
                    $countBinTransientSaved = 0;
                    $sum_total = array();
                    if($this->request->query['bin'] != 1){
                        //we are not directing to add_to_session in this case when the request is coming from checkout page
                        $productCounts = $this->add_to_session($this->request->query, $current_page, $kioskId);
						if(!is_int($productCounts)){
							  return $productCounts;
						 }
                        $this->check_session($productCounts, $current_page, $kioskId);
                    }
                    
                    $sessionQuantity = $this->session_quantity();
                    $this->check_session($sessionQuantity, $current_page, $kioskId);
                    
                    $session_basket = $this->request->Session()->read('consolidate_faulty');
                    $reference = $this->request->Session()->read('reference');
                    
                    if(is_array($session_basket) && count($session_basket)){
                        //storing the session in session_backups table
                        $this->SessionRestore->update_session_backup_table($this->request->params['controller'], $this->request->params['action'], 'consolidate_faulty', $session_basket, $kioskId);
                        $this->SessionRestore->update_session_backup_table($this->request->params['controller'], $this->request->params['action'], 'reference', $this->request->Session()->read('reference'), $kioskId);
                    }
                    
                    $datetime = $this->get_time();
                    
                    //checking if reference already exists in defectivebinreference table
                    $res = $this->check_duplicate_reference('DefectiveBinReferences', $reference, "consolidate_faulty/page:$current_page?kioskDropdown=$kioskId");
                    if(!empty($res)){
						 return $res;die;
					}
                    //create reference id in reference table
                    //get reference id
                    $binReferenceData = array(
                                        'reference' => $reference,
                                        'kiosk_id' => $kioskId,
                                        'user_id' => $this->request->session()->read('Auth.User.id'),
                                                 );
                    $DefectiveBinReferencesEntity = $this->DefectiveBinReferences->newEntity($binReferenceData,['validate' => false]);
					$DefectiveBinReferencesEntity = $this->DefectiveBinReferences->patchEntity($DefectiveBinReferencesEntity,$binReferenceData,['validate' => false]);
                    $this->DefectiveBinReferences->save($DefectiveBinReferencesEntity);
                    $binReferenceId = $DefectiveBinReferencesEntity->id;
                    
                    $productCostlist_query = $this->Products->find('list', array('conditions' => array('Products.id IN' => array_keys($session_basket)), 'fields' => array('id', 'cost_price')));
					
                    $productCostlist_query = $productCostlist_query->hydrate(false);
                    if(!empty($productCostlist_query)){
						 $productCostlist  = $productCostlist_query->toArray();
                    }else{
						 $productCostlist = array();
					}
                    foreach($session_basket as $productID => $product_data){
                        //mark the same item in raw_faulty_product table as moved with the date_of_action added
                        foreach($product_data as $faultyId => $faultyQtt){
                            $singleProductCost = $productCostlist[$productID];
                            $totalProductCost = $productCostlist[$productID]*$faultyQtt;
          
                            $checkIfAlreadyExists_query = $this->DefectiveBinTransients->find('all', array(
											 'conditions' => array('DefectiveBinTransients.defective_bin_reference_id' => $binReferenceId,
																   'DefectiveBinTransients.product_id' => $productID,
																   'DefectiveBinTransients.kiosk_id' => $kioskId,
																   'DefectiveBinTransients.status' => 0),
											 'order' => array('DefectiveBinTransients.id desc'))
																							  );
                            $checkIfAlreadyExists_query  = $checkIfAlreadyExists_query->hydrate(false);
							if(!empty($checkIfAlreadyExists_query)){
							  $checkIfAlreadyExists  = $checkIfAlreadyExists_query->toArray();
							}else{
							  $checkIfAlreadyExists  = array();
							}
                            if(count($checkIfAlreadyExists)){
                                $transientId = $checkIfAlreadyExists['id'];
                                $date_time = $this->get_time();
                                //since modified was not getting updated in updateall, sending manually
								$query =  "UPDATE `defective_bin_transients` SET  quantity = quantity + $faultyQtt AND modified = '$date_time' AND  total_product_cost = '$totalProductCost' WHERE id = $transientId";
								$conn = ConnectionManager::get('default');
							    $stmt = $conn->execute($query); 
                                if($stmt){
								   //$this->DefectiveBinTransient->updateAll(array('DefectiveBinTransient.quantity' => "DefectiveBinTransient.quantity + $faultyQtt", 'DefectiveBinTransient.modified' => "'$date_time'", 'DefectiveBinTransient.total_product_cost' => "'$totalProductCost'"), array('DefectiveBinTransient.id' => $transientId))
                                    //$this->update_product_quantity($kioskId, $faultyQtt, $productID); adjusting at the time of kiosk marking faulty
                                    $DefectiveKioskProductsEntity = $this->DefectiveKioskProducts->get($faultyId);
									$data  = array('status' => 2,
												   'date_of_movement' => $date_time
												   );
									$DefectiveKioskProductsEntity = $this->DefectiveKioskProducts->patchEntity($DefectiveKioskProductsEntity,$data,['validate' => false]);
                                    $this->DefectiveKioskProducts->save($DefectiveKioskProductsEntity);
                                    //saving status to 2 which is for moved to bin transient
                                    //$this->DefectiveKioskProduct->saveField('date_of_movement',$date_time);
                                    $countBinTransientSaved = 1;
                                }
                            }else{
                                $transientBinProductData = array(
                                                    'kiosk_id' => $kioskId,
                                                    'defective_bin_reference_id' => $binReferenceId,
                                                    'user_id' => $this->request->session()->read('Auth.User.id'),
                                                    'product_id' => $productID,
                                                    'quantity' => $faultyQtt,
                                                    'total_product_cost' => $totalProductCost,
                                                    'single_product_cost' => $singleProductCost,
                                                    'status' => 1,//now we are saving the products directly to the central bin along with transient. so not saving the status 0
                                                              );
                         $DefectiveBinTransientEntity = $this->DefectiveBinTransients->newEntity($transientBinProductData,['validate' => false]);
						 $DefectiveBinTransientEntity = $this->DefectiveBinTransients->patchEntity($DefectiveBinTransientEntity,$transientBinProductData,['validate' => false]);
                                if($this->DefectiveBinTransients->save($DefectiveBinTransientEntity)){
                                    //$this->update_product_quantity($kioskId, $faultyQtt, $productID);adjusting at the time of kiosk marking faulty
									
                                    $transientId = $DefectiveBinTransientEntity->id;
                                    $transientData_query = $this->DefectiveBinTransients->find('all', array('conditions' => array('DefectiveBinTransients.id' => $transientId)));
									$transientData_query =  $transientData_query->hydrate(false);
									if(!empty($transientData_query)){
										$transientData = $transientData_query->first();
									}else{
										$transientData = array();
									}
                                    $transientCreated = $transientData['created'];
                                    $DefectiveKioskProductsEntity = $this->DefectiveKioskProducts->get($faultyId);
									$data = array('status' => 2,
												  'date_of_movement' => $transientCreated
												  );
									$DefectiveKioskProductsEntity = $this->DefectiveKioskProducts->patchEntity($DefectiveKioskProductsEntity,$data,['validate' => false]);
                                    $this->DefectiveKioskProducts->save($DefectiveKioskProductsEntity);
                                    //saving status to 2 which is for moved to bin transient
                                    //$this->DefectiveKioskProduct->saveField('date_of_movement',$transientCreated);
                                    $countBinTransientSaved = 1;
                                }
                            }
                            
                            if($countBinTransientSaved == 1){
                                //sending to central bin from here
                                $this->send_to_central_bin($productID, $faultyQtt);
                            }
                        }
                    }
                    
                    if($countBinTransientSaved > 0){
                        //sending to central bin from here
                        $totalCostData_query = $this->DefectiveBinTransients->find('all', array('conditions' => array('DefectiveBinTransients.defective_bin_reference_id' => $binReferenceId)));
						$totalCostData_query
											  ->select(['totalCost' => $totalCostData_query->func()->sum('total_product_cost')]);
                        $totalCostData_query = $totalCostData_query->hydrate(false);
                        if(!empty($totalCostData_query)){
                         $totalCostData  = $totalCostData_query->first();
                        }else{
						 $totalCostData = array();
						}
					
                        $totalCostSum = $totalCostData['totalCost'];
						$DefectiveBinReferenceEntity = $this->DefectiveBinReferences->get($binReferenceId);
						$data = array('total_cost' => $totalCostSum);
						$DefectiveBinReferenceEntity = $this->DefectiveBinReferences->patchEntity($DefectiveBinReferenceEntity,$data,['validate' => false]);
                        $this->DefectiveBinReferences->save($DefectiveBinReferenceEntity);
                    }
                    $sessionProductTable = $this->flash_cart();
                    $flashMessage = $countBinTransientSaved." products added to Bin under reference: $reference<br/>$sessionProductTable";
                    if($this->request->Session()->delete('consolidate_faulty')){
                        $this->request->Session()->delete('reference');
                        //deleting the session from session_backups table
                        $this->SessionRestore->delete_from_session_backup_table($this->request->params['controller'], $this->request->params['action'], 'consolidate_faulty', $kioskId);
                        $this->SessionRestore->delete_from_session_backup_table($this->request->params['controller'], $this->request->params['action'], 'reference', $kioskId);
                    }
                    $this->Flash->error($flashMessage,['escape' => false]);
                    return $this->redirect(array('action' => "consolidate_faulty?kioskDropdown=$kioskId"));
                
                }elseif(array_key_exists('Dispatch',$this->request->query)){//die("dispatch");
                    $productArr = array();
                    $productCounts = 0;
                    $countTransientSavedProduct = 0;
                    $sum_total = array();
                    if($this->request->query['Dispatch'] != 1){
                        //we are not directing to add_to_session in this case when the request is coming from checkout page
                        $productCounts = $this->add_to_session($this->request->query, $current_page, $kioskId);
						if(!is_int($productCounts)){
							  return $productCounts;
						  }
                        $this->check_session($productCounts, $current_page, $kioskId);
                    }
                    
                    $reference = $this->request->Session()->read('reference');
                  
                    $sessionQuantity = $this->session_quantity();
                    $this->check_session($sessionQuantity, $current_page, $kioskId);
                    
                    $datetime = $this->get_time();
                    
                    //checking if reference already exists in defectivebinreference table
                    $res = $this->check_duplicate_reference('DefectiveKioskReferences', $reference, "consolidate_faulty/page:$current_page?kioskDropdown=$kioskId");
                    if(!empty($res)){
						 return $res;
					die;
					}
					
                    $session_basket = $this->request->Session()->read('consolidate_faulty');
                    //create reference id in reference table
                    //get reference id
                    $faultyReferenceData = array(
                                        'reference' => $reference,
                                        'kiosk_id' => $kioskId,
                                        'user_id' => $this->request->session()->read('Auth.User.id'),
                                                 );
                    $DefectiveKioskReferencesEntity = $this->DefectiveKioskReferences->newEntity($faultyReferenceData,['validate' => false]);
                   // pr($DefectiveKioskReferencesEntity);die;
					$DefectiveKioskReferencesEntity = $this->DefectiveKioskReferences->patchEntity($DefectiveKioskReferencesEntity,$faultyReferenceData,['validate' => false]);
                   // pr($DefectiveKioskReferencesEntity);die;
                    $this->DefectiveKioskReferences->save($DefectiveKioskReferencesEntity);
                    $faultyReferenceId = $DefectiveKioskReferencesEntity->id;
                    $centralUpdated = 0;
                    //pr($session_basket);die;
                    foreach($session_basket as $productID => $product_data){
                        //mark the same item in raw_faulty_product table as moved with the date_of_action added
                        foreach($product_data as $faultyId => $faultyQtt){
                            if($kioskId == 10000){//CASE: warehouse as kiosk in dropdown
                                $transientStatus = 1;
                            }else{
                                $transientStatus = 0;
                            }
                           // $this->DefectiveKioskTransient->clear();
                            $checkIfAlreadyExists_query = $this->DefectiveKioskTransients->find('all', array('conditions' => array('DefectiveKioskTransients.defective_kiosk_reference_id' => $faultyReferenceId, 'DefectiveKioskTransients.product_id' => $productID, 'DefectiveKioskTransients.kiosk_id' => $kioskId, 'DefectiveKioskTransients.status' => $transientStatus), 'order' => array('DefectiveKioskTransients.id desc')));
                            $checkIfAlreadyExists_query = $checkIfAlreadyExists_query->hydrate(false);
							if(!empty($checkIfAlreadyExists_query)){
							  $checkIfAlreadyExists = $checkIfAlreadyExists_query->first();
							}else{
							  $checkIfAlreadyExists = array();
							}
							// pr($checkIfAlreadyExists);die;
                            if(count($checkIfAlreadyExists)){
                                $transientId = $checkIfAlreadyExists['id'];
                                $date_time = $this->get_time();
                                //since modified was not getting updated in updateall, sending manually
								$query =  "UPDATE `defective_kiosk_transients` SET  quantity = quantity + $faultyQtt,modified = '$date_time' WHERE id = $transientId";
								$conn = ConnectionManager::get('default');
							    $stmt = $conn->execute($query);
								// pr($stmt);die;
                                if(true){
                                   // echo "kk";die;
                                    //$this->update_product_quantity($kioskId, $faultyQtt, $productID); adjusting at the time of kiosk marking faulty
									//$this->DefectiveKioskTransient->updateAll(array('DefectiveKioskTransient.quantity' => "DefectiveKioskTransient.quantity + $faultyQtt", 'DefectiveKioskTransient.modified' => "'$date_time'"), array('DefectiveKioskTransient.id' => $transientId
                                    $DefectiveKioskProductsEntity = $this->DefectiveKioskProducts->get($faultyId);
                                    $date_time = $this->get_time();
									$data = array(
												  'status' => 1,
												  'date_of_movement' => $date_time,
												  'reference'=>$reference,
												  'defective_kiosk_reference_id'=>$faultyReferenceId,
												  );
									$DefectiveKioskProductsEntity = $this->DefectiveKioskProducts->patchEntity($DefectiveKioskProductsEntity,$data,['validate' => false]);
									$this->DefectiveKioskProducts->save($DefectiveKioskProductsEntity);
                                    //$this->DefectiveKioskProduct->saveField('status',1);//saving status to 1 which is for moved to transient
                                    //$this->DefectiveKioskProduct->saveField('date_of_movement',$date_time);
                                    //$this->DefectiveKioskProduct->saveField('reference',$reference);
                                    //$this->DefectiveKioskProduct->saveField('defective_kiosk_reference_id',$faultyReferenceId);
                                    $countTransientSavedProduct++;
                                }
                            }else{
                                $transientFaultyProductData = array(
                                                    'kiosk_id' => $kioskId,
                                                    'defective_kiosk_reference_id' => $faultyReferenceId,
                                                    'user_id' => $this->request->session()->read('Auth.User.id'),
                                                    'product_id' => $productID,
                                                    'quantity' => $faultyQtt,
                                                    'status' => $transientStatus//here we need to send 1 for warehouse, 0 is for transient
                                                              );
               $DefectiveKioskTransientsEntity = $this->DefectiveKioskTransients->newEntity($transientFaultyProductData,['validate' => false]);
			   $DefectiveKioskTransientsEntity = $this->DefectiveKioskTransients->patchEntity($DefectiveKioskTransientsEntity,$transientFaultyProductData,['validate' => false]);
                                if($this->DefectiveKioskTransients->save($DefectiveKioskTransientsEntity)){
                                    //$this->update_product_quantity($kioskId, $faultyQtt, $productID);
                                    $transientId = $DefectiveKioskTransientsEntity->id;
                                    $transientData_query = $this->DefectiveKioskTransients->find('all', array('conditions' => array('DefectiveKioskTransients.id' => $transientId)));
									$transientData_query = $transientData_query->hydrate(false);
									if(!empty($transientData_query)){
										$transientData = $transientData_query->first();
									}else{
										$transientData = array();
									}
									
                                    $transientCreated = $transientData['created'];
                                    $DefectiveKioskProductsEntity = $this->DefectiveKioskProducts->get($faultyId);
									$data = array(
												  'status' => 1,
												  'date_of_movement' => $transientCreated,
												  'reference' => $reference,
												  'defective_kiosk_reference_id' => $faultyReferenceId,
												  );
			   $DefectiveKioskProductsEntity = $this->DefectiveKioskProducts->patchEntity($DefectiveKioskProductsEntity,$data,['validate' => false]);
               $this->DefectiveKioskProducts->save($DefectiveKioskProductsEntity);
                                    //$this->DefectiveKioskProducts->saveField('status',1);//saving status to 1 which is for moved to transient
                                    //$this->DefectiveKioskProducts->saveField('date_of_movement',$transientCreated);
                                    //$this->DefectiveKioskProducts->saveField('reference',$reference);
                                    //$this->DefectiveKioskProducts->saveField('defective_kiosk_reference_id',$faultyReferenceId);
                                    $countTransientSavedProduct++;
                                }
                            }
                            
                            if($countTransientSavedProduct > 0 && $transientStatus == 1){
                                //CASE:Warehouse, sending products to central table at the same time of moving to transient
                                $countAdded = $this->update_central_table($productID, $faultyQtt);
                                $centralUpdated+=$countAdded;
                            }
                        }
                    }
                    
                    if($centralUpdated > 0){
                         $DefectiveKioskReferences = $this->DefectiveKioskReferences->get($faultyReferenceId);
                        // echo $datetime;die;
                         $datetime = $this->get_time();
                         $data = array(
                                    'status'=> 1,
                                    'date_of_receiving'=> $datetime,
                                    'received_by' => $this->request->session()->read('Auth.User.id')
                         );
                        $DefectiveKioskReferencespatchEntity = $this->DefectiveKioskReferences->patchEntity($DefectiveKioskReferences,$data,['validate' => false]);
                       
                        $this->DefectiveKioskReferences->save($DefectiveKioskReferencespatchEntity);
                  
                        //$this->DefectiveKioskReferences->saveField('status', 1);
                        //$this->DefectiveKioskReferences->saveField('date_of_receiving', $datetime);
                        //$this->DefectiveKioskReferences->saveField('received_by', $this->Auth->user('id'));
                    }
                    
                    //$sessionQuantity = $this->session_quantity();
                    $sessionProductTable = $this->flash_cart();
                    $flashMessage = $countTransientSavedProduct." products moved to transient faulty under reference: $reference<br/>$sessionProductTable";
                    if($this->request->Session()->delete('consolidate_faulty')){
                        $this->request->Session()->delete('reference');
                        //deleting the session from session_backups table
                        $this->SessionRestore->delete_from_session_backup_table($this->request->params['controller'], $this->request->params['action'], 'consolidate_faulty', $kioskId);
                        $this->SessionRestore->delete_from_session_backup_table($this->request->params['controller'], $this->request->params['action'], 'reference', $kioskId);
                    }
                    $this->Flash->success($flashMessage,['escape' => false]);
                    return $this->redirect(array('action' => "consolidate_faulty?kioskDropdown=$kioskId"));
                }
            }else{
                $kioskId = current(array_keys($kiosks));
            }
        }else{
            $kioskId = current(array_keys($kiosks));
        }
      //pr($kiosks);die;
 
        //$faultyProductIds = $this->DefectiveKioskProducts->find('list',
        //                                                        array('fields' => array('id', 'product_id'),
        //                                                              'conditions' => array('DefectiveKioskProducts.kiosk_id' => $kioskId,
        //                                                                                    'DefectiveKioskProducts.status' => 0)));
	//   if( $this->request->session()->read('Auth.User.group_id')== MANAGERS){
	//		$managerKiosk = $this->managerLogin();//pr($managerKiosk);die;
	//		if(!empty($managerKiosk)){
	//			$kioskId = key($managerKiosk);
	//		}
	//   }
         $faultyProductIds_query = $this->DefectiveKioskProducts->find('list', [
                                                                 'keyField' => 'id',
                                                                 'valueField' =>'product_id',
                                                                 'conditions' => ['DefectiveKioskProducts.kiosk_id' => $kioskId, 'DefectiveKioskProducts.status' => 0]
                                                               ]);
        //pr($faultyProductIds_query);die;
        $faultyProductIds_query = $faultyProductIds_query->hydrate(false);
        if(!empty($faultyProductIds_query)){
         $faultyProductIds  = $faultyProductIds_query->toArray();
        }else{
		  $faultyProductIds = array();
		}
        $userList_query = $this->DefectiveKioskProducts->find('list', [
                                                                 'keyField' => 'id',
                                                                 'valueField' =>'user_id',
                                                                 'conditions' => ['DefectiveKioskProducts.kiosk_id' => $kioskId, 'DefectiveKioskProducts.status' => 0]
                                                               ]);
		$userList_query = $userList_query->hydrate(false);
		if(!empty($userList_query)){
			   $userList = $userList_query->toArray();
		}else{
		  $userList = array();
		}
        
        if(empty($userList)){
		  $userList = array(0 => null);
		}
        $users_query = $this->Users->find('list', [
                                             'keyField' => 'id',
                                             'valueField' => 'username',
                                             'conditions' => [
                                                              'Users.id IN' => $userList]
                                             ]);
      
        $users_query = $users_query->hydrate(false);
        if(!empty($users_query)){
         $users  = $users_query->toArray();
        }else{
		  $users  = array();
		}
        //  pr($users);die;
        $session_basket = $this->request->Session()->read('consolidate_faulty_basket');
        //echo $kioskId;die;
        if($kioskId != 10000){
            $productTable_source = "kiosk_{$kioskId}_products";
        }else{
            $productTable_source = "products";
        }
        
        $productTable = TableRegistry::get($productTable_source,[
                                                                 'table' => $productTable_source,
                                                                    ]);
        if(empty($faultyProductIds)){
		  $faultyProductIds  = array(0 => null);
		}
       $product_query = $productTable->find('all',
                                           ['conditions' => ['id IN' => $faultyProductIds]]);
        //$productData = $productTable->find('all',
        //                                   array('conditions' => array($productTable_source.'.id IN' => $faultyProductIds)));
       
        //$productData->hydrate(false);
       
        $product_query = $product_query->hydrate(false);
        if(!empty($product_query)){
            $productData  = $product_query->toArray();
        }else{
		  $productData  = array();
		}
        $productArray = array();
        foreach($productData as $pd => $products){
            $productArray[$products['id']] = $products;
        }
        //$productquery->hydrate(false);
        //if(!empty($productquery)){
        // $productArray  =  $productquery->toArray();
        //}
        //pr($productArray);
        
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
//		if( $this->request->session()->read('Auth.User.group_id')== MANAGERS){         
//			$managerKiosk = $this->managerLogin();//pr($managerKiosk);die;
//			if(!empty($managerKiosk)){
//				$kiosk_Id = key($managerKiosk);
//				$this->paginate =  array(
//                                            'limit' => ROWS_PER_PAGE,
//                                            'conditions'=>array('DefectiveKioskProducts.kiosk_id' => $kiosk_Id,
//                                                                'DefectiveKioskProducts.status' => 0),
//                                            'order' => ['DefectiveKioskProducts.id desc']
//                                    );
//			}
//		}else{
			$this->paginate =  array(
                                            'limit' => ROWS_PER_PAGE,
                                            'conditions'=>array('DefectiveKioskProducts.kiosk_id' => $kioskId,
                                                                'DefectiveKioskProducts.status' => 0),
                                            'order' => ['DefectiveKioskProducts.id desc']
                                    );
		//}
          
        
        $categories = $this->CustomOptions->category_options($categories,true);
        $kiosks_query = $this->Kiosks->find('list',array(
                                                        'fields' => array('id', 'name'),
                                                        'conditions' => array('Kiosks.status' => 1),
                                                        'order' => 'Kiosks.name asc'
                                                )
                                     );
        $kiosks_query = $kiosks_query->hydrate(false);
        if(!empty($kiosks_query)){
         $kiosks  = $kiosks_query->toArray();
        }else{
		   $kiosks  = array();
		}
        if(is_array($session_basket)){
            $this->flash_cart();
        }
        
         $hint = $this->ScreenHint->hint('defective_kiosk_products','consolidate_faulty');
            if(!$hint){
                $hint = "";
            }
			$rawFaultyProduct_query = $this->paginate("DefectiveKioskProducts");
			$rawFaultyProduct  = $rawFaultyProduct_query->toArray();
			//pr($rawFaultyProduct);die;
              $this->set('rawFaultyProduct', $rawFaultyProduct);  
        $this->set(compact('rawFaultyProduct','hint','categories','kiosks','displayType', 'productArray', 'users', 'kioskId', 'faulty_conditions'));
	}
    
    function addRawData(){
     //pr($this->request->params['']);die;
          $conn = ConnectionManager::get('default');
          $stmt = $conn->execute('SELECT NOW() as created');
          $currentTimeInfo = $stmt ->fetchAll('assoc');
          $currentTime = $currentTimeInfo[0]['created'];
          if(array_key_exists('kiosk_id', $this->request->Session()->read())){
              $kioskId = $this->request->Session()->read('kiosk_id');
          }else{
              $kioskId = '';
          }
          $current_page = '';
          if(array_key_exists('current_page',$this->request['data'])){
                  $current_page = $this->request['data']['current_page'];
          }
          if(!isset($current_page)){$this->redirect(array('action' => 'add'));}
        
          $productCounts = 0;
          if(array_key_exists('basket',$this->request['data'])){
               
                $productArr = array();
                $remarkEmptyProductIds = array();
                $errorProductIds = array();
                $notNumberProductIds = array();
                $errorArr = array();
                $errorStr = '';
                //pr($this->request);die;
                //if(AuthComponent::user('group_id') == ADMINISTRATORS || AuthComponent::user('group_id') == MANAGERS){
                    //check for admin/manager to choose the product quantity less than the current quantity
                    foreach($this->request['data']['DefectiveKioskProduct']['quantity'] as $key => $quantity){
                        if((int)$quantity && $this->request['data']['DefectiveKioskProduct']['p_quantity'][$key] < $quantity){
                            $errorProductIds[] = $this->request['data']['DefectiveKioskProduct']['product_id'][$key];
                        }
                        
                        if(!empty($quantity) && !(int)$quantity){
                            $notNumberProductIds[] = $this->request['data']['DefectiveKioskProduct']['product_id'][$key];
                        }
                        
                        if($quantity > 0 && empty($this->request['data']['DefectiveKioskProduct']['remarks'][$key])){
                            $remarkEmptyProductIds[] = $this->request['data']['DefectiveKioskProduct']['product_id'][$key];
                        }
                    }
                //}
               
                if(count($errorProductIds)){
                    $productsName_query = $this->Products->find('list',[
                                                                      'conditions' => ['Products.id IN' => $errorProductIds],
                                                                      'keyField' =>  'id',
                                                                      'valueField' => 'product'
                                                                 ]
                                                         );
                    if(!empty($productsName_query)){
                         $productsName = $productsName_query->toArray();
                    }
                    $errorArr[] = "Please choose quantity less than the current quantity for ".implode(', ',$productsName);
                }
               
                if(count($notNumberProductIds)){
                   $notNumberProductsName_query =  $this->Products->find('list',[
                                                                      'conditions' => ['Products.id IN' => $notNumberProductIds],
                                                                      'keyField' =>  'id',
                                                                      'valueField' => 'product'
                                                                 ]
                                                         );
                   if(!empty($notNumberProductsName_query)){
                    $notNumberProductsName = $notNumberProductsName_query->toArray();
                   }
                    $errorArr[] = "Please choose an integer as quantity for ".implode(', ',$notNumberProductsName);
                }
                if(count($remarkEmptyProductIds)){
                    $remarkProductsName_query =  $this->Products->find('list',[
                                                                      'conditions' => ['Products.id IN' => $remarkEmptyProductIds],
                                                                      'keyField' =>  'id',
                                                                      'valueField' => 'product'
                                                                 ]
                                                         );
                   if(!empty($remarkProductsName_query)){
                    $remarkProductsName = $remarkProductsName_query->toArray();
                   }
                    
                    $errorArr[] = "Please choose remarks for ".implode(', ',$remarkProductsName);
                }
                
                if(count($errorArr)){
                    $errorStr = implode('<br/>',$errorArr);
                    $this->Flash->error($errorStr);
                    return $this->redirect(array('action' => "add/page:$current_page"));
                    die;
                }
              
                //foreach($this->request['data']['DefectiveKioskProduct']['product_id'] as $ki => $ide){
                //	$currentPrice = $this->request['data']['DefectiveKioskProduct']['current_price'][$ki];
                //	$currentPriceArr[$ide] = $currentPrice;
                //}
                
                $currentQuantity = $productID = $price = '';
                foreach($this->request['data']['DefectiveKioskProduct']['quantity'] as $key => $quantity){
                        $currentQuantity = $this->request['data']['DefectiveKioskProduct']['p_quantity'][$key];
                        $productID = $this->request['data']['DefectiveKioskProduct']['product_id'][$key];
                        
                        
                        $price = $this->request['data']['DefectiveKioskProduct']['current_price'][$key];
                        $remarks = $this->request['data']['DefectiveKioskProduct']['remarks'][$key];
                        
                        if($quantity > 0 && $quantity <= $currentQuantity){
                                $productArr[$productID] = array(
                                                                'quantity' => $quantity,
                                                                'price' => $price,
                                                                'remarks' => $remarks
                                                                );
                                $productCounts++;
                        }				
                }
                $session_basket = $this->request->Session()->read('ch_raw_faulty_product_basket');//change rajju 13/12/17
                if(count($session_basket) >= 1){
                        //adding item to the the existing session
                        $sum_total = $this->add_arrays(array($productArr,$session_basket));
                        $this->request->Session()->write('ch_raw_faulty_product_basket', $sum_total);//change rajju 13/12/17
                       //  $this->request->Session()->write('raw_faulty_product_basket', $sum_total);
                        $session_basket = $this->request->Session()->read('ch_raw_faulty_product_basket');//change rajju 13/12/17
                }else{
                        //adding item first time to session
                        if(count($productCounts))$this->request->Session()->write('ch_raw_faulty_product_basket', $productArr);//change rajju 13/12/17
                }
                $session_basket = $this->request->Session()->read('ch_raw_faulty_product_basket');//change rajju 13/12/17
                if(is_array($session_basket) && count($session_basket)){
                    //storing the session in session_backups table
                    $this->SessionRestore->update_session_backup_table($this->request->params['controller'], 'add', 'ch_raw_faulty_product_basket', $session_basket, $kioskId);//change rajju 13/12/17
                }            
                $totalItems = count($this->request->Session()->read('ch_raw_faulty_product_basket'));//change rajju 13/12/17
                
                if($productCounts){
                        $flashMessage = "$productCounts product(s) successfully added. Total item Count:$totalItems";
                }else{
                        $flashMessage = "No item added. Item Count:$productCounts";
                }
                $this->Flash->success($flashMessage);
                return $this->redirect(array('action' => "add/page:$current_page"));
        }elseif(array_key_exists('empty_basket',$this->request['data'])){
                if($this->request->Session()->delete('ch_raw_faulty_product_basket')){//change rajju 13/12/17
                    //deleting the session from session_backups table
                    $this->SessionRestore->delete_from_session_backup_table($this->request->params['controller'], 'add', 'ch_raw_faulty_product_basket', $kioskId);//change rajju 13/12/17
                }
                $this->SessionRestore->delete_from_session_backup_table($this->request->params['controller'], 'add', 'ch_raw_faulty_product_basket', $kioskId);//change rajju 13/12/17
                $flashMessage = "Basket is empty; Add Fresh Products!";
                $this->Flash->success($flashMessage);
                return $this->redirect(array('action' => "add/page:$current_page"));			
        }elseif(array_key_exists('checkout',$this->request['data'])){
                $session_basket = $this->request->Session()->read('ch_raw_faulty_product_basket');//change rajju 13/12/17
                if(!empty($session_basket)){
                        return $this->redirect(array('action'=>'checkout_add_raw'));
                }else{
                        $this->Flash->error('Please add items to the basket');
                        return $this->redirect(array('action' => "add/page:$current_page"));
                }
        }else{
                $kioskId = $this->request->Session()->read('kiosk_id');
                $productArr = array();
                $remarkEmptyProductIds = array();
                $errorProductIds = array();
                $productsStr = '';
                $emptyRemarkStr = '';
                if(empty($kioskId) && $this->request->session()->read('Auth.User.group_id') == KIOSK_USERS){
                    $flashMessage = "Failed to add products. <br />Please login from your kiosk website!";
                    $this->Flash->error($flashMessage,['escape' => false]);
                    return $this->redirect(array('action' => "add/page:$current_page"));
                    die;
                }elseif(empty($kioskId) && ($this->request->session()->read('Auth.User.group_id') == ADMINISTRATORS || $this->request->session()->read('Auth.User.group_id') == MANAGERS ||  $this->request->session()->read('Auth.User.group_id') == SALESMAN)){
                    $kioskId = 10000;
                }
                //foreach($this->request['data']['DefectiveKioskProduct']['product_id'] as $ki => $ide){
                //	$currentPrice = $this->request['data']['DefectiveKioskProduct']['current_price'][$ki];
                //	$currentPriceArr[$ide] = $currentPrice;
                //}
                
                if(array_key_exists('DefectiveKioskProduct',$this->request['data'])){
                    $productArr = array();
                    $remarkEmptyProductIds = array();
                    $errorProductIds = array();
                    $errorArr = array();
                    $errorStr = '';
                    if($this->request->session()->read('Auth.User.group_id') == ADMINISTRATORS || $this->request->session()->read('Auth.User.group_id') == MANAGERS){
                    //check for admin/manager to choose the product quantity less than the current quantity
                        foreach($this->request['data']['DefectiveKioskProduct']['quantity'] as $key => $quantity){
                            if($this->request['data']['DefectiveKioskProduct']['p_quantity'][$key] < $quantity){
                                $errorProductIds[] = $this->request['data']['DefectiveKioskProduct']['product_id'][$key];
                            }
                        }
                    }
                    
                    if(count($errorProductIds)){
                        $productsName_query = $this->Products->find('list',[
                                                                      'conditions' => ['Products.id IN' => $errorProductIds],
                                                                      'keyField' => 'id',
                                                                      'valueField' => 'product',
                                                                      
                                                                 ]
                                                             );
                        $productsName_query = $productsName_query->hydrate(false);
                        if(!empty($productsName_query)){
                         $productsName = $productsName_query->toArray();
                        }
                        $errorArr[] = "Please choose quantity less than the current quantity for ".implode(', ',$productsName);
                    }
                    
                    foreach($this->request['data']['DefectiveKioskProduct']['quantity'] as $key => $quantity){
                        if($quantity > 0 && empty($this->request['data']['DefectiveKioskProduct']['remarks'][$key])){
                            $remarkEmptyProductIds[] = $this->request['data']['DefectiveKioskProduct']['product_id'][$key];
                        }
                    }
                    
                    if(count($remarkEmptyProductIds)){
                        $remarkProductsName_query = $this->Products->find('list',[
                                                                           'conditions' => ['Products.id In' => $remarkEmptyProductIds],
                                                                           'keyField' => 'id',
                                                                           'valueField' => 'product',
                                                                           ]
                                                                   );
                        if(!empty($remarkProductsName_query)){
                              $remarkProductsName = $remarkProductsName_query->toArray();
                        }
                        $errorArr[] = "Please choose remarks for ".implode(', ',$remarkProductsName);
                    }
                    
                    if(count($errorArr)){
                        $errorStr = implode('<br/>',$errorArr);
                        $this->Flash->error($errorStr, ['escape' => false]);
                        return $this->redirect(array('action' => "add/page:$current_page"));
                        die;
                    }
                        
                        $currentQuantity = $productID = $price = '';
                        foreach($this->request['data']['DefectiveKioskProduct']['quantity'] as $key => $quantity){
                                $currentQuantity = $this->request['data']['DefectiveKioskProduct']['p_quantity'][$key];
                                $productID = $this->request['data']['DefectiveKioskProduct']['product_id'][$key];
                                $price = $this->request['data']['DefectiveKioskProduct']['current_price'][$key];
                                $remarks = $this->request['data']['DefectiveKioskProduct']['remarks'][$key];
                                
                                if($quantity > 0 && $quantity <= $currentQuantity){
                                        $productArr[$productID] = array(
                                                                        'quantity' => $quantity,
                                                                        'price' => $price,
                                                                        'remarks' => $remarks
                                                                        );
                                        $productCounts++;
                                }
                        }
                        /*if(empty($productCounts)){
                                $flashMessage = "Failed to transfer stock. <br />Please select quantity atleast for one product!";
                                $this->Session->setFlash($flashMessage);
                                $this->redirect(array('action' => "index/page:$current_page"));
                                die;
                        }*/
                        $session_basket = $this->request->Session()->read('ch_raw_faulty_product_basket');//change rajju 13/12/17
                        $sum_total = $this->add_arrays(array($productArr,$session_basket));
                        $this->request->Session()->write('ch_raw_faulty_product_basket',$sum_total);//change rajju 13/12/17
                        
                        if(count($sum_total) == 0){
                                $flashMessage = "Failed to add products. <br />Please select quantity atleast for one product!";
                                $this->Flash->error($flashMessage,['escape' => false]);
                                return $this->redirect(array('action' => "add/page:$current_page"));
                                die;
                        }
                }
                        
                $datetime = $this->get_time();            
                $session_basket = $this->request->Session()->read('ch_raw_faulty_product_basket');//change rajju 13/12/17
    
                $product_Ids = array_keys($session_basket);
                $cost_price_result_query = $this->Products->find('list',
                                                           [
                                                            'conditions' => ['Products.id IN' => $product_Ids],
                                                            'keyField' => 'id',
                                                            'valueField' => 'cost_price'
                                                           ]);
                if(!empty($cost_price_result_query)){
                    $cost_price_result = $cost_price_result_query->toArray();
                }
                foreach($session_basket as $productID => $productData){
                        //continue;
                         $DefectiveKioskProducts = $this->DefectiveKioskProducts->newEntity();
                        $price = $productData['price'];
                        $quantity = $productData['quantity'];
                        $remarks = $productData['remarks'];
                        
                        $rawFaultyProductData = array(
                                                'product_id' => $productID,
                                                'quantity' => $quantity,
                                                'kiosk_id' => $kioskId,
                                                'user_id' => $this->request->session()->read('Auth.User.id'),
                                                'status' => 0, //not moved to central_faulty_products table
                                                'cost_price' => $cost_price_result[$productID],
                                                'remarks' => $remarks
                                                );
                        
                        $DefectiveKioskProducts = $this->DefectiveKioskProducts->patchEntity($DefectiveKioskProducts, $rawFaultyProductData);
						//pr($DefectiveKioskProducts);die;
                        if($this->DefectiveKioskProducts->save($DefectiveKioskProducts)){
                                //decreasing central stock
                                //$this->Product->recursive = -1;
                                //$this->Product->id = $productID;
                                $this->update_product_quantity($kioskId, $quantity, $productID);
                                /*$this->Product->read(null, $productID);
                                $this->Product->set(array(
                                        'Product.quantity' => "Product.quantity - $quantity"
                                ));*/
                                //SET SOURCE FOR PRODUCT TABLE
                                /*Quantity will be adjusted once the products are consolidated
                                 *if($kioskId == 10000){
                                    $productTable = "products";
                                }else{
                                    $productTable = "kiosk_{$kioskId}_products";
                                }
                                
                                $productData = array('quantity' => "Product.quantity - $quantity");
                                $this->Product->query("UPDATE `$productTable` SET `quantity` = `quantity` - $quantity WHERE `id` = $productID");
                                //--------code for reading cake query---
                                $dbo = $this->Product->getDatasource();
                                $logData = $dbo->getLog();
                                $getLog = end($logData['log']);
                                //echo $getLog['query'];
                                //--------code for reading cake query---
                                //die;*/
                        }else{
                                //failed to create order
                                /*$this->KioskOrder->id = $kiosk_order_id;
                                if ($this->KioskOrder->exists()) {
                                        $this->KioskOrder->delete();
                                }*/
                        }
                }
                if($this->request->Session()->delete('ch_raw_faulty_product_basket')){//change rajju 13/12/17
                    //deleting the session from session_backups table
                    //$this->SessionRestore->delete_from_session_backup_table($this->params['controller'], 'add', 'raw_faulty_product_basket', $kioskId);
                }
                $flashMessage = count($session_basket)." products marked as faulty";
                $this->Flash->success($flashMessage,['escape' => false]);
        }		
        return $this->redirect(array('action' => "add/page:$current_page"));
    }
    
    private function get_time(){
          $conn = ConnectionManager::get('default');
          $stmt = $conn->execute('SELECT NOW() as created');
          $currentTimeInfo = $stmt ->fetchAll('assoc');
          $currentTime = $currentTimeInfo[0]['created'];
        $date_time = date('Y-m-d H:i:s',strtotime($currentTime));
        return $date_time;
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
    
    public function checkoutAddRaw(){
        $session_basket = $this->request->Session()->read('ch_raw_faulty_product_basket');//change rajju 13/12/17
        $faulty_conditions_query = $this->FaultyConditions->find('list',[
                                                                 'keyField' => 'id',
                                                                 'valueField' =>  'faulty_condition',
                                                            ]
                                                          );
        if(!empty($faulty_conditions_query)){
          $faulty_conditions = $faulty_conditions_query->toArray();
        }
        if(is_array($session_basket)){
            $product_ids = array_keys($session_basket);
            $productCodeArr = array();
            if(array_key_exists('kiosk_id', $this->request->Session()->read())){
                $kiosk_id = $this->request->Session()->read('kiosk_id');
            }else{
                $kiosk_id = '';
            }
            
            if(!empty($kiosk_id)){
                $productSource = "kiosk_{$kiosk_id}_products";
                //$this->Product->setSource($productSource);
            }else{
                $productSource = "products";
                //$this->Product->setSource($productSource);
            }
            
             $productTable = TableRegistry::get($productSource,[                                                                                 'table' => $productSource,
                                                                    ]);
            
            $productCodeArr_query = $productTable->find('all',array('conditions' => array('id IN' => $product_ids),
                                                                                                              'fields' => array('id','product_code','quantity'),
                                                                                                              'recursive' => -1
                                                                                                              )
                                                                                      );
            $productCodeArr_query = $productCodeArr_query->hydrate(false);
            if(!empty($productCodeArr_query)){
               $productCodeArr = $productCodeArr_query->toArray();
            }
            foreach($session_basket as $key => $basketItem){
            if($key == 'error')continue;
            //$productCodeArr[] = $this->Product->find('first',array('conditions'=>array('Product.id'=>$key),'fields'=>array('id','product_code'),'recursive'=>-1));
            }
            $productCode = array();
            if(!empty($productCodeArr)){
                    foreach($productCodeArr as $k=>$productCodeData){
                            //echo $productCode[$productCodeData['Product']['id']]=$productCodeData['Product']['product_code'];
                            
                       $productIds[$productCodeData['id']] = $productCodeData['product_code'];
                             
                               $productCodes[$productCodeData['product_code']] = $productCodeData['quantity'];
                    
                    }
            }
            $productNameArr = array();
            foreach($session_basket as $productId=>$productDetails){
                    $productNameArr_query = $productTable->find('all',array('conditions'=>array('id IN'=>$productId),
                                                                           'fields'=>array('id','product'),'recursive'=>-1)
                                                             );
                    $productNameArr_query = $productNameArr_query->hydrate(false);
                    if(!empty($productNameArr_query)){
                         $productNameArr[] = $productNameArr_query->first();
                         //$productNameArr[] = $productNameArr_query->toArray();
                    }
            }
            foreach($productNameArr as $key=>$selectedProducts){
                    $productArr[$selectedProducts['id']] = $selectedProducts['product'];
            }
            if($this->request->is('post')){
              // pr($this->request);die;
                    $error = array();
                    if(array_key_exists('update_quantity',$this->request->data)){
                        $productCodeName_query = $productTable->find('list',[
                                                                           'conditions' => ['id IN' => $product_ids],
                                                                           'keyField' => 'product_code',
                                                                           'valueField' => 'product',
                                                                      ]
                                                               );
                        if(!empty($productCodeName_query)){
                         $productCodeName = $productCodeName_query->toArray();
                        }
                        $lessProducts = array();
                        $lowProducts = array();
                        foreach($this->request->data['CheckOut'] as $productCode => $quantity){
                                if($quantity == 0 || !(int)$quantity){
                                                $lowProducts[] = $productCodeName[$productCode];
                                }
                                $availableQty = $productCodes[$productCode];
                                if($quantity > $availableQty){
                                        $lessProducts[] = $productCodeName[$productCode];
                                }
                        }
                        if(count($lessProducts) >= 1){
                                $this->Session->setFlash("Please choose quantity less than or equal to available stock for ".implode(",",$lessProducts));
                                return $this->redirect(array('action'=>'checkout_add_raw'));
                        }
                        if(count($lowProducts) > 0){
                                 $this->Session->setFlash("Please choose quantity more than 0 for product : ".implode(",",$lowProducts) );
                                return $this->redirect(array('action'=>'checkout_add_raw'));
                        } else{
                                $requestedQuantity = $this->request->data['CheckOut'];
                                $newArray = array();
                         //pr($session_basket);
                                $counter = 0;
                                $requestedQuantity = array_values($requestedQuantity);
                                foreach($session_basket as $productCode=>$productData){
                                        $qty = "";
                                        if(array_key_exists($counter,$requestedQuantity)){
                                                 $qty =  $requestedQuantity[$counter];
                                        }
                                        
                                        $newArray[$productCode] = array(
                                                                        'quantity' => $qty,
                                                                        'price' => $productData['price'],
                                                                        'remarks' => $productData['remarks']
                                                                                                );
                                        $counter++;
                                }
                                $this->request->Session()->delete('ch_raw_faulty_product_basket');//change rajju 13/12/17
                                if($this->request->Session()->write('ch_raw_faulty_product_basket',$newArray)){//change rajju 13/12/17
                                    //$this->SessionRestore->update_session_backup_table($this->params['controller'], 'add', 'raw_faulty_product_basket', $newArray, $kiosk_id);
                                }
                                $this->Flash->success("Quantity has been basket successfully updated");
                                return $this->redirect(array('action'=>'checkout_add_raw'));
                        }
                    }elseif(array_key_exists('edit_basket',$this->request->data)){
                            return $this->redirect(array('action'=>"add"));
                    }
            }
            $this->set(compact('productArr','productCode','productIds', 'faulty_conditions'));
        }
    }
    
     public function deleteProductFromSession($productId=''){
        if(array_key_exists('kiosk_id', $this->request->Session()->read())){
            $kioskId = $this->request->Session()->read('kiosk_id');
        }else{
            $kioskId = '';
        }
        
        unset($_SESSION['ch_raw_faulty_product_basket'][$productId]);       //change rajju 13/12/17                                //$this->request->Session()->delete("raw_faulty_product_basket.$productId")
                $session_basket = $this->request->Session()->read('ch_raw_faulty_product_basket');//change rajju 13/12/17
                //$this->SessionRestore->update_session_backup_table($this->params['controller'], 'add', 'raw_faulty_product_basket', $session_basket, $kioskId);
                if(!empty($session_basket)){
                        return $this->redirect(array('action'=>'checkout_add_raw'));
                }else{
                        return $this->redirect(array('action'=>'add'));
                }
        
    }
    
    private function update_product_quantity($kioskId = '', $faultyQtt = '', $productID = ''){
        if($kioskId != 10000){
            $productTable_source = "kiosk_{$kioskId}_products";
        }else{
            $productTable_source = "products";
        }
        $productTable = TableRegistry::get($productTable_source,[
                                                                 'table' => $productTable_source,
                                                                    ]);
		$query = "UPDATE $productTable_source SET quantity = quantity - $faultyQtt WHERE id = $productID";
		$conn = ConnectionManager::get('default');
	    $stmt = $conn->execute($query); 
        //$productTable->updateAll(array('quantity' => "quantity - $faultyQtt"), array("id" => $productID));
    }
    
    public function restoreSession($currentController = '', $currentAction = '', $session_key = '', $kiosk_id = '', $redirectAction = ''){
        if(!$redirectAction){
            $redirectAction = $currentAction;
        }
        $status = $this->SessionRestore->restore_from_session_backup_table($currentController, $currentAction, $session_key, $kiosk_id);
        if($currentAction == 'consolidate_faulty' && $status == 'Success'){
            //writing the reference number as well to the session
            $status = $this->SessionRestore->restore_from_session_backup_table($currentController, $currentAction, 'reference', $kiosk_id);
        }
        if($status == 'Success'){
            $msg = "Session succesfully retreived!";
        }else{
            $msg = "Session could not be retreived!";
        }
        $this->Flash->success($msg);
        return $this->redirect(array('action' => $redirectAction));
    }
    
    public function listDefectiveReferences(){
        //pr($this->DefectiveCentralProduct->find('all'));
        $kiosk_id = $this->request->Session()->read('kiosk_id');
        $status = array('0' => 'Transient', '1' => 'Received');
        $kiosks_query = $this->Kiosks->find('list',[
                                             'order' => 'Kiosks.name asc',
                                             'keyField' => 'id',
                                             'valueField' => 'name'
                                             ]
                                     );
        if(!empty($kiosks_query)){
          $kiosks = $kiosks_query->toArray();
        }
        $defectiveReferences_query = $this->DefectiveKioskReferences->find('all');
        $defectiveReferences_query = $defectiveReferences_query->hydrate(false);
        if(!empty($defectiveReferences_query)){
          $defectiveReferences = $defectiveReferences_query->toArray();
        }
        $userIds = array();
        foreach($defectiveReferences as $key => $defectiveReference){
            $userIds[$defectiveReference['user_id']] = $defectiveReference['user_id'];
            $userIds[$defectiveReference['received_by']] = $defectiveReference['received_by'];
        }
		if(empty($userIds)){
		  $userIds = array('0'=>null);
		}
        $users_query = $this->Users->find('list',[
                                             'conditions' => ['Users.id IN' => $userIds],
                                             'keyField' => 'id',
                                             'valueField' => 'username'
                                        ]
                                    );
        if(!empty($users_query)){
          $users = $users_query->toArray();
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
		  $kiosk_ids = array();
		  if($ext_site == 1){
			   $managerKiosk = $this->get_kiosk();
			   if(!empty($managerKiosk)){
				   $kiosk_ids = $managerKiosk;		
			   }
		  }
		
	   
        if($kiosk_id == ''){
			 if(empty($kiosk_ids)){
			   $this->paginate  = [
                                   'limit' => 50  ,
                                    'order' => ['DefectiveKioskReferences.id desc'],
                                    'recursive' => -1 
                                ];   
			 }else{
			   $this->paginate  = [
								   'conditions' => ['DefectiveKioskReferences.kiosk_id IN' => $kiosk_ids],
                                   'limit' => 50  ,
                                    'order' => ['DefectiveKioskReferences.id desc'],
                                    'recursive' => -1 
                                ];
			 }
		  
            
        }else{
            $this->paginate = [
                                
                                    'conditions' => ['DefectiveKioskReferences.kiosk_id' => $kiosk_id],
                                    'limit' =>  50  ,
                                     'order' => ['DefectiveKioskReferences.id desc'] ,
                                ];
        }
      // pr($this->paginate);die;
        
         $hint = $this->ScreenHint->hint('defective_kiosk_products','list_defective_references');
            if(!$hint){
                $hint = "";
            }
        
        $defectiveReferences_query = $this->paginate('DefectiveKioskReferences');
        if(!empty($defectiveReferences_query)){
          $defectiveReferences = $defectiveReferences_query->toArray();
        }

       // foreach($defectiveReferences as $key => $defectiveReference){
         //   $referenceIds[$defectiveReference['DefectiveKioskReference']['id']] = $defectiveReference['DefectiveKioskReference']['id'];
        //}
        $transientDetail_query = $this->DefectiveKioskTransients->find('all',
                                                  [
                                                       'fields' => ['defective_kiosk_reference_id' => "defective_kiosk_reference_id",
                                                                    "count" =>"COUNT(status)"],
                                                       'conditions' => ['status' => 0],
                                                       'group' => ['defective_kiosk_reference_id']]
                                                  );
        $transientDetail_query = $transientDetail_query->hydrate(false);
        if(!empty($transientDetail_query)){
          $transientDetail = $transientDetail_query->toArray();
        }
        $receivingArr = array();
        foreach($transientDetail as $key => $transient){
            $receivingArr[$transient['defective_kiosk_reference_id']] = $transient['count'];
        }
        $this->set(compact('hint','defectiveReferences', 'kiosks', 'users', 'status','receivingArr'));
	}
    
     public function searchDefectiveReferences(){
        $date_type = $this->request->query['date_type'];
        $userIds = array();
        $kiosk_id = $this->request->Session()->read('kiosk_id');
        $conditionArr = array();
        $selectedKiosk = $reference = $startDate = $endDate = '';
		//pr($this->request);die;
        if((array_key_exists('selectKiosk', $this->request->query) && !empty($this->request->query['selectKiosk'])) || is_numeric($kiosk_id)){
            if(is_numeric($kiosk_id)){//for retail kiosk
                $conditionArr[] = array("DefectiveKioskReferences.kiosk_id" => $kiosk_id);
            }else{
                $conditionArr[] = array("DefectiveKioskReferences.kiosk_id" => $this->request->query['selectKiosk']);
                $selectedKiosk = $this->request->query['selectKiosk'];
            }
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
					$kiosks_related = $this->get_kiosk();
					if(empty($kiosks_related)){
						 $kiosks_related = array(0 => null);
					}
					$conditionArr[] = array("DefectiveKioskReferences.kiosk_id IN" => $kiosks_related);
			   }
		}
        if(!empty($this->request->query['reference'])){
            $rfrence = $this->request->query['reference'];
            $conditionArr[] = array("DefectiveKioskReferences.reference like" => strtolower("%$rfrence%"));
            $reference = $this->request->query['reference'];
        }
        if(!empty($this->request->query['from_date']) && !empty($this->request->query['to_date'])){
            if($date_type == 'receiving_date'){
                $date_field = "date_of_receiving";
            }else{
                $date_field = "created";
            }
            $conditionArr[] = array("date($date_field) >=" => date('Y-m-d', strtotime($this->request->query['from_date'])),
                                    "date($date_field) <" => date('Y-m-d', strtotime("+1 day", strtotime($this->request->query['to_date']))));
            $startDate = $this->request->query['from_date'];
            $endDate = $this->request->query['to_date'];
        }
        
        $status = array('0' => 'Transient', '1' => 'Received');
        $kiosks_query = $this->Kiosks->find('list',[
                                                  'keyField' => 'id',
                                                  'valueField' => 'name',
                                                  'order' => 'Kiosks.name asc'
                                             ]
                                     );
        $kiosks_query = $kiosks_query->hydrate(false);
        if(!empty($kiosks_query)){
          $kiosks = $kiosks_query->toArray();
        }
        $defectiveReferences_query = $this->DefectiveKioskReferences->find('all');
        $defectiveReferences_query = $defectiveReferences_query->hydrate(false);
        if(!empty($defectiveReferences_query)){
          $defectiveReferences = $defectiveReferences_query->toArray();
        }
        foreach($defectiveReferences as $key => $defectiveReference){
            $userIds[$defectiveReference['user_id']] = $defectiveReference['user_id'];
            $userIds[$defectiveReference['received_by']] = $defectiveReference['received_by'];
        }
        if(empty($userIds)){
		  $userIds = array(0 => null);
		}
        $users_query = $this->Users->find('list',[
                                             'keyField' => 'id',
                                             'valueField' => 'username',
                                             'conditions' => ['Users.id IN' => $userIds]
                                             ]
                                    );
        $users_query = $users_query->hydrate(false);
        if(!empty($users_query)){
          $users = $users_query->toArray();
        }
        $receivingArr = array();
		// pr($conditionArr);die;
        if(count($conditionArr)){ 
             $this->paginate = [
                
                             'conditions' =>  
                                               $conditionArr
                                             ,
                             'order' => ['DefectiveKioskReferences.id' => 'desc'],
                            'limit' => ROWS_PER_PAGE
                          ];
           
            
        }else{
           
             $this->paginate = [
                                 'order' => ['DefectiveKioskReferences.id' => 'desc'],
                                'limit' => 50 
                          ];
           
            
        }
  //pr($this->Paginate);die;
        $defectiveReferences_query = $this->paginate('DefectiveKioskReferences');//die;
        if(!empty($defectiveReferences_query)){
          $defectiveReferences = $defectiveReferences_query->toArray();
        }else{
            $defectiveReferences = array();
        }
        
        $transientDetail_query = $this->DefectiveKioskTransients->find('all',
                                                  [
                                                       'fields' => ['defective_kiosk_reference_id' => "defective_kiosk_reference_id",
                                                                    "count" =>"COUNT(status)"],
                                                       'conditions' => ['status' => 0],
                                                       'group' => ['defective_kiosk_reference_id']]
                                                  );
        $transientDetail_query = $transientDetail_query->hydrate(false);
        if(!empty($transientDetail_query)){
          $transientDetail = $transientDetail_query->toArray();
        }
        foreach($transientDetail as $key => $transient){
            $receivingArr[$transient['defective_kiosk_reference_id']] = $transient['count'];
        }
            
        $hint = $this->ScreenHint->hint('defective_kiosk_products ','list_defective_references');
        if(!$hint){
           $hint = "";
        }  
        $this->set(compact('hint','defectiveReferences', 'kiosks', 'users', 'status', 'selectedKiosk', 'reference', 'startDate', 'endDate','date_type','receivingArr'));
        $this->render('list_defective_references');
    }
    
     public function viewTransientFaulty($id = ''){
        if($this->request->is('post')){
            pr($this->request);die;
            $transientData = $this->request->data['DefectiveKioskTransient'];
            //pr($transientData);
            $checked_id = $transientData['checked_id'];
            //Product which selected for receiving.
            $selectedKey = array_search($checked_id,$transientData['id']);
            //Key of selected product
            
            //checking if the product_id already exists in the central table
            $product_id = $transientData['product_id'][$selectedKey];
            $originalQuantity = $transientData['original_quantity'][$selectedKey];
            $quantity = $transientData['quantity'][$selectedKey];
            $dataArray = $transientData['json_data'][$selectedKey];
            if(array_key_exists('Receive',$transientData)){
                // updating central table quantity
                $jsonDataArr = json_decode($dataArray);
                $kioskID = (int)$jsonDataArr->kiosk_id;
                $productID = (int)$jsonDataArr->product_id;
                $refID = (int)$jsonDataArr->defective_kiosk_reference_id;
                $query = "SELECT `id` FROM `defective_kiosk_products` WHERE `kiosk_id` = $kioskID AND `product_id` = $productID AND `defective_kiosk_reference_id` = $refID AND `receive_date` IS NULL ORDER BY `id` ASC LIMIT 0,$quantity";
                    $conn = ConnectionManager::get('default');
                    $stmt = $conn->execute($query);
                    $queryRS = $stmt ->fetchAll('assoc');
                //$queryRS = $this->DefectiveKioskProduct->query($query);
                $date_time = $this->get_time();
                $queryArr = array();
                foreach($queryRS as $defProd){
                    $defID = $defProd['id'];
                    $receivedBy = $this->request->session()->read('Auth.User.group_id');
                    $queryArr[] = $updateQry = "UPDATE `defective_kiosk_products` SET `received_by` = $receivedBy, `receive_date` = '$date_time' WHERE `id` = $defID";
                      $conn = ConnectionManager::get('default');
                    $stmt = $conn->execute($updateQry);
                    $queryRS = $stmt ->fetchAll('assoc');
                   // $queryRS = $this->DefectiveKioskProduct->query($updateQry);
                }
                //echo implode("<br/>",$queryArr);die;
                //die;
                
                /* Sample Data: {"DefectiveKioskTransient":{"id":"72","kiosk_id":"2","defective_kiosk_reference_id":"44","user_id":"1","product_id":"1","quantity":"2","status":"0","created":"2016-05-28 10:45:11","modified":"2016-05-28 10:45:11"}}
                 */
       
                //echo "^^^";die;
                $countAdded = $this->update_central_table($product_id, $quantity);
                
                if($countAdded > 0){
                    if($quantity == $originalQuantity){
                        //if all quantity is received. e.g. (4 out of 4)
                         $remainingQty = $originalQuantity - $quantity;
                         
                        if($this->DefectiveKioskTransients->updateAll(array('status' => 1,'quantity' => $remainingQty), array('defective_kiosk_reference_id' => $id, 'product_id' => $product_id))){
                            //--------code for reading cake query---
                            /*
                             UPDATE `ADMIN_DOMAIN`.`defective_kiosk_transients` AS `DefectiveKioskTransient` SET `DefectiveKioskTransient`.`status` = '1' WHERE `DefectiveKioskTransient`.`defective_kiosk_reference_id` = 34 AND `DefectiveKioskTransient`.`product_id` = 5397
                             */
                            //--------code for reading cake query---
                            $datetime = $this->get_time();
                            $defectiveReferenceData = array(
                                                    'id' => $id,
                                                    'date_of_receiving' => $datetime,
                                                    'received_by' => $this->Auth->user('id'),
                                                    'status' => 1//1 is for receiving status
                                                            );
                            $getId = $this->DefectiveKioskReferences->get($id);
                            $patchEntity = $this->DefectiveKioskReferences->patchEntity($getId,$defectiveReferenceData,['validate' => false]);
                            $this->DefectiveKioskReferences->save($patchEntity);
                            //--------code for reading cake query---
                            
                            /*
                             UPDATE `ADMIN_DOMAIN`.`defective_kiosk_references` SET `id` = 34, `date_of_receiving` = '2016-05-28 06:55:57', `received_by` = 1, `status` = 1, `modified` = '2016-05-28 06:55:57' WHERE `ADMIN_DOMAIN`.`defective_kiosk_references`.`id` = '34'
                             */
                            //--------code for reading cake query---
                            $msg = "Product has been successfully received!";
                        }//status 1 for received
                    }else{
                        //if some quantity is received. e.g. (2 out of 4)
                        $remainingQty = $originalQuantity - $quantity;
                        if($this->DefectiveKioskTransients->updateAll(array('quantity' => $remainingQty), array('defective_kiosk_reference_id' => $id, 'product_id' => $product_id))){
                            //--------code for reading cake query---
                            
                            $datetime = $this->get_time();
                            $defectiveReferenceData = array(
                                                    'id' => $id,
                                                    'date_of_receiving' => $datetime,
                                                    'received_by' => $this->Auth->user('id'),
                                                    'status' => 1//1 is for receiving status
                                                            );
                            $getId = $this->DefectiveKioskReferences->get($id);
                            $patchEntity = $this->DefectiveKioskReferences->patchEntity($getId,$defectiveReferenceData,['validate' => false]);
                            $this->DefectiveKioskReference->save($patchEntity);
                            //--------code for reading cake query---
                            
                            //echo '<br/>DefectiveKioskTransient#else:'.$getLog['query'];
                            //--------code for reading cake query---
                            $msg = "Product has been successfully received!";
                        }
                    }
                    //die("-------");
                    //if($originalQuantity!=$quantity){
                      //  $quantity4Bin = $originalQuantity-$quantity;
                        //$this->move_2_bin($dataArray,$id,$quantity4Bin,$checked_id);
                    //}
                }else{
                    $msg = "Product could not be received!";
                }
            }elseif(array_key_exists('move_to_bin',$transientData)){
                $movedToCentral = $this->move_2_bin($dataArray,$id,$quantity,$checked_id,$originalQuantity);
                if($movedToCentral == 1){
                    $msg = "Product has been successfully moved to bin!";
                }else{
                    $msg = "Product could not be moved to bin!";
                }
            }else{
                $msg = "Please try again!";
            }
            if(!isset($msg) && empty($msg)){
                $msg = "";
            }
            $this->Flash->error($msg);
            
            return $this->redirect(array('action' => 'list_defective_references'));
        }else{
            $referenceArr_query = $this->DefectiveKioskReferences->find('all', array('conditions' => array('DefectiveKioskReferences.id' => $id), 'recursive' => -1));
            $referenceArr_query = $referenceArr_query->hydrate(false);
            if(!empty($referenceArr_query)){
               $referenceArr = $referenceArr_query->first();
               //$referenceArr = $referenceArr_query->toArray();
            }
           //$refrence = $referenceArr['DefectiveKioskReference']['reference'];
            $users_query = $this->Users->find('list',[
                                                  'conditions' =>  ['Users.id' => $referenceArr['user_id']],
                                                  'keyField' => 'id',
                                                  'valueField' => 'username',
                                                  ]
                                       );
            $users_query = $users_query->hydrate(false);
            if(!empty($users_query)){
               $users = $users_query->toArray();
            }
            $kiosks_query = $this->Kiosks->find('list',
                                                  [
                                                       'keyField' => 'id',
                                                       'valueField' => 'name',
                                                       'order' => 'Kiosks.name asc'
                                                  ]
                                         );
               $kiosks_query = $kiosks_query->hydrate(false);
               if(!empty($kiosks_query)){
                    $kiosks = $kiosks_query->toArray();
               }
               
            $productIdList_query = $this->DefectiveKioskTransients->find('list',[
                                                                                'keyField' => 'id',
                                                                                'valueField' => 'product_id',
                                                                 'conditions' => ['DefectiveKioskTransients.defective_kiosk_reference_id' => $id],
                                                                           ]);
            //pr($productIdList_query);die;
            $productIdList_query = $productIdList_query->hydrate(false);
            if(!empty($productIdList_query)){
               $productIdList = $productIdList_query->toArray();
            }
            //pr($productIdList);die;
            if(empty($productIdList)){
			   $productIdList = array(0 => null);
			}
            $productDetail_query = $this->Products->find('all', array('conditions' => array('Products.id IN' => $productIdList), 'fields' => array('id', 'product', 'product_code', 'color', 'image'), 'recursive' => -1));
            $productDetail_query = $productDetail_query->hydrate(false);
            if(!empty($productDetail_query)){
               $productDetail = $productDetail_query->toArray();
            }
            $productArr = array();
            foreach($productDetail as $key => $products){
                $productArr[$products['id']] = $products;
            }
            $this->paginate = [
                                        'conditions' => ['DefectiveKioskTransients.defective_kiosk_reference_id' => $id],
                                        ['limit' => '1000'],//kept as we need to show all the products on one page
                                        ['order' => 'DefectiveKioskTransients.id desc'],   
                              ];
            
            $defectiveTransients = $this->paginate('DefectiveKioskTransients');
            $this->set(compact('defectiveTransients','productArr','kiosks','id','referenceArr','users'));
        }
    }
    
     private function update_central_table($product_id = '', $quantity = ''){
        //This function is updating quantity in defective_central_products
        $countAdded = 0;
        $countCentral_query = $this->DefectiveCentralProducts->find('all', array('conditions' => array('DefectiveCentralProducts.product_id' => $product_id)));
		$countCentral = $countCentral_query->count();
        if($countCentral > 0){
            //updating the quantity if already existiing in defective_central_products
            $date_time = $this->get_time();
			$query = "UPDATE `defective_central_products` AS `DefectiveCentralProduct` SET `DefectiveCentralProduct`.`original_quantity` = DefectiveCentralProduct.original_quantity + 1, `DefectiveCentralProduct`.`modified` = '$date_time' WHERE `DefectiveCentralProduct`.`product_id` = $product_id";
			$conn = ConnectionManager::get('default');
		    $stmt = $conn->execute($query); 
            if($stmt){
                
                /*
                 *UPDATE `ADMIN_DOMAIN`.`defective_central_products` AS `DefectiveCentralProduct` SET `DefectiveCentralProduct`.`original_quantity` = DefectiveCentralProduct.original_quantity + 1, `DefectiveCentralProduct`.`modified` = '2016-05-28 06:55:57' WHERE `DefectiveCentralProduct`.`product_id` = 5397
                */
                $countAdded++;
            }
        }else{
            //$this->DefectiveCentralProduct->clear();
            //inserting new row if no item found for id in defective_central_products
            $defectiveCentralData = array(
                            'product_id' => $product_id,
                            'original_quantity' => $quantity
                                          );
            $DefectiveCentralProductsEntity = $this->DefectiveCentralProducts->newEntity($defectiveCentralData,['validate' => false]);
			$DefectiveCentralProductsEntity = $this->DefectiveCentralProducts->patchEntity($DefectiveCentralProductsEntity,$defectiveCentralData,['validate' => false]);
            if($this->DefectiveCentralProducts->save($DefectiveCentralProductsEntity)){
                $countAdded++;
            }
        }
        return $countAdded;
    }
    
    
    public function allFaultyProducts(){
           //$this->ImportOrderReference->query("UPDATE `import_order_references` SET `received_date` = '0000-00-00 00:00:00'");
        $statusArr = array('0' => 'Not Moved', '1' => 'Moved to Transient', '2' => 'Moved to Bin Transient');
        $kiosks_query = $this->Kiosks->find('list',[
                                                       'keyField' => 'id',
                                                       'keyValue' => 'name',
                                                       'order' => 'Kiosks.name asc',
                                             ]
                                     );
        $kiosks_query->hydrate(false);
        if(!empty($kiosks_query)){
          $kiosks = $kiosks_query->toArray();
        }
        $sumQuery = "SELECT SUM(`quantity` * cost_price) AS total FROM defective_kiosk_products WHERE `received_by` IS NOT NULL";
        
        $conn = ConnectionManager::get('default');
        $stmt = $conn->execute($sumQuery);
        $sumResult = $stmt ->fetchAll('assoc');
        //client does not want to see not moved items
        //$sumResult = $this->DefectiveKioskProduct->query($sumQuery);
        $faulty_conditions_query = $this->FaultyConditions->find('list',
                                                           [
                                                            'keyField' => 'id',
                                                            'valueField' => 'faulty_condition',
                                                           ]
                                                       );
        $faulty_conditions_query  = $faulty_conditions_query->hydrate(false);
        if(!empty($faulty_conditions_query)){
               $faulty_conditions = $faulty_conditions_query->toArray();
        }
		if(true ){
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
		  }else{
			   $managerKiosk = array();   
		  }
			
			if(!empty($managerKiosk)){
				$kiosk_id = $managerKiosk;
				$this->paginate = [
                                            'conditions' => ['DefectiveKioskProducts.status' => 1,
                                                             'DefectiveKioskProducts.received_by NOT IN' => 'null',
												 'DefectiveKioskProducts.kiosk_id IN' => $kiosk_id,
                                                            ],
                                                       //'`DefectiveKioskProducts`.`received_by` IS NOT NULL'],
                                            'limit' => [ROWS_PER_PAGE],
                                             'order' => ['DefectiveKioskProducts.date_of_movement desc'],
                                   ];
				
			}else{
				$this->paginate = [
                                            'conditions' => ['DefectiveKioskProducts.status' => 1,
                                                             'DefectiveKioskProducts.received_by NOT IN' => 'null'
                                                            ],
                                                       //'`DefectiveKioskProducts`.`received_by` IS NOT NULL'],
                                            'limit' => [ROWS_PER_PAGE],
                                             'order' => ['DefectiveKioskProducts.date_of_movement desc'],
                                   ];
			}
			
		}else{
			$this->paginate = [
                                            'conditions' => ['DefectiveKioskProducts.status' => 1,
                                                             'DefectiveKioskProducts.received_by NOT IN' => 'null'
                                                            ],
                                                       //'`DefectiveKioskProducts`.`received_by` IS NOT NULL'],
                                            'limit' => [ROWS_PER_PAGE],
                                             'order' => ['DefectiveKioskProducts.date_of_movement desc'],
                                   ];
		}
		
        
        //pr($this->paginate);die;
        //pr($this->paginate);die;
        $defectiveKioskProducts = $this->paginate('DefectiveKioskProducts');
        $userIds = array();
        $productIds = array();
        foreach($defectiveKioskProducts as $key => $defectiveKioskProduct){
            $userIds[$defectiveKioskProduct['user_id']] = $defectiveKioskProduct['user_id'];
            $productIds[$defectiveKioskProduct['product_id']] = $defectiveKioskProduct['product_id'];
        }
	   if(empty($productIds)){
		$productIds = array(0=>null);
	   }
        $productNames_query = $this->Products->find('list',
                                             [
                                                  'keyField' => 'id',
                                                  'valueField' => 'product',
                                                  'conditions' => ['Products.id IN' => $productIds]
                                             ]
                                             );
        $productNames_query = $productNames_query->hydrate(false);
        if(!empty($productNames_query)){
          $productNames = $productNames_query->toArray();
        }
        $productCodes_query = $this->Products->find('list',
                                                  [
                                                  'keyField' => 'id',
                                                  'valueField' => 'product_code',
                                                  'conditions' => ['Products.id IN' => $productIds]
                                             ]);
        $productCodes_query = $productCodes_query->hydrate(false);
        if(!empty($productCodes_query)){
          $productCodes  = $productCodes_query->toArray();
        }
        
          $hint = $this->ScreenHint->hint('defective_kiosk_products','all_faulty_products');
        if(!$hint)$hint = "";
        $users_query = $this->Users->find('list',
                                        [
                                             'keyField' => 'id',
                                             'valueField' => 'username',
                                        ]
                                   );
        $users_query = $users_query->hydrate(false);
        if(!empty($users_query)){
          $users = $users_query->toArray();
        }
        $this->set(compact('hint','faulty_conditions','sumResult','defectiveKioskProducts', 'users', 'kiosks','productNames','productCodes','statusArr'));
    }
    
    public function searchAllFaulty(){
       $date_type = $this->request->query['date_type'];
        if($date_type == 'date_of_movement'){
            $date = "receive_date";
        }else{
            $date = "created";
        }
        $this->set(compact('date_type'));
        $statusArr = array('0' => 'Not Moved', '1' => 'Moved to Transient', '2' => 'Moved to Bin Transient');
        $conditionArr = array();
        $selectedKiosk = $startDate = $endDate = '';
        if((array_key_exists('selectKiosk', $this->request->query) && !empty($this->request->query['selectKiosk']))){
            $conditionArr[] = array("DefectiveKioskProducts.kiosk_id" => $this->request->query['selectKiosk']);
            $selectedKiosk = $this->request->query['selectKiosk'];
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
			if(!empty($managerKiosk)){
			   $conditionArr[] = array("DefectiveKioskProducts.kiosk_id IN" => $managerKiosk);   
			}
		  }
			   
		}
        if(!empty($this->request->query['from_date']) && !empty($this->request->query['to_date'])){
            $conditionArr[] = array("date($date) >=" => date('Y-m-d', strtotime($this->request->query['from_date'])),
                                    "date($date) <" => date('Y-m-d', strtotime("+1 day", strtotime($this->request->query['to_date']))),
                                    '`DefectiveKioskProducts`.`received_by` IS NOT NULL');
            $startDate = $this->request->query['from_date'];
            $endDate = $this->request->query['to_date'];
        }
        if($date_type == 'date_of_movement'){
            $conditionArr[] = array('`DefectiveKioskProducts`.`received_by` IS NOT NULL');
        }
        if(count($conditionArr)){
            $sumResult_query = $this->DefectiveKioskProducts->find('all',array(
                                                        'conditions' => array($conditionArr, 'DefectiveKioskProducts.status' => 1),
                                                        //'fields' => array('SUM(`quantity` * cost_price) as total'),
                                                        'order' => 'DefectiveKioskProducts.id desc',
                                                )
                                     );
            //$sumResult_query = $sumResult_query->sumOf('quantity * cost_price');
            
            $sumResult_query
                          ->select(['total' => $sumResult_query->func()->sum('quantity * cost_price')]);
                          //->toArray();

            $sumResult_query = $sumResult_query->hydrate(false);
            if(!empty($sumResult_query)){
               $sumResult = $sumResult_query->toArray();
            }
            $this->paginate = [
                                    'conditions' => [$conditionArr, 'DefectiveKioskProducts.status' => 1],
                                    'limit' => ROWS_PER_PAGE,
                                    ['order' => 'DefectiveKioskProducts.date_of_movement desc'],
                              ];
             
        }else{
            $sumResult_query = $this->DefectiveKioskProducts->find('all',array(
                                                        'conditions' => array('DefectiveKioskProducts.status' => 1,'`DefectiveKioskProducts`.`received_by` IS NOT NULL'),
                                                       // 'fields' => array('SUM(`quantity` * cost_price) as total'),
                                                        'order' => 'DefectiveKioskProducts.id desc',
                                                )
                                     );
            $sumResult_query
                          ->select(['total' => $sumResult_query->func()->sum('quantity * cost_price')]);
            $sumResult_query = $sumResult_query->hydrate(false);
            if(!empty($sumResult_query)){
               $sumResult_query = $sumResult_query->toArray();
            }
            $this->paginate = [
                                    'conditions' => ['DefectiveKioskProducts.status' => 1],
                                    'limit' => ROWS_PER_PAGE,
                                    ['order' => 'DefectiveKioskProducts.date_of_movement desc'],
                              ];
        }
        $kiosks_query = $this->Kiosks->find('list',
                                      [
                                        'keyField' => 'id',
                                        'valueField' => 'name',
                                        'order' => 'Kiosks.name asc',
                                      ]);
        $kiosks_query = $kiosks_query->hydrate(false);
        if(!empty($kiosks_query)){
          $kiosks = $kiosks_query->toArray();
        }
        $faulty_conditions_query = $this->FaultyConditions->find('list',
                                                           [
                                                            'keyField' => 'id',
                                                            'valueField' => 'faulty_condition',
                                                           ]);
        $faulty_conditions_query = $faulty_conditions_query->hydrate(false);
        if(!empty($faulty_conditions_query)){
          $faulty_conditions = $faulty_conditions_query->toArray();
        }
		//pr($this->paginate);die;
        $defectiveKioskProducts = $this->paginate('DefectiveKioskProducts');
        $userIds = array();
        $productIds = array();
        foreach($defectiveKioskProducts as $key => $defectiveKioskProduct){
            $userIds[$defectiveKioskProduct['user_id']] = $defectiveKioskProduct['user_id'];
            $productIds[$defectiveKioskProduct['product_id']] = $defectiveKioskProduct['product_id'];
        }
        $productNames_query = $this->Products->find('list',
                                             [
                                                  'keyField' => 'id',
                                                  'valueField' => 'product',
                                                  ['conditions' => ['Products.id IN' => $productIds]]
                                             ]);
        //pr($productNames_query);die;
        $productNames_query = $productNames_query->hydrate(false);
        if(!empty($productNames_query)){
          $productNames = $productNames_query->toArray();
        }
        $productCodes_query = $this->Products->find('list',
                                             [
                                                  'keyField' => 'id',
                                                  'valueField' => 'product',
                                                  ['conditions' => ['Products.id IN' => $productIds]]
                                             ]);
        $productCodes_query =$productCodes_query->hydrate(false);
        if(!empty($productCodes_query)){
          $productCodes = $productCodes_query->toArray();
        }
        $users_query = $this->Users->find('list',
                                    [
                                        'keyField' => 'id',
                                        'valueField' => 'username',
                                    ]);
        $users_query = $users_query->hydrate(false);
        if(!empty($users_query)){
          $users  = $users_query->toArray();
        }
         $hint = $this->ScreenHint->hint('defective_kiosk_products','all_faulty_products');
            if(!$hint){
                $hint = "";
            }
        $this->set(compact('hint','faulty_conditions','sumResult','defectiveKioskProducts', 'kiosks', 'users', 'selectedKiosk', 'startDate', 'endDate', 'statusArr', 'productNames','productCodes'));
        $this->render('all_faulty_products');
    }
    
    public function deleteFaultyReceived()
    {
        $kiosks = $this->Kiosks->find('list',
                                      [
                                         'keyField' => 'id',               
                                         'valueField' => 'name',
                                         'order' => 'Kiosks.name asc'
                                         //'recursive' => -1
                                      ]);
        $kiosks = $kiosks->toArray();
        $this->set(compact('kiosks'));
        if($this->request->is('post')){
            $date_type = $this->request->data['date_type'];
            if($date_type == 'created_date'){
                $date = 'created';
            }else{
                $date = 'date_of_movement';
            }
            $from_date = $this->request->data['from_date'];
            $from_date = date("Y-m-d",strtotime($from_date));
            $to_date = $this->request->data['to_date'];
            $to_date = date("Y-m-d",strtotime("+1 day",strtotime($to_date)));
            if(empty($this->request->data['DefectiveKioskProduct']['selectKiosk'])){
               $kiosk = 'all';
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
					   $kiosk_condition = array('kiosk_id IN' => $managerKiosk);
					}
					//$kiosk_condition = '';
			   }else{
					$kiosk_condition = '';
			   }
				
               // $kiosk_condition = '';
            }else{
                $kiosk = $this->request->data['DefectiveKioskProduct']['selectKiosk'];
                $kiosk_condition = array('kiosk_id' => $kiosk);
            }
            
            if($this->DefectiveKioskProducts->deleteAll(array("DATE($date) >=" => $from_date, "DATE($date) <" => $to_date, $kiosk_condition), false)){
                $this->Flash->success("Data successfully deleted!");
            }else{
                $this->Flash->error("Data could not be deleted!");
            }
            return $this->redirect(array('action' => 'delete_faulty_received'));
        }
    }
    
    public function faultyBinReferences()
    {
        $kiosks_query = $this->Kiosks->find('list',[
                                                'keyField' => 'id',
                                                'valueField' => 'name',
                                                'order' => ['Kiosks.name asc'],
                                                //'recursive' => -1
                                             ]
                                     );
        if(!empty($kiosks_query)){
            $kiosks = $kiosks_query->toArray();
        }else{
            $kiosks = array();
        }
        $defectiveBinReferences_query = $this->DefectiveBinReferences->find('all');
        $defectiveBinReferences_query->hydrate(false);
        if(!empty($defectiveBinReferences_query)){
            $defectiveBinReferences = $defectiveBinReferences_query->toArray();
        }else{
            $defectiveBinReferences = array();
        }
        $userIds = array();
        foreach($defectiveBinReferences as $key => $defectiveBinReference){
            $userIds[$defectiveBinReference['user_id']] = $defectiveBinReference['user_id'];
        }
		if(empty($userIds)){
		  $userIds = array(0 => null);
		}
		
        $users_query = $this->Users->find('list',[
                                            'keyField' => 'id',
                                            'valueField' => 'username',
                                            'conditions' => ['Users.id IN' => $userIds]
                                           ]);
        if(!empty($users_query)){
            $users = $users_query->toArray();
        }else{
            $users = array();
        }
	   
	   if(true ){ //$this->request->session()->read('Auth.User.group_id')== MANAGERS
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
		  }else{
			   $managerKiosk = array();
		  }
		  
			if(!empty($managerKiosk)){
				//$kiosk_id = key($managerKiosk);
				$this->paginate = [
						'conditions' => ['DefectiveBinReferences.kiosk_id IN' => $managerKiosk],
                            'limit' => ROWS_PER_PAGE,
                            'order' => ['DefectiveBinReferences.id desc'],
                          ];
			}else{
				$this->paginate = [
						//'conditions' => ['DefectiveBinReferences.kiosk_id' => $kiosk_id],
                            'limit' => ROWS_PER_PAGE,
                            'order' => ['DefectiveBinReferences.id desc'],
                          ];
			}
	     }else{
			$this->paginate = [
						//'conditions' => ['DefectiveBinReferences.kiosk_id' => $kiosk_id],
                            'limit' => ROWS_PER_PAGE,
                            'order' => ['DefectiveBinReferences.id desc'],
                          ];
		}
	   
        
        $defectiveBinReferences_query = $this->paginate('DefectiveBinReferences');
        if(!empty($defectiveBinReferences_query)){
            $defectiveBinReferences = $defectiveBinReferences_query->toArray();
        }else{
            $defectiveBinReferences = array();
        }
        
        $sumData = $this->DefectiveBinReferences->find('all');
                  $sumData
                          ->select(['grand_total' => $sumData->func()->sum('total_cost')]);
        $sumData = $sumData->first();
        $sumData = $sumData->toArray();
        $hint = $this->ScreenHint->hint('defective_kiosk_products ','faulty_bin_references');
        if(!$hint){
            $hint = "";
        }        
        
        
        $sum = $sumData['grand_total'];
        $this->set(compact('hint','defectiveBinReferences', 'users', 'status', 'kiosks', 'sum'));
    }
    
    public function viewBinDetail($id='')
    {
        //echo $id;
        $referenceArr_query = $this->DefectiveBinReferences->find('all', array(
                                                                               'conditions' => array(
                                                                                                     'DefectiveBinReferences.id IN' => $id
                                                                                                     ),
                                                                               'recursive' => -1
                                                                               )
                                                                  );
        $referenceArr_result   = $referenceArr_query->hydrate(false);
       
        if(!empty($referenceArr_result)){
            $referenceArr = $referenceArr_result->first(); 
        }else{
            $referenceArr = array();
        }
        $users_query = $this->Users->find('list',[
                                            'keyField' => 'id',
                                            'valueField' => 'username',
                                            'conditions' => ['Users.id IN' => $referenceArr['user_id']]
                                            ]);
        if(!empty($users_query)){
            $users = $users_query->toArray();
        }else{
            $users = array();
        }
        $kiosks = $this->Kiosks->find('list',[
                                            'keyField' => 'id',
                                            'valueField' => 'name',
                                            'order' => ['Kiosks.name asc']
                                            ]);
        if(!empty($kiosks)){
            $kiosks = $kiosks->toArray();
        }else{
            $kiosks = array();
        }
        $productIdList_query = $this->DefectiveBinTransients->find('list',[
                                                                     'conditions' => ['DefectiveBinTransients.defective_bin_reference_id IN' => $id],
                                                                     'keyField' => 'id',
                                                                     'valueField' => 'product_id',
                                                                    ]);
        if(!empty($productIdList_query)){
            $productIdList = $productIdList_query->toArray();
        }else{
            $productIdList = array('0'=>null);
        }
        //pr($productIdList);die;
        $productDetail_query = $this->Products->find('all', array(
                                                                  'conditions' => array(
                                                                                        'Products.id IN' => $productIdList
                                                                                        ),
                                                                  'fields' => array('id', 'product', 'product_code', 'color', 'image'),
                                                                  'recursive' => -1
                                                                  )
                                                     );
        $productDetail_query->hydrate(false);
        if(!empty($productDetail_query)){
            $productDetail = $productDetail_query->toArray();
        }else{
            $productDetail = array();
        }
       // pr($productDetail);die;
        $productArr = array();
        foreach($productDetail as $key => $products){
            $productArr[$products['id']] = $products;
        }
        $this->paginate = [
                            'conditions' => array('DefectiveBinTransients.defective_bin_reference_id IN' => $id),
                            'limit' => '1000',//kept as we need to show all the products on one page
                            'order' => ['DefectiveBinTransients.id desc'],
                            //'recursive' => -1
                          ];
        $defectiveTransients = $this->paginate('DefectiveBinTransients');
        $this->set(compact('defectiveTransients','productArr','kiosks','id','referenceArr','users'));
    }
    
    public function searchBinReferences()
    {
        $conditionArr = array();
        //pr($this->request);die;
        $selectedKiosk = $reference = $startDate = $endDate = '';
        if((array_key_exists('selectKiosk', $this->request->query) && !empty($this->request->query['selectKiosk']))){
            $conditionArr[] = array("DefectiveBinReferences.kiosk_id" => $this->request->query['selectKiosk']);
            $selectedKiosk = $this->request->query['selectKiosk'];
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
						if(!empty($managerKiosk)){
						 $conditionArr[] = array("DefectiveBinReferences.kiosk_id IN" => $managerKiosk);
						}
			   }
		}
        if(!empty($this->request->query['reference'])){
            //echo '2';die;
            $reference = $this->request->query['reference'];
            $conditionArr[] = array("DefectiveBinReferences.reference like" => strtolower("%$reference%"));
        }
        if(!empty($this->request->query['from_date']) && !empty($this->request->query['to_date'])){
            $conditionArr[] = array("date(DefectiveBinReferences.created) >=" => date('Y-m-d', strtotime($this->request->query['from_date'])),
                                    "date(DefectiveBinReferences.created) <" => date('Y-m-d', strtotime("+1 day", strtotime($this->request->query['to_date']))));
            $startDate = $this->request->query['from_date'];
            $endDate = $this->request->query['to_date'];
        }
        
        $kiosks_query = $this->Kiosks->find('list',[
                                                'keyField' => 'id',
                                                'valueField' => 'name',
                                                'order' => ['Kiosks.name asc']
                                             ]);
        if(!empty($kiosks_query)){
            $kiosks = $kiosks_query->toArray();
        }else{
            $kiosks = array();
        }
        $defectiveReferences_query = $this->DefectiveBinReferences->find('all');
        $defectiveReferences_query->hydrate(false);
        if(!empty($defectiveReferences_query)){
            $defectiveReferences = $defectiveReferences_query->toArray();
        }else{
            $defectiveReferences = array();
        }
        $userIds = array();
        foreach($defectiveReferences as $key => $defectiveReference){
            $userIds[$defectiveReference['user_id']] = $defectiveReference['user_id'];
        }
        $users_query = $this->Users->find('list',[
                                            'keyField' => 'id',
                                            'valueField' => 'username',
                                            'conditions' => ['Users.id IN' => $userIds]
                                           ]);
        if(!empty($users_query)){
            $users = $users_query->toArray();
        }else{
            $users = array();
        }
        //pr($conditionArr);die;
        if(count($conditionArr)){
            //echo'3';die;
            $this->paginate = [
                                    'conditions' => $conditionArr,
                                    'limit' => ROWS_PER_PAGE,
                                    'order' => ['DefectiveBinReferences.id desc'],
                                    //'recursive' => -1
                            ];
                            
            $sumData = $this->DefectiveBinReferences->find('all',['conditions' => $conditionArr]);
            $sumData
                    ->select(['grand_total' => $sumData->func()->sum('total_cost')]);
            $sumData = $sumData->first();
            $sumData = $sumData->toArray();
            //$sumData = $this->DefectiveBinReference->find('first', array('fields' => array('SUM(total_cost) as grand_total'), 'conditions' => $conditionArr));
        }else{
            //echo'4';die;
            $this->paginate = [
                                    'limit' => ROWS_PER_PAGE,
                                    'order' => ['DefectiveBinReferences.id desc'],
                                    //'recursive' => -1
                            ];
            
             $sumData = $this->DefectiveBinReferences->find('all');
             $sumData
                     ->select(['grand_total' => $sumData->func()->sum('total_cost')]);
            $sumData = $sumData->first();
            $sumData = $sumData->toArray();
            //$sumData = $this->DefectiveBinReference->find('first', array('fields' => array('SUM(total_cost) as grand_total')));
        }
        
        $defectiveBinReferences = $this->paginate('DefectiveBinReferences');
        
        $sum = $sumData['grand_total'];
        $hint = $this->ScreenHint->hint('defective_kiosk_products ','faulty_bin_references');
        if(!$hint){
            $hint = "";
        } 
        
        $this->set(compact('hint','defectiveBinReferences', 'kiosks', 'users', 'status', 'selectedKiosk', 'reference', 'startDate', 'endDate', 'sum'));
        $this->render('faulty_bin_references');
    }
    
     public function centralBin()
    {
        $categories_query = $this->Categories->find('all',[
                                                        'fields' => ['id', 'category','id_name_path'],
                                                        'conditions' => ['Categories.status' => 1],
                                                        'order' => ['Categories.category asc'],
                                                        'recursive' => -1
                                                    ]);
        $categories_query->hydrate(false);
        if(!empty($categories_query)){
            $categories = $categories_query->toArray();
        }else{
            $categories = array();
        }
        $categories = $this->CustomOptions->category_options($categories,true);
        $this->paginate = [
                            'limit' => ROWS_PER_PAGE,
                            'order' => ['DefectiveBin.id desc'],
                            //'recursive' => -1
                          ];
        $defectiveBin_query = $this->paginate('DefectiveBin');
        if(!empty($defectiveBin_query)){
            $defectiveBin = $defectiveBin_query->toArray();
        }else{
            $defectiveBin = array();
        }
        $productIdList_query = $this->DefectiveBin->find('list',[
                                                            'keyField' => 'id',
                                                            'valueField' => 'product_id'
                                                           ]);
        if(!empty($productIdList_query)){
            $productIdList = $productIdList_query->toArray();
        }else{
            $productIdList = array();
        }
		if(empty($productIdList)){
		  $productIdList = array(0 => null);
		}
        $productDetail_query = $this->Products->find('all', array('conditions' => array('Products.id IN' => $productIdList), 'fields' => array('id', 'product', 'product_code', 'color', 'image'), 'recursive' => -1));
        $productDetail_query->hydrate(false);
        if(!empty($productDetail_query)){
            $productDetail = $productDetail_query->toArray();
        }else{
            $productDetail = array();
        }
        $productArr = array();
        foreach($productDetail as $key => $products){
            $productArr[$products['id']] = $products;
        }
        $hint = $this->ScreenHint->hint('defective_kiosk_products','central_bin');
            if(!$hint){
                $hint = "";
            }   
        
        
        $this->set(compact('hint','defectiveBin', 'productArr', 'categories'));
    }
    
     public function searchBin()
    {
        if(array_key_exists('search_kw',$this->request->query)){
                $search_kw = $this->request->query['search_kw'];
                $this->set(compact('search_kw'));
        }
        
        extract($this->request->query);
        $categories_query = $this->Categories->find('all',array(
                                                        'fields' => array('id', 'category','id_name_path'),
                                                        'conditions' => array('Categories.status' => 1),
                                                        'order' => ['Categories.category asc'],
                                                        'recursive' => -1
                                                        ));
        $categories_query->hydrate(false);
        if(!empty($categories_query)){
            $categories = $categories_query->toArray();
        }else{
            $categories = array();
        }
        //pr($this->request->query);die;
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
        //pr($conditionArr);die;
        $searchedProductIds_query = $this->Products->find('list',[
                                                            'conditions' => $conditionArr,
                                                            'keyField' => 'id',
                                                            'valueField' => 'id'
                                                           ]);
        if(!empty($searchedProductIds_query)){
            $searchedProductIds = $searchedProductIds_query->toArray();
        }else{
            $searchedProductIds = array();
        }
        $selectedCategoryId=array();
        //pr($conditionArr);die;
        if(array_key_exists('category_id IN',$conditionArr) && !empty($conditionArr['category_id IN'][0])){
                $selectedCategoryId=$conditionArr['category_id IN'];
        }
        $this->Products->recursive = 0;
        //pr($selectedCategoryId);die;
        $categories = $this->CustomOptions->category_options($categories,true,$selectedCategoryId);
        //pr($categories);die;
        if(empty($searchedProductIds)){
           $searchedProductIds = array(0 => null); 
        }
        $this->paginate = [
                            'conditions' => ['DefectiveBin.product_id IN' => $searchedProductIds],
                            'limit' => ROWS_PER_PAGE,
                            'order' => ['DefectiveBin.id desc'],
                            //'recursive' => -1                     
                          ];
        $defectiveBin = $this->paginate('DefectiveBin');
        if(!empty($defectiveBin)){
            $defectiveBin = $defectiveBin->toArray();
        }else{
            $defectiveBin = array();
        }
        $productIdList_query = $this->DefectiveBin->find('list',[
                                                           'keyField' => 'id',
                                                            'valueField' => 'product_id'
                                                          ]);
        if(!empty($productIdList_query)){
            $productIdList = $productIdList_query->toArray();
        }else{
            $productIdList = array();
        }
        $productDetail_query = $this->Products->find('all', array('conditions' => array('Products.id IN' => $productIdList), 'fields' => array('id', 'product', 'product_code', 'color', 'image'), 'recursive' => -1));
        $productDetail_query->hydrate(false);
        if(!empty($productDetail_query)){
            $productDetail = $productDetail_query->toArray();
        }else{
            $productDetail = array();
        }
        $productArr = array();
        foreach($productDetail as $key => $products){
            $productArr[$products['id']] = $products;
        }
        
        $hint = $this->ScreenHint->hint('defective_kiosk_products','central_bin');
        if(!$hint){
           $hint = "";
        }   
        
        $this->set(compact('hint','defectiveBin', 'productArr', 'categories'));
        $this -> render('central_bin');
    }
    
     public function viewFaultyProducts(){
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
         $categories = $this->CustomOptions->category_options($categories,true);
        $faulty_conditions_query = $this->FaultyConditions->find('list',
                                                                 [
                                                                      'keyField' => 'id',
                                                                      'valueField' => 'faulty_condition',
                                                                 ]
                                                           );
        $faulty_conditions_query = $faulty_conditions_query->hydrate(false);
        if(!empty($faulty_conditions_query)){
            $faulty_conditions = $faulty_conditions_query->toArray();
        }else{
            $faulty_conditions = array();
        }
        $kiosk_id = $this->request->Session()->read('kiosk_id');
        $kiosks_query = $this->Kiosks->find('list',
                                                  [
                                                       'keyField' => 'id',
                                                       'valueField' => 'name',
                                                       'conditions' => ['Kiosks.status' => 1, 'Kiosks.id' => $kiosk_id],
                                                       'order' => 'Kiosks.name asc'
                                                  ]
                                    );
        $kiosks_query = $kiosks_query->hydrate(false);
        if(!empty($kiosks_query)){
          $kiosks = $kiosks_query->toArray();
        }else{
          $kiosks = array();
        }
        $this->paginate = [
                        'limit' => ROWS_PER_PAGE,
                        'conditions'=>['DefectiveKioskProducts.kiosk_id' => $kiosk_id,
                                            'DefectiveKioskProducts.status' => 0//removing of this condition will show transient and moved to bin in the frontend, codding for which has already been done on the ctp file
                                      ],
                        'order' => ['DefectiveKioskProducts.id desc']
                        ];
        //pr($this->paginate);die;
        $defectiveKioskProduct_query = $this->paginate('DefectiveKioskProducts');
        //pr($defectiveKioskProduct_query);die;
        $defectiveKioskProduct = $defectiveKioskProduct_query->toArray();
        $userIds = array();
        $productIdList = array();
        foreach($defectiveKioskProduct as $key => $defectiveKiosk){
            $userIds[$defectiveKiosk->user_id] = $defectiveKiosk->user_id;
            $productIdList[$defectiveKiosk->product_id] = $defectiveKiosk->product_id;
        }
        if(empty($productIdList)){
          $productIdList = array(0=>null);
        }
        $productDetail_query = $this->Products->find('all', array('conditions' => array('Products.id IN' => $productIdList), 'fields' => array('id', 'product', 'product_code', 'color', 'image')));
        $productDetail_query = $productDetail_query->hydrate(false);
        if(!empty($productDetail_query)){
          $productDetail = $productDetail_query->toArray();
        }else{
          $productDetail = array();
        }
        $productArr = array();
        foreach($productDetail as $pd => $productDet){
            $productArr[$productDet['id']] = $productDet;
        }
        $users = array();
        if(count($userIds)){
            $users_query = $this->Users->find('list',
                                                  [
                                                       'keyField' => 'id',
                                                       'valueField' => 'username',
                                                       'conditions' => ['Users.id IN' => $userIds]
                                                  ]
                                        );
            $users_query = $users_query->hydrate(false);
            if(!empty($users_query)){
               $users = $users_query->toArray();
            }else{
               $users = array();
            }
        }
        $this->set(compact('kiosks','faulty_conditions','defectiveKioskProduct','users','productArr','categories'));
    }
    
    public function searchFaulty(){
        if(array_key_exists('search_kw',$this->request->query) && !empty($this->request->query['search_kw'])){
			$searchKW = $this->request->query['search_kw'];
			$searchW = strtolower($searchKW);
			$productResult_query = "SELECT `id` from `products` WHERE LOWER(`product_code`) like ('%{$searchW}%') or LOWER(`product`) like ('%{$searchW}%')";
			$conn = ConnectionManager::get('default');
            $stmt = $conn->execute($productResult_query); 
            $productResult = $stmt ->fetchAll('assoc');
          
			$productIDs = array();
			
			foreach($productResult as $sngproductResult){
				$productIDs[] = $sngproductResult['id'];
			}
			$this->set('search_kw',$this->request->query['search_kw']);
		}
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
        
        if(array_key_exists('category',$this->request->query) && !empty($this->request->query['category']) ){
			$catagory = $this->request->query['category'];
           
			$product_ids_query = $this->Products->find('list',array('conditions' => array('category_id IN' => $catagory),
											 'fields' => array('id')
											 ));
            $product_ids_query = $product_ids_query->hydrate(false);
            if(!empty($product_ids_query)){
               $product_ids = $product_ids_query->toArray();
            }else{
               $product_ids = array();  
            }
            $categories = $this->CustomOptions->category_options($categories,true, $catagory);
		}else{
            $categories = $this->CustomOptions->category_options($categories,true);
        }
        
        $p_ids = array();
		if(!empty($product_ids)&&!empty($productIDs)){
			$p_ids = array_merge($productIDs,$product_ids);
		}elseif(!empty($productIDs)){
			$p_ids = $productIDs;
		}elseif(!empty($product_ids)){
			$p_ids = $product_ids;
		}
        
        $faulty_conditions_query = $this->FaultyConditions->find('list', array('keyField' => 'id','valueField' => 'faulty_condition'));
        $faulty_conditions_query = $faulty_conditions_query->hydrate(false);
        if(!empty($faulty_conditions_query)){
          $faulty_conditions = $faulty_conditions_query->toArray();
        }else{
          $faulty_conditions = array();
        }
        $kiosk_id = $this->request->Session()->read('kiosk_id');
        $kiosks_query = $this->Kiosks->find('list',array(
                                                  'keyField' =>'id',
                                                  'valueField' => 'name',
                                                'conditions' => array('Kiosks.status' => 1, 'Kiosks.id' => $kiosk_id),
                                                'order' => 'Kiosks.name asc'
                                        )
                                    );
        $kiosks_query = $kiosks_query->hydrate(false);
        if(!empty($kiosks_query)){
               $kiosks = $kiosks_query->toArray();
        }else{
               $kiosks = array(); 
        }
        
        
        $conditionArr = array(
                            'DefectiveKioskProducts.kiosk_id' => $kiosk_id,
                            'DefectiveKioskProducts.status' => 0
                            //removing of this condition will show transient and moved to bin in the frontend,
                            //codding for which has already been done on the ctp file
                            );
        if(count($p_ids)){
          //pr($p_ids);die;
               $conditionArr['DefectiveKioskProducts.product_id IN'] = $p_ids;
        }
        $this->paginate = [
                        'limit' => ROWS_PER_PAGE,
                        'conditions' => $conditionArr,
                        'order' => ['DefectiveKioskProducts.id desc']
                ];
        $defectiveKioskProduct_query = $this->paginate('DefectiveKioskProducts');
        $defectiveKioskProduct = $defectiveKioskProduct_query->toArray();
         $userIds = array();
        $productIdList = array();
        foreach($defectiveKioskProduct as $key => $defectiveKiosk){
            $userIds[$defectiveKiosk->user_id] = $defectiveKiosk->user_id;
            $productIdList[$defectiveKiosk->product_id] = $defectiveKiosk->product_id;
        }
        //pr($defectiveKioskProduct);die;
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
        foreach($productDetail as $pd => $productDet){
            $productArr[$productDet['id']] = $productDet;
        }
        $users = array();
        if(count($userIds)){
            $users_query = $this->Users->find('list', array('keyField' => 'id','valueField' => 'username', 'conditions' => array('Users.id IN' => $userIds)));
            $users_query = $users_query->hydrate(false);
            if(!empty($users_query)){
               $users = $users_query->toArray();
            }else{
               $users = array();
            }
        }
        $this->set(compact('kiosks','faulty_conditions','defectiveKioskProduct','users','productArr','categories'));
        $this->render('view_faulty_products');
    }
    
     public function restore($id = '', $kioskId = '', $current_page = ''){
        $session_basket = $this->request->Session()->read('consolidate_faulty');
        $exists = 0;
        if(is_array($session_basket)){
            foreach($session_basket as $productId => $sessionInfo){
                if(array_key_exists($id, $sessionInfo)){
                    $exists = 1;
                }
            }
        }
        
        $defectiveProductData_query = $this->DefectiveKioskProducts->find('all', array('conditions' => array('DefectiveKioskProducts.id' => $id)));
        $defectiveProductData_query = $defectiveProductData_query->hydrate(false);
        if(!empty($defectiveProductData_query)){
          $defectiveProductData = $defectiveProductData_query->first();
        }else{
          $defectiveProductData = array();
        }
        $faultyQuantity = $defectiveProductData['quantity'];
        $prdctId = $defectiveProductData['product_id'];
        
        if($current_page == 'view_faulty_products'){
            //bock when request comes from view_faulty_products
            $delete_Entity = $this->DefectiveKioskProducts->get($id);
            if($this->DefectiveKioskProducts->delete($delete_Entity)){
                $msg = "Product has been successfully restored";
                //code for adding quantity back to stock
                $this->add_product_quantity($kioskId, $faultyQuantity, $prdctId);
            }else{
                $msg = "Product could not be restored";
            }
        }else{
            if($exists == 0){
               $delete_Entity = $this->DefectiveKioskProducts->get($id);
                if($this->DefectiveKioskProducts->delete($delete_Entity)){
                    //code for adding quantity back to stock
                    $this->add_product_quantity($kioskId, $faultyQuantity, $prdctId);
                    $msg = "Product has been successfully restored";
                }else{
                    $msg = "Product could not be restored";
                }
            }else{
                $msg = "Please remove the product from the cart to restore";
            }
        }
        
        $this->Flash->success($msg);
        if($current_page == 'view_faulty_products'){//defined in view_faulty_products.ctp
            return $this->redirect(array('action' => "view_faulty_products"));
        }else{
            return $this->redirect(array('action' => "consolidate_faulty/page:$current_page?kioskDropdown=$kioskId"));
        }
    }
    
     private function add_product_quantity($kioskId = '', $faultyQtt = '', $productID = ''){
        if($kioskId != 10000){
            $productTable = "kiosk_{$kioskId}_products";
        }else{
            $productTable = "products";
        }
        $conn = ConnectionManager::get('default');
        $query = "UPDATE $productTable SET $productTable.quantity = $productTable.quantity + $faultyQtt WHERE $productTable.id = $productID";
       // echo $query;die;
        $stmt = $conn->execute($query);
    }
	
	  function searchRawFaulty($keyword = "",$displayCondition = ""){
        $faulty_conditions_query = $this->FaultyConditions->find('list', array(
																		 'keyField' => 'id',
																		 'valueField' => 'faulty_condition',
																		 ));
		$faulty_conditions_query = $faulty_conditions_query->hydrate(false);
		if(!empty($faulty_conditions_query)){
			   $faulty_conditions = $faulty_conditions_query->toArray();
		}else{
			   $faulty_conditions = array(); 
		}
		
		
        $kiosks_query = $this->Kiosks->find('list',array(
													   'keyField' => 'id',
													   'valueField' => 'name',
                                                        'conditions' => array('Kiosks.status' => 1),
                                                        'order' => 'Kiosks.name asc'
                                                )
                                     );
		$kiosks_query = $kiosks_query->hydrate(false);
		if(!empty($kiosks_query)){
		  $kiosks = $kiosks_query->toArray();
		}else{
		 $kiosks = array(); 
		}
		//pr($this->request->query);die;
        if($this->request->is(array('get'))){
            if(array_key_exists('kiosk-dropdown', $this->request->query)){
                $kioskId = $this->request->query['kiosk-dropdown'];
            }else{
                $kioskId = current(array_keys($kiosks));
            }
        }else{
            $kioskId = current(array_keys($kiosks));
        }
        //pr($kioskId);die;
        $this->set(compact('kioskId'));
        
        $faultyProductIds_query = $this->DefectiveKioskProducts->find('list',array(
																			 'keyField' => 'id',
																			 'valueField' => 'product_id',
												  'conditions' => array('DefectiveKioskProducts.kiosk_id' => $kioskId, 'DefectiveKioskProducts.status' => 0)));
		$faultyProductIds_query = $faultyProductIds_query->hydrate(false);
		if(!empty($faultyProductIds_query)){
			   $faultyProductIds = $faultyProductIds_query->toArray();
		}else{
			   $faultyProductIds = array();	
		}
        $userList_query = $this->DefectiveKioskProducts->find('list', array(
																	  'keyField' => 'id',
																	  'valueField' => 'user_id',
																	   'conditions' => array('DefectiveKioskProducts.kiosk_id' => $kioskId, 'DefectiveKioskProducts.status' => 0)));
        $userList_query = $userList_query->hydrate(false);
		if(!empty($userList_query)){
			   $userList = $userList_query->toArray();
		}else{
			   $userList = array();
		}
        if(empty($userList)){
            $userList = array(0 => null);
        }
        $users_query = $this->Users->find('list', array(
												  'keyField' => 'id',
												  'valueField' => 'username',
												   'conditions' => array('Users.id IN' => $userList)));
		$users_query = $users_query->hydrate(false);
		if(!empty($users_query)){
			   $users = $users_query->toArray();
		}else{
			   $users = array();
		}
            if(array_key_exists('search_kw',$this->request->query)){
                     $search_kw = $this->request->query['search_kw'];
                    $this->set(compact('search_kw'));
            }
            
            extract($this->request->query);
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
			
            $conditionArr = array();
            $conditionArr['NOT']['quantity'] = 0;
            //----------------------
            if(!empty($search_kw)){
                    $conditionArr['OR']['LOWER(product) like '] =  strtolower("%$search_kw%");
                    $conditionArr['OR']['LOWER(product_code) like '] =  strtolower("%$search_kw%");
            }
            //----------------------
            if(array_key_exists('category',$this->request->query) && !empty($this->request->query['category'][0])){
                 $conditionArr['category_id IN'] = $this->request->query['category'];
            }
            
            if($kioskId != 10000){
                $productTable_source = "kiosk_{$kioskId}_products";
            }else{
                $productTable_source = "products";
            }
            
            $productTable = TableRegistry::get($productTable_source,[
                                                                                    'table' => $productTable_source,
                                                                                ]);
            
            $searchedProducts = array();
            $productArray = array();
			 
			//if(count($conditionArr) > 1){
				$searchedProducts_query = $productTable->find('list', array(
																	  'valueField' =>'id',
																	   'conditions' => $conditionArr));
				$searchedProducts_query = $searchedProducts_query->hydrate(false);
				if(!empty($searchedProducts_query)){
					$searchedProducts = $searchedProducts_query->toArray();
				}else{
					$searchedProducts = array();
				}
				
				// pr($searchedProducts);//die;
				if(empty($searchedProducts)){
					$searchedProducts = array(0 => null);
				}
                $productData_query = $productTable->find('all', array('conditions' => array('id IN' => $searchedProducts)));
				$productData_query = $productData_query->hydrate(false);
				if(!empty($productData_query)){
					$productData = $productData_query->toArray();
				}else{
					$productData = array();
				}
				//pr($productData);
                $productArray = array();
				if(!empty($productData)){
					foreach($productData as $pd => $products){
						$productArray[$products['id']] = $products;
					}
				}
			//} 
            
            
            $selectedCategoryId = array();
            //if(array_key_exists('category_id',$conditionArr) && !empty($conditionArr['category_id'][0])){
             if(array_key_exists('category_id IN',$conditionArr)  ){
                      $selectedCategoryId = $conditionArr['category_id IN'];
            }else{
            $selectedCategoryId = array(0 => 0);
            }
            //pr($selectedCategoryId); 
            //pr($conditionArr);die;
            //$this->Product->recursive = 0;
            $categories = $this->CustomOptions->category_options($categories,true,$selectedCategoryId);
            $userList_query = $this->DefectiveKioskProducts->find('list', array(
																		  'keyField' =>'id',
																		  'valueField' => 'user_id',
																	   'conditions' => array('DefectiveKioskProducts.kiosk_id' => $kioskId, 'DefectiveKioskProducts.status' => 0)));
		  $userList_query = $userList_query->hydrate(false);
		  if(!empty($userList_query)){
			   $userList = $userList_query->toArray();
		  }else{
			   $userList = array();
		  }
		  
            if(empty($userList)){
                $userList = array(0 =>null);
            }
            $users_query = $this->Users->find('list', array(
													  'keyField' =>'id',
													   'valueField' => 'username',
													   'conditions' => array('Users.id IN' => $userList)));
			$users_query = $users_query->hydrate(false);
			if(!empty($users_query)){
			   $users = $users_query->toArray();
			}else{
			   $users = array();
			}
			if(!empty($searchedProducts)){
				if(count($searchedProducts)){
					$searchedProductsArr = array('DefectiveKioskProducts.product_id IN' => $searchedProducts);
				}else{
					$searchedProductsArr = array();
				}
            }else{
				 $searchedProductsArr = array('DefectiveKioskProducts.product_id IN' => $searchedProducts);
			}
			
		//pr($searchedProductsArr);
            //if(count($searchedProducts)){
            //    $searchedProductsArr = array('DefectiveKioskProduct.product_id' => $searchedProducts);
            //} 
            
            $this->paginate = array(
                                'limit' => ROWS_PER_PAGE,
                                'conditions'=>array('DefectiveKioskProducts.kiosk_id' => $kioskId,
                                                    $searchedProductsArr,
                                                    'DefectiveKioskProducts.status' => 0),
                                'order' => ['DefectiveKioskProducts.id desc']
                        );
            
            $defectiveKioskProduct_query = $this->paginate('DefectiveKioskProducts');
			$defectiveKioskProduct = $defectiveKioskProduct_query->toArray();
			if(count($conditionArr) >= 1){
                $productIdArr = array();
                foreach($defectiveKioskProduct as $key => $defective_product){
                    $productIdArr[] = $defective_product->product_id;
                }
                if(empty($productIdArr)){
					$productIdArr = array(0 => null);
				}
                $productData_query = $productTable->find('all', array('conditions' => array('id IN' => $productIdArr),
                                                                 ));
                $productData_query = $productData_query->hydrate(false);
				if(!empty($productData_query)){
					$productData = $productData_query->toArray();
				}else{
					$productData = array();
				}
                $productArray = array();
                foreach($productData as $pd => $products){
                    $productArray[$products['id']] = $products;
                }
            }
            
            $hint = $this->ScreenHint->hint('defective_kiosk_products','add');
            if(!$hint){
                $hint = "";
            }
            
            $this->set('rawFaultyProduct', $defectiveKioskProduct);
            $this->set(compact('hint','categories','kiosks','displayType','productArray','users','faulty_conditions'));
            $this->render('consolidate_faulty');
    }
	
	 private function add_to_session($queryVar = array(), $current_page, $kioskId){
        //pr($queryVar);die;
        $productCounts = 0;
        $reference = $this->request->Session()->read('reference');
        //$productArr = array();
        if(empty($reference)){
            if(array_key_exists('reference',$queryVar) && !empty($queryVar['reference'])){
                $this->request->Session()->write('reference',$queryVar['reference']);
            }else{
                $this->Flash->error('Please enter a reference number to proceed');
                return $this->redirect(array('action' => "consolidate_faulty/page:$current_page?kioskDropdown=$kioskId"));
            }
        }else{
            if(array_key_exists('reference',$queryVar) && !empty($queryVar['reference'])){
                $this->request->Session()->write('reference',$queryVar['reference']);
            }else{
                $this->Flash->error('Please enter a reference number to proceed');
                return $this->redirect(array('action' => "consolidate_faulty/page:$current_page?kioskDropdown=$kioskId"));
            }
        }
        if(count($queryVar) && array_key_exists('data', $queryVar)){
            $consolidateFaultyArr = $queryVar['data']['ConsolidateFaulty'];
            foreach($consolidateFaultyArr as $productId => $product_data){
                foreach($product_data as $faultyId => $faultyQtt){
                    if($faultyQtt > 0){
                        $productArr[$productId][$faultyId] = $faultyQtt;//$faultyId is primary id of faulty products table
                        
                        $productCounts++;
                    }
                }
            }
        }
    
        $session_basket = $this->request->Session()->read('consolidate_faulty');
        
        if($productCounts > 0){
             //adding item to the the existing session
                foreach($productArr as $productId => $info){
                    if(is_array($session_basket) && array_key_exists($productId, $session_basket)){
                        //push the array to existing product_id
                        //$session_basket[$productId] = $info;
                        foreach($info as $faultyId => $faultyQtt){
                            $session_basket[$productId][$faultyId] = $faultyQtt;
                        }
                    }else{
                        //push array with new product_id as key
                        $session_basket[$productId] = $info;
                    }
                }
                //$sum_total = $this->add_arrays(array($productArr,$session_basket));
                $this->request->Session()->write('consolidate_faulty', $session_basket);
                $session_basket = $this->request->Session()->read('consolidate_faulty');
        }
        
        return $productCounts;
    }
	
	  private function session_quantity(){
        $sessionQuantity = 0;
        if(is_array($this->request->Session()->read('consolidate_faulty'))){
            foreach(array_values($this->request->Session()->read('consolidate_faulty')) as $key => $sessionInfo){
               foreach($sessionInfo as $ss_key => $ss_value){
                $sessionQuantity+=$ss_value;//count($sessionInfo);
			   }
            }
        }
        return $sessionQuantity;
    }
	
	 private function flash_cart($sessionVar = 'consolidate_faulty'){
        $session_basket = $this->request->Session()->read($sessionVar);
        $sessionPrdctInfo = array();
        $product_ids = array_keys($session_basket);
        $sessionProducts_query = $this->Products->find('all',array('conditions'=>array('Products.id IN'=>$product_ids),'fields'=>array('id','product','product_code')));
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
        foreach($session_basket as $productId => $productInfo){
            foreach($productInfo as $faultyId => $faultyQtt){
                $flashTable.= "<tr>
                                <td>".$sessionPrdctInfo[$productId]['product_code']."</td>
                                <td>".$sessionPrdctInfo[$productId]['product']."</td>
                                <td>".$faultyQtt."</td>
                        <tr>";
            }   
        }
        
        $sessionProductTable = '';
        if(!empty($flashTable)){
                $sessionProductTable = "<table>
                                <tr>
								<th style='width: 128px;'>Product code</th>
								<th style='width: 1200px;'>Product</th>
                                <th>Qty</th>
                                </tr>$flashTable
                                </table>";
        }
        return $sessionProductTable;
    }
	
	  private function check_session($sessionQuantity = '', $current_page = '', $kioskId = ''){
        if($sessionQuantity == 0){
            $flashMessage = "Failed to proceed. <br />Please select quantity atleast for one product!";
            $this->Flash->error($flashMessage);
            $this->redirect(array('action' => "consolidate_faulty/page:$current_page?kioskDropdown=$kioskId"));
            die;
        }
    }
	
	 public function consolidateCheckOut(){
            $session_basket = $this->request->Session()->read('consolidate_faulty');
			//pr($this->request);die;
            if(!array_key_exists('kiosk-dropdown',$this->request->query)){
                $this->Flash->error("Please choose a kiosk to proceed!");
                return $this->redirect(array('action' => 'consolidate_faulty'));
                die;
            }
            
            if(is_array($session_basket)){
                    $product_ids = array_keys($session_basket);
                    $productCodeArr = array();
                    $kiosk_id = $this->request->query['kiosk-dropdown'];
                    if($kiosk_id != 10000){
                        $productSource = "kiosk_{$kiosk_id}_products";
                        //$this->Product->setSource($productSource);
                    }else{
                        $productSource = "products";
                        //$this->Product->setSource($productSource);
                    }
					$productTable = TableRegistry::get($productSource,[
																				'table' => $productSource,
																			]);
                    $productCodeArr_query = $productTable->find('all',array('conditions' => array('id IN' => $product_ids),
                                                                                                                      'fields' => array('id','product_code','quantity')
                                                                                                                      )
                                                                                              );
					$productCodeArr_query = $productCodeArr_query->hydrate(false);
					if(!empty($productCodeArr_query)){
						 $productCodeArr = $productCodeArr_query->toArray();
					}else{
						 $productCodeArr = array();
					}
                    $productIds = array();
                    if(!empty($productCodeArr)){
                            foreach($productCodeArr as $k=>$productCodeData){
                                $productIds[$productCodeData['id']] = $productCodeData['product_code'];
                            }
                    }
                    $productNameArr = array();
                    foreach($session_basket as $productId=>$productDetails){
						 $productNameArr_query = $productTable->find('all',array('conditions'=>array('id'=>$productId),'fields'=>array('id','product')));
						 $productNameArr_query = $productNameArr_query->hydrate(false);
						 if(!empty($productNameArr_query)){
							  $productNameArr[] = $productNameArr_query->first(); 
						 }else{
							  $productNameArr = array();
						 }
					
                    }
					//pr($productNameArr);die;
                    foreach($productNameArr as $key=>$selectedProducts){
                            $productArr[$selectedProducts['id']] = $selectedProducts['product'];
                    }
                    if($this->request->is('post')){
                            $error = array();
                            if(array_key_exists('update_quantity',$this->request->data)){
                                $faultyIds = array_keys($this->request->data['CheckOut']);
                                $quantityList = $this->DefectiveKioskProduct->find('list', array('conditions' => array('DefectiveKioskProduct.id' => $faultyIds), 'fields' => array('id', 'quantity')));
                                $productIdList = $this->DefectiveKioskProduct->find('list', array('conditions' => array('DefectiveKioskProduct.id' => $faultyIds), 'fields' => array('id', 'product_id')));
                                    $lessProducts = array();
                                    $lowProducts = array();
                                    foreach($this->request->data['CheckOut'] as $faultyId => $faulty_qtt){
                                            if($faulty_qtt == 0 || !(int)$faulty_qtt){
                                                $lowProducts[] = $productArr[$productIdList[$faultyId]];
                                            }
                                            
                                            if($faulty_qtt > $quantityList[$faultyId]){
                                                    $lessProducts[] = $productArr[$productIdList[$faultyId]];
                                            }
                                    }
                                    if(count($lessProducts) >= 1){
                                            $this->Session->setFlash("Please choose quantity less than or equal to the available stock for product: ".implode(", ",$lessProducts));
                                            return $this->redirect(array('action' => "consolidate_check_out?kioskDropdown=$kiosk_id"));
                                    }
                                    if(count($lowProducts) > 0){
                                             $this->Session->setFlash("Please choose quantity more than 0 for product: ".implode(", ",$lowProducts) );
                                            return $this->redirect(array('action' => "consolidate_check_out?kioskDropdown=$kiosk_id"));
                                    } else{
                                            $requestedQuantity = $this->request->data['CheckOut'];
                                            $newArray = array();
                                            foreach($session_basket as $product_id => $productData){
                                                foreach($productData as $faulty_id => $faulty_Qtt){
                                                    $newArray[$product_id][$faulty_id] = $requestedQuantity[$faulty_id];
                                                }
                                            }
                                            $this->Session->delete('consolidate_faulty');
                                            $this->Session->write('consolidate_faulty',$newArray);
                                            $this->Session->setFlash("Quantity has been basket successfully updated");
                                            return $this->redirect(array('action' => "consolidate_check_out?kioskDropdown=$kiosk_id"));
                                    }
                            }elseif(array_key_exists('edit_basket',$this->request->data)){
                                    return $this->redirect(array('action'=>"consolidate_faulty?kioskDropdown=$kioskId"));
                            }
                    }
                    $this->set(compact('productArr','productCode','productIds'));
            }else{
                $this->Flash->error("Please add products to the basket to proceed");
                return $this->redirect(array('action'=>"consolidate_faulty"));
            }
    }
	
	public function deleteProductFromCondolidateSession($productId='', $defectiveId='', $kioskId = ''){
        $session_basket = $this->request->Session()->read('consolidate_faulty');
        unset($session_basket[$productId][$defectiveId]);
        if(array_key_exists($productId,$session_basket) && count($session_basket[$productId]) == 0){
            //if session basket only contains 1 raw id corresponding to a product id and that too is being unset,
            //then deleting the entire product id from the session
            $this->request->Session()->delete("consolidate_faulty.$productId");
        }else{
            //deleting the old basket and writting the new one
            $this->request->Session()->delete('consolidate_faulty');
            $this->request->Session()->write('consolidate_faulty', $session_basket);
        }
        $this->SessionRestore->update_session_backup_table($this->request->params['controller'], 'consolidate_faulty', 'consolidate_faulty', $this->request->Session()->read('consolidate_faulty'), $kioskId);
        if(count($this->request->Session()->read('consolidate_faulty'))){
            return $this->redirect(array('action'=>"consolidate_check_out?kioskDropdown=$kioskId"));
        }else{
            return $this->redirect(array('action'=>"consolidate_faulty?kioskDropdown=$kioskId"));
        }
    }
	
	 private function check_duplicate_reference($modelName = '', $reference = '', $redirectAction = ''){
        $countDuplicate_query = $this->$modelName->find('all', array('conditions' => array($modelName.'.reference' => $reference)));
		$countDuplicate = $countDuplicate_query->count();
        if($countDuplicate > 0){
            $this->Flash->error('Entered reference already exists. Please enter a unique reference');
            return $this->redirect(array('action' => $redirectAction));
		  die;
        }
    }
	
	   private function send_to_central_bin($productId = '', $unreceived_quantity = ''){
			   $productListBin_query = $this->DefectiveBin->find('list', array(
																		  'keyField' => 'product_id',
																		  'valueField' => 'quantity'
																		  ));
			   $productListBin_query = $productListBin_query->hydrate(false);
			   if(!empty($productListBin_query)){
					$productListBin = $productListBin_query->toArray();
			   }else{
					$productListBin = array();
			   }
			   if(array_key_exists($productId,$productListBin)){
					   //updating the quantity
					   $date_time = $this->get_time();
					   $query = "UPDATE `defective_bin` SET quantity = quantity + $unreceived_quantity AND modified = '$date_time' WHERE product_id = $productId";
					   $conn = ConnectionManager::get('default');
					  $stmt = $conn->execute($query);
					  
					   //$this->DefectiveBin->updateAll(array('DefectiveBin.quantity' => "DefectiveBin.quantity + $unreceived_quantity", 'DefectiveBin.modified' => "'$date_time'"), array('DefectiveBin.product_id' => $productId));
			   }else{
					   //$this->DefectiveBin->clear();
					   //insert a row
					   $binData = array(
							   'product_id' => $productId,
							   'quantity' => $unreceived_quantity
					   );
					   $DefectiveBinEntity = $this->DefectiveBin->newEntity($binData,['validate' => false]);
					   $DefectiveBinEntity = $this->DefectiveBin->patchEntity($DefectiveBinEntity,$binData,['validate' => false]);
					   $this->DefectiveBin->save($DefectiveBinEntity);
			   }
	}
    
    public function move_2_bin($dataArray = '', $id = '', $quantity = '',$checkedId = '',$originalQuantity = ''){
        $array = json_decode($dataArray,true);
        $costPriceList_query = $this->Products->find('list',[
                                                       'keyFields' => 'id',
                                                       'valueFields' => 'cost_price'
                                                      ]
                                               );
        $costPriceList_query = $costPriceList_query->hydrate(false);
        if(!empty($costPriceList_query)){
            $costPriceList = $costPriceList_query->toArray();
        }else{
            $costPriceList = array();
        }
        $singleProductCost = $costPriceList[$array['product_id']];
        $totalProductCost = $singleProductCost*$quantity;
        
        //creating defective reference
        $reference = "Refused by admin_".time();
        $binReferenceData = array(
                    'reference' => $reference,
                    'kiosk_id' => $array['kiosk_id'],
                    'user_id' => $this->Auth->user('id'),
                    'total_cost' => $totalProductCost
                         );
        $new_entity = $this->DefectiveBinReferences->newEntity($binReferenceData,['validate' => false]);
        $patch_entity = $this->DefectiveBinReferences->patchEntity($new_entity,$binReferenceData,['validate' => false]);
        $this->DefectiveBinReferences->save($patch_entity);
        $binReferenceId = $patch_entity->id;
                    
        $transientBinProductData = array(
                                'kiosk_id' => $array['kiosk_id'],
                                'defective_bin_reference_id' => $binReferenceId,
                                'user_id' => $this->Auth->user('id'),
                                'product_id' => $array['product_id'],
                                'quantity' => $quantity,
                                'total_product_cost' => $totalProductCost,
                                'single_product_cost' => $singleProductCost,
                                'status' => 1,//now we are saving the products directly to the central bin along with transient. so not saving the status 0
                                          );
        $countBinTransientSaved = 0;
        $newEntity = $this->DefectiveBinTransients->newEntity($transientBinProductData,['validate' => false]);
        $patchEntity = $this->DefectiveBinTransients->patchEntity($newEntity,$transientBinProductData,['validate' => false]);
        if($this->DefectiveBinTransients->save($patchEntity)){
            $datetime = $this->get_time();
            $defectiveTable = "defective_kiosk_references";
            $user_id = $this->Auth->user('id');
             //$this->DefectiveKioskReference->query("UPDATE `$defectiveTable` SET `date_of_receiving` = '$datetime' , `received_by` = $user_id WHERE `id` = $id");
             
            $conn = ConnectionManager::get('default');
            $stmt = $conn->execute("UPDATE `$defectiveTable` SET `date_of_receiving` = '$datetime' , `received_by` = $user_id WHERE `id` = $id"); 
            if($quantity != $originalQuantity){
                $qtt2update = $originalQuantity-$quantity;
                $this->DefectiveKioskTransients->updateAll(array('quantity' => "'$qtt2update'"), array('defective_kiosk_reference_id' => $id, 'id' => $checkedId));
            }else{
                $del_id = $this->DefectiveKioskTransients->get($checkedId);
                $this->DefectiveKioskTransients->delete($del_id);
            }
            $countBinTransientSaved = 1;
        }
        
        $movedToCentral = 0;
        if($countBinTransientSaved == 1){
            //sending to central bin from here
            $this->send_to_central_bin($array['product_id'], $quantity);
            $movedToCentral = 1;
        }
        return $movedToCentral;
    }
    public function test(){
			if(!empty($this->request->query)){
              //  pr($this->request->query);die;
				$selected_id = $this->request->query['checked_id'];
				$reference = $this->request->query['reference'];
				$quantity = $this->request->query['quantity'];
				$product_id = $this->request->query['product_id'];
				$originalQuantity = $this->request->query['original_quantity'];
				$id = $this->request->query['pass_id'];
				$json = $this->request->query['json'];
				
				if(array_key_exists('Receive',$this->request->query)){
                    
					// updating central table quantity
					$jsonDataArr = json_decode($json);
                   // pr($jsonDataArr);
					$kioskID = (int)$jsonDataArr->kiosk_id;
					$productID = (int)$jsonDataArr->product_id;
					$refID = (int)$jsonDataArr->defective_kiosk_reference_id;
                    $conn = ConnectionManager::get('default');
					 $query = "SELECT `id` FROM `defective_kiosk_products` WHERE `kiosk_id` = $kioskID AND `product_id` = $productID AND `defective_kiosk_reference_id` = $refID AND `receive_date` IS NULL ORDER BY `id` ASC LIMIT 0,$quantity";
					//die;
                    $stmt = $conn->execute($query);
                    $queryRS = $stmt ->fetchAll('assoc');
                    //$queryRS = $this->DefectiveKioskProduct->query($query);
					// pr($queryRS);die;
					$date_time = $this->get_time();
					$queryArr = array();
					foreach($queryRS as $defProd){
						$defID = $defProd['id'];
						$receivedBy = $this->request->session()->read('Auth.User.id');
                        
						$updateQry = "UPDATE `defective_kiosk_products` SET `received_by` = $receivedBy, `receive_date` = '$date_time' WHERE `id` = $defID";
						$stmt1 = $conn->execute($updateQry);
                       // $queryRS = $stmt1->fetchAll('assoc');
                        //$queryRS = $this->DefectiveKioskProduct->query($updateQry);
					}
					/* Sample Data: {"DefectiveKioskTransient":{"id":"72","kiosk_id":"2","defective_kiosk_reference_id":"44","user_id":"1","product_id":"1","quantity":"2","status":"0","created":"2016-05-28 10:45:11","modified":"2016-05-28 10:45:11"}}
					 */
		   
					//echo "^^^";die;
					$countAdded = $this->update_central_table($product_id, $quantity);
					$msg = array();
					if($countAdded > 0){
                       
						if($quantity == $originalQuantity){
							//if all quantity is received. e.g. (4 out of 4)
							 $remainingQty = $originalQuantity - $quantity;
							 
							if($this->DefectiveKioskTransients->updateAll(array('status' => 1,'quantity' => $remainingQty), array('defective_kiosk_reference_id' => $id, 'product_id' => $product_id))){
                                
	
								/*
								 UPDATE `ADMIN_DOMAIN`.`defective_kiosk_transients` AS `DefectiveKioskTransient` SET `DefectiveKioskTransient`.`status` = '1' WHERE `DefectiveKioskTransient`.`defective_kiosk_reference_id` = 34 AND `DefectiveKioskTransient`.`product_id` = 5397
								 */
								//--------code for reading cake query---
								$datetime = $this->get_time();
                                
								$defectiveReferenceData = array(
														'id' => $id,
														'date_of_receiving' => $datetime,
														'received_by' => $this->request->session()->read('Auth.User.id'),
														'status' => 1//1 is for receiving status
																);
                                 $DefectiveKioskReferencesEntity = $this->DefectiveKioskReferences->get($id);
                               	$DefectiveKioskReferencesEntity = $this->DefectiveKioskReferences->patchEntity($DefectiveKioskReferencesEntity,$defectiveReferenceData,['validate' => false]);
                               // pr($DefectiveKioskReferencesEntity);die;
									$this->DefectiveKioskReferences->save($DefectiveKioskReferencesEntity);
								//$this->DefectiveKioskReferences->save($defectiveReferenceData);
								//--------code for reading cake query---
								 
								/*
								 UPDATE `ADMIN_DOMAIN`.`defective_kiosk_references` SET `id` = 34, `date_of_receiving` = '2016-05-28 06:55:57', `received_by` = 1, `status` = 1, `modified` = '2016-05-28 06:55:57' WHERE `ADMIN_DOMAIN`.`defective_kiosk_references`.`id` = '34'
								 */
								//--------code for reading cake query---
								$msg['success'] = "Product has been successfully received!";
							}//status 1 for received
						}else{
							//if some quantity is received. e.g. (2 out of 4)
							$remainingQty = $originalQuantity - $quantity;
							if($this->DefectiveKioskTransients->updateAll(array('quantity' => $remainingQty), array('defective_kiosk_reference_id' => $id, 'product_id' => $product_id))){
								//--------code for reading cake query---
								 
								//--------code for reading cake query---
								$datetime = $this->get_time();
								$defectiveReferenceData = array(
														'id' => $id,
														'date_of_receiving' => $datetime,
														'received_by' => $this->request->session()->read('Auth.User.id'),
														'status' => 1//1 is for receiving status
																);
                                $defectiveReferenceDataEntity = $this->DefectiveKioskReferences->get($id);
                               $DefectiveKioskReferencesEntity = $this->DefectiveKioskReferences->patchEntity($defectiveReferenceDataEntity,$defectiveReferenceData,['validate' => false]);
									$this->DefectiveKioskReferences->save($DefectiveKioskReferencesEntity);
								//$this->DefectiveKioskReferences->save($defectiveReferenceData);
								//--------code for reading cake query---
								 
								//echo '<br/>DefectiveKioskTransient#else:'.$getLog['query'];
								//--------code for reading cake query---
								$msg['success'] = "Product has been successfully received!";
							}
						}
					}else{
						$msg['error'] = "Product could not be received!";
					}
				}elseif(array_key_exists('move_to_bin',$this->request->query)){
					 $movedToCentral = $this->move_2_bin($json,$id,$quantity,$selected_id,$originalQuantity);
					if($movedToCentral == 1){
						$msg['success'] = "Product has been successfully moved to bin!";
					}else{
						$msg['error'] = "Product could not be moved to bin!";
					}
				}
				echo json_encode($msg);die;
			}else{
				$msg['error'] = "no query found";
				echo json_encode($msg);die;
			}
	}
    
    
    
}
?>