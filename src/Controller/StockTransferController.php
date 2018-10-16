<?php
namespace App\Controller;

use App\Controller\AppController;
use Cake\Event\Event;
use Cake\Core\Configure;
use Cake\Core\Configure\Engine\PhpConfig;
use Cake\Network\Request;
use Cake\ORM\TableRegistry;
use Cake\Mailer\Email;
use Cake\I18n\Time;

use Cake\Datasource\ConnectionManager;

class StockTransferController extends AppController{
    
     public $helpers = [
         'Paginator' => ['templates' => 'paginatortemplates']
         ];
    public function initialize()
    {
        $siteUrl = Configure::read('SITE_BASE_URL');
        // pr($siteUrl);die;
        parent::initialize();
		$this->loadComponent('TableDefinition');
		$this->loadComponent('SessionRestore');
        $this->loadComponent('ScreenHint');
        $this->loadComponent('CustomOptions');
        $this->loadModel('TransferSurplus');
        $this->loadModel('Products');
        $this->loadModel('Users');
        $this->loadModel('Categories');
        $this->loadModel('TransferUnderstock');
        $this->loadModel('StockTransfer');
		$this->loadModel('ReservedProducts');
		$this->loadModel('Customers');
		$this->loadModel('KioskOrders');
        $this->loadModel('OrderDisputes');
		$this->loadModel('KioskProductSales');
        $this->loadModel('KioskCancelledOrderProducts');
		$this->loadModel('MobileRepairLogs');
        $this->loadModel('MobileRepairs');
        $this->loadModel('MobileUnlockLogs');
        $this->loadModel('MobileUnlocks');
        $this->loadModel('MobileUnlockSales');
        $this->loadModel('MobileReSales');
        $this->loadModel('MobilePurchases');
        $this->loadModel('MobileBlkReSales');
        $this->loadModel('MobileUnlockPrices');
        $this->loadModel('ProblemTypes');
        $this->loadModel('MobileRepairParts');
        $this->loadModel('MobileModels');
        $this->loadModel('Brands');
        $this->loadModel('Networks');
        $this->loadModel('OnDemandProducts');
        $this->loadModel('KioskPlacedOrders');
        $this->loadModel('KioskOrderProducts');
        $this->loadModel('MobilePlacedOrders');
		$this->loadModel('on_demand_products');
		$this->loadModel('OnDemandOrders');
		$this->loadModel('daily_stocks_hp');
		
		$this->loadComponent('Pusher');
       	$CURRENCY_TYPE = Configure::read('CURRENCY_TYPE');
		$this->set(compact('CURRENCY_TYPE','siteUrl'));
    }
    
    public function lostStock(){
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
            $this->Flash->error(__("This function works only on hpwaheguru"));
			return $this->redirect(array('controller' => 'home','action' => "dashboard"));die;
		}
		$this->paginate = ['order'=>['TransferSurplus.id desc'],
                            'limit'=>[100]
                            ]
             ;
		$surplusData = $this->paginate($this->TransferSurplus);
		//pr($surplusData);die;

                          
        $totalCostNSalePrice_query = $this->TransferSurplus->find('all');
        $totalCostNSalePrice_query
                            ->select(['total_cost' => $totalCostNSalePrice_query->func()->sum('cost_price * quantity')])
                            ->select(['total_sale' => $totalCostNSalePrice_query->func()->sum('sale_price * quantity')]);
        $totalCostNSalePrice_query = $totalCostNSalePrice_query->hydrate(false);
        $totalCostNSalePrice = $totalCostNSalePrice_query->first();
        //pr($totalCostNSalePrice);die;
		$productArr = array();
		$product_query = $this->Products->find('all',array('fields' =>array('id','product','product_code') ,'recursive'=>-1));
        $product_query->hydrate(false);
        if(!empty($product_query)){
         $product  = $product_query->toArray();
        }else{
            $product = array();
        }
		foreach($product as $key => $value){
			$productArr[$value['id']]['product'] =  $value['product'];
			$productArr[$value['id']]['product_code'] =  $value['product_code'];
		}
		$catData_query = $this->Categories->find('all',array('fields'=>array('id','category'),'recursive'=>-1,'order'=>'Categories.category ASC'));
        $catData_query->hydrate(false);
        if(!empty($catData_query)){
         $catData  = $catData_query->toArray();
        }else{
            $catData = array();
        }
		$categories = $this->CustomOptions->category_options($catData,true);
		
		$catagory_query = $this->Categories->find('list',['keyField'=>'id','valueField' => 'category']);
        $catagory_query->hydrate(false);
        if(!empty($catagory_query)){
         $catagory  = $catagory_query->toArray();
        }else{
            $catagory = array();
        }
		$searched = 0;
		$this->set(compact('surplusData','productArr','catagory','categories','totalCostNSalePrice','searched'));
	}
    
    public function searchLostStock(){
        //pr($this->request);die;
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
            $this->Flash->error(__("This function works only on hpwaheguru"));
			return $this->redirect(array('controller' => 'home','action' => "dashboard"));die;
		}
		$conditionArr = array();
		$endDate = $startDate = $from_date = $to_date = $search_KW = "";
		if(!empty($this->request->query['from_date'])){
			$from_date = date("Y-m-d",strtotime($this->request->query['from_date']));
			$startDate = date('Y-m-d', strtotime($from_date.'-1 day'));
		}
		if(!empty($this->request->query['to_date'])){
			$to_date = date("Y-m-d",strtotime($this->request->query['to_date']));
			$endDate = date('Y-m-d', strtotime($to_date.'+1 day'));
		}
        $conditionArr1 = array();
        //pr($this->request->query);die;
		if(array_key_exists('category',$this->request->query)&& !empty($this->request->query['category'][0])){
			$conditionArr1['category_id IN'] = $this->request->query['category'];
			$productIdData_query = $this->Products->find('all',array('conditions'=>$conditionArr1,'fields'=>array('id'),'recursive'=>-1,'order'=>'Products.id DESC'));
            $productIdData_query->hydrate(false);
            if(!empty($productIdData_query)){
             $productIdData  = $productIdData_query->toArray();
            }else{
                $productIdData = array();
            }
			$ids = array();
			foreach($productIdData as $a => $val){
                //pr($val);die;
				$ids[] = $val['id'];
			}
            if(!empty($ids)){
                $conditionArr['product_id IN'] = $ids;
            }	
		}
		if(!empty($this->request->query['search_kw'])){
			$search_KW = $this->request->query['search_kw'];
			//$conditionArr['AND']['invoice_reference'] = $search_KW;
		}
		
		if(!empty($this->request->query['search_rcit'])){
			$search_RCT = $this->request->query['search_rcit'];
			$conditionArr['AND']['product_receipt_id IN'] = $search_RCT;
		}
		
		if(!empty($search_KW)){
			$productSearch_query = $this->Products->find('all',array('conditions'=>array(
								'OR'	=> array(
								'LOWER(Products.product_code) like' => "%$search_KW%",
								'LOWER(Products.product) like' => "%$search_KW%",
									)
								),
								'fields' => array('id'),
								'recursive'=>-1
							)
					     );
            $productSearch_query->hydrate(false);
            if(!empty($productSearch_query)){
                $productSearch = $productSearch_query->toArray();
            }else{
                $productSearch = array();
            }
			$requestedProductIds = array();
			foreach($productSearch as $key=>$productResult){
				$requestedProductIds[] = $productResult['id'];
			}
			//pr($requestedProductIds);die;
			if(!empty($requestedProductIds)){
				$conditionArr[] = array("TransferSurplus.product_id IN"=>$requestedProductIds);
			}
		}
		
		if(!empty($startDate) || !empty($endDate)){
			$conditionArr[] = array("DATE(TransferSurplus.created)>'$startDate'","DATE(TransferSurplus.created)<'$endDate'");
		}
		
		//$this->Paginator->settings = array(
		//									'conditions' => $conditionArr,
		//									'order' => 'TransferSurplus.id desc',
		//									'limit' => 100,
		//									'recursive'=>-1,
		//									);
		//$surplusData = $this->Paginator->paginate('TransferSurplus');
		//pr($conditionArr);die;
		$surplusData_query = $this->TransferSurplus->find('all',array(
												 'conditions' => $conditionArr,
												 'order' => ['TransferSurplus.id desc'],
												 'recursive'=>-1,
												 ));
		$surplusData_query->hydrate(false);
        if(!empty($surplusData_query)){
         $surplusData  = $surplusData_query->toArray();
        }else{
            $surplusData = array();
        }
		$totalCostNSalePrice_query = $this->TransferSurplus->find('all',array('conditions' =>$conditionArr));
        $totalCostNSalePrice_query
                             ->select(['total_cost' => $totalCostNSalePrice_query->func()->sum('cost_price * quantity')])
                            ->select(['total_sale' => $totalCostNSalePrice_query->func()->sum('sale_price * quantity')]);
        $totalCostNSalePrice_query = $totalCostNSalePrice_query->hydrate(false);
        $totalCostNSalePrice = $totalCostNSalePrice_query->first();
		$productArr = array();
		$product_query = $this->Products->find('all',array('fields' =>array('id','product','product_code') ,'recursive'=>-1));
        $product_query->hydrate(false);
        if(!empty($product_query)){
         $product  = $product_query->toArray();
        }else{
            $product = array();
        }
		foreach($product as $key => $value){
			$productArr[$value['id']]['product'] =  $value['product'];
			$productArr[$value['id']]['product_code'] =  $value['product_code'];
		}
		$catData_query = $this->Categories->find('all',array('fields'=>array('id','category'),'recursive'=>-1,'order'=>'Categories.category ASC'));
        $catData_query->hydrate(false);
        if(!empty($catData_query)){
         $catData  = $catData_query->toArray();
        }else{
            $catData = array();
        }
        if(array_key_exists('category_id IN',$conditionArr1)){
          $categories = $this->CustomOptions->category_options($catData,true,$conditionArr1['category_id IN']);  
        }else{
            $categories = $this->CustomOptions->category_options($catData,true);
        }
		//pr($categories);die;
		$searched = 1;
		$catagory_query = $this->Categories->find('list',['keyField'=>'id','valueField' => 'category']);
        $catagory_query->hydrate(false);
        if(!empty($catagory_query)){
         $catagory  = $catagory_query->toArray();
        }else{
            $catagory = array();
        }
        //pr($catagory);die;
		$this->set(compact('surplusData','productArr','catagory','categories','totalCostNSalePrice','searched'));
		$this->render('lost_stock');
	}
    
    public function suspenseStock()
    {
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
            $this->Flash->error(__("This function works only on hpwaheguru"));
			return $this->redirect(array('controller' => 'home','action' => "dashboard"));die;
		}
		$this->paginate = 
                             ['order' => ['TransferUnderstock.id desc'],
										   'limit' => 100
							  ];
		$suspenseData = $this->paginate($this->TransferUnderstock);
		
        $totalCostNSalePrice_query = $this->TransferUnderstock->find('all');
        $totalCostNSalePrice_query
                            ->select(['total_cost' => $totalCostNSalePrice_query->func()->sum('cost_price * quantity')])
                            ->select(['total_sale' => $totalCostNSalePrice_query->func()->sum('sale_price * quantity')]);
        $totalCostNSalePrice_query = $totalCostNSalePrice_query->hydrate(false);
        $totalCostNSalePrice = $totalCostNSalePrice_query->first();
        //pr($totalCostNSalePrice);die;
		//$totalCostNSalePrice = $this->TransferUnderstock->find('first',array('fields' => array("SUM(`cost_price`*`quantity`) as total_cost,SUM(`sale_price`*`quantity`) as total_sale"),'recursive'=>-1));
		
		$productArr = array();
		$product_query = $this->Products->find('all',array('fields' =>array('id','product','product_code') ,'recursive'=>-1));
        $product_query->hydrate(false);
        if(!empty($product_query)){
         $product  = $product_query->toArray();
        }else{
            $product = array();
        }
		foreach($product as $key => $value){
			$productArr[$value['id']]['product'] =  $value['product'];
			$productArr[$value['id']]['product_code'] =  $value['product_code'];
		}
		
		$catData_query = $this->Categories->find('all',array('fields'=>array('id','category'),'recursive'=>-1,'order'=>'Categories.category ASC'));
        $catData_query->hydrate(false);
        if(!empty($catData_query)){
         $catData  = $catData_query->toArray();
        }else{
            $catData = array();
        }
		$categories = $this->CustomOptions->category_options($catData,true);
		
		$catagory_query = $this->Categories->find('list',['keyField'=>'id','valueField' => 'category']);
        $catagory_query->hydrate(false);
        if(!empty($catagory_query)){
         $catagory  = $catagory_query->toArray();
        }else{
            $catagory = array();
        }
		$searched = 0;
		$this->set(compact('suspenseData','productArr','catagory','categories','totalCostNSalePrice','searched','sites'));
    }
    
    public function searchSuspenseStock()
    {
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
            $this->Flash->error(__("This function works only on hpwaheguru"));
			return $this->redirect(array('controller' => 'home','action' => "dashboard"));die;
		}
		$conditionArr = array();
		$endDate = $startDate = $from_date = $to_date = $search_KW = "";
		if(!empty($this->request->query['from_date'])){
			$from_date = date("Y-m-d",strtotime($this->request->query['from_date']));
			$startDate = date('Y-m-d', strtotime($from_date.'-1 day'));
		}
		if(!empty($this->request->query['to_date'])){
			$to_date = date("Y-m-d",strtotime($this->request->query['to_date']));
			$endDate = date('Y-m-d', strtotime($to_date.'+1 day'));
		}
		$conditionArr1 = array();
		if(array_key_exists('category',$this->request->query)&& !empty($this->request->query['category'][0])){
            //echo'hi';die;
			$conditionArr1['category_id IN'] = $this->request->query['category'];
			$productIdData_query = $this->Products->find('all',array('conditions'=>$conditionArr1,'fields'=>array('id'),'recursive'=>-1,'order'=>'Products.id DESC'));
            $productIdData_query->hydrate(false);
            if(!empty($productIdData_query)){
             $productIdData  = $productIdData_query->toArray();
            }else{
                $productIdData = array();
            }
			$ids = array();
			foreach($productIdData as $a => $val){
				$ids[] = $val['id'];
			}
            if(!empty($ids)){
                $conditionArr['product_id IN'] = $ids;
            }else{
                $conditionArr['product_id IN'] = array(0 => null);
            }
		}
		$site_id = 0;
		if(array_key_exists('site',$this->request->query)){
		  $site_id = $this->request->query['site'];
		  $conditionArr['site_id'] = $site_id;
		}
		
		//pr($conditionArr1);die;
		 $selectedCategoryId=array();
		if(array_key_exists('category_id IN',$conditionArr1)){
			$selectedCategoryId = $conditionArr1['category_id IN'];
		}
		//pr($selectedCategoryId);
		if(!empty($this->request->query['search_kw'])){
			$search_KW = $this->request->query['search_kw'];
			//$conditionArr['AND']['invoice_reference'] = $search_KW;
		}
		
		if(!empty($search_KW)){
			$productSearch_query = $this->Products->find('all',array('conditions'=>array(
								'OR'	=> array(
								'LOWER(Products.product_code) like' => "%$search_KW%",
								'LOWER(Products.product) like' => "%$search_KW%",
									)
								),
								'fields' => array('id'),
								'recursive'=>-1
							)
					     );
            $productSearch_query->hydrate(false);
            if(!empty($productSearch_query)){
             $productSearch  = $productSearch_query->toArray();
            }else{
                $productSearch = array();
            }
			$requestedProductIds = array();
			foreach($productSearch as $key=>$productResult){
				$requestedProductIds[] = $productResult['id'];
			}
			
			if(!empty($requestedProductIds)){
				$conditionArr[] = array("TransferUnderstock.product_id IN"=>$requestedProductIds);
			}
		}
		
		
		if(!empty($startDate) || !empty($endDate)){
			$conditionArr[] = array("DATE(TransferUnderstock.created)>'$startDate'","DATE(TransferUnderstock.created)<'$endDate'");
		}
        //pr($conditionArr);die;
		$suspenseData_query = $this->TransferUnderstock->find('all',array(
													'conditions' => $conditionArr,
													'order' => 'TransferUnderstock.id desc',
													'recursive'=>-1,
													));
		$suspenseData_query->hydrate(false);
        if(!empty($suspenseData_query)){
         $suspenseData  = $suspenseData_query->toArray();
        }else{
            $suspenseData = array();
        }
		
        $totalCostNSalePrice_query = $this->TransferUnderstock->find('all',array('conditions' =>$conditionArr));
        $totalCostNSalePrice_query
                            ->select(['total_cost' => $totalCostNSalePrice_query->func()->sum('cost_price * quantity')])
                            ->select(['total_sale' => $totalCostNSalePrice_query->func()->sum('sale_price * quantity')]);
        $totalCostNSalePrice_query = $totalCostNSalePrice_query->hydrate(false);
        $totalCostNSalePrice = $totalCostNSalePrice_query->first();
		$productArr = array();
		$product_query = $this->Products->find('all',array('fields' =>array('id','product','product_code') ,'recursive'=>-1));
        $product_query->hydrate(false);
        if(!empty($product_query)){
         $product  = $product_query->toArray();
        }else{
            $product = array();
        }
		foreach($product as $key => $value){
			$productArr[$value['id']]['product'] =  $value['product'];
			$productArr[$value['id']]['product_code'] =  $value['product_code'];
		}
		
		$catData_query = $this->Categories->find('all',array('fields'=>array('id','category'),'recursive'=>-1,'order' => 'Categories.category asc',));
        $catData_query->hydrate(false);
        if(!empty($catData_query)){
         $catData  = $catData_query->toArray();
        }else{
            $catData = array();
        }
        //pr($selectedCategoryId);die;
		$categories = $this->CustomOptions->category_options($catData,true,$selectedCategoryId);
		//pr($categories);die;
		$searched = 1;
		$catagory_query = $this->Categories->find('list',['keyField'=>'id','valueField' => 'category']);
        $catagory_query->hydrate(false);
        if(!empty($catagory_query)){
         $catagory  = $catagory_query->toArray();
        }else{
            $catagory = array();
        }
		$this->set(compact('suspenseData','productArr','catagory','categories','totalCostNSalePrice','searched','sites'));
		$this->render('suspense_stock');
	}
    
    public function dispatchedProducts()//need to check sum queries.....
    {
        $today = date("Y-m-d");
		$startDate = date("Y-m-d",strtotime($today."-1 day"));
		$endDate = date("Y-m-d",strtotime($today."+1 day"));
		 $query = $this->StockTransfer->find('all',array(
                                                         'conditions' =>[
                                                                         "DATE(StockTransfer.created)>'$startDate'",
                                                                         "DATE(StockTransfer.created)<'$endDate'"
                                                                         ],
                                                            'group' => 'StockTransfer.product_id',
                                                            'order' => 'StockTransfer.id DESC'
                                                            ));
                  $query
                           ->select(['totalquantity' => $query->func()->sum('StockTransfer.quantity')])
                          ->select(['product_id','created']);   
                          //->toArray();
		
		 
		//$qty_res = $this->StockTransfer->find('all',array(
		//						'conditions' => array("DATE(StockTransfer.created)>'$startDate'","DATE(StockTransfer.created)<'$endDate'"),
		//						'fields' => array('product_id','SUM(StockTransfer.quantity) as totalquantity','created'),
		//						'recursive' => -1,
		//						'order' => 'StockTransfer.id DESC',
		//						'group' => 'StockTransfer.product_id'
		//									   ));
		$qty_res = array();
		$final_qty = 0;
		 //pr($qty_res);
		foreach($qty_res as $s_key => $k_value){
			$final_qty += $k_value->totalquantity;
		}
		$dispatchedProducts = $this->paginate($query);
        if(!empty($dispatchedProducts)){
            $dispatchedProducts = $dispatchedProducts->toArray(); 
        }
		$productIdArr = array();
		foreach($dispatchedProducts as $key=>$dispatchedProduct){
			$productIdArr[] = $dispatchedProduct->product_id;
		}
		if(empty($productIdArr)){
            $productIdArr = array(0=>null);
        }
		
		
		$productData = $this->Products->find('all',array('conditions'=>array('Products.id IN'=>$productIdArr),'fields'=>array('id','product_code','product','image','category_id'),'recursive'=>-1,'order'=>'Products.id DESC'));
        $productData->hydrate(false);
        if(!empty($productData)){
         $productData  = $productData->toArray();
        }else{
            $productData = array();
        }
		$productIdDetail = array();
		foreach($productData as $productInfo){
			$productIdDetail[$productInfo['id']] = $productInfo;
		}
        $categories = $this->Categories->find('all',[
                                                        'keyField' => 'id',
                                                        'valueField' => 'category',
                                                        'order' => 'Categories.id asc' 
                                                       
                                                    ]);
        $categories = $categories->hydrate(false);
        if(!empty($categoryData)){
         $category  = $categoryData->toArray();
        }else{
            $category = array();
        }
         $Category_query = $this->Categories->find('all',array(
								'fields' => array('id', 'category','id_name_path'),
								'conditions' => array('Categories.status' => 1),
								'order' => 'Categories.category asc' 
								));
          $Category_query = $Category_query->hydrate(false);
        if(!empty($Category_query)){
              $categoryData = $Category_query->toArray();
        }
		$categories = $this->CustomOptions->category_options($categoryData,true);
		$categoryArr = array();
       // pr($categoryData);
		foreach($categoryData as $k => $value){
			$categoryArr[$value['id']] =  $value['category'];
		}
		$hint = $this->ScreenHint->hint('stock_transfer','dispatched_products');
			if(!$hint){
				$hint = "";
			}
		 
		$this->set(compact('categories','categoryArr','hint','dispatchedProducts','productIdDetail','from_date','to_date','final_qty'));
    }
    
    public function searchDispatched(){
		
		//pr($this->request);die;
		$startDate = '';
		$from_date = '';
		
		if(!empty($this->request->query['from_date'])){
			$from_date = date("Y-m-d",strtotime($this->request->query['from_date']));
			$startDate = date('Y-m-d', strtotime($from_date.'-1 day'));
		}
		
		$endDate = '';
		$to_date = '';
		if(!empty($this->request->query['to_date'])){
			$to_date = date("Y-m-d",strtotime($this->request->query['to_date']));
			$endDate = date('Y-m-d', strtotime($to_date.'+1 day'));
		}
		
		$search_kw = '';
		if(!empty($this->request->query['search_kw'])){
			$search_kw = $this->request->query['search_kw'];
		}
		
		$conditionArr = array();
		$conditionArr1 = array();
		if(!empty($startDate) || !empty($endDate)){
			$conditionArr[] = array("DATE(StockTransfer.created)>'$startDate'","DATE(StockTransfer.created)<'$endDate'");
		}
        //pr($this->request->query);die;
        //pr($conditionArr1);die;
        $selectedCategoryId = array();
        
		if(array_key_exists('category',$this->request->query)&& !empty($this->request->query['category'][0])){
           // pr($this->request->query);die;
			$conditionArr1['category_id IN'] = $this->request->query['category'];
            //pr($conditionArr1);die;
            if(array_key_exists('category_id IN',$conditionArr1)){
                $selectedCategoryId = $conditionArr1['category_id IN'];
            }
            $productIdData_query = $this->Products->find('all',[
                                                                'conditions'=>[$conditionArr1],
                                                                'keyField' => 'id',
                                                                
                                                               'order'=>'Products.id DESC'
                                                               ]);
            
            if(!empty($productIdData_query)){
                $productIdData = $productIdData_query->toArray();
            }else{
                $productIdData = array();
            }
          	$ids = array();
			foreach($productIdData as $a => $val){
				$ids[] = $val['id'];
			}
            if(empty($ids)){
                $ids = array('0'=>null);
            }
			$conditionArr['product_id IN'] = $ids;
		}
		if(!empty($search_kw)){
			$productSearch_query = $this->Products->find('all',array('conditions'=>array(
								'OR'	=> array(
								'LOWER(Products.product_code) like' => "%$search_kw%",
								'LOWER(Products.product) like' => "%$search_kw%",
									)
								),
								'fields' => array('id')
							)
					     );
            $productSearch_query = $productSearch_query->hydrate(false);
            if(!empty($productSearch_query)){
                $productSearch = $productSearch_query->toArray();
            }
           //pr($productSearch);die;
			$requestedProductIds = array();
			foreach($productSearch as $key=>$productResult){
				$requestedProductIds[] = $productResult['id'];
			}
            if(empty($requestedProductIds)){
                $requestedProductIds = array('0' =>null);
            }
			
			if(!empty($requestedProductIds)){
				$conditionArr[] = array("product_id IN"=>$requestedProductIds);
			}
		}
		//pr($conditionArr);die;
		if(array_key_exists('forprint',$this->request->query) && $this->request->query['forprint'] == 'Yes'){
			$limit = 1000;
		}else{
			$limit = 20;
		}
		 $query = $this->StockTransfer->find('all',array(
                                                         'conditions' =>[
                                                                        $conditionArr
                                                                         ],
                                                            'group' => 'StockTransfer.product_id',
                                                            'order' => 'StockTransfer.id DESC'
                                                            ));
                  $query
                           ->select(['totalquantity' => $query->func()->sum('StockTransfer.quantity')])
                          ->select(['product_id','created']);   
		 
		$dispatchedProducts = $this->paginate($query);
        if(!empty($dispatchedProducts)){
            $dispatchedProducts = $dispatchedProducts->toArray();
        }else{
            $dispatchedProducts = array();
        }
    //    pr($dispatchedProducts);
		 $qty_res_query = $this->StockTransfer->find('all',array(
                                                         'conditions' =>[
                                                                        $conditionArr
                                                                         ],
                                                            'group' => 'StockTransfer.product_id',
                                                            'order' => 'StockTransfer.id DESC'
                                                            ));
                  $qty_res_query
                           ->select(['totalquantity' => $query->func()->sum('StockTransfer.quantity')])
                          ->select(['product_id','created']);
                          if(!empty($qty_res_query)){
                            $qty_res = $qty_res_query->toArray();
                          }else{
                            $qty_res = array(); 
                          }
		//$qty_res = $this->StockTransfer->find('all',array(
		//						'conditions' => $conditionArr,
		//						'fields' => array('product_id','SUM(StockTransfer.quantity) as totalquantity','created'),
		//						'recursive' => -1,
		//						'order' => 'StockTransfer.id DESC',
		//						'group' => 'StockTransfer.product_id'
		//									   ));
		
		$final_qty = 0;
       // pr($qty_res);
		foreach($qty_res as $s_key => $k_value){
			$final_qty += $k_value->totalquantity;
		}
	//pr($final_qty);
		//$final_qty = 0;
		$productIdArr = array();
		foreach($dispatchedProducts as $key=>$dispatchedProduct){
			$productIdArr[] = $dispatchedProduct->product_id;
			//$final_qty += $dispatchedProduct[0]['totalquantity'];
		}
        //pr($productIdArr); 
        if(empty($productIdArr)){
            $productIdArr = array('0'=>null);
        }
		 $productData_query = $this->Products->find('all',array(
                                                         'conditions'=>array('Products.id IN '=>$productIdArr),
                                                         'fields'=>array('id','product_code','product','image','category_id'),
                                                          'order'=>'Products.id DESC'
                                                         )
                                             );
      
        $productData_query = $productData_query->hydrate(false);
        if(!empty($productData_query)){
         $productData = $productData_query->toArray();
        }else{
            $productData = array();
        }
		$productIdDetail = array();
         //  pr($productData);         
		foreach($productData as $productInfo){
          
			$productIdDetail[$productInfo['id']] = $productInfo;
		}
        $categories = $this->Categories->find('all',[
                                                        'keyField' => 'id',
                                                        'valueField' => 'category',
                                                        'order' => 'Categories.id asc' 
                                                       
                                                    ]);
        $categories = $categories->hydrate(false);
        if(!empty($categoryData)){
         $category  = $categoryData->toArray();
        }else{
            $category = array();
        }
         $Category_query = $this->Categories->find('all',array(
								'fields' => array('id', 'category','id_name_path'),
								'conditions' => array('Categories.status' => 1),
								'order' => 'Categories.category asc' 
								));
        if(!empty($Category_query)){
              $categoryData = $Category_query->toArray();
        }
        //pr($productIdArr);die;
		$categories = $this->CustomOptions->category_options($categoryData,true,$selectedCategoryId);
        //pr($categories);
		$categoryArr = array();
		foreach($categoryData as $k => $value){
			$categoryArr[$value['id']] =  $value['category'];
		}
		$hint = $this->ScreenHint->hint('stock_transfer','dispatched_products');
        if(!$hint){
            $hint = "";
        }
		//echo $categories;die;
		$this->set(compact('hint','dispatchedProducts','productIdDetail','categoryArr','categories','final_qty'));
		$this->render('dispatched_products');
	}
    public function viewStockTransferByKiosk($id = '')
    {
        //echo $id;die;
        $receiptTable_source = "center_orders";
        $KioskOrderTable = TableRegistry::get($receiptTable_source,[
                                                                    'table' => $receiptTable_source,
                                                                ]);
			//Configure::load('common-arrays');
			$disputeOptions = Configure::read('dispute');
			$kiosks = $this->Kiosks->find('list',
                                                [
                                                'keyField' => 'id',
                                                'valueField' => 'code',
                                                'conditions' => ['Kiosks.status' => 1]
                                                ]);
            $kiosks = $kiosks->toArray();
			$users = $this->Users->find('list',
                                        [
                                            'keyField' => 'id',
                                            'valueField' => 'username'
                                        ]);
            $users = $users->toArray();
            $receiptTable_source = "stock_transfer_by_kiosk";
            $StockTransferTable = TableRegistry::get($receiptTable_source,[
                                                                    'table' => $receiptTable_source,
                                                                ]);
			//$this->StockTransfer->setSource('stock_transfer_by_kiosk');
			//$options = ['conditions' => ['stock_transfer_by_kiosk.kiosk_order_id' => $id]];
			$kioskOrderStatusDetail_query = $KioskOrderTable->find('all',
                                                                   [
                                                                        'conditions'=>['center_orders.id'=>$id],                                                                        'fields'=>['id','status'],//'recursive'=>-1
                                                                    ]);
             //pr($kioskOrderStatusDetail_query);die;
            $kioskOrderStatusDetail = $kioskOrderStatusDetail_query->first();
           
            if(!empty($kioskOrderStatusDetail)){
                $kioskOrderStatusDetail = $kioskOrderStatusDetail->toArray();
            }
            
			$kioskOrderStatus = $kioskOrderStatusDetail['status'];
			//echo $kioskOrderStatus;
			$products = $StockTransferTable->find('all',[
                                                  'contain' => ['center_orders','Products'],
                                                  'conditions' => ['stock_transfer_by_kiosk.kiosk_order_id' => $id]
                                                  //$options
                                                  ]);
            
            $products->hydrate(false);
            
            if(!empty($products)){
                $products = $products->toArray();
            }
            //pr($products);die;
			if(!array_key_exists('0',$products)){
				$this->Flash->error("No data found!");
				return $this->redirect(array('action' => 'transient_kiosk_orders', 'controller' => 'kiosk_orders'));
			}
			$this->set(compact('users','disputeOptions','kiosks'));
			$this->set('products', $products);
			$this->set('kioskOrderStatus', $kioskOrderStatus);
    }
    public function view($id = null){
      
        $disputeOptions = Configure::read('dispute');
        $kiosk_query = $this->Kiosks->find('list',
                                      [
										'conditions'=>['Kiosks.status'=>1],
                                       'keyField' => 'id',
                                        'valueField' => 'name',
                                        'order' => 'Kiosks.name asc'
                                    ]);
        if(!empty($kiosk_query)){
            $allKiosks = $kiosk_query->toArray();    
        }else{
            $allKiosks = array();
        }
        
         $kiosklist_query = $this->Kiosks->find('list',
                                      [
                                        'conditions'=>['Kiosks.status'=>1],
                                        'keyField' => 'id',
                                        'valueField' => 'name',
                                        'order' => 'Kiosks.name asc'
                                    ]);
		 //pr($kiosklist_query);die;
        if(!empty($kiosklist_query)){
            $kiosks = $kiosklist_query->toArray();    
        }else{
             $kiosks = array();
        }
		//pr($kiosks);die;
		$kioskOrderStatusDetail_query = $this->KioskOrders->find('all',array(
                                                                       'conditions'=>array('KioskOrders.id'=>$id),
                                                                       'fields'=>array('id','status','kiosk_placed_order_id','is_on_demand')));
        $kioskOrderStatusDetail_result = $kioskOrderStatusDetail_query->first();
        if(!empty($kioskOrderStatusDetail_result)){
                $kioskOrderStatusDetail  = $kioskOrderStatusDetail_result->toArray();
        }else{
            $kioskOrderStatusDetail = array();
        }
        $kioskOrderStatusDetail_query = $this->KioskOrders->find('all',array(
                                                                        'conditions'=>array(
                                                                                            'KioskOrders.id'=>$id
                                                                                            ),
                                                                        'fields'=>array('id','status','kiosk_placed_order_id','is_on_demand')
                                                                        )
                                                           );
        $kioskOrderStatusDetail_result = $kioskOrderStatusDetail_query->first();
        if(!empty($kioskOrderStatusDetail_result)){
            $kioskOrderStatusDetail  = $kioskOrderStatusDetail_result->toArray();
        }else{
            $kioskOrderStatusDetail = array();
        }
		$kiosk_placed_order_id = $kioskOrderStatusDetail['kiosk_placed_order_id'];
		$is_on_demand = $kioskOrderStatusDetail['is_on_demand'];
		$requestedTime = "";
        $clone_order = 0;
		
        if((int)$kiosk_placed_order_id){
           if($is_on_demand == 1){
                   $kiosk_placed_user_id_query = $this->OnDemandOrders->find('list',[
                                    'conditions' => array('id' => $kiosk_placed_order_id),
                                    'keyField' => 'id',
                                     'valueField' => 'user_id'
                    ]);
                    if(!empty($kiosk_placed_user_id_query)){
                       $kiosk_placed_user_id = $kiosk_placed_user_id_query->toArray();
                   }else{
                        $kiosk_placed_user_id = array();
                    }	     
					//pr($kiosk_placed_user_id);die;
                    $kioskOrderProduct_query = $this->OnDemandProducts->find('all',[
                                    'conditions' => ['OnDemandProducts.kiosk_placed_order_id'=>$kiosk_placed_order_id]
                                    
                    ]);
                    $kioskOrderProduct_query = $kioskOrderProduct_query->hydrate(false);
                    if(!empty($kioskOrderProduct_query)){
                       $kioskOrderProduct = $kioskOrderProduct_query->toArray();
                   }else{
                        $kioskOrderProduct = array();
                    }	     
					
					// pr($kioskOrderProduct);
					if(array_key_exists(0,$kioskOrderProduct)){
						//$requestedTime = $kioskOrderProduct[0]['created'];
                        $requestedTime_arr = $kioskOrderProduct[0]['created'];
                        //$modified = $OnDemandOrder['modified'];
                        if(!empty($requestedTime_arr)){
                             $requestedTime_arr->i18nFormat(
                                                                [\IntlDateFormatter::FULL, \IntlDateFormatter::FULL]
                                                        );
							$requestedTime =  $requestedTime_arr->i18nFormat('dd-MM-yyyy HH:mm:ss');
                            $requestedTime = date("d-m-y h:i a",strtotime($requestedTime)); 
                        }else{
                            $requestedTime = "--";
                        }
					}
					
					$quantityRequestedArr = array();
					foreach($kioskOrderProduct as $key => $kioskOrderProducts){
						$quantityRequestedArr[$kioskOrderProducts['product_id']]=$kioskOrderProducts['org_qty'];
					}
					
					
					
					$kioskOrderProductremarks = array();
					foreach($kioskOrderProduct as $key => $kioskOrderProducts){
						$kioskOrderProductremarks[$kioskOrderProducts['product_id']]=$kioskOrderProducts['remarks'];
					}
					//-----------------------------------------
					$statusArr = array();
					foreach($kioskOrderProduct as $kioskOrderProducts){
						$statusArr[$kioskOrderProducts['product_id']] = $kioskOrderProducts['status'];
					}
					$merge_data = array();
				}else{
					//pr($kiosk_placed_order_id);die;
					
					$kiosk_placed_merge_data = $this->KioskPlacedOrders->find('all',array(
																					'conditions' => array('id' => $kiosk_placed_order_id),
																				  ))->toArray();
					$merge_data = array();
					if(!empty($kiosk_placed_merge_data)){
						 
						 $merged = $kiosk_placed_merge_data[0]->merged;
						 $merge_data['merged'] = $merged;
						 if($merged == 1){
							  $merge_data1 = $kiosk_placed_merge_data[0]->merge_data;
							  $merge_data['merge_data'] = $merge_data1;
						 }
						 
					}
					
					$kiosk_placed_user_id_query = $this->KioskPlacedOrders->find('list',array(
																					'conditions' => array('id' => $kiosk_placed_order_id),
																					'keyField' =>'id',
																					'valueField' => 'user_id',
																				  ));
					$kiosk_placed_user_id_query  = $kiosk_placed_user_id_query->hydrate(false);
					if(!empty($kiosk_placed_user_id_query)){
						 $kiosk_placed_user_id = $kiosk_placed_user_id_query->toArray();
					}else{
						 $kiosk_placed_user_id = array();
					}
					
					$kioskOrderProduct_query = $this->KioskOrderProducts->find('all',
												array('conditions' => array('KioskOrderProducts.kiosk_placed_order_id IN'=>$kiosk_placed_order_id)
													  )
																		);
					$kioskOrderProduct_query = $kioskOrderProduct_query->hydrate(false);
					if(!empty($kioskOrderProduct_query)){
						 $kioskOrderProduct = $kioskOrderProduct_query->toArray();
					}else{
						$kioskOrderProduct = array(); 
					}
					
					 //pr($kioskOrderProduct);
					if(array_key_exists(0,$kioskOrderProduct)){
						$requestedTime_arr = $kioskOrderProduct[0]['created'];
                        //$modified = $OnDemandOrder['modified'];
                        if(!empty($requestedTime_arr)){
                             $requestedTime_arr->i18nFormat(
                                                                [\IntlDateFormatter::FULL, \IntlDateFormatter::FULL]
                                                        );
							$requestedTime =  $requestedTime_arr->i18nFormat('dd-MM-yyyy HH:mm:ss');
                            $requestedTime = date("d-m-y h:i a",strtotime($requestedTime)); 
                        }else{
                            $requestedTime = "--";
                        }
					}
					
					$quantityRequestedArr = array();
					foreach($kioskOrderProduct as $key => $kioskOrderProducts){
						$quantityRequestedArr[$kioskOrderProducts['product_id']]=$kioskOrderProducts['org_qty'];
					}
					
					
					
					$kioskOrderProductremarks = array();
					foreach($kioskOrderProduct as $key => $kioskOrderProducts){
						$kioskOrderProductremarks[$kioskOrderProducts['product_id']]=$kioskOrderProducts['remarks'];
					}
					//-----------------------------------------
					$statusArr = array();
					foreach($kioskOrderProduct as $kioskOrderProducts){
						$statusArr[$kioskOrderProducts['product_id']] = $kioskOrderProducts['status'];
					}
				}
				//pr($statusArr);die;
				//-----------------------------------------
			}else{
				$clone_order = 1;
				$kioskOrderProductremarks = array();
			}
	 
			$kioskOrderStatus = $kioskOrderStatusDetail['status'];
            $users_query = $this->Users->find('list',
                                    [
                                        'keyField' => 'id',
                                        'valueField' => 'username',
                                    ]);
            if(!empty($users_query)){
                 $users = $users_query->toArray();
            }
           
			$options = array('conditions' => ['StockTransfer.kiosk_order_id' => $id], 'contain' => ['Products','KioskOrders'],
							 'order' => 'StockTransfer.sr_no asc'
							 );
			$products_query = $this->StockTransfer->find('all',
                                                         $options
                                                         );
			//pr($products_query);
            $products_query = $products_query->hydrate(false);
                if(!empty($products_query)){
                    $products = $products_query->toArray();
                }
            //pr($products);die;
		   
			$this->get_cancelled_placed_order_products($kiosk_placed_order_id);
			$this->set(compact('users','disputeOptions','kiosks','kioskOrderStatus','kiosk_placed_order_id','quantityRequestedArr','requestedTime','kioskOrderProductremarks','kioskOrderProduct','allKiosks','kiosk_placed_user_id', 'statusArr','clone_order','merge_data'));
			$this->set('products', $products);
    }
    private function get_cancelled_placed_order_products($kiosk_placed_order_id = 0){
		 $cancelledProds_query = $this->KioskCancelledOrderProducts->find('all', array(
													   'conditions' => array('kiosk_placed_order_id' => $kiosk_placed_order_id),
													//   'recursive' => -1,
													   )); 
         $cancelledProds_query = $cancelledProds_query->hydrate(false);
        if(!empty($cancelledProds_query)){
            $cancelledProds = $cancelledProds_query->toArray();
        }else{
            $cancelledProds = array();
        }
        
		$productIDs = array();
        if(!empty($cancelledProds)){
            foreach($cancelledProds as $cancelledProd){
                $productIDs[] = $cancelledProd['product_id'];
            }
        }
		
		//pr($productIDs);die;
        if(empty($productIDs)){
            $productIDs = array(0 => null);
        }
		$tProds_query = $this->Products->find('all', array(
															 'conditions' => array('id IN' => $productIDs),
															 'fields' => array('id','product_code','product','image'),
															// 'recursive' => -1
															 )
												);
        $tProds_query = $tProds_query->hydrate(false);
        if(!empty($tProds_query)){
            $tProds = $tProds_query->toArray();
        }
		foreach($tProds as $tProd){
			$id = $tProd['id'];
			$infoCancelProds[$id] = array($tProd['product_code'], $tProd['product'],$tProd['image']);
		}
		//pr($infoCancelProds);die;
		$this->set(compact('cancelledProds','infoCancelProds'));
		return $cancelledProds;
	}
	
	public function index() {
		$this->increase_execution_time();
		//$displayType = "more_than_zero";
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
		if(!empty($this->request->query)){
		  if(array_key_exists('display_type',$this->request->query)){
			 $displayType = $this->request->query['display_type'];  
		  }else{
			   $displayType = "more_than_zero";
		  }
			
			$queryStr = $this->searchUrl();
			$categories = $queryStr[0];
			$conditionArr = $queryStr[1];
			$this->Products->find('all',array('conditions' => $conditionArr));
			$this->paginate = [
							   'limit' => 20,
							   'conditions' => $conditionArr];
			
		}elseif ($this->request->is(array('get', 'put'))) {
			if(array_key_exists('display_type',$this->request->query)){
				$displayType = $this->request->query['display_type'];
				if($displayType=="show_all"){
					$this->paginate = ['limit' => ROWS_PER_PAGE ,'order' => ['Products.id desc']];
				}elseif($displayType=="more_than_zero"){
					$this->paginate = ['limit' => ROWS_PER_PAGE,
												 'conditions'=>['NOT'=>['Products.quantity'=>0]],
												 'order' => ['Products.id desc']
										];
				}
			}else{
				$this->paginate = [
								   'limit' => ROWS_PER_PAGE,
								   'conditions'=>['NOT'=>['Products.quantity'=>0]],
								   'order' => ['Products.id desc']
								   ];
			}
		}
		$tKioskID = $this->request->Session()->read("tKioskID");
		if( !empty($tKioskID)){
			$this->request->Session()->write('kioskId', $tKioskID);
		}
		$session_basket = $this->request->Session()->read('Basket');
		$basketStrDetail = '';
		if(is_array($session_basket)){
			$productCodeArr = array();
			foreach($session_basket as $key => $basketItem){
					$product_query = $this->Products->find('all',
															  array('conditions'=>array('Products.id IN'=>$key),
																	'fields'=>array('id','product_code'))
															  );
					$product_query = $product_query->hydrate(false);
					$productCodeArr[] = $product_query->first();
			}
			$productCode = array();
			if(!empty($productCodeArr)){
				foreach($productCodeArr as $k=>$productCodeData){
					$productCode[$productCodeData['id']]=$productCodeData['product_code'];
				}
			}
			
			foreach($session_basket as $productId=>$productDetails){
			    if($productId == "position")continue;
				$productName_query = $this->Products->find('all',array('conditions'=>array('Products.id IN'=>$productId),
																 'fields'=>array('id','product'))
													 );
				$productName_query = $productName_query->hydrate(false);
				$productName = $productName_query->first();
				
				$basketStrDetail.= "<tr>
				<td>".$productCode[$productId]."</td>
				<td>".$productName['product']."</td>
				<td>".$productDetails['price']."</td>
				<td>".$productDetails['quantity']."</td>
				<td>".$productDetails['remarks']."</td>
				</tr>";
			}
			
		}
		
			if(!empty($basketStrDetail)){
				$basketStr = "<table>
				<tr>
					<th style='width: 152px;'>Product code</th>
					<th>Product</th>
					<th style='width: 58px;'>New price</th>
					<th style='width: 31px;'>Qty</th>
					<th style='width: 94px;'>Remarks</th>
				</tr>".$basketStrDetail."
				</table>";
			}
			
			$totalItems = count($session_basket);
			if($totalItems){
				$flashMessage = "Total item Count:$totalItems<br/>$basketStr";
				$this->Flash->success($flashMessage,array('escape' => false));
			}
			
		//$this->Paginator->settings = array('limit' => ROWS_PER_PAGE);
		//pr($this->paginate);die;
		$centralStocks_query = $this->paginate("Products");
		
		$centralStocks = $centralStocks_query->toArray();
		$categoryIdArr = array();
		foreach($centralStocks as $key=>$centralStock){
			$categoryIdArr[] = $centralStock->category_id;
		}
		$categoryName_query = $this->Categories->find('list',
													   [
															'keyField' => 'id',
															'valueField' => 'category',
															'conditions'=>['Categories.id IN'=>$categoryIdArr]
													   ]);
		
        $categoryName_query = $categoryName_query->hydrate(false);
        if(!empty($categoryName_query)){
            $categoryName = $categoryName_query->toArray();
        }else{
            $categoryName = array();
        }
        
		$hint = $this->ScreenHint->hint('stock_transfer','index');
        if(!$hint){
            $hint = "";
        }
		$this->set(compact('hint','categories','kiosks','displayType','centralStocks','categoryName'));
		$this -> render('index');
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
			$conditionArr['category_id'] = $this->request->query['category'];
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
	
	public function viewStock($kiosk_id=0){
		
		$kiosks_query = $this->Kiosks->find('list',
											 [
												  'keyField' => 'id',
												  'valueField' => 'name',
												  'conditions' => ['Kiosks.status' => 1, 'Kiosks.id != ' => 10000],
												  'order' => 'Kiosks.name asc',
											 ]
					     );
		$kiosks_query = $kiosks_query->hydrate(false);
		$kiosks = $kiosks_query->toArray();
		//pr($kiosks);die;
		$kiosks['all']="all";
		$this->set(compact('kiosks'));
		
		if(array_key_exists('KioskStock', $this->request['data'])){
			$kiosk_id = $this->request['data']['KioskStock']['kiosk_id'];
		}
		$categories_query = $this->Categories->find('all',array(
								'fields' => array('id', 'category','id_name_path'),
								'conditions' => array('Categories.status' => 1),
								'order' => 'Categories.category asc',
								));
		$categories_query = $categories_query->hydrate(false);
		$categories = $categories_query->toArray();
		
		$categories = $this->CustomOptions->category_options($categories,true);
		$hint = $this->ScreenHint->hint('stock_transfer','view_stock');
			if(!$hint){
				$hint = "";
			}
		$this->set(compact('hint','categories','kiosk_id'));
		
		if((int)$kiosk_id){
		   $productsTable = TableRegistry::get("kiosk_{$kiosk_id}_products",[
                                                                                                'table' => "kiosk_{$kiosk_id}_products",
                                                                                                    ]);
			$productCostPrice_query = $productsTable->find('all',array('fields'=>array('id','cost_price','quantity'),'recursive'=>-1));
			$productCostPrice_query = $productCostPrice_query->hydrate(false);
			$productCostPrice = $productCostPrice_query->toArray();
			
			$sumTotalCostPrice = 0;
			$productIDs = array();
			foreach($productCostPrice as $key=>$productDetail){
				$productIDs[] = $productDetail['id'];
			}
			//print_r($productIDs);
			$costPriceArr = array();
			foreach($productCostPrice as $key=>$productDetail){
				$productId = $productDetail['id'];
				$costPriceQuery_data = "SELECT `cost_price`,`id` FROM `products` WHERE `id`='$productId'";
			   $conn = ConnectionManager::get('default');
			   $stmt = $conn->execute($costPriceQuery_data);
			   $costPriceQuery = $stmt ->fetchAll('assoc');
			   
				
				if(array_key_exists(0,$costPriceQuery)){
					$costPrice = $costPriceQuery[0]['cost_price'];
					$costPriceArr[$productId]=$costPrice;
					$quantity = $productDetail['quantity'];
					$totalCostPrice = $costPrice*$quantity;
					$sumTotalCostPrice+=$totalCostPrice;
				}
			}
			$this->set(compact('sumTotalCostPrice','costPriceArr'));
			if ($this->request->is(array('get', 'put')) && !empty($this->request->query)) {
				if(array_key_exists('display_type',$this->request->query)){
					$displayType = $this->request->query['display_type'];
					if($displayType=="show_all"){
						$this->paginate = ['limit' => ROWS_PER_PAGE];
					}elseif($displayType=="more_than_zero"){
						$this->paginate = ['limit' => ROWS_PER_PAGE,'conditions'=>['NOT'=>['quantity'=>0]]];
					}
					$this->set(compact('displayType'));
				}	
			}else{
				
				$this->paginate = ['limit' => ROWS_PER_PAGE,'conditions'=>['NOT'=>['quantity'=>0]]];
			}
			//if( $this->request->session()->read('Auth.User.group_id')== MANAGERS){
			//		$managerKiosk = $this->managerLogin();//pr($managerKiosk);die;
			//		if(!empty($managerKiosk)){
			//			$kiosk_id = key($managerKiosk);
			//			if(array_key_exists('conditions',$this->paginate)){
			//				$this->paginate['conditions'] = ['kiosk_id'=> $kiosk_id];
			//			}else{
			//				$this->paginate[] = ['conditions'=>['kiosk_id'=> $kiosk_id]];
			//			}
			//		}
			//}
			//pr($this->paginate);die;
			$products_query  = $this->paginate($productsTable);
			$products = $products_query->toArray();
			$this->set('products', $products);
		}elseif($kiosk_id=="0" && empty($this->request['data']) ||
			$kiosk_id=="0" && array_key_exists('KioskStock',$this->request['data']) ||
			$kiosk_id=="" && array_key_exists('KioskStock',$this->request['data'])
			){
			//$this->Product->setSource("products");
			$productCostPrice_query = $this->Products->find('all',array('fields'=>array('id','cost_price','quantity'),'recursive'=>-1));
			$productCostPrice_query = $productCostPrice_query->hydrate(false);
			$productCostPrice = $productCostPrice_query->toArray();
			$sumTotalCostPrice = 0;
			foreach($productCostPrice as $key=>$productDetail){
				$productId = $productDetail['id'];
				
			   $costPriceQuery_data = "SELECT `cost_price`,`id` FROM `products` WHERE `id`='$productId'";
			   $conn = ConnectionManager::get('default');
			   $stmt = $conn->execute($costPriceQuery_data);
			   $costPriceQuery = $stmt ->fetchAll('assoc');
				$costPrice = $costPriceQuery[0]['cost_price'];
				$quantity = $productDetail['quantity'];
				$totalCostPrice = $costPrice*$quantity;
				$sumTotalCostPrice+=$totalCostPrice;
			}
			$this->set(compact('sumTotalCostPrice'));
			if ($this->request->is(array('get', 'put')) && !empty($this->request->query)) {
				if(array_key_exists('display_type',$this->request->query)){
					$displayType = $this->request->query['display_type'];
					if($displayType=="show_all"){
						$this->paginate = ['limit' => ROWS_PER_PAGE];
					}elseif($displayType=="more_than_zero"){
						$this->paginate = ['limit' => ROWS_PER_PAGE,'conditions'=>['NOT'=>['quantity'=>0]]];
					}
					$this->set(compact('displayType'));
				}	
			}else{
				$this->paginate = ['limit' => ROWS_PER_PAGE,'conditions'=>['NOT'=>['quantity'=>0]]];
			}
			//if( $this->request->session()->read('Auth.User.group_id')== MANAGERS){
			//		$managerKiosk = $this->managerLogin();//pr($managerKiosk);die;
			//		if(!empty($managerKiosk)){
			//			$kiosk_id = key($managerKiosk);
			//			if(array_key_exists('conditions',$this->paginate)){
			//				$this->paginate['conditions'] = ['kiosk_id'=> $kiosk_id];
			//			}else{
			//				$this->paginate[] = ['conditions'=>['kiosk_id'=> $kiosk_id]];
			//			}
			//		}
			//}
			//pr($this->paginate);die;
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
						 $kiosk_table_id = current($managerKiosk);
						 $table = "kiosk_{$kiosk_table_id}_products";
						$productsTable = TableRegistry::get($table,[
                                                                                                'table' => $table,
                                                                                                    ]);
						 $products_query = $this->paginate($productsTable);
					}else{
						 $products_query = $this->paginate("products");
					}
			   }else{
					$products_query = $this->paginate("products");
			   }
			
			$products_query = $this->paginate("products");
			$products = $products_query->toArray();
			$this->set('products', $products);
		}elseif($kiosk_id=="all" && array_key_exists('KioskStock',$this->request['data']) ||
			$kiosk_id=="all" && empty($this->request['data']) && !(int)$kiosk_id
			){
		  
			$sum_quantity = $this->sum_of_quantity();
			//$this->Product->setSource("products");
			
			$products = $this->paginate = ['limit' => ROWS_PER_PAGE];
			//if( $this->request->session()->read('Auth.User.group_id')== MANAGERS){
			//		$managerKiosk = $this->managerLogin();//pr($managerKiosk);die;
			//		if(!empty($managerKiosk)){
			//			$kiosk_id = key($managerKiosk);
			//			$this->paginate[] = ['conditions'=>['kiosk_id'=> $kiosk_id]];
			//		}
			//}
			
			
			$products_query = $this->paginate("products");
			$products = $products_query->toArray();
			$this->set('products', $products);
			$sumTotalCostPrice = $this->total_cost();
			$this->set(compact('sumTotalCostPrice','sum_quantity'));
		}
	}
	
	private function sum_of_quantity(){
		$this->paginate = ['limit' => ROWS_PER_PAGE,'fields'=>['id','quantity']];
		$products_query = $this->paginate("products");
		$products = $products_query->toArray();
		$productArr = array();
		$quantityArr = array();
		foreach($products as $key=>$productDetail){
			$productArr[$productDetail->id]=$productDetail->id;
			$quantityArr[$productDetail->id]=$productDetail->quantity;
		}
		
		$sumArr = array();
		
		$kioskList_query = $this->Kiosks->find('list',
												  [
													   'keyField' => 'id',
													   'valueField' => 'name',
													   'conditions'=>array('Kiosks.status'=>1),
												  ]);
		$kioskList_query = $kioskList_query->hydrate(false);
		$kioskList = $kioskList_query->toArray();
		$counter = 0;
		foreach($kioskList as $kiosk_id=>$kiosk_name){
			$counter++;
			$tableName = "kiosk_{$kiosk_id}_products";
			$productsTable = TableRegistry::get($tableName,[
																	  'table' => $tableName,
																		  ]);
			//$this->Product->setSource($tableName);
			$query = $productsTable->find('all',array('conditions'=>array('id IN'=>$productArr),'fields'=>array('id','quantity'),'recursive'=>-1));
			$qttArr = array();
			foreach($query as $k=>$detail){
				$qttArr[$detail['id']]=$detail['quantity'];
			}
			
			if(empty($sumArr)){
				foreach($quantityArr as $productId=>$quantity){
					if(array_key_exists($productId,$qttArr)){
						$sumArr[$productId] = $quantityArr[$productId]+$qttArr[$productId];
					}
				}
			}else{
				foreach($sumArr as $productId=>$quantity){
					if(array_key_exists($productId,$qttArr)){
						$sumArr[$productId] = $sumArr[$productId]+$qttArr[$productId];
					}
				}
			}
		}
		
		return $sumArr;
	}
	
	private function search_total_cost($conditionArr){
	// echo $productId;die;
	 
		$products = $this->Products->find('all',array('condition'=>array('status'=>1),'fields'=>array('id','quantity','cost_price'),'recursive'=>-1));
		$productArr = array();
		foreach($products as $key=>$productDetail){
			$productArr[$productDetail['id']]=$productDetail['id'];
			$quantityArr[$productDetail['id']]=$productDetail['quantity'];
			$costPriceArr[$productDetail['id']]=$productDetail['cost_price'];
		}
		
		$sumArr = array();
		
		$kioskList_query = $this->Kiosks->find('list',
												  [
													   'keyField' => 'id',
													   'valueField' => 'name',
													   'conditions'=>['Kiosks.status'=>1],
												  ]);
		$kioskList_query = $kioskList_query->hydrate(false);
		$kioskList = $kioskList_query->toArray();
		foreach($kioskList as $kiosk_id=>$kiosk_name){
			$tableName = "kiosk_{$kiosk_id}_products";
			$productsTable = TableRegistry::get($tableName,[
																	  'table' => $tableName,
																		  ]);
			$query = $productsTable->find('all',array('conditions'=>array('id IN'=>$productArr,$conditionArr),'fields'=>array('id','quantity'),'recursive'=>-1));
			$qttArr = array();
			foreach($query as $k=>$detail){
				$qttArr[$detail['id']]=$detail['quantity'];
			}
			
			if(empty($sumArr)){
				foreach($quantityArr as $productId=>$quantity){
					if(array_key_exists($productId,$qttArr)){
						$sumArr[$productId] = $quantityArr[$productId]+$qttArr[$productId];
					}
				}
			}else{
				foreach($sumArr as $productId=>$quantity){
					if(array_key_exists($productId,$qttArr)){
						$sumArr[$productId] = $sumArr[$productId]+$qttArr[$productId];
					}
				}
			}
		}
		
		$totalCost = 0;
		foreach($sumArr as $productId=>$totalQtty){
			$totalCost+=$costPriceArr[$productId]*$totalQtty;
		}
		
		return $totalCost;
	}
	private function total_cost(){
	 
		$products = $this->Products->find('all',array('condition'=>array('status'=>1),'fields'=>array('id','quantity','cost_price'),'recursive'=>-1));
		$productArr = array();
		foreach($products as $key=>$productDetail){
			$productArr[$productDetail['id']]=$productDetail['id'];
			$quantityArr[$productDetail['id']]=$productDetail['quantity'];
			$costPriceArr[$productDetail['id']]=$productDetail['cost_price'];
		}
		
		$sumArr = array();
		
		$kioskList_query = $this->Kiosks->find('list',
												  [
													   'keyField' => 'id',
													   'valueField' => 'name',
													   'conditions'=>['Kiosks.status'=>1],
												  ]);
		$kioskList_query = $kioskList_query->hydrate(false);
		$kioskList = $kioskList_query->toArray();
		foreach($kioskList as $kiosk_id=>$kiosk_name){
			$tableName = "kiosk_{$kiosk_id}_products";
			$productsTable = TableRegistry::get($tableName,[
																	  'table' => $tableName,
																		  ]);
			$query = $productsTable->find('all',array('conditions'=>array('id IN'=>$productArr),'fields'=>array('id','quantity'),'recursive'=>-1));
			$qttArr = array();
			foreach($query as $k=>$detail){
				$qttArr[$detail['id']]=$detail['quantity'];
			}
			
			if(empty($sumArr)){
				foreach($quantityArr as $productId=>$quantity){
					if(array_key_exists($productId,$qttArr)){
						$sumArr[$productId] = $quantityArr[$productId]+$qttArr[$productId];
					}
				}
			}else{
				foreach($sumArr as $productId=>$quantity){
					if(array_key_exists($productId,$qttArr)){
						$sumArr[$productId] = $sumArr[$productId]+$qttArr[$productId];
					}
				}
			}
		}
		
		$totalCost = 0;
		foreach($sumArr as $productId=>$totalQtty){
			$totalCost+=$costPriceArr[$productId]*$totalQtty;
		}
		
		return $totalCost;
	}
	
	
	public function searchViewStock($kiosk_id = "",$keyword = ""){
	 $kiosks_query = $this->Kiosks->find('list',
											 [
												  'keyField' => 'id',
												  'valueField' => 'name',
												  'conditions' => ['Kiosks.status' => 1, 'Kiosks.id != ' => 10000],
												  'order' => 'Kiosks.name asc',
											 ]
					     );
		$kiosks_query = $kiosks_query->hydrate(false);
		$kiosks = $kiosks_query->toArray();
		$kiosks['all']="all";
		$this->set(compact('kiosks'));
	 
	 
//		$kiosks_query = $this->Kiosks->find('list',array(
//								'fields' => array('id', 'name'),
//                                                                'conditions' => array('Kiosks.status' => 1),
//								'order' => 'Kiosks.name asc',
//								'recursive' => -1
//							)
//					     );
//		$kiosks_query = $kiosks_query->hydrate(false);
//		$kiosks = $kiosks_query->toArray();
//		$kiosks['all']="all";
//		$this->set(compact('kiosks'));
		if(array_key_exists('KioskStock', $this->request['data'])){
			$kiosk_id = $this->request['data']['KioskStock']['kiosk_id'];
		}
		$displayType = "";
		if(array_key_exists('display_type',$this->request->query)){
			$displayType = $this->request->query['display_type'];
		}
		$conditionArr = array();
		if((int)$kiosk_id){
		   $productsTable = TableRegistry::get("kiosk_{$kiosk_id}_products",[
																	  'table' => "kiosk_{$kiosk_id}_products",
																		  ]);
			if($displayType=="more_than_zero"){
				$conditionArr['NOT']['`quantity`'] = 0;
			}
			$this->set(compact('displayType'));
		}elseif($kiosk_id=='all'){
		  $productsTable = TableRegistry::get("products",[
																	  'table' => "products",
																		  ]);
		}elseif($kiosk_id=="0" || $kiosk_id==""){
			if($displayType=="more_than_zero"){
				$conditionArr['NOT']['`quantity`'] = 0;
			}
			$this->set(compact('displayType'));
			$productsTable = TableRegistry::get("products",[
																	  'table' => "products",
																		  ]);
		}
		if(array_key_exists('search_kw',$this->request->query)){
			$searchKW = trim(strtolower($this->request->query['search_kw']));
		}
		if(!empty($searchKW)){
			$conditionArr['OR']['LOWER(product) like '] =  strtolower("%$searchKW%");
			$conditionArr['OR']['LOWER(product_code) like '] =  strtolower("%$searchKW%");
			$conditionArr['OR']['LOWER(description) like '] =  strtolower("%$searchKW%");
		}
		if(array_key_exists('category',$this->request->query) && !empty($this->request->query['category'][0])){
			$conditionArr['category_id IN'] = $this->request->query['category'];
		}
		$products = $this->paginate = [
					'conditions' => $conditionArr,
					'limit' => ROWS_PER_PAGE
				];
		
		$products_query = $this->paginate($productsTable);
		$products = $products_query->toArray();
		$this->set(compact('products'));
		
		if((int)$kiosk_id){
		  $productsTable = TableRegistry::get("kiosk_{$kiosk_id}_products",[
																	  'table' => "kiosk_{$kiosk_id}_products",
																		  ]);
			$productCostPrice_query = $productsTable->find('all',array(
																	   'fields'=>array('id','cost_price','quantity'),
																	   'conditions' => $conditionArr,
																	   'recursive'=>-1));
			$productCostPrice_query = $productCostPrice_query->hydrate(false);
			$productCostPrice = $productCostPrice_query->toArray();
			
			$sumTotalCostPrice = 0;
			$costPriceArr = array();
			//pr($productCostPrice);die;
			foreach($productCostPrice as $key=>$productDetail){
				$productId = $productDetail['id'];
				//$costPriceQuery = $this->Product->query("SELECT `cost_price`,`id` FROM `products` WHERE `id`='$productId'");
				$costPriceQuery_query = $productsTable->find('all',array('conditions' => array('id' => $productId),
																	 'fields' => array('id','cost_price'),
																	 'recursive'=>-1
																	 ));
				$costPriceQuery_query  =$costPriceQuery_query->hydrate(false);
				$costPriceQuery = $costPriceQuery_query->toArray();
				// echo $costPriceQuery_id = $costPriceQuery['0']['products']['id'];die;
				if(!empty($costPriceQuery)){
					if($productId == $costPriceQuery[0]['id']){
						 $costPrice = $costPriceQuery[0]['cost_price'];
					}else{
						$costPrice = "--";
					}
				}else{
						$costPrice = "--";
				}
				//echo $costPrice = $costPriceQuery[$productId]['products']['cost_price'];
				$costPriceArr[$productId]=$costPrice;
				$quantity = $productDetail['quantity'];
				$totalCostPrice = $costPrice*$quantity;
				$sumTotalCostPrice+=$totalCostPrice;
			}
			$this->set(compact('sumTotalCostPrice','costPriceArr'));
		}elseif($kiosk_id=="0" || $kiosk_id==""){
			//$this->Product->setSource("products");
			//echo "kiosk= 0";
			$productCostPrice_query = $this->Products->find('all',array(
																		'fields'=>array('id','cost_price','quantity'),
																		'conditions' => $conditionArr,
																		'recursive'=>-1));
			$productCostPrice_query = $productCostPrice_query->hydrate(false);
			$productCostPrice = $productCostPrice_query->toArray();
			//pr($productCostPrice);die;
			$sumTotalCostPrice = 0;
			foreach($productCostPrice as $key=>$productDetail){
				$productId = $productDetail['id'];
				$costPriceQuery_data = "SELECT `cost_price`,`id` FROM `products` WHERE `id`='$productId'";
					$conn = ConnectionManager::get('default');
					$stmt = $conn->execute($costPriceQuery_data);
					$costPriceQuery = $stmt ->fetchAll('assoc');
				 
				//echo $costPrice = $costPriceQuery[$productId]['products']['cost_price'];
				$costPrice = $costPriceQuery[0]['cost_price'];
				 $quantity = $productDetail['quantity'];
				//$totalCostPrice = 0;
				$totalCostPrice = $costPrice*$quantity;
				$sumTotalCostPrice+=$totalCostPrice;
			}
			$this->set(compact('sumTotalCostPrice'));
		}elseif($kiosk_id=='all'){
			$productArr = array();
			$quantityArr = array();
			foreach($products as $key=>$productDetail){
				$productArr[$productDetail->id]=$productDetail->id;
				$quantityArr[$productDetail->id]=$productDetail->quantity;
			}
			
			$sumArr = array();
			
			$kioskList_query = $this->Kiosks->find('list',
													   [
															'keyField' => 'id',
															'valueField' => 'name',
															'conditions'=>['Kiosks.status'=>1]
													   ]
											 );
			$kioskList_query = $kioskList_query->hydrate(false);
			$kioskList  =$kioskList_query->toArray();
			$counter = 0;
			foreach($kioskList as $kiosk_id=>$kiosk_name){
				$counter++;
				$tableName = "kiosk_{$kiosk_id}_products";
			     $products_Table = TableRegistry::get($tableName,[
																		   'table' => $tableName,
																			   ]);
			   if(empty($productArr)){
					$productArr = array(0 => null);
			   }
			  // pr($productArr);
				$query = $products_Table->find('all',array('conditions'=>array('id IN'=>$productArr,$conditionArr),
														   'fields'=>array('id','quantity'),'recursive'=>-1)
											   );
				 $query = $query->hydrate(false);
			   $query  =$query->toArray();
			   
				$qttArr = array();
				foreach($query as $k=>$detail){
					$qttArr[$detail['id']]=$detail['quantity'];
				}
				
				if(empty($sumArr)){
					foreach($quantityArr as $productId=>$quantity){
						if(array_key_exists($productId,$qttArr)){
							$sumArr[$productId] = $quantityArr[$productId]+$qttArr[$productId];
						}
					}
				}else{
					foreach($sumArr as $productId=>$quantity){
						if(array_key_exists($productId,$qttArr)){
							$sumArr[$productId] = $sumArr[$productId]+$qttArr[$productId];
						}
					}
				}
			}
			
			$sum_quantity = $sumArr;
			$sumTotalCostPrice = $this->search_total_cost($conditionArr);
			$this->set(compact('sumTotalCostPrice','sum_quantity'));
		}
		$selectedCategoryId=array();
		if(array_key_exists('category_id IN',$conditionArr) && !empty($conditionArr['category_id IN'][0])){
			$selectedCategoryId = $conditionArr['category_id IN'];
		}
		$categories_query = $this->Categories->find('all',array(
								'fields' => array('id', 'category','id_name_path'),
								'conditions' => array('Categories.status' => 1),
								'order' => 'Categories.category asc',
								'recursive' => -1
								));
		$categories_query = $categories_query->hydrate(false);
		$categories = $categories_query->toArray();
		
		$categories = $this->CustomOptions->category_options($categories,true,$selectedCategoryId);
			$hint = $this->ScreenHint->hint('stock_transfer','view_stock');
					if(!$hint){
						$hint = "";
					}		
		
		
		$this->set(compact('hint','categories'));
		//$this->layout = 'default'; 
		//$this->viewPath = 'Products';
		$this->render('view_stock');
	}
	
	
	public function transferredStock(){
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
			$this->Flash->error("This function works only on hpwaheguru");
			return $this->redirect(array('controller' => 'home','action' => "dashboard"));die;
		}
		
		$external_sites = Configure::read('external_sites_for_bulk');
		  $external_site_kiosk = array();
		  foreach($external_sites as $ex_site_id => $ex_site_name){
			   $connection = ConnectionManager::get($ex_site_name);
			   $stmt1 = $connection->execute('SELECT id,name From kiosks where status = 1 order by name asc'); 
			   $external_site_kiosk_data = $stmt1 ->fetchAll('assoc');
			   
			   if(!empty($external_site_kiosk_data)){
					foreach($external_site_kiosk_data as $key => $val){
						 if($val['id'] == 10000){
							  $external_site_kiosk[$ex_site_name] = array(-1 => "All") + $external_site_kiosk[$ex_site_name];
						 }else{
							  $external_site_kiosk[$ex_site_name][$val['id']] = $val['name'];	 
						 }
					}
			   }
		  }
		//pr($external_site_kiosk);die;
		$today = date("Y-m-d",strtotime("yesterday"));
		$startDate = date("Y-m-d",strtotime($today."-1 day"));
		$endDate = date("Y-m-d",strtotime($today."+1 day"));
		$this->paginate = ['order' => ['ReservedProducts.id DESC'],
                           'limit' => 100
                           ];
		$dispatchedProducts_query = $this->paginate('ReservedProducts');
		$dispatchedProducts = $dispatchedProducts_query->toArray();
        //pr($dispatchedProducts);die;
		$productIdArr = array();
		foreach($dispatchedProducts as $key=>$dispatchedProduct){
			$productIdArr[] = $dispatchedProduct->product_id;
		}
		if(empty($productIdArr)){
		  $productIdArr = array(0 => null);
		}
		$productData_query = $this->Products->find('all',array('conditions'=>array('Products.id IN'=>$productIdArr),'fields'=>array('id','product_code','product','image','category_id','cost_price','selling_price'),'recursive'=>-1,'order'=>'Products.id DESC'));
		$productData_query = $productData_query->hydrate(false);
		$productData = $productData_query->toArray();
		$productIdDetail = array();
		foreach($productData as $productInfo){
			$productIdDetail[$productInfo['id']] = $productInfo;
		}
		//pr($productIdDetail);die;
		$categoryData_query = $this->Categories->find('all',array('fields'=>array('id','category'),'order'=>'Categories.category ASC'));
		$categoryData_query = $categoryData_query->hydrate(false);
		$categoryData  =$categoryData_query->toArray();
		
		$categories = $this->CustomOptions->category_options($categoryData,true);
		$categoryArr = array();
		foreach($categoryData as $k => $value){
			$categoryArr[$value['id']] =  $value['category'];
		}
		$searched = 0;
		$todaysearched = 0;
		$this->set(compact('categories','categoryArr','hint','dispatchedProducts','productIdDetail','from_date','to_date','customers','todaysearched','searched','sites','external_site_kiosk','external_sites'));	
	}
	
	public function searchTransferredStock(){
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
			$this->Flash->error("This function works only on hpwaheguru");
			return $this->redirect(array('controller' => 'home','action' => "dashboard"));die;
		}
		$external_sites = Configure::read('external_sites_for_bulk');
		  $external_site_kiosk = array();
		  foreach($external_sites as $ex_site_id => $ex_site_name){
			   $connection = ConnectionManager::get($ex_site_name);
			   $stmt1 = $connection->execute('SELECT id,name From kiosks order by name asc'); 
			   $external_site_kiosk_data = $stmt1 ->fetchAll('assoc');
			   
			   if(!empty($external_site_kiosk_data)){
					foreach($external_site_kiosk_data as $key => $val){
						 if($val['id'] == 10000){
							  $external_site_kiosk[$ex_site_name][-1] = "All";
						 }else{
							  $external_site_kiosk[$ex_site_name][$val['id']] = $val['name'];	 
						 }
					}
			   }
		  }
		
		
		$customers_query = $this->Customers->find('all' ,array(
			 'fields'=>array('id','fname','business'),
			));
		$customers_query = $customers_query->hydrate(false);
		$customers = $customers_query->toArray();
          // pr($customers);                                             
		$startDate = '';
		$from_date = '';
		$searched = 1;
		$notAllow = 0;
		if(!empty($this->request->query['from_date'])){
			$from_date = date("Y-m-d",strtotime($this->request->query['from_date']));
			$startDate = date('Y-m-d', strtotime($from_date.'-1 day'));
		}
		
		$endDate = '';
		$to_date = '';
		if(!empty($this->request->query['to_date'])){
			$to_date = date("Y-m-d",strtotime($this->request->query['to_date']));
			$endDate = date('Y-m-d', strtotime($to_date.'+1 day'));
		}
		
		$search_kw = '';
		if(!empty($this->request->query['search_kw'])){
			
			$search_kw = $this->request->query['search_kw'];
		}
		
		$conditionArr = array();
		
		if(!empty($startDate) || !empty($endDate)){
			$conditionArr[] = array("DATE(ReservedProducts.created)>'$startDate'","DATE(ReservedProducts.created)<'$endDate'");
		}
		$selectedId = array();
		$cat_serach = 0;
		if(array_key_exists('category',$this->request->query)&& !empty($this->request->query['category'][0])){
		  $cat_serach = 1;
			//$notAllow = 1;
			$conditionArr1['category_id IN'] = $this->request->query['category'];
			$productIdData_query = $this->Products->find('all',array('conditions'=>$conditionArr1,'fields'=>array('id'),'recursive'=>-1,'order'=>'Products.id DESC'));
			$productIdData_query = $productIdData_query->hydrate(false);
			$productIdData = $productIdData_query->toArray();
			$ids = array();
            $selectedId = $conditionArr1['category_id IN'];
			foreach($productIdData as $a => $val){
				$ids[] = $val['id'];
			}
			if(empty($ids)){
			   $ids = array(0 => null);
			}
			$conditionArr['product_id IN'] = $ids;
			
		}

		if(!empty($search_kw)){
			$notAllow = 1;
			//$searched = 2;
			$productSearch_query = $this->Products->find('all',array('conditions'=>array(
								'OR'	=> array(
								'LOWER(Products.product_code) like' => "%$search_kw%",
								'LOWER(Products.product) like' => "%$search_kw%",
									)
								),
								'fields' => array('id'),
							)
					     );
			$productSearch_query = $productSearch_query->hydrate(false);
			$productSearch = $productSearch_query->toArray();
			$requestedProductIds = array();
			foreach($productSearch as $key=>$productResult){
				$requestedProductIds[] = $productResult['id'];
			}
			
			if(!empty($requestedProductIds)){
				$conditionArr[] = array("ReservedProducts.product_id IN"=>$requestedProductIds);
			}
		}
		$selected_site_id = "";
		if(array_key_exists('site',$this->request->query)){
		  $selected_site_id = $this->request->query['site'];
		  $this->set(compact('selected_site_id'));
		  if(!empty($selected_site_id)){
			   $conditionArr['site_id'] = $selected_site_id;   
		  }
		}
		
		foreach($external_sites as $ky=>$kval){
		  if($ky != $selected_site_id){
			   continue;
		  }
			   $text = $kval."_kiosk";
			   if(array_key_exists($text,$this->request->query)){
					$ex_kiosk_id = $this->request->query[$text];
					if($ex_kiosk_id != -1){
						 $conditionArr['kiosk_id'] = $ex_kiosk_id;	
					}
					$this->set(compact('ex_kiosk_id'));
			   }
		}
		
		if(array_key_exists('forprint',$this->request->query) && $this->request->query['forprint'] == 'Yes'){
			$limit = 1000;
		}else{
			$limit = 20;
		}
		
		$this->paginate = [
							  'conditions'=> $conditionArr,
							  'order' => ['ReservedProducts.id DESC'],
							  'limit' => 5000,
							  'maxLimit' => 5000
		];
		//pr($this->paginate);die;
		//die;
		$res_query = $this->paginate("ReservedProducts");
		
		$dispatchedProducts = $res_query->toArray();
		$alreadyDone = $productIdArr = array();
		$invoiceBillAmt = 0;
		
		foreach($dispatchedProducts as $key => $dispatchedProduct){
			$productIdArr[] = $dispatchedProduct['product_id'];
		}
		if(empty($productIdArr)){$productIdArr = array(0=>null); }
		$productData_query = $this->Products->find('all', array(
														 'conditions' => array('Products.id IN' => $productIdArr),
														 'fields' => array('id','product_code','product','image','category_id','cost_price','selling_price'),
														 'order' => 'Products.id DESC')
											);
		
		$productData_query = $productData_query->hydrate(false);
		$productData = $productData_query->toArray();
		$productIdDetail = array();
		foreach($productData as $productInfo){
			$productIdDetail[$productInfo['id']] = $productInfo;
		}
		$categoryData_query = $this->Categories->find('all',array('fields'=>array('id','category'),'order'=>'Categories.category ASC'));
		$categoryData_query = $categoryData_query->hydrate(false);
		$categoryData = $categoryData_query->toArray();
		$categories = $this->CustomOptions->category_options($categoryData,true,$selectedId);
		$categoryArr = array();
		foreach($categoryData as $k => $value){
			$categoryArr[$value['id']] =  $value['category'];
		}
		$hint = $this->ScreenHint->hint('stock_transfer','dispatched_products');
        if(!$hint){$hint = "";}

		$toDateTimestamp = strtotime($to_date);
		$todayDate = date('Y-m-d');
		$todayTimestamp = strtotime($todayDate);
		
		if($toDateTimestamp >= $todayTimestamp){
			$todaysearched = 0;
		}else{
			$todaysearched = 0;
		}
		  $Kiosks_list = $this->Kiosks->find('list',array('keyField' => 'id',
										 'valueField' => 'name'
										 ))->toArray();		
		//echo $categories;die;
		$this->set(compact('hint','dispatchedProducts','productIdDetail','categoryArr','categories','customers','searched','todaysearched', 'invoiceBillAmt','notAllow','cat_serach','sites','external_sites','external_site_kiosk','external_sites'));
		$this->render('transferred_stock');
	}
	
	function updateStock(){
		//increase - script execution time - rasu
		$this->increase_execution_time();
		$kioskId = $this->request->Session()->read('kiosk_id');//this is readin the kiosk_id of main website
		$productsName_query = $this->Products->find('list',
													   [
															'valueField' => 'product',	
													   ]);
		$productsName_query = $productsName_query->hydrate(false);
		$productsName = $productsName_query->toArray();
		$current_page = '';
		///pr($this->request);die;
		if(array_key_exists('current_page',$this->request['data'])){
			$current_page = $this->request['data']['current_page'];
		}
		
		if(!isset($current_page)){$this->redirect(array('action' => 'index'));}
		
		$productCounts = 0;
		if(array_key_exists('KioskStock',$this->request['data'])){
			$kiosk_id = $this->request['data']['KioskStock']['kiosk_id'];
		}else{
			$kiosk_id = $this->request->Session()->read('kioskId');//for reading the kiosk id chosen in the dropdown
		}
		
		if(array_key_exists('searchQueryUrl',$this->request['data'])){
			$searchQueryUrl = $this->request['data']['searchQueryUrl'];
		}
		
		if(array_key_exists('basket',$this->request['data'])){
			$productArr = array();
			
			foreach($this->request['data']['KioskStock']['product_id'] as $ki => $ide){
				$currentPrice = $this->request['data']['KioskStock']['current_price'][$ki];
				$currentPriceArr[$ide] = $currentPrice; //storing price for each product
			}
			
			$price = $remarks = '';
			$prdctIdArr = array();
			foreach($this->request['data']['KioskStock']['quantity'] as $key => $quantity){
				if(!empty($quantity)){
					$productID = $this->request['data']['KioskStock']['product_id'][$key];
					$prdctIdArr[$productID] = $productID;
				}
			}
			if(empty($prdctIdArr)){
			   $prdctIdArr = array(0 => null);
			}
			$costPriceArr_query = $this->Products->find('list',
															[
																 'keyField' => 'id',
																 'valueField' => 'cost_price',
																 'conditions' => ['Products.id IN' => $prdctIdArr]
															]);
			
			$costPriceArr_query = $costPriceArr_query->hydrate(false);
			$costPriceArr = $costPriceArr_query->toArray();
			
			foreach($this->request['data']['KioskStock']['quantity'] as $key => $quantity){
				if(!empty($quantity)){
					$currentQuantity = $this->request['data']['KioskStock']['p_quantity'][$key];
					$productID = $this->request['data']['KioskStock']['product_id'][$key];
					$remarks = $this->request['data']['KioskStock']['remarks'][$key];
					$price = $this->request['data']['KioskStock']['price'][$key];
				
					if($price<$costPriceArr[$productID]){
						$this->Flash->error("New sale price must be greater than the cost price for {$productsName[$productID]}");
						return $this->redirect(array('action' => "index/page:$current_page"));
						die;
					}
				
					if($quantity > 0 && $quantity <= $currentQuantity){
						$productArr[$productID] = array(
										'quantity' => $quantity,
										'price' => $price,
										'remarks' => $remarks,
										);
						$productCounts++;
					}
				}
								
			}
			
			$session_basket = $this->request->Session()->read('Basket');
			
			if(count($session_basket) >= 1){
				//adding item to the the existing session
				$sum_total = $this->add_arrays(array($productArr,$session_basket));
				$this->request->Session()->write('Basket', $sum_total);
				$session_basket = $this->request->Session()->read('Basket');
			}else{
				//adding item first time to session
				if(count($productCounts))$this->request->Session()->write('Basket', $productArr);
			}
			$session_basket = $this->request->Session()->read('Basket');
			if(is_array($session_basket) && count($session_basket)){
				//storing the session in session_backups table
				$this->SessionRestore->update_session_backup_table($this->request->params['controller'], 'index', 'Basket', $session_basket, $kioskId);
				$tKioskID = $this->request['data']['KioskStock']['kiosk_id'];
				$this->SessionRestore->update_session_backup_table($this->request->params['controller'], 'index', 'tKioskID', $tKioskID, $kioskId);
			}
			$this->request->Session()->write('kioskId', $kiosk_id);
			//changing it to kioskId as kiosk_id is for the main key for website and
			//it will conflict with it while saving the session in sessionrestore table
			$totalItems = count($this->request->Session()->read('Basket'));
			
			if($productCounts){
				$flashMessage = "$productCounts product(s) added to the stock. Total item Count:$totalItems";
			}else{
				$flashMessage = "No item added to the stock. Item Count:$productCounts";
			}
			
			$this->Flash->success($flashMessage);
			if(!empty($searchQueryUrl)){
				return $this->redirect(array('action'=>"index/page:$current_page"));
				//return $this->redirect(array('action' => "index$searchQueryUrl"));
			}else{
				return $this->redirect(array('action' => "index/page:$current_page"));
			}
		}elseif(array_key_exists('empty_basket',$this->request['data'])){
			if($this->request->Session()->delete('Basket')){
				$this->SessionRestore->delete_from_session_backup_table("StockTransfer", 'index', 'Basket', $kioskId);
			}else{
			   $this->SessionRestore->delete_from_session_backup_table("StockTransfer", 'index', 'Basket', $kioskId);
			}
			$this->request->Session()->delete('kioskId');
			$flashMessage = "Basket is empty; Add Fresh Stock!";
			$this->Flash->success($flashMessage);
			return $this->redirect(array('action' => "index/page:$current_page"));			
		}elseif(array_key_exists('check_out',$this->request['data'])){
			if(isset($this->request['data']['KioskStock']['kiosk_id'])){
				$kiosk_id = $this->request['data']['KioskStock']['kiosk_id'];
				$this->request->Session()->write('kioskId', $kiosk_id);
			}elseif(empty($this->request['data']['KioskStock']['kiosk_id'])){
				$this->Flash->error('Please select the kiosk!!');
			}
			return $this->redirect(array('action' => "stock_transfer_checkout"));			
		}else{
			$productArr = array();
			if(empty($kiosk_id)){
				$flashMessage = "Failed to transfer stock. <br />Please select kiosk for stock transfer!";
				$this->Flash->error($flashMessage,['escape' => false]);
				return $this->redirect(array('action' => "index/page:$current_page"));
				die;
			}
			
			if(!empty($this->request->data) &&
			array_key_exists('KioskStock',$this->request['data'])){
				
				$prdctIdArr = array();
				foreach($this->request['data']['KioskStock']['quantity'] as $key => $quantity){
					if(!empty($quantity)){
						$productID = $this->request['data']['KioskStock']['product_id'][$key];
						$prdctIdArr[$productID] = $productID;
					}
				}
				if(empty($prdctIdArr)){
					$prdctIdArr  = array(0 => null);
				}
				
				$costPriceArr_query = $this->Products->find('list',
																 [
																	  'keyField' => 'id',
																	  'valueField' => 'cost_price',
																	  'conditions' => ['Products.id IN' => $prdctIdArr],
																 ]);
				$costPriceArr_query = $costPriceArr_query->hydrate(false);
				$costPriceArr = $costPriceArr_query->toArray();
				
			//if(array_key_exists('product_id',$this->request['data']['KioskStock'])){
				foreach($this->request['data']['KioskStock']['product_id'] as $ki => $ide){
					$currentPrice = $this->request['data']['KioskStock']['current_price'][$ki];
					$currentPriceArr[$ide] = $currentPrice;
				}
				
				foreach($this->request['data']['KioskStock']['quantity'] as $key => $quantity){
					if(!empty($quantity)){
						$currentQuantity = $this->request['data']['KioskStock']['p_quantity'][$key];
						$productID = $this->request['data']['KioskStock']['product_id'][$key];
						$remarks = $this->request['data']['KioskStock']['remarks'][$key];
						$price = $this->request['data']['KioskStock']['price'][$key];
						
						if($price<$costPriceArr[$productID]){
							$this->Flash->error("New sale price must be greater than the cost price for {$productsName[$productID]}");
							return $this->redirect(array('action' => "index/page:$current_page"));
							die;
						}
						
						if($quantity > 0 && $quantity <= $currentQuantity){
							$productArr[$productID] = array(
											'quantity' => $quantity,
											'price' => $price,
											'remarks' => $remarks,
											);
							$productCounts++;
						}
					}
				}
				
				$session_basket = $this->request->Session()->read('Basket');
				$sum_total = $this->add_arrays(array($productArr,$session_basket));
				
				if(count($sum_total) == 0){
					$flashMessage = "Failed to transfer stock. <br />Please select quantity atleast for one product!";
					$this->Flash->error($flashMessage);
					return $this->redirect(array('action' => "index/page:$current_page"));
					die;
				}
						 
				//echo"bye";die;
				if(count($session_basket) >= 1){
					//adding item to the the existing session
					$sum_total = $this->add_arrays(array($productArr,$session_basket));
					$this->request->Session()->write('Basket', $sum_total);
					$session_basket = $this->request->Session()->read('Basket');
				}else{
					//adding item first time to session
					if(count($productCounts))$this->request->Session()->write('Basket', $productArr);
					$session_basket = $this->request->Session()->read('Basket');
				}
			}
			//pr($session_basket);die;
			
			$qty_error = array();
			if(!empty($session_basket)){
				foreach($session_basket as $prd_id => $values){
					if($prd_id == "position")continue;
					$req_qty = $values['quantity'];
					$qty_arr = $this->Products->find('list',array('conditions' => array('Products.id' => $prd_id),
																  'keyField' => 'id',
																  'valueField' => 'quantity',
																  //'fields' => array('id','quantity')
																  ))->toArray();
					$code_arr = $this->Products->find('list',array('conditions' => array('Products.id' => $prd_id),
																   'keyField' => 'id',
																   'valueField' => 'product_code',
																   //'fields' => array('id','product_code')
																   )
													  )->toArray();
					if($req_qty > $qty_arr[$prd_id]){
						$qty_error[] = $code_arr[$prd_id];
					}
				}	
			}
			if(!empty($qty_error)){
				$this->request->Session()->delete('Basket');
				$product_codes = implode(",",$qty_error);
				$error_str = "Not Sufficent Quantity For Product code $product_codes";
				$this->Flash->success($error_str);
			    return $this->redirect(array('action' => "index/page:$current_page"));
			    die;
			}
			
			
			
			$datetime = date('Y-m-d H:i:s');
			$kioskOrderData = array(
						'kiosk_id' => $kiosk_id,
                                                'user_id' => $this->request->Session()->read('Auth.User.id'),
						'dispatched_on' => $datetime,
						'status' => 1
						);
			
			$KioskOrders = $this->KioskOrders->newEntity();
			$KioskOrders = $this->KioskOrders->patchEntity($KioskOrders, $kioskOrderData,['validate' => false]);
			$this->KioskOrders->save($KioskOrders);
			$kiosk_order_id = $KioskOrders->id;
			
			$prodtCatArr_query = $this->Products->find('list',
															[
																 'keyField' => 'id',
																 'valueField' => 'category_id',
															]);
			$prodtCatArr_query = $prodtCatArr_query->hydrate(false);
			$prodtCatArr = $prodtCatArr_query->toArray();
			$session_basket = $this->request->Session()->read('Basket');
			$countTransferred = 0;
			$timestamp = time();
			$boloram_products = array();
			foreach($session_basket as $productID => $productData){
				if($productID == "position")continue;
				$cost_price_list_query = $this->Products->find('list',
																 [
																	  'keyField' => 'id',
																	  'valueField' => 'cost_price',
																	  'conditions' => array('Products.id' => $productID),
																 ]);
				$cost_price_list_query = $cost_price_list_query->hydrate(false);
				$cost_price_list = $cost_price_list_query->toArray();
				
			    $StockTransfer = $this->StockTransfer->newEntity();
			    
				$price = $productData['price'];
				$quantity = $productData['quantity'];
				$remarks = $productData['remarks']; 
				$boloram_products[$productID] = $quantity;
				$stockTransferData = array(
							'kiosk_order_id' => $kiosk_order_id,
							'kiosk_id' => $kiosk_id,
							'product_id' => $productID,
							'quantity' => $quantity,
							'cost_price' => $cost_price_list[$productID],
							'static_cost' => $cost_price_list[$productID],
							'sale_price' => $price,
							'status' => '1',
							'remarks' => $remarks
							);
				$StockTransfer = $this->StockTransfer->patchEntity($StockTransfer, $stockTransferData,['validate' => false]);
				if($this->StockTransfer->save($StockTransfer)){
					$countTransferred++;
					$kioskTable = "kiosk_transferred_stock_$kiosk_id";
					$tableQuery = $this->TableDefinition->get_table_defination('transferred_stock',$kiosk_id);
					$conn = ConnectionManager::get('default');
					$stmt = $conn->execute($tableQuery);
					
					$insertQuery = "INSERT INTO $kioskTable SET
								`kiosk_order_id` = $kiosk_order_id,
								`product_id` = $productID,
								`quantity` = $quantity,
								`sale_price` = $price,
								`created` = '$datetime',
								`status` = 1
							";
					$conn = ConnectionManager::get('default');
					$stmt = $conn->execute($insertQuery);
					
					//decreasing central stock
					
					//$this->Product->id = $productID;
					$external_site_status = $site_id_to_save = 0;
					$sites = Configure::read('sites');
					$external_sites = Configure::read('external_sites_for_bulk');
					$isboloRam = false;
					$path = dirname(__FILE__);
					if(!empty($sites)){
						 foreach($sites as $site_id => $site_value){
							  if($isboloRam){
								   continue;
							  }
							  $isboloRam = strpos($path,$site_value);
							  $site_id_to_save = $site_id;
							  if(in_array($site_value,$external_sites)){
								   $external_site_status = 1;
							  }
						 }
					}
					 //sourabh delete code
					if($isboloRam != false){
						$vat = $this->VAT;
						//$dummyproductArr = array('2151','2155','2388','2678','2988','4131','4753','5409','5411','5289','5287','6672');
						//if(in_array($productID,$dummyproductArr)){
							//$this->Product->setDataSource('ADMIN_DOMAIN_db');
							//$this->ReservedProducts->setDataSource('ADMIN_DOMAIN_db');
							//$this->TransferUnderstock->setDataSource('ADMIN_DOMAIN_db');
							
							$product_qty_query = "SELECT id,quantity From products";
							
							$connection = ConnectionManager::get('hpwaheguru');
							
							$stmt1 = $connection->execute('SELECT NOW() as created'); 
						    $currentTimeInfo = $stmt1 ->fetchAll('assoc');  
							$currentTime = $currentTimeInfo[0]['created'];
							
						    $stmt = $connection->execute($product_qty_query);
						    $product_qty = $stmt ->fetchAll('assoc');
							foreach($product_qty as $s_key => $s_value){
							  $prodtQtyArr[$s_value['id']] = $s_value['quantity'];
							}
						 
							$product_cost_query = "SELECT id,cost_price From products";
							$stmt = $connection->execute($product_cost_query);
						    $product_cost = $stmt ->fetchAll('assoc');
							foreach($product_cost as $s_key1 => $s_value1){
							  $prodtCostArr[$s_value1['id']] = $s_value1['cost_price'];
							}
							
							$product_sale_query = "SELECT id,selling_price From products";
							$stmt = $connection->execute($product_sale_query);
						    $product_sale = $stmt ->fetchAll('assoc');
							foreach($product_sale as $s_key2 => $s_value2){
							  $prodtSaleArr[$s_value2['id']] = $s_value2['selling_price'];
							}
							
							//$prodtQtyArr = $this->Product->find('list',array('fields'=>array('id','quantity'),'recursive'=>-1));
							//$prodtCostArr = $this->Product->find('list',array('fields'=>array('id','cost_price'),'recursive'=>-1));
							//$prodtSaleArr = $this->Product->find('list',array('fields'=>array('id','selling_price'),'recursive'=>-1));
							$wheguruQtity = $prodtQtyArr[$productID];
							if($wheguruQtity < $quantity){
								$sellPrice = $prodtSaleArr[$productID];
								$a = $sellPrice*100;
								$b = $vat+100;
								$orignalPrice = $a/$b;
								
								$transfer_under_stock_query = "SELECT * FROM transfer_understock WHERE product_id = $productID AND DATE(created) = CURDATE() AND site_id = $site_id_to_save";
								if($external_site_status == 1){
								   $transfer_under_stock_query .= " AND kiosk_id = $kiosk_id";
								}
								$stmt = $connection->execute($transfer_under_stock_query);
							    $underStockResult = $stmt ->fetchAll('assoc');
								
								$qtyToSub = $wheguruQtity;
								$remainingQty = $quantity - $wheguruQtity;
								$understockData = array(
														'product_id' => $productID,
														'quantity' => $remainingQty,
														'cost_price' => $prodtCostArr[$productID],
														'sale_price' => $orignalPrice,
														'invoice_reference' => $timestamp,
														'category_id' => $prodtCatArr[$productID],
														'created' => $currentTime,
														'modified' => $currentTime,
														'site_id' => $site_id_to_save,
														'kiosk_id' => $kiosk_id,
														);
								
								if(count($underStockResult) > 0){
									foreach($underStockResult as $key => $value){
										$understockId =  $value['id'];
									}
									$query = "UPDATE `transfer_understock` SET `modified` = '$currentTime' , `quantity` = `quantity` + $remainingQty WHERE `transfer_understock`.`id` = $understockId";
									if($external_site_status == 1){
										$query .= " AND kiosk_id = $kiosk_id";
									 }
									$stmt = $connection->execute($query);
									//$this->TransferUnderstock->query("");
								}else{
									$connection->insert('transfer_understock',
																		  $understockData
																	   , ['created' => 'datetime']);
									//$this->TransferUnderstock->save();
								}
								
							}else{
								$qtyToSub = $quantity;
							}
							
							
							if($qtyToSub > 0){
							  $Product_query = "UPDATE `products` SET `quantity` = `quantity` - $qtyToSub WHERE `products`.`id` = $productID";
							  $stmt = $connection->execute($Product_query);
							  //$product_qty = $stmt ->fetchAll('assoc');   
							  $reserve_result_query = "SELECT * FROM reserved_products WHERE product_id = $productID AND DATE(created) = CURDATE() AND status = 0 AND site_id =  $site_id_to_save";
							  if($external_site_status == 1){
								   $reserve_result_query .= " AND kiosk_id = $kiosk_id";
								}
							  $stmt = $connection->execute($reserve_result_query);
							  $rserveResult = $stmt ->fetchAll('assoc');
								if(count($rserveResult) > 0){
									foreach($rserveResult as $key => $value){
										$reserveId =  $value['id'];
										$oldQtity = $value['quantity'];
									}
									$ReservedProducts_query = "UPDATE `reserved_products` SET `modified` = '$currentTime', `quantity` = `quantity` + $quantity WHERE `reserved_products`.`id` = $reserveId AND `reserved_products`.`site_id` =  $site_id_to_save";
									if($external_site_status == 1){
										$ReservedProducts_query .= " AND kiosk_id = $kiosk_id";
									 }
									$stmt = $connection->execute($ReservedProducts_query);
								}else{
									$reservedProductData = array(
															 'product_id' =>  $productID,
															 'category_id' => $prodtCatArr[$productID],
															 'quantity' => $qtyToSub,
															 'cost_price' => $cost_price_list[$productID],
															 'sale_price' => $price,
															 'created' => $currentTime,
															'modified' => $currentTime,
															'site_id' => $site_id_to_save,
															'kiosk_id' => $kiosk_id,
															 );
									$connection->insert('reserved_products', 
																		  $reservedProductData
																	   , ['created' => 'datetime']);
									//$this->ReservedProducts->save($reservedProductData);
									//$this->Product->setDataSource('default');
								}
							}
						//}
					}
					//die;
				}else{
					//failed to create order
				}
			}
			//die;
			//$this->Products->setDataSource('default');
			//$this->ReservedProducts->setDataSource('default');
			//$this->TransferUnderstocks->setDataSource('default');
			$counter =  $this->update_qantities($boloram_products);
			$countTransferred = $counter + $countTransferred;   
			
			if($countTransferred > 0){
				//** code for sending pusher messages
				$pushStr = "Products have been transferred under order # $kiosk_order_id. Please receive them";
				  $this->Pusher->email_kiosk_push($pushStr,$kiosk_id);
				// till here **
			}
			$this->request->Session()->delete('kioskId');
			if(empty($kioskId)){
			   $kioskId1 = 10000;
			}else{
			   $kioskId1 = $kioskId;
			}
			if($this->request->Session()->delete('Basket')){
				$this->SessionRestore->delete_from_session_backup_table("StockTransfer", 'index', 'Basket', $kioskId1);
			}else{
			   $this->SessionRestore->delete_from_session_backup_table("StockTransfer", 'index', 'Basket', $kioskId1);
			}
			$flashMessage = count($session_basket)." products dispatched for order id $kiosk_order_id";
			$this->Flash->error($flashMessage);
		}		
		return $this->redirect(array('action' => "index/page:$current_page"));
	}
	private function add_arrays($arrays = array()){
	 $allValues = array();
	 $arrays = array_reverse($arrays,true);
	 //pr($arrays);die;
	 foreach($arrays as $sngArr){
		  if(is_array($sngArr)){
			//pr($sngArr);die;
			   foreach($sngArr as $key => $value){
					if(!array_key_exists($key,$allValues)){
						 $allValues[$key] = $value;
					}else{
						$allValues[$key] = $value;
						//$allValues[$key]['quantity'] += $value['quantity'];
					}
				}
			}
		}
		  //sort($allValues,SORT_STRING);
		  return $allValues;
	}
	 
	 public function stockTransferCheckout(){
		$session_basket = $this->request->Session()->read('Basket');
		$kiosk_Id = $this->request->Session()->read('kioskId');//kiosk id in the chosen dropdown
		$kioskId = $this->request->Session()->read('kiosk_Id');//the main kiosk id of the website
		if(is_array($session_basket)){
			$product_ids = array_keys($session_basket);
			//pr($product_ids);
			$productCodeArr = array();
			if(empty($product_ids)){
			   $product_ids = array(0 => null);
			}
			$productCodeArr_query = $this->Products->find('all',
												   array('conditions'=>array('Products.id IN'=>$product_ids),
														 'fields'=>array('id','product_code','quantity'),
														 'recursive'=>-1)
												   );
			$productCodeArr_query = $productCodeArr_query->hydrate(false);
			$productCodeArr = $productCodeArr_query->toArray();
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
				$product_name_query = $this->Products->find('all',array('conditions'=>array('Products.id'=>$productId),
												   'fields'=>array('id','product'),
												   'recursive'=>-1));
				$product_name_query = $product_name_query->hydrate(false);
				$productNameArr[] = $product_name_query->first();
			}
			foreach($productNameArr as $key=>$selectedProducts){
				$productArr[$selectedProducts['id']] = $selectedProducts['product'];
			}
			if($this->request->is('post')){
				$error = array();
				if(array_key_exists('update_quantity',$this->request->data)){
					$lessProducts = array();
					$lowProducts = array();
					foreach($this->request->data['CheckOut'] as $productCode => $quantity){
						if($quantity == 0 || !(int)$quantity){
								$lowProducts[] = $productCode;
						}
						$availableQty = $productCodes[$productCode];
						if($quantity > $availableQty){
							$lessProducts[] = $productCode;
						}	
					}
					if(count($lessProducts) >= 1){
						$this->Session->setFlash("Please choose ".implode(",",$lessProducts)." quantity less than or equal to available stock" );
						return $this->redirect(array('action'=>'stock_transfer_checkout'));
					}
					if(count($lowProducts) > 0){
						 $this->Session->setFlash("Please choose  more than 0 for product : ".implode(",",$lowProducts) );
						return $this->redirect(array('action'=>'stock_transfer_checkout'));
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
									    'quantity' => $qty   ,
									    'current_qtt' => $productData ['quantity'],
										'price' => $productData['price'],
										'remarks' => $productData['remarks']
													);
						$counter++;
					}
					$this->request->Session()->delete('Basket');
					if($this->request->Session()->write('Basket',$newArray)){
						$this->SessionRestore->update_session_backup_table($this->request->params['controller'], 'index', 'Basket', $newArray, $kioskId);
					}
					$this->Flash->success("Quantity has been basket successfully updated");
					return $this->redirect(array('action'=>'stock_transfer_checkout'));
				}
			}elseif(array_key_exists('edit_basket',$this->request->data)){
				return $this->redirect(array('action'=>"index/$kiosk_id"));
			}
	    }
			 
		 
			
			$this->set(compact('productArr','productCode','productIds'));
		}
	}
	
	 public function stockTransferCheckoutAjaxBase(){
		$session_basket = $this->request->Session()->read('Basket');
		$kiosk_Id = $this->request->Session()->read('kioskId');//kiosk id in the chosen dropdown
		$kioskId = $this->request->Session()->read('kiosk_Id');//the main kiosk id of the website
		if(is_array($session_basket)){
			$product_ids = array_keys($session_basket);
			//pr($product_ids);
			$productCodeArr = array();
			if(empty($product_ids)){
			   $product_ids = array(0 => null);
			}
			$productCodeArr_query = $this->Products->find('all',
												   array('conditions'=>array('Products.id IN'=>$product_ids),
														 'fields'=>array('id','product_code','quantity'),
														 'recursive'=>-1)
												   );
			$productCodeArr_query = $productCodeArr_query->hydrate(false);
			$productCodeArr = $productCodeArr_query->toArray();
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
				$product_name_query = $this->Products->find('all',array('conditions'=>array('Products.id'=>$productId),
												   'fields'=>array('id','product'),
												   'recursive'=>-1));
				$product_name_query = $product_name_query->hydrate(false);
				$productNameArr[] = $product_name_query->first();
			}
			foreach($productNameArr as $key=>$selectedProducts){
				$productArr[$selectedProducts['id']] = $selectedProducts['product'];
			}
			if($this->request->is('post')){
				$error = array();
				if(array_key_exists('update_quantity',$this->request->data)){
					$lessProducts = array();
					$lowProducts = array();
					foreach($this->request->data['CheckOut'] as $productCode => $quantity){
						if($quantity == 0 || !(int)$quantity){
								$lowProducts[] = $productCode;
						}
						$availableQty = $productCodes[$productCode];
						if($quantity > $availableQty){
							$lessProducts[] = $productCode;
						}	
					}
					if(count($lessProducts) >= 1){
						$this->Session->setFlash("Please choose ".implode(",",$lessProducts)." quantity less than or equal to available stock" );
						return $this->redirect(array('action'=>'stock_transfer_checkout'));
					}
					if(count($lowProducts) > 0){
						 $this->Session->setFlash("Please choose  more than 0 for product : ".implode(",",$lowProducts) );
						return $this->redirect(array('action'=>'stockTransferCheckoutAjaxBase'));
				} else{
					 
			  	$requestedQuantity = $this->request->data['CheckOut'];
			 	$newArray = array();
					$counter = 0;
					//$requestedQuantity = array_values($requestedQuantity);
					foreach($session_basket as $productCode=>$productData){
						 if($productCode == "position"){
							  continue;
						 }
						$qty = "";
						//pr($requestedQuantity);die;
						if(array_key_exists($productData['product_code'],$requestedQuantity)){
						 //echo "hi";die;
							 $qty =  $requestedQuantity[$productData['product_code']];
							 $session_basket[$productCode]['quantity'] = $qty;
						}
						$counter++;
					}
					//pr($session_basket);die;
					$this->request->Session()->delete('Basket');
					if($this->request->Session()->write('Basket',$session_basket)){
						$this->SessionRestore->update_session_backup_table($this->request->params['controller'], 'index', 'Basket', $newArray, $kioskId);
					}
					$this->Flash->success("Quantity has been basket successfully updated");
					return $this->redirect(array('action'=>'stockTransferCheckoutAjaxBase'));
				}
			}elseif(array_key_exists('edit_basket',$this->request->data)){
				return $this->redirect(array('action'=>"ajax-base-transfer"));
			}
	    }
			 
		 
			
			$this->set(compact('productArr','productCode','productIds'));
		}
	}
	
	public function deleteProductFromSessionBasket($productId=''){
	 unset($_SESSION['Basket'][$productId]);
		if(true){
			$kioskId = $this->request->Session()->read('kiosk_id');
			$session_basket = $this->request->Session()->read('Basket');
			$this->SessionRestore->update_session_backup_table($this->request->params['controller'], 'index', 'Basket', $session_basket, $kioskId);
			return $this->redirect(array('action'=>'stock_transfer_checkout'));
		}
	}
	
	public function update_qantities($boloram_products){
		if(!empty($boloram_products)){
			$counter = 0;
			foreach($boloram_products as $key => $value){
				$productData = array('quantity' => "Product.quantity - $value");
				$query = "UPDATE `products` SET `quantity` = `quantity` - $value WHERE `products`.`id` = $key";
				  $conn = ConnectionManager::get('default');
			      $stmt = $conn->execute($query);
				$counter++;
			}
		}
		return $counter;
	}
	
	function search($keyword = "",$displayCondition = ""){
		$this->increase_execution_time();
		$session_basket = $this->request->Session()->read('Basket');
		$basketStrDetail = '';
		if(is_array($session_basket)){
			$productCodeArr = array();
			foreach($session_basket as $key => $basketItem){
			   $productCodeArr_query = $this->Products->find('all',array('conditions'=>array('Products.id'=>$key),'fields'=>array('id','product_code')));
			   $productCodeArr_query = $productCodeArr_query->hydrate(false);
			   $productCodeArr[] =$productCodeArr_query->first();
			}
			$productCode = array();
			if(!empty($productCodeArr)){
				foreach($productCodeArr as $k=>$productCodeData){
					$productCode[$productCodeData['id']]=$productCodeData['product_code'];
				}
			}
			
			foreach($session_basket as $productId=>$productDetails){
				$productName_query = $this->Products->find('all',array('conditions'=>array('Products.id'=>$productId),'fields'=>array('id','product'),'recursive'=>-1));
				$productName_query = $productName_query->hydrate(false);
				$productName = $productName_query->first();
				$basketStrDetail.= "<tr>
				<td>".$productCode[$productId]."</td>
				<td>".$productName['product']."</td>
				<td>".$productDetails['price']."</td>
				<td>".$productDetails['quantity']."</td>
				<td>".$productDetails['remarks']."</td>
				</tr>";
			}
			
		}
		
		if(!empty($basketStrDetail)){
			$basketStr = "<table>
			<tr>
				<th style='width: 152px;'>Product code</th>
				<th>Product</th>
				<th style='width: 58px;'>New price</th>
				<th style='width: 31px;'>Qty</th>
				<th style='width: 94px;'>Remarks</th>
			</tr>".$basketStrDetail."
			</table>";
		}
		
		$totalItems = count($session_basket);
		if($totalItems){
			$flashMessage = "Total item Count:$totalItems<br/>$basketStr";
			$this->Flash->success($flashMessage,array('escape' => false));
		}
		$tKioskID = $this->request->Session()->read("tKioskID");
		if( !empty($tKioskID)){
			$this->request->Session()->write('kioskId', $tKioskID);
		}
		if(array_key_exists('search_kw',$this->request->query)){
			$search_kw = $this->request->query['search_kw'];
		}
		
		$displayType = "";
		if(array_key_exists('display_type',$this->request->query)){
			$displayType = $this->request->query['display_type'];
		}
		
		 
		extract($this->request->query);
		$categories_query = $this->Categories->find('all',array(
								'fields' => array('id', 'category','id_name_path'),
                                                                'conditions' => array('Categories.status' => 1),
								'order' => 'Categories.category asc',
								'recursive' => -1
								));
		$categories_query = $categories_query->hydrate(false);
		$categories = $categories_query->toArray();
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
		if($displayType=="more_than_zero"){
			$conditionArr['NOT']['`Products`.`quantity`'] = 0;
		}
		//$this->Product->find('all',array('conditions' => $conditionArr));
		$this->paginate = [
						   'limit' => ROWS_PER_PAGE,
						   'conditions' => $conditionArr];
		$selectedCategoryId=array();
		if(array_key_exists('category_id IN',$conditionArr) && !empty($conditionArr['category_id IN'][0])){
			$selectedCategoryId=$conditionArr['category_id IN'];
		}
		$categories = $this->CustomOptions->category_options($categories,true,$selectedCategoryId);
		$kiosks_query = $this->Kiosks->find('list',
													   [
															'keyField' => 'id',
															'valueField' => 'name',
															'conditions' => ['Kiosks.status' => 1],
															'order' => 'Kiosks.name asc',
													   ]);
		$kiosks_query = $kiosks_query->hydrate(false);
		$kiosks = $kiosks_query->toArray();
		
		//pr($this->paginate);die;
		$centralStocks_query = $this->paginate("Products");
		$centralStocks = $centralStocks_query->toArray();
		 
		$categoryIdArr = array();
		foreach($centralStocks as $key=>$centralStock){
			$categoryIdArr[] = $centralStock->category_id;
		}
		if(empty($categoryIdArr)){
		  $categoryIdArr = array(0 => null);
		}
		$categoryName_query = $this->Categories->find('list',
															[
																 'keyField' => 'id',
																 'valueField' => 'category',
																 'conditions'=>['Categories.id IN'=>$categoryIdArr],
															]
												);
		$categoryName_query  =$categoryName_query->hydrate(false);
		$categoryName = $categoryName_query->toArray();
		//pr($categoryName);die;
		$hint = $this->ScreenHint->hint('stock_transfer','index');
					if(!$hint){
						$hint = "";
					}
		
		$this->set(compact('hint','categories','kiosks','displayType','centralStocks','categoryName'));
		$this -> render('index');
	}
    public function disputedOrders(){
    	$kiosk_id = $this->request->Session()->read('kiosk_id');
		
		$disputeOptions = Configure::read('dispute');
		$approvalOptions = Configure::read('approval_status');
        $kiosks_query = $this->Kiosks->find('list',
                                            [
                                                 'keyField' => 'id',
                                                 'valueField' => 'name',
                                                 'conditions' => ['Kiosks.status' => 1],
                                                 'order' => ['Kiosks.name asc']
                                            ]);
        $kiosks_query = $kiosks_query->hydrate(false);
        if(!empty($kiosks_query)){
             $kiosk = $kiosks_query->toArray();
        }else{
            $kiosk = array();
        }
      
		if($kiosk_id > 0){
            $this->paginate = [
                                    'conditions' => ['OrderDisputes.kiosk_id'=>$kiosk_id],
                                                'limit' => ROWS_PER_PAGE,
                                               'order' => ['OrderDisputes.id' => 'desc']
                                                
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
											 'conditions' => ['OrderDisputes.kiosk_id IN'=>$managerKiosk],
                                                'limit' => ROWS_PER_PAGE,
                                               'order' => ['OrderDisputes.id' => 'desc']
                                                
                                              ];
			   }else{
					$this->paginate = [
                                   
                                                'limit' => ROWS_PER_PAGE,
                                               'order' => ['OrderDisputes.id' => 'desc']
                                                
                                              ];
			   }
		  }else{
			 $this->paginate = [
                                   
                                                'limit' => ROWS_PER_PAGE,
                                               'order' => ['OrderDisputes.id' => 'desc']
                                                
                                              ];  
		  }
             
		 
		}
		$data = $this->paginate("OrderDisputes");
        if(!empty($data)){
            $orderDisputes = $data->toArray();
        }else{
            $orderDisputes = array();
        }
       // pr($orderDisputes);die;
		//$orderDisputes = $this->Paginate('OrderDispute');
		$disputedByArr = array();
		$partial_or_done = $orderDispute_remarks= array();
		//pr($orderDisputes);die;
		foreach($orderDisputes as $key=>$orderDispute){
			$kiosk_order_id = $orderDispute['kiosk_order_id'];
			$approvel_status = $orderDispute['approval_status'];
			if(array_key_exists($kiosk_order_id,$partial_or_done)){
				if($approvel_status == 0){
					$partial_or_done[$kiosk_order_id] = $kiosk_order_id;
				}
			}else{
				if($approvel_status == 0){
					$partial_or_done[$kiosk_order_id] = $kiosk_order_id;
				}
			}
			if(array_key_exists($kiosk_order_id,$orderDispute_remarks)){
				if(!empty($orderDispute['admin_remarks'])){
					$orderDispute_remarks[$kiosk_order_id] = $orderDispute_remarks[$kiosk_order_id]."|".$orderDispute['admin_remarks'];
				}
			}else{
				if(!empty($orderDispute['admin_remarks'])){
					$orderDispute_remarks[$kiosk_order_id] = $orderDispute['admin_remarks'];
				}
			}
			
			if($orderDispute['disputed_by']>0){
				$disputedByArr[] = $orderDispute['disputed_by'];
			}
		}
		$users_query = $this->Users->find('list',
                                    [
                                        'keyField' => 'id',
                                        'valueField' => 'username',
                                    ]);
        if(!empty($users_query)){
             $users = $users_query->toArray();
        }
		$this->set(compact('orderDisputes','kiosk','disputeOptions','approvalOptions','users','orderDispute_remarks','partial_or_done'));
	}
    public function viewDisputedOrders($order_id = null,$kiosk_id = null){
		if(empty($kiosk_id)){
			throw new NotFoundException(__('No Kiosk Id Found'));
		}
		$disputeOptions = Configure::read('dispute');
		$approvalOptions = Configure::read('approval_status');
        $kiosks_query = $this->Kiosks->find('list',
                                    [
                                         'keyField' => 'id',
                                         'valueField' => 'name',
                                         'conditions' => ['Kiosks.status' => 1],
                                         'order' => ['Kiosks.name asc']
                                    ]);
        $kiosks_query = $kiosks_query->hydrate(false);
        $kiosk = $kiosks_query->toArray();

//		$options = ['conditions' =>
//                                [
//                                 'OrderDispute.kiosk_order_id' => $order_id,
//                                 'OrderDispute.kiosk_id' => $kiosk_id
//                                 ]
//                    ];
		  $orderDisputes_query = $this->OrderDisputes->find('all',
                                                         ['conditions' =>
                                                                        [
                                                                         'OrderDisputes.kiosk_order_id' => $order_id,
                                                                         'OrderDisputes.kiosk_id' => $kiosk_id
                                                                         ],
                                                                        'contain' => ['Products']
                                                        ]
                                          );
        if(!empty($orderDisputes_query)){
            $orderDisputes = $orderDisputes_query->toArray();
        } 
		$this->set('orderDisputes', $orderDisputes);
		$this->set(compact('kiosk','disputeOptions','approvalOptions'));
		
		//if(AuthComponent::user('group_id') == KIOSK_USERS && $orderDisputes['OrderDispute']['approval_status'] == 0){
		//	$this->Session->setFlash('Admin has not currently actioned on this request');
		//	return $this->redirect(array('action' => 'disputed_orders'));
		//}
		
		if ($this->request->is(array('post', 'put'))) {
           
			foreach($this->request->data['data'] as $key => $value){
                
				if(is_array($value)){
					if(array_key_exists('id',$value)){
                        
						if(array_key_exists('approval_status',$value)){
							$id = $value['id'];
							$adminRemarks = $value['admin_remarks'];
							$approvalStatus = $value['approval_status'];
							$admin_acted = date('Y-m-d H:i:s');
							$quantity = $value['quantity'];
							$product_id = $value['product_id'];
							$kiosk_id = $value['kiosk_id'];
							
							if($value['approval_status'] == 1){
								if($value['receiving_status'] == -1){
									//received less case approval
									//products table validation
                                    
									$tableName = "kiosk_{$kiosk_id}_products";
									$checkExistingQuantityQuery = "Select `quantity` from $tableName WHERE `$tableName`.`id` = $product_id";
									$conn = ConnectionManager::get('default');
								   $stmt = $conn->execute($checkExistingQuantityQuery); 
								   $existingQuantityData = $stmt ->fetchAll('assoc');
								   
									//$existingQuantityData = $this->Product->query($checkExistingQuantityQuery);
									$existingQuantity = $existingQuantityData['0']['quantity'];
									$remainingQuantity = $existingQuantity - $quantity;
									if($remainingQuantity < 0){
									   $this->Flash->error("Only $existingQuantity products available in Kiosk. Can't adjust the quantity at this moment!");
									   return $this->redirect(array('action'=>'disputed_orders'));
									}
								}elseif($value['receiving_status'] == 1){
									//received more case approval
									//products table validation
									$tableName = "products";
									$checkExistingQuantityQuery = "Select `quantity` from $tableName WHERE `$tableName`.`id` = $product_id";
									
									$conn = ConnectionManager::get('default');
								   $stmt = $conn->execute($checkExistingQuantityQuery); 
								   $existingQuantityData = $stmt ->fetchAll('assoc');
									
									
									$existingQuantity = $existingQuantityData['0']['quantity'];
									$remainingQuantity = $existingQuantity - $quantity;
									if($remainingQuantity < 0){
									   $this->Flash->error("Only $existingQuantity products available in Warehouse. Can't adjust the quantity at this moment!");
									   return $this->redirect(array('action'=>'disputed_orders'));
									}
								}
							}
							
							
							$userID = $this->request->session()->read('Auth.User.id');
							$adminRemarks = str_replace("'", "", $adminRemarks);
							//updating order disputes table
							$query = "UPDATE `order_disputes` SET `approval_by` = '$userID' ,`admin_remarks` = '$adminRemarks', `approval_status` = '$approvalStatus', `admin_acted` = '$admin_acted' WHERE `order_disputes`.`id` = $id";
							$conn = ConnectionManager::get('default');
						   $stmt = $conn->execute($query); 
							//$this->OrderDispute->query($query);
							if($approvalStatus == 0){
								
							}else{
								if($value['receiving_status'] == -1 &&
								$value['approval_status'] == 1){
								//received less case
									 //updating products table
									 $tableName = "kiosk_{$kiosk_id}_products";
									 $checkExistingQuantityQuery = "Select `quantity` from $tableName WHERE `$tableName`.`id` = $product_id";
									 
									 $conn = ConnectionManager::get('default');
								   $stmt = $conn->execute($checkExistingQuantityQuery); 
								   $existingQuantityData = $stmt ->fetchAll('assoc');
									 
									 //$existingQuantityData = $this->Product->query($checkExistingQuantityQuery);
									 $existingQuantity = $existingQuantityData['0']['quantity'];
									 $remainingQuantity = $existingQuantity - $quantity;
									 if($remainingQuantity < 0){
									$this->Flash->error("Only $existingQuantity products available in Kiosk. Can't adjust the quantity at this moment!");
									return $this->redirect(array('action'=>'disputed_orders'));
									 }
									 $kioskProductUpdateQuery = "UPDATE `$tableName` SET `quantity` = `quantity`-$quantity WHERE `$tableName`.`id` = $product_id";
									 $productUpdateQuery = "UPDATE `products` SET `quantity` = `quantity`+$quantity WHERE `products`.`id` = $product_id";
								   $conn = ConnectionManager::get('default');
								   $stmt = $conn->execute($kioskProductUpdateQuery); 
								   
								   $conn = ConnectionManager::get('default');
								   $stmt = $conn->execute($productUpdateQuery);
								   
								   $path = dirname(__FILE__);
								   $sites = Configure::read('sites');
								   foreach($sites  as $key => $value1){
										$fonerevive = strpos($path,$value1);
										if($fonerevive){
											 break;
										}
								   }
								   if($fonerevive != false){
										 $conn = ConnectionManager::get('hpwaheguru');
										 $stmt = $conn->execute($productUpdateQuery);
									}
								   
								}		
								 
								if($value['receiving_status'] == 1 &&
								$value['approval_status'] == 1){
									 //received more case
									 //updating products table
									 $tableName = "kiosk_{$kiosk_id}_products";
									 $kioskProductUpdateQuery = "UPDATE `$tableName` SET `quantity` = `quantity`+$quantity WHERE `$tableName`.`id` = $product_id";
									 $productUpdateQuery = "UPDATE `products` SET `quantity` = `quantity`-$quantity WHERE `products`.`id` = $product_id";
									 $conn = ConnectionManager::get('default');
								   $stmt = $conn->execute($kioskProductUpdateQuery); 
								   
								   $conn = ConnectionManager::get('default');
								   $stmt = $conn->execute($productUpdateQuery);
								   
								    $path = dirname(__FILE__);
								   $sites = Configure::read('sites');
								   foreach($sites  as $key => $value1){
										$fonerevive = strpos($path,$value1);
										if($fonerevive){
											 break;
										}
								   }
								   if($fonerevive != false){
										 $conn = ConnectionManager::get('hpwaheguru');	
										$checkExistingQuantityQuery = "Select `quantity` from `products` WHERE `products`.`id` = $product_id";
										
										$stmt = $conn->execute($checkExistingQuantityQuery);
										
										$existingQuantityData = $stmt ->fetchAll('assoc');
										$existingQuantity = $existingQuantityData['0']['quantity'];
										
										if($existingQuantity > 0){
											 $stmt = $conn->execute($productUpdateQuery);	 
										}
									}
								   
								   
									 $this->Flash->success("Quantity has been adjusted");
									// return $this->redirect(array('action'=>'disputed_orders'));
								}
							}
						}
					}
				}
			}
			return $this->redirect(array('action'=>'disputedOrders'));
			die;
		}
	}
	
	public function createInvoice(){
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
			$this->Flash->error("This function works only on hpwaheguru");
			return $this->redirect(array('controller' => 'home','action' => "dashboard"));die;
		}
		
		$userID = $this->request->session()->read('Auth.User.id');
		$vat = $this->VAT;
		$bulk_discount = 0;
		if(!empty($this->request->data)){
		  //pr($this->request);die;
			$from_date = $to_date = "";
			if(array_key_exists('from_date',$this->request->data['transfer_stock'])){
				$from_date = $this->request->data['transfer_stock']['from_date'];
				$startDate = date('Y-m-d', strtotime($from_date.'-1 day'));
			}
			if(array_key_exists('to_date',$this->request->data['transfer_stock'])){
				$to_date = $this->request->data['transfer_stock']['to_date'];
				$endDate = date('Y-m-d', strtotime($to_date.'+1 day'));
			}
			
			$external_site_id = $this->request->data['transfer_stock']['external_site_id_hidden'];
			$site_kiosk_id = $this->request->data['transfer_stock']['site_id_hidden'];
			
			if(!empty($from_date) && !empty($to_date)){
				$productCostPriceArr_query = $this->Products->find('list',
																		   [
																				'keyField' => 'id',
																				'valueField' => 'cost_price',
																		   ]
									 );
				$productCostPriceArr_query = $productCostPriceArr_query->hydrate(false);
				$productCostPriceArr = $productCostPriceArr_query->toArray();
				
				$productSellingPriceArr_query = $this->Products->find('list',
																				[
																				'keyField' => 'id',
																				'valueField' => 'selling_price',
																		   ]
										 );
				$productSellingPriceArr_query = $productSellingPriceArr_query->hydrate(false);
				$productSellingPriceArr = $productSellingPriceArr_query->toArray();
				
				$productCodeArr_query = $this->Products->find('list',
																		   [
																				'keyField' => 'id',
																				'valueField' => 'product_code',
																		   ]
										 );
				$productCodeArr_query = $productCodeArr_query->hydrate(false);
				$productCodeArr = $productCodeArr_query->toArray();
				
				$productQantityArr_query = $this->Products->find('list',
																		   [
																				'keyField' => 'id',
																				'valueField' => 'quantity',
																		   ]
										 );
				$productQantityArr_query = $productQantityArr_query->hydrate(false);
				$productQantityArr = $productQantityArr_query->toArray();
				
				$productCatgoryArr_query = $this->Products->find('list',
																		   [
																				'keyField' => 'id',
																				'valueField' => 'category_id',
																		   ]
										 );
				$productCatgoryArr_query = $productCatgoryArr_query->hydrate(false);
				$productCatgoryArr = $productCatgoryArr_query->toArray();
				
				if(array_key_exists('customer_Id',$this->request->data) && !empty($this->request->data['customer_Id'])){
					$customerId = $this->request->data['customer_Id'];
					$custData_query = $this->Customers->find('all',array(
														'conditions' => array('id' => $customerId)
														));
					$custData_query = $custData_query->hydrate(false);
					$custData = $custData_query->first();
					$agent_id = 0;
					if(!empty($custData)){
					//foreach($custData as $key => $value){
						 $country = $custData['country'];
						  $agent_id = $custData['agent_id'];
						$customerData = array(
											  'customer_id' => $custData['id'],
											  'fname' => $custData['fname'],
											  'lname' => $custData['lname'],
											  'email' => $custData['email'],
											  'mobile' => $custData['mobile'],
											  'address_1' => $custData['address_1'],
											  'address_2' => $custData['address_2'],
											  'city' => $custData['city'],
											  'state' => $custData['state'],
											  'zip' => $custData['zip'],
											  );
					//}
					}
					if($country == 'OTH'){
						$vat_applied = 0;
					}else{
						$vat_applied = $vat;
					}
					$recitdata = $customerData;
					//pr($customerData);die;
				}else{
					$this->Flash->error("please chosse a customer");
					return $this->redirect(array('action' => "transferred_stock"));die;
				}
				if(array_key_exists('bulk_discount',$this->request->data)){
					$bulk_discount = $this->request->data['bulk_discount'];
				}
				if(array_key_exists('transfer_stock',$this->request->data)){
					$understock = false;
					$surplus = false;
					$timestamp = time();
					$recitID = 0;
					$vat = $this->VAT;
					//pr($this->request);die;
					foreach($this->request->data['transfer_stock']['proID'] as $key => $value){
						//if(array_key_exists($key,$this->request->data['transfer_stock']['checkbox'])){
						//if($this->request->data['transfer_stock']['checkbox'][$key] == 1){
							$id = $this->request->data['transfer_stock']['id'][$key];//stock Id
							$productId = $this->request->data['transfer_stock']['proID'][$key];
							if(array_key_exists($key,$this->request->data['transfer_stock']['productCode'])){
								   $productCode = $this->request->data['transfer_stock']['productCode'][$key];
							}else{
							  $productCode = "";
							}
							if(array_key_exists($key,$this->request->data['transfer_stock']['origQty'])){
								   $orignalQtity = $this->request->data['transfer_stock']['origQty'][$key];
							}else{
							  $orignalQtity = "";
							}
							if(array_key_exists($key,$this->request->data['transfer_stock']['selectedQty'])){
								   $requestedQtity = $this->request->data['transfer_stock']['selectedQty'][$key];
							}else{
							  $requestedQtity = "";
							}
							
							if($id != "" && $productId != "" && $productCode !="" && $orignalQtity != "" && $requestedQtity != ""){
								   $costPerProduct = $productCostPriceArr[$productId];
								   $salePricePerProduct = $productSellingPriceArr[$productId];
								   
								   $productQtityInPrdtTble = $productQantityArr[$productId];
								   //formula for price without vat price = total*100/vat+100
								   $numerator = $salePricePerProduct*100;
								   $denominator = $vat+100;
								   $priceWithoutVat = $numerator/$denominator;
								   
								   
								   
								   if($requestedQtity < $orignalQtity){
									   $surplusQtity = $orignalQtity - $requestedQtity;
									   $surplusData = array(
															'invoice_reference' => $timestamp,
															'customer_id' => $customerId,
															'product_id' => $productId,
															'quantity' => $surplusQtity,
															'category_id' => $productCatgoryArr[$productId],
															'cost_price' => $costPerProduct,
															'sale_price' => $priceWithoutVat,
															'bulk_discount' => $bulk_discount,
															'vat_applied' => $vat_applied,
															);
									   $this->loadModel('TransferSurplus');
									   $TransferSurplus = $this->TransferSurplus->newEntity();
									   $TransferSurplus = $this->TransferSurplus->patchEntity($TransferSurplus, $surplusData,['validate' => false]);
									   
									   if($this->TransferSurplus->save($TransferSurplus)){  //
										   $surplus = true;
									   }
								   }
							   
								   $kioskProductSaleData[$productId] = array(
																			   'cost_price' => $costPerProduct,
																			   'sale_price' => $priceWithoutVat,
																			   'product_id' => $productId,
																			   'quantity' => $requestedQtity,
																			   'customer_id' => $customerId,
																			   'sold_by' => $userID,
																			   'kiosk_id' => 10000,
																			   'refund_price' => 0,
																			   'discount' => 0,
																			   'discount_status' => 0,
																			   'refund_gain' => 0,
																			   'refund_by' => 0,
																			   'status' => 1,
																			   'refund_status' => 0,
																		   );
							  }
						
					}
					
					if(!empty($kioskProductSaleData)){
						$result = $this->create_recit($kioskProductSaleData,$country,$bulk_discount,$recitdata,$userID);
						list($recitID,$finalAmt) = $result;
					}else{
						$this->Flash->success("please chosse a product");
						return $this->redirect(array('action' => "transferred_stock"));
					}
					
					if($recitID > 0){
						$total_data = count($kioskProductSaleData);
						$counter = 0;
						foreach($kioskProductSaleData as $prdId => $prdData){
							if($bulk_discount > 0){
								$bulk_value = $prdData['sale_price'] * ($bulk_discount/100);
								$after_bulk_value = $prdData['sale_price'] - $bulk_value;
								$selling_price_withot_vat = $after_bulk_value*$prdData['quantity'];
							}else{
									$after_bulk_value = $prdData['sale_price'];
									$selling_price_withot_vat = $after_bulk_value*$prdData['quantity'];
							}
							
							if($country != 'OTH'){
								$vat_amount = $after_bulk_value * ($vat/100);
								$total_vat = $vat_amount * $prdData['quantity'];
							}else{
								$total_vat = 0;
							}
							
							$prdData['product_receipt_id'] = $recitID;
						 $KioskProductSales = $this->KioskProductSales->newEntity();
						 $KioskProductSales = $this->KioskProductSales->patchEntity($KioskProductSales, $prdData,['validate' => false]);
							if($this->KioskProductSales->save($KioskProductSales)){ //
								$data = array(
												'quantity' => $prdData['quantity'],
												'product_code' => $productCodeArr[$prdId],
												'selling_price_withot_vat' => $selling_price_withot_vat,
												'vat' => $total_vat,
										   );
								$this->insert_to_ProductSellStats($prdId,$data,10000,$operations = '+',0,1);
								$reseveProductTable = 'reserved_products';
								   
								 if($external_site_id == 0){
										$updateQry1  = "UPDATE `$reseveProductTable` SET `status` = 1 WHERE `$reseveProductTable`.`product_id` = '$prdId' AND (DATE(`$reseveProductTable`.created) > '$startDate' AND DATE(`$reseveProductTable`.created)<'$endDate')"; 
								 }else{
								   if($site_kiosk_id == -1){
										$updateQry1  = "UPDATE `$reseveProductTable` SET `status` = 1 WHERE `$reseveProductTable`.`product_id` = '$prdId' AND (DATE(`$reseveProductTable`.created) > '$startDate' AND DATE(`$reseveProductTable`.created)<'$endDate') AND site_id = $external_site_id"; 
								   }else{
										$updateQry1  = "UPDATE `$reseveProductTable` SET `status` = 1 WHERE `$reseveProductTable`.`product_id` = '$prdId' AND (DATE(`$reseveProductTable`.created) > '$startDate' AND DATE(`$reseveProductTable`.created)<'$endDate') AND site_id = $external_site_id AND kiosk_id=$site_kiosk_id"; 
								   }
								 }
								 
								 
								   $conn = ConnectionManager::get('default');
								   $stmt = $conn->execute($updateQry1);
								   //$currentTimeInfo = $stmt ->fetchAll('assoc');
								
								$counter++;
							}
						}
						if($total_data == $counter){
							$paymentData = array(
												 'product_receipt_id' => $recitID,
												 'payment_method' => 'Bank Transfer',
												 'description' => 'stock invoice',
												 'amount' => $finalAmt,
												 'payment_status' => 1,
												 'status' => 1,
												 'agent_id' => $agent_id,
												 );
							$this->loadModel('PaymentDetails');
							
						 $PaymentDetails = $this->PaymentDetails->newEntity();
						 $PaymentDetails = $this->PaymentDetails->patchEntity($PaymentDetails, $paymentData,['validate' => false]);
							
							if($this->PaymentDetails->save($PaymentDetails)){
								if($surplus){
									$surplusTable = 'transfer_surplus';
									$updateQry = "UPDATE `$surplusTable` SET `product_receipt_id` = $recitID WHERE `$surplusTable`.`invoice_reference` = '$timestamp'";
									//$this->TransferSurplus->query($updateQry);
									$conn = ConnectionManager::get('default');
								   $stmt = $conn->execute($updateQry);
								}
								//die;
								$this->Flash->success("invoice created");
								return $this->redirect(array('action' => "transferred_stock"));
							}else{
								
							}
						}else{
							// kioskproductsale is not saved properly. action goes here
						}
					}else{
						// if recit id is not generated
					}
				}
			}else{
				$this->Flash->error("enter from and to date");
			    return $this->redirect(array('action' => "transferred_stock"));
			}
		}else{
			return $this->redirect(array('action' => "transferred_stock"));
		}
	}
	
	function create_recit($kioskProductSaleData = array(),$country = '',$bulk_discount = '',$recitdata = array(),$userID){
		$vat = $this->VAT;
		if(!empty($kioskProductSaleData)){
			$finalSalePrice = 0;
				$totalCST = 0;
			foreach($kioskProductSaleData as $k => $val){
				$costPrice = $val['cost_price'];
				$qantity = $val['quantity'];
				$totalCostPrice = $costPrice * $qantity;
				 $totalCST += $totalCostPrice;
				
				$withoutVatPrice = $val['sale_price'];
				$withvatPrice = $withoutVatPrice + ($withoutVatPrice*($vat/100));
				if($country == 'OTH'){
					$total_sale_price = $withoutVatPrice * $qantity;
				}else{
					$total_sale_price = $withvatPrice * $qantity;
				}
				
				$finalSalePrice += $total_sale_price;
			}
			
			if($bulk_discount >0){
				$discount = $finalSalePrice*($bulk_discount/100);
				$finalSalePrice = $finalSalePrice - $discount;
			}
			
			if($country == 'OTH'){
				$recitdata['vat'] = 0;
			}else{
				$recitdata['vat'] = $vat;
			}
			
			$recitdata['bulk_discount'] = $bulk_discount;
			$recitdata['bill_cost'] = $totalCST;
			$recitdata['bill_amount'] = $finalSalePrice;
			$recitdata['orig_bill_amount'] = $finalSalePrice;
			$recitdata['bulk_invoice'] = 1;
			$recitdata['processed_by'] = $userID;
			$recitdata['status'] = 0;
			//pr($recitdata);
			$this->loadModel('ProductReceipts');
		  $ProductReceipts = $this->ProductReceipts->newEntity();
		  $ProductReceipts = $this->ProductReceipts->patchEntity($ProductReceipts, $recitdata,['validate' => false]);
			if($this->ProductReceipts->save($ProductReceipts)){
				 $id = $ProductReceipts->id;
				 return array($id,$finalSalePrice);
			}else{
				return array(0,0);
			}
		}else{
			return array(0,0);
		}
	}
	public function cloneOrder(){
	 // pr($this->request->data); 
      if(array_key_exists('data',$this->request)){
			if(!empty($this->request->data)){
				if(array_key_exists('order_id',$this->request->data)){
                    
					if(array_key_exists('kiosk_id',$this->request->data['data'])){
                       	$this->request->data = $this->request->data;
					}
				}
			}
		}
		if(array_key_exists('order_id',$this->request->data)){
			   $order_id = $this->request->data['order_id']; 
		}else{
			return $this->redirect(array('controller' => 'kiosk_orders','action' => "transient_orders"));
		}
         
		$kiosk_id = array();
		$str_kiosk_id = "";
		if(array_key_exists('kiosk_id',$this->request->data['data'])){
			
             $kiosk_id = $this->request->data['data']['kiosk_id'];
				$str_kiosk_id = implode(",",$kiosk_id);
		}else{
			
		}
       
		if(!empty($kiosk_id)){
			 $total_kiosk = count($kiosk_id);
		}else{
			$msg = "Please Choose Kiosk";
              $this->Flash->success(__($msg));
			 
			return $this->redirect(array('action' => "view",$order_id));
		}
		
		$result_query = $this->StockTransfer->find('all',[
                                                          'conditions'=>['kiosk_order_id' => $order_id],
                                                           'contain' => ['Products']
														 //'recursive'=>-1,
														 ]);
        $result_query = $result_query->hydrate(false); 
        if(!empty($result_query)){
            $result = $result_query->toArray();
        }
      	$products = $qantity_requested = $avalable_qantity = array();
		
		
		foreach($result as $key => $value){
			$product_id = $value['product_id'];
			$quantity = $value['quantity'];
			$products[] = $value['product_id'];
			$qantity_requested[$product_id] = $quantity*$total_kiosk;
			//$avalable_qantity[$product_id] = $value['Product']['quantity'];
		}
		$isboloRam = false;
		 $path = dirname(__FILE__);
		  $sites = Configure::read('sites');
		  foreach($sites  as $key => $value){
			   $isboloRam = strpos($path,$value);
			   if($isboloRam){
					break;
			   }
		  }
		if($isboloRam != false){  // on boloram this code run
             $connection = ConnectionManager::get('hpwaheguru');
            $products_str = implode(",",$products);
            $avalable_qantity_query = "SELECT id,quantity From products where id IN ($products_str)";
            $stmt = $connection->execute($avalable_qantity_query);
            $avalable_qantity_res = $stmt ->fetchAll('assoc');
			$avalable_qantity = array();
			foreach($avalable_qantity_res as $key_s => $value_s){
			   $avalable_qantity[$value_s['id']] = $value_s['quantity'];
			}
            //$this->Products->setDataSource('default');
            $connection1 = ConnectionManager::get('default');
            $boloram_qantity_query = "SELECT id,quantity From products where id IN ($products_str)";
            $stmtbolram = $connection1->execute($boloram_qantity_query);
            $boloram_qantity_res = $stmtbolram ->fetchAll('assoc');
			foreach($boloram_qantity_res as $key_s1 => $value_s1){
			   $boloram_qantity[$value_s1['id']] = $value_s1['quantity'];
			}
            $this->set(compact('boloram_qantity'));
		}else{
			$avalable_qantity_query = $this->Products->find('list',[
                                                                   'conditions' => ['Products.id IN' => $products],
                                                                   'keyField' => 'id',
																 'valueField' => 'quantity',
                                                                   
                                                                  ]);
                                                                 
            if(!empty($avalable_qantity_query)){
                $avalable_qantity = $avalable_qantity_query->toArray();
            }else{
                $avalable_qantity = array();
            }
		}
		
$counter = 0;
		foreach($products as $p_key => $p_value){
			if($qantity_requested[$p_value] > $avalable_qantity[$p_value]){
				$counter++;
				$prodRS_query = $this->Products->find('all', array(
													'fields' => 'product_code',
													'conditions' => array('id' => $p_value),
													'recursive' => -1));
                 
                    if(!empty($prodRS_query)){
                         $prodRS = $prodRS_query->first();
                    }else{
                        $prodRS = array();
                    }
				 $prodCode = $prodRS['product_code']; 
				break;
			}
                            

		}
		//pr($prodRS);die;
		$kiosks_query = $this->Kiosks->find('list',[
                                                        'keyField' => 'id',
                                                        'valueField' => 'name',
								                        'conditions' => ['Kiosks.status' => 1],
                                                        'order' => 'Kiosks.name asc' 
								 
							]
					     );
        if(!empty($kiosks_query)){
            $kiosks = $kiosks_query->toArray();
        }else{
            $kiosks = array();
        }
		
		$change_order = 1;
		if($counter > 0){
			$change_order = 0;
              $this->Flash->success(__("Requested qantity is not avalable for product with product code :$prodCode. Please change your order"));
 
		}
		//pr($result);die;
		$this->set(compact('result','qantity_requested','avalable_qantity','change_order','products','order_id','kiosk_id','str_kiosk_id','kiosks'));
	}
    
    public function placeOrder(){
		if($this->request->is(array('post', 'put'))){
         // pr($this->request->data); die;
			if(array_key_exists('order_id',$this->request->data)){
				$order_id = $this->request->data['order_id'];
			}
			$kiosk_id_str = "";$kiosk_ids = array();
			if(array_key_exists('kiosk_id',$this->request->data)){ //
				$kiosk_id_str = $this->request->data['kiosk_id'];
				if(!empty($kiosk_id_str)){
					$kiosk_ids = explode(",",$kiosk_id_str);
				}
			}
			$total_kiosk = 0;
			if(!empty($kiosk_ids)){
				$total_kiosk = count($kiosk_ids);
			}else{
				$msg = "Please Choose Kiosk";
				$this->Session->setFlash($msg);
				return $this->redirect(array('action' => "view",$order_id));
			}
			$order_detail = $product_ids = array();
			if(array_key_exists('placedorder',$this->request->data)){
				foreach($this->request->data['placedorder'] as $key => $value){
					$order_detail[$key] = $value;
					$product_ids[] = $key;
				}
			}
			
			if(!empty($product_ids)){
					$isboloRam = false;
					$path = dirname(__FILE__);
					$sites = Configure::read('sites');
					foreach($sites  as $key => $value){
						 $isboloRam = strpos($path,$value);
						 if($isboloRam){
							  break;
						 }
					}
				if($isboloRam != false){  // this runs on boloram
                    $connection = ConnectionManager::get('hpwaheguru');
                    $product_ids = implode(",",$product_ids);
                    $product_detail_query = "SELECT * From products where id IN ($product_ids)";
					//echo $product_detail_query;die;
                    $stmt = $connection->execute($product_detail_query);
                    $product_detail = $stmt ->fetchAll('assoc');
                    $connection1 = ConnectionManager::get('default');
					
				}else{ // this run on ADMIN_DOMAIN
					$product_detail_query = $this->Products->find('all',array('conditions' => array('Products.id IN' => $product_ids),
													   'recursive' => -1,
													   ));
                    $product_detail_query = $product_detail_query->hydrate(false);
                    if(!empty($product_detail_query)){
                        $product_detail = $product_detail_query->toArray();
                    }else{
                        $product_detail = array();
                    }
                  //  pr($product_detail);die;
                    
				}
				
				$counter = 0;
				//pr($product_detail);die;
				foreach($product_detail as $p_key => $p_detail){    // on boloram $product_detail have result of wheguru as we are checking waheguru qantity to subtract
					$org_quantity = $p_detail['quantity'];
					$product_id = $p_detail['id'];
					$code_list_query = $this->Products->find('list',[
                                                                'conditions' => [ 'Products.id' => $product_id],
                                                                'keyField' => 'id',
                                                                'valueField' => 'product_code'
                                                            ]
                                                      );
                    if(!empty($code_list_query)){
                        $code_list = $code_list_query->toArray();
                    }else{
                        $code_list = array();
                    }
                                                                    
					if($order_detail[$product_id]%$total_kiosk != 0){
						//echo "hi";
						$counter++;
						$more_qantity_product[] = $code_list[$product_id];
					}else{
						$order_detail[$product_id] = $order_detail[$product_id]/$total_kiosk;
					}
					if(array_key_exists($product_id,$order_detail)){
						if($org_quantity < $order_detail[$product_id]){
							$counter++;
							$more_qantity_product[] = $code_list[$product_id];
						}
					}
				}
				//pr($order_detail);die;
				if($counter > 0){
                   // echo "ll";die;
					$data = array();
					$data['order_id'] = $order_id;
					$data['kiosk_id'] = $kiosk_ids;
					$msg = "Either Qantity is not sufficent or Qantity is not divided properly b/w kiosk for products ".implode(",",$more_qantity_product);
					$this->request->data = $data;
					$this->Flash->success($msg);
					return $this->redirect(array('controller' => 'StockTransfer','action' => "clone_order",'order_id' => $order_id,'kiosk_id' => $kiosk_ids));die;
				}else{
					 //pr($order_detail);die;
					$countTransferred = 0;
					$prodtCatArr_query = $this->Products->find('list',[
                                                                    'keyField' => 'id',
                                                                    'valueField' => 'category_id'
                                                            ]
                                                      );
                    if(!empty($prodtCatArr_query)){
                        $prodtCatArr = $prodtCatArr_query->toArray();
                    }else{
                        $prodtCatArr = array();
                    }
                                                               
					foreach($kiosk_ids as $k_key => $k_value){
						$datetime = date('Y-m-d H:i:s');
						$kioskOrderData = array(
									'kiosk_id' => $k_value,
									'user_id' => $this->request->Session()->read('Auth.User.id'),
									'dispatched_on' => $datetime,
									'status' => 1
									);
                        
						//$this->KioskOrder->create();
                        $KioskOrderEntity = $this->KioskOrders->newEntity($kioskOrderData,['validate' => false]);
						$KioskOrderPatchEntity = $this->KioskOrders->patchEntity($KioskOrderEntity,$kioskOrderData,['validate' => false]);
                       // pr($KioskOrderPatchEntity);die;
						$this->KioskOrders->save($KioskOrderPatchEntity);
						 $kiosk_order_id = $KioskOrderPatchEntity->id; 
						foreach($order_detail as $product_id => $qntity_req){
							$cost_price_list_query = $this->Products->find('list',[
                                                                                'conditions' =>['Products.id' => $product_id],
                                                                                'keyField' => 'id',
                                                                                'valueField' => 'cost_price'
                                                                             ]);
                            if(!empty($cost_price_list_query)){
                                $cost_price_list = $cost_price_list_query->toArray();
                            }else{
                                $cost_price_list = array();
                            }
							$selling_price_list_query = $this->Products->find('list',[
                                                                                 'conditions' =>['Products.id' => $product_id],
                                                                                'keyField' => 'id',
                                                                                'valueField' => 'selling_price'
                                                                               ]);
                            if(!empty($selling_price_list_query)){
                                $selling_price_list = $selling_price_list_query->toArray();
                            }else{
                                $selling_price_list = array();
                            }
                            
							//$this->StockTransfer->clear;
							//$this->StockTransfer->create();
							$stockTransferData = array(
														'kiosk_order_id' => $kiosk_order_id,
														'kiosk_id' => $k_value,
														'product_id' => $product_id,
														'quantity' => $qntity_req,
														'cost_price' => $cost_price_list[$product_id],
														'static_cost' => $cost_price_list[$product_id],
														'sale_price' => $selling_price_list[$product_id],
														'status' => '1',
														'remarks' => "Bulk Clone",
													);
                             $stockTransferDataEntity = $this->StockTransfer->newEntity($stockTransferData,['validate' => false]);
                            $stockTransferData = $this->StockTransfer->patchEntity($stockTransferDataEntity,$stockTransferData,['validate' => false]);
                            // pr($KioskOrderPatchEntity);die;
                         
							if($this->StockTransfer->save($stockTransferData)){
								$countTransferred++;
								$kioskTable = "kiosk_transferred_stock_$k_value";
								$tableQuery = $this->TableDefinition->get_table_defination('transferred_stock',$k_value);
                                $conn = ConnectionManager::get('default');
                                $stmt = $conn->execute($tableQuery);
                                 $statement = $conn->insert($kioskTable, 
                                                                [
                                                                   'kiosk_order_id' => $kiosk_order_id,
                                                                    'product_id' => $product_id,
                                                                    'quantity' => $qntity_req,
                                                                   'sale_price' => $selling_price_list[$product_id],
                                                                    'created' => '$datetime',
                                                                    'status' => 1
                                                                ]
                                                            );
								if($isboloRam != false){  // on boloram this code run
									// firstly subtract from ADMIN_DOMAIN then from boloram
                                    $connection = ConnectionManager::get('hpwaheguru');
									
									//$this->Product->id = $product_id;
									$productData = array('quantity' => "Product.quantity - $qntity_req");
									$update_query_s = "UPDATE `products` SET `quantity` = `quantity` - $qntity_req WHERE `products`.`id` = $product_id";
									 $stmt = $connection->execute($update_query_s);
									 
									//$this->Product->setDataSource('default');
									$connection = ConnectionManager::get('default');
									 
									$boloram_qantity_query = $this->Products->find('list',array('conditions' =>
																								array('Products.id' => $product_id),
																								'keyField' => 'id',
																								'valueField' => 'quantity',
																								//'fields' => array('id','quantity')
																								));
									$boloram_qantity_query = $boloram_qantity_query->hydrate(false);
									if(!empty($boloram_qantity_query)){
										$boloram_qantity = $boloram_qantity_query->toArray();
									}else{
										$boloram_qantity =array();
									}
									$check_res = $boloram_qantity[$product_id] - $qntity_req;
									if($check_res < 0){   // if qantity on boloram is not sufficent to the requested quantity then we set qantity to zero
										//$this->Product->recursive = -1;
										//$this->Product->id = $product_id;
										$productData = array('quantity' => "Product.quantity - $qntity_req");
										//$this->Product->query("UPDATE `products` SET `quantity` = 0 WHERE `products`.`id` = $product_id");
                                        $update_query = "UPDATE `products` SET `quantity` = 0 WHERE `products`.`id` = $product_id";
                                        $stmt = $connection->execute($update_query);
									}else{ // normally subtract from boloram
										//$this->Product->recursive = -1;
										//$this->Product->id = $product_id;
										$productData = array('quantity' => "Product.quantity - $qntity_req");
                                        $update_query = "UPDATE `products` SET `quantity` = `quantity` - $qntity_req WHERE `products`.`id` = $product_id";
                                        $stmt = $connection->execute($update_query);
										//$this->Product->query("UPDATE `products` SET `quantity` = `quantity` - $qntity_req WHERE `products`.`id` = $product_id");
									}
									//$this->ReservedProducts->setDataSource('ADMIN_DOMAIN_db');
									$rserveResult_query = $this->ReservedProducts->find('all',array(
																		  'conditions' => array('product_id' => $product_id,
																							   'DATE(created) = CURDATE()'
																							   ),
																		  //'recursive'=>-1,
																		  ));
                                    $rserveResult_query = $rserveResult_query->hydrate(false);
                                    if(!empty($rserveResult_query)){
                                        $rserveResult = $rserveResult_query->toArray();
                                    }else{
                                        $rserveResult = array();
                                    }
									if(count($rserveResult) > 0){
										foreach($rserveResult as $key => $value){
											$reserveId =  $value['id'];
											$oldQtity = $value['quantity'];
										}
										//$this->ReservedProducts->query("UPDATE `reserved_products` SET `quantity` = `quantity` + $qntity_req WHERE `reserved_products`.`id` = $reserveId");
                                        $update_query1 = "UPDATE `reserved_products` SET `quantity` = `quantity` + $qntity_req WHERE `reserved_products`.`id` = $reserveId";
                                        $stmt1 = $connection->execute($update_query1);
                                    }else{
										$reservedProductData = array(
																 'product_id' =>  $product_id,
																 'category_id' => $prodtCatArr[$product_id],
																 'quantity' => $qntity_req,
																 'cost_price' => $cost_price_list[$product_id],
																 'sale_price' => $selling_price_list[$product_id],
																 );
                                        $reservedProductDataEntity = $this->ReservedProducts->newEntity($reservedProductData,['validate' => false]);
                                        $reservedProductData = $this->ReservedProducts->patchEntity($reservedProductDataEntity,$reservedProductData,['validate' => false]);
										//$this->ReservedProducts->clear();
										//$this->ReservedProducts->create();
										$this->ReservedProducts->save($reservedProductData);
									}
									//$this->ReservedProducts->setDataSource('default');
								}else{  // on hpwaheguru this code run    // normal subtract
                                  $connection = ConnectionManager::get('default');
									//$this->Product->recursive = -1;
									// $this->Products  = $product_id;
                                   
									$productData = array('quantity' => "Products.quantity - $qntity_req");
                                    $update_query = "UPDATE `products` SET `quantity` = `quantity` - $qntity_req WHERE `products`.`id` = $product_id";
								 	$stmt = $connection->execute($update_query);
									
									$rserveResult_query = $this->ReservedProducts->find('all',array(
																		  'conditions' => array('product_id' => $product_id,
																							   'DATE(created) = CURDATE()'
																							   ),
																		  //'recursive'=>-1,
																		  ));
                                    $rserveResult_query = $rserveResult_query->hydrate(false);
                                    if(!empty($rserveResult_query)){
                                        $rserveResult = $rserveResult_query->toArray();
                                    }else{
                                        $rserveResult = array();
                                    }
                                  //pr($rserveResult);die;
									if(count($rserveResult) > 0){
										foreach($rserveResult as $key => $value){
											$reserveId =  $value['id'];
											$oldQtity = $value['quantity'];
										}
                                        $update_query1 = "UPDATE `reserved_products` SET `quantity` = `quantity` + $qntity_req WHERE `reserved_products`.`id` = $reserveId";
                                        $stmt1 = $connection->execute($update_query1);
										//$this->ReservedProducts->query();
									}else{
										$reservedProductData = array(
																 'product_id' =>  $product_id,
																 'category_id' => $prodtCatArr[$product_id],
																 'quantity' => $qntity_req,
																 'cost_price' => $cost_price_list[$product_id],
																 'sale_price' => $selling_price_list[$product_id],
																 );
										 $reservedProductDataEntity = $this->ReservedProducts->newEntity($reservedProductData,['validate' => false]);
                                         $reservedProductData = $this->ReservedProducts->patchEntity($reservedProductDataEntity,$reservedProductData,['validate' => false]);
										//$this->ReservedProducts->clear();
										//$this->ReservedProducts->create();
										$this->ReservedProducts->save($reservedProductData);
										 
									}
								}
							}
						}
					}
                    
                   
					if($countTransferred >0 ){ 
						$msg = "product transfered";
						 $this->Flash->success($msg);
						return $this->redirect(array('controller'=>'kiosk_orders','action' => "transient_orders"));
					}else{
						$msg = "Error";
                        $this->Flash->success($msg);
						//$this->Session->setFlash($msg);
						return $this->redirect(array('controller'=>'kiosk_orders','action' => "transient_orders"));
					}
				}
			}
		} 
	}
    
    
	public function restoreSession($currentController = '', $currentAction = '', $session_key = '', $kioskId = '', $redirectAction = ''){
		if(!$redirectAction){
		    $redirectAction = $currentAction;
		}
		$status1 = $this->SessionRestore->restore_from_session_backup_table($currentController, $currentAction, 'tKioskID', $kioskId);
		//rasu later
		$status = $this->SessionRestore->restore_from_session_backup_table($currentController, $currentAction, $session_key, $kioskId);
		
		if($status == 'Success'){
		    $msg = "Session succesfully retreived!";
		}else{
		    $msg = "Session could not be retreived!";
		}
		$this->Flash->success($msg);
		
		return $this->redirect(array('action' => $redirectAction));
	}
	
	public function summarySale(){
		$selectedKiosk = '';
        //pr($this->request->query);die;
		if(!empty($this->request->query['kiosk'])){
			$selectedKiosk = $this->request->query['kiosk'];
		}
		
		$from_date = '';
		$startDate = '';
		if(!empty($this->request->query['from_date'])){
			$from_date = $this->request->query['from_date'];
			$startDate = date("Y-m-d",strtotime($from_date.'-1 day'));
		}
		
		$to_date = '';
		$endDate = '';
		if(!empty($this->request->query['to_date'])){
			$to_date = $this->request->query['to_date'];
			$endDate = date("Y-m-d",strtotime($to_date.'+1 day'));
		}
		
		//kiosk to warehouse
		//$this->KioskOrders->setSource("center_orders");
        $KioskOrders_source = "center_orders";
        $KioskOrdersTable = TableRegistry::get($KioskOrders_source,[
                                                                    'table' => $KioskOrders_source,
                                                                ]);
		
		
		// getting details of disputed order
		$total_dispute_cost = 0;
		if($selectedKiosk != 10000){
			   $order_dispute_res = $this->OrderDisputes->find("all",[
												 'conditions' => [
													'kiosk_id' => $selectedKiosk,
													"DATE(admin_acted)>'$startDate'","DATE(admin_acted)<'$endDate'",
													'approval_status' => 1
												 ]
												 ])->toArray();
			  // pr($order_dispute_res);die;
			   $order_dispute = array();
			   if(!empty($order_dispute_res)){
				 foreach($order_dispute_res as $key => $value){
				   $product_id = $value->product_id;
				   $reciving_status = $value->receiving_status;
				   $quantity = $value->quantity;
				   
				   $cost_price = $value->cost_price;
				   $sale_price = $value->sale_price;
				   
				   $order_dispute[][$value->product_id] = array('receiving_status' => $reciving_status,
																 'quantity' => $quantity,
																 'cost_price' => $cost_price,
																 'sale_price' => $sale_price,
																);
				   
				   
				//   if(array_key_exists($value->product_id,$order_dispute) && array_key_exists("quantity",$order_dispute[$value->product_id])){
				//		 $order_dispute[$value->product_id]['quantity'] += $quantity;
				//	}else{
				//		 $order_dispute[$value->product_id]['quantity'] = $quantity;
				//	}
				
				
				   
				 }
				 
			   }
			   
			   $products = $this->Products->find("list",[
											 'keyField' => "id",
											 'valueField' => "cost_price",
											 ])->toArray();
			   $total_dispute_cost_static = 0;
			   if(!empty($order_dispute)){
				$recive_more_cost_static =  $recive_less_cost_static = $recive_less_cost = $recive_more_cost = 0;
				 foreach($order_dispute as $temp_key => $temp_value){
					foreach($temp_value as $p_id => $summary_arr){
					  if($summary_arr['receiving_status'] == -1){
						   $recive_less_cost += $products[$p_id] * $summary_arr['quantity'];
						   $recive_less_cost_static += $summary_arr['cost_price'] * $summary_arr['quantity'];
					  }else{
						   $recive_more_cost += $products[$p_id] * $summary_arr['quantity'];
						   $recive_more_cost_static += $summary_arr['cost_price'] * $summary_arr['quantity'];
					  }
					}
						 //echo   $recive_less_cost;echo "</br>";
				 }
				 
				 $total_dispute_cost = $recive_less_cost - $recive_more_cost;
				 $total_dispute_cost_static = $recive_less_cost_static - $recive_more_cost_static;
			   }
		}else{
		  $total_dispute_cost_static =0;
		}
		// getting details of disputed order
		
		
		//getting stock details transferred by kiosk to warehouse
		$transferredByKiosk = array();
		//$this->StockTransfers->setSource('stock_transfer_by_kiosk');
        $StockTransfers_source = "stock_transfer_by_kiosk";
        $StockTransfersTable = TableRegistry::get($StockTransfers_source,[
                                                                    'table' => $StockTransfers_source,
                                                                ]);
		
		$trnsfrdByKioskOrderIds_query = $StockTransfersTable->find('list',[
                                                                     'conditions'=>["DATE(created)>'$startDate'","DATE(created)<'$endDate'"],
                                                                     'keyField' => 'id',
                                                                     'valueField' => 'kiosk_order_id',
                                                                     //'recursive'=>-1
                                                                    ]);
        if(!empty($trnsfrdByKioskOrderIds_query)){
            $trnsfrdByKioskOrderIds = $trnsfrdByKioskOrderIds_query->toArray();
        }else{
            $trnsfrdByKioskOrderIds = array();
        }
		
		if(empty($trnsfrdByKioskOrderIds)){
            $trnsfrdByKioskOrderIds = array(0 => null);
        }
		
		$checkIfBelong2Kiosk_query = $KioskOrdersTable->find('list',[
                                                               'keyField' => 'id',
                                                               'valueField' => 'id',
                                                               'conditions'=>['kiosk_id'=>$selectedKiosk, //'id IN'=>$trnsfrdByKioskOrderIds,
																			  "Date(received_on)>'$startDate'","Date(received_on)<'$endDate'",
																			  'status' => 2
																			  ],
                                                               //'recursive'=>-1
                                                              ]);
		
        if(!empty($checkIfBelong2Kiosk_query)){
            $checkIfBelong2Kiosk = $checkIfBelong2Kiosk_query->toArray();
        }else{
            $checkIfBelong2Kiosk = array();
        }
		
		if(empty($checkIfBelong2Kiosk)){
            $checkIfBelong2Kiosk = array(0 =>null);
        }
		$transferredByKiosk_query = $StockTransfersTable->find('all',array('conditions'=>array('kiosk_order_id IN'=>$checkIfBelong2Kiosk),'fields'=>array('product_id','sale_price','quantity','cost_price')));
		$transferredByKiosk_query = $transferredByKiosk_query->hydrate(false);
		if(!empty($transferredByKiosk_query)){
            $transferredByKiosk = $transferredByKiosk_query->toArray();
        }else{
            $transferredByKiosk = array();
        }
		$productIds = array();
		$costStockTransByKiosk = 0;
		if(!empty($transferredByKiosk)){
			foreach($transferredByKiosk as $key=>$byKiosk){
				$costStockTransByKiosk+=floatval($byKiosk['cost_price'] * $byKiosk['quantity']);
				$productIds[$byKiosk['product_id']] = $byKiosk['product_id'];
			}
		}
		
		//$this->KioskOrders->setSource("kiosk_orders");
        $KioskOrders_source = "kiosk_orders";
        $KioskOrdersTable = TableRegistry::get($KioskOrders_source,[
                                                                    'table' => $KioskOrders_source,
                                                                ]);
		$transferredByWarehouse = array();
		//$this->StockTransfers->setSource('stock_transfer');
		$StockTransfers_source = "stock_transfer";
        $StockTransfersTable = TableRegistry::get($StockTransfers_source,[
                                                                    'table' => $StockTransfers_source,
                                                                ]);
		$trnsfrd2KioskOrderIds = $StockTransfersTable->find('list',[
                                                                    'conditions'=>["DATE(created)>'$startDate'","DATE(created)<'$endDate'"],
                                                                    'keyField' => 'id',
                                                                    'valueField' => 'kiosk_order_id',
                                                                    //'recursive'=>-1
                                                                   ]);
		if(!empty($trnsfrd2KioskOrderIds)){
            $trnsfrd2KioskOrderIds = $trnsfrd2KioskOrderIds->toArray();
        }else{
            $trnsfrd2KioskOrderIds = array();
        }
        if(empty($trnsfrd2KioskOrderIds)){
            $trnsfrd2KioskOrderIds = array( 0 => null);
        }
		$checkIfBelongToKiosk = $KioskOrdersTable->find('list',[
                                                                'keyField' => 'id',
                                                                'valueField' => 'id',
                                                                'conditions'=>array('kiosk_id'=>$selectedKiosk, 'id IN'=>$trnsfrd2KioskOrderIds),
                                                                //'recursive'=>-1
                                                               ]);
        if(!empty($checkIfBelongToKiosk)){
            $checkIfBelongToKiosk = $checkIfBelongToKiosk->toArray();
        }else{
            $checkIfBelongToKiosk = array();
        }
		//pr($checkIfBelongToKiosk);
		if(empty($checkIfBelongToKiosk)){
            $checkIfBelongToKiosk = array(0 => null);
        }
		$transferredByWarehouse = $StockTransfersTable->find('all',array('conditions'=>array('kiosk_order_id IN'=>$checkIfBelongToKiosk),'fields'=>array('product_id','sale_price','quantity','cost_price')));
		$transferredByWarehouse = $transferredByWarehouse->hydrate(false);
		if(!empty($transferredByWarehouse)){
            $transferredByWarehouse = $transferredByWarehouse->toArray();
        }else{
            $transferredByWarehouse = array();
        }		
		$costTransByWh = 0;
		if(!empty($transferredByWarehouse)){
			foreach($transferredByWarehouse as $key=>$byWarehouse){
				$costTransByWh+=floatval($byWarehouse['cost_price'] * $byWarehouse['quantity']);
				$productIds[$byWarehouse['product_id']] = $byWarehouse['product_id'];
			}
		}
		
		$repairIdsArray = array();
		
		$repairIdsData = $this->MobileRepairLogs->find('list',
											[
                                                'conditions' => [
                                                'MobileRepairLogs.repair_status' => DISPATCHED_2_KIOSK_REPAIRED,
                                                "DATE(MobileRepairLogs.created) > '$startDate'",
                                                "DATE(MobileRepairLogs.created) < '$endDate'",
                                                "kiosk_id" => (int)$selectedKiosk, //Added by rajiv
                                                ],
                                                'keyField' => 'mobile_repair_id',
                                                'valueField' => 'status',
                                                'order' => ['MobileRepairLogs.id asc']
                                            ]);
        if(!empty($repairIdsData)){
            $repairIdsData = $repairIdsData->toArray();
        }else{
            $repairIdsData = array();
        }
		$repairIds = array_keys($repairIdsData);
		$repairIdsArray = $repairIds;
		//getting brand id, model id, problem type from mobile repair table for above ids
		$repairDetail = array();
		if(count($repairIds)){
			$repairDetail_query = $this->MobileRepairs->find('all',array('conditions'=>array('MobileRepairs.id IN' => $repairIds),'fields'=>array('id','brand_id','mobile_model_id','problem_type','net_cost')));
            $repairDetail_query = $repairDetail_query->hydrate(false);
            if(!empty($repairDetail_query)){
                $repairDetail = $repairDetail_query->toArray();
            }else{
                $repairDetail = array();
            }
		}
		
		$fixedRepairCost = 0;
		//getting cost price corresponding to the brand,model,problem combination
		$repairCostArr = array();
		if(!empty($repairDetail)){
			foreach($repairDetail as $key=>$repairInfo){
				$fixedRepairCost+=floatval($repairInfo['net_cost']);
				$repairId = $repairInfo['id'];
				$brand_id = $repairInfo['brand_id'];
				$mobile_model_id = $repairInfo['mobile_model_id'];
				$problemTypeArr = explode('|',$repairInfo['problem_type']);
				
				$repairCostArr_query = $this->MobileRepairPrices->find('all',array('conditions'=>array('MobileRepairPrices.brand_id'=>$brand_id,'MobileRepairPrices.mobile_model_id'=>$mobile_model_id,'MobileRepairPrices.problem_type IN'=>$problemTypeArr),'fields'=>array('MobileRepairPrices.repair_cost')));
                $repairCostArr_query = $repairCostArr_query->hydrate(false);
                if(!empty($repairCostArr_query)){
                    $repairCostArr[$repairId] = $repairCostArr_query->toArray();
                }else{
                    $repairCostArr[$repairId] = array();
                }
			}
		}
		$sumRepairCost = 0;
		$sumRefundRepair = 0;
		
		//pr($repairCostArr);die;
		if(!empty($repairIdsArray)){
			foreach($repairIdsArray as $rk => $repairIdIn){
				//if($repairIdAmnt==0){continue;}//ignoring the case for rebooked
				$repairCost = 0;
				if(array_key_exists($repairIdIn,$repairCostArr)){
					foreach($repairCostArr[$repairIdIn] as $key=>$costInfo){
						if(is_array($costInfo) && array_key_exists('repair_cost',$costInfo)){
							$repairCost+=$costInfo['repair_cost'];
						}
					}
					$sumRepairCost+=$repairCost;
				}
			}
		}
				
		//Addition by Rajiv on 5th Feb, 2016
		$unlockIds = $this->MobileUnlockLogs->find('list',[
                                                           'conditions' => [
                                                                'MobileUnlockLogs.unlock_status IN' => array(UNLOCK_PROCESSED_CONFIRMATION_SENT_2_KIOSK, DISPATCHED_2_KIOSK_UNLOCKED),
                                                                "DATE(MobileUnlockLogs.created)>'$startDate'",
                                                                "DATE(MobileUnlockLogs.created) < '$endDate'",
                                                                "kiosk_id" => (int)$selectedKiosk,
                                                                ],
                                                                'keyField' => 'id',
                                                                'valueField' => 'mobile_unlock_id',
                                                          ]);
        if(!empty($unlockIds)){
            $unlockIds = $unlockIds->toArray();
        }else{
            $unlockIds = array();
        }
		$unlockIdsArray = $unlockIds;
		
		
		$unlockDetail = array();
		if(count($unlockIds)){
			$unlockDetail_query = $this->MobileUnlocks->find('all',array('conditions' => array('MobileUnlocks.id IN' => $unlockIds),'fields' => array('id','brand_id', 'mobile_model_id', 'network_id','net_cost')));
            $unlockDetail_query = $unlockDetail_query->hydrate(false);
            if(!empty($unlockDetail_query)){
                $unlockDetail = $unlockDetail_query->toArray();
            }else{
                $unlockDetail = array();
            }
		}
		
		$fixedUnlockCost = 0;
		//getting cost price corresponding to the brand,model,network combination
		$unlockCostArr = array();
		if(!empty($unlockDetail)){
			foreach($unlockDetail as $key => $unlockInfo){
				$fixedUnlockCost+=floatval($unlockInfo['net_cost']);
				//pr($unlockInfo);die;
				$unlockId = $unlockInfo['id'];
				$brand_id = $unlockInfo['brand_id'];
				$mobile_model_id = $unlockInfo['mobile_model_id'];
				$network_id = $unlockInfo['network_id'];
				
				$unlockCostArr_query = $this->MobileUnlockPrices->find('all', array('conditions' => array('MobileUnlockPrices.brand_id' => $brand_id, 'MobileUnlockPrices.mobile_model_id' => $mobile_model_id,'MobileUnlockPrices.network_id' => $network_id),'fields'=>array('MobileUnlockPrices.unlocking_cost')));
                $unlockCostArr_query = $unlockCostArr_query->hydrate(false);
                if(!empty($unlockCostArr_query)){
                    $unlockCostArr[$unlockId] = $unlockCostArr_query->toArray();
                }else{
                    $unlockCostArr[$unlockId] = array();
                }
			}
		}
		
		$sumUnlockCost = 0;
		$sumRefundCost = 0;
		$unlockIdsArray = array_unique($unlockIdsArray);
		
		//getting unlock ids from the unlock sale table
		$unlockIdsList = $this->MobileUnlockSales->find('list',[
                                                                    'keyField' => 'id',
                                                                    'valueField' => 'mobile_unlock_id',
                                                                    'conditions' => array("DATE(MobileUnlockSales.created)>'$startDate'","DATE(MobileUnlockSales.created)<'$endDate'",
                                                                                  'MobileUnlockSales.refund_status'=>0,
                                                                                  'MobileUnlockSales.kiosk_id'=>$selectedKiosk),
                                                                                  //'recursive' => -1
                                                              ]);
        if(!empty($unlockIdsList)){
            $unlockIdsList = $unlockIdsList->toArray();
        }else{
            $unlockIdsList = array();
        }
		if(count($unlockIds)){
			$refundedUnlocks = array();
			foreach(array_unique($unlockIds) as $unlockListID){
				$t_unlock = array();
				$t_unlock_query = $this->MobileUnlockSales->find('all',array(
						'conditions'=>array('MobileUnlockSales.mobile_unlock_id' => $unlockListID
								 ),
						'fields' => array('mobile_unlock_id', 'refund_status'),
						'order' => 'MobileUnlockSales.id DESC',
						'limit' => 1,
						)
					   );
                $t_unlock_result = $t_unlock_query->first();
                if(!empty($t_unlock_result)){
                    $t_unlock = $t_unlock_result->toArray();
                }else{
                    $t_unlock = array();
                }
                if(array_key_exists('refund_status',$t_unlock) && $t_unlock['refund_status'] == 1){
                    $refundedUnlocks[] = $t_unlock['mobile_unlock_id'];
                }
			}
            
			$refundedUnlocks = array();//we do not need to subtract refund
			$resultUnlockIDs = array_diff($unlockIdsArray,$refundedUnlocks);
			//pr($resultUnlockIDs); //rasu
			$refundedUnlocks = array();//we do not need to sub
			if(!empty($resultUnlockIDs)){
				foreach($resultUnlockIDs as $rk => $unlockIdIn){
					//pr($unlockIdIn);die;
					if(array_key_exists($unlockIdIn, $unlockCostArr)){
                       if(!empty($unlockCostArr[$unlockIdIn])){
						$unlockCostPrice = $unlockCostArr[$unlockIdIn][0]['unlocking_cost'];
						$sumUnlockCost+=$unlockCostPrice;
                       }
					}
				}
			}
            if(empty($unlockIdsList)){
                $unlockIdsList = array(0 => null);
            }
			$unlockData = $this->MobileUnlockLogs->find('list',[
                                                                    'conditions'=>array('MobileUnlockLogs.mobile_unlock_id IN' => array_values($unlockIdsList),'MobileUnlockLogs.unlock_status IN' => array(DISPATCHED_2_KIOSK_UNLOCKED, UNLOCK_PROCESSED_CONFIRMATION_SENT_2_KIOSK)
								 ),
                                                                    'keyField' => 'id',
                                                                    'valueField' => 'mobile_unlock_id',
                                            						//'recursive' => -1
                                                               ]
                                                        );
            if(!empty($unlockData)){
                $unlockData = $unlockData->toArray();
            }else{
                $unlockData = array();
            }
			$unlockIdsArray = array_intersect($unlockIdsList,$unlockData);
		}
		$resultUnlock = array();
		if(!empty($unlockIdsArray)){
			foreach($unlockIdsArray as $key=>$unlockIdIn){
				$resultUnlock[$unlockIdIn]=$unlockIdIn;
			}
		}
		
		//getting brand id, model id, network id from mobile unlock table for above ids
		$unlockDetail = array();
        if(empty($resultUnlock)){
            $resultUnlock = array(0 => null);
        }
		if(!empty($unlockIdsArray)){
			$unlockDetail_query = $this->MobileUnlocks->find('all',array('conditions'=>array('MobileUnlocks.id IN'=>$resultUnlock),'fields'=>array('id','brand_id','mobile_model_id','network_id')));
            $unlockDetail_query = $unlockDetail_query->hydrate(false);
            if(!empty($unlockDetail_query)){
                $unlockDetail = $unlockDetail_query->toArray();
            }else{
                $unlockDetail = array();
            }
		}
		
		//getting cost price corresponding to the brand,model,network combination
		$unlockCostArr = array();
		if(!empty($unlockDetail)){
			foreach($unlockDetail as $key=>$unlockInfo){
				$unlockId = $unlockInfo['id'];
				$brand_id = $unlockInfo['brand_id'];
				$mobile_model_id = $unlockInfo['mobile_model_id'];
				$network_id = $unlockInfo['network_id'];
				
				$unlockCostArr_query = $this->MobileUnlockPrices->find('all',array('conditions' => array('MobileUnlockPrices.brand_id'=>$brand_id,'MobileUnlockPrices.mobile_model_id'=>$mobile_model_id,'MobileUnlockPrices.network_id'=>$network_id),'fields'=>array('MobileUnlockPrices.unlocking_cost')));
                $unlockCostArr_query = $unlockCostArr_query->first();
                if(!empty($unlockCostArr_query)){
                    $unlockCostArr[$unlockId] = $unlockCostArr_query->toArray();
                }else{
                    $unlockCostArr[$unlockId] = array();
                }
			}
		}
        //commented this block by rajiv on 5th Feb
		
		
		$mobileResale = array();
		//getting purchase id of all the sold phones in this time period
		$mobileResale = $this->MobileReSales->find('all',array('fields'=>array('MobileReSales.id','MobileReSales.mobile_purchase_id','MobileReSales.refund_status'),'conditions'=>array("DATE(MobileReSales.created)>'$startDate'","DATE(MobileReSales.created)<'$endDate'",'MobileReSales.kiosk_id'=>$selectedKiosk),'order'=>'MobileReSales.id DESC'));
        $mobileResale = $mobileResale->hydrate(false);
        if(!empty($mobileResale)){
            $mobileResale = $mobileResale->toArray();
        }else{
            $mobileResale = array();
        }
		
		$returnPurchaseIds = array();
		$allPurchaseIds = array();
		
		$purchaseIds = array();
		if(!empty($mobileResale)){
			foreach($mobileResale as $key=>$mobileResaleData){
				$allPurchaseIds[] = $mobileResaleData['mobile_purchase_id'];
				if($mobileResaleData['refund_status']!=1){
					$purchaseIds[] = $mobileResaleData['mobile_purchase_id'];
				}
				if($mobileResaleData['refund_status']==1){
					$returnPurchaseIds[] = $mobileResaleData['mobile_purchase_id'];
				}
			}
		}
		if(empty($allPurchaseIds)){
            $allPurchaseIds = array(0 => null);
        }
        //pr($allPurchaseIds);
		$purchaseCostList = array();
		$purchaseCostList_query = $this->MobilePurchases->find('all',array('fields'=>array('MobilePurchases.id','MobilePurchases.topedup_price','MobilePurchases.cost_price'),'conditions'=>array('MobilePurchases.id IN'=>$allPurchaseIds),'order'=>'MobilePurchases.id DESC'));
        $purchaseCostList_query = $purchaseCostList_query->hydrate(false);
        if(!empty($purchaseCostList_query)){
            $purchaseCostList = $purchaseCostList_query->toArray();
        }else{
            $purchaseCostList = array();
        }
		//pr($purchaseCostList);
		$costPrice = array();
		if(!empty($purchaseCostList)){
			foreach($purchaseCostList as $key=>$purchaseCostDetail){
				$topedup_price = $purchaseCostDetail['topedup_price'];
				$cost_price = $purchaseCostDetail['cost_price'];
				
				if($topedup_price>0){
					$finalPrice = $topedup_price;
				}else{
					$finalPrice = $cost_price;
				}
				$purchase_id = $purchaseCostDetail['id'];
				
				$costPrice[$purchase_id]=$finalPrice;
			}
		}
		
		$totalPhoneCost = 0;
		if(!empty($purchaseIds)){
		  //pr($costPrice);
		  //pr($purchaseIds);die;
			foreach($purchaseIds as $key=>$mobilePurchaseId){
				if(!empty($costPrice)){
					if(array_key_exists($mobilePurchaseId,$costPrice)){
						 $totalPhoneCost+=$costPrice[$mobilePurchaseId];
					}
				}
			}
		}
		
		$mobileBlkResale = array();
		$mobileBlkResale = $this->MobileBlkReSales->find('all',array('fields'=>array('MobileBlkReSales.id','MobileBlkReSales.mobile_purchase_id','MobileBlkReSales.refund_status'),'conditions'=>array("DATE(MobileBlkReSales.created)>'$startDate'","DATE(MobileBlkReSales.created)<'$endDate'",'MobileBlkReSales.kiosk_id'=>$selectedKiosk),'order'=>'MobileBlkReSales.id DESC'));
        $mobileBlkResale = $mobileBlkResale->hydrate(false);
        if(!empty($mobileBlkResale)){
            $mobileBlkResale = $mobileBlkResale->toArray();
        }else{
            $mobileBlkResale = array();
        }
		
		$allBlkPurchaseIds = $bulkPurchaseIds = $blkRetunrendIds = array();
		
		if(!empty($mobileBlkResale)){
			foreach($mobileBlkResale as $key => $value){
				$allBlkPurchaseIds[] = $value['mobile_purchase_id'];
				if($value['refund_status'] != 1){
					$bulkPurchaseIds[] = $value['mobile_purchase_id'];
				}
				if($value['refund_status'] == 1){
					$blkRetunrendIds[] = $value['mobile_purchase_id'];
				}
			}
		}
		if(empty($allBlkPurchaseIds)){
            $allBlkPurchaseIds = array(0 => null);
        }
		$bulkPurchaseCostList = array();
		$bulkPurchaseCostList = $this->MobilePurchases->find('all',array('fields'=>array('MobilePurchases.id','MobilePurchases.topedup_price','MobilePurchases.cost_price'),'conditions'=>array('MobilePurchases.id IN'=>$allBlkPurchaseIds),'order'=>'MobilePurchases.id DESC'));
		$bulkPurchaseCostList = $bulkPurchaseCostList->hydrate(false);
        if(!empty($bulkPurchaseCostList)){
            $bulkPurchaseCostList = $bulkPurchaseCostList->toArray();
        }else{
            $bulkPurchaseCostList = array();
        }
        //pr($bulkPurchaseCostList);		
		$bulkCostPrice = array();
		if(!empty($bulkPurchaseCostList)){
			foreach($bulkPurchaseCostList as $key1=>$purchaseCostDetail1){
				$bulk_topedup_price = $purchaseCostDetail1['topedup_price'];
				$bulk_cost_price = $purchaseCostDetail1['cost_price'];
				
				if($bulk_topedup_price>0){
					$finalPrice1 = $bulk_topedup_price;
				}else{
					$finalPrice1 = $bulk_cost_price;
				}
				$purchase_id1 = $purchaseCostDetail1['id'];
				
				$bulkCostPrice[$purchase_id1]=$finalPrice1;
			}
		}
		
		$totalBulkPhoneCost = 0;
		if(!empty($bulkPurchaseIds)){
			foreach($bulkPurchaseIds as $key=>$bulkPurchaseIds1){
				if(!empty($bulkCostPrice)){
					if(array_key_exists($bulkPurchaseIds1,$bulkCostPrice)){
						 $totalBulkPhoneCost+=$bulkCostPrice[$bulkPurchaseIds1];
					}
				}
			}
		}
		
		$totalBulkReturnCost = 0;
		if(!empty($blkRetunrendIds)){
			foreach($blkRetunrendIds as $key=>$blkRetunrendIds1){
				if(!empty($bulkCostPrice)){
					if(array_key_exists($blkRetunrendIds1,$bulkCostPrice)){
						$totalBulkReturnCost+=$bulkCostPrice[$blkRetunrendIds1]; 
					}
					
				}
			}
		}
		
		
		$productDetail = array();
        if(empty($productIds)){
            $productIds = array(0 => null);
        }
		if(!empty($productIds)){
			$productDetail = $this->Products->find('all',array('conditions'=>array('Products.id IN'=>$productIds),'fields'=>array('id','product_code','product','cost_price'),'recursive'=>-1));
            $productDetail = $productDetail->hydrate(false);
            if(!empty($productDetail)){
                $productDetail = $productDetail->toArray();
            }else{
                $productDetail = array();
            }
		}
		
		$productArr = array();
		if(!empty($productDetail)){
			foreach($productDetail as $key=>$productInfo){
				$productArr[$productInfo['id']] = $productInfo;
			}
		}
		
		$totalReturnCost = 0;
		if(!empty($returnPurchaseIds)){
			foreach($returnPurchaseIds as $key=>$mobileReturnPurchaseId){
				if(!empty($costPrice)){
                    if(array_key_exists($mobileReturnPurchaseId,$costPrice)){
						 $totalReturnCost+=$costPrice[$mobileReturnPurchaseId];	 
					}
				}
			}
		}
		
		$kiosks = $this->Kiosks->find('list',[
                                                'keyField' => 'id',
                                                'valueField' => 'name',
                                                'conditions' => ['Kiosks.status' => 1],
                                                'order' => ['Kiosks.name asc']
                                                //'recursive' => -1
                                             ]
			                        );
        if(!empty($kiosks)){
            $kiosks = $kiosks->toArray();
        }else{
            $kiosks = array();
        }
		$hint = $this->ScreenHint->hint('stock_transfer','summary_sale');
					if(!$hint){
						$hint = "";
					}
		$this->set(compact('hint','kiosks','transferredByKiosk','transferredByWarehouse','productArr','unlockRefund','unlockSale','sumRepairCost','sumRefundRepair','sumUnlockCost','sumRefundUnlock','mobile_cost','totalPhoneCost','totalReturnCost','fixedRepairCost','fixedUnlockCost','costStockTransByKiosk','costTransByWh','totalBulkPhoneCost','totalBulkReturnCost','total_dispute_cost','total_dispute_cost_static'));
		if($selectedKiosk == -1){
		  $this->all_kiosk_bill($startDate,$endDate);
		  $this->render('all_summary_sale');
		}
	}
    
    public function searchCrossStockTransfer(){
        //pr($this->request);die;
		$selectedKiosk = $this->request->query['data']['kiosk'];
		
		$from_date = '';
		$startDate = '';
		if(!empty($this->request->query['from_date'])){
			$from_date = $this->request->query['from_date'];
			$startDate = date("Y-m-d",strtotime($from_date.'-1 day'));
		}
		
		$to_date = '';
		$endDate = '';
		if(!empty($this->request->query['to_date'])){
			$to_date = $this->request->query['to_date'];
			$endDate = date("Y-m-d",strtotime($to_date.'+1 day'));
		}
		
		//warehouse to kiosk
		//$this->KioskOrder->setSource("kiosk_orders");
        $KioskOrders_source = "kiosk_orders";
        $KioskOrdersTable = TableRegistry::get($KioskOrders_source,[
                                                                    'table' => $KioskOrders_source,
                                                                ]); 
        
		$transferredByWarehouse = array();
		//$this->StockTransfers->setSource('stock_transfer');
        $StockTransfers_source = "stock_transfer";
        $StockTransfersTable = TableRegistry::get($StockTransfers_source,[
                                                                    'table' => $StockTransfers_source,
                                                                ]);
		
		$condArr = array();
		if( !empty($startDate) && !empty($endDate)){
			$condArr = array("DATE(created) > '$startDate'", "DATE(created) < '$endDate'");
		}
		
		if(!empty($this->request->query['data']['kiosk'])){
	
		}
		$trnsfrd2KioskOrderIds_query = $StockTransfersTable->find('list', [
                                                                        'conditions' => $condArr,
                                                                        'keyField' => 'id',
                                                                        'valueField' => 'kiosk_order_id'
                                                                        //'recursive' => -1
                                                                    ]
															);
        if(!empty($trnsfrd2KioskOrderIds_query)){
            $trnsfrd2KioskOrderIds = $trnsfrd2KioskOrderIds_query->toArray();
        }else{
            $trnsfrd2KioskOrderIds = array();
        }

		$checkIfBelongToKiosk_query = $KioskOrdersTable->find('list',[
                                                                'keyField' => 'id',
                                                                'valueField' => 'id',
                                                                'conditions' => [   																				'kiosk_id' => $selectedKiosk,
																					'id IN' =>$trnsfrd2KioskOrderIds
                                                                                ],
															  ]
														);
        if(!empty($checkIfBelongToKiosk_query)){
            $checkIfBelongToKiosk = $checkIfBelongToKiosk_query->toArray();
        }else{
            $checkIfBelongToKiosk = array();
        }
		if(empty($checkIfBelongToKiosk)){
            $checkIfBelongToKiosk = array(0 => null);
        }
		$transferredByWarehouse = $StockTransfersTable->find('all',array(
											   'conditions' => array('kiosk_order_id IN' => $checkIfBelongToKiosk),
											   'fields' => array('product_id',
                                                                'sale_price',
                                                                'cost_price',
                                                                'kiosk_order_id',
                                                                'quantity',
                                                                'created'
                                                                ),
											   'order' => 'id desc'
											   ));
		$transferredByWarehouse = $transferredByWarehouse->hydrate(false);
        if(!empty($transferredByWarehouse)){
            $transferredByWarehouse = $transferredByWarehouse->toArray();
        }else{
            $transferredByWarehouse = array();
        }
		
		$productIds = array();
		if(!empty($transferredByWarehouse)){
			foreach($transferredByWarehouse as $key => $byWarehouse){
				$prodID = $byWarehouse['product_id'];
				$productIds[$prodID] = $prodID;
			}
		}
		
		$productArr = $productDetail = array();
        if(empty($productIds)){
            $productIds = array(0 => null);
        }
		if(!empty($productIds)){
			$productDetail = $this->Products->find('all',array(
														'conditions' => array('Products.id IN' => $productIds),
														'fields' => array('id','product_code','product','cost_price'),
														'recursive' => -1
														)
												  );
            $productDetail = $productDetail->hydrate(false);
            if(!empty($productDetail)){
                $productDetail = $productDetail->toArray();
            }else{
                $productDetail = array();
            }
			if(!empty($productDetail)){
				foreach($productDetail as $key => $productInfo){
					$productArr[$productInfo['id']] = $productInfo;
					//storing product titles in product id array
				}
			}
		}
		
		$kiosks = $this->Kiosks->find('list',[
                                                'keyField' => 'id',
                                                'valueField' => 'name',
                                                'conditions' => ['Kiosks.status' => 1],
                                                'order' => ['Kiosks.name asc']
                                                //'recursive' => -1
                                             ]
									);
        $kiosks = $kiosks->hydrate(false);
        if(!empty($kiosks)){
            $kiosks = $kiosks->toArray();
        }else{
            $kiosks = array();
        }
		
		$product_cats = $this->Products->find("list",[
											   'keyField' => "id",
											   'valueField' => "category_id",
											   ])->toArray();
		  $cat_name = $this->Categories->find("list",[
													  'keyField' => 'id',
													  'valueField' => 'category',
													  ])->toArray();
		
		
		$this->set(compact('kiosks','transferredByWarehouse','productArr','product_cats','cat_name'));
		$this->render('cross_stock_transfer');
	}
    
    public function searchCrossStockReturn(){
		$selectedKiosk = $this->request->query['data']['kiosk'];
		
		$from_date = '';
		$startDate = '';
		if(!empty($this->request->query['from_date'])){
			$from_date = $this->request->query['from_date'];
			$startDate = date("Y-m-d",strtotime($from_date.'-1 day'));
		}
		
		$to_date = '';
		$endDate = '';
		if(!empty($this->request->query['to_date'])){
			$to_date = $this->request->query['to_date'];
			$endDate = date("Y-m-d",strtotime($to_date.'+1 day'));
		}
		
	
		//$this->KioskOrder->setSource("center_orders");
        $KioskOrders_source = "center_orders";
        $KioskOrdersTable = TableRegistry::get($KioskOrders_source,[
                                                                    'table' => $KioskOrders_source,
                                                                ]);
		$transferredByKiosk = array();
		//$this->StockTransfer->setSource('stock_transfer_by_kiosk');
        $StockTransfers_source = "stock_transfer_by_kiosk";
        $StockTransfersTable = TableRegistry::get($StockTransfers_source,[
                                                                    'table' => $StockTransfers_source,
                                                                ]);
		
		$trnsfrdByKioskOrderIds_query = $StockTransfersTable->find('list',[
                                                                     'conditions'=>["DATE(created)>'$startDate'","DATE(created)<'$endDate'"],
                                                                     'keyField' => 'id',
                                                                     'valueField' => 'kiosk_order_id'
                                                                     //'recursive'=>-1
                                                                    ]
                                                            );
        if(!empty($trnsfrdByKioskOrderIds_query)){
            $trnsfrdByKioskOrderIds = $trnsfrdByKioskOrderIds_query->toArray();
        }else{
            $trnsfrdByKioskOrderIds = array();
        }
		if(empty($trnsfrdByKioskOrderIds)){
            $trnsfrdByKioskOrderIds = array(0 => null);
        }
		$checkIfBelong2Kiosk = $KioskOrdersTable->find('list',[
                                                               'keyField' => 'id',
                                                               'valueField' => 'id',
                                                               'conditions'=>['kiosk_id'=>$selectedKiosk, //'id IN'=>$trnsfrdByKioskOrderIds
																			  "Date(received_on)>'$startDate'","Date(received_on)<'$endDate'"
																			  ],
                                                               //'recursive'=>-1
                                                              ]
                                                       );
        if(!empty($checkIfBelong2Kiosk)){
            $checkIfBelong2Kiosk = $checkIfBelong2Kiosk->toArray();
        }else{
            $checkIfBelong2Kiosk = array();
        }
		if(empty($checkIfBelong2Kiosk)){
            $checkIfBelong2Kiosk = array(0 => null);
        }
		$transferredByKiosk = $StockTransfersTable->find('all',array('conditions'=>array('kiosk_order_id IN'=>$checkIfBelong2Kiosk),'fields'=>array('product_id','sale_price','cost_price','quantity','created')));
		$transferredByKiosk = $transferredByKiosk->hydrate(false);
        if(!empty($transferredByKiosk)){
            $transferredByKiosk = $transferredByKiosk->toArray();
        }else{
            $transferredByKiosk = array();
        }
		
		$productIds = array();
		if(!empty($transferredByKiosk)){
			foreach($transferredByKiosk as $key=>$byKiosk){
				$productIds[$byKiosk['product_id']] = $byKiosk['product_id'];
			}
		}
		if(empty($productIds)){
            array(0 => null);
        }
		$productDetail = array();
		if(!empty($productIds)){
			$productDetail = $this->Products->find('all',array('conditions'=>array('Products.id IN'=>$productIds),'fields'=>array('id','product_code','product','cost_price')));
            $productDetail = $productDetail->hydrate(false);
            if(!empty($productDetail)){
                $productDetail = $productDetail->toArray();
            }else{
                $productDetail = array();
            }
		}
		
		$productArr = array();
		if(!empty($productDetail)){
			foreach($productDetail as $key=>$productInfo){
				$productArr[$productInfo['id']] = $productInfo;
			}
		}
		
		$kiosks = $this->Kiosks->find('list',[
                                                'keyField' => 'id',
                                                'valueField' => 'name',
                                                'conditions' => ['Kiosks.status' => 1],
                                                'order' => ['Kiosks.name asc']
                                                //'recursive' => -1
                                            ]
                                    );
		if(!empty($kiosks)){
            $kiosks = $kiosks->toArray();
        }else{
            $kiosks = array();
        }
		$product_cats = $this->Products->find("list",[
											   'keyField' => "id",
											   'valueField' => "category_id",
											   ])->toArray();
		  $cat_name = $this->Categories->find("list",[
													  'keyField' => 'id',
													  'valueField' => 'category',
													  ])->toArray();
		
		
		$this->set(compact('kiosks','transferredByKiosk','productArr','product_cats','cat_name'));
		$this->render('cross_stock_returned');
	}
    
    public function totalRepairCost(){
		//pr($this->request->query);die;
		$problemTypeOptions = $this->ProblemTypes->find('list',[
                                                                'keyField' => 'id',
                                                                'valueField' => 'problem_type'
                                                                //'fields' => array('id', 'problem_type')
                                                               ]
                                                        );
        if(!empty($problemTypeOptions)){
            $problemTypeOptions = $problemTypeOptions->toArray();
        }else{
            $problemTypeOptions = array();
        }
		$selectedKiosk = '';
		if(!empty($this->request->query['data']['kiosk'])){
			$selectedKiosk = $this->request->query['data']['kiosk'];
		}
		
		$from_date = '';
		$startDate = '';
		if(!empty($this->request->query['from_date'])){
			$from_date = $this->request->query['from_date'];
			$startDate = date("Y-m-d",strtotime($from_date.'-1 day'));
		}
		
		$to_date = '';
		$endDate = '';
		if(!empty($this->request->query['to_date'])){
			$to_date = $this->request->query['to_date'];
			$endDate = date("Y-m-d",strtotime($to_date.'+1 day'));
		}
		
		
		$repairIdsArray = array();
		
		$repairIdsData = $this->MobileRepairLogs->find('list',[
                                                               'conditions' => array('MobileRepairLogs.repair_status IN' => DISPATCHED_2_KIOSK_REPAIRED, "DATE(MobileRepairLogs.created)>'$startDate'","DATE(MobileRepairLogs.created)<'$endDate'","kiosk_id" => (int)$selectedKiosk),
                                                               'keyField' => 'mobile_repair_id',
                                                               'valueField' => 'created'
                                                              ]
                                                       );
        if(!empty($repairIdsData)){
            $repairIdsData = $repairIdsData->toArray();
        }else{
            $repairIdsData = array();
        }
		$repairIds = array_keys($repairIdsData);
                
		$repairIdsArray = $repairIds;
		
        $repair_data = array();
        $repairdata = array();        
		$repairAttr = array();
		$repairParts = array();
		$repairDetail = array();
        if(empty($repairIdsArray)){
            $repairIdsArray = array(0 => null);
        }
		if(!empty($repairIdsArray)){
			$repair_data = $repairDetail = $repairAttr = $this->MobileRepairs->find('all',array('conditions'=>array('MobileRepairs.id IN'=>$repairIdsArray),'fields'=>array('id','brand_id','kiosk_id','mobile_model_id','problem_type','net_cost','created')));
            $repair_data = $repair_data->hydrate(false);
            if(!empty($repair_data)){
                $repair_data = $repair_data->toArray();
            }else{
                $repair_data = array();
            }
			$repairParts = $this->MobileRepairParts->find('all',array('conditions'=>array('MobileRepairParts.mobile_repair_id IN'=>$repairIdsArray)));
            $repairParts = $repairParts->hydrate(false);
            if(!empty($repairParts)){
                $repairParts = $repairParts->toArray();
            }else{
                $repairParts = array();
            }

		}
                
                if(count($repairAttr)){
                    foreach($repairAttr as $rd => $repair_info){
                        $repairdata[$repair_info['id']] = $repair_info;
                    }
                }
		
		$repairPartList = array();
		
		if(count($repairParts)){
			foreach($repairParts as $key=>$repairPartsInfo){
				$repairPartList[$repairPartsInfo['mobile_repair_id']][]=$repairPartsInfo['product_id'];
			}
		}
		$productIds = array();
		
		foreach($repairPartList as $repairId=>$productDetail){
			foreach($productDetail as $key=>$productInfo){
				$productIds[$productInfo]=$productInfo;
			}
		}
		if(empty($productIds)){
            $productIds = array(0 => null);
        }
		$productDetail = array();
		if(!empty($productIds)){
			$productDetail = $this->Products->find('all',array('conditions'=>array('Products.id IN'=>$productIds),'fields'=>array('id','product_code','product','cost_price')));
            $productDetail = $productDetail->hydrate(false);
            if(!empty($productDetail)){
                $productDetail = $productDetail->toArray();
            }else{
                $productDetail = array();
            }
		}
		
		$productArr = array();
		if(!empty($productDetail)){
			foreach($productDetail as $key=>$productInfo){
				$productArr[$productInfo['id']] = $productInfo;
			}
		}
		
		//getting cost price corresponding to the brand,model,problem combination
		$repairCostArr = array();
		$mobile_model_ids = array();//rajju
		$brand_ids = array();//rajju
		if(!empty($repairAttr)){
			foreach($repairAttr as $key=>$repairInfo){
				$repairId = $repairInfo['id'];
				 $brand_id = $repairInfo['brand_id'];
				 $mobile_model_id = $repairInfo['mobile_model_id'];
				$brand_ids[$repairInfo['brand_id']] = $repairInfo['brand_id'];//rajju
				$mobile_model_ids[$repairInfo['mobile_model_id']] = $repairInfo['mobile_model_id'];//rajju
				$problemTypeArr = explode('|',$repairInfo['problem_type']);
				if(empty($problemTypeArr)){
                    $problemTypeArr = array(0 => null);
                }
				$repairCostArr_query = $this->MobileRepairPrices->find('all',array('conditions'=>array('MobileRepairPrices.brand_id'=>$brand_id,'MobileRepairPrices.mobile_model_id'=>$mobile_model_id,'MobileRepairPrices.problem_type IN'=>$problemTypeArr),'fields'=>array('MobileRepairPrices.repair_cost')));
                $repairCostArr_query = $repairCostArr_query->hydrate(false);
                if(!empty($repairCostArr_query)){
                    $repairCostArr[$repairId] = $repairCostArr_query->toArray();
                }else{
                    $repairCostArr[$repairId] = array();
                }
			}
		}
        if(empty($mobile_model_ids)){
            $mobile_model_ids = array(0 => null);
        }
		$mobileModels_query = $this->MobileModels->find('list',[
                                                          'conditions' => ['MobileModels.id IN' => $mobile_model_ids],
                                                          'keyField' => 'id',
                                                          'valueField' => 'model'
                                                         ]
                                                );
        if(!empty($mobileModels_query)){
            $mobileModels = $mobileModels_query->toArray();
        }else{
            $mobileModels = array();
        }
        if(empty($brand_ids)){
            $brand_ids = array(0 => null);
        }
		$brands = $this->Brands->find('list',[
                                              'conditions' => ['Brands.id IN' => $brand_ids],
                                              'keyField' => 'id',
                                              'valueField' => 'brand'
                                             ]
                                    );
        if(!empty($brands)){
            $brands = $brands->toArray();
        }else{
            $brands = array();
        }
		$kiosks_query = $this->Kiosks->find('list',[
                                                'keyField' => 'id',
                                                'valueField' => 'name',
                        						'conditions' => ['Kiosks.status' => 1],
                        						'order' => ['Kiosks.name asc']
                        						//'recursive' => -1
                                            ]
                                    );
        if(!empty($kiosks_query)){
            $kiosks = $kiosks_query->toArray();
        }else{
            $kiosks = array();
        }
		
		 $this->set(compact('repairdata'));
		$this->set(compact('repairDetail','repairCostArr','productArr','repairPartList','mobileModels','kiosks','repairAttr','brands','problemTypeArr','problemTypeOptions','repairIdsData'));//rajju
	}
    
    public function totalUnlockCost(){
        $Unlockarr = array();
        $network_ids = array();
		$selectedKiosk = '';
		if(!empty($this->request->query['data']['kiosk'])){
			$selectedKiosk = $this->request->query['data']['kiosk'];
		}
		
		$from_date = '';
		$startDate = '';
		if(!empty($this->request->query['from_date'])){
			$from_date = $this->request->query['from_date'];
			$startDate = date("Y-m-d",strtotime($from_date.'-1 day'));
		}
		
		$to_date = '';
		$endDate = '';
		if(!empty($this->request->query['to_date'])){
			$to_date = $this->request->query['to_date'];
			$endDate = date("Y-m-d",strtotime($to_date.'+1 day'));
		}
		
		$unlockIdsDetail = array();
		$unlockIdsArray = array();
		//----------------------------------------------------
		$unlockIds_query = $this->MobileUnlockLogs->find('list',[
                                                           'conditions' => array(
								'MobileUnlockLogs.unlock_status IN' => array(UNLOCK_PROCESSED_CONFIRMATION_SENT_2_KIOSK, DISPATCHED_2_KIOSK_UNLOCKED),
								"DATE(MobileUnlockLogs.created)>'$startDate'",
								"DATE(MobileUnlockLogs.created) < '$endDate'",
								"kiosk_id" => (int)$selectedKiosk,
								),                       
                                                            'keyField' => 'id',
                                                            'valueField' => 'mobile_unlock_id'
                                                          ]
                                                   );
        if(!empty($unlockIds_query)){
            $unlockIds = $unlockIds_query->toArray();
        }else{
            $unlockIds = array();
        }
		$unlockIdsArray = $unlockIds = array_unique($unlockIds);
		
		$unlockDetail = array();
		if(count($unlockIds)){
            if(empty($unlockIds)){
                $unlockIds = array(0 => null);
            }
			$unlockDetail_query = $this->MobileUnlocks->find('all',array('conditions' => array('MobileUnlocks.id IN' => $unlockIds),'fields' => array('id','brand_id', 'mobile_model_id', 'network_id','net_cost')));
            $unlockDetail_query = $unlockDetail_query->hydrate(false);
            if(!empty($unlockDetail_query)){
                $unlockDetail = $unlockDetail_query->toArray();
            }else{
                $unlockDetail = array();
            }
		}
		
		$unlockCostArr = array();
		$totalUnlockCost = 0;
		if(!empty($unlockDetail)){
			foreach($unlockDetail as $key => $unlockInfo){
				$unlockId = $unlockInfo['id'];
				$brand_id = $unlockInfo['brand_id'];
				$mobile_model_id = $unlockInfo['mobile_model_id'];
				$network_id = $unlockInfo['network_id'];
				
				$tPrice = $this->MobileUnlockPrices->find('all', array('conditions' => array('MobileUnlockPrices.brand_id' => $brand_id, 'MobileUnlockPrices.mobile_model_id' => $mobile_model_id,'MobileUnlockPrices.network_id' => $network_id),'fields'=>array('MobileUnlockPrices.unlocking_cost')));
                $tPrice = $tPrice->hydrate(false);
                if(!empty($tPrice)){
                    $tPrice = $tPrice->toArray();
                }else{
                    $tPrice = array();
                }
                if(!empty($tPrice)){
                    $unlockCostArr[$unlockId] = $tPrice;
                    $sumUnlockCost = $totalUnlockCost += $tPrice[0]['unlocking_cost'];
                }
			}
		}
		//pr($unlockCostArr);
		//pr($totalUnlockCost);
		$sumUnlockCost = 0;
		$sumRefundCost = 0;
		$unlockIdsArray = array_unique($unlockIdsArray);
		
		
		$resultUnlock = array();
		if(!empty($unlockIdsArray)){
			foreach($unlockIdsArray as $key=>$unlockIdIn){
				$resultUnlock[$unlockIdIn]=$unlockIdIn;
			}
		}
		//***
		$unlockDetail = array();
		if(!empty($unlockIdsArray)){
            if(empty($resultUnlock)){
                $resultUnlock = array(0 => null);
            }
			$unlockDetail_query = $this->MobileUnlocks->find('all',array('conditions'=>array('MobileUnlocks.id IN'=>$resultUnlock),'fields'=>array('id','brand_id','mobile_model_id','network_id','net_cost')));
            $unlockDetail_query = $unlockDetail_query->hydrate(false);
            if(!empty($unlockDetail_query)){
                $unlockDetail = $unlockDetail_query->toArray();
            }else{
                $unlockDetail = array();
            }
		}
		
		$unlockCostArr = array();
		//pr($unlockDetail);die;
		if(!empty($unlockDetail)){
			foreach($unlockDetail as $key => $unlockInfo){
				$unlockId = $unlockInfo['id'];
				$brand_id = $unlockInfo['brand_id'];
				$mobile_model_id = $unlockInfo['mobile_model_id'];
				$network_id = $unlockInfo['network_id'];
				
				$unlockCostArr_query = $this->MobileUnlockPrices->find('all',array('conditions' => array('MobileUnlockPrices.brand_id'=>$brand_id,'MobileUnlockPrices.mobile_model_id'=>$mobile_model_id,'MobileUnlockPrices.network_id'=>$network_id),'fields'=>array('MobileUnlockPrices.unlocking_cost')));
                $unlockCostArr_query = $unlockCostArr_query->first();
                if(!empty($unlockCostArr_query)){
                    $unlockCostArr[$unlockId] = $unlockCostArr_query->toArray();
                }else{
                    $unlockCostArr[$unlockId] = array();
                }
			}
		}
		$unlockDetail = array();
		
		if(!empty($resultUnlock)){
            if(empty($unlockIdsArray)){
                $unlockIdsArray = array(0 => null);
            }
			$unlockDetail_query = $this->MobileUnlocks->find('all',array('conditions'=>array('MobileUnlocks.id IN'=>$unlockIdsArray),'fields'=>array('id','brand_id','mobile_model_id','network_id','net_cost','created')));
            $unlockDetail_query = $unlockDetail_query->hydrate(false);
            if(!empty($unlockDetail_query)){
                $unlockDetail = $unlockDetail_query->toArray();
            }else{
                $unlockDetail = array();
            }
		}
		if(count($unlockDetail)){
			foreach($unlockDetail as $ud => $unlock_det){
                //pr($unlock_det);die; 
                //pr($unlock_det['id']);
                //pr($unlockCostArr);die;
                if(array_key_exists('unlocking_cost',$unlockCostArr[$unlock_det['id']])){
                    $unlock_det['unlocking_cost'] = $unlockCostArr[$unlock_det['id']]['unlocking_cost'];
                }
				$Unlockarr[$unlock_det['id']] = $unlock_det;
			}
		}
		
		$unlockIdsDetail = $Unlockarr;
		
		
		$resultUnlock = array();
		
		//-------------------------
		
		$unlockCostArr = array();
		$mobile_model_ids = array();
		$brand_ids = array();
		//pr($unlockDetail);
		if(!empty($unlockDetail)){
			foreach($unlockDetail as $key=>$unlockInfo){
				$unlockId = $unlockInfo['id'];
				$brand_id = $unlockInfo['brand_id'];
				$mobile_model_id = $unlockInfo['mobile_model_id'];
				$network_id = $unlockInfo['network_id'];
				$brand_ids[$unlockInfo['brand_id']] = $unlockInfo['brand_id'];
				$mobile_model_ids[$unlockInfo['mobile_model_id']] = $unlockInfo['mobile_model_id'];
				$network_ids[$unlockInfo['network_id']] = $unlockInfo['network_id'];
				$unlockCostArr_query = $this->MobileUnlockPrices->find('all',array('conditions'=>array('MobileUnlockPrices.brand_id'=>$brand_id,'MobileUnlockPrices.mobile_model_id'=>$mobile_model_id,'MobileUnlockPrices.network_id'=>$network_id),'fields'=>array('MobileUnlockPrices.unlocking_cost')));
                $unlockCostArr_query = $unlockCostArr_query->first();
                if(!empty($unlockCostArr_query)){
                    $unlockCostArr[$unlockId] = $unlockCostArr_query->toArray();
                }else{
                    $unlockCostArr[$unlockId] = array();
                }
			}
		}
		
		$kiosks_query = $this->Kiosks->find('list',[
                                                'keyField' => 'id',
                                                'valueField' => 'name',
                                                'conditions' => ['Kiosks.status' => 1],
                                                'order' => ['Kiosks.name asc']
                                                //'recursive' => -1
                                            ]
                                    );
        if(!empty($kiosks_query)){
            $kiosks = $kiosks_query->toArray();
        }else{
            $kiosks = array();
        }
        if(empty($network_ids)){
            $network_ids = array(0 => null);
        }
		$networks_query = $this->Networks->find('list',[
                                                  'conditions' => ['Networks.id IN' => $network_ids],
                                                  'keyField' => 'id',
                                                  'valueField' => 'name'
                                                 ]
                                        );
        if(!empty($networks_query)){
            $networks = $networks_query->toArray();
        }else{
            $networks = array();
        }
		//pr($networks);
        if(empty($mobile_model_ids)){
            $mobile_model_ids = array(0 => null);
        }
		$mobileModels_query = $this->MobileModels->find('list',[
                                                          'conditions' => ['MobileModels.id IN' => $mobile_model_ids],
                                                          'keyField' => 'id',
                                                          'valueField' => 'model'
                                                         ]
                                                  );
        if(!empty($mobileModels_query)){
            $mobileModels = $mobileModels_query->toArray();
        }else{
            $mobileModels = array();
        }
        if(empty($brand_ids)){
            $brand_ids = array(0 => null);
        }
		$brands = $this->Brands->find('list',[
                                              'conditions' => ['Brands.id IN' => $brand_ids],
                                              'keyField' => 'id',
                                              'valueField' => 'brand'
                                             ]
                                    );
        if(!empty($brands)){
            $brands = $brands->toArray();
        }else{
            $brands = array();
        }
		$this->set(compact('networks','mobileModels','brands','Unlockarr'));
		$this->set(compact('unlockIdsDetail','unlockCostArr','kiosks','unlockDetail'));
	}
    
    public function totalPhoneCost(){
		$selectedKiosk = '';
		if(!empty($this->request->query['data']['kiosk'])){
			$selectedKiosk = $this->request->query['data']['kiosk'];
		}
		
		$from_date = '';
		$startDate = '';
		if(!empty($this->request->query['from_date'])){
			$from_date = $this->request->query['from_date'];
			$startDate = date("Y-m-d",strtotime($from_date.'-1 day'));
		}
		
		$to_date = '';
		$endDate = '';
		if(!empty($this->request->query['to_date'])){
			$to_date = $this->request->query['to_date'];
			$endDate = date("Y-m-d",strtotime($to_date.'+1 day'));
		}
		
		$mobileResale = array();
		//getting purchase id of all the sold phones in this time period
		$mobileResale = $this->MobileReSales->find('all',array('fields'=>array('MobileReSales.id','MobileReSales.mobile_purchase_id','MobileReSales.refund_status','MobileReSales.created'),'conditions'=>array("DATE(MobileReSales.created)>'$startDate'","DATE(MobileReSales.created)<'$endDate'",'MobileReSales.kiosk_id'=>$selectedKiosk),'order'=>'MobileReSales.id DESC'));
        $mobileResale = $mobileResale->hydrate(false);
        if(!empty($mobileResale)){
            $mobileResale = $mobileResale->toArray();
        }else{
            $mobileResale = array();
        }
		
		$returnPurchaseIds = array();
		$allPurchaseIds = array();
		
		$purchaseIds = array();
		if(!empty($mobileResale)){
			foreach($mobileResale as $key=>$mobileResaleData){
				$allPurchaseIds[] = $mobileResaleData['mobile_purchase_id'];
				if($mobileResaleData['refund_status']!=1){
					$purchaseIds[] = $mobileResaleData['mobile_purchase_id'];
				}
				if($mobileResaleData['refund_status']==1){
					$returnPurchaseIds[] = $mobileResaleData['mobile_purchase_id'];
				}
			}
		}
		if(empty($allPurchaseIds)){
            $allPurchaseIds = array(0 => null);
        }
		$purchaseCostList = array();
		$purchaseCostList_query = $this->MobilePurchases->find('all',array('fields'=>array('MobilePurchases.id','MobilePurchases.topedup_price','MobilePurchases.cost_price'),'conditions'=>array('MobilePurchases.id IN'=>$allPurchaseIds),'order'=>'MobilePurchases.id DESC'));
        $purchaseCostList_query = $purchaseCostList_query->hydrate(false);
        if(!empty($purchaseCostList_query)){
            $purchaseCostList = $purchaseCostList_query->toArray();
        }else{
            $purchaseCostList = array();
        }
		
		$costPrice = array();
		if(!empty($purchaseCostList)){
			foreach($purchaseCostList as $key=>$purchaseCostDetail){
				$topedup_price = $purchaseCostDetail['topedup_price'];
				$cost_price = $purchaseCostDetail['cost_price'];
				
				if($topedup_price>0){
					$finalPrice = $topedup_price;
				}else{
					$finalPrice = $cost_price;
				}
				$purchase_id = $purchaseCostDetail['id'];
				
				$costPrice[$purchase_id]=$finalPrice;
			}
		}
		
		$kiosks = $this->Kiosks->find('list',[
                                                'keyField' => 'id',
                                                'valueField' => 'name',
                                                'conditions' => ['Kiosks.status' => 1],
                                                'order' => ['Kiosks.name asc']
                                                //'recursive' => -1
                                            ]
                                    );
		if(!empty($kiosks)){
            $kiosks = $kiosks->toArray();
        }else{
            $kiosks = array();
        }
		$this->set(compact('mobileResale','costPrice','kiosks'));
	}
    
    public function totalBulkPhoneCost(){
		//pr($this->request);die;
		$selectedKiosk = '';
		if(!empty($this->request->query['data']['kiosk'])){
			$selectedKiosk = $this->request->query['data']['kiosk'];
		}
		
		$from_date = '';
		$startDate = '';
		if(!empty($this->request->query['from_date'])){
			$from_date = $this->request->query['from_date'];
			$startDate = date("Y-m-d",strtotime($from_date.'-1 day'));
		}
		
		$to_date = '';
		$endDate = '';
		if(!empty($this->request->query['to_date'])){
			$to_date = $this->request->query['to_date'];
			$endDate = date("Y-m-d",strtotime($to_date.'+1 day'));
		}
		
		$mobileResale = array();
		//getting purchase id of all the sold phones in this time period
		$mobileResale_query = $this->MobileBlkReSales->find('all',array('fields'=>array('MobileBlkReSales.id','MobileBlkReSales.mobile_purchase_id','MobileBlkReSales.refund_status','MobileBlkReSales.created'),'conditions'=>array("DATE(MobileBlkReSales.created)>'$startDate'","DATE(MobileBlkReSales.created)<'$endDate'",'MobileBlkReSales.kiosk_id'=>$selectedKiosk),'order'=>'MobileBlkReSales.id DESC'));
        $mobileResale_query = $mobileResale_query->hydrate(false);
        if(!empty($mobileResale_query)){
            $mobileResale = $mobileResale_query->hydrate(false);
        }else{
            $mobileResale = array();
        }
		
		$returnPurchaseIds = array();
		$allPurchaseIds = array();
		
		$purchaseIds = array();
		if(!empty($mobileResale)){
			foreach($mobileResale as $key=>$mobileResaleData){
				$allPurchaseIds[] = $mobileResaleData['mobile_purchase_id'];
				if($mobileResaleData['refund_status']!=1){
					$purchaseIds[] = $mobileResaleData['mobile_purchase_id'];
				}
				if($mobileResaleData['refund_status']==1){
					$returnPurchaseIds[] = $mobileResaleData['mobile_purchase_id'];
				}
			}
		}
		if(empty($allPurchaseIds)){
            $allPurchaseIds = array(0 => null);
        }
		$purchaseCostList = array();
		$purchaseCostList_query = $this->MobilePurchases->find('all',array('fields'=>array('MobilePurchases.id','MobilePurchases.topedup_price','MobilePurchases.cost_price'),'conditions'=>array('MobilePurchases.id IN'=>$allPurchaseIds),'order'=>'MobilePurchases.id DESC'));
        $purchaseCostList_query = $purchaseCostList_query->hydrate(false);
        if(!empty($purchaseCostList_query)){
            $purchaseCostList = $purchaseCostList_query->toArray();
        }else{
            $purchaseCostList = array();
        }
		
		$costPrice = array();
		if(!empty($purchaseCostList)){
			foreach($purchaseCostList as $key=>$purchaseCostDetail){
				$topedup_price = $purchaseCostDetail['topedup_price'];
				$cost_price = $purchaseCostDetail['cost_price'];
				
				if($topedup_price>0){
					$finalPrice = $topedup_price;
				}else{
					$finalPrice = $cost_price;
				}
				$purchase_id = $purchaseCostDetail['id'];
				
				$costPrice[$purchase_id]=$finalPrice;
			}
		}
		
		$kiosks_query = $this->Kiosks->find('list',[
                                                'keyField' => 'id',
                                                'valueField' => 'name',
                                                'conditions' => ['Kiosks.status' => 1],
                                                'order' => ['Kiosks.name asc']
                                                //'recursive' => -1
                                            ]
                                    );
		if(!empty($kiosks_query)){
            $kiosks = $kiosks_query->toArray();
        }else{
            $kiosks = array();
        }
		$this->set(compact('mobileResale','costPrice','kiosks'));
	}
	
	public function stockTransferByKiosk() {
		$session_basket = $this->request->Session()->read('stBasket');
		$kioskId = $this->request->Session()->read('kiosk_id');
		$productTable_source = "kiosk_{$kioskId}_products";
		$productTable = TableRegistry::get($productTable_source,[
																 'table' => $productTable_source,
															 ]);
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
		if ($this->request->is(array('get', 'put'))) {
			if(array_key_exists('display_type',$this->request->query)){
				$displayType = $this->request->query['display_type'];
				if($displayType=="show_all"){
					$this->paginate = array('limit' => '20',array('order' => ['id desc']));
					
				}elseif($displayType=="more_than_zero"){
					$this->paginate = array('limit' => '20','conditions'=>array('NOT'=>array('quantity'=>0)),'order' => ['id desc']);
					
				}
			}else{
			   $this->paginate = array('conditions'=>array('NOT'=>array('quantity'=>0)));
			}
		}else{
		  $this->paginate = array('conditions'=>array('NOT'=>array('quantity'=>0)));
		}
		
		$categories = $this->CustomOptions->category_options($categories,true);
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
		if(is_array($session_basket)){
			$sessionPrdctInfo = array();
			$product_ids = array_keys($session_basket);
			if(empty($product_ids)){
			   $product_ids = array(0 => null);
			   }
			$sessionProducts_query = $productTable->find('all',array('conditions'=>array('id IN'=>$product_ids),'fields'=>array('id','product','product_code')));
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
				$flashTable.= "<tr>
						<td>".$sessionPrdctInfo[$productId]['product_code']."</td>
						<td>".$sessionPrdctInfo[$productId]['product']."</td>
						<td>".$productInfo['quantity']."</td>
						<td>".$productInfo['price']."</td>
						<td>".$productInfo['remarks']."</td>
					<tr>";
			}
			
			
			if(!empty($flashTable)){
				$sessionProductTable = "<table>
						<tr>
							<th>Product Code</th>
							<th>Product</th>
							<th>Quantity</th>
							<th>Selling Price</th>
							<th>Remarks</th>
						</tr>.$flashTable
						</table>";
				$this->Flash->success($sessionProductTable,array('escape' => false));
			}
		}
		//unset($this->paginate['order']);
		
		//pr($this->paginate);die;
		$centralStocks_query = $this->paginate($productTable_source);
		$centralStocks = $centralStocks_query->toArray();
		$this->set(compact('categories','kiosks','displayType','centralStocks'));
		$this -> render('stock_transfer_by_kiosk');
	}
	
	function searchStockTransferByKiosk($keyword = "",$displayCondition = ""){
		if(array_key_exists('search_kw',$this->request->query)){
			$search_kw = $this->request->query['search_kw'];
		}
		
		$displayType = "";
		if(array_key_exists('display_type',$this->request->query)){
			$displayType = $this->request->query['display_type'];
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
		//----------------------
		if(!empty($search_kw)){
			$conditionArr['OR']['LOWER(product) like '] =  strtolower("%$search_kw%");
			$conditionArr['OR']['LOWER(product_code) like '] =  strtolower("%$search_kw%");
		}
		//----------------------
        //pr($this->request->query);die;
		if(array_key_exists('category',$this->request->query) && !empty($this->request->query['category'][0])){
			$conditionArr['category_id IN'] = $this->request->query['category'];
		}
		if($displayType=="more_than_zero"){
			$conditionArr['NOT']['`quantity`'] = 0;
		}
		$kioskId = $this->request->Session()->read('kiosk_id');
		$productTable_source = "kiosk_{$kioskId}_products";
		$productTable = TableRegistry::get($productTable_source,[
																 'table' => $productTable_source,
															 ]);
		
		$this->paginate = array(
						   'limit' => 40,
						   'conditions' => $conditionArr);
		$selectedCategoryId=array();
		if(array_key_exists('category_id IN',$conditionArr) && !empty($conditionArr['category_id IN'][0])){
			$selectedCategoryId=$conditionArr['category_id IN'];
		}

		$categories = $this->CustomOptions->category_options($categories,true,$selectedCategoryId);
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
		$centralStocks_query = $this->paginate($productTable);
		$centralStocks = $centralStocks_query->toArray();
		$this->set('centralStocks',$centralStocks);
		$this->set(compact('categories','kiosks','displayType'));
		$this -> render('stock_transfer_by_kiosk');
	}
	
	function updateCenterStock(){
		$kioskId = $this->request->Session()->read('kiosk_id');
		$productsName_query = $this->Products->find('list',array('fields'=>'product'));
		$productsName_query = $productsName_query->hydrate(false);
		if(!empty($productsName_query)){
			   $productsName_query = $productsName_query->toArray();	
		}else{
			   $productsName_query = array();
		}
		$productsName_query = array();
		$current_page = '';
		if(array_key_exists('current_page',$this->request['data'])){
			$current_page = $this->request['data']['current_page'];
		}
		
		if(!isset($current_page)){$this->redirect(array('action' => 'stock_transfer_by_kiosk'));}
		
		$productCounts = 0;
		
		if(array_key_exists('basket',$this->request['data'])){
			$productArr = array();
			//foreach($this->request['data']['KioskStock']['product_id'] as $ki => $ide){
			//	$currentPrice = $this->request['data']['KioskStock']['current_price'][$ki];
			//	$currentPriceArr[$ide] = $currentPrice;
			//}
			
			$currentQuantity = $productID = $price = '';
			foreach($this->request['data']['KioskStock']['quantity'] as $key => $quantity){
				$currentQuantity = $this->request['data']['KioskStock']['p_quantity'][$key];
				$productID = $this->request['data']['KioskStock']['product_id'][$key];
				$price = $this->request['data']['KioskStock']['current_price'][$key];
				$remarks = $this->request['data']['KioskStock']['remarks'][$key];
				
				//foreach($currentPriceArr as $d => $current_price){				
				//	if($d == $productID && $price<$current_price){					
				//		$this->Session->setFlash("New sale price must be greater than or equal to sale price for {$productsName[$productID]}");
				//		return $this->redirect(array('action' => "index/page:$current_page"));
				//		die;
				//	}
				//}
			
				if($quantity > 0 && $quantity <= $currentQuantity){
					$productArr[$productID] = array(
									'quantity' => $quantity,
									'price' => $price,
									'remarks' => $remarks
									);
					$productCounts++;
				}				
			}
			
			$session_basket = $this->request->Session()->read('stBasket');
			
			if(count($session_basket) >= 1){
				//adding item to the the existing session
				$sum_total = $this->add_arrays(array($productArr,$session_basket));
				$this->request->Session()->write('stBasket', $sum_total);
				$session_basket = $this->request->Session()->read('stBasket');
			}else{
				//adding item first time to session
				if(count($productCounts))$this->request->Session()->write('stBasket', $productArr);
			}
			
			$totalItems = count($this->request->Session()->read('stBasket'));
			
			if($productCounts){
				$flashMessage = "$productCounts product(s) added to the stock. Total item Count:$totalItems";
			}else{
				$flashMessage = "No item added to the stock. Item Count:$productCounts";
			}
			
			$this->Flash->error($flashMessage);
			return $this->redirect(array('action' => "stock_transfer_by_kiosk/page:$current_page"));
		}elseif(array_key_exists('empty_basket',$this->request['data'])){
			$this->request->Session()->delete('stBasket');
			$flashMessage = "Basket is empty; Add Fresh Stock!";
			$this->Flash->error($flashMessage);
			return $this->redirect(array('action' => "stock_transfer_by_kiosk/page:$current_page"));			
		}elseif(array_key_exists('checkout',$this->request['data'])){
			$session_basket = $this->request->Session()->read('stBasket');
			if(!empty($session_basket)){
				return $this->redirect(array('action'=>'checkout_stock_transfer_by_kiosk'));
			}else{
				$this->Flash->error('Please add items to the basket');
				return $this->redirect(array('action' => "stock_transfer_by_kiosk/page:$current_page"));
			}
		}else{
			//echo "//create order";
			$kioskId = $this->request->Session()->read('kiosk_id');
			$productArr = array();
			if(empty($kioskId)){
				$flashMessage = "Failed to transfer stock. <br />Please login from your kiosk website!";
				$this->Flash->error($flashMessage,['escape' => false]);
				return 	$this->redirect(array('action' => "stock_transfer_by_kiosk/page:$current_page"));
				die;
			}
		  
			
			if(array_key_exists('KioskStock',$this->request['data'])){
				
				$currentQuantity = $productID = $price = '';
				foreach($this->request['data']['KioskStock']['quantity'] as $key => $quantity){
					$currentQuantity = $this->request['data']['KioskStock']['p_quantity'][$key];
					$productID = $this->request['data']['KioskStock']['product_id'][$key];
					$price = $this->request['data']['KioskStock']['current_price'][$key];
					$remarks = $this->request['data']['KioskStock']['remarks'][$key];
					
					if($quantity > 0 && $quantity <= $currentQuantity){
						$productArr[$productID] = array(
										'quantity' => $quantity,
										'price' => $price,
										'remarks' => $remarks
										);
						$productCounts++;
					}
				}
				
				$session_basket = $this->request->Session()->read('stBasket');
				$sum_total = $this->add_arrays(array($productArr,$session_basket));
				$this->request->Session()->write('stBasket',$sum_total);
				if(count($sum_total) == 0){
					$flashMessage = "Failed to transfer stock. <br/>Please select quantity atleast for one product!";
					$this->Flash->error($flashMessage);
					return $this->redirect(array('action' => "stock_transfer_by_kiosk/page:$current_page"));
					die;
				}
			}
				
			$datetime = date('Y-m-d H:i:s');
			$kioskOrderData = array(
						'kiosk_id' => $kioskId,
						'dispatched_on' => $datetime,
						'user_id' => $this->request->Session()->read('Auth.User.id'),
						'status' => 1
						);
			//pr($kioskOrderData);
			//$this->KioskOrder->setSource("center_orders");
		    $center_ordersTable = TableRegistry::get("center_orders",[
																	  'table' => "center_orders",
																  ]);
			$center_ordersEntity = $center_ordersTable->newEntity($kioskOrderData,['validate' => false]);
			$center_ordersEntity = $center_ordersTable->patchEntity($center_ordersEntity,$kioskOrderData,['validate' => false]);
			
			$center_ordersTable->save($center_ordersEntity,['validate' => false]);
			$kiosk_order_id = $center_ordersEntity->id;
			
			$session_basket = $this->request->Session()->read('stBasket');
			foreach($session_basket as $productID => $productData){
				//continue;
				$cost_price_list_query = $this->Products->find('list',array('conditions' => array('Products.id' => $productID),
																				'keyField' => 'id',
																				'valueField' => 'cost_price',
																		   ));
				$cost_price_list_query = $cost_price_list_query->hydrate(false);
				if(!empty($cost_price_list_query)){
					$cost_price_list = $cost_price_list_query->toArray();
				}else{
					$cost_price_list = array();
				}
				
				//$this->StockTransfer->setSource("stock_transfer_by_kiosk");
				$StockTransferTable = TableRegistry::get("stock_transfer_by_kiosk",[
																	  'table' => "stock_transfer_by_kiosk",
																  ]);
				
				
				
				$price = $productData['price'];
				$quantity = $productData['quantity'];
				$remarks = $productData['remarks'];
				
				$stockTransferData = array(
							'kiosk_order_id' => $kiosk_order_id,
							'product_id' => $productID,
							'quantity' => $quantity,
							'cost_price' => $cost_price_list[$productID],
							'sale_price' => $price,
							'remarks' => $remarks,
							'status' => '1'
							);
				$new_entity = $StockTransferTable->newEntity($stockTransferData,['validate' => false]);
				$new_entity = $StockTransferTable->patchEntity($new_entity,$stockTransferData,['validate' => false]);
				if($StockTransferTable->save($new_entity)){
					//decreasing central stock
					$productTable = "kiosk_{$kioskId}_products";
					$productData = array('quantity' => "Product.quantity - $quantity");
					$query = "UPDATE `$productTable` SET `quantity` = `quantity` - $quantity WHERE `id` = $productID";
					$conn = ConnectionManager::get('default');
					$stmt = $conn->execute($query); 
				}else{
					echo "<pre>";print_r($new_entity);die;
				}
			}
			$this->request->Session()->delete('stBasket');
			$flashMessage = count($session_basket)." products dispatched for order id $kiosk_order_id";
			$this->Flash->error($flashMessage);
		}		
		return $this->redirect(array('action' => "stock_transfer_by_kiosk/page:$current_page"));
	}
	
	public function checkoutStockTransferByKiosk(){
		$session_basket = $this->request->Session()->read('stBasket');
			
		if(is_array($session_basket)){
			$product_ids = array_keys($session_basket);
			$productCodeArr = array();
			//Note : this error caused by inderpreet. Find should run on kiosk_products not main products table
			$kiosk_id = $this->request->Session()->read('kiosk_id');
			if(!empty($kiosk_id)){
				$productSource = "kiosk_{$kiosk_id}_products";
				$productTable = TableRegistry::get($productSource,[
                                                                                    'table' => $productSource,
                                                                                ]);
			}else{
			   $productTable = TableRegistry::get("products",[
                                                                                    'table' => "products",
                                                                                ]);
			}
			
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
			foreach($session_basket as $key => $basketItem){
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
			foreach($session_basket as $productId=>$productDetails){
				$productNameArr_query = $productTable->find('all',array('conditions'=>array('id'=>$productId),'fields'=>array('id','product')));
				$productNameArr_query = $productNameArr_query->hydrate(false);
				if(!empty($productNameArr_query)){
					$productNameArr[] = $productNameArr_query->first();
				}else{
					$productNameArr = array();
				}
			}
			
			
			foreach($productNameArr as $key=>$selectedProducts){
				$productArr[$selectedProducts['id']] = $selectedProducts['product'];
			}
			if($this->request->is('post')){
				$error = array();
				if(array_key_exists('update_quantity',$this->request->data)){
					$lessProducts = array();
					$lowProducts = array();
					foreach($this->request->data['CheckOut'] as $productCode => $quantity){
						if($quantity == 0 || !(int)$quantity){
								$lowProducts[] = $productCode;
						}
						$availableQty = $productCodes[$productCode];
						if($quantity > $availableQty){
							$lessProducts[] = $productCode;
						}
					}
					if(count($lessProducts) >= 1){
						$this->Flash->error("Please choose ".implode(",",$lessProducts)." quantity less than or equal to available stock" );
						return $this->redirect(array('action'=>'checkout_stock_transfer_by_kiosk'));
					}
					if(count($lowProducts) > 0){
						 $this->Flash->error("Please choose  more than 0 for product : ".implode(",",$lowProducts) );
						return $this->redirect(array('action'=>'checkout_stock_transfer_by_kiosk'));
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
						$this->request->Session()->delete('stBasket');
						$this->request->Session()->write('stBasket',$newArray);
						$this->Flash->error("Quantity has been basket successfully updated");
						return $this->redirect(array('action'=>'checkout_stock_transfer_by_kiosk'));
					}
				}elseif(array_key_exists('edit_basket',$this->request->data)){
					return $this->redirect(array('action'=>"stock_transfer_by_kiosk"));
				}
			}
			$this->set(compact('productArr','productCode','productIds'));
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
			//pr($this->request);die;
			$stockTransfer = $this->request->data['StockTransfer'];
			// pr($stockTransfer);die;
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
				$productName_query = $this->Products->find('list',array('conditions'=>array('Products.id IN'=>$errorProductId),'fields'=>array('id','product')));
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
			   $products_cost_price = $this->Products->find('list',[
											'keyField' => 'id',
											'valueField' => 'cost_price',
											])->toArray();
			   
			   $products_selling_price = $this->Products->find('list',[
											'keyField' => 'id',
											'valueField' => 'selling_price',
											])->toArray();	
				foreach($stockTransfer['quantity'] as $qt => $quantity){
					//$this->OrderDisputes->clear();
					if(empty($quantity))continue;
					if(array_key_exists('receiving_status',$stockTransfer)){
						 if(array_key_exists($qt,$stockTransfer['receiving_status'])){
							  $receiving_status = $stockTransfer['receiving_status'][$qt];					 	  
						 }else{
							  $receiving_status = "";					 
						 }
						 
					}else{
						 $receiving_status = "";				
					}
					
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
									'cost_price' => $products_cost_price[$product_id],
									 'sale_price' => $products_selling_price[$product_id],
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
						$OrderDisputesEntity = $this->OrderDisputes->newEntity($orderDisputeData,['validate' => false]);
						$OrderDisputesEntity = $this->OrderDisputes->patchEntity($OrderDisputesEntity,$orderDisputeData,['validate' => false]);
						//pr($OrderDisputesEntity);die;
						if($this->OrderDisputes->save($OrderDisputesEntity,['validate' => false])){
							$recordSaved++;
							$isSaved = true;
						}else{
						}
						
					}catch(\Exception $e){
						//echo $e;die;
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
			$users_query = $this->Users->find('list', array(
													 'keyField' => 'id',
													 'valueField' => 'username',
													 ));
			$users_query = $users_query->hydrate(false);
			if(!empty($users_query)){
				  $users = $users_query->toArray();
			}else{
				  $users = array();
			}
			$options = array('conditions' => array('StockTransfer.kiosk_order_id' => $id),
							 'contain' => array('Products','KioskOrders')
							 );
			$products_query = $this->StockTransfer->find('all', $options);
			$products_query = $products_query->hydrate(false);
			if(!empty($products_query)){
				  $products = $products_query->toArray();
			}else{
				$products = array();  
			}
			//$this->set(compact('users','disputeOptions','kiosks','disputedProductIds','Productscode'));
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
																	'conditions'=>array('Products.id IN'=>$transferredProductIds),
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
			$disputed_data = $disputedProductIds = array();
			if(!empty($alreadyDisputedProducts)){
				foreach($alreadyDisputedProducts as $key=>$alreadyDisputedProduct){
					$disputedProductIds[] = $alreadyDisputedProduct['product_id'];
                    $disputed_data[$alreadyDisputedProduct['product_id']] = $alreadyDisputedProduct;
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
			$users_query = $this->Users->find('list', array(
													 'keyField' => 'id',
													 'valueField' => 'username',
													 ));
			$users_query = $users_query->hydrate(false);
			if(!empty($users_query)){
				  $users = $users_query->toArray();
			}else{
				  $users = array();
			}
			$options = array('conditions' => array('StockTransfer.kiosk_order_id' => $id),
							 'contain' => array('Products','KioskOrders')
							 );
			$products_query = $this->StockTransfer->find('all', $options);
			$products_query = $products_query->hydrate(false);
			if(!empty($products_query)){
				  $products = $products_query->toArray();
			}else{
				$products = array();  
			}
			
			$this->set(compact('users','disputeOptions','kiosks','disputedProductIds','Productscode','disputed_data'));
			$this->set('products', $products);
	}
    private function render_products(){
		$searchKW = '';
		$conditionArr = array();
		if(array_key_exists('search_kw',$this->request->query)){
			$searchKW = $this->request->query['search_kw'];
		}
		if(!empty($searchKW)){
			$conditionArr['OR']['LOWER(product) like '] =  strtolower("%$searchKW%");
			$conditionArr['OR']['LOWER(product_code) like '] =  strtolower("%$searchKW%");
		}
		$selectedCats = array();
		if(array_key_exists('category',$this->request->query)){
			//$conditionArr['category_id'] = $this->request->query['category'];
			$selectedCats = $this->request->query['category'];
			$conditionArr['AND']['category_id IN'] = $this->request->query['category'];
		}
        $conditionArr[] = "Products.quantity > 0";
        $this->paginate = [
                            'conditions' => $conditionArr,
                            'limit' => ROWS_PER_PAGE
                          ];
        //pr($this->paginate);die;
      $repProducts_query = $this->paginate('Products');
        if(!empty($repProducts_query)){
            $repProducts = $repProducts_query->toArray();
        }else{
            $repProducts = array();
        }
        
     //  pr($repProducts); die;
		$this->set(compact('repProducts'));
		//-----------------------------------------
         $categories = $this->Categories->find('all',array(
                                                        'fields' => array('id', 'category','id_name_path'),
                                                        'conditions' => array('Categories.status' => 1),
                                                        'order' => 'Categories.category asc' 
                                                        ));
        $categories->hydrate(false);
        if(!empty($categories)){
         $categories  = $categories->toArray();
        }else{
            $categories = array();
        }
		$categories = $this->CustomOptions->category_options($categories,true,$selectedCats);
		$this->set(compact('categories','searchKW'));
	}
    
    private function cancel_items($kiosk_id, $place_order_id, $cancelled_products){
		//echo $place_order_id;pr($cancelled_products);
		//kiosk_id, kiosk_placed_order_id, product_id,quantity, difference, remarks,
		$kioskOrderProducts_query = $this->KioskOrderProducts->find('all',array(
								   'conditions' => array(
														 'kiosk_placed_order_id' => $place_order_id,
														 'product_id IN' => $cancelled_products,
														 ) 
								   )
						       );
        $kioskOrderProducts_query = $kioskOrderProducts_query->hydrate(false);
        if(!empty($kioskOrderProducts_query)){
         $kioskOrderProducts  = $kioskOrderProducts_query->toArray();
        }else{
            $kioskOrderProducts = array();
        }
       // pr($kioskOrderProducts);die;
		$idForDeletion = array();
		$cancelledOrderIDs = array();
		foreach($kioskOrderProducts as $kioskOrderProduct){
			$dataArr = $kioskOrderProduct;
			$dataArr['cancelled_by'] = $this->request->Session()->read('Auth.User.id');
			//$this->KioskCancelledOrderProduct->create();
			$idForDeletion[] = $dataArr['id'];
			unset($dataArr['id']);
			unset($dataArr['created']);
			unset($dataArr['modified']);
             $KioskCancelledOrderProductEntity = $this->KioskCancelledOrderProducts->newEntity($dataArr,['validate' => false]);
            $KioskCancelledOrderProductEntity = $this->KioskCancelledOrderProducts->patchEntity($KioskCancelledOrderProductEntity,$dataArr,['validate' => false]);
            $this->KioskCancelledOrderProducts->save($KioskCancelledOrderProductEntity);
         //   pr($KioskCancelledOrderProductEntity);
            $cancelledOrderIDs[] = $KioskCancelledOrderProductEntity->id;
             
          //  pr($cancelledOrderIDs);die;
		}
		//deleted all cancelled ids from KioskOrderProduct
		$idStr = "'".implode("','",$idForDeletion)."'";
		$delQry = "DELETE FROM `kiosk_order_products` WHERE `id` IN($idStr)";
		$conn = ConnectionManager::get('default');
		  $stmt = $conn->execute($delQry); 
		//$this->KioskOrderProducts->query($delQry);
		//$errorStr = implode("<br/>",$error);
        $this->Flash->success("Order cancelled for these product ids: $idStr for order id:$place_order_id");
		 
		return $this->redirect(array('action'=>'placed_order',$place_order_id));
		die;
	}
    public function placedOrder($id = "",$repProduct = "") {
		// pr($this->request);  die;
		$repProduct = (int)$repProduct;
		if(!empty($repProduct)){
			$prodCodeRS_query = $this->Products->find('all',array(
                                                            'fields' => array('product_code'),
                                                            'conditions' => array('Products.id' => $repProduct)));
            $prodCodeRS_query =  $prodCodeRS_query->hydrate(false);
            if(!empty($prodCodeRS_query)){
            $prodCodeRS = $prodCodeRS_query->first();
             }else{
            $prodCodeRS  = array();
            }
       //pr($prodCodeRS);die;
			$prodCode = $prodCodeRS['product_code'];
			//pr($prodCodeRS);
			$this->set(compact('prodCode'));
			$this->render_products();
		}
		//die;
		if($this->request->is(array('post', 'put'))) {
	// pr($this->request->data);die;
			if(array_key_exists('KioskStock', $this->request->data)){
              $kioskStock = $this->request->data['KioskStock'];
				$kiosk_id = $kioskStock['kiosk_id'];
                $placed_order_id = $this->request->data['KioskStock']['placed_order_id']; 
			}
			if(
			   (array_key_exists('add_product', $this->request->data) && $this->request->data['add_product'] == 1) ||
			   (array_key_exists('newly_added_product', $this->request->data) && !empty($this->request->data['newly_added_product']))
			){
              //  echo "add_product";die;
				//  pr($this->request->data);die;
				$placedOrderID = (int)$id;
              //  pr($this->request->data);
				 $productID = $this->request->data['newly_added_product'] ;
				$KPO_query = $this->KioskPlacedOrders->find('all',array(
													'fields' => array('kiosk_id'),
													'recursive' => -1,
													'conditions' => array('KioskPlacedOrders.id' => $placedOrderID)
												  ));
                $KPO_query =  $KPO_query->hydrate(false);
                if(!empty($KPO_query)){
                    $KPO = $KPO_query->first();
                 }else{
                    $KPO  = array();
                }
               
				$kioskID = $KPO['kiosk_id'];
				$productCodeArr_query = $this->Products->find('list',[
                                                                        'keyField' => 'id',
                                                                        'valueField' => 'product_code',
                                                                        'conditions' =>['Products.id IN' =>$productID]
                                                                    ]
                                                        );
                $productCodeArr_query =  $productCodeArr_query->hydrate(false);
                if(!empty($productCodeArr_query)){
                    $productCodeArr = $productCodeArr_query->toArray();
                 }else{
                    $productCodeArr = array();
                }
                //pr($productCodeArr);die;
				//check if product is already existing for $placedOrderID
				$max_query = $this->KioskOrderProducts->find('all',array(
												'conditions' => array(
																	  'KioskOrderProducts.kiosk_placed_order_id' => $placedOrderID,
																	  'KioskOrderProducts.kiosk_id' => $kioskID,
																	  ) 
															 )
											   );
				$max_query
                            ->select(['last_sr_no' => $max_query->func()->max('sr_no ')]);
							
					$max_query = $max_query->hydrate(false);
					if(!empty($max_query)){
						 $max = $max_query->toArray();	 
					}else{
						 $max = array();
					}
					if(!empty($max)){
						 $last_sr_no = $max[0]['last_sr_no'];
					}else{
						 $last_sr_no = 0;
					}
					
				  $productExistRS_query = $this->KioskOrderProducts->find('all',array(
												'fields' => array('id'),
												'conditions' => array(
																	  'KioskOrderProducts.kiosk_placed_order_id' => $placedOrderID,
																	  'KioskOrderProducts.product_id IN' => $productID,
																	  'KioskOrderProducts.kiosk_id' => $kioskID,
																	  ) 
															 )
											   );
                 $productExistRS_query =  $productExistRS_query->hydrate(false);
                if(!empty($productExistRS_query)){
                    $productExistRS = $productExistRS_query->first();
                 }else{
                    $productExistRS = array();
                }
             //  echo "kk";
				if(!count($productExistRS)){
					$remarks = "Code:".$productCodeArr[$productID]." is newly added";
					$dataArr = array(
										'product_id' => $productID,
										'status' => '2',
										'remarks' => $remarks,
										'kiosk_placed_order_id' => $placedOrderID,
										'kiosk_id' => $kioskID,
										'quantity' => 1, //setting by default for newly added
										'sr_no' => $last_sr_no+1,
									 );
					 
					 //pr($dataArr);die;
                     $KioskOrderProducts = $this->KioskOrderProducts->newEntity();
                     $KioskOrderProducts = $this->KioskOrderProducts->patchEntity($KioskOrderProducts, $dataArr,['validate' => false]);
                    $this->KioskOrderProducts->save($KioskOrderProducts);
                    $kiosk_order_id = $KioskOrderProducts->id;
                    $this->Flash->success(__("Product (code: ".$productCodeArr[$productID].") added successfully for order id: $placedOrderID"));
					 
				}else{
                    $this->Flash->success(__("Product already existing for placed order: $placedOrderID"));
					 
				}
				return $this->redirect(array('action'=>'placed_order',$placedOrderID));
			}elseif(array_key_exists('ReplaceButton', $this->request->data) && $this->request->data['ReplaceButton'] == 1){
				//First step for replacing item when radio button is clicked for `Replace or Add`
				// pr($this->request);die;
               // echo "ReplaceButton";die;
				$replaceProduct = $this->request->data["replace_product"];
               // pr($replaceProduct);die;
				return $this->redirect(array('action'=>'placed_order',$placed_order_id, $replaceProduct));
			}elseif(
			   (array_key_exists('replace_product', $this->request->data) && $this->request->data['replace_product'] == 1) ||
			   (array_key_exists('PartsReplaced', $this->request->data))
			){
             // echo "replace_product";die;
				//Final step for replacing item after `Replace` button pressed
				// pr($this->request->data);
				$replacement = array_flip($this->request->data['PartsReplaced']['replacement']);
				$replaceBy = (int)$replacement['Replace'];
				$toBeReplaced = (int)$repProduct;
				$placedOrderID = (int)$id;
				$productCodeArr_query = $this->Products->find('list',array(
												  'keyField' => 'id',
												  'valueField' => 'product_code',
													'fields' => array('id', 'product_code'),
													'conditions' => array('Products.id IN' => array($toBeReplaced, $replaceBy))
												  ));
				$productCodeArr_query = $productCodeArr_query->hydrate(false);
				if(!empty($productCodeArr_query)){
					$productCodeArr = $productCodeArr_query->toArray();
				}else{
					$productCodeArr = array();
				}
				$remarks = "Code:".$productCodeArr[$toBeReplaced]." is replaced";
				//die;
				//No need to adjust quanties as stock is not delivered yet; Here we are just creating order
				$KioskOrderProduct_query = "UPDATE `kiosk_order_products` SET `product_id` = $replaceBy, `status` = '1', `remarks` = '$remarks' WHERE `product_id` = $toBeReplaced AND `kiosk_placed_order_id` = $placedOrderID";
				$conn = ConnectionManager::get('default');
			   $stmt = $conn->execute($KioskOrderProduct_query); 
	 
				$this->Flash->error("Product replaced successfully for order id: $placedOrderID");
				return $this->redirect(array('action'=>'placed_order',$placedOrderID));
			}elseif(array_key_exists('CancelButton', $this->request->data) && $this->request->data['CancelButton'] == 1){
				//Step for cancelling item(s) when `Cancel itme` button is pressed
               // echo "CancelButton";die;
				$cancelledItems = $this->request->data["cancelled_items"];
				$res = $this->cancel_items($kiosk_id, $placed_order_id,$cancelledItems);
				return $res;die;
			}
			
			$productArr = array();
			$productCounts = 0;
			
			$error = array();
           
			foreach($kioskStock['quantity'] as $key => $quantity){
				$productID = $kioskStock['product_id'][$key];
				$code_query = $this->Products->find('list',array('conditions' => array('id' => $productID),'keyField' => 'id','valueField' => 'product_code'));
				$code_query = $code_query->hydrate(false);
				if(!empty($code_query)){
					$code_data =  $code_query->toArray();
				}else{
					$code_data = array();
				}
				//pr($code_data);die;
				$product_code = $code_data[$productID];
				$currentQuantity = $kioskStock['p_quantity'][$key];
				if($currentQuantity<$quantity){
					$error[] = "Quantity chosen for product code: $product_code should be less than current quantity";
				}
			}
			
			$errorStr = '';
			if(count($error)>0){
				$errorStr = implode("<br/>",$error);
                $this->Flash->success($errorStr,['escape' => false]);
				 
				return $this->redirect(array('action'=>'placed_order',$id));
			}else{
				//pr($kioskStock['quantity']);die;
				foreach($kioskStock['quantity'] as $key => $quantity){
					$productID = $kioskStock['product_id'][$key];
					$productPrice = $kioskStock['price'][$key];
					$currentQuantity = $kioskStock['p_quantity'][$key];
					$remarks = $kioskStock['remarks'][$key];
					$kiosk_user_remarks = $kioskStock['kiosk_user_remarks'][$key];
					$kiosk_order_product_id = $kioskStock['kiosk_product_order_id'][$key];
					if($currentQuantity < $quantity){
						$error[] = "Quantity chosen for product id: $productID should be less than current quantity";
					}
					
					$productArr[$productID] = array(
									'quantity' => $quantity,
									'price' => $productPrice,
									'remarks' => $remarks,
									'kiosk_user_remarks' => $kiosk_user_remarks,
									'kiosk_order_product_id' => $kiosk_order_product_id,
									);
					$productCounts++;
				}
			}
			if(!empty($productArr)){
			       $prodtArr_query = $this->Products->find('list',
                                    [
                                        'keyField' => 'id',
                                        'valueField' => 'cost_price',
                                    ]);
                    $prodtArr_query = $prodtArr_query->hydrate(false);
                   if(!empty($prodtArr_query)){
                        $prodtArr = $prodtArr_query->toArray();
                   }else{
                       $prodtArr = array();
                   }
                    $prodtCatArr_query = $this->Products->find('list',
                                    [
                                        'keyField' => 'id',
                                        'valueField' => 'category_id',
                                    ]);
                $prodtCatArr_query = $prodtCatArr_query->hydrate(false);
               if(!empty($prodtCatArr_query)){
                    $prodtCatArr = $prodtCatArr_query->toArray();
               }else{
                   $prodtCatArr = array();
               }
				//$prodtArr_query = $this->Products->find('list',array('fields'=>array('id','cost_price'),'recursive'=>-1));
				//$prodtCatArr = $this->Products->find('list',array('fields'=>array('id','category_id'),'recursive'=>-1));
				//
				$datetime = date('Y-m-d H:i:s');
				$kioskOrderData = array(
							'kiosk_id' => $kiosk_id,//
							'user_id' => $this->request->Session()->read('Auth.User.id'),
							'dispatched_on' => $datetime,
							'kiosk_placed_order_id' => $placed_order_id,//
							'status' => 1
							);
                 
            $KioskOrderEntity = $this->KioskOrders->newEntity($kioskOrderData,['validate' => false]);
            $KioskOrderEntity = $this->KioskOrders->patchEntity($KioskOrderEntity,$kioskOrderData,['validate' => false]);
            $this->KioskOrders->save($KioskOrderEntity);
         //   pr($KioskCancelledOrderProductEntity);
            $kiosk_order_id = $KioskOrderEntity->id; 
                
				$timestamp = time();
				
				foreach($productArr as $productID => $productData){
					if(array_key_exists($productID,$prodtArr)){
						$cost_price = $prodtArr[$productID];
					}else{
						$cost_price = "";
					}
					//$this->StockTransfer->clear;
					//$this->StockTransfer->create();
					$price = $productData['price'];
					$quantity = $productData['quantity'];
					$remarks = $productData['remarks'];
					$kiosk_user_remarks = $productData['kiosk_user_remarks'];
					$kiosk_order_product_id = "";
					if(array_key_exists("kiosk_order_product_id",$productData)){
						 $kiosk_order_product_id = $productData["kiosk_order_product_id"];
					}
					
					$stockTransferData = array(
								'kiosk_order_id' => $kiosk_order_id,
								//'kiosk_placed_order_id' => $placed_order_id,
								'static_cost' => $cost_price,
								'cost_price' => $cost_price,
								'product_id' => $productID,
								'quantity' => $quantity,
								'sale_price' => $price,
								'status' => '1',
								'remarks' => $remarks,
								'kiosk_user_remarks' => $kiosk_user_remarks,
								'sr_no' => $kiosk_order_product_id,
								'kiosk_id' => $kiosk_id,//
								);
					
                     $StockTransferEntity = $this->StockTransfer->newEntity($stockTransferData,['validate' => false]);
            $StockTransferEntity = $this->StockTransfer->patchEntity($StockTransferEntity,$stockTransferData,['validate' => false]);
           if( $this->StockTransfer->save($StockTransferEntity)){
			   //echo $StockTransferEntity->id;die;
						$pushStr = "Products have been transferred under order # $kiosk_order_id. Please receive them";
						 $this->Pusher->email_kiosk_push($pushStr,$kiosk_id);
						// till here **
						$kioskTable = "kiosk_transferred_stock_$kiosk_id";
						$tableQuery = $this->TableDefinition->get_table_defination('transferred_stock',$kiosk_id);
						$conn = ConnectionManager::get('default');
					    $stmt = $conn->execute($tableQuery); 
						 
					
						$insertQuery = "INSERT INTO $kioskTable SET
									`kiosk_order_id` = $kiosk_order_id,
									`product_id` = $productID,
									`quantity` = $quantity,
									`sale_price` = $price,
									`created` = '$datetime',
									`status` = 1
								";
						$conn = ConnectionManager::get('default');
					    $stmt = $conn->execute($insertQuery); 
						
						$KioskPlacedOrdersEntity = $this->KioskPlacedOrders->get($placed_order_id);
						$data = array('status' => 1);
						$KioskPlacedOrdersEntity = $this->KioskPlacedOrders->patchEntity($KioskPlacedOrdersEntity,$data,['validate' => false]);
						$this->KioskPlacedOrders->save($KioskPlacedOrdersEntity);
						
						if($quantity==0)continue;
						$productData = array('quantity' => "Products.quantity - $quantity");
						$Products_query = "UPDATE `products` SET `quantity` = `quantity` - $quantity WHERE `products`.`id` = $productID";
						$conn = ConnectionManager::get('default');
					    $stmt = $conn->execute($Products_query); 
						$external_site_status = $site_id_to_save = 0;
						$isboloRam = false;
						 $path = dirname(__FILE__);
						  $sites = Configure::read('sites');
						  $external_sites = Configure::read('external_sites_for_bulk');
						  foreach($sites  as $key => $value){
							   $isboloRam = strpos($path,$value);
							   if($isboloRam){
								   if(isset($value) && in_array($value,$external_sites)){
										$external_site_status = 1;
								   }
								   $site_id_to_save = $key;
									break;
							   }
						  }
						// sourabh delete code
						if($isboloRam != false){
							$vat = $this->VAT;
							//$dummyproductArr = array('2151','2155','2388','2678','2988','4131','4753','5409','5411','5289','5287','6672');
							//if(in_array($productID,$dummyproductArr)){
							
						    $hp_conn = ConnectionManager::get('hpwaheguru');
							$productTable_source = "products";
							$transferStock_source = "transfer_understock";
							$ReserveStock_source = "reserved_products";
							$productTable = TableRegistry::get($productTable_source,[
																	'table' => $productTable_source,
																	'connection' => $hp_conn
																]);
							$transferStockTable = TableRegistry::get($transferStock_source,[
																	'table' => $transferStock_source,
																	'connection' => $hp_conn
																]);
							$reservStockTable = TableRegistry::get($ReserveStock_source,[
																	'table' => $ReserveStock_source,
																	'connection' => $hp_conn
																]);
							
								//$this->Products->setDataSource('ADMIN_DOMAIN_db');
								//$this->ReservedProducts->setDataSource('ADMIN_DOMAIN_db');
								//$this->TransferUnderstocks->setDataSource('ADMIN_DOMAIN_db');
								
								$prodtQtyArr_query = $productTable->find('list',array(
																					 'keyField' =>'id',
																					 'valueField' => 'quantity',
																					  'recursive'=>-1));
								$prodtQtyArr_query = $prodtQtyArr_query->hydrate(false);
								if(!empty($prodtQtyArr_query)){
								   $prodtQtyArr = $prodtQtyArr_query->toArray();
								}else{
								   $prodtQtyArr = array();
								}
								
								$prodtCostArr_query = $productTable->find('list',array(
																					   'keyField' =>'id',
																					   'valueField' => 'cost_price',
																					   'recursive'=>-1));
								$prodtCostArr_query = $prodtCostArr_query->hydrate(false);
								if(!empty($prodtCostArr_query)){
								   $prodtCostArr = $prodtCostArr_query->toArray();
								}else{
								   $prodtCostArr = array();
								}
								
								$prodtSaleArr_query = $productTable->find('list',array(
																					   'keyField' =>'id',
																					   'valueField' => 'selling_price',
																					   'recursive'=>-1));
								$prodtSaleArr_query = $prodtSaleArr_query->hydrate(false);
								if(!empty($prodtSaleArr_query)){
								   $prodtSaleArr = $prodtSaleArr_query->toArray();
								}else{
								   $prodtSaleArr = array();
								}
								//$prodtCatArr = $this->Product->find('list',array('fields'=>array('id','category_id'),'recursive'=>-1));
								
								$wheguruQtity = $prodtQtyArr[$productID];
								
								//$wheguruQtity = $prodtQtyArr[$productID];
								
								if($wheguruQtity < $quantity){
									$sellPrice = $prodtSaleArr[$productID];
									$a = $sellPrice*100;
									$b = $vat+100;
									$orignalPrice = $a/$b;
									
									$condArr = array(
															'product_id' => $productID,
															'DATE(created) = CURDATE()',
															'site_id' => $site_id_to_save,
													   );
									
									if($external_site_status == 1){
										$condArr['kiosk_id'] = $kiosk_id;
									 }
									
									$underStockResult_query = $transferStockTable->find('all',array(
																		  'conditions' => $condArr,
																		  'recursive'=>-1,
																		  ));
									$underStockResult_query = $underStockResult_query->hydrate(false);
									if(!empty($underStockResult_query)){
										$underStockResult = $underStockResult_query->first();
									}else{
										$underStockResult = array();
									}
									$qtyToSub = $wheguruQtity;
									$remainingQty = $quantity - $wheguruQtity;
									$understockData = array(
															'product_id' => $productID,
															'quantity' => $remainingQty,
															'cost_price' => $prodtCostArr[$productID],
															'sale_price' => $orignalPrice,
															'invoice_reference' => $timestamp,
															'category_id' => $prodtCatArr[$productID],
															'site_id' => $site_id_to_save,
															'kiosk_id' => $kiosk_id,
															);
									if(count($underStockResult) > 0){
										$understockId = $underStockResult['id'];
										//foreach($underStockResult as $key => $value){
										//	$understockId =  $value['id'];
										//}
										$TransferUnderstock_query = "UPDATE `transfer_understock` SET `quantity` = `quantity` + $remainingQty WHERE `transfer_understock`.`id` = $understockId";
										if($external_site_status == 1){
											 $TransferUnderstock_query .= " AND kiosk_id = $kiosk_id";
										  }
										 $stmt = $hp_conn->execute($TransferUnderstock_query); 
									}else{
										$trasferStock = $transferStockTable->newEntity();
										$trasferStock = $transferStockTable->patchEntity($trasferStock, $understockData,['validate' => false]);
										if($transferStockTable->save($trasferStock)){
											 
										}else{
											// pr($trasferStock->errors());die;
										}
										
										///$this->TransferUnderstock->save($understockData);
									}
									
												
								}else{
									$qtyToSub = $quantity;
								}
								$stmt1 = $hp_conn->execute('SELECT NOW() as created'); 
								   $currentTimeInfo = $stmt1 ->fetchAll('assoc');  
								   $currentTime = $currentTimeInfo[0]['created'];
								if($qtyToSub > 0){
									$Product_query="UPDATE `products` SET `quantity` = `quantity` - $qtyToSub WHERE `products`.`id` = $productID";
									$stmt = $hp_conn->execute($Product_query); 
									
									
									
									$conditionsArr = array(
														   'product_id' => $productID,
															'DATE(created) = CURDATE()',
															'status' => 0,
															'site_id' => $site_id_to_save,
														   );
									
									if($external_site_status == 1){
										$conditionsArr['kiosk_id'] = $kiosk_id;
									 }
									
									$rserveResult_query = $reservStockTable->find('all',array(
																		  'conditions' => $conditionsArr,
																		  'recursive'=>-1,
																		  ));
									$rserveResult_query = $rserveResult_query->hydrate(false);
									if(!empty($rserveResult_query)){
										$rserveResult = $rserveResult_query->first();
									}else{
										$rserveResult = array();
									}
									if(count($rserveResult) > 0){
										//foreach($rserveResult as $key => $value){
										$status =  $rserveResult['status'];
										if($status == 0){
											 $reserveId =  $rserveResult['id'];
											$oldQtity = $rserveResult['quantity'];
											
										//}
											 $ReservedProducts_query = "UPDATE `reserved_products` SET `quantity` = `quantity` + $qtyToSub WHERE `reserved_products`.`id` = $reserveId";
											  $stmt = $hp_conn->execute($ReservedProducts_query);  
										}else{
											 $reservedProductData = array(
																 'product_id' =>  $productID,
																 'category_id' => $prodtCatArr[$productID],
																 'quantity' => $qtyToSub,
																 'cost_price' => $cost_price,
																 'sale_price' => $price,
																 'created' => $currentTime,
																 'modified' => $currentTime,
																 'site_id' => $site_id_to_save,
																 'kiosk_id' => $kiosk_id,
																 );
										
											 $hp_conn->insert('reserved_products',
																			   $reservedProductData
																			, ['created' => 'datetime']);
										}
											
									}else{
										$reservedProductData = array(
																 'product_id' =>  $productID,
																 'category_id' => $prodtCatArr[$productID],
																 'quantity' => $qtyToSub,
																 'cost_price' => $cost_price,
																 'sale_price' => $price,
																 'created' => $currentTime,
																 'modified' => $currentTime,
																 'site_id' => $site_id_to_save,
																 'kiosk_id' => $kiosk_id,
																 );
										
										$hp_conn->insert('reserved_products',
																		  $reservedProductData
																	   , ['created' => 'datetime']);
										// $reservStock = $reservStockTable->newEntity();
										// $reservStock = $reservStockTable->patchEntity($reservStock, $reservedProductData);
										//$reservStockTable->save($reservStock);
										//$this->Product->setDataSource('default');
									}
								}
							//}
						}
						//$this->Product->setDataSource('default');
						//$this->ReservedProducts->setDataSource('default');
						//$this->TransferUnderstock->setDataSource('default');
						
					}else{
						
					}
				}
                 $this->Flash->success("Order placed/dispatched for $productCounts items for Order ID: $kiosk_order_id");
				return $this->redirect(array('controller' => 'kiosk_orders','action' => "placed_orders"));
			}else{
                $this->Flash->success("Failed to create order!");
				return $this->redirect(array('action'=>'placed_order',$id));
			}
		}else{
			$forprint = "yes";
			if(array_key_exists("1",$this->request->params["pass"])){
				$forprint =  $this->request->params["pass"]["1"];
			}
			if(array_key_exists("forprint",$this->request->data)){
				$forprint = $this->request->data["forprint"];
			}
			//without post block
			$org_product_qty = $productArr = array();
			$remarksArr = array();
			$categoryIdArr = array();
			$kiosk_query = $this->KioskPlacedOrders->find('all' , array(
									//'conditions' => array('id' => $id,'KioskPlacedOrder.status' => 0), //original
									'conditions' => array('id' => $id,),
									'fields' => array('user_id','kiosk_id','created','modified','lock_status','merged','merge_data'),
									'recursive' => -1,
									'order' => 'KioskPlacedOrders.id desc'
								       ));
            $kiosk_query =  $kiosk_query->hydrate(false);
            if(!empty($kiosk_query)){
            $kiosk = $kiosk_query->first();
             }else{
            $kiosk  = array();
            }
			
			$KioskPlacedOrders_data = $kiosk;
           //  pr($kiosk);
            
            $kiosk_id = $kiosk['kiosk_id'];
			$created = $kiosk['created'];
            
            $modified_on = $kiosk['modified'];
            if(!empty($modified_on)){
                  $modified_on->i18nFormat(
                                                    [\IntlDateFormatter::FULL, \IntlDateFormatter::FULL]
                                            );
				$modified_date =  $modified_on->i18nFormat('dd-MM-yyyy HH:mm:ss');
                $modified_date = date("d-m-y h:i a",strtotime($modified_date)); 
            }else{
                $modified_date = "--";
            }
            
			$modified = $modified_date;
			$placedby = $kiosk['user_id'] ;
            $users_query = $this->Users->find('list',
                                    [
                                        'keyField' => 'id',
                                        'valueField' => 'username',
                                    ]);
            if(!empty($users_query)){
                 $users = $users_query->toArray();
            }else{
                $users = array();
            }
            
			$kiosk_name_query = $this->Kiosks->findAllById($kiosk_id,array('id','name'));
            if(!empty($kiosk_name_query)){
                 $kiosk = $kiosk_name_query->toArray();
            }else{
                $kiosk = array();
            }
          //  pr($kiosk);die;
			$kioskOrderProducts_query = $this->KioskOrderProducts->find('all',array(
								   'conditions' => array('kiosk_placed_order_id' => $id),
								    'fields' => array('id','product_id','quantity','difference','remarks','status','org_qty','sr_no'),
								   )
						       );
            $kioskOrderProducts_query->hydrate(false);
            if(!empty($kioskOrderProducts_query)){
                $kioskOrderProducts  = $kioskOrderProducts_query->toArray();
            }else{
                $kioskOrderProducts = array();
            }
            
			
			
			$kioskCancelOrderProducts_query = $this->KioskCancelledOrderProducts->find('all',array(
								   'conditions' => array('kiosk_placed_order_id' => $id) 
								   
								   )
						       );
            $kioskCancelOrderProducts_query->hydrate(false);
            if(!empty($kioskCancelOrderProducts_query)){
            $kioskCancelOrderProducts  = $kioskCancelOrderProducts_query->toArray();
            }else{
            $kioskCancelOrderProducts = array();
            }
		//	 pr($kioskCancelOrderProducts);die;
			$canSrNoArr = $cancelProducts = array();
			if(!empty($kioskCancelOrderProducts)){
				foreach($kioskCancelOrderProducts as $kioskCancelOrderProduct){
					$productCancelArr[$kioskCancelOrderProduct['product_id']] = $kioskCancelOrderProduct['quantity'];
					$cnaceldiferenceArr[$kioskCancelOrderProduct['product_id']] = $kioskCancelOrderProduct['difference'];
					$cancelRemarksArr[$kioskCancelOrderProduct['product_id']] = $kioskCancelOrderProduct['remarks'];
					$canProductArr[$kioskCancelOrderProduct['product_id']] = $kioskCancelOrderProduct['quantity'];
					$canSrNoArr[$kioskCancelOrderProduct['product_id']] = $kioskCancelOrderProduct['sr_no'];
				}
				
				if(!empty($productCancelArr)){
					$productCancelIds = array_keys($productCancelArr);
                    if(empty($productCancelIds)){
                        $productCancelIds = array('0'=>null);
                    }
					$cancelProducts_query = $this->Products->find('all', array(
							   'conditions' => array('Products.id IN' => $productCancelIds),
							   'order' => 'Products.category_id asc',
							   'recursive' => -1
							  ));
                    $cancelProducts_query->hydrate(false);
                    if(!empty($cancelProducts_query)){
                         $cancelProducts  = $cancelProducts_query->toArray();
                    }else{
                         $cancelProducts = array();
                    }
                   // pr($cancelProducts);
				}
				foreach($cancelProducts as $key1 => $product_data1){
					$cancelcategoryIdArr[] = $product_data1['category_id'];
				}
                 $cancelCategoryList_query = $this->Categories->find('list',[
														  'conditions'=>['Categories.id IN'=>$cancelcategoryIdArr],
														  'keyField'=> 'id',
														  'valueField' => 'category'
														 ]);
                //pr($categoryName);die;
                if(!empty($cancelCategoryList_query)){
                 $cancelCategoryList  = $cancelCategoryList_query->toArray();
                }else{
                     $cancelCategoryList = array();
                }
				 
			}
			
			// pr($kioskOrderProducts);die;
			$product_sr_no = array();
			foreach($kioskOrderProducts as $kioskOrderProduct){
			   $org_product_qty[$kioskOrderProduct['product_id']] = $kioskOrderProduct['org_qty'];
				$diferenceArr[$kioskOrderProduct['product_id']] = $kioskOrderProduct['difference'];
				$productArr[$kioskOrderProduct['product_id']] = $kioskOrderProduct['quantity'];
				$remarksArr[$kioskOrderProduct['product_id']] = $kioskOrderProduct['remarks'];
				$statusArr[$kioskOrderProduct['product_id']] = $kioskOrderProduct['status'];
				
			}
			
			
			
			//pr($statusArr); die;
			$productIds = array_keys($productArr);
		 if(empty($productIds)){
		  $productIds = array(0 => null);
		 }
			$products_query = $this->Products->find('all', array(
							   'conditions' => array('Products.id IN' => $productIds),
							   'order' => 'Products.category_id asc',
							   'recursive' => -1
							  ));
             $products_query->hydrate(false);
                    if(!empty($products_query)){
                         $products  = $products_query->toArray();
                    }else{
                         $products = array();
                    }
			 
			foreach($products as $key => $product_data){
				$categoryIdArr[] = $product_data['category_id'];
			}
			
			
			$res_data_query = $this->MobilePlacedOrders->find('all',array('conditions' => array('kiosk_placed_order_id' => $id)));
             $res_data_query->hydrate(false);
                    if(!empty($res_data_query)){
                         $res_data  = $res_data_query->toArray();
                    }else{
                         $res_data = array();
                    }
			$brand_query = $this->Brands->find('list',[
                                                            'keyField'=> 'id',
														  'valueField' => 'brand'
												]
												);
             $brand_query->hydrate(false);
            if(!empty($brand_query)){
                 $brand  = $brand_query->toArray();
            }else{
                 $brand = array();
            }
			$models_query = $this->MobileModels->find('list',[
                                                            'keyField'=> 'id',
														  'valueField' => 'model'
                                                        ]
                                                      
                                                     );
             $models_query->hydrate(false);
                    if(!empty($models_query)){
                         $models  = $models_query->toArray();
                    }else{
                         $models = array();
                    }
			$this->set(compact('brand','models'));
			$this->set(compact('res_data'));
			if(empty($categoryIdArr)){
			   $categoryIdArr = array(0 => null);
			}
			$categoryList_query = $this->Categories->find('list',[
														  'conditions'=>['Categories.id IN'=>$categoryIdArr],
														  'keyField'=> 'id',
														  'valueField' => 'category'
														 ]);
            
                //pr($categoryName);die;
                $categoryList_query->hydrate(false);
                if(!empty($categoryList_query)){
                 $categoryList  = $categoryList_query->toArray();
                }else{
                     $categoryList = array();
                }
				
				$user_group_data = $this->Users->find("list",[
										   'keyField' => "id",
										   'valueField' => "group_id"
										   ])->toArray();
                
			//$categoryList = $this->Category->find('list', array('conditions' => array('Category.id' => $categoryIdArr), 'fields' => array('id', 'category')));
			
			
			
			if($KioskPlacedOrders_data['lock_status'] == 1){
			   $counter = 0;
			   foreach($products as $k => $v){
				  $counter++;
				  $p_id = $v['id'];
				  $get_query = "SELECT * FROM  `kiosk_order_products`  WHERE `kiosk_placed_order_id` = $id AND `product_id` = $p_id";
				  $conn = ConnectionManager::get('default');
				  $stmt = $conn->execute($get_query);
				  $res = $stmt ->fetchAll('assoc');  
				  if(empty($res[0]['sr_no'])){
					   $update_query = "UPDATE `kiosk_order_products` SET `sr_no` = $counter WHERE `kiosk_placed_order_id` = $id AND `product_id` = $p_id";
					   $conn = ConnectionManager::get('default');
					   $stmt = $conn->execute($update_query); 	
				  } 
			   }   
			}
			
			
			$kioskOrderProducts_query = $this->KioskOrderProducts->find('all',array(
								   'conditions' => array('kiosk_placed_order_id' => $id),
								    'fields' => array('id','product_id','quantity','difference','remarks','status','org_qty','sr_no'),
								   )
						       );
            $kioskOrderProducts_query->hydrate(false);
            if(!empty($kioskOrderProducts_query)){
                $kioskOrderProducts  = $kioskOrderProducts_query->toArray();
            }else{
                $kioskOrderProducts = array();
            }
			
			foreach($kioskOrderProducts as $kioskOrderProduct){
				$product_sr_no[$kioskOrderProduct['product_id']] = $kioskOrderProduct['sr_no'];
			}
			
			if($KioskPlacedOrders_data['lock_status'] == 1){
			   $product_new_arr = array();
			   if(!empty($products)){
				  foreach($products as $p_key => $p_val){
					   $product_new_arr[$product_sr_no[$p_val['id']]] = $p_val;
				  }
			   }
			   ksort($product_new_arr);   
			}else{
			   $product_new_arr = $products;
			}
			
			
			
			$this->set(compact('user_group_data','cnaceldiferenceArr','canProductArr','cancelRemarksArr','kioskCancelOrderProducts','cancelCategoryList','cancelProducts','statusArr','products','remarksArr','productArr','diferenceArr','created','users','placedby','modified','categoryList','forprint','org_product_qty','KioskPlacedOrders_data','product_sr_no','product_new_arr','canSrNoArr'));
			$this->set('kiosk', $kiosk[0]);
		}
	}
	
	public function placedOrderOnDemand($id = "",$repProduct = "") {
		//pr($this->request);die;
		$repProduct = (int)$repProduct;
		if(!empty($repProduct)){
			$prodCodeRS_query = $this->Products->find('all',array('fields' => array('product_code'),
																  'conditions' => array('Products.id' => $repProduct)));
			$prodCodeRS_query = $prodCodeRS_query->hydrate(false);
			if(!empty($prodCodeRS_query)){
			   $prodCodeRS = $prodCodeRS_query->first();
			}else{
			   $prodCodeRS = array();
			}
			$prodCode = $prodCodeRS['product_code'];
			//pr($prodCodeRS);
			$this->set(compact('prodCode'));
			$this->render_products();
		}
		if($this->request->is(array('post', 'put'))) {
		  
		//  pr($this->request);die;
			if(array_key_exists('KioskStock', $this->request->data)){
				$kioskStock = $this->request['data']['KioskStock'];
				$kiosk_id = $kioskStock['kiosk_id'];
				if(array_key_exists('placed_order_id',$this->request['data']['KioskStock'])){
					$placed_order_id = $this->request['data']['KioskStock']['placed_order_id'];	
				}
			}
			if(
			   (array_key_exists('add_product', $this->request->data) && $this->request->data['add_product'] == 1) ||
			   (array_key_exists('newly_added_product', $this->request->data) && !empty($this->request->data['newly_added_product']))
			){
				//pr($this->request);die;
				$placedOrderID = (int)$id;
				$productID = $this->request->data['newly_added_product'];
				$KPO_query = $this->OnDemandOrders->find('all',array(
													'fields' => array('kiosk_id'),
													'conditions' => array('OnDemandOrders.id' => $placedOrderID)
												  ));
				$KPO_query = $KPO_query->hydrate(false);
				if(!empty($KPO_query)){
					$KPO = $KPO_query->first();
				}else{
					$KPO = array();
				}
				$kioskID = $KPO['kiosk_id'];
				$productCodeArr_query = $this->Products->find('list',array(
													   'keyField' => 'id',
													   'valueField' => 'product_code',
													'conditions' => array('Products.id IN' => array($productID))
												  ));
				$productCodeArr_query = $productCodeArr_query->hydrate(false);
				if(!empty($productCodeArr_query)){
					$productCodeArr = $productCodeArr_query->toArray();
				}else{
					$productCodeArr = array();
				}
				//check if product is already existing for $placedOrderID
				$productExistRS_query = $this->OnDemandProducts->find('all',array(
												'fields' => array('id'),
												'conditions' => array(
																	  'OnDemandProducts.kiosk_placed_order_id' => $placedOrderID,
																	  'OnDemandProducts.product_id' => $productID,
																	  'OnDemandProducts.kiosk_id' => $kioskID,
																	  ),
												'recursive' => -1,
															 )
											   );
				$productExistRS_query = $productExistRS_query->hydrate(false);
				if(!empty($productExistRS_query)){
					$productExistRS = $productExistRS_query->first();
				}else{
					$productExistRS = array();
				}
				if(!count($productExistRS)){
					$remarks = "On Demamd ,Code:".$productCodeArr[$productID]." is newly added";
					$dataArr = array(
										'product_id' => $productID,
										'status' => '2',
										'remarks' => $remarks,
										'kiosk_placed_order_id' => $placedOrderID,
										'kiosk_id' => $kioskID,
										'quantity' => 1 //setting by default for newly added
									 );
					$OnDemandProductsEntity = $this->OnDemandProducts->newEntity($dataArr,['validate' => false]);
					$OnDemandProductsEntity = $this->OnDemandProducts->patchEntity($OnDemandProductsEntity,$dataArr,['validate' => false]);
					//pr($dataArr);die;
					$this->OnDemandProducts->save($OnDemandProductsEntity);
					$kiosk_order_id = $OnDemandProductsEntity->id;
					$this->Flash->error("Product (code: ".$productCodeArr[$productID].") added successfully for order id: $placedOrderID");
				}else{
					$this->Flash->error("Product already existing for placed order: $placedOrderID");
				}
				return $this->redirect(array('action'=>'placed_order_on_demand',$placedOrderID));
			}elseif(array_key_exists('ReplaceButton', $this->request->data) && $this->request->data['ReplaceButton'] == 1){
				
				//First step for replacing item when radio button is clicked for `Replace or Add`
				//pr($this->request);die;
				$replaceProduct = $this->request->data["replace_product"];
				return $this->redirect(array('action'=>'placed_order_on_demand',$placed_order_id, $replaceProduct));
			}elseif(
			   (array_key_exists('replace_product', $this->request->data) && $this->request->data['replace_product'] == 1) ||
			   (array_key_exists('PartsReplaced', $this->request->data))
			){
			 //  pr($this->request);die;
				//Final step for replacing item after `Replace` button pressed
				//pr($this->request);
				$replacement = array_flip($this->request->data['PartsReplaced']['replacement']);
				$replaceBy = (int)$replacement['Replace'];
				$toBeReplaced = (int)$repProduct;
				$placedOrderID = (int)$id;
				$productCodeArr_query = $this->Products->find('list',array(
												  'keyField' => 'id',
												  'valueField' => 'product_code',
													'conditions' => array('Products.id IN' => array($toBeReplaced, $replaceBy))
												  ));
				$productCodeArr_query = $productCodeArr_query->hydrate(false);
				if(!empty($productCodeArr_query)){
					$productCodeArr = $productCodeArr_query->toArray();
				}else{
					$productCodeArr = array();
				}
				$remarks = "On Demamd ,Code:".$productCodeArr[$toBeReplaced]." is replaced";
				//die;
				//No need to adjust quanties as stock is not delivered yet; Here we are just creating order
				$KioskOrderProduct_query = "UPDATE `on_demand_products` SET `product_id` = $replaceBy, `status` = '1', `remarks` = '$remarks' WHERE `product_id` = $toBeReplaced AND `kiosk_placed_order_id` = $placedOrderID";
				
				$conn = ConnectionManager::get('default');
					$stmt = $conn->execute($KioskOrderProduct_query); 
					
				$this->Flash->error("Product replaced successfully for order id: $placedOrderID");
				return $this->redirect(array('action'=>'placed_order_on_demand',$placedOrderID));
			}elseif(array_key_exists('CancelButton', $this->request->data) && $this->request->data['CancelButton'] == 1){
				//Step for cancelling item(s) when `Cancel itme` button is pressed
				$cancelledItems = $this->request->data["cancelled_items"];
				$this->cancel_items_ondemand($kiosk_id, $placed_order_id,$cancelledItems);
			}
			$productArr = array();
			$productCounts = 0;
			//echo "hi";die;
			$error = array();
			if(isset($kioskStock) && !empty($kioskStock)){
			   if(array_key_exists('quantity',$kioskStock)){
				foreach($kioskStock['quantity'] as $key => $quantity){
					$productID = $kioskStock['product_id'][$key];
					$code_query = $this->Products->find('list',array('conditions' => array('id' => $productID),'keyField' => 'id','valueField' => 'product_code'));
					$code_query = $code_query->hydrate(false);
					if(!empty($code_query)){
						$code_data =  $code_query->toArray();
					}else{
						$code_data = array();
					}
					//pr($code_data);die;
					$product_code = $code_data[$productID];
					$currentQuantity = $kioskStock['p_quantity'][$key];
					if($currentQuantity<$quantity){
						$error[] = "Quantity chosen for product code: $product_code should be less than current quantity";
					}
				}
			   }
			}else{
				$error[] = "Error";
			}
			
			$errorStr = '';
			if(count($error)>0){
				$errorStr = implode("<br/>",$error);
				$this->Flash->error($errorStr,array('escape' => false));
				return $this->redirect(array('action'=>'placed_order_on_demand',$id));
			}else{
				//pr($kioskStock);die;
				//pr($kioskStock['quantity']);die;
				if(array_key_exists('quantity',$kioskStock)){
					foreach($kioskStock['quantity'] as $key => $quantity){
						$productID = $kioskStock['product_id'][$key];
						$productPrice = $kioskStock['price'][$key];
						$currentQuantity = $kioskStock['p_quantity'][$key];
						$remarks = $kioskStock['remarks'][$key];
						$kiosk_user_remarks = $kioskStock['kiosk_user_remarks'][$key];
						$kiosk_order_product_id = $kioskStock['kiosk_product_order_id'][$key];
						if($currentQuantity < $quantity){
							$error[] = "Quantity chosen for product id: $productID should be less than current quantity";
						}
						
						$productArr[$productID] = array(
										'quantity' => $quantity,
										'price' => $productPrice,
										'remarks' => $remarks,
										'kiosk_user_remarks' => $kiosk_user_remarks,
										'kiosk_order_product_id' => $kiosk_order_product_id,
										);
						$productCounts++;
					}
				}
			}
			if(!empty($productArr)){
				//pr($productArr);die;
				$prodtArr_query = $this->Products->find('list',array(
																	 'keyField' => 'id',
																	 'valueField' => 'cost_price',
																	 ));
				$prodtArr_query = $prodtArr_query->hydrate(false);
				if(!empty($prodtArr_query)){
					$prodtArr = $prodtArr_query->toArray();
				}else{
					$prodtArr = array();
				}
				$prodtCatArr_query = $this->Products->find('list',array(
																  'keyField' => 'id',
																  'valueField' => 'category_id',
																  ));
				$prodtCatArr_query = $prodtCatArr_query->hydrate(false);
				if(!empty($prodtCatArr_query)){
					$prodtCatArr = $prodtCatArr_query->toArray();
				}else{
					$prodtCatArr = array();
				}
				$datetime = date('Y-m-d H:i:s');
				$kioskOrderData = array(
							'kiosk_id' => $kiosk_id,//
							'user_id' => $this->request->Session()->read('Auth.User.id'),
							'dispatched_on' => $datetime,
							'kiosk_placed_order_id' => $placed_order_id,//
							'status' => 1,
							'is_on_demand' => 1
							);
				$KioskOrderEntity = $this->KioskOrders->newEntity($kioskOrderData,['validate' => false]);
				$KioskOrderEntity = $this->KioskOrders->patchEntity($KioskOrderEntity,$kioskOrderData,['validate' => false]);
				if($this->KioskOrders->save($KioskOrderEntity)){
					;//all good
				}else{
					 $errors = array();
					if(!empty($KioskOrderEntity->errors())){
						 foreach($KioskOrderEntity->errors() as $key){
							  foreach($key as $value){
								   $errors[] = $value;  
							  }
						 }
					}
					$this->Flash->error(implode("</br>",$errors),['escape' => false]);
					return $this->redirect(array('action'=>'placed_order_on_demand',$id));
				}
				$kiosk_order_id = $KioskOrderEntity->id;
				$timestamp = time();
				foreach($productArr as $productID => $productData){
					if(array_key_exists($productID,$prodtArr)){
						$cost_price = $prodtArr[$productID];
					}else{
						$cost_price = "";
					}
					
					$price = $productData['price'];
					$quantity = $productData['quantity'];
					$remarks = $productData['remarks'];
					$kiosk_user_remarks = $productData['kiosk_user_remarks'];
					$kiosk_order_product_id = $productData['kiosk_order_product_id'];
					$stockTransferData = array(
								'kiosk_order_id' => $kiosk_order_id,
								//'kiosk_placed_order_id' => $placed_order_id,
								'static_cost' => $cost_price,
								'cost_price' => $cost_price,
								'product_id' => $productID,
								'quantity' => $quantity,
								'sale_price' => $price,
								'status' => '1',
								'remarks' => $remarks,
								'kiosk_user_remarks' => $kiosk_user_remarks,
								'is_on_demand' => 1,
								'sr_no' => $kiosk_order_product_id,
								'kiosk_id' => $kiosk_id,
								);
					
					$StockTransferEntity = $this->StockTransfer->newEntity($stockTransferData,['validate' => false]);
					$StockTransferEntity = $this->StockTransfer->patchEntity($StockTransferEntity,$stockTransferData,['validate' => false]);
					
					if($this->StockTransfer->save($StockTransferEntity)){
						//** code for sending pusher messages
						$pushStr = "Products have been transferred under order # $kiosk_order_id. Please receive them";
						 $this->Pusher->email_kiosk_push($pushStr,$kiosk_id);
						// till here **
						$kioskTable = "kiosk_transferred_stock_$kiosk_id";
						$tableQuery = $this->TableDefinition->get_table_defination('transferred_stock',$kiosk_id);
						
						$conn = ConnectionManager::get('default');
					    $stmt = $conn->execute($tableQuery); 

						$insertQuery = "INSERT INTO $kioskTable SET
									`kiosk_order_id` = $kiosk_order_id,
									`product_id` = $productID,
									`quantity` = $quantity,
									`sale_price` = $price,
									`created` = '$datetime',
									`status` = 1
								";
						 $conn = ConnectionManager::get('default');
					    $stmt = $conn->execute($insertQuery);
						
						//decreasing central stock
						
						//$this->Product->id = $productID;
						/*$this->Product->read(null, $productID);
						$this->Product->set(array(
							'Product.quantity' => "Product.quantity - $quantity"
						));*/
						$OnDemandOrders_Entity = $this->OnDemandOrders->get($placed_order_id);
						$data = array('status' => 1);
						$OnDemandOrders_Entity = $this->OnDemandOrders->patchEntity($OnDemandOrders_Entity,$data,['validate' => false]);
						$this->OnDemandOrders->save($OnDemandOrders_Entity);
						
						if($quantity==0)continue;
						$productData = array('quantity' => "Product.quantity - $quantity");
						$Product_query = "UPDATE `products` SET `quantity` = `quantity` - $quantity WHERE `products`.`id` = $productID";
						
					    $conn = ConnectionManager::get('default');
					    $stmt = $conn->execute($Product_query);
						
						$external_site_status = $site_id_to_save = 0;
						$isboloRam = false;
						 $path = dirname(__FILE__);
						  $sites = Configure::read('sites');
						  $external_sites = Configure::read('external_sites_for_bulk');
						  foreach($sites  as $key => $value){
							   $isboloRam = strpos($path,$value);
							   if($isboloRam){
								   if(isset($value) && in_array($value,$external_sites)){
										$external_site_status = 1;
								   }
								   $site_id_to_save = $key;
									break;
							   }
						  }
						// sourabh delete code
						if($isboloRam != false){
							$vat = $this->VAT;
							//$dummyproductArr = array('2151','2155','2388','2678','2988','4131','4753','5409','5411','5289','5287','6672');
							//if(in_array($productID,$dummyproductArr)){
								$hp_conn = ConnectionManager::get('hpwaheguru');
							$productTable_source = "products";
							$transferStock_source = "transfer_understock";
							$ReserveStock_source = "reserved_products";
							$productTable = TableRegistry::get($productTable_source,[
																	'table' => $productTable_source,
																	'connection' => $hp_conn
																]);
							$transferStockTable = TableRegistry::get($transferStock_source,[
																	'table' => $transferStock_source,
																	'connection' => $hp_conn
																]);
							$reservStockTable = TableRegistry::get($ReserveStock_source,[
																	'table' => $ReserveStock_source,
																	'connection' => $hp_conn
																]);
							
								//$this->Products->setDataSource('ADMIN_DOMAIN_db');
								//$this->ReservedProducts->setDataSource('ADMIN_DOMAIN_db');
								//$this->TransferUnderstocks->setDataSource('ADMIN_DOMAIN_db');
								
								$prodtQtyArr_query = $productTable->find('list',array(
																					 'keyField' =>'id',
																					 'valueField' => 'quantity',
																					  'recursive'=>-1));
								$prodtQtyArr_query = $prodtQtyArr_query->hydrate(false);
								if(!empty($prodtQtyArr_query)){
								   $prodtQtyArr = $prodtQtyArr_query->toArray();
								}else{
								   $prodtQtyArr = array();
								}
								
								$prodtCostArr_query = $productTable->find('list',array(
																					   'keyField' =>'id',
																					   'valueField' => 'cost_price',
																					   'recursive'=>-1));
								$prodtCostArr_query = $prodtCostArr_query->hydrate(false);
								if(!empty($prodtCostArr_query)){
								   $prodtCostArr = $prodtCostArr_query->toArray();
								}else{
								   $prodtCostArr = array();
								}
								
								$prodtSaleArr_query = $productTable->find('list',array(
																					   'keyField' =>'id',
																					   'valueField' => 'selling_price',
																					   'recursive'=>-1));
								$prodtSaleArr_query = $prodtSaleArr_query->hydrate(false);
								if(!empty($prodtSaleArr_query)){
								   $prodtSaleArr = $prodtSaleArr_query->toArray();
								}else{
								   $prodtSaleArr = array();
								}
								//$prodtCatArr = $this->Product->find('list',array('fields'=>array('id','category_id'),'recursive'=>-1));
								
								$wheguruQtity = $prodtQtyArr[$productID];
								
								if($wheguruQtity < $quantity){
									$sellPrice = $prodtSaleArr[$productID];
									$a = $sellPrice*100;
									$b = $vat+100;
									$orignalPrice = $a/$b;
									
									$condArr = array(
															'product_id' => $productID,
															'DATE(created) = CURDATE()',
															'site_id' => $site_id_to_save,
													   );
									
									if($external_site_status == 1){
										$condArr['kiosk_id'] = $kiosk_id;
									 }
									
									$underStockResult_query = $transferStockTable->find('all',array(
																		  'conditions' => $condArr,
																		  'recursive'=>-1,
																		  ));
									$underStockResult_query = $underStockResult_query->hydrate(false);
									if(!empty($underStockResult_query)){
										$underStockResult = $underStockResult_query->first();
									}else{
										$underStockResult = array();
									}
									
									$qtyToSub = $wheguruQtity;
									$remainingQty = $quantity - $wheguruQtity;
									$understockData = array(
															'product_id' => $productID,
															'quantity' => $remainingQty,
															'cost_price' => $prodtCostArr[$productID],
															'sale_price' => $orignalPrice,
															'invoice_reference' => $timestamp,
															'category_id' => $prodtCatArr[$productID],
															'site_id' => $site_id_to_save,
															'kiosk_id' => $kiosk_id,
															);
									if(count($underStockResult) > 0){
										
										$understockId =  $underStockResult['id'];
										$TransferUnderstock_query = "UPDATE `transfer_understock` SET `quantity` = `quantity` + $remainingQty WHERE `transfer_understock`.`id` = $understockId";
										if($external_site_status == 1){
											 $TransferUnderstock_query .= " AND kiosk_id = $kiosk_id";
										  }
										$stmt = $hp_conn->execute($TransferUnderstock_query); 
									}else{
										
										$trasferStock = $transferStockTable->newEntity();
										$trasferStock = $transferStockTable->patchEntity($trasferStock, $understockData);
										if($transferStockTable->save($trasferStock)){
											 
										}else{
											 //pr($trasferStock->errors());die;
										}
									}
									
										
								}else{
									$qtyToSub = $quantity;
								}
								$stmt1 = $hp_conn->execute('SELECT NOW() as created'); 
								   $currentTimeInfo = $stmt1 ->fetchAll('assoc');  
								   $currentTime = $currentTimeInfo[0]['created'];
								if($qtyToSub > 0){
									$Product_query = "UPDATE `products` SET `quantity` = `quantity` - $qtyToSub WHERE `products`.`id` = $productID";
												
									$stmt = $hp_conn->execute($Product_query); 
									
									$conditionsArr = array(
														   'product_id' => $productID,
															'DATE(created) = CURDATE()',
															'status' => 0,
															'site_id' => $site_id_to_save,
														   );
									
									if($external_site_status == 1){
										$conditionsArr['kiosk_id'] = $kiosk_id;
									 }
									
												
									$rserveResult_query = $reservStockTable->find('all',array(
																		 'conditions' => $conditionsArr,
																		  'recursive'=>-1,
																		  ));
									$rserveResult_query = $rserveResult_query->hydrate(false);
									if(!empty($rserveResult_query)){
										$rserveResult = $rserveResult_query->first();
									}else{
										$rserveResult = array();
									}
									if(count($rserveResult) > 0){
										//foreach($rserveResult as $key => $value){
											 $status =  $rserveResult['status'];
											 if($status == 0){
												 $reserveId =  $rserveResult['id'];
											     $oldQtity = $rserveResult['quantity'];
												 $ReservedProducts_query = "UPDATE `reserved_products` SET `quantity` = `quantity` + $qtyToSub WHERE `reserved_products`.`id` = $reserveId";
												  $stmt = $hp_conn->execute($ReservedProducts_query);  
											 }else{
												  $reservedProductData = array(
																 'product_id' =>  $productID,
																 'category_id' => $prodtCatArr[$productID],
																 'quantity' => $qtyToSub,
																 'cost_price' => $cost_price,
																 'sale_price' => $price,
																 'created' => $currentTime,
																 'modified' => $currentTime,
																 'site_id' => $site_id_to_save,
																 'kiosk_id' => $kiosk_id,
																 );
										
												  $hp_conn->insert('reserved_products',
																					$reservedProductData
																				 , ['created' => 'datetime']);
											 }
									}else{
										$reservedProductData = array(
																 'product_id' =>  $productID,
																 'category_id' => $prodtCatArr[$productID],
																 'quantity' => $qtyToSub,
																 'cost_price' => $cost_price,
																 'sale_price' => $price,
																 'created' => $currentTime,
																 'modified' => $currentTime,
																 'site_id' => $site_id_to_save,
																 'kiosk_id' => $kiosk_id,
																 );
										$hp_conn->insert('reserved_products',
																		  $reservedProductData
																	   , ['created' => 'datetime']);
									}
								}
							//}
						}
						//$this->Product->setDataSource('default');
						//$this->ReservedProducts->setDataSource('default');
						//$this->TransferUnderstock->setDataSource('default');
						
					}else{
						pr($StockTransferEntity->errors());die("For Developer Purpose");
					}
				}
				$this->Flash->success("Order placed/dispatched for $productCounts items for Order ID: $kiosk_order_id");
				return $this->redirect(array('controller' => 'kiosk_orders','action' => "on_demand_placed_orders"));
			}else{
				$this->Flash->error("Failed to create order!");
				return $this->redirect(array('action'=>'placed-order-on-demand',$id));
			}
		}else{
			$forprint = "Yes";
			if(array_key_exists("1",$this->request->params["pass"])){
				$forprint =  $this->request->params["pass"]["1"];
				if($forprint == "yes"){
					$forprint = "Yes";
				}
			}
			if(array_key_exists("forprint",$this->request->data)){
				$forprint = $this->request->data["forprint"];
				if($forprint == "yes"){
					$forprint = "Yes";
				}
			}
			//without post block
			$org_product_qty = $productArr = array();
			$remarksArr = array();
			$categoryIdArr = array();
			$kiosk_query = $this->OnDemandOrders->find('all' , array(
									'conditions' => array('id' => $id,),
									'fields' => array('user_id','kiosk_id','created','modified','lock_status'),
									'order' => 'OnDemandOrders.id desc'
								       ));
			$kiosk_query = $kiosk_query->hydrate(false);
			if(!empty($kiosk_query)){
			   $kiosk = $kiosk_query->first();
			}else{
			   $kiosk = array();
			}
			$on_demand_orders_data = $kiosk;
			$kiosk_id = $kiosk['kiosk_id'];
			$created = $kiosk['created'];
			 $modified_on = $kiosk['modified'];
            if(!empty($modified_on)){
                 $modified_on->i18nFormat(
                                                    [\IntlDateFormatter::FULL, \IntlDateFormatter::FULL]
                                            );
				$modified_date =  $modified_on->i18nFormat('dd-MM-yyyy HH:mm:ss');
                $modified_date = date("d-m-y h:i a",strtotime($modified_date)); 
            }else{
                $modified_date = "--";
            }
            
			$modified = $modified_date;
			$placedby = $kiosk['user_id'];
			$users_query = $this->Users->find('list',array(
													 'keyField' => 'id',
													 'valueField' => 'username'
													 ));
		  $users_query = $users_query->hydrate(false);
		  if(!empty($users_query)){
			 $users = $users_query->toArray();  
		  }else{
		     $users = array();
		  }
		  $kiosk_query = $this->Kiosks->findAllById($kiosk_id,array('fields' => array('id','name')));
		  $kiosk_query = $kiosk_query->hydrate(false);
		  if(!empty($kiosk_query)){
			   $kiosk = $kiosk_query->toArray();
		  }else{
			   $kiosk = array();
		  }
		  
			$kioskOrderProducts_query = $this->OnDemandProducts->find('all',array(
								   'conditions' => array('kiosk_placed_order_id' => $id),
								   'fields' => array('id','product_id','quantity','remarks','status','org_qty'),
								   )
						       );
			$kioskOrderProducts_query = $kioskOrderProducts_query->hydrate(false);
			if(!empty($kioskOrderProducts_query)){
			   $kioskOrderProducts = $kioskOrderProducts_query->toArray();
			}else{
			   $kioskOrderProducts = array();
			}
			$kioskCancelOrderProducts_query = $this->KioskCancelledOrderProducts->find('all',array(
								   'conditions' => array('kiosk_placed_order_id' => $id,
														 'is_on_demand' => 1,
														 )
								   )
						       );
			$kioskCancelOrderProducts_query = $kioskCancelOrderProducts_query->hydrate(false);
			if(!empty($kioskCancelOrderProducts_query)){
			   $kioskCancelOrderProducts  = $kioskCancelOrderProducts_query->toArray();
			}else{
			   $kioskCancelOrderProducts = array();
			}
			$canSrNoArr = $cancelProducts = array();
			if(!empty($kioskCancelOrderProducts)){
				foreach($kioskCancelOrderProducts as $kioskCancelOrderProduct){
					$productCancelArr[$kioskCancelOrderProduct['product_id']] = $kioskCancelOrderProduct['quantity'];
					$cnaceldiferenceArr[$kioskCancelOrderProduct['product_id']] = $kioskCancelOrderProduct['difference'];
					$cancelRemarksArr[$kioskCancelOrderProduct['product_id']] = $kioskCancelOrderProduct['remarks'];
					$canProductArr[$kioskCancelOrderProduct['product_id']] = $kioskCancelOrderProduct['quantity'];
					$canSrNoArr[$kioskCancelOrderProduct['product_id']] = $kioskCancelOrderProduct['sr_no'];
				}
				if(!empty($productCancelArr)){
					$productCancelIds = array_keys($productCancelArr);
					$cancelProducts_query = $this->Products->find('all', array(
							   'conditions' => array('Products.id IN' => $productCancelIds),
							   'order' => 'Products.category_id asc'
							  ));
					$cancelProducts_query = $cancelProducts_query->hydrate(false);
					if(!empty($cancelProducts_query)){
						 $cancelProducts = $cancelProducts_query->toArray();
					}else{
						 $cancelProducts = array();
					}
				}
				foreach($cancelProducts as $key1 => $product_data1){
					$cancelcategoryIdArr[] = $product_data1['category_id'];
				}
				$cancelCategoryList_query = $this->Categories->find('list', array('conditions' => array('Categories.id IN' => $cancelcategoryIdArr),
																			'keyField' => 'id',
																			'valueField' => 'category'
																			));
				$cancelCategoryList_query = $cancelCategoryList_query->hydrate(false);
				if(!empty($cancelCategoryList_query)){
					$cancelCategoryList =  $cancelCategoryList_query->toArray();
				}else{
					$cancelCategoryList = array();
				}
				//pr($cancelCategoryList);die;
			}
		
			//pr($kioskCancelOrderProducts);die;
			$kiosk_product_order_ids = array();
			foreach($kioskOrderProducts as $kioskOrderProduct){
			   $org_product_qty[$kioskOrderProduct['product_id']] = $kioskOrderProduct['org_qty'];
				$productArr[$kioskOrderProduct['product_id']] = $kioskOrderProduct['quantity'];
				$remarksArr[$kioskOrderProduct['product_id']] = $kioskOrderProduct['remarks'];
				$statusArr[$kioskOrderProduct['product_id']] = $kioskOrderProduct['status'];
				$kiosk_product_order_ids[$kioskOrderProduct['product_id']] = $kioskOrderProduct['id'];
			}
			//pr($statusArr); die;
			$productIds = array_keys($productArr);
			
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
				//$this->Product->setDataSource('ADMIN_DOMAIN_db');	
			}
			if(empty($productIds)){
			   $productIds = array(0 => null);
			}
			$products_query = $this->Products->find('all', array(
							   'conditions' => array('Products.id IN' => $productIds),
							   'order' => 'Products.category_id asc'
							  ));
			$products_query = $products_query->hydrate(false);
			if(!empty($products_query)){
			   $products = $products_query->toArray();
			}else{
			   $products = array();
			}
			if($isboloRam != false){
				//$this->Product->setDataSource('default');	
			}
			if($isboloRam != false){
				//$bolram_products = $this->Product->find('list',array('conditios' => array('Product.id' => $productIds),'fields' => array('id','selling_price')));
				//$this->set(compact('bolram_products'));
			}
			//pr($products);die;
			foreach($products as $key => $product_data){
				$categoryIdArr[] = $product_data['category_id'];
			}
			if(empty($categoryIdArr)){
			   $categoryIdArr = array(0 => null);
			}
			$categoryList_query = $this->Categories->find('list', array('conditions' => array('Categories.id IN' => $categoryIdArr),
																  'keyField' => 'id',
																  'valueField' =>'category', 
																  ));
			$categoryList_query = $categoryList_query->hydrate(false);
			if(!empty($categoryList_query)){
			   $categoryList = $categoryList_query->toArray();
			}else{
			   $categoryList = array();
			}
			
			//$product_new_arr = array();
			//if(!empty($products)){
			//   foreach($products as $p_key => $p_val){
			//		$product_new_arr[$kiosk_product_order_ids[$p_val['id']]] = $p_val;
			//   }
			//}
			//ksort($product_new_arr);
			if($on_demand_orders_data['lock_status'] == 1){
			   $counter = 0;
			   foreach($products as $k => $v){
				  $counter++;
				  $p_id = $v['id'];
				  $get_query = "SELECT * FROM  `on_demand_products`  WHERE `kiosk_placed_order_id` = $id AND `product_id` = $p_id";
				  $conn = ConnectionManager::get('default');
				  $stmt = $conn->execute($get_query);
				  $res = $stmt ->fetchAll('assoc');  
				  if(empty($res[0]['sr_no'])){
					   $update_query = "UPDATE `on_demand_products` SET `sr_no` = $counter WHERE `kiosk_placed_order_id` = $id AND `product_id` = $p_id";
					   $conn = ConnectionManager::get('default');
					   $stmt = $conn->execute($update_query); 	
				  } 
			   }
			}
			$kioskOrderProducts_query = $this->OnDemandProducts->find('all',array(
								   'conditions' => array('kiosk_placed_order_id' => $id),
								    'fields' => array('id','product_id','quantity','difference','remarks','status','org_qty','sr_no'),
								   )
						       );
            $kioskOrderProducts_query->hydrate(false);
            if(!empty($kioskOrderProducts_query)){
                $kioskOrderProducts  = $kioskOrderProducts_query->toArray();
            }else{
                $kioskOrderProducts = array();
            }
			
			foreach($kioskOrderProducts as $kioskOrderProduct){
				$product_sr_no[$kioskOrderProduct['product_id']] = $kioskOrderProduct['sr_no'];
			}
			
			if($on_demand_orders_data['lock_status'] == 1){
			   $product_new_arr = array();
			   if(!empty($products)){
				  foreach($products as $p_key => $p_val){
					   $product_new_arr[$product_sr_no[$p_val['id']]] = $p_val;
				  }
			   }
			   ksort($product_new_arr);   
			}else{
			   $product_new_arr = $products;
			}
			
			
			$this->set(compact('cnaceldiferenceArr','canProductArr','cancelRemarksArr','kioskCancelOrderProducts','cancelCategoryList','cancelProducts','statusArr','products','remarksArr','productArr','diferenceArr','created','users','placedby','modified','categoryList','forprint','org_product_qty','on_demand_orders_data','product_new_arr','kiosk_product_order_ids','product_sr_no','canSrNoArr'));
			$this->set('kiosk', $kiosk[0]);
		}
	}
	
	private function cancel_items_ondemand($kiosk_id, $place_order_id, $cancelled_products){
		//echo $place_order_id;pr($cancelled_products);
		//kiosk_id, kiosk_placed_order_id, product_id,quantity, difference, remarks,
		$kioskOrderProducts_query = $this->OnDemandProducts->find('all',array(
								   'conditions' => array(
														 'kiosk_placed_order_id' => $place_order_id,
														 'product_id IN' => $cancelled_products,
														 )
								   )
						       );
		$kioskOrderProducts_query = $kioskOrderProducts_query->hydrate(false);
		if(!empty($kioskOrderProducts_query)){
		  $kioskOrderProducts = $kioskOrderProducts_query->toArray();
		}else{
		  $kioskOrderProducts = array();
		}
		$idForDeletion = array();
		$cancelledOrderIDs = array();
        $code_query = $this->Products->find('list',array('keyField' => 'id',
                                           'valueField' => 'product_code'
                                           ));
        $code_query = $code_query->hydrate(false);
        if(!empty($code_query)){
            $code = $code_query->toArray();
        }else{
            $code = array();
        }
        //pr($kioskOrderProducts);die;
        $product_code_for_deletion = array();
		foreach($kioskOrderProducts as $kioskOrderProduct){
			$dataArr = $kioskOrderProduct;
			$dataArr['remarks'] = "On Demand Cancelled";
			$dataArr['cancelled_by'] = $this->request->session()->read('Auth.User.id');
			$dataArr['is_on_demand'] = 1;
			//$KioskCancelledOrderProductsEntity = $this->KioskCancelledOrderProducts->newEntity();
			$idForDeletion[] = $dataArr['id'];
            $product_code_for_deletion[] = $code[$dataArr['product_id']];
			unset($dataArr['id']);
			unset($dataArr['created']);
			unset($dataArr['modified']);
			//pr($dataArr);
			$KioskCancelledOrderProductsEntity = $this->KioskCancelledOrderProducts->newEntity($dataArr,['validate' => false]);
			$KioskCancelledOrderProductsEntity = $this->KioskCancelledOrderProducts->patchEntity($KioskCancelledOrderProductsEntity,$dataArr,['validate' => false]);
			if($this->KioskCancelledOrderProducts->save($KioskCancelledOrderProductsEntity,['validate' => false])){
			   
			}else{
			   pr($KioskCancelledOrderProductsEntity->errors());die;
			}
			$cancelledOrderIDs[] = $KioskCancelledOrderProductsEntity->id;
		}
		//deleted all cancelled ids from KioskOrderProduct
		$idStr = "'".implode("','",$idForDeletion)."'";
		$delQry = "DELETE FROM `on_demand_products` WHERE `id` IN($idStr)";
		$conn = ConnectionManager::get('default');
		  $stmt = $conn->execute($delQry); 
		//$errorStr = implode("<br/>",$error);
        
        $idStr_to_show = "'".implode("','",$product_code_for_deletion)."'";
		$this->Flash->success("Order cancelled for these product codes: $idStr_to_show for order id:$place_order_id");
		$domain_name =  $_SERVER['HTTP_HOST'];
        header("Location:http://$domain_name/stock-transfer/placed-order-on-demand/$place_order_id");
		//return $this->redirect(array('action'=>'placed_order_on_demand',$place_order_id));
		die;
	}
	public function onDemandForPrint($id = ""){
		 echo $id;
	 // pr($this->request);die;
		if(array_key_exists("forprint",$this->request->data)){
			echo $forprint = $this->request->data["forprint"]; 
		}
		return $this->redirect(array('action'=>"placed_order_on_demand/{$id}/{$forprint}"));
	}
    public function forPrint($id = ""){
		//echo $id;
	//	 pr($this->request);die;
		if(array_key_exists("forprint",$this->request->data)){
			$forprint = $this->request->data["forprint"];
		}
		return $this->redirect(array('action'=>"placed_order/{$id}/{$forprint}"));
	}
	
	
	//public function onDemand_forPrint($id = ""){
	//	//echo $id;
	//	//pr($this->request);die;
	//	if(array_key_exists("forprint",$this->request->data)){
	//		$forprint = $this->request->data["forprint"];
	//	}
	//	return $this->redirect(array('action'=>"placed_order_on_demand/{$id}/{$forprint}"));
	//}
	
	public function kioskStock(){
		$kiosks_query = $this->Kiosks->find('list',array(
							    'keyField' => 'id',
								'valueField' => 'name',
								//'fields' => array('id', 'name'),
                                                                'conditions' => array('Kiosks.status' => 1),
								'order' => 'Kiosks.name asc'
							)
					     );
		$kiosks_query = $kiosks_query->hydrate(false);
		if(!empty($kiosks_query)){
		  $kiosks = $kiosks_query->toArray();
		}else{
		  $kiosks  = array();
		}
		$this->set(compact('kiosks'));
	}
	
	public function productPerKiosk($product_id=""){
		$kiosks_query = $this->Kiosks->find('list',array(
							    'keyField' => 'id',
								'valueField' => 'name',
								//'fields' => array('id', 'name'),
                                                                'conditions' => array('Kiosks.status' => 1),
								'order' => 'Kiosks.id desc'
							)
					     );
		$kiosks_query = $kiosks_query->hydrate(false);
		if(!empty($kiosks_query)){
		  $kiosks = $kiosks_query->toArray();
		}else{
		  $kiosks = array();
		}
		$kiosks[0]="Warehouse";
		$productArr = array();
		foreach($kiosks as $kiosk_id=>$kioskName){
			if($kiosk_id==0){
			   $productTable = TableRegistry::get("products",[
                                                                                    'table' => "products",
                                                                                ]);
			}else{
			   $tableSource = "kiosk_{$kiosk_id}_products";
			   $productTable = TableRegistry::get($tableSource,[
                                                                                    'table' => $tableSource,
                                                                                ]);
			}
			
			
			$productArr_query = $productTable->find('all',array('conditions'=>array('id'=>$product_id),'fields'=>array('id','quantity','product_code','image','product')));
			$productArr_query = $productArr_query->hydrate(false);
			if(!empty($productArr_query)){
			   $productArr[$kioskName] = $productArr_query->first();
			}else{
			   $productArr[$kioskName] = array();
			}
		}
		$this->set(compact('productArr'));
	}
    
    public function export(){
	 $path = realpath(dirname(__FILE__));
	 if (strpos($path,ADMIN_DOMAIN) !== false) {
		$adminSite = true;  
	 }else{
		  $adminSite = false;  
	 }
	 if(array_key_exists('kiosk',$this->request->query)){
		$selectedKiosk = $this->request->query['kiosk'];  
	 }else{
		  $selectedKiosk = "";
	 }
		
		
		$req_type = 'dynamic';
		if(array_key_exists('req_type',$this->request->query)){
			$req_type = $this->request->query['req_type'];
		}
		
		
		$from_date = '';
		$startDate = '';
		if(!empty($this->request->query['from_date'])){
			$from_date = $this->request->query['from_date'];
			$startDate = date("Y-m-d",strtotime($from_date.'-1 day'));
		}
		
		$to_date = '';
		$endDate = '';
		if(!empty($this->request->query['to_date'])){
			$to_date = $this->request->query['to_date'];
			$endDate = date("Y-m-d",strtotime($to_date.'+1 day'));
		}
		
        $KioskOrder_source = "kiosk_orders";
        $KioskOrderTable = TableRegistry::get($KioskOrder_source,[
                                                                    'table' => $KioskOrder_source,
                                                                ]);
		
		$transferredByWarehouse = array();
        $StockTransfer_source = "stock_transfer";
        $StockTransferTable = TableRegistry::get($StockTransfer_source,[
                                                                    'table' => $StockTransfer_source,
                                                                ]);
		
		//code modified on Aug 6, 2016
		$condArr = array();
		if( !empty($startDate) && !empty($endDate)){
			$condArr = array("DATE(created) > '$startDate'", "DATE(created) < '$endDate'");
		}

		if(!empty($this->request->query['data']['kiosk'])){
			//$condArr = array("StockTransfer.kiosk_id" => (int)$this->request->query['data']['kiosk']);
		}
		$trnsfrd2KioskOrderIds_query = $StockTransferTable->find('list',[
                                                                    'conditions' => $condArr,
                                                                    'keyField' => 'id',
                                                                    'valueField' => 'kiosk_order_id'
                                                                  ]
															);
        $trnsfrd2KioskOrderIds_query = $trnsfrd2KioskOrderIds_query->hydrate(false);
        if(!empty($trnsfrd2KioskOrderIds_query)){
            $trnsfrd2KioskOrderIds = $trnsfrd2KioskOrderIds_query->toArray();
        }else{
            $trnsfrd2KioskOrderIds = array();
        }
        if(empty($trnsfrd2KioskOrderIds)){
            $trnsfrd2KioskOrderIds = array(0 => null);
        }
		if($selectedKiosk == ""){
		  $kiosks_list = $this->Kiosks->find("list",[
												  'conditions' => [
													'status' => 1,   
												  ],
												  'keyField' => "id",
												  'valueField' => "id",
											   ])->toArray();
		  $checkIfBelongToKiosk_query = $KioskOrderTable->find('list',[
                                                                'keyField' => 'id',
                                                                'valueField' => 'kiosk_id',
                                                                'conditions' => [
																					'id IN' => $trnsfrd2KioskOrderIds,
																					'kiosk_id IN' => $kiosks_list,
                                                                                ]
															  ]
														);
		}else{
			   $checkIfBelongToKiosk_query = $KioskOrderTable->find('list',[
                                                                'keyField' => 'id',
                                                                'valueField' => 'kiosk_id',
                                                                'conditions' => [
																					'kiosk_id' => $selectedKiosk,
																					'id IN' => $trnsfrd2KioskOrderIds
                                                                                ]
															  ]
														);
		}
		
        $checkIfBelongToKiosk_query = $checkIfBelongToKiosk_query->hydrate(false);
        if(!empty($checkIfBelongToKiosk_query)){
            $checkIfBelongToKiosk = $checkIfBelongToKiosk_query->toArray();
        }else{
            $checkIfBelongToKiosk = array();
        }
        if(empty($checkIfBelongToKiosk)){
            $checkIfBelongToKiosk = array(0 => null);
        }
		
		$transferredByWarehouse_query = $StockTransferTable->find('all',array(
											   'conditions' => array('kiosk_order_id IN' => array_keys($checkIfBelongToKiosk)),
											   'fields' => array('product_id',
																				  'sale_price',
																				  'cost_price',
																				  'kiosk_order_id',
																				  'quantity',
																				  'kiosk_id',
																				  'created'
																				  ),
											   'order' => 'kiosk_id desc'
											   ));
		//pr($transferredByWarehouse_query);die;
		$transferredByWarehouse_query = $transferredByWarehouse_query->hydrate(false);
        if(!empty($transferredByWarehouse_query)){
            $transferredByWarehouse = $transferredByWarehouse_query->toArray();
        }else{
            $transferredByWarehouse = array();
        }
		//pr($transferredByWarehouse);die;
		$productIds = array();
		if(!empty($transferredByWarehouse)){
			foreach($transferredByWarehouse as $key => $byWarehouse){
				$prodID = $byWarehouse['product_id'];
				$productIds[$prodID] = $prodID;
			}
		}
		
		$productArr = $productDetail = array();
		if(!empty($productIds)){
            if(empty($productIds)){
                $productIds = array(0 => null);
            }
			$productDetail_query = $this->Products->find('all',array(
														'conditions' => array('Products.id IN' => $productIds),
														'fields' => array('id','product_code','product','cost_price')
														)
												  );
            $productDetail_query = $productDetail_query->hydrate(false);
            if(!empty($productDetail_query)){
                $productDetail = $productDetail_query->toArray();
            }else{
                $productDetail = array();
            }
			//pr($productDetail);die;
			if(!empty($productDetail)){
				foreach($productDetail as $key => $productInfo){
					$productArr[$productInfo['id']] = $productInfo;
					//storing product titles in product id array
				}
			}
		}
		
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
		//pr($transferredByWarehouse);die;
		$product_cats = $this->Products->find("list",[
											   'keyField' => "id",
											   'valueField' => "category_id",
											   ])->toArray();
		  $cat_name = $this->Categories->find("list",[
													  'keyField' => 'id',
													  'valueField' => 'category',
													  ])->toArray();
		
		
		$whCostPrice = $sumCostPrice = $totalCostPrice = 0;
		$whProductCode = $whProduct = '--';
		$csvArr = array();
		
		$main_site_db = Configure::read('MAIN_SITE_DB');
		$conn = ConnectionManager::get($main_site_db);
		$query = "SELECT `id`,`cost_price` FROM `products`";
	    $stmt = $conn->execute($query);
	    $query_res = $stmt->fetchAll('assoc');
		$hp_cost_arr = array();
		if(!empty($query_res)){
		  foreach($query_res as $k => $v){
			   $hp_cost_arr[$v["id"]] = $v["cost_price"];
		  }
		}
		$startDate = "'".date("Y-m-d",strtotime($startDate))."'";
		$endDate = "'".date("Y-m-d",strtotime($endDate))."'";
		$daily_stock_query = "SELECT `product_id`,`cost_price`,`created` FROM `daily_stocks` WHERE  DATE(`created`) > $startDate AND DATE(`created`) < $endDate";
	    $stmt = $conn->execute($daily_stock_query);
	    $daily_stock_query_res = $stmt->fetchAll('assoc');
		$created_product_cst_arr = array();
		if(!empty($daily_stock_query_res)){
		  foreach($daily_stock_query_res as $daily_key => $daily_val){
			   $created_product_cst_arr[date("Y-m-d",strtotime($daily_val['created']))][$daily_val['product_id']] = $daily_val['cost_price'];
		  }
		}
		
		
		$conn = ConnectionManager::get("default");
		foreach($transferredByWarehouse as $key => $warehouseData){
			$prodQty = $warehouseData['quantity'];
			if($prodQty > 0){
			   $product_cat_name = $cat_name[$product_cats[$warehouseData['product_id']]];
				if(array_key_exists($warehouseData['product_id'],$productArr)){
					$whProductCode = $productArr[$warehouseData['product_id']]['product_code'];
					$whProduct = $productArr[$warehouseData['product_id']]['product'];
					if($req_type == 'fixed'){
						$whCostPrice = $warehouseData['cost_price'];
						$sumCostPrice = $prodQty * $warehouseData['cost_price'];
					}else{
						$productID = $warehouseData['product_id'];
						$whCostPrice = $productArr[$productID]['cost_price'];
						$sumCostPrice = $prodQty * $whCostPrice;
					}
					$totalCostPrice += $sumCostPrice;
				}
				$csvArr[$key]['Product_id'] = $warehouseData['product_id'];
				$csvArr[$key]['Product Code'] = $whProductCode;
				$csvArr[$key]['Product'] = $whProduct;
				$csvArr[$key]['Category'] =$product_cat_name;
				$csvArr[$key]['Cost Price'] = $whCostPrice;
				if(!$adminSite){
					
					$loggedInUser =  $this->request->session()->read('Auth.User.username');
					if (!preg_match('/'.QUOT_USER_PREFIX.'/',$loggedInUser)){
						
					}else{
						 $created =date("Y-m-d",strtotime($warehouseData['created']));
						 $p_id = $warehouseData['product_id'];
						 
						 $cost_price_temp = "";
						 if(array_key_exists($created,$created_product_cst_arr)){
							  if(array_key_exists($p_id,$created_product_cst_arr[$created])){
								   $cost_price_temp = $created_product_cst_arr[$created][$p_id];
							  }
						 }
						 
						// $res = $this->daily_stocks_hp->find("all",[
						//											'conditions' => [
						//											  'product_id' => $p_id,
						//											  'DATE(created)' => $created,
						//											]
						//											])->toArray();
						
						 //$main_site_db = Configure::read('MAIN_SITE_DB');
						 //
						 //$query = "SELECT `cost_price` FROM `daily_stocks_hp` WHERE `product_id`=$p_id AND DATE(`created`) = $created";
						 //$stmt = $conn->execute($query);
						 //$query_res = $stmt->fetchAll('assoc');
						 if(!empty($cost_price_temp)){
							   $cost_price = $cost_price_temp;
						 }else{
							  $cost_price = $hp_cost_arr[$p_id];
							  
						 }
						 $csvArr[$key]['Wharehouse Cost Price'] = $cost_price;
					}
					
				}
				$csvArr[$key]['Quantity'] = $prodQty;
				$csvArr[$key]['Amount'] = $sumCostPrice;
				if($selectedKiosk == ""){
					if(array_key_exists($warehouseData['kiosk_id'],$kiosks)){
						$csvArr[$key]['kiosk'] = $kiosks[$warehouseData['kiosk_id']];
					}else{
						 if(array_key_exists($warehouseData['kiosk_order_id'],$checkIfBelongToKiosk)){
							  $csvArr[$key]['kiosk'] = $kiosks[$checkIfBelongToKiosk[$warehouseData['kiosk_order_id']]];
						 }else{
							 $csvArr[$key]['kiosk'] = "--"; 
						 }
					}
				}
				
				$csvArr[$key]['Transfer Date'] =  date("d/m/Y h:m:i",strtotime($warehouseData['created']));
			}
		}
		
		//pr($csvArr);die;
		if($selectedKiosk != ""){
		  $file_name = $kiosks[$selectedKiosk].time();
		}else{
		  $file_name = "All_sale".time();
		}
		
		$this->outputCsv($file_name.".csv" ,$csvArr);
		$this->autoRender = false;
	 }
	 
	 public function export1(){
		$from_date = '';
		$startDate = '';
		if(!empty($this->request->query['start_date'])){
			$from_date = $this->request->query['start_date'];
			$startDate = date("Y-m-d",strtotime($from_date.'-1 day'));
		}
		
		$to_date = '';
		$endDate = '';
		if(!empty($this->request->query['end_date'])){
			$to_date = $this->request->query['end_date'];
			$endDate = date("Y-m-d",strtotime($to_date.'+1 day'));
		}
		
		$search_kw = '';
		if(!empty($this->request->query['search_kw'])){
			
			$search_kw = $this->request->query['search_kw'];
		}
		
		$conditionArr = array();
		
		if(!empty($startDate) || !empty($endDate)){
			$conditionArr[] = array("DATE(ReservedProducts.created)>'$startDate'","DATE(ReservedProducts.created)<'$endDate'");
		}
		$selectedId = array();
		$cat_serach = 0;
		if(array_key_exists('category',$this->request->query)&& !empty($this->request->query['category'][0])){
		  $cat_serach = 1;
			//$notAllow = 1;
			$conditionArr1['category_id IN'] = $this->request->query['category'];
			$productIdData_query = $this->Products->find('all',array('conditions'=>$conditionArr1,'fields'=>array('id'),'recursive'=>-1,'order'=>'Products.id DESC'));
			$productIdData_query = $productIdData_query->hydrate(false);
			$productIdData = $productIdData_query->toArray();
			$ids = array();
            $selectedId = $conditionArr1['category_id IN'];
			foreach($productIdData as $a => $val){
				$ids[] = $val['id'];
			}
			if(empty($ids)){
			   $ids = array(0 => null);
			}
			$conditionArr['product_id IN'] = $ids;
			
		}

		if(!empty($search_kw)){
			$notAllow = 1;
			//$searched = 2;
			$productSearch_query = $this->Products->find('all',array('conditions'=>array(
								'OR'	=> array(
								'LOWER(Products.product_code) like' => "%$search_kw%",
								'LOWER(Products.product) like' => "%$search_kw%",
									)
								),
								'fields' => array('id'),
							)
					     );
			$productSearch_query = $productSearch_query->hydrate(false);
			$productSearch = $productSearch_query->toArray();
			$requestedProductIds = array();
			foreach($productSearch as $key=>$productResult){
				$requestedProductIds[] = $productResult['id'];
			}
			
			if(!empty($requestedProductIds)){
				$conditionArr[] = array("ReservedProducts.product_id IN"=>$requestedProductIds);
			}
		}
		
		$dispatchedProducts = $this->ReservedProducts->find('all',['conditions' => $conditionArr])->toArray();
		
		
		//pr($dispatchedProducts);die;
		//die;
		$alreadyDone = $productIdArr = array();
		$invoiceBillAmt = 0;
		
		foreach($dispatchedProducts as $key => $dispatchedProduct){
			$productIdArr[] = $dispatchedProduct['product_id'];
		}
		if(empty($productIdArr)){$productIdArr = array(0=>null); }
		$productData_query = $this->Products->find('all', array(
														 'conditions' => array('Products.id IN' => $productIdArr),
														 'fields' => array('id','product_code','product','image','category_id','cost_price','selling_price'),
														 'order' => 'Products.id DESC')
											);
		
		$productData_query = $productData_query->hydrate(false);
		$productData = $productData_query->toArray();
		$productIdDetail = array();
		foreach($productData as $productInfo){
			$productIdDetail[$productInfo['id']] = $productInfo;
		}
		$alreadyDone = $checkQty = array();
		  foreach($dispatchedProducts as $dispatchedProduct){
			   if(array_key_exists($dispatchedProduct['product_id'],$productIdDetail)){
				   if($dispatchedProduct->status == 0){
					   if(array_key_exists($dispatchedProduct['product_id'],$checkQty)){
						   $checkQty[$dispatchedProduct['product_id']] = $checkQty[$dispatchedProduct['product_id']]+$dispatchedProduct['quantity'];
					   }else{
							   $checkQty[$dispatchedProduct['product_id']] = $dispatchedProduct['quantity'];		
					   }
				   }
			   }
		  }
		  $categoryData = $this->Categories->find('all',array('fields'=>array('id','category'),'order'=>'Categories.category ASC'))->toArray();
		  $categoryArr = array();
		foreach($categoryData as $k => $value){
			$categoryArr[$value['id']] =  $value['category'];
		}
		  
		  foreach ($dispatchedProducts as $key => $dispatchedProduct){
			   if($dispatchedProduct['status'] == 1){
					continue;
			   }
			   if(in_array($dispatchedProduct['product_id'],$alreadyDone)){
					continue;
			   }
			   if(array_key_exists($dispatchedProduct->product_id,$checkQty)){
					$totalquantity = $checkQty[$dispatchedProduct['product_id']];
					$alreadyDone[] = $dispatchedProduct['product_id'];
				}
				if(array_key_exists($dispatchedProduct->product_id,$productIdDetail)){
					$id = $dispatchedProduct['id'];
					$productCode = $productIdDetail[$dispatchedProduct['product_id']]['product_code'];
					$productName = $productIdDetail[$dispatchedProduct['product_id']]['product'];
					$category_id = $productIdDetail[$dispatchedProduct['product_id']]['category_id'];
					$costPrice = $productIdDetail[$dispatchedProduct['product_id']]['cost_price'];
					$salePrice = $productIdDetail[$dispatchedProduct['product_id']]['selling_price'];
				}
				$dispatchDate = $dispatchedProduct['created'];
				$category = $categoryArr[$category_id];
				$final_arr[] = array(
									 'Product_Code' => $productCode,
									 'Dispatch_Date' => date("jS M, Y h:i A",strtotime($dispatchDate)),
									 'Category' => $category,
									 'Product' => $productName,
									 'Cost_Price' => $costPrice,
									 'sale_price' => $salePrice,
									 'quantity' => $totalquantity
									 );
		  }
		//pr($csvArr);die;
		$this->outputCsv('Sale_'.time().".csv" ,$final_arr);
		$this->autoRender = false;
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
    
    public function  test(){
        $query = "SET @cat_counts=0; ";//call count_prod_in_cat_temp('CAR USB',@cat_counts,@min_price, @max_price);
        $conn = ConnectionManager::get('default');
        $stmt = $conn->execute($query);
        
        $query1 = "SET @min_price=0; ";//call count_prod_in_cat_temp('CAR USB',@cat_counts,@min_price, @max_price);
        $stmt = $conn->execute($query1);
        
        $query2 = "SET @max_price=0; ";//call count_prod_in_cat_temp('CAR USB',@cat_counts,@min_price, @max_price);
        $stmt = $conn->execute($query2);
        
        $query4 = "call count_prod_in_cat_temp('CAR USB',@cat_counts,@min_price, @max_price)";//;
        $stmt = $conn->execute($query4);
        
        $query5 = "SELECT @min_price;";//;
        $stmt1 = $conn->execute($query5);
        
        
        
        //$Query2 = "call count_prod_in_cat_temp('CAR USB',0,0,0)";
        //$conn = ConnectionManager::get('default');
        //$stmt = $conn->execute($query);
        $costPriceQuery = $stmt ->fetchAll('assoc');
        pr($costPriceQuery);
        
        $costPriceQuery1 = $stmt1 ->fetchAll('assoc');
        pr($costPriceQuery1);die;
    }
	
	public function getCatPrice(){
	 $start_date = $end_date = "";
		  if(array_key_exists('start_date',$_REQUEST) && array_key_exists('end_date',$_REQUEST)){
			   $start_date = $_REQUEST['start_date'];
			   $end_date =$_REQUEST['end_date'];
			   $site_id = $_REQUEST['site_id'];
			   $kiosk_id = $_REQUEST['kiosk_id'];
			   if(!empty($kiosk_id)){
					if($site_id == 0){
						$res_query = $this->ReservedProducts->find('all',['conditions' => ['created >' => date("Y-m-d",strtotime($start_date)),
																		  'created <' => date('Y-m-d', strtotime($end_date.' +1 day')),
																		  'kiosk_id' => $kiosk_id,
																		  'status' => 0
																		  ]]); 
					}else{
						 $res_query = $this->ReservedProducts->find('all',['conditions' => ['created >' => date("Y-m-d",strtotime($start_date)),
																		  'created <' => date('Y-m-d', strtotime($end_date.' +1 day')),
																		  'site_id' => $site_id,
																		  'kiosk_id' => $kiosk_id,
																		  'status' => 0
																		  ]]);
					}
					
			   }else{
					if($site_id == 0){
						 $res_query = $this->ReservedProducts->find('all',['conditions' => ['created >' => date("Y-m-d",strtotime($start_date)),
																		  'created <' => date('Y-m-d', strtotime($end_date.' +1 day')),
																		  'status' => 0
																		  ]]);
					}else{
						$res_query = $this->ReservedProducts->find('all',['conditions' => ['created >' => date("Y-m-d",strtotime($start_date)),
																		  'created <' => date('Y-m-d', strtotime($end_date.' +1 day')),
																		  'site_id' => $site_id,
																		  'status' => 0
																		  ]]); 
					}
					
			   }
					
					//pr($res_query);die;
					$res_query = $res_query->hydrate(false);
					if(!empty($res_query)){
						 $res = $res_query->toArray();
					}else{
						 $res = array();
					}
					
					$filter_array = array();
					foreach($res as $key => $value){
							  $filter_array[$value['category_id']][] = $value;	  
					}
					$products_cost_price = $this->Products->find('list',array('keyField' => "id",'valueField' => 'cost_price'))->toArray();
					$products_selling_price = $this->Products->find('list',array('keyField' => "id",'valueField' => 'selling_price'))->toArray();
					$final_data = array();
					$catData = $this->Categories->find('list',array(
																		  'keyField' => 'id',
																		  'valueField' => 'category',
																		  'recursive'=>-1,'order'=>'Categories.id DESC'))->toArray();
					$temp_arr = array();
					foreach($filter_array as $cat_id => $all_data){
						 foreach($all_data as $s_key => $S_value){
							  $cost_data = $sum_data = 0;
							  $sum_data = $S_value['quantity'] * $products_selling_price[$S_value['product_id']];
							  $cost_data = $S_value['quantity'] * $products_cost_price[$S_value['product_id']];
							  if(array_key_exists($catData[$cat_id],$final_data)){
								   $final_data[$catData[$cat_id]]['sale_price'] =  $final_data[$catData[$cat_id]]['sale_price'] + $sum_data;
								   $temp_arr[$catData[$cat_id]] += $sum_data;
							  }else{
								   $final_data[$catData[$cat_id]]['sale_price'] =  $sum_data;
								   $temp_arr[$catData[$cat_id]] = $final_data[$catData[$cat_id]]['sale_price'] + $sum_data;
							  }
							  
							  if(array_key_exists($catData[$cat_id],$final_data)){
								   if(array_key_exists('cost_price',$final_data[$catData[$cat_id]])){
										$final_data[$catData[$cat_id]]['cost_price'] =  $final_data[$catData[$cat_id]]['cost_price'] + $cost_data;	  						
								   }else{
										$final_data[$catData[$cat_id]]['cost_price'] =  $cost_data;
								   }
								   //$final_data[$catData[$cat_id]]['cost_price'] =  $final_data[$catData[$cat_id]]['cost_price'] + $cost_data;	  
							  }else{
								   $final_data[$catData[$cat_id]]['cost_price'] =  $cost_data;	  
							  }
							  if(array_key_exists($catData[$cat_id],$final_data)){
								   if(array_key_exists('products',$final_data[$catData[$cat_id]])){
										if(!in_array($S_value['product_id'],$final_data[$catData[$cat_id]]['products'])){
											 $final_data[$catData[$cat_id]]['products'][$S_value['product_id']] = $S_value['product_id'];;
										}
								   }else{
										$final_data[$catData[$cat_id]]['products'] = array($S_value['product_id'] => $S_value['product_id']);
								   }
							  }else{
								   $final_data[$catData[$cat_id]]['products'] = array($S_value['product_id'] => $S_value['product_id']);
							  }
							  
							  if(array_key_exists($catData[$cat_id],$final_data)){
								   if(array_key_exists('total_qty',$final_data[$catData[$cat_id]])){
										$final_data[$catData[$cat_id]]["total_qty"] = $final_data[$catData[$cat_id]]["total_qty"] + $S_value['quantity'];					   	
								   }else{
										$final_data[$catData[$cat_id]]["total_qty"] = $S_value['quantity'];					   	
								   }
								   
							  }else{
								   $final_data[$catData[$cat_id]]["total_qty"] = $S_value['quantity'];
							  }
							  $final_data[$catData[$cat_id]]['cat_id'] = $cat_id;
							 // pr($final_data);die;
						 }
					}
					arsort($temp_arr);
					
					if(!empty($final_data)){
						 $new_arr = array();
						 foreach($temp_arr as $cat_name => $catvalue){
							  $new_arr[$cat_name] = array('sale_price' => round($final_data[$cat_name]['sale_price'],2),
														   'cost_price' => round($final_data[$cat_name]['cost_price'],2),
														   'product_count' => count($final_data[$cat_name]['products']),
														   'total_qty' => $final_data[$cat_name]['total_qty'],
														   'cat_id' => $final_data[$cat_name]['cat_id'],
														   'site_id' => $site_id,
														   'kiosk_id' => $kiosk_id,
														   );	  
						 }
						// echo "<pre>";print_r($new_arr);die;
						 $submit_array = array('data' => $new_arr);
						 echo json_encode($submit_array);die;
					}else{
						 $error_array  = array("error" => "no data found");
						 echo json_encode($error_array);die;
					}
					
		  }else{
			   $error_array  = array("error" => "Please add start and end date");
						 echo json_encode($error_array);die;
		  }
	}
	
	public function discard(){
		  if(array_key_exists('start_date',$_REQUEST) && array_key_exists('end_date',$_REQUEST) && array_key_exists('cat_val',$_REQUEST)){
			   if(!empty($_REQUEST['start_date']) && !empty($_REQUEST['end_date']) && !empty($_REQUEST['cat_val'])){
					$transferStock_source = "transfer_understock";
					$transferStockTable = TableRegistry::get($transferStock_source,[
															'table' => $transferStock_source,
														]);
					
					
						$start_date = 	$_REQUEST['start_date'];
					    $end_date = $_REQUEST['end_date'];
					    $cat_val = $_REQUEST['cat_val'];
						$site_id = $_REQUEST['site_id'];
						$kiosk_id = $_REQUEST['kiosk_id'];
						$cat_arr = explode(",",$cat_val);
						if(!empty($kiosk_id)){
						 if($site_id == 0){
							$res_query = $this->ReservedProducts->find('all',['conditions' => ['created >' => date("Y-m-d",strtotime($start_date)),
																		  'created <' => date('Y-m-d', strtotime($end_date.' +1 day')),
																		  'category_id IN' => $cat_arr,
																		  
																		  'kiosk_id' => $kiosk_id,
																		  'status' => 0,
																		  ]]);   
						 }else{
							  $res_query = $this->ReservedProducts->find('all',['conditions' => ['created >' => date("Y-m-d",strtotime($start_date)),
																		  'created <' => date('Y-m-d', strtotime($end_date.' +1 day')),
																		  'category_id IN' => $cat_arr,
																		  'site_id' => $site_id,
																		  'kiosk_id' => $kiosk_id,
																		  'status' => 0,
																		  ]]); 
						 }
						   
						}else{
						 if($site_id == 0){
							  $res_query = $this->ReservedProducts->find('all',['conditions' => ['created >' => date("Y-m-d",strtotime($start_date)),
																		  'created <' => date('Y-m-d', strtotime($end_date.' +1 day')),
																		  'category_id IN' => $cat_arr,
																		  'status' => 0,
																		  ]]); 
						 }else{
							  $res_query = $this->ReservedProducts->find('all',['conditions' => ['created >' => date("Y-m-d",strtotime($start_date)),
																		  'created <' => date('Y-m-d', strtotime($end_date.' +1 day')),
																		  'category_id IN' => $cat_arr,
																		  'site_id' => $site_id,
																		  'status' => 0,
																		  ]]); 
						 }
							  
						}
						
						$res_query = $res_query->hydrate(false);
						 if(!empty($res_query)){
							  $res = $res_query->toArray();
						 }else{
							  $res = array();
						 }
						 
						 if(!empty($res)){
							  $this->loadModel('TransferSurplus');
							  $lost_res = $this->TransferSurplus->find('all',['conditions' => [
																				'created >' => date("Y-m-d",strtotime($start_date)),
																			   'created <' => date('Y-m-d', strtotime($end_date.' +1 day')),
																			   'category_id IN' => $cat_arr
																				],
																			  'fields' => ['id','sequencr_number'],
																			  'order'=>'sequencr_number DESC',
																			  ])->toArray();
							  if(empty($lost_res)){
								   $seq_number = 1;
							  }else{
								 $seq_number =  $lost_res[0]->sequencr_number + 1;  
							  }
							  
							  $cost_price_arr = $this->Products->find('list',['keyField' => 'id','valueField' => 'cost_price'])->toArray();
							  $sale_price_arr = $this->Products->find('list',['keyField' => 'id','valueField' => 'selling_price'])->toArray();
							  
							  
							  foreach($res as $key => $value){
								   $id = $value['id'];
								   $product_id = $value['product_id'];
								   $quantity = $value['quantity'];
								   $category_id = $value['category_id'];
								   $vat = $this->VAT;
								   
								   $sellPrice = $sale_price_arr[$product_id];
								   
								   $surplusData = array(
																 
																 
																 'product_id' => $product_id,
																 'quantity' => $quantity,
																 'category_id' => $category_id,
																 'cost_price' => $cost_price_arr[$product_id],
																 'sale_price' => $sellPrice,
																 'sequencr_number' => $seq_number,
																 );
										  
											$TransferSurplus = $this->TransferSurplus->newEntity();
											$TransferSurplus = $this->TransferSurplus->patchEntity($TransferSurplus, $surplusData,['validate' => false]);
								   if($this->TransferSurplus->save($TransferSurplus)){
										$data_to_save = array('status' => 1);
										$getdata = $this->ReservedProducts->get($id);
										$getdata = $this->ReservedProducts->patchEntity($getdata,$data_to_save);
										$this->ReservedProducts->save($getdata);   
								   }else{
										pr($trasferStock->errors());die;
								   }
							  }
							  $success = array("key" => "success");
							  echo json_encode($success);die;
						 }else{
							  $success = array("key" => "No Data Found for This category");
							  echo json_encode($success);die;
						 }
						 
			   }else{
						$success = array("key" => "Start date or End date Or Category is empty");
						 echo json_encode($success);die;
			   }
		  }else{
			   $success = array("key" => "Start date or End date Or Category is empty");
						 echo json_encode($success);die;
		  }
	}
	function updateQty(){
	 
		  if(array_key_exists('transfer_stock',$this->request->data)){
			   $from_date = $this->request->data['transfer_stock']['from_date'];
			   $end_date = $this->request->data['transfer_stock']['to_date'];
			   $cat_id = $this->request->data['transfer_stock']['cat_value'];
			   
			   
			   $site_id = $this->request->data['transfer_stock']['external_site_id_hidden'];
			   $site_kiosk_id = $this->request->data['transfer_stock']['site_id_hidden'];
			   
			   if(array_key_exists('id',$this->request->data['transfer_stock'])){
					foreach($this->request->data['transfer_stock']['id'] as $key => $id){
						 if(!array_key_exists($key,$this->request->data['transfer_stock']['proID'])){
							  continue; 
						  }
							  $qty_selected = $this->request->data['transfer_stock']['selectedQty'][$key];
							  $product_id = $this->request->data['transfer_stock']['proID'][$key];
							  $product_code = $this->request->data['transfer_stock']['productCode'][$key];
							  $origQty = $this->request->data['transfer_stock']['origQty'][$key];
							  if($qty_selected == $origQty){
								   continue;
							  }
							  
							  
							  $reservedProductDataEntity = $this->ReservedProducts->get($id);
							  if(!empty($reservedProductDataEntity)){
								   $prd_id = $reservedProductDataEntity->product_id;
								   
								   if(empty($site_id)){
										$res_res = $this->ReservedProducts->find("all",["conditions" => ["product_id" => $prd_id,
																						 'created >' => date("Y-m-d",strtotime($from_date)),
																						 'created <' => date('Y-m-d', strtotime($end_date.' +1 day')),
																						 
																						 'status' => 0,
																						 ]])->toArray();	
								   }else{
										if($site_kiosk_id == -1){
											 $res_res = $this->ReservedProducts->find("all",["conditions" => ["product_id" => $prd_id,
																						 'created >' => date("Y-m-d",strtotime($from_date)),
																						 'created <' => date('Y-m-d', strtotime($end_date.' +1 day')),
																						 'site_id' => $site_id,
																						 'status' => 0,
																						 ]])->toArray();
										}else{
											 $res_res = $this->ReservedProducts->find("all",["conditions" => ["product_id" => $prd_id,
																						 'created >' => date("Y-m-d",strtotime($from_date)),
																						 'created <' => date('Y-m-d', strtotime($end_date.' +1 day')),
																						 'site_id' => $site_id,
																						 "kiosk_id" => $kiosk_id,
																						 'status' => 0,
																						 ]])->toArray();
										}
								   }
								   
								   
								   if(!empty($res_res)){
										$count = $counter = $qty_to_sub = 0;
										$qty_to_sub = $origQty - $qty_selected;// this is the qty that we need to sub   
										foreach($res_res as $k => $v){
											 $qty_of_other_row = $v->quantity;
											 $other_row_id = $v->id;
											 if($qty_selected == 0){
												  $reservedProductDataEntity_for_other_row = $this->ReservedProducts->get($other_row_id);
												  $data_to_save = array('quantity' => $qty_of_other_row,
														 'status' => 1, 
														 );
												   $reservedProductDataEntity_for_other_row = $this->ReservedProducts->patchEntity($reservedProductDataEntity_for_other_row,$data_to_save,['validate' => false]);
												  $this->ReservedProducts->save($reservedProductDataEntity_for_other_row);  	  
											 }else{
												 if($qty_to_sub <= 0){
												  continue;
												 }
												  //pr($qty_to_sub);
												  //pr($qty_of_other_row);
												  if($qty_to_sub < $qty_of_other_row){
													   
													   $data_to_save = array('quantity' => $qty_of_other_row - $qty_to_sub,
														 //'status' => 1, 
													   );
													//   pr($data_to_save);
													    $reservedProductDataEntity_for_other_row = $this->ReservedProducts->get($other_row_id);
												  
													   $reservedProductDataEntity_for_other_row = $this->ReservedProducts->patchEntity($reservedProductDataEntity_for_other_row,$data_to_save,['validate' => false]);
													  if($this->ReservedProducts->save($reservedProductDataEntity_for_other_row)){
															
													  }else{
													   pr($reservedProductDataEntity_for_other_row->errors());die;
													  }
													  $qty_to_sub = $qty_to_sub-$qty_to_sub;
												  }else{
													   $data_to_save = array('quantity' => $qty_of_other_row,
														 'status' => 1, 
													   );
													//   pr($data_to_save);
													    $reservedProductDataEntity_for_other_row = $this->ReservedProducts->get($other_row_id);
												  
													   $reservedProductDataEntity_for_other_row = $this->ReservedProducts->patchEntity($reservedProductDataEntity_for_other_row,$data_to_save,['validate' => false]);
													  if($this->ReservedProducts->save($reservedProductDataEntity_for_other_row)){
													   
													  }else{
													   pr($reservedProductDataEntity_for_other_row->errors());die;
													  }
													  $qty_to_sub = $qty_to_sub-$qty_of_other_row;
												  }
												 	  
												  
											 }
										}
								   }
							  }
							  if($qty_selected == 0){
								   $data_to_save = array(//'quantity' => $qty_selected,
														// 'status' => 1, 
														 );
							  }else{
								   $data_to_save = array(//'quantity' => $qty_selected
														 );	 
							  }
							  
							  $reservedProductDataEntity = $this->ReservedProducts->patchEntity($reservedProductDataEntity,$data_to_save,['validate' => false]);
							  $cat_arr = $this->Products->find('list',['keyField' => 'id','valueField' => 'category_id'])->toArray();
							  $cost_price_arr = $this->Products->find('list',['keyField' => 'id','valueField' => 'cost_price'])->toArray();
							  $sale_price_arr = $this->Products->find('list',['keyField' => 'id','valueField' => 'selling_price'])->toArray();
							  //die;
							  if($this->ReservedProducts->save($reservedProductDataEntity)){
								   $lost_res = $this->TransferSurplus->find('all',['conditions' => [
																						  'created >' => date("Y-m-d",strtotime(date("y-m-d"))),
																						 'created <' => date('Y-m-d', strtotime(date("y-m-d").' +1 day')),
																						 'sequencr_number ' => 0,
																						 'product_id' =>  $product_id
																						  ],
																						'fields' => ['id','quantity'],
																						'order'=>'sequencr_number DESC',
																						])->first();
								   $quantity = $origQty - $qty_selected;
								   if(empty($lost_res)){
										$surplusData = array(									 
																		   'product_id' => $product_id,
																		   'quantity' => $quantity,
																		   'category_id' => $cat_arr[$product_id],
																		   'cost_price' => $cost_price_arr[$product_id],
																		   'sale_price' => $sale_price_arr[$product_id],
																		   'sequencr_number' => 0,
													   );
										$TransferSurplus = $this->TransferSurplus->newEntity();
										$TransferSurplus = $this->TransferSurplus->patchEntity($TransferSurplus, $surplusData,['validate' => false]);
										 if($this->TransferSurplus->save($TransferSurplus)){
											 
										 }
								   }else{
										$row_id = $lost_res->id;
										$qty = $lost_res->quantity;
										$surplusData = array(									 
																		   'quantity' => $qty + $quantity,
													   );
										$TransferSurplus = $this->TransferSurplus->get($row_id);
										$TransferSurplus = $this->TransferSurplus->patchEntity($TransferSurplus, $surplusData,['validate' => false]);
										 if($this->TransferSurplus->save($TransferSurplus)){
											 
										 }else{
											 pr($reservedProductDataEntity->errors());die;
										 }
								   }
								   
							  }else{
								   pr($reservedProductDataEntity->errors());die;
							  }
						 }
						 if((int)$cat_id == 0){
							  return $this->redirect(array('action' => "search_transferred_stock",'from_date'=>$from_date,'to_date' => $end_date));die;
						 }else{
							   $cat_ids_arr = explode(",",$cat_id);
						 $new_cat_id_str = implode("&category[]=",$cat_ids_arr);
							  return $this->redirect(array('action' => "search_transferred_stock",'from_date'=>$from_date,'to_date' => $end_date,"category[]" =>$new_cat_id_str));die;	
						 }
			   }else{
					if((int)$cat_id == 0){
							  return $this->redirect(array('action' => "search_transferred_stock",'from_date'=>$from_date,'to_date' => $end_date));die;
					}else{
						 $cat_ids_arr = explode(",",$cat_id);
						 $new_cat_id_str = implode("&category[]=",$cat_ids_arr);
						 return $this->redirect(array('action' => "search_transferred_stock",'from_date'=>$from_date,'to_date' => $end_date,"category[]" =>$new_cat_id_str));die;		  
					}
					
			   }
		  }
	}
	
	 function updatedOnDemandOrderQty(){
		 //pr($_REQUEST);die;
		  $kiosk_id = $_REQUEST['kiosk_id'];
		  $quantity = $_REQUEST['quantity'];
		  $kiosk_placed_order_id = $_REQUEST['kiosk_placed_order_id'];
		  $product_id = $_REQUEST['product_id'];
		  $check_for_lock = $this->OnDemandOrders->find("all",['conditions' => [
																  'id' => $kiosk_placed_order_id,
																  ]])->first();
		  if(!empty($check_for_lock)){
			   if($check_for_lock->lock_status == 1){
					if($this->request->session()->read('Auth.User.group_id') == KIOSK_USERS){
						 $perform_operation = 0;
					}else{
						 $perform_operation = 1;
					}
			   }else{
					$perform_operation = 1;
			   }
		  }else{
			   $perform_operation = 1;
		  }
		  
		  if($perform_operation == 1){
			   $qty_query = $this->on_demand_products->find('all',[
																   'conditions' => [
																					   'on_demand_products.kiosk_id' => $kiosk_id,
																					   'on_demand_products.kiosk_placed_order_id' => $kiosk_placed_order_id,
																					   'on_demand_products.product_id' => $product_id,
																				  ],
																 ]
															);
			   $qty_query = $qty_query->hydrate(false);
			   if(!empty($qty_query)){
					$qty = $qty_query->first();
			   }else{
					$qty = array();
			   }
			   
			   if(!empty($qty)){
					$updateQry = "UPDATE `on_demand_products` SET `quantity`='$quantity' WHERE `kiosk_id`='$kiosk_id' AND `kiosk_placed_order_id`='$kiosk_placed_order_id' AND `product_id`=$product_id";
					$conn = ConnectionManager::get('default');
					$stmt = $conn->execute($updateQry);
					echo json_encode(array('status' => '1'));
					$this->viewBuilder()->layout(false);die;
			   }else{
					echo json_encode(array('status' => '0'));
					$this->viewBuilder()->layout(false);die;
			   }
		  }else{
			   echo json_encode(array('status' => '2','msg' => "Order Is In Locked State"));
					$this->viewBuilder()->layout(false);die;
		  }
		 
		 
	 }
	 
	 
	 function updatedOrderQty(){
		 //pr($_REQUEST);die;
		  $kiosk_id = $_REQUEST['kiosk_id'];
		  $quantity = $_REQUEST['quantity'];
		  $kiosk_placed_order_id = $_REQUEST['kiosk_placed_order_id'];
		  $product_id = $_REQUEST['product_id'];
		  
		  $check_for_lock = $this->KioskPlacedOrders->find("all",['conditions' => [
																  'id' => $kiosk_placed_order_id,
																  ]])->first();
		  if(!empty($check_for_lock)){
			   if($check_for_lock->lock_status == 1){
					if($this->request->session()->read('Auth.User.group_id') == KIOSK_USERS){
						 $perform_operation = 0;
					}else{
						 $perform_operation = 1;
					}
			   }else{
					$perform_operation = 1;
			   }
		  }else{
			   $perform_operation = 1;
		  }
		  
		  
		  if($perform_operation == 1){
					$qty_query = $this->KioskOrderProducts->find('all',[
																		'conditions' => [
																							'KioskOrderProducts.kiosk_id' => $kiosk_id,
																							'KioskOrderProducts.kiosk_placed_order_id' => $kiosk_placed_order_id,
																							'KioskOrderProducts.product_id' => $product_id,
																					   ],
																	  ]
																 );
					//pr($qty_query);die;
					$qty_query = $qty_query->hydrate(false);
					if(!empty($qty_query)){
						 $qty = $qty_query->first();
					}else{
						 $qty = array();
					}
					
					if(!empty($qty)){
						 $updateQry = "UPDATE `kiosk_order_products` SET `quantity`='$quantity' WHERE `kiosk_id`='$kiosk_id' AND `kiosk_placed_order_id`='$kiosk_placed_order_id' AND `product_id`=$product_id";
						 $conn = ConnectionManager::get('default');
						 $stmt = $conn->execute($updateQry);
						 echo json_encode(array('status' => '1'));
						 $this->viewBuilder()->layout(false);die;
					}else{
						 echo json_encode(array('status' => '0'));
						 $this->viewBuilder()->layout(false);die;
					}
		  }else{
			   echo json_encode(array('status' => '2','msg' => 'Order Is In Lock State'));
						 $this->viewBuilder()->layout(false);die;
		  }
		 
	 }
	 
	 function updateLock(){
		 //pr($_REQUEST);die;
		  $kiosk_placed_order_id = $_REQUEST['id'];
		  $kiosk_id = $_REQUEST['kiosk_id'];
		  $res = $this->OnDemandOrders->find("all",['conditions' => [
																'id' => $kiosk_placed_order_id,
																'kiosk_id' => $kiosk_id
																]])->first();
		  if(!empty($res)){
			   $user_id = $this->request->session()->read('Auth.User.id');
			   $updateQry = "UPDATE `on_demand_orders` SET `lock_status`= 1, `locked_by` = $user_id WHERE `id`='$kiosk_placed_order_id'";
			   $conn = ConnectionManager::get('default');
			   $stmt = $conn->execute($updateQry);
			   echo json_encode(array('status' => '1'));
			   $this->viewBuilder()->layout(false);die;
		  }else{
			   echo json_encode(array('status' => '0'));
			   $this->viewBuilder()->layout(false);die;
		  }
		 
		 
	 }
	 
	 
	 public function updateLockNormal(){
		  $kiosk_placed_order_id = $_REQUEST['id'];
		  $kiosk_id = $_REQUEST['kiosk_id'];
		  $res = $this->KioskPlacedOrders->find("all",['conditions' => [
																'id' => $kiosk_placed_order_id,
																'kiosk_id' => $kiosk_id
																]])->first();
		  if(!empty($res)){
			   $user_id = $this->request->session()->read('Auth.User.id');
			   $group_id = $this->request->session()->read('Auth.User.group_id');
			   if($group_id == KIOSK_USERS){
					$updateQry = "UPDATE `kiosk_placed_orders` SET `lock_status`= 1,`kiosk_merged`= 1,`locked_by` = $user_id WHERE `id`='$kiosk_placed_order_id'";
			   }else{
					$updateQry = "UPDATE `kiosk_placed_orders` SET `lock_status`= 1,`locked_by` = $user_id WHERE `id`='$kiosk_placed_order_id'";	
			   }
			   
			   $conn = ConnectionManager::get('default');
			   $stmt = $conn->execute($updateQry);
			   echo json_encode(array('status' => '1'));
			   $this->viewBuilder()->layout(false);die;
		  }else{
			   echo json_encode(array('status' => '0'));
			   $this->viewBuilder()->layout(false);die;
		  }
	 }
	 
	 
	 public function disputedOrderList(){
		  $from_date = '';
		$startDate = '';
		if(!empty($this->request->query['from_date'])){
			$from_date = $this->request->query['from_date'];
			$startDate = date("Y-m-d",strtotime($from_date.'-1 day'));
		}
		
		$to_date = '';
		$endDate = '';
		if(!empty($this->request->query['to_date'])){
			$to_date = $this->request->query['to_date'];
			$endDate = date("Y-m-d",strtotime($to_date.'+1 day'));
		}
		  
		  
		  $selectedKiosk = $this->request->query['data']['kiosk'];
		  $total_dispute_cost = 0;
		  if($selectedKiosk != 10000){
				 $order_dispute_res = $this->OrderDisputes->find("all",[
												   'conditions' => [
													  'kiosk_id' => $selectedKiosk,
													  "DATE(admin_acted)>'$startDate'","DATE(admin_acted)<'$endDate'",
													  'approval_status' => 1
												   ]
												   ])->toArray();
		  }else{
			   $order_dispute_res = array();
		  }
		  $products_cost = $this->Products->find("list",[
											   'keyField' => "id",
											   'valueField' => "cost_price",
											   ])->toArray();
		  $products_name = $this->Products->find("list",[
											   'keyField' => "id",
											   'valueField' => "product",
											   ])->toArray();
		  $product_code = $this->Products->find("list",[
											   'keyField' => "id",
											   'valueField' => "product_code",
											   ])->toArray();
		  $kiosks = $this->Kiosks->find('list',[
									  'keyField' => 'id',
									  'valueField' => 'name',
									  ])->toArray();
	
		   $product_cats = $this->Products->find("list",[
											   'keyField' => "id",
											   'valueField' => "category_id",
											   ])->toArray();
		  $cat_name = $this->Categories->find("list",[
													  'keyField' => 'id',
													  'valueField' => 'category',
													  ])->toArray();
		  
		  $this->set(compact('order_dispute_res','kiosks','selectedKiosk','products_cost','products_name','product_code','cat_name','product_cats'));
	 }
	 
     
	  function add2CartShort(){
		extract($this->request->query);
		$quick_cart = $this->request->Session()->read('Basket');
		$prodErrArr = $itemArr = array();
		$kiosk_id = $this->request->Session()->read('kiosk_id');
		
			$productsTable = TableRegistry::get("products",[
																		'table' => "products",
																	]);
		
           
        $prodRow_query = $productsTable->find('all', array('conditions' => array('product_code' => $product_code),'recursive' => -1));
         $prodRow_query->hydrate(false);
        $prodRow = $prodRow_query->first();
       
		//--------price without vat and discounted value---------
		$vat = $this->VAT;
		if(count($prodRow) >= 1){
			$numerator = $prodRow['selling_price']*100;
			$denominator = $vat+100;
			$priceWithoutVat = $numerator/$denominator;
			if($prodRow['discount_status'] == 1){
				$disValue = $priceWithoutVat * $prodRow['discount']/100;
				$netVal = $priceWithoutVat - $disValue;
			}else{
				$netVal = 0;
			}
		}else{
			$prodErrArr['error'] = "Product either out of stock or invalid product code!";
		}
		
		if(is_array($quick_cart) && count($quick_cart) > 0){
			// pr($quick_cart);die;
			$items = $quick_cart;
			if(count($prodRow) >= 1 && $prodRow['quantity']){
			   
				//quanity should not be 0
				$prod_id = $prodRow['id'];
				//pr($quick_cart);echo "prod id : $prod_id";pr($items);
				if(array_key_exists($prod_id, $items)){
					//echo "updating quantity for added item";
					//updating only quantity
					$totalQty = $items[$prod_id]['quantity'] + $quantity;
					if($totalQty > $prodRow['quantity']){
						$availQty = $prodRow['quantity'];
						$items[$prod_id]['quantity'] = $availQty; //updating qty
						$qantity_short = 1;
						$prodErrArr['error'] = "Product Quantity adjusted to $availQty due to limited stock";
					}else{
						$items[$prod_id]['quantity'] = $totalQty; //updating qty
						$qantity_short = 0;
					}
					$items['position'] += 1; //updating position
					$items[$prod_id]['position'] = $items['position']; //updating position
					$items[$prod_id]['qantity_short'] = $qantity_short;
                   // pr($items);die;
					$quick_cart = $items;
				}else{
					//add new item to existing cart
					//echo "adding item to existing cart";
					$qantity_short = 0;
					if(array_key_exists('position',$items)){
						$items['position'] += 1; //updating position
					}else{
						$items['position'] = 1; //updating position
					}
					
					if($quantity > $prodRow['quantity']){
						$availQty = $prodRow['quantity'];
						$quantity = $availQty; //updating qty
						$qantity_short = 1;
						$prodErrArr['error'] = "Product Quantity adjusted to $availQty due to limited stock!";
					}
					
					$items[$prod_id] = array(
										'product' => $prodRow['product'],
										'quantity' => $quantity, //coming from box adjacent to google suggest
										'price' => $prodRow['selling_price'],
										'position' => $items['position'],
										'product_code' => $prodRow['product_code'],
										'remarks' => "",
										);
					$items[$prod_id]['qantity_short'] = $qantity_short;
					$quick_cart = $items;
				}
			}else{
				$prodErrArr['error'] = "Product either out of stock or invalid product code!";
			}
		}else{
			//create new cart
			// echo "create new cart";die;
			
			if(count($prodRow) >= 1 && $prodRow['quantity']){
				//quanity should not be 0
				$itemArr['position'] = 1;
				$prod_id = $prodRow['id'];
				$qantity_short = 0;
				if($quantity > $prodRow['quantity']){
					$availQty = $prodRow['quantity'];
					$quantity = $availQty; //updating qty
					$qantity_short = 1;
					$prodErrArr['error'] = "Product Quantity adjusted to $availQty due to limited stock!";
				}
				$itemArr[$prod_id] = array(
										'product' => $prodRow['product'],
										'quantity' => $quantity, //coming from box adjacent to google suggest
										'price' => $prodRow['selling_price'],
										'position' => $itemArr['position'],
										'product_code' => $prodRow['product_code'],
										'remarks' => "",
										);
				
				$itemArr[$prod_id]['qantity_short'] = $qantity_short;
				$quick_cart = $itemArr;
				
			}else{
				$prodErrArr['error'] = "Product either out of stock or invalid product code!";
				$quick_cart = array();
				$this->reorder_cart($quick_cart, $prodErrArr);
				//echo json_encode(array('msg' => 'Nothing to restore'));
				//$this->layout = false;
				//die;
			}
		}
		//pr($quick_cart);die;
		$this->reorder_cart($quick_cart, $prodErrArr);die;
	}
	 
	 private function reorder_cart($quick_cart, $prodErrArr = array()){
		$items = $sortedItems = $posArr = array();
        if(!empty($quick_cart)){
            $items = $quick_cart;    
        }
		
		if(!is_array($items)){
			$items = array();
		}else{
			foreach($items as $item){ $posArr[] = $item['position']; }
			rsort($posArr);
			  // pr($posArr);die;
			foreach($posArr as $pos){
				foreach($items as $key => $item){
					if($item['position'] == $pos){
						$sortedItems[$key] = $item;
						unset($items[$key]);
						break;
					}
				}
			}
			$new_arr = $quick_cart = $sortedItems;
			unset($new_arr['position']);
			if(empty($new_arr)){
			   unset($quick_cart['position']);
			}
			$this->request->Session()->write('Basket',$quick_cart);
		}
		
		//pr($_SESSION);die;
		//$quick_cart['new_sale_basket'] = array($sortedItems);
		$cart = $quick_cart;
		if(count($prodErrArr)){
			$quick_cart['prodError'] = $prodErrArr['error'];
			echo $quickCart = json_encode($quick_cart);
		}else{
			echo $quickCart = json_encode($quick_cart);
		}
		$serilized_data = serialize($cart);
		//--------------------------------
		if(is_array($quick_cart) && count($quick_cart)){
		  $kioskId = 3;
				//storing the session in session_backups table
			   $this->SessionRestore->update_session_backup_table($this->request->params['controller'], 'index', 'Basket', $quick_cart, $kioskId);
			   $tKioskID = 3;
			   $this->SessionRestore->update_session_backup_table($this->request->params['controller'], 'index', 'tKioskID', $tKioskID, $kioskId);
		  }
		//--------------------------------
		
		
		$this->viewBuilder()->layout(false);
		die;
	}
	
	function deleteFromCart(){
		extract($this->request->query);
		$quick_cart = $this->request->Session()->read('Basket');
		if(array_key_exists($prod_id,$quick_cart)){
			unset($_SESSION['Basket'][$prod_id]);
		}
		$quick_cart = $_SESSION['Basket'];
		$this->reorder_cart($quick_cart);
		$this->viewBuilder()->layout(false);
		die;
	}
	
	public function updateCart(){
		extract($this->request->query);
		if(empty($bulk)){$bulk = "";}
		$quick_cart = $this->request->Session()->read('Basket');
		$kiosk_id = $this->request->Session()->read('kiosk_id');
		
            $product_source  = 'products';
        
         $productTable = TableRegistry::get($product_source,[
                                                                    'table' => $product_source,
                                                                ]);
		$prodRow_query = $productTable->find('all', array('conditions' => array('id' => $prod_id),'recursive' => -1));
		$prodRow_first = $prodRow_query->first();
		$prodRow = $prodRow_first->toArray();
		
		$databse_qantity = $prodRow['quantity'];
		if(is_array($quick_cart) && count($quick_cart) > 0){
			if($qty > $databse_qantity){
				$allowed_qtity = $databse_qantity;
				$qantity_short = 1;
			}else{
				$allowed_qtity = $qty;
				$qantity_short = 0;
			}
			if(!array_key_exists('position', $quick_cart)){
				$quick_cart['position'] = 1;
			}else{
				$quick_cart['position'] += 1; //updating position
			}
			
			$items = $quick_cart;
			$items[$prod_id]['quantity'] = $allowed_qtity;
			$items[$prod_id]['position'] = $quick_cart['position'];
			$items[$prod_id]['price'] = $sp;
			$items[$prod_id]['qantity_short'] = $qantity_short;
			$quick_cart = $items;
		}
		$this->reorder_cart($quick_cart);
		die;
	}
	
	function updateStockAjaxBase(){
		//increase - script execution time - rasu
		$this->increase_execution_time();
		$kioskId = $this->request->Session()->read('kiosk_id');//this is readin the kiosk_id of main website
		$productsName_query = $this->Products->find('list',
													   [
															'valueField' => 'product',	
													   ]);
		$productsName_query = $productsName_query->hydrate(false);
		$productsName = $productsName_query->toArray();
		$current_page = '';
		///pr($this->request);die;
		if(array_key_exists('current_page',$this->request['data'])){
			$current_page = $this->request['data']['current_page'];
		}
		
		if(!isset($current_page)){$this->redirect(array('action' => 'index'));}
		
		$productCounts = 0;
		if(array_key_exists('KioskStock',$this->request['data'])){
			$kiosk_id = $this->request['data']['KioskStock']['kiosk_id'];
		}else{
			$kiosk_id = $this->request->Session()->read('kioskId');//for reading the kiosk id chosen in the dropdown
		}
		
		if(array_key_exists('searchQueryUrl',$this->request['data'])){
			$searchQueryUrl = $this->request['data']['searchQueryUrl'];
		}
		
		if(array_key_exists('basket',$this->request['data'])){
			$productArr = array();
			
			foreach($this->request['data']['KioskStock']['product_id'] as $ki => $ide){
				$currentPrice = $this->request['data']['KioskStock']['current_price'][$ki];
				$currentPriceArr[$ide] = $currentPrice; //storing price for each product
			}
			
			$price = $remarks = '';
			$prdctIdArr = array();
			foreach($this->request['data']['KioskStock']['quantity'] as $key => $quantity){
				if(!empty($quantity)){
					$productID = $this->request['data']['KioskStock']['product_id'][$key];
					$prdctIdArr[$productID] = $productID;
				}
			}
			if(empty($prdctIdArr)){
			   $prdctIdArr = array(0 => null);
			}
			$costPriceArr_query = $this->Products->find('list',
															[
																 'keyField' => 'id',
																 'valueField' => 'cost_price',
																 'conditions' => ['Products.id IN' => $prdctIdArr]
															]);
			
			$costPriceArr_query = $costPriceArr_query->hydrate(false);
			$costPriceArr = $costPriceArr_query->toArray();
			
			foreach($this->request['data']['KioskStock']['quantity'] as $key => $quantity){
				if(!empty($quantity)){
					$currentQuantity = $this->request['data']['KioskStock']['p_quantity'][$key];
					$productID = $this->request['data']['KioskStock']['product_id'][$key];
					$remarks = $this->request['data']['KioskStock']['remarks'][$key];
					$price = $this->request['data']['KioskStock']['price'][$key];
				
					if($price<$costPriceArr[$productID]){
						$this->Flash->error("New sale price must be greater than the cost price for {$productsName[$productID]}");
						return $this->redirect(array('action' => "ajaxBaseTransfer/page:$current_page"));
						die;
					}
				
					if($quantity > 0 && $quantity <= $currentQuantity){
						$productArr[$productID] = array(
										'quantity' => $quantity,
										'price' => $price,
										'remarks' => $remarks,
										);
						$productCounts++;
					}
				}
								
			}
			
			$session_basket = $this->request->Session()->read('Basket');
			
			if(count($session_basket) >= 1){
				//adding item to the the existing session
				$sum_total = $this->add_arrays(array($productArr,$session_basket));
				$this->request->Session()->write('Basket', $sum_total);
				$session_basket = $this->request->Session()->read('Basket');
			}else{
				//adding item first time to session
				if(count($productCounts))$this->request->Session()->write('Basket', $productArr);
			}
			$session_basket = $this->request->Session()->read('Basket');
			if(is_array($session_basket) && count($session_basket)){
				//storing the session in session_backups table
				$this->SessionRestore->update_session_backup_table($this->request->params['controller'], 'index', 'Basket', $session_basket, $kioskId);
				$tKioskID = $this->request['data']['KioskStock']['kiosk_id'];
				$this->SessionRestore->update_session_backup_table($this->request->params['controller'], 'index', 'tKioskID', $tKioskID, $kioskId);
			}
			$this->request->Session()->write('kioskId', $kiosk_id);
			//changing it to kioskId as kiosk_id is for the main key for website and
			//it will conflict with it while saving the session in sessionrestore table
			$totalItems = count($this->request->Session()->read('Basket'));
			
			if($productCounts){
				$flashMessage = "$productCounts product(s) added to the stock. Total item Count:$totalItems";
			}else{
				$flashMessage = "No item added to the stock. Item Count:$productCounts";
			}
			
			$this->Flash->success($flashMessage);
			if(!empty($searchQueryUrl)){
				return $this->redirect(array('action'=>"ajaxBaseTransfer/page:$current_page"));
				//return $this->redirect(array('action' => "index$searchQueryUrl"));
			}else{
				return $this->redirect(array('action' => "ajaxBaseTransfer/page:$current_page"));
			}
		}elseif(array_key_exists('empty_basket',$this->request['data'])){
			if($this->request->Session()->delete('Basket')){
				$this->SessionRestore->delete_from_session_backup_table("StockTransfer", 'index', 'Basket', $kioskId);
			}else{
			   $this->SessionRestore->delete_from_session_backup_table("StockTransfer", 'index', 'Basket', $kioskId);
			}
			$this->request->Session()->delete('kioskId');
			$flashMessage = "Basket is empty; Add Fresh Stock!";
			$this->Flash->success($flashMessage);
			return $this->redirect(array('action' => "ajaxBaseTransfer/page:$current_page"));			
		}elseif(array_key_exists('check_out',$this->request['data'])){
			if(isset($this->request['data']['KioskStock']['kiosk_id'])){
				$kiosk_id = $this->request['data']['KioskStock']['kiosk_id'];
				$this->request->Session()->write('kioskId', $kiosk_id);
			}elseif(empty($this->request['data']['KioskStock']['kiosk_id'])){
				$this->Flash->error('Please select the kiosk!!');
			}
			return $this->redirect(array('action' => "stockTransferCheckoutAjaxBase"));
		  
		}else{
			
			//$kiosk_id = $this->request['data']['KioskStock']['kiosk_id'];
			
			if(empty($kiosk_id)){
				$flashMessage = "Failed to transfer stock. <br />Please select kiosk for stock transfer!";
				$this->Flash->error($flashMessage,['escape' => false]);
				return $this->redirect(array('action' => "ajaxBaseTransfer/page:$current_page"));
				die;
			}
			
			$session_basket = $this->request->Session()->read('Basket');
			$qty_error = array();
			if(!empty($session_basket)){
				foreach($session_basket as $prd_id => $values){
					if($prd_id == "position"){
						 continue;
					}
					$req_qty = $values['quantity'];
					$qty_arr = $this->Products->find('list',array('conditions' => array('Products.id' => $prd_id),
																  'keyField' => 'id',
																  'valueField' => 'quantity',
																  //'fields' => array('id','quantity')
																  ))->toArray();
					$code_arr = $this->Products->find('list',array('conditions' => array('Products.id' => $prd_id),
																   'keyField' => 'id',
																   'valueField' => 'product_code',
																   //'fields' => array('id','product_code')
																   )
													  )->toArray();
					if($req_qty > $qty_arr[$prd_id]){
						$qty_error[] = $code_arr[$prd_id];
					}
				}	
			}
			if(!empty($qty_error)){
				$this->request->Session()->delete('Basket');
				$product_codes = implode(",",$qty_error);
				$error_str = "Not Sufficent Quantity For Product code $product_codes";
				$this->Flash->success($error_str);
			    return $this->redirect(array('action' => "ajaxBaseTransfer/page:$current_page"));
			    die;
			}
			
			
			
			$datetime = date('Y-m-d H:i:s');
			$kioskOrderData = array(
						'kiosk_id' => $kiosk_id,
                                                'user_id' => $this->request->Session()->read('Auth.User.id'),
						'dispatched_on' => $datetime,
						'status' => 1
						);
			
			$KioskOrders = $this->KioskOrders->newEntity();
			$KioskOrders = $this->KioskOrders->patchEntity($KioskOrders, $kioskOrderData,['validate' => false]);
			$this->KioskOrders->save($KioskOrders);
			$kiosk_order_id = $KioskOrders->id;
			
			$prodtCatArr_query = $this->Products->find('list',
															[
																 'keyField' => 'id',
																 'valueField' => 'category_id',
															]);
			$prodtCatArr_query = $prodtCatArr_query->hydrate(false);
			$prodtCatArr = $prodtCatArr_query->toArray();
			$session_basket = $this->request->Session()->read('Basket');
			$countTransferred = 0;
			$timestamp = time();
			$boloram_products = array();
            if(empty($session_basket)){
			   $error_str = "No Item In Cart.";
			   $this->Flash->success($error_str);
			    return $this->redirect(array('action' => "ajaxBaseTransfer/page:$current_page"));
			    die;
			}
			foreach($session_basket as $productID => $productData){
				if($productID == "position"){
					continue;
				}
				$cost_price_list_query = $this->Products->find('list',
																 [
																	  'keyField' => 'id',
																	  'valueField' => 'cost_price',
																	  'conditions' => array('Products.id' => $productID),
																 ]);
				$cost_price_list_query = $cost_price_list_query->hydrate(false);
				$cost_price_list = $cost_price_list_query->toArray();
				
			    $StockTransfer = $this->StockTransfer->newEntity();
			    
				$price = $productData['price'];
				$quantity = $productData['quantity'];
				$remarks = $productData['remarks']; 
				$boloram_products[$productID] = $quantity;
				$stockTransferData = array(
							'kiosk_order_id' => $kiosk_order_id,
							'kiosk_id' => $kiosk_id,
							'product_id' => $productID,
							'quantity' => $quantity,
							'cost_price' => $cost_price_list[$productID],
							'static_cost' => $cost_price_list[$productID],
							'sale_price' => $price,
							'status' => '1',
							'remarks' => $remarks
							);
				$StockTransfer = $this->StockTransfer->patchEntity($StockTransfer, $stockTransferData,['validate' => false]);
				if($this->StockTransfer->save($StockTransfer)){
					$countTransferred++;
					$kioskTable = "kiosk_transferred_stock_$kiosk_id";
					$tableQuery = $this->TableDefinition->get_table_defination('transferred_stock',$kiosk_id);
					$conn = ConnectionManager::get('default');
					$stmt = $conn->execute($tableQuery);
					
					$insertQuery = "INSERT INTO $kioskTable SET
								`kiosk_order_id` = $kiosk_order_id,
								`product_id` = $productID,
								`quantity` = $quantity,
								`sale_price` = $price,
								`created` = '$datetime',
								`status` = 1
							";
					$conn = ConnectionManager::get('default');
					$stmt = $conn->execute($insertQuery);
					
					//decreasing central stock
					
					//$this->Product->id = $productID;
					$external_site_status = $site_id_to_save = 0;
					$sites = Configure::read('sites');
					$external_sites = Configure::read('external_sites_for_bulk');
					$isboloRam = false;
					$path = dirname(__FILE__);
					if(!empty($sites)){
						 foreach($sites as $site_id => $site_value){
							  if($isboloRam){
								   continue;
							  }
							  $isboloRam = strpos($path,$site_value);
							  $site_id_to_save = $site_id;
							  if(in_array($site_value,$external_sites)){
								   $external_site_status = 1;
							  }
						 }
					}
					 //sourabh delete code
					if($isboloRam != false){
						$vat = $this->VAT;
						//$dummyproductArr = array('2151','2155','2388','2678','2988','4131','4753','5409','5411','5289','5287','6672');
						//if(in_array($productID,$dummyproductArr)){
							//$this->Product->setDataSource('ADMIN_DOMAIN_db');
							//$this->ReservedProducts->setDataSource('ADMIN_DOMAIN_db');
							//$this->TransferUnderstock->setDataSource('ADMIN_DOMAIN_db');
							
							$product_qty_query = "SELECT id,quantity From products";
							
							$connection = ConnectionManager::get('hpwaheguru');
							
							$stmt1 = $connection->execute('SELECT NOW() as created'); 
						    $currentTimeInfo = $stmt1 ->fetchAll('assoc');  
							$currentTime = $currentTimeInfo[0]['created'];
							
						    $stmt = $connection->execute($product_qty_query);
						    $product_qty = $stmt ->fetchAll('assoc');
							foreach($product_qty as $s_key => $s_value){
							  $prodtQtyArr[$s_value['id']] = $s_value['quantity'];
							}
						 
							$product_cost_query = "SELECT id,cost_price From products";
							$stmt = $connection->execute($product_cost_query);
						    $product_cost = $stmt ->fetchAll('assoc');
							foreach($product_cost as $s_key1 => $s_value1){
							  $prodtCostArr[$s_value1['id']] = $s_value1['cost_price'];
							}
							
							$product_sale_query = "SELECT id,selling_price From products";
							$stmt = $connection->execute($product_sale_query);
						    $product_sale = $stmt ->fetchAll('assoc');
							foreach($product_sale as $s_key2 => $s_value2){
							  $prodtSaleArr[$s_value2['id']] = $s_value2['selling_price'];
							}
							
							//$prodtQtyArr = $this->Product->find('list',array('fields'=>array('id','quantity'),'recursive'=>-1));
							//$prodtCostArr = $this->Product->find('list',array('fields'=>array('id','cost_price'),'recursive'=>-1));
							//$prodtSaleArr = $this->Product->find('list',array('fields'=>array('id','selling_price'),'recursive'=>-1));
							$wheguruQtity = $prodtQtyArr[$productID];
							if($wheguruQtity < $quantity){
								$sellPrice = $prodtSaleArr[$productID];
								$a = $sellPrice*100;
								$b = $vat+100;
								$orignalPrice = $a/$b;
								
								$transfer_under_stock_query = "SELECT * FROM transfer_understock WHERE product_id = $productID AND DATE(created) = CURDATE() AND site_id = $site_id_to_save";
								if($external_site_status == 1){
								   $transfer_under_stock_query .= " AND kiosk_id = $kiosk_id";
								}
								$stmt = $connection->execute($transfer_under_stock_query);
							    $underStockResult = $stmt ->fetchAll('assoc');
								
								$qtyToSub = $wheguruQtity;
								$remainingQty = $quantity - $wheguruQtity;
								$understockData = array(
														'product_id' => $productID,
														'quantity' => $remainingQty,
														'cost_price' => $prodtCostArr[$productID],
														'sale_price' => $orignalPrice,
														'invoice_reference' => $timestamp,
														'category_id' => $prodtCatArr[$productID],
														'created' => $currentTime,
														'modified' => $currentTime,
														'site_id' => $site_id_to_save,
														'kiosk_id' => $kiosk_id,
														);
								
								if(count($underStockResult) > 0){
									foreach($underStockResult as $key => $value){
										$understockId =  $value['id'];
									}
									$query = "UPDATE `transfer_understock` SET `modified` = '$currentTime' , `quantity` = `quantity` + $remainingQty WHERE `transfer_understock`.`id` = $understockId";
									if($external_site_status == 1){
										$query .= " AND kiosk_id = $kiosk_id";
									 }
									$stmt = $connection->execute($query);
									//$this->TransferUnderstock->query("");
								}else{
									$connection->insert('transfer_understock',
																		  $understockData
																	   , ['created' => 'datetime']);
									//$this->TransferUnderstock->save();
								}
								
							}else{
								$qtyToSub = $quantity;
							}
							
							
							if($qtyToSub > 0){
							  $Product_query = "UPDATE `products` SET `quantity` = `quantity` - $qtyToSub WHERE `products`.`id` = $productID";
							  $stmt = $connection->execute($Product_query);
							  //$product_qty = $stmt ->fetchAll('assoc');   
							  $reserve_result_query = "SELECT * FROM reserved_products WHERE product_id = $productID AND DATE(created) = CURDATE() AND status = 0 AND site_id =  $site_id_to_save";
							  if($external_site_status == 1){
								   $reserve_result_query .= " AND kiosk_id = $kiosk_id";
								}
							  $stmt = $connection->execute($reserve_result_query);
							  $rserveResult = $stmt ->fetchAll('assoc');
								if(count($rserveResult) > 0){
									foreach($rserveResult as $key => $value){
										$reserveId =  $value['id'];
										$oldQtity = $value['quantity'];
									}
									$ReservedProducts_query = "UPDATE `reserved_products` SET `modified` = '$currentTime', `quantity` = `quantity` + $quantity WHERE `reserved_products`.`id` = $reserveId AND `reserved_products`.`site_id` =  $site_id_to_save";
									if($external_site_status == 1){
										$ReservedProducts_query .= " AND kiosk_id = $kiosk_id";
									 }
									$stmt = $connection->execute($ReservedProducts_query);
								}else{
									$reservedProductData = array(
															 'product_id' =>  $productID,
															 'category_id' => $prodtCatArr[$productID],
															 'quantity' => $qtyToSub,
															 'cost_price' => $cost_price_list[$productID],
															 'sale_price' => $price,
															 'created' => $currentTime,
															'modified' => $currentTime,
															'site_id' => $site_id_to_save,
															'kiosk_id' => $kiosk_id,
															 );
									$connection->insert('reserved_products', 
																		  $reservedProductData
																	   , ['created' => 'datetime']);
									//$this->ReservedProducts->save($reservedProductData);
									//$this->Product->setDataSource('default');
								}
							}
						//}
					}
					//die;
				}else{
					//failed to create order
				}
			}
			//die;
			//$this->Products->setDataSource('default');
			//$this->ReservedProducts->setDataSource('default');
			//$this->TransferUnderstocks->setDataSource('default');
			$counter =  $this->update_qantities($boloram_products);
			$countTransferred = $counter + $countTransferred;   
			
			if($countTransferred > 0){
				//** code for sending pusher messages
				$pushStr = "Products have been transferred under order # $kiosk_order_id. Please receive them";
				  $this->Pusher->email_kiosk_push($pushStr,$kiosk_id);
				// till here **
			}
			$this->request->Session()->delete('kioskId');
			if(empty($kioskId)){
			   $kioskId1 = 10000;
			}else{
			   $kioskId1 = $kioskId;
			}
			if($this->request->Session()->delete('Basket')){
				$this->SessionRestore->delete_from_session_backup_table("StockTransfer", 'index', 'Basket', $kioskId1);
			}else{
			   $this->SessionRestore->delete_from_session_backup_table("StockTransfer", 'index', 'Basket', $kioskId1);
			}
            $count = 0;
            if(!empty($session_basket)){
			   $count = count($session_basket) - 1;   // this is done b/c there is position index in session which cause extra count in flash message.
			}
            $flashMessage = $count." products dispatched for order id $kiosk_order_id";
			$this->Flash->error($flashMessage);
		}		
		return $this->redirect(array('action' => "ajaxBaseTransfer/page:$current_page"));
	}
	
	public function restoreCart(){
		$quick_cart = $this->request->Session()->read('Basket');
		if(is_array($quick_cart) && count($quick_cart) > 0){
			//all good
			$this->reorder_cart($quick_cart);
		}else{
			echo json_encode(array('msg' => 'Nothing to restore'));
		}
		$this->viewBuilder()->layout(false);
		die;
	}
	
	public function ajaxBaseTransfer() {
		$this->increase_execution_time();
		//$displayType = "more_than_zero";
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
		if(!empty($this->request->query)){
		  if(array_key_exists('display_type',$this->request->query)){
			 $displayType = $this->request->query['display_type'];  
		  }else{
			   $displayType = "more_than_zero";
		  }
			
			$queryStr = $this->searchUrl();
			$categories = $queryStr[0];
			$conditionArr = $queryStr[1];
			$this->Products->find('all',array('conditions' => $conditionArr));
			$this->paginate = [
							   'limit' => 20,
							   'conditions' => $conditionArr];
			
		}elseif ($this->request->is(array('get', 'put'))) {
			if(array_key_exists('display_type',$this->request->query)){
				$displayType = $this->request->query['display_type'];
				if($displayType=="show_all"){
					$this->paginate = ['limit' => ROWS_PER_PAGE ,'order' => ['Products.id desc']];
				}elseif($displayType=="more_than_zero"){
					$this->paginate = ['limit' => ROWS_PER_PAGE,
												 'conditions'=>['NOT'=>['Products.quantity'=>0]],
												 'order' => ['Products.id desc']
										];
				}
			}else{
				$this->paginate = [
								   'limit' => ROWS_PER_PAGE,
								   'conditions'=>['NOT'=>['Products.quantity'=>0]],
								   'order' => ['Products.id desc']
								   ];
			}
		}
		$tKioskID = $this->request->Session()->read("tKioskID");
		if( !empty($tKioskID)){
			$this->request->Session()->write('kioskId', $tKioskID);
		}
		$session_basket = $this->request->Session()->read('Basket');
		$basketStrDetail = '';
		if(is_array($session_basket)){
			$productCodeArr = array();
			foreach($session_basket as $key => $basketItem){
					$product_query = $this->Products->find('all',
															  array('conditions'=>array('Products.id IN'=>$key),
																	'fields'=>array('id','product_code'))
															  );
					$product_query = $product_query->hydrate(false);
					$productCodeArr[] = $product_query->first();
			}
			$productCode = array();
			if(!empty($productCodeArr)){
				foreach($productCodeArr as $k=>$productCodeData){
					$productCode[$productCodeData['id']]=$productCodeData['product_code'];
				}
			}
			
			foreach($session_basket as $productId=>$productDetails){
			   if($productId == "position"){
					continue;
			   }
				$productName_query = $this->Products->find('all',array('conditions'=>array('Products.id IN'=>$productId),
																 'fields'=>array('id','product'))
													 );
				$productName_query = $productName_query->hydrate(false);
				$productName = $productName_query->first();
				
				$basketStrDetail.= "<tr>
				<td>".$productCode[$productId]."</td>
				<td>".$productName['product']."</td>
				<td>".$productDetails['price']."</td>
				<td>".$productDetails['quantity']."</td>
				<td>".$productDetails['remarks']."</td>
				</tr>";
			}
			
		}
		
			if(!empty($basketStrDetail)){
				$basketStr = "<table>
				<tr>
					<th style='width: 152px;'>Product code</th>
					<th>Product</th>
					<th style='width: 58px;'>New price</th>
					<th style='width: 31px;'>Qty</th>
					<th style='width: 94px;'>Remarks</th>
				</tr>".$basketStrDetail."
				</table>";
			}
			
			$totalItems = count($session_basket);
			if($totalItems){
				$flashMessage = "Total item Count:$totalItems<br/>$basketStr";
				//$this->Flash->success($flashMessage,array('escape' => false));
			}
			
		//$this->Paginator->settings = array('limit' => ROWS_PER_PAGE);
		//pr($this->paginate);die;
		$centralStocks_query = $this->paginate("Products");
		
		$centralStocks = $centralStocks_query->toArray();
		$categoryIdArr = array();
		foreach($centralStocks as $key=>$centralStock){
			$categoryIdArr[] = $centralStock->category_id;
		}
		$categoryName_query = $this->Categories->find('list',
													   [
															'keyField' => 'id',
															'valueField' => 'category',
															'conditions'=>['Categories.id IN'=>$categoryIdArr]
													   ]);
		
        $categoryName_query = $categoryName_query->hydrate(false);
        if(!empty($categoryName_query)){
            $categoryName = $categoryName_query->toArray();
        }else{
            $categoryName = array();
        }
        
		$hint = $this->ScreenHint->hint('stock_transfer','index');
        if(!$hint){
            $hint = "";
        }
		$this->set(compact('hint','categories','kiosks','displayType','centralStocks','categoryName'));
		//$this -> render('index');
	}
	
	function ajaxBaseSearch($keyword = "",$displayCondition = ""){
		$this->increase_execution_time();
		$session_basket = $this->request->Session()->read('Basket');
		$basketStrDetail = '';
		if(is_array($session_basket)){
			$productCodeArr = array();
			foreach($session_basket as $key => $basketItem){
			   $productCodeArr_query = $this->Products->find('all',array('conditions'=>array('Products.id'=>$key),'fields'=>array('id','product_code')));
			   $productCodeArr_query = $productCodeArr_query->hydrate(false);
			   $productCodeArr[] =$productCodeArr_query->first();
			}
			$productCode = array();
			if(!empty($productCodeArr)){
				foreach($productCodeArr as $k=>$productCodeData){
					$productCode[$productCodeData['id']]=$productCodeData['product_code'];
				}
			}
			
			foreach($session_basket as $productId=>$productDetails){
			   if($productId == "position"){
					continue;
			   }
				$productName_query = $this->Products->find('all',array('conditions'=>array('Products.id'=>$productId),'fields'=>array('id','product'),'recursive'=>-1));
				$productName_query = $productName_query->hydrate(false);
				$productName = $productName_query->first();
				$basketStrDetail.= "<tr>
				<td>".$productCode[$productId]."</td>
				<td>".$productName['product']."</td>
				<td>".$productDetails['price']."</td>
				<td>".$productDetails['quantity']."</td>
				<td>".$productDetails['remarks']."</td>
				</tr>";
			}
			
		}
		
		if(!empty($basketStrDetail)){
			$basketStr = "<table>
			<tr>
				<th style='width: 152px;'>Product code</th>
				<th>Product</th>
				<th style='width: 58px;'>New price</th>
				<th style='width: 31px;'>Qty</th>
				<th style='width: 94px;'>Remarks</th>
			</tr>".$basketStrDetail."
			</table>";
		}else{
		  $basketStr = "";
		}
		
		$totalItems = count($session_basket);
		if($totalItems){
			$flashMessage = "Total item Count:$totalItems<br/>$basketStr";
			//$this->Flash->success($flashMessage,array('escape' => false));
		}
		$tKioskID = $this->request->Session()->read("tKioskID");
		if( !empty($tKioskID)){
			$this->request->Session()->write('kioskId', $tKioskID);
		}
		if(array_key_exists('search_kw1',$this->request->query)){
			$search_kw = $this->request->query['search_kw1'];
		}
		
		$displayType = "";
		if(array_key_exists('display_type',$this->request->query)){
			$displayType = $this->request->query['display_type'];
		}
		
		 
		extract($this->request->query);
		$categories_query = $this->Categories->find('all',array(
								'fields' => array('id', 'category','id_name_path'),
                                                                'conditions' => array('Categories.status' => 1),
								'order' => 'Categories.category asc',
								'recursive' => -1
								));
		$categories_query = $categories_query->hydrate(false);
		$categories = $categories_query->toArray();
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
		if($displayType=="more_than_zero"){
			$conditionArr['NOT']['`Products`.`quantity`'] = 0;
		}
		//$this->Product->find('all',array('conditions' => $conditionArr));
		$this->paginate = [
						   'limit' => ROWS_PER_PAGE,
						   'conditions' => $conditionArr];
		$selectedCategoryId=array();
		if(array_key_exists('category_id IN',$conditionArr) && !empty($conditionArr['category_id IN'][0])){
			$selectedCategoryId=$conditionArr['category_id IN'];
		}
		$categories = $this->CustomOptions->category_options($categories,true,$selectedCategoryId);
		$kiosks_query = $this->Kiosks->find('list',
													   [
															'keyField' => 'id',
															'valueField' => 'name',
															'conditions' => ['Kiosks.status' => 1],
															'order' => 'Kiosks.name asc',
													   ]);
		$kiosks_query = $kiosks_query->hydrate(false);
		$kiosks = $kiosks_query->toArray();
		
		//pr($this->paginate);die;
		$centralStocks_query = $this->paginate("Products");
		$centralStocks = $centralStocks_query->toArray();
		 
		$categoryIdArr = array();
		foreach($centralStocks as $key=>$centralStock){
			$categoryIdArr[] = $centralStock->category_id;
		}
		if(empty($categoryIdArr)){
		  $categoryIdArr = array(0 => null);
		}
		$categoryName_query = $this->Categories->find('list',
															[
																 'keyField' => 'id',
																 'valueField' => 'category',
																 'conditions'=>['Categories.id IN'=>$categoryIdArr],
															]
												);
		$categoryName_query  =$categoryName_query->hydrate(false);
		$categoryName = $categoryName_query->toArray();
		//pr($categoryName);die;
		$hint = $this->ScreenHint->hint('stock_transfer','index');
					if(!$hint){
						$hint = "";
					}
		
		$this->set(compact('hint','categories','kiosks','displayType','centralStocks','categoryName'));
		$this -> render('ajaxBaseTransfer');
	}
	
	 public function all_kiosk_bill($startDate,$endDate){
		  $kiosks = $this->Kiosks->find("list",[
												  'conditions' => [
													'status' => 1,   
												  ],
												  'keyField' => "id",
												  'valueField' => "name",
												  'order'=>['name asc'],
											   ])->toArray();
		  foreach($kiosks as $kiosk_key => $kiosk_value){
			    $KioskOrders_source = "kiosk_orders";
					$KioskOrdersTable = TableRegistry::get($KioskOrders_source,[
																				'table' => $KioskOrders_source,
																			]);
					$transferredByWarehouse = array();
					//$this->StockTransfers->setSource('stock_transfer');
					$StockTransfers_source = "stock_transfer";
					$StockTransfersTable = TableRegistry::get($StockTransfers_source,[
																				'table' => $StockTransfers_source,
																			]);
			   $trnsfrd2KioskOrderIds = $StockTransfersTable->find('list',[
                                                                    'conditions'=>["DATE(created)>'$startDate'","DATE(created)<'$endDate'"],
                                                                    'keyField' => 'id',
                                                                    'valueField' => 'kiosk_order_id',
                                                                    //'recursive'=>-1
                                                                   ]);
			   if(!empty($trnsfrd2KioskOrderIds)){
				   $trnsfrd2KioskOrderIds = $trnsfrd2KioskOrderIds->toArray();
			   }else{
				   $trnsfrd2KioskOrderIds = array();
			   }
			   if(empty($trnsfrd2KioskOrderIds)){
				   $trnsfrd2KioskOrderIds = array( 0 => null);
			   }
			   
			   
			   $checkIfBelongToKiosk = $KioskOrdersTable->find('list',[
																		'keyField' => 'id',
																		'valueField' => 'id',
																		'conditions'=>array('kiosk_id'=>$kiosk_key, 'id IN'=>$trnsfrd2KioskOrderIds),
																		//'recursive'=>-1
																	   ]);
				if(!empty($checkIfBelongToKiosk)){
					$checkIfBelongToKiosk = $checkIfBelongToKiosk->toArray();
				}else{
					$checkIfBelongToKiosk = array();
				}
				//pr($checkIfBelongToKiosk);
				if(empty($checkIfBelongToKiosk)){
					$checkIfBelongToKiosk = array(0 => null);
				}
				
				
				$transferredByWarehouse = $StockTransfersTable->find('all',array('conditions'=>array('kiosk_order_id IN'=>$checkIfBelongToKiosk),'fields'=>array('product_id','sale_price','quantity','cost_price')));
			   $transferredByWarehouse = $transferredByWarehouse->hydrate(false);
			   if(!empty($transferredByWarehouse)){
				   $transferredByWarehouse = $transferredByWarehouse->toArray();
			   }else{
				   $transferredByWarehouse = array();
			   }		
			   $costTransByWh = 0;
			   if(!empty($transferredByWarehouse)){
				   foreach($transferredByWarehouse as $key=>$byWarehouse){
					   $costTransByWh+=floatval($byWarehouse['cost_price'] * $byWarehouse['quantity']);
					   $productIds[$byWarehouse['product_id']] = $byWarehouse['product_id'];
				   }
			   }
				$final_arr[$kiosk_key]["stock_transfer"] = $costTransByWh;
				
				// second part
				
				
				if($kiosk_key != 10000){
						 $order_dispute_res = $this->OrderDisputes->find("all",[
														   'conditions' => [
															  'kiosk_id' => $kiosk_key,
															  "DATE(admin_acted)>'$startDate'","DATE(admin_acted)<'$endDate'",
															  'approval_status' => 1
														   ]
														   ])->toArray();
						// pr($order_dispute_res);die;
						 $order_dispute = array();
						 if(!empty($order_dispute_res)){
						   foreach($order_dispute_res as $key => $value){
							 $product_id = $value->product_id;
							 $reciving_status = $value->receiving_status;
							 $quantity = $value->quantity;
							 
							 $cost_price = $value->cost_price;
							 $sale_price = $value->sale_price;
							 
							 $order_dispute[][$value->product_id] = array('receiving_status' => $reciving_status,
																		   'quantity' => $quantity,
																		   'cost_price' => $cost_price,
																		   'sale_price' => $sale_price,
																		  );
						   }
						   
						 }
						 
						 $products = $this->Products->find("list",[
													   'keyField' => "id",
													   'valueField' => "cost_price",
													   ])->toArray();
						 $total_dispute_cost_static = 0;
						 if(!empty($order_dispute)){
						  $recive_more_cost_static =  $recive_less_cost_static = $recive_less_cost = $recive_more_cost = 0;
						   foreach($order_dispute as $temp_key => $temp_value){
							  foreach($temp_value as $p_id => $summary_arr){
								if($summary_arr['receiving_status'] == -1){
									 $recive_less_cost += $products[$p_id] * $summary_arr['quantity'];
									 $recive_less_cost_static += $summary_arr['cost_price'] * $summary_arr['quantity'];
								}else{
									 $recive_more_cost += $products[$p_id] * $summary_arr['quantity'];
									 $recive_more_cost_static += $summary_arr['cost_price'] * $summary_arr['quantity'];
								}
							  }
								   //echo   $recive_less_cost;echo "</br>";
						   }
						   
						   $total_dispute_cost = $recive_less_cost - $recive_more_cost;
						   $total_dispute_cost_static = $recive_less_cost_static - $recive_more_cost_static;
						 }
				  }else{
					$total_dispute_cost_static =0;
				  }
				  
				  $final_arr[$kiosk_key]["order_dispute"] = $total_dispute_cost_static;
				  
				  // geting stock returend
				  
				  
				  $KioskOrders_source = "center_orders";
					$KioskOrdersTable = TableRegistry::get($KioskOrders_source,[
																				'table' => $KioskOrders_source,
																			]);
					
				  $transferredByKiosk = array();
					//$this->StockTransfers->setSource('stock_transfer_by_kiosk');
					$StockTransfers_source = "stock_transfer_by_kiosk";
					$StockTransfersTable = TableRegistry::get($StockTransfers_source,[
																				'table' => $StockTransfers_source,
																			]);
					
					$trnsfrdByKioskOrderIds_query = $StockTransfersTable->find('list',[
																				 'conditions'=>["DATE(created)>'$startDate'","DATE(created)<'$endDate'"],
																				 'keyField' => 'id',
																				 'valueField' => 'kiosk_order_id',
																				 //'recursive'=>-1
																				]);
					if(!empty($trnsfrdByKioskOrderIds_query)){
						$trnsfrdByKioskOrderIds = $trnsfrdByKioskOrderIds_query->toArray();
					}else{
						$trnsfrdByKioskOrderIds = array();
					}
					if(empty($trnsfrdByKioskOrderIds)){
						$trnsfrdByKioskOrderIds = array(0 => null);
					}
					$checkIfBelong2Kiosk_query = $KioskOrdersTable->find('list',[
																		   'keyField' => 'id',
																		   'valueField' => 'id',
																		   'conditions'=>['kiosk_id'=>$kiosk_key, 'id IN'=>$trnsfrdByKioskOrderIds,
																						  'status' => 2
																						  ],
																		   //'recursive'=>-1
																		  ]);
					
					if(!empty($checkIfBelong2Kiosk_query)){
						$checkIfBelong2Kiosk = $checkIfBelong2Kiosk_query->toArray();
					}else{
						$checkIfBelong2Kiosk = array();
					}
					if(empty($checkIfBelong2Kiosk)){
						$checkIfBelong2Kiosk = array(0 =>null);
					}
					
					$transferredByKiosk_query = $StockTransfersTable->find('all',array('conditions'=>array('kiosk_order_id IN'=>$checkIfBelong2Kiosk),'fields'=>array('product_id','sale_price','quantity','cost_price')));
					$transferredByKiosk_query = $transferredByKiosk_query->hydrate(false);
					if(!empty($transferredByKiosk_query)){
						$transferredByKiosk = $transferredByKiosk_query->toArray();
					}else{
						$transferredByKiosk = array();
					}
					$productIds = array();
					$costStockTransByKiosk = 0;
					if(!empty($transferredByKiosk)){
						foreach($transferredByKiosk as $key=>$byKiosk){
							$costStockTransByKiosk+=floatval($byKiosk['cost_price'] * $byKiosk['quantity']);
							$productIds[$byKiosk['product_id']] = $byKiosk['product_id'];
						}
					}
				  
				 $final_arr[$kiosk_key]["stock_returned"] = $costStockTransByKiosk; 
				  
				  
				  // getting repair cost
					$repairIdsArray = array();
		
					$repairIdsData = $this->MobileRepairLogs->find('list',
														[
															'conditions' => [
															'MobileRepairLogs.repair_status' => DISPATCHED_2_KIOSK_REPAIRED,
															"DATE(MobileRepairLogs.created) > '$startDate'",
															"DATE(MobileRepairLogs.created) < '$endDate'",
															"kiosk_id" => (int)$kiosk_key, //Added by rajiv
															],
															'keyField' => 'mobile_repair_id',
															'valueField' => 'status',
															'order' => ['MobileRepairLogs.id asc']
														]);
					if(!empty($repairIdsData)){
						$repairIdsData = $repairIdsData->toArray();
					}else{
						$repairIdsData = array();
					}
					$repairIds = array_keys($repairIdsData);
					$repairIdsArray = $repairIds;
					//getting brand id, model id, problem type from mobile repair table for above ids
					$repairDetail = array();
					if(count($repairIds)){
						$repairDetail_query = $this->MobileRepairs->find('all',array('conditions'=>array('MobileRepairs.id IN' => $repairIds),'fields'=>array('id','brand_id','mobile_model_id','problem_type','net_cost')));
						$repairDetail_query = $repairDetail_query->hydrate(false);
						if(!empty($repairDetail_query)){
							$repairDetail = $repairDetail_query->toArray();
						}else{
							$repairDetail = array();
						}
					}
					
					$fixedRepairCost = 0;
					//getting cost price corresponding to the brand,model,problem combination
					$repairCostArr = array();
					if(!empty($repairDetail)){
						foreach($repairDetail as $key=>$repairInfo){
							$fixedRepairCost+=floatval($repairInfo['net_cost']);
						}
					}
					
				$final_arr[$kiosk_key]["repair_cost"] = $fixedRepairCost; 	
					
				// getting repair cost
				
				// getting unlock cost
					$unlockIds = $this->MobileUnlockLogs->find('list',[
                                                           'conditions' => [
                                                                'MobileUnlockLogs.unlock_status IN' => array(UNLOCK_PROCESSED_CONFIRMATION_SENT_2_KIOSK, DISPATCHED_2_KIOSK_UNLOCKED),
                                                                "DATE(MobileUnlockLogs.created)>'$startDate'",
                                                                "DATE(MobileUnlockLogs.created) < '$endDate'",
                                                                "kiosk_id" => (int)$kiosk_key,
                                                                ],
                                                                'keyField' => 'id',
                                                                'valueField' => 'mobile_unlock_id',
                                                          ]);
					if(!empty($unlockIds)){
						$unlockIds = $unlockIds->toArray();
					}else{
						$unlockIds = array();
					}
					$unlockIdsArray = $unlockIds;
					
					
					$unlockDetail = array();
					if(count($unlockIds)){
						$unlockDetail_query = $this->MobileUnlocks->find('all',array('conditions' => array('MobileUnlocks.id IN' => $unlockIds),'fields' => array('id','brand_id', 'mobile_model_id', 'network_id','net_cost')));
						$unlockDetail_query = $unlockDetail_query->hydrate(false);
						if(!empty($unlockDetail_query)){
							$unlockDetail = $unlockDetail_query->toArray();
						}else{
							$unlockDetail = array();
						}
					}
					
					$fixedUnlockCost = 0;
					//getting cost price corresponding to the brand,model,network combination
					$unlockCostArr = array();
					if(!empty($unlockDetail)){
						foreach($unlockDetail as $key => $unlockInfo){
							$fixedUnlockCost+=floatval($unlockInfo['net_cost']);
						}
					}
				// getting unlock cost
				
				$final_arr[$kiosk_key]["unlock_cost"] = $fixedUnlockCost; 	
				
				// getting cost of sold phone
				$mobileResale = array();
			   //getting purchase id of all the sold phones in this time period
			   $mobileResale = $this->MobileReSales->find('all',array('fields'=>array('MobileReSales.id','MobileReSales.mobile_purchase_id','MobileReSales.refund_status'),'conditions'=>array("DATE(MobileReSales.created)>'$startDate'","DATE(MobileReSales.created)<'$endDate'",'MobileReSales.kiosk_id'=>$kiosk_key),'order'=>'MobileReSales.id DESC'));
			   $mobileResale = $mobileResale->hydrate(false);
			   if(!empty($mobileResale)){
				   $mobileResale = $mobileResale->toArray();
			   }else{
				   $mobileResale = array();
			   }
			   
			   $returnPurchaseIds = array();
			   $allPurchaseIds = array();
			   
			   $purchaseIds = array();
			   if(!empty($mobileResale)){
				   foreach($mobileResale as $key=>$mobileResaleData){
					   $allPurchaseIds[] = $mobileResaleData['mobile_purchase_id'];
					   if($mobileResaleData['refund_status']!=1){
						   $purchaseIds[] = $mobileResaleData['mobile_purchase_id'];
					   }
					   if($mobileResaleData['refund_status']==1){
						   $returnPurchaseIds[] = $mobileResaleData['mobile_purchase_id'];
					   }
				   }
			   }
			   if(empty($allPurchaseIds)){
				   $allPurchaseIds = array(0 => null);
			   }
			   //pr($allPurchaseIds);
			   $purchaseCostList = array();
			   $purchaseCostList_query = $this->MobilePurchases->find('all',array('fields'=>array('MobilePurchases.id','MobilePurchases.topedup_price','MobilePurchases.cost_price'),'conditions'=>array('MobilePurchases.id IN'=>$allPurchaseIds),'order'=>'MobilePurchases.id DESC'));
			   $purchaseCostList_query = $purchaseCostList_query->hydrate(false);
			   if(!empty($purchaseCostList_query)){
				   $purchaseCostList = $purchaseCostList_query->toArray();
			   }else{
				   $purchaseCostList = array();
			   }
			   //pr($purchaseCostList);
			   $costPrice = array();
			   if(!empty($purchaseCostList)){
				   foreach($purchaseCostList as $key=>$purchaseCostDetail){
					   $topedup_price = $purchaseCostDetail['topedup_price'];
					   $cost_price = $purchaseCostDetail['cost_price'];
					   
					   if($topedup_price>0){
						   $finalPrice = $topedup_price;
					   }else{
						   $finalPrice = $cost_price;
					   }
					   $purchase_id = $purchaseCostDetail['id'];
					   
					   $costPrice[$purchase_id]=$finalPrice;
				   }
			   }
			   
			   $totalPhoneCost = 0;
			   if(!empty($purchaseIds)){
				 //pr($costPrice);
				 //pr($purchaseIds);die;
				   foreach($purchaseIds as $key=>$mobilePurchaseId){
					   if(!empty($costPrice)){
						   if(array_key_exists($mobilePurchaseId,$costPrice)){
								$totalPhoneCost+=$costPrice[$mobilePurchaseId];
						   }
					   }
				   }
			   }
				// getting cost of sold phone
			
			   $final_arr[$kiosk_key]["sold_phone_cost"] = $totalPhoneCost;
				
				// getting cost of bulk sold phone
					$mobileBlkResale = array();
					$mobileBlkResale = $this->MobileBlkReSales->find('all',array('fields'=>array('MobileBlkReSales.id','MobileBlkReSales.mobile_purchase_id','MobileBlkReSales.refund_status'),'conditions'=>array("DATE(MobileBlkReSales.created)>'$startDate'","DATE(MobileBlkReSales.created)<'$endDate'",'MobileBlkReSales.kiosk_id'=>$kiosk_key),'order'=>'MobileBlkReSales.id DESC'));
					$mobileBlkResale = $mobileBlkResale->hydrate(false);
					if(!empty($mobileBlkResale)){
						$mobileBlkResale = $mobileBlkResale->toArray();
					}else{
						$mobileBlkResale = array();
					}
					
					$allBlkPurchaseIds = $bulkPurchaseIds = $blkRetunrendIds = array();
					
					if(!empty($mobileBlkResale)){
						foreach($mobileBlkResale as $key => $value){
							$allBlkPurchaseIds[] = $value['mobile_purchase_id'];
							if($value['refund_status'] != 1){
								$bulkPurchaseIds[] = $value['mobile_purchase_id'];
							}
							if($value['refund_status'] == 1){
								$blkRetunrendIds[] = $value['mobile_purchase_id'];
							}
						}
					}
					if(empty($allBlkPurchaseIds)){
						$allBlkPurchaseIds = array(0 => null);
					}
					$bulkPurchaseCostList = array();
					$bulkPurchaseCostList = $this->MobilePurchases->find('all',array('fields'=>array('MobilePurchases.id','MobilePurchases.topedup_price','MobilePurchases.cost_price'),'conditions'=>array('MobilePurchases.id IN'=>$allBlkPurchaseIds),'order'=>'MobilePurchases.id DESC'));
					$bulkPurchaseCostList = $bulkPurchaseCostList->hydrate(false);
					if(!empty($bulkPurchaseCostList)){
						$bulkPurchaseCostList = $bulkPurchaseCostList->toArray();
					}else{
						$bulkPurchaseCostList = array();
					}
					//pr($bulkPurchaseCostList);		
					$bulkCostPrice = array();
					if(!empty($bulkPurchaseCostList)){
						foreach($bulkPurchaseCostList as $key1=>$purchaseCostDetail1){
							$bulk_topedup_price = $purchaseCostDetail1['topedup_price'];
							$bulk_cost_price = $purchaseCostDetail1['cost_price'];
							
							if($bulk_topedup_price>0){
								$finalPrice1 = $bulk_topedup_price;
							}else{
								$finalPrice1 = $bulk_cost_price;
							}
							$purchase_id1 = $purchaseCostDetail1['id'];
							
							$bulkCostPrice[$purchase_id1]=$finalPrice1;
						}
					}
					
					$totalBulkPhoneCost = 0;
					if(!empty($bulkPurchaseIds)){
						foreach($bulkPurchaseIds as $key=>$bulkPurchaseIds1){
							if(!empty($bulkCostPrice)){
								if(array_key_exists($bulkPurchaseIds1,$bulkCostPrice)){
									 $totalBulkPhoneCost+=$bulkCostPrice[$bulkPurchaseIds1];
								}
							}
						}
					}
				// getting cost of bulk sold phone
			 $final_arr[$kiosk_key]["bulk_sold_phone_cost"] = $totalBulkPhoneCost;
			 
			 // getting returned phone cost
			 $totalReturnCost = 0;
			   if(!empty($returnPurchaseIds)){
				   foreach($returnPurchaseIds as $key=>$mobileReturnPurchaseId){
					   if(!empty($costPrice)){
						   if(array_key_exists($mobileReturnPurchaseId,$costPrice)){
								$totalReturnCost+=$costPrice[$mobileReturnPurchaseId];	 
						   }
					   }
				   }
			   }
			 // getting returned phone cost
			 $final_arr[$kiosk_key]["return_phone_cost"] = $totalReturnCost;
			 
			 
			 // getting returned bulk phone cost
			 $totalBulkReturnCost = 0;
			   if(!empty($blkRetunrendIds)){
				   foreach($blkRetunrendIds as $key=>$blkRetunrendIds1){
					   if(!empty($bulkCostPrice)){
						   if(array_key_exists($blkRetunrendIds1,$bulkCostPrice)){
							   $totalBulkReturnCost+=$bulkCostPrice[$blkRetunrendIds1]; 
						   }
						   
					   }
				   }
			   }
			 // getting returned bulk phone cost
			 $final_arr[$kiosk_key]["blk_return_phone_cost"] = $totalBulkReturnCost;
		  }
		  
		  $this->set(compact('final_arr',"kiosks"));
     }
	 
	 
	  public function disputedOrderExport(){
		  
		  $path = realpath(dirname(__FILE__));
		  if (strpos($path,ADMIN_DOMAIN) !== false) {
			 $adminSite = true;  
		  }else{
			   $adminSite = false;  
		  }
		  
		  $from_date = '';
		$startDate = '';
		if(!empty($this->request->query['from_date'])){
			$from_date = $this->request->query['from_date'];
			$startDate = date("Y-m-d",strtotime($from_date.'-1 day'));
		}
		
		$to_date = '';
		$endDate = '';
		if(!empty($this->request->query['to_date'])){
			$to_date = $this->request->query['to_date'];
			$endDate = date("Y-m-d",strtotime($to_date.'+1 day'));
		}
		  
		  if(array_key_exists('kiosk',$this->request->query)){
			$selectedKiosk = $this->request->query['kiosk'];   
		  }else{
			   $selectedKiosk = "";
		  }
		  if($selectedKiosk == ""){
			   $kiosks_list = $this->Kiosks->find("list",[
													   'conditions' => [
														 'status' => 1,   
													   ],
													   'keyField' => "id",
													   'valueField' => "id",
													])->toArray();
			   if(empty($kiosks_list)){
					$kiosks_list = array(0 => null);
			   }
			$total_dispute_cost = 0;			   
					  $order_dispute_res = $this->OrderDisputes->find("all",[
														'conditions' => [
														   'kiosk_id IN' => $kiosks_list,
														   "DATE(admin_acted)>'$startDate'","DATE(admin_acted)<'$endDate'",
														   'approval_status' => 1
														]
														])->toArray();   
		  }else{
			$total_dispute_cost = 0;
			   if($selectedKiosk != 10000){
					  $order_dispute_res = $this->OrderDisputes->find("all",[
														'conditions' => [
														   'kiosk_id' => $selectedKiosk,
														   "DATE(admin_acted)>'$startDate'","DATE(admin_acted)<'$endDate'",
														   'approval_status' => 1
														]
														])->toArray();
			   }else{
					$order_dispute_res = array();
			   }   
		  }
		  
		  $products_cost = $this->Products->find("list",[
											   'keyField' => "id",
											   'valueField' => "cost_price",
											   ])->toArray();
		  $products_name = $this->Products->find("list",[
											   'keyField' => "id",
											   'valueField' => "product",
											   ])->toArray();
		  $product_code = $this->Products->find("list",[
											   'keyField' => "id",
											   'valueField' => "product_code",
											   ])->toArray();
		  $kiosks = $this->Kiosks->find('list',[
									  'keyField' => 'id',
									  'valueField' => 'name',
									  ])->toArray();
	
		   $product_cats = $this->Products->find("list",[
											   'keyField' => "id",
											   'valueField' => "category_id",
											   ])->toArray();
		  $cat_name = $this->Categories->find("list",[
													  'keyField' => 'id',
													  'valueField' => 'category',
													  ])->toArray();
		  $final_arr_res = array();
		 // pr($order_dispute_res);die;
		 
		 $main_site_db = Configure::read('MAIN_SITE_DB');
		$conn = ConnectionManager::get($main_site_db);
		$query = "SELECT `id`,`cost_price` FROM `products`";
	    $stmt = $conn->execute($query);
	    $query_res = $stmt->fetchAll('assoc');
		$hp_cost_arr = array();
		if(!empty($query_res)){
		  foreach($query_res as $k => $v){
			   $hp_cost_arr[$v["id"]] = $v["cost_price"];
		  }
		}
		$startDate = "'".date("Y-m-d",strtotime($startDate))."'";
		$endDate = "'".date("Y-m-d",strtotime($endDate))."'";
		$daily_stock_query = "SELECT `product_id`,`cost_price`,`created` FROM `daily_stocks` WHERE  DATE(`created`) > $startDate AND DATE(`created`) < $endDate";
	    $stmt = $conn->execute($daily_stock_query);
	    $daily_stock_query_res = $stmt->fetchAll('assoc');
		$created_product_cst_arr = array();
		if(!empty($daily_stock_query_res)){
		  foreach($daily_stock_query_res as $daily_key => $daily_val){
			   $created_product_cst_arr[date("Y-m-d",strtotime($daily_val['created']))][$daily_val['product_id']] = $daily_val['cost_price'];
		  }
		}
		
		
		$conn = ConnectionManager::get("default");
		 
		 
		  foreach($order_dispute_res as $key => $warehouseData){
            $amt = 0;
           $product_id = $warehouseData->product_id;
           $qty = $warehouseData->quantity;
           $reciving_status = $warehouseData->receiving_status;
           if($reciving_status == -1){
            $reciving_status_name = "Received Less";
           }else{
            $reciving_status_name = "Received More";
           }
           $admin_Acted = $warehouseData->admin_acted;
		   $cost_price = $warehouseData->cost_price;
		   $amt = $cost_price * $qty;
		  
			   $final_arr_res[$key]["Product_id"] = $product_id;
			   $final_arr_res[$key]["Product Code"] = $product_code[$product_id];
			   $final_arr_res[$key]["Product"] = $products_name[$product_id];
			   $final_arr_res[$key]["Category"] = $cat_name[$product_cats[$product_id]];
			   $final_arr_res[$key]["Cost Price"] = number_format($cost_price,2);
			   if(!$adminSite){
						 
						 $loggedInUser =  $this->request->session()->read('Auth.User.username');
						 if (!preg_match('/'.QUOT_USER_PREFIX.'/',$loggedInUser)){
							 
						 }else{
							  $created = date("Y-m-d",strtotime($admin_Acted));
							  $p_id = $warehouseData->product_id;
							  
							  $cost_price_temp = "";
							  if(array_key_exists($created,$created_product_cst_arr)){
								   if(array_key_exists($p_id,$created_product_cst_arr[$created])){
										$cost_price_temp = $created_product_cst_arr[$created][$p_id];
								   }
							  }
						 
						
							  if(!empty($cost_price_temp)){
									$cost_price = $cost_price_temp;
							  }else{
								   $cost_price = $hp_cost_arr[$p_id]; 
							  }
							  $final_arr_res[$key]['Wharehouse Cost Price'] = $cost_price;
						 }
						 $conn = ConnectionManager::get("default");
			   }
			   
			   $final_arr_res[$key]["Quantity"] = $qty;
			   if($selectedKiosk == ""){
					$final_arr_res[$key]["Kiosks"] = $kiosks[$warehouseData->kiosk_id];
			   }
			   $final_arr_res[$key]["Receiving Status"] =  $reciving_status_name;
			   $final_arr_res[$key]["Amount"] = number_format($amt,2);
			   $final_arr_res[$key]["Admin Acted Date"] = date('d-m-Y h:i A',strtotime($admin_Acted));
		}
		
		$this->outputCsv('Dispute_'.time().".csv" ,$final_arr_res);
		$this->autoRender = false;
		
	 }
	 
	 
	   public function crossStockReturnExport(){
		  
		  $path = realpath(dirname(__FILE__));
		  if (strpos($path,ADMIN_DOMAIN) !== false) {
			 $adminSite = true;  
		  }else{
			   $adminSite = false;  
		  }
		  
		  if(array_key_exists('kiosk',$this->request->query)){
			 $selectedKiosk = $this->request->query['kiosk'];  
		  }else{
			   $selectedKiosk = "";
		  }
		
		$from_date = '';
		$startDate = '';
		if(!empty($this->request->query['from_date'])){
			$from_date = $this->request->query['from_date'];
			$startDate = date("Y-m-d",strtotime($from_date.'-1 day'));
		}
		
		$to_date = '';
		$endDate = '';
		if(!empty($this->request->query['to_date'])){
			$to_date = $this->request->query['to_date'];
			$endDate = date("Y-m-d",strtotime($to_date.'+1 day'));
		}
		
	
		//$this->KioskOrder->setSource("center_orders");
        $KioskOrders_source = "center_orders";
        $KioskOrdersTable = TableRegistry::get($KioskOrders_source,[
                                                                    'table' => $KioskOrders_source,
                                                                ]);
		$transferredByKiosk = array();
		//$this->StockTransfer->setSource('stock_transfer_by_kiosk');
        $StockTransfers_source = "stock_transfer_by_kiosk";
        $StockTransfersTable = TableRegistry::get($StockTransfers_source,[
                                                                    'table' => $StockTransfers_source,
                                                                ]);
		
		$trnsfrdByKioskOrderIds_query = $StockTransfersTable->find('list',[
                                                                     'conditions'=>["DATE(created)>'$startDate'","DATE(created)<'$endDate'"],
                                                                     'keyField' => 'id',
                                                                     'valueField' => 'kiosk_order_id'
                                                                     //'recursive'=>-1
                                                                    ]
                                                            );
        if(!empty($trnsfrdByKioskOrderIds_query)){
            $trnsfrdByKioskOrderIds = $trnsfrdByKioskOrderIds_query->toArray();
        }else{
            $trnsfrdByKioskOrderIds = array();
        }
		if(empty($trnsfrdByKioskOrderIds)){
            $trnsfrdByKioskOrderIds = array(0 => null);
        }
		if($selectedKiosk == ""){
		  $kiosks_list = $this->Kiosks->find("list",[
													   'conditions' => [
														 'status' => 1,   
													   ],
													   'keyField' => "id",
													   'valueField' => "id",
													])->toArray();
		  if(empty($kiosks_list)){
			   $kiosks_list = array(0 => null);
		  }
		  $checkIfBelong2Kiosk = $KioskOrdersTable->find('list',[
                                                               'keyField' => 'id',
                                                               'valueField' => 'kiosk_id',
                                                               'conditions'=>[
																			  'kiosk_id IN'=>$kiosks_list,
																			  'id IN'=>$trnsfrdByKioskOrderIds],
                                                               //'recursive'=>-1
                                                              ]
                                                       );
		}else{
			   $checkIfBelong2Kiosk = $KioskOrdersTable->find('list',[
                                                               'keyField' => 'id',
                                                               'valueField' => 'kiosk_id',
                                                               'conditions'=>['kiosk_id'=>$selectedKiosk, 'id IN'=>$trnsfrdByKioskOrderIds],
                                                               //'recursive'=>-1
                                                              ]
                                                       ); 
		}
		
        if(!empty($checkIfBelong2Kiosk)){
            $checkIfBelong2Kiosk = $checkIfBelong2Kiosk->toArray();
        }else{
            $checkIfBelong2Kiosk = array();
        }
		if(empty($checkIfBelong2Kiosk)){
            $checkIfBelong2Kiosk = array(0 => null);
        }
		$transferredByKiosk = $StockTransfersTable->find('all',array('conditions'=>array('kiosk_order_id IN'=>array_keys($checkIfBelong2Kiosk)),'fields'=>array('product_id','sale_price','cost_price','quantity','created','kiosk_order_id')));
		$transferredByKiosk = $transferredByKiosk->hydrate(false);
        if(!empty($transferredByKiosk)){
            $transferredByKiosk = $transferredByKiosk->toArray();
        }else{
            $transferredByKiosk = array();
        }
		
		$productIds = array();
		if(!empty($transferredByKiosk)){
			foreach($transferredByKiosk as $key=>$byKiosk){
				$productIds[$byKiosk['product_id']] = $byKiosk['product_id'];
			}
		}
		if(empty($productIds)){
            array(0 => null);
        }
		$productDetail = array();
		if(!empty($productIds)){
			$productDetail = $this->Products->find('all',array('conditions'=>array('Products.id IN'=>$productIds),'fields'=>array('id','product_code','product','cost_price')));
            $productDetail = $productDetail->hydrate(false);
            if(!empty($productDetail)){
                $productDetail = $productDetail->toArray();
            }else{
                $productDetail = array();
            }
		}
		
		$productArr = array();
		if(!empty($productDetail)){
			foreach($productDetail as $key=>$productInfo){
				$productArr[$productInfo['id']] = $productInfo;
			}
		}
		
		$kiosks = $this->Kiosks->find('list',[
                                                'keyField' => 'id',
                                                'valueField' => 'name',
                                                'conditions' => ['Kiosks.status' => 1],
                                                'order' => ['Kiosks.name asc']
                                                //'recursive' => -1
                                            ]
                                    );
		if(!empty($kiosks)){
            $kiosks = $kiosks->toArray();
        }else{
            $kiosks = array();
        }
		$product_cats = $this->Products->find("list",[
											   'keyField' => "id",
											   'valueField' => "category_id",
											   ])->toArray();
		  $cat_name = $this->Categories->find("list",[
													  'keyField' => 'id',
													  'valueField' => 'category',
													  ])->toArray();
		$final_arr_res = array();
		$kskProductCode = $kskProduct = '--';
		$kskCostPrice = $sumCostPrice = $totalCostPrice = 0;
		
		 $main_site_db = Configure::read('MAIN_SITE_DB');
		$conn = ConnectionManager::get($main_site_db);
		$query = "SELECT `id`,`cost_price` FROM `products`";
	    $stmt = $conn->execute($query);
	    $query_res = $stmt->fetchAll('assoc');
		$hp_cost_arr = array();
		if(!empty($query_res)){
		  foreach($query_res as $k => $v){
			   $hp_cost_arr[$v["id"]] = $v["cost_price"];
		  }
		}
		$startDate = "'".date("Y-m-d",strtotime($startDate))."'";
		$endDate = "'".date("Y-m-d",strtotime($endDate))."'";
		$daily_stock_query = "SELECT `product_id`,`cost_price`,`created` FROM `daily_stocks` WHERE  DATE(`created`) > $startDate AND DATE(`created`) < $endDate";
	    $stmt = $conn->execute($daily_stock_query);
	    $daily_stock_query_res = $stmt->fetchAll('assoc');
		$created_product_cst_arr = array();
		if(!empty($daily_stock_query_res)){
		  foreach($daily_stock_query_res as $daily_key => $daily_val){
			   $created_product_cst_arr[date("Y-m-d",strtotime($daily_val['created']))][$daily_val['product_id']] = $daily_val['cost_price'];
		  }
		}
		
		
		$conn = ConnectionManager::get("default");
		
		
		foreach($transferredByKiosk as $key=>$kioskData){
            //pr($kioskData);die;
			$product_cat_name=  $cat_name[$product_cats[$kioskData['product_id']]];
			if($kioskData['quantity']>0){
			if(array_key_exists($kioskData['product_id'],$productArr)){
				$kskProductCode = $productArr[$kioskData['product_id']]['product_code'];
				$kskProduct = $productArr[$kioskData['product_id']]['product'];
				
                    $kskCostPrice = $kioskData['cost_price'];
					$sumCostPrice = $kioskData['quantity']*$kioskData['cost_price'];;
				
				$totalCostPrice+=$sumCostPrice;
			}	
		 }
		 
		  $final_arr_res[$key]["Product_id"] = $kioskData['product_id'];
		  $final_arr_res[$key]["Product Code"] = $kskProductCode;
		  $final_arr_res[$key]["Product"] = $kskProduct;
		  $final_arr_res[$key]["Category"] = $product_cat_name;
		  if($selectedKiosk == ""){
			   if(array_key_exists($kioskData['kiosk_order_id'],$transferredByKiosk)){
					$final_arr_res[$key]["Kiosk"] = $kiosks[$checkIfBelong2Kiosk[$kioskData['kiosk_order_id']]];
			   }else{
					$final_arr_res[$key]["Kiosk"] = "--";
			   }
		  }else{
			   
		  }
		  $final_arr_res[$key]["Cost Price"] = $kskCostPrice;
		  if(!$adminSite){
					
					$loggedInUser =  $this->request->session()->read('Auth.User.username');
					if (!preg_match('/'.QUOT_USER_PREFIX.'/',$loggedInUser)){
						
					}else{
						 $created = date("Y-m-d",strtotime($kioskData['created']));
						 $p_id = $kioskData['product_id'];
						 
						 $cost_price_temp = "";
						 if(array_key_exists($created,$created_product_cst_arr)){
							  if(array_key_exists($p_id,$created_product_cst_arr[$created])){
								   $cost_price_temp = $created_product_cst_arr[$created][$p_id];
							  }
						 }
					
				   
						 if(!empty($cost_price_temp)){
							   $cost_price = $cost_price_temp;
						 }else{
							  $cost_price = $hp_cost_arr[$p_id]; 
						 }
						 $final_arr_res[$key]['Wharehouse Cost Price'] = $cost_price;
					}
					$conn = ConnectionManager::get("default");
		  }
		  $final_arr_res[$key]["Quantity"] = $kioskData['quantity'];
		  $final_arr_res[$key]["Amount"] = number_format($sumCostPrice,2);
		  $final_arr_res[$key]["Return Date"] = date('d-m-Y h:i A',strtotime($kioskData['created']));
		}
		
		$this->outputCsv('Return_data'.time().".csv" ,$final_arr_res);
		$this->autoRender = false;
		  	
	}
	
	public function totalRepairCostExport(){
	 
		  $path = realpath(dirname(__FILE__));
		  if (strpos($path,ADMIN_DOMAIN) !== false) {
			 $adminSite = true;  
		  }else{
			   $adminSite = false;  
		  }
	 
		//pr($this->request->query);die;
		$problemTypeOptions = $this->ProblemTypes->find('list',[
                                                                'keyField' => 'id',
                                                                'valueField' => 'problem_type'
                                                                //'fields' => array('id', 'problem_type')
                                                               ]
                                                        );
        if(!empty($problemTypeOptions)){
            $problemTypeOptions = $problemTypeOptions->toArray();
        }else{
            $problemTypeOptions = array();
        }
		$selectedKiosk = '';
		if(!empty($this->request->query['kiosk'])){
			$selectedKiosk = $this->request->query['kiosk'];
		}
		
		$from_date = '';
		$startDate = '';
		if(!empty($this->request->query['from_date'])){
			$from_date = $this->request->query['from_date'];
			$startDate = date("Y-m-d",strtotime($from_date.'-1 day'));
		}
		
		$to_date = '';
		$endDate = '';
		if(!empty($this->request->query['to_date'])){
			$to_date = $this->request->query['to_date'];
			$endDate = date("Y-m-d",strtotime($to_date.'+1 day'));
		}
		
		
		$repairIdsArray = array();
		if($selectedKiosk == ""){
		  
		  $kiosks_list = $this->Kiosks->find("list",[
													   'conditions' => [
														 'status' => 1,   
													   ],
													   'keyField' => "id",
													   'valueField' => "id",
													])->toArray();
			   if(empty($kiosks_list)){
					$kiosks_list = array(0 => null);
			   }
		  
		  $repairIdsData = $this->MobileRepairLogs->find('list',[
                                                               'conditions' =>
												  array('MobileRepairLogs.repair_status IN' => DISPATCHED_2_KIOSK_REPAIRED,
															"DATE(MobileRepairLogs.created)>'$startDate'",
															"DATE(MobileRepairLogs.created)<'$endDate'",
															"kiosk_id IN" => $kiosks_list
															),
                                                               'keyField' => 'mobile_repair_id',
                                                               'valueField' => 'created'
                                                              ]
                                                       );  
		}else{
		  $repairIdsData = $this->MobileRepairLogs->find('list',[
                                                               'conditions' => array('MobileRepairLogs.repair_status IN' => DISPATCHED_2_KIOSK_REPAIRED, "DATE(MobileRepairLogs.created)>'$startDate'","DATE(MobileRepairLogs.created)<'$endDate'","kiosk_id" => (int)$selectedKiosk),
                                                               'keyField' => 'mobile_repair_id',
                                                               'valueField' => 'created'
                                                              ]
                                                       );
		}
		
        if(!empty($repairIdsData)){
            $repairIdsData = $repairIdsData->toArray();
        }else{
            $repairIdsData = array();
        }
		$repairIds = array_keys($repairIdsData);
                
		$repairIdsArray = $repairIds;
		
        $repair_data = array();
        $repairdata = array();        
		$repairAttr = array();
		$repairParts = array();
		$repairDetail = array();
        if(empty($repairIdsArray)){
            $repairIdsArray = array(0 => null);
        }
		if(!empty($repairIdsArray)){
			$repair_data = $repairDetail = $repairAttr = $this->MobileRepairs->find('all',array('conditions'=>array('MobileRepairs.id IN'=>$repairIdsArray),'fields'=>array('id','brand_id','kiosk_id','mobile_model_id','problem_type','net_cost','created')));
            $repair_data = $repair_data->hydrate(false);
            if(!empty($repair_data)){
                $repair_data = $repair_data->toArray();
            }else{
                $repair_data = array();
            }
			$repairParts = $this->MobileRepairParts->find('all',array('conditions'=>array('MobileRepairParts.mobile_repair_id IN'=>$repairIdsArray)));
            $repairParts = $repairParts->hydrate(false);
            if(!empty($repairParts)){
                $repairParts = $repairParts->toArray();
            }else{
                $repairParts = array();
            }

		}
                
                if(count($repairAttr)){
                    foreach($repairAttr as $rd => $repair_info){
                        $repairdata[$repair_info['id']] = $repair_info;
                    }
                }
		
		$repairPartList = array();
		
		if(count($repairParts)){
			foreach($repairParts as $key=>$repairPartsInfo){
				$repairPartList[$repairPartsInfo['mobile_repair_id']][]=$repairPartsInfo['product_id'];
			}
		}
		$productIds = array();
		
		foreach($repairPartList as $repairId=>$productDetail){
			foreach($productDetail as $key=>$productInfo){
				$productIds[$productInfo]=$productInfo;
			}
		}
		if(empty($productIds)){
            $productIds = array(0 => null);
        }
		$productDetail = array();
		if(!empty($productIds)){
			$productDetail = $this->Products->find('all',array('conditions'=>array('Products.id IN'=>$productIds),'fields'=>array('id','product_code','product','cost_price')));
            $productDetail = $productDetail->hydrate(false);
            if(!empty($productDetail)){
                $productDetail = $productDetail->toArray();
            }else{
                $productDetail = array();
            }
		}
		
		$productArr = array();
		if(!empty($productDetail)){
			foreach($productDetail as $key=>$productInfo){
				$productArr[$productInfo['id']] = $productInfo;
			}
		}
		
		//getting cost price corresponding to the brand,model,problem combination
		$repairCostArr = array();
		$mobile_model_ids = array();//rajju
		$brand_ids = array();//rajju
		if(!empty($repairAttr)){
			foreach($repairAttr as $key=>$repairInfo){
				$repairId = $repairInfo['id'];
				 $brand_id = $repairInfo['brand_id'];
				 $mobile_model_id = $repairInfo['mobile_model_id'];
				$brand_ids[$repairInfo['brand_id']] = $repairInfo['brand_id'];//rajju
				$mobile_model_ids[$repairInfo['mobile_model_id']] = $repairInfo['mobile_model_id'];//rajju
				$problemTypeArr = explode('|',$repairInfo['problem_type']);
				if(empty($problemTypeArr)){
                    $problemTypeArr = array(0 => null);
                }
				$repairCostArr_query = $this->MobileRepairPrices->find('all',array('conditions'=>array('MobileRepairPrices.brand_id'=>$brand_id,'MobileRepairPrices.mobile_model_id'=>$mobile_model_id,'MobileRepairPrices.problem_type IN'=>$problemTypeArr),'fields'=>array('MobileRepairPrices.repair_cost')));
                $repairCostArr_query = $repairCostArr_query->hydrate(false);
                if(!empty($repairCostArr_query)){
                    $repairCostArr[$repairId] = $repairCostArr_query->toArray();
                }else{
                    $repairCostArr[$repairId] = array();
                }
			}
		}
        if(empty($mobile_model_ids)){
            $mobile_model_ids = array(0 => null);
        }
		$mobileModels_query = $this->MobileModels->find('list',[
                                                          'conditions' => ['MobileModels.id IN' => $mobile_model_ids],
                                                          'keyField' => 'id',
                                                          'valueField' => 'model'
                                                         ]
                                                );
        if(!empty($mobileModels_query)){
            $mobileModels = $mobileModels_query->toArray();
        }else{
            $mobileModels = array();
        }
        if(empty($brand_ids)){
            $brand_ids = array(0 => null);
        }
		$brands = $this->Brands->find('list',[
                                              'conditions' => ['Brands.id IN' => $brand_ids],
                                              'keyField' => 'id',
                                              'valueField' => 'brand'
                                             ]
                                    );
        if(!empty($brands)){
            $brands = $brands->toArray();
        }else{
            $brands = array();
        }
		$kiosks_query = $this->Kiosks->find('list',[
                                                'keyField' => 'id',
                                                'valueField' => 'name',
                        						'conditions' => ['Kiosks.status' => 1],
                        						'order' => ['Kiosks.name asc']
                        						//'recursive' => -1
                                            ]
                                    );
        if(!empty($kiosks_query)){
            $kiosks = $kiosks_query->toArray();
        }else{
            $kiosks = array();
        }
		
		  $final_arr_res = array();
		$totalRepairCost = 0;
		$status = array('0'=>'No','1'=>'Yes');
		foreach($repairDetail as $key=>$repairInfo){
			$repairCost = 0;
//pr($repairInfo);die;
            $problemType1 = $problemType2 = $problemType3 = "--";
			$problemTypeArr = explode("|",$repairdata[$repairInfo['id']]['problem_type']);
			//pr($problemTypeArr);
			 
				if(array_key_exists('0',$problemTypeArr)){
					if(array_key_exists($problemTypeArr[0],$problemTypeOptions)){
						$problemType1 = $problemTypeOptions[$problemTypeArr[0]];
					}else{
						$problemType1 = "--";
					}
					
				}
				if(array_key_exists('1',$problemTypeArr)){
                    if(array_key_exists($problemTypeArr[1],$problemTypeOptions)){
                        $problemType2 = $problemTypeOptions[$problemTypeArr[1]];
                    }
				}
				if(array_key_exists('2',$problemTypeArr)){
					if(array_key_exists($problemTypeArr[2],$problemTypeOptions)){
						$problemType3 = $problemTypeOptions[$problemTypeArr[2]];
					}
				}
			 
			
			
				$repairCost = $repairInfo['net_cost'];
			
			$totalRepairCost+=$repairCost;
			
			
		  $final_arr_res[$key]["Repair Id"] = $repairInfo['id'];
		  $final_arr_res[$key]["Kiosk"] = $kiosks[$repairInfo['kiosk_id']];
		  $final_arr_res[$key]["Brand"] = $brands[$repairInfo['brand_id']];
		  $final_arr_res[$key]["Model"] = $mobileModels[$repairInfo['mobile_model_id']];
		  $final_arr_res[$key]["Problem Type 1"] = $problemType1;
		  $final_arr_res[$key]["Problem Type 2"] = $problemType2;
		  $final_arr_res[$key]["Problem Type 3"] = $problemType3;
		  $final_arr_res[$key]['Cost Price'] = $repairCost;
		  $final_arr_res[$key]["Repair Date"] = date('d-m-Y h:i A',strtotime($repairIdsData[$repairInfo['id']]));
		
		}
		  
		$this->outputCsv('Repair_'.time().".csv" ,$final_arr_res);
		$this->autoRender = false;
	}
	
	
	public function totalUnlockCostExport(){
	 
		$path = realpath(dirname(__FILE__));
		  if (strpos($path,ADMIN_DOMAIN) !== false) {
			 $adminSite = true;  
		  }else{
			   $adminSite = false;  
		  }  
		  
        $Unlockarr = array();
        $network_ids = array();
		$selectedKiosk = '';
		if(!empty($this->request->query['kiosk'])){
			$selectedKiosk = $this->request->query['kiosk'];
		}
		
		$from_date = '';
		$startDate = '';
		if(!empty($this->request->query['from_date'])){
			$from_date = $this->request->query['from_date'];
			$startDate = date("Y-m-d",strtotime($from_date.'-1 day'));
		}
		
		$to_date = '';
		$endDate = '';
		if(!empty($this->request->query['to_date'])){
			$to_date = $this->request->query['to_date'];
			$endDate = date("Y-m-d",strtotime($to_date.'+1 day'));
		}
		
		$unlockIdsDetail = array();
		$unlockIdsArray = array();
		//----------------------------------------------------
		if($selectedKiosk == ""){
		  $kiosks_list = $this->Kiosks->find("list",[
													   'conditions' => [
														 'status' => 1,   
													   ],
													   'keyField' => "id",
													   'valueField' => "id",
													])->toArray();
			   if(empty($kiosks_list)){
					$kiosks_list = array(0 => null);
			   }
		  
		  $unlockIds_query = $this->MobileUnlockLogs->find('list',[
                                                           'conditions' => array(
								'MobileUnlockLogs.unlock_status IN' => array(UNLOCK_PROCESSED_CONFIRMATION_SENT_2_KIOSK, DISPATCHED_2_KIOSK_UNLOCKED),
								"DATE(MobileUnlockLogs.created)>'$startDate'",
								"DATE(MobileUnlockLogs.created) < '$endDate'",
								"kiosk_id IN" => $kiosks_list,
								),                       
                                                            'keyField' => 'id',
                                                            'valueField' => 'mobile_unlock_id'
                                                          ]
                                                   ); 
		}else{
		  $unlockIds_query = $this->MobileUnlockLogs->find('list',[
                                                           'conditions' => array(
								'MobileUnlockLogs.unlock_status IN' => array(UNLOCK_PROCESSED_CONFIRMATION_SENT_2_KIOSK, DISPATCHED_2_KIOSK_UNLOCKED),
								"DATE(MobileUnlockLogs.created)>'$startDate'",
								"DATE(MobileUnlockLogs.created) < '$endDate'",
								"kiosk_id" => (int)$selectedKiosk,
								),                       
                                                            'keyField' => 'id',
                                                            'valueField' => 'mobile_unlock_id'
                                                          ]
                                                   );
		}
		
        if(!empty($unlockIds_query)){
            $unlockIds = $unlockIds_query->toArray();
        }else{
            $unlockIds = array();
        }
		$unlockIdsArray = $unlockIds = array_unique($unlockIds);
		
		$unlockDetail = array();
		if(count($unlockIds)){
            if(empty($unlockIds)){
                $unlockIds = array(0 => null);
            }
			$unlockDetail_query = $this->MobileUnlocks->find('all',array('conditions' => array('MobileUnlocks.id IN' => $unlockIds),'fields' => array('id','brand_id', 'mobile_model_id', 'network_id','net_cost')));
            $unlockDetail_query = $unlockDetail_query->hydrate(false);
            if(!empty($unlockDetail_query)){
                $unlockDetail = $unlockDetail_query->toArray();
            }else{
                $unlockDetail = array();
            }
		}
		
		$unlockCostArr = array();
		$totalUnlockCost = 0;
		if(!empty($unlockDetail)){
			foreach($unlockDetail as $key => $unlockInfo){
				$unlockId = $unlockInfo['id'];
				$brand_id = $unlockInfo['brand_id'];
				$mobile_model_id = $unlockInfo['mobile_model_id'];
				$network_id = $unlockInfo['network_id'];
				
				$tPrice = $this->MobileUnlockPrices->find('all', array('conditions' => array('MobileUnlockPrices.brand_id' => $brand_id, 'MobileUnlockPrices.mobile_model_id' => $mobile_model_id,'MobileUnlockPrices.network_id' => $network_id),'fields'=>array('MobileUnlockPrices.unlocking_cost')));
                $tPrice = $tPrice->hydrate(false);
                if(!empty($tPrice)){
                    $tPrice = $tPrice->toArray();
                }else{
                    $tPrice = array();
                }
                if(!empty($tPrice)){
                    $unlockCostArr[$unlockId] = $tPrice;
                    $sumUnlockCost = $totalUnlockCost += $tPrice[0]['unlocking_cost'];
                }
			}
		}
		//pr($unlockCostArr);
		//pr($totalUnlockCost);
		$sumUnlockCost = 0;
		$sumRefundCost = 0;
		$unlockIdsArray = array_unique($unlockIdsArray);
		
		
		$resultUnlock = array();
		if(!empty($unlockIdsArray)){
			foreach($unlockIdsArray as $key=>$unlockIdIn){
				$resultUnlock[$unlockIdIn]=$unlockIdIn;
			}
		}
		//***
		$unlockDetail = array();
		if(!empty($unlockIdsArray)){
            if(empty($resultUnlock)){
                $resultUnlock = array(0 => null);
            }
			$unlockDetail_query = $this->MobileUnlocks->find('all',array('conditions'=>array('MobileUnlocks.id IN'=>$resultUnlock),'fields'=>array('id','brand_id','mobile_model_id','network_id','net_cost')));
            $unlockDetail_query = $unlockDetail_query->hydrate(false);
            if(!empty($unlockDetail_query)){
                $unlockDetail = $unlockDetail_query->toArray();
            }else{
                $unlockDetail = array();
            }
		}
		
		$unlockCostArr = array();
		//pr($unlockDetail);die;
		if(!empty($unlockDetail)){
			foreach($unlockDetail as $key => $unlockInfo){
				$unlockId = $unlockInfo['id'];
				$brand_id = $unlockInfo['brand_id'];
				$mobile_model_id = $unlockInfo['mobile_model_id'];
				$network_id = $unlockInfo['network_id'];
				
				$unlockCostArr_query = $this->MobileUnlockPrices->find('all',array('conditions' => array('MobileUnlockPrices.brand_id'=>$brand_id,'MobileUnlockPrices.mobile_model_id'=>$mobile_model_id,'MobileUnlockPrices.network_id'=>$network_id),'fields'=>array('MobileUnlockPrices.unlocking_cost')));
                $unlockCostArr_query = $unlockCostArr_query->first();
                if(!empty($unlockCostArr_query)){
                    $unlockCostArr[$unlockId] = $unlockCostArr_query->toArray();
                }else{
                    $unlockCostArr[$unlockId] = array();
                }
			}
		}
		$unlockDetail = array();
		
		if(!empty($resultUnlock)){
            if(empty($unlockIdsArray)){
                $unlockIdsArray = array(0 => null);
            }
			$unlockDetail_query = $this->MobileUnlocks->find('all',array('conditions'=>array('MobileUnlocks.id IN'=>$unlockIdsArray),'fields'=>array('id','brand_id','mobile_model_id','network_id','net_cost','created','kiosk_id')));
            $unlockDetail_query = $unlockDetail_query->hydrate(false);
            if(!empty($unlockDetail_query)){
                $unlockDetail = $unlockDetail_query->toArray();
            }else{
                $unlockDetail = array();
            }
		}
		if(count($unlockDetail)){
			foreach($unlockDetail as $ud => $unlock_det){
                //pr($unlock_det);die; 
                //pr($unlock_det['id']);
                //pr($unlockCostArr);die;
                if(array_key_exists('unlocking_cost',$unlockCostArr[$unlock_det['id']])){
                    $unlock_det['unlocking_cost'] = $unlockCostArr[$unlock_det['id']]['unlocking_cost'];
                }
				$Unlockarr[$unlock_det['id']] = $unlock_det;
			}
		}
		
		$unlockIdsDetail = $Unlockarr;
		
		
		$resultUnlock = array();
		
		//-------------------------
		
		$unlockCostArr = array();
		$mobile_model_ids = array();
		$brand_ids = array();
		//pr($unlockDetail);
		if(!empty($unlockDetail)){
			foreach($unlockDetail as $key=>$unlockInfo){
				$unlockId = $unlockInfo['id'];
				$brand_id = $unlockInfo['brand_id'];
				$mobile_model_id = $unlockInfo['mobile_model_id'];
				$network_id = $unlockInfo['network_id'];
				$brand_ids[$unlockInfo['brand_id']] = $unlockInfo['brand_id'];
				$mobile_model_ids[$unlockInfo['mobile_model_id']] = $unlockInfo['mobile_model_id'];
				$network_ids[$unlockInfo['network_id']] = $unlockInfo['network_id'];
				$unlockCostArr_query = $this->MobileUnlockPrices->find('all',array('conditions'=>array('MobileUnlockPrices.brand_id'=>$brand_id,'MobileUnlockPrices.mobile_model_id'=>$mobile_model_id,'MobileUnlockPrices.network_id'=>$network_id),'fields'=>array('MobileUnlockPrices.unlocking_cost')));
                $unlockCostArr_query = $unlockCostArr_query->first();
                if(!empty($unlockCostArr_query)){
                    $unlockCostArr[$unlockId] = $unlockCostArr_query->toArray();
                }else{
                    $unlockCostArr[$unlockId] = array();
                }
			}
		}
		
		$kiosks_query = $this->Kiosks->find('list',[
                                                'keyField' => 'id',
                                                'valueField' => 'name',
                                                'conditions' => ['Kiosks.status' => 1],
                                                'order' => ['Kiosks.name asc']
                                                //'recursive' => -1
                                            ]
                                    );
        if(!empty($kiosks_query)){
            $kiosks = $kiosks_query->toArray();
        }else{
            $kiosks = array();
        }
        if(empty($network_ids)){
            $network_ids = array(0 => null);
        }
		$networks_query = $this->Networks->find('list',[
                                                  'conditions' => ['Networks.id IN' => $network_ids],
                                                  'keyField' => 'id',
                                                  'valueField' => 'name'
                                                 ]
                                        );
        if(!empty($networks_query)){
            $networks = $networks_query->toArray();
        }else{
            $networks = array();
        }
		//pr($networks);
        if(empty($mobile_model_ids)){
            $mobile_model_ids = array(0 => null);
        }
		$mobileModels_query = $this->MobileModels->find('list',[
                                                          'conditions' => ['MobileModels.id IN' => $mobile_model_ids],
                                                          'keyField' => 'id',
                                                          'valueField' => 'model'
                                                         ]
                                                  );
        if(!empty($mobileModels_query)){
            $mobileModels = $mobileModels_query->toArray();
        }else{
            $mobileModels = array();
        }
        if(empty($brand_ids)){
            $brand_ids = array(0 => null);
        }
		$brands = $this->Brands->find('list',[
                                              'conditions' => ['Brands.id IN' => $brand_ids],
                                              'keyField' => 'id',
                                              'valueField' => 'brand'
                                             ]
                                    );
        if(!empty($brands)){
            $brands = $brands->toArray();
        }else{
            $brands = array();
        }
		$final_arr_res = array();
		$status = array('0'=>'No','1'=>'Yes');
		  foreach($unlockIdsDetail as $unlockID => $unlockInfo){
			   $costPrice = 0;
			   $id = $unlockInfo['id'];
					  if(count($unlockInfo)){
							  $costPrice = floatval($unlockInfo['net_cost']);
					  }
			   $totalUnlockCost+=$costPrice;
            
			   $final_arr_res[$unlockID]["Unlock Id"] = $unlockInfo['id'];
			   if(array_key_exists($unlockInfo['kiosk_id'],$kiosks)){
					$final_arr_res[$unlockID]["Kiosk"] = $kiosks[$unlockInfo['kiosk_id']];
			   }else{
					$final_arr_res[$unlockID]["Kiosk"] = "--";
			   }
			   
			   if(array_key_exists($unlockInfo['id'],$Unlockarr)){
					 $brand = $brands[$Unlockarr[$unlockInfo['id']]['brand_id']];
			   }else{
					 $brand = '--';
			   } 
			   $final_arr_res[$unlockID]["Brand"] = $brand;
			   if(array_key_exists($unlockInfo['id'],$Unlockarr)){
					if(!empty($Unlockarr[$unlockInfo['id']]['mobile_model_id']) && array_key_exists($Unlockarr[$unlockInfo['id']]['mobile_model_id'],$mobileModels)){
						$mobile_model = $mobileModels[$Unlockarr[$unlockInfo['id']]['mobile_model_id']];
					}else{
					   $mobile_model = '--';
					}
			   }else{
				   $mobile_model = '--';
			   }
			   $final_arr_res[$unlockID]["Model"] = $mobile_model;
			   if(array_key_exists($unlockInfo['id'],$Unlockarr)){
					if(!empty($Unlockarr[$unlockInfo['id']]['network_id']) && array_key_exists($Unlockarr[$unlockInfo['id']]['network_id'],$networks)){
						$network = $networks[$Unlockarr[$unlockInfo['id']]['network_id']];
					 }else{
						$network = '--';
					 }
				}else{
					$network = '--';
				}
		  
			   $final_arr_res[$unlockID]["Network"] = $network;
			   $final_arr_res[$unlockID]['Cost Price'] = $costPrice;
			   $final_arr_res[$unlockID]["Created Date"] = date('d-m-Y h:i A',strtotime($unlockInfo['created']));
			
		  }
		  
		$this->outputCsv('Unlock_'.time().".csv" ,$final_arr_res);
		$this->autoRender = false;
	}
	
	
	public function totalBulkPhoneCostExport(){
		
		$path = realpath(dirname(__FILE__));
		  if (strpos($path,ADMIN_DOMAIN) !== false) {
			 $adminSite = true;  
		  }else{
			   $adminSite = false;  
		  }
		
		$selectedKiosk = '';
		if(!empty($this->request->query['kiosk'])){
			$selectedKiosk = $this->request->query['kiosk'];
		}
		
		$from_date = '';
		$startDate = '';
		if(!empty($this->request->query['from_date'])){
			$from_date = $this->request->query['from_date'];
			$startDate = date("Y-m-d",strtotime($from_date.'-1 day'));
		}
		
		$to_date = '';
		$endDate = '';
		if(!empty($this->request->query['to_date'])){
			$to_date = $this->request->query['to_date'];
			$endDate = date("Y-m-d",strtotime($to_date.'+1 day'));
		}
		
		$refunded = 0;
		if(!empty($this->request->query['refunded'])){
			$refunded = $this->request->query['refunded'];
		}
		
		$mobileResale = array();
		//getting purchase id of all the sold phones in this time period
		if($selectedKiosk == ""){
			   $kiosks_list = $this->Kiosks->find("list",[
													   'conditions' => [
														 'status' => 1,   
													   ],
													   'keyField' => "id",
													   'valueField' => "id",
													])->toArray();
			   if(empty($kiosks_list)){
					$kiosks_list = array(0 => null);
			   }
			   $mobileResale_query = $this->MobileBlkReSales->find('all',array('fields'=>array('MobileBlkReSales.id','MobileBlkReSales.mobile_purchase_id','MobileBlkReSales.refund_status','MobileBlkReSales.created','MobileBlkReSales.kiosk_id'),
																	  'conditions'=>array("DATE(MobileBlkReSales.created)>'$startDate'",
																						  "DATE(MobileBlkReSales.created)<'$endDate'",
																						  "MobileBlkReSales.kiosk_id IN" => $kiosks_list
																						  ),
																						  'order'=>'MobileBlkReSales.id DESC'));  
		}else{
			   $mobileResale_query = $this->MobileBlkReSales->find('all',array('fields'=>array('MobileBlkReSales.id','MobileBlkReSales.mobile_purchase_id','MobileBlkReSales.refund_status','MobileBlkReSales.created'),'conditions'=>array("DATE(MobileBlkReSales.created)>'$startDate'","DATE(MobileBlkReSales.created)<'$endDate'",'MobileBlkReSales.kiosk_id'=>$selectedKiosk),'order'=>'MobileBlkReSales.id DESC')); 
		}
		
		
		
		
        $mobileResale_query = $mobileResale_query->hydrate(false);
        if(!empty($mobileResale_query)){
            $mobileResale = $mobileResale_query->hydrate(false);
        }else{
            $mobileResale = array();
        }
		
		$returnPurchaseIds = array();
		$allPurchaseIds = array();
		
		$purchaseIds = array();
		if(!empty($mobileResale)){
			foreach($mobileResale as $key=>$mobileResaleData){
				$allPurchaseIds[] = $mobileResaleData['mobile_purchase_id'];
				if($mobileResaleData['refund_status']!=1){
					$purchaseIds[] = $mobileResaleData['mobile_purchase_id'];
				}
				if($mobileResaleData['refund_status']==1){
					$returnPurchaseIds[] = $mobileResaleData['mobile_purchase_id'];
				}
			}
		}
		if(empty($allPurchaseIds)){
            $allPurchaseIds = array(0 => null);
        }
		$purchaseCostList = array();
		$purchaseCostList_query = $this->MobilePurchases->find('all',array('fields'=>array('MobilePurchases.id','MobilePurchases.topedup_price','MobilePurchases.cost_price'),'conditions'=>array('MobilePurchases.id IN'=>$allPurchaseIds),'order'=>'MobilePurchases.id DESC'));
        $purchaseCostList_query = $purchaseCostList_query->hydrate(false);
        if(!empty($purchaseCostList_query)){
            $purchaseCostList = $purchaseCostList_query->toArray();
        }else{
            $purchaseCostList = array();
        }
		
		$costPrice = array();
		if(!empty($purchaseCostList)){
			foreach($purchaseCostList as $key=>$purchaseCostDetail){
				$topedup_price = $purchaseCostDetail['topedup_price'];
				$cost_price = $purchaseCostDetail['cost_price'];
				
				if($topedup_price>0){
					$finalPrice = $topedup_price;
				}else{
					$finalPrice = $cost_price;
				}
				$purchase_id = $purchaseCostDetail['id'];
				
				$costPrice[$purchase_id]=$finalPrice;
			}
		}
		
		$kiosks_query = $this->Kiosks->find('list',[
                                                'keyField' => 'id',
                                                'valueField' => 'name',
                                                'conditions' => ['Kiosks.status' => 1],
                                                'order' => ['Kiosks.name asc']
                                                //'recursive' => -1
                                            ]
                                    );
		if(!empty($kiosks_query)){
            $kiosks = $kiosks_query->toArray();
        }else{
            $kiosks = array();
        }
		
		$final_arr_res = array();
		 
		$totalMobileCost = 0;
                $less = 0;
                $add = 0;
		  foreach($mobileResale as $key=>$mobileData){
			  if($mobileData['id'] == 0){
				  continue;
			  }
			  if($mobileData['mobile_purchase_id'] == 0){
				  continue;
			  }
			  if($mobileData['refund_status']>0){
				  $refund = "Yes";
				  if($refunded == 1){
					continue;
				  }
			  }else{
				  $refund = "No";
				  if($refunded == 0){
					continue;
				  }
			  }
			  
			$final_arr_res[$key]["Resale Id"] = $mobileData['id'];
			$final_arr_res[$key]["Purchase Id"] = $mobileData['mobile_purchase_id'];
			if($selectedKiosk == ""){
			   if($mobileData['kiosk_id'] == 0){
					$mobileData['kiosk_id'] = 10000;
			   }
			   $final_arr_res[$key]["Kiosk"] = $kiosks[$mobileData['kiosk_id']];
			}
			
			
			$final_arr_res[$key]["Refunded"] = $refund;
			if(array_key_exists($mobileData['mobile_purchase_id'],$costPrice)){
				 $final_arr_res[$key]["Cost Price"] = $costPrice[$mobileData['mobile_purchase_id']];
			}else{
				 echo "--";
				 $final_arr_res[$key]["Cost Price"] = "--";
			}
			$final_arr_res[$key]["Date"] = date('d-m-Y h:i A',strtotime($mobileData['created']));
		  }	
		  $this->outputCsv('bulk_phone_'.time().".csv" ,$final_arr_res);
		  $this->autoRender = false;
	}
	
	
	 public function totalPhoneCostExport(){
		  $path = realpath(dirname(__FILE__));
		  if (strpos($path,ADMIN_DOMAIN) !== false) {
			 $adminSite = true;  
		  }else{
			   $adminSite = false;  
		  }
		  
		$selectedKiosk = '';
		if(!empty($this->request->query['kiosk'])){
			$selectedKiosk = $this->request->query['kiosk'];
		}
		
		$from_date = '';
		$startDate = '';
		if(!empty($this->request->query['from_date'])){
			$from_date = $this->request->query['from_date'];
			$startDate = date("Y-m-d",strtotime($from_date.'-1 day'));
		}
		
		$to_date = '';
		$endDate = '';
		if(!empty($this->request->query['to_date'])){
			$to_date = $this->request->query['to_date'];
			$endDate = date("Y-m-d",strtotime($to_date.'+1 day'));
		}
		
		$refunded = 0;
		if(!empty($this->request->query['refunded'])){
			$refunded = $this->request->query['refunded'];
		}
		
		$mobileResale = array();
		//getting purchase id of all the sold phones in this time period
		
		
		if($selectedKiosk == ""){
		  $kiosks_list = $this->Kiosks->find("list",[
												  'conditions' => [
													'status' => 1,   
												  ],
												  'keyField' => "id",
												  'valueField' => "id",
											   ])->toArray();
		  if(empty($kiosks_list)){
			   $kiosks_list = array(0 => null);
		  }
			   $mobileResale = $this->MobileReSales->find('all',array('fields'=>array('MobileReSales.id','MobileReSales.mobile_purchase_id','MobileReSales.refund_status','MobileReSales.created',"MobileReSales.kiosk_id"),
																	  'conditions'=>array("DATE(MobileReSales.created)>'$startDate'",
																						  "DATE(MobileReSales.created)<'$endDate'",
																						  'MobileReSales.kiosk_id IN'=>	$kiosks_list	  
																						  ),
																	  'order'=>'MobileReSales.id DESC'));
		}else{
	 		  $mobileResale = $this->MobileReSales->find('all',array('fields'=>array('MobileReSales.id','MobileReSales.mobile_purchase_id','MobileReSales.refund_status','MobileReSales.created'),'conditions'=>array("DATE(MobileReSales.created)>'$startDate'","DATE(MobileReSales.created)<'$endDate'",'MobileReSales.kiosk_id'=>$selectedKiosk),'order'=>'MobileReSales.id DESC'));  
		}
		
        $mobileResale = $mobileResale->hydrate(false);
        if(!empty($mobileResale)){
            $mobileResale = $mobileResale->toArray();
        }else{
            $mobileResale = array();
        }
		
		$returnPurchaseIds = array();
		$allPurchaseIds = array();
		
		$purchaseIds = array();
		if(!empty($mobileResale)){
			foreach($mobileResale as $key=>$mobileResaleData){
				$allPurchaseIds[] = $mobileResaleData['mobile_purchase_id'];
				if($mobileResaleData['refund_status']!=1){
					$purchaseIds[] = $mobileResaleData['mobile_purchase_id'];
				}
				if($mobileResaleData['refund_status']==1){
					$returnPurchaseIds[] = $mobileResaleData['mobile_purchase_id'];
				}
			}
		}
		if(empty($allPurchaseIds)){
            $allPurchaseIds = array(0 => null);
        }
		$purchaseCostList = array();
		$purchaseCostList_query = $this->MobilePurchases->find('all',array('fields'=>array('MobilePurchases.id','MobilePurchases.topedup_price','MobilePurchases.cost_price'),'conditions'=>array('MobilePurchases.id IN'=>$allPurchaseIds),'order'=>'MobilePurchases.id DESC'));
        $purchaseCostList_query = $purchaseCostList_query->hydrate(false);
        if(!empty($purchaseCostList_query)){
            $purchaseCostList = $purchaseCostList_query->toArray();
        }else{
            $purchaseCostList = array();
        }
		
		$costPrice = array();
		if(!empty($purchaseCostList)){
			foreach($purchaseCostList as $key=>$purchaseCostDetail){
				$topedup_price = $purchaseCostDetail['topedup_price'];
				$cost_price = $purchaseCostDetail['cost_price'];
				
				if($topedup_price>0){
					$finalPrice = $topedup_price;
				}else{
					$finalPrice = $cost_price;
				}
				$purchase_id = $purchaseCostDetail['id'];
				
				$costPrice[$purchase_id]=$finalPrice;
			}
		}
		
		$kiosks = $this->Kiosks->find('list',[
                                                'keyField' => 'id',
                                                'valueField' => 'name',
                                                'conditions' => ['Kiosks.status' => 1],
                                                'order' => ['Kiosks.name asc']
                                                //'recursive' => -1
                                            ]
                                    );
		if(!empty($kiosks)){
            $kiosks = $kiosks->toArray();
        }else{
            $kiosks = array();
        }
		
		
		 $final_arr_res = array();
		 
		$totalMobileCost = 0;
                $less = 0;
                $add = 0;
				
		  foreach($mobileResale as $key=>$mobileData){
			  if($mobileData['id'] == 0){
				  continue;
			  }
			  if($mobileData['mobile_purchase_id'] == 0){
				  continue;
			  }
			  if($mobileData['refund_status']>0){
				  $refund = "Yes";
				  if($refunded == 1){
					continue;
				  }
			  }else{
				  $refund = "No";
				  if($refunded == 0){
					continue;
				  }
			  }
			  
			$final_arr_res[$key]["Resale Id"] = $mobileData['id'];
			$final_arr_res[$key]["Purchase Id"] = $mobileData['mobile_purchase_id'];
			
			if($selectedKiosk == ""){
			   if($mobileData['kiosk_id'] == 0){
					$mobileData['kiosk_id'] = 10000;
			   }
			   $final_arr_res[$key]["Kiosk"] = $kiosks[$mobileData['kiosk_id']];
			}
			
			$final_arr_res[$key]["Refunded"] = $refund;
			if(array_key_exists($mobileData['mobile_purchase_id'],$costPrice)){
				 $final_arr_res[$key]["Cost Price"] = $costPrice[$mobileData['mobile_purchase_id']];
			}else{
				 echo "--";
				 $final_arr_res[$key]["Cost Price"] = "--";
			}
			$final_arr_res[$key]["Date"] = date('d-m-Y h:i A',strtotime($mobileData['created']));
		  }	
		  $this->outputCsv('phone_'.time().".csv" ,$final_arr_res);
		  $this->autoRender = false;
	}
	
	
	public function checkPrice(){
		  if(array_key_exists('id',$this->request->query)){
			   $id = $this->request->query['id'];
		  }
		  
		  if(array_key_exists('price',$this->request->query)){
			   $price = $this->request->query['price'];
		  }
		  $msg = array("msg" => "");
		  if(!empty($id)){
			  $cost_price_arr = $this->Products->find("list",["conditions" => [
														 'id' => $id,
														 ],
										'keyField' => "id",
										'valueField' => "cost_price"
										])->toArray();
			  $cost_price = $cost_price_arr[$id];
			  if($price >= $cost_price){
					$msg = array("msg" => "ok");
			  }else{
					$msg = array("msg" => "error");
			  }
		  }
	 
		  echo json_encode($msg);die;
	}
	
	
	public function orderQtyUpdate(){
		  $qty = $this->request->query("qty");
		  $p_id = $this->request->query("product_id");
		  $row_id = $this->request->query("row_id");
		  $receiptTable_source = "stock_transfer_by_kiosk";
		  $StockTransferTable = TableRegistry::get($receiptTable_source,[
																  'table' => $receiptTable_source,
															  ]);
		  $row_entity = $StockTransferTable->get($row_id);
		  $data_to_save = array(
								"quantity" =>$qty,
								);
		  $row_entity = $StockTransferTable->patchEntity($row_entity,$data_to_save);
		  if($StockTransferTable->save($row_entity)){
			   $msg = array("msg" => "Quantity Updated");
		  }else{
			   $msg = array("msg" => "error");
		  }
		  echo json_encode($msg);die;
	}
}
?>