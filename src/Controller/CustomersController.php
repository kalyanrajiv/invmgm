<?php
namespace App\Controller;
use App\Controller\AppController;
use Cake\Routing\Router;
use Cake\ORM\TableRegistry;
use Cake\ORM\Entity;
use Cake\Datasource\ConnectionManager;
use Cake\Core\Configure;
use Cake\Core\Configure\Engine\PhpConfig;
class CustomersController extends AppController{
    
     public $helpers = [
         'Paginator' => ['templates' => 'paginatortemplates']
         ];
    public function initialize()
    {
        parent::initialize();
        $active = Configure::read('active');
        $this->loadModel('MobileRepairs');
        $this->loadModel('MobileUnlocks');
        $this->loadModel('MobileReSales');
        $this->loadModel('Customers');
        $this->loadModel('Kiosks');
        $this->loadModel('Receipts');
		$this->loadModel('CustomerProductPrice');
	   $this->loadModel('Agents');
		$countryOptions = Configure::read('uk_non_uk');
		$this->set(compact('countryOptions', 'active'));
		
    }

	public function index() {
		$agent_query = $this->Agents->find('all', array(
										  //'conditions' =>array('Agents.id' => $agentID)
										  )
									  );
		$agent_query = $agent_query->hydrate(false);
		if(!empty($agent_query)){
			   $agents = $agent_query->toArray(); 
		}else{
			   $agents = array();
		}
		//pr($agents);die;
		$allAgents[0] = "Select Acc Manger";
		foreach($agents as $key => $agent){
			$allAgents[$agent['id']] = $agent['name'];
		}
		
		ksort($allAgents);
		$agents = 0;
		//pr($allAgents);die;
		$agentname_query = $this->Agents->find('list', array(
													   'keyField' => 'id',
                                                     'valueField' => 'name',
										  )
									  );
		$agentname_query = $agentname_query->hydrate(false);
		if(!empty($agentname_query)){
			   $agentname = $agentname_query->toArray(); 
		}else{
			   $agentname = array();
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
                //pr($agentname);die;
		$this->paginate = array('limit'=>50);
		$customers = $this->paginate($this->Customers);
                //pr($customers);die;
		$this->set(compact('customers','allAgents','agents','agentname','users'));
		$this->set('_serialize', ['customers']);
	}

    
	public function view($id = null){
		$customer = $this->Customers->get($id, [
		    'contain' => []
		]);
		$agentID = $customer->agent_id;
		$createdBy = $customer->created_by;
		$agent_query = $this->Agents->find('all', array(
										  'conditions' =>array('Agents.id' => $agentID)
										  )
									  );
		$agent_query = $agent_query->hydrate(false);
		if(!empty($agent_query)){
			   $agents = $agent_query->first(); 
		}else{
			   $agents = array();
		}
		
		$agentName = $agents['name'];
		
		$created_query = $this->Users->find('all', array(
										  'conditions' =>array('Users.id' => $createdBy)
										  )
									  );
		$created_query = $created_query->hydrate(false);
		if(!empty($created_query)){
			$created = $created_query->first(); 
		}else{
			$created = array();
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
		
		$createdByName = $created['username'];
		$this->set(compact('customer','agentName','createdByName','users'));
		$this->set('_serialize', ['customer']);
	}

    
	public function add(){
		$userId = $this->request->Session()->read('Auth.User.id');
		$this->request->data['created_by'] = $userId;
		$customer = $this->Customers->newEntity();
		if ($this->request->is('post')) {
		    $customer = $this->Customers->patchEntity($customer, $this->request->data);
		    if ($this->Customers->save($customer)) {
			   $this->Flash->success(__('The customer has been saved.'));
  
			   return $this->redirect(['action' => 'index']);
		    }
		    $this->Flash->error(__('The customer could not be saved. Please, try again.'));
		}
		
		$agent_query = $this->Agents->find('list',
										[
											'keyField' => 'id',
											'valueField' => 'name'
										]
									);
		$agent_query = $agent_query->hydrate(false);
		if(!empty($agent_query)){
			   $agents = $agent_query->toArray(); 
		}else{
			   $agents = array();
		}
		$agents[0] = "Select Acc manager";
		ksort($agents);
		$this->set(compact('customer','agents'));
		$this->set('_serialize', ['customer']);
	}

    public function edit($id = null) {
		$customer = $this->Customers->get($id, [
		    'contain' => []
		]);
		$old_agent = $customer->agent_id;
		//pr($this->request->data);die;
		if ($this->request->is(['patch', 'post', 'put'])) {
			$this->request->data['edited_by'] = $this->request->Session()->read('Auth.User.id');
			
			$new_agent = $this->request->data['agent_id'];
			   if($old_agent != $new_agent){
				$this->update_invoice_agent($old_agent,$new_agent,$id);
				$this->update_quotation_agent($old_agent,$new_agent,$id);
				$this->update_credit_note_agent($old_agent,$new_agent,$id);
				$this->update_credit_quotation_agent($old_agent,$new_agent,$id);
			   }
			
		    $customer = $this->Customers->patchEntity($customer, $this->request->data);
		    if ($this->Customers->save($customer)) {
			   $this->Flash->success(__('The customer has been saved.'));
  
			   return $this->redirect(['action' => 'index']);
		    }else{
			   $table = $this->warehouse_purchase_history($customer['email'], $customer['mobile'], $id);
			  $this->set('table', $table);
		    $this->Flash->error(__('The customer could not be saved. Please, try again.'));
		    }
		    
		}else{
			  $table = $this->warehouse_purchase_history($customer['email'], $customer['mobile'], $id);
			  $this->set('table', $table);
		}
		
		$agent_query = $this->Agents->find('list',
										[
											'keyField' => 'id',
											'valueField' => 'name'
										]
									);
		$agent_query = $agent_query->hydrate(false);
		if(!empty($agent_query)){
			   $agents = $agent_query->toArray(); 
		}else{
			   $agents = array();
		}
		$agents[0] = "Select Acc manager";
		ksort($agents);
		$this->set(compact('customer','agents'));
		$this->set('_serialize', ['customer']);
	}
	
	public function warehouse_purchase_history($email = '', $mobile = '', $id = ''){
		 $this->loadModel('ProductReceipts');
		$refundStatus = array('0' => 'Sale', '1' => 'Refund', '2' => 'Faulty Refund');
		$tableHeading = "<tr>
						<th>Type</th>
						<th>Invoice Number</th>
						<th>Amount</th>
						<th>Date</th>
					</tr>";
					
		$productSaleRows = '';
		$receiptData_query = $this->ProductReceipts->find('all', array(
										'conditions' =>array('ProductReceipts.customer_id' => $id, 'ProductReceipts.status' => 0),
										'order' => 'id desc',
										'recursive' => -1
										)
									);
		$receiptData_query->hydrate(false);
		$receiptData = $receiptData_query->toArray();
		if(count($receiptData)){
			foreach($receiptData as $key => $receiptInfo){
				$productSaleDate = date("d-m-Y", strtotime($receiptInfo['created']));
				$productSaleRows.= "<tr>
											<td>Accessory</td>
											<td><a href=".Router::url(array('controller' => 'product_receipts', 'action' => 'generate_receipt', $receiptInfo['id'])).">".$receiptInfo['id']."</a></td>
											<td>&#163; ".number_format($receiptInfo['bill_amount'],2)."</td>
											<td>".$productSaleDate."</td>
									</tr>";
			}
		}
		
		return $table = "<table>".$tableHeading.$productSaleRows."</table>";
	}
	
    public function delete($id = null) {
        $this->request->allowMethod(['post', 'delete']);
        $customer = $this->Customers->get($id);
        if ($this->Customers->delete($customer)) {
            $this->Flash->success(__('The customer has been deleted.'));
        } else {
            $this->Flash->error(__('The customer could not be deleted. Please, try again.'));
        }

        return $this->redirect(['action' => 'index']);
    }
    private function generate_condition_array(){
         
		echo $searchKW = trim(strtolower($this->request->query['search_kw']));
		 
		$conditionArr = array();
		if(!empty($searchKW)){
			//$conditionArr['Customer.brand like'] =  strtolower("%$searchKW%");
			$conditionArr ['OR']['Customers.email like'] = strtolower("%$searchKW%");
			$conditionArr ['OR']['Customers.mobile like'] = strtolower("%$searchKW%");
			$conditionArr ['OR']['Customers.business  like'] = strtolower("%$searchKW%");
		}
		return $conditionArr;
    $conditionArr = $this->generate_condition_array();
	}
    public function search($keyword = ""){
		  $searchId = $this->request->query['id'];	
		$searchKW = trim(strtolower($this->request->query['search_kw']));
		if(!empty($searchId)){
			$query = 	$this->Customers->find('all', array(
										'conditions' =>array('id' => $searchId),
										'limit' => 50
										)
									);
		}else{
			$query = $this->Customers->find()->where(['OR' => [
									   'email like' => "%$searchKW%",
									   'mobile like' => "%$searchKW%",
							    'business like' => "%$searchKW%"
									   ],
													  ]);
		}
		 $agent_query = $this->Agents->find('all', array(
										  //'conditions' =>array('Agents.id' => $agentID)
										  )
									  );
		  $agent_query = $agent_query->hydrate(false);
		  if(!empty($agent_query)){
				 $agents1 = $agent_query->toArray(); 
		  }else{
				 $agents1 = array();
		  }
		  //pr($agents);die;
		  $allAgents[0] = "Select Acc Manger";
		  foreach($agents1 as $key => $agent){
			  $allAgents[$agent['id']] = $agent['name'];
		  }
		  
		  ksort($allAgents);
		
		$agentname_query = $this->Agents->find('list', array(
													   'keyField' => 'id',
                                                     'valueField' => 'name',
										  )
									  );
		$agentname_query = $agentname_query->hydrate(false);
		if(!empty($agentname_query)){
			   $agentname = $agentname_query->toArray(); 
		}else{
			   $agentname = array();
		}
		$this->paginate = array('limit' => 50);
		$customers1 =    $this->paginate($query);
		$customers = $customers1->toArray();
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
		$this->set(compact('customers','allAgents','agents','agentname','users'));
		$this -> render('index');
		// $this->render('index');
	}
	
	public function searchAgent(){
		  $agents = "";
		  if(array_key_exists('agent',$this->request->query)){
			   $agents = $this->request->query['agent'];
		  }
		  //if(!empty($agents)){
			   $query = 	$this->Customers->find('all', array(
											 'conditions' =>array('agent_id' => $agents),
											 'limit' => 50
											 )
										 );
		//  }
	 
		  $agent_query = $this->Agents->find('all', array(
										  //'conditions' =>array('Agents.id' => $agentID)
										  )
									  );
		  $agent_query = $agent_query->hydrate(false);
		  if(!empty($agent_query)){
				 $agents1 = $agent_query->toArray(); 
		  }else{
				 $agents1 = array();
		  }
		  //pr($agents);die;
		  $allAgents[0] = "Select Acc Manger";
		  foreach($agents1 as $key => $agent){
			  $allAgents[$agent['id']] = $agent['name'];
		  }
		  
		  ksort($allAgents);
		  $agentname_query = $this->Agents->find('list', array(
													   'keyField' => 'id',
                                                     'valueField' => 'name',
										  )
									  );
		$agentname_query = $agentname_query->hydrate(false);
		if(!empty($agentname_query)){
			   $agentname = $agentname_query->toArray(); 
		}else{
			   $agentname = array();
		}
		  $this->paginate = array('limit' => 50);
		  $customers1 =    $this->paginate($query);
		$customers = $customers1->toArray();
		$this->set(compact('customers','allAgents','agents','agentname'));
		$this -> render('index');
	}
	
    public function getCustomersData(){
         $conn = ConnectionManager::get('default');
		$ispost = 0;
		if($this->request->is('post')){
			$ispost = 1;
			$postData = $this->request->data;
            //pr($postData);die;
			$fieldsArray = array();
			if($postData['customer_fname'] == 1){
				$fieldsArray[] = 'customer_fname';
				$fields1Array[] = 'fname';
			}
			if($postData['customer_lname'] == 1){
				$fieldsArray[] = 'customer_lname';
				$fields1Array[] = 'lname';
			}
			//if($postData['CustomerData']['customer_email'] == 1){ keeping email as mandatory
				$fieldsArray[] = 'customer_email';
				$fields1Array[] = 'email';
			//}
			if($postData['customer_contact'] == 1){
				$fieldsArray[] = 'customer_contact';
				$fields1Array[] = 'mobile';
			}
			
		}else{
			$fieldsArray = array('customer_fname', 'customer_lname', 'customer_email', 'customer_contact');
			$fields1Array = array('fname', 'lname', 'email', 'mobile');// for receipt and customers table
		}
		$finalArray = array();
		//data from mobile repair
		$repairData = $this->MobileRepairs->find('all', array('fields' => $fieldsArray, 'recursive' => -1, 'order' => 'id asc'));
        $repairData->hydrate(false);
        if(!empty($repairData)){
         $repairData  = $repairData->toArray();
        }
		foreach($repairData as $key => $repairInfo){
			$finalArray[$repairInfo['customer_email']] = $repairInfo;
		}
		
		//data from mobile unlock
		$unlockData = $this->MobileUnlocks->find('all', array('fields' => $fieldsArray, 'recursive' => -1, 'order' => 'id asc'));
        $unlockData->hydrate(false);
        if(!empty($unlockData)){
         $unlockData  = $unlockData->toArray();
        }
		foreach($unlockData as $key => $unlockInfo){
			$finalArray[$unlockInfo['customer_email']] = $unlockInfo;
		}
				
		//data from mobile resales
		$mobileData = $this->MobileReSales->find('all', array('fields' => $fieldsArray, 'recursive' => -1, 'order' => 'id asc'));
        $mobileData->hydrate(false);
        if(!empty($mobileData)){
         $mobileData  = $mobileData->toArray();
        }
		foreach($mobileData as $key => $mobileInfo){
			$finalArray[$mobileInfo['customer_email']] = $mobileInfo;
		}
		
		//data from customers
		$customersData = $this->Customers->find('all', array(
															 'fields' => $fields1Array,
															// 'recursive' => -1,
															 
															 'order' => 'id asc'
															 )
												);
        $customersData->hydrate(false);
        if(!empty($customersData)){
         $customersData  = $customersData->toArray();
        }
		foreach($customersData as $key => $customerInfo){
			if(in_array('fname',$fields1Array)){
				$finalArray[$customerInfo['email']]['customer_fname'] = $customerInfo['fname'];
			}
			if(in_array('lname',$fields1Array)){
				$finalArray[$customerInfo['email']]['customer_lname'] = $customerInfo['lname'];
			}
			if(in_array('email',$fields1Array)){
				$finalArray[$customerInfo['email']]['customer_email'] = $customerInfo['email'];
			}
			if(in_array('mobile',$fields1Array)){
				$finalArray[$customerInfo['email']]['customer_contact'] = $customerInfo['mobile'];
			}
		}
		
		//getting data from all the receipt tables for product sale (in case of retail accessory sale)
		$kiosks = $this->Kiosks->find('list', array('fields' => array('id', 'name')));
        $kiosks->hydrate(false);
        if(!empty($kiosks)){
         $kiosks  = $kiosks->toArray();
        }
		unset($kiosks['10000']);		
		foreach($kiosks as $kioskId => $kioskName){
           	$receiptTable_source = "kiosk_{$kioskId}_product_receipts";
            $receiptTable = TableRegistry::get($receiptTable_source,[
																	  'table' => $receiptTable_source,
																	  ]);
			$receiptData_query = $receiptTable->find('all', array('fields' => $fields1Array, 'recursive' => -1, 'order' => 'id asc'));
			$receiptData_query = $receiptData_query->hydrate(false);
			if(!empty($receiptData_query)){
				$receiptData = $receiptData_query->toArray();
			}
            if(count($receiptData)){
				foreach($receiptData as $key => $receiptInfo){
					if(!empty($receiptInfo['email'])){
						if(in_array('fname',$fields1Array)){
							 $finalArray[$receiptInfo['email']]['customer_fname'] = $receiptInfo['fname'];
						}
						if(in_array('lname',$fields1Array)){
							 $finalArray[$receiptInfo['email']]['customer_lname'] = $receiptInfo['lname'];
						}
						if(in_array('email',$fields1Array)){
							$finalArray[$receiptInfo['email']]['customer_email'] = $receiptInfo['email'];
						}
						if(in_array('mobile',$fields1Array)){
							 $finalArray[$receiptInfo['email']]['customer_contact'] = $receiptInfo['mobile'];
						}
					}
				}
			}
		}
		
		if($ispost == 1){
		 // pr($finalArray);die;
			if(count($finalArray)){
				$fileName = 'CustomerData_'.time().".csv";
				header('Content-Type: application/csv');
				header('Content-Disposition: attachment;filename=' . $fileName);
				$fp = fopen('php://output', 'a+');
				//pr($finalArray);die;
				fputcsv($fp, array_keys(current($finalArray)));
				foreach($finalArray as $key => $values){
					fputcsv($fp, $values);
				}
				fclose($fp);
			}
			die;
		}
		
		$this->set(compact('finalArray'));
	}
	
	public function export(){
		 $conditionArr = array();
		if(array_key_exists('search_kw',$this->request->query)){
			$conditionArr = $this->generate_condition_array();
  
   
		}
		if(count($conditionArr)>=1){
			$customers_query = $this->Customers->find('all',array(
									'conditions' => $conditionArr,
									));
		}else{
			$customers_query = $this->Customers->find('all');
		}
		$customers_query = $customers_query->hydrate(false);
		if(!empty($customers_query)){
		  $customers = $customers_query->toArray();
		}else{
		  $customers = array();
		}
		//pr($customers);
		$tmpcustomers = array(); 
		foreach($customers as $key => $customer){
		  //unset($customer['modified']);
		  //unset($customer['created']);
		 $tmpcustomers[] = $customer;
		}
		//pr($tmpcustomers);die;
		
		$this->outputCsv('Customer_'.time().".csv" ,$tmpcustomers);
		$this->autoRender = false;
	}
	
	public function getAddress(){
	 $address_api_credentials = Configure::read('address_api_credentials');
	 
		$zipCode = strtoupper($this->request->query('zip'));
		$account = $address_api_credentials['account'];//"2643";
		$password = $address_api_credentials['password'];
		if(empty($zipCode))$zipCode = 'LS18 4AA';
		$URL = "http://ws1.postcodesoftware.co.uk/lookup.asmx/getAddress?account=$account&password=$password&postcode=$zipCode";
		$xml = simplexml_load_file(str_replace(' ','', $URL));
		
		$chunks = explode (";", $xml->PremiseData);
		$addressArr = $address1Arr = array();
		$errorNumber = @(String)$xml->ErrorNumber;
		if(!empty($errorNumber)){
			$addressArr['ErrorMessage'] = @(String)$xml->ErrorMessage;
			$addressArr['ErrorNumber'] = $errorNumber;
		}else{
			$addressArr['ErrorNumber'] = 0;
			foreach($chunks as $key => $v){
				$address1Str = "";
				if($key == 0){$address1Arr[0] = "--Choose Address--";}
				if ($v <> ""){
					list($organisation, $building , $number) = explode("|",$v);//split('[|]', $v);
					if ($organisation <> "")$address1Str.=$organisation . ", ";
					if ($building <> "")$address1Str.= str_replace("/",", ",$building) . ", ";
					if ($number <> "")$address1Str.= $number . " ";
					$address1Str.= $xml->Address1;
					$address1Arr[$key+1] = $address1Str;	
				}		
			}
			$addressArr['Street'] = $address1Arr;		
			if($xml->Address2 <> ""){$addressArr['Address2'] = @(String)$xml->Address2;}
			if($xml->Address3 <> ""){$addressArr['Address3'] = @(String)$xml->Address3;}
			if($xml->Address4 <> ""){$addressArr['Address4'] = @(String)$xml->Address4;}
			$addressArr['Town'] = @(String)$xml->Town;
			$addressArr['County'] = @(String)$xml->County;
			$addressArr['Postcode'] = @(String)$xml->Postcode;
		}
		//end of creating street array				
		echo json_encode($addressArr);
		die();
	}
	
	public function customerBasePrice(){
	 
		  $cust_data = $this->Customers->find('list',['keyField' => 'id',
										'valueField' => 'business',
										])->toArray();
		  $start_entry = array(0 => "Choose Customer");
		  $cust_data = $start_entry + $cust_data;
		  $this->set(compact('cust_data'));
	}
    
    public function custData(){
		  if(array_key_exists('cust_id',$this->request->query)){
			   $cust_id = $this->request->query['cust_id'];
		  }
		  if(empty($cust_id)){
			   $this->Flash->error(__('Please Select Customer.'));
			 return $this->redirect(['action' => 'customerBasePrice']);die;  
		  }
		  
		  
		  $cust_data = $this->Customers->find('list',['keyField' => 'id',
		  								'valueField' => 'business',
		 								])->toArray();
		  $start_entry = array(0 => "Choose Customer");
		  $cust_data = $start_entry + $cust_data;
		  $this->set(compact('cust_data','cust_id'));
		  $this -> render('customer_base_price');		  
	}
	
	
	public function update_invoice_agent($old_agent,$new_agent,$customer_id){
		  $kiosks = $this->Kiosks->find("list",[
											   'keyField' => "id",
											   'valueField' => "id",
									 ])->toArray();
		  if(!empty($kiosks)){
			   foreach($kiosks as $key => $kiosk_id){
					if($kiosk_id == 10000){
							  //echo'1';
						$kioskProdctSaleTable_source = "kiosk_product_sales";
						$product_recit_table_source = "product_receipts";
						$paymentTable_source = "payment_details";
				   }else{
						if(empty($kiosk_id)){
							//echo'2';
							$kiosk_id = 10000;
							$kioskProdctSaleTable_source = "kiosk_product_sales";
							$product_recit_table_source = "product_receipts";
							$paymentTable_source = "payment_details";
						}else{
							//echo'3';
							$kioskProdctSaleTable_source = "kiosk_{$kiosk_id}_product_sales";
							$product_recit_table_source = "kiosk_{$kiosk_id}_product_receipts";
							$paymentTable_source = "kiosk_{$kiosk_id}_payment_details";
						}
				   }
				   
					$receiptTable = TableRegistry::get($product_recit_table_source,[
																		'table' => $product_recit_table_source,
																	]);
			
					$kioskProdctSalesTable = TableRegistry::get($kioskProdctSaleTable_source,[
																					'table' => $kioskProdctSaleTable_source,
																				]);
						
					$paymentTable = TableRegistry::get($paymentTable_source,[
																					'table' => $paymentTable_source,
																				]);
					
					$recipt_ids = $receiptTable->find("list",[
											    'conditions' => [
												  'customer_id' => $customer_id,
												  'agent_id' => $old_agent,
												],
												'keyField' => 'id',
												'valueField' => 'id',
												])->toArray();
					
					$conn = ConnectionManager::get('default');
					$query = "UPDATE $product_recit_table_source SET agent_id=$new_agent WHERE agent_id=$old_agent AND customer_id=$customer_id";
					$query = "UPDATE $product_recit_table_source SET agent_id=$new_agent WHERE agent_id=$old_agent"; // Modified on 7 sep
					$stmt = $conn->execute($query);
					
					if(!empty($recipt_ids)){
						 $recipt_id_str = implode(",",$recipt_ids);
					}else{
						 $recipt_id_str = "";
					}
					
					$payment_query = "UPDATE $paymentTable_source SET agent_id=$new_agent WHERE agent_id=$old_agent";
					if(!empty($recipt_id_str)){
						 $payment_query .= " AND `product_receipt_id` IN($recipt_id_str)";
						 $stmt = $conn->execute($payment_query);
					}
					
			   }
					
					
					
					
				   
		  }
	 }
	 
	 
	 public function update_quotation_agent($old_agent,$new_agent,$customer_id){
					$kioskProdctSaleTable_source = "t_kiosk_product_sales";
					$product_recit_table_source = "t_product_receipts";
					$paymentTable_source = "t_payment_details";
					
					$ProductReceiptTable = TableRegistry::get($product_recit_table_source,[
																			'table' => $product_recit_table_source,
																		]);
					$KioskProductSaleTable = TableRegistry::get($kioskProdctSaleTable_source,[
																						'table' => $kioskProdctSaleTable_source,
																					]);
					$PaymentDetailTable = TableRegistry::get($paymentTable_source,[
																						'table' => $paymentTable_source,
																					]); 
					
					$recipt_ids = $ProductReceiptTable->find("list",[
											    'conditions' => [
												  'customer_id' => $customer_id,
												  'agent_id' => $old_agent,
												],
												'keyField' => 'id',
												'valueField' => 'id',
												])->toArray();
					
					
					$conn = ConnectionManager::get('default');
					$query = "UPDATE $product_recit_table_source SET agent_id=$new_agent WHERE agent_id=$old_agent AND customer_id=$customer_id";
					$query = "UPDATE $product_recit_table_source SET agent_id=$new_agent WHERE agent_id=$old_agent";// modified on 7 sep 2018
					$stmt = $conn->execute($query);
					
					if(!empty($recipt_ids)){
						 $recipt_id_str = implode(",",$recipt_ids);
					}else{
						 $recipt_id_str = "";
					}
					
					$payment_query = "UPDATE $paymentTable_source SET agent_id=$new_agent WHERE agent_id=$old_agent";
					if(!empty($recipt_id_str)){
						 $payment_query .= " AND `product_receipt_id` IN($recipt_id_str)";
						 $stmt = $conn->execute($payment_query);
					}
	 }
	public function update_credit_note_agent($old_agent,$new_agent,$customer_id){
		 $kiosks = $this->Kiosks->find("list",[
											  'keyField' => "id",
											  'valueField' => "id",
									])->toArray();
		 if(!empty($kiosks)){
			  foreach($kiosks as $key => $kiosk_id){
				    if($kiosk_id == 10000){
							 //echo'1';
					    
					    $product_recit_table_source = "credit_receipts";
					    $paymentTable_source = "credit_payment_details";
				  }else{
					    if(empty($kiosk_id)){
						    //echo'2';
						    $kiosk_id = 10000;
						    
						    $product_recit_table_source = "credit_receipts";
						    $paymentTable_source = "credit_payment_details";
					    }else{
						    //echo'3';
						    
						    $product_recit_table_source = "kiosk_{$kiosk_id}_credit_receipts";
						    $paymentTable_source = "kiosk_{$kiosk_id}_credit_payment_details";
					    }
				  }
				  
				    $receiptTable = TableRegistry::get($product_recit_table_source,[
																	    'table' => $product_recit_table_source,
																    ]);
		    
				    $paymentTable = TableRegistry::get($paymentTable_source,[
																				    'table' => $paymentTable_source,
																			    ]);
				    
				    $recipt_ids = $receiptTable->find("list",[
											   'conditions' => [
												 'customer_id' => $customer_id,
												 'agent_id' => $old_agent,
											    ],
											    'keyField' => 'id',
											    'valueField' => 'id',
											    ])->toArray();
				    
				    $conn = ConnectionManager::get('default');
				    $query = "UPDATE $product_recit_table_source SET agent_id=$new_agent WHERE agent_id=$old_agent AND customer_id=$customer_id";
					$query = "UPDATE $product_recit_table_source SET agent_id=$new_agent WHERE agent_id=$old_agent";// modified on 7 sep
				    $stmt = $conn->execute($query);
				    
				    if(!empty($recipt_ids)){
						$recipt_id_str = implode(",",$recipt_ids);
				    }else{
						$recipt_id_str = "";
				    }
				    
				    $payment_query = "UPDATE $paymentTable_source SET agent_id=$new_agent WHERE agent_id=$old_agent";
				    if(!empty($recipt_id_str)){
						$payment_query .= " AND `credit_receipt_id` IN($recipt_id_str)";
						$stmt = $conn->execute($payment_query);
				    }
				    
			  }
				    
				    
				    
				    
				  
		 }
	}
	
	
	 public function update_credit_quotation_agent($old_agent,$new_agent,$customer_id){
				    
				    $product_recit_table_source = "t_credit_receipts";
				    $paymentTable_source = "t_credit_payment_details";
				    
				    $ProductReceiptTable = TableRegistry::get($product_recit_table_source,[
																		    'table' => $product_recit_table_source,
																	    ]);
				    
				    $PaymentDetailTable = TableRegistry::get($paymentTable_source,[
																					    'table' => $paymentTable_source,
																				    ]); 
				    
				    $recipt_ids = $ProductReceiptTable->find("list",[
											   'conditions' => [
												 'customer_id' => $customer_id,
												 'agent_id' => $old_agent,
											    ],
											    'keyField' => 'id',
											    'valueField' => 'id',
											    ])->toArray();
				    
				    
				    $conn = ConnectionManager::get('default');
				    $query = "UPDATE $product_recit_table_source SET agent_id=$new_agent WHERE agent_id=$old_agent AND customer_id=$customer_id";
					$query = "UPDATE $product_recit_table_source SET agent_id=$new_agent WHERE agent_id=$old_agent"; // modified on 7 sep
				    $stmt = $conn->execute($query);
				    
				    if(!empty($recipt_ids)){
						$recipt_id_str = implode(",",$recipt_ids);
				    }else{
						$recipt_id_str = "";
				    }
				    
				    $payment_query = "UPDATE $paymentTable_source SET agent_id=$new_agent WHERE agent_id=$old_agent";
				    if(!empty($recipt_id_str)){
						$payment_query .= " AND `credit_receipt_id` IN($recipt_id_str)";
						$stmt = $conn->execute($payment_query);
				    }
	}
	
}