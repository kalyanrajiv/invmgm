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

class ProductSellStatsController extends AppController
{
        public $helpers = [
         'Paginator' => ['templates' => 'paginatortemplates']
         ];
    public function initialize(){
        parent::initialize();
        $this->loadComponent('ScreenHint');
        $this->loadModel('ProductReceipts');
        $this->loadModel('Customers');
        $this->loadModel('PaymentDetails');
        $this->loadModel('Users');
		$this->loadModel('KioskProductSales');
        $this->loadModel('Categories');
        $this->loadModel('Kiosks');
        $this->loadModel('PaymentDetails');
        $this->loadModel('ProductPayments');
        $this->loadModel('ProductReceipts');
        $this->loadModel('ProductSaleStats');
        $this->loadModel('ProductSellStats');
        $CURRENCY_TYPE = Configure::read('CURRENCY_TYPE');
		$this->set(compact('CURRENCY_TYPE'));
    }
    
    public function index(){
		$loggedInUser = $this->request->session()->read('Auth.User.username');
        if (preg_match('/'.QUOT_USER_PREFIX.'/', $loggedInUser)){//checking if the user has right for tempering
            $productsSellStateTable = TableRegistry::get("product_sell_stats_new",[
																		'table' => "product_sell_stats_new",
																	]);
            $productsSaleStateTable = TableRegistry::get("product_sale_stats_new",[
																		'table' => "product_sale_stats_new",
																	]);
           // $this->ProductSellStat->setSource('t_product_sell_stats');
           // $this->ProductSaleStat->setSource('t_product_sale_stats');
        }else{
            $productsSellStateTable = TableRegistry::get("product_sell_stats_new",[
																		'table' => "product_sell_stats_new",
																	]);
            $productsSaleStateTable = TableRegistry::get("product_sale_stats_new",[
																		'table' => "product_sale_stats_new",
																	]);
        }
		//pr($this->request);
		$start_date = $end_date = $search_kw = $kiosk_id = "";
		$conditionArr = array();
		if(array_key_exists('start_date',$this->request->query)){
			$start_date = $this->request->query['start_date'];
			$this->set('start_date',$this->request->query['start_date']);
		}
        
        
        
		if(array_key_exists('end_date',$this->request->query)){
			$end_date = $this->request->query['end_date'];
			$this->set('end_date',$this->request->query['end_date']);
		}
		if(!empty($start_date) && !empty($end_date)){
			$conditionArr = array(
						"created >" => date('Y-m-d', strtotime($start_date)),
						"created <" => date('Y-m-d', strtotime($end_date. ' +1 Days')),			
					       );
		}else{
			$conditionArr = array(
						"created >=" => date('Y-m-d'),
						"created <" => date('Y-m-d',strtotime(' +1 day')),	
					       );
		}
		if(array_key_exists('category_id',$this->request->query)&& !empty($this->request->query['category_id'])){
			$category_ids = $this->request->query['category_id'];
			if(!empty($category_ids)){
				$category_id = explode("_",$category_ids);
			}
			$conditionArr['category_id IN'] = $category_id;
			$this->set(compact('category_id'));
		}
		//pr($conditionArr);die;
        if(array_key_exists('category_id IN',$conditionArr)){
			$selectedCategoryId = $conditionArr['category_id IN'];
		}else{
            $selectedCategoryId = array(0 => 0);
        }
		
		
		
		if(array_key_exists('search_kw',$this->request->query)){
			$search_kw = $this->request->query['search_kw'];
			$searchW = strtolower($search_kw);
			if(!empty($searchW)){
                $conn = ConnectionManager::get('default');
                $stmt = $conn->execute("SELECT `id` from `products` WHERE LOWER(`product_code`) like ('%{$searchW}%') or LOWER(`product`) like ('%{$searchW}%')");
                $productResult = $stmt ->fetchAll('assoc');
				//$productResult = $this->Product->query("SELECT `id` from `products` WHERE LOWER(`product_code`) like ('%{$searchW}%') or LOWER(`product`) like ('%{$searchW}%')");
				
				$productIDs = array();
				
				foreach($productResult as $sngproductResult){
					$productIDs[] = $sngproductResult['id'];
				}
				if(!empty($productIDs)){
					$conditionArr['product_id IN'] = $productIDs;
				}
			}
			$this->set('search_kw',$this->request->query['search_kw']);
		}
        
         if (preg_match('/'.QUOT_USER_PREFIX.'/',$loggedInUser)){//checking if the user has right for tempering
            //$conditionArr['status'] = $category_id;
            }else{
                $conditionArr['status'] = 0;
            }
            //pr($this->request->query);die;
            if(array_key_exists('type',$this->request->query)){
                $type = $this->request->query['type'];
                if($type == "special"){
                    $conditionArr['status'] = 1;
                }elseif($type == "normal"){
                    $conditionArr['status'] = 0;
                }elseif($type == "both"){
                    //$conditionArr['status'] = 0;
                }
            }
        
        
		//pr($conditionArr);die;
		$kiosks_query = $this->Kiosks->find('list',[
                                                'keyField' => 'id',
                                                'valueField' => 'name',
                                                'conditions' => ['Kiosks.status' => 1],
                                                'order' => ['Kiosks.name asc']
                                             ]);
        $kiosks_query = $kiosks_query->hydrate(false);
        if(!empty($kiosks_query)){
            $kiosks = $kiosks_query->toArray();
        }else{
            $kiosks = array();
        }
        
        
        $sale_query = $productsSaleStateTable->find('all',['conditions' => $conditionArr]);
                  $sale_query
                          ->select(['total_sale_price' => $sale_query->func()->sum('selling_price')])
                          ->select('product_id')
                          ->group('product_id');
        //pr($sale_query);die;
        if(!empty($sale_query)){
            $sale_res = $sale_query->toArray();
        }else{
            $sale_res = array();
        }
        
        $cost_query = $productsSaleStateTable->find('all',['conditions' => $conditionArr]);
                  $cost_query
                          ->select(['total_cost_price' => $cost_query->func()->sum('cost_price')])
                          ->select('product_id')
                          ->group('product_id');
        if(!empty($cost_query)){
            $cost_res = $cost_query->toArray();
        }else{
            $cost_res = array();
        }
	
        $vat_query = $productsSaleStateTable->find('all',['conditions' => $conditionArr]);
                  $vat_query
                          ->select(['total_vat' => $vat_query->func()->sum('vat')])
                          ->select('product_id')
                          ->group('product_id');
                          //pr($vat_query);die;
        if(!empty($vat_query)){
            $vat_res = $vat_query->toArray();
        }else{
            $vat_res = array();
        }
		
		$final_cost_price = $final_vat_price = $final_sale_price = 0;
		if(!empty($sale_res)){
			foreach($sale_res as $s_key => $s_value){
				$final_sale_price += $s_value['total_sale_price'];
			}
		}
		
		if(!empty($cost_res)){
			foreach($cost_res as $c_key => $c_value){
				$final_cost_price += $c_value['total_cost_price'];
			}
		}
		
		if(!empty($vat_res)){
			foreach($vat_res as $v_key => $v_value){
				$final_vat_price += $v_value['total_vat'];
			}
		}
		if(array_key_exists('bulk_invoice',$this->request->query)){
			$bulk_invoice = $this->request->query["bulk_invoice"];
			if(!empty($bulk_invoice)){
				$conditionArr["bulk_invoice"] =  $bulk_invoice;
			}
		}
		
		
		if(true){ //$status == 1
           
            $query = $productsSaleStateTable->find()->where(
                                                             $conditionArr
                                                           );
                  $query
                          ->select('product_id')
                          ->select(['quantity' => $query->func()->sum('quantity')])
                          ->select('product_code')
                          ->select(['cost_price' => $query->func()->sum('cost_price')])
                          ->select(['selling_price' => $query->func()->sum('selling_price')])
                          ->select(['vat' => $query->func()->sum('vat')])
                          ->select('created')
                          ->group('product_id')
                          ->order('quantity desc');
                          //->limit(50);
						  //pr($query);die;
				//$query = $query->hydrate(false);
            $this->paginate = [
                                'limit' => 50    
                              ];
            
            //pr($query);die;
			$query_result = $this->paginate($query);
            if(!empty($query_result)){
                $result = $query_result->toArray();
            }else{
                $result = array();
            }
            //pr($result);die;
		}
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
		$categories = $this->CustomOptions->category_options($categories,true,$selectedCategoryId);
		
		$product_name_query = $this->Products->find('list',[
                                                            'keyField' => 'id',
                                                            'valueField' => 'product'
                                                            // 'conditions' => array('Product.id' =>$productIDs),
                                                            //'recursive' => -1
                                                           ]);
        if(!empty($product_name_query)){
            $product_name = $product_name_query->toArray();
        }else{
            $product_name = array();
        }
		$this->set(compact('result','kiosks','final_cost_price','final_vat_price','final_sale_price','categories','product_name'));
		if(true){  //$status == 1
			$this->render('combined1');
		}
    }
    
    public function search(){
		$loggedInUser = $this->request->session()->read('Auth.User.username');
         if (preg_match('/'.QUOT_USER_PREFIX.'/',$loggedInUser)){//checking if the user has right for tempering
            $productsSellStateSource = "product_sell_stats_new";
            $productsSellStateTable = TableRegistry::get("product_sell_stats_new",[
																		'table' => "product_sell_stats_new",
																	]);
            $productsSaleStateTable = TableRegistry::get("product_sale_stats_new",[
																		'table' => "product_sale_stats_new",
																	]);
           // $this->ProductSellStat->setSource('t_product_sell_stats');
           // $this->ProductSaleStat->setSource('t_product_sale_stats');
        }else{
            $productsSellStateSource = "product_sell_stats_new";
            $productsSellStateTable = TableRegistry::get("product_sell_stats_new",[
																		'table' => "product_sell_stats_new",
																	]);
            $productsSaleStateTable = TableRegistry::get("product_sale_stats_new",[
																		'table' => "product_sale_stats_new",
																	]);
        }
		$start_date = $end_date = $search_kw = $kiosk_id = "";
		$conditionArr = array();
		if(array_key_exists('start_date',$this->request->query)){
			$start_date = $this->request->query['start_date'];
			$this->set('start_date',$this->request->query['start_date']);
		}
        
         if (preg_match('/'.QUOT_USER_PREFIX.'/',$loggedInUser)){//checking if the user has right for tempering
            //$conditionArr['status'] = $category_id;
        }else{
			$conditionArr['status'] = 0;
		}
        
		if(array_key_exists('end_date',$this->request->query)){
			$end_date = $this->request->query['end_date'];
			$this->set('end_date',$this->request->query['end_date']);
		}
        
         $type = "";
		if(array_key_exists('type',$this->request->query)){
			$type = $this->request->query['type'];
			if($type == "special"){
				$conditionArr['status'] = 1;
			}elseif($type == "normal"){
				$conditionArr['status'] = 0;
			}elseif($type == "both"){
				//$conditionArr['status'] = 0;
			}
		}
        
		if(!empty($start_date) && !empty($end_date)){
			$conditionArr[] = array(
						"created >" => date('Y-m-d', strtotime($start_date)),
						"created <" => date('Y-m-d', strtotime($end_date. ' +1 Days')),			
					       );
		}
		$category_ids = "";
        $category_id = array();
        //pr($this->request);die;
		if(array_key_exists('category',$this->request->query)&& !empty($this->request->query['category'][0])){
			$category_id = $this->request->query['category'];
			foreach($category_id as $cat_key => $cat_value){
				if($cat_value == 0){
					unset($category_id[$cat_key]);
				}
			}
            //pr($category_id);die;
			if(!empty($category_id)){
				$category_ids = implode("_",$category_id);
				$conditionArr['category_id IN'] = $category_id;
			}
			$this->set(compact('category_id'));
		}
		
		//pr($category_ids);die;
		if(array_key_exists('search_kw',$this->request->query)){
			$search_kw = $this->request->query['search_kw'];
			$searchW = strtolower($search_kw);
			if(!empty($searchW)){
                $conn = ConnectionManager::get('default');
                $stmt = $conn->execute("SELECT `id` from `products` WHERE LOWER(`product_code`) like ('%{$searchW}%') or LOWER(`product`) like ('%{$searchW}%')");
                $productResult = $stmt ->fetchAll('assoc');
				//$productResult = $this->Product->query("SELECT `id` from `products` WHERE LOWER(`product_code`) like ('%{$searchW}%') or LOWER(`product`) like ('%{$searchW}%')");
				
				$productIDs = array();
				
				foreach($productResult as $sngproductResult){
					$productIDs[] = $sngproductResult['id'];
				}
				if(!empty($productIDs)){
					$conditionArr['product_id IN'] = $productIDs;
				}
			}
			$this->set('search_kw',$this->request->query['search_kw']);
		}
		
        //pr($this->request);die;
			if(array_key_exists('ProductSale',$this->request->query)){
				if(array_key_exists('kiosk_id',$this->request->query['ProductSale'])){
							$kiosk_id = $this->request->query['ProductSale']['kiosk_id'];
							if($kiosk_id != -1){
								$conditionArr['kiosk_id'] = $kiosk_id;
							}
							$this->set('kioskId',$this->request->query['ProductSale']['kiosk_id']);
				}
			}
			
			if(array_key_exists('bulk_invoice',$this->request->query)){
						$bulk_invoice = $this->request->query["bulk_invoice"];
						if(!empty($bulk_invoice)){
							$conditionArr["bulk_invoice"] =  $bulk_invoice;		
						}
					    
		    }
		if(!empty($kiosk_id) && $kiosk_id == -1){
			return $this->redirect("/ProductSellStats/index?start_date=$start_date&end_date=$end_date&kiosk_id=-1&search_kw=$search_kw&category_id=$category_ids&type=$type&bulk_invoice=$bulk_invoice");die;
		}
		
        //pr($conditionArr);die;
         $sale_query = $productsSellStateTable->find('all',['conditions' => $conditionArr]);
                  $sale_query
                          ->select(['total_sale_price' => $sale_query->func()->sum('selling_price')])
                          ->select('product_id')
                          ->group('product_id');
        if(!empty($sale_query)){
            $sale_res = $sale_query->toArray();
        }else{
            $sale_res = array();
        }
        
        $cost_query = $productsSellStateTable->find('all',['conditions' => $conditionArr]);
                  $cost_query
                          ->select(['total_cost_price' => $cost_query->func()->sum('cost_price')])
                          ->select('product_id')
                          ->group('product_id');                    
        if(!empty($cost_query)){
            $cost_res = $cost_query->toArray();
        }else{
            $cost_res = array();
        }
	
	
	
        $vat_query = $productsSellStateTable->find('all',['conditions' => $conditionArr]);
                  $vat_query
                          ->select(['total_vat' => $vat_query->func()->sum('vat')])
                          ->select('product_id')
                          ->group('product_id');                    
        if(!empty($vat_query)){
            $vat_res = $vat_query->toArray();
        }else{
            $vat_res = array();
        }
	
		$final_cost_price = $final_vat_price = $final_sale_price = 0;
		if(!empty($sale_res)){
			foreach($sale_res as $s_key => $s_value){
				$final_sale_price += $s_value['total_sale_price'];
			}
		}
		
		if(!empty($cost_res)){
			foreach($cost_res as $c_key => $c_value){
				$final_cost_price += $c_value['total_cost_price'];
			}
		}
		
		if(!empty($vat_res)){
			foreach($vat_res as $v_key => $v_value){
				$final_vat_price += $v_value['total_vat'];
			}
		}
		
		
		
		$query = $productsSellStateTable->find()->where(
                                                             $conditionArr
                                                           );
                  $query
                          ->select('product_id')
                          ->select(['quantity' => $query->func()->sum('quantity')])
                          ->select('product_code')
                          ->select(['cost_price' => $query->func()->sum('cost_price')])
                          ->select(['selling_price' => $query->func()->sum('selling_price')])
                          ->select(['vat' => $query->func()->sum('vat')])
                          ->select('created')
                          ->group('product_id')
                          ->order('quantity desc');
		
		//unset($conditionArr['kiosk_id']);
		$this->paginate = [
                            //'conditions' => $conditionArr,
                            //'order' => ['quantity desc'],
							//'group' => ["product_id"],
                            'limit' => 100
                          ];

		$kiosks = $this->Kiosks->find('list',[
                                                'keyField' => 'id',
                                                'valueField' => 'name',
                                                'conditions' => ['Kiosks.status' => 1],
                                                'order' => ['Kiosks.name asc']
                                            ]);
        if(!empty($kiosks)){
            $kiosks = $kiosks->toArray();
        }else{
            $kiosks = array();
        }
        
		$result = $this->paginate($query);
		
		
		$categories_query = $this->Categories->find('all',array(
								'fields' => array('id', 'category','id_name_path'),
								'conditions' => array('Categories.status' => 1),
								'order' => ['Categories.category asc'],
								//'recursive' => -1
								));
        $categories_query = $categories_query->hydrate(false);
        if(!empty($categories_query)){
            $categories = $categories_query->toArray();
        }else{
            $categories = array();
        }
        //pr($category_id);die;
		$categories = $this->CustomOptions->category_options($categories,true,$category_id);
		$product_name = $this->Products->find('list',[
                                                        'keyField' => 'id',
                                                        'valueField' => 'product'
                                                        //'recursive' => -1
                                                    ]);
		if(!empty($product_name)){
            $product_name = $product_name->toArray();
        }else{
            $product_name = array();
        }
		
		$this->set(compact('result','kiosks','final_sale_price','final_cost_price','final_vat_price','categories','product_name'));
		//$this->render('index');
    }
    
    public function export(){
		$loggedInUser = $this->request->session()->read('Auth.User.username');
        if (preg_match('/'.QUOT_USER_PREFIX.'/',$loggedInUser)){//checking if the user has right for tempering
            //$this->ProductSellStat->setSource('t_product_sell_stats');
            $ProductSellStat_source = "product_sale_stats_new";
            $ProductSellStatTable = TableRegistry::get($ProductSellStat_source,[
                                                                        'table' => $ProductSellStat_source,
                                                                    ]);
            //$this->ProductSaleStat->setSource('t_product_sale_stats');
            $ProductSaleStat_source = "product_sale_stats_new";
            $ProductSaleStatTable = TableRegistry::get($ProductSaleStat_source,[
                                                                        'table' => $ProductSaleStat_source,
                                                                    ]);
        }
		$ProductSellStat_source = "product_sale_stats_new";
            $ProductSellStatTable = TableRegistry::get($ProductSellStat_source,[
                                                                        'table' => $ProductSellStat_source,
                                                                    ]);
            //$this->ProductSaleStat->setSource('t_product_sale_stats');
            $ProductSaleStat_source = "product_sale_stats_new";
            $ProductSaleStatTable = TableRegistry::get($ProductSaleStat_source,[
                                                                        'table' => $ProductSaleStat_source,
                                                                    ]);
		//pr($this->request);
		$start_date = $end_date = $search_kw = $kiosk_id = "";
		$conditionArr = array();
		if(array_key_exists('start_date',$this->request->query)){
			$start_date = $this->request->query['start_date'];
			$this->set('start_date',$this->request->query['start_date']);
		}
        
       
        
		if(array_key_exists('end_date',$this->request->query)){
			$end_date = $this->request->query['end_date'];
			$this->set('end_date',$this->request->query['end_date']);
		}
        
       
        
		if(!empty($start_date) && !empty($end_date)){
			$conditionArr[] = array(
						"created >" => date('Y-m-d', strtotime($start_date)),
						"created <" => date('Y-m-d', strtotime($end_date. ' +1 Days')),			
					       );
		}else{
			$conditionArr[] = array(
						"created >=" => date('Y-m-d'),
						"created <" => date('Y-m-d',strtotime(' +1 day')),	
					       );
		}
		
		if(array_key_exists('category_id',$this->request->query)&& !empty($this->request->query['category_id'])){
			$category_ids = $this->request->query['category_id'];
			if(!empty($category_ids)){
				$category_id = explode("_",$category_ids);
			}
			$conditionArr['category_id IN'] = $category_id;
			$this->set(compact('category_id'));
		}
		
		if(array_key_exists('search_kw',$this->request->query)){
			$search_kw = $this->request->query['search_kw'];
			$searchW = strtolower($search_kw);
			if(!empty($searchW)){
                $conn = ConnectionManager::get('default');
                $stmt = $conn->execute("SELECT `id` from `products` WHERE LOWER(`product_code`) like ('%{$searchW}%') or LOWER(`product`) like ('%{$searchW}%')"); 
                $productResult = $stmt ->fetchAll('assoc');
				//$productResult = $this->Product->query("SELECT `id` from `products` WHERE LOWER(`product_code`) like ('%{$searchW}%') or LOWER(`product`) like ('%{$searchW}%')");
				
				$productIDs = array();
				
				foreach($productResult as $sngproductResult){
					$productIDs[] = $sngproductResult['id'];
				}
				if(!empty($productIDs)){
					$conditionArr['product_id IN'] = $productIDs;
				}
			}
			$this->set('search_kw',$this->request->query['search_kw']);
		}
		//pr($this->request);die;
		if(array_key_exists('type',$this->request->query)){
                $type = $this->request->query['type'];
				if($type != ""){
						$conditionArr['status'] = $type;	
				}
				
                //if($type == "special"){
                //    $conditionArr['status'] = 1;
                //}elseif($type == "normal"){
                //    $conditionArr['status'] = 0;
                //}elseif($type == "both"){
                //    //$conditionArr['status'] = 0;
                //}
            }
		
		//pr($conditionArr);die;
		$kiosks_query = $this->Kiosks->find('list',[
                                                'keyField'=> 'id',
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
        
        $sale_res_query = $ProductSaleStatTable->find('all',['conditions' => $conditionArr,'group' => 'product_id']);
        $sale_res_query
              ->select('product_id')
              ->select(['total_sale_price' => $sale_res_query->func()->sum('selling_price')]);
        $sale_res_query = $sale_res_query->hydrate(false);
        if(!empty($sale_res_query)){
           $sale_res = $sale_res_query->toArray();
        }else{
            $sale_res = array();
        }
        
		//$sale_res = $this->ProductSaleStats->find('all',array('conditions' => $conditionArr,
		//										   'fields' => array('product_id','SUM(selling_price) as total_sale_price'),
		//										   'group' => 'product_id',
		//										   ));
        
        $cost_res_query = $ProductSaleStatTable->find('all',['conditions' => $conditionArr,'group' => 'product_id']);
        $cost_res_query
              ->select('product_id')
              ->select(['total_cost_price' => $cost_res_query->func()->sum('cost_price')]);
        $cost_res_query = $cost_res_query->hydrate(false);
        if(!empty($cost_res_query)){
           $cost_res = $cost_res_query->toArray();
        }else{
            $cost_res = array();
        }
		//$cost_res = $this->ProductSaleStats->find('all',array('conditions' => $conditionArr,
		//										   'fields' => array('product_id','SUM(cost_price) as total_cost_price'),
		//										   'group' => 'product_id',
		//										   ));
		//$vat_res = $this->ProductSaleStats->find('all',array('conditions' => $conditionArr,
		//										   'fields' => array('product_id','SUM(vat) as total_vat'),
		//										   'group' => 'product_id',
		//										   ));
        
        $vat_res_query = $ProductSaleStatTable->find('all',['conditions' => $conditionArr,'group' => 'product_id']);
        $vat_res_query
              ->select('product_id')
              ->select(['total_vat' => $vat_res_query->func()->sum('vat')]);
        $vat_res_query = $vat_res_query->hydrate(false);
        if(!empty($vat_res_query)){
           $vat_res = $vat_res_query->toArray();
        }else{
            $vat_res = array();
        }
		//$dbo = $this->ProductSellStat->getDatasource();$logData = $dbo->getLog();$getLog = end($logData['log']);echo $getLog['query'];
		//pr($sale_res);die;
		$final_cost_price = $final_vat_price = $final_sale_price = 0;
		if(!empty($sale_res)){
			foreach($sale_res as $s_key => $s_value){
				$final_sale_price += $s_value['total_sale_price'];
			}
		}
		
		if(!empty($cost_res)){
			foreach($cost_res as $c_key => $c_value){
				$final_cost_price += $c_value['total_cost_price'];
			}
		}
		
		if(!empty($vat_res)){
			foreach($vat_res as $v_key => $v_value){
				$final_vat_price += $v_value['total_vat'];
			}
		}
		//pr($conditionArr);die;
		if(true){ //$status == 1
            $ProductSaleStats_query = $ProductSaleStatTable->find('all',[
                                                                           'conditions' => $conditionArr,
                                                                           'order' => ['quantity desc'],
                                                                            'group' => 'product_id',
                                                                           // 'limit' => 50
                                                                           ]);
            $ProductSaleStats_query
                            ->select('product_id')
                            ->select('product_code')
                            ->select(['quantity' => $ProductSaleStats_query->func()->sum('quantity')])
                            ->select(['cost_price' => $ProductSaleStats_query->func()->sum('cost_price')])
                            ->select(['selling_price' => $ProductSaleStats_query->func()->sum('selling_price')])
							->select('created')
                            ->select(['vat' => $ProductSaleStats_query->func()->sum('vat')]);
//			$this->paginate = [
////                                'conditions' => $conditionArr,
////								'fields' => array('product_id','SUM(quantity) as quantity','product_code','SUM(cost_price) as cost_price','SUM(selling_price) as selling_price','SUM(vat) as vat','created'),
////								'order' => ['quantity desc'],
////								'group' => 'product_id',
//								'limit' => 50
//                              ];
//		//pr($ProductSaleStats_query);die;
//			$result_query = $this->paginate($ProductSaleStats_query);
				//$ProductSaleStats_query = $ProductSaleStats_query->hydrate(false);
            if(!empty($ProductSaleStats_query)){
                $result = $ProductSaleStats_query->toArray();
            }else{
                $result = array();
            }
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
		$categories = $this->CustomOptions->category_options($categories,true );
		
		$product_name_query = $this->Products->find('list',[
                                                                'keyField' => 'id',
                                                                'valueField' => 'product'
                                                           ]
                                              );
		$product_name_query = $product_name_query->hydrate(false);
        if(!empty($product_name_query)){
            $product_name = $product_name_query->toArray();
        }else{
            $product_name = array();
        }
		
		$total_gross_sale = $Gross_sale = $run_time_quantity = $run_time_cost = $run_time_selling_price = $run_time_vat = 0;
		$tmparray = array();
		if(!empty($result)){
			foreach($result as $key => $value){
                //pr($value);die;
				$product_code = $value->product_code;
                   $cost_price = $value->cost_price;
                   $selling_price_without_vat = $value->selling_price;
                   $vat =  $value->vat;
                   $created =  $value->created;
                   $quantity =  $value->quantity;
				   $product_id =  $value->product_id;
				   
				   
				   $product = $product_name[$product_id];
                   $run_time_quantity += $quantity;
                   $run_time_cost += $cost_price;
                   $run_time_selling_price += $selling_price_without_vat;
                   $run_time_vat += $vat;
                   $Gross_sale = $selling_price_without_vat + $vat;
                   $total_gross_sale += $Gross_sale;
				   $last_sold_on = date('d-m-y g:i A', strtotime($created));
				   $tmparray[] = array(
									   'Product' => $product,
									   'Product Code' => $product_code,
									   'Quantity' => $quantity,
									   'Cost Price' => $cost_price,
									   'Gross Sale' => $Gross_sale,
									   'Net Sale' => $selling_price_without_vat,
									   'Vat' => $vat,
									   'Last Sold On' =>  $last_sold_on,
									   ); 
			}
		}
		
		$this->outputCsv('kiosk_sale_state_'.time().".csv" ,$tmparray);
		$this->autoRender = false;
	}
	
	
	 public function exportKiosk(){
		$loggedInUser = $this->request->session()->read('Auth.User.username');
        if (preg_match('/'.QUOT_USER_PREFIX.'/',$loggedInUser)){//checking if the user has right for tempering
            //$this->ProductSellStat->setSource('t_product_sell_stats');
            $ProductSellStat_source = "product_sell_stats_new";
            $ProductSellStatTable = TableRegistry::get($ProductSellStat_source,[
                                                                        'table' => $ProductSellStat_source,
                                                                    ]);
            //$this->ProductSaleStat->setSource('t_product_sale_stats');
            $ProductSaleStat_source = "product_sell_stats_new";
            $ProductSaleStatTable = TableRegistry::get($ProductSaleStat_source,[
                                                                        'table' => $ProductSaleStat_source,
                                                                    ]);
        }
		$ProductSellStat_source = "product_sell_stats_new";
            $ProductSellStatTable = TableRegistry::get($ProductSellStat_source,[
                                                                        'table' => $ProductSellStat_source,
                                                                    ]);
            //$this->ProductSaleStat->setSource('t_product_sale_stats');
            $ProductSaleStat_source = "product_sell_stats_new";
            $ProductSaleStatTable = TableRegistry::get($ProductSaleStat_source,[
                                                                        'table' => $ProductSaleStat_source,
                                                                    ]);
		//pr($this->request);
		$start_date = $end_date = $search_kw = $kiosk_id = "";
		$conditionArr = array();
		if(array_key_exists('start_date',$this->request->query)){
			$start_date = $this->request->query['start_date'];
			$this->set('start_date',$this->request->query['start_date']);
		}
        
       
        
		if(array_key_exists('end_date',$this->request->query)){
			$end_date = $this->request->query['end_date'];
			$this->set('end_date',$this->request->query['end_date']);
		}
        
       
        
		if(!empty($start_date) && !empty($end_date)){
			$conditionArr[] = array(
						"created >" => date('Y-m-d', strtotime($start_date)),
						"created <" => date('Y-m-d', strtotime($end_date. ' +1 Days')),			
					       );
		}else{
			$conditionArr[] = array(
						"created >=" => date('Y-m-d'),
						"created <" => date('Y-m-d',strtotime(' +1 day')),	
					       );
		}
		
		if(array_key_exists('category_id',$this->request->query)&& !empty($this->request->query['category_id'])){
			$category_ids = $this->request->query['category_id'];
			if(!empty($category_ids)){
				$category_id = explode("_",$category_ids);
			}
			$conditionArr['category_id IN'] = $category_id;
			$this->set(compact('category_id'));
		}
		
		if(array_key_exists('search_kw',$this->request->query)){
			$search_kw = $this->request->query['search_kw'];
			$searchW = strtolower($search_kw);
			if(!empty($searchW)){
                $conn = ConnectionManager::get('default');
                $stmt = $conn->execute("SELECT `id` from `products` WHERE LOWER(`product_code`) like ('%{$searchW}%') or LOWER(`product`) like ('%{$searchW}%')"); 
                $productResult = $stmt ->fetchAll('assoc');
				//$productResult = $this->Product->query("SELECT `id` from `products` WHERE LOWER(`product_code`) like ('%{$searchW}%') or LOWER(`product`) like ('%{$searchW}%')");
				
				$productIDs = array();
				
				foreach($productResult as $sngproductResult){
					$productIDs[] = $sngproductResult['id'];
				}
				if(!empty($productIDs)){
					$conditionArr['product_id IN'] = $productIDs;
				}
			}
			$this->set('search_kw',$this->request->query['search_kw']);
		}
		//pr($this->request);die;
		if(array_key_exists('type',$this->request->query)){
                $type = $this->request->query['type'];
				if($type != ""){
						$conditionArr['status'] = $type;	
				}
				
                //if($type == "special"){
                //    $conditionArr['status'] = 1;
                //}elseif($type == "normal"){
                //    $conditionArr['status'] = 0;
                //}elseif($type == "both"){
                //    //$conditionArr['status'] = 0;
                //}
            }
		
		//pr($conditionArr);die;
		$kiosks_query = $this->Kiosks->find('list',[
                                                'keyField'=> 'id',
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
        
		$kiosk_id = $this->request->query['kiosk_id'];
		if($kiosk_id != -1){
			$conditionArr['kiosk_id'] = $kiosk_id;
		}
		
		
        $sale_res_query = $ProductSaleStatTable->find('all',['conditions' => $conditionArr,'group' => 'product_id']);
        $sale_res_query
              ->select('product_id')
              ->select(['total_sale_price' => $sale_res_query->func()->sum('selling_price')]);
        $sale_res_query = $sale_res_query->hydrate(false);
        if(!empty($sale_res_query)){
           $sale_res = $sale_res_query->toArray();
        }else{
            $sale_res = array();
        }
        
		//$sale_res = $this->ProductSaleStats->find('all',array('conditions' => $conditionArr,
		//										   'fields' => array('product_id','SUM(selling_price) as total_sale_price'),
		//										   'group' => 'product_id',
		//										   ));
        
        $cost_res_query = $ProductSaleStatTable->find('all',['conditions' => $conditionArr,'group' => 'product_id']);
        $cost_res_query
              ->select('product_id')
              ->select(['total_cost_price' => $cost_res_query->func()->sum('cost_price')]);
        $cost_res_query = $cost_res_query->hydrate(false);
        if(!empty($cost_res_query)){
           $cost_res = $cost_res_query->toArray();
        }else{
            $cost_res = array();
        }
		//$cost_res = $this->ProductSaleStats->find('all',array('conditions' => $conditionArr,
		//										   'fields' => array('product_id','SUM(cost_price) as total_cost_price'),
		//										   'group' => 'product_id',
		//										   ));
		//$vat_res = $this->ProductSaleStats->find('all',array('conditions' => $conditionArr,
		//										   'fields' => array('product_id','SUM(vat) as total_vat'),
		//										   'group' => 'product_id',
		//										   ));
        
        $vat_res_query = $ProductSaleStatTable->find('all',['conditions' => $conditionArr,'group' => 'product_id']);
        $vat_res_query
              ->select('product_id')
              ->select(['total_vat' => $vat_res_query->func()->sum('vat')]);
        $vat_res_query = $vat_res_query->hydrate(false);
        if(!empty($vat_res_query)){
           $vat_res = $vat_res_query->toArray();
        }else{
            $vat_res = array();
        }
		//$dbo = $this->ProductSellStat->getDatasource();$logData = $dbo->getLog();$getLog = end($logData['log']);echo $getLog['query'];
		//pr($sale_res);die;
		$final_cost_price = $final_vat_price = $final_sale_price = 0;
		if(!empty($sale_res)){
			foreach($sale_res as $s_key => $s_value){
				$final_sale_price += $s_value['total_sale_price'];
			}
		}
		
		if(!empty($cost_res)){
			foreach($cost_res as $c_key => $c_value){
				$final_cost_price += $c_value['total_cost_price'];
			}
		}
		
		if(!empty($vat_res)){
			foreach($vat_res as $v_key => $v_value){
				$final_vat_price += $v_value['total_vat'];
			}
		}
		//pr($conditionArr);die;
		if(true){ //$status == 1
            $ProductSaleStats_query = $ProductSaleStatTable->find('all',[
                                                                           'conditions' => $conditionArr,
                                                                           'order' => ['quantity desc'],
                                                                            'group' => 'product_id',
                                                                           // 'limit' => 50
                                                                           ]);
            $ProductSaleStats_query
                            ->select('product_id')
                            ->select('product_code')
                            ->select(['quantity' => $ProductSaleStats_query->func()->sum('quantity')])
                            ->select(['cost_price' => $ProductSaleStats_query->func()->sum('cost_price')])
                            ->select(['selling_price' => $ProductSaleStats_query->func()->sum('selling_price')])
							->select('created')
                            ->select(['vat' => $ProductSaleStats_query->func()->sum('vat')]);
//			$this->paginate = [
////                                'conditions' => $conditionArr,
////								'fields' => array('product_id','SUM(quantity) as quantity','product_code','SUM(cost_price) as cost_price','SUM(selling_price) as selling_price','SUM(vat) as vat','created'),
////								'order' => ['quantity desc'],
////								'group' => 'product_id',
//								'limit' => 50
//                              ];
//		//pr($ProductSaleStats_query);die;
//			$result_query = $this->paginate($ProductSaleStats_query);
				//$ProductSaleStats_query = $ProductSaleStats_query->hydrate(false);
            if(!empty($ProductSaleStats_query)){
                $result = $ProductSaleStats_query->toArray();
            }else{
                $result = array();
            }
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
		$categories = $this->CustomOptions->category_options($categories,true );
		
		$product_name_query = $this->Products->find('list',[
                                                                'keyField' => 'id',
                                                                'valueField' => 'product'
                                                           ]
                                              );
		$product_name_query = $product_name_query->hydrate(false);
        if(!empty($product_name_query)){
            $product_name = $product_name_query->toArray();
        }else{
            $product_name = array();
        }
		
		$total_gross_sale = $Gross_sale = $run_time_quantity = $run_time_cost = $run_time_selling_price = $run_time_vat = 0;
		$tmparray = array();
		if(!empty($result)){
			foreach($result as $key => $value){
                //pr($value);die;
				$product_code = $value->product_code;
                   $cost_price = $value->cost_price;
                   $selling_price_without_vat = $value->selling_price;
                   $vat =  $value->vat;
                   $created =  $value->created;
                   $quantity =  $value->quantity;
				   $product_id =  $value->product_id;
				   
				   
				   $product = $product_name[$product_id];
                   $run_time_quantity += $quantity;
                   $run_time_cost += $cost_price;
                   $run_time_selling_price += $selling_price_without_vat;
                   $run_time_vat += $vat;
                   $Gross_sale = $selling_price_without_vat + $vat;
                   $total_gross_sale += $Gross_sale;
				   $last_sold_on = date('d-m-y g:i A', strtotime($created));
				   $tmparray[] = array(
									   'Product' => $product,
									   'Product Code' => $product_code,
									   'Quantity' => $quantity,
									   'Cost Price' => $cost_price,
									   'Gross Sale' => $Gross_sale,
									   'Net Sale' => $selling_price_without_vat,
									   'Vat' => $vat,
									   'Last Sold On' =>  $last_sold_on,
									   ); 
			}
		}
		
		$this->outputCsv('kiosk_sale_state_'.time().".csv" ,$tmparray);
		$this->autoRender = false;
	}
}

