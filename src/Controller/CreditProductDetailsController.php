<?php
namespace App\Controller;
use Cake\Core\Configure;
use Cake\Core\Configure\Engine\PhpConfig;
use Cake\Datasource\ConnectionManager;
use Cake\ORM\TableRegistry;
use Cake\Mailer\Email;
use Cake\Utility\Text;
use Cake\I18n;
use Cake\ORM\Behavior;
use App\Controller\AppController;

class CreditProductDetailsController  extends AppController
{
     public $helpers = [
         'Paginator' => ['templates' => 'paginatortemplates']
         ];
    public function initialize(){
        parent::initialize();
        $paymentType=Configure::read('payment_type');
        $this->set(compact('paymentType'));
        $this->loadComponent('ScreenHint');
        $this->loadModel('CreditPaymentDetails');
        $this->loadModel('Customers');
        $this->loadModel('PaymentDetails');
        $this->loadModel('Users');
		$this->loadModel('CreditReceipts');
        $this->loadModel('ProductPayments');
        $this->loadModel('InvoiceOrders');
		$this->loadModel('Categories');
		$this->loadModel('ProductReceipts');
		$this->loadModel('Agents');
		$CURRENCY_TYPE = Configure::read('CURRENCY_TYPE');
		$this->set(compact('CURRENCY_TYPE'));
		
   $discountArr = array();
    for($i = 0; $i <= 50; $i++){
	 if($i==0){
	    $discountArr[0] = "None";
	    continue;
	 }
            $discountArr[$i] = "$i %";
    }
	 for($i=0; $i<=50; $i++){
        $newDiscountArr[$i] = "$i %";
	 }
	  $this->set(compact('newDiscountArr'));
    }
    public function viewCreditNote($source='') {
	     $external_sites = Configure::read('external_sites');
		$path = dirname(__FILE__);
		$ext_site = 0;
		foreach($external_sites as $site_id => $site_name){
			  $isboloRam = strpos($path,$site_name);
			  if($isboloRam != false){
				  $ext_site = 1;
			  }
		}
	 
        $kiosk_id = $this->request->Session()->read('kiosk_id');
		if($source == 1){
			$CreditReceiptSource = "t_credit_receipts";
			$CreditProductDetailSource = "t_credit_product_details";
			$CreditPaymentDetailSource = "t_credit_payment_details";
			
			   if(!empty($kiosk_id)){
				$this->paginate = [
								   'conditions' => ['kiosk_id' => $kiosk_id],
								   	'order' => ['credit_receipt_id DESC'],
									'limit' => 50
								   ];
			   }else{
				   $this->paginate = [
										'conditions' => ['kiosk_id' => 0],
								       'order' => ['credit_receipt_id DESC'],
									   'limit' => 50
								     ];
			   }
				if($this->request->session()->read('Auth.User.group_id') == ADMINISTRATORS || $this->request->session()->read('Auth.User.group_id') == FRANCHISE_OWNER){
					//$session_id = $this->Session->id();
					$user_id = $this->request->Session()->read('Auth.User.id');
					$data_arr = array('kiosk_id' => 10000);
					$jsondata = json_encode($data_arr);
					$this->loadModel('UserSettings');
					
					$res_query = $this->UserSettings->find('all',array('conditions' => array(
																				 'user_id' => $user_id,
																				 'setting_name' => "dr_credit_search",
																				 //'user_session_key' => $session_id,
																				 )));
                    //pr($res_query);die;
					$res_query = $res_query->hydrate(false);
					if(!empty($res_query)){
						$res = $res_query->first();
					}else{
						$res = array();
					}
					if(count($res) >0){
                        //pr($res);die;
						$userSettingid =  $res['id'];
						$data_to_save = array(
											'id' => $userSettingid,
											'user_id' => $user_id,
											//'user_session_key' => $session_id,
											'setting_name' => "dr_credit_search",
											'data' => $jsondata
											);
						$entity = $this->UserSettings->get($userSettingid);
						$entity = $this->UserSettings->patchEntity($entity,$data_to_save,['validate'=>false]);
						$this->UserSettings->save($entity);
					}else{
						$data_to_save = array(
											'user_id' => $user_id,
											'user_session_key' => $session_id,
											'setting_name' => "dr_credit_search",
											'data' => $jsondata
											);
						$entity = $this->UserSettings->newEntity();
						$entity = $this->UserSettings->patchEntity($entity,$data_to_save,['validate'=>false]);
						$this->UserSettings->save($entity);
					}
				}
				// $this->paginate = [
				//				  // 'conditions' => array('kiosk_id' => 0),
				//				   'order' => ['credit_receipt_id DESC']
				//				 ];
		}else{
			//echo $kiosk_id;die;
			if(empty($kiosk_id)){
				$CreditReceiptSource = "credit_receipts";
				$CreditProductDetailSource = "credit_product_details";
				$CreditPaymentDetailSource = "credit_payment_details";	
			}else{
				$CreditReceiptSource = "kiosk_{$kiosk_id}_credit_receipts";
				$CreditProductDetailSource = "kiosk_{$kiosk_id}_credit_product_details";
				$CreditPaymentDetailSource = "kiosk_{$kiosk_id}_credit_payment_details";
			}
			   if($this->request->session()->read('Auth.User.group_id') == ADMINISTRATORS || $this->request->session()->read('Auth.User.group_id') == FRANCHISE_OWNER){
				//$session_id = $this->Session->id();
				$user_id = $this->request->Session()->read('Auth.User.id');
				$data_arr = array('kiosk_id' => 10000);
				$jsondata = json_encode($data_arr);
				$this->loadModel('UserSettings');
				
				$res_query = $this->UserSettings->find('all',array('conditions' => array(
																			 'user_id' => $user_id,
																			 'setting_name' => "credit_search",
																			 //'user_session_key' => $session_id,
																			 )));
				$res_query = $res_query->hydrate(false);
				if(!empty($res_query)){
					$res = $res_query->first();
				}else{
					$res = array();
				}
				if(count($res) >0){
					$userSettingid =  $res['id'];
					$data_to_save = array(
										'id' => $userSettingid,
										'user_id' => $user_id,
										//'user_session_key' => $session_id,
										'setting_name' => "credit_search",
										'data' => $jsondata
										);
					$entity = $this->UserSettings->get($userSettingid);
					$entity = $this->UserSettings->patchEntity($entity,$data_to_save,['validate'=>false]);
					$this->UserSettings->save($entity);
				}else{
					$data_to_save = array(
										'user_id' => $user_id,
										//'user_session_key' => $session_id,
										'setting_name' => "credit_search",
										'data' => $jsondata
										);
					$entity = $this->UserSettings->newEntity();
					$entity = $this->UserSettings->patchEntity($entity,$data_to_save,['validate'=>false]);
					$this->UserSettings->save($entity);
				}
			}
			   $this->paginate = [
								   'order' => ['credit_receipt_id DESC'],
								   'limit' => 50
								 ];
		}
		
		$CreditReceiptTable = TableRegistry::get($CreditReceiptSource,[
														 'table' => $CreditReceiptSource,
													  ]);
		$CreditPaymentDetailTable = TableRegistry::get($CreditPaymentDetailSource,[
																	 'table' => $CreditPaymentDetailSource,
																  ]);
		$CreditProductDetailTable = TableRegistry::get($CreditProductDetailSource,[
																			 'table' => $CreditProductDetailSource,
																		  ]);
		$hint = $this->ScreenHint->hint('credit_product_details','view_credit_note');
					if(!$hint){
						$hint = "";
					}
		$this->set(compact('hint'));
		$creditPaymentDetails_query = $this->paginate($CreditPaymentDetailTable);
        if(!empty($creditPaymentDetails_query)){
            $creditPaymentDetails = $creditPaymentDetails_query->toArray();
        }else{
            $creditPaymentDetails = array();
        }
	   $req_data_query = $CreditPaymentDetailTable->find('all');
	   $req_data_query = $req_data_query->hydrate(false);
	   if(!empty($req_data_query)){
			$req_data = $req_data_query->toArray();
	   }else{
			$req_data = array();
	   }
        //pr($creditPaymentDetails);die;
		$amt_to_show = 0;
		
		$amt_to_show_query = $CreditPaymentDetailTable->find('all');
                  $amt_to_show_query
                          ->select(['sum' => $amt_to_show_query->func()->sum('amount')])
                          //->where(['Details.site_id' => $id])
                          ->toArray();
                          
		$amt_to_show_query = $amt_to_show_query->hydrate(false);
		if(!empty($amt_to_show_query)){
		  $amt_to_show_data = $amt_to_show_query->first();
		}else{
		  $amt_to_show_data = array();
		}
		if(!empty($amt_to_show_data)){
		 $amt_to_show =  $amt_to_show_data['sum'];
		}
        $creditReceiptData= array();
        if(!empty($creditPaymentDetails)){
            foreach($creditPaymentDetails as $creditPaymentDetails_value){
                $creditReceiptID = $creditPaymentDetails_value['credit_receipt_id'];
                $creditReceiptData_query = $CreditReceiptTable->find('all',[
                                                                            'conditions' => ['id' => $creditReceiptID]
                                                                            ]);
                $creditReceiptData_query = $creditReceiptData_query->hydrate(false);
                if(!empty($creditReceiptData_query)){
                    $creditReceiptData[$creditReceiptID] = $creditReceiptData_query->first();
                }else{
                    $creditReceiptData[$creditReceiptID] = array();
                }
            }
        }else{
            $creditReceiptData[] = array();
        }
        //pr(count($creditReceiptData));die;
        $this->set(compact('creditReceiptData','amt_to_show'));
		//pr($creditPaymentDetails);die;
		if($source == 1){
			//echo'hi';die;
			$kiosk_id = $this->request->Session()->read('kiosk_id');
			if(!empty($kiosk_id)){
				$query = $CreditReceiptTable->find('all',[
														  'conditions' => ['kiosk_id' => $kiosk_id]
														  ]);
                  $query
                          ->select(['TotalAmount' => $query->func()->sum('credit_amount')]);
				$result = $query->hydrate(false);
				if(!empty($result)){
				    $total = $result->first();
				}else{
				    $total = array();
				}
			}else{
			   $query = $CreditReceiptTable->find('all',[
														  'conditions' => ['kiosk_id' => 0]
														  ]);
                  $query
                          ->select(['TotalAmount' => $query->func()->sum('credit_amount')]);
					$result = $query->hydrate(false);
					if(!empty($result)){
					    $total = $result->first();
					}else{
					    $total = array();
					}
			}
			
		}else{
			//echo'bye';die;
			$query =  $CreditReceiptTable->find('all');
			$result = $query->hydrate(false);
			if(!empty($result)){
			    $total = $result->toArray();
			}else{
			    $total = array();
			}
			//pr($total);die;
		}
		$lptotalVat = $total_vat = $remaining_vat = $withVat_amt = $totalAmt = 0;
		$totalAmt = 0;
		if($source == 1){
			if(!empty($total)){
				$totalAmt = $total['TotalAmount'];
			}
		}else{
		  $lptotalVat = $lptotalPaymentAmount = $lpgrandNetAmount = 0;
			   if(!empty($creditPaymentDetails)){
				 foreach($creditPaymentDetails as $key => $value){
					  $paymentAmount = $value['amount'];
					  $lptotalPaymentAmount+=floatval($paymentAmount);
					  $vat = $creditReceiptData[$value['credit_receipt_id']]['vat'];
					  $vatPercentage = $vat/100;
					  $netAmount = $paymentAmount/(1+$vatPercentage);
					  $lpgrandNetAmount+=floatval($netAmount);
				 }
				 $lptotalVat = $lptotalPaymentAmount - $lpgrandNetAmount;
			   }
		  
			if(!empty($total)){
				//pr($total);die;
				//$totalAmt = $total[0]['TotalAmount'];
				$total_vat = $remaining_vat = $withVat_amt = $totalAmt = 0;
				//pr($total);die;
				foreach($total as $key => $value){
					$remaining_vat = $amount1 = 0;
					$amount1 = $value['credit_amount'];
					$totalAmt += $amount1;
					$vat = $value['vat'];
					if($vat >0){
						$amount1 = ($amount1 /(1+($vat/100)));
						$withVat_amt +=  $value['credit_amount'];
						$remaining_vat = $value['credit_amount'] - $amount1;
					}
					$total_vat += $remaining_vat;
				}
			}
		}
		$this->set(compact("withVat_amt","total_vat","lptotalVat"));
		$customerIdArr = array();
		if(!empty($creditReceiptData)){
		  foreach($creditReceiptData as $key => $value){
			   if(array_key_exists('customer_id',$value)){
					$customerIdArr[] = $value['customer_id'];	
			   }
			   
		   }  
		}
		
		$recipt_ids = array();
		foreach($req_data as $r_key => $r_value){
			$recipt_ids[] = $r_value['credit_receipt_id'];
		}
		if(empty($recipt_ids)){
			$recipt_ids = array(0=>null);
		}
		$recipt_res_query = $CreditReceiptTable->find('all',array('conditions' => array('id IN' => $recipt_ids)));
		$recipt_res_query = $recipt_res_query->hydrate(false);
		if(!empty($recipt_res_query)){
			$recipt_res = $recipt_res_query->toArray();
		}else{
			$recipt_res = array();
		}
        //pr($recipt_ids);die;
         $CreditProductData_query = $CreditProductDetailTable->find('all',[
													'conditions'=>['credit_receipt_id IN'=>$recipt_ids]
												]
										    );
        // pr($CreditProductData_query);die;
        $CreditProductData_query = $CreditProductData_query->hydrate(false);
        
        if(!empty($CreditProductData_query)){
            $creditProductDetail = $CreditProductData_query->toArray();
        }else{
            $creditProductDetail = array();
        }
        
		//pr($recipt_res);die;
		//if(!empty($recipt_res)){
		//	foreach($recipt_res as $recipt_res_k => $recipt_res_v){
		//		$credit_receipt_id = $recipt_res_v['id'];
		//		$creditProductDetail_query = $CreditProductDetailTable->find('all',[
		//															   'conditions'=>['credit_receipt_id'=>$credit_receipt_id]
		//															   ]);
		//		$creditProductDetail_query = $creditProductDetail_query->hydrate(false);
		//		if(!empty($creditProductDetail_query)){
		//			$creditProductDetail[] = $creditProductDetail_query->first();
		//		}else{
		//			$creditProductDetail[] = array();
		//		}
		//	}
		//}else{
		//	$creditProductDetail = array();
		//}
        
		//pr($creditProductDetail);die;
		$this->set(compact('creditProductDetail'));
		//pr($recipt_res);die;
		$product_ids = $quantity = array();
		//foreach($recipt_res as $r_kry => $r_value){
			foreach($creditProductDetail as $skey => $svalue){
				$product_ids[] = $svalue['product_id'];
				if(array_key_exists($svalue['product_id'],$quantity)){
					$quantity[$svalue['product_id']] += $svalue['quantity'];
				}else{
					$quantity[$svalue['product_id']] = $svalue['quantity'];
				}
				
			}
		//}
		if(empty($product_ids)){
			$product_ids = array(0=>null);
		}
		$res_p_query = $this->Products->find('list',[
									    'conditions' => [
														'Products.id IN' => $product_ids
													],
										'keyField' => 'id',
										'valueField' => 'cost_price',
									   ]
								);
		$res_p_query = $res_p_query->hydrate(false);
		if(!empty($res_p_query)){
			$res_p = $res_p_query->toArray();
		}else{
			$res_p = array();
		}
		//pr($res_p);pr($product_ids);die;
		//pr($res_p);pr($quantity);die;
		if(!empty($res_p)){
			$cost_sum = 0;
			foreach($res_p as $key => $value){
				if(array_key_exists($key,$quantity)){
					$cost_sum += $value*$quantity[$key];
				}
			}
		}else{
			$cost_sum = 0;
		}
		//echo $cost_sum;die;
		$this->set(compact('cost_sum'));
        //pr($customerIdArr);die;
		if(empty($customerIdArr)){
		  $customerIdArr = array(0 => null);
		}
		$customerBusiness_query = $this->Customers->find('list',[
                                                                    'conditions'=>['Customers.id IN'=>$customerIdArr],
                                                                    'keyField' => 'id',
                                                                    'valueField' => 'business'
                                                                ]);
        //pr($customerBusiness_query);die;
		$customerBusiness_query = $customerBusiness_query->hydrate(false);
		if(!empty($customerBusiness_query)){
			$customerBusiness = $customerBusiness_query->toArray();
		}else{
			$customerBusiness = array();
		}
		$kiosk_list_query = $this->Kiosks->find('list');
		$kiosk_list_query= $kiosk_list_query->hydrate(false);
		if(!empty($kiosk_list_query)){
			   $kiosk_list = $kiosk_list_query->toArray();
		}else{
			   $kiosk_list = array();
		}
		
		if($ext_site == 1){
		    $managerKiosk = $this->get_kiosk();
			   if(!empty($managerKiosk)){
					if(array_key_exists($kiosk_id,$managerKiosk)){
						// nothing to do;
					}else{
						if(empty($kiosk_id)){
							$kiosk_id = 10000;
						}else{
							$kiosk_id = current($managerKiosk);		
						}
					}
			   }
		}else{
		  $kiosk_id = 10000;
		}
		
		
		
		$agents_query = $this->Agents->find('list');
		$agents_query = $agents_query->hydrate(false);
		
		if(!empty($agents_query)){
			$agents = $agents_query->toArray();
		}
		$agents[0] = "Select Acc manager";
		ksort($agents);
		
		$customerAgent_query = $this->Customers->find('list',[
                                                                    'conditions'=>['Customers.id IN'=>$customerIdArr],
                                                                    'keyField' => 'id',
                                                                    'valueField' => 'agent_id'
                                                                ]);
        
		$customerAgent_query = $customerAgent_query->hydrate(false);
		if(!empty($customerAgent_query)){
			$customerAgent_res = $customerAgent_query->toArray();
		}else{
			$customerAgent_res = array();
		}
		$customerAgent = array();
		if(!empty($customerAgent_res)){
		   foreach($customerAgent_res as $k => $value){
			   if($value == 0){
					$customerAgent[$k] = "--";
			   }else{
					$customerAgent[$k] = $agents[$value];
			   }
			   
		   }
		}
		
		
		
       // pr($customerBusiness);die;
		$this->set(compact('creditPaymentDetails','customerBusiness','totalAmt','kiosk_list','kiosk_id','agents','customerAgent'));
		$this->render("view_credit_note");
	}
	public function search($keyword = ""){
		$conditionArr = array();
		if(array_key_exists('payment_type',$this->request->query)){
			$searchKeyword = $this->request->query['payment_type'];
			if($searchKeyword=="On Credit" ||
				$searchKeyword=="Cash" ||
				$searchKeyword=="Card" ||
				$searchKeyword=="Bank Transfer" ||
				$searchKeyword=="Cheque"){
				$conditionArr['payment_method like '] =  strtolower("%$searchKeyword%");
			}
			$this->set('searchKeyword',$this->request->query['payment_type']);
		}
         //pr($this->request);die;
		if(array_key_exists('kiosk_id',$this->request->query) || array_key_exists('kiosk-id',$this->request->query)){
			//echo'hi';die;
			if(array_key_exists('kiosk_id',$this->request->query)){
				$kiosk_id = $this->request->query['kiosk_id'];
			}
			if(array_key_exists('kiosk-id',$this->request->query)){
				$kiosk_id = $this->request->query['kiosk-id'];
			}
			//echo $kiosk_id;die;
			$this->set(compact('kiosk_id'));
			if($kiosk_id == 10000){
				$kiosk_id = "";
			}else{
				if(array_key_exists('kiosk_id',$this->request->query)){
					$kiosk_id = $this->request->query['kiosk_id'];
				}
				if(array_key_exists('kiosk-id',$this->request->query)){
					$kiosk_id = $this->request->query['kiosk-id'];
				}
			}
		}else{
			//echo'bye';die;
			$kiosk_id = $this->request->Session()->read('kiosk_id');
		}
		//echo $kiosk_id;die;
       	if(!empty($kiosk_id)){
			$productSource = "kiosk_{$kiosk_id}_products";
			$CreditReceiptSource = "kiosk_{$kiosk_id}_credit_receipts";
			$CreditProductDetailSource = "kiosk_{$kiosk_id}_credit_product_details";
			$CreditPaymentDetailSource = "kiosk_{$kiosk_id}_credit_payment_details";
		}else{
			$productSource = "products";
			$CreditReceiptSource = "credit_receipts";
			$CreditProductDetailSource = "credit_product_details";
			$CreditPaymentDetailSource = "credit_payment_details";
		}
		 
		$productTable = TableRegistry::get($productSource,[
												  'table' => $productSource,
											   ]);
		$CreditReceiptTable = TableRegistry::get($CreditReceiptSource,[
															   'table' => $CreditReceiptSource,
														    ]);
		$CreditProductDetailTable = TableRegistry::get($CreditProductDetailSource,[
																	 'table' => $CreditProductDetailSource,
																  ]);
		$CreditPaymentDetailTable = TableRegistry::get($CreditPaymentDetailSource,[
                                                                                    'table' => $CreditPaymentDetailSource,
                                                                                ]);
		if(array_key_exists('invoice_detail',$this->request->query)){
			$invoiceSearchKeyword = $this->request->query['invoice_detail'];
			$this->set('invoiceSearchKeyword',$this->request->query['invoice_detail']);
		}
		
		if(array_key_exists('start_date',$this->request->query)){
			$this->set('start_date',$this->request->query['start_date']);
		}
		
		if(array_key_exists('end_date',$this->request->query)){
			$this->set('end_date',$this->request->query['end_date']);
		}
		if(array_key_exists('date_type',$this->request->query)){
			$date_type = $this->request->query['date_type'];
		}

		$this->set(compact('date_type'));
		
		if(array_key_exists('start_date',$this->request->query) &&
		   array_key_exists('end_date',$this->request->query) &&
		   !empty($this->request->query['start_date']) &&
		   !empty($this->request->query['end_date'])){
		  //---------------------my code---------------------
		  if(array_key_exists('date_type',$this->request->query)){
				$date_type = $this->request->query['date_type'];
                $this->set(compact('date_type'));
				if($date_type == "payment"){
					$conditionArr[] = array(
						"created >" => date('Y-m-d', strtotime($this->request->query['start_date'])),
						"created <" => date('Y-m-d', strtotime($this->request->query['end_date']. ' +1 Days')),			
					       );
				}else{
					$conditionArr1 = array();
					$conditionArr1[] = array(
						"created >" => date('Y-m-d', strtotime($this->request->query['start_date'])),
						"created <" => date('Y-m-d', strtotime($this->request->query['end_date']. ' +1 Days')),			
					       );
					$Receipts_query = $CreditReceiptTable->find('list',array(
															'conditions' => $conditionArr1,
															'valueField' => 'id'
															));
					$Receipts_query = $Receipts_query->hydrate(false);
					if(!empty($Receipts_query)){
						$Receipts = $Receipts_query->toArray();
					}else{
						$Receipts = array();
					}
					if(empty($Receipts)){
						$Receipts = array(0 => null);
					}
					$conditionArr['credit_receipt_id IN'] = $Receipts;
				}
			}else{
				$conditionArr[] = array(
						"created >" => date('Y-m-d', strtotime($this->request->query['start_date'])),
						"created <" => date('Y-m-d', strtotime($this->request->query['end_date']. ' +1 Days')),			
					       );
			}
		  //--------------------my code-----------------------
			//$conditionArr[] = array(
			//			"created >" => date('Y-m-d', strtotime($this->request->query['start_date'])),
			//			"created <" => date('Y-m-d', strtotime($this->request->query['end_date']. ' +1 Days')),			
			//		       );
		}
		//pr($this->request);die;
		
		
		if(array_key_exists('search_kw',$this->request->query)){
			$textKeyword = $this->request->query['search_kw'];
			if(!empty($textKeyword) && array_key_exists('invoice_detail',$this->request->query)){
				if($invoiceSearchKeyword=="receipt_number"){
                    $search_recipt_Id = strtolower("'%$textKeyword%'");
					$conditionArr[] =  "credit_receipt_id like $search_recipt_Id";
				}elseif($invoiceSearchKeyword=="business"){
					$fname = strtolower("%$textKeyword%");
                    $customerIds_query = $this->Customers->find('list',
															array(
														'conditions'=>array(
																"OR" => array(
															"LOWER(`Customers`.`business`) like" => strtolower("%$textKeyword%"),
															"LOWER(`Customers`.`fname`) like" => strtolower("%$textKeyword%"),
											"LOWER(`Customers`.`business`) like" => strtolower("%$textKeyword%")												    )
												    ),
										'keyField'=>'id',
                                        'valueField' => 'id',
                                        //'recursive'=>-1
                                        ));
                    $customerIds_query = $customerIds_query->hydrate(false);
                    if(!empty($customerIds_query)){
                        $customerIds = $customerIds_query->toArray();
                    }else{
                        $customerIds = array();
                    }
                    if(empty($customerIds)){
                        $customerIds = array(0 => null);
                    }
					$receipt_query = $CreditReceiptTable->find('all',[
																	  'conditions' => ['customer_id IN' => $customerIds]
																	  ]);
					//pr($receipt_query);
					$receipt_query = $receipt_query->hydrate(false);
					if(!empty($receipt_query)){
						 $receipt = $receipt_query->toArray();
					}else{
						 $receipt = array();
					}
					//pr($receipt);die;
					foreach($receipt as $receipts){
						 $receiptID[] = $receipts['id'];
					}
					if(empty($receiptID)){
						 $receiptID = array(0 => null) ;
					}
					$conditionArr['credit_receipt_id IN'] =  $receiptID;
					//$conditionArr['CreditReceipt.fname like '] =  strtolower("%$textKeyword%");
				}elseif($invoiceSearchKeyword=="customer_id"){
					$receipt_query = $CreditReceiptTable->find('all',[
																	  'conditions' => ['customer_id' => $textKeyword]
																	  ]);
					//pr($receipt_query);
					$receipt_query = $receipt_query->hydrate(false);
					if(!empty($receipt_query)){
						 $receipt = $receipt_query->toArray();
					}else{
						 $receipt = array();
					}
					//pr($receipt);die;
					foreach($receipt as $receipts){
						 $receiptID[] = $receipts['id'];
					}
					//pr($receiptID);die;
					if(empty($receiptID)){
						 $receiptID = array(0 => null) ;
					}
					$conditionArr['credit_receipt_id IN'] =  $receiptID;
				}
				$this->set('textKeyword',$this->request->query['search_kw']);
			}
		}
		$agent_id = 0;
		if(array_key_exists('agent_id',$this->request->query) && !empty($this->request->query['agent_id'])){
			$agent_id = $this->request->query['agent_id'];
			   if(($invoiceSearchKeyword=="business" || $invoiceSearchKeyword=="customer_id") && !empty($this->request->query['search_kw'])){
					$search_kw = $this->request->query['search_kw'];
					if($invoiceSearchKeyword == "business"){
						 $agent_cust_res = $this->Customers->find("list",['conditions' => [
																"agent_id" => $agent_id,
																"OR" => array(
																 "LOWER(`Customers`.`business`) like" => strtolower("%$search_kw%"),
																 "LOWER(`Customers`.`fname`) like" => strtolower("%$search_kw%"),
												 "LOWER(`Customers`.`business`) like" => strtolower("%$search_kw%")	),
																],
																 'keyField' => "id",
																 "valueField" => "agent_id",
																 ])->toArray();
					}else{
						 $agent_cust_res = $this->Customers->find("list",['conditions' => [
																"agent_id" => $agent_id,
																'id' => $search_kw,
																],
																 'keyField' => "id",
																 "valueField" => "agent_id",
																 ])->toArray();
					}
					   
			   }else{
					$agent_cust_res = $this->Customers->find("list",['conditions' => [
														   "agent_id" => $agent_id,
														   ],
															'keyField' => "id",
															"valueField" => "agent_id",
															])->toArray();
			   }
			
			if(!empty($agent_cust_res)){
			   $searchCriteria['customer_id IN'] = array_keys($agent_cust_res);
			   if(array_key_exists('start_date',$this->request->query) &&
					array_key_exists('end_date',$this->request->query) &&
					!empty($this->request->query['start_date']) &&
					!empty($this->request->query['end_date']))
				{
							$date_type = $this->request->query['date_type'];
							if($date_type == 'payment'){
								
							}else{
								$searchCriteria[] = array(
											"created >" => date('Y-m-d', strtotime($this->request->query['start_date'])),
											"created <" => date('Y-m-d', strtotime($this->request->query['end_date']. ' +1 Days')),			
											   );
							}
				}
				if(empty($searchCriteria)){
					$searchCriteria = array('0'=>null);
				}
				
				$cutomerReceipts_query = $CreditReceiptTable->find('all',array('fields' => array('id'),
													'conditions' => $searchCriteria,
													));
				//pr($cutomerReceipts_query);die;
				$cutomerReceipts_query = $cutomerReceipts_query->hydrate(false);
				if(!empty($cutomerReceipts_query)){
					$cutomerReceipts = $cutomerReceipts_query->toArray();
				}else{
					$cutomerReceipts = array();
				}
				$receiptIDs = array();
				$conditionArr['credit_receipt_id IN'] = 0;
				if( count($cutomerReceipts) ){
					//echo $cutomerReceipts['ProductReceipt']['id'];
					foreach($cutomerReceipts as $cutomerReceipt){
						$receiptIDs[] = $cutomerReceipt['id'];
					}
					if(empty($receiptIDs)){
						$receiptIDs = array('0'=>null);
					}
					$conditionArr['credit_receipt_id IN'] = $receiptIDs;
				}
				
			}else{
			   $conditionArr['credit_receipt_id IN'] = array(0 => null);
			}
		}
		$this->set(compact('agent_id'));
		$this->paginate = [
                            'conditions' => $conditionArr,
                            'limit' => 20,
                            'order' => ['credit_receipt_id DESC'],
							'limit' => 50
                            //'contain' => 'CreditReceipts'
                          ];
		$creditPaymentDetails_query = $this->paginate($CreditPaymentDetailTable);
		if(!empty($creditPaymentDetails_query)){
			$creditPaymentDetails = $creditPaymentDetails_query->toArray();
		}else{
			$creditPaymentDetails = array(); 
		}
		//pr($creditPaymentDetails);
		$amt_to_show = 0;
		
		$amt_to_show_query = $CreditPaymentDetailTable->find('all',array('conditions' => $conditionArr));
                  $amt_to_show_query
                          ->select(['sum' => $amt_to_show_query->func()->sum('amount')])
                          //->where(['Details.site_id' => $id])
                          ->toArray();
                          
		$amt_to_show_query = $amt_to_show_query->hydrate(false);
		if(!empty($amt_to_show_query)){
		  $amt_to_show_data = $amt_to_show_query->first();
		}else{
		  $amt_to_show_data = array();
		}
		if(!empty($amt_to_show_data)){
		 $amt_to_show =  $amt_to_show_data['sum'];
		}
		if(!empty($creditPaymentDetails)){
			foreach($creditPaymentDetails as $creditPaymentDetails_value){
			   $creditReceiptId = $creditPaymentDetails_value['credit_receipt_id'];
			   
			   //echo $creditPaymentDetails_value['amount'];echo "</br>";
			  // $amt += $creditPaymentDetails_value['amount'];
			   $creditReceiptData_query = $CreditReceiptTable->find('all',[
															   'conditions' => ['id' => $creditReceiptId]
															   ]);
			   $creditReceiptData_query = $creditReceiptData_query->hydrate(false);
			   if(!empty($creditReceiptData_query)){
				   $creditReceiptData[$creditReceiptId] = $creditReceiptData_query->first();
			   }else{
				   $creditReceiptData[$creditReceiptId] = array();
			   }
			}
		}else{
		    $creditReceiptData[] = array();
		}
		$this->set(compact('amt_to_show'));
		//echo $amt_to_show;die;
        //pr($creditReceiptData);die;
		$this->set(compact('creditReceiptData'));
		$total_query =  $CreditPaymentDetailTable->find('all',[
                                                                 'conditions' => $conditionArr,
                                                                 //'contain' => 'CreditReceipts'
                                                                ]);
		$total_query = $total_query->hydrate(false);
		if(!empty($total_query)){
		    $total = $total_query->toArray();
		}else{
		    $total = array();
		}
		if(!empty($total)){
            //pr($total);die;
			$creditReceiptData = array();
			foreach($total as $total_value){
			    $creditReceipt_Id = $total_value['credit_receipt_id'];
			    $creditReceipt_Data_query = $CreditReceiptTable->find('all',[
															    'conditions' => ['id' => $creditReceipt_Id]
															    ]);
			    $creditReceipt_Data_query = $creditReceipt_Data_query->hydrate(false);
			    if(!empty($creditReceipt_Data_query)){
				   $creditReceiptData[$creditReceipt_Id] = $creditReceipt_Data_query->first();
			    }else{
				   $creditReceiptData[$creditReceipt_Id] = array();
			    }
			}
		}else{
			$creditReceiptData = array();
		}
        //pr($creditReceiptData);die;
		$this->set(compact('creditReceiptData'));
		$totalAmt = 0;
		//pr($creditReceiptData);die;
		$lptotalVat = $lptotalPaymentAmount = $lpgrandNetAmount = 0;
		if(!empty($creditPaymentDetails)){
		  foreach($creditPaymentDetails as $key => $value){
			   $paymentAmount = $value['amount'];
			   $lptotalPaymentAmount+=floatval($paymentAmount);
			   $vat = $creditReceiptData[$value['credit_receipt_id']]['vat'];
			   $vatPercentage = $vat/100;
			   $netAmount = $paymentAmount/(1+$vatPercentage);
			   $lpgrandNetAmount+=floatval($netAmount);
		  }
		  $lptotalVat = $lptotalPaymentAmount - $lpgrandNetAmount;
		}
		$total_vat = $remaining_vat = $withVat_amt = $totalAmt = 0;
		$recipt_ids = $quantity = $product_ids = array();
		if(!empty($total)){
			foreach($creditReceiptData as $key => $value){
				$remaining_vat = $amount1 = 0;
				$amount1 = $value['credit_amount'];
				$totalAmt += $amount1;
				$vat = $value['vat'];
				if($vat >0){
					$amount1 = ($amount1 /(1+($vat/100)));
					$withVat_amt +=  $value['credit_amount'];
					$remaining_vat = $value['credit_amount'] - $amount1;
				}
				$total_vat += $remaining_vat;
				$recipt_ids[] = $value['id'];
			}
		}
		$this->set(compact("withVat_amt","total_vat","lptotalVat"));
		if(empty($recipt_ids)){
			$recipt_ids = array(0=>null);
		}
        //pr($recipt_ids);die;
		$recipt_res_query = $CreditReceiptTable->find('all',array('conditions' => array('id IN' => $recipt_ids)));
		$recipt_res_query = $recipt_res_query->hydrate(false);
		if(!empty($recipt_res_query)){
			$recipt_res = $recipt_res_query->toArray();
		}else{
			$recipt_res = array();
		}
		//pr($recipt_res);die;
        
        $CreditProductData_query = $CreditProductDetailTable->find('all',[
													'conditions'=>['credit_receipt_id IN'=>$recipt_ids]
												]
										    );
        $CreditProductData_query = $CreditProductData_query->hydrate(false);
        if(!empty($CreditProductData_query)){
            $CreditProductData = $CreditProductData_query->toArray();
        }else{
            $CreditProductData = array();
        }
        //pr($CreditProductData);die;
		//if(!empty($recipt_res)){
		//	foreach($recipt_res as $recipt_key => $recipt_value){
		//		$id = $recipt_value['id'];
		//		$CreditProductData_query = $CreditProductDetailTable->find('all',[
		//											'conditions'=>['credit_receipt_id'=>$id]
		//										]
		//								    );
		//		$CreditProductData_query = $CreditProductData_query->hydrate(false);
		//		if(!empty($CreditProductData_query)){
		//			$CreditProductData[] = $CreditProductData_query->toArray();
		//		}else{
		//			$CreditProductData[] = array();
		//		}
		//	}
		//}
        
		//pr($CreditProductData);die;
		$product_ids = $quantity = array();
		//foreach($recipt_res as $r_kry => $r_value){
			foreach($CreditProductData as $skey => $svalue){
				$product_ids[] = $svalue['product_id'];
				if(array_key_exists($svalue['product_id'],$quantity)){
					$quantity[$svalue['product_id']] += $svalue['quantity'];
				}else{
					$quantity[$svalue['product_id']] = $svalue['quantity'];
				}
				
			}
		//}
       // pr($quantity);die;
		if(empty($product_ids)){
			$product_ids = array(0=>null);
		}
		$res_p_query = $this->Products->find('list',[
										'conditions' => [
														'Products.id IN' => $product_ids
													 ],
										'keyField' => 'id',
										'valueField' => 'cost_price'
									   ]
								);
		$res_p_query = $res_p_query->hydrate(false);
		if(!empty($res_p_query)){
			$res_p = $res_p_query->toArray();
		}else{
			$res_p = array();
		}
		//pr($res_p);pr($quantity);die;
		if(!empty($res_p)){
			$cost_sum = 0;
			foreach($res_p as $key => $value){
				if(array_key_exists($key,$quantity)){
					$cost_sum += $value*$quantity[$key];
				}
			}
		}else{
			$cost_sum = 0;
		}
		$this->set(compact('cost_sum'));
		
		if(!empty($creditPaymentDetails)){
			foreach($creditReceiptData as $creditReceiptData_value){
                //pr($creditPaymentDetail);die;
				$customerIdArr[] = $creditReceiptData_value['customer_id'];
			}
		}
		
		if(!empty($customerIdArr)){
			foreach($customerIdArr as $customerId){
				//pr($customerId);die;
				//if(empty($customerId)){
				//    $customerId = array(0 => null);
				//}
				$customerDetailArr_query = $this->Customers->find('all',array('conditions'=>array('Customers.id'=>$customerId),'fields'=>array('id','business','agent_id')));
				//pr($customerDetailArr_query);die;
				$customerDetailArr_query = $customerDetailArr_query->hydrate(false);
				
				if(!empty($customerDetailArr_query)){
					$customerDetailArr[] = $customerDetailArr_query->first();
				}else{
					$customerDetailArr[] = array();
				}
			}
		}
		
		
		
		$hint = $this->ScreenHint->hint('credit_product_details','view_credit_note');
					if(!$hint){
						$hint = "";
					}
		$kiosk_list_query = $this->Kiosks->find('list',[
                                                  'keyField' => 'id',
                                                  'valueField' => 'name',
                                                   
                                             ]
                                     );
		 
		$kiosk_list_query= $kiosk_list_query->hydrate(false);
		if(!empty($kiosk_list_query)){
			$kiosk_list = $kiosk_list_query->toArray();
		}else{
			$kiosk_list = array();
		}
		//pr($kiosk_list);die;
		if($this->request->session()->read('Auth.User.group_id') == ADMINISTRATORS || $this->request->session()->read('Auth.User.group_id') == FRANCHISE_OWNER){
			$session_id = $this->request->session()->id(); 
			$user_id = $this->request->session()->read('Auth.User.id');
			$data_arr = array('kiosk_id' => $kiosk_id);
			$jsondata = json_encode($data_arr);
			$this->loadModel('UserSettings');
			$res_query = $this->UserSettings->find('all',array('conditions' => array(
																		 'user_id' => $user_id,
																		 'setting_name' => "credit_search",
																		 //'user_session_key' => $session_id,
																		 )));
            
			$res_query= $res_query->hydrate(false);
			if(!empty($res_query)){
				  $res = $res_query->First();
			}else{
				  $res = array();
			}
          // pr($res);die;
			if(count($res) >0){
				$userSettingid =  $res['id'];
				$data_to_save = array(
									'id' => $userSettingid,
									'user_id' => $user_id,
									'user_session_key' => $session_id,
									'setting_name' => "credit_search",
									'data' => $jsondata
									);
				$UserSetting = $this->UserSettings->get($userSettingid);
				$UserSetting_data = $this->UserSettings->patchEntity($UserSetting, $data_to_save);
				$this->UserSettings->save($UserSetting_data);
			}else{
               	$data_to_save = array(
									'user_id' => $user_id,
									'user_session_key' => $session_id,
									'setting_name' => "credit_search",
									'data' => $jsondata
									);
				$UserSettingEntity = $this->UserSettings->newEntity();
				$UserSettingPatchEntity = $this->UserSettings->patchEntity($UserSettingEntity, $data_to_save);
				$this->UserSettings->save($UserSettingPatchEntity);
			}
		}
		
		$agents_query = $this->Agents->find('list');
		$agents_query = $agents_query->hydrate(false);
		
		if(!empty($agents_query)){
			$agents = $agents_query->toArray();
		}
		$agents[0] = "Select Acc manager";
		ksort($agents);
		
		
		$customerBusiness = array();
		if(!empty($customerDetailArr)){
			foreach($customerDetailArr as $customerDetail){
					$customerBusiness[$customerDetail['id']] = $customerDetail['business'];
					if($customerDetail['agent_id'] == 0){
						 $customerAgent[$customerDetail['id']] = "--";
					}else{
						$customerAgent[$customerDetail['id']] = $agents[$customerDetail['agent_id']]; 
					}
			}
		}
		
		
		$this->set(compact('hint','creditPaymentDetails','customerBusiness','totalAmt','kiosk_list','agents','customerAgent'));
		//$this->layout = 'default';
		$this->render('view_credit_note');
	}
	
    public function search1($keyword = ""){
		// pr($this->request);die;
		if(array_key_exists('kiosk_id',$this->request->query)){
		  $kiosk_id = $this->request->query['kiosk_id'];
		  $this->set(compact('kiosk_id'));
		  if($kiosk_id == 10000){
			   $kiosk_id = "";
		  }
		}else{
		  $kiosk_id = $this->request->Session()->read('kiosk_id');
		}
		if(!empty($kiosk_id)){
			$productSource = "kiosk_{$kiosk_id}_products";
			$CreditReceiptSource = "kiosk_{$kiosk_id}_credit_receipts";
			$CreditProductDetailSource = "kiosk_{$kiosk_id}_credit_product_details";
			$CreditPaymentDetailSource = "kiosk_{$kiosk_id}_credit_payment_details";
		}else{
            $productSource = "products";
			$CreditReceiptSource = "credit_receipts";
			$CreditProductDetailSource = "credit_product_details";
			$CreditPaymentDetailSource = "credit_payment_details";
        }
        $productTable = TableRegistry::get($productSource,[
                                                            'table' => $productSource,
                                                        ]);
        $CreditReceiptTable = TableRegistry::get($CreditReceiptSource,[
                                                                            'table' => $CreditReceiptSource,
                                                                        ]);
        $CreditProductDetailTable = TableRegistry::get($CreditProductDetailSource,[
                                                                                    'table' => $CreditProductDetailSource,
                                                                                ]);
        $CreditPaymentDetailTable = TableRegistry::get($CreditPaymentDetailSource,[
                                                                                    'table' => $CreditPaymentDetailSource,
                                                                                ]);
		$conditionArr = array();
		if(array_key_exists('payment_type',$this->request->query)){
			$searchKeyword = $this->request->query['payment_type'];
			if($searchKeyword=="On Credit" ||
				$searchKeyword=="Cash" ||
				$searchKeyword=="Card" ||
				$searchKeyword=="Bank Transfer" ||
				$searchKeyword=="Cheque"){
				     $conditionArr['payment_method like '] =  strtolower("%$searchKeyword%");
			}
			$this->set('searchKeyword',$this->request->query['payment_type']);
		}
		if(array_key_exists('invoice_detail',$this->request->query)){
			$invoiceSearchKeyword = $this->request->query['invoice_detail'];
			$this->set('invoiceSearchKeyword',$this->request->query['invoice_detail']);
		}
		
		if(array_key_exists('start_date',$this->request->query)){
			$this->set('start_date',$this->request->query['start_date']);
		}
		
		if(array_key_exists('end_date',$this->request->query)){
			$this->set('end_date',$this->request->query['end_date']);
		}
		
		if(array_key_exists('start_date',$this->request->query) &&
		   array_key_exists('end_date',$this->request->query) &&
		   !empty($this->request->query['start_date']) &&
		   !empty($this->request->query['end_date'])){
			$conditionArr[] = array(
						"created >" => date('Y-m-d', strtotime($this->request->query['start_date'])),
						"created <" => date('Y-m-d', strtotime($this->request->query['end_date']. ' +1 Days')),			
					       );
		}
		if(array_key_exists('search_kw',$this->request->query)){
			$textKeyword = $this->request->query['search_kw'];
			if(!empty($textKeyword) && array_key_exists('invoice_detail',$this->request->query)){
				if($invoiceSearchKeyword=="receipt_number"){
					$conditionArr['credit_receipt_id like '] =  strtolower("%$textKeyword%");
				}elseif($invoiceSearchKeyword=="customer"){
					$fname = strtolower("%$textKeyword%");
					$receipt_query = $CreditReceiptTable->find('all',[
																	  'conditions' => ['fname like' => $fname]
																	  ]);
					//pr($receipt_query);
					$receipt_query = $receipt_query->hydrate(false);
					if(!empty($receipt_query)){
						 $receipt = $receipt_query->toArray();
					}else{
						 $receipt = array();
					}
					//pr($receipt);die;
					foreach($receipt as $receipts){
						 $receiptID[] = $receipts['id'];
					}
					if(empty($receiptID)){
						 $receiptID = array(0 => null) ;
					}
					$conditionArr['credit_receipt_id IN'] =  $receiptID;
					//$conditionArr['CreditReceipt.fname like '] =  strtolower("%$textKeyword%");
				}elseif($invoiceSearchKeyword=="customer_id"){
					$receipt_query = $CreditReceiptTable->find('all',[
																	  'conditions' => ['customer_id' => $textKeyword]
																	  ]);
					//pr($receipt_query);
					$receipt_query = $receipt_query->hydrate(false);
					if(!empty($receipt_query)){
						 $receipt = $receipt_query->toArray();
					}else{
						 $receipt = array();
					}
					//pr($receipt);die;
					foreach($receipt as $receipts){
						 $receiptID[] = $receipts['id'];
					}
					//pr($receiptID);die;
					if(empty($receiptID)){
						 $receiptID = array(0 => null) ;
					}
					$conditionArr['credit_receipt_id IN'] =  $receiptID;
				}
				$this->set('textKeyword',$this->request->query['search_kw']);
			}
		}
		$this->paginate = [
                            'conditions' => $conditionArr,
                            'limit' => 20,
                            'order' => ['id DESC'],
                            //'contain' => 'CreditReceipts'
                          ];
		
		$creditPaymentDetails_query = $this->paginate($CreditPaymentDetailTable);
        if(!empty($creditPaymentDetails_query)){
            $creditPaymentDetails = $creditPaymentDetails_query->toArray();
        }else{
            $creditPaymentDetails = array(); 
        }
        //pr($creditPaymentDetails);die;
        if(!empty($creditPaymentDetails)){
            foreach($creditPaymentDetails as $creditPaymentDetails_value)
            $creditReceiptId = $creditPaymentDetails_value['credit_receipt_id'];
            $creditReceiptData_query = $CreditReceiptTable->find('all',[
                                                                        'conditions' => ['id' => $creditReceiptId]
                                                                        ]);
            $creditReceiptData_query = $creditReceiptData_query->hydrate(false);
            if(!empty($creditReceiptData_query)){
                $creditReceiptData[$creditReceiptId] = $creditReceiptData_query->toArray();
            }else{
                $creditReceiptData[$creditReceiptId] = array();
            }
        }else{
            $creditReceiptData[] = array();
        }
        //pr($creditReceiptData);die;
		$this->set(compact('creditReceiptData'));
		$total_query =  $CreditPaymentDetailTable->find('all',[
                                                                 'conditions' => $conditionArr,
                                                                 //'contain' => 'CreditReceipts'
                                                                ]);
        $total_query = $total_query->hydrate(false);
        if(!empty($total_query)){
            $total = $total_query->toArray();
        }else{
            $total = array();
        }
        if(!empty($total)){
            //pr($total);die;
            $creditReceiptData = array();
            foreach($total as $total_value){
                $creditReceipt_Id = $total_value['credit_receipt_id'];
                $creditReceipt_Data_query = $CreditReceiptTable->find('all',[
                                                                            'conditions' => ['id' => $creditReceipt_Id]
                                                                            ]);
                $creditReceipt_Data_query = $creditReceipt_Data_query->hydrate(false);
                if(!empty($creditReceipt_Data_query)){
                    $creditReceiptData[$creditReceipt_Id] = $creditReceipt_Data_query->first();
                }else{
                    $creditReceiptData[$creditReceipt_Id] = array();
                }
            }
        }else{
            $creditReceiptData = array();
        }
        //pr($creditReceiptData);die;
        $this->set(compact('creditReceiptData'));
		$totalAmt = 0;
		if(!empty($total)){
			foreach($creditReceiptData as $key => $value){
                //pr($value);die;
				$totalAmt += $value['credit_amount'];
			}
		}
		
		
		if(!empty($creditPaymentDetails)){
			foreach($creditReceiptData as $creditReceiptData_value){
                //pr($creditPaymentDetail);die;
				$customerIdArr[] = $creditReceiptData_value['customer_id'];
			}
		}
		
		if(!empty($customerIdArr)){
			foreach($customerIdArr as $customerId){
                //pr($customerId);die;
                //if(empty($customerId)){
                //    $customerId = array(0 => null);
                //}
				$customerDetailArr_query = $this->Customers->find('all',array('conditions'=>array('Customers.id'=>$customerId),'fields'=>array('id','business')));
                //pr($customerDetailArr_query);die;
                $customerDetailArr_query = $customerDetailArr_query->hydrate(false);
                
                if(!empty($customerDetailArr_query)){
                    $customerDetailArr[] = $customerDetailArr_query->first();
                }else{
                    $customerDetailArr[] = array();
                }
            }
		}
		
		
		$customerBusiness = array();
        //pr($customerDetailArr);die;
		if(!empty($customerDetailArr)){
			foreach($customerDetailArr as $customerDetail){
                //pr($customerDetail);die;
					$customerBusiness[$customerDetail['id']] = $customerDetail['business'];
			}
		}
		$hint = $this->ScreenHint->hint('credit_product_details','view_credit_note');
					if(!$hint){
						$hint = "";
					}

		$kiosk_list_query = $this->Kiosks->find('list');
		$kiosk_list_query= $kiosk_list_query->hydrate(false);
		if(!empty($kiosk_list_query)){
			   $kiosk_list = $kiosk_list_query->toArray();
		}else{
			   $kiosk_list = array();
		}
		$kiosk_id = 10000;			
		$this->set(compact('hint','creditPaymentDetails','customerBusiness','totalAmt','kiosk_list'));
		//$this->layout = 'default';
		$this->render('view_credit_note');
	}
    
	public function view($id = null,$kiosk_id="") {
		//pr($this->request);die;
		//echo $kiosk_id;die;
		//pr($kiosks_id);die;
		$newKioskID = $kiosk_id;
		$new_sessionKioskId = $this->request->Session()->read('kiosk_id');
		if($newKioskID == ''){
			if($new_sessionKioskId != ''){
				$newKioskID = $new_sessionKioskId;
			}else{
				$newKioskID = 10000;
			}
		}
		$kioskDetails_query = $this->Kiosks->find('all',array('conditions'=>array('Kiosks.id'=>$newKioskID),'recursive'=>-1,
													    //'fields'=>array('id','name','address_1','address_2','city','state','zip','contact','country')
													    ));
			$kioskDetails_result = $kioskDetails_query->hydrate(false);
			if(!empty($kioskDetails_result)){
			    $NewkioskDetails = $kioskDetails_result->first();
			}else{
			    $NewkioskDetails = array();
			}
		$CURRENCY_TYPE = Configure::read('CURRENCY_TYPE');
		if($kiosk_id != 10000 && $kiosk_id != ""){
			$CreditReceiptSource = "kiosk_{$kiosk_id}_credit_receipts";
			$CreditProductDetailSource = "kiosk_{$kiosk_id}_credit_product_details";
			$CreditPaymentDetailSource = "kiosk_{$kiosk_id}_credit_payment_details";
			
		}else{
			if($kiosk_id == 10000){
				$CreditReceiptSource = "credit_receipts";
				$CreditProductDetailSource = "credit_product_details";
				$CreditPaymentDetailSource = "credit_payment_details";
			}else{
				$kiosks_id = $this->request->Session()->read('kiosk_id');
				if($kiosks_id == ""){
					$CreditReceiptSource = "credit_receipts";
					$CreditProductDetailSource = "credit_product_details";
					$CreditPaymentDetailSource = "credit_payment_details";	
				}else{
					$CreditReceiptSource = "kiosk_{$kiosks_id}_credit_receipts";
					$CreditProductDetailSource = "kiosk_{$kiosks_id}_credit_product_details";
					$CreditPaymentDetailSource = "kiosk_{$kiosks_id}_credit_payment_details";
				}
			}
		}
		$CreditReceiptTable = TableRegistry::get($CreditReceiptSource,[
                                                                            'table' => $CreditReceiptSource,
                                                                        ]);
		$CreditProductDetailTable = TableRegistry::get($CreditProductDetailSource,[
																    'table' => $CreditProductDetailSource,
																]);
		$CreditPaymentDetailTable = TableRegistry::get($CreditPaymentDetailSource,[
																    'table' => $CreditPaymentDetailSource,
																]);
		if (!$CreditReceiptTable->exists($id)) {
			throw new NotFoundException(__('Invalid credit receipt'));
		}
		//Configure::load('common-arrays');
		$countryOptions = Configure::read('uk_non_uk');
		
		$fullAddress = $kiosk_id = $kioskDetails = $kioskName = $kioskAddress1 = $kioskAddress2 = $kioskCity = $kioskState = $kioskZip  = $kioskZip = $kioskContact = $kioskCountry = $kioskTable = "";
		$kioskDetails = array();
		if(($this->request->session()->read('Auth.User.group_id') == KIOSK_USERS &&
			$this->request->session()->read('Auth.User.user_type')=='wholesale')||!empty($kiosk_id)){
			$kiosk_id = $this->request->Session()->read('kiosk_id');
			$kioskDetails_query = $this->Kiosks->find('all',array('conditions'=>array('Kiosks.id'=>$kiosk_id),'recursive'=>-1,
													    //'fields'=>array('id','name','address_1','address_2','city','state','zip','contact','country')
													    ));
			$kioskDetails_result = $kioskDetails_query->hydrate(false);
			if(!empty($kioskDetails_result)){
			    $kioskDetails = $kioskDetails_result->first();
			}else{
			    $kioskDetails = array();
			}
			//pr($kioskDetails);die;
			$kioskName = $kioskDetails['name'];
			$kioskAddress1 = $kioskDetails['address_1'];
			$kioskAddress2 = $kioskDetails['address_2'];
			$kioskCity = $kioskDetails['city'];
			$kioskState = $kioskDetails['state'];
			$kioskZip = $kioskDetails['zip'];
			$kioskContact = $kioskDetails['contact'];
			$kioskCountry = $kioskDetails['country'];
			
			if(!empty($kioskAddress1)){
				$fullAddress.=$kioskAddress1.", ";
			}
			
			if(!empty($kioskAddress2)){
				$fullAddress.=$kioskAddress2.", ";
			}
			
			if(!empty($kioskCity)){
				$fullAddress.=$kioskCity.", ";
			}
			
			if(!empty($kioskState)){
				$fullAddress.=$kioskState.", ";
			}
			
			if(!empty($kioskZip)){
				$fullAddress.=$kioskZip.", ";
			}
			
			if(!empty($kioskCountry)){
				$fullAddress.=$countryOptions[$kioskCountry];
			}
			
			$kioskTable = "<table>
			<tr><td style='color: chocolate;'>".$kioskName."</td></tr>
			<tr><td style='font-size: 11px;'>".$fullAddress."</td></tr>
			</table>";
		}
		$creditReceiptDetail_query = $CreditReceiptTable->find('all',[
                                                                        'conditions'=>['id'=>$id],
                                                                        //'contain' =>['CreditProductDetails' , 'Customers','CreditPaymentDetails']
                                                                        ]);
		$creditReceiptDetail_result = $creditReceiptDetail_query->hydrate(false);
		if(!empty($creditReceiptDetail_result)){
		    $creditReceiptDetail = $creditReceiptDetail_result->first();
		}else{
		    $creditReceiptDetail = array();
		}
       // pr($CreditProductDetailTable);die;
		if(!empty($creditReceiptDetail)){
			$creditProductDetailsId = $creditReceiptDetail['id'];
			$creditProductDetailsData_query = $CreditProductDetailTable->find('all',[
																		 'conditions' => ['credit_receipt_id' => $creditProductDetailsId]
																	  ]);
			$creditProductDetailsData_query = $creditProductDetailsData_query->hydrate(false);
			if(!empty($creditProductDetailsData_query)){
				$creditProductDetailsData = $creditProductDetailsData_query->toArray();
			}else{
				$creditProductDetailsData = array();
			}
			//pr($creditProductDetailsData);die;
			$creditPaymentDetailId = $creditReceiptDetail['id'];
			$creditPaymentDetailData_query = $CreditPaymentDetailTable->find('all',[
																		 'conditions' => ['credit_receipt_id' => $creditPaymentDetailId]
																	  ]);
			$creditPaymentDetailData_query = $creditPaymentDetailData_query->hydrate(false);
			if(!empty($creditPaymentDetailData_query)){
				$creditPaymentDetailData = $creditPaymentDetailData_query->toArray();
			}else{
				$creditPaymentDetailData = array();
			}
			//pr($creditPaymentDetailData);die;
			$customerID = $creditReceiptDetail['customer_id'];
			$customerData_query = $this->Customers->find('all',[
														 'conditions' => ['id' => $customerID]
													  ]);
			$customerData_query = $customerData_query->hydrate(false);
			if(!empty($customerData_query)){
				$customer = $customerData_query->first();
			}else{
				$customer = array();
			}
		}else{
			$creditProductDetailsData = array();
			$creditPaymentDetailData = array();
			$customer = array();
		}
		//pr($creditProductDetailsData);die;
        //pr($creditProductDetailsData);die;
        //pr($customer);die;
		if(!empty($customer)){
			$customerEmail = $customer['email'];
		}
		$this->set(compact('creditProductDetailsData','creditPaymentDetailData','customer','customerEmail'));
        //pr($creditReceiptDetail);die;
		$userId = $creditReceiptDetail['processed_by'];
		$userName_query = $this->Users->find('list',[
                                                    'conditions'=>array('Users.id'=>$userId),
                                                    'keyField' => 'id',
                                                    'valueField' => 'username'
                                                    ]);
		$userName_query = $userName_query->hydrate(false);
		if(!empty($userName_query)){
		    $userName = $userName_query->toArray();
		}else{
		    $userName = array();
		}
		foreach($userName as $user_id => $user_name){break;}
		$settingArr = $this->setting;
		$vat = $this->VAT;
		$productName_query = $this->Products->find('list',[
                                                        'keyField' => 'id',
                                                        'valueField' => 'product'
                                                    ]);
		$productName_query  = $productName_query->hydrate(false);
		if(!empty($productName_query)){
		    $productName = $productName_query->toArray();
		}else{
		    $productName = array();
		}
		$productCode_query = $this->Products->find('list',[
                                                        'keyField' => 'id',
                                                        'valueField' => 'product_code'
                                                    ]);
		if(!empty($productCode_query)){
		    $productCode = $productCode_query->toArray();
		}else{
		    $productCode = array();
		}
        $this->set(compact('productCode'));
		$paymentDetails_query = $CreditPaymentDetailTable->find('all',array(
									'conditions'=>array('credit_receipt_id'=>$id)
									)
								  );
		$paymentDetails_query = $paymentDetails_query->hydrate(false);
		if(!empty($paymentDetails_query)){
		    $paymentDetails = $paymentDetails_query->toArray();
		}else{
		    $paymentDetails = array();
		}
		$payment_method = array();
		foreach($paymentDetails as $key=>$paymentDetail){
			$payment_method[] = $paymentDetail['payment_method']." ".$CURRENCY_TYPE.$paymentDetail['amount'];
		}
		//pr($this->request);die;
		//pr($customer);die;
		$this->set(compact('creditProductDetailsData','creditPaymentDetailData','customerData'));
		$send_by_email = Configure::read('send_by_email');
		$emailSender = Configure::read('EMAIL_SENDER');
		if(isset($this->request->data['submit']) && $this->request->data['submit'] == "Submit"){
			if(isset($this->request->data['customer_email']) && !empty($this->request->data['customer_email'])){
				$emailTo = $this->request->data['customer_email'];
                //pr($emailTo);die;
                //echo $this->fromemail;die;
				$CURRENCY_TYPE = Configure::read('CURRENCY_TYPE');
				$Email = new Email();
				$Email->config('default');
				$Email->viewVars(array(
								'productReceipt' => $creditReceiptDetail,
								'payment_method' => $payment_method,
								'vat' => $vat,
								'settingArr' =>$settingArr,
								'user_name'=>$user_name,
								'productName'=>$productName,
								'productCode'=>$productCode,
								'kioskContact'=>$kioskContact,
								'kioskTable'=>$kioskTable,
								'countryOptions'=>$countryOptions,
								'creditProductDetailsData' => $creditProductDetailsData,
								'creditPaymentDetailData' => $creditPaymentDetailData,
								'customer' => $customer,
								'CURRENCY_TYPE' => $CURRENCY_TYPE,
								'NewkioskDetails' => $NewkioskDetails
                                       )
                                );
				$Email->template('credit_receipt');
				$Email->emailFormat('both');
				$Email->to($emailTo);
                $Email->transport(TRANSPORT);
                $Email->from([$send_by_email => $emailSender]);
				//$Email->sender("sales@oceanstead.co.uk");
				$Email->subject('Credit Receipt');
				$Email->send();
				$this->Flash->success("Email send successfully");
			}else{
				$this->Flash->error("Please enter email");
			}
		}
		
		$this->set(compact('creditReceiptDetail','productName','paymentDetails','vat','settingArr','user_name','payment_method'));
		$this->set(compact('kioskTable','kioskContact','countryOptions','kioskDetails','NewkioskDetails'));
        
     }
    
	public function updateCreditPayment($paymentId = '',$kiosk_id = ""){
	 
		if($kiosk_id != 10000 && $kiosk_id != ""){
			$CreditReceiptSource = "kiosk_{$kiosk_id}_credit_receipts";
			$CreditProductDetailSource = "kiosk_{$kiosk_id}_credit_product_details";
			$CreditPaymentDetailSource = "kiosk_{$kiosk_id}_credit_payment_details";
			$CreditReceiptTable = TableRegistry::get($CreditReceiptSource,[
															    'table' => $CreditReceiptSource,
														    ]);
			$CreditProductDetailTable = TableRegistry::get($CreditProductDetailSource,[
																		    'table' => $CreditProductDetailSource,
																	    ]);
			$CreditPaymentDetailTable = TableRegistry::get($CreditPaymentDetailSource,[
																		    'table' => $CreditPaymentDetailSource,
																	    ]);
		}else{
            if(!empty($kiosk_id) && $kiosk_id == 10000){
				$CreditReceiptSource = "credit_receipts";
				$CreditProductDetailSource = "credit_product_details";
				$CreditPaymentDetailSource = "credit_payment_details";   
            }else{
                $kiosk_id = $this->request->Session()->read('kiosk_id');
			 if($kiosk_id == ""){
				$CreditReceiptSource = "credit_receipts";
				$CreditProductDetailSource = "credit_product_details";
				$CreditPaymentDetailSource = "credit_payment_details";	
			 }else{
				$CreditReceiptSource = "kiosk_{$kiosk_id}_credit_receipts";
				$CreditProductDetailSource = "kiosk_{$kiosk_id}_credit_product_details";
				$CreditPaymentDetailSource = "kiosk_{$kiosk_id}_credit_payment_details";
			 }
            }
			
			$CreditReceiptTable = TableRegistry::get($CreditReceiptSource,[
															    'table' => $CreditReceiptSource,
														    ]);
			$CreditProductDetailTable = TableRegistry::get($CreditProductDetailSource,[
																		    'table' => $CreditProductDetailSource,
																	    ]);
			$CreditPaymentDetailTable = TableRegistry::get($CreditPaymentDetailSource,[
																		    'table' => $CreditPaymentDetailSource,
																	    ]);
		}
		
		$paymentData_query = $CreditPaymentDetailTable->find('all',array(
							'conditions' => array('id'=>$paymentId),
							'recursive' => -1
								)
							   );
		$paymentData_result =$paymentData_query->hydrate(false);
		if(!empty($paymentData_result)){
			$paymentData = $paymentData_result->first();
		}else{
			$paymentData = array();
		}
		$credit_cleared_or_not = $paymentData['credit_cleared'];
		if($credit_cleared_or_not == 1){
			   $this->Flash->error('This credit Note is Allready Processed!');
			 		$kiosk_id_to_set = $this->get_kiosk_for_credit("credit_search");
					return $this->redirect(array('action'=>"search?kiosk_id=$kiosk_id_to_set&payment_type=All&invoice_detail=receipt_number"));
			  die;
		}
		
		
		$recit_id = $paymentData['credit_receipt_id'];
		$old_payment_method = $paymentData['payment_method'];
		$result_query = $CreditReceiptTable->find('all',array(
							'conditions' => array('id'=>$recit_id,
												  //'kiosk_id' => $kiosk_id
												  )
								)
							  );
		$result_query = $result_query->hydrate(false);
		if(!empty($result_query)){
			$result = $result_query->first();
		}else{
			$result = array();
		}
		$recit_created = $result['created'];
		$note_or_quotation = 1;
		$this->set(compact('recit_created','note_or_quotation'));
		
		//pr($paymentData);die;
		if ($this->request->is(array('post', 'put'))){
		  if(array_key_exists("ticked",$this->request->data)){
				$ticked = $this->request->data['ticked'];
			}else{
				$ticked = 0;
			}
			$paymentMode = $this->request['data']['change_mode'];
			if($paymentMode=="Cheque"||
				$paymentMode=="Cash"||
				$paymentMode=="Bank Transfer"||
				$paymentMode=="Card"
				){
				 $paymentStatus = 1;
			}elseif($paymentMode=="On Credit"){
				$paymentStatus = 0;
			}
			if($ticked == 1){
			   if(array_key_exists("date_box_date",$this->request->data)){
					$date_box_date = date("Y-m-d G:i:s",strtotime($this->request->data['date_box_date']));
			   }else{
					$date_box_date = "";
			   }
			   $credit_cleared = 0;
			    if(array_key_exists('added_amount',$this->request->data) && is_numeric($this->request->data['added_amount']) && $this->request->data['added_amount'] > 0){
					if($this->request->data['sale_amount'] != $this->request->data['old_amt'] + $this->request->data['added_amount']){
						 $this->Flash->error('Payment could not be updated!');
						 return $this->redirect(array('action' => 'update_payment',$paymentId));
						 die;
					 }
					 
					 $new_paymentMode = $this->request->data['new_change_mode'];
					 $new_added_amount = $this->request->data['added_amount'];
					if($new_paymentMode=="Cheque"||
						 $new_paymentMode=="Cash"||
						 $new_paymentMode=="Bank Transfer"||
						 $new_paymentMode=="Card"
						 ){
						  $paymentStatus = 1;
					}elseif($new_paymentMode=="On Credit"){
						 $paymentStatus = 0;
					}
					
					
					
					if($old_payment_method == "On Credit"){
						 if($paymentMode != "On Credit"){
							  $credit_cleared = 1;
						 }
					}
					$new_credit_cleared = 0;
					if($new_paymentMode != "On Credit"){
						 $new_credit_cleared = 1;
					}
					$new_box_desc = "";
					if(array_key_exists('new_box_desc',$this->request->data)){
						 $new_box_desc = $this->request->data['new_box_desc'];
					}
					
					
					$paymentDetailData = array(
							 'payment_method' => $new_paymentMode,
							 'payment_status' => $paymentStatus,
							 'credit_receipt_id' => $paymentData['credit_receipt_id'],
							 'kiosk_id' =>$kiosk_id,
							 'amount' => $new_added_amount,
							 'description' => $new_box_desc,
							 //'credit_cleared' => $new_credit_cleared,
							 'created' => $date_box_date,
							 
								 );
					$CreditPaymentDetailTable->behaviors()->load('Timestamp');
					$credit_entity = $CreditPaymentDetailTable->newEntity();
					$credit_entity = $CreditPaymentDetailTable->patchEntity($credit_entity,$paymentDetailData,['validate'=>false]);
					$CreditPaymentDetailTable->save($credit_entity);
					$desciption = "";
					if(array_key_exists('desc',$this->request->data)){
						 $desciption = $this->request->data['desc'];
					}
					
					
					$old_amount = $this->request->data['old_amt']; 
					$paymentDetailData = array(
								'id' => $paymentId,
								'payment_method' => $paymentMode,
								'amount' => $old_amount,
								'payment_status' => $paymentStatus,
								'description' => $desciption,
								//'credit_cleared' => $credit_cleared,
								'created' => $date_box_date,
									);
					$CreditPaymentDetailTable->behaviors()->load('Timestamp');
					$get_id = $CreditPaymentDetailTable->get($paymentId);
					$patch_entity = $CreditPaymentDetailTable->patchEntity($get_id,$paymentDetailData,['validate'=>false]);
					
					$CreditPaymentDetailTable->save($patch_entity);
			   }
			   
			   if($old_payment_method == "On Credit"){
					if($paymentMode != "On Credit"){
						 $credit_cleared = 1;
					}
			   }
			   $desciption = "";
					if(array_key_exists('desc',$this->request->data)){
						 $desciption = $this->request->data['desc'];
					}
					$created  = date("Y-m-d G:i:s");
						 //pr($paymentData);die;
						 if($paymentData['payment_method'] == "On Credit"){   // changing created when changing payment method from  on-credit to any other
							 $paymentDetailData = array(
									 'id' => $paymentId,
									 'payment_method' => $paymentMode,
									 'payment_status' => $paymentStatus,
								     'description' => $desciption,
									// 'created' => $created,
									// 'credit_cleared' => $credit_cleared,
									 'created' => $date_box_date,
										);
						 }
						 
						 if($paymentMode == "On Credit"){  //when changed payment method is On Credit add recit date to payment table created
							 $paymentDetailData = array(
									 'id' => $paymentId,
									 'payment_method' => $paymentMode,
									 'payment_status' => $paymentStatus,
									 'description' => $desciption,
									 'created' => $recit_created,
									// 'credit_cleared' => $credit_cleared,
										);
						 }
						 
						 if(empty($paymentDetailData)){
							  
							 $paymentDetailData = array(
									 'id' => $paymentId,
									 'payment_method' => $paymentMode,
									 'payment_status' => $paymentStatus,
									 'description' => $desciption,
									// 'credit_cleared' => $credit_cleared,
									 'created' => $date_box_date,
										);
						 }
						// pr($paymentDetailData);die;
			}else{
			   $credit_cleared = 0;
			   if($old_payment_method == "On Credit"){
					if($paymentMode != "On Credit"){
						 $credit_cleared = 1;
					}
			   }
			   if(array_key_exists('added_amount',$this->request->data) && is_numeric($this->request->data['added_amount']) && $this->request->data['added_amount'] > 0){
					if($this->request->data['sale_amount'] != $this->request->data['old_amt'] + $this->request->data['added_amount']){
						 $this->Flash->error('Payment could not be updated!');
						 return $this->redirect(array('action' => 'update_payment',$paymentId));
						 die;
					 }
					 
					 $new_paymentMode = $this->request->data['new_change_mode'];
					 $new_added_amount = $this->request->data['added_amount'];
					if($new_paymentMode=="Cheque"||
						 $new_paymentMode=="Cash"||
						 $new_paymentMode=="Bank Transfer"||
						 $new_paymentMode=="Card"
						 ){
						  $paymentStatus = 1;
					}elseif($new_paymentMode=="On Credit"){
						 $paymentStatus = 0;
					}
					$new_box_desc = "";
					if(array_key_exists('new_box_desc',$this->request->data)){
						 $new_box_desc = $this->request->data['new_box_desc'];
					}
					
					$new_credit_cleared = 0;
					if($new_paymentMode != "On Credit"){
						 $new_credit_cleared = 1;
					}
					
					$paymentDetailData = array(
							 'payment_method' => $new_paymentMode,
							 'payment_status' => $paymentStatus,
							 'credit_receipt_id' => $paymentData['credit_receipt_id'],
							 'kiosk_id' =>$kiosk_id,
							 'amount' => $new_added_amount,
							 'description' => $new_box_desc,
							 //'credit_cleared' => $new_credit_cleared,
								 );
					$CreditPaymentDetailTable->behaviors()->load('Timestamp');
					$credit_entity = $CreditPaymentDetailTable->newEntity();
					$credit_entity = $CreditPaymentDetailTable->patchEntity($credit_entity,$paymentDetailData,['validate'=>false]);
					$CreditPaymentDetailTable->save($credit_entity);
					
					$desciption = "";
					if(array_key_exists('desc',$this->request->data)){
						 $desciption = $this->request->data['desc'];
					}
					
					
					$old_amount = $this->request->data['old_amt']; 
					$paymentDetailData = array(
								'id' => $paymentId,
								'payment_method' => $paymentMode,
								'amount' => $old_amount,
								'payment_status' => $paymentStatus,
								'description' => $desciption,
								//'credit_cleared' => $credit_cleared,
									);
					$CreditPaymentDetailTable->behaviors()->load('Timestamp');
					$get_id = $CreditPaymentDetailTable->get($paymentId);
					$patch_entity = $CreditPaymentDetailTable->patchEntity($get_id,$paymentDetailData,['validate'=>false]);
					
					$CreditPaymentDetailTable->save($patch_entity);
			   }
			   
			   $desciption = "";
					if(array_key_exists('desc',$this->request->data)){
						 $desciption = $this->request->data['desc'];
					}
					
					
			   if($paymentMode == "On Credit"){  //when changed payment method is On Credit add recit date to payment table created
					$paymentDetailData = array(
							'id' => $paymentId,
							'payment_method' => $paymentMode,
							'payment_status' => $paymentStatus,
							'created' => $recit_created,
							'description' => $desciption,
							//'credit_cleared' => $credit_cleared,
							   );
				}
				if(empty($paymentDetailData)){
					$paymentDetailData = array(
							'id' => $paymentId,
							'payment_method' => $paymentMode,
							'description' => $desciption,
							'payment_status' => $paymentStatus,
							//'credit_cleared' => $credit_cleared,
							   );
				}
			}
		 
			   $pay_det = $CreditPaymentDetailTable->get($paymentId);
			
			//pr($paymentDetailData);die;
			$payment_detaildata = $CreditPaymentDetailTable->patchEntity($pay_det,$paymentDetailData);
			if($CreditPaymentDetailTable->save($payment_detaildata)){
				if($this->request->session()->read('Auth.User.group_id') == ADMINISTRATORS || $this->request->session()->read('Auth.User.group_id') == FRANCHISE_OWNER){
					$kiosk_id_to_set = $this->get_kiosk_for_credit("credit_search");
					
					//echo $kiosk_id_to_set;die;
					$this->Flash->success("Payment method has been updated");
					return $this->redirect(array('action'=>"search?kiosk_id=$kiosk_id_to_set&payment_type=All&invoice_detail=receipt_number"));
				}else{
				    $this->Flash->success("Payment method has been updated");
				    return $this->redirect(array('action'=>'view_credit_note'));
				}
			}else{
			   //pr($payment_detaildata->errors());die;
			}
		}
		
		$this->set(compact('paymentData'));
	}
	
	
	public function creditNote($customerId = ''){
		  $kiosk_id = $this->request->Session()->read('kiosk_id');
		  if(!empty($kiosk_id)){
			   $productSource = "kiosk_{$kiosk_id}_products";
		  }else{
			   $productSource = "products";
		  }
		  $productTable = TableRegistry::get($productSource,[
																	  'table' => $productSource,
																  ]);
        $CURRENCY_TYPE = Configure::read('CURRENCY_TYPE');	 
		$currencySymbol = $this->setting['currency_symbol'];
		$customerAccountDetails_query = $this->Customers->find('all',array(
									'conditions'=>array('Customers.id'=>$customerId)
									)
								);
		$customerAccountDetails_query = $customerAccountDetails_query->hydrate(false);
        if(!empty($customerAccountDetails_query)){
            $customerAccountDetails = $customerAccountDetails_query->first();
        }else{
            $customerAccountDetails = array();
        }
		$country = $customerAccountDetails['country'];
		$this->paginate = [
						'limit' => 20,
						'model' => 'Product',
						'order' => ['product' => 'ASC'],
						'recursive' => -1
					];
		
		$products_query = $this->paginate($productTable);
		$products = $products_query->toArray();
		$product_code = array();
		foreach($products as $key => $value){
				$product_code[$value->id] = $value->product_code;
		}
		
		//-----------------------------------------
		$categories = $this->Categories->find('all',array(
								'fields' => array('id', 'category','id_name_path'),
                                                                'conditions' => array('Categories.status' => 1),
								'order' => 'Categories.category asc',
								'recursive' => -1
								));
		$categories = $this->CustomOptions->category_options($categories,true);
		
		//-----------------------------------------
		$session_basket = $this->request->Session()->read('Basket');
		$bulkDiscountPercentage = 0;
		$bulkDiscountPercentage = $this->request->Session()->read('bulk_discount');
		$special_invoice = $this->request->Session()->read('special_invoice');
		if(is_array($session_basket)){
			$basketStr = "";
			$vatAmount = $counter = $totalBillingAmount = $totalDiscountAmount = 0;
			//	pr($session_basket);die;
			$productIDs = array_keys($session_basket);
            if(empty($productIDs)){
                $productIDs =  array(0 => null);
            }
			$productCodes_query = $productTable->find('all', array(
											  'conditions' => array('id IN' => $productIDs),
											  'fields' => array('id', 'product_code')
											  ));
			
			$productCodes_query = $productCodes_query->hydrate(false);
            if(!empty($productCodes_query)){
                $productCodes = $productCodes_query->toArray();
            }else{
                $productCodes = array();
            }
			$product_code = array();
			foreach($productCodes as $key => $value){
				$product_code[$value['id']] = $value['product_code'];
			}
			$sub_total = 0;
			foreach($session_basket as $key => $basketItem){
				//pr($session_basket);die;
				if($key == 'error')continue;
				$counter++;
				$vat = $this->VAT;
				$vatItem = $vat/100;
				$discount = $basketItem['discount'];				
				$sellingPrice = $basketItem['selling_price'];
				$net_amount = $basketItem['net_amount']; //newly added
				$price_without_vat = $basketItem['price_without_vat']; //newly added
				$netAmount = $basketItem['net_amount']; //newly added
				$refundType = $basketItem['type'];
				$itemPrice = $basketItem['selling_price']/(1+$vatItem);
				//$discountAmount = $sellingPrice*$basketItem['discount']/100* $basketItem['quantity'];
				$discountAmount = $price_without_vat*$basketItem['discount']/100* $basketItem['quantity'];
				$totalDiscountAmount+= $discountAmount;
				//$totalItemPrice = $basketItem['selling_price'] * $basketItem['quantity'];
				$totalItemPrice = $price_without_vat * $basketItem['quantity'];
				$bulkDiscountPercentage = $bulkDiscountPercentage;
				//$bulkDiscountPercentage instead of $sessionBulkDiscount; in kioskproduct sales controller
				//$pricebeforeVat = $itemPrice*$basketItem['quantity']-$discountAmount;
				$totalItemCost = $totalItemPrice-$discountAmount;
				$totalBillingAmount+=$totalItemCost;
				$vatperitem = $basketItem['quantity']*($sellingPrice-$itemPrice);
				$vatAmount+= $vatperitem;
            $bulkDiscountValue = (float)$totalBillingAmount * (float)$bulkDiscountPercentage / 100;
				$netBillingAmount = $totalBillingAmount-$bulkDiscountValue;
				$netPrice = $netBillingAmount;//newly added
				$vatAmount = $netBillingAmount*$vatItem;//newly added
				if($country == "OTH"){
					$finalAmount  = $netBillingAmount;
				}else{
					if($special_invoice == 1){
						$finalAmount = $netBillingAmount;//newly added. This can be conditional
					}else{
						$finalAmount = $netBillingAmount+$vatAmount;//newly added. This can be conditional
					}
				}
                
                $single_product_value = 0;
				$single_product_value = $totalItemCost/$basketItem['quantity'];
                
				$code = "";
				if(array_key_exists($key,$product_code)){
						$code = $product_code[$key];
				}
				
				$sub_total = $sub_total + $totalItemCost; 
				
				$basketStr.="<tr>
						<td>{$counter})</td>
						<td>{$code}</td>
						<td>".$basketItem['product']."</td>
						<td>".$refundType."</td>
						<td>".$basketItem['quantity']."</td>
						<td> ".$CURRENCY_TYPE.number_format($price_without_vat,2)."</td>
						<!--td> ".$vat."</td-->
						<!--td> ".$CURRENCY_TYPE.number_format($itemPrice, 2)."</td--><!--commented on Jul 17, 2016 -->
						<td> ".$single_product_value."</td>
						<!--td> ".$CURRENCY_TYPE.number_format($vatperitem,2)."</td--><!--commented on Jul 17, 2016 -->
						<td> ".$CURRENCY_TYPE.number_format($discountAmount,2)."</td>
						<td> ".$CURRENCY_TYPE.number_format($totalItemCost,2)."</td></tr>";
			}
			$cust_id = $this->request->params['pass'][0];
			$cust_res_query = $this->Customers->find('all',array('fields' => array('id','country'),'conditions' => array(
																	'id' => $cust_id
																   )
															));
			$cust_res_query = $cust_res_query->hydrate(false);
			if(!empty($cust_res_query)){
			   $cust_res = $cust_res_query->first();
			}else{
			   $cust_res = array();
			}
			if(!empty($cust_res)){
				$country = $cust_res['country'];
			}
			if(!empty($basketStr)){
				if($country == "OTH"){
					$basketStr = "<table><tr>
							<th style='width: 10px;'>Sr No</th>
							<th style='width: 128px;'>Product Code</th>
							<th style='width:455px;'>Product</th>
							<th style='width: 65px;'>Type</th>
							<th style='width: 30px;'>Qty</th>
							<th style='width: 105px;'>Sale Price</th>
							<!--th>Vat %</th-->
							<!--th>Net Price/Item</th--><!-- commented on Jul 17, 2016--> 
							<th style='width: 77px;'>Disct Price</th>
							<!--th>Vat Value</th--><!-- commented on Jul 17, 2016--> 
							<th style='width: 76px;'>Disct Value</th>
							<th style='width: 49px;'>Amount</th>
							</tr>".$basketStr."
							<tr><td colspan='8'>Sub Total</td><td>".$CURRENCY_TYPE.number_format($sub_total,2)."</td></tr>
							<tr><td colspan='8'>Bulk Discount ({$bulkDiscountPercentage}%)</td><td> ".$CURRENCY_TYPE.number_format($bulkDiscountValue,2)."</td></tr>
							<tr><td colspan='8'>Sub Total(After bulk discount)</td><td>".$CURRENCY_TYPE.number_format($netBillingAmount,2)."</td></tr>
							<tr><td colspan='8'>Total Amount</td><td>".$CURRENCY_TYPE.number_format($finalAmount,2)."</td></tr></table>";		
				}else{
					if($special_invoice == 1){
						$basketStr = "<table><tr>
							<th style='width: 10px;'>Sr No</th>
							<th style='width: 128px;'>Product Code</th>
							<th style='width:455px;'>Product</th>
							<th style='width: 65px;'>Type</th>
							<th style='width: 30px;'>Qty</th>
							<th style='width: 105px;'>Sale Price</th>
							<!--th>Vat %</th-->
							<!--th>Net Price/Item</th--><!-- commented on Jul 17, 2016--> 
							<th style='width: 77px;'>Disct Price</th>
							<!--th>Vat Value</th--><!-- commented on Jul 17, 2016--> 
							<th style='width: 76px;'>Disct Value</th>
							<th style='width: 49px;'>Amount</th>
							</tr>".$basketStr."
							<tr><td colspan='8'>Sub Total</td><td>".$CURRENCY_TYPE.number_format($sub_total,2)."</td></tr>
							<tr><td colspan='8'>Bulk Discount ({$bulkDiscountPercentage}%)</td><td> ".$CURRENCY_TYPE.number_format($bulkDiscountValue,2)."</td></tr>
							<tr><td colspan='8'>Sub Total(After bulk discount)</td><td>".$CURRENCY_TYPE.number_format($netBillingAmount,2)."</td></tr>
							<tr><td colspan='8'>Total Amount</td><td>".$CURRENCY_TYPE.number_format($finalAmount,2)."</td></tr></table>";	
					}else{
						$basketStr = "<table><tr>
									<th style='width: 10px;'>Sr No</th>
									<th style='width: 128px;'>Product Code</th>
									<th style='width:455px;'>Product</th>
									<th style='width: 65px;'>Type</th>
									<th style='width: 30px;'>Qty</th>
									<th style='width: 105px;'>Sale Price</th>
									<!--th>Vat %</th-->
									<!--th>Net Price/Item</th--><!-- commented on Jul 17, 2016--> 
									<th style='width: 77px;'>Disct Price</th>
									<!--th>Vat Value</th--><!-- commented on Jul 17, 2016--> 
									<th style='width: 76px;'>Disct Value</th>
									<th style='width: 49px;'>Amount</th>
									</tr>".$basketStr."
									<tr><td colspan='8'>Sub Total</td><td>".$CURRENCY_TYPE.number_format($sub_total,2)."</td></tr>
									<tr><td colspan='8'>Bulk Discount ({$bulkDiscountPercentage}%)</td><td> ".$CURRENCY_TYPE.number_format($bulkDiscountValue,2)."</td></tr>
									<tr><td colspan='8'>Sub Total(After bulk discount)</td><td>".$CURRENCY_TYPE.number_format($netBillingAmount,2)."</td></tr>
									<tr><td colspan='8'>Vat</td><td> ".$CURRENCY_TYPE.number_format($vatAmount,2)."</td></tr>
									<tr><td colspan='8'>Net Amount</td><td>".$CURRENCY_TYPE.number_format($totalBillingAmount,2)."</td></tr>
									<tr><td colspan='8'>Total Amount</td><td>".$CURRENCY_TYPE.number_format($finalAmount,2)."</td></tr></table>";
					}
				}
					
				$productCounts = count($this->request->Session()->read('Basket'));
			
				if($productCounts){
					//$productCounts product(s) added to the cart.
					$flashMessage = "Total item Count:$productCounts.<br/>$basketStr";
				}else{
					$flashMessage = "No item added to the cart. Item Count:$productCounts";
				}
				
				if(array_key_exists('error',$session_basket)){
					$flashMessage = $session_basket['error']."<br/>".$flashMessage;
				}
				$this->Flash->success($flashMessage,array('escape' => false));
			}
		}
		//-----------------------------------------
		$vat = $this->VAT;
		$this->set(compact('categories','customerAccountDetails','products','vat'));
	}
	
	public function searchCreditNote($customerId = '', $keyword = ""){
        $kiosk_id = $this->request->Session()->read('kiosk_id');
		if($kiosk_id < 10000 && $kiosk_id != 0 && !empty($kiosk_id)){
			$productTable_source = "kiosk_{$kiosk_id}_products";
            
            
			//$this->Product->setSource("kiosk_{$kiosk_id}_products");
		}else{
			$productTable_source = "products";
		}
        
        $productTable = TableRegistry::get($productTable_source,[
                                                                            'table' => $productTable_source,
                                                                        ]);
		$customerAccountDetails_query = $this->Customers->find('all',array(
									'conditions'=>array('Customers.id'=>$customerId)
									)
								);
		$customerAccountDetails_query = $customerAccountDetails_query->hydrate(false);
        if(!empty($customerAccountDetails_query)){
            $customerAccountDetails = $customerAccountDetails_query->first();
        }else{
            $customerAccountDetails = array();
        }
		$searchKW = $this->request->query['search_kw'];		
		$categories_query = $this->Categories->find('all',array(
								'fields' => array('id', 'category','id_name_path'),
                                                                'conditions' => array('Categories.status' => 1),
								'order' => 'Categories.category asc'
								));
        $categories_query = $categories_query->hydrate(false);
        if(!empty($categories_query)){
            $categories  = $categories_query->toArray();
        }else{
            $categories = array();
        }
		$conditionArr = array();
		//----------------------
		if(!empty($searchKW)){
			$conditionArr['OR']['LOWER(product) like '] =  strtolower("%$searchKW%");
			$conditionArr['OR']['LOWER(product_code) like '] =  strtolower("%$searchKW%");
			$conditionArr['OR']['LOWER(description) like '] =  strtolower("%$searchKW%");
			//'NOT'=>array('Product.quantity'=>0)
		}
		
		//----------------------
		if(array_key_exists('category',$this->request->query)){
			$category = $this->request->query['category'];
			if(isset($category)){
				$conditionArr['category_id IN'] =  $category;
			}
		}
		$this->paginate = [
						'conditions' => $conditionArr,
						'limit' => 20
					];
		$categories = $this->CustomOptions->category_options($categories,true);
		$products_query = $this->paginate($productTable);
		$products = $products_query->toArray();
		
		$this->set(compact('products','categories','customerAccountDetails'));
		//$this->layout = 'default'; 
		//$this->viewPath = 'Products';
		$vat = $this->VAT;
		$this->set(compact('vat'));
		$this->render('credit_note');
		
	}
	
	public function generateCreditNote($customerId = ''){
        $CURRENCY_TYPE = Configure::read('CURRENCY_TYPE');
        $kiosk_id = $this->request->Session()->read('kiosk_id');
		if(!empty($kiosk_id)){
			$productSource = "kiosk_{$kiosk_id}_products";
			$CreditReceiptSource = "kiosk_{$kiosk_id}_credit_receipts";
			$CreditProductDetailSource = "kiosk_{$kiosk_id}_credit_product_details";
			$CreditPaymentDetailSource = "kiosk_{$kiosk_id}_credit_payment_details";
		}else{
            $productSource = "products";
			$CreditReceiptSource = "credit_receipts";
			$CreditProductDetailSource = "credit_product_details";
			$CreditPaymentDetailSource = "credit_payment_details";
        }
        $productTable = TableRegistry::get($productSource,[
                                                            'table' => $productSource,
                                                        ]);
        $CreditReceiptTable = TableRegistry::get($CreditReceiptSource,[
                                                                            'table' => $CreditReceiptSource,
                                                                        ]);
        $CreditProductDetailTable = TableRegistry::get($CreditProductDetailSource,[
                                                                                    'table' => $CreditProductDetailSource,
                                                                                ]);
        $CreditPaymentDetailTable = TableRegistry::get($CreditPaymentDetailSource,[
                                                                                    'table' => $CreditPaymentDetailSource,
                                                                                ]);
		$vat = $this->VAT;
		$customerAccountDetails_query = $this->Customers->find('all',array(
									'conditions'=>array('Customers.id'=>$customerId)
									)
								);
		
		$customerAccountDetails_query = $customerAccountDetails_query->hydrate(false);
        if(!empty($customerAccountDetails_query)){
            $customerAccountDetails = $customerAccountDetails_query->first();
        }else{
            $customerAccountDetails = array();
        }
		//pr($customerAccountDetails['Customer']);die;
		$firstName = $customerAccountDetails['fname'];
		$lastName = $customerAccountDetails['lname'];
		$emailId = $customerAccountDetails['email'];
		$mobileNum = $customerAccountDetails['mobile'];
		$address1 = $customerAccountDetails['del_address_1'];
		$address2 = $customerAccountDetails['del_address_2'];
		$del_city = $customerAccountDetails['del_city'];
		$del_state = $customerAccountDetails['del_state'];
		$country = $customerAccountDetails['country'];
		$del_zip = $customerAccountDetails['del_zip'];
		$delCity = $customerAccountDetails['del_city'];
		$agent_id = $customerAccountDetails['agent_id'];
		
		//----------Kiosk database tables--------------------
		//$receiptTable = "product_receipts";
		//$salesTable = "kiosk_product_sales";
		//$productTable = "products";
		//----------Kiosk database tables--------------------
		
		
		$user_id = $this->Auth->user('id');	//rasa
		//$this->initialize_tables($kiosk_id);
		$current_page = "";
		if(array_key_exists('current_page',$this->request['data'])){
			$current_page = $this->request['data']['current_page'];		
		}
		//if(!isset($current_page)){$this->redirect(array('action' => "credit_note"));}		
		$productCounts = 0;
		$session_basket = $this->request->Session()->read('Basket');
		//--------------------------
		if(array_key_exists('basket',$this->request['data'])){
            
			//pr($this->request);die;
			if(array_key_exists('receipt_required',$this->request['data'])){
				$receipt_required = $this->request['data']['receipt_required'];
				$this->request->Session()->write('receipt_required',$receipt_required);
				$sessionReceiptRequired = $this->request->Session()->read('receipt_required');
			}
			
			if(array_key_exists('special_invoice',$this->request['data'])){
				$special_invoice = $this->request['data']['special_invoice'];
				$this->request->Session()->write('special_invoice',$special_invoice);
				$session_special_invoice = $this->request->Session()->read('special_invoice');
			}
			
			if(array_key_exists('bulk_discount',$this->request['data'])){
				$bulk_discount = $this->request['data']['bulk_discount'];
				$this->request->Session()->write('bulk_discount',$bulk_discount);
				$sessionBulkDiscount = $this->request->Session()->read('bulk_discount');
			}
			
			if($this->request['data']['bulk_discount'] > 100){
					$flashMessage = "Bulk discount percentage must be less than 100";
					$this->Flash->error($flashMessage);
					$this->redirect(array('action' => "credit_note/$customerId/page:$current_page"));
					die;
			}elseif($this->request['data']['bulk_discount'] < 0){
				$flashMessage = "Bulk discount percentage must be a positive number";
				$this->Flash->error($flashMessage);
				$this->redirect(array('action' => "credit_note/$customerId/page:$current_page"));
				die;
			}
            
			//die;
			//pr($this->request);die;
			$productArr = array();
			foreach($this->request['data']['CreditProductDetail']['item'] as $key => $item){				
				if((int)$item){
					//pr($this->request['data']['CreditProductDetail']);
					$discount = $this->request['data']['CreditProductDetail']['discount'][$key];					
					$price = $this->request['data']['CreditProductDetail']['selling_price'][$key];
					$discountStatus = $this->request['data']['CreditProductDetail']['discount_status'][$key];
					$currentQuantity = $this->request['data']['CreditProductDetail']['p_quantity'][$key];
					$productID = $this->request['data']['CreditProductDetail']['product_id'][$key];
					$remarks = $this->request['data']['CreditProductDetail']['remarks'][$key];
					$productTitle = $this->request['data']['CreditProductDetail']['product'][$key];
					$quantity = $this->request['data']['CreditProductDetail']['quantity'][$key];
					$netAmount = $this->request['data']['KioskProductSale']['net_amount'][$key];
					$priceWithoutVat = $this->request['data']['KioskProductSale']['price_without_vat'][$key];
					$minPrice = $this->request['data']['CreditProductDetail']['minimum_discount'][$key];
					if(empty($netAmount)){$netAmount = $priceWithoutVat;}
					$type = $this->request['data']['CreditProductDetail']['type'][$key];
					$priceCheck_query = $productTable->find('all',array(
																		'conditions' => array('id' => $productID),
																		'fields' => array('selling_price','product')
																	)
														);
					$priceCheck_query = $priceCheck_query->hydrate(false);
                    if(!empty($priceCheck_query)){
                        $priceCheck = $priceCheck_query->first();
                    }else{
                        $priceCheck = array();
                    }
					$originalPrice = $priceCheck['selling_price'];echo "</br>";
				    $discountValue = $originalPrice * $discount/100;echo "</br>";
				    $minPrice = round($originalPrice - $discountValue, 2);echo "</br>";
					//e $minPrice;echo "</br>";die;
					//----------------------------------
					if(array_key_exists('minimum_discount', $this->request['data']['CreditProductDetail'])){
						$minPrice = $this->request['data']['CreditProductDetail']['minimum_discount'][$key];
					}
					//----------------------------------
					
					if($netAmount != $priceWithoutVat && $netAmount < $minPrice){
						//echo "$netAmount != $priceWithoutVat && $netAmount < $minPrice";die;
                        if($netAmount < 0){
                            $flashMessage = "Selling price cannot be less than the minimum allowed price";
                            $this->Flash->error($flashMessage);
                            return $this->redirect(array('action' => "credit_note/$customerId/page:$current_page"));
                            die;
                        }
					}
					///echo "out $netAmount != $priceWithoutVat && $netAmount < $minPrice";die;
					if($netAmount >= $priceWithoutVat){
						$price = $netAmount + $netAmount*($vat/100);
						$priceWithoutVat = $netAmount;
					}
					$productArr[$productID] = array(
													'quantity' => $quantity,
													'selling_price' => $price,
													'net_amount' => $netAmount,//new added
													'price_without_vat' => $priceWithoutVat, //new added
													'remarks' => $remarks,
													'product' => $productTitle,
													'discount' => $discount,
													'discount_status' => $discountStatus,
													'receipt_required' => $this->request['data']['receipt_required'],
													'bulk_discount' => $this->request['data']['bulk_discount'],
													'type' => $type
												);
					$productCounts++;
				}			
			}
            
			//pr($productArr);die;
			$session_basket = $this->request->Session()->read('Basket');
			
			if(count($session_basket) >= 1){
				//pr($this->request['data']);die('if');;
				//adding item to the the existing session
				$sum_total = $this->add_arrays(array($productArr,$session_basket));
				$this->request->Session()->write('Basket', $sum_total);
				$session_basket = $this->request->Session()->read('Basket');				
			}else{
				//pr($this->request['data']);pr($productArr);die('else');;
				//adding item first time to session
				if(count($productCounts))$this->request->Session()->write('Basket', $productArr);
			}
			//pr($_SESSION['Basket']);die;
			$currencySymbol = $this->setting['currency_symbol'];
			$basketStr = "";
			$counter = 0;
			$totalBillingAmount = 0;
			$totalDiscountAmount = 0;
			$vatAmount = 0;
			
			if(is_array($session_basket)){
				foreach($session_basket as $key => $basketItem){
					//pr($basketItem);
					$counter++;
					$vat = $this->VAT;
					$vatItem = $vat/100;
					$discount = $basketItem['discount'];				
					$sellingPrice = $basketItem['selling_price'];
					$net_amount = $basketItem['net_amount']; //newly added
					//if($net_amount > $sellingPrice){
					//	$sellingPrice = $net_amount;
					//}
					$refundType = $basketItem['type'];
					$itemPrice = $sellingPrice/(1+$vatItem);  //$basketItem['selling_price']
					$discountAmount = $sellingPrice*$basketItem['discount']/100* $basketItem['quantity'];
					$totalDiscountAmount+= $discountAmount;
					$totalItemPrice = $sellingPrice * $basketItem['quantity'];				//$basketItem['selling_price']
					$totalItemCost = $totalItemPrice-$discountAmount;
					$totalBillingAmount+=$totalItemCost;
					$vatperitem = $basketItem['quantity']*($sellingPrice-$itemPrice);
					$vatAmount+= $vatperitem;
					$bulkDiscountPercentage = $sessionBulkDiscount;
					$bulkDiscountValue = $totalBillingAmount*$bulkDiscountPercentage/100;
					$netBillingAmount = $totalBillingAmount-$bulkDiscountValue;
					$basketStr.="<tr>
							<td>{$counter})</td>
							<td>{$key}</td><!-- Product code -->
							<td>".$basketItem['product']."</td><!-- Product Title -->
							<td>".$basketItem['product']."</td><!-- Product Type -->
							<td>".$refundType."</td>
							<td> ".$CURRENCY_TYPE.number_format($sellingPrice, 2)."</td>
							<td> ".$vat."</td>
							<td> ".$CURRENCY_TYPE.number_format($itemPrice, 2)."</td>
							<td> ".$discount."</td>
							<td> ".$CURRENCY_TYPE.number_format($vatperitem,2)."</td>
							<td> ".$CURRENCY_TYPE.number_format($discountAmount,2)."</td>
							<td> ".$CURRENCY_TYPE.number_format($totalItemCost,2)."</td></tr>";
				}
			}
			if(!empty($basketStr)){
				$basketStr = "<table><tr>
							<th style='width: 10px;'>Sr No</th>
							<th style='width: 128px;'>Product Id</th>
							<th style='width:455px;'>Product</th>
							<th style='width: 65px;'>Type</th>
							<th style='width: 30px;'>Qty</th>
							<th style='width: 105px;'>Price/Item</th>
							<th style='width: 10px;'>Vat %</th>
							<th style='width: 10px;'>Net Price/Item</th>
							<th style='width: 77px;'>Disct %</th>
							<th style='width: 10px;'>Vat Value</th>
							<th style='width: 76px;'>Disct Value</th>
							<th style='width: 49px;'>Gross</th>
							</tr>".$basketStr."
							<tr><td colspan='11'>Total Vat</td><td> ".$CURRENCY_TYPE.number_format($vatAmount,2)."</td></tr>
							<tr><td colspan='11'>Total Discount</td><td> ".$CURRENCY_TYPE.number_format($totalDiscountAmount,2)."</td></tr>
							<tr><td colspan='11'>Bulk Discount ({$bulkDiscountPercentage}%)</td><td>".$CURRENCY_TYPE.number_format($bulkDiscountValue,2)."</td></tr>
							<tr><td colspan='11'>Net Amount</td><td>".$CURRENCY_TYPE.number_format($totalBillingAmount,2)."</td></tr>
							<tr><td colspan='11'>Total Amount</td><td> ".$CURRENCY_TYPE.number_format($netBillingAmount,2)."</td></tr></table>";
							//pr($basketStr);
			}
			
			//die;
		
			$totalItems = count($this->request->Session()->read('Basket'));
			
			if($productCounts){
				//$productCounts product(s) added to the cart.
				//$flashMessage = "Total item Count(rasu):$totalItems.<br/>$basketStr";
			}else{
				$flashMessage = "No item added to the cart. Item Count:$productCounts";
                $this->Flash->success($flashMessage,array('escape' => false));
			}
			
			
			return $this->redirect(array('action' => "credit_note/$customerId/page:$current_page"));
		
		}elseif(array_key_exists('check_out',$this->request['data'])){
			$this->set(compact('customerId'));
			return $this->redirect(array('action'=>'credit_note_checkout',$customerId));
		}elseif(array_key_exists('empty_basket',$this->request['data'])){
			$this->request->Session()->delete('Basket');
			$this->request->Session()->delete('receipt_required');
			$this->request->Session()->delete('bulk_discount');
			$flashMessage = "Basket is empty; Add new items to cart!";
			$this->Flash->success($flashMessage);
			return $this->redirect(array('action' => "credit_note/$customerId/page:$current_page"));			
		}elseif(array_key_exists('calculate',$this->request['data'])){
			return $this->redirect(array('action' => "credit_note/$customerId/page:$current_page"));
		}else{
			if(array_key_exists('receipt_required',$this->request['data'])){
				$receipt_required = $this->request['data']['receipt_required'];
				$this->request->Session()->write('receipt_required',$receipt_required);
				$sessionReceiptRequired = $this->request->Session()->read('receipt_required');
			}
			
			if(array_key_exists('special_invoice',$this->request['data'])){
				$special_invoice = $this->request['data']['special_invoice'];
				$this->request->Session()->write('special_invoice',$special_invoice);
				$session_special_invoice = $this->request->Session()->read('special_invoice');
			}else{
				$session_special_invoice = 0;
			}
			if($session_special_invoice == 1){
			   $CreditReceiptSource = "t_credit_receipts";
			   $CreditReceiptTable = TableRegistry::get($CreditReceiptSource,[
																				'table' => $CreditReceiptSource,
																			]);			   
			   //$this->CreditReceipt->setSource("t_credit_receipts");
			}
			
			$productArr = array();
			//---------------------Step 1 code -------------------------------			
			//$customer = $this->request['data']['customer'];
			$receiptData = array(
						'customer_id' => $customerId,
						'agent_id' => $agent_id,
						'address_1' => $address1,
						'address_2' => $address2,
						'city' => $del_city,
						'state' => $del_state,
						'zip' => $del_zip,
						'processed_by' => $user_id,
						'fname' => $firstName,
						'lname' => $lastName,
						'mobile' => $mobileNum,
						'email' => $emailId
					     );
			if($session_special_invoice == 1){
				$kiosk_id = $this->request->Session()->read('kiosk_id');
				if(!empty($kiosk_id)){
					$receiptData['kiosk_id'] = $kiosk_id;
				}
				
			}
			$CreditReceiptTable->behaviors()->load('Timestamp');
            $CreditReceiptsEntity = $CreditReceiptTable->newEntity();
			$CreditReceiptsEntity = $CreditReceiptTable->patchEntity($CreditReceiptsEntity,$receiptData);
			$CreditReceiptTable->save($CreditReceiptsEntity);
			//---------------------Step 1 code -------------------------------
			
			//---------------------Step 2 code -------------------------------
			$receiptId = $CreditReceiptsEntity->id;
			$session_basket = $this->request->Session()->read('Basket');
			// NORMAL SUBMIT CASE OTHER THAN BASKET
			$sessionBulkDiscount = 0;
			
			if(array_key_exists('bulk_discount',$this->request->Session()->read())){
					$sessionBulkDiscount = $this->request->Session()->read('bulk_discount');
			}
			//pr($this->request);die;
			
			if(array_key_exists('CreditProductDetail',$this->request['data']))
			foreach($this->request['data']['CreditProductDetail']['item'] as $key => $item){
				$bulkDiscountPercentage = 0;
				if((int)$item){
					$currentQuantity = $this->request['data']['CreditProductDetail']['p_quantity'][$key];
					$productID = $this->request['data']['CreditProductDetail']['product_id'][$key];
					$remarks = $this->request['data']['CreditProductDetail']['remarks'][$key];
					$productTitle = $this->request['data']['CreditProductDetail']['product'][$key];
					$discount = $this->request['data']['CreditProductDetail']['discount'][$key];
					$selling_price = $this->request['data']['CreditProductDetail']['selling_price'][$key];
					$net_amount = $this->request['data']['KioskProductSale']['net_amount'][$key]; //newly added
					$discountStatus = $this->request['data']['CreditProductDetail']['discount_status'][$key];
					$quantity = $this->request['data']['CreditProductDetail']['quantity'][$key];
					$type = $this->request['data']['CreditProductDetail']['type'][$key];
					$price_without_vat1 = $this->request['data']['KioskProductSale']['price_without_vat'][$key];
					$bulkDiscountPercentage = $this->request['data']['bulk_discount'];
					
					if($net_amount >= $price_without_vat1){
						$price_without_vat1 = $net_amount;
						$selling_price = $net_amount + $net_amount*($vat/100);
					}
				}
				
				//pr($bulkDiscountPercentage);die;
				if($bulkDiscountPercentage>0){
					if($bulkDiscountPercentage>100){
						$flashMessage = "Bulk discount percentage must be less than 100";
						$this->Flash->error($flashMessage);
						$this->redirect(array('action' => "credit_note/$customerId/page:$current_page"));
						if($session_special_invoice == 1){
							  $query = "DELETE FROM `t_credit_receipts` WHERE `t_credit_receipts.id` = '$receiptId'";
							  $conn = ConnectionManager::get('default');
							  $stmt = $conn->execute($query); 
							//$this->CreditReceipt->query("DELETE FROM `t_credit_receipts` WHERE `t_credit_receipts.id` = '$receiptId'");
						}else{
						 $query = "DELETE FROM `credit_receipts` WHERE `credit_receipts.id` = '$receiptId'";
						 $conn = ConnectionManager::get('default');
						 $stmt = $conn->execute($query); 
							//$this->CreditReceipt->query("DELETE FROM `credit_receipts` WHERE `credit_receipts.id` = '$receiptId'");
						}
						die;
					}elseif($bulkDiscountPercentage<0){
						$flashMessage = "Bulk discount percentage must be a positive number";
						$this->Flash->error($flashMessage);
						$this->redirect(array('action' => "credit_note/$customerId/page:$current_page"));
						if($session_special_invoice == 1){
							  $query = "DELETE FROM `t_credit_receipts` WHERE `t_credit_receipts.id` = '$receiptId'";
							  $conn = ConnectionManager::get('default');
							  $stmt = $conn->execute($query); 
							//$this->CreditReceipt->query("DELETE FROM `t_credit_receipts` WHERE `t_credit_receipts.id` = '$receiptId'");
						}else{
						 $query = "DELETE FROM `credit_receipts` WHERE `credit_receipts.id` = '$receiptId'";
						 $conn = ConnectionManager::get('default');
						 $stmt = $conn->execute($query); 
							//$this->CreditReceipt->query("DELETE FROM `credit_receipts` WHERE `credit_receipts.id` = '$receiptId'");
						}
						
						die;
					}
				}
				
				if(array_key_exists('bulk_discount',$this->request['data'])){
					$bulk_discount = $this->request['data']['bulk_discount'];
					$this->request->Session()->write('bulk_discount',$bulk_discount);
					$sessionBulkDiscount = $this->request->Session()->read('bulk_discount');
				}
				
				if((int)$item){
					$productArr[$productID] = array(
									'quantity' => $quantity,
									'selling_price' => $selling_price,
									'net_amount' => $net_amount, //newly added
									'remarks' => $remarks,
									'product' => $productTitle,
									'discount' => $discount,
									'price_without_vat' => $price_without_vat1,
									'discount_status' => $discountStatus,
									'bulk_discount' => $bulkDiscountPercentage,
									'type' => $type
									);
					//pr($productArr);die;
					$productCounts++;
				}				
			}
			//pr($productArr);die;
			//pr($session_basket);die;
			$sum_total = $this->add_arrays(array($productArr,$session_basket));
			$this->request->Session()->write('Basket', $sum_total);
			$this->request->Session()->write('country', $country);
			$sum_total = $this->request->Session()->read('Basket');
			if(empty($sum_total)){
				$flashMessage = "Failed to create order. <br />Please select quantity atleast for one product!";
				$this->Flash->error($flashMessage,array('escape' => false));
				$this->redirect(array('action' => "credit_note/$customerId/page:$current_page"));
				if($session_special_invoice == 1){
					$query = "DELETE FROM `t_credit_receipts` WHERE `t_credit_receipts.id` = '$receiptId'";
					$conn = ConnectionManager::get('default');
					$stmt = $conn->execute($query); 
					//$this->CreditReceipt->query("DELETE FROM `t_credit_receipts` WHERE `t_credit_receipts.id` = '$receiptId'");
				}else{
					$query = "DELETE FROM `credit_receipts` WHERE `credit_receipts.id` = '$receiptId'";
					$conn = ConnectionManager::get('default');
					$stmt = $conn->execute($query); 
					//$this->CreditReceipt->query("DELETE FROM `credit_receipts` WHERE `credit_receipts.id` = '$receiptId'");
				}
				
				die;
			}
			
			$datetime = date('Y-m-d H:i:s');
			$currencySymbol = $this->setting['currency_symbol'];
			//----------------------Code for printing and updating session for payment page---------------------------
			$basketStr = "";
			$vatAmount = $totalDiscountAmount = $totalBillingAmount = $billingAmount = $counter = 0;
			if(is_array($sum_total)){
				$productIDs = array_keys($sum_total);
				if(empty($productIDs)){
                    $productIDs = array(0 => null);
                }
                $productCodes_query = $productTable->find('all', array(
											  'conditions' => array('id IN' => $productIDs),
											  'fields' => array('id', 'product_code')));
				$productCodes_query = $productCodes_query->hydrate(false);
                if(!empty($productCodes_query)){
                    $productCodes = $productCodes_query->toArray();
                }else{
                    $productCodes = array();
                }
				$product_code = array();
				foreach($productCodes as $key => $value){
					$product_code[$value['id']] = $value['product_code'];
				}
				$p_kiosk_id = $this->request->Session()->read('kiosk_id');
				if($p_kiosk_id == 0 || $p_kiosk_id == ""){
					$p_kiosk_id = 10000;
				}
				//$final_vat = 0;
				foreach($sum_total as $key => $basketItem){
					$counter++;
					$vat = $this->VAT;
					$vatItem = $vat/100;
					$discount = $basketItem['discount'];				
					$sellingPrice = $basketItem['selling_price'];
					$refundType = $basketItem['type'];
					$netAmount = $basketItem['net_amount']; //newly added
					$price_without_vat = $basketItem['price_without_vat']; //newly added
					$itemPrice = $basketItem['selling_price']/(1+$vatItem);
					$discountAmount = $price_without_vat*$basketItem['discount']/100* $basketItem['quantity']; //newly updated
					//$discountAmount = $sellingPrice*$basketItem['discount']/100* $basketItem['quantity'];
					$totalDiscountAmount+= $discountAmount;
					$totalItemPrice = $price_without_vat * $basketItem['quantity']; //newly updated
					//$totalItemPrice = $basketItem['selling_price'] * $basketItem['quantity'];
					$bulkDiscountPercentage = $sessionBulkDiscount;
					//$pricebeforeVat = $itemPrice*$basketItem['quantity']-$discountAmount;
					$totalItemCost = $totalItemPrice-$discountAmount;
					$totalBillingAmount+=$totalItemCost;
					$vatperitem = $basketItem['quantity']*($sellingPrice-$itemPrice);
					$bulkDiscountValue = (float)$totalBillingAmount*(float)$bulkDiscountPercentage/100;
					$netBillingAmount = $totalBillingAmount-$bulkDiscountValue;
					$netPrice = $netBillingAmount; //Newly Added
					$vatAmount = $netBillingAmount*$vatItem; //Newly Added
					//$vatAmount+= $vatperitem;
					if($country == "OTH"){
						$finalAmount = $netBillingAmount;
					}else{
						if($session_special_invoice == 1){
							$finalAmount = $netBillingAmount;
						}else{
							$finalAmount = $netBillingAmount+$vatAmount;
						}
						//$finalAmount = $netBillingAmount+$vatAmount;
					}
                    
                    $single_product_vale = 0;
					$single_product_vale = $totalItemCost/$basketItem['quantity'];
                    
					$code = "";
					if(array_key_exists($key,$product_code)){
						$code = $product_code[$key];
					}
					$basketStr.="<tr>
							<td>{$counter})</td>
							<td>{$code}</td>
							<td>".$basketItem['product']."</td>
							<td>".$refundType."</td>
							<td>".$basketItem['quantity']."</td>
							<td> ".$CURRENCY_TYPE.number_format($price_without_vat, 2)."</td><!-- value coming wrong-->
							<!--td> ".$vat."</td--><!-- Not Required-->
							<!--td> ".$CURRENCY_TYPE.number_format($itemPrice, 2)."</td-->
							<td> ".$single_product_vale."</td>
							<!--td> ".$CURRENCY_TYPE.number_format($vatperitem,2)."</td--><!-- Not Required-->
							<td> ".$CURRENCY_TYPE.number_format($discountAmount,2)."</td>
							<td> ".$CURRENCY_TYPE.number_format($totalItemCost,2)."</td></tr>";
				}
			}
			$cust_id = $this->request->params['pass'][0];
			$cust_res_query = $this->Customers->find('all',array('fields' => array('id','country'),'conditions' => array(
																	'id' => $cust_id
																   )
															));
			$cust_res_query = $cust_res_query->hydrate(false);
			if(!empty($cust_res_query)){
			   $cust_res = $cust_res_query->first();
			}else{
			   $cust_res = array();
			}
			if(!empty($cust_res)){
				$country = $cust_res['country'];
			}
			if(!empty($basketStr)){
				if($country == "OTH"){
					$basketStr = "<table border='1'><tr>
							<th style='width: 10px;'>Sr No</th>
							<th style='width: 128px;'>Product code</th>
							<th style='width:455px;'>Product</th>
							<th style='width: 65px;'>Type</th>
							<th style='width: 30px;'>Qty</th>
							<th style='width: 105px;'>Price/Item</th><!-- value coming wrong-->
							<!--th>Vat %</th--><!-- Not Required-->
							<!--th>Net Price/Item</th-->
							<th style='width: 77px;'>Disct Value</th>
							<!--th>Vat Value</th--><!-- Not Required-->
							<th style='width: 76px;'>Disct Value</th>
							<th style='width: 49px;'>Gross</th>
							</tr>".$basketStr."
							<tr><td colspan='8'>Bulk Discount ({$bulkDiscountPercentage}%)</td><td>".$CURRENCY_TYPE.number_format($bulkDiscountValue,2)."</td></tr>
							<tr><td colspan='8'>Sub Total</td><td>".$CURRENCY_TYPE.number_format($netBillingAmount,2)."</td></tr>
							<tr><td colspan='8'>Total Amount</td><td> ".$CURRENCY_TYPE.number_format($finalAmount,2)."</td></tr></table>";
				}else{
					if($session_special_invoice == 1){
						$basketStr = "<table border='1'><tr>
							<th style='width: 10px;'>Sr No</th>
							<th style='width: 128px;'>Product code</th>
							<th style='width:455px;'>Product</th>
							<th style='width: 65px;'>Type</th>
							<th style='width: 30px;'>Qty</th>
							<th style='width: 105px;'>Price/Item</th><!-- value coming wrong-->
							<!--th>Vat %</th--><!-- Not Required-->
							<!--th>Net Price/Item</th-->
							<th style='width: 77px;'>Disct Value</th>
							<!--th>Vat Value</th--><!-- Not Required-->
							<th style='width: 76px;'>Disct Value</th>
							<th style='width: 49px;'>Gross</th>
							</tr>".$basketStr."
							<tr><td colspan='8'>Bulk Discount ({$bulkDiscountPercentage}%)</td><td>".$CURRENCY_TYPE.number_format($bulkDiscountValue,2)."</td></tr>
							<tr><td colspan='8'>Sub Total</td><td>".$CURRENCY_TYPE.number_format($netBillingAmount,2)."</td></tr>
							<tr><td colspan='8'>Total Amount</td><td> ".$CURRENCY_TYPE.number_format($finalAmount,2)."</td></tr></table>";
					}else{
						$basketStr = "<table border='1'><tr>
							<th style='width: 10px;'>Sr No</th>
							<th style='width: 128px;'>Product code</th>
							<th style='width:455px;'>Product</th>
							<th style='width: 65px;'>Type</th>
							<th style='width: 30px;'>Qty</th>
							<th style='width: 105px;'>Price/Item</th><!-- value coming wrong-->
							<!--th>Vat %</th--><!-- Not Required-->
							<!--th>Net Price/Item</th-->
							<th style='width: 77px;'>Disct Value</th>
							<!--th>Vat Value</th--><!-- Not Required-->
							<th style='width: 76px;'>Disct Value</th>
							<th style='width: 49px;'>Gross</th>
							</tr>".$basketStr."
							<tr><td colspan='8'>Bulk Discount ({$bulkDiscountPercentage}%)</td><td>".$CURRENCY_TYPE.number_format($bulkDiscountValue,2)."</td></tr>
							<tr><td colspan='8'>Sub Total</td><td>".$CURRENCY_TYPE.number_format($netBillingAmount,2)."</td></tr>
							<tr><td colspan='8'>Vat</td><td> ".$CURRENCY_TYPE.number_format($vatAmount,2)."</td></tr>
							<tr><td colspan='8'>Net Amount</td><td>".$CURRENCY_TYPE.number_format($netPrice,2)."</td></tr>
							<tr><td colspan='8'>Total Amount</td><td> ".$CURRENCY_TYPE.number_format($finalAmount,2)."</td></tr></table>";
					}
					
				}
				
			}
			$totalItems = count($this->request->Session()->read('Basket'));
			$this->request->Session()->write('finalAmount', $finalAmount);
			//-------------------------------------------------------------
			$flashMessage = "Please review the credit note details and choose payment mode:<br/>$basketStr";
			//echo "$basketStr";die;
			$this->Flash->Success($flashMessage,array('escape' => false));	
			return $this->redirect(array('action' => "credit_payment",$receiptId,$customerId));
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
        }//end of custom code by rasa
		
	public function creditNoteCheckout($customerId=""){
		$currencySymbol = $this->setting['currency_symbol'];
		$customerAccountDetails_query = $this->Customers->find('all',array(
									'conditions'=>array('Customers.id'=>$customerId)
									)
								);
		$customerAccountDetails_query = $customerAccountDetails_query->hydrate(false);
        if(!empty($customerAccountDetails_query)){
            $customerAccountDetails = $customerAccountDetails_query->first();
        }else{
            $customerAccountDetails = array();
        }
        $product_code_query = $this->Products->find('list',array('keyField' => 'id',
                                           'valueField' => 'product_code'
                                           ));
        $product_code_query = $product_code_query->hydrate(false);
        if(!empty($product_code_query)){
            $product_code = $product_code_query->toArray();
        }else{
            $product_code = array();
        }
		$country = $customerAccountDetails['country'];
		$vat = $this->VAT;
		$this->set(compact('vat','country','currencySymbol','customerId','product_code'));
	}
	
	public function deleteItemFromSession($product_id="",$customerId = ""){
		  unset($_SESSION['Basket'][$product_id]);
		if(true){ //$this->Session->delete("Basket.$product_id")
			return $this->redirect(array('action'=>'credit_note_checkout',$customerId));
		}
	}
	
	public function updateQuantityInSession($customerId = ""){
		$session_basket = $this->request->Session()->read('Basket');
		$newArray = array();
		if(array_key_exists('credit_product_details',$this->request['data'])){
			$requestedData = $this->request['data']['credit_product_details'];
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
				$this->request->Session()->delete("Basket");
				$this->request->Session()->write("Basket",$newArray);
			    $this->Flash->Success("Quantity has been successfully updated");
			}
		}
		
		return $this->redirect(array('action'=>'credit_note_checkout',$customerId));
	}
	
	public function creditPayment($id = null,$customerId = '') {
		//pr($this->Session->read());
        $kiosk_id = $this->request->Session()->read('kiosk_id');
		if(!empty($kiosk_id)){
			$productSource = "kiosk_{$kiosk_id}_products";
			$CreditReceiptSource = "kiosk_{$kiosk_id}_credit_receipts";
			$CreditProductDetailSource = "kiosk_{$kiosk_id}_credit_product_details";
			$CreditPaymentDetailSource = "kiosk_{$kiosk_id}_credit_payment_details";
		}else{
            $productSource = "products";
			$CreditReceiptSource = "credit_receipts";
			$CreditProductDetailSource = "credit_product_details";
			$CreditPaymentDetailSource = "credit_payment_details";
        }
		$session_special_invoice = $this->request->Session()->read('special_invoice');
		if($session_special_invoice == 1){
			$CreditReceiptSource = "t_credit_receipts";
			$CreditProductDetailSource = "t_credit_product_details";
			$CreditPaymentDetailSource = "t_credit_payment_details";
			//$this->CreditPaymentDetail->setSource("t_credit_payment_details");
		}
        $productTable = TableRegistry::get($productSource,[
                                                            'table' => $productSource,
                                                        ]);
        $CreditReceiptTable = TableRegistry::get($CreditReceiptSource,[
                                                                            'table' => $CreditReceiptSource,
                                                                        ]);
        $CreditProductDetailTable = TableRegistry::get($CreditProductDetailSource,[
                                                                                    'table' => $CreditProductDetailSource,
                                                                                ]);
        $CreditPaymentDetailTable = TableRegistry::get($CreditPaymentDetailSource,[
                                                                                    'table' => $CreditPaymentDetailSource,
                                                                                ]);
		if (!$CreditReceiptTable->exists($id)) {
			throw new NotFoundException(__('Invalid product receipt'));
		}
		$productName_query = $productTable->find('list',
													   [
															'valueField' => "product",
													   ]
											 );
		$productName_query = $productName_query->hydrate(false);
        if(!empty($productName_query)){
            $productName = $productName_query->toArray();
        }else{
            $productName = array();
        }
		
		$options = array('conditions' => array('ProductReceipts.id' => $id));
		$productReceiptData_query = $this->ProductReceipts->find('all', $options);
		$productReceiptData_query = $productReceiptData_query->hydrate(false);
        if(!empty($productReceiptData_query)){
            $productReceiptData = $productReceiptData_query->first();
        }else{
            $productReceiptData = array();
        }
		//pr($productReceiptData);die;
		$this->set('ProductReceipt', $productReceiptData);
		$this->set(compact('productName'));
		
		if ($this->request->is(array('post', 'put'))) {
			$productReceiptDetails_query = $CreditReceiptTable->Find('all',array(
									'conditions' => array('id'=>$id)
									)
								 );
			
			$productReceiptDetails_query = $productReceiptDetails_query->hydrate(false);
            if(!empty($productReceiptDetails_query)){
                $productReceiptDetails = $productReceiptDetails_query->first();
            }else{
                $productReceiptDetails = array();
            }
			//pr($productReceiptDetails);die;
			
			if(!empty($productReceiptDetails)){
			   if(array_key_exists('agent_id',$productReceiptDetails)){
					$agent_id = $productReceiptDetails['agent_id'];
			   }else{
					$agent_id = 0;
			   }
			}else{
			   $agent_id = 0;
			}
			
			
			$amountToPay = $this->request['data']['final_amount'];
			$totalPaymentAmount = 0;
			$amountDesc = array();
			$countCycles = 0;
			$error = '';
			$errorStr = '';
			foreach($this->request['data']['Payment']['Amount'] as $key => $paymentAmount){
				$totalPaymentAmount+= (float)$paymentAmount;
				//$paymentDescription = $this->request['data']['Payment']['Description'][$key];
				//!empty($paymentDescription) && 
				if(!empty($paymentAmount)){
					$countCycles++;
				}
				
				//if(empty($paymentDescription) && !empty($paymentAmount)){
					//$error[] = "Sale could not be created. Payment description must be entered";
					//break;
				//}
			}
			
			foreach($this->request['data']['Payment']['Payment_Method'] as $key => $paymentMethod){
				/*if($paymentMethod=="On Credit" and $countCycles>1){
					$error[] = "'On Credit' payment method cannot be clubbed with any other. Either choose 'On Credit' or the other payment methods";
				}else*/if($totalPaymentAmount<$amountToPay &&
					($paymentMethod=="Cheque" ||
					$paymentMethod=="Cash" ||
					$paymentMethod=="Bank Transfer" ||
					$paymentMethod=="Card")){
					$error[] = "Amount must be equivalent to &#163; {$amountToPay}. Please try again";
					break;
				}elseif($totalPaymentAmount>$amountToPay &&
					($paymentMethod=="Cheque" ||
					$paymentMethod=="Cash" ||
					$paymentMethod=="Bank Transfer" ||
					$paymentMethod=="Card")){
					$error[] = "Amount must be equivalent to &#163; {$amountToPay}. Please try again";
					break;
				}elseif($paymentMethod=="On Credit" && empty($this->request['data']['Payment']['Description'][$key])){
					$error[] = "Sale could not be created. Payment description must be entered";
					break;
				}elseif($totalPaymentAmount<$amountToPay && $paymentMethod=="On Credit")break;
			}
			if(!empty($error)){
				$errorStr = implode("<br/>",$error);
				$this->Flash->error("$errorStr");
				return $this->redirect(array('action'=>'credit_payment',$id,$customerId));
			}
			
			$counter = 0;
			if($this->request['data']['Payment']['Payment_Method'][0] == "On Credit" && $countCycles==1){
				$creditPaymentDetailData = array(
							'credit_receipt_id' => $id,
							'agent_id' => $agent_id,
							'payment_method' => $this->request['data']['Payment']['Payment_Method'][0],
							//'description' => $this->request['data']['Payment']['Description'][0],
							'amount' => $amountToPay,
							'payment_status' => 0,
							'status' => 1,
							   );
                    $CreditPaymentDetailTable->behaviors()->load('Timestamp');
					$CreditPaymentDetailsEntity = $CreditPaymentDetailTable->newEntity();
					$CreditPaymentDetailsEntity = $CreditPaymentDetailTable->patchEntity($CreditPaymentDetailsEntity,$creditPaymentDetailData,['validate' => false]);
					if($CreditPaymentDetailTable->save($CreditPaymentDetailsEntity)){
						$counter++;
					}
			}else{
				foreach($this->request['data']['Payment']['Amount'] as $key => $paymentAmount){
					$paymentMethod = $this->request['data']['Payment']['Payment_Method'][$key];
					//$paymentDescription = $this->request['data']['Payment']['Description'][$key];
					
					if($paymentMethod == "On Credit"){
						$payment_status = 0;
					}else{
						$payment_status = 1;
					}
					if(!empty($paymentAmount) ){ //&& $paymentDescription
						$creditPaymentDetailData = array(
								'credit_receipt_id' => $id,
								'agent_id' => $agent_id,
								'payment_method' => $paymentMethod,
								//'description' => $paymentDescription,
								'amount' => $paymentAmount,
								'payment_status' => $payment_status,
								'status' => 1,
								   );
						if($session_special_invoice == 1){
							$kiosk_id = $this->request->Session()->read('kiosk_id');
							if(!empty($kiosk_id)){
								$creditPaymentDetailData['kiosk_id'] = $kiosk_id;
							}
						}
                        $CreditPaymentDetailTable->behaviors()->load('Timestamp');
						$CreditPaymentDetailsEntity = $CreditPaymentDetailTable->newEntity();
						 $CreditPaymentDetailsEntity = $CreditPaymentDetailTable->patchEntity($CreditPaymentDetailsEntity,$creditPaymentDetailData,['validate' => false]);
						if($CreditPaymentDetailTable->save($CreditPaymentDetailsEntity)){
							$counter++;
						}
					}
				}
			}
			if($counter>0){
				return $this->redirect(array('action'=>'save_credit_note',$id,$customerId));;
			}else{
				$flashMessage = ("Sale could not be created. Please try again");
				$this->Flash->error($flashMessage);
				return $this->redirect(array('action'=>'credit_payment', $id,$customerId));
			}
		}
	}
	
	public function saveCreditNote($saleId = '',$customerId = ''){
		$settingArr = $this->setting;
		$oldBasket = $this->request->Session()->read('oldBasket');
		$newBasket = $this->request->Session()->read('Basket');
		$country = $this->request->Session()->read('country');
		$kiosk_id = $this->request->Session()->read('kiosk_id');
		$session_special_invoice = $this->request->Session()->read('special_invoice');
		if(!empty($kiosk_id)){
			$productSource = "kiosk_{$kiosk_id}_products";
			$CreditReceiptSource = "kiosk_{$kiosk_id}_credit_receipts";
			$CreditProductDetailSource = "kiosk_{$kiosk_id}_credit_product_details";
			$CreditPaymentDetailSource = "kiosk_{$kiosk_id}_credit_payment_details";
		}else{
            $productSource = "products";
			$CreditReceiptSource = "credit_receipts";
			$CreditProductDetailSource = "credit_product_details";
			$CreditPaymentDetailSource = "credit_payment_details";
        }
		if($session_special_invoice == 1){
		    $CreditReceiptSource = "t_credit_receipts";
			$CreditProductDetailSource = "t_credit_product_details";
			$CreditPaymentDetailSource = "t_credit_payment_details";
		}
        $productTable = TableRegistry::get($productSource,[
                                                            'table' => $productSource,
                                                        ]);
        $CreditReceiptTable = TableRegistry::get($CreditReceiptSource,[
                                                                            'table' => $CreditReceiptSource,
                                                                        ]);
        $CreditProductDetailTable = TableRegistry::get($CreditProductDetailSource,[
                                                                                    'table' => $CreditProductDetailSource,
                                                                                ]);
        $CreditPaymentDetailTable = TableRegistry::get($CreditPaymentDetailSource,[
                                                                                    'table' => $CreditPaymentDetailSource,
                                                                                ]);
		
		$new_kiosk_id = $kiosk_id;
		if($new_kiosk_id == 0){
		  $new_kiosk_id = 10000;
		}
		$NewkioskDetails_query = $this->Kiosks->find("all",['conditions' => ['id' => $new_kiosk_id]]);
		$NewkioskDetails_query = $NewkioskDetails_query->hydrate(false);
		if(!empty($NewkioskDetails_query)){
		  $NewkioskDetails = $NewkioskDetails_query->first();
		}else{
		  $NewkioskDetails = array();
		}
		
		
		$bulkDiscount = $this->request->Session()->read('bulk_discount');
		$amount = 0;
		foreach($newBasket as $finalProductId => $finalDetail){
			$finalDiscount = $finalDetail['discount'];
			if($country == "OTH"){
				$selling_price = $finalDetail['price_without_vat'];
			}else{
				$selling_price = $finalDetail['selling_price'];
			}
			$amountToPay = $finalDetail['quantity']*($selling_price-$selling_price*$finalDiscount/100);
			$netAmount = (float)$amountToPay-(float)$amountToPay*(float)$bulkDiscount/100;
			$amount+=$netAmount;
		}
		$finalAmount=$this->request->Session()->read('finalAmount');
		if($finalAmount > 0){
			$amount = number_format((float)$finalAmount, 2, '.', '');
		}
		$paymentDetails_query = $CreditPaymentDetailTable->find('all',array(
									'conditions'=>array('credit_receipt_id'=>$saleId)
									)
								  );
		$paymentDetails_query = $paymentDetails_query->hydrate(false);
        if(!empty($paymentDetails_query)){
            $paymentDetails = $paymentDetails_query->toArray();
        }else{
            $paymentDetails = array();
        }
		$payment_method = array();
		
		foreach($paymentDetails as $key=>$paymentDetail){
			$payment_method[] = $paymentDetail['payment_method']." ".$settingArr['currency_symbol'].$paymentDetail['amount'];
		}
			$newBasket = $this->request->Session()->read('Basket');
			$count = 0;
			$sum_total = $newBasket;
			$vat = $this->VAT;
			$date = date("d/m/Y", $_SERVER['REQUEST_TIME']);
			
			$customerData_query = $this->Customers->find('all',array(
										'conditions' => array('Customers.id'=>$customerId)
											)
									      );
			$customerData_query = $customerData_query->hydrate(false);
            if(!empty($customerData_query)){
                $customerData = $customerData_query->first();
            }else{
                $customerData = array();
            }
			
					$receiptData = array(
						'customer_id' => $customerId,
						'address_1' => $customerData['del_address_1'],
						'address_2' => $customerData['del_address_2'],
						'city' => $customerData['del_city'],
						'state' => $customerData['del_state'],
						'zip' => $customerData['del_zip'],
						'processed_by' => $this->request->Session()->read('Auth.User.id'),
						'fname' => $customerData['fname'],
						'lname' => $customerData['lname'],
						'mobile' => $customerData['mobile'],
						'email' => $customerData['email']
					     );
				//pr($newBasket);
				$total_cost = 0;
				$product_cost = $this->Products->find("list",array('keyField' => 'id','valueField' => 'cost_price'))->toArray();
			foreach($newBasket as $productID => $productData){
				if($productID == 'error')continue;
				
				$p_kiosk_id = $kisk_id = $this->request->Session()->read('kiosk_id');
				if((int)$kisk_id){
					$kiosk_id = $this->request->Session()->read('kiosk_id');
				}else{
					$kiosk_id = 0;
				}
				if($p_kiosk_id == 0 || $p_kiosk_id == ''){
					$p_kiosk_id = 10000;
				}
				$quantity = $productData['quantity'];
				$discount = $productData['discount'];
				$refundType = $productData['type'];
				$net_amount = $productData['net_amount'];
				
				
				$product_detail_query = $productTable->find('all',array('conditions' => array(
																	   'id' => $productID
																	   )));
				
				$product_detail_query = $product_detail_query->hydrate(false);
				if(!empty($product_detail_query)){
					$product_detail = $product_detail_query->toArray();
				}else{
					$product_detail = array();
				}
				$withVatValue = $product_detail[0]['selling_price'];
				
				
				
				
				//----------------------
				$sale_price = $productData['selling_price'];
					$credit_price = $productData['selling_price'];
				if($net_amount > $withVatValue){
					$numerator = $withVatValue*100;
					$denominator = $vat+100;
					$priceWithoutVat = $numerator/$denominator;
					$priceWithoutVat = round($priceWithoutVat,2);
					$sold_price = $net_amount;
					$firstVal = $priceWithoutVat - $sold_price;
					$discount = $firstVal/$priceWithoutVat*100;
					
					$sale_price = $withVatValue;
					$credit_price = $withVatValue;
					
				}
					
				//----------------------
				
				$creditProductDetailsData = array(
							'kiosk_id' => $kiosk_id,
							'customer_id' => $customerId,
							'credit_receipt_id' => $saleId,
							'sale_price' => $sale_price,
							'credit_price' => $credit_price,
							'credit_by' => $this->request->session()->read('Auth.User.id'),
							'quantity' => $quantity,
							'product_id' => $productID,
							'discount' => $discount,
							'type' => $refundType
								);
				
				$cost = $product_cost[$productID];
				$final_cost = $cost * $quantity;
				$total_cost += $final_cost;
				///pr($creditProductDetailsData);die;
                $CreditProductDetailTable->behaviors()->load('Timestamp');
                $CreditProductDetailsEntity = $CreditProductDetailTable->newEntity();
                $CreditProductDetailsEntity = $CreditProductDetailTable->patchEntity($CreditProductDetailsEntity,$creditProductDetailsData,['validate' => false]);
			   //pr($CreditProductDetailsEntity);die;
				if($CreditProductDetailTable->save($CreditProductDetailsEntity)){
					$p_discount = $productData['discount'];
					$price_withot_vat = $productData['price_without_vat'];
					$fianl_value = $price_withot_vat - ($price_withot_vat * $p_discount)/100;
					if($bulkDiscount > 0){
						$bulk_value = $fianl_value * ($bulkDiscount/100);
						$after_bulk_value = $fianl_value - $bulk_value;
						$selling_price_without_vat = $after_bulk_value*$quantity;
					}else{
						$after_bulk_value = $fianl_value;
						$selling_price_without_vat = $fianl_value*$quantity;
					}
					
					
					if($country == "OTH"){
						//$finalAmount = $netBillingAmount;
						$final_vat = 0;
					}else{
						$vat_value =  $after_bulk_value * ($vat/100);
						$final_vat = $vat_value * $quantity;
						//$finalAmount = $netBillingAmount+$vatAmount;
					}
					$product_code_query = $productTable->find('list',[
																			 'conditions' => ['id' => $productID],
																			 'keyField' => 'id',
																			 'valueField' => 'product_code'
																	   ]
																);
					$product_code_query = $product_code_query->hydrate(false);
                    if(!empty($product_code_query)){
                        $product_code = $product_code_query->toArray();
                    }else{
                        $product_code = array();
                    }
					$data = array(
									'quantity' => $quantity,
									'product_code' => $product_code[$productID],
									'selling_price_withot_vat' => $selling_price_without_vat,
									'vat' => $final_vat
								   );
					//pr($data);die;
                    if($session_special_invoice == 1){
						$is_special = 1;
					}else{
						$is_special = 0;
					}
                    
					$this->insert_to_ProductSellStats($productID,$data,$p_kiosk_id,$operations = '-',$is_special);
					$count++;
					
					$data_to_save = array(
								   'bill_amount' => $amount,
								   'orig_bill_amount' => $amount,
								  'credit_amount' => $amount,
								   'bulk_discount' => $bulkDiscount
								  );
					if($final_vat > 0){
						 $data_to_save['vat'] = $vat;
					}else{
						 $data_to_save['vat'] = 0;
					}
					$CreditReceiptTable->behaviors()->load('Timestamp');
					$CreditReceiptsEntity = $CreditReceiptTable->get($saleId);
					$CreditReceiptsEntity = $CreditReceiptTable->patchEntity($CreditReceiptsEntity,$data_to_save);
					$CreditReceiptTable->save($CreditReceiptsEntity);
					if($refundType=='Normal'){
						 $productData = array('quantity' => "Product.quantity + $quantity");
						 $query = "UPDATE `$productSource` SET `quantity` = `quantity` + $quantity WHERE `$productSource`.`id` = '$productID'";
						 $conn = ConnectionManager::get('default');
						 $stmt = $conn->execute($query); 
					}else{
						$faultyProductData = array(
								'kiosk_id' => $kiosk_id,
								'credit_receipt_id' => $saleId,
								'credit_by' => $this->request->session()->read('Auth.User.id'),
								'product_id' => $productID,
								'customer_id' => $customerId,
								'quantity' => $quantity,
								'sale_price' => $productData['selling_price'],
								'discount' => $discount
									   );
						$this->loadModel('FaultyProductDetails');
						$new_entity = $this->FaultyProductDetails->newEntity($faultyProductData,['validate' => false]);
                        $patch_entity = $this->FaultyProductDetails->patchEntity($new_entity,$faultyProductData,['validate' => false]);
						$this->FaultyProductDetails->save($patch_entity);
						
						//****added on 15.03.2016 sending data to defective_kiosk_product table from here xyz
						//echo $this->request->Session()->read('Auth.User.groupid'); die;//echo ADMINISTRATORS;die;
						if(empty($kiosk_id) && ($this->request->Session()->read('Auth.User.group_id') == ADMINISTRATORS || $this->request->Session()->read('Auth.User.group_id') == MANAGERS)){
							$kskId = 10000;
						}else{
							$kskId = $this->request->Session()->read('kiosk_id');//$this->request->data['kiosk_id'];
						}
						$defectiveProductData = array(
								'product_id' => $productID,
								'quantity' => $quantity,
								'kiosk_id' => $kskId,
								'user_id' => $this->Auth->user('id'),
								'status' => 0,//not moved to central_faulty_products table
								'remarks' => 1//reserved for faulty refund to customer
									      );
						//not adjusting the kiosk quantity after moving to faulty as discussed by client
						$this->loadModel('DefectiveKioskProducts');
						$getID = $this->DefectiveKioskProducts->newEntity($defectiveProductData,['validate' => false]);
                        $patchEntity = $this->DefectiveKioskProducts->patchEntity($getID,$defectiveProductData,['validate' => false]);
						$this->DefectiveKioskProducts->save($patchEntity);
						//****till here
					}
				}
			}
			
			$cost_query = "UPDATE $CreditReceiptSource SET `bill_cost` = $total_cost WHERE `id` = $saleId";
			$conn = ConnectionManager::get('default');
		    $stmt = $conn->execute($cost_query);
			$send_by_email = Configure::read('send_by_email');
				if($count>0){
					$receiptRequired = $this->request->Session()->read('receipt_required');
					
					$options = array(
						'conditions' => array('id'=> $saleId),
						//'contain' => array("CreditProductDetails","Customers"),
						);
					$productReceipt_query = $CreditReceiptTable->find('all', $options);
					$productReceipt_query = $productReceipt_query->hydrate(false);
                    if(!empty($productReceipt_query)){
                        $productReceipt = $productReceipt_query->first();
                    }else{
                        $productReceipt = array();
                    }
					$customer_id = $productReceipt['customer_id'];
					$recipt_id = $productReceipt['id'];
					$res_query = $CreditProductDetailTable->find('all',[
															'conditions' => ['credit_receipt_id' => $recipt_id],
														   ]);
					$res_query = $res_query->hydrate(false);
					if(!empty($res_query)){
						 $credit_product_detail = $res_query->toArray();
					}else{
						 $credit_product_detail = array();
					}
					$this->loadModel('Customers');
					$cust_query = $this->Customers->find('all',[
												  'conditions' => ['id' => $customer_id]
												  ]);
					$cust_query = $cust_query->hydrate(false);
					if(!empty($cust_query)){
						 $cust = $cust_query->first();
					}else{
						 $cust = array();
					}
					
					//pr($productReceipt);die;
					$processed_by = $productReceipt['processed_by'];
					$userName_query = $this->Users->find('all',array('conditions'=>array('Users.id'=>$processed_by),'fields'=>array('username')));
					$userName_query = $userName_query->hydrate(false);
                    if(!empty($userName_query)){
                        $userName = $userName_query->first();
                    }else{
                        $userName = array();
                    }
					
					$user_name = $userName['username'];
					foreach($credit_product_detail as $key => $productDetail){
						$productIdArr[] = $productDetail['product_id'];
					}
					foreach($productIdArr as $product_id){
						$product_detail_query = $productTable->find('all', array('conditions'=>array('id'=>$product_id),'fields' => array('id','product','product_code')));
						$product_detail_query = $product_detail_query->hydrate(false);
                        if(!empty($product_detail_query)){
                            $product_detail[] = $product_detail_query->first();
                        }else{
                            $product_detail[] = array();
                        }
					}
					foreach($product_detail as $productInfo){
						$productName[$productInfo['id']] = $productInfo['product'];
						$productCode[$productInfo['id']] = $productInfo['product_code'];
					}
					
					$countryOptions = Configure::read('uk_non_uk');
					
					$fullAddress = $kiosk_id = $kioskDetails = $kioskName = $kioskAddress1 = $kioskAddress2 = $kioskCity = $kioskState = $kioskZip  = $kioskZip = $kioskContact = $kioskCountry = $kioskTable = "";
					if($this->request->session()->read('Auth.User.group_id')== KIOSK_USERS &&
					   $this->request->session()->read('Auth.User.user_type') == 'wholesale'){
						$kiosk_id = $this->request->Session()->read('kiosk_id');
						$kioskDetails_query = $this->Kiosks->find('all',array('conditions'=>array('Kiosks.id'=>$kiosk_id),'fields'=>array('id','name','address_1','address_2','city','state','zip','contact','country')));
						$kioskDetails_query = $kioskDetails_query->hydrate(false);
                        if(!empty($kioskDetails_query)){
                            $kioskDetails = $kioskDetails_query->first();
                        }else{
                            $kioskDetails = array();
                        }
						$kioskName = $kioskDetails['name'];
						$kioskAddress1 = $kioskDetails['address_1'];
						$kioskAddress2 = $kioskDetails['address_2'];
						$kioskCity = $kioskDetails['city'];
						$kioskState = $kioskDetails['state'];
						$kioskZip = $kioskDetails['zip'];
						$kioskContact = $kioskDetails['contact'];
						$kioskCountry = $kioskDetails['country'];
						
						if(!empty($kioskAddress1)){
							$fullAddress.=$kioskAddress1.", ";
						}
						
						if(!empty($kioskAddress2)){
							$fullAddress.=$kioskAddress2.", ";
						}
						
						if(!empty($kioskCity)){
							$fullAddress.=$kioskCity.", ";
						}
						
						if(!empty($kioskState)){
							$fullAddress.=$kioskState.", ";
						}
						
						if(!empty($kioskZip)){
							$fullAddress.=$kioskZip.", ";
						}
						
						if(!empty($kioskCountry)){
							$fullAddress.=$countryOptions[$kioskCountry];
						}
						
						$kioskTable = "<table>
						<tr><td style='color: chocolate;'>".$kioskName."</td></tr>
						<tr><td style='font-size: 11px;'>".$fullAddress."</td></tr>
						</table>";
					}
					$emailSender = Configure::read('EMAIL_SENDER');
					if($session_special_invoice != 1){
						 if($receiptRequired == 1){
							  $CURRENCY_TYPE = Configure::read('CURRENCY_TYPE');
							 $Email = new Email();
							 $Email->config('default');
							 $Email->viewVars(array
											 (
											 'productReceipt' => $productReceipt,
											 'payment_method' => $payment_method,
											 'vat' => $vat,
											 'settingArr' =>$settingArr,
											 'user_name'=>$user_name,
											 'productName'=>$productName,
											 'productCode'=>$productCode,
											 'kioskContact'=>$kioskContact,
											 'kioskTable'=>$kioskTable,
											 'CURRENCY_TYPE' => $CURRENCY_TYPE,
											 'customer' => $cust,
											 'creditProductDetailsData' => $credit_product_detail,
											 'countryOptions'=>$countryOptions,
											 'NewkioskDetails' => $NewkioskDetails
											 )
										 );
							 //$Email->config(array('from' => 'me@example.org', 'transport' => 'MyCustom'));
							 //$Email = new CakeEmail(array('from' => 'me@example.org', 'transport' => 'MyCustom'));
							 $emailTo = $customerData['email'];;
							 $Email->template('credit_receipt');
							 $Email->emailFormat('both');
							 $Email->to($emailTo);
                             $Email->transport(TRANSPORT);
                             $Email->from([$send_by_email => $emailSender]);
							// $Email->sender("sales@oceanstead.co.uk");
							 $Email->subject('Credit Receipt');
							 $Email->send();
						 }
					}
					
					$this->Flash->success("Credit note has been saved");
					$this->request->Session()->delete('Basket');
					$this->request->Session()->delete('BulkDiscount');
					$this->request->Session()->delete('receipt_required');
					$this->request->Session()->delete('bulk_discount');
					$this->request->Session()->delete('oldBasket');
					$this->request->Session()->delete('session_basket');
					return $this->redirect(array('controller'=>'customers','action'=>'index'));
				}else{
					//failed to save data
				}
	}
	
	public function tViewCreditNote(){
		$source = 1;
		$t_searched = 1;
		$this->set(compact('t_searched'));
		$this->viewCreditNote($source);
		//$this->layout = 'default';
		//$this->render(false);
	}
	
	public function tSearch(){
		$CreditReceiptSource = "t_credit_receipts";
		$CreditProductDetailSource = "t_credit_product_details";
		$CreditPaymentDetailSource = "t_credit_payment_details";
		$CreditReceiptTable = TableRegistry::get($CreditReceiptSource,[
														    'table' => $CreditReceiptSource,
														]);
		$CreditProductDetailTable = TableRegistry::get($CreditProductDetailSource,[
																					'table' => $CreditProductDetailSource,
																				]);
		$CreditPaymentDetailTable = TableRegistry::get($CreditPaymentDetailSource,[
																  'table' => $CreditPaymentDetailSource,
															   ]);
		$conditionArr = array();
		if(array_key_exists('payment_type',$this->request->query)){
			$searchKeyword = $this->request->query['payment_type'];
			if($searchKeyword=="On Credit" ||
				$searchKeyword=="Cash" ||
				$searchKeyword=="Card" ||
				$searchKeyword=="Bank Transfer" ||
				$searchKeyword=="Cheque"){
			    $conditionArr['payment_method like '] =  strtolower("%$searchKeyword%");
			}
			$this->set('searchKeyword',$this->request->query['payment_type']);
		}
		if(array_key_exists('invoice_detail',$this->request->query)){
			$invoiceSearchKeyword = $this->request->query['invoice_detail'];
			$this->set('invoiceSearchKeyword',$this->request->query['invoice_detail']);
		}
		
		if(array_key_exists('start_date',$this->request->query)){
			$this->set('start_date',$this->request->query['start_date']);
		}
		
		if(array_key_exists('end_date',$this->request->query)){
			$this->set('end_date',$this->request->query['end_date']);
		}
		
		if(array_key_exists('date_type',$this->request->query)){
			$date_type = $this->request->query['date_type'];
		}
		$this->set(compact('date_type'));
		
		if(array_key_exists('start_date',$this->request->query) &&
		    array_key_exists('end_date',$this->request->query) &&
		    !empty($this->request->query['start_date']) &&
		    !empty($this->request->query['end_date'])){
		  
			   if(array_key_exists('date_type',$this->request->query)){
					$date_type = $this->request->query['date_type'];
					if($date_type == 'invoice'){
						$conditionArr[] = array(
							"created >" => date('Y-m-d', strtotime($this->request->query['start_date'])),
							"created <" => date('Y-m-d', strtotime($this->request->query['end_date']. ' +1 Days')),			
							   );
					}else{
						$conditionArr[] = array(
							"created >" => date('Y-m-d', strtotime($this->request->query['start_date'])),
							"created <" => date('Y-m-d', strtotime($this->request->query['end_date']. ' +1 Days')),			
							   );
					}
			   }else{ 
					$conditionArr[] = array(
							"created >" => date('Y-m-d', strtotime($this->request->query['start_date'])),
							"created <" => date('Y-m-d', strtotime($this->request->query['end_date']. ' +1 Days')),			
							   );
			   }
		  
			//$conditionArr[] = array(
			//			"created >" => date('Y-m-d', strtotime($this->request->query['start_date'])),
			//			"created <" => date('Y-m-d', strtotime($this->request->query['end_date']. ' +1 Days')),			
			//			    );
		}
		
		if(array_key_exists('search_kw',$this->request->query)){
			//echo'hi';die;
			$textKeyword = $this->request->query['search_kw'];
			 if(!empty($textKeyword) && array_key_exists('invoice_detail',$this->request->query)){
					if($invoiceSearchKeyword=="receipt_number"){
						//echo'hi';die;
						$recID = strtolower("'%$textKeyword%'");
						$conditionArr[] = ["credit_receipt_id like $recID"] ;
					}elseif($invoiceSearchKeyword=="business"){
						//echo'hi111';die;
						 $fname = strtolower("%$textKeyword%");
						 //echo $fname;die;
                         
                         $customerIds_query = $this->Customers->find('list',
															array(
														'conditions'=>array(
																"OR" => array(
															"LOWER(`Customers`.`business`) like" => strtolower("%$fname%"),
															"LOWER(`Customers`.`fname`) like" => strtolower("%$fname%"),
											"LOWER(`Customers`.`business`) like" => strtolower("%$fname%")												    )
												    ),
										'keyField'=>'id',
                                        'valueField' => 'id',
                                        ));
                         $customerIds_query = $customerIds_query->hydrate(false);
                         if(!empty($customerIds_query)){
                            $customerIds = $customerIds_query->toArray();
                         }else{
                            $customerIds = array();
                         }
                         if(empty($customerIds)){
                            $customerIds = array(0 => null);
                         }
						 $searchCriteria['customer_id IN'] = $customerIds;
						if(array_key_exists('start_date',$this->request->query) &&
							array_key_exists('end_date',$this->request->query) &&
							!empty($this->request->query['start_date']) &&
							!empty($this->request->query['end_date'])){
							$date_type = $this->request->query['date_type'];
							if($date_type == "payment"){
								
							}else{
							 $searchCriteria[] = array(
										 "created >" => date('Y-m-d', strtotime($this->request->query['start_date'])),
										 "created <" => date('Y-m-d', strtotime($this->request->query['end_date']. ' +1 Days')),			
											);
							}
						 }
						 
						 
						 $receipt_query = $CreditReceiptTable->find('all',[
																	  'conditions' => $searchCriteria
																	  ]);
					//pr($receipt_query);
						$receipt_query = $receipt_query->hydrate(false);
						if(!empty($receipt_query)){
							 $receipt = $receipt_query->toArray();
						}else{
							 $receipt = array();
						}
						//pr($receipt);die;
						foreach($receipt as $receipts){
							 $receiptID[] = $receipts['id'];
						}
						if(empty($receiptID)){
							 $receiptID = array(0 => null) ;
						}
						//echo'hi';die;
						//echo $receiptID;die;
						$conditionArr['credit_receipt_id IN'] =  $receiptID;
						//$conditionArr['fname like '] =  strtolower("%$textKeyword%");
					}elseif($invoiceSearchKeyword=="customer_id"){
						//echo'hi222';die;
						$customerID =  (int)$textKeyword;
						$searchCriteria['customer_id'] = $customerID;
						if(array_key_exists('start_date',$this->request->query) &&
						array_key_exists('end_date',$this->request->query) &&
						!empty($this->request->query['start_date']) &&
						!empty($this->request->query['end_date'])){
							  $date_type = $this->request->query['date_type'];
							  if($date_type == "payment"){
								  
							  }else{
								  $searchCriteria[] = array(
										   "created >" => date('Y-m-d', strtotime($this->request->query['start_date'])),
										   "created <" => date('Y-m-d', strtotime($this->request->query['end_date']. ' +1 Days')),			
											  );							
							  }
	  
						   }
						
						
						 $receipt_query = $CreditReceiptTable->find('all',[
																	  'conditions' => $searchCriteria
																	  ]
																	);
					//pr($receipt_query);
						 $receipt_query = $receipt_query->hydrate(false);
						 if(!empty($receipt_query)){
							  $receipt = $receipt_query->toArray();
						 }else{
							  $receipt = array();
						 }
						 //pr($receipt);die;
						 foreach($receipt as $receipts){
							  $receiptID[] = $receipts['id'];
						 }
						 //pr($receiptID);die;
						 if(empty($receiptID)){
							  $receiptID = array(0 => null) ;
						 }
						 $conditionArr['credit_receipt_id IN'] =  $receiptID;
					}
				   $this->set('textKeyword',$this->request->query['search_kw']);
			 }
		}
		
		$agent_id = 0;
		if(array_key_exists('agent_id',$this->request->query) && !empty($this->request->query['agent_id'])){
		  
		  $agent_id = $this->request->query['agent_id'];
		  if(($invoiceSearchKeyword=="business" || $invoiceSearchKeyword=="customer_id") && !empty($this->request->query['search_kw'])){
			   $search_kw = $this->request->query['search_kw'];
			   if($invoiceSearchKeyword == "business"){
					$agent_cust_res = $this->Customers->find("list",['conditions' => [
														   "agent_id" => $agent_id,
														   "OR" => array(
															"LOWER(`Customers`.`business`) like" => strtolower("%$search_kw%"),
															"LOWER(`Customers`.`fname`) like" => strtolower("%$search_kw%"),
											"LOWER(`Customers`.`business`) like" => strtolower("%$search_kw%")	),
														   ],
															'keyField' => "id",
															"valueField" => "agent_id",
															])->toArray();
			   }else{
					$agent_cust_res = $this->Customers->find("list",['conditions' => [
														   "agent_id" => $agent_id,
														   'id' => $search_kw,
														   ],
															'keyField' => "id",
															"valueField" => "agent_id",
															])->toArray();
			   }
				  
		  }else{
			$agent_cust_res = $this->Customers->find("list",['conditions' => [
														   "agent_id" => $agent_id,
														   ],
															'keyField' => "id",
															"valueField" => "agent_id",
															])->toArray(); 
		  }
			if(!empty($agent_cust_res)){
			   $searchCriteria['customer_id IN'] = array_keys($agent_cust_res);
			   if(array_key_exists('start_date',$this->request->query) &&
					array_key_exists('end_date',$this->request->query) &&
					!empty($this->request->query['start_date']) &&
					!empty($this->request->query['end_date']))
				{
							$date_type = $this->request->query['date_type'];
							if($date_type == 'payment'){
								
							}else{
								$searchCriteria[] = array(
											"created >" => date('Y-m-d', strtotime($this->request->query['start_date'])),
											"created <" => date('Y-m-d', strtotime($this->request->query['end_date']. ' +1 Days')),			
											   );
							}
				}
				
				if(empty($searchCriteria)){
					$searchCriteria = array('0'=>null);
				}
				//if date range search
				 //pr($searchCriteria);die;
				$cutomerReceipts_query = $CreditReceiptTable->find('all',array('fields' => array('id'),
													'conditions' => $searchCriteria,
													));
				//pr($cutomerReceipts_query);die;
				$cutomerReceipts_query = $cutomerReceipts_query->hydrate(false);
				if(!empty($cutomerReceipts_query)){
					$cutomerReceipts = $cutomerReceipts_query->toArray();
				}else{
					$cutomerReceipts = array();
				}
				$receiptIDs = array();
				$conditionArr['credit_receipt_id IN'] = 0;
				if( count($cutomerReceipts) ){
					//echo $cutomerReceipts['ProductReceipt']['id'];
					foreach($cutomerReceipts as $cutomerReceipt){
						$receiptIDs[] = $cutomerReceipt['id'];
					}
					if(empty($receiptIDs)){
						$receiptIDs = array('0'=>null);
					}
					$conditionArr['credit_receipt_id IN'] = $receiptIDs;
				}
			}else{
			   $conditionArr['credit_receipt_id IN'] = array(0 => null);
			}
		}
		$this->set(compact('agent_id'));
		
		
		if(array_key_exists('kiosk_id',$this->request->query) || array_key_exists('kiosk-id',$this->request->query)){
		if(array_key_exists('kiosk_id',$this->request->query)){
		    $kiosk_id = $this->request->query['kiosk_id'];   
		}
		   if(array_key_exists('kiosk-id',$this->request->query)){
		    $kiosk_id = $this->request->query['kiosk-id'];   
		}
		   if($kiosk_id == 10000){
			   $kiosk_id = 0;
		   }
		   $conditionArr['kiosk_id'] = $kiosk_id;
		   if($kiosk_id == 0){
			   $kiosk_id = 10000;
		   }
		   $this->set(compact('kiosk_id'));
		}else{
		    $kiosk_id = $this->request->Session()->read('kiosk_id');
			 if($kiosk_id > 0){
				 $conditionArr['kiosk_id'] = $kiosk_id;
			 }else{
				 $conditionArr['kiosk_id'] = 0;
			 }  
		}
		//pr($conditionArr);die;
		$this->paginate = [
						'conditions' => $conditionArr,
						'limit' => 50,
						'order' => ['credit_receipt_id DESC']
						   ];
		$res  =$CreditPaymentDetailTable->find("all",$this->paginate);
		//pr($res);die;
		
		//pr($this->paginate);die;
		$creditPaymentDetails_query = $this->paginate($CreditPaymentDetailTable);
		//pr($creditPaymentDetails_query);die;
		if(!empty($creditPaymentDetails_query)){
			 $creditPaymentDetails = $creditPaymentDetails_query->toArray();
		}else{
			 $creditPaymentDetails = array();
		}
		//pr($creditPaymentDetails);die;
		
		$amt_to_show = 0;
		
		$amt_to_show_query = $CreditPaymentDetailTable->find('all',array('conditions' => $conditionArr));
                  $amt_to_show_query
                          ->select(['sum' => $amt_to_show_query->func()->sum('amount')])
                          //->where(['Details.site_id' => $id])
                          ->toArray();
                          
		$amt_to_show_query = $amt_to_show_query->hydrate(false);
		if(!empty($amt_to_show_query)){
		  $amt_to_show_data = $amt_to_show_query->first();
		}else{
		  $amt_to_show_data = array();
		}
		if(!empty($amt_to_show_data)){
		 $amt_to_show =  $amt_to_show_data['sum'];
		}
		$this->set(compact('amt_to_show'));
		$total_query =  $CreditPaymentDetailTable->find('all',array( 'conditions' => $conditionArr,
														    //'fields' => array('SUM(credit_amount) as TotalAmount'),
													   //'recursive'=>-1,
													    ));
		$total_query = $total_query->hydrate(false);
		if(!empty($total_query)){
			 $total = $total_query->toArray();
		}else{
			 $total = array();
		}
		//pr($total);die;
		if(!empty($total)){
			$id = array();
		   foreach($total as $total_key => $total_value){
			   $id[] = $total_value['credit_receipt_id'];
			   $CreditReceiptData_query = $CreditReceiptTable->find('all',[
																   'conditions'=>['id IN'=>$id]
															   ]
														  );
			   $CreditReceiptData_query = $CreditReceiptData_query->hydrate(false);
			   if(!empty($CreditReceiptData_query)){
				   $CreditReceiptData = $CreditReceiptData_query->toArray();
			   }else{
				   $CreditReceiptData = array();
			   }
		   }
		   //pr($CreditReceiptData);die;
		}
	   $lptotalVat = $total_vat = $remaining_vat = $withVat_amt = $totalAmt = 0;
	   $totalAmt = 0;
	   $recipt_ids = array();
	   //pr($CreditReceiptData);die;
	   if(!empty($total)){
		   foreach($CreditReceiptData as $key => $value){
			   $totalAmt += $value['credit_amount'];
			   $recipt_ids[] = $value['id'];
		   }
	   }
	   $this->set(compact("withVat_amt","total_vat","lptotalVat"));
	   if(empty($recipt_ids)){
		   $recipt_ids = array(0=>null);
	   }
	   //pr($recipt_ids);die;
	   $recipt_res_query = $CreditReceiptTable->find('all',array('conditions' => array('id IN' => $recipt_ids)));
	   $recipt_res_query = $recipt_res_query->hydrate(false);
	   if(!empty($recipt_res_query)){
		   $recipt_res = $recipt_res_query->toArray();
	   }else{
		   $recipt_res = array();
	   }
	   //pr($recipt_res);die;
	   if(!empty($recipt_res)){
		$credit_id = array();
		foreach($recipt_res as $recipt_key => $recipt_value){
		   $credit_id[] = $recipt_value['id'];
		   $CreditProductData_query = $CreditProductDetailTable->find('all',['conditions'=>['credit_receipt_id IN'=>$credit_id]]);
		   $CreditProductData_query = $CreditProductData_query->hydrate(false);
		   if(!empty($CreditProductData_query)){
			   $CreditProductData = $CreditProductData_query->toArray();
		   }else{
			   $CreditProductData = array();
		   }
		}
	   }
	   //pr($CreditProductData);die;
	   //pr($recipt_res);die;
	   $product_ids = $quantity = array();
	   //foreach($recipt_res as $r_kry => $r_value){
	   if(!empty($CreditProductData)){
		   foreach($CreditProductData as $skey => $svalue){
			   $product_ids[] = $svalue['product_id'];
			   if(array_key_exists($svalue['product_id'],$quantity)){
				   $quantity[$svalue['product_id']] += $svalue['quantity'];
			   }else{
				   $quantity[$svalue['product_id']] = $svalue['quantity'];
			   }
			   
		   }
	   }
	   //}
	   //pr($product_ids);die;
	   //pr($creditPaymentDetails);
	   //pr($creditPaymentDetails);die;
	   if(!empty($creditPaymentDetails)){
		   foreach($creditPaymentDetails as $creditPaymentKey => $creditPaymentValue)	{
			   $credit_receipt_id = $creditPaymentValue['credit_receipt_id'];
			   $creditReceiptData_query = $CreditReceiptTable->find('all',[
												   'conditions'=>['id'=>$credit_receipt_id]
											   ]
										  );
			   $creditReceiptData_query = $creditReceiptData_query->hydrate(false);
			   if(!empty($creditReceiptData_query)){
				   $creditReceiptData[$credit_receipt_id] = $creditReceiptData_query->first();
			   }else{
				   $creditReceiptData[$credit_receipt_id] = array();
			   }
		   }
	   }else{
		   $creditReceiptData = array();
	   }
	   //pr($creditReceiptData);die;
	   $this->set(compact('creditReceiptData'));
	   if(!empty($creditPaymentDetails)){
		   foreach($creditReceiptData as $creditPaymentDetail){
			   $customerIdArr[] = $creditPaymentDetail['customer_id'];
		   }
	   }
	   if(empty($product_ids)){
		   $product_ids = array(0=>null);
	   }
	   $res_p_query = $this->Products->find('list',[
										   'conditions' => [
														   'Products.id IN' => $product_ids
													    ],
										   'keyField' => 'id',
										   'valueField' => 'cost_price'
										 ]
								    );
	   $res_p_query = $res_p_query->hydrate(false);
	   if(!empty($res_p_query)){
		   $res_p = $res_p_query->toArray();
	   }else{
		   $res_p = array();
	   }
	   //pr($product_ids);pr($res_p);die;
	   if(!empty($res_p)){
		   $cost_sum = 0;
		   foreach($res_p as $key => $value){
			   if(array_key_exists($key,$quantity)){
				   $cost_sum += $value*$quantity[$key];
			   }
		   }
	   }else{
		   $cost_sum = 0;
	   }
	   //echo $cost_sum;die;
	   $this->set(compact('cost_sum'));
		
		if(!empty($customerIdArr)){
			foreach($customerIdArr as $customerId){
				$customerDetailArr_query = $this->Customers->find('all',array('conditions'=>array('Customers.id'=>$customerId),'fields'=>array('id','business','agent_id')));
				$customerDetailArr_query = $customerDetailArr_query->hydrate(false);
				if(!empty($customerDetailArr_query)){
				   $customerDetailArr[] = $customerDetailArr_query->first();
				}else{
				   $customerDetailArr[] = array();
				}
			}
		}
		
		//pr($customerDetailArr);die;
		
		$kiosk_list_query = $this->Kiosks->find('list');
		$kiosk_list_query= $kiosk_list_query->hydrate(false);
		if(!empty($kiosk_list_query)){
			    $kiosk_list = $kiosk_list_query->toArray();
		}else{
			    $kiosk_list = array();
		}
		
	   if($this->request->session()->read('Auth.User.group_id') == ADMINISTRATORS || $this->request->session()->read('Auth.User.group_id') == FRANCHISE_OWNER){
		   // $session_id = $this->Session->id();
		    $user_id = $this->request->Session()->read('Auth.User.id');
		    $data_arr = array('kiosk_id' => $kiosk_id);
		    $jsondata = json_encode($data_arr);
		    $this->loadModel('UserSettings');
		    $res_query = $this->UserSettings->find('all',array('conditions' => array(
															'user_id' => $user_id,
															'setting_name' => "dr_credit_search",
															//'user_session_key' => $session_id,
															)));
		    $res_query = $res_query->hydrate(false);
		    if(!empty($res_query)){
			   $res = $res_query->first();
		    }else{
			   $res = array();
		    }
		    if(count($res) >0){
			   $userSettingid =  $res['id'];
			   $data_to_save = array(
							   'id' => $userSettingid,
							   'user_id' => $user_id,
							   //'user_session_key' => $session_id,
							   'setting_name' => "dr_credit_search",
							   'data' => $jsondata
							   );
			   $entity = $this->UserSettings->get($userSettingid);
			   $entity = $this->UserSettings->patchEntity($entity,$data_to_save,['validate'=>false]);
			   $this->UserSettings->save($entity);
		    }else{
			   $data_to_save = array(
							   'user_id' => $user_id,
							   //'user_session_key' => $session_id,
							   'setting_name' => "dr_credit_search",
							   'data' => $jsondata
							   );
			   
			   $entity = $this->UserSettings->newEntity;
			   $entity = $this->UserSettings->patchEntity($entity,$data_to_save,['validate'=>false]);
			   $this->UserSettings->save($entity);
		    }
		}
		
		$hint = $this->ScreenHint->hint('credit_product_details','view_credit_note');
					if(!$hint){
						$hint = "";
					}
					$t_searched = 1;
		$agents_query = $this->Agents->find('list');
		$agents_query = $agents_query->hydrate(false);
		
		if(!empty($agents_query)){
			$agents = $agents_query->toArray();
		}
		$agents[0] = "Select Acc manager";
		ksort($agents);			
		
		$customerAgent =$customerBusiness = array();
		if(!empty($customerDetailArr)){
			foreach($customerDetailArr as $customerDetail){
			 //pr($customerDetail);die;
				//if(array_key_exists('Customer',$customerDetail)){
					$customerBusiness[$customerDetail['id']] = $customerDetail['business'];
					if($customerDetail['agent_id'] == 0){
						 $customerAgent[$customerDetail['id']] = "--";
					}else{
						 $customerAgent[$customerDetail['id']] = $agents[$customerDetail['agent_id']];
					}
					
				//}
			}
		}
					
					
		$this->set(compact('hint','creditPaymentDetails','customerBusiness','totalAmt','t_searched','kiosk_list','agents','customerAgent'));
		//$this->layout = 'default';
		$this->render('view_credit_note');
	} 	
	
	 public function tView($id = null){
		  //echo'hi';die;
		  $CURRENCY_TYPE = Configure::read('CURRENCY_TYPE');
		  $CreditReceiptSource = "t_credit_receipts";
		  $CreditProductDetailSource = "t_credit_product_details";
		  $CreditPaymentDetailSource = "t_credit_payment_details";
		  $CreditReceiptTable = TableRegistry::get($CreditReceiptSource,[
                                                                            'table' => $CreditReceiptSource,
                                                                        ]);
		  $CreditProductDetailTable = TableRegistry::get($CreditProductDetailSource,[
																					  'table' => $CreditProductDetailSource,
																				  ]);
		  $CreditPaymentDetailTable = TableRegistry::get($CreditPaymentDetailSource,[
                                                                                    'table' => $CreditPaymentDetailSource,
                                                                                ]);
		  //pr($this->request);die;
		  if (!$CreditReceiptTable->exists($id)) {
			  throw new NotFoundException(__('Invalid credit receipt'));
		  }
		  $countryOptions = Configure::read('uk_non_uk');
		  
		  $fullAddress = $kiosk_id = $kioskDetails = $kioskName = $kioskAddress1 = $kioskAddress2 = $kioskCity = $kioskState = $kioskZip  = $kioskZip = $kioskContact = $kioskCountry = $kioskTable = "";
		  if($this->request->session()->read('Auth.User.group_id') == KIOSK_USERS &&
			   $this->request->session()->read('Auth.User.username')=='wholesale'){
			   $kiosk_id = $this->request->Session()->read('kiosk_id');
			   $kioskDetails_query = $this->Kiosks->find('all',array('conditions'=>array('Kiosks.id'=>$kiosk_id),'fields'=>array('id','name','address_1','address_2','city','state','zip','contact','country')));
			   $kioskDetails_query = $kioskDetails_query->hydrate(false);
			   if(!empty($kioskDetails_query)){
				$kioskDetails = $kioskDetails_query->first();
			   }else{
				$kioskDetails = array();
			   }
			   $kioskName = $kioskDetails['name'];
			   $kioskAddress1 = $kioskDetails['address_1'];
			   $kioskAddress2 = $kioskDetails['address_2'];
			   $kioskCity = $kioskDetails['city'];
			   $kioskState = $kioskDetails['state'];
			   $kioskZip = $kioskDetails['zip'];
			   $kioskContact = $kioskDetails['contact'];
			   $kioskCountry = $kioskDetails['country'];
			  
			   if(!empty($kioskAddress1)){
				   $fullAddress.=$kioskAddress1.", ";
			   }
			  
			   if(!empty($kioskAddress2)){
				   $fullAddress.=$kioskAddress2.", ";
			   }
			  
			   if(!empty($kioskCity)){
				   $fullAddress.=$kioskCity.", ";
			   }
			  
			   if(!empty($kioskState)){
				   $fullAddress.=$kioskState.", ";
			   }
			  
			  if(!empty($kioskZip)){
				  $fullAddress.=$kioskZip.", ";
			  }
			  
			  if(!empty($kioskCountry)){
				  $fullAddress.=$countryOptions[$kioskCountry];
			  }
			  
			  $kioskTable = "<table>
			  <tr><td style='color: chocolate;'>".$kioskName."</td></tr>
			  <tr><td style='font-size: 11px;'>".$fullAddress."</td></tr>
			  </table>";
		  }
		  
		  $creditReceiptDetail_query = $CreditReceiptTable->find('all',array(
									  'conditions'=>array('id'=>$id)
									  )
									);
		  $creditReceiptDetail_query = $creditReceiptDetail_query->hydrate(false);
		  if(!empty($creditReceiptDetail_query)){
			   $creditReceiptDetail = $creditReceiptDetail_query->first();
		  }else{
			   $creditReceiptDetail = array();
		  }
		  //pr($creditReceiptDetail);die;
		  if(!empty($creditReceiptDetail)){
			   $customerID = $creditReceiptDetail['customer_id'];
			   $customer_query = $this->Customers->find('all',[
																 'conditions' => ['id' => $customerID]
															   ]);
			   $customer_query = $customer_query->hydrate(false);
			   if(!empty($customer_query)){
					$customer = $customer_query->first();
			   }else{
					$customer = array();
			   }
			   $creditProductDetailsID = $creditReceiptDetail['id'];
			   $creditProductDetails_query = $CreditProductDetailTable->find('all',[
																 'conditions' => ['credit_receipt_id' => $creditProductDetailsID]
															   ]);
			   $creditProductDetails_query = $creditProductDetails_query->hydrate(false);
			   if(!empty($creditProductDetails_query)){
					$creditProductDetails = $creditProductDetails_query->toArray();
			   }else{
					$creditProductDetails = array();
			   }
		  }else{
			   		$customer = array();
					$creditProductDetails = array();
		  }
		  //pr($customer);die;
		  $this->set(compact('customer','creditProductDetails'));
		  $userId = $creditReceiptDetail['processed_by'];
		  $userName_query = $this->Users->find('list',[
													   'conditions'=>['Users.id'=>$userId],
													   'keyField'=>'id',
													   'valueField'=>'username'
													  ]
											  );
		  $userName_query = $userName_query->hydrate(false);
		  if(!empty($userName_query)){
			   $userName = $userName_query->toArray();
		  }else{
			   $userName = array();
		  }
		  foreach($userName as $user_id => $user_name){break;}
		  $settingArr = $this->setting;
		  $vat = $this->VAT;
		  $productName_query = $this->Products->find('list',[
															'keyField' => 'id',
															'valueField'=>'product'
													   ]
						
											 );
		  $productName_query = $productName_query->hydrate(false);
		  if(!empty($productName_query)){
			   $productName = $productName_query->toArray();
		  }else{
			   $productName = array();
		  }
		  $productCode_query = $this->Products->find('list',[
															'keyField' => 'id',
															'valueField'=>'product_code'
													  ]
											  );
		  $productCode_query = $productCode_query->hydrate(false);
		  if(!empty($productCode_query)){
			   $productCode = $productCode_query->toArray();
		  }else{
			   $productCode = array();
		  }
		  $paymentDetails_query = $CreditPaymentDetailTable->find('all',[
																	  'conditions'=>['credit_receipt_id'=>$id]
																  ]
															);
		  $paymentDetails_query = $paymentDetails_query->hydrate(false);
		  if(!empty($paymentDetails_query)){
			   $paymentDetails = $paymentDetails_query->toArray();
		  }else{
			   $paymentDetails = array();
		  }
		  $payment_method = array();
		  foreach($paymentDetails as $key=>$paymentDetail){
			  $payment_method[] = $paymentDetail['payment_method']." ".$CURRENCY_TYPE.$paymentDetail['amount'];
		  }
		  //echo'hi';die;
		  //pr($this->request);die;
		  $send_by_email = Configure::read('send_by_email');
		  $emailSender = Configure::read('EMAIL_SENDER');
		  if(isset($this->request->data['send_receipt']) && $this->request->data['send_receipt'] == "Submit"){
			   //echo'hi';die;
			   if(isset($this->request->data['customer_email']) && !empty($this->request->data['customer_email'])){
					//echo'hi';die;
					$emailTo = $this->request->data['customer_email'];
					$Email = new Email();
					$Email->config('default');
					$Email->viewVars(array
										  (
										   'productReceipt' => $creditReceiptDetail,
										   'payment_method' => $payment_method,
										   'vat' => $vat,
										   'settingArr' =>$settingArr,
										   'user_name'=>$user_name,
										   'productName'=>$productName,
										   'productCode'=>$productCode,
										   'kioskContact'=>$kioskContact,
										   'kioskTable'=>$kioskTable,
										   'countryOptions'=>$countryOptions,
										   'customer'=>$customer,
										   'creditProductDetailsData'=>$creditProductDetails
										  )
									 );
					//$Email->config(array('from' => 'me@example.org', 'transport' => 'MyCustom'));
					//$Email = new CakeEmail(array('from' => 'me@example.org', 'transport' => 'MyCustom'));
					//$emailTo = $customerData['Customer']['email'];;
					$Email->template('credit_receipt');
					$Email->emailFormat('both');
					$Email->to($emailTo);
                    $Email->transport(TRANSPORT);
                    $Email->from([$send_by_email => $emailSender]);
				//	$Email->sender("sales@oceanstead.co.uk");
					$Email->subject('Credit Receipt');
					$Email->send();
					$this->Flash->success("Email send successfully");
			   }else{
					$this->Flash->error("Please enter email");
			   }
		  }
		  $this->set(compact('creditReceiptDetail','productName','paymentDetails','vat','settingArr','user_name','payment_method'));
		  $this->set(compact('kioskTable','kioskContact','countryOptions'));
		  //$this->render('view');
	 }
	 
	 public function tUpdateCreditPayment($paymentId = ''){
		  $CreditReceiptSource = "t_credit_receipts";
		  $CreditProductDetailSource = "t_credit_product_details";
		  $CreditPaymentDetailSource = "t_credit_payment_details";
		  $CreditReceiptTable = TableRegistry::get($CreditReceiptSource,[
                                                                            'table' => $CreditReceiptSource,
                                                                        ]);
		  $CreditProductDetailTable = TableRegistry::get($CreditProductDetailSource,[
																					  'table' => $CreditProductDetailSource,
																				  ]);
		  $CreditPaymentDetailTable = TableRegistry::get($CreditPaymentDetailSource,[
                                                                                    'table' => $CreditPaymentDetailSource,
                                                                                ]);
		  $paymentData_query = $CreditPaymentDetailTable->find('all',array(
							'conditions' => array('id'=>$paymentId)
								)
							  );
		  $paymentData_query = $paymentData_query->hydrate(false);
		  if(!empty($paymentData_query)){
			   $paymentData = $paymentData_query->first();
		  }else{
			   $paymentData = array();
		  }
		  $recipt_id = $paymentData['credit_receipt_id'];
		  $credit_cleared = $paymentData['credit_cleared'];
		  if($credit_cleared == 1){
			$this->Flash->error('This credit Note is Allready Processed!');
			 		$kiosk_id_to_set = $this->get_kiosk_for_credit("dr_credit_search");
				    return $this->redirect(array('action'=>"t_search?kiosk_id=$kiosk_id_to_set&payment_type=All&invoice_detail=receipt_number"));
			  die;
		  }
		  
		  $recipt_data_query = $CreditReceiptTable->find('all',array('conditions' => array('id' => $recipt_id)));
		  $recipt_data_query = $recipt_data_query->hydrate(false);
		  if(!empty($recipt_data_query)){
			   $recipt_data = $recipt_data_query->first();
		  }else{
			   $recipt_data = array();
		  }
		  $recit_created = $recipt_data['created'];
		  $note_or_quotation = 0;
		  $this->set(compact('recit_created','note_or_quotation'));
		 // pr($this->request);die;
		  if ($this->request->is(array('post', 'put'))){
			   if(array_key_exists("ticked",$this->request->data)){
					$ticked = $this->request->data['ticked'];
				}else{
					$ticked = 0;
				}
				
				$paymentMode = $this->request['data']['change_mode'];
					if($paymentMode=="Cheque"||
					   $paymentMode=="Cash"||
					   $paymentMode=="Bank Transfer"||
					   $paymentMode=="Card"
					   ){
						$paymentStatus = 1;
					}elseif($paymentMode=="On Credit"){
						$paymentStatus = 0;
					}
				
				if($ticked == 1){
					if(array_key_exists("date_box_date",$this->request->data)){
						 $date_box_date = date("Y-m-d G:i:s",strtotime($this->request->data['date_box_date']));
					}else{
						 $date_box_date = "";
					}
					if(array_key_exists('added_amount',$this->request->data) && is_numeric($this->request->data['added_amount']) && $this->request->data['added_amount'] > 0){
						 $sale_amt = round($this->request->data['sale_amount'],2);
						 $added_amt = round($this->request->data['added_amount'],2);
						 $old_amt = round($this->request->data['old_amt'],2);
						 $sum = $added_amt+$old_amt;
						 if((int)$sale_amt != (int)$sum){
							  $this->Flash->error('Payment could not be updated!');
							  return $this->redirect(array('action' => 't-update-credit-payment',$paymentId));
							  die;
						  }
						  
						  $new_paymentMode = $this->request->data['new_change_mode'];
						  $new_added_amount = $this->request->data['added_amount'];
						 if($new_paymentMode=="Cheque"||
							  $new_paymentMode=="Cash"||
							  $new_paymentMode=="Bank Transfer"||
							  $new_paymentMode=="Card"
							  ){
							   $paymentStatus = 1;
						 }elseif($new_paymentMode=="On Credit"){
							  $paymentStatus = 0;
						 }
						 $new_box_desc = "";
						 if(array_key_exists('new_box_desc',$this->request->data)){
							  $new_box_desc = $this->request->data['new_box_desc'];
						 }
						 
						 
						 $paymentDetailData = array(
								  'payment_method' => $new_paymentMode,
								  'payment_status' => $paymentStatus,
								  'credit_receipt_id' => $paymentData['credit_receipt_id'],
								  'kiosk_id' =>$paymentData['kiosk_id'],
								  'amount' => $new_added_amount,
								  'description' => $new_box_desc,
								  'created' => $date_box_date,
									  );
						 $CreditPaymentDetailTable->behaviors()->load('Timestamp');
						 $credit_entity = $CreditPaymentDetailTable->newEntity();
						 $credit_entity = $CreditPaymentDetailTable->patchEntity($credit_entity,$paymentDetailData,['validate'=>false]);
						 $CreditPaymentDetailTable->save($credit_entity);
						 
						 $desc = "";
						 if(array_key_exists('desc',$this->request->data)){
							  $desc = $this->request->data['desc'];
						 }
						 
						 
						 $old_amount = $this->request->data['old_amt'];
						 $paymentDetailData = array(
													   'id' => $paymentId,
													   'amount' => $old_amount,
													   'payment_method' => $paymentMode,
													   'payment_status' => $paymentStatus,
													   'description' => $desc,
													   'created' => $date_box_date,
												  );
						 $getId = $CreditPaymentDetailTable->get($paymentId);
						 $patchEntity = $CreditPaymentDetailTable->patchEntity($getId,$paymentDetailData,['validate' =>false]);
						 $CreditPaymentDetailTable->save($patchEntity);
					}
					
					$desc = "";
						 if(array_key_exists('desc',$this->request->data)){
							  $desc = $this->request->data['desc'];
						 }
					$created  = date("Y-m-d G:i:s");
					if($paymentData['payment_method'] == "On Credit"){   // changing created when changing payment method from  on-credit to any other
						$paymentDetailData = array(
								'id' => $paymentId,
								'payment_method' => $paymentMode,
								'payment_status' => $paymentStatus,
								'description' => $desc,
								'created' => $created,
								'created' => $date_box_date,
								   );
					}
					
					if($paymentMode == "On Credit"){  //when changed payment method is On Credit add recit date to payment table created
						$paymentDetailData = array(
								'id' => $paymentId,
								'payment_method' => $paymentMode,
								'payment_status' => $paymentStatus,
								'description' => $desc,
								'created' => $recit_created
								   );
					}
					
					if(empty($paymentDetailData)){
						$paymentDetailData = array(
								'id' => $paymentId,
								'payment_method' => $paymentMode,
								'payment_status' => $paymentStatus,
								'description' => $desc,
								'created' => $date_box_date,
								   );
					}
				}else{
					if(array_key_exists('added_amount',$this->request->data) && is_numeric($this->request->data['added_amount']) && $this->request->data['added_amount'] > 0){
						 $sale_amt = round($this->request->data['sale_amount'],2);
						 $added_amt = round($this->request->data['added_amount'],2);
						 $old_amt = round($this->request->data['old_amt'],2);
						 $sum = $added_amt+$old_amt;
						 if((int)$sale_amt != (int)$sum){
							  $this->Flash->error('Payment could not be updated!');
							  return $this->redirect(array('action' => 't-update-credit-payment',$paymentId));
							  die;
						  }
						  
						  $new_paymentMode = $this->request->data['new_change_mode'];
						  $new_added_amount = $this->request->data['added_amount'];
						 if($new_paymentMode=="Cheque"||
							  $new_paymentMode=="Cash"||
							  $new_paymentMode=="Bank Transfer"||
							  $new_paymentMode=="Card"
							  ){
							   $paymentStatus = 1;
						 }elseif($new_paymentMode=="On Credit"){
							  $paymentStatus = 0;
						 }
						 
						  $new_box_desc = "";
						 if(array_key_exists('new_box_desc',$this->request->data)){
							  $new_box_desc = $this->request->data['new_box_desc'];
						 }
						 
						 
						 $paymentDetailData = array(
								  'payment_method' => $new_paymentMode,
								  'payment_status' => $paymentStatus,
								  'credit_receipt_id' => $paymentData['credit_receipt_id'],
								  'kiosk_id' =>$paymentData['kiosk_id'],
								  'amount' => $new_added_amount,
								  'description' => $new_box_desc
									  );
						 $CreditPaymentDetailTable->behaviors()->load('Timestamp');
						 $credit_entity = $CreditPaymentDetailTable->newEntity();
						 $credit_entity = $CreditPaymentDetailTable->patchEntity($credit_entity,$paymentDetailData,['validate'=>false]);
						 $CreditPaymentDetailTable->save($credit_entity);
						 
						 $old_amount = $this->request->data['old_amt'];
						 
						 $desc = "";
						 if(array_key_exists('desc',$this->request->data)){
							  $desc = $this->request->data['desc'];
						 }
						 
						 $paymentDetailData = array(
													   'id' => $paymentId,
													   'amount' => $old_amount,
													   'payment_method' => $paymentMode,
													   'payment_status' => $paymentStatus,
													   'description' => $desc
												  );
						 $getId = $CreditPaymentDetailTable->get($paymentId);
						 $patchEntity = $CreditPaymentDetailTable->patchEntity($getId,$paymentDetailData,['validate' =>false]);
						 $CreditPaymentDetailTable->save($patchEntity);
					}

					$desc = "";
						 if(array_key_exists('desc',$this->request->data)){
							  $desc = $this->request->data['desc'];
						 }
					
					if($paymentMode == "On Credit"){  //when changed payment method is On Credit add recit date to payment table created
						 $paymentDetailData = array(
								 'id' => $paymentId,
								 'payment_method' => $paymentMode,
								 'payment_status' => $paymentStatus,
								 'created' => $recit_created,
								 'description' => $desc
									);
					 }
					 if(empty($paymentDetailData)){
						 $paymentDetailData = array(
								 'id' => $paymentId,
								 'payment_method' => $paymentMode,
								 'payment_status' => $paymentStatus,
								 'description' => $desc
									);
					 }
				}
			   
			   //pr($this->request);die;
			 $CreditPaymentDetailTable->behaviors()->load('Timestamp');
			$get_ID = $CreditPaymentDetailTable->get($paymentId);
			$patch_Entity = $CreditPaymentDetailTable->patchEntity($get_ID,$paymentDetailData,['validate' => false]);
            // pr($patch_Entity);die;
			if($CreditPaymentDetailTable->save($patch_Entity)){
			   
			   
                    if($this->request->Session()->read('Auth.User.group_id') == ADMINISTRATORS){
                        $kiosk_id_to_set = $this->get_kiosk_for_credit("dr_credit_search");
				    
                        $this->Flash->success("Payment method has been updated");
                        return $this->redirect(array('action'=>"t_search?kiosk_id=$kiosk_id_to_set&payment_type=All&invoice_detail=receipt_number"));
                    }else{
                        $this->Flash->success("Payment method has been updated");
                        return $this->redirect(array('action'=>'t_view_credit_note'));
                    }
                
                
					//$this->Flash->success("Payment method has been updated to {$paymentMode}");
					//return $this->redirect(array('action'=>'t_view_credit_note'));
			   }
		  }
		  
		  $this->set(compact('paymentData'));
		  $this->render('update_credit_payment');
	 }
	 public function changeCustomer($recipt_id,$passed_kiosk_id = ""){
        if(!empty($passed_kiosk_id)){
			if($passed_kiosk_id == 10000){
				$kiosk_id = "";
			}else{
				$kiosk_id = $passed_kiosk_id;
			}
		}else{
			$kiosk_id = $this->request->Session()->read('kiosk_id');
		}
        if(!empty($kiosk_id)){
		  $recipt = "kiosk_{$kiosk_id}_credit_receipts";
		  $sales = "kiosk_{$kiosk_id}_credit_product_details";
		  $payment = "kiosk_{$kiosk_id}_credit_payment_details";
		}else{
			   $recipt = "credit_receipts";
			   $sales = "credit_product_details";
			   $payment = "credit_payment_details";
		}
		$CreditReceiptTable = TableRegistry::get($recipt,[
                                                            'table' => $recipt,
                                                ]);
		$CreditProductDetailTable = TableRegistry::get($sales,[
															 'table' => $sales,
													 ]);
		$CreditPaymentDetailTable = TableRegistry::get($payment,[
                                                          'table' => $payment,
                                                     ]);
        $customer_res_query = $this->Customers->find('all',array(
														  'fields' => array('id','fname','country','lname','business'),
														 // 'recursive'=>-1
														  ));
        $customer_res_query = $customer_res_query->hydrate(false);
		if(!empty($customer_res_query)){
		  $customer_res = $customer_res_query->toArray();
		}else{
		  $customer_res = array();
		}
        $recipt_res_query = $CreditReceiptTable->find('all',array('conditions' => array(
																	  'id'=> $recipt_id,
																	  )));
        $recipt_res_query = $recipt_res_query->hydrate(false);
		if(!empty($recipt_res_query)){
		  $recipt_res = $recipt_res_query->toArray();
		}else{
		  $recipt_res = array();
		}
		$credit_amt = $recipt_res[0]['credit_amount'];
		$this->set(compact('credit_amt'));
        $credit_detail_res_query = $CreditProductDetailTable->find('all',[
                                                                'conditions' =>  ['credit_receipt_id'=> $recipt_id],
                                                                //'contain' => ['CreditProductDetails']
                                                            ]);
		$credit_detail_res_query = $credit_detail_res_query->hydrate(false);
		if(!empty($credit_detail_res_query)){
		  $credit_detail_res = $credit_detail_res_query->toArray();
		}else{
		  $credit_detail_res = array();
		}
        
         $credit_payment_res_query = $CreditPaymentDetailTable->find('all',[
                                                                'conditions' =>  ['credit_receipt_id'=> $recipt_id],
                                                                //'contain' => ['CreditProductDetails']
                                                                      
                                                            ]);
		 $credit_payment_res_query = $credit_payment_res_query->hydrate(false);
		if(!empty($credit_payment_res_query)){
		  $credit_payment_res = $credit_payment_res_query->toArray();
		}else{
		  $credit_payment_res = array();
		}
		
        //$recipt_res['credit_product_details'] = $credit_detail_res;
        //$recipt_res['credit_payment_details'] = $credit_payment_res;
       //pr($recipt_res);die;
		if(!empty($recipt_res)){
			$old_customer_id = $recipt_res[0]['customer_id'];
		}
        $customer_country = $customer_Arr = array();
		$customer_first_name = $customer_last_name = $customer_bussiness = "";
       // pr($customer_res);die;
        foreach($customer_res as $key => $value){
			if($value['id'] == $old_customer_id){
				$customer_first_name = $value['fname'];
				$customer_last_name = $value['lname'];
				$customer_bussiness = $value['business'];
			}
			$customer_Arr[$value['id']] = $value['fname']."(".$value['country'].")";
			$customer_country[$value['id']] = $value['country'];
		}
        $this->paginate = array(
							'conditions' => array('system_user' => 0),
							'limit' => 50
							);
		$customers = $this->paginate('Customers');
      //  pr($customers);die;
		$this->set(compact('customers'));
		$this->set(compact('customer_first_name','customer_last_name','customer_bussiness','old_customer_id','kiosk_id','recipt_id'));
        if($this->request->is('Post') && array_key_exists('customer',$this->request->data)){
           
            $new_customer_id = $this->request->data['customer'];
			$new_customer_res_query = $this->Customers->find('all',array(
															'conditions' => array('id' => $new_customer_id),
														 
														  ));
			$new_customer_res_query = $new_customer_res_query->hydrate(false);
			if(!empty($new_customer_res_query)){
			   $new_customer_res = $new_customer_res_query->first();
			}else{
			   $new_customer_res = array();
			}
            if($new_customer_id != $old_customer_id){
             
				if($customer_country[$new_customer_id] != $customer_country[$old_customer_id]){
                  
                   // pr($recipt_res);die;
					$kiosk_product_sale_data = $credit_detail_res;
						if(!empty($kiosk_product_sale_data)){
                           	$total_sale_price = 0;
							$vat = $this->VAT;
                          //  pr($kiosk_product_sale_data);die;
							foreach($kiosk_product_sale_data as $s_key => $s_value){
                                
								if($s_value['discount']){
                                  //  echo "if";die;
									$discount = $s_value['discount'];
									$sale_price_with_vat = $s_value['sale_price'];
									$sale_price =  $sale_price_with_vat/(1+($vat/100));
									$after_discount_price = $sale_price - ($sale_price*$discount/100);
									$total_price = $after_discount_price*$s_value['quantity'];
								}else{
                                   	$sale_price_with_vat = $s_value['sale_price'];
									$sale_price =  $sale_price_with_vat/(1+($vat/100));
									$total_price = $sale_price*$s_value['quantity'];
								}	
								$total_sale_price += $total_price;
							}
						}
                        $bulk_dis = $recipt_res[0]['bulk_discount'];
						if($bulk_dis > 0){
							$total_sale_price = $total_sale_price - $total_sale_price*($bulk_dis/100);
						}
					if($customer_country[$new_customer_id] == "OTH"){  // if changed to other country which mens no vat
						 $selected_cutomer_id = $this->request->data['customer'];
                         //echo "3";die;
						//$vat = $this->VAT;
						//$vat_amount = $total_sale_price*($vat/100);
						$final_amt = round($total_sale_price,2);
						$res = $credit_payment_res;
						$this->set(compact('res','final_amt','selected_cutomer_id','kiosk_id'));
						$this->render("payment_screen_cst_change");
					}else{ // same country mns will have vat
                       // echo "4";die;
						$vat = $this->VAT;
						 $vat_amount = $total_sale_price*($vat/100);
						$after_vat_value = $total_sale_price + $vat_amount;
						
						$selected_cutomer_id = $this->request->data['customer'];
						$final_amt = round($after_vat_value,2);
						$res = $credit_payment_res;
						$this->set(compact('res','final_amt','selected_cutomer_id','kiosk_id'));
						$this->render("payment_screen_cst_change");
					}
				}else{
                   // echo "zxzc";die;
					$product_recipt_data = array(
													 'id' => $recipt_id,
													 'customer_id' => $new_customer_id,
													 'fname' => $new_customer_res['fname'],
													 'lname' => $new_customer_res['lname'],
													 'email' => $new_customer_res['email'],
													 'mobile' => $new_customer_res['mobile'],
													 'address_1' => $new_customer_res['address_1'],
													 'address_2' => $new_customer_res['address_2'],
													 'city' => $new_customer_res['city'],
													 'state' => $new_customer_res['state'],
													 'zip' => $new_customer_res['zip'],
													 'agent_id' => $new_customer_res['agent_id']
													);
                  //  pr($product_recipt_data);die;
                  
                      $CreditReceiptEntity = $CreditReceiptTable->get($recipt_id);
                      $CreditReceiptPatchEntity = $CreditReceiptTable->patchEntity($CreditReceiptEntity,$product_recipt_data);
					if($CreditReceiptTable->save($CreditReceiptPatchEntity)){
						 if(!empty($credit_payment_res)){
							  foreach($credit_payment_res as $key => $value){
								   $credit_pay_id = $value['id'];
								   $pay_data = array(
												'agent_id' => $new_customer_res['agent_id']
												);
								   $CreditPaymentEntity = $CreditPaymentDetailTable->get($credit_pay_id);
								   $CreditPaymentPatchEntity = $CreditPaymentDetailTable->patchEntity($CreditPaymentEntity,$pay_data);
								   $CreditPaymentDetailTable->save($CreditPaymentPatchEntity) ;
							  }
						 }
                       	if($this->request->Session()->read('Auth.User.group_id') == ADMINISTRATORS){
								 $kiosk_id_to_set = $this->get_kiosk_for_credit("credit_search");//
                                $this->Flash->success("customer has been changed for Invoice ID $recipt_id");
								
								return $this->redirect(array('action'=>"search?kiosk_id=$kiosk_id_to_set&payment_type=All&invoice_detail=receipt_number"));
							}else{
                                $this->Flash->success("customer has been changed for Invoice ID $recipt_id");
								//$this->Session->setFlash(__("customer has been changed for Invoice ID $recipt_id"));
								return $this->redirect(array('action'=>'view_credit_note'));
							}
					}else{
                        //echo "hhh2";die;
                    }
				}
            }
        }elseif( $this->request->is('Post') && array_key_exists('payment',$this->request->data['data'])){
           // pr($this->request);die;
			  $selected_customer = $this->request->data['selected_customer']; 
			$finalAmt = $this->request->data['sale_amount'];
			$payArr = $this->request->data['data']['payment'];
			$payment_total = array_sum($payArr);
			if($payment_total != $finalAmt){
				$final_amt = $finalAmt;
				$res = $recipt_res;
				$selected_cutomer_id = $selected_customer;
				$this->set(compact('res','final_amt','selected_cutomer_id','kiosk_id'));
				$this->render("payment_screen_cst_change");
                 $this->Flash->success("amount is not matching");
				 
				return $this->redirect(array('action'=>'payment_screen_cst_change'));
			}
            $recipt_res = $credit_payment_res;
         //  pr($recipt_res);die;
		 
		    $new_customer_result_query = $this->Customers->find('all',array(
															'conditions' => array('id' => $selected_customer),
														//  'recursive'=>-1
														  ));
            $new_customer_result_query = $new_customer_result_query->hydrate(false);
            if(!empty($new_customer_result_query)){
                $new_customer_result = $new_customer_result_query->First();
            }else{
                $new_customer_result  = array();
            } 
		 
			foreach($recipt_res as $s_key1 => $value1){
					if(array_key_exists($value1['id'],$payArr)){
						if($payArr[$value1['id']] != $value1['amount']){
							$pay_data = array(
												'amount' => $payArr[$value1['id']],
												'agent_id' => $new_customer_result['agent_id']
												);
							  $CreditPaymentEntity = $CreditPaymentDetailTable->get($value1['id']);
                             $CreditPaymentPatchEntity = $CreditPaymentDetailTable->patchEntity($CreditPaymentEntity,$pay_data);
                               $CreditPaymentDetailTable->save($CreditPaymentPatchEntity) ;
							 
						}
					}
			}
			
            
			// pr($new_customer_result);die;
			if($new_customer_result['country'] == "OTH"){
				$vat = "";
			}else{
				$vat = $this->VAT;
			}
			$product_recipt_data = array(
														'id' => $recipt_id,
														'customer_id' => $selected_customer,
														'fname' => $new_customer_result['fname'],
														'lname' => $new_customer_result['lname'],
														'email' => $new_customer_result['email'],
														'mobile' => $new_customer_result['mobile'],
														'address_1' => $new_customer_result['address_1'],
														'address_2' => $new_customer_result['address_2'],
														'city' => $new_customer_result['city'],
														'state' => $new_customer_result['state'],
														'zip' => $new_customer_result['zip'],
														//'vat' => $vat,
														'credit_amount' => $finalAmt,
														//'bill_amount' => $finalAmt,
														//'orig_bill_amount' => $finalAmt,
														 'agent_id' => $new_customer_result['agent_id']
														);
            $CreditReceiptEntity = $CreditReceiptTable->get($recipt_id);
            $CreditReceiptPatchEntity = $CreditReceiptTable->patchEntity($CreditReceiptEntity,$product_recipt_data);
			if($CreditReceiptTable->save($CreditReceiptPatchEntity)){
				if($this->request->Session()->read('Auth.User.group_id') == ADMINISTRATORS){
					$kiosk_id_to_set = $this->get_kiosk_for_credit("credit_search");
                       $this->Flash->success("customer has been changed for Invoice ID $recipt_id");
					return $this->redirect(array('action'=>"search?kiosk_id=$kiosk_id_to_set&payment_type=All&invoice_detail=receipt_number"));
				}else{
                     $this->Flash->success("customer has been changed for Invoice ID $recipt_id");
					 
					return $this->redirect(array('action'=>'view_credit_note'));
				}
			}
		}else{
			$this->set(compact('customer_Arr','recipt_id','old_customer_id','kiosk_id'));
		}
     } 
	 
	 public function changeCustomer1($recipt_id,$passed_kiosk_id = ""){
		
		if(!empty($passed_kiosk_id)){
			if($passed_kiosk_id == 10000){
				$kiosk_id = "";
			}else{
				$kiosk_id = $passed_kiosk_id;
			}
		}else{
			$kiosk_id = $this->request->Session()->read('kiosk_id');
		}
		
		if(!empty($kiosk_id)){
		  $recipt = "kiosk_{$kiosk_id}_credit_receipts";
		  $sales = "kiosk_{$kiosk_id}_credit_product_details";
		  $payment = "kiosk_{$kiosk_id}_credit_payment_details";
		}else{
			   $recipt = "credit_receipts";
			   $sales = "credit_product_details";
			   $payment = "credit_payment_details";
		}
		$CreditReceiptTable = TableRegistry::get($recipt,[
                                                                            'table' => $recipt,
                                                                        ]);
		$CreditProductDetailTable = TableRegistry::get($sales,[
																					  'table' => $sales,
																				  ]);
		$CreditPaymentDetailTable = TableRegistry::get($payment,[
                                                                                    'table' => $payment,
                                                                                ]);
		
		$customer_res_query = $this->Customers->find('all',array(
														  'fields' => array('id','fname','country','lname','business'),
														  
														  ));
		
		$customer_res_query = $customer_res_query->hydrate(false);
		if(!empty($customer_res_query)){
		  $customer_res = $customer_res_query->toArray();
		}else{
		  $customer_res = array();
		}
       // pr($customer_res);die;
		$recipt_res_query = $CreditReceiptTable->find('all',[
                                                                'conditions' =>  ['id'=> $recipt_id],
                                                                //'contain' => ['CreditProductDetails']
                                                                      
                                                            ]);
       $recipt_res_query = $recipt_res_query->hydrate(false);
		if(!empty($recipt_res_query)){
		  $recipt_res = $recipt_res_query->first();
		}else{
		  $recipt_res = array();
		}
        $credit_detail_res_query = $CreditProductDetailTable->find('all',[
                                                                'conditions' =>  ['credit_receipt_id'=> $recipt_id],
                                                                //'contain' => ['CreditProductDetails']
                                                                      
                                                            ]);
		 $credit_detail_res_query = $credit_detail_res_query->hydrate(false);
		if(!empty($credit_detail_res_query)){
		  $credit_detail_res = $credit_detail_res_query->toArray();
		}else{
		  $credit_detail_res = array();
		}
        
         $credit_payment_res_query = $CreditPaymentDetailTable->find('all',[
                                                                'conditions' =>  ['credit_receipt_id'=> $recipt_id],
                                                                //'contain' => ['CreditProductDetails']
                                                                      
                                                            ]);
		 $credit_payment_res_query = $credit_payment_res_query->hydrate(false);
		if(!empty($credit_payment_res_query)){
		  $credit_payment_res = $credit_payment_res_query->toArray();
		}else{
		  $credit_payment_res = array();
		}
       //pr($recipt_res);die;
		if(!empty($recipt_res)){
			$old_customer_id = $recipt_res['customer_id'];
		}
       // pr($recipt_res);die;
		$customer_country = $customer_Arr = array();
		$customer_first_name = $customer_last_name = $customer_bussiness = "";
		foreach($customer_res as $key => $value){
			if($value['id'] == $old_customer_id){
				$customer_first_name = $value['fname'];
				$customer_last_name = $value['lname'];
				$customer_bussiness = $value['business'];
			}
			$customer_Arr[$value['id']] = $value['fname']."(".$value['country'].")";
			$customer_country[$value['id']] = $value['country'];
		}
		$this->paginate = array(
							'conditions' => array('system_user' => 0),
							'limit' => 50
							);
		$customers = $this->paginate('Customers');
		$this->set(compact('customers'));
		$this->set(compact('customer_first_name','customer_last_name','customer_bussiness','old_customer_id','kiosk_id','recipt_id'));
		if($this->request->is('Post') && array_key_exists('customer',$this->request->data)){
           // echo "hi";die;
			$new_customer_id = $this->request->data['customer'];
			$new_customer_res_query = $this->Customers->find('all',array(
															'conditions' => array('id' => $new_customer_id),
														 
														  ));
			$new_customer_res_query = $new_customer_res_query->hydrate(false);
			if(!empty($new_customer_res_query)){
			   $new_customer_res = $new_customer_res_query->first();
			}else{
			   $new_customer_res = array();
			}
            
			if($new_customer_id != $old_customer_id){
                
				if($customer_country[$new_customer_id] != $customer_country[$old_customer_id]){
                   // pr($recipt_res);die;
                    $recipt_res['credit_product_details'] = $credit_detail_res;
                    $recipt_res['credit_payment_details'] = $credit_payment_res;
                  // $kiosk_product_sale_data = $recipt_res ;
					   $kiosk_product_sale_data = $credit_detail_res;
						if(!empty($kiosk_product_sale_data)){
							$total_sale_price = 0;
							$vat = $this->VAT;
                 //pr($kiosk_product_sale_data );// die;
							foreach($kiosk_product_sale_data as $s_key => $s_value){
                               // pr($s_value);
                                if($s_value['discount']){
									$discount = $s_value['discount'];
									$sale_price_with_vat = $s_value['sale_price'];
									$sale_price =  $sale_price_with_vat/(1+($vat/100));
									$after_discount_price = $sale_price - ($sale_price*$discount/100);
									$total_price = $after_discount_price*$s_value['quantity'];
								}else{
									$sale_price_with_vat = $s_value['sale_price'];
									$sale_price =  $sale_price_with_vat/(1+($vat/100));
									$total_price = $sale_price*$s_value['quantity'];
								}	
								$total_sale_price += $total_price;
							}
						}
				   //echo $total_sale_price;die;
					if($customer_country[$new_customer_id] == "OTH"){  // if changed to other country which mens no vat
						$selected_cutomer_id = $this->request->data['customer'];
						//$vat = $this->VAT;
						//$vat_amount = $total_sale_price*($vat/100);
						$final_amt = round($total_sale_price,2);
                       
						$res = $recipt_res;
                        // pr($res);die;
                        // $res_query = $res_query->hydrate(false);
                        
						$this->set(compact('res','final_amt','selected_cutomer_id','kiosk_id'));
						$this->render("payment_screen_cst_change");
					}else{ // same country mns will have vat
						$vat = $this->VAT;
						 $vat_amount = $total_sale_price*($vat/100);
						$after_vat_value = $total_sale_price + $vat_amount;
						
						$selected_cutomer_id = $this->request->data['customer'];
						$final_amt = round($after_vat_value,2);
						$res = $recipt_res;
						$this->set(compact('res','final_amt','selected_cutomer_id','kiosk_id'));
						$this->render("payment_screen_cst_change");
					}
				}else{
                  //echo "hkjzjhfjdi";die; 
					$product_recipt_data = array(
													 'id' => $recipt_id,
													 'customer_id' => $new_customer_id,
													 'fname' => $new_customer_res['fname'],
													 'lname' => $new_customer_res['lname'],
													 'email' => $new_customer_res['email'],
													 'mobile' => $new_customer_res['mobile'],
													 'address_1' => $new_customer_res['address_1'],
													 'address_2' => $new_customer_res['address_2'],
													 'city' => $new_customer_res['city'],
													 'state' => $new_customer_res['state'],
													 'zip' => $new_customer_res['zip']
													);
                   // pr($product_recipt_data);die;
                  
                      $CreditReceiptEntity = $CreditReceiptTable->get($recipt_id);
                      $CreditReceiptPatchEntity = $CreditReceiptTable->patchEntity($CreditReceiptEntity,$product_recipt_data);
					if($CreditReceiptTable->save($CreditReceiptPatchEntity)){
                      //  echo "hh";die;
							if($this->request->Session()->read('Auth.User.group_id') == ADMINISTRATORS){
								$kiosk_id_to_set = $this->get_kiosk_for_credit("credit_search");
                                $this->Flash->success("customer has been changed for Invoice ID $recipt_id");
								
								return $this->redirect(array('action'=>"search?kiosk_id=$kiosk_id_to_set&payment_type=All&invoice_detail=receipt_number"));
							}else{
								$this->Session->setFlash(__("customer has been changed for Invoice ID $recipt_id"));
								return $this->redirect(array('action'=>'view_credit_note'));
							}
					}
				}
			}
		}elseif($this->request->is('Post') && array_key_exists('payment',$this->request->data)){
			$selected_customer = $this->request->data['selected_customer'];
			$finalAmt = $this->request->data['sale_amount'];
			$payArr = $this->request->data['payment'];
			$payment_total = array_sum($payArr);
			if($payment_total != $finalAmt){
				$final_amt = $finalAmt;
				$res = $recipt_res;
				$selected_cutomer_id = $selected_customer;
				$this->set(compact('res','final_amt','selected_cutomer_id','kiosk_id'));
				$this->render("payment_screen_cst_change");
				$this->Session->setFlash(__("amount is not matching"));
				return $this->redirect(array('action'=>'payment_screen_cst_change'));
			}
			foreach($recipt_res as $s_key1 => $value1){
				foreach($value1['CreditPaymentDetail'] as $y => $data){
					if(array_key_exists($data['id'],$payArr)){
						if($payArr[$data['id']] != $data['amount']){
							$pay_data = array(
												'id' => $data['id'],
												'amount' => $payArr[$data['id']]
												);
							//pr($pay_data);die;
							$this->CreditPaymentDetail->save($pay_data);
						}
					}
				}
			}
			$new_customer_result = $this->Customer->find('first',array(
															'conditions' => array('id' => $selected_customer),
														  'recursive'=>-1
														  ));
			//pr($new_customer_result);die;
			if($new_customer_result['Customer']['country'] == "OTH"){
				$vat = "";
			}else{
				$vat = $this->VAT;
			}
			$product_recipt_data = array(
														'id' => $recipt_id,
														'customer_id' => $selected_customer,
														'fname' => $new_customer_result['Customer']['fname'],
														'lname' => $new_customer_result['Customer']['lname'],
														'email' => $new_customer_result['Customer']['email'],
														'mobile' => $new_customer_result['Customer']['mobile'],
														'address_1' => $new_customer_result['Customer']['address_1'],
														'address_2' => $new_customer_result['Customer']['address_2'],
														'city' => $new_customer_result['Customer']['city'],
														'state' => $new_customer_result['Customer']['state'],
														'zip' => $new_customer_result['Customer']['zip'],
														//'vat' => $vat,
														'credit_amount' => $finalAmt
														//'bill_amount' => $finalAmt,
														//'orig_bill_amount' => $finalAmt,
														);
			if($this->CreditReceipt->save($product_recipt_data)){
				if(AuthComponent::user('group_id') == ADMINISTRATORS){
					$kiosk_id_to_set = $this->get_kiosk_for_credit("credit_search");
					$this->Session->setFlash(__("customer has been changed for Invoice ID $recipt_id"));
					return $this->redirect(array('action'=>"search?kiosk_id=$kiosk_id_to_set&payment_type=All&invoice_detail=receipt_number"));
				}else{
					$this->Session->setFlash(__("customer has been changed for Invoice ID $recipt_id"));
					return $this->redirect(array('action'=>'view_credit_note'));
				}
			}
		}else{
			$this->set(compact('customer_Arr','recipt_id','old_customer_id','kiosk_id'));
		}
	}
	
	 public function searchCustomer(){
			   //pr($this->request->query);die;
			   $kiosk_id = $this->request->query['kiosk_id'];
			   $recipt_id = $this->request->query['recipt_id'];
			   $search_kw = $this->request->query['search_kw'];
			   $old_customer_id = $this->request->query['old_customer_id'];
			   $customer_res_query = $this->Customers->find('all',array(
															   'conditions' => array('id' => $old_customer_id),
																 'fields' => array('id','fname','country','lname','business'),
																 'recursive'=>-1
																 ));
			   $customer_res_query = $customer_res_query->hydrate(false);
			   if(!empty($customer_res_query)){
					$customer_res = $customer_res_query->first();
			   }else{
					$customer_res = array();
			   }
			   
			   if(!empty($kiosk_id)){
					$recipt = "kiosk_{$kiosk_id}_credit_receipts";
					$sales = "kiosk_{$kiosk_id}_credit_product_details";
					$payment = "kiosk_{$kiosk_id}_credit_payment_details";
				  }else{
						 $recipt = "credit_receipts";
						 $sales = "credit_product_details";
						 $payment = "credit_payment_details";
				  }
				  $CreditReceiptTable = TableRegistry::get($recipt,[
																					  'table' => $recipt,
																				  ]);
				  $CreditProductDetailTable = TableRegistry::get($sales,[
																								'table' => $sales,
																							]);
				  $CreditPaymentDetailTable = TableRegistry::get($payment,[
																							  'table' => $payment,
																						  ]);
		
			   $recipt_res_query = $CreditReceiptTable->find('all',array('conditions' => array(
																	  'id'=> $recipt_id,
																	  )));
			   $recipt_res_query = $recipt_res_query->hydrate(false);
			   if(!empty($recipt_res_query)){
				 $recipt_res = $recipt_res_query->toArray();
			   }else{
				 $recipt_res = array();
			   }
			   $credit_amt = $recipt_res[0]['credit_amount'];
			   $this->set(compact('credit_amt'));
			   
			   
			   if(!empty($customer_res)){
				   $customer_first_name = $customer_res['fname'];
				   $customer_last_name = $customer_res['lname'];
				   $customer_bussiness = $customer_res['business'];
			   }
			   if(!empty($search_kw)){
				   $conditionArr = array();
				   if(!empty($search_kw)){
					   $search_kw = trim($search_kw);
					   //$conditionArr['Customer.brand like'] =  strtolower("%$searchKW%");
					   $conditionArr ['OR']['Customers.fname like'] = strtolower("%$search_kw%");
					   $conditionArr ['OR']['Customers.email like'] = strtolower("%$search_kw%");
					   $conditionArr ['OR']['Customers.mobile like'] = strtolower("%$search_kw%");
					   $conditionArr ['OR']['Customers.business  like'] = strtolower("%$search_kw%");
				   }
				   $this->paginate = array(
								   'conditions' => array($conditionArr,'system_user' => 0),
								   'limit' => 50
								   );
				   $customers = $this->paginate('Customers');
				   $this->set(compact('customers','kiosk_id'));
			   }else{
				   $this->paginate = array(
									   'conditions' => array('system_user' => 0),
									   'limit' => 50
									   );
				   $customers = $this->paginate('Customers');
				   $this->set(compact('customers','kiosk_id'));
			   }
			   $this->set(compact('customer_first_name','customer_last_name','customer_bussiness','old_customer_id','recipt_id','kiosk_id'));
			   $this->render('change_customer');
	 }
	
	 public function adminData($search = ""){
		if(array_key_exists('search',$this->request->query)){
			$search = strtolower($this->request->query['search']);
		}
		
		$catgoryArr = array();
		if(array_key_exists('category',$this->request->query)){
			$catgoryArr = explode(",",$this->request->query['category']);
		}
		
		
		//change product resource at run time
		//quantity should be more than 0
		if(!empty($search)){
			/*$productList = $this->Product->find('all',array(
															'fields'=> array('product','product_code'),
															'recursive'=> -1,
															'conditions' => array(
																				  "LOWER(`Product`.`product`) like '%$search%'"
																				  )
												)
						    );*/
			ob_start();
			$this->pc_permute(explode(' ', $search));
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
				$searchArray['OR'][] = "LOWER(`Products`.`product`) like '%".str_replace(" ","%",$value)."%'";
				//removing 0 value from array which is for all
				
			}
			if(count($newCatArr) >= 1){
				$searchArray['category_id'] = $newCatArr;
			}
			$productList_query = $this->Products->find('all',array(
															'fields'=> array('product','product_code'),
															'conditions' => $searchArray
												)
						    );
			$productList_query = $productList_query->hydrate(false);
			if(!empty($productList_query)){
			   $productList = $productList_query->toArray();
			}else{
			   $productList = array();
			}
		}else{
			$productList_query = $this->Products->find('all',array(
													'fields'=> array('product','product_code'),
													'conditions' => array(),
											)
						    );
			$productList_query = $productList_query->hydrate(false);
			if(!empty($productList_query)){
			   $productList = $productList_query->toArray();
			}else{
			   $productList = array();
			}
		}
		$customProductList = array();
		foreach($productList as $productRow){
			$customProductList[] = array(
										 'product' => $productRow['product'],
										 'product_code'=> $productRow['product']."-".$productRow['product_code']
										 );
		}
		echo json_encode($customProductList);
		//$this->layout = false;
		die;
	 }
	 
	public function orgToSpecial($id,$passed_kiosk_id = ""){
	    //$this->check_dr5();
		 if(!empty($passed_kiosk_id)){
			  if($passed_kiosk_id == 10000){
				  $kiosk_id = "";
			  }else{
				  $kiosk_id = $passed_kiosk_id;
			  }
		 }else{
			  $kiosk_id = $this->request->Session()->read('kiosk_id');
		 }
	    
		 if(!empty($kiosk_id)){
			  $CreditReceiptSource = "kiosk_{$kiosk_id}_credit_receipts";
			  $CreditProductDetailSource = "kiosk_{$kiosk_id}_credit_product_details";
			  $CreditPaymentDetailSource = "kiosk_{$kiosk_id}_credit_payment_details";
		 }else{
			  $CreditReceiptSource = "credit_receipts";
			  $CreditProductDetailSource = "credit_product_details";
			  $CreditPaymentDetailSource = "credit_payment_details";
		 }
		 $CreditReceiptTable = TableRegistry::get($CreditReceiptSource,[
																'table' => $CreditReceiptSource,
															]);
		 $CreditProductDetailTable = TableRegistry::get($CreditProductDetailSource,[
																		'table' => $CreditProductDetailSource,
																	]);
		 $CreditPaymentDetailTable = TableRegistry::get($CreditPaymentDetailSource,[
																	'table' => $CreditPaymentDetailSource,
																   ]);
		 $res_query = $CreditReceiptTable->find('all',array(
												 'conditions' => array(
													 'id' => $id
												 )
												 ));
		 $res_query = $res_query->hydrate(false);
		 if(!empty($res_query)){
			  $res = $res_query->toArray();
		 }else{
			  $res = array();
		 }
		 //pr($res);die;
		 if(!empty($res)){
			  $cus_id = $res[0]['customer_id'];
			  $customer_query = $this->Customers->find('all',
													    ['conditions'=>['id'=>$cus_id]
														]
													  );
			  $customer_query = $customer_query->hydrate(false);
			  if(!empty($customer_query)){
			    $customer = $customer_query->first();
			  }else{
			    $customer = array();
			  }
			  $id = $res[0]['id'];
			  $creditPaymentDetail_query = $CreditPaymentDetailTable->find('all',[
																	'conditions'=>['credit_receipt_id'=>$id]
																    ]
															   );
			  $creditPaymentDetail_query = $creditPaymentDetail_query->hydrate(false);
			  if(!empty($creditPaymentDetail_query)){
			    $creditPaymentDetail = $creditPaymentDetail_query->toArray();
			  }else{
			    $creditPaymentDetail = array();
			  }
			  $creditProductDetail_query = $CreditProductDetailTable->find('all',[
																	'conditions'=>['credit_receipt_id'=>$id]
																    ]
															   );
			  $creditProductDetail_query = $creditProductDetail_query->hydrate(false);
			  if(!empty($creditProductDetail_query)){
			    $creditProductDetail = $creditProductDetail_query->toArray();
			  }else{
			    $creditProductDetail = array();
			  }
		 }else{
		    $customer = array();
		    $creditPaymentDetail = array();
		    $creditProductDetail = array();
		 }
		 //pr($creditProductDetail);die;
		 $this->set(compact('customer','creditPaymentDetail','creditProductDetail'));
		 $country = $customer['country'];
		 if($country == "OTH"){
			 //echo "hi";die;
			  if(!empty($res)){
				    $product_recipt_data1 = $product_recipt_data = $res[0];
				    $payment_detail_data1 = $payment_detail_data = $creditPaymentDetail;
				    $kiosk_product_sale_data1 = $kiosk_product_sale_data = $creditProductDetail;
				    $created = $res[0]['created'];
				    $database_date = strtotime(date('d-m-y',strtotime($created)));
				    $today_date = strtotime(date('d-m-y'));
					$loggedInUser = $this->request->session()->read('Auth.User.username');
					if ($loggedInUser != SPL_PRIVILEGE_USER){ //QUOT_USER_PREFIX."inderjit"
						 if($today_date != $database_date){
							 //echo "hi";die;
							 $this->Flash->error(__("credit note can be migrated on same day only"));
							 if($this->request->session()->read('Auth.User.group_id') == ADMINISTRATORS || $this->request->session()->read('Auth.User.group_id') == FRANCHISE_OWNER){
								 $kiosk_id_to_set = $this->get_kiosk_for_credit('credit_search');
								 return $this->redirect(array('action'=>"search?kiosk_id=$kiosk_id_to_set&payment_type=All&invoice_detail=receipt_number"));
							 }else{
								 return $this->redirect(array('action'=>'view_credit_note'));
							 }
						 }
					}
				  
				    $recipt = "t_credit_receipts";
				    $kiosk_product_sale = "t_credit_product_details";
				    $payment_table = "t_credit_payment_details";
			    
				    $CreditReceiptTable = TableRegistry::get($recipt,[
															    'table' => $recipt,
														    ]);
				    $CreditProductDetailTable = TableRegistry::get($kiosk_product_sale,[
																		    'table' => $kiosk_product_sale,
																	    ]);
				    $CreditPaymentDetailTable = TableRegistry::get($payment_table,[
																	    'table' => $payment_table,
																	  ]);
				  
				    if(!empty($product_recipt_data)){
						unset($product_recipt_data['id']);
						unset($product_recipt_data['created']);
						unset($product_recipt_data['modified']);
						if(!empty($kiosk_id)){
							$product_recipt_data['kiosk_id'] = $kiosk_id;
						}
						$CreditReceiptTable->behaviors()->load('Timestamp');
						$entity = $CreditReceiptTable->newEntity();
						$entity = $CreditReceiptTable->patchEntity($entity,$product_recipt_data);
						$CreditReceiptTable->save($entity);
						$recipt_id = $entity->id;
				    }
				    if(!empty($payment_detail_data)){
						foreach($payment_detail_data as $key => $value){
							unset($value['id']);
							unset($value['created']);
							unset($value['modified']);
							if(!empty($kiosk_id)){
								$value['kiosk_id'] = $kiosk_id;
							}
							$value['credit_receipt_id'] = $recipt_id;
							//pr($value);die;
							$CreditPaymentDetailTable->behaviors()->load('Timestamp');
							$Entity = $CreditPaymentDetailTable->newEntity();
							$Entity = $CreditPaymentDetailTable->patchEntity($Entity,$value);
							$CreditPaymentDetailTable->save($Entity);
						}
				    }
				    if(!empty($kiosk_product_sale_data)){
						foreach($kiosk_product_sale_data as $key1 => $value1){
							 unset($value1['id']);
							 unset($value1['created']);
							 unset($value1['modified']);
							 if(!empty($kiosk_id)){
								 $value1['kiosk_id'] = $kiosk_id;
							 }
							 $value1['credit_receipt_id'] = $recipt_id;
							 //pr($value1);die;
							 $CreditProductDetailTable->behaviors()->load('Timestamp');
							 $eNtity = $CreditProductDetailTable->newEntity();
							 $eNtity = $CreditProductDetailTable->patchEntity($eNtity,$value1);
							 $CreditProductDetailTable->save($eNtity);
						}
				    }
			  }
					 
				    if(!empty($kiosk_id)){
						$recipt = "kiosk_{$kiosk_id}_credit_receipts";
						$kiosk_product_sale = "kiosk_{$kiosk_id}_credit_product_details";
						$payment_table = "kiosk_{$kiosk_id}_credit_payment_details";
				    }else{
						$recipt = "credit_receipts";
						$kiosk_product_sale = "credit_product_details";
						$payment_table = "credit_payment_details";
				    }
				    $CreditReceiptTable = TableRegistry::get($recipt,[
															    'table' => $recipt,
														    ]);
				    $CreditProductDetailTable = TableRegistry::get($kiosk_product_sale,[
																		    'table' => $kiosk_product_sale,
																	    ]);
				    $CreditPaymentDetailTable = TableRegistry::get($payment_table,[
																	    'table' => $payment_table,
																	  ]);
			 
				    $recipt_update_query = "UPDATE {$recipt} SET credit_amount=0.25,bill_cost=0.25,bulk_discount=0 WHERE id = $id";
				    //$this->CreditReceipt->query($recipt_update_query);
				    $conn = ConnectionManager::get('default');
				    $stmt = $conn->execute($recipt_update_query); 
				 
				    if(count($kiosk_product_sale_data1) > 1){
						$first_entry_id = $kiosk_product_sale_data1[0]['id'];
						$sale_update_query = "UPDATE {$kiosk_product_sale} SET product_id = 7224,discount = 0,quantity = 1,sale_price=0.30,credit_price = 0.30 WHERE id = $first_entry_id";
						$conn1 = ConnectionManager::get('default');
						$stmt1 = $conn1->execute($sale_update_query); 
						//$this->CreditProductDetail->query($sale_update_query);
						unset($kiosk_product_sale_data1[0]);
						foreach($kiosk_product_sale_data1 as $s => $raw_data){
							$delete_id  = $raw_data['id'];
							$sale_delete_query = "DELETE FROM {$kiosk_product_sale} WHERE id = $delete_id";
							$conn2 = ConnectionManager::get('default');
							$stmt2 = $conn2->execute($sale_delete_query);
							//$this->CreditProductDetail->query($sale_delete_query);
						}
				    }else{
						$first_entry_id = $kiosk_product_sale_data1[0]['id'];
						$sale_update_query = "UPDATE {$kiosk_product_sale} SET product_id = 7224,discount = 0,quantity = 1,credit_price = 0.30,sale_price=0.30 WHERE id = $first_entry_id";
						$conn2 = ConnectionManager::get('default');
						$stmt2 = $conn2->execute($sale_update_query);
						//$this->CreditProductDetail->query($sale_update_query);
				    }
				 
				    if(count($payment_detail_data1) > 1){
						$paymentfirst_entry_id = $payment_detail_data1[0]['id'];
						$payment_update_query = "UPDATE {$payment_table} SET amount=0.25,payment_method='Cash' WHERE id = $paymentfirst_entry_id";
						$conn2 = ConnectionManager::get('default');
						$stmt2 = $conn2->execute($payment_update_query);
						//$this->CreditPaymentDetail->query($payment_update_query);
						unset($payment_detail_data1[0]);
						foreach($payment_detail_data1 as $p => $p_raw_data){
							$p_delete_id  = $p_raw_data['id'];
							$payment_delete_query = "DELETE FROM {$payment_table} WHERE id = $p_delete_id";
							$conn3 = ConnectionManager::get('default');
							$stmt3 = $conn3->execute($payment_delete_query);
							//$this->CreditPaymentDetail->query($payment_delete_query);
						}
				    }else{
						$paymentfirst_entry_id = $payment_detail_data1[0]['id'];
						$payment_update_query = "UPDATE {$payment_table} SET amount=0.25,payment_method='Cash' WHERE id = $paymentfirst_entry_id";
						$conn3 = ConnectionManager::get('default');
						$stmt3 = $conn3->execute( $payment_update_query);
						//$this->CreditPaymentDetail->query($payment_update_query);
				    }
				    if($this->request->session()->read('Auth.User.group_id') == ADMINISTRATORS || $this->request->session()->read('Auth.User.group_id') == FRANCHISE_OWNER){
						$kiosk_id_to_set = $this->get_kiosk_for_credit('credit_search');
						$this->Flash->success(__("Credit Note with credit note id {$id} changed to Credit Quotation"));
						return $this->redirect(array('action'=>"search?kiosk_id=$kiosk_id_to_set&payment_type=All&invoice_detail=receipt_number"));
				    }else{
						$this->Flash->success(__("Credit Note with credit note id {$id} changed to Credit Quotation"));
						return $this->redirect(array('action'=>'view_credit_note'));
				    }
				 
		 }else{
			 //echo "hiiiii";die;
			  if($this->request->is('Post')){
				    $total_amt = 0;
				    $sale_amt = $this->request->data['sale_amount'];
				    foreach($this->request->data['payment'] as $key => $value){
					    $total_amt += $value;
				    }
				    $sale_amt = round($sale_amt,2);
				    $total_amt  = round($total_amt,2);
				    if($sale_amt != $total_amt){
						$amt = $res[0]['credit_amount'];
						//$vat = $res[0]['CreditReceipt']['vat'];
						//if(!empty($vat)){
							$final_amt = $amt/(1+($vat/100));
						//}else{
							//$final_amt = $amt;
						//}
						$this->set(compact('res','final_amt'));
						$this->Flash->error(__("amount is not matching"));
						//return $this->redirect(array('action'=>'payment_screen'));
				    }else{
						$created = $res[0]['created'];
						$database_date = strtotime(date('d-m-y',strtotime($created)));
						$today_date = strtotime(date('d-m-y'));
						$loggedInUser = $this->request->session()->read('Auth.User.username');
						 if ($loggedInUser != SPL_PRIVILEGE_USER){ //QUOT_USER_PREFIX."inderjit"
							  if($today_date != $database_date){
								  if($this->request->session()->read('Auth.User.group_id') == ADMINISTRATORS || $this->request->session()->read('Auth.User.group_id') == FRANCHISE_OWNER){
									  $kiosk_id_to_set = $this->get_kiosk_for_credit('credit_search');
									  $this->Flash->error(__("Credit note can be migrated on same day only"));
									  return $this->redirect(array('action'=>"search?kiosk_id=$kiosk_id_to_set&payment_type=All&invoice_detail=receipt_number"));
								  }else{
									  $this->Flash->error(__("Credit note can be migrated on same day only"));
									  return $this->redirect(array('action'=>'view_credit_note'));
								  }
							  }
						 }
					    //pr($res);die;
						$payment_data = $this->request->data['payment'];
						foreach($creditPaymentDetail as $s_key => $s_value){
							if(array_key_exists($s_value['id'],$payment_data)){
								$new_amt = $payment_data[$s_value['id']];
								$creditPaymentDetail[$s_key]['amount'] = $new_amt;
							}
						}
						$res[0]['credit_amount'] = $sale_amt;
						//$res[0]['CreditReceipt']['bill_amount'] = $sale_amt;
						
						if(!empty($res)){
							$product_recipt_data1 = $product_recipt_data = $res[0];
							$payment_detail_data1 = $payment_detail_data = $creditPaymentDetail;
							$kiosk_product_sale_data1 = $kiosk_product_sale_data = $creditProductDetail;
									
							$recipt = "t_credit_receipts";
							$kiosk_product_sale = "t_credit_product_details";
							$payment_table = "t_credit_payment_details";
							$products = "products";
							
							$CreditReceiptTable = TableRegistry::get($recipt,[
															    'table' => $recipt,
														    ]);
							$CreditProductDetailTable = TableRegistry::get($kiosk_product_sale,[
																					'table' => $kiosk_product_sale,
																				]);
							$CreditPaymentDetailTable = TableRegistry::get($payment_table,[
																				'table' => $payment_table,
																			   ]);
						
							if(!empty($product_recipt_data)){
								unset($product_recipt_data['id']);
								unset($product_recipt_data['created']);
								unset($product_recipt_data['modified']);
								if(!empty($kiosk_id)){
									$product_recipt_data['kiosk_id'] = $kiosk_id;
								}
								$CreditReceiptTable->behaviors()->load('Timestamp');
								$newEntity = $CreditReceiptTable->newEntity();
								$newEntity = $CreditReceiptTable->patchEntity($newEntity,$product_recipt_data);
								$CreditReceiptTable->save($newEntity);
								$recipt_id = $newEntity->id;
							}
							if(!empty($payment_detail_data)){
								foreach($payment_detail_data as $key => $value){
									unset($value['id']);
									unset($value['created']);
									unset($value['modified']);
									if(!empty($kiosk_id)){
										$value['kiosk_id'] = $kiosk_id;
									}
									$value['credit_receipt_id'] = $recipt_id;
									$CreditPaymentDetailTable->behaviors()->load('Timestamp');
									$newEntity1 = $CreditPaymentDetailTable->newEntity();
									$newEntity1 = $CreditPaymentDetailTable->patchEntity($newEntity1,$value);
									$CreditPaymentDetailTable->save($newEntity1);
								}
							}
							if(!empty($kiosk_product_sale_data)){
								foreach($kiosk_product_sale_data as $key1 => $value1){
									unset($value1['id']);
									unset($value1['created']);
									unset($value1['modified']);
									if(!empty($kiosk_id)){
										$value1['kiosk_id'] = $kiosk_id;
									}
									$value1['credit_receipt_id'] = $recipt_id;
									$CreditProductDetailTable->behaviors()->load('Timestamp');
									$newEntity2 = $CreditProductDetailTable->newEntity();
									$newEntity2 = $CreditProductDetailTable->patchEntity($newEntity2,$value1);
									$CreditProductDetailTable->save($newEntity2);
								}
							}
						}
						if(!empty($kiosk_id)){
							$recipt = "kiosk_{$kiosk_id}_credit_receipts";
							$kiosk_product_sale = "kiosk_{$kiosk_id}_credit_product_details";
							$payment_table = "kiosk_{$kiosk_id}_credit_payment_details";
						}else{
							$recipt = "credit_receipts";
							$kiosk_product_sale = "credit_product_details";
							$payment_table = "credit_payment_details";
						}
						$CreditReceiptTable = TableRegistry::get($recipt,[
															    'table' => $recipt,
														    ]);
						$CreditProductDetailTable = TableRegistry::get($kiosk_product_sale,[
																				'table' => $kiosk_product_sale,
																			]);
						$CreditPaymentDetailTable = TableRegistry::get($payment_table,[
																			'table' => $payment_table,
																		   ]);
					
						$recipt_update_query = "UPDATE {$recipt} SET credit_amount=0.30,bill_cost=0.25,bulk_discount=0 WHERE id = $id";
						$conn = ConnectionManager::get('default');
						$stmt = $conn->execute($recipt_update_query); 
						//$this->CreditReceipt->query($recipt_update_query);
						
						if(count($kiosk_product_sale_data1) > 1){
							 $first_entry_id = $kiosk_product_sale_data1[0]['id'];
							 $sale_update_query = "UPDATE {$kiosk_product_sale} SET discount = 0,product_id = 7224,quantity = 1,sale_price=0.30,credit_price=0.25 WHERE id = $first_entry_id";
							 $conn1 = ConnectionManager::get('default');
							 $stmt1 = $conn1->execute($sale_update_query);
							 //$this->CreditProductDetail->query($sale_update_query);
							 unset($kiosk_product_sale_data1[0]);
							 foreach($kiosk_product_sale_data1 as $s => $raw_data){
								 $delete_id  = $raw_data['id'];
								 $sale_delete_query = "DELETE FROM {$kiosk_product_sale} WHERE id = $delete_id";
								 $conn1 = ConnectionManager::get('default');
							      $stmt1 = $conn1->execute($sale_delete_query);
								 //$this->CreditProductDetail->query($sale_delete_query);
							 }
						}else{
							 $first_entry_id = $kiosk_product_sale_data1[0]['id'];
							 $sale_update_query = "UPDATE {$kiosk_product_sale} SET product_id = 7224,discount = 0,quantity = 1,sale_price=0.30,credit_price=0.25 WHERE id = $first_entry_id";
							 $conn1 = ConnectionManager::get('default');
							 $stmt1 = $conn1->execute($sale_update_query);
							 //$this->CreditProductDetail->query($sale_update_query);
						}
						
						if(count($payment_detail_data1) > 1){
							 $paymentfirst_entry_id = $payment_detail_data1[0]['id'];
							 $payment_update_query = "UPDATE {$payment_table} SET amount=0.30,payment_method='Cash' WHERE id = $paymentfirst_entry_id";
							 $conn2 = ConnectionManager::get('default');
							 $stmt2 = $conn2->execute($payment_update_query);
							 //$this->CreditPaymentDetail->query($payment_update_query);
							 unset($payment_detail_data1[0]);
							 foreach($payment_detail_data1 as $p => $p_raw_data){
								 $p_delete_id  = $p_raw_data['id'];
								 $payment_delete_query = "DELETE FROM {$payment_table} WHERE id = $p_delete_id";
								 $conn2 = ConnectionManager::get('default');
							      $stmt2 = $conn2->execute($payment_delete_query);
								 //$this->CreditPaymentDetail->query($payment_delete_query);
							 }
						}else{
							 $paymentfirst_entry_id = $payment_detail_data1[0]['id'];
							 $payment_update_query = "UPDATE {$payment_table} SET amount=0.30,payment_method='Cash' WHERE id = $paymentfirst_entry_id";
							 $conn2 = ConnectionManager::get('default');
							 $stmt2 = $conn2->execute($payment_update_query);
							 //$this->CreditPaymentDetail->query($payment_update_query);
						}
						if($this->request->session()->read('Auth.User.group_id') == ADMINISTRATORS || $this->request->session()->read('Auth.User.group_id') == FRANCHISE_OWNER){
							 $kiosk_id_to_set = $this->get_kiosk_for_credit('credit_search');
							 $this->Flash->success(__("Credit Note with credit note id {$id} changed to Credit Quotation"));
							 return $this->redirect(array('action'=>"search?kiosk_id=$kiosk_id_to_set&payment_type=All&invoice_detail=receipt_number"));
						}else{
							 $this->Flash->success(__("Credit Note with credit note id {$id} changed to Credit Quotation"));
							 return $this->redirect(array('action'=>'view_credit_note'));
						}
				    }
			  }
			 
			  $amt = $res[0]['credit_amount'];
			  $vat = $this->VAT;
			  //$vat = $res[0]['CreditReceipt']['vat'];
			  //if(!empty($vat)){
				  $final_amt = $amt/(1+($vat/100));
			  //}else{
			  //	$final_amt = $amt;
			  //}
			  $final_amt =  round($final_amt,2);
			  $this->set(compact('res','final_amt'));
			  $this->render("payment_screen");
		 }
	}
	
	public function specialToOrig($id){
		//$this->check_dr5();
		$recipt = "t_credit_receipts";
		$kiosk_product_sale = "t_credit_product_details";
		$payment_table = "t_credit_payment_details";
	
		$CreditReceiptTable = TableRegistry::get($recipt,[
													'table' => $recipt,
												]);
		$CreditProductDetailTable = TableRegistry::get($kiosk_product_sale,[
																'table' => $kiosk_product_sale,
															]);
		$CreditPaymentDetailTable = TableRegistry::get($payment_table,[
															'table' => $payment_table,
														   ]);
		
		$res_query = $CreditReceiptTable->find('all',array(
												'conditions' => array('id' => $id)
												));
		$res_query = $res_query->hydrate(false);
		if(!empty($res_query)){
			$res = $res_query->toArray();
		}else{
			$res = array();
		}
		//pr($res);die;
		if(!empty($res)){
			$cust_id = $res[0]['customer_id'];
			$customer_query = $this->Customers->find('all',[
											  'conditions'=>['id'=>$cust_id]
											  ]
										);
			$customer_query = $customer_query->hydrate(false);
			if(!empty($customer_query)){
				$customer = $customer_query->first();
			}else{
				$customer = array();
			}
			$id = $res[0]['id'];
			$creditPaymentDetail_query = $CreditPaymentDetailTable->find('all',[
																   'conditions'=>['credit_receipt_id'=>$id]
																   ]);
			$creditPaymentDetail_query = $creditPaymentDetail_query->hydrate(false);
			if(!empty($creditPaymentDetail_query)){
				$creditPaymentDetail = $creditPaymentDetail_query->toArray();
			}else{
				$creditPaymentDetail = array();
			}
			$creditProductDetail_query = $CreditProductDetailTable->find('all',[
															  'conditions'=>['credit_receipt_id'=>$id]
															  ]);
			$creditProductDetail_query = $creditProductDetail_query->hydrate(false);
			if(!empty($creditProductDetail_query)){
				$creditProductDetail = $creditProductDetail_query->toArray();
			}else{
				$creditProductDetail = array();
			}
		}else{
			$customer = array();
			$creditPaymentDetail = array();
			$creditProductDetail = array();
		}
		//pr($creditProductDetail);die;
		$country = $customer['country'];
		if($country == "OTH"){
			if(!empty($res)){
				$product_recipt_data1 = $product_recipt_data = $res[0];
				$payment_detail_data1 = $payment_detail_data = $creditPaymentDetail;
				$kiosk_product_sale_data1 = $kiosk_product_sale_data = $creditProductDetail;
				$created = $res[0]['created'];
				$database_date = strtotime(date('d-m-y',strtotime($created)));
				$today_date = strtotime(date('d-m-y'));
				$loggedInUser = $this->request->session()->read('Auth.User.username');
			    if ($loggedInUser != SPL_PRIVILEGE_USER){ //QUOT_USER_PREFIX."inderjit"
					if($today_date != $database_date){
						$this->Flash->error(__("Credit Quotation can be migrated on same day only"));
						if($this->request->session()->read('Auth.User.group_id') == ADMINISTRATORS || $this->request->session()->read('Auth.User.group_id') == FRANCHISE_OWNER){
							$kiosk_id_to_set = $this->get_kiosk_for_credit('dr_credit_search');
							return $this->redirect(array('action'=>"t_search?kiosk_id=$kiosk_id_to_set&payment_type=All&invoice_detail=receipt_number"));
						}else{
							return $this->redirect(array('action'=>'t_view_credit_note'));
						}
					}
				}
			}
			$kiosk_id = "";//$this->Session->read('kiosk_id');
			//if($kiosk_id == ""){
			//	$kiosk_id = 0;
			//}
			//if($product_recipt_data['kiosk_id'] != $kiosk_id){
			//	$this->Session->setFlash(__("cannot change invoice of other kiosk"));				
			//	if(AuthComponent::user('group_id') == ADMINISTRATORS){
			//		$kiosk_id_to_set = $this->get_kiosk_for_credit('dr_credit_search');
			//		return $this->redirect(array('action'=>"t_search?kiosk_id=$kiosk_id_to_set&payment_type=All&invoice_detail=receipt_number"));
			//	}else{
			//		return $this->redirect(array('action'=>'t_view_credit_note'));
			//	}
			//}
			
			if(!empty($kiosk_id)){
				$recipt = "kiosk_{$kiosk_id}_credit_receipts";
				$kiosk_product_sale = "kiosk_{$kiosk_id}_credit_product_details";
				$payment_table = "kiosk_{$kiosk_id}_credit_payment_details";
			}else{
				$saved_kiosk_id = $res[0]['kiosk_id'];
				if(!empty($saved_kiosk_id)){
					$recipt = "kiosk_{$saved_kiosk_id}_credit_receipts";
					$kiosk_product_sale = "kiosk_{$saved_kiosk_id}_credit_product_details";
					$payment_table = "kiosk_{$saved_kiosk_id}_credit_payment_details";
				}else{
					$recipt = "credit_receipts";
					$kiosk_product_sale = "credit_product_details";
					$payment_table = "credit_payment_details";
				}
				
			}
			$CreditReceiptTable = TableRegistry::get($recipt,[
													'table' => $recipt,
												]);
			$CreditProductDetailTable = TableRegistry::get($kiosk_product_sale,[
																	'table' => $kiosk_product_sale,
																]);
			$CreditPaymentDetailTable = TableRegistry::get($payment_table,[
																'table' => $payment_table,
															   ]);
			
			if(!empty($product_recipt_data)){
				unset($product_recipt_data['id']);
				unset($product_recipt_data['created']);
				unset($product_recipt_data['modified']);
                 $CreditReceiptTable->behaviors()->load('Timestamp');
				$entity = $CreditReceiptTable->newEntity();
				$entity = $CreditReceiptTable->patchEntity($entity,$product_recipt_data,['validate'=>false]);
				$CreditReceiptTable->save($entity);
				$recipt_id = $entity->id;
			}
			if(!empty($payment_detail_data)){
				foreach($payment_detail_data as $key => $value){
					unset($value['id']);
					unset($value['created']);
					unset($value['modified']);
					$value['credit_receipt_id'] = $recipt_id;
                    $CreditPaymentDetailTable->behaviors()->load('Timestamp');
					$Entity = $CreditPaymentDetailTable->newEntity();
					$Entity = $CreditPaymentDetailTable->patchEntity($Entity,$value,['validate'=>false]);
					$CreditPaymentDetailTable->save($Entity);
				}
			}
			if(!empty($kiosk_product_sale_data)){
				foreach($kiosk_product_sale_data as $key1 => $value1){
					unset($value1['id']);
					unset($value1['created']);
					unset($value1['modified']);
					$value1['credit_receipt_id'] = $recipt_id;

                    $CreditProductDetailTable->behaviors()->load('Timestamp');
					$pEntity = $CreditProductDetailTable->newEntity();
					$pEntity = $CreditProductDetailTable->patchEntity($pEntity,$value1,['validate'=>false]);
					$CreditProductDetailTable->save($pEntity);
				}
			}
			
			$t_recipt = "t_credit_receipts";
            $t_kiosk_product_sale = "t_credit_product_details";
            $t_payment_table = "t_credit_payment_details";
            
            
			$recipt_update_query = "UPDATE {$t_recipt} SET credit_amount=0.25,bill_cost=0.25,bulk_discount=0 WHERE id = $id";
			$conn = ConnectionManager::get('default');
			$stmt = $conn->execute($recipt_update_query); 
			//$this->CreditReceipt->query($recipt_update_query);
			
			if(count($kiosk_product_sale_data1) > 1){
				$first_entry_id = $kiosk_product_sale_data1[0]['id'];
				$sale_update_query = "UPDATE {$t_kiosk_product_sale} SET product_id = 7224,discount = 0,credit_price = 0.30,quantity = 1,sale_price=0.30 WHERE id = $first_entry_id";
				$conn1 = ConnectionManager::get('default');
				$stmt1 = $conn1->execute($sale_update_query); 
				//$this->CreditProductDetail->query($sale_update_query);
				unset($kiosk_product_sale_data1[0]);
				foreach($kiosk_product_sale_data1 as $s => $raw_data){
					$delete_id  = $raw_data['id'];
					$sale_delete_query = "DELETE FROM {$t_kiosk_product_sale} WHERE id = $delete_id";
					$conn2 = ConnectionManager::get('default');
					$stmt2 = $conn2->execute($sale_delete_query); 
					//$this->CreditProductDetail->query($sale_delete_query);
				}
			}else{
				$first_entry_id = $kiosk_product_sale_data1[0]['id'];
				$sale_update_query = "UPDATE {$t_kiosk_product_sale} SET product_id = 7224,discount = 0,quantity = 1,credit_price = 0.30,sale_price=0.30 WHERE id = $first_entry_id";
				$conn2 = ConnectionManager::get('default');
				$stmt2 = $conn2->execute($sale_update_query); 
				//$this->CreditProductDetail->query($sale_update_query);
			}
			
			if(count($payment_detail_data1) > 1){
				$paymentfirst_entry_id = $payment_detail_data1[0]['id'];
				$payment_update_query = "UPDATE {$t_payment_table} SET amount=0.25,payment_method='Cash' WHERE id = $paymentfirst_entry_id";
				$conn3 = ConnectionManager::get('default');
				$stmt3 = $conn3->execute($payment_update_query); 
				//$this->CreditProductDetail->query($payment_update_query);
				unset($payment_detail_data1[0]);
				foreach($payment_detail_data1 as $p => $p_raw_data){
					$p_delete_id  = $p_raw_data['id'];
					$payment_delete_query = "DELETE FROM {$t_payment_table} WHERE id = $p_delete_id";
					$conn4 = ConnectionManager::get('default');
					$stmt4 = $conn4->execute($payment_delete_query); 
					//$this->CreditProductDetail->query($payment_delete_query);
				}
			}else{
				$paymentfirst_entry_id = $payment_detail_data1[0]['id'];
				$payment_update_query = "UPDATE {$t_payment_table} SET amount=0.25,payment_method='Cash' WHERE id = $paymentfirst_entry_id";
				$conn4 = ConnectionManager::get('default');
				$stmt4 = $conn4->execute($payment_update_query); 
				//$this->CreditProductDetail->query($payment_update_query);
			}
			if($this->request->session()->read('Auth.User.group_id') == ADMINISTRATORS || $this->request->session()->read('Auth.User.group_id') == FRANCHISE_OWNER){
				$kiosk_id_to_set = $this->get_kiosk_for_credit('dr_credit_search');
				$this->Flash->success(__("Credit Quotation with id {$id} changed to Credit Note"));
				return $this->redirect(array('action'=>"t_search?kiosk_id=$kiosk_id_to_set&payment_type=All&invoice_detail=receipt_number"));
			}else{
				$this->Flash->success(__("Credit Quotation with id {$id} changed to Credit Note"));
				return $this->redirect(array('action'=>'t_view_credit_note'));
			}
		}else{
			if($this->request->is('Post')){
				$total_amt = 0;
				$sale_amt = $this->request->data['sale_amount'];
				foreach($this->request->data['payment'] as $key => $value){
					$total_amt += $value;
				}
				$sale_amt = round($sale_amt,2);
				$total_amt = round($total_amt,2);
				if($sale_amt != $total_amt){
					$amt = $res[0]['credit_amount'];
					$vat = $this->VAT;
					if(!empty($vat)){
						$final_amt = $amt + $amt*($vat/100);
					}else{
						$final_amt = $amt;
					}
					$this->set(compact('res','final_amt'));
					$this->Flash->error(__("amount is not matching"));
					//return $this->redirect(array('action'=>'payment_screen'));
				}else{
					$created = $res[0]['created'];
					$database_date = strtotime(date('d-m-y',strtotime($created)));
					$today_date = strtotime(date('d-m-y'));
					$loggedInUser = $this->request->session()->read('Auth.User.username');
					if ($loggedInUser != SPL_PRIVILEGE_USER){ //QUOT_USER_PREFIX."inderjit"
						 
						 if($today_date != $database_date){
							 $this->Flash->error(__("Credit Quotation can be migrated on same day only"));
							 //return $this->redirect(array('action'=>'index'));
							 if($this->request->session()->read('Auth.User.group_id') == ADMINISTRATORS || $this->request->session()->read('Auth.User.group_id') == FRANCHISE_OWNER){
								 $kiosk_id_to_set = $this->get_kiosk_for_credit('dr_credit_search');
								 return $this->redirect(array('action'=>"t_search?kiosk_id=$kiosk_id_to_set&payment_type=All&invoice_detail=receipt_number"));
							 }else{
								 return $this->redirect(array('action'=>'t_view_credit_note'));
							 }	
								 
						 
						 
						 }
					}
					
					$payment_data = $this->request->data['payment'];
					foreach($creditPaymentDetail as $s_key => $s_value){
						if(array_key_exists($s_value['id'],$payment_data)){
							$new_amt = $payment_data[$s_value['id']];
							$creditPaymentDetail[$s_key]['amount'] = $new_amt;
						}
					}
					$res[0]['credit_amount'] = $sale_amt;
					//----------------------------------------------
					if(!empty($res)){
						$product_recipt_data1 = $product_recipt_data = $res[0];
						$payment_detail_data1 = $payment_detail_data = $creditPaymentDetail;
						$kiosk_product_sale_data1 = $kiosk_product_sale_data = $creditProductDetail;
						$created = $res[0]['created'];
						$database_date = strtotime(date('d-m-y',strtotime($created)));
						$today_date = strtotime(date('d-m-y'));
						$loggedInUser = $this->request->session()->read('Auth.User.username');
						 if ($loggedInUser != SPL_PRIVILEGE_USER){ //QUOT_USER_PREFIX."inderjit"
							  if($today_date != $database_date){
								  $this->Flash->error(__("Credit Quotation can be migrated on same day only"));
								  if($this->request->session()->read('Auth.User.group_id') == ADMINISTRATORS || $this->request->session()->read('Auth.User.group_id') == FRANCHISE_OWNER){
									  $kiosk_id_to_set = $this->get_kiosk_for_credit('dr_credit_search');
									  return $this->redirect(array('action'=>"t_search?kiosk_id=$kiosk_id_to_set&payment_type=All&invoice_detail=receipt_number"));
								  }else{
									  return $this->redirect(array('action'=>'t_view_credit_note'));
								  }	
							  }
						 }
					}
					$kiosk_id = "";//$this->Session->read('kiosk_id');
					//if($kiosk_id == ""){
					//	$kiosk_id = 0;
					//}
					if($product_recipt_data['kiosk_id'] != $kiosk_id){
						//$this->Session->setFlash(__("cant change invoice"));
						//if(AuthComponent::user('group_id') == ADMINISTRATORS){
						//		$kiosk_id_to_set = $this->get_kiosk_for_credit('dr_credit_search');
						//		return $this->redirect(array('action'=>"t_search?kiosk_id=$kiosk_id_to_set&payment_type=All&invoice_detail=receipt_number"));
						//	}else{
						//		return $this->redirect(array('action'=>'t_view_credit_note'));
						//	}	
					}
					
					if(!empty($kiosk_id)){
						$recipt1 = "kiosk_{$kiosk_id}_credit_receipts";
						$kiosk_product_sale1 = "kiosk_{$kiosk_id}_credit_product_details";
						$payment_table1 = "kiosk_{$kiosk_id}_credit_payment_details";
					}else{
						$saved_kiosk_id = $res[0]['kiosk_id'];
						if(!empty($saved_kiosk_id)){
							$recipt1 = "kiosk_{$saved_kiosk_id}_credit_receipts";
							$kiosk_product_sale1 = "kiosk_{$saved_kiosk_id}_credit_product_details";
							$payment_table1 = "kiosk_{$saved_kiosk_id}_credit_payment_details";
						}else{
							$recipt1 = "credit_receipts";
							$kiosk_product_sale1  = "credit_product_details";
							$payment_table1 = "credit_payment_details";
						}
						
					}
					$CreditReceiptTable = TableRegistry::get($recipt1,[
													'table' => $recipt1,
												]);
					$CreditProductDetailTable = TableRegistry::get($kiosk_product_sale1,[
																			'table' => $kiosk_product_sale1,
																		]);
					$CreditPaymentDetailTable = TableRegistry::get($payment_table1,[
																		'table' => $payment_table1,
																	   ]);
					
					if(!empty($product_recipt_data)){
						unset($product_recipt_data['id']);
						unset($product_recipt_data['created']);
						unset($product_recipt_data['modified']);
                        
                        $CreditReceiptTable->behaviors()->load('Timestamp');
						$entity = $CreditReceiptTable->newEntity();
						$entity = $CreditReceiptTable->patchEntity($entity,$product_recipt_data,['validate'=>false]);
						$CreditReceiptTable->save($entity);
						$recipt_id = $entity->id;
					}
					if(!empty($payment_detail_data)){
						foreach($payment_detail_data as $key => $value){
							unset($value['id']);
							unset($value['created']);
							unset($value['modified']);
							$value['credit_receipt_id'] = $recipt_id;
                            
                             $CreditPaymentDetailTable->behaviors()->load('Timestamp');
							$Entity = $CreditPaymentDetailTable->newEntity();
							$Entity = $CreditPaymentDetailTable->patchEntity($Entity,$value,['validate'=>false]);
							$CreditPaymentDetailTable->save($Entity);
						}
					}
					if(!empty($kiosk_product_sale_data)){
						foreach($kiosk_product_sale_data as $key1 => $value1){
							unset($value1['id']);
							unset($value1['created']);
							unset($value1['modified']);
							$value1['credit_receipt_id'] = $recipt_id;
                            
                            $CreditProductDetailTable->behaviors()->load('Timestamp');
							$pEntity = $CreditProductDetailTable->newEntity();
							$pEntity = $CreditProductDetailTable->patchEntity($pEntity,$value1,['validate'=>false]);
							$CreditProductDetailTable->save($pEntity);
						}
					}
					
					
					$recipt_update_query = "UPDATE {$recipt} SET credit_amount=0.25,bill_cost=0.25,bulk_discount=0 WHERE id = $id";
					$conn = ConnectionManager::get('default');
					$stmt = $conn->execute($recipt_update_query); 
					//$this->CreditReceipt->query($recipt_update_query);
					
					if(count($kiosk_product_sale_data1) > 1){
						$first_entry_id = $kiosk_product_sale_data1[0]['id'];
						$sale_update_query = "UPDATE {$kiosk_product_sale} SET discount = 0, product_id = 7224,quantity = 1,credit_price = 0.30,sale_price=0.30 WHERE id = $first_entry_id";
						$conn1 = ConnectionManager::get('default');
						$stmt1 = $conn1->execute($sale_update_query);
						//$this->CreditProductDetail->query($sale_update_query);
						unset($kiosk_product_sale_data1[0]);
						foreach($kiosk_product_sale_data1 as $s => $raw_data){
							$delete_id  = $raw_data['id'];
							$sale_delete_query = "DELETE FROM {$kiosk_product_sale} WHERE id = $delete_id";
							$conn2 = ConnectionManager::get('default');
							$stmt2 = $conn2->execute($sale_delete_query);
							//$this->CreditProductDetail->query($sale_delete_query);
						}
					}else{
						$first_entry_id = $kiosk_product_sale_data1[0]['id'];
						$sale_update_query = "UPDATE {$kiosk_product_sale} SET discount = 0,quantity = 1,credit_price = 0.30,sale_price=0.30 WHERE id = $first_entry_id";
						$conn2 = ConnectionManager::get('default');
						$stmt2 = $conn2->execute($sale_update_query);
						//$this->CreditProductDetail->query($sale_update_query);
					}
					
					if(count($payment_detail_data1) > 1){
						$paymentfirst_entry_id = $payment_detail_data1[0]['id'];
						$payment_update_query = "UPDATE {$payment_table} SET amount=0.25,payment_method='Cash' WHERE id = $paymentfirst_entry_id";
						$conn3 = ConnectionManager::get('default');
						$stmt3 = $conn3->execute($payment_update_query);
						//$this->CreditPaymentDetail->query($payment_update_query);
						unset($payment_detail_data1[0]);
						foreach($payment_detail_data1 as $p => $p_raw_data){
							$p_delete_id  = $p_raw_data['id'];
							$payment_delete_query = "DELETE FROM {$payment_table} WHERE id = $p_delete_id";
							$conn4 = ConnectionManager::get('default');
							$stmt4 = $conn4->execute($payment_delete_query);
							//$this->CreditPaymentDetail->query($payment_delete_query);
						}
					}else{
						$paymentfirst_entry_id = $payment_detail_data1[0]['id'];
						$payment_update_query = "UPDATE {$payment_table} SET amount=0.25,payment_method='Cash' WHERE id = $paymentfirst_entry_id";
						$conn4 = ConnectionManager::get('default');
						$stmt4 = $conn4->execute($payment_update_query);
						//$this->CreditPaymentDetail->query($payment_update_query);
					}
					
					if($this->request->session()->read('Auth.User.group_id') == ADMINISTRATORS || $this->request->session()->read('Auth.User.group_id') == FRANCHISE_OWNER){
						$kiosk_id_to_set = $this->get_kiosk_for_credit('dr_credit_search');
						$this->Flash->success(__("Credit Quotation with id {$id} changed to Credit Note"));
						return $this->redirect(array('action'=>"t_search?kiosk_id=$kiosk_id_to_set&payment_type=All&invoice_detail=receipt_number"));
					}else{
						$this->Flash->success(__("Credit Quotation with id {$id} changed to Credit Note"));
						return $this->redirect(array('action'=>'t_view_credit_note'));
					}
					
					
					//----------------------------------------------
				}
			}
			
			$kiosk_id = "";//$this->Session->read('kiosk_id');
			//if($kiosk_id == ""){
			//	$kiosk_id = 0;
			//}
			if($res[0]['kiosk_id'] != $kiosk_id){
				//$this->Session->setFlash(__("cannot change invoice of other kiosk"));				
				//if(AuthComponent::user('group_id') == ADMINISTRATORS){
				//	$kiosk_id_to_set = $this->get_kiosk_for_credit('dr_credit_search');
				//	return $this->redirect(array('action'=>"t_search?kiosk_id=$kiosk_id_to_set&date_type=invoice"));
				//}else{
				//	return $this->redirect(array('action'=>'t_view_credit_note'));
				//}die;
			}
			
			$amt = $res[0]['credit_amount'];
			$vat = $this->VAT;
			if(!empty($vat)){
				$final_amt = $amt + $amt*($vat/100);
			}else{
				$final_amt = $amt;
			}
			$final_amt = round($final_amt,2);
			$this->set(compact('res','final_amt','creditPaymentDetail'));
			$this->render("payment_screen");
		}	
	}
    
    public function drChangeCustomer($recipt_id){
		$recipt = "t_credit_receipts";
		$kiosk_product_sale = "t_credit_product_details";
		$payment_table = "t_credit_payment_details";
        
        
        $CreditReceiptTable = TableRegistry::get($recipt,[
													'table' => $recipt,
												]);
		$CreditProductDetailTable = TableRegistry::get($kiosk_product_sale,[
																'table' => $kiosk_product_sale,
															]);
		$CreditPaymentDetailTable = TableRegistry::get($payment_table,[
															'table' => $payment_table,
														   ]);
		//echo "hi";die;
		
		 $credit_payment_res = $CreditPaymentDetailTable->find('all',[
                                                                'conditions' =>  ['credit_receipt_id'=> $recipt_id],
                                                                //'contain' => ['CreditProductDetails']
                                                                      
                                                            ])->toArray();
		
		$customer_res_query = $this->Customers->find('all',array(
														  'fields' => array('id','fname','country','lname','business'),
														  ));
        $customer_res_query = $customer_res_query->hydrate(false);
        if(!empty($customer_res_query)){
            $customer_res = $customer_res_query->toArray();
        }else{
            $customer_res = array();
        }
		$recipt_res_query = $CreditReceiptTable->find('all',array('conditions' => array(
																	  'id'=> $recipt_id,
																	  )));
        $recipt_res_query = $recipt_res_query->hydrate(false);
        if(!empty($recipt_res_query)){
            $recipt_res = $recipt_res_query->toArray();
        }else{
            $recipt_res = array();
        }
		if(!empty($recipt_res)){
			$old_customer_id = $recipt_res[0]['customer_id'];
			$credit_amt = $recipt_res[0]['credit_amount'];
			$this->set(compact('credit_amt'));
		}
        
        $product_detail_query = $CreditProductDetailTable->find('all',array('conditions' => array('credit_receipt_id' => $recipt_id)));
        $product_detail_query = $product_detail_query->hydrate(false);
        if(!empty($product_detail_query)){
            $product_detail =  $product_detail_query->toArray();
        }else{
            $product_detail = array();
        }
       // pr($product_detail);die;
        
		//pr($recipt_res);die;
		$customer_country = $customer_Arr = array();
		$customer_first_name = $customer_last_name = $customer_bussiness = "";
		foreach($customer_res as $key => $value){
			if($value['id'] == $old_customer_id){
				$customer_first_name = $value['fname'];
				$customer_last_name = $value['lname'];
				$customer_bussiness = $value['business'];
			}
			$customer_Arr[$value['id']] = $value['fname']."(".$value['country'].")";
			$customer_country[$value['id']] = $value['country'];
		}
        $this->paginate = [
								   'conditions' => array('system_user' => 0),
								   	'limit' => 50
								   ];
		$customers = $this->paginate('Customers');
		$this->set(compact('customers'));
		$this->set(compact('customer_first_name','customer_last_name','customer_bussiness','old_customer_id'));
		if($this->request->is('Post')){
			$new_customer_id = $this->request->data['customer'];
			$new_customer_res_query = $this->Customers->find('all',array(
															'conditions' => array('id' => $new_customer_id),
														  //'recursive'=>-1
														  ));
            $new_customer_res_query = $new_customer_res_query->hydrate(false);
            if(!empty($new_customer_res_query)){
                $new_customer_res = $new_customer_res_query->first();
            }else{
                $new_customer_res = array();
            }
			if($new_customer_id != $old_customer_id){
				if($customer_country[$new_customer_id] != $customer_country[$old_customer_id]){
					$kiosk_product_sale_data = $product_detail;
					//pr($kiosk_product_sale_data);die;
						if(!empty($kiosk_product_sale_data)){
							$total_sale_price = 0;
							foreach($kiosk_product_sale_data as $s_key => $s_value){
								if($s_value['discount']){
									$discount = $s_value['discount'];
									$sale_price = $s_value['sale_price'];
									$after_discount_price = $sale_price - ($sale_price*$discount/100);
									$total_price = $after_discount_price*$s_value['quantity'];
								}else{
									$total_price = $s_value['sale_price']*$s_value['quantity'];
								}	
								$total_sale_price += $total_price;
							}
						}
					if($customer_country[$new_customer_id] == "OTH"){  // if changed to other country which mens no vat
						$product_recipt_data = array(
													 'id' => $recipt_id,
													 'customer_id' => $new_customer_id,
													 'fname' => $new_customer_res['fname'],
													 'lname' => $new_customer_res['lname'],
													 'email' => $new_customer_res['email'],
													 'mobile' => $new_customer_res['mobile'],
													 'address_1' => $new_customer_res['address_1'],
													 'address_2' => $new_customer_res['address_2'],
													 'city' => $new_customer_res['city'],
													 'state' => $new_customer_res['state'],
													 'zip' => $new_customer_res['zip'],
													 'vat' => "",
													 'agent_id' => $new_customer_res['agent_id']
													 //'bill_amount' => $total_sale_price,
													 //'orig_bill_amount' => $total_sale_price,
													 );
                        $credit_Entity = $CreditReceiptTable->get($recipt_id);
                        $credit_Entity = $CreditReceiptTable->patchEntity($credit_Entity,$product_recipt_data,['validate' => false]);
						if($CreditReceiptTable->save($credit_Entity)){
							  if(!empty($credit_payment_res)){
								   foreach($credit_payment_res as $key => $value){
										$credit_pay_id = $value['id'];
										$pay_data = array(
													 'agent_id' => $new_customer_res['agent_id']
													 );
										$CreditPaymentEntity = $CreditPaymentDetailTable->get($credit_pay_id);
										$CreditPaymentPatchEntity = $CreditPaymentDetailTable->patchEntity($CreditPaymentEntity,$pay_data);
										$CreditPaymentDetailTable->save($CreditPaymentPatchEntity) ;
								   }
							  }
						 
							//$this->Flash->success(__("customer has been changed for Invoice ID $recipt_id"));
							if($this->request->session()->read('Auth.User.group_id') == ADMINISTRATORS || $this->request->session()->read('Auth.User.group_id') == FRANCHISE_OWNER){
								$kiosk_id_to_set = $this->get_kiosk_for_credit("dr_credit_search");
								$this->Flash->success(__("customer has been changed for Invoice ID $recipt_id"));
								return $this->redirect(array('action'=>"t_search?kiosk_id=$kiosk_id_to_set&payment_type=All&invoice_detail=receipt_number"));
							}else{
								$this->Flash->success(__("customer has been changed for Invoice ID $recipt_id"));
								return $this->redirect(array('action'=>'t_view_credit_note'));
							}
						
						}
					}else{ // same country mns will have vat
						$vat = $this->VAT;
						$vat_amount = $total_sale_price*($vat/100);
						$after_vat_value = $total_sale_price+$vat_amount;
						$product_recipt_data = array(
													 'id' => $recipt_id,
													 'customer_id' => $new_customer_id,
													 'fname' => $new_customer_res['fname'],
													 'lname' => $new_customer_res['lname'],
													 'email' => $new_customer_res['email'],
													 'mobile' => $new_customer_res['mobile'],
													 'address_1' => $new_customer_res['address_1'],
													 'address_2' => $new_customer_res['address_2'],
													 'city' => $new_customer_res['city'],
													 'state' => $new_customer_res['state'],
													 //'vat' => $vat,
													 'zip' => $new_customer_res['zip'],
													 'agent_id' => $new_customer_res['agent_id']
													 //'bill_amount' => $after_vat_value,
													 //'orig_bill_amount' => $after_vat_value,
													 );
                         $credit_Entity = $CreditReceiptTable->get($recipt_id);
                        $credit_Entity = $CreditReceiptTable->patchEntity($credit_Entity,$product_recipt_data,['validate' => false]);
						if($CreditReceiptTable->save($credit_Entity)){
							  if(!empty($credit_payment_res)){
								   foreach($credit_payment_res as $key => $value){
										$credit_pay_id = $value['id'];
										$pay_data = array(
													 'agent_id' => $new_customer_res['agent_id']
													 );
										$CreditPaymentEntity = $CreditPaymentDetailTable->get($credit_pay_id);
										$CreditPaymentPatchEntity = $CreditPaymentDetailTable->patchEntity($CreditPaymentEntity,$pay_data);
										$CreditPaymentDetailTable->save($CreditPaymentPatchEntity) ;
								   }
							  }
							if($this->request->session()->read('Auth.User.group_id') == ADMINISTRATORS || $this->request->session()->read('Auth.User.group_id') == FRANCHISE_OWNER){
								$kiosk_id_to_set = $this->get_kiosk_for_credit("dr_credit_search");
								$this->Flash->success(__("customer has been changed for Invoice ID $recipt_id"));
								return $this->redirect(array('action'=>"t_search?kiosk_id=$kiosk_id_to_set&payment_type=All&invoice_detail=receipt_number"));
							}else{
								$this->Flash->success(__("customer has been changed for Invoice ID $recipt_id"));
								return $this->redirect(array('action'=>'t_view_credit_note'));
							}
						}
					}
				}else{
					$product_recipt_data = array(
													 'id' => $recipt_id,
													 'customer_id' => $new_customer_id,
													 'fname' => $new_customer_res['fname'],
													 'lname' => $new_customer_res['lname'],
													 'email' => $new_customer_res['email'],
													 'mobile' => $new_customer_res['mobile'],
													 'address_1' => $new_customer_res['address_1'],
													 'address_2' => $new_customer_res['address_2'],
													 'city' => $new_customer_res['city'],
													 'state' => $new_customer_res['state'],
													 'zip' => $new_customer_res['zip'],
													 'agent_id' => $new_customer_res['agent_id']
													);
                     $credit_Entity = $CreditReceiptTable->get($recipt_id);
                    $credit_Entity = $CreditReceiptTable->patchEntity($credit_Entity,$product_recipt_data,['validate' => false]);
					if($CreditReceiptTable->save($credit_Entity)){
						 if(!empty($credit_payment_res)){
								   foreach($credit_payment_res as $key => $value){
										$credit_pay_id = $value['id'];
										$pay_data = array(
													 'agent_id' => $new_customer_res['agent_id']
													 );
										$CreditPaymentEntity = $CreditPaymentDetailTable->get($credit_pay_id);
										$CreditPaymentPatchEntity = $CreditPaymentDetailTable->patchEntity($CreditPaymentEntity,$pay_data);
										$CreditPaymentDetailTable->save($CreditPaymentPatchEntity) ;
								   }
							  }
						if($this->request->session()->read('Auth.User.group_id') == ADMINISTRATORS || $this->request->session()->read('Auth.User.group_id') == FRANCHISE_OWNER){
							$kiosk_id_to_set = $this->get_kiosk_for_credit("dr_credit_search");
							$this->Flash->success(__("customer has been changed for Invoice ID $recipt_id"));
							return $this->redirect(array('action'=>"t_search?kiosk_id=$kiosk_id_to_set&payment_type=All&invoice_detail=receipt_number"));
						}else{
							$this->Flash->success(__("customer has been changed for Invoice ID $recipt_id"));
							return $this->redirect(array('action'=>'t_view_credit_note'));
						}
						
					}
				}
			}
		}
		
		
		$this->set(compact('customer_Arr','recipt_id','old_customer_id'));
	}
    
    
	public function drSearchCustomer(){
		//pr($this->request->query);die;
		$recipt_id = $this->request->query['recipt_id'];
		$search_kw = $this->request->query['search_kw'];
		$old_customer_id = $this->request->query['old_customer_id'];
		$customer_res_query = $this->Customers->find('all',array(
														'conditions' => array('id' => $old_customer_id),
														  'fields' => array('id','fname','country','lname','business'),
														//  'recursive'=>-1
														  ));
        $customer_res_query = $customer_res_query->hydrate(false);
        if(!empty($customer_res_query)){
            $customer_res = $customer_res_query->toArray();
        }else{
            $customer_res = array();
        }
		if(!empty($customer_res)){
			$customer_first_name = $customer_res[0]['fname'];
			$customer_last_name = $customer_res[0]['lname'];
			$customer_bussiness = $customer_res[0]['business'];
		}
		
		$recipt = "t_credit_receipts";
		$kiosk_product_sale = "t_credit_product_details";
		$payment_table = "t_credit_payment_details";
        
        
        $CreditReceiptTable = TableRegistry::get($recipt,[
													'table' => $recipt,
												]);
		$CreditProductDetailTable = TableRegistry::get($kiosk_product_sale,[
																'table' => $kiosk_product_sale,
															]);
		$CreditPaymentDetailTable = TableRegistry::get($payment_table,[
															'table' => $payment_table,
														   ]);
		
		$recipt_res_query = $CreditReceiptTable->find('all',array('conditions' => array(
																	  'id'=> $recipt_id,
																	  )));
        $recipt_res_query = $recipt_res_query->hydrate(false);
        if(!empty($recipt_res_query)){
            $recipt_res = $recipt_res_query->toArray();
        }else{
            $recipt_res = array();
        }
		if(!empty($recipt_res)){
			$credit_amt = $recipt_res[0]['credit_amount'];
			$this->set(compact('credit_amt'));
		}
		
		
		if(!empty($search_kw)){
			//$this->Customer->recursive = 0;
			$conditionArr = array();
			if(!empty($search_kw)){
				$search_kw = trim($search_kw);
				//$conditionArr['Customer.brand like'] =  strtolower("%$searchKW%");
				$conditionArr ['OR']['Customers.fname like'] = strtolower("%$search_kw%");
				$conditionArr ['OR']['Customers.email like'] = strtolower("%$search_kw%");
				$conditionArr ['OR']['Customers.mobile like'] = strtolower("%$search_kw%");
				$conditionArr ['OR']['Customers.business  like'] = strtolower("%$search_kw%");
			}
			$this->paginate = array(
							'conditions' => array($conditionArr,'system_user' => 0),
							'limit' => 50
							);
			$customers = $this->paginate('Customers');
			$this->set(compact('customers'));
		}else{
			$this->paginate = array(
								'conditions' => array('system_user' => 0),
								'limit' => 50
								);
			$customers = $this->paginate('Customers');
			$this->set(compact('customers'));
		}
		$this->set(compact('customer_first_name','customer_last_name','customer_bussiness','old_customer_id','recipt_id'));
		$this->render('dr_change_customer');
	}
}
?>