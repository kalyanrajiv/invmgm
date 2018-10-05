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



class MobileReSalesController extends AppController{
	
	 public $helpers = [
         'Paginator' => ['templates' => 'paginatortemplates']
         ];
        public function initialize()
        {
            parent::initialize();
            $this->loadModel('MobileModels');
            $this->loadModel('Brands');
            $this->loadModel('ProblemTypes');
			$this->loadModel('MobileReSales');
            $this->loadModel('MobileReSalePayments');
            $this->loadModel('Kiosks');
            $this->loadModel('Users');
            $this->loadModel('MobilePurchases');
			$this->loadModel('Networks');
            $this->loadModel('MobilePrices');
            $this->loadModel('MobileTransferLogs');
			$this->loadModel('RetailCustomers');
			$this->loadModel('MobileBlkReSalePayments');
			$this->loadModel('MobileBlkReSales');
			$this->loadModel('MobileBlkTransferLogs');
			$this->loadModel('Customers');
			
			
			
			$CURRENCY_TYPE = Configure::read('CURRENCY_TYPE');
			$this->set(compact('CURRENCY_TYPE'));
            //Configure::load('common-arrays');
            $this->fromemail = Configure::read('FROM_EMAIL');
            $gradeType = Configure::read('grade_type');
            $resaleOptions = Configure::read('resale_statuses');
            $countryOptions = Configure::read('uk_non_uk');
            $discountOptions = Configure::read('discount');
            $colorOptions = Configure::read('color');

		  $discountArr = [];
		  for($i = 0; $i <= 50; $i++){
			   if($i==0){
					$discountArr[0] = "None";
					continue;
			   }
            $discountArr[$i] = "$i %";
		  }
			$discountOptions = $discountArr;
			
			//pr($gradeType);die;
            $this->set(compact('resaleOptions','countryOptions','gradeType','colorOptions','discountOptions','discountOptions'));
        }
	
	public function index() {
	 
		$brands_query = $this->MobileReSales->Brands->find('list',[
                                                                'keyField' => 'id',
                                                                'valueField' => 'brand',
                                                                'conditions' => ['Brands.status' => 1]
                                                            ]
                                                    );
        $brands_query = $brands_query->hydrate(false);
        if(!empty($brands_query)){
            $brands = $brands_query->toArray();
        }else{
            $brands = array();
        }
		$vat = $this->setting['vat'];
		$currency = $this->setting['currency_symbol'];
		$kiosk_id = $this->request->Session()->read('kiosk_id');
		if($this->request->is('post')){
			if(!empty($this->request['data']['MobileResale']['kiosk_id'])){
				$kiosk_id = $this->request['data']['MobileResale']['kiosk_id'];
			}
		}
			
		if($kiosk_id > 0){
		 
			if($kiosk_id == 10000){
				$kiosk_id = 0;//when admin sells a product kiosk_id 0 goes to mobile resales table
			}
		 
			  $this->paginate = [
                                'conditions' => ['MobileReSales.kiosk_id'=>$kiosk_id,
                                                 'MobileReSales.refund_status IS NULL'],
								'order' => ['MobileReSales.id desc'],
								'limit' => ROWS_PER_PAGE,
								//'recursive' => -1
							  ];
			 
			   
			 
            $mobileSale_conn = ConnectionManager::get('default');
            $mobileSale_stmt = $mobileSale_conn->execute("SELECT `discounted_price`, `selling_price`, 
						    case when `discounted_price` is NULL OR `discounted_price` = 0 THEN 
						    `selling_price` 
						    ELSE 
						    `discounted_price` 
						    END 
						    as `mobileSale`
						    from `mobile_re_sales`as MobileReSale WHERE `refund_status` is NULL AND `kiosk_id` = $kiosk_id"); 
            $mobileSale = $mobileSale_stmt ->fetchAll('assoc');
						    //status 1 is for the refunded mobiles
			$totalMobileSale = 0;
			if(!empty($mobileSale)){
			    foreach($mobileSale as $key=>$data){
				$totalMobileSale+= $data['mobileSale'];
			    }  
			}
			
            $mobileRefund_conn = ConnectionManager::get('default');
            $mobileRefund_stmt = $mobileRefund_conn->execute("SELECT `refund_price`, `refund_gain`, 
						    SUM(`refund_price`)
						    as `totalRefund`,
						    SUM(`refund_gain`)
						    as `totalRefundGain`
						    from `mobile_re_sales`as MobileReSale WHERE `refund_status` = 1 AND `kiosk_id` = $kiosk_id"); 
            $mobileRefund = $mobileRefund_stmt ->fetchAll('assoc');
						    //status 1 is for the refunded mobiles
		}else{
			$this->MobileReSales->recursive = 0;
			if( true){
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
						 $manager_kiosk_ids = $managerKiosk;//die;
						// $manager_kiosk_id = implode('|', $manager_kiosk_ids);
						 $this->paginate = [
                                'conditions' => ['MobileReSales.kiosk_id IN'=>$manager_kiosk_ids,'MobileReSales.refund_status IS NULl'],
                        		'order' => ['MobileReSales.id desc'],
                            	'limit' => ROWS_PER_PAGE,
                            	//'recursive' => -1
							 ];
					}
			   }else{
					$managerKiosk  = array();
						 $this->paginate = [
                                'conditions' => ['MobileReSales.refund_status IS NULl'],
                        		'order' => ['MobileReSales.id desc'],
                            	'limit' => ROWS_PER_PAGE,
                            	//'recursive' => -1
							 ];
			   }
			}else{
			   $this->paginate = [
                                'conditions' => ['MobileReSales.refund_status IS NULl'],
                        		'order' => ['MobileReSales.id desc'],
                            	'limit' => ROWS_PER_PAGE,
                            	//'recursive' => -1
							 ];
			}
			
			
			//data for all mobiles excluding the new entries that get created on refund
            
            $mobileSale_conn = ConnectionManager::get('default');
            $mobileSale_stmt = $mobileSale_conn->execute("SELECT `discounted_price`, `selling_price`, 
						    case when `discounted_price` is NULL OR `discounted_price` = 0 THEN 
						    `selling_price` 
						    ELSE 
						    `discounted_price` 
						    END 
						    as `mobileSale`
						    from `mobile_re_sales`as MobileReSale WHERE `refund_status` is NULL"); 
            $mobileSale = $mobileSale_stmt ->fetchAll('assoc');
						    //status 1 is for the refunded mobiles
			$totalMobileSale = 0;
			if(!empty($mobileSale)){
			    foreach($mobileSale as $key=>$data){
				$totalMobileSale+= $data['mobileSale'];
			    }  
			}
			
			//data for new entries that get created on refund
            $mobileRefund_conn = ConnectionManager::get('default');
            $mobileRefund_stmt = $mobileRefund_conn->execute("SELECT `refund_price`, `refund_gain`, 
						    SUM(`refund_price`)
						    as `totalRefund`,
            			    SUM(`refund_gain`)
						    as `totalRefundGain`
						    from `mobile_re_sales`as MobileReSale WHERE `refund_status` = 1"); 
            $mobileRefund = $mobileRefund_stmt ->fetchAll('assoc');
						    //status 1 is for the refunded mobiles
		}
		
		$totalMobileRefund = 0;
        //pr($mobileRefund);die;
		if(!empty($mobileRefund[0]['totalRefund'])){
			$totalMobileRefund = $mobileRefund[0]['totalRefund'];
		}
		
		$totalRefundGain = 0;
		if(!empty($mobileRefund[0]['totalRefundGain'])){
			$totalRefundGain = $mobileRefund[0]['totalRefundGain'];
		}
			
		$grandNetSale = $totalMobileSale-$totalMobileRefund;
        //pr($this->paginate);
		$mobileReSales_query = $this->paginate('MobileReSales');
        if(!empty($mobileReSales_query)){
            $mobileReSales = $mobileReSales_query->toArray();
        }else{
            $mobileReSales = array();
        }
		//pr($mobileReSales);die;
		$this->set(compact('mobileReSales'));
		$mobileModelId = array();
		$saleIdArr = array();
		foreach($mobileReSales as $key=>$mobileSale){
            //pr($mobileSale);die;
			$saleIdArr[$mobileSale->id] = $mobileSale->id;
			$mobileModelId[$mobileSale->mobile_model_id] = $mobileSale->mobile_model_id;
		}
		
		//** getting payment details
		$paymentArr = array();
		$payment_amount_arr = array();
        if(empty($saleIdArr)){
            $saleIdArr = array(0 => null);
        }
		$mobileResalePayment_query = $this->MobileReSalePayments->find('all', array('conditions' => array('MobileReSalePayments.mobile_re_sale_id IN' => $saleIdArr)));
        $mobileResalePayment_query = $mobileResalePayment_query->hydrate(false);
        if(!empty($mobileResalePayment_query)){
            $mobileResalePayment = $mobileResalePayment_query->toArray();
        }else{
            $mobileResalePayment = array();
        }
		if(count($mobileResalePayment)){
			foreach($mobileResalePayment as $rp => $resalePayment){
                //pr($resalePayment);die;
				$mobilePurchaseID = $resalePayment['mobile_purchase_id'];
				$mobileReSaleID = $resalePayment['mobile_re_sale_id'];
				$paymentArr[$mobileReSaleID][] = $resalePayment;
				$pmtMethod = $resalePayment['payment_method'];
				$resalePmt = $resalePayment['amount'];
				//$paymentArr[$mobilePurchaseID][] = $resalePayment;
				
				if(array_key_exists($mobileReSaleID,$payment_amount_arr) && array_key_exists($pmtMethod,$payment_amount_arr[$mobileReSaleID])){
					$payment_amount_arr[$mobileReSaleID][$pmtMethod]+= $resalePmt;
				}else{
					$payment_amount_arr[$mobileReSaleID][$pmtMethod] = $resalePmt;
				}
			}
		}
		//code for payment ends here **
		if(empty($mobileModelId)){
            $mobileModelId = array(0 =>null);
        }
		$mobileModels_query = $this->MobileModels->find('all',array('conditions'=>array('MobileModels.id IN'=>$mobileModelId),'fields'=>array('id','model')));
        $mobileModels_query = $mobileModels_query->hydrate(false);
        if(!empty($mobileModels_query)){
            $mobileModels = $mobileModels_query->toArray();
        }else{
            $mobileModels = array();
        }
		foreach($mobileModels as $k=>$modelinfo){
			$modelName[$modelinfo['id']]=$modelinfo['model'];
		}
		$kiosks_query = $this->Kiosks->find('list',[
                                                        'keyField' => 'id',
                                                        'valueField' => 'name',
                                                        'conditions' => ['Kiosks.status' => 1],
                                                        'order' => 'Kiosks.name asc'
                                                    ]
                                            );
        $kiosks_query = $kiosks_query->hydrate(false);
        if(!empty($kiosks_query)){
            $kiosks = $kiosks_query->toArray();
        }else{
            $kiosks = array();
        }
		$users_query = $this->Users->find('list',[
                                                'keyField' => 'id',
                                                'valueField' => 'username'
                                           ]
                                    );
        $users_query = $users_query->hydrate(false);
        if(!empty($users_query)){
            $users = $users_query->toArray();
        }else{
            $users = array();
        }
		$this->set(compact('modelName','currency','vat','totalRefundGain','grandNetSale','kiosks','users','brands','paymentArr','payment_amount_arr'));
	}
    
    public function search($keyword = ""){
	  $external_sites = Configure::read('external_sites');
			   $path = dirname(__FILE__);
			   $ext_site = 0;
			   foreach($external_sites as $site_id => $site_name){
					$isboloRam = strpos($path,$site_name);
					if($isboloRam != false){
						$ext_site = 1;
					}
			   }
		//echo'hi';die;
		$mobileCostData = 0;
		$paymentMode = 'Multiple';
		$refundStatus =  NULL;
		if(array_key_exists('payment_mode',$this->request->query)){
			$paymentMode = $this->request->query['payment_mode'];
			if($paymentMode == 'refunded'){
				$refundStatus = 1;
			}
		}
		$brands_query = $this->MobileReSales->Brands->find('list',[
                                                                'keyField' => 'id',
                                                                'valueField' => 'brand',
                                                                'conditions' => ['Brands.status' => 1]
                                                            ]
                                                    );
        $brands_query = $brands_query->hydrate(false);
        if(!empty($brands_query)){
            $brands = $brands_query->toArray();
        }else{
            $brands = array();
        }
		$vat = $this->setting['vat'];
		$currency = $this->setting['currency_symbol'];
		$conditionArr = $this->generate_condition_array();
		$currency = $this->setting['currency_symbol'];
		$kioskid = $this->request->Session()->read('kiosk_id');
		if($kioskid>0){
           // echo "ss";die;
			   if($refundStatus == '' || $refundStatus == NULL){
				   $mobileSale_query = $this->MobileReSales->find('all', array( 
									   'conditions' => array('MobileReSales.kiosk_id'=>$kioskid,$conditionArr),
									   'contain' => array('Brands' , 'MobileModels')
										   ));
				   $mobileSale_query 
						  ->select(['mobileSale'=>'CASE WHEN MobileReSales.discounted_price is NULL THEN MobileReSales.selling_price ELSE MobileReSales.discounted_price END'])
						  ->select('MobileReSales.id')
						  ->select('MobileReSales.discounted_price')
						  ->select( 'MobileReSales.selling_price')
						 ->where(function ($exp, $q) {
										   return $exp->isNull('refund_status');
									   });
				   $mobileSale_query = $mobileSale_query->hydrate(false);
				   if(!empty($mobileSale_query)){
					   $mobileSale = $mobileSale_query->toArray();
				   }else{
					   $mobileSale = array();
				   }
			   }else{
					$mobileSale_query = $this->MobileReSales->find('all', array( 
										'conditions' => array('MobileReSales.refund_status'=>$refundStatus,'MobileReSales.kiosk_id'=>$kioskid,$conditionArr),
										'contain' => array('Brands' , 'MobileModels')
											));
					$mobileSale_query 
						   ->select(['mobileSale'=>'CASE WHEN MobileReSales.discounted_price is NULL THEN MobileReSales.selling_price ELSE MobileReSales.discounted_price END'])
						   ->select('MobileReSales.id')
						   ->select('MobileReSales.discounted_price')
						   ->select( 'MobileReSales.selling_price');
						   //pr($mobileSale_query);die;
					$mobileSale_query = $mobileSale_query->hydrate(false);
					if(!empty($mobileSale_query)){
						$mobileSale = $mobileSale_query->toArray();
					}else{
						$mobileSale = array();
					}
			   }
						 
			   $totalMobileSale = 0;
			   if(!empty($mobileSale)){
				   foreach($mobileSale as $key=>$data){
				   $totalMobileSale+= $data['mobileSale'];
				   }  
			   }
		
			   $mobileRefund_query = $this->MobileReSales->find('all',[
																	   'conditions' =>['MobileReSales.refund_status'=>1,'MobileReSales.kiosk_id'=>$kioskid,$conditionArr],
																	   'contain' => ['Brands' , 'MobileModels']
																	   ]
															   );
			   $mobileRefund_query
					   ->select('MobileReSales.refund_price')
					   ->select('MobileReSales.refund_gain')
					   ->select(['totalRefund' => $mobileRefund_query->func()->sum('MobileReSales.refund_price')])
					   ->select(['totalRefundGain' => $mobileRefund_query->func()->sum('MobileReSales.refund_gain')]);
			   $mobileRefund_query = $mobileRefund_query->hydrate(false);
			   if(!empty($mobileRefund_query)){
				   $mobileRefund = $mobileRefund_query->first();
			   }else{
				   $mobileRefund = array();
			   }
            
			
		  }else{
         // echo'hi';die;
			 $kioskId = $this->request->query['MobileResale']['kiosk_id'];
            //echo 'hi'.$refundStatus;die;
            //echo $kioskId;die;
            if($refundStatus == '' || $refundStatus == NULL){
                //echo'hi';die;
                $mobileSale_query = $this->MobileReSales->find('all', array( 
                    'conditions' => array($conditionArr),
                    'contain' => array('Brands' , 'MobileModels')
                        ));
                $mobileSale_query 
                       ->select(['mobileSale'=>'CASE WHEN MobileReSales.discounted_price is NULL THEN MobileReSales.selling_price ELSE MobileReSales.discounted_price END'])
                       ->select('MobileReSales.id')
                       ->select('MobileReSales.discounted_price')
                       ->select( 'MobileReSales.selling_price')
                       ->where(function ($exp, $q) {
                                        return $exp->isNull('refund_status');
                                    });
                       //pr($mobileSale_query);die;
                $mobileSale_query = $mobileSale_query->hydrate(false);
                if(!empty($mobileSale_query)){
                    $mobileSale = $mobileSale_query->toArray();
                }else{
                    $mobileSale = array();
                }    
            }else{
                //echo'bye';die;
                $mobileSale_query = $this->MobileReSales->find('all', array( 
                                    'conditions' => array('MobileReSales.refund_status'=>$refundStatus,$conditionArr),
                                    'contain' => array('Brands' , 'MobileModels')
                                        ));
                $mobileSale_query 
                       ->select(['mobileSale'=>'CASE WHEN MobileReSales.discounted_price is NULL THEN MobileReSales.selling_price ELSE MobileReSales.discounted_price END'])
                       ->select('MobileReSales.id')
                       ->select('MobileReSales.discounted_price')
                       ->select( 'MobileReSales.selling_price');
                       //pr($mobileSale_query);die;
                $mobileSale_query = $mobileSale_query->hydrate(false);
                if(!empty($mobileSale_query)){
                    $mobileSale = $mobileSale_query->toArray();
                }else{
                    $mobileSale = array();
                }
            }
            
			$totalMobileSale = 0;
			if(!empty($mobileSale)){
				foreach($mobileSale as $key=>$data){
				$totalMobileSale+= $data['mobileSale'];
				}  
			}
			
            $mobileRefund_query = $this->MobileReSales->find('all',[
                                                                    'conditions' =>['MobileReSales.refund_status'=>1,$conditionArr],
                                                                    'contain' => ['Brands' , 'MobileModels']
                                                                    ]
                                                            );
            $mobileRefund_query
                    ->select('MobileReSales.refund_price')
                    ->select('MobileReSales.refund_gain')
                    ->select(['totalRefund' => $mobileRefund_query->func()->sum('MobileReSales.refund_price')])
                    ->select(['totalRefundGain' => $mobileRefund_query->func()->sum('MobileReSales.refund_gain')]);
            $mobileRefund_query = $mobileRefund_query->hydrate(false);
            if(!empty($mobileRefund_query)){
                $mobileRefund = $mobileRefund_query->first();
            }else{
                $mobileRefund = array();
            }
            
		}
		$saleIds = array();
        //pr($mobileSale);die;
		foreach($mobileSale as $ms => $sale){
			$saleIds[$sale['id']] = $sale['id'];
		}
		
		$paymentSaleIds = $saleIds;
		//payment integration
		$payment_array = array();
		$payment_amount_arr = array();
		if($paymentMode == 'Cash' || $paymentMode == 'Card'){
			$mobileSale = array();
			$mobileRefund = array();
			if(empty($saleIds)){
                $saleIds = array(0 => null);
            }
            $searchPaymentResult_query = $this->MobileReSalePayments->find('all', array('conditions' => array('MobileReSalePayments.mobile_re_sale_id IN' => $saleIds, 'MobileReSalePayments.payment_method' => $paymentMode)));
            $searchPaymentResult_query = $searchPaymentResult_query->hydrate(false);
            if(!empty($searchPaymentResult_query)){
                $searchPaymentResult = $searchPaymentResult_query->toArray();
            }else{
                $searchPaymentResult = array();
            }
            //pr($searchPaymentResult);die;
			if(count($searchPaymentResult)){
				foreach($searchPaymentResult as $spr => $searchPaymentInfo){
					$payment_array[$searchPaymentInfo['mobile_re_sale_id']] = $searchPaymentInfo['mobile_re_sale_id'];

				}
                $mobileSale_query = $this->MobileReSales->find('all', array( 
								'conditions' => array('MobileReSales.id IN'=>$payment_array
									)));
                $mobileSale_query 
                       ->select('MobileReSales.id')
                       ->select('MobileReSales.discounted_price')
                       ->select('MobileReSales.selling_price')
                       ->select(['mobileSale'=>'CASE WHEN MobileReSales.discounted_price is NULL THEN MobileReSales.selling_price ELSE MobileReSales.discounted_price END']);
                       //pr($mobileSale_query);die;
                $mobileSale_query = $mobileSale_query->hydrate(false);
                if(!empty($mobileSale_query)){
                    $mobileSale = $mobileSale_query->toArray();
                }else{
                    $mobileSale = array();
                }
                //pr($mobileSale);die;
                
                $mobileRefund_query = $this->MobileReSales->find('all',[
                                                                        'conditions' =>['MobileReSales.id IN'=>$payment_array]
                                                                    ]
                                                            );
                $mobileRefund_query
                        ->select('MobileReSales.refund_price')
                        ->select('MobileReSales.refund_gain')
                        ->select(['totalRefund' => $mobileRefund_query->func()->sum('MobileReSales.refund_price')])
                        ->select(['totalRefundGain' => $mobileRefund_query->func()->sum('MobileReSales.refund_gain')]);;
                $mobileRefund_query = $mobileRefund_query->hydrate(false);
                if(!empty($mobileRefund_query)){
                    $mobileRefund = $mobileRefund_query->first();
                }else{
                    $mobileRefund = array();
                }
              
			}
			
			$totalMobileSale = 0;
			if(count($mobileSale)){
				foreach($mobileSale as $key=>$data){
				$totalMobileSale+= $data['mobileSale'];
				}  
			}
			
			$paymentSaleIds = $payment_array;
		}
		
		$totalMobileRefund = 0;
		if(count($mobileRefund) && !empty($mobileRefund['totalRefund'])){
			$totalMobileRefund = $mobileRefund['totalRefund'];
		}
		
		//calculating payment as per the search
		if($paymentMode == 'Card'){
            if(empty($paymentSaleIds)){
                $paymentSaleIds = array(0 => null);
            }
            $saleSumData_query = $this->MobileReSalePayments->find('all',[
                                                                        'conditions' =>['payment_method' => 'Card', 'MobileReSalePayments.mobile_re_sale_id IN' => $paymentSaleIds],
                                                                    ]
                                                            );
            $saleSumData_query
                    ->select(['totalsale' => $saleSumData_query->func()->sum('amount')]);
            $saleSumData_query = $saleSumData_query->hydrate(false);
            if(!empty($saleSumData_query)){
                $saleSumData = $saleSumData_query->first();
            }else{
                $saleSumData = array();
            }
          
			$totalMobileRefund = 0;
			//pr($saleSumData);
		}elseif($paymentMode == 'Cash'){
            if(empty($paymentSaleIds)){
                $paymentSaleIds = array(0 => null);
            }
            $saleSumData_query = $this->MobileReSalePayments->find('all',[
                                                                        'conditions' =>['payment_method' => 'Cash', 'MobileReSalePayments.mobile_re_sale_id IN' => $paymentSaleIds],
                                                                    ]
                                                            );
            $saleSumData_query
                    ->select(['totalsale' => $saleSumData_query->func()->sum('amount')]);
            $saleSumData_query = $saleSumData_query->hydrate(false);
            if(!empty($saleSumData_query)){
                $saleSumData = $saleSumData_query->first();
            }else{
                $saleSumData = array();
            }
            
			
		}else{//if($paymentMode == 'Multiple')
            if(empty($paymentSaleIds)){
                $paymentSaleIds = array(0 => null);
            }
            
            $saleSumData_query = $this->MobileReSalePayments->find('all',[
                                                                        'conditions' =>['MobileReSalePayments.mobile_re_sale_id IN' => $paymentSaleIds],
                                                                    ]
                                                            );
            $saleSumData_query
                    ->select(['totalsale' => $saleSumData_query->func()->sum('amount')]);
            $saleSumData_query = $saleSumData_query->hydrate(false);
            if(!empty($saleSumData_query)){
                $saleSumData = $saleSumData_query->first();
            }else{
                $saleSumData = array();
            }    
		}
		$saleSum = $saleSumData['totalsale'];
		if($paymentMode == 'Cash' || $paymentMode == 'Card'){//count($payment_array)
		  
			if(empty($payment_array)){
                $payment_array = array(0 => null);
            }
            
            if($refundStatus == '' || $refundStatus == NULL){
			   if($ext_site == 1){
						 $managerKiosk = $this->get_kiosk();
						 if(!empty($managerKiosk)){
							  $conditionArr[] =  array('kiosk_id IN' => $managerKiosk);
							  $this->paginate = [
                                    'conditions' => ['MobileReSales.id IN'=>$payment_array,function ($q) {
                                                            return $q->isNull('refund_status');
                                                        },$conditionArr],
                                    'order' => ['MobileReSales.id desc'],
                                    'limit' => ROWS_PER_PAGE,
                                   ];   
						 }else{
							  $this->paginate = [
                                    'conditions' => ['MobileReSales.id IN'=>$payment_array,function ($q) {
                                                            return $q->isNull('refund_status');
                                                        }],
                                    'order' => ['MobileReSales.id desc'],
                                    'limit' => ROWS_PER_PAGE,
                                   ];
						 }
			   }else{
					$this->paginate = [
                                    'conditions' => ['MobileReSales.id IN'=>$payment_array,function ($q) {
                                                            return $q->isNull('refund_status');
                                                        }],
                                    'order' => ['MobileReSales.id desc'],
                                    'limit' => ROWS_PER_PAGE,
                                   ];
			   }
                
            }else{
			   if($ext_site == 1){
						 $managerKiosk = $this->get_kiosk();
						 if(!empty($managerKiosk)){
							  //$conditionArr[] =  array('kiosk_id IN' => $managerKiosk);
							  $this->paginate = [
                                    'conditions' => [
													 'MobileReSales.id IN'=>$payment_array,
													 'MobileReSales.refund_status' =>$refundStatus,
													 'MobileReSales.kiosk_id IN' => $managerKiosk
													 ],
                                    'order' => ['MobileReSales.id desc'],
                                    'limit' => ROWS_PER_PAGE,
                                   ];  
						 }else{
							  $this->paginate = [
                                    'conditions' => [
													 'MobileReSales.id IN'=>$payment_array,
													 'MobileReSales.refund_status' =>$refundStatus
													 ],
                                    'order' => ['MobileReSales.id desc'],
                                    'limit' => ROWS_PER_PAGE,
                                   ];
						 }
			   }else{
					$this->paginate = [
                                    'conditions' => [
													 'MobileReSales.id IN'=>$payment_array,
													 'MobileReSales.refund_status' =>$refundStatus
													 ],
                                    'order' => ['MobileReSales.id desc'],
                                    'limit' => ROWS_PER_PAGE,
                                   ];
					} 
            }
            if($refundStatus == '' || $refundStatus == NULL){
                $mobileCostData1_query = $this->MobileReSales->find('all', array(
                                'conditions' => array('MobileReSales.id IN'=>$payment_array,function ($q) {
                                                            return $q->isNull('refund_status');
                                                        },
                                        ),
                                'fields' => array('id','mobile_purchase_id'),
                                        ));
                
                $mobileCostData1_query = $mobileCostData1_query->hydrate(false);
                if(!empty($mobileCostData1_query)){
                    $mobileCostData1 = $mobileCostData1_query->toArray();
                }else{
                    $mobileCostData1 = array();
                }
            }else{
                $mobileCostData1_query = $this->MobileReSales->find('all', array(
                                'conditions' => array('MobileReSales.id IN'=>$payment_array,
                                        'MobileReSales.refund_status'=>$refundStatus),
                                'fields' => array('id','mobile_purchase_id'),
                                        )
                                          );
                $mobileCostData1_query = $mobileCostData1_query->hydrate(false);
                if(!empty($mobileCostData1_query)){
                    $mobileCostData1 = $mobileCostData1_query->toArray();
                }else{
                    $mobileCostData1 = array();
                }
            }
			$purchaseIds = array();
			if(!empty($mobileCostData1)){
				foreach($mobileCostData1 as $id => $purchaseId){
					$purchaseIds[] = $purchaseId["mobile_purchase_id"];
				}
			}
			if(empty($purchaseIds)){
                $purchaseIds = array(0 => null);
            }
            $mobileCostentries_query = $this->MobilePurchases->find('all', array( 
								'conditions' => array('MobilePurchases.id IN'=>$purchaseIds
									)));
            $mobileCostentries_query 
                   ->select(['mobileSalePrice'=>'CASE WHEN MobilePurchases.topedup_price is NULL THEN MobilePurchases.cost_price ELSE MobilePurchases.topedup_price END']);
           
            $mobileCostentries_query = $mobileCostentries_query->hydrate(false);
            if(!empty($mobileCostentries_query)){
                $mobileCostentries = $mobileCostentries_query->toArray();
            }else{
                $mobileCostentries = array();
            }
           
			foreach($mobileCostentries as $key1 => $value){
				$mobileCostData = $mobileCostData + $value['mobileSalePrice'];
			}
		}elseif($kioskid>0){
		  
			if($refundStatus == "" || $refundStatus == NULL){
                //pr($kioskid);die;
                $this->paginate = [
                                    'conditions' => ['MobileReSales.kiosk_id'=>$kioskid,
                                     function ($q) {
                                                            return $q->isNull('refund_status');
                                                        },
                                      $conditionArr],
                                    'order' => ['MobileReSales.id desc'],
                                    'limit' => ROWS_PER_PAGE,
                                    'contain' => ['Brands' , 'MobileModels']
                                ];
            }else{
                $this->paginate = [
                    'conditions' => ['MobileReSales.kiosk_id'=>$kioskid,
                      'MobileReSales.refund_status'=>$refundStatus,
                      $conditionArr],
                    'order' => ['MobileReSales.id desc'],
                    'limit' => ROWS_PER_PAGE,
                    'contain' => ['Brands' , 'MobileModels']
                ];
            }
            if($refundStatus == "" || $refundStatus == NULL){
                $mobileCostData1_query = $this->MobileReSales->find('all', array(
                                'conditions' => array('MobileReSales.kiosk_id'=>$kioskid,
                                                      function ($q) {
                                                            return $q->isNull('refund_status');
                                                        },
                                      $conditionArr),
                                'fields' => array('id','mobile_purchase_id'),
                                'contain' => array('Brands' , 'MobileModels')
                                        )
                                          );
                 //$mobileCostData1_query
                 //                      ->where(function ($exp, $q) {
                 //                               return $exp->isNull('refund_status');
                 //                           });
            }else{
                    $mobileCostData1_query = $this->MobileReSales->find('all', array(
                    'conditions' => array('MobileReSales.kiosk_id'=>$kioskid,
                          'MobileReSales.refund_status'=>$refundStatus,
                          $conditionArr),
                    'fields' => array('id','mobile_purchase_id'),
                    'contain' => array('Brands' , 'MobileModels')
                            )
                              );
            }
            $mobileCostData1_query = $mobileCostData1_query->hydrate(false);
            if(!empty($mobileCostData1_query)){
                $mobileCostData1 = $mobileCostData1_query->toArray();
            }else{
                $mobileCostData1 = array();
            }
			$purchaseIds = array();
			if(!empty($mobileCostData1)){
				foreach($mobileCostData1 as $id => $purchaseId){
					$purchaseIds[] = $purchaseId["mobile_purchase_id"];
				}
			}
            if(empty($purchaseIds)){
                $purchaseIds = array(0 => null);
            }
            $mobileCostentries_query = $this->MobilePurchases->find('all', array( 
                    'conditions' => array('MobilePurchases.id IN'=>$purchaseIds),
                        ));
            $mobileCostentries_query 
                   ->select(['mobileSalePrice'=>'CASE WHEN MobilePurchases.topedup_price is NULL THEN MobilePurchases.cost_price ELSE MobilePurchases.topedup_price END']);
                   //pr($mobileCostentries_query);die;
            $mobileCostentries_query = $mobileCostentries_query->hydrate(false);
            if(!empty($mobileCostentries_query)){
                $mobileCostentries = $mobileCostentries_query->toArray();
            }else{
                $mobileCostentries = array();
            }
            
			
			foreach($mobileCostentries as $key1 => $value){
				$mobileCostData = $mobileCostData + $value['mobileSalePrice'];
			}
		}else{
		  	   if($refundStatus == "" || $refundStatus == NULL){
					if($ext_site == 1){
						 $managerKiosk = $this->get_kiosk();
						 if(!empty($managerKiosk)){
							  $conditionArr[] =  array('kiosk_id IN' => $managerKiosk);
							 //  $conditionArr['conditions']['MobileReSales.kiosk_id IN'] = $managerKiosk;   
						 }
					}
					$this->paginate = [
									   'conditions' => [
													   function ($q) {
															   return $q->isNull('refund_status');
														   },$conditionArr],
									   'order' => ['MobileReSales.id desc'],
									   'limit' => ROWS_PER_PAGE,
									   'contain' => ['Brands' , 'MobileModels']
									 ];
				    // pr($conditionArr);die;
			   }else{
					if($ext_site == 1){
						 $managerKiosk = $this->get_kiosk();
						 if(!empty($managerKiosk)){
							    $conditionArr[] =  array('kiosk_id IN' => $managerKiosk);  
						 }
					 }
					$this->paginate = [
									   'conditions' => [
														'MobileReSales.refund_status' => $refundStatus
														   ,$conditionArr],
									   'order' => ['MobileReSales.id desc'],
									   'limit' => ROWS_PER_PAGE,
									   'contain' => ['Brands' , 'MobileModels']
									 ];
			   }
		 
			  // pr($conditionArr);
			//   pr( $this->paginate);die;
			
			
            if($refundStatus == "" || $refundStatus == NULL){
                $mobileCostData1_query = $this->MobileReSales->find('all', array(
                                'conditions' => array(
                                    function ($q) {
                                                            return $q->isNull('refund_status');
                                                        },
                                    $conditionArr),
                                'fields' => array('id','mobile_purchase_id'),
                                'contain' => array('Brands' , 'MobileModels')
                                        )
                                          );
               
            }else{
                $mobileCostData1_query = $this->MobileReSales->find('all', array(
                                'conditions' => array(
                                    'MobileReSales.refund_status'=>$refundStatus,
                                    $conditionArr),
                                'fields' => array('id','mobile_purchase_id'),
                                'contain' => array('Brands' , 'MobileModels')
                                        )
                                          );
            }
			
			
			
            $mobileCostData1_query = $mobileCostData1_query->hydrate(false);
            if(!empty($mobileCostData1_query)){
                $mobileCostData1 = $mobileCostData1_query->toArray();
            }else{
                $mobileCostData1 = array();
            }
			$purchaseIds = array();
			if(!empty($mobileCostData1)){
				foreach($mobileCostData1 as $id => $purchaseId){
					$purchaseIds[] = $purchaseId["mobile_purchase_id"];
				}
			}
			if(empty($purchaseIds)){
                $purchaseIds = array(0 => null);
            }
            $mobileCostentries_query = $this->MobilePurchases->find('all', array( 
								'conditions' => array('MobilePurchases.id IN'=>$purchaseIds),
                                //'contain' => array('Brands' , 'MobileModels')
									));
            $mobileCostentries_query 
                   ->select(['mobileSalePrice'=>'CASE WHEN MobilePurchases.topedup_price is NULL THEN MobilePurchases.cost_price ELSE MobilePurchases.topedup_price END'])
                   ->select('MobilePurchases.id');
            $mobileCostentries_query = $mobileCostentries_query->hydrate(false);
            if(!empty($mobileCostentries_query)){
                $mobileCostentries = $mobileCostentries_query->toArray();
            }else{
                $mobileCostentries = array();
            }
            $priceArr = array();
			foreach($mobileCostentries as $key1 => $value){
				$priceArr[$value['id']] = $value['mobileSalePrice'];
			}
			foreach($purchaseIds as $a => $b){
				if(array_key_exists($b,$priceArr)){
					$mobileCostData = $mobileCostData + $priceArr[$b];
				}else{
					$mobileCostData = $mobileCostData;
				}
			}
			//echo $mobileCostData;die;
			if($refundStatus == NULL){
				$refund = array();
				$refundPrice_query = $this->MobileReSales->find('all', array(
                                    'conditions' => array(
									'MobileReSales.refund_status'=> 1,
									//'MobileReSale.status' => 1,
									$conditionArr),
                                    'fields' => array('id','mobile_purchase_id'),
                                    'contain' => array('Brands' , 'MobileModels')
										)
										  );
                $refundPrice_query = $refundPrice_query->hydrate(false);
                if(!empty($refundPrice_query)){
                    $refundPrice = $refundPrice_query->toArray();
                }else{
                    $refundPrice = array();
                }
				//pr($refundPrice);die;
				foreach($refundPrice as $s => $val){
					$refund[$val['id']] = $val['mobile_purchase_id'];
				}
				$totalRefund = 0;
				if(is_array($refund)){
					foreach($refund as $key2 => $value2){
						if(array_key_exists($value2,$priceArr)){
							$totalRefund = $totalRefund + $priceArr[$value2];	
						}
					}
					$mobileCostData = $mobileCostData - $totalRefund;
				}
			}
			
		}
		$totalRefundGain = 0;
		if(count($mobileRefund) && !empty($mobileRefund['totalRefundGain'])){
			$totalRefundGain = $mobileRefund['totalRefundGain'];
		}
		$grandNetSale = $totalMobileSale-$totalMobileRefund;
		
		$mobileModels_query = $this->MobileModels->find('all',array('fields'=>array('id','model')));
        $mobileModels_query = $mobileModels_query->hydrate(false);
        if(!empty($mobileModels_query)){
            $mobileModels = $mobileModels_query->toArray();
        }else{
            $mobileModels = array();
        }
		foreach($mobileModels as $k=>$modelinfo){
			$modelName[$modelinfo['id']]=$modelinfo['model'];
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
		$paymentArr = array();
		if(empty($saleIds)){
            $saleIds = array(0 => null);
        }
        $mobileResalePayment_query = $this->MobileReSalePayments->find('all', array('conditions' => array('MobileReSalePayments.mobile_re_sale_id IN' => $saleIds)));
        $mobileResalePayment_query = $mobileResalePayment_query->hydrate(false);
        if(!empty($mobileResalePayment_query)){
            $mobileResalePayment = $mobileResalePayment_query->toArray();
        }else{
            $mobileResalePayment = array();
        }
		if(count($mobileResalePayment)){
			foreach($mobileResalePayment as $rp => $resalePayment){
				$mobilePurchaseID = $resalePayment['mobile_purchase_id'];
				$mobileReSaleID = $resalePayment['mobile_re_sale_id'];
				$paymentArr[$mobileReSaleID][] = $resalePayment;
				$pmtMethod = $resalePayment['payment_method'];
				$resalePmt = $resalePayment['amount'];
				
				if(array_key_exists($mobileReSaleID,$payment_amount_arr) && array_key_exists($pmtMethod,$payment_amount_arr[$mobileReSaleID])){
					$payment_amount_arr[$mobileReSaleID][$pmtMethod]+= $resalePmt;
				}else{
					$payment_amount_arr[$mobileReSaleID][$pmtMethod] = $resalePmt;
				}
			}
		}
		//pr($this->paginate);die;
		$mobileReSales_query = $this->paginate('MobileReSales');
        if(!empty($mobileReSales_query)){
            $mobileReSales = $mobileReSales_query->toArray();
        }else{
            $mobileReSales = array();
        }
		$users_query = $this->Users->find('list',[
                                                'keyField' => 'id',
                                                'valueField' => 'username'
                                           ]
                                    );
        $users_query = $users_query->hydrate(false);
        if(!empty($users_query)){
            $users = $users_query->toArray();
        }else{
            $users = array();
        }
		$this->set(compact('brands','modelName','grandNetSale','totalRefundGain','vat','currency','kiosks','users','paymentArr','paymentMode','payment_amount_arr','saleSum','totalMobileRefund','mobileCostData'));
		$this->set(compact('mobileReSales'));
		//$this->layout = 'default';
		$this->render('index');
	}
    
    private function generate_condition_array(){
		$searchKW = trim(strtolower($this->request->query['search_kw']));
		$conditionArr = array();
		if(!empty($searchKW)){
			$conditionArr['OR']['MobileReSales.imei like'] =  strtolower("%$searchKW%");
			$conditionArr['OR']['Brands.brand like'] = strtolower("%$searchKW%");
			$conditionArr['OR']['MobileReSales.customer_email like'] = strtolower("%$searchKW%");
			$conditionArr ['OR']['MobileModels.model like'] = strtolower("%$searchKW%");
		}
		
		if(array_key_exists('start_date',$this->request->query) &&
		   !empty($this->request->query['start_date'])
		   ){
			$this->set('start_date',$this->request->query['start_date']);
		}
		
		if(array_key_exists('end_date',$this->request->query) &&
		   !empty($this->request->query['end_date'])
		   ){
			$this->set('end_date',$this->request->query['end_date']);
		}
		
		if(array_key_exists('start_date',$this->request->query) &&
		   !empty($this->request->query['start_date']) &&
		   array_key_exists('end_date',$this->request->query) &&
		   !empty($this->request->query['end_date'])
		   ){
			$conditionArr[] = array(
			"MobileReSales.created >" => date('Y-m-d', strtotime($this->request->query['start_date'])),
			"MobileReSales.created <" => date('Y-m-d', strtotime($this->request->query['end_date']. ' +1 Days')),   
			);
		}
		//echo "admin";
		$kioskid = $this->request->Session()->read('kiosk_id');
        //pr($this->request->query);die;
		if($kioskid == ""){
			// pr($this->request->query); 
        // echo   $kioskId = $this->request->query['MobileResale']['kiosk_id'];die;
//			$this->set('kioskId', $kioskId);
            if(array_key_exists('MobileResale',$this->request->query)){
                  $kioskId = $this->request->query['MobileResale']['kiosk_id'];
            }else{
                $kioskId = '';
            }
            $this->set('kioskId', $kioskId);
			if(array_key_exists('MobileResale',$this->request->query) && !empty($this->request->query['MobileResale']['kiosk_id'])){
				//echo "admin1";
				if($kioskId == "10000"){
					$kioskId = "0";
					$conditionArr[] =  array('kiosk_id' => $kioskId);
				}else{
				 $conditionArr[] =  array('kiosk_id' => $this->request->query['MobileResale']['kiosk_id']);
				}
			}
		}
		if($kioskid>0){
			
			$conditionArr[] = array('kiosk_id' =>$kioskid);
		}
		if(!array_key_exists('kiosk_id',$conditionArr)){
		  $managerKiosk = $this->get_kiosk();
		  $conditionArr['kiosk_id IN'] = $managerKiosk;
		}
		
       // pr($conditionArr);
		return $conditionArr;
	}
    
    public function research($start = '',$last = '',$kiosk = ''){
		//pr($this->request);die;
		$conditionArr = array();
		if(array_key_exists('0',$this->request->params['pass']) &&
		   !empty($this->request->params['pass'][0])
		   ){
			$this->set('start_date',$this->request->params['pass'][0]);
		}
		
		if(array_key_exists('1',$this->request->params['pass']) &&
		   !empty($this->request->params['pass'][1])
		   ){
			$this->set('end_date',$this->request->params['pass'][1]);
		}
		
		if(array_key_exists('0',$this->request->params['pass']) &&
		   !empty($this->request->params['pass'][0]) &&
		   array_key_exists('1',$this->request->params['pass']) &&
		   !empty($this->request->params['pass'][1])
		   ){
			$conditionArr[] = array(
			"MobileReSales.created >" => date('Y-m-d', strtotime($this->request->params['pass'][0])),
			"MobileReSales.created <" => date('Y-m-d', strtotime($this->request->params['pass'][1]. ' +1 Days')),   
			);
		}
		if(array_key_exists('2',$this->request->params['pass']) && !empty($this->request->params['pass'][2])){
			//$this->set('kiosk_id',$this->request->data['MobileResale']['kiosk_id']);
			$kioskId = $this->request->params['pass'][2];
			//$this->request->data['MobileResale']['kiosk_id'] = $kioskid;
			$this->set(compact('kioskId'));
			$conditionArr[] = array('kiosk_id' =>$kioskId);
		}
		if(!array_key_exists('kiosk_id',$conditionArr)){
		  $conditionArr['kiosk_id IN'] = $this->get_kiosk(); 
		}
		//--------------------------------------
		$vat = $this->setting['vat'];
		$currency = $this->setting['currency_symbol'];
        
        $mobileSale_query = $this->MobileReSales->find('all', array( 
								'conditions' => array($conditionArr),
                                'contain' => array('Brands' , 'MobileModels')
									));
        $mobileSale_query 
               ->select(['mobileSale'=>'CASE WHEN MobileReSales.discounted_price is NULL THEN MobileReSales.selling_price ELSE MobileReSales.discounted_price END'])
               ->select('MobileReSales.id')
               ->select('MobileReSales.discounted_price')
               ->select('MobileReSales.selling_price');
        $mobileSale_query = $mobileSale_query->hydrate(false);
        if(!empty($mobileSale_query)){
            $mobileSale = $mobileSale_query->toArray();
        }else{
            $mobileSale = array();
        }
       
		$saleIds = array();
		foreach($mobileSale as $ms => $sale){
			$saleIds[$sale['id']] = $sale['id'];
		}
		$this->paginate = [
                                'conditions' => [$conditionArr],
								'order' => ['MobileReSales.id desc'],
								'limit' => 200,//ROWS_PER_PAGE,
                          ];
		$mobileCostData1_query = $this->MobileReSales->find('all', array(
							'conditions' => array(
							      $conditionArr),
							'fields' => array('id','mobile_purchase_id'),
									)
								      );
        $mobileCostData1_query = $mobileCostData1_query->hydrate(false);
        if(!empty($mobileCostData1_query)){
            $mobileCostData1 = $mobileCostData1_query->toArray();
        }else{
            $mobileCostData1 = array();
        }
		$purchaseIds = array();
		if(!empty($mobileCostData1)){
			foreach($mobileCostData1 as $id => $purchaseId){
				$purchaseIds[] = $purchaseId["mobile_purchase_id"];
			}
		}
		if(empty($purchaseIds)){
		  $purchaseIds = array(0 => null);
		}
        $mobileCostData_query = $this->MobilePurchases->find('all', array( 
								'conditions' => array('MobilePurchases.id IN'=>$purchaseIds),
                                //'contain' => array('Brands' , 'MobileModels')
									));
        $mobileCostData_query 
             ->select(['total_mobile_cost' => $mobileCostData_query->func()->sum('topedup_price')]);
        $mobileCostData_query = $mobileCostData_query->hydrate(false);
        if(!empty($mobileCostData_query)){
            $mobileCostData = $mobileCostData_query->toArray();
        }else{
            $mobileCostData = array();
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
		$paymentArr = array();
		$payment_amount_arr = array();
		if(empty($saleIds)){
		  $saleIds = array(0 => null);
		}
		$mobileResalePayment_query = $this->MobileReSalePayments->find('all', array('conditions' => array('MobileReSalePayments.mobile_re_sale_id IN' => $saleIds)));
        $mobileResalePayment_query = $mobileResalePayment_query->hydrate(false);
        if(!empty($mobileResalePayment_query)){
            $mobileResalePayment = $mobileResalePayment_query->toArray();
        }else{
            $mobileResalePayment = array();
        }
		if(count($mobileResalePayment)){
			foreach($mobileResalePayment as $rp => $resalePayment){
				$mobilePurchaseID = $resalePayment['mobile_purchase_id'];
				$mobileReSaleID = $resalePayment['mobile_re_sale_id'];
				$paymentArr[$mobileReSaleID][] = $resalePayment;
				$pmtMethod = $resalePayment['payment_method'];
				$resalePmt = $resalePayment['amount'];
				//$paymentArr[$mobilePurchaseID][] = $resalePayment;
				
				if(array_key_exists($mobileReSaleID,$payment_amount_arr) && array_key_exists($pmtMethod,$payment_amount_arr[$mobileReSaleID])){
					$payment_amount_arr[$mobileReSaleID][$pmtMethod]+= $resalePmt;
				}else{
					$payment_amount_arr[$mobileReSaleID][$pmtMethod] = $resalePmt;
				}
			}
		}
		$mobileReSales_query = $this->paginate('MobileReSales');
        if(!empty($mobileReSales_query)){
            $mobileReSales = $mobileReSales_query->toArray();
        }else{
            $mobileReSales = array();
        }
		$brands_query = $this->MobileReSales->Brands->find('list',[
                                                                'keyField' => 'id',
                                                                'valueField' => 'brand',
                                                                'conditions' => ['Brands.status' => 1]
                                                            ]
                                                    );
        $brands_query = $brands_query->hydrate(false);
        if(!empty($brands_query)){
            $brands = $brands_query->toArray();
        }else{
            $brands = array();
        }
		$mobileModels_query = $this->MobileModels->find('all',array('fields'=>array('id','model')));
        $mobileModels_query = $mobileModels_query->hydrate(false);
        if(!empty($mobileModels_query)){
            $mobileModels = $mobileModels_query->toArray();
        }else{
            $mobileModels = array();
        }
		foreach($mobileModels as $k=>$modelinfo){
			$modelName[$modelinfo['id']]=$modelinfo['model'];
		}
		$users_query = $this->Users->find('list',[
                                            'keyField' => 'id',
                                            'valueField' => 'username'
                                           ]
                                    );
        $users_query = $users_query->hydrate(false);
        if(!empty($users_query)){
            $users = $users_query->toArray();
        }else{
            $users = array();
        }
		$this->set(compact('brands','modelName','grandNetSale','vat','currency','kiosks','users','paymentArr','paymentMode','payment_amount_arr','saleSum','mobileCostData'));
		$this->set(compact('mobileReSales'));
	}
    
    public function view($id = null) {
		$kiosk_id = $this->request->Session()->read('kiosk_id');
		$currency = $this->setting['currency_symbol'];
		$gradeType = Configure::read('grade_type');
		$type = array('1'=> 'Locked', '0' => 'Unlocked');
		$resaleOptions = Configure::read('resale_statuses');
		$countryOptions = Configure::read('uk_non_uk');
		$discountOptions = Configure::read('discount');
		$colorOptions = Configure::read('color');
		 
		$this->set(compact('resaleOptions','countryOptions','kiosk_id','gradeType','type','colorOptions','discountOptions','currency'));
		if (!$this->MobileReSales->exists($id)) {
			throw new NotFoundException(__('Invalid mobile re sale'));
		}
		$options = array('conditions' => array('MobileReSales.id' => $id),'contain' => array('Brands')); //
		$mobileReSale_query = $this->MobileReSales->find('all', $options);
		//echo'<pre>';pr($mobileReSale_query);die;
        $mobileReSale_query = $mobileReSale_query->hydrate(false);
        if(!empty($mobileReSale_query)){
            $mobileReSale = $mobileReSale_query->first();
        }else{
            $mobileReSale = array();
        }
		
		$this->set('mobileReSale', $mobileReSale);
		$mobilePurchaseId = $mobileReSale['mobile_purchase_id'];
		$mobilePurchaseData_query = $this->MobilePurchases->find('all', array('conditions' => array('MobilePurchases.id' => $mobilePurchaseId)));
        $mobilePurchaseData_query = $mobilePurchaseData_query->hydrate(false);
        if(!empty($mobilePurchaseData_query)){
            $mobilePurchaseData = $mobilePurchaseData_query->first();
        }else{
            $mobilePurchaseData = array();
        }
		//pr($mobilePurchaseData);die;
		$mobileModels_query = $this->MobileModels->find('list',[
                                                            'keyField' => 'id',
                                                            'valueField' => 'model'
                                                        ]
                                                );
        $mobileModels_query = $mobileModels_query->hydrate(false);
        if(!empty($mobileModels_query)){
            $mobileModels = $mobileModels_query->toArray();
        }else{
            $mobileModels = array();
        }
		$networks_query = $this->Networks->find('list',[
                                                    'keyField' => 'id',
                                                    'valueField' => 'name'
                                                 ]
                                        );
        $networks_query = $networks_query->hydrate(false);
        if(!empty($networks_query)){
            $networks = $networks_query->toArray();
        }else{
            $networks = array();
        }
		$networks[""] = "--";
		
		
		$kiosks = $this->Kiosks->find("list",array(
										 'keyField' =>"id",
										 'valueField' => "name"
										 ))->toArray();
		
		$this->set(compact('mobileModels','networks','mobilePurchaseData','kiosks'));
	}
    
    public function edit($id = null) {
		  
		if (!$this->MobileReSales->exists($id)) {
			throw new NotFoundException(__('Invalid mobile re sale'));
		}else{
		  $mobile_re_sale_entity = $this->MobileReSales->get($id);
		  $this->set(compact('mobile_re_sale_entity'));
		}
		$kiosks_query = $this->MobileReSales->Kiosks->find('list');
        $kiosks_query = $kiosks_query->hydrate(false);
        if(!empty($kiosks_query)){
            $kiosks = $kiosks_query->toArray();
        }else{
            $kiosks = array();
        }
		$users_query = $this->Users->find('list',[
                                            'keyField' => 'id',
                                            'valueField' => 'username'
                                           ]
                                    );
        $users_query = $users_query->hydrate(false);
        if(!empty($users_query)){
            $users = $users_query->toArray();
        }else{
            $users = array();
        }
		$mobileResaleData_query = $this->MobileReSales->find('all',array(
																	'conditions' => array('MobileReSales.id' => $id)
																	));
        $mobileResaleData_query = $mobileResaleData_query->hydrate(false);
        if(!empty($mobileResaleData_query)){
            $mobileResaleData = $mobileResaleData_query->first();
        }else{
            $mobileResaleData = array();
        }
		//Start: getting lowest selling price
		$mobilePrice_query = $this->MobilePrices->find('all', array(
											  'conditions' => array(
																		'brand_id' => $mobileResaleData['brand_id'],
																		'mobile_model_id' => $mobileResaleData['mobile_model_id'],
																		'grade' => $mobileResaleData['grade'],
																		'locked' => $mobileResaleData['type'],
																		//1: for locked, 0: for unlocked
																	)));
        $mobilePrice_query =$mobilePrice_query->hydrate(false);
        if(!empty($mobilePrice_query)){
            $mobilePrice = $mobilePrice_query->first();
        }else{
            $mobilePrice = array();
        }
		
		$lowestSalePrice = $mobileResaleData['discounted_price'];
		if(count($mobilePrice) > 0){
			$lowestSalePrice = $adminSalePrice = $mobilePrice['sale_price'];
			$adminDscntStatus = $mobilePrice['discount_status'];
			if($adminDscntStatus){
				//calculate lowest selling price
				$adminMaxDscntPerc = $mobilePrice['maximum_discount'];
				$lowestSalePrice = $adminSalePrice - (($adminMaxDscntPerc / 100) * $adminSalePrice);
				$lowestSalePrice = number_format($lowestSalePrice, 2);
			}
		}
		$this->set(compact('lowestSalePrice'));
		//End: getting lowest selling price
		
		$created = strtotime($mobileResaleData['created']);
		$currentTime_conn = ConnectionManager::get('default');
        $currentTime_stmt = $currentTime_conn->execute('SELECT NOW() as timeDate'); 
        $currentTime = $currentTime_stmt ->fetchAll('assoc');
        //$currentTime = $this->MobileReSalePayment->query('SELECT NOW() as timeDate');
		$curTime = strtotime($currentTime[0]['timeDate']);
		//checking if the repair belongs to the kiosk for customers screen
		if($this->request->session()->read('Auth.User.group_id') == KIOSK_USERS){
			$diffOfTime = $curTime-$created;
			if($diffOfTime > 600){//
				$diffInMins = number_format($diffOfTime / 60,2);
				$this->Flash->success("You can only edit the sale within 10 mintues (Current Difference: $diffInMins Mins)!");
				return $this->redirect(array('controller' => 'mobile_re_sales','action' => 'index'));
				die;
			}
			if($mobileResaleData['kiosk_id'] != $this->request->Session()->read('kiosk_id')){
				$this->Flash->success("You can only edit the sale belonging to your kiosk!");
				return $this->redirect(array('controller' => 'retail_customers', 'action' => 'index'));
				die;
			}
		}
		if ($this->request->is(array('post', 'put'))){
			if(array_key_exists('cancel',$this->request->data)){
				return $this->redirect(array('action' => 'edit', $id));
				die;
			}
			if(array_key_exists('UpdatePayment',$this->request->data)){
				$saveCount = 0;
				$saleAmount = $this->request->data['sale_amount'];
				$addedAmount = 0;
				if(array_key_exists('added_amount',$this->request->data)){
					$addedAmount = $this->request->data['added_amount'];
				}
				
				$updatedPaymentData = $this->request->data['UpdatePayment'];
				$updatedAmountData = $this->request->data['updated_amount'];
				$totalAmount = 0;
				foreach($updatedAmountData as $pmntId => $paymentAmnt){
					$totalAmount+=$paymentAmnt;
				}
				$totalAmount = $totalAmount+$addedAmount;
				$error = array();
				$errorStr = '';
				if($totalAmount != $saleAmount){
					$error = "Total sum must be equivalent to the sales amount(&#163;$saleAmount)";
				}
				
				if(!empty($error)){
					$this->Flash->success($error);
					return $this->redirect(array('action' => 'edit',$id));
					die;
				}else{
					
					//****saving newly added payment amount
					if(array_key_exists('added_amount',$this->request->data) && is_numeric($this->request->data['added_amount'])){
						$paymntData_query = $this->MobileReSalePayments->find('all',array(
									'conditions' => array('MobileReSalePayments.mobile_re_sale_id'=>$id),
										)
									  );
                        $paymntData_query = $paymntData_query->hydrate(false);
                        if(!empty($paymntData_query)){
                            $paymntData = $paymntData_query->first();
                        }else{
                            $paymntData = array();
                        }
						//unsetting the unrequired fields
						unset($paymntData['id']);
						unset($paymntData['payment_method']);
						unset($paymntData['amount']);
						//unset($paymntData['created']);
						unset($paymntData['modified']);
						
						//adding new fields
						//pr($this->request->data);die;
						$paymntData['payment_method'] = $this->request->data['new_change_mode'];
						$paymntData['amount'] = $this->request->data['added_amount'];
						$new_entity = $this->MobileReSalePayments->newEntity($paymntData,['validate' => false]);
                        $patch_entity = $this->MobileReSalePayments->patchEntity($new_entity,$paymntData,['validate' => false]);
						if($this->MobileReSalePayments->save($patch_entity)){
							$saveCount++;
						}
					}
					// saving new added payment till here*****
					
					foreach($updatedPaymentData as $paymentId => $paymentMode){
						if($paymentId == 'new_change_mode'){continue;}
						$resalePaymentData = array(
							'id' => $paymentId,
							'payment_method' => $paymentMode,
							'amount' => $updatedAmountData[$paymentId]
							   );
                        $getId = $this->MobileReSalePayments->get($paymentId);
                        $patchEntity = $this->MobileReSalePayments->patchEntity($getId,$resalePaymentData,['validate' => false]);
						if($this->MobileReSalePayments->save($patchEntity)){
							$saveCount++;
						}
					}
				}
				
				if($saveCount > 0){
					$get_Id = $this->MobileReSales->get($id);
                    $data_array = array();
                    $data_array['selling_price'] = $this->request->data['updated_selling_price'];
                    $data_array['discounted_price'] = $this->request->data['updated_discounted_price'];
                    $data_array['discount'] = $this->request->data['updated_discount'];
                    $patch_Entity = $this->MobileReSales->patchEntity($get_Id,$data_array,['validate' => false]);
					$this->MobileReSales->save($patch_Entity);
					//$this->MobileReSale->saveField('discounted_price',$this->request->data['updated_discounted_price']);
					//$this->MobileReSale->saveField('discount',$this->request->data['updated_discount']);
					
					$this->Flash->success('Payment has been successfully updated!');
					return $this->redirect(array('action' => 'index'));
				}else{
					$this->Flash->error('Payment could not be updated!');
					return $this->redirect(array('action' => 'edit',$id));
				}
				die;
			}
			
			$custmGrade = $this->request->data['custom_grade'];
			$lowestSellingPrice = $this->request->data['lowest_selling_price'];
			$hidden_discount = $this->request->data['hidden_discount'];
			$discount = $this->request->data['discount'];
			$hidden_selling_price = $this->request->data['hidden_selling_price'];
			$selling_price = $this->request->data['selling_price'];
			$hidden_discounted_price = $this->request->data['hidden_discounted_price'];
			$discounted_price = $this->request->data['discounted_price'];
			if($custmGrade == 1){ 
				if($selling_price < $lowestSellingPrice){
					$this->Flash->error('Selling price cannot be less then lowest selling price!');
					return $this->redirect(array('action' => 'edit',$id));
				die;
				}
			}
			if($this->request->session()->read('Auth.User.group_id') == KIOSK_USERS){
				if($discounted_price < $lowestSellingPrice){
					$this->Flash->success('Selling price cannot be less then lowest selling price!');
						return $this->redirect(array('action' => 'edit',$id));
					die;
				}
			}
			if($hidden_selling_price != $selling_price || $hidden_discounted_price != $discounted_price){
				if(
				   $this->request->session()->read('Auth.User.group_id') == ADMINISTRATORS || $this->request->session()->read('Auth.User.group_id') == FRANCHISE_OWNER ||
				   $this->request->session()->read('Auth.User.group_id') == MANAGERS ||
				   $this->request->session()->read('Auth.User.group_id') == KIOSK_USERS
				){
					//kiosk users added on 16may 2016 on client's request
					$rsleData = $this->request->data;
					$rsleData['selling_price'] = $hidden_selling_price;
					$rsleData['discounted_price'] = $hidden_discounted_price;
					$rsleData['discount'] = $hidden_discount;
					$iemi = $this->request['data']['imei'].$this->request['data']['imei1'];
					$rsleData['imei']=$iemi;
					//pr($rsleData);die;
                    $id_get = $this->MobileReSales->get($rsleData['id']);
                    $entity_patch = $this->MobileReSales->patchEntity($id_get,$rsleData,['validate' => false]);
					if($this->MobileReSales->save($entity_patch)){
						//saving logs
					
						$mobileTransferLogData = array(
							'mobile_purchase_id' => $mobileResaleData['mobile_purchase_id'],
							'kiosk_id' => $mobileResaleData['kiosk_id'],
							'mobile_resale_id' => $id,
							'imei' => $mobileResaleData['imei'],
							'status' => 1,
							'network_id' => $mobileResaleData['network_id'],
							'grade' => $mobileResaleData['grade'],
							'type' => $mobileResaleData['type']
						);
						$idGet = $this->MobileTransferLogs->newEntity($mobileTransferLogData,['validate' => false]);
						$entinty_p = $this->MobileTransferLogs->patchEntity($idGet,$mobileTransferLogData,['validate' => false]);
						$this->MobileTransferLogs->save($entinty_p);
						
						$paymentData_query = $this->MobileReSalePayments->find('all',array(
							'conditions' => array('MobileReSalePayments.mobile_re_sale_id'=>$id),
								)
							  );
                        $paymentData_query = $paymentData_query->hydrate(false);
                        if(!empty($paymentData_query)){
                            $paymentData = $paymentData_query->toArray();
                        }else{
                            $paymentData = array();
                        }
						$this->set(compact('paymentData','users','kiosks'));
						$this->render('admin_resale_payment');
						goto fakeblock;
					}
				}
			}
			$iemi = $this->request['data']['imei'].$this->request['data']['imei1'];
			$this->request->data['imei']=$iemi;
            //pr($this->request->data);die;
            $Id_get = $this->MobileReSales->get($this->request->data['id']);
            $Patch_entity = $this->MobileReSales->patchEntity($Id_get,$this->request->data,['validate' => false]);
			if ($this->MobileReSales->save($Patch_entity)) {
				//saving logs
					
					$mobileTransferLogData = array(
						'mobile_purchase_id' => $mobileResaleData['mobile_purchase_id'],
						'kiosk_id' => $mobileResaleData['kiosk_id'],
						'mobile_resale_id' => $id,
						'imei' => $mobileResaleData['imei'],
						'status' => 1,
						'network_id' => $mobileResaleData['network_id'],
						'grade' => $mobileResaleData['grade'],
						'type' => $mobileResaleData['type']
						);
					$id_G = $this->MobileTransferLogs->newEntity($mobileTransferLogData,['validate' => false]);
					$patch_En = $this->MobileTransferLogs->patchEntity($id_G,$mobileTransferLogData,['validate' => false]);
					$this->MobileTransferLogs->save($patch_En);
				$this->Flash->success(__('The mobile re sale has been saved.'));
				return $this->redirect(array('action' => 'index'));
			} else {
				$this->Flash->error(__('The mobile re sale could not be saved. Please, try again.'));
			}
		} else {
			$options = array('conditions' => array('MobileReSales.id' => $id));
			$MobileReSale_query = $this->MobileReSales->find('all', $options);
            $MobileReSale_query = $MobileReSale_query->hydrate(false);
            if(!empty($MobileReSale_query)){
                $MobileReSale = $MobileReSale_query->first();
            }else{
                $MobileReSale = array();
            }
            $this->request->data = $MobileReSale;
		}
		$lockedUnlocked = array('0'=>'Unlocked','1'=>'Locked');
		$brands_query = $this->MobileReSales->Brands->find('list',[
                                                                'keyField' => 'id',
                                                                'valueField' => 'brand',
                                                                'conditions' => array('Brands.status' => 1)
                                                            ]
                                                    );
        $brands_query = $brands_query->hydrate(false);
        if(!empty($brands_query)){
            $brands = $brands_query->toArray();
        }else{
            $brands = array();
        }
		$mobileModels_query = $this->MobileModels->find('list',[
                                                            'keyField' => 'id',
                                                            'valueField' => 'model',
                                                            'conditions'=>array('MobileModels.status'=>1)
                                                        ]
                                                );
		$mobileModels_query = $mobileModels_query->hydrate(false);
        if(!empty($mobileModels_query)){
            $mobileModels = $mobileModels_query->toArray();
        }else{
            $mobileModels = array();
        }
		$created = strtotime($mobileResaleData['created']);
		$currentTime = strtotime(date("Y-m-d H:i:s"));
		$diffTime = $currentTime-$created;
		if($this->request->session()->read('Auth.User.group_id') == KIOSK_USERS){
			//if($diffTime>900){
				//$this->Session->setFlash("You can only edit the sale within 15 minutes from the time of creation");
				//return $this->redirect(array('action'=>'index'));
			//}
		}
		$mobilePurchaseBrandId = $mobileResaleData['brand_id'];
		$mobilePurchaseModelId = $mobileResaleData['mobile_model_id'];
		$customGrade = $mobileResaleData['custom_grade'];
		$imei1 = $mobileResaleData['imei'];
		$mobilePurchaseGrade = $mobileResaleData['grade'];
		$mobilePurchaseType = $mobileResaleData['type'];
		
		if($customGrade == 1){
			$mobilePrices = $this->MobilePurchase->find('first',array('conditions'=>array('imei' => $imei1),'order' => 'id desc','recursive'=>-1));
			//pr($mobilePrices);die;
		}else{
			$mobilePrices_query = $this->MobilePrices->find('all',array('conditions'=>array('MobilePrices.brand_id'=>$mobilePurchaseBrandId,'MobilePrices.mobile_model_id'=>$mobilePurchaseModelId,'MobilePrices.grade'=>$mobilePurchaseGrade,'MobilePrices.locked'=>$mobilePurchaseType)));
            $mobilePrices_query = $mobilePrices_query->hydrate(false);
            if(!empty($mobilePrices_query)){
                $mobilePrices = $mobilePrices_query->first();
            }else{
                $mobilePrices = array();
            }
		}
		
		
		if(empty($mobilePrices)){
			$this->Flash->success("No pricing details found for this combination, please enter cost-price and selling-price for this combination. Brand:$brands[$mobilePurchaseBrandId], Model:$mobileModels[$mobilePurchaseModelId], Grade:$mobilePurchaseGrade & Type:$lockedUnlocked[$mobilePurchaseType]");
			return $this->redirect(array('controller'=>'mobile_re_sales','action'=>'index'));
		}
		
		$mobileCostPrice = $mobileSalePrice = $maximum_discount = "";
		//pr($mobilePrices);die;
		if(!empty($mobilePrices)){
			if($customGrade == 1){
				$mobileCostPrice = $mobilePrices['MobilePurchase']['cost_price'];
				$mobileSalePrice = $mobilePrices['MobilePurchase']['selling_price'];
				$lowestSalePrice = $mobilePrices['MobilePurchase']['lowest_selling_price'];
				$this->set(compact('lowestSalePrice'));
				//$maximum_discount = $mobilePrices['MobilePurchase']['maximum_discount'];
			}else{
				$mobileCostPrice = $mobilePrices['cost_price'];
				$mobileSalePrice = $mobilePrices['sale_price'];
				$maximum_discount = $mobilePrices['maximum_discount'];
			}
			
		}
		$this->set(compact('kiosks', 'brands','mobileModels','maximum_discount'));
		fakeblock:
		;
	}
    
    public function updateResalePayment($resaleId = ''){
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
		$users_query = $this->Users->find('list',[
                                                'keyField' => 'id',
                                                'valueField' => 'username'
                                           ]
                                    );
        $users_query = $users_query->hydrate(false);
        if(!empty($users_query)){
            $users = $users_query->toArray();
        }else{
            $users = array();
        }
		$paymentType = array('Cash' => 'Cash', 'Card' => 'Card');
		$paymentData_query = $this->MobileReSalePayments->find('all',array(
							'conditions' => array('MobileReSalePayments.mobile_re_sale_id'=>$resaleId,
												  )
                                        )
							  );
		$paymentData_query = $paymentData_query->hydrate(false);
        if(!empty($paymentData_query)){
            $paymentData = $paymentData_query->toArray();
        }else{
            $paymentData = array();
        }
        //pr($paymentData);die;
		$saleData_query = $this->MobileReSales->find('all', array('conditions' => array('MobileReSales.id' => $resaleId, 'MobileReSales.refund_status IS NULL')));
        $saleData_query = $saleData_query->hydrate(false);
        if(!empty($saleData_query)){
            $saleData = $saleData_query->first();
        }else{
            $saleData = array();
        }
		//pr($saleData);die;
		if((int)$saleData['discounted_price'] && $saleData['discounted_price'] > 0){
			$saleAmount = $saleData['discounted_price'];
		}else{
			$saleAmount = $saleData['selling_price'];
		}
		
		//$currentTime = $this->MobileReSalePayment->query('SELECT CURDATE() as timeDate');
		$conn = ConnectionManager::get('default');
        $stmt = $conn->execute('SELECT CURDATE() as timeDate'); 
        $currentTime = $stmt ->fetchAll('assoc');
		$currentDate = strtotime($currentTime[0]['timeDate']);
		//$checkTime = strtotime('-24 hours',$time);
		if(count($paymentData) && $this->request->session()->read('Auth.User.group_id') == KIOSK_USERS){
			$created = getdate(strtotime($paymentData['0']['created']));
			$curDate =  $created["year"]."-".$created["mon"]."-".$created["mday"];
			$createdTime = strtotime($curDate);
			if($currentDate != $createdTime){
				$this->Flash->success('Payment can only be updated within same day!');
				return $this->redirect(array('action' => 'index'));
				die;
			}
		}
		if ($this->request->is(array('post', 'put'))){
            //pr($this->request->data);die;
			if(array_key_exists('cancel',$this->request->data)){
					$this->Flash->error('You have cancelled transaction!');
					return $this->redirect(array('controller' => 'mobile_re_sales','action' => 'index'));
					die;
				}
			if(is_array($this->request->data) && array_key_exists('UpdatePayment',$this->request->data) && count($this->request->data['UpdatePayment'])){
					$totalAmount = 0;
					$addedAmount = 0;
					$updatedPaymentData = $this->request->data['UpdatePayment'];
					//card or cash options
					$updatedAmountData = $this->request->data['updated_amount'];
					//card or carsh amounts
					$sale_amount = $this->request->data['sale_amount'];
					//total updated amount
					if(array_key_exists('added_amount',$this->request->data)){
						$addedAmount = $this->request->data['added_amount'];
					}
					//if new row added for amount
					foreach($updatedPaymentData as $paymentId => $paymentMode){
						$totalAmount += $updatedAmountData[$paymentId];
					}
					$totalAmount = $addedAmount + $totalAmount;
					if($totalAmount != $sale_amount){
						//validation check
						$this->Flash->error('Payment could not be updated!');
						return $this->redirect(array('action' => 'update_resale_payment', $resaleId));
						die;
					}
					$saveAdminPayment = 0;
					//****saving newly added payment amount
					if(array_key_exists('added_amount',$this->request->data) && is_numeric($this->request->data['added_amount']) && $this->request->data['added_amount'] > 0){
						$paymntData_query = $this->MobileReSalePayments->find('all',array(
																				'conditions' => array('MobileReSalePayments.mobile_re_sale_id'=>$resaleId)
																			)
																);
                        $paymntData_query = $paymntData_query->hydrate(false);
                        if(!empty($paymntData_query)){
                            $paymntData = $paymntData_query->first();
                        }else{
                            $paymntData = array();
                        }
						//unsetting the unrequired fields
						unset($paymntData['id']);
						unset($paymntData['payment_method']);
						unset($paymntData['amount']);
						//unset($paymntData['created']);
						unset($paymntData['modified']);
						
						//adding new fields
						//pr($this->request->data);die;
						$paymntData['payment_method'] = $this->request->data['new_change_mode'];
						$paymntData['amount'] = $this->request->data['added_amount'];
                        $get_id = $this->MobileReSalePayments->newEntity();
                        $patch_entity = $this->MobileReSalePayments->patchEntity($get_id,$paymntData);
						if($this->MobileReSalePayments->save($patch_entity)){
							$saveAdminPayment++;
						}
					}
					 
					// saving new added payment till here*****
					$sale_amount = $this->request->data['sale_amount'];
					foreach($updatedPaymentData as $paymentId => $paymentMode){
						$paymentDetailData = array(
													'id' => $paymentId,
													'payment_method' => $paymentMode,
													'amount' => $updatedAmountData[$paymentId]
													);
                        $getId = $this->MobileReSalePayments->get($paymentId);
						$patchEntity = $this->MobileReSalePayments->patchEntity($getId,$paymentDetailData);
						if($this->MobileReSalePayments->save($patchEntity)){
							$saveAdminPayment++;
						}
					}
					if($saveAdminPayment > 0){
						$this->Flash->success('Payment has been successfully updated!');
						return $this->redirect(array('controller' => 'mobile_re_sales','action' => 'index'));
					}else{
						$this->Flash->error('Payment could not be updated!');
						return $this->redirect(array('action' => 'update_resale_payment',$resaleId));
					}
				}
			}
			$this->set(compact('paymentData','paymentType','kiosks','users','saleAmount'));
	}
	
	public function add($mobilePurchaseId = "",$pmt_identifier ="") {
		  $resaleEntity = $this->MobileReSales->newEntity();
		  $this->set(compact('resaleEntity'));
		$terms_resale = $this->setting['terms_resale'];
		$countryOptions = Configure::read('uk_non_uk');
		$this->set(compact('countryOptions'));
		$mobilePurchaseData_query = $this->MobilePurchases->find('all', array(
																		 'conditions' => array('MobilePurchases.id' => $mobilePurchaseId)
																		)
														  );
		$mobilePurchaseData_query = $mobilePurchaseData_query->hydrate(false);
		if(!empty($mobilePurchaseData_query)){
		  $mobilePurchaseData = $mobilePurchaseData_query->first();
		}else{
		  $mobilePurchaseData = array();
		}
		//pr($mobilePurchaseData);die;
		if(empty($mobilePurchaseData)){
			$this->Flash->error("Please choose a valid mobile purchase id");
			return $this->redirect(array('controller'=>'mobile_purchases','action'=>'index'));
		}
		$lockedUnlocked = array('0'=>'Unlocked','1');
		$mobileModels_query = $this->MobileModels->find('list',array(
															   'keyField' => 'id',
															   'valueField' => 'model',
										    'conditions'=>array('MobileModels.status'=>1)
										    )
								       );
		$mobileModels_query = $mobileModels_query->hydrate(false);
		if(!empty($mobileModels_query)){
		  $mobileModels = $mobileModels_query->toArray();
		}else{
		  $mobileModels = array();
		}
		$kiosks_query = $this->MobileReSales->Kiosks->find('list');
		$kiosks_query = $kiosks_query->hydrate(false);
		if(!empty($kiosks_query)){
		  $kiosks = $kiosks_query->toArray();
		}else{
		  $kiosks = array();
		}
		$brands_query = $this->MobileReSales->Brands->find('list', array(
										'keyField' => 'id',
										'valueField' => 'brand',
									 'conditions' => array('Brands.status' => 1)
									)
							   );
		$brands_query = $brands_query->hydrate(false);
		if(!empty($brands_query)){
		  $brands = $brands_query->toArray();
		}else{
		  $brands = array();
		}
		$mobilePurchaseBrandId = $mobilePurchaseData['brand_id'];
		$mobilePurchaseModelId = $mobilePurchaseData['mobile_model_id'];
		$mobilePurchaseGrade = $mobilePurchaseData['grade'];
		$mobilePurchaseType = $mobilePurchaseData['type'];
		$networks_query = $this->Networks->find('list',array(
													   'keyField' => 'id',
													   'valueField' => 'name',
													   )
										  );
		$networks_query = $networks_query->hydrate(false);
		if(!empty($networks_query)){
		  $networks = $networks_query->toArray();
		}else{
		  $networks = array();
		}
		if($mobilePurchaseData['purchase_status'] == 1 && $mobilePurchaseData['custom_grades'] == 1){
			$mobilePrices['MobilePrice'] = array(
													'brand_id' => $mobilePurchaseBrandId,
													'mobile_model_id' => $mobilePurchaseModelId,
													'grade' => $mobilePurchaseData['grade'],
													'locked' => $mobilePurchaseType,
													'sale_price' => $mobilePurchaseData['selling_price'],
													'lowest_selling_price' => $mobilePurchaseData['lowest_selling_price'],
													'cost_price' => $mobilePurchaseData['cost_price'],
													'maximum_discount' => 0, //not set for custom grade case
												 );
		}else{
			$mobilePrices_query = $this->MobilePrices->find('all',array(
															   'conditions' => array(
																					 'MobilePrices.brand_id' => $mobilePurchaseBrandId,
																					 'MobilePrices.mobile_model_id' => $mobilePurchaseModelId,
																					 'MobilePrices.grade' => $mobilePurchaseGrade,
																					 'MobilePrices.locked' => $mobilePurchaseType
																					 ))
												 );
			$mobilePrices_query = $mobilePrices_query->hydrate(false);
			if(!empty($mobilePrices_query)){
			   $mobilePrices = $mobilePrices_query->first();
			}else{
			   $mobilePrices = array();
			}
			//pr($mobilePrices);
		}
		
		if(empty($mobilePrices)){
			$this->Flash->error("No pricing details found for this combination, please enter cost-price and selling-price for this combination. Brand:$brands[$mobilePurchaseBrandId], Model:$mobileModels[$mobilePurchaseModelId], Grade:$mobilePurchaseGrade & Type:$lockedUnlocked[$mobilePurchaseType]");
			return $this->redirect(array('controller'=>'mobile_purchases','action'=>'index'));
		}
		
		if(!empty($mobilePrices)){
			$mobileCostPrice = $mobilePrices['cost_price'];
			
			//change on 25.03.2016 we are showing top up price at the time of selling if exists in mobile purchase
			if(!empty($mobilePurchaseData['topedup_price']) && is_numeric($mobilePurchaseData['topedup_price'])){
				$mobileCostPrice = $mobilePurchaseData['topedup_price'];
				
			}
			
			$mobileSalePrice = $mobilePrices['sale_price'];
			$maximum_discount = $mobilePrices['maximum_discount'];
		}
		
		$this->set(compact('mobilePurchaseData'));
		$this->set(compact('kiosks', 'brands','mobileModels','mobileCostPrice','mobileSalePrice','maximum_discount','networks','terms_resale'));
		
		if ($this->request->is('post') || $this->request->Session()->read('resale_payment_confirmation.resale_payment_status') == $mobilePurchaseId) {
			if($this->request->is('post')){
				$cust_data = $this->request->data['MobileReSale'];
				$this->loadModel('RetailCustomer');
				$countDuplicate_query = $this->RetailCustomers->find('all', array('conditions' => array('RetailCustomers.email' => $cust_data['customer_email'])));
				$countDuplicate_query = $countDuplicate_query->hydrate(false);
				if(!empty($countDuplicate_query)){
					$countDuplicate = $countDuplicate_query->first();
				}else{
					$countDuplicate  = array();
				}
				$userId = $this->request->Session()->read('Auth.User.id');
				$customer_data = array(
												'kiosk_id' => $cust_data["kiosk_id"],
												'fname' => $cust_data['customer_fname'],
												'lname' => $cust_data['customer_lname'],
												'mobile' => $cust_data['customer_contact'],
												'email' => $cust_data['customer_email'],
												'zip' => $cust_data['zip'],
												'address_1' => $cust_data['customer_address_1'],
												'address_2' => $cust_data['customer_address_2'],
												'city' => $cust_data['city'],
												'state' => $cust_data['state'],
												'created_by' => $userId
											   );
				if(count($countDuplicate) == 0){
						//pr($customer_data);die;
						$RetailCustomersEntity = $this->RetailCustomers->newEntity($customer_data,['validate' => false]);
						$RetailCustomersEntity = $this->RetailCustomers->patchEntity($RetailCustomersEntity,$customer_data,['validate' => false]);
						$this->RetailCustomers->save($RetailCustomersEntity);
						$custmor_id = $RetailCustomersEntity->id;
				}else{
						$custmor_id =  $countDuplicate["id"];
						$RetailCustomersEntity = $this->RetailCustomers->get($custmor_id);
						
						$RetailCustomersEntity = $this->RetailCustomers->patchEntity($RetailCustomersEntity,$customer_data,['validate' => false]);
						$this->RetailCustomers->save($RetailCustomersEntity);
				}
			}
			//deleting session id payment_confirmation as we no longer need it after entering this loop
			$this->request->Session()->delete('resale_payment_confirmation');
			
			if(count($this->request->data)){
				$costPrice = $this->request['data']['MobileReSale']['cost_price'];
				$sellingPrice = $this->request['data']['MobileReSale']['selling_price'];
				
				if($costPrice>$sellingPrice){
					$this->Flash->error("Selling Price can not be less than Cost Price");
					return $this->redirect(array('action' => 'add',$mobilePurchaseId));;
				}
			}
			
			if(count($this->request->data)){
				//change on 25.03.2016 AuthComponent::user('group_id') == KIOSK_USERS &&
				//need to check if admin can also sell the mobile
				$this->request->data['MobileReSale']['prchseId'] = $mobilePurchaseId;//for redirection of session, using in resale_payment
				$this->request->data['MobileReSale']['retail_customer_id'] = $custmor_id;
				$this->request->Session()->write('resale_data_session',$this->request->data);
				return $this->redirect(array('action' => 'resale_payment'));
				die;
			}elseif($this->Session->read('resale_data_session')){
				$this->request->data = $this->request->Session()->read('resale_data_session');
			}
			
			//deleting the session, as it is no longer required
			$this->request->Session()->delete('resale_data_session');
			
			$iemi = $this->request['data']['MobileReSale']['imei'].$this->request['data']['MobileReSale']['imei1'];
			if($this->request->session()->read('Auth.User.group_id') == ADMINISTRATORS || $this->request->session()->read('Auth.User.group_id') == FRANCHISE_OWNER){
				$this->request->data['MobileReSale']['kiosk_id']=0;
			}
			
			if(!array_key_exists('retail_customer_id',$this->request['data']['MobileReSale'])){
				//if by chance we do not get retail_customer_id throug the front end
				$customerData_query = $this->RetailCustomers->find('all', array(
								'conditions' => array(
											 'OR' => array('RetailCustomers.mobile' => $this->request['data']['MobileReSale']['customer_contact'],
														   'LOWER(RetailCustomers.email)' => $this->request['data']['MobileReSale']['customer_email']) 
													  ),
								 'order' => 'RetailCustomers.id desc'
							 )
						 );
				$customerData_query = $customerData_query->hydrate(false);
				if(!empty($customerData_query)){
					$customerData = $customerData_query->toArray();
				}else{
					$customerData = array();
				}
				if(count($customerData)){
					$this->request->data['MobileReSale']['retail_customer_id'] = $customerData['id'];
				}
			}
			$this->request->data['MobileReSale']['mobile_purchase_id']=$mobilePurchaseId;
			$this->request->data['MobileReSale']['imei']=$iemi;
			
			$this->request->data['MobileReSale']['user_id'] = $this->request->session()->read('Auth.User.id');
			//pr($this->request);die;
			$identifier = $this->request->params['pass'][1];
			$customGrade = $this->request->data['MobileReSale']['custom_grade'];
			if($customGrade == 1){
			   echo "hi";die;
				$this->MobileBlkReSale->create();
				if($this->MobileBlkReSale->save($this->request->data['MobileReSale'])){
					$mobileResaleId = $this->MobileReSale->id;
					$this->MobilePurchase->id = $mobilePurchaseId;
					$this->MobilePurchase->saveField('status',1);
					$this->MobileBlkReSalePayment->updateAll(array('mobile_re_sale_id' => "'$mobileResaleId'"),array('MobileBlkReSalePayment.mobile_purchase_id' => $mobilePurchaseId, 'pmt_identifier' => $identifier));
					
					$query = "UPDATE `mobile_blk_re_sale_payments` SET `mobile_blk_re_sale_id` = '$mobileResaleId' WHERE `mobile_purchase_id` = $mobilePurchaseId AND `pmt_identifier` = '$identifier'";
					$this->MobileBlkReSalePayment->query($query);
					//Start: checking if updated for sure otherwise deleting generate sale
					$paymentDetails = $this->MobileBlkReSalePayment->find('all', array(
													'conditions' => array(
																		  'MobileBlkReSalePayment.mobile_blk_re_sale_id' => $mobileResaleId,
																		  'MobileBlkReSalePayment.pmt_identifier' => $pmt_identifier,
																		  ),
																));
					if(empty($paymentDetails) || count($paymentDetails) == 0){
						//delete sale and redirect to index page
						$this->MobileBlkReSalePayment->id = $mobileResaleId;
						$this->MobileBlkReSalePayment->delete();
						$alterQuery = "ALTER TABLE `mobile_re_sale_payments` AUTO_INCREMENT = $mobileResaleId";
						$this->MobileBlkReSalePayment->query($alterQuery);
						$this->Session->setFlash("Failed to fire query: $query. <br/>For this reason generated sale delelted for Mobile Blk Resale ID: {$mobileResaleId} and receipt counter is again set to $mobileResaleId for avoiding out of sequences problem!");
						return $this->redirect(array('action' => 'index'));
					}
					//End: checking if updated for sure otherwise deleting generate sale
					$kiosk_id = $this->Session->read('kiosk_id');
					$kioskaddress = $this->Kiosk->find('first',array(
								'fields' => array('Kiosk.name','Kiosk.address_1','Kiosk.address_2', 'Kiosk.city','Kiosk.state','Kiosk.country','Kiosk.zip' ),
								'conditions'=> array('Kiosk.id' => $kiosk_id),
								'recursive' => -1
								));
					
					$mobileresaledata = $this->request->data['MobileReSale'];
					//pr($mobileresaledata);die;
					$phone_resale_email_message = $this->setting['phone_resale_email_message'];
					 $currency = $this->setting['currency_symbol'];
					$imei = $mobileresaledata['imei'];
					if(array_key_exists('discounted_price',$mobileresaledata) && $mobileresaledata['discounted_price']> 0){
						 $sellingprice = $mobileresaledata['discounted_price'];
					}else{
						$sellingprice = $mobileresaledata['selling_price'];
					}
					//$sellingprice = $mobileresaledata['selling_price'];
					$brand = $brands[$mobileresaledata['brand_id']];
					$model = $mobileModels[$mobileresaledata['mobile_model_id']];
					$resaleDetails = " (IMEI:".$imei.",\t"."Brand:".$brand.",\t"."Model:".$model.",\t"."Selling Price:".$currency.$sellingprice .").";
					//pr($resaleDetails);die;
					$mobileTransferLogData = array(
							'mobile_purchase_id' => $mobilePurchaseId,
							'kiosk_id' => $this->request->data['MobileReSale']['kiosk_id'],
							'mobile_resale_id' => $mobileResaleId,
							'imei' => $this->request->data['MobileReSale']['imei'],
							'status' => 1,
							'network_id' => $this->request->data['MobileReSale']['network_id'],
							'grade' => $this->request->data['MobileReSale']['grade'],
							'type' => $this->request->data['MobileReSale']['type']
							);
					$this->MobileBlkTransferLog->clear();
					$this->MobileBlkTransferLog->create();
					$this->MobileBlkTransferLog->save($mobileTransferLogData);
					
					if(!empty($mobileresaledata)){
						$emailSender = Configure::read('EMAIL_SENDER');
						$Email = new Email();
						$Email->config('default');
						$Email->viewVars(array( 'mobileresaledata'=>$mobileresaledata,'mobileModels' => $mobileModels,"kiosks" => $kiosks,'kiosk_id' => $kiosk_id, 'brands' => $brands ,'kioskaddress' => $kioskaddress,'countryOptions' => $countryOptions,'resaleDetails' => $resaleDetails,'phone_resale_email_message'=>$phone_resale_email_message));
						//$Email->config(array('from' => 'me@example.org', 'transport' => 'MyCustom'));
						//$Email = new CakeEmail(array('from' => 'me@example.org', 'transport' => 'MyCustom'));
						$emailTo = $mobileresaledata['customer_email'];
						$Email->template('resale_notification');
						$Email->emailFormat('both');
						$Email->to($emailTo);
						 $Email->transport(TRANSPORT);
						 $Email->from([$this->fromemail => $emailSender]);
						//$Email->sender($this->fromemail);
						$Email->subject('Mobile Purchase Details');
						$Email->send();
					}
		  
					$this->Flash->success(__('The mobile re sale has been saved.'));
					return $this->redirect(array('action' => 'index'));
				} else {
					$this->Flash->error(__('The mobile re sale could not be saved. Please, try again.'));
				}
			}else{
			   echo "bye";die;
				$this->MobileReSale->create();
				if ($this->MobileReSale->save($this->request->data)) {
					$mobileResaleId = $this->MobileReSale->id;
					$this->MobilePurchase->id = $mobilePurchaseId;
					$this->MobilePurchase->saveField('status',1);
					//$this->MobileReSalePayment->updateAll(array('mobile_re_sale_id' => "'$mobileResaleId'"),array('MobileReSalePayment.mobile_purchase_id' => $mobilePurchaseId));
					$query = "UPDATE `mobile_re_sale_payments` SET `mobile_re_sale_id` = '$mobileResaleId' WHERE `mobile_purchase_id` = $mobilePurchaseId AND `pmt_identifier` = '$identifier'";
					$this->MobileReSalePayment->query($query);
					//Start: checking if updated for sure otherwise deleting generate sale
					$paymentDetails = $this->MobileReSalePayment->find('all', array(
													'conditions' => array('MobileReSalePayment.mobile_re_sale_id' => $mobileResaleId),
																));
					if(empty($paymentDetails) || count($paymentDetails) == 0){
						//delete sale and redirect to index page
						$this->MobileReSalePayment->id = $mobileResaleId;
						$this->MobileReSalePayment->delete();
						$alterQuery = "ALTER TABLE `mobile_re_sale_payments` AUTO_INCREMENT = $mobileResaleId";
						$this->MobileReSalePayment->query($alterQuery);
						$this->Session->setFlash("Failed to fire query: $query. <br/>For this reason generated sale delelted for Mobile Resale ID: {$mobileResaleId} and receipt counter is again set to $mobileResaleId for avoiding out of sequences problem!");
						return $this->redirect(array('action' => 'index'));
					}
					//End: checking if updated for sure otherwise deleting generate sale
					$kiosk_id = $this->Session->read('kiosk_id');
					$kioskaddress = $this->Kiosk->find('first',array(
																		'fields' => array(
																						  'Kiosk.name',
																						  'Kiosk.address_1',
																						  'Kiosk.address_2',
																						  'Kiosk.city',
																						  'Kiosk.state',
																						  'Kiosk.country',
																						  'Kiosk.zip' ),
																		'conditions'=> array('Kiosk.id' => $kiosk_id),
																		'recursive' => -1
																	));
					$mobileresaledata = $this->request->data['MobileReSale'];
					$phone_resale_email_message = $this->setting['phone_resale_email_message'];
					$currency = $this->setting['currency_symbol'];
					$imei = $mobileresaledata['imei'];
					if(array_key_exists('discounted_price',$mobileresaledata) && $mobileresaledata['discounted_price']> 0){
						 $sellingprice = $mobileresaledata['discounted_price'];
					}else{
						$sellingprice = $mobileresaledata['selling_price'];
					}
					//$sellingprice = $mobileresaledata['selling_price'];
					$brand = $brands[$mobileresaledata['brand_id']];
					$model = $mobileModels[$mobileresaledata['mobile_model_id']];
					$resaleDetails = " (IMEI:".$imei.",\t"."Brand:".$brand.",\t"."Model:".$model.",\t"."Selling Price:".$currency.$sellingprice .").";
					//pr($resaleDetails);die;
					$mobileTransferLogData = array(
													'mobile_purchase_id' => $mobilePurchaseId,
													'kiosk_id' => $this->request->data['MobileReSale']['kiosk_id'],
													'mobile_resale_id' => $mobileResaleId,
													'imei' => $this->request->data['MobileReSale']['imei'],
													'status' => 1,
													'network_id' => $this->request->data['MobileReSale']['network_id'],
													'grade' => $this->request->data['MobileReSale']['grade'],
													'type' => $this->request->data['MobileReSale']['type']
												);
					$this->MobileTransferLog->clear();
					$this->MobileTransferLog->create();
					$this->MobileTransferLog->save($mobileTransferLogData);
					
					if(!empty($mobileresaledata)){
						$emailSender = Configure::read('EMAIL_SENDER');
						$Email = new CakeEmail();
						$Email->config('default');
						$Email->viewVars(array( 'mobileresaledata'=>$mobileresaledata,'mobileModels' => $mobileModels,"kiosks" => $kiosks,'kiosk_id' => $kiosk_id, 'brands' => $brands ,'kioskaddress' => $kioskaddress,'countryOptions' => $countryOptions,'resaleDetails' => $resaleDetails,'phone_resale_email_message'=>$phone_resale_email_message));
						//$Email->config(array('from' => 'me@example.org', 'transport' => 'MyCustom'));
						//$Email = new CakeEmail(array('from' => 'me@example.org', 'transport' => 'MyCustom'));
						$emailTo = $mobileresaledata['customer_email'];
						$Email->template('resale_notification');
						$Email->emailFormat('both');
						$Email->to($emailTo);
						 $Email->transport(TRANSPORT);
						 $Email->from([$this->fromemail => $emailSender]);
						//$Email->sender($this->fromemail);
						$Email->subject('Mobile Purchase Details');
						$Email->send();
					}
		  
					$this->Session->setFlash(__('The mobile re sale has been saved.'));
					return $this->redirect(array('action' => 'index'));
				} else {
					$this->Session->setFlash(__('The mobile re sale could not be saved. Please, try again.'));
				}
			}
			
		}
	}
	
	public function resalePayment(){
		//pr($this->request);
		//pr($this->Session->read('received_reprd_from_tech_data'));
		//pr($_SESSION);
		//$this->RepairPayment->query('TRUNCATE `repair_payments`');
		//
		//**commented the below code on 25.03.2016, as we are not sure if admin can also sell!
		//if(AuthComponent::user('group_id') != KIOSK_USERS){
		//	$this->Session->setFlash('Only kiosk user can authorize/enter payment');
		//	return $this->redirect(array('action' => 'index'));
		//}
		//pr($this->RepairPayment->find('all'));
		$setting = $this->setting;
		$paymentType = array('Cash' => 'Cash', 'Card' => 'Card');
		$this->set(compact('paymentType','setting'));
		
		if(is_array($this->request->Session()->read('resale_data_session'))){
			$basket = "resale_data_session";
			$session_basket = $this->request->Session()->read('resale_data_session');
			$sessionResaleId = $session_basket['MobileReSale']['prchseId'];
		}else{
			return $this->redirect(array('action' => 'index'));
			die;
		}
		
		if ($this->request->is(array('post', 'put'))) {
			//pr($_SESSION);
			//pr($this->request);die;
			if(array_key_exists('cancel',$this->request->data)){
				$this->Session->delete($basket);
				return $this->redirect(array('controller'=>'mobile_re_sales','action'=>'add',$sessionResaleId));
				die;
			}
			$amountToPay = $this->request['data']['final_amount'];
			$totalPaymentAmount = 0;
			$amountDesc = array();
			$error = '';
			$errorStr = '';
			$customGrade = '';
			$session_basket = $this->Session->read('resale_data_session');
			$customGrade = $session_basket['MobileReSale']['custom_grade'];
			
			foreach($this->request['data']['Payment']['Amount'] as $key => $paymentAmount){
				$totalPaymentAmount+= $paymentAmount;
				$paymentDescription = $this->request['data']['Payment']['Description'][$key];
				
				//if(empty($paymentDescription) && !empty($paymentAmount)){
				//	$error[] = "Sale could not be created. Payment description must be entered";
				//	break;
				//}
			}
			
			foreach($this->request['data']['Payment']['Payment_Method'] as $key => $paymentMethod){
				/*if($paymentMethod=="On Credit" and $countCycles>1){
					$error[] = "'On Credit' payment method cannot be clubbed with any other. Either choose 'On Credit' or the other payment methods";
				}else*/if($totalPaymentAmount<$amountToPay){
					$error[] = "Amount must be equivalent to &#163; {$amountToPay}. Please try again";
					break;
				}elseif($totalPaymentAmount>$amountToPay){
					$error[] = "Amount must be equivalent to &#163; {$amountToPay}. Please try again";
					break;
				}
			}
			if(!empty($error)){
				$errorStr = implode("<br/>",$error);
				$this->Session->setFlash("$errorStr");
				return $this->redirect(array('action'=>'resale_payment'));
			}
			
			$counter = 0;
			foreach($this->request['data']['Payment']['Amount'] as $key => $paymentAmount){
				$paymentMethod = $this->request['data']['Payment']['Payment_Method'][$key];
				$paymentDescription = $this->request['data']['Payment']['Description'][$key];
				$payment_status = 1;//since we do not have option for credit here, so just sending status 1 as payment done
				
				//added on 25.03.2016
				$kskId = $this->Session->read('kiosk_id');
				if(empty($kskId)){
					$kskId = 10000;
				}
				$pmt_identifier = time();
				if(!empty($paymentAmount)){// && $paymentDescription
					$resalePaymentData = array(
							'kiosk_id' => $kskId,
							'user_id' => $this->Auth->user('id'),
							'mobile_purchase_id' => $sessionResaleId,
							'payment_method' => $paymentMethod,
							'description' => $paymentDescription,
							'amount' => $paymentAmount,
							'payment_status' => $payment_status,
							'pmt_identifier' => $pmt_identifier,
							'status' => 1,//this 1 currently does not have any relevance
							   );
					if($customGrade == 1){
						$this->MobileBlkReSalePayment->clear;
						$this->MobileBlkReSalePayment->create();
						$sessionBskt = array();
						if($this->MobileBlkReSalePayment->save($resalePaymentData)){
							$counter++;
							$sessionBskt['resale_payment_status'] = $sessionResaleId;
							//here we are sending payment status in session to unlock edit as an identifier for successful payment
							$this->Session->write('resale_payment_confirmation',$sessionBskt);
						}
					}else{
						$this->MobileReSalePayment->clear;
						$this->MobileReSalePayment->create();
						$sessionBskt = array();
						if($this->MobileReSalePayment->save($resalePaymentData)){
							$counter++;
							$sessionBskt['resale_payment_status'] = $sessionResaleId;
							//here we are sending payment status in session to unlock edit as an identifier for successful payment
							$this->Session->write('resale_payment_confirmation',$sessionBskt);
						}
					}
					
				}
			}
			if($counter>0){
				return $this->redirect(array('controller'=>'mobile_re_sales','action'=>'add',$sessionResaleId,$pmt_identifier));;
			}else{
				$flashMessage = ("Sale could not be created. Please try again");
				$this->Session->delete($basket);
				$this->Session->setFlash($flashMessage);
				return $this->redirect(array('controller'=>'mobile_re_sales','action'=>'add',$sessionResaleId));
			}
		}
	}
	
	public function cancelAjax(){
		unset($_SESSION['resale_data_session']);
		echo json_encode(array('status' => 'ok'));
		die;
	}
	
	public function doPayment(){
		if(!empty($this->request->query) && array_key_exists('resale_data_session',$_SESSION)){
			if(!empty($_SESSION['resale_data_session'])){
				$basket = $_SESSION['resale_data_session'];
				unset($_SESSION['resale_data_session']);
			}else{
				echo json_encode(array('error' =>'session is empty'));die;
			}
			if(array_key_exists('MobileReSale',$basket)){
				$customGrade = $basket['MobileReSale']['custom_grade'];
			}else{
				$customGrade = $basket['MobileBlkReSale']['custom_grade'];
			}
			
			$final_amount = $this->request->query['final_amount'];
			$payment_1 = $this->request->query['payment_1'];
			$payment_2 = $this->request->query['payment_2'];
			$method_1 = $this->request->query['method_1'];
			$method_2 = $this->request->query['method_2'];
			$part_time = $this->request->query['part_time'];
			if(array_key_exists('purchase_id',$this->request->query) && !empty($this->request->query['purchase_id'])){
				$purchase_id = $this->request->query['purchase_id'];
			}else{
				echo json_encode(array('error' =>'no purchase id found'));die;
			}
			
			$kskId = $this->request->Session()->read('kiosk_id');
            if($kskId == ""){
                $kskId = 10000;
            }
			$user_id = $this->request->session()->read('Auth.User.id');
			$pmt_identifier = time();
			$payment_status = 1;
			$counter = 0;
			if($part_time == 1){ // part time
                
				if($payment_1 + $payment_2 == $final_amount){
					$resalePaymentData = array(
							'kiosk_id' => $kskId,
							'user_id' => $user_id,
							'mobile_purchase_id' => $purchase_id,
							'payment_method' => $method_1,
							'amount' => $payment_1,
							'payment_status' => $payment_status,
							'pmt_identifier' => $pmt_identifier,
							'status' => 1,//this 1 currently does not have any relevance
							   );
					
					if($customGrade == 1){
						$MobileBlkReSalePaymentsEntity = $this->MobileBlkReSalePayments->newEntity($resalePaymentData,['validate' => false]);
						$MobileBlkReSalePaymentsEntity = $this->MobileBlkReSalePayments->patchEntity($MobileBlkReSalePaymentsEntity,$resalePaymentData,['validate' => false]);
						$sessionBskt = array();
						if($this->MobileBlkReSalePayments->save($MobileBlkReSalePaymentsEntity)){
							$counter++;
							//$sessionBskt['resale_payment_status'] = $purchase_id;
						}
					}else{
						$MobileReSalePaymentEntity = $this->MobileReSalePayments->newEntity($resalePaymentData,['validate' => false]);
						$MobileReSalePaymentEntity = $this->MobileReSalePayments->patchEntity($MobileReSalePaymentEntity,$resalePaymentData,['validate' => false]);
						$sessionBskt = array();
						if($this->MobileReSalePayments->save($MobileReSalePaymentEntity)){
							$counter++;
							//$sessionBskt['resale_payment_status'] = $purchase_id;
						}
					}
					
					$resalePaymentData_1 = array(
							'kiosk_id' => $kskId,
							'user_id' => $user_id,
							'mobile_purchase_id' => $purchase_id,
							'payment_method' => $method_2,
							'amount' => $payment_2,
							'payment_status' => $payment_status,
							'pmt_identifier' => $pmt_identifier,
							'status' => 1,//this 1 currently does not have any relevance
							   );
					
					if($customGrade == 1){
						$MobileBlkReSalePaymentEntity = $this->MobileBlkReSalePayments->newEntity($resalePaymentData_1,['validate' => false]);
						$MobileBlkReSalePaymentEntity = $this->MobileBlkReSalePayments->patchEntity($MobileBlkReSalePaymentEntity,$resalePaymentData_1,['validate' => false]);
						$sessionBskt = array();
						if($this->MobileBlkReSalePayments->save($MobileBlkReSalePaymentEntity)){
							$counter++;
							//$sessionBskt['resale_payment_status'] = $purchase_id;
						}
					}else{
						$MobileReSalePaymentsEntity = $this->MobileReSalePayments->newEntity($resalePaymentData_1,['validate' => false]);
						$MobileReSalePaymentsEntity = $this->MobileReSalePayments->patchEntity($MobileReSalePaymentsEntity,$resalePaymentData_1,['validate' => false]);
						$sessionBskt = array();
						if($this->MobileReSalePayments->save($MobileReSalePaymentsEntity)){
							$counter++;
							//$sessionBskt['resale_payment_status'] = $purchase_id;
						}
					}
					
					
				}else{
					echo json_encode(array('error' => 'amount is not matching'));
				}
			}else{ // full time
				if($payment_1 == $final_amount){
					$resalePaymentData = array(
							'kiosk_id' => $kskId,
							'user_id' => $user_id,
							'mobile_purchase_id' => $purchase_id,
							'payment_method' => $method_1,
							'amount' => $payment_1,
							'payment_status' => $payment_status,
							'pmt_identifier' => $pmt_identifier,
							'status' => 1,//this 1 currently does not have any relevance
							   );
					if($customGrade == 1){
                        //pr($resalePaymentData);die;
						$MobileBlkReSalePaymentEntity = $this->MobileBlkReSalePayments->newEntity($resalePaymentData,['validate' => false]);
						$MobileBlkReSalePaymentEntity = $this->MobileBlkReSalePayments->patchEntity($MobileBlkReSalePaymentEntity,$resalePaymentData,['validate' => false]);
						$sessionBskt = array();
						if($this->MobileBlkReSalePayments->save($MobileBlkReSalePaymentEntity)){
							$counter++;
							//$sessionBskt['resale_payment_status'] = $purchase_id;
						}
					}else{
						$MobileReSalePaymentsEntity = $this->MobileReSalePayments->newEntity($resalePaymentData,['validate' => false]);
						$MobileReSalePaymentsEntity = $this->MobileReSalePayments->patchEntity($MobileReSalePaymentsEntity,$resalePaymentData,['validate' => false]);
						$sessionBskt = array();
						if($this->MobileReSalePayments->save($MobileReSalePaymentsEntity,['validate' => false])){
							$counter++;
							//$sessionBskt['resale_payment_status'] = $purchase_id;
						}else{
						 //debug($MobileReSalePaymentsEntity->errors());die;
						}
					}
				}else{
					echo json_encode(array('error' => 'amount is not matching'));
				}
			}
			if($counter >0){
				$this->save_sale($basket,$kskId,$user_id,$purchase_id,$customGrade,$pmt_identifier);die;	
			}else{
				echo json_encode(array('error' =>'some error on step one' ));die;
			}
		}else{
			echo json_encode(array('error' =>'either no session or query is empty' ));die;
		}
	}
	
	
	public function save_sale($basket,$kiosk_id,$user_id,$purchase_id,$customGrade,$pmt_identifier){
		if(!empty($basket) && !empty($purchase_id)){
            
			$mobileModels_query = $this->MobileModels->find('list',array(
																		 'keyField' => 'id',
																		 'valueField' => 'model',
																		   'conditions'=>array('MobileModels.status'=>1)
									)
							   );
			$mobileModels_query = $mobileModels_query->hydrate(false);
			if(!empty($mobileModels_query)){
			   $mobileModels = $mobileModels_query->toArray();
			}else{
			   $mobileModels = array();
			}
			$kiosks_query = $this->MobileReSales->Kiosks->find('list');
			$kiosks_query = $kiosks_query->hydrate(false);
			if(!empty($kiosks_query)){
			   $kiosks = $kiosks_query->toArray();
			}else{
			   $kiosks = array();
			}
			$brands_query = $this->MobileReSales->Brands->find('list', array(
										'keyField' => 'id',
										'valueField' => 'brand',
										 'conditions' => array('Brands.status' => 1)
										)
								   );
			$brands_query = $brands_query->hydrate(false);
			if(!empty($brands_query)){
			   $brands = $brands_query->toArray();
			}else{
			   $brands = array();
			}
			$countryOptions = Configure::read('uk_non_uk');
			if($customGrade == 1){
                if($basket['MobileBlkReSale']['kiosk_id'] == ""){
                     $basket['MobileBlkReSale']['kiosk_id'] = 10000;
                 }
				$iemi = $basket['MobileBlkReSale']['imei'].$basket['MobileBlkReSale']['imei1'];
					$basket['MobileBlkReSale']['mobile_purchase_id']=$purchase_id;
					$basket['MobileBlkReSale']['imei']=$iemi;
					$basket['MobileBlkReSale']['user_id'] = $user_id;
					$basket['MobileBlkReSale']['user_id'] = $user_id;
					//$basket['MobileBlkReSale']['kiosk_id'] = 3;
					$MobileBlkReSalesEntity = $this->MobileBlkReSales->newEntity($basket['MobileBlkReSale'],['validate' => false]);
					$MobileBlkReSalesEntity = $this->MobileBlkReSales->patchEntity($MobileBlkReSalesEntity,$basket['MobileBlkReSale'],['validate' => false]);
				if($this->MobileBlkReSales->save($MobileBlkReSalesEntity)){
					$mobileResaleId = $MobileBlkReSalesEntity->id;
					$MobilePurchasesEntity = $this->MobilePurchases->get($purchase_id);
					$data = array('status' => 1);
					$MobilePurchasesEntity = $this->MobilePurchases->patchEntity($MobilePurchasesEntity,$data,['validate' => false]);
					$this->MobilePurchases->save($MobilePurchasesEntity);
					//$this->MobileBlkReSalePayment->updateAll(array('mobile_re_sale_id' => "'$mobileResaleId'"),array('MobileBlkReSalePayment.mobile_purchase_id' => $mobilePurchaseId, 'pmt_identifier' => $identifier));
					
					$query = "UPDATE `mobile_blk_re_sale_payments` SET `mobile_blk_re_sale_id` = '$mobileResaleId' WHERE `mobile_purchase_id` = $purchase_id AND `pmt_identifier` = '$pmt_identifier'";
					$conn = ConnectionManager::get('default');
					$stmt = $conn->execute($query); 
					//Start: checking if updated for sure otherwise deleting generate sale
					$paymentDetails_query = $this->MobileBlkReSalePayments->find('all', array(
													'conditions' => array(
																		  'MobileBlkReSalePayments.mobile_blk_re_sale_id' => $mobileResaleId,
																		  'MobileBlkReSalePayments.pmt_identifier' => $pmt_identifier,
																		  ),
																));
					$paymentDetails_query = $paymentDetails_query->hydrate(false);
					if(!empty($paymentDetails_query)){
						 $paymentDetails = $paymentDetails_query->toArray();
					}else{
						 $paymentDetails = array();
					}
					if(empty($paymentDetails) || count($paymentDetails) == 0){
						//delete sale and redirect to index page
						$MobileBlkReSalePaymentEntity = $this->MobileBlkReSalePayments->get($mobileResaleId);
						$this->MobileBlkReSalePayment->delete($MobileBlkReSalePaymentEntity);
						$alterQuery = "ALTER TABLE `mobile_re_sale_payments` AUTO_INCREMENT = $mobileResaleId";
						$conn = ConnectionManager::get('default');
						 $stmt = $conn->execute($alterQuery); 
						$msg = "Failed to fire query: $query. <br/>For this reason generated sale delelted for Mobile Blk Resale ID: {$mobileResaleId} and receipt counter is again set to $mobileResaleId for avoiding out of sequences problem!";
						echo json_encode(array('error' => $msg));die;
					}
					//End: checking if updated for sure otherwise deleting generate sale
					$kioskaddress_query = $this->Kiosks->find('all',array(
								'fields' => array('Kiosks.name','Kiosks.address_1','Kiosks.address_2', 'Kiosks.city','Kiosks.state','Kiosks.country','Kiosks.zip' ),
								'conditions'=> array('Kiosks.id' => $kiosk_id)
								));
					$kioskaddress_query = $kioskaddress_query->hydrate(false);
					if(!empty($kioskaddress_query)){
						 $kioskaddress = $kioskaddress_query->first();
					}else{
						 $kioskaddress = array();
					}
					
					$mobileresaledata = $basket['MobileBlkReSale'];
					//pr($mobileresaledata);die;
					$phone_resale_email_message = $this->setting['phone_resale_email_message'];
					 $currency = $this->setting['currency_symbol'];
					$imei = $mobileresaledata['imei'];
					if(array_key_exists('discounted_price',$mobileresaledata) && $mobileresaledata['discounted_price']> 0){
						 $sellingprice = $mobileresaledata['discounted_price'];
					}else{
						$sellingprice = $mobileresaledata['selling_price'];
					}
					//$sellingprice = $mobileresaledata['selling_price'];
					$brand = $brands[$mobileresaledata['brand_id']];
					$model = $mobileModels[$mobileresaledata['mobile_model_id']];
					$CURRENCY_TYPE = Configure::read('CURRENCY_TYPE');
					$resaleDetails = " (IMEI:".$imei.",\t"."Brand:".$brand.",\t"."Model:".$model.",\t"."Selling Price:".$CURRENCY_TYPE.$sellingprice .").";
					//pr($resaleDetails);die;
					$mobileTransferLogData = array(
							'mobile_purchase_id' => $purchase_id,
							'kiosk_id' => $basket['MobileBlkReSale']['kiosk_id'],
							'mobile_resale_id' => $mobileResaleId,
							'imei' => $basket['MobileBlkReSale']['imei'],
							'status' => 1,
							'user_id' => $this->request->session()->read('Auth.User.id'),
							'network_id' => $basket['MobileBlkReSale']['network_id'],
							'grade' => $basket['MobileBlkReSale']['grade'],
							'type' => $basket['MobileBlkReSale']['type']
							);
					$MobileBlkTransferLogsEntity = $this->MobileBlkTransferLogs->newEntity($mobileTransferLogData,['validate' => false]);
					$MobileBlkTransferLogsEntity = $this->MobileBlkTransferLogs->patchEntity($MobileBlkTransferLogsEntity,$mobileTransferLogData,['validate' => false]);
					$this->MobileBlkTransferLogs->save($MobileBlkTransferLogsEntity);
					$send_by_email = Configure::read('send_by_email');
					$emailSender = Configure::read('EMAIL_SENDER');
					if(!empty($mobileresaledata) && !empty($emailTo)){
						$Email = new Email();
						$Email->config('default');
						$Email->viewVars(array( 'mobileresaledata'=>$mobileresaledata,'mobileModels' => $mobileModels,"kiosks" => $kiosks,'kiosk_id' => $kiosk_id, 'brands' => $brands ,'kioskaddress' => $kioskaddress,'countryOptions' => $countryOptions,'resaleDetails' => $resaleDetails,'phone_resale_email_message'=>$phone_resale_email_message));
						//$Email->config(array('from' => 'me@example.org', 'transport' => 'MyCustom'));
						//$Email = new CakeEmail(array('from' => 'me@example.org', 'transport' => 'MyCustom'));
						$emailTo = $mobileresaledata['customer_email'];
						$Email->template('resale_notification');
						$Email->emailFormat('both');
						$Email->to($emailTo);
						 $Email->transport(TRANSPORT);
						 $Email->from([$send_by_email => $emailSender]);
					//	$Email->sender("sales@oceanstead.co.uk",'MyApp emailer');
						$Email->subject('Mobile Purchase Details');
						$Email->send();
					}
					$msg = 'The mobile re sale has been saved.';
					echo  json_encode(array('status' => $msg,'id' => $mobileResaleId));die;
				} else {
					//debug($MobileBlkReSalesEntity->errors());die;
					$msg = 'The mobile re sale could not be saved. Please, try again.';
					echo  json_encode(array('error' => $msg));die;
				}
			}else{
				if($basket['MobileReSale']['kiosk_id'] == ""){
                     $basket['MobileReSale']['kiosk_id'] = 10000;
                 }
				$iemi = $basket['MobileReSale']['imei'].$basket['MobileReSale']['imei1'];
				$basket['MobileReSale']['mobile_purchase_id']=$purchase_id;
				$basket['MobileReSale']['imei']=$iemi;
				$basket['MobileReSale']['user_id'] = $user_id;
				//$basket['MobileReSale']['kiosk_id'] = 3;
				$MobileReSalesEntity = $this->MobileReSales->newEntity($basket['MobileReSale'],['validate' => false]);
				$MobileReSalesEntity = $this->MobileReSales->patchEntity($MobileReSalesEntity,$basket['MobileReSale'],['validate' => false]);
				//pr($MobileReSalesEntity);
				if ($this->MobileReSales->save($MobileReSalesEntity,['validate'=>false])) {
					$mobileResaleId = $MobileReSalesEntity->id;
					$MobilePurchasesEntity = $this->MobilePurchases->get($purchase_id);
					$data = array('status' => 1);
					$MobilePurchasesEntity = $this->MobilePurchases->patchEntity($MobilePurchasesEntity,$data,['validate' => false]);
					$this->MobilePurchases->save($MobilePurchasesEntity);
					//$this->MobileReSalePayment->updateAll(array('mobile_re_sale_id' => "'$mobileResaleId'"),array('MobileReSalePayment.mobile_purchase_id' => $mobilePurchaseId));
					$query = "UPDATE `mobile_re_sale_payments` SET `mobile_re_sale_id` = '$mobileResaleId' WHERE `mobile_purchase_id` = $purchase_id AND `pmt_identifier` = '$pmt_identifier'";
					$conn = ConnectionManager::get('default');
					$stmt = $conn->execute($query); 
					//Start: checking if updated for sure otherwise deleting generate sale
					$paymentDetails_query = $this->MobileReSalePayments->find('all', array(
													'conditions' => array('MobileReSalePayments.mobile_re_sale_id' => $mobileResaleId),
																));
					$paymentDetails_query = $paymentDetails_query->hydrate(false);
					if(!empty($paymentDetails_query)){
						 $paymentDetails = $paymentDetails_query->toArray();
					}else{
						 $paymentDetails = array();
					}
					if(empty($paymentDetails) || count($paymentDetails) == 0){
						//delete sale and redirect to index page
						$MobileReSalePaymentsEntity = $this->MobileReSalePayments->get($mobileResaleId);
						$this->MobileReSalePayment->delete($MobileReSalePaymentsEntity);
						$alterQuery = "ALTER TABLE `mobile_re_sale_payments` AUTO_INCREMENT = $mobileResaleId";
						$conn = ConnectionManager::get('default');
						 $stmt = $conn->execute($alterQuery); 
						$msg = "Failed to fire query: $query. <br/>For this reason generated sale delelted for Mobile Resale ID: {$mobileResaleId} and receipt counter is again set to $mobileResaleId for avoiding out of sequences problem!";
						echo json_encode(array('error' => $msg));die;
					}
					//End: checking if updated for sure otherwise deleting generate sale
					$kioskaddress_query = $this->Kiosks->find('all',array(
																		'fields' => array(
																						  'Kiosks.name',
																						  'Kiosks.address_1',
																						  'Kiosks.address_2',
																						  'Kiosks.city',
																						  'Kiosks.state',
																						  'Kiosks.country',
																						  'Kiosks.zip' ),
																		'conditions'=> array('Kiosks.id' => $kiosk_id)
																	));
					$kioskaddress_query  = $kioskaddress_query->hydrate(false);
					if(!empty($kioskaddress_query)){
						 $kioskaddress = $kioskaddress_query->first();
					}else{
						 $kioskaddress = array();
					}
					$mobileresaledata = $basket['MobileReSale'];
					$phone_resale_email_message = $this->setting['phone_resale_email_message'];
					$currency = $this->setting['currency_symbol'];
					$imei = $mobileresaledata['imei'];
					if(array_key_exists('discounted_price',$mobileresaledata) && $mobileresaledata['discounted_price']> 0){
						 $sellingprice = $mobileresaledata['discounted_price'];
					}else{
						$sellingprice = $mobileresaledata['selling_price'];
					}
					
					
					//$sellingprice = $mobileresaledata['selling_price'];
					$brand = $brands[$mobileresaledata['brand_id']];
					$model = $mobileModels[$mobileresaledata['mobile_model_id']];
					$CURRENCY_TYPE = Configure::read('CURRENCY_TYPE');
					$resaleDetails = " (IMEI:".$imei.",\t"."Brand:".$brand.",\t"."Model:".$model.",\t"."Selling Price:".$CURRENCY_TYPE.$sellingprice .").";
					//pr($resaleDetails);die;
					$mobileTransferLogData = array(
													'mobile_purchase_id' => $purchase_id,
													'kiosk_id' => $basket['MobileReSale']['kiosk_id'],
													'mobile_resale_id' => $mobileResaleId,
													'imei' => $basket['MobileReSale']['imei'],
													'status' => 1,
													'user_id' => $this->request->session()->read('Auth.User.id'),
													'network_id' => $basket['MobileReSale']['network_id'],
													'grade' => $basket['MobileReSale']['grade'],
													'type' => $basket['MobileReSale']['type']
												);
					$MobileTransferLogsEntity = $this->MobileTransferLogs->newEntity($mobileTransferLogData,['validate' => false]);
					$MobileTransferLogsEntity = $this->MobileTransferLogs->patchEntity($MobileTransferLogsEntity,$mobileTransferLogData,['validate' => false]);
					$this->MobileTransferLogs->save($MobileTransferLogsEntity);
					$send_by_email = Configure::read('send_by_email');
					$emailSender = Configure::read('EMAIL_SENDER');
					if(!empty($mobileresaledata) && !empty($emailTo)){
						$Email = new Email();
						$Email->config('default');
						$Email->viewVars(array( 'mobileresaledata'=>$mobileresaledata,'mobileModels' => $mobileModels,"kiosks" => $kiosks,'kiosk_id' => $kiosk_id, 'brands' => $brands ,'kioskaddress' => $kioskaddress,'countryOptions' => $countryOptions,'resaleDetails' => $resaleDetails,'phone_resale_email_message'=>$phone_resale_email_message));
						//$Email->config(array('from' => 'me@example.org', 'transport' => 'MyCustom'));
						//$Email = new CakeEmail(array('from' => 'me@example.org', 'transport' => 'MyCustom'));
						$emailTo = $mobileresaledata['customer_email'];
						$Email->template('resale_notification');
						$Email->emailFormat('both');
						$Email->to($emailTo);
						 $Email->transport(TRANSPORT);
						 $Email->from([$send_by_email => $emailSender]);
						//$Email->sender("sales@oceanstead.co.uk",'MyApp emailer');
						$Email->subject('Mobile Purchase Details');
						$Email->send();
					}
					$msg = 'The mobile re sale has been saved.';
					echo json_encode(array('status' => $msg,'id' => $mobileResaleId));die;
				} else {
                   // debug($MobileReSalesEntity->errors());die;
					$msg = 'The mobile re sale could not be saved. Please, try again.';
					echo json_encode(array('status' => $msg));die;
				}
			}
		}else{
			echo json_encode(array('error' => "basket is empty on step two"));
		}
	}
	
	public function mobileSaleReceipt($id = ""){
	 //$id = mobile purchase id, using it for showing original and return details, as it remains unique for both
		$kiosk_query = $this->Kiosks->find('list',array(
														'keyField' => 'id',
														'valueField' => 'name',
														));
		$kiosk_query = $kiosk_query->hydrate(false);
		if(!empty($kiosk_query)){
		  $kiosk = $kiosk_query->toArray();
		}else{
		  $kiosk = array();
		}
		$settingArr = $this->setting;
		$mobileResaleData_query = $this->MobileReSales->find('all',array('conditions'=>array('MobileReSales.id'=>$id,'MobileReSales.refund_status IS NULL')));
		$mobileResaleData_query = $mobileResaleData_query->hydrate(false);
		if(!empty($mobileResaleData_query)){
		  $mobileResaleData = $mobileResaleData_query->first();
		}else{
		  $mobileResaleData = array();
		}
		
		$mobileReturnData_query = $this->MobileReSales->find('all',array('conditions'=>array('MobileReSales.sale_id'=>$id,'MobileReSales.refund_status'=>1)));
		$mobileReturnData_query = $mobileReturnData_query->hydrate(false);
		if(!empty($mobileReturnData_query)){
		  $mobileReturnData = $mobileReturnData_query->first();
		}else{
		  $mobileReturnData = array();
		}
		
		$users_list = $this->Users->find("list",['keyField' => 'id',
								  'valueField' => 'username',
								  ])->toArray();
		
		
		
		$brandId = $mobileResaleData['brand_id'];
		$mobileModelId = $mobileResaleData['mobile_model_id'];
		$kiosk_id = $mobileResaleData['kiosk_id'];
		$brandName_query = $this->Brands->find('list',array('conditions'=>array('Brands.id'=>$brandId),
															'keyField' => 'id',
															'valueField' => 'brand',
															));
		$brandName_query = $brandName_query->hydrate(false);
		if(!empty($brandName_query)){
		  $brandName = $brandName_query->toArray();
		}else{
		  $brandName = array();
		}
		
		$kiosk_id1 = $mobileReturnData['kiosk_id'];
		
		$modelName_query = $this->MobileModels->find('list',array('conditions'=>array('MobileModels.id'=>$mobileModelId),
																  'keyField' => 'id',
																 'valueField' => 'model',
																  ));
		$MobileReSalePayments = $this->MobileReSalePayments->find("all",['conditions' => ['mobile_re_sale_id' => $id,
																						   //'kiosk_id' => $kiosk_id1,
																						   ]
																		 ])->toArray();
		
		$str = "";
		if(!empty($MobileReSalePayments)){
		  foreach($MobileReSalePayments as $key => $value){
			   $amount = $value->amount;
			   $payment_method = $value->payment_method;
			   $str .= $payment_method." : ".$amount." ";
		  }
		}
		
		$modelName_query = $modelName_query->hydrate(false);
		if(!empty($modelName_query)){
		  $modelName = $modelName_query->toArray();
		}else{
		  $modelName = array();
		}
		$kioskDetails_query = $this->Kiosks->find('all',array('conditions'=>array('Kiosks.id'=>$kiosk_id),//'fields'=>array('id','name','address_1','address_2','city','state','zip','contact','country')
												    ));
		$kioskDetails_query = $kioskDetails_query->hydrate(false);
		if(!empty($kioskDetails_query)){
		  $kioskDetails = $kioskDetails_query->first();
		}else{
		  $kioskDetails = array();
		}
		if($this->request->is('post')){
			$send_by_email = Configure::read('send_by_email');
			$emailSender = Configure::read('EMAIL_SENDER');
			$Email = new Email();
						$Email->config('default');
						$Email->viewVars(array('settingArr'=>$settingArr,'mobileResaleData'=>$mobileResaleData,'brandName'=>$brandName,'modelName'=>$modelName,'mobileReturnData'=>$mobileReturnData,'kiosk'=>$kiosk, 'kioskDetails' => $kioskDetails));
						//$Email->config(array('from' => 'me@example.org', 'transport' => 'MyCustom'));
						//$Email = new CakeEmail(array('from' => 'me@example.org', 'transport' => 'MyCustom'));
						$emailTo = $this->request['data']['customer_email'];
						$Email->template('mobile_receipt');
						$Email->emailFormat('both');
						$Email->to($emailTo);
						 $Email->transport(TRANSPORT);
						 $Email->from([$send_by_email => $emailSender]);
					//	$Email->sender("sales@oceanstead.co.uk");
						$Email->subject('Mobile Purchase Receipt');
					if($Email->send()){
						$this->Flash->success("Email has been successfully sent");
					}
		}
		$this->set(compact('settingArr','mobileResaleData','brandName','modelName','mobileReturnData','kiosk', 'kioskDetails','users_list',
						   'users_list','str'
						   ));
	}
	
	public function mobileRefund($resaleId = ""){
		  $CURRENCY_TYPE = Configure::read('CURRENCY_TYPE');
		$currency = $this->setting['currency_symbol'];
		$error = array();
		
		$kioskName_query = $this->Kiosks->find('list',array(
															'keyField' => 'id',
															'valueField' => 'name',
															));
		$kioskName_query = $kioskName_query->hydrate(false);
		if(!empty($kioskName_query)){
		  $kioskName = $kioskName_query->toArray();
		}else{
		  $kioskName = array();
		}
		$kioskName[0] = "http://hpwaheguru.co.uk/";
		if($this->request->is(array('post','put'))){
			// pr($this->request);die;
			$terms_resale = $this->setting['phone_resale_email_message'];
			$sale_price = $this->request['data']['sale_price'];//price that is displayed on view screen among discount and selling price
			$mobileRefundData = $this->request->data;
			
			$refundBy = $mobileRefundData['refund_by'];
			$refundStatus = $mobileRefundData['refund_status'];
			if($this->request->session()->read('Auth.User.group_id') == ADMINISTRATORS || $this->request->session()->read('Auth.User.group_id') == FRANCHISE_OWNER){
				$kiosk_id = 0;
			}else{
				$kiosk_id = $mobileRefundData['kiosk_id'];
			}
			
			$refundRemarks = $mobileRefundData['MobileRefund']['refund_remarks'];
			$refundPrice = $mobileRefundData['MobileRefund']['refund_price'];
			$refundGain = $sale_price-$refundPrice;
			if(empty($refundRemarks)){
				$error[] = "Please input the reason for refund.";
			}
			if(empty($refundPrice)){
				$error[] = "Please input the refund amount.";
			}
			if($refundPrice>$sale_price){
				$error[] = "Refund amount cannot exceed selling price.";
			}
			if(!(is_numeric($refundPrice))){
				$error[] = "Refund amount can only be numeric.";
			}
			$errorStr = '';
			
			if(count($error)>0){
				$errorStr = implode("<br/>",$error);
				$this->Flash->error($errorStr,['escape' => false]);
				return $this->redirect(array('action'=>'mobile_refund',$resaleId));
			}else{
				$conn = ConnectionManager::get('default');
			    $stmt = $conn->execute('SELECT NOW() as created'); 
			    $currentTime = $stmt ->fetchAll('assoc');
			   
				$refundDateTime = $currentTime[0]['created'];
				$mobileResaleData_query = $this->MobileReSales->find('all',array('conditions'=>array('MobileReSales.id'=>$resaleId)));
				$mobileResaleData_query = $mobileResaleData_query->hydrate(false);
				if(!empty($mobileResaleData_query)){
					$mobileResaleData = $mobileResaleData_query->first();
				}else{
					$mobileResaleData = array();
				}
				//pr($mobileResaleData);die;
				$mobileResaleData['selling_date'] = $mobileResaleData['created'];
				unset($mobileResaleData['created']);
				unset($mobileResaleData['modified']);
				unset($mobileResaleData['id']);
				$mobileResaleData['user_id'] = $refundBy;
				$mobileResaleData['refund_status'] = $refundStatus;
				$mobileResaleData['kiosk_id'] = $kiosk_id;
				$mobileResaleData['refund_remarks'] = $refundRemarks;
				$mobileResaleData['refund_price'] = $refundPrice;
				$mobileResaleData['refund_gain'] = $refundGain;
				$mobileResaleData['refund_date'] = $refundDateTime;
				$MobileReSalesEntity = $this->MobileReSales->newEntity($mobileResaleData,['validate' => false]);
				$MobileReSalesEntity = $this->MobileReSales->patchEntity($MobileReSalesEntity,$mobileResaleData,['validate' => false]);
				if($this->MobileReSales->save($MobileReSalesEntity,['validate' => false])){
					unset($mobileResaleData['selling_date']);
					$newId = $MobileReSalesEntity->id;
					$MobileReSales_new_Entity = $this->MobileReSales->get($newId);
					$data = array('sale_id' => $resaleId);
					$MobileReSales_new_Entity = $this->MobileReSales->patchEntity($MobileReSales_new_Entity,$data,['validate' => false]);
					$this->MobileReSales->save($MobileReSales_new_Entity);//to identify the id that has been refunded
					
					//-------------------
					$MobileReSales_other_new_Entity = $this->MobileReSales->get($resaleId);
					$data1 = array('status' => 1,
								  'refund_price' => $refundPrice,
								  'refund_gain' => $refundGain,
								  'refund_by' => $refundBy,
								  'refund_date' => $refundDateTime,
                                  
								  );
					$MobileReSales_other_new_Entity = $this->MobileReSales->patchEntity($MobileReSales_other_new_Entity,$data1,['validate' => false]);
					$this->MobileReSales->save($MobileReSales_other_new_Entity);
					
					//changing the status of mobile in mobile purchases table to available:0
					$MobilePurchasesEntity = $this->MobilePurchases->get($mobileResaleData['mobile_purchase_id']);
					$data2 = array('status' => 0);
					$MobilePurchasesEntity = $this->MobilePurchases->patchEntity($MobilePurchasesEntity,$data2,['validate' => false]);
					$this->MobilePurchases->save($MobilePurchasesEntity);
					
					//saving mobile transfer logs
					$mobileTransferLogData = array(
							'mobile_purchase_id' => $mobileResaleData['mobile_purchase_id'],
							'user_id' => $this->request->Session()->read('Auth.User.id'),
							'kiosk_id' => $mobileResaleData['kiosk_id'],
							'network_id' => $mobileResaleData['network_id'],
							'grade' => $mobileResaleData['grade'],
							'type' => $mobileResaleData['type'],
							'mobile_resale_id' => $resaleId,
							'receiving_status' => NULL,
							'imei' => $mobileResaleData['imei'],
							'status' => 2
							);
					
					$MobileTransferLogsEntity = $this->MobileTransferLogs->newEntity($mobileTransferLogData,['validate' => false]);
					$MobileTransferLogsEntity = $this->MobileTransferLogs->patchEntity($MobileTransferLogsEntity,$mobileTransferLogData,['validate' => false]);
					$this->MobileTransferLogs->save($MobileTransferLogsEntity);
					$kioskaddress_query = $this->Kiosks->find('all',array(
							'fields' => array('name','Kiosks.address_1','Kiosks.address_2', 'Kiosks.city','Kiosks.state','Kiosks.country','Kiosks.zip','Kiosks.contact' ),
							'conditions'=> array('Kiosks.id' => $mobileRefundData['kiosk_id'])
							)
						);
					$kioskaddress_query = $kioskaddress_query->hydrate(false);
					if(!empty($kioskaddress_query)){
						 $kioskaddress = $kioskaddress_query->toArray();
					}else{
						 $kioskaddress = array();
					}
					$countryOptions = Configure::read('uk_non_uk');
					$this->set(compact('countryOptions'));
			 //pr($kioskaddress);die;
					$model = $mobileRefundData['mobile_model_id'];
					$kioskaddress1 = $kioskaddress2 = $kioskcity = $kioskstate = $kioskcountry = $kioskzip = $kioskcontact = "";
					if(!empty($kioskaddress['0']['address_1'])){
						$kioskaddress1 = "<br/>".$kioskaddress['0']['address_1'].", ";
					}
					if(!empty($kioskaddress['0']['address_2'])){
						$kioskaddress2 = "<br/>".$kioskaddress['0']['address_2'].", " ;
					}
					if(!empty($kioskaddress['0']['city'])){
						$kioskcity = "\t".$kioskaddress['0']['city'].", ";
					}
					if(!empty($kioskaddress['0']['state'])){
					   $kioskstate =  "<br/>".$kioskaddress['0']['state'].", ";
					}
					if(!empty($kioskaddress['0']['country'])){
						 $kioskcountry = "<br/>".$countryOptions[$kioskaddress['0']['country']].", ";
					}
					if(!empty($kioskaddress['0']['zip'])){
						 $kioskzip = "<br/>".$kioskaddress['0']['zip'] ;
					}
					if(!empty($kioskaddress['0']['contact'])){
						 $kioskcontact =  "<br/>Contact: ".$kioskaddress['0']['contact'];
					}
					 $send_by_email = Configure::read('send_by_email');
					 $emailSender = Configure::read('EMAIL_SENDER');
					if(!empty($mobileResaleData['customer_email'])){
						  $emailStatement = "Hi ".$mobileResaleData['customer_fname']." ".$mobileResaleData['customer_lname'].",<br/><br/>As requested, refund has been processed for an amount of ".$CURRENCY_TYPE.$refundPrice."	for your mobile(imei:".$mobileResaleData['imei'].",\tMobile Model: ".$model.") 	.<br/><br/>Thank you for shopping with us.<br/><br/>Regards,<br/>" .$kioskName[$kiosk_id]. $kioskaddress1.$kioskaddress2.$kioskcity.$kioskstate.$kioskcountry.	$kioskzip.$kioskcontact."<br/><br/>".$terms_resale;
			
						//pr($mobileResaleData);die;
						$Email = new Email();
						$Email->config('default');
						$Email->viewVars(array('emailStatement' => $emailStatement));
						//$Email->config(array('from' => 'me@example.org', 'transport' => 'MyCustom'));
						//$Email = new CakeEmail(array('from' => 'me@example.org', 'transport' => 'MyCustom'));
						$emailTo = $mobileResaleData['customer_email'];
						$Email->template('mobile_refund_notification');
						$Email->emailFormat('both');
						$Email->to($emailTo);
						 $Email->transport(TRANSPORT);
						 $Email->from([$send_by_email => $emailSender]);
					//	$Email->sender("sales@oceanstead.co.uk");
						$Email->subject('Refund Processed');
						$Email->send();
					}
					
					$this->Flash->error("Refund request has been submitted");
					return $this->redirect(array('action'=>'index'));
				}else{
					//pr($MobileReSalesEntity);die;
					$this->Flash->error("Refund request could not be processed, please try again!U");
					return $this->redirect(array('action'=>'index'));
				}
			}
		}
		$MobileReSales_query = $this->MobileReSales->find('all',array('conditions'=>array('MobileReSales.id'=>$resaleId)));
		$MobileReSales_query = $MobileReSales_query->hydrate(false);
		if(!empty($MobileReSales_query)){
		  $MobileReSales = $MobileReSales_query->first();
		}else{
		  $MobileReSales = array();
		}
		$this->request->data = $MobileReSales;
		$userId = $this->request->data['user_id'];
		$brandId = $this->request->data['brand_id'];
		$mobileModelId = $this->request->data['mobile_model_id'];
		$networkId = $this->request->data['network_id'];
		$userName_query = $this->Users->find('list',array('conditions'=>array('Users.id'=>$userId),
														  'keyField' => 'id',
														  'valueField' => 'username'
														  ));
		$userName_query = $userName_query->hydrate(false);
		if(!empty($userName_query)){
		  $userName = $userName_query->toArray();
		}else{
		  $userName = array();
		}
		$brandName_query = $this->Brands->find('list',array('conditions'=>array('Brands.id'=>$brandId),
															'keyField' => 'id',
															'valueField' => 'brand'
															));
		$brandName_query = $brandName_query->hydrate(false);
		if(!empty($brandName_query)){
		  $brandName = $brandName_query->toArray();
		}else{
		  $brandName = array();
		}
		$modelName_query = $this->MobileModels->find('list',array('conditions'=>array('MobileModels.id'=>$mobileModelId),
																  'keyField' => 'id',
																   'valueField' => 'model'
																  ));
		$modelName_query = $modelName_query->hydrate(false);
		if(!empty($modelName_query)){
		  $modelName = $modelName_query->toArray();
		}else{
		  $modelName = array();
		}
		$userName[0] = "Admin";
		$networkName_query = $this->Networks->find('list',array('conditions'=>array('Networks.id'=>$networkId),
																'keyField' => 'id',
																 'valueField' => 'name'
																));
		$networkName_query = $networkName_query->hydrate(false);
		if(!empty($networkName_query)){
		  $networkName = $networkName_query->toArray();
		}else{
		  $networkName = array();
		}
		$type = array('0'=>'Unlocked','1'=>'Locked');
		$this->set(compact('currency','userName','brandName','modelName','type','networkName'));
	}
	
	 public function export(){
		if(array_key_exists('search_kw',$this->request->query) &&
		   !empty($this->request->query['search_kw'])
		   ){
			$searchKW = trim(strtolower($this->request->query['search_kw']));
		}  
		
		$conditionArr = array();
		if(!empty($searchKW)){
			$conditionArr['OR']['MobileReSales.imei like'] =  strtolower("%$searchKW%");
			$conditionArr['OR']['Brands.brand like'] = strtolower("%$searchKW%");
			$conditionArr['OR']['MobileReSales.customer_email like'] = strtolower("%$searchKW%");
			$conditionArr ['OR']['MobileModels.model like'] = strtolower("%$searchKW%");
		}
		
		if(array_key_exists('start_date',$this->request->query) &&
		   !empty($this->request->query['start_date'])
		   ){
			$this->set('start_date',$this->request->query['start_date']);
		}
		
		if(array_key_exists('end_date',$this->request->query) &&
		   !empty($this->request->query['end_date'])
		   ){
			$this->set('end_date',$this->request->query['end_date']);
		}
		
		if(array_key_exists('start_date',$this->request->query) &&
		   !empty($this->request->query['start_date']) &&
		   array_key_exists('end_date',$this->request->query) &&
		   !empty($this->request->query['end_date'])
		   ){
			$conditionArr[] = array(
			"MobileReSales.created >" => date('Y-m-d', strtotime($this->request->query['start_date'])),
			"MobileReSales.created <" => date('Y-m-d', strtotime($this->request->query['end_date']. ' +1 Days')),   
			);
		}
		//echo "admin";
		$kioskid = $this->request->Session()->read('kiosk_id');
        //pr($this->request->query);die;
		if($kioskid == ""){
			// pr($this->request->query); 
        // echo   $kioskId = $this->request->query['MobileResale']['kiosk_id'];die;
//			$this->set('kioskId', $kioskId);
            if(array_key_exists('MobileResale',$this->request->query)){
                  $kioskId = $this->request->query['MobileResale']['kiosk_id'];
            }else{
                $kioskId = '';
            }
            $this->set('kioskId', $kioskId);
			if(array_key_exists('MobileResale',$this->request->query) && !empty($this->request->query['MobileResale']['kiosk_id'])){
				//echo "admin1";
				if($kioskId == "10000"){
					$kioskId = "0";
					$conditionArr[] =  array('kiosk_id' => $kioskId);
				}else{
				 $conditionArr[] =  array('kiosk_id' => $this->request->query['MobileResale']['kiosk_id']);
				}
			}
		}
		if($kioskid>0){
			
			$conditionArr[] = array('kiosk_id' =>$kioskid);
		}
		if(!array_key_exists('kiosk_id',$conditionArr)){
		  $managerKiosk = $this->get_kiosk();
		  $conditionArr['kiosk_id IN'] = $managerKiosk;
		}
		  if(count($conditionArr)>=1){
			   //pr($conditionArr);die;
			   $count_query = $this->MobileReSales->find('all');
			   $count = $count_query->count();
			   $mobileReSales_query = $this->MobileReSales->find('all',array(
																		  'conditions' => $conditionArr,
																		  'limit' => $count,
                                                                          'contain' => ['Brands','MobileModels']
																		  ));
			//$mobileReSales = $this->Paginator->paginate('MobileReSale');
			
		  }else{
			   	$mobileReSales_query = $this->MobileReSales->find('all');
		  }
		  $mobileReSales_query = $mobileReSales_query->hydrate(false);
		  if(!empty($mobileReSales_query)){
			   $mobileReSales = $mobileReSales_query->toArray();
		  }else{
			   $mobileReSales = array();
		  }
		  //pr( $mobileReSales);die;
		  $tmpMobileReSales = array(); 
		  foreach($mobileReSales as $key => $mobileReSale){
			   unset($mobileReSale['mobile_model']);
			   unset($mobileReSale['brand']);
			   $refund_date = $mobileReSale['refund_date'];
			   if(!empty($refund_date)){
					 $refund_date = date("d-m-y h:i a",strtotime($refund_date));
					 $mobileReSale['refund_date'] = $refund_date;
			   }
			   $selling_date = $mobileReSale['selling_date'];
			   if(!empty($selling_date)){
					 $selling_date = date("d-m-y h:i a",strtotime($selling_date));
					 $mobileReSale['selling_date'] = $selling_date;
			   }
			   $created = $mobileReSale['created'];
			   if(!empty($created)){
					 $created = date("d-m-y h:i a",strtotime($created));
					 $mobileReSale['created'] = $created;
			   }
			   $modified = $mobileReSale['modified'];
			   if(!empty($modified)){
					$modified = date("d-m-y h:i a",strtotime($modified));
					$mobileReSale['modified'] = $modified;
			   }
			   $tmpMobileReSales[] = $mobileReSale;
		  }
		$this->outputCsv('MobileReSale_'.time().".csv" ,$tmpMobileReSales);
		$this->autoRender = false;
	}
    
    function exportCost(){
		$conditionArr = array();
		$startDate = $endDate = "";
		if(array_key_exists('start_date',$this->request->query)){$startDate = $this->request->query['start_date'];}
		if(array_key_exists('start_date',$this->request->query)){$endDate = $this->request->query['end_date'];}
		
		if(!empty($startDate) && !empty($endDate)){
			$conditionArr[] = array(
										"MobileReSales.created >" => date('Y-m-d', strtotime($startDate)),
										"MobileReSales.created <" => date('Y-m-d', strtotime($endDate.' +1 Days')),   
									);
		}
		
		if(array_key_exists('kioskId',$this->request->query)){
			$kioskId = $this->request->query['kioskId'];
			$conditionArr[] = array('kiosk_id' => $kioskId);
		}
		
		$mobileReSales_query = $this->MobileReSales->find('all', array(
																'conditions' => $conditionArr,
																'order' => 'MobileReSales.id desc')
												   );
        $mobileReSales_query = $mobileReSales_query->hydrate(false);
        if(!empty($mobileReSales_query)){
            $mobileReSales = $mobileReSales_query->toArray();
        }else{
            $mobileReSales = array();
        }
		//pr($mobileReSales);die;
		$purchase_ids = array();
		foreach($mobileReSales as $s_key => $s_value){
			$purchase_ids[] = $s_value['mobile_purchase_id'];
		}
        if(empty($purchase_ids)){
            $purchase_ids = array(0 => null);
        }
		$res_query = $this->MobilePurchases->find('all',array('conditions' => array('id IN' => $purchase_ids)
													   )
                                            );
        $res_query = $res_query->hydrate(false);
        if(!empty($res_query)){
            $res = $res_query->toArray();
        }else{
            $res = array();
        }
		$purchase_data = array();
		foreach($res as $y_key => $y_value){
			$purchase_data[$y_value['id']] = $y_value;
		}
		$kiosks_query = $this->Kiosks->find('list',[
                                                'keyField' => 'id', 
                                                'valueField' =>'name',
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
		
		$brands_query = $this->MobileReSales->Brands->find('list',[
																'keyField' => 'id',
                                                                'valueField' => 'brand',
																'conditions' => ['Brands.status' => 1]
                                                            ]
                                                    );
        $brands_query = $brands_query->hydrate(false);
        if(!empty($brands_query)){
            $brands = $brands_query->toArray();
        }else{
            $brands = array();
        }
		$mobileModels_query = $this->MobileModels->find('all',array('fields' => array('id', 'model')));
        $mobileModels_query = $mobileModels_query->hydrate(false);
        if(!empty($mobileModels_query)){
            $mobileModels = $mobileModels_query->toArray();
        }else{
            $mobileModels = array();
        }
		foreach($mobileModels as $k => $modelinfo){
			$modelName[$modelinfo['id']] = $modelinfo['model'];
		}
		
		$csvRows = array();
		//pr($purchase_data);die;
		foreach ($mobileReSales as $key => $mobileReSale){
			   if($mobileReSale['discounted_price'] > 0){
				   $price = $mobileReSale['discounted_price'];
			   }else{
				   $price = $mobileReSale['selling_price'];
			   }
			   if((int)$mobileReSale['sale_id']){
				   $csvRows[$key]['Rcpt'] = $mobileReSale['sale_id'];
			   }else{
				   $csvRows[$key]['Rcpt'] = $mobileReSale['id'];
			   }
			   $csvRows[$key]['Brand'] = $brands[$mobileReSale['brand_id']];
			   $csvRows[$key]['Model'] = $modelName[$mobileReSale['mobile_model_id']];
			   $csvRows[$key]['IMEI'] = $mobileReSale['imei'];
			   if($mobileReSale['refund_status'] == 1){
				   $csvRows[$key]['Selling Price'] = "";
				   $csvRows[$key]['Cost Price'] = "-".$mobileReSale['cost_price'];
				   if(!empty($mobileReSale['refund_price'])){
					   $csvRows[$key]['Refund Price'] = $mobileReSale['refund_price'];
					}else{
					   $csvRows[$key]['Refund Price'] = "---";
					}
					if(!empty($mobileReSale['refund_date'])){
						   $mobileReSale['refund_date'] = date("d-m-y h:i a",strtotime($mobileReSale['refund_date']));
						  $csvRows[$key]['Refund Date'] = $mobileReSale['refund_date'];
					}
				    
			   }else{
				   $csvRows[$key]['Selling Price'] = $price;
				   $csvRows[$key]['Cost Price'] = $mobileReSale['cost_price'];
				   $csvRows[$key]['Refund Price'] = "";
				   $csvRows[$key]['Refund Date'] = "";
			   }
			   //pr($purchase_data);die;
			   
			   
			   if($mobileReSale['selling_date'] == "0000-00-00 00:00:00"||empty($mobileReSale['selling_date'])){
					$mobileReSale['created'] = date("d-m-y h:i a",strtotime($mobileReSale['created']));
					$csvRows[$key]['Sale Date'] = $mobileReSale['created'];
			   }else{
					$mobileReSale['selling_date'] = date("d-m-y h:i a",strtotime($mobileReSale['selling_date']));
				   $csvRows[$key]['Sale Date'] = $mobileReSale['selling_date'];
			   }
			   $csvRows[$key]['First Name'] = $mobileReSale['customer_fname'];
			   $csvRows[$key]['Last Name'] = $mobileReSale['customer_lname'];
			   $csvRows[$key]['Email'] = $mobileReSale['customer_email'];
			   $csvRows[$key]['Contact'] = $mobileReSale['customer_contact'];
			   $csvRows[$key]['Address 1'] = $mobileReSale['customer_address_1'];
			   $csvRows[$key]['Address 2'] = $mobileReSale['customer_address_2'];
			   $csvRows[$key]['City'] = $mobileReSale['city'];
			   $csvRows[$key]['State'] = $mobileReSale['state'];
			   $csvRows[$key]['Country'] = $mobileReSale['country'];
			   $csvRows[$key]['post code'] = $mobileReSale['zip'];
			   if(array_key_exists($mobileReSale['mobile_purchase_id'],$purchase_data)){
						 if(!empty($purchase_data[$mobileReSale['mobile_purchase_id']]['created'])){
							  $purchase_data[$mobileReSale['mobile_purchase_id']]['created'] = date("d-m-y h:i a",strtotime($purchase_data[$mobileReSale['mobile_purchase_id']]['created']));
							   $csvRows[$key]['Purchase Date'] = $purchase_data[$mobileReSale['mobile_purchase_id']]['created'];
						 }
						 $csvRows[$key]['P First Name'] = $purchase_data[$mobileReSale['mobile_purchase_id']]['customer_fname'];
						 $csvRows[$key]['P Last Name'] = $purchase_data[$mobileReSale['mobile_purchase_id']]['customer_lname'];
						  if(!empty($purchase_data[$mobileReSale['mobile_purchase_id']]['date_of_birth'])){
							  $purchase_data[$mobileReSale['mobile_purchase_id']]['date_of_birth'] = date("d-m-y h:i a",strtotime($purchase_data[$mobileReSale['mobile_purchase_id']]['created']));
							    $csvRows[$key]['P Dob'] = $purchase_data[$mobileReSale['mobile_purchase_id']]['date_of_birth'];
						 }
						
						 $csvRows[$key]['P Email'] = $purchase_data[$mobileReSale['mobile_purchase_id']]['customer_email'];
						 $csvRows[$key]['P customer_contact'] = $purchase_data[$mobileReSale['mobile_purchase_id']]['customer_contact'];
						 $csvRows[$key]['P customer_address_1'] = $purchase_data[$mobileReSale['mobile_purchase_id']]['customer_address_1'];
						 $csvRows[$key]['P customer_address_2'] = $purchase_data[$mobileReSale['mobile_purchase_id']]['customer_address_2'];
						 $csvRows[$key]['P city'] = $purchase_data[$mobileReSale['mobile_purchase_id']]['city'];
						 $csvRows[$key]['P state'] = $purchase_data[$mobileReSale['mobile_purchase_id']]['state'];
						 $csvRows[$key]['P country'] = $purchase_data[$mobileReSale['mobile_purchase_id']]['country'];
						 $csvRows[$key]['P customer_identification'] = $purchase_data[$mobileReSale['mobile_purchase_id']]['customer_identification'];
						 $csvRows[$key]['P serial_number'] = $purchase_data[$mobileReSale['mobile_purchase_id']]['serial_number'];
						 $csvRows[$key]['P post code'] = $purchase_data[$mobileReSale['mobile_purchase_id']]['zip'];
			   }
			//pr($csvRows);die;
		}
		$this->outputCsv('MobileReSale_'.time().".csv" ,$csvRows);
		$this->autoRender = false;
	}
}
?>