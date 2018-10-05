<?php
namespace App\Controller;


use App\Controller\AppController;
use Cake\Core\Configure;
use Cake\Core\Configure\Engine\PhpConfig;
use Cake\Datasource\ConnectionManager;
use Cake\ORM\TableRegistry;
use Cake\Mailer\Email;
use Cake\Utility\Text;
use Cake\Network\Exception\NotFoundException;

/**
 * ProductReceipts Controller
 *
 * @property \App\Model\Table\ProductReceiptsTable $ProductReceipts
 */
class PrintsController extends AppController
{

    /**
     * Index method
     *
     * @return \Cake\Network\Response|null
     */
    public $helpers = [
         'Paginator' => ['templates' => 'paginatortemplates']
         ];
    public function initialize(){
        parent::initialize();
        $paymentType=Configure::read('payment_type');
       $this->loadModel('Users');
        $this->loadModel('Kiosks');
        $this->loadModel('MobilePrices');
        $this->loadModel('MobileModels');
        $this->loadModel('Networks');
        $this->loadModel('MobileTransferLogs');
        $this->loadModel('MobileReSales');
        $this->loadModel('Brands');
        $this->loadModel('MobileConditions');
        $this->loadModel('FunctionConditions');
        $this->loadModel('RetailCustomers');
		$this->loadModel('MobilePurchases');
		$this->loadModel('ProblemTypes');
		$this->loadModel('MobileRepairs');
		$this->loadModel('MobileReSales');
		$this->loadModel('RepairPayments');
		$this->loadModel('MobileUnlocks');
		$this->loadModel('MobileUnlockLogs');
		$this->loadModel('UnlockPayments');
		
       $this->loadComponent('ScreenHint');
		$CURRENCY_TYPE = Configure::read('CURRENCY_TYPE');
		$this->set(compact('CURRENCY_TYPE' ));
        $countiesUkOptions = Configure::read('uk_counties');
		$paymentType=Configure::read('payment_type');
		$sellingOptions = Configure::read('selling_status');
		$refundOptions = Configure::read('refund_status');
		$this->set('sellingOptions', $sellingOptions);
		$this->set('refundOptions', $refundOptions);
		$this->set('countiesUkOptions', $countiesUkOptions);
		$this->set(compact('paymentType'));
    }
    
    public function print() {
		$this->thermal_printing("unlock",446);die;
	 //$this->viewBuilder()->layout(false);
		$this->render('print2');
    }
	public function repair($id = null) {
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
			$repair_data = $mobileRepairData_query->first();
		}else{
			$repair_data = array();
		}
		$brands_query = $this->Brands->find('list', array(
			   'keyField' => 'id',
			   'valueField' => 'brand',
			   'order'=>'brand asc'
		));
		$brands_query = $brands_query->hydrate(false);
		if(!empty($brands_query)){
		  $brands = $brands_query->toArray();
		}else{
		  $brands = array();
		}
		$mobileModels_query = $this->MobileModels->find('list',array(
																	'keyField' => 'id',
																	'valueField' => 'model',
																	'order'=>'model asc'
																	));
		$mobileModels_query = $mobileModels_query->hydrate(false);
		if(!empty($mobileModels_query)){
		  $mobileModels = $mobileModels_query->toArray();
		}else{
		  $mobileModels = array();
		}
		// pr($mobileRepairData);die;
		$repairRefundData = array();
		foreach($repair_data['mobile_repair_sales'] as $key=>$repairSaleData){
			if($repairSaleData['refund_status']==1){
				$repairRefundData[] = $repairSaleData;
			}
		}
		$settingArr = $this->setting;
		$userId = $repair_data['booked_by'];
		$kiosk_id = $repair_data['kiosk_id'];
		$kiosk_info = $this->Kiosks->find('all')->toArray();
		$user_info = $this->Users->find('list',
										 [
											'keyField' => 'id',
											'valueField' => 'username',
											'conditions'=>['Users.id'=>$userId]
										 ]
										 )->toArray();
		
		$vat = $this->VAT;
		$paymentdata_query = $this->RepairPayments->find('all',array(
														'conditions' => array('mobile_repair_id' => $id),
														));
		$paymentdata_query->hydrate(false);
		if(!empty($paymentdata_query)){
			$paymentdata = $paymentdata_query->toArray();
		}else{
			$paymentdata = array();
		}
        
	
		$common_data = array(
                'user_id' => $repair_data['booked_by'],
                'id' => $repair_data['id'],
                'cust' => array(
                        'customer_fname' => $repair_data['customer_fname'],
                        'customer_lname' => $repair_data['customer_lname'],
                        'customer_address_1' => $repair_data['customer_address_1'],
                        'customer_address_2' => $repair_data['customer_address_2'],
                        'city' => $repair_data['city'],
                        'state' => $repair_data['state'],
                        'zip' => $repair_data['zip'],
                )
              );
		if($repair_data['status'] == 6 || $repair_data['status'] == 8){
			if(empty($repair_data['delivered_at'])){
				$common_data['created'] = $repair_data['modified'];
			}else{
				$common_data['created'] = $repair_data['delivered_at'];	
			}
			
		}elseif($repair_data['status'] == 2){
			$common_data['created'] = $repair_data['modified'];
		}else{
			$common_data['created'] = $repair_data['created'];
		}
		
		$recipt_for = "repair";
		
		$this->viewBuilder()->setLayout(false); 
		$this->set(compact('settingArr','repair_data','user_info','repairRefundData','kiosk_info','problemTypeOptions'));
		$this->set(compact('mobileModels','brands','kiosk_id','common_data','recipt_for','paymentdata'));
		   $this->render('custom_reciept');
    }
    public function mobilePurchases($id= null) {
		//echo $id;
		//$this->viewBuilder()->layout(false);
		$refundOptions = Configure::read('refund_status');
		if (!$this->MobilePurchases->exists($id)) {
			throw new NotFoundException(__('Invalid mobile purchase'));
		}
		$countryOptions = Configure::read('uk_non_uk');
		$options = array('conditions' => array('MobilePurchases.id' => $id));
		$res_query = $this->MobilePurchases->find('all', $options);
		$res_query = $res_query->hydrate(false);
		if(!empty($res_query)){
		  $res = $res_query->first();
		}else{
		  $res = array();
		}
		$this->set('mobilePurchase',$res);
		$options = array(
			'conditions' => array('MobilePurchases.id'=> $id)
		);
		$mobilePurchase_query = $this->MobilePurchases->find('all', $options);
		$mobilePurchase_query = $mobilePurchase_query->hydrate(false);
		if(!empty($mobilePurchase_query)){
		  $mobilePurchase = $mobilePurchase_query->toArray();
		}else{
		  $mobilePurchase = array();
		}
		$kiosk_id = "";
		if(!empty($mobilePurchase)){
			$kiosk_id = $mobilePurchase[0]['kiosk_id'];
		}
		
		$settingArr = $this->setting;
		$currency = $settingArr['currency_symbol'];
		$kiosk_query = $this->Kiosks->find('list');
		$kiosk_query = $kiosk_query->hydrate(false);
		if(!empty($kiosk_query)){
		  $kiosk = $kiosk_query->toArray();
		}else{
		  $kiosk = array();
		}
		$brands_query = $this->MobilePurchases->Brands->find('list', array(
			   'keyField' => 'id',
			   'valueField' => 'brand',
			   'order'=>'brand asc'
		));
		$brands_query = $brands_query->hydrate(false);
		if(!empty($brands_query)){
		  $brands = $brands_query->toArray();
		}else{
		  $brands = array();
		}
		$mobileModels_query = $this->MobileModels->find('list',array(
																	'keyField' => 'id',
																	'valueField' => 'model',
																	'order'=>'model asc'
																	));
		$mobileModels_query = $mobileModels_query->hydrate(false);
		if(!empty($mobileModels_query)){
		  $mobileModels = $mobileModels_query->toArray();
		}else{
		  $mobileModels = array();
		}
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
		
		$kiosk_info = $this->Kiosks->find('all')->toArray();
		$user_info = $this->Users->find('list',
										 [
											'keyField' => 'id',
											'valueField' => 'username',
										 ]
										 )->toArray();
		
		$vat = $this->VAT;
		
		$cutomer_arr = array();
		if(!empty($mobilePurchase[0]['customer_fname'])){
			$cutomer_arr['customer_fname'] = $mobilePurchase[0]['customer_fname'];
		}
		if(!empty($mobilePurchase[0]['customer_lname'])){
			$cutomer_arr['customer_lname'] = $mobilePurchase[0]['customer_lname'];
		}
		
		if(!empty($mobilePurchase[0]['customer_address_1'])){
			$cutomer_arr['customer_address_1'] = $mobilePurchase[0]['customer_address_1'];
		}
		
		if(!empty($mobilePurchase[0]['customer_address_2'])){
			$cutomer_arr['customer_address_2'] = $mobilePurchase[0]['customer_address_2'];
		}
		
		if(!empty($mobilePurchase[0]['city'])){
			$cutomer_arr['city'] = $mobilePurchase[0]['city'];
		}
		
		if(!empty($mobilePurchase[0]['state'])){
			$cutomer_arr['state'] = $mobilePurchase[0]['state'];
		}
		
		if(!empty($mobilePurchase[0]['zip'])){
			$cutomer_arr['zip'] = $mobilePurchase[0]['zip'];
		}
		
		$common_data = array(
                'created' => $mobilePurchase[0]['created'],
                'user_id' => $mobilePurchase[0]['user_id'],
                'id' => $mobilePurchase[0]['id'],
                'cust' => $cutomer_arr,
              );
		
		$this->viewBuilder()->setLayout(false); 
		
		$recipt_for = "mobile_purchase";
		$this->set(compact('mobilePurchase','mobileModels','networks','settingArr','brands','kiosk_info','user_info','kiosk_id','common_data','recipt_for'));
		   $this->render('custom_reciept');
		// $this->render('print3');
    }
	
	public function mobileSale($id = null){
		$this->loadModel('MobileReSales');
		$this->loadModel('MobileReSalePayments');
		$kiosk_query = $this->Kiosks->find('list',array(
														'keyField' => 'id',
														'valueField' => 'name',
														));
		$kiosk_query = $kiosk_query->hydrate(false);
		if(!empty($kiosk_query)){
		  $kiosk = $kiosk_query->toArray();
		}else{
		  $kiosk = array();
		}
		
		$settingArr = $this->setting;
		$mobileResaleData_query = $this->MobileReSales->find('all',array('conditions'=>array('MobileReSales.id'=>$id,'MobileReSales.refund_status IS NULL')));
		//echo "<pre>";print_r($mobileResaleData_query);die;
		$mobileResaleData_query = $mobileResaleData_query->hydrate(false);
		
		if(!empty($mobileResaleData_query)){
		  $mobileResaleData = $mobileResaleData_query->first();
		}else{
		  $mobileResaleData = array();
		}
		$pay_arr = array();
		$payment_data = $this->MobileReSalePayments->find("all",array('conditions' => array('mobile_re_sale_id' => $id)))->toArray();
		//pr($payment_data);
		if(!empty($payment_data)){
			foreach($payment_data as $key => $value){
				if(array_key_exists($value->payment_method,$pay_arr)){
					$pay_arr[$value->payment_method] = $pay_arr[$value->payment_method] + $value->amount;	
				}else{
					$pay_arr[$value->payment_method] = $value->amount;
				}
				
			}
		}
		
		$mobileReturnData_query = $this->MobileReSales->find('all',array('conditions'=>array('MobileReSales.sale_id'=>$id,'MobileReSales.refund_status'=>1)));
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
															'valueField' => 'brand',
															));
		$brandName_query = $brandName_query->hydrate(false);
		if(!empty($brandName_query)){
		  $brandName = $brandName_query->toArray();
		}else{
		  $brandName = array();
		}
		$modelName_query = $this->MobileModels->find('list',array('conditions'=>array('MobileModels.id'=>$mobileModelId),
																  'keyField' => 'id',
																 'valueField' => 'model',
																  ));
		$modelName_query = $modelName_query->hydrate(false);
		if(!empty($modelName_query)){
		  $modelName = $modelName_query->toArray();
		}else{
		  $modelName = array();
		}
		
		
		$kiosk_info = $this->Kiosks->find('all')->toArray();
		$user_info = $this->Users->find('list',
										 [
											'keyField' => 'id',
											'valueField' => 'username',
										 ]
										 )->toArray();
		
		$cutomer_arr = array();
		if(!empty($mobileResaleData['customer_fname'])){
			$cutomer_arr['customer_fname'] = $mobileResaleData['customer_fname'];
		}
		if(!empty($mobileResaleData['customer_lname'])){
			$cutomer_arr['customer_lname'] = $mobileResaleData['customer_lname'];
		}
		
		if(!empty($mobileResaleData['customer_address_1'])){
			$cutomer_arr['customer_address_1'] = $mobileResaleData['customer_address_1'];
		}
		
		if(!empty($mobileResaleData['customer_address_2'])){
			$cutomer_arr['customer_address_2'] = $mobileResaleData['customer_address_2'];
		}
		
		if(!empty($mobileResaleData['city'])){
			$cutomer_arr['city'] = $mobileResaleData['city'];
		}
		
		if(!empty($mobileResaleData['state'])){
			$cutomer_arr['state'] = $mobileResaleData['state'];
		}
		
		if(!empty($mobileResaleData['zip'])){
			$cutomer_arr['zip'] = $mobileResaleData['zip'];
		}
		
		$common_data = array(
                'created' => $mobileResaleData['created'],
                'user_id' => $mobileResaleData['user_id'],
                'id' => $mobileResaleData['id'],
                'cust' => $cutomer_arr,
              );
		
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
		$recipt_for = "mobile_sale";
		
		$this->viewBuilder()->setLayout(false); 
		$this->set(compact('settingArr','mobileResaleData','brandName','modelName','mobileReturnData','kiosk', 'kioskDetails','kiosk_info','user_info','common_data','recipt_for','kiosk_id','networks','pay_arr'));
		$this->render('custom_reciept');
	}
	
	
	public function mobileBulkSale($id = null){
		$this->loadmodel("MobileBlkReSales");
		$this->loadmodel("MobileBlkReSalePayments");
		
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
		
		$modelName_query = $modelName_query->hydrate(false);
		if(!empty($modelName_query)){
			$modelName = $modelName_query->toArray();
		}else{
			$modelName = array();
		}
		
		$kioskDetails_query = $this->Kiosks->find('all',array('conditions'=>array('Kiosks.id'=>$kiosk_id),'fields'=>array('id','name','address_1','address_2','city','state','zip','contact','country')));
		
		$kioskDetails_query = $kioskDetails_query->hydrate(false);
		if(!empty($kioskDetails_query)){
			$kioskDetails = $kioskDetails_query->first();
		}else{
			$kioskDetails = array();
		}
		
		$kiosk_info = $this->Kiosks->find('all')->toArray();
		$user_info = $this->Users->find('list',
										 [
											'keyField' => 'id',
											'valueField' => 'username',
										 ]
										 )->toArray();
		$cutomer_arr = array();
		if(!empty($mobileResaleData['customer_fname'])){
			$cutomer_arr['customer_fname'] = $mobileResaleData['customer_fname'];
		}
		if(!empty($mobileResaleData['customer_lname'])){
			$cutomer_arr['customer_lname'] = $mobileResaleData['customer_lname'];
		}
		
		if(!empty($mobileResaleData['customer_address_1'])){
			$cutomer_arr['customer_address_1'] = $mobileResaleData['customer_address_1'];
		}
		
		if(!empty($mobileResaleData['customer_address_2'])){
			$cutomer_arr['customer_address_2'] = $mobileResaleData['customer_address_2'];
		}
		
		if(!empty($mobileResaleData['city'])){
			$cutomer_arr['city'] = $mobileResaleData['city'];
		}
		
		if(!empty($mobileResaleData['state'])){
			$cutomer_arr['state'] = $mobileResaleData['state'];
		}
		
		if(!empty($mobileResaleData['zip'])){
			$cutomer_arr['zip'] = $mobileResaleData['zip'];
		}
		
		$common_data = array(
                'created' => $mobileResaleData['created'],
                'user_id' => $mobileResaleData['user_id'],
                'id' => $mobileResaleData['id'],
                'cust' => $cutomer_arr,
              );
		
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
		$recipt_for = "mobile_bulk_sale";
		
		$pay_arr = array();
		$payment_data = $this->MobileBlkReSalePayments->find("all",array('conditions' => array('mobile_blk_re_sale_id' => $id)))->toArray();
		//pr($payment_data);
		if(!empty($payment_data)){
			foreach($payment_data as $key => $value){
				if(array_key_exists($value->payment_method,$pay_arr)){
					$pay_arr[$value->payment_method] = $pay_arr[$value->payment_method] + $value->amount;	
				}else{
					$pay_arr[$value->payment_method] =  $value->amount;
				}
				
			}
		}
		
		$this->viewBuilder()->setLayout(false); 
		$this->set(compact('settingArr','mobileResaleData','brandName','modelName','mobileReturnData','kiosk', 'kioskDetails','recipt_for',
						   'networks','common_data','kiosk_info','user_info','kiosk_id','pay_arr'));
		$this->render('custom_reciept');     
	}

	 public function generateReceipt($id = null,$kioskID =""){
        $this->loadModel('Users');
        $this->loadModel('Kiosks');
        $this->loadModel('PaymentDetails');
		$this->loadModel('Customers');
		
		
		$refundOptions = Configure::read('refund_status');
        $users_query = $this->Users->find('list', [
                                            'keyField' => 'id',
                                            'valueField' => 'username'
                                        ]);
        if(!empty($users_query)){
            $users_query = $users_query->hydrate(false);
            $users = $users_query->toArray();   
        }
		if(!empty($kioskID)){
			$receiptTable_source = "kiosk_{$kioskID}_product_receipts";
			$salesTable_source = "kiosk_{$kioskID}_product_sales";
			$paymentTable_source = "product_payments";
			
			$receiptTable = TableRegistry::get($receiptTable_source,[
                                                                                    'table' => $receiptTable_source,
                                                                                ]);
			$salesTable = TableRegistry::get($salesTable_source,[
                                                                                    'table' => $salesTable_source,
                                                                                ]);
			$paymentTable = TableRegistry::get($paymentTable_source,[
                                                                                    'table' => $paymentTable_source,
                                                                                ]);

			
			$saleData = $salesTable->find('all', array(
																   'conditions' => array('product_receipt_id' => $id),
																   'recursive' => -1,
																   ))->toArray();
			$saleDataArr = array();
			foreach($saleData as $key => $productSaleData){
				$saleDataArr[] = $productSaleData;
			}
			//pr($saleDataArr);die;
		}else{
			$kiosk_id = $this->request->Session()->read('kiosk_id');	
			if(!empty($kiosk_id)){
				$receiptTable_source = "kiosk_{$kiosk_id}_product_receipts";
			$salesTable_source = "kiosk_{$kiosk_id}_product_sales";
			$paymentTable_source = "product_payments";
			}else{
				$receiptTable_source = "product_receipts";
			$salesTable_source = "kiosk_product_sales";
			$paymentTable_source = "product_payments";
			}
			
			
			$receiptTable = TableRegistry::get($receiptTable_source,[
                                                                                    'table' => $receiptTable_source,
                                                                                ]);
			$salesTable = TableRegistry::get($salesTable_source,[
                                                                                    'table' => $salesTable_source,
                                                                                ]);
			$paymentTable = TableRegistry::get($paymentTable_source,[
                                                                                    'table' => $paymentTable_source,
                                                                                ]);
		}
		
		if (!$receiptTable->exists($id)) {
			throw new NotFoundException(__('Invalid product receipt'));
		}
		
		$countryOptions = Configure::read('uk_non_uk');
		
		$options = array(
							'conditions' => array('id'  => $id), //$this->ProductReceipt->primaryKey returns "id"
							'recursive' => 1
						);
		//----------------------------------------------
		
		$productReceipt_query = $receiptTable->find('all', $options);
        $productReceipt_res = $productReceipt_query->hydrate(false);
        if(!empty($productReceipt_res)){
            $productReceipt = $productReceipt_res->first();
        }else{
			$productReceipt = array();
		}
		if(!empty($productReceipt)){
			$recipt_id = $productReceipt['id'];
			$customer_id = $productReceipt['customer_id'];
		}
		
		$sale_table_query = $salesTable->find('all',['conditions' => [
													'product_receipt_id' => $recipt_id
												  ]]);
		$sale_table_query = $sale_table_query->hydrate(false);
		if(!empty($sale_table_query)){
			$sale_table = $sale_table_query->toArray();
		}else{
			$sale_table = array();
		}
		
		$customer_data_query = $this->Customers->find('all',['conditions' => [
													'id' => $customer_id
												  ]]);
		$customer_data_query = $customer_data_query->hydrate(false);
		if(!empty($customer_data_query)){
			$customer_data = $customer_data_query->toArray();
		}else{
			$customer_data = array();
		}
		 
		$this->set(compact('customer_data','sale_table'));
		$email_sale_table = $kiosk_products_data = $sale_table;
		$this->set(compact('kiosk_products_data'));
		
		$kiosk_id = '';
		if(array_key_exists(0,$sale_table)){
			$kiosk_id = $sale_table['0']['kiosk_id'];
		}
		 $fullAddress = $kioskDetails = $kioskName = $kioskAddress1 = $kioskAddress2 = $kioskCity = $kioskState = $kioskZip  = $kioskZip = $kioskContact = $kioskCountry = $kioskTable = "";
		$kioskDetails_query = $this->Kiosks->find('all',array(
														 'conditions' => array('Kiosks.id' => $kiosk_id),
														 'fields' => array('id','name','address_1','address_2','city','state','zip','contact','country')
														)
										   );
        $kioskDetails = $kioskDetails_query->hydrate(false);
        if(!empty($kioskDetails)){
            $kioskDetails = $kioskDetails->first();
        }else{
            $kioskDetails = array();
        }
		if(($this->request->Session()->read('Auth.User.group_id') == KIOSK_USERS  &&
		   $this->request->Session()->read('Auth.User.user_type') == "wholesale") ||
		   $this->request->Session()->read('Auth.User.group_id') == ADMINISTRATORS ||
		   $this->request->session()->read('Auth.User.group_id') == SALESMAN ||
		   $this->request->session()->read('Auth.User.group_id') == inventory_manager ||
		   $this->request->session()->read('Auth.User.group_id') == FRANCHISE_OWNER){//forsaleman add by rajju
			 
			if(!empty($kioskDetails)){
				$kioskName = $kioskDetails['name'];
				$kioskAddress1 = $kioskDetails['address_1'];
				$kioskAddress2 = $kioskDetails['address_2'];
				$kioskCity = $kioskDetails['city'];
				$kioskState = $kioskDetails['state'];
				$kioskZip = $kioskDetails['zip'];
				$kioskContact = $kioskDetails['contact'];
				$kioskCountry = $kioskDetails['country'];
			}
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
		
		$settingArr = $this->setting;
		$currency = $settingArr['currency_symbol'];
		$kiosk_query = $this->Kiosks->find('list');
        $kiosk_query = $kiosk_query->hydrate(false);
        if(!empty($kiosk_query)){
            $kiosk = $kiosk_query->toArray();
        }else{
            $kiosk = array();
        }
		$vat = $this->VAT;
		$productReturnArr = $productSaleArr = $productCode = $productName = $productIdArr = array();
		
		if(!empty($kioskID)){
			$sale_table = $saleDataArr;
		}
		
		foreach($sale_table as $key => $productDetail){
			$productIdArr[] = $productDetail['product_id'];
			if($productDetail['refund_status']==0){
				$productSaleArr[] = $productDetail;
			}else{
				$productReturnArr[] = $productDetail;
			}
		}
		$returnQuantityArr = array();
		if(!empty($productReturnArr)){
			foreach($productReturnArr as $key => $productReturnDetail){
				$returnProductId = $productReturnDetail['product_id'];
				$returnReceiptId = $productReturnDetail['product_receipt_id'];
				$returnKey = "$returnProductId|$returnReceiptId";
				//$returnQuantityArr[$returnKey] = $productReturnDetail['quantity'];
				if(!array_key_exists($returnKey,$returnQuantityArr)){
					$returnQuantityArr[$returnKey] = $productReturnDetail['quantity'];
				}else{
					$returnQuantityArr[$returnKey]+= $productReturnDetail['quantity'];
				}
			}
		}
		$qttyArr = array();
		if(!empty($productSaleArr)){
			foreach($productSaleArr as $key => $productSaleDetail){
				$saleProductId = $productSaleDetail['product_id'];
				$saleReceiptId = $productSaleDetail['product_receipt_id'];
				$saleKey = "$saleProductId|$saleReceiptId";
				$qttyArr[$saleKey] = $productSaleDetail['quantity'];
				if(array_key_exists($saleKey,$returnQuantityArr)){
					$qttyArr[$saleKey]+= $returnQuantityArr[$saleKey];
				}
			}
		}
		foreach($productIdArr as $product_id){
            $product_detail_query = $this->Products->find('all', array(
																	'conditions' => array('Products.id' => $product_id),
																	'fields' => array('id', 'product', 'product_code')
																	)
													);
            $product_detail_res = $product_detail_query->hydrate(false);
            if(!empty($product_detail_res)){
                $product_detail_res = $product_detail_res->first();
                $product_detail[] = $product_detail_res;
            }
		}
		if(!isset($product_detail))$product_detail = array();
		
		foreach($product_detail as $productInfo){
			$productName[$productInfo['id']] = $productInfo['product'];
			$productCode[$productInfo['id']] = $productInfo['product_code'];
		}
		
		$processed_by = $productReceipt['processed_by'];
		$userName_query = $this->Users->find('all',array(
													'conditions' => array('Users.id' => $processed_by),
													'fields' => array('username'),
													'recursive' => -1
													)
									  );
        $userName_query = $userName_query->hydrate(false);
        if(!empty($userName_query)){
            $userName = $userName_query->first();
        }else{
			$userName = array();
		}
		$user_name = $userName['username']; 
		$paymentDetails_query = $paymentTable->find('all',array(
																 'conditions' => array('product_receipt_id' => $id,
																					   'kiosk_id' => $kioskID
																					   ),
																 'recursive' => -1)
													 );
		//pr($paymentDetails_query);die;
        $paymentDetails = $paymentDetails_query->hydrate(false);
        if(!empty($paymentDetails)){
            $paymentDetails = $paymentDetails->toArray();
        }
		//pr($paymentDetails);die;
		$CURRENCY_TYPE = Configure::read('CURRENCY_TYPE');
		$payment_method = array();
		foreach($paymentDetails as $key=>$paymentDetail){
			if($paymentDetail['status'] != 2){
				$payment_method[] = "<td style='font-size: 12px;' align='left'>".$paymentDetail['payment_method']."</td><td style='font-size: 12px;' align='right'>".$CURRENCY_TYPE.number_format($paymentDetail['amount'],2)."</td>";	
			}
		}
		$payment_method1 = array();
		foreach($paymentDetails as $key=>$paymentDetail){
			$payment_method1[] = $paymentDetail['payment_method'];
		}
		if(!empty($productReceipt['Customer']['email'])){
			$customerEmail = $productReceipt['Customer']['email'];
		}else{
			$customerDataReceipt_query = $receiptTable->find('all',array(
																			 'conditions' => array('id' => $id),
																			 'fields' => array('id', 'email')
																			)
															   );
			$customerDataReceipt_query = $customerDataReceipt_query->hydrate(false);
			if(!empty($customerDataReceipt_query)){
				$customerDataReceipt = $customerDataReceipt_query->first();
			}else{
				$customerDataReceipt = array();
			}
			$customerEmail = $customerDataReceipt['email'];
		}
		
		 $recipt_for = "new_sale";
		 $kiosk_info = $this->Kiosks->find('all')->toArray();
		$user_info = $this->Users->find('list',
										 [
											'keyField' => 'id',
											'valueField' => 'username',
										 ]
										 )->toArray();
		$cutomer_arr = array();
		if(!empty($productReceipt['fname'])){
			$cutomer_arr['customer_fname'] = $productReceipt['fname'];
		}
		if(!empty($productReceipt['lname'])){
			$cutomer_arr['customer_lname'] = $productReceipt['lname'];
		}
		
		if(!empty($productReceipt['address_1'])){
			$cutomer_arr['customer_address_1'] = $productReceipt['address_1'];
		}
		
		if(!empty($productReceipt['address_2'])){
			$cutomer_arr['customer_address_2'] = $productReceipt['address_2'];
		}
		
		if(!empty($productReceipt['city'])){
			$cutomer_arr['city'] = $productReceipt['city'];
		}
		
		if(!empty($productReceipt['state'])){
			$cutomer_arr['state'] = $productReceipt['state'];
		}
		
		if(!empty($productReceipt['zip'])){
			$cutomer_arr['zip'] = $productReceipt['zip'];
		}
		
		$common_data = array(
                'created' => $productReceipt['created'],
                'user_id' => $productReceipt['processed_by'],
                'id' => $productReceipt['id'],
                'cust' => $cutomer_arr,
              );
		
		$this->viewBuilder()->setLayout(false); 
		$this->set(compact('productReceipt','users', 'kiosk','vat','productName','customerEmail','paymentDetails','settingArr','customer_data','payment_method','user_name','productCode','kioskTable','kioskContact','countryOptions','currency','qttyArr','kioskDetails','payment_method1','recipt_for','common_data','kiosk_info','user_info','kiosk_id'));
		$this->render("custom_reciept");
	}
	
	public function unlock($id = null){
		$mobileUnlockData_query = $this->MobileUnlocks->find('all',[
                                                                    'conditions'=>['MobileUnlocks.id'=>$id],
                                                                    'contain' => ['MobileUnlockSales','Brands','MobileModels','Networks']
                                                                    ]);
        $mobileUnlockData_result = $mobileUnlockData_query->first();
        if(!empty($mobileUnlockData_result)){
            $mobileUnlockData = $mobileUnlockData_result->toArray();
        }else{
            $mobileUnlockData = array();
        }
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
		
		$unlockRefundData = array();
   // pr($mobileUnlockData['mobile_unlock_sales']);//die;
		foreach($mobileUnlockData['mobile_unlock_sales'] as $key=>$unlockSaleData){
			if($unlockSaleData['refund_status'] == 1){
				//echo "sds";
				$unlockRefundData[] = $unlockSaleData;
			}
		}
			
		$settingArr = $this->setting;
		$brands_query = $this->Brands->find('list', array(
			   'keyField' => 'id',
			   'valueField' => 'brand',
			   'order'=>'brand asc'
		));
		$brands_query = $brands_query->hydrate(false);
		if(!empty($brands_query)){
		  $brands = $brands_query->toArray();
		}else{
		  $brands = array();
		}
		$mobileModels_query = $this->MobileModels->find('list',array(
																	'keyField' => 'id',
																	'valueField' => 'model',
																	'order'=>'model asc'
																	));
		$mobileModels_query = $mobileModels_query->hydrate(false);
		if(!empty($mobileModels_query)){
		  $mobileModels = $mobileModels_query->toArray();
		}else{
		  $mobileModels = array();
		}
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
		
		 
        $kiosk_info = $this->Kiosks->find('all')->toArray();
		$user_info = $this->Users->find('list',
										 [
											'keyField' => 'id',
											'valueField' => 'username',
											'conditions'=>['Users.id'=>$userId]
										 ]
										 )->toArray();
		
		$vat = $this->VAT;
		$paymentdata_query = $this->UnlockPayments->find('all',array(
														'conditions' => array('mobile_unlock_id' => $id),
														));
		$paymentdata_query->hydrate(false);
		if(!empty($paymentdata_query)){
			$paymentdata = $paymentdata_query->toArray();
		}else{
			$paymentdata = array();
		}
		$brandId = $mobileUnlockData['brand_id'];
		$mobileModelId = $mobileUnlockData['mobile_model_id'];
		$networkId = $mobileUnlockData['network_id'];
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
		$mobileUnlockData['unlocking_days'] = $unlockingDays;
		$mobileUnlockData['estimated_minutes'] = $unlockMinutes;
		
		$pay_res = $this->UnlockPayments->find('all', array('conditions' => array('UnlockPayments.mobile_unlock_id' => $id),
															'order by' => 'UnlockPayments.created ASC',
															)
											   )->toArray();
		$date = "";
		if(!empty($pay_res)){
			$date = $pay_res[0]->created;
		}
		
		$common_data = array(
				'mobile_status' =>$mobileStatus,
				'payment_date' => $date,
                'created' => $mobileUnlockData['created'],
                'user_id' => $mobileUnlockData['booked_by'],
                'id' => $mobileUnlockData['id'],
                'cust' => array(
                        'customer_fname' => $mobileUnlockData['customer_fname'],
                        'customer_lname' => $mobileUnlockData['customer_lname'],
                        'customer_address_1' => $mobileUnlockData['customer_address_1'],
                        'customer_address_2' => $mobileUnlockData['customer_address_2'],
                        'city' => $mobileUnlockData['city'],
                        'state' => $mobileUnlockData['state'],
                        'zip' => $mobileUnlockData['zip'],
                )
              );
		//pr($common_data);
		$recipt_for = "unlock";
		$this->viewBuilder()->setLayout(false); 
		$this->set(compact('settingArr','mobileUnlockData','user_info','unlockRefundData','kiosk_info','problemTypeOptions'));
		$this->set(compact('mobileModels','brands','networks','kiosk_id','common_data','unlock','paymentdata','recipt_for'));
		$this->render('custom_reciept');
		 
		
	}
	 
}
