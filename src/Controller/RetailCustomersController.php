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
use Cake\Routing\Router;
use Cake\Datasource\ConnectionManager;


class RetailCustomersController  extends AppController
{
    public $helpers = [
         'Paginator' => ['templates' => 'paginatortemplates']
         ];
       public function initialize(){
        parent::initialize();
        $paymentType=Configure::read('payment_type');
	   $newDiscountArr = array();
	    for($i=0; $i<=50; $i++){
			  $newDiscountArr[$i] = "$i %";
		  }
		Configure::write('new_discount',$newDiscountArr);
		$newDiscountArr = Configure::read('new_discount');
		$countryOptions = Configure::read('uk_non_uk');
		$this->set(compact('paymentType','newDiscountArr','countryOptions'));
        $this->set(compact('paymentType'));
		
        $this->loadComponent('ScreenHint');
		$this->loadComponent('SessionRestore');
		$this->loadComponent('TableDefinition');
		
        $this->loadModel('ProductReceipts');
        $this->loadModel('Customers');
        $this->loadModel('PaymentDetails');
        $this->loadModel('Users');
		$this->loadModel('KioskProductSales');
        $this->loadModel('Categories');
        $this->loadModel('Kiosks');
		$this->loadModel('ProductReceipts');
        $this->loadModel('PaymentDetails');
        $this->loadModel('ProductPayments');
        $this->loadModel('ProductReceipts');
        $this->loadModel('SaleLogs');
		$this->loadModel('FaultyProduct');
		$this->loadModel('RetailCustomers');
		$this->loadModel("MobileRepairs");
		$this->loadModel("MobileRepairSales");
		$this->loadModel("MobileUnlocks");
		$this->loadModel("MobileUnlockSales");
		$this->loadModel("MobileReSales");
		//$this->loadModel("MobileRepairs");
		//$this->loadModel("MobileRepairs");
		//$this->loadModel("MobileRepairs");
    }
    
    public function index() {
		$this->paginator = array(
						'limit' => ROWS_PER_PAGE,
						   );
        $retailcustomers_query = $this->paginate("RetailCustomers");
        $retailcustomers = $retailcustomers_query->toArray();
		$this->set('retailcustomers',$retailcustomers);
	}
    public function getCustomerAjax(){
		$cust_email = trim(strtolower($this->request->query['cust_email']));
		$retCustomers_query = $this->RetailCustomers->find('all',array('conditions' => array('LOWER(`RetailCustomers`.`email`)'=> "$cust_email")
							      ));
        $retCustomers_query = $retCustomers_query->hydrate(false);
        $retCustomers = $retCustomers_query->first();
		if(!empty($retCustomers)){ //array_key_exists('RetailCustomer',$retCustomers)
			$customerData = $retCustomers;
		}else{
			$customerData = array('email' => $cust_email);
		}
		//$this->request->onlyAllow('ajax');
		//$this->layout = false;
		//$this->render = false;
		echo json_encode($customerData);
		die;
	}
	
	public function clearCustomerAjax(){
		if(isset($_SESSION["session_basket"]["customer"])){
			unset($_SESSION["session_basket"]["customer"]);
			echo json_encode("success");	
		}else{
			echo json_encode("falier");	
		}
		die;
	}
	
	public function search($keyword = ""){		
		$conditionArr = $this->generate_condition_array();
		$this->paginate = array(
							'conditions' => $conditionArr,
							'limit' => ROWS_PER_PAGE,
							);
		
		$retailcustomers = $this->paginate('RetailCustomers');
		$this->set(compact('retailcustomers'));
		//$this->layout = 'default';
		$this->render('index');
	}
	
	private function generate_condition_array(){
		$searchKW = trim(strtolower($this->request->query['search_kw']));
		$searchid = trim(strtolower($this->request->query['id']));
		//pr($searchKW);
		$conditionArr = array();
		if(!empty($searchKW)){
			$conditionArr ['OR']['RetailCustomers.email like'] = strtolower("%$searchKW%");
			$conditionArr ['OR']['RetailCustomers.mobile like'] = strtolower("%$searchKW%");
			$conditionArr ['OR']['RetailCustomers.fname  like'] = strtolower("%$searchKW%");
			$conditionArr ['OR']['RetailCustomers.lname  like'] = strtolower("%$searchKW%");
			$conditionArr ['OR']['RetailCustomers.zip  like'] = strtolower("%$searchKW%");
		}
		if(!empty($searchid)){
			$conditionArr ['OR']['RetailCustomers.id'] = $searchid;
		}
		return $conditionArr;
	}
	
	
	public function getCustomerDetailsAjax(){
		$search_kw = strtolower($this->request->query['search_kw']);
		$customerData_query = $this->RetailCustomers->find('all', array(
																   'conditions' => array(
																				'OR' => array('RetailCustomers.mobile' => $search_kw,
																							  'LOWER(RetailCustomers.email)' => $search_kw) 
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
			$customerData['error'] = 0;
			$data = json_encode($customerData);
		}else{
			$data = json_encode(array('error' => 1));
		}
		echo $data;
		die;
	}
	
	public function edit($id = null) {
		if (!$this->RetailCustomers->exists($id)) {
				throw new NotFoundException(__('Invalid customer'));
		}else{
			$RetailCustomersEntity = $this->RetailCustomers->get($id);
			$this->set(compact('RetailCustomersEntity'));
		}
		if ($this->request->is(array('post', 'put'))) {
			//pr($this->request);die;
			$RetailCustomerEntity = $this->RetailCustomers->patchEntity($RetailCustomersEntity,$this->request['data'],['validate' => false]);
			if ($this->RetailCustomers->save($RetailCustomerEntity)) {
				$this->Flash->error(__('The Retail customer has been saved.'));
				return $this->redirect(array('action' => 'index'));
			} else {
				$this->Flash->error(__('The Retail customer could not be saved. Please, try again.'));
				$RetailCustomerEntity = $this->RetailCustomers->find('all',array('conditions'=>array('RetailCustomers.id'=>$id)));
				$RetailCustomerEntity = $RetailCustomerEntity->hydrate(false);
				if(!empty($RetailCustomerEntity)){
					$RetailCustomer = $RetailCustomerEntity->first();
				}else{
					$RetailCustomer = array();
				}
				$customers = $this->request->data = $RetailCustomer;
			$table = $this->get_purchase_history($customers['email'], $customers['mobile'], $id);
			$this->set('table', $table);	
			}
		}else{
			$options = array('conditions' => array('RetailCustomers.id' => $id));
			$RetailCustomers_query = $this->RetailCustomers->find('all', $options);
			$RetailCustomers_query = $RetailCustomers_query->hydrate(false);
			if(!empty($RetailCustomers_query)){
				$RetailCustomers = $RetailCustomers_query->first();
			}else{
				$RetailCustomers = array();
			}
			$data = $this->request->data = $RetailCustomers;
			$table = $this->get_purchase_history($data['email'], $data['mobile'], $id);
			$this->set('table', $table);
		}
	}
	
	public function get_purchase_history($email = '', $mobile = '', $id = ''){
		

		
		$kiosks_query = $this->Kiosks->find('list', array(
														  'keyField' => 'id',
														  'valueField' => 'name',
									 //'conditions' => array('Kiosk.status' => 1),
									 //'order' => 'Kiosk.name asc'
									));
		$kiosks_query = $kiosks_query->hydrate(false);
		if(!empty($kiosks_query)){
			$kiosks = $kiosks_query->toArray();
		}else{
			$kiosks = array();
		}
		unset($kiosks['10000']);
		$refundStatus = array('0' => 'Sale', '1' => 'Refund', '2' => 'Faulty Refund');
		
		$tableHeading = "<tr>
						<th>Type</th>
						<th>Kiosk</th>
						<th>Id</th>
						<th>Status</th>
						<th>Amount</th>
						<th>Date</th>
					</tr>";
					//echo $tableHeading;
		
		$repairIds_query = $this->MobileRepairs->find('list', array(
											'valueField' => 'id',
											'conditions' => array(
														'OR' => array('MobileRepairs.customer_email' => $email,
																	  'MobileRepairs.customer_contact' => $mobile,
																	  'MobileRepairs.retail_customer_id' => $id)
													)
											)
										);
		$repairIds_query = $repairIds_query->hydrate(false);
		if(!empty($repairIds_query)){
			$repairIds = $repairIds_query->toArray();
		}else{
			$repairIds = array();
		}
		
		$repairSaleRows = '';
		if(count($repairIds)){
			$repairSaleData_query = $this->MobileRepairSales->find('all',array('conditions' => array('MobileRepairSales.mobile_repair_id IN' => $repairIds), 'order' => 'id desc'));
			$repairSaleData_query = $repairSaleData_query->hydrate(false);
			if(!empty($repairSaleData_query)){
				$repairSaleData = $repairSaleData_query->toArray();
			}else{
				$repairSaleData = array();
			}
			if(count($repairSaleData)){
				foreach($repairSaleData as $rsk => $repairSale){
					$repairAmount = ($repairSale['refund_status'] == 1) ? $repairSale['refund_amount'] : $repairSale['amount'];
					$repairDate = date("d-m-Y", strtotime($repairSale['created']));
					$repairSaleRows.= "<tr>
											<td>Repair</td>
											<td>".$kiosks[$repairSale['kiosk_id']]."</td>
											<td><a href=".Router::url(array('controller' => 'mobile_repairs', 'action' => 'view', $repairSale['mobile_repair_id'])).">".$repairSale['mobile_repair_id']."</a></td>
											<td>".$refundStatus[$repairSale['refund_status']]."</td>
											<td>&#163; ".number_format($repairAmount,2)."</td>
											<td>".$repairDate."</td>
									</tr>";
				}
			}
		}
		
		$unlockIds_query = $this->MobileUnlocks->find('list', array(
											'valueField' => 'id',
											'conditions' => array(
														'OR' => array('MobileUnlocks.customer_email' => $email,
																	  'MobileUnlocks.customer_contact' => $mobile,
																	  'MobileUnlocks.retail_customer_id' => $id)
													)
											)
										);
		$unlockIds_query = $unlockIds_query->hydrate(false);
		if(!empty($unlockIds_query)){
			$unlockIds = $unlockIds_query->toArray();
		}else{
			$unlockIds = array();
		}
		
		$unlockSaleRows = '';
		if(count($unlockIds)){
			$unlockSaleData_query = $this->MobileUnlockSales->find('all',array('conditions' => array('MobileUnlockSales.mobile_unlock_id IN' => $unlockIds),'order' => 'id desc'));
			$unlockSaleData_query = $unlockSaleData_query->hydrate(false);
			if(!empty($unlockSaleData_query)){
				$unlockSaleData = $unlockSaleData_query->toArray();
			}else{
				$unlockSaleData = array();
			}
			if(count($unlockSaleData)){
				foreach($unlockSaleData as $usk => $unlockSale){
					$unlockAmount = ($unlockSale['refund_status'] == 1) ? $unlockSale['refund_amount'] : $unlockSale['amount'];
					$unlockDate = date("d-m-Y", strtotime($unlockSale['created']));
					$unlockSaleRows.= "<tr>
											<td>Unlock</td>
											<td>".$kiosks[$unlockSale['kiosk_id']]."</td>
											<td><a href=".Router::url(array('controller' => 'mobile_unlocks', 'action' => 'view', $unlockSale['mobile_unlock_id'])).">".$unlockSale['mobile_unlock_id']."</a></td>
											<td>".$refundStatus[$unlockSale['refund_status']]."</td>
											<td>&#163; ".number_format($unlockAmount,2)."</td>
											<td>".$unlockDate."</td>
									</tr>";
				}
			}
		}
		
		$mobileSaleData_query = $this->MobileReSales->find('all', array(
											'conditions' => array(
														'OR' => array('MobileReSales.customer_email' => $email,
																	  'MobileReSales.customer_contact' => $mobile,
																	  'MobileReSales.retail_customer_id' => $id)
													),
														'order' => 'id desc',
														'recursive' => -1
											)
										);
		$mobileSaleData_query = $mobileSaleData_query->hydrate(false);
		if(!empty($mobileSaleData_query)){
			$mobileSaleData =  $mobileSaleData_query->toArray();
		}else{
			$mobileSaleData = array();
		}
		$mobileSaleRows = '';
		if(count($mobileSaleData)){
			foreach($mobileSaleData as $msk => $mobileSale){
				$saleAmount = ($mobileSale['refund_status'] == 1) ? $mobileSale['refund_price'] : (is_numeric($mobileSale['discounted_price']) && $mobileSale['discounted_price'] > 0) ? $mobileSale['discounted_price'] : $mobileSale['selling_price'];
				$saleDate = date("d-m-Y", strtotime($mobileSale['created']));
				$kiosk_nme = (array_key_exists($mobileSale['kiosk_id'], $kiosks)) ? $kiosks[$mobileSale['kiosk_id']] : "Warehouse";
				$mobileSaleRows.= "<tr>
										<td>Mobile Sale</td>
										<td>".$kiosk_nme."</td>
										<td><a href=".Router::url(array('controller' => 'mobile_re_sales', 'action' => 'view', $mobileSale['id'])).">".$mobileSale['id']."</a></td>
										<td>".$refundStatus[intval($mobileSale['refund_status'])]."</td>
										<td>&#163; ".number_format($saleAmount,2)."</td>
										<td>".$saleDate."</td>
								</tr>";
			}
		}
		
		$productSaleRows = '';
		foreach($kiosks as $kioskId => $kioskName){
			
			$KioskProductSale_source = "kiosk_{$kioskId}_product_sales";
			$ProductReceipt_Source = "kiosk_{$kioskId}_product_receipts";
			$KioskProductSaleTable = TableRegistry::get($KioskProductSale_source,[
                                                                                    'table' => $KioskProductSale_source,
                                                                                ]);
			$ProductReceiptTable = TableRegistry::get($ProductReceipt_Source,[
                                                                                    'table' => $ProductReceipt_Source,
                                                                                ]);
			
			$receiptIds_query = $ProductReceiptTable->find('list', array(
											'valueField' => 'id',
											'conditions' => array(
														'OR' => array('email' => $email,
																	  'mobile' => $mobile,
																	  'retail_customer_id' => $id)
													),
											'order' => 'id desc'
											)
										);
			$receiptIds_query = $receiptIds_query->hydrate(false);
			if(!empty($receiptIds_query)){
				$receiptIds = $receiptIds_query->toArray();
			}else{
				$receiptIds = array();
			}
			
			
			
			if(count($receiptIds)){
				
				$productSaleData_query = $KioskProductSaleTable->find('all',array('conditions' => array('product_receipt_id IN' => $receiptIds), 'order' => 'id desc'));
				$productSaleData_query = $productSaleData_query->hydrate(false);
				
				if(!empty($productSaleData_query)){
					$productSaleData = $productSaleData_query->toArray();
				}else{
					$productSaleData = array();
				}
				
				if(count($productSaleData)){
					foreach($productSaleData as $psk => $productSale){
						if($kioskId == $this->request->Session()->read('kiosk_id')){
							$primaryId = "<td><a href=".Router::url(array('controller' => 'product_receipts', 'action' => 'view', $productSale['product_receipt_id'])).">".$productSale['product_receipt_id']."</a></td>";
						}else{
							$primaryId = "<td>".$productSale['product_receipt_id']."</td>";
						}
						$productSaleAmount = ($productSale['refund_status'] == 1 || $productSale['refund_status'] == 2) ? $productSale['refund_price'] : $productSale['sale_price'] - $productSale['sale_price'] * $productSale['discount']/100;
						$productSaleDate = date("d-m-Y", strtotime($productSale['created']));
						$productSaleRows.= "<tr>
												<td>Accessory</td>
												<td>".$kiosks[$kioskId]."</td>"
												.$primaryId.
												"<td>".$refundStatus[$productSale['refund_status']]."</td>
												<td>&#163; ".number_format($productSaleAmount,2)."</td>
												<td>".$productSaleDate."</td>
										</tr>";
					}
				}
			}
			
		}
		
		return $table = "<table>".$tableHeading.$repairSaleRows.$unlockSaleRows.$mobileSaleRows.$productSaleRows."</table>";
	}
	
	public function view($id = null) {
		if (!$this->RetailCustomers->exists($id)) {
			throw new NotFoundException(__('Invalid customer'));
		}
		$options = array('conditions' => array('RetailCustomers.id'=> $id));
		$RetailCustomersEntity = $this->RetailCustomers->find('all', $options);
		$RetailCustomersEntity = $RetailCustomersEntity->hydrate(false);
		if(!empty($RetailCustomersEntity)){
			$RetailCustomers = $RetailCustomersEntity->first();
		}else{
			$RetailCustomers = array();
		}
		$this->set('retailcustomer', $RetailCustomers);
	}
    
    public function add() {
       // pr($this->request['data']);die;
       if ($this->request->is('post')) {
        //pr($this->request);die;
           $new_entity = $this->RetailCustomers->newEntity($this->request->data,['validate' => false]);
           $patch_entity = $this->RetailCustomers->patchEntity($new_entity,$this->request->data,['validate' => false]);
           //pr($patch_entity);die;
               if ($this->RetailCustomers->save($patch_entity)) {
                   $this->Flash->success(__('The Retail customer has been saved.'));
                   return $this->redirect(array('action' => 'index'));
               } else {
                   $this->Flash->error(__('The Retail customer could not be saved. Please, try again.'));
           }
       }
        
	}
    
    public function custemail($search = ""){
		if(array_key_exists('search',$this->request->query)){
			$search = strtolower($this->request->query['search']);
		}
		$userArr = array();
		if(!empty($search)){
           $query = $this->RetailCustomers->find('all',array(
										'fields' => array('id','email'),
										'conditions' =>array(
											'OR' => array('RetailCustomers.mobile like' => "%$search%",
																							  'LOWER(RetailCustomers.email) like' => "%$search%") 
																						 ),
										
										//'recursive' => -1,
										  ));
            if(!empty($query)){
                $customers = $query->toArray();
            }
        }
       $customerArr = array();
		foreach($customers as $customer){
			$customerArr[] = array('id' => $customer->id, 'email' => $customer->email);
		}
        
		echo json_encode($customerArr);
		$this->viewBuilder()->layout(false);
		die;
	}
	
	public function custemailW($search = ""){
		$this->loadModel('customers');
		if(array_key_exists('search',$this->request->query)){
			$search = strtolower($this->request->query['search']);
		}
		$userArr = array();
		if(!empty($search)){
           $query = $this->Customers->find()->where(['OR' => [
									'email like' => "%$search%",
									'fname like' => "%$search%",
									'lname like' => "%$search%",
                                    'business like' => "%$search%",
									]]);  
          
            if(!empty($query)){
                $customers = $query->toArray();
            }
        }
		//pr($customers);die;
		foreach($customers as $customer){
			$customerArr[] = array('id' => $customer->id,
								   'email' => $customer->email,
								   'fname' => $customer->fname,
								   'lname' => $customer->lname,
								   'business' => $customer->business
								   );
		}
		//pr($customerArr);die;
		echo json_encode($customerArr);
		$this->viewBuilder()->layout(false);
		die;
	}
	
}
    ?>