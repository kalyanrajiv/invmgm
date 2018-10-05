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

class MobileRepairsController extends AppController{
    
    public $helpers = [
         'Paginator' => ['templates' => 'paginatortemplates']
         ];
     public function initialize()
    {
        parent::initialize();
        $this->loadComponent('ScreenHint');
		$this->loadComponent('TextMessage');
        $this->loadModel('Categories');
        $this->loadModel('Products');
        $this->loadModel('MobileRepairs');
        $this->loadModel('Users');
        $this->loadModel('Kiosks');
        $this->loadModel('RepairPayments');
        $this->loadModel('MobileModels');
		$this->loadModel('MobileRepairSales');
		$this->loadModel('MobileRepairLogs');
		$this->loadModel('MobileRepairPrices');
        $this->loadModel('MobileRepairParts');
        $this->loadModel('ProblemTypes');
        $this->loadModel('MobileConditions');
        $this->loadModel('FunctionConditions');
        $this->loadModel('CommentMobileRepairs');
        $this->loadModel('MobilePurchases');
        $this->loadModel('RetailCustomers');
		$this->loadModel('FaultyConditions');
		$this->loadModel('DefectiveKioskProducts');
		$this->loadModel('Brands');
		$this->loadModel('MobileTransferLogs');
		
		
        $CURRENCY_TYPE = Configure::read('CURRENCY_TYPE');
		$this->set(compact('CURRENCY_TYPE'));
		$this->fromemail = Configure::read('FROM_EMAIL');
		$activeOptions = Configure::read('active');
		$repairStatusUserOptions = Configure::read('repair_statuses_user');
		$repairStatusTechnicianOptions = Configure::read('repair_statuses_technician');
		$countryOptions = Configure::read('uk_non_uk');
		$this->set(compact('activeOptions'));
		$this->set(compact('repairStatusUserOptions','repairStatusTechnicianOptions','countryOptions'));
    }
    
    public $repairDayz = array('0' => '3');
    
    public function updateRepairPayment($repairId = ''){
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
                                            ]);
        $users_query = $users_query->hydrate(false);
        if(!empty($users_query)){
            $users = $users_query->toArray();
        }
		$paymentType = array('Cash' => 'Cash', 'Card' => 'Card');
        //pr($repairId);die;
		$paymentData_query = $this->RepairPayments->find('all',array(
							'conditions' => array('RepairPayments.mobile_repair_id'=>$repairId),
								)
							  );
		
        $paymentData_query = $paymentData_query->hydrate(false);
        if(!empty($paymentData_query)){
            $paymentData = $paymentData_query->toArray();
        }
		$saleData_query = $this->MobileRepairSales->find('all', array(
                                                        'conditions' => array('MobileRepairSales.mobile_repair_id' => $repairId,
                                                                              'MobileRepairSales.refund_status' => 0))
                                                   );
        $saleData_query = $saleData_query->hydrate(false);
        if(!empty($saleData_query)){
            $saleData = $saleData_query->first();
        }
		$saleAmount = $saleData['amount'];
		//LOG: CODE updated after requect received from Inder
        
        $conn = ConnectionManager::get('default');
        $stmt = $conn->execute('SELECT CURDATE() as timeDate From mobile_repair_sales limit 1');
        $currentTime = $stmt ->fetchAll('assoc');
		$currentDate = strtotime($currentTime[0]['timeDate']);
		//$checkTime = strtotime('-24 hours',$time);
		if(count($paymentData) && $this->request->session()->read('Auth.User.group_id') == KIOSK_USERS){
			$created = getdate(strtotime($paymentData['0']['created']));
			$curDate =  $created["year"]."-".$created["mon"]."-".$created["mday"];
			$createdTime = strtotime($curDate);
			if($currentDate != $createdTime){//$checkTime > $createdTime
				$this->Flash->error('Payment can only be updated within same day!');
				return $this->redirect(array('controller' => 'mobile-repair-sales','action' => 'view-repair-sales'));
				die;
			}
		}
		if ($this->request->is(array('post', 'put'))){
			if(array_key_exists('cancel',$this->request->data)){
					$this->Flash->error('You have cancelled transaction!');
					return $this->redirect(array('controller' => 'mobile-repair-sales','action' => 'view-repair-sales'));
					die;
				}
				if(is_array($this->request->data) && array_key_exists('UpdatePayment',$this->request->data) && count($this->request->data['UpdatePayment'])){
					//echo "hi";die;
				//if(array_key_exists('UpdatePayment',$this->request->data)){
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
						return $this->redirect(array('action' => 'update-repair-payment', $repairId));
						die;
					}
					$saveAdminPayment = 0;
                    //die;
					//****saving newly added payment amount
					if(array_key_exists('added_amount',$this->request->data) && is_numeric($this->request->data['added_amount']) && $this->request->data['added_amount'] > 0){
						$paymntData_query = $this->RepairPayments->find('all',array(
																				'conditions' => array('RepairPayments.mobile_repair_id'=>$repairId),
																			)
																);
                        $paymntData_query = $paymntData_query->hydrate(false);
                        if(!empty($paymntData_query)){
                          $paymntData = $paymntData_query->first();  
                        }
                        
						//unsetting the unrequired fields
						unset($paymntData['id']);
						unset($paymntData['payment_method']);
						unset($paymntData['amount']);
						unset($paymntData['created']);
						unset($paymntData['modified']);
						//pr($this->request);die;
						//adding new fields
						$paymntData['payment_method'] = $this->request->data['new_change_mode'];
						$paymntData['amount'] = $this->request->data['added_amount'];
						$RepairPayments = $this->RepairPayments->newEntity();
                        $RepairPayments = $this->RepairPayments->patchEntity($RepairPayments, $paymntData,['validate' => false]);
						if($this->RepairPayments->save($RepairPayments,['validate' => false])){
							$saveAdminPayment++;
						}
					}
					 
					// saving new added payment till here*****
					$sale_amount = $this->request->data['sale_amount'];
					foreach($updatedPaymentData as $paymentId => $paymentMode){
						$paymentDetailData = array(
							///'id' => $paymentId,
							'payment_method' => $paymentMode,
							'amount' => $updatedAmountData[$paymentId]
							   );
                        $RepairPayments1 = $this->RepairPayments->get($paymentId);
                        $RepairPayments1 = $this->RepairPayments->patchEntity($RepairPayments1, $paymentDetailData,['validate' => false]);
						if($this->RepairPayments->save($RepairPayments1,['validate' => false])){
							$saveAdminPayment++;
						}
					}
					if($saveAdminPayment > 0){
						$this->Flash->success('Payment has been successfully updated!');
						return $this->redirect(array('controller' => 'mobile-repair-sales','action' => 'view-repair-sales'));
					}else{
						$this->Flash->error('Payment could not be updated!');
						return $this->redirect(array('action' => 'update-repair-payment',$repairId));
					}
				}
			}
		
		$this->set(compact('paymentData','paymentType','kiosks','users','saleAmount'));
	}
    
    public function addRepairPayment($repairId = ''){
		if($this->request->session()->read('Auth.User.group_id') == MANAGERS || $this->request->session()->read('Auth.User.group_id') == ADMINISTRATORS || $this->request->session()->read('Auth.User.group_id') == FRANCHISE_OWNER){
			$paymntData_query = $this->MobileRepairSales->find('all',array(
																	'conditions' => array('MobileRepairSales.mobile_repair_id'=>$repairId)
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
					return $this->redirect(array('controller' => 'mobile-repair-sales','action' => 'view-repair-sales')); 
					die;
				}
				//pr($this->request);die;
				$amountToPay = $this->request['data']['final_amount'];
				$totalPaymentAmount = 0;
				$amountDesc = array();
				$error = '';
				$errorStr = '';
				$mobile_repair_id = $this->request['data']['Payment']['repair_id'];
				$mobile_repair_sale_id = $this->request['data']['Payment']['sale_id'];
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
					return $this->redirect(array('action'=>'addRepairPayment'));
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
						echo $amount;
						$paymentDetailData = array(
													'kiosk_id' => $kiosk_id,
													'user_id' => $user_id,
													'mobile_repair_id'=> $mobile_repair_id,
													'mobile_repair_sale_id' =>$mobile_repair_sale_id,
													'payment_method' => $paymentMethod,
													'description' => $paymentDescription,
													'amount' => $amount,
													'payment_status' => 1,
													'status' => 1,//this 1 currently does not have any relevance
												);
					}
					//pr($paymentDetailData);
					$RepairPayments = $this->RepairPayments->newEntity();
                    $RepairPayments = $this->RepairPayments->patchEntity($RepairPayments, $paymentDetailData,['validate' => false]);
					if($this->RepairPayments->save($RepairPayments)){
						$counter++;
						
					}
				}//die;
				if($counter>0){
					//return $this->redirect(array('controller'=>'kiosk_product_sales','action'=>'sell_products',$product_receipt_id));
					$this->Flash->success("Payment Updated");
					return $this->redirect("/mobile-repair-sales/view-repair-sales");
				}else{
					$flashMessage = ("Payment could not be created. Please try again");
					$this->Flash->error($flashMessage);
					return $this->redirect(array('controller'=>'mobile-repair-sales','action'=>'view-repair-sales'));
				}
			}
		}else{
			$this->Session->setFlash('Only admin  can add payment');
			return $this->redirect(array('controller'=>'mobile-repair-sales','action'=>'view-repair-sales'));
		}
	}
    
    public function repairTechnicianReport(){
		$userName = $this->Users->find('list',[
                                                    'keyField' => 'id',
                                                    'valueField' => 'username'
                                              ]);
        if(!empty($userName)){
            $userName = $userName->toArray();
        }else{
            $userName = array();
        }
		$kiosks_query = $this->Kiosks->find('list',[
                                              'keyField' => 'id',
                                              'valueField' => 'name',
                                              'conditions'=>['Kiosks.kiosk_type'=>1],
                                              'order' => ['Kiosks.name asc']
                                            ]);
        if(!empty($kiosks_query)){
            $kiosks = $kiosks_query->toArray();
        }else{
            $kiosks = array();
        }
		$serviceCenters_query = $this->Kiosks->find('list',[
                                                      'keyField' => 'id',
                                                      'valueField' => 'name',
                                                      'conditions'=>['Kiosks.kiosk_type'=>2],
                                                      'order' => ['Kiosks.name asc']
                                                    ]);
        if(!empty($serviceCenters_query)){
            $serviceCenters = $serviceCenters_query->toArray();
        }else{
            $serviceCenters = array();
        }
		$users_query = $this->Users->find('list',[
                                            'conditions'=>['Users.group_id'=>7],
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
		$lastDay = date("Y-m-t");
		$start = date("Y-m-d",strtotime("-1 day",strtotime($firstDay)));
		$end = date("Y-m-d",strtotime("+1 day",strtotime($lastDay)));
		$technicians_query = $this->Users->find('list',[
                                                  'conditions' => ['Users.group_id' => 7],
                                                  'keyField' => 'id',
                                                  'valueField' => 'username'
                                                 ]);
        if(!empty($technicians_query)){
            $technicians = $technicians_query->toArray();
        }else{
            $technicians = array();
        }
		$technicianKeys = array_keys($technicians);
		if(empty($technicianKeys)){
			$technicianKeys = array(0 => null);
		}
		$kioskId = "";
		$userId = "";
		
		if($this->request->is('post')){
			$start_date = $this->request->data['start_date'];
			if(!empty($start_date)){
				$firstDay = strtotime($start_date);
				$start = date("Y-m-d",strtotime("-1 day",$firstDay));
			}
			
			$end_date = $this->request->data['end_date'];
			if(!empty($end_date)){
				$lastDay = strtotime($end_date);
				$end = date("Y-m-d",strtotime("+1 day",$lastDay));
			}
			
			$user = $this->request->data['user'];
			$kiosk = $this->request->data['kiosk'];
			$service_center = $this->request->data['service_center'];
			
			if(empty($user) && empty($kiosk) && empty($service_center)){
				$repairData_query = $this->MobileRepairLogs->find('all',array('conditions'=>array("DATE(MobileRepairLogs.created) > '$start'" ,
											"DATE(MobileRepairLogs.created) < '$end'",
											'MobileRepairLogs.repair_status IN' => array(DISPATCHED_2_KIOSK_REPAIRED, DISPATCHED_2_KIOSK_UNREPAIRED),
											'MobileRepairLogs.user_id IN' => $technicianKeys
											)
								       ));
                $repairData_query = $repairData_query->hydrate(false);
                if(!empty($repairData_query)){
                    $repairData = $repairData_query->toArray();
                }else{
                    $repairData = array();
                }
			}elseif(!empty($user) && empty($kiosk) && empty($service_center)){
				$userId = $user;
				$repairData_query = $this->MobileRepairLogs->find('all',array('conditions'=>array("DATE(MobileRepairLogs.created) > '$start'",
											"DATE(MobileRepairLogs.created) < '$end'",
											'MobileRepairLogs.repair_status IN' => array(DISPATCHED_2_KIOSK_REPAIRED, DISPATCHED_2_KIOSK_UNREPAIRED),
											'MobileRepairLogs.user_id IN' => $userId
											)
								       ));
                $repairData_query = $repairData_query->hydrate(false);
                if(!empty($repairData_query)){
                    $repairData = $repairData_query->toArray();
                }else{
                    $repairData = array();
                }
			}elseif(!empty($kiosk) && empty($user) && empty($service_center)){
				$kioskId = $kiosk;
				$repairData_query = $this->MobileRepairLogs->find('all',array('conditions'=>array("DATE(MobileRepairLogs.created) > '$start'",
											"DATE(MobileRepairLogs.created) < '$end'",
											'MobileRepairLogs.repair_status IN' => array(DISPATCHED_2_KIOSK_REPAIRED, DISPATCHED_2_KIOSK_UNREPAIRED),
											'MobileRepairLogs.user_id IN' => $technicianKeys,
											'MobileRepairLogs.kiosk_id' => $kioskId,
											)
								       ));
				
                $repairData_query = $repairData_query->hydrate(false);
                if(!empty($repairData_query)){
                    $repairData = $repairData_query->toArray();
                }else{
                    $repairData = array();
                }
			}elseif(!empty($service_center) && empty($user) && empty($kiosk)){
				$kioskId = $kiosk;
				$repairData_query = $this->MobileRepairLogs->find('all',array('conditions'=>array("DATE(MobileRepairLogs.created) > '$start'",
											"DATE(MobileRepairLogs.created) < '$end'",
											'MobileRepairLogs.repair_status IN' => array(DISPATCHED_2_KIOSK_REPAIRED, DISPATCHED_2_KIOSK_UNREPAIRED),
											'MobileRepairLogs.service_center_id' => $service_center,
											'MobileRepairLogs.user_id IN' => $technicianKeys
											)
								       ));
                $repairData_query = $repairData_query->hydrate(false);
                if(!empty($repairData_query)){
                    $repairData = $repairData_query->toArray();
                }else{
                    $repairData = array();
                }
			}elseif(!empty($service_center) && !empty($user) && empty($kiosk)){
				$userId = $user;
				$repairData_query = $this->MobileRepairLogs->find('all',array('conditions'=>array("DATE(MobileRepairLogs.created) > '$start'",
											"DATE(MobileRepairLogs.created) < '$end'",
											'MobileRepairLogs.repair_status IN' => array(DISPATCHED_2_KIOSK_REPAIRED, DISPATCHED_2_KIOSK_UNREPAIRED),
											'MobileRepairLogs.service_center_id' => $service_center,
											'MobileRepairLogs.user_id IN' => $userId
											)
								       ));
                $repairData_query = $repairData_query->hydrate(false);
                if(!empty($repairData_query)){
                    $repairData = $repairData_query->toArray();
                }else{
                    $repairData = array();
                }
			}elseif(!empty($service_center) && empty($user) && !empty($kiosk)){
				$kioskId = $kiosk;
				$repairData_query = $this->MobileRepairLogs->find('all',array('conditions'=>array("DATE(MobileRepairLogs.created) > '$start'",
											"DATE(MobileRepairLogs.created) < '$end'",
											'MobileRepairLogs.repair_status IN' => array(DISPATCHED_2_KIOSK_REPAIRED, DISPATCHED_2_KIOSK_UNREPAIRED),
											'MobileRepairLogs.service_center_id' => $service_center,
											'MobileRepairLogs.kiosk_id' => $kioskId,
											'MobileRepairLogs.user_id IN' => $technicianKeys
											)
								       ));
                $repairData_query = $repairData_query->hydrate(false);
                if(!empty($repairData_query)){
                    $repairData = $repairData_query->toArray();
                }else{
                    $repairData = array();
                }
			}elseif(empty($service_center) && !empty($user) && !empty($kiosk)){
				$userId = $user;
				$kioskId = $kiosk;
				$repairData = $this->MobileRepairLogs->find('all',array('conditions'=>array("DATE(MobileRepairLogs.created) > '$start'",
											"DATE(MobileRepairLogs.created) < '$end'",
											'MobileRepairLogs.repair_status IN' => array(DISPATCHED_2_KIOSK_REPAIRED, DISPATCHED_2_KIOSK_UNREPAIRED),
											'MobileRepairLogs.kiosk_id' => $kioskId,
											'MobileRepairLogs.user_id IN' => $userId
											)
								       ));
                $repairData = $repairData->hydrate(false);
                if(!empty($repairData)){
                    $repairData = $repairData->toArray();
                }else{
                    $repairData = array();
                }
			}else{
				$userId = $user;
				$kioskId = $kiosk;
				$repairData_query = $this->MobileRepairLogs->find('all',array('conditions'=>array("DATE(MobileRepairLogs.created) > '$start'",
											"DATE(MobileRepairLogs.created) < '$end'",
											'MobileRepairLogs.repair_status IN' => array(DISPATCHED_2_KIOSK_REPAIRED, DISPATCHED_2_KIOSK_UNREPAIRED),
											'MobileRepairLogs.kiosk_id' => $kioskId,
											'MobileRepairLogs.user_id IN' => $userId,
											'MobileRepairLogs.service_center_id' => $service_center
											)
								       ));
                $repairData_query = $repairData_query->hydrate(false);
                if(!empty($repairData_query)){
                    $repairData = $repairData_query->toArray();
                }else{
                    $repairData = array();
                }
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
				$managerKiosk = $this->get_kiosk();
				if(!empty($managerKiosk)){
					$repairData_query = $this->MobileRepairLogs->find('all',array('conditions'=>array(
																							"DATE(MobileRepairLogs.created) > '$start'",
																							"DATE(MobileRepairLogs.created) < '$end'",
																							'MobileRepairLogs.repair_status IN' =>
																	array(DISPATCHED_2_KIOSK_REPAIRED, DISPATCHED_2_KIOSK_UNREPAIRED),
																							'MobileRepairLogs.user_id IN' => $technicianKeys,
																							'MobileRepairLogs.kiosk_id IN' => $managerKiosk,			
																					),
																				'recursive' => -1));	
				}else{
					$repairData_query = $this->MobileRepairLogs->find('all',array('conditions'=>array("DATE(MobileRepairLogs.created) > '$start'",
											"DATE(MobileRepairLogs.created) < '$end'",
											'MobileRepairLogs.repair_status IN' => array(DISPATCHED_2_KIOSK_REPAIRED, DISPATCHED_2_KIOSK_UNREPAIRED),
											'MobileRepairLogs.user_id IN' => $technicianKeys
											),
								       'recursive' => -1));
				}
			}else{
				$repairData_query = $this->MobileRepairLogs->find('all',array('conditions'=>array("DATE(MobileRepairLogs.created) > '$start'",
											"DATE(MobileRepairLogs.created) < '$end'",
											'MobileRepairLogs.repair_status IN' => array(DISPATCHED_2_KIOSK_REPAIRED, DISPATCHED_2_KIOSK_UNREPAIRED),
											'MobileRepairLogs.user_id IN' => $technicianKeys
											),
								       'recursive' => -1));
			}
			
			
            $repairData_query = $repairData_query->hydrate(false);
            if(!empty($repairData_query)){
                $repairData = $repairData_query->toArray();
            }else{
                $repairData = array();
            }
		}
		//pr($this->MobileRepairSale->find('all', array('recursive' => -1)));
		//pr($this->MobileRepair->find('all', array('recursive' => -1)));
        
		$userArray = array();
		$userRepairIds = array();
		$repairIds = array();
		foreach($repairData as $key => $repairLog){
			$userArray[$repairLog['user_id']][] = $repairLog;
			if($repairLog['repair_status'] != DISPATCHED_2_KIOSK_UNREPAIRED){
				$userRepairIds[$repairLog['user_id']][] = $repairLog['mobile_repair_id'];
			}
			$repairIds[$repairLog['mobile_repair_id']] = $repairLog['mobile_repair_id'];
		}
		//pr($userRepairIds);
		foreach($userRepairIds as $user_id => $userRepairArr){
            $query = $this->MobileRepairSales->find('all',['conditions' => ['MobileRepairSales.mobile_repair_id IN' => $userRepairArr,
									      'MobileRepairSales.refund_status' => 0]]);
                $query
                          ->select(['sumSale' => $query->func()->sum('MobileRepairSales.amount')]);
                $result = $query->first();
                if(!empty($result)){
                    $sum_sale[$user_id] = $result->toArray();
                }else{
                    $sum_sale[$user_id] = array();
                }
		}
		
		$checkSale = array();
		$finalSale = array();
		//checking if this repair id exists in repair sale table
		//pr($userRepairIds);die;
		foreach($userRepairIds as $user_id => $userRepairArr){
			foreach($userRepairArr as $key => $userRepair){
				
				//$checkSale[$user_id][$userRepair] = $this->MobileRepairSales->find('count',array(
				//			'conditions' => array('MobileRepairSale.mobile_repair_id' => $userRepair,
				//					      'MobileRepairSale.refund_status' => 0),
				//			'recursive' => -1
				//			)
				//	      );
                
                $query_count = $this->MobileRepairSales->find('all',['conditions' => ['MobileRepairSales.mobile_repair_id' => $userRepair,
									      'MobileRepairSales.refund_status' => 0]]);
                $query_count->select(['count' => $query_count->func()->count('*')]);
                //pr($query_count);die;
                $query_count = $query_count->hydrate(false);
                if(!empty($query_count)){
					
					$count = $query_count->toArray();
                    $checkSale[$user_id][$userRepair] = $count[0]['count'];
					
                }else{
					
                    $checkSale[$user_id][$userRepair] = array();
                }
                
			}
			
			$finalSale[$user_id] = array_keys($checkSale[$user_id]);//array_keys(array_filter($checkSale[$user_id]));//to remove the array with zero values and get keys which are actually repair ids
			
		}
		
		//$checkSale is an array that contains repair id and their corresponding count in sale table, only these should be considered for final data
		#pr($this->MobileRepairSale->find('all',array('order' => 'MobileRepairSale.mobile_repair_id DESC','recursive'=>-1)));
		//pr($userRepairIds);die;
		foreach($userRepairIds as $user_id => $userRepairArr){
            $query_mobile_repair = $this->MobileRepairSales->find('all',['conditions' => ['MobileRepairSales.mobile_repair_id IN' => $userRepairArr,
									      'MobileRepairSales.refund_status' => 1]]);
                $query_mobile_repair
                          ->select(['refundSale' => $query_mobile_repair->func()->sum('MobileRepairSales.refund_amount')]);
                $result_mobile_repair = $query_mobile_repair->hydrate(false);
                if(!empty($result_mobile_repair)){
                    $refund_sale[$user_id] = $result_mobile_repair->toArray();
                }else{
                    $refund_sale[$user_id] = array();
                }
            
		}
		//pr($refund_sale);
		//getting brand id, model id, problem type from mobile repair table for above ids
		$repairDetail = array();
		//pr($finalSale);die;
		foreach($finalSale as $user_id => $userRepairArr){
			$repairDetail_query = $this->MobileRepairs->find('all',array('conditions'=>array('MobileRepairs.id IN'=>$userRepairArr),'fields'=>array('id','brand_id','mobile_model_id','problem_type','net_cost')));
            $repairDetail_query = $repairDetail_query->hydrate(false);
            if(!empty($repairDetail_query)){
                $repairDetail[$user_id] = $repairDetail_query->toArray();
            }else{
                $repairDetail[$user_id] = array();
            }
		}
		
		//pr($repairDetail);die;
		//getting cost price corresponding to the brand,model,problem combination
		//pr($repairDetail);die;
		
		$repairFixedCostArr = $repairCostArr = array();
		if(!empty($repairDetail)){
			foreach($repairDetail as $user_id => $repairInf){
				foreach($repairInf as $key => $repairInfo){
					$repairId = $repairInfo['id'];
					$brand_id = $repairInfo['brand_id'];
					$mobile_model_id = $repairInfo['mobile_model_id'];
					$problemTypeArr = explode('|',$repairInfo['problem_type']);
					$repairFixedCostArr[$user_id][$repairId] = $repairInfo['net_cost'];
					$repairCostArr_query = $this->MobileRepairPrices->find('all',array('conditions'=>array('MobileRepairPrices.brand_id IN'=>$brand_id,'MobileRepairPrices.mobile_model_id IN'=>$mobile_model_id,'MobileRepairPrices.problem_type IN'=>$problemTypeArr,'MobileRepairPrices.repair_price > 0'),'fields'=>array('MobileRepairPrices.repair_cost')));
					$repairCostArr_query = $repairCostArr_query->hydrate(false);
					if(!empty($repairCostArr_query)){
						
						//echo $brand_id;echo'<br/>';
						//echo $mobile_model_id;echo'<br/>';
						//pr($problemTypeArr);echo'<br/>';
						$repairCostArr[$user_id][] = $repairCostArr_query->toArray();
						//pr($repairCostArr);echo'<br/>';
					}else{
						
					}
				}
			}
		}
		$repairCost = array();
		//pr($repairCostArr);die;
		if(!empty($repairCostArr)){
			foreach($repairCostArr as $user_id => $repairCostDetail){
				foreach($repairCostDetail as $key => $repairCostInfo){
					foreach($repairCostInfo as $r => $repair_cost_info){
						if(array_key_exists($user_id,$repairCost)){
							$repairCost[$user_id]+= $repair_cost_info['repair_cost'];
						}else{
							$repairCost[$user_id] = $repair_cost_info['repair_cost'];
						}
					}
				}
			}
		}
		$repairFixedCost = array();
		if(!empty($repairFixedCostArr)){
			foreach($repairFixedCostArr as $k => $v){
				foreach($v as $key => $cst){
					if(array_key_exists($k,$repairFixedCost)){
						$repairFixedCost[$k] += $cst;
					}else{
						$repairFixedCost[$k] = $cst;
					}
				}
			}
		}
		//pr($repairFixedCostArr);die;
		$hint = $this->ScreenHint->hint('mobile_repairs','repair_technician_report');
					if(!$hint){
						$hint = "";
					}
		
		$this->set(compact('hint','kiosks','users','serviceCenters','repairData','userArray','sum_sale','refund_sale','repairCost','userName','repairFixedCost'));
	}
    
    public function multipleRepairPartReport() {
        $partReport_query = $this->MobileRepairParts->find('all',['group' => 'mobile_repair_id HAVING count > 1','order' => 'MobileRepairParts.id desc']);
        $partReport_query
                ->select('mobile_repair_id')
                ->select(['count' => $partReport_query->func()->count('MobileRepairParts.id')]);
        $partReport_query = $partReport_query->hydrate(false);
        if(!empty($partReport_query)){
            $partReport = $partReport_query->toArray();
        }else{
            $partReport = array();
        }
		$repair_ids = array();
		$repairPartArr = array();
		foreach($partReport as $key => $prts){
            //pr($prts);die;
			$repair_ids[$prts['mobile_repair_id']] = $prts['mobile_repair_id'];
			$repairPartArr[$prts['mobile_repair_id']] = $prts['count'];
		}
		if(empty($repair_ids)){
			$repair_ids = array(0 => null);
		}
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
		$kiosk_id = $this->request->Session()->read('kiosk_id');
		
		if(true){ // $this->request->session()->read('Auth.User.group_id')== MANAGERS
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
					$managerKiosk = $this->get_kiosk();//pr($managerKiosk);die;		
				}else{
					$managerKiosk = array();
				}
			if(!empty($managerKiosk)){
				$kiosk_id = $managerKiosk;
				$this->paginate = [
                            'conditions' => ['MobileRepairs.id IN' => $repair_ids,
											 'MobileRepairs.kiosk_id IN' => $kiosk_id,
											],
                            'limit' => ROWS_PER_PAGE,
                            'order' => ['MobileRepairs.id DESC'],
                            'contain' => ['Kiosks']
                          ];
			}else{
				$this->paginate = [
                            'conditions' => ['MobileRepairs.id IN' => $repair_ids],
                            'limit' => ROWS_PER_PAGE,
                            'order' => ['MobileRepairs.id DESC'],
                            'contain' => ['Kiosks']
                          ];
			}
		}else{
			$this->paginate = [
                            'conditions' => ['MobileRepairs.id IN' => $repair_ids],
                            'limit' => ROWS_PER_PAGE,
                            'order' => ['MobileRepairs.id DESC'],
                            'contain' => ['Kiosks']
                          ];
		}
		
		$mobileRepairs_query = $this->paginate('MobileRepairs');
        if(!empty($mobileRepairs_query)){
            $mobileRepairs = $mobileRepairs_query->toArray();
        }else{
            $mobileRepairs = array();
        }

		$repairIDs = array();
		$users_query = $this->Users->find('list',[
                                                'conditions' => ['Users.group_id' => 7],
                                                'keyField' => 'id',
                                                'valueField' => 'username',
                                           ]);
        //pr($users_query);die;
        $users_query = $users_query->hydrate(false);
        if(!empty($users_query)){
            $users = $users_query->toArray();
        }else{
            $users = array();
        }
        //pr($users);
		foreach($mobileRepairs as $mobileRepair){
            //pr($mobileRepair);die;
			$repairIDs[] = $mobileRepair->id;
		}
		$repairTechniciansIds = array_keys($users);
		$viewRepairParts = array();
		
		if(count($repairIDs)){
			foreach($repairIDs as $repairID){
                if(empty($repairID)){
                    $repairID = array(0 => null);
                }
				$repLog_query = $this->MobileRepairLogs->find('all', array('conditions' => array(
							'mobile_repair_id IN' => $repairID,
							'user_id IN' => $repairTechniciansIds),
							'recursive' => -1,
							'order' => 'MobileRepairLogs.id DESC')
				);
                //pr($repLog_query);die;
                $repLog_query = $repLog_query->hydrate(false);
                if(!empty($repLog_query)){
                    $repLog = $repLog_query->first();
                }else{
                    $repLog = array();
                }
				if(count($repLog) >= 1){
                    //pr($repLog);die;
					$repairLogDetails[$repLog['mobile_repair_id']] = $users[$repLog['user_id']];
				}
			}
			
			$viewRepairParts_query = $this->MobileRepairParts->find('all',array('conditions' => array('MobileRepairParts.mobile_repair_id IN' => $repairIDs)));
            $viewRepairParts_query = $viewRepairParts_query->hydrate(false);
            if(!empty($viewRepairParts_query)){
                $viewRepairParts = $viewRepairParts_query->toArray();
            }else{
                $viewRepairParts = array();
            }
		}
		
		
		$mobileModels_query = $this->MobileModels->find('list',[
                                                            'keyField' => 'id',
                                                            'valueField' => 'model',
															'order'=>'model asc',
                                                            'conditions' => ['MobileModels.status' => 1]
                                                        ]
                                                 );
        $mobileModels_query = $mobileModels_query->hydrate(false);
        if(!empty($mobileModels_query)){
            $mobileModels = $mobileModels_query->toArray();
        }else{
            $mobileModels = array();
        }
		$hint = $this->ScreenHint->hint('mobile_repairs','multiple_repair_part_report');
					if(!$hint){
						$hint = "";
					}
                    //pr($hint);die;
		$this->set(compact('hint','viewRepairParts','repairLogDetails','mobileModels','kiosks', 'repairPartArr'));	
		$this->set('mobileRepairs', $mobileRepairs);
	}
    
    public function multiplePartsSearch(){
        $partReport_query = $this->MobileRepairParts->find('all',['group' => 'mobile_repair_id HAVING count > 1','order' => 'MobileRepairParts.id desc']);
        $partReport_query
                ->select('mobile_repair_id')
                ->select(['count' => $partReport_query->func()->count('MobileRepairParts.id')]);
        $partReport_query = $partReport_query->hydrate(false);
        if(!empty($partReport_query)){
            $partReport = $partReport_query->toArray();
        }else{
            $partReport = array();
        }
		$repair_ids = array();
		$repairPartArr = array();
		foreach($partReport as $key => $prts){
            //pr($prts);die;
			$repair_ids[$prts['mobile_repair_id']] = $prts['mobile_repair_id'];
			$repairPartArr[$prts['mobile_repair_id']] = $prts['count'];
		}
		if(empty($repair_ids)){
			$repair_ids = array(0 => null);
		}
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
        //pr($this->request->query);die;
		$imei = $this->request->query['imei'];
		$conditionArr = array();
		
		if(!empty($imei)){
			$conditionArr[] = "`imei` like '%$imei%'";
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
			!empty($this->request->query['end_date'])){
			$conditionArr[] = array(
						"MobileRepairs.modified >" => date('Y-m-d', strtotime($this->request->query['start_date'])),
						"MobileRepairs.modified <" => date('Y-m-d', strtotime($this->request->query['end_date']. ' +1 Days')),			
					       );
		}
		//pr($this->request->query);die;
		$dataKioskID = '';
		if(array_key_exists('MobileRepair', $this->request->query)){
			$kioskId = $this->request->query['MobileRepair']['kiosk_id'];
			if(array_key_exists('kiosk_id',$this->request->query['MobileRepair']) && !empty($this->request->query['MobileRepair']['kiosk_id'])){
				$conditionArr[] = array('MobileRepairs.kiosk_id' =>$this->request->query['MobileRepair']['kiosk_id']);
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
					$managerKiosk = $this->get_kiosk();
					if(!empty($managerKiosk)){
						$conditionArr[] = array('MobileRepairs.kiosk_id IN' =>$managerKiosk);	
					}
					
				}
			}
			$dataKioskID = $this->request->query['MobileRepair']['kiosk_id'];
		}
		$this->set('kioskId', $dataKioskID);
		$this->paginate = [
                            'conditions' => [$conditionArr,['MobileRepairs.id IN' => $repair_ids]],
                            'limit' => ROWS_PER_PAGE,
                            'order' => ['MobileRepairs.id desc'],
                            'contain' => ['Kiosks']
		                  ];
		
		$mobileRepairs_query = $this->paginate('MobileRepairs');
        if(!empty($mobileRepairs_query)){
            $mobileRepairs = $mobileRepairs_query->toArray();
        }else{
            $mobileRepairs = array();
        }
		$mobileModels_query = $this->MobileModels->find('list',[
                                                            'keyField' => 'id',
                                                            'valueField' => 'model',
															'order'=>'model asc',
                                                            'conditions' => ['MobileModels.status' => 1]
                                                        ]
                                                );
        $mobileModels_query = $mobileModels_query->hydrate(false);
        if(!empty($mobileModels_query)){
            $mobileModels = $mobileModels_query->toArray();
        }else{
            $mobileModels = array();
        }
		$repairIDs = array();
		foreach($mobileRepairs as $mobileRepair){
            //pr($mobileRepair);die;
			$repairIDs[] = $mobileRepair->id;
		}
		
		$viewRepairParts = array();
		if(count($repairIDs)){
			$viewRepairParts_query = $this->MobileRepairParts->find('all',array('conditions' => array('MobileRepairParts.mobile_repair_id IN' => $repairIDs)));
            $viewRepairParts_query = $viewRepairParts_query->hydrate(false);
            if(!empty($viewRepairParts_query)){
                $viewRepairParts = $viewRepairParts_query->toArray();
            }else{
                $viewRepairParts = array();
            }
		}
		
		$hint = $this->ScreenHint->hint('mobile_repairs','multiple_repair_part_report');
					if(!$hint){
						$hint = "";
					}
		
		$this->set(compact('hint','mobileRepairs','viewRepairParts','mobileModels','kiosks', 'repairPartArr'));
		//$this->layout = 'default';
		//$this->viewPath = 'mobileRepairs';
		$this->render('multiple_repair_part_report');
	}
    
    public function view($id = null) {
		if (!$this->MobileRepairs->exists($id)) {
			throw new NotFoundException(__('Invalid mobile repair'));
		}
		$options = [
                        'conditions' => ['MobileRepairs.id' => $id],
                        'contain' => 'Kiosks'
                    ];
		$mobileRepair_query = $this->MobileRepairs->find('all', $options);
        $mobileRepair_query = $mobileRepair_query->hydrate(false);
        if(!empty($mobileRepair_query)){
            $mobileRepair = $mobileRepair_query->first();
        }else{
            $mobileRepair = array();
        }
        $this->set('mobileRepair', $mobileRepair);
		//pr($this->MobileRepair->find('first', $options));
		$problemTypeOptions_query = $this->ProblemTypes->find('list',[
                                                                    'keyField' => 'id',
                                                                    'valueField' => 'problem_type'
                                                               ]);
        $problemTypeOptions_query = $problemTypeOptions_query->hydrate(false);
        if(!empty($problemTypeOptions_query)){
            $problemTypeOptions = $problemTypeOptions_query->toArray();
        }else{
            $problemTypeOptions = array();
        }
		$repairLogs_query = $this->MobileRepairLogs->find('all',array(
							'conditions' => array('MobileRepairLogs.mobile_repair_id' => $id),
							'order' => array('MobileRepairLogs.id DESC')
								)
							   );
        $repairLogs_query = $repairLogs_query->hydrate(false);
        if(!empty($repairLogs_query)){
            $repairLogs = $repairLogs_query->toArray();
        }else{
            $repairLogs = array();
        }
		$mobileConditions_query = $this->MobileConditions->find('list',[
                                                                    'keyField' => 'id',
                                                                    'valueField' => 'mobile_condition'
                                                                 ]);
        $mobileConditions_query = $mobileConditions_query->hydrate(false);
        if(!empty($mobileConditions_query)){
            $mobileConditions = $mobileConditions_query->toArray();
        }else{
            $mobileConditions = array();
        }
		$functionConditions_query = $this->FunctionConditions->find('list',[
                                                                        'keyField' => 'id',
                                                                        'valueField' => 'function_condition'
                                                                     ]);
        $functionConditions_query = $functionConditions_query->hydrate(false);
        if(!empty($functionConditions_query)){
            $functionConditions = $functionConditions_query->toArray();
        }else{
            $functionConditions = array();
        }
		$users_query = $this->Users->find('list',[
                                                'keyField' => 'id',
                                                'valueField' => 'username'
                                           ]);
        $users_query = $users_query->hydrate(false);
        if(!empty($users_query)){
            $users = $users_query->toArray();
        }else{
            $users = array();
        }
		
		$kiosks_query = $this->MobileRepairs->Kiosks->find('list',[
                                                                'keyField' => 'id',
                                                                'valueField' => 'name',
                                                                'conditions' => ['Kiosks.status' => 1]
                                                            ]);
        $kiosks_query = $kiosks_query->hydrate(false);
        if(!empty($kiosks_query)){
            $kiosks = $kiosks_query->toArray();
        }else{
            $kiosks = array();
        }
		
		$comments_query = $this->MobileRepairs->CommentMobileRepairs->find('all', array(
									 //'fields' => array('*'),
									 'conditions' => array('CommentMobileRepairs.status' => 1,'CommentMobileRepairs.mobile_repair_id' => $id),
									 'contain' => array('Users'),
									 'order' => array('CommentMobileRepairs.id DESC'),
									 'limit' => 5
									));
        //pr($comments_query);die;
        $comments_query = $comments_query->hydrate(false);
        if(!empty($comments_query)){
            $comments = $comments_query->toArray();
        }else{
            $comments = array();
        }
        //pr($comments);die;
		$brands_query = $this->MobileRepairs->Brands->find('list',[
                                                                'keyField' => 'id',
                                                                'valueField' => 'brand',
																'order'=>'brand asc',
                                                                'conditions' => ['Brands.status' => 1]
                                                            ]
                                                    );
        $brands_query = $brands_query->hydrate(false);
        if(!empty($brands_query)){
            $brands = $brands_query->toArray();
        }else{
            $brands = array();
        }
		$mobileModels_query = $this->MobileRepairs->MobileModels->find('list',[
                                                                            'keyField' => 'id',
                                                                            'valueField' => 'model',
																			'order'=>'model asc',
                                                                            'conditions' => ['MobileModels.status' => 1]
                                                                        ]
                                                                );
        $mobileModels_query = $mobileModels_query->hydrate(false);
        if(!empty($mobileModels_query)){
            $mobileModels = $mobileModels_query->toArray();
        }else{
            $mobileModels = array();
        }
		
		$viewRepairParts_query = $this->MobileRepairParts->find('all',array(
								'conditions' => array('MobileRepairParts.mobile_repair_id' => $id)
								)
							);
        $viewRepairParts_query = $viewRepairParts_query->hydrate(false);
        if(!empty($viewRepairParts_query)){
            $viewRepairParts = $viewRepairParts_query->toArray();
        }else{
            $viewRepairParts = array();
        }
		$products_query = $this->Products->find('list',[
                                                    'keyField' => 'id',
                                                    'valueField' => 'product'
                                                 ]);
        $products_query = $products_query->hydrate(false);
        if(!empty($products_query)){
            $products = $products_query->toArray();
        }else{
            $products = array();
        }
		
		$dataPerId_query = $this->MobileRepairs->find('all',array(
							'conditions' => array('MobileRepairs.id'=>$id),
                                                ));
        $dataPerId_query = $dataPerId_query->hydrate(false);
        if(!empty($dataPerId_query)){
            $dataPerId = $dataPerId_query->first();
        }else{
            $dataPerId = array();
        }
        //pr($dataPerId);die;
        $brandId = $dataPerId['brand_id'];
        $mobileModelId = $dataPerId['mobile_model_id'];
        $problemTypStr = $dataPerId['problem_type'];
        $problemTypArr = explode("|",$problemTypStr);
        foreach($problemTypArr as $p => $problemTyp){
            $conn = ConnectionManager::get('default');
            $stmt = $conn->execute("SELECT `repair_days` from `mobile_repair_prices` WHERE `brand_id`='$brandId' AND `mobile_model_id`='$mobileModelId' AND `problem_type`='$problemTyp'"); 
            $repairDays[] = $stmt ->fetchAll('assoc');
            //$repairDays[] = $this->MobileRepairPrice->query("SELECT `repair_days` from `mobile_repair_prices` WHERE `brand_id`='$brandId' AND `mobile_model_id`='$mobileModelId' AND `problem_type`='$problemTyp'");
        }
        
        $repairDayz = $this->repairDayz;//default
        //pr($repairDays);die;
        if(!empty($repairDays[0][0]['repair_days'])){
            $repairDayz['repair_days_a'] = $repairDays[0][0]['repair_days'];	
        }
        
        if(!empty($repairDays[1][0]['repair_days'])){
            $repairDayz['repair_days_b'] = $repairDays[1][0]['repair_days'];	
        }
        
        if(!empty($repairDays[2][0]['repair_days'])){
            $repairDayz['repair_days_c'] = $repairDays[2][0]['repair_days'];	
        }
					
		$maxRepairDays = max($repairDayz);
		
		$this->set(compact('brands','mobileModels','comments','repairLogs','users','kiosks','viewRepairParts','products','maxRepairDays','problemTypeOptions','mobileConditions','functionConditions'));		
	}
    
    public function edit($id = null) {
		$setting = $this->setting;
		$this->set(compact('setting'));
		//pr($_SESSION);die;
		
		$this->get_condition_problemtype_options();
		
		//capturing the mobile model id and brand ids from mobilerepairprice table with status 1 ie active
		$activeData = $this->get_active_brand_models();
		$activeBrands = $activeData['activeBrands'];
		$activeModels = $activeData['activeModels'];
		
		if (!$this->MobileRepairs->exists($id)) {
			throw new NotFoundException(__('Invalid mobile repair'));
		}
		
		//getting repair sale and repair data from database corresponding to this repair
		$repair_sale = $this->get_repair_and_sale($id);
		//pr($repair_sale);die;
		$dataRepairSale = $repair_sale['dataRepairSale'];
		$dataPerId = $repair_sale['dataPerId'];
		//checking if the repair belongs to the kiosk for customers screen
        //pr($dataPerId);die;
		if($this->request->session()->read('Auth.User.group_id') == KIOSK_USERS){
			if($dataPerId['kiosk_id'] != $this->request->Session()->read('kiosk_id')){
				$this->Flash->error("You can only edit the repair belonging to your kiosk!");
				return $this->redirect(array('controller' => 'retail_customers', 'action' => 'index'));
				die;
			}
		}
		
		//finding the brand names as per the fetched active brands
		if(empty($activeBrands)){
			$activeBrands = array(0=>null);
		}
		$brands_query = $this->MobileRepairs->Brands->find('list',[
                                                                'keyField' => 'id',
                                                                'valueField' => 'brand',
																'order'=>'brand asc',
                                                                'conditions' => ['Brands.status' => 1,'Brands.id IN' => $activeBrands]
                                                            ]
                                                    );
		$brands_query = $brands_query->hydrate(false);
        if(!empty($brands_query)){
            $brands = $brands_query->toArray();
        }else{
            $brands = array();
        }
		
		$kiosk_id = $this->request->Session()->read('kiosk_id');
		
		//getting kiosk list, user list, comments, repairlogs mobile and function conditions through the following function
		$miscData = $this->get_user_kiosk_comments_conditions($id);
		$users = $miscData['users'];
		$kiosks = $miscData['kiosks'];
		$comments = $miscData['comments'];
		$mobileConditions = $miscData['mobileConditions'];
		$function_conditions = $miscData['function_conditions'];
		$repairLogs = $miscData['repairLogs'];
		
		//for kiosk address in emails
		//pr($dataPerId);die;
		$kioskAddressArr = $this->kiosk_address($dataPerId['kiosk_id']);
		$kioskaddress1 = $kioskAddressArr['kioskaddress1'];
		$kioskaddress2 = $kioskAddressArr['kioskaddress2'];
		$kioskcity = $kioskAddressArr['kioskcity'];
		$kioskstate = $kioskAddressArr['kioskstate'];
		$kioskcountry = $kioskAddressArr['kioskcountry'];
		$kioskzip = $kioskAddressArr['kioskzip'];
		$kioskcontact = $kioskAddressArr['kioskcontact'];
		
		$countryOptions = Configure::read('uk_non_uk');
		$this->set(compact('countryOptions'));
		
		$brandId = $dataPerId['brand_id'];
		$mobileModelId = $dataPerId['mobile_model_id'];
		
		//repair dayz for showing in frontend and email
		$maxRepairDays = $this->max_repair_dayz($dataPerId['problem_type'], $brandId, $mobileModelId);
		
	//	pr($this->request);die;
		if ( $this->request->is(array('get'))
		    && isset($this->request->params  )
		    && array_key_exists(1,$this->request->params['pass'])
		    
			){
			//&&  (
			//	(
			//		array_key_exists('sort',$this->request->params['named']) ||
			//		array_key_exists('direction',$this->request->params['named'])
			//	) ||
			//	array_key_exists('page',$this->request->params['named'])
			
			
			//echo "hi";die;
			//This code block is for paging
			$status_rebooked = $dataPerId['status_rebooked'];
			$this->set(compact('status_rebooked'));
			$this->set('repair_id',$id);
			$this->get_product_categories();
			$this->render('product');	
		}elseif ($this->request->is(array('post', 'put')) || $this->request->Session()->read('payment_confirmation.payment_status') == $id) {
			//echo'hi';die;
			//pr($this->request);die;
			//echo'hi';die;
			//CASE: When payment is being updated from http://www.boloram.co.uk/mobile_repairs/update_repair_payment/4606
			if(array_key_exists('cancel',$this->request->data)){
				return $this->redirect(array('action' => 'edit', $id));
				die;
			}
			
			if(array_key_exists('UpdatePayment',$this->request->data)){
				//update repair payment (when payment is already done and we need to make change to it: http://www.boloram.co.uk/mobile_repairs/update_repair_payment/4606)
				$retern_res = $this->update_Repair_Payment($this->request->data, $id);
				return $retern_res;
			}
			//above case of final_parts_basket is when request is coming through payment page
			//we are verifying through payment_confirmation.payment_status that the payment has been done for $id
			
			//deleting session id payment_confirmation as we no longer need it after entering this loop
			$this->request->Session()->delete('payment_confirmation');
			
			//getting phone and function condition for email purpose
			$mobConditionArr = $this->mobile_condition_data($this->request->data, $dataPerId);
			$funcConditionStr = $mobConditionArr['funcConditionStr'];
			$phoneConditionStr = $mobConditionArr['phoneConditionStr'];
			
			//Case : when user is submitting any of edit form or product form
			if($this->request->session()->read('Auth.User.group_id') == REPAIR_TECHNICIANS){
				if(array_key_exists('no_parts',$this->request['data']) && $this->request['data']['no_parts'] == 1){
					//form validation
					$this->edit_form_validation($this->request['data']['MobileRepair'], $id);
				}
			}
			
			$repair_email_message = $this->setting['repair_email_message'];
			$comments_query = $this->MobileRepairs->CommentMobileRepairs->find('all', array(
									 'conditions' => array('CommentMobileRepairs.status' => 1,'CommentMobileRepairs.mobile_repair_id' => $id),
									 'contain' => array('Users'),
									 'order' => array('CommentMobileRepairs.id DESC'),
									 'limit' => 5
									));
            $comments_query = $comments_query->hydrate(false);
            if(!empty($comments_query)){
                $comments = $comments_query->toArray();
            }else{
                $comments = array();
            }
			
			$kiosks_query = $this->Kiosks->find('list',[
                                                    'keyField' => 'id',
                                                    'valueField' => 'name',
                                                    'conditions' => array('Kiosks.status' => 1)
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
			$this->set(compact('users','kiosks','comments','dataRepairSale'));
			//$mobile_condition = $mobile_condition_remark = $function_condition = '';
			
			$currentPage = 0;
			if(array_key_exists('current_page',$this->request['data'])){
				$currentPage = $this->request['data']['current_page'];
			}
			$session_parts_basket = $this->request->Session()->read('parts_basket');
			$problemVar = array();
			if(array_key_exists('MobileRepair',$this->request->data)){
				$problemVar = $this->request->data['MobileRepair'];
				//pr($problemVar);die;
			}
			$parts_basket = array();
			$imei = $dataPerId['imei'];
			
			//checking if the mobile belongs to kiosks or customer
			
			$mobilePurchaseData_query = $this->MobilePurchases->find('all',array('conditions' => array(
									'MobilePurchases.imei' => $imei,
									'MobilePurchases.status' => 4)
                                                        ));
            $mobilePurchaseData_query = $mobilePurchaseData_query->hydrate(false);
            if(!empty($mobilePurchaseData_query)){
                $mobilePurchaseData = $mobilePurchaseData_query->first();
            }else{
                $mobilePurchaseData = array();
            }
			//will not update the sale in case of rebooking
			//**UPDATING THE SALE IN CASE OF ADMIN OR MANAGER OR TECHNICIAN
			
			$fakeblock = 0;
			if($this->request->session()->read('Auth.User.group_id') == ADMINISTRATORS ||
			   $this->request->session()->read('Auth.User.group_id') == MANAGERS ||
			   $this->request->session()->read('Auth.User.group_id') == REPAIR_TECHNICIANS ||
			   $this->request->session()->read('Auth.User.group_id') == FRANCHISE_OWNER
			){
				
				//echo "UPDATING THE SALE IN CASE OF ADMIN OR MANAGER OR TECHNICIAN";
				$fakeblock = $this->update_repair_sale_admin($this->request->data, $dataPerId);
				///pr($fakeblock);die;
				if($fakeblock == 1){//we are returning fakeblock = 1 from update_repair_sale_admin to move user to payment page
					goto fakeblock;
				}
			}
			
			//---------------- saving changes made by service center technician to imei, description, problemtype, password
			if(!empty($problemVar) && $this->request->session()->read('Auth.User.group_id') == REPAIR_TECHNICIANS && $problemVar['status'] == DISPATCHED_2_KIOSK_REPAIRED){
				//echo "hi";die('--');
				$this->save_technician_changes($problemVar);
			}
			
			if(!empty($problemVar) && $this->request->session()->read('Auth.User.group_id') == KIOSK_USERS && $problemVar['status'] == DELIVERED_REPAIRED_BY_KIOSK){
				//echo'hi';die;
				//pr($problemVar);die;
				//echo "hi";die();
				//code for saving description, phone password while kiosk user delivers phone as repaired
				//when adding parts we come to this block
				//echo "code for saving description, phone password while kiosk user delivers phone as repaired";
				$technicianData2 = array(
											'id' => $problemVar['id'],
											'description' => $problemVar['description'],
											'phone_password' => $problemVar['phone_password'],
											'customer_contact' => $problemVar['customer_contact'],
											'imei' => $problemVar['imei']
										);
				//pr($technicianData2);die;
				$MobileRepairsEntity = $this->MobileRepairs->get($problemVar['id']);
				$MobileRepairsEntity = $this->MobileRepairs->patchEntity($MobileRepairsEntity,$technicianData2,['validate' => false]);
				//pr($MobileRepairsEntity);die;
				$this->MobileRepairs->save($MobileRepairsEntity);
				//$this->Session->delete('final_parts_basket'); //rasu
			}
			//pr($this->request);die;
			if( array_key_exists('submit_repair',$this->request['data']) ){
				
				//saving repair parts through this function and also adding mobile transfer logs, saving repair logs, adjusting inventory on adding the parts for the particular repair, sending entry to sale in case of kiosk repairing the phone and sending email to the customer
				$this->save_repair_parts($this->request['data'], $id, $dataPerId, $mobilePurchaseData, $activeModels, $kioskAddressArr, $mobConditionArr, $repair_email_message);
			}elseif( array_key_exists('empty_basket',$this->request['data']) ){
				//Case: Empty the basket
				$this->request->Session()->delete('parts_basket');
				$this->Flash->success('Basket is empty!');
				if($currentPage){
					return $this->redirect(array('action' => "edit/{$id}/page:$currentPage?page=$currentPage"));
				}else{
					$this->render('product');
				}
			}elseif(array_key_exists('add_2_basket',$this->request['data']) ){
				//Case: Adding new products to repair
				$flashMsg = $this->add_to_session_edit($id, $this->request['data']);
				$this->Flash->success($flashMsg,array('escape' => false));
				$this->get_product_categories();
				$this->set('repair_id',$id);
				//pr($this->request);
				//echo $currentPage;die;
				if($currentPage){
					return $this->redirect(array('action' => "edit/{$id}/page:$currentPage?page=$currentPage"));
				}else{
					$this->render('product');
				}
			}elseif(array_key_exists('status',$problemVar) && $problemVar['status'] == DISPATCHED_2_KIOSK_REPAIRED &&
				$this->request->session()->read('Auth.User.group_id') != ADMINISTRATORS &&
				$this->request->session()->read('Auth.User.group_id') != MANAGERS &&
				$this->request->session()->read('Auth.User.group_id') != FRANCHISE_OWNER &&
				!array_key_exists('hiddenStatus', $problemVar)){
				//go to screen and show him products that technician has used for repairing.
				//hiddenStatus comes only in case of checkbox unchecked by user, so we need to stop user from
				//redirecting to products page in this case
				//Screen: Manage Previous Repair Parts[For service center]
				$this->backstock($id);
				$this->get_product_categories();
				$this->render('product');
				//die;
			}elseif(array_key_exists('status',$problemVar) && $problemVar['status'] == DELIVERED_REPAIRED_BY_KIOSK &&
				$this->request->session()->read('Auth.User.group_id') != ADMINISTRATORS &&
				$this->request->session()->read('Auth.User.group_id') != MANAGERS &&
				$this->request->session()->read('Auth.User.group_id') != REPAIR_TECHNICIANS &&
				$this->request->session()->read('Auth.User.group_id') != FRANCHISE_OWNER &&
				!array_key_exists('hiddenStatus', $problemVar)){
					$this->backstock($id);
					$this->get_product_categories();
					$this->render('product');
			}else{
				//echo'hi';die;
				//pr($this->request);die;
				//echo "127";die;
                //pr($this->request);die;
				$paymentMade = 0;
				//pr($dataPerId);die;
				//pr($this->request);die;
				//pr($_SESSION);die;
				//pr($this->request->Session()->read('received_reprd_from_tech_data'));die;
				if(is_array($this->request->Session()->read('final_parts_basket'))){
					
					//**CASE: when request is coming through the payment page
					//we are not saving the data of request coming through payment in mobile repair,
					//but repair sale is getting saved from the below code
					$reprData_query = $this->MobileRepairs->find('all',array(
						'conditions' => array('MobileRepairs.id'=>$id)
						)
					);
                    $reprData_query = $reprData_query->hydrate(false);
                    if(!empty($reprData_query)){
                        $reprData = $reprData_query->first();
                    }else{
                        $reprData = array();
                    }
					
					$problemVar = $reprData;
					$this->request->data['MobileRepair'] = $reprData;//for below code
					$repairBookingData = $reprData;//for sending email below
					$this->request->data['MobileRepair']['total_cost'] = $this->request->Session()->read('final_parts_basket.total_cost');//this has been added for sending entry in mobile repair sale below
					
					$partsSaved = 0;
					//***saving repair parts in mobile_repair_parts table
					foreach($this->request->Session()->read('final_parts_basket') as $key => $productID){
						if($key == 'repair_id' || $key == 'total_cost')continue;
						//$kskId = $dataPerId['MobileRepair']['kiosk_id'];
						$mobileRepairPartData = array(
										'user_id' => $this->request->Session()->read('Auth.User.id'), //added on Nov 22, 2016
										'mobile_repair_id' => $id,
										'product_id' => $productID,
										'kiosk_id' => $kiosk_id
									      );
						//pr($mobileRepairPartData);die;
                        
                        
						$part_entity = $this->MobileRepairParts->newEntity();
                        $part_entity = $this->MobileRepairParts->patchEntity($part_entity,$mobileRepairPartData,['validate' => false]);
						if($this->MobileRepairParts->save($part_entity)){
							$partsSaved++;
							$savedPartsArr[$productID] = $productID;
						}
						
						//on save decrease inventory for each item.
						if(empty($kiosk_id)){$productSource = "products";}else{$productSource = "kiosk_{$kiosk_id}_products";}
						if($partsSaved){
							//$this->Product->setSource($productSource);
							//$this->Product->clear();
							//$this->Product->id = $productID;
							$quantity = 1;
                        
                            $update_query = "UPDATE `$productSource` SET `quantity` = `quantity` - $quantity WHERE `$productSource`.`id` = $productID";
                            $conn = ConnectionManager::get('default');
							$stmt = $conn->execute($update_query); 
						}
					}
					//$this->Session->delete('final_parts_basket');//rasu - inder
					//till here*** repair parts
					
					//if request is coming from payment page, means that the payment has just been made, sending identifier in a variable
					$paymentMade = 1;
					
				}elseif(is_array($this->request->Session()->read('received_reprd_from_tech_data'))){
					
					//**CASE: request coming through payment page for the mobile repaired by technician
					//below assigning the $this->request->data from session that was stored from below loop
					$this->request->data = $this->request->Session()->read('received_reprd_from_tech_data');
					$problemVar = $this->request->Session()->read('received_reprd_from_tech_data.MobileRepair');
					//if request is coming from payment page, means that the payment has just been made, sending identifier in a variable
					$paymentMade = 1;
				}elseif(!is_array($this->request->Session()->read('received_reprd_from_tech_data')) && $this->request->data['MobileRepair']['status'] == DELIVERED_REPAIRED_BY_TECHNICIAN && $this->request->session()->read('Auth.User.group_id') == KIOSK_USERS && ($dataPerId['internal_repair'] !=1 || $dataPerId['internal_repair'] == NULL || empty($dataPerId['internal_repair'])) && !array_key_exists('hiddenStatus', $problemVar)){
					//pr($dataPerId);die;
					//checking if atleast 1 entry in payment table
					$exempted = $this->checkIfExempted($id, $dataPerId['status_rebooked']);
				 
					//redirecting to the payment page from here, using the session final_parts_basket from below
					//exempting the entries of rebook for which payment was never taken
					//hidden status for checking if user has unchecked status from frontend
					//**CASE: kiosk delivering repair done by technician after receiving
					//redirecting to the payment page
					if($exempted == 0){
                     
						$this->request->Session()->write('received_reprd_from_tech_data',$this->request->data);
						return $this->redirect(array('controller'=>'MobileRepair','action' => 'repair_payment'));
						die;
					}
				}				
				
				if(is_array($this->request->Session()->read('received_reprd_from_tech_data'))){
					//deleting the session created before payment
					$this->request->Session()->delete('received_reprd_from_tech_data');
				}
				
				$problemData = array();
				$problemData1 = array();
				//Case : Normal submit other than for case :DISPATCHED_2_KIOSK_REPAIRED
				if(!empty($kiosk_id) && $this->request->session()->read('Auth.User.group_id') == KIOSK_USERS){
					$redirectAction = 'all';
				}else{
					$redirectAction = 'dispatched';
					//pr($problemVar);
				}
				if(array_key_exists('mobile_condition',$this->request->data['MobileRepair']) && is_array($this->request->data['MobileRepair']['mobile_condition'])){
					foreach($this->request->data['MobileRepair']['mobile_condition'] as $mc => $mobCond){
						
						if($mobCond == 1000 && empty($this->request->data['MobileRepair']['mobile_condition_remark'])){
							//code for showing variables on front end
							$this->validate_mobile_condition($this->request->data['MobileRepair'], $mobilePurchaseDetails, $brands, $activeModels);
							$this->Flash->error('Please input mobile condition remarks!');
							return;
						}
					}
					
					//for removing values with 0
					$mbleCondtion = array_filter($this->request->data['MobileRepair']['mobile_condition']);
					
					$mobile_condition = implode("|",$mbleCondtion);
					$mobile_condition_remark = $this->request->data['MobileRepair']['mobile_condition_remark'];
					//sending the above 2 fields for saving only if they are not empty in a new array, will add it to $problemData
					$problemData1['mobile_condition'] = $mobile_condition;
					$problemData1['mobile_condition_remark'] = $mobile_condition_remark;
				}else{
					//if key mobile_condition does not exist in the array means the checkboxes have been unchecked so sending them blank in database table
					$problemData1['mobile_condition'] = '';
					$problemData1['mobile_condition_remark'] = '';
				}
				
				$function_condition = array();
				if(array_key_exists('function_condition',$this->request->data['MobileRepair']) &&
				is_array($this->request->data['MobileRepair']['function_condition'])){
					$fnctnCondition = array_filter($this->request->data['MobileRepair']['function_condition']);
					$function_condition = implode("|",$fnctnCondition);
					$problemData1['function_condition'] = $function_condition;
				}else{
					$problemData1['function_condition'] = '';
				}
				
				if(array_key_exists('hiddenStatus', $problemVar)){
					if($problemVar['hiddenStatus'] == DELIVERED_REPAIRED_BY_KIOSK || $problemVar['hiddenStatus'] == DELIVERED_REPAIRED_BY_TECHNICIAN){
						$problemData1 = array();
					}
				}
				
				if(array_key_exists('estimated_cost_a',$problemVar) && is_numeric($problemVar['estimated_cost_a'])){
					//pr($problemVar);die;
					$problemType[] = $problemVar['problem_type_a'];
					$estimatedCost[] = $problemVar['estimated_cost_a'];
				}
				
				if(array_key_exists('estimated_cost_b',$problemVar) && is_numeric($problemVar['estimated_cost_b'])){
					$problemType[] = $problemVar['problem_type_b'];
					$estimatedCost[] = $problemVar['estimated_cost_b'];
				}
				
				if(array_key_exists('estimated_cost_c',$problemVar) && is_numeric($problemVar['estimated_cost_c'])){
					$problemType[] = $problemVar['problem_type_c'];
					$estimatedCost[] = $problemVar['estimated_cost_c'];
				}						
						
				//print_r($problemVar);die;
				//pr($problemVar);die;
				if($problemVar['status'] == DELIVERED_REPAIRED_BY_KIOSK ||
				   $problemVar['status'] == DELIVERED_UNREPAIRED_BY_KIOSK ||
				   $problemVar['status'] == DELIVERED_REPAIRED_BY_TECHNICIAN ||
				   $problemVar['status'] == DELIVERED_UNREPAIRED_BY_TECHNICIAN){
					$date = date('Y-m-d h:i:s A');	
				}else{
					$date = date('0-0-0 0:0:0 A');	
				}
				
				$statusDelivered = 0;
				if(is_array($this->request->Session()->read('final_parts_basket'))){
					//echo "--rasu--";
					//echo $id;
                    $data_to_save = array("status"=>DELIVERED_REPAIRED_BY_KIOSK);
					//pr($data_to_save);die;
					$repairs_entity = $this->MobileRepairs->get($id);
                    $repairs_entity =$this->MobileRepairs->patchEntity($repairs_entity,$data_to_save,['validate' => false]);
					//pr($repairs_entity);
					if($this->MobileRepairs->save($repairs_entity,['validate' => false])){
						//echo'111111111111111111111111111111111111111';die;
						$statusDelivered = 1;
						$problemVar['status'] = DELIVERED_REPAIRED_BY_KIOSK;
					}
				}else{
					$netCosta = (array_key_exists('net_cost_a', $problemVar)) ? floatval($problemVar['net_cost_a']) : 0;
					$netCostb = (array_key_exists('net_cost_b', $problemVar)) ? floatval($problemVar['net_cost_b']) : 0;
					$netCostc = (array_key_exists('net_cost_c', $problemVar)) ? floatval($problemVar['net_cost_c']) : 0;
						//pr($problemVar);die;				
					$net_cost = $netCosta + $netCostb + $netCostc;
					$problemData = array(
						   'id' => $problemVar['id'],
						   'repair_number' => $problemVar['repair_number'],
						   'customer_fname' => $problemVar['customer_fname'],
						   'customer_lname' => $problemVar['customer_lname'],
						   'customer_email' => $problemVar['customer_email'],
						   'customer_contact' => $problemVar['customer_contact'],
						   'zip' => $problemVar['zip'],
						   'customer_address_1' => $problemVar['customer_address_1'],
						   'customer_address_2' => $problemVar['customer_address_2'],
						   'city' => $problemVar['city'],
						   'state' => $problemVar['state'],
						   'country' => $problemVar['country'],
						   'brand_id' => $problemVar['brand_id'],
						   'mobile_model_id' => $problemVar['mobile_model_id'],
						   'problem_type' => implode('|',$problemType),
						   'estimated_cost' => implode('|',$estimatedCost),
						   //'status_freezed' => $problemVar['status_freezed'],
						   //'net_cost' => $net_cost,
						   'imei' => $problemVar['imei'],
						   'description' => $problemVar['description'],
						   'brief_history' => $problemVar['brief_history'],
						   'actual_cost' => $problemVar['actual_cost'],
						   'received_at' => $problemVar['received_at'],
						   'delivered_at' => $date,
						   'phone_password' => $problemVar['phone_password'],
						   'status' => $problemVar['status']
						      );
					if($net_cost > 0){
						$problemData['net_cost'] = $net_cost;
					}
					if(array_key_exists('status_freezed',$problemVar)){
						$problemData['status_freezed'] = $problemVar['status_freezed'];
					}
					//adding $problemData1 (that contains mobile_condition, remarks and function_condition) to the above array		
					$problemData+= $problemData1;
				}
				//pr($problemVar);die;
				//$this->MobileRepair->create();
				$warehouseKioskId = Configure::read('WAREHOUSE_KIOSK_ID');
				if($problemVar['status'] == REBOOKED){
                    
					$payment_res_query = $this->RepairPayments->find('all',array('conditions' => array('mobile_repair_id' => $problemVar['id'])));
					$payment_res_query = $payment_res_query->hydrate(false);
					if(!empty($payment_res_query)){
						$payment_res = $payment_res_query->toArray(false);
					}else{
						$payment_res = array();
					}
                   /// pr($payment_res);die;
					if(empty($payment_res)){
						$redirect = array('controller'=>'mobile_repairs','action'=>'index');
						$flashMessage = "Cannot be rebooked. Already delivered un-repaired. Please book it fresh !!";
						$this->Flash->error($flashMessage);
						return $this->redirect($redirect);die;
					}
				}
				//$problemData = $this->checkIfPricesChanged($problemData, $this->request['data']['MobileRepair']);
				$MobileRepairsEntity = $this->MobileRepairs->get($problemVar['id']);
				$MobileRepairsEntity = $this->MobileRepairs->patchEntity($MobileRepairsEntity,$problemData,['validate' => false]);
				if($this->MobileRepairs->save($MobileRepairsEntity,['validate' => false]) || $statusDelivered == 1){
					if($kiosk_id==""){$kiosk_id = $warehouseKioskId;}
					$repair_sale = $this->get_repair_and_sale($id);
					//getting the details again, after saving
					$dataRepairSale = $repair_sale['dataRepairSale'];
					$dataPerId = $repair_sale['dataPerId'];
					if(!empty($mobilePurchaseData) &&
						($problemVar['status'] == DELIVERED_REPAIRED_BY_KIOSK ||
						$problemVar['status'] == DELIVERED_UNREPAIRED_BY_KIOSK ||
						$problemVar['status'] == DELIVERED_REPAIRED_BY_TECHNICIAN ||
						$problemVar['status'] == DELIVERED_UNREPAIRED_BY_TECHNICIAN)){
						$purchaseId = $mobilePurchaseData['id'];
						$mobileTransferLogData = array(
								'mobile_purchase_reference' => $mobilePurchaseData['mobile_purchase_reference'],
								'mobile_purchase_id' => $mobilePurchaseData['id'],
								'kiosk_id' => $kiosk_id,
								'network_id' => $mobilePurchaseData['network_id'],
								'grade' => $mobilePurchaseData['grade'],
								'type' => $mobilePurchaseData['type'],
								'receiving_status' => 0,
								'imei' => $mobilePurchaseData['imei'],
								'user_id' => $this->request->Session()->read('Auth.User.id'),
								'status' => 0
							);
							
							$MobileTransferLogsEntity = $this->MobileTransferLogs->newEntity($mobileTransferLogData,['validation' => false]);
							$MobileTransferLogsEntity = $this->MobileTransferLogs->patchEntity($MobileTransferLogsEntity,$mobileTransferLogData,['validate' => false]);
							$this->MobileTransferLogs->save($MobileTransferLogsEntity);
							
							$MobilePurchasesEntity = $this->MobilePurchases->get($purchaseId);
							$data = array('status' => 0);
							$MobilePurchasesEntity = $this->MobilePurchases->patchEntity($MobilePurchasesEntity,$data,['validate' =>false]);
							
							$this->MobilePurchases->save($MobilePurchasesEntity,['validate' => false]);//changing the status to available
					}
						
					if($problemVar['status'] == REBOOKED){
                      //  echo "<br>";echo "hikljl";die;
						$payment_res_query = $this->RepairPayments->find('all',array('conditions' => array('mobile_repair_id' => $problemVar['id'])));
						$payment_res_query = $payment_res_query->hydrate(false);
						if(!empty($payment_res_query)){
							$payment_res = $payment_res_query->toArray();
						}else{
							$payment_res = array();
						}
						if(empty($payment_res)){
							$redirect = array('controller'=>'mobile_repairs','action'=>'index');
							$flashMessage = "Cannot Rebook previously Unrepaired";
							$this->Flash->error($flashMessage);
							return $this->redirect($redirect);
							die;
						}
						$MobileRepairsEntity = $this->MobileRepairs->get($problemVar['id']);
						$data = array('status_rebooked' => 1);
						$MobileRepairsEntity = $this->MobileRepairs->patchEntity($MobileRepairsEntity,$data,['validate' => false]);
						$this->MobileRepairs->save($MobileRepairsEntity);	
					}
					
					$mobileRepairLogsData = array(
						'kiosk_id' => $dataPerId['kiosk_id'],
						'user_id' => $this->request->Session()->read('Auth.User.id'),
						'mobile_repair_id' => $MobileRepairsEntity->id,					
						'repair_status' => $problemVar['status']
						);
					
					if($problemVar['status'] == DISPATCHED_2_KIOSK_UNREPAIRED){
						$service_center_id = $this->request->Session()->read('kiosk_id');
						$mobileRepairLogsData = array(
							'kiosk_id' => $dataPerId['kiosk_id'],
							'user_id' => $this->request->Session()->read('Auth.User.id'),
							'mobile_repair_id' => $MobileRepairsEntity->id,					
							'repair_status' => $problemVar['status'],
							'service_center_id' => $service_center_id
						);
					}
					//pr($this->request);die;
					$totalCost = $this->request->data['MobileRepair']['total_cost'];
					$MobileRepairLogsEntity = $this->MobileRepairLogs->newEntity($mobileRepairLogsData,['validate' => false]);
					$MobileRepairLogsEntity = $this->MobileRepairLogs->patchEntity($MobileRepairLogsEntity,$mobileRepairLogsData,['validate' => false]);
					$this->MobileRepairLogs->save($MobileRepairLogsEntity,['validate' => false]);
				
				//saving data in mobile repair sales table
					$rebookCheckData_query = $this->MobileRepairLogs->find('all',array('conditions'=> array('MobileRepairLogs.repair_status IN' => array(BOOKED,REBOOKED),
											      'MobileRepairLogs.mobile_repair_id' => $id
											      ),
										'order' => array('MobileRepairLogs.id DESC'),
										'limit' => 1
							     )
							    );
					$rebookCheckData_query = $rebookCheckData_query->hydrate(false);
					if(!empty($rebookCheckData_query)){
						$rebookCheckData = $rebookCheckData_query->toArray();
					}else{
						$rebookCheckData = array();
					}
					
					
					$rebookRepairStatus = $dataPerId['status_rebooked'];
					if($problemVar['status'] == DELIVERED_UNREPAIRED_BY_KIOSK ||
					   $problemVar['status'] == DELIVERED_UNREPAIRED_BY_TECHNICIAN ||
					   $dataPerId['internal_repair'] == 1){
						//xyz case for all
						$amount = 0;
					}elseif(!empty($rebookRepairStatus)){
						if($paymentMade == 1){
							$amount = $totalCost;
						}else{
							$amount = 0;
						}
					}else{
						$amount = $totalCost;
					}
					//die;
				
					$dateRepaired = date('Y-m-d h:i:s A');
					if($problemVar['status'] == DELIVERED_REPAIRED_BY_KIOSK ||
					   $problemVar['status'] == DELIVERED_REPAIRED_BY_TECHNICIAN ||
					   $problemVar['status'] == DELIVERED_UNREPAIRED_BY_KIOSK ||
					   $problemVar['status'] == DELIVERED_UNREPAIRED_BY_TECHNICIAN){
						//echo'hi';die;
						
							$mobileRepairSalesData = array(
										'kiosk_id' => $dataPerId['kiosk_id'],
										'retail_customer_id' => $dataPerId['retail_customer_id'],
										'mobile_repair_id' => $MobileRepairsEntity->id,
										'sold_by' => $this->request->Session()->read('Auth.User.id'),
										'sold_on' => $dateRepaired,
										'refund_by' => '',
										'amount' => $amount,
										'refund_amount' => '',
										'refund_status' => 0,
										'refund_on' => '',
										'refund_remarks' => '',
										'repair_status' => $problemVar['status']
										       );
							//pr($mobileRepairSalesData);die;
							if(!empty($dataPerId)){
								if(array_key_exists('status_rebooked',$dataPerId)){
									$mobileRepairSalesData['rebooked_status'] = $dataPerId['status_rebooked'];
								}
							}
						//pr($mobileRepairSalesData);die;
						//code for bypassing save in case of admin edit
						if($this->request->session()->read('Auth.User.group_id') == KIOSK_USERS){
							if(!array_key_exists('hiddenStatus', $problemVar)){
								//echo'hi';
								//Case: when user unchecks change status from edit page repair is delivered
								$MobileRepairSaleEntity = $this->MobileRepairSales->newEntity($mobileRepairSalesData,['validate' => false]);
								//--rasu-
								
								$checkReprPmtExist_query = $this->RepairPayments->find('all', array(
									'conditions' => array('RepairPayments.mobile_repair_id' => $id),
									'fields' => array('id')
																	));
								$checkReprPmtExist_query = $checkReprPmtExist_query->hydrate(false);
								if(!empty($checkReprPmtExist_query)){
									$checkReprPmtExist = $checkReprPmtExist_query->toArray();
								}else{
									$checkReprPmtExist = array();
								}
								
								//pr($checkReprPmtExist);die("-----");
								if(empty($checkReprPmtExist)){
									//echo'aaaa';die;
									if($problemVar['status'] == 8){
											if(!empty($dataPerId)){
												$internal_repair = 0;
												if(!empty($dataPerId)){
													$internal_repair = $dataPerId['internal_repair'];
													if($internal_repair == 1){
														// code for payment table not needed in internal case 
								$MobileRepairSaleEntity = $this->MobileRepairSales->patchEntity($MobileRepairSaleEntity,$mobileRepairSalesData,['validate' => false]);
								//pr($MobileRepairSaleEntity);
														if($this->MobileRepairSales->save($MobileRepairSaleEntity,['validate' => false])){
															echo'hi';die;
															$redirect = array('controller'=>'mobile_repairs','action'=>'index');
															$flashMessage = "payment done";
															$this->Flash->success($flashMessage);
															return $this->redirect($redirect);die;
														}
													}
												}
											}
									}
									//redirect to edit page
									$redirect = array('controller'=>'mobile_repairs','action'=>'index');
									$repairBookingData = $this->request['data']['MobileRepair'];
									$repairBookingData['status'] = DELIVERED_UNREPAIRED_BY_KIOSK;
									$repairStatus = $repairBookingData['status'];
									$mobileModels_query = $this->MobileRepairs->MobileModels->find('list',array(
																'fields' => array('id', 'model'),
																'order'=>'model asc',
																'conditions' => array(
																		  'MobileModels.status' => 1,
																		  'MobileModels.id IN' => $activeModels)
																)
														   );
									$mobileModels_query = $mobileModels_query->hydrate(false);
									if(!empty($mobileModels_query)){
										$mobileModels = $mobileModels_query->toArray();
									}else{
										$mobileModels = array();
									}
										//code for getting statement for email
										//pr($repairBookingData);die;
										$statementArray = $this->get_email_statement($repairStatus, $repairBookingData, $mobileModels, $maxRepairDays, $phoneConditionStr, $funcConditionStr, $kiosks, $kioskAddressArr, $repair_email_message, $dataPerId);
										$statement = $statementArray['statement'];
										$messageStatement = $statementArray['messageStatement'];
										$send_by_email = Configure::read('send_by_email');
										$emailSender = Configure::read('EMAIL_SENDER');
										if(!empty($statement)){
											if($this->request->session()->read('Auth.User.group_id') == MANAGERS || $this->request->session()->read('Auth.User.group_id') == ADMINISTRATORS){
												if($this->request['data']['MobileRepair']['send'] == '1'){
													if(!empty($dataPerId['MobileRepair']['customer_contact'])){
														$destination = $dataPerId['MobileRepair']['customer_contact'];
														if(!empty($messageStatement)){
															$this->TextMessage->test_text_message($destination, $messageStatement);
														}
													}
													if(!empty($emailTo)){
														$Email = new Email();
														$Email->config('default');
														$Email->viewVars(array('statement' => $statement));
														//$Email->config(array('from' => 'me@example.org', 'transport' => 'MyCustom'));
														//$Email = new CakeEmail(array('from' => 'me@example.org', 'transport' => 'MyCustom'));
														$emailTo = $repairBookingData['customer_email'];
														$Email->template('repair_booking_receipt');
														$Email->emailFormat('both');
														$Email->to($emailTo);
														 $Email->transport(TRANSPORT);
														$Email->from([$send_by_email => $emailSender]);
											//			$Email->sender("sales@oceanstead.co.uk");
														$Email->subject('Mobile Repair Details');
														$Email->send();
													}
													
												}
											}else{
												if(!array_key_exists('hiddenStatus', $problemVar)){
													if(!empty($dataPerId['MobileRepair']['customer_contact'])){
														$destination = $dataPerId['MobileRepair']['customer_contact'];
														if(!empty($messageStatement)){
															$this->TextMessage->test_text_message($destination, $messageStatement);
														}
													}
													if(!empty($emailTo)){
														$Email = new Email();
														$Email->config('default');
														$Email->viewVars(array('statement' => $statement));
														//$Email->config(array('from' => 'me@example.org', 'transport' => 'MyCustom'));
														//$Email = new CakeEmail(array('from' => 'me@example.org', 'transport' => 'MyCustom'));
														$emailTo = $repairBookingData['customer_email'];
														$Email->template('repair_booking_receipt');
														$Email->emailFormat('both');
														$Email->to($emailTo);
														 $Email->transport(TRANSPORT);
														$Email->from([$send_by_email => $emailSender]);
														//$Email->sender("sales@oceanstead.co.uk");
														$Email->subject('Mobile Repair Details');
														$send_mail = 0;
														if($send_mail == 1){
															$Email->send();	
														}
													}
													
													
												}
											}
										}
									
									$flashMessage = "Delivered un-repaired. Sorry, we could not repair it.";
									$this->Flash->error($flashMessage);
									return $this->redirect($redirect);
								}else{
									//echo'bbbb';die;
									//process sale and payments
									$MobileRepairSalesEntity = $this->MobileRepairSales->newEntity($mobileRepairSalesData,['validate' => false]);
									$MobileRepairSalesEntity = $this->MobileRepairSales->patchEntity($MobileRepairSalesEntity,$mobileRepairSalesData,['validate' => false]);
									if($this->MobileRepairSales->save($MobileRepairSalesEntity,['validate' => false]) && is_array($this->request->Session()->read('final_parts_basket'))){
										//pr($mobileRepairSalesData);die;
										$rprSaleId = $MobileRepairSalesEntity->id;
										$result_query = $this->RepairPayments->find("all",array('conditions' => array('RepairPayments.mobile_repair_id' => $id)
																			   )
																   );
										$result_query = $result_query->hydrate(false);
										if(!empty($result_query)){
											$result = $result_query->toArray();
										}else{
											$result = array();
										}
										
										$conn = ConnectionManager::get('default');
										$query = "UPDATE  `repair_payments`  SET  `mobile_repair_sale_id` = $rprSaleId WHERE `repair_payments`.`mobile_repair_id` = $id";
										$stmt = $conn->execute($query); 
									}
								}
							}
							//echo 'hlo';die;
							if(!empty($rebookRepairStatus) && ($problemVar['status'] == DELIVERED_UNREPAIRED_BY_KIOSK || $problemVar['status'] == DELIVERED_UNREPAIRED_BY_TECHNICIAN)){
								if($rebookRepairStatus == 1){
									//case rebooked repair is getting delivered unrepaired
									//here we are auto refunding the customer if sale was generated
									if($this->request->session()->read('Auth.User.group_id') == ADMINISTRATORS && $this->request->session()->read('Auth.User.group_id') == MANAGERS && $this->request->session()->read('Auth.User.group_id') == FRANCHISE_OWNER ){
										$this->refund_rebooked_unrepaired($id);
									}
									
								}
							}
						}
					}
					if(empty($activeModels)){
						$activeModels  = array(0=>null);
					}
					$mobileModels_query = $this->MobileRepairs->MobileModels->find('list',array(
											    'fields' => array('id', 'model'),
												'order'=>'model asc',
											    'conditions' => array(
														  'MobileModels.status' => 1,
														  'MobileModels.id IN' => $activeModels)
											    )
									       );
					$mobileModels_query = $mobileModels_query->hydrate(false);
					if(!empty($mobileModels_query)){
						$mobileModels = $mobileModels_query->toArray();
					}else{
						$mobileModels = array();
					}
					//pr($this->Session->read('final_parts_basket'));
					if(is_array($this->request->Session()->read('final_parts_basket'))){
						$repairBookingData['status'] = DELIVERED_REPAIRED_BY_KIOSK;
						//deleting the session that got created through payment screen
						$this->request->Session()->delete('final_parts_basket');
						$this->request->Session()->delete('received_reprd_from_tech_data');
						$this->request->Session()->delete('view_parts_basket');
						$this->request->Session()->delete('parts_basket');
					}else{
						$repairBookingData = $this->request['data']['MobileRepair'];
					}
					
					
					$repairStatus = $repairBookingData['status'];
					//code for getting statement for email
                    //pr($repairBookingData);die;
					$statementArray = $this->get_email_statement($repairStatus, $repairBookingData, $mobileModels, $maxRepairDays, $phoneConditionStr, $funcConditionStr, $kiosks, $kioskAddressArr, $repair_email_message, $dataPerId);
					$statement = $statementArray['statement'];
					$messageStatement = $statementArray['messageStatement'];
					$send_by_email = Configure::read('send_by_email');
					$emailSender = Configure::read('EMAIL_SENDER');
					if(!empty($statement)){
						if($this->request->session()->read('Auth.User.group_id') == MANAGERS || $this->request->session()->read('Auth.User.group_id') == ADMINISTRATORS || $this->request->session()->read('Auth.User.group_id') == FRANCHISE_OWNER ){
							if($this->request['data']['MobileRepair']['send'] == '1'){
								if(!empty($dataPerId['MobileRepair']['customer_contact'])){
									$destination = $dataPerId['MobileRepair']['customer_contact'];
									if(!empty($messageStatement)){
										$this->TextMessage->test_text_message($destination, $messageStatement);
									}
								}
								if(!empty($emailTo)){
									$Email = new Email();
									$Email->config('default');
									$Email->viewVars(array('statement' => $statement));
									//$Email->config(array('from' => 'me@example.org', 'transport' => 'MyCustom'));
									//$Email = new CakeEmail(array('from' => 'me@example.org', 'transport' => 'MyCustom'));
									$emailTo = $repairBookingData['customer_email'];
									$Email->template('repair_booking_receipt');
									$Email->emailFormat('both');
									$Email->to($emailTo);
									 $Email->transport(TRANSPORT);
									$Email->from([$send_by_email => $emailSender]);
						//			$Email->sender("sales@oceanstead.co.uk");
									$Email->subject('Mobile Repair Details');
									$Email->send();
								}
							}
						}else{
							if(!array_key_exists('hiddenStatus', $problemVar)){
								if(!empty($dataPerId['MobileRepair']['customer_contact'])){
									$destination = $dataPerId['MobileRepair']['customer_contact'];
									if(!empty($messageStatement)){
										$this->TextMessage->test_text_message($destination, $messageStatement);
									}
								}
								if(!empty($emailTo)){
									$Email = new Email();
									$Email->config('default');
									$Email->viewVars(array('statement' => $statement));
									//$Email->config(array('from' => 'me@example.org', 'transport' => 'MyCustom'));
									//$Email = new CakeEmail(array('from' => 'me@example.org', 'transport' => 'MyCustom'));
									$emailTo = $repairBookingData['customer_email'];
									$Email->template('repair_booking_receipt');
									$Email->emailFormat('both');
									$Email->to($emailTo);
									 $Email->transport(TRANSPORT);
									$Email->from([$send_by_email => $emailSender]);
									//$Email->sender("sales@oceanstead.co.uk");
									$Email->subject('Mobile Repair Details');
									$send_mail = 0;
									if($send_mail == 1){
										$Email->send();
									}
								}
							}
						}
					}
					$statusArr = $this->status_repair();
					if($problemVar['status'] == BOOKED){
						$statusArr[$problemVar['status']] = "Record has been updated";
					}
					if(array_key_exists($problemVar['status'],$statusArr)){
						$msg = $statusArr[$problemVar['status']];
					}else{
						$msg = "";
					}
					
					$this->Flash->success(__($msg));
					if($this->request->session()->read('Auth.User.group_id') == KIOSK_USERS ||
					   $this->request->session()->read('Auth.User.group_id') == ADMINISTRATORS ||
					   $this->request->session()->read('Auth.User.group_id') == MANAGERS  ||
					   $this->request->session()->read('Auth.User.group_id') == FRANCHISE_OWNER){
						if($this->request->session()->read('Auth.User.group_id') == KIOSK_USERS){
							if($problemVar['status'] == REBOOKED){
								$print_type = $this->setting['print_type'];
								if($print_type == 1){
									return $this->redirect(array('controller' => 'prints','action' => 'repair',$id));	
								}else{
									return $this->redirect(array('action' => 'all'));		
								}
							}else{
								return $this->redirect(array('action' => 'all'));		
							}	
						}else{
							return $this->redirect(array('action' => 'all'));		
						}
					}elseif($this->request->session()->read('Auth.User.group_id') == REPAIR_TECHNICIANS){
						return $this->redirect(array('action' => 'dispatched'));
					}
				} else {
					$this->Flash->error(__('The mobile repair could not be saved. Please, try again.'));
				}
				
			}
		} else {
			$options = array('conditions' => array('id' => $id));
            $MobileRepair_query = $this->MobileRepairs->find('all', $options);
            $MobileRepair_query = $MobileRepair_query->hydrate(false);
            if(!empty($MobileRepair_query)){
                $MobileRepair = $MobileRepair_query->first();
            }else{
                $MobileRepair = array();
            }
			$this->request->data = $MobileRepair;
		}
		
		if(empty($activeModels)){
			$activeModels = array(0=>null);
		}
		$mobileModels_query = $this->MobileRepairs->MobileModels->find('list',[
                                                                            'keyField' => 'id',
                                                                            'valueField' => 'model',
																			'order'=>'model asc',
                                                                            'conditions' => [
                                                                                        'MobileModels.status' => 1,
                                                                                        'MobileModels.brand_id' => $brandId,
                                                                                        'MobileModels.id IN' => $activeModels
                                                                                            ]
                                                                        ]
                                                                );
        $mobileModels_query = $mobileModels_query->hydrate(false);
        if(!empty($mobileModels_query)){
            $mobileModels = $mobileModels_query->toArray();
        }else{
            $mobileModels = array();
        }
		
		$problemTypeData_query = $this->MobileRepairPrices->find('all',array('conditions' => array(
											'MobileRepairPrices.status'=>1,
											'MobileRepairPrices.brand_id' => $brandId,
											'MobileRepairPrices.mobile_model_id' => $mobileModelId,
											'MobileRepairPrices.repair_price > 0'
												       ),
							      'fields' => array('problem_type','repair_price')
							      ));
        $problemTypeData_query = $problemTypeData_query->hydrate(false);
        if(!empty($problemTypeData_query)){
            $problemTypeData = $problemTypeData_query->toArray();
        }else{
            $problemTypeData = array();
        }
		$problemArrOptns = array();
		//Configure::load('common-arrays');
		$problemTypeOptions_query = $this->ProblemTypes->find('list',[
                                                                    'keyField' => 'id',
                                                                    'valueField' => 'problem_type'
																	]
															 );
        $problemTypeOptions_query = $problemTypeOptions_query->hydrate(false);
        if(!empty($problemTypeOptions_query)){
            $problemTypeOptions = $problemTypeOptions_query->toArray();
        }else{
            $problemTypeOptions = array();
        }
        //pr($problemTypeOptions);die;
		foreach($problemTypeData as $key => $problemTpe){
            //pr($problemTpe);die;
			$problemArrOptns[$problemTpe['problem_type']] = $problemTypeOptions[$problemTpe['problem_type']];
			$costArr[$problemTpe['problem_type']] = $problemTpe['repair_price'];
		}
		
		$this->set(compact('kiosks', 'brands', 'comments','mobileModels','repairLogs','users','dataRepairSale','maxRepairDays','problemArrOptns','costArr'));
		fakeblock:
		;
	}
    
    private function get_condition_problemtype_options(){//being used in edit()
		$mobileConditions_query = $this->MobileConditions->find('list',[
                                                                    'conditions' => ['MobileConditions.status' => 1],
                                                                    'keyField' => 'id',
                                                                    'valueField' => 'mobile_condition',
                                                                 ]
                                                        );
		$mobileConditions_query = $mobileConditions_query->hydrate(false);
        if(!empty($mobileConditions_query)){
            $mobileConditions = $mobileConditions_query->toArray();
        }else{
            $mobileConditions = array();
        }
		$functionConditions_query = $this->FunctionConditions->find('list',[
																			'conditions' => ['FunctionConditions.status' => 1],
																			'keyField' => 'id',
																			'valueField' => 'function_condition',
																		   ]
																  );
		$functionConditions_query = $functionConditions_query->hydrate(false);
        if(!empty($functionConditions_query)){
            $functionConditions = $functionConditions_query->toArray();
        }else{
            $functionConditions = array();
        }
		//problem type options
		$problemTypeOptions_query = $this->ProblemTypes->find('list',[
                                                                'keyField' => 'id',
                                                                'valueField' => 'problem_type'
                                                               ]
                                                        );
        $problemTypeOptions_query = $problemTypeOptions_query->hydrate(false);
        if(!empty($problemTypeOptions_query)){
            $problemTypeOptions = $problemTypeOptions_query->toArray();
        }else{
            $problemTypeOptions = array();
        }
		$this->set(compact('mobileConditions','functionConditions','problemTypeOptions'));
	}
    
    private function get_active_brand_models(){//being used in edit()
		//finding the active combinations
		//capturing the mobile model id and brand ids from mobilerepairprice table with status 1 ie active
		$activeCombinations_query = $this->MobileRepairPrices->find('all',array('conditions' => array('MobileRepairPrices.status'=>1,
												       'MobileRepairPrices.repair_price > 0'),
							      'fields' => array('mobile_model_id','brand_id'),
							      'group' => 'MobileRepairPrices.mobile_model_id'
							      ));
        $activeCombinations_query = $activeCombinations_query->hydrate(false);
        if(!empty($activeCombinations_query)){
            $activeCombinations = $activeCombinations_query->toArray();
        }else{
            $activeCombinations = array();
        }
		$activeBrands = array();
		$activeModels = array();
		
		//finding the active brands and models as per the active combinations fetched above
        //pr($activeCombinations);die;
		foreach($activeCombinations as $key => $activeCombination){
            //pr($activeCombination);die;
			$activeBrands[$activeCombination['brand_id']] = $activeCombination['brand_id'];
			$activeModels[$activeCombination['mobile_model_id']] = $activeCombination['mobile_model_id'];
		}
		
		$activeData = array('activeBrands' => $activeBrands, 'activeModels' => $activeModels);
		
		return $activeData;
	}
    
    private function get_repair_and_sale($id = ''){//for getting repair and sale information of repair in edit()
		//repair sale data corresponding to this repair
		$dataRepairSale_query = $this->MobileRepairSales->find('all',array(
									'conditions' => array('MobileRepairSales.mobile_repair_id' => $id)
                                                        ));
        $dataRepairSale_query = $dataRepairSale_query->hydrate(false);
        if(!empty($dataRepairSale_query)){
            $dataRepairSale = $dataRepairSale_query->toArray();
        }else{
            $dataRepairSale = array();
        }
		
		$dataPerId_query = $this->MobileRepairs->find('all',array(
					'conditions' => array('MobileRepairs.id'=>$id)
                                                ));
        $dataPerId_query = $dataPerId_query->hydrate(false);
        if(!empty($dataPerId_query)){
            $dataPerId = $dataPerId_query->first();
        }else{
            $dataPerId = array();
        }
        
		$dataRepairSale_query = $this->MobileRepairSales->find('all',array(
									'conditions' => array('MobileRepairSales.mobile_repair_id' => $id),
                                                        ));
        $dataRepairSale_query = $dataRepairSale_query->hydrate(false);
        if(!empty($dataRepairSale_query)){
            $dataRepairSale = $dataRepairSale_query->toArray();
        }else{
            $dataRepairSale = array();
        }

		$repair_sale = array('dataRepairSale' => $dataRepairSale, 'dataPerId' => $dataPerId);
		return $repair_sale;
	}
    
    private function get_user_kiosk_comments_conditions($id = ''){//being used in mobile repair edit
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
		$kiosks_query = $this->MobileRepairs->Kiosks->find('list',[
                                                                'keyField' => 'id',
                                                                'valueField' => 'name',
                                                                'conditions' => ['Kiosks.status' => 1]
                                                            ]
                                                    );
        $kiosks_query = $kiosks_query->hydrate(false);
        if(!empty($kiosks_query)){
            $kiosks = $kiosks_query->toArray();
        }else{
            $kiosks = array();
        }
		$comments_query = $this->MobileRepairs->CommentMobileRepairs->find('all', array(
									 //'fields' => array('*'),
									 'conditions' => array('CommentMobileRepairs.status' => 1,'CommentMobileRepairs.mobile_repair_id' => $id),
									 'contain' => array('Users'),
									 'order' => array('CommentMobileRepairs.id DESC'),
									 'limit' => 5
									));
        $comments_query = $comments_query->hydrate(false);
        if(!empty($comments_query)){
            $comments = $comments_query->toArray();
        }else{
            $comments = array();
        }
		$mobileConditions_query = $this->MobileConditions->find('list',[
                                                                    'keyField' => 'id',
                                                                    'valueField' => 'mobile_condition',
                                                                 ]
                                                          );
        $mobileConditions_query = $mobileConditions_query->hydrate(false);
        if(!empty($mobileConditions_query)){
            $mobileConditions = $mobileConditions_query->toArray();
        }else{
            $mobileConditions = array();
        }
		//'conditions' => array('MobileCondition.status' => 1), 
		$function_conditions_query = $this->FunctionConditions->find('list',[
																			'keyField' => 'id',
																			'valueField' => 'function_condition',
																		  ]
																   );
        $function_conditions_query = $function_conditions_query->hydrate(false);
        if(!empty($function_conditions_query)){
            $function_conditions = $function_conditions_query->toArray();
        }else{
            $function_conditions = array();
        }
		//'conditions' => array('FunctionCondition.status' => 1), commented status 1 as it may create notices during edit in the email if any function is disabled after booking
		$repairLogs_query = $this->MobileRepairLogs->find('all',array(
					'conditions' => array('MobileRepairLogs.mobile_repair_id' => $id),
					'order' => array('MobileRepairLogs.id DESC')
                                                    ));
        $repairLogs_query = $repairLogs_query->hydrate(false);
        if(!empty($repairLogs_query)){
            $repairLogs = $repairLogs_query->toArray();
        }else{
            $repairLogs = array();
        }
		//pr($repairLogs);die;
		$miscData = array('users' => $users, 'kiosks' => $kiosks, 'comments' => $comments, 'mobileConditions' => $mobileConditions, 'function_conditions' => $function_conditions, 'repairLogs' => $repairLogs);
		
		return $miscData;
	}
    
    private function kiosk_address($kioskID = ''){//for address in email (in edit())
		//for address in email
		$mobileConditions_query = $this->MobileConditions->find('list',[
                                                                    'keyField' => 'id',
                                                                    'valueField' => 'mobile_condition',
                                                                 ]
                                                          );
        $mobileConditions_query = $mobileConditions_query->hydrate(false);
        if(!empty($mobileConditions_query)){
            $mobileConditions = $mobileConditions_query->toArray();
        }else{
            $mobileConditions = array();
        }
		$function_conditions_query = $this->FunctionConditions->find('list',[
                                                                        'keyField' => 'id',
                                                                        'valueField' => 'function_condition',
                                                                      ]
                                                               );
        $function_conditions_query = $function_conditions_query->hydrate(false);
        if(!empty($function_conditions_query)){
            $function_conditions = $function_conditions_query->toArray();
        }else{
            $function_conditions = array();
        }
		$countryOptions = Configure::read('uk_non_uk');
		$kioskaddress_query = $this->Kiosks->find('all',array(
			'fields' => array('Kiosks.address_1',
							  'Kiosks.address_2',
							  'Kiosks.city',
							  'Kiosks.state',
							  'Kiosks.country',
							  'Kiosks.zip',
							  'Kiosks.contact' ),
			'conditions'=> array('Kiosks.id' => $kioskID)
			)
		);
        $kioskaddress_query = $kioskaddress_query->hydrate(false);
        if(!empty($kioskaddress_query)){
            $kioskaddress = $kioskaddress_query->first();
        }else{
            $kioskaddress = array();
        }
		//pr($kioskaddress);die;
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
		
		$kioskAddressArr = array('kioskaddress1' => $kioskaddress1, 'kioskaddress2' => $kioskaddress2, 'kioskcity' => $kioskcity, 'kioskstate' => $kioskstate, 'kioskcountry' => $kioskcountry, 'kioskzip' => $kioskzip, 'kioskcontact' => $kioskcontact);
		
		return $kioskAddressArr;
	}
    
    private function max_repair_dayz($problemTypStr = '', $brandId = '', $mobileModelId = ''){//being used in edit()
		//repair dayz for showing in frontend and email
		$problemTypArr = explode("|",$problemTypStr);
		
		foreach($problemTypArr as $p => $problemTyp){
            $conn = ConnectionManager::get('default');
            $stmt = $conn->execute("SELECT `repair_days` from `mobile_repair_prices` WHERE `brand_id`='$brandId' AND `mobile_model_id`='$mobileModelId' AND `problem_type`='$problemTyp'"); 
            $repairDays[] = $stmt ->fetchAll('assoc');
			//$repairDays[] = $this->MobileRepairPrice->query("SELECT `repair_days` from `mobile_repair_prices` WHERE `brand_id`='$brandId' AND `mobile_model_id`='$mobileModelId' AND `problem_type`='$problemTyp'");
		}
		//pr($repairDays);die;
		$repairDayz = $this->repairDayz;//default
		
		if(!empty($repairDays[0][0]['repair_days'])){
			$repairDayz['repair_days_a'] = $repairDays[0][0]['repair_days'];	
		}
		
		if(!empty($repairDays[1][0]['repair_days'])){
			$repairDayz['repair_days_b'] = $repairDays[1][0]['repair_days'];	
		}
					
		if(!empty($repairDays[2][0]['repair_days'])){
			$repairDayz['repair_days_c'] = $repairDays[2][0]['repair_days'];	
		}
		$maxRepairDays = max($repairDayz);
		
		return $maxRepairDays;
    }
    
    private function mobile_condition_data($dataRepair = array(), $dataPerId = array()){
		//pr($dataRepair);die;
		//getting phone and function condition for email purpose in edit()
		$mobileConditions_query = $this->MobileConditions->find('list',[
                                                                    'keyField' => 'id',
                                                                    'valueField' => 'mobile_condition'
                                                                 ]
                                                        );
        $mobileConditions_query = $mobileConditions_query->hydrate(false);
        if(!empty($mobileConditions_query)){
            $mobileConditions = $mobileConditions_query->toArray();
        }else{
            $mobileConditions = array();
        }
		$function_conditions_query = $this->FunctionConditions->find('list',[
                                                                        'keyField' => 'id',
                                                                        'valueField' => 'function_condition'
                                                                      ]
                                                               );
        $function_conditions_query = $function_conditions_query->hydrate(false);
        if(!empty($function_conditions_query)){
            $function_conditions = $function_conditions_query->toArray();
        }else{
            $function_conditions = array();
        }
        //pr($dataPerId);die;
		if(array_key_exists('MobileRepair',$dataRepair)){
			//in case of rebook we are storing the mobile condition and function condition here
			if(array_key_exists('mobile_condition',$dataRepair['MobileRepair'])){
				$mobile_condition_array = $dataRepair['MobileRepair']['mobile_condition'];
			}else{
				$mobile_condition_str = $dataPerId['mobile_condition'];
				$mobile_condition_array = array();
				if(!empty($mobile_condition_str)){
					$mobile_condition_array = explode("|",$mobile_condition_str);
				}
			}
			
			if(array_key_exists('function_condition',$dataRepair['MobileRepair'])){
				$function_condition_array = $dataRepair['MobileRepair']['function_condition'];
			}else{
				$function_condition_str = $dataPerId['function_condition'];
				$function_condition_array = array();
				if(!empty($function_condition_str)){
					$function_condition_array = explode("|",$function_condition_str);
				}
			}
			
			$mobileConditionRemark = $dataRepair['MobileRepair']['mobile_condition_remark'];
			//for email purpose till
		}else{
			//in case of delivered repaired by kiosk or dispatched to kiosk by technician (variables for email).
			$mobile_condition_str = $dataPerId['mobile_condition'];
			$mobile_condition_array = array();
			if(!empty($mobile_condition_str)){
				$mobile_condition_array = explode("|",$mobile_condition_str);
			}
			
			$function_condition_str = $dataPerId['function_condition'];
			$function_condition_array = array();
			if(!empty($function_condition_str)){
				$function_condition_array = explode("|",$function_condition_str);
			}
			
			$mobileConditionRemark = $dataPerId['mobile_condition_remark'];
		}
		
		if($this->setting['function_test_notification'] == 'active'){
			if(count($function_condition_array)){
				$funcConditionArr = array();
				foreach($function_condition_array as $fc => $function_condition_id){
					if(array_key_exists($function_condition_id, $function_conditions)){
						$funcConditionArr[] = $function_conditions[$function_condition_id];
					}
				}
				$funcConditionStr = "<br/><br/>**Phone's function test(at the time of booking): ".implode(", ", $funcConditionArr).".";
			}else{
				$funcConditionStr = '';
			}
		}else{
			$funcConditionStr = '';
		}
					
		if($this->setting['phone_condition_notification'] == 'active'){
			if(count($mobile_condition_array)){
				$phoneConditionArr = array();
				foreach($mobile_condition_array as $mc => $mobile_condition_id){
					//1000 is for others
					if($mobile_condition_id == '1000'){
						$phoneConditionArr[] = $mobileConditionRemark;
					}else{
						if(array_key_exists($mobile_condition_id, $mobileConditions)){
							$phoneConditionArr[] = $mobileConditions[$mobile_condition_id];
						}
					}
				}
				$phoneConditionStr = "<br/><br/>**Phone condition(at the time of booking): ".implode(", ", $phoneConditionArr).".";
			}else{
				$phoneConditionStr = '';
			}
		}else{
			$phoneConditionStr = '';
		}
		
		$mobConditionArr = array('funcConditionStr' => $funcConditionStr, 'phoneConditionStr' => $phoneConditionStr);
		return $mobConditionArr;
	}
    
    private function update_repair_sale_admin($repairData = array(), $dataPerId = array()){//using in edit()
		//pr($repairData);die;
		//from this function we save the sale in case admin,manager,technician makes any change to the repair cost on edit screen
		if(!array_key_exists('submit_repair',$repairData) &&
			!array_key_exists('add_2_basket',$repairData) &&
			!array_key_exists('empty_basket',$repairData)){
			//echo "hi";
				// ||(array_key_exists('submit_repair',$repairData) &&$repairData['submit_repair'] != 'Add to Repair')
				//case when parts are being added to the repair, will not update the sale in that step
				//pr($repairData);die;
                $rprId = $repairData['MobileRepair']['id'];
				$ttlCst = $repairData['MobileRepair']['total_cost'];
				if(
				   array_key_exists('total_cost_hidden',$repairData['MobileRepair'] ) &&
				   $repairData['MobileRepair']['total_cost_hidden'] != $ttlCst){
					//echo "hii";die;
					//Step 1: save mobile repair data with proper validations
					//Step 2: Save Mobile Log data
					//Step 3: Take user to udpate Payment modes with respect to change in Pmt.
					//Step 4: If cancels on that screen, redirect user back to edit screen.
					//Step 5: If success redirect user to index page of Repair.
					$estimatedCost = $repairData['MobileRepair']['estimated_cost'];
					unset($repairData['MobileRepair']['estimated_cost']);
					$repairData['MobileRepair']['mobile_condition'] = implode("|",$repairData['MobileRepair']['mobile_condition']);
					if(array_key_exists('function_condition',$repairData['MobileRepair']) && is_array($repairData['MobileRepair']['function_condition'])){
						$repairData['MobileRepair']['function_condition'] = implode("|",$repairData['MobileRepair']['function_condition']);
					}
					
					$netCosta = (array_key_exists('net_cost_a', $repairData['MobileRepair'])) ? floatval($repairData['MobileRepair']['net_cost_a']) : 0;
					$netCostb = (array_key_exists('net_cost_b', $repairData['MobileRepair'])) ? floatval($repairData['MobileRepair']['net_cost_b']) : 0;
					$netCostc = (array_key_exists('net_cost_c', $repairData['MobileRepair'])) ? floatval($repairData['MobileRepair']['net_cost_c']) : 0;
					if(($netCosta + $netCostb + $netCostc) > 0){
						$repairData['net_cost'] =  $netCosta + $netCostb + $netCostc;
					}
					
					//----------------------------------------
					//still there could be issue when we would have mulitiple repair
					//----------------------------------------
					
					//here we are only updating the fields other than the estimated cost
					//and sendind the user to payment page in case of difference in estimated cost
					//$maxRepairDays;
					//pr($this->request->data);die;
					$mobile_repair_entity = $this->MobileRepairs->get($rprId);
					$mobile_repair_entity = $this->MobileRepairs->patchEntity($mobile_repair_entity,$repairData, array('validate' => false));
					if($this->MobileRepairs->save($mobile_repair_entity, array('validate' => false))){
						//saving logs
						$mobileRepairLogsData = array(
									'kiosk_id' => $dataPerId['kiosk_id'],
									'user_id' => $this->request->Session()->read('Auth.User.id'),
									'mobile_repair_id' => $dataPerId['id'],
									'repair_status' => $dataPerId['status']
										);
						$MobileRepairLogsEntity = $this->MobileRepairLogs->newEntity($mobileRepairLogsData,['validation' => false]);
						$MobileRepairLogsEntity = $this->MobileRepairLogs->patchEntity($MobileRepairLogsEntity,$mobileRepairLogsData,['validation' => false]);
						$this->MobileRepairLogs->save($MobileRepairLogsEntity);
						//echo "saved";die;
						
						if($dataPerId['internal_repair'] != 1 &&
						   ($dataPerId['status'] == DELIVERED_REPAIRED_BY_KIOSK ||
						    $dataPerId['status'] == DELIVERED_REPAIRED_BY_TECHNICIAN)){
							//die('here');
							//sending to payment update page for taking the difference of payment
							$paymentData_query = $this->RepairPayments->find('all',array(
													'conditions' => array('RepairPayments.mobile_repair_id'=>$dataPerId['id'])
													)
												);
							$paymentData_query = $paymentData_query->hydrate(false);
							if(!empty($paymentData_query)){
								$paymentData = $paymentData_query->toArray();
							}else{
								$paymentData = array();
							}
							$this->set('paymentData',$paymentData);
							$this->render('admin_repair_payment');
							$fakeblock = 1;
							//die;
							return $fakeblock;
							goto fakeblock;
						}else{
							//echo "hi";die;
							$mobileRepairData = $repairData['MobileRepair'];
							
							$netCosta = (array_key_exists('net_cost_a', $repairData['MobileRepair'])) ? floatval($repairData['MobileRepair']['net_cost_a']) : 0;
							$netCostb = (array_key_exists('net_cost_b', $repairData['MobileRepair'])) ? floatval($repairData['MobileRepair']['net_cost_b']) : 0;
							$netCostc = (array_key_exists('net_cost_c', $repairData['MobileRepair'])) ? floatval($repairData['MobileRepair']['net_cost_c']) : 0;
							
							if(($netCosta + $netCostb + $netCostc) > 0){
								$mobileRepairData['net_cost'] =  $netCosta + $netCostb + $netCostc;
							}
							
							$mobileRepairData['total_cost'] = $estimatedCost;
							if(array_key_exists('mobile_condition',$mobileRepairData) && is_array($mobileRepairData['mobile_condition'])){
								$mobileRepairData['mobile_condition'] = implode("|",$mobileRepairData['mobile_condition']);
							}
							if(array_key_exists('function_condition',$mobileRepairData) && is_array($mobileRepairData['function_condition'])){
								$mobileRepairData['function_condition'] = implode("|",$mobileRepairData['function_condition']);
							}
							//$this->MobileRepair->id = $dataPerId['id'];
							foreach($mobileRepairData as $key => $mobileRepairDatum){
								if(empty($mobileRepairDatum))unset($mobileRepairData[$key]);
							}
							$estimatedCost = array();
							$problemType = array();
							if(array_key_exists('estimated_cost_a', $mobileRepairData) && !empty($mobileRepairData['estimated_cost_a'])){
								$problemType[] = $mobileRepairData['problem_type_a'];
								//$estimatedCost[] = $problemVar['estimated_cost_a'];
								if($dataPerId['internal_repair'] == 1){
									$estimatedCost[]= .0001;
								}else{
									$estimatedCost[]= $mobileRepairData['estimated_cost_a'];
								}
							}
							if(array_key_exists('estimated_cost_b', $mobileRepairData) && !empty($mobileRepairData['estimated_cost_b'])){
								$problemType[] = $mobileRepairData['problem_type_b'];
								if($dataPerId['internal_repair'] == 1){
									$estimatedCost[]= .0001;
								}else{
									$estimatedCost[]=$mobileRepairData['estimated_cost_b'];
								}
							}
							if(array_key_exists('estimated_cost_c', $mobileRepairData) && !empty($mobileRepairData['estimated_cost_c'])){
								$problemType[] = $mobileRepairData['problem_type_c'];
								if($dataPerId['internal_repair'] == 1){
									$estimatedCost[]= .0001;
								}else{
									$estimatedCost[]=$mobileRepairData['estimated_cost_c'];
								}
							}
							//pr($problemType);
							$mobileRepairData['problem_type'] = implode("|",$problemType);
							$mobileRepairData['estimated_cost'] = implode("|",$estimatedCost);
							//$this->MobileRepair->saveField('total_cost',$estimatedCost);
							
							$mobile_repair_entity1 = $this->MobileRepairs->get($dataPerId['id']);
							$mobile_repair_entity1 = $this->MobileRepairs->patchEntity($mobile_repair_entity1,$mobileRepairData, array('validate' => false));
							$this->MobileRepairs->save($mobile_repair_entity1, array('validate' => false));
							
							$this->Flash->success('Repair Updated!!!');
							$domain_name = $_SERVER['HTTP_HOST'];
							header("location:http://$domain_name/mobile-repairs");
							//return $this->redirect(array('action' => 'index'));
							die;
						}
					}else{
						pr($repairData['MobileRepair']);die("Failed to save!!!");
					}
				}
				//echo "out";die;
				$saleInfo_query = $this->MobileRepairSales->find('all', array('conditions' => array( 'MobileRepairSales.mobile_repair_id' => $rprId, 'MobileRepairSales.refund_status' => 0, 'MobileRepairSales.amount > 0'), 'order' => 'MobileRepairSales.id desc'));
                $saleInfo_query = $saleInfo_query->hydrate(false);
                if(!empty($saleInfo_query)){
                    $saleInfo = $saleInfo_query->first();
                }else{
                    $saleInfo = array();
                }
				if(count($saleInfo)){
					$sale_ide = $saleInfo['id'];
					$MobileRepairSalesEntity = $this->MobileRepairSales->get($sale_ide);
					$data = array('amount' => $ttlCst);
					$MobileRepairSalesEntity = $this->MobileRepairSales->patchEntity($MobileRepairSalesEntity,$data,['validate' => false]);
					if($this->MobileRepairSales->save($MobileRepairSalesEntity)){
						
					}
				}
		fakeblock:
		;
		}
	}
    
    private function get_email_statement($repairStatus = '', $repairBookingData = array(), $mobileModels = array(), $maxRepairDays = '', $phoneConditionStr = '', $funcConditionStr = '', $kiosks = array(), $kioskAddressArr = array(), $repair_email_message = '', $dataPerId = array()){
		$kioskaddress1 = $kioskAddressArr['kioskaddress1'];
		$kioskaddress2 = $kioskAddressArr['kioskaddress2'];
		$kioskcity = $kioskAddressArr['kioskcity'];
		$kioskstate = $kioskAddressArr['kioskstate'];
		$kioskcountry = $kioskAddressArr['kioskcountry'];
		$kioskzip = $kioskAddressArr['kioskzip'];
		$kioskcontact = $kioskAddressArr['kioskcontact'];
		$messageKioskcontact = strip_tags($kioskAddressArr['kioskcontact']);//removing br in case of message
		$messageKioskcontact = str_replace(array('Contact: ','Contact:'), array('',''), $messageKioskcontact);
		
		$statement = "";
		$messageStatement = '';
		$statementArray = array();
		$mobileRepairID = $dataPerId['id'];
		$link = $this->setting['repair_unlock_terms_link'];
		switch($repairStatus){
			case 2:
				
		$statement = "Hi ".$repairBookingData['customer_fname']." ".$repairBookingData['customer_lname'].",<br/><br/>
		The repair has been rebooked for your Mobile Repair id :".$mobileRepairID."and Mobile Model is:".$mobileModels[$repairBookingData['mobile_model_id']]." phone &#40;IMEI: ".$repairBookingData['imei']."&#41;. Your repair is expected to get done within ".$maxRepairDays." working day&#40;s&#41;.".$phoneConditionStr.$funcConditionStr."<br/>
		<br/>Regards,<br/>".$kiosks[$dataPerId['kiosk_id']].$kioskaddress1.$kioskaddress2.$kioskcity.$kioskstate.$kioskcountry.$kioskzip.$kioskcontact."<br/><br/>".$repair_email_message;
		$messageStatement = "Your phone repair id {$mobileRepairID} has been re-booked and will be repaired within {$maxRepairDays} working days. Please contact the kiosk with any queries t&s {$link}";
				break;
		
				case 3:
		$statement = "Hi ".$repairBookingData['customer_fname']." ".$repairBookingData['customer_lname'].",<br/><br/>
		Your Mobile Repair id :".$mobileRepairID."and Mobile Model is :".$mobileModels[$repairBookingData['mobile_model_id']]." phone &#40;IMEI: ".$repairBookingData['imei']."&#41; has been dispatched to specialist team for repair.".$phoneConditionStr.$funcConditionStr."<br/><br/>
		Regards,<br/>".$kiosks[$dataPerId['kiosk_id']].$kioskaddress1.$kioskaddress2.$kioskcity.$kioskstate.$kioskcountry.$kioskzip.$kioskcontact."<br/><br/>".$repair_email_message;
				break;
				
				case 4:
		$statement = "Hi ".$repairBookingData['customer_fname']." ".$repairBookingData['customer_lname'].",<br/><br/>
		Your Mobile Repair id :".$mobileRepairID."and Mobile Model is :".$mobileModels[$repairBookingData['mobile_model_id']]." phone &#40;IMEI: ".$repairBookingData['imei']."&#41; has been successfully repaired. Please contact ".$kiosks[$dataPerId['kiosk_id']]." before collecting the phone.".$phoneConditionStr.$funcConditionStr."<br/><br/>
		Regards,<br/>".$kiosks[$repairBookingData['kiosk_id']].$kioskaddress1.$kioskaddress2.$kioskcity.$kioskstate.$kioskcountry.$kioskzip.$kioskcontact."<br/><br/>".$repair_email_message;
		$messageStatement = "Your phone repair id {$mobileRepairID} successfully repaired is ready for collection. Please contact {$messageKioskcontact} before collection t&s {$link}";
				break;
				
				case 5:
		$statement = "Hi ".$repairBookingData['customer_fname']." ".$repairBookingData['customer_lname'].",<br/><br/>
		Your Mobile Repair id :".$mobileRepairID."and Mobile Model is :".$mobileModels[$repairBookingData['mobile_model_id']]." phone &#40;IMEI: ".$repairBookingData['imei']."&#41; has been received at ".$kiosks[$repairBookingData['kiosk_id']].". Unfortunately, we could not repair your phone.<br/><br/>
		We regret for the inconvenience.".$phoneConditionStr.$funcConditionStr."<br/><br/>
		Regards,<br/>".$kiosks[$dataPerId['kiosk_id']].$kioskaddress1.$kioskaddress2.$kioskcity.$kioskstate.$kioskcountry.$kioskzip.$kioskcontact."<br/><br/>".$repair_email_message;
		$messageStatement = "Your phone repair id {$mobileRepairID} un-repaired is ready for collection. Please contact {$messageKioskcontact} before collection t&s {$link}";
				break;
				
				case 6:
		$statement = "Hi ".$repairBookingData['customer_fname']." ".$repairBookingData['customer_lname'].",<br/><br/>
		Your Mobile Repair id :".$mobileRepairID."and Mobile Model is :".$mobileModels[$repairBookingData['mobile_model_id']]." phone &#40;IMEI: ".$repairBookingData['imei']."&#41; has been succesfully repaired and collected by you.<br/><br/>
		Thank you for using our repair services.".$phoneConditionStr.$funcConditionStr."<br/><br/>
		Regards,<br/>".$kiosks[$dataPerId['kiosk_id']].$kioskaddress1.$kioskaddress2.$kioskcity.$kioskstate.$kioskcountry.$kioskzip.$kioskcontact."<br/><br/>".$repair_email_message;
		$messageStatement = "Thank you for collecting your repaired phone id {$mobileRepairID}. Thank you for using our service. t&s {$link}";
				break;
				
				case 7:
		$statement = "Hi ".$repairBookingData['customer_fname']." ".$repairBookingData['customer_lname'].",<br/><br/>
		Your Mobile Repair id : ".$mobileRepairID."and Mobile Model is:".$mobileModels[$repairBookingData['mobile_model_id']]." phone &#40;IMEI: ".$repairBookingData['imei']."&#41; could not be repaired and has been delivered back to you.<br/><br/>
		We regret for the inconvenience.".$phoneConditionStr.$funcConditionStr."<br/><br/>
		Regards,<br/>".$kiosks[$dataPerId['kiosk_id']].$kioskaddress1.$kioskaddress2.$kioskcity.$kioskstate.$kioskcountry.$kioskzip.$kioskcontact."<br/><br/>".$repair_email_message;
		$messageStatement = "Thank you for collecting your un-repaired phone id {$mobileRepairID}. We are sorry we could not repair this phone. t&s {$link}";
				break;
				
				case 8:
		$statement = "Hi ".$repairBookingData['customer_fname']." ".$repairBookingData['customer_lname'].",<br/><br/>
		Your Mobile Repair id :".$mobileRepairID."and Mobile Model is : ".$mobileModels[$repairBookingData['mobile_model_id']]." phone &#40;IMEI: ".$repairBookingData['imei']."&#41; has been succesfully repaired and collected by you.<br/><br/>
		Thank you for using our repair services.".$phoneConditionStr.$funcConditionStr."<br/><br/>
		Regards,<br/>".$kiosks[$dataPerId['kiosk_id']].$kioskaddress1.$kioskaddress2.$kioskcity.$kioskstate.$kioskcountry.$kioskzip.$kioskcontact."<br/><br/>".$repair_email_message;
		$messageStatement = "Thank you for collecting your repaired phone id {$mobileRepairID}. Thank you for using our service. t&s {$link}";
				break;
				
				case 9:
		$statement = "Hi ".$repairBookingData['customer_fname']." ".$repairBookingData['customer_lname'].",<br/><br/>
		Your Mobile Repair id".$mobileRepairID."and Mobile Model is".$mobileModels[$repairBookingData['mobile_model_id']]." phone &#40;IMEI: ".$repairBookingData['imei']."&#41; could not be repaired and has been delivered back to you.<br/><br/>
		We regret for the inconvenience.".$phoneConditionStr.$funcConditionStr."<br/><br/>
		Regards,<br/>".$kiosks[$dataPerId['kiosk_id']].$kioskaddress1.$kioskaddress2.$kioskcity.$kioskstate.$kioskcountry.$kioskzip.$kioskcontact."<br/><br/>".$repair_email_message;
		$messageStatement = "Thank you for collecting your un-repaired phone id {$mobileRepairID}. We are sorry we could not repair this phone. t&s {$link}";
				break;
				
				case 16:
		if(!isset($kioskcity))$kioskcity = "";
		$statement = "Hi ".$repairBookingData['customer_fname']." ".$repairBookingData['customer_lname'].",<br/><br/>
		Your Mobile Repair id:".$mobileRepairID."and Mobile Model is:".$mobileModels[$repairBookingData['mobile_model_id']]." phone &#40;IMEI: ".$repairBookingData['imei']."&#41; has been received by the concerned technician. Your repair will start very soon.".$phoneConditionStr.$funcConditionStr."<br/><br/>
		Regards,<br/>".$kiosks[$dataPerId['kiosk_id']].$kioskaddress1.$kioskaddress2.$kioskcity.$kioskstate.$kioskcountry.$kioskzip.$kioskcontact."<br/><br/>".$repair_email_message;
				break;
		}
		$statementArray = array('statement' => $statement, 'messageStatement' => $messageStatement);
		return $statementArray;
	}
    
    private function refund_rebooked_unrepaired($id = ''){ //using in edit()
		//case rebooked repair is getting delivered unrepaired
		//here we are auto refunding the customer if sale was generated
		$countIfRefunded = $this->MobileRepairSale->find('count', array('conditions' => array('MobileRepairSale.refund_status' => 1, 'MobileRepairSale.mobile_repair_id' => $id)));
		$saleData = $this->MobileRepairSale->find('first', array('conditions' => array('MobileRepairSale.amount > 0', 'MobileRepairSale.refund_status' => 0, 'MobileRepairSale.mobile_repair_id' => $id), 'order' => 'MobileRepairSale.id desc'));
		if($countIfRefunded == 0 && count($saleData)){//means it was never refunded however sale does exist for this repair id, so we need to refund it
			//pr($saleData);die;
			$refund_by = $this->Auth->user('id');
			$refund_remarks = 'Unrepaired delivery for rebooked mobile';
			$refund_on = date('Y-m-d h:i:s A');
			$refund_amount = -$saleData['MobileRepairSale']['amount'];
			$currentKiosk = $saleData['MobileRepairSale']['kiosk_id'];
			
			$mobileRepairRefundData = array(
				'refund_status' => 1,
				'kiosk_id' => $currentKiosk,
				'retail_customer_id' => $saleData['MobileRepairSale']['retail_customer_id'],
				'mobile_repair_id' => $id,
				'sold_by' => $saleData['MobileRepairSale']['sold_by'],
				'sold_on' => $saleData['MobileRepairSale']['sold_on'],
				'amount' => 0,
				'refund_by' => $refund_by,
				'refund_on' => $refund_on,
				'refund_remarks' => $refund_remarks,
				'refund_amount' => $refund_amount
				
			);
			
			$this->MobileRepairSale->create();
			if($this->MobileRepairSale->save($mobileRepairRefundData)){
				$this->MobileRepairSale->id = $saleData['MobileRepairSale']['id'];
				$this->MobileRepairSale->saveField('refund_by',$refund_by);
				//sending refund by to the original sale, implies that it has been refunded
				$this->MobileRepairSale->saveField('refund_remarks',$refund_remarks);
				//sending refund remarks to the original sale, implies that it has been refunded
				$this->MobileRepairSale->saveField('refund_on',$refund_on);
				//sending refund on to the original sale, implies that it has been refunded
				$this->MobileRepairSale->saveField('refund_amount',$refund_amount);
				//sending refund amount to the original sale, implies that it has been refunded
				$this->MobileRepairSale->saveField('status',1);
				//sending status 1 to the original sale, implies that it has been refunded
				
				$repairEditData = array('id' => $id,'status_refund' => 1);     
				$this->MobileRepair->save($repairEditData, array('validate' => false));
				$mobileRepairLogData = array(
							'kiosk_id' => $currentKiosk,
							'user_id' => $this->Auth->user('id'),
							'mobile_repair_id' => $id,
							'status' => 1 //for refunded repair
							     );
				$this->MobileRepairLog->create();
				$this->MobileRepairLog->save($mobileRepairLogData);
			}
		}
	}
    
    private function save_technician_changes($problemVar = array()){
		if(!empty($problemVar['estimated_cost_a'])){
			$problemType[] = $problemVar['problem_type_a'];
			$estimatedCost[] = $problemVar['estimated_cost_a'];
		}
		
		if(array_key_exists('estimated_cost_b',$problemVar) && !empty($problemVar['estimated_cost_b'])){
			$problemType[] = $problemVar['problem_type_b'];
			$estimatedCost[] = $problemVar['estimated_cost_b'];
		}
		
		if(array_key_exists('estimated_cost_c',$problemVar) && !empty($problemVar['estimated_cost_c'])){
			$problemType[] = $problemVar['problem_type_c'];
			$estimatedCost[] = $problemVar['estimated_cost_c'];
		}
		
		$netCosta = (array_key_exists('net_cost_a', $problemVar)) ? floatval($problemVar['net_cost_a']) : 0;
		$netCostb = (array_key_exists('net_cost_b', $problemVar)) ? floatval($problemVar['net_cost_b']) : 0;
		$netCostc = (array_key_exists('net_cost_c', $problemVar)) ? floatval($problemVar['net_cost_c']) : 0;
							
		$net_cost = $netCosta + $netCostb + $netCostc;
		
		$technicianData = array(
			'id' => $problemVar['id'],
			'problem_type' => implode('|',$problemType),
			'estimated_cost' => implode('|',$estimatedCost),
			'imei' => $problemVar['imei'],
			'description' => $problemVar['description'],
			'brief_history' => $problemVar['brief_history'],
			'actual_cost' => $problemVar['actual_cost'],
			//'net_cost' => $net_cost,
			//'received_at' => $problemVar['received_at'],
			//'delivered_at' => $date,
			'phone_password' => $problemVar['phone_password'],
			//'status' => $problemVar['status']
			   );
		
		if($net_cost > 0){
			$technicianData['net_cost'] = $net_cost;
		}
		$MobileRepairsEntity = $this->MobileRepairs->get($problemVar['id']);
		$MobileRepairsEntity = $this->MobileRepairs->patchEntity($MobileRepairsEntity,$technicianData,['validate' => false]);
		$this->MobileRepairs->save($MobileRepairsEntity);
	}
    
    public function delete($id = null) {
		if (!$this->MobileRepairs->exists($id)) {
			throw new NotFoundException(__('Invalid mobile repair'));
		}
		$this->request->allowMethod('post', 'delete');
		if ($this->request->session()->read('Auth.User.group_id') == ADMINISTRATORS || $this->request->session()->read('Auth.User.group_id') == MANAGERS || $this->request->session()->read('Auth.User.group_id') == FRANCHISE_OWNER){
            $query = $this->MobileRepairs->get($id);
			if($this->MobileRepairs->delete($query)){
				$this->Flash->success(__('The mobile repair has been deleted.'));
			}else{
				$this->Flash->error(__('The mobile repair could not be deleted. Please, try again.'));
			}
		} else {
			$this->Flash->error(__('The mobile repair could not be deleted. Please, try again.'));
		}
		return $this->redirect(array('action' => 'index'));
	}
    
    public function viewRepairParts($id = null){
        
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
		$this->set(compact('users'));
		$kiosks_query = $this->Kiosks->find('list',[
                                                'keyField' => 'id',
                                                'valueField' => 'name',
                                                'order' => ['Kiosks.name asc']
                                             ]
                                      );
        $kiosks_query = $kiosks_query->hydrate(false);
        if(!empty($kiosks_query)){
            $kiosks = $kiosks_query->toArray();
        }else{
            $kiosks = array();
        }
		$sessionKioskId = $this->request->Session()->read('kiosk_id');
		$currentTime_conn = ConnectionManager::get('default');
        $currentTime_stmt = $currentTime_conn->execute('SELECT now() as timeDate'); 
        $currentTime = $currentTime_stmt ->fetchAll('assoc');
        //$currentTime = $this->RepairPayment->query('SELECT now() as timeDate');
		$date = date('Y-m-d',strtotime($currentTime[0]['timeDate']));
		$repairData_query = $this->MobileRepairs->find('all', array('conditions' => array('MobileRepairs.id' => $id)));
		$repairData_query = $repairData_query->hydrate(false);
        if(!empty($repairData_query)){
            $repairData = $repairData_query->first();
        }else{
            $repairData = array();
        }
		//getting last repaired info by service center/kiosk from logs
		$repairLogData_query = $this->MobileRepairLogs->find('all', array('conditions' => array('MobileRepairLogs.mobile_repair_id' => $id,
								'OR' => array(
									      array('MobileRepairLogs.repair_status' => DISPATCHED_2_KIOSK_REPAIRED),
									      array('MobileRepairLogs.repair_status' => DELIVERED_REPAIRED_BY_KIOSK)
									      )
									),
								'order' => 'MobileRepairLogs.id desc'
								));
        $repairLogData_query = $repairLogData_query->hydrate(false);
        if(!empty($repairLogData_query)){
            $repairLogData = $repairLogData_query->first();
        }else{
            $repairLogData = array();
        }
		$repairDate = date('Y-m-d',strtotime($repairLogData['created']));
		
		//this kioskID is for admin
		if((int)$repairLogData['service_center_id'] && $repairLogData['service_center_id'] > 0){
			$repairedAt = $kioskID = $repairLogData['service_center_id'];
		}else{
			$repairedAt = $kioskID = $repairLogData['kiosk_id'];
		}
		
		$now_conn = ConnectionManager::get('default');
        $now_stmt = $now_conn->execute('SELECT now() as curdt'); 
        $now = $now_stmt ->fetchAll('assoc');
        //$now = $this->MobileRepairPart->query('SELECT now() as curdt');
		$currntDate = date('Y-m-d',strtotime($now[0]['curdt']));
		$products = array();
		$this->set(compact('products'));
		$viewOtherRepairParts = array();
		if((int)$sessionKioskId){
			$viewRepairParts_query = $this->MobileRepairParts->find('all',array(
								'conditions' => array('MobileRepairParts.mobile_repair_id' => $id,
										      'MobileRepairParts.kiosk_id' => $sessionKioskId
										      ),
								'order' => 'MobileRepairParts.id desc'
								)
							);
            $viewRepairParts_query = $viewRepairParts_query->hydrate(false);
            if(!empty($viewRepairParts_query)){
                $viewRepairParts = $viewRepairParts_query->toArray();
            }else{
                $viewRepairParts = array();
            }
			//for parts not added by kioskitself or added by others
			$viewOtherRepairParts_query = $this->MobileRepairParts->find('all',array(
																'conditions' => array(
																						'MobileRepairParts.mobile_repair_id' => $id,
																						'MobileRepairParts.kiosk_id !=' => $sessionKioskId
																				   ),
																'order' => 'MobileRepairParts.id desc'
																			)
																);
            $viewOtherRepairParts_query = $viewOtherRepairParts_query->hydrate(false);
            if(!empty($viewOtherRepairParts_query)){
                $viewOtherRepairParts = $viewOtherRepairParts_query->toArray();
            }else{
                $viewOtherRepairParts = array();
            }
		}else{
			$viewRepairParts_query = $this->MobileRepairParts->find('all',array(
								'conditions' => array('MobileRepairParts.mobile_repair_id' => $id),
								'order' => 'MobileRepairParts.id desc'
								)
							);
            $viewRepairParts_query = $viewRepairParts_query->hydrate(false);
            if(!empty($viewRepairParts_query)){
                $viewRepairParts = $viewRepairParts_query->toArray();
            }else{
                $viewRepairParts = array();
            }
		}
		
		//pr($viewRepairParts);
		$otherproductIds = $productIds = array();
		foreach($viewRepairParts as $viewRepairPart){
			$productIds[] = $viewRepairPart['product_id'];
		}
		
		foreach($viewOtherRepairParts as $viewRepairPart){
			$productIds[] = $viewRepairPart['product_id'];
		}
		if(empty($productIds)){
			$productIds = array('0'=>null);
		}
		$productName_query = $this->Products->find('list',[
                                                        'keyField' => 'id',
                                                        'valueField' => 'product',
                                                        'conditions' => ['id IN' => $productIds]
                                                    ]
                                            );
        $productName_query = $productName_query->hydrate(false);
        if(!empty($productName_query)){
            $productName = $productName_query->toArray();
        }else{
            $productName = array();
        }
		
		if(empty($productIds)){
			$productIds = array('0'=>null);
		}
		$productsCode_query = $this->Products->find('list',[
                                                        'keyField' => 'id',
                                                        'valueField' => 'product_code',
                                                        'conditions' => array('id IN' => $productIds)
                                                     ]
                                              );
		$productsCode_query = $productsCode_query->hydrate(false);
        if(!empty($productsCode_query)){
            $productsCode = $productsCode_query->toArray();
        }else{
            $productsCode = array();
        }
		$repair_id = $id;
		$kioskId = '0';
		$currentPage = 0;
		if(array_key_exists('current_page',$this->request['data'])){
			$currentPage = $this->request['data']['current_page'];
		}
		$this->set(compact('kioskId','kioskID'));
		if($this->request->is(array('get', 'put'))){
			//pr($this->request);die;
			if(array_key_exists('add_2_basket',$this->request->query)){
				//Case: Adding new products to repair
				//print_r($this->request['data']);
				$session_parts_basket = $this->request->Session()->read('view_parts_basket');
				//$kioskID = $this->request->query['kioskID'];
				if($this->request->session()->read('Auth.User.group_id') == KIOSK_USERS || $this->request->session()->read('Auth.User.group_id') == REPAIR_TECHNICIANS){
					$kioskID = $sessionKioskId;
				}
				
				$parts_basket['repair_id'] = $id;
				if(
				 array_key_exists('PartsRepaired',$this->request->query['data']) &&
				 array_key_exists('item',$this->request->query['data']['PartsRepaired'])
				){
					$partsRepaired = $this->request->query['data']['PartsRepaired']['item'];
					$productCount = 0;
					foreach($partsRepaired as $key => $productID){
						//pr($productID);die;
						if((int)$productID && $productID > 0){      
						 $productCount++;
						 $parts_basket[$productID] = $productID;     
						}     
					}
				}
				
				$sum_total = $this->add_arrays(array($parts_basket,$session_parts_basket));
				
				if(isset($productCount) && ($productCount || count($sum_total) > 1)){
					$this->request->Session()->write('view_parts_basket',$sum_total);
					$sessionKeys = array_keys($this->request->Session()->read('view_parts_basket'));
					array_splice($sessionKeys,0,1,array());//removing the repair_id from the array to find the product names
					if(empty($sessionKeys)){
						$sessionKeys = array('0'=>null);
					}
					$productSesNameList_query = $this->Products->find('all',array('fields'=> array('id','product_code','product'),'conditions' => array('Products.id IN' => $sessionKeys)));
					$productSesNameList_query = $productSesNameList_query->hydrate(false);
					if(!empty($productSesNameList_query)){
						$productSesNameList = $productSesNameList_query->toArray();
					}else{
						$productSesNameList = array();
					}
					$savedPartsName = array();
					$productSesNameDetail = array();
					foreach($productSesNameList as $ps => $productSes){
						$productSesNameDetail[$productSes['id']] = $productSes;
					}
					
					$sessionRow = '';
					foreach($sessionKeys as $sp => $sessionPart){
						$sessionRow.= "<tr>".
							"<td>".
							$productSesNameDetail[$sessionPart]['product_code'].
							"</td>".
							"<td>".
							$productSesNameDetail[$sessionPart]['product'].
							"</td>".
						"</tr>";
						//$svdProducts[] = $productNameList[$savedPart];
					}
					$sessionTable = '';
					if(!empty($sessionRow)){
						$sessionTable = "<table>".
							"<tr>".
								"<th>Product Code</th>".
								"<th>Product</th>".
								"</tr>".$sessionRow.
								"</table>";
					}
						
					$totalProductCount = count($sum_total)-1;
					$this->Flash->success("You have added {$totalProductCount} product(s) to repair for repair id:{$id}<br/>Total Product count for repair:{$totalProductCount}".$sessionTable,['escape' => false]);
				}else{
					$this->Flash->error('No part was added to the basket!');
				}
				
				$this->get_view_repair_parts_categories($kioskID);
				$this->set(compact('repair_id','kioskID'));
				if($currentPage){
					return $this->redirect(array('action' => "view_repair_parts/{$id}/page:$currentPage"));
				}else{
					$this->render('replace_parts_page');
				}
			}elseif(array_key_exists('empty_basket',$this->request->query)){
				//Case: Empty the basket
				//$kioskID = $this->request->query['kioskID'];
				if($this->request->session()->read('Auth.User.group_id') == KIOSK_USERS || $this->request->session()->read('Auth.User.group_id') == REPAIR_TECHNICIANS){
					$kioskID = $sessionKioskId;
				}
				$this->get_view_repair_parts_categories($kioskID);
				$this->request->Session()->delete('view_parts_basket');
				$this->Flash->success('Basket is empty!');
				$this->set(compact('kioskID','repair_id'));
				if($currentPage){
					return $this->redirect(array('action' => "view_repair_parts/{$id}/page:$currentPage"));
				}else{
					$this->render('replace_parts_page');
				}
			}elseif(array_key_exists('submit_repair',$this->request->query)){
                
				$this->partsValidation($id, $repairDate, $currntDate, $repairedAt, $sessionKioskId);
				if($this->request->session()->read('Auth.User.group_id') == KIOSK_USERS || $this->request->session()->read('Auth.User.group_id') == REPAIR_TECHNICIANS){
					$kioskID = $sessionKioskId;
				}
				//$kioskID = $this->request->query['kioskID'];
				$parts_basket['repair_id'] = $id;
				$parts_basket_temp = $sum_total_temp = array();
				$session_parts_basket = $this->request->Session()->read('view_parts_basket');
				if(
				 array_key_exists('PartsRepaired',$this->request->query['data']) &&
				 array_key_exists('item',$this->request->query['data']['PartsRepaired'])
				){
					$partsRepaired = $this->request->query['data']['PartsRepaired']['item'];    
					
					$productCount = 0;
					foreach($partsRepaired as $key => $productID){
						//pr($productID);die;
						if((int)$productID){      
							$productCount++;
							$parts_basket[$productID] = $productID;
							$parts_basket_temp[$productID] = $productID;
						}     
					}
				}
				$sum_total = $this->add_arrays(array($parts_basket,$session_parts_basket));
				$sum_total_temp = $this->add_arrays(array($parts_basket_temp, $session_parts_basket));
				if(count($sum_total_temp) <= 0){
					if($currentPage){
						$this->Flash->error('No part was added to the basket!');
						return $this->redirect(array('action' => "view_repair_parts/{$id}/page:$currentPage"));
					}
				}
				
				$partsSaved = 0;
				$savedPartsArr = array();
				foreach($sum_total as $key => $productID){
					if($key == 'repair_id')continue;
					//$kskId = $dataPerId['MobileRepair']['kiosk_id'];
					$mobileRepairPartData = array(
									'user_id' =>$this->request->session()->read('Auth.User.id'), 
									'mobile_repair_id' => $id,
									'product_id' => $productID,
									'kiosk_id' => $kioskID
								      );
					$MobileRepairPartsEntity = $this->MobileRepairParts->newEntity($mobileRepairPartData,['validate' => false]);
					$MobileRepairPartsEntity = $this->MobileRepairParts->patchEntity($MobileRepairPartsEntity,$mobileRepairPartData,['validate' => false]);
					if($this->MobileRepairParts->save($MobileRepairPartsEntity)){
						$partsSaved++;
						$savedPartsArr[$productID] = $productID;
					}
					
				}
				if($partsSaved > 0){
					$productSource = "kiosk_{$kioskID}_products";
					foreach($sum_total as $key => $productID){
							if($key == 'repair_id')continue;
							//$this->Product->clear();
							//$this->Product->id = $productID;
							$quantity = 1;
							$query = "UPDATE `$productSource` SET `quantity` = `quantity` - $quantity WHERE `$productSource`.`id` = $productID";
							$conn = ConnectionManager::get('default');
							$stmt = $conn->execute($query); 
						}
				}
				if(empty($savedPartsArr)){
					$savedPartsArray = array('0'=>null);
				}else{
					$savedPartsArray = $savedPartsArr;
				}
				$productNameList_query = $this->Products->find('all',array('fields'=> array('id','product','product_code'),'conditions' => array('Products.id IN' => $savedPartsArray)));
				$productNameList_query = $productNameList_query->hydrate(false);
				if(!empty($productNameList_query)){
					$productNameList = $productNameList_query->toArray();
				}else{
					$productNameList = array();
				}
				
				$productNameDetail = array();
				foreach($productNameList as $pn => $productNameDtl){
					$productNameDetail[$productNameDtl['id']] = $productNameDtl;
				}
				$svdProducts = array();
				$finalRow = '';
				foreach($savedPartsArr as $sp => $savedPart){
					$finalRow.= "<tr>".
						"<td>".
						$productNameDetail[$savedPart]['product_code'].
						"</td>".
						"<td>".
						$productNameDetail[$savedPart]['product'].
						"</td>".
					"</tr>";
					//$svdProducts[] = $productNameList[$savedPart];
				}
				$finalTable = '';
				if(!empty($finalRow)){
					$finalTable = "<table>".
						"<tr>".
							"<th>Product Code</th>".
							"<th>Product</th>".
							"</tr>".$finalRow.
							"</table>";
				}
				$productNameStr = implode(', ',$svdProducts);
				//$savedPartsArr
				$this->Flash->success("{$partsSaved} part(s) added for repair id:$id".$finalTable,array('escape' => false));
				$this->request->Session()->delete('view_parts_basket');
				return $this->redirect(array('action' => 'view_repair_parts',$id));
			}elseif(array_key_exists('delete',$this->request->query)){
				$this->partsValidation($id, $repairDate, $currntDate, $repairedAt, $sessionKioskId);
				if($this->request->session()->read('Auth.User.group_id') == KIOSK_USERS || $this->request->session()->read('Auth.User.group_id') == REPAIR_TECHNICIANS){
					if(count($viewRepairParts) == 1){//1 part needs to be retained in case of kiosk/service center
						$this->Flash->error("You are not allowed to delete all parts for a repair!");
						return $this->redirect(array('action' => 'view_repair_parts', $id));
						die;
					}
					
					$qtt2Del = 0;
					foreach($this->request->query['data']['delete'] as $key => $prdctId){
						if((int)$prdctId && $prdctId > 0){
							$qtt2Del++;
						}
					}
					
					if($qtt2Del == count($viewRepairParts)){
						$this->Flash->error("You are not allowed to delete all parts for a repair!");
						return $this->redirect(array('action' => 'view_repair_parts', $id));
						die;
					}
				}
				$deleteCount = 0;
				
				$kiosk_IDArr = $this->request->query['data']['kiosk_ID'];
				foreach($this->request->query['data']['delete'] as $key => $prdctId){
					$productSource = "kiosk_{$kiosk_IDArr[$key]}_products";
					if((int)$prdctId && $prdctId > 0){
						//if($this->MobileRepairPart->deleteAll(array('MobileRepairPart.product_id' => $prdctId, 'MobileRepairPart.mobile_repair_id' => $reprId))){
//						$conn = ConnectionManager::get('default');
//                        $stmt = $conn->execute('SELECT NOW() as created'); 
//                        $currentTimeInfo = $stmt ->fetchAll('assoc');
                        $query = "DELETE FROM `mobile_repair_parts` WHERE `product_id` = '$prdctId' AND `mobile_repair_id` = '$id' AND `kiosk_id` = '$kiosk_IDArr[$key]' LIMIT 1";
						$conn = ConnectionManager::get('default');
                        $stmt = $conn->execute($query); 
							$deleteCount++;
						//}
						$quantity = 1;
						$query1 = "UPDATE `$productSource` SET `quantity` = `quantity` + $quantity WHERE `$productSource`.`id` = $prdctId";
						$conn = ConnectionManager::get('default');
                        $stmt = $conn->execute($query1); 
					}
				}
				
				if($deleteCount > 0){
					$msg = "$deleteCount records have been deleted!";
				}else{
					$msg = "Records could not be deleted!";
				}
				$this->Flash->error($msg);
				return $this->redirect(array('action' => 'view_repair_parts', $id));
			}elseif(array_key_exists('add_repair_parts',$this->request->query)){
				$data_return = $this->partsValidation($id, $repairDate, $currntDate, $repairedAt, $sessionKioskId);
				if(!empty($data_return)){
					return $data_return;
				}
				if($this->request->session()->read('Auth.User.group_id') == KIOSK_USERS || $this->request->session()->read('Auth.User.group_id') == REPAIR_TECHNICIANS){
					$kioskID = $sessionKioskId;
				}
				//$data = $this->request->query['data'];
				//$kioskID = $data['PartsRepaired']['kiosk_id'];
				$this->get_view_repair_parts_categories($kioskID);
				$this->set(compact('repair_id','kioskID'));
				$this->render('replace_parts_page');
			}elseif(array_key_exists('data',$this->request->query)){
				//pr($this->request->query);
				//die;
				$data_return = $this->partsValidation($id, $repairDate, $currntDate, $repairedAt, $sessionKioskId);
				if(!empty($data_return)){
					return $data_return;
				}
				$data = $this->request->query['data'];
				//$kioskProductInfo = $data['PartsRepaired']['original_product'];
				$original_product = $data['PartsRepaired']['original_product'];
				//$kioskId = array_keys($kioskProductInfo);
				//$kioskId = $kioskId[0];
				$primaryId = $data['PartsRepaired']['part'];
				$ksk_Id = $kioskId = $data['PartsRepaired']['kiosk_id'];
				//$original_product = array_values($kioskProductInfo);
				//$original_product = $original_product[0];
				$source = "kiosk_{$kioskId}_products";
				$receiptTable = TableRegistry::get($source,[
																			'table' => $source,
																		]);
				if($kioskId){
					$this->get_product_categories($kioskId);
				}
				
				//below code is for relacing the part on submit of replace button
				if(array_key_exists('replacement',$data['PartsRepaired'])){
					$productTable = "kiosk_{$kioskId}_products";
					$newProduct = array_flip($data['PartsRepaired']['replacement']);
					$replacement = $newProduct['Replace'];
					$MobileRepairPartEntity = $this->MobileRepairParts->get($primaryId);
					$data = array('product_id' => $replacement);
					$MobileRepairPartEntity = $this->MobileRepairParts->patchEntity($MobileRepairPartEntity,$data,['validate' => false]);
					if($this->MobileRepairParts->save($MobileRepairPartEntity)){//please check this
						
						$query = "UPDATE `$productTable` SET `quantity` = `quantity` + 1 WHERE `id` = '$original_product'";
						$conn = ConnectionManager::get('default');
						$stmt = $conn->execute($query);
						
						$query1 = "UPDATE `$productTable` SET `quantity` = `quantity` - 1 WHERE `id` = '$replacement'";
						$conn = ConnectionManager::get('default');
						$stmt = $conn->execute($query1);
					}
					$this->Flash->Success("Parts replaced successfully!!!");
					return $this->redirect(array('action'=>'view_repair_parts', $id));
				}
			}else{
				//$this->get_product_categories();
			}
		}
		$this->set(compact('viewRepairParts','viewOtherRepairParts','productName','repair_id','productsCode','currntDate','ksk_Id','kiosks'));
	}
	
	public function index() {
		//$cookieKioskId = $this->Cookie->read('kiosk_id');
		//$cookieKioskId = $this->request->Session()->read('kiosk_id');
		//$this->set('cookieKioskId',$cookieKioskId);
		//deleting the sessions created during payment if user does not complete the process
		$this->request->Session()->delete('final_parts_basket');
		$this->request->Session()->delete('received_reprd_from_tech_data');
		$this->request->Session()->delete('payment_confirmation');
		
		//$this->MobileRepair->query("DELETE FROM `mobile_repairs` WHERE `id` = '72'");
		$kiosks_query = $this->Kiosks->find('list',
											[
												'keyField' =>'id',
												'valueField' =>'name',
												'conditions' => ['Kiosks.status' => 1],
												'order' => ['Kiosks.name asc']
											]);
		$kiosks_query = $kiosks_query->hydrate(false);
		if(!empty($kiosks_query)){
			$kiosks = $kiosks_query->toArray();
		}else{
			$kiosks = array();
		}
		
		$kiosk_id = $this->request->Session()->read('kiosk_id');
		if(!empty($kiosk_id) && $this->request->session()->read('Auth.User.group_id') == KIOSK_USERS){
			$this->paginate = [
						'conditions' => ['kiosk_id' => $kiosk_id],
						'limit' => ROWS_PER_PAGE,
						'order' => ['MobileRepairs.id DESC'],
                        'contain' => 'Kiosks'
					];	
		}elseif($this->request->session()->read('Auth.User.group_id') == REPAIR_TECHNICIANS){
			$this->paginate = [
						'conditions' => ['MobileRepairs.status' => DISPATCHED_TO_TECHNICIAN],
						'limit' => ROWS_PER_PAGE,
						'order' => ['MobileRepairs.id DESC'],
                        'contain' => 'Kiosks'
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
					$kiosk_id = $managerKiosk;
					$this->paginate = [
							'conditions' => ['kiosk_id IN' => $kiosk_id],
							'limit' => ROWS_PER_PAGE,
							'order' => ['MobileRepairs.id DESC'],
							'contain' => 'Kiosks'
					];
				}else{
					$this->paginate = [
							'conditions' => array('kiosk_id IN' => array(0 => null)),
							'limit' => ROWS_PER_PAGE,
							'order' => ['MobileRepairs.id DESC'],
							'contain' => 'Kiosks'
					];
				}
			}else{
			//for admin
				$this->paginate = [
							//'conditions' => array('MobileRepair.status' => BOOKED),
							'limit' => ROWS_PER_PAGE,
							'order' => ['MobileRepairs.id DESC'],
							'contain' => 'Kiosks'
					];
			}
		}
		$mobileRepairs = $this->paginate("MobileRepairs");
		$repairIDs = array();
		$users_query = $this->Users->find('list',
											['keyField' => 'id',
											 'valueField' => 'username',
											 'conditions' => ['Users.group_id' => 7],
											 ]
									);
		$users_query = $users_query->hydrate(false);
		if(!empty($users_query)){
			$users = $users_query->toArray();
		}else{
			$users = array();
		}
		foreach($mobileRepairs as $mobileRepair){
			$repairIDs[] = $mobileRepair['id'];
		}
		$repairTechniciansIds = array_keys($users);
		if(empty($repairTechniciansIds)){
			$repairTechniciansIds = array('0'=>null);
		}
		$repairLogDetails = $viewRepairParts = array();
		
		if(count($repairIDs)){
			
			$repLog = $this->MobileRepairLogs->find('all', array('conditions' => array(
							'mobile_repair_id IN' => $repairIDs,
							'user_id IN' => $repairTechniciansIds),
							'order' => 'MobileRepairLogs.id DESC')
				)->toArray();
			if(!empty($repLog)){
				foreach($repLog as $k => $val){
					$repairLogDetails[$val->mobile_repair_id][] = $users[$val->user_id];
				}
			}
			
			//foreach($repairIDs as $repairID){
			////getting the very recent user_id from mobile repair log which is of technician for this repair
			//	$repLog_query = $this->MobileRepairLogs->find('all', array('conditions' => array(
			//				'mobile_repair_id' => $repairID,
			//				'user_id IN' => $repairTechniciansIds),
			//				'order' => 'MobileRepairLogs.id DESC')
			//	);
			//	$repLog_query = $repLog_query->hydrate(false);
			//	if(!empty($repLog_query)){
			//		$repLog = $repLog_query->first();
			//	}else{
			//		$repLog = array();
			//	}
			//	if(count($repLog) >= 1){
			//		$repairLogDetails[$repLog['mobile_repair_id']] = $users[$repLog['user_id']];
			//	}
			//}
			
			$viewRepairParts_query = $this->MobileRepairParts->find('all',array('conditions' => array('MobileRepairParts.mobile_repair_id IN' => $repairIDs)));
			$viewRepairParts_query = $viewRepairParts_query->hydrate(false);
			if(!empty($viewRepairParts_query)){
				$viewRepairParts = $viewRepairParts_query->toArray();
			}else{
				$viewRepairParts = array();
			}
		}
		
		 $model_query = $this->MobileModels->find('list', [
                                                        'keyField' => 'id',
                                                        'valueField' => 'model',
														'order'=>'model asc',
                                                         'conditions' =>['MobileModels.status' => 1],
                                                          
                                                    ] 
                                            );
            if(!empty($model_query)){
                 $mobileModels = $model_query->toArray();
            }else{
			$mobileModels = array();
            }
		$this->repair_part_arr($repairIDs);
		$hint = $this->ScreenHint->hint('mobile_repairs','index');
					if(!$hint){
						$hint = "";
					}
		$this->set(compact('hint','viewRepairParts','repairLogDetails','mobileModels','kiosks'));	
		$this->set('mobileRepairs', $mobileRepairs);
		
	}
	
	private function repair_part_arr($repairIDs){
		if(empty($repairIDs)){
			$repairIDs = array(0 => null);
		}
		$repairParts_query = $this->MobileRepairParts->find('all',array(
									'fields' => array('mobile_repair_id'),
									'conditions' => array('MobileRepairParts.mobile_repair_id IN' => $repairIDs),
									'group' => 'mobile_repair_id'
									    )
								   );
		$repairParts_query
							->select(['total' => $repairParts_query->func()->count('MobileRepairParts.product_id')]);
		if(!empty($repairParts_query)){
            $repairParts = $repairParts_query->toArray();
        }else{
            $repairParts = array();
        }
		
        $repairPartArr = array();
		foreach($repairParts as $mobileRepairPart){
			$repairPartArr[$mobileRepairPart['mobile_repair_id']] = $mobileRepairPart['total'];
		}
		$this->set(compact('repairPartArr'));
	}
    
    private function partsValidation($id, $repairDate, $currntDate, $repairedAt, $sessionKioskId){
		if(!isset($id)){$id = '';}
		if(!isset($repairDate)){$repairDate = '';}
		if(!isset($currntDate)){$currntDate = '';}
		if(!isset($repairedAt)){$repairedAt = '';}
		if(!isset($sessionKioskId)){$sessionKioskId = '';}
		if($this->request->Session()->read('Auth.User.group_id') == KIOSK_USERS || $this->request->session()->read('Auth.User.group_id') == REPAIR_TECHNICIANS){
			if($repairDate != $currntDate){
				$this->Flash->success("You can modify the parts for the most recent repair done at your kiosk!");
				return $this->redirect(array('action' => 'view_repair_parts', $id));
				die;
			}
			
			if($repairedAt != $sessionKioskId){//if repair is rebooked and done by other kiosk
				$this->Flash->success("You can modify the parts for the most recent repair done at your kiosk!", ['escape' => false]);
				return $this->redirect(array('action' => 'view_repair_parts', $id));
				die;
			}
		}
	}
    
    private function get_view_repair_parts_categories($kioskID){
		if(!empty($kioskID)){
			$productSource = "kiosk_{$kioskID}_products";
		}
		
        $ProductsTable = TableRegistry::get($productSource,[
                                                            'table' => $productSource,
                                                        ]);
		//$this->Products->setSource($productSource);
		$this->paginate = [
							'conditions' => ['quantity >' => 0],
							'limit' => 20,
							'model' => 'Product',
							'order' => ['product ASC'],
							//'recursive' => -1
                          ];
		//-----------------------------------------
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
		$this->set(compact('categories'));
        $products_query = $this->paginate($ProductsTable);
        if(!empty($products_query)){
            $products = $products_query->toArray();
        }else{
            $products = array();
        }
		$this->set('products', $products);
	}
	
	public function search(){
		//pr($this->request);die;
		$kiosks_query = $this->Kiosks->find('list',
											[
												'keyField' =>'id',
												'valueField' =>'name',
												'conditions' => ['Kiosks.status' => 1],
												'order' => ['Kiosks.name asc']
											]);
		$kiosks_query = $kiosks_query->hydrate(false);
		if(!empty($kiosks_query)){
			$kiosks = $kiosks_query->toArray();
		}else{
			$kiosks = array();
		}
		$searchKW = trim(strtolower($this->request->query['search_kw']));
		$repair_id = $this->request->query['repair_id'];
		$imei = $this->request->query['imei'];
		$conditionArr = array();
		//echo $searchKW;die;
		if(!empty($searchKW)){
			//echo "hi";
			$conditionArr['OR'] = array(
									'LOWER(MobileModels.model) like' => "%$searchKW%",
									'LOWER(MobileRepairs.customer_fname) like' => "%$searchKW%",
									'LOWER(MobileRepairs.customer_email) like' => "%$searchKW%",
									);
		}//die("--");
		if(!empty($imei)){
			$conditionArr[] = "MobileRepairs.imei like '%$imei%'";
		}
		if(!empty($repair_id)){
			$conditionArr["MobileRepairs.id"] = $repair_id;
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
			!empty($this->request->query['end_date'])){
			$conditionArr[] = array(
						"MobileRepairs.modified >" => date('Y-m-d', strtotime($this->request->query['start_date'])),
						"MobileRepairs.modified <" => date('Y-m-d', strtotime($this->request->query['end_date']. ' +1 Days')),			
					       );
		}
		
		if(!empty($this->request->query['MobileRepair']['status'])){
			$status = $this->request->query['MobileRepair']['status'];
			$conditionArr[] = array(
						"MobileRepairs.status" => $status
					       );
			$this->set(compact('status', $status));
		}
		
		if($this->request->session()->read('Auth.User.group_id') == KIOSK_USERS){
			$kiosk_id = $this->request->Session()->read('kiosk_id');
			//pr($kiosk_id);die; 	 	
			$this->paginate = [
					'conditions' => [
							      'MobileRepairs.kiosk_id' => $kiosk_id,
								'OR' => $conditionArr
							],
					'limit' => ROWS_PER_PAGE,
					'order' => ['MobileRepairs.id desc'],
					'contain' => ['Kiosks','MobileModels']
				];
		}else{
			$dataKioskID = '';
			if(array_key_exists('MobileRepair', $this->request->query)){
				$kioskId = $this->request->query['MobileRepair']['kiosk_id'];
				if(array_key_exists('kiosk_id',$this->request->query['MobileRepair']) && !empty($this->request->query['MobileRepair']['kiosk_id'])){
					$conditionArr[] = array('MobileRepairs.kiosk_id' =>$this->request->query['MobileRepair']['kiosk_id']);
					$selectedKiosk = $this->request->query['MobileRepair']['kiosk_id'];
					//$checkKioskCookie = $this->Cookie->read('kiosk_id');
					if((int)$selectedKiosk){
						//$this->Cookie->write('kiosk_id',$selectedKiosk);
						//$this->request->session()->write('kiosk_id',$selectedKiosk);
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
						$managerKiosk = $this->get_kiosk();
						//pr($managerKiosk);
						$conditionArr[] = array('MobileRepairs.kiosk_id IN' =>$managerKiosk);
					}
					//$this->request->session()->write('kiosk_id','');
					//$this->Cookie->write('kiosk_id','');
				}
				$dataKioskID = $this->request->query['MobileRepair']['kiosk_id'];
			}
			//echo $dataKioskID;die;
			$this->set('kioskId', $dataKioskID);
			$this->paginate = [
					'conditions' => $conditionArr,
					'limit' => ROWS_PER_PAGE,
					'order' => ['MobileRepairs.id desc'],
					'contain' => ['Kiosks','MobileModels']
			];
		}
		//pr($this->paginate);die;
		//$cookieKioskId = $this->Cookie->read('kiosk_id');
		//$cookieKioskId = $this->request->Session()->read('kiosk_id');
		//$viewRepairParts = $this->MobileRepairPart->find('all');
		$mobileRepairs = $this->paginate('MobileRepairs');
		//pr($mobileRepairs);die;
		$mobileModels_query = $this->MobileModels->find('list',
															[
																'keyField' => 'id',
																'valueField' => 'model',
																'order'=>'model asc',
																'conditions' => [
																						'MobileModels.status' => 1
																				]
															]
								       );
		$mobileModels_query = $mobileModels_query->hydrate(false);
		if(!empty($mobileModels_query)){
			$mobileModels = $mobileModels_query->toArray();
		}else{
			$mobileModels = array();
		}
		//----------------------------------
		$repairIDs = array();
		foreach($mobileRepairs as $mobileRepair){
			$repairIDs[] = $mobileRepair->id;
		}
		
		$viewRepairParts = array();
		if(count($repairIDs)){
			$viewRepairParts_query = $this->MobileRepairParts->find('all',array('conditions' => array('MobileRepairParts.mobile_repair_id IN' => $repairIDs)));
			$viewRepairParts_query = $viewRepairParts_query->hydrate(false);
			if(!empty($viewRepairParts_query)){
				$viewRepairParts = $viewRepairParts_query->toArray();
			}else{
				$viewRepairParts = array();
			}
		}
		$this->repair_part_arr($repairIDs);
		$hint = $this->ScreenHint->hint('mobile_repairs','multiple_repair_part_report');
					if(!$hint){
						$hint = "";
					}
		$this->set(compact('hint','mobileRepairs','viewRepairParts','mobileModels','kiosks', 'repairPartArr'));
		$this->render('index');
	}
	
	public function all() {
		//$cookieKioskId = $this->Cookie->read('kiosk_id');
		//$cookieKioskId = $this->request->Session()->read('kiosk_id');
		//$this->set('cookieKioskId',$cookieKioskId);
		//deleting the sessions created during payment if user does not complete the process
		$this->request->Session()->delete('final_parts_basket');
		$this->request->Session()->delete('received_reprd_from_tech_data');
		$this->request->Session()->delete('payment_confirmation');
		
		$kiosks_query = $this->Kiosks->find('list',
											[
												'keyField' =>'id',
												'valueField' =>'name',
												'conditions' => ['Kiosks.status' => 1],
												'order' => ['Kiosks.name asc']
											]);
		$kiosks_query = $kiosks_query->hydrate(false);
		if(!empty($kiosks_query)){
			$kiosks = $kiosks_query->toArray();
		}else{
			$kiosks = array();
		}
		$kiosk_id = $this->request->Session()->read('kiosk_id');
		if(!empty($kiosk_id) && $this->request->session()->read('Auth.User.group_id') == KIOSK_USERS){
			$this->paginate = [
						'conditions' => ['kiosk_id' => $kiosk_id],
						'limit' => ROWS_PER_PAGE,
						'order' => ['MobileRepairs.id DESC'],
						'contain' => ['Kiosks','MobileModels']
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
						'conditions' => ['MobileRepairs.kiosk_id IN' => $managerKiosk],
							'limit' => ROWS_PER_PAGE,
							'order' => ['MobileRepairs.id DESC'],
							'contain' => ['Kiosks','MobileModels']
					];			
			   }else{
					$this->paginate = [
							'limit' => ROWS_PER_PAGE,
							'order' => ['MobileRepairs.id DESC'],
							'contain' => ['Kiosks','MobileModels']
					];		
			   }
			}else{
				$this->paginate = [
						'limit' => ROWS_PER_PAGE,
						'order' => ['MobileRepairs.id DESC'],
						'contain' => ['Kiosks','MobileModels']
				];		
			}
			
		}
		$viewRepairParts = array();
		$mobileRepairs = $this->paginate("MobileRepairs");
		//pr($mobileRepairs);
		$repairIDs = array();
		$users_query = $this->Users->find('list',
											[
												'keyField' => 'id',
												'valueField' => 'username',
												'conditions' => ['Users.group_id' => 7],
											]);
		$users_query = $users_query->hydrate(false);
		if(!empty($users_query)){
			$users = $users_query->toArray();
		}else{
			$users = array();
		}
		foreach($mobileRepairs as $mobileRepair){
			$repairIDs[] = $mobileRepair->id;
		}
		if(count($repairIDs)){
			$viewRepairParts_query = $this->MobileRepairParts->find('all',array('conditions' => array('MobileRepairParts.mobile_repair_id IN' => $repairIDs)));
			$viewRepairParts_query = $viewRepairParts_query->hydrate(false);
			if(!empty($viewRepairParts_query)){
				$viewRepairParts = $viewRepairParts_query->toArray();
			}else{
				$viewRepairParts = array();
			}
		}
		$this->repair_part_arr($repairIDs);
		//pr($repairIDs);
		$repairTechniciansIds = array_keys($users);
		if(empty($repairTechniciansIds)){
			$repairTechniciansIds = array(0 => null);
		}
		//pr($repairTechniciansIds);
		if(count($repairIDs)){
			foreach($repairIDs as $repairID){
			//getting the very recent user_id from mobile repair log which is of technician for this repair
				$repLog_query = $this->MobileRepairLogs->find('all', array('conditions' => array(
							'mobile_repair_id' => $repairID,
							'user_id IN' => $repairTechniciansIds),
							'order' => 'MobileRepairLogs.id DESC')
				);
				$repLog_query = $repLog_query->hydrate(false);
				if(!empty($repLog_query)){
					$repLog = $repLog_query->first();
				}else{
					$repLog = array();
				}
				//pr($repLog);
				if(count($repLog) >= 1){
					 $repairLogDetails[$repLog['mobile_repair_id']] = $users[$repLog['user_id']];
				}
			}
		}
		$mobileModels_query = $this->MobileModels->find('list',
															[
																'keyField' => 'id',
																'valueField' => 'model',
																'order'=>'model asc',
																'conditions' => [
																					'MobileModels.status' => 1
																				]
															]
								       );
		$mobileModels_query = $mobileModels_query->hydrate(false);
		if(!empty($mobileModels_query)){
			$mobileModels = $mobileModels_query->toArray();
		}else{
			$mobileModels = array();
		}
		$hint = $this->ScreenHint->hint('mobile_repairs','all');
					if(!$hint){
						$hint = "";
					}
		$this->set(compact('hint','viewRepairParts','repairLogDetails','mobileModels' ));
		$this->set(compact('kiosks'));
		$this->set('mobileRepairs', $this->paginate("MobileRepairs"));
		$this->render('index');
	}
	
	public function booked() {
		$repairIDs = array();
		//$cookieKioskId = $this->Cookie->read('kiosk_id');
		//$cookieKioskId = $this->request->session()->read('kiosk_id');
		//$this->set('cookieKioskId',$cookieKioskId);
		$kiosks_query = $this->Kiosks->find('list',
											[
												'keyField' =>'id',
												'valueField' =>'name',
												'conditions' => ['Kiosks.status' => 1],
												'order' => ['Kiosks.name asc']
											]);
		$kiosks_query = $kiosks_query->hydrate(false);
		if(!empty($kiosks_query)){
			$kiosks = $kiosks_query->toArray();
		}else{
			$kiosks = array();
		}
		$kiosk_id = $this->request->Session()->read('kiosk_id');
		if(!empty($kiosk_id) && $this->request->session()->read('Auth.User.group_id') == KIOSK_USERS){
			$this->paginate = [
						'conditions' => ['MobileRepairs.status' => BOOKED, 'kiosk_id' => $kiosk_id],
						'limit' => ROWS_PER_PAGE,
						'order' => ['MobileRepairs.id DESC']
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
							'conditions' => ['MobileRepairs.status' => BOOKED,
											 'MobileRepairs.kiosk_id IN' => $managerKiosk,
											 ],
							'limit' => ROWS_PER_PAGE,
							'order' => ['MobileRepairs.id DESC']
					];				
				}
			}else{
				$this->paginate = [
						'conditions' => ['MobileRepairs.status' => BOOKED],
						'limit' => ROWS_PER_PAGE,
						'order' => ['MobileRepairs.id DESC']
				];		
			}
			
		}
		$mobileRepairs = $this->paginate("MobileRepairs");
		$this->set(compact('mobileRepairs'));
		foreach($mobileRepairs as $mobileRepair){
			$repairIDs[] = $mobileRepair->id;
		}
		$viewRepairParts = array();
		if(count($repairIDs)){
			$viewRepairParts_query = $this->MobileRepairParts->find('all',array('conditions' => array('MobileRepairParts.mobile_repair_id IN' => $repairIDs)));
			$viewRepairParts_query = $viewRepairParts_query->hydrate(false);
			if(!empty($viewRepairParts_query)){
				$viewRepairParts = $viewRepairParts_query->toArray();
			}else{
				$viewRepairParts = array();
			}
			
		}
		$this->repair_part_arr($repairIDs);
		
		$mobileModels_query = $this->MobileModels->find('list',
															[
																'keyField' => 'id',
																'valueField' => 'model',
																'order'=>'model asc',
																'conditions' => [
																						'MobileModels.status' => 1
																				]
															]
								       );
		$mobileModels_query = $mobileModels_query->hydrate(false);
		if(!empty($mobileModels_query)){
			$mobileModels = $mobileModels_query->toArray();
		}else{
			$mobileModels = array();
		}
		
		$hint = $this->ScreenHint->hint('mobile_repairs','booked');
					if(!$hint){
						$hint = "";
					}
		$this->set(compact('hint','viewRepairParts','mobileModels'));
		$this->set(compact('kiosks'));
		$this->render('index');
	}
	
	public function rebooked() {
		$repairIDs = array();
		//$cookieKioskId = $this->Cookie->read('kiosk_id');
		//$cookieKioskId = $this->request->session()->read('kiosk_id');
		//$this->set('cookieKioskId',$cookieKioskId);
		$kiosks_query = $this->Kiosks->find('list',
											[
												'keyField' =>'id',
												'valueField' =>'name',
												'conditions' => ['Kiosks.status' => 1],
												'order' => ['Kiosks.name asc']
											]);
		$kiosks_query = $kiosks_query->hydrate(false);
		if(!empty($kiosks_query)){
			$kiosks = $kiosks_query->toArray();
		}else{
			$kiosks = array();
		}

		$kiosk_id = $this->request->Session()->read('kiosk_id');
		if(!empty($kiosk_id) && $this->request->session()->read('Auth.User.group_id')  == KIOSK_USERS){
			$this->paginate = array(
						'limit' => ROWS_PER_PAGE,
						'conditions' => array('MobileRepairs.status' => REBOOKED, 'kiosk_id' => $kiosk_id),
						'order' => ['MobileRepairs.id DESC']
				);	
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
					$this->paginate =  array(
							'limit' => ROWS_PER_PAGE,
							'conditions' => array('MobileRepairs.status' => REBOOKED,
												  'MobileRepairs.kiosk_id IN' => $managerKiosk,
												  ),
							'order' => ['MobileRepairs.id DESC']
					);		
				}
			}else{
				$this->paginate =  array(
						'limit' => ROWS_PER_PAGE,
						'conditions' => array('MobileRepairs.status' => REBOOKED),
						'order' => ['MobileRepairs.id DESC']
				);			
			}
			
		}	
		//$this->set('mobileRepairs', $this->Paginator->paginate());
		$mobileRepairs_query = $this->paginate("MobileRepairs");
		$mobileRepairs  = $mobileRepairs_query->toArray();
		$this->set(compact('mobileRepairs'));
		foreach($mobileRepairs as $mobileRepair){
			$repairIDs[] = $mobileRepair['id'];
		}
		$viewRepairParts = array();
		if(count($repairIDs)){
			$viewRepairParts_query = $this->MobileRepairParts->find('all',array('conditions' => array('MobileRepairParts.mobile_repair_id IN' => $repairIDs)));
			$viewRepairParts_query = $viewRepairParts_query->hydrate(false);
			if(!empty($viewRepairParts_query)){
				$viewRepairParts = $viewRepairParts_query->toArray();
			}else{
				$viewRepairParts = array();
			}
		}
		$this->repair_part_arr($repairIDs);
		
		$mobileModels_query = $this->MobileModels->find('list',array(
											'keyField' => 'id',
											'valueField' => 'model',
											'order'=>'model asc',
										    'conditions' => array(
													  'MobileModels.status' => 1
													  )
										    )
								       );
		$mobileModels_query = $mobileModels_query->hydrate(false);
		if(!empty($mobileModels_query)){
			$mobileModels = $mobileModels_query->toArray();
		}else{
			$mobileModels = array();
		}
		$hint = $this->ScreenHint->hint('mobile_repairs','rebooked');
					if(!$hint){
						$hint = "";
					}
		$this->set(compact('hint','viewRepairParts','mobileModels'));
		$this->set(compact('kiosks'));
		$this->render('index');
	}
	public function add($id = '') {
		$mobile_repair_entity = $this->MobileRepairs->newEntity();
		//pr($mobile_repair_entity);die;
		$this->set(compact('mobile_repair_entity',$mobile_repair_entity));
		//---------Rajju's code---------------------
		$customerId = '';
		$customerdetail = array();
		if(!empty( $this->request->query)){
			$customerId = $this->request->query['customerId'] ;
		}
		if(!empty($customerId)){
			$customerdetail_query = $this->RetailCustomers->find('all',array(
											'conditions' => array('RetailCustomers.id'=>$customerId),        
											'fields' => array('fname','lname','email','mobile','city','country','state','zip','address_1','address_2')
														));
			$customerdetail_query = $customerdetail_query->hydrate(false);
			if(!empty($customerdetail_query)){
				$customerdetail = $customerdetail_query->toArray();
			}else{
				$customerdetail = array();
			}
		}
		$this->set(compact('customerdetail'));
		//End: ---------Rajju's code---------------------
		
		$mobileConditions_query = $this->MobileConditions->find('list',
																		[
																			'keyField' => 'id',
																			'valueField' => 'mobile_condition',
																			'conditions' => ['MobileConditions.status' => 1],
																		]
										);
		$mobileConditions_query = $mobileConditions_query->hydrate(false);
		if(!empty($mobileConditions_query)){
			$mobileConditions = $mobileConditions_query->toArray();
		}else{
			$mobileConditions = array();
		}
		$functionConditions_query = $this->FunctionConditions->find('list',
																		[
																			'keyField' => 'id',
																			'valueField' => 'function_condition',
																			'conditions' => ['FunctionConditions.status' => 1],
																		]
															  );
		$functionConditions_query = $functionConditions_query->hydrate(false);
		if(!empty($functionConditions_query)){
			$functionConditions = $functionConditions_query->toArray();
		}else{
			$functionConditions = array();
		}
		$problemTypeOptions_query = $this->ProblemTypes->find('list',
																[
																	'keyField' => 'id',
																	'valueField' => 'problem_type',
																]
														);
		$problemTypeOptions_query = $problemTypeOptions_query->hydrate(false);
		if(!empty($problemTypeOptions_query)){
			$problemTypeOptions = $problemTypeOptions_query->toArray();
		}else{
			$problemTypeOptions = array();
		}
		//capturing the mobile model id and brand ids from mobilerepairprice table with status 1 ie active
		$activeCombinations_query = $this->MobileRepairPrices->find('all',array(
												'conditions' => array('MobileRepairPrices.status' => 1, 'MobileRepairPrices.repair_price > 0'),
												'fields' => array('mobile_model_id','brand_id'),
												'group' => 'MobileRepairPrices.mobile_model_id'
																		));
		$activeCombinations_query = $activeCombinations_query->hydrate(false);
		if(!empty($activeCombinations_query)){
			$activeCombinations = $activeCombinations_query->toArray();
		}else{
			$activeCombinations = array();
		}
		//pr($activeCombinations);
		$activeBrands = $activeModels = array();
		
		foreach($activeCombinations as $key => $activeCombination){
			$activeBrands[$activeCombination['brand_id']] = $activeCombination['brand_id'];
			$activeModels[$activeCombination['mobile_model_id']] = $activeCombination['mobile_model_id'];
		}//pr($activeModels);
		if(empty($activeBrands)){
			$activeBrands = array('0'=>null);
		}
		if(empty($activeModels)){
			$activeModels = array('0'=>null);
		}
		$terms_repair = $this->setting['terms_repair'];
		$errors = $mobilePurchaseDetails = $mobileUnlockPrice = array();
		
		//mobile purchase status 0 => available, 1 => sold , 2 => reserved, 3 => sent for unlock 4 => under repair
		if($id > 0){
			//$id For Internal repair purpose
			$res_query = $this->Settings->find('list',
												[
													'keyField' => 'attribute_name',
													'valueField' => 'attribute_value',
												]
										);
			$res_query = $res_query->hydrate(false);
			if(!empty($res_query)){
				$res = $res_query->toArray();
			}else{
				$res = array();
			}
			$internal_repair_default_cost = $res['internal_repair_default_cost'];
			$this->set(compact('internal_repair_default_cost'));
			//code for internal booking of purchased mobiles
			$mobilePurchaseDetails_query = $this->MobilePurchases->find('all',array(
																			   'conditions' => array('MobilePurchases.id' => $id),
																			   'contain' => array('Kiosks')
																			   ));
			$mobilePurchaseDetails_query = $mobilePurchaseDetails_query->hydrate(false);
			if(!empty($mobilePurchaseDetails_query)){
				$mobilePurchaseDetails = $mobilePurchaseDetails_query->first();
			}else{
				$mobilePurchaseDetails = array();
			}
			$brandID = $mobilePurchaseDetails['brand_id'];
			$modelID = $mobilePurchaseDetails['mobile_model_id'];
			$mobileRepairPrice_query = $this->MobileRepairPrices->find('all',array(
																				'conditions' => array( 
																										'brand_id' => $brandID,
																										'mobile_model_id' => $modelID,
																										'MobileRepairPrices.repair_price > 0'
																					   )
																	   )
																  );
			$mobileRepairPrice_query = $mobileRepairPrice_query->hydrate(false);
			if(!empty($mobileRepairPrice_query)){
				$mobileRepairPrice = $mobileRepairPrice_query->first();
			}else{
				$mobileRepairPrice = array();
			}
			if(empty($mobileRepairPrice)){
				//$this->Session->setFlash("No pricing detail found for brand:$brandID and model:$modelID");
				//return $this->redirect(array('controller'=>'mobile_purchases','action'=>'view',$id));
				//if no pricing detail found corresponding to this mobile repair, then we will use internal brand and model
				//for this repair
				$brandList_query = $this->Brands->find('list',
														[
															//'keyField' => ''
															'valueField' =>'id',
															'order'=>'brand asc',
															'conditions' => ['Brands.brand' => 'Internal Repair/Unlock'],
														]
												 );
				$brandList_query = $brandList_query->hydrate(false);
				if(!empty($brandList_query)){
					$brandList = $brandList_query->toArray();
				}else{
					$brandList = array();
				}
				
				$brandList = array_values($brandList);
                
				$modelList_query = $this->MobileModels->find('list',
													   [
														'valueField' => 'id',
														'order'=>'model asc',
														'conditions' => ['MobileModels.model' => 'Internal Repair/Unlock']
													   ]
										);
				$modelList_query = $modelList_query->hydrate(false);
				if(!empty($modelList_query)){
					$modelList = $modelList_query->toArray();
				}else{
					$modelList = array();
				}
				$modelList = array_values($modelList);
				$mobilePurchaseDetails['brand_id'] = $brandList['0'];
				$mobilePurchaseDetails['mobile_model_id'] = $modelList['0'];
				//pr($mobilePurchaseDetails);die;
				 $brandID = $mobilePurchaseDetails['brand_id'];
				$modelID = $mobilePurchaseDetails['mobile_model_id'];
			}
			//till here;
		}
		if($this->request->session()->read('Auth.User.group_id') != KIOSK_USERS){
			$this->Flash->error('You are not authorized to book repair');
			return $this->redirect(array('action' => 'index'));
		}
		$this->check_if_kiosk();
		
		$kiosks_query = $this->MobileRepairs->Kiosks->find('list',
																[
																	'keyField' => 'id',
																	'valueField' => 'name',
																	'conditions' => ['Kiosks.status' => 1]
																]);
		$kiosks_query = $kiosks_query->hydrate(false);
		if($kiosks_query){
			$kiosks = $kiosks_query->toArray();
		}else{
			$kiosks = array();
		}
		$brands_query = $this->MobileRepairs->Brands->find('list',
															[
																'keyField' =>'id',
																'valueField' => 'brand',
																'order'=>'brand asc',
																'conditions' => ['Brands.status' => 1,'Brands.id IN' => $activeBrands]
															]);
		$brands_query = $brands_query->hydrate(false);
		if($brands_query){
			$brands = $brands_query->toArray();
		}else{
			$brands = array();
		}
		if($id > 0){
			//$id For Internal Repair case
			$problemTypeData_query = $this->MobileRepairPrices->find('all',array(
																			'conditions' => array(
																								'MobileRepairPrices.status' => 1,
																								'MobileRepairPrices.brand_id' => $brandID,
																								'MobileRepairPrices.mobile_model_id' => $modelID,
																								'MobileRepairPrices.repair_price > 0'
																								),
																			'fields' => array('problem_type')
																		));
            //pr($problemTypeData_query);die;
			$problemTypeData_query = $problemTypeData_query->hydrate(false);
			if($problemTypeData_query){
				$problemTypeData = $problemTypeData_query->toArray();
			}else{
				$problemTypeData = array();
			}
            
			if(!empty($problemTypeData)){
				foreach($problemTypeData as $key => $problemTpe){
					$problemArrOptns[$problemTpe['problem_type']] = $problemTypeOptions[$problemTpe['problem_type']];
				}
			}else{
				$problemArrOptns = $problemTypeData;	
			}
            
            
		}else{
			$problemArrOptns = $problemTypeOptions;
		}
		$kiosk_id = $this->request->Session()->read('kiosk_id');
		
		if ($this->request->is('post')) {
			//pr($this->request);die;
			if(array_key_exists("MobileRepair",$this->request->data)){
				$barndId = $this->request->data['MobileRepair']['brand_id'];
				$modelId = $this->request->data['MobileRepair']['mobile_model_id'];
				$result_query = $this->MobileModels->find('all',array('conditions' => array('MobileModels.id' => $modelId,
																					 'MobileModels.brand_id' => $barndId,
																					 )
															   ));
				$result_query = $result_query->hydrate(false);
				if(!empty($result_query)){
					$result = $result_query->first();
				}else{
					$result = array();
				}
				
				if(empty($result)){
					//$this->request->data = $this->request->data["MobileRepair"];
						$this->Flash->error(__('The mobile repair could not be saved. Please Choose Right Combination For Brand And Model.'));
						return $this->redirect(array('action' => 'add'));
				}	
			}
			
			if(array_key_exists('default_cost',$this->request->data)){
				$netCost = $internal_repair_default_cost;
			}else{
				$netCost = floatval($this->request->data['MobileRepair']['net_cost_a']) + floatval($this->request->data['MobileRepair']['net_cost_b']) + floatval($this->request->data['MobileRepair']['net_cost_c']);
			}
			
			$mobile_condition_array = array();
			$mobile_condition = $mobile_condition_remark = $function_condition = '';
			if(array_key_exists('mobile_condition',$this->request->data['MobileRepair'])){
				foreach($this->request->data['MobileRepair']['mobile_condition'] as $mc => $mobCond){
					if($mobCond == 1000 && empty($this->request->data['MobileRepair']['mobile_condition_remark'])){
						$modelID = '';
						//code for showing model and problem type on validation fail
						if(!empty($this->request->data['MobileRepair']['mobile_model_id'])){
							$modelID = $this->request->data['MobileRepair']['mobile_model_id'];
						}
						if(!empty($this->request->data['MobileRepair']['brand_id'])){
							$brandID = $this->request->data['MobileRepair']['brand_id'];
						}elseif(!empty($mobilePurchaseDetails)){
							$brandID = $mobilePurchaseDetails['MobilePurchase']['brand_id'];
						}else{
							foreach($brands as $brandID => $brand)break;
						}
						
						$problemArrOptns = array();
						if(is_numeric($modelID)){
							$problemTypeData_query = $this->MobileRepairPrices->find('all',array('conditions' => array(
																							'MobileRepairPrices.status'=>1,
																							'MobileRepairPrices.brand_id' => $brandID,
																							'MobileRepairPrices.mobile_model_id' => $modelID,
																							'MobileRepairPrices.repair_price > 0'
																					),
																			'fields' => array('problem_type')
																			));
							$problemTypeData_query = $problemTypeData_query->hydrate(false);
							if(!empty($problemTypeData_query)){
								$problemTypeData = $problemTypeData_query->toArray();
							}else{
								$problemTypeData = array();
							}
							
							foreach($problemTypeData as $key => $problemTpe){
							  $problemArrOptns[$problemTpe['problem_type']] = $problemTypeOptions[$problemTpe['problem_type']];
							}
						}else{
							$problemArrOptns = $problemTypeOptions;
						}
						
						$mobileModels_query = $this->MobileRepairs->MobileModels->find('list',array(
																									'keyField' => 'id',
																									'valueField' => 'model',
																							'order'=>'model asc',
																							'conditions' => array(
										      'MobileModels.status' => 1,
										      'MobileModels.brand_id' => $brandID,
										      'MobileModels.id IN' => $activeModels
											)
										)
									);
						$mobileModels_query = $mobileModels_query->hydrate(false);
						if(!empty($mobileModels_query)){
							$mobileModels = $mobileModels_query->toArray();
						}else{
							$mobileModels = array();
						}
						$this->set(compact('problemArrOptns','mobileModels','brands','mobileConditions','functionConditions'));
						$this->Flash->error('Please input mobile condition remarks!');
						return;
					}
				}
				
				$mobile_condition = implode("|",$this->request->data['MobileRepair']['mobile_condition']);
				$mobile_condition_remark = $this->request->data['MobileRepair']['mobile_condition_remark'];
				if(!empty($mobile_condition)){
					$mobile_condition_array = explode("|",$mobile_condition);
				}
			}//End Mobile condition Block
			
			$function_condition_array = array();
			if(array_key_exists('function_condition',$this->request->data['MobileRepair'])){
				$function_condition = implode("|",$this->request->data['MobileRepair']['function_condition']);
				if(!empty($function_condition)){
					$function_condition_array = explode("|",$function_condition);
				}
			}//End Mobile function condition Block
			
			$repair_email_message = $this->setting['repair_email_message'];
			$problemType = $estimatedCost = array();
			if(!empty($this->request->data['MobileRepair']['estimated_cost_a'])){
				$problemType[] = $this->request->data['MobileRepair']['problem_type_a'];
				$estimatedCost[] = $this->request->data['MobileRepair']['estimated_cost_a'];
			}
			
			if(array_key_exists('estimated_cost_b',$this->request->data['MobileRepair']) && !empty($this->request->data['MobileRepair']['estimated_cost_b'])){
				$problemType[] = $this->request->data['MobileRepair']['problem_type_b'];
				$estimatedCost[] = $this->request->data['MobileRepair']['estimated_cost_b'];
			}
			
			if(array_key_exists('estimated_cost_c',$this->request->data['MobileRepair']) && !empty($this->request->data['MobileRepair']['estimated_cost_c'])){
				$problemType[] = $this->request->data['MobileRepair']['problem_type_c'];
				$estimatedCost[] = $this->request->data['MobileRepair']['estimated_cost_c'];
			}
			
			if(count($problemType) == 0 || count($estimatedCost) == 0){
				$modelID = '';
				if(!empty($this->request->data['MobileRepair']['mobile_model_id'])){
					$modelID = $this->request->data['MobileRepair']['mobile_model_id'];
				}
				if(!empty($this->request->data['MobileRepair']['brand_id'])){
					$brandID = $this->request->data['MobileRepair']['brand_id'];
				}elseif(!empty($mobilePurchaseDetails)){
					$brandID = $mobilePurchaseDetails['MobilePurchase']['brand_id'];
				}else{
					foreach($brands as $brandID => $brand)break;
				}
				$problemArrOptns = array();
				if(is_numeric($modelID)){
					$problemTypeData_query = $this->MobileRepairPrices->find('all',array(
																					'conditions' => array(
																										'MobileRepairPrices.status' => 1,
																										'MobileRepairPrices.brand_id' => $brandID,
																										'MobileRepairPrices.mobile_model_id' => $modelID,
																										'MobileRepairPrices.repair_price > 0'
																										),
																					'fields' => array('problem_type')
																	));
					$problemTypeData_query = $problemTypeData_query->hydrate(false);
					if(!empty($problemTypeData_query)){
						$problemTypeData = $problemTypeData_query->toArray();
					}else{
						$problemTypeData = array();
					}
					foreach($problemTypeData as $key => $problemTpe){
					  $problemArrOptns[$problemTpe['problem_type']] = $problemTypeOptions[$problemTpe['problem_type']];
					}
				}else{
					$problemArrOptns = $problemTypeOptions;
				}
				
				$mobileModels_query = $this->MobileRepairs->MobileModels->find('list',array(
																					'keyField' =>'id',
																					'valueField' => 'model',
																					'order'=>'model asc',
																					'conditions' => array(
																								  'MobileModels.status' => 1,
																								  'MobileModels.brand_id' => $brandID,
																								  'MobileModels.id' => $activeModels
																								)
																							)
																		);
				$mobileModels_query = $mobileModels_query->hydrate(false);
				if(!empty($mobileModels_query)){
					$mobileModels_query = $mobileModels_query->toArray();
				}else{
					$mobileModels_query = array();
				}
				$this->set(compact('problemArrOptns','mobileModels','brands'));
				$this->Flash->error("Please input problem type and estimated cost!");
				return; //This needs to be checked
			}
			
			$problemVar = $this->request->data['MobileRepair'];
			//pr($this->request);die;
			$problemData = array(
								'repair_number' => $problemVar['repair_number'],
								'kiosk_id' => $kiosk_id,
								'booked_by' => $this->request->Session()->read('Auth.User.id'),
								'customer_fname' => $problemVar['customer_fname'],
								'customer_lname' => $problemVar['customer_lname'],
								'customer_email' => $problemVar['customer_email'],
								'customer_contact' => $problemVar['customer_contact'],
								'zip' => $problemVar['zip'],
								'customer_address_1' => $problemVar['customer_address_1'],
								'customer_address_2' => $problemVar['customer_address_2'],
								'city' => $problemVar['city'],
								'state' => $problemVar['state'],
								'country' => $problemVar['country'],
								'brand_id' => $problemVar['brand_id'],
								'mobile_condition' => $mobile_condition,
								'mobile_condition_remark' => $mobile_condition_remark,
								'function_condition' => $function_condition,
								'mobile_model_id' => $problemVar['mobile_model_id'],
								'problem_type' => implode('|',$problemType),
								'estimated_cost' => implode('|',$estimatedCost),
								'net_cost' => $netCost,
								'status_freezed' => 1,
								'imei' => $problemVar['imei'],
								'description' => $problemVar['description'],
								'brief_history' => $problemVar['brief_history'],
								'actual_cost' => $problemVar['actual_cost'],
								'received_at' => $problemVar['received_at'],
								'status' => $problemVar['status'],
								'internal_repair' =>  $problemVar['internal_repair'],
								'phone_password' => $problemVar['phone_password'], //added on 23rd Oct
							);
			//	pr($problemData);die;
			if(!empty($customerId)){$problemData['retail_customer_id'] = $customerId;}
			$MobileRepairs_entity = $this->MobileRepairs->newEntity($problemData,['validate' => false]);
			//pr($MobileRepairs_entity);die;
			//$this->MobileRepairs->set($problemData);
			//if (!$this->MobileRepairs->validates()) {
			//	$errors = $this->MobileRepairs->validationErrors;
			//	//pr($this->request->data['MobileRepair']);
			//	//pr($problemData);pr($errors);die;
			//}
			//before saving check if record with same data existing in database.
			//unset($problemData['received_at']);
			$flag = false;
			if($problemData['internal_repair'] == ""){
				$flag = true;
				unset($problemData['internal_repair']);
			}
			
			//$checkDbData_query = $this->MobileRepairs->find('all',array('fields' => array('id'),'conditions' => array('internal_repair' => 'IS NULL')));
			
			$checkDbData_query = $this->MobileRepairs->find('all',array('fields' => array('id'),'conditions' => $problemData));
			if($flag){
				$checkDbData_query
									->where(function ($exp, $q) {
															return $exp->isNull('internal_repair');
														});
				$problemData['internal_repair'] = "";					
			}
			//debug($checkDbData_query->sql());die;
			//pr($checkDbData_query);die;
			$checkDbData_query = $checkDbData_query->hydrate(false);
			if(!empty($checkDbData_query)){
				$checkDbData = $checkDbData_query->first();
			}else{
				$checkDbData = array();
			}
			///pr($checkDbData);die;
			$addToDatabase = true;
			if(count($checkDbData) >= 1){
				 $addToDatabase = false;
			}else{
				;//echo "Record do not exist!";
			}
			//$problemData['received_at'] = $problemVar['received_at'];
			$userId = $this->request->Session()->read('Auth.User.id');
			if(!empty($problemVar['customer_email'])){
				$countDuplicate_query = $this->RetailCustomers->find('all', array('conditions' => array('RetailCustomers.email' => $problemVar['customer_email'])));
				$countDuplicate_query = $countDuplicate_query->hydrate(false);
				if(!empty($countDuplicate_query)){
					$countDuplicate = $countDuplicate_query->first();
				}else{
					$countDuplicate = array();
				}
				
				
				$customer_data = array(
											'kiosk_id' =>  $kiosk_id,
											'fname' => $problemVar['customer_fname'],
											'lname' => $problemVar['customer_lname'],
											'mobile' => $problemVar['customer_contact'],
											'email' => $problemVar['customer_email'],
											'zip' => $problemVar['zip'],
											'address_1' => $problemVar['customer_address_1'],
											'address_2' => $problemVar['customer_address_2'],
											'city' => $problemVar['city'],
											'state' => $problemVar['state'],
											'created_by'=> $userId
									   );
				if(count($countDuplicate) == 0){
					$retailCustomersEntity = $this->RetailCustomers->newEntity();
					$retailCustomersEntity = $this->RetailCustomers->patchEntity($retailCustomersEntity,$customer_data,['validation' => false]);
					$this->RetailCustomers->save($retailCustomersEntity);
				}else{
					$custmor_id =  $countDuplicate["id"];
					$retailCustomersEntity = $this->RetailCustomers->get($custmor_id);
					$retailCustomersEntity = $this->RetailCustomers->patchEntity($retailCustomersEntity,$customer_data,['validate' => false]);
					$this->RetailCustomers->save($retailCustomersEntity);
				}
			}
			
				
			if($addToDatabase){
				//if duplicate repair is not existing in database
				$MobileRepairs_entity = $this->MobileRepairs->patchEntity($MobileRepairs_entity,$problemData,['validate' => false]);
				//pr($MobileRepairs_entity);die;
				if($repairStatus = $this->MobileRepairs->save($MobileRepairs_entity)){
					$rprBookingId = $MobileRepairs_entity->id; 
					if(array_key_exists('add_repair',$this->request->data)){
						$MobilePurchasesEntity = $this->MobilePurchases->get($id);
						$data_to_save = array('status' => '4');
						$MobilePurchasesEntity = $this->MobilePurchases->patchEntity($MobilePurchasesEntity,$data_to_save,['validate' => false]);
						$this->MobilePurchases->save($MobilePurchasesEntity);//status of mobile changed to "sent for repair" in mobile purchase table
						
						$mobileTransferLogData = array(
														'mobile_purchase_reference' => $mobilePurchaseDetails['mobile_purchase_reference'],
														'mobile_purchase_id' => $mobilePurchaseDetails['id'],
														'kiosk_id' => $kiosk_id,
														'network_id' => $mobilePurchaseDetails['network_id'],
														'grade' => $mobilePurchaseDetails['grade'],
														'type' => $mobilePurchaseDetails['type'],
														'receiving_status' => 0,
														'imei' => $mobilePurchaseDetails['imei'],
														'user_id' => $this->request->Session()->read('Auth.User.id'),
														'status' => 5
												);
						$MobileTransferLogsEntity = $this->MobileTransferLogs->newEntity($mobileTransferLogData,['validate' => false]);
						$MobileTransferLogsEntity = $this->MobileTransferLogs->patchEntity($MobileTransferLogsEntity,$mobileTransferLogData,['validate' => false]);
						$this->MobileTransferLogs->save($MobileTransferLogsEntity);
					}
					
					$mobileRepairLogsData = array(
													'kiosk_id' => $kiosk_id,
													'user_id' => $this->request->session()->read('Auth.User.id'),
													'mobile_repair_id' => $MobileRepairs_entity->id,					
													'repair_status' => $problemVar['status']
												);
					
					$mobileRepairLogsEntity = $this->MobileRepairLogs->newEntity($mobileRepairLogsData,['validate' => false]);
					$mobileRepairLogsEntity = $this->MobileRepairLogs->patchEntity($mobileRepairLogsEntity,$mobileRepairLogsData,['validate' => false]);
					
					$this->MobileRepairLogs->save($mobileRepairLogsEntity);
					
					$mobileModels_query = $this->MobileRepairs->MobileModels->find('list',array(
													'keyField' => 'id',
													'valueField' => 'model',
													'order'=>'model asc',
												'conditions' => array(
														  'MobileModels.status' => 1,
														  'MobileModels.id IN' => $activeModels)
												)
										   );
					$mobileModels_query = $mobileModels_query->hydrate(false);
					if(!empty($mobileModels_query)){
						$mobileModels = $mobileModels_query->toArray();
					}else{
						$mobileModels = array();
					}
					//pr($mobileModels);
					$repairBookingData = $this->request['data']['MobileRepair'];
					
					$repairDays = array();
				
					if(!empty($repairBookingData['repair_days_a'])){
						$repairDays[] = $repairBookingData['repair_days_a'];
					}
					if(!empty($repairBookingData['repair_days_b'])){
						$repairDays[] = $repairBookingData['repair_days_b'];
					}
					if(!empty($repairBookingData['repair_days_c'])){
						$repairDays[] = $repairBookingData['repair_days_c'];
					}
					
					$repairStatus = $repairBookingData['status'];
					
					if($this->setting['function_test_notification'] == 'active'){
						if(count($function_condition_array)){
							$funcConditionArr = array();
							foreach($function_condition_array as $fc => $function_condition_id){
								$funcConditionArr[] = $functionConditions[$function_condition_id];
							}
							$funcConditionStr = "<br/><br/>**Phone's function test(at the time of booking): ".implode(", ", $funcConditionArr).".";
						}else{
							$funcConditionStr = '';
						}
					}else{
						$funcConditionStr = '';
					}
						
					if($this->setting['phone_condition_notification'] == 'active'){
						if(count($mobile_condition_array)){
							$phoneConditionArr = array();
							foreach($mobile_condition_array as $mc => $mobile_condition_id){
								//1000 is for others
								if($mobile_condition_id == '1000'){
									$phoneConditionArr[] = $mobile_condition_remark;
								}else{
									$phoneConditionArr[] = $mobileConditions[$mobile_condition_id];
								}
							}
							$phoneConditionStr = "<br/><br/>**Phone condition(at the time of booking): ".implode(", ", $phoneConditionArr).".";
						}else{
							$phoneConditionStr = '';
						}
					}else{
						$phoneConditionStr = '';
					}
						
					$countryOptions = Configure::read('uk_non_uk');
					$this->set(compact('countryOptions'));
					
					$kioskaddress_query = $this->Kiosks->find('all',array(
						'fields' => array('Kiosks.address_1','Kiosks.address_2', 'Kiosks.city','Kiosks.state','Kiosks.country','Kiosks.zip','Kiosks.contact' ),
						'conditions'=> array('Kiosks.id' => $repairBookingData['kiosk_id'])
						)
					);
					$kioskaddress_query = $kioskaddress_query->hydrate(false);
					if(!empty($kioskaddress_query)){
						$kioskaddress = $kioskaddress_query->first();
					}else{
						$kioskaddress = array();
					}
					//for address in the emails
					
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
					$send_by_email = Configure::read('send_by_email');
					$emailSender = Configure::read('EMAIL_SENDER');
					$statement = "Hi ".$repairBookingData['customer_fname']." ".$repairBookingData['customer_lname'].",<br/><br/>
	The repair has been booked for your Mobile Repair Id: ".$rprBookingId."and Mobile Model :".$mobileModels[$repairBookingData['mobile_model_id']]." phone &#40;IMEI: ".$repairBookingData['imei']."&#41;. Your repair is expected to get done within ".(max($repairDays))." working day&#40;s&#41;.".$phoneConditionStr.$funcConditionStr."<br/>
	<br/>Regards,<br/>".$kiosks[$repairBookingData['kiosk_id']].$kioskaddress1.$kioskaddress2.$kioskcity.$kioskstate.$kioskcountry.$kioskzip.$kioskcontact."<br/><br/>".$repair_email_message;
	
					$messageStatement = "Your phone repair id {$rprBookingId} has been booked and will be repaired within ".(max($repairDays))." working days. Please contact the kiosk with any queries t&s ".$this->setting['repair_unlock_terms_link'];
					
					if(!empty($problemVar['customer_contact'])){
						$destination = $problemVar['customer_contact'];
						if(!empty($messageStatement)){
							$this->TextMessage->test_text_message($destination, $messageStatement);
						}
					}
					if(!empty($emailTo)){
						$Email = new Email();
						$Email->config('default');
						$Email->viewVars(array('statement' => $statement));
						$emailTo = $repairBookingData['customer_email'];
						$Email->template('repair_booking_receipt');
						$Email->emailFormat('both');
						$Email->to($emailTo);
						 $Email->transport(TRANSPORT);
						$Email->from([$send_by_email => $emailSender]);
						//$Email->sender("sales@oceanstead.co.uk");
						$Email->subject('Mobile Repair Details');
						$send_mail = 0;
						if($send_mail == 1){
							$Email->send();
						}
					}
					
					$succMsg = 'The mobile repair has been saved.';
					$this->Flash->success(__($succMsg));
					$print_type = $this->setting['print_type'];
					if($print_type == 1){
						return $this->redirect(array('controller' => 'prints','action' => 'repair',$rprBookingId));	
					}
				}else{
					debug($MobileRepairs_entity->errors());die;
					$succMsg = 'The mobile repair could not be saved.';											
				}
				$this->Flash->success(__($succMsg));
				return $this->redirect(array('action' => 'index'));
			}else{
				
				if($this->request->data['MobileRepair']['mobile_model_id'] > 0){
					$problemArrOptns = array();
					$brandID = $this->request->data['MobileRepair']['brand_id'];
					$modelID = $this->request->data['MobileRepair']['mobile_model_id'];
					$problemTypeData_query = $this->MobileRepairPrices->find('all',array('conditions' => array(
																										'MobileRepairPrices.status'=>1,
																										'MobileRepairPrices.brand_id' => $brandID,
																										'MobileRepairPrices.mobile_model_id' => $modelID,
																										'MobileRepairPrices.repair_price > 0'
																									),
																		'fields' => array('problem_type')
																		));
					$problemTypeData_query = $problemTypeData_query->hydrate(false);
					if(!empty($problemTypeData_query)){
						$problemTypeData = $problemTypeData_query->toArray();						
					}else{
						$problemTypeData = array();
					}
					foreach($problemTypeData as $key => $problemTpe){
						$problemArrOptns[$problemTpe['problem_type']] = $problemTypeOptions[$problemTpe['problem_type']];
					}
					$this->set(compact('problemArrOptns'));
				}
				//$lastRepairID = $this->MobileRepairs->getInsertID();//Alias: getLastInsertID
				$kiosk_id = $this->request->Session()->read('kiosk_id');
				//mail('kalyanrajiv@gmail.com', 'bug in saving repair', "repair id: $lastRepairID for kiosk: $kiosk_id");
				$imploded_errors = implode("<br/>",$errors);
				$this->Flash->error("The mobile repair has been allready saved. Please, check repairs. ");//Please check last repair id:$lastRepairID as well.$repairStatus $imploded_errors
			}
		}
		if(!empty($brands)){
		foreach($brands as $brandID => $brand)break;
		if(!empty($mobilePurchaseDetails)){
			$brandID = $mobilePurchaseDetails['brand_id'];
		}
		$mobileModels_query = $this->MobileRepairs->MobileModels->find('list',
																		[
																			'keyField' => 'id',
																			'valueField' => 'model',
																			'order'=>'model asc',
																			'conditions' => [
																							'MobileModels.status' => 1,
																							'MobileModels.brand_id' => $brandID,
																							'MobileModels.id IN' => $activeModels
																						]
																		]
															   );
		$mobileModels_query = $mobileModels_query->hydrate(false);
		}
		if(!empty($mobileModels_query)){
			$mobileModels = $mobileModels_query->toArray();
		}else{
			$mobileModels = array();
		}
		 
		$this->set(compact('brands','mobileModels','kiosks','mobilePurchaseDetails','terms_repair','problemArrOptns','mobileConditions','functionConditions'));
		if(!empty($mobilePurchaseDetails)){
			$this->render('add_repair');
		}
	}
	
	
	public function getModels(){
		//capturing the mobile model id and brand ids from mobilerepairprice table with status 1 ie active
		$activeCombinations_query = $this->MobileRepairPrices->find('all',array('conditions' => array('MobileRepairPrices.status'=>1,
												       'MobileRepairPrices.repair_price > 0'),
							      'fields' => array('mobile_model_id','brand_id'),
							      'group' => 'MobileRepairPrices.mobile_model_id'
							      ));
		$activeCombinations_query = $activeCombinations_query->hydrate(false);
		if(!empty($activeCombinations_query)){
			$activeCombinations = $activeCombinations_query->toArray();
		}else{
			$activeCombinations = array();
		}
		//pr($activeCombinations);
		$activeBrands = array();
		$activeModels = array();
		foreach($activeCombinations as $key => $activeCombination){
			$activeModels[$activeCombination['mobile_model_id']] = $activeCombination['mobile_model_id'];
		}
		// $this->layout = null;
		$brand_id = $this->request->query('id');
		//$this->autoRender = false;
		//$this->request->onlyAllow('ajax');
		$mobileModels_query = $this->MobileRepairs->MobileModels->find('list',
																		[
																			'keyField' => 'id',
																			'valueField' => 'model',
																			'order'=>'model asc',
																			'conditions' => [
																									'MobileModels.status' => 1,
																									'MobileModels.brand_id' => $brand_id,
																									'MobileModels.id IN' => $activeModels
																							]
																		]);
		$mobileModels_query = $mobileModels_query->hydrate(false);
		if(!empty($mobileModels_query)){
			$mobileModels = $mobileModels_query->toArray();
		}else{
			$mobileModels = array();
		}
		$this->set(compact('mobileModels')); 
		
	}
	
	public function getRepairProblems(){
		//Configure::load('common-arrays');
		$problemTypeOptions_query = $this->ProblemTypes->find('list',
																[
																	'keyField' => 'id',
																	'valueField' => 'problem_type',
																	
																]);
		$problemTypeOptions_query = $problemTypeOptions_query->hydrate(false);
		if(!empty($problemTypeOptions_query)){
			$problemTypeOptions = $problemTypeOptions_query->toArray();
		}else{
			$problemTypeOptions = array();
		}
		$brandID = $this->request->query('brandID');
		$modelID = $this->request->query('modelID');
		
		$problemTypeData_query = $this->MobileRepairPrices->find('all',array('conditions' => array(
											'MobileRepairPrices.status'=>1,
											'MobileRepairPrices.brand_id' => $brandID,
											'MobileRepairPrices.mobile_model_id' => $modelID,
											'MobileRepairPrices.repair_price > 0'
												       ),
							      'fields' => array('problem_type')
							      ));
		$problemTypeData_query = $problemTypeData_query->hydrate(false);
		if(!empty($problemTypeData_query)){
			$problemTypeData = $problemTypeData_query->toArray();
		}else{
			$problemTypeData = array();
		}
		$problemTypeArr = array();
		
		foreach($problemTypeData as $key => $problemType){
			$problemTypeArr[$problemType['problem_type']] = $problemTypeOptions[$problemType['problem_type']];
		}
		echo json_encode($problemTypeArr);
		die;
	}
	
	public function getRepairPrice(){
		$problemType = $this->request->query('problemType');
		$brandID = $this->request->query('brandID');
		$modelID = $this->request->query('modelID');
		
		//---------------------------------------
		if(empty($problemType))$problemType = 0;
		if(empty($brandID))$brandID = 0;
		if(empty($modelID))$modelID = 0;
		
		$mobileRepairPrice_query = $this->MobileRepairPrices->find('all',array(
							     'conditions' => array( 
										'brand_id' => $brandID,
										'mobile_model_id' => $modelID,
										'problem_type' => $problemType,
										'MobileRepairPrices.repair_price > 0'
										),
							     'fields' => array(
									       'repair_price',
									       'repair_days',
										   'repair_cost'
									      )
							    )
					       );
		$mobileRepairPrice_query = $mobileRepairPrice_query->hydrate(false);
		if(!empty($mobileRepairPrice_query)){
			$mobileRepairPrice = $mobileRepairPrice_query->first();
		}else{
			$mobileRepairPrice = array();
		}
		
		if(!empty($mobileRepairPrice)){
			$mobileRepairPrice['error'] = 0;
		}else{
			$mobileRepairPrice['error'] = 1;
		}
		echo json_encode($mobileRepairPrice);die;
		//$this->request->allowMethod('post', 'delete'); or $this->request->allowMethod(array('post', 'delete'));
	}
	
	private function edit_form_validation($mobileRepairData = array(), $id){
		//Case : when user is submitting any of edit form or product form in edit()
		$MobileRepairsEntity = $this->MobileRepairs->get($mobileRepairData['id']);
		$data = array(
					  'imei' => $mobileRepairData['imei'],
					  'description' => $mobileRepairData['description'],
					  'phone_password' => $mobileRepairData['phone_password'],
					  
					  );
		$MobileRepairsEntity = $this->MobileRepairs->patchEntity($MobileRepairsEntity,$data,['validate' => false]);
		$this->MobileRepairs->save($MobileRepairsEntity);
		//$result1 = $this->MobileRepair->saveField('imei',$mobileRepairData['imei'], array('validate' => true));
		//$result2 = $this->MobileRepair->saveField('description',$mobileRepairData['description'], array('validate' => true));
		//$result3 = $this->MobileRepair->saveField('phone_password',$mobileRepairData['phone_password'], array('validate' => true));
		$errorArr = array();
		//if(!is_array($result1))$errorArr[] = "IMEI is invalid/empty!; Please reupdate existing value";
		//if(!is_array($result2))$errorArr[] = "Description is required! Please reupdate existing value";
		//if(!is_array($result3))$errorArr[] = "Phone Password is required! Please reupdate existing value";
		$errorStr = implode("<br/>",$errorArr);
		if(!empty($errorStr)){
			$this->Flash->error($errorStr);
			return $this->redirect(array('action' => "edit/{$id}"));
		}else{
			$this->Flash->success("Mobile repair saved for id:$id");
			return $this->redirect(array('action' => "index"));
		}
	}
	
	private function save_repair_parts($repairData = array(), $id = '', $dataPerId = array(), $mobilePurchaseData = array(), $activeModels = array(), $kioskAddressArr = array(), $mobConditionArr = array(), $repair_email_message = ''){//saving repair parts through this function, using in edit()
		//pr($_SESSION);die;
		//pr($this->request);die;
		$kiosks_query = $this->MobileRepairs->Kiosks->find('list', array(
																   'keyField' => 'id',
																   'valueField' => 'name',
																	'conditions' => array('Kiosks.status' => 1),
														));
		$kiosks_query = $kiosks_query->hydrate(false);
		if(!empty($kiosks_query)){
			$kiosks = $kiosks_query->toArray();
		}else{
			$kiosks = array();
		}
		//getting phone and function condition for email purpose
		$funcConditionStr = $mobConditionArr['funcConditionStr'];
		$phoneConditionStr = $mobConditionArr['phoneConditionStr'];
			
		//for kiosk address in emails
		$kioskaddress1 = $kioskAddressArr['kioskaddress1'];
		$kioskaddress2 = $kioskAddressArr['kioskaddress2'];
		$kioskcity = $kioskAddressArr['kioskcity'];
		$kioskstate = $kioskAddressArr['kioskstate'];
		$kioskcountry = $kioskAddressArr['kioskcountry'];
		$kioskzip = $kioskAddressArr['kioskzip'];
		$kioskcontact = $kioskAddressArr['kioskcontact'];
		
		$kiosk_id = $this->request->Session()->read('kiosk_id');
		
		//case 1: No item in session + we have items in session and needs to be merged
		$currentPage = 0;
		if(array_key_exists('current_page',$repairData)){
			$currentPage = $repairData['current_page'];
		}
		$session_parts_basket = $this->request->Session()->read('parts_basket');
		$parts_basket['repair_id'] = $id;
		$parts_basket_temp = $sum_total_temp = array();
		if(
		 array_key_exists('PartsRepaired',$repairData) &&
		 array_key_exists('item',$repairData['PartsRepaired'])
		){
			$partsRepaired = $repairData['PartsRepaired']['item'];    
			
			$productCount = 0;
			foreach($partsRepaired as $key => $productID){
				//pr($productID);die;
				if((int)$productID){      
					$productCount++;
					$parts_basket[$productID] = $productID;
					$parts_basket_temp[$productID] = $productID;
				}     
			}
		}
		$sum_total = $this->add_arrays(array($parts_basket,$session_parts_basket));
		$sum_total_temp = $this->add_arrays(array($parts_basket_temp, $session_parts_basket));
		if(count($sum_total_temp) <= 0){
			
			if($currentPage){
				$this->Flash->error('No part was added to the basket!');
				return $this->redirect(array('action' => "edit/{$id}/page:$currentPage"));
			}
		}else{
			
			if($this->request->session()->read('Auth.User.group_id') == KIOSK_USERS && ($dataPerId['internal_repair'] !=1 || $dataPerId['internal_repair'] == NULL || empty($dataPerId['internal_repair']))){
				//checking if atleast 1 payment entry exists in payment table for this repair id
				$exempted = $this->checkIfExempted($id, $dataPerId['status_rebooked']);
				//redirecting to the payment page from here, using the session final_parts_basket from below
				//exempting the entries of rebook for which payment was never taken
				if($exempted == 0){
					$costArray = explode('|',$dataPerId['estimated_cost']);
					$totalCostValue = 0;
					foreach($costArray as $ca => $costValue){
						$totalCostValue+= $costValue;
					}
					$sum_total['total_cost'] = $totalCostValue;
					$this->request->Session()->write('final_parts_basket',$sum_total);
					return $this->redirect(array('controller'=>'MobileRepairs','action'=>'repair_payment'));
				}
			}
		}

		//if it is kiosk user, the request will only reach here if $exempted == 1, means payment has already been made for the repair, so no sale should be generated in case of kiosk ie. only kiosk with the case of rebook and internal repair will go below from here (so the $amount that we are using below for mobilerepairsale will remain 0), otherwise they will get redirected to return $this->redirect('repair_payment'); as mentioned above
		
		$partsSaved = 0;
		$savedPartsArr = array();
		foreach($sum_total as $key => $productID){
			if($key == 'repair_id')continue;
			//$kskId = $dataPerId['MobileRepair']['kiosk_id'];
			$mobileRepairPartData = array(
							'user_id' =>$this->request->session()->read('Auth.User.id'), //added on Nov 22, 2016
							'mobile_repair_id' => $id,
							'product_id' => $productID,
							'kiosk_id' => $kiosk_id
						      );
			$MobileRepairPartsEntity = $this->MobileRepairParts->newEntity($mobileRepairPartData,['validate' => false]);
			$MobileRepairPartsEntity = $this->MobileRepairParts->patchEntity($MobileRepairPartsEntity,$mobileRepairPartData,['validate' => false]);
			// pr($MobileRepairPartsEntity);
           
            if($this->MobileRepairParts->save($MobileRepairPartsEntity)){
              
				$partsSaved++;
				$savedPartsArr[$productID] = $productID;
			} /*else{
                debug($MobileRepairPartsEntity->errors());die;
            }*/
		}
		
		if(!empty($kiosk_id) && $this->request->session()->read('Auth.User.group_id') == KIOSK_USERS){
			$redirectAction = 'all';
		}else{
			$redirectAction = 'dispatched';
		}
		
		if($partsSaved){
			//$this->MobileRepair->id = $id;
			if(!empty($kiosk_id) && $this->request->session()->read('Auth.User.group_id') == KIOSK_USERS){					
				//Note: if parts added by kiosk user, it is assumed product will be delivered by kiosk user 
				$status = DELIVERED_REPAIRED_BY_KIOSK;
			}else{
				//Note: if parts added by technician for repair, repair will be delivered to kiosk user
				$status = DISPATCHED_2_KIOSK_REPAIRED;						
			}
			$MobileRepairsEntity = $this->MobileRepairs->get($id);
			$data = array('status' => $status);
			$MobileRepairsEntity = $this->MobileRepairs->patchEntity($MobileRepairsEntity,$data,['validate' => false]);
			if($this->MobileRepairs->save($MobileRepairsEntity,['validate' => false])){
				$this->request->Session()->delete('parts_basket');
				if(!empty($mobilePurchaseData) && $status == DELIVERED_REPAIRED_BY_KIOSK){
					$purchaseId = $mobilePurchaseData['id'];
					$mobileTransferLogData = array(
						'mobile_purchase_reference' => $mobilePurchaseData['mobile_purchase_reference'],
						'mobile_purchase_id' => $mobilePurchaseData['id'],
						'kiosk_id' => $mobilePurchaseData['kiosk_id'],
						'network_id' => $mobilePurchaseData['network_id'],
						'grade' => $mobilePurchaseData['grade'],
						'type' => $mobilePurchaseData['type'],
						'receiving_status' => 0,
						'imei' => $mobilePurchaseData['imei'],
						'user_id' => $this->request->Session()->read('Auth.User.id'),
						'status' => 0
					);
					
					$MobileTransferLogsEntity = $this->MobileTransferLogs->newEntity($mobileTransferLogData,['validate' => false]);
					$MobileTransferLogsEntity = $this->MobileTransferLogs->patchEntity($MobileTransferLogsEntity,$mobileTransferLogData,['validate' => false]);
					$this->MobileTransferLogs->save($MobileTransferLogsEntity);
					
					$MobilePurchaseEntity = $this->MobilePurchases->get($purchaseId);
					$data = array('status' => 0);
					$MobilePurchaseEntity = $this->MobilePurchases->patchEntity($MobilePurchaseEntity,$data,['validate' => false]);
					$this->MobilePurchases->save($MobilePurchaseEntity);
				}
				
				$mobileRepairLogsData = array(
								'kiosk_id' => $dataPerId['kiosk_id'],
								'user_id' => $this->request->Session()->read('Auth.User.id'),
								'mobile_repair_id' => $MobileRepairsEntity->id,
								'repair_status' => $status
							);
				
				if($status == DISPATCHED_2_KIOSK_REPAIRED){
					$service_center_id = $this->request->Session()->read('kiosk_id');
					$mobileRepairLogsData = array(
								'kiosk_id' => $dataPerId['kiosk_id'],
								'user_id' => $this->request->Session()->read('Auth.User.id'),
								'mobile_repair_id' => $MobileRepairsEntity->id,
								'service_center_id' => $service_center_id,
								'repair_status' => $status
							);
				}
				$MobileRepairLogsEntity = $this->MobileRepairLogs->newEntity($mobileRepairLogsData,['validate' => false]);
				$MobileRepairLogsEntity = $this->MobileRepairLogs->patchEntity($MobileRepairLogsEntity,$mobileRepairLogsData,['validate' => false]);
				$this->MobileRepairLogs->save($MobileRepairLogsEntity);				
				
				if(empty($kiosk_id)){$productSource = "products";}else{$productSource = "kiosk_{$kiosk_id}_products";}
				$productTable = TableRegistry::get($productSource,[
																			'table' => $productSource,
																		]);
				
				//on save decrease inventory for each item.
				foreach($sum_total as $key => $productID){
					if($key == 'repair_id')continue;
					//$this->Product->clear();
					//$this->Product->id = $productID;
					$quantity = 1;
					 $query = "UPDATE `$productSource` SET `quantity` = `quantity` - $quantity WHERE `$productSource`.`id` = $productID";
                   	$conn = ConnectionManager::get('default');
					$stmt = $conn->execute($query); 
				}
				//$this->Session->delete('parts_basket');
				if($partsSaved && $status == DELIVERED_REPAIRED_BY_KIOSK){
					//pr($this->request);die;
					$dateRepaired = date('Y-m-d h:i:s A');
					$estimatedCostData_query = $this->MobileRepairs->find('all',array('fields' => array('MobileRepairs.estimated_cost','MobileRepairs.status_rebooked'),'conditions'=> array('MobileRepairs.id' => $MobileRepairsEntity->id)));
					
					$estimatedCostData_query = $estimatedCostData_query->hydrate(false);
					if(!empty($estimatedCostData_query)){
						$estimatedCostData = $estimatedCostData_query->first();
					}else{
						$estimatedCostData = array();
					}
					$estimatedCost = $estimatedCostData['estimated_cost'];
					$statusRebooked = $estimatedCostData['status_rebooked'];							
					$totalCost = array_sum(explode("|",$estimatedCost));
					$rebooked = false;
					if(!empty($statusRebooked) || $dataPerId['internal_repair'] == 1){
						//xyz case delivered repaired by kiosk
						$amount = 0;
						$rebooked = true;
					}else{
						$amount = $totalCost;
					}
					
					$mobileRepairSalesData = array(
						'kiosk_id' => $dataPerId['kiosk_id'],
						'retail_customer_id' => $dataPerId['retail_customer_id'],
						'mobile_repair_id' => $MobileRepairsEntity->id,
						'sold_by' => $this->request->Session()->read('Auth.User.id'),
						'sold_on' => $dateRepaired,
						'refund_by' => '',
						'amount' => $amount,
						'refund_amount' => '',
						'refund_status' => 0,
						'refund_on' => '',
						'refund_remarks' => '',
						'repair_status' => $status
					);
					if($rebooked){$mobileRepairSalesData['rebooked_status'] = 1;}
					//code for bypassing save in case of admin edit
					if($this->request->session()->read('Auth.User.group_id') == KIOSK_USERS){
						
						//check if entry for sale data already existing in the table:mobile_repair_sales for first time booked case
						//For first time we would have amount for sure in mobile_repair_sales and for rebook case amount would be 0
						if($amount > 0){
							$rand = rand(500,10000);
							//mail('kalyanrajiv@gmail.com', "Line Controller MR #3376- $rand","");
							//Case: New Repair
							//checking if any sale existing for repair id with some amount
							$saleExistRS_query = $this->MobileRepairSales->find('all',array(
													'conditions' => array(
																		  'MobileRepairSales.mobile_repair_id' => $MobileRepairsEntity->id,
																		  'MobileRepairSales.amount <>' => 0.00,
																		  )
													));
							$saleExistRS_query = $saleExistRS_query->hydrate(false);
							if(!empty($saleExistRS_query)){
								$saleExistRS = $saleExistRS_query->toArray();
							}else{
								$saleExistRS = array();
							}
							if(count($saleExistRS) == 0){
								$MobileRepairSaleEntity = $this->MobileRepairSales->newEntity($mobileRepairSalesData,['validate' => false]);
								$MobileRepairSaleEntity = $this->MobileRepairSales->patchEntity($MobileRepairSaleEntity,$mobileRepairSalesData,['validate' => false]);
								$this->MobileRepairSales->save($MobileRepairSaleEntity);
							}
						}else{
							//Case: Rebook
							$MobileRepairSaleEntity = $this->MobileRepairSales->newEntity($mobileRepairSalesData,['validate' => false]);
							$MobileRepairSaleEntity = $this->MobileRepairSales->patchEntity($MobileRepairSaleEntity,$mobileRepairSalesData,['validate' => false]);
							$this->MobileRepairSales->save($MobileRepairSaleEntity);
						}
					}
				}
				//die;
				$productNameList_query = $productTable->find('all',array('fields'=> array('id','product','product_code'),'conditions' => array('id IN' => $savedPartsArr)));
				$productNameList_query = $productNameList_query->hydrate(false);
				if(!empty($productNameList_query)){
					$productNameList = $productNameList_query->toArray();
				}else{
					$productNameList = array();
				}
				$productNameDetail = array();
				foreach($productNameList as $pn => $productNameDtl){
					$productNameDetail[$productNameDtl['id']] = $productNameDtl;
				}
				$svdProducts = array();
				$finalRow = '';
				foreach($savedPartsArr as $sp => $savedPart){
					$finalRow.= "<tr>".
						"<td>".
						$productNameDetail[$savedPart]['product_code'].
						"</td>".
						"<td>".
						$productNameDetail[$savedPart]['product'].
						"</td>".
					"</tr>";
					//$svdProducts[] = $productNameList[$savedPart];
				}
				$finalTable = '';
				if(!empty($finalRow)){
					$finalTable = "<table>".
						"<tr>".
							"<th>Product Code</th>".
							"<th>Product</th>".
							"</tr>".$finalRow.
							"</table>";
				}
				$productNameStr = implode(', ',$svdProducts);
				if($this->request->session()->read('Auth.User.group_id') == KIOSK_USERS){
					//$savedPartsArr
					$this->Flash->success("{$partsSaved} part(s) added for repair id:$id and delivered".$finalTable,array('escape' => false));	//Part(s): $productNameStr
					
				//sending email in case of delivered repaired by kiosk
				if(empty($activeModels)){
					$activeModels = array(0=>null);
				}
		$mobileModels_query = $this->MobileRepairs->MobileModels->find('list',array(
									    'fields' => array('id', 'model'),
										'order'=>'model asc',
									    'conditions' => array(
												  'MobileModels.status' => 1,
												  'MobileModels.id IN' => $activeModels)
									    )
							       );
		$mobileModels_query = $mobileModels_query->hydrate(false);
		if(!empty($mobileModels_query)){
			$mobileModels = $mobileModels_query->toArray();
		}else{
			$mobileModels = array();
		}
		$send_by_email = Configure::read('send_by_email');
		$emailSender = Configure::read('EMAIL_SENDER');
		$statement = "Hi ".$dataPerId['customer_fname']." ".$dataPerId['customer_lname'].",<br/><br/>
	Your ".$mobileModels[$dataPerId['mobile_model_id']]." phone &#40;IMEI: ".$dataPerId['imei']."&#41; has been succesfully repaired and collected by you.<br/><br/>
	Thank you for using our repair services.".$phoneConditionStr.$funcConditionStr."<br/><br/>
	Regards,<br/>".$kiosks[$dataPerId['kiosk_id']].$kioskaddress1.$kioskaddress2.$kioskcity.$kioskstate.$kioskcountry.$kioskzip.$kioskcontact."<br/><br/>".$repair_email_message;
	
	$messageStatement = "Thank you for collecting your repaired phone id ".$dataPerId['id'].". Thank you for using our service. t&s ".$this->setting['repair_unlock_terms_link'];
	
					//code for sending text message
					if(!empty($dataPerId['customer_contact'])){
						$destination = $dataPerId['customer_contact'];
						if(!empty($messageStatement)){
							$this->TextMessage->test_text_message($destination, $messageStatement);
						}
					}
					if(!empty($emailTo)){
						$Email = new Email();
						$Email->config('default');
						$Email->viewVars(array('statement' => $statement));
						//$Email->config(array('from' => 'me@example.org', 'transport' => 'MyCustom'));
						//$Email = new CakeEmail(array('from' => 'me@example.org', 'transport' => 'MyCustom'));
						$emailTo = $dataPerId['customer_email'];
						$Email->template('repair_booking_receipt');
						$Email->emailFormat('both');
						$Email->to($emailTo);
						$Email->transport(TRANSPORT);
						$Email->from([$send_by_email => $emailSender]);
						//$Email->sender("sales@oceanstead.co.uk");
						$Email->subject('Mobile Repair Details');
						$Email->send();
					}
					
				}else{
					$this->Flash->error("{$partsSaved} part(s) added for repair id:$id and dispatched".$finalTable,array('escape' => false));	
				}
				
				$print_type = $this->setting['print_type'];
				if($print_type == 1){
					if($this->request->session()->read('Auth.User.group_id') == KIOSK_USERS){
						return $this->redirect(array('controller' => 'prints','action' => 'repair',$id));		
					}else{
						return $this->redirect(array('action' => $redirectAction));	
					}
					
				}else{
					return $this->redirect(array('action' => $redirectAction));
				}
			}else{
                //debug($MobileRepairsEntity->errors());
                //pr($MobileRepairsEntity);die;
				$this->request->Session()->delete('parts_basket');
				$this->Flash->error("Failed to add part(s) repair id:$id");
				return $this->redirect(array('action' => $redirectAction));
			}
		}else{
            echo "fff";
            //debug($MobileRepairsEntity->errors());
            //pr($MobileRepairsEntity);die;
			$this->request->Session()->delete('parts_basket');
			$this->Flash->error("Failed to add part(s) repair id:$id");
			return $this->redirect(array('action' => $redirectAction));
		}
	}
	
	private function checkIfExempted($id = '', $statusRebooked = ''){
		$checkIfPayment_query = $this->RepairPayments->find('all',array(
				'conditions' => array('RepairPayments.mobile_repair_id'=>$id)
					)
				  );
		$checkIfPayment = $checkIfPayment_query->count();
		//echo $statusRebooked;die;
		//pr($checkIfPayment);die;
		$exempted = 0;
		if($statusRebooked == 1){
			if($checkIfPayment == 0){
				$exempted = 0;
			}else{
				$exempted = 1;
			}
		}
		return $exempted;
	}
	
	private function add_to_session_edit($id = '', $repairData = array()){//Case: Adding new products to repair in edit() for kiosk user and service technican
	//pr($repairData);die;
		$session_parts_basket = $this->request->Session()->read('parts_basket');
		$parts_basket['repair_id'] = $id;
		if(
		 array_key_exists('PartsRepaired',$repairData) &&
		 array_key_exists('item',$repairData['PartsRepaired'])
		){
			$partsRepaired = $repairData['PartsRepaired']['item'];
			$productCount = 0;
			foreach($partsRepaired as $key => $productID){
				//pr($productID);die;
				if((int)$productID){      
				 $productCount++;
				 $parts_basket[$productID] = $productID;     
				}     
			}
		}
		//pr($parts_basket);die;
		$sum_total = $this->add_arrays(array($parts_basket,$session_parts_basket));
		//pr($sum_total);die;
		if(isset($productCount) && ($productCount || count($sum_total) > 1)){
			$this->request->Session()->write('parts_basket',$sum_total);
			$sessionKeys = array_keys($this->request->Session()->read('parts_basket'));
			array_splice($sessionKeys,0,1,array());//removing the repair_id from the array to find the product names
			if(empty($sessionKeys)){
				$sessionKeys = array(0=>null);
			}
			$productSesNameList_query = $this->Products->find('all',array('fields'=> array('id','product_code','product'),'conditions' => array('Products.id IN' => $sessionKeys)));
			//pr($productSesNameList_query );die;
			$productSesNameList_query = $productSesNameList_query->hydrate(false);
			if(!empty($productSesNameList_query)){
				$productSesNameList = $productSesNameList_query->toArray();
			}else{
				$productSesNameList = array();
			}
			
			$savedPartsName = array();
			$productSesNameDetail = array();
			foreach($productSesNameList as $ps => $productSes){
				$productSesNameDetail[$productSes['id']] = $productSes;
			}
			//pr($productSesNameDetail);die;
			$sessionRow = '';
			foreach($sessionKeys as $sp => $sessionPart){
				$sessionRow.= "<tr>".
					"<td>".
					$productSesNameDetail[$sessionPart]['product_code'].
					"</td>".
					"<td>".
					$productSesNameDetail[$sessionPart]['product'].
					"</td>".
				"</tr>";
				//$svdProducts[] = $productNameList[$savedPart];
			}
			$sessionTable = '';
			if(!empty($sessionRow)){
				$sessionTable = "<table>".
					"<tr>".
						"<th>Product Code</th>".
						"<th>Product</th>".
						"</tr>".$sessionRow.
						"</table>";
			}
			
			$totalProductCount = count($sum_total)-1;
			
			$msg = "You have added {$totalProductCount} product(s) to repair for repair id:{$id}<br/>Total Product count for repair:{$totalProductCount}".$sessionTable;
		}else{
			$msg = 'No part was added to the basket!';
		}
		
		return $msg;
	}
	
	private function backstock($id = ''){
		$rebook_mobilerepair_query = $this->MobileRepairs->find('all',array( 
				  'conditions' => array('MobileRepairs.id' => $id 
				   )));
		$rebook_mobilerepair_query = $rebook_mobilerepair_query->hydrate(false);
		if($rebook_mobilerepair_query){
			$rebook_mobilerepair = $rebook_mobilerepair_query->first();
		}else{
			$rebook_mobilerepair = array();
		}
		$rebook_mobilerepair_id = $rebook_mobilerepair['status_rebooked'];
		$viewRepairParts_query = $this->MobileRepairParts->find('all',array(
			  'conditions' => array('MobileRepairParts.mobile_repair_id' => $id),
			  'order' => 'MobileRepairParts.id desc'
			  )
			 );
		$viewRepairParts_query = $viewRepairParts_query->hydrate(false);
		if(!empty($viewRepairParts_query)){
			$viewRepairParts = $viewRepairParts_query->toArray();
		}else{
			$viewRepairParts = array();
		}
		$productName_query = $this->Products->find('list',[
															'keyField' => 'id',
															'valueField' => 'product',
														  ]
												   );
		$productName_query = $productName_query->hydrate(false);
		if(!empty($productName_query)){
			$productName = $productName_query->toArray();
		}else{
			$productName = array();
		}
		$productcode_query = $this->Products->find('list',[
															'keyField' => 'id',
															'valueField' => 'product_code',
															]);
		$productcode_query = $productcode_query->hydrate(false);
		if(!empty($productcode_query)){
			$productcode = $productcode_query->toArray();
		}else{
			$productcode = array();
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
		//pr($viewRepairParts);
		$kiosks_query = $this->Kiosks->find('list',[
													'keyField' =>'id',
													'valueField' => 'name'
												   ]
											);
		$kiosks_query = $kiosks_query->hydrate(false);
		if(!empty($kiosks_query)){
			$kiosks = $kiosks_query->toArray();
		}else{
			$kiosks = array();
		}
		$this->set(compact('viewRepairParts','productName','users','kiosks','productcode','rebook_mobilerepair_id'));
	}
	
	private function get_product_categories($kioskID = 0){
		$kiosk_id = $this->request->Session()->read('kiosk_id');
		if(empty($kiosk_id)){
			$productSource = "products";
		}else{
			$productSource = "kiosk_{$kiosk_id}_products";
		}
		
		if(!empty($kioskID)){
			$productSource = "kiosk_{$kioskID}_products";
		}
		
		$productTable = TableRegistry::get($productSource,[
															'table' => $productSource,
														]);
		//$receiptTable = TableRegistry::get($receiptTable_source,[
		//													'table' => $receiptTable_source,
		//												]);
		$this->paginate = [
							'conditions' => ['quantity >' => 0],
							'limit' => 20,
							'model' => 'Product',
							'order' => ['product' => 'ASC']
						];
		//-----------------------------------------
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
		$this->set(compact('categories'));
		$products_query = $this->paginate($productTable);
		$products = $products_query->toArray();
		$this->set('products', $products);
	}
	private function status_repair($id = null){
		return $repairStatus = array(
				      REBOOKED => 'The mobile repair has been rebooked',
				      DISPATCHED_TO_TECHNICIAN => 'The mobile repair dispatch request has been submitted to technician',
				      RECEIVED_REPAIRED_FROM_TECHNICIAN => 'The mobile has been received repaired',
				      RECEIVED_UNREPAIRED_FROM_TECHNICIAN => 'The mobile has been received unrepaired',
				      DELIVERED_REPAIRED_BY_KIOSK => 'The mobile has been delivered repaired by kiosk',
				      DELIVERED_UNREPAIRED_BY_KIOSK => 'The mobile has been delivered unrepaired by kiosk',
				      DELIVERED_REPAIRED_BY_TECHNICIAN => 'The mobile has been delivered repaired by technician',
				      DELIVERED_UNREPAIRED_BY_TECHNICIAN => 'The mobile has been delivered unrepaired by technician',
				      RECEIVED_BY_TECHNICIAN => 'The phone has been successfully received',
				      DISPATCHED_2_KIOSK_REPAIRED => 'The phone has been dispatched to Kiosk repaired',
				      DISPATCHED_2_KIOSK_UNREPAIRED => 'The phone has been dispatched to Kiosk unrepaired'
				      );
	}
	
	private function add_arrays($arrays = array()){
            $allValues = array();
            foreach($arrays as $sngArr){
		if(is_array($sngArr)){
			foreach($sngArr as $key => $value){
			    if(!array_key_exists($key,$allValues))
			    $allValues[$key] = $value;
			}			
		}
            }
            return $allValues;
    }
	
	public function searchProduct($id = null){
		$searchKW = '';
		if(array_key_exists('search_kw',$this->request->data)){
			$searchKW = $this->request->data['search_kw'];
		}
		
		if (!$this->MobileRepairs->exists($id)) {
			throw new NotFoundException(__('Invalid mobile repair'));
		}
		
		$conditionArr = array();
		//----------------------
		if(!empty($searchKW)){
			$conditionArr['OR']['LOWER(product) like '] =  strtolower("%$searchKW%");
			$conditionArr['OR']['LOWER(product_code) like '] =  strtolower("%$searchKW%");
		}
		if(array_key_exists('category',$this->request->data)){
			$conditionArr['category_id IN'] = $this->request->data['category'];
			//$conditionArr['OR']['category_id'] = $this->request->data['category'];
		}
		$this->paginate = array(
						'conditions' => array($conditionArr,'quantity > 0'),
						'limit' => 20
					);
		//--------------------------------------------------------------
		$kiosk_id = $this->request->Session()->read('kiosk_id');
		if(empty($kiosk_id)){
			$productSource = "products";
		}else{
			$productSource = "kiosk_{$kiosk_id}_products";
		}
		
		$productTable = TableRegistry::get($productSource,[
																	'table' => $productSource,
																]);
		//echo $problemVar['repair_number'];
		
		//-----------------------------------------
		$categories = $this->Categories->find('all',array(
								'fields' => array('id', 'category','id_name_path'),
								'conditions' => array('Categories.status' => 1),
								'order' => 'Categories.category asc',
								'recursive' => -1
								));
		$categories = $this->CustomOptions->category_options($categories,true);
		$this->set(compact('categories'));
		//$this->request['data']['MobileRepairs']['id'] = $id;
		$products = $this->paginate($productTable);
		$this->set('products', $products);
		//--------------------------------------------------------------
		$result_data_query = $this->MobileRepairs->find('all',array('conditions' => array('id' => $id)));
		$result_data_query = $result_data_query->hydrate(false);
		if(!empty($result_data_query)){
			$result_data = $result_data_query->first();	
		}else{
			$result_data = array();
		}
		
		$status_rebooked = $result_data['status_rebooked'];
		//$this->get_product_categories();
		$this->set(compact('status_rebooked'));
		$this->set('repair_id',$id);
		$this->set('product_code',$searchKW);
		$this->render('product');
	}
	
	public function calculatePaymentAjax(){
		if(array_key_exists('parts_basket',$_SESSION)){
			if(array_key_exists('repair_id',$_SESSION['parts_basket'])){
				$repair_id = $_SESSION['parts_basket']['repair_id'];
			}else{
				echo json_encode(array('error' => 'repair_id is missing'));die;
			}
			$rep_res_query = $this->MobileRepairs->find('all',array('conditions' => array('id' => $repair_id)));
			$rep_res_query = $rep_res_query->hydrate(false);
			if(!empty($rep_res_query)){
				$rep_res = $rep_res_query->first();
			}else{
				$rep_res = array();
			}
			$exempted = $this->checkIfExempted($repair_id, $rep_res['status_rebooked']);
			if($exempted == 0){
				if(!empty($rep_res)){
					$basket = $_SESSION['parts_basket'];
					$costArray = explode('|',$rep_res['estimated_cost']);
						$totalCostValue = 0;
						foreach($costArray as $ca => $costValue){
							$totalCostValue+= $costValue;
						}
						$sum_total['total_cost'] = $totalCostValue;
						$sum_total['repair_id'] = $repair_id;
						if(!empty($basket)){
							$parts = array();
							foreach($basket as $b_key => $b_value){
								if($b_key == "repair_id"){
									continue;
								}
								$parts[$b_key] = $b_value;
							}
							$sum_total['parts'] = $parts;
						}
						$this->request->Session()->write('final_parts_basket',$sum_total);
						echo json_encode(array('total_cost' => $totalCostValue,'repair_id' => $repair_id));die;
				}else{
					echo json_encode(array('error' => 'no data for this repair id'));die;
				}
			}else{
				echo json_encode(array('error' => 'repair is rebooked'));die;
			}
		}else{
			echo json_encode(array('error' => 'Session is empty'));die;
		}
	}
	
	public function finalStepAjax(){
		if(array_key_exists('final_parts_basket',$_SESSION) || array_key_exists('received_reprd_from_tech_data',$this->request->query)){
			if(array_key_exists('final_parts_basket',$_SESSION)){
				$basket = $_SESSION['final_parts_basket'];
			}else{
				$basket = array();
			}
			
			if(array_key_exists('received_reprd_from_tech_data',$this->request->query)){
				$privious_status = 1;
			}else{
				$privious_status = 0;
			}
			if(!empty($this->request->query)){
				$final_amt = $this->request->query['final_amount'];
				$repair_id = "";
				if(array_key_exists('repair_id',$this->request->query)){
					$repair_id = $this->request->query['repair_id'];
				}
				if(empty($repair_id)){
					if(array_key_exists('repair_id',$_SESSION['final_parts_basket'])){
						$repair_id = $_SESSION['final_parts_basket']['repair_id'];
					}
				}
				if(empty($repair_id)){
					echo  json_encode(array('error' =>'No repair id Found'));die;
				}
				
				
				
				
				$pay_1 = $this->request->query['payment_1'];
				$pay_2 = $this->request->query['payment_2'];
				$method_1 = $this->request->query['method_1'];
				$method_2 = $this->request->query['method_2'];
				$part_time = $this->request->query['part_time'];
				
				$rep_res_query = $this->MobileRepairs->find('all',array('conditions' => array('id' => $repair_id)));
				if(!empty($rep_res_query)){
					$rep_res = $rep_res_query->first();
				}else{
					$rep_res = array();
				}
				
				
				$payment_data = $this->RepairPayments->find('all',array(
														'conditions' => array('mobile_repair_id' => $repair_id),
														))->toArray();
				if(!empty($payment_data)){
					if($rep_res['status_rebooked'] != 1){
						echo  json_encode(array('error' =>'Payment Allready Done'));die;
					}
				}
				
				if(!empty($rep_res)){
					$exempted = $this->checkIfExempted($repair_id, $rep_res['status_rebooked']);
					if($exempted == 0){
						//pr($rep_res);die;
						$userId = $this->request->session()->read('Auth.User.id');
						$kioskId = $this->request->Session()->read('kiosk_id');
						
						$costArray = explode('|',$rep_res['estimated_cost']);
						$totalCostValue = 0;
						foreach($costArray as $ca => $costValue){
							$totalCostValue+= $costValue;
						}
						//echo $totalCostValue;echo "</br>";
						//echo $final_amt;die;
						if((int)$totalCostValue != (int)$final_amt){
							echo  json_encode(array('error' =>'amount is not matching'));die;
						}//die;
						$payment_status = 1;
						if($part_time == 1){
							if((int)($pay_1 + $pay_2) == (int)$final_amt){
								$paymentDetailData = array(
													'kiosk_id' => $kioskId,
													'user_id' => $userId,
													'mobile_repair_id' => $repair_id,
													'payment_method' => $method_1,
													'amount' => $pay_1,
													'payment_status' => $payment_status,
													'status' => 1,//this 1 currently does not have any relevance
												);
								
								$RepairPaymentsEntity = $this->RepairPayments->newEntity($paymentDetailData,['validate' => false]);
								$RepairPaymentsEntity = $this->RepairPayments->patchEntity($RepairPaymentsEntity,$paymentDetailData,['validate' => false]);
								$this->RepairPayments->save($RepairPaymentsEntity);
								$paymentDetailData = array(
													'kiosk_id' => $kioskId,
													'user_id' => $userId,
													'mobile_repair_id' => $repair_id,
													'payment_method' => $method_2,
													'amount' => $pay_2,
													'payment_status' => $payment_status,
													'status' => 1,//this 1 currently does not have any relevance
												);
								$RepairPaymentsEntity1 = $this->RepairPayments->newEntity($paymentDetailData,['validate' => false]);
								$RepairPaymentsEntity1 = $this->RepairPayments->patchEntity($RepairPaymentsEntity1,$paymentDetailData,['validate' => false]);
								$this->RepairPayments->save($RepairPaymentsEntity1);
							}else{
								echo  json_encode(array('error' =>'amount is not matching'));die;
							}
						}else{
							//echo $pay_1;echo "</br>";
							//echo $final_amt;die;
							if((int)$pay_1 == (int)$final_amt){
								$paymentDetailData = array(
													'kiosk_id' => $kioskId,
													'user_id' => $userId,
													'mobile_repair_id' => $repair_id,
													'payment_method' => $method_1,
													'amount' => $pay_1,
													'payment_status' => $payment_status,
													'status' => 1,//this 1 currently does not have any relevance
												);
								//pr($paymentDetailData);die;
								$RepairPaymentsEntity = $this->RepairPayments->newEntity($paymentDetailData,['validate' => false]);
								$RepairPaymentsEntity = $this->RepairPayments->patchEntity($RepairPaymentsEntity,$paymentDetailData,['validate' => false]);
								$this->RepairPayments->save($RepairPaymentsEntity);
							}else{
								echo  json_encode(array('error' =>'amount is not matching'));die;
							}
						}
						$this->save_part_and_sale($basket,$rep_res,$repair_id,$kioskId,$userId,$final_amt,$privious_status);
					}else{
						echo json_encode(array('error' => 'repair is rebooked'));die;
					}
				}else{
					  echo json_encode(array('error' => 'no data for this repair id in database'));die;
				}
				
			}
		}else{
			echo json_encode(array('error' => 'No basket found'));die;
		}
	}
	
	public function save_part_and_sale($basket,$rep_res,$repair_id,$kioskId,$userId,$final_amt,$privious_status){
		if(is_array($basket)){
			$savedPartsArr = array();
			$partsSaved = 0;
			$part_res_query = $this->MobileRepairParts->find('all',array('conditions' => array('mobile_repair_id' => $repair_id)));
			$part_res_query = $part_res_query->hydrate(false);
			if(!empty($part_res_query)){
				$part_res = $part_res_query->toArray();
			}else{
				$part_res = array();
			}
			if(empty($part_res)){
				if(!empty($basket)){
					foreach($basket as $key => $productArray){
						if($key == 'repair_id' || $key == 'total_cost')continue;
							foreach($productArray as $p_key => $p_value){
								$mobileRepairPartData = array(
												'user_id' => $userId, 
												'mobile_repair_id' => $repair_id,
												'product_id' => $p_key,
												'kiosk_id' => $kioskId
												  );
								//pr($mobileRepairPartData);die;
								$MobileRepairPartsEntity = $this->MobileRepairParts->newEntity($mobileRepairPartData,['validate' => false]);
								$MobileRepairPartsEntity = $this->MobileRepairParts->patchEntity($MobileRepairPartsEntity,$mobileRepairPartData,['validate' => false]);
								if($this->MobileRepairParts->save($MobileRepairPartsEntity)){
									$partsSaved++;
									$savedPartsArr[$p_key] = $p_key;
								}
							}
					}
					if($partsSaved >= 1){
						if(empty($kioskId)){$productSource = "products";}else{$productSource = "kiosk_{$kioskId}_products";}
						foreach($savedPartsArr as $y_key => $y_val){
							$quantity = 1;
							$query = "UPDATE `$productSource` SET `quantity` = `quantity` - $quantity WHERE `$productSource`.`id` = $y_val";
							$conn = ConnectionManager::get('default');
							$stmt = $conn->execute($query); 
						}
					}
				}else{
					echo json_encode(array('error' => 'No basket found on step two'));die;
				}
			}else{
				$partsSaved = 1;
			}
			if($partsSaved > 0){
				//$this->MobileRepair->id = $repair_id;
				if($privious_status == 1){
					$status = DELIVERED_REPAIRED_BY_TECHNICIAN;
				}else{
					$status = DELIVERED_REPAIRED_BY_KIOSK;
				}
				
				$mobilePurchaseData_query = $this->MobilePurchases->find('all',array('conditions' => array(
									'MobilePurchases.imei' => $rep_res['imei'],
									'MobilePurchases.status' => 4)));
				$mobilePurchaseData_query = $mobilePurchaseData_query->hydrate(false);
				if(!empty($mobilePurchaseData_query)){
					$mobilePurchaseData = $mobilePurchaseData_query->first();
				}else{
					$mobilePurchaseData = array();
				}
				//pr($mobilePurchaseData);die;
				$MobileRepairsEntity = $this->MobileRepairs->get($repair_id);
				$data = array('status' => $status);
				$MobileRepairsEntity = $this->MobileRepairs->patchEntity($MobileRepairsEntity,$data,['validate' => false]);
					if($this->MobileRepairs->save($MobileRepairsEntity)){
						if(!empty($mobilePurchaseData)){
							$purchaseId = $mobilePurchaseData['id'];
							$mobileTransferLogData = array(
									'mobile_purchase_reference' => $mobilePurchaseData['mobile_purchase_reference'],
									'mobile_purchase_id' => $mobilePurchaseData['id'],
									'kiosk_id' => $kioskId,
									'network_id' => $mobilePurchaseData['network_id'],
									'grade' => $mobilePurchaseData['grade'],
									'type' => $mobilePurchaseData['type'],
									'receiving_status' => 0,
									'imei' => $mobilePurchaseData['imei'],
									'user_id' => $userId,
									'status' => 0
								);
							
							$MobileTransferLogsEntity = $this->MobileTransferLogs->newEntity($mobileTransferLogData,['validate' => false]);
							$MobileTransferLogsEntity = $this->MobileTransferLogs->patchEntity($MobileTransferLogsEntity,$mobileTransferLogData,['validate' => false]);
							$this->MobileTransferLogs->save($MobileTransferLogsEntity,['validate' => false]);
							
							$MobilePurchasesEntity = $this->MobilePurchases->get($purchaseId);
							$data = array('status' => 0);
							$MobilePurchasesEntity = $this->MobilePurchases->patchEntity($MobilePurchasesEntity,$data,['validate' => false]);
							$this->MobilePurchases->save($MobilePurchasesEntity);//changing the status to available
						
						}
						
						
						$dateRepaired = date('Y-m-d h:i:s A');
						$mobileRepairSalesData = array(
										'kiosk_id' => $kioskId,
										'retail_customer_id' => $rep_res['retail_customer_id'],
										'mobile_repair_id' => $repair_id,
										'sold_by' => $userId,
										'sold_on' => $dateRepaired,
										'refund_by' => '',
										'amount' => $final_amt,
										'refund_amount' => '',
										'refund_status' => 0,
										'refund_on' => '',
										'refund_remarks' => '',
										'repair_status' => $status
										       );
						$MobileRepairSalesEntity = $this->MobileRepairSales->newEntity($mobileRepairSalesData,['validate' => false]);
						$MobileRepairSalesEntity = $this->MobileRepairSales->patchEntity($MobileRepairSalesEntity,$mobileRepairSalesData,['validate' => false]);
						//pr($MobileRepairSalesEntity);die;
						if($this->MobileRepairSales->save($MobileRepairSalesEntity)){
							$rprSaleId = $MobileRepairSalesEntity->id;
							$query = "UPDATE  `repair_payments`  SET  `mobile_repair_sale_id` = $rprSaleId WHERE `repair_payments`.`mobile_repair_id` = $repair_id";
							$conn = ConnectionManager::get('default');
							$stmt = $conn->execute($query); 
							$mobileRepairLogsData = array(
															'kiosk_id' => $kioskId,
															'user_id' => $userId,
															'mobile_repair_id' => $repair_id,					
															'repair_status' => $status
														);
							$MobileRepairLogsEntity = $this->MobileRepairLogs->newEntity($mobileRepairLogsData,['validate' => false]);
							$MobileRepairLogsEntity = $this->MobileRepairLogs->patchEntity($MobileRepairLogsEntity,$mobileRepairLogsData,['validate' => false]);
							$this->MobileRepairLogs->save($MobileRepairLogsEntity);
							unset($_SESSION['final_parts_basket']);
							unset($_SESSION['parts_basket']);
							echo json_encode(array('status' => 'Repair Done','id' => $repair_id));die;
						}else{
							echo json_encode(array('error' => 'Repair could not be saved'));die;
						}
					}else{
						debug($MobileRepairsEntity->errors());die;
						echo json_encode(array('error' => 'Repair could not be saved'));die;
					}
			}else{
				echo json_encode(array('error' => 'Repair could not be saved'));die;
			}
		}else{
			echo json_encode(array('error' => 'No basket found on step two'));die;
		}
	}
	
	public function dispatched() {
		$repairIDs = array();
		//$cookieKioskId = $this->Cookie->read('kiosk_id');
		//$cookieKioskId = $this->request->session()->read('kiosk_id');
		//$this->set('cookieKioskId',$cookieKioskId);
		$kiosks_query = $this->Kiosks->find('list', array(
													'keyField' => 'id',
													'valueField' => 'name',
									 'conditions' => array('Kiosks.status' => 1),
									 'order' => 'Kiosks.name asc'
									));
		$kiosks_query = $kiosks_query->hydrate(false);
		if(!empty($kiosks_query)){
			$kiosks = $kiosks_query->toArray();
		}else{
			$kiosks = array();
		}
		$kiosk_id = $this->request->Session()->read('kiosk_id');
		if(!empty($kiosk_id) && $this->request->session()->read('Auth.User.group_id') == KIOSK_USERS){
			$this->paginate = array(
					'MobileRepairs' => array(
						'limit' => ROWS_PER_PAGE,
						'conditions' => array('MobileRepairs.status' => DISPATCHED_TO_TECHNICIAN, 'kiosk_id' => $kiosk_id),
						'order' => ['MobileRepairs.id DESC'],
						'contain' => ['Kiosks','MobileModels']
					)
				);	
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
				$this->paginate = array(
					'MobileRepairs' => array(
						'limit' => ROWS_PER_PAGE,
						'conditions' => array('MobileRepairs.status' => DISPATCHED_TO_TECHNICIAN,
											  'MobileRepairs.kiosk_id IN' => $managerKiosk
											  
											  ),
						'order' => ['MobileRepairs.id DESC'],
						'contain' => ['Kiosks','MobileModels']
					)
				);		
			   }
			}else{
				$this->paginate = array(
					'MobileRepairs' => array(
						'limit' => ROWS_PER_PAGE,
						'conditions' => array('MobileRepairs.status' => DISPATCHED_TO_TECHNICIAN),
						'order' => ['MobileRepairs.id DESC'],
						'contain' => ['Kiosks','MobileModels']
					)
				);			
			}
			
			
			
		}
		//pr($this->paginate);die;
		$mobileRepairs_query = $this->paginate('MobileRepairs');
		$mobileRepairs = $mobileRepairs_query->toArray();
		$this->set(compact('mobileRepairs'));
		foreach($mobileRepairs as $mobileRepair){
			$repairIDs[] = $mobileRepair['id'];
		}
		$viewRepairParts = array();
		if(count($repairIDs)){
			$viewRepairParts_query = $this->MobileRepairParts->find('all',array(
																'conditions' => array('MobileRepairParts.mobile_repair_id IN' => $repairIDs),
																		  'recursive' => -1));
			$viewRepairParts_query = $viewRepairParts_query->hydrate(false);
			if(!empty($viewRepairParts_query)){
				$viewRepairParts = $viewRepairParts_query->toArray();
			}else{
				$viewRepairParts = array();
			}
		}
		$this->repair_part_arr($repairIDs);
		$mobileModels_query = $this->MobileModels->find('list',array(
											'keyField' => 'id',
											'valueField' => 'model',
											'order'=>'model asc',
										    'conditions' => array(
													  'MobileModels.status' => 1
													  )
										    )
								       );
		$mobileModels_query = $mobileModels_query->hydrate(false);
		if(!empty($mobileModels_query)){
				$mobileModels = $mobileModels_query->toArray();
		}else{
			$mobileModels = array();
		}
		$hint = $this->ScreenHint->hint('mobile_repairs','dispatched');
					if(!$hint){
						$hint = "";
					}
		$this->set(compact('hint','viewRepairParts','mobileModels'));
		$this->set(compact('kiosks'));
		$this->render('index');
	}
	
	public function receivedByTechnician() {
		//$cookieKioskId = $this->Cookie->read('kiosk_id');
		//$cookieKioskId = $this->request->session()->read('kiosk_id');
		//$this->set('cookieKioskId',$cookieKioskId);
		$kiosks_query = $this->Kiosks->find('list', array(
													'keyField' => 'id',
													'valueField' => 'name',
									 'conditions' => array('Kiosks.status' => 1),
									 'order' => 'Kiosks.name asc'
									));
		$kiosks_query = $kiosks_query->hydrate(false);
		if(!empty($kiosks_query)){
			$kiosks = $kiosks_query->toArray();
		}else{
			$kiosks = array();
		}
		//$this->MobileRepair->recursive = 0;
		$kiosk_id = $this->request->Session()->read('kiosk_id');
		if(!empty($kiosk_id) && $this->request->session()->read('Auth.User.group_id') == KIOSK_USERS){
			$this->paginate = array(
					
						'conditions' => array('MobileRepairs.status' => RECEIVED_BY_TECHNICIAN, 'kiosk_id' => $kiosk_id),
						'limit' => ROWS_PER_PAGE,
						'order' => ['MobileRepairs.id DESC']
					
				);	
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
				
					$this->paginate = array(
						
							'conditions' => array('MobileRepairs.status' => RECEIVED_BY_TECHNICIAN,
												  'MobileRepairs.kiosk_id IN' => $managerKiosk
												  ),
							'limit' => ROWS_PER_PAGE,
							'order' => ['MobileRepairs.id DESC']
						
					);
			   }
			}else{
				$this->paginate = array(
					
						'conditions' => array('MobileRepairs.status' => RECEIVED_BY_TECHNICIAN),
						'limit' => ROWS_PER_PAGE,
						'order' => ['MobileRepairs.id DESC']
					
				);			
			}
			
			
		}
		//$this->set('mobileRepairs', $this->Paginator->paginate());
		$mobileRepairs_query = $this->paginate("MobileRepairs");
		$mobileRepairs = $mobileRepairs_query->toArray();
		$repairIDs = array();
		$users_query = $this->Users->find('list',
									array('conditions' => array('Users.group_id' => 7),
										  'fields' => array('id','username'))
									);
		$users_query = $users_query->hydrate(false);
		if(!empty($users_query )){
			$users  = $users_query ->toArray();
		}else{
			$users  = array();
		}
		
		
		foreach($mobileRepairs as $mobileRepair){
			$repairIDs[] = $mobileRepair['id'];
		}
		$repairTechniciansIds = array_keys($users);
		$viewRepairParts = array();
		if(count($repairIDs)){
			foreach($repairIDs as $repairID){
			//getting the very recent user_id from mobile repair log which is of technician for this repair
				$repLog_query = $this->MobileRepairLogs->find('all', array('conditions' => array(
							'mobile_repair_id' => $repairID,
							'user_id IN' => $repairTechniciansIds),
							'order' => 'MobileRepairLogs.id DESC')
				);
				$repLog_query = $repLog_query->hydrate(false);
				if(!empty($repLog_query)){
					$repLog = $repLog_query->first();
				}else{
					$repLog = array();
				}
				if(count($repLog) >= 1){
					$repairLogDetails[$repLog['mobile_repair_id']] = $users[$repLog['user_id']];
				}
			}
			$viewRepairParts_query = $this->MobileRepairParts->find('all',array('conditions' => array('MobileRepairParts.mobile_repair_id IN' => $repairIDs)));
			$viewRepairParts_query = $viewRepairParts_query->hydrate(false);
			if(!empty($viewRepairParts_query)){
				$viewRepairParts = $viewRepairParts_query->toArray();
			}else{
				$viewRepairParts = array();
			}
		}
		$this->repair_part_arr($repairIDs);
		$mobileModels_query = $this->MobileModels->find('list',array(
											'keyField' => 'id',
											'valueField' => 'model',
											'order'=>'model asc',
										    'conditions' => array(
													  'MobileModels.status' => 1
													  )
										    )
								       );
		$mobileModels_query = $mobileModels_query->hydrate(false);
		if(!empty($mobileModels_query)){
			$mobileModels = $mobileModels_query->toArray();
		}else{
			$mobileModels = array();
		}
		//$viewRepairParts = $this->MobileRepairPart->find('all');
		$hint = $this->ScreenHint->hint('mobile_repairs','received_by_technician');
					if(!$hint){
						$hint = "";
					}
		$this->set(compact('hint','viewRepairParts','repairLogDetails','mobileModels' ));	
		$this->set(compact('mobileRepairs'));
		$this->set(compact('kiosks'));
		$this->render('index');
	}
	
	public function backInventory($mobile_repair_id = "",$part_id =""){
		$userID = $this->request->session()->read('Auth.User.id');
		$userName = $this->request->session()->read('Auth.User.username');
		$kiosk_id = $this->request->Session()->read('kiosk_id');
		$currentDate = date('Y-m-d');
		$viewRepairParts_query = $this->MobileRepairParts->find('all',array(
																		'conditions' => array('MobileRepairParts.id' => $part_id),
																		'order' => 'MobileRepairParts.id desc'
																	)
														);
		$viewRepairParts_query = $viewRepairParts_query->hydrate(false);
		if(!empty($viewRepairParts_query)){
			$viewRepairParts = $viewRepairParts_query->toArray();
		}else{
			$viewRepairParts = array();
		}
		
		
		//grabbing all single part
		$productid  = $viewRepairParts['0']['product_id'];
		$opp_status  =  $viewRepairParts['0']['opp_status'];
		$created = date('d-m-y', strtotime($viewRepairParts['0']['created']));
		$kioskID = $viewRepairParts['0']['kiosk_id'];
		$kioskInfo_query = $this->Kiosks->find('all',
												array('conditions' => array('id' => $kioskID),
											   'fields' => array('name'))
										 );
		$kioskInfo_query = $kioskInfo_query->hydrate(false);
		if(!empty($kioskInfo_query)){
			$kioskInfo = $kioskInfo_query->first();
		}else{
			$kioskInfo = array();
		}
		$kioskName = $kioskInfo['name'];
		$product_query = $this->Products->find('all', array(
											'conditions' => array('id' => $productid),
											'fields' => array('product_code', 'product')
											)
							);
		$product_query = $product_query->hydrate(false);
		if(!empty($product_query)){
			$product = $product_query->first();
		}else{
			$product = array();
		}
		$prodCode = $prodTitle = "";
		if(count($product)){
			$prodCode = $product['product_code'];
			$prodTitle = $product['product'];
		}
		if($opp_status == 0){
            //echo $currentDate;
			//if neither moved to stock nor to faulty
			//$res = $this->MobileRepairParts->updateAll(array(
			//										 'mobile_repair_parts.opp_status' => "1",
			//										 'mobile_repair_parts.user_id'=> $userID,
			//										 'mobile_repair_parts.opp_date' => "'".$currentDate."'"),
			//								   array("mobile_repair_parts.id" => $part_id));
            $partsquery = "UPDATE `mobile_repair_parts` SET `opp_status` = 1,`user_id` = $userID,`opp_date` = '$currentDate' WHERE `id` = $part_id";
            $conn = ConnectionManager::get('default');
			$stmt = $conn->execute($partsquery); 
            //pr($res);
			//part status updated to "Moved to stock"
			if($kiosk_id != 10000){
				$productTable_source = "kiosk_{$kiosk_id}_products";
			}else{
				$productTable_source = "products";
			}
			$productTable = TableRegistry::get($productTable_source,[
                                                                                    'table' => $productTable_source,
                                                                                ]);
			$query = "UPDATE $productTable_source set quantity = quantity + 1 WHERE id = $productid";
			//$this->Products->updateAll(array('Product.quantity' => "Product.quantity + 1"), array("Product.id" => $productid));
			$conn = ConnectionManager::get('default');
			$stmt = $conn->execute($query); 
			//Part inventory updated
			$currentDate = date('d-m-y', strtotime($currentDate));//opTime
			$msg = array('status' => 1, 'username' => $userName, 'operation' => 'Moved 2 Stock', 'opTime' => $currentDate, 'repair_id'=> $mobile_repair_id, 'part_id' => $part_id, 'product' => $prodTitle, 'productCode' => $prodCode, 'partDate' => $created, 'kioskName' => $kioskName);
		}else{
			$msg = array('status' => 0);
		}
		echo json_encode($msg);
		//$this->Session->setFlash($msg);
		//return $this->redirect(array('action' => 'edit',$mobile_repair_id));
		//$this->layout = false;
		die;
	}
	
	public function moveFaulty($mobile_repair_id ="",$part_id =""){
		$userID = $this->request->session()->read('Auth.User.id');
		$userName = $this->request->session()->read('Auth.User.username');
		$kiosk_id = $this->request->Session()->read('kiosk_id');

		$currentDate = $currentTime = date('Y-m-d');
		$kiosks_query = $this->MobileRepairs->Kiosks->find('list', array(
																   'keyField' => 'id',
																   'valueField' => 'name',
																	'conditions' => array('Kiosks.status' => 1)
														));
		$kiosks_query = $kiosks_query->hydrate(false);
		if(!empty($kiosks_query)){
			$kiosks = $kiosks_query->toArray();
		}else{
			$kiosks = array();
		}
		$viewRepairParts_query = $this->MobileRepairParts->find('all',array(
									'conditions' => array('MobileRepairParts.id' => $part_id,'MobileRepairParts.opp_status' => 0),
									'order' => 'MobileRepairParts.id desc'
								)
							);
		if(!empty($viewRepairParts_query)){
			$viewRepairParts = $viewRepairParts_query->first();
		}else{
			$viewRepairParts = array();
		}
		$msg = array();
		if(!empty($viewRepairParts)){
			$productid = $viewRepairParts['product_id'];
			$created = date('d-m-y', strtotime($viewRepairParts['created']));
			$kioskID = $viewRepairParts['kiosk_id'];
			$kioskInfo_query = $this->Kiosks->find('all',array('conditions' => array('id' => $kioskID),
														    'fields' => array('name'))
											 );
			$kioskInfo_query = $kioskInfo_query->hydrate(false);
			if(!empty($kioskInfo_query)){
				$kioskInfo = $kioskInfo_query->first();
			}else{
				$kioskInfo = array();
			}
			$kioskName = $kioskInfo['name'];
			//-----product details------------
			$product_query = $this->Products->find('all', array(
											'conditions' => array('id' => $productid),
											'fields' => array('product_code', 'product','cost_price')
											)
							);
			$product_query = $product_query->hydrate(false);
			if(!empty($product_query)){
				$product = $product_query->first();
			}else{
				$product = array();
			}
			$prodCode = $prodTitle = "";
			$productCost = 0;
			if(count($product)){
				$prodCode = $product['product_code'];
				$prodTitle = $product['product'];
				$prodCost = $product['cost_price'];
			}
			//--------------------------------
			$opp_status =  $viewRepairParts['opp_status'];
			$productcost_query = $this->Products->find('list',array(
						'keyField' => 'id',
						'valueField' => 'cost_price',
					));
			$productcost_query = $productcost_query->hydrate(false);
			if(!empty($productcost_query)){
				$productcost = $productcost_query->toArray();
			}else{
				$productcost = array();
			}
			$remarks_query = $this->FaultyConditions->find('all',array(
																	  'fields'=> array('id', 'faulty_condition'),
																	  'conditions'=>array( 'faulty_condition'=>'Moved to faluty from previous Repair')
																	  ));
			$remarks_query = $remarks_query->hydrate(false);
			if(!empty($remarks_query)){
				$remarks = $remarks_query->first();
			}else{
				$remarks = array();
			}
			if(count($remarks) >=0){
				$remarks_id =  $remarks['id'];
			}else{
				 $remarks_id =  14;//Default
			}
			
			if($opp_status == 0){
				$rawFaultyProductData = array(
					  'product_id' => $productid,
					  'quantity' => 1,
					  'kiosk_id' => $kiosk_id,
					  'user_id' => $this->request->session()->read('Auth.User.id'),
					  'status' => 0, //not moved to central_faulty_products table
					  'cost_price' => $prodCost,
					  'remarks' => $remarks_id, //Note: needs to be different for boloram
					  );
				$DefectiveKioskProductsEntity = $this->DefectiveKioskProducts->newEntity($rawFaultyProductData,['validate' => false]);
				$DefectiveKioskProductsEntity = $this->DefectiveKioskProducts->patchEntity($DefectiveKioskProductsEntity,$rawFaultyProductData,['validate' => false]);
				if($this->DefectiveKioskProducts->save($DefectiveKioskProductsEntity)){
					$this->MobileRepairParts->updateAll(array(
															 'mobile_repair_parts.opp_status' => "2",
															 'mobile_repair_parts.user_id' => $userID,
															 'mobile_repair_parts.opp_date' => "'$currentTime'"),
													   array("mobile_repair_parts.id" => $part_id)
													   );
					$currentDate = date('d-m-y', strtotime($currentDate));//opTime
					$msg = array('status' => 1, 'username' => $userName, 'operation' => 'Moved 2 Faulty', 'opTime' => $currentDate, 'repair_id'=> $mobile_repair_id, 'part_id' => $part_id, 'product' => $prodTitle, 'productCode' => $prodCode, 'partDate' => $created, 'kioskName' => $kioskName);
				}else{
					$msg = array('status' => 0);
				}
			}
		}else{
			$msg = array('status' => 0);
		}
		echo json_encode($msg);
		//$this->layout = false;
		die;
	}
	
	public function receivedRepair() {
		//$cookieKioskId = $this->Cookie->read('kiosk_id');
		//$cookieKioskId = $this->request->session()->read('kiosk_id');
		//$this->set('cookieKioskId',$cookieKioskId);
		$kiosks_query = $this->Kiosks->find('list', array(
													'keyField' => 'id',
													'valueField' => 'name',
									 'conditions' => array('Kiosks.status' => 1),
									 'order' => 'Kiosks.name asc'
									));
		$kiosks_query = $kiosks_query->hydrate(false);
		if(!empty($kiosks_query)){
			$kiosks = $kiosks_query->toArray();
		}else{
			$kiosks = array();
		}
		$kiosk_id = $this->request->Session()->read('kiosk_id');
		if(!empty($kiosk_id) && $this->request->Session()->read('Auth.User.group_id') == KIOSK_USERS){
			$this->paginate =  array(
						'limit' => ROWS_PER_PAGE,
						'conditions' => array('MobileRepairs.status' => RECEIVED_REPAIRED_FROM_TECHNICIAN, 'kiosk_id' => $kiosk_id),
						'order' => ['MobileRepairs.id DESC'],
						'contain' => ['Kiosks']
				);	
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
				$this->paginate = array(
						'limit' => ROWS_PER_PAGE,
						'conditions' => array('MobileRepairs.status' => RECEIVED_REPAIRED_FROM_TECHNICIAN,
											  'MobileRepairs.kiosk_id IN' => $managerKiosk,
											  ),
						'order' => ['MobileRepairs.id DESC'],
						'contain' => ['Kiosks']
				);		
			   }
			}else{
				$this->paginate = array(
						'limit' => ROWS_PER_PAGE,
						'conditions' => array('MobileRepairs.status' => RECEIVED_REPAIRED_FROM_TECHNICIAN),
						'order' => ['MobileRepairs.id DESC'],
						'contain' => ['Kiosks']
				);			
			}
			
			
		}	
		//$this->set('mobileRepairs', $this->Paginator->paginate());
		$mobileRepairs = $this->paginate("MobileRepairs");
		$repairIDs = array();
		$users_query = $this->Users->find('list',array('conditions' => array('Users.group_id' => 7), 'fields' => array('id','username')));
		$users_query = $users_query->hydrate(false);
		if(!empty($users_query)){
			$users = $users_query->toArray();
		}else{
			$users = array();
		}
		
		foreach($mobileRepairs as $mobileRepair){
			$repairIDs[] = $mobileRepair['id'];
		}
		$repairTechniciansIds = array_keys($users);
		$viewRepairParts = array();
		if(count($repairIDs)){
			foreach($repairIDs as $repairID){
			//getting the very recent user_id from mobile repair log which is of technician for this repair
				$repLog_query = $this->MobileRepairLogs->find('all', array('conditions' => array(
							'mobile_repair_id' => $repairID,
							'user_id IN' => $repairTechniciansIds),
							'order' => 'MobileRepairLogs.id DESC')
				);
				$repLog_query = $repLog_query->hydrate(false);
				if(!empty($repLog_query)){
					$repLog = $repLog_query->first();
				}else{
					$repLog = array();
				}
				if(count($repLog) >= 1){
					$repairLogDetails[$repLog['mobile_repair_id']] = $users[$repLog['user_id']];
				}
			}
			$viewRepairParts_query = $this->MobileRepairParts->find('all',array('conditions' => array('MobileRepairParts.mobile_repair_id IN' => $repairIDs)));
			$viewRepairParts_query = $viewRepairParts_query->hydrate(false);
			if(!empty($viewRepairParts_query)){
				$viewRepairParts = $viewRepairParts_query->toArray();
			}else{
				$viewRepairParts = array();
			}
		}
		$this->repair_part_arr($repairIDs);
		$mobileModels_query = $this->MobileModels->find('list',array(
										    'fields' => array('id', 'model'),
											'order'=>'model asc',
										    'conditions' => array(
													  'MobileModels.status' => 1
													  )
										    )
								       );
		$mobileModels_query = $mobileModels_query->hydrate(false);
		if(!empty($mobileModels_query)){
			$mobileModels = $mobileModels_query->toArray();
		}else{
			$mobileModels = array();
		}
		//$viewRepairParts = $this->MobileRepairPart->find('all');
								     
		$hint = $this->ScreenHint->hint('mobile_repairs','received_repair');
					if(!$hint){
						$hint = "";
					}
		$this->set(compact('hint','viewRepairParts','repairLogDetails','mobileModels' ));	
		$this->set(compact('mobileRepairs'));
		$this->set(compact('kiosks'));
		$this->render('index');
	}
	
	public function receivedUnrepaired() {
		//$cookieKioskId = $this->Cookie->read('kiosk_id');
		//$cookieKioskId = $this->request->session()->read('kiosk_id');
		//$this->set('cookieKioskId',$cookieKioskId);
		$kiosks_query = $this->Kiosks->find('list', array(
													'keyField' => 'id',
													'valueField' => 'name',
									 'conditions' => array('Kiosks.status' => 1),
									 'order' => 'Kiosks.name asc'
									));
		$kiosks_query = $kiosks_query->hydrate(false);
		if(!empty($kiosks_query)){
			$kiosks = $kiosks_query->toArray();
		}else{
			$kiosks = array();
		}
		$kiosk_id = $this->request->Session()->read('kiosk_id');
		if(!empty($kiosk_id) && $this->request->Session()->read('Auth.User.group_id') == KIOSK_USERS){
			$this->paginate = array(
					
						'limit' => ROWS_PER_PAGE,
						'conditions' => array('MobileRepairs.status' => RECEIVED_UNREPAIRED_FROM_TECHNICIAN, 'kiosk_id' => $kiosk_id),
						'order' => ['MobileRepairs.id DESC']
					
				);	
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
				$this->paginate = array(
					
						'limit' => ROWS_PER_PAGE,
						'conditions' => array('MobileRepairs.status' => RECEIVED_UNREPAIRED_FROM_TECHNICIAN,
											  'MobileRepairs.kiosk_id IN' => $managerKiosk,
											  ),
						'order' => ['MobileRepairs.id DESC']
					
					);		
			   }
			}else{
				$this->paginate = array(
					
						'limit' => ROWS_PER_PAGE,
						'conditions' => array('MobileRepairs.status' => RECEIVED_UNREPAIRED_FROM_TECHNICIAN),
						'order' => ['MobileRepairs.id DESC']
					
				);			
			}
			
			
		}	
		$mobileRepairs_query = $this->paginate("MobileRepairs");
		$mobileRepairs = $mobileRepairs_query->toArray();
		$repairIDs = array();
		$users_query = $this->Users->find('list',array('conditions' => array('Users.group_id' => 7), 'fields' => array('id','username')));
		$users_query = $users_query->hydrate(false);
		if(!empty($users_query)){
			$users = $users_query->toArray();
		}else{
			$users = array();
		}
		foreach($mobileRepairs as $mobileRepair){
			$repairIDs[] = $mobileRepair->id;
		}
		$repairTechniciansIds = array_keys($users);
		$viewRepairParts = array();
		
		if(count($repairIDs)){
			foreach($repairIDs as $repairID){
			//getting the very recent user_id from mobile repair log which is of technician for this repair
				$repLog_query = $this->MobileRepairLogs->find('all', array('conditions' => array(
							'mobile_repair_id' => $repairID,
							'user_id IN' => $repairTechniciansIds),
							'order' => 'MobileRepairLogs.id DESC')
				);
				$repLog_query = $repLog_query->hydrate(false);
				if(!empty($repLog_query)){
					$repLog = $repLog_query->first();
				}else{
					$repLog = array();
				}
				if(count($repLog) >= 1){
					$repairLogDetails[$repLog['mobile_repair_id']] = $users[$repLog['user_id']];
				}
			}
			$viewRepairParts_query = $this->MobileRepairParts->find('all',array('conditions' => array('MobileRepairParts.mobile_repair_id IN' => $repairIDs)));
			$viewRepairParts_query = $viewRepairParts_query->hydrate(false);
			if(!empty($viewRepairParts_query)){
				$viewRepairParts = $viewRepairParts_query->toArray();
			}else{
				$viewRepairParts = array();
			}
		}
		$this->repair_part_arr($repairIDs);
		$mobileModels_query = $this->MobileModels->find('list',array(
											'keyField' => 'id',
											'valueField' => 'model',
											'order'=>'model asc',
										    'conditions' => array(
													  'MobileModels.status' => 1
													  )
										    )
								       );
		$mobileModels_query = $mobileModels_query->hydrate(false);
		if(!empty($mobileModels_query)){
			$mobileModels = $mobileModels_query->toArray();
		}else{
			$mobileModels = array();
		}
		//$viewRepairParts = $this->MobileRepairPart->find('all');
		$hint = $this->ScreenHint->hint('mobile_repairs','received_unrepaired');
					if(!$hint){
						$hint = "";
					}
		
		$this->set(compact('hint','viewRepairParts','repairLogDetails','mobileModels' ));	
		$this->set(compact('mobileRepairs'));
		$this->set(compact('kiosks'));
		$this->render('index');
	}
	
	public function delivered() {
		//$cookieKioskId = $this->Cookie->read('kiosk_id');
		//$cookieKioskId = $this->request->session()->read('kiosk_id');
		//$this->set('cookieKioskId',$cookieKioskId);
		$kiosks_query = $this->Kiosks->find('list', array(
													'keyField' => 'id',
													'valueField' => 'name',
									 'conditions' => array('Kiosks.status' => 1),
									 'order' => 'Kiosks.name asc'
									));
		$kiosks_query = $kiosks_query->hydrate(false);
		if(!empty($kiosks_query)){
			$kiosks = $kiosks_query->toArray();
		}else{
			$kiosks = array();
		}
		$kiosk_id = $this->request->Session()->read('kiosk_id');
			if(!empty($kiosk_id) && $this->request->Session()->read('Auth.User.group_id') == KIOSK_USERS){
				$this->paginate = array(
							'conditions' => array(
										'OR' => array(
												array('MobileRepairs.status' => DELIVERED_REPAIRED_BY_KIOSK),
												array('MobileRepairs.status' => DELIVERED_UNREPAIRED_BY_KIOSK),
												array('MobileRepairs.status' => DELIVERED_REPAIRED_BY_TECHNICIAN),
												array('MobileRepairs.status' => DELIVERED_UNREPAIRED_BY_TECHNICIAN)
											),
										'kiosk_id' => $kiosk_id
									),
							'limit' => ROWS_PER_PAGE,
							'order' => ['MobileRepairs.id DESC'],
							'contain' => ['Kiosks']
							 );	
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
						$this->paginate = array(
							'conditions' => array(
										'OR' => array(
												array('MobileRepairs.status' => DELIVERED_REPAIRED_BY_KIOSK),
												array('MobileRepairs.status' => DELIVERED_UNREPAIRED_BY_KIOSK),
												array('MobileRepairs.status' => DELIVERED_REPAIRED_BY_TECHNICIAN),
												array('MobileRepairs.status' => DELIVERED_UNREPAIRED_BY_TECHNICIAN),
												
											),
										array('MobileRepairs.kiosk_id IN' => $managerKiosk),
									),
							'limit' => ROWS_PER_PAGE,
							'order' => ['MobileRepairs.id DESC'],
							'contain' => ['Kiosks']
							 );
					}
				}else{
					$this->paginate = array(
							'conditions' => array(
										'OR' => array(
												array('MobileRepairs.status' => DELIVERED_REPAIRED_BY_KIOSK),
												array('MobileRepairs.status' => DELIVERED_UNREPAIRED_BY_KIOSK),
												array('MobileRepairs.status' => DELIVERED_REPAIRED_BY_TECHNICIAN),
												array('MobileRepairs.status' => DELIVERED_UNREPAIRED_BY_TECHNICIAN),
												
											)
									),
							'limit' => ROWS_PER_PAGE,
							'order' => ['MobileRepairs.id DESC'],
							'contain' => ['Kiosks']
							 );	
				}
				
				
			}
		
		$mobileRepairs_query = $this->paginate("MobileRepairs");
		$mobileRepairs = $mobileRepairs_query->toArray();
		$repairIDs = array();
		$users_query = $this->Users->find('list',array('conditions' => array('Users.group_id' => 7), 'fields' => array('id','username')));
		$users_query = $users_query->hydrate(false);
		if(!empty($users_query)){
			$users = $users_query->toArray();
		}else{
			$users = array();
		}
		foreach($mobileRepairs as $mobileRepair){
			$repairIDs[] = $mobileRepair['id'];
		}
		$repairTechniciansIds = array_keys($users);
		if(empty($repairTechniciansIds)){
			$repairTechniciansIds = array(0 => null);
		}
		$viewRepairParts = array();
		if(count($repairIDs)){
			foreach($repairIDs as $repairID){
			//getting the very recent user_id from mobile repair log which is of technician for this repair
				$repLog_query = $this->MobileRepairLogs->find('all', array('conditions' => array(
							'mobile_repair_id' => $repairID,
							'user_id IN' => $repairTechniciansIds),
							'order' => 'MobileRepairLogs.id DESC')
				);
				$repLog_query = $repLog_query->hydrate(false);
				if(!empty($repLog_query)){
					$repLog = $repLog_query->first();
				}else{
					$repLog = array();
				}
				if(count($repLog) >= 1){
					$repairLogDetails[$repLog['mobile_repair_id']] = $users[$repLog['user_id']];
				}
			}
			
			$viewRepairParts_query = $this->MobileRepairParts->find('all',array('conditions' => array('MobileRepairParts.mobile_repair_id IN' => $repairIDs)));
			$viewRepairParts_query = $viewRepairParts_query->hydrate(false);
			if(!empty($viewRepairParts_query)){
				$viewRepairParts = $viewRepairParts_query->toArray();
			}else{
				$viewRepairParts = array();
			}
		}
		$this->repair_part_arr($repairIDs);
		$mobileModels_query = $this->MobileModels->find('list',array(
											'keyField' => 'id',
											'valueField' => 'model',
											'order'=>'model asc',
										    'conditions' => array(
													  'MobileModels.status' => 1
													  )
										    )
								       );
		$mobileModels_query = $mobileModels_query->hydrate(false);
		if(!empty($mobileModels_query)){
			$mobileModels = $mobileModels_query->toArray();
		}else{
			$mobileModels = array();
		}
		//$viewRepairParts = $this->MobileRepairPart->find('all');
		$hint = $this->ScreenHint->hint('mobile_repairs','delivered');
					if(!$hint){
						$hint = "";
					}
		$this->set(compact('hint','viewRepairParts','repairLogDetails','mobileModels' ));	
		$this->set(compact('mobileRepairs'));
		$this->set(compact('kiosks'));
		$this->render('index');
	}
	
	public function dispatchedToKiosk() {
		//$cookieKioskId = $this->Cookie->read('kiosk_id');
		//$cookieKioskId = $this->request->session()->read('kiosk_id');
		//$this->set('cookieKioskId',$cookieKioskId);
		$kiosks_query = $this->Kiosks->find('list', array(
													'keyField' => 'id',
													'valueField' => 'name',
									 'conditions' => array('Kiosks.status' => 1),
									 'order' => 'Kiosks.name asc'
									));
		$kiosks_query = $kiosks_query->hydrate(false);
		if(!empty($kiosks_query)){
			$kiosks = $kiosks_query->toArray();
		}else{
			$kiosks = array();
		}

		$kiosk_id = $this->request->Session()->read('kiosk_id');
		if(!empty($kiosk_id) && $this->request->Session()->read('Auth.User.group_id') == KIOSK_USERS){
			$this->paginate = array(
						'conditions' => array(
									'OR' => array(
											array('MobileRepairs.status' => DISPATCHED_2_KIOSK_REPAIRED),
											array('MobileRepairs.status' => DISPATCHED_2_KIOSK_UNREPAIRED)
										),
									'kiosk_id' => $kiosk_id
								),
						'limit' => ROWS_PER_PAGE,
						'order' => ['MobileRepairs.id DESC']
						 );				
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
					$this->paginate = array(
						'conditions' => array(
									'OR' => array(
											array('MobileRepairs.status' => DISPATCHED_2_KIOSK_REPAIRED),
											array('MobileRepairs.status' => DISPATCHED_2_KIOSK_UNREPAIRED)
										),
									'MobileRepairs.kiosk_id IN' => $managerKiosk,
								),
						'limit' => ROWS_PER_PAGE,
						'order' => ['MobileRepairs.id DESC']
						 );			
			   }
				
			}else{
				$this->paginate = array(
						'conditions' => array(
									'OR' => array(
											array('MobileRepairs.status' => DISPATCHED_2_KIOSK_REPAIRED),
											array('MobileRepairs.status' => DISPATCHED_2_KIOSK_UNREPAIRED)
										)
								),
						'limit' => ROWS_PER_PAGE,
						'order' => ['MobileRepairs.id DESC']
						 );		
			}
			
		}		
		//$this->set('mobileRepairs', $this->Paginator->paginate());
		$mobileRepairs_query = $this->paginate("MobileRepairs");
		$mobileRepairs = $mobileRepairs_query->toArray();
		$repairIDs = array();
		$users_query = $this->Users->find('list',array('conditions' => array('Users.group_id' => 7), 'fields' => array('id','username')));
		$users_query = $users_query->hydrate(false);
		if(!empty($users_query)){
			$users = $users_query->toArray();
		}else{
			$users = array();
		}
		foreach($mobileRepairs as $mobileRepair){
			$repairIDs[] = $mobileRepair->id;
		}
		$repairTechniciansIds = array_keys($users);
		
		$viewRepairParts = array();
		if(count($repairIDs)){
			foreach($repairIDs as $repairID){
			//getting the very recent user_id from mobile repair log which is of technician for this repair
				$repLog_query = $this->MobileRepairLogs->find('all', array('conditions' => array(
							'mobile_repair_id' => $repairID,
							'user_id IN' => $repairTechniciansIds),
							'order' => 'MobileRepairLogs.id DESC')
				);
				$repLog_query = $repLog_query->hydrate(false);
				if(!empty($repLog_query)){
					$repLog = $repLog_query->first();
				}else{
					$repLog = array();
				}
				if(count($repLog) >= 1){
					$repairLogDetails[$repLog['mobile_repair_id']] = $users[$repLog['user_id']];
				}
			}
			$viewRepairParts_query = $this->MobileRepairParts->find('all',array('conditions' => array('MobileRepairParts.mobile_repair_id IN' => $repairIDs)));
			$viewRepairParts_query = $viewRepairParts_query->hydrate(false);
			if(!empty($viewRepairParts_query)){
				$viewRepairParts = $viewRepairParts_query->toArray();
			}else{
				$viewRepairParts = array();
			}
		}
		$this->repair_part_arr($repairIDs);
		$mobileModels_query = $this->MobileModels->find('list',array(
											'keyField' => 'id',
											'valueField' => 'model',
											'order'=>'model asc',
										    'conditions' => array(
													  'MobileModels.status' => 1
													  )
										    )
								       );
		$mobileModels_query = $mobileModels_query->hydrate(false);
		if(!empty($mobileModels_query)){
			$mobileModels = $mobileModels_query->toArray();
		}else{
			$mobileModels = array();
		}
		$hint = $this->ScreenHint->hint('mobile_repairs','dispatched_to_kiosk');
					if(!$hint){
						$hint = "";
					}
		$this->set(compact('hint','dispatched_to_kiosk','viewRepairParts','repairLogDetails','mobileModels' ));	
		$this->set(compact('mobileRepairs'));
		$this->set(compact('kiosks'));
		$this->render('index');
	}
	
	public function searchRepairParts($id = null){
		$repairData_query = $this->MobileRepairs->find('all', array('conditions' => array('MobileRepairs.id' => $id)));
		$repairData_query = $repairData_query->hydrate(false);
		if(!empty($repairData_query)){
			$repairData = $repairData_query->first();
		}else{
			$repairData = array();
		}
		//getting last repaired info by service center/kiosk from logs
		$repairLogData_query = $this->MobileRepairLogs->find('all', array('conditions' => array('MobileRepairLogs.mobile_repair_id' => $id,
								'OR' => array(
									      array('MobileRepairLogs.repair_status' => DISPATCHED_2_KIOSK_REPAIRED),
									      array('MobileRepairLogs.repair_status' => DELIVERED_REPAIRED_BY_KIOSK)
									      )
									),
								'order' => 'MobileRepairLogs.id desc'
								));
		$repairLogData_query = $repairLogData_query->hydrate(false);
		if(!empty($repairLogData_query)){
			$repairLogData = $repairLogData_query->first();
		}else{
			$repairLogData = array();
		}
		
		//this kioskID is for admin
		if((int)$repairLogData['service_center_id'] && $repairLogData['service_center_id'] > 0){
			$kioskID = $repairLogData['service_center_id'];
		}else{
			$kioskID = $repairLogData['kiosk_id'];
		}
		
		$sessionKioskId = $this->request->Session()->read('kiosk_id');
		if($this->request->session()->read('Auth.User.group_id') == KIOSK_USERS || $this->request->session()->read('Auth.User.group_id') == REPAIR_TECHNICIANS){
			$kioskID = $sessionKioskId;
		}
		//pr($this->request);die;		
		//$kioskID = $repairData['MobileRepair']['kiosk_id'];
		$searchKW = '';
		if(array_key_exists('search_kw',$this->request->data)){
			$searchKW = $this->request->data['search_kw'];
		}
		
		if (!$this->MobileRepairs->exists($id)) {
			throw new NotFoundException(__('Invalid mobile repair'));
		}
		
		$conditionArr = array();
		//----------------------
		if(!empty($searchKW)){
			$conditionArr['OR']['LOWER(product) like '] =  strtolower("%$searchKW%");
			$conditionArr['OR']['LOWER(product_code) like '] =  strtolower("%$searchKW%");
		}
		if(array_key_exists('category',$this->request->data)){
			$conditionArr['category_id IN'] = $this->request->data['category'];
			//$conditionArr['OR']['category_id'] = $this->request->data['category'];
		}
		$this->paginate = array(
						'conditions' => array($conditionArr,'quantity > 0'),
						'limit' => 20
					);
		//--------------------------------------------------------------
		
		$productSource = "kiosk_{$kioskID}_products";
		$productTable = TableRegistry::get($productSource,[
																	'table' => $productSource,
																]);
		//echo $problemVar['repair_number'];
		
		//-----------------------------------------
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
		$this->set(compact('categories','kioskID'));
		//$this->request['data']['MobileRepairs']['id'] = $id;
		$products = $this->paginate($productTable);
		$this->set('products', $products);
		//--------------------------------------------------------------
		
		//$this->get_product_categories();
		$this->set('repair_id',$id);
		$this->set('product_code',$searchKW);
		$this->render('replace_parts_page');
	}
	
	public function searchRepairProduct($id = null){//being used in view repair parts
		$query = 'SELECT now() as curdt';
		$conn = ConnectionManager::get('default');
		$stmt = $conn->execute($query);
		$now = $stmt ->fetchAll('assoc');
		$currntDate = date('Y-m-d',strtotime($now[0]['curdt']));
		$kiosks_query = $this->Kiosks->find('list',array(
													'keyField' => 'id',
													'valueField' => 'name',
												   'order' => 'Kiosks.name asc'));
		$kiosks_query = $kiosks_query->hydrate(false);
		if(!empty($kiosks_query)){
			$kiosks = $kiosks_query->toArray();
		}else{
			$kiosks = array();
		}
		
		$repairData_query_query = $this->MobileRepairs->find('all', array('conditions' => array('MobileRepairs.id' => $id)));
		$repairData_query_query = $repairData_query_query->hydrate(false);
		if(!empty($repairData_query_query)){
			$repairData = $repairData_query_query->first();
		}else{
			$repairData = array();
		}
		
		
		//getting last repaired info by service center/kiosk from logs
		$repairLogData_query = $this->MobileRepairLogs->find('all', array('conditions' => array('MobileRepairLogs.mobile_repair_id' => $id,
								'OR' => array(
									      array('MobileRepairLogs.repair_status' => DISPATCHED_2_KIOSK_REPAIRED),
									      array('MobileRepairLogs.repair_status' => DELIVERED_REPAIRED_BY_KIOSK)
									      )
									),
								'order' => 'MobileRepairLogs.id desc'
								));
		$repairLogData_query = $repairLogData_query->hydrate(false);
		if(!empty($repairLogData_query)){
			$repairLogData = $repairLogData_query->first();
		}else{
			$repairLogData = array();
		}
		$repairDate = date('Y-m-d',strtotime($repairLogData['created']));
		
		//this kioskID is for admin
		if((int)$repairLogData['service_center_id'] && $repairLogData['service_center_id'] > 0){
			$kioskID = $repairLogData['service_center_id'];
		}else{
			$kioskID = $repairLogData['kiosk_id'];
		}
		
		$ksk_Id = $kskId = $prtId = 0;
		if(array_key_exists('selectedKiosk',$this->request->query)){
			$ksk_Id = $kskId = (int)$this->request->query['selectedKiosk'];
		}
		if(array_key_exists('part',$this->request->query)){
			$prtId = $this->request->query['part'];
		}
		
		$this->set(compact('kskId','prtId','kioskID','kiosks','ksk_Id'));
		$kioskId = $this->request->Session()->read('kiosk_id');
		$condArr = array('MobileRepairParts.mobile_repair_id' => $id,'MobileRepairParts.kiosk_id' => $kioskId);
		if($ksk_Id)$condArr = array('MobileRepairParts.mobile_repair_id' => $id,'MobileRepairParts.kiosk_id' => $ksk_Id);
		if($this->request->session()->read('Auth.User.group_id') == ADMINISTRATORS || $this->request->session()->read('Auth.User.group_id') == MANAGERS || $this->request->session()->read('Auth.User.group_id') == FRANCHISE_OWNER){
			unset($condArr['MobileRepairPart.kiosk_id']); //show all parts to admin
		}
		$viewRepairParts_query = $this->MobileRepairParts->find('all',array(
								'conditions' => $condArr
								)
							);
		$viewRepairParts_query = $viewRepairParts_query->hydrate(false);
		if(!empty($viewRepairParts_query)){
			$viewRepairParts = $viewRepairParts_query->toArray();
		}else{
			$viewRepairParts = array();
		}
		$condArr = array('MobileRepairParts.mobile_repair_id' => $id,'MobileRepairParts.kiosk_id !=' => $kioskId);
		if($ksk_Id)$condArr = array('MobileRepairParts.mobile_repair_id' => $id,'MobileRepairParts.kiosk_id !=' => $ksk_Id);
		$viewOtherRepairParts = array();
		$viewOtherRepairParts_query = $this->MobileRepairParts->find('all',array(
																'conditions' => $condArr,
																'order' => 'MobileRepairParts.id desc'
																			)
																);
		$viewOtherRepairParts_query = $viewOtherRepairParts_query->hydrate(false);
		if(!empty($viewOtherRepairParts_query)){
			$viewOtherRepairParts = $viewOtherRepairParts_query->toArray();
		}else{
			$viewOtherRepairParts = array();
		}
		if($this->request->session()->read('Auth.User.group_id') == ADMINISTRATORS || $this->request->session()->read('Auth.User.group_id') == MANAGERS || $this->request->session()->read('Auth.User.group_id') == FRANCHISE_OWNER){
			$viewOtherRepairParts = array();
		}
		
		foreach($viewRepairParts as $viewRepairPart){
			$productIds[] = $viewRepairPart['product_id'];
		}
		foreach($viewOtherRepairParts as $viewRepairPart){
			$productIds[] = $viewRepairPart['product_id'];
		}
		$productName_query = $this->Products->find('list',array('fields'=> array('id','product'), 'conditions' => array('id IN' => $productIds)));
		$productName_query = $productName_query->hydrate(false);
		if(!empty($productName_query)){
			$productName = $productName_query->toArray();
		}else{
			$productName = array();
		}
		$productsCode_query = $this->Products->find('list',array('fields'=> array('id','product_code'), 'conditions' => array('id IN' => $productIds)));
		$productsCode_query  = $productsCode_query->hydrate(false);
		if(!empty($productsCode_query)){
			$productsCode = $productsCode_query->toArray();
		}else{
			$productsCode = array();
		}
		$repair_id = $id;
		
		$searchKW = '';
		if(array_key_exists('search_kw',$this->request->query)){
			$searchKW = $this->request->query['search_kw'];
		}
		
		if (!$this->MobileRepairs->exists($id)) {
			throw new NotFoundException(__('Invalid mobile repair'));
		}
		
		$conditionArr = array();
		//----------------------
		if(!empty($searchKW)){
			$conditionArr['OR']['LOWER(product) like '] =  strtolower("%$searchKW%");
			$conditionArr['OR']['LOWER(product_code) like '] =  strtolower("%$searchKW%");
		}
		if(array_key_exists('category',$this->request->query)){
			//$conditionArr['category_id'] = $this->request->query['category'];
			$conditionArr['OR']['category_id IN'] = $this->request->query['category'];
		}
		
		$productSource = "products";
		if(array_key_exists('selectedKiosk',$this->request->query)){
			$kiosk_id = $this->request->query['selectedKiosk'];
			$productSource = "kiosk_{$kiosk_id}_products";
		}
		
		$this->paginate = array(
						'conditions' => array($conditionArr,'quantity > 0'),
						'limit' => 20
					);
		//--------------------------------------------------------------
		
		//$this->Product->setSource($productSource);
		$productTable = TableRegistry::get($productSource,[
																	'table' => $productSource,
																]);
		//echo $problemVar['repair_number'];
		
		//-----------------------------------------
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
		$users_query = $this->Users->find('list',array(
												 'keyField' =>'id',
												 'valueField' => 'username',
												 ));
		$users_query = $users_query->hydrate(false);
		if(!empty($users_query)){
			$users = $users_query->toArray();
		}else{
			$users = array();
		}
		$this->set(compact('users'));
		$this->set(compact('categories','repair_id','viewRepairParts','viewOtherRepairParts','productName','productsCode','currntDate'));
		//$this->request['data']['MobileRepairs']['id'] = $id;
		$products = $this->paginate($productTable);
		$this->set('products',$products);
		//--------------------------------------------------------------
		
		//$this->get_product_categories();
		$this->set('repair_id',$id);
		$this->set('product_code',$searchKW);
		$this->render('view_repair_parts');
	}
	
	public function repairReceipt($id = null){
		//Configure::load('common-arrays');
		$problemTypeOptions_query = $this->ProblemTypes->find('list',array(
																	 'keyField' => 'id',
																	 'valueField' => 'problem_type',
																	 ));
		$problemTypeOptions_query  = $problemTypeOptions_query->hydrate(false);
		if(!empty($problemTypeOptions_query)){
			$problemTypeOptions = $problemTypeOptions_query->toArray();
		}else{
			$problemTypeOptions = array();
		}
		$mobileRepairData_query = $this->MobileRepairs->find('all',array('conditions'=>array('MobileRepairs.id'=>$id),
																		   'contain' => array("MobileRepairSales","Brands","MobileModels")
																		   ));
		$mobileRepairData_query = $mobileRepairData_query->hydrate(false);
		if(!empty($mobileRepairData_query)){
			$mobileRepairData = $mobileRepairData_query->first();
		}else{
			$mobileRepairData = array();
		}
		//pr($mobileRepairData);die;
		$repairRefundData = array();
		foreach($mobileRepairData['mobile_repair_sales'] as $key=>$repairSaleData){
			if($repairSaleData['refund_status']==1){
				$repairRefundData[] = $repairSaleData;
			}
		}
		$settingArr = $this->setting;
		$userId = $mobileRepairData['booked_by'];
		$kiosk_id = $mobileRepairData['kiosk_id'];
		$userName_query = $this->Users->find('list',array('conditions'=>array('Users.id'=>$userId),
														  'keyField' => 'id',
														  'valueField' => 'username',
														  ));
		$userName_query = $userName_query->hydrate(false);
		if(!empty($userName_query)){
			$userName = $userName_query->toArray();
		}else{
			$userName = array();
		}
		
		$kioskDetails_query = $this->Kiosks->find('all',array('conditions'=>array('Kiosks.id'=>$kiosk_id),
															  //'fields'=>array('id','name','address_1','address_2','city','state','zip','contact','country')
															  ));
		$kioskDetails_query = $kioskDetails_query->hydrate(false);
		if(!empty($kioskDetails_query)){
			$kioskDetails = $kioskDetails_query->first();
		}else{
			$kioskDetails = array();
		}
		
		$pay_res = $this->RepairPayments->find('all', array('conditions' => array('RepairPayments.mobile_repair_id' => $id),
															'order by' => 'RepairPayments.created ASC',
															)
											   )->toArray();
		$date = "";
		if(!empty($pay_res)){
			$date = $pay_res[0]->created;
		}
		
		if($this->request->is('post')){
			//pr($this->request);die;
			$send_by_email = Configure::read('send_by_email');
			$emailSender = Configure::read('EMAIL_SENDER');
			$customerEmail = $this->request['data']['email'];
			if(!empty($customerEmail)){
					$Email = new Email();
					$Email->config('default');
					$Email->viewVars(array('mobileRepairData' => $mobileRepairData,'settingArr' => $settingArr,'userId' => $userId, 'userName' => $userName,'problemTypeOptions'=>$problemTypeOptions,'repairRefundData'=>$repairRefundData, 'kioskDetails' => $kioskDetails,"date"=>$date));
					//$Email->config(array('from' => 'me@example.org', 'transport' => 'MyCustom'));
					//$Email = new CakeEmail(array('from' => 'me@example.org', 'transport' => 'MyCustom'));
					$emailTo = $customerEmail;
					$Email->template('repair_email_receipt');
					$Email->emailFormat('both');
					$Email->to($emailTo);
					 $Email->transport(TRANSPORT);
                    $Email->from([$send_by_email => $emailSender]);
					//$Email->sender("sales@oceanstead.co.uk");
					$Email->subject('Mobile Repair Receipt');
					if($Email->send()){
						$this->Flash->success("Email has been sent");
					}
				}
		}
		$this->set(compact('settingArr','mobileRepairData','userName','repairRefundData','kioskDetails','problemTypeOptions',"date"));
	}
	
	public function repairReportDetail(){
		$problemTypeOptions_query = $this->ProblemTypes->find('list',array(
																	 'keyField' => 'id',
																	 'valueField' => 'problem_type',
																	 ));
		$problemTypeOptions_query = $problemTypeOptions_query->hydrate(false);
		if(!empty($problemTypeOptions_query)){
			$problemTypeOptions = $problemTypeOptions_query->toArray();
		}else{
			$problemTypeOptions = array();
		}
		$firstDay = date("Y-m-1");
		$lastDay = date("Y-m-t");
		$start = date("Y-m-d",strtotime("-1 day",strtotime($firstDay)));
		$end = date("Y-m-d",strtotime("+1 day",strtotime($lastDay)));
		
		$userName_query = $this->Users->find('list',[
													 'keyField' => 'id',
													 'valueField'=>'username'
													]
											);
		$userName_query = $userName_query->hydrate(false);
		if(!empty($userName_query)){
			$userName = $userName_query->toArray();
		}else{
			$userName = array();
		}
		//pr($userName);die;
		$kiosks_query = $this->Kiosks->find('list',array('fields'=>array('id','name')));
		$kiosks_query = $kiosks_query->hydrate(false);
		if(!empty($kiosks_query)){
			$kiosks = $kiosks_query->toArray();
		}else{
			$kiosks = array(); 
		}
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
		
		$user = $this->request->query['user_id'];
		$kiosk = $this->request->query['kiosk_id'];
		$service_center = $this->request->query['service_center'];
		$requestParams = array(
							'user_id' => $user,
							'kiosk_id' => $kiosk,
							'service_center' => $service_center,
							'start_date' => $start,
							'end_date' => $end,
							);
		
		if(empty($user) && empty($kiosk) && empty($service_center)){
			$this->paginate =  array(
					'conditions' => array("DATE(MobileRepairLogs.created) > '$start'" ,
								"DATE(MobileRepairLogs.created) < '$end'",
								'MobileRepairLogs.repair_status IN' => array(DISPATCHED_2_KIOSK_REPAIRED)
								),
						'order' => ['MobileRepairLogs.id desc']
				);
		}elseif(!empty($user) && empty($kiosk) && empty($service_center)){
			$userId = $user;
			$this->paginate =  array(
					'conditions' => array("DATE(MobileRepairLogs.created) > '$start'" ,
								"DATE(MobileRepairLogs.created) < '$end'",
								'MobileRepairLogs.repair_status IN' => array(DISPATCHED_2_KIOSK_REPAIRED),
								'MobileRepairLogs.user_id' => $userId
								),
							'order' => ['MobileRepairLogs.id desc']
				);
		}elseif(!empty($kiosk) && empty($user) && empty($service_center)){
			$kioskId = $kiosk;
			$this->paginate =  array(
					'conditions' => array("DATE(MobileRepairLogs.created) > '$start'" ,
								"DATE(MobileRepairLogs.created) < '$end'",
								'MobileRepairLogs.repair_status IN' => array(DISPATCHED_2_KIOSK_REPAIRED),
								'MobileRepairLogs.kiosk_id' => $kioskId
								),
						'order' => ['MobileRepairLogs.id desc']
				);
		}elseif(!empty($service_center) && empty($user) && empty($kiosk)){
			$kioskId = $kiosk;
			$this->paginate =  array(
					'conditions' => array("DATE(MobileRepairLogs.created) > '$start'" ,
								"DATE(MobileRepairLogs.created) < '$end'",
								'MobileRepairLogs.repair_status IN' => array(DISPATCHED_2_KIOSK_REPAIRED),
								'MobileRepairLogs.service_center_id' => $service_center
								),
						'order' => ['MobileRepairLogs.id desc']
				);
		}elseif(!empty($service_center) && !empty($user) && empty($kiosk)){
			$userId = $user;
			$this->paginate =  array(
					'conditions' => array("DATE(MobileRepairLogs.created) > '$start'" ,
								"DATE(MobileRepairLogs.created) < '$end'",
								'MobileRepairLogs.repair_status IN' => array(DISPATCHED_2_KIOSK_REPAIRED),
								'MobileRepairLogs.service_center_id' => $service_center,
								'MobileRepairLogs.user_id' => $userId
								),
						'order' => ['MobileRepairLogs.id desc']
				);
		}elseif(!empty($service_center) && empty($user) && !empty($kiosk)){
			$kioskId = $kiosk;
			$this->paginate =  array(
					'conditions' => array("DATE(MobileRepairLogs.created) > '$start'" ,
								"DATE(MobileRepairLogs.created) < '$end'",
								'MobileRepairLogs.repair_status IN' => array(DISPATCHED_2_KIOSK_REPAIRED),
								'MobileRepairLogs.service_center_id' => $service_center,
								'MobileRepairLogs.kiosk_id' => $kioskId
								),
						'order' => ['MobileRepairLogs.id desc']
				);
		}elseif(empty($service_center) && !empty($user) && !empty($kiosk)){
			$userId = $user;
			$kioskId = $kiosk;
			$this->paginate =  array(
					'conditions' => array("DATE(MobileRepairLogs.created) > '$start'" ,
								"DATE(MobileRepairLogs.created) < '$end'",
								'MobileRepairLogs.repair_status IN' => array(DISPATCHED_2_KIOSK_REPAIRED),
								'MobileRepairLogs.kiosk_id' => $kioskId,
								'MobileRepairLogs.user_id' => $userId
								),
						'order' => ['MobileRepairLogs.id desc']
				);
		}else{
			$userId = $user;
			$kioskId = $kiosk;
			$this->paginate =  array(
					'conditions' => array("DATE(MobileRepairLogs.created) > '$start'" ,
								"DATE(MobileRepairLogs.created) < '$end'",
								'MobileRepairLogs.repair_status IN' => array(DISPATCHED_2_KIOSK_REPAIRED),
								'MobileRepairLogs.kiosk_id' => $kioskId,
								'MobileRepairLogs.user_id' => $userId,
								'MobileRepairLogs.service_center_id' => $service_center
								),
						'order' => ['MobileRepairLogs.id desc']
				);
		}
		
		$repairData_query = $this->paginate('MobileRepairLogs');
		$repairData = $repairData_query->toArray();
		//pr($repairData);
		$userRepairIds = array();
		$mobile_model_ids = array();
		foreach($repairData as $key => $repairLog){
			$userRepairIds[$repairLog->user_id][] = $repairLog->mobile_repair_id;
		}
		//pr($userRepairIds);die;
		
		$repairDetail = array();
		$data = array();
		foreach($userRepairIds as $user_id => $userRepairArr){
			
			$repairDetail_query = $this->MobileRepairs->find('all',array('conditions'=>array('MobileRepairs.id IN'=>$userRepairArr),'fields'=>array('id','brand_id','mobile_model_id','problem_type','net_cost','status_rebooked')));
			$repairDetail_query = $repairDetail_query->hydrate(false);
			if(!empty($repairDetail_query)){
				$repairDetail[$user_id] = $repairDetail_query->toArray();
			}else{
				$repairDetail[$user_id] = array();
			}
			$data_query = $this->MobileRepairSales->find('all',array('conditions'=>array('MobileRepairSales.mobile_repair_id IN'=>$userRepairArr)));
			$data_query = $data_query->hydrate(false);
			if(!empty($data_query)){
				$data[] = $data_query->toArray();
			}else{
				$data[] = array();
			}
		}
		//pr($data);
		$i = 0;
		$sale_data = array();
		foreach($data[0] as $key => $value){
			$sale_data[$i]['repair_id'] = $value['mobile_repair_id'];
			$sale_data[$i]['refund_status'] = $value['refund_status'];
			$sale_data[$i]['amount'] = $value['amount'];
			$sale_data[$i]['refund_amount'] = $value['refund_amount'];
			$i = $i+1;
		}
		$final = array();
		foreach($sale_data as $key1 => $value1){
			if(array_key_exists($value1['repair_id'],$final)){
				if($final[$value1['repair_id']]['refund_status'] == 0 && $value1['amount'] != 0){
					$final[$value1['repair_id']]['refund_status'] = $value1['refund_status'];
					$final[$value1['repair_id']]['amount'] = $value1['amount'];
					$final[$value1['repair_id']]['refund_amount'] = $value1['refund_amount'];
				}
			}else{
				$final[$value1['repair_id']]['refund_status'] = $value1['refund_status'];
				$final[$value1['repair_id']]['amount'] = $value1['amount'];
				$final[$value1['repair_id']]['refund_amount'] = $value1['refund_amount'];
			}
		}	
		
		foreach($userRepairIds as $user_id => $userRepairArr){
			
			$res_query = $this->MobileRepairParts->find('all',array('conditions'=>array('MobileRepairParts.mobile_repair_id IN'=>$userRepairArr)));
			$res_query = $res_query->hydrate(false);
			if(!empty($res_query)){
				$partsResult[] =$res_query->toArray();
			}else{
				$partsResult[] = array();
			}
		}
		$finalPart = array();
		if(!empty($partsResult)){
			foreach($partsResult[0] as $k => $partsUsed){
					$finalPart[$partsUsed['mobile_repair_id']][] = $partsUsed['product_id'];
			}
		}
		$products_query = $this->Products->find('list',array(
													   'keyField' => 'id',
													   'valueField' => 'product',
													   ));
		$products_query = $products_query->hydrate(false);
		if(!empty($products_query)){
			$products = $products_query->toArray();
		}else{
			$products = array();
		}
		//pr($products);die;
		foreach($repairDetail[$user_id] as $k => $repairDet){
			$mobile_model_ids[$repairDet['mobile_model_id']] = $repairDet['mobile_model_id'];
		}
		
		$mobileModels_query = $this->MobileModels->find('list',array('conditions' => array('MobileModels.id IN' => $mobile_model_ids),
																	 'keyField' => 'id',
																	 'valueField' => 'model',
																	 'order'=>'model asc'
															   )
												  );
		$mobileModels_query = $mobileModels_query->hydrate(false);
		if(!empty($mobileModels_query)){
			$mobileModels = $mobileModels_query->toArray();
		}else{
			$mobileModels = array();
		}
		$this->set(compact('repairData','user','repairDetail','userName','kiosks','mobileModels','problemTypeOptions', 'requestParams','final','finalPart','products'));
	}
	
	public function managerEdit($id = null) {
		//pr($this->request);die;
		$mobileConditions_query = $this->MobileConditions->find('list',[
                                                                    'conditions' => ['MobileConditions.status' => 1],
                                                                    'keyField' => 'id',
																	'valueField' => 'mobile_condition'
                                                                 ]
                                                          );
        $mobileConditions_query = $mobileConditions_query->hydrate(false);
        if(!empty($mobileConditions_query)){
            $mobileConditions = $mobileConditions_query->toArray();
        }else{
            $mobileConditions = array();
        }
		$functionConditions_query = $this->FunctionConditions->find('list',[
                                                                        'conditions' => ['FunctionConditions.status' => 1],
                                                                        'keyField' => 'id',
                                                                        'valueField' => 'function_condition'
                                                                     ]
                                                              );
        $functionConditions_query = $functionConditions_query->hydrate(false);
        if(!empty($functionConditions_query)){
            $functionConditions = $functionConditions_query->toArray();
        }else{
            $functionConditions = array();
        }
		$problemTypeOptions_query = $this->ProblemTypes->find('list',[
                                                                    'keyField' => 'id',
                                                                    'valueField' => 'problem_type'
                                                               ]
                                                        );
        $problemTypeOptions_query = $problemTypeOptions_query->hydrate(false);
        if(!empty($problemTypeOptions_query)){
            $problemTypeOptions = $problemTypeOptions_query->toArray();
        }else{
            $problemTypeOptions = array();
        }
		if (!$this->MobileRepairs->exists($id)) {
			throw new NotFoundException(__('Invalid mobile repair'));
		}
		$options = array('conditions' => array('MobileRepairs.id' => $id),'contain' => 'Kiosks');
		$mobilerepairs_query =  $this->MobileRepairs->find('all', $options);
        $mobilerepairs_query = $mobilerepairs_query->hydrate(false);
        if(!empty($mobilerepairs_query)){
            $mobilerepairs = $mobilerepairs_query->first();
        }else{
            $mobilerepairs = array();
        }
        $this->set('mobileRepair',$mobilerepairs);
		
		$repairLogs_query = $this->MobileRepairLogs->find('all',array(
							'conditions' => array('MobileRepairLogs.mobile_repair_id' => $id),
							'order' => array('MobileRepairLogs.id DESC')
								)
							   );
        $repairLogs_query = $repairLogs_query->hydrate(false);
        if(!empty($repairLogs_query)){
			//echo'hi';die;
			//pr($repairLogs_query);die;
            $repairLogs = $repairLogs_query->toArray();
        }else{
            $repairLogs = array();
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
		
		$kiosks_query = $this->MobileRepairs->Kiosks->find('list',[
                                                                'fields' => array('id', 'name'),
                                                                'conditions' => ['Kiosks.status' => 1]
                                                            ]
                                                    );
        $kiosks_query = $kiosks_query->hydrate(false);
        if(!empty($kiosks_query)){
            $kiosks = $kiosks_query->toArray();
        }else{
            $kiosks = array();
        }
		
		$comments_query = $this->MobileRepairs->CommentMobileRepairs->find('all', array(
									 'conditions' => array('CommentMobileRepairs.status' => 1,'CommentMobileRepairs.mobile_repair_id' => $id),
                                     'contain' => 'Users',
									 'order' => array('CommentMobileRepairs.id DESC'),
									 'limit' => 5
									));
        $comments_query = $comments_query->hydrate(false);
        if(!empty($comments_query)){
            $comments = $comments_query->toArray();
        }else{
            $comments = array();
        }
		$brands_query = $this->MobileRepairs->Brands->find('list',[
                                                                'keyField' => 'id',
                                                                'valueField' => 'brand',
																'order'=>'brand asc',
                                                                'conditions' => ['Brands.status' => 1]
                                                            ]
                                                    );
        $brands_query = $brands_query->hydrate(false);
        if(!empty($brands_query)){
            $brands = $brands_query->toArray();
        }else{
            $brands = array();
        }
		$mobileModels_query = $this->MobileRepairs->MobileModels->find('list',[
                                                                            'fields' => array('id', 'model'),
																			'order'=>'model asc',
                                                                            'conditions' => ['MobileModels.status' => 1]
                                                                        ]
                                                                );
        $mobileModels_query = $mobileModels_query->hydrate(false);
        if(!empty($mobileModels_query)){
            $mobileModels = $mobileModels_query->toArray();
        }else{
            $mobileModels = array();
        }
		
		$viewRepairParts_query = $this->MobileRepairParts->find('all',array(
								'conditions' => array('MobileRepairParts.mobile_repair_id' => $id)
								)
							);
        $viewRepairParts_query = $viewRepairParts_query->hydrate(false);
        if(!empty($viewRepairParts_query)){
            $viewRepairParts = $viewRepairParts_query->toArray();
        }else{
            $viewRepairParts = array();
        }
		$products_query = $this->Products->find('list',[
                                                    'keyField' => 'id',
                                                    'valueField' => 'product'
                                                 ]
                                          );
        $products_query = $products_query->hydrate(false);
        if(!empty($products_query)){
            $products = $products_query->toArray();
        }else{
            $products = array();
        }
		
		$dataPerId_query = $this->MobileRepairs->find('all',array(
							'conditions' => array('MobileRepairs.id'=>$id),
                            ));
        $dataPerId_query = $dataPerId_query->hydrate(false);
        if(!empty($dataPerId_query)){
            $dataPerId = $dataPerId_query->first();
        }else{
            $dataPerId = array();
        }
		$brandId = $dataPerId['brand_id'];
		$mobileModelId = $dataPerId['mobile_model_id'];
		$problemTypStr = $dataPerId['problem_type'];
		$problemTypArr = explode("|",$problemTypStr);
		foreach($problemTypArr as $p => $problemTyp){
            
            $repairDays_conn = ConnectionManager::get('default');
            $repairDays_stmt = $repairDays_conn->execute("SELECT `repair_days` from `mobile_repair_prices` WHERE `brand_id`='$brandId' AND `mobile_model_id`='$mobileModelId' AND `problem_type`='$problemTyp'"); 
            $repairDays[] = $repairDays_stmt ->fetchAll('assoc');
           
		}
		
		$repairDayz = $this->repairDayz;//default
		
		if(!empty($repairDays[0]['mobile_repair_prices']['repair_days'])){
			$repairDayz['repair_days_a'] = $repairDays[0]['mobile_repair_prices']['repair_days'];	
		}
		
		if(!empty($repairDays[1]['mobile_repair_prices']['repair_days'])){
			$repairDayz['repair_days_b'] = $repairDays[1]['mobile_repair_prices']['repair_days'];	
		}
		
		if(!empty($repairDays[2]['mobile_repair_prices']['repair_days'])){
			$repairDayz['repair_days_c'] = $repairDays[2]['mobile_repair_prices']['repair_days'];	
		}
				//pr($_SESSION);die;	
		$maxRepairDays = max($repairDayz);
			if($this->request->is('post') || $this->request->Session()->read('payment_confirmation.payment_status') == $id."_manager_repair_edit"){
				
				$this->request->Session()->delete('payment_confirmation');
                //pr($this->request);die;
				if(count($this->request->data)){
					$repairId = $this->request['data']['id'];
					$updatedStatus = $this->request['data']['status'];
					//$estimateCost = $this->request['data']['MobileRepair']['estimated_cost'];
					$kiosk_id = $this->request['data']['kiosk_id'];//kiosk id of the original kiosk
				}
				//echo "hi";die;
				if(is_array($this->request->data) && !empty($this->request->data)){
					//pr($_SESSION);
					//echo $this->request->data['status'];die;
					//pr($this->request);die;
					//echo "here";
					//Case I
					if($this->request->data['status'] == DELIVERED_UNREPAIRED_BY_KIOSK || $this->request->data['status'] == DELIVERED_UNREPAIRED_BY_TECHNICIAN){
                        //echo'1';die;
						//updating the amount to 0 sale on moving from delivered repaired to delivered unrepaired
						$sale_info_query = $this->MobileRepairSales->find('all', array('conditions' => array( 'MobileRepairSales.mobile_repair_id' => $id, 'MobileRepairSales.refund_status' => 0, 'MobileRepairSales.amount > 0'), 'order' => 'MobileRepairSales.id desc'));
                        $sale_info_query = $sale_info_query->hydrate(false);
                        if(!empty($sale_info_query)){
                            $sale_info = $sale_info_query->first();
                        }else{
                            $sale_info = array();
                        }
						$repair_status = $this->request->data['status'];
						if(count($sale_info)){
                            $array_data = array('amount' => 0 , 'repair_status' => $repair_status);
							$sale_ide = $sale_info['id'];
							$g_id = $this->MobileRepairSales->get($sale_ide);
							$p_entity = $this->MobileRepairSales->patchEntity($g_id,$array_data,['validate' => false]);
							$this->MobileRepairSales->save($p_entity);
						}
				
						$conn_query = ConnectionManager::get('default');
                        $stmt_query = $conn_query->execute("DELETE FROM `repair_payments` WHERE `mobile_repair_id` = '$id'"); 

                        //$this->MobileRepairSale->query("DELETE FROM `repair_payments` WHERE `mobile_repair_id` = '$id'");
					}elseif(($this->request->data['status'] == DELIVERED_REPAIRED_BY_KIOSK || $this->request->data['status'] == DELIVERED_REPAIRED_BY_TECHNICIAN) && $dataPerId['internal_repair'] != 1){
                        //echo'2';die;
					//Case II
						//***status update to delivered repaired by center or kiosk from here
							//check if payment entry exists in table else, take payment
							$checkIfPmtExists_query = $this->RepairPayments->find('all', array('conditions' => array('RepairPayments.mobile_repair_id' => $id)));
                            $checkIfPmtExists = $checkIfPmtExists_query->count();
							//
							//pr($dataPerId);die;
							//this is for rebook case, we are checking if payment is taken for atleast once
							$exempted = $this->checkIfExempted($id, $dataPerId['status_rebooked']);
							if($checkIfPmtExists == 0 || $exempted == 0){//payment was never taken for this repair id
								//take to payment screen
								$Data = $this->request->data;
								$Data['booked_by'] = $dataPerId['booked_by'];
								$Data['created'] = $dataPerId['created'];
								$this->request->Session()->write('manager_edit_repair_data',$Data);
								return $this->redirect(array('action' => 'repairPayment'));
								die;
							}
					}
				}elseif(count($this->request->data) == 0 && is_array($this->request->Session()->read('manager_edit_repair_data')) && $this->request->Session()->read('manager_edit_repair_data.id') == $id){
					//echo'3';die;
					//condition: when request is coming from payment screen
					//check if sale exists for this id
					//if not insert a new sale
					$managerEditSession = $this->request->Session()->read('manager_edit_repair_data');
					$checkIfSaleExists_query = $this->MobileRepairSales->find('all', array('conditions' => array( 'MobileRepairSales.mobile_repair_id' => $id)));
                    $checkIfSaleExists = $checkIfSaleExists_query->count();
					//pr($dataPerId);
					//pr($this->Session->read());die;
					//if($checkIfSaleExists == 0){
						//insert sale
					$countSale = 0;
					//pr($managerEditSession);die;
					if(($managerEditSession['status'] == DELIVERED_REPAIRED_BY_KIOSK || $managerEditSession['status'] == DELIVERED_REPAIRED_BY_TECHNICIAN) && $checkIfSaleExists > 0){
                        //echo'4';die;
						//getting total payment amount from session
						$finalAmount = 0;
						$costArr = explode('|',$managerEditSession['estimated_cost']);
						foreach($costArr as $key => $cost){
							$finalAmount+=$cost;
						}
						
						$saleInfo_query = $this->MobileRepairSales->find('all', array('conditions' => array( 'MobileRepairSales.mobile_repair_id' => $id, 'MobileRepairSales.refund_status' => 0, 'MobileRepairSales.amount > 0'), 'order' => 'MobileRepairSales.id desc'));
                        $saleInfo_query = $saleInfo_query->hydrate(false);
                        if(!empty($saleInfo_query)){
                            $saleInfo = $saleInfo_query->first();
                        }else{
                            $saleInfo = array();
                        }
						if(count($saleInfo)){
                            //echo'5';die;
                            $data_array = array('amount' => $finalAmount,'repair_status' => $managerEditSession['status']);
							$sale_ide = $saleInfo['id'];
							$id_g = $this->MobileRepairSales->get($sale_ide);
							$patch_e = $this->MobileRepairSales->patchEntity($id_g,$data_array,['validate' => false]);
							if($this->MobileRepairSales->save($patch_e)){
								//updating the sale id in payment
								//$this->RepairPayment->updateAll(array('mobile_repair_sale_id' => "'$sale_ide'"),array('RepairPayment.mobile_repair_id' => $id));
								$query = "UPDATE `repair_payments` SET `mobile_repair_sale_id` = '$sale_ide' WHERE `mobile_repair_id` = $id";
                                $RepairPayment_conn = ConnectionManager::get('default');
                                $RepairPayment_stmt = $RepairPayment_conn->execute($query);
                                
								//-------------------------adding new code Aug 25----------------
								$paymentDetails_query = $this->RepairPayments->find('all', array(
													'conditions' => array('RepairPayments.mobile_repair_sale_id' => $id),
																));
                                $paymentDetails_query = $paymentDetails_query->hydrate(false);
                                if(!empty($paymentDetails_query)){
                                    $paymentDetails = $paymentDetails_query->toArray();
                                }else{
                                    $paymentDetails = array();
                                }
								if(empty($paymentDetails)){
									//delete sale and redirect to index page
									$id_G = $this->MobileRepairSales->get($id);
									$this->MobileRepairSales->delete($id_G);
									$alterQuery = "ALTER TABLE `mobile_re_sales` AUTO_INCREMENT = $id";
                                    $MobileRepairSale_conn = ConnectionManager::get('default');
                                    $MobileRepairSale_stmt = $MobileRepairSale_conn->execute($alterQuery);
                                    
									$this->Flash->error("Failed to fire query: $query. <br/>For this reason generated sale delelted for Mobile Repair Sale ID: {$id} and receipt counter is again set to $id for maintaining sequences<br/>Please take screenshot of this bug and report to admin",['escape'=>false]);
									return $this->redirect(array('action' => 'index'));
								}
								//---------------------------------------------------------------
								$countSale++;
							}
						}else{
							//17.03.2016 generating a new sale in case of changing the status to successful repair delivery
							$mobileRepairSalesData = array(
								'kiosk_id' => $dataPerId['kiosk_id'],
								'mobile_repair_id' => $id,
								'sold_by' => $dataPerId['booked_by'],
								'sold_on' => date('Y-m-d h:i:s A'),
								'refund_by' => '',
								'amount' => $finalAmount,
								'refund_amount' => '',
								'refund_status' => 0,
								'refund_on' => '',
								'refund_remarks' => '',
								'repair_status' => $managerEditSession['status']
							);
							$new_entity = $this->MobileRepairSales->newEntity();
                            $Patch_entity = $this->MobileRepairSales->patchEntity($new_entity,$mobileRepairSalesData,['validate' => false]);
							if($this->MobileRepairSales->save($Patch_entity)){
								$sale_ide = $Patch_entity->id;
								//updating the sale id in payment
								//$this->RepairPayment->updateAll(array('mobile_repair_sale_id' => "'$sale_ide'"),array('RepairPayment.mobile_repair_id' => $id));
								$query = "UPDATE `repair_payments` SET `mobile_repair_sale_id` = '$sale_ide' WHERE `mobile_repair_id` = $id";
                                $RepairPayment_conn = ConnectionManager::get('default');
                                $RepairPayment_stmt = $RepairPayment_conn->execute($query);
								//-------------------------adding new code Aug 25----------------
								$paymentDetails_query = $this->RepairPayments->find('all', array(
													'conditions' => array('RepairPayments.mobile_repair_sale_id' => $id),
																));
								$paymentDetails_query = $paymentDetails_query->hydrate(false);
								if(!empty($paymentDetails_query)){
									$paymentDetails = $paymentDetails_query->toArray();
								}else{
									$paymentDetails = array();
								}
								if(empty($paymentDetails)){
									//delete sale and redirect to index page
									$Id_get = $this->MobileRepairSales->get($id);
									$this->MobileRepairSales->delete($Id_get);
									$alterQuery = "ALTER TABLE `mobile_re_sales` AUTO_INCREMENT = $id";
                                    $MobileRepairSales_conn = ConnectionManager::get('default');
                                    $MobileRepairSales_stmt = $MobileRepairSales_conn->execute($alterQuery);
									$this->Flash->error("Failed to fire query: $query. <br/>For this reason generated sale delelted for Mobile Repair Sale ID: {$id} and receipt counter is again set to $id for maintaining sequences<br/>Please take screenshot of this bug and report to admin");
									return $this->redirect(array('action' => 'index'));
								}
								//---------------------------------------------------------------
								$countSale++;
							}
						}
					}
					
					if($countSale > 0){
						//assigning the value of session to $this->request->data
						$this->request->data = $managerEditSession;
						
						//deleting the session
						$this->request->Session()->delete('manager_edit_repair_data');
						//pr($this->request);die;
						$repairId = $this->request['data']['id'];
						$updatedStatus = $this->request['data']['status'];
						//$estimateCost = $this->request['data']['MobileRepair']['estimated_cost'];
						$kiosk_id = $this->request['data']['kiosk_id'];//kiosk id of the original kiosk
					}else{
						$this->Flash->error("Sale could not be saved for repair id $id!");
						return $this->redirect(array('action' => 'manager_edit', $id));
						die;
					}
				}
				//pr($_SESSION);die;
                    $dataArr = array('status' => $updatedStatus);
					$id_get = $this->MobileRepairs->get($repairId);
                    $entity_patch = $this->MobileRepairs->patchEntity($id_get,$dataArr,['validate' => false]);
					if($this->MobileRepairs->save($entity_patch)){
						if($updatedStatus == 2){
                            $new_dataArr = array('status_rebooked' => 1);
                            $idGet = $this->MobileRepairs->get($repairId);
                            $entityPatch = $this->MobileRepairs->patchEntity($idGet,$new_dataArr,['validate' => false]);
							$this->MobileRepairs->save($entityPatch);
						}
						$mobileRepairLogsData = array(
						'kiosk_id' => $kiosk_id,
						'user_id' => $this->request->Session()->read('Auth.User.id'),
						'mobile_repair_id' => $repairId,					
						'repair_status' => $updatedStatus
						);			
										
						$new_entity = $this->MobileRepairLogs->newEntity();
                        $patch_Entity = $this->MobileRepairLogs->patchEntity($new_entity,$mobileRepairLogsData,['validate' => false]);
						$this->MobileRepairLogs->save($patch_Entity);
						
				//**********code for sending email starts from here
						$repair_email_message = $this->setting['repair_email_message'];
						//pr($dataPerId);die;
                        $repairBookingData = $dataPerId;
						
						$mobile_condition_array = array();
						$mobile_condition_remark = $repairBookingData['mobile_condition_remark'];
						$mobile_condition_array = explode("|",$repairBookingData['mobile_condition']);
						
						$function_condition_array = array();
						//pr($repairBookingData);die;
						if(array_key_exists('function_condition',$repairBookingData)){
							$function_condition = $repairBookingData['function_condition'];
							if(!empty($function_condition)){
								$function_condition_array = explode("|",$function_condition);
							}
						}
						
						if($this->setting['function_test_notification'] == 'active'){
							if(count($function_condition_array)){
								$funcConditionArr = array();
								foreach($function_condition_array as $fc => $function_condition_id){
									if(array_key_exists($function_condition_id, $functionConditions))
										$funcConditionArr[] = $functionConditions[$function_condition_id];
								}
								$funcConditionStr = "<br/><br/>**Phone's function test(at the time of booking): ".implode(", ", $funcConditionArr).".";
							}else{
								$funcConditionStr = '';
							}
						}else{
							$funcConditionStr = '';
						}
							
						if($this->setting['phone_condition_notification'] == 'active'){
							if(count($mobile_condition_array)){
								$phoneConditionArr = array();
								foreach($mobile_condition_array as $mc => $mobile_condition_id){
									//1000 is for others
									if($mobile_condition_id == '1000'){
										$phoneConditionArr[] = $mobile_condition_remark;
									}else{
										if(array_key_exists($mobile_condition_id, $mobileConditions))
											$phoneConditionArr[] = $mobileConditions[$mobile_condition_id];
									}
								}
								$phoneConditionStr = "<br/><br/>**Phone condition(at the time of booking): ".implode(", ", $phoneConditionArr).".";
							}else{
								$phoneConditionStr = '';
							}
						}else{
							$phoneConditionStr = '';
						}
							
						$countryOptions = Configure::read('uk_non_uk');
						//pr($repairDays);die;
                        //pr($repairDays);
						if(!empty($repairDays[0][0]['repair_days'])){
							$repairBookingData['repair_days_a'] = $repairDays[0][0]['repair_days'];	
						}
						
						if(!empty($repairDays[1][0]['repair_days'])){
							$repairBookingData['repair_days_b'] = $repairDays[1][0]['repair_days'];	
						}
						
						if(!empty($repairDays[2][0]['repair_days'])){
							$repairBookingData['repair_days_c'] = $repairDays[2][0]['repair_days'];	
						}
						
						$repairDays = array();
                        //pr($repairBookingData);die;
						if(!empty($repairBookingData['repair_days_a'])){
							$repairDays[] = $repairBookingData['repair_days_a'];
						}
						if(!empty($repairBookingData['repair_days_b'])){
							$repairDays[] = $repairBookingData['repair_days_b'];
						}
						if(!empty($repairBookingData['repair_days_c'])){
							$repairDays[] = $repairBookingData['repair_days_c'];
						}
						
						$repairStatus = $updatedStatus;
						$kioskAddressArr = $this->kiosk_address($dataPerId['kiosk_id']);
						//pr($repairDays);die;
						//code for getting statement for email
						$statementArray = $this->get_email_statement($repairStatus, $repairBookingData, $mobileModels, max($repairDays), $phoneConditionStr, $funcConditionStr, $kiosks, $kioskAddressArr, $repair_email_message, $dataPerId);
						$statement = $statementArray['statement'];
						$messageStatement = $statementArray['messageStatement'];
						$send_by_email = Configure::read('send_by_email');
						$emailSender = Configure::read('EMAIL_SENDER');
						if(!empty($statement)){
							if($this->request->session()->read('Auth.User.group_id') == MANAGERS || $this->request->session()->read('Auth.User.group_id') == ADMINISTRATORS || $this->request->session()->read('Auth.User.group_id') == FRANCHISE_OWNER){
								if($this->request['data']['send'] == '1'){
									if(!empty($dataPerId['customer_contact'])){
										$destination = $dataPerId['customer_contact'];
										if(!empty($messageStatement)){
											$this->TextMessage->test_text_message($destination, $messageStatement);
										}
									}
									if(!empty($emailTo)){
										$Email = new Email();
										$Email->config('default');
										$Email->viewVars(array('statement' => $statement));
										//$Email->config(array('from' => 'me@example.org', 'transport' => 'MyCustom'));
										//$Email = new CakeEmail(array('from' => 'me@example.org', 'transport' => 'MyCustom'));
										$emailTo = $repairBookingData['customer_email'];
										$Email->template('repair_booking_receipt');
										$Email->emailFormat('both');
										$Email->to($emailTo);
										 $Email->transport(TRANSPORT);
										$Email->from([$send_by_email => $emailSender]);
										//$Email->sender("sales@oceanstead.co.uk");
										$Email->subject('Mobile Repair Details');
										$Email->send();
									}
									
								}
							}else{
								if(!empty($dataPerId['customer_contact'])){
									$destination = $dataPerId['customer_contact'];
									if(!empty($messageStatement)){
										$this->TextMessage->test_text_message($destination, $messageStatement);
									}
								}
								if(!empty($emailTo)){
									$Email = new Email();
									$Email->config('default');
									$Email->viewVars(array('statement' => $statement));
									//$Email->config(array('from' => 'me@example.org', 'transport' => 'MyCustom'));
									//$Email = new CakeEmail(array('from' => 'me@example.org', 'transport' => 'MyCustom'));
									$emailTo = $repairBookingData['customer_email'];
									$Email->template('repair_booking_receipt');
									$Email->emailFormat('both');
									$Email->to($emailTo);
									 $Email->transport(TRANSPORT);
									$Email->from([$send_by_email => $emailSender]);
									//$Email->sender("sales@oceanstead.co.uk");
									$Email->subject('Mobile Repair Details');
									$Email->send();
								}
								
							}
						}
			//code for sending email ends here*******************
				
				}
				$this->Flash->success("Status has been successfully updated for repair id:$repairId");
				return $this->redirect(array('action'=>'index'));
			}	
		
		$this->set(compact('brands','mobileModels','comments','repairLogs','users','kiosks','viewRepairParts','products','maxRepairDays','problemTypeOptions'));		
	}
    
    public function repairPayment(){
		//echo'hi';die;
		//pr($this->Session->read('received_reprd_from_tech_data'));
		//$this->RepairPayment->query('TRUNCATE `repair_payments`');
		//if(AuthComponent::user('group_id') != KIOSK_USERS){
		//	$this->Session->setFlash('Only kiosk user can authorize/enter payment');
		//	return $this->redirect(array('action' => 'index'));
		//}
		//pr($this->RepairPayment->find('all'));
		//pr($this->request);
		$paymentType = array('Cash' => 'Cash', 'Card' => 'Card');
		$this->set(compact('paymentType'));
		$sessionBskt = array();
		if(is_array($this->request->Session()->read('final_parts_basket'))){
			$basket = "final_parts_basket";
			$session_basket = $this->request->Session()->read('final_parts_basket');
			$sessionRepairId = $session_basket['repair_id'];
			$userId = $this->Auth->user('id');
			$kioskId = $this->request->Session()->read('kiosk_id');
			$redirect = array('controller'=>'mobile_repairs','action'=>'edit',$sessionRepairId);
			$sessionBskt['payment_status'] = $sessionRepairId;//for sending identification of successfull payment
		}elseif(is_array($this->request->Session()->read('manager_edit_repair_data'))){
			//case for manager edit
			$basket = "manager_edit_repair_data";
			$session_basket = $this->request->Session()->read('manager_edit_repair_data');
			$sessionRepairId = $session_basket['id'];
			$userId = $session_basket['booked_by'];
			$kioskId = $session_basket['kiosk_id'];
			$redirect = array('controller'=>'mobile_repairs','action'=>'manager_edit',$sessionRepairId);
			$sessionBskt['payment_status'] = $sessionRepairId."_manager_repair_edit";//for sending identification of successfull payment
		}elseif(is_array($this->request->Session()->read('received_reprd_from_tech_data'))){
			$basket = "received_reprd_from_tech_data";
			$session_basket = $this->request->Session()->read('received_reprd_from_tech_data');
			$sessionRepairId = $session_basket['id'];
			$userId = $this->Auth->user('id');
			$kioskId = $this->request->Session()->read('kiosk_id');
			$redirect = array('controller'=>'mobile_repairs','action'=>'edit',$sessionRepairId);
			$sessionBskt['payment_status'] = $sessionRepairId;//for sending identification of successfull payment
		}
		
		
		if ($this->request->is(array('post', 'put'))) {
			if(array_key_exists('cancel',$this->request->data)){
				if(isset($basket) && isset($redirect)){
					$this->request->Session()->delete($basket);
					return $this->redirect($redirect);
					die;
				}else{
					return $this->redirect(array('controller'=>'mobile_repairs','action'=>'index'));
				}
			}
			/*$amountToPay = $this->request['data']['final_amount'];*/
			$priceFlds_query = $this->MobileRepairs->find('all', array(
														'fields' => array('estimated_cost' ),
														'conditions' => array('MobileRepairs.id' => $sessionRepairId)
														  ));
			$priceFlds_query = $priceFlds_query->hydrate(false);
			if(!empty($priceFlds_query)){
				$priceFlds = $priceFlds_query->first();
			}else{
				$priceFlds = array();
			}
			//pr($priceFlds);die;
			//we are not relying on $this->request['data']['final_amount'] this parameter rather we grabbed it again from database.
			//pr($priceFlds);die;
			$amountToPay = $priceFlds['estimated_cost'];
			$amountToPayArr = explode("|",$amountToPay);
			$totalRepAmt = 0;
			foreach($amountToPayArr as $repairAmt){
				$totalRepAmt+=$repairAmt;
			}
			$amountToPay = number_format($totalRepAmt,2);
			$totalPaymentAmount = 0;
			$amountDesc = array();
			$error = '';
			$errorStr = '';
			
			foreach($this->request['data']['Payment']['Amount'] as $key => $paymentAmount){
				$totalPaymentAmount+= (float)$paymentAmount;
				$paymentDescription = $this->request['data']['Payment']['Description'][$key];
			}
			
			//Part payment Amount($totalPaymentAmount) comparison with total receipt amount($amountToPay)
			foreach($this->request['data']['Payment']['Payment_Method'] as $key => $paymentMethod){
				if($totalPaymentAmount < $amountToPay){
					$error[] = "1Amount must be equivalent to &#163; {$amountToPay}. Please try again";
					break;
				}elseif($totalPaymentAmount > $amountToPay){
					$error[] = "2Amount must be equivalent to &#163; {$amountToPay}. Please try again";
					break;
				}
			}
			
			if(!empty($error)){
				$errorStr = implode("<br/>",$error);
				$this->Flash->error($errorStr);
				return $this->redirect(array('controller'=>'MobileRepairs','action'=>'repair_payment'));
			}
			
			$counter = 0;
			foreach($this->request['data']['Payment']['Amount'] as $key => $paymentAmount){
				if(empty($paymentAmount))continue;
				$paymentMethod = $this->request['data']['Payment']['Payment_Method'][$key];
				$paymentDescription = $this->request['data']['Payment']['Description'][$key];
				$payment_status = 1;//since we do not have option for credit here, so just sending status 1 as payment done
				
				if(!empty($paymentAmount)){// && $paymentDescription
					$paymentDetailData = array(
												'kiosk_id' => $kioskId,
												'user_id' => $userId,
												'mobile_repair_id' => $sessionRepairId,
												'payment_method' => $paymentMethod,
												'description' => $paymentDescription,
												'amount' => $paymentAmount,
												'payment_status' => $payment_status,
												'status' => 1,//this 1 currently does not have any relevance
											);
					$newEntity = $this->RepairPayments->newEntity();
					$patchEntity = $this->RepairPayments->patchEntity($newEntity,$paymentDetailData,['validate' => false]);
					//validating repair payment amount and should not be greater than $amountToPay
					$reprPmtAmts_query = $this->RepairPayments->find('all',['conditions'=>['RepairPayments.mobile_repair_id' => $sessionRepairId]]);
						$reprPmtAmts_query
							->select(['totalAmt'=>$reprPmtAmts_query->func()->sum('amount')]);
					$reprPmtAmts_query = $reprPmtAmts_query->hydrate(false);
					if(!empty($reprPmtAmts_query)){
						$reprPmtAmts = $reprPmtAmts_query->first();
					}else{
						$reprPmtAmts = array();
					}
					//pr($reprPmtAmts);die;
					$totalAmt = $reprPmtAmts['totalAmt'];
					if($totalAmt == $amountToPay){
						$this->request->Session()->write('payment_confirmation',$sessionBskt); // updated on 23/12/2016 ($totalAmt >= $amountToPay) diveied to two parts
						$counter++;
					}elseif($totalAmt > $amountToPay){
						$counter++;
					}else{ //echo "helo";
						if($this->RepairPayments->save($patchEntity)){
							$rand = rand(500,10000);
							//mail('kalyanrajiv@gmail.com', "Line Controller MR #4560- $rand","$redirect");
							$counter++;
							//$sessionBskt['payment_status'] = $sessionRepairId;
							//here we are sending payment status in session to repair edit as an identifier for successful payment
							$this->request->Session()->write('payment_confirmation',$sessionBskt);
						}
					}
				}
			}
			
			if($counter > 0){
				if(is_array($this->request->Session()->read('manager_edit_repair_data'))){
					//pr($this->request);
					//pr($_SESSION);die;
				}
				return $this->redirect($redirect);
			}else{
				$flashMessage = ("Sale could not be created. Please try again");
				$this->request->Session()->delete($basket);
				$this->Flash->error($flashMessage);
				return $this->redirect($redirect);
			}
		}//echo'hi';die;
	}
	
	private function update_Repair_Payment($dataPayment = array(), $id = ''){//being used in edit()
		//update repair payment (when payment is already done and we need to make change to it: http://www.boloram.co.uk/mobile_repairs/update_repair_payment/4606)
		$ttlAmount = 0;
		$updatedPaymentData = $dataPayment['UpdatePayment'];
		$updatedAmountData = $dataPayment['updated_amount'];
		$sale_amount = $dataPayment['sale_amount'];
		$addedAmount = 0;
		if(array_key_exists('added_amount',$dataPayment)){
			$addedAmount = $dataPayment['added_amount'];
		}
		
		foreach($updatedPaymentData as $paymentId => $paymentMode){
			$ttlAmount+= $updatedAmountData[$paymentId];
		}
		
		$ttl_amount = $addedAmount+$ttlAmount;
		
		if($ttl_amount != $sale_amount){
			//validation check
			$this->Flash->error('Payment could not be updated!');
			return $this->redirect(array('action' => 'edit',$id));
			die;
		}
		
		$saveAdminPayment = 0;
		//****saving newly added payment amount through the new row that creates on clicking of + (javascript)
		if(array_key_exists('added_amount',$dataPayment) && is_numeric($dataPayment['added_amount'])){
			//here we are fetching the existing payment row corresponding to this repair id. We are unsetting the fields for the new payment row and using the same payment row (fields) of the existing repair i.e mobile_repair_sale_id, mobile_repair_id, kiosk_id etc.
			$paymntData_query = $this->RepairPayments->find('all',array(
						'conditions' => array('RepairPayments.mobile_repair_id'=>$id)
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
			unset($paymntData['created']);
			unset($paymntData['modified']);
			
			//adding new fields
			$paymntData['payment_method'] = $dataPayment['new_change_mode'];
			$paymntData['amount'] = $dataPayment['added_amount'];
			$RepairPaymentsEntity = $this->RepairPayments->newEntity($paymntData,['validate' =>false]);
			$RepairPaymentsEntity = $this->RepairPayments->patchEntity($RepairPaymentsEntity,$paymntData,['validate' =>false]);
			if($this->RepairPayments->save($RepairPaymentsEntity)){
				$saveAdminPayment++;
			}
		}
		// saving new added payment till here*****
		
		//below code is for updating the existing payment row
		foreach($updatedPaymentData as $paymentId => $paymentMode){
			$RepairPaymentsEntity1 = $this->RepairPayments->get($paymentId);
			$paymentDetailData = array(
				//'id' => $paymentId,
				'payment_method' => $paymentMode,
				'amount' => $updatedAmountData[$paymentId]
				   );
			$RepairPaymentsEntity1 = $this->RepairPayments->patchEntity($RepairPaymentsEntity1,$paymentDetailData,['validate' => false]);
			if($this->RepairPayments->save($RepairPaymentsEntity1)){
				$saveAdminPayment++;
			}
		}
		
		if($saveAdminPayment > 0){
			$estimated_cost_array = json_decode($dataPayment['estimated_cost_array']);
			$problem_type_array = json_decode($dataPayment['problem_type_array']);
			$MobileRepairsEntity = $this->MobileRepairs->get($id);
			$data = array(
						  'estimated_cost' => implode("|",$estimated_cost_array),
						  'problem_type' => implode("|",$problem_type_array),
						  );
			$MobileRepairsEntity = $this->MobileRepairs->patchEntity($MobileRepairsEntity,$data,['validate' => false]);
			$this->MobileRepairs->save($MobileRepairsEntity);
			//$this->MobileRepairs->saveField('estimated_cost',implode("|",$estimated_cost_array));
			//$this->MobileRepairs->saveField('problem_type',implode("|",$problem_type_array));
			
			$sale_Info_query = $this->MobileRepairSales->find('all', array('conditions' => array( 'MobileRepairSales.mobile_repair_id' => $id,
																							 'MobileRepairSales.refund_status' => 0,
																							 'MobileRepairSales.amount > 0'),
																	   'order' => 'MobileRepairSales.id desc')
														);
			$sale_Info_query = $sale_Info_query->hydrate(false);
			if(!empty($sale_Info_query)){
				$sale_Info = $sale_Info_query->first();
			}else{
				$sale_Info = array();
			}
			if(count($sale_Info)){
				$sale_Ide = $sale_Info['id'];
				$MobileRepairSalesEntity = $this->MobileRepairSales->get($sale_Ide);
				$data_to_save = array(
									  'amount' => $sale_amount
									  );
				$MobileRepairSalesEntity = $this->MobileRepairSales->patchEntity($MobileRepairSalesEntity,$data_to_save,['validate' => false]);
				$this->MobileRepairSales->save($MobileRepairSalesEntity);
			}
			
							
			//$this->MobileRepairSale->updateAll(array('amount' => "'$sale_amount'"),
			//					array('MobileRepairSale.mobile_repair_id' => $id )
			//		 );
			
			$this->Flash->success('Payment has been successfully updated!');
			return $this->redirect(array('action' => 'index'));
		}else{
			$this->Flash->error('Payment could not be updated!');
			return $this->redirect(array('action' => 'edit',$id));
		}
		die;
	}
    
    public function exportUserRepairs(){
        
		$repairStatusTechnicianOptions = Configure::read('repair_statuses_technician');
		$problemTypeOptions_query = $this->ProblemTypes->find('list',[
                                                                    'keyField' => 'id',
                                                                    'valueField' => 'problem_type'
                                                               ]
                                                        );
        $problemTypeOptions_query = $problemTypeOptions_query->hydrate(false);
        if(!empty($problemTypeOptions_query)){
            $problemTypeOptions = $problemTypeOptions_query->toArray();
        }else{
            $problemTypeOptions = array();
        }
		$userID = $this->request->query['user_id'];
		$kioskID = $this->request->query['kiosk_id'];
		$serviceCenter = $this->request->query['service_center'];
		$startDate = $this->request->query['start_date'];
		$endDate = $this->request->query['end_date'];
		$kiosks_query = $this->Kiosks->find('list',[
                                                'keyField' => 'id',
                                                'valueField' => 'name'
                                             ]
                                      );
        $kiosks_query = $kiosks_query->hydrate(false);
        if(!empty($kiosks_query)){
            $kiosks = $kiosks_query->toArray();
        }else{
            $kiosks = array();
        }
		$condArr = array(
							"DATE(MobileRepairLogs.created) > '$startDate'" ,
							"DATE(MobileRepairLogs.created) < '$endDate'",
							'MobileRepairLogs.repair_status IN' => array(DISPATCHED_2_KIOSK_REPAIRED)
						);
		if(empty($userID) && empty($kioskID) && empty($serviceCenter)){
			;
		}
		if(!empty($userID)){
			$condArr[]['MobileRepairLogs.user_id'] = $userID;
		}
		if(!empty($kioskID)){
			$condArr[]['MobileRepairLogs.kiosk_id'] = $kioskID;
		}
		if(!empty($serviceCenter)){
			$condArr[]['MobileRepairLogs.service_center_id'] = $serviceCenter;
		}
		$repairData_query = $this->MobileRepairLogs->find('all', array('conditions' => $condArr));
		
		$repairData_query = $repairData_query->hydrate(false);
        if(!empty($repairData_query)){
            $repairData = $repairData_query->toArray();
        }else{
            $repairData = array();
        }
		
		foreach($repairData as $key => $repairLog){
			$userRepairIds[$repairLog['user_id']][] = $repairLog['mobile_repair_id'];
		}
		
		
		$repairDetail = array();
		$data = array();
		foreach($userRepairIds as $user_id => $userRepairArr){
            if(empty($userRepairArr)){
            $userRepairArr = array(0 => null);
            }
			$repairDetail_query = $this->MobileRepairs->find('all',array('conditions'=>array('MobileRepairs.id IN'=>$userRepairArr),'fields'=>array('id','brand_id','mobile_model_id','problem_type','net_cost','status_rebooked')));
            $repairDetail_query = $repairDetail_query->hydrate(false);
            if(!empty($repairDetail_query)){
                $repairDetail[$user_id] = $repairDetail_query->toArray();
            }else{
                $repairDetail[$user_id] = array();
            }
			if(empty($userRepairArr)){
                $userRepairArr = array(0 => null);
            }
            $data_query = $this->MobileRepairSales->find('all',array('conditions'=>array('MobileRepairSales.mobile_repair_id IN'=>$userRepairArr)));
            $data_query = $data_query->hydrate(false);
            if(!empty($data_query)){
                $data[] = $data_query->toArray();
            }else{
                $data[] = array();
            }
		}
		
		$i = 0;
		foreach($data[0] as $key => $value){
			$sale_data[$i]['repair_id'] = $value['mobile_repair_id'];
			$sale_data[$i]['refund_status'] = $value['refund_status'];
			$sale_data[$i]['amount'] = $value['amount'];
			$sale_data[$i]['refund_amount'] = $value['refund_amount'];
			$i = $i+1;
		}
		$final = array();
		foreach($sale_data as $key1 => $value1){
			if(array_key_exists($value1['repair_id'],$final)){
				if($final[$value1['repair_id']]['refund_status'] == 0 && $value1['amount'] != 0){
					$final[$value1['repair_id']]['refund_status'] = $value1['refund_status'];
					$final[$value1['repair_id']]['amount'] = $value1['amount'];
					$final[$value1['repair_id']]['refund_amount'] = $value1['refund_amount'];
				}
			}else{
				$final[$value1['repair_id']]['refund_status'] = $value1['refund_status'];
				$final[$value1['repair_id']]['amount'] = $value1['amount'];
				$final[$value1['repair_id']]['refund_amount'] = $value1['refund_amount'];
			}
		}
		
		foreach($userRepairIds as $user_id => $userRepairArr){
			if(empty($userRepairArr)){
                $userRepairArr = array(0 => null);
            }
            $partsResult_query = $this->MobileRepairParts->find('all',array('conditions'=>array('MobileRepairParts.mobile_repair_id IN'=>$userRepairArr)));
            $partsResult_query = $partsResult_query->hydrate(false);
            if(!empty($partsResult_query)){
                $partsResult[] = $partsResult_query->toArray();
            }else{
                $partsResult[] = array();
            }
		}
		$finalPart = array();
		if(!empty($partsResult)){
			foreach($partsResult[0] as $k => $partsUsed){
					$finalPart[$partsUsed['mobile_repair_id']][] = $partsUsed['product_id'];
			}
		}
		$products_query = $this->Products->find('list',[
                                                    'keyField' => 'id',
                                                    'valueField' => 'product'
                                                 ]
                                          );
        $products_query = $products_query->hydrate(false);
        if(!empty($products_query)){
            $products = $products_query->toArray();
        }else{
            $products = array();
        }
		//pr($products);die;
		foreach($repairDetail[$user_id] as $k => $repairDet){
			$mobile_model_ids[$repairDet['mobile_model_id']] = $repairDet['mobile_model_id'];
		}
		if(empty($mobile_model_ids)){
            $mobile_model_ids = array(0 => null);
        }
		$mobileModels_query = $this->MobileModels->find('list',[
                                                            'conditions' => ['MobileModels.id IN' => $mobile_model_ids],
                                                            'keyField' => 'id',
                                                            'valueField' => 'model',
															'order'=>'model asc'
                                                         ]
                                                  );
		$mobileModels_query = $mobileModels_query->hydrate(false);
        if(!empty($mobileModels_query)){
            $mobileModels = $mobileModels_query->toArray();
        }else{
            $mobileModels = array();
        }
		$check = array();
		$i = 0;
		$print_Arr = array();
		foreach($repairData as $key => $repairInfo){
			$cost_price = 0;
			
			$part_used = 0;
			if(array_key_exists($repairInfo['mobile_repair_id'],$finalPart)){
				$part_used = count($finalPart[$repairInfo['mobile_repair_id']]);
			}
			//$check = array();
			$problemType1 = $problemType2 = $problemType3 = "--";
					$problemTypeArr = array();
					if(array_key_exists($userID, $repairDetail) &&
					is_array($repairDetail[$userID])){
						$repair_detail = array_values($repairDetail);
						foreach($repair_detail[0] as $k => $repairDet){
							if($repairInfo['mobile_repair_id'] == $repairDet['id']){
								$problemTypeArr = explode("|",$repairDet["problem_type"]);
								if(array_key_exists('0',$problemTypeArr)){
									if(!empty($problemTypeArr[0])){
											$problemType1 = $problemTypeOptions[$problemTypeArr[0]];
									}
									else{
											$problemType1 = "";
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
								
								$modelName = $mobileModels[$repairDet["mobile_model_id"]];
								$cost_price = $repairDet['net_cost'];
								if(array_key_exists($repairDet['id'],$final)){
									$sale_price =  $final[$repairDet['id']]['amount'];
									$refundAmount = $final[$repairDet['id']]['refund_amount'];
								}else{
									$sale_price = 0;
									$refundAmount = 0;
								}
							}
						}
					}else{
						$modelName = 'id = '.$repairInfo['mobile_repair_id'];
					}
					if(!empty($repairInfo['service_center_id']) && array_key_exists($repairInfo['service_center_id'],$kiosks)){
						$serviceCenter = $kiosks[$repairInfo['service_center_id']];
					}else{
						$serviceCenter = '--';
					}
					if(in_array($repairInfo['mobile_repair_id'],$check)){
						//continue;
						$cost_price = $sale_price = $refundAmount = 0;
					}else{
						$check[] = $repairInfo['mobile_repair_id'];
					}
					$print_Arr[] = array(
										 'Dispatch Date' => date('Y-m-d h:i:s',strtotime($repairInfo['created'])),
										 'RepairId' => $repairInfo['mobile_repair_id'],
										 'Cost Price' => $cost_price,
										 'Selling Price' => $sale_price,
										 'Refund' => $refundAmount,
										 'kiosk_id' => $kiosks[$repairInfo['kiosk_id']],
										 'Model' => $modelName,
										 'Problem 1' => $problemType1,
										 'Problem 2' => $problemType2,
										 'Problem 3' => $problemType3,
										 'Parts used' => $part_used,
										 'repair_status' => $repairStatusTechnicianOptions[$repairInfo['repair_status']],
										);
		}
		rsort($print_Arr);
		$this->outputCsv('MobileRepair_'.time().".csv" ,$print_Arr);
		$this->autoRender = false;
	}
	
	public function adminData($search = ""){
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
			$searchArray['AND']['quantity >'] = '0';
			$productList_query = $productTable->find('all',array(
															'fields'=> array('product','product_code','quantity'),
															'recursive'=> -1,
															'conditions' => $searchArray
												)
						    );
			$productList_query->hydrate(false);
			$productList = $productList_query->toArray();
		}else{
			$productList_query = $productTable->find('all',array(
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
   
}


?>