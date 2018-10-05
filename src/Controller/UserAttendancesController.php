<?php
namespace App\Controller;
use Cake\Core\Configure;
use Cake\Core\Configure\Engine\PhpConfig; 
use App\Controller\AppController;
use Cake\Datasource\ConnectionManager;
use Cake\Mailer\Email;
class UserAttendancesController extends AppController
{

     public $helpers = [
         'Paginator' => ['templates' => 'paginatortemplates']
         ];
	 public function initialize(){
        parent::initialize();
        $this->set(compact('paymentType'));
        $this->loadComponent('ScreenHint');
        $this->loadModel('ProductReceipts');
        $this->loadModel('Customers');
        $this->loadModel('PaymentDetails');
        $this->loadModel('Users');
        $this->loadModel('kiosks');
		$this->loadModel('KioskProductSales');
        $this->loadModel('Categories');
    }
	
	public function index()
	{
	    $userArr = array();
	    $this->loadModel('Users');
		 
		   $id = $this->request->session()->read('Auth.User.id');
		   $all_ids = $this->getChildren($id);
		   if(!empty($all_ids)){
			  $all_ids_str = implode(",",$all_ids);
		   }else{
			  $all_ids_str = "";
		   }
		if($this->request->session()->read('Auth.User.group_id') == ADMINISTRATORS){
		   $this->paginate = array(
			 'fields'=>array('id','username'),
			 'order' => array('username' => 'asc'),
			 'recursive' => -1
			);
		}else{
		  if(!empty($all_ids_str)){
			   $this->paginate = array(
					'conditions' => array('id IN' => $all_ids),
			 'fields'=>array('id','username'),
			 'order' => array('username' => 'asc'),
			 'recursive' => -1
			);	   
		  }else{
			   $this->paginate = array(
			 'fields'=>array('id','username'),
			 'order' => array('username' => 'asc'),
			 'recursive' => -1
			);
		  }
		}
		
		if(SPECIAL_USER == 0){
			if(!array_key_exists('conditions',$this->paginate)){
				$this->paginate['conditions']['username NOT LIKE'] = 'dr5%';
			}else{
				$this->paginate['conditions']['OR']['username NOT LIKE'] = 'dr5%';
			}
			
		}
		
		$users = $this->paginate($this->Users);
	    foreach($users as $user){
					 $userArr[] = array(
										 'id' => $user->id,
										 'username' => $user->username,
										 'hours' => $this->get_current_day_hour($user->id),
										 'Days' => $this->get_current_days($user->id)
									    );
		 }
	    $usersname = $this->Users->find('list',array(
														 'fields'=>array('id','username'),
														 'recursive' => -1,
														 ));
		 $this->set(compact( 'userArr','username' ));
	    //$this->set('_serialize', ['users']);
	}
    
    
    private function get_current_day_hour($userID = ''){
        $conn = ConnectionManager::get('default');
		$query = "SELECT DISTINCT(DATE_FORMAT(`logged_in`,'%Y-%m-%d')) as days,
					DATE_FORMAT(`logged_in`,' %h:%i %p') as login_time, CASE WHEN (`day_off` = '0000-00-00 00-00-00' OR day_off is null) THEN '---' ELSE DATE_FORMAT(`day_off`,'%h:%i %p' ) END as dayoff, SEC_TO_TIME(round(TIMESTAMPDIFF(SECOND, `logged_in`,`day_off`), 2)) as Hours FROM `user_attendances` WHERE `user_id` = '$userID' AND DATE_FORMAT(`logged_in`,'%Y-%m') = DATE_FORMAT(CURRENT_DATE(),'%Y-%m')";
                    
		$this->loadModel('UserAttendances');
        $stmt = $conn->execute($query);
        $attendanceDays = $stmt ->fetchAll('assoc');
		$hoursWorked = 0;
		$end_hour = $end_min = 0;
		foreach($attendanceDays as $attendanceDay){
			$dayHour = $attendanceDay['Hours'];
			if(!empty($dayHour)){
				if($dayHour > 0){
					if($dayHour >=4){
						$date = date($dayHour);
						$time = strtotime($date);
						$time = $time - (30 * 60);
						$date = date("H:i:s", $time);
						$dayHour = $date;
					}
					$final_user_hrs = explode(":",$dayHour);
					list($h1,$m1) = $final_user_hrs;
					$end_hour = $end_hour + $h1;
					$end_min = $end_min + $m1;
					$hoursWorked = $end_hour.":".$end_min;
				}
			}
		}
		return $hoursWorked;
	}
    
    private function get_current_days($userID = ''){
          $conn = ConnectionManager::get('default');
		$query = "SELECT count(DISTINCT(DATE_FORMAT(`logged_in`,'%Y-%m-%d'))) as days from `user_attendances` where `user_id` = '$userID'  and DATE_FORMAT(`logged_in`,'%Y-%m') = DATE_FORMAT(CURRENT_DATE(),'%Y-%m') ";
		if($userID==1){
			//echo "<br/>".$query;
		}
		$this->loadModel('UserAttendances');
        $stmt = $conn->execute($query);
        $result = $stmt ->fetchAll('assoc');
		if(array_key_exists('0', $result)){
			return $result['0']['days'];
		}else{
			return 0;
		}
		//return $result;
	}
    
    /**
     * View method
     *
     * @param string|null $id User id.
     * @return \Cake\Network\Response|null
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view($id = null) {
		//if(AuthComponent::user('group_id') == ADMINISTRATORS || AuthComponent::user('group_id') == MANAGERS){
			$userID = $id;
            $this->loadModel('Users');
            $this->loadModel('UserAttendances');
            $this->loadModel('Kiosks');
			if (!$this->Users->exists($id)) {//UserAttendance
			   throw new NotFoundException(__('Invalid user attendance'));
			}
            
            $query = $this->UserAttendances->find('all', [
                                                'conditions' => array('id' => $id),
                                            ]);
            $userAttendance = $query->first();
            $query2 = $this->Users->find('all', [
                                                'conditions' =>array('id' => $userID),
                                                'fields' => array( 'username'),
                                                'recursive' => -1
                                            ]);
            $users = $query2->first();
			$current_month=  $this->current_month_attendance($userID);
            $kiosks_query = $this->Kiosks->find('list',[
                                                'keyField' => 'id',
                                                'valueField' => 'name',
											]);
            if(!empty($kiosks_query)){
                $kiosks = $kiosks_query->toArray();
            }else{
                $kiosks = array();
            }
			//$kiosks = $this->Kiosks->find('list', array('fields' => array('id', 'name')));
			$this->set(compact('users','userID','current_month','kiosks' ));
		//}else{
		//	return $this->redirect(array('controller' =>'home', 'action' => 'index'));
		//}
	}
    
    private function current_month_attendance($userID = ''){
        $conn = ConnectionManager::get('default');
		$query = "SELECT
						DISTINCT(DATE_FORMAT(`logged_in`,'%Y-%m-%d'))  as days,
						DATE_FORMAT(`logged_in`,'  %h:%i %p')   as login_time,
						CASE WHEN (`day_off` = '0000-00-00 00-00-00' OR day_off is null) THEN '---' ELSE DATE_FORMAT(`day_off`,'%h:%i %p' ) END as dayoff,
						SEC_TO_TIME(round(TIMESTAMPDIFF(SECOND, `logged_in`,`day_off`), 2)) as Hours,`Kiosk_id`
					FROM `user_attendances` WHERE `user_id` = '$userID'  AND DATE_FORMAT(`logged_in`,'%Y-%m') = DATE_FORMAT(CURRENT_DATE(),'%Y-%m')";
		if($userID == 1){
			//SELECT * FROM `user_attendances` WHERE `user_id`=38 AND `logged_in`='2015-10-12 09:57:11'
		 ;//echo "<br/>".$query;
		}
		//$result = $this->UserAttendance->query($query);
        $this->loadModel('UserAttendances');
        $stmt = $conn->execute($query);
        $result = $stmt ->fetchAll('assoc');
		return $result;
	}
    
   public function viewlastmonth( ){
    //echo "hi";die;
		//if(AuthComponent::user('group_id') == ADMINISTRATORS || AuthComponent::user('group_id') == MANAGERS){
		$id = $this->request->session()->read('Auth.User.id');
		  $all_ids = $this->getChildren($id);
		  if(!empty($all_ids)){
			 $all_ids_str = implode(",",$all_ids);
		  }else{
			 $all_ids_str = "";
		  }
		
            $userArr = array();
            $this->loadModel('Users');
			if($this->request->session()->read('Auth.User.group_id') == ADMINISTRATORS){
			   $this->paginate = array(
						 'fields'=>array('id','username'),
						 'order' => array('username' => 'asc'),
						 'recursive' => -1
					  );  
			}else{
			   if(!empty($all_ids)){
					$this->paginate = array(
						 'conditions' => array('id IN' => $all_ids),
						 'fields'=>array('id','username'),
						 'order' => array('username' => 'asc'),
						 'recursive' => -1
					  );
				 }else{
					$this->paginate = array(
						 'fields'=>array('id','username'),
						 'order' => array('username' => 'asc'),
						 'recursive' => -1
					  );  
				 }   
			}
			
             
            $users = $this->paginate($this->Users);
            foreach($users as $user){
                        $userArr[] = array(
                                            'id' => $user->id,
                                            'username' => $user->username,
                                            'hours' => $this->View_last_month_hours($user->id),
                                            'Days' => $this->View_last_month_day($user->id)
                                           );
            }
            $usersname_query = $this->Users->find('list',[
                                                       'keyField'=>'id',
											'valueField'=>'username',
										 ]
								    );
		  $usersname_query = $usersname_query->hydrate(false);
		  if(!empty($usersname_query)){
			$usersname = $usersname_query->toArray();
		  }else{
			$usersname = array();
		  }
            $this->set(compact( 'userArr','username' ));
            
            
		//}else{
		//	return $this->redirect(array('controller' =>'home', 'action' => 'index'));
		//}
	}
    
    
    private function View_last_month_day($userID = ''){
         $conn = ConnectionManager::get('default');
		$query = "SELECT count(DISTINCT(DATE_FORMAT(`logged_in`,'%Y-%m-%d'))) as `days` FROM `user_attendances` where `user_id` = '$userID' and DATE_FORMAT(`logged_in`,'%Y-%m') = concat(YEAR(CURRENT_DATE - INTERVAL 1 MONTH), '-', LPAD(MONTH(CURRENT_DATE - INTERVAL 1 MONTH),2,0))";
		//$result = $this->UserAttendance->query($query);
		$this->loadModel('UserAttendances');
        $stmt = $conn->execute($query);
        $result = $stmt ->fetchAll('assoc');
		if(array_key_exists('0', $result)){
			return $result['0']['days'];
		}else{
			return 0;
		}
		//return $result;
	}
    
   private function View_last_month_hours($userID = ''){
        $conn = ConnectionManager::get('default');
		 $query = "SELECT SEC_TO_TIME(round(TIMESTAMPDIFF(SECOND, `logged_in`,`day_off`), 2)) as Hours from `user_attendances` WHERE `user_id` = '$userID' and DATE_FORMAT(`logged_in`,'%Y-%m' ) = concat(YEAR(CURRENT_DATE - INTERVAL 1 MONTH), '-', LPAD(MONTH(CURRENT_DATE - INTERVAL 1 MONTH),2,0))";
		//$attendanceHours = $this->UserAttendance->query($query);
         $this->loadModel('UserAttendances');
        $stmt = $conn->execute($query);
        $attendanceHours = $stmt ->fetchAll('assoc');
        
		$hoursWorked = 0;
		$end_hour = $end_min = 0;
		foreach($attendanceHours as $attendanceHour){
			$dayHour = $attendanceHour['Hours'];
			if(!empty($dayHour)){
				if($dayHour >=4){
					$date = date($dayHour);
					$time = strtotime($date);
					$time = $time - (30 * 60);
					$date = date("H:i:s", $time);
					$dayHour = $date;
				}
				$final_user_hrs = explode(":",$dayHour);
				list($h1,$m1) = $final_user_hrs;
				$end_hour = $end_hour + $h1;
				$end_min = $end_min + $m1;
				$hoursWorked = $end_hour.":".$end_min;
			}
		}
		return $hoursWorked;
		//return $result;
	}
    
    public function a_b(){
        echo "hi";die;
    }
    public function kioskwiseAttendences(){
        $conn = ConnectionManager::get('default');
		 $start_date = date('Y-m-01');
		 $end_date = date('Y-m-d');
		 $kiosks_data = $this->get_kiosk();
		//if(AuthComponent::user('group_id') == ADMINISTRATORS || AuthComponent::user('group_id') == MANAGERS){
		//if( $this->request->session()->read('Auth.User.group_id')== MANAGERS){
		//			$managerKiosk = $this->managerLogin();//pr($managerKiosk);die;
		//			if(!empty($managerKiosk)){
		//				$manager_kiosk_id = key($managerKiosk);
		//				//$kiosk_id = implode('|', $kiosk_id);	
		//				$query = "SELECT
		//				DISTINCT(DATE_FORMAT(`logged_in`,'%Y-%m-%d'))  as days,
		//				DATE_FORMAT(`logged_in`,'  %h:%i %p')   as login_time,`kiosk_id` , `user_id`   ,
		//				CASE WHEN (day_off = '0000-00-00 00-00-00' OR day_off is null) THEN '---' ELSE DATE_FORMAT(`day_off`,'%h:%i %p' ) END as dayoff,
		//				SEC_TO_TIME(round(TIMESTAMPDIFF(SECOND, `logged_in`,`day_off`), 2)) as Hours
		//				 FROM `user_attendances` WHERE `kiosk_id` = '$manager_kiosk_id' And
		//				 DATE_FORMAT(`logged_in`,'%Y-%m-%d') >= '$start_date' and  DATE_FORMAT(`logged_in`,'%Y-%m-%d') <= '$end_date'
		//			  ORDER BY `user_id` ASC ";
		//			}
		//	}else{
		if($this->request->session()->read('Auth.User.group_id') == ADMINISTRATORS){
		  $query = "SELECT
						DISTINCT(DATE_FORMAT(`logged_in`,'%Y-%m-%d'))  as days,
						DATE_FORMAT(`logged_in`,'  %h:%i %p')   as login_time,`kiosk_id` , `user_id`   ,
						CASE WHEN (day_off = '0000-00-00 00-00-00' OR day_off is null) THEN '---' ELSE DATE_FORMAT(`day_off`,'%h:%i %p' ) END as dayoff,
						SEC_TO_TIME(round(TIMESTAMPDIFF(SECOND, `logged_in`,`day_off`), 2)) as Hours
					FROM `user_attendances` WHERE `kiosk_id` = 0 And
					DATE_FORMAT(`logged_in`,'%Y-%m-%d') >= '$start_date' and  DATE_FORMAT(`logged_in`,'%Y-%m-%d') <= '$end_date'
					  ORDER BY `user_id` ASC ";
		}else{
		   $external_site_arry = $CURRENCY_TYPE = Configure::read('external_sites');
										$path = dirname(__FILE__);
										$ext_site = 0;
										foreach($external_site_arry as $k=>$v){
											  $isboloRam = strpos($path,$v);
											  if($isboloRam){
												  $ext_site = 1;
											  }
										}
		  if($ext_site == 1){
			   $k_id = $kiosks_data[0];
			   $query = "SELECT
							 DISTINCT(DATE_FORMAT(`logged_in`,'%Y-%m-%d'))  as days,
							 DATE_FORMAT(`logged_in`,'  %h:%i %p')   as login_time,`kiosk_id` , `user_id`   ,
							 CASE WHEN (day_off = '0000-00-00 00-00-00' OR day_off is null) THEN '---' ELSE DATE_FORMAT(`day_off`,'%h:%i %p' ) END as dayoff,
							 SEC_TO_TIME(round(TIMESTAMPDIFF(SECOND, `logged_in`,`day_off`), 2)) as Hours
						 FROM `user_attendances` WHERE `kiosk_id` = $k_id And
						 DATE_FORMAT(`logged_in`,'%Y-%m-%d') >= '$start_date' and  DATE_FORMAT(`logged_in`,'%Y-%m-%d') <= '$end_date'
						   ORDER BY `user_id` ASC ";   
		  }else{
			   $query = "SELECT
						DISTINCT(DATE_FORMAT(`logged_in`,'%Y-%m-%d'))  as days,
						DATE_FORMAT(`logged_in`,'  %h:%i %p')   as login_time,`kiosk_id` , `user_id`   ,
						CASE WHEN (day_off = '0000-00-00 00-00-00' OR day_off is null) THEN '---' ELSE DATE_FORMAT(`day_off`,'%h:%i %p' ) END as dayoff,
						SEC_TO_TIME(round(TIMESTAMPDIFF(SECOND, `logged_in`,`day_off`), 2)) as Hours
					FROM `user_attendances` WHERE `kiosk_id` = 0 And
					DATE_FORMAT(`logged_in`,'%Y-%m-%d') >= '$start_date' and  DATE_FORMAT(`logged_in`,'%Y-%m-%d') <= '$end_date'
					  ORDER BY `user_id` ASC ";
		  }
		  
		}
		  
			//}
            $this->loadModel('UserAttendances');
            $stmt = $conn->execute($query);
            $UserAttendances = $stmt ->fetchAll('assoc');
			//$UserAttendances = $this->UserAttendance->query($query);
			$userArr = array();
			foreach($UserAttendances as $user){
				$hour = $min = "";
				$userId = $user['user_id'];
				$kioskId = $user['kiosk_id'];
				if(array_key_exists($kioskId,$userArr)){
					if(array_key_exists($userId,$userArr[$kioskId])){
						if($user['Hours'] >0 ){
							$abc =  $user['Hours'];
							if($abc >4){
								$date = date($abc);
								$time = strtotime($date);
								$time = $time - (30 * 60);
								$date = date("H:i:s", $time);
								$abc = $date;
							}
							list($h,$m) = explode(":",$abc);
							$allready = $userArr[$kioskId][$userId];
							$abc1 =  $allready;
							list($h1,$m1) = explode(":",$abc1);
							$hour = $h+$h1;
							$min = $m+$m1;
							$userArr[$kioskId][$userId] = $hour.":".$min;
						}
					}else{
						if($user['Hours'] >0 ){
							$abc =  $user['Hours'];
							if($abc >4){
								$date = date($abc);
								$time = strtotime($date);
								$time = $time - (30 * 60);
								$date = date("H:i:s", $time);
								$abc = $date;
							}
							$userArr[$kioskId][$userId] = $abc;
						}
					}
				}else{
					if($user['Hours'] >0 ){
						$abc =  $user['Hours'];
							if($abc >4){
								$date = date($abc);
								$time = strtotime($date);
								$time = $time - (30 * 60);
								$date = date("H:i:s", $time);
								$abc = $date;
							}
							$userArr[$kioskId][$userId]= $abc;
					}
				}
				
			}
			 //pr($userArr);die;
            $this->loadModel('Kiosks');
            $this->loadModel('Users');
			$kiosks1 = $this->Kiosks->find('list',array('fields' =>  array('id', 'name'), 'order' => array('Kiosks.name asc')));
			$query = $this->Users->find('list', [
                                        'keyField' => 'id',
                                        'valueField' => 'username'
                                     ]);
        
            $users = $query->toArray();
			$kiosks = $kiosks1->toArray();
			$this->set(compact('kiosks','UserAttendances','users','userArr','manager_kiosk_id'));
		//} 
	}
    
    public function kioskSearch(){
        $conn = ConnectionManager::get('default');
        $this->loadModel('Kiosks');
        $this->loadModel('Users');
        $this->loadModel('UserAttendances');
		 // pr($this->request->query);die;
		if(array_key_exists('kiosk_id',$this->request->query['data']['user_attendances'])){
			$kiosk_id = $this->request->query['data']['user_attendances']['kiosk_id'];
		}
		if(array_key_exists('month',$this->request->query)){
			$month = $this->request->query['month'];
		} 
		if(empty($month)){
		$month = date('Y-m');
		} 
		$this->set(compact('month'));
		
		list($year, $onlyMonth) = explode("-",$month);
		$daysOfMonth = date('t',strtotime(date("{$year}-{$onlyMonth}-01"))); 
		$monthEndDay = date("Y-m-d",strtotime(date("{$year}-{$onlyMonth}-$daysOfMonth"))); 
		$firstDay = date("{$year}-{$onlyMonth}-01");
		
		
		
		if($kiosk_id == '10000'){
			$kiosk_id = '0';
		}
		if(!empty($kiosk_id)){
			 $UserAttendances = array();
			 if(!empty($monthEndDay) && !empty($firstDay)){
				$query = "SELECT
									DISTINCT(DATE_FORMAT(`logged_in`,'%Y-%m-%d'))  as days,
									DATE_FORMAT(`logged_in`,'  %h:%i %p')   as login_time,`kiosk_id` , `user_id`   ,
									CASE WHEN (day_off = '0000-00-00 00-00-00' OR day_off is null) THEN '---' ELSE DATE_FORMAT(`day_off`,'%h:%i %p' ) END as dayoff,
									SEC_TO_TIME(round(TIMESTAMPDIFF(SECOND, `logged_in`,`day_off`), 2)) as Hours
									FROM `user_attendances` WHERE  `kiosk_id` = '$kiosk_id' and
									DATE_FORMAT(`logged_in`,'%Y-%m-%d') >= '$firstDay' and  DATE_FORMAT(`logged_in`,'%Y-%m-%d') <= '$monthEndDay' ORDER BY `user_id` ASC";
				//$UserAttendances = $this->UserAttendance->query($query);
                $stmt = $conn->execute($query);
                $UserAttendances = $stmt ->fetchAll('assoc');
				$userArr = array();
				foreach($UserAttendances as $user){
					$userId = $user['user_id'];
					$kioskId = $user['kiosk_id'];
					if(array_key_exists($kioskId,$userArr)){
						if(array_key_exists($userId,$userArr[$kioskId])){
							if($user['Hours'] >0 ){
								$abc =  $user['Hours'];
								if($abc >=4){
									$date = date($abc);
									$time = strtotime($date);
									$time = $time - (30 * 60);
									$date = date("H:i:s", $time);
									$abc = $date;
								}
								list($h,$m) = explode(":",$abc);
								$allready = $userArr[$kioskId][$userId];
								$abc1 =  $allready;
								list($h1,$m1) = explode(":",$abc1);
								$hour = $h+$h1;
								$min = $m+$m1;
								$userArr[$kioskId][$userId] = $hour.":".$min;
							}
						}else{
							if($user['Hours'] >0 ){
								$abc =  $user['Hours'];
								if($abc >=4){
									$date = date($abc);
									$time = strtotime($date);
									$time = $time - (30 * 60);
									$date = date("H:i:s", $time);
									$abc = $date;
								}
								$userArr[$kioskId][$userId] = $abc;
							}
						} 
					}else{
						if($user['Hours'] >0 ){
							$abc =  $user['Hours'];
								if($abc >=4){
									$date = date($abc);
									$time = strtotime($date);
									$time = $time - (30 * 60);
									$date = date("H:i:s", $time);
									$abc = $date;
								}
								$userArr[$kioskId][$userId]= $abc;
						}
					}
				
				}
			}else{
				$start_date = date('Y-m-01');
				$end_date = date('Y-m-d');
					$query = "SELECT
							DISTINCT(DATE_FORMAT(`logged_in`,'%Y-%m-%d'))  as days,
							DATE_FORMAT(`logged_in`,'  %h:%i %p')   as login_time,`kiosk_id` , `user_id`   ,
							CASE WHEN (day_off = '0000-00-00 00-00-00' OR day_off is null) THEN '---' ELSE DATE_FORMAT(`day_off`,'%h:%i %p' ) END as dayoff,
							SEC_TO_TIME(round(TIMESTAMPDIFF(SECOND, `logged_in`,`day_off`), 2)) as Hours
							FROM `user_attendances` WHERE  `kiosk_id` = '$kiosk_id' and
							DATE_FORMAT(`logged_in`,'%Y-%m-%d') >= '$start_date' and  DATE_FORMAT(`logged_in`,'%Y-%m-%d') <= '$end_date 'ORDER BY `user_id` ASC";
					//$UserAttendances = $this->UserAttendance->query($query);
                    $stmt = $conn->execute($query);
                    $UserAttendances = $stmt ->fetchAll('assoc');
                    
					$userArr = array();
					foreach($UserAttendances as $user){
						$userId = $user['user_id'];
						$kioskId = $user['kiosk_id'];
						if(array_key_exists($kioskId,$userArr)){
							if(array_key_exists($userId,$userArr[$kioskId])){
								if($user['Hours'] >0 ){
									$abc =  $user['Hours'];
									if($abc >=4){
										$date = date($abc);
										$time = strtotime($date);
										$time = $time - (30 * 60);
										$date = date("H:i:s", $time);
										$abc = $date;
									}
									list($h,$m) = explode(":",$abc);
									$allready = $userArr[$kioskId][$userId];
									$abc1 =  $allready;
									list($h1,$m1) = explode(":",$abc1);
									$hour = $h+$h1;
									$min = $m+$m1;
									$userArr[$kioskId][$userId] = $hour.":".$min;
								}
								
							}else{
								if($user['Hours'] >0 ){
									$abc =  $user['Hours'];
									if($abc >=4){
										$date = date($abc);
										$time = strtotime($date);
										$time = $time - (30 * 60);
										$date = date("H:i:s", $time);
										$abc = $date;
									}
									$userArr[$kioskId][$userId] = $abc;
								}
							}
						}else{
							if($user['Hours'] >0 ){
								$abc =  $user['Hours'];
								if($abc >=4){
									$date = date($abc);
									$time = strtotime($date);
									$time = $time - (30 * 60);
									$date = date("H:i:s", $time);
									$abc = $date;
								}
								$userArr[$kioskId][$userId]= $abc;
							}
						}
				
					}
			
			 }
		}else{
			$query = "SELECT
						DISTINCT(DATE_FORMAT(`logged_in`,'%Y-%m-%d'))  as days,
						DATE_FORMAT(`logged_in`,'  %h:%i %p')   as login_time,`kiosk_id` , `user_id`   ,
						CASE WHEN (day_off = '0000-00-00 00-00-00' OR day_off is null) THEN '---' ELSE DATE_FORMAT(`day_off`,'%h:%i %p' ) END as dayoff,
						SEC_TO_TIME(round(TIMESTAMPDIFF(SECOND, `logged_in`,`day_off`), 2)) as Hours
					FROM `user_attendances` WHERE  `kiosk_id` = '$kiosk_id' and  DATE_FORMAT(`logged_in`,'%Y-%m-%d') >= '$firstDay' and  DATE_FORMAT(`logged_in`,'%Y-%m-%d') <= '$monthEndDay'  ORDER BY `user_id` ASC "; 
			//$UserAttendances = $this->UserAttendance->query($query);
              $stmt = $conn->execute($query);
              $UserAttendances = $stmt ->fetchAll('assoc');
			$userArr = array();
			foreach($UserAttendances as $user){
				$userId = $user['user_id'];
				$kioskId = $user['kiosk_id'];
				if(array_key_exists($kioskId,$userArr)){
					if(array_key_exists($userId,$userArr[$kioskId])){
						if($user['Hours'] >0 ){
							$abc =  $user['Hours'];
							if($abc >=4){
								$date = date($abc);
								$time = strtotime($date);
								$time = $time - (30 * 60);
								$date = date("H:i:s", $time);
								$abc = $date;
							}
							list($h,$m) = explode(":",$abc);
							$allready = $userArr[$kioskId][$userId];
							$abc1 =  $allready;
							list($h1,$m1) = explode(":",$abc1);
							$hour = $h+$h1;
							$min = $m+$m1;
							$userArr[$kioskId][$userId] = $hour.":".$min;
						}
					}else{
						if($user['Hours'] >0 ){
							$abc =  $user['Hours'];
							if($abc >=4){
								$date = date($abc);
								$time = strtotime($date);
								$time = $time - (30 * 60);
								$date = date("H:i:s", $time);
								$abc = $date;
							}
							$userArr[$kioskId][$userId] = $abc;
						}
					} 
				}else{
					if($user['Hours'] >0 ){
						$abc =  $user['Hours'];
							if($abc >=4){
								$date = date($abc);
								$time = strtotime($date);
								$time = $time - (30 * 60);
								$date = date("H:i:s", $time);
								$abc = $date;
							}
							$userArr[$kioskId][$userId]= $abc;
					}
				}
				
			}
			// pr($userArr);
		}
		 
		$kiosks1 = $this->Kiosks->find('list',array('fields' =>  array('id', 'name'), 'order' => array('Kiosks.name asc')));
		//$users1 = $this->Users->find('list',array('fields' =>  array('id', 'username')));
        $query = $this->Users->find('list', [
                                        'keyField' => 'id',
                                        'valueField' => 'username'
                                     ]);
        
        $users = $query->toArray();
		$kiosks = $kiosks1->toArray();
		 
		$this->set(compact('kiosks', 'UserAttendances','users','userArr'));
		//$this->layout = 'default'; 
		$this->render('kioskwise_attendences');
	}
    
    public function datewiseAttendences(){
         $conn = ConnectionManager::get('default');
            $this->loadModel('Kiosks');
            $this->loadModel('Users');
            $this->loadModel('UserAttendances');
			$id = $this->request->session()->read('Auth.User.id');
			$all_ids = $this->getChildren($id);
			if(!empty($all_ids)){
			   $all_ids_str = implode(",",$all_ids);
			}else{
			   $all_ids_str = "";
			}
			
		//if(AuthComponent::user('group_id') == ADMINISTRATORS || AuthComponent::user('group_id') == MANAGERS){
			//$this->UserAttendance->recursive = 0;
			$users_query = $this->Users->find('all',array(
													'fields'=>array('Users.id','Users.username'),
													'order' => array('Users.username' => 'asc'),
													//'recursive' => -1,
													'limit' => '20'
						));
			$users_query = $users_query->hydrate(false);
            if(!empty($users_query)){
                $users = $users_query->toArray();
            }else{
                $users = array();
            } 	
            //pr($users);
        	$userArr = array();
			foreach($users as $user){
			 	$userids[] = $user['id'];
			}
		 
			$start = date("Y-m-d");
			if($this->request->session()->read('Auth.User.group_id') == ADMINISTRATORS){
			   $query = "SELECT
						DISTINCT(DATE_FORMAT(`logged_in`,'%Y-%m-%d'))  as days,
						DATE_FORMAT(`logged_in`,'  %h:%i %p')   as login_time,`kiosk_id` , `user_id`   ,
						CASE WHEN (day_off = '0000-00-00 00-00-00' OR day_off is null) THEN '---' ELSE DATE_FORMAT(`day_off`,'%h:%i %p' ) END as dayoff,
						SEC_TO_TIME(round(TIMESTAMPDIFF(SECOND, `logged_in`,`day_off`), 2)) as Hours
					FROM `user_attendances` WHERE   DATE_FORMAT(`logged_in`,'%Y-%m-%d') = '$start' ";
			}else{
			    $external_site_arry = $CURRENCY_TYPE = Configure::read('external_sites');
										$path = dirname(__FILE__);
										$ext_site = 0;
										foreach($external_site_arry as $k=>$v){
											  $isboloRam = strpos($path,$v);
											  if($isboloRam){
												  $ext_site = 1;
											  }
										}
			   if($ext_site == 1){
					$query = "SELECT
						DISTINCT(DATE_FORMAT(`logged_in`,'%Y-%m-%d'))  as days,
						DATE_FORMAT(`logged_in`,'  %h:%i %p')   as login_time,`kiosk_id` , `user_id`   ,
						CASE WHEN (day_off = '0000-00-00 00-00-00' OR day_off is null) THEN '---' ELSE DATE_FORMAT(`day_off`,'%h:%i %p' ) END as dayoff,
						SEC_TO_TIME(round(TIMESTAMPDIFF(SECOND, `logged_in`,`day_off`), 2)) as Hours
					FROM `user_attendances` WHERE   DATE_FORMAT(`logged_in`,'%Y-%m-%d') = '$start' AND user_id IN ($all_ids_str) ";   	
			   }else{
					$query = "SELECT
						DISTINCT(DATE_FORMAT(`logged_in`,'%Y-%m-%d'))  as days,
						DATE_FORMAT(`logged_in`,'  %h:%i %p')   as login_time,`kiosk_id` , `user_id`   ,
						CASE WHEN (day_off = '0000-00-00 00-00-00' OR day_off is null) THEN '---' ELSE DATE_FORMAT(`day_off`,'%h:%i %p' ) END as dayoff,
						SEC_TO_TIME(round(TIMESTAMPDIFF(SECOND, `logged_in`,`day_off`), 2)) as Hours
					FROM `user_attendances` WHERE   DATE_FORMAT(`logged_in`,'%Y-%m-%d') = '$start' ";
			   }
			   
			}
			
			//$query = "SELECT
			//			DISTINCT(DATE_FORMAT(`logged_in`,'%Y-%m-%d'))  as days,
			//			DATE_FORMAT(`logged_in`,'  %h:%i %p')   as login_time,`kiosk_id` , `user_id`   ,
			//			CASE WHEN (day_off = '0000-00-00 00-00-00' OR day_off is null) THEN '---' ELSE DATE_FORMAT(`day_off`,'%h:%i %p' ) END as dayoff,
			//			round(TIMESTAMPDIFF(MINUTE, `logged_in`,`day_off`)/60, 2) as Hours
			//		FROM `user_attendances` WHERE   DATE_FORMAT(`logged_in`,'%Y-%m-%d') = '$start' ";
			
			//$UserAttendances = $this->UserAttendance->query($query);
            
             $stmt = $conn->execute($query);
            $UserAttendances = $stmt ->fetchAll('assoc');
            
			$kiosks1 = $this->Kiosks->find('list',array('fields' =>  array('id', 'name'), 'order' => array('Kiosks.name asc')));
			$users1 = $this->Users->find('list',[
                                                 'keyField' => 'id',
											  'valueField' => 'username'
											  ]);
 
            if(!empty($users1)){
               $users = $users1->toArray();
            }
			$kiosks = $kiosks1->toArray();
			
			
			$this->set(compact('kiosks','users'));
			$this->set(compact('kiosk_name','UserAttendances' ));
		//}else{
		//	return $this->redirect(array('controller' =>'home', 'action' => 'index'));
		//}
	}
    
    public function dateSearch(){
        $conn = ConnectionManager::get('default');
        $this->loadModel('Kiosks');
        $this->loadModel('Users');
        $this->loadModel('UserAttendances');
		if(array_key_exists('date',$this->request->query)){
			$date = $this->request->query['date'];
		}
		
		  $id = $this->request->session()->read('Auth.User.id');
		  $all_ids = $this->getChildren($id);
		  if(!empty($all_ids)){
			 $all_ids_str = implode(",",$all_ids);
		  }else{
			 $all_ids_str = "";
		  }
		
		if(!empty($date)){
			$userid = array();
			$UserAttendances = array();
			$firstDay = strtotime($date);
			$start = date("Y-m-d",$firstDay);
			if($this->request->session()->read('Auth.User.group_id') == ADMINISTRATORS){
			   $query = "SELECT
								  DISTINCT(DATE_FORMAT(`logged_in`,'%Y-%m-%d'))  as days,
								  DATE_FORMAT(`logged_in`,'  %h:%i %p')   as login_time,`kiosk_id` , `user_id`   ,
								  CASE WHEN (day_off = '0000-00-00 00-00-00' OR day_off is null) THEN '---' ELSE DATE_FORMAT(`day_off`,'%h:%i %p' ) END as dayoff,
								  SEC_TO_TIME(round(TIMESTAMPDIFF(SECOND, `logged_in`,`day_off`), 2)) as Hours
							  FROM `user_attendances` WHERE   DATE_FORMAT(`logged_in`,'%Y-%m-%d') = '$start' ";
			}else{
					if(!empty($all_ids_str)){
						 $query = "SELECT
								  DISTINCT(DATE_FORMAT(`logged_in`,'%Y-%m-%d'))  as days,
								  DATE_FORMAT(`logged_in`,'  %h:%i %p')   as login_time,`kiosk_id` , `user_id`   ,
								  CASE WHEN (day_off = '0000-00-00 00-00-00' OR day_off is null) THEN '---' ELSE DATE_FORMAT(`day_off`,'%h:%i %p' ) END as dayoff,
								  SEC_TO_TIME(round(TIMESTAMPDIFF(SECOND, `logged_in`,`day_off`), 2)) as Hours
							  FROM `user_attendances` WHERE   DATE_FORMAT(`logged_in`,'%Y-%m-%d') = '$start' AND user_id IN ($all_ids_str)";   
					  }else{
						 $query = "SELECT
								  DISTINCT(DATE_FORMAT(`logged_in`,'%Y-%m-%d'))  as days,
								  DATE_FORMAT(`logged_in`,'  %h:%i %p')   as login_time,`kiosk_id` , `user_id`   ,
								  CASE WHEN (day_off = '0000-00-00 00-00-00' OR day_off is null) THEN '---' ELSE DATE_FORMAT(`day_off`,'%h:%i %p' ) END as dayoff,
								  SEC_TO_TIME(round(TIMESTAMPDIFF(SECOND, `logged_in`,`day_off`), 2)) as Hours
							  FROM `user_attendances` WHERE   DATE_FORMAT(`logged_in`,'%Y-%m-%d') = '$start' ";
					  }   
			}
			
			
			//$UserAttendances = $this->UserAttendance->query($query);
			$stmt = $conn->execute($query);
            $UserAttendances = $stmt ->fetchAll('assoc');
            
			foreach($UserAttendances as $UserAttendance){
				$userid =  $UserAttendance['user_id'];
			}
			//$this->Paginator->settings = array(
			//	'conditions' => $userid ,
			//	'order' => array('username' => 'asc'),
			//	'recursive' => -1
			//);
            $this->paginate = array(
                'conditions' => $userid ,
				'order' => array('username' => 'asc'),
				'recursive' => -1
		     );
			//$users = $this->Paginator->paginate('User');  
		}else{
		  if($this->request->session()->read('Auth.User.group_id') == ADMINISTRATORS){
			   $users = $this->Users->find('all',array(
														 'fields'=>array('Users.id','Users.username'),
														 'order' => array('Users.username' => 'asc'),
														 //'recursive' => -1,
														 'limit' => '20'
							 ));
		  }else{
			   if(!empty($all_ids)){
					$users = $this->Users->find('all',array(
													   'conditions' => ['Users.id IN' => $all_ids],
														 'fields'=>array('Users.id','Users.username'),
														 'order' => array('Users.username' => 'asc'),
														 //'recursive' => -1,
														 'limit' => '20'
							 ));   
			   }else{
					$users = $this->Users->find('all',array(
														 'fields'=>array('Users.id','Users.username'),
														 'order' => array('Users.username' => 'asc'),
														 //'recursive' => -1,
														 'limit' => '20'
							 ));
			   }   
		  }
		  
			
					
			$userArr = array();
			foreach($users as $user){
			 	$userids[] = $user->id;
			}
			 
			 
			$start = date("Y-m-d");
			if($this->request->session()->read('Auth.User.group_id') == ADMINISTRATORS){
			    $query = "SELECT
								  DISTINCT(DATE_FORMAT(`logged_in`,'%Y-%m-%d'))  as days,
								  DATE_FORMAT(`logged_in`,'  %h:%i %p')   as login_time,`kiosk_id` , `user_id`   ,
								  CASE WHEN (day_off = '0000-00-00 00-00-00' OR day_off is null) THEN '---' ELSE DATE_FORMAT(`day_off`,'%h:%i %p' ) END as dayoff,
								  SEC_TO_TIME(round(TIMESTAMPDIFF(SECOND, `logged_in`,`day_off`), 2)) as Hours
							  FROM `user_attendances` WHERE   DATE_FORMAT(`logged_in`,'%Y-%m-%d') = '$start' ";   
			}else{
					if(!empty($all_ids_str)){
						 $query = "SELECT
								  DISTINCT(DATE_FORMAT(`logged_in`,'%Y-%m-%d'))  as days,
								  DATE_FORMAT(`logged_in`,'  %h:%i %p')   as login_time,`kiosk_id` , `user_id`   ,
								  CASE WHEN (day_off = '0000-00-00 00-00-00' OR day_off is null) THEN '---' ELSE DATE_FORMAT(`day_off`,'%h:%i %p' ) END as dayoff,
								  SEC_TO_TIME(round(TIMESTAMPDIFF(SECOND, `logged_in`,`day_off`), 2)) as Hours
							  FROM `user_attendances` WHERE   DATE_FORMAT(`logged_in`,'%Y-%m-%d') = '$start' AND user_id IN ($all_ids_str)";
					  }else{
						 $query = "SELECT
								  DISTINCT(DATE_FORMAT(`logged_in`,'%Y-%m-%d'))  as days,
								  DATE_FORMAT(`logged_in`,'  %h:%i %p')   as login_time,`kiosk_id` , `user_id`   ,
								  CASE WHEN (day_off = '0000-00-00 00-00-00' OR day_off is null) THEN '---' ELSE DATE_FORMAT(`day_off`,'%h:%i %p' ) END as dayoff,
								  SEC_TO_TIME(round(TIMESTAMPDIFF(SECOND, `logged_in`,`day_off`), 2)) as Hours
							  FROM `user_attendances` WHERE   DATE_FORMAT(`logged_in`,'%Y-%m-%d') = '$start' ";   
					  }   
			}
			
			
			//$UserAttendances = $this->UserAttendance->query($query);
            
			$stmt = $conn->execute($query);
            $UserAttendances = $stmt ->fetchAll('assoc');
		}
		$userArr = array();
        
        $users = $this->paginate($this->Users);
		foreach($users as $user){
			$userArr[] = array(
				'id' => $user ['User']['id'],
				'username' => $user['User']['username'],
				'Hours' => $this->get_current_day_hour($user ['User']['id']),
				'Days' => $this->get_current_days($user ['User']['id'])
			);
		}
		$kiosks1 = $this->Kiosks->find('list',array('fields' =>  array('id', 'name'), 'order' => array('Kiosks.name asc')));
		$users1 = $this->Users->find('list',[
                                                    'keyField' => 'id',
                                                     'valueField' => 'username',
                                             ]); 
		
		$users = $users1->toArray();
		$kiosks = $kiosks1->toArray();
		
		$this->set(compact('userArr','kiosks', 'UserAttendances','users', 'start'));
		$this->render('datewise_attendences');
	}
    
	public function lastMonthViewAttendence($id = null){
		$conn = ConnectionManager::get('default');
        $this->loadModel('Kiosks');
        $this->loadModel('Users');
        $this->loadModel('UserAttendances');
		
		$userID = $id;
		if (!$this->Users->exists($id)) {
		   throw new NotFoundException(__('Invalid User'));
		}
		
		//$options = array('conditions' => array('UserAttendances.id' => $id));
		//$this->set('userAttendance', $this->UserAttendances->find('first', $options));
		
		$query1 = $this->UserAttendances->find('all', [
                                                'conditions' => array('UserAttendances.id' => $id)
                                            ]);
		$this->set('userAttendance', $query1->first());
		//$users = $this->Users->find('first',array(
		//   'conditions' => array('id' => $userID),
		//   'fields' => array( 'username'),
		//   'recursive' => -1
		//   )
		//);
		$users_query = $this->Users->find('all', [
                                                'conditions' => array('id' => $userID),
												'fields' => array( 'username'),
												'recursive' => -1
                                            ]);
		$users_query = $users_query->hydrate(false);
		if(!empty($users_query)){
			$users = $users_query->first();
		}else{
			$users = array();
		}
		$kiosks_query = $this->Kiosks->find('list',[
										'keyField' => 'id',
										'valueField'=>'name'
									]
								);
		$kiosks_query = $kiosks_query->hydrate(false);
		if(!empty($kiosks_query)){
			$kiosks = $kiosks_query->toArray();
		}else{
			$kiosks = array();
		}
		
		$last_month=  $this->View_last_month_attendence($userID);
		$this->set(compact('users','userID','last_month' ,'kiosks'));
	}
	
	private function View_last_month_attendence($userID = ''){
		$conn = ConnectionManager::get('default');
        $this->loadModel('UserAttendances');
		$query = "SELECT
						DISTINCT(DATE_FORMAT(`logged_in`,'%Y-%m-%d'))  as days,
						SEC_TO_TIME(round(TIMESTAMPDIFF(SECOND, `logged_in`,`day_off`), 2)) as Hours,
						DATE_FORMAT(`logged_in`,'%h:%i %p')   as login_time,
						CASE WHEN (day_off = '0000-00-00 00-00-00' OR day_off is null) THEN '---' ELSE DATE_FORMAT(`day_off`,'%h:%i %p' ) END as dayoff ,`kiosk_id`
						FROM `user_attendances` WHERE `user_id` =  '$userID ' AND DATE_FORMAT(`logged_in`,'%Y-%m') = concat(YEAR(CURRENT_DATE - INTERVAL 1 MONTH), '-', LPAD(MONTH(CURRENT_DATE - INTERVAL 1 MONTH),2,0)) group by DATE_FORMAT(`logged_in`,'%Y-%m-%d')";
		//$results = $this->UserAttendance->query($query);
		$stmt = $conn->execute($query);
        $results = $stmt ->fetchAll('assoc');
		//pr($results);
		return $results;
	}
    public function dayoff() {
        $conn = ConnectionManager::get('default');
        $this->loadModel('Users');
        $this->loadModel('UserAttendances');
        $this->loadModel('KioskTimings');
        $this->loadModel('Settings');
        $this->loadModel('Kiosks');
		 $userID = $this->request->session()->read('Auth.User.id'); 
        $result_query = $this->Users->find('all',array(
																'conditions' => array('Users.id'=>$userID),
																'recursive' => -1
														)
												   );
        $user_result = $result_query->first();
        //->toArray();
        if(!empty($user_result)){
            $user_result  = $user_result->toArray();
        }
        $username = "";
		if(!empty($user_result)){
			$username = $user_result['username'];
		}
		$session_id = session_id();
		$logged_out = date('Y-m-d G:i:s');//updated the time to G from H as per 24 hours format
		$dayof = date('Y-m-d G:i:s'); //should be with reference to database timezone.
        
		if(isset($dayof)){
             
			//-----------------------------------
			$this->update_daily_target();
			//-----------------------------------
			$query = "UPDATE user_attendances SET `day_off`='$dayof', `logged_out` = '$logged_out' WHERE session_ide='$session_id'";
			 $query = "UPDATE user_attendances SET `day_off`='$dayof', `logged_out` = '$logged_out' WHERE `user_id` = $userID AND DATE_FORMAT(`logged_in`,'%Y-%m-%d') = DATE_FORMAT(CURRENT_DATE(),'%Y-%m-%d')";
		    $stmt = $conn->execute($query);
             
		}
        
		$kioskId = $this->request->session()->read('kiosk_id');
		$time_query  = $this->KioskTimings->find('all',array('conditions' => array('kiosk_id' => $kioskId)));
         $result = $time_query->first();
		 if(!empty($result)){
            $result  = $result->toArray();
        }
		//echo "hi";pr($result);die;
		if(!empty($result)){
			//$emailTo = "d_inderjit@hotmail.com";
            $emailTo = "rajjukaura@gmail.com"; 
			$email_query = $this->Settings->find('all',array('conditions' => array(
																				'attribute_name' => 'attendence_email'),
																				'fields' => ['attribute_value']
																	   ));
           
            $email_result = $email_query->first();
             if(!empty($email_result)){
                $email_result  = $email_result->toArray();
            }
			$emails = array();
           
			if(!empty($email_result)){
				$emailTo = $email_result['attribute_value'];
				$emails = explode(",",$emailTo);
				 
			}
			 
		 	if(date('N') == 1){
			   $time_to_check = $result['mon_time_out'];
			}elseif(date('N') == 2){
			   $time_to_check = $result['tues_time_out'];
			}elseif(date('N') == 3){
			   $time_to_check = $result['wed_time_out'];
			}elseif(date('N') == 4){
                $time_to_check = $result['thrus_time_out'];
			}elseif(date('N') == 5){
			   $time_to_check = $result['fri_time_out'];
			}elseif(date('N') == 6){
			   $time_to_check = $result['sat_time_out'];
			}elseif(date('N') == 7){
			   $time_to_check = $result['sun_time_out'];
			} 
			$min = 0; $status = "";
		   $ldeal_time1 = strtotime($time_to_check); 
		   $ldeal_time2 = date("G:i:s", strtotime('-10 minutes', $ldeal_time1));
		   $ldeal_time = strtotime($ldeal_time2);
		   $current_time = strtotime(date("G:i:00"));
		   if($ldeal_time == $current_time){
			   ;// do nothing
		   }else{
			   if($ldeal_time < $current_time){
				//echo "after";
				   //$status = "after";
				   //$diff = $current_time - $ldeal_time;
			   }else{
				  $status = "early";
				   $diff = $ldeal_time1 - $current_time;
			   } 
			   if(!empty($diff)){
					$min = $diff/60;
			   }
			   
		   }
		   $send_by_email = Configure::read('send_by_email');
		   $emailSender = Configure::read('EMAIL_SENDER');
		   if($min >0 && !empty($status)){
           		   $kiosk_query = $this->Kiosks->find('list');
                   $kiosk = $kiosk_query->toArray();
                   foreach($emails as $email){
						$email = trim($email);
						if (!filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
								//$Email = new CakeEmail();
                                $Email = new Email();
								$Email->config('default');
								$Email->viewVars(array(
									'kiosk' => $kiosk,
									'kiosk_id' =>  $kioskId,
									'min' => $min,
									'log' => 'closed by',
									'username' => $username,
									'status' => $status,
								));
                              
								//$emailTo = "sourabh.proavid@gmail.com";
								// echo $emailTo;die;
								$Email->template('attaendence_email');
								$Email->emailFormat('html');
								//$Email->to($emailTo);
								$Email->to($email);
								$Email->transport(TRANSPORT);
								   $Email->from([$send_by_email => $emailSender]);
								//$Email->sender('sales@oceanstead.co.uk','Sales Team');
								//This should be added in config file
								$sub = $kiosk[$kioskId]." , ".$username." ".$status." closed by ".$min." minutes";
								$Email->subject($sub);
								$Email->send();
						  }
						
				    } 
				  
		   } 
		}
		$this->deleteCacheFilesOfKiosk();
        $this->Flash->success(__('Good-Bye'));
		$this->redirect($this->Auth->logout());
	}
	
	public function deleteCacheFilesOfKiosk(){
	 $site_path = dirname(__FILE__);
	  $server = $_SERVER['SERVER_NAME'];
	   $sites = Configure::read('site_full_url');
	  foreach($sites as $site_name => $site_path1){
			$isMbwaheguru = strpos($site_path,$site_name);
			if($isMbwaheguru){
				$domain_name = $site_path1;
			}
	  }
	  $path[] = "/var/www/vhosts/$domain_name/$server/tmp/cache/persistent/";
         $path[] = "/var/www/vhosts/$domain_name/$server/tmp/cache/models/";
		 
      
      if(count($path) > 0){
         $count = 0;
         foreach($path as $key => $value){
			if(is_dir($value)){
				$scanned_directory = array_diff(scandir($value), array('..', '.'));
				if(!empty($scanned_directory)){
				  foreach($scanned_directory as $scanned_key =>$scanned_value ){
					 $fullpath  = $value.$scanned_value;
					 if(is_dir($fullpath)){
					   unlink($fullpath);
					   $count++;
					 }
				  }
			   }
			}
         }
      }
      
   }
	
	public function search(){
		  $id = $this->request->session()->read('Auth.User.id');
		  $all_ids = $this->getChildren($id);
		  if(!empty($all_ids)){
			 $all_ids_str = implode(",",$all_ids);
		  }else{
			 $all_ids_str = "";
		  }
		  
		  
		$conditionArr = $this->generate_condition_array();
		if(SPECIAL_USER == 0){
			$conditionArr['username NOT LIKE'] = 'dr5%';
		}
		if(array_key_exists('month',$this->request->query['month'])){
			$month = $this->request->query['month']['month'];
		}
		//if(empty($month)){
		//	$month = date("Y-n", strtotime("first day of previous month"));
		//}
		$username_query = $this->Users->find('list',[
											  'keyField' => 'id',
											  'valueField' => 'username'
											  ]);
		if(!empty($username_query)){
			$username = $username_query->toArray();
		}
		$searchKW = $this->request->query['search_kw'];
		$userId = '';
		$userIDs = array();
		$userArr = array();
        if(empty($searchKW)&& empty($month)){
		  if($this->request->session()->read('Auth.User.group_id') == ADMINISTRATORS){
			if(SPECIAL_USER == 0){
				$users_query = $this->Users->find('all',array(
												'fields'=>array('id','username'),
												'conditions' => array('username NOT LIKE' => 'dr5%')
												));
				
			}else{
				$users_query = $this->Users->find('all',array(
												'fields'=>array('id','username'),
												'recursive' => -1
												));	
			}
			
		  }else{
			   if(!empty($all_ids)){
					if(SPECIAL_USER == 0){
						$users_query = $this->Users->find('all',array(
															'conditions' => ['id IN' =>  $all_ids,
																		  'username NOT LIKE' => 'dr5%'],
															 'fields'=>array('id','username')
															 ));
					}else{
						$users_query = $this->Users->find('all',array(
															'conditions' => ['id IN' =>  $all_ids],
															 'fields'=>array('id','username')
															 ));
					}
			   }else{
					if(SPECIAL_USER == 0){
						$users_query = $this->Users->find('all',array(
															 'fields'=>array('id','username'),
															 'conditions' => array('username NOT LIKE'=> 'dr5%')
															 ));
					}else{
						$users_query = $this->Users->find('all',array(
															 'fields'=>array('id','username'),
															 'recursive' => -1
															 ));
					}
			   }   
		  }
		  
            
			$users_query = $users_query->hydrate(false);
			if(!empty($users_query)){
				$users = $users_query->toArray();
			}
			
						foreach($users as $user){
							$userArr[] = array(
										'id' => $user['id'],
										'username' => $user['username'],
										'hours' => $this->get_current_day_hour($user['id']),
										'Days' => $this->get_current_days($user['id'])
									   );
						}
			 
		}else{
		  
			if(!empty($searchKW) && empty($month) ){
				$users_query = $this->Users->find('all',array(
															'fields'=>array('id','username'),
															 'conditions' => $conditionArr 
															));
				$users_query = $users_query->hydrate(false);
				if(!empty($users_query)){
					$users = $users_query->toArray();
				}
					foreach($users as $user){
						$userArr[] = array(
										'id' => $user['id'],
										'username' => $user['username'],
										'hours' => $this->get_current_day_hour($user['id']),
										'Days' => $this->get_current_days($user['id'])
									   );
						
					}
					
			}elseif(!empty($month) && !empty($searchKW)){
				$users_query = $this->Users->find('all',array(
															'fields'=>array('id','username'),
															 'conditions' => $conditionArr ,
															'recursive' => -1
															));
				$users_query = $users_query->hydrate(false);
				if(!empty($users_query)){
					$users = $users_query->toArray();
				}
					foreach($users as $user){
						$userArr[] = array(
										'id' => $user['id'],
										'username' => $user['username'],
										'hours' => $this->View_last_month_hours_search($user['id'],$month),
										'Days' => $this->View_last_month_day_search($user['id'],$month)
									   );
						
					}
					//pr($userArr);
			}
			elseif(!empty($month) && empty($searchKW)){
			   if($this->request->session()->read('Auth.User.group_id') == ADMINISTRATORS){
					$users_query = $this->Users->find('all',array(
																 'fields'=>array('id','username'),
																 // 'conditions' => $conditionArr ,
																 'recursive' => -1
																 ));
			   }else{
					if(!empty($all_ids)){
						 $users_query = $this->Users->find('all',array(
																 'fields'=>array('id','username'),
																 'conditions' => ['id IN' => $all_ids] ,
																 'recursive' => -1
																 ));	
					}else{
						 $users_query = $this->Users->find('all',array(
																 'fields'=>array('id','username'),
																 // 'conditions' => $conditionArr ,
																 'recursive' => -1
																 ));
					}	
			   }
			   
				
				$users_query = $users_query->hydrate(false);
				if(!empty($users_query)){
					$users = $users_query->toArray();
				}
				
					foreach($users as $user){
						$userArr[] = array(
										'id' => $user['id'],
										'username' => $user['username'],
										'hours' => $this->View_last_month_hours_search($user['id'],$month),
										'Days' => $this->View_last_month_day_search($user['id'],$month)
									   );
						
					}
					//pr($userArr);
			}
			
		}
		$this->set(compact('userArr','users','username'));
		$this->render('index');
	}
	
	private function generate_condition_array(){
		$searchKW = trim(strtolower($this->request->query['search_kw']));
		$conditionArr = array();
		if(!empty($searchKW)){
			$conditionArr['Users.username like '] =  strtolower("%$searchKW%");
		}
		return $conditionArr;
	}
	
	private function View_last_month_hours_search($userID = '',$month =''){
		$query = "SELECT SEC_TO_TIME(round(TIMESTAMPDIFF(SECOND, `logged_in`,`day_off`), 2)) as Hours from `user_attendances` WHERE `user_id` = '$userID' and  DATE_FORMAT(`logged_in`,'%Y-%m') = '$month'";
		$conn = ConnectionManager::get('default');
		$this->loadModel('UserAttendances');
        $stmt = $conn->execute($query);
        $attendanceHours = $stmt ->fetchAll('assoc');
		
		$hoursWorked = 0;
		$end_hour = $end_min = 0;
		foreach($attendanceHours as $attendanceHour){
			$dayHour = "";
			$dayHour = $attendanceHour['Hours'];
			if(!empty($dayHour)){
				if($dayHour >=4){
					$date = date($dayHour);
					$time = strtotime($date);
					$time = $time - (30 * 60);
					$date = date("H:i:s", $time);
					$dayHour = $date;
				}
				if($dayHour > 0){
					$final_user_hrs = explode(":",$dayHour);
					list($h1,$m1) = $final_user_hrs;
						$end_hour = $end_hour + $h1;
						$end_min = $end_min + $m1;
						$hoursWorked = $end_hour.":".$end_min;
				}
				
			}
		}
		return $hoursWorked;
		//return $result;
	}
	
	private function View_last_month_day_search($userID = '',$month =''){
		$query = "SELECT count(DISTINCT(DATE_FORMAT(`logged_in`,'%Y-%m-%d'))) as `days` FROM `user_attendances` where `user_id` = '$userID' and  DATE_FORMAT(`logged_in`,'%Y-%m') = '$month'";
		
		$conn = ConnectionManager::get('default');
		$this->loadModel('UserAttendances');
        $stmt = $conn->execute($query);
        $result = $stmt ->fetchAll('assoc');
		//$result = $this->UserAttendance->query($query);
		if(array_key_exists('0', $result)){
			return $result['0']['days'];
		}else{
			return 0;
		}
		//return $result;
	}
    
    public function add() {
        //change model file
         $activeOptions = $active = Configure::read('active');
       
        $this->set(compact('activeOptions'));
        $UserAttendances = $this->UserAttendances->newEntity();
		if ($this->request->is('post')) {
		    $UserAttendances = $this->UserAttendances->patchEntity($UserAttendances,$this->request->data,['validate' => false]);
			if ($this->UserAttendances->save($UserAttendances)) {
				$this->Flash->success(__('The user attendance has been saved.'));
				return $this->redirect(array('action' => 'index'));
			} else {
				$this->Flash->error(__('The user attendance could not be saved. Please, try again.'));
			}
		}
		$kiosks_query = $this->Kiosks->find('list',  [
                                                               'keyField' => 'id',
                                                                'valueField' => 'name',
													    ]); 
		$kiosks_query = $kiosks_query->hydrate(false);
		if(!empty($kiosks_query)){
		    $kiosks = $kiosks_query->toArray();
		}else{
		    $kiosks = array();
		}
		$users_query = $this->UserAttendances->Users->find('list',[
											   'keyField' => 'id',
											   'valueField' => 'username',
													    ]);
		$users_query = $users_query->hydrate(false);
		if(!empty($users_query)){
		    $users = $users_query->toArray();
		}else{
		    $users = array();
		}
         $this->set(compact('UserAttendances'));
		$this->set(compact('kiosks', 'users'));
	}
    public function lastmonthsearch(){
       
		$conditionArr = $this->generate_condition_array();
		if(array_key_exists('month',$this->request->query['month'])){
			$month = $this->request->query['month']['month'];
		}
		//if(empty($month)){
		//	$month = date("Y-n", strtotime("first day of previous month"));
		//}
        $username_query = $this->Users->find('list',[
                                                    'keyField' => 'id',
                                                     'valueField' => 'username' 
                                                    ]
                                        ); 
         if(!empty($username_query)){
             $username = $username_query->toArray();
        }
		 
		$searchKW = $this->request->query['search_kw'];
		$userId = '';
		$userIDs = array();
        $userArr = array();
		if(empty($searchKW)&& empty($month)){
            $users_query = $this->Users->find('all',array(
														'fields'=>array('id','username'),
														//'recursive' => -1
														));
			// pr($users);
			$users_query =  $users_query->hydrate(false);
            if(!empty($users_query)){
                $users = $users_query->toArray();
            }else{
                $users = array();
            }
           
            foreach($users as $user){
                $userArr[] = array(
                            'id' => $user['id'],
                            'username' => $user['username'],
                            'hours' => $this->View_last_month_hours($user['id']),
                            'Days' => $this->View_last_month_day($user['id'])
                           );
            }
			 
		}else{
            
			if(!empty($searchKW) && empty($month) ){
                
				$users_query = $this->Users->find('all',array(
															'fields'=>array('id','username'),
															 'conditions' => $conditionArr ,
														//	'recursive' => -1
															));
                $users_query =  $users_query->hydrate(false);
                if(!empty($users_query)){
                    $users = $users_query->toArray();
                }else{
                    $users = array();
                }
               
                foreach($users as $user){
						$userArr[] = array(
										'id' => $user['id'],
										'username' => $user['username'],
										'hours' => $this->View_last_month_hours($user['id']),
										'Days' => $this->View_last_month_day($user['id'])
									   );
						
					}
					
			}elseif(!empty($month) && !empty($searchKW)){
				$users_query = $this->Users->find('all',array(
															'fields'=>array('id','username'),
															 'conditions' => $conditionArr ,
															//'recursive' => -1
															));
                $users_query =  $users_query->hydrate(false);
                if(!empty($users_query)){
                    $users = $users_query->toArray();
                }else{
                    $users = array();
                }
                 if(!empty ($users)){
                    foreach($users as $user){
						$userArr[] = array(
										'id' => $user['id'],
										'username' => $user['username'],
										'hours' => $this->View_last_month_hours_search($user['id'],$month),
										'Days' => $this->View_last_month_day_search($user['id'],$month)
									   );
						
					}
                }
				
					//pr($userArr);
			}
			elseif(!empty($month) && empty($searchKW)){
				$users_query = $this->Users->find('all',array(
															'fields'=>array('id','username'),
															// 'conditions' => $conditionArr ,
														//	'recursive' => -1
															));
                $users_query =  $users_query->hydrate(false);
                if(!empty($users_query)){
                    $users = $users_query->toArray();
                }else{
                    $users = array();
                }
				foreach($users as $user){
						$userArr[] = array(
										'id' => $user['id'],
										'username' => $user['username'],
										'hours' => $this->View_last_month_hours_search($user['id'],$month),
										'Days' => $this->View_last_month_day_search($user['id'],$month)
									   );
						
					}
					//pr($userArr);
			}
			
		}
        $usersname_query = $this->Users->find('list', array(
													 'keyField' => 'id',
													 'valueField' => 'username'
													 ));
        $usersname_query = $usersname_query->hydrate(false);
        if(!empty($usersname_query)){
              $usersname = $usersname_query->toArray();
        }else{
              $usersname = array();
        }
		
		$this->set(compact('userArr','users','username'));
		//$this->layout = 'default'; 
		$this->render('viewlastmonth');
	}
    public function searchLastMonth($id = null,$mnth = null){
		$userID = $id;
		if (!$this->Users->exists($id)) {
		   throw new NotFoundException(__('Invalid User'));
		}
        $query = $this->UserAttendances->find('all', [
                                                'conditions' => array('id' => $id),
                                            ]);
        $query = $query->hydrate(false);
        if(!empty($query)){
            $userAttendance = $query->first();
        }
        $users_query = $this->Users->find('all', [
                                                'conditions' =>array('id' => $userID),
                                                'fields' => array( 'username'),
                                                'recursive' => -1
                                            ]);
        $users_query = $users_query->hydrate(false);
        if(!empty($users_query)){
            $users = $users_query->first();
        }else{
            $users = array();
        }
        
		$kiosks_query = $this->Kiosks->find('list',[
                                                    'keyField' => 'id',
													 'valueField' => 'name',
        ]);
         if(!empty($kiosks_query)){
            $kiosks = $kiosks_query->toArray();
        }else{
            $kiosks = array();
        }
        $conn = ConnectionManager::get('default');                                
		$query = "SELECT
						DISTINCT(DATE_FORMAT(`logged_in`,'%Y-%m-%d'))  as days,
						SEC_TO_TIME(round(TIMESTAMPDIFF(SECOND, `logged_in`,`day_off`), 2)) as Hours,
						DATE_FORMAT(`logged_in`,'%h:%i %p')   as login_time,
						CASE WHEN (day_off = '0000-00-00 00-00-00' OR day_off is null) THEN '---' ELSE DATE_FORMAT(`day_off`,'%h:%i %p' ) END as dayoff ,`kiosk_id`
						FROM `user_attendances` WHERE `user_id` =  '$userID' AND DATE_FORMAT(`logged_in`,'%Y-%m') = '$mnth' group by DATE_FORMAT(`logged_in`,'%Y-%m-%d')";
		//$last_month = $this->UserAttendance->query($query);
        $this->loadModel('UserAttendances');
        $stmt = $conn->execute($query);
        $last_month = $stmt ->fetchAll('assoc');
		$this->set(compact('users','userID','last_month' ,'kiosks','mnth'));
	}
    
    public function export($id = ''){
		$userID = $id;
         $conn = ConnectionManager::get('default'); 
		$query = "SELECT
						DISTINCT(DATE_FORMAT(`logged_in`,'%Y-%m-%d'))  as days,
						DATE_FORMAT(`logged_in`,'  %h:%i %p')   as login_time,
						CASE WHEN (day_off = '0000-00-00 00-00-00' OR day_off is null) THEN '---' ELSE DATE_FORMAT(`day_off`,'%h:%i %p' ) END as dayoff,
						round(TIMESTAMPDIFF(MINUTE, `logged_in`,`day_off`)/60, 2) as Hours
					FROM `user_attendances` WHERE `user_id` = '$userID'  AND DATE_FORMAT(`logged_in`,'%Y-%m') = DATE_FORMAT(CURRENT_DATE(),'%Y-%m') ";
		$stmt = $conn->execute($query);
        $current_month = $stmt ->fetchAll('assoc');
		$sngMonthArr = array();
       foreach($current_month as $key => $sngcurrent_month){
			$sngMonthArr[] = $sngcurrent_month;
		}
		
		$this->outputCsv("user_".$userID."_".time().".csv" ,$sngMonthArr);
		$this->autoRender = false;
	}
	
	public function update_daily_target(){
		//echo"hi";die;
		$kioskId = $this->request->Session()->read('kiosk_id');
		$userID = $this->Auth->User('id');
		$date = date('Y-m-d');
		$product_sale   = $this->product_sale();
		$mobile_unlock_sale =  $this->mobile_unlock_sale(); 
		$mobile_sale = $this->mobile_sale();
		$mobile_repair_sale = $this->mobile_repair_sale();
		$mobile_refund = $this->mobile_refund();
			
			if($mobile_refund < 0){
				$mobile_refund = $mobile_refund * (-1); 
			}
			
			$mobile_unlock_refund= $this->mobile_unlock_refund();
			if($mobile_unlock_refund < 0){
				$mobile_unlock_refund = $mobile_unlock_refund * (-1); 
			}
			
			$mobile_repair_refund = $this->mobile_repair_refund();
			if($mobile_repair_refund < 0){
				$mobile_repair_refund = $mobile_repair_refund*(-1); 
			}
			
			$total_sale =  $mobile_sale + $mobile_repair_sale + $product_sale + $mobile_unlock_sale;
			$total_refund = $mobile_refund + $mobile_repair_refund + $mobile_unlock_refund;
			$daily_target =  "UPDATE daily_targets SET
										`product_sale` = '$product_sale' ,
										`mobile_sale` = '$mobile_sale',
										`mobile_repair_sale` = '$mobile_repair_sale',
										`mobile_unlock_sale`= '$mobile_unlock_sale',
										`mobile_refund` = '$mobile_refund',
										`mobile_repair_refund` = '$mobile_repair_refund',
										`mobile_unlock_refund` ='$mobile_unlock_refund',
										`total_refund` = '$total_refund',
										`total_sale` = '$total_sale',
										`user_id` = $userID
							WHERE `kiosk_id` = '$kioskId' and DATE(`target_date`) = CURDATE()";
			//$target = $this->DailyTarget->query($daily_target);
			$conn = ConnectionManager::get('default');
			$stmt = $conn->execute($daily_target); 
			//$currentTimeInfo = $stmt ->fetchAll('assoc');
	}
	
	public function product_sale(){
		$kioskId = $this->request->Session()->read('kiosk_id');
		$receiptTable = "kiosk_{$kioskId}_product_receipts";
		$date = date('Y-m-d');
		$query =  "SELECT SUM(`bill_amount`) as `bill_amount` FROM $receiptTable WHERE DATE(`created`) = CURDATE()";
		//$results = $this->KioskProductSale->query($query);
		$conn = ConnectionManager::get('default');
		$stmt = $conn->execute($query); 
		$results = $stmt ->fetchAll('assoc');
		//pr($results);die;
		if(array_key_exists('0', $results)){
			return $results['0']['bill_amount'];
		}
		return 0;
	}
	 
	public function mobile_unlock_sale(){
		$kioskId = $this->request->Session()->read('kiosk_id');
		$date = date('Y-m-d');
		$query =  "SELECT SUM(`amount`) as `amount` FROM `mobile_unlock_sales` WHERE `kiosk_id` = '$kioskId' AND DATE(`created`) = CURDATE()";
		//$results = $this->MobileUnlockSale->query($query);
		$conn = ConnectionManager::get('default');
		$stmt = $conn->execute($query); 
		$results = $stmt ->fetchAll('assoc');
		if(array_key_exists('0', $results)){
			return $results['0']['amount'];
		}
		return 0;
	}
	
	public function mobile_unlock_refund(){
		$kioskId = $this->request->Session()->read('kiosk_id');
		$date = date('Y-m-d');
		$query =  "SELECT SUM(`refund_amount`) as `refund_amount` from `mobile_unlock_sales` WHERE `kiosk_id` = '$kioskId' AND DATE(`created`) = CURDATE()";
		//$results = $this->MobileUnlockSale->query($query);
		$conn = ConnectionManager::get('default');
		$stmt = $conn->execute($query); 
		$results = $stmt ->fetchAll('assoc');
		if(array_key_exists('0', $results)){
			return $results['0']['refund_amount'];
		}
		return 0;
	}
	public function mobile_sale(){
		$kioskId = $this->request->Session()->read('kiosk_id');
		$date = date('Y-m-01 ');
		//$query = "SELECT CASE WHEN (`discounted_price` is not null) THEN SUM(`discounted_price`) ELSE SUM(`selling_price`) as `selling_price` WHERE `kiosk_id` = '$kioskId' AND DATE(`created`) = CURDATE()";
		$query = "SELECT CASE WHEN (`discounted_price` >0.00) THEN `discounted_price` ELSE `selling_price` END as selling_price_1 FROM `mobile_re_sales` WHERE `kiosk_id` = '$kioskId' AND DATE(`created`) = CURDATE()";
		//$results = $this->MobileReSale->query($query);
		$conn = ConnectionManager::get('default');
		$stmt = $conn->execute($query); 
		$results = $stmt ->fetchAll('assoc');
		$totalMobileSale = 0;
		if($results){
			foreach($results as $key=>$resultData){
				$totalMobileSale+= $resultData['selling_price_1'];
			}
		}
		return $totalMobileSale;
	}
	
	public function mobile_refund(){
		$kioskId = $this->request->Session()->read('kiosk_id');
		$date = date('Y-m-d');
		$query =  "SELECT SUM(`refund_price`) as `refund price` from `mobile_re_sales` WHERE `kiosk_id` = '$kioskId' AND DATE(`created`) = CURDATE()";
		//$results = $this->MobileReSale->query($query);
		$conn = ConnectionManager::get('default');
		$stmt = $conn->execute($query); 
		$results = $stmt ->fetchAll('assoc');
		if(array_key_exists('0', $results)){
			return   $results['0']['refund price'];
		}
		return 0;
	}
	
	public function mobile_repair_sale(){
		$kioskId = $this->request->Session()->read('kiosk_id');
		$date = date('Y-m-d');
		$query =  "SELECT SUM(`amount`) as `amount` from `mobile_repair_sales` WHERE  `kiosk_id` = '$kioskId' AND DATE(`created`) = CURDATE()";
		//$results = $this->MobileRepairSale->query($query);
		$conn = ConnectionManager::get('default');
		$stmt = $conn->execute($query); 
		$results = $stmt ->fetchAll('assoc');
		if(array_key_exists('0', $results)){
			return $results['0']['amount'];
		}
		return 0;
	}
	
	public function mobile_repair_refund(){
		$kioskId = $this->request->Session()->read('kiosk_id');
		$date = date('Y-m-d');
		$query =  "SELECT SUM(`refund_amount`)  as `refund_amount` from `mobile_repair_sales` WHERE `kiosk_id` = '$kioskId' AND DATE(`created`) = CURDATE()";
		//$results = $this->MobileRepairSale->query($query);
		$conn = ConnectionManager::get('default');
		$stmt = $conn->execute($query); 
		$results = $stmt ->fetchAll('assoc');
		if(array_key_exists('0', $results)){
			return $results['0']['refund_amount'];
		}
		return 0;
	}
	
	function getChildren($parent_id) {
		  $tree = Array();
		  if (!empty($parent_id)) {
			  $tree = $this->getOneLevel($parent_id);
			  foreach ($tree as $key => $val) {
				  $ids = $this->getChildren($val);
					$tree = array_merge($tree, $ids);
			  }
		  }
		  return $tree;
	 }
	 
	 function getOneLevel($catId){
		  $cat_id = array();
		  $res = $this->Users->find('all',array('conditions' => array('parent_id' => $catId)))->toArray();
		  if(!empty($res)){
			   foreach($res as $key => $value){
					$cat_id[] = $value->id;	
			   }
		  }
		  return $cat_id;
	  }
	
}
