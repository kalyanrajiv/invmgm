<?php
namespace App\Controller;
use Cake\Core\App;
use App\Controller\AppController;
use Cake\Event\Event;
use Cake\Core\Configure;
use Cake\Core\Configure\Engine\PhpConfig;
use Cake\Network\Request;
use Cake\ORM\TableRegistry;
use Cake\Mailer\Email;
use Cake\I18n;
use Cake\Datasource\ConnectionManager;

class KioskOrdersController extends AppController
{
         public $helpers = [
         'Paginator' => ['templates' => 'paginatortemplates']
         ];
    public function initialize()
    {
        parent::initialize();
        $siteBaseURL = Configure::read('SITE_BASE_URL'); //rasu
       	$this->set(compact('siteBaseURL'));
        $this->loadComponent('ScreenHint');
        $this->loadComponent('SessionRestore');
        $this->loadModel('KioskPlacedOrders');
        $this->loadModel('OnDemandOrders');
        $this->loadModel('Products');
        $this->loadModel('Kiosks');
        $this->loadModel('KioskOrders');
        $this->loadModel('StockTransfer');
        $this->loadModel('KioskOrderProducts'); 
        $this->loadModel('DefectiveKioskReferences');
        $this->loadModel('DefectiveKioskTransients');
        $this->loadModel('Users');
        $this->loadModel('RevertStocks');
        $this->loadModel('ReservedProducts');
        $this->loadModel('KioskCancelledOrderProducts');
        $this->loadModel('MobileBlkReSales');
        $this->loadModel('Brands');
        $this->loadModel('MobileModels');
        $this->loadModel('KioskProductSales');
        $this->loadModel('MobileRepairParts');
        $this->loadModel('OrderDisputes');
        $this->loadModel('FaultyProducts');
        $this->loadModel('MobilePlacedOrders');
		$this->loadModel('MobilePlacedOrders');
		
       // MobilePlacedOrders
        
        
    }
    
    public function transientOrders(){
        $kiosks = $this->Kiosks->find('list',
                                      [
                                        'conditions'=>['Kiosks.status'=>1],
                                        'keyField' => 'id',
                                        'valueField' => 'name',
                                        'order' => 'Kiosks.name asc'
                                    ]);
		$kiosks = $kiosks->toArray();
		//$kiosks = $this->Kiosk->find('list', array('fields' => array('id', 'name'),
		//											'conditions' => array('Kiosk.kiosk_type' => 1 , 'Kiosk.kiosk_type' => 2),
		//											'order' => 'Kiosk.name asc'
		//										));
		$users_query = $this->Users->find('list',
                                    [
                                        'keyField' => 'id',
                                        'valueField' => 'username',
                                    ]);
        if(!empty($users_query)){
             $users = $users_query->toArray();
        }
        
        //pr($users);die;
		//pr($users);
		$kiosk_id = $this->request->Session()->read('kiosk_id');
		//Configure::load('common-arrays');
		$orderOptions = $active = Configure::read('order_status');
		
		if(!empty($kiosk_id)){
			$conditions = array('KioskOrders.status' => "1",'KioskOrders.kiosk_id' => $kiosk_id);
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
									$conditions = array('KioskOrders.status' => "1",'KioskOrders.kiosk_id IN' => $managerKiosk);
						   }else{
								$conditions = array('KioskOrders.status' => "1" ,'KioskOrders.kiosk_id IN' => array(0 => null));			 						
						   }
				  }else{
						   $conditions = array('KioskOrders.status' => "1");			 						
				  }
			
		}
		
		$this->paginate = [
                            'conditions' => $conditions,
                            'order' => array('KioskOrders.dispatched_on' => 'desc'),
                            'limit' => ROWS_PER_PAGE
                          ];
		$this->KioskOrders->recursive = 0;
		$kioskOrders = $this->paginate('KioskOrders');
        $kioskOrders = $kioskOrders->toArray();
        //pr($kioskOrders);die;
		$on_demand_placed_orderid = $kiosk_placed_orderid = array();
		foreach($kioskOrders as $kioskOrder){
            //pr($kioskOrder);die;
			if((int)$kioskOrder->kiosk_placed_order_id){
				if($kioskOrder['is_on_demand'] == 1){
					$on_demand_placed_orderid[] = $kioskOrder->kiosk_placed_order_id;
				}else{
					$kiosk_placed_orderid[] = $kioskOrder->kiosk_placed_order_id;
				}
			  
			}
		}
		if(empty($kiosk_placed_orderid)){
				  $kiosk_placed_orderid = array(0 => null);
		}
		
		//pr($on_demand_placed_orderid);die;
		$kiosk_placed_user_id = $this->KioskPlacedOrders->find('list',
                                                               [
                                                                    'conditions' => array("id IN" => $kiosk_placed_orderid),
                                                                    'keyField' => 'id',
                                                                    'valueField' => 'user_id'
                                                                    //'recursive' =>-1
																]);
        //pr($kiosk_placed_user_id);die;
        $kiosk_placed_user_id = $kiosk_placed_user_id->toArray();
        //pr($kiosk_placed_user_id);die;
		$kiosk_placed_user_date1 = $this->KioskPlacedOrders->find('list',
                                                                 [
                                                        			'conditions' => array("id IN" => $kiosk_placed_orderid),
                                                                    'keyField' => 'id',
                                                                    'valueField' => 'created',
																	//'recursive' =>-1
																]);
        $kiosk_placed_user_date1 = $kiosk_placed_user_date1->toArray();
        $kiosk_placed_user_date = array();
        foreach($kiosk_placed_user_date1 as $key1 => $value1){
            //$dispatched_on = $value;
            if(!empty($value1)){
                //$value_date1 = $value1->i18nFormat(
                //                                    [\IntlDateFormatter::FULL, \IntlDateFormatter::FULL]
                //                            );
                $value_date1 = date("d-m-y h:i a",strtotime($value1));
                $kiosk_placed_user_date[$key1] = $value_date1;
            }else{
                $value_date1 = "--";
                $kiosk_placed_user_date[$key1] = $value_date1;
            }
            
        }
		if(empty($on_demand_placed_orderid)){
		 $on_demand_placed_orderid = array(0 => null);
		}
		$on_demand_placed_user_id = $this->OnDemandOrders->find('list',
                                                                [
																	'conditions' => array('id IN' => $on_demand_placed_orderid),
                                                                    'keyField' => 'id',
                                                                    'valueField' => 'user_id'
																	//'recursive' =>-1
                                                                ]);
        //pr($on_demand_placed_user_id);die;
        $on_demand_placed_user_id = $on_demand_placed_user_id->toArray();
        //pr($on_demand_placed_user_id);die;
		$on_demand_user_date1 = $this->OnDemandOrders->find('list',
                                                            [
                                                                'conditions' => array("id IN" => $on_demand_placed_orderid),
                                                                'keyField' => 'id',
                                                                'valueField' => 'created'
																//'recursive' =>-1
                                                            ]);
		//pr($kiosk_placed_user_id);
		$on_demand_user_date1 = $on_demand_user_date1->toArray();
        $on_demand_user_date = array();
        foreach($on_demand_user_date1 as $key => $value){
            //$dispatched_on = $value;
            if(!empty($value)){
                //$value_date = $value->i18nFormat(
                //                                    [\IntlDateFormatter::FULL, \IntlDateFormatter::FULL]
                //                            );
                $value_date = date("d-m-y h:i a",strtotime($value));
                $on_demand_user_date[$key] = $value_date;
            }else{
                $value_date = "--";
                $on_demand_user_date[$key] = $value_date;
            }
            
        }
		
		$kiosk_placed_merged_orders = $this->KioskPlacedOrders->find('list',
                                                                 [
                                                        			'conditions' => array("id IN" => $kiosk_placed_orderid),
                                                                    'keyField' => 'id',
                                                                    'valueField' => 'merged',
																	//'recursive' =>-1
																])->toArray();
		
		 $hint = $this->ScreenHint->hint('kiosk_orders','transient_orders');
			if(!$hint){
			 $hint = "";
			}
		
		$this->set(compact('hint','orderOptions', 'users','kiosks','kioskOrders','kiosk_placed_user_id','kiosk_placed_user_date','on_demand_placed_user_id','on_demand_user_date','kiosk_placed_merged_orders'));
    }
    
    public function transientOrdersSearch($keyword =""){
		//pr($this->request->query);die;
		 $external_sites = Configure::read('external_sites');
		 $path = dirname(__FILE__);
		 $ext_site = 0;
		 foreach($external_sites as $site_id => $site_name){
			   $isboloRam = strpos($path,$site_name);
			   if($isboloRam != false){
				   $ext_site = 1;
			   }
		 }
		 
		
		$orderOptions = $active = Configure::read('order_status');
		$searchKW = $this->request->query['search_kw'];
        $kiosks_query = $this->Kiosks->find('list',
                                      [
                                        'conditions'=>['Kiosks.status'=>1],
                                        'keyField' => 'id',
                                        'valueField' => 'name',
                                        'order' => 'Kiosks.name asc'
                                    ]);
        if(!empty($kiosks_query)){
            $kiosks = $kiosks_query->toArray();
        }
		$users_query = $this->Users->find('list',
                                    [
                                        'keyField' => 'id',
                                        'valueField' => 'username',
                                        'order' => ['Users.username asc']
                                    ]);
        if(!empty($users_query)){
             $users = $users_query->toArray();
        }
		 
	 
		$kioskId = $this->request->Session()->read('kiosk_id');
		$conditionArr =array();
		if(!empty($kioskId)){
			//search is running for kiosk
			if(!empty($searchKW)){
				$conditionArr['OR']['LOWER(Products.product_code) like '] =  strtolower("%$searchKW%");
				$conditionArr['OR']['LOWER(Products.product) like '] =  strtolower("%$searchKW%");
				$products_query = $this->Products->find('all', array(
						'fields' => array('Products.id'),
						'conditions' => [$conditionArr],
						
					)
				);
                if(!empty($products_query)){
                    $products = $products_query->toArray();
					
                }
              //  pr($products);
				$productId = "";
				if(array_key_exists('Product',$products)){
					$productId = $products['id'];
				}
				$resultIds = array();
				if(count($products)){
					foreach($products as $key => $singlePrdct){
						$resultIds[$singlePrdct->id] = $singlePrdct->id;
					}
				}
                if(empty($resultIds)){
                    $resultIds = array('0' =>null);
                }
				$stocktransfers_query = $this->StockTransfer->find('all', array(
					'conditions' => array('StockTransfer.product_id IN' => $resultIds),
					'fields' => array('product_id','kiosk_order_id') 
					
					)
				);
                if(!empty($stocktransfers_query)){
                    $stocktransfers = $stocktransfers_query->toArray();
                }else{
                    $stocktransfers = array();
                }
				$kiosk_order_id = array();
             //  pr($stocktransfers);
				if(count($stocktransfers)){
					foreach($stocktransfers as $stocktransfer){
							$kiosk_order_id[] = $stocktransfer->kiosk_order_id;
					}
				}
                if(empty($kiosk_order_id)){
                    $kiosk_order_id = array('0' =>null);
                }
				
				  $this->paginate = [
                            'conditions' => [ 'KioskOrders.id IN' => $kiosk_order_id,
											  'KioskOrders.status' => '1',
											  'KioskOrders.kiosk_id' =>$kioskId],
                            'order' => ['KioskOrders.dispatched_on' => 'desc'],
                            'limit' => ROWS_PER_PAGE
                          ];
					 
				
			}else{
				//when kiosk user is searching nothing
                 $this->paginate = [
                            'conditions' => [ 'KioskOrders.status' => '1',
										  'KioskOrders.kiosk_id' =>$kioskId],
                            'order' => ['KioskOrders.dispatched_on' => 'desc'],
                            'limit' => ROWS_PER_PAGE
                          ];
				 
			}
		}else{
			//search is running for admin and manager
			 //pr($this->request->query);die;
			$kiosk_id = $this->request->query['KioskOrder']['kiosk_id'];
			$firstDay = date("Y-m-1");
			$lastDay = date("Y-m-t");//last day of month - 2016-01-31
			
			$start_date = $this->request->query['start_date'];
			if(!empty($start_date)){
				$firstDay = strtotime($start_date);
				$start = date("Y-m-d",strtotime("-1 day",$firstDay));
				//returned 2015-12-31 when passed 2016-01-1
			}
			 
			$end_date = $this->request->query['end_date'];
			if(!empty($end_date)){
				$lastDay = strtotime($end_date);
				$end = date("Y-m-d",strtotime("+1 day",$lastDay));
				// passed 2016-01-31 and returned 2015-12-31
			}
			
			$type = $this->request->query['type'];
			$dispatch_place_id = $this->request->query['dispatch_place_id'];;
			if(!empty($dispatch_place_id)){
               // echo "dispatch_place_id".$dispatch_place_id;
				$condArr = array();
				if($type == "Placed On"){
					$kiosk_placed_order_id = $dispatch_place_id;
					$condArr = array(
									'KioskOrders.status' => '1',
									'KioskOrders.kiosk_placed_order_id' => $kiosk_placed_order_id,
									);
					if(!empty($kiosk_id)){
						   $condArr['kiosk_id'] = $kiosk_id;
						   }else{
									if($ext_site == 1){
											 $managerKiosk = $this->get_kiosk();
											 if(!empty($managerKiosk)){
													  $condArr['kiosk_id IN'] = $managerKiosk;
											 }
									}
						   }
				}elseif($type == 'Dispatch Date'){
					$id = $dispatch_place_id;
					$condArr = array(
									'KioskOrders.status' => '1',
									'KioskOrders.id' => $id,
									);
					if(!empty($kiosk_id)){
						   $condArr['kiosk_id'] = $kiosk_id;
						   }else{
									if($ext_site == 1){
											 $managerKiosk = $this->get_kiosk();
											 if(!empty($managerKiosk)){
													  $condArr['kiosk_id IN'] = $managerKiosk;
											 }
									}
						   }
				}
                 $this->paginate = [
                            'conditions' => $condArr,
                            'order' => array('KioskOrders.dispatched_on' => 'desc'),
                            'limit' => ROWS_PER_PAGE
                          ];
				 
			}elseif(!empty($searchKW) && empty($kiosk_id) ){
                
				//  echo "finding records only if we have product code/title and all other are empty";
				$conditionArr['OR']['LOWER(Products.product_code) like'] =  strtolower("%$searchKW%");
				$conditionArr['OR']['LOWER(Products.product) like'] =  strtolower("%$searchKW%");
				$products_query = $this->Products->find('all', array(
																'fields' => array('Products.id'),
																'conditions' => $conditionArr,
																//'recursive' => -1
															)
												);
                 $products_query->hydrate(false);
                if(!empty($products_query)){
                    $products = $products_query->toArray();
                }
               
				$resultIds = array();
				if(count($products)){
					foreach($products as $key => $singlePrdct){
						$resultIds[] = $singlePrdct['id'];
					}
				}
				 if(empty($resultIds)){
                    $resultIds = array('0' =>null);
                }		
				$stocktransfers_query = $this->StockTransfer->find('all', array(
																			'conditions' => array('StockTransfer.product_id IN' => $resultIds),
																			'fields' => array('product_id','kiosk_order_id'),
																			'recursive' => -1
																			)
															);
                 $stocktransfers_query->hydrate(false);
                if(!empty($stocktransfers_query)){
                    $stocktransfers = $stocktransfers_query->toArray();
                }
               
				$kiosk_order_id = array();
				if(count($stocktransfers)){
					foreach($stocktransfers as $stocktransfer){
						$kiosk_order_id[] = $stocktransfer['kiosk_order_id'];
					}
				}
				if(empty($kiosk_order_id)){
                    $kiosk_order_id = array('0' =>null);
                }	
				$kiosk_placed_ids = array();
				$kiosk_placed_orders = array();
			    if(!empty($start) && !empty($end)){ 
				   $kiosk_placed_orders_query = $this->KioskPlacedOrders->find('all',[
																				'conditions' => [
																								"DATE(KioskPlacedOrders.created) > '$start'",
																								"DATE(KioskPlacedOrders.created) < '$end'"
                                                                                                ],
																				'fields'=>array('KioskPlacedOrders.id') 
																				 
																			 ]);
                   $kiosk_placed_orders_query = $kiosk_placed_orders_query->hydrate(false);
                   if(!empty($kiosk_placed_orders_query)){
                        $kiosk_placed_orders = $kiosk_placed_orders_query->toArray();
                   }else{
                        $kiosk_placed_orders = array(0=>null);
                   }
                   
				}
              
                if(!empty($kiosk_placed_orders)){
                    foreach($kiosk_placed_orders as $kiosk_placed_order){
					//pr($kiosk_placed_order);
                        $kiosk_placed_ids[$kiosk_placed_order['id']] =  $kiosk_placed_order['id'];
                    }
                }
				
               
				if(!empty($kiosk_placed_ids)){
                    $kiosk_placed_ids = $kiosk_placed_ids->toArray();
                }
                if(empty($kiosk_placed_ids)){
                    $kiosk_placed_ids = array(0=>null);
                }
				if($type == "Placed On"){
					if(!empty($start) && !empty($end)){
						   if($ext_site == 1){
									$managerKiosk = $this->get_kiosk();
									if(!empty($managerKiosk)){
											 $this->paginate = [
															   'conditions' => [
																				   'KioskOrders.id IN' => $kiosk_order_id,
																				   'KioskOrders.status' => '1',
																				   'KioskOrders.kiosk_placed_order_id IN' =>$kiosk_placed_ids,
																				   'KioskOrders.kiosk_id IN' =>$managerKiosk,
																			   ],
															   'order' => ['KioskOrders.dispatched_on' => 'desc'],
															   'limit' => ROWS_PER_PAGE
															 ];					 
									}else{
											 $this->paginate = [
													  'conditions' => [
																		  'KioskOrders.id IN' => $kiosk_order_id,
																		  'KioskOrders.status' => '1',
																		  'KioskOrders.kiosk_placed_order_id IN' =>$kiosk_placed_ids
																	  ],
													  'order' => ['KioskOrders.dispatched_on' => 'desc'],
													  'limit' => ROWS_PER_PAGE
													];
									}
						   }else{
													  $this->paginate = [
											  'conditions' => [
																  'KioskOrders.id IN' => $kiosk_order_id,
																  'KioskOrders.status' => '1',
																  'KioskOrders.kiosk_placed_order_id IN' =>$kiosk_placed_ids
															  ],
											  'order' => ['KioskOrders.dispatched_on' => 'desc'],
											  'limit' => ROWS_PER_PAGE
											];
						   }
                         
					 
					}else{
						   // ext code
						   if($ext_site == 1){
									$managerKiosk = $this->get_kiosk();
									if(!empty($managerKiosk)){
											 $this->paginate = [
															   'conditions' => [
																					'KioskOrders.id IN' => $kiosk_order_id,
																					'KioskOrders.kiosk_id IN' => $managerKiosk,
																					'KioskOrders.status' => '1' 
																			   ],
															   'order' => ['KioskOrders.dispatched_on' => 'desc'],
															   'limit' => ROWS_PER_PAGE
															 ];
									}else{
											 $this->paginate = [
															   'conditions' => [
																					'KioskOrders.id IN' => $kiosk_order_id,
																					'KioskOrders.status' => '1' 
																			   ],
															   'order' => ['KioskOrders.dispatched_on' => 'desc'],
															   'limit' => ROWS_PER_PAGE
															 ];
									}
						   }else{
									$this->paginate = [
													  'conditions' => [
																		   'KioskOrders.id IN' => $kiosk_order_id,
																		   'KioskOrders.status' => '1' 
																	  ],
													  'order' => ['KioskOrders.dispatched_on' => 'desc'],
													  'limit' => ROWS_PER_PAGE
													];
						   }
                         
						 
						
					}
				}else{
					if(!empty($start) && !empty($end)){
						   if($ext_site == 1){
									$managerKiosk = $this->get_kiosk();
									if(!empty($managerKiosk)){
											 $this->paginate = [
															   'conditions' => [
																				   'KioskOrders.id IN' => $kiosk_order_id,
																					 'KioskOrders.status' => '1',
																					 "DATE(KioskOrders.dispatched_on) > '$start'" ,
																					   "DATE(KioskOrders.dispatched_on) < '$end'",
																					   'KioskOrders.kiosk_id IN' => $managerKiosk,
																			   ],
															   'order' => ['KioskOrders.dispatched_on' => 'desc'],
															   'limit' => ROWS_PER_PAGE
															 ];				 
									}else{
											 $this->paginate = [
															   'conditions' => [
																				   'KioskOrders.id IN' => $kiosk_order_id,
																					 'KioskOrders.status' => '1',
																					 "DATE(KioskOrders.dispatched_on) > '$start'" ,
																					   "DATE(KioskOrders.dispatched_on) < '$end'"
																			   ],
															   'order' => ['KioskOrders.dispatched_on' => 'desc'],
															   'limit' => ROWS_PER_PAGE
															 ];
									}
						   }else{
									$this->paginate = [
													  'conditions' => [
																		  'KioskOrders.id IN' => $kiosk_order_id,
																			'KioskOrders.status' => '1',
																			"DATE(KioskOrders.dispatched_on) > '$start'" ,
																			  "DATE(KioskOrders.dispatched_on) < '$end'"
																	  ],
													  'order' => ['KioskOrders.dispatched_on' => 'desc'],
													  'limit' => ROWS_PER_PAGE
													];
						   }
                          
						 
					}else{
						   if($ext_site == 1){
											 $managerKiosk = $this->get_kiosk();
											 if(!empty($managerKiosk)){
													  $this->paginate = [
																		'conditions' => [
																							'KioskOrders.id IN' => $kiosk_order_id,
																							'KioskOrders.kiosk_id IN' => $managerKiosk,
																							  'KioskOrders.status' => '1'
																						],
																		'order' => ['KioskOrders.dispatched_on' => 'desc'],
																		'limit' => ROWS_PER_PAGE
																	  ];						 
											 }else{
													  $this->paginate = [
																		'conditions' => [
																							'KioskOrders.id IN' => $kiosk_order_id,
																							  'KioskOrders.status' => '1'
																						],
																		'order' => ['KioskOrders.dispatched_on' => 'desc'],
																		'limit' => ROWS_PER_PAGE
																	  ];						  
											 }
									}else{
											 $this->paginate = [
															   'conditions' => [
																				   'KioskOrders.id IN' => $kiosk_order_id,
																					 'KioskOrders.status' => '1'
																			   ],
															   'order' => ['KioskOrders.dispatched_on' => 'desc'],
															   'limit' => ROWS_PER_PAGE
															 ];				 
									}
                           
						 
					}
				}
			}elseif(!empty($kiosk_id) && !empty($searchKW)){
				//  echo "searching with kiosk and product code/ product title";
				  
				$conditionArr['OR']['LOWER(Products.product_code) like '] =  strtolower("%$searchKW%");
				$conditionArr['OR']['LOWER(Products.product) like '] =  strtolower("%$searchKW%");
				$products_query = $this->Products->find('all', array(
																'fields' => array('Products.id'),
																'conditions' => $conditionArr,
																'recursive' => -1
															)
												);
                $products_query->hydrate(false);
                if(!empty($products_query)){
                    $products = $products_query->toArray();
					
                }
				
				$resultIds = array();
				if(count($products)){
					foreach($products as $key => $singlePrdct){
						$resultIds[$singlePrdct['id']] = $singlePrdct['id'];
					}
				}
					if(empty($resultIds)){
                    $resultIds = array('0' =>null);
                }			
				$stocktransfers_query = $this->StockTransfer->find('all', array(
																'conditions' => array('StockTransfer.product_id IN' => $resultIds),
																'fields' => array('product_id','kiosk_order_id'),
																 
																	)
															);
                 $stocktransfers_query->hydrate(false);
                if(!empty($stocktransfers_query)){
                    $stocktransfers =  $stocktransfers_query->toArray();
                }
				
				$kiosk_order_id = array();
				if(count($stocktransfers)){
					foreach($stocktransfers as $stocktransfer){
						$kiosk_order_id[] = $stocktransfer['kiosk_order_id'];
					}
				}
					if(empty($kiosk_order_id)){
                    $kiosk_order_id = array('0' =>null);
                }	
				$kiosk_placed_ids = array();
				$kiosk_placed_orders = array();
			    if(!empty($start) && !empty($end)){ 
				   $kiosk_placed_orders_query = $this->KioskPlacedOrders->find('all',array(
																		'conditions' => array(
																					//"KioskPlacedOrder.id" =>$kiosk_placed_orderid,
																					"DATE(KioskPlacedOrders.created) > '$start'" ,
																					"DATE(KioskPlacedOrders.created) < '$end'"
																					),
																		'fields' => array('KioskPlacedOrders.id')
																		
																		)
																		);
                    $kiosk_placed_orders_query->hydrate(false);
                    if(!empty($kiosk_placed_orders_query)){
                        $kiosk_placed_orders =  $kiosk_placed_orders_query->toArray();
                    }
                   
				}
				
				foreach($kiosk_placed_orders as $kiosk_placed_order){
						  $kiosk_placed_ids[$kiosk_placed_order['id']] =  $kiosk_placed_order['id'];
				}
				 if(empty($kiosk_placed_ids)){
                    $kiosk_placed_ids = array(0=>null);
                } 
				if($type == "Placed On"){
                    if(!empty($start) && !empty($end)){
                        // ext code
                         $this->paginate = [
                            'conditions' => [
                                                'KioskOrders.id IN' => $kiosk_order_id,
                                                'KioskOrders.kiosk_id' => $kiosk_id,
                                                'KioskOrders.status' => '1',
                                                'KioskOrders.kiosk_placed_order_id IN' =>$kiosk_placed_ids 
                                            ],
                            'order' => ['KioskOrders.dispatched_on' => 'desc'],
                            'limit' => ROWS_PER_PAGE
                          ];
						 
					}else{
                        // ext code
                         $this->paginate = [
                            'conditions' => [
                                               'KioskOrders.kiosk_placed_order_id IN' => $kiosk_placed_ids,
												'KioskOrders.status' => '1'
                                            ],
                            'order' => ['KioskOrders.dispatched_on' => 'desc'],
                            'limit' => ROWS_PER_PAGE
                          ];
						 
					}
				}else{
                    
					if(!empty($start) && !empty($end)){
                        // ext code
                         $this->paginate = [
                            'conditions' => [
                                                'KioskOrders.id IN' => $kiosk_order_id,
                                                'KioskOrders.kiosk_id' => $kiosk_id,
                                                'KioskOrders.status' => '1',
                                                "DATE(KioskOrders.dispatched_on) > '$start'" ,
                                                "DATE(KioskOrders.dispatched_on) < '$end'"
                                               
                                            ],
                            'order' => ['KioskOrders.dispatched_on' => 'desc'],
                            'limit' => ROWS_PER_PAGE
                          ];
						 
					}else{
						   // ext code
                         $this->paginate = [
                            'conditions' => [
                                               	'KioskOrders.id IN' => $kiosk_order_id,
                                                'KioskOrders.kiosk_id' => $kiosk_id,
                                                'KioskOrders.status' => '1'
                                               
                                            ],
                            'order' => ['KioskOrders.dispatched_on' => 'desc'],
                            'limit' => ROWS_PER_PAGE
                          ];
					 
					}
				}
					
			}elseif(!empty($kiosk_id) && empty($searchKW)){
			//  echo "kiosk";die;
				$kiosk_placed_ids = array();
				$kiosk_placed_orders = array();
				if(!empty($start) && !empty($end)){ 
				   $kiosk_placed_orders_query = $this->KioskPlacedOrders->find('all',array(
																			'conditions' => array(
																						//"KioskPlacedOrder.id" =>$kiosk_placed_orderid,
																						"DATE(KioskPlacedOrders.created) > '$start'" ,
																						"DATE(KioskPlacedOrders.created) < '$end'"
																							   ),
																			'fields' => array('KioskPlacedOrders.id') 
																			 
																			  ));
                    $kiosk_placed_orders_query->hydrate(false);
                    if(!empty($kiosk_placed_orders_query)){
                        $kiosk_placed_orders =  $kiosk_placed_orders_query->toArray();
                    }
				}
				
				foreach($kiosk_placed_orders as $kiosk_placed_order){
					$kiosk_placed_ids[$kiosk_placed_order['id']] =  $kiosk_placed_order['id'];
				}
                 if(empty($kiosk_placed_ids)){
                    $kiosk_placed_ids = array(0=>null);
                }
				if($type == "Placed On"){
					if(!empty($start) && !empty($end)){
						   // ext code
                         $this->paginate = [
                            'conditions' => [
                                               'KioskOrders.kiosk_id'=>$kiosk_id,
                                                'KioskOrders.status' => '1',
                                                'KioskOrders.kiosk_placed_order_id IN' =>$kiosk_placed_ids 
           
                                            ],
                            'order' => ['KioskOrders.dispatched_on' => 'desc'],
                            'limit' => ROWS_PER_PAGE
                          ];
						 
					}else{
						   // ext code
                         $this->paginate = [
                            'conditions' => [
                                             'KioskOrders.kiosk_id'=>$kiosk_id,
											'KioskOrders.status' => '1'
           
                                            ],
                            'order' => ['KioskOrders.dispatched_on' => 'desc'],
                            'limit' => ROWS_PER_PAGE
                          ];
						 											
					}
				}else{
					if(!empty($start) && !empty($end)){
						   // ext code
                         $this->paginate = [
                            'conditions' => [
                                            'KioskOrders.kiosk_id'=>$kiosk_id,
                                            'KioskOrders.status' => '1',
                                            "DATE(KioskOrders.dispatched_on) > '$start'" ,
                                            "DATE(KioskOrders.dispatched_on) < '$end'"
           
                                            ],
                            'order' => ['KioskOrders.dispatched_on' => 'desc'],
                            'limit' => ROWS_PER_PAGE
                          ];
						 
					}else{
						   // ext code
                         $this->paginate = [
                            'conditions' => [
                                           'KioskOrders.kiosk_id'=>$kiosk_id,
											'KioskOrders.status' => '1'
           
                                            ],
                            'order' => ['KioskOrders.dispatched_on' => 'desc'],
                            'limit' => ROWS_PER_PAGE
                          ];
						 
					}										 
				}
			}else{
				// echo "else";
				$kiosk_placed_ids = array();
				$kiosk_placed_orders = array();
				if(!empty($start) && !empty($end)){ 
                    $kiosk_placed_orders_query = $this->KioskPlacedOrders->find('all',array(
																	   'conditions' => array(
																					   "DATE(KioskPlacedOrders.created) > '$start'" ,
																					   "DATE(KioskPlacedOrders.created) < '$end'"
																					  ),
																	   'fields' => array('KioskPlacedOrders.id') 
																	   
																			 ));
                    $kiosk_placed_orders_query->hydrate(false);
                    if(!empty($kiosk_placed_orders_query)){
                        $kiosk_placed_orders =  $kiosk_placed_orders_query->toArray();
                    }
				}
				
				foreach($kiosk_placed_orders as $kiosk_placed_order){
					$kiosk_placed_ids[$kiosk_placed_order['id']] =  $kiosk_placed_order['id'];
				}
				 if(empty($kiosk_placed_ids)){
                    $kiosk_placed_ids = array(0=>null);
                }
				if($type == "Placed On"){
				  
					if(!empty($start) && !empty($end)){
						   if($ext_site == 1){
									$managerKiosk = $this->get_kiosk();
									if(!empty($managerKiosk)){
											 $this->paginate = [
													  'conditions' => [
																	 'KioskOrders.status' => '1',
																	 'KioskOrders.kiosk_id IN' => $managerKiosk,
																	'KioskOrders.kiosk_placed_order_id IN' =>$kiosk_placed_ids 
									 
																	  ],
													  'order' => ['KioskOrders.dispatched_on' => 'desc'],
													  'limit' => ROWS_PER_PAGE
													];
									}else{
											 $this->paginate = [
													  'conditions' => [
																	 'KioskOrders.status' => '1',
																	// 'KioskOrders.kiosk_id IN' => $managerKiosk,
																	'KioskOrders.kiosk_placed_order_id IN' =>$kiosk_placed_ids 
									 
																	  ],
													  'order' => ['KioskOrders.dispatched_on' => 'desc'],
													  'limit' => ROWS_PER_PAGE
													];
									}
						   }else{
									$this->paginate = [
													  'conditions' => [
																	 'KioskOrders.status' => '1',
														//			 'KioskOrders.kiosk_id IN' => $managerKiosk,
																	'KioskOrders.kiosk_placed_order_id IN' =>$kiosk_placed_ids 
									 
																	  ],
													  'order' => ['KioskOrders.dispatched_on' => 'desc'],
													  'limit' => ROWS_PER_PAGE
													];
						   }
                        
						 
					}else{
						   // ext code
						   if($ext_site == 1){
									$managerKiosk = $this->get_kiosk();
									if(!empty($managerKiosk)){
											 $this->paginate = [
													  'conditions' => [
																	 'KioskOrders.status' => '1' ,
																	 'KioskOrders.kiosk_id IN' => $managerKiosk ,
																	],
													  'order' => ['KioskOrders.dispatched_on' => 'desc'],
													  'limit' => ROWS_PER_PAGE
													];
									}else{
											 $this->paginate = [
													  'conditions' => [
																	 'KioskOrders.status' => '1' 
																	],
													  'order' => ['KioskOrders.dispatched_on' => 'desc'],
													  'limit' => ROWS_PER_PAGE
													];		 
									}
						   }else{
									$this->paginate = [
													  'conditions' => [
																	 'KioskOrders.status' => '1' 
																	],
													  'order' => ['KioskOrders.dispatched_on' => 'desc'],
													  'limit' => ROWS_PER_PAGE
													];			
						   }
                         
						 
					}
				}else{
					if(!empty($start) && !empty($end)){
						   // ext code
						    if($ext_site == 1){
									$managerKiosk = $this->get_kiosk();
									if(!empty($managerKiosk)){
											 $this->paginate = [
															   'conditions' => [
																			  'KioskOrders.status' => '1',
																			  'KioskOrders.kiosk_id IN' => $managerKiosk,
																			   "DATE(KioskOrders.dispatched_on) > '$start'" ,
																			   "DATE(KioskOrders.dispatched_on) < '$end'"
																			 ],
															   'order' => ['KioskOrders.dispatched_on' => 'desc'],
															   'limit' => ROWS_PER_PAGE
															 ];
									}else{
											$this->paginate = [
																		'conditions' => [
																					   'KioskOrders.status' => '1',
																						"DATE(KioskOrders.dispatched_on) > '$start'" ,
																						"DATE(KioskOrders.dispatched_on) < '$end'"
																					  ],
																		'order' => ['KioskOrders.dispatched_on' => 'desc'],
																		'limit' => ROWS_PER_PAGE
																	  ]; 
									}
						   }else{
									$this->paginate = [
													  'conditions' => [
																	 'KioskOrders.status' => '1',
																	  "DATE(KioskOrders.dispatched_on) > '$start'" ,
																	  "DATE(KioskOrders.dispatched_on) < '$end'"
																	],
													  'order' => ['KioskOrders.dispatched_on' => 'desc'],
													  'limit' => ROWS_PER_PAGE
													];		
						   }
                          
						 
					}else{
						   // ext code
						    if($ext_site == 1){
									$managerKiosk = $this->get_kiosk();
									if(!empty($managerKiosk)){
											 $this->paginate = [
															   'conditions' => [
																			  'KioskOrders.status' => '1' ,
																				'KioskOrders.kiosk_id IN' => $managerKiosk 
																			 ],
															   'order' => ['KioskOrders.dispatched_on' => 'desc'],
															   'limit' => ROWS_PER_PAGE
															 ];
									}else{
											 $this->paginate = [
															   'conditions' => [
																			  'KioskOrders.status' => '1' 
																				
																			 ],
															   'order' => ['KioskOrders.dispatched_on' => 'desc'],
															   'limit' => ROWS_PER_PAGE
															 ];
									}
						   }else{
											 $this->paginate = [
															   'conditions' => [
																			  'KioskOrders.status' => '1' 
																				
																			 ],
															   'order' => ['KioskOrders.dispatched_on' => 'desc'],
															   'limit' => ROWS_PER_PAGE
															 ];		
						   }
                         
						 
					}
				}
			}
		}
        //pr( $this->paginate);die;
        $kioskOrders_query = $this->paginate('KioskOrders');//die;
		 if(!empty($kioskOrders_query)){
            $kioskOrders = $kioskOrders_query->toArray();
        } 
		 
		$kiosk_placed_orderid = array();
		foreach($kioskOrders as $kioskOrder){
			if((int)$kioskOrder['kiosk_placed_order_id']){
			  $kiosk_placed_orderid[] = $kioskOrder['kiosk_placed_order_id'];
			}
		}
		$kiosk_placed_user_id_query = $this->KioskPlacedOrders->find('list',array(
																		'conditions' => $kiosk_placed_orderid,
																		'fields'=>array('KioskPlacedOrders.id','KioskPlacedOrders.user_id') 
																		 
																			  ));
         $kiosk_placed_user_id_query->hydrate(false);
        if(!empty($kiosk_placed_user_id_query)){
            $kiosk_placed_user_id = $kiosk_placed_user_id_query->toArray();
            
        }
		$kiosk_placed_user_date_query = $this->KioskPlacedOrders->find('list',array(
																		'conditions' => $kiosk_placed_orderid,
																		'fields' => array('KioskPlacedOrders.id','KioskPlacedOrders.created'),
																		'recursive' => -1
																			  ));
          $kiosk_placed_user_date_query->hydrate(false);
        if(!empty($kiosk_placed_user_date_query)){
            $kiosk_placed_user_date = $kiosk_placed_user_date_query->toArray();
            
        }
		
		$kiosk_placed_merged_orders = $this->KioskPlacedOrders->find('list',
                                                                 [
                                                        			'conditions' => array("id IN" => $kiosk_placed_orderid),
                                                                    'keyField' => 'id',
                                                                    'valueField' => 'merged',
																	//'recursive' =>-1
																])->toArray();
		
		
		$hint = $this->ScreenHint->hint('kiosk_orders','transient_orders');
		if(!$hint){$hint = "";}
		$this->set(compact('orderOptions'));
		$this->set(compact('hint','kioskOrders','users','kiosks','kiosk_id','kiosk_placed_user_date','kiosk_placed_user_id','kiosk_placed_merged_orders'));
		$this->render('transient_orders');
	}
    public function revertStock($kioskOrderID = '',$kioskID =''){
        
		$path = dirname(__FILE__);
		$isboloRam = strpos($path,"mbwaheguru");
		
		if(!empty($kioskOrderID)){
          $this->KioskOrders->updateAll(['status' =>3], ['id' => $kioskOrderID]);   
			 
			$stocktransfers_query = $this->StockTransfer->find('all', array(
																'conditions' => array('StockTransfer.kiosk_order_id' => $kioskOrderID)
															)
													);
             $stocktransfers_query = $stocktransfers_query->hydrate(false);
                if(!empty($stocktransfers_query)){
                    $stocktransfers = $stocktransfers_query->toArray();
					
                }
            //  pr($stocktransfers);die;
			if(!empty($stocktransfers)){
				$counter = 0;
				foreach($stocktransfers as $stocktransfer){
					$kiosk_order_id = $stocktransfer['kiosk_order_id'];
					$product_id = $stocktransfer['product_id'];
					$quantity =  $stocktransfer['quantity'];
					$sale_price =  $stocktransfer['sale_price'];
					$cost_price = $stocktransfer['cost_price'];
					$remarks = $stocktransfer['remarks'];
					$product_processed = $stocktransfer['product_processed'];
					$status = $stocktransfer['status'];
					$flag = $stocktransfer['flag'];
					//UPDATE quantity in kiosk_productstable
					$revert_stock['RevertStock'] = array(
														'kiosk_id'=>$kioskID,
														'kiosk_order_id'=>$kiosk_order_id,
														'user_id'=> $this->Auth->User('id'),
														'product_id' =>$product_id,
														'quantity' =>  $quantity,
														'sale_price' =>  $sale_price,
														'cost_price' => $cost_price,
														'remarks' => $remarks,
														'product_processed' => $product_processed,
														'status' => $status,
														'flag' => $flag
													);
					$RevertStock = $this->RevertStocks->newEntity();
                    $RevertStock = $this->RevertStocks->patchEntity($RevertStock,$revert_stock);
                   	$this->RevertStocks->save($RevertStock);
                     $conn = ConnectionManager::get('default');
					 
					 $updateQuery = "UPDATE `products` SET `quantity` = quantity+$quantity WHERE `id` = $product_id";
					if($isboloRam != false){  // boloram code
						$connection = ConnectionManager::get('hpwaheguru');
				        $stmt = $connection->execute($updateQuery);

						$reserve_product_query = "SELECT * From reserved_products  WHERE product_id = $product_id AND DATE(created) = CURDATE() AND status = 0";
						$stmt = $connection->execute($reserve_product_query);
						$resrve_result = $stmt ->fetchAll('assoc');
						
							if(!empty($resrve_result)){
								$curr_qantity = $resrve_result[0]['quantity'];
								if($curr_qantity >= $quantity){
									$sub_qty_query = "UPDATE `reserved_products` SET `quantity` = quantity-$quantity WHERE `product_id` = $product_id AND DATE(`created`) = CURDATE() AND status = 0";
									$stmt = $connection->execute($sub_qty_query);
								}
							}
					}else{ // bolwaheeguru code
						//die("--");
						if($updateQuery){
                          
							$resrve_result_query = $this->ReservedProducts->find('all',array('conditions' => array('product_id' => $product_id,
																											    'DATE(created) = CURDATE()',
																												'status' => 0
																											   )));
                            $resrve_result_query = $resrve_result_query->first();
                            if(!empty($resrve_result_query)){
                            $resrve_result  = $resrve_result_query->toArray();
                            }
                             
							if(!empty($resrve_result)){
                                 $conn = ConnectionManager::get('default');
                                 $sub_qty_query = $conn->execute("UPDATE `reserved_products` SET
                                                                 `quantity` = quantity-$quantity
                                                                 WHERE `product_id` = $product_id AND
                                                                 'DATE(created) = CURDATE()' AND status = 0");
                                
							}
						}
					}
					$counter++;
				}
			}else{
				$flashMessage = "No Record Found For This Order Id";
				   $this->Flash->success(__($flashMessage));
				return $this->redirect(array('controller' => 'kiosk_orders', 'action' => 'transient_orders'));
			}
			if($counter > 0){
				$this->StockTransfer->deleteAll(array('StockTransfer.kiosk_order_id'=>$kioskOrderID));
				$flashMessage = $counter."Product(s) Revert Stock successfully";
				 $this->Flash->success(__($flashMessage));
				return $this->redirect(array('controller' => 'kiosk_orders', 'action' => 'transient_orders'));
			}
		}else{
			$flashMessage = "kiosk order id is missing";
			 $this->Flash->success(__($flashMessage));
			return $this->redirect(array('controller' => 'kiosk_orders', 'action' => 'transient_orders'));
		}
	}
    public function transientKioskOrders()
    {
        //Configure::load('common-arrays');
		$users = $this->Users->find('list',[
                                            'keyField' => 'id',
                                            'valueField' => 'username'
                                            ]);
        $users = $users->toArray();
        //pr($users);die;
		$kiosks = $this->Kiosks->find('list',
                                            [
												  'keyField' => 'id',
                                                  'valueField' => 'name',
												  'conditions'=>['Kiosks.status'=>1],
												  'order' => ['Kiosks.name asc']
                                            ]);
        //pr($kiosks);die;
        $kiosks = $kiosks->toArray();
		$orderOptions = $active = Configure::read('order_status');
        $receiptTable_source = "center_orders";
        $KioskOrderTable = TableRegistry::get($receiptTable_source,[
                                                                    'table' => $receiptTable_source,
                                                                ]);
		//$this->KioskOrder->setSource("center_orders");
		$kiosk_id = $this->request->Session()->read('kiosk_id');
		
		if($kiosk_id>0){
			$conditions = array('center_orders.status' => "1",'center_orders.kiosk_id' => $kiosk_id);
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
				  $conditions = array('center_orders.status' => "1",'kiosk_id IN' => $managerKiosk);
				    //$conditions['kiosk_id IN'] = $managerKiosk;
			   }else{
				  $conditions = array('center_orders.status' => "1");
			   }
		  }else{
			$conditions = array('center_orders.status' => "1");
		  }
		}

		$this->paginate =
                            [
                                'contain' => ['Kiosks'],
                                'conditions' => $conditions,
                                'order' => ['center_orders.dispatched_on desc'],
                                'limit' => ROWS_PER_PAGE
                            ];		
		$KioskOrderTable->recursive = 0;
		$kioskOrders_query = $this->paginate($KioskOrderTable);
        if(!empty($kioskOrders_query)){
            $kioskOrders = $kioskOrders_query->toArray();
        }
		 //pr($kioskOrders);die;
		 
		$hint = $this->ScreenHint->hint('kiosk_orders','transient_kiosk_orders');
        if(!$hint){
            $hint = "";
        }
		$this->set(compact('hint','orderOptions','kioskOrders','users','kiosks'));
    }
    
    public function transientKioskOrdersSearch($keyword = "")
    {
		 $external_sites = Configure::read('external_sites');
		  $path = dirname(__FILE__);
		  $ext_site = 0;
		  foreach($external_sites as $site_id => $site_name){
				$isboloRam = strpos($path,$site_name);
				if($isboloRam != false){
					$ext_site = 1;
				}
		  }
        //Configure::load('common-arrays');
		$orderOptions = $active = Configure::read('order_status');
        $receiptTable_source = "center_orders";
        $KioskOrderTable = TableRegistry::get($receiptTable_source,[
                                                                    'table' => $receiptTable_source,
                                                                ]);
		//$this->KioskOrder->setSource("center_orders");
        $receiptTable_source = "stock_transfer_by_kiosk";
        $StockTransferTable = TableRegistry::get($receiptTable_source,[
                                                                    'table' => $receiptTable_source,
                                                                ]);
		//$this->StockTransfer->setSource('stock_transfer_by_kiosk');
		$searchKW = $this->request->query['search_kw'];
		$kiosks_query = $this->Kiosks->find('list',[
												  'keyField' => 'id',
                                                  'valueField' => 'name',
												  'conditions'=>['Kiosks.status'=>1],
												  'order' => ['Kiosks.name asc']
                                            ]);
        if(!empty($kiosks_query)){
             $kiosks = $kiosks_query->toArray();
        }else{
             $kiosks = array();
        }
       
		$users = $this->Users->find('list',
                                    [
                                        'keyField' => 'id',
                                        'valueField' => 'username',
                                        'order' => ['Users.username asc']
                                    ]);
        //pr($users);die;
        $users = $users->toArray();
        
		$firstDay = date("Y-m-1");
		$lastDay = date("Y-m-t");//last day of month - 2016-01-31
		//$start = date("Y-m-d",strtotime("-1 day",strtotime($firstDay))); //returned 2015-12-31 when passed 2016-01-1
		//$end = date("Y-m-d",strtotime("+1 day",strtotime($lastDay))); // passed 2016-01-31 and returned 2015-12-31
		
		$kioskId = $this->request->Session()->read('kiosk_id');
		$conditionArr =array();
		if(!empty($kioskId)){
			//search is running for kiosk
			if(!empty($searchKW)){
				$conditionArr['OR']['LOWER(Products.product_code) like '] =  strtolower("%$searchKW%");
				$conditionArr['OR']['LOWER(Products.product) like '] =  strtolower("%$searchKW%");
				$products = $this->Products->find('all', array(
						'fields' => array('Products.id'),
						'conditions' => $conditionArr,
						'recursive' => -1
					)
				);
                $products->hydrate(false);
                if(!empty($products)){
                    $products = $products->toArray();
					
                }
				$productId = "";
				if(array_key_exists('Product',$products)){
					$productId = $products['id'];
				}
				$resultIds = array();
				if(count($products)){
					foreach($products as $key => $singlePrdct){
						$resultIds[$singlePrdct['id']] = $singlePrdct['id'];
					}
				}
                 if(empty($resultIds)){
                    $resultIds = array('0'=>null);
                }	
				$stocktransfers = $StockTransferTable->find('all', array(
					'conditions' => array('stock_transfer_by_kiosk.product_id IN' => $resultIds),
					'fields' => array('product_id','kiosk_order_id'),
					'recursive' => -1
					)
				);
                $stocktransfers->hydrate(false);
                if(!empty($stocktransfers)){
                    $stocktransfers = $stocktransfers->toArray();
                }
				$kiosk_order_id = array();
				if(count($stocktransfers)){
					foreach($stocktransfers as $stocktransfer){
							$kiosk_order_id[] = $stocktransfer['kiosk_order_id'];
					}
				}
				 if(empty($kiosk_order_id)){
                    $kiosk_order_id = array('0'=>null);
                }
				
				
				
					$this->paginate = 
                                        [
                                            'conditions' => [
                                                                'center_orders.id IN' => $kiosk_order_id,
                                                                'center_orders.status' => '1',
                                                                'center_orders.kiosk_id' =>$kioskId
                                                            ],
                                            'limit' => ROWS_PER_PAGE,
                                            'order' => ['center_orders.dispatched_on desc'],
                                            'contain' => ['Kiosks']
                                ];
				
			}else{
				
				//when kiosk user is searching nothing
				$this->paginate = [
                                    'conditions' => [
                                                        'center_orders.status' => '1',
                                                        'center_orders.kiosk_id IN' =>$kioskId
                                                    ],
										'limit' => ROWS_PER_PAGE,
										'order' => ['center_orders.dispatched_on desc'],
                                        'contain' => ['Kiosks']
								];
			}
		}else{
			//echo "admin";
			//search is running for admin and manager
			$start_date = $this->request->query['start_date'];
			if(!empty($start_date)){
					$firstDay = strtotime($start_date);
					$start = date("Y-m-d",strtotime("-1 day",$firstDay));
			}
					
			$end_date = $this->request->query['end_date'];
			if(!empty($end_date)){
					$lastDay = strtotime($end_date);
					$end = date("Y-m-d",strtotime("+1 day",$lastDay));
			}
            //pr($this->request->query);die;
			$kiosk_id = $this->request->query['KioskOrder']['kiosk_id'];
			if(!empty($searchKW) && empty($kiosk_id) ){
				 //echo "1st";
				//finding records only if we have product code/title and all other are empty
				$conditionArr['OR']['LOWER(Products.product_code) like '] =  strtolower("%$searchKW%");
				$conditionArr['OR']['LOWER(Products.product) like '] =  strtolower("%$searchKW%");
				$products = $this->Products->find('all', array(
																'fields' => array('Products.id'),
																'conditions' => $conditionArr,
																'recursive' => -1
															)
												);
                $products->hydrate(false);
                if(!empty($products)){
                    $products = $products->toArray();
                }
				//pr($conditionArr);
				//pr($products);
				$resultIds = array();
				if(count($products)){
					foreach($products as $key => $singlePrdct){
						$resultIds[] = $singlePrdct['id'];
					}
				}
				 if(empty($resultIds)){
                    $resultIds = array('0'=>null);
                }	
				$transferredByKiosk = $StockTransferTable->find('all', array(
																			'conditions' => array('stock_transfer_by_kiosk.product_id IN' => $resultIds),
																			'fields' => array('product_id','kiosk_order_id'),
																			'recursive' => -1
																			)
															);
                $transferredByKiosk->hydrate(false);
                if(!empty($transferredByKiosk)){
                    $transferredByKiosk = $transferredByKiosk->toArray();
                }
				$kiosk_order_id = array();
				if(count($transferredByKiosk)){
					foreach($transferredByKiosk as $stocktransfer){
						$kiosk_order_id[] = $stocktransfer['kiosk_order_id'];
					}
				}
                 if(empty($kiosk_order_id)){
                    $kiosk_order_id = array('0'=>null);
                }	
				//pr($kiosk_order_id);
				//echo "<pre>";print_r( $resultIds);die;
				if(!empty($start) && !empty($end)){
				  if($ext_site == 1){
						   $managerKiosk = $this->get_kiosk();
						   if(!empty($managerKiosk)){
							   $this->paginate =
													  [
														  'conditions' => ['center_orders.id IN' => $kiosk_order_id,
																				'center_orders.status' => '1',
																				"DATE(center_orders.dispatched_on) > '$start'" ,
																				"DATE(center_orders.dispatched_on) < '$end'",
																				'center_orders.kiosk_id IN' => $managerKiosk
																		  ],
														  'limit' => ROWS_PER_PAGE,
														  'order' => ['center_orders.dispatched_on desc'],
														  'contain' => ['Kiosks']
													  ];
						   }else{
								$this->paginate =
													  [
														  'conditions' => ['center_orders.id IN' => $kiosk_order_id,
																				'center_orders.status' => '1',
																				"DATE(center_orders.dispatched_on) > '$start'" ,
																				"DATE(center_orders.dispatched_on) < '$end'"
																		  ],
														  'limit' => ROWS_PER_PAGE,
														  'order' => ['center_orders.dispatched_on desc'],
														  'contain' => ['Kiosks']
													  ];	
						   }
				  }else{
						   $this->paginate =
													  [
														  'conditions' => ['center_orders.id IN' => $kiosk_order_id,
																				'center_orders.status' => '1',
																				"DATE(center_orders.dispatched_on) > '$start'" ,
																				"DATE(center_orders.dispatched_on) < '$end'"
																		  ],
														  'limit' => ROWS_PER_PAGE,
														  'order' => ['center_orders.dispatched_on desc'],
														  'contain' => ['Kiosks']
													  ];
				  }
							
				}else{
						   if($ext_site == 1){
									$managerKiosk = $this->get_kiosk();
									if(!empty($managerKiosk)){
										$this->paginate = [
															   'conditions' => ['center_orders.id IN' => $kiosk_order_id,
																					 'center_orders.status' => '1',
																					 'center_orders.kiosk_id IN' => $managerKiosk,
																			   ],
															   'limit' => ROWS_PER_PAGE,
															   'order' => ['center_orders.dispatched_on desc'],
															   'contain' => ['Kiosks']
														   ];
									}else{
											 $this->paginate = [
															   'conditions' => ['center_orders.id IN' => $kiosk_order_id,
																					 'center_orders.status' => '1',
																			   ],
															   'limit' => ROWS_PER_PAGE,
															   'order' => ['center_orders.dispatched_on desc'],
															   'contain' => ['Kiosks']
														   ];
									}
						   }else{
									$this->paginate = [
															   'conditions' => ['center_orders.id IN' => $kiosk_order_id,
																					 'center_orders.status' => '1',
																			   ],
															   'limit' => ROWS_PER_PAGE,
															   'order' => ['center_orders.dispatched_on desc'],
															   'contain' => ['Kiosks']
														   ];
						   }				
				  }
					
				
			}elseif(!empty($kiosk_id) && !empty($searchKW)){
				//searching with kiosk and product code/ product title
				//echo "searching with kiosk and product code/ product title";
				$conditionArr['OR']['LOWER(Products.product_code) like '] =  strtolower("%$searchKW%");
				$conditionArr['OR']['LOWER(Products.product) like '] =  strtolower("%$searchKW%");
				
				$products = $this->Products->find('all', array(
						'fields' => array('Products.id'),
						'conditions' => $conditionArr,
						'recursive' => -1
					)
				);
                $products->hydrate(false);
                if(!empty($products)){
                    $products = $products->toArray();
                }
				//pr($products);
				$resultIds = array();
				if(count($products)){
					foreach($products as $key => $singlePrdct){
						$resultIds[$singlePrdct['id']] = $singlePrdct['id'];
					}
				}
				 if(empty($resultIds)){
                    $resultIds = array('0'=>null);
                }		
				$stocktransfers = $StockTransferTable->find('all', array(
					'conditions' => array('stock_transfer_by_kiosk.product_id IN' => $resultIds),
					'fields' => array('product_id','kiosk_order_id'),
					'recursive' => -1
					)
				);
                
                $stocktransfers->hydrate(false);
                if(!empty($stocktransfers)){
                    $stocktransfers = $stocktransfers->toArray();
                }
				//pr($stocktransfers);die;
				$kiosk_order_id = array();
				if(count($stocktransfers)){
					foreach($stocktransfers as $stocktransfer){
						$kiosk_order_id[] = $stocktransfer['kiosk_order_id'];
					}
				}
                 if(empty($kiosk_order_id)){
                    $kiosk_order_id = array('0'=>null);
                }
				//pr($kiosk_order_id);
				if(!empty($start) && !empty($end)){
					$this->paginate = [
                                        'conditions' => [
                                                            'center_orders.id IN' => $kiosk_order_id,
                                                            'center_orders.kiosk_id IN' => $kiosk_id,
                                                            'center_orders.status' => '1',
                                                            "DATE(center_orders.dispatched_on) > '$start'" ,
                                                            "DATE(center_orders.dispatched_on) < '$end'"
                                                        ],
                                        'limit' => ROWS_PER_PAGE,
                                        'order' => ['center_orders.dispatched_on desc'],
                                        'contain' => ['Kiosks']
                                      ];
				}else{
					$this->paginate = [
                                        'conditions' => [
                                                            'center_orders.id IN' => $kiosk_order_id,
                                                            'center_orders.kiosk_id' => $kiosk_id,
                                                            'center_orders.status' => '1'
                                                        ],
                                        'limit' => ROWS_PER_PAGE,
                                        'order' => ['center_orders.dispatched_on desc'],
                                        'contain' => ['Kiosks']
                                      ];
				}
			}elseif(!empty($kiosk_id) && empty($searchKW)){
					//echo "fourth";
					if(!empty($start) && !empty($end)){
                        $this->paginate = [
											'conditions' => [
                                                                'center_orders.kiosk_id IN'=>$kiosk_id,
                                                                'center_orders.status' => '2',
                                                                "DATE(center_orders.dispatched_on) > '$start'" ,
                                                                "DATE(center_orders.dispatched_on) < '$end'"
                                                            ],
                                            'limit' => ROWS_PER_PAGE,
											'order' => ['center_orders.dispatched_on desc'],
                                            'contain' => ['Kiosks']
                                        ];
					}else{
						$this->paginate = [
                                            'conditions' => [
                                                                'center_orders.kiosk_id IN'=>$kiosk_id,
                                                                'center_orders.status' => '1'
                                                            ],
                                            'limit' => ROWS_PER_PAGE,
                                            'order' => ['center_orders.dispatched_on desc'],
                                            'contain' => ['Kiosks']
                                          ];
					}
			}else{
				  
				if(!empty($start) && !empty($end)){
				  if($ext_site == 1){
						   $managerKiosk = $this->get_kiosk();
						   if(!empty($managerKiosk)){
							   $this->paginate = [
                                            'conditions' => [
                                                                'center_orders.status' => '1',
                                                                "DATE(center_orders.dispatched_on) > '$start'" ,
                                                                "DATE(center_orders.dispatched_on) < '$end'",
																'center_orders.kiosk_id IN' => $managerKiosk
                                                            ],
                                                'limit' => ROWS_PER_PAGE,
                                                'order' => ['center_orders.dispatched_on desc'],
                                                'contain' => ['Kiosks']
                                        ];
						   }else{
									$this->paginate = [
                                            'conditions' => [
                                                                'center_orders.status' => '1',
                                                                "DATE(center_orders.dispatched_on) > '$start'" ,
                                                                "DATE(center_orders.dispatched_on) < '$end'"
                                                            ],
                                                'limit' => ROWS_PER_PAGE,
                                                'order' => ['center_orders.dispatched_on desc'],
                                                'contain' => ['Kiosks']
                                        ];
						   }
				  }else{
						$this->paginate = [
                                            'conditions' => [
                                                                'center_orders.status' => '1',
                                                                "DATE(center_orders.dispatched_on) > '$start'" ,
                                                                "DATE(center_orders.dispatched_on) < '$end'"
                                                            ],
                                                'limit' => ROWS_PER_PAGE,
                                                'order' => ['center_orders.dispatched_on desc'],
                                                'contain' => ['Kiosks']
                                        ];   
				  }
						
					}else{
						   if($ext_site == 1){
									$managerKiosk = $this->get_kiosk();
									if(!empty($managerKiosk)){
										$this->paginate = [
                                                'conditions' => ['center_orders.status' => '1',
																 'center_orders.kiosk_id IN' => $managerKiosk
																 ],
                                                'limit' => ROWS_PER_PAGE,
                                                'order' => ['center_orders.dispatched_on desc'],
                                                'contain' => ['Kiosks']
                                              ];
									}else{
											 $this->paginate = [
                                                'conditions' => ['center_orders.status' => '1'],
                                                'limit' => ROWS_PER_PAGE,
                                                'order' => ['center_orders.dispatched_on desc'],
                                                'contain' => ['Kiosks']
                                              ];
									}
						   }else{
									$this->paginate = [
                                                'conditions' => ['center_orders.status' => '1'],
                                                'limit' => ROWS_PER_PAGE,
                                                'order' => ['center_orders.dispatched_on desc'],
                                                'contain' => ['Kiosks']
                                              ];		
						   }
							 
							
					}
				
			}
		}
		
		$kioskOrders = $this->paginate($KioskOrderTable);
		$kioskOrders = $kioskOrders->toArray();
		$hint = $this->ScreenHint->hint('kiosk_orders','transient_kiosk_orders');
        if(!$hint){
            $hint = "";
        }
		
		
		//pr($kioskOrders);
		$this->set(compact('orderOptions'));
		$this->set(compact('hint','kioskOrders','users','kiosks','kiosk_id'));
		$this->render('transient_kiosk_orders');
    }
    function confirmedOrders(){
		//$this->request->here; // =>/InventoryManagement/kiosk_orders/confirmed_orders
		//echo Router::url(null, true); //http://localhost/InventoryManagement/kiosk_orders/confirmed_orders
		//$this->Html->url( $this->here, true ); //for views	
		$kiosk_id = $this->request->session()->read('kiosk_id');
		if(!empty($kiosk_id)){
			$conditions = array('KioskOrders.status' => "2",'KioskOrders.kiosk_id' => $kiosk_id);
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
				  $conditions = array('KioskOrders.status' => "2",'KioskOrders.kiosk_id IN' => $managerKiosk);
			   }else{
				  $conditions = array('KioskOrders.status' => "2");
			   }
		  }else{
			$conditions = array('KioskOrders.status' => "2");
		  }
		}
        $orderOptions = $active = Configure::read('order_status');
		$this->paginate = [
                                                'conditions' => $conditions,
                                                'limit' => ROWS_PER_PAGE,
                                                'order' => ['KioskOrders.dispatched_on' => 'desc'],
                                               'contain' => ['Kiosks']
                                              ];
       // pr($this->paginate);die;
        $kioskOrders_query = $this->paginate($this->KioskOrders);
        
        if(!empty($kioskOrders_query)){
            $kioskOrders = $kioskOrders_query->toArray();
        }
        //pr($kioskOrders);die;
        $query = $this->Users->find('list', [
                                        'keyField' => 'id',
                                        'valueField' => 'username'
                                    ]);
        if(!empty($query)){
            $users = $query->toArray();
        }
       $kiosks_query = $this->Kiosks->find('list',[
                                                'keyField' => 'id',
                                                'valueField' => 'name',
                                                'conditions'=>['Kiosks.status'=>1],
                                                'order' => ['Kiosks.name asc']
                                             ]);
        if(!empty($kiosks_query)){
            $kiosks = $kiosks_query->toArray();
        }else{
            $kiosks = array();
        }
		//$kiosks = $this->Kiosks->find('list', array('fields' => array('id', 'name'),
		//											'conditions' => array('Kiosks.status' => 1),
		//										//	'order' => 'Kiosks.name asc'
		//										));
		//$this->KioskOrder->recursive = 0;
		//$kioskOrders = $this->Paginator->paginate('KioskOrder');
		// pr($kioskOrders);
		$ondemand_placed_orderid = $kiosk_placed_orderid = array();
		foreach($kioskOrders as $kioskOrder){
			if($kioskOrder['is_on_demand'] == 1){
			  $ondemand_placed_orderid[] = $kioskOrder['kiosk_placed_order_id'];
			}else{
				$kiosk_placed_orderid[] = $kioskOrder['kiosk_placed_order_id'];
			}
		}
		 
		$kiosk_placed_user_id_query = $this->KioskPlacedOrders->find('list',[
                                                                       'conditions' => $kiosk_placed_orderid,
                                                                        'keyField' => 'id',
                                                                        'valueField' => 'user_id'
																		 
                                                                      ]);
        if(!empty($kiosk_placed_user_id_query)){
            $kiosk_placed_user_id = $kiosk_placed_user_id_query->toArray();
		
        }
       
		$kiosk_placed_user_date_query1 = $this->KioskPlacedOrders->find('list',[
                                            'conditions' => $kiosk_placed_orderid,
                                             'keyField' => 'id',
                                              'valueField' => 'created'
        ]);
		 if(!empty($kiosk_placed_user_date_query1)){
            $kiosk_placed_user_date1 = $kiosk_placed_user_date_query1->toArray();
        }																	
        $kiosk_placed_user_date = array();
        foreach($kiosk_placed_user_date1 as $key1 => $value1){
            //$dispatched_on = $value;
            if(!empty($value1)){
                $value_date1 = $value1->i18nFormat(
                                                    [\IntlDateFormatter::FULL, \IntlDateFormatter::FULL]
                                            );
                $value_date1 = date("d-m-y h:i a",strtotime($value_date1));
                $kiosk_placed_user_date[$key1] = $value_date1;
            }else{
                $value_date1 = "--";
                $kiosk_placed_user_date[$key1] = $value_date1;
            }
            
        }                
                        
		$on_demand_placed_user_id_query = $this->OnDemandOrders->find('list',[
                        'conditions' => $ondemand_placed_orderid,
                        'keyField' => 'id',
                         'valueField' => 'user_id'
        ]);
         if(!empty($on_demand_placed_user_id_query)){
            $on_demand_placed_user_id = $on_demand_placed_user_id_query->toArray();
		
        }	                                                              
                                
		  //pr($kiosk_placed_user_id);
		$on_demand_placed_user_date_query1 = $this->OnDemandOrders->find('list',[
                'conditions' => $ondemand_placed_orderid,
                 'keyField' => 'id',
                 'valueField' => 'created'
        ]);
         if(!empty($on_demand_placed_user_date_query1)){
            $on_demand_placed_user_date1 = $on_demand_placed_user_date_query1->toArray();
		
        }	  
         
         $on_demand_placed_user_date = array();
        foreach($on_demand_placed_user_date1 as $key => $value){
            //$dispatched_on = $value;
            if(!empty($value)){
                $value_date = $value->i18nFormat(
                                                    [\IntlDateFormatter::FULL, \IntlDateFormatter::FULL]
                                            );
                $value_date = date("d-m-y h:i a",strtotime($value_date));
                $on_demand_placed_user_date[$key] = $value_date;
            }else{
                $value_date = "--";
                $on_demand_placed_user_date[$key] = $value_date;
            }
            
        }
         if(empty($kiosk_placed_orderid)){
				  $kiosk_placed_orderid = array(0 => null);
		 }
		 
		  $kiosk_placed_merged_orders = $this->KioskPlacedOrders->find('list',
                                                                 [
                                                        			'conditions' => array("id IN" => $kiosk_placed_orderid),
                                                                    'keyField' => 'id',
                                                                    'valueField' => 'merged',
																	//'recursive' =>-1
																])->toArray();
		                
		 $hint = $this->ScreenHint->hint('kiosk_orders','confirmed_orders');
        if(!$hint){
            $hint = "";
        }
		
		
		$this->set(compact('hint','orderOptions','users','kiosks','kioskOrders','kiosk_placed_user_id','kiosk_placed_user_date','on_demand_placed_user_id','on_demand_placed_user_date','kiosk_placed_merged_orders'));
	
		
	}
    public function confirmedOrdersSearch($keyword = ""){
		 $external_sites = Configure::read('external_sites');
			 $path = dirname(__FILE__);
			 $ext_site = 0;
			 foreach($external_sites as $site_id => $site_name){
				   $isboloRam = strpos($path,$site_name);
				   if($isboloRam != false){
					   $ext_site = 1;
				   }
			 }
			 
		$orderOptions = $active = Configure::read('order_status');
		$searchKW = trim($this->request->query['search_kw']);
        $query = $this->Users->find('list', [
                                        'keyField' => 'id',
                                        'valueField' => 'username',
                                        'order' => 'Users.username asc'
                                    ]);
		$query = $query->hydrate(false);
        if(!empty($query)){
            $users = $query->toArray();
        }else{
		 $users = array();
		}
         $kiosks_query = $this->Kiosks->find('list',[
                                                'keyField' => 'id',
                                                'valueField' => 'name',
                                                'conditions'=>['Kiosks.status'=>1],
                                                'order' => ['Kiosks.name asc']
                                             ]);
        if(!empty($kiosks_query)){
            $kiosks = $kiosks_query->toArray();
        }else{
            $kiosks = array();
        }
		 
		$kioskId = $this->request->session()->read('kiosk_id');
		$conditionArr =array();
		
		if(!empty($kioskId)){
            //echo'hi';die;
			//echo "search is running for kiosk";
			if(!empty($searchKW)){
				$conditionArr['OR']['LOWER(Products.product_code) like '] =  strtolower("%$searchKW%");
				$conditionArr['OR']['LOWER(Products.product) like '] =  strtolower("%$searchKW%");
				$products_query = $this->Products->find('all', array(
						'fields' => array('Products.id'),
						'conditions' => $conditionArr
					)
				);
                $products_query =  $products_query->hydrate(false);
                if(!empty($products_query)){
                    $products = $products_query->toArray();
                }else{
                    $products = array();
                }
			 
				$productId = "";
//				if(!empty($products)){
//					$productId = $products->id;
//				}
//                pr($productId);die;
				$resultIds = array();
				if(count($products)){
					foreach($products as $key => $singlePrdct){
						$resultIds[$singlePrdct['id']] = $singlePrdct['id'];
					}
				}
                if(empty($resultIds)){
                    $resultIds = array(0 => null);
                }
				$stocktransfers_query = $this->StockTransfer->find('all', array(
					'conditions' => array('StockTransfer.product_id IN' => $resultIds),
					'fields' => array('product_id','kiosk_order_id'),
					'recursive' => -1
					)
				);
                $stocktransfers_query =  $stocktransfers_query->hydrate(false);
                if(!empty($stocktransfers_query)){
                    $stocktransfers = $stocktransfers_query->toArray();
                }else{
                    $stocktransfers = array();
                }
              //  pr($stocktransfers);die;
				$kiosk_order_id = array();
				if(count($stocktransfers)){
					foreach($stocktransfers as $stocktransfer){
							$kiosk_order_id[] = $stocktransfer['kiosk_order_id'];
					}
				}
                 if(empty($kiosk_order_id)){
                    $kiosk_order_id = array('0'=>null);
                }
                $this->paginate = [
                                                'conditions' =>[ 'KioskOrders.id IN' => $kiosk_order_id,
                                                                    'KioskOrders.status' => '2',
                                                                    'KioskOrders.kiosk_id' => $kioskId
                                                                    ],
                                                'limit' => ROWS_PER_PAGE,
                                                'order' => ['KioskOrders.dispatched_on' => 'desc'],
                                               'contain' => ['Kiosks']
                                              ];
				 
				
			}else{
				//when kiosk user is searching nothing
                 $this->paginate = [
                                                'conditions' =>[
                                                                    'KioskOrders.status' => '2',
                                                                    'KioskOrders.kiosk_id' => $kioskId
                                                                    ],
                                                'limit' => ROWS_PER_PAGE,
                                                'order' => ['KioskOrders.dispatched_on' => 'desc'],
                                               'contain' => ['Kiosks']
                                              ];
				 
			}
            $kioskOrders_query = $this->paginate($this->KioskOrders);
            if(!empty($kioskOrders_query)){
                $kioskOrders = $kioskOrders_query->toArray();
            }
            
		}else{
		  //echo'bye';die;
			//search is running for admin and manager
            // pr($this->request->query);
            if(array_key_exists('data',$this->request->query )){
                $kiosk_id = $this->request->query['data']['KioskOrder']['kiosk_id']; 
            }
			
			$firstDay = date("Y-m-1");
			$lastDay = date("Y-m-t");//last day of month - 2016-01-31
            $start_date = $this->request->query['start_date'];
			
			if(!empty($start_date)){
				$firstDay = strtotime($start_date);
				$start = date("Y-m-d",strtotime("-1 day",$firstDay));
				//returned 2015-12-31 when passed 2016-01-1
			}
			 
			$end_date = $this->request->query['end_date'];
			if(!empty($end_date)){
				$lastDay = strtotime($end_date);
				$end = date("Y-m-d",strtotime("+1 day",$lastDay));
				// passed 2016-01-31 and returned 2015-12-31
			}
			
			$type = $this->request->query['type'];
			$dispatch_place_id = $this->request->query['dispatch_place_id'];;
			if(!empty($dispatch_place_id)){
				 //echo'hi';die;
				$condArr = array();
				if($type == "Placed On"){
					$kiosk_placed_order_id = $dispatch_place_id;
					$condArr = array(
									'KioskOrders.status' => '2',
									'KioskOrders.kiosk_placed_order_id' => $kiosk_placed_order_id,
									);
					if(!empty($kiosk_id)){$condArr['kiosk_id'] = $kiosk_id;}else{
						   // ex site code
					}
				}elseif($type == 'Dispatch Date'){
					$id = $dispatch_place_id;
					$condArr = array(
									'KioskOrders.status' => '2',
									'KioskOrders.id' => $id,
									);
					if(!empty($kiosk_id)){$condArr['kiosk_id'] = $kiosk_id;}else{
						   // ex site code
					}
				}
                 $this->paginate = [
                                                'conditions' =>$condArr,
                                                'limit' => ROWS_PER_PAGE,
                                                'order' => ['KioskOrders.dispatched_on' => 'desc'],
                                                'contain' => ['Kiosks']
                                              ];
				 
			}elseif(!empty($searchKW) && empty($kiosk_id) ){
				  //echo'bye';die;
				//echo "finding records only if we have product code/title and all other are empty";die;
				//finding records only if we have product code/title and all other are empty
				$conditionArr['OR']['LOWER(Products.product_code) like'] =  strtolower("%$searchKW%");
				$conditionArr['OR']['LOWER(Products.product) like'] =  strtolower("%$searchKW%");
               	$products_query = $this->Products->find('all', array(
																'fields' => array('Products.id'),
																'conditions' => $conditionArr,
																//'recursive' => -1
															)
												);
                 if(!empty($products_query)){
                    $products = $products_query->toArray();
                }
                
				$resultIds = array();
				if(count($products)){
					foreach($products as $key => $singlePrdct){
						$resultIds[] = $singlePrdct['id'];
					}
				}
                 if(empty($resultIds)){
                    $resultIds = array(0 =>null);
                }
				//pr($resultIds);die;	
                    $stocktransfers_query = $this->StockTransfer->find('all', array(
					'conditions' => array('StockTransfer.product_id IN' => $resultIds),
					'fields' => array('product_id','kiosk_order_id'),
					 
					)
				); 	
				//$stocktransfers_query = $this->StockTransfer->find('all', array(
				//															'conditions' => array('StockTransfer.product_id IN' => $resultIds),
				//															'fields' => array('product_id','kiosk_order_id'),
				//															'recursive' => -1
				//															)
				//											);
                 if(!empty($stocktransfers_query)){
                    $stocktransfers = $stocktransfers_query->toArray();
                }
				 
				$kiosk_order_id = array();
				if(count($stocktransfers)){
					foreach($stocktransfers as $stocktransfer){
						$kiosk_order_id[] = $stocktransfer['kiosk_order_id'];
					}
				}
                 if(empty($kiosk_order_id)){
                    $kiosk_order_id = array('0'=>null);
                }
				$kiosk_placed_ids = array();
				$kiosk_placed_orders = array();
			    if(!empty($start) && !empty($end)){ 
				   $kiosk_placed_orders_query = $this->KioskPlacedOrders->find('all',array(
																		'conditions' => array(
																						"DATE(KioskPlacedOrders.created) > '$start'" ,
																						"DATE(KioskPlacedOrders.created) < '$end'"
																					   ),
																				'fields' => array('KioskPlacedOrders.id'),
																				
																			  ));
                   if(!empty($kiosk_placed_orders_query)){
                    $kiosk_placed_orders = $kiosk_placed_orders_query->toArray();
                }
				} 
				foreach($kiosk_placed_orders as $kiosk_placed_order){
					$kiosk_placed_ids[$kiosk_placed_order['id']] =  $kiosk_placed_order['id'];
				}
                 if(empty($kiosk_placed_ids)){
                    $kiosk_placed_ids = array(0=>null);
                }
				if($type == "Placed On"){
					if(!empty($start) && !empty($end)){
						   if($ext_site == 1){
									$managerKiosk = $this->get_kiosk();
									if(!empty($managerKiosk)){
										$this->paginate = [
                                                'conditions' =>[
                                                                'KioskOrders.id IN' => $kiosk_order_id,
													  'KioskOrders.status' => '2',
													  'KioskOrders.kiosk_placed_order_id IN' =>$kiosk_placed_ids,
													  'KioskOrders.kiosk_id IN' => $managerKiosk,
                                                                ],
                                                'limit' => ROWS_PER_PAGE,
                                                'order' => ['KioskOrders.dispatched_on' => 'desc'],
                                                'contain' => ['Kiosks']
                                              ];
									}else{
											 $this->paginate = [
                                                'conditions' =>[
                                                                'KioskOrders.id IN' => $kiosk_order_id,
													  'KioskOrders.status' => '2',
													  'KioskOrders.kiosk_placed_order_id IN' =>$kiosk_placed_ids
                                                                ],
                                                'limit' => ROWS_PER_PAGE,
                                                'order' => ['KioskOrders.dispatched_on' => 'desc'],
                                                'contain' => ['Kiosks']
                                              ];
									}
						   }else{
									$this->paginate = [
                                                'conditions' =>[
                                                                'KioskOrders.id IN' => $kiosk_order_id,
													  'KioskOrders.status' => '2',
													  'KioskOrders.kiosk_placed_order_id IN' =>$kiosk_placed_ids
                                                                ],
                                                'limit' => ROWS_PER_PAGE,
                                                'order' => ['KioskOrders.dispatched_on' => 'desc'],
                                                'contain' => ['Kiosks']
                                              ];			
						   }
                         
						  
					}else{
						   if($ext_site == 1){
									$managerKiosk = $this->get_kiosk();
									if(!empty($managerKiosk)){
										$this->paginate = [
                                                'conditions' =>[
                                                               'KioskOrders.id IN' => $kiosk_order_id,
													'KioskOrders.status' => '2',
													'KioskOrders.kiosk_id IN' => $managerKiosk
                                                                ],
                                                'limit' => ROWS_PER_PAGE,
                                                'order' => ['KioskOrders.dispatched_on' => 'desc'],
                                                'contain' => ['Kiosks']
                                              ];
									}else{
											 $this->paginate = [
                                                'conditions' =>[
                                                               'KioskOrders.id IN' => $kiosk_order_id,
													'KioskOrders.status' => '2',
                                                                ],
                                                'limit' => ROWS_PER_PAGE,
                                                'order' => ['KioskOrders.dispatched_on' => 'desc'],
                                                'contain' => ['Kiosks']
                                              ];				 
									}
						   }else{
									$this->paginate = [
                                                'conditions' =>[
                                                               'KioskOrders.id IN' => $kiosk_order_id,
													'KioskOrders.status' => '2',
                                                                ],
                                                'limit' => ROWS_PER_PAGE,
                                                'order' => ['KioskOrders.dispatched_on' => 'desc'],
                                                'contain' => ['Kiosks']
                                              ];			
						   }
						   
					}
						
				}else{
						if(!empty($start) && !empty($end)){
						   if($ext_site == 1){
									$managerKiosk = $this->get_kiosk();
									if(!empty($managerKiosk)){
										$this->paginate = [
                                                'conditions' =>[
                                                             'KioskOrders.id IN' => $kiosk_order_id,
													  'KioskOrders.status' => '2',
													  "DATE(KioskOrders.dispatched_on) > '$start'" ,
														"DATE(KioskOrders.dispatched_on) < '$end'",
														'KioskOrders.kiosk_id IN' => $managerKiosk,
                                                                ],
                                                'limit' => ROWS_PER_PAGE,
                                                'order' => ['KioskOrders.dispatched_on' => 'desc'],
                                                'contain' => ['Kiosks']
                                              ];
									}else{
											 $this->paginate = [
                                                'conditions' =>[
                                                             'KioskOrders.id IN' => $kiosk_order_id,
													  'KioskOrders.status' => '2',
													  "DATE(KioskOrders.dispatched_on) > '$start'" ,
														"DATE(KioskOrders.dispatched_on) < '$end'"
                                                                ],
                                                'limit' => ROWS_PER_PAGE,
                                                'order' => ['KioskOrders.dispatched_on' => 'desc'],
                                                'contain' => ['Kiosks']
                                              ];
									}
						   }else{
									$this->paginate = [
                                                'conditions' =>[
                                                             'KioskOrders.id IN' => $kiosk_order_id,
													  'KioskOrders.status' => '2',
													  "DATE(KioskOrders.dispatched_on) > '$start'" ,
														"DATE(KioskOrders.dispatched_on) < '$end'"
                                                                ],
                                                'limit' => ROWS_PER_PAGE,
                                                'order' => ['KioskOrders.dispatched_on' => 'desc'],
                                                'contain' => ['Kiosks']
                                              ];		
						   }
                            
							 
						}else{
						   if($ext_site == 1){
									$managerKiosk = $this->get_kiosk();
									if(!empty($managerKiosk)){
										$this->paginate = [
                                                'conditions' =>[
                                                            'KioskOrders.id IN' => $kiosk_order_id,
													  'KioskOrders.status' => '2',
													  'KioskOrders.kiosk_id IN' => $managerKiosk,
                                                                ],
                                                'limit' => ROWS_PER_PAGE,
                                                'order' => ['KioskOrders.dispatched_on' => 'desc'],
                                                'contain' => ['Kiosks']
                                              ];
									}else{
											 $this->paginate = [
                                                'conditions' =>[
                                                            'KioskOrders.id IN' => $kiosk_order_id,
													  'KioskOrders.status' => '2',
                                                                ],
                                                'limit' => ROWS_PER_PAGE,
                                                'order' => ['KioskOrders.dispatched_on' => 'desc'],
                                                'contain' => ['Kiosks']
                                              ];
									}
						   }else{
									$this->paginate = [
                                                'conditions' =>[
                                                            'KioskOrders.id IN' => $kiosk_order_id,
													  'KioskOrders.status' => '2',
                                                                ],
                                                'limit' => ROWS_PER_PAGE,
                                                'order' => ['KioskOrders.dispatched_on' => 'desc'],
                                                'contain' => ['Kiosks']
                                              ];		
						   }
						}
					}
			} elseif(!empty($kiosk_id) && !empty($searchKW)){
                //echo'1';die;
				 //echo "searching with kiosk and product code/ product title";die;
				$conditionArr['OR']['LOWER(Products.product_code) like '] =  strtolower("%$searchKW%");
				$conditionArr['OR']['LOWER(Products.product) like '] =  strtolower("%$searchKW%");
				$products_query = $this->Products->find('all', array(
						'fields' => array('Products.id'),
						'conditions' => $conditionArr
					)
				);
                if(!empty($products_query)){
                    $products = $products_query->toArray();
                }
				 
				$resultIds = array();
				if(count($products)){
					foreach($products as $key => $singlePrdct){
						$resultIds[$singlePrdct['id']] = $singlePrdct['id'];
					}
				}
				if(empty($resultIds)){
				  $resultIds = array(0 => null);
				}
				$stocktransfers_query = $this->StockTransfer->find('all', array(
					'conditions' => array('StockTransfer.product_id IN' => $resultIds),
					'fields' => array('product_id','kiosk_order_id'),
					 
					)
				); 
                if(!empty($stocktransfers_query)){
                    $stocktransfers = $stocktransfers_query->toArray();
                }
				$kiosk_order_id = array();
				if(count($stocktransfers)){
					foreach($stocktransfers as $stocktransfer){
						$kiosk_order_id[] = $stocktransfer['kiosk_order_id'];
					}
				}
                
				$kiosk_placed_ids = array();
				$kiosk_placed_orders = array();
			    if(!empty($start) && !empty($end)){ 
				   $kiosk_placed_orders_query = $this->KioskPlacedOrders->find('all',array(
																	'conditions' => array(
																					"DATE(KioskPlacedOrders.created) > '$start'" ,
																					"DATE(KioskPlacedOrders.created) < '$end'"
																				   ),
																				'fields'=>array('KioskPlacedOrders.id')
																			  ));
                    if(!empty($kiosk_placed_orders_query)){
                        $kiosk_placed_orders = $kiosk_placed_orders_query->toArray();
                    }
				}
				
				foreach($kiosk_placed_orders as $kiosk_placed_order){
					$kiosk_placed_ids[$kiosk_placed_order['id']] =  $kiosk_placed_order['id'];
				}
				  if(empty($kiosk_placed_ids)){
                    $kiosk_placed_ids = array(0=>null);
                }
                
				if(empty($kiosk_order_id)){
						   $kiosk_order_id = array(0 => null);
				   }
				
				if($type == "Placed On"){
					if(!empty($start) && !empty($end)){
                         $this->paginate = [
                                                'conditions' =>[
                                                            'KioskOrders.id IN' => $kiosk_order_id,
																					'KioskOrders.kiosk_id' => $kiosk_id,
																					'KioskOrders.status' => '2',
																					'KioskOrders.kiosk_placed_order_id IN' =>$kiosk_placed_ids,
                                                                ],
                                                'limit' => ROWS_PER_PAGE,
                                                'order' => ['KioskOrders.dispatched_on' => 'desc'],
                                                'contain' => ['Kiosks']
                                              ];
						 
					}else{
                         $this->paginate = [
                                                'conditions' =>[
                                                           'KioskOrders.id IN' => $kiosk_order_id,
																				'KioskOrders.kiosk_id' => $kiosk_id,
																				'KioskOrders.status' => '2'
                                                                ],
                                                'limit' => ROWS_PER_PAGE,
                                                'order' => ['KioskOrders.dispatched_on' => 'desc'],
                                                'contain' => ['Kiosks']
                                              ];
						 
					}
				}
				else{
                   
					if(!empty($start) && !empty($end)){
                         
                         $this->paginate = [
                                                'conditions' =>[
                                                          'KioskOrders.id IN' => $kiosk_order_id,
																					'KioskOrders.kiosk_id' => $kiosk_id,
																					'KioskOrders.status' => '2',
																					"DATE(KioskOrders.dispatched_on) > '$start'" ,
																					"DATE(KioskOrders.dispatched_on) < '$end'"
                                                                ],
                                                'limit' => ROWS_PER_PAGE,
                                                'order' => ['KioskOrders.dispatched_on' => 'desc'],
                                                'contain' => ['Kiosks']
                                              ];
						 
					}else{
                        
                         $this->paginate = [
                                                'conditions' =>[
                                                          'KioskOrders.id IN' => $kiosk_order_id,
																					'KioskOrders.kiosk_id' => $kiosk_id,
																					'KioskOrders.status' => '2'
                                                                ],
                                                'limit' => ROWS_PER_PAGE,
                                                'order' => ['KioskOrders.dispatched_on' => 'desc'],
                                                'contain' => ['Kiosks']
                                              ];
					 
					}
				}
					
			}elseif(!empty($kiosk_id) && empty($searchKW)){
                //echo'2';die;
				// echo "kiosk";die;
					$kiosk_placed_ids = array();
					$kiosk_placed_orders = array();
					if(!empty($start) && !empty($end)){ 
					   $kiosk_placed_orders_query = $this->KioskPlacedOrders->find('all',array(
																						   'conditions' => array(
																										  //"KioskPlacedOrder.id" =>$kiosk_placed_orderid,
																										   "DATE(KioskPlacedOrders.created) > '$start'" ,
																										 "DATE(KioskPlacedOrders.created) < '$end'"
																										  ),
																					'fields'=>array('KioskPlacedOrders.id'),
																					'recursive' =>-1
																				  ));
                       if(!empty($kiosk_placed_orders_query)){
                        $kiosk_placed_orders = $kiosk_placed_orders_query->toArray();
                       }
                       
					}
                  // pr($kiosk_placed_orders);die;
					foreach($kiosk_placed_orders as $kiosk_placed_order){
							$kiosk_placed_ids[$kiosk_placed_order['id']] =  $kiosk_placed_order['id'];
					}
                     if(empty($kiosk_placed_ids)){
                        $kiosk_placed_ids = array(0=>null);
                    }
					if($type == "Placed On"){
							if(!empty($start) && !empty($end)){
                                $this->paginate = [
                                                'conditions' =>[
                                                          'KioskOrders.kiosk_id'=>$kiosk_id,
																							'KioskOrders.status' => '2',
																							'KioskOrders.kiosk_placed_order_id IN' =>$kiosk_placed_ids
                                                                ],
                                                'limit' => ROWS_PER_PAGE,
                                                'order' => ['KioskOrders.dispatched_on' => 'desc'],
                                                'contain' => ['Kiosks']
                                              ];
					 
								 
							}else{
                                 $this->paginate = [
                                                'conditions' =>[
                                                         'KioskOrders.kiosk_id'=>$kiosk_id,
														'KioskOrders.status' => '2'
                                                                ],
                                                'limit' => ROWS_PER_PAGE,
                                                'order' => ['KioskOrders.dispatched_on' => 'desc'],
                                                'contain' => ['Kiosks']
                                              ];
									 								
							}
					}else{
						if(!empty($start) && !empty($end)){
                             $this->paginate = [
                                                'conditions' =>[
                                                          'KioskOrders.kiosk_id'=>$kiosk_id,
																				'KioskOrders.status' => '2',
																				"DATE(KioskOrders.dispatched_on) > '$start'" ,
																				"DATE(KioskOrders.dispatched_on) < '$end'"
                                                                ],
                                                'limit' => ROWS_PER_PAGE,
                                                'order' => ['KioskOrders.dispatched_on' => 'desc'],
                                                'contain' => ['Kiosks']
                                              ];
							 
						}else{
                             $this->paginate = [
                                                'conditions' =>[
                                                          'KioskOrders.kiosk_id'=>$kiosk_id,
															'KioskOrders.status' => '2'
                                                                ],
                                                'limit' => ROWS_PER_PAGE,
                                                'order' => ['KioskOrders.dispatched_on' => 'desc'],
                                                'contain' => ['Kiosks']
                                              ];
							 
						}										 
					}
					
			}else{
				  //echo'3';die;
                $kiosk_placed_ids = array();
				 $kiosk_placed_orders = array();
					if(!empty($start) && !empty($end)){
                        
                       $kiosk_placed_orders_query = $this->KioskPlacedOrders->find('all',[
                                                                                    'conditions' => [//"KioskPlacedOrder.id" =>$kiosk_placed_orderid,
																										   "DATE(KioskPlacedOrders.created) > '$start'" ,
																										 "DATE(KioskPlacedOrders.created) < '$end'"
																									],
																					'fields'=>['KioskPlacedOrders.id'] 
																					 
																				  ]);
                       if(!empty($kiosk_placed_orders_query)){
                         $kiosk_placed_orders = $kiosk_placed_orders_query->toArray();
                       }
                       
					}
                   
					foreach($kiosk_placed_orders as $kiosk_placed_order){
							   //$kiosk_placed_ids[]   =
							  $kiosk_placed_ids[$kiosk_placed_order['id']] =  $kiosk_placed_order['id'];
					}
                     if(empty($kiosk_placed_ids)){
                        $kiosk_placed_ids = array(0=>null);
                    }
                    if($type == "Placed On"){
                       	if(!empty($start) && !empty($end)){
						   // ex site code
						   if($ext_site == 1){
									$managerKiosk = $this->get_kiosk();
									if(!empty($managerKiosk)){
										$this->paginate = [
                                                'conditions' =>[
                                                          'KioskOrders.status' => '2',
														  'KioskOrders.kiosk_placed_order_id IN' =>$kiosk_placed_ids,
														  'KioskOrders.kiosk_id IN' => $managerKiosk,
                                                                ],
                                                'limit' => ROWS_PER_PAGE,
                                                'order' => ['KioskOrders.dispatched_on' => 'desc'],
                                                'contain' => ['Kiosks']
                                              ];
									}else{
											 $this->paginate = [
                                                'conditions' =>[
                                                          'KioskOrders.status' => '2',
														  'KioskOrders.kiosk_placed_order_id IN' =>$kiosk_placed_ids 
                                                                ],
                                                'limit' => ROWS_PER_PAGE,
                                                'order' => ['KioskOrders.dispatched_on' => 'desc'],
                                                'contain' => ['Kiosks']
                                              ];
									}
						   }else{
									$this->paginate = [
                                                'conditions' =>[
                                                          'KioskOrders.status' => '2',
														  'KioskOrders.kiosk_placed_order_id IN' =>$kiosk_placed_ids 
                                                                ],
                                                'limit' => ROWS_PER_PAGE,
                                                'order' => ['KioskOrders.dispatched_on' => 'desc'],
                                                'contain' => ['Kiosks']
                                              ];		
						   }
                             
							 
						}else{
						   if($ext_site == 1){
									$managerKiosk = $this->get_kiosk();
									if(!empty($managerKiosk)){
										$this->paginate = [
                                                'conditions' =>[
                                                          'KioskOrders.status' => '2',
														  'KioskOrders.kiosk_id IN' => $managerKiosk,
														  ],
                                                'limit' => ROWS_PER_PAGE,
                                                'order' => ['KioskOrders.dispatched_on' => 'desc'],
                                                'contain' => ['Kiosks']
                                              ];
									}else{
											 $this->paginate = [
                                                'conditions' =>[
                                                          'KioskOrders.status' => '2' 
														  ],
                                                'limit' => ROWS_PER_PAGE,
                                                'order' => ['KioskOrders.dispatched_on' => 'desc'],
                                                'contain' => ['Kiosks']
                                              ];
									}
							   }else{
									$this->paginate = [
                                                'conditions' =>[
                                                          'KioskOrders.status' => '2' 
														  ],
                                                'limit' => ROWS_PER_PAGE,
                                                'order' => ['KioskOrders.dispatched_on' => 'desc'],
                                                'contain' => ['Kiosks']
                                              ];	
							   }
                               
							 
						}
					}else{
						if(!empty($start) && !empty($end)){
						   if($ext_site == 1){
									$managerKiosk = $this->get_kiosk();
									if(!empty($managerKiosk)){
										$this->paginate = [
                                                'conditions' =>[
                                                         'KioskOrders.status' => '2',
																				"DATE(KioskOrders.dispatched_on) > '$start'" ,
																				"DATE(KioskOrders.dispatched_on) < '$end'",
																				'KioskOrders.kiosk_id IN' => $managerKiosk,
														  ],
                                                'limit' => ROWS_PER_PAGE,
                                                'order' => ['KioskOrders.dispatched_on' => 'desc'],
                                                'contain' => ['Kiosks']
                                              ];
									}else{
											 $this->paginate = [
                                                'conditions' =>[
                                                         'KioskOrders.status' => '2',
																				"DATE(KioskOrders.dispatched_on) > '$start'" ,
																				"DATE(KioskOrders.dispatched_on) < '$end'"
														  ],
                                                'limit' => ROWS_PER_PAGE,
                                                'order' => ['KioskOrders.dispatched_on' => 'desc'],
                                                'contain' => ['Kiosks']
                                              ];
									}
							   }else{
									$this->paginate = [
                                                'conditions' =>[
                                                         'KioskOrders.status' => '2',
																				"DATE(KioskOrders.dispatched_on) > '$start'" ,
																				"DATE(KioskOrders.dispatched_on) < '$end'"
														  ],
                                                'limit' => ROWS_PER_PAGE,
                                                'order' => ['KioskOrders.dispatched_on' => 'desc'],
                                                'contain' => ['Kiosks']
                                              ];		
							   }
                             
							 
						}else{
							if($ext_site == 1){
									$managerKiosk = $this->get_kiosk();
									if(!empty($managerKiosk)){
										$this->paginate = [
                                                'conditions' =>[
                                                         'KioskOrders.status' => '2',
													     'KioskOrders.kiosk_id IN' => $managerKiosk,				 
														  ],
                                                'limit' => ROWS_PER_PAGE,
                                                'order' => ['KioskOrders.dispatched_on' => 'desc'],
                                                'contain' => ['Kiosks']
                                              ];
									}else{
											 $this->paginate = [
                                                'conditions' =>[
                                                         'KioskOrders.status' => '2' 
																			 
														  ],
                                                'limit' => ROWS_PER_PAGE,
                                                'order' => ['KioskOrders.dispatched_on' => 'desc'],
                                                'contain' => ['Kiosks']
                                              ];
									}
						   }else{
							$this->paginate = [
                                                'conditions' =>[
                                                         'KioskOrders.status' => '2' 
																			 
														  ],
                                                'limit' => ROWS_PER_PAGE,
                                                'order' => ['KioskOrders.dispatched_on' => 'desc'],
                                                'contain' => ['Kiosks']
                                              ];		
						   }		
						}
					}
				}
		}
		//pr($this->paginate);die;
        $kioskOrders_query = $this->paginate($this->KioskOrders);
        
        if(!empty($kioskOrders_query)){
            $kioskOrders = $kioskOrders_query->toArray();
        }
		 
		//pr($kioskOrders);
		$kiosk_placed_orderid = array();
		foreach($kioskOrders as $kioskOrder){
			if((int)$kioskOrder['kiosk_placed_order_id']){
			  $kiosk_placed_orderid[] = $kioskOrder['kiosk_placed_order_id'];
			}
		}
        $kiosk_placed_user_id_query = $this->KioskPlacedOrders->find('list',[
                                                                       'conditions' => $kiosk_placed_orderid,
                                                                        'keyField' => 'id',
                                                                        'valueField' => 'user_id'
																		 
                                                                      ]);
        if(!empty($kiosk_placed_user_id_query)){
            $kiosk_placed_user_id = $kiosk_placed_user_id_query->toArray();
		
        }
		$kiosk_placed_user_date_query = $this->KioskPlacedOrders->find('list',[
                                            'conditions' => $kiosk_placed_orderid,
                                             'keyField' => 'id',
                                              'valueField' => 'created'
        ]);
		 if(!empty($kiosk_placed_user_date_query)){
            $kiosk_placed_user_date = $kiosk_placed_user_date_query->toArray();
		
        }			 
		 
		  $kiosk_placed_merged_orders = $this->KioskPlacedOrders->find('list',
                                                                 [
                                                        			'conditions' => array("id IN" => $kiosk_placed_orderid),
                                                                    'keyField' => 'id',
                                                                    'valueField' => 'merged',
																	//'recursive' =>-1
																])->toArray();
		 
		
		$hint = $this->ScreenHint->hint('kiosk_orders','confirmed_orders');
        if(!$hint){
            $hint = "";
        }
		$this->set(compact('orderOptions'));
		$this->set(compact('hint','kioskOrders','users','kiosks','kiosk_id','kiosk_placed_user_date','kiosk_placed_user_id','kiosk_placed_merged_orders'));

		$this->render('confirmed_orders');
	}
    public function receiveKioskOrder($order_id = null)
    {
		 $settingArr = $this->setting;
		 $domain_name = $settingArr['domain_name'];
        $user_id = $this->request->session()->read('Auth.User.id'); 	//rasa	
        $datetime = date('Y-m-d H:i:s');
        $receiptTable_source = "stock_transfer_by_kiosk";
        $StockTransferTable = TableRegistry::get($receiptTable_source,[
                                                                    'table' => $receiptTable_source,
                                                                        ]);
        //$this->StockTransfer->setSource('stock_transfer_by_kiosk');
        $products = $StockTransferTable->find('all', array(
                            'conditions' => array('kiosk_order_id' => $order_id),
                            //'recursive' => -1
                            ));
        $products->hydrate(false);
        if(!empty($products)){
            $products = $products->toArray();
        }
        $counter=0;
        foreach($products as $product){
            $product_id = $product['product_id'];
            $quantity = $product['quantity'];
            $sale_price = $product['sale_price'];
            $conn = ConnectionManager::get('default');
            $stmt = $conn->execute("UPDATE `products` SET
                        `quantity`=`quantity`+ $quantity
                            WHERE `products`.`id`=$product_id");
				  $path = dirname(__FILE__);
				  if(in_array($domain_name,array("fonerevive","mbwaheguru"))){
						   $isboloRam = strpos($path,$domain_name);
						   if($isboloRam != false){
									   $connection = ConnectionManager::get('hpwaheguru');
									   $stmt = $connection->execute("UPDATE `products` SET
												   `quantity`=`quantity`+ $quantity
													   WHERE `products`.`id`=$product_id");			   
						   }	   
				  }
				  
            //$updateQuery = "UPDATE `products` SET
            //            `quantity`=`quantity`+ $quantity
            //                WHERE `products`.`id`=$product_id";
            //$this->Product->query($updateQuery);
            $counter++;
        }
		if($counter>0){
            $receiptTable_source = "center_orders";
            $KioskOrderTable = TableRegistry::get($receiptTable_source,[
                                                                    'table' => $receiptTable_source,
                                                                        ]);
			//$this->KioskOrder->setSource('center_orders');
			$KioskOrderTable->id = $order_id;
            $kioskorder_entity = $KioskOrderTable->get($order_id);
			$data = array('received_on' => $datetime, 'received_by' => $user_id,'status' => 2);
            $kioskorder_entity = $KioskOrderTable->patchEntity($kioskorder_entity,$data);
			$KioskOrderTable->save($kioskorder_entity);
			$flashMessage = "Order received successfully.";
			$this->Flash->success($flashMessage);
			//$redirectURL = Router::url( array('controller'=> 'kiosk_orders','action' => 'confirmed_kiosk_orders'), true );
			//header("Location:$redirectURL");
			return $this->redirect(array('action' => 'confirmed_kiosk_orders'));
		//user header if not re
		}else{
			$flashMessage =  "Failed to process order";
			$this->Flash->error(__("$flashMessage"));
			//$redirectURL = Router::url( array('controller'=> 'kiosk_orders','action' => 'transient_ordres'), true );
			//header("Location:$redirectURL");
			return $this->redirect(array('action' => 'transient_kiosk_orders'));
		}
    }
    
    public function confirmedKioskOrders()
    {
		 $orderOptions = $active = Configure::read('order_status');
        $receiptTable_source = "center_orders";
        $KioskOrderTable = TableRegistry::get($receiptTable_source,[
                                                                    'table' => $receiptTable_source,
                                                                        ]);
        //$this->KioskOrder->setSource('center_orders');
		$kiosk_id = $this->request->Session()->read('kiosk_id');
		if(!empty($kiosk_id)){
			$conditions = array('center_orders.status' => "2",'center_orders.kiosk_id' => $kiosk_id);
		}else{
		 $orderOptions = $active = Configure::read('order_status');
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
				   $conditions = array('center_orders.status' => "2",'center_orders.kiosk_id IN' => $managerKiosk);
			   }else{
				  $conditions = array('center_orders.status' => "2");
			   }
		  }else{
				  $conditions = array('center_orders.status' => "2");
		  }
		}
		//Configure::load('common-arrays');
		
		
		
		$this->paginate = [
                                'conditions' => $conditions,
                                'order' => ['center_orders.dispatched_on' => 'desc'],
                                'limit' => ROWS_PER_PAGE,
                                'contain' => ['Kiosks'],
			              ];
		$users = $this->Users->find('list',
                                    [
                                      'keyField' => 'id',
                                      'valueField' => 'username'
                                    ]);
        $users = $users->toArray();
		$kiosks = $this->Kiosks->find('list',[
                                                'keyField' => 'id',
                                                'valueField' => 'name',
                                                'conditions'=>['Kiosks.status'=>1],
                                                'order' => ['Kiosks.name asc']
                                            ]);
        $kiosks = $kiosks->toArray();
		$KioskOrderTable->recursive = 0;
		
		$hint = $this->ScreenHint->hint('kiosk_orders','confirmed_kiosk_orders');
        if(!$hint){
            $hint = "";
        }
		
		$this->set(compact('hint','orderOptions','users','kiosks'));
        $kioskOrders = $this->paginate($KioskOrderTable);
        $kioskOrders = $kioskOrders->toArray();
		$this->set('kioskOrders',$kioskOrders);
    }
    
    public function confirmedKioskOrdersSearch()
    {
        //Configure::load('common-arrays');
		$orderOptions = $active = Configure::read('order_status');
        $receiptTable_source = "center_orders";
        $KioskOrderTable = TableRegistry::get($receiptTable_source,[
                                                                    'table' => $receiptTable_source,
                                                                        ]);
		//$this->KioskOrder->setSource("center_orders");
        $receiptTable_source = "stock_transfer_by_kiosk";
        $StockTransferTable = TableRegistry::get($receiptTable_source,[
                                                                    'table' => $receiptTable_source,
                                                                        ]);
		//$this->StockTransfer->setSource('stock_transfer_by_kiosk');
		$searchKW = $this->request->query['search_kw'];
		$kiosks = $this->Kiosks->find('list',[
                                                'keyField' => 'id',
                                                'valueField' => 'name',
                                                'conditions'=>['Kiosks.status'=>1],
                                                'order' => ['Kiosks.name asc']
                                             ]);
        $kiosks = $kiosks->toArray();
		$users_query = $this->Users->find('list',
                                        [
                                            'keyField' => 'id',
                                            'valueField' => 'username',
                                            'order' => ['Users.username asc']
                                        ]);
        if(!empty($users_query)){
              $users = $users_query->toArray();
        }else{
            $users = array();
        }
      
		$firstDay = date("Y-m-1");
		$lastDay = date("Y-m-t");//last day of month - 2016-01-31
		//$start = date("Y-m-d",strtotime("-1 day",strtotime($firstDay))); //returned 2015-12-31 when passed 2016-01-1
		//$end = date("Y-m-d",strtotime("+1 day",strtotime($lastDay))); // passed 2016-01-31 and returned 2015-12-31
		
		$kioskId = $this->request->Session()->read('kiosk_id');
		$conditionArr =array();
		if(!empty($kioskId)){
			//search is running for kiosk
			if(!empty($searchKW)){
				$conditionArr['OR']['LOWER(Products.product_code) like '] =  strtolower("%$searchKW%");
				$conditionArr['OR']['LOWER(Products.product) like '] =  strtolower("%$searchKW%");
				$products_query = $this->Products->find('all', array(
						'fields' => array('Products.id'),
						'conditions' => $conditionArr,
						'recursive' => -1
					)
				);
				$products_query = $products_query->hydrate(false);
                if(!empty($products_query)){
                    $products = $products_query->toArray();
                }else{
                    $products = array();
                }
               // pr($products);die;
				$productId = "";
				//if(array_key_exists('Product',$products)){
				//	$productId = $products['Product']['id'];
				//}
				$resultIds = array();
				if(count($products)){
					foreach($products as $key => $singlePrdct){
						$resultIds[$singlePrdct['id']] = $singlePrdct['id'];
					}
				}
                if(empty($resultIds)){
                    $resultIds = array('0'=>null);
                }
				$stocktransfers_query = $StockTransferTable->find('all', array(
					'conditions' => array('product_id IN' => $resultIds),
					'fields' => array('product_id','kiosk_order_id'),
					'recursive' => -1
					)
				);
                $stocktransfers_query = $stocktransfers_query->hydrate(false);
                if(!empty($stocktransfers_query)){
                    $stocktransfers = $stocktransfers_query->toArray();
                }else{
                    $stocktransfers = array();
                }
				$kiosk_order_id = array();
				if(count($stocktransfers)){
					foreach($stocktransfers as $stocktransfer){
							$kiosk_order_id[] = $stocktransfer['kiosk_order_id'];
					}
				}
                 if(empty($kiosk_order_id)){
                    $kiosk_order_id = array('0'=>null);
                }
				$this->paginate = [
                                    'conditions' => [
                                                        'center_orders.id IN' => $kiosk_order_id,
                                                        'center_orders.status' => '2',
                                                        'center_orders.kiosk_id' =>$kioskId
                                                    ],
                                    'limit' => ROWS_PER_PAGE,
                                    'order' => ['center_orders.dispatched_on desc'],
                                    'contain' => ['Kiosks']
                                ];
				
			}else{
				
				//when kiosk user is searching nothing
				$this->paginate = [
                                    'conditions' => [
                                                        'center_orders.status' => '2',
                                                        'center_orders.kiosk_id' =>$kioskId
                                                    ],
									'limit' => ROWS_PER_PAGE,
									'order' => ['center_orders.dispatched_on desc'],
                                    'contain' => ['Kiosks']
                                ];
			}
		}else{
			//search is running for admin and manager
            //pr($this->request);die;
			if(array_key_exists('KioskOrder',$this->request->query)){
				$kiosk_id = $this->request->query['KioskOrder']['kiosk_id'];
			}else{
				$kiosk_id = "";
			}
            //echo $kiosk_id;die;
			$firstDay = date("Y-m-1");
			$lastDay = date("Y-m-t");//last day of month - 2016-01-31
            $start_date = $this->request->query['start_date'];
			if(!empty($start_date)){
					$firstDay = strtotime($start_date);
					$start = date("Y-m-d",strtotime("-1 day",$firstDay));
			}
					
			$end_date = $this->request->query['end_date'];
			if(!empty($end_date)){
					$lastDay = strtotime($end_date);
					$end = date("Y-m-d",strtotime("+1 day",$lastDay));
			}
			if(!empty($searchKW) && empty($kiosk_id) ){
				//finding records only if we have product code/title and all other are empty
				$conditionArr['OR']['LOWER(Products.product_code) like '] =  strtolower("%$searchKW%");
				$conditionArr['OR']['LOWER(Products.product) like '] =  strtolower("%$searchKW%");
				$products = $this->Products->find('all', array(
																'fields' => array('Products.id'),
																'conditions' => $conditionArr,
																'recursive' => -1
															)
												);
                $products->hydrate(false);
                if(!empty($products)){
                    $products = $products->toArray();
                }
				//pr($products);
				$resultIds = array();
				if(count($products)){
					foreach($products as $key => $singlePrdct){
						$resultIds[] = $singlePrdct['id'];
					}
				}
				if(empty($resultIds)){
				  $resultIds = array(0 => null);
				}
				$transferredByKiosk = $StockTransferTable->find('all', array(
																			'conditions' => array('stock_transfer_by_kiosk.product_id IN' => $resultIds),
																			'fields' => array('product_id','kiosk_order_id'),
																			'recursive' => -1
																			)
															);
                $transferredByKiosk->hydrate(false);
                if(!empty($transferredByKiosk)){
                    $transferredByKiosk = $transferredByKiosk->toArray();
                }
				$kiosk_order_id = array();
				if(count($transferredByKiosk)){
					foreach($transferredByKiosk as $stocktransfer){
						$kiosk_order_id[] = $stocktransfer['kiosk_order_id'];
					}
				}
				//pr($kiosk_order_id);
				//echo "<pre>";print_r( $resultIds);die;
				 if(empty($kiosk_order_id)){
				  $kiosk_order_id = array(0 => null);
				 }
				$this->paginate = [
                                    'conditions' => ['center_orders.id IN' => $kiosk_order_id,
                                                          'center_orders.status' => '2',
                                                    ],
                                    'limit' => ROWS_PER_PAGE,
                                    'order' => ['center_orders.dispatched_on desc'],
                                    'contain' => ['Kiosks']
                                ];       
			}elseif(!empty($kiosk_id) && !empty($searchKW)){
                //echo 'hi';die;
				//searching with kiosk and product code/ product title
				//echo "searching with kiosk and product code/ product title";
				$conditionArr['OR']['LOWER(Products.product_code) like '] =  strtolower("%$searchKW%");
				$conditionArr['OR']['LOWER(Products.product) like '] =  strtolower("%$searchKW%");
				
				$products = $this->Products->find('all', array(
						'fields' => array('Products.id'),
						'conditions' => $conditionArr,
						'recursive' => -1
					)
				);
                $products->hydrate(false);
                if(!empty($products)){
                    $products = $products->toArray();
                }
				//pr($products);
				$resultIds = array();
				if(count($products)){
					foreach($products as $key => $singlePrdct){
						$resultIds[$singlePrdct['id']] = $singlePrdct['id'];
					}
				}
				if(empty($resultIds)){
				  $resultIds = array(0 => null);
				}
				$stocktransfers = $StockTransferTable->find('all', array(
					'conditions' => array('stock_transfer_by_kiosk.product_id IN' => $resultIds),
					'fields' => array('product_id','kiosk_order_id'),
					'recursive' => -1
					)
				);
                $stocktransfers->hydrate(false);
                if(!empty($stocktransfers)){
                    $stocktransfers = $stocktransfers->toArray();
                }
				//pr($stocktransfers);die;
				$kiosk_order_id = array();
				if(count($stocktransfers)){
					foreach($stocktransfers as $stocktransfer){
						$kiosk_order_id[] = $stocktransfer['kiosk_order_id'];
					}
				}
				//pr($kiosk_order_id);
				if(empty($kiosk_order_id)){
				  $kiosk_order_id = array(0 => null);
				}
				if(!empty($start) && !empty($end)){
                    //echo'hi';die;
					$this->paginate = [
                                        'conditions' => [
                                                            'center_orders.id IN' => $kiosk_order_id,
                                                            'center_orders.kiosk_id IN' => $kiosk_id,
                                                            'center_orders.status' => '2',
                                                            "DATE(center_orders.dispatched_on) > '$start'" ,
                                                            "DATE(center_orders.dispatched_on) < '$end'"
                                                        ],
                                        'limit' => ROWS_PER_PAGE,
                                        'order' => ['center_orders.dispatched_on desc'],
                                        'contain' => ['Kiosks']
                                      ];
				}else{
					$this->paginate = [
                                        'conditions' => [
                                                            'center_orders.id IN' => $kiosk_order_id,
                                                            'center_orders.kiosk_id IN' => $kiosk_id,
                                                            'center_orders.status' => '2'
                                                        ],
                                        'limit' => ROWS_PER_PAGE,
                                        'order' => ['center_orders.dispatched_on desc'],
                                        'contain' => ['Kiosks']
                                      ];
				}
			}elseif(!empty($kiosk_id) && empty($searchKW)){
					//echo "fourth";die;
					if(!empty($start) && !empty($end)){
						$this->paginate = [
                                            'conditions' => [
                                                                'center_orders.kiosk_id IN'=>$kiosk_id,
                                                                'center_orders.status' => '2',
                                                                "DATE(center_orders.dispatched_on) > '$start'" ,
                                                                "DATE(center_orders.dispatched_on) < '$end'"
                                                            ],
                                                'limit' => ROWS_PER_PAGE,
                                                'order' => ['center_orders.dispatched_on desc'],
                                                'contain' => ['Kiosks']
                                        ];
					}else{
                        //echo 'hi';die;
						$this->paginate = [
                                            'conditions' => [
                                                                'center_orders.kiosk_id IN'=>$kiosk_id,
                                                                'center_orders.status' => '2'
                                                            ],
                                            'limit' => ROWS_PER_PAGE,
                                            'order' => ['center_orders.dispatched_on desc'],
                                            'contain' => ['Kiosks']
                                          ];
                        //pr($this->paginate);die;
					}
			}else{
				if(!empty($start) && !empty($end)){
						$this->paginate = [
                                            'conditions' => [
                                                                'center_orders.status' => '2',
                                                                "DATE(center_orders.dispatched_on) > '$start'" ,
                                                                "DATE(center_orders.dispatched_on) < '$end'"
                                                            ],
                                            'limit' => ROWS_PER_PAGE,
                                            'order' => ['center_orders.dispatched_on desc'],
                                            'contain' => ['Kiosks']
                                          ];
					}else{ 
							$this->paginate = [
                                                'conditions' => ['center_orders.status' => '2'],
                                                'limit' => ROWS_PER_PAGE,
                                                'order' => ['center_orders.dispatched_on desc'],
                                                'contain' => ['Kiosks']
                                              ];
					}
				
			}
		}
        //pr($this->paginate);die;
		$kioskOrders = $this->paginate($KioskOrderTable);
        
        //pr($kioskOrders);die;
		$kiosk_placed_orderid = array();
        //pr($kioskOrders);die;
		foreach($kioskOrders as $kioskOrder){
            //pr($kioskOrder);die;
			if(array_key_exists('kiosk_placed_order_id', $kioskOrder) &&(int)$kioskOrder['KioskOrder']['kiosk_placed_order_id']){
			  $kiosk_placed_orderid[] = $kioskOrder['KioskOrder']['kiosk_placed_order_id'];
			}
		}
		//pr($kiosk_placed_orderid);
		$kiosk_placed_user_id = $this->KioskPlacedOrders->find('list',[
                                                                        'conditions' => $kiosk_placed_orderid,
                                                                        'fields'=>['KioskPlacedOrders.id','KioskPlacedOrders.user_id'],
                                                                        'recursive' =>-1
                                                                      ]);
        $kiosk_placed_user_id = $kiosk_placed_user_id->toArray();
		$kiosk_placed_user_date = $this->KioskPlacedOrders->find('list',[
                                                                            'conditions' => $kiosk_placed_orderid,
                                                                            'fields'=>['KioskPlacedOrders.id','KioskPlacedOrders.created'],
                                                                            'recursive' =>-1
                                                                        ]);
        $kiosk_placed_user_date = $kiosk_placed_user_date->toArray();
		$hint = $this->ScreenHint->hint('kiosk_orders','confirmed_kiosk_orders');
        if(!$hint){
            $hint = "";
        }

		$this->set(compact('orderOptions'));
		$this->set(compact('hint','kioskOrders','users','kiosks','kiosk_id','kiosk_placed_user_id','kiosk_placed_user_date'));
		$this->render('confirmed_kiosk_orders');
    }
    public function placedOrders() {
		 $settingArr = $this->setting;
		 $this->set(compact('settingArr'));
        $kiosk_query = $this->Kiosks->find('list',
                                      [
                                        'conditions'=>['Kiosks.id NOT IN'=>10000,
													   'Kiosks.status' => 1
													   ],
                                        'keyField' => 'id',
                                        'valueField' => 'name',
                                        'order' => 'Kiosks.name asc'
                                    ]);
        if(!empty($kiosk_query)){
            $kioskDropdown = $kiosk_query->toArray();    
        }
		$kiosks_query = $this->Kiosks->find('list',
                                      [
                                         
                                        'keyField' => 'id',
                                        'valueField' => 'name',
                                        'order' => 'Kiosks.name asc'
                                    ]);
        if(!empty($kiosk_query)){
            $kiosks = $kiosks_query->toArray();    
        }
		//$kiosks = $this->Kiosk->find('list', array('fields' => array('id', 'name'),
		//											'conditions' => array('Kiosk.kiosk_type' => 1 , 'Kiosk.kiosk_type' => 2),
		//											'order' => 'Kiosk.name asc'
		//										));
		$users_query = $this->Users->find('list',
                                    [
                                        'keyField' => 'id',
                                        'valueField' => 'username',
                                    ]);
        if(!empty($users_query)){
             $users = $users_query->toArray();
        }
        $cronUser_query = $this->Users->find('all',
                                    [
                                        'keyField' => 'id',
                                        'valueField' => 'username',
                                        'conditions'=>['Users.username'=>'cronjob'],
                                    ]);
       
        if(!empty($cronUser_query)){
               $cronUser = $cronUser_query->first();
        } else{
            $cronUser = array();
        }
         $croneId = $cronUser['id']; 
		/*$kioskPlacedOrder = $this->KioskPlacedOrder->find('first', array(
									'recursive' => -1,
									'conditions' => array(
										'DATE(KioskPlacedOrder.created)' => date('Y-m-d')
									),
									'order' => array('KioskPlacedOrder.id Desc'),
								     ));
		*/
		$kioskId = '';
		$kiosk_id = $this->request->Session()->read('kiosk_id');
        if($this->request->is(array('get','put'))){
            if(array_key_exists('kiosk',$this->request->query)){
                $kioskId = $this->request->query['kiosk'];
            }
            elseif((int)$kiosk_id){//in case of kiosks
                $kioskId = $kiosk_id;
             }
        }
        if(!empty($kioskId)){
            $this->paginate = [
                                        'conditions' => ['KioskPlacedOrders.status' => 0,'KioskPlacedOrders.kiosk_id' => $kioskId],
                                        'limit' => ROWS_PER_PAGE,
                                        'order' => ['KioskPlacedOrders.id desc']
                                       // 'contain' => ['Kiosks']
                                      ];
            
            
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
				   $this->paginate = [
                                        'conditions' => ['KioskPlacedOrders.status' => 0,
														 'KioskPlacedOrders.kiosk_id IN' => $managerKiosk,
														 ],
                                        'limit' => ROWS_PER_PAGE,
                                       'order' => ['KioskPlacedOrders.id desc']
                                       // 'contain' => ['Kiosks']
                                      ];
			   }else{
				  $this->paginate = [
                                        'conditions' => ['KioskPlacedOrders.status' => 0],
                                        'limit' => ROWS_PER_PAGE,
                                       'order' => ['KioskPlacedOrders.id desc']
                                       // 'contain' => ['Kiosks']
                                      ];
			   }
		  }else{
			$this->paginate = [
                                        'conditions' => ['KioskPlacedOrders.status' => 0],
                                        'limit' => ROWS_PER_PAGE,
                                       'order' => ['KioskPlacedOrders.id desc']
                                       // 'contain' => ['Kiosks']
                                      ];	  
		  }
		 
             
             
        }
         //  pr( $this->paginate);die;
       //  $this->paginate = array();
		 $data_query = $this->paginate('KioskPlacedOrders');
     
        if(!empty($data_query)){
            $data = $data_query->toArray();
        }
		$hint = $this->ScreenHint->hint('kiosk_orders','placed_orders');
        if(!$hint){
            $hint = "";
        }
		$this->set(compact('hint','kiosk','kiosks','croneId','users','kioskDropdown'));
        $kioskPlacedOrders = $data;
		$this->set('data', $data);
		//pr($kioskPlacedOrder);
	}
    public function restore($id =null){
        $this->KioskPlacedOrders->updateAll(['status' =>0], ['id' => $id]);   
		 
		return $this->redirect(array('action' => 'placedOrders')); 
		 
	}
	public function placeOrderTrash($id= null){
		$this->KioskPlacedOrders->updateAll(['status' =>9], ['id' => $id]);
		$this->Flash->success("Order Moved To Trash successfully!");
		return $this->redirect(array('action' =>'placedOrders'));
	}
    public function deleteOrder($id = null) {
		 $KioskPlacedOrdersEntity = $this->KioskPlacedOrders->get($id);
		if($this->KioskPlacedOrders->delete($KioskPlacedOrdersEntity)){
            $this->Flash->success("The kiosk order has been deleted.");
			 
		} else {
             $this->Flash->success("The kiosk order could not be deleted. Please, try again.");
			 
		}
		return $this->redirect(array('action' => 'trash'));
	}
     public function delete($id = null)
    {
        $this->request->allowMethod(['post', 'delete']);
        $KioskOrders = $this->KioskOrders->get($id);
        if ($this->KioskOrders->delete($KioskOrders)) {
            $this->Flash->success(__('The kiosk order has been deleted.'));
        } else {
            $this->Flash->error(__('The kiosk order could not be deleted. Please, try again.'));
        }

        return $this->redirect(['action' => 'index']);
    }
	public function trash() {
		//die;
       // pr($this->request->query); die;
		if(array_key_exists('delete',$this->request->query)){
            $this->KioskPlacedOrders->deleteAll(array('KioskPlacedOrders.status' => 9), false);
		}
		if(array_key_exists('checked',$this->request->data)){
			$counter = 0;
            //pr($this->request->data['checked']);die;
			foreach($this->request->data['checked'] as $key => $value){
                $delete_id= $this->KioskPlacedOrders->get($key);
               // $this->KioskPlacedOrders->delete ($delete_id)
				if($this->KioskPlacedOrders->delete ($delete_id)){
					$counter++;
				}
			}
			if($counter >0){
                 $this->Flash->success(__('Order Deleted successfully!'));
                
			}
		}
         $kiosk_query = $this->Kiosks->find('list',
                                      [
                                        'conditions'=>['Kiosks.id NOT IN'=>10000],
                                        'keyField' => 'id',
                                        'valueField' => 'name',
                                        'order' => 'Kiosks.name asc'
                                    ]);
        if(!empty($kiosk_query)){
            $kioskDropdown = $kiosk_query->toArray();    
        }
		$kiosks_query = $this->Kiosks->find('list',
                                      [
                                         
                                        'keyField' => 'id',
                                        'valueField' => 'name',
                                        'order' => 'Kiosks.name asc'
                                    ]);
        if(!empty($kiosk_query)){
            $kiosks = $kiosks_query->toArray();    
        }
		//$kiosks = $this->Kiosk->find('list', array('fields' => array('id', 'name'),
		//											'conditions' => array('Kiosk.kiosk_type' => 1 , 'Kiosk.kiosk_type' => 2),
		//											'order' => 'Kiosk.name asc'
		//										));
		$users_query = $this->Users->find('list',
                                    [
                                        'keyField' => 'id',
                                        'valueField' => 'username',
                                    ]);
        if(!empty($users_query)){
             $users = $users_query->toArray();
        }
        $cronUser_query = $this->Users->find('all',
                                    [
                                        'keyField' => 'id',
                                        'valueField' => 'username',
                                        'conditions'=>['Users.username'=>'cronjob'],
                                    ]);
        	$cronUser_query = $cronUser_query->hydrate(false);
       
        if(!empty($cronUser_query)){
              $cronUser = $cronUser_query->first();
              
        } else{
            $cronUser = array();
        }
        $croneId = $cronUser['id']; 
        $users_query = $this->Users->find('list',
                                    [
                                        'keyField' => 'id',
                                        'valueField' => 'username',
                                    ]);
        if(!empty($users_query)){
             $users = $users_query->toArray();
        }
		$kioskId = '';
		$kiosk_id = $this->request->Session()->read('kiosk_id');
		if($this->request->is(array('get','put'))){
			if(array_key_exists('kiosk',$this->request->query)){
				$kioskId = $this->request->query['kiosk'];
			}elseif((int)$kiosk_id){//in case of kiosks
				   $kioskId = $kiosk_id;
			}
		}
		if(!empty($kioskId)){
              $this->paginate = [
                                        'conditions' => ['KioskPlacedOrders.status' => 9,'KioskPlacedOrders.kiosk_id' => $kioskId],
                                        'limit' => ROWS_PER_PAGE,
                                        'order' => ['KioskPlacedOrders.id desc']
                                       // 'contain' => ['Kiosks']
                                      ];
			 
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
									 $this->paginate = [
                                        'conditions' => [
														 'KioskPlacedOrders.status' => 9,
														 'KioskPlacedOrders.kiosk_id IN' => $managerKiosk
														 ],
                                        'limit' => ROWS_PER_PAGE,
                                        'order' => ['KioskPlacedOrders.id desc']
                                       // 'contain' => ['Kiosks']
                                      ];
						   }else{
								 $this->paginate = [
                                        'conditions' => ['KioskPlacedOrders.status' => 9],
                                        'limit' => ROWS_PER_PAGE,
                                        'order' => ['KioskPlacedOrders.id desc']
                                       // 'contain' => ['Kiosks']
                                      ];		 						
						   }
				  }else{
						   $this->paginate = [
                                        'conditions' => ['KioskPlacedOrders.status' => 9],
                                        'limit' => ROWS_PER_PAGE,
                                        'order' => ['KioskPlacedOrders.id desc']
                                       // 'contain' => ['Kiosks']
                                      ];	 						
				  }
             
				 
        }
        //pr( $this->paginate);die;
		$data_query = $this->paginate('KioskPlacedOrders');
        if(!empty($data_query)){
            $data = $data_query->toArray();
        }
        //pr($data);
		$this->set(compact('kiosks','croneId','users','kioskDropdown'));
		$this->set('data', $data);
	}
    public function searchFromOrders(){
		/*
		 *4 cases: warehouse to kiosk transient/confirmed, kiosk to warehouse transient/confirmed: 2 tables
		 *show listing as per the kiosk_orders table with required fields
		 *switch table source if user chooses kiosk to ware house confirmed/transient
		 *if kiosk to warehouse then hide fields ordererd quantity, dispatched quantity
		 *
		 *logic for items received less: 1. capture kiosk_placed_order_id from kiosk_orders
		 *2. capture the product ids and quantity from kiosk_order_products table as quantity requested.
		 *3. compare the quantity dispatched with the quantity in stock transfer table corresponding to the kiosk_order_id.
		 **/
		$cases = array(
				'1'=>"Warehouse to kiosk transient products",
				'2'=>"Warehouse to kiosk confirmed products",
				'3'=>"Kiosk to warehouse transient products",
				'4'=>"Kiosk to warehouse confirmed products",
				'5'=>'Warehouse to kiosk(All)',
				'6'=> 'Kiosk to warehouse(All)'
			       );
		 
		//fetching ware house to kiosk transient orders, to be shown by default
         $kiosk_id = $this->request->session()->read('kiosk_id');
		 
		if($kiosk_id>0){
			$kioskOrders_query = $this->KioskOrders->find('all',array(
						'conditions'=>array('KioskOrders.status'=>1,
											'KioskOrders.kiosk_id'=>$kiosk_id),
						'recursive'=>-1,
						'fields'=>array('id','kiosk_id','kiosk_placed_order_id')
							)
						);
			//$this->Product->setSource("kiosk_{$kiosk_id}_products");
		}else{
			$kioskOrders_query = $this->KioskOrders->find('all',array(
						'conditions'=>array('KioskOrders.status'=>1),
						'recursive'=>-1,
						'fields'=>array('id','kiosk_id','kiosk_placed_order_id')
							)
						);
		}
		$kioskOrders_query = $kioskOrders_query->hydrate(false);
        if(!empty($kioskOrders_query)){
            $kioskOrders = $kioskOrders_query->toArray();

        }else{
        		 $kioskOrders = array();
        }
        $users_query = $this->Users->find('list',
                                    [
                                        'keyField' => 'id',
                                        'valueField' => 'username',
                                    ]);
		$users_query = $users_query->hydrate(false);
        if(!empty($users_query)){
             $users = $users_query->toArray();
        }else{
		  $users = array();
        }
		$conditionArray = array();
		$inputProductArr = array();
		$inputProduct = array();
		$warehouseToKioskOrders = array();
		$type = '';
		$start_date = '';
		$end_date = '';
		$selectedKiosk = 0;
		$dateCondition = array();
		$receiptTable_source = "stock_transfer";
                    $StockTransferTable = TableRegistry::get($receiptTable_source,[
                                                                                    'table' => $receiptTable_source,
                                                                                ]);
		if($this->request->is('get')){
         
			if(!empty($this->request->query) && array_key_exists('start_date',$this->request->query)){
                //pr($this->request);die;
				$start_date = $this->request->query['start_date'];
				$end_date = $this->request->query['end_date'];
				$type = $this->request->query['type'];
              //  pr($this->request);
             
				$searchKeyword = trim(strtolower($this->request->query['search_kw']));
				//pr($conditionArray);
				if(!empty($searchKeyword)){
					$conditionArray = array(
								'OR' => array(
									      ('Products.product_code like') => "%$searchKeyword%",
									      ('Products.product like') => "%$searchKeyword%"
									)
								);
					$inputProduct_query = $this->Products->find('list',array('conditions'=>$conditionArray,'fields'=>array('id')));
                    if(!empty($inputProduct_query)){
                        $inputProduct = $inputProduct_query->toArray();
                    }else{
						   $inputProduct = array();
					}
					if(empty($inputProduct)){
						   $inputProduct = array(0 => null);
					}
					$inputProductArr = array('product_id IN' => $inputProduct);
        		}
				 
				if(!empty($start_date) && !empty($end_date)){
				$dateCondition = array(
							"created >" => date('Y-m-d', strtotime($start_date)),
							"created <" => date('Y-m-d', strtotime($end_date. ' +1 Days')),			
						       );
				}
               
				if(array_key_exists('kiosks',$this->request->query)){
					 $selectedKiosk = $this->request->query['kiosks'];
				}
				
				if($kiosk_id>0){//for kiosks, only kiosk data will be shown
					$selectedKiosk = $kiosk_id;
				}
				
				
				
				//pr($this->request->query);die;
				$kioskOrders = array();
				if($this->request->query['cases']==2){
					if($selectedKiosk>0){
						$kioskOrders_query = $this->KioskOrders->find('all',array(
																				  'conditions'=>
																				  array('KioskOrders.status'=>2,
																						'KioskOrders.kiosk_id'=>$selectedKiosk,
																						//$dateCondition
																						),
																				  'fields'=>array('id','kiosk_id','kiosk_placed_order_id')
																				  )
																	  );
					}else{
						$kioskOrders_query = $this->KioskOrders->find('all',array('conditions'=>array('KioskOrders.status'=>2,
																									  $dateCondition
																									  ),
																				  'fields'=>array('id','kiosk_id','kiosk_placed_order_id')));
					}
					$kioskOrders_query = $kioskOrders_query->hydrate(false);
					if(!empty($kioskOrders_query)){
						$kioskOrders = $kioskOrders_query->toArray();   
					}else{
						$kioskOrders = array();   
					}
					$warehouseToKioskOrders = $kioskOrders;
				}elseif($this->request->query['cases']==3){
                     $receiptTable_source = "center_orders";
                     $KioskOrderTable = TableRegistry::get($receiptTable_source,[
                                                                                    'table' => $receiptTable_source,
                                                                                ]);
                        //$this->KioskOrder->setSource("center_orders");
                    $receiptTable_source = "stock_transfer_by_kiosk";
                    $StockTransferTable = TableRegistry::get($receiptTable_source,[
                                                                                    'table' => $receiptTable_source,
                                                                                ]);
					//$this->KioskOrder->setSource('center_orders');
					//$this->StockTransfer->setSource('stock_transfer_by_kiosk');
					if($selectedKiosk>0){
						$kioskOrders_query = $KioskOrderTable->find('all',array('conditions'=>array('status'=>1,'kiosk_id'=>$selectedKiosk),'recursive'=>-1,'fields'=>array('id','kiosk_id')));
					}else{
						$kioskOrders_query = $KioskOrderTable->find('all',array('conditions'=>array('status'=>1),'recursive'=>-1,'fields'=>array('id','kiosk_id')));
					}
					$kioskOrders_query = $kioskOrders_query->hydrate(false);
					if(!empty($kioskOrders_query)){
						   $kioskOrders = $kioskOrders_query->toArray();
					}else{
						   $kioskOrders = array();
					}
					
				}elseif($this->request->query['cases']==4){
					 $receiptTable_source = "center_orders";
                     $KioskOrderTable = TableRegistry::get($receiptTable_source,[
                                                                                    'table' => $receiptTable_source,
                                                                                ]);
                        //$this->KioskOrder->setSource("center_orders");
                    $receiptTable_source = "stock_transfer_by_kiosk";
                    $StockTransferTable = TableRegistry::get($receiptTable_source,[
                                                                                    'table' => $receiptTable_source,
                                                                                ]);
					if($selectedKiosk>0){
						$kioskOrders_query = $KioskOrderTable->find('all',array('conditions'=>array('status'=>2,'kiosk_id'=>$selectedKiosk),'recursive'=>-1,'fields'=>array('id','kiosk_id')));
					}else{
						$kioskOrders_query = $KioskOrderTable->find('all',array('conditions'=>array('status'=>2),'recursive'=>-1,'fields'=>array('id','kiosk_id')));
					}
					$kioskOrders_query = $kioskOrders_query->hydrate(false);
					if(!empty($kioskOrders_query)){
						   $kioskOrders = $kioskOrders_query->toArray();
					}else{
						   $kioskOrders = array();
					}
				}elseif($this->request->query['cases']==1){
                    
					if($selectedKiosk>0){
                        
                      $kioskOrders_query = $this->KioskOrders->find('all',array(
                                                                          'conditions'=>array(
                                                                                              'status'=>1,
                                                                                              'kiosk_id'=>$selectedKiosk
                                                                                              ),
                                                                          'fields'=>array('id','kiosk_id','kiosk_placed_order_id')
                                                                          )
                                                              );
                     
                   	}else{
						$kioskOrders_query = $this->KioskOrders->find('all',array('conditions'=>array('status'=>1),'recursive'=>-1,'fields'=>array('id','kiosk_id','kiosk_placed_order_id')));
					}
					$kioskOrders_query = $kioskOrders_query->hydrate(false);
                     if(!empty($kioskOrders_query)){
                        $kioskOrders = $kioskOrders_query->toArray();
                      }else{
						   $kioskOrders = array();
					  }
					$warehouseToKioskOrders = $kioskOrders;
				}elseif($this->request->query['cases']==5){
					if($selectedKiosk>0){
						$kioskOrders_query = $this->KioskOrders->find('all',array('conditions'=>array('status IN'=>array(1,2),'KioskOrders.kiosk_id'=>$selectedKiosk),'recursive'=>-1,'fields'=>array('id','kiosk_id','kiosk_placed_order_id')));
					}else{
						$kioskOrders_query = $this->KioskOrders->find('all',array('conditions'=>array('status IN'=>array(1,2),
																									  $dateCondition
																									  ),'recursive'=>-1,'fields'=>array('id','kiosk_id','kiosk_placed_order_id')));
					}
					$kioskOrders_query = $kioskOrders_query->hydrate(false);
					$kioskOrders = $kioskOrders_query->toArray();
                    if(!empty($kioskOrders_query)){
                        $kioskOrders = $kioskOrders_query->toArray();
                      }else{
						   $kioskOrders = array();
					  }
					$warehouseToKioskOrders = $kioskOrders;
				}
				elseif($this->request->query['cases']==6){
					 $receiptTable_source = "center_orders";
                     $KioskOrderTable = TableRegistry::get($receiptTable_source,[
                                                                                    'table' => $receiptTable_source,
                                                                                ]);
                        //$this->KioskOrder->setSource("center_orders");
                    $receiptTable_source = "stock_transfer_by_kiosk";
                    $StockTransferTable = TableRegistry::get($receiptTable_source,[
                                                                                    'table' => $receiptTable_source,
                                                                                ]);
					if($selectedKiosk>0){
						$kioskOrders_query = $KioskOrderTable->find('all',array('conditions'=>array('status IN'=>array(1,2),'kiosk_id'=>$selectedKiosk),'recursive'=>-1,'fields'=>array('id','kiosk_id')));
					}else{
						$kioskOrders_query = $KioskOrderTable->find('all',array('conditions'=>array('status IN'=>array(1,2)),'recursive'=>-1,'fields'=>array('id','kiosk_id')));
					}
					$kioskOrders_query = $kioskOrders_query->hydrate(false);
					if(!empty($kioskOrders_query)){
						   $kioskOrders = $kioskOrders_query->toArray();
					}else{
						   $kioskOrders = array();
					}
					//pr($kioskOrders);die;
				}
			}
		}
		
		$kioskOrder_placedOrderList = array();
        
		if(count($warehouseToKioskOrders)){
           
			foreach($warehouseToKioskOrders as $wtk => $warehouseToKioskOrder){
				if(!empty($warehouseToKioskOrder['kiosk_placed_order_id']) && $warehouseToKioskOrder['kiosk_placed_order_id'] > 0){
					$kioskOrder_placedOrderList[$warehouseToKioskOrder['id']] = $warehouseToKioskOrder['kiosk_placed_order_id'];
				}
			}
		}
		$kiosk_placed_user_id_list = array();
		if(count($kioskOrder_placedOrderList)){
		 if($this->request->query['type'] == "on_demand"){
				  $this->loadModel('on_demand_orders');
				  $kiosk_placed_user_id_list_query = $this->OnDemandOrders->find('list',
											 array(
													  'conditions' => array('id IN'=> $kioskOrder_placedOrderList),
													  'keyField' => 'id',
													  'valueField' => 'user_id',
						//'fields'=>array('KioskPlacedOrders.id','KioskPlacedOrders.user_id'),
						 
						));
		 }else{
			$kiosk_placed_user_id_list_query = $this->KioskPlacedOrders->find('list',
											 array(
													  'conditions' => array('id IN'=> $kioskOrder_placedOrderList),
													  'keyField' => 'id',
													  'valueField' => 'user_id',
						//'fields'=>array('KioskPlacedOrders.id','KioskPlacedOrders.user_id'),
						 
						));	  
		 }
			
			//pr($kiosk_placed_user_id_list_query);
			$kiosk_placed_user_id_list_query = $kiosk_placed_user_id_list_query->hydrate(false);
            if(!empty($kiosk_placed_user_id_list_query)){
                $kiosk_placed_user_id_list = $kiosk_placed_user_id_list_query->toArray();
            }else{
				  $kiosk_placed_user_id_list = array();
			}
		}
		 
		
		//pr($kioskOrders);die;
         $kiosks_query = $this->Kiosks->find('list',
                                      [
                                        'keyField' => 'id',
                                        'valueField' => 'name',
                                        'order' => 'Kiosks.name asc'
                                    ]);
		 $kiosks_query = $kiosks_query->hydrate(false);
         if(!empty($kiosks_query)){
            $kioskName = $kiosks_query->toArray();
         }
		
		$transientW2KorderIds = array();
		$relatedKioskId = array();
		$kioskPlacedOrderIds = array();
		$relatedKioskPlacedOrder = array();
		if(!empty($kioskOrders)){
			foreach($kioskOrders as $key=>$kioskOrderData){
				$relatedKioskId[$kioskOrderData['id']] = $kioskOrderData['kiosk_id'];
				$transientW2KorderIds[] = $kioskOrderData['id'];
				if(!empty($kioskOrderData['kiosk_placed_order_id'])){
					//$kioskPlacedOrderIds[$kioskOrderData['KioskOrder']['kiosk_placed_order_id']] = $kioskOrderData['KioskOrder']['kiosk_placed_order_id'];
					$relatedKioskPlacedOrder[$kioskOrderData['id']] = $kioskOrderData['kiosk_placed_order_id'];
				}
			}
		}
		
      $kioskOrderIdArr = array();
		//pr($relatedKioskPlacedOrder);
		$kioskOrderIdArr = array_keys($relatedKioskPlacedOrder);
		$kioskPlacedOrderIds = array_values($relatedKioskPlacedOrder);
		
		//pr($kioskOrderIdArr);die;
		if(empty($kioskOrderIdArr)){$kioskOrderIdArr = array(0=>'null');}
		$dispatchedStockData_query = $StockTransferTable->find('all',array('conditions'=>array('kiosk_order_id IN'=>$kioskOrderIdArr),'recursive'=>-1,'fields'=>array('kiosk_order_id','product_id','quantity')));
		$dispatchedStockData_query = $dispatchedStockData_query->hydrate(false);
         if(!empty($dispatchedStockData_query)){
            $dispatchedStockData = $dispatchedStockData_query->toArray();
         }else{
			$dispatchedStockData = array();	  
		 }
       
		$dispatchedproductsArr = array();//collecting data of dispatched products of all kiosks
       
		foreach($dispatchedStockData as $key=>$dispatchedproducts){
			$dispatchedproductsArr[$dispatchedproducts['kiosk_order_id']][$dispatchedproducts['product_id']]=$dispatchedproducts['quantity'];
		}
		if(empty($kioskPlacedOrderIds)){$kioskPlacedOrderIds = array(0 => NULL);}
		$kioskOrderedProducts_query = $this->KioskOrderProducts->find('all',array('conditions'=>array('KioskOrderProducts.kiosk_placed_order_id IN'=>$kioskPlacedOrderIds),'fields'=>array('kiosk_placed_order_id','product_id','quantity')));
		$kioskOrderedProducts_query = $kioskOrderedProducts_query->hydrate(false);
		if(!empty($kioskOrderedProducts_query)){
            $kioskOrderedProducts = $kioskOrderedProducts_query->toArray();
        }else{
				  $kioskOrderedProducts = array();
		}
        $kioskOrderedProductDetail = array();//collecting data of requested products of all kiosks
		$flipArr = array_flip($relatedKioskPlacedOrder);
		foreach($kioskOrderedProducts as $key=>$kioskOrderedProduct){
			$newKey = $flipArr[$kioskOrderedProduct['kiosk_placed_order_id']];
			$kioskOrderedProductDetail[$newKey][$kioskOrderedProduct['product_id']] = $kioskOrderedProduct['quantity'];
		}
		
		
         
	/*	 pr($dispatchedproductsArr); /*///compare arr1
		//pr($kioskOrderedProductDetail); //compare arr2
		$dispatchedLessProducts = array();
		$conditnArr = array();
		
		if($type == "received_less"){
			$diff = array();
			$count = 0;
			$lessProductIds = array();   
			if(!empty($kioskOrderIdArr)){
				foreach($kioskOrderIdArr as $k=>$kioskOrderId){
					$count++;
					if(array_key_exists($kioskOrderId,$dispatchedproductsArr)){
						foreach($dispatchedproductsArr[$kioskOrderId] as $compProductId=>$qtt){
							$count++;
							//pr($kioskOrderedProductDetail);
							if(array_key_exists($compProductId,$kioskOrderedProductDetail)){
									
									if(array_key_exists($kioskOrderId,$kioskOrderedProductDetail) && is_array($kioskOrderedProductDetail[$kioskOrderId]) && array_key_exists($compProductId,$kioskOrderedProductDetail[$kioskOrderId])){
											 if($qtt<$kioskOrderedProductDetail[$kioskOrderId][$compProductId]){
													  $lessProductIds[$kioskOrderId."_$count"]=$compProductId;
											 }			 
									}
							}
						}
					} 
				}
				if(!empty($lessProductIds)){
					foreach($lessProductIds as $kioskOrderIdKey=>$corresProduct){
						$kioskOrderIdArr = explode("_",$kioskOrderIdKey);
						$kskOrderId = $kioskOrderIdArr[0];
						$conditnArr['OR'][] = array('kiosk_order_id'=>$kskOrderId,'product_id'=>$corresProduct);
					}
					if(empty($transientW2KorderIds)){
						   $transientW2KorderIds = array(0 => 'NULL');
					}
					 $this->paginate = [
                                    'conditions' => [$conditnArr,$inputProductArr,$dateCondition,'kiosk_order_id IN'=>$transientW2KorderIds],
									'limit' => 100,                                                         
                           ];
				}else{
                     $this->paginate = [
                                    'conditions'=>['kiosk_order_id'=>"",'product_id'=>""],
                                    'limit' => 100,                                          
                                    ];
				 
				}
			}else{
                 $this->paginate = [
									'conditions'=>['kiosk_order_id'=>"",'product_id'=>""],
                                    'limit' => 100,                                          
									];
				 
			}
			
			
			
		}elseif($type == "received_more"){
            
           $diff = array();
			$count = 0;
			$moreProductIds = array();
			if(!empty($kioskOrderIdArr)){
				foreach($kioskOrderIdArr as $k=>$kioskOrderId){
					$count++;
					if(array_key_exists($kioskOrderId,$dispatchedproductsArr)){
						foreach($dispatchedproductsArr[$kioskOrderId] as $compProductId=>$qtt){
							$count++;
							if(array_key_exists($kioskOrderId,$kioskOrderedProductDetail) && is_array($kioskOrderedProductDetail[$kioskOrderId]) && array_key_exists($compProductId,$kioskOrderedProductDetail[$kioskOrderId])){
								if($qtt>$kioskOrderedProductDetail[$kioskOrderId][$compProductId]){
									$moreProductIds[$kioskOrderId."_$count"]=$compProductId;
								}
							}
						}
					}
				}
				if(!empty($moreProductIds)){
					foreach($moreProductIds as $kioskOrderIdKey=>$corresProduct){
						$kioskOrderIdArr = explode("_",$kioskOrderIdKey);
						$kskOrderId = $kioskOrderIdArr[0];
						$conditnArr['OR'][] = array('kiosk_order_id'=>$kskOrderId,'product_id'=>$corresProduct);
						
					}
					if(empty($transientW2KorderIds)){
						   $transientW2KorderIds = array(0 => 'NULL');
					}
                     $this->paginate = [
                                    'conditions' => [$conditnArr,$inputProductArr,$dateCondition,'kiosk_order_id IN'=>$transientW2KorderIds],
                                    'limit' => 100,
                                    'order' => ['kiosk_order_id desc'] 
                                              ];
					 
				}else{
                     $this->paginate = [
                                    'conditions'=>['kiosk_order_id'=>"",'product_id'=>""],
                                    'limit' => 100,
                                    'order' => ['kiosk_order_id desc'],
                                    ];
					 
				}				
			}else{
                 $this->paginate = [
                                    'conditions'=>['kiosk_order_id'=>"",'product_id'=>""],
                                    'limit' => 100,
                                    'order' => ['kiosk_order_id desc'],
                                              ];
			}
		}elseif($type == "out_of_stock"){
		 if(empty($transientW2KorderIds)){
				  $transientW2KorderIds = array(0 => 'NULL');
		 }
		 
             $this->paginate = [
                                               'conditions' => ['quantity'=>0,$inputProductArr,$dateCondition,'kiosk_order_id IN'=>$transientW2KorderIds],
                                                'limit' => 100,
                                                'order' => ['kiosk_order_id desc'],
                                                
                                              ];
			 
		}elseif($type == "on_demand"){
			//$this->StockTransfer->setSource('stock_transfer');
			if(empty($transientW2KorderIds)){
				  $transientW2KorderIds = array(0 => 'NULL');
		 }
		 if($this->request->query['cases'] == 6 || $this->request->query['cases'] == 3 || $this->request->query['cases'] == 4){
			$this->paginate = [
                                                'conditions' => ['kiosk_order_id IN'=>array(0)],
                                                'limit' => 100,
                                                'order' => ['kiosk_order_id desc'],
                                                
                                              ];	  
		 }else{
			$this->paginate = [
                                                'conditions' => ['is_on_demand'=>1,$inputProductArr,$dateCondition,'kiosk_order_id IN'=>$transientW2KorderIds],
                                                'limit' => 100,
                                                'order' => ['kiosk_order_id desc'],
                                                
                                              ];	  
		 }
             
			 
		}else{
		 
		 if(empty($transientW2KorderIds)){
				  $transientW2KorderIds = array(0 => 'NULL');
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
				 $this->paginate = [
                                    'conditions' => [
									'kiosk_order_id IN'=>$transientW2KorderIds,$inputProductArr,$dateCondition,
									'kiosk_id IN'=> $managerKiosk,
									],//
									
                                    'limit' => 100,
                                    'order' => ['kiosk_order_id desc'],
                                                
                                              ]; 
			   }else{
				  $this->paginate = [
                                    'conditions' => [
									'kiosk_order_id IN'=>$transientW2KorderIds,$inputProductArr,$dateCondition],//
                                    'limit' => 100,
                                    'order' => ['kiosk_order_id desc'],
                                                
                                              ];
			   }
		  }else{
				  $this->paginate = [
                                    'conditions' => [
									'kiosk_order_id IN'=>$transientW2KorderIds,$inputProductArr,$dateCondition],//
                                    'limit' => 100,
                                    'order' => ['kiosk_order_id desc'],
                                                
                                              ];
		  }
		 
                 
                   
		}
        
        $stockTransfer = $this->paginate($StockTransferTable);
		
		
		$kiosk_placed_orderid = array();
		$transientW2KproductIds = array();
		foreach($stockTransfer as $key=>$stockTransferDetails){
			$transientW2KproductIds[$stockTransferDetails['product_id']] = $stockTransferDetails['product_id'];
			 //$kiosk_orderid[] = $stockTransferDetails['StockTransfer']['kiosk_order_id'];
		}
        //pr($transientW2KproductIds);
		$users_query = $this->Users->find('list',
                                    [
                                        'keyField' => 'id',
                                        'valueField' => 'username',
                                    ]);
		$users_query = $users_query->hydrate(false);
        if(!empty($users_query)){
             $users = $users_query->toArray();
        }else{
		   $users = array();
        }
		if(empty($transientW2KproductIds)){$transientW2KproductIds=array(0=>'NULL');}
		$productFields_query = $this->Products->find('all',array('conditions'=>array('Products.id IN'=>$transientW2KproductIds),'fields'=>array('id','product_code','product')));
		$productFields_query = $productFields_query->hydrate(false);
		if(!empty($productFields_query)){
            $productFields = $productFields_query->toArray();
        }else{
		   $productFields = array();
		}
        $transientW2KproductTitleArr = array();
		$transientW2KproductCodeArr = array();
         
		foreach($productFields as $key=>$productDetails){
			$transientW2KproductTitleArr[$productDetails['id']] = $productDetails['product'];
			$transientW2KproductCodeArr[$productDetails['id']] = $productDetails['product_code'];
		}
		//pr($transientW2KproductTitleArr);
		 $hint = $this->ScreenHint->hint('kiosk_orders','search_from_orders');
        if(!$hint){
            $hint = "";
        }
		
		$this->set(compact('hint','stockTransfer','transientW2KproductTitleArr','kiosk_placed_orders','kiosk_orderid','transientW2KproductCodeArr','kioskName','relatedKioskId','relatedKioskPlacedOrder','kioskOrderedProductDetail','cases', 'start_date','end_date','kiosk_placed_user_id'));
		$this->set(compact('kioskOrder_placedOrderList','kiosk_placed_user_id_list','users'));
	}
	 
	public function searchGlobally (){
        //pr($this->request);
		$this->searchFromOrders();
		$this->render('search_from_orders');
	}
    public function onDemandPlacedOrders() {
		 $kiosk_query = $this->Kiosks->find('list',
                                      [
                                        'conditions'=>['Kiosks.id NOT IN'=>10000],
                                        'keyField' => 'id',
                                        'valueField' => 'name',
                                        'order' => 'Kiosks.name asc'
                                    ]);
        if(!empty($kiosk_query)){
            $kioskDropdown = $kiosk_query->toArray();    
        }
		$kiosks_query = $this->Kiosks->find('list',
                                      [
                                         
                                        'keyField' => 'id',
                                        'valueField' => 'name',
                                        'order' => 'Kiosks.name asc'
                                    ]);
        if(!empty($kiosk_query)){
            $kiosks = $kiosks_query->toArray();    
        }
		//$kiosks = $this->Kiosk->find('list', array('fields' => array('id', 'name'),
		//											'conditions' => array('Kiosk.kiosk_type' => 1 , 'Kiosk.kiosk_type' => 2),
		//											'order' => 'Kiosk.name asc'
		//										));
		$users_query = $this->Users->find('list',
                                    [
                                        'keyField' => 'id',
                                        'valueField' => 'username',
                                    ]);
        if(!empty($users_query)){
             $users = $users_query->toArray();
        }
        $cronUser_query = $this->Users->find('all',
                                    [
                                        'keyField' => 'id',
                                        'valueField' => 'username',
                                        'conditions'=>['Users.username'=>'cronjob'],
                                    ]);
        	$cronUser_query = $cronUser_query->hydrate(false);
       
        if(!empty($cronUser_query)){
              $cronUser = $cronUser_query->first();
              
        } else{
            $cronUser = array();
        }
        $croneId = $cronUser['id']; 
		$kioskId = '';
        $kiosk_id = $this->request->Session()->read('kiosk_id');
		if($this->request->is(array('get','put'))){
			if(array_key_exists('kiosk',$this->request->query)){
				$kioskId = $this->request->query['kiosk'];
			}
			elseif((int)$kiosk_id){//in case of kiosks
				$kioskId = $kiosk_id;
			 }
		}
		if(!empty($kioskId)){
             $this->paginate = [
                                    'conditions' => ['OnDemandOrders.status' => 0,
														  'OnDemandOrders.kiosk_id' => $kioskId],
                                                'limit' => ROWS_PER_PAGE,
                                               'order' => ['OnDemandOrders.id' => 'desc']
                                                
                                              ];
			 
        }else{
			 $this->paginate = [
                                    'conditions' => ['OnDemandOrders.status' => 0,
														 ],
                                                'limit' => ROWS_PER_PAGE,
                                               'order' => ['OnDemandOrders.id' => 'desc']
                                                
                                              ];
                    
        }
        
          $data = $this->paginate("OnDemandOrders");
            if(!empty($data)){
                $OnDemandOrders = $data->toArray();
            }
	 
		$this->set(compact('hint','kiosks','croneId','users','kioskDropdown'));
		$this->set('OnDemandOrders', $OnDemandOrders);
	}
    public function onDemandOrderTrash($id= null){
        
         
		  $this->OnDemandOrders->updateAll(
						['status' => 9],
						['id'=> $id]
								); 
         $this->Flash->success(__('Order Moved To Trash successfully!'));
		 
		return $this->redirect(array('action' =>'onDemandPlacedOrders'));
	}
	
	public function onDemandTrash() {
        
		//die;
		if(array_key_exists('delete',$this->request->query)){
			$this->OnDemandOrders->deleteAll(array('OnDemandOrders.status' => 9), false);
		}
		if(array_key_exists('checked',$this->request->data)){
			$counter = 0;
            foreach($this->request->data['checked'] as $key => $value){
                $delete_id= $this->OnDemandOrders->get($key);
               // $this->KioskPlacedOrders->delete ($delete_id)
				if($this->OnDemandOrders->delete ($delete_id)){
					$counter++;
				}
			}
			if($counter >0){
                 $this->Flash->success(__('Order Deleted successfully!'));
                
			}
			 
		}
		 $kiosk_query = $this->Kiosks->find('list',
                                      [
                                        'conditions'=>['Kiosks.id NOT IN'=>10000],
                                        'keyField' => 'id',
                                        'valueField' => 'name',
                                        'order' => 'Kiosks.name asc'
                                    ]);
        if(!empty($kiosk_query)){
            $kioskDropdown = $kiosk_query->toArray();    
        }
		$kiosks_query = $this->Kiosks->find('list',
                                      [
                                         
                                        'keyField' => 'id',
                                        'valueField' => 'name',
                                        'order' => 'Kiosks.name asc'
                                    ]);
        if(!empty($kiosk_query)){
            $kiosk = $kiosks_query->toArray();    
        }
		 
		$users_query = $this->Users->find('list',
                                    [
                                        'keyField' => 'id',
                                        'valueField' => 'username',
                                    ]);
        if(!empty($users_query)){
             $users = $users_query->toArray();
        }
        $cronUser_query = $this->Users->find('all',
                                    [
                                        'keyField' => 'id',
                                        'valueField' => 'username',
                                        'conditions'=>['Users.username'=>'cronjob'],
                                    ]);
        $cronUser_query = $cronUser_query->hydrate(false);
        if(!empty($cronUser_query)){
              $cronUser = $cronUser_query->first();
              
        } else{
            $cronUser = array();
        }
        $croneId = $cronUser['id']; 
		$kioskId = '';
        $kiosk_id = $this->request->Session()->read('kiosk_id');
		if($this->request->is(array('get','put'))){
			if(array_key_exists('kiosk',$this->request->query)){
				$kioskId = $this->request->query['kiosk'];
			}elseif((int)$kiosk_id){//in case of kiosks
				   $kioskId = $kiosk_id;
			}
		}
		if(!empty($kioskId)){
		      $this->paginate = [
                                    'conditions' => ['OnDemandOrders.status' => 9,
																	  'OnDemandOrders.kiosk_id' => $kioskId],
                                                'limit' => ROWS_PER_PAGE,
                                               'order' => ['OnDemandOrders.id' => 'desc']
                                                
                                              ];
                    
			 
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
									 
									$this->paginate = [
                                    'conditions' => ['OnDemandOrders.status' => 9,
													 ' OnDemandOrders.kiosk_id IN' => $managerKiosk
													],
                                                'limit' => ROWS_PER_PAGE,
                                               'order' => ['OnDemandOrders.id' => 'desc']
                                                
                                              ];
						   }else{
									$this->paginate = [
                                    'conditions' => ['OnDemandOrders.status' => 9,
													],
                                                'limit' => ROWS_PER_PAGE,
                                               'order' => ['OnDemandOrders.id' => 'desc']
                                                
                                              ];
									 						
						   }
				  }else{
						   $this->paginate = [
                                    'conditions' => ['OnDemandOrders.status' => 9,
													],
                                                'limit' => ROWS_PER_PAGE,
                                               'order' => ['OnDemandOrders.id' => 'desc']
                                                
                                              ];
						   		 						
				  } 
//             $this->paginate = [
//                                    'conditions' => ['OnDemandOrders.status' => 9,
//													],
//                                                'limit' => ROWS_PER_PAGE,
//                                               'order' => ['OnDemandOrders.id' => 'desc']
//                                                
//                                              ];
				 
        }
       
		 $data = $this->paginate("OnDemandOrders");
        if(!empty($data)){
            $kioskPlacedOrders = $data->toArray();
        }
		 
		$this->set(compact('kiosk','croneId','users','kioskDropdown'));
		$this->set('kioskPlacedOrders', $kioskPlacedOrders);
	}
	
	public function onDemandDeleteOrder($id = null) {
		  $get_id = $this->OnDemandOrders->get($id);
        if($this->OnDemandOrders->delete($get_id)){
            $this->Flash->success("The kiosk order has been deleted.");
			 
		} else {
             $this->Flash->success("The kiosk order could not be deleted. Please, try again.");
			 
		}
		return $this->redirect(array('action' => 'onDemandOrderTrash'));
		 
	}
	
	
	public function onDemandRestore($id =null){
       
		 $this->OnDemandOrders->updateAll(
						['status' =>0],
						['id'=> $id]
								); 
		return $this->redirect(array('action' => 'onDemandPlacedOrders')); 
		 
	}
    
    	public function receiveOrder($order_id = null) {		
		//1. 	Only kiosk user can receive order - Permission added
		//2.	Only Transient Orders should be received - checked
		//3.	Transient Order should belong to particular Kiosk - checked
		//4.	After receiving order, stock should be updated of Kiosk - pending
		//5.	User should be redirected back to confirmed order page - pending
		//6.	Ensure we have respective tables for stock update - done
		//7.	Create log for user who received stock - pending
		//8.	Update Kiosk Stock
		//9.	Change status of order from transient to confirmed
		//ALTER TABLE `kiosk_orders` ADD `received_by` INT( 11 ) UNSIGNED NOT NULL COMMENT 'user_id of logged in users' AFTER `received_on` ;
		
		$user_id = $this->request->session()->read('Auth.User.id'); 	
		$kiosk_id = $this->request->session()->read('kiosk_id'); //for server
		//$kiosk_id = 5;// for localhost
		$kioskProdctTable = "kiosk_{$kiosk_id}_products";
		$kioskOrderStatusDetail_query = $this->KioskOrders->find('all',[
                                                                        'conditions'=>['KioskOrders.id'=>$order_id],
                                                                        'fields'=>array('id','status'),
                                                                       ]);
       $kioskOrderStatusDetail_query = $kioskOrderStatusDetail_query->first();
        
        if(!empty($kioskOrderStatusDetail_query)){
            $kioskOrderStatusDetail = $kioskOrderStatusDetail_query->toArray();
        }else{
            $kioskOrderStatusDetail = array();
        }
        
		$kioskOrderStatus = $kioskOrderStatusDetail['status'];
		if(
			$this->table_exists($kioskProdctTable,$kiosk_id) &&
			$this->KioskOrders->is_transient_order($order_id) &&
			$this->KioskOrders->belongs_to_kiosk($order_id,$kiosk_id) &&
			$kioskOrderStatus == 1){
			
			
			$datetime = date('Y-m-d H:i:s');
			$products_query = $this->StockTransfer->find('all', array(
								'conditions' => array('kiosk_order_id' => $order_id)
								));
			$products_query = $products_query->hydrate(false);
			if(!empty($products_query)){
				  $products = $products_query->toArray();
			}else{
				  $products = array();
			}
			foreach($products as $product){
				$product_id = $product['product_id'];
				$quantity = $product['quantity'];
				$sale_price = $product['sale_price'];
				$updateQuery = "UPDATE `$kioskProdctTable` SET
							    `quantity` = `quantity` + $quantity,
							    `selling_price` = $sale_price
							    WHERE `$kioskProdctTable`.`id` = $product_id";
				//echo "\n updateQuery => $updateQuery";
				$conn = ConnectionManager::get('default');
		        $stmt = $conn->execute($updateQuery); 
			}
			
			$KioskOrders_Entity = $this->KioskOrders->get($order_id);
			$data = array('received_on' => $datetime, 'received_by' => $user_id,'status' => 2);
			$KioskOrders_Entity = $this->KioskOrders->patchEntity($KioskOrders_Entity,$data,['validate' => false]);
			$this->KioskOrders->save($KioskOrders_Entity);
			$flashMessage = "Order received successfully.";
			$this->Flash->success($flashMessage);
			//$redirectURL = Router::url( array('controller'=> 'kiosk_orders','action' => 'confirmed_orders'), true );
			//header("Location:$redirectURL");
			return $this->redirect(array('action' => 'confirmed_orders'));
		//user header if not re
		}else{
			$flashMessage =  "Failed to process order";
			$this->Flash->error(__("$flashMessage"));
			//$redirectURL = Router::url( array('controller'=> 'kiosk_orders','action' => 'transient_ordres'), true );
			//header("Location:$redirectURL");
			return $this->redirect(array('action' => 'transient_orders'));
		}
	}
    private function table_exists($kioskProdctTable,$kiosk_id){
		$tableQuery = "SHOW TABLES LIKE '$kioskProdctTable'";
		//$data = $this->KioskOrder->query($tableQuery);
		
		$conn = ConnectionManager::get('default');
        $stmt = $conn->execute($tableQuery); 
        $data = $stmt ->fetchAll('assoc');
		
		if(!count($data)){
			return $stockInitializer->syncProducts($kiosk_id);			
		}else{
			return count($data) ? true :false;
		}
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
	
	
	public function createDispute($id = null){
		$disputeOptions = Configure::read('dispute');
		if ($this->request->is(array('post', 'put'))) {
			//pr($this->request->data['StockTransfer']);die;
			$recordSaved = 0;
			$error = array();
			$remarksError = array();
			$errorProductId = array();
			$stockTransfer = $this->request->data['StockTransfer'];
			//pr($stockTransfer);die;
			foreach($stockTransfer['quantity'] as $qtt => $quantity){
				if(!empty($quantity)){
					$product_id = $stockTransfer['product_id'][$qtt];
					$kiosk_user_remarks = $stockTransfer['kiosk_user_remarks'][$qtt];
					if(empty($kiosk_user_remarks)){
						$errorProductId[] = $product_id;
					}
				}
			}
			
			if(!empty($errorProductId)){
				$productName_query = $this->Products->find('list',array('conditions'=>array('Product.id IN'=>$errorProductId),'fields'=>array('id','product')));
				$productName_query = $productName_query->hydrate(false);
				if(!empty($productName_query)){
				  $productName = $productName_query->toArray();
				}else{
				  $productName = array();
				}
				foreach($productName as $productId=>$productNme){
					$remarksError[] = "Please input user remarks for $productNme";
				}
			}
			
			$remarksErrStr = "";
			if(count($remarksError)>0){
				$remarksErrStr = implode("<br/>",$remarksError);
				$this->Flash->error($remarksErrStr,array('escape' => false));
				return $this->redirect(array('action'=>'create_dispute',$id));
				die;
			}else{
				foreach($stockTransfer['quantity'] as $qt => $quantity){
					$this->OrderDisputes->clear();
					if(empty($quantity))continue;
					$receiving_status = $stockTransfer['receiving_status'][$qt];				
					$kiosk_id = $stockTransfer['kiosk_id'][$qt];
					$kiosk_order_id = $stockTransfer['kiosk_order_id'][$qt];
					$kiosk_user_remarks = $stockTransfer['kiosk_user_remarks'][$qt];
					$product_id = $stockTransfer['product_id'][$qt];
					$actualQuantity = $stockTransfer['actual_quantity'][$qt];
					$disputed_by = $this->request->Session()->read('Auth.User.id');
					
					
					
					$orderDisputeData = array(
									'kiosk_id' => $kiosk_id,
									'kiosk_order_id' => $kiosk_order_id,
									'receiving_status' => $receiving_status,
									'disputed_by' => $disputed_by,
									'quantity' => $quantity,
									'kiosk_user_remarks' => $kiosk_user_remarks,				 
									'approval_status' => '0',
									'product_id'=> $product_id,
									//'user_id' => $this->request->session()->read('Auth.User.id')
								);
					
					//prr($orderDisputeData);die;
					
					//$this->OrderDispute->set($orderDisputeData);
					//if ($this->OrderDispute->validates()) {
					//	;
					//}else{
					//	$errors = $this->OrderDispute->validationErrors;
					//}
					if( $quantity > $actualQuantity && $receiving_status == -1 ){
						//case: if received less,
						//than difference can not be greater than quatities transferred from central repository					
						$error[] = "<br/>Quantity for Product ID: $product_id can not be greater than quantities transferred from central repository";					
						continue;
					}else{
						//echo "Good to Save";
					}
					$isSaved = false;
					try{
						$OrderDisputesEntity = $this->OrderDisputes->newEntity($orderDisputeData);
						$OrderDisputesEntity = $this->OrderDisputes->patchEntity($OrderDisputesEntity,$orderDisputeData);
						if($this->OrderDisputes->save($OrderDisputesEntity)){
							$recordSaved++;
							$isSaved = true;
						}
						
					}catch(Exception $e){
						echo $e;die;
						$error[] = "<br/>Already raised order dispute for product with id: $product_id";
					}
				}
			}
			
			
			
			$errorStr = "";
			if(count($error)){
				$errorStr = implode("<br/>", $error);
			}
			if($recordSaved){
				$this->Flash->error("$recordSaved records disputed.{$errorStr}",array('escape' => false));
			}else{
				$this->Flash->error("Failed to save records!{$errorStr}",array('escape' => false));
			}
			
			$kiosks_query = $this->Kiosks->find('list', array(
														'valueField' => 'code',
														'conditions' => array('Kiosks.status' => 1)));
			$kiosks_query = $kiosks_query->hydrate(false);
			if(!empty($kiosks_query)){
				  $kiosks = $kiosks_query->toArray();
			}else{
				  $kiosks = array();
			}
			$users_query = $this->User->find('list', array(
													 'keyField' => 'id',
													 'valueField' => 'username',
													 ));
			$users_query = $users_query->hydrate(false);
			if(!empty($users_query)){
				  $users = $users_query->toArray();
			}else{
				  $users = array();
			}
			$options = array('conditions' => array('StockTransfer.kiosk_order_id' => $id));
			$products_query = $this->StockTransfer->find('all', $options);
			$products_query = $products_query->hydrate(false);
			if(!empty($products_query)){
				  $products = $products_query->toArray();
			}else{
				$products = array();  
			}
			
			$this->set(compact('users','disputeOptions','kiosks'));
			$this->set('products', $products);
		}
			//$this->OrderDispute->find('all',array('conditions'=>array('OrderDispute.')));
			$stockTransferDetails_query = $this->StockTransfer->find('all',array('conditions'=>array('StockTransfer.kiosk_order_id'=>$id),'recursive'=>-1));
			
			$stockTransferDetails_query = $stockTransferDetails_query->hydrate(false);
			if(!empty($stockTransferDetails_query)){
				  $stockTransferDetails = $stockTransferDetails_query->toArray();
			}else{
				  $stockTransferDetails = array();	
			}
			
			$transferredProductIds = array();
			if(!empty($stockTransferDetails)){
				foreach($stockTransferDetails as $key=>$stockTransferDetail){
					$transferredProductIds[] = $stockTransferDetail['product_id'];
				}
			}
			
			if(!empty($transferredProductIds)){
				$alreadyDisputedProducts_query = $this->OrderDisputes->find('all',array('conditions'=>array('OrderDisputes.kiosk_order_id'=>$id,'OrderDisputes.product_id IN'=>$transferredProductIds)));
				$alreadyDisputedProducts_query  = $alreadyDisputedProducts_query->hydrate(false);
				if(!empty($alreadyDisputedProducts_query)){
						   $alreadyDisputedProducts = $alreadyDisputedProducts_query->toArray();
				}else{
						   $alreadyDisputedProducts  = array();
				}
			}
			if(!empty($transferredProductIds)){
				$Productscode_query = $this->Products->find('list',array(
																	'conditions'=>array('Product.id IN'=>$transferredProductIds),
																	'keyField' => 'id',
																	'valueField' => 'product_code'
																	));
				$Productscode_query = $Productscode_query->hydrate(false);
				if(!empty($Productscode_query)){
						   $Productscode = $Productscode_query->toArray();
				}else{
						   $Productscode = array();
				}
				
			}
			$disputedProductIds = array();
			if(!empty($alreadyDisputedProducts)){
				foreach($alreadyDisputedProducts as $key=>$alreadyDisputedProduct){
					$disputedProductIds[] = $alreadyDisputedProduct['product_id'];
				}
			}
			
			$kiosks_query = $this->Kiosks->find('list', array(
														'valueField' => 'code',
														'conditions' => array('Kiosks.status' => 1)));
			$kiosks_query = $kiosks_query->hydrate(false);
			if(!empty($kiosks_query)){
				  $kiosks = $kiosks_query->toArray();
			}else{
				  $kiosks = array();
			}
			$users_query = $this->User->find('list', array(
													 'keyField' => 'id',
													 'valueField' => 'username',
													 ));
			$users_query = $users_query->hydrate(false);
			if(!empty($users_query)){
				  $users = $users_query->toArray();
			}else{
				  $users = array();
			}
			$options = array('conditions' => array('StockTransfer.kiosk_order_id' => $id));
			$products_query = $this->StockTransfer->find('all', $options);
			$products_query = $products_query->hydrate(false);
			if(!empty($products_query)){
				  $products = $products_query->toArray();
			}else{
				$products = array();  
			}
			
			$this->set(compact('users','disputeOptions','kiosks','disputedProductIds','Productscode'));
			$this->set('products', $products);
	}
    
    public function cancelProduct(){
       $this->paginate = [
                             'order' => ['KioskCancelledOrderProducts.id desc'],
                            'limit' =>100 
                          ];
		// pr($this->paginate);//die;
          $result_query = $this->paginate($this->KioskCancelledOrderProducts);
		
        if(!empty($result_query)){
            $result = $result_query->toArray();
        }else{
            $result = array();
        }
       // pr($result);die;
		 
		$product_code_query = $this->Products->find('list',[
                                                     'keyField' => 'id',
													 'valueField' => 'product_code',
			
                                        ]);
        								 
		$product_code_query = $product_code_query->hydrate(false);
        if(!empty($product_code_query)){
            $product_code = $product_code_query->toArray();
        }else{
            $product_code = array();
        }
       $product_title_query = $this->Products->find('list',[
                                                     'keyField' => 'id',
													 'valueField' => 'product',
			
                                        ]);
        								 
		$product_title_query = $product_title_query->hydrate(false);
        if(!empty($product_title_query)){
            $product_title = $product_title_query->toArray();
        }else{
            $product_title = array();
        }                                         
		 $kiosks_query = $this->Kiosks->find('list',[
                                                     'keyField' => 'id',
													 'valueField' => 'name',
			
                                        ]);
        								 
		$kiosks_query = $kiosks_query->hydrate(false);
        if(!empty($kiosks_query)){
            $kiosks = $kiosks_query->toArray();
        }else{
            $kiosks = array();
        }          
		 
		$this->set(compact('result','product_code','product_title','kiosks'));
	}
    public function searchCancel(){
       // pr($this->request);die;
			$search_kw = $start_date = $end_date ="";
			$conditionArray = array();
			if(array_key_exists('start_date',$this->request->query)){
				$start_date = $this->request->query['start_date'];
				$this->set(compact('start_date'));
			}
			if(array_key_exists('end_date',$this->request->query)){
				$end_date = $this->request->query['end_date'];
				$this->set(compact('end_date'));
			}
			if(!empty($start_date) && !empty($end_date)){
				$conditionArray[] = array(
							"KioskCancelledOrderProducts.created >" => date('Y-m-d', strtotime($start_date)),
							"KioskCancelledOrderProducts.created <" => date('Y-m-d', strtotime($end_date. ' +1 Days')),			
						       );
				}
			if(array_key_exists('search_kw',$this->request->query)){
				$search_kw = $this->request->query['search_kw'];
				if(!empty($search_kw)){
					$conditionArray1 = array(
								'OR' => array(
									      ('Products.product_code like') => "%$search_kw%",
									      ('Products.product like') => "%$search_kw%"
									)
								);
                    $inputProduct_query = $this->Products->find('list',[
                                                     'keyField' => 'id',
													 'valueField' => 'id',
                                                     'conditions'=>$conditionArray1,
			
                                        ]);
        								 
                    $inputProduct_query = $inputProduct_query->hydrate(false);
                    if(!empty($inputProduct_query)){
                        $inputProduct = $inputProduct_query->toArray();
                    }else{
                        $inputProduct = array();
                    }
                   // pr($inputProduct);die;
					//$inputProduct = $this->Product->find('list',array('conditions'=>$conditionArray1,'fields'=>array('id')));
					$conditionArray[] = array('KioskCancelledOrderProducts.product_id IN' => $inputProduct);
				}
			}
			$kiosk_id = "";
			if(array_key_exists('cancel',$this->request->query)){
				if(array_key_exists('kiosk_id',$this->request->query['cancel'])){
					$kiosk_id = $this->request->query['cancel']['kiosk_id'];
					if(!empty($kiosk_id)){
						$conditionArray[] = array('KioskCancelledOrderProducts.kiosk_id' => $kiosk_id);
					}
				}
			}
             $this->paginate = [
                            'conditions'=>$conditionArray,
                             'order' => ['KioskCancelledOrderProducts.id desc'],
                            'limit' =>100 
                          ];
		// pr($this->paginate);//die;
          $result_query = $this->paginate($this->KioskCancelledOrderProducts);
		
        if(!empty($result_query)){
            $result = $result_query->toArray();
        }else{
            $result = array();
        }
		 
		$product_code_query = $this->Products->find('list',[
                                                     'keyField' => 'id',
													 'valueField' => 'product_code',
			
                                        ]);
        								 
		$product_code_query = $product_code_query->hydrate(false);
        if(!empty($product_code_query)){
            $product_code = $product_code_query->toArray();
        }else{
            $product_code = array();
        }
       $product_title_query = $this->Products->find('list',[
                                                     'keyField' => 'id',
													 'valueField' => 'product',
			
                                        ]);
        								 
		$product_title_query = $product_title_query->hydrate(false);
        if(!empty($product_title_query)){
            $product_title = $product_title_query->toArray();
        }else{
            $product_title = array();
        }                                         
		 $kiosks_query = $this->Kiosks->find('list',[
                                                     'keyField' => 'id',
													 'valueField' => 'name',
			
                                        ]);
        								 
		$kiosks_query = $kiosks_query->hydrate(false);
        if(!empty($kiosks_query)){
            $kiosks = $kiosks_query->toArray();
        }else{
            $kiosks = array();
        }          
		$this->set(compact('result','product_code','product_title','kiosks','kiosk_id'.'search_kw'));
		$this->render('cancel_product');
	}
    public  function initiateOrderPlacement(){
		 $ignore_merge = true;
		/*finding sold products for today from kiosk product sales
		created an array of product and quantity using above
		finding title, code, quantity, image for sold product from products table
		finding dispatched and undispatched orders for today from kiosk_placed_orders table
		capturing dispatched order ids in an array
		using dispatched order ids, getting all product ids with their quantities
		now subtracting the dispatched product quantities from the sold products and showing the difference in the frontend
		getting the product id, quantity and remarks for the non dispatched orders from kiosk_order_products table (if exists)
		using the same kiosk_placed_order id if already placed else generating a new order
		finally adding/updating the products, kiosk_place_order_id, quantity to kiosk_order_products_table
		 */
		//echo date('Y-m-d');die;
		$kiosk_id = $this->request->Session()->read('kiosk_id');
		$user_id = $this->request->session()->read('Auth.User.id')	;//rasa
		
		$kiosk_placed_order_id = 0;
		$kioskProdctTable_source = "kiosk_{$kiosk_id}_products";
		$kioskProductSaleTable_source = "kiosk_{$kiosk_id}_product_sales";
        $kioskProdctTable = TableRegistry::get($kioskProdctTable_source,[
                                                                    'table' => $kioskProdctTable_source,
                                                                ]);
        $kioskProductSaleTable = TableRegistry::get($kioskProductSaleTable_source,[
                                                                    'table' => $kioskProductSaleTable_source,
                                                                ]);
		//$this->Product->setSource($kioskProdctTable);
		$productIds = array();
		$product_quantities = array();
		
		//finding mobile phones sold today
         $soldPhones_query = $this->MobileBlkReSales->find('all',array(
                                                                        'fields' => array('brand_id','mobile_model_id'),
                                                                       'conditions' => array(
                                                                                        'DATE(MobileBlkReSales.created)' => date('Y-m-d'),
                                                                                        'MobileBlkReSales.kiosk_id' => $kiosk_id,
                                                                                        //'MobileBlkReSale.refund_status <>' => 1,
                                                                                    ),
                                                                        'group' => array('brand_id','mobile_model_id'),
                                                            ));
                  $soldPhones_query
                          //->select(['count' => $soldPhones_query->func()->count('*')])
                          //  $soldProducts_query
                          ->select(['total' => $soldPhones_query->func()->count('id')]);
                           
                $soldPhones_query = $soldPhones_query->hydrate(false);
                if(!empty($soldPhones_query)){
                        $soldPhones = $soldPhones_query->toArray();
                }else{
                    $soldPhones = array();
                }
                 $soldPhones_query1 = $soldPhones_query->count();
                 //pr($soldPhones);die;
		 
         $brands_query = $this->Brands->find('list', [
                                                        'keyField' => 'id',
                                                        'valueField' => 'brand'
                                                    ]
                                                  
                                            );
        $brands_query = $brands_query->hydrate(false);
        if(!empty($brands_query)){
                $brand = $brands_query->toArray();
        }else{
            $brand = array();
        }
		$models_query = $this->MobileModels->find('list', [
                                                        'keyField' => 'id',
                                                        'valueField' => 'model'
                                                    ]
                                                  
                                            );
        $models_query = $models_query->hydrate(false);
        if(!empty($models_query)){
                $models = $models_query->toArray();
        }else{
            $models = array();
        }
		 
		$this->set(compact('soldPhones','brand','models'));
		//finding proudcts sole by kiosk user for today
         $soldProducts_query = $kioskProductSaleTable->find('all',array(
                                                                    'fields' => array('product_id' ),
                                                                      'conditions' => array(
                                                                                            'DATE(created)' => date('Y-m-d'),
                                                                                            'refund_status' => 0
                                                                                ),
                                                                                'group' => array('product_id')
                                                            ));
        $soldProducts_query
                ->select(['total_quantity' => $soldProducts_query->func()->sum('quantity')]);
        $soldProducts_query = $soldProducts_query->hydrate(false);
        if(!empty($soldProducts_query)){
                $soldProducts = $soldProducts_query->toArray();
        }else{
            $soldProducts = array();
        }
        foreach($soldProducts as $key => $soldProduct){
			$productID = $soldProduct['product_id'];
			$productIds[] = $productID;
			$product_quantity = $soldProduct['total_quantity'];
			$product_quantities[$productID] = $product_quantity;
		}
		if(empty($productIds)){
            $productIds = array('0'=>null);
        }
        
		//getting the details of parts repaired today
		
		$partsRepaired_query = $this->MobileRepairParts->find('all',array(
					'conditions' => array(
						 'DATE(MobileRepairParts.created)' => date('Y-m-d'),
						'MobileRepairParts.kiosk_id' => $kiosk_id
					),
					'recursive' => -1
				)
			);
		$partsRepaired_query = $partsRepaired_query->hydrate(false);
        if(!empty($partsRepaired_query)){
                $partsRepaired = $partsRepaired_query->toArray();
        }else{
            $partsRepaired = array();
        }
        //finding disputed orders for today
        
		$disputedProducts_query = $this->OrderDisputes->find('all',array(
				'fields' => array('product_id'),
				'conditions' => array(
					 'DATE(OrderDisputes.admin_acted)' => date('Y-m-d'),
					'OrderDisputes.kiosk_id' => $kiosk_id,
					'OrderDisputes.receiving_status' => -1,
					'OrderDisputes.approval_status' => 1
				),
				'group' => array('OrderDisputes.product_id') 
				 
			)
		);
        $disputedProducts_query
                          ->select(['disputed_quantity' => $disputedProducts_query->func()->sum('OrderDisputes.quantity')]);
          $disputedProducts_query = $disputedProducts_query->hydrate(false);
        if(!empty($disputedProducts_query)){
                $disputedProducts = $disputedProducts_query->toArray();
        }else{
            $disputedProducts = array();
        }
        $reciptIds_query = $kioskProductSaleTable->find('all',array(
				'fields' => array('product_receipt_id'),
				'conditions' => array(
					 'DATE(created)' => date('Y-m-d'),
					'refund_status' => 0,
					//'KioskProductSale.kiosk_id' => $kiosk_id
				),
				
			)
		);
		$reciptIds_query = $reciptIds_query->hydrate(false);
        if(!empty($reciptIds_query)){
                $reciptIds = $reciptIds_query->toArray();
        }else{
            $reciptIds = array();
        }
       $rscIDS = array();
		foreach($reciptIds as $key1 => $value1){
			$rscIDS[$value1["product_receipt_id"]] = $value1["product_receipt_id"];
		}
        if(empty($rscIDS)){
            $rscIDS = array('0'=>null);
        }

		//finding faulty products for today
		$faultyProducts_query = $this->FaultyProducts->find('all',array(
				'fields' => array('product_id','receipt_id'),
				'conditions' => array(
					 'DATE(FaultyProducts.created)' => date('Y-m-d'),
					'FaultyProducts.kiosk_id' => $kiosk_id,
					'FaultyProducts.receipt_id IN'  => $rscIDS,
				),
				'group' => array('FaultyProducts.product_id') 
				 
			)
		);
         $faultyProducts_query
                          ->select(['faulty_quantity' => $disputedProducts_query->func()->sum('FaultyProducts.quantity')]);
		
		$faultyProducts_query = $faultyProducts_query->hydrate(false);
        if(!empty($faultyProducts_query)){
                $faultyProducts = $faultyProducts_query->toArray();
        }else{
            $faultyProducts = array();
        }
        if(count($disputedProducts)){
			foreach($disputedProducts as $dp => $disputedProduct){
				if(array_key_exists($disputedProduct['product_id'],$product_quantities)){
					$product_quantities[$disputedProduct['product_id']]+=$disputedProduct['disputed_quantity'];
				}else{
					$productIds[] = $disputedProduct['product_id'];
					//for getting the title, code etc of product
					$product_quantities[$disputedProduct['product_id']] = $disputedProduct['disputed_quantity'];
				}
			}
		}
		
		if(count($faultyProducts)){
			foreach($faultyProducts as $fp => $faultyProduct){
				if(array_key_exists($faultyProduct['product_id'],$product_quantities)){
					$product_quantities[$faultyProduct['product_id']]+=$faultyProduct['faulty_quantity'];
				}else{
					$productIds[] = $faultyProduct['product_id'];
					//for getting the title, code etc of product
					$product_quantities[$faultyProduct['product_id']] = $faultyProduct['faulty_quantity'];
				}
			}
		}
     
		if(empty($productIds)){
            $productIds = array('0' =>null);
        }
      
		//adding the repaired parts to the main array to place the order along with
		foreach($partsRepaired as $k => $parts){
			$partsProductId = $parts['product_id'];
			$partsQuantity = '1'; //by default 1 quantity is going while repairing
			if(array_key_exists($partsProductId,$product_quantities)){
				$product_quantities[$partsProductId]+=$partsQuantity;
			}else{
				$productIds[] = $partsProductId;//for getting the title, code etc of product
				$product_quantities[$partsProductId] = $partsQuantity;
			}
		}
		
		$returnedReceiptIds_query = $kioskProductSaleTable->find('list',[
                                                        'keyField' => 'id',
                                                        'valueField' => 'product_receipt_id',
                                                        'conditions' => [
                                                           'DATE(modified)' => date('Y-m-d'),
                                                                'DATE(created) != DATE(modified)',
                                                                'refund_status' => 0 
                                                        ]
						                              ] 
		);
		 $returnedReceiptIds_query = $returnedReceiptIds_query->hydrate(false);
        if(!empty($returnedReceiptIds_query)){
                $returnedReceiptIds = $returnedReceiptIds_query->toArray();
        }else{
            $returnedReceiptIds = array();
        }
        //finding the returned product_receipt ids that were not sold today added on 07.03.2016
		$returned_products = array();
		if(count($returnedReceiptIds)){
			$returned_products_query = $kioskProductSaleTable->find('all',array(
					'fields' => array('product_id'),
					'conditions' => array(
						'product_receipt_id IN' => $returnedReceiptIds,
						'refund_status' => 1,
						'order_refund_value' => 0,
					),
					'group' => array('product_id'),
					'recursive' => -1
				)
                                                                     
			);
             $returned_products_query
                          ->select(['total_returned_quantity' => $returned_products_query->func()->sum('quantity')]);
             $returned_products_query = $returned_products_query->hydrate(false);
            if(!empty($returned_products_query)){
                    $returned_products = $returned_products_query->toArray();
            }else{
                $returned_products = array();
            }
		}
     
		$returnedProductsArr = array();
		$ids=array();
		if(count($returned_products)){
			foreach($returned_products as $rp => $returned_product){
				$returnedProductsArr[$returned_product['product_id']] = $returned_product;
				$ids[] = $returned_product['product_id'];
				//if(array_key_exists($returned_product['KioskProductSale']['product_id'],$product_quantities) && $product_quantities[$returned_product['KioskProductSale']['product_id']] > 0){
				//	$finalQuantity = $product_quantities[$returned_product['KioskProductSale']['product_id']] - $returned_product[0]['total_returned_quantity'];
				//	
				//	if($finalQuantity <= 0){
				//		unset($product_quantities[$returned_product['KioskProductSale']['product_id']]);
				//	}else{
				//		$product_quantities[$returned_product['KioskProductSale']['product_id']] = $finalQuantity;
				//	}
				//}
			}
		}
		if(empty($ids)){
            $ids = array('0'=>null);
        }
		
		//07.03.2016 till here****
		//*/
		$origQtys = $product_quantities;
		///rajju.........
		$kioskPlacedOrder = array();
		if($ignore_merge){
				  $kioskPlacedOrders_query = $this->KioskPlacedOrders->find('all', array(
											 'fields' => array('id'),
											 'conditions' => array(
												  'DATE(KioskPlacedOrders.created)' => date('Y-m-d'),
												 'KioskPlacedOrders.kiosk_id' => $kiosk_id,
												 'KioskPlacedOrders.status' => 0, //unconfirmed
												 //'KioskPlacedOrders.merged' => 0, //unconfirmed
												 'KioskPlacedOrders.lock_status' => 0 //unconfirmed
											 )
										 )); 
		}else{
				  $kioskPlacedOrders_query = $this->KioskPlacedOrders->find('all', array(
													  'fields' => array('id'),
													  'conditions' => array(
														   'DATE(KioskPlacedOrders.created)' => date('Y-m-d'),
														  'KioskPlacedOrders.kiosk_id' => $kiosk_id,
														  'KioskPlacedOrders.status' => 0, //unconfirmed
														  'KioskPlacedOrders.merged' => 0, //unconfirmed
														  'KioskPlacedOrders.lock_status' => 0 //unconfirmed
													  )
												  ));
		}
		
		
        $kioskPlacedOrders_query = $kioskPlacedOrders_query->hydrate(false);
        if(!empty($kioskPlacedOrders_query)){
                $kioskPlacedOrders = $kioskPlacedOrders_query->toArray();
        }else{
            $kioskPlacedOrders = array();
        }
        $kioskPlacedOrdersids = array();
		foreach($kioskPlacedOrders as $key => $kioskPlcdOrder){
			   $kioskPlacedOrdersids[] =  $kioskPlcdOrder['id'];
			}
            if(empty($kioskPlacedOrdersids)){
                $kioskPlacedOrdersids = array('0' =>null);
            }
		$placed_product_quantities_query = $this->KioskOrderProducts->find('all',array(
						 'fields' => array('product_id','quantity','difference'),
						'recursive' => -1,
							'conditions' => array(
								 'DATE(KioskOrderProducts.created)' => date('Y-m-d'),
								'KioskOrderProducts.product_id IN' => $productIds,
								'KioskOrderProducts.kiosk_id' => $kiosk_id,
								'kiosk_placed_order_id IN' => $kioskPlacedOrdersids,
								 
								)
							)
						);
        $placed_product_quantities_query = $placed_product_quantities_query->hydrate(false);
        if(!empty($placed_product_quantities_query)){
                $placed_product_quantities = $placed_product_quantities_query->toArray();
        }else{
            $placed_product_quantities = array();
        } 
		$productplacedquant = array();
		$placedproduct_quantities = array();
		foreach($placed_product_quantities as $key => $placedProduct){
			$product_ID = $placedProduct['product_id'];
			$product_Ids[] = $product_ID;
			//$product_quant = $placedProduct['KioskOrderProduct']['quantity'];
			//$placedproduct_quantities[$product_ID] = $product_quant;
			$diffrnce = $placedProduct['difference'];
			$placedproduct_quantities[$product_ID] = $diffrnce;
		}
		///rajju.........
		//finding product title, product_code and quanties, images for product sold 
		$products_query = $kioskProdctTable->find('all',array(
					'fields' => array('id','product','product_code','image','quantity'),
					'conditions' => array('id IN' => $productIds),
					'recursive' => -1
					)
		);
		 $products_query = $products_query->hydrate(false);
            if(!empty($products_query)){
                    $products = $products_query->toArray();
            }else{
                $products = array();
            }
        //here we will ignore also if order processed or not so we commented 'KioskPlacedOrder.status' => 0
		//Note: for same day we should not have 2 orders with 0 status(unconfirmed/dispatched). This is required
		if($ignore_merge){
				  $kioskPlacedOrder_query = $this->KioskPlacedOrders->find('all', array(
											 'fields' => array('id'),
											 'recursive' => -1,
											 'conditions' => array(
												  'DATE(KioskPlacedOrders.created)' => date('Y-m-d'),
												 'KioskPlacedOrders.kiosk_id' => $kiosk_id,
												 'KioskPlacedOrders.status' => 0, //unconfirmed
												 'KioskPlacedOrders.merged != ' => 1,
												 'KioskPlacedOrders.lock_status' => 0, //unconfirmed
												 //change on 07.03.2016
												 'OR' => array('KioskPlacedOrders.weekly_placed is null',
														   'KioskPlacedOrders.weekly_placed != 1'),
												 'OR' => array('KioskPlacedOrders.weekly_order is null',
														   'KioskPlacedOrders.weekly_order != 1')
											 )
									 )); 
		}else{
				  $kioskPlacedOrder_query = $this->KioskPlacedOrders->find('all', array(
													  'fields' => array('id'),
													  'recursive' => -1,
													  'conditions' => array(
														   'DATE(KioskPlacedOrders.created)' => date('Y-m-d'),
														  'KioskPlacedOrders.kiosk_id' => $kiosk_id,
														  'KioskPlacedOrders.status' => 0, //unconfirmed
														  'KioskPlacedOrders.merged' => 0,
														  'KioskPlacedOrders.lock_status' => 0, //unconfirmed
														  //change on 07.03.2016
														  'OR' => array('KioskPlacedOrders.weekly_placed is null',
																	'KioskPlacedOrders.weekly_placed != 1'),
														  'OR' => array('KioskPlacedOrders.weekly_order is null',
																	'KioskPlacedOrders.weekly_order != 1')
													  )
											  ));
		}
		
        $kioskPlacedOrder_query = $kioskPlacedOrder_query->hydrate(false);
        if(!empty($kioskPlacedOrder_query)){
                $kioskPlacedOrder = $kioskPlacedOrder_query->first();;
        }else{
            $kioskPlacedOrder = array();
        }
        //-----using this block we will fetch all items that warehouse have dispatched to kiosk for confirmed orders.-----
		if($ignore_merge){
				  $kioskConfirmedOrders_query = $this->KioskPlacedOrders->find('all', array(
									'fields' => array('id'),
									'recursive' => -1,
									'conditions' => array(
										 'DATE(KioskPlacedOrders.created)' => date('Y-m-d'),
										'KioskPlacedOrders.kiosk_id' => $kiosk_id,
										'OR' => [
											   'KioskPlacedOrders.status' => 1, //confirmed
										//'KioskPlacedOrders.merged' => 1, //confirmed
										'KioskPlacedOrders.lock_status' => 1, //confirmed
										]
									)
							)); 
		}else{
				  $kioskConfirmedOrders_query = $this->KioskPlacedOrders->find('all', array(
													  'fields' => array('id'),
													  'recursive' => -1,
													  'conditions' => array(
														   'DATE(KioskPlacedOrders.created)' => date('Y-m-d'),
														  'KioskPlacedOrders.kiosk_id' => $kiosk_id,
														  'OR' => [
																 'KioskPlacedOrders.status' => 1, //confirmed
														  'KioskPlacedOrders.merged' => 1, //confirmed
														  'KioskPlacedOrders.lock_status' => 1, //confirmed
														  ]
													  )
											  ));
		}
		
		//pr($kioskConfirmedOrders_query);die;
        $kioskConfirmedOrders_query = $kioskConfirmedOrders_query->hydrate(false);
        if(!empty($kioskConfirmedOrders_query)){
                $kioskConfirmedOrders = $kioskConfirmedOrders_query->toArray();;
        }else{
            $kioskConfirmedOrders = array();
        }
		
		$productsReceived = $todayReceivedProducts = $confirmedOrderIds = array();
		if(count($kioskConfirmedOrders) >= 1){
			foreach($kioskConfirmedOrders as $key => $kioskConfirmedOrder){
				$confirmedOrderIds[] = $kioskConfirmedOrder['id'];
			}
		 
		}
        if(empty($confirmedOrderIds)){
            $confirmedOrderIds = array('0' =>null);
        }
		 
		if(count($confirmedOrderIds)){
			//if we have any confirmed order for the same day, get all product ids with their quantities
			$todayReceivedProducts_query = $this->KioskOrderProducts->find('all',array(
					'fields' => array('product_id','quantity'),
					'recursive' => -1,
					'conditions' => array(
						 'DATE(KioskOrderProducts.created)' => date('Y-m-d'),
						'KioskOrderProducts.kiosk_id' => $kiosk_id,
						'kiosk_placed_order_id IN' => $confirmedOrderIds,
					)
				));
			//pr($todayReceivedProducts_query);
             $todayReceivedProducts_query = $todayReceivedProducts_query->hydrate(false);
            if(!empty($todayReceivedProducts_query)){
                    $todayReceivedProducts = $todayReceivedProducts_query->toArray();;
            }else{
                $todayReceivedProducts = array();
            }
		}
		//pr($todayReceivedProducts);die;	
		foreach($todayReceivedProducts as $todayReceivedProduct){
			$productID = $todayReceivedProduct['product_id'];
			$qty = $todayReceivedProduct['quantity'];
			if(array_key_exists($productID, $productsReceived)){
				$productsReceived[$productID] = $qty + $productsReceived[$productID];
			}else{
				$productsReceived[$productID] = $qty;
			}
		}
		//-----using this block we will fetch all items that warehouse have dispatched to kiosk for confirmed orders.-----
		
		/*
		 *Block quantity opened
		 $product_quantities contails total products sold from morning time.
		 we will subtract received product quantities from these quanties. 
		*/
		 
		foreach($product_quantities as $product_id => $product_quantity){
			if(array_key_exists($product_id, $productsReceived)){
			  	$diff = $product_quantity - $productsReceived[$product_id]; 
				if(($diff) >= 0){
					$product_quantities[$product_id] = $diff;
				}else{
					//if warehouse supplies more than sold, it can go in negative for next order
					$product_quantities[$product_id] = 0;
				}
			}
		}
		//pr($product_quantities);
		/*
		 *Block quantity closed
		**/
		
		$unconfirmedOrderID = null;
		if(count($kioskPlacedOrder) >= 1){
			$unconfirmedOrderID = $kioskPlacedOrder['id'];
		}
		
				
		//--------get already placed quantities--------
		//This should get only for non confirmed orders which warehouse not have yet dispatched.
		$todaysPlacedProductOrders = array();
		if($unconfirmedOrderID){
			$todaysPlacedProductOrders_query = $this->KioskOrderProducts->find('all',array(
				'fields' => array('product_id','quantity','remarks'),
				'recursive' => -1,
				'conditions' => array(
					 'DATE(KioskOrderProducts.created)' => date('Y-m-d'),
					'KioskOrderProducts.kiosk_id' => $kiosk_id,
					'kiosk_placed_order_id' => $unconfirmedOrderID,
				)
			));
              $todaysPlacedProductOrders_query = $todaysPlacedProductOrders_query->hydrate(false);
            if(!empty($todaysPlacedProductOrders_query)){
                    $todaysPlacedProductOrders = $todaysPlacedProductOrders_query->toArray();;
            }else{
                $todaysPlacedProductOrders = array();
            }
		}
    //    pr($todaysPlacedProductOrders);die;
		//---------------------------------------------
		
		
		if ($this->request->is(array('post', 'put'))) {
          	$returnedReceiptIds_query = $kioskProductSaleTable->find('list',[
                                        'keyField' => 'id',
                                        'valueField' => 'product_receipt_id',
										 
										'conditions' => [
											'DATE(modified)' => date('Y-m-d'),
											'DATE(created) != DATE(modified)',
											'refund_status' => 0,
										]
									]
								);
			  $returnedReceiptIds_query = $returnedReceiptIds_query->hydrate(false);
            if(!empty($returnedReceiptIds_query)){
                    $returnedReceiptIds = $returnedReceiptIds_query->toArray();;
            }else{
                $returnedReceiptIds = array();
            }
           // pr($returnedReceiptIds);die;
							//finding the returned product_receipt ids that were not sold today added on 07.03.2016
            $returned_products = array();
            if(count($returnedReceiptIds)){
                $returned_products_query = $kioskProductSaleTable->find('all',array(
                        'fields' => array('product_receipt_id','product_id'),
                        'conditions' => array(
                            'product_receipt_id IN' => $returnedReceiptIds,
                            'refund_status' => 1,
                            'order_refund_value' => 0,
                        ),
                        'group' => array('product_id'),
                        'recursive' => -1
                    )
                );
                 $returned_products_query
          ->select(['total_returned_quantity' => $returned_products_query->func()->sum('quantity')]);
                 $returned_products_query = $returned_products_query->hydrate(false);
                if(!empty($returned_products_query)){
                        $returned_products = $returned_products_query->toArray();;
                }else{
                    $returned_products = array();
                }
            }
           // pr($returned_products);
            $refundArr = array();
            foreach($returned_products as $rsp => $productDetail){
                $refundArr[$productDetail["product_id"]] =$productDetail["product_receipt_id"] ;
            }
        
         //  pr($this->request->data);die;
            if(array_key_exists('placedorder_hidden',$this->request->data)){
                //echo "jj";die;
                $placedProducthiddenArr = $this->request->data['placedorder_hidden'];
                if(!empty($placedProducthiddenArr)){
                    foreach($placedProducthiddenArr as $id => $Qty){
                        if(array_key_exists($id,$refundArr)){
                            $rcitId = $refundArr[$id];
                            $kioskProductSaleTable->updateAll(
                                array('order_refund_value' => "'1'"),
                                array('product_id'=> $id,
                                      'product_receipt_id' => $rcitId,
                                      'refund_status' => 1
                                      )
                            );
                        }
                    }
                }
            }
          //  pr($product_quantities);
			if(count($product_quantities)){
				//here we are generating order id if no order added for today for kiosk otherwise we will use same kiosk order id.
				
				if(count($kioskPlacedOrder)){
					$kiosk_placed_order_id = $kioskPlacedOrder['id'];
					$date = date("y-m-d h:i:s");
					$data = array('created' => $date);
					$KioskPlacedOrders = $this->KioskPlacedOrders->get($kiosk_placed_order_id);
					$KioskPlacedOrders = $this->KioskPlacedOrders->patchEntity($KioskPlacedOrders, $data,['validate' => false]);
                    if($this->KioskPlacedOrders->save($KioskPlacedOrders)) {
						   
					}
				}else{
					//-----------Generating order id here----------------
					$data = array('kiosk_id' => $kiosk_id, 'status' => 0, 'user_id' => $user_id,);
					$KioskPlacedOrders = $this->KioskPlacedOrders->newEntity();
                    $KioskPlacedOrders = $this->KioskPlacedOrders->patchEntity($KioskPlacedOrders, $data,['validate' => false]);
                    if($this->KioskPlacedOrders->save($KioskPlacedOrders)) {
					//if($this->KioskPlacedOrder->save($data)){
						 $kiosk_placed_order_id = $KioskPlacedOrders->id; 
					}
					//---------------------------------------------------
				}
               // echo $kiosk_placed_order_id;die;
				if(!empty($kiosk_placed_order_id)){
					$placed_res_query = $this->MobilePlacedOrders->find('all',array('conditions' => array('kiosk_placed_order_id'=>$kiosk_placed_order_id)));
                    $placed_res_query = $placed_res_query->hydrate(false);
                    if(!empty($placed_res_query)){
                            $placed_res = $placed_res_query->toArray();;
                    }else{
                        $placed_res = array();
                    }
                   //  pr($soldPhones);die;
                    foreach($soldPhones as $m_key => $m_val){
						$brand_id = $model_id = $total_qanty = "";
						$brand_id = $m_val['brand_id'];
						$model_id = $m_val['mobile_model_id'];
						$total_qanty = $m_val['total'];
						if(!empty($placed_res)){ // insert or update
							$check_res_query = $this->MobilePlacedOrders->find('list',['conditions' => ['kiosk_placed_order_id'=>$kiosk_placed_order_id,
																										   'brand' => $brand_id,
																										   'model' => $model_id,
                                                                                                 ],
                                                                                        'keyField' => 'kiosk_placed_order_id',
                                                                                        'valueField' => 'quantity'
																					  
                                                                                ]);
                             $check_res_query = $check_res_query->hydrate(false);
                            if(!empty($check_res_query)){
                                    $check_res = $check_res_query->toArray();;
                            }else{
                                $check_res = array();
                            }
							//$dbo = $this->MobilePlacedOrder->getDatasource();
							//$logData = $dbo->getLog();
							//$getLog = end($logData['log']);
							//echo $getLog['query'];die;
							
							if(!empty($check_res)){
								if($check_res[$kiosk_placed_order_id] != $total_qanty){
									 $updateQuery = "UPDATE `mobile_placed_orders` SET
													`quantity` = $total_qanty
													WHERE `mobile_placed_orders`.`brand` = $brand_id AND `mobile_placed_orders`.`model` = $model_id AND `mobile_placed_orders`.`kiosk_placed_order_id` = $kiosk_placed_order_id";
									$this->MobilePlacedOrders->query($updateQuery);
								}
							}else{
								// insert
								$mobile_data = array(
											 'kiosk_id' => $kiosk_id,
											 'user_id' => $this->request->session()->read('Auth.User.id'),
											 'brand' => $brand_id,
											 'model' => $model_id,
											 'quantity' => $total_qanty,
											 'kiosk_placed_order_id' => $kiosk_placed_order_id,
											 );
                                $MobilePlacedOrder = $this->MobilePlacedOrders->newEntity();
                                $MobilePlacedOrder = $this->MobilePlacedOrders->patchEntity($MobilePlacedOrder, $mobile_data,['validate' => false]);
                                $this->MobilePlacedOrders->save($MobilePlacedOrder);
                                         
							}
						}else{ // insert
							$mobile_data = array(
											 'kiosk_id' => $kiosk_id,
											 'user_id' =>$this->request->session()->read('Auth.User.id'),
											 'brand' => $brand_id,
											 'model' => $model_id,
											 'quantity' => $total_qanty,
											 'kiosk_placed_order_id' => $kiosk_placed_order_id,
											 );
                             $MobilePlacedOrder = $this->MobilePlacedOrders->newEntity();
                             $MobilePlacedOrder = $this->MobilePlacedOrders->patchEntity($MobilePlacedOrder, $mobile_data,['validate' => false]);
                             $this->MobilePlacedOrders->save($MobilePlacedOrder);
							 
						}
					}
				
				}
              //  pr($this->request->data);die;
				$placedProductArr = $this->request->data['placedorder'];
              //  pr($placedProductArr);die;
				$this->set(compact('placedProductArr'));
				// pr($placedProductArr);die;
				$savedRecord = 0;
				$updatedRecords = 0;
				// pr($placedProductArr);die;
				foreach($placedProductArr as $placedProdID => $placedProdQty){
					$remarks = $this->request->data['remarks'][$placedProdID];
					  $diff = $placedProdQty-$product_quantities[$placedProdID];
					//saving difference for quantities requested more than exhausted/sold below
				 
					if($placedProdQty >= 0){
                       //echo $kiosk_placed_order_id;
						$placedProduct_query = $this->KioskOrderProducts->find('all',array(
						'fields' => array('id'),
						//'recursive' => -1,
							'conditions' => array(
								'DATE(KioskOrderProducts.created)' => date('Y-m-d'),
								'KioskOrderProducts.product_id' => $placedProdID,
								'KioskOrderProducts.kiosk_id' => $kiosk_id,
								'KioskOrderProducts.kiosk_placed_order_id' => $kiosk_placed_order_id
								)
							)
						);
                       	$placedProduct_query = $placedProduct_query->hydrate(false);
                       if(!empty($placedProduct_query)){
                              $placedProduct = $placedProduct_query->first();
                        } else{
                            $placedProduct = array();
                        }
                       // pr($placedProduct);die;
						if(count($placedProduct) >= 1 ){
							$this->KioskOrderProducts->id = $placedProduct['id'];
							$data = array(
									  'product_id' => $placedProdID ,
									  'quantity' => $placedProdQty,
									  'org_qty' => $placedProdQty,
									  'difference' => $diff,
									  'kiosk_id' => $kiosk_id,
									  'status' => 0,
									  'kiosk_placed_order_id' => $kiosk_placed_order_id,
									  'id' => $placedProduct['id'],
									  'remarks' => $remarks,
									  );
							// pr($data);die;
                            //echo $this->KioskOrderProducts->id;die;
                              $KioskOrderProduct = $this->KioskOrderProducts->get($this->KioskOrderProducts->id);

                             $KioskOrderProduct = $this->KioskOrderProducts->patchEntity($KioskOrderProduct, $data ,['validate' => false]);
                             if(empty($KioskOrderProduct->errors())){
                                 $this->KioskOrderProducts->save($KioskOrderProduct);
                                 $updatedRecords++;
                             }else{
                               // echo "hh";
								$errors = $KioskOrderProduct->errors();
								pr($errors);
							}
                          
							
						}else{
					//	echo "insert record";
							$data = array(
									  'product_id' => $placedProdID ,
									  'quantity' => $placedProdQty,
									  'org_qty' => $placedProdQty,
									  'difference' => $diff,
									  'kiosk_id' => $kiosk_id,
									  'status' => 0,
									  'kiosk_placed_order_id' => $kiosk_placed_order_id,
									  'remarks' => $remarks,
									  );
							$KioskOrderProducts = $this->KioskOrderProducts->newEntity();
                            $KioskOrderProducts = $this->KioskOrderProducts->patchEntity($KioskOrderProducts, $data,['validate' => false]);
                             	 if ($this->KioskOrderProducts->save($KioskOrderProducts,['validate' => false])) {
							 
								if(array_key_exists($placedProdID,$refundArr)){
									$rcitId = $refundArr[$placedProdID];
									$kioskProductSaleTable->updateAll(
										array('order_refund_value' => "'1'"),
										array('product_id'=> $placedProdID,
											  'product_receipt_id' => $rcitId,
											  'refund_status' => 1)
									);
								}
								$savedRecord++;
							}else{
								$errors = $KioskOrderProducts->errors();
								pr($errors);
							}
						}
					}
				}
				$flashMessage = "Order placed for $savedRecord product(s)";
				$flashMessage.= "<br/>Order updated for $updatedRecords product(s)";
                  $this->Flash->success(__("Order placed successfully!<br/>$flashMessage"),['escape' => false]);
				//$this->Session->setFlash(__("Order placed successfully!<br/>$flashMessage"));
				return $this->redirect(array('controller' => 'kiosk_orders', 'action' => 'initiate_order_placement'));
			}	
		}
		$todaysPlaced = array();
		if(is_array($todaysPlacedProductOrders) && count($todaysPlacedProductOrders) >= 1){
			foreach($todaysPlacedProductOrders as $todaysPlacedProductOrder){
				$placedProdID = $todaysPlacedProductOrder['product_id'];
				$placedQty = $todaysPlacedProductOrder['quantity'];
				$placedRemarks = $todaysPlacedProductOrder['remarks'];
				$todaysPlaced[$placedProdID] = array($placedQty,$placedRemarks);	
			}
				
		}
		$this->set(compact('product_quantities','todaysPlaced', 'origQtys', 'returnedProductsArr'));
		$this->set(compact('products'));
		$this->set(compact('placedproduct_quantities'));
		 
	}
	
	public function placeOrder() {
		//function to place order in the day any time.
		//If order will be placed 2nd time in a day, same order will be updated next time for the day
		//if admin dispatch placed order in between day, new order should be created if kisok place another order in a day after dispatch
		//find today's sale from $productSalesSource
		$kiosk_id = $this->request->session()->read('kiosk_id');
		$user_id = $this->request->session()->read('Auth.User.id');	//rasa
		$kiosk_placed_order_id = 0;
		
		$kioskProductTable_source = "kiosk_{$kiosk_id}_product_sales";
        $KioskProductTable = TableRegistry::get($kioskProductTable_source,[
                                                                    'table' => $kioskProductTable_source,
                                                                ]);
		
		$soldProducts_query = $KioskProductTable->find('all',array(
						'fields' => array('product_id'),
						'conditions' => array(
								      'DATE(created)' => date('Y-m-d'),
								      'refund_status' => 0
								      ),
						'group' => array('product_id'),
						)
					      );
		$soldProducts_query
											->select(['total_quantity' => $soldProducts_query->func()->sum('quantity')]);
		$soldProducts_query = $soldProducts_query->hydrate(false);
		if(!empty($soldProducts_query)){
				  $soldProducts = $soldProducts_query->toArray(); 
		}else{
				  $soldProducts = array();
		}
		if(count($soldProducts)){
			//auto place order only if atleast one item sold
			// status 0 to check if the products have been dispatched or not, if zero use the same placed order id and update
			$kioskPlacedOrder_query = $this->KioskPlacedOrders->find('all', array(
									'fields' => array('id'),
									//'recursive' => -1,
									'conditions' => array(
										'DATE(created)' => date('Y-m-d'),
										'kiosk_id' => $kiosk_id,
										'status' => 0
									)
								     ));
			$kioskPlacedOrder_query = $kioskPlacedOrder_query->hydrate(false);
			if(!empty($kioskPlacedOrder_query)){
				  $kioskPlacedOrder = $kioskPlacedOrder_query->first();
			}else{
				  $kioskPlacedOrder = array();
			}
			if(count($kioskPlacedOrder)){
				//--------------
				$kiosk_placed_order_id = $kioskPlacedOrder['id'];
			}else{
				//create place order
				$data = array(
						'kiosk_id' => $kiosk_id,
						'status' => 0,
						'user_id' => $user_id,
					      );
				$new_entity = $this->KioskPlacedOrders->newEntity($data,['validate'=>false]);
				$new_entity = $this->KioskPlacedOrders->patchEntity($new_entity,$data,['validate'=>false]);
				if($this->KioskPlacedOrders->save($new_entity)){
					$kiosk_placed_order_id = $new_entity->id;
				}else{
				  pr($new_entity->errors());die;
				}
			}
			
			foreach($soldProducts as $key => $soldProduct){
				//$this->KioskOrderProduct->clear();
				//pr($soldProduct);
				//check if any order was placed for the same product under the same order id which is not dispatched till now
				$productID = $soldProduct['product_id'];
				$quantity = $soldProduct['total_quantity'];
				$product_query = $this->KioskOrderProducts->find('all',array(
										'fields' => array('id'),
										'conditions' => array(
											'DATE(created)' => date('Y-m-d'),
											'product_id' => $productID,
											'kiosk_placed_order_id' => $kiosk_placed_order_id
										)
									     ));
				$product_query = $product_query->hydrate(false);
				if(!empty($product_query)){
				  $product = $product_query->first();
				}else{
				  $product = array();
				}
				
				if(count($product)){				
					//update record
					$data = array();
					$id = $product['id'];
					$get_entity = $this->KioskOrderProducts->get($id);
					
					$data = array(
						      //'id' => $id,
						      'product_id' => $productID,
						      'quantity' => $quantity,
						      'kiosk_placed_order_id' => $kiosk_placed_order_id,
						      );
					$get_entity = $this->KioskOrderProducts->patchEntity($get_entity,$data,['validate' => false]);
					if($this->KioskOrderProducts->save($get_entity)){
						   
					}else{
						   pr($get_entity->errors());die;
					}
				}else{
					//add record				
					$data = array(
						      'user_id' => $user_id,
						      'product_id' => $productID,
						      'quantity' => $quantity,
						      'kiosk_id' => $kiosk_id,
						      'status' => 0,
						      'kiosk_placed_order_id' => $kiosk_placed_order_id,
						      );
					//pr($data);
					
				  	$new_entity = $this->KioskOrderProducts->newEntity($data,['validate'=>false]);
				    $new_entity = $this->KioskOrderProducts->patchEntity($new_entity,$data,['validate'=>false]);
					if($this->KioskOrderProducts->save($new_entity)){
						//-------------------
					}else{
						   pr($data->errors());die;
					}
				}
			}
		}
		
		$this->Flash->success(__("Order placed successfully!"));
		return $this->redirect(array('controller' => 'home', 'action' => 'index'));
		
	}
	
	public function mergeOrder(){
		 $this->loadModel('MobilePlacedOrders');
		 $order_ids = $this->request->data['merge_ids'];
		 $kiosk_id = $this->request->data['kiosk_id'];
		 $curent_user_id = $this->request->session()->read('Auth.User.id');
		 $today_created_arr = $privous_order_data = array();
		 
		 foreach($order_ids as $order_k => $order_id_k){
						   $get_res = $this->KioskPlacedOrders->get($order_id_k);
						    $id_to_save = $get_res->id;
						   $created = $get_res->created;
						   
						   $created_timestamp = strtotime(date("y-m-d",strtotime($created)));
						   $today_timestamp = strtotime(date("y-m-d"));
						   if($created_timestamp == $today_timestamp){
									$today_created_arr[] = $created;
						   }
						   $user_id = $get_res->user_id;
						   $privous_order_data[$id_to_save] = array('id' => $id_to_save,
																	'created' => $created,
																	'user_id' => $user_id,
																	); 
				  }
				  
				  
		 $serilize_data = serialize($privous_order_data);
		 if(!empty($today_created_arr)){
				  $cretaed_date = $today_created_arr[0];
				  $data = array('kiosk_id' => $kiosk_id, 'status' => 0, 'user_id' => $curent_user_id,'merged' => 1,'merge_data' => $serilize_data);
				  // 'created' =>$cretaed_date
		 }else{
				  $data = array('kiosk_id' => $kiosk_id, 'status' => 0, 'user_id' => $curent_user_id,'merged' => 1,'merge_data' => $serilize_data);
		 }
		 
		 
		 $KioskPlacedOrders = $this->KioskPlacedOrders->newEntity();
		 $KioskPlacedOrders = $this->KioskPlacedOrders->patchEntity($KioskPlacedOrders, $data,['validate' => false]);
		 
		 
		 if($this->KioskPlacedOrders->save($KioskPlacedOrders)) {
			  $kiosk_placed_order_id = $KioskPlacedOrders->id; 
		 }
		 $data = array();
		 foreach($order_ids as $key => $old_order_ids){
				  $res = $this->KioskOrderProducts->find("all",array(
															  'conditions' => array(
															   'kiosk_placed_order_id' => $old_order_ids
															  )
															  ))->toArray();
				  if(!empty($res)){
						   foreach($res as $k => $orderProduct){
									$placedProdID = $orderProduct->product_id;
									$placedProdQty = $orderProduct->quantity;
									$org_qty = $orderProduct->org_qty;
									$kiosk_id = $orderProduct->kiosk_id;
									$remarks = $orderProduct->remarks;
									if(array_key_exists($placedProdID,$data)){
											 $data[$placedProdID]['quantity'] = $data[$placedProdID]['quantity'] + $placedProdQty;
											 $data[$placedProdID]['org_qty'] = $data[$placedProdID]['org_qty'] + $org_qty;
									}else{
											 $data[$placedProdID] = array(
													  'product_id' => $placedProdID ,
													  'quantity' => $placedProdQty,
													  'org_qty' => $org_qty,
													  'difference' => 0,
													  'kiosk_id' => $kiosk_id,
													  'status' => 0,
													  'kiosk_placed_order_id' => $kiosk_placed_order_id,
													  'remarks' => $remarks,
											 );		 
									}
									
						   }
						   
				  }
				  
		 }
		 if(!empty($data)){
				  foreach($data as $k1 => $val){
						   $KioskOrderProducts = $this->KioskOrderProducts->newEntity();
						   $KioskOrderProducts = $this->KioskOrderProducts->patchEntity($KioskOrderProducts, $val,['validate' => false]);
						   if ($this->KioskOrderProducts->save($KioskOrderProducts,['validate' => false])) {
						   }
				  }
				  foreach($order_ids as $order_k => $order_id_k){
						   $MobilePlacedOrders = $this->MobilePlacedOrders->find('all', [
									'conditions' => [
											 'kiosk_placed_order_id' => $order_id_k
									]
								])->toArray();
						   if(!empty($MobilePlacedOrders)){
									foreach($MobilePlacedOrders as $m_key => $m_value){
											 $mobile_id = $m_value->id;
											 $mobile_pdate_query = "UPDATE `mobile_placed_orders` set kiosk_placed_order_id = $kiosk_placed_order_id where id = $mobile_id";
											 $conn = ConnectionManager::get('default');
											 $stmt = $conn->execute($mobile_pdate_query); 		 
									}
									
						   }
						   
						   $get_res = $this->KioskPlacedOrders->get($order_id_k);
						   if($this->KioskPlacedOrders->delete($get_res)){
									$this->KioskOrderProducts->deleteAll(array('KioskOrderProducts.kiosk_placed_order_id'=>$order_id_k));								
						   }					   
				  }
			return $this->redirect(array('controller' => 'StockTransfer', 'action' => 'placed-order',$kiosk_placed_order_id));	  
		 } 
	}
	
	
	public function placedOrdersCrone(){
		 $kiosks = $this->Kiosks->find("list",[
									'keyField' => 'id',
									'valueField' => 'name',
									])->toArray();
		 if(array_key_exists(10000,$kiosks)){
				  unset($kiosks[10000]);
		 }
		 if($this->request->is('post')){
				  
				  $kioskId = $this->request->data['kiosk'];
				  $date = $this->request->data['start_date'];
				  $start_date = date("Y-m-d",strtotime($date));
				  
				  $start_date_with_time = date("Y-m-d h:i:s",strtotime($date));
				  
				  $query1 = "SELECT now() as dt";
				  $connection = ConnectionManager::get('default');
				  $stmt = $connection->execute($query1);
				  $query1Result = $stmt ->fetchAll('assoc');
				  $created = $query1Result[0]['dt'];
				  
				  
				  $disputedProductsQuery = "SELECT `product_id`, SUM(`quantity`) as disputed_quantity FROM order_disputes WHERE DATE(`admin_acted`) = '$start_date' AND `kiosk_id` = '$kioskId' AND `receiving_status` = '-1' AND `approval_status` = '1' GROUP BY `product_id`";
				  
				  $connection = ConnectionManager::get('default');
				        $stmt = $connection->execute($disputedProductsQuery);
				  $disputedProducts = $stmt ->fetchAll('assoc');
				  
				  $disputedProductsArr = array();
				  if(!empty($disputedProducts)){
						   foreach($disputedProducts as $dispute_key){
									$disputedProductsArr[] = $dispute_key;
						   }
				  }
				  
				  $phone_query = "SELECT `MobileBlkReSale`.`brand_id`, `MobileBlkReSale`.`mobile_model_id`, count(*) as total FROM `mobile_blk_re_sales` AS `MobileBlkReSale` WHERE DATE(`MobileBlkReSale`.`created`) = '$start_date' and `MobileBlkReSale`.`kiosk_id` = $kioskId GROUP BY brand_id, mobile_model_id";
				  
				  $connection = ConnectionManager::get('default');
				        $stmt = $connection->execute($phone_query);
				  $phone_query_exec = $stmt ->fetchAll('assoc');
				  
				  $phone_data_array = array();
                        if(!empty($phone_query_exec)){
                            foreach($phone_query_exec as $phone_key){
                                $phone_data_array[] = $phone_key;
                            }
						}
				  
				  
				  $repairPartsQuery = "SELECT `product_id`, COUNT(`product_id`) as total_quantity FROM `mobile_repair_parts` WHERE DATE(`created`) = '$start_date' AND `kiosk_id` = '$kioskId' GROUP BY `product_id`";
				  
				   $connection = ConnectionManager::get('default');
				        $stmt = $connection->execute($repairPartsQuery);
				  $repairParts = $stmt ->fetchAll('assoc');
				  
				  $repairnum_rows = 0;
				  $repairPartArr = array();
				  if(!empty($repairParts)){
						   $repairnum_rows = 1;
						 foreach($repairParts as $repairParts_key){
						   $repairPartArr[] = $repairParts_key;
						 }
				  }
				  
				  
				  
				  $productQttArr = array();
                    foreach($repairPartArr as $key => $repair_parts){
                        $productQttArr[$repair_parts['product_id']] = $repair_parts['total_quantity'];
                    }
				  
				  $saleQuery = "SELECT product_receipt_id FROM kiosk_{$kioskId}_product_sales AS `KioskProductSale` WHERE DATE(`KioskProductSale`.`created`) = '$start_date' AND `KioskProductSale`.`refund_status` = 0";
				  
				  $connection = ConnectionManager::get('default');
				        $stmt = $connection->execute($saleQuery);
				  $recit_Ids = $stmt ->fetchAll('assoc');
				  
				  $resIDS = array();
				  if(!empty($recit_Ids)){
						   foreach($recit_Ids as $recit_Ids_key => $recit_Ids_value){
									$resIDS[] =  $recit_Ids_value['product_receipt_id'];
						   }
				  }
				  
				  $ids = "'".implode("','",$resIDS)."'";
				  
				  $faulty_query = "SELECT `product_id`, SUM(`quantity`) as faulty_quantity FROM faulty_products WHERE DATE(`created`) = '$start_date' AND `kiosk_id` = '$kioskId' AND `receipt_id` IN($ids) GROUP BY `product_id`";
				  
				  $connection = ConnectionManager::get('default');
				        $stmt = $connection->execute($faulty_query);
				  $faultyReturnedProduct = $stmt ->fetchAll('assoc');
				  
				  $faultyProductDetail = array();
				  if(!empty($faultyReturnedProduct)){
						   foreach($faultyReturnedProduct as $faultyReturnedProduct_key){
									$faultyProductDetail[] = $faultyReturnedProduct_key;
						   }
				  }
				  
				  
				  $query3 = "SELECT `product_id`, SUM(`quantity`) as total_quantity FROM kiosk_{$kioskId}_product_sales WHERE  DATE(`created`) = '$start_date' AND `refund_status` = 0 GROUP BY `product_id`";
				  
				  $connection = ConnectionManager::get('default');
				        $stmt = $connection->execute($query3);
				  $soldProductRS = $stmt ->fetchAll('assoc');
				  
				  
				  
				  if(!empty($soldProductRS) || $repairnum_rows == 1 || count($faultyProductDetail) || count($disputedProductsArr)){
						   foreach($soldProductRS as $soldProductRS_key){
									$soldProdArray[] = $soldProductRS_key;
						   }
						   //pr($soldProdArray);die;
						   foreach($soldProdArray as $key => $soldProd){
									
									if(array_key_exists($soldProd['product_id'], $productQttArr)){
											 $productQttArr[$soldProd['product_id']]+=$soldProd['total_quantity'];
									}else{
											 if($soldProd['total_quantity'] > 0){
												 $productQttArr[$soldProd['product_id']] = $soldProd['total_quantity'];
												 //we are only adding quantities that are more than 0
											 }
									}
						   }
						   
						   
						   $query4 =  "SELECT `id`, `product_receipt_id` FROM kiosk_{$kioskId}_product_sales AS `KioskProductSale` WHERE DATE(`KioskProductSale`.`modified`) = '$start_date' AND DATE(`KioskProductSale`.`created`) != DATE(`KioskProductSale`.`modified`) AND `refund_status` = 0 ";
						   
						$connection = ConnectionManager::get('default');
				        $stmt = $connection->execute($query4);
				        $query4Result = $stmt ->fetchAll('assoc');
						
						$returnedReceiptIds = array();
						if(!empty($query4Result)){
						   foreach($query4Result as $query4Result_key => $query4Result_value){
									$returnedReceiptIds[] = $query4Result_value['product_receipt_id'];
						   }
						}
						
						if(count($returnedReceiptIds)){
						   $recIDs = "'".implode("','",$returnedReceiptIds)."'";
						   $query5 = "SELECT `product_id`, SUM(`quantity`) as total_returned_quantity FROM kiosk_{$kioskId}_product_sales AS `KioskProductSale` WHERE `KioskProductSale`.`product_receipt_id` IN ($recIDs) AND `refund_status` = 1 AND `KioskProductSale`.`order_refund_value` = 0 GROUP BY `KioskProductSale`.`product_id`";
						   
						   $connection = ConnectionManager::get('default');
						   $stmt = $connection->execute($query5);
						   $query5Result = $stmt ->fetchAll('assoc');
						   
						   $retProds = array();
						   if(!empty($query5Result)){
									foreach($query5Result as $query5Result_key => $query5Result_value){
											 $retProds[$query5Result_value['product_id']] = $query5Result_value['total_returned_quantity'];
									}
						   }
						   
						   foreach($retProds as $retProdID => $retProdQty){
                                if(array_key_exists($retProdID, $productQttArr)){
                                    //subtract qty
                                    $actualQty = $productQttArr[$retProdID];
                                    $remainingQty = $actualQty -  $retProdQty;
                                    $productQttArr[$retProdID] = $remainingQty;
                                    if($remainingQty < 0){
                                        //returned quanties are more than sold, than we will set $remainingQty = 0
                                        $productQttArr[$retProdID] = 0;
                                    }
                                }
                            }
						   
						}
						
						if(count($faultyProductDetail)){
                            foreach($faultyProductDetail as $fp => $faultyPrdctDetail){
                                if(array_key_exists($faultyPrdctDetail['product_id'], $productQttArr)){
                                    $productQttArr[$faultyPrdctDetail['product_id']]+=$faultyPrdctDetail['faulty_quantity'];
                                }else{
                                    $productQttArr[$faultyPrdctDetail['product_id']] = $faultyPrdctDetail['faulty_quantity'];
                                }
                            }
                        }
						
						
						if(count($disputedProductsArr)){
                            foreach($disputedProductsArr as $dpk => $disputed_products){
                                if(array_key_exists($disputed_products['product_id'], $productQttArr)){
                                    $productQttArr[$disputed_products['product_id']]+= $disputed_products['disputed_quantity'];;
                                }else{
                                    $productQttArr[$disputed_products['product_id']] = $disputed_products['disputed_quantity'];;
                                }
                            }
                        }
						
						$query6 = "SELECT `product_receipt_id` FROM kiosk_{$kioskId}_product_sales AS `KioskProductSale` WHERE DATE(`KioskProductSale`.`modified`) = '$start_date' AND DATE(`KioskProductSale`.`created`) != DATE(`KioskProductSale`.`modified`) AND `refund_status` = 0"; //echo "\n\n".$query6;
						
						$connection = ConnectionManager::get('default');
				        $stmt = $connection->execute($query6);
				        $query6Result = $stmt ->fetchAll('assoc');
						
						$reciptIDSArr = array();
						if(!empty($query6Result)){
						   foreach($query6Result as $query6Result_key => $query6Result_value){
									$reciptIDSArr[] = $query6Result_value['product_receipt_id'];
						   }
						}
						
						$rcIDS = "";
                        if(!empty($reciptIDSArr)){
                            $rcIDS = "'".implode("', '",$reciptIDSArr)."'";    
                        }
						
						$returnedArr = array();
						if(!empty($rcIDS)){
						   
						   $query7 = "SELECT `product_receipt_id`,`product_id`, SUM(`quantity`) as total_returned_quantity FROM kiosk_{$kioskId}_product_sales AS `KioskProductSale` WHERE `KioskProductSale`.`product_receipt_id` IN ($rcIDS) AND `refund_status` = 1 AND `order_refund_value` = 0 GROUP BY `KioskProductSale`.`product_id`";
						   
						   $connection = ConnectionManager::get('default');
						   $stmt = $connection->execute($query7);
						   $query7Result = $stmt ->fetchAll('assoc');
						   
						   foreach($query7Result as $query7Result_key => $query7Result_value){
									$prodIdx = $query7Result_value['product_id'];
									$qtyRet = $query7Result_value['total_returned_quantity'];
									$recptID = $query7Result_value['product_receipt_id'];
									$returnedArr[$prodIdx] = array('returned_quantity' => $qtyRet, 'receipt_id' => $recptID);
						   }
						}
						   foreach($returnedArr as $prodIdx => $returnedRow){
									/*
									 In this loop we are subtracting product quantities from $productQttArr for the matching product id in $returnedArr
									*/
									if(array_key_exists($prodIdx, $productQttArr)){
										$quantitySold = $productQttArr[$prodIdx];
										$quantityReturned = $returnedArr[$prodIdx]['returned_quantity'];
										$receiptID = $returnedArr[$prodIdx]['receipt_id'];
										//echo "\nquantitySold: $quantitySold; quantityReturned:$quantityReturned";
										$remQty = $quantitySold - $quantityReturned;
										$remQty = $remQty < 0 ? 0:$remQty;
										//$productQttArr[$prodIdx] = $remQty;
										//Products which are refunded have refund_status = 1; and we are setting order_refund_value = 1
										$refundQry = "UPDATE kiosk_{$kioskId}_product_sales AS `KioskProductSale` SET `KioskProductSale`.`order_refund_value` = '1' WHERE `KioskProductSale`.`product_id` = $prodIdx AND `KioskProductSale`.`product_receipt_id` = $receiptID AND `KioskProductSale`.`refund_status` = 1";
										
											 $connection = ConnectionManager::get('default');
											 $stmt = $connection->execute($refundQry);
											 
									}
						   }
						   
						   $query2 = "SELECT `id`, `user_id` FROM `kiosk_placed_orders` WHERE DATE(`created`) = '$start_date' AND `kiosk_id` = '$kioskId' AND (`weekly_order` <> 1 or `weekly_order` IS NULL )  AND (`weekly_placed` <> 1 OR `weekly_placed` IS NULL) AND (`merged` <> 1)";
									
									$connection = ConnectionManager::get('default');
									$stmt = $connection->execute($query2);
									$resultplacedorder = $stmt ->fetchAll('assoc');
									$userId = 0;
									if(count($resultplacedorder)){
											 foreach($resultplacedorder as $resultplacedorder_key => $resultplacedorder_value){
													  $kioskPlacedOrderId = $resultplacedorder_value['id'];
													  $userId = $resultplacedorder_value['user_id'];
											 }
									}else{
											 $data = array(
														   'kiosk_id' => $kioskId,
														   'user_id' => 40,
														   'status' => 0,
														   'created' => $start_date_with_time,
														   'modified' => $start_date_with_time
														   ); 
											 $statement = $connection->insert('kiosk_placed_orders',$data);
											 $kioskPlacedOrderId =  $statement->lastInsertId('kiosk_placed_orders');
									}
				  }  
						
						
						if(count($phone_data_array) > 0){
                            foreach($phone_data_array as $number => $phone_data){
                                $model_id = $brand_id = $phone_quantity = "";
                                $brand_id = $phone_data['brand_id'];
                                $model_id = $phone_data['mobile_model_id'];
                                $phone_quantity = $phone_data['total'];
								
								$phone_data = array(
									'kiosk_id' => $kioskId,
									'brand' => $brand_id,
									'model' => $model_id,
									'quantity' => $phone_quantity,
									'kiosk_placed_order_id' => $kioskPlacedOrderId,
									'user_id' => 40,
									'created' => $start_date_with_time,
									'modified' => $start_date_with_time,
								);
								
								$statement = $connection->insert('mobile_placed_orders',$phone_data);
				  
								 
                               // $kioskPlacedOrderId = mysqli_insert_id($warehouseDBLink);
                            }
                        }
						
						if(count($productQttArr) > 0){
						   
						   if($userId > 0){
									$countUpdatedProducts = 0;
									foreach($productQttArr as $product => $quantity){
											 $countQuery = "SELECT count(*) as countProduct FROM `kiosk_order_products` WHERE `kiosk_id` = '$kioskId' AND `kiosk_placed_order_id` = '$kioskPlacedOrderId' AND `product_id` = '$product'";
											 
											 $connection = ConnectionManager::get('default');
											 $stmt = $connection->execute($countQuery);
											 $count_query = $stmt ->fetchAll('assoc');
											 
											 if(!empty($count_query)){
													  
													  foreach($count_query as $count_query_key => $count_query_value){
															   if($count_query_value['countProduct'] >= 1){
																		
																		$prodUpQry = "UPDATE `kiosk_order_products` SET `quantity` = '$quantity' + `difference` , `org_qty` = '$quantity' + `difference` WHERE `kiosk_id` = '$kioskId' AND `kiosk_placed_order_id` = '$kioskPlacedOrderId' AND `product_id` = '$product'";
																	
															$connection = ConnectionManager::get('default');
															   $stmt = $connection->execute($prodUpQry);
															   
															   }else{
																		
																		$data_to_save = array(
																							  'kiosk_id' => $kioskId,
																							  'kiosk_placed_order_id' => $kioskPlacedOrderId,
																							  'product_id' => $product,
																							  'quantity' => $quantity,
																							  'org_qty' => $quantity,
																							  'status' => 0,
																							  'created' => $start_date_with_time,
																							  'modified' => $start_date_with_time,
																							  );
																		$statement = $connection->insert('kiosk_order_products',$data_to_save);
															   }
													  }
													  $countUpdatedProducts++;
											 }
									}
									if($countUpdatedProducts > 0){
											 
									}
						   }else{
											 foreach($productQttArr as $product => $quantity){
													  $data_to_save = array(
																							  'kiosk_id' => $kioskId,
																							  'kiosk_placed_order_id' => $kioskPlacedOrderId,
																							  'product_id' => $product,
																							  'quantity' => $quantity,
																							  'org_qty' => $quantity,
																							  'status' => 0,
																							  'created' => $start_date_with_time,
																							  'modified' => $start_date_with_time,
																							  );
													  $statement = $connection->insert('kiosk_order_products',$data_to_save);
											 }
						   }
				  }
				  if(!empty($kioskPlacedOrderId)){
						   $start_date = date("d-m-Y",strtotime($start_date));
						   $msg = "Order Placed For Order Id " .$kioskPlacedOrderId . " on Order date ". $start_date ;		   
				  }else{
						   $msg = "No Order Generated" ;		   
				  }
				 
				  $this->Flash->success(__($msg));			 
		 }
		
		 $this->set(compact('kiosks'));	
	}
	
	public function deleteTransientOrder($id,$kiosk_id){
		 $kiosk_order_entity = $this->KioskOrders->get($id);
		 $res = $this->StockTransfer->find("all",[
				  
													  'conditions' => ['kiosk_order_id' => $id],
										   ])->toArray();
		 if(!empty($res)){
				   $this->StockTransfer->deleteAll(array('StockTransfer.kiosk_order_id'=>$id));
		 }
		 if($this->KioskOrders->delete($kiosk_order_entity)){
				  $flashMessage = "Order Deleted successfully";
				 $this->Flash->success(__($flashMessage));
				return $this->redirect(array('controller' => 'kiosk_orders', 'action' => 'transient_orders'));			  
		 }
		 
	}
	
	public function deleteKioskTransientOrder($id){
		 $this->loadModel("CenterOrders");
		 $this->loadModel("StockTransferByKiosk");
		 $kiosk_order_entity = $this->CenterOrders->get($id);
		 $res = $this->StockTransferByKiosk->find("all",[
				  
													  'conditions' => ['kiosk_order_id' => $id],
										   ])->toArray();
		 if(!empty($res)){
				   $this->StockTransferByKiosk->deleteAll(array('StockTransferByKioskTable.kiosk_order_id'=>$id));
		 }
		 if($this->CenterOrders->delete($kiosk_order_entity)){
				  $flashMessage = "Order Deleted successfully";
				 $this->Flash->success(__($flashMessage));
				return $this->redirect(array('controller' => 'kiosk_orders', 'action' => 'transient-kiosk-orders'));			  
		 }
	}
	
	
}
?>