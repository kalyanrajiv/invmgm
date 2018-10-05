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
class TestsController extends AppController
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
		$receiptIdArr = array();
		$productReceiptDetail = array();
		 
		$productReceiptDetail_query = $receiptTable->find('all',array('fields'=>array('id','vat','status','bill_amount', 'bill_cost','created'),'recursive'=>-1));
        if(!empty($productReceiptDetail_query)){
            $productReceiptDetail = $productReceiptDetail_query->hydrate(false);
            $productReceiptDetail = $productReceiptDetail->toArray();   
        }
        //pr($productReceiptDetail);die;
		$createdArr = array();
		foreach($productReceiptDetail as $key=>$productReceiptDta){
			//showing the data with product receipt status == 0 ie. the payment went through and data saved properly
			if($productReceiptDta['status']==0){
				$createdArr[$productReceiptDta['id']] = $productReceiptDta['created'];
				$receiptIdArr[$productReceiptDta['id']] = $productReceiptDta['id'];//capturing the receipt ids
				$paymentAmount = $productReceiptDta['bill_amount'];
				$receiptIdArrpayment[$productReceiptDta['id']] = $paymentAmount;
			}
		}
		//pr($receiptIdArrpayment);die;
		if(empty($receiptIdArrpayment)){
			$receiptIdArrpayment[$productReceiptDta['id']] = array('0'=>null);
		}
        
		$PaymentDetails_query = $this->PaymentDetails->find('all',array(
																	 'fields'=>array('id','product_receipt_id','amount'),
                                                                     'conditions' => array('product_receipt_id IN' => $receiptIdArr),
                                                                        'recursive'=>-1
                                                                        ));
		 $PaymentDetails_query = $PaymentDetails_query->hydrate(false);
		 if(!empty($PaymentDetails_query)){
          $PaymentDetails = $PaymentDetails_query->toArray();
        }else{
			$PaymentDetails = array();
		}
		//pr($PaymentDetails);die;
		$pramount = array();
		$product_receipt_ids = array();
		foreach($PaymentDetails as  $PaymentDetail){
			$product_receipt_id = $PaymentDetail['product_receipt_id'];
			$pramount[$product_receipt_id][]  = $PaymentDetail['amount'];
		}
		$PaymentDetail_product_receipt_ids = array();
		foreach($pramount as $key =>$value){
			$totalpayment = array_sum($pramount[$key])  ;
			$totalpayment = number_format((float)$totalpayment, 2);
			$receiptIdArrpayment[$key] = number_format((float) $receiptIdArrpayment[$key], 2);
			if($totalpayment != $receiptIdArrpayment[$key]){
				  $PaymentDetail_product_receipt_ids[] = $key;
				 
			}
		 
		}
		 $this->paginate = [
								 'conditions' => ['product_receipts.id IN' => $PaymentDetail_product_receipt_ids],
                                  //  'order' => ['product_receipt_id' => 'desc'],
									'limit' => 50,
                                     'contain' => ['KioskProductSales','PaymentDetails','Customers'] 
                                ];
		
        
		$productReceipts = $this->paginate($receiptTable);
       // pr($productReceipts);die;
        if(!empty($productReceipts)){
          $productReceipts = $productReceipts->toArray();
        }else{
			$productReceipts = array();
		}
        foreach($productReceipts as $receiptDetail){
			$customerIdArr[] = $receiptDetail['customer_id'];
			//$product_receipt_id = $receiptDetail['PaymentDetail']['product_receipt_id'];
			//$pramount[$product_receipt_id][]  = $receiptDetail['PaymentDetail']['amount'];
		}
 
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
		 
		$kiosks_query = $this->Kiosks->find('list');
		$kiosks_query = $kiosks_query->hydrate(false);
		if(!empty($kiosks_query)){
			$kiosks = $kiosks_query->toArray();
		}else{
			$kiosks = array();
		}
		$kiosk_id = 10000;
		$this->set(compact('productReceipts', 'customerBusiness','createdArr','kiosks','kiosk_id'));
        $this->set(compact('productReceipts', 'customerBusiness','totalAmount','totalBillCost','createdArr','kiosks','kiosk_id','pramount','totalpayment'));
	}
    
     
    private function check_dr5(){
		$loggedInUser =  $this->request->session()->read('Auth.User.username');
		if (!preg_match('/'.QUOT_USER_PREFIX.'/',$loggedInUser)){
			$this->Flash->error(__('Sorry,This Page Is Not Existing.'));
				return $this->redirect(array('controller' => 'home','action' => 'dashboard'));
			die;
		}
	}
	
	public function drIndex(){
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
		$receiptIdArr = array();
		$productReceiptDetail = array();
		 
		$productReceiptDetail_query = $ProductReceiptTable->find('all',array('fields'=>array('id','vat','status','bill_amount', 'bill_cost','created'),'recursive'=>-1));
        if(!empty($productReceiptDetail_query)){
            $productReceiptDetail = $productReceiptDetail_query->hydrate(false);
            $productReceiptDetail = $productReceiptDetail->toArray();   
        }
        //pr($productReceiptDetail);die;
		$createdArr = array();
		foreach($productReceiptDetail as $key=>$productReceiptDta){
			//showing the data with product receipt status == 0 ie. the payment went through and data saved properly
			if($productReceiptDta['status']==0){
				$createdArr[$productReceiptDta['id']] = $productReceiptDta['created'];
				$receiptIdArr[$productReceiptDta['id']] = $productReceiptDta['id'];//capturing the receipt ids
				$paymentAmount = $productReceiptDta['bill_amount'];
				$receiptIdArrpayment[$productReceiptDta['id']] = $paymentAmount;
			}
		}
		//pr($receiptIdArrpayment);die;
		if(empty($receiptIdArrpayment)){
			$receiptIdArrpayment  = array('0'=>null);
		}
        if(empty($receiptIdArr)){
			$receiptIdArr  = array('0'=>null);
		}
		$PaymentDetails_query = $PaymentDetailTable->find('all',array(
																	 'fields'=>array('id','product_receipt_id','amount'),
                                                                     'conditions' => array('product_receipt_id IN' => $receiptIdArr),
                                                                        'recursive'=>-1
                                                                        ));
		 $PaymentDetails_query = $PaymentDetails_query->hydrate(false);
		 if(!empty($PaymentDetails_query)){
          $PaymentDetails = $PaymentDetails_query->toArray();
        }else{
			$PaymentDetails = array();
		}
		//pr($PaymentDetails);die;
		$pramount = array();
		$product_receipt_ids = array();
		foreach($PaymentDetails as  $PaymentDetail){
			$product_receipt_id = $PaymentDetail['product_receipt_id'];
			$pramount[$product_receipt_id][]  = $PaymentDetail['amount'];
		}
		$PaymentDetail_product_receipt_ids = array();
		foreach($pramount as $key =>$value){
			$totalpayment = array_sum($pramount[$key])  ;
			$totalpayment = number_format((float)$totalpayment, 2);
			$receiptIdArrpayment[$key] = number_format((float) $receiptIdArrpayment[$key], 2);
			if($totalpayment != $receiptIdArrpayment[$key]){
				  $PaymentDetail_product_receipt_ids[] = $key;
				 
			}
		 
		}
		 $this->paginate = [
								 'conditions' => ['id IN' => $PaymentDetail_product_receipt_ids 
                                                   ],
                                  //  'order' => ['product_receipt_id' => 'desc'],
									'limit' => 50,
                                    // 'contain' => ['KioskProductSales','PaymentDetails','Customers'] 
                                ];
		
        
		$productReceipts = $this->paginate($ProductReceiptTable);
       // pr($productReceipts);die;
        if(!empty($productReceipts)){
          $productReceipts = $productReceipts->toArray();
        }else{
			$productReceipts = array();
		}
        foreach($productReceipts as $receiptDetail){
			$customerIdArr[] = $receiptDetail['customer_id'];
			
		}
 
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
		 
		$kiosks_query = $this->Kiosks->find('list');
		$kiosks_query = $kiosks_query->hydrate(false);
		if(!empty($kiosks_query)){
			$kiosks = $kiosks_query->toArray();
		}else{
			$kiosks = array();
		}
		$kiosk_id = 10000;
		$this->set(compact('productReceipts', 'customerBusiness','createdArr','kiosks','kiosk_id'));
        $this->set(compact('productReceipts', 'customerBusiness','totalAmount','totalBillCost','createdArr','kiosks','kiosk_id','pramount','totalpayment'));
	}
	
	public function kioskPayment(){
        $this->loadModel('ProductReceipts');
        $this->loadModel('KioskProductSales');
        $this->loadModel('Products');
		if(array_key_exists('0',$this->request->params['pass'])){
			$kiosk_id = $this->request->params['pass'][0];
			if($kiosk_id == 10000){
				$KioskProductSale_source = "kiosk_product_sales";
				$ProductReceipt_source = "product_receipts";
				$PaymentDetail_source = "payment_details";
			}else{
				$ProductReceipt_source = "kiosk_{$kiosk_id}_product_receipts";
				$KioskProductSale_source = "kiosk_{$kiosk_id}_product_sales";
				$PaymentDetail_source = "kiosk_{$kiosk_id}_payment_details";
			}
            
            $ProductReceiptTable = TableRegistry::get($ProductReceipt_source,[
                                                                                'table' => $ProductReceipt_source,
                                                                            ]);
            
            $kioskProdctSalesTable = TableRegistry::get($KioskProductSale_source,[
                                                                                    'table' => $KioskProductSale_source,
                                                                                ]);
            
            $PaymentDetailTable = TableRegistry::get($PaymentDetail_source,[
																				'table' => $PaymentDetail_source,
																			]);
		}else{
				$kioskProdctSaleTable_source = "kiosk_product_sales";
				$ProductReceipt_source = "product_receipts";
				$paymentTable_source = "payment_details";
			
			$ProductReceiptTable = TableRegistry::get($ProductReceipt_source,[
																		'table' => $ProductReceipt_source,
																	]);
			
			$kioskProdctSalesTable = TableRegistry::get($kioskProdctSaleTable_source,[
																		'table' => $kioskProdctSaleTable_source,
																	]);
			
			$PaymentDetailTable = TableRegistry::get($paymentTable_source,[
																		'table' => $paymentTable_source,
																	]);
		}
        $productReceiptDetail_query = $ProductReceiptTable->find('all',array('fields'=>array('id','vat','status','bill_amount', 'bill_cost','created')));
        $productReceiptDetail_query = $productReceiptDetail_query->hydrate(false);
        if(!empty($productReceiptDetail_query)){
            $productReceiptDetail = $productReceiptDetail_query->toArray();   
        }else{
            $productReceiptDetail = array();
        }
     
		$createdArr = array();
        if(!empty($productReceiptDetail)){
            foreach($productReceiptDetail as $key=>$productReceiptDta){
                if($productReceiptDta['status']==0){
                    $receiptIdArr[$productReceiptDta['id']] = $productReceiptDta['id'];
                    $receiptIdArrpayment[$productReceiptDta['id']] = $productReceiptDta['bill_amount'];
                }
            }
			if(empty($receiptIdArrpayment)){
				$receiptIdArrpayment  = array('0'=>null);
			}
			if(empty($receiptIdArr)){
				$receiptIdArr  = array('0'=>null);
			}
			$PaymentDetails_query = $PaymentDetailTable->find('all',array(
																		 'fields'=>array('id','product_receipt_id','amount'),
																		 'conditions' => array('product_receipt_id IN' => $receiptIdArr),
																			'recursive'=>-1
																			));
			 $PaymentDetails_query = $PaymentDetails_query->hydrate(false);
			 if(!empty($PaymentDetails_query)){
			  $PaymentDetails = $PaymentDetails_query->toArray();
			}else{
				$PaymentDetails = array();
			}
			//pr($PaymentDetails);die;
			$pramount = array();
			$product_receipt_ids = array();
			foreach($PaymentDetails as  $PaymentDetail){
				$product_receipt_id = $PaymentDetail['product_receipt_id'];
				if(array_key_exists($product_receipt_id,$pramount)){
					$pramount[$product_receipt_id]  += $PaymentDetail['amount'];	
				}else{
					$pramount[$product_receipt_id]  = $PaymentDetail['amount'];
				}
				
			}
			$PaymentDetail_product_receipt_ids = array();
			foreach($pramount as $key =>$value){
				if(array_key_exists($key,$receiptIdArrpayment)){
					$recipt_payment = $receiptIdArrpayment[$key];

					$recipt_payment = round($recipt_payment,2);
					$value = round($value,2);
					
					if((float)$recipt_payment != (float)$value){
						$PaymentDetail_product_receipt_ids[] = $key;
					}
				}else{
					
				}
			}
        }
        if(empty($PaymentDetail_product_receipt_ids)){
			$PaymentDetail_product_receipt_ids  = array('0'=>null);
		}
		 $this->paginate = [
								 'conditions' => ['id IN' => $PaymentDetail_product_receipt_ids 
                                                   ],
									'limit' => 50,
                                ];
		
        
		$productReceipts = $this->paginate($ProductReceiptTable);
        if(!empty($productReceipts)){
          $productReceipts = $productReceipts->toArray();
        }else{
			$productReceipts = array();
		}
        foreach($productReceipts as $receiptDetail){
			$customerIdArr[] = $receiptDetail['customer_id'];
		}
 
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
		 
		$kiosks_query = $this->Kiosks->find('list');
		$kiosks_query = $kiosks_query->hydrate(false);
		if(!empty($kiosks_query)){
			$kiosks = $kiosks_query->toArray();
		}else{
			$kiosks = array();
		}
		$kiosk_id = 10000;
		$this->set(compact('productReceipts', 'customerBusiness','createdArr','kiosks','kiosk_id'));
        $this->set(compact('productReceipts', 'customerBusiness','totalAmount','totalBillCost','createdArr','kiosks','kiosk_id','pramount','totalpayment'));
    }
	
	
	public function drKioskPayment(){
        $this->loadModel('ProductReceipts');
        $this->loadModel('KioskProductSales');
        $this->loadModel('Products');
		
		$ProductReceipt_source = 't_product_receipts';
		
		$KioskProductSale_source = 't_kiosk_product_sales';
		
		$PaymentDetail_source = 't_payment_details';
		
		$ProductReceiptTable = TableRegistry::get($ProductReceipt_source,[
                                                                                'table' => $ProductReceipt_source,
                                                                            ]);
            
            $kioskProdctSalesTable = TableRegistry::get($KioskProductSale_source,[
                                                                                    'table' => $KioskProductSale_source,
                                                                                ]);
            
            $PaymentDetailTable = TableRegistry::get($PaymentDetail_source,[
																				'table' => $PaymentDetail_source,
																			]);
		
		
		if(array_key_exists('0',$this->request->params['pass'])){
			$kiosk_id = $this->request->params['pass'][0];
			if($kiosk_id == 10000){
				$kiosk_id = 0;
			}
		}else{
				$kiosk_id = 0;
		}
        $productReceiptDetail_query = $ProductReceiptTable->find('all',array(
																				'conditions' => array('kiosk_id' => $kiosk_id),
																			 'fields'=>array('id','vat','status','bill_amount', 'bill_cost','created')));
        $productReceiptDetail_query = $productReceiptDetail_query->hydrate(false);
        if(!empty($productReceiptDetail_query)){
            $productReceiptDetail = $productReceiptDetail_query->toArray();   
        }else{
            $productReceiptDetail = array();
        }
     
		$createdArr = array();
        if(!empty($productReceiptDetail)){
            foreach($productReceiptDetail as $key=>$productReceiptDta){
                if($productReceiptDta['status']==0){
                    $receiptIdArr[$productReceiptDta['id']] = $productReceiptDta['id'];
                    $receiptIdArrpayment[$productReceiptDta['id']] = $productReceiptDta['bill_amount'];
                }
            }
			if(empty($receiptIdArrpayment)){
				$receiptIdArrpayment  = array('0'=>null);
			}
			if(empty($receiptIdArr)){
				$receiptIdArr  = array('0'=>null);
			}
			$PaymentDetails_query = $PaymentDetailTable->find('all',array(
																		 'fields'=>array('id','product_receipt_id','amount'),
																		 'conditions' => array('product_receipt_id IN' => $receiptIdArr,
																							   'kiosk_id' => $kiosk_id,
																							   ),
																			'recursive'=>-1
																			));
			 $PaymentDetails_query = $PaymentDetails_query->hydrate(false);
			 if(!empty($PaymentDetails_query)){
			  $PaymentDetails = $PaymentDetails_query->toArray();
			}else{
				$PaymentDetails = array();
			}
			//pr($PaymentDetails);die;
			$pramount = array();
			$product_receipt_ids = array();
			foreach($PaymentDetails as  $PaymentDetail){
				$product_receipt_id = $PaymentDetail['product_receipt_id'];
				if(array_key_exists($product_receipt_id,$pramount)){
					$pramount[$product_receipt_id]  += $PaymentDetail['amount'];	
				}else{
					$pramount[$product_receipt_id]  = $PaymentDetail['amount'];
				}
				
			}
			$PaymentDetail_product_receipt_ids = array();
			foreach($pramount as $key =>$value){
				if(array_key_exists($key,$receiptIdArrpayment)){
					$recipt_payment = $receiptIdArrpayment[$key];

					$recipt_payment = round($recipt_payment,2);
					$value = round($value,2);
					
					if((float)$recipt_payment != (float)$value){
						$PaymentDetail_product_receipt_ids[] = $key;
					}
				}else{
					
				}
			}
        }
        if(empty($PaymentDetail_product_receipt_ids)){
			$PaymentDetail_product_receipt_ids  = array('0'=>null);
		}
		 $this->paginate = [
								 'conditions' => ['id IN' => $PaymentDetail_product_receipt_ids,
												  'kiosk_id' => $kiosk_id,
                                                   ],
									'limit' => 50,
                                ];
		
        
		$productReceipts = $this->paginate($ProductReceiptTable);
        if(!empty($productReceipts)){
          $productReceipts = $productReceipts->toArray();
        }else{
			$productReceipts = array();
		}
        foreach($productReceipts as $receiptDetail){
			$customerIdArr[] = $receiptDetail['customer_id'];
		}
 
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
		 
		$kiosks_query = $this->Kiosks->find('list');
		$kiosks_query = $kiosks_query->hydrate(false);
		if(!empty($kiosks_query)){
			$kiosks = $kiosks_query->toArray();
		}else{
			$kiosks = array();
		}
		$kiosk_id = 10000;
		$this->set(compact('productReceipts', 'customerBusiness','createdArr','kiosks','kiosk_id'));
        $this->set(compact('productReceipts', 'customerBusiness','totalAmount','totalBillCost','createdArr','kiosks','kiosk_id','pramount','totalpayment'));
    }
	
	function controllerPermissions(){
		$getControllers = $this->getControllers();
		foreach($getControllers as $key => $controller){
			if($controller == "Tests" || $controller == "Acl"){
				continue;
			}
			$allControllers[$controller] = $controller;
		}
		$users = $this->Users->find('list',array('keyField' => 'id','valueField' => 'username'))->toArray();
		$users_email = $this->Users->find('list',array('keyField' => 'id','valueField' => 'email'))->toArray();
		//pr($allControllers);die;
		$this->set(compact('allControllers','users','users_email'));
	}
	
	function getControllers() {
		$files = scandir('../src/Controller/');
		$results = [];
		$ignoreList = [
			'.', 
			'..', 
			'Component',
			'Component_fonrevive',
			'Component_mb',
			'AppController.php',
		];
		foreach($files as $file){
			if(!in_array($file, $ignoreList)) {
				$controller = explode('.', $file)[0];
				array_push($results, str_replace('Controller', '', $controller));
			}            
		}
		return $results;
	}
}
