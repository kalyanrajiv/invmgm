<?php
namespace App\Controller;

use App\Controller\AppController;
use App\Controller\NotFoundException;
use Cake\Event\Event;
use Cake\Core\Configure;
use Cake\Core\Configure\Engine\PhpConfig;
use Cake\Network\Request;
use Cake\ORM\TableRegistry;
use Cake\Mailer\Email;
use Cake\I18n;
use Cake\I18n\Time;
use Cake\Datasource\ConnectionManager;

class MobileUnlocksController  extends AppController{
    public $helpers = [
         'Paginator' => ['templates' => 'paginatortemplates']
         ];
     public function initialize()
    {
        parent::initialize();
        $unlockStatusUserOptions = Configure::read('unlock_statuses_user');
		//pr($unlockStatusUserOptions);die;
		$unlockStatusTechnicianOptions = Configure::read('unlock_statuses_technician');
		$countryOptions = Configure::read('uk_non_uk');
		$paymentType = array('Cash' => 'Cash', 'Card' => 'Card');
		$this->set(compact('unlockStatusUserOptions','unlockStatusTechnicianOptions','countryOptions','paymentType'));
        $this->loadComponent('ScreenHint');
		$this->loadComponent('TextMessage');
        $this->loadModel('MobileModels');
        $this->loadModel('Networks');
        $this->loadModel('MobileRepairs');
        $this->loadModel('Users');
        $this->loadModel('Kiosks');
        $this->loadModel('RepairPayments');
        $this->loadModel('MobileUnlocks');
		$this->loadModel('MobileUnlockSales');
		$this->loadModel('MobileUnlockLogs');
		$this->loadModel('MobileUnlockPrices');
        $this->loadModel('CommentMobileUnlocks');
		$this->loadModel('RetailCustomers');
		$this->loadModel('UnlockPayments');
		$this->loadModel('MobilePurchases');
		$this->loadModel('Brands');
		$this->loadModel('MobileTransferLogs');
		
		
		$CURRENCY_TYPE = Configure::read('CURRENCY_TYPE');
		$this->set(compact('CURRENCY_TYPE'));
    }
    
	public function index() {
		//$cookieKioskId = $this->request->session()->read('kiosk_id');
		//$this->set('cookieKioskId',$cookieKioskId);
		//deleting the sessions created during payment if user does not complete the process
		$this->request->Session()->delete('unlock_data_session');
		$this->request->Session()->delete('unlock_payment_confirmation');
		
		//unlock_status kiosk users: booked
		$kiosks_query = $this->Kiosks->find('list',
													[
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
		if(!empty($kiosk_id) && $this->request->session()->read('Auth.User.group_id') == KIOSK_USERS){
			$this->paginate = [
						'limit' => ROWS_PER_PAGE,
						'conditions' => [
						      'MobileUnlocks.kiosk_id' => $kiosk_id,
						],
						'order' => ['MobileUnlocks.id DESC'],
						'contain' => ['Kiosks','Networks','MobileModels']
					];
		}elseif($this->request->session()->read('Auth.User.group_id') == UNLOCK_TECHNICIANS){
			$this->paginate = [
						'conditions' => [
						'OR' => [
							['MobileUnlocks.status' => DISPATCHED_2_CENTER],
							['MobileUnlocks.status' => VIRTUALLY_BOOKED]
								]
							],
						'limit' => ROWS_PER_PAGE,
						'order' => ['MobileUnlocks.id DESC'],
						'contain' => ['Kiosks','Networks','MobileModels']
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
				$managerKiosk = $this->get_kiosk();
				if(!empty($managerKiosk)){
					$kiosk_id = $managerKiosk;
					$this->paginate = [
						'conditions' => [
						      'MobileUnlocks.kiosk_id IN' => $kiosk_id,
						],
						'limit' => ROWS_PER_PAGE,
						'order' => ['MobileUnlocks.id DESC'],
						'contain' => ['Kiosks','Networks','MobileModels']
						];
				}else{
					$this->paginate = [
						'conditions' => [
						      'MobileUnlocks.kiosk_id IN' => array(0 => null),
						],
						'limit' => ROWS_PER_PAGE,
						'order' => ['MobileUnlocks.id DESC'],
						'contain' => ['Kiosks','Networks','MobileModels']
						];
				}
			}else{
				$this->paginate = [
						'limit' => ROWS_PER_PAGE,
						'order' => ['MobileUnlocks.id DESC'],
						'contain' => ['Kiosks','Networks','MobileModels']
						];
			}
		}
		$mobileUnlocks_query = $this->paginate("MobileUnlocks");
		$mobileUnlocks = $mobileUnlocks_query->toArray();
		$hint = $this->ScreenHint->hint('mobile_unlocks','index');
					if(!$hint){
						$hint = "";
					}
					
		$this->set(compact('hint','kiosks'));
		$this->set('mobileUnlocks',$mobileUnlocks);
		$this->render('index');
	}
	
    public function unlockTechnicianReport(){
		$userName_query = $this->Users->find('list',[
                                               'keyField' => 'id',
                                                'valueField' => 'username'
                                              ]);
        if(!empty($userName_query)){
            $userName = $userName_query->toArray();
        }else{
            $userName = array();
        }
		#pr($this->MobileRepairLog->find('all',array('order' => 'MobileRepairLog.id DESC','recursive'=>-1)));
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
                                                      'conditions'=>['Kiosks.kiosk_type'=>3],
                                                      'order' => ['Kiosks.name asc']
                                                      ]);
        if(!empty($serviceCenters_query)){
            $serviceCenters = $serviceCenters_query->toArray();
        }else{
            $serviceCenters = array();
        }
		$users_query = $this->Users->find('list',[
                                            'conditions'=>['Users.group_id'=>8],
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
				$unlockData_query = $this->MobileUnlockLogs->find('all',array('conditions'=>array("DATE(MobileUnlockLogs.created) > '$start'" ,
											"DATE(MobileUnlockLogs.created) < '$end'",
											'MobileUnlockLogs.unlock_status IN' => array(DISPATCHED_2_KIOSK_UNLOCKED, UNLOCK_PROCESSED_CONFIRMATION_SENT_2_KIOSK,DISPATCHED_2_KIOSK_UNPROCESSED,UNLOCK_FAILED_CONFIRMATION_SENT_2_KIOSK)
											)));
                $unlockData_query = $unlockData_query->hydrate(false);
                if(!empty($unlockData_query)){
                    $unlockData = $unlockData_query->toArray();
                }else{
                    $unlockData = array();
                }
			}elseif(!empty($user) && empty($kiosk) && empty($service_center)){
				$userId = $user;
				$unlockData_query = $this->MobileUnlockLogs->find('all',array('conditions'=>array("DATE(MobileUnlockLogs.created) > '$start'",
											"DATE(MobileUnlockLogs.created) < '$end'",
											'MobileUnlockLogs.unlock_status IN' => array(DISPATCHED_2_KIOSK_UNLOCKED, UNLOCK_PROCESSED_CONFIRMATION_SENT_2_KIOSK,DISPATCHED_2_KIOSK_UNPROCESSED,UNLOCK_FAILED_CONFIRMATION_SENT_2_KIOSK),
											'MobileUnlockLogs.user_id IN' => $userId
											)));
                $unlockData_query = $unlockData_query->hydrate(false);
                if(!empty($unlockData_query)){
                    $unlockData = $unlockData_query->toArray();
                }else{
                    $unlockData = array();
                }
			}elseif(!empty($kiosk) && empty($user) && empty($service_center)){
				$kioskId = $kiosk;
				$unlockData_query = $this->MobileUnlockLogs->find('all',array('conditions'=>array("DATE(MobileUnlockLogs.created) > '$start'",
											"DATE(MobileUnlockLogs.created) < '$end'",
											'MobileUnlockLogs.unlock_status IN' => array(DISPATCHED_2_KIOSK_UNLOCKED, UNLOCK_PROCESSED_CONFIRMATION_SENT_2_KIOSK,DISPATCHED_2_KIOSK_UNPROCESSED,UNLOCK_FAILED_CONFIRMATION_SENT_2_KIOSK),
											'MobileUnlockLogs.kiosk_id' => $kioskId,
											)));
                $unlockData_query = $unlockData_query->hydrate(false);
                if(!empty($unlockData_query)){
                    $unlockData = $unlockData_query->toArray();
                }else{
                    $unlockData = array();
                }
			}elseif(!empty($service_center) && empty($user) && empty($kiosk)){
				$kioskId = $kiosk;
				$unlockData_query = $this->MobileUnlockLogs->find('all',array('conditions'=>array("DATE(MobileUnlockLogs.created) > '$start'",
											"DATE(MobileUnlockLogs.created) < '$end'",
											'MobileUnlockLogs.unlock_status IN' => array(DISPATCHED_2_KIOSK_UNLOCKED, UNLOCK_PROCESSED_CONFIRMATION_SENT_2_KIOSK,DISPATCHED_2_KIOSK_UNPROCESSED,UNLOCK_FAILED_CONFIRMATION_SENT_2_KIOSK),
											'MobileUnlockLogs.unlock_center_id' => $service_center,
											)));
                $unlockData_query = $unlockData_query->hydrate(false);
                if(!empty($unlockData_query)){
                    $unlockData = $unlockData_query->toArray();
                }else{
                    $unlockData = array();
                }
			}elseif(!empty($service_center) && !empty($user) && empty($kiosk)){
				$userId = $user;
				$unlockData_query = $this->MobileUnlockLogs->find('all',array('conditions'=>array("DATE(MobileUnlockLogs.created) > '$start'",
											"DATE(MobileUnlockLogs.created) < '$end'",
											'MobileUnlockLogs.unlock_status IN' => array(DISPATCHED_2_KIOSK_UNLOCKED, UNLOCK_PROCESSED_CONFIRMATION_SENT_2_KIOSK,DISPATCHED_2_KIOSK_UNPROCESSED,UNLOCK_FAILED_CONFIRMATION_SENT_2_KIOSK),
											'MobileUnlockLogs.unlock_center_id' => $service_center,
											'MobileUnlockLogs.user_id IN' => $userId
											)));
                $unlockData_query = $unlockData_query->hydrate(false);
                if(!empty($unlockData_query)){
                    $unlockData = $unlockData_query->toArray();
                }else{
                    $unlockData = array();
                }
			}elseif(!empty($service_center) && empty($user) && !empty($kiosk)){
				$kioskId = $kiosk;
				$unlockData_query = $this->MobileUnlockLogs->find('all',array('conditions'=>array("DATE(MobileUnlockLogs.created) > '$start'",
											"DATE(MobileUnlockLogs.created) < '$end'",
											'MobileUnlockLogs.unlock_status IN' => array(DISPATCHED_2_KIOSK_UNLOCKED, UNLOCK_PROCESSED_CONFIRMATION_SENT_2_KIOSK,DISPATCHED_2_KIOSK_UNPROCESSED,UNLOCK_FAILED_CONFIRMATION_SENT_2_KIOSK),
											'MobileUnlockLogs.unlock_center_id' => $service_center,
											'MobileUnlockLogs.kiosk_id' => $kioskId
											)));
                $unlockData_query = $unlockData_query->hydrate(false);
                if(!empty($unlockData_query)){
                    $unlockData = $unlockData_query->toArray();
                }else{
                    $unlockData = array();
                }
				//pr($unlockData);
			}elseif(empty($service_center) && !empty($user) && !empty($kiosk)){
				$userId = $user;
				$kioskId = $kiosk;
				$unlockData_query = $this->MobileUnlockLogs->find('all',array('conditions'=>array("DATE(MobileUnlockLogs.created) > '$start'",
											"DATE(MobileUnlockLogs.created) < '$end'",
											'MobileUnlockLogs.unlock_status IN' => array(DISPATCHED_2_KIOSK_UNLOCKED, UNLOCK_PROCESSED_CONFIRMATION_SENT_2_KIOSK,DISPATCHED_2_KIOSK_UNPROCESSED,UNLOCK_FAILED_CONFIRMATION_SENT_2_KIOSK),
											'MobileUnlockLogs.kiosk_id' => $kioskId,
											'MobileUnlockLogs.user_id IN' => $userId
											)));
                $unlockData_query = $unlockData_query->hydrate(false);
                if(!empty($unlockData_query)){
                    $unlockData = $unlockData_query->toArray();
                }else{
                    $unlockData = array();
                }
				//pr($unlockData);
			}else{
				$userId = $user;
				$kioskId = $kiosk;
				$unlockData_query = $this->MobileUnlockLogs->find('all',array('conditions'=>array("DATE(MobileUnlockLogs.created) > '$start'",
											"DATE(MobileUnlockLogs.created) < '$end'",
											'MobileUnlockLogs.unlock_status IN' => array(DISPATCHED_2_KIOSK_UNLOCKED, UNLOCK_PROCESSED_CONFIRMATION_SENT_2_KIOSK,DISPATCHED_2_KIOSK_UNPROCESSED,UNLOCK_FAILED_CONFIRMATION_SENT_2_KIOSK),
											'MobileUnlockLogs.kiosk_id' => $kioskId,
											'MobileUnlockLogs.user_id IN' => $userId,
											'MobileUnlockLogs.unlock_center_id' => $service_center
											)));
                $unlockData_query = $unlockData_query->hydrate(false);
                if(!empty($unlockData_query)){
                    $unlockData = $unlockData_query->toArray();
                }else{
                    $unlockData = array();
                }
				//pr($unlockData);
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
					$unlockData_query = $this->MobileUnlockLogs->find('all',array('conditions'=>array("DATE(MobileUnlockLogs.created) > '$start'",
											"DATE(MobileUnlockLogs.created) < '$end'",
											'MobileUnlockLogs.unlock_status IN' => array(DISPATCHED_2_KIOSK_UNLOCKED, UNLOCK_PROCESSED_CONFIRMATION_SENT_2_KIOSK,DISPATCHED_2_KIOSK_UNPROCESSED,UNLOCK_FAILED_CONFIRMATION_SENT_2_KIOSK),
											'MobileUnlockLogs.kiosk_id IN' => $managerKiosk,
											)
								       ));		
				}else{
					$unlockData_query = $this->MobileUnlockLogs->find('all',array('conditions'=>array("DATE(MobileUnlockLogs.created) > '$start'",
											"DATE(MobileUnlockLogs.created) < '$end'",
											'MobileUnlockLogs.unlock_status IN' => array(DISPATCHED_2_KIOSK_UNLOCKED, UNLOCK_PROCESSED_CONFIRMATION_SENT_2_KIOSK,DISPATCHED_2_KIOSK_UNPROCESSED,UNLOCK_FAILED_CONFIRMATION_SENT_2_KIOSK)
											)
								       ));
				}
			}else{
				$unlockData_query = $this->MobileUnlockLogs->find('all',array('conditions'=>array("DATE(MobileUnlockLogs.created) > '$start'",
											"DATE(MobileUnlockLogs.created) < '$end'",
											'MobileUnlockLogs.unlock_status IN' => array(DISPATCHED_2_KIOSK_UNLOCKED, UNLOCK_PROCESSED_CONFIRMATION_SENT_2_KIOSK,DISPATCHED_2_KIOSK_UNPROCESSED,UNLOCK_FAILED_CONFIRMATION_SENT_2_KIOSK)
											)
								       ));
			}
			
			
            $unlockData_query = $unlockData_query->hydrate(false);
            if(!empty($unlockData_query)){
                $unlockData = $unlockData_query->toArray();
            }else{
                $unlockData = array();
            }
		}
		$userArray = array();
		$userunlockIds = array();
		$unlockIds = array();
		foreach($unlockData as $key => $unlockLog){
			$userArray[$unlockLog['user_id']][] = $unlockLog;
			$userunlockIds[$unlockLog['user_id']][] = $unlockLog['mobile_unlock_id'];
			$unlockIds[$unlockLog['mobile_unlock_id']] = $unlockLog['mobile_unlock_id'];
		}
		
		//pr($this->MobileUnlockSale->find('all', array('recursive' => -1)));
		//pr($this->MobileUnlock->find('all', array('recursive' => -1)));
		
		//Unprocessed Unlock
		$checkSale = array();
		$finalSale = array();
		//checking if this unlock id exists in unlock sale table
		//pr($userunlockIds);die;
		foreach($userunlockIds as $user_id => $userUnlockArr){
			foreach($userUnlockArr as $key => $userUnlock){
                
                $query_count = $this->MobileUnlockSales->find('all',['conditions' => ['MobileUnlockSales.mobile_unlock_id' => $userUnlock,'MobileUnlockSales.refund_status' => 0]]);
                $query_count->select(['count' => $query_count->func()->count('*')]);
                //pr($query_count);die;
                $query_count = $query_count->hydrate(false);
                if(!empty($query_count)){
					$count = $query_count->toArray();
                    $checkSale[$user_id][$userUnlock] = $count[0]['count'];
                }else{
                    $checkSale[$user_id][$userUnlock] = array();
                }
			}
			//pr($checkSale);die;
			$finalSale[$user_id] = array_keys($checkSale[$user_id]);//to remove the array with zero values and get keys which are actually unlock ids
		}
		foreach($userunlockIds as $user_id => $userUnlockArr){
            
            $query = $this->MobileUnlockSales->find('all',['conditions' => ['MobileUnlockSales.mobile_unlock_id IN' => $userUnlockArr,'MobileUnlockSales.refund_status' => 0]]);
                $query
                          ->select(['sumSale' => $query->func()->sum('MobileUnlockSales.amount')]);
                $result = $query->hydrate(false);
                if(!empty($result)){
                    $sum_sale[$user_id] = $result->first();
                }else{
                    $sum_sale[$user_id] = array();
                }
		}
		foreach($userunlockIds as $user_id => $userUnlockArr){
            
            $query_mobile_unlock = $this->MobileUnlockSales->find('all',['conditions' => ['MobileUnlockSales.mobile_unlock_id IN' => $userUnlockArr,
									      'MobileUnlockSales.refund_status' => 1]]);
                $query_mobile_unlock
                          ->select(['refundSale' => $query_mobile_unlock->func()->sum('MobileUnlockSales.refund_amount')]);
                $result_mobile_unlock = $query_mobile_unlock->hydrate(false);
                if(!empty($result_mobile_unlock)){
                    $refund_sale[$user_id] = $result_mobile_unlock->first();
                }else{
                    $refund_sale[$user_id] = array();
                }
		}
		
		//getting brand id, model id, problem type from mobile repair table for above ids
		$unlockDetail = array();
		foreach($finalSale as $user_id => $userUnlockArr){
			$unlockDetail[$user_id] = $this->MobileUnlocks->find('all',array('conditions'=>array('MobileUnlocks.id IN'=>$userUnlockArr),'fields'=>array('id','brand_id','mobile_model_id','network_id','created'),'recursive'=>-1));
            $unlockDetail_query = $unlockDetail[$user_id]->hydrate(false);
            if(!empty($unlockDetail_query)){
                $unlockDetail[$user_id] = $unlockDetail_query->toArray();
            }else{
                $unlockDetail[$user_id] = array();
            }
		}
		//getting cost price corresponding to the brand,model combination
		//pr($unlockDetail);die;
		$unlockCostArr = array();
		if(!empty($unlockDetail)){
			foreach($unlockDetail as $user_id => $unlockInf){
				foreach($unlockInf as $key => $unlockInfo){
					$unlockId = $unlockInfo['id'];
					$brand_id = $unlockInfo['brand_id'];
					$mobile_model_id = $unlockInfo['mobile_model_id'];
					$mobile_network_id = $unlockInfo['network_id'];
					$unlockCostArr_query = $this->MobileUnlockPrices->find('all',array('conditions'=>array('MobileUnlockPrices.brand_id'=>$brand_id,'MobileUnlockPrices.mobile_model_id'=>$mobile_model_id,'MobileUnlockPrices.network_id'=>$mobile_network_id,'MobileUnlockPrices.unlocking_price > 0'),'fields'=>array('MobileUnlockPrices.unlocking_cost')));
                    $unlockCostArr_result = $unlockCostArr_query->hydrate(false);
                    if(!empty($unlockCostArr_result)){
                        $unlockCostArr[$user_id][$unlockId] = $unlockCostArr_query->first();
                    }else{
                        $unlockCostArr[$user_id][$unlockId] = array();
                    }
				}
			}
		}
		$unlockCost = array();
		//pr($unlockCostArr);die;
		if(!empty($unlockCostArr)){
			foreach($unlockCostArr as $user_id => $unlockCostDetail){
				foreach($unlockCostDetail as $key => $unlockCostInfo){
					//if(array_key_exists('MobileUnlockPrice',$unlockCostInfo)){
						if(array_key_exists($user_id,$unlockCost)){
							$unlockCost[$user_id]+= $unlockCostInfo['unlocking_cost'];
						}else{
							$unlockCost[$user_id] = $unlockCostInfo['unlocking_cost'];
						}
					//}
				}
			}
		}
		//pr($unlockCost);die;
		$hint = $this->ScreenHint->hint('mobile_unlocks','unlock_technician_report');
					if(!$hint){
						$hint = "";
					}
		$this->set(compact('hint','kiosks','users','serviceCenters','unlockData','userArray','sum_sale','refund_sale','unlockCost','userName'));
	}
    
    public function unlockReportDetail(){
		$firstDay = date("Y-m-1");
		$lastDay = date("Y-m-t");
		$start = date("Y-m-d",strtotime("-1 day",strtotime($firstDay)));
		$end = date("Y-m-d",strtotime("+1 day",strtotime($lastDay)));
		
		$userName_query = $this->Users->find('list',[
                                               'keyField' => 'id',
                                               'valueField' => 'username'
                                              ]);
        if(!empty($userName_query)){
            $userName = $userName_query->toArray();
        }else{
            $userName = array();
        }
		$kiosks_query = $this->Kiosks->find('list',[
                                                'keyField' => 'id',
                                                'valueField' => 'name'
                                             ]);
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
			$this->paginate = [
                                'conditions' => ["DATE(MobileUnlockLogs.created) > '$start'" ,
                                            "DATE(MobileUnlockLogs.created) < '$end'",
                                            'MobileUnlockLogs.unlock_status IN' => array(DISPATCHED_2_KIOSK_UNLOCKED, DISPATCHED_2_KIOSK_UNPROCESSED,UNLOCK_PROCESSED_CONFIRMATION_SENT_2_KIOSK,UNLOCK_FAILED_CONFIRMATION_SENT_2_KIOSK)
                                            ],
                                'order' => ['MobileUnlockLogs.id desc']
                            ];
		}elseif(!empty($user) && empty($kiosk) && empty($service_center)){
			$userId = $user;
			$this->paginate = [
                                'conditions' => ["DATE(MobileUnlockLogs.created) > '$start'" ,
                                            "DATE(MobileUnlockLogs.created) < '$end'",
                                            'MobileUnlockLogs.unlock_status IN' => array(DISPATCHED_2_KIOSK_UNLOCKED, DISPATCHED_2_KIOSK_UNPROCESSED,UNLOCK_PROCESSED_CONFIRMATION_SENT_2_KIOSK,UNLOCK_FAILED_CONFIRMATION_SENT_2_KIOSK),
                                            'MobileUnlockLogs.user_id IN' => $userId
                                            ],
                                'order' => ['MobileUnlockLogs.id desc']
                            ];
		}elseif(!empty($kiosk) && empty($user) && empty($service_center)){
			$kioskId = $kiosk;
			$this->paginate = [
					'conditions' => ["DATE(MobileUnlockLogs.created) > '$start'" ,
								"DATE(MobileUnlockLogs.created) < '$end'",
								'MobileUnlockLogs.unlock_status IN' => array(DISPATCHED_2_KIOSK_UNLOCKED, DISPATCHED_2_KIOSK_UNPROCESSED,UNLOCK_PROCESSED_CONFIRMATION_SENT_2_KIOSK,UNLOCK_FAILED_CONFIRMATION_SENT_2_KIOSK),
								'MobileUnlockLogs.kiosk_id' => $kioskId
                                    ],
						'order' => ['MobileUnlockLogs.id desc']
                            ];
		}elseif(!empty($service_center) && empty($user) && empty($kiosk)){
			$kioskId = $kiosk;
			$this->paginate = [
					'conditions' => ["DATE(MobileUnlockLogs.created) > '$start'" ,
								"DATE(MobileUnlockLogs.created) < '$end'",
								'MobileUnlockLogs.unlock_status IN' => array(DISPATCHED_2_KIOSK_UNLOCKED, DISPATCHED_2_KIOSK_UNPROCESSED,UNLOCK_PROCESSED_CONFIRMATION_SENT_2_KIOSK,UNLOCK_FAILED_CONFIRMATION_SENT_2_KIOSK),
								'MobileUnlockLogs.service_center_id' => $service_center
                                    ],
						'order' => ['MobileUnlockLogs.id desc']
                            ];
		}elseif(!empty($service_center) && !empty($user) && empty($kiosk)){
			$userId = $user;
			$this->paginate = [
                                'conditions' => ["DATE(MobileUnlockLogs.created) > '$start'" ,
                                            "DATE(MobileUnlockLogs.created) < '$end'",
                                            'MobileUnlockLogs.unlock_status IN' => array(DISPATCHED_2_KIOSK_UNLOCKED, DISPATCHED_2_KIOSK_UNPROCESSED,UNLOCK_PROCESSED_CONFIRMATION_SENT_2_KIOSK,UNLOCK_FAILED_CONFIRMATION_SENT_2_KIOSK),
                                            'MobileUnlockLogs.service_center_id' => $service_center,
                                            'MobileUnlockLogs.user_id IN' => $userId
                                            ],
                                'order' => ['MobileUnlockLogs.id desc']
                            ];
		}elseif(!empty($service_center) && empty($user) && !empty($kiosk)){
			$kioskId = $kiosk;
			$this->paginate = [
                                'conditions' => ["DATE(MobileUnlockLogs.created) > '$start'" ,
                                            "DATE(MobileUnlockLogs.created) < '$end'",
                                            'MobileUnlockLogs.unlock_status IN' => array(DISPATCHED_2_KIOSK_UNLOCKED, DISPATCHED_2_KIOSK_UNPROCESSED,UNLOCK_PROCESSED_CONFIRMATION_SENT_2_KIOSK,UNLOCK_FAILED_CONFIRMATION_SENT_2_KIOSK),
                                            'MobileUnlockLogs.service_center_id' => $service_center,
                                            'MobileUnlockLogs.kiosk_id' => $kioskId
                                                ],
                                    'order' => ['MobileUnlockLogs.id desc']
                            ];
		}elseif(empty($service_center) && !empty($user) && !empty($kiosk)){
			$userId = $user;
			$kioskId = $kiosk;
			$this->paginate = [
					'conditions' => ["DATE(MobileUnlockLogs.created) > '$start'" ,
								"DATE(MobileUnlockLogs.created) < '$end'",
								'MobileUnlockLogs.unlock_status IN' => array(DISPATCHED_2_KIOSK_UNLOCKED, DISPATCHED_2_KIOSK_UNPROCESSED,UNLOCK_PROCESSED_CONFIRMATION_SENT_2_KIOSK,UNLOCK_FAILED_CONFIRMATION_SENT_2_KIOSK),
								'MobileUnlockLogs.kiosk_id' => $kioskId,
								'MobileUnlockLogs.user_id IN' => $userId
								],
						'order' => ['MobileUnlockLogs.id desc']
                            ];
		}else{
			$userId = $user;
			$kioskId = $kiosk;
			$this->paginate = [
					'conditions' => ["DATE(MobileUnlockLogs.created) > '$start'" ,
								"DATE(MobileUnlockLogs.created) < '$end'",
								'MobileUnlockLogs.unlock_status IN' => array(DISPATCHED_2_KIOSK_UNLOCKED, DISPATCHED_2_KIOSK_UNPROCESSED,UNLOCK_PROCESSED_CONFIRMATION_SENT_2_KIOSK,UNLOCK_FAILED_CONFIRMATION_SENT_2_KIOSK),
								'MobileUnlockLogs.kiosk_id' => $kioskId,
								'MobileUnlockLogs.user_id IN' => $userId,
								'MobileUnlockLogs.service_center_id' => $service_center
								],
						'order' => ['MobileUnlockLogs.id desc']
                            ];
		}
		$this->paginate["limit"] = 50;
		$unlockData_query = $this->paginate('MobileUnlockLogs');
        if(!empty($unlockData_query)){
            $unlockData = $unlockData_query->toArray();
        }else{
            $unlockData = array();
        }
		$userUnlockIds = array();
		$mobile_model_ids = array();
		$network_ids = array();
		foreach($unlockData as $key => $unlockLog){
            //pr($unlockLog);die;
			$userUnlockIds[$unlockLog->user_id][] = $unlockLog->mobile_unlock_id;
		}
		
		$unlockDetail = array();
        //pr($userUnlockIds);die;
		foreach($userUnlockIds as $user_id => $userUnlockArr){
            //pr($userUnlockArr);die;
			$unlockDetail_query = $this->MobileUnlocks->find('all',array('conditions'=>array('MobileUnlocks.id IN'=>$userUnlockArr),'fields'=>array('id','brand_id','mobile_model_id','network_id','net_cost')));
            $unlockDetail_query = $unlockDetail_query->hydrate(false);
            if(!empty($unlockDetail_query)){
                $unlockDetail[$user_id] = $unlockDetail_query->toArray();
            }else{
                $unlockDetail[$user_id] = array();
            }
			
			$data_query = $this->MobileUnlockSales->find('all',array('conditions'=>array('MobileUnlockSales.mobile_unlock_id IN'=>$userUnlockArr)));
            //pr($data_query);die;
            $data_query = $data_query->hydrate(false);
            if(!empty($data_query)){
                $data = $data_query->toArray();
            }else{
                $data = array();
            }
		}
		$sale_Arr = array();
        //pr($data);die;
		foreach($data as $key => $value){
			//pr($value);die;
			if(array_key_exists($value['mobile_unlock_id'],$sale_Arr)){
				if($value['amount'] == 0){
					continue;
				}
				$sale_Arr[$value['mobile_unlock_id']] = array(
                                                                'amount' => $value['amount'],
                                                                'refund_amount' => $value['refund_amount'],
                                                             );
			}else{
				$sale_Arr[$value['mobile_unlock_id']] = array(
								'amount' => $value['amount'],
								'refund_amount' => $value['refund_amount'],
								);
			}
		}
		//pr($sale_Arr);
		//die;
        //pr($unlockDetail);die;
		foreach($unlockDetail[$user_id] as $k => $unlockDet){
            //pr($unlockDet);die;
			$mobile_model_ids[$unlockDet['mobile_model_id']] = $unlockDet['mobile_model_id'];
			$network_ids[$unlockDet['network_id']] = $unlockDet['network_id'];
		}
		if(empty($mobile_model_ids)){
            $mobile_model_ids = array(0 => null);
        }
		$mobileModels_query = $this->MobileModels->find('list',[
                                                          'conditions' => ['MobileModels.id IN' => $mobile_model_ids],
                                                          'keyField' => 'id',
                                                          'valueField' => 'model',
														  'order'=>'model asc',
                                                         ]);
        if(!empty($mobileModels_query)){
            $mobileModels = $mobileModels_query->toArray();
        }else{
            $mobileModels = array();
        }
        if(empty($network_ids)){
            $network_ids = array(0 => null);
        }
		$networks = $this->Networks->find('list',[
                                                  'conditions' => ['Networks.id IN' => $network_ids],
                                                  'keyField' => 'id',
                                                  'valueField' => 'name'
                                                 ]);
        if(!empty($networks)){
            $networks = $networks->toArray();
        }else{
            $networks = array();
        }
		$this->set(compact('unlockData','user','unlockDetail','userName','kiosks','mobileModels','networks','sale_Arr','requestParams'));
	}
    
    public function view($id = null) {
		if (!$this->MobileUnlocks->exists($id)) {
			throw new NotFoundException(__('Invalid mobile unlock'));
		}
		//echo $id;die;
		//$options =[['conditions' => ['id' => $id]],'contain' => ['Kiosks','Brands','MobileModels','Networks']];
        $mobileUnlock_query = $this->MobileUnlocks->find('all', ['conditions' =>['MobileUnlocks.id' => $id],'contain' => ['Kiosks','Brands','MobileModels','Networks']]);
		//pr($mobileUnlock_query);die;
        $mobileUnlock_result = $mobileUnlock_query->first();
        if(!empty($mobileUnlock_result)){
            $mobileUnlock = $mobileUnlock_result->toArray();
        }else{
            $mobileUnlock = array();
        }
		$this->set('mobileUnlock',$mobileUnlock);
		//pr($mobileUnlock);die;
		$comments_query = $this->MobileUnlocks->CommentMobileUnlocks->find('all', array(
									 //'fields' => array('*'),
									 'conditions' => array('CommentMobileUnlocks.status' => 1,'CommentMobileUnlocks.mobile_unlock_id' => $id),
									 'order' => array('CommentMobileUnlocks.id DESC'),
									 'limit' => 5
									));
        $comments_query = $comments_query->hydrate(false);
        if(!empty($comments_query)){
            $comments = $comments_query->toArray();
        }else{
            $comments = array();
        }
		$unlockLogs_query = $this->MobileUnlockLogs->find('all',array(
								'conditions'=>array('MobileUnlockLogs.mobile_unlock_id' => $id),
								'order'=>array('MobileUnlockLogs.id DESC')
								));
        $unlockLogs_query = $unlockLogs_query->hydrate(false);
        if(!empty($unlockLogs_query)){
            $unlockLogs = $unlockLogs_query->toArray();
        }else{
            $unlockLogs = array();
        }
		$users_query = $this->Users->find('list',[
                                                    'keyField' => 'id',
                                                    'valueField' => 'username'
                                                ]);
		if(!empty($users_query)){
            $users = $users_query->toArray();
        }else{
            $users = array();
        }
		$kiosks_query = $this->MobileUnlocks->Kiosks->find('list',[
                                                            'conditions' => ['Kiosks.status' => 1]
                                                            ]);
        if(!empty($kiosks_query)){
            $kiosks = $kiosks_query->toArray();
        }else{
            $kiosks = array();
        }
		$dataPerId_query = $this->MobileUnlocks->find('all',array(
							'conditions' => array('MobileUnlocks.id'=>$id),
							));
        //pr($dataPerId_query);die;
        $dataPerId_result = $dataPerId_query->first();
        if(!empty($dataPerId_result)){
            $dataPerId = $dataPerId_result->toArray();
        }else{
            $dataPerId = array();
        }
        //pr($dataPerId);die;
				$dataPerId['brand_id'];
				$brandId = $dataPerId['brand_id'];
				$mobileModelId = $dataPerId['mobile_model_id'];
				$networkId = $dataPerId['network_id'];
                $conn = ConnectionManager::get('default');
                $stmt = $conn->execute("SELECT `unlocking_days`,`unlocking_minutes` from `mobile_unlock_prices` WHERE `brand_id`='$brandId' AND `mobile_model_id`='$mobileModelId' AND `network_id`='$networkId'");
                $unlockingDaysArr = $stmt ->fetchAll('assoc');
				if(array_key_exists(0,$unlockingDaysArr)){
					$unlockingDays = $unlockingDaysArr['0']['unlocking_days'];
					$unlockingMinutes = $unlockingDaysArr['0']['unlocking_minutes'];	
				}else{
					$unlockingDays = "";
					$unlockingMinutes = "";
				}
                
		$this->set(compact('comments','users','kiosks','unlockLogs','unlockingDays','unlockingMinutes'));
	}
    
    public function unlockReceipt($id = null){
		$mobileUnlockData_query = $this->MobileUnlocks->find('all',[
                                                                    'conditions'=>['MobileUnlocks.id'=>$id],
                                                                    'contain' => ['Brands','MobileModels','Networks']
                                                                    ]);
        $mobileUnlockData_result = $mobileUnlockData_query->first();
        if(!empty($mobileUnlockData_result)){
            $mobileUnlockData = $mobileUnlockData_result->toArray();
        }else{
            $mobileUnlockData = array();
        }
		$unlockRefundData = array();
        //pr($mobileUnlockData);die;
			if($mobileUnlockData['status_refund']==1){
				$unlockRefundData[] = $mobileUnlockData['status_refund'];
			}
		$settingArr = $this->setting;
		$userId = $mobileUnlockData['booked_by'];
		$kiosk_id = $mobileUnlockData['kiosk_id'];
		
		$mobileUnlockStatusQry = $this->MobileUnlockLogs->find('all',[
																	  'conditions'=>[
																		'MobileUnlockLogs.mobile_unlock_id'=>$id,
																		'MobileUnlockLogs.kiosk_id'=>$kiosk_id
																	  ],
																	  'limit' => 1,
																		'order' => ['MobileUnlockLogs.id asc'],
																	  ]);
		$mobileUnlockStatusQry = $mobileUnlockStatusQry->hydrate(false);
		if(!empty($mobileUnlockStatusQry)){
			$mobileUnlockStatus = $mobileUnlockStatusQry->first();
		}else{
			$mobileUnlockStatus = array();
		}
		//pr($mobileUnlockStatus);die;
		if(!empty($mobileUnlockStatus)){
			$mobileStatus = $mobileUnlockStatus['unlock_status'];
		}else{
			$mobileStatus = '';
		}
		
		$userName = $this->Users->find('list',[
                                               'conditions'=>['Users.id'=>$mobileUnlockData['booked_by']],
                                               'keyField' => 'id',
                                                'valueField' => 'username'
                                              ]);
        if(!empty($userName)){
            $userName = $userName->toArray();
        }else{
            $userName = array();
        }
		$kioskDetails = $this->Kiosks->find('all',array('conditions'=>array('Kiosks.id'=>$kiosk_id),
														//'fields'=>array('id','name','address_1','address_2','city','state','zip','contact','country')
														)
														);
        $kioskDetails = $kioskDetails->first();
        if(!empty($kioskDetails)){
            $kioskDetails = $kioskDetails->toArray();
        }else{
            $kioskDetails = array();
        }
		
		$pay_res = $this->UnlockPayments->find('all', array('conditions' => array('UnlockPayments.mobile_unlock_id' => $id),
															'order by' => 'UnlockPayments.created ASC',
															)
											   )->toArray();
		$date = "";
		if(!empty($pay_res)){
			$date = $pay_res[0]->created;
		}
		
		if($this->request->is('post')){
			$send_by_email = Configure::read('send_by_email');
			$emailSender = Configure::read('EMAIL_SENDER');
			$customerEmail = $this->request['data']['email'];
			if(!empty($customerEmail)){
					$Email = new Email();
					$Email->config('default');
					$Email->viewVars(array(
                                           'mobileUnlockData' => $mobileUnlockData,
                                           'settingArr' => $settingArr,
                                           'userId' => $userId,
                                           'userName' => $userName,
                                           'kioskDetails' => $kioskDetails,
                                           'unlockRefundData' => $unlockRefundData,
										   "date"=>$date
                                           )
                                    );
					//$Email->config(array('from' => 'me@example.org', 'transport' => 'MyCustom'));
					//$Email = new CakeEmail(array('from' => 'me@example.org', 'transport' => 'MyCustom'));
					$emailTo = $customerEmail;
					$Email->template('unlock_email_receipt');
					$Email->emailFormat('both');
					$Email->to($emailTo);
                   
                    $Email->transport(TRANSPORT);
                    $Email->from([$send_by_email => $emailSender]);
					//$Email->sender("sales@oceanstead.co.uk");
					
					$Email->subject('Mobile Unlock Receipt');
					if($Email->send()){
						$this->Flash->success('Email has been successfully sent!');
					}
				}
		}
		$this->set(compact('settingArr','mobileUnlockData','userName','kioskDetails','unlockRefundData','mobileStatus','date'));
	}
	
	public function add($id = '') {
		$mobile_unlocks = $this->MobileUnlocks->newEntity();
		$this->set(compact('mobile_unlocks'));
		//capturing the mobile model id and brand ids from mobileunlockprice table with status 1 ie active
		$customerId ='';
		$customerdetail = array();
		if(!empty( $this->request->query)){
			$customerId = $this->request->query['customerId'] ;
		}
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
		
		$activeCombinations_query = $this->MobileUnlockPrices->find('all',array('conditions' => array('MobileUnlockPrices.status'=>1,
												       'MobileUnlockPrices.unlocking_price > 0'),
							      'fields' => array('mobile_model_id','brand_id'),
							      'group' => 'MobileUnlockPrices.mobile_model_id'
							      ));
		
		$activeCombinations_query = $activeCombinations_query->hydrate(false);
		if(!empty($activeCombinations_query)){
			$activeCombinations = $activeCombinations_query->toArray();
		}else{
			$activeCombinations = array();
		}
		$activeBrands = array();
		$activeModels = array();
		foreach($activeCombinations as $key => $activeCombination){
			$activeBrands[$activeCombination['brand_id']] = $activeCombination['brand_id'];
			$activeModels[$activeCombination['mobile_model_id']] = $activeCombination['mobile_model_id'];
		}
		$terms_unlock = $this->setting['terms_unlock'];
		$mobilePurchaseDetails = array();
		$mobileUnlockPrice = array();
		//mobile purchase status 0 => available, 1 => sold , 2 => reserved, 3 => sent for unlock 4 => under repair
		if($id > 0){
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
			
			$internal_unlock_default_cost = $res['internal_unlock_default_cost'];
			$internal_unlock_default_price = $res['internal_unlock_default_price'];
			$this->set(compact('internal_unlock_default_cost','internal_unlock_default_price'));
			//code for internal booking of purchased mobiles
			$mobilePurchaseDetails_query = $this->MobilePurchases->find('all',array('conditions' => array('MobilePurchases.id' => $id),
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
			$networkID = $mobilePurchaseDetails['network_id'];
			
			$mobileUnlockPrice_query = $this->MobileUnlockPrices->find('all',array(
							     'conditions' => array( 
										'brand_id' => $brandID,
										'mobile_model_id' => $modelID,
										'network_id' => $networkID,
										'MobileUnlockPrices.unlocking_price > 0'
										),
							     'fields' => array(
									       'unlocking_price',
									       'unlocking_days'
									      )
							    )
					       );
			//pr($mobileUnlockPrice_query);die;
			$mobileUnlockPrice_query = $mobileUnlockPrice_query->hydrate(false);
			if(!empty($mobileUnlockPrice_query)){
				$mobileUnlockPrice = $mobileUnlockPrice_query->first();
			}else{
				$mobileUnlockPrice = array();
			}
		
			if(empty($mobileUnlockPrice)){
				//$this->Session->setFlash("No pricing detail found for brand:$brandID, model:$modelID and network:$networkID");
				//return $this->redirect(array('controller'=>'mobile_purchases','action'=>'view',$id));
				//if no pricing detail found corresponding to this mobile unlock, then we will use internal brand and model
				//for this unlock
				$brandList_query = $this->Brands->find('list', array('conditions' => array('Brands.brand' => 'Internal Repair/Unlock'), 'fields' => array('id')));
				$brandList_query = $brandList_query->hydrate(false);
				if(!empty($brandList_query)){
					$brandList = $brandList_query->toArray();
				}else{
					$brandList = array();
				}
				
				$brandList = array_values($brandList);
				$modelList_query = $this->MobileModels->find('list', array('conditions' => array('MobileModels.model' => 'Internal Repair/Unlock'), 'order'=>'model asc','fields' => array('id')));
				
				$modelList_query = $modelList_query->hydrate(false);
				if(!empty($modelList_query)){
					$modelList = $modelList_query->toArray();
				}else{
					$modelList = array();
				}
				
				$modelList = array_values($modelList);
				$networkList_query = $this->Networks->find('list', array('conditions' => array('Networks.name' => 'Internal Repair/Unlock'), 'fields' => array('id')));
				$networkList_query = $networkList_query->hydrate(false);
				if(!empty($networkList_query)){
					$networkList = $networkList_query->toArray();
				}else{
					$networkList = array();
				}
				
				
				$networkList = array_keys($networkList);
                
				$mobilePurchaseDetails['brand_id'] = $brandList['0'];;
				$mobilePurchaseDetails['mobile_model_id'] = $modelList['0'];
                if(!empty($networkList)){
                    $mobilePurchaseDetails['network_id'] = $networkList['0'];
                }
				
				$brandID = $mobilePurchaseDetails['brand_id'];
				$modelID = $mobilePurchaseDetails['mobile_model_id'];
				$networkID = $mobilePurchaseDetails['network_id'];
				
				$mobileUnlockPrice_query = $this->MobileUnlockPrices->find('all',array(
							     'conditions' => array( 
										'brand_id' => $brandID,
										'mobile_model_id' => $modelID,
										'network_id' => $networkID,
										'MobileUnlockPrices.unlocking_price > 0'
										),
							     'fields' => array(
									       'unlocking_price',
									       'unlocking_days'
									      )
							    )
					       );
				$mobileUnlockPrice_query = $mobileUnlockPrice_query->hydrate(false);
				if(!empty($mobileUnlockPrice_query)){
					$mobileUnlockPrice = $mobileUnlockPrice_query->first();
				}else{
					$mobileUnlockPrice = array();
				}
			}
			//till here
		}
		//pr($mobileUnlockPrice);die;
		if($this->request->session()->read('Auth.User.group_id') != KIOSK_USERS){
			$this->Flash->error('You are not authorized to book unlocking');
			return $this->redirect(array('action' => 'index'));
		}
		$unlock_email_message = $this->setting['unlock_email_message'];
		$kiosk_id = $this->request->Session()->read('kiosk_id');
		$countryOptions = Configure::read('uk_non_uk');
		$this->set(compact('countryOptions'));
		
		$kiosks_query = $this->MobileUnlocks->Kiosks->find('list',array(
									 'conditions' => array('Kiosks.status' => 1)
									));
		$kiosks_query = $kiosks_query->hydrate(false);
		if(!empty($kiosks_query)){
			$kiosks = $kiosks_query->toArray();
		}else{
			$kiosks = array();
		}
		$kioskaddress_query = $this->Kiosks->find('all',array(
					'fields' => array('Kiosks.address_1','Kiosks.address_2', 'Kiosks.city','Kiosks.state','Kiosks.country','Kiosks.zip','Kiosks.contact'),
					'conditions'=> array('Kiosks.id' => $kiosk_id)
					)
				);
		$kioskaddress_query = $kioskaddress_query->hydrate(false);
		if(!empty($kioskaddress_query)){
			$kioskaddress = $kioskaddress_query->first();
		}else{
			$kioskaddress = array();
		}
		$networks_query = $this->MobileUnlocks->Networks->find('list');
		$networks_query = $networks_query->hydrate(false);
		if(!empty($networks_query)){
			$networks = $networks_query->toArray();
		}else{
			$networks = array();
		}
		$brands_query = $this->MobileUnlocks->Brands->find('list',
																	[
																		'keyField' => 'id',
																		'valueField' => 'brand',
																		'order'=>'brand asc',
																		'conditions' => ['Brands.status' => 1,
																							'Brands.id IN' => $activeBrands]
																	]
									);
		$brands_query = $brands_query->hydrate(false);
		if(!empty($brands_query)){
			$brands = $brands_query->toArray();
		}else{
			$brands = array();
		}
		//pr($brands);die;
		unset($this->request->data['MobileUnlock']['show_unlock_minutes']);
		$this->check_if_kiosk();
		$dateUnlocked = date('Y-m-d G:i:s A');
		if ($this->request->is('post') || is_array($this->request->Session()->read('add_unlock_session'))) {
			//pr($this->request);die;
			if($this->request->is('post')){
				//pr($this->request);die;
				if(array_key_exists("MobileUnlock",$this->request->data)){
					
					$brdID = $this->request->data['MobileUnlock']['brand_id'];
					$mobilMdlId = $this->request->data['MobileUnlock']['mobile_model_id'];
					$result_query = $this->MobileModels->find('all',array('conditions' => array('MobileModels.id' => $mobilMdlId,
																				 'MobileModels.brand_id' => $brdID,
																				 )
														   ));
					$result_query = $result_query->hydrate(false);
					if(!empty($result_query)){
						$result = $result_query->first();
					}else{
						$result =  array();
					}
					if(empty($result)){
						$this->Flash->error(__('The mobile unlock could not be saved. Please Choose Right Combination For Brand And Model.'));
						return $this->redirect(array('action' => 'add'));
					}
				}
				//pr($this->request->data);die;
				$cust_data = $this->request->data;
				if(!empty($cust_data['customer_email'])){
					$countDuplicate_query = $this->RetailCustomers->find('all', array('conditions' => array('RetailCustomers.email' => $cust_data['customer_email'])));
					$countDuplicate_query = $countDuplicate_query->hydrate(false);
					if(!empty($countDuplicate_query)){
						$countDuplicate = $countDuplicate_query->first();
					}else{
						$countDuplicate = array();
					}
					$userId = $this->request->Session()->read('Auth.User.id');
					$customer_data = array(
													'kiosk_id' =>  $kiosk_id,
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
					//pr($customer_data);die;
					if(count($countDuplicate) == 0){
						$retailCustomersEntity = $this->RetailCustomers->newEntity();
						$retailCustomersEntity = $this->RetailCustomers->patchEntity($retailCustomersEntity,$customer_data);
						$this->RetailCustomers->save($retailCustomersEntity);
					}else{
						$custmor_id =  $countDuplicate["id"];
						$retailCustomersEntity = $this->RetailCustomers->get($custmor_id);
						$retailCustomersEntity = $this->RetailCustomers->patchEntity($retailCustomersEntity,$customer_data);
						//$customer_data["id"] =  $custmor_id;
						$this->RetailCustomers->save($retailCustomersEntity);
					}
				}
			}
			$payment_ids = array();
			if(count($this->request->data) && $this->request->data['status'] == VIRTUALLY_BOOKED && !array_key_exists('internal_unlock',$this->request->data)){
				if(!empty($customerId)){
					$this->request->data['retail_customer_id'] = $customerId;
				}else{
					$this->request->data['retail_customer_id'] = 0;
				}
				$this->request->Session()->write('add_unlock_session', $this->request->data);
				return $this->redirect(array('action' => 'add_unlock_payment'));
				die;
			}elseif(is_array($this->request->Session()->read('add_unlock_session'))){
				$ssn = $this->request->Session()->read('add_unlock_session');
				$payment_ids = $ssn['payment_id'];
				$this->request->data = $this->request->Session()->read('add_unlock_session');
			}
			
			//deleting the session, as we no longer require it
			$this->request->Session()->delete('add_unlock_session');
			
			
			$this->request->data['kiosk_id'] = $this->request->Session()->read('kiosk_id');
			$this->request->data['booked_by'] = $this->request->session()->read('Auth.User.id');			
			$unlock_status = $this->request->data['status'];
			if(array_key_exists('internal_unlock',$this->request->data)){
				$internal_unlock = $this->request->data['internal_unlock'];
			}
			$mobileUnlockId = 0;
			//rajju dec 28,2015
			if(array_key_exists('imei',$this->request->data)){
				$imei = $this->request->data['imei'].$this->request->data['imei1'];
			}
			$this->request->data['imei']= $imei;
			//rajju dec 28,2015
			//------------------------------
			//$unlocl_cost = $this->request->data['MobileUnlock']['net_cost_b'];
			$unlockData = $this->request->data;
			unset($unlockData['street_address']);
			unset($unlockData['formValid']);
			unset($unlockData['unlocking_days']);
			unset($unlockData['unlocking_minutes']);
			unset($unlockData['imei1']);
			unset($unlockData['cust_email']);
			unset($unlockData['show_unlock_minutes']);
			unset($unlockData['add_mobile']);
			$checkDbData_query = $this->MobileUnlocks->find('all',array('conditions' => $unlockData));
			$checkDbData_query = $checkDbData_query->hydrate(false);
			if(!empty($checkDbData_query)){
				$checkDbData = $checkDbData_query->first();
			}else{
				$checkDbData = array();
			}
			$addToDatabase = true;
			if(count($checkDbData) >= 1){
				$addToDatabase = false;
			}else{
				;//echo "Record do not exist!";
			}
			
			$MobileUnlocksEntity = $this->MobileUnlocks->newEntity();
			$MobileUnlocksEntity = $this->MobileUnlocks->patchEntity($MobileUnlocksEntity,$this->request->data,['validate' => false]);
			
			//------------------------------
			if ($addToDatabase && $this->MobileUnlocks->save($MobileUnlocksEntity)) {
				
				//pr($this->request);die;
				//updating the unlock id in payment table
				if(count($payment_ids)){
					foreach($payment_ids as $pi => $payment_id){
						$UnlockPaymentsEntity = $this->UnlockPayments->get($payment_id);
						
						$mobileUnlckId = $MobileUnlocksEntity->id;
						$data = array('mobile_unlock_id' => $mobileUnlckId);
						$UnlockPaymentsEntity = $this->UnlockPayments->patchEntity($UnlockPaymentsEntity,$data,['validate' => false]);
						$this->UnlockPayments->save($UnlockPaymentsEntity);
					}
				}
				if(array_key_exists('add_mobile',$this->request->data)){
					$MobilePurchasesEntity = $this->MobilePurchases->get($id);
					$data1 = array('status' => 3);
					$MobilePurchasesEntity = $this->MobilePurchases->patchEntity($MobilePurchasesEntity,$data1,['validate' => false]);
					$this->MobilePurchases->save($MobilePurchasesEntity);//status of mobile changed to "sent for unlock" in mobile purchase table
					
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
						'status' => 4//unlocking
						);
				
					$MobileTransferLogsEntity = $this->MobileTransferLogs->newEntity($mobileTransferLogData,['validate' => false]);
					$MobileTransferLogsEntity = $this->MobileTransferLogs->patchEntity($MobileTransferLogsEntity,$mobileTransferLogData,['validate' => false]);
					$this->MobileTransferLogs->save($MobileTransferLogsEntity);
				}
				
				$mobileUnlockId = $MobileUnlocksEntity->id;
				$mobileUnlockLogsData = array(
					'kiosk_id' => $kiosk_id,
					'user_id' => $this->request->session()->read('Auth.User.id'),
					'mobile_unlock_id' => $MobileUnlocksEntity->id,					
					'unlock_status' => $unlock_status
					);
				
				$MobileUnlockLogsEntity = $this->MobileUnlockLogs->newEntity();
				$MobileUnlockLogsEntity = $this->MobileUnlockLogs->patchEntity($MobileUnlockLogsEntity,$mobileUnlockLogsData,['validate' => false]);
				
				$this->MobileUnlockLogs->save($MobileUnlockLogsEntity);
				$amount = $this->request['data']['estimated_cost'];
				if($unlock_status == VIRTUALLY_BOOKED){					
					$mobileUnlockSalesData = array(
								'kiosk_id' => $kiosk_id,
								'retail_customer_id' => $this->request->data['retail_customer_id'],
								'mobile_unlock_id' => $MobileUnlocksEntity->id,
								'sold_by' => $this->request->Session()->read('Auth.User.id'),
								'sold_on' => $dateUnlocked,
								'refund_by' => '',
								'amount' => $amount,
								'refund_amount' => '',
								'refund_status' => 0,
								'refund_on' => '',
								'refund_remarks' => ''
								       );
					/*$this->MobileUnlockSale->set($mobileUnlockSalesData);
					if (!$this->MobileUnlockSale->validates()) {
						pr($errors = $this->MobileUnlockSale->validationErrors);
					}*/
					//pr($mobileUnlockSalesData);die;
					$MobileUnlockSalesEntity = $this->MobileUnlockSales->newEntity($mobileUnlockSalesData,['validate' => false]);
					$MobileUnlockSalesEntity = $this->MobileUnlockSales->patchEntity($MobileUnlockSalesEntity,$mobileUnlockSalesData,['validate' => false]);
					if($this->MobileUnlockSales->save($MobileUnlockSalesEntity)){
						if(count($payment_ids)){
							foreach($payment_ids as $pi => $payment_id){
								$UnlockPaymentsEntity = $this->UnlockPayments->get($payment_id);
								$mobileUnlckSaleId = $MobileUnlockSalesEntity->id;
								$data = array('mobile_unlock_sale_id' => $mobileUnlckSaleId);
								$UnlockPaymentsEntity = $this->UnlockPayments->patchEntity($UnlockPaymentsEntity,['validate' => false]);
								$this->UnlockPayments->save($UnlockPaymentsEntity);
							}
						}
					}
				}
				
		
				$mobileModels_query = $this->MobileUnlocks->MobileModels->find('list',
																				[
																					'keyField' => 'id',
																					'valueField' => 'model',
																					'order'=>'model asc',
																					'conditions' => [
																											'MobileModels.status' => 1,
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
		
				$unlockBookingData = $this->request->data;
				//pr($unlockBookingData);die;
				$unlockStatus = $unlockBookingData['status'];
				$messageStatement = '';
				$customerContact = $this->request->data['customer_contact'];
				$duration = "";
				if(empty($unlockBookingData['unlocking_days'])){
					$duration = $this->convertToHoursMins($unlockBookingData['unlocking_minutes'], '%02d hours %02d minutes');		
				}else{
					$duration = $unlockBookingData['unlocking_days']." working day(s)";
				}
				switch($unlockStatus){
					case VIRTUALLY_BOOKED:
						
						$unlockStatusStatement = "The unlock has been booked for your unlock id ".$mobileUnlockId."And Mobile Model :".$mobileModels[$unlockBookingData['mobile_model_id']]." phone &#40;IMEI: ".$unlockBookingData['imei']."&#41;. Your unlock is expected to get done within {$duration}&#41;.<br/><br/>";
						$messageStatement = "Your phone unlock id ".$mobileUnlockId." has been booked and will be unlocked within {$duration} Please contact the kiosk with any queries t&s ".$this->setting['repair_unlock_terms_link'];
						break;
					
					case BOOKED:
						/*$unlockStatusStatement = "The unlock has been booked for your ".$mobileModels[$unlockBookingData['mobile_model_id']]." phone &#40;IMEI: ".$unlockBookingData['imei']."&#41;. Your unlock is expected to get done within ".$unlockBookingData['unlocking_days']." working day&#40;s&#41;.<br/><br/>";*/
						$unlockStatusStatement = "The unlock has been booked for your unlock id ".$mobileUnlockId."And Mobile Model :".$mobileModels[$unlockBookingData['mobile_model_id']]." phone &#40;IMEI: ".$unlockBookingData['imei']."&#41;. Your unlock is expected to get done within {$duration}.<br/><br/>";
						$messageStatement = "Your phone unlock id ".$mobileUnlockId." has been booked and will be unlocked within {$duration}. Please contact the kiosk with any queries t&s ".$this->setting['repair_unlock_terms_link'];
						break;
				}
				$send_by_email = Configure::read('send_by_email');
				$emailSender = Configure::read('EMAIL_SENDER');
				if(!empty($unlockStatusStatement)){
					if(!empty($customerContact)){
						$destination = $customerContact;
						if(!empty($messageStatement)){
							$this->TextMessage->test_text_message($destination, $messageStatement);
						}
					}
					if(!empty($emailTo)){
						$Email = new Email();
						$Email->config('default');
						$Email->viewVars(array('unlockBookingData' => $unlockBookingData,'unlockStatus' => $unlockStatus,'mobileModels' => $mobileModels, 'kiosks' => $kiosks, 'unlockStatusStatement' => $unlockStatusStatement, 'kiosk_id' => $kiosk_id, 'brands' => $brands ,'kioskaddress' => $kioskaddress,'countryOptions' => $countryOptions,'unlock_email_message'=>$unlock_email_message));
						//$Email->config(array('from' => 'me@example.org', 'transport' => 'MyCustom'));
						//$Email = new CakeEmail(array('from' => 'me@example.org', 'transport' => 'MyCustom'));
						$emailTo = $unlockBookingData['customer_email'];
						$Email->template('unlock_booking_receipt');
						$Email->emailFormat('both');
						$Email->to($emailTo);
						$Email->transport(TRANSPORT);
						$Email->from([$send_by_email => $emailSender]);
						//$Email->sender("sales@oceanstead.co.uk");
						$Email->subject('Mobile Unlock Details');
						$send_mail = 0;
						if($send_mail == 1){
							$Email->send();
						}
					}
					
				}
				$statusArr = $this->status_unlock(); //$unlockStatus
				$this->Flash->success($statusArr[$unlockStatus]." (Unlock ID: $mobileUnlockId).");
				$print_type = $this->setting['print_type'];
				if($print_type == 1){
					return $this->redirect(array('controller' => 'prints','action' => 'unlock',$mobileUnlockId));	
				}else{
					return $this->redirect(array('action' => 'all'));
				}
			} else {
				$this->Flash->error(__('The mobile unlock could not be saved. Please, try again.'));
			}			
		}
		
		foreach($brands as $brandID => $brand)break;
		if(!empty($mobilePurchaseDetails)){
			$brandID = $mobilePurchaseDetails['brand_id'];
		}
		if ($this->request->is('post')) {
			$brandID = $this->request['data']['brand_id'];
			//get models based on selected brand if user have posted form same form again
			$mobileModelID = $this->request['data']['mobile_model_id'];
			if($mobileModelID){
				//if user have selected mobile model, check if user have also selected network
				$networkID = $this->request['data']['network_id'];
				$estimatedCost = $this->request['data']['estimated_cost'];
				$this->set(compact('networkID','estimatedCost'));
			}
			$this->set(compact('mobileModelID'));
		}
		$mobileModels_query = $this->MobileUnlocks->MobileModels->find('list',
																				[
																					'keyField' =>'id',
																					'valueField' =>'model',
																					'order'=>'model asc',
																					'conditions' => [
																										'MobileModels.status' => 1,
																										'MobileModels.brand_id IN' => $brandID,
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
		$this->set(compact('kiosks', 'brands', 'networks','mobileModels','mobilePurchaseDetails','mobileUnlockPrice','terms_unlock'));
		$this->set(compact('customerdetail'));
		if(!empty($mobilePurchaseDetails)){
			$this->render('add_mobile');
		}
		
	}
	
	public function getModels(){
		//$this->autoRender = false;
		//capturing the mobile model id and brand ids from mobileunlockprice table with status 1 ie active
		$activeCombinations_query = $this->MobileUnlockPrices->find('all',array('conditions' => array('MobileUnlockPrices.status'=>1,
												       'MobileUnlockPrices.unlocking_price > 0'),
							      'fields' => array('mobile_model_id','brand_id'),
							      'group' => 'MobileUnlockPrices.mobile_model_id'
							      ));
		$activeCombinations_query = $activeCombinations_query->hydrate(false);
		if(!empty($activeCombinations_query)){
			$activeCombinations = $activeCombinations_query->toArray();
		}else{
			$activeCombinations = array();
		}
		$activeBrands = array();
		$activeModels = array();
		foreach($activeCombinations as $key => $activeCombination){
			$activeModels[$activeCombination['mobile_model_id']] = $activeCombination['mobile_model_id'];
		}
		//$this->layout = false;
		$brand_id = $this->request->query('id');
		//$this->request->onlyAllow('ajax');
		$mobileModels_query = $this->MobileUnlocks->MobileModels->find('list',
																		[
																			'keyField' => 'id',
																			'valueField' => 'model',
																			'order'=>'model asc',
																			'conditions' => [
																									'MobileModels.status' => 1,
																									'MobileModels.brand_id IN' => $brand_id,
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
		$this->set(compact('mobileModels')); // Pass $data to the view		
	}
	
	public function getNetworkOptions(){
		$brandID = $this->request->query('brandID');
		$modelID = $this->request->query('modelID');
		
		$mobileUnlockPriceData_query = $this->MobileUnlockPrices->find('all',array('conditions' => array(
											'MobileUnlockPrices.status'=>1,
											'MobileUnlockPrices.brand_id' => $brandID,
											'MobileUnlockPrices.mobile_model_id' => $modelID,
											'MobileUnlockPrices.unlocking_price > 0'
												       ),
							      'fields' => array('network_id')
							      ));
		$mobileUnlockPriceData_query = $mobileUnlockPriceData_query->hydrate(false);
		if(!empty($mobileUnlockPriceData_query)){
			$mobileUnlockPriceData = $mobileUnlockPriceData_query->toArray();
		}else{
			$mobileUnlockPriceData = array();
		}
		$networkOptionsArr = array();
		$networkIds = array();
		foreach($mobileUnlockPriceData as $key => $mobileUnlockPriceInfo){
			$networkIds[$mobileUnlockPriceInfo['network_id']] = $mobileUnlockPriceInfo['network_id'];
		}
		
		$networkData = $this->Networks->find('list',
													[
														'keyField' => 'id',
														'valueField' => 'name',
														'conditions' => ['Networks.id IN' => $networkIds]
													]
											 );
		echo json_encode($networkData);
		die;
	}
	
	public function getUnlockPrice(){
		$networkID = $this->request->query('networkID');
		$brandID = $this->request->query('brandID');
		$modelID = $this->request->query('modelID');
		
		//---------------------------------------
		if(empty($networkID))$networkID = 0;
		if(empty($brandID))$brandID = 0;
		if(empty($modelID))$modelID = 0;
		
		$mobileUnlockPrice_query = $this->MobileUnlockPrices->find('all',array(
							     'conditions' => array( 
										'brand_id' => $brandID,
										'mobile_model_id' => $modelID,
										'network_id' => $networkID,
										'MobileUnlockPrices.unlocking_price > 0'
										),
							     'fields' => array(
									       'unlocking_price',
									       'unlocking_days',
										   'unlocking_cost',
										   'unlocking_minutes',
									      )
							    )
					       );
		$mobileUnlockPrice_query = $mobileUnlockPrice_query->hydrate(false);
		if(!empty($mobileUnlockPrice_query)){
			$mobileUnlockPrice = $mobileUnlockPrice_query->first();
		}else{
			$mobileUnlockPrice = array();
		}
		//$this->request->onlyAllow('ajax');
		//pr($mobileRepairPrice['MobileRepairPrice']);
		//--------code for reading cake query---
		//--------code for reading cake query---
		if(!empty($mobileUnlockPrice)){
			$mobileUnlockPrice['error'] = 0;
		}else{
			$mobileUnlockPrice['error'] = 1;
		}
		echo json_encode($mobileUnlockPrice);die;
		//$this->request->allowMethod('post', 'delete'); or $this->request->allowMethod(array('post', 'delete'));
	}
	
	private function status_unlock($id = null){
		return $repairStatus = array(
				      VIRTUALLY_BOOKED => 'The mobile unlock has been virtually booked',	
				      BOOKED => 'The mobile unlock has been booked',	
				      REBOOKED => 'The mobile unlock has been rebooked',
				      DISPATCHED_2_CENTER => 'Mobile dipatched to unlocking center',
				      UNLOCKED_CONFIRMATION_PASSED => 'Unlocking confirmed to customer',
				      UNLOCKING_FAILED_CONFIRMATION_PASSED => 'Unlocking failed, amount refunded and confirmation passed to customer',
				      RECEIVED_UNLOCKED_FROM_CENTER => 'Phone unlocked and confirmation sent to unlocking center',
				      RECEIVED_UNPROCESSED_FROM_CENTER => 'Phone/Request received from unlocking center for failed unlocking',
				      REFUND_RAISED => '--REFUND_RAISED-',
				      DELIVERED_UNLOCKED_BY_CENTER => 'Payment received and confirmation sent to customer',
				      DELIVERED_UNLOCKING_FAILED_AT_CENTER => 'Unlocking failed and delived to customer. No Amount charged!',
				      DELIVERED_UNLOCKED_BY_KIOSK => 'Unlock processed at kiosk and customer informed',
				      DELIVERED_UNLOCKING_FAILED_AT_KIOSK => 'Unlock Failed at Kiosk and customer informed',
				      REQUEST_RECEIVED_IN_PROCESS => 'Unlocking under process and confirmation sent to kiosk',
				      PHONE_RECEIVED_BY_CENTER => 'Phone received and confirmation sent to Kiosk',
				      UNLOCK_UNDER_PROCESS => '--UNLOCK_UNDER_PROCESS--',
				      WAITING_FOR_DISPATCH_UNLOCKED => '--WAITING_FOR_DISPATCH_UNLOCKED--',
				      UNLOCK_PROCESSED_CONFIRMATION_SENT_2_KIOSK => 'Unlocking processed and confirmation sent to kiosk',
				      DISPATCHED_2_KIOSK_UNLOCKED => 'Unlock Done and Phone dispatched to kiosk',
				      DISPATCHED_2_KIOSK_UNPROCESSED => 'Unlocking Failed. Dispatched to Kiosk.',
				      UNLOCK_FAILED_CONFIRMATION_SENT_2_KIOSK => 'Unlocking failed and confirmation sent to kiosk',
				      );
	}
	
	public function addUnlockPayment(){
		$setting = $this->setting;
		$this->set(compact('setting'));
		//echo "hi";die;
		//pr($this->request);die;
		//pr($this->Session->read('received_reprd_from_tech_data'));
		//$this->RepairPayment->query('TRUNCATE `repair_payments`');
		if($this->request->session()->read('Auth.User.group_id') != KIOSK_USERS){
			$this->Flash->error('Only kiosk user can authorize/enter payment');
			return $this->redirect(array('action' => 'index'));
		}
		//pr($this->RepairPayment->find('all'));
		$paymentType = array('Cash' => 'Cash', 'Card' => 'Card');
		$this->set(compact('paymentType'));
		
		if(is_array($this->request->Session()->read('add_unlock_session'))){
			$basket = "add_unlock_session";
			$session_basket = $this->request->Session()->read('add_unlock_session');
			//$sessionUnlockId = $session_basket['MobileUnlock']['id'];
		}else{
			return $this->redirect(array('action' => 'index'));
			die;
		}
		
		if ($this->request->is(array('post', 'put'))) {
			if(array_key_exists('cancel',$this->request->data)){
				$this->request->Session()->delete($basket);
				return $this->redirect(array('controller'=>'mobile_unlocks','action' => 'add'));
				die;
			}
			$amountToPay = $this->request['data']['final_amount'];
			$totalPaymentAmount = 0;
			$amountDesc = array();
			$error = '';
			$errorStr = '';
			
			foreach($this->request['data']['Payment']['Amount'] as $key => $paymentAmount){
				$totalPaymentAmount+= $paymentAmount;
				$paymentDescription = $this->request['data']['Payment']['Description'][$key];
			}
			
			if($totalPaymentAmount<$amountToPay){
				$error[] = "Amount must be equivalent to &#163; {$amountToPay}. Please try again";
			}elseif($totalPaymentAmount>$amountToPay){
				$error[] = "Amount must be equivalent to &#163; {$amountToPay}. Please try again";
			}
			
			if(!empty($error)){
				$errorStr = implode("<br/>",$error);
				$this->Flash->error("$errorStr");
				return $this->redirect(array('action'=>'add_unlock_payment'));
			}
			
			$counter = 0;
			foreach($this->request['data']['Payment']['Amount'] as $key => $paymentAmount){
				$paymentMethod = $this->request['data']['Payment']['Payment_Method'][$key];
				$paymentDescription = $this->request['data']['Payment']['Description'][$key];
				$payment_status = 1;//since we do not have option for credit here, so just sending status 1 as payment done
				
				if(!empty($paymentAmount)){// && $paymentDescription
					$paymentDetailData = array(
							'kiosk_id' => $this->request->Session()->read('kiosk_id'),
							'user_id' => $this->request->session()->read('Auth.User.id'),
							//'mobile_unlock_id' => $sessionUnlockId,
							'payment_method' => $paymentMethod,
							'description' => $paymentDescription,
							'amount' => $paymentAmount,
							'payment_status' => $payment_status,
							'status' => 1,//this 1 currently does not have any relevance
							   );
					$this->UnlockPayment->clear;
					$this->UnlockPayment->create();
					$sessionBskt = array();
					if($this->UnlockPayment->save($paymentDetailData)){
						$counter++;
						$session_basket['payment_id'][] = $this->UnlockPayment->id;
						//$sessionBskt['unlock_payment_status'] = $sessionUnlockId;
						//here we are sending payment status in session to unlock edit as an identifier for successful payment
					}
				}
			}
			if($counter>0){
				$this->request->Session()->write('add_unlock_session',$session_basket);
				return $this->redirect(array('controller'=>'mobile_unlocks','action'=>'add'));;
			}else{
				$flashMessage = ("Payment could not be processed. Please try again");
				$this->request->Session()->delete($basket);
				$this->request->Session()->setFlash($flashMessage);
				return $this->redirect(array('controller'=>'mobile_unlocks','action'=>'add'));
			}
		}
	}
	
	public function finalStepAjax(){
	 // pr($_SESSION);die;
		if(!empty($this->request->query) && array_key_exists('add_unlock_session',$_SESSION)){
			if(!empty($_SESSION['add_unlock_session'])){
				$unlock_basket = $_SESSION['add_unlock_session'];
				  unset($_SESSION['add_unlock_session']);
			}else{
				echo json_encode(array('error' => 'basket is empty'));die;
			}
			
			if(!empty($unlock_basket)){
				$brdID = $unlock_basket['brand_id'];
				$mobilMdlId = $unlock_basket['mobile_model_id'];
				$result_query = $this->MobileModels->find('all',array('conditions' => array('MobileModels.id' => $mobilMdlId,
																			 'MobileModels.brand_id' => $brdID,
																			 )
													   ));
				$result_query = $result_query->hydrate(false);
				if(!empty($result_query)){
					$result = $result_query->first();
				}else{
					$result = array();
				}
				if(empty($result)){
					echo json_encode(array('error' => 'The mobile unlock could not be saved. Please Choose Right Combination For Brand And Model.'));die;
				}
			}
			
			$unlock_pay_id = array();
			$final_amount = $this->request->query['final_amount'];
			$payment_1 = $this->request->query['payment_1'];
			$payment_2 = $this->request->query['payment_2'];
			$method_1 = $this->request->query['method_1'];
			$method_2 = $this->request->query['method_2'];
			$part_time = $this->request->query['part_time'];
			$payment_status = 1;
			if($part_time == 1){
				if($final_amount == $payment_1 + $payment_2){
					$paymentDetailData = array(
							'kiosk_id' => $this->request->Session()->read('kiosk_id'),
							'user_id' => $this->request->session()->read('Auth.User.id'),
							'payment_method' => $method_1,
							'amount' => $payment_1,
							'payment_status' => $payment_status,
							'status' => 1,//this 1 currently does not have any relevance
							   );
					$unlockPaymentEntity = $this->UnlockPayments->newEntity();
					$unlockPaymentEntity = $this->UnlockPayments->patchEntity($unlockPaymentEntity,$paymentDetailData);
					$this->UnlockPayments->save($unlockPaymentEntity);
					$unlock_pay_id[] = $unlockPaymentEntity->id;
					
					$paymentDetailData_1 = array(
							'kiosk_id' => $this->request->Session()->read('kiosk_id'),
							'user_id' => $this->request->session()->read('Auth.User.id'),
							'payment_method' => $method_2,
							'amount' => $payment_2,
							'payment_status' => $payment_status,
							'status' => 1,//this 1 currently does not have any relevance
							   );
					$unlockPaymentEntity1 = $this->UnlockPayments->newEntity();
					$unlockPaymentEntity1 = $this->UnlockPayments->patchEntity($unlockPaymentEntity1,$paymentDetailData_1);
					$this->UnlockPayments->save($unlockPaymentEntity1);
					$unlock_pay_id[] = $unlockPaymentEntity1->id;
				}else{
					echo json_encode(array('error' => 'amount is not matching'));die;
				}
			}else{
				if($final_amount == $payment_1){
					$paymentDetailData = array(
							'kiosk_id' => $this->request->Session()->read('kiosk_id'),
							'user_id' => $this->request->session()->read('Auth.User.id'),
							'payment_method' => $method_1,
							'amount' => $payment_1,
							'payment_status' => $payment_status,
							'status' => 1,//this 1 currently does not have any relevance
							   );
					$unlockPaymentEntity = $this->UnlockPayments->newEntity();
					$unlockPaymentEntity = $this->UnlockPayments->patchEntity($unlockPaymentEntity,$paymentDetailData);
					$this->UnlockPayments->save($unlockPaymentEntity);
					$unlock_pay_id[] = $unlockPaymentEntity->id;
				}else{
					echo json_encode(array('error' => 'amount is not matching'));die;
				}
			}
			//pr($unlock_basket);die;
			$kiosk_id = $this->request->Session()->read('kiosk_id');
			$user_id = $this->request->session()->read('Auth.User.id');
			$this->save_unlock_details($unlock_basket,$unlock_pay_id,$kiosk_id,$user_id);
		}else{
			echo json_encode(array('error' => 'no basket or query string is empty'));die;
		}
	}
	
	public function save_unlock_details($unlock_basket,$unlock_pay_id,$kiosk_id,$user_id){
		if(!empty($unlock_basket) && !empty($unlock_pay_id)){
			$unlock_basket['kiosk_id'] = $kiosk_id;
			$unlock_basket['booked_by'] = $user_id;			
			$unlock_status = $unlock_basket['status'];
			
			if(array_key_exists('imei',$unlock_basket)){
				$imei = $unlock_basket['imei'].$unlock_basket['imei1'];
			}
			$unlock_basket['imei']= $imei;
			//rajju dec 28,2015
			//------------------------------
			//$unlocl_cost = $this->request->data['MobileUnlock']['net_cost_b'];
			$unlockData = $unlock_basket;
			unset($unlockData['street_address']);
			unset($unlockData['formValid']);
			unset($unlockData['unlocking_days']);
			unset($unlockData['unlocking_minutes']);
			unset($unlockData['imei1']);
			unset($unlockData['cust_email']);
			unset($unlockData['show_unlock_minutes']);
			///pr($unlockData);die;
			$checkDbData_query = $this->MobileUnlocks->find('all',array('conditions' => $unlockData));
			//pr($checkDbData_query);
			$checkDbData_query = $checkDbData_query->hydrate(false);
			if(!empty($checkDbData_query)){
				$checkDbData = $checkDbData_query->first();
			}else{
				$checkDbData = array();
			}
			$addToDatabase = true;
			if(count($checkDbData) >= 1){
				$addToDatabase = false;
			}else{
				;//echo "Record do not exist!";
			}
			
			$mobileUnlockEntity = $this->MobileUnlocks->newEntity($unlock_basket,['validate' => false]);
			$mobileUnlockEntity = $this->MobileUnlocks->patchEntity($mobileUnlockEntity,$unlock_basket,['validate' => false]);
			//$mobileUnlockEntity = $this->MobileUnlocks->save($mobileUnlockEntity);
			//debug($this->MobileUnlocks->save($mobileUnlockEntity));die;
			if ($addToDatabase && $this->MobileUnlocks->save($mobileUnlockEntity)) {
				//pr($this->request);die;
				//updating the unlock id in payment table
				if(count($unlock_pay_id)){
					foreach($unlock_pay_id as $pi => $payment_id){
						$UnlockPaymentsEntity = $this->UnlockPayments->get($payment_id);
						$mobileUnlckId = $mobileUnlockEntity->id;
						$data = array('mobile_unlock_id' => $mobileUnlckId);
						$UnlockPaymentsEntity = $this->UnlockPayments->patchEntity($UnlockPaymentsEntity,$data);
						$this->UnlockPayments->save($UnlockPaymentsEntity);
					}
				}
				$mobileUnlockId = $mobileUnlockEntity->id;
				$mobileUnlockLogsData = array(
					'kiosk_id' => $kiosk_id,
					'user_id' => $this->request->session()->read('Auth.User.id'),
					'mobile_unlock_id' => $mobileUnlockEntity->id,					
					'unlock_status' => $unlock_status
					);
				
				$mobileUnlockLogEntity = $this->MobileUnlockLogs->newEntity();
				$mobileUnlockLogEntity = $this->MobileUnlockLogs->patchEntity($mobileUnlockLogEntity,$mobileUnlockLogsData);
				$this->MobileUnlockLogs->save($mobileUnlockLogEntity);				
				$amount = $unlock_basket['estimated_cost'];
				$dateUnlocked = date('Y-m-d H:i:s');
				
				
				$activeCombinations_query = $this->MobileUnlockPrices->find('all',array('conditions' => array('MobileUnlockPrices.status'=>1,
												       'MobileUnlockPrices.unlocking_price > 0'),
							      'fields' => array('mobile_model_id','brand_id'),
							      'group' => 'MobileUnlockPrices.mobile_model_id'
							      ));
				$activeCombinations_query = $activeCombinations_query->hydrate(false);
				if(!empty($activeCombinations_query)){
					$activeCombinations = $activeCombinations_query->toArray();
				}else{
					$activeCombinations = array();
				}
				
				$activeBrands = array();
				$activeModels = array();
				foreach($activeCombinations as $key => $activeCombination){
					$activeBrands[$activeCombination['brand_id']] = $activeCombination['brand_id'];
					$activeModels[$activeCombination['mobile_model_id']] = $activeCombination['mobile_model_id'];
				}
				
				 
				
				if($unlock_status == VIRTUALLY_BOOKED){
					$dateUnlocked = date('Y-m-d H:i:s');
                  $mobileUnlockSalesData = array(
								'kiosk_id' => $kiosk_id,
								'retail_customer_id' => $unlock_basket['retail_customer_id'],
								'mobile_unlock_id' => $mobileUnlockEntity->id,
								'sold_by' => $this->request->session()->read('Auth.User.id'),
								'sold_on' => "$dateUnlocked",
								'refund_by' => '',
								'amount' => $amount,
								'refund_amount' => '',
								'refund_status' => 0,
								'refund_on' => '',
								'refund_remarks' => '',
                                'status' => 0,
									   );
				  //pr($mobileUnlockSalesData);die;
					$mobileUnlockSaleEmtity = $this->MobileUnlockSales->newEntity();
					$mobileUnlockSaleEmtity = $this->MobileUnlockSales->patchEntity($mobileUnlockSaleEmtity,$mobileUnlockSalesData);
					 //pr($mobileUnlockSaleEmtity);die;
                    if($this->MobileUnlockSales->save($mobileUnlockSaleEmtity, array('validate' => false))){
                        //echo"hi";die;
						if(count($unlock_pay_id)){
							foreach($unlock_pay_id as $pi => $payment_id){
								$unlockPaymentEntity1 = $this->UnlockPayments->get($payment_id);
                                $mobileUnlckSaleId = $mobileUnlockSaleEmtity->id; 
								$data = array('mobile_unlock_sale_id' => $mobileUnlckSaleId);
								$unlockPaymentEntity1 = $this->UnlockPayments->patchEntity($unlockPaymentEntity1,$data);
								$this->UnlockPayments->save($unlockPaymentEntity1);
							}
						}
					}else{
						//pr($mobileUnlockSaleEmtity->errors()); die;
						$unlock_id_data = $this->MobileUnlocks->get($mobileUnlockId);
						$this->MobileUnlocks->delete($unlock_id_data);
						$alterQuery = "ALTER TABLE `mobile_unlocks` AUTO_INCREMENT = $mobileUnlockId";
						$conn = ConnectionManager::get('default');
						$stmt = $conn->execute($alterQuery); 
						if(count($unlock_pay_id)){
							foreach($unlock_pay_id as $pi => $payment_id){
								$unlockPaymentEntity1 = $this->UnlockPayments->get($payment_id);
                                $this->UnlockPayments->delete($unlockPaymentEntity1);
							}
						}
						//echo json_encode(array('error' => 'Unlock could not be saved'));die;
						$tmp = $mobileUnlockSaleEmtity->errors();
						$tmpStr = serialize($tmp);
						echo json_encode(array(
												'error' => 'Unlock could not be saved',
												'tmpStr' => $tmpStr,
												'sold_on' => $dateUnlocked));
						die;
                         pr($mobileUnlockSaleEmtity->errors()); die;
                    }
				} 
		
				$mobileModels_query = $this->MobileUnlocks->MobileModels->find('list',
																						[
																							'keyField' => 'id',
																							'valueField' => 'model',
																							'order'=>'model asc',
																							'conditions' => [
																												'MobileModels.status' => 1,
																												'MobileModels.id IN' => $activeModels
																											]
																						]);
				$mobileModels_query = $mobileModels_query->hydrate(false);
				if(!empty($mobileModels_query)){
					$mobileModels = $mobileModels_query->toArray();
				}else{
					$mobileModels = array();
				}
		
				$unlockBookingData = $unlock_basket;
				$unlockStatus = $unlockBookingData['status'];
				$messageStatement = '';
				$customerContact = $unlock_basket['customer_contact'];
				$duration = "";
				if(empty($unlockBookingData['unlocking_days'])){
					$duration = $this->convertToHoursMins($unlockBookingData['unlocking_minutes'], '%02d hours %02d minutes');		
				}else{
					$duration = $unlockBookingData['unlocking_days']." working day(s)";
				}
				switch($unlockStatus){
					case VIRTUALLY_BOOKED:
						
						$unlockStatusStatement = "The unlock has been booked for your unlock id ".$mobileUnlockId." and mobile model:".$mobileModels[$unlockBookingData['mobile_model_id']]." phone &#40;IMEI: ".$unlockBookingData['imei']."&#41;. Your unlock is expected to get done within {$duration}&#41;.<br/><br/>";
						$messageStatement = "Your phone unlock id ".$mobileUnlockId." has been booked and will be unlocked within {$duration} Please contact the kiosk with any queries t&s ".$this->setting['repair_unlock_terms_link'];
						break;
					
					case BOOKED:
						/*$unlockStatusStatement = "The unlock has been booked for your ".$mobileModels[$unlockBookingData['mobile_model_id']]." phone &#40;IMEI: ".$unlockBookingData['imei']."&#41;. Your unlock is expected to get done within ".$unlockBookingData['unlocking_days']." working day&#40;s&#41;.<br/><br/>";*/
						$unlockStatusStatement = "The unlock has been booked for your unlock id ".$mobileUnlockId." and mobile model:".$mobileModels[$unlockBookingData['mobile_model_id']]." phone &#40;IMEI: ".$unlockBookingData['imei']."&#41;. Your unlock is expected to get done within {$duration}.<br/><br/>";
						$messageStatement = "Your phone unlock id ".$mobileUnlockId." has been booked and will be unlocked within {$duration}. Please contact the kiosk with any queries t&s ".$this->setting['repair_unlock_terms_link'];
						break;
				}
				$countryOptions = Configure::read('uk_non_uk');
				//$this->set(compact('countryOptions'));
				
				$kiosks_query = $this->MobileUnlocks->Kiosks->find('list',array(
											 'conditions' => array('Kiosks.status' => 1)
											));
				$kiosks_query = $kiosks_query->hydrate(false);
				if(!empty($kiosks_query)){
					$kiosks = $kiosks_query->toArray();
				}else{
					$kiosks = array();
				}
				$kioskaddress_query = $this->Kiosks->find('all',array(
							'fields' => array('Kiosks.address_1','Kiosks.address_2', 'Kiosks.city','Kiosks.state','Kiosks.country','Kiosks.zip','Kiosks.contact'),
							'conditions'=> array('Kiosks.id' => $kiosk_id)
							)
						);
				$kioskaddress_query = $kioskaddress_query->hydrate(false);
				if(!empty($kioskaddress_query)){
					$kioskaddress = $kioskaddress_query->first();
				}else{
					$kioskaddress = array();
				}
				
				$networks_query = $this->MobileUnlocks->Networks->find('list');
				$networks_query = $networks_query->hydrate(false);
				if(!empty($networks_query)){
					$networks = $networks_query->toArray();
				}else{
					$networks = array();
				}
				
				$brands_query = $this->MobileUnlocks->Brands->find('list',
																		[
																			'keyField' => 'id',
																			'valueField' => 'brand',
																			'order'=>'brand asc',
																			'conditions' => array('Brands.status' => 1,
																												'Brands.id IN' => $activeBrands)
																		]);
				$brands_query = $brands_query->hydrate(false);
				if(!empty($brands_query)){
					$brands = $brands_query->toArray();
				}else{
					$brands = array();
				}
				$unlock_email_message = $this->setting['unlock_email_message'];
				$send_by_email = Configure::read('send_by_email');
				$emailSender = Configure::read('EMAIL_SENDER');
				if(!empty($unlockStatusStatement)){
					if(!empty($customerContact)){
						$destination = $customerContact;
						if(!empty($messageStatement)){
							$this->TextMessage->test_text_message($destination, $messageStatement);
						}
					}
					if(!empty($emailTo)){
						$Email = new Email();
						$Email->config('default');
						$Email->viewVars(array('unlockBookingData' => $unlockBookingData,'unlockStatus' => $unlockStatus,'mobileModels' => $mobileModels, 'kiosks' => $kiosks, 'unlockStatusStatement' => $unlockStatusStatement, 'kiosk_id' => $kiosk_id, 'brands' => $brands ,'kioskaddress' => $kioskaddress,'countryOptions' => $countryOptions,'unlock_email_message'=>$unlock_email_message));
						$emailTo = $unlockBookingData['customer_email'];
						$Email->template('unlock_booking_receipt');
						$Email->emailFormat('both');
						$Email->to($emailTo);
						 $Email->transport(TRANSPORT);
						$Email->from([$send_by_email => $emailSender]);
						//$Email->sender("sales@oceanstead.co.uk");
						$Email->subject('Mobile Unlock Details');
						$send_mail = 0;
						if($send_mail == 1){
							$Email->send();
						}
					}
					
				}
				$statusArr = $this->status_unlock(); //$unlockStatus
				$msg = "";
				$msg = $statusArr[$unlockStatus]." (Unlock ID: $mobileUnlockId).";
				echo json_encode(array('status' => $msg,'id' => $mobileUnlockId));die;
			}else{
				//debug($mobileUnlockEntity->errors());die;
				echo json_encode(array('error' => 'The mobile unlock could not be saved'));die;
			}
		} else {
			echo json_encode(array('error' => 'The mobile unlock could not be saved. Please, try again.'));die;
		}	
	}
	
	public function cancelAjax(){
		unset($_SESSION['add_unlock_session']);
		echo json_encode(array('status' =>'success'));
		die;
	}
	
	public function search($keyword = ""){
		$kiosks_query = $this->Kiosks->find('list',
													[
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
		
		$searchKW = strtolower($this->request->query['search_kw']);
		$imei = $this->request->query['imei'];
		$unlock_id = $this->request->query['unlock_id'];
		$andArr = $kioskSearchArr = $searchCriteriaArr = $conditionArr = array();
		if(!empty($searchKW)){
			$conditionArr = array('OR' => array(
								'LOWER(MobileModels.model) like' => "%$searchKW%" ,
								'LOWER(MobileUnlocks.customer_email) like' => "%$searchKW%",
								'LOWER(MobileUnlocks.customer_fname) like' => "%$searchKW%",
							)	
						);
		}
		if(!empty($unlock_id)){$conditionArr[] = "MobileUnlocks.id = $unlock_id";}
		if(!empty($imei)){$conditionArr[] = "MobileUnlocks.imei like '%$imei%'";}
		if($this->request->session()->read('Auth.User.group_id') == KIOSK_USERS){
			$kiosk_id = $this->request->Session()->read('kiosk_id');
			$andArr['kiosk_id'] = $kiosk_id;
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
						"MobileUnlocks.modified >" => date('Y-m-d', strtotime($this->request->query['start_date'])),
						"MobileUnlocks.modified <" => date('Y-m-d', strtotime($this->request->query['end_date']. ' +1 Days')),			
					       );
		}
		//pr($this->request);
		if($this->request->query['MobileUnlock']['status'] != -1){
			$status = $this->request->query['MobileUnlock']['status'];
			$conditionArr[] = array(
						"MobileUnlocks.status" => $status
					       );
			$this->set(compact('status', $status));
		}
		
		if(count($andArr) && count($conditionArr)){
			$searchCriteriaArr['AND'] = array('OR' => $conditionArr,'AND' => $andArr);
			//kiosk and conditionarrray has values
		}elseif(count($andArr)){
			$searchCriteriaArr[] = $andArr;
			//kiosk and empty condition array
		}else{
			$dataKioskID = '';
            //pr($this->request->query);die;
			if(array_key_exists('MobileUnlock', $this->request->query)){
				$kioskId = $this->request->query['MobileUnlock'];
				if(array_key_exists('kiosk_id',$this->request->query['MobileUnlock']) && !empty($this->request->query['MobileUnlock']['kiosk_id'])){
					$conditionArr[] = array('MobileUnlocks.kiosk_id' =>$this->request->query['MobileUnlock']['kiosk_id']);
					$selectedKiosk = $this->request->query['MobileUnlock']['kiosk_id'];
					if((int)$selectedKiosk){
						//$this->request->Session()->write('kiosk_id',$selectedKiosk);
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
						$conditionArr[] = array('MobileUnlocks.kiosk_id IN' =>$managerKiosk);
					}
					
					
					//$this->request->Session()->write('kiosk_id','');
				}
				$dataKioskID = $this->request->query['MobileUnlock']['kiosk_id'];
			}
		
			
			$this->set('kioskId', $dataKioskID);
			if(!empty($conditionArr)){
				$searchCriteriaArr[] = $conditionArr;
			}
			//admin
		}
		$this->paginate = [
					'conditions' => $searchCriteriaArr,
					'limit' => ROWS_PER_PAGE,
					'order' => ['MobileUnlocks.id desc'],
					'contain' => ['Kiosks','Networks','MobileModels']
				];
		//pr($this->paginate);die;
		$mobileUnlocks = $this->paginate('MobileUnlocks');
		$hint = $this->ScreenHint->hint('mobile_unlocks','index');
					if(!$hint){
						$hint = "";
					}
		$this->set(compact('hint','mobileUnlocks','kiosks'));
		//$this->layout = 'default'; 
		//$this->viewPath = 'MobileUnlocks';
		$this->render('index');
	}
	public function all() {
		//$cookieKioskId = $this->Cookie->read('kiosk_id');
	//	$cookieKioskId = $this->request->session()->read('kiosk_id');
	//	$this->set('cookieKioskId',$cookieKioskId);
		//deleting the sessions created during payment if user does not complete the process
		$this->request->Session()->delete('unlock_data_session');
		$this->request->Session()->delete('unlock_payment_confirmation');
		$kiosks_query = $this->Kiosks->find('list',
													[
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
		if(!empty($kiosk_id) && $this->request->session()->read('Auth.User.group_id') == KIOSK_USERS){
			$this->paginate = [
						'conditions' => ['kiosk_id' => $kiosk_id],
						'limit' => ROWS_PER_PAGE,
						'order' => ['MobileUnlocks.id DESC'],
						'contain' => ['Kiosks','Networks','MobileModels']
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
						'conditions' => ['MobileUnlocks.kiosk_id IN' => $managerKiosk],
						'limit' => ROWS_PER_PAGE,
						'order' => ['MobileUnlocks.id DESC'],
						'contain' => ['Kiosks','Networks','MobileModels']
						   ];
			   }else{
					$this->paginate = [
						'limit' => ROWS_PER_PAGE,
						'order' => ['MobileUnlocks.id DESC'],
						'contain' => ['Kiosks','Networks','MobileModels']
						   ];
			   }
			}else{
				$this->paginate = [
						'limit' => ROWS_PER_PAGE,
						'order' => ['MobileUnlocks.id DESC'],
						'contain' => ['Kiosks','Networks','MobileModels']
						   ];
			}
			
			
		}
		$mobileUnlocks = $this->paginate("MobileUnlocks");
		//$mobileUnlocks = $mobileUnlocks_query->toArray();
		$hint = $this->ScreenHint->hint('mobile_unlocks','all');
		
					if(!$hint){
						$hint = "";
					}
					
		$this->set(compact('hint','kiosks'));
		$this->set('mobileUnlocks', $mobileUnlocks);
		$this->render('index');
	}
	
	public function booked() {
		//$cookieKioskId = $this->Cookie->read('kiosk_id');
		//$cookieKioskId = $this->request->Session()->read('kiosk_id');
		//$this->set('cookieKioskId',$cookieKioskId);
		$kiosks_query = $this->Kiosks->find('list',
													[
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
		//unlock_status kiosk users: booked
		$kiosk_id = $this->request->Session()->read('kiosk_id');
		//$this->request->session()->read('Auth.User.group_id');die;
		if(!empty($kiosk_id) && $this->request->session()->read('Auth.User.group_id') == KIOSK_USERS){
			$this->paginate = [
						'conditions' => ['MobileUnlocks.status' => BOOKED, 'kiosk_id' => $kiosk_id],
						'limit' => ROWS_PER_PAGE,
						'order' => ['MobileUnlocks.id DESC'],
						'contain' => ['Kiosks','Networks','MobileModels']
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
						'conditions' => ['MobileUnlocks.status' => BOOKED,
										 'MobileUnlocks.kiosk_id IN' => $managerKiosk,
										 ],
						'limit' => ROWS_PER_PAGE,
						'order' => ['MobileUnlocks.id DESC'],
						'contain' => ['Kiosks','Networks','MobileModels']
						   ];	
			   }else{
					$this->paginate = [
						'conditions' => ['MobileUnlocks.status' => BOOKED],
						'limit' => ROWS_PER_PAGE,
						'order' => ['MobileUnlocks.id DESC'],
						'contain' => ['Kiosks','Networks','MobileModels']
						   ];
			   }
			}else{
				$this->paginate = [
						'conditions' => ['MobileUnlocks.status' => BOOKED],
						'limit' => ROWS_PER_PAGE,
						'order' => ['MobileUnlocks.id DESC'],
						'contain' => ['Kiosks','Networks','MobileModels']
						   ];
			}
			
			
		}
		//pr($this->paginate);die;
		$hint = $this->ScreenHint->hint('mobile_unlocks','booked');
					if(!$hint){
						$hint = "";
					}
					
		$mobileUnlocks = $this->paginate("MobileUnlocks");
		$this->set(compact('hint','kiosks'));
		$this->set('mobileUnlocks',$mobileUnlocks);
		$this->render('index');
	}
	
	public function virtuallyBooked() {
		//$cookieKioskId = $this->Cookie->read('kiosk_id');
		//$cookieKioskId = $this->request->session()->read('kiosk_id');
		//$this->set('cookieKioskId',$cookieKioskId);
		$kiosks_query = $this->Kiosks->find('list',
													[
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
		//unlock_status kiosk users: virtually booked
		$kiosk_id = $this->request->Session()->read('kiosk_id');
		if(!empty($kiosk_id) && $this->request->session()->read('Auth.User.group_id') == KIOSK_USERS){
			$this->paginate = array(
						'conditions' => ['MobileUnlocks.status' => VIRTUALLY_BOOKED, 'kiosk_id' => $kiosk_id],
						'limit' => ROWS_PER_PAGE,
						'order' => ['MobileUnlocks.id DESC'],
						'contain' => ['Kiosks','Networks','MobileModels']
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
						'conditions' => ['MobileUnlocks.status' => VIRTUALLY_BOOKED,
										 'MobileUnlocks.kiosk_id IN' => $managerKiosk
										 ],
						'limit' => ROWS_PER_PAGE,
						'order' => ['MobileUnlocks.id DESC'],
						'contain' => ['Kiosks','Networks','MobileModels']
						   );
			   }else{
					$this->paginate = array(
						'conditions' => ['MobileUnlocks.status' => VIRTUALLY_BOOKED],
						'limit' => ROWS_PER_PAGE,
						'order' => ['MobileUnlocks.id DESC'],
						'contain' => ['Kiosks','Networks','MobileModels']
						   );
			   }
			}else{
				$this->paginate = array(
						'conditions' => ['MobileUnlocks.status' => VIRTUALLY_BOOKED],
						'limit' => ROWS_PER_PAGE,
						'order' => ['MobileUnlocks.id DESC'],
						'contain' => ['Kiosks','Networks','MobileModels']
						   );	
			}
			
			
		}
		$hint = $this->ScreenHint->hint('mobile_unlocks','virtually_booked');
					if(!$hint){
						$hint = "";
					}
		$mobileUnlocks = $this->paginate("MobileUnlocks");
		$this->set(compact('hint','kiosks'));
		$this->set('mobileUnlocks', $mobileUnlocks);
		$this->render('index');
	}
	
	public function unlockRequestSent() {
		//$cookieKioskId = $this->Cookie->read('kiosk_id');
		//$cookieKioskId = $this->request->session()->read('kiosk_id');
		//$this->set('cookieKioskId',$cookieKioskId);
		$kiosks_query = $this->Kiosks->find('list',
													[
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
		//unlock_status kiosk users: Unlock request sent to Unlocking center
		$kiosk_id = $this->request->Session()->read('kiosk_id');
		if(!empty($kiosk_id) && $this->request->session()->read('Auth.User.group_id') == KIOSK_USERS){
			$this->paginate = [
						'conditions' => ['MobileUnlocks.status' => UNLOCK_REQUEST_SENT, 'kiosk_id' => $kiosk_id],
						'limit' => ROWS_PER_PAGE,
						'order' => ['MobileUnlocks.id DESC'],
						'contain' => ['Kiosks','Networks','MobileModels']
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
						'conditions' => ['MobileUnlocks.status' => UNLOCK_REQUEST_SENT,
										 'MobileUnlocks.kiosk_id IN' => $managerKiosk,
										 ],
						'limit' => ROWS_PER_PAGE,
						'order' => ['MobileUnlocks.id DESC'],
						'contain' => ['Kiosks','Networks','MobileModels']
						   ];	
			   }else{
					$this->paginate = [
						'conditions' => ['MobileUnlocks.status' => UNLOCK_REQUEST_SENT],
						'limit' => ROWS_PER_PAGE,
						'order' => ['MobileUnlocks.id DESC'],
						'contain' => ['Kiosks','Networks','MobileModels']
						   ];
			   }
			}else{
					$this->paginate = [
						'conditions' => ['MobileUnlocks.status' => UNLOCK_REQUEST_SENT],
						'limit' => ROWS_PER_PAGE,
						'order' => ['MobileUnlocks.id DESC'],
						'contain' => ['Kiosks','Networks','MobileModels']
						   ];
			}
			
			
		}
		$mobileUnlocks = $this->paginate("MobileUnlocks");
		$hint = $this->ScreenHint->hint('mobile_unlocks','unlock_request_sent');
					if(!$hint){
						$hint = "";
					}
		$this->set(compact('hint','kiosks'));
		$this->set('mobileUnlocks', $mobileUnlocks);
		$this->render('index');
	}
	
	public function dispatched() {
		//$cookieKioskId = $this->Cookie->read('kiosk_id');
		//$cookieKioskId = $this->request->session()->read('kiosk_id');
		//$this->set('cookieKioskId',$cookieKioskId);
		$kiosks_query = $this->Kiosks->find('list',
													[
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
		//unlock_status kiosk users:'Dispatched to Unlocking Center'
		$kiosk_id = $this->request->Session()->read('kiosk_id');
		if(!empty($kiosk_id) && $this->request->session()->read('Auth.User.group_id') == KIOSK_USERS){
			$this->paginate = [
						'conditions' => ['MobileUnlocks.status' => DISPATCHED_2_CENTER, 'kiosk_id' => $kiosk_id],
						'limit' => ROWS_PER_PAGE,
						'order' => ['MobileUnlocks.id DESC'],
						'contain' => ['Kiosks','Networks','MobileModels']
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
						'conditions' => ['MobileUnlocks.status' => DISPATCHED_2_CENTER,
										 'MobileUnlocks.kiosk_id IN' => $managerKiosk,
										 ],
						'limit' => ROWS_PER_PAGE,
						'order' => ['MobileUnlocks.id DESC'],
						'contain' => ['Kiosks','Networks','MobileModels']
						   ];		
			   }else{
					$this->paginate = [
						'conditions' => ['MobileUnlocks.status' => DISPATCHED_2_CENTER],
						'limit' => ROWS_PER_PAGE,
						'order' => ['MobileUnlocks.id DESC'],
						'contain' => ['Kiosks','Networks','MobileModels']
						   ];
			   }
			}else{
				$this->paginate = [
						'conditions' => ['MobileUnlocks.status' => DISPATCHED_2_CENTER],
						'limit' => ROWS_PER_PAGE,
						'order' => ['MobileUnlocks.id DESC'],
						'contain' => ['Kiosks','Networks','MobileModels']
						   ];
			}
			
			
		}
		$mobileUnlocks = $this->paginate("MobileUnlocks");
		$hint = $this->ScreenHint->hint('mobile_unlocks','dispatched');
					if(!$hint){
						$hint = "";
					}
		$this->set(compact('hint','kiosks'));
		$this->set('mobileUnlocks', $mobileUnlocks);
		$this->render('index');
	}
	
	public function unlocked() {
		//$cookieKioskId = $this->Cookie->read('kiosk_id');
		//$cookieKioskId = $this->request->session()->read('kiosk_id');
		//$this->set('cookieKioskId',$cookieKioskId);
		$kiosks_query = $this->Kiosks->find('list',
													[
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
		//unlock_status kiosk users:'Unlocked: Confirmation passed to customer'
		$kiosk_id = $this->request->Session()->read('kiosk_id');
		if(!empty($kiosk_id) && $this->request->session()->read('Auth.User.group_id') == KIOSK_USERS){
			$this->paginate= [
						'conditions' => ['MobileUnlocks.status' => UNLOCKED_CONFIRMATION_PASSED, 'kiosk_id' => $kiosk_id],
						'limit' => ROWS_PER_PAGE,
						'order' => ['MobileUnlocks.id DESC'],
						'contain' => ['Kiosks','Networks','MobileModels']
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
						'conditions' => ['MobileUnlocks.status' => UNLOCKED_CONFIRMATION_PASSED,
										 'MobileUnlocks.kiosk_id IN' => $managerKiosk,
										 ],
						'limit' => ROWS_PER_PAGE,
						'order' => ['MobileUnlocks.id DESC'],
						'contain' => ['Kiosks','Networks','MobileModels']
						   ];	
			   }else{
					$this->paginate = [
						'conditions' => ['MobileUnlocks.status' => UNLOCKED_CONFIRMATION_PASSED],
						'limit' => ROWS_PER_PAGE,
						'order' => ['MobileUnlocks.id DESC'],
						'contain' => ['Kiosks','Networks','MobileModels']
						   ];
			   }
			}else{
				$this->paginate = [
						'conditions' => ['MobileUnlocks.status' => UNLOCKED_CONFIRMATION_PASSED],
						'limit' => ROWS_PER_PAGE,
						'order' => ['MobileUnlocks.id DESC'],
						'contain' => ['Kiosks','Networks','MobileModels']
						   ];
			}
			
		}
		$mobileUnlocks = $this->paginate("MobileUnlocks");
		$hint = $this->ScreenHint->hint('mobile_unlocks','unlocked');
					if(!$hint){
						$hint = "";
					}
		$this->set(compact('hint','kiosks'));
		$this->set('mobileUnlocks', $mobileUnlocks);
		$this->render('index');
	}
	
	public function unlockingFailed() {
		//$cookieKioskId = $this->Cookie->read('kiosk_id');
		//$cookieKioskId = $this->request->session()->read('kiosk_id');
		//$this->set('cookieKioskId',$cookieKioskId);
		$kiosks_query = $this->Kiosks->find('list',
													[
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
		//unlock_status kiosk users:'Unlocking Failed: Confirmation passed to customer'
		$kiosk_id = $this->request->Session()->read('kiosk_id');
		if(!empty($kiosk_id) && $this->request->session()->read('Auth.User.group_id') == KIOSK_USERS){
			$this->paginate = [
						'conditions' => ['MobileUnlocks.status' => UNLOCKING_FAILED_CONFIRMATION_PASSED, 'kiosk_id' => $kiosk_id],
						'limit' => ROWS_PER_PAGE,
						'order' => ['MobileUnlocks.id DESC'],
						'contain' => ['Kiosks','Networks','MobileModels']
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
						'conditions' => ['MobileUnlocks.status' => UNLOCKING_FAILED_CONFIRMATION_PASSED,
										 'MobileUnlocks.kiosk_id IN' => $managerKiosk,
										 ],
						'limit' => ROWS_PER_PAGE,
						'order' => ['MobileUnlocks.id DESC'],
						'contain' => ['Kiosks','Networks','MobileModels']

						   ];		
				}else{
						$this->paginate = [
						'conditions' => ['MobileUnlocks.status' => UNLOCKING_FAILED_CONFIRMATION_PASSED],
						'limit' => ROWS_PER_PAGE,
						'order' => ['MobileUnlocks.id DESC'],
						'contain' => ['Kiosks','Networks','MobileModels']

						   ];
				}
		   }else{
				$this->paginate = [
						'conditions' => ['MobileUnlocks.status' => UNLOCKING_FAILED_CONFIRMATION_PASSED],
						'limit' => ROWS_PER_PAGE,
						'order' => ['MobileUnlocks.id DESC'],
						'contain' => ['Kiosks','Networks','MobileModels']

						   ];
		   }
		}
		$hint = $this->ScreenHint->hint('mobile_unlocks','unlocking_failed');
					if(!$hint){
						$hint = "";
					}
		$mobileUnlocks = $this->paginate("MobileUnlocks");					
		$this->set(compact('hint','kiosks'));
		$this->set('mobileUnlocks', $mobileUnlocks);
		$this->render('index');
	}
	
	public function receivedUnlocked() {
		//$cookieKioskId = $this->Cookie->read('kiosk_id');
		//$cookieKioskId = $this->request->session()->read('kiosk_id');
		//$this->set('cookieKioskId',$cookieKioskId);
		$kiosks_query = $this->Kiosks->find('list',
													[
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
		//unlock_status kiosk users:'Received unlocked from Unlocking Center'
		$kiosk_id = $this->request->Session()->read('kiosk_id');
		if(!empty($kiosk_id) && $this->request->session()->read('Auth.User.group_id') == KIOSK_USERS){
			$this->paginate = [
						'conditions' => ['MobileUnlocks.status' => RECEIVED_UNLOCKED_FROM_CENTER, 'kiosk_id' => $kiosk_id],
						'limit' => ROWS_PER_PAGE,
						'order' => ['MobileUnlocks.id DESC'],
						'contain' => ['Kiosks','Networks','MobileModels']
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
						'conditions' => ['MobileUnlocks.status' => RECEIVED_UNLOCKED_FROM_CENTER,
										 'MobileUnlocks.kiosk_id IN' => $managerKiosk,
										 ],
						'limit' => ROWS_PER_PAGE,
						'order' => ['MobileUnlocks.id DESC'],
						'contain' => ['Kiosks','Networks','MobileModels']
						   ];	
			   }else{
					$this->paginate = [
						'conditions' => ['MobileUnlocks.status' => RECEIVED_UNLOCKED_FROM_CENTER],
						'limit' => ROWS_PER_PAGE,
						'order' => ['MobileUnlocks.id DESC'],
						'contain' => ['Kiosks','Networks','MobileModels']
						   ];
			   }
			}else{
				$this->paginate = [
						'conditions' => ['MobileUnlocks.status' => RECEIVED_UNLOCKED_FROM_CENTER],
						'limit' => ROWS_PER_PAGE,
						'order' => ['MobileUnlocks.id DESC'],
						'contain' => ['Kiosks','Networks','MobileModels']
						   ];
			}
			
		}
		$hint = $this->ScreenHint->hint('mobile_unlocks','received_unlocked');
					if(!$hint){
						$hint = "";
					}
		$mobileUnlocks = $this->paginate("MobileUnlocks");										
		$this->set(compact('hint','kiosks'));
		$this->set('mobileUnlocks', $mobileUnlocks);
		$this->render('index');
	}
	
	public function receivedUnprocessed() {
		//$cookieKioskId = $this->Cookie->read('kiosk_id');
		//$cookieKioskId = $this->request->Session()->read('kiosk_id');
		//$this->set('cookieKioskId',$cookieKioskId);
		$kiosks_query = $this->Kiosks->find('list',
													[
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
		//unlock_status kiosk users:'Received unprocessed from Unlocking Center'
		$kiosk_id = $this->request->Session()->read('kiosk_id');
		if(!empty($kiosk_id) && $this->request->session()->read('Auth.User.group_id') == KIOSK_USERS){
			$this->paginate = [
						'conditions' => ['MobileUnlocks.status' => RECEIVED_UNPROCESSED_FROM_CENTER, 'kiosk_id' => $kiosk_id],
						'limit' => ROWS_PER_PAGE,
						'order' => ['MobileUnlocks.id DESC'],
						'contain' => ['Kiosks','Networks','MobileModels']
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
						'conditions' => ['MobileUnlocks.status' => RECEIVED_UNPROCESSED_FROM_CENTER,
										 'MobileUnlocks.kiosk_id IN' => $managerKiosk,
										 ],
						'limit' => ROWS_PER_PAGE,
						'order' => ['MobileUnlocks.id DESC'],
						'contain' => ['Kiosks','Networks','MobileModels']
						   ];	
			   }else{
					$this->paginate = [
						'conditions' => ['MobileUnlocks.status' => RECEIVED_UNPROCESSED_FROM_CENTER],
						'limit' => ROWS_PER_PAGE,
						'order' => ['MobileUnlocks.id DESC'],
						'contain' => ['Kiosks','Networks','MobileModels']
						   ];
			   }
			}else{
				$this->paginate = [
						'conditions' => ['MobileUnlocks.status' => RECEIVED_UNPROCESSED_FROM_CENTER],
						'limit' => ROWS_PER_PAGE,
						'order' => ['MobileUnlocks.id DESC'],
						'contain' => ['Kiosks','Networks','MobileModels']
						   ];
			}
			
			
		}
		$hint = $this->ScreenHint->hint('mobile_unlocks','received_unprocessed');
					if(!$hint){
						$hint = "";
					}
		$this->set(compact('hint','kiosks'));
		$this->set('mobileUnlocks', $this->paginate("MobileUnlocks"));
		$this->render('index');
	}
	
	public function refundRaised() {
		//$cookieKioskId = $this->Cookie->read('kiosk_id');
		//$cookieKioskId = $this->request->Session()->read('kiosk_id');
		//$this->set('cookieKioskId',$cookieKioskId);
		$kiosks_query = $this->Kiosks->find('list',
													[
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
		//unlock_status kiosk users:'Refund raised'
		$kiosk_id = $this->request->Session()->read('kiosk_id');
		if(!empty($kiosk_id) && $this->request->session()->read('Auth.User.group_id') == KIOSK_USERS){
			$this->paginate = [
						'conditions' => ['MobileUnlocks.status' => REFUND_RAISED, 'kiosk_id' => $kiosk_id],
						'limit' => ROWS_PER_PAGE,
						'order' => ['MobileUnlocks.id DESC'],
						'contain' => ['Kiosks','Networks','MobileModels']

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
						'conditions' => array('MobileUnlocks.status' => REFUND_RAISED,
											  'MobileUnlocks.kiosk_id IN' => $managerKiosk
											  ),
						'limit' => ROWS_PER_PAGE,
						'order' => ['MobileUnlocks.id DESC'],
						'contain' => ['Kiosks','Networks','MobileModels']
						   ];	
			   }else{
					$this->paginate = [
						'conditions' => array('MobileUnlocks.status' => REFUND_RAISED),
						'limit' => ROWS_PER_PAGE,
						'order' => ['MobileUnlocks.id DESC'],
						'contain' => ['Kiosks','Networks','MobileModels']
						   ];
			   }
			}else{
			   $this->paginate = [
						'conditions' => array('MobileUnlocks.status' => REFUND_RAISED),
						'limit' => ROWS_PER_PAGE,
						'order' => ['MobileUnlocks.id DESC'],
						'contain' => ['Kiosks','Networks','MobileModels']
						   ];
			}
			
			
		}
		$hint = $this->ScreenHint->hint('mobile_unlocks','refund_raised');
					if(!$hint){
						$hint = "";
					}
		$this->set(compact('hint','kiosks'));
		$this->set('mobileUnlocks', $this->paginate("MobileUnlocks"));
		$this->render('index');
	}
	
	public function delivered() {
		//$cookieKioskId = $this->Cookie->read('kiosk_id');
		//$cookieKioskId = $this->request->Session()->read('kiosk_id');
		//$this->set('cookieKioskId',$cookieKioskId);
		$kiosks_query = $this->Kiosks->find('list',
													[
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
		//unlock_status kiosk users:Delivered 
		$kiosk_id = $this->request->Session()->read('kiosk_id');
		if(!empty($kiosk_id) && $this->request->session()->read('Auth.User.group_id') == KIOSK_USERS){
			$this->paginate = [
						'conditions' => [
									'OR' =>[
											['MobileUnlocks.status' => DELIVERED_UNLOCKED_BY_CENTER],
											['MobileUnlocks.status' => DELIVERED_UNLOCKING_FAILED_AT_CENTER],
											['MobileUnlocks.status' => DELIVERED_UNLOCKED_BY_KIOSK],
											['MobileUnlocks.status' => DELIVERED_UNLOCKING_FAILED_AT_KIOSK]
										],
								'kiosk_id' => $kiosk_id
								],
						'limit' => ROWS_PER_PAGE,
						'order' => ['MobileUnlocks.id DESC'],
						'contain' => ['Kiosks','Networks','MobileModels']
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
					$this->paginate= [
						'conditions' => [
									'OR' => [
											['MobileUnlocks.status' => DELIVERED_UNLOCKED_BY_CENTER],
											['MobileUnlocks.status' => DELIVERED_UNLOCKING_FAILED_AT_CENTER],
											['MobileUnlocks.status' => DELIVERED_UNLOCKED_BY_KIOSK],
											['MobileUnlocks.status' => DELIVERED_UNLOCKING_FAILED_AT_KIOSK]
										],
									'MobileUnlocks.kiosk_id IN' => $managerKiosk,
								],
						'limit' => ROWS_PER_PAGE,
						'order' => ['MobileUnlocks.id DESC'],
						'contain' => ['Kiosks','Networks','MobileModels']
						 ];	
			   }else{
					$this->paginate= [
						'conditions' => [
									'OR' => [
											['MobileUnlocks.status' => DELIVERED_UNLOCKED_BY_CENTER],
											['MobileUnlocks.status' => DELIVERED_UNLOCKING_FAILED_AT_CENTER],
											['MobileUnlocks.status' => DELIVERED_UNLOCKED_BY_KIOSK],
											['MobileUnlocks.status' => DELIVERED_UNLOCKING_FAILED_AT_KIOSK]
										]
								],
						'limit' => ROWS_PER_PAGE,
						'order' => ['MobileUnlocks.id DESC'],
						'contain' => ['Kiosks','Networks','MobileModels']
						 ];
			   }
			}else{
				$this->paginate= [
						'conditions' => [
									'OR' => [
											['MobileUnlocks.status' => DELIVERED_UNLOCKED_BY_CENTER],
											['MobileUnlocks.status' => DELIVERED_UNLOCKING_FAILED_AT_CENTER],
											['MobileUnlocks.status' => DELIVERED_UNLOCKED_BY_KIOSK],
											['MobileUnlocks.status' => DELIVERED_UNLOCKING_FAILED_AT_KIOSK]
										]
								],
						'limit' => ROWS_PER_PAGE,
						'order' => ['MobileUnlocks.id DESC'],
						'contain' => ['Kiosks','Networks','MobileModels']
						 ];
			}
			
		}
		$hint = $this->ScreenHint->hint('mobile_unlocks','delivered');
					if(!$hint){
						$hint = "";
					}
		$this->set(compact('hint','kiosks'));
		$this->set('mobileUnlocks', $this->paginate("MobileUnlocks"));
		$this->render('index');
	}
	
	public function unlockRequestReceived() {
		//$cookieKioskId = $this->Cookie->read('kiosk_id');
		//$cookieKioskId = $this->request->Session()->read('kiosk_id');
		//$this->set('cookieKioskId',$cookieKioskId);
		$kiosks_query = $this->Kiosks->find('list',
													[
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
		// unlock_status technician:'Unlock request received: In process'
		$kiosk_id = $this->request->Session()->read('kiosk_id');
		if(!empty($kiosk_id) && $this->request->session()->read('Auth.User.group_id') == KIOSK_USERS){
			$this->paginate = [
						'conditions' => ['MobileUnlocks.status' => REQUEST_RECEIVED_IN_PROCESS, 'kiosk_id' => $kiosk_id],
						'limit' => ROWS_PER_PAGE,
						'order' => ['MobileUnlocks.id DESC'],
						'contain' => ['Kiosks','Networks','MobileModels']
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
						'conditions' => ['MobileUnlocks.status' => REQUEST_RECEIVED_IN_PROCESS,
										 'MobileUnlocks.kiosk_id IN' => $managerKiosk,
										 ],
						'limit' => ROWS_PER_PAGE,
						'order' => ['MobileUnlocks.id DESC'],
						'contain' => ['Kiosks','Networks','MobileModels']
						   ];
			   }else{
					$this->paginate = [
						'conditions' => ['MobileUnlocks.status' => REQUEST_RECEIVED_IN_PROCESS,
										 'MobileUnlocks.kiosk_id IN' => $managerKiosk,
										 ],
						'limit' => ROWS_PER_PAGE,
						'order' => ['MobileUnlocks.id DESC'],
						'contain' => ['Kiosks','Networks','MobileModels']
						   ];
			   }
			}else{
				$this->paginate = [
						'conditions' => ['MobileUnlocks.status' => REQUEST_RECEIVED_IN_PROCESS],
						'limit' => ROWS_PER_PAGE,
						'order' => ['MobileUnlocks.id DESC'],
						'contain' => ['Kiosks','Networks','MobileModels']
						   ];
			}
			
			
		}
		$hint = $this->ScreenHint->hint('mobile_unlocks','unlock_request_received');
					if(!$hint){
						$hint = "";
					}
		$this->set(compact('hint','kiosks'));
		$this->set('mobileUnlocks', $this->paginate("MobileUnlocks"));
		$this->render('index');
	}
	
	public function phoneReceived() {
		//$cookieKioskId = $this->Cookie->read('kiosk_id');
		//$cookieKioskId = $this->request->Session()->read('kiosk_id');
		//$this->set('cookieKioskId',$cookieKioskId);
		$kiosks_query = $this->Kiosks->find('list',
													[
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
		// unlock_status technician: 'Phone received by Unlocking Center'
		$kiosk_id = $this->request->Session()->read('kiosk_id');
		if(!empty($kiosk_id) && $this->request->session()->read('Auth.User.group_id') == KIOSK_USERS){
			$this->paginate = [
						'conditions' => ['MobileUnlocks.status' => PHONE_RECEIVED_BY_CENTER, 'kiosk_id' => $kiosk_id],
						'limit' => ROWS_PER_PAGE,
						'order' => ['MobileUnlocks.id DESC'],
						'contain' => ['Kiosks','Networks','MobileModels']
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
			$kiosk_ids = array();
			if($ext_site == 1){
				 $managerKiosk = $this->get_kiosk();
				 if(!empty($managerKiosk)){
					 $this->paginate = [
						'conditions' => array('MobileUnlocks.status' => PHONE_RECEIVED_BY_CENTER,
											  'MobileUnlocks.kiosk_id IN' => $managerKiosk,
											  ),
						'limit' => ROWS_PER_PAGE,
						'order' => ['MobileUnlocks.id DESC'],
						'contain' => ['Kiosks','Networks','MobileModels']
						   ];
				 }else{
					$this->paginate = [
						'conditions' => array('MobileUnlocks.status' => PHONE_RECEIVED_BY_CENTER),
						'limit' => ROWS_PER_PAGE,
						'order' => ['MobileUnlocks.id DESC'],
						'contain' => ['Kiosks','Networks','MobileModels']
						   ];
				 }
			}else{
				$this->paginate = [
						'conditions' => array('MobileUnlocks.status' => PHONE_RECEIVED_BY_CENTER),
						'limit' => ROWS_PER_PAGE,
						'order' => ['MobileUnlocks.id DESC'],
						'contain' => ['Kiosks','Networks','MobileModels']
						   ];	
			}
			
		}
		$hint = $this->ScreenHint->hint('mobile_unlocks','phone_received');
					if(!$hint){
						$hint = "";
					}
		$this->set(compact('hint','kiosks'));
		$this->set('mobileUnlocks', $this->paginate("MobileUnlocks"));
		$this->render('index');
	}
	
	public function unlockProcessed() {
		//$cookieKioskId = $this->Cookie->read('kiosk_id');
		//$cookieKioskId = $this->request->session()->read('kiosk_id');
		//$this->set('cookieKioskId',$cookieKioskId);
		$kiosks_query = $this->Kiosks->find('list',
													[
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
		// unlock_status technician: 'Unlock under Process'
		$kiosk_id = $this->request->Session()->read('kiosk_id');
		if(!empty($kiosk_id) && $this->request->session()->read('Auth.User.group_id') == KIOSK_USERS){
			$this->paginate = [
						'conditions' => ['MobileUnlocks.status' => UNLOCK_UNDER_PROCESS, 'kiosk_id' => $kiosk_id],
						'limit' => ROWS_PER_PAGE,
						'order' => ['MobileUnlocks.id DESC'],
						'contain' => ['Kiosks','Networks','MobileModels']
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
						'conditions' => ['MobileUnlocks.status' => UNLOCK_UNDER_PROCESS,
										 'MobileUnlocks.kiosk_id IN' => $managerKiosk,
										 ],
						'limit' => ROWS_PER_PAGE,
						'order' => ['MobileUnlocks.id DESC'],
						'contain' => ['Kiosks','Networks','MobileModels']
						   ];	
			   }else{
					$this->paginate = [
						'conditions' => ['MobileUnlocks.status' => UNLOCK_UNDER_PROCESS],
						'limit' => ROWS_PER_PAGE,
						'order' => ['MobileUnlocks.id DESC'],
						'contain' => ['Kiosks','Networks','MobileModels']
						   ];
			   }
			}else{
				$this->paginate = [
						'conditions' => ['MobileUnlocks.status' => UNLOCK_UNDER_PROCESS],
						'limit' => ROWS_PER_PAGE,
						'order' => ['MobileUnlocks.id DESC'],
						'contain' => ['Kiosks','Networks','MobileModels']
						   ];
			}
			
			
		}
		
		$this->set(compact('kiosks'));
		$this->set('mobileUnlocks', $this->paginate("MobileUnlocks"));
		$this->render('index');
	}
	
	public function waiting_dispatch() {
		//$cookieKioskId = $this->Cookie->read('kiosk_id');
		//$this->set('cookieKioskId',$cookieKioskId);
		$kiosks = $this->Kiosk->find('list', array('fields' => array('id', 'name'),
									 'conditions' => array('Kiosk.status' => 1),
									 'order' => 'Kiosk.name asc'
									));
		// unlock_status technician: 'Waiting for Dispatch: Unlocked'
		$this->MobileUnlock->recursive = 0;
		$kiosk_id = $this->Session->read('kiosk_id');
		if(!empty($kiosk_id) && AuthComponent::user('group_id') == KIOSK_USERS){
			$this->Paginator->settings = array(
						'conditions' => array('MobileUnlock.status' => WAITING_FOR_DISPATCH_UNLOCKED, 'kiosk_id' => $kiosk_id),
						'limit' => ROWS_PER_PAGE,
						'order' => 'MobileUnlock.id DESC'
						   );
		}else{
			$this->Paginator->settings = array(
						'conditions' => array('MobileUnlock.status' => WAITING_FOR_DISPATCH_UNLOCKED),
						'limit' => ROWS_PER_PAGE,
						'order' => 'MobileUnlock.id DESC'
						   );
		}
		$this->set(compact('kiosks'));
		$this->set('mobileUnlocks', $this->Paginator->paginate());
		$this->render('index');
	}
	
	public function unlock_processed() {
		//$cookieKioskId = $this->Cookie->read('kiosk_id');
		//$this->set('cookieKioskId',$cookieKioskId);
		$kiosks = $this->Kiosk->find('list', array('fields' => array('id', 'name'),
									 'conditions' => array('Kiosk.status' => 1),
									 'order' => 'Kiosk.name asc'
									));
		// unlock_status technician:'Unlock processed: Confirmation sent to Kiosk'
		$this->MobileUnlock->recursive = 0;
		$kiosk_id = $this->Session->read('kiosk_id');
		if(!empty($kiosk_id) && AuthComponent::user('group_id') == KIOSK_USERS){
			$this->Paginator->settings = array(
						'conditions' => array('MobileUnlock.status' => UNLOCK_PROCESSED_CONFIRMATION_SENT_2_KIOSK, 'kiosk_id' => $kiosk_id),
						'limit' => ROWS_PER_PAGE,
						'order' => 'MobileUnlock.id DESC'
						   );
		}else{
			$this->Paginator->settings = array(
						'conditions' => array('MobileUnlock.status' => UNLOCK_PROCESSED_CONFIRMATION_SENT_2_KIOSK),
						'limit' => ROWS_PER_PAGE,
						'order' => 'MobileUnlock.id DESC'
						   );
		}
		$hint = $this->ScreenHint->hint('mobile_unlocks','unlock_processed');
					if(!$hint){
						$hint = "";
					}
		$this->set(compact('hint','kiosks'));
		$this->set('mobileUnlocks', $this->Paginator->paginate());
		$this->render('index');
	}
	
	public function unlockFailed() {
		//$cookieKioskId = $this->Cookie->read('kiosk_id');
		//$cookieKioskId = $this->request->Session()->read('kiosk_id');
		//$this->set('cookieKioskId',$cookieKioskId);
		$kiosks_query = $this->Kiosks->find('list',
													[
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
		// unlock_status technician:'Unlock failed: Confirmation sent to Kiosk'
		$kiosk_id = $this->request->Session()->read('kiosk_id');
		if(!empty($kiosk_id) && $this->request->session()->read('Auth.User.group_id') == KIOSK_USERS){
			$this->paginate = [
						'conditions' => ['MobileUnlocks.status' => UNLOCK_FAILED_CONFIRMATION_SENT_2_KIOSK, 'kiosk_id' => $kiosk_id],
						'limit' => ROWS_PER_PAGE,
						'order' => ['MobileUnlocks.id DESC'],
						'contain' => ['Kiosks','Networks','MobileModels']
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
						'conditions' => ['MobileUnlocks.status' => UNLOCK_FAILED_CONFIRMATION_SENT_2_KIOSK,
										 'MobileUnlocks.kiosk_id IN' => $managerKiosk,
										 ],
						'limit' => ROWS_PER_PAGE,
						'order' => ['MobileUnlocks.id DESC'],
						'contain' => ['Kiosks','Networks','MobileModels']
						   ];	
			   }else{
				$this->paginate = [
						'conditions' => ['MobileUnlocks.status' => UNLOCK_FAILED_CONFIRMATION_SENT_2_KIOSK],
						'limit' => ROWS_PER_PAGE,
						'order' => ['MobileUnlocks.id DESC'],
						'contain' => ['Kiosks','Networks','MobileModels']
						   ];
			   }
			}else{
				$this->paginate = [
						'conditions' => ['MobileUnlocks.status' => UNLOCK_FAILED_CONFIRMATION_SENT_2_KIOSK],
						'limit' => ROWS_PER_PAGE,
						'order' => ['MobileUnlocks.id DESC'],
						'contain' => ['Kiosks','Networks','MobileModels']
						   ];
			}
			
		}
		$hint = $this->ScreenHint->hint('mobile_unlocks','unlock_failed');
					if(!$hint){
						$hint = "";
					}
		$this->set(compact('hint','kiosks'));
		$this->set('mobileUnlocks', $this->paginate("MobileUnlocks"));
		$this->render('index');
	}
	
	
	public function dispatchedToKiosk() {
		//$cookieKioskId = $this->Cookie->read('kiosk_id');
		//$cookieKioskId = $this->request->Session()->read('kiosk_id');
		//$this->set('cookieKioskId',$cookieKioskId);
		$kiosks_query = $this->Kiosks->find('list',
													[
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
		// unlock_status technician:'Dispatched'
		$kiosk_id = $this->request->Session()->read('kiosk_id');
		if(!empty($kiosk_id) && $this->request->session()->read('Auth.User.group_id') == KIOSK_USERS){
			$this->paginate = [
						'conditions' => [
									'OR' => [
											['MobileUnlocks.status' => DISPATCHED_2_KIOSK_UNLOCKED],
											['MobileUnlocks.status' => DISPATCHED_2_KIOSK_UNPROCESSED]
										],
									'kiosk_id' => $kiosk_id
								],
						'limit' => ROWS_PER_PAGE,
						'order' => ['MobileUnlocks.id DESC'],
						'contain' => ['Kiosks','Networks','MobileModels']
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
										'OR' => [
												['MobileUnlocks.status' => DISPATCHED_2_KIOSK_UNLOCKED],
												['MobileUnlocks.status' => DISPATCHED_2_KIOSK_UNPROCESSED]
											],
										'MobileUnlocks.kiosk_id IN' => $managerKiosk
									],
							'limit' => ROWS_PER_PAGE,
							'order' => ['MobileUnlocks.id DESC'],
							'contain' => ['Kiosks','Networks','MobileModels']
							];
				}else{
					$this->paginate = [
							'conditions' => [
										'OR' => [
												['MobileUnlocks.status' => DISPATCHED_2_KIOSK_UNLOCKED],
												['MobileUnlocks.status' => DISPATCHED_2_KIOSK_UNPROCESSED]
											]
									],
							'limit' => ROWS_PER_PAGE,
							'order' => ['MobileUnlocks.id DESC'],
							'contain' => ['Kiosks','Networks','MobileModels']
							];
				}
			}else{
				$this->paginate = [
						'conditions' => [
									'OR' => [
											['MobileUnlocks.status' => DISPATCHED_2_KIOSK_UNLOCKED],
											['MobileUnlocks.status' => DISPATCHED_2_KIOSK_UNPROCESSED]
										]
								],
						'limit' => ROWS_PER_PAGE,
						'order' => ['MobileUnlocks.id DESC'],
						'contain' => ['Kiosks','Networks','MobileModels']
						];
			}
			
			
		}
		$hint = $this->ScreenHint->hint('mobile_unlocks','dispatched_to_kiosk');
					if(!$hint){
						$hint = "";
					}
		$this->set(compact('hint','kiosks'));
		$this->set('mobileUnlocks', $this->paginate("MobileUnlocks"));
		$this->render('index');
	}
	
	public function edit($id = null) {
		$setting = $this->setting;
		$this->set(compact('setting'));
		$mobile_unlocks_res = $this->MobileUnlocks->get($id);
		$this->set(compact('mobile_unlocks_res'));
		$unlock_center_id = $this->request->Session()->read('kiosk_id');//die;
		//pr($this->MobileUnlock->find('all', array('recursive'= > -1, 'limit' => 10, 'order' => 'MobileUnlock.id desc')));
		//pr($this->MobileUnlockSale->find('all', array('recursive' => -1, 'limit' => 10, 'order' => 'MobileUnlockSale.mobile_unlock_id desc')));
		//pr($this->MobileUnlockLog->find('all', array('recursive' => -1, 'limit' => 10, 'order' => 'MobileUnlockLog.id desc')));
		$checkIfVirtuallyBooked = 0;
		//capturing the mobile model id and brand ids from mobileunlockprice table with status 1 ie active
		//$checkIfVirtuallyBooked = $this->MobileUnlockLog->find('count',array('conditions' => array('MobileUnlockLog.mobile_unlock_id' => $id, 'MobileUnlockLog.unlock_status' => VIRTUALLY_BOOKED), 'recursive' => -1, 'limit' => 1, 'order' => 'MobileUnlockLog.id desc'));
		////above is written to check if the phone was booked virtually
		
		//below we are fetching all the entries that have status: VIRTUALLY_BOOKED AND BOOKED and will check the id that has greater value. The id that has greater value means it was inserted later in logs table and will be considered ie booked or virtually booked (this has been done for rebook case)
		$checkIfVirtuallyBookedOrBooked_query = $this->MobileUnlockLogs->find('all',array('conditions' => array(									'MobileUnlockLogs.mobile_unlock_id' => $id,
									'OR' => array(
											array('MobileUnlockLogs.unlock_status' => VIRTUALLY_BOOKED),
											array('MobileUnlockLogs.unlock_status' => BOOKED)
										)
									),
									'order' => 'MobileUnlockLogs.id desc'
									)
							);
		$checkIfVirtuallyBookedOrBooked_query = $checkIfVirtuallyBookedOrBooked_query->hydrate(false);
		if(!empty($checkIfVirtuallyBookedOrBooked_query)){
			$checkIfVirtuallyBookedOrBooked = $checkIfVirtuallyBookedOrBooked_query->toArray();
		}else{
			$checkIfVirtuallyBookedOrBooked = array();
		}
		//pr($checkIfVirtuallyBookedOrBooked);die;
		if(count($checkIfVirtuallyBookedOrBooked)){
			if($checkIfVirtuallyBookedOrBooked['0']['unlock_status'] == VIRTUALLY_BOOKED){
				$checkIfVirtuallyBooked = 1;
			}
		}
		
		$activeCombinations_query = $this->MobileUnlockPrices->find('all',array('conditions' => array('MobileUnlockPrices.status'=>1,
												       'MobileUnlockPrices.unlocking_price > 0'),
							      'fields' => array('mobile_model_id','brand_id'),
							      'group' => 'MobileUnlockPrices.mobile_model_id'
							      ));
		$activeCombinations_query = $activeCombinations_query->hydrate(false);
		if(!empty($activeCombinations_query)){
			$activeCombinations = $activeCombinations_query->toArray();
		}else{
			$activeCombinations = array();
		}
		
		
		$activeBrands = array();
		$activeModels = array();
		foreach($activeCombinations as $key => $activeCombination){
			$activeBrands[$activeCombination['brand_id']] = $activeCombination['brand_id'];
			$activeModels[$activeCombination['mobile_model_id']] = $activeCombination['mobile_model_id'];
		}
		$kiosk_id = $this->request->Session()->read('kiosk_id');
		
		$dataPerId_query = $this->MobileUnlocks->find('all',array(
							'conditions' => array('MobileUnlocks.id'=>$id)
								)
						       );
		$dataPerId_query = $dataPerId_query->hydrate(false);
		if(!empty($dataPerId_query)){
			$dataPerId = $dataPerId_query->first();
		}else{
			$dataPerId = array();
		}
		
		//checking if the repair belongs to the kiosk for customers screen
		if($this->request->session()->read('Auth.User.group_id') == KIOSK_USERS){
			if($dataPerId['kiosk_id'] != $this->request->Session()->read('kiosk_id')){
				$this->Flash->error("You can only edit the unlock belonging to your kiosk!");
				return $this->redirect(array('controller' => 'retail_customers', 'action' => 'index'));
				die;
			}
		}
		
		$brandId = $dataPerId['brand_id'];
		$mobileModelId = $dataPerId['mobile_model_id'];
		$networkId = $dataPerId['network_id'];
		$booking_kiosk_id = $dataPerId['kiosk_id'];
		
			$unlockingDaysArr_query = "SELECT `unlocking_days`,`unlocking_minutes` from `mobile_unlock_prices` WHERE `brand_id`='$brandId' AND `mobile_model_id`='$mobileModelId' AND `network_id`='$networkId'";
		
			$conn = ConnectionManager::get('default');
			$stmt = $conn->execute($unlockingDaysArr_query); 
			$unlockingDaysArr = $stmt ->fetchAll('assoc');
		
		if(array_key_exists(0,$unlockingDaysArr)){
			$unlockingDays = $unlockingDaysArr['0']['unlocking_days'];
			$unlockMinutes = $unlockingDaysArr['0']['unlocking_minutes'];
			if(empty($unlockingDays) && empty($unlockMinutes)){
				$unlockingDays = 3;
				$unlockMinutes = 0;
			}else{
				if(empty($unlockingDays)){
					$unlockingDays = 0;
				}
				if(empty($unlockMinutes)){
					$unlockMinutes = 0;
				}
				
			}
		}else{
			$unlockingDays = 3;//kept in case there are no unlocking days
			$unlockMinutes = 0;
		}
		//echo $unlockMinutes;die;
		$countryOptions = Configure::read('uk_non_uk');
		$this->set(compact('countryOptions'));
		$kioskaddress_query = $this->Kiosks->find('all',array(
				'fields' => array('Kiosks.address_1','Kiosks.address_2', 'Kiosks.city','Kiosks.state','Kiosks.country','Kiosks.zip','Kiosks.contact' ),
				'conditions'=> array('Kiosks.id' => $booking_kiosk_id)
				)
			);
		$kioskaddress_query = $kioskaddress_query->hydrate(false);
		if(!empty($kioskaddress_query)){
			$kioskaddress = $kioskaddress_query->first();
		}else{
			$kioskaddress = array();
		}
		
		
		$kioskContact = $kioskaddress['contact'];
		$unlock_email_message = $this->setting['unlock_email_message'];
		$currency = $this->setting['currency_symbol'];
		$users_query = $this->Users->find('list',['keyField' => 'id',
										   'valueField' => 'username',
										   ]);
		$users_query = $users_query->hydrate(false);
		if(!empty($users_query)){
			$users = $users_query->toArray();
		}else{
			$users = array();
		}
		
		$kiosks_query = $this->MobileUnlocks->Kiosks->find('list',array(
									 'conditions' => array('Kiosks.status' => 1)
									));
		$kiosks_query = $kiosks_query->hydrate(false);
		if(!empty($kiosks_query)){
			$kiosks = $kiosks_query->toArray();
		}else{
			$kiosks = array();
		}
		$brands_query = $this->MobileUnlocks->Brands->find('list',
																	['keyField' => 'id',
																	 'valueField' => 'brand',
																	 'order'=>'brand asc',
																	 'conditions' => ['Brands.status' => 1,
																					'Brands.id IN' => $activeBrands]
																	 ]);
		$brands_query = $brands_query->hydrate(false);
		if(!empty($brands_query)){
			$brands = $brands_query->toArray();
		}else{
			$brands = array();
		}
		
		$mobileModels_query = $this->MobileUnlocks->MobileModels->find('list',
																				[
																					'keyField' => 'id',
																					'valueField' => 'model',
																					'order'=>'model asc',
																					'conditions' => ['MobileModels.status' => 1,
																										'MobileModels.id IN' => $activeModels,
																										'MobileModels.brand_id IN' => $brandId]
																						 ]
																	   );
		$mobileModels_query = $mobileModels_query->hydrate(false);
		if(!empty($mobileModels_query)){
			$mobileModels = $mobileModels_query->toArray();
		}else{
			$mobileModels = array();
		}
		
		$unlockLogs_query = $this->MobileUnlockLogs->find('all',array(
								'conditions'=>array('MobileUnlockLogs.mobile_unlock_id' => $id),
								'order'=>array('MobileUnlockLogs.id DESC')
								)
							   );
		$unlockLogs_query = $unlockLogs_query->hydrate(false);
		if(!empty($unlockLogs_query)){
			$unlockLogs = $unlockLogs_query->toArray();
		}else{
			$unlockLogs = array();
		}
				
				
		if (!$this->MobileUnlocks->exists($id)) {
			throw new NotFoundException(__('Invalid mobile unlock'));
		}		
		
		$currentStatus = $dataPerId['status'];
		$createdTime = strtotime($dataPerId['created']);
		$currentTime = strtotime(date("Y-m-d H:i:s"));
		$timeDifference = $currentTime - $createdTime;
		if($this->request->session()->read('Auth.User.group_id') == KIOSK_USERS){
			
		}
		
		
		if ($this->request->is(array('post', 'put')) || $this->request->Session()->read('unlock_payment_confirmation.unlock_payment_status') == $id) {
			//pr($this->request);die;
			if(array_key_exists("MobileUnlock",$this->request->data)){
				if(array_key_exists('brand_id',$this->request->data['MobileUnlock']) && array_key_exists('mobile_model_id',$this->request->data['MobileUnlock'])){
					$brdId = $this->request->data['MobileUnlock']['brand_id'];
					$modlId = $this->request->data['MobileUnlock']['mobile_model_id'];
					$result_query = $this->MobileModels->find('all',array('conditions' => array('MobileModels.id' => $modlId,
																					 'MobileModels.brand_id' => $brdId,
																					 )
															   ));
					$result_query = $result_query->hydrate(false);
					if(!empty($result_query)){
						$result = $result_query->first();
					}else{
						$result = array();
					}
					if(empty($result)){
						$this->Flash->error(__('The mobile unlock could not be saved. Please Choose Right Combination For Brand And Model.'));
						return $this->redirect(array('action' => 'edit',$id));
					}			
				}	
			}
			
			//**************Case update payment*********************
			if(array_key_exists('cancel',$this->request->data)){
				$this->Flash->success('You have cancelled transaction!');
				return $this->redirect(array('action' => 'edit', $id));
				die;
			}
			if(array_key_exists('UpdatePayment',$this->request->data)){
				$ttlAmount = 0;
				$updatedPaymentData = $this->request->data['UpdatePayment'];
				//card or cash options
				$updatedAmountData = $this->request->data['updated_amount'];
				//card or carsh amounts
				$sale_amount = $this->request->data['sale_amount'];
				//total updated amount
				$addedAmount = 0;
				if(array_key_exists('added_amount',$this->request->data)){
					$addedAmount = $this->request->data['added_amount'];
				}
				//if new row added for amount
				
				foreach($updatedPaymentData as $paymentId => $paymentMode){
					$ttlAmount += $updatedAmountData[$paymentId];
				}
				$ttl_amount = $addedAmount + $ttlAmount;
				
				if($ttl_amount != $sale_amount){
					//validation check
					$this->Flash->error('Payment could not be updated!');
					return $this->redirect(array('action' => 'edit',$id));
					die;
				}
				
				$saveAdminPayment = 0;
				//****saving newly added payment amount
				if(array_key_exists('added_amount',$this->request->data) && is_numeric($this->request->data['added_amount'])){
					$paymntData_query = $this->UnlockPayments->find('all',array(
																			'conditions' => array('UnlockPayments.mobile_unlock_id'=>$id)
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
					//$paymntData['payment_method'] = $this->request->data['MobileUnlock']['new_change_mode'];
                   // pr($this->request->data);
                    $paymntData['payment_method'] = $this->request->data['new_change_mode'];
					$paymntData['amount'] = $this->request->data['added_amount'];
					$UnlockPaymentsEntity = $this->UnlockPayments->newEntity($paymntData,['validate' => false]);
					$UnlockPaymentsEntity = $this->UnlockPayments->patchEntity($UnlockPaymentsEntity,$paymntData,['validate' => false]);
					if($this->UnlockPayments->save($UnlockPaymentsEntity)){
						$saveAdminPayment++;
					}
				}
				// saving new added payment till here*****
				
				$sale_amount = $this->request->data['sale_amount'];
				
				foreach($updatedPaymentData as $paymentId => $paymentMode){
					$UnlockPaymentEntity = $this->UnlockPayments->get($paymentId);
					$paymentDetailData = array(
												'payment_method' => $paymentMode,
												'amount' => $updatedAmountData[$paymentId]
												);
					$UnlockPaymentEntity = $this->UnlockPayments->patchEntity($UnlockPaymentEntity,$paymentDetailData,['validate' => false]);
					if($this->UnlockPayments->save($UnlockPaymentEntity)){
						$saveAdminPayment++;
					}
				}
				
					
				if($saveAdminPayment > 0){
					$MobileUnlocksEntity = $this->MobileUnlocks->get($id);
					$data_to_save_3 = array('estimated_cost' => $sale_amount);
					$MobileUnlocksEntity = $this->MobileUnlocks->patchEntity($MobileUnlocksEntity,$data_to_save_3,['validate' => false]);
					$this->MobileUnlocks->save($MobileUnlocksEntity);
					//this needs to be checked
					
					$this->MobileUnlockSales->updateAll(array('amount' => $sale_amount),
															array('MobileUnlockSales.mobile_unlock_id' => $id )
														);
					
					//saving logs
					$mobileUnlockLogsData = array(
													'kiosk_id' => $dataPerId['kiosk_id'],
													'user_id' => $this->request->Session()->read('Auth.User.id'),
													'mobile_unlock_id' => $id,
													'unlock_status' => $dataPerId['status']
												);
					$MobileUnlockLogsEntity = $this->MobileUnlockLogs->newEntity($mobileUnlockLogsData,['validate' => false]);
					$MobileUnlockLogsEntity = $this->MobileUnlockLogs->patchEntity($MobileUnlockLogsEntity,$mobileUnlockLogsData,['validate' => false]);
					$this->MobileUnlockLogs->save($MobileUnlockLogsEntity);
					$this->Flash->success('Payment has been successfully updated!');
					//return $this->redirect(array('action' => 'edit',$id));
					return $this->redirect(array('action' => 'all'));
				}else{
					$this->Flash->error('Payment could not be updated!');
					return $this->redirect(array('action' => 'edit',$id));
				}
			}
			//**************Case update payment*********************
		
			//deleting session id payment_confirmation as we no longer need it after entering this loop
			$this->request->Session()->delete('unlock_payment_confirmation');
			if($this->request->session()->read('Auth.User.group_id') == KIOSK_USERS && count($this->request->data) && $dataPerId['internal_unlock'] != 1){
				$unlock_status = $this->request->data['MobileUnlock']['status'];
				if($unlock_status == DELIVERED_UNLOCKED_BY_CENTER ||
				   $unlock_status == DELIVERED_UNLOCKED_BY_KIOSK
				   //|| $unlock_status == UNLOCKED_CONFIRMATION_PASSED
				   ){
					$this->request->Session()->write('unlock_data_session',$this->request->data);
					return $this->redirect(array('action' => 'unlock_payment'));
					die;
				}
			}elseif(is_array($this->request->Session()->read('unlock_data_session'))){
				$this->request->data = $this->request->Session()->read('unlock_data_session');
				//for request coming through payment page
			}
			//pr($this->Session->read('unlock_data_session'));
			//die;
			
			//deleting the session, as it is no longer required
			$this->request->Session()->delete('unlock_data_session');
			
			//$imei = $this->request->data['MobileUnlock']['imei'];
			$unlock_status = $this->request->data['MobileUnlock']['status'];
			if(array_key_exists('imei',$this->request->data['MobileUnlock'])){
				$imei = $this->request->data['MobileUnlock']['imei'].$this->request->data['MobileUnlock']['imei1'];
			}
			$this->request->data['MobileUnlock']['imei']= $imei;
			//checking if the mobile belongs to kiosks or customer
			
			$mobilePurchaseData_query = $this->MobilePurchases->find('all',array('conditions'=>array('MobilePurchases.imei'=>$imei,'MobilePurchases.status'=>3)));
			$mobilePurchaseData_query = $mobilePurchaseData_query->hydrate(false);
			if(!empty($mobilePurchaseData_query)){
				$mobilePurchaseData = $mobilePurchaseData_query->first();
			}else{
				$mobilePurchaseData = array();
			}
			
			if($unlock_status == DELIVERED_UNLOCKED_BY_CENTER ||
			   $unlock_status == DELIVERED_UNLOCKING_FAILED_AT_CENTER ||
			   $unlock_status == DELIVERED_UNLOCKED_BY_KIOSK ||
			   $unlock_status == DELIVERED_UNLOCKING_FAILED_AT_KIOSK ||
			   $unlock_status == UNLOCKED_CONFIRMATION_PASSED ||
			   $unlock_status == UNLOCKING_FAILED_CONFIRMATION_PASSED){
				$this->request->data['MobileUnlock']['delivered_at'] = date('Y-m-d h:i:s A');
			}else{
				$this->request->data['MobileUnlock']['delivered_at'] = date('0-0-0 0:0:0 A');
			}		
			
			if(!(int)$kiosk_id){
				$kiosk_id = '10000';//for warehouse
			}
			
			//Code block by Rajiv to cehck if sale is generated for unlock ID
			$ide = $this->request['data']['MobileUnlock']['id'];
			$mobileUnlockSalesIdData_query = $this->MobileUnlockSales->find('all',array(
																			'conditions' => array('MobileUnlockSales.mobile_unlock_id' => $ide)
																			)
																		);
			$mobileUnlockSalesIdData_query = $mobileUnlockSalesIdData_query->hydrate(false);
			if(!empty($mobileUnlockSalesIdData_query)){
				$mobileUnlockSalesIdData = $mobileUnlockSalesIdData_query->toArray();
			}else{
				$mobileUnlockSalesIdData = array();
			}
			$saleAmountUpdated = false;
			$mobileUnlockData = $this->request['data']['MobileUnlock'];
			if(array_key_exists(0,$mobileUnlockSalesIdData)){
				if(
				   array_key_exists('estimated_cost_hidden', $this->request['data']['MobileUnlock']) &&
				   $this->request['data']['MobileUnlock']['estimated_cost_hidden'] != $this->request['data']['MobileUnlock']['estimated_cost']){
					$saleAmountUpdated = true;
				}
			}
			if($saleAmountUpdated){
				//set estimated_cost to original cost by using estimated_cost_hidden
				//we will save updated estimated_cost only after update of sale price and payment methods
				$mobileUnlockData['estimated_cost'] = $this->request['data']['MobileUnlock']['estimated_cost_hidden'];
				$saleAmount = $this->request['data']['MobileUnlock']['estimated_cost'];
				
			}
			//Code block by Rajiv to check if sale is generated for unlock ID
				//echo "hi";die;
                //pr($mobileUnlockData);die;
			$MobileUnlocksEntity = $this->MobileUnlocks->get($mobileUnlockData['id']);
			$MobileUnlocksEntity = $this->MobileUnlocks->patchEntity($MobileUnlocksEntity,$mobileUnlockData,['validate' => false]);
            //pr($MobileUnlocksEntity);die;
			if ($this->MobileUnlocks->save($MobileUnlocksEntity)) {//saving the data in unlock table
					
				$mobileUnlockLogsData = array(
						'kiosk_id' => $booking_kiosk_id,
						'user_id' => $this->request->Session()->read('Auth.User.id'),
						'mobile_unlock_id' => $MobileUnlocksEntity->id,
						'unlock_center_id' => $unlock_center_id,
						'unlock_status' => $unlock_status
					);
								
				$MobileUnlockLogEntity = $this->MobileUnlockLogs->newEntity($mobileUnlockLogsData,['validate' => false]);
				$MobileUnlockLogEntity = $this->MobileUnlockLogs->patchEntity($MobileUnlockLogEntity,$mobileUnlockLogsData,['validate' => false]);
				$this->MobileUnlockLogs->save($MobileUnlockLogEntity);				
				//****** Code added by Rajiv: Check if payment updated******
				/*
				 *Step 1: Save Log and trasfer log conditions
				 *Step 2: Show payment modes here
				 *Please check this payment should be updated only by admin and manager
				*/
				if($saleAmountUpdated && $dataPerId['internal_unlock'] != 1 &&
				   ($dataPerId['status'] == VIRTUALLY_BOOKED ||
					$dataPerId['status'] == DELIVERED_UNLOCKED_BY_CENTER ||
					$dataPerId['status'] == DELIVERED_UNLOCKED_BY_KIOSK ||
					$dataPerId['status'] == UNLOCKED_CONFIRMATION_PASSED ||
					$dataPerId['status'] == REQUEST_RECEIVED_IN_PROCESS ||
					$dataPerId['status'] == UNLOCK_PROCESSED_CONFIRMATION_SENT_2_KIOSK
					)){
					$kiosks_query = $this->Kiosks->find('list', array('keyField' => 'id','valueField' =>'name',
									 'conditions' => array('Kiosks.status' => 1),
									 'order' => 'Kiosks.name asc'
									));
					$kiosks_query = $kiosks_query->hydrate(false);
					if(!empty($kiosks_query)){
						$kiosks = $kiosks_query->toArray();
					}else{
						$kiosks = array();
					}
					
					$users_query = $this->Users->find('list',array('keyField' => 'id','valueField' => 'username'));
					$users_query = $users_query->hydrate(false);
					if(!empty($users_query)){
						$users = $users_query->toArray();
					}else{
						$users = array();
					}
					$paymentData_query = $this->UnlockPayments->find('all',array(
							'conditions' => array('UnlockPayments.mobile_unlock_id' => $ide)
								)
							  );
					$paymentData_query = $paymentData_query->hydrate(false);
					if(!empty($paymentData_query)){
						$paymentData = $paymentData_query->toArray();
					}else{
						$paymentData = array();
					}
					$unlockID = $ide;
					$this->set(compact('saleAmount', 'paymentData','kiosks','users','unlockID'));
					$this->render('update_unlockpayment');
					goto fakeblock;
				}
				//****** Code added by Rajiv: Check if payment updated******
				
				//changing the status of mobile to available from sent to unlock in case of kiosk sending mobile for unlock
				if(!empty($mobilePurchaseData) &&
					($unlock_status == DELIVERED_UNLOCKED_BY_CENTER ||
					$unlock_status == DELIVERED_UNLOCKING_FAILED_AT_CENTER ||
					$unlock_status == DELIVERED_UNLOCKED_BY_KIOSK ||
					$unlock_status == DELIVERED_UNLOCKING_FAILED_AT_KIOSK ||
					$unlock_status == UNLOCKED_CONFIRMATION_PASSED ||
					$unlock_status == UNLOCKING_FAILED_CONFIRMATION_PASSED)
					
				){
					//Transfer Log is only for internal Unlock
					$purchaseId = $mobilePurchaseData['id'];
					$mobileTransferLogData = array(
						'mobile_purchase_reference' => $mobilePurchaseData['mobile_purchase_reference'],
						'mobile_purchase_id' => $mobilePurchaseData['id'],
						'kiosk_id' => $booking_kiosk_id,
						'network_id' => $mobilePurchaseData['network_id'],
						'grade' => $mobilePurchaseData['grade'],
						'type' => $mobilePurchaseData['type'],
						'receiving_status' => 0,
						'imei' => $mobilePurchaseData['imei'],
						'user_id' => $this->request->Session()->read('Auth.User.id'),
						'status' => 0//available
					);
			
					$MobileTransferLogEntity = $this->MobileTransferLogs->newEntity($mobileTransferLogData,['validate' => false]);
					$MobileTransferLogEntity = $this->MobileTransferLogs->patchEntity($MobileTransferLogEntity,$mobileTransferLogData,['validate' => false]);
					$this->MobileTransferLogs->save($MobileTransferLogEntity);
					$MobilePurchasesEntity = $this->MobilePurchases->get($purchaseId);
					$data_to_save_1 = array('status'=>0);
					$MobilePurchasesEntity = $this->MobilePurchases->patchEntity($MobilePurchasesEntity,$data_to_save_1,['validate' => false]);
					$this->MobilePurchases->save($MobilePurchasesEntity);
					if($unlock_status == DELIVERED_UNLOCKED_BY_CENTER ||
					   $unlock_status == DELIVERED_UNLOCKED_BY_KIOSK ||
					   $unlock_status == UNLOCKED_CONFIRMATION_PASSED
					   ){
						$data_to_save_2 = array('type' => 0);
						$MobilePurchasesEntity_2 = $this->MobilePurchases->patchEntity($MobilePurchasesEntity,$data_to_save_2,['validate' => false]);
						$this->MobilePurchases->save($MobilePurchasesEntity_2);
					}
				}
					
				$mobileUnlockLogsData = array(
					'kiosk_id' => $booking_kiosk_id,
					'user_id' => $this->request->Session()->read('Auth.User.id'),
					'mobile_unlock_id' => $MobileUnlocksEntity->id,					
					'unlock_status' => $unlock_status
				);
				
				if(	$unlock_status == UNLOCK_PROCESSED_CONFIRMATION_SENT_2_KIOSK ||
					$unlock_status == UNLOCK_FAILED_CONFIRMATION_SENT_2_KIOSK ||
					$unlock_status == DISPATCHED_2_KIOSK_UNLOCKED ||
					$unlock_status == DISPATCHED_2_KIOSK_UNPROCESSED
				){
					$unlock_center_id = $this->request->Session()->read('kiosk_id');
				}
					
				$problemVar = $this->request['data']['MobileUnlock'];
			
				//saving the data in sales table from here onwards
				if($problemVar['status'] == DELIVERED_UNLOCKING_FAILED_AT_CENTER ||
				   $problemVar['status'] == DELIVERED_UNLOCKING_FAILED_AT_KIOSK ||
				   $dataPerId['internal_unlock'] == 1
				){
					$amount = 0;
					$refundAmount = '';
				}elseif($problemVar['status'] == UNLOCKING_FAILED_CONFIRMATION_PASSED){
					//UNLOCK_FAILED_CONFIRMATION_SENT_2_KIOSK
					//case:  || UNLOCKED_CONFIRMATION_PASSED
					$amount = $this->request['data']['MobileUnlock']['estimated_cost'];
					$refundAmount = -1 * $amount; //Ask Inder
				}else{
					//successful case of unlock
					$amount = $this->request['data']['MobileUnlock']['estimated_cost'];
					$refundAmount = ''; //Ask Inder
				}
				
				$dateUnlocked = date('Y-m-d G:i:s A');
				
				//----- /if for saving sales data----------------
				$ide = $MobileUnlocksEntity->id;
				
				//echo "hi";die;
				if($problemVar['status'] == UNLOCKED_CONFIRMATION_PASSED){
					//echo "hi";die;
					//Refund Case: Amount will be refunded to customer if unlocking fails in this case.
					//customer will physically appear in the kiosk to receive amount.
					//pr($mobileUnlockSalesIdData);
					if(array_key_exists(0,$mobileUnlockSalesIdData)){
						$mobileUnlockSalesId = $mobileUnlockSalesIdData[0]['id'];						
						$mobileUnlockSalesData = array(
														'id' => $mobileUnlockSalesId,
														'kiosk_id' => $dataPerId['kiosk_id'],
														'retail_customer_id' => $dataPerId['retail_customer_id'],
														'mobile_unlock_id' => $MobileUnlocksEntity->id,
														//'sold_by' => $this->Session->read('Auth.User.id'),
														'sold_on' => $dateUnlocked,
														'refund_by' => '',
														'amount' => $amount,
														'refund_amount' => $refundAmount,
														'refund_status' => 0,
														'refund_on' => '',
														'refund_remarks' => ''
													);
						//pr($mobileUnlockSalesData);
						$MobileUnlockSaleEntity = $this->MobileUnlockSales->get($mobileUnlockSalesId);
						$MobileUnlockSaleEntity = $this->MobileUnlockSales->patchEntity($MobileUnlockSaleEntity,$mobileUnlockSalesData,['validate' => false]);
						if($this->MobileUnlockSales->save($MobileUnlockSaleEntity,array('validate'=>false))){
							$unlckSaleId = $MobileUnlockSaleEntity->id;
							//$this->UnlockPayment->updateAll(array('mobile_unlock_sale_id' => $unlckSaleId),array('UnlockPayment.mobile_unlock_id' => $id));
							$query = "UPDATE `unlock_payments` SET `mobile_unlock_sale_id` = '$unlckSaleId' WHERE `mobile_unlock_id` = $id";  //$sale_ide
							$conn = ConnectionManager::get('default');
							$stmt = $conn->execute($query); 

							//-------------------------adding new code Aug 25----------------
							$paymentDetails_query = $this->UnlockPayments->find('all', array(
												'conditions' => array('UnlockPayments.mobile_unlock_sale_id' => $unlckSaleId), //$id
															));
							$paymentDetails_query = $paymentDetails_query->hydrate(false);
							if(!empty($paymentDetails_query)){
								$paymentDetails = $paymentDetails_query->toArray();
							}else{
								$paymentDetails = array();
							}
							if(empty($paymentDetails) && $dataPerId['internal_unlock'] != 1){
								//delete sale and redirect to index page
								$MobileUnlockSales_del_entity = $this->MobileUnlockSales->get($id);
								$this->MobileUnlockSales->delete($MobileUnlockSales_del_entity);
								$alterQuery = "ALTER TABLE `mobile_unlock_sales` AUTO_INCREMENT = $id";
								$conn = ConnectionManager::get('default');
								$stmt = $conn->execute($alterQuery); 
								$this->Flash->error("Failed to fire query: $query. <br/>For this reason generated sale delelted for Mobile Repair Sale ID: {$id} and receipt counter is again set to $id for maintaining sequences<br/>Please take screenshot of this bug and report to admin");
								return $this->redirect(array('action' => 'index'));
							}
							//---------------------------------------------------------------
						}
					}						
				}elseif($problemVar['status'] == DELIVERED_UNLOCKED_BY_CENTER ||
					$problemVar['status'] == DELIVERED_UNLOCKING_FAILED_AT_CENTER ||
					$problemVar['status'] == DELIVERED_UNLOCKED_BY_KIOSK ||
					$problemVar['status'] == DELIVERED_UNLOCKING_FAILED_AT_KIOSK){
					//echo "1667";die;
					//case: physical booking, here we need to check if sale id already exists in database, then we will just update the data, 'sold_by' needs to be commented in case of admin
					$countSale_query = $this->MobileUnlockSales->find('all', array('conditions' => array('MobileUnlockSales.mobile_unlock_id' => $MobileUnlocksEntity->id)));
					//pr($countSale_query);
					$countSale = $countSale_query->count();
					//pr($countSale);die;
					$mobileUnlockSalesData = array(
													'kiosk_id' => $dataPerId['kiosk_id'],
													'retail_customer_id' => $dataPerId['retail_customer_id'],
													'mobile_unlock_id' => $MobileUnlocksEntity->id,
													//'sold_by' => $this->Session->read('Auth.User.id'),
													'sold_on' => $dateUnlocked,
													'refund_by' => '',
													'amount' => $amount,
													'refund_amount' => '',
													'refund_status' => 0,
													'refund_on' => '',
													'refund_remarks' => ''
													);
					if($this->request->session()->read('Auth.User.group_id') == KIOSK_USERS){
						$mobileUnlockSalesData['sold_by'] = $this->request->Session()->read('Auth.User.id');
						$mobileUnlockSalesData['sold_on'] = $dateUnlocked;
					}
					//below code is to check if sale already exists, if yes, it updates pushes key id to the sales array to update the existing row rather than inserting a new row
					if($countSale > 0){
						$ide = $MobileUnlocksEntity->id;
						$mobileUnlockSalesIdData_query = $this->MobileUnlockSales->find('all',array(
								'conditions' => array(
										'MobileUnlockSales.mobile_unlock_id' => $ide
											)
										)
									);
						$mobileUnlockSalesIdData_query = $mobileUnlockSalesIdData_query->hydrate(false);
						if(!empty($mobileUnlockSalesIdData_query)){
							$mobileUnlockSalesId = $mobileUnlockSalesIdData[0]['id'];
						}else{
							$mobileUnlockSalesData['id'] = $mobileUnlockSalesId;
						}
					$MobileUnlockSaleEntity = $this->MobileUnlockSales->get($mobileUnlockSalesId);
					$MobileUnlockSaleEntity = $this->MobileUnlockSales->patchEntity($MobileUnlockSaleEntity,$mobileUnlockSalesData,['validate' => false]);
					}else{
						$MobileUnlockSaleEntity = $this->MobileUnlockSales->newEntity($mobileUnlockSalesData,['validate' => false]);
						$MobileUnlockSaleEntity = $this->MobileUnlockSales->patchEntity($MobileUnlockSaleEntity,$mobileUnlockSalesData,['validate' => false]);
					}
					//$this->MobileUnlockSale->create();
					/*$this->MobileUnlockSale->set($mobileUnlockSalesData);
					if (!$this->MobileUnlockSale->validates()) {
						pr($errors = $this->MobileUnlockSale->validationErrors);
					}*/
					
					if($this->MobileUnlockSales->save($MobileUnlockSaleEntity,array('validate' => false))){
						$unlckSaleId = $MobileUnlockSaleEntity->id;
						$query_to_run = "UPDATE  `unlock_payments`  SET  `mobile_unlock_sale_id` = $unlckSaleId WHERE `unlock_payments`.`mobile_unlock_id` = $id";
						$conn = ConnectionManager::get('default');
						$stmt = $conn->execute($query_to_run); 
						//$this->UnlockPayment->updateAll(array('mobile_unlock_sale_id' => "'$unlckSaleId'"),array('UnlockPayment.mobile_unlock_id' => $id));
					}
				}elseif($problemVar['status'] == UNLOCKING_FAILED_CONFIRMATION_PASSED){
					//echo "1708";die;
					$ide = $MobileUnlocksEntity->id;
					$mobileUnlockSalesIdData_query = $this->MobileUnlockSales->find('all',array(
															'conditions' => array('MobileUnlockSales.mobile_unlock_id' => $ide),
															'fields' => array('MobileUnlockSales.id')
															)
														);
					$mobileUnlockSalesIdData_query = $mobileUnlockSalesIdData_query->hydrate(false);
					if(!empty($mobileUnlockSalesIdData_query)){
						$mobileUnlockSalesIdData = $mobileUnlockSalesIdData_query->first();
					}else{
						$mobileUnlockSalesIdData = array();
					}
			
					$refundOn = date('Y-m-d H:i:s A');
					if(!empty($mobileUnlockSalesIdData)){
						//Note: Updating delivery date in sales table, I think it should be updated
						// in mobile_unlocks table rather than sales table
						//Note : refund should be added in all cases if success/failur in updating record in this block
						//$refundAmount = -$this->request['data']['MobileUnlock']['estimated_cost'];
						$mobileUnlockSalesId = $mobileUnlockSalesIdData['id'];
						$mobileUnlockSalesData = array(
										'id' => $mobileUnlockSalesId,
										'kiosk_id' => $dataPerId['kiosk_id'],
										'retail_customer_id' => $dataPerId['retail_customer_id'],
										'mobile_unlock_id' => $MobileUnlocksEntity->id,
										//'sold_by' => $this->Session->read('Auth.User.id'),
										//'sold_on' => $dateUnlocked,
										'refund_by' => $this->request->Session()->read('Auth.User.id'),
										'amount' => $amount,
										'refund_amount' => $refundAmount,
										'refund_status' => 0,
										'refund_on' => $refundOn,
										'refund_remarks' => 'Unprocessed Unlock',
										'status' => 1
										   );
						if($this->request->Session()->read('Auth.User.group_id') == KIOSK_USERS){
							$mobileUnlockSalesData['sold_by'] = $this->request->Session()->read('Auth.User.id');
						}
						$MobileUnlockSale_data_entity = $this->MobileUnlockSales->get($mobileUnlockSalesId);
						$MobileUnlockSale_data_entity = $this->MobileUnlockSales->patchEntity($MobileUnlockSale_data_entity,$mobileUnlockSalesData,['validate' => false]);
						$this->MobileUnlockSales->save($MobileUnlockSale_data_entity);
					}
				
					//case refund is processed(could not be unlocked by center): when admin edits the data, it may result in duplicacy of id with same mobile unlock id for below code, so we need to check if a row exists with refund_status = 1 and mobile_unlock_id as this id.
					$countRefund_query = $this->MobileUnlockSales->find('all', array('conditions' =>
											array(
												  'MobileUnlockSales.mobile_unlock_id' => $MobileUnlocksEntity->id,
												  'MobileUnlockSales.refund_status' => 1)));
					$countRefund = $countRefund_query->count();
				
					$mobileUnlockSalesRefundData = array(
										'kiosk_id' => $dataPerId['kiosk_id'],
										'retail_customer_id' => $dataPerId['retail_customer_id'],
										'mobile_unlock_id' => $MobileUnlocksEntity->id,
										'sold_by' => $this->request->Session()->read('Auth.User.id'),
										//should be original seller needs to be updated
										'sold_on' => $dateUnlocked,
										'refund_by' => $this->request->Session()->read('Auth.User.id'),
										'amount' => 0,
										'refund_amount' => $refundAmount,
										'refund_status' => 1,
										//'refund_on' => $refundOn,
										'refund_remarks' => 'Unprocessed Unlock'
									 );
					//below code is to check if a refunded row already exists with refund_status = 1 and mobile_unlock_id as this id
					if($countRefund > 0){
						$ide = $MobileUnlocksEntity->id;
						$mobileUnlockRefundIdData_query = $this->MobileUnlockSales->find('all',array(
								'conditions' => array(
										'MobileUnlockSales.mobile_unlock_id' => $ide,
										'MobileUnlockSales.refund_status' => 1
											)
										)
									);
						$mobileUnlockRefundIdData_query = $mobileUnlockRefundIdData_query->hydrate(false);
						if(!empty($mobileUnlockRefundIdData_query)){
							$mobileUnlockRefundIdData = $mobileUnlockRefundIdData_query->toArray();
						}else{
							$mobileUnlockRefundIdData = array();
						}
						$mobileUnlockRefundId = $mobileUnlockRefundIdData[0]['id'];
						$mobileUnlockSalesRefundData['id'] = $mobileUnlockRefundId;
						$MobileUnlockSales_Entity = $this->MobileUnlockSales->get($mobileUnlockSalesRefundData['id']);
					}else{
						$MobileUnlockSales_Entity = $this->MobileUnlockSales->newEntity($mobileUnlockSalesRefundData,['validate' => false]);//for inserting a new entry
					}
				
					if($this->request->session()->read('Auth.User.group_id') == KIOSK_USERS){
						//sending only in case of kiosk, not in updation of same data by admin for editing purpose
						$mobileUnlockSalesRefundData['refund_by'] = $this->request->Session()->read('Auth.User.id');
						$mobileUnlockSalesRefundData['refund_on'] = $refundOn;
					}
			//pr($mobileUnlockSalesRefundData);die;
					$MobileUnlockSales_Entity = $this->MobileUnlockSales->patchEntity($MobileUnlockSales_Entity,$mobileUnlockSalesRefundData,['validate' => false]);
					if($this->MobileUnlockSales->save($MobileUnlockSales_Entity)){
						$data_to_save_new = array('status_refund' => 1);
						$MobileUnlocksPatchEntity = $this->MobileUnlocks->get($MobileUnlocksEntity->id);
						$MobileUnlocksPatchEntity = $this->MobileUnlocks->patchEntity($MobileUnlocksPatchEntity,$data_to_save_new,['validte' => false]);
						$this->MobileUnlocks->save($MobileUnlocksPatchEntity);
					}elseif(!$this->MobileUnlockSales->save($MobileUnlockSales_Entity)){
						$this->Flash->error("Failed to create refund, please contact admin for unlock id ".$MobileUnlocksEntity->id);
						return $this->redirect(array('action' => 'all'));
					}
				}elseif($problemVar['status'] == VIRTUALLY_BOOKED){
					//echo "1792";die;
				}
				//----- /if for saving sales data----------------
					
				$mobileModels_query = $this->MobileUnlocks->MobileModels->find('list',array(
																		'keyField' => 'id',
																		'valueField' => 'model',
																		'order'=>'model asc',
																		'conditions' => array(
																				  'MobileModels.status' => 1,
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
				
				$unlockBookingData = $this->request->data['MobileUnlock'];
				$codeClause = "";
				
				if(!empty($unlockBookingData['code']) && $unlockBookingData['code'] != NULL){
					$codeClause = "Your unlock code is ".$unlockBookingData['code'].".<br/><br/>";
				}
				
				$unlockCodeInstructions = '';
				if(!empty($unlockBookingData['unlock_code_instructions']) && $unlockBookingData['unlock_code_instructions'] != NULL){
					$unlockCodeInstructions = "Unlock Instructions:<br/>".$unlockBookingData['unlock_code_instructions'].".<br/><br/><span style='color: red;'>**PLEASE READ: Please do not attempt if you are unsure about the above procedure. As incorrect procedure may lock your phone permanently!</span><br/><br/>";
				}
				
				$unlockBookingData['mobile_model_id'] = $mobileModelId;
				$unlockBookingData['unlocking_days'] = $unlockingDays;
				$unlockStatus = $unlockBookingData['status'];
				$messageStatement = '';
				//die;
					switch($unlockStatus){
						case VIRTUALLY_BOOKED:
							$unlockStatusStatement = "The unlock has been booked for your unlock id ".$unlockBookingData['id']." and Mobile Model :".$mobileModels[$unlockBookingData['mobile_model_id']]." phone &#40;IMEI: ".$unlockBookingData['imei']."&#41;. Your unlock is expected to get done within ".$unlockBookingData['unlocking_days']." working day&#40;s&#41;.<br/><br/>";
							$messageStatement = "Your phone unlock id ".$unlockBookingData['id']." has been booked and will be unlocked within ".$unlockBookingData['unlocking_days']." working days. Please contact the kiosk with any queries t&s ".$this->setting['repair_unlock_terms_link'];
							break;
						
						case BOOKED:
							$unlockStatusStatement = "The unlock has been booked for your unlock id ".$unlockBookingData['id']." and Mobile Model :".$mobileModels[$unlockBookingData['mobile_model_id']]." phone &#40;IMEI: ".$unlockBookingData['imei']."&#41;. Your unlock is expected to get done within ".$unlockBookingData['unlocking_days']." working day&#40;s&#41;.<br/><br/>";
							$messageStatement = "Your phone unlock id ".$unlockBookingData['id']." has been booked and will be unlocked within ".$unlockBookingData['unlocking_days']." working days. Please contact the kiosk with any queries t&s ".$this->setting['repair_unlock_terms_link'];
							break;
					
						case DISPATCHED_2_CENTER:
							$unlockStatusStatement = "Your unlock id ".$unlockBookingData['id']." and Mobile Model :".$mobileModels[$unlockBookingData['mobile_model_id']]." phone &#40;IMEI: ".$unlockBookingData['imei']."&#41; has been sent to the specialized team for unlock. We will keep you posted with the updates. Your unlock is expected to get done within ".$unlockBookingData['unlocking_days']." working day&#40;s&#41;.<br/><br/>";
							break;
						
						case UNLOCK_FAILED_CONFIRMATION_SENT_2_KIOSK:
							$unlockStatusStatement = "The unlock for your unlock id ".$unlockBookingData['id']." and Mobile Model :".$mobileModels[$unlockBookingData['mobile_model_id']]." phone &#40;IMEI: ".$unlockBookingData['imei']."&#41; could not be processed. Please get in touch with the Kiosk for any query.<br/><br/>";
							$messageStatement = "Your phone unlock id ".$unlockBookingData['id']." is un-successful. Please contact ".$kioskContact." for refund t&s ".$this->setting['repair_unlock_terms_link'];
							break;
						
						case RECEIVED_UNLOCKED_FROM_CENTER:
							$unlockStatusStatement = "Your unlock id ".$unlockBookingData['id']." and Mobile Model :".$mobileModels[$unlockBookingData['mobile_model_id']]." phone &#40;IMEI: ".$unlockBookingData['imei']."&#41; has been successfully unlocked. Please contact ".$kiosks[$unlockBookingData['kiosk_id']]." before collecting the phone.<br/><br/>Thank you for using our Mobile Unlocking services.<br/><br/>{$codeClause}{$unlockCodeInstructions}";
							if($checkIfVirtuallyBooked == 0){
								$messageStatement = "Your phone unlock id ".$unlockBookingData['id']." successfully unlocked is ready for collection. Please contact ".$kioskContact." before collection t&s ".$this->setting['repair_unlock_terms_link'];
							}
							break;
						
						case RECEIVED_UNPROCESSED_FROM_CENTER:
							$unlockStatusStatement = "The unlock for your unlock id ".$id." and Mobile Model :".$mobileModels[$unlockBookingData['mobile_model_id']]." phone &#40;IMEI: ".$unlockBookingData['imei']."&#41; could not be processed. Please get in touch with the Kiosk for any query.<br/><br/>";
							if($checkIfVirtuallyBooked == 0){
								$messageStatement = "Your phone unlock id ".$unlockBookingData['id']." un-successful is ready for collection. Please contact ".$kioskContact." before collection t&s ".$this->setting['repair_unlock_terms_link'];
							}
							break;
						
						case UNLOCK_PROCESSED_CONFIRMATION_SENT_2_KIOSK:
							$unlockStatusStatement = "The unlock for your unlock id ".$unlockBookingData['id']." and Mobile Model :".$mobileModels[$unlockBookingData['mobile_model_id']]." phone &#40;IMEI: ".$unlockBookingData['imei']."&#41; has been successfully processed. Please get in touch with the Kiosk for any query.<br/><br/>{$codeClause}{$unlockCodeInstructions}";
							$messageStatement = "Your phone unlock id ".$unlockBookingData['id']." successfully unlocked. Please contact ".$kioskContact." for guidance t&s ".$this->setting['repair_unlock_terms_link'];
							break;
						
						case UNLOCKED_CONFIRMATION_PASSED:
							$unlockStatusStatement = "The unlock has been successfully processed for your unlock id ".$unlockBookingData['id']." and Mobile Model :".$mobileModels[$unlockBookingData['mobile_model_id']]." phone &#40;IMEI: ".$unlockBookingData['imei']."&#41;. Please get in touch with the Kiosk for any query.<br/><br/>{$codeClause}{$unlockCodeInstructions}";
							break;
					
						case UNLOCKING_FAILED_CONFIRMATION_PASSED:
							$unlockStatusStatement = "The unlock for your unlock id ".$unlockBookingData['id']." and Mobile Model :".$mobileModels[$unlockBookingData['mobile_model_id']]." phone &#40;IMEI: ".$unlockBookingData['imei']."&#41; could not be processed and a refund of ".$currency.$unlockBookingData['estimated_cost']." has been made. We regret for the inconvenience.<br/><br/>";
							break;
					
						case DELIVERED_UNLOCKED_BY_CENTER://
							$unlockStatusStatement = "Your unlock id ".$unlockBookingData['id']." and Mobile Model :".$mobileModels[$unlockBookingData['mobile_model_id']]." phone &#40;IMEI: ".$unlockBookingData['imei']."&#41; has been successfully unlocked and delivered to you.<br/><br/>Thank you for using our Mobile Unlocking services.{$codeClause}{$unlockCodeInstructions}";
							$messageStatement = "Thank you for collecting your un-locked phone id ".$unlockBookingData['id'].". Thank you for using our service t&s ".$this->setting['repair_unlock_terms_link'];
							break;
					
						case DELIVERED_UNLOCKING_FAILED_AT_CENTER:
							$unlockStatusStatement = "The unlock for your unlock id ".$unlockBookingData['id']." and Mobile Model :".$mobileModels[$unlockBookingData['mobile_model_id']]." phone &#40;IMEI: ".$unlockBookingData['imei']."&#41; could not be processed and has been delivered back to you. We regret for the inconvenience.<br/><br/>";
							$messageStatement = "Thank you for collecting your phone id ".$unlockBookingData['id'].". We are sorry we could not unlock this phone t&s ".$this->setting['repair_unlock_terms_link'];
							break;
					
						case DELIVERED_UNLOCKED_BY_KIOSK://
							$unlockStatusStatement = "Your unlock id ".$unlockBookingData['id']." and Mobile Model :".$mobileModels[$unlockBookingData['mobile_model_id']]." phone &#40;IMEI: ".$unlockBookingData['imei']."&#41; has been successfully unlocked and delivered to you.<br/><br/>Thank you for using our Mobile Unlocking services.{$codeClause}{$unlockCodeInstructions}";
							$messageStatement = "Thank you for collecting your un-locked phone id ".$unlockBookingData['id'].". Thank you for using our service t&s ".$this->setting['repair_unlock_terms_link'];
							break;
					
						case DELIVERED_UNLOCKING_FAILED_AT_KIOSK:
							$unlockStatusStatement = "The unlock for your unlock id ".$unlockBookingData['id']." and Mobile Model :".$mobileModels[$unlockBookingData['mobile_model_id']]." phone &#40;IMEI: ".$unlockBookingData['imei']."&#41; could not be processed and has been delivered back to you. We regret for the inconvenience.<br/><br/>";
							$messageStatement = "Thank you for collecting your phone id ".$unlockBookingData['id'].". We are sorry we could not unlock this phone t&s ".$this->setting['repair_unlock_terms_link'];
							break;
					}
					
				
				$send_by_email = Configure::read('send_by_email');
				$emailSender = Configure::read('EMAIL_SENDER');
				if(!empty($unlockStatusStatement)){
					if($this->request->session()->read('Auth.User.group_id') == MANAGERS || $this->request->session()->read('Auth.User.group_id') == ADMINISTRATORS || $this->request->session()->read('Auth.User.group_id') == FRANCHISE_OWNER){
						if($this->request['data']['MobileUnlock']['send'] == '1'){
							if(!empty($dataPerId['MobileUnlock']['customer_contact'])){
								$destination = $dataPerId['MobileUnlock']['customer_contact'];
								if(!empty($messageStatement)){
									$this->TextMessage->test_text_message($destination, $messageStatement);
								}
							}
							if(!empty($emailTo)){
								$Email = new Email();
								$Email->config('default');
								$Email->viewVars(array('unlockBookingData' => $unlockBookingData,'unlockStatus' => $unlockStatus,'mobileModels' => $mobileModels, 'kiosks' => $kiosks, 'unlockStatusStatement' => $unlockStatusStatement, 'kiosk_id' => $kiosk_id, 'brands' => $brands,'kioskaddress' => $kioskaddress,'countryOptions' => $countryOptions, 'unlock_email_message' => $unlock_email_message));
								//$Email->config(array('from' => 'me@example.org', 'transport' => 'MyCustom'));
								//$Email = new CakeEmail(array('from' => 'me@example.org', 'transport' => 'MyCustom'));
								$emailTo = $unlockBookingData['customer_email'];
								$Email->template('unlock_booking_receipt');
								$Email->emailFormat('both');
								$Email->to($emailTo);
								$Email->transport(TRANSPORT);
								$Email->from([$send_by_email => $emailSender]);
								//$Email->sender("sales@oceanstead.co.uk");
								$Email->subject('Mobile Unlock Details');
								$Email->send();	
							}
							
						}
					}else{
						if(!empty($dataPerId['MobileUnlock']['customer_contact'])){
							$destination = $dataPerId['MobileUnlock']['customer_contact'];
							if(!empty($messageStatement)){
								$this->TextMessage->test_text_message($destination, $messageStatement);
							}
						}
						if(!empty($emailTo)){
							$Email = new Email();
							$Email->config('default');
							$Email->viewVars(array('unlockBookingData' => $unlockBookingData,'unlockStatus' => $unlockStatus,'mobileModels' => $mobileModels, 'kiosks' => $kiosks, 'unlockStatusStatement' => $unlockStatusStatement, 'kiosk_id' => $kiosk_id, 'brands' => $brands,'kioskaddress' => $kioskaddress,'countryOptions' => $countryOptions, 'unlock_email_message' => $unlock_email_message));
							//$Email->config(array('from' => 'me@example.org', 'transport' => 'MyCustom'));
							//$Email = new CakeEmail(array('from' => 'me@example.org', 'transport' => 'MyCustom'));
							$emailTo = $unlockBookingData['customer_email'];
							$Email->template('unlock_booking_receipt');
							$Email->emailFormat('both');
							$Email->to($emailTo);
							 $Email->transport(TRANSPORT);
							$Email->from([$send_by_email => $emailSender]);
							//$Email->sender("sales@oceanstead.co.uk");
							$Email->subject('Mobile Unlock Details');
							$send_mail = 0;
							if($send_mail == 1){
								$Email->send();
							}
						}
						
					}
				}
				
				//Note: pending:status messages w.r.t status
				$statusArr = $this->status_unlock(); //$unlockStatus
				$this->Flash->success($statusArr[$unlockStatus]." (Unlock ID: $id).");
				//$this->Session->setFlash(__('The mobile unlock has been saved.'));
				
				if($this->request->session()->read('Auth.User.group_id') == UNLOCK_TECHNICIANS){
					return $this->redirect(array('action' => 'virtually_booked'));
				}else{					
					if($unlockStatus == UNLOCKING_FAILED_CONFIRMATION_PASSED){
						$print_type = $this->setting['print_type'];
						if($print_type == 1){
							return $this->redirect(array('controller' => 'prints','action' => 'unlock',$id));	
						}else{
							return $this->redirect(array('action' => 'all'));
						}
						
					}else{
						return $this->redirect(array('action' => 'all'));
					}
				}
			}else{
				//Case: Failed in saving unlock data
				$this->Flash->error(__('The mobile unlock could not be saved. Please, try again.'));
			}
		}else{
			//-----Case: Block to render layout when user launched to edit page without post 
			$options = array('conditions' => array('MobileUnlocks.id' => $id));
			$res_query = $this->MobileUnlocks->find('all', $options);
			$res_query = $res_query->hydrate(false);
			if(!empty($res_query)){
				$res = $res_query->first();
			}else{
				$res = array();
			}
			$this->request->data = $res;
		}
		
		$mobileUnlockPriceData_query = $this->MobileUnlockPrices->find('all',array('conditions' => array(
																		'MobileUnlockPrices.status'=>1,
																		'MobileUnlockPrices.brand_id' => $brandId,
																		'MobileUnlockPrices.mobile_model_id' => $mobileModelId,
																		'MobileUnlockPrices.unlocking_price > 0'
																		),
																	'fields' => array('network_id','unlocking_price')
																));
		//pr($mobileUnlockPriceData_query);die;
		$mobileUnlockPriceData_query = $mobileUnlockPriceData_query->hydrate(false);
		if(!empty($mobileUnlockPriceData_query)){
			$mobileUnlockPriceData = $mobileUnlockPriceData_query->toArray();
		}else{
			$mobileUnlockPriceData  = array();
		}
		
		$networkOptionsArr = array();
		$networkIds = array();
		foreach($mobileUnlockPriceData as $key => $mobileUnlockPriceInfo){
			$networkIds[$mobileUnlockPriceInfo['network_id']] = $mobileUnlockPriceInfo['network_id'];
			$costArr[$mobileUnlockPriceInfo['network_id']] = $mobileUnlockPriceInfo['unlocking_price'];
		}
		if(empty($networkIds)){
			$networkIds = array(0 => null);
		}
		
		$networks_query = $this->MobileUnlocks->Networks->find('list',
																[
																	'keyField' => 'id',
																	'valueField' => 'title',
																	'conditions' => ['Networks.status' => 1,
																						'Networks.id IN' => $networkIds]
																]
														 );
		$networks_query = $networks_query->hydrate(false);
		if(!empty($networks_query)){
			$networks = $networks_query->toArray();
		}else{
			$networks = array();
		}
		
		//$this->MobileUnlock->CommentMobileUnlock->unbindModel(array('hasMany' => array('MobileUnlock')));
		//$this->MobileUnlock->CommentMobileUnlock->unbindModel(array('associationType' => array('MobileUnlock')));
		$comments_query = $this->MobileUnlocks->CommentMobileUnlocks->find('all', array(
									 'conditions' => array('CommentMobileUnlocks.status' => 1,'CommentMobileUnlocks.mobile_unlock_id' => $id),
									 'order' => array('CommentMobileUnlocks.id DESC'),
									 'contain' => array('Users'),
									 'limit' => 5
									));
		$comments_query = $comments_query->hydrate(false);
		if(!empty($comments_query)){
			$comments = $comments_query->toArray();
		}else{
			$comments = array();
		}
		//$this->Model->unbindModel(array('associationType' => array('associatedModelClassName')));
		//print_r($comments);die;
		$this->set(compact('kiosks', 'brands','mobileModels','networks','comments','unlockLogs','users', 'unlockingDays','checkIfVirtuallyBooked','unlockMinutes','costArr'));
		fakeblock:
		;
	}
	
	public function calculatePaymentAjax(){
		if(!empty($this->request->query)){
			$id = $this->request->query['id'];
			$options = array('conditions' => array('MobileUnlocks.id' => $id));
			$res_query = $this->MobileUnlocks->find('all', $options);
			$res_query = $res_query->hydrate(false);
			if(!empty($res_query)){
				$res = $res_query->first();
			}else{
				$res = array();
			}
			if(!empty($res)){
				$amount = $res['estimated_cost'];
				echo json_encode(array('amount' => $amount));die;
			}else{
				echo json_encode(array('error' => 'no result found for this id in database'));die;
			}
		}else{
			echo json_encode(array('error' => 'no id found'));die;
		}
	}
	
	public function makePaymentAjax(){
		if(!empty($this->request->query)){
			$final_amount = $this->request->query['final_amount'];
			$unlock_id = $this->request->query['unlock_id'];
			$payment_1 = $this->request->query['payment_1'];
			$payment_2 = $this->request->query['payment_2'];
			$method_1 = $this->request->query['method_1'];
			$method_2 = $this->request->query['method_2'];
			$part_time = $this->request->query['part_time'];
			
			$res = $this->UnlockPayments->find('all',array('conditions' => array(
																		  'mobile_unlock_id' => $unlock_id,
																		  ),
														   'order' => ['created desc']
														   ))->toArray();
			if(!empty($res)){
//				$last_created = $res[0]->created;
//				
//				$last_created = $last_created->i18nFormat(
//                                                                [\IntlDateFormatter::FULL, \IntlDateFormatter::FULL]
//                                                        );
//				$check_time = date("Y-m-d h:i:s",strtotime($last_created." +1 minutes"));
//				$current_time = date("Y-m-d h:i:s");
				//if(strtotime($current_time) < strtotime($check_time)){
					echo json_encode(array('error' =>"Payment Allready done"));die;
				//}
			}
			$counter = 1;
			$kioskId = $this->request->Session()->read('kiosk_id');
			$userId = $this->request->session()->read('Auth.User.id');
			$payment_status = 1;
			if($part_time == 1){
				if($payment_1 + $payment_2 == $final_amount){
					$paymentDetailData = array(
							'kiosk_id' => $kioskId,
							'user_id' => $userId,
							'mobile_unlock_id' => $unlock_id,
							'payment_method' => $method_1,
							'amount' => $payment_1,
							'payment_status' => $payment_status,
							'status' => 1,//this 1 currently does not have any relevance
							   );
					$unlockPaymentsEntity1 = $this->UnlockPayments->newEntity($paymentDetailData,['validate' => false]);
					$unlockPaymentsEntity1 = $this->UnlockPayments->patchEntity($unlockPaymentsEntity1,$paymentDetailData,['validate' => false]);
					if($this->UnlockPayments->save($unlockPaymentsEntity1)){
						$counter++;
					}
					
					$paymentDetailData_1 = array(
							'kiosk_id' => $kioskId,
							'user_id' => $userId,
							'mobile_unlock_id' => $unlock_id,
							'payment_method' => $method_2,
							'amount' => $payment_2,
							'payment_status' => $payment_status,
							'status' => 1,//this 1 currently does not have any relevance
							   );
					$unlockPaymentsEntity = $this->UnlockPayments->newEntity($paymentDetailData_1,['validate' => false]);
					$unlockPaymentsEntity = $this->UnlockPayments->patchEntity($unlockPaymentsEntity,$paymentDetailData_1,['validate' => false]);
					if($this->UnlockPayments->save($unlockPaymentsEntity)){
						$counter++;
					}
				}else{
					echo json_encode(array('error' =>"amount is not matching"));die;
				}
			}else if($part_time == 0){
				if($payment_1 == $final_amount){
					$paymentDetailData = array(
							'kiosk_id' => $kioskId,
							'user_id' => $userId,
							'mobile_unlock_id' => $unlock_id,
							'payment_method' => $method_1,
							'amount' => $payment_1,
							'payment_status' => $payment_status,
							'status' => 1,//this 1 currently does not have any relevance
							   );
					$unlockPaymentsEntity = $this->UnlockPayments->newEntity($paymentDetailData,['validate' => false]);
					$unlockPaymentsEntity = $this->UnlockPayments->patchEntity($unlockPaymentsEntity,$paymentDetailData,['validate' => false]);
					if($this->UnlockPayments->save($unlockPaymentsEntity)){
						$counter++;
					}
				}else{
					echo json_encode(array('error' =>"amount is not matching"));die;
				}
			}
			if($counter >0){
				$this->generate_sale($unlock_id,$kioskId,$userId,$final_amount);
			}
		}else{
			echo json_encode(array('error' =>"query string is empty"));die;
		}
	}
	
	public function generate_sale($unlock_id,$kioskId,$userId,$final_amount){
		$options = array('conditions' => array('MobileUnlocks.id' => $unlock_id));
		$res_query = $this->MobileUnlocks->find('all', $options);
		$res_query = $res_query->hydrate(false);
		if(!empty($res_query)){
			$res = $res_query->first();
		}else{
			$res = array();
		}
		if(!empty($res)){
			$booking_kiosk_id = $res['kiosk_id'];
			$prv_status = $res['status'];
			if($prv_status == RECEIVED_UNLOCKED_FROM_CENTER){
				$unlock_status = DELIVERED_UNLOCKED_BY_CENTER;
			}else{
				$unlock_status = DELIVERED_UNLOCKED_BY_KIOSK;
			}
			
			
			$mobileUnlockLogsData = array(
							'kiosk_id' => $booking_kiosk_id,
							'user_id' => $userId,
							'mobile_unlock_id' => $unlock_id,
							'unlock_center_id' => $kioskId,
							'unlock_status' => $unlock_status
						);
									
			$MobileUnlockLogsEntity = $this->MobileUnlockLogs->newEntity($mobileUnlockLogsData,['validate' => false]);
			$MobileUnlockLogsEntity = $this->MobileUnlockLogs->patchEntity($MobileUnlockLogsEntity,$mobileUnlockLogsData,['validate' => false]);
			$this->MobileUnlockLogs->save($MobileUnlockLogsEntity);
			$dateUnlocked = date('Y-m-d G:i:s A');
			
			$mobileUnlockSalesData = array(
											'kiosk_id' => $booking_kiosk_id,
											'retail_customer_id' => $res['retail_customer_id'],
											'mobile_unlock_id' => $unlock_id,
											//'sold_by' => $this->Session->read('Auth.User.id'),
											'sold_on' => $dateUnlocked,
											'refund_by' => '',
											'amount' => $final_amount,
											'refund_amount' => '',
											'refund_status' => 0,
											'refund_on' => '',
											'refund_remarks' => ''
											);
			if($this->request->session()->read('Auth.User.group_id') == KIOSK_USERS){
				$mobileUnlockSalesData['sold_by'] = $userId;
				$mobileUnlockSalesData['sold_on'] = $dateUnlocked;
			}
			$MobileUnlockSalesEntity = $this->MobileUnlockSales->newEntity($mobileUnlockSalesData,['validate' => false]);
			
			$MobileUnlockSalesEntity = $this->MobileUnlockSales->patchEntity($MobileUnlockSalesEntity,$mobileUnlockSalesData,['validate' => false]);
			if($this->MobileUnlockSales->save($MobileUnlockSalesEntity)){
				$unlckSaleId = $MobileUnlockSalesEntity->id;
				$query = "UPDATE  `unlock_payments`  SET  `mobile_unlock_sale_id` = $unlckSaleId WHERE `unlock_payments`.`mobile_unlock_id` = $unlock_id";
				$conn = ConnectionManager::get('default');
				$stmt = $conn->execute($query); 

					
				//$this->MobileUnlock->id = $unlock_id;
				$data = array('status' => $unlock_status);
				$MobileUnlocksEntity = $this->MobileUnlocks->get($unlock_id);
				$MobileUnlocksEntity = $this->MobileUnlocks->patchEntity($MobileUnlocksEntity,$data,['validate' => false]);
				$this->MobileUnlocks->save($MobileUnlocksEntity);
				
				
				if(array_key_exists('imei',$res)){
					$imei = $res['imei'];
				}
				$mobilePurchaseData_query = $this->MobilePurchases->find('all',array('conditions'=>array('MobilePurchases.imei'=>$imei,'MobilePurchases.status'=>3)));
				$mobilePurchaseData_query = $mobilePurchaseData_query->hydrate(false);
				if(!empty($mobilePurchaseData_query)){
					$mobilePurchaseData = $mobilePurchaseData_query->first();
				}else{
					$mobilePurchaseData = array();
				}
				if(!empty($mobilePurchaseData)){
					$purchaseId = $mobilePurchaseData['id'];
					$mobileTransferLogData = array(
						'mobile_purchase_reference' => $mobilePurchaseData['mobile_purchase_reference'],
						'mobile_purchase_id' => $mobilePurchaseData['id'],
						'kiosk_id' => $booking_kiosk_id,
						'network_id' => $mobilePurchaseData['network_id'],
						'grade' => $mobilePurchaseData['grade'],
						'type' => $mobilePurchaseData['type'],
						'receiving_status' => 0,
						'imei' => $mobilePurchaseData['imei'],
						'user_id' => $userId,
						'status' => 0//available
						);
					
					$mobileTransferLogDataEntity = $this->MobileTransferLogs->newEntity($mobileTransferLogData,['validate' => false]);
					$mobileTransferLogDataEntity = $this->MobileTransferLogs->patchEntity($mobileTransferLogDataEntity,$mobileTransferLogData,['validate' => false]);
					$this->MobileTransferLogs->save($mobileTransferLogDataEntity);
					
					$mobile_purchase_data = array('status' => 0,'type' => 0);
					$MobilePurchaseEntity = $this->MobilePurchases->get($purchaseId);
					$MobilePurchaseEntity = $this->MobilePurchases->patchEntity($MobilePurchaseEntity,$mobile_purchase_data,['validate' => false]);
					$this->MobilePurchases->save($MobilePurchaseEntity);//changing the status to available
				}
			}
			$activeCombinations_query = $this->MobileUnlockPrices->find('all',array('conditions' => array('MobileUnlockPrices.status'=>1,
												       'MobileUnlockPrices.unlocking_price > 0'),
							      'fields' => array('mobile_model_id','brand_id'),
							      'group' => 'MobileUnlockPrices.mobile_model_id'
							      ));
			$activeCombinations_query = $activeCombinations_query->hydrate(false);
			if(!empty($activeCombinations_query)){
				$activeCombinations = $activeCombinations_query->toArray();
			}else{
				$activeCombinations = array();
			}
			$activeBrands = array();
			$activeModels = array();
			foreach($activeCombinations as $key => $activeCombination){
				$activeBrands[$activeCombination['brand_id']] = $activeCombination['brand_id'];
				$activeModels[$activeCombination['mobile_model_id']] = $activeCombination['mobile_model_id'];
			}
			
			
			$mobileModels_query = $this->MobileUnlocks->MobileModels->find('list',array(
																		'keyField' =>'id',
																		'valueField' => 'model',
																		'order'=>'model asc',
																		'conditions' => array(
																				  'MobileModels.status' => 1,
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
			$unlockBookingData = $res;
			$codeClause = "";
			
			if(!empty($unlockBookingData['code']) && $unlockBookingData['code'] != NULL){
				$codeClause = "Your unlock code is ".$unlockBookingData['code'].".<br/><br/>";
			}
				
			$unlockCodeInstructions = '';
			if(!empty($unlockBookingData['unlock_code_instructions']) && $unlockBookingData['unlock_code_instructions'] != NULL){
				$unlockCodeInstructions = "Unlock Instructions:<br/>".$unlockBookingData['unlock_code_instructions'].".<br/><br/><span style='color: red;'>**PLEASE READ: Please do not attempt if you are unsure about the above procedure. As incorrect procedure may lock your phone permanently!</span><br/><br/>";
			}
			
			$brandId = $res['brand_id'];
			$mobileModelId = $res['mobile_model_id'];
			$networkId = $res['network_id'];
			
			
			$unlockingDaysArr_query = "SELECT `unlocking_days`,`unlocking_minutes` from `mobile_unlock_prices` WHERE `brand_id`='$brandId' AND `mobile_model_id`='$mobileModelId' AND `network_id`='$networkId'";
			$conn = ConnectionManager::get('default');
			$stmt = $conn->execute($unlockingDaysArr_query); 
			$unlockingDaysArr = $stmt ->fetchAll('assoc');
		//pr($unlockingDaysArr);die;
		if(array_key_exists(0,$unlockingDaysArr)){
			$unlockingDays = $unlockingDaysArr['0']['unlocking_days'];
			$unlockMinutes = $unlockingDaysArr['0']['unlocking_minutes'];
			if(empty($unlockingDays) && empty($unlockMinutes)){
				$unlockingDays = 3;
				$unlockMinutes = 0;
			}else{
				if(empty($unlockingDays)){
					$unlockingDays = 0;
				}
				if(empty($unlockMinutes)){
					$unlockMinutes = 0;
				}
				
			}
		}else{
			$unlockingDays = 3;//kept in case there are no unlocking days
			$unlockMinutes = 0;
		}
			
				
			$unlockBookingData['mobile_model_id'] = $mobileModelId;
			$unlockBookingData['unlocking_days'] = $unlockingDays;
			$unlockStatus = $unlockBookingData['status'];
			$messageStatement = '';
			
			$unlockStatusStatement = "Your unlock id".$unlockBookingData['id'].",".$mobileModels[$unlockBookingData['mobile_model_id']]." phone &#40;IMEI: ".$unlockBookingData['imei']."&#41; has been successfully unlocked and delivered to you.<br/><br/>Thank you for using our Mobile Unlocking services.{$codeClause}{$unlockCodeInstructions}";
			$messageStatement = "Thank you for collecting your un-locked phone id ".$unlockBookingData['id'].". Thank you for using our service t&s ".$this->setting['repair_unlock_terms_link'];
			$unlock_email_message = $this->setting['unlock_email_message'];
			$brands_query = $this->MobileUnlocks->Brands->find('list', array(
												'keyField' => 'id',
												'valueField' => 'brand',
												'order'=>'brand asc',
											 'conditions' => array('Brands.status' => 1,
														   'Brands.id IN' => $activeBrands)
											));
			$brands_query = $brands_query->hydrate(false);
			if(!empty($brands_query)){
				$brands = $brands_query->toArray();
			}else{
				$brands = array();
			}
			$countryOptions = Configure::read('uk_non_uk');
			//$this->set(compact('countryOptions'));
			
			$kiosks_query = $this->MobileUnlocks->Kiosks->find('list',array(
										 'conditions' => array('Kiosks.status' => 1)
										));
			$kiosks_query = $kiosks_query->hydrate(false);
			if(!empty($kiosks_query)){
				$kiosks = $kiosks_query->toArray();
			}else{
				$kiosks = array();
			}
			$kioskaddress_query = $this->Kiosks->find('all',array(
						'fields' => array('Kiosks.address_1','Kiosks.address_2', 'Kiosks.city','Kiosks.state','Kiosks.country','Kiosks.zip','Kiosks.contact'),
						'conditions'=> array('Kiosks.id' => $kioskId)
						)
					);
			$kioskaddress_query = $kioskaddress_query->hydrate(false);
			if(!empty($kioskaddress_query)){
				$kioskaddress = $kioskaddress_query->first();
			}else{
				$kioskaddress = array();
			}
			$kioskaddress_query = array();
			$send_by_email = Configure::read('send_by_email');
			$emailSender = Configure::read('EMAIL_SENDER');
			if($unlockStatusStatement){
				if(!empty($res['customer_contact'])){
					$destination = $res['customer_contact'];
					if(!empty($messageStatement)){
						$this->TextMessage->test_text_message($destination, $messageStatement);
					}
				}
				if(!empty($emailTo)){
					$Email = new Email();
					$Email->config('default');
					$Email->viewVars(array('unlockBookingData' => $unlockBookingData,'unlockStatus' => $unlock_status,'mobileModels' => $mobileModels, 'kiosks' => $kiosks, 'unlockStatusStatement' => $unlockStatusStatement, 'kiosk_id' => $kioskId, 'brands' => $brands,'kioskaddress' => $kioskaddress,'countryOptions' => $countryOptions, 'unlock_email_message' => $unlock_email_message));
					//$Email->config(array('from' => 'me@example.org', 'transport' => 'MyCustom'));
					//$Email = new CakeEmail(array('from' => 'me@example.org', 'transport' => 'MyCustom'));
					$emailTo = $unlockBookingData['customer_email'];
					$Email->template('unlock_booking_receipt');
					$Email->emailFormat('both');
					$Email->to($emailTo);
					 $Email->transport(TRANSPORT);
						$Email->from([$send_by_email => $emailSender]);
					//$Email->sender("sales@oceanstead.co.uk");
					$Email->subject('Mobile Unlock Details');
					$send_mail = 0;
					if($send_mail == 1){
						$Email->send();
					}
				}
				
			}
			
			$statusArr = $this->status_unlock(); //$unlockStatus
			$mag = $statusArr[$unlock_status]." (Unlock ID: $unlock_id).";
			echo json_encode(array('status' => $mag,'id' => $unlock_id));die;
		}else{
			echo json_encode(array('error' =>"no data found for this id on step two"));die;
		}
	}
	
	public function updateUnlockPayment($unlockId = ''){
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
		$users_query = $this->Users->find('list',array(
													  'keyField' => 'id',
													  'valueField' => 'username',
													  ));
		$users_query = $users_query->hydrate(false);
		if(!empty($users_query)){
			$users = $users_query->toArray();
		}else{
			$users = array();
		}
		$paymentType = array('Cash' => 'Cash', 'Card' => 'Card');
		$paymentData_query = $this->UnlockPayments->find('all',array(
							'conditions' => array('UnlockPayments.mobile_unlock_id'=>$unlockId)
								)
							  );
		$paymentData_query = $paymentData_query->hydrate(false);
		if(!empty($paymentData_query)){
			$paymentData = $paymentData_query->toArray();
		}else{
			$paymentData = array();
		}
		$saleData_query = $this->MobileUnlockSales->find('all', array('conditions' => array('MobileUnlockSales.mobile_unlock_id' => $unlockId, 'MobileUnlockSales.refund_status' => 0)));
		$saleData_query = $saleData_query->hydrate(false);
		if(!empty($saleData_query)){
			$saleData = $saleData_query->first();
		}else{
			$saleData = array();
		}
		$saleAmount = $saleData['amount'];
		
		//Log: change done on 1st June as requested by Inder for editing payment only for same day by kiosk user
		
		$conn = ConnectionManager::get('default');
		$stmt = $conn->execute('SELECT CURDATE() as timeDate'); 
		$currentTime = $stmt ->fetchAll('assoc');
		
		 $currentDate = strtotime($currentTime[0]['timeDate']);
		//$checkTime = strtotime('-24 hours',$time);
		if(count($paymentData) && $this->request->session()->read('Auth.User.group_id') == KIOSK_USERS){
			$created = getdate(strtotime($paymentData[0]['created']));
			$curDate =  $created["year"]."-".$created["mon"]."-".$created["mday"];
			$createdTime = strtotime($curDate);
			if($currentDate != $createdTime){//$checkTime > $createdTime
				$this->Flash->error('Payment can only be updated within same day!');
				return $this->redirect(array('controller' => 'mobile_unlock_sales', 'action' => 'view_unlock_sales'));
				die;
			}
		}
		
		
			if ($this->request->is(array('post', 'put'))){
				// echo $unlock_id = $this->request->data['unlock_id'];
				if(array_key_exists('cancel',$this->request->data)){
					$this->Flash->error('You have cancelled transaction!');
					return $this->redirect(array('controller' => 'mobile_unlock_sales', 'action' => 'view_unlock_sales'));
					die;
				}
				if(is_array($this->request->data) && array_key_exists('UpdatePayment',$this->request->data) && count($this->request->data['UpdatePayment'])){
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
						return $this->redirect(array('action' => 'update_unlock_payment',$unlockId));
						die;
					}
					$saveAdminPayment = 0;
					//****saving newly added payment amount
					if(array_key_exists('added_amount',$this->request->data) && is_numeric($this->request->data['added_amount']) && ($this->request->data['added_amount'] > 0)){
						$paymntData_query = $this->UnlockPayments->find('all',array(
																				'conditions' => array('UnlockPayments.mobile_unlock_id'=>$unlockId)
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
						$paymntData['payment_method'] = $this->request->data['new_change_mode'];
						$paymntData['amount'] = $this->request->data['added_amount'];
						$UnlockPaymentsEntity = $this->UnlockPayments->newEntity($paymntData,['validate' => false]);
						$UnlockPaymentsEntity = $this->UnlockPayments->patchEntity($UnlockPaymentsEntity,$paymntData,['validate' => false]);
						//pr($UnlockPaymentsEntity);die;
						if($this->UnlockPayments->save($UnlockPaymentsEntity,['validate' => false])){
							$saveAdminPayment++;
						}else{
							///pr($UnlockPaymentsEntity);die;
						}
					}
					// saving new added payment till here*****
					$sale_amount = $this->request->data['sale_amount'];
					foreach($updatedPaymentData as $paymentId => $paymentMode){
						$paymentDetailData = array(
							//'id' => $paymentId,
							'payment_method' => $paymentMode,
							'amount' => $updatedAmountData[$paymentId]
							   );
						$UnlockPaymentsEntity = $this->UnlockPayments->get($paymentId);
						$UnlockPaymentsEntity = $this->UnlockPayments->patchEntity($UnlockPaymentsEntity,$paymentDetailData,['validate' => false]);
						if($this->UnlockPayments->save($UnlockPaymentsEntity)){
							$saveAdminPayment++;
						}
					}
					if($saveAdminPayment > 0){
						$this->Flash->error('Payment has been successfully updated!');
						return $this->redirect(array('controller'=>'mobile_unlock_sales','action' => 'view_unlock_sales'));
					}else{
						$this->Flash->error('Payment could not be updated!');
						return $this->redirect(array('action' => 'update_unlock_payment',$unlockId));
					}
				}
			}
            $this->set(compact('paymentData','paymentType','kiosks','users','saleAmount'));
		//$this->set(compact('paymentData','paymentType','kiosks','users','saleAmount'));
	}
	
	public function managerEdit($id = null) {
		//pr($_SESSION);die;
		if (!$this->MobileUnlocks->exists($id)) {
			throw new NotFoundException(__('Invalid mobile unlock'));
		}
		///echo $this->request->Session()->read('Auth.User.group_id');die;
		if($this->request->session()->read('Auth.User.group_id') == MANAGERS ||
		     $this->request->session()->read('Auth.User.group_id') == ADMINISTRATORS || $this->request->session()->read('Auth.User.group_id') == FRANCHISE_OWNER){
			$options = array('conditions' => array('MobileUnlocks.id' => $id),
							 'contain' => array('Kiosks','Brands','MobileModels','Networks','MobileUnlockLogs')
							 );
			$MobileUnlocks_query = $this->MobileUnlocks->find('all', $options);
			$MobileUnlocks_query = $MobileUnlocks_query->hydrate(false);
			if(!empty($MobileUnlocks_query)){
				$MobileUnlocks = $MobileUnlocks_query->first();
			}else{
				$MobileUnlocks = array();
			}
			$this->set('mobileUnlock', $MobileUnlocks);
			
			$checkIfVirtuallyBooked = 0;
			//below we are fetching all the entries that have status: VIRTUALLY_BOOKED AND BOOKED and will check the id that has greater value. The id that has greater value means it was inserted later in logs table and will be considered ie booked or virtually booked (this has been done for rebook case)
			$checkIfVirtuallyBookedOrBooked_query = $this->MobileUnlockLogs->find('all',array('conditions' => array(									'MobileUnlockLogs.mobile_unlock_id' => $id,
										'OR' => array(
												array('MobileUnlockLogs.unlock_status' => VIRTUALLY_BOOKED),
												array('MobileUnlockLogs.unlock_status' => BOOKED)
											)
										),
										'order' => 'MobileUnlockLogs.id desc'
										)
								);
			$checkIfVirtuallyBookedOrBooked_query = $checkIfVirtuallyBookedOrBooked_query->hydrate(false);
			if(!empty($checkIfVirtuallyBookedOrBooked_query)){
				$checkIfVirtuallyBookedOrBooked = $checkIfVirtuallyBookedOrBooked_query->toArray();
			}else{
				$checkIfVirtuallyBookedOrBooked = array();
			}
			if(count($checkIfVirtuallyBookedOrBooked)){
				if($checkIfVirtuallyBookedOrBooked['0']['unlock_status'] == VIRTUALLY_BOOKED){
					$checkIfVirtuallyBooked = 1;
				}
			}
			
			$comments_query = $this->MobileUnlocks->CommentMobileUnlocks->find('all', array(
										 //'fields' => array('*'),
										 'conditions' => array('CommentMobileUnlocks.status' => 1,'CommentMobileUnlocks.mobile_unlock_id' => $id),
										 'order' => array('CommentMobileUnlocks.id DESC'),
										 'limit' => 5,
										 'contain' => 'Users'
										));
			$comments_query = $comments_query->hydrate(false);
			if(!empty($comments_query)){
				$comments = $comments_query->toArray(); 
			}else{
				$comments = array();
			}
			//if(!empty($comments)){
			//	$users_data_query = $this->Users->find('all',['conditions'=>[''=>]])
			//}
			//pr($comments);die;
			$unlockLogs_query = $this->MobileUnlockLogs->find('all',array(
									'conditions'=>array('MobileUnlockLogs.mobile_unlock_id' => $id),
									'order'=>array('MobileUnlockLogs.id DESC')
									)
								   );
			$unlockLogs_query = $unlockLogs_query->hydrate(false);
			if(!empty($unlockLogs_query)){
				$unlockLogs = $unlockLogs_query->toArray();
			}else{
				$unlockLogs = array();
			}
			$users_query = $this->Users->find('list',array(
														  'keyField' => 'id',
														  'valueField' => 'username',
														  ));
			$users_query = $users_query->hydrate(false);
			if(!empty($users_query)){
				$users = $users_query->toArray();
			}else{
				$users = array();
			}
			
			$kiosks_query = $this->MobileUnlocks->Kiosks->find('list',array(
										 'conditions' => array('Kiosks.status' => 1)
										));
			
			$kiosks_query = $kiosks_query->hydrate(false);
			if(!empty($kiosks_query)){
				$kiosks = $kiosks_query->toArray();
			}else{
				$kiosks = array();
			}
			
			$dataPerId_query = $this->MobileUnlocks->find('all',array(
								'conditions' => array('MobileUnlocks.id'=>$id)
									     
									)
							       );
			$dataPerId_query = $dataPerId_query->hydrate(false);
			//pr($dataPerId_query);die;
			if(!empty($dataPerId_query)){
				$dataPerId = $dataPerId_query->first();
			}else{
				$dataPerId = array();
			}
			//pr($dataPerId);die;
					$dataPerId['brand_id'];
					$brandId = $dataPerId['brand_id'];
					$mobileModelId = $dataPerId['mobile_model_id'];
					$networkId = $dataPerId['network_id'];

					$MobileUnlockPrice_query = "SELECT `unlocking_days` from `mobile_unlock_prices` WHERE `brand_id`='$brandId' AND `mobile_model_id`='$mobileModelId' AND `network_id`='$networkId'";
					$conn = ConnectionManager::get('default');
					$stmt = $conn->execute($MobileUnlockPrice_query); 
					$unlockingDaysArr = $stmt ->fetchAll('assoc');
					
					//pr($unlockingDaysArr);die;										
					$unlockingDays = $unlockingDaysArr['0']['unlocking_days'];
			if($this->request->is('post') || $this->request->Session()->read('unlock_payment_confirmation.unlock_payment_status') == $id."_manager_edit"){
				//$this->Session->read('unlock_payment_confirmation.unlock_payment_status') becomes equal to
				//$id."_manager_edit when payment gets successfully processed
				//also we send array manager_edit_data as an array equal to $this->request->data from payment page
				
				//deleting the identification array, as we no longer require it
				$this->request->Session()->delete('unlock_payment_confirmation');
				if(count($this->request->data)){
					$unlockId = $this->request['data']['MobileUnlock']['id'];
					$estimateCost = $this->request['data']['MobileUnlock']['estimated_cost'];
					$updatedStatus = $this->request['data']['MobileUnlock']['status'];
					$kiosk_id = $this->request['data']['MobileUnlock']['kiosk_id'];//kiosk id of the original kiosk
				}
				//here we need to exempt internal bookings from payment
			
				if(is_array($this->request->data) && array_key_exists('MobileUnlock',$this->request->data)){
					//pr($this->request);die;
					if($this->request->data['MobileUnlock']['status'] == VIRTUALLY_BOOKED && $dataPerId['internal_unlock'] != 1){
						//echo'hi';die;
			//CASE I: ***status update to virtual booking from here
						//check if payment entry exists in table else, take payment
						//pr($unlockId);die;
						$checkIfPmtExists_query = $this->UnlockPayments->find('all', array('conditions' => array('UnlockPayments.mobile_unlock_id' => $unlockId)));
						//pr($checkIfPmtExists_query);die;
						$checkIfPmtExists = $checkIfPmtExists_query->count();
						//pr($checkIfPmtExists);die;
						//
						if($checkIfPmtExists == 0){//payment was never taken for this unlock id
							//take to payment screen
							$Data = $this->request->data;
							$Data['MobileUnlock']['booked_by'] = $dataPerId['booked_by'];
							$Data['MobileUnlock']['created'] = $dataPerId['created'];
							$this->request->Session()->write('manager_edit_data',$Data);
							return $this->redirect(array('controller'=>'mobile_unlocks','action'=>'unlock_payment'));
							die;
						}
			//**CASE I if loop till here
					}elseif($this->request->data['MobileUnlock']['status'] == BOOKED){
			//CASE II: ***status update to physical booking from here
						//deleting sale on moving from virtual to physical
						$MobileUnlockSale_query = "DELETE FROM `mobile_unlock_sales` WHERE `mobile_unlock_id` = '$id'";
						$conn = ConnectionManager::get('default');
						$stmt = $conn->execute($MobileUnlockSale_query); 
						//deleting payment on moving from physical to virtual
						$MobileUnlockSale_query = "DELETE FROM `unlock_payments` WHERE `mobile_unlock_id` = '$id'";
						$conn = ConnectionManager::get('default');
						$stmt = $conn->execute($MobileUnlockSale_query); 
						
						
			//CASE II: Ends here
					}elseif(($this->request->data['MobileUnlock']['status'] == DELIVERED_UNLOCKED_BY_CENTER || $this->request->data['MobileUnlock']['status'] == DELIVERED_UNLOCKED_BY_KIOSK) && $dataPerId['internal_unlock'] != 1){
						//echo'hi';die;
			//CASE III: ***status update to delivered unlocked by center or unlocked by kiosk from here
						//check if payment entry exists in table else, take payment
						$checkIfPmtExists_query = $this->UnlockPayments->find('all', array('conditions' => array('UnlockPayments.mobile_unlock_id' => $unlockId)));
						$checkIfPmtExists = $checkIfPmtExists_query->count();
						//
						if($checkIfPmtExists == 0){//payment was never taken for this unlock id
							//take to payment screen
							$Data = $this->request->data;
							$Data['MobileUnlock']['booked_by'] = $dataPerId['booked_by'];
							$Data['MobileUnlock']['created'] = $dataPerId['created'];
							$this->request->Session()->write('manager_edit_data',$Data);
							return $this->redirect(array('controller'=> 'mobile_unlocks','action'=>'unlock_payment'));
							die;
						}
			//CASE III: Ends here
					}elseif($this->request->data['MobileUnlock']['status'] == DELIVERED_UNLOCKING_FAILED_AT_CENTER || $this->request->data['MobileUnlock']['status'] == DELIVERED_UNLOCKING_FAILED_AT_KIOSK){
			//CASE IV: ***status update to delivered: unlocking failed from here
						//updating the amount to 0 sale on moving from delivered unlocked to delivered unlocking failed
						$this->MobileUnlockSales->updateAll(array('amount' => "'0'"),array('MobileUnlockSales.mobile_unlock_id' => $id));
						//deleting payment on moving from delivered unlocked to delivered unlocking failed
						$MobileUnlockSales_query = "DELETE FROM `unlock_payments` WHERE `mobile_unlock_id` = '$id'";
						$conn = ConnectionManager::get('default');
						$stmt = $conn->execute($MobileUnlockSales_query); 
						
			//CASE IV: Ends here
					}elseif($this->request->data['MobileUnlock']['status'] == UNLOCKING_FAILED_CONFIRMATION_PASSED){
			//CASE V: ***status update to unlocking failed:confirmation passed to customer from here
						//getting the orignal sale details from here
						$originalSale_query = $this->MobileUnlockSales->find('all', array('conditions' => array('MobileUnlockSales.mobile_unlock_id' => $id, 'MobileUnlockSales.refund_status' => 0)));
						//pr($originalSale_query);die;
						$originalSale_query = $originalSale_query->hydrate(false);
						if(!empty($originalSale_query)){
							$originalSale = $originalSale_query->toArray();
						}else{
							$originalSale = array();
						}
						
						//pr($originalSale);die;
						$originalRefund = array();
						//checking if an entry with refund_status = 1 already exists
						$checkOriginalRefund_query = $this->MobileUnlockSales->find('all', array('conditions' => array('MobileUnlockSales.mobile_unlock_id' => $id, 'MobileUnlockSales.refund_status' => 1)));
						$checkOriginalRefund = $checkOriginalRefund_query->count();
						
						if($checkOriginalRefund == 0){
							//inserting a new row for refund
							$rfndAmount = -$originalSale['amount'];
							$rfndOn = date('Y-m-d G:i:s A');
							$orgnlSaleId = $originalSale['id'];
							$refundRow = array(
								'kiosk_id' => $originalSale['kiosk_id'],
								'mobile_unlock_id' => $originalSale['mobile_unlock_id'],
								'sold_by' => $originalSale['sold_by'],
								'sold_on' => $originalSale['sold_on'],
								'refund_by' => $originalSale['sold_by'],
								'amount' => 0,
								'refund_amount' => $rfndAmount,
								'refund_status' => 1,
								'refund_on' => $rfndOn,
								'refund_remarks' => 'Unprocessed Unlock'
							);
							
							$MobileUnlockSalesEntity = $this->MobileUnlockSales->newEntity($refundRow,['validate' => false]);
							$MobileUnlockSalesEntity = $this->MobileUnlockSales->patchEntity($MobileUnlockSalesEntity,$refundRow,['validate' => false]);
							if($this->MobileUnlockSales->save($MobileUnlockSalesEntity)){
								//updating the original row
								$this->MobileUnlockSales->updateAll(array('refund_status' => "'0'", 'status' => "'1'", 'refund_remarks' => "'Unprocessed Unlock'", 'refund_amount' => "'$rfndAmount'", 'refund_on' => "'$rfndOn'"),array('MobileUnlockSale.id' => $orgnlSaleId));
								$this->MobileUnlock->updateAll(array('status_refund' => "'1'"),array('MobileUnlock.id' => $id));
							}
						}
			//CASE V: Ends here
					}elseif($this->request->data['MobileUnlock']['status'] == UNLOCKED_CONFIRMATION_PASSED){
			//CASE VI: ***status update to unlocked :confirmation passed to customer from here
						//getting the orignal sale details from here
						$originalSale_query = $this->MobileUnlockSales->find('all', array('conditions' => array('MobileUnlockSales.mobile_unlock_id' => $id, 'MobileUnlockSales.refund_status' => 0)));
						$originalSale_query = $originalSale_query->hydrate(false);
						if(!empty($originalSale_query)){
							$originalSale = $originalSale_query->first();
						}else{
							$originalSale = array();
						}
						$orgnlSaleId = $originalSale['id'];
						
						//removing the refund figures from the original sale row
						if($this->MobileUnlockSales->updateAll(array('status' => "'0'", 'refund_remarks' => NULL, 'refund_amount' => NULL, 'refund_on' => NULL, 'refund_by' => NULL),array('MobileUnlockSales.id' => $orgnlSaleId))){
							$MobileUnlockSale_query = "DELETE FROM `mobile_unlock_sales` WHERE `mobile_unlock_id` = '$id' AND `refund_status` = '1'";
							$conn = ConnectionManager::get('default');
							$stmt = $conn->execute($MobileUnlockSale_query); 
							$this->MobileUnlocks->updateAll(array('status_refund' => NULL),array('MobileUnlock.id' => $id));
						}
			//CASE VI: Ends here
					}
				}elseif(count($this->request->data) == 0 && is_array($this->request->Session()->read('manager_edit_data')) && $this->request->Session()->read('manager_edit_data.MobileUnlock.id') == $id){
					//condition: when request is coming from payment screen
			//CASE I, CASE III continues from here
					//check if sale exists for this id
					//if not insert a new sale
					$managerEditSession = $this->request->Session()->read('manager_edit_data');
					$checkIfSaleExists_query = $this->MobileUnlockSales->find('all', array('conditions' => array( 'MobileUnlockSales.mobile_unlock_id' => $id)));
					$checkIfSaleExists = $checkIfSaleExists_query->count();
					//pr($dataPerId);
					//pr($this->Session->read());die;
					//if($checkIfSaleExists == 0){
						//insert sale
						$countSale = 0;
						if($managerEditSession['MobileUnlock']['status'] == VIRTUALLY_BOOKED && $checkIfSaleExists == 0){
							//echo'hi';die;
							$mobileUnlockSalesData = array(
								'kiosk_id' => $managerEditSession['MobileUnlock']['kiosk_id'],
								'mobile_unlock_id' => $id,
								'sold_by' => $managerEditSession['MobileUnlock']['booked_by'],
								'sold_on' => $managerEditSession['MobileUnlock']['created'],
								'refund_by' => '',
								'amount' => $managerEditSession['MobileUnlock']['estimated_cost'],
								'refund_amount' => '',
								'refund_status' => 0,
								'refund_on' => '',
								'refund_remarks' => ''
							);
							$MobileUnlockSalesEntity = $this->MobileUnlockSales->newEntity($mobileUnlockSalesData,['validate' => false]);
							$MobileUnlockSalesEntity = $this->MobileUnlockSales->patchEntity($MobileUnlockSalesEntity,$mobileUnlockSalesData,['validate' => false]);
							if($this->MobileUnlockSales->save($MobileUnlockSalesEntity)){
								$countSale++;
							}
						}elseif(($managerEditSession['MobileUnlock']['status'] == DELIVERED_UNLOCKED_BY_CENTER || $managerEditSession['MobileUnlock']['status'] == DELIVERED_UNLOCKED_BY_KIOSK) && $checkIfSaleExists > 0){
							$saleInfo_query = $this->MobileUnlockSales->find('first', array('conditions' => array( 'MobileUnlockSales.mobile_unlock_id' => $id, 'MobileUnlockSales.status' => 0)));
							$saleInfo_query = $saleInfo_query->hydrate(false);
							if(!empty($saleInfo_query)){
								$saleInfo = $saleInfo_query->first();
							}else{
								$saleInfo = array();
							}
							$sale_ide = $saleInfo['id'];
							$MobileUnlockSalesEntity = $this->MobileUnlockSales->get($sale_ide);
							$data = array('amount' => $managerEditSession['MobileUnlock']['estimated_cost']);
							$MobileUnlockSalesEntity = $this->MobileUnlockSales->patchEntity($MobileUnlockSalesEntity,$data,['validate' => false]);
							if($this->MobileUnlockSale->save($MobileUnlockSalesEntity)){
								//updating the sale id in payment
								//$this->UnlockPayment->updateAll(array('mobile_unlock_sale_id' => "'$sale_ide'"),array('UnlockPayment.mobile_unlock_id' => $id));
								$query = "UPDATE `unlock_payments` SET `mobile_unlock_sale_id` = '$sale_ide' WHERE `mobile_unlock_id` = $id";
								$conn = ConnectionManager::get('default');
								$stmt = $conn->execute($query); 

								//-------------------------adding new code Aug 25----------------
								$paymentDetails_query = $this->UnlockPayments->find('all', array(
													'conditions' => array('UnlockPayments.mobile_unlock_sale_id' => $id),
																));
								$paymentDetails_query = $paymentDetails_query->hydrate(false);
								if(!empty($paymentDetails_query)){
									$paymentDetails = $paymentDetails_query->toArray();
								}else{
									$paymentDetails = array();
								}
								if(empty($paymentDetails)){
									//delete sale and redirect to index page
									$MobileUnlockSalesEntity = $this->MobileUnlockSales->get($id);
									$this->MobileUnlockSales->delete($MobileUnlockSalesEntity);
									$alterQuery = "ALTER TABLE `mobile_unlock_sales` AUTO_INCREMENT = $id";
									
									$conn = ConnectionManager::get('default');
									$stmt = $conn->execute($query); 
									
									$this->Flash->session("Failed to fire query: $query. <br/>For this reason generated sale delelted for Mobile Repair Sale ID: {$id} and receipt counter is again set to $id for maintaining sequences<br/>Please take screenshot of this bug and report to admin");
									return $this->redirect(array('action' => 'index'));
								}
								//---------------------------------------------------------------
								$countSale++;
							}
						}
							
						if($countSale > 0){
							//assigning the value of session to $this->request->data
							$this->request->data = $managerEditSession;
							
							//deleting the session
							$this->request->Session()->delete('manager_edit_data');
							
							$unlockId = $this->request['data']['MobileUnlock']['id'];
							$estimateCost = $this->request['data']['MobileUnlock']['estimated_cost'];
							$updatedStatus = $this->request['data']['MobileUnlock']['status'];
							$kiosk_id = $this->request['data']['MobileUnlock']['kiosk_id'];
							//kiosk id of the original kiosk
						}else{
							//$this->MobileUnlockSale->set($mobileUnlockSalesData);
							//if (!$this->MobileUnlockSale->validates()) {
							//	pr($errors = $this->MobileUnlockSale->validationErrors);
							//}
							//die;
							//pr($mobileUnlockSalesData);die;
							$this->Flash->error("Sale could not be saved for unlock id $id!");
							return $this->redirect(array('action' => 'manager_edit', $id));
							die;
						}
					//}
					
				}
			//CASE I, CASE III: Ends here ****
				//die;
				$MobileUnlocksEntity = $this->MobileUnlocks->get($unlockId);
				$data = array('status' => $updatedStatus);
				$MobileUnlocksEntity = $this->MobileUnlocks->patchEntity($MobileUnlocksEntity,$data,['validate' => false]);
				if($this->MobileUnlocks->save($MobileUnlocksEntity)){
					$MobileUnlocksEntity1 = $this->MobileUnlocks->get($unlockId);
					$dat_to_save = array('estimated_cost' => $estimateCost);
					$MobileUnlocksEntity1 = $this->MobileUnlocks->patchEntity($MobileUnlocksEntity1,$dat_to_save,['validate' => false]);
					$this->MobileUnlocks->save($MobileUnlocksEntity1);
					$mobileUnlockLogsData = array(
					'kiosk_id' => $kiosk_id,
					'user_id' => $this->request->Session()->read('Auth.User.id'),
					'mobile_unlock_id' => $unlockId,					
					'unlock_status' => $updatedStatus
					);			
									
					$MobileUnlockLogsEntity = $this->MobileUnlockLogs->newEntity($mobileUnlockLogsData,['validate' => false]);
					$MobileUnlockLogsEntity = $this->MobileUnlockLogs->patchEntity($MobileUnlockLogsEntity,$mobileUnlockLogsData,['validate' => false]);
					$this->MobileUnlockLogs->save($MobileUnlockLogsEntity);
					
				//************code for sending email starts from here
					$countryOptions = Configure::read('uk_non_uk');
					$unlock_email_message = $this->setting['unlock_email_message'];
					$kioskaddress_query = $this->Kiosks->find('all',array(
						'fields' => array('Kiosks.address_1','Kiosks.address_2', 'Kiosks.city','Kiosks.state','Kiosks.country','Kiosks.zip','Kiosks.contact' ),
						'conditions'=> array('Kiosks.id' => $kiosk_id)
						)
					);
					
					$kioskaddress_query = $kioskaddress_query->hydrate(false);
					if(!empty($kioskaddress_query)){
						$kioskaddress = $kioskaddress_query->first();
					}else{
						$kioskaddress = array();
					}
					
					$brands_query = $this->MobileUnlocks->Brands->find('list', array(
									 'fields' => array('id', 'brand'),
									 'order'=>'brand asc',
									 'conditions' => array('Brands.status' => 1,
											       'Brands.id' => $dataPerId['brand_id'])
									));
					
					$brands_query = $brands_query->hydrate(false);
					if(!empty($brands_query)){
						$brands = $brands_query->toArray();
					}else{
						$brands = array();
					}
					
					$mobileModels_query = $this->MobileUnlocks->MobileModels->find('list',array(
									    'fields' => array('id', 'model'),
										'order'=>'model asc',
									    'conditions' => array(
												  'MobileModels.id' => $dataPerId['mobile_model_id']
												  )
									    )
							       );
					
					$mobileModels_query = $mobileModels_query->hydrate(false);
					if(!empty($mobileModels_query)){
						$mobileModels = $mobileModels_query->toArray();
					}else{
						$mobileModels = array();
					}
					
					$unlockBookingData = $dataPerId;
					//pr($unlockBookingData);die;
					$codeClause = "";
					$unlockCodeInstructions = '';
					if(!empty($unlockBookingData['code']) && $unlockBookingData['code'] != NULL){
						$codeClause = "Your unlock code is ".$unlockBookingData['code'].". <br/><br/>";
					}
					$unlockCodeInstructions = '';
					if(!empty($unlockBookingData['unlock_code_instructions']) && $unlockBookingData['unlock_code_instructions'] != NULL){
						$unlockCodeInstructions = "Unlock Instructions:<br/>".$unlockBookingData['unlock_code_instructions'].".<br/><br/><span style='color: red;'>**PLEASE READ: Please do not attempt if you are unsure about the above procedure. As incorrect procedure may lock your phone permanently!</span><br/><br/>";
					}
					
					$unlockBookingData['mobile_model_id'] = $mobileModelId;
					$unlockBookingData['unlocking_days'] = $unlockingDays;
					//$unlockStatus = $unlockBookingData['status'];
					$unlockStatus = $this->request->data['MobileUnlock']['status'];
					$kioskContact = $kioskaddress['contact'];
					$messageStatement = '';
					$currency = $this->setting['currency_symbol'];
					
					switch($unlockStatus){
						case VIRTUALLY_BOOKED:
							$unlockStatusStatement = "The unlock has been booked for your unlock id ".$id."and Mobile Model:".$mobileModels[$unlockBookingData['mobile_model_id']]." phone &#40;IMEI: ".$unlockBookingData['imei']."&#41;. Your unlock is expected to get done within ".$unlockBookingData['unlocking_days']." working day&#40;s&#41;.<br/><br/>";
							$messageStatement = "Your phone unlock id ".$id." has been booked and will be unlocked within ".$unlockBookingData['unlocking_days']." working days. Please contact the kiosk with any queries t&s ".$this->setting['repair_unlock_terms_link'];
							break;
						
						case BOOKED:
							$unlockStatusStatement = "The unlock has been booked for your unlock id ".$id."and Mobile model:".$mobileModels[$unlockBookingData['mobile_model_id']]." phone &#40;IMEI: ".$unlockBookingData['imei']."&#41;. Your unlock is expected to get done within ".$unlockBookingData['unlocking_days']." working day&#40;s&#41;.<br/><br/>";
							$messageStatement = "Your phone unlock id ".$id." has been booked and will be unlocked within ".$unlockBookingData['unlocking_days']." working days. Please contact the kiosk with any queries t&s ".$this->setting['repair_unlock_terms_link'];
							break;
					
						case DISPATCHED_2_CENTER:
							$unlockStatusStatement = "your unlock id ".$id."and Mobile model:".$mobileModels[$unlockBookingData['mobile_model_id']]." phone &#40;IMEI: ".$unlockBookingData['imei']."&#41; has been sent to the specialized team for unlock. We will keep you posted with the updates. Your unlock is expected to get done within ".$unlockBookingData['unlocking_days']." working day&#40;s&#41;.<br/><br/>";
							break;
						
						case UNLOCK_FAILED_CONFIRMATION_SENT_2_KIOSK:
							$unlockStatusStatement = "The unlock for your unlock id ".$unlockBookingData['id']."and Mobile model:".$mobileModels[$unlockBookingData['mobile_model_id']]." phone &#40;IMEI: ".$unlockBookingData['imei']."&#41; could not be processed. Please get in touch with the Kiosk for any query.<br/><br/>";
							$messageStatement = "Your phone unlock id ".$unlockBookingData['id']." is un-successful. Please contact ".$kioskContact." for refund t&s ".$this->setting['repair_unlock_terms_link'];
							break;
						
						case RECEIVED_UNLOCKED_FROM_CENTER:
							$unlockStatusStatement = "your unlock id ".$unlockBookingData['id']."and Mobile model:".$mobileModels[$unlockBookingData['mobile_model_id']]." phone &#40;IMEI: ".$unlockBookingData['imei']."&#41; has been successfully unlocked. Please contact ".$kiosks[$unlockBookingData['kiosk_id']]." before collecting the phone.<br/><br/>Thank you for using our Mobile Unlocking services.<br/><br/>{$codeClause}{$unlockCodeInstructions}";
							if($checkIfVirtuallyBooked == 0){
								$messageStatement = "Your phone unlock id ".$unlockBookingData['id']." successfully unlocked is ready for collection. Please contact ".$kioskContact." before collection t&s ".$this->setting['repair_unlock_terms_link'];
							}
							break;
						
						case RECEIVED_UNPROCESSED_FROM_CENTER:
							$unlockStatusStatement = "The unlock for your unlock id".$unlockBookingData['id']."and Mobile model:".$mobileModels[$unlockBookingData['mobile_model_id']]." phone &#40;IMEI: ".$unlockBookingData['imei']."&#41; could not be processed. Please get in touch with the Kiosk for any query.<br/><br/>";
							if($checkIfVirtuallyBooked == 0){
								$messageStatement = "Your phone unlock id ".$unlockBookingData['id']." un-successful is ready for collection. Please contact ".$kioskContact." before collection t&s ".$this->setting['repair_unlock_terms_link'];
							}
							break;
						
						case UNLOCK_PROCESSED_CONFIRMATION_SENT_2_KIOSK:
							$unlockStatusStatement = "The unlock for your unlock id".$unlockBookingData['id']."and Mobile model:".$mobileModels[$unlockBookingData['mobile_model_id']]." phone &#40;IMEI: ".$unlockBookingData['imei']."&#41; has been successfully processed. Please get in touch with the Kiosk for any query.<br/><br/>{$codeClause}{$unlockCodeInstructions}";
							$messageStatement = "Your phone unlock id ".$unlockBookingData['id']." successfully unlocked. Please contact ".$kioskContact." for guidance t&s ".$this->setting['repair_unlock_terms_link'];
							break;
						
						case UNLOCKED_CONFIRMATION_PASSED:
							$unlockStatusStatement = "The unlock has been successfully processed for your unlock id".$unlockBookingData['id']."and Mobile model:".$mobileModels[$unlockBookingData['mobile_model_id']]." phone &#40;IMEI: ".$unlockBookingData['imei']."&#41;. Please get in touch with the Kiosk for any query.<br/><br/>{$codeClause}{$unlockCodeInstructions}";
							break;
					
						case UNLOCKING_FAILED_CONFIRMATION_PASSED:
							$unlockStatusStatement = "The unlock for your unlock id".$unlockBookingData['id']."and Mobile model:".$mobileModels[$unlockBookingData['mobile_model_id']]." phone &#40;IMEI: ".$unlockBookingData['imei']."&#41; could not be processed and a refund of ".$currency.$unlockBookingData['estimated_cost']." has been made. We regret for the inconvenience.<br/><br/>";
							break;
					
						case DELIVERED_UNLOCKED_BY_CENTER://
							$unlockStatusStatement = "Your unlock id".$unlockBookingData['id']."and Mobile model:".$mobileModels[$unlockBookingData['mobile_model_id']]." phone &#40;IMEI: ".$unlockBookingData['imei']."&#41; has been successfully unlocked and delivered to you.<br/><br/>Thank you for using our Mobile Unlocking services.<br/><br/>{$codeClause}{$unlockCodeInstructions}";
							$messageStatement = "Thank you for collecting your un-locked phone id ".$unlockBookingData['id'].". Thank you for using our service t&s ".$this->setting['repair_unlock_terms_link'];
							break;
					
						case DELIVERED_UNLOCKING_FAILED_AT_CENTER:
							$unlockStatusStatement = "The unlock for your unlock id".$unlockBookingData['id']."and Mobile model:".$mobileModels[$unlockBookingData['mobile_model_id']]." phone &#40;IMEI: ".$unlockBookingData['imei']."&#41; could not be processed and has been delivered back to you. We regret for the inconvenience.<br/><br/>";
							$messageStatement = "Thank you for collecting your phone id ".$unlockBookingData['id'].". We are sorry we could not unlock this phone t&s ".$this->setting['repair_unlock_terms_link'];
							break;
					
						case DELIVERED_UNLOCKED_BY_KIOSK://
							$unlockStatusStatement = "Your unlock id".$unlockBookingData['id']."and Mobile model:".$mobileModels[$unlockBookingData['mobile_model_id']]." phone &#40;IMEI: ".$unlockBookingData['imei']."&#41; has been successfully unlocked and delivered to you.<br/><br/>Thank you for using our Mobile Unlocking services.<br/><br/>{$codeClause}{$unlockCodeInstructions}";
							$messageStatement = "Thank you for collecting your un-locked phone id ".$unlockBookingData['id'].". Thank you for using our service t&s ".$this->setting['repair_unlock_terms_link'];
							break;
					
						case DELIVERED_UNLOCKING_FAILED_AT_KIOSK:
							$unlockStatusStatement = "The unlock for your unlock id".$unlockBookingData['id']."and Mobile model:".$mobileModels[$unlockBookingData['mobile_model_id']]." phone &#40;IMEI: ".$unlockBookingData['imei']."&#41; could not be processed and has been delivered back to you. We regret for the inconvenience.<br/><br/>";
							$messageStatement = "Thank you for collecting your phone id ".$unlockBookingData['id'].". We are sorry we could not unlock this phone t&s ".$this->setting['repair_unlock_terms_link'];
							break;
					}
					$send_by_email = Configure::read('send_by_email');
					$emailSender = Configure::read('EMAIL_SENDER');
					if(!empty($unlockStatusStatement)){
						if($this->request->session()->read('Auth.User.group_id') == MANAGERS || $this->request->session()->read('Auth.User.group_id') == ADMINISTRATORS || $this->request->session()->read('Auth.User.group_id') == FRANCHISE_OWNER){
								if($this->request['data']['MobileUnlock']['send'] == '1'){
									if(!empty($dataPerId['MobileUnlock']['customer_contact'])){
										$destination = $dataPerId['MobileUnlock']['customer_contact'];
										if(!empty($messageStatement)){
											$this->TextMessage->test_text_message($destination, $messageStatement);
										}
									}
									if(!empty($emailTo)){
										$Email = new Email();
										$Email->config('default');
										$Email->viewVars(array('unlockBookingData' => $unlockBookingData,'unlockStatus' => $unlockStatus,'mobileModels' => $mobileModels, 'kiosks' => $kiosks, 'unlockStatusStatement' => $unlockStatusStatement, 'kiosk_id' => $kiosk_id, 'brands' => $brands,'kioskaddress' => $kioskaddress,'countryOptions' => $countryOptions, 'unlock_email_message' => $unlock_email_message));
										//$Email->config(array('from' => 'me@example.org', 'transport' => 'MyCustom'));
										//$Email = new CakeEmail(array('from' => 'me@example.org', 'transport' => 'MyCustom'));
										$emailTo = $unlockBookingData['customer_email'];
										$Email->template('unlock_booking_receipt');
										$Email->emailFormat('both');
										$Email->to($emailTo);
										 $Email->transport(TRANSPORT);
										$Email->from([$send_by_email => $emailSender]);
										//$Email->sender("sales@oceanstead.co.uk");
										$Email->subject('Mobile Unlock Details');
										$Email->send();	
									}
									
								}
						}else{
								if(!empty($dataPerId['MobileUnlock']['customer_contact'])){
									$destination = $dataPerId['MobileUnlock']['customer_contact'];
									if(!empty($messageStatement)){
										$this->TextMessage->test_text_message($destination, $messageStatement);
									}
								}
								if(!empty($emailTo)){
									$Email = new Email();
									$Email->config('default');
									$Email->viewVars(array('unlockBookingData' => $unlockBookingData,'unlockStatus' => $unlockStatus,'mobileModels' => $mobileModels, 'kiosks' => $kiosks, 'unlockStatusStatement' => $unlockStatusStatement, 'kiosk_id' => $kiosk_id, 'brands' => $brands,'kioskaddress' => $kioskaddress,'countryOptions' => $countryOptions, 'unlock_email_message' => $unlock_email_message));
									//$Email->config(array('from' => 'me@example.org', 'transport' => 'MyCustom'));
									//$Email = new CakeEmail(array('from' => 'me@example.org', 'transport' => 'MyCustom'));
									$emailTo = $unlockBookingData['customer_email'];
									$Email->template('unlock_booking_receipt');
									$Email->emailFormat('both');
									$Email->to($emailTo);
									 $Email->transport(TRANSPORT);
									$Email->from([$send_by_email => $emailSender]);
									//$Email->sender("sales@oceanstead.co.uk");
									$Email->subject('Mobile Unlock Details');
									$send_mail = 0;
									if($send_mail == 1){
										$Email->send();
									}
								}
								
						}
	
					}
				//code for sending email ends here ******************
				
				}
				$this->Flash->success("Status has been successfully updated for unlock id:$unlockId");
				return $this->redirect(array('action'=>'index'));
			}		
			$this->set(compact('comments','users','kiosks','unlockLogs','unlockingDays'));
		}else{
			$this->Flash->error("Only Manager/Admin can access this page");
			return $this->redirect(array('action'=>'index'));
		}
	}
    
    public function exportUserUnlock(){
		$finalArr = array();
		$unlockStatusTechnicianOptions = Configure::read('unlock_statuses_technician');
		//$problemTypeOptions = $this->ProblemType->find('list',array('fields' => array('id', 'problem_type')));
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
							"DATE(MobileUnlockLogs.created) > '$startDate'" ,
							"DATE(MobileUnlockLogs.created) < '$endDate'",
							'MobileUnlockLogs.unlock_status IN' => array(DISPATCHED_2_KIOSK_UNLOCKED, DISPATCHED_2_KIOSK_UNPROCESSED,UNLOCK_PROCESSED_CONFIRMATION_SENT_2_KIOSK,UNLOCK_FAILED_CONFIRMATION_SENT_2_KIOSK)
						);
		if(empty($userID) && empty($kioskID) && empty($serviceCenter)){
			;
		}
		if(!empty($userID)){
			$condArr[]['MobileUnlockLogs.user_id'] = $userID;
		}
		if(!empty($kioskID)){
			$condArr[]['MobileUnlockLogs.kiosk_id'] = $kioskID;
		}
		if(!empty($serviceCenter)){
			$condArr[]['MobileUnlockLogs.service_center_id'] = $serviceCenter;
		}
		$unlockData_query = $this->MobileUnlockLogs->find('all', array('conditions' => $condArr));
        $unlockData_query = $unlockData_query->hydrate(false);
        if(!empty($unlockData_query)){
            $unlockData = $unlockData_query->toArray();
        }else{
            $unlockData = array();
        }
		
		foreach($unlockData as $key => $unlockLog){
			$userUnlockIds[$unlockLog['user_id']][] = $unlockLog['mobile_unlock_id'];
		}
		
		$unlockDetail = array();
		foreach($userUnlockIds as $user_id => $userUnlockArr){
			if(empty($userUnlockArr)){
                $userUnlockArr = array(0 => null);
            }
            $unlockDetail_query = $this->MobileUnlocks->find('all',array('conditions'=>array('MobileUnlocks.id IN'=>$userUnlockArr),'fields'=>array('id','brand_id','mobile_model_id','network_id','net_cost')));
            $unlockDetail_query = $unlockDetail_query->hydrate(false);
            if(!empty($unlockDetail_query)){
                $unlockDetail[$user_id] = $unlockDetail_query->toArray();
            }else{
                $unlockDetail[$user_id] = array();
            }
			if(empty($userUnlockArr)){
                $userUnlockArr = array(0 => null);
            }
			$data_query = $this->MobileUnlockSales->find('all',array('conditions'=>array('MobileUnlockSales.mobile_unlock_id IN'=>$userUnlockArr)));
            $data_query = $data_query->hydrate(false);
            if(!empty($data_query)){
                $data[] = $data_query->toArray();
            }else{
                $data[] = array();
            }
		}
		$sale_Arr = array();
		foreach($data[0] as $key => $value){
			//pr($value);die;
			if(array_key_exists($value['mobile_unlock_id'],$sale_Arr)){
				if($value['amount'] == 0){
					continue;
				}
				$sale_Arr[$value['mobile_unlock_id']] = array(
                                                                'amount' => $value['amount'],
                                                                'refund_amount' => $value['refund_amount'],
                                                            );
			}else{
				$sale_Arr[$value['mobile_unlock_id']] = array(
								'amount' => $value['amount'],
								'refund_amount' => $value['refund_amount'],
								);
			}
		}
		//pr($sale_Arr);
		//die;
		foreach($unlockDetail[$user_id] as $k => $unlockDet){
            //pr($unlockDet);die;
			$mobile_model_ids[$unlockDet['mobile_model_id']] = $unlockDet['mobile_model_id'];
			$network_ids[$unlockDet['network_id']] = $unlockDet['network_id'];
		}
		
		if(empty($mobile_model_ids)){
            $mobile_model_ids = array(0 => null);
        }
        $mobileModels_query = $this->MobileModels->find('list',[
                                                            'conditions' => ['MobileModels.id In' => $mobile_model_ids],
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
		if(empty($network_ids)){
            $network_ids = array(0 => null);
        }
        $networks_query = $this->Networks->find('list',[
                                                    'conditions' => [
                                                                        'Networks.id IN' => $network_ids
                                                                    ],
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
		$cost = 0;
		foreach($unlockData as $key => $unlockInfo){
			$amount = $refundAmt =0;
			if(array_key_exists($unlockInfo['mobile_unlock_id'],$sale_Arr)){
				$amount = $sale_Arr[$unlockInfo['mobile_unlock_id']]['amount'];
				$refundAmt = $sale_Arr[$unlockInfo['mobile_unlock_id']]['refund_amount'];
				if($refundAmt > 0){
					$refundAmt = (-1)*$refundAmt;
				}
			}
			
            if(array_key_exists($userID, $unlockDetail) &&
                is_array($unlockDetail[$userID])){
                $unlock_detail = array_values($unlockDetail);
                foreach($unlock_detail[0] as $k => $unlockDet){
                    if($unlockDet['id'] == $unlockInfo['mobile_unlock_id']){
                        $model = $mobileModels[$unlockDet['mobile_model_id']];
                        $network = $networks[$unlockDet['network_id']];
						$cost = $unlockDet['net_cost'];
                    }
                }
            }else{
                $model = '--';
                $network = '--';
            }
            if(!empty($unlockInfo['unlock_center_id']) && array_key_exists($unlockInfo['unlock_center_id'],$kiosks)){
                $unlockCenter = $kiosks[$unlockInfo['unlock_center_id']];
            }else{
                $unlockCenter = '--';
            }
			
			$finalArr[] = array(
								'Dispatch Date' => date("Y-m-d h:i:s",strtotime($unlockInfo['created'])),
								'UnlockId' => $unlockInfo['mobile_unlock_id'],
								'Cost' => $cost,
								'Selling Price' => $amount,
								'refund' => $refundAmt,
								'kiosk_id' => $kiosks[$unlockInfo['kiosk_id']],
								'Model' => $model,
								'Network' => $network,
								'unlock_status' => $unlockStatusTechnicianOptions[$unlockInfo['unlock_status']],
								);
			
			
		}
		
		$this->outputCsv('MobileUnlock_'.time().".csv" ,$finalArr);
		$this->autoRender = false;
	}
	public function delete($id = null) {
		if (!$this->MobileUnlocks->exists($id)) {
			throw new NotFoundException(__('Invalid mobile unlock'));
		}
		$getId = $this->MobileUnlocks->get($id);
        $sale_query = $this->MobileUnlockSales->find('all',array('fields' => array('id','mobile_unlock_id'),'conditions' => array('mobile_unlock_id' => $id)));
       // pr($sale_query);die;
        $sale_query = $sale_query->hydrate(false);
        if(!empty($sale_query)){
            $sale_data = $sale_query->first();
        }else{
            $sale_data = array();
        }
        if(!empty($sale_data)){
            $sales_id = $sale_data['id'];
        }else{
            $sales_id = "";
        }
        
        $payment_query = $this->UnlockPayments->find('all',array('fields' => array('id','mobile_unlock_id'),'conditions' => array('mobile_unlock_id' => $id)));
        $payment_query = $payment_query->hydrate(false);
        if(!empty($payment_query)){
            $payment_data = $payment_query->first();
        }else{
            $payment_data = array();
        }
        if(!empty($payment_data)){
            $payment_id = $payment_data['id'];
        }else{
            $payment_id = "";
        }
        
        
        
		$this->request->allowMethod('post', 'delete');
		
		if ($this->request->session()->read('Auth.User.group_id') == ADMINISTRATORS || $this->request->session()->read('Auth.User.group_id') == FRANCHISE_OWNER || $this->request->session()->read('Auth.User.group_id') == MANAGERS){
			if ($this->MobileUnlocks->delete($getId) ) {
                if(!empty($sales_id)){
                    $getId_sale = $this->MobileUnlockSales->get($sales_id);
                    if($this->MobileUnlockSales->delete($getId_sale)){
                        if(!empty($payment_id)){
                            $getId_payment =$this->UnlockPayments->get($payment_id);
                            if($this->UnlockPayments->delete($getId_payment)){
                                $this->Flash->success(__('The mobile unlock has been deleted.'));   
                            }
                        }
                    }
                }else{
                    $this->Flash->success(__('The mobile unlock has been deleted.'));   
                }
			} else {
				$this->Flash->error(__('The mobile unlock could not be deleted. Please, try again.'));
			}
		} else {
			$this->Flash->error(__('The mobile unlock could not be deleted. Please, try again.'));
		}
		return $this->redirect(array('action' => 'index'));
	}
	
	public function unlockPayment(){
		//pr($this->Session->read('received_reprd_from_tech_data'));
		//$this->RepairPayment->query('TRUNCATE `repair_payments`');
		//if(AuthComponent::user('group_id') != KIOSK_USERS){
		//	$this->Session->setFlash('Only kiosk user can authorize/enter payment');
		//	return $this->redirect(array('action' => 'index'));
		//} //commented on 01.02.2016 as we are now accepting payment on manager edit from any status to virtual
		//pr($this->RepairPayment->find('all'));
		$paymentType = array('Cash' => 'Cash', 'Card' => 'Card');
		$this->set(compact('paymentType'));
		$sessionBskt = array();
		
		if(is_array($this->request->Session()->read('unlock_data_session'))){
			//case for normal edit
			$kioskId = $this->request->Session()->read('kiosk_id');
			$basket = "unlock_data_session";
			$session_basket = $this->request->Session()->read('unlock_data_session');
			$userId = $this->Auth->user('id');
			$sessionUnlockId = $session_basket['MobileUnlock']['id'];
			$redirect = array('controller'=>'mobile_unlocks','action'=>'edit',$sessionUnlockId);//for redirection
			$sessionBskt['unlock_payment_status'] = $sessionUnlockId;//for sending identification of successfull payment
		}elseif(is_array($this->request->Session()->read('manager_edit_data'))){
			//case for manager edit
			$basket = "manager_edit_data";
			$session_basket = $this->request->Session()->read('manager_edit_data');
			$userId = $session_basket['MobileUnlock']['booked_by'];
			$kioskId = $session_basket['MobileUnlock']['kiosk_id'];
			$sessionUnlockId = $session_basket['MobileUnlock']['id'];
			$redirect = array('controller'=>'mobile_unlocks','action'=>'manager_edit',$sessionUnlockId);
			$sessionBskt['unlock_payment_status'] = $sessionUnlockId."_manager_edit";//for sending identification of successfull payment
		}else{
			return $this->redirect(array('action' => 'index'));
			die;
		}
		
		if ($this->request->is(array('post', 'put'))) {
			if(array_key_exists('cancel',$this->request->data)){
				$this->request->Session()->delete($basket);
				return $this->redirect($redirect);
				die;
			}
			$amountToPay = $this->request['data']['final_amount'];
			$totalPaymentAmount = 0;
			$amountDesc = array();
			$error = '';
			$errorStr = '';
			
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
				$this->Flash->error("$errorStr");
				return $this->redirect(array('action'=>'unlock_payment'));
			}
			
			$counter = 0;
			foreach($this->request['data']['Payment']['Amount'] as $key => $paymentAmount){
				$paymentMethod = $this->request['data']['Payment']['Payment_Method'][$key];
				$paymentDescription = $this->request['data']['Payment']['Description'][$key];
				$payment_status = 1;//since we do not have option for credit here, so just sending status 1 as payment done
				
				if(!empty($paymentAmount)){// && $paymentDescription
					$paymentDetailData = array(
							'kiosk_id' => $kioskId,
							'user_id' => $userId,
							'mobile_unlock_id' => $sessionUnlockId,
							'payment_method' => $paymentMethod,
							'description' => $paymentDescription,
							'amount' => $paymentAmount,
							'payment_status' => $payment_status,
							'status' => 1,//this 1 currently does not have any relevance
							   );
					$newEntity = $this->UnlockPayments->newEntity();
					$patchEntity = $this->UnlockPayments->patchEntity($newEntity,$paymentDetailData);
					if($this->UnlockPayments->save($patchEntity)){
						$counter++;
						//here we are sending payment status in session to unlock edit as an identifier for successful payment
						$this->request->Session()->write('unlock_payment_confirmation',$sessionBskt);
					}
				}
			}
			if($counter>0){
				return $this->redirect($redirect);
			}else{
				$flashMessage = ("Sale could not be created. Please try again");
				$this->request->Session()->delete($basket);
				$this->Flash->success($flashMessage);
				return $this->redirect($redirect);
			}
		}
	}
}
