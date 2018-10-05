<?php
namespace App\Controller;

use App\Controller\AppController;
use Cake\Event\Event;
use Cake\Core\Configure;
use Cake\Core\Configure\Engine\PhpConfig;
use Cake\Network\Request;
use Cake\ORM\TableRegistry;
use Cake\Mailer\Email;


use Cake\Datasource\ConnectionManager;


class MobileUnlockSalesController extends AppController{
        public $helpers = [
         'Paginator' => ['templates' => 'paginatortemplates']
         ];
     public function initialize()
    {
        $siteUrl = Configure::read('SITE_BASE_URL');
        // pr($siteUrl);die;
        parent::initialize();
        $this->loadModel('TransferSurplus');
        $this->loadModel('Products');
        $this->loadModel('Users');
        $this->loadModel('Categories');
        $this->loadModel('TransferUnderstock');
        $this->loadModel('StockTransfer');
        $this->loadModel('UnlockPayments');
        $this->loadModel('MobileUnlocks');
		$this->loadModel('MobileModels');
		$CURRENCY_TYPE = Configure::read('CURRENCY_TYPE');
		$this->set(compact('CURRENCY_TYPE' ));
		
        $unlockStatusUserOptions = Configure::read('unlock_statuses_user');
		$unlockStatusTechnicianOptions = Configure::read('unlock_statuses_technician');
		$this->set(compact('unlockStatusUserOptions','unlockStatusTechnicianOptions'));
    }
    
    public function viewUnlockSales(){
		$saleSum = $refundSum = '';
		if($this->request->session()->read('Auth.User.group_id') == ADMINISTRATORS || $this->request->session()->read('Auth.User.group_id') == FRANCHISE_OWNER || $this->request->session()->read('Auth.User.group_id') == MANAGERS){
			if(!empty($this->request['data']['UnlockSale']['kiosk_id'])){
				$kiosk_id = $this->request['data']['UnlockSale']['kiosk_id'];			
				
				$this->paginate = [
							'conditions'=>['MobileUnlockSales.kiosk_id' => $kiosk_id ,'MobileUnlockSales.refund_status'=>0],
							'order' => ['MobileUnlockSales.mobile_unlock_id DESC, MobileUnlockSales.id DESC'],
							'limit' => ROWS_PER_PAGE
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
				if( $ext_site == 1){
					$managerKiosk = $this->get_kiosk();//pr($managerKiosk);die;
					if(!empty($managerKiosk)){
						$kiosk_id = $managerKiosk;
					$this->paginate = [
								'conditions'=>['MobileUnlockSales.refund_status'=>0,
								'MobileUnlockSales.kiosk_id IN'=>$kiosk_id,
									       ],
								'order' => ['MobileUnlockSales.mobile_unlock_id DESC, MobileUnlockSales.id DESC'],
								'limit' => ROWS_PER_PAGE
					];
					}
				}else{
					$this->paginate = [
								'conditions'=>['MobileUnlockSales.refund_status'=>0],
								'order' => ['MobileUnlockSales.mobile_unlock_id DESC, MobileUnlockSales.id DESC'],
								'limit' => ROWS_PER_PAGE
					];
				}
			}
		}elseif($this->request->session()->read('Auth.User.group_id') == KIOSK_USERS){
			$kioskId = $this->request->Session()->read('kiosk_id');
			$this->paginate = [
							'conditions'=>['MobileUnlockSales.kiosk_id' => $kioskId,'MobileUnlockSales.refund_status'=>0],
							'order' => ['MobileUnlockSales.mobile_unlock_id DESC, MobileUnlockSales.id DESC'],
							'limit' => ROWS_PER_PAGE
							];
		}
		
		$kiosks_query = $this->Kiosks->find('list',
                                                [
                                                    'keyField' => 'id',
                                                    'valueField' => 'name',
                                                    'conditions' => ['Kiosks.status' => 1],
                                                    'order' => 'Kiosks.name asc'
                                                ]);
        $kiosks_query = $kiosks_query->hydrate(false);
        if(!empty($kiosks_query)){
            $kiosks = $kiosks_query->toArray();
        }
        
		unset($kiosks[10000]);//removing warehouse from list, as the unlock is only allowed to kiosk
		$users_query = $this->Users->find('list',
                                            [
                                                'keyField' => 'id',
                                                'valueField' => 'username',
                                            ]
                                   );
        $users_query = $users_query->hydrate(false);
        if(!empty($users_query)){
            $users = $users_query->toArray();
        }else{
            $users = array();
        }
		$this->set(compact('kiosks','users','kiosk_id'));
		$mobileUnlockSales_query = $this->paginate("MobileUnlockSales");
        if(!empty($mobileUnlockSales_query)){
            $mobileUnlockSales = $mobileUnlockSales_query->toArray();
        }else{
            $mobileUnlockSales = array();
        }
		$unlockIdArr = array();
		foreach($mobileUnlockSales as $key=>$mobileUnlockSale){
			$unlockIdArr[] = $mobileUnlockSale->mobile_unlock_id;
		}
        if(empty($unlockIdArr)){
            $unlockIdArr = array('0'=>null);
        }
		//** getting unlock payment details
		$paymentArr = array();
		$payment_amount_arr = array();
		$mobileUnlockPayment_query = $this->UnlockPayments->find('all', array('conditions' => array('UnlockPayments.mobile_unlock_id IN' => $unlockIdArr)));
	 
        $mobileUnlockPayment_query = $mobileUnlockPayment_query->hydrate(false);
        if(!empty($mobileUnlockPayment_query)){
            $mobileUnlockPayment = $mobileUnlockPayment_query->toArray();
        }
        
		if(count($mobileUnlockPayment)){
			foreach($mobileUnlockPayment as $rp => $paymentDetail){
				$paymentArr[$paymentDetail['mobile_unlock_id']][] = $paymentDetail;
				if(array_key_exists($paymentDetail['mobile_unlock_id'],$payment_amount_arr) && array_key_exists($paymentDetail['payment_method'],$payment_amount_arr[$paymentDetail['mobile_unlock_id']])){
					$payment_amount_arr[$paymentDetail['mobile_unlock_id']][$paymentDetail['payment_method']]+= $paymentDetail['amount'];
				}else{
					$payment_amount_arr[$paymentDetail['mobile_unlock_id']][$paymentDetail['payment_method']] = $paymentDetail['amount'];
				}
			}
		}
		//code for payment ends here **
	 
		$mobileUnlockStsArray = array();
		$mobileUnlockStatus_query = $this->MobileUnlocks->find('all',array('conditions'=>array('MobileUnlocks.id IN'=>$unlockIdArr),'fields'=>array('id','status')));
        $mobileUnlockStatus_query = $mobileUnlockStatus_query->hydrate(false);
        if(!empty($mobileUnlockStatus_query)){
            $mobileUnlockStatus = $mobileUnlockStatus_query->toArray();
        }
		foreach($mobileUnlockStatus as $key=>$mobileUnlockSts){
			$mobileUnlockStsArray[$mobileUnlockSts['id']] = $mobileUnlockSts['status'];
		}
		$this->set(compact('mobileUnlockSales','mobileUnlockStsArray','paymentArr','payment_amount_arr','saleSum','refundSum'));
	}
    
    public function search(){
		//unset($_SESSION['kiosk_id']);
        //pr($_SESSION);die;
        $kioskId = $this->request->Session()->read('kiosk_id');
		$searchKW = "";
		$searchKW1 = "";
		$conditionArr = array();
		$paymentMode = 'Multiple';
		$refundStatus = 0;
		if(array_key_exists('payment_mode',$this->request->query)){
			$paymentMode = $this->request->query['payment_mode'];
			if($paymentMode == 'refunded'){
				$refundStatus = 1;
			}
		}
		if(array_key_exists('search_kw',$this->request->query)){
			$searchKW = $this->request->query['search_kw'];
		}	
		if(array_key_exists('search_kw1',$this->request->query)){
			$searchKW1 = $this->request->query['search_kw1'];
		}
		
		if(array_key_exists('start_date',$this->request->query)){
			$this->set('start_date',$this->request->query['start_date']);
		}
		
		if(array_key_exists('end_date',$this->request->query)){
			$this->set('end_date',$this->request->query['end_date']);
		}
        //pr($this->request->query);die;
		if($kioskId == ""){
			if(array_key_exists('data',$this->request->query)){
				$kiosk_id = $this->request->query['data']['UnlockSale'];
				if(array_key_exists('kiosk_id',$this->request->query['data']['UnlockSale']) && !empty($this->request->query['data']['UnlockSale']['kiosk_id'])){
					$conditionArr[] = array('MobileUnlockSales.kiosk_id' =>$this->request->query['data']['UnlockSale']['kiosk_id']);
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
										$conditionArr[] = array('MobileUnlockSales.kiosk_id IN' =>$managerKiosk);
								}
						}
				}
                //pr($kiosk_id);die;
				$this->set('kiosk_id', $kiosk_id);
			}
		}
		 
		if($kioskId > 0){
			$conditionArr[] = array("MobileUnlockSales.kiosk_id" => $kioskId);
		}
		if(array_key_exists('start_date',$this->request->query) &&
		   !empty($this->request->query['start_date']) &&
		   array_key_exists('end_date',$this->request->query) &&
			!empty($this->request->query['end_date'])){
			
				$conditionArr[] = array(
						"MobileUnlockSales.created >" => date('Y-m-d', strtotime($this->request->query['start_date'])),
						"MobileUnlockSales.created <" => date('Y-m-d', strtotime($this->request->query['end_date']. ' +1 Days')),			
					       );
		}
		if($searchKW){
			$conditionArr[] = array(//['OR']
						'MobileUnlockSales.mobile_unlock_id' => "$searchKW",
						);
						
		}
		if($searchKW1){
			$conditionArr[] = array('MobileUnlocks.imei like' => "%$searchKW1%");
		}
		$this->set('search_kw', $searchKW);
		$this->set('search_kw1', $searchKW1);
		//$this->set('kiosk_id', $kiosk_id);
		$sales_data_query = $this->MobileUnlockSales->find('all',array(
											'conditions' => array($conditionArr,'MobileUnlockSales.refund_status' => $refundStatus),
											'fields' => array('mobile_unlock_id','refund_status'),
											'order' => 'MobileUnlockSales.id DESC',
                                            'contain' => ['MobileUnlocks'],
											//'recursive' => 0
												)
											);
		if($paymentMode == "refunded"){
			$sales_data_query = $this->MobileUnlockSales->find('all',array(
											'conditions' => array($conditionArr),
											'fields' => array('id','mobile_unlock_id','refund_status'),
											'order' => 'MobileUnlockSales.id DESC',
                                            'contain' => ['MobileUnlocks'],
											//'recursive' => 0
												)
											);
		}
        $sales_data_query = $sales_data_query->hydrate(false);
        if(!empty($sales_data_query)){
            $sales_data = $sales_data_query->toArray();
        }
        //pr($sales_data);die;
		$saleDataArr = array();
		foreach($sales_data as $sngSaleData){
			$saleDataArr[$sngSaleData['mobile_unlock_id']] = $sngSaleData['refund_status'];
		}
		//pr($saleDataArr);
		$kiosks_query = $this->Kiosks->find('list',
                                            [
                                                'keyField' => 'id',
                                                'valueField' => 'name',
                                                'conditions' => ['Kiosks.status' => 1],
                                                'order' => 'Kiosks.name asc'
                                            ]);
		$kiosks_query = $kiosks_query->hydrate(false);
        if(!empty($kiosks_query)){
            $kiosks = $kiosks_query->toArray();
        }
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
		
		$unlockIdArr = array();
		foreach($sales_data as $key => $mobileUnlockSale){
			if($paymentMode == 'refunded'){
				if(
				   array_key_exists($mobileUnlockSale['mobile_unlock_id'], $saleDataArr) &&
				   $mobileUnlockSale['refund_status'] == 1
				   ){ 
					$unlockIdArr[] = $mobileUnlockSale['mobile_unlock_id'];
				}else{
					$unlockIdArr[] = $mobileUnlockSale['mobile_unlock_id'];
				}
			}else{
				$unlockIdArr[] = $mobileUnlockSale['mobile_unlock_id'];
			}
		}
		//$resultUnlockIds = array(0 => 0);
		$resultUnlockIds = $unlockIdArr;
		
		
		
		$payment_array = array();
		$payment_amount_arr = array();
		//to integrate payment mode search with above search, we are grabbing the unlock ids from the payment table as per the
		//above result ($unlockIdArr with condition array)
		//then again we are finding the unlock ids from unlock payment table with the search unlock id result $unlockIdArr
		//and will again change condition in paginator setting to paginate as per the payment table outcome
		
		//getting unlock ids from unlock payment table as per the selected in radio buttons
		if($paymentMode == 'Cash' || $paymentMode == 'Card'){
		  if(empty($unlockIdArr)){
			   $unlockIdArr = array(0=>null);
		  }
			$searchPaymentResult_query = $this->UnlockPayments->find('all', array('conditions' => array('UnlockPayments.mobile_unlock_id IN' => $unlockIdArr, 'UnlockPayments.payment_method' => $paymentMode), 'recursive' => -1));
            //pr($searchPaymentResult_query);die;
            $searchPaymentResult_query = $searchPaymentResult_query->hydrate(false);
            if(!empty($searchPaymentResult_query)){
                $searchPaymentResult  = $searchPaymentResult_query->toArray();
            }else{
                $searchPaymentResult = array();
            }
			if(count($searchPaymentResult)){
				foreach($searchPaymentResult as $spr => $searchPaymentInfo){
					$payment_array[$searchPaymentInfo['mobile_unlock_id']] = $searchPaymentInfo['mobile_unlock_id'];
				}
				
				$resultUnlockIds = $payment_array;
			}
			if(!empty($payment_array)){
					$this->paginate = [
							'conditions' => [ 'MobileUnlockSales.mobile_unlock_id IN' => $payment_array,
									      'MobileUnlockSales.refund_status' => $refundStatus
									      ],
							'order' => ['MobileUnlockSales.mobile_unlock_id DESC'],//MobileUnlockSale.id DESC
							'limit' => ROWS_PER_PAGE,
                            'contain' => ['MobileUnlocks'],
							//'recursive' => 0
						  ]; 
			}else{
			   $payment_array = array(0=>null);
						 $this->paginate = [
							'conditions' => [ 'MobileUnlockSales.mobile_unlock_id IN' => $payment_array,
									      'MobileUnlockSales.refund_status' => $refundStatus
									      ],
							'order' => ['MobileUnlockSales.mobile_unlock_id DESC'],//MobileUnlockSale.id DESC
							'limit' => ROWS_PER_PAGE,
                            'contain' => ['MobileUnlocks'],
							//'recursive' => 0
						  ];
			}
			
		}else{
			$this->paginate = [
				
							'conditions' => [$conditionArr,
									      'MobileUnlockSales.refund_status' => $refundStatus
									      ],
										'order' => ['MobileUnlockSales.mobile_unlock_id DESC'],
										'limit' => ROWS_PER_PAGE,
                                        'contain' => ['MobileUnlocks'],
										//'recursive' => 0
                                        ];
		}
		
			$refundSumData_query = $this->MobileUnlockSales->find('all',array(
                                                                  'conditions' => array($conditionArr, 'MobileUnlockSales.refund_status' => 1),
                                                                  'contain' => array('MobileUnlocks')
                                                                  ));
            
            $refundSumData_query
                                    ->select(['totalrefund' => $refundSumData_query->func()->sum('refund_amount')]);
            $refundSumData_query = $refundSumData_query->hydrate(false);
            if(!empty($refundSumData_query)){
                $refundSumData = $refundSumData_query->toArray();
            }else{
                $refundSumData = array();
            }
            
		$refundSum = $refundSumData[0]['totalrefund'];
		if($refundSum < 0){
			$refundSum = -$refundSum;
		}elseif(empty($refundSum)){
			$refundSum = 0;
		}
		if($paymentMode == 'Card'){
		  if(empty($resultUnlockIds)){
			   $resultUnlockIds  = array(0=>null);
		  }
			$saleSumData_query = $this->UnlockPayments->find('all',array(
                                    'conditions' => array('payment_method' => 'Card', 'UnlockPayments.mobile_unlock_id IN' => $resultUnlockIds))
                                                      );
                 $saleSumData_query
                                    ->select(['totalsale' => $saleSumData_query->func()->sum('amount')]);
                 $saleSumData_query  = $saleSumData_query->hydrate(false);
                 if(!empty($saleSumData_query)){
                    $saleSumData = $saleSumData_query->toArray();
                 }else{
                    $saleSumData = array();
                 }

			$refundSum = 0;
		}elseif($paymentMode == 'Cash'){
            if(empty($resultUnlockIds)){
			   $resultUnlockIds  = array(0=>null);
		  }
			$saleSumData_query = $this->UnlockPayments->find('all', array(
                                                                   'conditions' => array('payment_method' => 'Cash',
                                                                    'UnlockPayments.mobile_unlock_id IN' => $resultUnlockIds)
                                                                   )
                                                      );
            $saleSumData_query
                                ->select(['totalsale' => $saleSumData_query->func()->sum('amount')]);
             $saleSumData_query  = $saleSumData_query->hydrate(false);
             if(!empty($saleSumData_query)){
                $saleSumData = $saleSumData_query->toArray();
             }else{
                $saleSumData = array();
             }
		}else{
           
			if($paymentMode == 'refunded'){
			    if(empty($resultUnlockIds)){
					 $resultUnlockIds  = array(0=>null);
			     }
				$saleSumData_query = $this->UnlockPayments->find('all', array(
													'conditions' => array(
													'UnlockPayments.mobile_unlock_id IN' => $resultUnlockIds,														 'UnlockPayments.created >' => date('Y-m-d', strtotime($this->request->query['start_date'])),
												'UnlockPayments.created <' => date('Y-m-d', strtotime($this->request->query['start_date']. ' +1 Days')),			
														)));
                $saleSumData_query
                                    ->select(['totalsale' => $saleSumData_query->func()->sum('amount')]);
                                    
                 $saleSumData_query = $saleSumData_query->hydrate(false);
                 if(!empty($saleSumData_query)){
                    $saleSumData = $saleSumData_query->toArray();
                 }else{
                    $saleSumData = array();
                 }
                 
			}else{
			   if(!empty($resultUnlockIds)){
					$saleSumData_query = $this->UnlockPayments->find('all', array(
																			'conditions' => array('UnlockPayments.mobile_unlock_id IN' => $resultUnlockIds)));
				   
					$saleSumData_query
										->select(['totalsale' => $saleSumData_query->func()->sum('amount')]);
										
					$saleSumData_query = $saleSumData_query->hydrate(false);
					if(!empty($saleSumData_query)){
						$saleSumData = $saleSumData_query->toArray();
					}else{
						$saleSumData = array();
					}
			   }else{
					$saleSumData = array();
			   }
			}
		}
                   
        if(!empty($saleSumData)){
            $saleSum = $saleSumData[0]['totalsale'];
        }else{
            $saleSum = 0;
        }
		
        //pr($this->paginate);die;
		$mobileUnlockSales_query = $this->paginate('MobileUnlockSales');
		$mobileUnlockSales = $mobileUnlockSales_query->toArray();
        //pr($mobileUnlockSales);die;
		$unlockAmounts = $mobileUnlockIds = array();
		if($paymentMode == 'refunded'){
			foreach($mobileUnlockSales as $key => $sngMobUnlockSale){
				$mobileUnlockIds[] = $sngMobUnlockSale->mobile_unlock_id;
			}
            if(!empty($mobileUnlockIds)){
                    $unlockAmounts_query = $this->MobileUnlockSales->find('list',
                                                                    [
                                                                        'keyField' => "mobile_unlock_id",
                                                                        'valueField' => "amount",
                                                                        'conditions' => [
																			"MobileUnlockSales.mobile_unlock_id IN" => $mobileUnlockIds,
																			"MobileUnlockSales.refund_status" => 0,
																			 ],
                                                                    ]);   
            }else{
                $unlockAmounts_query = $this->MobileUnlockSales->find('list',
                                                                    [
                                                                        'keyField' => "mobile_unlock_id",
                                                                        'valueField' => "amount",
                                                                        'conditions' => [
																			"MobileUnlockSales.refund_status" => 0,
																			 ],
                                                                    ]);
            }
			
            $unlockAmounts_query = $unlockAmounts_query->hydrate(false);
            if(!empty($unlockAmounts_query)){
                $unlockAmounts = $unlockAmounts_query->toArray();
            }else{
                $unlockAmounts = array();
            }
		
		}
               
		//getting unlock payment details
		if(!empty($unlockIdArr)){
		  $paymentArr = array();
		  $mobileUnlockPayment_query = $this->UnlockPayments->find('all',
															 array('conditions' => array('UnlockPayments.mobile_unlock_id IN' => $unlockIdArr)
																   )
															 );
		  $mobileUnlockPayment_query = $mobileUnlockPayment_query->hydrate(false);
		  if(!empty($mobileUnlockPayment_query)){
			  $mobileUnlockPayment = $mobileUnlockPayment_query->toArray();
		  }else{
			  $mobileUnlockPayment = array();
		  }
		}else{
		  $mobileUnlockPayment = array();
		}
		if(count($mobileUnlockPayment)){
			foreach($mobileUnlockPayment as $rp => $paymentDetail){
				$paymentArr[$paymentDetail['mobile_unlock_id']][] = $paymentDetail;
				if(array_key_exists($paymentDetail['mobile_unlock_id'],$payment_amount_arr) && array_key_exists($paymentDetail['payment_method'],$payment_amount_arr[$paymentDetail['mobile_unlock_id']])){
					$payment_amount_arr[$paymentDetail['mobile_unlock_id']][$paymentDetail['payment_method']]+= $paymentDetail['amount'];
				}else{
					$payment_amount_arr[$paymentDetail['mobile_unlock_id']][$paymentDetail['payment_method']] = $paymentDetail['amount'];
				}
			}
		}
		//code for payment integration ends here ***
		
		
		if(!empty($mobileUnlockPayment)){
			   $mobileUnlockStsArray = array();
			   $mobileUnlockStatus_query = $this->MobileUnlocks->find('all',array('conditions'=>array('MobileUnlocks.id IN'=>$unlockIdArr),'fields'=>array('id','status'),'recursive'=>-1));
			   $mobileUnlockStatus_query = $mobileUnlockStatus_query->hydrate(false);
			   if(!empty($mobileUnlockStatus_query)){
				   $mobileUnlockStatus = $mobileUnlockStatus_query->toArray();
			   }else{
				   $mobileUnlockStatus = array();
			   }
			   foreach($mobileUnlockStatus as $key=>$mobileUnlockSts){
					$mobileUnlockStsArray[$mobileUnlockSts['id']] = $mobileUnlockSts['status'];
				}
		}else{
		  $mobileUnlockStsArray = array();
		}
		
		//--------------------------------------
		$prem = 0;
		 if(array_key_exists('payment_mode',$this->request->query)){
			$paymentMode = $this->request->query['payment_mode'];
			if($paymentMode == 'missing'){
				if(array_key_exists('kiosk_id',$this->request->query['data']['UnlockSale']) &&
						!empty($this->request->query['data']['UnlockSale']['kiosk_id'])){
					$kiosk_id =  $this->request->query['data']['UnlockSale']['kiosk_id'];
				}else{
					$kiosk_id = '';
				}
				$mobileUnlockSales = array();
				$mobileUnlockSales = $this->fetch_missing_record($kiosk_id);
				$prem = 1;
			}
		}
		if($prem == 1){
			$paymentArr = array();
			$missing_payment = 'missing';
			$this->set(compact('missing_payment','mobileUnlockSales','kiosks','users','mobileUnlockStsArray','paymentArr','paymentMode','payment_amount_arr','saleSum','refundSum','unlockAmounts'));
		}else{
			$this->set(compact('mobileUnlockSales','kiosks','users','mobileUnlockStsArray','paymentArr','paymentMode','payment_amount_arr','saleSum','refundSum','unlockAmounts'));
		}
		
		//$this->layout = 'default';
		//$this->viewPath = 'mobileRepairs';
		$this->render('view_unlock_sales');
	}
	
	public function fetch_missing_record($kiosk_id){
		$query = "SELECT t1.`id`,t1.`mobile_unlock_id`,t1.`amount`,t1.`refund_status`,t1.`created` FROM `mobile_unlock_sales` t1 LEFT JOIN `unlock_payments` t2 ON t2.`mobile_unlock_id` = t1.`mobile_unlock_id` WHERE t2.`mobile_unlock_id` IS NULL AND t1.amount > 0";
		
		 $conn = ConnectionManager::get('default');
          $stmt = $conn->execute($query);
          $result = $stmt ->fetchAll('assoc');
		
		if(!empty($result)){
			foreach($result as $key => $value){
				$repair_ids[] = $value['mobile_unlock_id'];
			}
			$data1 = $this->MobileUnlockSales->find('all',
																 array(
																	   'conditions' => array(
																							 'mobile_unlock_id IN' => $repair_ids
																							 ),
																		'recursive' => -1
																));
			foreach($data1 as $key1 => $value1){
				if($value1['refund_status'] == 0 && $value1['amount'] > 0){
					$repair_idArr[$value1['mobile_unlock_id']] = $value1['amount'];
				}
			}
			$ids = array_keys($repair_idArr);
			$data = $this->MobileUnlockSales->find('all',
																 array(
																	   'conditions' => array(
																							 'MobileUnlockSales.mobile_unlock_id IN' => $ids,
																							 'MobileUnlockSales.refund_status' => 1,
																							 ),
																		'recursive' => -1
																));
			//pr($data);die;
			foreach($data as $k => $val){
				if(array_key_exists($val['mobile_unlock_id'],$repair_idArr)){
					unset($repair_idArr[$val['mobile_unlock_id']]);
				}
			}
			$id = array_keys($repair_idArr);
			if(!empty($kiosk_id)){
				$this->paginate = [
												'conditions' => [
																		'MobileUnlockSales.kiosk_id' => $kiosk_id,
																		'MobileUnlockSales.mobile_unlock_id IN' => $id,
																		'MobileUnlockSales.amount >' => 0
																],
												'order' => ['MobileUnlockSales.mobile_unlock_id DESC'],//, MobileRepairSale.id DESC
												'limit' => ROWS_PER_PAGE,
								  ];	
			}else{
				$this->paginate = [
												'conditions' => [
																		'MobileUnlockSales.mobile_unlock_id IN' => $id,
																		'MobileUnlockSales.amount >' => 0
																],
												'order' => ['MobileUnlockSales.mobile_unlock_id DESC'],//, MobileRepairSale.id DESC
												'limit' => ROWS_PER_PAGE,
								  ];	
			}
			//pr($this->paginate);die;
			$mobileRepairSales_query = $this->paginate('MobileUnlockSales');
			//debug($mobileRepairSales_query);die;
			return $mobileRepairSales = $mobileRepairSales_query->toArray();
		  //pr($mobileRepairSales);die;
		}else{
			
			 $mobileRepairSales = array();
			return $mobileRepairSales;
		}
	}
	
	
	public function addUnlockPayments($unlockId = ''){
		if($this->request->session()->read('Auth.User.group_id') == MANAGERS || $this->request->session()->read('Auth.User.group_id') == ADMINISTRATORS || $this->request->session()->read('Auth.User.group_id') == FRANCHISE_OWNER){
			$paymntData_query = $this->MobileUnlockSales->find('all',array(
																	'conditions' => array('MobileUnlockSales.mobile_unlock_id'=>$unlockId),
																	'recursive' => -1
																  )
													);
			$paymntData_query = $paymntData_query->hydrate(false);
			if(!empty($paymntData_query)){
			   $paymntData = $paymntData_query->first();
			}
			
			$paymentType = array('Cash' => 'Cash', 'Card' => 'Card');
			$this->set(compact('paymntData','paymentType'));
			if($this->request->is('post')){
				if(array_key_exists('cancel',$this->request->data)){
					return $this->redirect(array('controller' => 'mobile_unlock_sales','action' => 'view_unlock_sales')); 
					die;
				}
				//pr($this->request);die;
				$amountToPay = $this->request['data']['final_amount'];
				$totalPaymentAmount = 0;
				$amountDesc = array();
				$error = '';
				$errorStr = '';
				$mobile_unlock_id = $this->request['data']['Payment']['mobile_unlock_id'];
				$mobile_unlock_sale_id = $this->request['data']['Payment']['sale_id'];
				$kiosk_id = $this->request['data']['Payment']['kiosk_id'];
				$user_id = $this->Auth->user('id');
				foreach($this->request['data']['Payment']['Amount'] as $key => $paymentAmount){
					$totalPaymentAmount+= $paymentAmount; 
					$paymentDescription = $this->request['data']['Payment']['Description'][$key];
				}
				foreach($this->request['data']['Payment']['Payment_Method'] as $key => $paymentMethod){
					if($totalPaymentAmount<$amountToPay){
						$error[] = "Amount must be equivalent to &#163; {$amountToPay}. Please try again";
						break;
					}elseif($totalPaymentAmount>$amountToPay){
						$error[] = "Amount must be equivalent to &#163; {$amountToPay}. Please try again";
						break;
					}
				}
				if(!empty($error)){
					$errorStr = implode("<br/>",$error);
					$this->Flash->error("$errorStr",array('escape' => false));
					return $this->redirect(array('controller'=>'mobile_unlock_sales','action'=>'view_unlock_sales'));
				}
				$counter = 0;
				//pr($this->request['data']['Payment']['Amount']);
				foreach($this->request['data']['Payment']['Amount'] as $key => $paymentAmount){
					if(empty($paymentAmount))continue;
					$paymentMethod = $this->request['data']['Payment']['Payment_Method'][$key];
					$paymentDescription = $this->request['data']['Payment']['Description'][$key];
					$amount = 0;
					$amount =  $paymentAmount;
					if(!empty($amount)){
						//echo $amount;
						$paymentDetailData = array(
													'kiosk_id' => $kiosk_id,
													'user_id' => $user_id,
													'mobile_unlock_id'=> $mobile_unlock_id,
													'mobile_unlock_sale_id' =>$mobile_unlock_sale_id,
													'payment_method' => $paymentMethod,
													'description' => $paymentDescription,
													'amount' => $amount,
													'payment_status' => 1,
													'status' => 1,//this 1 currently does not have any relevance
												);
					}
					//pr($paymentDetailData);die;
					 $UnlockPayments = $this->UnlockPayments->newEntity();
					 $UnlockPayments = $this->UnlockPayments->patchEntity($UnlockPayments,$paymentDetailData);
					 //pr($UnlockPayments);die;
					if($this->UnlockPayments->save($UnlockPayments)){
						$counter++;
						
					}
				}//die;
				if($counter>0){
					//return $this->redirect(array('controller'=>'kiosk_product_sales','action'=>'sell_products',$product_receipt_id));
					$this->Flash->success("Payment Updated");
					return $this->redirect("/mobile-unlock-sales/viewUnlockSales");
				}else{
					$flashMessage = ("Payment could not be created. Please try again");
					$this->Flash->error($flashMessage);
					return $this->redirect(array('controller'=>'mobile-unlock-sales','action'=>'viewUnlockSales'));
				}
			}
		}
	}
	
	
	public function mobileUnlockRefund($id=null){
         $countryOptions = Configure::read('uk_non_uk');
		if($this->request->session()->read('Auth.User.group_id') != ADMINISTRATORS && $this->request->session()->read('Auth.User.group_id') != MANAGERS){
			$this->Flash->error(__('Only a Manager or an Administrator can initiate a refund'));
			return $this->redirect(array('action' => 'viewUnlockSales'));
		}else{
			$kiosks_query = $this->Kiosks->find('list',
												  [
													   'keyField' => 'id',
													   'valueField' => 'name',
													   'conditions' => ['Kiosks.status' => 1]
												  ]);
			$kiosks_query = $kiosks_query->hydrate(false);
			if(!empty($kiosks_query)){
			   $kiosks = $kiosks_query->toArray();
			}
			$users_query = $this->Users->find('list',
												  [
													   'keyField' => 'id',
													   'valueField' => 'username',
												  ]);
			$users_query = $users_query->hydrate(false);
			if(!empty($users_query)){
			   $users = $users_query->toArray();
			}
			$mobileUnlockRefund_query = $this->MobileUnlockSales->find('all',array(
										'conditions'=>array('MobileUnlockSales.id'=>$id),
										'contain' => array('Kiosks'),
										'order' => 'MobileUnlockSales.id DESC',
										)
									    );
            $mobileUnlockRefund_query = $mobileUnlockRefund_query->hydrate(false);
			if(!empty($mobileUnlockRefund_query)){
			   $mobileUnlockRefund = $mobileUnlockRefund_query->first();
			}else{
			   $mobileUnlockRefund = array();
			}
			//pr($mobileUnlockRefund);die;
                        //for email purpose getting the address
                        $kioskaddress1 = $kioskaddress2 = $kioskstate = $kioskcountry = $kioskzip = $kioskcontact = "";
                        if(!empty($mobileUnlockRefund['kiosk']['address_1'])){
                            $kioskaddress1 = "<br/>".$mobileUnlockRefund['kiosk']['address_1'].", ";
                        }
                        if(!empty($mobileUnlockRefund['kiosk']['address_2'])){
                            $kioskaddress2 = "<br/>".$mobileUnlockRefund['kiosk']['address_2'].", " ;
                        }
                        if(!empty($mobileUnlockRefund['kiosk']['city'])){
                            $kioskcity = "\t".$mobileUnlockRefund['kiosk']['city'].", ";
                        }
                        if(!empty($mobileUnlockRefund['kiosk']['state'])){
                           $kioskstate =  "<br/>".$mobileUnlockRefund['kiosk']['state'].", ";
                        }
                        if(!empty($mobileUnlockRefund['kiosk']['country'])){
                             $kioskcountry = "<br/>".$countryOptions[$mobileUnlockRefund['kiosk']['country']].", ";
                        }
                        if(!empty($mobileUnlockRefund['kiosk']['zip'])){
                             $kioskzip = "<br/>".$mobileUnlockRefund['kiosk']['zip'] ;
                        }
                        if(!empty($mobileUnlockRefund['kiosk']['contact'])){
                             $kioskcontact =  "<br/>Contact: ".$mobileUnlockRefund['kiosk']['contact'];
                        }
                        //
			$this->set(compact('mobileUnlockRefund','kiosks','users'));
			//getting repair id for the sale and the related data from the repair table
			$mobileUnlockId = $mobileUnlockRefund['mobile_unlock_id'];
			$mobileUnlockTableData_query = $this->MobileUnlocks->find('all',array(
								'conditions' => array('MobileUnlocks.id' => $mobileUnlockId),
								'recursive' => -1
									 )
							   );
			$mobileUnlockTableData_query = $mobileUnlockTableData_query->hydrate(false);
			if(!empty($mobileUnlockTableData_query)){
			   $mobileUnlockTableData = $mobileUnlockTableData_query->first();
			}
			if(empty($mobileUnlockRefund)){			
			$this->Flash->error(__('Invalid mobile unlock sale'));
				return $this->redirect(array('action' => 'viewUnlockSales'));
			}
			if($mobileUnlockTableData['status_refund'] == 1){
				$this->Flash->error(__('The customer has been already refunded for this unlock.'));
				return $this->redirect(array('action' => 'viewUnlockSales'));
			}elseif($this->request->is(array('post', 'put'))) {
				
				$unlock_email_message = $this->setting['unlock_email_message'];
				$mobile_model = $mobileUnlockTableData['mobile_model_id'];
				$iemi = $mobileUnlockTableData['imei']; 
				$modelname_query = $this->MobileModels->find('all',array(
																		  'conditions'=>array('MobileModels.id'=>$mobile_model),
																		  'fields' => array('id','model'),
																		 'recursive'=>-1
																		  ));
				$modelname_query = $modelname_query->hydrate(false);
				$modelname = $modelname_query->toArray();
				$model = $modelname['0']['model'];
				 
				if($this->request['data']['MobileUnlockSale']['refunded_amount']>$this->request['data']['MobileUnlockSale']['amount']){
							$this->Flash->error(__('The refund request could not be saved. Refund amount must be lesser than the Sale Price.'));
							return $this->redirect(array('action' => 'viewUnlockSales'));
				}
				if($this->request['data']['MobileUnlockSale']['refunded_amount'] <= 0){
						$this->Flash->error(__('The refund request could not be saved. Refund amount must be a positive number and more than zero.'));
						return $this->redirect(array('action' => 'viewUnlockSales'));
				}
				if(empty($this->request['data']['MobileUnlockSale']['refund_remarks'])){
						$this->Flash->error(__('The refund request could not be saved. Refund remarks cannot be empty.'));
						return $this->redirect(array('action' => 'viewUnlockSales'));
				}
				$this->request->data['MobileUnlockSale']['refund_amount']=-$this->request['data']['MobileUnlockSale']['refunded_amount'];
				$this->request->data['MobileUnlockSale']['amount']= 0;
                 $sold_on = $this->request->data['MobileUnlockSale']['sold_on'];
                $sold_on = date("Y-m-d h:i:s",strtotime($sold_on));
                $this->request->data['MobileUnlockSale']['sold_on'] = $sold_on;
				//pr($this->request);die;
				$entity = $this->MobileUnlockSales->newEntity();
				$entity = $this->MobileUnlockSales->patchEntity($entity, $this->request->data['MobileUnlockSale'],['validate'=>false]);
				if($this->MobileUnlockSales->save($entity)){
					//pr($mobileUnlockSale = $this->request->data['MobileUnlockSale']);
					$id_s = $id; 
					$refund_remarks = $this->request['data']['MobileUnlockSale']['refund_remarks'];
					$refund_amount = $this->request['data']['MobileUnlockSale']['refunded_amount'];
					//$refund_on = date('d-m-y h:i A', strtotime($this->request->data['MobileUnlockSale']['refund_on']));
					$refund_on = $this->request->data['MobileUnlockSale']['refund_on'];
					$refund_by = $this->request['data']['MobileUnlockSale']['refund_by'];
					$afterSaveArr = array(
						 'refund_by' => $refund_by,
						 'refund_remarks' => $refund_remarks,
						 'refund_on' => $refund_on,
						 'refund_amount' => $refund_amount,
						 'status' => 1,
					);
					$entity1 = $this->MobileUnlockSales->get($id_s);
					$entity1 = $this->MobileUnlockSales->patchEntity($entity1, $afterSaveArr,['validate' => false]);
					$this->MobileUnlockSales->save($entity1);
					//$this->MobileUnlockSale->saveField('refund_by',$refund_by);//sending refund by to the original sale, implies that it has been refunded
					//$this->MobileUnlockSale->saveField('refund_remarks',$refund_remarks);//sending refund remarks to the original sale, implies that it has been refunded
					//$this->MobileUnlockSale->saveField('refund_on',$refund_on);//sending refund on to the original sale, implies that it has been refunded
					//$this->MobileUnlockSale->saveField('refund_amount',$refund_amount);//sending refund amount to the original sale, implies that it has been refunded
					//$this->MobileUnlockSale->saveField('status',1);//sending status 1 to the original sale, implies that it has been refunded
					if($this->request->data['MobileUnlockSale']['refund_status'] == 1){					
						$unlockEditData = array('status_refund' => 1);
						//					pr($unlockEditData);die;
						$unlock_entity = $this->MobileUnlocks->get($mobileUnlockId);
						$unlock_entity = $this->MobileUnlockSales->patchEntity($unlock_entity, $unlockEditData);
						$this->MobileUnlocks->save($unlock_entity);      
					}
					$send_by_email = Configure::read('send_by_email');
					$emailSender = Configure::read('EMAIL_SENDER');
					// sending email to the customer
					$recipient = $mobileUnlockTableData['customer_email'];
					//pr($mobileUnlockTableData['MobileUnlock']);
					$name = $mobileUnlockTableData['customer_fname'];
					$kioskCode = $mobileUnlockTableData['kiosk_id'];
					$refundAmount = $this->request->data['MobileUnlockSale']['refunded_amount'];
					$refundOn = date('d-m-y h:i A', strtotime($this->request->data['MobileUnlockSale']['refund_on'])); 
					$content = "Hi ".$name."<br/><br/>As requested, the refund has been processed for an amount of &#163;".$refundAmount."\tfor your mobile(imei:".$iemi.",\tmodel:".$model.")\t on \t".$refundOn.". <br/><br/>Regards,<br/>".$kiosks[$kioskCode].$kioskaddress1.$kioskaddress2.$kioskcity.$kioskstate.$kioskcountry.$kioskzip.$kioskcontact."</br>".$unlock_email_message;
					//echo $recipient;die;
					$Email = new Email();
					$Email->config('default');
					$Email->viewVars(array('mobileUnlockTableData' => $mobileUnlockTableData, 'recipient' => $recipient, 'name' => $name, 'refundAmount' => $refundAmount, 'refundOn' => $refundOn, 'kioskCode' => $kioskCode, 'content' => $content));
					$Email->template('repair_refund');
					$Email->emailFormat('both');
					$emailTo = $recipient;
					$Email->to($emailTo);
					$Email->transport(TRANSPORT);
				$Email->from([$send_by_email => $emailSender]);
					//$Email->sender("sourabh.proavid@gmail.com");
					$Email->subject('Mobile Unlock Refund Details');
					$Email->send();
					
					$this->Flash->success(__('The refund request has been saved'));
					return $this->redirect(array('action' => 'viewUnlockSales'));
				}else{
					$this->Flash->error(__('The refund request could not be saved. Please, try again.'));
					return $this->redirect(array('action' => 'viewUnlockSales'));
				}
			}
		}
	}
    
}


?>