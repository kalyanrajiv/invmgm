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

class MobileRepairSalesController extends AppController{
    
    public $helpers = [
         'Paginator' => ['templates' => 'paginatortemplates']
         ];
    public function initialize()
    {
        parent::initialize();
        $this->loadComponent('ScreenHint');
        $this->loadModel('Categories');
        $this->loadModel('Products');
        $this->loadModel('MobileRepairs');
        $this->loadModel('Users');
        $this->loadModel('Kiosks');
        $this->loadModel('RepairPayments');
        $this->loadModel('MobileModels');
		$this->loadModel('MobileRepairSales');
		$this->loadModel('MobileRepairLogs');
		$CURRENCY_TYPE = Configure::read('CURRENCY_TYPE');
		$this->set(compact('CURRENCY_TYPE'));
		
		
        $this->fromemail = Configure::read('FROM_EMAIL');
		$repairStatusUserOptions = Configure::read('repair_statuses_user');
		$repairStatusTechnicianOptions = Configure::read('repair_statuses_technician');
		$this->set(compact('repairStatusUserOptions','repairStatusTechnicianOptions'));
    }
    
    public function viewRepairSales(){
		//$rs = $this->RepairPayment->find('all', array('recursive' => -1));
		//pr($rs);die;
		$saleSum = $refundSum = '';
		if($this->request->session()->read('Auth.User.group_id') == ADMINISTRATORS || $this->request->session()->read('Auth.User.group_id') == FRANCHISE_OWNER || $this->request->session()->read('Auth.User.group_id') == MANAGERS){
			if(true ){//$this->request->session()->read('Auth.User.group_id')== MANAGERS
				
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
					$managerKiosk = $this->get_kiosk();//pr($managerKiosk);die;
					if(!empty($managerKiosk)){
						$kiosk_ids = $managerKiosk;		
					}
				}
				
					//$managerKiosk = $this->managerLogin();//pr($managerKiosk);die;
					if(!empty($kiosk_ids)){
						//$kiosk_id = key($managerKiosk);
						$this->paginate = [
							'conditions'=>['MobileRepairSales.kiosk_id IN' => $kiosk_ids,'MobileRepairSales.refund_status'=>0],
							'order' => ['MobileRepairSales.mobile_repair_id DESC, MobileRepairSales.id DESC'],
							'limit' => ROWS_PER_PAGE
							];
					}else{
						$this->paginate = [
							'conditions'=>['MobileRepairSales.refund_status'=>0],
							'order' => ['MobileRepairSales.mobile_repair_id DESC, MobileRepairSales.id DESC'],
							'limit' => ROWS_PER_PAGE
							];
					}
			}else{
			
				if(!empty($this->request['data']['RepairSale']['kiosk_id'])){
				$kiosk_id = $this->request['data']['RepairSale']['kiosk_id'];
					
					$this->paginate = [
								'conditions'=>['MobileRepairSales.kiosk_id' => $kiosk_id,'MobileRepairSales.refund_status'=>0],
								'order' => ['MobileRepairSales.mobile_repair_id DESC, MobileRepairSales.id DESC'],
								'limit' => ROWS_PER_PAGE
								];
				}else{
					$this->paginate = [
								'conditions'=>array('MobileRepairSales.refund_status'=>0),
								'order' => ['MobileRepairSales.mobile_repair_id DESC, MobileRepairSales.id DESC'],
								'limit' => ROWS_PER_PAGE
								];
				}
			}
		}elseif($this->request->session()->read('Auth.User.group_id')){
			$kioskId = $this->request->Session()->read('kiosk_id');			
			$this->paginate = [
							'conditions'=>['MobileRepairSales.kiosk_id' => $kioskId,'MobileRepairSales.refund_status'=>0],
							'order' => ['MobileRepairSales.mobile_repair_id DESC, MobileRepairSales.id DESC'],
							'limit' => ROWS_PER_PAGE
							];
		}
		
		$kiosks_query = $this->Kiosks->find('list',
                                            [
                                                'keyField' => 'id',
                                                'valueField' => 'name',
                                                'conditions' => ['Kiosks.status' => 1],
                                                'order'=>'Kiosks.name asc',
                                            ]);
        $kiosks_query = $kiosks_query->hydrate(false);
        if(!empty($kiosks_query)){
            $kiosks = $kiosks_query->toArray();
        }
        
		unset($kiosks['10000']);
		$users_query = $this->Users->find('list',
                                            [
                                                'keyField' => 'id',
                                                'valueField' => 'username',
                                            ]);
        $users_query = $users_query->hydrate(false);
        if(!empty($users_query)){
            $users = $users_query->toArray();
        }
		$this->set(compact('mobileRepairSales','kiosks','users','kiosk_id'));
		
		$mobileRepairSales_query = $this->paginate();
        $mobileRepairSales = $mobileRepairSales_query->toArray();
        
        
		$repairIdArr = array();
		foreach($mobileRepairSales as $key=>$mobileRepairSale){
			$repairIdArr[] = $mobileRepairSale->mobile_repair_id;
		}
        if(empty($repairIdArr)){
            $repairIdArr = array('0'=>null);
        }
		//getting repair payment details
		
		$paymentArr = array();
		$payment_amount_arr = array();
		
		$mobileRepairPayment_query = $this->RepairPayments->find('all',
                                                           array('conditions' => array('RepairPayments.mobile_repair_id IN' => $repairIdArr))
                                                           );
		
        //pr($mobileRepairPayment_query);die;
        $mobileRepairPayment_query = $mobileRepairPayment_query->hydrate(false);
        $mobileRepairPayment = $mobileRepairPayment_query->toArray();
		
		
		if(count($mobileRepairPayment)){
			foreach($mobileRepairPayment as $rp => $paymentDetail){
				$paymentArr[$paymentDetail['mobile_repair_id']][] = $paymentDetail;
				if(array_key_exists($paymentDetail['mobile_repair_id'],$payment_amount_arr) && array_key_exists($paymentDetail['payment_method'],$payment_amount_arr[$paymentDetail['mobile_repair_id']])){
					$payment_amount_arr[$paymentDetail['mobile_repair_id']][$paymentDetail['payment_method']]+= $paymentDetail['amount'];
				}else{
					$payment_amount_arr[$paymentDetail['mobile_repair_id']][$paymentDetail['payment_method']] = $paymentDetail['amount'];
				}
			}
		}
		$mobileRepairStsArray = array();
		$mobileRepairStatus_query = $this->MobileRepairs->find('all',
                                                         array('conditions'=>array('MobileRepairs.id IN'=>$repairIdArr),
                                                               'fields'=>array('id','status'))
                                                         );
        $mobileRepairStatus_query = $mobileRepairStatus_query->hydrate(false);
        $mobileRepairStatus = $mobileRepairStatus_query->toArray();
		foreach($mobileRepairStatus as $key=>$mobileRepairSts){
			$mobileRepairStsArray[$mobileRepairSts['id']] = $mobileRepairSts['status'];
		}
		 $this->set(compact('mobileRepairStsArray','mobileRepairSales','paymentArr','payment_amount_arr','saleSum','refundSum'));
		//$this->set('mobileRepairSales', $this->Paginator->paginate());
	}
	
	
	public function search(){
		ini_set('max_execution_time', 300); 
        //pr($this->request->query);die;
		//pr($this->request);die;
		//defaults
		$searchKW1 = $searchKW = "";
		$kiosk_id = $this->request->Session()->read('kiosk_id');
		$type = 'refunded';
		$conditionArr = array();
		$refundStatus = 0;
		$paymentMode = 'Multiple';
		//pr($_SESSION);die;
		if(array_key_exists('payment_mode',$this->request->query)){
			$paymentMode = $this->request->query['payment_mode'];
			if($paymentMode == 'refunded'){
				$refundStatus = 1;
			}
		}
		if(
		   array_key_exists('search_kw',$this->request->query) &&
		   !empty($this->request->query['search_kw']) 
		   ){
			$searchKW = $this->request->query['search_kw'];
			//We are receiving IMEI int repair_id parameter
		}
		
		if(array_key_exists('search_kw1',$this->request->query) &&
		   !empty($this->request->query['search_kw1'])
		   ){
			$searchKW1 = $this->request->query['search_kw1'];
			//We are receiving IMEI int search_kw1 parameter
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
		
		if(
		   array_key_exists('start_date',$this->request->query) &&
		   !empty($this->request->query['start_date']) &&
		   array_key_exists('end_date',$this->request->query) &&
		   !empty($this->request->query['end_date'])){
			
			$a = strtotime($this->request->query['start_date']);
			$b = strtotime($this->request->query['end_date']);
			$c = $a- $b;
			$c = -($c);
			if($c > 8640000){
				$this->Flash->error("Maximum Date Range Allowed is 3 Months");
				return $this->redirect(array('action' => 'view-repair-sales'));
			}
			$conditionArr[] = array(
						"MobileRepairSales.created >" => date('Y-m-d', strtotime($this->request->query['start_date'])),
						"MobileRepairSales.created <" => date('Y-m-d', strtotime($this->request->query['end_date']. ' +1 Days')),			
					       );
		}
		
		if(!empty($searchKW)){
			$conditionArr[] = array(
						'MobileRepairSales.mobile_repair_id' => $searchKW
			);
		}
		
		if(!empty($searchKW1)){
			$imei_res_query = $this->MobileRepairs->find('all',array('conditions' => array(
																					'MobileRepairs.imei like' => "%$searchKW1%"
																					),
															  'fields' => array('id','imei'),
															  'recursive' => -1
															  )
												  );
			$imei_res_query = $imei_res_query->hydrate(false);
			if(!empty($imei_res_query)){
				$imei_res = $imei_res_query->toArray();
			}
			$repair_ids = array();
			if(!empty($imei_res)){
				foreach($imei_res as $imei_key => $imei_value){
					$repair_ids[] = $imei_value['id'];
				}
			}
			if(empty($repair_ids)){
				$repair_ids = array(0 => null);
			}
			$conditionArr[] = array(
						'MobileRepairSales.mobile_repair_id IN' => $repair_ids,
			);
			//$conditionArr[] = array(
			//			'MobileRepair.imei like' => "%$searchKW1%"
			//);
		}
		
		if($kiosk_id == ""){
			$kioskId = $this->request->query['RepairSale'];
			if(
			   array_key_exists('kiosk_id',$this->request->query['RepairSale']) &&
			   !empty($this->request->query['RepairSale']['kiosk_id'])
			){
				$conditionArr[] = array('MobileRepairSales.kiosk_id' => $this->request->query['RepairSale']['kiosk_id']);
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
				$kiosk_ids = array();
				if($ext_site == 1){
					 $managerKiosk = $this->get_kiosk();//pr($managerKiosk);die;
					 if(!empty($managerKiosk)){
						 $kiosk_ids = $managerKiosk;		
					 }
					 $conditionArr[] = array('MobileRepairSales.kiosk_id IN' => $kiosk_ids);
				}
			}
			$this->set('kioskId', $kioskId);
		}
		
		if($kiosk_id > 0){
			//echo "kiosk";
			$conditionArr[] = array('MobileRepairSales.kiosk_id' => "$kiosk_id",);
		}
		
		//pr($conditionArr);die;
		$this->set('search_kw', $searchKW);
		$this->set('search_kw1', $searchKW1);
		//pr($conditionArr);die;
		$sales_data_query = $this->MobileRepairSales->find('all',array(
							'conditions' => array($conditionArr,'MobileRepairSales.refund_status' => $refundStatus),
							'fields' => array('id','mobile_repair_id','refund_status'),
							'order' => 'MobileRepairSales.mobile_repair_id DESC, MobileRepairSales.id DESC',
							'recursive' => 0
						  ));
        //pr($sales_data_query);die;
		$sales_data_query = $sales_data_query->hydrate(false);
		if(!empty($sales_data_query)){
			$sales_data = $sales_data_query->toArray();
		}else{
			$sales_data = array();
		}
		
		/*
		 *Above query will fetch repair_ids and that will fell for both case again refund and non-refunded; filter will work not this base.
		 **/
		
		$ids = array();
		foreach($sales_data as $key => $value){
			$ids[] = $value["id"];
		}
		
		$kiosks_query = $this->Kiosks->find('list',
												[
													'keyField' => 'id',
													'valueField' => 'name',
													'conditions' => ['Kiosks.status' => 1],
													'order'=>['Kiosks.name asc'],
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
		
		$repairIdArr = $status = array();
		
		
		foreach($sales_data as $key => $mobileRepairSale){
				$repairIdArr[] = $mobileRepairSale['mobile_repair_id'];
			//$status[$mobileRepairSale['MobileRepairSale']['mobile_repair_id']] = $mobileRepairSale['MobileRepairSale']['status'];
		}
		//pr($repairIdArr);
		//below is being used for sum and refund in the code below
		$resultRepairIds = $repairIdArr;
		$payment_amount_arr = $payment_array = array();
		
		//To integrate payment mode search with above search, we are grabbing the repair ids from the payment table as per the
		//above result ($repairIdArr with condition array)
		//then again we are finding the repair ids from repair payment table with the search repair id result $repairIdArr
		//and will again change condition in paginator setting to paginate as per the payment table outcome
		
		//getting repair ids from repair payment table as per the selected in radio buttons
			
		if($paymentMode == 'Cash' || $paymentMode == 'Card'){
			if(empty($repairIdArr)){
				$repairIdArr = array(0 => null);
			}
			
			$searchPaymentResult_query = $this->RepairPayments->find('all',
															   array('conditions' => array('RepairPayments.mobile_repair_id IN' => $repairIdArr,
																						   'RepairPayments.payment_method' => $paymentMode))
															   );
			$searchPaymentResult_query = $searchPaymentResult_query->hydrate(false);
			if(!empty($searchPaymentResult_query)){
				$searchPaymentResult = $searchPaymentResult_query->toArray();
			}else{
				$searchPaymentResult = array();
			}
			
			
			if(count($searchPaymentResult)){
				foreach($searchPaymentResult as $spr => $searchPaymentInfo){
					$payment_array[$searchPaymentInfo['mobile_repair_id']] = $searchPaymentInfo['mobile_repair_id'];
				}
				
				$resultRepairIds = $payment_array;
				//pr($resultRepairIds);
			}
			if(empty($payment_array)){
				$payment_array = array(0 => null);
			}
			if(empty($ids)){
				$ids = array(0 => null);
			}
			$this->paginate = [
												'conditions' => [
																		'MobileRepairSales.mobile_repair_id IN' => $payment_array,
																		'MobileRepairSales.refund_status' => $refundStatus,
																		'MobileRepairSales.id IN' => $ids,
															  ],
												'order' => ['MobileRepairSales.mobile_repair_id DESC'],//MobileRepairSale.id DESC
												'limit' => ROWS_PER_PAGE,
												//'recursive' => 0
							  ];
			
		}else{
			//Case: if all or refunded
			if(empty($resultRepairIds)){
				$resultRepairIds = array(0 => null);
			}
			if(empty($ids)){
				$ids = array(0 => null);
			}
			$this->paginate = [
							'conditions' => [
													'MobileRepairSales.mobile_repair_id IN' => $resultRepairIds,
													'MobileRepairSales.refund_status' => $refundStatus,
													'MobileRepairSales.id IN' => $ids,
											],
							'order' => ['MobileRepairSales.mobile_repair_id DESC'],//, MobileRepairSale.id DESC
							'limit' => ROWS_PER_PAGE,
							//'recursive' => 0
						  ];
		}
		
		//pr($this->Paginate);die;
		$refundSumData_query = $this->MobileRepairSales->find('all',
														array(
															  'conditions' => array('MobileRepairSales.refund_status' => 1, $conditionArr),
															  'order' => 'MobileRepairSales.id desc')
														);
		$refundSumData_query
								 ->select(['totalrefund' => $refundSumData_query->func()->sum('refund_amount')])
								 ->first();
		$refundSumData_query = $refundSumData_query->hydrate(false);
		$refundSumData = $refundSumData_query->toArray();
		
		$refundSum = $refundSumData[0]['totalrefund'];
		if($refundSum < 0){
			$refundSum = -$refundSum;
		}elseif(empty($refundSum)){
			$refundSum = 0;
		}

		
			
				
		if($paymentMode == 'Card'){
			if(empty($resultRepairIds)){
				$resultRepairIds = array(0 => null);
			}
			$saleSumData_query = $this->RepairPayments->find('all', array(
																	'conditions' => array('payment_method' => 'Card',
																	'RepairPayments.mobile_repair_id IN' => $resultRepairIds)
																	)
													   );
			$saleSumData_query
								->select(['totalsale' => $refundSumData_query->func()->sum('amount')])
								 ->first();
			
			$saleSumData_query = $saleSumData_query->hydrate(false);
			if(!empty($saleSumData_query)){
				$saleSumData = $saleSumData_query->toArray();
			}else{
				$saleSumData = array();
			}
			$refundSum = 0;
			//pr($saleSumData);
		}elseif($paymentMode == 'Cash'){
			if(empty($resultRepairIds)){
				$resultRepairIds = array(0 => null);
			}
			$saleSumData_query = $this->RepairPayments->find('all', array(
																	  'conditions' => array('payment_method' => 'Cash',
																	'RepairPayments.mobile_repair_id IN' => $resultRepairIds)
																	  )
													   );
			$saleSumData_query
								->select(['totalsale' => $refundSumData_query->func()->sum('amount')])
								 ->first();
			$saleSumData_query = $saleSumData_query->hydrate(false);
			if(!empty($saleSumData_query)){
				$saleSumData = $saleSumData_query->toArray();
			}else{
				$saleSumData = array();
			}
		}else{
			//$paymentMode == 'Multiple'
			if($paymentMode == 'refunded'){
				$saleSumData_query = $this->MobileRepairSales->find('all',
															 array(   
																   'conditions' => array(
																						$conditionArr,
																						'refund_status' => '0'
																					)
															));
				$saleSumData_query
								->select(['totalsale' => $refundSumData_query->func()->sum('amount')])
								 ->first();
				$saleSumData_query = $saleSumData_query->hydrate(false);
				if(!empty($saleSumData_query)){
					$saleSumData = $saleSumData_query->toArray();
				}else{
					$saleSumData = array();
				}
			}else{
				if(!empty($ids) && !empty($resultRepairIds)){
					//pr($resultRepairIds);die;
					$res_query = $this->MobileRepairSales->find('all',array('conditions' => array(
																								  $conditionArr,'MobileRepairSales.refund_status' => $refundStatus
																								  //'MobileRepairSales.mobile_repair_id IN' => $resultRepairIds,
																				// 'MobileRepairSales.id IN' => $ids,
																				 ),
														   'fields' => array('mobile_repair_id','rebooked_status'),
														   ));
					//pr($res_query);die;
					$res_query = $res_query->hydrate(false);
					if(!empty($res_query)){
						$res = $res_query->toArray();
					}else{
						$res = array();
					}
					
					$id_array = array();
					foreach($res as $key_s => $value_s){
						$repair_id = $value_s['mobile_repair_id'];
						if(!in_array($repair_id,$resultRepairIds)){
							unset($res[$key_s]);
							continue;
						}else{
							$rep_id = $value_s['mobile_repair_id'];
							$reb_status = $value_s['rebooked_status'];
							if($reb_status != 1){
								$id_array[] = $rep_id;
							}
						}
					}
					
					//$id_array = array();
					//foreach($res as $res_key => $res_value){
					//	$rep_id = $res_value['mobile_repair_id'];
					//	$reb_status = $res_value['rebooked_status'];
					//	if($reb_status != 1){
					//		$id_array[] = $rep_id;
					//	}
					//}
					if(empty($id_array)){
						$id_array = array(0 => null);
						$saleSumData_query = $this->RepairPayments->find('all', array(
																			'conditions' => array('RepairPayments.mobile_repair_id IN' => $id_array))
															   );
						$saleSumData_query
											->select(['totalsale' => $refundSumData_query->func()->sum('amount')])
											->first();
											
						$saleSumData_query = $saleSumData_query->hydrate(false);
						if(!empty($saleSumData_query)){
							$saleSumData = $saleSumData_query->toArray();
						}else{
							$saleSumData = array();
						}
					}else{
						$new_arr = array_chunk($id_array,500);
						$saleSumData = array(0 => array('totalsale' => 0));
						foreach($new_arr as $arr){
							$saleSumData_query = $this->RepairPayments->find('all', array(
																			'conditions' => array('RepairPayments.mobile_repair_id IN' => $arr))
															   );
							$saleSumData_query
												->select(['totalsale' => $refundSumData_query->func()->sum('amount')])
												->first();
												
							$saleSumData_query = $saleSumData_query->hydrate(false);
							if(!empty($saleSumData_query)){
								$saleSumData_arr = $saleSumData_query->toArray();
								$saleSumData[0]['totalsale'] += $saleSumData_arr[0]['totalsale'];
							}else{
								$saleSumData[0]['totalsale'] += 0;
							}
							unset($saleSumData_query);
						}
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
		
		if($paymentMode != 'missing'){
			$mobileRepairSales_query = $this->paginate('MobileRepairSales');
			$mobileRepairSales = $mobileRepairSales_query->toArray();	
		}
		
		
		
		if($paymentMode == 'refunded'){
			$repairidArr = array();
			foreach($mobileRepairSales as $key1 => $value1){
				$repairidArr[] = $value1['mobile_repair_id'];
			}
			if(empty($repairidArr)){
				$repairidArr = array(0 => null);
			}
			$data_query = $this->MobileRepairSales->find('all',
															 array(
																   'fields' => array('mobile_repair_id','amount'),
																   'conditions' => array(
																						'mobile_repair_id IN' => $repairidArr,
																						'refund_status' => '0'
																					),
																	'recursive' => -1
															));
			$data_query = $data_query->hydrate(false);
			if(!empty($data_query)){
				$data = $data_query->toArray();
			}else{
				$data = array();
			}
			$origAmtArr = array();
			foreach($data as $k => $data1){
				$origAmtArr[$data1['mobile_repair_id']] = $data1['amount'];
			}
		}
		
		
		
		//pr($mobileRepairSales);die;
		//getting repair payment details
		if(!empty($repairIdArr)){
			$paymentArr = array();
				$mobileRepairPayment_query = $this->RepairPayments->find('all', array('conditions' => array('RepairPayments.mobile_repair_id In' => $repairIdArr)));
				$mobileRepairPayment_query = $mobileRepairPayment_query->hydrate(false);
				if(!empty($mobileRepairPayment_query)){
					$mobileRepairPayment = $mobileRepairPayment_query->toArray();
				}else{
					$mobileRepairPayment = array();
				}	
		}else{
			$mobileRepairPayment = array();	
		}
		if(count($mobileRepairPayment)){
			foreach($mobileRepairPayment as $rp => $paymentDetail){
				$paymentArr[$paymentDetail['mobile_repair_id']][] = $paymentDetail;
				if(array_key_exists($paymentDetail['mobile_repair_id'],$payment_amount_arr) && array_key_exists($paymentDetail['payment_method'],$payment_amount_arr[$paymentDetail['mobile_repair_id']])){
					$payment_amount_arr[$paymentDetail['mobile_repair_id']][$paymentDetail['payment_method']]+= $paymentDetail['amount'];
				}else{
					$payment_amount_arr[$paymentDetail['mobile_repair_id']][$paymentDetail['payment_method']] = $paymentDetail['amount'];
				}
				
			}
		}else{
			$paymentArr = array();
		}
		
		if(!empty($repairIdArr)){
			$mobileRepairStsArray = array();
			$mobileRepairStatus_query = $this->MobileRepairs->find('all',array('conditions'=>array('MobileRepairs.id IN'=>$repairIdArr),
																		'fields'=>array('id','status')
																		)
															);
			$mobileRepairStatus_query = $mobileRepairStatus_query->hydrate(false);
			if(!empty($mobileRepairStatus_query)){
				$mobileRepairStatus = $mobileRepairStatus_query->toArray();
			}else{
				$mobileRepairStatus = array();
			}
			foreach($mobileRepairStatus as $key=>$mobileRepairSts){
				$mobileRepairStsArray[$mobileRepairSts['id']] = $mobileRepairSts['status'];
			}	
		}else{
			$mobileRepairStsArray = array();
		}
		
		//pr($this->request);die;
		$prem = 0;
		 if(array_key_exists('payment_mode',$this->request->query)){
			$paymentMode = $this->request->query['payment_mode'];
			if($paymentMode == 'missing'){
				if(array_key_exists('kiosk_id',$this->request->query['RepairSale']) &&
						!empty($this->request->query['RepairSale']['kiosk_id'])){
					$kiosk_id =  $this->request->query['RepairSale']['kiosk_id'];
				}else{
					$kiosk_id = '';
				}
				
				$mobileRepairSales = array();
				
				$mobileRepairSales = $this->fetch_missing_record($kiosk_id);
				$prem = 1;
			}
		}
		if($prem == 1){
			$paymentArr = array();
			$missing_payment = 'missing';
			$this->set(compact('missing_payment','origAmtArr','mobileRepairSales','kiosks','users','mobileRepairStsArray','paymentArr','paymentMode','payment_amount_arr','saleSum','refundSum'));
		}else{
			$this->set(compact('origAmtArr','mobileRepairSales','kiosks','users','mobileRepairStsArray','paymentArr','paymentMode','payment_amount_arr','saleSum','refundSum'));	
		}
		
		//$this->layout = 'default';
		$this->render('view_repair_sales');
	}
	
	public function fetch_missing_record($kiosk_id){
		//$query = "SELECT t1.`id`,t1.`mobile_repair_id`,t1.`amount`,t1.`refund_status`,t1.`created` FROM `mobile_repair_sales` t1 LEFT JOIN `repair_payments` t2 ON t2.`mobile_repair_id` = t1.`mobile_repair_id` WHERE t2.`mobile_repair_id` IS NULL AND t1.amount > 0";
		
		$query = "SELECT count(*) as total from mobile_repair_sales";
		
		
		$conn = ConnectionManager::get('default');
        $stmt = $conn->execute($query);
        $result = $stmt ->fetchAll('assoc');
		//$result = $this->MobileRepairSale->query($query);
		//pr($result);die;
		
		$total_count = $result[0]['total'];
		$count = 0;
		$id_arr = array();
		for($i=0; $i<=$total_count; $i+=500){
			if($i == 0){
				continue;
			}
			$count ++;
			$res_query = $this->MobileRepairSales->find('list',array(
															'conditions' => array(
																'amount > ' => 0,
															),
														'keyField' => 'id',
														'valueField' => 'amount',
														'limit' => 500,
														'page' => $count,
														));
			$res_query = $res_query->hydrate(false);
			if(!empty($res_query)){
				$res = $res_query->toArray();
			}else{
				$res = array();
			}
				
			foreach($res as $key => $value){
			
				$query_to_fire = "SELECT * FROM `repair_payments` where id = $key";
				$conn = ConnectionManager::get('default');
				$stmt = $conn->execute($query_to_fire);
				$result_of_query = $stmt ->fetchAll('assoc');
				if(empty($result_of_query)){
					$id_arr[$key] = $key;
				}
				
			}
			unset($res);
		}

		
		
		if(!empty($id_arr)){
			//foreach($result as $key => $value){
			//	$repair_ids[] = $value['mobile_repair_id'];
			//}
			$repair_ids = $id_arr;
			//pr($repair_ids);die;
			$data1_query = $this->MobileRepairSales->find('all',
																 array(
																	   'conditions' => array(
																							 'mobile_repair_id IN' => $repair_ids
																							 )
																));
			$data1_query = $data1_query->hydrate(false);
			if(!empty($data1_query)){
				$data1 = $data1_query->toArray();
			}else{
				$data1 = array();
			}
			foreach($data1 as $key1 => $value1){
				if($value1['refund_status'] == 0 && $value1['amount'] > 0){
					$repair_idArr[$value1['mobile_repair_id']] = $value1['amount'];
				}
			}
			$ids = array_keys($repair_idArr);
			$data_query = $this->MobileRepairSales->find('all',
																 array(
																	   //'fields' => array('mobile_repair_id','mobile_repair_sale_id'),
																	   'conditions' => array(
																							 'MobileRepairSales.mobile_repair_id IN' => $ids,
																							 'MobileRepairSales.refund_status' => 1,
																							 )
																));
			$data_query = $data_query->hydrate(false);
			if(!empty($data_query)){
				$data = $data_query->toArray();
			}
			//pr($data);die;
			foreach($data as $k => $val){
				if(array_key_exists($val['mobile_repair_id'],$repair_idArr)){
					unset($repair_idArr[$val['mobile_repair_id']]);
				}
			}
			$id = array_keys($repair_idArr);
			if(!empty($kiosk_id)){
				$this->paginate = array(
												'conditions' => array(
																		'MobileRepairSales.kiosk_id' => $kiosk_id,
																		'MobileRepairSales.mobile_repair_id IN' => $id,
																		'MobileRepairSales.amount >' => 0
																	  ),
												'order' => ['MobileRepairSales.mobile_repair_id DESC'],//, MobileRepairSale.id DESC
												'limit' => ROWS_PER_PAGE,
												
											  );	
			}else{
				$this->paginate = array(
												'conditions' => array(
																		'MobileRepairSales.mobile_repair_id IN' => $id,
																		'MobileRepairSales.amount >' => 0
																	  ),
												'order' => ['MobileRepairSales.mobile_repair_id DESC'],//, MobileRepairSale.id DESC
												'limit' => ROWS_PER_PAGE,
												'recursive' => -1
											  );	
			}
			
			$mobileRepairSales_query = $this->paginate('MobileRepairSales');
            if(!empty($mobileRepairSales_query)){
              return  $mobileRepairSales = $mobileRepairSales_query->toArray();
            }else{
              return  $mobileRepairSales = array();
            }
		}else{
			 $mobileRepairSales = array();
			return $mobileRepairSales;
		}
	}
	
	public function mobileRepairRefund($id=null){
		//if (!$this->MobileRepairSale->exists($id)) {
		//	throw new NotFoundException(__('Invalid mobile repair sale'));
		//return $this->redirect(array('action' => 'index'));
		//}
		//pr(AuthComponent);die;
		if($this->request->session()->read('Auth.User.group_id') != ADMINISTRATORS && $this->request->session()->read('Auth.User.group_id') != MANAGERS){
			 $this->Flash->success('Only a Manager or an Administrator can initiate a refund');
			return $this->redirect(array('action' => 'view_repair_sales'));
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
			
			$mobileRepairRefund_query = $this->MobileRepairSales->find('all',array(
										'conditions'=>array('MobileRepairSales.id'=>$id),
										'recursive' => -1,
										'order' => 'MobileRepairSales.id DESC',
										)
									    );
			$mobileRepairRefund_query = $mobileRepairRefund_query->hydrate(false);
			if(!empty($mobileRepairRefund_query)){
				$mobileRepairRefund = $mobileRepairRefund_query->first();
			}
           
			$currentKiosk = $mobileRepairRefund['kiosk_id'];
			$kioskaddress_query = $this->Kiosks->find('all',array(
				'fields' => array('Kiosks.address_1',
								  'Kiosks.address_2',
								  'Kiosks.city',
								  'Kiosks.state',
								  'Kiosks.country',
								  'Kiosks.zip',
								  'Kiosks.contact' ),
				'conditions'=> array('Kiosks.id' => $currentKiosk),
				'recursive' => -1
				)
			);
			$kioskaddress_query = $kioskaddress_query->hydrate(false);
			if(!empty($kioskaddress_query)){
				$kioskaddress = $kioskaddress_query->first();
			}
			
			
			$countryOptions = Configure::read('uk_non_uk');
			$this->set(compact('countryOptions'));
			
			$kioskaddress1 = $kioskaddress2 = $kioskstate = $kioskcountry = $kioskzip = $kioskcontact = "";
			if(!empty($kioskaddress['address_1'])){
			    $kioskaddress1 = "<br/>".$kioskaddress['address_1'].", ";
			}
			if(!empty($kioskaddress['address_2'])){
			    $kioskaddress2 = "<br/>".$kioskaddress['address_2'].", " ;
			}
			if(!empty($kioskaddress['city'])){
			    $kioskcity = "\t".$kioskaddress['city'].", ";
			}
			if(!empty($kioskaddress['state'])){
			   $kioskstate =  "<br/>".$kioskaddress['state'].", ";
			}
			if(!empty($kioskaddress['country'])){
			     $kioskcountry = "<br/>".$countryOptions[$kioskaddress['country']].", ";
			}
			if(!empty($kioskaddress['zip'])){
			     $kioskzip = "<br/>".$kioskaddress['zip'] ;
			}
			if(!empty($kioskaddress['contact'])){
			     $kioskcontact =  "<br/>Contact: ".$kioskaddress['contact'];
			}
			
			$this->set(compact('mobileRepairRefund','kiosks','users'));
			//getting repair id for the sale and the related data from the repair table
			$mobileRepairId = $mobileRepairRefund['mobile_repair_id'];
           $mobileRepairsold_on = $mobileRepairRefund['sold_on']; 
			$mobileRepairTableData_query = $this->MobileRepairs->find('all',array(
								'conditions' => array('MobileRepairs.id IN' => $mobileRepairId),
								'recursive' => -1
									 )
							   );
			$mobileRepairTableData_query = $mobileRepairTableData_query->hydrate(false);
			if(!empty($mobileRepairTableData_query)){
				$mobileRepairTableData = $mobileRepairTableData_query->first();
			}
			if(empty($mobileRepairRefund)){			
			$this->Flash->error(__('Invalid mobile repair sale'));
				return $this->redirect(array('action' => 'viewRepairSales'));
			}
			if($mobileRepairTableData['status_refund'] == 1){
				$this->Flash->success(__('The customer has been already refunded for this repair.'));
				return $this->redirect(array('action' => 'viewRepairSales'));
			}elseif($this->request->is(array('post', 'put'))) {
             //   pr($this->request['data']['MobileRepairSale']);die;
				$repair_email_message  = $this->setting['repair_email_message'];
				$mobile_model = $mobileRepairTableData['mobile_model_id'];
				$iemi = $mobileRepairTableData['imei']; 
				$modelname_query = $this->MobileModels->find('all',array(
																	'conditions'=>array('MobileModels.id'=>$mobile_model),
																	'fields' => array('id','model'),
																	));
				$modelname_query = $modelname_query->hydrate(false);
				if(!empty($modelname_query)){
					$modelname = $modelname_query->toArray();
				}
				$model = $modelname['0']['model'];
				if($this->request['data']['MobileRepairSale']['refunded_amount']>$this->request['data']['MobileRepairSale']['amount']){
					$this->Flash->error(__('The refund request could not be saved. Refund amount must be lesser than the Sale Price.'));
					return $this->redirect(array('action' => 'viewRepairSales'));
				}
				if($this->request['data']['MobileRepairSale']['refunded_amount'] <= 0){
					$this->Flash->error(__('The refund request could not be saved. Refund amount must be a positive number and more than zero.'));
					return $this->redirect(array('action' => 'viewRepairSales'));
				}
				$this->request->data['MobileRepairSale']['refund_amount'] = -$this->request['data']['MobileRepairSale']['refunded_amount'];
				$this->request->data['MobileRepairSale']['amount'] = 0;
                $sold_on = $this->request->data['MobileRepairSale']['sold_on'];
                $sold_on = date("Y-m-d h:i:s",strtotime($sold_on));
                $this->request->data['MobileRepairSale']['sold_on'] = $sold_on;
				$entity = $this->MobileRepairSales->newEntity();
				$entity = $this->MobileRepairSales->patchEntity($entity, $this->request->data['MobileRepairSale'],['validate'=>false]);
               // pr($entity);die;
				if($this->MobileRepairSales->save($entity)){
                        $id_s = $id; 
						$refund_remarks = $this->request['data']['MobileRepairSale']['refund_remarks'];
						$refund_amount = $this->request['data']['MobileRepairSale']['refunded_amount'];
						//$refund_on = date('d-m-y h:i A', strtotime($this->request->data['MobileRepairSale']['refund_on']));
						$refund_on = $this->request->data['MobileRepairSale']['refund_on'];
						$refund_by = $this->request['data']['MobileRepairSale']['refund_by'];
                       // $sold_on = $this->request['data']['MobileRepairSale']['sold_on'];
						$after_sale_arr = array(
													'refund_by' => $refund_by,
													'refund_remarks' => $refund_remarks,
													'refund_on' => $refund_on,
                                                   // 'sold_on' =>$sold_on,
													'refund_amount' => $refund_amount,
													'status' => 1
												);
						$entity1 = $this->MobileRepairSales->get($id_s);
						$entity1 = $this->MobileRepairSales->patchEntity($entity1, $after_sale_arr,['validate' => false]);
						$this->MobileRepairSales->save($entity1);
						
					if($this->request->data['MobileRepairSale']['refund_status'] == 1){
						$repairEditData = array('status_refund' => 1);     
						$repair_entity = $this->MobileRepairs->get($mobileRepairId);
						$repair_entity = $this->MobileRepairs->patchEntity($repair_entity,$repairEditData,['validate' => false]);
						//pr($repair_entity);die;
						$this->MobileRepairs->save($repair_entity);
						
						$mobileRepairLogData = array(
									'kiosk_id' => $currentKiosk,
									'user_id' => $this->request->Session()->read('Auth.User.id'),
									'mobile_repair_id' => $mobileRepairId,
									'status' => 1 //for refunded repair
									     );
						$MobileRepairLogs = $this->MobileRepairLogs->newEntity();
						$MobileRepairLogs = $this->MobileRepairLogs->patchEntity($MobileRepairLogs, $mobileRepairLogData,['validate' => false]);
						//pr($MobileRepairLogs);die;
						$this->MobileRepairLogs->save($MobileRepairLogs);
					}
					
					// sending email to the customer
									
					$recipient = $mobileRepairTableData['customer_email'];
					$name = $mobileRepairTableData['customer_fname'];
					$kioskCode = $mobileRepairTableData['kiosk_id'];
					$refundAmount = $this->request['data']['MobileRepairSale']['refunded_amount'];
					$refundOn = date('d-m-y h:i A', strtotime($this->request->data['MobileRepairSale']['refund_on']));
					$content = "Hi ".$name."<br/><br/>As requested, the refund has been processed for an amount of &#163;".$refundAmount."\tfor your mobile(imei:".$iemi.",\tmodel:".$model.")\t on\t".$refundOn.". <br/><br/>Regards,<br/>".$kiosks[$kioskCode].$kioskaddress1.$kioskaddress2.$kioskcity.$kioskstate.$kioskcountry.$kioskzip.$kioskcontact."</br>".$repair_email_message;
					
					//echo $this->fromemail;die;
					$Email = new Email();
					$Email->config('default');
					$Email->viewVars(array('mobileRepairTableData' => $mobileRepairTableData, 'recipient' => $recipient, 'name' => $name, 'refundAmount' => $refundAmount, 'refundOn' => $refundOn, 'kioskCode' => $kioskCode, 'content' => $content));
					$emailTo = $recipient;
					$Email->template('repair_refund');
					$Email->emailFormat('both');
					$Email->to($emailTo);
					$Email->transport(TRANSPORT);
					$Email->from([$this->fromemail => 'Mobile Repair Refund Details']);
					//$Email->sender($this->fromemail);  //$this->fromemail
					$Email->subject('Mobile Repair Refund Details');
					$Email->send();
					
					$this->Flash->success(__('The refund request has been saved'));
					return $this->redirect(array('action' => 'view_repair_sales'));
				}else{
					$this->Flash->error(__('The refund request could not be saved. Please, try again.'));
					return $this->redirect(array('action' => 'view_repair_sales'));
				}
			}
		}		
	}
    
}
?>