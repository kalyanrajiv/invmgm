<?php
namespace App\Controller;
use Cake\Core\Configure;
use Cake\Core\Configure\Engine\PhpConfig;

use App\Controller\AppController;
use Cake\Datasource\ConnectionManager;
/**
 * DailyTargets Controller
 *
 * @property \App\Model\Table\DailyTargetsTable $DailyTargets
 */
class DailyTargetsController extends AppController{
	 public function initialize(){
        parent::initialize();
		$CURRENCY_TYPE = Configure::read('CURRENCY_TYPE');
		$this->set(compact('CURRENCY_TYPE' ));
	}
    /**
     * Index method
     *
     * @return \Cake\Network\Response|null
     */
    public function index()
    {
        $this->paginate = [
            'contain' => ['Kiosks', 'Users']
        ];
        $dailyTargets = $this->paginate($this->DailyTargets);

        $this->set(compact('dailyTargets'));
        $this->set('_serialize', ['dailyTargets']);
    }
    
    public function userSaleReport() {
		//if no kiosk user and no kiosk user active this function is generating error
        $this->loadModel('Users');
        $this->loadModel('DailyTargets');
        $this->loadModel('Kiosks');
		$users_query = $this->Users->find('list',array
					   (
					    'conditions'=>array('Users.active'=>1,'group_id' => 3),
						'keyField' => 'id',
						'valueField' => 'username',
					    'order'=>'Users.id asc'));
		$users_query = $users_query->hydrate(false);
		if(!empty($users_query)){
		  $users = $users_query->toArray();
		}else{
		  $users = array();
		}
		
        $encoded_users = json_encode($users);
        $decoded_users = json_decode($encoded_users);
        //pr($decoded_users);die;
        $user_arr = array();
        foreach($decoded_users as $key => $value){
                $user_arr[$key] = $value;
        }
		$kiosks = $this->Kiosks->find('list',array('conditions'=>array('Kiosks.kiosk_type'=>1,'Kiosks.status'=>1),'fields'=>array('id','name')));
		$month = date('m');
		$daysOfMonth = date('t');
		$monthEndDay = date("Y-m-d",strtotime(date("Y-m-$daysOfMonth")));
		$userIdArr = array_keys($user_arr);
		$firstDay = date("Y-m-01");
        $userTargetData_query = $this->DailyTargets->find('all',array(
                                                                      'conditions'=>array(
                                                                                          'DailyTargets.user_id IN'=>$userIdArr,
                                                                                          'DailyTargets.user_id>0',
                                                                                          "DATE(DailyTargets.target_date) BETWEEN '$firstDay' AND '$monthEndDay'"),
                                                                      'group'=>'DailyTargets.user_id'
                                                                      )
                                                          );
		
		$userTargetData_query
						->select(['sumtarget' => $userTargetData_query->func()->sum('target')])
						->select(['sumtargetachieved' => $userTargetData_query->func()->sum('total_sale - total_refund')])
						->select(['sumaccessale' => $userTargetData_query->func()->sum('product_sale')])
                        ->select(['mobile_blk_sale' => $userTargetData_query->func()->sum('mobile_blk_refund')])
                        ->select(['sumunlocksale' => $userTargetData_query->func()->sum('mobile_unlock_sale - mobile_unlock_refund')])
						->select(['sumrepairsale' => $userTargetData_query->func()->sum('mobile_repair_sale' - 'mobile_repair_refund')])
						->select(['summobilesale' => $userTargetData_query->func()->sum('mobile_sale - mobile_refund')])
						->select(['sumgainloss' => $userTargetData_query->func()->sum('total_sale-total_refund-target')])
						->select('user_id');
		
		$userTargetData_query = $userTargetData_query->hydrate(false);
		if(!empty($userTargetData_query)){
			$userTargetData = $userTargetData_query->toArray();
		}
		//'fields'=>array(('SUM(DailyTargets.target) as sumtarget'),'SUM(DailyTargets.total_sale-DailyTarget.total_refund) as sumtargetachieved','SUM(DailyTargets.product_sale) as sumaccessale', 'SUM(DailyTargets.mobile_unlock_sale-DailyTargets.mobile_unlock_refund) as sumunlocksale','SUM(DailyTargets.mobile_repair_sale-DailyTargets.mobile_repair_refund) as sumrepairsale','SUM(DailyTargets.mobile_sale-DailyTarget.mobile_refund) as summobilesale','SUM(DailyTargets.total_sale-DailyTargets.total_refund-DailyTarget.target) as sumgainloss','DailyTargets.user_id')
		
		
		$month = Date("Y-m");
		$this->set(compact('userTargetData','users','kiosks','month'));
	}
    
    
    public function searchUserSaleReport(){
        $this->loadModel('Users');
        $this->loadModel('DailyTargets');
        $this->loadModel('Kiosks');
		$username = $this->request->query['username'];
		$month = $this->request->query['month']['month'];
		if(empty($month)){
			$month = date('Y-m');
		}
		$users_query = $this->Users->find('list',
													[
														'keyField' => 'id',
														'valueField' => 'username',
														'order'=>'Users.id desc'								
													]);
		$users_query = $users_query->hydrate(false);
		if(!empty($users_query)){
			$users = $users_query->toArray();
		}
		
		
		list($year, $onlyMonth) = explode("-",$month); //rasu
		
		$userNameArr = array_flip($users);
		$userId = '';
		if($username){
			if(array_key_exists($username,$userNameArr)){
				$userId = $userNameArr[$username];
			}
		}
		$kiosks = $this->Kiosks->find('list',array('conditions'=>array('Kiosks.kiosk_type'=>1,'Kiosks.status'=>1),'fields'=>array('id','name')));
		$daysOfMonth = date('t',strtotime(date("{$year}-{$onlyMonth}-01"))); //rasu
		$monthEndDay = date("Y-m-d",strtotime(date("{$year}-{$onlyMonth}-$daysOfMonth"))); //rasu
		$firstDay = date("{$year}-{$onlyMonth}-01"); //rasu
		if(!empty($username)){
			
			$query = $this->DailyTargets->find('all',array(
										'conditions'=>array('DailyTargets.user_id'=>$userId,
															"DATE(DailyTargets.target_date) BETWEEN '$firstDay' AND '$monthEndDay'")
															)
											   );
			
			$query
						->select(['sumtarget' => $query->func()->sum('target')])
						->select(['sumtargetachieved' => $query->func()->sum('total_sale - total_refund')])
						->select(['mobile_blk_sale' => $query->func()->sum('mobile_blk_sale - mobile_blk_refund')])
						->select(['sumaccessale' => $query->func()->sum('product_sale')])
						->select(['sumunlocksale' => $query->func()->sum('mobile_unlock_sale - mobile_unlock_refund')])
						->select(['sumrepairsale' => $query->func()->sum('mobile_repair_sale' - 'mobile_repair_refund')])
						->select(['summobilesale' => $query->func()->sum('mobile_sale - mobile_refund')])
						->select(['sumgainloss' => $query->func()->sum('total_sale-total_refund-target')])
						->select('user_id');
		}else{
		$query = $this->DailyTargets->find('all',array(
										'conditions'=>array('DailyTargets.user_id>0',"DATE(DailyTargets.target_date) BETWEEN '$firstDay' AND '$monthEndDay'"),'group'=>'DailyTargets.user_id')
											   );
			
			$query
						->select(['sumtarget' => $query->func()->sum('target')])
						->select(['sumtargetachieved' => $query->func()->sum('total_sale - total_refund')])
						->select(['sumaccessale' => $query->func()->sum('product_sale')])
						->select(['mobile_blk_sale' => $query->func()->sum('mobile_blk_sale - mobile_blk_refund')])
						->select(['sumunlocksale' => $query->func()->sum('mobile_unlock_sale - mobile_unlock_refund')])
						->select(['sumrepairsale' => $query->func()->sum('mobile_repair_sale' - 'mobile_repair_refund')])
						->select(['summobilesale' => $query->func()->sum('mobile_sale - mobile_refund')])
						->select(['sumgainloss' => $query->func()->sum('total_sale-total_refund-target')])
						->select('user_id');
		}
		
		$query = $query->hydrate(false);
		if(!empty($query)){
			$userTargetData = $query->toArray();
		}
		$this->set(compact('userTargetData','users','kiosks'));
		//pr($userTargetData);die;
		$this->render('user_sale_report');
	}
	
	public function kioskSaleReport() {
		$this->loadModel('Users');
		$this->loadModel('Kiosks');
		$users_query = $this->Users->find('list',array(
													'conditions'=>array('Users.active'=>1),
													 'keyField' => 'id',
                                                     'valueField' => 'username',
													'order'=>'Users.id desc'
												   )
												  );
		$users = $users_query->toArray();
		
		$kiosks_query = $this->Kiosks->find('list',array('conditions'=>array('Kiosks.kiosk_type'=>1,'Kiosks.status'=>1),'fields'=>array('id','name'),'order' => 'Kiosks.name asc'));
		$kiosks = $kiosks_query->toArray();
		$month = date('m');
		$daysOfMonth = date('t');
		$monthEndDay = date("Y-m-d",strtotime(date("Y-m-$daysOfMonth")));
		$firstDay = date("Y-m-01");
		foreach($kiosks as $kioskId=>$kioskName){break;}
		$kiosk = $kioskId;
		$kioskTargetData = $this->DailyTargets->find('all',array('conditions'=>array('DailyTargets.kiosk_id'=>$kioskId,"DATE(DailyTargets.target_date) BETWEEN '$firstDay' AND '$monthEndDay'")));
		
		$month = Date("Y-m"); //rasu
		$this->set(compact('kioskTargetData','users','kiosks','kiosk','month'));
	}
	
	public function searchKioskSaleReport(){
		$this->loadModel('Users');
		$this->loadModel('Kiosks');
		$kioskId = $this->request->query['kiosk'];
		$month = $this->request->query['month']['month'];
		$kiosks_query = $this->Kiosks->find('list',
													[
														'keyField' => 'id',
														'valueField' => 'name',
														'order' => 'Kiosks.name asc',
											'conditions' => array('Kiosks.kiosk_type' => 1,'Kiosks.status' => 1, 'Kiosks.id !=' => 10000),
													]);
		$users_query = $this->Users->find('list',
												[
													'keyField' => 'id',
													'valueField' => 'username',
													'order'=>'Users.id desc',
													'conditions'=>['Users.active'=>1],
												]);
		$kiosks_query = $kiosks_query->hydrate(false);
		if(!empty($kiosks_query)){
			$kiosks = $kiosks_query->toArray();
		}else{
			$kiosks = array();
		}
		$users_query = $users_query->hydrate(false);
		if(!empty($users_query)){
			$users = $users_query->toArray();
		}else{
			$users = array();
		}
		$userNameArr = array_flip($users);
		list($year, $onlyMonth)=explode("-",$month);
		$daysOfMonth = date('t',strtotime(date("$year-{$onlyMonth}-01")));
		$monthEndDay = date("Y-m-d",strtotime(date("{$year}-{$onlyMonth}-$daysOfMonth")));
		$firstDay = date("{$year}-{$onlyMonth}-01");
		$kioskTargetData = $this->DailyTargets->find('all',array('conditions'=>array('DailyTargets.kiosk_id'=>$kioskId,"DATE(DailyTargets.target_date) BETWEEN '$firstDay' AND '$monthEndDay'")));
		//$kioskTargetData_query = $kioskTargetData_query->hydrate(false);
		//if(!empty($kioskTargetData_query)){
		//	$kioskTargetData = $kioskTargetData_query->toArray();
		//}
		
		$this->set(compact('kioskTargetData','users','kiosks','month'));
		$this->render('kiosk_sale_report');
	}
	
	public function monthlyKioskSaleReport(){
		$this->loadModel('Users');
		$this->loadModel('Kiosks');
		$users_query = $this->Users->find('list',array
					   (
					    'conditions'=>array('Users.active'=>1),
					    'fields'=>array('id','username'),
					    'order'=>'Users.id desc'));
		$kiosks_query = $this->Kiosks->find('list',array('conditions'=>array('Kiosks.kiosk_type'=>1,'Kiosks.status'=>1),'fields'=>array('id','name'),'order' => 'Kiosks.name asc'));
		 $users = $users_query->toArray();
		 $kiosks = $kiosks_query->toArray();
		
		$month = date('m');
		$daysOfMonth = date('t');
		$monthEndDay = date("Y-m-d",strtotime(date("Y-m-$daysOfMonth")));
		$this->set(compact('monthEndDay'));
		$firstDay = date("Y-m-01");
		$this->set('month',$firstDay);
		
		foreach($kiosks as $kioskId=>$kioskName){
			$query = $this->DailyTargets->find('all',
									 array('conditions'=>array('DailyTargets.kiosk_id'=>$kioskId,"DATE(DailyTargets.target_date) BETWEEN '$firstDay' AND '$monthEndDay'")));
			$query
					->select(['monthly_target' => $query->func()->sum('target')])
					->select(['monthly_sale' => $query->func()->sum('total_sale')])
					->select(['monthly_refund' => $query->func()->sum('total_refund')])
					->select(['monthly_product_sale' => $query->func()->sum('product_sale')])
					->select(['monthly_product_refund' => $query->func()->sum('product_refund')])
					->select(['monthly_mobile_sale' => $query->func()->sum('mobile_sale')])
					->select(['monthly_mobile_refund' => $query->func()->sum('mobile_refund')])
					->select(['monthly_mobile_repair_sale' => $query->func()->sum('mobile_repair_sale')])
					->select(['monthly_mobile_repair_refund' => $query->func()->sum('mobile_repair_refund')])
					->select(['monthly_mobile_unlock_sale' => $query->func()->sum('mobile_unlock_sale')])
					->select(['monthly_mobile_unlock_refund' => $query->func()->sum('mobile_unlock_refund')])
					->select(['monthly_mobile_blk_sale' => $query->func()->sum('mobile_blk_sale')])
					->select(['monthly_mobile_blk_refund' => $query->func()->sum('mobile_blk_refund')])
					->select('kiosk_id');
			$query = $query->hydrate(false);
			if(!empty($query)){
					$kioskTargetData[] = $query->toArray();
			}
		}
		//pr($kioskTargetData);die;
		$month = Date("Y-m");
		$this->set(compact('kioskTargetData','users','kiosks','kiosk','month'));
	}
    
    /**
     * View method
     *
     * @param string|null $id Daily Target id.
     * @return \Cake\Network\Response|null
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view($id = null)
    {
        $dailyTarget = $this->DailyTargets->get($id, [
            'contain' => ['Kiosks', 'Users']
        ]);

        $this->set('dailyTarget', $dailyTarget);
        $this->set('_serialize', ['dailyTarget']);
    }

    /**
     * Add method
     *
     * @return \Cake\Network\Response|null Redirects on successful add, renders view otherwise.
     */
    public function add()
    {
        $dailyTarget = $this->DailyTargets->newEntity();
        if ($this->request->is('post')) {
            $dailyTarget = $this->DailyTargets->patchEntity($dailyTarget, $this->request->data);
            if ($this->DailyTargets->save($dailyTarget)) {
                $this->Flash->success(__('The daily target has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The daily target could not be saved. Please, try again.'));
        }
        $kiosks = $this->DailyTargets->Kiosks->find('list', ['limit' => 200]);
        $users = $this->DailyTargets->Users->find('list', ['limit' => 200]);
        $this->set(compact('dailyTarget', 'kiosks', 'users'));
        $this->set('_serialize', ['dailyTarget']);
    }

    /**
     * Edit method
     *
     * @param string|null $id Daily Target id.
     * @return \Cake\Network\Response|null Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Network\Exception\NotFoundException When record not found.
     */
    public function edit($id = null)
    {
        $dailyTarget = $this->DailyTargets->get($id, [
            'contain' => []
        ]);
        if ($this->request->is(['patch', 'post', 'put'])) {
            $dailyTarget = $this->DailyTargets->patchEntity($dailyTarget, $this->request->data);
            if ($this->DailyTargets->save($dailyTarget)) {
                $this->Flash->success(__('The daily target has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The daily target could not be saved. Please, try again.'));
        }
        $kiosks = $this->DailyTargets->Kiosks->find('list', ['limit' => 200]);
        $users = $this->DailyTargets->Users->find('list', ['limit' => 200]);
        $this->set(compact('dailyTarget', 'kiosks', 'users'));
        $this->set('_serialize', ['dailyTarget']);
    }

    /**
     * Delete method
     *
     * @param string|null $id Daily Target id.
     * @return \Cake\Network\Response|null Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete($id = null)
    {
        $this->request->allowMethod(['post', 'delete']);
        $dailyTarget = $this->DailyTargets->get($id);
        if ($this->DailyTargets->delete($dailyTarget)) {
            $this->Flash->success(__('The daily target has been deleted.'));
        } else {
            $this->Flash->error(__('The daily target could not be deleted. Please, try again.'));
        }

        return $this->redirect(['action' => 'index']);
    }
	
	
	public function searchMonthlyKioskSaleReport(){
		 $this->loadModel('Users');
		//pr($this->request);die;
		if(array_key_exists('start',$this->request->query)){
			$month = $this->request->query['start'];
			$this->set(compact('month'));
			$firstDay = date("Y-m-d",strtotime($month));
		}
		if(array_key_exists('end',$this->request->query)){
			$monthEndDay = $this->request->query['end'];
			$this->set(compact('monthEndDay'));
			$monthEndDay = date('Y-m-d',strtotime($monthEndDay));
		}
		
		
		
		//list($year, $onlyMonth)=explode("-",$month);
		if(is_array($this->request->query['kiosk'])){
			$resultKiosks = $this->request->query['kiosk'];
			$kioskNames_query = $this->Kiosks->find('list',
														[
															'keyField' => 'id',
															'valueField' => 'name',
															'conditions' => ['Kiosks.id IN'=>$resultKiosks,'Kiosks.kiosk_type'=>1,'Kiosks.status'=>1],
														]);								  
		}else{
			$kioskNames_query = $this->Kiosks->find('list',
											  [
												'keyField' => 'id',
												'valuefield' => 'name',
												'conditions' => ['Kiosks.kiosk_type'=>1,'Kiosks.status'=>1],
												'order' => 'Kiosks.name asc'
											  ]);
		}
		
		$kioskNames_query = $kioskNames_query->hydrate(false);
		if(!empty($kioskNames_query)){
			$kioskNames = $kioskNames_query->toArray();
		}else{
			$kioskNames = array();
		}
		
		//for showing all the kiosks in frontend
		$kiosks_query = $this->Kiosks->find('list',
											[
												'keyField' => 'id',
												'valueField' => 'name',
												'conditions' => ['Kiosks.kiosk_type'=>1,'Kiosks.status'=>1],
												'order' => 'Kiosks.name asc'
											]);
		$kiosks_query = $kiosks_query->hydrate(false);
		if(!empty($kiosks_query)){
			$kiosks = $kiosks_query->toArray();
		}else{
			$kiosks = array();
		}
		
		$users_query = $this->Users->find('list',
												[
													'keyField' => 'id',
													'valueField' => 'username',
													'conditions' => ['Users.active'=>1],
													'order'=>'Users.id desc',
												]);
		$kioskTargetData = array();
		foreach($kioskNames as $kioskId=>$kioskName){
			
			//$kioskTargetData[] =
			
			$query = $this->DailyTargets->find('all',
									 array('conditions'=>array('DailyTargets.kiosk_id'=>$kioskId,"DATE(DailyTargets.target_date) BETWEEN '$firstDay' AND '$monthEndDay'")));
			
			$query
					->select(['monthly_target' => $query->func()->sum('target')])
					->select(['monthly_sale' => $query->func()->sum('total_sale')])
					->select(['monthly_refund' => $query->func()->sum('total_refund')])
					->select(['monthly_product_sale' => $query->func()->sum('product_sale')])
					->select(['monthly_product_refund' => $query->func()->sum('product_refund')])
					->select(['monthly_mobile_sale' => $query->func()->sum('mobile_sale')])
					->select(['monthly_mobile_refund' => $query->func()->sum('mobile_refund')])
					->select(['monthly_mobile_repair_sale' => $query->func()->sum('mobile_repair_sale')])
					->select(['monthly_mobile_blk_sale' => $query->func()->sum('mobile_blk_sale')])
					->select(['monthly_mobile_blk_refund' => $query->func()->sum('mobile_blk_refund')])
					->select(['monthly_mobile_repair_refund' => $query->func()->sum('mobile_repair_refund')])
					->select(['monthly_mobile_unlock_sale' => $query->func()->sum('mobile_unlock_sale')])
					->select(['monthly_mobile_unlock_refund' => $query->func()->sum('mobile_unlock_refund')])
					->select('kiosk_id');
			
			$query = $query->hydrate(false);
			if(!empty($query)){
				$kioskTargetData[] = $query->toArray();
			}
		}
		//pr($kioskTargetData);die;
		$this->set(compact('kioskTargetData','users','kiosks','kiosk','month'));
		$this->render('monthly_kiosk_sale_report');
	}
	
	public function userSaleDetail(){
		$this->loadModel('Users');
		$this->loadModel('Kiosks');
		
		
		$username = $this->request->query['username'];
		$users_query = $this->Users->find('list',
											[
												'keyField' => 'id',
												'valueField' => 'username',
												'conditions'=>['Users.active'=>1],
												'order'=>'Users.id desc'
											]);
		$users_query = $users_query->hydrate(false);
		if(!empty($users_query)){
			$users = $users_query->toArray();
		}
		
		
		$kiosks_query = $this->Kiosks->find('list',
											[
												'keyField' => 'id',
												'valueField' => 'name',
												'conditions'=>['Kiosks.kiosk_type'=>1,'Kiosks.status'=>1],
											]);
		
		$kiosks_query = $kiosks_query->hydrate(false);
		if(!empty($kiosks_query)){
			$kiosks = $kiosks_query->toArray();
		}
		
		$userNameArr = array_flip($users);
		$userId = '';
		if($username){
			if(array_key_exists($username,$userNameArr)){
				$userId = $userNameArr[$username];
			}
		}
		
		if(!empty($this->request->query['month'])){
			$month = $this->request->query['month'];
			list($year, $onlyMonth) = explode("-",$month); //rasu
			$daysOfMonth = date('t',strtotime(date("{$year}-{$onlyMonth}-01"))); //rasu
			$monthEndDay = date("Y-m-d",strtotime(date("{$year}-{$onlyMonth}-$daysOfMonth"))); //rasu
			$firstDay = date("{$year}-{$onlyMonth}-01"); //rasu
		}else{
			$month = Date("Y-m");
			$daysOfMonth = date('t');
			$monthEndDay = date("Y-m-d",strtotime(date("Y-m-$daysOfMonth")));
			$firstDay = date("Y-m-01");
			
		}
		
		//foreach($users as $userId=>$userName){break;}
		$userTargetData_query = $this->DailyTargets->find('all',array('conditions'=>array('DailyTargets.user_id'=>$userId,'DailyTargets.user_id>0',"DATE(DailyTargets.target_date) BETWEEN '$firstDay' AND '$monthEndDay'")));
		$userTargetData_query = $userTargetData_query->hydrate(false);
		if(!empty($userTargetData_query)){
			$userTargetData = $userTargetData_query->toArray();
		}
		$this->set(compact('userTargetData','users','kiosks','month','userId'));
	}
	
	public function all() {
		$month = "";
		if($this->request->is('post','put')){
			if(array_key_exists('start_date',$this->request->data)){
				$start_date = $this->request->data['start_date'];
				$firstDay = date("Y-m-d",strtotime($start_date));
			}else{
				//
			}
			if(array_key_exists('end_date',$this->request->data)){
				$end_date = $this->request->data['end_date'];
				$monthEndDay = date("Y-m-d",strtotime($end_date));
			}else{
				//$monthEndDay =
			}
		}else{
			$daysOfMonth = date('t');
			$monthEndDay = date("Y-m-d",strtotime(date("Y-m-$daysOfMonth")));
			$firstDay = date("Y-m-01");
		}
		
		$kiosk_arr = $this->Kiosks->find("list",['conditions' =>
													   ["status" => 1],
													   'keyField' => 'id',
													   'valueField' => 'id',
									])->toArray();
		
		$kioskTargetData_query = $this->DailyTargets->find('all',array('conditions'=>array(
															"DATE(DailyTargets.target_date) BETWEEN '$firstDay' AND '$monthEndDay'",
															'kiosk_id IN' => $kiosk_arr,
															)));
		
		$kioskTargetData_query
									->select(['target' => $kioskTargetData_query->func()->sum('target')])
									->select(['product_sale' => $kioskTargetData_query->func()->sum('product_sale')])
									->select(['mobile_sale' => $kioskTargetData_query->func()->sum('mobile_sale')])
									->select(['mobile_blk_sale' => $kioskTargetData_query->func()->sum('mobile_blk_sale')])
									->select(['mobile_repair_sale' => $kioskTargetData_query->func()->sum('mobile_repair_sale')])
									->select(['mobile_unlock_sale' => $kioskTargetData_query->func()->sum('mobile_unlock_sale')])
									->select(['product_refund' => $kioskTargetData_query->func()->sum('product_refund')])
									->select(['mobile_refund' => $kioskTargetData_query->func()->sum('mobile_refund')])
									->select(['mobile_blk_refund' => $kioskTargetData_query->func()->sum('mobile_blk_refund')])
									->select(['mobile_repair_refund' => $kioskTargetData_query->func()->sum('mobile_repair_refund')])
									->select(['mobile_unlock_refund' => $kioskTargetData_query->func()->sum('mobile_unlock_refund')])
									->select(['total_sale' => $kioskTargetData_query->func()->sum('total_sale')])
									->select(['total_refund' => $kioskTargetData_query->func()->sum('total_refund')])
									->select(['total_sale' => $kioskTargetData_query->func()->sum('total_sale')])
									->select('target_date')
									->group('target_date');
									//pr($kioskTargetData_query);die;
		$kioskTargetData_query = $kioskTargetData_query->hydrate(false);
		if(!empty($kioskTargetData_query)){
			$kioskTargetData = $kioskTargetData_query->toArray();
		}
		$month = Date("Y-m"); 
		$start_date = date('Y-M-d',strtotime($firstDay));
		$end_date = date('Y-M-d',strtotime($monthEndDay));
		$this->set(compact('start_date','end_date'));
		$this->set(compact('kioskTargetData','users','kiosks','kiosk','month'));
	}
	
	
}
