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
use Cake\Controller\Component\AuthComponent;
use Cake\Network\Exception\NotFoundException;
use Cake\View\Helper\FlashHelper;


class MobileBlkReSalesController extends AppController{
	
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
			$this->loadModel('MobileBlkReSalePayments');
			$this->loadModel('Kiosks');
			$this->loadModel('Users');
			$this->loadModel('MobileBlkReSales');
			$this->loadModel('MobilePurchases');
			$this->loadModel('exists');
			$this->loadModel('Networks');
			$this->loadModel('MobileTransferLogs');
			$this->loadModel('Users');
			
			$CURRENCY_TYPE = Configure::read('CURRENCY_TYPE');
			$this->set(compact('CURRENCY_TYPE'));
			
			$this->fromemail = Configure::read('FROM_EMAIL');
            $gradeType = Configure::read('grade_type');
            $resaleOptions = Configure::read('resale_statuses');
            $countryOptions = Configure::read('uk_non_uk');
            $discountOptions = Configure::read('discount');
            $colorOptions = Configure::read('color');
            $this->set(compact('resaleOptions','countryOptions','gradeType','colorOptions','discountOptions'));
			$colorOptions = Configure::read('color');
            $activeOptions = Configure::read('active');
            $CURRENCY_TYPE = Configure::read('CURRENCY_TYPE');
         $this->set(compact('activeOptions','CURRENCY_TYPE','colorOptions','gradeType'));
        }
	public function index() {
		//$this->MobileBlkReSale->query("DELETE FROM `mobile_blk_re_sales` WHERE `id` = '62'");
		//pr($this->MobileBlkReSale->find('all',array('recursive'=>-1,'order'=>'MobileBlkReSale.id desc','limit'=>20)));
		$brands_query = $this->MobileBlkReSales->Brands->find('list',[
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
                                'conditions' => ['MobileBlkReSales.kiosk_id'=>$kiosk_id,
							    'MobileBlkReSales.refund_status IS NULL'],
								'order' => ['MobileBlkReSales.id desc'],
								'limit' => ROWS_PER_PAGE,
							];
			
			//data for all mobiles excluding the new entries that get created on refund
            $mobileSale_conn = ConnectionManager::get('default');
            $mobileSale_stmt = $mobileSale_conn->execute("SELECT `discounted_price`, `selling_price`, 
						    case when `discounted_price` is NULL OR `discounted_price` = 0 THEN 
						    `selling_price` 
						    ELSE 
						    `discounted_price` 
						    END 
						    as `mobileSale`
						    from `mobile_blk_re_sales` as MobileBlkReSales WHERE `refund_status` is NULL AND `kiosk_id` = $kiosk_id"); 
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
						    from `mobile_blk_re_sales`as MobileBlkReSales WHERE `refund_status` = 1 AND `kiosk_id` = $kiosk_id"); 
            $mobileRefund = $mobileRefund_stmt ->fetchAll('assoc');
						    //status 1 is for the refunded mobiles
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
									'conditions' => ['MobileBlkReSales.refund_status IS NULL',
													 'MobileBlkReSales.kiosk_id IN' => $managerKiosk,
													 ],
									'order' => ['MobileBlkReSales.id desc'],
									'limit' => ROWS_PER_PAGE,
                        		//'recursive' => -1
							];		
					}else{
						$this->paginate = [
									'conditions' => ['MobileBlkReSales.refund_status IS NULL'],
									'order' => ['MobileBlkReSales.id desc'],
									'limit' => ROWS_PER_PAGE,
                        		//'recursive' => -1
							];
					}
				}else{
					$this->paginate = [
									'conditions' => ['MobileBlkReSales.refund_status IS NULL'],
									'order' => ['MobileBlkReSales.id desc'],
									'limit' => ROWS_PER_PAGE,
                        		//'recursive' => -1
							];
				}
			
				
			//}
			
			//data for all mobiles excluding the new entries that get created on refund
            $mobileSale_conn = ConnectionManager::get('default');
            $mobileSale_stmt = $mobileSale_conn->execute("SELECT `discounted_price`, `selling_price`, 
						    case when `discounted_price` is NULL OR `discounted_price` = 0 THEN 
						    `selling_price` 
						    ELSE 
						    `discounted_price` 
						    END 
						    as `mobileSale`
						    from `mobile_blk_re_sales`as MobileBlkReSales WHERE `refund_status` is NULL"); 
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
						    from `mobile_blk_re_sales`as MobileBlkReSales WHERE `refund_status` = 1"); 
            $mobileRefund = $mobileRefund_stmt ->fetchAll('assoc');
						    //status 1 is for the refunded mobiles
                            
		}
		
		$totalMobileRefund = 0;
		if(!empty($mobileRefund[0]['totalRefund'])){
			$totalMobileRefund = $mobileRefund[0]['totalRefund'];
		}
		
		$totalRefundGain = 0;
		if(!empty($mobileRefund[0]['totalRefundGain'])){
			$totalRefundGain = $mobileRefund[0]['totalRefundGain'];
		}
		$grandNetSale = $totalMobileSale-$totalMobileRefund;
		$mobileReSales_query = $this->paginate('MobileBlkReSales');
        if(!empty($mobileReSales_query)){
            $mobileReSales = $mobileReSales_query->toArray();
        }else{
            $mobileReSales = array();
        }
       // $this->set('users', $this->Paginator->paginate());
		//pr($mobileReSales);
		$this->set(compact('mobileReSales'));
		$mobileModelId = array();
		$saleIdArr = array();
		foreach($mobileReSales as $key=>$mobileSale){
			$saleIdArr[$mobileSale->id] = $mobileSale->id;
			$mobileModelId[$mobileSale->mobile_model_id] = $mobileSale->mobile_model_id;
		}
        if(empty($mobileModelId)){
            $mobileModelId = array('0'=>null);
        }
		if(empty($saleIdArr)){
            $saleIdArr = array('0'=>null);
        }
		//** getting payment details
		$paymentArr = array();
		$payment_amount_arr = array();
		$mobileResalePayment_query = $this->MobileBlkReSalePayments->find('all', array('conditions' => array('MobileBlkReSalePayments.mobile_blk_re_sale_id IN' => $saleIdArr)));
        $mobileResalePayment_query = $mobileResalePayment_query->hydrate(false);
        if(!empty($mobileResalePayment_query)){
            $mobileResalePayment = $mobileResalePayment_query->toArray();
        }else{
            $mobileResalePayment = array();
        }
		if(count($mobileResalePayment)){
			//pr($mobileResalePayment);die;
			foreach($mobileResalePayment as $rp => $resalePayment){
				//pr($resalePayment);die;
				$mobilePurchaseID = $resalePayment['mobile_purchase_id'];
				$mobileBulkReSaleID = $resalePayment['mobile_blk_re_sale_id'];
				$paymentArr[$mobileBulkReSaleID][] = $resalePayment;
				$pmtMethod = $resalePayment['payment_method'];
				$blkPmt = $resalePayment['amount'];
				if(
				   array_key_exists($mobileBulkReSaleID, $payment_amount_arr) &&
				   array_key_exists($pmtMethod,$payment_amount_arr[$mobileBulkReSaleID])){
					$payment_amount_arr[$mobileBulkReSaleID][$pmtMethod]+= $blkPmt;
				}else{
					$payment_amount_arr[$mobileBulkReSaleID][$pmtMethod] = $blkPmt;
				}
			}
			//pr($payment_amount_arr);die;
		}
		//code for payment ends here **
		
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
		
        //pr($this->request->query);die;
		$mobileCostData = 0;
		$paymentMode = 'Multiple';
		$refundStatus = NULL;
		if(array_key_exists('payment_mode',$this->request->query)){
			$paymentMode = $this->request->query['payment_mode'];
			if($paymentMode == 'refunded'){
				$refundStatus = 1;
			}
		}
		$brands_query = $this->MobileBlkReSales->Brands->find('list',[
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
		$conditionArr = $this->generate_condition_array1();
        //pr($conditionArr);die;
		$currency = $this->setting['currency_symbol'];
		$kioskid = $this->request->Session()->read('kiosk_id');
		if($kioskid>0){
			
            if($refundStatus == '' || $refundStatus == NULL){
                $mobileSale_query = $this->MobileBlkReSales->find('all', array( 
                                    'conditions' => array('MobileBlkReSales.kiosk_id'=>$kioskid,$conditionArr),
                                    'contain' => array('Brands' , 'MobileModels')
                                        ));
                $mobileSale_query 
                       ->select(['mobileSale'=>'CASE WHEN MobileBlkReSales.discounted_price is NULL THEN MobileBlkReSales.selling_price ELSE MobileBlkReSales.discounted_price END'])
                       ->select('MobileBlkReSales.id')
                       ->select('MobileBlkReSales.discounted_price')
                       ->select( 'MobileBlkReSales.selling_price')
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
                $mobileSale_query = $this->MobileBlkReSales->find('all', array( 
                                    'conditions' => array('MobileBlkReSales.refund_status'=>$refundStatus,'MobileBlkReSales.kiosk_id'=>$kioskid,$conditionArr),
                                    'contain' => array('Brands' , 'MobileModels')
                                        ));
                $mobileSale_query 
                       ->select(['mobileSale'=>'CASE WHEN MobileBlkReSales.discounted_price is NULL THEN MobileBlkReSales.selling_price ELSE MobileBlkReSales.discounted_price END'])
                       ->select('MobileBlkReSales.id')
                       ->select('MobileBlkReSales.discounted_price')
                       ->select( 'MobileBlkReSales.selling_price');
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
			
            $mobileRefund_query = $this->MobileBlkReSales->find('all',[
                                                                    'conditions' =>['MobileBlkReSales.refund_status'=>1,'MobileBlkReSales.kiosk_id'=>$kioskid,$conditionArr],
                                                                    'contain' => ['Brands' , 'MobileModels']
                                                                    ]
                                                            );
            $mobileRefund_query
                    ->select('MobileBlkReSales.refund_price')
                    ->select('MobileBlkReSales.refund_gain')
                    ->select(['totalRefund' => $mobileRefund_query->func()->sum('MobileBlkReSales.refund_price')])
                    ->select(['totalRefundGain' => $mobileRefund_query->func()->sum('MobileBlkReSales.refund_gain')]);
            $mobileRefund_query = $mobileRefund_query->hydrate(false);
            if(!empty($mobileRefund_query)){
                $mobileRefund = $mobileRefund_query->first();
            }else{
                $mobileRefund = array();
            }
            
		}else{
			$kioskId = $this->request->query['MobileBlkReSale']['kiosk_id'];
			
            if($refundStatus == '' || $refundStatus == NULL){
                $mobileSale_query = $this->MobileBlkReSales->find('all', array( 
                    'conditions' => array($conditionArr),
                    'contain' => array('Brands' , 'MobileModels')
                        ));
                $mobileSale_query 
                       ->select(['mobileSale'=>'CASE WHEN MobileBlkReSales.discounted_price is NULL THEN MobileBlkReSales.selling_price ELSE MobileBlkReSales.discounted_price END'])
                       ->select('MobileBlkReSales.id')
                       ->select('MobileBlkReSales.discounted_price')
                       ->select( 'MobileBlkReSales.selling_price')
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
                $mobileSale_query = $this->MobileBlkReSales->find('all', array( 
                                    'conditions' => array('MobileBlkReSales.refund_status'=>$refundStatus,$conditionArr),
                                    'contain' => array('Brands' , 'MobileModels')
                                        ));
                $mobileSale_query 
                       ->select(['mobileSale'=>'CASE WHEN MobileBlkReSales.discounted_price is NULL THEN MobileBlkReSales.selling_price ELSE MobileBlkReSales.discounted_price END'])
                       ->select('MobileBlkReSales.id')
                       ->select('MobileBlkReSales.discounted_price')
                       ->select( 'MobileBlkReSales.selling_price');
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
            
            $mobileRefund_query = $this->MobileBlkReSales->find('all',[
                                                                    'conditions' =>['MobileBlkReSales.refund_status'=>1,$conditionArr],
                                                                    'contain' => ['Brands' , 'MobileModels']
                                                                    ]
                                                            );
            $mobileRefund_query
                    ->select('MobileBlkReSales.refund_price')
                    ->select('MobileBlkReSales.refund_gain')
                    ->select(['totalRefund' => $mobileRefund_query->func()->sum('MobileBlkReSales.refund_price')])
                    ->select(['totalRefundGain' => $mobileRefund_query->func()->sum('MobileBlkReSales.refund_gain')]);
            $mobileRefund_query = $mobileRefund_query->hydrate(false);
            if(!empty($mobileRefund_query)){
                $mobileRefund = $mobileRefund_query->first();
            }else{
                $mobileRefund = array();
            }
           
		}
		$saleIds = array();
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
            $searchPaymentResult_query = $this->MobileBlkReSalePayments->find('all', array('conditions' => array('MobileBlkReSalePayments.mobile_blk_re_sale_id IN' => $saleIds, 'MobileBlkReSalePayments.payment_method' => $paymentMode)));
            $searchPaymentResult_query = $searchPaymentResult_query->hydrate(false);
            if(!empty($searchPaymentResult_query)){
                $searchPaymentResult = $searchPaymentResult_query->toArray();
            }else{
                $searchPaymentResult = array();
            }
			if(count($searchPaymentResult)){
				foreach($searchPaymentResult as $spr => $searchPaymentInfo){
					$payment_array[$searchPaymentInfo['mobile_blk_re_sale_id']] = $searchPaymentInfo['mobile_blk_re_sale_id'];

				}
				if(empty($payment_array)){
                    $payment_array = array(0 => null);
                }
                $mobileSale_query = $this->MobileBlkReSales->find('all', array( 
								'conditions' => array('MobileBlkReSales.id IN'=>$payment_array
									)));
                $mobileSale_query 
                       ->select('MobileBlkReSales.id')
                       ->select('MobileBlkReSales.discounted_price')
                       ->select('MobileBlkReSales.selling_price')
                       ->select(['mobileSale'=>'CASE WHEN MobileBlkReSales.discounted_price is NULL THEN MobileBlkReSales.selling_price ELSE MobileBlkReSales.discounted_price END']);
                       //pr($mobileSale_query);die;
                $mobileSale_query = $mobileSale_query->hydrate(false);
                if(!empty($mobileSale_query)){
                    $mobileSale = $mobileSale_query->toArray();
                }else{
                    $mobileSale = array();
                }
               if(empty($payment_array)){
                $payment_array = array(0 => null);
               }
			    $managerKiosk =  $this->get_kiosk();
               $mobileRefund_query = $this->MobileBlkReSales->find('all',[
                                                                        'conditions' =>['MobileBlkReSales.id IN'=>$payment_array,
																						'MobileBlkReSales.kiosk_id IN'=>$managerKiosk,
																						]
                                                                    ]
                                                            );
                $mobileRefund_query
                        ->select('MobileBlkReSales.refund_price')
                        ->select('MobileBlkReSales.refund_gain')
                        ->select(['totalRefund' => $mobileRefund_query->func()->sum('MobileBlkReSales.refund_price')])
                        ->select(['totalRefundGain' => $mobileRefund_query->func()->sum('MobileBlkReSales.refund_gain')]);;
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
            $saleSumData_query = $this->MobileBlkReSalePayments->find('all',[
                                                                        'conditions' =>['payment_method' => 'Card', 'MobileBlkReSalePayments.mobile_blk_re_sale_id IN' => $paymentSaleIds],
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
            $saleSumData_query = $this->MobileBlkReSalePayments->find('all',[
                                                                        'conditions' =>['payment_method' => 'Cash', 'MobileBlkReSalePayments.mobile_blk_re_sale_id IN' => $paymentSaleIds],
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
           
			//pr($saleSumData);
		}else{//if($paymentMode == 'Multiple')
            
            if(empty($paymentSaleIds)){
                $paymentSaleIds = array(0 => null);
            }
            
            $managerKiosk =  $this->get_kiosk();
            $saleSumData_query = $this->MobileBlkReSalePayments->find('all',[
                                                                        'conditions' =>[
															'MobileBlkReSalePayments.mobile_blk_re_sale_id IN' => $paymentSaleIds,
																						'kiosk_id IN' => $managerKiosk,
																						],
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
							$this->paginate = [
                                    'conditions' => ['MobileBlkReSales.id IN'=>$payment_array,function ($q) {
                                                            return $q->isNull('refund_status');
                                                        },
														'MobileBlkReSales.kiosk_id IN' => $managerKiosk,
														],
                                    'order' => ['MobileBlkReSales.id desc'],
                                    'limit' => ROWS_PER_PAGE,
                                   ];
						}else{
							$this->paginate = [
                                    'conditions' => ['MobileBlkReSales.id IN'=>$payment_array,function ($q) {
                                                            return $q->isNull('refund_status');
                                                        }],
                                    'order' => ['MobileBlkReSales.id desc'],
                                    'limit' => ROWS_PER_PAGE,
                                   ];
						}
				 }else{
						$this->paginate = [
                                    'conditions' => ['MobileBlkReSales.id IN'=>$payment_array,function ($q) {
                                                            return $q->isNull('refund_status');
                                                        }],
                                    'order' => ['MobileBlkReSales.id desc'],
                                    'limit' => ROWS_PER_PAGE,
                                   ];	
				 }
                
                
            }else{
				
				if($ext_site == 1){
						$managerKiosk = $this->get_kiosk();
						if(!empty($managerKiosk)){
							$this->paginate = [
                                    'conditions' => ['MobileBlkReSales.id IN'=>$payment_array,
													 'MobileBlkReSales.refund_status' =>$refundStatus,
													 'MobileBlkReSales.kiosk_id IN' => $managerKiosk
													 ],
                                    'order' => ['MobileBlkReSales.id desc'],
                                    'limit' => ROWS_PER_PAGE,
                                   ];
						}else{
							$this->paginate = [
                                    'conditions' => ['MobileBlkReSales.id IN'=>$payment_array,
													 'MobileBlkReSales.refund_status' =>$refundStatus
													 
													 ],
                                    'order' => ['MobileBlkReSales.id desc'],
                                    'limit' => ROWS_PER_PAGE,
                                   ];
						}
				}else{
					$this->paginate = [
                                    'conditions' => ['MobileBlkReSales.id IN'=>$payment_array,'MobileBlkReSales.refund_status' =>$refundStatus],
                                    'order' => ['MobileBlkReSales.id desc'],
                                    'limit' => ROWS_PER_PAGE,
                                   ];	
				}
            }
           
           if($refundStatus == '' || $refundStatus == NULL){
                $mobileCostData1_query = $this->MobileBlkReSales->find('all', array(
                                'conditions' => array('MobileBlkReSales.id IN'=>$payment_array,function ($q) {
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
                $mobileCostData1_query = $this->MobileBlkReSales->find('all', array(
                                'conditions' => array('MobileBlkReSales.id IN'=>$payment_array,
                                        'MobileBlkReSales.refund_status'=>$refundStatus),
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
           
			//pr($mobileCostentries);die;
			foreach($mobileCostentries as $key1 => $value){
				$mobileCostData = $mobileCostData + $value['mobileSalePrice'];
			}
			
		}elseif($kioskid>0){
			
            if($refundStatus == "" || $refundStatus == NULL){
                //pr($kioskid);die;
                $this->paginate = [
                                    'conditions' => ['MobileBlkReSales.kiosk_id'=>$kioskid,
                                     function ($q) {
                                                            return $q->isNull('refund_status');
                                                        },
                                      $conditionArr],
                                    'order' => ['MobileBlkReSales.id desc'],
                                    'limit' => ROWS_PER_PAGE,
                                    'contain' => ['Brands' , 'MobileModels']
                                ];
            }else{
                $this->paginate = [
                    'conditions' => ['MobileBlkReSales.kiosk_id'=>$kioskid,
                      'MobileBlkReSales.refund_status'=>$refundStatus,
                      $conditionArr],
                    'order' => ['MobileBlkReSales.id desc'],
                    'limit' => ROWS_PER_PAGE,
                    'contain' => ['Brands' , 'MobileModels']
                ];
            }
          
			if($refundStatus == "" || $refundStatus == NULL){
                $mobileCostData1_query = $this->MobileBlkReSales->find('all', array(
                                'conditions' => array('MobileBlkReSales.kiosk_id'=>$kioskid,
                                                      function ($q) {
                                                            return $q->isNull('refund_status');
                                                        },
                                      $conditionArr),
                                'fields' => array('id','mobile_purchase_id'),
                                'contain' => array('Brands' , 'MobileModels')
                                        ));
              
            }else{
                    $mobileCostData1_query = $this->MobileBlkReSales->find('all', array(
                    'conditions' => array('MobileBlkReSales.kiosk_id'=>$kioskid,
                          'MobileBlkReSales.refund_status'=>$refundStatus,
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
           
			//pr($mobileCostentries);die;
			foreach($mobileCostentries as $key1 => $value){
				$mobileCostData = $mobileCostData + $value['mobileSalePrice'];
			}
			
		}else{
			
            if($refundStatus == "" || $refundStatus == NULL){
				 if($ext_site == 1){
					$managerKiosk = $this->get_kiosk();
					if(!empty($managerKiosk)){
						$conditionArr['MobileBlkReSales.kiosk_id IN'] = $managerKiosk;
					}
				 }
                $this->paginate = [
                                    'conditions' => [
                                                    function ($q) {
                                                            return $q->isNull('refund_status');
                                                        },$conditionArr],
                                    'order' => ['MobileBlkReSales.id desc'],
                                    'limit' => ROWS_PER_PAGE,
                                    'contain' => ['Brands' , 'MobileModels']
                                  ];
            }else{
				if($ext_site == 1){
					$managerKiosk = $this->get_kiosk();
					if(!empty($managerKiosk)){
						$conditionArr['MobileBlkReSales.kiosk_id IN'] = $managerKiosk;
					}
				 }
                $this->paginate = [
                                    'conditions' => [
                                                     'MobileBlkReSales.refund_status' => $refundStatus
                                                        ,$conditionArr],
                                    'order' => ['MobileBlkReSales.id desc'],
                                    'limit' => ROWS_PER_PAGE,
                                    'contain' => ['Brands' , 'MobileModels']
                                  ];
            }
			
			if($refundStatus == 1){
                
                if($refundStatus == "" || $refundStatus == NULL){
                $mobileCostData1_query = $this->MobileBlkReSales->find('all', array(
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
                    $mobileCostData1_query = $this->MobileBlkReSales->find('all', array(
                                    'conditions' => array(
                                        'MobileBlkReSales.refund_status'=>$refundStatus,
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
              
			}else{
                
                if($refundStatus == "" || $refundStatus == NULL){
                $mobileCostData1_query = $this->MobileBlkReSales->find('all', array(
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
                    $mobileCostData1_query = $this->MobileBlkReSales->find('all', array(
                                    'conditions' => array(
                                        'MobileBlkReSales.refund_status'=>$refundStatus,
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
			
			//pr($mobileCostentries);die;
			$priceArr = array();
			foreach($mobileCostentries as $key1 => $value){
				$priceArr[$value['id']] = $value['mobileSalePrice'];
			}
			foreach($purchaseIds as $a => $b){
				if(array_key_exists($b,$priceArr)){
					$mobileCostData = $mobileCostData + $priceArr[$b];
				}
			}
			if($refundStatus == NULL){
				$refund = array();
                
                $refundPrice_query = $this->MobileBlkReSales->find('all', array(
                                    'conditions' => array(
									'MobileBlkReSales.refund_status'=> 1,
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
        $mobileResalePayment_query = $this->MobileBlkReSalePayments->find('all', array('conditions' => array('MobileBlkReSalePayments.mobile_blk_re_sale_id IN' => $saleIds)));
        $mobileResalePayment_query = $mobileResalePayment_query->hydrate(false);
        if(!empty($mobileResalePayment_query)){
            $mobileResalePayment = $mobileResalePayment_query->toArray();
        }else{
            $mobileResalePayment = array();
        }
		
		if(count($mobileResalePayment)){
			foreach($mobileResalePayment as $rp => $resalePayment){
				$mobilePurchaseID = $resalePayment['mobile_purchase_id'];
				$mobileBulkReSaleID = $resalePayment['mobile_blk_re_sale_id'];
				$paymentArr[$mobileBulkReSaleID][] = $resalePayment;
				$pmtMethod = $resalePayment['payment_method'];
				$blkPmt = $resalePayment['amount'];
				if(
				   array_key_exists($mobileBulkReSaleID, $payment_amount_arr) &&
				   array_key_exists($pmtMethod,$payment_amount_arr[$mobileBulkReSaleID])){
					$payment_amount_arr[$mobileBulkReSaleID][$pmtMethod]+= $blkPmt;
				}else{
					$payment_amount_arr[$mobileBulkReSaleID][$pmtMethod] = $blkPmt;
				}
			}
		}
		//pr($this->paginate);die;
		$mobileReSales_query = $this->paginate('MobileBlkReSales');
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
		//pr($this->request->query);die;
		
		$searchKW = trim(strtolower($this->request->query['search_kw']));
		$conditionArr = array();
		if(!empty($searchKW)){
			$conditionArr['OR']['MobileBlkReSales.imei like'] =  strtolower("%$searchKW%");
			//$conditionArr['OR']['Brands.brand like'] = strtolower("%$searchKW%");
			$conditionArr['OR']['MobileBlkReSales.customer_email like'] = strtolower("%$searchKW%");
			//$conditionArr['OR']['MobileModels.model like'] = strtolower("%$searchKW%");
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
			"MobileBlkReSales.created >" => date('Y-m-d', strtotime($this->request->query['start_date'])),
			"MobileBlkReSales.created <" => date('Y-m-d', strtotime($this->request->query['end_date']. ' +1 Days')),   
			);
		}
		//echo "admin";
		$kioskid = $this->request->Session()->read('kiosk_id');
		if($kioskid == ""){
            //pr($this->request->query);die;
			if(array_key_exists('MobileBlkReSale',$this->request->query)){
				$kioskId = $this->request->query['MobileBlkReSale']['kiosk_id'];
				$this->set('kioskId', $kioskId);
				if(array_key_exists('kiosk_id',$this->request->query['MobileBlkReSale']) && !empty($this->request->query['MobileBlkReSale']['kiosk_id'])){
					//echo "admin1";
					if($kioskId == "10000"){
						$kioskId = "0";
						$conditionArr[] =  array('kiosk_id' => $kioskId);
					}else{
					 $conditionArr[] =  array('kiosk_id' => $this->request->query['MobileBlkReSale']['kiosk_id']);
					}
				}
			}else{
				$kioskId = "";
			}
			//$this->set('kioskId', $kioskId);
		}
		if($kioskid>0){
			//echo "kiosk";
			//$conditionArr[] = array('MobileResale.kiosk_id' => "$kioskid",);
			$conditionArr[] = array('kiosk_id' =>$kioskid);
		}
		return $conditionArr;
	}
    
    private function generate_condition_array1(){
		//pr($this->request->query);die;
		
		$searchKW = trim(strtolower($this->request->query['search_kw']));
		$conditionArr = array();
		if(!empty($searchKW)){
			$conditionArr['OR']['MobileBlkReSales.imei like'] =  strtolower("%$searchKW%");
			$conditionArr['OR']['Brands.brand like'] = strtolower("%$searchKW%");
			$conditionArr['OR']['MobileBlkReSales.customer_email like'] = strtolower("%$searchKW%");
			$conditionArr['OR']['MobileModels.model like'] = strtolower("%$searchKW%");
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
			"MobileBlkReSales.created >" => date('Y-m-d', strtotime($this->request->query['start_date'])),
			"MobileBlkReSales.created <" => date('Y-m-d', strtotime($this->request->query['end_date']. ' +1 Days')),   
			);
		}
		//echo "admin";
		$kioskid = $this->request->Session()->read('kiosk_id');
		if($kioskid == ""){
            //pr($this->request->query);die;
			if(array_key_exists('MobileBlkReSale',$this->request->query)){
				$kioskId = $this->request->query['MobileBlkReSale']['kiosk_id'];
				$this->set('kioskId', $kioskId);
				if(array_key_exists('kiosk_id',$this->request->query['MobileBlkReSale']) && !empty($this->request->query['MobileBlkReSale']['kiosk_id'])){
					//echo "admin1";
					if($kioskId == "10000"){
						$kioskId = "10000";
						$conditionArr[] =  array('kiosk_id' => $kioskId);
					}else{
					 $conditionArr[] =  array('kiosk_id' => $this->request->query['MobileBlkReSale']['kiosk_id']);
					}
				}
			}else{
				$kioskId = "";
			}
			//$this->set('kioskId', $kioskId);
		}
		if($kioskid>0){
			//echo "kiosk";
			//$conditionArr[] = array('MobileResale.kiosk_id' => "$kioskid",);
			$conditionArr[] = array('kiosk_id' =>$kioskid);
		}
		$managerKiosk = $this->get_kiosk();
		if(!array_key_exists('kiosk_id',$conditionArr)){
			$conditionArr['kiosk_id IN'] = $managerKiosk;
		}
		return $conditionArr;
	}
	public function edit($id = null) {
        
		if (!$this->MobileBlkReSales->exists($id)) {
			throw new NotFoundException(__('Invalid mobile re sale'));
		}else{
		  $mobile_re_sale_entity = $this->MobileBlkReSales->get($id);
		  $this->set(compact('mobile_re_sale_entity'));
		}
        $kiosks_query = $this->MobileBlkReSales->Kiosks->find('list');
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
        $mobileResaleData_query = $this->MobileBlkReSales->find('all',array(
																	'conditions' => array('MobileBlkReSales.id' => $id)
																	));
        $mobileResaleData_query = $mobileResaleData_query->hydrate(false);
        if(!empty($mobileResaleData_query)){
            $mobileResaleData = $mobileResaleData_query->first();
        }else{
            $mobileResaleData = array();
        }
		
		//pr($mobileResaleData);die;
		$created = strtotime($mobileResaleData['created']);
        $currentTime_conn = ConnectionManager::get('default');
        $currentTime_stmt = $currentTime_conn->execute('SELECT NOW() as timeDate'); 
        $currentTime = $currentTime_stmt ->fetchAll('assoc');
		$curTime = strtotime($currentTime[0]['timeDate']);
		//checking if the repair belongs to the kiosk for customers screen
       
		if($this->request->session()->read('Auth.User.group_id') == KIOSK_USERS){
			$diffOfTime = $curTime-$created;
			//if($diffOfTime > 600){ //
			//	$diffInMins = number_format($diffOfTime / 60,2);
			//	$this->Flash->error("You can only edit the sale within 10 mintues(Current Difference: $diffInMins Mins)!");
			//	return $this->redirect(array('controller' => 'mobile_blk_re_sales','action' => 'index'));
			//	die;
			//}
			if($mobileResaleData['kiosk_id'] != $this->request->Session()->read('kiosk_id')){
				$this->Flash->error("You can only edit the sale belonging to your kiosk!");
				return $this->redirect(array('controller' => 'retail_customers', 'action' => 'index'));
				die;
			}
		}
		if ($this->request->is(array('post', 'put'))){
			//pr($this->request);die;
 
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
					$this->Flash->error($error);
					return $this->redirect(array('action' => 'edit',$id));
					die;
				}else{
					//pr($this->request);die;
					//****saving newly added payment amount
					if(array_key_exists('added_amount',$this->request->data) && is_numeric($this->request->data['added_amount'])){
                        
                        $paymntData_query = $this->MobileBlkReSalePayments->find('all',array(
									'conditions' => array('MobileBlkReSalePayments.mobile_blk_re_sale_id'=>$id),
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
						$paymntData['payment_method'] = $this->request->data['new_change_mode'];
						$paymntData['amount'] = $this->request->data['added_amount'];
                        $new_entity = $this->MobileBlkReSalePayments->newEntity($paymntData,['validate' => false]);
                        $patch_entity = $this->MobileBlkReSalePayments->patchEntity($new_entity,$paymntData,['validate' => false]);
						if($this->MobileBlkReSalePayments->save($patch_entity)){
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
                        $getId = $this->MobileBlkReSalePayments->get($paymentId);
                        $patchEntity = $this->MobileBlkReSalePayments->patchEntity($getId,$resalePaymentData);
						if($this->MobileBlkReSalePayments->save($patchEntity)){
							$saveCount++;
						}
					}
				}
				
				if($saveCount > 0){
					//$this->MobileBlkReSale->id = $id;
                    
                    $get_Id = $this->MobileBlkReSales->get($id);
                    $data_array = array();
                    $data_array['selling_price'] = $this->request->data['updated_selling_price'];
                    $data_array['discounted_price'] = $this->request->data['updated_discounted_price'];
                    $data_array['discount'] = $this->request->data['updated_discount'];
                    $patch_Entity = $this->MobileBlkReSales->patchEntity($get_Id,$data_array);
                    
					$this->MobileBlkReSales->save($patch_Entity);
					//$this->MobileBlkReSale->saveField('discounted_price',$this->request->data['updated_discounted_price']);
					//$this->MobileBlkReSale->saveField('discount',$this->request->data['updated_discount']);
					
					$this->Flash->success('Payment has been successfully updated!');
					return $this->redirect(array('action' => 'index'));
				}else{
					$this->Flash->error('Payment could not be updated!');
					return $this->redirect(array('action' => 'edit',$id));
				}
				die;
			}
             //  pr($this->request->data);     
            $custmGrade = $this->request->data['custom_grade'];
            $lowestSellingPrice = $this->request->data['lowest_selling_price'];
            $hidden_discount = $this->request->data['hidden_discount'];
            $discount = $this->request->data['discount'];
            $hidden_selling_price = $this->request->data['hidden_selling_price'];
            $selling_price = $this->request->data['selling_price'];
            $hidden_discounted_price = $this->request->data['hidden_discounted_price'];
            $discounted_price = $this->request->data['discounted_price']; 
			if($custmGrade == 1){
				if($this->request->session()->read('Auth.User.group_id') == ADMINISTRATORS || $this->request->session()->read('Auth.User.group_id') == FRANCHISE_OWNER || $this->request->session()->read('Auth.User.group_id') == MANAGERS || $this->request->session()->read('Auth.User.group_id') == KIOSK_USERS){
					if($selling_price < 0){
						$this->Flash->error('Selling price cannot be less then less then zero!');
						return $this->redirect(array('action' => 'edit',$id));
					die;
					}
				}else{
					if($selling_price<$lowestSellingPrice){
						$this->Flash->error('Selling price cannot be less then lowest selling price!');
						return $this->redirect(array('action' => 'edit',$id));
					die;
					}
				}
			}
			if($this->request->session()->read('Auth.User.group_id') == KIOSK_USERS){
				if($discounted_price < $lowestSellingPrice){
						$this->Flash->error('Selling price cannot be less then lowest selling price!');
						return $this->redirect(array('action' => 'edit',$id));
					die;
				}
			}
           // pr($this->request['data']);
			if($hidden_selling_price != $selling_price || $hidden_discounted_price != $discounted_price){
                
				if($this->request->session()->read('Auth.User.group_id') == ADMINISTRATORS || $this->request->session()->read('Auth.User.group_id') == FRANCHISE_OWNER ||
			   $this->request->session()->read('Auth.User.group_id') == MANAGERS ||
			   $this->request->session()->read('Auth.User.group_id') == KIOSK_USERS){//kiosk users added on 16may 2016 on client's request
					$rsleData = $this->request->data;
					$rsleData['selling_price'] = $hidden_selling_price;
					$rsleData['discounted_price'] = $hidden_discounted_price;
					$rsleData['discount'] = $hidden_discount;
					$iemi = $this->request['data']['imei'].$this->request['data']['imei1'];
					$rsleData['imei']=$iemi;
                 //   pr($rsleData);die;
                    $id_get = $this->MobileBlkReSales->get($rsleData['id']);
                    $entity_patch = $this->MobileBlkReSales->patchEntity($id_get,$rsleData);
					if($this->MobileBlkReSales->save($entity_patch)){
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
                        $idGet = $this->MobileTransferLogs->newEntity();
						$entinty_p = $this->MobileTransferLogs->patchEntity($idGet,$mobileTransferLogData);
						$this->MobileTransferLogs->save($entinty_p);
						$paymentData_query = $this->MobileBlkReSalePayments->find('all',array(
							'conditions' => array('MobileBlkReSalePayments.mobile_blk_re_sale_id'=>$id),
								)
							  );
                        $paymentData_query = $paymentData_query->hydrate(false);
                        if(!empty($paymentData_query)){
                            $paymentData = $paymentData_query->toArray();
                        }else{
                            $paymentData = array();
                        }
						//pr($this->request);die;
						$this->set(compact('paymentData','users','kiosks'));
						$this->render('admin_resale_payment');
						goto fakeblock;
					}
				}
			}
			$iemi = $this->request['data']['imei'].$this->request['data']['imei1'];
			$this->request->data['imei']=$iemi;
            $Id_get = $this->MobileBlkReSales->get($this->request->data['id']);
            $Patch_entity = $this->MobileBlkReSales->patchEntity($Id_get,$this->request->data);
			if ($this->MobileBlkReSales->save($Patch_entity)) {
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
                    $id_G = $this->MobileTransferLogs->newEntity();
					$patch_En = $this->MobileTransferLogs->patchEntity($id_G,$mobileTransferLogData);
					$this->MobileTransferLogs->save($patch_En);
				$this->Flash->success(__('The mobile re sale has been saved.'));
				return $this->redirect(array('action' => 'index'));
			} else {
				$this->Flash->error(__('The mobile re sale could not be saved. Please, try again.'));
			}
		} else {
            
            $options = array('conditions' => array('MobileBlkReSales.id' => $id));
			$MobileReSale_query = $this->MobileBlkReSales->find('all', $options);
            $MobileReSale_query = $MobileReSale_query->hydrate(false);
            if(!empty($MobileReSale_query)){
                $MobileReSale = $MobileReSale_query->first();
            }else{
                $MobileReSale = array();
            }
            $this->request->data = $MobileReSale;
		}
		$lockedUnlocked = array('0'=>'Unlocked','1'=>'Locked');
        
        $brands_query = $this->MobileBlkReSales->Brands->find('list',[
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
            $mobilePrices_query = $this->MobilePurchases->find('all',array('conditions'=>array('imei' => $imei1),'order' => 'id desc'));
            $mobilePrices_query = $mobilePrices_query->hydrate(false);
            if(!empty($mobilePrices_query)){
                $mobilePrices = $mobilePrices_query->first();
            }else{
                $mobilePrices = array();
            }
           
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
			return $this->redirect(array('controller'=>'mobile_blk_re_sales','action'=>'index'));
		}
		
		$mobileCostPrice = $mobileSalePrice = $maximum_discount = "";
		//pr($mobilePrices);die;
		if(!empty($mobilePrices)){
			$mobileCostPrice = $mobilePrices['cost_price'];
			$mobileSalePrice = $mobilePrices['selling_price'];
			$staticSP = 0;
			if($customGrade == 1){
				$lowestSalePrice = $mobilePrices['lowest_selling_price'];
				$staticSP = $mobilePrices['static_selling_price'];
				$this->set(compact('lowestSalePrice', 'staticSP'));
			}else{
				$maximum_discount = $mobilePrices['maximum_discount'];
			}
		}
		$this->set(compact('kiosks', 'brands','mobileModels','maximum_discount'));
		fakeblock:
		;
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
		 //pr($colorOptions);die;
		$this->set(compact('resaleOptions','countryOptions','kiosk_id','gradeType','type','colorOptions','discountOptions','currency'));
		if (!$this->MobileBlkReSales->exists($id)) {
			throw new NotFoundException(__('Invalid mobile re sale'));
		}
		$options = [
					'conditions' => ['MobileBlkReSales.id'=> $id],
					'contain' => ['Brands','Kiosks']
					];
		//pr($options);
		//$mobileReSale = $this->MobileBlkReSale->find('first', $options);
		$mobileReSale_query = $this->MobileBlkReSales->find('all', $options);
//pr($mobileReSale_query);die;
		$mobileReSale_query = $mobileReSale_query->hydrate(false);
		$mobileReSale = $mobileReSale_query->first();
        
        
		$purchaseID = $mobileReSale['mobile_purchase_id'];
		$orgSellingPrice_query = $this->MobilePurchases->find('all', [
												'conditions' => ['MobilePurchases.id' => $purchaseID],
												//'fields' => array('static_selling_price', 'lowest_selling_price')
												'keyField' => 'static_selling_price',
												'valueField' => 'lowest_selling_price'
											]
										);
		$orgSellingPrice_query = $orgSellingPrice_query->hydrate(false);
		$orgSellingPrice = $orgSellingPrice_query->first();
		$orgSP = $orgSellingPrice['static_selling_price'];
		$lowestSP = $orgSellingPrice['lowest_selling_price'];
		$this->set('mobileReSale', $mobileReSale);
		$mobilePurchaseId = $mobileReSale['mobile_purchase_id'];
		/*$mobilePurchaseData = $this->MobilePurchases->find('first', array('conditions' => array('MobilePurchase.id' => $mobilePurchaseId), 'recursive' => -1));*/
		
		$mobilePurchaseData_query = $this->MobilePurchases->find('all', [
										'conditions' => [
													'MobilePurchases.id' => $mobilePurchaseId
													] 
												]
											);
		$mobilePurchaseData_query = $mobilePurchaseData_query->hydrate(false);
		$mobilePurchaseData = $mobilePurchaseData_query->first();
		
		//pr($mobilePurchaseData);die;
		/*$mobileModels = $this->MobileModels->find('list',array('fields' => array('id', 'model'),
								      ));*/
		$mobileModels_query = $this->MobileModels->find('list',[
													'keyField' => 'id',
													'valueField' => 'model'
												]
											);
		$mobileModels_query = $mobileModels_query->hydrate(false);
		$mobileModels = $mobileModels_query->toArray();
		
		//$networks = $this->Networks->find('list',array('fields'=>array('id','name')));
		
		$networks_query = $this->Networks->find('list',[
												'keyField' => 'id',
												'valueField' => 'name'
												]
											);
		$networks_query = $networks_query->hydrate(false);
		$networks = $networks_query->toArray();
		
		$networks[""] = "--";
		$this->set(compact('mobileModels','networks','mobilePurchaseData', 'orgSP','lowestSP'));
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
        
        $paymentData_query = $this->MobileBlkReSalePayments->find('all',array(
							'conditions' => array('MobileBlkReSalePayments.mobile_blk_re_sale_id'=>$resaleId,
												  )
                                        )
							  );
		$paymentData_query = $paymentData_query->hydrate(false);
        if(!empty($paymentData_query)){
            $paymentData = $paymentData_query->toArray();
        }else{
            $paymentData = array();
        }
        $saleData_query = $this->MobileBlkReSales->find('all', array('conditions' => array('MobileBlkReSales.id' => $resaleId, 'MobileBlkReSales.refund_status IS NULL')));
        $saleData_query = $saleData_query->hydrate(false);
        if(!empty($saleData_query)){
            $saleData = $saleData_query->first();
        }else{
            $saleData = array();
        }
		
		if((int)$saleData['discounted_price'] && $saleData['discounted_price'] > 0){
			$saleAmount = $saleData['discounted_price'];
		}else{
			$saleAmount = $saleData['selling_price'];
		}
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
				$this->Flash->error('Payment can only be updated within same day!');
				return $this->redirect(array('action' => 'index'));
				die;
			}
		}
		if ($this->request->is(array('post', 'put'))){
			if(array_key_exists('cancel',$this->request->data)){
					$this->Flash->error('You have cancelled transaction!');
					return $this->redirect(array('controller' => 'mobile_blk_re_sales','action' => 'index'));
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
                        
                        $paymntData_query = $this->MobileBlkReSalePayments->find('all',array(
																				'conditions' => array('MobileBlkReSalePayments.mobile_blk_re_sale_id'=>$resaleId)
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
						
                        $get_id = $this->MobileBlkReSalePayments->newEntity();
                        $patch_entity = $this->MobileBlkReSalePayments->patchEntity($get_id,$paymntData);
						if($this->MobileBlkReSalePayments->save($patch_entity)){
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
						 $getId = $this->MobileBlkReSalePayments->get($paymentId);
						$patchEntity = $this->MobileBlkReSalePayments->patchEntity($getId,$paymentDetailData);
						if($this->MobileBlkReSalePayments->save($patchEntity)){
							$saveAdminPayment++;
						}
					}
					if($saveAdminPayment > 0){
						$this->Flash->success('Payment has been successfully updated!');
						return $this->redirect(array('controller' => 'mobile_blk_re_sales','action' => 'index'));
					}else{
						$this->Flash->error('Payment could not be updated!');
						return $this->redirect(array('action' => 'update_resale_payment',$resaleId));
					}
				}
			}
			$this->set(compact('paymentData','paymentType','kiosks','users','saleAmount'));
	}
	
	public function add($mobilePurchaseId = "",$pmt_identifier ="") {
		$MobileBlkReSalesEntity = $this->MobileBlkReSales->newEntity();
		$this->set(compact('MobileBlkReSalesEntity'));
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
		
		$kiosks_query = $this->MobileBlkReSales->Kiosks->find('list');
		$kiosks_query = $kiosks_query->hydrate(false);
		if(!empty($kiosks_query)){
			$kiosks = $kiosks_query->toArray();
		}else{
			$kiosks = array();
		}
		$brands_query = $this->MobileBlkReSales->Brands->find('list', array(
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
													   ));
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
			$mobilePrices_query = $this->MobilePrice->find('all',array(
															   'conditions' => array(
																					 'MobilePrices.brand_id' => $mobilePurchaseBrandId,
																					 'MobilePrices.mobile_model_id' => $mobilePurchaseModelId,
																					 'MobilePrices.grade' => $mobilePurchaseGrade,
																					 'MobilePrices.locked' => $mobilePurchaseType
																					 ))
												 );
			$mobilePrices_query = $mobilePrices_query->hydrate(false);
			if(!empty($mobilePrices_query)){
				$mobilePrices_query = $mobilePrices_query->first();
			}else{
				$mobilePrices_query =  array();
			}
			//pr($mobilePrices);
		}
		
		if(empty($mobilePrices)){
			$this->Flash->error("No pricing details found for this combination, please enter cost-price and selling-price for this combination. Brand:$brands[$mobilePurchaseBrandId], Model:$mobileModels[$mobilePurchaseModelId], Grade:$mobilePurchaseGrade & Type:$lockedUnlocked[$mobilePurchaseType]");
			return $this->redirect(array('controller'=>'mobile_purchases','action'=>'index'));
		}
		
		if(!empty($mobilePrices)){
			$mobileCostPrice = $mobilePrices['MobilePrice']['cost_price'];
			
			//change on 25.03.2016 we are showing top up price at the time of selling if exists in mobile purchase
			if(!empty($mobilePurchaseData['MobilePurchase']['topedup_price']) && is_numeric($mobilePurchaseData['MobilePurchase']['topedup_price'])){
				$mobileCostPrice = $mobilePurchaseData['MobilePurchase']['topedup_price'];
			}
			
			$mobileSalePrice = $mobilePrices['MobilePrice']['sale_price'];
			$maximum_discount = $mobilePrices['MobilePrice']['maximum_discount'];
		}
		
		$this->set(compact('mobilePurchaseData'));
		$this->set(compact('kiosks', 'brands','mobileModels','mobileCostPrice','mobileSalePrice','maximum_discount','networks','terms_resale'));
		
		if ($this->request->is('post') || $this->request->Session()->read('resale_payment_confirmation.resale_payment_status') == $mobilePurchaseId) {
			//pr($this->request);die;
			if($this->request->is('post')){
				$cust_data = $this->request->data['MobileBlkReSale'];
				$this->loadModel('RetailCustomers');
				if(!empty($cust_data['customer_email'])){
					$countDuplicate_query = $this->RetailCustomers->find('all',
																	 array('conditions' => array('RetailCustomers.email' => $cust_data['customer_email'])));
				
					$countDuplicate_query = $countDuplicate_query->hydrate(false);
					if(!empty($countDuplicate_query)){
						$countDuplicate = $countDuplicate_query->first();
					}else{
						$countDuplicate = array();
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
					}else{
							$custmor_id =  $countDuplicate["id"];
							$RetailCustomersEntity = $this->RetailCustomers->get($custmor_id);
							$RetailCustomersEntity = $this->RetailCustomers->patchEntity($RetailCustomersEntity,$customer_data,['validate' => false]);
							
							if($this->RetailCustomers->save($RetailCustomersEntity)){
								
							}else{
								
							}
					}
				}
				
			}
			//deleting session id payment_confirmation as we no longer need it after entering this loop
			$this->request->Session()->delete('resale_payment_confirmation');
			
			if(count($this->request->data)){
				$costPrice = $this->request['data']['MobileBlkReSale']['cost_price'];
				$sellingPrice = $this->request['data']['MobileBlkReSale']['selling_price'];
				if($costPrice>$sellingPrice){
					$this->Flash->error("Cost price cannot exceed the selling price");
					return;
				}
			}
			
			if(count($this->request->data)){
				//change on 25.03.2016 AuthComponent::user('group_id') == KIOSK_USERS &&
				//need to check if admin can also sell the mobile
				//pr($this->request->data);die;
				$this->request->data['MobileBlkReSale']['prchseId'] = $mobilePurchaseId;//for redirection of session, using in resale_payment
				$this->request->Session()->write('resale_data_session',$this->request->data);
				return $this->redirect(array('action' => 'resale_payment'));
				die;
			}elseif($this->request->Session()->read('resale_data_session')){
				$this->request->data = $this->request->Session()->read('resale_data_session');
			}
			
			//deleting the session, as it is no longer required
			$this->request->Session()->delete('resale_data_session');
			
			$iemi = $this->request['data']['MobileBlkReSale']['imei'].$this->request['data']['MobileBlkReSale']['imei1'];
			if($this->request->session()->read('Auth.User.group_id') == ADMINISTRATORS || $this->request->session()->read('Auth.User.group_id') == FRANCHISE_OWNER){
				$this->request->data['MobileBlkReSale']['kiosk_id']=0;
			}
			
			if(!array_key_exists('retail_customer_id',$this->request['data']['MobileBlkReSale'])){
				//if by chance we do not get retail_customer_id throug the front end
				$customerData_query = $this->RetailCustomers->find('all', array(
								'conditions' => array(
											 'OR' => array('RetailCustomers.mobile' => $this->request['data']['MobileBlkReSale']['customer_contact'],
														   'LOWER(RetailCustomers.email)' => $this->request['data']['MobileBlkReSale']['customer_email']) 
													  ),
								 'order' => 'RetailCustomers.id desc'
							 )
						 );
				$customerData_query = $customerData_query->hydrate(false);
				if(!empty($customerData_query)){
					$customerData = $customerData_query->first();
				}else{
					$customerData = array();
				}
				if(count($customerData)){
					$this->request->data['MobileBlkReSale']['retail_customer_id'] = $customerData['id'];
				}
			}
			$this->request->data['MobileBlkReSale']['mobile_purchase_id']=$mobilePurchaseId;
			$this->request->data['MobileBlkReSale']['imei']=$iemi;
			
			$this->request->data['MobileBlkReSale']['user_id'] = $this->request->session()->read('Auth.User.id');
			$identifier = $this->request->params['pass'][1];
			$customGrade = $this->request->data['MobileBlkReSale']['custom_grade'];
			if($customGrade == 1){
				$this->MobileBlkReSale->create();
				if($this->MobileBlkReSale->save($this->request->data['MobileBlkReSale'])){
					$mobileResaleId = $this->MobileBlkReSale->id;
					$this->MobilePurchase->id = $mobilePurchaseId;
					$this->MobilePurchase->saveField('status',1);
					$query = "UPDATE `mobile_blk_re_sale_payments` SET `mobile_blk_re_sale_id` = '$mobileResaleId' WHERE `mobile_purchase_id` = $mobilePurchaseId AND `pmt_identifier` = '$identifier'";
					//$this->MobileBlkReSalePayment->updateAll(array('mobile_blk_re_sale_id' => "'$mobileResaleId'"),array('MobileBlkReSalePayment.mobile_purchase_id' => $mobilePurchaseId,'pmt_identifier' =>$identifier));
					$this->MobileBlkReSalePayment->query($query);
					if(true){
						$paymentDetails = $this->MobileBlkReSalePayment->find('all', array(
													'conditions' => array('MobileBlkReSalePayment.mobile_blk_re_sale_id' => $mobileResaleId),
																));
						if(empty($paymentDetails)){
							//delete sale and redirect to index page
							$this->MobileBlkReSalePayment->id = $mobileResaleId;
							$this->MobileBlkReSalePayment->delete();
							$alterQuery = "ALTER TABLE `mobile_blk_re_sale_payments` AUTO_INCREMENT = $mobileResaleId";
							$this->MobileBlkReSalePayment->query($alterQuery);
							$this->Session->setFlash("Failed to fire query: $query. <br/>For this reason generated sale delelted for Mobile Bulk Resale ID: {$mobileResaleId} and receipt counter is again set to $mobileResaleId for maintaining sequences");
							return $this->redirect(array('action' => 'index'));
						}
						$kiosk_id = $this->Session->read('kiosk_id');
						$kioskaddress = $this->Kiosk->find('first',array(
									'fields' => array('Kiosk.name','Kiosk.address_1','Kiosk.address_2', 'Kiosk.city','Kiosk.state','Kiosk.country','Kiosk.zip' ),
									'conditions'=> array('Kiosk.id' => $kiosk_id),
									'recursive' => -1
									));
						
						$mobileresaledata = $this->request->data['MobileBlkReSale'];
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
								'kiosk_id' => $this->request->data['MobileBlkReSale']['kiosk_id'],
								'mobile_resale_id' => $mobileResaleId,
								'imei' => $this->request->data['MobileBlkReSale']['imei'],
								'status' => 1,
								'network_id' => $this->request->data['MobileBlkReSale']['network_id'],
								'grade' => $this->request->data['MobileBlkReSale']['grade'],
								'type' => $this->request->data['MobileBlkReSale']['type']
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
					}else{
						$dbo = $this->MobileBlkReSalePayment->getDatasource();
						$logData = $dbo->getLog();
						$getLog = end($logData['log']);
						$qryLog = $getLog['query'];
						$this->Session->setFlash(__("The mobile re sale could not be saved. Please, try again.: $qryLog"));
					}
				} else {
					$this->Session->setFlash(__('The mobile re sale could not be saved. Please, try again.'));
				}
			}else{
				$this->MobileBlkReSale->create();
				if ($this->MobileBlkReSale->save($this->request->data)) {
					$mobileResaleId = $this->MobileBlkReSale->id;
					$this->MobilePurchase->id = $mobilePurchaseId;
					$this->MobilePurchase->saveField('status',1);
					//$this->MobileBlkReSalePayment->updateAll(array('mobile_re_sale_id' => "'$mobileResaleId'"),array('MobileBlkReSalePayment.mobile_purchase_id' => $mobilePurchaseId));
					$query = "UPDATE `mobile_blk_re_sale_payments` SET `mobile_blk_re_sale_id` = '$mobileResaleId' WHERE `mobile_purchase_id` = $mobilePurchaseId AND `pmt_identifier` = '$identifier'";
					//$this->MobileBlkReSalePayment->updateAll(array('mobile_blk_re_sale_id' => "'$mobileResaleId'"),array('MobileBlkReSalePayment.mobile_purchase_id' => $mobilePurchaseId,'pmt_identifier' =>$identifier));
					/*$this->MobileBlkReSalePayment->query($query);
					$dbo = $this->MobileBlkReSalePayment->getDatasource();
					$logData = $dbo->getLog();
					$getLog = end($logData['log']);
					echo $getLog['query'];*/
					$this->MobileBlkReSalePayment->query($query);
					if(true){
						$paymentDetails = $this->MobileBlkReSalePayment->find('all', array(
													'conditions' => array('MobileBlkReSalePayment.mobile_blk_re_sale_id' => $mobileResaleId),
																));
						if(empty($paymentDetails)){
							//delete sale and redirect to index page
							$this->MobileBlkReSalePayment->id = $mobileResaleId;
							$this->MobileBlkReSalePayment->delete();
							$alterQuery = "ALTER TABLE `mobile_blk_re_sale_payments` AUTO_INCREMENT = $mobileResaleId";
							$this->MobileBlkReSalePayment->query($alterQuery);
							$this->Session->setFlash("Failed to fire query: $query. <br/>For this reason generated sale delelted for Mobile Bulk Resale ID: {$mobileResaleId} and receipt counter is again set to $mobileResaleId for maintaining sequences");
							return $this->redirect(array('action' => 'index'));
						}
						$kiosk_id = $this->Session->read('kiosk_id');
						$kioskaddress = $this->Kiosk->find('first',array(
									'fields' => array('Kiosk.name','Kiosk.address_1','Kiosk.address_2', 'Kiosk.city','Kiosk.state','Kiosk.country','Kiosk.zip' ),
									'conditions'=> array('Kiosk.id' => $kiosk_id),
									'recursive' => -1
									));
						
						$mobileresaledata = $this->request->data['MobileBlkReSale'];
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
								'kiosk_id' => $this->request->data['MobileBlkReSale']['kiosk_id'],
								'mobile_resale_id' => $mobileResaleId,
								'imei' => $this->request->data['MobileBlkReSale']['imei'],
								'status' => 1,
								'network_id' => $this->request->data['MobileBlkReSale']['network_id'],
								'grade' => $this->request->data['MobileBlkReSale']['grade'],
								'type' => $this->request->data['MobileBlkReSale']['type']
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
					}else{
						$dbo = $this->MobileBlkReSalePayment->getDatasource();
						$logData = $dbo->getLog();
						$getLog = end($logData['log']);
						$logQry = $getLog['query'];
						$this->Session->setFlash("The mobile re sale could not be saved. Please, try again:$logQry");
						return $this->redirect(array('action' => 'index'));
					}
				}else{
					$this->Session->setFlash(__('The mobile re sale could not be saved. Please, try again.'));
					return $this->redirect(array('action' => 'index'));
				}
			}
		}
	}
	
	public function resalePayment(){
		$setting = $this->setting;
		$this->set(compact('setting'));
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
		$paymentType = array('Cash' => 'Cash', 'Card' => 'Card');
		$this->set(compact('paymentType'));
		
		if(is_array($this->request->Session()->read('resale_data_session'))){
			$basket = "resale_data_session";
			$session_basket = $this->request->Session()->read('resale_data_session');
			$sessionResaleId = $session_basket['MobileBlkReSale']['prchseId'];
		}else{
			return $this->redirect(array('action' => 'index'));
			die;
		}
		
		if ($this->request->is(array('post', 'put'))) {
			//pr($_SESSION);
			//pr($this->request);die;
			if(array_key_exists('cancel',$this->request->data)){
				$this->request->Session()->delete($basket);
				return $this->redirect(array('controller'=>'mobile_blk_re_sales','action'=>'add',$sessionResaleId));
				die;
			}
			$amountToPay = $this->request['data']['final_amount'];
			$totalPaymentAmount = 0;
			$amountDesc = array();
			$error = '';
			$errorStr = '';
			$customGrade = '';
			$session_basket = $this->request->Session()->read('resale_data_session');
			$customGrade = $session_basket['MobileBlkReSale']['custom_grade'];
			
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
			$pmt_identifier = time();
			foreach($this->request['data']['Payment']['Amount'] as $key => $paymentAmount){
				$paymentMethod = $this->request['data']['Payment']['Payment_Method'][$key];
				$paymentDescription = $this->request['data']['Payment']['Description'][$key];
				$payment_status = 1;//since we do not have option for credit here, so just sending status 1 as payment done
				
				//added on 25.03.2016
				$kskId = $this->Session->read('kiosk_id');
				if(empty($kskId)){
					$kskId = 10000;
				}
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
						$this->MobileBlkReSalePayment->clear;
						$this->MobileBlkReSalePayment->create();
						$sessionBskt = array();
						if($this->MobileBlkReSalePayment->save($resalePaymentData)){
							$counter++;
							$sessionBskt['resale_payment_status'] = $sessionResaleId;
							//here we are sending payment status in session to unlock edit as an identifier for successful payment
							$this->Session->write('resale_payment_confirmation',$sessionBskt);
						}
					}
					
				}
			}
			if($counter>0){
				return $this->redirect(array('controller'=>'mobile_blk_re_sales','action'=>'add',$sessionResaleId,$pmt_identifier));;
			}else{
				$flashMessage = ("Sale could not be created. Please try again");
				$this->Session->delete($basket);
				$this->Session->setFlash($flashMessage);
				return $this->redirect(array('controller'=>'mobile_blk_re_sales','action'=>'add',$sessionResaleId));
			}
		}
	}
	
	public function mobileSaleReceipt($id = ""){//$id = mobile purchase id, using it for showing original and return details, as it remains unique for both
		$kiosk_query = $this->Kiosks->find('list',array(
														'keyField' => 'id',
														'valueField' => 'name'
														));
		$kiosk_query = $kiosk_query->hydrate(false);
		if(!empty($kiosk_query)){
			$kiosk = $kiosk_query->toArray();
		}else{
			$kiosk = array();
		}
		$settingArr = $this->setting;
		$mobileResaleData_query = $this->MobileBlkReSales->find('all',array('conditions'=>array('MobileBlkReSales.id'=>$id,'MobileBlkReSales.refund_status IS NULL')));
		
		$mobileResaleData_query = $mobileResaleData_query->hydrate(false);
		if(!empty($mobileResaleData_query)){
			$mobileResaleData = $mobileResaleData_query->first();
		}else{
			$mobileResaleData = array();
		}
		
		$mobileReturnData_query = $this->MobileBlkReSales->find('all',array('conditions'=>array('MobileBlkReSales.sale_id'=>$id,'MobileBlkReSales.refund_status'=>1)));
		
		$mobileReturnData_query = $mobileReturnData_query->hydrate(false);
		if(!empty($mobileReturnData_query)){
			$mobileReturnData = $mobileReturnData_query->first();
		}else{
			$mobileReturnData = array();
		}
		
		$brandId = $mobileResaleData['brand_id'];
		$mobileModelId = $mobileResaleData['mobile_model_id'];
		$kiosk_id = $mobileResaleData['kiosk_id'];
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
		
		$MobileBlkReSalePayments = $this->MobileBlkReSalePayments->find("all",['conditions' => ['mobile_blk_re_sale_id' => $id,
																						   //'kiosk_id' => $kiosk_id1,
																						   ]
																		 ])->toArray();
		
		$str = "";
		if(!empty($MobileBlkReSalePayments)){
		  foreach($MobileBlkReSalePayments as $key => $value){
			   $amount = $value->amount;
			   $payment_method = $value->payment_method;
			   $str .= $payment_method." : ".$amount." ";
		  }
		}
		
		
		$users_list = $this->Users->find("list",['keyField' => 'id',
								  'valueField' => 'username',
								  ])->toArray();
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
		$send_by_email = Configure::read('send_by_email');
		$emailSender = Configure::read('EMAIL_SENDER');
		if($this->request->is('post')){
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
						//$Email->sender("sales@oceanstead.co.uk");
						$Email->subject('Mobile Purchase Receipt');
					if($Email->send()){
						$this->Flash->success("Email has been successfully sent");
					}
		}
		$this->set(compact('settingArr','str','mobileResaleData','brandName','modelName','mobileReturnData','kiosk', 'kioskDetails','users_list'));
	}
	
	public function mobileRefund($resaleId = ""){
		$currency = Configure::read('CURRENCY_TYPE');
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
			///pr($this->request);die;
			// pr($this->request['data']);
			$terms_resale = $this->setting['phone_resale_email_message'];
			$sale_price = $this->request['data']['sale_price'];//price that is displayed on view screen among discount and selling price
			$mobileRefundData = $this->request->data;
			
			$refundBy = $mobileRefundData['refund_by'];
			$refundStatus = $mobileRefundData['refund_status'];
			if($this->request->session()->read('Auth.User.group_id') == ADMINISTRATORS || $this->request->session()->read('Auth.User.group_id') == FRANCHISE_OWNER){
				$kiosk_id = 10000;
			}else{
				$kiosk_id = $mobileRefundData['kiosk_id'];
			}
			
			$refundRemarks = $mobileRefundData['refund_remarks'];
			$refundPrice = $mobileRefundData['refund_price'];
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
				$mobileResaleData_query = $this->MobileBlkReSales->find('all',array('conditions'=>array('MobileBlkReSales.id'=>$resaleId)));
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
				$MobileBlkReSalesEntity = $this->MobileBlkReSales->newEntity($mobileResaleData,['valiadte' => false]);
				$MobileBlkReSalesEntity = $this->MobileBlkReSales->patchEntity($MobileBlkReSalesEntity,$mobileResaleData,['valiadte' => false]);
				//pr($MobileBlkReSalesEntity);die;
                if($this->MobileBlkReSales->save($MobileBlkReSalesEntity)){
                   // echo "kk";die;
					unset($mobileResaleData['MobileBlkReSale']['selling_date']);
					$MobileBlkReSalesEntity1 = $this->MobileBlkReSales->get($MobileBlkReSalesEntity->id);
					$data_to_save = array('sale_id' => $resaleId);
					$MobileBlkReSalesEntity1 = $this->MobileBlkReSales->patchEntity($MobileBlkReSalesEntity1,$data_to_save,['validate' => false]);
					$this->MobileBlkReSales->save($MobileBlkReSalesEntity1);//to identify the id that has been refunded
					
					//-------------------
					$MobileBlkReSalesEntity2 = $this->MobileBlkReSales->get($resaleId);
					$data_to_save2 = array(
										   'status'=>1,
										   'refund_price'=>$refundPrice,
										   'refund_gain'=>$refundGain,
										   'refund_by'=>$refundBy,
										   'refund_date'=>$refundDateTime,
										   );
					$MobileBlkReSalesEntity2 = $this->MobileBlkReSales->patchEntity($MobileBlkReSalesEntity2,$data_to_save2,['validate' => false]);
					$this->MobileBlkReSales->save($MobileBlkReSalesEntity2);
					
					//changing the status of mobile in mobile purchases table to available:0
					$MobilePurchasesEntity = $this->MobilePurchases->get($mobileResaleData['mobile_purchase_id']);
					$data_to_save3 = array('status' => 0);
					$MobilePurchasesEntity = $this->MobilePurchases->patchEntity($MobilePurchasesEntity,$data_to_save3,['validate' => false]);
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
						echo $emailStatement = "Hi ".$mobileResaleData['customer_fname']."".$mobileResaleData['customer_lname'].",<br/><br/>As requested, refund has been processed for an amount of ".$currency.$refundPrice."	for your mobile(imei:".$mobileResaleData['imei'].",\tMobile Model: ".$model.") 	.<br/><br/>Thank you for shopping with us.<br/><br/>Regards,<br/>" .$kioskName[$kiosk_id]. $kioskaddress1.$kioskaddress2.$kioskcity.$kioskstate.$kioskcountry.	$kioskzip.$kioskcontact."<br/><br/>".$terms_resale;
			
						
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
						//$Email->sender("sales@oceanstead.co.uk");
						$Email->subject('Refund Processed');
						$Email->send();
					}
					
					$this->Flash->error("Refund request has been submitted");
					return $this->redirect(array('action'=>'index'));
				}else{
					pr($MobileBlkReSalesEntity);die;
					$this->Flash->error("Refund request could not be processed, please try again!");
					return $this->redirect(array('action'=>'index'));
				}
			}
		}
		$res_query = $this->MobileBlkReSales->find('all',array('conditions'=>array('MobileBlkReSales.id'=>$resaleId)));
		$res_query = $res_query->hydrate(false);
		if(!empty($res_query)){
			$res = $res_query->first();
		}else{
			$res = array();
		}
		$this->request->data =$res;
		
		$userId = $this->request->data['user_id'];
		$brandId = $this->request->data['brand_id'];
		$mobileModelId = $this->request->data['mobile_model_id'];
		$networkId = $this->request->data['network_id'];
		if(empty($userId)){
			$userId = array(0 => null);
		}
		$userName_query = $this->Users->find('list',array('conditions'=>array('Users.id IN'=>$userId),
														  'keyField' => 'id',
														   'valueField' => 'username',
														  ));
		
		$userName_query = $userName_query->hydrate(false);
		if(!empty($userName_query)){
			$userName = $userName_query->toArray();
		}else{
			$userName = array();
		}
		if(empty($brandId)){
			$brandId = array(0 => null);
		}
		
		$brandName_query = $this->Brands->find('list',array('conditions'=>array('Brands.id IN'=>$brandId),
															'keyField' => 'id',
															'valueField' => 'brand',
															));
		
		$brandName_query = $brandName_query->hydrate(false);
		if(!empty($brandName_query)){
			$brandName = $brandName_query->toArray();
		}else{
			$brandName = array();
		}
		
		if(empty($mobileModelId)){
			$mobileModelId = array(0 => null);
		}
		$modelName_query = $this->MobileModels->find('list',array('conditions'=>array('MobileModels.id IN'=>$mobileModelId),
																 'keyField' => 'id',
																 'valueField' => 'model',
																 ));
		
		$modelName_query = $modelName_query->hydrate(false);
		if(!empty($modelName_query)){
			$modelName = $modelName_query->toArray();
		}else{
			$modelName = array();
		}
		if(empty($networkId)){
			$networkId = array(0 => null);
		}
		$userName[0] = "Admin";
		$networkName_query = $this->Networks->find('list',array('conditions'=>array('Networks.id IN'=>$networkId),
															   'keyField' => 'id',
															   'valueField' => 'name',
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
			$conditionArr['OR']['MobileBlkReSales.imei like'] =  strtolower("%$searchKW%");
			//$conditionArr['OR']['Brands.brand like'] = strtolower("%$searchKW%");
			$conditionArr['OR']['MobileBlkReSales.customer_email like'] = strtolower("%$searchKW%");
			//$conditionArr['OR']['MobileModels.model like'] = strtolower("%$searchKW%");
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
			"MobileBlkReSales.created >" => date('Y-m-d', strtotime($this->request->query['start_date'])),
			"MobileBlkReSales.created <" => date('Y-m-d', strtotime($this->request->query['end_date']. ' +1 Days')),   
			);
		}
		//echo "admin";
		$kioskid = $this->request->Session()->read('kiosk_id');
		if($kioskid == ""){
            //pr($this->request->query);die;
			if(array_key_exists('MobileBlkReSale',$this->request->query)){
				$kioskId = $this->request->query['MobileBlkReSale']['kiosk_id'];
				$this->set('kioskId', $kioskId);
				if(array_key_exists('kiosk_id',$this->request->query['MobileBlkReSale']) && !empty($this->request->query['MobileBlkReSale']['kiosk_id'])){
					//echo "admin1";
					if($kioskId == "10000"){
						$kioskId = "0";
						$conditionArr[] =  array('kiosk_id' => $kioskId);
					}else{
					 $conditionArr[] =  array('kiosk_id' => $this->request->query['MobileBlkReSale']['kiosk_id']);
					}
				}
			}else{
				$kioskId = "";
			}
			//$this->set('kioskId', $kioskId);
		}
		if($kioskid>0){
			//echo "kiosk";
			//$conditionArr[] = array('MobileResale.kiosk_id' => "$kioskid",);
			$conditionArr[] = array('kiosk_id' =>$kioskid);
		}
		if(count($conditionArr)>=1){
			$count_query = $this->MobileReSales->find('all');
			$count = $count_query->count();
			//$this->Paginator->settings =array(
			//				'conditions' => $conditionArr,
			//				'limit' => $count
			//	);
			//$mobileReSales = $this->Paginator->paginate('MobileBlkReSale');
			$mobileReSales_query = $this->MobileBlkReSales->find('all',array('conditions' => $conditionArr,'limit' => $count));
		}else{
			$mobileReSales_query = $this->MobileBlkReSales->find('all');
		}
		$mobileReSales_query = $mobileReSales_query->hydrate(false);
		if(!empty($mobileReSales_query)){
			$mobileReSales = $mobileReSales_query->toArray();
		}else{
			$mobileReSales = array();
		}
		       $tmpMobileReSales = array();
			 //  pr($mobileReSales);die;
		foreach($mobileReSales as $key => $mobileReSale){
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
		$this->outputCsv('MobileBlkReSale_'.time().".csv" ,$tmpMobileReSales);
		$this->autoRender = false;
	}
    
    public function research($start = '',$last = '',$kiosk = ''){
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
			"MobileBlkReSales.created >" => date('Y-m-d', strtotime($this->request->params['pass'][0])),
			"MobileBlkReSales.created <" => date('Y-m-d', strtotime($this->request->params['pass'][1]. ' +1 Days')),   
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
			$kiosk_list = $this->Kiosks->find('list',[
										'keyField' => 'id',
										'valueField' => 'id',
										])->toArray();
			$conditionArr['kiosk_id IN'] = $kiosk_list;
		}
		//--------------------------------------
		$vat = $this->setting['vat'];
		$currency = $this->setting['currency_symbol'];
        
        $mobileSale_query = $this->MobileBlkReSales->find('all', array( 
                                    'conditions' => array($conditionArr),
                                    'contain' => array('Brands' , 'MobileModels')
                                        ));
        $mobileSale_query 
               ->select(['MobileBlkReSale'=>'CASE WHEN MobileBlkReSales.discounted_price is NULL THEN MobileBlkReSales.selling_price ELSE MobileBlkReSales.discounted_price END'])
               ->select('MobileBlkReSales.id')
               ->select('MobileBlkReSales.discounted_price')
               ->select( 'MobileBlkReSales.selling_price');
        $mobileSale_query = $mobileSale_query->hydrate(false);
        if(!empty($mobileSale_query)){
            $mobileSale = $mobileSale_query->toArray();
        }else{
            $mobileSale = array();
        }
       
        //pr($mobileSale);die;
		$saleIds = array();
		foreach($mobileSale as $ms => $sale){
            //pr($sale);die;
			$saleIds[$sale['id']] = $sale['id'];
		}
		$this->paginate = [
                            'conditions' => [ //'MobileBlkReSale.refund_status' => null,
                                                $conditionArr
                                            ],
								'order' => ['MobileBlkReSales.id desc'],
								'limit' => 200,
                          ];
		$mobileCostData1_query = $this->MobileBlkReSales->find('all', array(
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
        $mobileCostData_query = $this->MobilePurchases->find('all',[
                                                                    'conditions' =>['MobilePurchases.id IN'=>$purchaseIds],
                                                                    ]
                                                            );
        $mobileCostData_query
                ->select(['total_mobile_cost' => $mobileCostData_query->func()->sum('topedup_price')]);
        $mobileCostData_query = $mobileCostData_query->hydrate(false);
        if(!empty($mobileCostData_query)){
            $mobileCostData = $mobileCostData_query->first();
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
		$mobileResalePayment_query = $this->MobileBlkReSalePayments->find('all', array('conditions' => array('MobileBlkReSalePayments.mobile_blk_re_sale_id IN' => $saleIds)));
        $mobileResalePayment_query = $mobileResalePayment_query->hydrate(false);
        if(!empty($mobileResalePayment_query)){
            $mobileResalePayment = $mobileResalePayment_query->toArray();
        }else{
            $mobileResalePayment = array();
        }
		if(count($mobileResalePayment)){
			foreach($mobileResalePayment as $rp => $resalePayment){
				$mobilePurchaseID = $resalePayment['mobile_purchase_id'];
				$mobileReSaleID = $resalePayment['mobile_blk_re_sale_id'];
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
		$mobileReSales = $this->paginate('MobileBlkReSales');
		$brands_query = $this->MobileBlkReSales->Brands->find('list',[
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
	
		function exportCost(){
			$conditionArr = array();
			$startDate = $endDate = "";
			if(array_key_exists('start_date',$this->request->query)){$startDate = $this->request->query['start_date'];}
			if(array_key_exists('end_date',$this->request->query)){$endDate = $this->request->query['end_date'];}
			
			if(!empty($startDate) && !empty($endDate)){
				$conditionArr[] = array(
											"MobileBlkReSales.created >" => date('Y-m-d', strtotime($startDate)),
											"MobileBlkReSales.created <" => date('Y-m-d', strtotime($endDate.' +1 Days')),   
										);
			}
			
			if(array_key_exists('kioskId',$this->request->query)){
				$kioskId = $this->request->query['kioskId'];
				$conditionArr[] = array('kiosk_id' => $kioskId);
			}
			
			$mobileBlkReSales_query = $this->MobileBlkReSales->find('all', array(
																	'conditions' => $conditionArr,
																	'order' => 'MobileBlkReSales.id desc')
													   );
			$mobileBlkReSales_query = $mobileBlkReSales_query->hydrate(false);
			if(!empty($mobileBlkReSales_query)){
				$mobileBlkReSales = $mobileBlkReSales_query->toArray();
			}else{
				$mobileBlkReSales = array();
			}
			
			$kiosks_query = $this->Kiosks->find('list', array(
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
			
			$brands_query = $this->MobileBlkReSales->Brands->find('list', array(
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
			// pr($mobileBlkReSales);die;
			foreach ($mobileBlkReSales as $key => $mobileBlkReSale){
				if($mobileBlkReSale['discounted_price'] > 0){
					$price = $mobileBlkReSale['discounted_price'];
				}else{
					$price = $mobileBlkReSale['selling_price'];
				}
				if((int)$mobileBlkReSale['sale_id']){
					$csvRows[$key]['Rcpt'] = $mobileBlkReSale['sale_id'];
				}else{
					$csvRows[$key]['Rcpt'] = $mobileBlkReSale['id'];
				}
				$csvRows[$key]['Brand'] = $brands[$mobileBlkReSale['brand_id']];
				if(array_key_exists($mobileBlkReSale['mobile_model_id'],$modelName)){
					$csvRows[$key]['Model'] = $modelName[$mobileBlkReSale['mobile_model_id']];
				}
				$csvRows[$key]['IMEI'] = $mobileBlkReSale['imei'];
				if($mobileBlkReSale['refund_status'] == 1){
					$csvRows[$key]['Selling Price'] = "";
					$csvRows[$key]['Cost Price'] = "-".$mobileBlkReSale['cost_price'];
					if(!empty($mobileBlkReSale['refund_price'])){
						$csvRows[$key]['Refund Price'] = $mobileBlkReSale['refund_price'];
					}else{
						$csvRows[$key]['Refund Price'] = "---";
					}
					
					if(!empty($mobileBlkReSale['refund_date'])){
						 $mobileBlkReSale['refund_date'] = date("d-m-y h:i a",strtotime($mobileBlkReSale['refund_date']));
						$csvRows[$key]['Refund Date'] = $mobileBlkReSale['refund_date'];
					}
					
				}else{
					$csvRows[$key]['Selling Price'] = $price;
					$csvRows[$key]['Cost Price'] = $mobileBlkReSale['cost_price'];
					$csvRows[$key]['Refund Price'] = "";
					$csvRows[$key]['Refund Date'] = "";
				}
				if(
				   $mobileBlkReSale['selling_date'] == "0000-00-00 00:00:00"||
				   empty($mobileBlkReSale['selling_date'])){
					$mobileBlkReSale['created'] = date("d-m-y h:i a",strtotime($mobileBlkReSale['created']));
					$csvRows[$key]['Sale Date'] = $mobileBlkReSale['created'];
				}else{
					$mobileBlkReSale['selling_date'] = date("d-m-y h:i a",strtotime($mobileBlkReSale['selling_date']));
					$csvRows[$key]['Sale Date'] = $mobileBlkReSale['selling_date'];
				}
				$csvRows[$key]['Customer'] = $mobileBlkReSale['customer_fname'];
			}
			//pr($csvRows);die;
			$this->outputCsv('MobileReSale_'.time().".csv" ,$csvRows);
			$this->autoRender = false;
		}
	
}
?>