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
use PdfCrowd;
/**
 * ProductReceipts Controller
 *
 * @property \App\Model\Table\ProductReceiptsTable $ProductReceipts
 */
class ProductReceiptsController extends AppController
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
        $this->set(compact('paymentType'));
        $this->loadComponent('ScreenHint');
        $this->loadModel('ProductReceipts');
        $this->loadModel('Customers');
        $this->loadModel('PaymentDetails');
        $this->loadModel('Users');
		$this->loadModel('KioskProductSales');
        $this->loadModel('ProductPayments');
		$this->loadModel('Agents');
		$this->loadModel('PmtLogs');
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
    
    public function index() {
		$external_sites = Configure::read('external_sites');
		$path = dirname(__FILE__);
		$ext_site = 0;
		if(!empty($external_sites)){
			foreach($external_sites as $site_id => $site_name){
					$isboloRam = strpos($path,$site_name);
					if($isboloRam != false){
						$ext_site = 1;
					}
			}
		}
		
		
		
        $this->loadModel('ProductReceipts');
        $this->loadModel('KioskProductSales');
        $this->loadModel('Products');
		if(array_key_exists('0',$this->request->params['pass'])){
			$kiosk_id = $this->request->params['pass'][0];
			$kioskProdctSaleTable_source = "kiosk_{$kiosk_id}_product_sales";
			$product_recit_table_source = "kiosk_{$kiosk_id}_product_receipts";
			$paymentTable_source = "kiosk_{$kiosk_id}_payment_details";
			
			$receiptTable = TableRegistry::get($product_recit_table_source,[
																		'table' => $product_recit_table_source,
																	]);
			
			$kioskProdctSalesTable = TableRegistry::get($kioskProdctSaleTable_source,[
																		'table' => $kioskProdctSaleTable_source,
																	]);
			
			$paymentTable = TableRegistry::get($paymentTable_source,[
																		'table' => $paymentTable_source,
																	]);

			
			//$this->KioskProductSale->setSource($kioskProdctTable);
			//$this->ProductReceipt->setSource($product_recit_table);
			//$this->PaymentDetail->setSource($paymentTable);
		}else{
			$kiosk_id = $this->request->Session()->read('kiosk_id');
			if(!empty($kiosk_id)){
				$kioskProdctSaleTable_source = "kiosk_{$kiosk_id}_product_sales";
				$product_recit_table_source = "kiosk_{$kiosk_id}_product_receipts";
				$paymentTable_source = "kiosk_{$kiosk_id}_payment_details";
			}else{
				$kioskProdctSaleTable_source = "kiosk_product_sales";
				$product_recit_table_source = "product_receipts";
				$paymentTable_source = "payment_details";
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
		}
		$settingArr = $this->setting;
		//$listPayment = $this->PaymentDetail->find('list',array('fields'=>array('product_receipt_id','amount')));
		
		$receiptIdArr = array();
		$productReceiptDetail = array();
		//
		//if($listPayment){
		//	$receiptIdArr = array_keys($listPayment);
		//}
		$totalBillCost = 0;
		$productReceiptDetail_query = $receiptTable->find('all',array('fields'=>array('id','vat','status','bill_amount', 'bill_cost','created'),'recursive'=>-1));
        if(!empty($productReceiptDetail_query)){
            $productReceiptDetail = $productReceiptDetail_query->hydrate(false);
            $productReceiptDetail = $productReceiptDetail->toArray();   
        }
        //pr($productReceiptDetail);die;
		$createdArr = array();
		$lptotalPaymentAmount = 0;
		$lpgrandNetAmount = 0;
		foreach($productReceiptDetail as $key=>$productReceiptDta){
			//showing the data with product receipt status == 0 ie. the payment went through and data saved properly
			if($productReceiptDta['status']==0){
				$createdArr[$productReceiptDta['id']] = $productReceiptDta['created'];
				$totalBillCost+=floatval($productReceiptDta['bill_cost']);
                $receiptIdArr[$productReceiptDta['id']] = $productReceiptDta['id'];//capturing the receipt ids
				$paymentAmount = $productReceiptDta['bill_amount'];
				$lptotalPaymentAmount+=floatval($paymentAmount);
				$vatPercentage = $productReceiptDta['vat']/100;
				$netAmount = $paymentAmount/(1+$vatPercentage);
				$lpgrandNetAmount+=$netAmount;
			}
		}
                //code for getting cost price of products*******************
                $totalCost = 0;
                if(count($receiptIdArr)){
                    $productQttArr_query = $kioskProdctSalesTable->find('all', array('conditions' => array('product_receipt_id IN' => $receiptIdArr), 'fields' => array('product_id','quantity','product_receipt_id'), 'recursive' => -1));
                    $productQttArr = $productQttArr_query->hydrate(false);
                    if(!empty($productQttArr)){
                        $productQttArr = $productQttArr->toArray();
                    }
                    
                    $receiptIdDetail = array();
                    $productIdsArr = array();
                    foreach($productQttArr as $key => $productQtt){
                        //$receiptIdDetail[$productQtt['KioskProductSale']['product_receipt_id']][] = $productQtt['KioskProductSale'];
                        $productIdsArr[$productQtt['product_id']] = $productQtt['product_id'];
                    }
                    if(empty($productIdsArr)){
						$productIdsArr = array(0=>null);
					}
                    $costPriceList_query = $this->Products->find('list', array('conditions' => array('Products.id IN' => $productIdsArr), 'fields' => array('id', 'cost_price')));
                    $costPriceList = $costPriceList_query->hydrate(false);
                    if(!empty($costPriceList)){
                        $costPriceList = $costPriceList->toArray();
                    }
                    foreach($productQttArr as $key => $productQtt){
                        if(!array_key_exists($productQtt['product_id'],$costPriceList)){continue;}
                        $costPrice = $costPriceList[$productQtt['product_id']]*$productQtt['quantity'];
                        $totalCost+=$costPrice;
                    }
                }
                //*********************till here
          
		$lptotalVat = $lptotalPaymentAmount - $lpgrandNetAmount;
		$lptotalVat = $lptotalVat;
		
		$this->set(compact('lptotalPaymentAmount','lpgrandNetAmount','lptotalVat','totalCost'));
		 $this->paginate = [
                                    'order' => [
                                        'product_receipt_id' => 'desc'
                                    ],
									'limit' => 50
                                   // 'contain' => ['ProductReceipts']
                                ];
		
        
		$productReceipts = $this->paginate($paymentTable);
        if(!empty($productReceipts)){
          $productReceipts = $productReceipts->toArray();
        }else{
			$productReceipts = array();
		}
		//pr($productReceipts);
		$recipt_table_data = array();
        $y_recipt_ids = array();
		foreach($productReceipts as $s_key => $s_value){
			$y_recipt_ids[] = $s_value->product_receipt_id;
		}
		if(empty($y_recipt_ids)){
            $y_recipt_ids = array('0'=>null);
        }
		$recipt_table_data_query = $receiptTable->find('all',[
								   'conditions' => ['id IN' => $y_recipt_ids]
								   ]);
		$recipt_table_data_query = $recipt_table_data_query->hydrate(false);
		if(!empty($recipt_table_data_query)){
			$recipt_table_data = $recipt_table_data_query->toArray();
		}else{
			$recipt_table_data = array();
		}
		
		//pr($recipt_table_data);die;
		$reciptTableData = $customerIdArr = array();
		foreach($recipt_table_data as $receiptDetail){
			//pr($receiptDetail);die;
			$reciptTableData[$receiptDetail['id']] = $receiptDetail;
			$customerIdArr[] = $receiptDetail['customer_id'];
		}
		$this->set('recipt_table_data', $reciptTableData);
		if(empty($customerIdArr)){
			$customerIdArr = array(0=>null);
		}
		$customerBusiness_query = $this->Customers->find('list',
                                                         ['conditions' => [
                                                                           'Customers.id IN' => $customerIdArr
                                                                           ],
                                                                 'keyField' => 'id',
                                                                'valueField' => 'business',
                                                         ]);
        $customerBusiness = $customerBusiness_query->hydrate(false);
        if(!empty($customerBusiness)){
            $customerBusiness = $customerBusiness->toArray();
        }
		$customerCountry_query = $this->Customers->find('list',
                                                         ['conditions' => [
                                                                           'Customers.id IN' => $customerIdArr,
																		   'Customers.country ' => "OTH",
                                                                           ],
                                                                 'keyField' => 'id',
                                                                'valueField' => 'country',
                                                         ]);
        $customerCountry = $customerCountry_query->hydrate(false);
        if(!empty($customerCountry)){
            $customerCountry = $customerCountry->toArray();
        }
		if($this->request->session()->read('Auth.User.group_id') == ADMINISTRATORS || $this->request->session()->read('Auth.User.group_id') == FRANCHISE_OWNER){
			//$session_id = $this->Session->id();
			$user_id = $this->request->Session()->read('Auth.User.id');
			$data_arr = array('kiosk_id' => 10000);
			$jsondata = json_encode($data_arr);
			$this->loadModel('UserSettings');
			
			$res_query = $this->UserSettings->find('all',array('conditions' => array(
																		 'user_id' => $user_id,
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
									'setting_name' => "search",
									'data' => $jsondata
									);
				$entity = $this->UserSettings->get($userSettingid);
                
				$entity = $this->UserSettings->patchEntity($entity,$data_to_save);
				$this->UserSettings->save($entity);
			}else{
				$data_to_save = array(
									'user_id' => $user_id,
									//'user_session_key' => $session_id,
									'setting_name' => "search",
									'data' => $jsondata
									);
				$entity = $this->UserSettings->newEntity();
				$entity = $this->UserSettings->patchEntity($entity,$data_to_save);
				$this->UserSettings->save($entity);
			}
		}
		$kiosks_query = $this->Kiosks->find('list');
		$kiosks_query = $kiosks_query->hydrate(false);
		
		
		if(!empty($kiosks_query)){
			$kiosks = $kiosks_query->toArray();
		}else{
			$kiosks = array();
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
		//pr($agents);die;
		$this->set(compact('productReceipts', 'customerBusiness','totalAmount','totalBillCost','createdArr','kiosks','kiosk_id','agents','customerCountry'));
	}
    
    public function allInvoices(){
     
        if(array_key_exists('0',$this->request->params['pass'])){
			$kiosk_id = $this->request->params['pass'][0];
			$kioskProdctsalesTable_source = "kiosk_{$kiosk_id}_product_sales";
			$product_recit_table_source = "kiosk_{$kiosk_id}_product_receipts";
			
			
			$kioskProdctsalesTable = TableRegistry::get($kioskProdctsalesTable_source,[
                                                                                    'table' => $kioskProdctsalesTable_source,
                                                                                ]);
			$receiptTable = TableRegistry::get($product_recit_table_source,[
                                                                                    'table' => $product_recit_table_source,
                                                                                ]);
			
			//$this->KioskProductSale->setSource($kioskProdctTable);
			//$this->ProductReceipt->setSource($product_recit_table);
			
			$recit_id_data_query = $kioskProdctsalesTable->find('list',[
																	'keyField' => 'id',
                                                                    'valueField' => 'product_receipt_id',
																	//'recursive' => -1
                                                                   ]);
            $recit_id_data_query = $recit_id_data_query->hydrate(false);
            if(!empty($recit_id_data_query)){
                $recit_id_data = $recit_id_data_query->toArray();
            }else{
                $recit_id_data = array();
            }
			$this->paginate = [
                                'conditions' => ['id IN' => $recit_id_data],
                                'recursive' => -1,
                                'order' => ['id desc']
                              ];
			
			$productReceipts_query = $this->paginate($receiptTable);
            if(!empty($productReceipts_query)){
                $productReceipts = $productReceipts_query->toArray();
            }else{
                $productReceipts = array();
            }
		}else{
			$kiosk_id = $this->request->Session()->read('kiosk_id');	
			if(!empty($kiosk_id)){
					$kioskProdctsalesTable_source = "kiosk_{$kiosk_id}_product_sales";
					$product_recit_table_source = "kiosk_{$kiosk_id}_product_receipts";
			}else{
				$kioskProdctsalesTable_source = "kiosk_product_sales";
				$product_recit_table_source = "product_receipts";
			}
			
			
			$kioskProdctsalesTable = TableRegistry::get($kioskProdctsalesTable_source,[
                                                                                    'table' => $kioskProdctsalesTable_source,
                                                                                ]);
			$receiptTable = TableRegistry::get($product_recit_table_source,[
                                                                                    'table' => $product_recit_table_source,
                                                                                ]);
			
			
             //$this->loadModel('ProductReceipts');
              $this->paginate = [
                                    'order' => ['id' => 'desc']
                                ];

			$productReceipts_query = $this->paginate($receiptTable);
            if(!empty($productReceipts_query)){
             $productReceipts = $productReceipts_query->toArray();   
            }
		}
		$customerIdArr = array();
		$customerIdArr = array();
		foreach($productReceipts as $receiptDetail){
			$customerIdArr[] = $receiptDetail->customer_id;
		}
		foreach($customerIdArr as $customerId){
			//pr($customerId);die;
			
             $cutomer_query = $this->Customers->find('all',array('conditions'=>array('Customers.id'=>$customerId),'fields'=>array('id','business')));
			
            $cutomer_data = $cutomer_query->hydrate(false);
            if(!empty($cutomer_data)){
                $cutomer_data = $cutomer_data->first();
                $customerDetailArr[] = $cutomer_data;
            }
		}
		$customerBusiness = array();
		if(!empty($customerDetailArr)){
			foreach($customerDetailArr as $customerDetail){
					$customerBusiness[$customerDetail['id']] = $customerDetail['business'];
				}
		}
    
		$hint = $this->ScreenHint->hint('product_receipts','all_invoices');
					if(!$hint){
						$hint = "";
					}
		$this->set(compact('hint','productReceipts', 'customerBusiness'));
    }
    public function generateReceipt($id = null,$kioskID =""){
      //  echo KIOSK_USERS;die;
		$CURRENCY_TYPE = Configure::read('CURRENCY_TYPE');
        $this->loadModel('Users');
        $this->loadModel('Kiosks');
        $this->loadModel('PaymentDetails');
        
		//Configure::load('commonarrays');
		
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
			 
			//Code for admin side
			//$this->ProductReceipt->setSource("kiosk_{$kioskID}_product_receipts");
			//$this->KioskProductSale->setSource("kiosk_{$kioskID}_product_sales");
			//$this->PaymentDetail->setSource("kiosk_{$kioskID}_payment_details");
			
			$receiptTable_source = "kiosk_{$kioskID}_product_receipts";
			$salesTable_source = "kiosk_{$kioskID}_product_sales";
			$paymentTable_source = "kiosk_{$kioskID}_payment_details";
			
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
																   ));
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
			$paymentTable_source = "kiosk_{$kiosk_id}_payment_details";
			}else{
				$receiptTable_source = "product_receipts";
			$salesTable_source = "kiosk_product_sales";
			$paymentTable_source = "payment_details";
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
		//pr($kiosk_products_data);die;
		$this->set(compact('kiosk_products_data'));
		//-----------code for getting data from kiosk_{}_product_sales
		
		//------------------------------------------------------------
		/*pr($productReceipt);
		$dbo = $this->ProductReceipt->getDatasource();
		$logData = $dbo->getLog();
		$getLog = end($logData['log']);
		echo "Log Query:".$getLog['query'];
		die;*/
		$kiosk_id = '';
		if(array_key_exists(0,$sale_table)){
			$kiosk_id = $sale_table['0']['kiosk_id'];
		}
		
		if($kiosk_id == 0){
			$new_kiosk_data =  $this->Kiosks->find('all',array(
														 'conditions' => array('Kiosks.id' => 10000),
														)
										   )->toArray();
		}else{
			$new_kiosk_data = array();
		}
		
		
		$fullAddress = $kioskDetails = $kioskName = $kioskAddress1 = $kioskAddress2 = $kioskCity = $kioskState = $kioskZip  = $kioskZip = $kioskContact = $kioskCountry = $kioskTable = "";
		$kioskDetails_query = $this->Kiosks->find('all',array(
														 'conditions' => array('Kiosks.id' => $kiosk_id),
														 //'fields' => array('id','name','address_1','address_2','city','state','zip','contact','country')
														)
										   );
        $kioskDetails = $kioskDetails_query->hydrate(false);
        if(!empty($kioskDetails)){
            $kioskDetails = $kioskDetails->first();
        }else{
            $kioskDetails = array();
        }
		//pr($kioskDetails);die;
		if(($this->request->Session()->read('Auth.User.group_id') == KIOSK_USERS  &&
		   $this->request->Session()->read('Auth.User.user_type') == "wholesale") ||
		   $this->request->Session()->read('Auth.User.group_id') == ADMINISTRATORS ||
		   $this->request->session()->read('Auth.User.group_id') == SALESMAN ||
		   $this->request->session()->read('Auth.User.group_id') == inventory_manager ||
		   $this->request->session()->read('Auth.User.group_id') == FRANCHISE_OWNER){//forsaleman add by rajju
			 
			//$kiosk_id = $this->Session->read('kiosk_id');
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
		//pr($settingArr);die;
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
			//Start:Added by Yamini
			$quantityTotal = array();
			foreach($productSaleArr as $key => $saleData){
				if(array_key_exists($saleData['product_id'],$quantityTotal)){
					$quantityTotal[$saleData['product_id']]+= $saleData['quantity'];	
				}else{
					$quantityTotal[$saleData['product_id']] = $saleData['quantity'];
				}
			}
			//End:Added by Yamini
			foreach($productSaleArr as $key => $productSaleDetail){
				$saleProductId = $productSaleDetail['product_id'];
				$saleReceiptId = $productSaleDetail['product_receipt_id'];
				$saleKey = "$saleProductId|$saleReceiptId";
				$qttyArr[$saleKey] = $quantityTotal[$saleProductId];//$productSaleDetail['quantity'];//modified by yamini
				if(array_key_exists($saleKey,$returnQuantityArr)){
					$qttyArr[$saleKey]+= $returnQuantityArr[$saleKey];
				}
			}
		}
		//pr($qttyArr);die;
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
		//pr($userName);die;
		$user_name = $userName['username']; 
		$paymentDetails_query = $paymentTable->find('all',array(
																 'conditions' => array('product_receipt_id' => $id),
																 'recursive' => -1)
													 );
        $paymentDetails = $paymentDetails_query->hydrate(false);
        if(!empty($paymentDetails)){
            $paymentDetails = $paymentDetails->toArray();
        }
		$payment_method = array();
		foreach($paymentDetails as $key=>$paymentDetail){
			//pr($paymentDetail);
			$payment_method[] = $paymentDetail['payment_method']." ".$CURRENCY_TYPE.$paymentDetail['amount'];
		}
		$payment_method1 = array();
		foreach($paymentDetails as $key=>$paymentDetail){
			//pr($paymentDetail);
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
		//pr($users);die;
		//pr($productReceipt);die;
		 
		$this->set(compact('productReceipt','users', 'kiosk','vat','productName','customerEmail','paymentDetails','settingArr','customer_data','payment_method','user_name','productCode','kioskTable','kioskContact','countryOptions','currency','qttyArr','kioskDetails','payment_method1','new_kiosk_data'));
		//pr($sale_table);die;
		if ($this->request->is(array('post', 'put'))) {
			 $emailContent = $this->request['data']['emailContent'];
			//$client = new \Pdfcrowd\HtmlToPdfClient("saurav7767", "4d983e65cc36982b7d138c1590e06e8b");
			$emailData = base64_decode($emailContent);
			$timestamp =  date('d-m-Y_H-i-s');
			$filename = "Invoice_".$timestamp.".pdf";
			$path = ROOT."/webroot";
			$file = $path . "/" . $filename;
			
			
			$this->html_to_pdf($file,$emailData);
			//$client->convertStringToFile($emailData, $file);
			
            $separator = md5(time());
			
			$send_by_email = Configure::read('send_by_email');
			$emailSender = Configure::read('EMAIL_SENDER');
			//pr($customer_data);die;
			$Email = new Email();
			//echo "hi";die;
			$Email->config('default');
			$Email->viewVars(array(
								   'qttyArr' => $qttyArr,
								   'settingArr' => $settingArr,
								   'productReceipt' => $productReceipt,
								   'kiosk' => $kiosk,
								   'vat' => $vat,
								   'productName' => $productName,
								   'currency' => $currency,
								   'refundOptions' => $refundOptions,
								   'kioskDetails' => $kioskDetails,
								   'productCode' => $productCode,
								   'payment_method1' => $payment_method1,
								   'users' => $users,
                                   'customer_table' => $customer_data,
                                   'sale_table' => $email_sale_table,
								   'invoice' => 'invoice',
								   'customer_data' => $customer_data,
								   'new_kiosk_data' => $new_kiosk_data,
								   "html" => $emailData,
								   )
							 );
			//$Email->config(array('from' => 'me@example.org', 'transport' => 'MyCustom'));
			//$Email = new CakeEmail(array('from' => 'me@example.org', 'transport' => 'MyCustom'));
             
			$emailTo = $this->request['data']['customer_email'];
			//$Email->template('attchment_template');
			$Email->template('customer_receipt');
			$Email->attachments([
									$filename => [
										'file' => $file,
										'mimetype' => 'pdf',
										'contentId' => $separator
									]
								]);
			
			$Email->emailFormat('both');
			$Email->to($emailTo);
			$Email->transport(TRANSPORT);
			$Email->from([$send_by_email => $emailSender]);
			//$Email->sender("sales@oceanstead.co.uk");  //$this->fromemail
			$Email->subject('Order Receipt');
			if($Email->send()){
				unlink($file);
               $this->Flash->success(__('Email has been successfully sent.'));
				
			}
		}
		
		//$this->layout = 'default';
		if($this->request->Session()->read('Auth.User.group_id') == ADMINISTRATORS ||
		   $this->request->session()->read('Auth.User.group_id') == FRANCHISE_OWNER ||
		   $this->request->session()->read('Auth.User.group_id') == SALESMAN ||
		   $this->request->session()->read('Auth.User.group_id') == inventory_manager ||// saleman add by rajju
		   $this->request->Session()->read('Auth.User.group_id') == KIOSK_USERS &&  //KIOSK_USERS 
		   $this->request->Session()->read('Auth.User.user_type') =='wholesale'
		   ){
			//$this->render('generate_receipt_withreturn');
			$this->render('generate_receipt_new');
			//$this->render('generate_receipt');
		}elseif($this->request->Session()->read('Auth.User.group_id') == KIOSK_USERS &&  //KIOSK_USERS
		   $this->request->Session()->read('Auth.User.user_type') == 'retail'){
			$this->render('generate_receipt_withreturn');
		}
	}
    
    
    
    public function search($keyword = ''){
		$cust_hidden_id = 0;
        $kiosk_id = $this->request->Session()->read('kiosk_id');        
		$kioskProdctSaleTable_source = "kiosk_{$kiosk_id}_product_sales";
        $product_recit_table_source = "kiosk_{$kiosk_id}_product_receipts";
        $paymentTable_source = "kiosk_{$kiosk_id}_payment_details";
		
		if(array_key_exists('kiosk_id',$this->request->query) || array_key_exists('kiosk-id',$this->request->query)){
			//echo'hi';
			if(array_key_exists('kiosk_id',$this->request->query)){
				$kiosk_id = $this->request->query['kiosk_id'];
			}
			if(array_key_exists('kiosk-id',$this->request->query)){
				$kiosk_id = $this->request->query['kiosk-id'];
			}
			
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
		}
		//if(array_key_exists('kiosk_id',$this->request->query)){
		//	$kiosk_id = $this->request->query['kiosk_id'];
		//	$kioskProdctSaleTable_source = "kiosk_{$kiosk_id}_product_sales";
		//	$product_recit_table_source = "kiosk_{$kiosk_id}_product_receipts";
		//	$paymentTable_source = "kiosk_{$kiosk_id}_payment_details";
		//}else{
		//	$kiosk_id = $this->request->Session()->read('kiosk_id');
		//	if(!empty($kiosk_id)){
		//		$kiosk_id = 10000;
		//		$kioskProdctSaleTable_source = "kiosk_{$kiosk_id}_product_sales";
		//		$product_recit_table_source = "kiosk_{$kiosk_id}_product_receipts";
		//		$paymentTable_source = "kiosk_{$kiosk_id}_payment_details";
		//	}else{
		//		$kioskProdctSaleTable_source = "kiosk_product_sales";
		//		$product_recit_table_source = "product_receipts";
		//		$paymentTable_source = "payment_details";
		//	}
		//}
		//echo $kiosk_id;die;
		$this->set(compact('kiosk_id'));
		$receiptTable = TableRegistry::get($product_recit_table_source,[
																		'table' => $product_recit_table_source,
																	]);
			
		$kioskProdctSalesTable = TableRegistry::get($kioskProdctSaleTable_source,[
																		'table' => $kioskProdctSaleTable_source,
																	]);
			
		$paymentTable = TableRegistry::get($paymentTable_source,[
																		'table' => $paymentTable_source,
																	]);
		$conditionArr = array();
		$settingArr = $this->setting;
		if(array_key_exists('payment_type',$this->request->query) &&
		   !empty($this->request->query['payment_type'])){
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
		$invoiceSearchKeyword = "";
		if(array_key_exists('invoice_detail',$this->request->query) &&
		   !empty($this->request->query['invoice_detail'])){
			$invoiceSearchKeyword = $this->request->query['invoice_detail'];
			$this->set('invoiceSearchKeyword',$this->request->query['invoice_detail']);
		}
		
		if(array_key_exists('start_date',$this->request->query) &&
		   !empty($this->request->query['start_date'])){
			$this->set('start_date',$this->request->query['start_date']);
		}
		
		if(array_key_exists('end_date',$this->request->query) &&
		   !empty($this->request->query['end_date'])){
			$this->set('end_date',$this->request->query['end_date']);
		}
		
		
		if(array_key_exists('start_date',$this->request->query) &&
		   array_key_exists('end_date',$this->request->query) &&
		   !empty($this->request->query['start_date']) &&
		   !empty($this->request->query['end_date'])){
			if(array_key_exists('date_type',$this->request->query)){
				$date_type = $this->request->query['date_type'];
                $this->set(compact('date_type'));
				if($date_type == "payment"){
					$conditionArr[] = array(
						"created >=" => date('Y-m-d', strtotime($this->request->query['start_date'])),
						"created <" => date('Y-m-d', strtotime($this->request->query['end_date']. ' +1 Days')),			
					       );
				}else{
					$conditionArr1 = array();
					$conditionArr1[] = array(
						"created >" => date('Y-m-d', strtotime($this->request->query['start_date'])),
						"created <" => date('Y-m-d', strtotime($this->request->query['end_date']. ' +1 Days')),			
					       );
					$Receipts_query = $receiptTable->find('list',array(
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
					$conditionArr['product_receipt_id IN'] = $Receipts;
				}
			}else{
				$conditionArr[] = array(
						"created >" => date('Y-m-d', strtotime($this->request->query['start_date'])),
						"created <" => date('Y-m-d', strtotime($this->request->query['end_date']. ' +1 Days')),			
					       );
			}
			
		}
		
		$customerResult = array();
		if(array_key_exists('search_kw',$this->request->query) &&
		   !empty($this->request->query['search_kw'])){
			$textKeyword = $this->request->query['search_kw'];
			if(!empty($textKeyword) && array_key_exists('invoice_detail',$this->request->query)){
				if($invoiceSearchKeyword=="receipt_number"){
					$conditionArr['product_receipt_id'] =  (int)$textKeyword;
				}elseif($invoiceSearchKeyword=="business"){
					//echo $textKeyword;die;
					$customerIds_query = $this->Customers->find('list',array(
																				'conditions'=>array(
															"OR" => array(
															"LOWER(`Customers`.`business`) like" => strtolower("%$textKeyword%"),
															"LOWER(`Customers`.`fname`) like" => strtolower("%$textKeyword%"),
															"LOWER(`Customers`.`lname`) like" => strtolower("%$textKeyword%"),
															)									    
												    ),
																	'valueField' => 'id',			
																	));
					$customerIds_query = $customerIds_query->hydrate(false);
					if(!empty($customerIds_query)){
						$customerIds = $customerIds_query->toArray();
					}else{
						$customerIds = array();
					}
					//pr($customerIds);die;
					$conditionArr['product_receipt_id IN'] = 0;
					if(count($customerIds) > 0){
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
						//if date range search
						//pr($searchCriteria);die;
						$cutomerReceipts_query = $receiptTable->find('all',array('fields' => array('id'),
															'conditions' => $searchCriteria,
															));
						//pr($cutomerReceipts_query);die;
						$cutomerReceipts_query = $cutomerReceipts_query->hydrate(false);
						if(!empty($cutomerReceipts_query)){
							$cutomerReceipts = $cutomerReceipts_query->toArray();
						}else{
							$cutomerReceipts = array();
						}
						//pr($cutomerReceipts);die;
						$receiptIDs = array();

						if( count($cutomerReceipts) ){
							//echo $cutomerReceipts['ProductReceipt']['id'];
							foreach($cutomerReceipts as $cutomerReceipt){
								$receiptIDs[] = $cutomerReceipt['id'];
							}
							if(empty($receiptIDs)){
								$receiptIDs = array(0 => null);
							}
							$conditionArr['product_receipt_id IN'] = $receiptIDs;
						}
					}
					//pr($conditionArr);die;
				}elseif($invoiceSearchKeyword=="customer_id"){//invoice_detail
					$customerID =  (int)$textKeyword;
					$cust_hidden_id = $customerID;
					$searchCriteria['customer_id'] = $customerID;
					if(array_key_exists('start_date',$this->request->query) &&
						array_key_exists('end_date',$this->request->query) &&
						!empty($this->request->query['start_date']) &&
						!empty($this->request->query['end_date'])){
						//$conditionArr = array(
						//			 "created >" => date('Y-m-d', strtotime($this->request->query['start_date'])),
						//			 "created <" => date('Y-m-d', strtotime($this->request->query['end_date']. ' +1 Days')),			
						//				);
						$date_type = $this->request->query['date_type'];
						if($date_type == "payment"){
							
						}else{
							$searchCriteria[] = array(
									 "created >" => date('Y-m-d', strtotime($this->request->query['start_date'])),
									 "created <" => date('Y-m-d', strtotime($this->request->query['end_date']. ' +1 Days')),			
										);							
						}

					 }
					// pr($searchCriteria);die;
					$cutomerReceipts_query = $receiptTable->find('all',array('fields' => array('id'),
															'conditions' => $searchCriteria));
					$cutomerReceipts_query = $cutomerReceipts_query->hydrate(false);
					if(!empty($cutomerReceipts_query)){
						$cutomerReceipts = $cutomerReceipts_query->toArray();
					}else{
						$cutomerReceipts = array();
					}
					
					//pr($cutomerReceipts);die;
					$receiptIDs = array();
					$conditionArr['product_receipt_id IN'] = 0;
					if( count($cutomerReceipts) ){
						//echo $cutomerReceipts['ProductReceipt']['id'];
						foreach($cutomerReceipts as $cutomerReceipt){
							$receiptIDs[] = $cutomerReceipt['id'];
						}
						if(empty($receiptIDs)){
							$receiptIDs = array(0 => null);
						}
						$conditionArr['product_receipt_id IN'] = $receiptIDs;
					}
				}
				$this->set('textKeyword',$this->request->query['search_kw']);
			}
		}
		
		
		
		$agent_id = 0;
		if(array_key_exists('agent_id',$this->request->query) && !empty($this->request->query['agent_id'])){
			$agent_id = $this->request->query['agent_id'];
			$agent_cust_res = $this->Customers->find("list",['conditions' => [
														   "agent_id" => $agent_id,
														   ],
															'keyField' => "id",
															"valueField" => "agent_id",
															])->toArray();
			
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
				$cutomerReceipts_query = $receiptTable->find('all',array('fields' => array('id'),
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
				$conditionArr['product_receipt_id IN'] = 0;
				if( count($cutomerReceipts) ){
					//echo $cutomerReceipts['ProductReceipt']['id'];
					foreach($cutomerReceipts as $cutomerReceipt){
						$receiptIDs[] = $cutomerReceipt['id'];
					}
					if(empty($receiptIDs)){
						$receiptIDs = array('0'=>null);
					}
					$conditionArr['product_receipt_id IN'] = $receiptIDs;
				}
						 
			}
			
			//$conditionArr['agent_id'] = $agent_id;
		}
		$this->set(compact('agent_id'));
		
		//pr($conditionArr);die;
		$listPaymentDet_query = $paymentTable->find('all',array('fields'=>array('product_receipt_id','amount'),'conditions'=>$conditionArr));
        $listPaymentDet = $listPaymentDet_query->hydrate(false);
        if(!empty($listPaymentDet)){
            $listPaymentDet = $listPaymentDet->toArray();
        }else{
			$listPaymentDet = array();
		}
		$listPayment = array();
		foreach($listPaymentDet as $lp => $list_payment){
			if(array_key_exists($list_payment['product_receipt_id'],$listPayment)){
				$listPayment[$list_payment['product_receipt_id']]+= $list_payment['amount'];
			}else{
				$listPayment[$list_payment['product_receipt_id']] = $list_payment['amount'];
			}
		}
		$lptotalPaymentAmount = 0;
		$lpgrandNetAmount = 0;
		$totalBillCost = 0;
		$totalCost = 0;
		
		if(count($listPayment)){
			$receiptIdArr = array();
			$productReceiptDetail = array();
			if($listPayment){
				$receiptIdArr = array_keys($listPayment);
			}
					//code for getting cost price of products*******************
                    $this->loadModel('KioskProductSales');
					if(count($receiptIdArr)){
						$productQttArr_query = $kioskProdctSalesTable->find('all', array('conditions' => array('product_receipt_id IN' => $receiptIdArr), 'fields' => array('product_id','quantity','product_receipt_id'), 'recursive' => -1));
                        $productQttArr_res = $productQttArr_query->hydrate(false);
                        if(!empty($productQttArr_res)){
                            $productQttArr = $productQttArr_res->toArray();
                        }
						$receiptIdDetail = array();
						$productIdsArr = array();
						foreach($productQttArr as $key => $productQtt){
							//$receiptIdDetail[$productQtt['KioskProductSale']['product_receipt_id']][] = $productQtt['KioskProductSale'];
							$productIdsArr[$productQtt['product_id']] = $productQtt['product_id'];
						}
						if(empty($productIdsArr)){
							$productIdsArr = array(0 => null);
						}
                        $costPriceList_query = $this->Products->find('list',
                                                         ['conditions' => [
                                                                           'Products.id IN' => $productIdsArr
                                                                           ],
                                                                'keyField' => 'id',
                                                                'valueField' => 'cost_price',
                                                         ]);
                        $costPriceList_res =$costPriceList_query->hydrate(false);
                        if(!empty($costPriceList_res)){
                            $costPriceList = $costPriceList_res->toArray();
                        }

						foreach($productQttArr as $key => $productQtt){
							if(!array_key_exists($productQtt['product_id'],$costPriceList)){continue;}
							$costPrice = $costPriceList[$productQtt['product_id']]*$productQtt['quantity'];
							$totalCost+=$costPrice;
						}
					}
					//*********************till here
			if(count($listPayment) && count($conditionArr)){
                if(empty($receiptIdArr)){
                    $receiptIdArr = array(0 =>null);
                }
				$productReceiptDetail_query = $receiptTable->find('all',array('conditions'=>array('id IN'=>$receiptIdArr),'fields'=>array('id','vat','status','bill_cost','created'),'recursive'=>-1));
			}else{
				$productReceiptDetail_query = $receiptTable->find('all',array('fields'=>array('id','vat','status','bill_cost','created'),'recursive'=>-1));
			}
            $productReceiptDetail_res = $productReceiptDetail_query->hydrate(false);
            if(!empty($productReceiptDetail_res)){
                $productReceiptDetail = $productReceiptDetail_res->toArray();
            }else{
                $productReceiptDetail = array();
            }
        
			$createdArr = array();
			foreach($productReceiptDetail as $key=>$productReceiptDta){
				//showing the data with product receipt status == 0 ie. the payment went through and data saved properly
				if($productReceiptDta['status']==0){
					$paymentAmount = 0;
					$createdArr[$productReceiptDta['id']] = $productReceiptDta['created'];
					$totalBillCost+=floatval($productReceiptDta['bill_cost']);
					if(array_key_exists($productReceiptDta['id'],$listPayment)){
						$paymentAmount = $listPayment[$productReceiptDta['id']];
					}
					$lptotalPaymentAmount+=floatval($paymentAmount);
					$vatPercentage = $productReceiptDta['vat']/100;
					$netAmount = $paymentAmount/(1+$vatPercentage);
					$lpgrandNetAmount+=floatval($netAmount);
				}
			}
		}
		
		/*$lptotalPaymentAmount = 0;
		$lpgrandNetAmount = 0;
		foreach($productReceiptDetail as $key=>$productReceiptDta){
			//showing the data with product receipt status == 0 ie. the payment went through and data saved properly
			if($productReceiptDta['ProductReceipt']['status']==0){
				$totalBillCost+=floatval($productReceiptDta['ProductReceipt']['bill_cost']);
                $receiptIdArr[$productReceiptDta['ProductReceipt']['id']] = $productReceiptDta['ProductReceipt']['id'];//capturing the receipt ids
				$paymentAmount = $productReceiptDta['ProductReceipt']['bill_amount'];
				$lptotalPaymentAmount+=floatval($paymentAmount);
				$vatPercentage = $productReceiptDta['ProductReceipt']['vat']/100;
				$netAmount = $paymentAmount/(1+$vatPercentage);
				$lpgrandNetAmount+=$netAmount;
			}
		}*/
		
		$lptotalVat = $lptotalPaymentAmount - $lpgrandNetAmount;
		$lptotalVat = $lptotalVat;
		$this->set(compact('lptotalPaymentAmount','lpgrandNetAmount','lptotalVat','totalCost'));
		
		//pr($conditionArr);die;
		//$paymentTable->find(['conditions'])
         $this->paginate = [
                                    'order' => [
                                        'product_receipt_id' => 'DESC'
                                    ],
                                    'conditions' => [$conditionArr],
									'limit' => 50
                                   // 'contain' => ['ProductReceipts']
                                ];
		
        //pr($this->paginate);die;
		$productReceipts = $this->paginate($paymentTable);
		//pr($productReceipts);die;
		$recipt_table_data = array();
        $y_recipt_ids = array();
		foreach($productReceipts as $s_key => $s_value){
			$y_recipt_ids[] = $s_value->product_receipt_id;
		}
		if(empty($y_recipt_ids)){
            $y_recipt_ids = array(0 => null);
        }
		$recipt_table_data_query = $receiptTable->find('all',[
								   'conditions' => ['id IN' => $y_recipt_ids]
								   ]);
		$recipt_table_data_query = $recipt_table_data_query->hydrate(false);
		if(!empty($recipt_table_data_query)){
			$recipt_table_data = $recipt_table_data_query->toArray();
		}else{
			$recipt_table_data = array();
		}
		//pr($recipt_table_data);die;
		$this->set(compact('recipt_table_data'));
		//$this->Paginator->settings = array(
		//				'conditions' => $conditionArr,
		//				'limit' => 50,
		//				'order' => 'PaymentDetail.id DESC'
		//				   );
		
		//$productReceipts = $this->Paginator->paginate('PaymentDetail');
		
		$reciptTableData = $customerIdArr = array();
		foreach($recipt_table_data as $receiptDetail){
			if($invoiceSearchKeyword=="receipt_number"){
				$cust_hidden_id = $receiptDetail['customer_id'];
			}
			//pr($receiptDetail);die;
			$reciptTableData[$receiptDetail['id']] = $receiptDetail;
			$customerIdArr[] = $receiptDetail['customer_id'];
		}
		$this->set('recipt_table_data', $reciptTableData);
		if(empty($customerIdArr)){
			$customerIdArr = array(0 => null); 
		}
		$customerBusiness_query = $this->Customers->find('list',
                                                         ['conditions' => [
                                                                           'Customers.id IN' => $customerIdArr
                                                                           ],
                                                                 'keyField' => 'id',
                                                                'valueField' => 'business',
                                                         ]);
        $customerBusiness = $customerBusiness_query->hydrate(false);
        if(!empty($customerBusiness)){
            $customerBusiness = $customerBusiness->toArray();
        }else{
            $customerBusiness = array();
        }
		$customerCountry_query = $this->Customers->find('list',
                                                         ['conditions' => [
                                                                           'Customers.id IN' => $customerIdArr,
																		   'Customers.country ' => "OTH",
                                                                           ],
                                                                 'keyField' => 'id',
                                                                'valueField' => 'country',
                                                         ]);
        $customerCountry = $customerCountry_query->hydrate(false);
        if(!empty($customerCountry)){
            $customerCountry = $customerCountry->toArray();
        }
		
		if($this->request->session()->read('Auth.User.group_id') == ADMINISTRATORS || $this->request->session()->read('Auth.User.group_id') == FRANCHISE_OWNER){
			//$session_id = $this->Session->id();
			$user_id = $this->request->Session()->read('Auth.User.id');
			$data_arr = array('kiosk_id' => $kiosk_id);
			$jsondata = json_encode($data_arr);
			$this->loadModel('UserSettings');
			$res_query = $this->UserSettings->find('all',array('conditions' => array(
																		 'user_id' => $user_id,
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
									'setting_name' => "search",
									'data' => $jsondata
									);
				$entity = $this->UserSettings->get($userSettingid);
				$entity = $this->UserSettings->patchEntity($entity,$data_to_save);
				$this->UserSettings->save($entity);
			}else{
				$data_to_save = array(
									'user_id' => $user_id,
									'user_session_key' => $session_id,
									'setting_name' => "search",
									'data' => $jsondata
									);
				$entity = $this->UserSettings->newEntity();
				$entity = $this->UserSettings->patchEntity($entity,$data_to_save);
				$this->UserSettings->save($data_to_save);
			}
		}
		$kiosks_query = $this->Kiosks->find('list');
		$kiosks_query = $kiosks_query->hydrate(false);
		if(!empty($kiosks_query)){
			$kiosks = $kiosks_query->toArray();
		}else{
			$kiosks = array();
		}
		
		$agents_query = $this->Agents->find('list');
		$agents_query = $agents_query->hydrate(false);
		
		if(!empty($agents_query)){
			$agents = $agents_query->toArray();
		}
		$agents[0] = "Select Acc manager";
		ksort($agents);
		
		
		
		
		
		$this->set(compact('productReceipts','customerBusiness','totalAmount','totalBillCost','createdArr','kiosks','agents','customerCountry','cust_hidden_id'));
		$this->render('index');
	}
    
    public function updatePayment($paymentId = ''){
		$users = $this->Users->find("list",['keyField' => 'id',
								   'valueField' => 'f_name',
								   ])->toArray();
		// $this->PaymentDetail->setSource('t_payment_details'); commented on 11th may 2016
		if(array_key_exists('1',$this->request->params['pass'])){
			$kiosk_id = $this->request->params['pass'][1];
		}else{
			$kiosk_id = $this->request->Session()->read('kiosk_id');
		}
		
		if(!empty($kiosk_id)){
			$source = "kiosk_{$kiosk_id}_payment_details";
			$recit_source = "kiosk_{$kiosk_id}_product_receipts";
			$payment_detailTable = TableRegistry::get($source,[
                                                                                    'table' => $source,
                                                                                ]);
			$product_reciptTable = TableRegistry::get($recit_source,[
                                                                                    'table' => $recit_source,
                                                                                ]);
			//$this->PaymentDetail->setSource($source);
			//$this->ProductReceipt->setSource($recit_source);
		}else{
            $payment_detailTable = TableRegistry::get("payment_details");
            $product_reciptTable = TableRegistry::get("product_receipts");
		}
		//$this->PaymentDetail->setSource('kiosk_1_payment_details');//
		
		$paymentData_query = $payment_detailTable->find('all',array(
							'conditions' => array('id'=>$paymentId),
							'recursive' => -1
								)
							  );
        $paymentData_res = $paymentData_query->hydrate(false);
        if(!empty($paymentData_res)){
            $paymentData = $paymentData_query->first();  
        }else{
            $paymentData = array();
        }
		
		$oldPmtMethod = $paymentData['payment_method'];
		
		$recit_id = $paymentData['product_receipt_id'];
        
        $result_query = $product_reciptTable->get($recit_id);
        if(!empty($result_query)){
            $result = $result_query->toArray();
        }else{
			$result = array();
		}
        
		$recit_created = $result['created'];
		$agent_id = $result['agent_id'];
		$this->set(compact('recit_created'));
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
			
			if($ticked == 1){   // normal trnsection or checked
				if(array_key_exists("date_box_date",$this->request->data)){
					$date_box_date = date("Y-m-d G:i:s",strtotime($this->request->data['date_box_date']));
			   }else{
					$date_box_date = "";
			   }
				if(array_key_exists('added_amount',$this->request->data) && is_numeric($this->request->data['added_amount']) && $this->request->data['added_amount'] > 0){
					$sale_amount = round($this->request->data['sale_amount'],2);
					$added_amount = round($this->request->data['added_amount'],2);
					$old_amt = round($this->request->data['old_amt'],2);
					
					$sum_up_amt = $added_amount + $old_amt;
					$sum_up_amt = round($sum_up_amt,2);
					if($sale_amount != $sum_up_amt){
						$this->Flash->error('Payment could not be updated!');
						return $this->redirect(array('action' => 'update_payment',$paymentId));
						die;
					}
					$new_paymentMode = $this->request->data['new_change_mode'];
					if($new_paymentMode == "On Credit"){
						$paymentStatus_for_new = 0;
					}else{
						$paymentStatus_for_new = 1;
					}
					$new_box_desc = "";
					if(array_key_exists('new_box_desc',$this->request->data)){
						$new_box_desc = $this->request->data['new_box_desc'];
					}
					$paymentDetailData_new = array(
							'product_receipt_id' => $recit_id,
							'payment_method' => $new_paymentMode,
							'amount' => $this->request->data['added_amount'],
							'payment_status' => $paymentStatus_for_new,
							'description' => $new_box_desc,
							'agent_id' => $agent_id,
							'created' => $date_box_date,
							//'created' => $recit_created
							   );
					$payment_detailTable->behaviors()->load('Timestamp');
					$new_entity = $payment_detailTable->newEntity($paymentDetailData_new,['validate' => false]);
					$patch_entity = $payment_detailTable->patchEntity($new_entity,$paymentDetailData_new,['validate' => false]);
					$payment_detailTable->save($patch_entity);
					$description = "";
					if(array_key_exists('desc',$this->request->data)){
						$description = $this->request->data['desc'];
					}
					
					$old_amt = $this->request->data['old_amt'];
					$old_payment_data = array(
							'id' => $paymentId,
							'payment_method' => $paymentMode,
							'amount' => $old_amt,
							'payment_status' => $paymentStatus,
							'description' => $description,
							'agent_id' => $agent_id,
							'created' => $date_box_date,
							//'created' => $recit_created
							   );
					$payment_detailTable->behaviors()->load('Timestamp');
					$getID = $payment_detailTable->get($paymentId);
					$patchEntity = $payment_detailTable->patchEntity($getID,$old_payment_data,['validate' => false]);
					$payment_detailTable->save($patchEntity);
				}
				
				$description = "";
					if(array_key_exists('desc',$this->request->data)){
						$description = $this->request->data['desc'];
					}
				
				$created  = date("Y-m-d G:i:s");
				//pr($paymentData);die;
				if($paymentData['payment_method'] == "On Credit"){   // changing created when changing payment method from  on-credit to any other
					$paymentDetailData = array(
							'id' => $paymentId,
							'payment_method' => $paymentMode,
							'payment_status' => $paymentStatus,
							'description' => $description,
							'created' => $created,
							 'created' => $date_box_date,
							   );
				}
				
				if($paymentMode == "On Credit"){  //when changed payment method is On Credit add recit date to payment table created
					$paymentDetailData = array(
							'id' => $paymentId,
							'payment_method' => $paymentMode,
							'payment_status' => $paymentStatus,
							'description' => $description,
							'created' => $recit_created
							   );
				}
				
				if(empty($paymentDetailData)){
					$paymentDetailData = array(
							'id' => $paymentId,
							'payment_method' => $paymentMode,
							'payment_status' => $paymentStatus,
							'description' => $description,
							'created' => $date_box_date,
							   );
				}
			}else{ // correcting or unchecked
				
				if(array_key_exists('added_amount',$this->request->data) && is_numeric($this->request->data['added_amount']) && $this->request->data['added_amount'] > 0){
                    
                    $sales_amount = round($this->request->data['sale_amount'],2);
					$old_amount = $this->request->data['old_amt'];
					$added_amount = $this->request->data['added_amount'];
					$sum_up_amount = $old_amount + $added_amount;
					$sum_up_amount  = round($sum_up_amount,2);
                    
					if($sales_amount != $sum_up_amount){
						$this->Flash->error('Payment could not be updated!');
						return $this->redirect(array('action' => 'update_payment',$paymentId));
						die;
					}
					$new_paymentMode = $this->request->data['new_change_mode'];
					if($new_paymentMode == "On Credit"){
						$paymentStatus_for_new = 0;
					}else{
						$paymentStatus_for_new = 1;
					}
					$new_box_desc = "";
					if(array_key_exists('new_box_desc',$this->request->data)){
						$new_box_desc = $this->request->data['new_box_desc'];
					}
					
					$paymentDetailData_new = array(
							'product_receipt_id' => $recit_id,
							'payment_method' => $new_paymentMode,
							'amount' => $this->request->data['added_amount'],
							'payment_status' => $paymentStatus_for_new,
							'description' => $new_box_desc,
							'agent_id' => $agent_id,
							//'created' => $recit_created
							   );
					$payment_detailTable->behaviors()->load('Timestamp');
					$entity_new = $payment_detailTable->newEntity($paymentDetailData_new,['validate' =>false]);
					$entity_patch = $payment_detailTable->patchEntity($entity_new,$paymentDetailData_new,['validate' =>false]);
					$payment_detailTable->save($entity_patch);
					
					$description = "";
					if(array_key_exists('desc',$this->request->data)){
						$description = $this->request->data['desc'];
					}
					
					$old_amt = $this->request->data['old_amt'];
					$old_payment_data = array(
							'id' => $paymentId,
							'payment_method' => $paymentMode,
							'amount' => $old_amt,
							'payment_status' => $paymentStatus,
							'description' => $description,
							'agent_id' => $agent_id,
							//'created' => $recit_created
							   );
					$payment_detailTable->behaviors()->load('Timestamp');
					$getID = $payment_detailTable->get($paymentId);
					$patchEntity = $payment_detailTable->patchEntity($getID,$old_payment_data,['validate' => false]);
					$payment_detailTable->save($patchEntity);
				}
				$description = "";
					if(array_key_exists('desc',$this->request->data)){
						$description = $this->request->data['desc'];
					}
				if($paymentMode == "On Credit"){  //when changed payment method is On Credit add recit date to payment table created
					$paymentDetailData = array(
							'id' => $paymentId,
							'payment_method' => $paymentMode,
							'payment_status' => $paymentStatus,
							'description' => $description,
							'created' => $recit_created
							   );
				}
				if(empty($paymentDetailData)){
					$paymentDetailData = array(
							'id' => $paymentId,
							'payment_method' => $paymentMode,
							'payment_status' => $paymentStatus,
							'description' => $description,
							   );
				}
			}
            //$payment_detailTable->behaviors()->load('Timestamp');
            $pay_det = $payment_detailTable->get($paymentId);
			
			//pr($paymentDetailData);die;
			$payment_detaildata = $payment_detailTable->patchEntity($pay_det,$paymentDetailData);
			if($payment_detailTable->save($payment_detaildata)){
				$paymentID = $payment_detaildata->id;
				$pmtMethod = $payment_detaildata->payment_method;
				$receiptID = $payment_detaildata->product_receipt_id;
				$user_id = $this->request->Session()->read('Auth.User.id');
				//$modified  = date("Y-m-d G:i:s");
				//$created  = date("Y-m-d G:i:s");
				if(empty($kiosk_id)){
					$kiosk_id = 10000;
				}
				$logData = array(
								 'user_id'=>$user_id,
								 'pmt_id'=>(int)$paymentID,
								 'old_pmt_method'=>$oldPmtMethod,
								 'pmt_method'=>$pmtMethod,
								 'receipt_id'=>$receiptID,
								 'kiosk_id' => $kiosk_id,
								 //'modified'=>$modified,
								 //'created'=>$created,
								 //'memo'=>$paymentID,
								 'receipt_type' => 1,
								);
				//pr($logData);die;
				if($oldPmtMethod != $pmtMethod){
					$newLog = $this->PmtLogs->newEntity($logData,['validate'=>true]);
					$patchLog = $this->PmtLogs->patchEntity($newLog,$logData,['validate'=>true]);
					$this->PmtLogs->save($patchLog);
					$logid = $patchLog->id;
					$log_created = $patchLog->created;
					$str = date("d/m/Y h:i",strtotime($log_created)).", ".$users[$user_id].", ".$oldPmtMethod."->".$pmtMethod;
					if(!empty($description)){
						$str.="||".$description;
					}
					
					$data = array("description" => $str);
					$pay_res = $payment_detailTable->get($paymentId);
					$pay_res = $payment_detailTable->patchEntity($pay_res,$data);
					$payment_detailTable->save($pay_res);
					
					$data = array("memo" => $description);
					$oldLog = $this->PmtLogs->get($logid);
					$oldLog = $this->PmtLogs->patchEntity($oldLog,$data,['validate'=>true]);
					$this->PmtLogs->save($oldLog);
					
				}
				//$this->Session->setFlash("Payment method has been updated to {$paymentMode}");
				if($this->request->session()->read('Auth.User.group_id') == ADMINISTRATORS || $this->request->session()->read('Auth.User.group_id') == FRANCHISE_OWNER){
					$kiosk_id_to_set = $this->get_kiosk_for_invoice();
					$this->Flash->success("Payment method has been updated to {$paymentMode}");
					return $this->redirect(array('action'=>"search?kiosk_id=$kiosk_id_to_set&date_type=invoice"));
				}else{
					$this->Flash->success("Payment method has been updated to {$paymentMode}");
					return $this->redirect(array('action'=>"index"));
				}
			}else{
                echo "bye";die;
            }
		}
		
		$this->set(compact('paymentData'));
	}
	
	
	public function deliveryNote($id = null,$kiosk_id=""){
        
		if(!empty($kiosk_id)){
			$recipt = "kiosk_{$kiosk_id}_product_receipts";
			$kiosk_product_sale = "kiosk_{$kiosk_id}_product_sales";
			$payment_table = "kiosk_{$kiosk_id}_payment_details";
			$products = "kiosk_{$kiosk_id}_products";
		}else{
			$recipt = "product_receipts";
			$kiosk_product_sale = "kiosk_product_sales";
			$payment_table = "payment_details";
			$products = "products";
		}
		$product_receiptsTable = TableRegistry::get($recipt,[
                                                                'table' => $recipt,
                                                            ]);
        $productsTable = TableRegistry::get($products,[
                                                        'table' => $products,
                                                    ]);
		$paymentDetailsTable = TableRegistry::get($payment_table,[
                                                        'table' => $payment_table,
                                                    ]);
        $kiosk_product_salesTable = TableRegistry::get($kiosk_product_sale,[
                                                                            'table' => $kiosk_product_sale,
                                                                        ]);
		//$this->ProductReceipt->setSource($recipt);
		//$this->PaymentDetail->setSource($payment_table);
		//$this->Product->setSource($products);
		//$this->KioskProductSale->setSource($kiosk_product_sale);
		
		if (!$product_receiptsTable->exists($id)) {
			throw new NotFoundException(__('Invalid product receipt'));
		}
		$settingData_query = $this->Settings->find('all');
		$settingData_query = $settingData_query->hydrate(false);
		if(!empty($settingData_query)){
			$settingData = $settingData_query->toArray();
		}else{
            $settingData = array();
        }
		$settingArr = array();
		foreach($settingData as $key => $settingInfo){
			$attribute_name = $settingInfo['attribute_name'];
			$attribute_value = $settingInfo['attribute_value'];
			$settingArr[$attribute_name] = $attribute_value;
		}
		
		$kiosk_query = $this->Kiosks->find('list');
		$kiosk_query = $kiosk_query->hydrate(false);
		if(!empty($kiosk_query)){
			$kiosk = $kiosk_query->toArray();
		}else{
            $kiosk = array();
        }
		
		$productName_query = $productsTable->find('list',[
														  'keyField' => 'id',
														  'valueField' => 'product',
													 ]);
		$productName_query = $productName_query->hydrate(false);
		if(!empty($productName_query)){
			$productName = $productName_query->toArray();
		}else{
            $productName = array();
        }
		$vat = $this->VAT;
		$productReceipt_query = $product_receiptsTable->get($id,[
														  // 'contain' => ['KioskProductSales','Customers']
														   ]);
		
		if(!empty($productReceipt_query)){
			$productReceipt = $productReceipt_query->toArray();
		}else{
            $productReceipt = array();
        }
		
		if(!empty($productReceipt)){
			$recipt_id = $productReceipt['id'];
			$customer_id = $productReceipt['customer_id'];
		}
        
        $sale_table_query = $kiosk_product_salesTable->find('all',['conditions' => [
													'product_receipt_id' => $recipt_id
												  ]]);
		$sale_table_query = $sale_table_query->hydrate(false);
		if(!empty($sale_table_query)){
			$sale_table = $sale_table_query->toArray();
		}else{
			$sale_table = array();
		}
        
        $users_query = $this->Users->find('list', [
                                            'keyField' => 'id',
                                            'valueField' => 'username'
                                        ]);
        
        if(!empty($users_query)){
            $users_query = $users_query->hydrate(false);
            $users = $users_query->toArray();   
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
		
		
        if(!empty($productReceipt)){
            $kiosk_product_dataId = $productReceipt['id'];
            $kiosk_product_data_query = $kiosk_product_salesTable->find('all',[
                                                                               'conditions' => ['product_receipt_id' => $kiosk_product_dataId]
                                                                               ]);
            $kiosk_product_data_query = $kiosk_product_data_query->hydrate(false);
            if(!empty($kiosk_product_data_query)){
                $kiosk_product_data = $kiosk_product_data_query->toArray();
            }else{
                $kiosk_product_data = array();
            }
            
        }
        if(!empty($productReceipt)){
            $customer_dataId = $productReceipt['customer_id'];
            $customer_data_query = $this->Customers->find('all',[
                                                                'conditions' => ['id' => $customer_dataId]
                                                                ]);
            $customer_data_query = $customer_data_query->hydrate(false);
            if(!empty($customer_data_query)){
                $customer_data = $customer_data_query->toArray();
            }else{
                $customer_data = array();
            }
            
        }
		$this->set(compact('kiosk_product_data','customer_data'));
		$productIdArr = array();
		foreach($kiosk_product_data as $key => $productDetail){
			$productIdArr[] = $productDetail['product_id'];
		}
		foreach($productIdArr as $product_id){
			//$product_detail[] =
			$product_detail_query = $productsTable->find('all', array('conditions'=>array('id'=>$product_id),'fields' => array('id','product','product_code')));
			$product_detail_query = $product_detail_query->hydrate(false);
			if(!empty($product_detail_query)){
				$product_detail[] = $product_detail_query->toArray();
			}else{
                $product_detail[] = array();
            }
		}
		foreach($product_detail as $productInfo){
			$productName[$productInfo[0]['id']] = $productInfo[0]['product'];
			$productCode[$productInfo[0]['id']] = $productInfo[0]['product_code'];
		}
		$paymentDetails_query = $paymentDetailsTable->find('all',array('conditions' => array('product_receipt_id' => $id),'recursive' => -1));
		$paymentDetails_query = $paymentDetails_query->hydrate(false);
		if(!empty($paymentDetails_query)){
			$paymentDetails = $paymentDetails_query->toArray();
		}else{
            $paymentDetails = array();
        }
        //pr($customer_data);die;
		$customerEmail = $customer_data[0]['email'];
		if($kiosk_id == "" || $kiosk_id == 0){
			$k_id_to_use = 10000;
		}else{
			$k_id_to_use = $kiosk_id;
		}
		
		$kioskDetails_query = $this->Kiosks->find('all',array(
														 'conditions' => array('Kiosks.id' => $k_id_to_use),
														 'fields' => array('id','name','address_1','address_2','city','state','zip','contact','country')
														)
										   );
        $kioskDetails = $kioskDetails_query->hydrate(false);
        if(!empty($kioskDetails)){
            $kioskDetails = $kioskDetails->first();
        }else{
            $kioskDetails = array();
        }
		$fullAddress = $kioskDetails = $kioskName = $kioskAddress1 = $kioskAddress2 = $kioskCity = $kioskState = $kioskZip  = $kioskZip = $kioskContact = $kioskCountry = $kioskTable = "";
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
		
		
		$currency = $settingArr['currency_symbol'];
		
		$email_sale_table =  $sale_table;
		
		if ($this->request->is(array('post', 'put'))) {
			//pr($customer_data);die;
			$send_by_email = Configure::read('send_by_email');
			$emailSender = Configure::read('EMAIL_SENDER');
			if(array_key_exists('customer_email',$this->request->data) && !empty($this->request->data['customer_email'])){
				$emailTo = $this->request->data['customer_email'];	
			}else{
				$this->Flash->error("Please enter email address");
					return $this->redirect(array('action'=>"deliveryNote",$id,$kiosk_id));
			}
			
			$Email = new Email();
			//echo "hi";die;
			$Email->config('default');
			$Email->viewVars(array(
								   'productReceipt' => $productReceipt,
								   'kiosk' => $kiosk,
								   'productName' => $productName,
								   'customerEmail' => $customerEmail,
								   'paymentDetails' => $paymentDetails,
								   'settingArr' => $settingArr,
								   'productCode' => $productCode,
								   'currency' => $currency,
								   'kioskDetails' => $kioskDetails,
								   'users' => $users,
                                   'customer_table' => $customer_data,
                                   'sale_table' => $email_sale_table,
								   'customer_data' => $customer_data,
								   )
							 );
			//$Email->config(array('from' => 'me@example.org', 'transport' => 'MyCustom'));
			//$Email = new CakeEmail(array('from' => 'me@example.org', 'transport' => 'MyCustom'));
             
			$Email->template('delivery_note');
			$Email->emailFormat('both');
			$Email->to($emailTo);
			$Email->transport(TRANSPORT);
			$Email->from([$send_by_email => $emailSender]);
			//$Email->sender("sales@oceanstead.co.uk");  //$this->fromemail
			$Email->subject('Delivery Note');
			if($Email->send()){
               $this->Flash->success(__('Email has been successfully sent.'));
				//$this->request->Session()->setFlash("Email has been successfully sent");
			}
		}
		
		
		$this->set(compact('productReceipt', 'kiosk','vat','productName','customerEmail','paymentDetails','settingArr','productCode'));
	}
	
	public function edit($id = null,$kiosk_id="") {
		//echo'hi';die;
        $kiosks_id = $this->request->Session()->read('kiosk_id');
        if(!empty($kiosks_id)){
			
            $kiosk_id = $kiosks_id;
        }
		if(!empty($kiosk_id)){
			if($kiosk_id == 10000){
				$recipt_source = "product_receipts";
				$kiosk_product_sale_source = "kiosk_product_sales";
				$payment_table_source = "payment_details";
				$products_source = "products";
			}else{
				$recipt_source = "kiosk_{$kiosk_id}_product_receipts";
				$kiosk_product_sale_source = "kiosk_{$kiosk_id}_product_sales";
				$payment_table_source = "kiosk_{$kiosk_id}_payment_details";
				$products_source = "kiosk_{$kiosk_id}_products";
			}
			
		}else{
			$recipt_source = "product_receipts";
			$kiosk_product_sale_source = "kiosk_product_sales";
			$payment_table_source = "payment_details";
			$products_source = "products";
		}
		$receiptTable = TableRegistry::get($recipt_source,[
															'table' => $recipt_source,
														]);
		$kiosk_product_saleTable = TableRegistry::get($kiosk_product_sale_source,[
                                                                                    'table' => $kiosk_product_sale_source,
                                                                                ]);
		$payment_Table = TableRegistry::get($payment_table_source,[
																	'table' => $payment_table_source,
																]);
		$productsTable = TableRegistry::get($products_source,[
																'table' => $products_source,
															]);
		
		//$this->ProductReceipt->setSource($recipt);
		//$this->PaymentDetail->setSource($payment_table);
		//$this->Product->setSource($products);
		//$this->KioskProductSale->setSource($kiosk_product_sale);
		
		if (!$receiptTable->exists($id)) {
			throw new \Cake\Network\Exception\NotFoundException(__('Invalid product receipt'));
		}
		//$options = array('conditions' => array('ProductReceipt.' . $this->ProductReceipt->primaryKey => $id),'recursive'=>1);
		//$productReceiptData = $this->ProductReceipt->find('first', $options);
		$productReceiptData_query = $receiptTable->get($id,[
															  // 'contain' => ['KioskProductSales','Customers']
															  ]);
		//$productReceiptData_query = $productReceiptData_query->hydrate(false);
		//pr($productReceiptData_query);die;
		if(!empty($productReceiptData_query)){
			$productReceiptData = $productReceiptData_query->toArray();
		}else{
			$productReceiptData = array();
		}
		
		if(!empty($productReceiptData)){
			$sale_table_query = $kiosk_product_saleTable->find('all',[
												  'conditions' => ['product_receipt_id' => $id]
												  ]);
			$sale_table_query = $sale_table_query->hydrate(false);
			if(!empty($sale_table_query)){
				$sale_table = $sale_table_query->toArray();
			}else{
				$sale_table = array();
			}
		}
		$combinedArr = array();
		$productArr = array();
		foreach($sale_table as $sale){
			$productArr[] = $sale['product_id'];
		}
		$bulkDiscount = $productReceiptData['bulk_discount'];
		$vat_in_table = $productReceiptData['vat'];
		$agent_id = $productReceiptData['agent_id'];
		$productQuantityDetail_query = $productsTable->find('all',array(
								'conditions' => array('id IN'=>$productArr),
								'fields' => array('id','quantity')
									)
							      );
		$productQuantityDetail_query = $productQuantityDetail_query->hydrate(false);
		if(!empty($productQuantityDetail_query)){
			$productQuantityDetail = $productQuantityDetail_query->toArray();
		}else{
			$productQuantityDetail = array();
		}
		$productQuantityArr = array();
		foreach($productQuantityDetail as $quantityDetail){
			$productQuantityArr[$quantityDetail['id']] = $quantityDetail['quantity'];
		}
		if ($this->request->is(array('post', 'put')) && isset($_REQUEST['update_invoice_pmt'])) {
			if(array_key_exists('cancel',$this->request->data)){
				return $this->redirect(array('action'=>'index'));
			}
			$newInvoiceOrderAmount = $this->request['data']['final_amount'];
			$productReceiptDetails_query = $receiptTable->get($id);
			if(!empty($productReceiptDetails_query)){
				$productReceiptDetails = $productReceiptDetails_query->toArray();
			}else{
                $productReceiptDetails = array();
            }
	
			$amountToPay = round($this->request['data']['final_amount'],2);
			
			$totalPaymentAmount = 0;
			$amountDesc = array();
			$countCycles = 0;
			$error = array();
			$errorStr = '';
			foreach($this->request['data']['Payment']['Amount'] as $key => $paymentAmount){
				$totalPaymentAmount+= (float)$paymentAmount;
				$paymentDescription = $this->request['data']['Payment']['Description'][$key];
				if(!empty($paymentDescription) && !empty($paymentAmount)){
					$countCycles++;
				}
				if(empty($paymentDescription) && !empty($paymentAmount)){
					$error[] = "Sale could not be created. Payment description must be entered";
					break;
				}
			}
			foreach($this->request['data']['Payment']['Payment_Method'] as $key => $paymentMethod){
				
				if(empty($totalPaymentAmount) &&
					($paymentMethod=="Cheque" ||
					$paymentMethod=="Cash" ||
					$paymentMethod=="Bank Transfer" ||
					$paymentMethod=="Card")){
					$error[] = "Amount must be equivalent to &#163; {$amountToPay}. Please try again";
					break;
				}elseif($totalPaymentAmount<$amountToPay &&
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
			
			$hiddenFields = "";
			foreach($this->request['data']['data']['Payment']['quantity'] as $key => $qtty){
				$productSaleId = $this->request['data']['data']['Payment']['id'][$key];
				$hiddenFields.="<input type = 'hidden' name ='data[Payment][quantity][$key]' value='$qtty'/>";
				$hiddenFields.="<input type = 'hidden' name ='data[Payment][id][$key]' value='$productSaleId'/>";
			}
				$counter = 0;
				if(count($error)>0){
					$errorStr = implode("<br/>",$error);
					$this->Flash->success(__($errorStr));
					$this->set(compact('newInvoiceOrderAmount','hiddenFields','bulkDiscount'));
					$this->render('update_invoice_payment');
					$this->response->send();
					$this->response->stop();
				}else{
					if($this->request['data']['Payment']['Payment_Method'][0] == "On Credit" && $countCycles==1){
						$paymentDetailData = array(
							'product_receipt_id' => $id,
							'payment_method' => $this->request['data']['Payment']['Payment_Method'][0],
							'description' => $this->request['data']['Payment']['Description'][0],
							'amount' => $amountToPay,
							'payment_status' => 0,
							'status' => 1,
							'agent_id' => $agent_id
							   );
						$payment_Table->behaviors()->load('Timestamp');
						$paymentDetails = $payment_Table->newEntity();
						$paymentDetails = $payment_Table->patchEntity($paymentDetails, $paymentDetailData);
						if($payment_Table->save($paymentDetails)){
							$counter++;
						}
					}else{
						foreach($this->request['data']['Payment']['Amount'] as $key => $paymentAmount){
							$paymentMethod = $this->request['data']['Payment']['Payment_Method'][$key];
							$paymentDescription = $this->request['data']['Payment']['Description'][$key];
							
								if($paymentMethod == "On Credit"){
									$payment_status = 0;
								}else{
									$payment_status = 1;
								}
								
								if(!empty($paymentAmount) && !empty($paymentDescription)){
									$paymentDetailData = array(
											'product_receipt_id' => $id,
											'payment_method' => $paymentMethod,
											//'description' => $paymentDescription,
											'amount' => $paymentAmount,
											'payment_status' => $payment_status,
											'status' => 1,
											'agent_id' => $agent_id
											   );
                                    $payment_Table->behaviors()->load('Timestamp');
									$paymentDetails = $payment_Table->newEntity();
									$paymentDetails = $payment_Table->patchEntity($paymentDetails, $paymentDetailData);
									if($payment_Table->save($paymentDetails)){
										$counter++;
									}
								}
							}
					}
				}
				if(empty($kiosk_id)){
					$kiosk_id = $this->request->Session()->read('kiosk_id');
				}//echo $kiosk_id;die;
							if(!empty($kiosk_id)){
								if($kiosk_id == 10000){
									$sales_source = "kiosk_product_sales";
									$products_source = "products";
									$productreceipts_source = "product_receipts";
								}else{
									$sales_source = "kiosk_{$kiosk_id}_product_sales";
									$products_source = "kiosk_{$kiosk_id}_products";
									$productreceipts_source = "kiosk_{$kiosk_id}_product_receipts";
								}
							}else{
								$sales_source = "kiosk_product_sales";
								$products_source = "products";
								$productreceipts_source = "product_receipts";
							}
							
				$count = 0;
				if($counter>0){
					$total = 0;
					$product_code = $this->Products->find('list',array(
										   'keyField' => 'id',
										   'valueField' => 'product_code',
										   ))->toArray();
					foreach($this->request['data']['data']['Payment']['quantity'] as $key => $quantity){
						$Id = $this->request['data']['data']['Payment']['id'][$key];
						
						$kioskProductSaleData = array(
									'id' => $Id,
									'quantity' => $quantity
										);
						
						if($quantity>0){
							$conn = ConnectionManager::get('default');
							$query = "UPDATE `$sales_source` SET `quantity` = `quantity` + $quantity WHERE `$sales_source`.`id` = $Id";
							//$this->loadModel($salesTable);
							$stmt = $conn->execute($query);
							
							//$this->KioskProductSale->query("UPDATE `$salesTable` SET `quantity` = `quantity` + $quantity WHERE `$salesTable`.`id` = $Id");
							$saleDetail_query = $kiosk_product_saleTable->get($Id,[
																					'fields' => ['product_id','quantity',"sale_price"]	
																					]);
							if(!empty($saleDetail_query)){
								$saleDetail = $saleDetail_query->toArray();
							}else{
								$saleDetail = array();
							}
							
							$productId = $saleDetail['product_id'];
							$selling_price_withot_vat = $saleDetail['sale_price'];
							
							if(!empty($vat_in_table)){
								$vat_val = ($selling_price_withot_vat*($vat_in_table/100))*$quantity;
							}
							
							$data = array(
									'quantity' => $quantity,
									'product_code' => $product_code[$productId],
									'selling_price_withot_vat' => $selling_price_withot_vat,
									'vat' => $vat_val,
							);
							if($kiosk_id == 0){
								$kiosk_id_to_use = 10000;
							}else{
								$kiosk_id_to_use = $kiosk_id;
							}
							$this->insert_to_ProductSellStats($productId,$data,$kiosk_id_to_use,$operations = '+');
							   
							   
							$query2 = "UPDATE `$products_source` SET `quantity` = `quantity` - $quantity WHERE `$products_source`.`id` = $productId";
							//$this->loadModel($productsTable);
							$stmt = $conn->execute($query2);
							//$this->Product->query("UPDATE `$productsTable` SET `quantity` = `quantity` - $quantity WHERE `$productsTable`.`id` = $productId");
							$count++;
						}
						// added by sourabh----------------
						//pr($saleDetail);die;	
						$detail_query = $kiosk_product_saleTable->find('all',array(
													'conditions'=>array('id'=>$Id),
													'fields'=>array('product_id','quantity')
													)
												       );
						$detail_query = $detail_query->hydrate(false);
						if(!empty($detail_query)){
							$detail = $detail_query->first();
						}else{
							$detail = array();
						}
						//pr($detail);die;
						$productId = $detail['product_id'];
						$qntity = $detail['quantity'];
						
						$costPrice_query = $productsTable->find('list',[
																	'conditions' => ['id' => $productId],
																	'keyField' => 'id',
																	'valueField' => 'cost_price'	
																]);
						$costPrice_query =  $costPrice_query->hydrate(false);
						if(!empty($costPrice_query)){
							$costPrice = $costPrice_query->toArray();
						}else{
                            $costPrice = array();
                        }
						$total += $costPrice[$productId] * $qntity;
						// added by sourabh-----------------------------
					}
				}
				//echo $total;die;
				if ((int)$count) {
					$query3 = "UPDATE `$productreceipts_source` SET `bill_cost` = $total WHERE `$productreceipts_source`.`id` = $id";
					$query4 = "UPDATE `$productreceipts_source` SET `orig_bill_amount` = `orig_bill_amount` + $newInvoiceOrderAmount WHERE `$productreceipts_source`.`id` = $id";
					//$this->loadModel($productreceiptsTable);
					$stmt = $conn->execute($query3);
					$stmt = $conn->execute($query4);
					//$this->ProductReceipt->query("UPDATE `$productreceiptsTable` SET `bill_cost` = $total WHERE `$productreceiptsTable`.`id` = $id");
					//$this->ProductReceipt->query("UPDATE `$productreceiptsTable` SET `bill_amount` = `bill_amount` + $newInvoiceOrderAmount WHERE `$productreceiptsTable`.`id` = $id");// added by sourabh
					
					
					$query4 = "UPDATE `$productreceipts_source` SET `bill_amount` = `bill_amount` + $newInvoiceOrderAmount WHERE `$productreceipts_source`.`id` = $id";
					//$this->loadModel($productreceiptsTable);
					$stmt = $conn->execute($query4);
					$this->Flash->success(__("$count record(s) have been saved."));
					if($this->request->session()->read('Auth.User.group_id') == ADMINISTRATORS || $this->request->session()->read('Auth.User.group_id') == FRANCHISE_OWNER){
						$kiosk_id_to_set = $this->get_kiosk_for_invoice();
						return $this->redirect(array('action'=>"search?kiosk_id=$kiosk_id_to_set&date_type=invoice"));
					}else{
						return $this->redirect(array('controller'=>'product_receipts','action' => 'index'));
					}
				}else{
					$this->Flash->success(__('The product receipt could not be saved. Please, try again.'));
					if($this->request->session()->read('Auth.User.group_id') == ADMINISTRATORS || $this->request->session()->read('Auth.User.group_id') == FRANCHISE_OWNER){
						$kiosk_id_to_set = $this->get_kiosk_for_invoice();
						return $this->redirect(array('action'=>"edit",$id,$kiosk_id_to_set));
					}else{
						return $this->redirect(array('action'=>'edit',$id));
					}
				}
				
		}elseif ($this->request->is(array('post', 'put'))) {
			//if(array_key_exists(''))
			if(array_key_exists('add_more_products',$this->request['data'])){
				return $this->redirect(array('controller'=>'kiosk-product-sales','action' => 'edit_receipt',$id));
			}else{
				//echo'hi111';die;
				//pr($this->request);die;
					$vat = $this->VAT;
					$vatItem = $vat/100;
					$totalQuantity = 0;
					foreach($this->request['data']['ProductReceipt']['quantity'] as $key=>$quantty){
						$totalQuantity+=(float)$quantty;
					}
					
					if(empty($this->request['data']['ProductReceipt']['quantity'])){
						$this->Flash->success(__('Please add quantity.'));
						if($this->request->session()->read('Auth.User.group_id') == ADMINISTRATORS || $this->request->session()->read('Auth.User.group_id') == FRANCHISE_OWNER){
							$kiosk_id_to_set = $this->get_kiosk_for_invoice();
							return $this->redirect(array('action'=>"edit",$id,$kiosk_id_to_set));
						}else{
							return $this->redirect(array('action'=>'edit',$id));
						}
					}elseif($totalQuantity == 0 || $totalQuantity == ''){
						$this->Flash->success(__('Please add quantity.'));
						if($this->request->session()->read('Auth.User.group_id') == ADMINISTRATORS || $this->request->session()->read('Auth.User.group_id') == FRANCHISE_OWNER){
							$kiosk_id_to_set = $this->get_kiosk_for_invoice();
							return $this->redirect(array('action'=>"edit",$id,$kiosk_id_to_set));
						}else{
							return $this->redirect(array('action'=>'edit',$id));
						}
					}
					
					$counter = 0;
					$afterDiscountPrice = 0;
					$hiddenFields = "";
					$quantityError = 0;
					foreach($this->request['data']['ProductReceipt']['id'] as $key=>$productSaleId){
						$centralQuantity = $this->request['data']['ProductReceipt']['centralQuantity'][$key];
						$orderQuantity = $this->request['data']['ProductReceipt']['quantity'][$key];
						$orderDiscount = $this->request['data']['ProductReceipt']['discount'][$key];
						$orderPrice = $this->request['data']['ProductReceipt']['price'][$key];
						$discountedPrice = (float)$orderQuantity*((float)$orderPrice-(float)$orderPrice*(float)$orderDiscount/100);
						$afterDiscountPrice+=(float)$discountedPrice;
						$hiddenFields.="<input type = 'hidden' name ='data[Payment][quantity][$key]' value='$orderQuantity'/>";
						$hiddenFields.="<input type = 'hidden' name ='data[Payment][id][$key]' value='$productSaleId'/>";
						
						if($orderQuantity>$centralQuantity){
							$quantityError++;
							break;
						}
					}
					//echo $afterDiscountPrice;echo "</br>";
					$newInvoiceOrderAmount = $afterDiscountPrice - $afterDiscountPrice*$bulkDiscount/100;
					//echo $newInvoiceOrderAmount;die;
					/*if($productReceiptData['ProductReceipt']['vat'] == 0){
							$newInvoiceOrderAmount = $newInvoiceOrderAmount/(1+$vatItem);
							$newInvoiceOrderAmount = number_format($newInvoiceOrderAmount,2);
					}*/
					if($productReceiptData['vat'] != 0){
						$newInvoiceOrderAmount = $newInvoiceOrderAmount + ($newInvoiceOrderAmount * $vat)/100;
						$newInvoiceOrderAmount = number_format($newInvoiceOrderAmount,2);
					}
					//echo $newInvoiceOrderAmount;die;
					
					if($quantityError>0){
						$this->Flash->success(__('Qantity cannot be more than Available quantity.'));
						if($this->request->session()->read('Auth.User.group_id') == ADMINISTRATORS || $this->request->session()->read('Auth.User.group_id') == FRANCHISE_OWNER){
							$kiosk_id_to_set = $this->get_kiosk_for_invoice();
							return $this->redirect(array('action'=>"edit",$id,$kiosk_id_to_set));
						}else{
							return $this->redirect(array('action'=>'edit',$id));
						}
					}
					
					$this->set(compact('newInvoiceOrderAmount','hiddenFields','bulkDiscount'));
					$this->render('update_invoice_payment');
			}
		} else {
			$res_query = $receiptTable->get($id,[
																//  'contain' => ['KioskProductSales','Customers']
																   ]);
			$sale_table_query1 = $kiosk_product_saleTable->find('all',['conditions' => ['product_receipt_id' => $id]]);
			$sale_table_query1  = $sale_table_query1->hydrate(false);
			if(!empty($sale_table_query1)){
				$sale_table1 = $sale_table_query1->toArray();
			}else{
				$sale_table1 = array();
			}
			$this->set(compact('sale_table1'));
			if(!empty($res_query)){
				$this->request->data =$res_query->toArray();
				//pr($res);die;
			}
			//$options = array('conditions' => array('ProductReceipt.' . $this->ProductReceipt->primaryKey => $id));
			//$this->request->data = $this->ProductReceipt->find('first', $options);
		}
		$products_query = $productsTable->find('all',array('conditions'=>array('id IN'=>$productArr),'fields'=>array('id','product_code','product'),'recursive'=>-1));
		$products_query = $products_query->hydrate(false);
		if(!empty($products_query)){
			$products = $products_query->toArray();
		}else{
            $products = array();
        }
		foreach($products as $p=>$prodc){
			$productName[$prodc['id']]=$prodc['product'];
			$productCode[$prodc['id']]=$prodc['product_code'];
		}
		$kiosks_query = $this->Kiosks->find('list');
		$kiosks_query = $kiosks_query->hydrate(false);
		if(!empty($kiosks_query)){
			$kiosks = $kiosks_query->toArray();
		}else{
            $kiosks = array();
        }
		$users_query = $this->Users->find('list');
		$users_query = $users_query->hydrate(false);
		if(!empty($users_query)){
			$users = $users_query->toArray();
		}else{
            $users = array();
        }
		$customers_query = $this->Customers->find('list');
		$customers_query = $customers_query->hydrate(false);
		if(!empty($customers_query)){
			$customers = $customers_query->toArray();
		}else{
            $customers = array();
        }
		$this->set(compact('customers','productCode','productName','kiosks','users','bulkDiscount','productReceiptData','productQuantityArr'));
	}
    
    public function kioskProductPayments()
    {
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
		$kskId = $this->request->Session()->read('kiosk_id');
		if((int)$kskId && $kskId > 0){
			$kioskId = $kskId;
		}else{
			$kioskId = current(array_keys($kiosks));
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
        
        
        $ProductReceipts_source = "kiosk_{$kioskId}_product_receipts";
        $ProductReceiptsTable = TableRegistry::get($ProductReceipts_source,[
                                                                    'table' => $ProductReceipts_source,
                                                                ]);
		$this->paginate = [
                            //'recursive' => -1,
                            'order' => ['id desc'],
                            'limit' => ROWS_PER_PAGE
                          ];
		$productReceipts_query = $this->paginate($ProductReceiptsTable);
        if(!empty($productReceipts_query)){
            $productReceipts = $productReceipts_query->toArray();
        }else{
            $productReceipts = array();
        }
		$receiptIds = array();
		foreach($productReceipts as $key => $productReceipt){
            //pr($productReceipt);die;
			$receiptIds[] = $productReceipt->id;
		}
		if(empty($receiptIds)){
			$receiptIds = array(0 => null);
		}
		
		$kiosk_product_sales_source = "kiosk_{$kioskId}_product_sales";
        $kiosk_product_sales_Table = TableRegistry::get($kiosk_product_sales_source,[
                                                                    'table' => $kiosk_product_sales_source,
                                                                ]);
		$kiosk_product_sale_data_query = $kiosk_product_sales_Table->find('all',array('conditions' => array(
																		   'product_receipt_id IN' => $receiptIds
																		   )));
		$kiosk_product_sale_data_query = $kiosk_product_sale_data_query->hydrate(false);
		if(!empty($kiosk_product_sale_data_query)){
			$kiosk_product_sale_data = $kiosk_product_sale_data_query->toArray();
		}else{
			$kiosk_product_sale_data = array();
		}
		$ref_status = $ref_by_s = array();
		foreach($kiosk_product_sale_data as $k=> $val){
			if($val['refund_status'] == 1 || $val['refund_status'] == 2){
				$ref_status[$val['product_receipt_id']] = $val['refund_status'];	
			}
			if(array_key_exists($val['product_receipt_id'],$ref_by_s)){
				
				$ref_by_s[$val['product_receipt_id']][] = $val['refund_by'];
			//	pr($ref_by_s);
			//	echo $val['refund_by'];die;
			}else{
				$ref_by_s[$val['product_receipt_id']][] = $val['refund_by'];
			}
			
		}
		$paymentArr = array();
		$payment_amount_arr = array();
		$productPayment_query = $this->ProductPayments->find('all', array('conditions' => array('ProductPayments.product_receipt_id IN' => $receiptIds,
																						 'ProductPayments.kiosk_id' => $kioskId,
																						 ),'recursive' => -1));
		$productPayment_query = $productPayment_query->hydrate(false);
        if(!empty($productPayment_query)){
            $productPayment = $productPayment_query->toArray();
        }else{
            $productPayment = array();
        }
	//pr($productPayment);die;
		if(count($productPayment)){
			foreach($productPayment as $pp => $paymentDetail){
				$paymentArr[$paymentDetail['product_receipt_id']][] = $paymentDetail;
				if(array_key_exists($paymentDetail['product_receipt_id'],$payment_amount_arr) && array_key_exists($paymentDetail['payment_method'],$payment_amount_arr[$paymentDetail['product_receipt_id']])){
					$payment_amount_arr[$paymentDetail['product_receipt_id']][$paymentDetail['payment_method']] += number_format($paymentDetail['amount'],2);
				}else{
					$payment_amount_arr[$paymentDetail['product_receipt_id']][$paymentDetail['payment_method']] = number_format($paymentDetail['amount'],2);
				}
			}
			
		}
		
		$hint = $this->ScreenHint->hint('product_receipts','kiosk_product_payments');
					if(!$hint){
						$hint = "";
					}
		$this->set(compact('hint','productReceipts','kioskId','kiosks','users','paymentArr','payment_amount_arr','kiosk_product_sale_data','ref_status', 'ref_by_s'));
    }
    
    public function searchProductPayments()
    {
		$startDate = $endDate = $searchKW = "";
		$kiosk_id = $this->request->Session()->read('kiosk_id');
		$paymentMode = 'Multiple';
		$searchRefunded = 0;
		
		if(array_key_exists('payment_mode',$this->request->query)){
			$paymentMode = $this->request->query['payment_mode'];
			if($paymentMode == 'refunded'){
				$searchRefunded = 1;
			}
		}
		if(array_key_exists('search_kw',$this->request->query) &&
		   !empty($this->request->query['search_kw']) 
		   ){
			$searchKW = $this->request->query['search_kw'];
			
		}

		$conditionArr = array();
		$saleConditionArr = array();
		$paymentConditionArr = array();
		if(array_key_exists('start_date',$this->request->query) &&
		   !empty($this->request->query['start_date'])
		   ){
			$this->set('start_date',$this->request->query['start_date']);
			$startDate = $this->request->query['start_date'];
		}
		
		if(array_key_exists('end_date',$this->request->query) &&
		   !empty($this->request->query['end_date'])
		   ){
			$this->set('end_date',$this->request->query['end_date']);
			$endDate = $this->request->query['end_date'];
		}
		//pr($this->request->query);die;
		if(array_key_exists('ProductSale',$this->request->query)){
			if($this->request->query['ProductSale']['kiosk_id'] == -1){
				//echo'hi';die;
				return $this->redirect("/ProductReceipts/search_all_product_payments?start_date=$startDate&end_date=$endDate&payment_mode=$paymentMode");
			}
		}
		if(
		   array_key_exists('start_date',$this->request->query) &&
		   !empty($this->request->query['start_date']) &&
		   array_key_exists('end_date',$this->request->query) &&
		   !empty($this->request->query['end_date'])
		   ){
			$conditionArr[] = array(
						"created >" => date('Y-m-d', strtotime($this->request->query['start_date'])),
						"created <" => date('Y-m-d', strtotime($this->request->query['end_date']. ' +1 Days')),			
					       );
			$saleConditionArr[] = array(
						"created >" => date('Y-m-d', strtotime($this->request->query['start_date'])),
						"created <" => date('Y-m-d', strtotime($this->request->query['end_date']. ' +1 Days')),			
					       );
			$paymentConditionArr[] = array(
						"ProductPayments.created >" => date('Y-m-d', strtotime($this->request->query['start_date'])),
						"ProductPayments.created <" => date('Y-m-d', strtotime($this->request->query['end_date']. ' +1 Days')),			
					       );
			$receiptCondtionArr[] = array(
									"created >" => date('Y-m-d', strtotime($this->request->query['start_date'])),
									"created <" => date('Y-m-d', strtotime($this->request->query['end_date']. ' +1 Days')),	
										  );
		}
		if($searchKW){
			$conditionArr[] = array(
						'id' => $searchKW
			);
			$saleConditionArr[] = array(
						'product_receipt_id' => $searchKW
			);
			$paymentConditionArr[] = array(
						'ProductPayments.product_receipt_id IN' => $searchKW
			);
		}
		
		if($kiosk_id == ""){
			
			if(array_key_exists('kiosk_id',$this->request->query['ProductSale']) && !empty($this->request->query['ProductSale']['kiosk_id'])){
				
				$kioskId = $this->request->query['ProductSale']['kiosk_id'];
			}
		}
		if($kiosk_id>0){
			//echo "kiosk";
			//$conditionArr[] = array('ProductReceipt.kiosk_id' => "$kiosk_id",);
			$kioskId = $kiosk_id;
		}
		
		$this->set('kioskId', $kioskId);
		//pr($conditionArr);
		$this->set('search_kw', $searchKW);
		//$this->set('search_kw1', $searchKW1);
		$ProductReceipts_source = "kiosk_{$kioskId}_product_receipts";
        $ProductReceiptsTable = TableRegistry::get($ProductReceipts_source,[
                                                                    'table' => $ProductReceipts_source,
                                                                ]);
        $KioskProductSales_source = "kiosk_{$kioskId}_product_sales";
        $KioskProductSalesTable = TableRegistry::get($KioskProductSales_source,[
                                                                    'table' => $KioskProductSales_source,
                                                                ]);
		
		$refundReceiptIds = array();
		$refundedEntries = array();
		$totalRefundAmount = 0;
		if($searchRefunded == 1){
            //pr($saleConditionArr);die;
			$kioskProductSaleData_query = $KioskProductSalesTable->find('all', array('conditions' => array($saleConditionArr,'refund_status IN' => array(1, 2)), 'fields' => array('refund_price', 'product_receipt_id','quantity'), 'recursive' => -1));
            $kioskProductSaleData_query = $kioskProductSaleData_query->hydrate(false);
            if(!empty($kioskProductSaleData_query)){
                $kioskProductSaleData = $kioskProductSaleData_query->toArray();
            }else{
                $kioskProductSaleData = array();
            }
			//pr($kioskProductSaleData);die;
			if(count($kioskProductSaleData) > 0){
				foreach($kioskProductSaleData as $ki => $refundSaleData){
					$totalRefundAmount+=$refundSaleData['refund_price']*$refundSaleData['quantity'];
					$amount = $refundSaleData['refund_price'];
					if(array_key_exists($refundSaleData['product_receipt_id'],$refundedEntries)){
						$amount = $amount + $refundedEntries[$refundSaleData['product_receipt_id']];
					}
					$refundedEntries[$refundSaleData['product_receipt_id']] = $amount;
					$refundReceiptIds[$refundSaleData['product_receipt_id']] = $refundSaleData['product_receipt_id'];
				}
				//pr($refundedEntries);die;
			}
		}
		
		//pr($sales_data);
		$kiosks_query = $this->Kiosks->find('list',[
                                                'keyField' => 'id',
                                                'valueField' => 'name',
                                                'conditions' => ['Kiosks.status' => 1],
                                                'order'=>['Kiosks.name asc']
                                             ]);
		$kiosks_query = $kiosks_query->hydrate(false);
        if(!empty($kiosks_query)){
            $kiosks = $kiosks_query->toArray();
        }else{
            $kiosks = array();
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
		$receiptIdArr = array();
		$payment_array = array();
		$payment_amount_arr = array();
		
		if($paymentMode == 'Cash' || $paymentMode == 'Card'){
			//-----rasu--------------
			if(!isset($receiptCondtionArr)){
				$receiptCondtionArr = array();
			}
            $ProductReceipts_source = "kiosk_{$kioskId}_product_receipts";
            $ProductReceiptsTable = TableRegistry::get($ProductReceipts_source,[
                                                                    'table' => $ProductReceipts_source,
                                                                ]);
			//$this->ProductReceipt->setSource("kiosk_{$kioskId}_product_receipts");
			$receiptRecs_query = $ProductReceiptsTable->find('all', array(
																	  'fields' => array('id','created'),
																	  'conditions' => array($receiptCondtionArr),
																	  'recursive' => -1,
																	  )
												   );
            $receiptRecs_query = $receiptRecs_query->hydrate(false);
            if(!empty($receiptRecs_query)){
                $receiptRecs = $receiptRecs_query->toArray();
            }else{
                $receiptRecs = array();
            }
				if(!empty($receiptRecs)){		
						$receiptIDs = array();
						foreach($receiptRecs as $receiptRec){
							$receiptIDs[] = $receiptRec['id'];
						}
						if(count($receiptIDs) && empty($searchKW)){
							$paymentConditionArr[] = array(
										'ProductPayments.product_receipt_id IN' => $receiptIDs
							);
						}
						if(!empty($paymentConditionArr)){
							if(array_key_exists("ProductPayments.created >",$paymentConditionArr[0])){
								unset($paymentConditionArr[0]);
								//pr($paymentConditionArr);
							}
						}
						//pr($paymentConditionArr);
						//----rasu---------------
                        //pr($paymentConditionArr);die;
                        //pr($paymentMode);die;
                        //pr($kioskId);die;
						$searchPaymentResult_query = $this->ProductPayments->find('all', array('conditions' => array($paymentConditionArr, 'ProductPayments.kiosk_id' => $kioskId,'ProductPayments.payment_method' => $paymentMode,'ProductPayments.status' => 1), 'recursive' => -1));
                        $searchPaymentResult_query = $searchPaymentResult_query->hydrate(false);
                        if(!empty($searchPaymentResult_query)){
                            $searchPaymentResult = $searchPaymentResult_query->toArray();
                        }else{
                            $searchPaymentResult = array();
                        }
						if(count($searchPaymentResult)){
							foreach($searchPaymentResult as $spr => $searchPaymentInfo){
								$payment_array[$searchPaymentInfo['product_receipt_id']] = $searchPaymentInfo['product_receipt_id'];
								#$payment_amount_arr[$searchPaymentInfo['RepairPayment']['mobile_repair_id']][$searchPaymentInfo['RepairPayment']['payment_method']] = $searchPaymentInfo['RepairPayment']['amount'];
							}
							
							//$resultReceiptIds = $payment_array;
						}
						//pr($payment_array);die;
				}
				if(empty($payment_array)){
					$payment_array = array(0 => null);
				}
			$this->paginate = [
                                'conditions' => ['id IN' => $payment_array],
                                'order' => ['id DESC'],//MobileRepairSale.id DESC
                                'limit' => ROWS_PER_PAGE,
                                //'recursive' => -1
                            ];
			
		}elseif($searchRefunded == 1){
            if(empty($refundReceiptIds)){
                $refundReceiptIds = array(0 => null);
            }
			$this->paginate = [
                                'conditions' => ['id IN' => $refundReceiptIds],
                                'order' => ['id DESC'],//MobileRepairSale.id DESC
                                'limit' => ROWS_PER_PAGE,
                                //'recursive' => -1
                              ];
		}else{
			//pr($conditionArr);die;
			$this->paginate = [
                                'conditions' => [$conditionArr],
                                'order' => ['id DESC'],//, MobileRepairSale.id DESC
                                'limit' => ROWS_PER_PAGE,
                                //'recursive' => -1
                            ];
		}
		
		if($searchRefunded == 1){
			//CASE: when refunded rows are being searched
			$refundSum = $totalRefundAmount;
		}else{
            $saleConditionArr['refund_status IN'] = array(1, 2);
            $query_kiosk_sale = $KioskProductSalesTable->find('all',['conditions' => $saleConditionArr]);
                $query_kiosk_sale
                      ->select(['totalrefund' => $query_kiosk_sale->func()->sum('refund_price * quantity')]);
            $query_kiosk_sale_result = $query_kiosk_sale->first();
            unset($saleConditionArr['refund_status IN']);
            if(!empty($query_kiosk_sale_result)){
               $refundSumData = $query_kiosk_sale_result->toArray();
            }else{
                $refundSumData = array();
            }
			
			$refundSum = $refundSumData['totalrefund'];
			if($refundSum < 0){
				$refundSum = -$refundSum;
			}elseif(empty($refundSum)){
				$refundSum = 0;
			}
		}
		//----------------------------------------------
		if(!isset($receiptCondtionArr)){
			$receiptCondtionArr = array();
		}
        $ProductReceipts_source = "kiosk_{$kioskId}_product_receipts";
        $ProductReceiptsTable = TableRegistry::get($ProductReceipts_source,[
                                                                             'table' => $ProductReceipts_source,
                                                                            ]);
		$receiptRecs_query = $ProductReceiptsTable->find('all', array(
                                                                'fields' => array('id','created'),
                                                                'conditions' => array($receiptCondtionArr),
                                                                //'recursive' => -1,
                                                                )
												   );
        $receiptRecs_query = $receiptRecs_query->hydrate(false);
        if(!empty($receiptRecs_query)){
            $receiptRecs = $receiptRecs_query->toArray();
        }else{
            $receiptRecs = array();
        }
		$receiptIDs = array();
		foreach($receiptRecs as $receiptRec){
			$receiptIDs[] = $receiptRec['id'];
		}
		if(count($receiptIDs) && empty($searchKW)){
			$paymentConditionArr[] = array(
						'ProductPayments.product_receipt_id IN' => $receiptIDs
			);
		}
		
		$kiosk_product_sales_source = "kiosk_{$kioskId}_product_sales";
        $kiosk_product_sales_Table = TableRegistry::get($kiosk_product_sales_source,[
                                                                    'table' => $kiosk_product_sales_source,
                                                                ]);
		
		if(empty($receiptIDs)){
			$receiptIDs = array(0 => null);	
		}
		$kiosk_product_sale_data_query = $kiosk_product_sales_Table->find('all',array('conditions' => array(
																		   'product_receipt_id IN' => $receiptIDs
																		   )));
		$kiosk_product_sale_data_query = $kiosk_product_sale_data_query->hydrate(false);
		if(!empty($kiosk_product_sale_data_query)){
			$kiosk_product_sale_data = $kiosk_product_sale_data_query->toArray();
		}else{
			$kiosk_product_sale_data = array();
		}
		$ref_status = $ref_by_s = array();
		foreach($kiosk_product_sale_data as $k=> $val){
			if($val['refund_status'] == 1 || $val['refund_status'] == 2){
				$ref_status[$val['product_receipt_id']] = $val['refund_status'];	
			}
			if(array_key_exists($val['product_receipt_id'],$ref_by_s)){
				
				$ref_by_s[$val['product_receipt_id']][] = $val['refund_by'];
			//	pr($ref_by_s);
			//	echo $val['refund_by'];die;
			}else{
				$ref_by_s[$val['product_receipt_id']][] = $val['refund_by'];
			}
			
		}
		
		
		if(!empty($paymentConditionArr)){
			if(array_key_exists(0,$paymentConditionArr)){
				if(array_key_exists("ProductPayments.created >",$paymentConditionArr[0])){
					unset($paymentConditionArr[0]);
					//pr($paymentConditionArr);
				}
			}
		}

		//----------------------------------------------
		$saleSum = 0;
		if($paymentMode == 'Card'){
			if(!empty($receiptRecs)){
                
                $query_product_payment = $this->ProductPayments->find('all',['conditions' => $paymentConditionArr,'ProductPayments.kiosk_id' => $kioskId,'payment_method' => 'Card']);
                    $query_product_payment
                          ->select(['totalsale' => $query_product_payment->func()->sum('amount')]);
                $query_product_payment_result = $query_product_payment->first();
                if(!empty($query_product_payment_result)){
                   $saleSumData = $query_product_payment_result->toArray();
                }else{
                    $saleSumData = array();
                }
				$saleSum = $saleSumData['totalsale'];
			$refundSum = 0;
			//pr($saleSumData);die;
			}
		}elseif($paymentMode == 'Cash'){
			if(!empty($receiptRecs)){
                //pr($paymentConditionArr);die;
                $query_product_payment = $this->ProductPayments->find('all',['conditions' => $paymentConditionArr,'ProductPayments.kiosk_id' => $kioskId,'payment_method' => 'Cash','ProductPayment.status' => 1]);
                    $query_product_payment
                          ->select(['totalsale' => $query_product_payment->func()->sum('amount')]);
                $query_product_payment_result = $query_product_payment->first();
                if(!empty($query_product_payment_result)){
                   $saleSumData = $query_product_payment_result->toArray();
                }else{
                    $saleSumData = array();
                }
				$saleSum = $saleSumData['totalsale'];
			}
		}else{//if($paymentMode == 'Multiple')
			if(!empty($receiptRecs)){
                
                $query_product_payment = $this->ProductPayments->find('all',['conditions' => [$paymentConditionArr,'ProductPayments.kiosk_id' => $kioskId,'ProductPayments.status' => 1]]);
                    $query_product_payment
                          ->select(['totalsale' => $query_product_payment->func()->sum('amount')]);
                         // pr($query_product_payment);die;
                $query_product_payment_result = $query_product_payment->first();
                if(!empty($query_product_payment_result)){
                   $saleSumData = $query_product_payment_result->toArray();
                }else{
                    $saleSumData = array();
                }
                
			$saleSum = $saleSumData['totalsale'];
			}
		}
        //echo $saleSum;die;
		
		
		$productReceipts_query = $this->paginate($ProductReceiptsTable);
        if(!empty($productReceipts_query)){
            $productReceipts = $productReceipts_query->toArray();
        }else{
            $productReceipts = array();
        }
		$paymentArr = array();
		$payment_amount_arr = array();
		
		if($kioskId){
			$paymentConditionArr[] = array(
						'ProductPayments.kiosk_id' => $kioskId
			);
		}
		
		if($paymentMode == "Multiple"){
			
			if(!empty($paymentConditionArr)){
				if(array_key_exists(0,$paymentConditionArr)){
					if(array_key_exists("ProductPayments.created >",$paymentConditionArr[0])){
						unset($paymentConditionArr[0]);
						//pr($paymentConditionArr);
					}
				}
			}
		}elseif($paymentMode == "refunded"){
			$paymentConditionArr[] = array(
						'ProductPayments.status' => 2
			);	
		}else{
			$paymentConditionArr[] = array(
						'ProductPayments.status' => 1
			);	
		}
		//pr($paymentConditionArr);die;
		$productPayment_query = $this->ProductPayments->find('all', array('conditions' => array($paymentConditionArr)));
        $productPayment_query = $productPayment_query->hydrate(false);
        if(!empty($productPayment_query)){
            $productPayment = $productPayment_query->toArray();
        }else{
            $productPayment = array();
        }

		if(count($productPayment)){
			foreach($productPayment as $pp => $paymentDetail){
				$paymentArr[$paymentDetail['product_receipt_id']][] = $paymentDetail;
				if(array_key_exists($paymentDetail['product_receipt_id'],$payment_amount_arr) && array_key_exists($paymentDetail['payment_method'],$payment_amount_arr[$paymentDetail['product_receipt_id']])){
					$payment_amount_arr[$paymentDetail['product_receipt_id']][$paymentDetail['payment_method']]+= $paymentDetail['amount'];
				}else{
					$payment_amount_arr[$paymentDetail['product_receipt_id']][$paymentDetail['payment_method']] = $paymentDetail['amount'];
				}
			}
		}
		//pr($paymentArr);die;
		$hint = $this->ScreenHint->hint('product_receipts','kiosk_product_payments');
					if(!$hint){
						$hint = "";
					}
		$this->set(compact('hint','productReceipts','kiosks','users','paymentArr','paymentMode','payment_amount_arr','saleSum','refundSum','refundedEntries','ref_status','ref_by_s'));
		//$this->layout = 'default';
		//$this->viewPath = 'mobileRepairs';
		$this->render('kiosk_product_payments');
    }
    
    public function searchAllProductPayments()
    {
		$kiosks_query = $this->Kiosks->find('list',[
                                                'keyField' => 'id',
                                                'valueField' => 'name',
                                                'conditions' => ['Kiosks.status' => 1],
                                                'order'=>['Kiosks.name asc']
                                            ]);
        $kiosks_query = $kiosks_query->hydrate(false);
        if(!empty($kiosks_query)){
            $kiosks = $kiosks_query->toArray();
        }else{
            $kiosks = array();
        }
		$conditionArr = array();
		$paymentConditionArr = array();
		$start_date = $this->request->query('start_date');
		$end_date = $this->request->query('end_date');
		$paymentMode = $this->request->query('payment_mode');
		
		if(!empty($start_date) && !empty($end_date)){
			$conditionArr[] = array(
						"created >" => date('Y-m-d', strtotime($this->request->query['start_date'])),
						"created <" => date('Y-m-d', strtotime($this->request->query['end_date']. ' +1 Days')),			
					       );
			$paymentConditionArr[] = array(
						"ProductPayments.created >" => date('Y-m-d', strtotime($this->request->query['start_date'])),
						"ProductPayments.created <" => date('Y-m-d', strtotime($this->request->query['end_date']. ' +1 Days')),			
					       );
		}
		
		$paymentDetail = array();
		$receiptIds = array();
		$refundData = array();
		$saleSumData = array();
		$cardPayment = array();
		$cashPayment = array();
		
		foreach($kiosks as $kioskId => $kioskName){
			if($kioskId == 10000)continue;
            $KioskProductSales_source = "kiosk_{$kioskId}_product_sales";
            $KioskProductSalesTable = TableRegistry::get($KioskProductSales_source,[
                                                                    'table' => $KioskProductSales_source,
                                                                ]);
            $conditionArr['refund_status IN'] = array(1,2);
            $query_kiosk = $KioskProductSalesTable->find('all',['conditions' => $conditionArr]);
                    $query_kiosk
                          ->select(['todayProductRefund' => $query_kiosk->func()->sum('refund_price*quantity')]);
                $query_kiosk_result = $query_kiosk->hydrate(false);
                unset($conditionArr['refund_status IN']);
                if(!empty($query_kiosk_result)){
                   $refundData[$kioskId] = $query_kiosk_result->first();
                }else{
                    $refundData[$kioskId] = array();
                }
            //pr($refundData[$kioskId]);
            $paymentConditionArr['payment_method'] = 'Card';
            $paymentConditionArr['ProductPayments.kiosk_id'] = $kioskId;
            $query_product_payment = $this->ProductPayments->find('all',['conditions' => $paymentConditionArr]);
                    $query_product_payment
                          ->select(['totalsale' => $query_product_payment->func()->sum('amount')]);
                $query_product_payment_result = $query_product_payment->hydrate(false);
                unset($paymentConditionArr['payment_method']);
                unset($paymentConditionArr['ProductPayments.kiosk_id']);
                if(!empty($query_product_payment_result)){
                   $cardPayment[$kioskId] = $query_product_payment_result->first();
                }else{
                    $cardPayment[$kioskId] = array();
                }
            //pr($cardPayment[$kioskId]);
            $paymentConditionArr['payment_method'] = 'Cash';
            $paymentConditionArr['ProductPayments.kiosk_id'] = $kioskId;
            $query_product_payment_cash = $this->ProductPayments->find('all',['conditions' => $paymentConditionArr]);
                    $query_product_payment_cash
                          ->select(['totalsale' => $query_product_payment_cash->func()->sum('amount')]);
                $query_product_payment_cash_result = $query_product_payment_cash->hydrate(false);
                unset($paymentConditionArr['payment_method']);
                unset($paymentConditionArr['ProductPayments.kiosk_id']);
                if(!empty($query_product_payment_cash_result)){
                   $cashPayment[$kioskId] = $query_product_payment_cash_result->first();
                }else{
                    $cashPayment[$kioskId] = array();
                }
            //pr($cashPayment[$kioskId]);die;
			if($paymentMode == 'Card'){
                $paymentConditionArr['payment_method'] = 'Card';
                $paymentConditionArr['ProductPayments.kiosk_id'] = $kioskId;
                $query_product_payment_card = $this->ProductPayments->find('all',['conditions' => $paymentConditionArr]);
                    $query_product_payment_card
                          ->select(['totalsale' => $query_product_payment_card->func()->sum('amount')]);
                $query_product_payment_card_result = $query_product_payment_card->hydrate(false);
                unset($paymentConditionArr['payment_method']);
                unset($paymentConditionArr['ProductPayments.kiosk_id']);
                if(!empty($query_product_payment_card_result)){
                   $saleSumData[$kioskId] = $query_product_payment_card_result->first();
                }else{
                    $saleSumData[$kioskId] = array();
                }  
				$refundSum = 0;
				//pr($saleSumData);
			}elseif($paymentMode == 'Cash'){
                $paymentConditionArr['payment_method'] = 'Cash';
                $paymentConditionArr['ProductPayments.kiosk_id'] = $kioskId;
                $query_product_payment_cash_sale = $this->ProductPayments->find('all',['conditions' => $paymentConditionArr,]);
                    $query_product_payment_cash_sale
                          ->select(['totalsale' => $query_product_payment_cash_sale->func()->sum('amount')]);
                $query_product_payment_cash_sale_result = $query_product_payment_cash_sale->hydrate(false);
                unset($paymentConditionArr['payment_method']);
                unset($paymentConditionArr['ProductPayments.kiosk_id']);
                if(!empty($query_product_payment_cash_sale_result)){
                   $saleSumData[$kioskId] = $query_product_payment_cash_sale_result->first();
                }else{
                    $saleSumData[$kioskId] = array();
                }  
                
				//pr($saleSumData);
			}elseif($paymentMode == 'refunded'){
				$refundInfo_query = $KioskProductSalesTable->find('list',[
                                                                            'keyField' => 'id',
                                                                            'valueField' => 'product_receipt_id',
                                                                            'conditions' => [$conditionArr,'refund_status IN' => [1,2]], 'recursive' => -1]);
                $refundInfo_query = $refundInfo_query->hydrate(false);
                if(!empty($refundInfo_query)){
                    $refundInfo[$kioskId] = $refundInfo_query->toArray();
                }else{
                    $refundInfo[$kioskId] = array();
                }
				if(count($refundInfo)){
					$refundReceipts = array_values($refundInfo[$kioskId]);                    
                    $query_refunded = $this->ProductPayments->find('all',['conditions' => ['ProductPayments.kiosk_id' => $kioskId],['ProductPayments.product_receipt_id' => $refundReceipts]]);
                    $query_refunded
                          ->select(['totalsale' => $query_refunded->func()->sum('amount')]);
                    $query_refunded_result = $query_refunded->hydrate(false);
                    if(!empty($query_refunded_result)){
                       $saleSumData[$kioskId] = $query_refunded_result->first();
                    }else{
                        $saleSumData[$kioskId] = array();
                    }        
                    
				}
			}else{//if($paymentMode == 'Multiple')
                $paymentConditionArr['ProductPayments.kiosk_id'] = $kioskId;
                $query_else = $this->ProductPayments->find('all',['conditions' => $paymentConditionArr]);
                $query_else
                      ->select(['totalsale' => $query_else->func()->sum('amount')]);
                $query_else_result = $query_else->hydrate(false);
                if(!empty($query_else_result)){
                   $saleSumData[$kioskId] = $query_else_result->first();
                }else{
                    $saleSumData[$kioskId] = array();
                }     
                
			}
		}
		$this->set(compact('kiosks', 'paymentMode', 'end_date', 'start_date', 'saleSumData', 'refundData', 'cardPayment', 'cashPayment'));
    }
    
    public function viewKioskSale($kioskId = '', $receiptId = ''){
		//echo $kioskId ;die;
        $ProductReceipts_source = "kiosk_{$kioskId}_product_receipts";
        $ProductReceiptsTable = TableRegistry::get($ProductReceipts_source,[
                                                                    'table' => $ProductReceipts_source,
                                                                ]);
        
        $KioskProductSales_source = "kiosk_{$kioskId}_product_sales";
        $KioskProductSalesTable = TableRegistry::get($KioskProductSales_source,[
                                                                    'table' => $KioskProductSales_source,
                                                                ]);
        
        $Products_source = "kiosk_{$kioskId}_products";
        $ProductsTable = TableRegistry::get($Products_source,[
                                                                'table' => $Products_source,
                                                               ]);
		//pr($receiptId);die;
        $options = [
				 'conditions' => ['id' => $receiptId],
				// 'contain' => array($KioskProductSales_source)
				 ];
		$productReceipt_query = $ProductReceiptsTable->find('all', $options);
        $productReceipt_result = $productReceipt_query->hydrate(false);
        if(!empty($productReceipt_result)){
            $productReceipt = $productReceipt_result->first();
        }else{
            $productReceipt = array();
        }
		//pr($productReceipt);die;
		//------------start fetch product info---------------------
		$productArr = $productIDs = array();
		$quantityArr = array();
		//pr($productReceipt);die;
        if(!empty($productReceipt)){
            $salesTableId = $productReceipt['id'];
        }
        //pr($salesTableId);die;
        $kiosk_products_data_query = $KioskProductSalesTable->find('all',[
                                            'conditions' => ['product_receipt_id' => $salesTableId]  
                                            ]);
        $kiosk_products_data_query = $kiosk_products_data_query->hydrate(false);
        if(!empty($kiosk_products_data_query)){
            $kiosk_products_data = $kiosk_products_data_query->toArray();
        }else{
            $kiosk_products_data = array();
        }
        
        if(!empty($productReceipt)){
            $customerTableId = $productReceipt['customer_id'];
            if(!empty($customerTableId)){
                $customer_data_query  = $this->Customers->find('all',[
                                                                     'conditions' => ['id' => $customerTableId]
                                                                     ]);
                //pr($customer_data_query);die;
                $customer_data_query = $customer_data_query->hydrate(false);
                if(!empty($customer_data_query)){
                    $customer_data = $customer_data_query->toArray();
                }else{
                    $customer_data = array();
                }
            }else{
                $customer_data = array();
            }
        }else{
            $customer_data = array();
        }
        //pr($kiosk_products_data);die;
		foreach($kiosk_products_data as $productSale){
			$productIid = $productSale['product_id'];
			$quantity = $productSale['quantity'];
			$productIDs[] = $productSale['product_id'];
			if(array_key_exists($productIid,$quantityArr)){
				$quantityArr[$productIid]+= $quantity;
			}else{
				$quantityArr[$productIid] =  $quantity;
			}
		}
	//	pr($productReceipt);die;
        if(empty($productIDs)){
            $productIDs = array(0 => null);
        }
		$products_query = $ProductsTable->find('all', array(
						  'conditions' => array('id IN' => $productIDs),
						  'fields' => array('id','product','image','image_dir')
						  )
				     );
		$products_query = $products_query->hydrate(false);
        if(!empty($products_query)){
            $products = $products_query->toArray();
        }else{
            $products = array();
        }
		foreach($products as $product){
			$productArr[(int)$product['id']] = $product['product'];
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
		$this->set('productArr', $productArr);
		$this->set('users', $users);
		$this->set(compact('quantityArr','kioskId','kiosk_products_data','customer_data'));
		//------------end fetch product info---------------------
		$this->set('productReceipt', $productReceipt);
	}
	
	public function cancel1(){
		//pr($_SESSION);die;
		$this->request->Session()->delete('new_sale_basket');
		$this->request->Session()->delete('finalAmount');
		echo json_encode(array('status' => 'ok'));
		die;
		//return $this->redirect(array('controller' => 'customers','action' => 'index'));
	}

	public function makePayment($id = null) {
		//pr($this->request);die;
		if($this->request->session()->read('Auth.User.group_id') == ADMINISTRATORS || $this->request->session()->read('Auth.User.group_id') == FRANCHISE_OWNER){
			$kiosk_id_to_set = $this->get_kiosk_for_invoice();
			if(!empty($kiosk_id_to_set)){
				if($kiosk_id_to_set == 10000){
					$Product_source = "products";
					$KioskProductSale_source = "kiosk_product_sales";
					$ProductReceipt_source = "product_receipts";
					$PaymentDetail_source = "payment_details";
				}else{
					$kiosk_id = $kiosk_id_to_set;
					$Product_source = "kiosk_{$kiosk_id}_products";
					$KioskProductSale_source = "kiosk_{$kiosk_id}_product_sales";
					$ProductReceipt_source = "kiosk_{$kiosk_id}_product_receipts";
					$PaymentDetail_source = "kiosk_{$kiosk_id}_payment_details";
				}
				
				$ProductTable = TableRegistry::get($Product_source,[
                                                                'table' => $Product_source,
                                                            ]);
			
				$kioskProdctSalesTable = TableRegistry::get($KioskProductSale_source,[
																				'table' => $KioskProductSale_source,
																			]);
					
				$PaymentDetailTable = TableRegistry::get($PaymentDetail_source,[
																				'table' => $PaymentDetail_source,
																			]);
				$ProductReceiptTable = TableRegistry::get($ProductReceipt_source,[
																		'table' => $ProductReceipt_source,
																	]);
				
			}
		}else{
            $kiosk_id = $this->request->Session()->read('kiosk_id');;
					$Product_source = "kiosk_{$kiosk_id}_products";
					$KioskProductSale_source = "kiosk_{$kiosk_id}_product_sales";
					$ProductReceipt_source = "kiosk_{$kiosk_id}_product_receipts";
					$PaymentDetail_source = "kiosk_{$kiosk_id}_payment_details";
                    
                    $ProductTable = TableRegistry::get($Product_source,[
                                                                'table' => $Product_source,
                                                            ]);
			
				$kioskProdctSalesTable = TableRegistry::get($KioskProductSale_source,[
																				'table' => $KioskProductSale_source,
																			]);
					
				$PaymentDetailTable = TableRegistry::get($PaymentDetail_source,[
																				'table' => $PaymentDetail_source,
																			]);
				$ProductReceiptTable = TableRegistry::get($ProductReceipt_source,[
																		'table' => $ProductReceipt_source,
																	]);
        }
		
        
		if (!$ProductReceiptTable->exists($id)) {
			throw new NotFoundException(__('Invalid product receipt'));
		}
		$session_basket = $this->request->Session()->read('Basket');
		//pr($this->request);
		//pr($session_basket);die;
		$productIdArr = array();
		foreach($session_basket as $productId=>$productDetail){
			$productIdArr[$productId] = $productId;
		}
		if(!empty($productIdArr)){
			$productQuantityCheck_query = $ProductTable->find('list',array('conditions'=>array('id In'=>$productIdArr),
																	   'keyField' => 'id',
																	   'valueField' => 'quantity',
																	   ));
			$productQuantityCheck_query = $productQuantityCheck_query->hydrate(false);
			if(!empty($productQuantityCheck_query)){
				$productQuantityCheck = $productQuantityCheck_query->toArray();
			}else{
				$productQuantityCheck = array();
			}
			$quantityError = array();
			foreach($productQuantityCheck as $productId=>$quantity){
				if($quantity==0){
					$quantityError[] = "Product with id:$productId does not have enough quantity, please choose a different product";
					unset($_SESSION['Basket'][$productId]);
					$this->request->Session()->delete("Basket.$productId");
				}
			}
			
			$quantityErrStr ='';
			if(count($quantityError)>0){
				$quantityErrStr = implode("<br/>",$quantityError);
				$this->request->Session()->write('quantityError',$quantityErrStr);
				return $this->redirect(array('controller'=>'kiosk_product_sales','action'=>'edit_receipt',$id));
			}
		}else{
			$quantityErrStr = "Please add products to the basket";
			$this->request->Session()->write('quantityError',$quantityErrStr);
			return $this->redirect(array('controller'=>'kiosk_product_sales','action'=>'edit_receipt',$id));
		}
		$productName_query = $ProductTable->find('list',array(
																'keyField' => 'id',
																'valueField' => 'product',
																));
		$productName_query = $productName_query->hydrate(false);
		if(!empty($productName_query)){
			$productName = $productName_query->toArray();
		}else{
			$productName = array();
		}
		$options = array('conditions' => array('id'  => $id));
		$ProductReceipts_query = $ProductReceiptTable->find('all', $options);
		$ProductReceipts_query = $ProductReceipts_query->hydrate(false);
		if(!empty($ProductReceipts_query)){
			$ProductReceipts = $ProductReceipts_query->first();
		}else{
			$ProductReceipts = array();
		}
		$this->set('ProductReceipt', $ProductReceipts);
		$this->set(compact('productName'));
		
		if ($this->request->is(array('post', 'put'))) {
			//pr($this->request);die;
			if(array_key_exists("cancel",$this->request->data)){
				if($this->request->data['cancel'] == "Cancel"){
					$uId = $this->request->params['pass'][0];
					return $this->redirect(array('controller'=>'product_receipts','action'=>'edit',$uId));
					die;
				}
			}
			$productReceiptDetails_query = $ProductReceiptTable->Find('all',array(
									'conditions' => array('id'=>$id)
									)
								 );
			$productReceiptDetails_query = $productReceiptDetails_query->hydrate(false);
			if(!empty($productReceiptDetails_query)){
				$productReceiptDetails = $productReceiptDetails_query->first();
			}else{
				$productReceiptDetails  = array();
			}
			$agent_id = $productReceiptDetails['agent_id'];
			$amountToPay = $this->request['data']['final_amount'];
			$totalPaymentAmount = 0;
			$amountDesc = array();
			$countCycles = 0;
			$error = array();
			$errorStr = '';
			$countCycles = 0;
			foreach($this->request['data']['Payment']['Amount'] as $key => $paymentAmount){
				$totalPaymentAmount+= (float)$paymentAmount;
				$paymentDescription = $this->request['data']['Payment']['Description'][$key];
				if(!empty($paymentDescription) && !empty($paymentAmount)){
					$countCycles++;
				}
				if(empty($paymentDescription) && !empty($paymentAmount)){
					$error[] = "Sale could not be created. Payment description must be entered";
					break;
				}
			}
			//pr($countCycles);die;
			foreach($this->request['data']['Payment']['Payment_Method'] as $key => $paymentMethod){
				/*if($paymentMethod=="On Credit" and $countCycles>1){
					$error[] = "'On Credit' payment method cannot be clubbed with any other. Either choose 'On Credit' or the other payment methods";
				}else*/if((float)$totalPaymentAmount<(float)$amountToPay &&
					($paymentMethod=="Cheque" ||
					$paymentMethod=="Cash" ||
					$paymentMethod=="Bank Transfer" ||
					$paymentMethod=="Card")){
					$error[] = "Amount must be equivalent to &#163; {$amountToPay}. Please try again";
					break;
				}elseif((float)$totalPaymentAmount>(float)$amountToPay &&
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
					$this->Flash->error("$errorStr",['validate' => false]);
					return $this->redirect(array('action'=>'make_payment',$id));
				}
				
			$counter = 0;
				if($this->request['data']['Payment']['Payment_Method'][0] == "On Credit" && $countCycles==1){
					$paymentDetailData = array(
						'product_receipt_id' => $id,
						'payment_method' => $this->request['data']['Payment']['Payment_Method'][0],
						//'description' => $this->request['data']['Payment']['Description'][0],
						'amount' => $amountToPay,
						'payment_status' => 0,
						'status' => 1,
						'agent_id' => $agent_id
						   );
                    
                    $PaymentDetailTable->behaviors()->load('Timestamp');
					$PaymentDetailsEntity = $PaymentDetailTable->newEntity($paymentDetailData,['validate' => false]);
					$PaymentDetailsEntity = $PaymentDetailTable->patchEntity($PaymentDetailsEntity,$paymentDetailData,['validate' => false]);
					if($PaymentDetailTable->save($PaymentDetailsEntity)){
						$counter++;
					}
				}else{
					foreach($this->request['data']['Payment']['Amount'] as $key => $paymentAmount){
					$paymentMethod = $this->request['data']['Payment']['Payment_Method'][$key];
					$paymentDescription = $this->request['data']['Payment']['Description'][$key];
					
						if($paymentMethod == "On Credit"){
							$payment_status = 0;
						}else{
							$payment_status = 1;
						}
						
						if(!empty($paymentAmount) && $paymentDescription){
							$paymentDetailData = array(
									'product_receipt_id' => $id,
									'payment_method' => $paymentMethod,
									//'description' => $paymentDescription,
									'amount' => $paymentAmount,
									'payment_status' => $payment_status,
									'status' => 1,
									'agent_id' => $agent_id
									   );
							$PaymentDetailTable->behaviors()->load('Timestamp');
							$PaymentDetailsEntity = $PaymentDetailTable->newEntity($paymentDetailData,['validate' => false]);
							$PaymentDetailsEntity = $PaymentDetailTable->patchEntity($PaymentDetailsEntity,$paymentDetailData,['validate' => false]);
							if($PaymentDetailTable->save($PaymentDetailsEntity)){
								$counter++;
							}
						}
					}
				}
			
			if($counter>0){
				return $this->redirect(array('controller'=>'kiosk_product_sales','action'=>'save_invoice_edit_detail',$id));;
			}else{
				$flashMessage = ("Sale could not be created. Please try again");
				$this->Flash->error($flashMessage,['validate' => false]);
				return $this->redirect(array('action'=>'make_payment', $id));
			}
		}
	}
    
    public function generateReceiptKioskSale($id = null,$kioskID =""){
		$refundOptions = Configure::read('refund_status');
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
		if(!empty($kioskID)){
			//Code for admin side
			if($kioskID == -1){
                $ProductReceipt_source = "product_receipts";
				//$this->ProductReceipt->setSource("product_receipts");
                $KioskProductSale_source = "kiosk_product_sales";
				//$this->KioskProductSale->setSource("kiosk_product_sales");
                $PaymentDetail_source = "payment_details";
				//$this->PaymentDetail->setSource("payment_details");
			}else{
                $ProductReceipt_source = "kiosk_{$kioskID}_product_receipts";
				//$this->ProductReceipt->setSource("kiosk_{$kioskID}_product_receipts");
				$KioskProductSale_source = "kiosk_{$kioskID}_product_sales";
                //$this->KioskProductSale->setSource("kiosk_{$kioskID}_product_sales");
                $PaymentDetail_source = "kiosk_{$kioskID}_payment_details";
				//$this->PaymentDetail->setSource("kiosk_{$kioskID}_payment_details");
			}
			
            $ProductReceiptTable = TableRegistry::get($ProductReceipt_source,[
                                                                        'table' => $ProductReceipt_source,
                                                                    ]);
            $KioskProductSaleTable = TableRegistry::get($KioskProductSale_source,[
                                                                        'table' => $KioskProductSale_source,
                                                                    ]);
            $PaymentDetailTable = TableRegistry::get($PaymentDetail_source,[
                                                                        'table' => $PaymentDetail_source,
                                                                    ]);
			
			$saleData_query = $KioskProductSaleTable->find('all', array(
																   'conditions' => array('product_receipt_id' => $id)
																   ));
			$saleData_query = $saleData_query->hydrate(false);
            if(!empty($saleData_query)){
                $saleData = $saleData_query->toArray();
            }else{
                $saleData = array();
            }
            //pr($saleData);die;
			$saleDataArr = array();
			foreach($saleData as $key => $productSaleData){
				$saleDataArr[] = $productSaleData;
			}
			  
		}
		
		if (!$ProductReceiptTable->exists($id)) {
			throw new NotFoundException(__('Invalid product receipt'));
		}
		
		$countryOptions = Configure::read('options.uk_non_uk');
		
		$options = array(
							'conditions' => array('id'  => $id), //$this->ProductReceipt->primaryKey returns "id"
						);
		//----------------------------------------------
		
		$productReceipt_query = $ProductReceiptTable->find('all', $options);
        $productReceipt_query = $productReceipt_query->hydrate(false);
        if(!empty($productReceipt_query)){
            $productReceipt = $productReceipt_query->first();
        }else{
            $productReceipt = array();
        }
        //pr($productReceipt);die;
        if(!empty($productReceipt)){
            $salesTableId = $productReceipt['id'];
        }
        //pr($salesTableId);die;
        $kiosk_products_data_query = $KioskProductSaleTable->find('all',[
                                            'conditions' => ['product_receipt_id' => $salesTableId]  
                                            ]);
        $kiosk_products_data_query = $kiosk_products_data_query->hydrate(false);
        if(!empty($kiosk_products_data_query)){
            $kiosk_products_data = $kiosk_products_data_query->toArray();
        }else{
            $kiosk_products_data = array();
        }
        //pr($productReceipt);die;
       
        //pr($kiosk_products_data);die;
        //pr($productReceipt);die;
        
        
        //pr($productReceipt);die;
        
        
		//pr($productReceipt);die;
		//-----------code for getting data from kiosk_{}_product_sales
		
		//------------------------------------------------------------
		/*pr($productReceipt);
		$dbo = $this->ProductReceipt->getDatasource();
		$logData = $dbo->getLog();
		$getLog = end($logData['log']);
		echo "Log Query:".$getLog['query'];
		die;*/
		 
		 $kiosk_id = '';
		if(array_key_exists(0,$kiosk_products_data)){
			$kiosk_id = $kiosk_products_data['0']['kiosk_id'];
		}
        //pr($productReceipt);
		//echo $kiosk_id;die;
		$fullAddress = $kioskDetails = $kioskName = $kioskAddress1 = $kioskAddress2 = $kioskCity = $kioskState = $kioskZip  = $kioskZip = $kioskContact = $kioskCountry = $kioskTable = "";
		
		$kioskDetails_query = $this->Kiosks->find('all',array(
														 'conditions' => array('Kiosks.id' => $kiosk_id),
														 //'fields' => array('id','name','address_1','address_2','city','state','zip','contact','country')
														)
										   );
        $kioskDetails_query = $kioskDetails_query->hydrate(false);
        if(!empty($kioskDetails_query)){
            $kioskDetails = $kioskDetails_query->first();
        }else{
            $kioskDetails = array();
        }
		
		if(!empty($productReceipt)){
            $customerTableId = $productReceipt['customer_id'];
            if(!empty($customerTableId)){
				//pr($productReceipt);die;
				if($productReceipt['sale_type']  == 0){
					$customer_data_query  = $this->RetailCustomers->find('all',[
                                                                     'conditions' => ['id' => $customerTableId]
                                                                     ]);
				}else{
					$customer_data_query  = $this->Customers->find('all',[
                                                                     'conditions' => ['id' => $customerTableId]
                                                                     ]);	
				}
                
                //pr($customer_data_query);die;
                $customer_data_query = $customer_data_query->hydrate(false);
                if(!empty($customer_data_query)){
                    $customer_data = $customer_data_query->toArray();
                }else{
                    $customer_data = array();
                }
            }else{
                $customer_data = array();
            }
        }else{
            $customer_data = array();
        }
        //pr($customer_data);die;
        $this->set(compact('kiosk_products_data','customer_data'));
		
		//pr($kioskDetails);die;
		if($this->request->session()->read('Auth.User.group_id') == KIOSK_USERS &&
		   $this->request->session()->read('Auth.User.user_type')=='wholesale'){
			//$kiosk_id = $this->Session->read('kiosk_id');
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
		
		$settingArr = $this->setting;
		//pr($settingArr);die;
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
			$kiosk_products_data = $saleDataArr;
		}
		
		foreach($kiosk_products_data as $key => $productDetail){
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
            $product_detail_query = $product_detail_query->hydrate(false);
            if(!empty($product_detail_query)){
                $product_detail[] = $product_detail_query->first();
            }else{
                $product_detail[] = array();
            }
		}
		if(!isset($product_detail))$product_detail = array();
		
		foreach($product_detail as $productInfo){
			$productName[$productInfo['id']] = $productInfo['product'];
			$productCode[$productInfo['id']] = $productInfo['product_code'];
		}
		//pr($productReceipt);die;
		$processed_by = $productReceipt['processed_by'];
		$userName_query = $this->Users->find('all',array(
													'conditions' => array('Users.id' => $processed_by),
													'fields' => array('username')
													)
									  );
        $userName_query = $userName_query->hydrate(false);
        if(!empty($userName_query)){
            $userName = $userName_query->first();
        }else{
            $userName = array();
        }
		$user_name = $userName['username'];
        
		$paymentDetails_query = $this->ProductPayments->find('all',array(
																 'conditions' => array('product_receipt_id' => $id,'status' => 1,
																					   'Date(created) =' => date("Y-m-d",strtotime($productReceipt['created']))
																					   ),
																 'order' => ['id desc'],
																 'limit' => 2
																 )
													 );
        $paymentDetails_query = $paymentDetails_query->hydrate(false);
        if(!empty($paymentDetails_query)){
            $paymentDetails = $paymentDetails_query->toArray();
        }else{
            $paymentDetails = array();
        }
        //pr($paymentDetails);die;
		$payment_method = array();
		foreach($paymentDetails as $key=>$paymentDetail){
			//pr($paymentDetail);
			$payment_method[] = $paymentDetail['payment_method']." ".$settingArr['currency_symbol'].$paymentDetail['amount'];
		}
		$payment_method1 = array();
		foreach($paymentDetails as $key=>$paymentDetail){
			//pr($paymentDetail);
			$payment_method1[] = $paymentDetail['payment_method'];
		}
		//pr($payment_method);die;
		if(!empty($customer_data['email'])){
			$customerEmail = $customer_data['email'];
		}else{
			$customerDataReceipt_query = $ProductReceiptTable->find('all',array(
																			 'conditions' => array('id' => $id),
																			 'fields' => array('id', 'email'),
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
		//pr($users);die;
		//pr($productReceipt);die;
		$this->set(compact('productReceipt','users', 'kiosk','vat','productName','customerEmail','paymentDetails','settingArr','payment_method','user_name','productCode','kioskTable','kioskContact','countryOptions','currency','qttyArr','kioskDetails','payment_method1'));
		
		if ($this->request->is(array('post', 'put'))) {
			$send_by_email = Configure::read('send_by_email');
			$emailSender = Configure::read('EMAIL_SENDER');
			$Email = new Email();
			$Email->config('default');
			$Email->viewVars(array(
								   'qttyArr' => $qttyArr,
								   'settingArr' => $settingArr,
								   'productReceipt' => $productReceipt,
								   'kiosk' => $kiosk,
								   'vat' => $vat,
								   'productName' => $productName,
								   'currency' => $currency,
								   'refundOptions' => $refundOptions,
								   'kioskDetails' => $kioskDetails,
								   'productCode' => $productCode,
								   'payment_method1' => $payment_method1,
								   'users' => $users,
                                   'kiosk_products_data' =>$kiosk_products_data,
                                   'customer_data' => $customer_data
								   )
							 );
			//$Email->config(array('from' => 'me@example.org', 'transport' => 'MyCustom'));
			//$Email = new CakeEmail(array('from' => 'me@example.org', 'transport' => 'MyCustom'));
			$emailTo = $this->request['data']['customer_email'];
			$Email->template('kiosk_customer_receipt');
			$Email->emailFormat('both');
			$Email->to($emailTo);
			$Email->transport(TRANSPORT);
				$Email->from([$send_by_email => $emailSender]);
			//$Email->sender("sales@oceanstead.co.uk");
			$Email->subject('Order Receipt');
			if($Email->send()){
				$this->Flash->success("Email has been successfully sent");
			}
		}
		//echo "hi";die;
		//$this->layout = 'default';
		$this->render('generate_receipt_withreturn');
	}
    
    public function view($id = null,$kiosk_id = null) {
		$kiosk_id = $this->request->Session()->read('kiosk_id');
        if(!empty($kiosk_id)){
            $productSource = "kiosk_{$kiosk_id}_products";
            $productSalesSource = "kiosk_{$kiosk_id}_product_sales";
            $reciptTable_source = "kiosk_{$kiosk_id}_product_receipts";
        }else{
            $productSource = "products";
            $productSalesSource = "kiosk_product_sales";
            $reciptTable_source = "product_receipts";
        }
        $productTable = TableRegistry::get($productSource,[
                                                                                    'table' => $productSource,
                                                                                ]);
        $salesTable = TableRegistry::get($productSalesSource,[
                                                                                    'table' => $productSalesSource,
                                                                                ]);
        $reciptTable = TableRegistry::get($reciptTable_source,[
                                                                                    'table' => $reciptTable_source,
                                                                                ]);
		
		if (!$reciptTable->exists($id)) {
			throw new NotFoundException(__('Invalid product receipt'));
		}
		$options = array(
				 'conditions' => array('id' => $id)
				 );
		$productReceipt_query = $reciptTable->find('all', $options);
        $productReceipt_query = $productReceipt_query->hydrate(false);
        if(!empty($productReceipt_query)){
            $productReceipt = $productReceipt_query->first();
        }else{
            $productReceipt = array();
        }
        
		if(!empty($productReceipt)){
            $salesTableId = $productReceipt['id'];
        }
        //pr($salesTableId);die;
        $kiosk_products_data_query = $salesTable->find('all',[
                                            'conditions' => ['product_receipt_id' => $salesTableId]  
                                            ]);
        $kiosk_products_data_query = $kiosk_products_data_query->hydrate(false);
        if(!empty($kiosk_products_data_query)){
            $kiosk_products_data = $kiosk_products_data_query->toArray();
        }else{
            $kiosk_products_data = array();
        }
        
		$productArr = $productIDs = array();
		$quantityArr = array();
		foreach($kiosk_products_data as $productSale){
			$productIid = $productSale['product_id'];
			$quantity = $productSale['quantity'];
			$productIDs[] = $productSale['product_id'];
			if(array_key_exists($productIid,$quantityArr)){
				$quantityArr[$productIid]+= $quantity;
			}else{
				$quantityArr[$productIid] =  $quantity;
			}
		}
		
        if(empty($productIDs)){
            $productIDs = array(0 => null);
        }
		$products_query = $productTable->find('all', array(
						  'conditions' => array('id IN' => $productIDs),
						  'fields' => array('id','product','image','image_dir')
						  )
				     );
        $products_query = $products_query->hydrate(false);
        if(!empty($products_query)){
            $products = $products_query->toArray();
        }else{
            $products = array();
        }
		foreach($products as $product){
			$productArr[(int)$product['id']] = $product['product'];
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
		$this->set('productArr', $productArr);
		$this->set('users', $users);
		$this->set(compact('quantityArr','kiosk_id','kiosk_products_data'));
		//------------end fetch product info---------------------
		$this->set('productReceipt', $productReceipt);
		/*$this->KioskProductSale->find('all',array(
                                                        'conditions' => array('KioskProductSale.product_receipt_id' => $id),								
							'recursive' => -1
							));*/
	}
	
	public function cancel(){
		//pr($_SESSION);die;
		$this->request->Session()->delete('Basket');
		return $this->redirect(array('controller' => 'customers','action' => 'index'));
	}
	
	public function drAllInvoices() {
		
		$this->check_dr5();
		$ProductReceipt_source = 't_product_receipts';
		$ProductReceiptTable = TableRegistry::get($ProductReceipt_source,[
																			'table' => $ProductReceipt_source,
																		]);
		$KioskProductSale_source = 't_kiosk_product_sales';
		$KioskProductSaleTable = TableRegistry::get($KioskProductSale_source,[
																				'table' => $KioskProductSale_source,
																			]);
		$kiosk_id = $this->request->Session()->read('kiosk_id');
		if(empty($kiosk_id)){
			$kiosk_id = 0;
		}
		$recit_id_data_query = $KioskProductSaleTable->find('list',[
																'conditions'=>['kiosk_id' => $kiosk_id],
																'keyField' => 'product_receipt_id',
																'valueField' => 'product_receipt_id'
															 ]
													  );
        //pr($recit_id_data_query);die;
		$recit_id_data_query = $recit_id_data_query->hydrate(false);
		if(!empty($recit_id_data_query)){
			$recit_id_data = $recit_id_data_query->toArray();
		}else{
			$recit_id_data = array();
		}
		//pr(count($recit_id_data));die;
		if(empty($recit_id_data)){
			$recit_id_data = array(0 => null);
		}
        
		$this->paginate = [
							'conditions' => ['id IN' => $recit_id_data,
											 'kiosk_id' => $kiosk_id
											 ],
							'order' => ['id desc']
						  ];
		//pr($recit_id_data);die;
		$productReceipts_query = $this->paginate($ProductReceiptTable);
		
		if(!empty($productReceipts_query)){
			$productReceipts = $productReceipts_query->toArray();
		}else{
			$productReceipts = array();
		}
		
		$customerIdArr = array();
		$customerIdArr = array();
		foreach($productReceipts as $receiptDetail){
			$customerIdArr[] = $receiptDetail->customer_id;
		}
		//pr($customerIdArr);//die;
		foreach($customerIdArr as $customerId){
			$temp = $customerId;
			if(empty($customerId)){
				$temp = $customerId;
				$customerId = array(0=>null);
			}
			//echo "<br/>".$customerId;
			if(!empty($temp)){
				$customerDetailArr_query = $this->Customers->find('all',array('conditions'=>array('Customers.id'=>$customerId),'fields'=>array('id','business')));
				//continue;
				$customerDetailArr_query = $customerDetailArr_query->hydrate(false);
				if(!empty($customerDetailArr_query)){
					$customerDetailArr[] = $customerDetailArr_query->first();
				}else{
					$customerDetailArr[] = array();
				}
			}else{
				$customerDetailArr[] = array();
			}
		}
		//pr($customerDetailArr);die;
		$customerBusiness = array();
		if(!empty($customerDetailArr)){
			foreach($customerDetailArr as $customerDetail){
			//if(array_key_exists('Customer',$customerDetail)){
					@$customerBusiness[$customerDetail['id']] = $customerDetail['business'];
				}
			//}
		}
		$this->set(compact('productReceipts', 'customerBusiness'));
		$this->render('all_invoices');
	}
	
	private function check_dr5(){
		$loggedInUser =  $this->request->session()->read('Auth.User.username');
		if (!preg_match('/'.QUOT_USER_PREFIX.'/',$loggedInUser)){
			$this->Flash->error(__('Sorry,This Page Is Not Existing.'));
				return $this->redirect(array('controller' => 'home','action' => 'dashboard'));
			die;
		}
	}
	
	public function drIndex() {
		$this->check_dr5();
		$ProductReceipt_source = 't_product_receipts';
		$ProductReceiptTable = TableRegistry::get($ProductReceipt_source,[
																			'table' => $ProductReceipt_source,
																		]);
		$KioskProductSale_source = 't_kiosk_product_sales';
		$KioskProductSaleTable = TableRegistry::get($KioskProductSale_source,[
																				'table' => $KioskProductSale_source,
																			]);
		$PaymentDetail_source = 't_payment_details';
		$PaymentDetailTable = TableRegistry::get($PaymentDetail_source,[
																				'table' => $PaymentDetail_source,
																			]);
		
		if(array_key_exists(0,$this->request->params['pass'])){
					$kskId = $this->request->params['pass'][0];
		}else{
			$kskId = $this->request->Session()->read('kiosk_id');
		}
		if(empty($kskId)){
			$kskId = 0;
		}
		
		$settingArr = $this->setting;
		//$listPayment = $this->PaymentDetail->find('list',array('fields'=>array('product_receipt_id','amount')));
		
		$receiptIdArr = array();
		$productReceiptDetail = array();
		//
		//if($listPayment){
		//	$receiptIdArr = array_keys($listPayment);
		//}
		$totalBillCost = 0;
		$productReceiptDetail_query = $ProductReceiptTable->find('all',array('conditions' => array(
																							  'kiosk_id' => $kskId,
																							  ),'fields'=>array('id','vat','status','bill_amount', 'bill_cost','created')));
		$productReceiptDetail_query = $productReceiptDetail_query->hydrate(false);
		if(!empty($productReceiptDetail_query)){
			$productReceiptDetail = $productReceiptDetail_query->toArray();
		}else{
			$productReceiptDetail = array();
		}
        //pr($productReceiptDetail);die;
		$createdArr = array();
		$lptotalPaymentAmount = 0;
		$lpgrandNetAmount = 0;
		foreach($productReceiptDetail as $key=>$productReceiptDta){
			//showing the data with product receipt status == 0 ie. the payment went through and data saved properly
			if($productReceiptDta['status']==0){
				$createdArr[$productReceiptDta['id']] = $productReceiptDta['created'];
				$totalBillCost+=floatval($productReceiptDta['bill_cost']);
                $receiptIdArr[$productReceiptDta['id']] = $productReceiptDta['id'];//capturing the receipt ids
				$paymentAmount = $productReceiptDta['bill_amount'];
				$lptotalPaymentAmount+=floatval($paymentAmount);
				$vatPercentage = $productReceiptDta['vat']/100;
				$netAmount = $paymentAmount/(1+$vatPercentage);
				$lpgrandNetAmount+=$netAmount;
			}
		}
		
                //code for getting cost price of products*******************
                $totalCost = 0;
                if(count($receiptIdArr)){
                    if(empty($receiptIdArr)){
						$receiptIdArr = array(0 => null);
					}
					$productQttArr_query = $KioskProductSaleTable->find('all', array('conditions' => array('product_receipt_id IN' => $receiptIdArr,'kiosk_id' => $kskId), 'fields' => array('product_id','quantity','product_receipt_id')));
					$productQttArr_query = $productQttArr_query->hydrate(false);
					if(!empty($productQttArr_query)){
						$productQttArr = $productQttArr_query->toArray();
					}else{
						$productQttArr = array();
					}
                    $receiptIdDetail = array();
                    $recit_ids = $productIdsArr = array();
                    foreach($productQttArr as $key => $productQtt){
						$recit_ids[$productQtt['product_receipt_id']] = $productQtt['product_receipt_id'];
                        //$receiptIdDetail[$productQtt['KioskProductSale']['product_receipt_id']][] = $productQtt['KioskProductSale'];
                        $productIdsArr[$productQtt['product_id']] = $productQtt['product_id'];
                    }
                    if(empty($productIdsArr)){
						$productIdsArr = array(0 => null);
					}
					$costPriceList_query = $this->Products->find('list',[
																			'conditions' => ['Products.id IN' => $productIdsArr],
																			'keyField' => 'id',
																			'valueField' => 'cost_price'
																		]
																);
					$costPriceList_query = $costPriceList_query->hydrate(false);
					if(!empty($costPriceList_query)){
						$costPriceList = $costPriceList_query->toArray();
					}else{
						$costPriceList = array();
					}
                    foreach($productQttArr as $key => $productQtt){
                        if(!array_key_exists($productQtt['product_id'],$costPriceList)){continue;}
                        $costPrice = $costPriceList[$productQtt['product_id']]*$productQtt['quantity'];
                        $totalCost+=$costPrice;
                    }
                }
                //*********************till here
          
		$lptotalVat = $lptotalPaymentAmount - $lpgrandNetAmount;
		$lptotalVat = $lptotalVat;
		
		$this->set(compact('lptotalPaymentAmount','lpgrandNetAmount','lptotalVat','totalCost'));
		
		$this->paginate = [
							'conditions' => ['kiosk_id' => $kskId],
							'order' => ['product_receipt_id DESC'],
							'limit' => 50
						  ];
		$productReceipts_query = $this->paginate($PaymentDetailTable);
		if(!empty($productReceipts_query)){
			$productReceipts = $productReceipts_query->toArray();
		}else{
			$productReceipts = array();
		}
		
		$fixed_cost_sum_query = $ProductReceiptTable->find('all',array('conditions' => ['kiosk_id' => $kskId]));
		$fixed_cost_sum_query
					->select(['fixed_cost' => $fixed_cost_sum_query->func()->sum('bill_cost')]);
        
		$fixed_cost_sum_query = $fixed_cost_sum_query->hydrate(false);
		if(!empty($fixed_cost_sum_query)){
			$fixed_cost_sum = $fixed_cost_sum_query->first(false);
		}else{
			$fixed_cost_sum = array();
		}
		
		//pr($productReceipts);die;
		$customerIdArr = array();
		 //pr($createdArr);die;
		 //pr($productReceipts);
		 $product_receiptId = array();
		if(!empty($productReceipts)){
			foreach($productReceipts as $productReceipts_value){
			   $product_receiptId[] = $productReceipts_value['product_receipt_id'];
			}
		}
		if(empty($product_receiptId)){
			$product_receiptId = array(0 => null);
		}
		//pr($product_receiptId);die;
		   $product_receipt_data_query = $ProductReceiptTable->find('all',[
																				   'conditions' => ['id IN' => $product_receiptId]
																			   ]
												   );
		   //pr($product_receipt_data_query);die;
			   $product_receipt_data_query = $product_receipt_data_query->hydrate(false);
			   if(!empty($product_receipt_data_query)){
				   $product_receipt_data = $product_receipt_data_query->toArray();
			   }else{
				   $product_receipt_data = array();
			   }
		 //pr($product_receipt_data);die;
		 
		foreach($product_receipt_data as $receiptDetail){
			//pr($receiptDetail);die;
			$customerIdArr[] = $receiptDetail['customer_id'];
			$productreceiptArr[$receiptDetail['id']] = $receiptDetail;
			
		}
		$this->set(compact('productreceiptArr'));
		if(empty($customerIdArr)){
			$customerIdArr = array(0 => null);
		}
		 $customerBusiness_query = $this->Customers->find('list',[
                                                    'keyField' => 'id',
                                                     'valueField' => 'business',
                                                   'conditions' =>['Customers.id IN'=>array_unique($customerIdArr)],
                                                 ]
                                        ); 
		$customerBusiness_query = $customerBusiness_query->hydrate(false);
		if(!empty($customerBusiness_query)){
			$customerBusiness = $customerBusiness_query->toArray();
		}else{
			$customerBusiness = array();
		}
		if($this->request->session()->read('Auth.User.group_id') == ADMINISTRATORS || $this->request->session()->read('Auth.User.group_id') == FRANCHISE_OWNER){
			//$session_id = $this->Session->id();
			$user_id = $this->request->Session()->read('Auth.User.id');
			$data_arr = array('kiosk_id' => 10000);
			$jsondata = json_encode($data_arr);
			$this->loadModel('UserSettings');
			
			$res_query = $this->UserSettings->find('all',array('conditions' => array(
																		 'user_id' => $user_id,
																		 'setting_name' => "dr_search",
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
									'setting_name' => "dr_search",
									'data' => $jsondata
									);
				$entity = $this->UserSettings->get($userSettingid);
				$entity = $this->UserSettings->patchEntity($entity,$data_to_save);
				$this->UserSettings->save($entity);
			}else{
				$data_to_save = array(
									'user_id' => $user_id,
									//'user_session_key' => $session_id,
									'setting_name' => "dr_search",
									'data' => $jsondata
									);
				$entity = $this->UserSettings->newEntity();
				$entity = $this->UserSettings->patchEntity($entity,$data_to_save);
				$this->UserSettings->save($entity);
			}
		}
		$kiosk_list_query = $this->Kiosks->find('list');
		$kiosk_list_query = $kiosk_list_query->hydrate(false);
		if(!empty($kiosk_list_query)){
			$kiosk_list = $kiosk_list_query->toArray();
		}else{
			$kiosk_list = array();
		}
		$kiosk_id = 10000;
		
		$agents_query = $this->Agents->find('list');
		$agents_query = $agents_query->hydrate(false);
		
		if(!empty($agents_query)){
			$agents = $agents_query->toArray();
		}
		$agents[0] = "Select Acc manager";
		ksort($agents);
		
		$this->set(compact('productReceipts', 'customerBusiness','totalAmount','totalBillCost','createdArr','recit_ids','kiosk_list','kiosk_id','fixed_cost_sum','agents'));
	}
	
	public function drGenerateReceipt($id = null){
		$this->check_dr5();
		$ProductReceipt_source = 't_product_receipts';
		$ProductReceiptTable = TableRegistry::get($ProductReceipt_source,[
																			'table' => $ProductReceipt_source,
																		]);
		$KioskProductSale_source = 't_kiosk_product_sales';
		$KioskProductSaleTable = TableRegistry::get($KioskProductSale_source,[
																				'table' => $KioskProductSale_source,
																			]);
		$PaymentDetail_source = 't_payment_details';
		$PaymentDetailTable = TableRegistry::get($PaymentDetail_source,[
																				'table' => $PaymentDetail_source,
																			]);
		$refundOptions = Configure::read('refund_status');
		
		if (!$ProductReceiptTable->exists($id)) {
			throw new NotFoundException(__('Invalid product receipt'));
		}
		
		$countryOptions = Configure::read('uk_non_uk');
		
		$options = array(
				 'conditions' => array('id' => $id)
				 );
		$productReceipt_query = $ProductReceiptTable->find('all', $options);
		$productReceipt_query = $productReceipt_query->hydrate(false);
		if(!empty($productReceipt_query)){
			$productReceipt = $productReceipt_query->first();
		}else{
			$productReceipt = array();
		}
		$kiosk_id = '';
		//pr($productReceipt);die;
		if(!empty($productReceipt)){
			$kioskProductSalesId = $productReceipt['id'];
			$kioskProductSales_query = $KioskProductSaleTable->find('all',[
														'conditions' => ['product_receipt_id' => $kioskProductSalesId]
													]);
			$kioskProductSales_query = $kioskProductSales_query->hydrate(false);
			if(!empty($kioskProductSales_query)){
				$kioskProductSales = $kioskProductSales_query->toArray();
			}else{
				$kioskProductSales = array();
			}
			$customersID = $productReceipt['customer_id'];
			$customers_query = $this->Customers->find('all',[
																'conditions' => ['id' => $customersID]
															]);
			$customers_query = $customers_query->hydrate(false);
			if(!empty($customers_query)){
				$customers =  $customers_query->first();
			}else{
				$customers = array();
			}
		}else{
			$kioskProductSales = array();
			$customers = array();
		}
		//pr($customers);die;
		//pr($kioskProductSales);die;
		if(array_key_exists(0,$kioskProductSales)){
			$kiosk_id = $kioskProductSales['0']['kiosk_id'];
		}
		$this->set(compact('kioskProductSales','customers'));
		$fullAddress = $kioskDetails = $kioskName = $kioskAddress1 = $kioskAddress2 = $kioskCity = $kioskState = $kioskZip  = $kioskZip = $kioskContact = $kioskCountry = $kioskTable = "";
		
		$kioskDetails_query = $this->Kiosks->find('all',array('conditions'=>array('Kiosks.id'=>$kiosk_id),'fields'=>array('id','name','address_1','address_2','city','state','zip','contact','country')));
		$kioskDetails_query = $kioskDetails_query->hydrate(false);
		if(!empty($kioskDetails_query)){
			$kioskDetails = $kioskDetails_query->first();
		}else{
			$kioskDetails = array();
		}
		if(!empty($kioskDetails)){	
		if($this->request->session()->read('Auth.User.group_id') == KIOSK_USERS &&
		    $this->request->session()->read('Auth.User.username')=='wholesale'){
			//$kiosk_id = $this->Session->read('kiosk_id');
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
		
		foreach($kioskProductSales as $key => $productDetail){
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
				if(array_key_exists($saleKey,$qttyArr)){
					$qttyArr[$saleKey] += $productSaleDetail['quantity'];
				}else{
					$qttyArr[$saleKey] = $productSaleDetail['quantity'];
				}
				if(array_key_exists($saleKey,$returnQuantityArr)){
					$qttyArr[$saleKey]+= $returnQuantityArr[$saleKey];
				}
			}
		}
		foreach($productIdArr as $product_id){
			$product_detail_query = $this->Products->find('all', array('conditions'=>array('Products.id'=>$product_id),'fields' => array('id','product','product_code')));
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
		$processed_by = $productReceipt['processed_by'];
		$userName_query = $this->Users->find('all',array('conditions'=>array('Users.id'=>$processed_by),'fields'=>array('username')));
		$userName_query = $userName_query->hydrate(false);
		if(!empty($userName_query)){
			$userName = $userName_query->first();
		}else{
			$userName = array();
		}
		$user_name = $userName['username']; 
		$paymentDetails_query = $PaymentDetailTable->find('all',array('conditions' => array('product_receipt_id' => $id)));
		$paymentDetails_query = $paymentDetails_query->hydrate(false);
		if(!empty($paymentDetails_query)){
			$paymentDetails = $paymentDetails_query->toArray();
		}else{
			$paymentDetails = array();
		}
		//pr($paymentDetails);die;
		if(!empty($paymentDetails)){
			$product_receiptId = $paymentDetails[0]['product_receipt_id'];
			$product_receipt_data_query = $ProductReceiptTable->find('all',[
															'conditions' => ['id' => $product_receiptId]
														]);
			$product_receipt_data_query = $product_receipt_data_query->hydrate(false);
			if(!empty($product_receipt_data_query)){
				$product_receipt_data = $product_receipt_data_query->first();
			}else{
				$product_receipt_data = array();
			}
			//pr($product_receipt_data);die;
			$customerID = $product_receipt_data['customer_id'];
			$customer_query = $this->Customers->find('all',[
																'conditions' => ['id' => $customerID]
															]);
			$customer_query = $customer_query->hydrate(false);
			if(!empty($customer_query)){
				$customer =  $customer_query->first();
			}else{
				$customer = array();
			}
			//pr($customer);die;
		}else{
			$customer = array();
		}
		$this->set(compact('customer'));
		$payment_method = array();
		foreach($paymentDetails as $key=>$paymentDetail){
			//pr($paymentDetail);
			$payment_method[] = $paymentDetail['payment_method']." ".$settingArr['currency_symbol'].$paymentDetail['amount'];
		}
		if(!empty($customer['email'])){
			$customerEmail = $customer['email'];
		}else{
			$customerDataReceipt_query = $ProductReceiptTable->find('all',array('conditions'=>array('id'=>$id),'fields'=>array('id','email')));
			$customerDataReceipt_query = $customerDataReceipt_query->hydrate(false);
			if(!empty($customerDataReceipt_query)){
				$customerDataReceipt = $customerDataReceipt_query->first();
			}else{
				$customerDataReceipt = array();
			}
			$customerEmail = $customerDataReceipt['email'];
		}
		
		
		$this->set(compact('productReceipt', 'kiosk','vat','productName','customerEmail','paymentDetails','settingArr','payment_method','user_name','productCode','kioskTable','kioskContact','countryOptions','currency','qttyArr','kioskDetails'));
		
		if ($this->request->is(array('post', 'put'))) {
			$emailSender = Configure::read('EMAIL_SENDER');
			$Email = new Email();
			$Email->config('default');
			$Email->viewVars(array(
								   'qttyArr' => $qttyArr,
								   'settingArr' => $settingArr,
								   'productReceipt' => $productReceipt,
								   'kiosk' => $kiosk,
								   'vat' => $vat,
								   'productName' => $productName,
								   'currency' => $currency,
								   'refundOptions' => $refundOptions,
								   'kioskDetails' => $kioskDetails
								   )
							 );
			//$Email->config(array('from' => 'me@example.org', 'transport' => 'MyCustom'));
			//$Email = new CakeEmail(array('from' => 'me@example.org', 'transport' => 'MyCustom'));
			$emailTo = $this->request['data']['customer_email'];
			$Email->template('customer_receipt');
			$Email->emailFormat('both');
			$Email->to($emailTo);
			$Email->transport(TRANSPORT);
			$Email->from([$send_by_email => $emailSender]);
			$Email->sender($send_by_email);
			$Email->subject('Order Receipt');
			if($Email->send()){
				$this->Flash->success("Email has been successfully sent");
			}
		}
		//$this->layout = 'default';
		$this->render('dr_generate_receipt');
	}
	
	public function drEdit($id = null) {
		$this->check_dr5();
		$ProductReceipt_source = 't_product_receipts';
		$ProductReceiptTable = TableRegistry::get($ProductReceipt_source,[
																			'table' => $ProductReceipt_source,
																		]);
		$KioskProductSale_source = 't_kiosk_product_sales';
		$KioskProductSaleTable = TableRegistry::get($KioskProductSale_source,[
																				'table' => $KioskProductSale_source,
																			]);
		$PaymentDetail_source = 't_payment_details';
		$PaymentDetailTable = TableRegistry::get($PaymentDetail_source,[
																				'table' => $PaymentDetail_source,
																			]);
        $kiosk_id = $this->request->Session()->read('kiosk_id');
        if($kiosk_id == "" || $kiosk_id == 10000 || empty($kiosk_id)){
            $Product_source = 'products';    
        }else{
            $Product_source = "kiosk_{$kiosk_id}_products";
        }
		
		$ProductTable = TableRegistry::get($Product_source,[
																'table' => $Product_source,
															]);
		
		if (!$ProductReceiptTable->exists($id)) {
			throw new NotFoundException(__('Invalid product receipt'));
		}
		
		$options = array('conditions' => array('id' => $id));
		$productReceiptData_query = $ProductReceiptTable->find('all', $options);
		$productReceiptData_query = $productReceiptData_query->hydrate(false);
		if(!empty($productReceiptData_query)){
			$productReceiptData = $productReceiptData_query->first();
		}else{
			$productReceiptData = array();
		}
		//pr($productReceiptData);die;
		if(!empty($productReceiptData)){
			$kiosk_product_salesID = $productReceiptData['id'];
			$kiosk_product_sales_query = $KioskProductSaleTable->find('all',[
																				'conditions' => ['product_receipt_id' => $kiosk_product_salesID]
																			]);
			$kiosk_product_sales_query = $kiosk_product_sales_query->hydrate(false);
			if(!empty($kiosk_product_sales_query)){
				$kiosk_product_sales = $kiosk_product_sales_query->toArray();
			}else{
				$kiosk_product_sales = array();
			}
		}else{
			$kiosk_product_sales = array();
		}
		//pr($kiosk_product_sales);die;
		$combinedArr = array();
		$productArr = array();
		foreach($kiosk_product_sales as $sale){
			$productArr[] = $sale['product_id'];
		}
		$this->set(compact('kiosk_product_sales'));
		$bulkDiscount = $productReceiptData['bulk_discount'];
		if(empty($productArr)){
			$productArr = array(0 => null);
		}
		$productQuantityDetail_query = $ProductTable->find('all',array(
								'conditions' => array('id IN'=>$productArr),
								'fields' => array('id','quantity')
									)
							      );
		$productQuantityDetail_query = $productQuantityDetail_query->hydrate(false);
		if(!empty($productQuantityDetail_query)){
			$productQuantityDetail = $productQuantityDetail_query->toArray();
		}else{
			$productQuantityDetail = array();
		}
		//pr($productQuantityDetail);
		$productQuantityArr = array();
		foreach($productQuantityDetail as $quantityDetail){
			$productQuantityArr[$quantityDetail['id']] = $quantityDetail['quantity'];
		}
		
		if ($this->request->is(array('post', 'put')) && isset($_REQUEST['update_invoice_pmt'])) {
			//pr($this->request);die;
			if(array_key_exists('cancel',$this->request->data)){
				if($this->request->session()->read('Auth.User.group_id') == ADMINISTRATORS || $this->request->session()->read('Auth.User.group_id') == FRANCHISE_OWNER){
					$kiosk_id_to_set = $this->get_kiosk_for_dr_invoice();
					return $this->redirect(array('controller'=>'product_receipts','action'=>"dr_search?kiosk_id=$kiosk_id_to_set&date_type=invoice"));
				}else{
					return $this->redirect(array('controller'=>'product_receipts','action'=>'dr_index'));
				}
				//return $this->redirect(array('action'=>'index'));
			}
			$newInvoiceOrderAmount = $this->request['data']['final_amount'];
			$productReceiptDetails_query = $ProductReceiptTable->Find('all',array(
									'conditions' => array('id'=>$id)
									)
								 );
			$productReceiptDetails_query = $productReceiptDetails_query->hydrate(false);
			if(!empty($productReceiptDetails_query)){
				$productReceiptDetails = $productReceiptDetails_query->first();
			}else{
				$productReceiptDetails = array();
			}
			$amountToPay = round($this->request['data']['final_amount'],2);
			
			$totalPaymentAmount = 0;
			$amountDesc = array();
			$countCycles = 0;
			$error = array();
			$errorStr = '';
			foreach($this->request['data']['Payment']['Amount'] as $key => $paymentAmount){
				$totalPaymentAmount+= $paymentAmount;
				$paymentDescription = $this->request['data']['Payment']['Description'][$key];
				if(!empty($paymentDescription) && !empty($paymentAmount)){
					$countCycles++;
				}
				if(empty($paymentDescription) && !empty($paymentAmount)){
					$error[] = "Sale could not be created. Payment description must be entered";
					break;
				}
			}
			foreach($this->request['data']['Payment']['Payment_Method'] as $key => $paymentMethod){
				
				if(empty($totalPaymentAmount) &&
					($paymentMethod=="Cheque" ||
					$paymentMethod=="Cash" ||
					$paymentMethod=="Bank Transfer" ||
					$paymentMethod=="Card")){
					$error[] = "Amount must be equivalent to &#163; {$amountToPay}. Please try again";
					break;
				}elseif($totalPaymentAmount<$amountToPay &&
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
			
			
			$hiddenFields = "";
			//pr($this->request['data']);die;
			foreach($this->request['data']['Payment']['quantity'] as $key => $qtty){
				$productSaleId = $this->request['data']['Payment']['id'][$key];
				$hiddenFields.="<input type = 'hidden' name ='Payment[quantity][$key]' value='$qtty'/>";
				$hiddenFields.="<input type = 'hidden' name ='Payment[id][$key]' value='$productSaleId'/>";
			}
				$counter = 0;
				if(count($error)>0){
					$errorStr = implode("<br/>",$error);
					$this->Flash->error($errorStr,['escape' => false]);
					$this->set(compact('newInvoiceOrderAmount','hiddenFields','bulkDiscount'));
					$this->render('update_invoice_payment');
					$this->response->send();
					$this->_stop();
				}else{
                    $kiosk_id = $this->request->Session()->read('kiosk_id');
                    if(empty($kiosk_id) || $kiosk_id == 10000){
                        $kiosk_id = $this->get_kiosk_for_dr_invoice();
                    }
					if($this->request['data']['Payment']['Payment_Method'][0] == "On Credit" && $countCycles==1){
						if($kiosk_id == 10000){
							$kiosk_id_to_save = 0;
						}else{
							$kiosk_id_to_save = $kiosk_id;
						}
						$paymentDetailData = array(
							'product_receipt_id' => $id,
                            'kiosk_id' => $kiosk_id_to_save,
							'payment_method' => $this->request['data']['Payment']['Payment_Method'][0],
							'description' => $this->request['data']['Payment']['Description'][0],
							'amount' => $amountToPay,
							'payment_status' => 0,
							'status' => 1,
							'agent_id' => $productReceiptData['agent_id']
							   );
                        $PaymentDetailTable->behaviors()->load('Timestamp');
						$new_entity = $PaymentDetailTable->newEntity($paymentDetailData,['validate' => false]);
						$patch_entity = $PaymentDetailTable->patchEntity($new_entity,$paymentDetailData,['validate' => false]);
						if($PaymentDetailTable->save($patch_entity)){
							$counter++;
						}
					}else{
						foreach($this->request['data']['Payment']['Amount'] as $key => $paymentAmount){
							$paymentMethod = $this->request['data']['Payment']['Payment_Method'][$key];
							$paymentDescription = $this->request['data']['Payment']['Description'][$key];
							
								if($paymentMethod == "On Credit"){
									$payment_status = 0;
								}else{
									$payment_status = 1;
								}
								
								if(!empty($paymentAmount) && !empty($paymentDescription)){
									if($kiosk_id == 10000){
										$kiosk_id_to_save = 0;
									}else{
										$kiosk_id_to_save = $kiosk_id;
									}
									$paymentDetailData = array(
                                            'kiosk_id' => $kiosk_id_to_save,
											'product_receipt_id' => $id,
											'payment_method' => $paymentMethod,
											//'description' => $paymentDescription,
											'amount' => $paymentAmount,
											'payment_status' => $payment_status,
											'status' => 1,
											'agent_id' => $productReceiptData['agent_id']
											   );
                                    $PaymentDetailTable->behaviors()->load('Timestamp');
									$new_entity = $PaymentDetailTable->newEntity($paymentDetailData,['validate' => false]);
									$patch_entity = $PaymentDetailTable->patchEntity($new_entity,$paymentDetailData,['validate' => false]);
									if($PaymentDetailTable->save($patch_entity)){
										$counter++;
									}
								}
							}
					}
				}
				if(empty($kiosk_id)){
					$kiosk_id = $this->request->Session()->read('kiosk_id');
				}
							//if(!empty($kiosk_id)){
							//	$salesTable = "kiosk_{$kiosk_id}_product_sales";
							//	$productsTable = "kiosk_{$kiosk_id}_products";
							//	$productreceiptsTable = "kiosk_{$kiosk_id}_product_receipts";
							//}else{
								$salesTable = "t_kiosk_product_sales";
								$productsTable = "products";
								$productreceiptsTable = "t_product_receipts";
							//}
                            if($kiosk_id == "" || $kiosk_id == 10000 || empty($kiosk_id)){
                                $productsTable = 'products';    
                            }else{
                                $productsTable = "kiosk_{$kiosk_id}_products";
                            }
							
				$count = 0;
				if($counter>0){
					$total = 0;
					$product_code = $this->Products->find('list',array(
										   'keyField' => 'id',
										   'valueField' => 'product_code',
										   ))->toArray();
					foreach($this->request['data']['Payment']['quantity'] as $key => $quantity){
						$Id = $this->request['data']['Payment']['id'][$key];
						
						$kioskProductSaleData = array(
									'id' => $Id,
									'quantity' => $quantity
										);
						
						if($quantity>0){
							$conn = ConnectionManager::get('default');
							$stmt = $conn->execute("UPDATE `$salesTable` SET `quantity` = `quantity` + $quantity WHERE `$salesTable`.`id` = $Id"); 
							//$currentTimeInfo = $stmt ->fetchAll('assoc');
							//$this->KioskProductSale->query("UPDATE `$salesTable` SET `quantity` = `quantity` + $quantity WHERE `$salesTable`.`id` = $Id");
							$saleDetail_query = $KioskProductSaleTable->find('all',array(
													'conditions'=>array('id'=>$Id),
													'fields'=>['product_id','sale_price']
													)
												       );
							$saleDetail_query = $saleDetail_query->hydrate(false);
							if(!empty($saleDetail_query)){
								$saleDetail = $saleDetail_query->first();
							}else{
								$saleDetail = array();
							}
							$productId = $saleDetail['product_id'];
							$selling_price_withot_vat = $saleDetail['sale_price'];
							
							$data = array(
									'quantity' => $quantity,
									'product_code' => $product_code[$productId],
									'selling_price_withot_vat' => $selling_price_withot_vat,
									'vat' => 0,
							);
							if($kiosk_id == 0){
								$kiosk_id_to_use = 10000;
							}else{
								$kiosk_id_to_use = $kiosk_id;
							}
							$this->insert_to_ProductSellStats($productId,$data,$kiosk_id_to_use,$operations = '+',1);
							
							
							$product_conn = ConnectionManager::get('default');
							$product_stmt = $product_conn->execute("UPDATE `$productsTable` SET `quantity` = `quantity` - $quantity WHERE `$productsTable`.`id` = $productId"); 
							//$this->Product->query("UPDATE `$productsTable` SET `quantity` = `quantity` - $quantity WHERE `$productsTable`.`id` = $productId");
							$count++;
						}
						// added by sourabh----------------
						$detail_query = $KioskProductSaleTable->find('all',array(
													'conditions'=>array('id'=>$Id),
													'fields'=>array('product_id','quantity')
													)
												       );
						$detail_query = $detail_query->hydrate(false);
						if(!empty($detail_query)){
							$detail = $detail_query->first();
						}else{
							$detail = array();
						}
						$productId = $detail['product_id'];
						$qntity = $detail['quantity'];
						$costPrice_query = $ProductTable->find('list',[
																	'conditions' => ['id' => $productId],
																	'keyField' => 'id',
																	'valueField' => 'cost_price'
																]
														);
						$costPrice_query = $costPrice_query->hydrate(false);
						if(!empty($costPrice_query)){
							$costPrice = $costPrice_query->toArray();
						}else{
							$costPrice = array();
						}
						//pr($costPrice);
						$total += $costPrice[$productId] * $qntity;
						// added by sourabh-----------------------------
					}
				}
				//echo $total;die;
				if ((int)$count) {
					$ProductReceipt_conn = ConnectionManager::get('default');
					$ProductReceipt_stmt = $ProductReceipt_conn->execute("UPDATE `$productreceiptsTable` SET `bill_cost` = $total WHERE `$productreceiptsTable`.`id` = $id");
					//$this->ProductReceipt->query("UPDATE `$productreceiptsTable` SET `bill_cost` = $total WHERE `$productreceiptsTable`.`id` = $id");
					$ProductReceipt1_conn = ConnectionManager::get('default');
					$ProductReceipt1_stmt = $ProductReceipt1_conn->execute("UPDATE `$productreceiptsTable` SET `bill_amount` = `bill_amount` + $newInvoiceOrderAmount,`orig_bill_amount` = `orig_bill_amount` + $newInvoiceOrderAmount WHERE `$productreceiptsTable`.`id` = $id");
					//$this->ProductReceipt->query("UPDATE `$productreceiptsTable` SET `bill_amount` = `bill_amount` + $newInvoiceOrderAmount WHERE `$productreceiptsTable`.`id` = $id");// added by sourabh
					$this->Flash->success(__("$count record(s) have been saved."));
					
					if($this->request->session()->read('Auth.User.group_id') == ADMINISTRATORS || $this->request->session()->read('Auth.User.group_id') == FRANCHISE_OWNER){
						$kiosk_id_to_set = $this->get_kiosk_for_dr_invoice();
						return $this->redirect(array('controller'=>'product_receipts','action'=>"dr_search?kiosk_id=$kiosk_id_to_set&date_type=invoice"));
					}else{
						return $this->redirect(array('controller'=>'product_receipts','action'=>'dr_index'));
					}
					//return $this->redirect(array('controller'=>'product_receipts','action' => 'dr_index'));
				} else {
					$this->Flash->error(__('The product receipt could not be saved. Please, try again.'));
					return $this->redirect(array('action'=>'dr_edit',$id));
				}
				
		}elseif ($this->request->is(array('post', 'put'))) {
			
			//if(array_key_exists(''))
			if(array_key_exists('add_more_products',$this->request['data'])){
				return $this->redirect(array('controller'=>'kiosk_product_sales','action' => 'dr_edit_receipt',$id));
			}else{
				//pr($this->request);die;
					$vat = $this->VAT;
					$vatItem = $vat/100;
					$totalQuantity = 0;
					foreach($this->request['data']['ProductReceipt']['quantity'] as $key=>$quantty){
						$totalQuantity+=$quantty;
					}
					
					if(empty($this->request['data']['ProductReceipt']['quantity'])){
						$this->Flash->error("Please add quantity");
						return $this->redirect(array('action'=>'dr_edit',$id));
					}elseif($totalQuantity == 0 || $totalQuantity == ''){
						$this->Flash->error("Please add quantity");
						return $this->redirect(array('action'=>'dr_edit',$id));
					}
					
					$counter = 0;
					$afterDiscountPrice = 0;
					$hiddenFields = "";
					$quantityError = 0;
					foreach($this->request['data']['ProductReceipt']['id'] as $key=>$productSaleId){
						$centralQuantity = $this->request['data']['ProductReceipt']['centralQuantity'][$key];
						$orderQuantity = $this->request['data']['ProductReceipt']['quantity'][$key];
						$orderDiscount = $this->request['data']['ProductReceipt']['discount'][$key];
						$orderPrice = $this->request['data']['ProductReceipt']['price'][$key];
						$discountedPrice = $orderQuantity*($orderPrice-$orderPrice*$orderDiscount/100);
						$afterDiscountPrice+=$discountedPrice;
						$hiddenFields.="<input type = 'hidden' name ='Payment[quantity][$key]' value='$orderQuantity'/>";
						$hiddenFields.="<input type = 'hidden' name ='Payment[id][$key]' value='$productSaleId'/>";
						
						if($orderQuantity>$centralQuantity){
							$quantityError++;
							break;
						}
					}
					//echo $afterDiscountPrice;echo "</br>";
					$newInvoiceOrderAmount = $afterDiscountPrice - $afterDiscountPrice*$bulkDiscount/100;
					//echo $newInvoiceOrderAmount;die;
					/*if($productReceiptData['ProductReceipt']['vat'] == 0){
							$newInvoiceOrderAmount = $newInvoiceOrderAmount/(1+$vatItem);
							$newInvoiceOrderAmount = number_format($newInvoiceOrderAmount,2);
					}*/
					if($productReceiptData['vat'] != 0){
						//$newInvoiceOrderAmount = $newInvoiceOrderAmount + ($newInvoiceOrderAmount * $vat)/100;
						//$newInvoiceOrderAmount = number_format($newInvoiceOrderAmount,2);
					}
					//echo $newInvoiceOrderAmount;die;
					
					if($quantityError>0){
						$this->Flash->error("Qantity cannot be more than central quantity");
						return $this->redirect(array('action'=>'dr_edit',$id));
					}
					
					$this->set(compact('newInvoiceOrderAmount','hiddenFields','bulkDiscount'));
					$this->render('update_invoice_payment');
			}
		} else {
			$options = array('conditions' => array('id' => $id));
			$ProductReceipt_query = $ProductReceiptTable->find('all', $options);
			$ProductReceipt_query = $ProductReceipt_query->hydrate(false);
			if(!empty($ProductReceipt_query)){
				$ProductReceipt = $ProductReceipt_query->first();
			}else{
				$ProductReceipt = array();
			}
			if(!empty($ProductReceipt)){
				$kiosk_productSalesID = $ProductReceipt['id'];
				$kiosk_productSales_query = $KioskProductSaleTable->find('all',[
															'conditions' => ['product_receipt_id' => $kiosk_productSalesID]
														  ]);
				$kiosk_productSales_query = $kiosk_productSales_query->hydrate(false);
				if(!empty($kiosk_productSales_query)){
					$kiosk_productSales = $kiosk_productSales_query->toArray();
				}else{
					$kiosk_productSales = array();
				}
			}
			$this->request->data = $kiosk_productSales	;
		}
		if(empty($productArr)){
			$productArr = array(0 => null) ;
		}
		$products_query = $ProductTable->find('all',array('conditions'=>array('id IN'=>$productArr),'fields'=>array('id','product_code','product')));
		$products_query = $products_query->hydrate(false);
		if(!empty($products_query)){
			$products = $products_query->toArray();
		}else{
			$products = array();
		}
		foreach($products as $p=>$prodc){
			$productName[$prodc['id']]=$prodc['product'];
			$productCode[$prodc['id']]=$prodc['product_code'];
		}
		$kiosks_query = $this->Kiosks->find('list');
		$kiosks_query = $kiosks_query->hydrate(false);
		if(!empty($kiosks_query)){
			$kiosks = $kiosks_query->toArray();
		}else{
			$kiosks = array();
		}
		$users_query = $this->Users->find('list');
		$users_query = $users_query->hydrate(false);
		if(!empty($users_query)){
			$users = $users_query->toArray();
		}else{
			$users = array();
		}
		$customers_query = $this->Customers->find('list');
		$customers_query = $customers_query->hydrate(false);
		if(!empty($customers_query)){
			$customers = $customers_query->toArray();
		}else{
			$customers = array();
		}
		$this->set(compact('customers','productCode','productName','kiosks','users','bulkDiscount','productReceiptData','productQuantityArr'));
	}
	
	public function drUpdatePayment($paymentId = '',$kiosk_id = ""){
		$users = $this->Users->find("list",['keyField' => 'id',
								   'valueField' => 'f_name',
								   ])->toArray();
		$this->check_dr5();
		//$this->PaymentDetail->setSource('t_payment_details');
		if(empty($kiosk_id)){
			$kiosk_id_by_session = $this->request->Session()->read('kiosk_id');
			$kiosk_id = $kiosk_id_by_session;
			if(empty($kiosk_id)){
				$kiosk_id = 0;
			}
		}
		$ProductReceipt_source = 't_product_receipts';
		$ProductReceiptTable = TableRegistry::get($ProductReceipt_source,[
																			'table' => $ProductReceipt_source,
																		]);
		$KioskProductSale_source = 't_kiosk_product_sales';
		$KioskProductSaleTable = TableRegistry::get($KioskProductSale_source,[
																				'table' => $KioskProductSale_source,
																			]);
		$PaymentDetail_source = 't_payment_details';
		$PaymentDetailTable = TableRegistry::get($PaymentDetail_source,[
																				'table' => $PaymentDetail_source,
																			]);
		$paymentData_query = $PaymentDetailTable->find('all',array(
							'conditions' => array('id'=>$paymentId)
								)
							  );
		$paymentData_query = $paymentData_query->hydrate(false);
		if(!empty($paymentData_query)){
			$paymentData = $paymentData_query->first();
		}else{
			$paymentData = array();
		}
		
		$oldPmtMethod = $paymentData['payment_method'];
		$recit_id = $paymentData['product_receipt_id'];
		$result_query = $ProductReceiptTable->find('all',array(
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
		$agent_id = $result['agent_id'];
		$this->set(compact('recit_created'));
		if ($this->request->is(array('post', 'put'))){
			if(array_key_exists('data',$this->request->data)){
				if(array_key_exists("ticked",$this->request->data['data'])){
					$ticked = $this->request->data['data']['ticked'];
				}else{
					$ticked = 0;
				}	
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
            //pr($this->request);
			//echo $ticked;die;
			if($ticked == 1){   // normal trnsection or checked
				if(array_key_exists("date_box_date",$this->request->data)){
					$date_box_date = date("Y-m-d G:i:s",strtotime($this->request->data['date_box_date']));
			   }else{
					$date_box_date = "";
			   }
				if(array_key_exists('added_amount',$this->request->data) && is_numeric($this->request->data['added_amount']) && $this->request->data['added_amount'] > 0){
					$sale_amount = round($this->request->data['sale_amount'],2);
					$added_amount = round($this->request->data['added_amount'],2);
					$old_amt = round($this->request->data['old_amt'],2);
					$sum_amt = $added_amount + $old_amt;
					$sum_amt = round($sum_amt,2);
					if($sale_amount != $sum_amt){
						$this->Flash->error('Payment could not be updated!');
						return $this->redirect(array('action' => 'dr_update_payment',$paymentId));
						die;
					}
                    //pr($this->request);die;
					$new_paymentMode = $this->request->data['new_change_mode'];
					if($new_paymentMode == "On Credit"){
						$paymentStatus_for_new = 0;
					}else{
						$paymentStatus_for_new = 1;
					}
					if($kiosk_id == 10000){
						$kiosk_id1 = 0;
					}else{
						$kiosk_id1 = $kiosk_id;
					}
					$new_box_desc = "";
					if(array_key_exists('new_box_desc',$this->request->data)){
						$new_box_desc = $this->request->data['new_box_desc'];
					}
					
					$paymentDetailData_new = array(
							'product_receipt_id' => $recit_id,
							'payment_method' => $new_paymentMode,
							'amount' => $this->request->data['added_amount'],
							'payment_status' => $paymentStatus_for_new,
							'kiosk_id' => $kiosk_id1,
							'description'=>$new_box_desc,
							'agent_id' => $agent_id,
							//'created' => $recit_created
							'created' => $date_box_date,
							   );
					$PaymentDetailTable->behaviors()->load('Timestamp');
					$new_entity = $PaymentDetailTable->newEntity($paymentDetailData_new,['validate' => false]);
					$patch_entity = $PaymentDetailTable->patchEntity($new_entity,$paymentDetailData_new,['validate' => false]);
					$PaymentDetailTable->save($patch_entity);
					$desciption = "";
					if(array_key_exists('desc',$this->request->data)){
						$desciption = $this->request->data['desc'];
					}
					
					
					$old_amt = $this->request->data['old_amt'];
					$old_payment_data = array(
							'id' => $paymentId,
							'payment_method' => $paymentMode,
							'amount' => $old_amt,
							'payment_status' => $paymentStatus,
							'description'=>$desciption,
							//'created' => $recit_created
							'created' => $date_box_date,
							   );
					$PaymentDetailTable->behaviors()->load('Timestamp');
					$getID = $PaymentDetailTable->get($paymentId);
					$patchEntity = $PaymentDetailTable->patchEntity($getID,$old_payment_data,['validate' => false]);
					$PaymentDetailTable->save($patchEntity);
				}
				
				$desciption = "";
					if(array_key_exists('desc',$this->request->data)){
						$desciption = $this->request->data['desc'];
					}
					
				$created  = date("Y-m-d G:i:s");
				if($paymentData['payment_method'] == "On Credit"){   // changing created when changing payment method from  on-credit to any other
					$paymentDetailData = array(
							'id' => $paymentId,
							'payment_method' => $paymentMode,
							'payment_status' => $paymentStatus,
							'created' => $created,
							'description'=>$desciption,
							'created' => $date_box_date,
							   );
				}
				
				if($paymentMode == "On Credit"){  //when changed payment method is On Credit add recit date to payment table created
					$paymentDetailData = array(
							'id' => $paymentId,
							'payment_method' => $paymentMode,
							'payment_status' => $paymentStatus,
							'created' => $recit_created,
							'description'=>$desciption
							   );
				}
				
				if(empty($paymentDetailData)){
					$paymentDetailData = array(
							'id' => $paymentId,
							'payment_method' => $paymentMode,
							'payment_status' => $paymentStatus,
							'description'=>$desciption,
							'created' => $date_box_date,
							   );
				}
			}else{ // correcting or unchecked
				//pr($this->request);die;
				if(array_key_exists('added_amount',$this->request->data) && is_numeric($this->request->data['added_amount']) && $this->request->data['added_amount'] > 0){
					$sales_amount = round($this->request->data['sale_amount'],2);
					$old_amount = $this->request->data['old_amt'];
					$added_amount = $this->request->data['added_amount'];
					$sum_up_amount = $old_amount + $added_amount;
					$sum_up_amount  = round($sum_up_amount,2);
					if($sales_amount != $sum_up_amount){
						$this->Flash->error('Payment could not be updated!');
						return $this->redirect(array('action' => 'dr_update_payment',$paymentId));
						die;
					}
					
					$new_paymentMode = $this->request->data['new_change_mode'];
					if($new_paymentMode == "On Credit"){
						$paymentStatus_for_new = 0;
					}else{
						$paymentStatus_for_new = 1;
					}
					if($kiosk_id == 10000){
						$kiosk_id1 = 0;
					}else{
						$kiosk_id1 = $kiosk_id;
					}
					
					$new_box_desc = "";
					if(array_key_exists('new_box_desc',$this->request->data)){
						$new_box_desc = $this->request->data['new_box_desc'];
					}
					
					$paymentDetailData_new = array(
							'product_receipt_id' => $recit_id,
							'payment_method' => $new_paymentMode,
							'amount' => $this->request->data['added_amount'],
							'payment_status' => $paymentStatus_for_new,
							'kiosk_id' => $kiosk_id1,
							'description'=>$new_box_desc,
							'agent_id' => $agent_id,
							//'created' => $recit_created
							   );
					//pr($paymentDetailData_new);die;
					$PaymentDetailTable->behaviors()->load('Timestamp');
					$entity_new = $PaymentDetailTable->newEntity(['validate' =>false]);
					$entity_patch = $PaymentDetailTable->patchEntity($entity_new,$paymentDetailData_new,['validate' =>false]);
					//pr($entity_patch);die;
					$yamini = $PaymentDetailTable->save($entity_patch);
					//pr($yamini);die;
					
					$desciption = "";
					if(array_key_exists('desc',$this->request->data)){
						$desciption = $this->request->data['desc'];
					}
					
					
					$old_amt = $this->request->data['old_amt'];
					$old_payment_data = array(
							'id' => $paymentId,
							'payment_method' => $paymentMode,
							'amount' => $old_amt,
							'payment_status' => $paymentStatus,
							'description'=>$desciption
							//'created' => $recit_created
							   );
					$PaymentDetailTable->behaviors()->load('Timestamp');
					$getID = $PaymentDetailTable->get($paymentId);
					$patchEntity = $PaymentDetailTable->patchEntity($getID,$old_payment_data,['validate' => false]);
					$PaymentDetailTable->save($patchEntity);
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
							'description'=>$desciption
							   );
				}
				if(empty($paymentDetailData)){
					$paymentDetailData = array(
							'id' => $paymentId,
							'payment_method' => $paymentMode,
							'payment_status' => $paymentStatus,
							'description'=>$desciption
							   );
				}
			}
			
			$PaymentDetailTable->behaviors()->load('Timestamp');
			$get_ID = $PaymentDetailTable->get($paymentId);
			$patch_Entity = $PaymentDetailTable->patchEntity($get_ID,$paymentDetailData,['validate' => false]);
            // pr($patch_Entity);die;
			if($PaymentDetailTable->save($patch_Entity)){
				$paymentID = $patch_Entity->id;
				$pmtMethod = $patch_Entity->payment_method;
				$receiptID = $patch_Entity->product_receipt_id;
				$user_id = $this->request->Session()->read('Auth.User.id');
				if($kiosk_id == 10000){
					$new_kiosk_id = 0;
				}else{
					$new_kiosk_id = $kiosk_id;
				}
				
				
				$logData = array(
								 'user_id'=>$user_id,
								 'pmt_id'=>(int)$paymentID,
								 'old_pmt_method'=>$oldPmtMethod,
								 'pmt_method'=>$pmtMethod,
								 'receipt_id'=>$receiptID,
								 'kiosk_id' => $new_kiosk_id,
								 //'modified'=>$modified,
								 //'created'=>$created,
								 //'memo'=>$paymentID,
								 'receipt_type' => 3,
								);
				//pr($logData);die;
				if($oldPmtMethod != $pmtMethod){
					$newLog = $this->PmtLogs->newEntity($logData,['validate'=>true]);
					$patchLog = $this->PmtLogs->patchEntity($newLog,$logData,['validate'=>true]);
					$this->PmtLogs->save($patchLog);
					$logid = $patchLog->id;
					$log_created = $patchLog->created;
					$first_name = $users[$user_id];
					if(empty($first_name)){
						$first_name = "Missing Name";
					}
					$str = date("d/m/Y h:i",strtotime($log_created)).", ".$first_name.", ".$oldPmtMethod."->".$pmtMethod;
					if(!empty($description)){
						$str.="||".$description;
					}
					
					$data = array("description" => $str);
					$pay_res = $PaymentDetailTable->get($paymentId);
					$pay_res = $PaymentDetailTable->patchEntity($pay_res,$data);
					$PaymentDetailTable->save($pay_res);
					
					$data = array("memo" => $desciption);
					$oldLog = $this->PmtLogs->get($logid);
					$oldLog = $this->PmtLogs->patchEntity($oldLog,$data,['validate'=>true]);
					$this->PmtLogs->save($oldLog);
					
				}
				
				if($this->request->session()->read('Auth.User.group_id') == ADMINISTRATORS || $this->request->session()->read('Auth.User.group_id') == FRANCHISE_OWNER){
					$kiosk_id_to_set = $this->get_kiosk_for_dr_invoice();
					$this->Flash->success("Payment method has been updated to {$paymentMode}");
					return $this->redirect(array('action'=>"dr_search?kiosk_id=$kiosk_id_to_set&date_type=invoice"));
				}else{
					$this->Flash->success("Payment method has been updated to {$paymentMode}");
					return $this->redirect(array('action'=>'dr_index'));
				}
				//$this->Flash->success("Payment method has been updated to {$paymentMode}");
				//return $this->redirect(array('action'=>'dr_index'));
			}
		}
		
		$this->set(compact('paymentData'));
	}
    public function drSearch($keyword = ''){
		$cust_hidden_id = 0;
		$this->check_dr5();
        $ProductReceipt_source = 't_product_receipts';
		$ProductReceiptTable = TableRegistry::get($ProductReceipt_source,[
																			'table' => $ProductReceipt_source,
																		]);
		$KioskProductSale_source = 't_kiosk_product_sales';
		$KioskProductSaleTable = TableRegistry::get($KioskProductSale_source,[
																				'table' => $KioskProductSale_source,
																			]);
		$PaymentDetail_source = 't_payment_details';
		$PaymentDetailTable = TableRegistry::get($PaymentDetail_source,[
																				'table' => $PaymentDetail_source,
																			]);
		//$this->ProductReceipt->setSource('t_product_receipts');
		//$this->KioskProductSale->setSource('t_kiosk_product_sales');
		//$this->PaymentDetail->setSource('t_payment_details');
		//pr($this->request->query);
		$conditionArr = array();
		$productreceiptArr = array();
		$settingArr = $this->setting;
        //pr($this->request);die;
		if(array_key_exists('payment_type',$this->request->query) &&
		   !empty($this->request->query['payment_type'])){
            //echo "payment typle";
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
		$invoiceSearchKeyword = "";
		if(array_key_exists('invoice_detail',$this->request->query) &&
		   !empty($this->request->query['invoice_detail'])){
            
			 
           $invoiceSearchKeyword = $this->request->query['invoice_detail']; 
			$this->set('invoiceSearchKeyword',$this->request->query['invoice_detail']);
		}
		
		if(array_key_exists('start_date',$this->request->query) &&
		   !empty($this->request->query['start_date'])){
			$this->set('start_date',$this->request->query['start_date']);
		}
		//pr($this->request);die;
		if(array_key_exists('date_type',$this->request->query)){
			$date_type = $this->request->query['date_type'];
		}
		if(array_key_exists('date-type',$this->request->query)){
			$date_type = $this->request->query['date-type'];
		}
		$this->set(compact('date_type'));
		if(array_key_exists('end_date',$this->request->query) &&
		   !empty($this->request->query['end_date'])){
			$this->set('end_date',$this->request->query['end_date']);
		}
		
		if(array_key_exists('start_date',$this->request->query) &&
		   array_key_exists('end_date',$this->request->query) &&
		   !empty($this->request->query['start_date']) &&
		   !empty($this->request->query['end_date'])){
			if(array_key_exists('date_type',$this->request->query)){
				$date_type = $this->request->query['date_type'];
				if($date_type == 'payment'){
					$conditionArr[] = array(
						"created >=" => date('Y-m-d', strtotime($this->request->query['start_date'])),
						"created <" => date('Y-m-d', strtotime($this->request->query['end_date']. ' +1 Days')),			
					       );
				}else{
					$conditionArr1 = array();
					$conditionArr1[] = array(
						"created >" => date('Y-m-d', strtotime($this->request->query['start_date'])),
						"created <" => date('Y-m-d', strtotime($this->request->query['end_date']. ' +1 Days')),			
					       );
					$Receipts_query = $ProductReceiptTable->find('list',array(
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
					$conditionArr['product_receipt_id IN'] = $Receipts;
				}
			}else{ 
				$conditionArr[] = array(
						"created >" => date('Y-m-d', strtotime($this->request->query['start_date'])),
						"created <" => date('Y-m-d', strtotime($this->request->query['end_date']. ' +1 Days')),			
					       );
			}
			
		}
		
		
		//pr($conditionArr);die;
		$customerResult = array();
		if(array_key_exists('search_kw',$this->request->query) &&
		   !empty($this->request->query['search_kw'])){
			 $textKeyword = $this->request->query['search_kw'];
			if(!empty($textKeyword) && array_key_exists('invoice_detail',$this->request->query)){
				if($invoiceSearchKeyword=="receipt_number"){
                   
					$conditionArr['product_receipt_id'] =  (int)$textKeyword;
				}elseif($invoiceSearchKeyword=="business"){
                   $customerIds_query = $this->Customers->find('list',
                                                         ['conditions'=>[
																"OR" => [
															"LOWER(`Customers`.`business`) like" => strtolower("%$textKeyword%"),
															"LOWER(`Customers`.`fname`) like" => strtolower("%$textKeyword%"),
											"LOWER(`Customers`.`business`) like" => strtolower("%$textKeyword%")												    ]
												    ],
                                                                'keyField' => 'id',
                                                                'valueField' => 'id',
                                                         ]);
                    //pr($customerIds_query);die;
					$customerIds_query = $customerIds_query->hydrate(false);
                    if(!empty($customerIds_query)){
                        $customerIds = $customerIds_query->toArray();
                    }else{
						$customerIds  = array();
					}
					//pr($customerIds);die;
                    
                    
                    $conditionArr['product_receipt_id IN'] = 0;
					if(count($customerIds) > 0){
                       
						$searchCriteria['customer_id IN'] = $customerIds;
						if(array_key_exists('start_date',$this->request->query) &&
							array_key_exists('end_date',$this->request->query) &&
							!empty($this->request->query['start_date']) &&
							!empty($this->request->query['end_date'])){
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
						$cutomerReceipts_query = $ProductReceiptTable->find('all',array('fields' => array('id'),
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
						$conditionArr['product_receipt_id IN'] = 0;
						if( count($cutomerReceipts) ){
							//echo $cutomerReceipts['ProductReceipt']['id'];
							foreach($cutomerReceipts as $cutomerReceipt){
								$receiptIDs[] = $cutomerReceipt['id'];
							}
                            if(empty($receiptIDs)){
                                $receiptIDs = array('0'=>null);
                            }
							$conditionArr['product_receipt_id IN'] = $receiptIDs;
						}
                        
					}
                   
				}elseif($invoiceSearchKeyword=="customer_id"){//invoice_detail
                   	$customerID =  (int)$textKeyword;
					$cust_hidden_id = $customerID;
					$searchCriteria['customer_id'] = $customerID;
					if(array_key_exists('start_date',$this->request->query) &&
						array_key_exists('end_date',$this->request->query) &&
						!empty($this->request->query['start_date']) &&
						!empty($this->request->query['end_date'])){
						$date_type = $this->request->query['date_type'];
						if($date_type == 'payment'){
							
						}else{
							$searchCriteria[] = array(
									 "created >" => date('Y-m-d', strtotime($this->request->query['start_date'])),
									 "created <" => date('Y-m-d', strtotime($this->request->query['end_date']. ' +1 Days')),			
										);	
						}
					 }
					$cutomerReceipts_query = $ProductReceiptTable->find('all',array('fields' => array('id'),
															'conditions' => $searchCriteria,
															'recursive' => -1));
                    $cutomerReceipts_query = $cutomerReceipts_query->hydrate(false);
                    if(!empty($cutomerReceipts_query)){
                        $cutomerReceipts = $cutomerReceipts_query->toArray();
                    }else{
                        $cutomerReceipts = array();
                    }
					$receiptIDs = array();
					$conditionArr['product_receipt_id IN'] = 0;
                  //  pr($cutomerReceipts);die;
					if( count($cutomerReceipts) ){
						//echo $cutomerReceipts['ProductReceipt']['id'];
						foreach($cutomerReceipts as $cutomerReceipt){
							$receiptIDs[] = $cutomerReceipt['id'];
						}
                        if(empty($receiptIDs)){
                            $receiptIDs = array('0'=>null);
                        }
						$conditionArr['product_receipt_id IN'] = $receiptIDs;
					}
					
				}
				$this->set('textKeyword',$this->request->query['search_kw']);
			}
		}
		
		$agent_id = 0;
		if(array_key_exists('agent_id',$this->request->query) && !empty($this->request->query['agent_id'])){
			$agent_id = $this->request->query['agent_id'];
			$agent_cust_res = $this->Customers->find("list",['conditions' => [
														   "agent_id" => $agent_id,
														   ],
															'keyField' => "id",
															"valueField" => "agent_id",
															])->toArray();
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
				$cutomerReceipts_query = $ProductReceiptTable->find('all',array('fields' => array('id'),
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
				$conditionArr['product_receipt_id IN'] = 0;
				if( count($cutomerReceipts) ){
					//echo $cutomerReceipts['ProductReceipt']['id'];
					foreach($cutomerReceipts as $cutomerReceipt){
						$receiptIDs[] = $cutomerReceipt['id'];
					}
					if(empty($receiptIDs)){
						$receiptIDs = array('0'=>null);
					}
					$conditionArr['product_receipt_id IN'] = $receiptIDs;
				}
						 
			}
			//$conditionArr['agent_id'] = $agent_id;
		}
		$this->set(compact('agent_id'));
		//pr($conditionArr);die;
		
		if(array_key_exists('kiosk_id',$this->request->query)){
           $kskId = $this->request->query['kiosk_id'];
			 
		}elseif(array_key_exists('kiosk-id',$this->request->query)){
			$kskId = $this->request->query['kiosk-id'];
		}else{
			$kskId =  $this->request->Session()->read('kiosk_id');
		}
		if(empty($kskId) || $kskId == 10000){
			$kskId = 0;
		}
		$this->set('kiosk',$kskId);
        $conditionArr['kiosk_id'] = $kskId;
	    //echo $kskId;die;
        //  pr($conditionArr);die;
		$listPaymentDet_query = $PaymentDetailTable->find('all',array(
                                                                'fields'=>array('product_receipt_id','amount'),
                                                                'conditions'=>$conditionArr
                                                                )
                                                    );
        
        $listPaymentDet_query = $listPaymentDet_query->hydrate(false);
		if(!empty($listPaymentDet_query)){
			$listPaymentDet = $listPaymentDet_query->toArray();
		}else{
			$listPaymentDet = array();
		}
       
		$listPayment = array();
		foreach($listPaymentDet as $lp => $list_payment){
			if(array_key_exists($list_payment['product_receipt_id'],$listPayment)){
				$listPayment[$list_payment['product_receipt_id']]+= $list_payment['amount'];
			}else{
				$listPayment[$list_payment['product_receipt_id']] = $list_payment['amount'];
			}
		}
        
		$lptotalPaymentAmount = 0;
		$lpgrandNetAmount = 0;
		$totalBillCost = 0;
		$totalCost = 0;
		if(count($listPayment)){
			$receiptIdArr = array();
			$productReceiptDetail = array();
			if($listPayment){
				$receiptIdArr = array_keys($listPayment);
			}
          //  pr($receiptIdArr);die;
            if(empty($receiptIdArr)){
                $receiptIdArr = array('0'=>null);
            }
					//code for getting cost price of products*******************
					$recit_ids = array();
                 //   pr($receiptIdArr);die;
                 
					if(count($receiptIdArr)){
						//pr($receiptIdArr);
                         $productQttArr_query = $KioskProductSaleTable->find('all', array(
                                                                                   'conditions' => array(
                                                                                                         'product_receipt_id IN' => $receiptIdArr,
                                                                                                         'kiosk_id'=>$kskId
                                                                                                         ),
                                                                                   'fields' => array('product_id','quantity','product_receipt_id'),
                                                                                   'recursive' => -1));
                        
                         $productQttArr_query = $productQttArr_query->hydrate(false);
                         
                        if(!empty($productQttArr_query)){
                            $productQttArr = $productQttArr_query->toArray();
                        }else{
                            $productQttArr = array();
                        }
						$receiptIdDetail = array();
						$productIdsArr = array();
						foreach($productQttArr as $key => $productQtt){
							$recit_ids[$productQtt['product_receipt_id']] = $productQtt['product_receipt_id'];
							//$receiptIdDetail[$productQtt['KioskProductSale']['product_receipt_id']][] = $productQtt['KioskProductSale'];
							$productIdsArr[$productQtt['product_id']] = $productQtt['product_id'];
						}
                        if(empty($productIdsArr)){
                            $productIdsArr = array('0'=>null);
                        }
                         if(empty($recit_ids)){
                            $recit_ids = array('0'=>null);
                        }

                        $costPriceList_query = $this->Products->find('list',[
                                                    'keyField' => 'id',
                                                     'valueField' => 'cost_price',
                                                    'conditions' => ['Products.id IN' => $productIdsArr]
                                                 ]
                                        );
                        
						if(!empty($costPriceList_query)){
                            $costPriceList = $costPriceList_query->toArray();
                       }
                     // pr($productQttArr);
						foreach($productQttArr as $key => $productQtt){
							if(!array_key_exists($productQtt['product_id'],$costPriceList)){continue;}
							$costPrice = $costPriceList[$productQtt['product_id']]*$productQtt['quantity'];
							$totalCost+=$costPrice;
						}
					}
					//*********************till here
			if(count($listPayment) && count($conditionArr)){
               	$productReceiptDetail_query = $ProductReceiptTable->find('all',array('conditions'=>array('id IN'=>$receiptIdArr,'kiosk_id' => $kskId),
                                                                               'fields'=>array('id','vat','status','bill_cost','created'),
                                                                               'recursive'=>-1
                                                                               )
                                                                   );
                $productReceiptDetail_query = $productReceiptDetail_query->hydrate(false);
                if(!empty($productReceiptDetail_query)){
                    $productReceiptDetail = $productReceiptDetail_query->toArray();
                }else{
                    $productReceiptDetail = array();
                }
			}else{
				$productReceiptDetail_query = $ProductReceiptTable->find('all',array(
                                                                               'fields'=>array('id','vat','status','bill_cost','created'),
                                                                               'recursive'=>-1
                                                                               )
                                                                   );
                $productReceiptDetail_query = $productReceiptDetail_query->hydrate(false);
                if(!empty($productReceiptDetail_query)){
                    $productReceiptDetail = $productReceiptDetail_query->toArray();
                }else{
                    $productReceiptDetail = array();
                }
                
			}
           // pr($productReceiptDetail);die;
			$createdArr = array();
			foreach($productReceiptDetail as $key=>$productReceiptDta){
				//showing the data with product receipt status == 0 ie. the payment went through and data saved properly
				if($productReceiptDta['status']==0){
					$paymentAmount = 0;
					$createdArr[$productReceiptDta['id']] = $productReceiptDta['created'];
					$totalBillCost+=floatval($productReceiptDta['bill_cost']);
					if(array_key_exists($productReceiptDta['id'],$listPayment)){
						$paymentAmount = $listPayment[$productReceiptDta['id']];
					}
					$lptotalPaymentAmount+=floatval($paymentAmount);
					$vatPercentage = $productReceiptDta['vat']/100;
					$netAmount = $paymentAmount/(1+$vatPercentage);
					$lpgrandNetAmount+=floatval($netAmount);
				}
			}
		}
		
		/*$lptotalPaymentAmount = 0;
		$lpgrandNetAmount = 0;
		foreach($productReceiptDetail as $key=>$productReceiptDta){
			//showing the data with product receipt status == 0 ie. the payment went through and data saved properly
			if($productReceiptDta['ProductReceipt']['status']==0){
				$totalBillCost+=floatval($productReceiptDta['ProductReceipt']['bill_cost']);
                $receiptIdArr[$productReceiptDta['ProductReceipt']['id']] = $productReceiptDta['ProductReceipt']['id'];//capturing the receipt ids
				$paymentAmount = $productReceiptDta['ProductReceipt']['bill_amount'];
				$lptotalPaymentAmount+=floatval($paymentAmount);
				$vatPercentage = $productReceiptDta['ProductReceipt']['vat']/100;
				$netAmount = $paymentAmount/(1+$vatPercentage);
				$lpgrandNetAmount+=$netAmount;
			}
		}*/
		
		$lptotalVat = $lptotalPaymentAmount - $lpgrandNetAmount;
		$lptotalVat = $lptotalVat;
		$conditionArr['kiosk_id'] = $kskId;
        //echo $lptotalPaymentAmount;die;
		$this->set(compact('lptotalPaymentAmount','lpgrandNetAmount','lptotalVat','totalCost'));
		//pr($conditionArr);die;
        
        $this->paginate = [
							'conditions' => $conditionArr,
							'order' => ['product_receipt_id DESC'],
							'limit' => 50,
						  ];
        
		$productReceipts_query = $this->paginate($PaymentDetailTable);
		if(!empty($productReceipts_query)){
			$productReceipts = $productReceipts_query->toArray();
		}else{
			$productReceipts = array();
		}
		$conditionArr_to_use = $conditionArr;
		
		if(array_key_exists('product_receipt_id IN',$conditionArr_to_use)){
			$s_res = $conditionArr_to_use['product_receipt_id IN'];
			//$conditionArr_to_use['id IN'] = $s_res;
			unset($conditionArr_to_use['product_receipt_id IN']);
			
		}
		if(array_key_exists('product_receipt_id',$conditionArr_to_use)){
			$s_res = $conditionArr_to_use['product_receipt_id'];
			//$conditionArr_to_use['id IN'] = $s_res;
			unset($conditionArr_to_use['product_receipt_id']);
		}
		
		if(array_key_exists('payment_method like ',$conditionArr_to_use)){
			unset($conditionArr_to_use['payment_method like ']);
		}
		
		$pay_detail = $PaymentDetailTable->find('all',array('conditions' => $conditionArr))->toArray();
		if(!empty($pay_detail)){
			foreach($pay_detail as $key => $val){
				$conditionArr_to_use['id IN'][] = $val->product_receipt_id;
			}
			//pr($conditionArr_to_use);die;
			$fixed_cost_sum_query = $ProductReceiptTable->find('all',array('conditions' => $conditionArr_to_use));
			$fixed_cost_sum_query
						->select(['fixed_cost' => $fixed_cost_sum_query->func()->sum('bill_cost')]);
			
			$fixed_cost_sum_query = $fixed_cost_sum_query->hydrate(false);
			if(!empty($fixed_cost_sum_query)){
				$fixed_cost_sum = $fixed_cost_sum_query->first(false);
			}else{
				$fixed_cost_sum = array();
			}	
		}else{
			$fixed_cost_sum['fixed_cost'] = 0;
		}
		
		 $product_receiptId = array();
		if(!empty($productReceipts)){
			foreach($productReceipts as $productReceipts_value){
			   $product_receiptId[] = $productReceipts_value['product_receipt_id'];
			}
		}
		
        if(empty($product_receiptId)){
            $product_receiptId = array('0' =>null);
        }
       
            $product_receipt_data_query = $ProductReceiptTable->find('all',[
																			'conditions' => ['id IN' => $product_receiptId]
																		]
												   );
		   //pr($product_receipt_data_query);die;
			   $product_receipt_data_query = $product_receipt_data_query->hydrate(false);
			   if(!empty($product_receipt_data_query)){
				   $product_receipt_data = $product_receipt_data_query->toArray();
			   }else{
				   $product_receipt_data = array();
			   }
		 //pr($product_receipt_data);die;
		 
            foreach($product_receipt_data as $receiptDetail){
                //pr($receiptDetail);die;
				if($invoiceSearchKeyword=="receipt_number"){
					$cust_hidden_id = $receiptDetail['customer_id'];
				}
                $customerIdArr[] = $receiptDetail['customer_id'];
                $productreceiptArr[$receiptDetail['id']] = $receiptDetail;
                
            }
            $this->set(compact('productreceiptArr'));
            if(empty($customerIdArr)){
                $customerIdArr = array(0 => null);
            }
             $customerBusiness_query = $this->Customers->find('list',[
                                                    'keyField' => 'id',
                                                     'valueField' => 'business',
                                                   'conditions' =>['Customers.id IN'=>array_unique($customerIdArr)],
                                                 ]
                                        ); 
 
            if(!empty($customerBusiness_query)){
                $customerBusiness = $customerBusiness_query->toArray();
            }else{
                $customerBusiness = array();
            }
       
		if($this->request->session()->read('Auth.User.group_id') == ADMINISTRATORS || $this->request->session()->read('Auth.User.group_id') == FRANCHISE_OWNER){
			//$session_id = $this->Session->id();
			$user_id = $this->request->Session()->read('Auth.User.id');
			if($kskId == ""){
				$kskId = 10000;
			}
			$data_arr = array('kiosk_id' => $kskId);
			$jsondata = json_encode($data_arr);
			$this->loadModel('UserSettings');
			$res_query = $this->UserSettings->find('all',array('conditions' => array(
																		 'user_id' => $user_id,
																		 'setting_name' => "dr_search",
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
									'setting_name' => "dr_search",
									'data' => $jsondata
									);
				$entity = $this->UserSettings->get($userSettingid);
				$entity = $this->UserSettings->patchEntity($entity,$data_to_save);
				$this->UserSettings->save($entity);
			}else{
				$data_to_save = array(
									'user_id' => $user_id,
									//'user_session_key' => $session_id,
									'setting_name' => "dr_search",
									'data' => $jsondata
									);
				$entity = $this->UserSettings->newEntity();
				$entity = $this->UserSettings->patchEntity($entity,$data_to_save);
				$this->UserSettings->save($entity);
			}
		}
		$kiosk_list_query = $this->Kiosks->find('list');
		$kiosk_list_query = $kiosk_list_query->hydrate(false);
		if(!empty($kiosk_list_query)){
			$kiosk_list = $kiosk_list_query->toArray();
		}else{
			$kiosk_list = array();
		}
		
		$agents_query = $this->Agents->find('list');
		$agents_query = $agents_query->hydrate(false);
		
		if(!empty($agents_query)){
			$agents = $agents_query->toArray();
		}
		$agents[0] = "Select Acc manager";
		ksort($agents);
		
		$this->set(compact('productReceipts','customerBusiness','totalAmount','totalBillCost','createdArr','recit_ids','kiosk_list','fixed_cost_sum','agents','cust_hidden_id'));
		//$this->layout = 'default';
		$this->render('dr_index');
	}
	
	public function orgToSpecial($id,$passed_kiosk_id = ""){
		$this->check_dr5();
		if(!empty($passed_kiosk_id)){
			if($passed_kiosk_id == 10000){
				$kiosk_id = "";
			}else{
				$kiosk_id = $passed_kiosk_id;
			}
		}else{
			$kiosk_id = $this->request->Session()->read('kiosk_id');
		}
		//$kiosk_id = $this->request->Session()->read('kiosk_id');
		if(!empty($kiosk_id)){
			$recipt_source = "kiosk_{$kiosk_id}_product_receipts";
			$KioskProductSale_Source = "kiosk_{$kiosk_id}_product_sales";
			$PaymentDetail_Source = "kiosk_{$kiosk_id}_payment_details";
		}else{
			$recipt_source = "product_receipts";
			$KioskProductSale_Source = "kiosk_product_sales";
			$PaymentDetail_Source = "payment_details";
		}
		
		$ProductReceiptTable = TableRegistry::get($recipt_source,[
																			'table' => $recipt_source,
																		]);
		$KioskProductSaleTable = TableRegistry::get($KioskProductSale_Source,[
																			'table' => $KioskProductSale_Source,
																		]);
		$PaymentDetailTable = TableRegistry::get($PaymentDetail_Source,[
																			'table' => $PaymentDetail_Source,
																		]); 
		
		$res_query = $ProductReceiptTable->find('all',array(
												'conditions' => array(
													'id' => $id
												)
												));
		$res_query = $res_query->hydrate(false);
		if(!empty($res_query)){
			$res = $res_query->first();
		}else{
			$res = array();
		}
		if(!empty($res)){
			$customer_id = $res['customer_id'];
		}
		$cust_query = $this->Customers->find('all',[
									  'conditions' => array('id' => $customer_id)
									  ]);
		$cust_query = $cust_query->hydrate(false);
		if(!empty($cust_query)){
			$cust_result = $cust_query->first();
		}else{
			$cust_result = array(); 
		}
		
		$payment_query = $PaymentDetailTable->find('all',[
										 'conditions' => [
											'product_receipt_id' => $id
										 ]
										 ]);
		$payment_query = $payment_query->hydrate(false);
		if(!empty($payment_query)){
			$payment_res = $payment_query->toArray();
		}else{
			$payment_res = array();
		}
		
		$sales_query = $KioskProductSaleTable->find('all',[
											'conditions' => [
												'product_receipt_id' => $id
											]
											]);
		$sales_query = $sales_query->hydrate(false);
		if(!empty($sales_query)){
			$sales_res = $sales_query->toArray();
		}else{
			$sales_res = array();
		}
		
		$country = $cust_result['country'];
		if($country == "OTH"){
				if(!empty($res)){
					$product_recipt_data1 = $product_recipt_data = $res;
					$payment_detail_data1 = $payment_detail_data = $payment_res;
					$kiosk_product_sale_data1 = $kiosk_product_sale_data = $sales_res;
					$created = $res['created'];
					$database_date = strtotime(date('d-m-y',strtotime($created)));
					$today_date = strtotime(date('d-m-y'));
					$loggedInUser = $this->request->session()->read('Auth.User.username');
					if ($loggedInUser != SPL_PRIVILEGE_USER){ //QUOT_USER_PREFIX."inderjit"
						if($today_date != $database_date){
							//echo'hi';die;
							$this->Flash->error(__("Invoice can be migrated on same day only"));
							if($this->request->session()->read('Auth.User.group_id') == ADMINISTRATORS || $this->request->session()->read('Auth.User.group_id') == FRANCHISE_OWNER){
								$kiosk_id_to_set = $this->get_kiosk_for_invoice();
								return $this->redirect(array('action'=>"search?kiosk_id=$kiosk_id_to_set&date_type=invoice"));
							}else{
								return $this->redirect(array('action'=>'index'));
							}
						}
					}
					
					$recipt = "t_product_receipts";
					$kiosk_product_sale = "t_kiosk_product_sales";
					$payment_table = "t_payment_details";
					$products = "products";
				
					$ProductReceiptTable = TableRegistry::get($recipt,[
																			'table' => $recipt,
																		]);
					$KioskProductSaleTable = TableRegistry::get($kiosk_product_sale,[
																						'table' => $kiosk_product_sale,
																					]);
					$PaymentDetailTable = TableRegistry::get($payment_table,[
																						'table' => $payment_table,
																					]); 
					
					if(!empty($product_recipt_data)){
						unset($product_recipt_data['id']);
						unset($product_recipt_data['created']);
						unset($product_recipt_data['modified']);
						if(!empty($kiosk_id)){
							$product_recipt_data['kiosk_id'] = $kiosk_id;
						}
						
						$ProductReceiptTable->behaviors()->load('Timestamp');
						$recipt_patch_entity = $ProductReceiptTable->newEntity($product_recipt_data);
						$recipt_patch_entity = $ProductReceiptTable->patchEntity($recipt_patch_entity,$product_recipt_data);
						$ProductReceiptTable->save($recipt_patch_entity);
						$recipt_id = $recipt_patch_entity->id;
					}
					if(!empty($payment_detail_data)){
						foreach($payment_detail_data as $key => $value){
							unset($value['id']);
							unset($value['created']);
							unset($value['modified']);
							if(!empty($kiosk_id)){
								$value['kiosk_id'] = $kiosk_id;
							}
							$value['product_receipt_id'] = $recipt_id;
							$PaymentDetailTable->behaviors()->load('Timestamp');
							$payment_patch_entity = $PaymentDetailTable->newEntity($value);
							$payment_patch_entity = $PaymentDetailTable->patchEntity($payment_patch_entity,$value);
							$PaymentDetailTable->save($payment_patch_entity);
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
							$value1['product_receipt_id'] = $recipt_id;
							$KioskProductSaleTable->behaviors()->load('Timestamp');
							$sale_entity = $KioskProductSaleTable->newEntity($value1);
							$sale_entity = $KioskProductSaleTable->patchEntity($sale_entity,$value1);
							$KioskProductSaleTable->save($sale_entity);
						}
					}
				}
				if(!empty($kiosk_id)){
					$recipt = "kiosk_{$kiosk_id}_product_receipts";
					$kiosk_product_sale = "kiosk_{$kiosk_id}_product_sales";
					$payment_table = "kiosk_{$kiosk_id}_payment_details";
					//$products = "products";
					
					//$this->ProductReceipt->setSource("kiosk_{$kiosk_id}_product_receipts");
					//$this->KioskProductSale->setSource("kiosk_{$kiosk_id}_product_sales");
					//$this->PaymentDetail->setSource("kiosk_{$kiosk_id}_payment_details");
				}else{
					$recipt = "product_receipts";
					$kiosk_product_sale = "kiosk_product_sales";
					$payment_table = "payment_details";
					//$products = "products";
					
					
					//$this->ProductReceipt->setSource("product_receipts");
					//$this->KioskProductSale->setSource("kiosk_product_sales");
					//$this->PaymentDetail->setSource("payment_details");
				}
				$ProductReceiptTable = TableRegistry::get($recipt,[
																		'table' => $recipt,
																	]);
				$KioskProductSaleTable = TableRegistry::get($kiosk_product_sale,[
																					'table' => $kiosk_product_sale,
																				]);
				$PaymentDetailTable = TableRegistry::get($payment_table,[
																			'table' => $payment_table,
																		]);
			
				$recipt_update_query = "UPDATE {$recipt} SET bulk_discount = 0,bill_cost = 0.10,bill_amount=0.25,orig_bill_amount=0.25 WHERE id = $id";
				
				$conn = ConnectionManager::get('default');
				$stmt = $conn->execute($recipt_update_query); 
				
				if(count($kiosk_product_sale_data1) > 1){
					$first_entry_id = $kiosk_product_sale_data1[0]['id'];
					$sale_update_query = "UPDATE {$kiosk_product_sale} SET product_id = 7224,discount=0,quantity = 1,cost_price=0.10,sale_price=0.25 WHERE id = $first_entry_id";
					$conn = ConnectionManager::get('default');
					$stmt = $conn->execute($sale_update_query); 
					unset($kiosk_product_sale_data1[0]);
					foreach($kiosk_product_sale_data1 as $s => $raw_data){
						$delete_id  = $raw_data['id'];
						$sale_delete_query = "DELETE FROM {$kiosk_product_sale} WHERE id = $delete_id";
						
						$conn = ConnectionManager::get('default');
						$stmt = $conn->execute($sale_delete_query); 
					}
				}else{
					$first_entry_id = $kiosk_product_sale_data1[0]['id'];
					$sale_update_query = "UPDATE {$kiosk_product_sale} SET product_id = 7224,discount=0,quantity = 1,cost_price=0.10,sale_price=0.25 WHERE id = $first_entry_id";
					
					$conn = ConnectionManager::get('default');
					$stmt = $conn->execute($sale_update_query); 
					
				}
				
				if(count($payment_detail_data1) > 1){
					$paymentfirst_entry_id = $payment_detail_data1[0]['id'];
					$payment_update_query = "UPDATE {$payment_table} SET amount=0.25,payment_method='Cash' WHERE id = $paymentfirst_entry_id";
					
					$conn = ConnectionManager::get('default');
					$stmt = $conn->execute($payment_update_query);
					
					unset($payment_detail_data1[0]);
					foreach($payment_detail_data1 as $p => $p_raw_data){
						$p_delete_id  = $p_raw_data['id'];
						$payment_delete_query = "DELETE FROM {$payment_table} WHERE id = $p_delete_id";
						$conn = ConnectionManager::get('default');
						$stmt = $conn->execute($payment_delete_query);
						
					}
				}else{
					$paymentfirst_entry_id = $payment_detail_data1[0]['id'];
					$payment_update_query = "UPDATE {$payment_table} SET amount=0.25,payment_method='Cash' WHERE id = $paymentfirst_entry_id";
					
					$conn = ConnectionManager::get('default');
					$stmt = $conn->execute($payment_update_query);

				}
				//Start block: cron dashboard code
				if(strtotime(date("y-m-d",strtotime($res['created']))) != strtotime(date("y-m-d"))){
					$this->loadModel('DashboardData');
					
					if($kiosk_id == 10000 || $kiosk_id == ""){
						$kioskid = 0;
					}else{
						$kioskid = $kiosk_id;
					}
					
					$conditionArr = array();
					$conditionArr[] = array(
								  "date >=" => date('Y-m-d', strtotime($res['created'])),
								  "date <" => date('Y-m-d', strtotime($res['created']. ' +1 Days')),			
									 );
					  
					$conditionArr['kiosk_id'] = $kioskid;
					  
					$dashboardData_query = $this->DashboardData->find('all',[
														'conditions'=>$conditionArr,
														'order'=>['id desc'],
														//'limit'=>2
													]
												);
					//->toArray();
					$dashboardData_query = $dashboardData_query->hydrate(false);
					if(!empty($dashboardData_query)){
						$dashboardData = $dashboardData_query->toArray();
					}else{
						$dashboardData = array();
					}
					$new_dash_data = $dashboardData;
					if(!empty($dashboardData)){
						$count = count($dashboardData);
						foreach($dashboardData as $daash_key => $dash_value){
							if($daash_key > 1){
								continue;
							}
							if($count > 3){
								
							}else{
								unset($new_dash_data[$daash_key]['id']);
							}
							unset($new_dash_data[$daash_key]['created']);
							unset($new_dash_data[$daash_key]['modified']);
							
							
							
							//pr($dash_value);
							if(array_key_exists("product_sale",$dash_value)){
								$new_dash_data[$daash_key]['product_sale'] = ((float)$dash_value['product_sale'] - (float)$prv_amt)+0.20;
							}
							if(array_key_exists("product_sale_desc",$dash_value)){
								$product_sale_desc = unserialize($dash_value['product_sale_desc']);
								$credit_desc = unserialize($dash_value['net_credit_desc']);
								
								$net_card_desc = unserialize($dash_value['net_card_desc']);
								$cash_in_hand_desc = unserialize($dash_value['cash_in_hand_desc']);
								$net_bnk_tnsfer_desc = unserialize($dash_value['net_bnk_tnsfer_desc']);
								$net_cheque_payment_desc = unserialize($dash_value['net_cheque_payment_desc']);
								
								foreach($payment_res as $pay_key => $pay_detail){
									$pay_method = $pay_detail['payment_method'];
									$pay_amt = $pay_detail['amount'];
									if($pay_method == "On Credit"){
										$product_sale_desc['credit'] = (float)$product_sale_desc['credit'] - (float)$pay_amt;
										$credit_desc[0] = (float)$credit_desc[0] - (float)$pay_amt;
										$new_dash_data[$daash_key]['net_credit'] = (float)$dash_value['net_credit'] - (float)$pay_amt;
									}
									if($pay_method == "Cash"){
										$product_sale_desc['cash'] = (float)$product_sale_desc['cash'] - (float)$pay_amt;
										$new_dash_data[$daash_key]['cash_in_hand'] = (float)$dash_value['cash_in_hand'] - (float)$pay_amt;
										$cash_in_hand_desc['sale']['Product'] = (float)$cash_in_hand_desc['sale']['Product']-(float)$pay_amt;
									}
									if($pay_method == "Card"){
										$net_card_desc['Product'] = (float)$net_card_desc['Product'] - (float)$pay_amt;
										$product_sale_desc['card'] = (float)$product_sale_desc['card'] - (float)$pay_amt;
										$new_dash_data[$daash_key]['net_card'] = (float)$dash_value['net_card'] - (float)$pay_amt;
									}
									if($pay_method == "Cheque"){
										$net_cheque_payment_desc[0] = (float)$net_cheque_payment_desc[0] - (float)$pay_amt;
										$product_sale_desc['cheque'] = (float)$product_sale_desc['cheque'] - (float)$pay_amt;
										$new_dash_data[$daash_key]['net_cheque_payment'] = (float)$dash_value['net_cheque_payment'] - (float)$pay_amt;
									}
									if($pay_method == "Bank Transfer"){
										$net_bnk_tnsfer_desc[0] = (float)$net_bnk_tnsfer_desc[0] - (float)$pay_amt;
										$product_sale_desc['bank_transfer'] = (float)$product_sale_desc['bank_transfer'] - (float)$pay_amt;
										$new_dash_data[$daash_key]['net_bnk_tnsfer'] = (float)$dash_value['net_bnk_tnsfer'] - (float)$pay_amt;
									}
								}
								
								$new_dash_data[$daash_key]['cash_in_hand'] = $new_dash_data[$daash_key]['cash_in_hand'] + 0.25;
								
								$cash_in_hand_desc['sale']['Product'] = $cash_in_hand_desc['sale']['Product'] + 0.25;
								$cash_in_hand_desc_new = serialize($cash_in_hand_desc);
								$new_dash_data[$daash_key]['cash_in_hand_desc'] = $cash_in_hand_desc_new;
								
								
								$credit_desc_new = serialize($credit_desc);
								$new_dash_data[$daash_key]['net_credit_desc'] = $credit_desc_new;
								
								$net_cheque_payment_desc_new = serialize($net_cheque_payment_desc);
								$new_dash_data[$daash_key]['net_cheque_payment_desc'] = $net_cheque_payment_desc_new;
								
								$net_bnk_tnsfer_desc_new = serialize($net_bnk_tnsfer_desc);
								$new_dash_data[$daash_key]['net_bnk_tnsfer_desc'] = $net_bnk_tnsfer_desc_new;
								
								$net_card_desc_new = serialize($net_card_desc);
								$new_dash_data[$daash_key]['net_card_desc'] = $net_card_desc_new;
								
								
								
								
								$product_sale_desc['cash'] = (float)$product_sale_desc['cash'] + 0.25;
								$product_sale_desc_new = serialize($product_sale_desc);
								$new_dash_data[$daash_key]['product_sale_desc'] = $product_sale_desc_new;
							}
							if(array_key_exists("total_sale",$dash_value)){
								$new_dash_data[$daash_key]['total_sale'] = ((float)$dash_value['total_sale'] - (float)$prv_amt)+0.25;
							}
							if(array_key_exists("net_cash",$dash_value)){
								$new_dash_data[$daash_key]['net_cash'] = ((float)$dash_value['net_cash'] - (float)$prv_amt)+0.25;
							}
							if(array_key_exists("net_sale",$dash_value)){
								$new_dash_data[$daash_key]['net_sale'] = ((float)$dash_value['net_sale'] - (float)$prv_amt)+0.25;
							}
							
						}
					}
					//pr($new_dash_data);die;
					if(!empty($new_dash_data)){
						foreach($new_dash_data as $new_key => $new_val){
							if(array_key_exists('id',$new_val)){
								$new_entity = $this->DashboardData->get($new_val['id']);
							}else{
								$new_entity = $this->DashboardData->newEntity();	
							}
							$new_entity = $this->DashboardData->patchEntity($new_entity,$new_val);
							$this->DashboardData->behaviors()->load('Timestamp');
							$this->DashboardData->save($new_entity);
						}
					}	
				}
				//End block: cron dashboard code
				
				if($this->request->session()->read('Auth.User.group_id') == ADMINISTRATORS || $this->request->session()->read('Auth.User.group_id') == FRANCHISE_OWNER){
					$kiosk_id_to_set = $this->get_kiosk_for_invoice();
					$this->Flash->success(__("Invoice with Invoice id {$id} changed to special invoice"));
					return $this->redirect(array('action'=>"search?kiosk_id=$kiosk_id_to_set&date_type=invoice"));
				}else{
					$this->Flash->success(__("Invoice with Invoice id {$id} changed to special invoice"));
					return $this->redirect(array('action'=>'index'));
				}
				//$this->Flash->success(__("Invoice with Invoice id {$id} changed to special invoice"));
				//return $this->redirect(array('action'=>'dr_index'));
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
					$amt = $res['orig_bill_amount'];
					$vat = $res['vat'];
					if(!empty($vat)){
						$final_amt = $amt/(1+($vat/100));
					}else{
						$final_amt = $amt;
					}
					$this->set(compact('res','final_amt','payment_res'));
					$this->Flash->error(__("amount is not matching"));
					return $this->redirect(array('action'=>'orgToSpecial',$id));
				}else{
					$created = $res['created'];
					$prv_amt = $res['orig_bill_amount'];
					$database_date = strtotime(date('d-m-y',strtotime($created)));
					$today_date = strtotime(date('d-m-y'));
					$loggedInUser = $this->request->session()->read('Auth.User.username');
					if ($loggedInUser != SPL_PRIVILEGE_USER){   //QUOT_USER_PREFIX."inderjit"
						if($today_date != $database_date){
							$this->Flash->error(__("Invoice can be migrated on same day only"));
							return $this->redirect(array('action'=>'index'));
						}
					}
					
					$old_payment_res = $payment_res;
					$payment_data = $this->request->data['payment'];
					foreach($payment_res as $s_key => $s_value){
						if(array_key_exists($s_value['id'],$payment_data)){
							$new_amt = $payment_data[$s_value['id']];
							$payment_res[$s_key]['amount'] = $new_amt;
						}
					}
					$res['orig_bill_amount'] = $sale_amt;
					$res['bill_amount'] = $sale_amt;
					
					if(!empty($res)){
						$product_recipt_data1 = $product_recipt_data = $res;
						$payment_detail_data1 = $payment_detail_data = $payment_res;
						$kiosk_product_sale_data1 = $kiosk_product_sale_data = $sales_res;
						
						
						
						$recipt = "t_product_receipts";
						$kiosk_product_sale = "t_kiosk_product_sales";
						$payment_table = "t_payment_details";
						$products = "products";
					
						$ProductReceiptTable = TableRegistry::get($recipt,[
																			'table' => $recipt,
																		]);
						$KioskProductSaleTable = TableRegistry::get($kiosk_product_sale,[
																							'table' => $kiosk_product_sale,
																						]);
						$PaymentDetailTable = TableRegistry::get($payment_table,[
																							'table' => $payment_table,
																						]); 
						
						if(!empty($product_recipt_data)){
							unset($product_recipt_data['id']);
							unset($product_recipt_data['created']);
							unset($product_recipt_data['modified']);
							if(!empty($kiosk_id)){
								$product_recipt_data['kiosk_id'] = $kiosk_id;
							}
							
							$ProductReceiptTable->behaviors()->load('Timestamp');
							$recipt_entity = $ProductReceiptTable->newEntity($product_recipt_data);
							$recipt_entity = $ProductReceiptTable->patchEntity($recipt_entity,$product_recipt_data);
							$ProductReceiptTable->save($recipt_entity);
							$recipt_id = $recipt_entity->id;
						}
						if(!empty($payment_detail_data)){
							foreach($payment_detail_data as $key => $value){
								unset($value['id']);
								unset($value['created']);
								unset($value['modified']);
								if(!empty($kiosk_id)){
									$value['kiosk_id'] = $kiosk_id;
								}
								$value['product_receipt_id'] = $recipt_id;
								$PaymentDetailTable->behaviors()->load('Timestamp');
								$payment_entity = $PaymentDetailTable->newEntity($value);
								$payment_entity = $PaymentDetailTable->patchEntity($payment_entity,$value);
								$PaymentDetailTable->save($payment_entity);
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
								$value1['product_receipt_id'] = $recipt_id;
								$KioskProductSaleTable->behaviors()->load('Timestamp');
								$sale_entity = $KioskProductSaleTable->newEntity($value1);
								$sale_entity = $KioskProductSaleTable->patchEntity($sale_entity,$value1);
								$KioskProductSaleTable->save($sale_entity);
							}
						}
					}
					if(!empty($kiosk_id)){
						$recipt = "kiosk_{$kiosk_id}_product_receipts";
						$kiosk_product_sale = "kiosk_{$kiosk_id}_product_sales";
						$payment_table = "kiosk_{$kiosk_id}_payment_details";
						//$products = "products";
						
						//$this->ProductReceipt->setSource("kiosk_{$kiosk_id}_product_receipts");
						//$this->KioskProductSale->setSource("kiosk_{$kiosk_id}_product_sales");
						//$this->PaymentDetail->setSource("kiosk_{$kiosk_id}_payment_details");
					}else{
						$recipt = "product_receipts";
						$kiosk_product_sale = "kiosk_product_sales";
						$payment_table = "payment_details";
						//$products = "products";
						
						
						//$this->ProductReceipt->setSource("product_receipts");
						//$this->KioskProductSale->setSource("kiosk_product_sales");
						//$this->PaymentDetail->setSource("payment_details");
					}
					$ProductReceiptTable = TableRegistry::get($recipt,[
																			'table' => $recipt,
																		]);
					$KioskProductSaleTable = TableRegistry::get($kiosk_product_sale,[
																						'table' => $kiosk_product_sale,
																					]);
					$PaymentDetailTable = TableRegistry::get($payment_table,[
																			'table' => $payment_table,
																		]); 
				
					$recipt_update_query = "UPDATE {$recipt} SET bulk_discount = 0,bill_cost = 0.10,bill_amount=0.30,orig_bill_amount=0.30 WHERE id = $id";
					$conn = ConnectionManager::get('default');
					$stmt = $conn->execute($recipt_update_query); 
					
					if(count($kiosk_product_sale_data1) > 1){
						$first_entry_id = $kiosk_product_sale_data1[0]['id'];
						$sale_update_query = "UPDATE {$kiosk_product_sale} SET discount = 0, discount=0,discount_status = 0 ,product_id = 7224,quantity = 1,cost_price=0.10,sale_price=0.25 WHERE id = $first_entry_id";
						
						$conn = ConnectionManager::get('default');
						$stmt = $conn->execute($sale_update_query); 
						
						unset($kiosk_product_sale_data1[0]);
						foreach($kiosk_product_sale_data1 as $s => $raw_data){
							$delete_id  = $raw_data['id'];
							$sale_delete_query = "DELETE FROM {$kiosk_product_sale} WHERE id = $delete_id";
							
							$conn = ConnectionManager::get('default');
							$stmt = $conn->execute($sale_delete_query); 
							
						}
					}else{
						$first_entry_id = $kiosk_product_sale_data1[0]['id'];
						$sale_update_query = "UPDATE {$kiosk_product_sale} SET product_id = 7224,discount=0,quantity = 1,cost_price=0.10,sale_price=0.25 WHERE id = $first_entry_id";
						
						$conn = ConnectionManager::get('default');
						$stmt = $conn->execute($sale_update_query);
					}
					
					if(count($payment_detail_data1) > 1){
						$paymentfirst_entry_id = $payment_detail_data1[0]['id'];
						$payment_update_query = "UPDATE {$payment_table} SET amount=0.30,payment_method='Cash' WHERE id = $paymentfirst_entry_id";
						
						$conn = ConnectionManager::get('default');
						$stmt = $conn->execute($payment_update_query);
						
						unset($payment_detail_data1[0]);
						foreach($payment_detail_data1 as $p => $p_raw_data){
							$p_delete_id  = $p_raw_data['id'];
							$payment_delete_query = "DELETE FROM {$payment_table} WHERE id = $p_delete_id";
							
							$conn = ConnectionManager::get('default');
							$stmt = $conn->execute($payment_delete_query);
							
						}
					}else{
						$paymentfirst_entry_id = $payment_detail_data1[0]['id'];
						$payment_update_query = "UPDATE {$payment_table} SET amount=0.30,payment_method='Cash' WHERE id = $paymentfirst_entry_id";
						
						$conn = ConnectionManager::get('default');
						$stmt = $conn->execute($payment_update_query);
					}
					//Start block: cron dashboard code
					if(strtotime(date("y-m-d",strtotime($res['created']))) != strtotime(date("y-m-d"))){ //
						$this->loadModel('DashboardData');
						
						if($kiosk_id == 10000 || $kiosk_id == ""){
							$kioskid = 0;
						}else{
							$kioskid = $kiosk_id;
						}
						
						$conditionArr = array();
						$conditionArr[] = array(
									  "date >=" => date('Y-m-d', strtotime($res['created'])),
									  "date <" => date('Y-m-d', strtotime($res['created']. ' +1 Days')),			
										 );
						  
						$conditionArr['kiosk_id'] = $kioskid;
						  
						$dashboardData_query = $this->DashboardData->find('all',[
															'conditions'=>$conditionArr,
															'order'=>['id desc'],
															//'limit'=>2
														]
													);
						//->toArray();
						$dashboardData_query = $dashboardData_query->hydrate(false);
						if(!empty($dashboardData_query)){
							$dashboardData = $dashboardData_query->toArray();
						}else{
							$dashboardData = array();
						}
						$new_dash_data = $dashboardData;
						if(!empty($dashboardData)){
							$count = count($dashboardData);
							foreach($dashboardData as $daash_key => $dash_value){
								if($daash_key > 1){
									continue;
								}
								if($count > 3){
									
								}else{
									unset($new_dash_data[$daash_key]['id']);
								}
								unset($new_dash_data[$daash_key]['created']);
								unset($new_dash_data[$daash_key]['modified']);
								
								
								
								//pr($dash_value);
								if(array_key_exists("product_sale",$dash_value)){
									$new_dash_data[$daash_key]['product_sale'] = ((float)$dash_value['product_sale'] - (float)$prv_amt)+0.20;
								}
								if(array_key_exists("product_sale_desc",$dash_value)){
									$product_sale_desc = unserialize($dash_value['product_sale_desc']);
									$credit_desc = unserialize($dash_value['net_credit_desc']);
									
									$net_card_desc = unserialize($dash_value['net_card_desc']);
									$cash_in_hand_desc = unserialize($dash_value['cash_in_hand_desc']);
									$net_bnk_tnsfer_desc = unserialize($dash_value['net_bnk_tnsfer_desc']);
									$net_cheque_payment_desc = unserialize($dash_value['net_cheque_payment_desc']);
									
									foreach($old_payment_res as $pay_key => $pay_detail){
										$pay_method = $pay_detail['payment_method'];
										$pay_amt = $pay_detail['amount'];
										if($pay_method == "On Credit"){
											$product_sale_desc['credit'] = (float)$product_sale_desc['credit'] - (float)$pay_amt;
											$credit_desc[0] = (float)$credit_desc[0] - (float)$pay_amt;
											$new_dash_data[$daash_key]['net_credit'] = (float)$dash_value['net_credit'] - (float)$pay_amt;
										}
										if($pay_method == "Cash"){
											$product_sale_desc['cash'] = (float)$product_sale_desc['cash'] - (float)$pay_amt;
											$new_dash_data[$daash_key]['cash_in_hand'] = (float)$dash_value['cash_in_hand'] - (float)$pay_amt;
											$cash_in_hand_desc['sale']['Product'] = (float)$cash_in_hand_desc['sale']['Product']-(float)$pay_amt;
										}
										if($pay_method == "Card"){
											$net_card_desc['Product'] = (float)$net_card_desc['Product'] - (float)$pay_amt;
											$product_sale_desc['card'] = (float)$product_sale_desc['card'] - (float)$pay_amt;
											$new_dash_data[$daash_key]['net_card'] = (float)$dash_value['net_card'] - (float)$pay_amt;
										}
										if($pay_method == "Cheque"){
											$net_cheque_payment_desc[0] = (float)$net_cheque_payment_desc[0] - (float)$pay_amt;
											$product_sale_desc['cheque'] = (float)$product_sale_desc['cheque'] - (float)$pay_amt;
											$new_dash_data[$daash_key]['net_cheque_payment'] = (float)$dash_value['net_cheque_payment'] - (float)$pay_amt;
										}
										if($pay_method == "Bank Transfer"){
											$net_bnk_tnsfer_desc[0] = (float)$net_bnk_tnsfer_desc[0] - (float)$pay_amt;
											$product_sale_desc['bank_transfer'] = (float)$product_sale_desc['bank_transfer'] - (float)$pay_amt;
											$new_dash_data[$daash_key]['net_bnk_tnsfer'] = (float)$dash_value['net_bnk_tnsfer'] - (float)$pay_amt;
										}
									}
									
									$new_dash_data[$daash_key]['cash_in_hand'] = $new_dash_data[$daash_key]['cash_in_hand'] + 0.30;
									
									$cash_in_hand_desc['sale']['Product'] = $cash_in_hand_desc['sale']['Product'] + 0.30;
									$cash_in_hand_desc_new = serialize($cash_in_hand_desc);
									$new_dash_data[$daash_key]['cash_in_hand_desc'] = $cash_in_hand_desc_new;
									
									
									$credit_desc_new = serialize($credit_desc);
									$new_dash_data[$daash_key]['net_credit_desc'] = $credit_desc_new;
									
									$net_cheque_payment_desc_new = serialize($net_cheque_payment_desc);
									$new_dash_data[$daash_key]['net_cheque_payment_desc'] = $net_cheque_payment_desc_new;
									
									$net_bnk_tnsfer_desc_new = serialize($net_bnk_tnsfer_desc);
									$new_dash_data[$daash_key]['net_bnk_tnsfer_desc'] = $net_bnk_tnsfer_desc_new;
									
									$net_card_desc_new = serialize($net_card_desc);
									$new_dash_data[$daash_key]['net_card_desc'] = $net_card_desc_new;
									
									
									
									
									$product_sale_desc['cash'] = (float)$product_sale_desc['cash'] + 0.30;
									$product_sale_desc_new = serialize($product_sale_desc);
									$new_dash_data[$daash_key]['product_sale_desc'] = $product_sale_desc_new;
								}
								
								if(array_key_exists("total_sale",$dash_value)){
									$new_dash_data[$daash_key]['total_sale'] = ((float)$dash_value['total_sale'] - (float)$prv_amt)+0.30;
								}
								if(array_key_exists("net_cash",$dash_value)){
									$new_dash_data[$daash_key]['net_cash'] = ((float)$dash_value['net_cash'] - (float)$prv_amt)+0.30;
								}
								if(array_key_exists("net_sale",$dash_value)){
									$new_dash_data[$daash_key]['net_sale'] = ((float)$dash_value['net_sale'] - (float)$prv_amt)+0.30;
								}
								
							}
						}
						//pr($new_dash_data);die;
						if(!empty($new_dash_data)){
							foreach($new_dash_data as $new_key => $new_val){
								if(array_key_exists('id',$new_val)){
									$new_entity = $this->DashboardData->get($new_val['id']);
								}else{
									$new_entity = $this->DashboardData->newEntity();	
								}
								$new_entity = $this->DashboardData->patchEntity($new_entity,$new_val);
								$this->DashboardData->behaviors()->load('Timestamp');
								$this->DashboardData->save($new_entity);
							}
						}	
					}
					//End block: cron dashboard code
					
					if($this->request->session()->read('Auth.User.group_id') == ADMINISTRATORS || $this->request->session()->read('Auth.User.group_id') == FRANCHISE_OWNER){
						$kiosk_id_to_set = $this->get_kiosk_for_invoice();
						$this->Flash->success(__("Invoice with Invoice id {$id} changed to special invoice"));
						return $this->redirect(array('action'=>"search?kiosk_id=$kiosk_id_to_set&date_type=invoice"));
					}else{
						$this->Flash->success(__("Invoice with Invoice id {$id} changed to special invoice"));
						return $this->redirect(array('action'=>'index'));
					}
					//$this->Flash->error(__("Invoice with Invoice id {$id} changed to special invoice"));
					//return $this->redirect(array('action'=>'dr_index'));
				}
			}
			$amt = $res['orig_bill_amount'];
			$vat = $res['vat'];
			if(!empty($vat)){
				$final_amt = $amt/(1+($vat/100));
			}else{
				$final_amt = $amt;
			}
			$final_amt = round($final_amt,2);
			$this->set(compact('res','final_amt','payment_res'));
			$this->render("payment_screen");
		}
	}
	
	public function specialToOrig($id){
		$this->check_dr5();
		$t_recipt = "t_product_receipts";
		$t_kiosk_product_sale = "t_kiosk_product_sales";
		$t_payment_table = "t_payment_details";
	
		$ProductReceiptTable = TableRegistry::get($t_recipt,[
																			'table' => $t_recipt,
																		]);
		$KioskProductSaleTable = TableRegistry::get($t_kiosk_product_sale,[
																			'table' => $t_kiosk_product_sale,
																		]);
		$PaymentDetailTable = TableRegistry::get($t_payment_table,[
																			'table' => $t_payment_table,
																		]); 
		
		$res_query = $ProductReceiptTable->find('all',array(
												'conditions' => array('id' => $id)
												));
		$res_query = $res_query->hydrate(false);
		if(!empty($res_query)){
			$res = $res_query->first();
		}else{
			$res = array();
		}
		$customer_id = $res['customer_id'];
		$cust_query = $this->Customers->find('all',[
									  'conditions' => array('id' => $customer_id)
									  ]);
		$cust_query = $cust_query->hydrate(false);
		if(!empty($cust_query)){
			$cust_result = $cust_query->first();
		}else{
			$cust_result = array(); 
		}
		
		$payment_query = $PaymentDetailTable->find('all',[
										 'conditions' => [
											'product_receipt_id' => $id
										 ]
										 ]);
		$payment_query = $payment_query->hydrate(false);
		if(!empty($payment_query)){
			$payment_res = $payment_query->toArray();
		}else{
			$payment_res = array();
		}
	
		$sales_query = $KioskProductSaleTable->find('all',[
											'conditions' => [
												'product_receipt_id' => $id
											]
											]);
		$sales_query = $sales_query->hydrate(false);
		if(!empty($sales_query)){
			$sales_res = $sales_query->toArray();
		}else{
			$sales_res = array();
		}
		$country = $cust_result['country'];
		if($country == "OTH"){
			//echo'hi';die;
			if(!empty($res)){
				$product_recipt_data1 = $product_recipt_data = $res;
				$payment_detail_data1 = $payment_detail_data = $payment_res;
				$kiosk_product_sale_data1 = $kiosk_product_sale_data = $sales_res;
				$created = $res['created'];
				$database_date = strtotime(date('d-m-y',strtotime($created)));
				$today_date = strtotime(date('d-m-y'));
				$loggedInUser = $this->request->session()->read('Auth.User.username');
				if ($loggedInUser != SPL_PRIVILEGE_USER){   //QUOT_USER_PREFIX."inderjit"
					if($today_date != $database_date){
						$this->Flash->error(__("Invoice can be migrated on same day only"));
						if($this->request->session()->read('Auth.User.group_id') == ADMINISTRATORS || $this->request->session()->read('Auth.User.group_id') == FRANCHISE_OWNER){
							$kiosk_id_to_set = $this->get_kiosk_for_dr_invoice();
							return $this->redirect(array('action'=>"dr_search?kiosk_id=$kiosk_id_to_set&date_type=invoice"));
						}else{
							return $this->redirect(array('action'=>'dr_index'));
						}die;
						//return $this->redirect(array('action'=>'dr_index'));
					}
				}
			}
			$kiosk_id = $this->request->Session()->read('kiosk_id');
			if($kiosk_id == ""){
				$kiosk_id = 0;
			}
			//if($product_recipt_data['kiosk_id'] != $kiosk_id){
			//	$this->Flash->error(__("cant change invoice"));
			//	return $this->redirect(array('action'=>'index'));die;
			//}
			
			if(!empty($kiosk_id)){
				//echo'1';die;
				$recipt_source = "kiosk_{$kiosk_id}_product_receipts";
				$sale_source = "kiosk_{$kiosk_id}_product_sales";
				$payment_source = "kiosk_{$kiosk_id}_payment_details";
			}else{
				$saved_kiosk_id = (int)$res['kiosk_id'];
				if(!empty($saved_kiosk_id)){
					if($saved_kiosk_id == 0 || $saved_kiosk_id == ""){
						$recipt_source = "product_receipts";
						$sale_source = "kiosk_product_sales";
						$payment_source = "payment_details";	
					}else{
						$recipt_source = "kiosk_{$saved_kiosk_id}_product_receipts";
						$sale_source = "kiosk_{$saved_kiosk_id}_product_sales";
						$payment_source = "kiosk_{$saved_kiosk_id}_payment_details";	
					}
				}else{
					//echo'3';die;
					$recipt_source = "product_receipts";
					$sale_source = "kiosk_product_sales";
					$payment_source = "payment_details";
				}
			}
			
			$ProductReceiptTable = TableRegistry::get($recipt_source,[
																			'table' => $recipt_source,
																		]);
			$KioskProductSaleTable = TableRegistry::get($sale_source,[
																				'table' => $sale_source,
																			]);
			$PaymentDetailTable = TableRegistry::get($payment_source,[
																				'table' => $payment_source,
																			]); 
			
			if(!empty($product_recipt_data)){
				$product_recipt_data['vat'] = 0; 
				unset($product_recipt_data['id']);
				unset($product_recipt_data['created']);
				unset($product_recipt_data['modified']);
				$product_recipt_data['sale_type'] = 1;
				$ProductReceiptTable->behaviors()->load('Timestamp');
				$recipt_entity = $ProductReceiptTable->newEntity($product_recipt_data);
				$recipt_entity = $ProductReceiptTable->patchEntity($recipt_entity,$product_recipt_data);
				$ProductReceiptTable->save($recipt_entity);
				$recipt_id = $recipt_entity->id;
			}
			if(!empty($payment_detail_data)){
				foreach($payment_detail_data as $key => $value){
					unset($value['id']);
					unset($value['created']);
					unset($value['modified']);
					$value['product_receipt_id'] = $recipt_id;
					$PaymentDetailTable->behaviors()->load('Timestamp');
					$payment_entity = $PaymentDetailTable->newEntity($value);
					$payment_entity = $PaymentDetailTable->patchEntity($payment_entity,$value);
					$PaymentDetailTable->save($payment_entity);
				}
			}
			if(!empty($kiosk_product_sale_data)){
				foreach($kiosk_product_sale_data as $key1 => $value1){
					unset($value1['id']);
					unset($value1['created']);
					unset($value1['modified']);
					$value1['product_receipt_id'] = $recipt_id;
					$KioskProductSaleTable->behaviors()->load('Timestamp');
					$sales_entity = $KioskProductSaleTable->newEntity($value1);
					$sales_entity = $KioskProductSaleTable->patchEntity($sales_entity,$value1);
					$KioskProductSaleTable->save($sales_entity);
				}
			}
			
			
			$recipt_update_query = "UPDATE {$t_recipt} SET bulk_discount = 0,bill_cost = 0.10,bill_amount=0.25,orig_bill_amount=0.25 WHERE id = $id";
			
			$conn = ConnectionManager::get('default');
			$stmt = $conn->execute($recipt_update_query); 
			
			if(count($kiosk_product_sale_data1) > 1){
				$first_entry_id = $kiosk_product_sale_data1[0]['id'];
				$sale_update_query = "UPDATE {$t_kiosk_product_sale} SET product_id = 7224,discount=0,quantity = 1,cost_price=0.10,sale_price=0.25 WHERE id = $first_entry_id";
				
				$conn = ConnectionManager::get('default');
				$stmt = $conn->execute($sale_update_query); 
				
				unset($kiosk_product_sale_data1[0]);
				foreach($kiosk_product_sale_data1 as $s => $raw_data){
					$delete_id  = $raw_data['id'];
					$sale_delete_query = "DELETE FROM {$t_kiosk_product_sale} WHERE id = $delete_id";
					
					$conn = ConnectionManager::get('default');
					$stmt = $conn->execute($sale_delete_query);
				}
			}else{
				$first_entry_id = $kiosk_product_sale_data1[0]['id'];
				$sale_update_query = "UPDATE {$t_kiosk_product_sale} SET product_id = 7224,discount=0,quantity = 1,cost_price=0.10,sale_price=0.25 WHERE id = $first_entry_id";
				
				$conn = ConnectionManager::get('default');
				$stmt = $conn->execute($sale_update_query);
			}
			
			if(count($payment_detail_data1) > 1){
				$paymentfirst_entry_id = $payment_detail_data1[0]['id'];
				$payment_update_query = "UPDATE {$t_payment_table} SET amount=0.25,payment_method='Cash' WHERE id = $paymentfirst_entry_id";
				
				$conn = ConnectionManager::get('default');
				$stmt = $conn->execute($payment_update_query);
				unset($payment_detail_data1[0]);
				foreach($payment_detail_data1 as $p => $p_raw_data){
					$p_delete_id  = $p_raw_data['id'];
					$payment_delete_query = "DELETE FROM {$t_payment_table} WHERE id = $p_delete_id";
					
					$conn = ConnectionManager::get('default');
					$stmt = $conn->execute($payment_delete_query);
				}
			}else{
				$paymentfirst_entry_id = $payment_detail_data1[0]['id'];
				$payment_update_query = "UPDATE {$t_payment_table} SET amount=0.25,payment_method='Cash' WHERE id = $paymentfirst_entry_id";
				
				$conn = ConnectionManager::get('default');
				$stmt = $conn->execute($payment_update_query);
				
			}
			//Start block: cron dashboard code
			if(strtotime(date("y-m-d",strtotime($res['created']))) != strtotime(date("y-m-d"))){ //
				$this->loadModel('DashboardData');
				
				if($kiosk_id == 10000 || $kiosk_id == ""){
					$kioskid = 0;
				}else{
					$kioskid = $kiosk_id;
				}
				
				$conditionArr = array();
				$conditionArr[] = array(
							  "date >=" => date('Y-m-d', strtotime($res['created'])),
							  "date <" => date('Y-m-d', strtotime($res['created']. ' +1 Days')),			
								 );
				  
				$conditionArr['kiosk_id'] = $kioskid;
				  
				$dashboardData_query = $this->DashboardData->find('all',[
													'conditions'=>$conditionArr,
													'order'=>['id desc'],
													//'limit'=>2
												]
											);
				//->toArray();
				$dashboardData_query = $dashboardData_query->hydrate(false);
				if(!empty($dashboardData_query)){
					$dashboardData = $dashboardData_query->toArray();
				}else{
					$dashboardData = array();
				}
				$new_dash_data = $dashboardData;
				if(!empty($dashboardData)){
					$count = count($dashboardData);
					foreach($dashboardData as $daash_key => $dash_value){
						if($count > 3){
							
						}else{
							unset($new_dash_data[$daash_key]['id']);
							unset($new_dash_data[$daash_key]['created']);
							unset($new_dash_data[$daash_key]['modified']);	
						}
						
						if($dash_value['user_type'] != "other"){
							continue;
						}
						
						if(array_key_exists("quotation",$dash_value)){
							$new_dash_data[$daash_key]['quotation'] = ((float)$dash_value['quotation'] - (float)$prv_amt)+0.25;
						}
						if(array_key_exists("quotation_desc",$dash_value)){
							$product_sale_desc = unserialize($dash_value['quotation_desc']);
							$credit_desc = unserialize($dash_value['net_credit_desc']);
							
							$net_card_desc = unserialize($dash_value['net_card_desc']);
							$cash_in_hand_desc = unserialize($dash_value['cash_in_hand_desc']);
							$net_bnk_tnsfer_desc = unserialize($dash_value['net_bnk_tnsfer_desc']);
							$net_cheque_payment_desc = unserialize($dash_value['net_cheque_payment_desc']);
							
							foreach($payment_res as $pay_key => $pay_detail){
								$pay_method = $pay_detail['payment_method'];
								$pay_amt = $pay_detail['amount'];
								if($pay_method == "On Credit"){
									$product_sale_desc['credit'] = (float)$product_sale_desc['credit'] - (float)$pay_amt;
									$credit_desc[0] = (float)$credit_desc[0] - (float)$pay_amt;
									$new_dash_data[$daash_key]['net_credit'] = (float)$dash_value['net_credit'] - (float)$pay_amt;
								}
								if($pay_method == "Cash"){
									$product_sale_desc['cash'] = (float)$product_sale_desc['cash'] - (float)$pay_amt;
									$new_dash_data[$daash_key]['cash_in_hand'] = (float)$dash_value['cash_in_hand'] - (float)$pay_amt;
									$cash_in_hand_desc['sale']['special'] = (float)$cash_in_hand_desc['sale']['special']-(float)$pay_amt;
								}
								if($pay_method == "Card"){
									$net_card_desc['special'] = (float)$net_card_desc['special'] - (float)$pay_amt;
									$product_sale_desc['card'] = (float)$product_sale_desc['card'] - (float)$pay_amt;
									$new_dash_data[$daash_key]['net_card'] = (float)$dash_value['net_card'] - (float)$pay_amt;
								}
								if($pay_method == "Cheque"){
									$net_cheque_payment_desc[0] = (float)$net_cheque_payment_desc[0] - (float)$pay_amt;
									$product_sale_desc['cheque'] = (float)$product_sale_desc['cheque'] - (float)$pay_amt;
									$new_dash_data[$daash_key]['net_cheque_payment'] = (float)$dash_value['net_cheque_payment'] - (float)$pay_amt;
								}
								if($pay_method == "Bank Transfer"){
									$net_bnk_tnsfer_desc[0] = (float)$net_bnk_tnsfer_desc[0] - (float)$pay_amt;
									$product_sale_desc['bank_transfer'] = (float)$product_sale_desc['bank_transfer'] - (float)$pay_amt;
									$new_dash_data[$daash_key]['net_bnk_tnsfer'] = (float)$dash_value['net_bnk_tnsfer'] - (float)$pay_amt;
								}
							}
							
							$new_dash_data[$daash_key]['cash_in_hand'] = $new_dash_data[$daash_key]['cash_in_hand'] + 0.25;
							
							$cash_in_hand_desc['sale']['special'] = $cash_in_hand_desc['sale']['special'] + 0.25;
							$cash_in_hand_desc_new = serialize($cash_in_hand_desc);
							$new_dash_data[$daash_key]['cash_in_hand_desc'] = $cash_in_hand_desc_new;
							
							
							$credit_desc_new = serialize($credit_desc);
							$new_dash_data[$daash_key]['net_credit_desc'] = $credit_desc_new;
							
							$net_cheque_payment_desc_new = serialize($net_cheque_payment_desc);
							$new_dash_data[$daash_key]['net_cheque_payment_desc'] = $net_cheque_payment_desc_new;
							
							$net_bnk_tnsfer_desc_new = serialize($net_bnk_tnsfer_desc);
							$new_dash_data[$daash_key]['net_bnk_tnsfer_desc'] = $net_bnk_tnsfer_desc_new;
							
							$net_card_desc_new = serialize($net_card_desc);
							$new_dash_data[$daash_key]['net_card_desc'] = $net_card_desc_new;
							
							
							
							
							$product_sale_desc['cash'] = (float)$product_sale_desc['cash'] + 0.25;
							$product_sale_desc_new = serialize($product_sale_desc);
							$new_dash_data[$daash_key]['quotation_desc'] = $product_sale_desc_new;
						}
						
					
						
						if(array_key_exists("total_sale",$dash_value)){
							$new_dash_data[$daash_key]['total_sale'] = ((float)$dash_value['total_sale'] - (float)$prv_amt)+0.25;
						}
						if(array_key_exists("net_cash",$dash_value)){
							$new_dash_data[$daash_key]['net_cash'] = ((float)$dash_value['net_cash'] - (float)$prv_amt)+0.25;
						}
						if(array_key_exists("net_sale",$dash_value)){
							$new_dash_data[$daash_key]['net_sale'] = ((float)$dash_value['net_sale'] - (float)$prv_amt)+0.25;
						}
						
					}
				}
				//pr($new_dash_data);die;
				if(!empty($new_dash_data)){
					foreach($new_dash_data as $new_key => $new_val){
						if(array_key_exists('id',$new_val)){
							$new_entity = $this->DashboardData->get($new_val['id']);
						}else{
							$new_entity = $this->DashboardData->newEntity();	
						}
						$new_entity = $this->DashboardData->patchEntity($new_entity,$new_val);
						$this->DashboardData->behaviors()->load('Timestamp');
						$this->DashboardData->save($new_entity);
					}
				}	
			}
			
			//End block: cron dashboard code
			if($this->request->session()->read('Auth.User.group_id') == ADMINISTRATORS || $this->request->session()->read('Auth.User.group_id') == FRANCHISE_OWNER){
				$kiosk_id_to_set = $this->get_kiosk_for_dr_invoice();
				$this->Flash->success(__("Invoice with Invoice id {$id} changed to Normal invoice"));
				return $this->redirect(array('action'=>"dr_search?kiosk_id=$kiosk_id_to_set&date_type=invoice"));
			}else{
				$this->Flash->success(__("Invoice with Invoice id {$id} changed to Normal invoice"));
				return $this->redirect(array('action'=>'dr_index'));
			}
		}else{
			//echo'bye';die;
			if($this->request->is('Post')){
				
				$total_amt = 0;
				$sale_amt = $this->request->data['sale_amount'];
				foreach($this->request->data['payment'] as $key => $value){
					$total_amt += $value;
				}
				if($sale_amt != $total_amt){
					$amt = $res['orig_bill_amount'];
					$vat = $this->VAT;
					if(!empty($vat)){
						$final_amt = $amt + $amt*($vat/100);
					}else{
						$final_amt = $amt;
					}
					$final_amt = round($final_amt,2);
					$this->set(compact('res','final_amt','payment_res'));
					$this->Flash->error(__("amount is not matching"));
					return $this->redirect(array('action'=>'specialToOrig',$id));
				}else{
					
					$created = $res['created'];
					$prv_amt = $res['orig_bill_amount'];
					$database_date = strtotime(date('d-m-y',strtotime($created)));
					$today_date = strtotime(date('d-m-y'));
					$loggedInUser = $this->request->session()->read('Auth.User.username');
					if ($loggedInUser != SPL_PRIVILEGE_USER){ //QUOT_USER_PREFIX."inderjit"
						if($today_date != $database_date){
							$this->Flash->error(__("Invoice can be migrated on same day only"));
							return $this->redirect(array('action'=>'index'));
						}
					}
					
					$payment_data = $this->request->data['payment'];
					$old_payment_res = $payment_res;
					foreach($payment_res as $s_key => $s_value){
						if(array_key_exists($s_value['id'],$payment_data)){
							$new_amt = $payment_data[$s_value['id']];
							$payment_res[$s_key]['amount'] = $new_amt;
						}
					}
					$res['orig_bill_amount'] = $sale_amt;
					$res['bill_amount'] = $sale_amt;
					$res['vat'] = $this->VAT;
					//----------------------------------------------
					if(!empty($res)){
						$product_recipt_data1 = $product_recipt_data = $res;
						$payment_detail_data1 = $payment_detail_data = $payment_res;
						$kiosk_product_sale_data1 = $kiosk_product_sale_data = $sales_res;
						$created = $res['created'];
						$database_date = strtotime(date('d-m-y',strtotime($created)));
						$today_date = strtotime(date('d-m-y'));
						$loggedInUser = $this->request->session()->read('Auth.User.username');
						if ($loggedInUser != SPL_PRIVILEGE_USER){
								if($today_date != $database_date){
									$this->Flash->error(__("Invoice can be migrated on same day only"));
									return $this->redirect(array('action'=>'dr_index'));
								}
						}
					}
					$kiosk_id = $this->request->Session()->read('kiosk_id');
					if($kiosk_id == ""){
						$kiosk_id = 0;
					}
					//if($product_recipt_data['kiosk_id'] != $kiosk_id){
					//	$this->Flash->error(__("cant change invoice"));
					//	return $this->redirect(array('action'=>'index'));die;
					//}
					
					if(!empty($kiosk_id)){
						$recipt = "kiosk_{$kiosk_id}_product_receipts";
						$kiosk_product_sale = "kiosk_{$kiosk_id}_product_sales";
						$payment_table = "kiosk_{$kiosk_id}_payment_details";
					}else{
						$saved_kiosk_id = $res['kiosk_id'];
						if(!empty($saved_kiosk_id)){
							if($saved_kiosk_id == 0 || $saved_kiosk_id == ""){
								$recipt = "product_receipts";
								$kiosk_product_sale = "kiosk_product_sales";
								$payment_table = "payment_details";
							}else{
								$recipt = "kiosk_{$saved_kiosk_id}_product_receipts";
								$kiosk_product_sale = "kiosk_{$saved_kiosk_id}_product_sales";
								$payment_table = "kiosk_{$saved_kiosk_id}_payment_details";
							}
						}else{
							$recipt = "product_receipts";
							$kiosk_product_sale = "kiosk_product_sales";
							$payment_table = "payment_details";
						}
					}
					
					
					$ProductReceiptTable = TableRegistry::get($recipt,[
																			'table' => $recipt,
																		]);
					$KioskProductSaleTable = TableRegistry::get($kiosk_product_sale,[
																						'table' => $kiosk_product_sale,
																					]);
					$PaymentDetailTable = TableRegistry::get($payment_table,[
																						'table' => $payment_table,
																					]); 
					
					
					if(!empty($product_recipt_data)){
						unset($product_recipt_data['id']);
						unset($product_recipt_data['created']);
						unset($product_recipt_data['modified']);
						$product_recipt_data['sale_type'] = 1;
						$ProductReceiptTable->behaviors()->load('Timestamp');
						$recipt_entity = $ProductReceiptTable->newEntity($product_recipt_data);
						$recipt_entity = $ProductReceiptTable->patchEntity($recipt_entity,$product_recipt_data);
						$ProductReceiptTable->save($recipt_entity);
						$recipt_id = $recipt_entity->id;
					}
					if(!empty($payment_detail_data)){
						foreach($payment_detail_data as $key => $value){
							unset($value['id']);
							unset($value['created']);
							unset($value['modified']);
							$value['product_receipt_id'] = $recipt_id;
							$PaymentDetailTable->behaviors()->load('Timestamp');
							$payment_entity = $PaymentDetailTable->newEntity($value);
							$payment_entity = $PaymentDetailTable->patchEntity($payment_entity,$value);
							$PaymentDetailTable->save($payment_entity);
						}
					}
					if(!empty($kiosk_product_sale_data)){
						foreach($kiosk_product_sale_data as $key1 => $value1){
							unset($value1['id']);
							unset($value1['created']);
							unset($value1['modified']);
							$value1['product_receipt_id'] = $recipt_id;
							$KioskProductSaleTable->behaviors()->load('Timestamp');
							$sales_entity = $KioskProductSaleTable->newEntity($value1);
							$sales_entity = $KioskProductSaleTable->patchEntity($sales_entity,$value1);
							$KioskProductSaleTable->save($sales_entity);
						}
					}
					
					
					$recipt_update_query = "UPDATE {$t_recipt} SET bulk_discount = 0,bill_cost = 0.10,bill_amount=0.25,orig_bill_amount=0.25 WHERE id = $id";
					
					$conn = ConnectionManager::get('default');
					$stmt = $conn->execute($recipt_update_query); 
					
					if(count($kiosk_product_sale_data1) > 1){
						$first_entry_id = $kiosk_product_sale_data1[0]['id'];
						$sale_update_query = "UPDATE {$t_kiosk_product_sale} SET discount = 0, discount_status = 0 ,product_id = 7224,quantity = 1,cost_price=0.10,sale_price=0.25 WHERE id = $first_entry_id";
						
						$conn = ConnectionManager::get('default');
						$stmt = $conn->execute($sale_update_query); 
						
						unset($kiosk_product_sale_data1[0]);
						foreach($kiosk_product_sale_data1 as $s => $raw_data){
							$delete_id  = $raw_data['id'];
							$sale_delete_query = "DELETE FROM {$t_kiosk_product_sale} WHERE id = $delete_id";
							
							$conn = ConnectionManager::get('default');
							$stmt = $conn->execute($sale_delete_query); 
						}
					}else{
						$first_entry_id = $kiosk_product_sale_data1[0]['id'];
						$sale_update_query = "UPDATE {$t_kiosk_product_sale} SET product_id = 7224,discount=0,quantity = 1,cost_price=0.10,sale_price=0.25 WHERE id = $first_entry_id";
						$conn = ConnectionManager::get('default');
						$stmt = $conn->execute($sale_update_query); 
					}
					
					if(count($payment_detail_data1) > 1){
						$paymentfirst_entry_id = $payment_detail_data1[0]['id'];
						$payment_update_query = "UPDATE {$t_payment_table} SET amount=0.25,payment_method='Cash' WHERE id = $paymentfirst_entry_id";
						
						$conn = ConnectionManager::get('default');
						$stmt = $conn->execute($payment_update_query); 
						unset($payment_detail_data1[0]);
						foreach($payment_detail_data1 as $p => $p_raw_data){
							$p_delete_id  = $p_raw_data['id'];
							$payment_delete_query = "DELETE FROM {$t_payment_table} WHERE id = $p_delete_id";
							
							$conn = ConnectionManager::get('default');
							$stmt = $conn->execute($payment_delete_query); 
						}
					}else{
						$paymentfirst_entry_id = $payment_detail_data1[0]['id'];
						$payment_update_query = "UPDATE {$t_payment_table} SET amount=0.25,payment_method='Cash' WHERE id = $paymentfirst_entry_id";
						$conn = ConnectionManager::get('default');
						$stmt = $conn->execute($payment_update_query); 
					}
					//Start block: cron dashboard code
					if(strtotime(date("y-m-d",strtotime($res['created']))) != strtotime(date("y-m-d"))){ //
							$this->loadModel('DashboardData');
							
							if($kiosk_id == 10000 || $kiosk_id == ""){
								$kioskid = 0;
							}else{
								$kioskid = $kiosk_id;
							}
							
							$conditionArr = array();
							$conditionArr[] = array(
										  "date >=" => date('Y-m-d', strtotime($res['created'])),
										  "date <" => date('Y-m-d', strtotime($res['created']. ' +1 Days')),			
											 );
							  
							$conditionArr['kiosk_id'] = $kioskid;
							  
							$dashboardData_query = $this->DashboardData->find('all',[
																'conditions'=>$conditionArr,
																'order'=>['id desc'],
																//'limit'=>2
															]
														);
							//->toArray();
							$dashboardData_query = $dashboardData_query->hydrate(false);
							if(!empty($dashboardData_query)){
								$dashboardData = $dashboardData_query->toArray();
							}else{
								$dashboardData = array();
							}
							$new_dash_data = $dashboardData;
							if(!empty($dashboardData)){
								$count = count($dashboardData);
								foreach($dashboardData as $daash_key => $dash_value){
									if($count > 3){
										
									}else{
										unset($new_dash_data[$daash_key]['id']);
										unset($new_dash_data[$daash_key]['created']);
										unset($new_dash_data[$daash_key]['modified']);
										unset($new_dash_data[$daash_key]['status']);	
									}
									
									if($dash_value['user_type'] != "other"){
										continue;
									}
									
									if(array_key_exists("quotation",$dash_value)){
										$new_dash_data[$daash_key]['quotation'] = ((float)$dash_value['quotation'] - (float)$prv_amt)+0.25;
									}
									if(array_key_exists("quotation_desc",$dash_value)){
										$product_sale_desc = unserialize($dash_value['quotation_desc']);
										$credit_desc = unserialize($dash_value['net_credit_desc']);
										
										$net_card_desc = unserialize($dash_value['net_card_desc']);
										$cash_in_hand_desc = unserialize($dash_value['cash_in_hand_desc']);
										$net_bnk_tnsfer_desc = unserialize($dash_value['net_bnk_tnsfer_desc']);
										$net_cheque_payment_desc = unserialize($dash_value['net_cheque_payment_desc']);
										
										foreach($old_payment_res as $pay_key => $pay_detail){
											$pay_method = $pay_detail['payment_method'];
											$pay_amt = $pay_detail['amount'];
											if($pay_method == "On Credit"){
												$product_sale_desc['credit'] = (float)$product_sale_desc['credit'] - (float)$pay_amt;
												$credit_desc[0] = (float)$credit_desc[0] - (float)$pay_amt;
												$new_dash_data[$daash_key]['net_credit'] = (float)$dash_value['net_credit'] - (float)$pay_amt;
											}
											if($pay_method == "Cash"){
												$product_sale_desc['cash'] = (float)$product_sale_desc['cash'] - (float)$pay_amt;
												$new_dash_data[$daash_key]['cash_in_hand'] = (float)$dash_value['cash_in_hand'] - (float)$pay_amt;
												$cash_in_hand_desc['sale']['special'] = (float)$cash_in_hand_desc['sale']['special']-(float)$pay_amt;
											}
											if($pay_method == "Card"){
												$net_card_desc['special'] = (float)$net_card_desc['special'] - (float)$pay_amt;
												$product_sale_desc['card'] = (float)$product_sale_desc['card'] - (float)$pay_amt;
												$new_dash_data[$daash_key]['net_card'] = (float)$dash_value['net_card'] - (float)$pay_amt;
											}
											if($pay_method == "Cheque"){
												$net_cheque_payment_desc[0] = (float)$net_cheque_payment_desc[0] - (float)$pay_amt;
												$product_sale_desc['cheque'] = (float)$product_sale_desc['cheque'] - (float)$pay_amt;
												$new_dash_data[$daash_key]['net_cheque_payment'] = (float)$dash_value['net_cheque_payment'] - (float)$pay_amt;
											}
											if($pay_method == "Bank Transfer"){
												$net_bnk_tnsfer_desc[0] = (float)$net_bnk_tnsfer_desc[0] - (float)$pay_amt;
												$product_sale_desc['bank_transfer'] = (float)$product_sale_desc['bank_transfer'] - (float)$pay_amt;
												$new_dash_data[$daash_key]['net_bnk_tnsfer'] = (float)$dash_value['net_bnk_tnsfer'] - (float)$pay_amt;
											}
										}
										
										$new_dash_data[$daash_key]['cash_in_hand'] = $new_dash_data[$daash_key]['cash_in_hand'] + 0.25;
										
										$cash_in_hand_desc['sale']['special'] = $cash_in_hand_desc['sale']['special'] + 0.25;
										$cash_in_hand_desc_new = serialize($cash_in_hand_desc);
										$new_dash_data[$daash_key]['cash_in_hand_desc'] = $cash_in_hand_desc_new;
										
										
										$credit_desc_new = serialize($credit_desc);
										$new_dash_data[$daash_key]['net_credit_desc'] = $credit_desc_new;
										
										$net_cheque_payment_desc_new = serialize($net_cheque_payment_desc);
										$new_dash_data[$daash_key]['net_cheque_payment_desc'] = $net_cheque_payment_desc_new;
										
										$net_bnk_tnsfer_desc_new = serialize($net_bnk_tnsfer_desc);
										$new_dash_data[$daash_key]['net_bnk_tnsfer_desc'] = $net_bnk_tnsfer_desc_new;
										
										$net_card_desc_new = serialize($net_card_desc);
										$new_dash_data[$daash_key]['net_card_desc'] = $net_card_desc_new;
										
										
										
										
										$product_sale_desc['cash'] = (float)$product_sale_desc['cash'] + 0.25;
										$product_sale_desc_new = serialize($product_sale_desc);
										$new_dash_data[$daash_key]['quotation_desc'] = $product_sale_desc_new;
									}

									if(array_key_exists("total_sale",$dash_value)){
										$new_dash_data[$daash_key]['total_sale'] = ((float)$dash_value['total_sale'] - (float)$prv_amt)+0.25;
									}
									if(array_key_exists("net_cash",$dash_value)){
										$new_dash_data[$daash_key]['net_cash'] = ((float)$dash_value['net_cash'] - (float)$prv_amt)+0.25;
									}
									if(array_key_exists("net_sale",$dash_value)){
										$new_dash_data[$daash_key]['net_sale'] = ((float)$dash_value['net_sale'] - (float)$prv_amt)+0.25;
									}
									
								}
							}
							//pr($new_dash_data);die;
							if(!empty($new_dash_data)){
								foreach($new_dash_data as $new_key => $new_val){
									if(array_key_exists('id',$new_val)){
										$new_entity = $this->DashboardData->get($new_val['id']);
									}else{
										$new_entity = $this->DashboardData->newEntity();	
									}
									$new_entity = $this->DashboardData->patchEntity($new_entity,$new_val);
									$this->DashboardData->behaviors()->load('Timestamp');
									$this->DashboardData->save($new_entity);
								}
							}	
						}
					//End block: cron dashboard code
					if($this->request->session()->read('Auth.User.group_id') == ADMINISTRATORS || $this->request->session()->read('Auth.User.group_id') == FRANCHISE_OWNER){
						$kiosk_id_to_set = $this->get_kiosk_for_dr_invoice();
						$this->Flash->success(__("Invoice with Invoice id {$id} changed to Normal invoice"));
						return $this->redirect(array('action'=>"dr_search?kiosk_id=$kiosk_id_to_set&date_type=invoice"));
					}else{
						$this->Flash->success(__("Invoice with Invoice id {$id} changed to Normal invoice"));
						return $this->redirect(array('action'=>'dr_index'));
					}
					//----------------------------------------------
				}
			}
			$kiosk_id = $this->request->Session()->read('kiosk_id');
			if($kiosk_id == ""){
				$kiosk_id = 0;
			}
			$amt = $res['orig_bill_amount'];
			$vat = $this->VAT;
			if(!empty($vat)){
				$final_amt = $amt + $amt*($vat/100);
			}else{
				$final_amt = $amt;
			}
			$final_amt = round($final_amt,2);
			
			$this->set(compact('res','final_amt','payment_res'));
			$this->render("payment_screen");
		}	
	}
	
	public function drChangeCustomer($recipt_id){
		$recipt = "t_product_receipts";
		$kiosk_product_sale = "t_kiosk_product_sales";
		$payment_table = "t_payment_details";
	
		$ProductReceiptTable = TableRegistry::get($recipt,[
																'table' => $recipt,
															]);
		$KioskProductSaleTable = TableRegistry::get($kiosk_product_sale,[
																			'table' => $kiosk_product_sale,
																		]);
		$PaymentDetailTable = TableRegistry::get($payment_table,[
																			'table' => $payment_table,
																		]); 
		//echo "hi";die;
		$this->loadModel("Customers");
		$customer_res_query = $this->Customers->find('all',array(
														  'fields' => array('id','fname','country','lname','business')
														  ));
		$customer_res_query = $customer_res_query->hydrate(false);
		if(!empty($customer_res_query)){
			$customer_res = $customer_res_query->toArray();
		}else{
			$customer_res = array();
		}
		$recipt_res_query = $ProductReceiptTable->find('all',array('conditions' => array(
																	  'id'=> $recipt_id,
																	  )));
		$recipt_res_query = $recipt_res_query->hydrate(false);
		if(!empty($recipt_res_query)){
			$recipt_res = $recipt_res_query->first();
		}else{
			$recipt_res = array();
		}
		$orig_bill_amount = $recipt_res['orig_bill_amount'];
		$this->set(compact('orig_bill_amount'));
		if(!empty($recipt_res)){
			$old_customer_id = $recipt_res['customer_id'];
		}
		$sale_res_query = $KioskProductSaleTable->find('all',['conditions' => [
															 'product_receipt_id' => $recipt_id,
															 ]]);
		$sale_res_query = $sale_res_query->hydrate(false);
		if(!empty($sale_res_query)){
			$sale_res = $sale_res_query->toArray();
		}else{
			$sale_res = array();
		}
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
		$this->set(compact('customer_first_name','customer_last_name','customer_bussiness','old_customer_id'));
		if($this->request->is('Post')){
			if(array_key_exists('customer',$this->request->data)){
				$new_customer_id = $this->request->data['customer'];	
			}else{
				$this->Flash->success(__("Please Chosse customer"));
				return $this->redirect(array('action'=>'drChangeCustomer',$recipt_id));
			}
			
			if(empty($new_customer_id)){
				return $this->redirect(array('action'=>'drChangeCustomer',$recipt_id));
			}
			$new_customer_res_query = $this->Customers->find('all',array(
															'conditions' => array('id' => $new_customer_id)
														  ));
			$new_customer_res_query = $new_customer_res_query->hydrate(false);
			if(!empty($new_customer_res_query)){
				$new_customer_res = $new_customer_res_query->first();
			}else{
				$new_customer_res = array();
			}
			if($new_customer_id != $old_customer_id){
				if($customer_country[$new_customer_id] != $customer_country[$old_customer_id]){
					$kiosk_product_sale_data = $sale_res;
						if(!empty($kiosk_product_sale_data)){
							$total_sale_price = 0;
							foreach($kiosk_product_sale_data as $s_key => $s_value){
								if(!empty($s_value['discount'])){
									$discount = $s_value['discount'];
									$sale_price = $s_value['sale_price'];
									$after_discount_price = $sale_price - ($sale_price*$discount/100);
									$total_price = $after_discount_price*$s_value['quantity'];
								}else{
									$total_price = $s_value['sale_price']*$s_value['quantity'];
								}	
								$total_sale_price += $total_price;
							}
							if($recipt_res['bulk_discount'] > 0){
								$total_sale_price = $total_sale_price - $total_sale_price*($recipt_res['bulk_discount']/100);
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
													 'agent_id' => $new_customer_res['agent_id']
													 //'vat' => "",
													 //'bill_amount' => $total_sale_price,
													 //'orig_bill_amount' => $total_sale_price,
													 );
						$recipt_entity = $ProductReceiptTable->get($recipt_id);
						$recipt_entity = $ProductReceiptTable->patchEntity($recipt_entity,$product_recipt_data,['validate' => false]);
						if($ProductReceiptTable->save($recipt_entity)){
							$agent_id = $new_customer_res['agent_id'];
							$update_agent_query = "UPDATE $payment_table SET `agent_id` = $agent_id WHERE `product_receipt_id` = $recipt_id";
							
							$conn = ConnectionManager::get('default');
							$stmt = $conn->execute($update_agent_query);
							
							
							$this->Flash->success(__("customer has been changed for Invoice ID $recipt_id"));
							if($this->request->session()->read('Auth.User.group_id') == ADMINISTRATORS || $this->request->session()->read('Auth.User.group_id') == FRANCHISE_OWNER){
								$kiosk_id_to_set = $this->get_kiosk_for_dr_invoice();
								return $this->redirect(array('action'=>"dr_search?kiosk_id=$kiosk_id_to_set&date_type=invoice"));
							}else{
								return $this->redirect(array('action'=>'dr_index'));
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
						$recipt_entity = $ProductReceiptTable->get($recipt_id);
						$recipt_entity = $ProductReceiptTable->patchEntity($recipt_entity,$product_recipt_data,['validate' => false]);
						if($ProductReceiptTable->save($recipt_entity)){
							$agent_id = $new_customer_res['agent_id'];
							$update_agent_query = "UPDATE $payment_table SET `agent_id` = $agent_id WHERE `product_receipt_id` = $recipt_id";
							
							$conn = ConnectionManager::get('default');
							$stmt = $conn->execute($update_agent_query);
							if($this->request->session()->read('Auth.User.group_id') == ADMINISTRATORS || $this->request->session()->read('Auth.User.group_id') == FRANCHISE_OWNER){
								$kiosk_id_to_set = $this->get_kiosk_for_dr_invoice();
								$this->Flash->success(__("customer has been changed for Invoice ID $recipt_id"));
								return $this->redirect(array('action'=>"dr_search?kiosk_id=$kiosk_id_to_set&date_type=invoice"));
							}else{
								$this->Flash->success(__("customer has been changed for Invoice ID $recipt_id"));
								return $this->redirect(array('action'=>'dr_index'));
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
					$recipt_entity = $ProductReceiptTable->get($recipt_id);
					$recipt_entity = $ProductReceiptTable->patchEntity($recipt_entity,$product_recipt_data,['validate' => false]);
					if($ProductReceiptTable->save($recipt_entity)){
						$agent_id = $new_customer_res['agent_id'];
							$update_agent_query = "UPDATE $payment_table SET `agent_id` = $agent_id WHERE `product_receipt_id` = $recipt_id";
							
							$conn = ConnectionManager::get('default');
							$stmt = $conn->execute($update_agent_query);
							if($this->request->session()->read('Auth.User.group_id') == ADMINISTRATORS || $this->request->session()->read('Auth.User.group_id') == FRANCHISE_OWNER){
							$kiosk_id_to_set = $this->get_kiosk_for_dr_invoice();
							$this->Flash->success(__("customer has been changed for Invoice ID $recipt_id"));
							return $this->redirect(array('action'=>"dr_search?kiosk_id=$kiosk_id_to_set&date_type=invoice"));
						}else{
							$this->Flash->success(__("customer has been changed for Invoice ID $recipt_id"));
							return $this->redirect(array('action'=>'dr_index'));
						}
					}
				}
			}
		}
		
		
		$this->set(compact('customer_Arr','recipt_id','old_customer_id'));
	}
	
	public function drSearchCustomer(){
		$recipt = "t_product_receipts";
		$ProductReceiptTable = TableRegistry::get($recipt,[
																'table' => $recipt,
															]);
		//pr($this->request->query);die;
		$recipt_id = $this->request->query['recipt_id'];
		$search_kw = $this->request->query['search_kw'];
		$id = $this->request->query['id'];
		$old_customer_id = $this->request->query['old_customer_id'];
		$customer_res_query = $this->Customers->find('all',array(
														'conditions' => array('id' => $old_customer_id),
														  'fields' => array('id','fname','country','lname','business')
														  ));
		$customer_res_query  = $customer_res_query->hydrate(false);
		if(!empty($customer_res_query)){
			$customer_res = $customer_res_query->first();
		}else{
			$customer_res = array();
		}
		
		$recipt_res_query = $ProductReceiptTable->find('all',array('conditions' => array(
																	  'id'=> $recipt_id,
																	  )));
		$recipt_res_query = $recipt_res_query->hydrate(false);
		if(!empty($recipt_res_query)){
			$recipt_res = $recipt_res_query->first();
		}else{
			$recipt_res = array();
		}
		$orig_bill_amount = $recipt_res['orig_bill_amount'];
		$this->set(compact('orig_bill_amount'));
		
		
		if(!empty($customer_res)){
			$customer_first_name = $customer_res['fname'];
			$customer_last_name = $customer_res['lname'];
			$customer_bussiness = $customer_res['business'];
		}
		if(!empty($search_kw) || !empty($id)){
			$conditionArr = array();
			if(!empty($search_kw)){
				$search_kw = trim($search_kw);
				//$conditionArr['Customer.brand like'] =  strtolower("%$searchKW%");
				$conditionArr ['OR']['fname like'] = strtolower("%$search_kw%");
				$conditionArr ['OR']['email like'] = strtolower("%$search_kw%");
				$conditionArr ['OR']['mobile like'] = strtolower("%$search_kw%");
				$conditionArr ['OR']['business  like'] = strtolower("%$search_kw%");
			}
			if(!empty($id)){
				$conditionArr ['OR']['id'] = $id;
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
	
	
	public function searchCustomer(){
		//pr($this->request->query);die;
        $kiosk_id = $this->request->query['kiosk_id'];
		$recipt_id = $this->request->query['recipt_id'];
		$search_kw = $this->request->query['search_kw'];
		$id = $this->request->query['id'];
		$old_customer_id = $this->request->query['old_customer_id'];
		$customer_res_query = $this->Customers->find('all',array(
														'conditions' => array('id' => $old_customer_id),
														  'fields' => array('id','fname','country','lname','business')
														  ));
		$customer_res_query  = $customer_res_query->hydrate(false);
		if(!empty($customer_res_query)){
			$customer_res = $customer_res_query->first();
		}else{
			$customer_res = array();
		}
		
		if(!empty($kiosk_id)){
			$recipt = "kiosk_{$kiosk_id}_product_receipts";
			$kiosk_product_sale = "kiosk_{$kiosk_id}_product_sales";
			$payment_table = "kiosk_{$kiosk_id}_payment_details";
		}else{
			$recipt = "product_receipts";
			$kiosk_product_sale = "kiosk_product_sales";
			$payment_table = "payment_details";
		}
		$ProductReceiptTable = TableRegistry::get($recipt,[
															'table' => $recipt,
														  ]);
		
		$recipt_res_query = $ProductReceiptTable->find('all',array('conditions' => array(
																	  'id'=> $recipt_id,
																	  )));
		//pr($recipt_res_query);die;
		$recipt_res_query = $recipt_res_query->hydrate(false);
		if(!empty($recipt_res_query)){
			$recipt_res = $recipt_res_query->first();
		}else{
			$recipt_res = array();
		}
		
		$orig_bill_amount = $recipt_res['orig_bill_amount'];
		$this->set(compact('orig_bill_amount'));
		
		
		if(!empty($customer_res)){
			$customer_first_name = $customer_res['fname'];
			$customer_last_name = $customer_res['lname'];
			$customer_bussiness = $customer_res['business'];
		}
		if(!empty($search_kw) || !empty($id)){
			$conditionArr = array();
			if(!empty($search_kw)){
				$search_kw = trim($search_kw);
				//$conditionArr['Customer.brand like'] =  strtolower("%$searchKW%");
				$conditionArr ['OR']['fname like'] = strtolower("%$search_kw%");
				$conditionArr ['OR']['email like'] = strtolower("%$search_kw%");
				$conditionArr ['OR']['mobile like'] = strtolower("%$search_kw%");
				$conditionArr ['OR']['business  like'] = strtolower("%$search_kw%");
			}
			if(!empty($id)){
				$conditionArr ['OR']['id'] = $id;
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
		$this->set(compact('customer_first_name','customer_last_name','customer_bussiness','old_customer_id','recipt_id','kiosk_id'));
		$this->render('change_customer');
	}
	
	public function changeCustomer($recipt_id,$passed_kiosk_id=""){
		//echo "hi";die;
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
			$recipt = "kiosk_{$kiosk_id}_product_receipts";
			$kiosk_product_sale = "kiosk_{$kiosk_id}_product_sales";
			$payment_table = "kiosk_{$kiosk_id}_payment_details";
		}else{
			$recipt = "product_receipts";
			$kiosk_product_sale = "kiosk_product_sales";
			$payment_table = "payment_details";
		}
		$ProductReceiptTable = TableRegistry::get($recipt,[
															'table' => $recipt,
														  ]);
		$KioskProductSaleTable = TableRegistry::get($kiosk_product_sale,[
																			'table' => $kiosk_product_sale,
																		]);
		$PaymentDetailTable = TableRegistry::get($payment_table,[
																	'table' => $payment_table,
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
		$recipt_res_query = $ProductReceiptTable->find('all',array('conditions' => array(
																	  'id'=> $recipt_id,
																	  )));
		//pr($recipt_res_query);die;
		$recipt_res_query = $recipt_res_query->hydrate(false);
		if(!empty($recipt_res_query)){
			$recipt_res = $recipt_res_query->first();
		}else{
			$recipt_res = array();
		}
		
		$orig_bill_amount = $recipt_res['orig_bill_amount'];
		$this->set(compact('orig_bill_amount'));
		$sale_res_query = $KioskProductSaleTable->find('all',['conditions' => [
															 'product_receipt_id' => $recipt_id,
															 ]]);
		$sale_res_query = $sale_res_query->hydrate(false);
		if(!empty($sale_res_query)){
			$sale_res = $sale_res_query->toArray();
		}else{
			$sale_res = array();
		}
		
		$payment_res_query = $PaymentDetailTable->find('all',['conditions' => [
															 'product_receipt_id' => $recipt_id,
															 ]]);
		$payment_res_query = $payment_res_query->hydrate(false);
		if(!empty($payment_res_query)){
			$payment_res = $payment_res_query->toArray();
		}else{
			$payment_res = array();
		}
		
		if(!empty($recipt_res)){
			$old_customer_id = $recipt_res['customer_id'];
			
		}
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
		$this->paginate = array(
							'conditions' => array('system_user' => 0),
							'limit' => 50
							);
		$customers = $this->paginate('Customers');
		$this->set(compact('customers'));
		$this->set(compact('customer_first_name','customer_last_name','customer_bussiness','old_customer_id'));
		if($this->request->is('Post') && array_key_exists('customer',$this->request->data)){
			$new_customer_id = $this->request->data['customer'];
			$new_customer_res_query = $this->Customers->find('all',array(
															'conditions' => array('id' => $new_customer_id),
														  'recursive'=>-1
														  ));
			$new_customer_res_query = $new_customer_res_query->hydrate(false);
			if(!empty($new_customer_res_query)){
				$new_customer_res = $new_customer_res_query->first();
			}else{
				$new_customer_res = array();
			}
			if($new_customer_id != $old_customer_id){
				if($customer_country[$new_customer_id] != $customer_country[$old_customer_id]){
					$kiosk_product_sale_data = $sale_res;
						if(!empty($kiosk_product_sale_data)){
							$total_sale_price = 0;
							foreach($kiosk_product_sale_data as $s_key => $s_value){
								if(!empty($s_value['discount'])){
									$discount = $s_value['discount'];
									$sale_price = $s_value['sale_price'];
									$after_discount_price = $sale_price - ($sale_price*$discount/100);
									$total_price = $after_discount_price*$s_value['quantity'];
								}else{
									$total_price = $s_value['sale_price']*$s_value['quantity'];
								}	
								$total_sale_price += $total_price;
							}
							if($recipt_res['bulk_discount'] > 0){
								$total_sale_price = $total_sale_price - $total_sale_price*($recipt_res['bulk_discount']/100);
							}
						}
					if($customer_country[$new_customer_id] == "OTH"){  // if changed to other country which mens no vat
						//pr($recipt_res);die;
						$selected_cutomer_id = $this->request->data['customer'];
						$final_amt = round($total_sale_price,2);
						$res = $recipt_res;
						$this->set(compact('res','final_amt','selected_cutomer_id','payment_res'));
						$this->render("payment_screen_cst_change");
					}else{ // same country mns will have vat
						$vat = $this->VAT;
						$vat_amount = $total_sale_price*($vat/100);
						$after_vat_value = $total_sale_price+$vat_amount;
						
						$selected_cutomer_id = $this->request->data['customer'];
						$final_amt = round($after_vat_value,2);
						$res = $recipt_res;
						$this->set(compact('res','final_amt','selected_cutomer_id','payment_res'));
						$this->render("payment_screen_cst_change");
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
													 'agent_id' => $new_customer_res['agent_id'],
													);
					$ProductReceiptTable->behaviors()->load('Timestamp');
					$recipt_entity = $ProductReceiptTable->get($recipt_id);
					$recipt_entity = $ProductReceiptTable->patchEntity($recipt_entity,$product_recipt_data,['validate' => false]);
					
					$agent_id = $new_customer_res['agent_id'];
					$update_agent_query = "UPDATE $payment_table SET `agent_id` = $agent_id WHERE `product_receipt_id` = $recipt_id";
					
					$conn = ConnectionManager::get('default');
					$stmt = $conn->execute($update_agent_query); 
					
					
					//pr($recipt_entity);die;
					if($ProductReceiptTable->save($recipt_entity)){
                        if($this->request->session()->read('Auth.User.group_id') == ADMINISTRATORS || $this->request->session()->read('Auth.User.group_id') == FRANCHISE_OWNER){
                            $kiosk_id_to_set = $this->get_kiosk_for_invoice();
                            $this->Flash->success(__("customer has been changed for Invoice ID $recipt_id"));
                            return $this->redirect(array('action'=>"search?kiosk_id=$kiosk_id_to_set&date_type=invoice"));
                        }else{
                            $this->Flash->success(__("customer has been changed for Invoice ID $recipt_id"));
							return $this->redirect(array('action'=>'index'));
                        }
					}
				}
			}
		}elseif($this->request->is('Post') && array_key_exists('payment',$this->request->data)){
			$selected_customer = $this->request->data['selected_customer'];
			$finalAmt = $this->request->data['sale_amount'];
			$payArr = $this->request->data['payment'];
			$payment_total = array_sum($payArr);
			$payment_total = round($payment_total,2);
			$finalAmt = round($finalAmt,2);
			if($payment_total != $finalAmt){
				$final_amt = $finalAmt;
				$res = $recipt_res;
				$selected_cutomer_id = $selected_customer;
				$this->set(compact('res','final_amt','selected_cutomer_id','payment_res'));
				$this->render("payment_screen_cst_change");
				$this->Flash->errror(__("amount is not matching"));
				return $this->redirect(array('action'=>'payment_screen_cst_change'));
			}
			//foreach($recipt_res as $s_key1 => $value1){
				foreach($payment_res as $y => $data){
					if(array_key_exists($data['id'],$payArr)){
						if($payArr[$data['id']] != $data['amount']){
							$pay_data = array(
												'id' => $data['id'],
												'amount' => $payArr[$data['id']]
												);
							//pr($pay_data);die;
							$PaymentDetailTable->behaviors()->load('Timestamp');
							$pay_entity = $PaymentDetailTable->get($data['id']);
							$pay_entity = $PaymentDetailTable->patchEntity($pay_entity,$pay_data,['validate' => false]);
							$PaymentDetailTable->save($pay_entity);
						}
					}
				}
			//}
			$new_customer_result_query = $this->Customers->find('all',array(
															'conditions' => array('id' => $selected_customer),
														  ));
			$new_customer_result_query = $new_customer_result_query->hydrate(false);
			if(!empty($new_customer_result_query)){
				$new_customer_result = $new_customer_result_query->first();
			}else{
				$new_customer_result = array();
			}
			//pr($new_customer_result);die;
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
														'vat' => $vat,
														'bill_amount' => $finalAmt,
														'orig_bill_amount' => $finalAmt,
														'agent_id' => $new_customer_result['agent_id'],
														);
			$ProductReceiptTable->behaviors()->load('Timestamp');
			$recipt_entity = $ProductReceiptTable->get($recipt_id);
			$recipt_entity = $ProductReceiptTable->patchEntity($recipt_entity,$product_recipt_data,['validate' => false]);
			if($ProductReceiptTable->save($recipt_entity)){
				$agent_id = $new_customer_result['agent_id'];
					$update_agent_query = "UPDATE $payment_table SET `agent_id` = $agent_id WHERE `product_receipt_id` = $recipt_id";
					
					$conn = ConnectionManager::get('default');
					$stmt = $conn->execute($update_agent_query);
				if($this->request->session()->read('Auth.User.group_id') == ADMINISTRATORS || $this->request->session()->read('Auth.User.group_id') == FRANCHISE_OWNER){
					$kiosk_id_to_set = $this->get_kiosk_for_invoice();
					$this->Flash->success(__("customer has been changed for Invoice ID $recipt_id"));
					return $this->redirect(array('action'=>"search?kiosk_id=$kiosk_id_to_set&date_type=invoice"));
				}else{
					$this->Flash->success(__("customer has been changed for Invoice ID $recipt_id"));
					return $this->redirect(array('action'=>'index'));
				}
					//$this->Flash->success(__("customer has been changed for Invoice ID $recipt_id"));
					//return $this->redirect(array('action'=>'index'));
			}
			
		}else{
			$this->set(compact('customer_Arr','recipt_id','old_customer_id','kiosk_id'));
		}
	}
	
	public function drMakePayment($id = null) {
		$CURRENCY_TYPE = Configure::read('CURRENCY_TYPE');
		if($this->request->session()->read('Auth.User.group_id') == ADMINISTRATORS || $this->request->session()->read('Auth.User.group_id') == FRANCHISE_OWNER){
			$kiosk_id_to_set = $this->get_kiosk_for_dr_invoice();
			if(!empty($kiosk_id_to_set)){
				if($kiosk_id_to_set == 10000){
					$productSource = "products";
					//$productSalesSource = "kiosk_product_sales";
					//$recipt_source = "product_receipts";
					//$payment_source = "payment_details";
				}else{
					$kiosk_id = $kiosk_id_to_set;
					$productSource = "kiosk_{$kiosk_id}_products";
					//$productSalesSource = "kiosk_{$kiosk_id}_product_sales";
					//$recipt_source = "kiosk_{$kiosk_id}_product_receipts";
					//$payment_source = "kiosk_{$kiosk_id}_payment_details";
				}
				
				$ProductTable = TableRegistry::get($productSource,[
																'table' => $productSource,
															]);
				
				//$this->KioskProductSale->setSource($productSalesSource);
				//
				//$this->ProductReceipt->setSource($recipt_source);
				//
				//$this->PaymentDetail->setSource($payment_source);
				
			}
		}else{
            $kiosk_id = $this->request->Session()->read('kiosk_id');
            $productSource = "kiosk_{$kiosk_id}_products";
            $ProductTable = TableRegistry::get($productSource,[
																'table' => $productSource,
															]);
            
        }
		
		$productSalesSource = "t_kiosk_product_sales";
		$recipt_source = "t_product_receipts";
		$payment_source = "t_payment_details";
		
		$KioskProductSaleTable = TableRegistry::get($productSalesSource,[
																'table' => $productSalesSource,
															]);
		$ProductReceiptTable = TableRegistry::get($recipt_source,[
																'table' => $recipt_source,
															]);
		$PaymentDetailTable = TableRegistry::get($payment_source,[
																'table' => $payment_source,
															]);
		
		//pr($this->request);die;
		if (!$ProductReceiptTable->exists($id)) {
			throw new NotFoundException(__('Invalid product receipt'));
		}
		$session_basket = $this->request->Session()->read('Basket');
		//pr($this->request);
		//pr($session_basket);die;
		$productIdArr = array();
		foreach($session_basket as $productId=>$productDetail){
			$productIdArr[$productId] = $productId;
		}
		if(!empty($productIdArr)){
			if(empty($productIdArr)){
				$productIdArr = array(0=>null);
			}
			$productQuantityCheck_query = $ProductTable->find('list',[
																'conditions'=>['id IN'=>$productIdArr],
																'keyField'=>'id',
																'valueField'=>'quantity'
															   ]
														);
			$productQuantityCheck_query = $productQuantityCheck_query->hydrate(false);
			if(!empty($productQuantityCheck_query)){
				$productQuantityCheck = $productQuantityCheck_query->toArray();
			}else{
				$productQuantityCheck = array();
				
			}
			$quantityError = array();
			foreach($productQuantityCheck as $productId=>$quantity){
				if($quantity==0){
					$quantityError[] = "Product with id:$productId does not have enough quantity, please choose a different product";
					$this->request->Session()->delete("Basket.$productId");
				}
			}
			
			$quantityErrStr ='';
			if(count($quantityError)>0){
				$quantityErrStr = implode("<br/>",$quantityError);
				$this->request->Session()->write('quantityError',$quantityErrStr);
				return $this->redirect(array('controller'=>'kiosk_product_sales','action'=>'dr_edit_receipt',$id));
			}
		}else{
			$quantityErrStr = "Please add products to the basket";
			$this->request->Session()->write('quantityError',$quantityErrStr);
			return $this->redirect(array('controller'=>'kiosk_product_sales','action'=>'dr_edit_receipt',$id));
		}
		$productName_query = $ProductTable->find('list',[
												   'keyField'=>'id',
												   'valueField'=>'product'
												  ]);
		$productName_query = $productName_query->hydrate(false);
		if(!empty($productName_query)){
			$productName = $productName_query->toArray();
		}else{
			$productName = array();
		}
		//$this->ProductReceipt->recursive=1;
		$options = array('conditions' => array('id' => $id));
		$ProductReceipt_query = $ProductReceiptTable->find('all', $options);
		$ProductReceipt_query = $ProductReceipt_query->hydrate(false);
		if(!empty($ProductReceipt_query)){
			$ProductReceipt = $ProductReceipt_query->first();
		}else{
			$ProductReceipt = array();
		}
       // pr($ProductReceipt['kiosk_id']);die;
		$this->set('ProductReceipt',$ProductReceipt);
		$this->set(compact('productName'));
		
		if ($this->request->is(array('post', 'put'))) {
			//pr($this->request);die;
			if(array_key_exists("cancel",$this->request->data)){
				if($this->request->data['cancel'] == "Cancel"){
					$uId = $this->request->params['pass'][0];
					return $this->redirect(array('controller'=>'product_receipts','action'=>'dr_edit',$uId));
					die;
				}
			}
			$productReceiptDetails_query = $ProductReceiptTable->Find('all',array(
									'conditions' => array('id'=>$id)
									)
								 );
			$productReceiptDetails_query = $productReceiptDetails_query->hydrate(false);
			if(!empty($productReceiptDetails_query)){
				$productReceiptDetails = $productReceiptDetails_query->first();
			}else{
				$productReceiptDetails = array();
			}
			$amountToPay = $this->request['data']['final_amount'];
			$totalPaymentAmount = 0;
			$amountDesc = array();
			$countCycles = 0;
			$error = '';
			$errorStr = '';
			$countCycles = 0;
			foreach($this->request['data']['Payment']['Amount'] as $key => $paymentAmount){
				$totalPaymentAmount+= (float)$paymentAmount;
				$paymentDescription = $this->request['data']['Payment']['Description'][$key];
				if(!empty($paymentDescription) && !empty($paymentAmount)){
					$countCycles++;
				}
				if(empty($paymentDescription) && !empty($paymentAmount)){
					$error[] = "Sale could not be created. Payment description must be entered";
					break;
				}
			}
			//pr($countCycles);die;
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
					$this->Flash->error("$errorStr",['escape'=>false]);
					return $this->redirect(array('action'=>'dr_make_payment',$id));
				}
				
			$counter = 0;
			//pr($this->request);die;
				if($this->request['data']['Payment']['Payment_Method'][0] == "On Credit" && $countCycles==1){
					//echo'hi';die;
					$paymentDetailData = array(
						'product_receipt_id' => $id,
						'payment_method' => $this->request['data']['Payment']['Payment_Method'][0],
						//'description' => $this->request['data']['Payment']['Description'][0],
						'amount' => $amountToPay,
						'payment_status' => 0,
                        'kiosk_id' => $ProductReceipt['kiosk_id'],
						'status' => 1,
						'agent_id' => $ProductReceipt['agent_id']
						   );
					//pr($paymentDetailData);die;
					$PaymentDetailTable->behaviors()->load('Timestamp');
					$entity = $PaymentDetailTable->newEntity(['validate'=>false]);
					$entity = $PaymentDetailTable->patchEntity($entity,$paymentDetailData,['validate'=>false]);
					//pr($entity);die;
					if($PaymentDetailTable->save($entity)){
						//echo'hi';die;
						$counter++;
					}
				}else{
					//echo'bye';die;
					foreach($this->request['data']['Payment']['Amount'] as $key => $paymentAmount){
					$paymentMethod = $this->request['data']['Payment']['Payment_Method'][$key];
					$paymentDescription = $this->request['data']['Payment']['Description'][$key];
					
						if($paymentMethod == "On Credit"){
							$payment_status = 0;
						}else{
							$payment_status = 1;
						}
						
						if(!empty($paymentAmount) && $paymentDescription){
							$paymentDetailData = array(
									'product_receipt_id' => $id,
									'payment_method' => $paymentMethod,
									//'description' => $paymentDescription,
									'amount' => $paymentAmount,
									'payment_status' => $payment_status,
                                    'kiosk_id' => $ProductReceipt['kiosk_id'],
									'status' => 1,
									'agent_id' => $ProductReceipt['agent_id'],
									   );
							$PaymentDetailTable->behaviors()->load('Timestamp');
							$entity = $PaymentDetailTable->newEntity();
							$entity = $PaymentDetailTable->patchEntity($entity,$paymentDetailData,['validate'=>false]);
							if($PaymentDetailTable->save($entity)){
								$counter++;
							}
						}
					}
				}
			
			if($counter>0){
				return $this->redirect(array('controller'=>'kiosk_product_sales','action'=>'dr_save_invoice_edit_detail',$id));;
			}else{
				$flashMessage = ("Sale could not be created. Please try again");
				$this->Flash->error($flashMessage);
				return $this->redirect(array('action'=>'dr_make_payment', $id));
			}
		}
	}
	
	public function delete($id = null) {
		$this->request->allowMethod('post', 'delete');
		$ProductReceipts = $this->ProductReceipts->get($id);
		
		if ($this->ProductReceipts->delete($ProductReceipts)) { //$this->ProductReceipt->delete()
			$this->Flash->success(__('The product receipt has been deleted.'));
		}else{
			$this->Flash->error(__('The product receipt could not be deleted. Please, try again.'));
		}
		
		return $this->redirect(array('action' => 'index'));
	}
	
	public function processBulkInvoices(){
		$kiosk_id = $this->request->query["kiosk"];
		
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
		
		
		
		$start_date = $this->request->query["start_date"];
		$end_date = $this->request->query["end_date"];
		$payment_date_type = $this->request->query["payment_date_type"];
		
		$customer_number = $this->request->query["customer_number"];
		$acc_manger = $this->request->query["acc_manger"];
		
		if($acc_manger == "undefined"){
			$acc_manger = "";
		}
		
		$conditionArr = $searchCriteria = array();
		$conditionArr['payment_method like '] =  strtolower("%on credit%");
		$searchCriteria['customer_id'] = $customer_number;
		if(!empty($acc_manger)){
			//$conditionArr['agent_id'] = $acc_manger;
		}
		
		if($payment_date_type == "payment"){
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
				$Receipts_query = $receiptTable->find('list',array(
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
				$conditionArr['product_receipt_id IN'] = $Receipts;
		}
		
		
		
		
		
		if(!empty($start_date) && !empty($end_date)){
				if($payment_date_type == "payment"){
								
				}else{
					$searchCriteria[] = array(
							 "created >" => date('Y-m-d', strtotime($this->request->query['start_date'])),
							 "created <" => date('Y-m-d', strtotime($this->request->query['end_date']. ' +1 Days')),			
								);							
				}
		}	
				$cutomerReceipts_query = $receiptTable->find('all',array('fields' => array('id'),
															'conditions' => $searchCriteria));
					$cutomerReceipts_query = $cutomerReceipts_query->hydrate(false);
					if(!empty($cutomerReceipts_query)){
						$cutomerReceipts = $cutomerReceipts_query->toArray();
					}else{
						$cutomerReceipts = array();
					}
					
					//pr($cutomerReceipts);die;
					$receiptIDs = array();
					$conditionArr['product_receipt_id IN'] = 0;
					if( count($cutomerReceipts) ){
						//echo $cutomerReceipts['ProductReceipt']['id'];
						foreach($cutomerReceipts as $cutomerReceipt){
							$receiptIDs[] = $cutomerReceipt['id'];
						}
						if(empty($receiptIDs)){
							$receiptIDs = array(0 => null);
						}
						$conditionArr['product_receipt_id IN'] = $receiptIDs;
					}
		
		
		$agent_id = 0;
		if(array_key_exists('acc_manger',$this->request->query) && !empty($this->request->query['acc_manger'])){
			$agent_id = $this->request->query['acc_manger'];
			$agent_cust_res = $this->Customers->find("list",['conditions' => [
														   "agent_id" => $agent_id,
														   ],
															'keyField' => "id",
															"valueField" => "agent_id",
															])->toArray();
			
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
				$cutomerReceipts_query = $receiptTable->find('all',array('fields' => array('id'),
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
				$conditionArr['product_receipt_id IN'] = 0;
				if( count($cutomerReceipts) ){
					//echo $cutomerReceipts['ProductReceipt']['id'];
					foreach($cutomerReceipts as $cutomerReceipt){
						$receiptIDs[] = $cutomerReceipt['id'];
					}
					if(empty($receiptIDs)){
						$receiptIDs = array('0'=>null);
					}
					$conditionArr['product_receipt_id IN'] = $receiptIDs;
				}
						 
			}
			
			//$conditionArr['agent_id'] = $agent_id;
		}
		
		//pr($conditionArr);die;
		$pay_res = $paymentTable->find("all",[
				'conditions' => [$conditionArr],
				 'order' => [
                                        'product_receipt_id' => 'desc'
                                    ],
				])->toArray();
		//pr($pay_res);die;
		//$pay_res_query = $pay_res_query->hydrate(false);
		//if(!empty($pay_res_query)){
		//	$pay_res = $pay_res_query->toArray();
		//}else{
		//	$pay_res = array();
		//}
		$total_amount = 0;
		foreach($pay_res as $s_key => $s_value){
			$recipt_ids[] = $s_value->product_receipt_id;
			$total_amount += $s_value->amount;
		}
		
		
		if(empty($recipt_ids)){
            $recipt_ids = array(0 => null);
        }
		$recipt_table_data_query = $receiptTable->find('all',[
								   'conditions' => ['id IN' => $recipt_ids]
								   ]);
		$recipt_table_data_query = $recipt_table_data_query->hydrate(false);
		if(!empty($recipt_table_data_query)){
			$recipt_table_data = $recipt_table_data_query->toArray();
		}else{
			$recipt_table_data = array();
		}
		
		foreach($recipt_table_data as $receiptDetail){
			$reciptTableData[$receiptDetail['id']] = $receiptDetail;
		}
		$recipt_table_data = array();
		if(!empty($reciptTableData)){
			$recipt_table_data = $reciptTableData;
		}
		
		$kiosks = $this->Kiosks->find('list',[
                                                'keyField' => 'id',
                                                'valueField' => 'name',
                                                'conditions' => ['Kiosks.status' => 1],
                                                'order' => ['Kiosks.name asc']
                                            ])->toArray();
		
		$customer_data = $this->Customers->find('all',
                                                         ['conditions' => [
                                                                           'Customers.id' => $customer_number
                                                                           ],
                                                         ])->toArray();
		
		$agents_query = $this->Agents->find('list');
		$agents_query = $agents_query->hydrate(false);
		
		if(!empty($agents_query)){
			$agents = $agents_query->toArray();
		}
		// get credit details
		if(!empty($kiosk_id)){
			if($kiosk_id == 10000){
				$CreditReceiptSource = "credit_receipts";
				$CreditPaymentDetailSource = "credit_payment_details";
			}else{
				$CreditReceiptSource = "kiosk_{$kiosk_id}_credit_receipts";
				$CreditPaymentDetailSource = "kiosk_{$kiosk_id}_credit_payment_details";
			}
			
		}else{
			$CreditReceiptSource = "credit_receipts";
			$CreditPaymentDetailSource = "credit_payment_details";
		}
		
		$CreditReceiptTable = TableRegistry::get($CreditReceiptSource,[
															   'table' => $CreditReceiptSource,
														    ]);
		$CreditPaymentDetailTable = TableRegistry::get($CreditPaymentDetailSource,[
                                                                                    'table' => $CreditPaymentDetailSource,
                                                                                ]);
		
		$credit_receipt = $CreditReceiptTable->find('all',[
																	  'conditions' => ['customer_id' => $customer_number]
																	  ])->toArray();
		$credit_data = $credit_pay_res = array();
		if(!empty($credit_receipt)){
			 $credit_receipt_ids = $credit_conditionArr = array();
			foreach($credit_receipt as $c_key => $c_value){
				$credit_data[$c_value->id] = $credit_receipt[$c_key];
				$credit_recipt_ids[] = $c_value->id;
			}
			$credit_conditionArr['credit_receipt_id IN'] =  $credit_recipt_ids;
			$credit_conditionArr['payment_method like '] =  strtolower("%on credit%");
			$credit_pay_res = $CreditPaymentDetailTable->find("all",[
												   'conditions' => $credit_conditionArr,
												   ])->toArray();
			//pr($credit_pay_res);die;
		}
		$this->set(compact("total_amount","pay_res","kiosks","kiosk_id","recipt_table_data","customer_data","agents","credit_pay_res","credit_data"));
	}

	public function processCart(){
		
		$users = $this->Users->find("list",['keyField' => 'id',
								   'valueField' => 'f_name',
								   ])->toArray();
		
		if(array_key_exists("ids",$this->request->query) && !empty($this->request->query['ids'])){
			$ids = $this->request->query['ids'];
		}else{
			$msg = array("msg" => "NO Invoice Selected");
			echo json_encode($msg);die;
		}
		
		if(array_key_exists("total_amt",$this->request->query) && !empty($this->request->query['total_amt'])){
			$total_amt = $this->request->query['total_amt'];
		}else{
			$msg = array("msg" => "NO Final Amt Found");
			echo json_encode($msg);die;
		}
		
		if(array_key_exists("payment_method",$this->request->query) && !empty($this->request->query['payment_method'])){
			$payment_method = $this->request->query['payment_method'];
		}else{
			$msg = array("msg" => "NO Payment Method Selected");
			echo json_encode($msg);die;
		}
		
		if(array_key_exists("kiosk_id",$this->request->query) && !empty($this->request->query['kiosk_id'])){
			$kiosk_id = $this->request->query['kiosk_id'];
		}else{
			$msg = array("msg" => "NO kiosk_id Found");
			echo json_encode($msg);die;
		}
		
		if(array_key_exists("date",$this->request->query) && !empty($this->request->query['date'])){
			$date = $this->request->query['date'];
		}else{
			$date = "";
		}
		
		if(array_key_exists("memo_content",$this->request->query) && !empty($this->request->query['memo_content'])){
			$memo_content = trim($this->request->query['memo_content'])." | ".$total_amt;
		}else{
			$memo_content = ""." | ".$total_amt;
		}
		
		
		
		$ids_arr = explode(",",$ids);
		
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
		
		
		$pay_res = $paymentTable->find("all",[
								   'conditions' => [
									'id IN' => $ids_arr,
								   ],
								   'order' => [
                                        'amount' => 'asc'
                                    ],
								   ])->toArray();
		
		$timestamp = strtotime(date("Y-m-d"));
		
		$user_id = $this->request->Session()->read('Auth.User.id');
		if(!empty($pay_res)){
			$outsatanding_amt = $org_amt = $counter = $remaining_amt = 0;
			foreach($pay_res as $key => $value){
				if(!empty($date)){
					$recipt_id = $value->product_receipt_id;
					$recipt_res = $receiptTable->find("all",[
											   'conditions' => ['id' => $recipt_id],
											   ])->toArray();
					if(!empty($recipt_res)){
						$recipt_created = $recipt_res[0]['created'];
						if(strtotime($recipt_created) > strtotime($date)){
							$date = date("Y-m-d G:i:s",strtotime($recipt_created));
						}else{
							$date = date("Y-m-d G:i:s",strtotime($date));
						}
					}
				}
				$amount = $value->amount;
				if($counter == 0){
					$org_amt = $total_amt;
					$remaining_amt = $total_amt-$amount;
				}else{
					$org_amt = $remaining_amt;
					
						$remaining_amt = (string)$remaining_amt;
						$remaining_amt = (float)$remaining_amt;
						
						$amount = (string)$amount;
						$amount = (float)$amount;
					
					$remaining_amt = $remaining_amt-$amount;
				}
				
				if($remaining_amt>=0){
					$payment_id = $value->id;
					if(!empty($date)){
						$data_to_save = array(
										  "created" => date("Y-m-d G:i:s",strtotime($date)),
										  "payment_method" => $payment_method,
										  "pmt_ref_id" => $timestamp);
					}else{
						$data_to_save = array(
										  "created" => date("Y-m-d G:i:s"),
										  "pmt_ref_id" => $timestamp,
										  "payment_method" => $payment_method)
						;
					}
					
					$entity = $paymentTable->get($payment_id);
					$entity = $paymentTable->patchEntity($entity,$data_to_save);
					$paymentTable->save($entity);
					
					$logData = array(
								 'user_id'=>$user_id,
								 'pmt_id'=>(int)$payment_id,
								 'kiosk_id' => $kiosk_id,
								 'old_pmt_method'=>"On Credit",
								 'pmt_method'=>$payment_method,
								 'receipt_id'=>$value->product_receipt_id,
								 'adjusted_by' => 1,
								 'memo'=>$memo_content,
								 'receipt_type' => 1,
								);
			
					$newLog = $this->PmtLogs->newEntity($logData);
					$patchLog = $this->PmtLogs->patchEntity($newLog,$logData);
					$this->PmtLogs->save($patchLog);
					
					$logid = $patchLog->id;
					$log_created = $patchLog->created;
					$firstName = $users[$user_id];
					if(empty($firstName)){
						$firstName = "Missing Name";
					}
					$str = date("d/m/Y h:i",strtotime($log_created)).", ".$firstName.", "."On Credit"."->"."Cash";
					if(!empty($memo_content)){
						$str.="||".$memo_content;
					}
					
					
					$data = array("description" => $str);
					$pay_res = $paymentTable->get($value->id);
					$pay_res = $paymentTable->patchEntity($pay_res,$data);
					$paymentTable->save($pay_res);
					
				}else{
					$outsatanding_amt = $amount - $org_amt;
					
					$payment_id = $value->id;
					if(!empty($date)){
						$data_to_save = array("payment_method" => $payment_method,
										  "amount" => $org_amt,
										  "pmt_ref_id" => $timestamp,
										  "created" => date("Y-m-d G:i:s",strtotime($date)),
										  );
					}else{
						$data_to_save = array("payment_method" => $payment_method,
										  "amount" => $org_amt,
										  "pmt_ref_id" => $timestamp,
										  "created" => date("Y-m-d G:i:s"),
										  );
					}
					
					$entity = $paymentTable->get($payment_id);
					$entity = $paymentTable->patchEntity($entity,$data_to_save);
					$paymentTable->save($entity);
					
					$logData = array(
								 'user_id'=>$user_id,
								 'pmt_id'=>(int)$payment_id,
								 'kiosk_id' => $kiosk_id,
								 'old_pmt_method'=>"On Credit",
								 'pmt_method'=>$payment_method,
								 'receipt_id'=>$value->product_receipt_id,
								 'adjusted_by' => 1,
								 'memo'=>$memo_content,
								 'receipt_type' => 1,
								);
			
					$newLog = $this->PmtLogs->newEntity($logData);
					$patchLog = $this->PmtLogs->patchEntity($newLog,$logData);
					$this->PmtLogs->save($patchLog);
					
					$logid = $patchLog->id;
					$log_created = $patchLog->created;
					$firstName = $users[$user_id];
					if(empty($firstName)){
						$firstName = "Missing Name";
					}
					$str = date("d/m/Y h:i",strtotime($log_created)).", ".$firstName.", "."On Credit"."->"."Cash";
					if(!empty($memo_content)){
						$str.="||".$memo_content;
					}
					
					
					$data = array("description" => $str);
					$pay_res = $paymentTable->get($value->id);
					$pay_res = $paymentTable->patchEntity($pay_res,$data);
					$paymentTable->save($pay_res);
					
					if(!empty($date)){
						$data_to_save1 = array(
										   "payment_method" => "On Credit",
											"amount" => $outsatanding_amt,
											"product_receipt_id" => $value->product_receipt_id,
											"created" => $value->created,
										   );
					}else{
						$data_to_save1 = array(
										   "payment_method" => "On Credit",
											"amount" => $outsatanding_amt,
											"product_receipt_id" => $value->product_receipt_id,
											"created" => $value->created,
										   );
					}
					
					$entity1 = $paymentTable->newEntity();
					$entity1 = $paymentTable->patchEntity($entity1,$data_to_save1);
					$paymentTable->save($entity1);
				}
				$counter++;
			}
			if($counter > 0){
				$msg = array("msg" => "Done");
				echo json_encode($msg);die;
			}
		}
	}
	
	public function processCreditCart(){
		
		$users = $this->Users->find("list",['keyField' => 'id',
								   'valueField' => 'f_name',
								   ])->toArray();
		
		if(array_key_exists("ids",$this->request->query) && !empty($this->request->query['ids'])){
			$ids = $this->request->query['ids'];
		}else{
			$msg = array("msg" => "NO Invoice Selected");
			echo json_encode($msg);die;
		}
		
		if(array_key_exists("credit_id",$this->request->query) && !empty($this->request->query['credit_id'])){
			$credit_id = $this->request->query['credit_id'];
		}else{
			$msg = array("msg" => "NO credit note id found");
			echo json_encode($msg);die;
		}
		
		if(array_key_exists("payment_method",$this->request->query) && !empty($this->request->query['payment_method'])){
			$payment_method = $this->request->query['payment_method'];
		}else{
			$msg = array("msg" => "NO Payment Method Selected");
			echo json_encode($msg);die;
		}
		
		if(array_key_exists("kiosk_id",$this->request->query) && !empty($this->request->query['kiosk_id'])){
			$kiosk_id = $this->request->query['kiosk_id'];
		}else{
			$msg = array("msg" => "NO kiosk_id Found");
			echo json_encode($msg);die;
		}
		
		if(array_key_exists("date",$this->request->query) && !empty($this->request->query['date'])){
			$date = $this->request->query['date'];
		}else{
			$date = "";
		}
		
		if(array_key_exists("memo_content",$this->request->query) && !empty($this->request->query['memo_content'])){
			$memo_content = trim($this->request->query['memo_content']);
		}else{
			$memo_content = "";
		}
		
		$timestamp = strtotime(date("y-m-d"));
		
		if(!empty($kiosk_id)){
			if($kiosk_id == 10000){
				$CreditReceiptSource = "credit_receipts";
				$CreditPaymentDetailSource = "credit_payment_details";
			}else{
				$CreditReceiptSource = "kiosk_{$kiosk_id}_credit_receipts";
				$CreditPaymentDetailSource = "kiosk_{$kiosk_id}_credit_payment_details";
			}
			
		}else{
			$CreditReceiptSource = "credit_receipts";
			$CreditPaymentDetailSource = "credit_payment_details";
		}
		
		$CreditReceiptTable = TableRegistry::get($CreditReceiptSource,[
															   'table' => $CreditReceiptSource,
														    ]);
		$CreditPaymentDetailTable = TableRegistry::get($CreditPaymentDetailSource,[
                                                                                    'table' => $CreditPaymentDetailSource,
                                                                                ]);
		
		$credit_res = $CreditPaymentDetailTable->find("all",[
											   'conditions' => ["id" => $credit_id]
											   ])->toArray();
		$total_amt = 0;
		if(!empty($credit_res)){
			$total_amt = $credit_res[0]->amount;
			$credit_recipt_id = $credit_res[0]->credit_receipt_id;
			$credit_created = $credit_res[0]->created;
		}
		
		$credit_recipt_data = $CreditReceiptTable->find("all",['conditions' => [
														  'id' => $credit_recipt_id,
														  ]])->toArray();
		$credit_recipt_created = "";
		if(!empty($credit_recipt_data)){
			$credit_recipt_created = $credit_recipt_data[0]['created'];
		}
		
		$ids_arr = explode(",",$ids);
		
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
		
		$pay_res = $paymentTable->find("all",[
								   'conditions' => [
									'id IN' => $ids_arr,
								   ],
								   'order' => [
                                        'amount' => 'asc'
                                    ],
								   ])->toArray();
		
		if(!empty($pay_res)){
			$outsatanding_amt = $org_amt = $counter = $remaining_amt = 0;
			$recipt_id_str = "";
			foreach($pay_res as $key => $value){
				$recipt_id = $value->product_receipt_id;
				if(empty($recipt_id_str)){
					$recipt_id_str .= $recipt_id;
				}else{
					$recipt_id_str .= ",".$recipt_id;
				}
				if(!empty($date)){
					
					$recipt_res = $receiptTable->find("all",[
											   'conditions' => ['id' => $recipt_id],
											   ])->toArray();
					if(!empty($recipt_res)){
						$recipt_created = $recipt_res[0]['created'];
						if(strtotime($recipt_created) > strtotime($date)){
							$date = date("Y-m-d G:i:s",strtotime($recipt_created));
						}else{
							$date = date("Y-m-d G:i:s",strtotime($date));
						}
					}
				}
				$amount = $value->amount;
				if($counter == 0){
					$org_amt = $total_amt;
					$remaining_amt = $total_amt-$amount;
				}else{
					$org_amt = $remaining_amt;
					
						$org_amt = (string)$org_amt;
						$org_amt = (float)$org_amt;
						
						$amount = (string)$amount;
						$amount = (float)$amount;
						
					$remaining_amt = $remaining_amt-$amount;
				}
				
				$user_id = $this->request->Session()->read('Auth.User.id');
				
				if($remaining_amt>=0){
					$payment_id = $value->id;
					if(!empty($date)){
						$data_to_save = array(
										  "created" => date("Y-m-d G:i:s",strtotime($credit_recipt_created)),
										  "cr_ref_id" => $timestamp,
										  "payment_method" => "Cash");
					}else{
						$data_to_save = array(
										  "created" => date("Y-m-d G:i:s",strtotime($credit_recipt_created)),
										  "cr_ref_id" => $timestamp,
										  "payment_method" => "Cash");
					}
					
					$entity = $paymentTable->get($payment_id);
					$entity = $paymentTable->patchEntity($entity,$data_to_save);
					$paymentTable->save($entity);
					
					$logData = array(
								 'user_id'=>$user_id,
								 'pmt_id'=>(int)$payment_id,
								 'kiosk_id' => $kiosk_id,
								 'old_pmt_method'=>"On Credit",
								 'pmt_method'=>"Cash",
								 'receipt_id'=>$value->product_receipt_id,
								 'adjusted_by' => 2,
								 'memo'=>$memo_content,
								 'receipt_type' => 1,
								 'credit_recipt_id' => $credit_recipt_id
								);
			
					$newLog = $this->PmtLogs->newEntity($logData);
					$patchLog = $this->PmtLogs->patchEntity($newLog,$logData);
					$this->PmtLogs->save($patchLog);
					
					$logid = $patchLog->id;
					$log_created = $patchLog->created;
					$firstName = $users[$user_id];
					if(empty($firstName)){
						$firstName = "Missing Name";
					}
					$str = date("d/m/Y h:i",strtotime($log_created)).", ".$firstName.", "."On Credit"."->"."Cash";
					if(!empty($memo_content)){
						$str.="||".$memo_content;
					}
					if(!empty($credit_recipt_id)){
						$str.= ",Invoice adjusted against credit note :".$credit_recipt_id;
					}
					
					$data = array("description" => $str);
					$pay_res1 = $paymentTable->get($value->id);
					$pay_res1 = $paymentTable->patchEntity($pay_res1,$data);
					$paymentTable->save($pay_res1);
					
				}else{
					$outsatanding_amt = $amount - $org_amt;
					$payment_id = $value->id;
					if(!empty($date)){
						$data_to_save = array("payment_method" => "Cash",
										  "amount" => $org_amt,
										  "cr_ref_id" => $timestamp,
										  "created" => date("Y-m-d G:i:s",strtotime($credit_recipt_created)),
										  );
					}else{
						$data_to_save = array("payment_method" => "Cash",
										  "amount" => $org_amt,
										  "cr_ref_id" => $timestamp,
										  "created" => date("Y-m-d G:i:s",strtotime($credit_recipt_created)),
										  );
					}
					
					$entity = $paymentTable->get($payment_id);
					$entity = $paymentTable->patchEntity($entity,$data_to_save);
					$paymentTable->save($entity);
					
					
					
					$logData = array(
								 'user_id'=>$user_id,
								 'pmt_id'=>(int)$payment_id,
								 'kiosk_id' => $kiosk_id,
								 'old_pmt_method'=>"On Credit",
								 'pmt_method'=>"Cash",
								 'receipt_id'=>$value->product_receipt_id,
								 'adjusted_by' => 2,
								 'memo'=>$memo_content,
								 'receipt_type' => 1,
								 'credit_recipt_id' => $credit_recipt_id
								);
			
					$newLog = $this->PmtLogs->newEntity($logData);
					$patchLog = $this->PmtLogs->patchEntity($newLog,$logData);
					$this->PmtLogs->save($patchLog);
					
					$logid = $patchLog->id;
					$log_created = $patchLog->created;
					$firstName = $users[$user_id];
					if(empty($firstName)){
						$firstName = "Missing Name";
					}
					$str = date("d/m/Y h:i",strtotime($log_created)).", ".$firstName.", "."On Credit"."->"."Cash";
					if(!empty($memo_content)){
						$str.="||".$memo_content;
					}
					if(!empty($credit_recipt_id)){
						$str.= ",Invoice adjusted against credit note :".$credit_recipt_id;
					}
					$data = array("description" => $str);
					$pay_res1 = $paymentTable->get($value->id);
					$pay_res1 = $paymentTable->patchEntity($pay_res1,$data);
					$paymentTable->save($pay_res1);
					
					if(!empty($date)){
						$data_to_save1 = array(
										   "payment_method" => "On Credit",
											"amount" => $outsatanding_amt,
											"product_receipt_id" => $value->product_receipt_id,
											"created" => $value->created,
										   );
					}else{
						$data_to_save1 = array(
										   "payment_method" => "On Credit",
											"amount" => $outsatanding_amt,
											"product_receipt_id" => $value->product_receipt_id,
											"created" => $value->created,
										   );
					}
					
					$entity1 = $paymentTable->newEntity();
					$entity1 = $paymentTable->patchEntity($entity1,$data_to_save1);
					$paymentTable->save($entity1);
				}
				$counter++;
			}
			$total = 0;
			foreach($pay_res as $key => $value){
				$total += $value->amount;
			}
			
			if($total < $total_amt){ // $total_amt = credit recipt amount, $total = invoice sum up amount
				$credit_outstanding_amt = $total_amt - $total;
				$credit_data_to_save = array("payment_method" => "Cash",
											 "amount" => $total,
											 "credit_cleared" => 1
											// "created" => date("Y-m-d G:i:s"),
											 );
				$credit_entity = $CreditPaymentDetailTable->get($credit_id);
				$credit_entity = $CreditPaymentDetailTable->patchEntity($credit_entity,$credit_data_to_save);
				$CreditPaymentDetailTable->save($credit_entity);
				
				$logData = array(
								 'user_id'=>$user_id,
								 'pmt_id'=>(int)$credit_id,
								 'kiosk_id' => $kiosk_id,
								 'old_pmt_method'=>"On Credit",
								 'pmt_method'=>"Cash",
								 'receipt_id'=>$credit_recipt_id,
								 'adjusted_by' => 2,
								 'memo'=>$memo_content,
								 'receipt_type' => 2,
								);
			
					$newLog = $this->PmtLogs->newEntity($logData);
					$patchLog = $this->PmtLogs->patchEntity($newLog,$logData);
					$this->PmtLogs->save($patchLog);
					
					$logid = $patchLog->id;
					$log_created = $patchLog->created;
					$firstName = $users[$user_id];
					if(empty($firstName)){
						$firstName = "Missing Name";
					}
					$str = date("d/m/Y h:i",strtotime($log_created)).", ".$firstName.", "."On Credit"."->"."Cash";
					if(!empty($memo_content)){
						$str.="||".$memo_content;
					}
					if(!empty($recipt_id_str)){
						$str.=",credit note adj against inv no. ".$recipt_id_str;
					}
				$credit_data_for_desc = array("description"=>$str);
				$credit_entity_for_desc = $CreditPaymentDetailTable->get($credit_id);
				$credit_entity_for_desc = $CreditPaymentDetailTable->patchEntity($credit_entity_for_desc,$credit_data_for_desc);
				$CreditPaymentDetailTable->save($credit_entity_for_desc);
				
				$credit_data_to_save = array("payment_method" => "On Credit",
											 "amount" => $credit_outstanding_amt,
											 "credit_receipt_id" => $credit_recipt_id,
											 "payment_status" => 0,
											 "created" => date("Y-m-d G:i:s",strtotime($credit_created)),
											 );
				$credit_add_entity = $CreditPaymentDetailTable->newEntity();
				$credit_add_entity = $CreditPaymentDetailTable->patchEntity($credit_add_entity,$credit_data_to_save);
				$CreditPaymentDetailTable->save($credit_add_entity);
				
				
						
			}else{
				$credit_data_to_save = array("payment_method" => "Cash",
											 //"created" => date("Y-m-d G:i:s"),
											 "credit_cleared" => 1
											 );
				$credit_entity = $CreditPaymentDetailTable->get($credit_id);
				$credit_entity = $CreditPaymentDetailTable->patchEntity($credit_entity,$credit_data_to_save);
				$CreditPaymentDetailTable->save($credit_entity);
				
				
				$logData = array(
								 'user_id'=>$user_id,
								 'pmt_id'=>(int)$credit_id,
								 'kiosk_id' => $kiosk_id,
								 'old_pmt_method'=>"On Credit",
								 'pmt_method'=>"Cash",
								 'receipt_id'=>$credit_recipt_id,
								 'adjusted_by' => 2,
								 'memo'=>$memo_content,
								 'receipt_type' => 2,
								);
			
					$newLog = $this->PmtLogs->newEntity($logData);
					$patchLog = $this->PmtLogs->patchEntity($newLog,$logData);
					$this->PmtLogs->save($patchLog);
					
					$logid = $patchLog->id;
					$log_created = $patchLog->created;
					$firstName = $users[$user_id];
					if(empty($firstName)){
						$firstName = "Missing Name";
					}
					$str = date("d/m/Y h:i",strtotime($log_created)).", ".$firstName.", "."On Credit"."->"."Cash";
					if(!empty($memo_content)){
						$str.="||".$memo_content;
					}
					if(!empty($recipt_id_str)){
						$str.=",credit note adj against inv no. ".$recipt_id_str;
					}
				$credit_data_for_desc = array("description"=>$str);
				$credit_entity_for_desc = $CreditPaymentDetailTable->get($credit_id);
				$credit_entity_for_desc = $CreditPaymentDetailTable->patchEntity($credit_entity_for_desc,$credit_data_for_desc);
				$CreditPaymentDetailTable->save($credit_entity_for_desc);
			}
			
			
			
				
			if($counter > 0){
				$msg = array("msg" => "Done");
				echo json_encode($msg);die;
			}
		}
	}
	
	function customerAccountStatement(){
		//pr($this->request->query);die;
        $settingArr = $this->setting;
        $kiosk_id = $this->request->query['kiosk'];
        //Start: sending email to customer
        //if(!empty($this->request->query['email2Customer']) && $this->request->query['email2Customer'] == 1){
        if(array_key_exists('customer_email', $this->request['data']) && !empty($customerEmail = $this->request['data']['customer_email'])){
            $emailContent = $this->request['data']['emailContent'];
			$client = new \Pdfcrowd\HtmlToPdfClient("saurav7767", "4d983e65cc36982b7d138c1590e06e8b");
			$emailData = base64_decode($emailContent);
			$timestamp =  date('d-m-Y_H-i-s');
			$filename = $timestamp.".pdf";
			$path = ROOT."/webroot";
			$file = $path . "/" . $filename;
			//echo $file;die;
			//$client->setNoPrint(True);
			//$client->setNoModify(True);
			//$client->setNoCopy(True);
			//$client->setOwnerPassword("password");
			//$client->setUserPassword("password2");
			//$client->convertStringToFile($emailData, $file);
			$this->html_to_pdf($file,$emailData);
			
            $customerEmail = $this->request['data']['customer_email'];
            if(!empty($customerEmail)){
                $to = $customerEmail;
                $subject = "Account Statement";
				$message = "
				Hi ,<br/>
				Please Find Attached Your Account Statement.<br/>
				
				Thanks";
				$content = file_get_contents($file);
				$content = chunk_split(base64_encode($content));
			
				// a random hash will be necessary to send mixed content
				$separator = md5(time());
			
				$eol = "\r\n";
				$send_by_email = Configure::read('send_by_email');
				$headers = "From: <$send_by_email>" . $eol;
				$headers .= "MIME-Version: 1.0" . $eol;
				$headers .= "Content-Type: multipart/mixed; boundary=\"" . $separator . "\"" . $eol;
			
				// message
				$body = "--".$separator.$eol;
				$body .= "Content-type:text/html; charset=iso-8859-1".$eol;
				$body .= "Content-Transfer-Encoding: 8bit".$eol.$eol;
				$body .= "$message".$eol;
			
				// attachment
				$body .= "--" . $separator . $eol;
				$body .= "Content-Type: application/octet-stream; name=\"" . $filename . "\"" . $eol;
				$body .= "Content-Transfer-Encoding: base64" . $eol;
				$body .= "Content-Disposition: attachment" . $eol;
				$body .= $eol.$content . $eol;
				$body .= "--" . $separator . "--";
                
                if(mail($to, $subject, $body, $headers)){
                    $this->Flash->success(__("Account Statement has been successfully sent to email address: {$customerEmail}."));
                    return $this->redirect(array('action'=>"index"));
                }else{
                    die("Failed to sent eamil statement");
                    $this->Flash->success(__("Failed to sent eamil statement"));
                }
            }else{
                die("Please input customer eamil address");
                $this->Flash->success(__("Please input customer eamil address"));
            }
        }
         
        //End: sending email to customer
        $k_id_to_use = $kiosk_id;
        $custId = 0;
        if($kiosk_id == "" || $kiosk_id == 0) $k_id_to_use = 10000;
		
        $productReceiptSource = "kiosk_{$k_id_to_use}_product_receipts";
		$paymentDetailSource = "kiosk_{$k_id_to_use}_payment_details";
        $crRecptSrc = "kiosk_{$k_id_to_use}_credit_receipts";
        $creditPmtDetSrc = "kiosk_{$k_id_to_use}_credit_payment_details";
        
		if($k_id_to_use == 10000){
			$productReceiptSource = "product_receipts";
			$paymentDetailSource = "payment_details";
            $crRecptSrc = "credit_receipts";
            $creditPmtDetSrc = "credit_payment_details";
		}
		$startDate = $endDate = "";
        if(!empty($this->request->query['start_date'])){
			$startDate = date("Y-m-d",strtotime($this->request->query['start_date'].'-1 day'));
		}
		
		if(!empty($this->request->query['end_date'])){
			$endDate = date("Y-m-d",strtotime($this->request->query['end_date'].'+1 day'));
		}
		
		if(!empty($this->request->query['customer_number'])){
			//$conditionArr['customer_id'] = $this->request->query['customer_number'];
			$custId = $this->request->query['customer_number'];
		}
        $combineArr = $custDetArr = array();
		$cutomer_query = $this->Customers->find('all',array('conditions' => array('Customers.id' => $custId)));
        $custDetails = $cutomer_query->hydrate(false);
        if(!empty($custDetails)){
            $custDetArr = $custDetails->first(); //pr($custDetArr);
        }
        
		if(!empty($startDate) || !empty($endDate)){
			$conditions = " AND (DATE(`cpd`.`created`) BETWEEN '$startDate' AND '$endDate')";
		}else{
			$conditions = "";
		}
        $conditions = "";
		//echo "SELECT *,`cr`.`created` as `recpt_date`, `cpd`.created as `pmt_date` FROM `$crRecptTbl` as `cr`, `$creditPmtDetSrc` as `cpd` WHERE `cr`.`id` = `cpd`.`credit_receipt_id` AND `cpd`.`payment_method`='On Credit' AND`cr`.`customer_id` = $custId".$conditions;die;
        
        
        $conn = ConnectionManager::get('default');
        $creditQry = "SELECT *,`cr`.`created` as `recpt_date`, `cpd`.modified as `pmt_date` FROM `$crRecptSrc` as `cr`, `$creditPmtDetSrc` as `cpd` WHERE `cr`.`id` = `cpd`.`credit_receipt_id`  AND`cr`.`customer_id` = $custId".$conditions; //AND `cpd`.`payment_method`='On Credit' //echo $creditQry."<br/>";
        $stmt = $conn->execute($creditQry);
        $crRecptData = $stmt ->fetchAll('assoc');   //pr($crRecptData);die;
        //Credit Note Tbls, Different fields (2) : credit_amount, kiosk_id
        
		$cnRecepts = $invReceptIDs = $creditReceptIDs = $prPmtDate = $crPmtDate = array();
        
		foreach($crRecptData as $key => $crData){
			$combineArr[] =$crPmtDate[] = $crData['pmt_date'];
            if(!in_array($crData['credit_receipt_id'], $creditReceptIDs)) $creditReceptIDs[] = $crData['credit_receipt_id'];
		}
        
		//Start: Get Credit Note receipts
        if(count($creditReceptIDs)){
            $cnReceptQry = "SELECT `id`, `customer_id`, `bill_amount`, `orig_bill_amount`, `created` as `cred_note_date` FROM `$crRecptSrc` as `pr` WHERE `pr`.`id` IN(".implode(",", $creditReceptIDs).") AND `pr`.`customer_id` = $custId"; //echo $invQry."<br/>";
            $stmtInv = $conn->execute($cnReceptQry); 
            $cnRecepts = $stmtInv ->fetchAll('assoc');
            foreach($cnRecepts as $key => $cnRecept){
                $combineArr[] = $cnRecept['cred_note_date'];
            }
        }
        //Eng: Get Credit Note receipts
        
		$conditions = str_replace("cpd","pd",$conditions);
        $invQry = "SELECT *,`pr`.`created` as `recpt_date`, `pd`.created as `pmt_date` FROM `$productReceiptSource` as `pr`, `$paymentDetailSource` as `pd` WHERE `pr`.`id` = `pd`.`product_receipt_id` AND  `pr`.`customer_id` = $custId".$conditions;//echo $invQry."<br/>";
        //`pd`.`payment_method` <> 'On Credit' AND 
		$stmt_pr = $conn->execute($invQry); 
        $prRecptData = $stmt_pr ->fetchAll('assoc');
		
        //Invoice tables, Different fields(4) : vat_number,sale_type, bulk_invoice, invoice_order_id
        
		foreach($prRecptData as $key => $prData){
			$combineArr[] = $prPmtDate[] = $prData['pmt_date'];
            if(!in_array($prData['product_receipt_id'], $invReceptIDs)) $invReceptIDs[] = $prData['product_receipt_id'];
		}
        
        //Start: remove entries from invoice/statement for which amount is matching with amount of invoices
        //pr($prRecptData);
        foreach($crRecptData as $key => $crData){
            foreach($prRecptData as $key1 => $prData){
                if($crData['amount'] == $prData['amount']){
                   unset($prRecptData[$key1]);break;
                }
                if(!empty($prData['cr_ref_id'])){
                   unset($prRecptData[$key1]);
                }
            }
        }
        //echo "after unset";pr($prRecptData);
        //End: remove entries from invoice for which amount is matching with amount of invoices
        
        //Start: Get invoice receipts
        $invRecepts = array();
        if(count($invReceptIDs)){
            /*$prodReceptTbl = TableRegistry::get($productReceiptSource,['table' => $productReceiptSource]);
            $invRecepts = $prodReceptTbl->find('all', ['conditions' => ['id IN' => $invReceptIDs], 'recursive' => -1, 'fields' => array('id', 'customer_id', 'bill_amount', 'orig_bill_amount', 'created')])->toArray();*/
            
            $invReceptQry = "SELECT `id`, `customer_id`, `bill_amount`, `orig_bill_amount`, `created` as `inv_date` FROM `$productReceiptSource` as `pr` WHERE `pr`.`id` IN(".implode(",",$invReceptIDs).") AND `pr`.`customer_id` = $custId"; //echo $invQry."<br/>";
            $stmtInv = $conn->execute($invReceptQry); 
            $invRecepts = $stmtInv ->fetchAll('assoc');//pr($invRecepts);die;
            foreach($invRecepts as $key => $invRecept){
                $combineArr[] = $invRecept['inv_date'];//$invRecept['created']->i18nFormat('yyyy-MM-dd HH:mm:ss');
            }
        }
        //Eng: Get invoice receipts
        
        //join arrays
        $crInvRecptData = $crRecptData;                                             //1. Adding credit notes payments data to $crInvRecptData array
        foreach($prRecptData as $key => $prData){$crInvRecptData[] = $prData;}      //2. Adding invoice payments data to $crInvRecptData array
        foreach($invRecepts as $key => $prData){$crInvRecptData[] = $prData;}       //3. Adding invoices data to $crInvRecptData array
        foreach($cnRecepts as $key => $prData){$crInvRecptData[] = $prData;}        //4. Adding credit note data to $crInvRecptData array
        //pr($crInvRecptData);
		//$combineArr = array_merge($crPmtDate, $prPmtDate);
		usort($combineArr, array('self', 'sortFunction'));
        $recptInOrders = array();
        //pr($combineArr);
       // pr($crInvRecptData);//die;
		//die;
        foreach($combineArr as $orderKey => $recptDate){
            foreach($crInvRecptData as $outerKey => $reeptDatum){
                if(
                    ( array_key_exists('pmt_date', $reeptDatum) && $reeptDatum['pmt_date'] == $recptDate) ||
                    ( array_key_exists('inv_date', $reeptDatum) && $reeptDatum['inv_date'] == $recptDate)){
					
                    $itemNotPresent = true;
					
                    foreach($recptInOrders as $key => $recptInOrder){
                        if($recptInOrder['id'] == $reeptDatum['id']){
                            if( array_key_exists('cred_note_date', $recptInOrder) && array_key_exists('cred_note_date', $reeptDatum) ){
                                //compare invoice numbers
                                if($recptInOrder['credit_receipt_id'] == $reeptDatum['product_receipt_id']){
                                    $itemNotPresent = false;
                                }
								
                            }elseif( array_key_exists('inv_date', $recptInOrder) && array_key_exists('inv_date', $reeptDatum) ){
                                //compare invoice numbers
                                 if($recptInOrder['id'] == $reeptDatum['id']){
                                    $itemNotPresent = false;
                                }
								
                            }elseif( array_key_exists('credit_receipt_id', $recptInOrder) && array_key_exists('credit_receipt_id', $reeptDatum) ){
                                //compare credit note numbers
                                if($recptInOrder['credit_receipt_id'] == $reeptDatum['credit_receipt_id']){
                                    $itemNotPresent = false;
                                }
								
                            }elseif( array_key_exists('product_receipt_id', $recptInOrder) && array_key_exists('product_receipt_id', $reeptDatum) ){
                                
                                //compare invoice numbers
                                 if(array_key_exists('product_receipt_id', $reeptDatum) && $recptInOrder['product_receipt_id'] == $reeptDatum['product_receipt_id']){
                                    $itemNotPresent = false;
                                }
                            }
                        }
                        if(array_key_exists('product_receipt_id', $reeptDatum) && $reeptDatum['product_receipt_id']){
                            //pr($reeptDatum);die("outside if");
                        }
                    }
					
                    if($itemNotPresent){
                        $recptInOrders[] = $reeptDatum;
                    }
					
                }
            }
        }
        //pr($recptInOrders);die;
		$productReceiptTable = TableRegistry::get($productReceiptSource,['table' => $productReceiptSource]);
		$paymentDetailTable = TableRegistry::get($paymentDetailSource,['table' => $paymentDetailSource]);
        $crRecptTbl = TableRegistry::get($crRecptSrc, ['table' => $crRecptSrc]);
		$creditPmtDetTbl = TableRegistry::get($creditPmtDetSrc, ['table' => $creditPmtDetSrc]);
		
		$kioskDetails = "";
        $new_kiosk_data =  $this->Kiosks->find('all',array('conditions' => array('Kiosks.id' => $k_id_to_use)))->toArray();
		$kioskDetails_query = $this->Kiosks->find('all',array('conditions' => array('Kiosks.id' => $k_id_to_use)));
        
        $kioskDetails = $kioskDetails_query->hydrate(false);
        if(!empty($kioskDetails)){
            $kioskDetails = $kioskDetails->first();
        }else{
            $kioskDetails = array();
        }
        //-------------------------------------------------
        //Step 1 : Get all 
        //-------------------------------------------------
		$conditionArr = array();
		
		
		
		if(!empty($startDate) || !empty($endDate)){
			$conditionArr[] = array("DATE(`created`) > '$startDate'","DATE(`created`) < '$endDate'");
		}
		$conditionArr = array();
		$productReceiptQry = $productReceiptTable->find("all",array("conditions" => $conditionArr));
		$productReceiptQry = $productReceiptQry->hydrate(false);
		//pr($productReceiptQry);die;
		if(!empty($productReceiptQry)){
			$productReceipt = $productReceiptQry->toArray();
		}else{
			$productReceipt = array();
		}
		//pr($productReceipt);die;
		$statement = array();
		if(!empty($productReceipt)){
			foreach($productReceipt as $key => $reciptData){
				$receptID = $reciptData['id'];
				$statmentArr[$receptID] = $reciptData;
				$paymentDetailQry = $paymentDetailTable->find("all",array("conditions" =>array('product_receipt_id' => $receptID)));
				$paymentDetailQry = $paymentDetailQry->hydrate(false);
                
				if(!empty($paymentDetailQry)){
					$paymentDetailQry = $paymentDetailQry->toArray();
				}else{
					$paymentDetailQry = array();
				}
				//pr($paymentDetailQry);die;
				$statmentArr[$receptID]['payment_details'] = $paymentDetailQry;
			}
		}
		//pr($statmentArr);die;
        $this->set(compact('kioskDetails', 'settingArr', 'new_kiosk_data','statmentArr', 'recptInOrders', 'custDetArr', 'startDate', 'endDate'));
	}
    
	private function sortFunction( $a, $b ) {
	    return strtotime($a) - strtotime($b);
	}
	
	function invoicePaymentClearness(){
		$external_sites = Configure::read('external_sites');
		$path = dirname(__FILE__);
		$ext_site = 0;
		foreach($external_sites as $site_id => $site_name){
			  $isboloRam = strpos($path,$site_name);
			  if($isboloRam != false){
				  $ext_site = 1;
			  }
		}
		
		
        $this->loadModel('ProductReceipts');
        $this->loadModel('KioskProductSales');
        $this->loadModel('Products');
		if(array_key_exists('0',$this->request->params['pass'])){
			$kiosk_id = $this->request->params['pass'][0];
			$kioskProdctSaleTable_source = "kiosk_{$kiosk_id}_product_sales";
			$product_recit_table_source = "kiosk_{$kiosk_id}_product_receipts";
			$paymentTable_source = "kiosk_{$kiosk_id}_payment_details";
			
			$receiptTable = TableRegistry::get($product_recit_table_source,[
																		'table' => $product_recit_table_source,
																	]);
			
			$kioskProdctSalesTable = TableRegistry::get($kioskProdctSaleTable_source,[
																		'table' => $kioskProdctSaleTable_source,
																	]);
			
			$paymentTable = TableRegistry::get($paymentTable_source,[
																		'table' => $paymentTable_source,
																	]);

			
			//$this->KioskProductSale->setSource($kioskProdctTable);
			//$this->ProductReceipt->setSource($product_recit_table);
			//$this->PaymentDetail->setSource($paymentTable);
		}else{
			$kiosk_id = $this->request->Session()->read('kiosk_id');
			if(!empty($kiosk_id)){
				$kioskProdctSaleTable_source = "kiosk_{$kiosk_id}_product_sales";
				$product_recit_table_source = "kiosk_{$kiosk_id}_product_receipts";
				$paymentTable_source = "kiosk_{$kiosk_id}_payment_details";
			}else{
				$kioskProdctSaleTable_source = "kiosk_product_sales";
				$product_recit_table_source = "product_receipts";
				$paymentTable_source = "payment_details";
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
		}
		$settingArr = $this->setting;
		//$listPayment = $this->PaymentDetail->find('list',array('fields'=>array('product_receipt_id','amount')));
		
		$receiptIdArr = array();
		$productReceiptDetail = array();
		//
		//if($listPayment){
		//	$receiptIdArr = array_keys($listPayment);
		//}
		$totalBillCost = 0;
		$productReceiptDetail_query = $receiptTable->find('all',array('fields'=>array('id','vat','status','bill_amount', 'bill_cost','created'),'recursive'=>-1));
        if(!empty($productReceiptDetail_query)){
            $productReceiptDetail = $productReceiptDetail_query->hydrate(false);
            $productReceiptDetail = $productReceiptDetail->toArray();   
        }
        //pr($productReceiptDetail);die;
		$createdArr = array();
		$lptotalPaymentAmount = 0;
		$lpgrandNetAmount = 0;
		foreach($productReceiptDetail as $key=>$productReceiptDta){
			//showing the data with product receipt status == 0 ie. the payment went through and data saved properly
			if($productReceiptDta['status']==0){
				$createdArr[$productReceiptDta['id']] = $productReceiptDta['created'];
				$totalBillCost+=floatval($productReceiptDta['bill_cost']);
                $receiptIdArr[$productReceiptDta['id']] = $productReceiptDta['id'];//capturing the receipt ids
				$paymentAmount = $productReceiptDta['bill_amount'];
				$lptotalPaymentAmount+=floatval($paymentAmount);
				$vatPercentage = $productReceiptDta['vat']/100;
				$netAmount = $paymentAmount/(1+$vatPercentage);
				$lpgrandNetAmount+=$netAmount;
			}
		}
                //code for getting cost price of products*******************
                $totalCost = 0;
                if(count($receiptIdArr)){
                    $productQttArr_query = $kioskProdctSalesTable->find('all', array('conditions' => array('product_receipt_id IN' => $receiptIdArr), 'fields' => array('product_id','quantity','product_receipt_id'), 'recursive' => -1));
                    $productQttArr = $productQttArr_query->hydrate(false);
                    if(!empty($productQttArr)){
                        $productQttArr = $productQttArr->toArray();
                    }
                    
                    $receiptIdDetail = array();
                    $productIdsArr = array();
                    foreach($productQttArr as $key => $productQtt){
                        //$receiptIdDetail[$productQtt['KioskProductSale']['product_receipt_id']][] = $productQtt['KioskProductSale'];
                        $productIdsArr[$productQtt['product_id']] = $productQtt['product_id'];
                    }
                    if(empty($productIdsArr)){
						$productIdsArr = array(0=>null);
					}
                    $costPriceList_query = $this->Products->find('list', array('conditions' => array('Products.id IN' => $productIdsArr), 'fields' => array('id', 'cost_price')));
                    $costPriceList = $costPriceList_query->hydrate(false);
                    if(!empty($costPriceList)){
                        $costPriceList = $costPriceList->toArray();
                    }
                    foreach($productQttArr as $key => $productQtt){
                        if(!array_key_exists($productQtt['product_id'],$costPriceList)){continue;}
                        $costPrice = $costPriceList[$productQtt['product_id']]*$productQtt['quantity'];
                        $totalCost+=$costPrice;
                    }
                }
                //*********************till here
          
		$lptotalVat = $lptotalPaymentAmount - $lpgrandNetAmount;
		$lptotalVat = $lptotalVat;
		
		$this->set(compact('lptotalPaymentAmount','lpgrandNetAmount','lptotalVat','totalCost'));
		 $this->paginate = [
                                    'order' => [
                                        'product_receipt_id' => 'desc'
                                    ],
									'limit' => 50
                                   // 'contain' => ['ProductReceipts']
                                ];
		
        
		$productReceipts = $this->paginate($paymentTable);
        if(!empty($productReceipts)){
          $productReceipts = $productReceipts->toArray();
        }else{
			$productReceipts = array();
		}
		//pr($productReceipts);
		$recipt_table_data = array();
        $y_recipt_ids = array();
		foreach($productReceipts as $s_key => $s_value){
			$y_recipt_ids[] = $s_value->product_receipt_id;
		}
		if(empty($y_recipt_ids)){
            $y_recipt_ids = array('0'=>null);
        }
		$recipt_table_data_query = $receiptTable->find('all',[
								   'conditions' => ['id IN' => $y_recipt_ids]
								   ]);
		$recipt_table_data_query = $recipt_table_data_query->hydrate(false);
		if(!empty($recipt_table_data_query)){
			$recipt_table_data = $recipt_table_data_query->toArray();
		}else{
			$recipt_table_data = array();
		}
		
		//pr($recipt_table_data);die;
		$reciptTableData = $customerIdArr = array();
		foreach($recipt_table_data as $receiptDetail){
			//pr($receiptDetail);die;
			$reciptTableData[$receiptDetail['id']] = $receiptDetail;
			$customerIdArr[] = $receiptDetail['customer_id'];
		}
		$this->set('recipt_table_data', $reciptTableData);
		if(empty($customerIdArr)){
			$customerIdArr = array(0=>null);
		}
		$customerBusiness_query = $this->Customers->find('list',
                                                         ['conditions' => [
                                                                           'Customers.id IN' => $customerIdArr
                                                                           ],
                                                                 'keyField' => 'id',
                                                                'valueField' => 'business',
                                                         ]);
        $customerBusiness = $customerBusiness_query->hydrate(false);
        if(!empty($customerBusiness)){
            $customerBusiness = $customerBusiness->toArray();
        }
		if($this->request->session()->read('Auth.User.group_id') == ADMINISTRATORS || $this->request->session()->read('Auth.User.group_id') == FRANCHISE_OWNER){
			//$session_id = $this->Session->id();
			$user_id = $this->request->Session()->read('Auth.User.id');
			$data_arr = array('kiosk_id' => 10000);
			$jsondata = json_encode($data_arr);
			$this->loadModel('UserSettings');
			
			$res_query = $this->UserSettings->find('all',array('conditions' => array(
																		 'user_id' => $user_id,
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
									'setting_name' => "search",
									'data' => $jsondata
									);
				$entity = $this->UserSettings->get($userSettingid);
                
				$entity = $this->UserSettings->patchEntity($entity,$data_to_save);
				$this->UserSettings->save($entity);
			}else{
				$data_to_save = array(
									'user_id' => $user_id,
									//'user_session_key' => $session_id,
									'setting_name' => "search",
									'data' => $jsondata
									);
				$entity = $this->UserSettings->newEntity();
				$entity = $this->UserSettings->patchEntity($entity,$data_to_save);
				$this->UserSettings->save($entity);
			}
		}
		$kiosks_query = $this->Kiosks->find('list');
		$kiosks_query = $kiosks_query->hydrate(false);
		
		
		if(!empty($kiosks_query)){
			$kiosks = $kiosks_query->toArray();
		}else{
			$kiosks = array();
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
		
		$log_refined_data = array();
		$log_table_data = $this->PmtLogs->find("all",[
													  'conditions' => ['kiosk_id' => $kiosk_id],
													  'order' => ['created DESC'],
													  ])->toArray();
		if(!empty($log_table_data)){
			foreach($log_table_data as $log_key => $log_value){
					$log_refined_data[$log_value->pmt_id][] = $log_value;
			}
		}
		
		$users = $this->Users->find("list",['keyField' => 'id',
								   'valueField' => 'f_name',
								   ])->toArray();
		
		$this->set(compact('productReceipts', 'customerBusiness','totalAmount','totalBillCost','createdArr','kiosks','kiosk_id','agents','log_refined_data',"users"));		
	}
	
	function quotationPaymentClearness(){
		$this->check_dr5();
		$ProductReceipt_source = 't_product_receipts';
		$ProductReceiptTable = TableRegistry::get($ProductReceipt_source,[
																			'table' => $ProductReceipt_source,
																		]);
		$KioskProductSale_source = 't_kiosk_product_sales';
		$KioskProductSaleTable = TableRegistry::get($KioskProductSale_source,[
																				'table' => $KioskProductSale_source,
																			]);
		$PaymentDetail_source = 't_payment_details';
		$PaymentDetailTable = TableRegistry::get($PaymentDetail_source,[
																				'table' => $PaymentDetail_source,
																			]);
		
		if(array_key_exists(0,$this->request->params['pass'])){
					$kskId = $this->request->params['pass'][0];
		}else{
			$kskId = $this->request->Session()->read('kiosk_id');
		}
		if(empty($kskId)){
			$kskId = 0;
		}
		
		$settingArr = $this->setting;
		//$listPayment = $this->PaymentDetail->find('list',array('fields'=>array('product_receipt_id','amount')));
		
		$receiptIdArr = array();
		$productReceiptDetail = array();
		//
		//if($listPayment){
		//	$receiptIdArr = array_keys($listPayment);
		//}
		$totalBillCost = 0;
		$productReceiptDetail_query = $ProductReceiptTable->find('all',array('conditions' => array(
																							  'kiosk_id' => $kskId,
																							  ),'fields'=>array('id','vat','status','bill_amount', 'bill_cost','created')));
		$productReceiptDetail_query = $productReceiptDetail_query->hydrate(false);
		if(!empty($productReceiptDetail_query)){
			$productReceiptDetail = $productReceiptDetail_query->toArray();
		}else{
			$productReceiptDetail = array();
		}
        //pr($productReceiptDetail);die;
		$createdArr = array();
		$lptotalPaymentAmount = 0;
		$lpgrandNetAmount = 0;
		foreach($productReceiptDetail as $key=>$productReceiptDta){
			//showing the data with product receipt status == 0 ie. the payment went through and data saved properly
			if($productReceiptDta['status']==0){
				$createdArr[$productReceiptDta['id']] = $productReceiptDta['created'];
				$totalBillCost+=floatval($productReceiptDta['bill_cost']);
                $receiptIdArr[$productReceiptDta['id']] = $productReceiptDta['id'];//capturing the receipt ids
				$paymentAmount = $productReceiptDta['bill_amount'];
				$lptotalPaymentAmount+=floatval($paymentAmount);
				$vatPercentage = $productReceiptDta['vat']/100;
				$netAmount = $paymentAmount/(1+$vatPercentage);
				$lpgrandNetAmount+=$netAmount;
			}
		}
		
                //code for getting cost price of products*******************
                $totalCost = 0;
                if(count($receiptIdArr)){
                    if(empty($receiptIdArr)){
						$receiptIdArr = array(0 => null);
					}
					$productQttArr_query = $KioskProductSaleTable->find('all', array('conditions' => array('product_receipt_id IN' => $receiptIdArr,'kiosk_id' => $kskId), 'fields' => array('product_id','quantity','product_receipt_id')));
					$productQttArr_query = $productQttArr_query->hydrate(false);
					if(!empty($productQttArr_query)){
						$productQttArr = $productQttArr_query->toArray();
					}else{
						$productQttArr = array();
					}
                    $receiptIdDetail = array();
                    $recit_ids = $productIdsArr = array();
                    foreach($productQttArr as $key => $productQtt){
						$recit_ids[$productQtt['product_receipt_id']] = $productQtt['product_receipt_id'];
                        //$receiptIdDetail[$productQtt['KioskProductSale']['product_receipt_id']][] = $productQtt['KioskProductSale'];
                        $productIdsArr[$productQtt['product_id']] = $productQtt['product_id'];
                    }
                    if(empty($productIdsArr)){
						$productIdsArr = array(0 => null);
					}
					$costPriceList_query = $this->Products->find('list',[
																			'conditions' => ['Products.id IN' => $productIdsArr],
																			'keyField' => 'id',
																			'valueField' => 'cost_price'
																		]
																);
					$costPriceList_query = $costPriceList_query->hydrate(false);
					if(!empty($costPriceList_query)){
						$costPriceList = $costPriceList_query->toArray();
					}else{
						$costPriceList = array();
					}
                    foreach($productQttArr as $key => $productQtt){
                        if(!array_key_exists($productQtt['product_id'],$costPriceList)){continue;}
                        $costPrice = $costPriceList[$productQtt['product_id']]*$productQtt['quantity'];
                        $totalCost+=$costPrice;
                    }
                }
                //*********************till here
          
		$lptotalVat = $lptotalPaymentAmount - $lpgrandNetAmount;
		$lptotalVat = $lptotalVat;
		
		$this->set(compact('lptotalPaymentAmount','lpgrandNetAmount','lptotalVat','totalCost'));
		
		$this->paginate = [
							'conditions' => ['kiosk_id' => $kskId],
							'order' => ['product_receipt_id DESC'],
							'limit' => 50
						  ];
		$productReceipts_query = $this->paginate($PaymentDetailTable);
		if(!empty($productReceipts_query)){
			$productReceipts = $productReceipts_query->toArray();
		}else{
			$productReceipts = array();
		}
		
		$fixed_cost_sum_query = $ProductReceiptTable->find('all',array('conditions' => ['kiosk_id' => $kskId]));
		$fixed_cost_sum_query
					->select(['fixed_cost' => $fixed_cost_sum_query->func()->sum('bill_cost')]);
        
		$fixed_cost_sum_query = $fixed_cost_sum_query->hydrate(false);
		if(!empty($fixed_cost_sum_query)){
			$fixed_cost_sum = $fixed_cost_sum_query->first(false);
		}else{
			$fixed_cost_sum = array();
		}
		
		//pr($productReceipts);die;
		$customerIdArr = array();
		 //pr($createdArr);die;
		 //pr($productReceipts);
		 $product_receiptId = array();
		if(!empty($productReceipts)){
			foreach($productReceipts as $productReceipts_value){
			   $product_receiptId[] = $productReceipts_value['product_receipt_id'];
			}
		}
		if(empty($product_receiptId)){
			$product_receiptId = array(0 => null);
		}
		//pr($product_receiptId);die;
		   $product_receipt_data_query = $ProductReceiptTable->find('all',[
																				   'conditions' => ['id IN' => $product_receiptId]
																			   ]
												   );
		   //pr($product_receipt_data_query);die;
			   $product_receipt_data_query = $product_receipt_data_query->hydrate(false);
			   if(!empty($product_receipt_data_query)){
				   $product_receipt_data = $product_receipt_data_query->toArray();
			   }else{
				   $product_receipt_data = array();
			   }
		 //pr($product_receipt_data);die;
		 
		foreach($product_receipt_data as $receiptDetail){
			//pr($receiptDetail);die;
			$customerIdArr[] = $receiptDetail['customer_id'];
			$productreceiptArr[$receiptDetail['id']] = $receiptDetail;
			
		}
		$this->set(compact('productreceiptArr'));
		if(empty($customerIdArr)){
			$customerIdArr = array(0 => null);
		}
		 $customerBusiness_query = $this->Customers->find('list',[
                                                    'keyField' => 'id',
                                                     'valueField' => 'business',
                                                   'conditions' =>['Customers.id IN'=>array_unique($customerIdArr)],
                                                 ]
                                        ); 
		$customerBusiness_query = $customerBusiness_query->hydrate(false);
		if(!empty($customerBusiness_query)){
			$customerBusiness = $customerBusiness_query->toArray();
		}else{
			$customerBusiness = array();
		}
		if($this->request->session()->read('Auth.User.group_id') == ADMINISTRATORS || $this->request->session()->read('Auth.User.group_id') == FRANCHISE_OWNER){
			//$session_id = $this->Session->id();
			$user_id = $this->request->Session()->read('Auth.User.id');
			$data_arr = array('kiosk_id' => 10000);
			$jsondata = json_encode($data_arr);
			$this->loadModel('UserSettings');
			
			$res_query = $this->UserSettings->find('all',array('conditions' => array(
																		 'user_id' => $user_id,
																		 'setting_name' => "dr_search",
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
									'setting_name' => "dr_search",
									'data' => $jsondata
									);
				$entity = $this->UserSettings->get($userSettingid);
				$entity = $this->UserSettings->patchEntity($entity,$data_to_save);
				$this->UserSettings->save($entity);
			}else{
				$data_to_save = array(
									'user_id' => $user_id,
									//'user_session_key' => $session_id,
									'setting_name' => "dr_search",
									'data' => $jsondata
									);
				$entity = $this->UserSettings->newEntity();
				$entity = $this->UserSettings->patchEntity($entity,$data_to_save);
				$this->UserSettings->save($entity);
			}
		}
		$kiosk_list_query = $this->Kiosks->find('list');
		$kiosk_list_query = $kiosk_list_query->hydrate(false);
		if(!empty($kiosk_list_query)){
			$kiosk_list = $kiosk_list_query->toArray();
		}else{
			$kiosk_list = array();
		}
		$kiosk_id = 10000;
		
		$agents_query = $this->Agents->find('list');
		$agents_query = $agents_query->hydrate(false);
		
		if(!empty($agents_query)){
			$agents = $agents_query->toArray();
		}
		$agents[0] = "Select Acc manager";
		ksort($agents);
		
		$log_refined_data = array();
		$log_table_data = $this->PmtLogs->find("all",[
													  'conditions' => ['kiosk_id' => $kskId,
																	   'receipt_type' => 3,
																	   ],
													  'order' => ['created DESC'],
													  ])->toArray();
		if(!empty($log_table_data)){
			foreach($log_table_data as $log_key => $log_value){
					$log_refined_data[$log_value->pmt_id][] = $log_value;
			}
		}
		
		$users = $this->Users->find("list",['keyField' => 'id',
								   'valueField' => 'f_name',
								   ])->toArray();
		
		$this->set(compact('productReceipts', 'customerBusiness','totalAmount','totalBillCost','createdArr','recit_ids','kiosk_list','kiosk_id','fixed_cost_sum','agents','log_refined_data','users'));				
	}
	
	 public function paymentClearnessSearch($keyword = ''){
        $kiosk_id = $this->request->Session()->read('kiosk_id');        
		$kioskProdctSaleTable_source = "kiosk_{$kiosk_id}_product_sales";
        $product_recit_table_source = "kiosk_{$kiosk_id}_product_receipts";
        $paymentTable_source = "kiosk_{$kiosk_id}_payment_details";
		
		if(array_key_exists('kiosk_id',$this->request->query) || array_key_exists('kiosk-id',$this->request->query)){
			//echo'hi';
			if(array_key_exists('kiosk_id',$this->request->query)){
				$kiosk_id = $this->request->query['kiosk_id'];
			}
			if(array_key_exists('kiosk-id',$this->request->query)){
				$kiosk_id = $this->request->query['kiosk-id'];
			}
			
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
		}
		//if(array_key_exists('kiosk_id',$this->request->query)){
		//	$kiosk_id = $this->request->query['kiosk_id'];
		//	$kioskProdctSaleTable_source = "kiosk_{$kiosk_id}_product_sales";
		//	$product_recit_table_source = "kiosk_{$kiosk_id}_product_receipts";
		//	$paymentTable_source = "kiosk_{$kiosk_id}_payment_details";
		//}else{
		//	$kiosk_id = $this->request->Session()->read('kiosk_id');
		//	if(!empty($kiosk_id)){
		//		$kiosk_id = 10000;
		//		$kioskProdctSaleTable_source = "kiosk_{$kiosk_id}_product_sales";
		//		$product_recit_table_source = "kiosk_{$kiosk_id}_product_receipts";
		//		$paymentTable_source = "kiosk_{$kiosk_id}_payment_details";
		//	}else{
		//		$kioskProdctSaleTable_source = "kiosk_product_sales";
		//		$product_recit_table_source = "product_receipts";
		//		$paymentTable_source = "payment_details";
		//	}
		//}
		//echo $kiosk_id;die;
		$this->set(compact('kiosk_id'));
		$receiptTable = TableRegistry::get($product_recit_table_source,[
																		'table' => $product_recit_table_source,
																	]);
			
		$kioskProdctSalesTable = TableRegistry::get($kioskProdctSaleTable_source,[
																		'table' => $kioskProdctSaleTable_source,
																	]);
			
		$paymentTable = TableRegistry::get($paymentTable_source,[
																		'table' => $paymentTable_source,
																	]);
		$conditionArr = array();
		$settingArr = $this->setting;
		if(array_key_exists('payment_type',$this->request->query) &&
		   !empty($this->request->query['payment_type'])){
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
		
		if(array_key_exists('invoice_detail',$this->request->query) &&
		   !empty($this->request->query['invoice_detail'])){
			$invoiceSearchKeyword = $this->request->query['invoice_detail'];
			$this->set('invoiceSearchKeyword',$this->request->query['invoice_detail']);
		}
		
		if(array_key_exists('start_date',$this->request->query) &&
		   !empty($this->request->query['start_date'])){
			$this->set('start_date',$this->request->query['start_date']);
		}
		
		if(array_key_exists('end_date',$this->request->query) &&
		   !empty($this->request->query['end_date'])){
			$this->set('end_date',$this->request->query['end_date']);
		}
		
		
		if(array_key_exists('start_date',$this->request->query) &&
		   array_key_exists('end_date',$this->request->query) &&
		   !empty($this->request->query['start_date']) &&
		   !empty($this->request->query['end_date'])){
			if(array_key_exists('date_type',$this->request->query)){
				$date_type = $this->request->query['date_type'];
                $this->set(compact('date_type'));
				if($date_type == "payment"){
					$conditionArr[] = array( 
						"created >=" => date('Y-m-d', strtotime($this->request->query['start_date'])),
						"created <" => date('Y-m-d', strtotime($this->request->query['end_date']. ' +1 Days')),			
					       );
				}else{
					$conditionArr1 = array();
					$conditionArr1[] = array(
						"created >" => date('Y-m-d', strtotime($this->request->query['start_date'])),
						"created <" => date('Y-m-d', strtotime($this->request->query['end_date']. ' +1 Days')),			
					       );
					$Receipts_query = $receiptTable->find('list',array(
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
					$conditionArr['product_receipt_id IN'] = $Receipts;
				}
			}else{
				$conditionArr[] = array(
						"created >" => date('Y-m-d', strtotime($this->request->query['start_date'])),
						"created <" => date('Y-m-d', strtotime($this->request->query['end_date']. ' +1 Days')),			
					       );
			}
			
		}
		$agent_id = 0;
		if(array_key_exists('agent_id',$this->request->query) && !empty($this->request->query['agent_id'])){
			$agent_id = $this->request->query['agent_id'];
			$conditionArr['agent_id'] = $agent_id;
		}
		$this->set(compact('agent_id'));
		$customerResult = array();
		if(array_key_exists('search_kw',$this->request->query) &&
		   !empty($this->request->query['search_kw'])){
			$textKeyword = $this->request->query['search_kw'];
			if(!empty($textKeyword) && array_key_exists('invoice_detail',$this->request->query)){
				if($invoiceSearchKeyword=="receipt_number"){
					$conditionArr['product_receipt_id'] =  (int)$textKeyword;
				}elseif($invoiceSearchKeyword=="business"){
					//echo $textKeyword;die;
					$customerIds_query = $this->Customers->find('list',array(
																				'conditions'=>array(
															"OR" => array(
															"LOWER(`Customers`.`business`) like" => strtolower("%$textKeyword%"),
															"LOWER(`Customers`.`fname`) like" => strtolower("%$textKeyword%"),
															"LOWER(`Customers`.`lname`) like" => strtolower("%$textKeyword%"),
															)									    
												    ),
																	'valueField' => 'id',			
																	));
					$customerIds_query = $customerIds_query->hydrate(false);
					if(!empty($customerIds_query)){
						$customerIds = $customerIds_query->toArray();
					}else{
						$customerIds = array();
					}
					//pr($customerIds);die;
					$conditionArr['product_receipt_id IN'] = 0;
					if(count($customerIds) > 0){
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
						//if date range search
						//pr($searchCriteria);die;
						$cutomerReceipts_query = $receiptTable->find('all',array('fields' => array('id'),
															'conditions' => $searchCriteria,
															));
						//pr($cutomerReceipts_query);die;
						$cutomerReceipts_query = $cutomerReceipts_query->hydrate(false);
						if(!empty($cutomerReceipts_query)){
							$cutomerReceipts = $cutomerReceipts_query->toArray();
						}else{
							$cutomerReceipts = array();
						}
						//pr($cutomerReceipts);die;
						$receiptIDs = array();

						if( count($cutomerReceipts) ){
							//echo $cutomerReceipts['ProductReceipt']['id'];
							foreach($cutomerReceipts as $cutomerReceipt){
								$receiptIDs[] = $cutomerReceipt['id'];
							}
							if(empty($receiptIDs)){
								$receiptIDs = array(0 => null);
							}
							$conditionArr['product_receipt_id IN'] = $receiptIDs;
						}
					}
					//pr($conditionArr);die;
				}elseif($invoiceSearchKeyword=="customer_id"){//invoice_detail
					$customerID =  (int)$textKeyword;
					$searchCriteria['customer_id'] = $customerID;
					if(array_key_exists('start_date',$this->request->query) &&
						array_key_exists('end_date',$this->request->query) &&
						!empty($this->request->query['start_date']) &&
						!empty($this->request->query['end_date'])){
						//$conditionArr = array(
						//			 "created >" => date('Y-m-d', strtotime($this->request->query['start_date'])),
						//			 "created <" => date('Y-m-d', strtotime($this->request->query['end_date']. ' +1 Days')),			
						//				);
						$date_type = $this->request->query['date_type'];
						if($date_type == "payment"){
							
						}else{
							$searchCriteria[] = array(
									 "created >" => date('Y-m-d', strtotime($this->request->query['start_date'])),
									 "created <" => date('Y-m-d', strtotime($this->request->query['end_date']. ' +1 Days')),			
										);							
						}

					 }
					// pr($searchCriteria);die;
					$cutomerReceipts_query = $receiptTable->find('all',array('fields' => array('id'),
															'conditions' => $searchCriteria));
					$cutomerReceipts_query = $cutomerReceipts_query->hydrate(false);
					if(!empty($cutomerReceipts_query)){
						$cutomerReceipts = $cutomerReceipts_query->toArray();
					}else{
						$cutomerReceipts = array();
					}
					
					//pr($cutomerReceipts);die;
					$receiptIDs = array();
					$conditionArr['product_receipt_id IN'] = 0;
					if( count($cutomerReceipts) ){
						//echo $cutomerReceipts['ProductReceipt']['id'];
						foreach($cutomerReceipts as $cutomerReceipt){
							$receiptIDs[] = $cutomerReceipt['id'];
						}
						if(empty($receiptIDs)){
							$receiptIDs = array(0 => null);
						}
						$conditionArr['product_receipt_id IN'] = $receiptIDs;
					}
				}
				$this->set('textKeyword',$this->request->query['search_kw']);
			}
		}
		$listPaymentDet_query = $paymentTable->find('all',array('fields'=>array('product_receipt_id','amount'),'conditions'=>$conditionArr));
        $listPaymentDet = $listPaymentDet_query->hydrate(false);
        if(!empty($listPaymentDet)){
            $listPaymentDet = $listPaymentDet->toArray();
        }else{
			$listPaymentDet = array();
		}
		$listPayment = array();
		foreach($listPaymentDet as $lp => $list_payment){
			if(array_key_exists($list_payment['product_receipt_id'],$listPayment)){
				$listPayment[$list_payment['product_receipt_id']]+= $list_payment['amount'];
			}else{
				$listPayment[$list_payment['product_receipt_id']] = $list_payment['amount'];
			}
		}
		$lptotalPaymentAmount = 0;
		$lpgrandNetAmount = 0;
		$totalBillCost = 0;
		$totalCost = 0;
		
		if(count($listPayment)){
			$receiptIdArr = array();
			$productReceiptDetail = array();
			if($listPayment){
				$receiptIdArr = array_keys($listPayment);
			}
					//code for getting cost price of products*******************
                    $this->loadModel('KioskProductSales');
					if(count($receiptIdArr)){
						$productQttArr_query = $kioskProdctSalesTable->find('all', array('conditions' => array('product_receipt_id IN' => $receiptIdArr), 'fields' => array('product_id','quantity','product_receipt_id'), 'recursive' => -1));
                        $productQttArr_res = $productQttArr_query->hydrate(false);
                        if(!empty($productQttArr_res)){
                            $productQttArr = $productQttArr_res->toArray();
                        }
						$receiptIdDetail = array();
						$productIdsArr = array();
						foreach($productQttArr as $key => $productQtt){
							//$receiptIdDetail[$productQtt['KioskProductSale']['product_receipt_id']][] = $productQtt['KioskProductSale'];
							$productIdsArr[$productQtt['product_id']] = $productQtt['product_id'];
						}
						if(empty($productIdsArr)){
							$productIdsArr = array(0 => null);
						}
                        $costPriceList_query = $this->Products->find('list',
                                                         ['conditions' => [
                                                                           'Products.id IN' => $productIdsArr
                                                                           ],
                                                                'keyField' => 'id',
                                                                'valueField' => 'cost_price',
                                                         ]);
                        $costPriceList_res =$costPriceList_query->hydrate(false);
                        if(!empty($costPriceList_res)){
                            $costPriceList = $costPriceList_res->toArray();
                        }

						foreach($productQttArr as $key => $productQtt){
							if(!array_key_exists($productQtt['product_id'],$costPriceList)){continue;}
							$costPrice = $costPriceList[$productQtt['product_id']]*$productQtt['quantity'];
							$totalCost+=$costPrice;
						}
					}
					//*********************till here
			if(count($listPayment) && count($conditionArr)){
                if(empty($receiptIdArr)){
                    $receiptIdArr = array(0 =>null);
                }
				$productReceiptDetail_query = $receiptTable->find('all',array('conditions'=>array('id IN'=>$receiptIdArr),'fields'=>array('id','vat','status','bill_cost','created'),'recursive'=>-1));
			}else{
				$productReceiptDetail_query = $receiptTable->find('all',array('fields'=>array('id','vat','status','bill_cost','created'),'recursive'=>-1));
			}
            $productReceiptDetail_res = $productReceiptDetail_query->hydrate(false);
            if(!empty($productReceiptDetail_res)){
                $productReceiptDetail = $productReceiptDetail_res->toArray();
            }else{
                $productReceiptDetail = array();
            }
        
			$createdArr = array();
			foreach($productReceiptDetail as $key=>$productReceiptDta){
				//showing the data with product receipt status == 0 ie. the payment went through and data saved properly
				if($productReceiptDta['status']==0){
					$paymentAmount = 0;
					$createdArr[$productReceiptDta['id']] = $productReceiptDta['created'];
					$totalBillCost+=floatval($productReceiptDta['bill_cost']);
					if(array_key_exists($productReceiptDta['id'],$listPayment)){
						$paymentAmount = $listPayment[$productReceiptDta['id']];
					}
					$lptotalPaymentAmount+=floatval($paymentAmount);
					$vatPercentage = $productReceiptDta['vat']/100;
					$netAmount = $paymentAmount/(1+$vatPercentage);
					$lpgrandNetAmount+=floatval($netAmount);
				}
			}
		}
		
		/*$lptotalPaymentAmount = 0;
		$lpgrandNetAmount = 0;
		foreach($productReceiptDetail as $key=>$productReceiptDta){
			//showing the data with product receipt status == 0 ie. the payment went through and data saved properly
			if($productReceiptDta['ProductReceipt']['status']==0){
				$totalBillCost+=floatval($productReceiptDta['ProductReceipt']['bill_cost']);
                $receiptIdArr[$productReceiptDta['ProductReceipt']['id']] = $productReceiptDta['ProductReceipt']['id'];//capturing the receipt ids
				$paymentAmount = $productReceiptDta['ProductReceipt']['bill_amount'];
				$lptotalPaymentAmount+=floatval($paymentAmount);
				$vatPercentage = $productReceiptDta['ProductReceipt']['vat']/100;
				$netAmount = $paymentAmount/(1+$vatPercentage);
				$lpgrandNetAmount+=$netAmount;
			}
		}*/
		
		$lptotalVat = $lptotalPaymentAmount - $lpgrandNetAmount;
		$lptotalVat = $lptotalVat;
		$this->set(compact('lptotalPaymentAmount','lpgrandNetAmount','lptotalVat','totalCost'));
		
		//pr($conditionArr);die;
         $this->paginate = [
                                    'order' => [
                                        'product_receipt_id' => 'DESC'
                                    ],
                                    'conditions' => [$conditionArr],
									'limit' => 50
                                   // 'contain' => ['ProductReceipts']
                                ];
		
        //pr($this->paginate);die;
		$productReceipts = $this->paginate($paymentTable);
		//pr($productReceipts);die;
		$recipt_table_data = array();
        $y_recipt_ids = array();
		foreach($productReceipts as $s_key => $s_value){
			$y_recipt_ids[] = $s_value->product_receipt_id;
		}
		if(empty($y_recipt_ids)){
            $y_recipt_ids = array(0 => null);
        }
		$recipt_table_data_query = $receiptTable->find('all',[
								   'conditions' => ['id IN' => $y_recipt_ids]
								   ]);
		$recipt_table_data_query = $recipt_table_data_query->hydrate(false);
		if(!empty($recipt_table_data_query)){
			$recipt_table_data = $recipt_table_data_query->toArray();
		}else{
			$recipt_table_data = array();
		}
		//pr($recipt_table_data);die;
		$this->set(compact('recipt_table_data'));
		//$this->Paginator->settings = array(
		//				'conditions' => $conditionArr,
		//				'limit' => 50,
		//				'order' => 'PaymentDetail.id DESC'
		//				   );
		
		//$productReceipts = $this->Paginator->paginate('PaymentDetail');
		
		$reciptTableData = $customerIdArr = array();
		foreach($recipt_table_data as $receiptDetail){
			//pr($receiptDetail);die;
			$reciptTableData[$receiptDetail['id']] = $receiptDetail;
			$customerIdArr[] = $receiptDetail['customer_id'];
		}
		$this->set('recipt_table_data', $reciptTableData);
		if(empty($customerIdArr)){
			$customerIdArr = array(0 => null); 
		}
		$customerBusiness_query = $this->Customers->find('list',
                                                         ['conditions' => [
                                                                           'Customers.id IN' => $customerIdArr
                                                                           ],
                                                                 'keyField' => 'id',
                                                                'valueField' => 'business',
                                                         ]);
        $customerBusiness = $customerBusiness_query->hydrate(false);
        if(!empty($customerBusiness)){
            $customerBusiness = $customerBusiness->toArray();
        }else{
            $customerBusiness = array();
        }
		if($this->request->session()->read('Auth.User.group_id') == ADMINISTRATORS || $this->request->session()->read('Auth.User.group_id') == FRANCHISE_OWNER){
			//$session_id = $this->Session->id();
			$user_id = $this->request->Session()->read('Auth.User.id');
			$data_arr = array('kiosk_id' => $kiosk_id);
			$jsondata = json_encode($data_arr);
			$this->loadModel('UserSettings');
			$res_query = $this->UserSettings->find('all',array('conditions' => array(
																		 'user_id' => $user_id,
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
									'setting_name' => "search",
									'data' => $jsondata
									);
				$entity = $this->UserSettings->get($userSettingid);
				$entity = $this->UserSettings->patchEntity($entity,$data_to_save);
				$this->UserSettings->save($entity);
			}else{
				$data_to_save = array(
									'user_id' => $user_id,
									'user_session_key' => $session_id,
									'setting_name' => "search",
									'data' => $jsondata
									);
				$entity = $this->UserSettings->newEntity();
				$entity = $this->UserSettings->patchEntity($entity,$data_to_save);
				$this->UserSettings->save($data_to_save);
			}
		}
		$kiosks_query = $this->Kiosks->find('list');
		$kiosks_query = $kiosks_query->hydrate(false);
		if(!empty($kiosks_query)){
			$kiosks = $kiosks_query->toArray();
		}else{
			$kiosks = array();
		}
		
		$agents_query = $this->Agents->find('list');
		$agents_query = $agents_query->hydrate(false);
		
		if(!empty($agents_query)){
			$agents = $agents_query->toArray();
		}
		$agents[0] = "Select Acc manager";
		ksort($agents);
		
		$log_refined_data = array();
		$log_table_data = $this->PmtLogs->find("all",[
														['conditions' => ['kiosk_id' => $kiosk_id]],
													  'order' => ['created DESC'],
													  ])->toArray();
		if(!empty($log_table_data)){
			foreach($log_table_data as $log_key => $log_value){
					$log_refined_data[$log_value->pmt_id][] = $log_value;
			}
		}
		
		$users = $this->Users->find("list",['keyField' => 'id',
								   'valueField' => 'f_name',
								   ])->toArray();
		
		
		$this->set(compact('productReceipts','customerBusiness','totalAmount','totalBillCost','createdArr','kiosks','agents','log_refined_data','users'));
		$this->render('invoice_payment_clearness');
	}
	
	public function viewLog(){
		$kiosk_id = $this->request->Session()->read('kiosk_id');
		if(array_key_exists("pass",$this->request->params) && array_key_exists(0,$this->request->params['pass'])){
			if(!empty($this->request->params['pass'][0])){
				$payment_table_id = $this->request->params['pass'][0];
			}
		}
		if(array_key_exists("pass",$this->request->params) && array_key_exists(1,$this->request->params['pass'])){
			if(!empty($this->request->params['pass'][1])){
				$kiosk_id = $this->request->params['pass'][0];
			}
		}
		
		
		if(empty($kiosk_id) || $kiosk_id == 10000){
			$kioskProdctSaleTable_source = "kiosk_product_sales";
				$product_recit_table_source = "product_receipts";
				$paymentTable_source = "payment_details";
		}else{
			$kioskProdctSaleTable_source = "kiosk_{$kiosk_id}_product_sales";
			$product_recit_table_source = "kiosk_{$kiosk_id}_product_receipts";
			$paymentTable_source = "kiosk_{$kiosk_id}_payment_details";
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
		
		
		
		
		$log_table_data = $this->PmtLogs->find("all",['conditions' => [
													 'pmt_id' => $payment_table_id,
													 ]])->toArray();
		$users = $this->Users->find("list",['keyField' => 'id',
								   'valueField' => 'f_name',
								   ])->toArray();
		
		
		
		$this->set(compact("log_table_data","users"));
	}
	
	function drCustomerAccountStatement(){
		//pr($this->request);die;
        $settingArr = $this->setting;
        $kiosk_id = $this->request->query['kiosk'];
		if($kiosk_id == 10000){
			$kiosk_id =0;
		}
        //Start: sending email to customer
        //if(!empty($this->request->query['email2Customer']) && $this->request->query['email2Customer'] == 1){
        if(array_key_exists('customer_email', $this->request['data']) && !empty($customerEmail = $this->request['data']['customer_email'])){
            $emailContent = $this->request['data']['emailContent'];
            $customerEmail = $this->request['data']['customer_email'];
            if(!empty($customerEmail)){
                $to = $customerEmail;
                $subject = "Account Statement";
                
                $headers = "MIME-Version: 1.0" . "\r\n";
                $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
				$send_by_email = Configure::read('send_by_email');
                $headers .= "From: <$send_by_email>" . "\r\n";
                
                if(mail($to, $subject, base64_decode($emailContent), $headers)){
                    $this->Flash->success(__("Account Statement has been successfully sent to email address: {$customerEmail}."));
                    return $this->redirect(array('action'=>"index"));
                }else{
                    die("Failed to sent eamil statement");
                    $this->Flash->success(__("Failed to sent eamil statement"));
                }
            }else{
                die("Please input customer eamil address");
                $this->Flash->success(__("Please input customer eamil address"));
            }
         }
         
        //End: sending email to customer
        $k_id_to_use = $kiosk_id;
        $custId = 0;
		$productReceiptSource = "t_product_receipts";
		$paymentDetailSource = "t_payment_details";
		$crRecptSrc = "t_credit_receipts";
		$creditPmtDetSrc = "t_credit_payment_details";
		
		$startDate = $endDate = "";
        if(!empty($this->request->query['start_date'])){
			$startDate = date("Y-m-d",strtotime($this->request->query['start_date'].'-1 day'));
		}
		
		if(!empty($this->request->query['end_date'])){
			$endDate = date("Y-m-d",strtotime($this->request->query['end_date'].'+1 day'));
		}
		
		if(!empty($this->request->query['customer_number'])){
			//$conditionArr['customer_id'] = $this->request->query['customer_number'];
			$custId = $this->request->query['customer_number'];
		}
        $combineArr = $custDetArr = array();
		$cutomer_query = $this->Customers->find('all',array('conditions' => array('Customers.id' => $custId)));
        $custDetails = $cutomer_query->hydrate(false);
        if(!empty($custDetails)){
            $custDetArr = $custDetails->first(); //pr($custDetArr);
        }
        
		if(!empty($startDate) || !empty($endDate)){
			$conditions = " AND (DATE(`cpd`.`created`) BETWEEN '$startDate' AND '$endDate')";
		}else{
			$conditions = "";
		}
        $conditions = " AND `cr`.`kiosk_id`='$kiosk_id'";
		//echo "SELECT *,`cr`.`created` as `recpt_date`, `cpd`.created as `pmt_date` FROM `$crRecptTbl` as `cr`, `$creditPmtDetSrc` as `cpd` WHERE `cr`.`id` = `cpd`.`credit_receipt_id` AND `cpd`.`payment_method`='On Credit' AND`cr`.`customer_id` = $custId".$conditions;die;
        
        
        $conn = ConnectionManager::get('default');
        $creditQry = "SELECT *,`cr`.`created` as `recpt_date`, `cpd`.modified as `pmt_date` FROM `$crRecptSrc` as `cr`, `$creditPmtDetSrc` as `cpd` WHERE `cr`.`id` = `cpd`.`credit_receipt_id`  AND`cr`.`customer_id` = $custId".$conditions; //AND `cpd`.`payment_method`='On Credit' //echo $creditQry."<br/>";
        $stmt = $conn->execute($creditQry);
        $crRecptData = $stmt ->fetchAll('assoc');   //pr($crRecptData);die;
        //Credit Note Tbls, Different fields (2) : credit_amount, kiosk_id
        
		$cnRecepts = $invReceptIDs = $creditReceptIDs = $prPmtDate = $crPmtDate = array();
        
		foreach($crRecptData as $key => $crData){
			$combineArr[] =$crPmtDate[] = $crData['pmt_date'];
            if(!in_array($crData['credit_receipt_id'], $creditReceptIDs)) $creditReceptIDs[] = $crData['credit_receipt_id'];
		}
        
		//Start: Get Credit Note receipts
        if(count($creditReceptIDs)){
            $cnReceptQry = "SELECT `id`, `customer_id`, `bill_amount`, `orig_bill_amount`, `created` as `cred_note_date` FROM `$crRecptSrc` as `pr` WHERE `pr`.`id` IN(".implode(",", $creditReceptIDs).") AND `pr`.`customer_id` = $custId"; //echo $invQry."<br/>";
            $stmtInv = $conn->execute($cnReceptQry); 
            $cnRecepts = $stmtInv ->fetchAll('assoc');
            foreach($cnRecepts as $key => $cnRecept){
                $combineArr[] = $cnRecept['cred_note_date'];
            }
        }
        //Eng: Get Credit Note receipts
        
		$conditions = str_replace("cr","pr",$conditions);
        $invQry = "SELECT *,`pr`.`created` as `recpt_date`, `pd`.created as `pmt_date` FROM `$productReceiptSource` as `pr`, `$paymentDetailSource` as `pd` WHERE `pr`.`id` = `pd`.`product_receipt_id` AND  `pr`.`customer_id` = $custId".$conditions;//echo $invQry."<br/>";
        //`pd`.`payment_method` <> 'On Credit' AND 
		$stmt_pr = $conn->execute($invQry); 
        $prRecptData = $stmt_pr ->fetchAll('assoc');
		
        //Invoice tables, Different fields(4) : vat_number,sale_type, bulk_invoice, invoice_order_id
        
		foreach($prRecptData as $key => $prData){
			$combineArr[] = $prPmtDate[] = $prData['pmt_date'];
            if(!in_array($prData['product_receipt_id'], $invReceptIDs)) $invReceptIDs[] = $prData['product_receipt_id'];
		}
        
        //Start: remove entries from invoice for which amount is matching with amount of invoices
        //pr($prRecptData);
        foreach($crRecptData as $key => $crData){
            foreach($prRecptData as $key1 => $prData){
                if($crData['amount'] == $prData['amount']){
                    unset($prRecptData[$key1]);break;
                }
            }
        }
        //echo "after unset";pr($prRecptData);
        //End: remove entries from invoice for which amount is matching with amount of invoices
        //Start: Get invoice receipts
        $invRecepts = array();
        if(count($invReceptIDs)){
            /*$prodReceptTbl = TableRegistry::get($productReceiptSource,['table' => $productReceiptSource]);
            $invRecepts = $prodReceptTbl->find('all', ['conditions' => ['id IN' => $invReceptIDs], 'recursive' => -1, 'fields' => array('id', 'customer_id', 'bill_amount', 'orig_bill_amount', 'created')])->toArray();*/
            
            $invReceptQry = "SELECT `id`, `customer_id`, `bill_amount`, `orig_bill_amount`, `created` as `inv_date` FROM `$productReceiptSource` as `pr` WHERE `pr`.`id` IN(".implode(",",$invReceptIDs).") AND `pr`.`customer_id` = $custId"; //echo $invQry."<br/>";
            $stmtInv = $conn->execute($invReceptQry); 
            $invRecepts = $stmtInv ->fetchAll('assoc');//pr($invRecepts);die;
            foreach($invRecepts as $key => $invRecept){
                $combineArr[] = $invRecept['inv_date'];//$invRecept['created']->i18nFormat('yyyy-MM-dd HH:mm:ss');
            }
        }
        //Eng: Get invoice receipts
        
        //join arrays
        $crInvRecptData = $crRecptData;                                             //1. Adding credit notes payments data to $crInvRecptData array
        foreach($prRecptData as $key => $prData){$crInvRecptData[] = $prData;}      //2. Adding invoice payments data to $crInvRecptData array
        foreach($invRecepts as $key => $prData){$crInvRecptData[] = $prData;}       //3. Adding invoices data to $crInvRecptData array
        foreach($cnRecepts as $key => $prData){$crInvRecptData[] = $prData;}        //4. Adding credit note data to $crInvRecptData array
        //pr($crInvRecptData);
		//$combineArr = array_merge($crPmtDate, $prPmtDate);
		usort($combineArr, array('self', 'sortFunction'));
        $recptInOrders = array();
        //pr($combineArr);//pr($crInvRecptData);die;
		//die;
        foreach($combineArr as $recptDate){
            foreach($crInvRecptData as $key => $reeptDatum){
                if(
                    ( array_key_exists('pmt_date', $reeptDatum) && $reeptDatum['pmt_date'] == $recptDate) ||
                    ( array_key_exists('inv_date', $reeptDatum) && $reeptDatum['inv_date'] == $recptDate)){
					
                    $itemNotPresent = true;
					
                    foreach($recptInOrders as $key => $recptInOrder){
						
                        if($recptInOrder['id'] == $reeptDatum['id']){
							
                            if( array_key_exists('cred_note_date', $recptInOrder) ){
                                //compare invoice numbers
                                if($recptInOrder['credit_receipt_id'] == $reeptDatum['product_receipt_id']){
                                    $itemNotPresent = false;
                                }
								
                            }elseif( array_key_exists('inv_date', $recptInOrder) && array_key_exists('inv_date', $reeptDatum) ){
                                //compare invoice numbers
                                 if($recptInOrder['id'] == $reeptDatum['id']){
                                    $itemNotPresent = false;
                                }
								
                            }elseif( array_key_exists('credit_receipt_id', $recptInOrder) && array_key_exists('credit_receipt_id', $reeptDatum) ){
                                //compare credit note numbers
                                if($recptInOrder['credit_receipt_id'] == $reeptDatum['credit_receipt_id']){
                                    $itemNotPresent = false;
                                }
								
                            }elseif( array_key_exists('product_receipt_id', $recptInOrder) && array_key_exists('product_receipt_id', $reeptDatum) ){
                                //compare invoice numbers
                                 if(array_key_exists('product_receipt_id', $reeptDatum) && $recptInOrder['product_receipt_id'] == $reeptDatum['product_receipt_id']){
                                    $itemNotPresent = false;
                                }
                            }
                        }
                        
                    }
					
                    if($itemNotPresent){
                        $recptInOrders[] = $reeptDatum;
                    }
					
                }
            }
        }
        //pr($recptInOrders);die;
//		$productReceiptTable = TableRegistry::get($productReceiptSource,['table' => $productReceiptSource]);
//		$paymentDetailTable = TableRegistry::get($paymentDetailSource,['table' => $paymentDetailSource]);
//        $crRecptTbl = TableRegistry::get($crRecptSrc, ['table' => $crRecptSrc]);
//		$creditPmtDetTbl = TableRegistry::get($creditPmtDetSrc, ['table' => $creditPmtDetSrc]);
		
		if($k_id_to_use == 0){
			$k_id_to_use = 10000;
		}
		$kioskDetails = "";
        $new_kiosk_data =  $this->Kiosks->find('all',array('conditions' => array('Kiosks.id' => $k_id_to_use)))->toArray();
		$kioskDetails_query = $this->Kiosks->find('all',array('conditions' => array('Kiosks.id' => $k_id_to_use)));
        
        $kioskDetails = $kioskDetails_query->hydrate(false);
        if(!empty($kioskDetails)){
            $kioskDetails = $kioskDetails->first();
        }else{
            $kioskDetails = array();
        }
        //-------------------------------------------------
        //Step 1 : Get all 
        //-------------------------------------------------
		//$conditionArr = array();
		//
		//
		//if(!empty($startDate) || !empty($endDate)){
		//	$conditionArr[] = array("DATE(`created`) > '$startDate'","DATE(`created`) < '$endDate'");
		//}
		//$conditionArr = array();
		//$productReceiptQry = $productReceiptTable->find("all",array("conditions" => $conditionArr));
		//$productReceiptQry = $productReceiptQry->hydrate(false);
		////pr($productReceiptQry);die;
		//if(!empty($productReceiptQry)){
		//	$productReceipt = $productReceiptQry->toArray();
		//}else{
		//	$productReceipt = array();
		//}
		////pr($productReceipt);die;
		//$statement = array();
		//pr($productReceipt);die;
		//if(!empty($productReceipt)){
		//	foreach($productReceipt as $key => $reciptData){
		//		$receptID = $reciptData['id'];
		//		$statmentArr[$receptID] = $reciptData;
		//		$paymentDetailQry = $paymentDetailTable->find("all",array("conditions" =>array('product_receipt_id' => $receptID)));
		//		$paymentDetailQry = $paymentDetailQry->hydrate(false);
		//              
		//		if(!empty($paymentDetailQry)){
		//			$paymentDetailQry = $paymentDetailQry->toArray();
		//		}else{
		//			$paymentDetailQry = array();
		//		}
		//		//pr($paymentDetailQry);die;
		//		$statmentArr[$receptID]['payment_details'] = $paymentDetailQry;
		//	}
		//}
		
		//pr($statmentArr);die;
        $this->set(compact('kioskDetails', 'settingArr', 'new_kiosk_data','statmentArr', 'recptInOrders', 'custDetArr', 'startDate', 'endDate'));
	}
	
	public function drProcessBulkInvoices(){
		$kiosk_id = $this->request->query["kiosk"];
		
		if($kiosk_id == 10000){
			$kiosk_id =0;
		}
		$kioskProdctSaleTable_source = "t_kiosk_product_sales";
		$product_recit_table_source = "t_product_receipts";
		$paymentTable_source = "t_payment_details";
		
		$receiptTable = TableRegistry::get($product_recit_table_source,[
																		'table' => $product_recit_table_source,
																	]);
			
		$kioskProdctSalesTable = TableRegistry::get($kioskProdctSaleTable_source,[
																		'table' => $kioskProdctSaleTable_source,
																	]);
			
		$paymentTable = TableRegistry::get($paymentTable_source,[
																		'table' => $paymentTable_source,
																	]);
		
		
		
		$start_date = $this->request->query["start_date"];
		$end_date = $this->request->query["end_date"];
		$payment_date_type = $this->request->query["payment_date_type"];
		
		$customer_number = $this->request->query["customer_number"];
		$acc_manger = $this->request->query["acc_manger"];
		
		if($acc_manger == "undefined"){
			$acc_manger = "";
		}
		$conditionArr = $searchCriteria = array();
		$conditionArr['payment_method like '] =  strtolower("%on credit%");
		$searchCriteria['customer_id'] = $customer_number;
		if(!empty($acc_manger)){
			//$conditionArr['agent_id'] = $acc_manger;
		}
		
		if($payment_date_type == "payment"){
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
				$conditionArr1['kiosk_id'] = $kiosk_id;
				$Receipts_query = $receiptTable->find('list',array(
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
				$conditionArr['product_receipt_id IN'] = $Receipts;
		}
		
		
		
		
		
		if(!empty($start_date) && !empty($end_date)){
				if($payment_date_type == "payment"){
								
				}else{
					$searchCriteria[] = array(
							 "created >" => date('Y-m-d', strtotime($this->request->query['start_date'])),
							 "created <" => date('Y-m-d', strtotime($this->request->query['end_date']. ' +1 Days')),			
								);							
				}
		}
				$searchCriteria['kiosk_id'] = $kiosk_id;
				$cutomerReceipts_query = $receiptTable->find('all',array('fields' => array('id'),
															'conditions' => $searchCriteria));
					$cutomerReceipts_query = $cutomerReceipts_query->hydrate(false);
					if(!empty($cutomerReceipts_query)){
						$cutomerReceipts = $cutomerReceipts_query->toArray();
					}else{
						$cutomerReceipts = array();
					}
					
					//pr($cutomerReceipts);die;
					$receiptIDs = array();
					$conditionArr['product_receipt_id IN'] = 0;
					if( count($cutomerReceipts) ){
						//echo $cutomerReceipts['ProductReceipt']['id'];
						foreach($cutomerReceipts as $cutomerReceipt){
							$receiptIDs[] = $cutomerReceipt['id'];
						}
						if(empty($receiptIDs)){
							$receiptIDs = array(0 => null);
						}
						$conditionArr['product_receipt_id IN'] = $receiptIDs;
					}
		
		$conditionArr['kiosk_id'] = $kiosk_id;
		
		$agent_id = 0;
		if(array_key_exists('agent_id',$this->request->query) && !empty($this->request->query['agent_id'])){
			$agent_id = $this->request->query['agent_id'];
			$agent_cust_res = $this->Customers->find("list",['conditions' => [
														   "agent_id" => $agent_id,
														   ],
															'keyField' => "id",
															"valueField" => "agent_id",
															])->toArray();
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
				$cutomerReceipts_query = $ProductReceiptTable->find('all',array('fields' => array('id'),
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
				$conditionArr['product_receipt_id IN'] = 0;
				if( count($cutomerReceipts) ){
					//echo $cutomerReceipts['ProductReceipt']['id'];
					foreach($cutomerReceipts as $cutomerReceipt){
						$receiptIDs[] = $cutomerReceipt['id'];
					}
					if(empty($receiptIDs)){
						$receiptIDs = array('0'=>null);
					}
					$conditionArr['product_receipt_id IN'] = $receiptIDs;
				}
						 
			}
			//$conditionArr['agent_id'] = $agent_id;
		}
		
		$pay_res = $paymentTable->find("all",[
				'conditions' => [$conditionArr],
				 'order' => [
                                        'product_receipt_id' => 'desc'
                                    ],
				])->toArray();
		//$pay_res_query = $pay_res_query->hydrate(false);
		//if(!empty($pay_res_query)){
		//	$pay_res = $pay_res_query->toArray();
		//}else{
		//	$pay_res = array();
		//}
		$total_amount = 0;
		foreach($pay_res as $s_key => $s_value){
			$recipt_ids[] = $s_value->product_receipt_id;
			$total_amount += $s_value->amount;
		}
		
		
		if(empty($recipt_ids)){
            $recipt_ids = array(0 => null);
        }
		$recipt_table_data_query = $receiptTable->find('all',[
								   'conditions' => ['id IN' => $recipt_ids,'kiosk_id'=>$kiosk_id]
								   ]);
		$recipt_table_data_query = $recipt_table_data_query->hydrate(false);
		if(!empty($recipt_table_data_query)){
			$recipt_table_data = $recipt_table_data_query->toArray();
		}else{
			$recipt_table_data = array();
		}
		
		foreach($recipt_table_data as $receiptDetail){
			$reciptTableData[$receiptDetail['id']] = $receiptDetail;
		}
		$recipt_table_data = array();
		if(!empty($reciptTableData)){
			$recipt_table_data = $reciptTableData;
		}
		
		$kiosks = $this->Kiosks->find('list',[
                                                'keyField' => 'id',
                                                'valueField' => 'name',
                                                'conditions' => ['Kiosks.status' => 1],
                                                'order' => ['Kiosks.name asc']
                                            ])->toArray();
		
		$customer_data = $this->Customers->find('all',
                                                         ['conditions' => [
                                                                           'Customers.id' => $customer_number
                                                                           ],
                                                         ])->toArray();
		
		$agents_query = $this->Agents->find('list');
		$agents_query = $agents_query->hydrate(false);
		
		if(!empty($agents_query)){
			$agents = $agents_query->toArray();
		}
		// get credit details
		$CreditReceiptSource = "t_credit_receipts";
		$CreditPaymentDetailSource = "t_credit_payment_details";
		
		$CreditReceiptTable = TableRegistry::get($CreditReceiptSource,[
															   'table' => $CreditReceiptSource,
														    ]);
		$CreditPaymentDetailTable = TableRegistry::get($CreditPaymentDetailSource,[
                                                                                    'table' => $CreditPaymentDetailSource,
                                                                                ]);
		
		$credit_receipt = $CreditReceiptTable->find('all',[
																	  'conditions' => ['customer_id' => $customer_number,
																					   'kiosk_id'=>$kiosk_id]
																	  ])->toArray();
		$credit_data = $credit_pay_res = array();
		if(!empty($credit_receipt)){
			 $credit_receipt_ids = $credit_conditionArr = array();
			foreach($credit_receipt as $c_key => $c_value){
				$credit_data[$c_value->id] = $credit_receipt[$c_key];
				$credit_recipt_ids[] = $c_value->id;
			}
			$credit_conditionArr['credit_receipt_id IN'] =  $credit_recipt_ids;
			$credit_conditionArr['payment_method like '] =  strtolower("%on credit%");
			$credit_pay_res = $CreditPaymentDetailTable->find("all",[
												   'conditions' => $credit_conditionArr,
												   'kiosk_id'=>$kiosk_id
												   ])->toArray();
			//pr($credit_pay_res);die;
		}
		if($kiosk_id == 0){
			$kiosk_id =10000;
		}
		$this->set(compact("total_amount","pay_res","kiosks","kiosk_id","recipt_table_data","customer_data","agents","credit_pay_res","credit_data"));
	}
	
	public function drProcessCart(){
		
		$users = $this->Users->find("list",['keyField' => 'id',
								   'valueField' => 'f_name',
								   ])->toArray();
		
		if(array_key_exists("ids",$this->request->query) && !empty($this->request->query['ids'])){
			$ids = $this->request->query['ids'];
		}else{
			$msg = array("msg" => "NO Invoice Selected");
			echo json_encode($msg);die;
		}
		
		if(array_key_exists("total_amt",$this->request->query) && !empty($this->request->query['total_amt'])){
			$total_amt = $this->request->query['total_amt'];
		}else{
			$msg = array("msg" => "NO Final Amt Found");
			echo json_encode($msg);die;
		}
		
		if(array_key_exists("payment_method",$this->request->query) && !empty($this->request->query['payment_method'])){
			$payment_method = $this->request->query['payment_method'];
		}else{
			$msg = array("msg" => "NO Payment Method Selected");
			echo json_encode($msg);die;
		}
		
		if(array_key_exists("kiosk_id",$this->request->query) && !empty($this->request->query['kiosk_id'])){
			$kiosk_id = $this->request->query['kiosk_id'];
		}else{
			$msg = array("msg" => "NO kiosk_id Found");
			echo json_encode($msg);die;
		}
		
		if($kiosk_id == 10000){
			$kiosk_id = 0;
		}
		
		if(array_key_exists("date",$this->request->query) && !empty($this->request->query['date'])){
            $time = date("h:i:s");
			$date = $this->request->query['date'].' '.$time;
		}else{
			$date = "";
		}
		
		if(array_key_exists("memo_content",$this->request->query) && !empty($this->request->query['memo_content'])){
			$memo_content = trim($this->request->query['memo_content'])." | ".$total_amt;
		}else{
			$memo_content = ""." | ".$total_amt;
		}
		
		
		
		$ids_arr = explode(",",$ids);
		$timestamp = strtotime(date("d-m-y"));
		
		$kioskProdctSaleTable_source = "t_kiosk_product_sales";
		$product_recit_table_source = "t_product_receipts";
		$paymentTable_source = "t_payment_details";
		
		
		
		$receiptTable = TableRegistry::get($product_recit_table_source,[
																		'table' => $product_recit_table_source,
																	]);
			
		$kioskProdctSalesTable = TableRegistry::get($kioskProdctSaleTable_source,[
																		'table' => $kioskProdctSaleTable_source,
																	]);
			
		$paymentTable = TableRegistry::get($paymentTable_source,[
																		'table' => $paymentTable_source,
																	]);
		
		
		$pay_res = $paymentTable->find("all",[
								   'conditions' => [
									'id IN' => $ids_arr,
									'kiosk_id' => $kiosk_id,
								   ],
								   'order' => [
                                        'amount' => 'asc'
                                    ],
								   ])->toArray();
		
		
		
		$user_id = $this->request->Session()->read('Auth.User.id');
		if(!empty($pay_res)){
			$outsatanding_amt = $org_amt = $counter = $remaining_amt = 0;
			foreach($pay_res as $key => $value){
				if(!empty($date)){
					$recipt_id = $value->product_receipt_id;
					$recipt_res = $receiptTable->find("all",[
											   'conditions' => ['id' => $recipt_id,
																'kiosk_id' => $kiosk_id,
																],
											   ])->toArray();
					if(!empty($recipt_res)){
						$recipt_created = $recipt_res[0]['created'];
						if(strtotime($recipt_created) > strtotime($date)){
							$date = date("Y-m-d G:i:s",strtotime($recipt_created));
						}else{
							$date = date("Y-m-d G:i:s",strtotime($date));
						}
					}
				}
				$amount = $value->amount;
				if($counter == 0){
					$org_amt = $total_amt;
					$remaining_amt = $total_amt-$amount;
				}else{
					$org_amt = $remaining_amt;
						
						$remaining_amt = (string)$remaining_amt;
						$remaining_amt = (float)$remaining_amt;
						
						$org_amt = (string)$org_amt;
						$org_amt = (float)$org_amt;
						
						$amount = (string)$amount;
						$amount = (float)$amount;
						
					$remaining_amt = $remaining_amt-$amount;
				}
				
				if($remaining_amt>=0){
					$payment_id = $value->id;
					if(!empty($date)){
						$data_to_save = array(
										  "created" => date("Y-m-d G:i:s",strtotime($date)),
										  "pmt_ref_id" => $timestamp,
										  "payment_method" => $payment_method);
					}else{
						$data_to_save = array(
										  "created" => date("Y-m-d G:i:s"),
										  "pmt_ref_id" => $timestamp,
										  "payment_method" => $payment_method);
					}
					
					$entity = $paymentTable->get($payment_id);
					$entity = $paymentTable->patchEntity($entity,$data_to_save);
					$paymentTable->save($entity);
					
					$logData = array(
								 'user_id'=>$user_id,
								 'pmt_id'=>(int)$payment_id,
								 'kiosk_id' => $kiosk_id,
								 'old_pmt_method'=>"On Credit",
								 'pmt_method'=>$payment_method,
								 'receipt_id'=>$value->product_receipt_id,
								 'adjusted_by' => 1,
								 'memo'=>$memo_content,
								 'receipt_type' => 3,
								);
			
					$newLog = $this->PmtLogs->newEntity($logData);
					$patchLog = $this->PmtLogs->patchEntity($newLog,$logData);
					$this->PmtLogs->save($patchLog);
					
					$logid = $patchLog->id;
					$log_created = $patchLog->created;
					$firstName = $users[$user_id];
					if(empty($firstName)){
						$firstName = "Missing Name";
					}
					$str = date("d/m/Y h:i",strtotime($log_created)).", ".$firstName.", "."On Credit"."->"."Cash";
					if(!empty($memo_content)){
						$str.="||".$memo_content;
					}
					
					
					$data = array("description" => $str);
					$pay_res = $paymentTable->get($value->id);
					$pay_res = $paymentTable->patchEntity($pay_res,$data);
					$paymentTable->save($pay_res);
					
				}else{
					$outsatanding_amt = $amount - $org_amt;
					
					$payment_id = $value->id;
					if(!empty($date)){
						$data_to_save = array("payment_method" => $payment_method,
										  "amount" => $org_amt,
										  "pmt_ref_id" => $timestamp,
										  "created" => date("Y-m-d G:i:s",strtotime($date)),
										  );
					}else{
						$data_to_save = array("payment_method" => $payment_method,
										  "amount" => $org_amt,
										  "pmt_ref_id" => $timestamp,
										  "created" => date("Y-m-d G:i:s"),
										  );
					}
					
					$entity = $paymentTable->get($payment_id);
					$entity = $paymentTable->patchEntity($entity,$data_to_save);
					$paymentTable->save($entity);
					
					$logData = array(
								 'user_id'=>$user_id,
								 'pmt_id'=>(int)$payment_id,
								 'kiosk_id' => $kiosk_id,
								 'old_pmt_method'=>"On Credit",
								 'pmt_method'=>$payment_method,
								 'receipt_id'=>$value->product_receipt_id,
								 'adjusted_by' => 1,
								 'memo'=>$memo_content,
								 'receipt_type' => 3,
								);
			
					$newLog = $this->PmtLogs->newEntity($logData);
					$patchLog = $this->PmtLogs->patchEntity($newLog,$logData);
					$this->PmtLogs->save($patchLog);
					
					$logid = $patchLog->id;
					$log_created = $patchLog->created;
					$firstName = $users[$user_id];
					if(empty($firstName)){
						$firstName = "Missing Name";
					}
					$str = date("d/m/Y h:i",strtotime($log_created)).", ".$firstName.", "."On Credit"."->"."Cash";
					if(!empty($memo_content)){
						$str.="||".$memo_content;
					}
					
					
					$data = array("description" => $str);
					$pay_res = $paymentTable->get($value->id);
					$pay_res = $paymentTable->patchEntity($pay_res,$data);
					$paymentTable->save($pay_res);
					
					if(!empty($date)){
						$data_to_save1 = array(
										   "payment_method" => "On Credit",
											"amount" => $outsatanding_amt,
											"product_receipt_id" => $value->product_receipt_id,
											"created" => $value->created,
											'kiosk_id' => $kiosk_id,
										   );
					}else{
						$data_to_save1 = array(
										   "payment_method" => "On Credit",
											"amount" => $outsatanding_amt,
											"product_receipt_id" => $value->product_receipt_id,
											"created" => $value->created,
											'kiosk_id' => $kiosk_id,
										   );
					}
					
					$entity1 = $paymentTable->newEntity();
					$entity1 = $paymentTable->patchEntity($entity1,$data_to_save1);
					$paymentTable->save($entity1);
				}
				$counter++;
			}
			if($counter > 0){
				$msg = array("msg" => "Done");
				echo json_encode($msg);die;
			}
		}
	}
	
	
	public function drProcessCreditCart(){
		
		$users = $this->Users->find("list",['keyField' => 'id',
								   'valueField' => 'f_name',
								   ])->toArray();
		
		if(array_key_exists("ids",$this->request->query) && !empty($this->request->query['ids'])){
			$ids = $this->request->query['ids'];
		}else{
			$msg = array("msg" => "NO Invoice Selected");
			echo json_encode($msg);die;
		}
		
		if(array_key_exists("credit_id",$this->request->query) && !empty($this->request->query['credit_id'])){
			$credit_id = $this->request->query['credit_id'];
		}else{
			$msg = array("msg" => "NO credit note id found");
			echo json_encode($msg);die;
		}
		
		if(array_key_exists("payment_method",$this->request->query) && !empty($this->request->query['payment_method'])){
			$payment_method = $this->request->query['payment_method'];
		}else{
			$msg = array("msg" => "NO Payment Method Selected");
			echo json_encode($msg);die;
		}
		
		if(array_key_exists("kiosk_id",$this->request->query) && !empty($this->request->query['kiosk_id'])){
			$kiosk_id = $this->request->query['kiosk_id'];
		}else{
			$msg = array("msg" => "NO kiosk_id Found");
			echo json_encode($msg);die;
		}
		
		if($kiosk_id == 10000){
			$kiosk_id = 0;
		}
		
		if(array_key_exists("date",$this->request->query) && !empty($this->request->query['date'])){
			$date = $this->request->query['date'];
		}else{
			$date = "";
		}
		
		if(array_key_exists("memo_content",$this->request->query) && !empty($this->request->query['memo_content'])){
			$memo_content = trim($this->request->query['memo_content']);
		}else{
			$memo_content = "";
		}
		
		$CreditReceiptSource = "t_credit_receipts";
		$CreditPaymentDetailSource = "t_credit_payment_details";
		
		
		$CreditReceiptTable = TableRegistry::get($CreditReceiptSource,[
															   'table' => $CreditReceiptSource,
														    ]);
		$CreditPaymentDetailTable = TableRegistry::get($CreditPaymentDetailSource,[
                                                                                    'table' => $CreditPaymentDetailSource,
                                                                                ]);
		
		$credit_res = $CreditPaymentDetailTable->find("all",[
											   'conditions' => ["id" => $credit_id,
																'kiosk_id' => $kiosk_id,
																]
											   ])->toArray();
		$total_amt = 0;
		if(!empty($credit_res)){
			$total_amt = $credit_res[0]->amount;
			$credit_recipt_id = $credit_res[0]->credit_receipt_id;
			$credit_created = $credit_res[0]->created;
		}
		
		$credit_recipt_data = $CreditReceiptTable->find("all",['conditions' => [
														  'id' => $credit_recipt_id,
														  'kiosk_id' => $kiosk_id,
														  ]])->toArray();
		$credit_recipt_created = "";
		if(!empty($credit_recipt_data)){
			$credit_recipt_created = $credit_recipt_data[0]['created'];
		}
		
		$ids_arr = explode(",",$ids);
		$timestamp = strtotime(date("d-m-y"));
		
		$kioskProdctSaleTable_source = "t_kiosk_product_sales";
		$product_recit_table_source = "t_product_receipts";
		$paymentTable_source = "t_payment_details";
		
		
		
		$receiptTable = TableRegistry::get($product_recit_table_source,[
																		'table' => $product_recit_table_source,
																	]);
			
		$kioskProdctSalesTable = TableRegistry::get($kioskProdctSaleTable_source,[
																		'table' => $kioskProdctSaleTable_source,
																	]);
			
		$paymentTable = TableRegistry::get($paymentTable_source,[
																		'table' => $paymentTable_source,
																	]);
		
		$pay_res = $paymentTable->find("all",[
								   'conditions' => [
									'id IN' => $ids_arr,
									'kiosk_id' => $kiosk_id,
								   ],
								   'order' => [
                                        'amount' => 'asc'
                                    ],
								   ])->toArray();
		
		if(!empty($pay_res)){
			$outsatanding_amt = $org_amt = $counter = $remaining_amt = 0;
			$recipt_id_str = "";
			foreach($pay_res as $key => $value){
				$recipt_id = $value->product_receipt_id;
				if(empty($recipt_id_str)){
					$recipt_id_str .= $recipt_id;
				}else{
					$recipt_id_str .= ",".$recipt_id;
				}	
				
				if(!empty($date)){
					
					$recipt_res = $receiptTable->find("all",[
											   'conditions' => ['id' => $recipt_id,
																'kiosk_id' => $kiosk_id,
																],
											   ])->toArray();
					if(!empty($recipt_res)){
						$recipt_created = $recipt_res[0]['created'];
						if(strtotime($recipt_created) > strtotime($date)){
							$date = date("Y-m-d G:i:s",strtotime($recipt_created));
						}else{
							$date = date("Y-m-d G:i:s",strtotime($date));
						}
					}
				}
				$amount = $value->amount;
				if($counter == 0){
					$org_amt = $total_amt;
					$remaining_amt = $total_amt-$amount;
				}else{
					$org_amt = $remaining_amt;
							$org_amt = (string)$org_amt;
							$org_amt = (float)$org_amt;
							
							$amount = (string)$amount;
							$amount = (float)$amount;
					$remaining_amt = $remaining_amt-$amount;
				}
				
				$user_id = $this->request->Session()->read('Auth.User.id');
				
				if($remaining_amt>=0){
					$payment_id = $value->id;
					if(!empty($date)){
						$data_to_save = array(
										  "created" => date("Y-m-d G:i:s",strtotime($credit_recipt_created)),
										  "cr_ref_id" => $timestamp,
										  "payment_method" => "Cash");
					}else{
						$data_to_save = array(
										  "created" => date("Y-m-d G:i:s",strtotime($credit_recipt_created)),
										  "cr_ref_id" => $timestamp,
										  "payment_method" => "Cash");
					}
					
					$entity = $paymentTable->get($payment_id);
					$entity = $paymentTable->patchEntity($entity,$data_to_save);
					$paymentTable->save($entity);
					
					$logData = array(
								 'user_id'=>$user_id,
								 'pmt_id'=>(int)$payment_id,
								 'kiosk_id' => $kiosk_id,
								 'old_pmt_method'=>"On Credit",
								 'pmt_method'=>"Cash",
								 'receipt_id'=>$value->product_receipt_id,
								 'adjusted_by' => 2,
								 'memo'=>$memo_content,
								 'receipt_type' => 3,
								 'credit_recipt_id' => $credit_recipt_id
								);
			
					$newLog = $this->PmtLogs->newEntity($logData);
					$patchLog = $this->PmtLogs->patchEntity($newLog,$logData);
					$this->PmtLogs->save($patchLog);
					
					$logid = $patchLog->id;
					$log_created = $patchLog->created;
					$firstName = $users[$user_id];
					if(empty($firstName)){
						$firstName = "Missing Name";
					}
					$str = date("d/m/Y h:i",strtotime($log_created)).", ".$firstName.", "."On Credit"."->"."Cash";
					if(!empty($memo_content)){
						$str.="||".$memo_content;
					}
					if(!empty($credit_recipt_id)){
						$str.= ",Invoice adjusted against credit note :".$credit_recipt_id;
					}
					
					$data = array("description" => $str);
					$pay_res1 = $paymentTable->get($value->id);
					$pay_res1 = $paymentTable->patchEntity($pay_res1,$data);
					$paymentTable->save($pay_res1);
					
				}else{
					$outsatanding_amt = $amount - $org_amt;
					$payment_id = $value->id;
					if(!empty($date)){
						$data_to_save = array("payment_method" => "Cash",
										  "amount" => $org_amt,
										  "cr_ref_id" => $timestamp,
										  "created" => date("Y-m-d G:i:s",strtotime($credit_recipt_created)),
										  );
					}else{
						$data_to_save = array("payment_method" => "Cash",
										  "amount" => $org_amt,
										  "cr_ref_id" => $timestamp,
										  "created" => date("Y-m-d G:i:s",strtotime($credit_recipt_created)),
										  );
					}
					
					$entity = $paymentTable->get($payment_id);
					$entity = $paymentTable->patchEntity($entity,$data_to_save);
					$paymentTable->save($entity);
					
					
					
					$logData = array(
								 'user_id'=>$user_id,
								 'pmt_id'=>(int)$payment_id,
								 'kiosk_id' => $kiosk_id,
								 'old_pmt_method'=>"On Credit",
								 'pmt_method'=>"Cash",
								 'receipt_id'=>$value->product_receipt_id,
								 'adjusted_by' => 2,
								 'memo'=>$memo_content,
								 'receipt_type' => 3,
								 'credit_recipt_id' => $credit_recipt_id
								);
			
					$newLog = $this->PmtLogs->newEntity($logData);
					$patchLog = $this->PmtLogs->patchEntity($newLog,$logData);
					$this->PmtLogs->save($patchLog);
					
					$logid = $patchLog->id;
					$log_created = $patchLog->created;
					$firstName = $users[$user_id];
					if(empty($firstName)){
						$firstName = "Missing Name";
					}
					$str = date("d/m/Y h:i",strtotime($log_created)).", ".$firstName.", "."On Credit"."->"."Cash";
					if(!empty($memo_content)){
						$str.="||".$memo_content;
					}
					if(!empty($credit_recipt_id)){
						$str.= ",Invoice adjusted against credit note :".$credit_recipt_id;
					}
					$data = array("description" => $str);
					$pay_res1 = $paymentTable->get($value->id);
					$pay_res1 = $paymentTable->patchEntity($pay_res1,$data);
					$paymentTable->save($pay_res1);
					
					if(!empty($date)){
						$data_to_save1 = array(
										   "payment_method" => "On Credit",
											"amount" => $outsatanding_amt,
											"product_receipt_id" => $value->product_receipt_id,
											"created" => $value->created,
											'kiosk_id' => $kiosk_id,
										   );
					}else{
						$data_to_save1 = array(
										   "payment_method" => "On Credit",
											"amount" => $outsatanding_amt,
											"product_receipt_id" => $value->product_receipt_id,
											"created" => $value->created,
											'kiosk_id' => $kiosk_id,
										   );
					}
					
					$entity1 = $paymentTable->newEntity();
					$entity1 = $paymentTable->patchEntity($entity1,$data_to_save1);
					$paymentTable->save($entity1);
				}
				$counter++;
			}
			$total = 0;
			foreach($pay_res as $key => $value){
				$total += $value->amount;
			}
			
			if($total < $total_amt){ // $total_amt = credit recipt amount, $total = invoice sum up amount
				
				$credit_outstanding_amt = $total_amt - $total;
				$credit_data_to_save = array("payment_method" => "Cash",
											 "amount" => $total,
											 "credit_cleared" => 1,
											 //"description" => $str,
											 //"created" => date("Y-m-d G:i:s"),
											 );
				$credit_entity = $CreditPaymentDetailTable->get($credit_id);
				$credit_entity = $CreditPaymentDetailTable->patchEntity($credit_entity,$credit_data_to_save);
				$CreditPaymentDetailTable->save($credit_entity);
				
				$logData = array(
								 'user_id'=>$user_id,
								 'pmt_id'=>(int)$credit_id,
								 'kiosk_id' => $kiosk_id,
								 'old_pmt_method'=>"On Credit",
								 'pmt_method'=>"Cash",
								 'receipt_id'=>$credit_recipt_id,
								 'adjusted_by' => 2,
								 'memo'=>$memo_content,
								 'receipt_type' => 4,
								);
			
					$newLog = $this->PmtLogs->newEntity($logData);
					$patchLog = $this->PmtLogs->patchEntity($newLog,$logData);
					$this->PmtLogs->save($patchLog);
				
					$logid = $patchLog->id;
					$log_created = $patchLog->created;
					$firstName = $users[$user_id];
					if(empty($firstName)){
						$firstName = "Missing Name";
					}
					$str = date("d/m/Y h:i",strtotime($log_created)).", ".$firstName.", "."On Credit"."->"."Cash";
					if(!empty($memo_content)){
						$str.="||".$memo_content;
					}
					if(!empty($recipt_id_str)){
						$str.=",credit note adj against inv no. ".$recipt_id_str;
					}
				$credit_data_for_desc = array("description"=>$str);
				$credit_entity_for_desc = $CreditPaymentDetailTable->get($credit_id);
				$credit_entity_for_desc = $CreditPaymentDetailTable->patchEntity($credit_entity_for_desc,$credit_data_for_desc);
				$CreditPaymentDetailTable->save($credit_entity_for_desc);	
				
				$credit_data_to_save = array("payment_method" => "On Credit",
											 "amount" => $credit_outstanding_amt,
											 "credit_receipt_id" => $credit_recipt_id,
											 "payment_status" => 0,
											 "created" => date("Y-m-d G:i:s",strtotime($credit_created)),
											 'kiosk_id' => $kiosk_id,
											 );
				$credit_add_entity = $CreditPaymentDetailTable->newEntity();
				$credit_add_entity = $CreditPaymentDetailTable->patchEntity($credit_add_entity,$credit_data_to_save);
				$CreditPaymentDetailTable->save($credit_add_entity);
				
				
						
			}else{
				$credit_data_to_save = array("payment_method" => "Cash",
											 //"created" => date("Y-m-d G:i:s"),
											 "credit_cleared" => 1,
											 //"description" => $str,
											 );
				$credit_entity = $CreditPaymentDetailTable->get($credit_id);
				$credit_entity = $CreditPaymentDetailTable->patchEntity($credit_entity,$credit_data_to_save);
				$CreditPaymentDetailTable->save($credit_entity);
				
				
				$logData = array(
								 'user_id'=>$user_id,
								 'pmt_id'=>(int)$credit_id,
								 'kiosk_id' => $kiosk_id,
								 'old_pmt_method'=>"On Credit",
								 'pmt_method'=>"Cash",
								 'receipt_id'=>$credit_recipt_id,
								 'adjusted_by' => 2,
								 'memo'=>$memo_content,
								 'receipt_type' => 4,
								);
			
					$newLog = $this->PmtLogs->newEntity($logData);
					$patchLog = $this->PmtLogs->patchEntity($newLog,$logData);
					$this->PmtLogs->save($patchLog);
					
					$logid = $patchLog->id;
					$log_created = $patchLog->created;
					$firstName = $users[$user_id];
					if(empty($firstName)){
						$firstName = "Missing Name";
					}
					$str = date("d/m/Y h:i",strtotime($log_created)).", ".$firstName.", "."On Credit"."->"."Cash";
					if(!empty($memo_content)){
						$str.="||".$memo_content;
					}
					if(!empty($recipt_id_str)){
						$str.=",credit note adj against inv no. ".$recipt_id_str;
					}
					$credit_data_for_desc = array("description"=>$str);
					$credit_entity_for_desc = $CreditPaymentDetailTable->get($credit_id);
					$credit_entity_for_desc = $CreditPaymentDetailTable->patchEntity($credit_entity_for_desc,$credit_data_for_desc);
					$CreditPaymentDetailTable->save($credit_entity_for_desc);
			}
			
			
			
				
			if($counter > 0){
				$msg = array("msg" => "Done");
				echo json_encode($msg);die;
			}
		}
	}
	
	public function drPaymentClearnessSearch($keyword = ''){
		
		$this->check_dr5();
        $ProductReceipt_source = 't_product_receipts';
		$ProductReceiptTable = TableRegistry::get($ProductReceipt_source,[
																			'table' => $ProductReceipt_source,
																		]);
		$KioskProductSale_source = 't_kiosk_product_sales';
		$KioskProductSaleTable = TableRegistry::get($KioskProductSale_source,[
																				'table' => $KioskProductSale_source,
																			]);
		$PaymentDetail_source = 't_payment_details';
		$PaymentDetailTable = TableRegistry::get($PaymentDetail_source,[
																				'table' => $PaymentDetail_source,
																			]);
		//$this->ProductReceipt->setSource('t_product_receipts');
		//$this->KioskProductSale->setSource('t_kiosk_product_sales');
		//$this->PaymentDetail->setSource('t_payment_details');
		//pr($this->request->query);
		$conditionArr = array();
		$productreceiptArr = array();
		$settingArr = $this->setting;
        //pr($this->request);die;
		if(array_key_exists('payment_type',$this->request->query) &&
		   !empty($this->request->query['payment_type'])){
            //echo "payment typle";
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
		if(array_key_exists('invoice_detail',$this->request->query) &&
		   !empty($this->request->query['invoice_detail'])){
            
			 
           $invoiceSearchKeyword = $this->request->query['invoice_detail']; 
			$this->set('invoiceSearchKeyword',$this->request->query['invoice_detail']);
		}
		
		if(array_key_exists('start_date',$this->request->query) &&
		   !empty($this->request->query['start_date'])){
			$this->set('start_date',$this->request->query['start_date']);
		}
		//pr($this->request);die;
		if(array_key_exists('date_type',$this->request->query)){
			$date_type = $this->request->query['date_type'];
		}
		if(array_key_exists('date-type',$this->request->query)){
			$date_type = $this->request->query['date-type'];
		}
		$this->set(compact('date_type'));
		if(array_key_exists('end_date',$this->request->query) &&
		   !empty($this->request->query['end_date'])){
			$this->set('end_date',$this->request->query['end_date']);
		}
		
		if(array_key_exists('start_date',$this->request->query) &&
		   array_key_exists('end_date',$this->request->query) &&
		   !empty($this->request->query['start_date']) &&
		   !empty($this->request->query['end_date'])){
			if(array_key_exists('date_type',$this->request->query)){
				$date_type = $this->request->query['date_type'];
				if($date_type == 'payment'){
					$conditionArr[] = array(
						"created >=" => date('Y-m-d', strtotime($this->request->query['start_date'])),
						"created <" => date('Y-m-d', strtotime($this->request->query['end_date']. ' +1 Days')),			
					       );
				}else{
					$conditionArr1 = array();
					$conditionArr1[] = array(
						"created >" => date('Y-m-d', strtotime($this->request->query['start_date'])),
						"created <" => date('Y-m-d', strtotime($this->request->query['end_date']. ' +1 Days')),			
					       );
					$Receipts_query = $ProductReceiptTable->find('list',array(
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
					$conditionArr['product_receipt_id IN'] = $Receipts;
				}
			}else{ 
				$conditionArr[] = array(
						"created >" => date('Y-m-d', strtotime($this->request->query['start_date'])),
						"created <" => date('Y-m-d', strtotime($this->request->query['end_date']. ' +1 Days')),			
					       );
			}
			
		}
		
		$agent_id = 0;
		if(array_key_exists('agent_id',$this->request->query) && !empty($this->request->query['agent_id'])){
			$agent_id = $this->request->query['agent_id'];
			$conditionArr['agent_id'] = $agent_id;
		}
		$this->set(compact('agent_id'));
		//pr($conditionArr);die;
		$customerResult = array();
		if(array_key_exists('search_kw',$this->request->query) &&
		   !empty($this->request->query['search_kw'])){
			 $textKeyword = $this->request->query['search_kw'];
			if(!empty($textKeyword) && array_key_exists('invoice_detail',$this->request->query)){
				if($invoiceSearchKeyword=="receipt_number"){
                   
					$conditionArr['product_receipt_id'] =  (int)$textKeyword;
				}elseif($invoiceSearchKeyword=="business"){
                   $customerIds_query = $this->Customers->find('list',
                                                         ['conditions'=>[
																"OR" => [
															"LOWER(`Customers`.`business`) like" => strtolower("%$textKeyword%"),
															"LOWER(`Customers`.`fname`) like" => strtolower("%$textKeyword%"),
											"LOWER(`Customers`.`business`) like" => strtolower("%$textKeyword%")												    ]
												    ],
                                                                'keyField' => 'id',
                                                                'valueField' => 'id',
                                                         ]);
                    //pr($customerIds_query);die;
					$customerIds_query = $customerIds_query->hydrate(false);
                    if(!empty($customerIds_query)){
                        $customerIds = $customerIds_query->toArray();
                    }else{
						$customerIds  = array();
					}
					//pr($customerIds);die;
                    
                    
                    $conditionArr['product_receipt_id IN'] = 0;
					if(count($customerIds) > 0){
                       
						$searchCriteria['customer_id IN'] = $customerIds;
						if(array_key_exists('start_date',$this->request->query) &&
							array_key_exists('end_date',$this->request->query) &&
							!empty($this->request->query['start_date']) &&
							!empty($this->request->query['end_date'])){
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
						$cutomerReceipts_query = $ProductReceiptTable->find('all',array('fields' => array('id'),
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
						$conditionArr['product_receipt_id IN'] = 0;
						if( count($cutomerReceipts) ){
							//echo $cutomerReceipts['ProductReceipt']['id'];
							foreach($cutomerReceipts as $cutomerReceipt){
								$receiptIDs[] = $cutomerReceipt['id'];
							}
                            if(empty($receiptIDs)){
                                $receiptIDs = array('0'=>null);
                            }
							$conditionArr['product_receipt_id IN'] = $receiptIDs;
						}
                        
					}
                   
				}elseif($invoiceSearchKeyword=="customer_id"){//invoice_detail
                   	$customerID =  (int)$textKeyword;
					$searchCriteria['customer_id'] = $customerID;
					if(array_key_exists('start_date',$this->request->query) &&
						array_key_exists('end_date',$this->request->query) &&
						!empty($this->request->query['start_date']) &&
						!empty($this->request->query['end_date'])){
						$date_type = $this->request->query['date_type'];
						if($date_type == 'payment'){
							
						}else{
							$searchCriteria[] = array(
									 "created >" => date('Y-m-d', strtotime($this->request->query['start_date'])),
									 "created <" => date('Y-m-d', strtotime($this->request->query['end_date']. ' +1 Days')),			
										);	
						}
					 }
					$cutomerReceipts_query = $ProductReceiptTable->find('all',array('fields' => array('id'),
															'conditions' => $searchCriteria,
															'recursive' => -1));
                    $cutomerReceipts_query = $cutomerReceipts_query->hydrate(false);
                    if(!empty($cutomerReceipts_query)){
                        $cutomerReceipts = $cutomerReceipts_query->toArray();
                    }else{
                        $cutomerReceipts = array();
                    }
					$receiptIDs = array();
					$conditionArr['product_receipt_id IN'] = 0;
                  //  pr($cutomerReceipts);die;
					if( count($cutomerReceipts) ){
						//echo $cutomerReceipts['ProductReceipt']['id'];
						foreach($cutomerReceipts as $cutomerReceipt){
							$receiptIDs[] = $cutomerReceipt['id'];
						}
                        if(empty($receiptIDs)){
                            $receiptIDs = array('0'=>null);
                        }
						$conditionArr['product_receipt_id IN'] = $receiptIDs;
					}
					
				}
				$this->set('textKeyword',$this->request->query['search_kw']);
			}
		}
		//pr($conditionArr);die;
		
		if(array_key_exists('kiosk_id',$this->request->query)){
           $kskId = $this->request->query['kiosk_id'];
			 
		}elseif(array_key_exists('kiosk-id',$this->request->query)){
			$kskId = $this->request->query['kiosk-id'];
		}else{
			$kskId =  $this->request->Session()->read('kiosk_id');
		}
		if(empty($kskId) || $kskId == 10000){
			$kskId = 0;
		}
		$this->set('kiosk',$kskId);
        $conditionArr['kiosk_id'] = $kskId;
	    //echo $kskId;die;
        //  pr($conditionArr);die;
		$listPaymentDet_query = $PaymentDetailTable->find('all',array(
                                                                'fields'=>array('product_receipt_id','amount'),
                                                                'conditions'=>$conditionArr
                                                                )
                                                    );
        
        $listPaymentDet_query = $listPaymentDet_query->hydrate(false);
		if(!empty($listPaymentDet_query)){
			$listPaymentDet = $listPaymentDet_query->toArray();
		}else{
			$listPaymentDet = array();
		}
       
		$listPayment = array();
		foreach($listPaymentDet as $lp => $list_payment){
			if(array_key_exists($list_payment['product_receipt_id'],$listPayment)){
				$listPayment[$list_payment['product_receipt_id']]+= $list_payment['amount'];
			}else{
				$listPayment[$list_payment['product_receipt_id']] = $list_payment['amount'];
			}
		}
        
		$lptotalPaymentAmount = 0;
		$lpgrandNetAmount = 0;
		$totalBillCost = 0;
		$totalCost = 0;
		if(count($listPayment)){
			$receiptIdArr = array();
			$productReceiptDetail = array();
			if($listPayment){
				$receiptIdArr = array_keys($listPayment);
			}
          //  pr($receiptIdArr);die;
            if(empty($receiptIdArr)){
                $receiptIdArr = array('0'=>null);
            }
					//code for getting cost price of products*******************
					$recit_ids = array();
                 //   pr($receiptIdArr);die;
                 
					if(count($receiptIdArr)){
						//pr($receiptIdArr);
                         $productQttArr_query = $KioskProductSaleTable->find('all', array(
                                                                                   'conditions' => array(
                                                                                                         'product_receipt_id IN' => $receiptIdArr,
                                                                                                         'kiosk_id'=>$kskId
                                                                                                         ),
                                                                                   'fields' => array('product_id','quantity','product_receipt_id'),
                                                                                   'recursive' => -1));
                        
                         $productQttArr_query = $productQttArr_query->hydrate(false);
                         
                        if(!empty($productQttArr_query)){
                            $productQttArr = $productQttArr_query->toArray();
                        }else{
                            $productQttArr = array();
                        }
						$receiptIdDetail = array();
						$productIdsArr = array();
						foreach($productQttArr as $key => $productQtt){
							$recit_ids[$productQtt['product_receipt_id']] = $productQtt['product_receipt_id'];
							//$receiptIdDetail[$productQtt['KioskProductSale']['product_receipt_id']][] = $productQtt['KioskProductSale'];
							$productIdsArr[$productQtt['product_id']] = $productQtt['product_id'];
						}
                        if(empty($productIdsArr)){
                            $productIdsArr = array('0'=>null);
                        }
                         if(empty($recit_ids)){
                            $recit_ids = array('0'=>null);
                        }

                        $costPriceList_query = $this->Products->find('list',[
                                                    'keyField' => 'id',
                                                     'valueField' => 'cost_price',
                                                    'conditions' => ['Products.id IN' => $productIdsArr]
                                                 ]
                                        );
                        
						if(!empty($costPriceList_query)){
                            $costPriceList = $costPriceList_query->toArray();
                       }
                     // pr($productQttArr);
						foreach($productQttArr as $key => $productQtt){
							if(!array_key_exists($productQtt['product_id'],$costPriceList)){continue;}
							$costPrice = $costPriceList[$productQtt['product_id']]*$productQtt['quantity'];
							$totalCost+=$costPrice;
						}
					}
					//*********************till here
			if(count($listPayment) && count($conditionArr)){
               	$productReceiptDetail_query = $ProductReceiptTable->find('all',array('conditions'=>array('id IN'=>$receiptIdArr,'kiosk_id' => $kskId),
                                                                               'fields'=>array('id','vat','status','bill_cost','created'),
                                                                               'recursive'=>-1
                                                                               )
                                                                   );
                $productReceiptDetail_query = $productReceiptDetail_query->hydrate(false);
                if(!empty($productReceiptDetail_query)){
                    $productReceiptDetail = $productReceiptDetail_query->toArray();
                }else{
                    $productReceiptDetail = array();
                }
			}else{
				$productReceiptDetail_query = $ProductReceiptTable->find('all',array(
                                                                               'fields'=>array('id','vat','status','bill_cost','created'),
                                                                               'recursive'=>-1
                                                                               )
                                                                   );
                $productReceiptDetail_query = $productReceiptDetail_query->hydrate(false);
                if(!empty($productReceiptDetail_query)){
                    $productReceiptDetail = $productReceiptDetail_query->toArray();
                }else{
                    $productReceiptDetail = array();
                }
                
			}
           // pr($productReceiptDetail);die;
			$createdArr = array();
			foreach($productReceiptDetail as $key=>$productReceiptDta){
				//showing the data with product receipt status == 0 ie. the payment went through and data saved properly
				if($productReceiptDta['status']==0){
					$paymentAmount = 0;
					$createdArr[$productReceiptDta['id']] = $productReceiptDta['created'];
					$totalBillCost+=floatval($productReceiptDta['bill_cost']);
					if(array_key_exists($productReceiptDta['id'],$listPayment)){
						$paymentAmount = $listPayment[$productReceiptDta['id']];
					}
					$lptotalPaymentAmount+=floatval($paymentAmount);
					$vatPercentage = $productReceiptDta['vat']/100;
					$netAmount = $paymentAmount/(1+$vatPercentage);
					$lpgrandNetAmount+=floatval($netAmount);
				}
			}
		}
		
		/*$lptotalPaymentAmount = 0;
		$lpgrandNetAmount = 0;
		foreach($productReceiptDetail as $key=>$productReceiptDta){
			//showing the data with product receipt status == 0 ie. the payment went through and data saved properly
			if($productReceiptDta['ProductReceipt']['status']==0){
				$totalBillCost+=floatval($productReceiptDta['ProductReceipt']['bill_cost']);
                $receiptIdArr[$productReceiptDta['ProductReceipt']['id']] = $productReceiptDta['ProductReceipt']['id'];//capturing the receipt ids
				$paymentAmount = $productReceiptDta['ProductReceipt']['bill_amount'];
				$lptotalPaymentAmount+=floatval($paymentAmount);
				$vatPercentage = $productReceiptDta['ProductReceipt']['vat']/100;
				$netAmount = $paymentAmount/(1+$vatPercentage);
				$lpgrandNetAmount+=$netAmount;
			}
		}*/
		
		$lptotalVat = $lptotalPaymentAmount - $lpgrandNetAmount;
		$lptotalVat = $lptotalVat;
		$conditionArr['kiosk_id'] = $kskId;
        //echo $lptotalPaymentAmount;die;
		$this->set(compact('lptotalPaymentAmount','lpgrandNetAmount','lptotalVat','totalCost'));
		//pr($conditionArr);die;
        
        $this->paginate = [
							'conditions' => $conditionArr,
							'order' => ['product_receipt_id DESC'],
							'limit' => 50,
						  ];
        
		$productReceipts_query = $this->paginate($PaymentDetailTable);
		if(!empty($productReceipts_query)){
			$productReceipts = $productReceipts_query->toArray();
		}else{
			$productReceipts = array();
		}
		$conditionArr_to_use = $conditionArr;
		
		if(array_key_exists('product_receipt_id IN',$conditionArr_to_use)){
			$s_res = $conditionArr_to_use['product_receipt_id IN'];
			//$conditionArr_to_use['id IN'] = $s_res;
			unset($conditionArr_to_use['product_receipt_id IN']);
			
		}
		if(array_key_exists('product_receipt_id',$conditionArr_to_use)){
			$s_res = $conditionArr_to_use['product_receipt_id'];
			//$conditionArr_to_use['id IN'] = $s_res;
			unset($conditionArr_to_use['product_receipt_id']);
		}
		
		if(array_key_exists('payment_method like ',$conditionArr_to_use)){
			unset($conditionArr_to_use['payment_method like ']);
		}
		
		$pay_detail = $PaymentDetailTable->find('all',array('conditions' => $conditionArr))->toArray();
		if(!empty($pay_detail)){
			foreach($pay_detail as $key => $val){
				$conditionArr_to_use['id IN'][] = $val->product_receipt_id;
			}
			//pr($conditionArr_to_use);die;
			$fixed_cost_sum_query = $ProductReceiptTable->find('all',array('conditions' => $conditionArr_to_use));
			$fixed_cost_sum_query
						->select(['fixed_cost' => $fixed_cost_sum_query->func()->sum('bill_cost')]);
			
			$fixed_cost_sum_query = $fixed_cost_sum_query->hydrate(false);
			if(!empty($fixed_cost_sum_query)){
				$fixed_cost_sum = $fixed_cost_sum_query->first(false);
			}else{
				$fixed_cost_sum = array();
			}	
		}else{
			$fixed_cost_sum['fixed_cost'] = 0;
		}
		
		 $product_receiptId = array();
		if(!empty($productReceipts)){
			foreach($productReceipts as $productReceipts_value){
			   $product_receiptId[] = $productReceipts_value['product_receipt_id'];
			}
		}
		
        if(empty($product_receiptId)){
            $product_receiptId = array('0' =>null);
        }
       
            $product_receipt_data_query = $ProductReceiptTable->find('all',[
																			'conditions' => ['id IN' => $product_receiptId]
																		]
												   );
		   //pr($product_receipt_data_query);die;
			   $product_receipt_data_query = $product_receipt_data_query->hydrate(false);
			   if(!empty($product_receipt_data_query)){
				   $product_receipt_data = $product_receipt_data_query->toArray();
			   }else{
				   $product_receipt_data = array();
			   }
		 //pr($product_receipt_data);die;
		 
            foreach($product_receipt_data as $receiptDetail){
                //pr($receiptDetail);die;
                $customerIdArr[] = $receiptDetail['customer_id'];
                $productreceiptArr[$receiptDetail['id']] = $receiptDetail;
                
            }
            $this->set(compact('productreceiptArr'));
            if(empty($customerIdArr)){
                $customerIdArr = array(0 => null);
            }
             $customerBusiness_query = $this->Customers->find('list',[
                                                    'keyField' => 'id',
                                                     'valueField' => 'business',
                                                   'conditions' =>['Customers.id IN'=>array_unique($customerIdArr)],
                                                 ]
                                        ); 
 
            if(!empty($customerBusiness_query)){
                $customerBusiness = $customerBusiness_query->toArray();
            }else{
                $customerBusiness = array();
            }
       
		if($this->request->session()->read('Auth.User.group_id') == ADMINISTRATORS || $this->request->session()->read('Auth.User.group_id') == FRANCHISE_OWNER){
			//$session_id = $this->Session->id();
			$user_id = $this->request->Session()->read('Auth.User.id');
			if($kskId == ""){
				$kskId = 10000;
			}
			$data_arr = array('kiosk_id' => $kskId);
			$jsondata = json_encode($data_arr);
			$this->loadModel('UserSettings');
			$res_query = $this->UserSettings->find('all',array('conditions' => array(
																		 'user_id' => $user_id,
																		 'setting_name' => "dr_search",
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
									'setting_name' => "dr_search",
									'data' => $jsondata
									);
				$entity = $this->UserSettings->get($userSettingid);
				$entity = $this->UserSettings->patchEntity($entity,$data_to_save);
				$this->UserSettings->save($entity);
			}else{
				$data_to_save = array(
									'user_id' => $user_id,
									//'user_session_key' => $session_id,
									'setting_name' => "dr_search",
									'data' => $jsondata
									);
				$entity = $this->UserSettings->newEntity();
				$entity = $this->UserSettings->patchEntity($entity,$data_to_save);
				$this->UserSettings->save($entity);
			}
		}
		$kiosk_list_query = $this->Kiosks->find('list');
		$kiosk_list_query = $kiosk_list_query->hydrate(false);
		if(!empty($kiosk_list_query)){
			$kiosk_list = $kiosk_list_query->toArray();
		}else{
			$kiosk_list = array();
		}
		
		$agents_query = $this->Agents->find('list');
		$agents_query = $agents_query->hydrate(false);
		
		if(!empty($agents_query)){
			$agents = $agents_query->toArray();
		}
		$agents[0] = "Select Acc manager";
		ksort($agents);
		
		$log_refined_data = array();
		if($kskId == 10000){
			$kskId = 0;
		}
		
		$log_table_data = $this->PmtLogs->find("all",[
													  'conditions' => ['kiosk_id' => $kskId,
																	   'receipt_type' => 3,
																	   ],
													  'order' => ['created DESC'],
													  ])->toArray();
		if(!empty($log_table_data)){
			foreach($log_table_data as $log_key => $log_value){
					$log_refined_data[$log_value->pmt_id][] = $log_value;
			}
		}
		//pr($log_refined_data);die;
		$users = $this->Users->find("list",['keyField' => 'id',
								   'valueField' => 'f_name',
								   ])->toArray();
		
		
		$this->set(compact('productReceipts','customerBusiness','totalAmount','totalBillCost','createdArr','recit_ids','kiosk_list','fixed_cost_sum','agents','log_refined_data','users'));
		//$this->layout = 'default';
		$this->render('quotation_payment_clearness');
	}
	
	
}

?>