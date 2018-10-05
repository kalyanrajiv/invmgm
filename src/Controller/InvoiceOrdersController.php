<?php
namespace App\Controller;
use Cake\Core\Configure;
use Cake\Core\Configure\Engine\PhpConfig;
use Cake\Datasource\ConnectionManager;
use Cake\ORM\TableRegistry;
use Cake\Mailer\Email;
use Cake\Utility\Text;
use Cake\ORM\Behavior;
use App\Controller\AppController;
use Cake\Network\Exception\NotFoundException;

class InvoiceOrdersController extends AppController
{
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
        $this->loadModel('InvoiceOrders');
        $this->loadModel('Kiosks');
        $this->loadModel('InvoiceOrderDetails');
        $CURRENCY_TYPE = Configure::read('CURRENCY_TYPE');
		$this->set(compact('CURRENCY_TYPE'));
    }
    
    public function index() {
        $kiosk_id = $this->request->Session()->read('kiosk_id');
        if(!empty($kiosk_id)){
			$InvoiceOrder_source = "kiosk_{$kiosk_id}_invoice_orders";
			$InvoiceOrderDetail_source = "kiosk_{$kiosk_id}_invoice_order_details";
		}else{
            $InvoiceOrder_source = "invoice_orders";
			$InvoiceOrderDetail_source = "invoice_order_details";
        }
        $InvoiceOrderTable = TableRegistry::get($InvoiceOrder_source,[
                                                                        'table' => $InvoiceOrder_source,
                                                                    ]);
		$InvoiceOrderDetail_Table = TableRegistry::get($InvoiceOrderDetail_source,[
                                                                        'table' => $InvoiceOrderDetail_source,
                                                                    ]);
		$users_query = $this->Users->find('list',[
                                            'keyField' => 'id',
                                            'valueField' => 'username'
                                           ]);
        if(!empty($users_query)){
            $users = $users_query->toArray();
        }else{
            $users = array();
        }
		$customers_query = $this->Customers->find('all');
        $customers_query = $customers_query->hydrate(false);
        if(!empty($customers_query)){
            $customers = $customers_query->toArray();
        }else{
            $customers = array();
        }
		$bussArr = $nameArr = array();
		foreach($customers as $key => $value){
			$bussArr[$value['id']] = $value['business'];
			$fname = $value['fname'];
			$lname = $value['lname'];
			$fullName = $fname." ".$lname;
			$nameArr[$value['id']] = $fullName;
		}
		//$this->InvoiceOrders->recursive = -1;
		$invoiceOrders_query = $this->paginate($InvoiceOrderTable);
        if(!empty($invoiceOrders_query)){
            $invoiceOrders = $invoiceOrders_query->toArray();
        }else{
            $invoiceOrders = array();
        }
		$date = strtotime(date("Y-m-d",strtotime("-7 days")));
		$counter=0;
        //pr($invoiceOrders);die;
		foreach($invoiceOrders as $key => $invoiceOrder){
            //pr($invoiceOrder);die;
			$invoiceId_Entity = $InvoiceOrderTable->get($invoiceOrder['id']);
			$createdDate = strtotime($invoiceOrder['created']);
			if($date > $createdDate){
				if($InvoiceOrderTable->delete($invoiceId_Entity)){
					$detail_data = $InvoiceOrderDetail_Table->find("list",["conditions" => ["invoice_order_id" => $invoiceOrder['id']],
															 'keyField' => 'id',
															 'valueField' => 'id',
															 ])->toArray();
					if(!empty($detail_data)){
						 foreach($detail_data as $key_s => $value_s){
							  $invoice_detail_Entity = $InvoiceOrderDetail_Table->get($key_s);
							  $InvoiceOrderDetail_Table->delete($invoice_detail_Entity);
						 }
					}
				}
				
				
			}
		}
		$this->paginate = [
                           'order' => ['id desc']
                          ];
		$invoiceOrders_query = $this->paginate($InvoiceOrderTable);
        if(!empty($invoiceOrders_query)){
            $invoiceOrders = $invoiceOrders_query->toArray();
        }else{
            $invoiceOrders = array();
        }
		$hint = $this->ScreenHint->hint('invoice_orders','index');
					if(!$hint){
						$hint = "";
					}
		$this->set(compact('hint','users','invoiceOrders','bussArr','nameArr'));
	}
    
    public function view($id = null) {
		
        $kiosk_id = $this->request->Session()->read('kiosk_id');
        if(!empty($kiosk_id)){
			$InvoiceOrder_source = "kiosk_{$kiosk_id}_invoice_orders";
			$InvoiceOrderDetail_source = "kiosk_{$kiosk_id}_invoice_order_details";
			$Product_source = "kiosk_{$kiosk_id}_products";
			$ProductReceipt_source = "kiosk_{$kiosk_id}_product_receipts";
			$KioskProductSale_source = "kiosk_{$kiosk_id}_product_sales";
			$PaymentDetail_source = "kiosk_{$kiosk_id}_payment_details";
		}else{
            $InvoiceOrder_source = "invoice_orders";
			$InvoiceOrderDetail_source = "invoice_order_details";
			$Product_source = "products";
			$ProductReceipt_source = "product_receipts";
			$KioskProductSale_source = "kiosk_product_sales";
			$PaymentDetail_source = "payment_details";
        }
        //pr($InvoiceOrder_source);die;
        $InvoiceOrderTable = TableRegistry::get($InvoiceOrder_source,[
                                                                'table' => $InvoiceOrder_source,
                                                            ]);
        $InvoiceOrderDetailTable = TableRegistry::get($InvoiceOrderDetail_source,[
                                                                'table' => $InvoiceOrderDetail_source,
                                                            ]);
        $ProductTable = TableRegistry::get($Product_source,[
                                                                'table' => $Product_source,
                                                            ]);
        $ProductReceiptTable = TableRegistry::get($ProductReceipt_source,[
                                                                'table' => $ProductReceipt_source,
                                                            ]);
        $KioskProductSaleTable = TableRegistry::get($KioskProductSale_source,[
                                                                'table' => $KioskProductSale_source,
                                                            ]);
        $PaymentDetailTable = TableRegistry::get($PaymentDetail_source,[
                                                                'table' => $PaymentDetail_source,
                                                            ]);
		$settingArr = $this->setting;
		if (!$InvoiceOrderTable->exists($id)) {
			throw new NotFoundException(__('Invalid invoice order'));
		}
		if(empty($kiosk_id)){
		  $new_kiosk_id = 10000;
		}else{
		  $new_kiosk_id = $kiosk_id;
		}
		$new_kiosk_data = $this->Kiosks->find("all",['conditions'=>['id' => $new_kiosk_id]])->toArray();
		
		//Configure::load('common-arrays');
		$countryOptions = Configure::read('uk_non_uk');
		
		$fullAddress = $kiosk_id = $kioskDetails = $kioskName = $kioskAddress1 = $kioskAddress2 = $kioskCity = $kioskState = $kioskZip  = $kioskZip = $kioskContact = $kioskCountry = $kioskTable = "";
		if($this->request->session()->read('Auth.User.group_id') == KIOSK_USERS &&
		   $this->request->session()->read('Auth.User.user_type')=='wholesale'){
			$kiosk_id = $this->request->Session()->read('kiosk_id');
			$kioskDetails_query = $this->Kiosks->find('all',array('conditions'=>array('Kiosks.id IN'=>$kiosk_id),'fields'=>array('id','name','address_1','address_2','city','state','zip','contact','country')));
            $kioskDetails_query = $kioskDetails_query->first();
            if(!empty($kioskDetails_query)){
                $kioskDetails = $kioskDetails_query->toArray();
            }else{
                $kioskDetails = array();
            }
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
		$kiosk_id = $this->request->Session()->read('kiosk_id');
        if(empty($kiosk_id)){
            $kiosk_id = 0;
        }
		$vat = $this->VAT;
		$invoiceOrderData_query = $InvoiceOrderTable->find('all',array('conditions'=>array('id'=>$id,'kiosk_id' => $kiosk_id)));
        //pr($invoiceOrderData_query);
        $invoiceOrderData_query = $invoiceOrderData_query->hydrate(false);
        if(!empty($invoiceOrderData_query)){
            $invoiceOrderData = $invoiceOrderData_query->first();
        }else{
            $invoiceOrderData = array();
        }
        if(!empty($invoiceOrderData)){
            $invoiceOrderDetail_ID = $invoiceOrderData['id'];
            $invoiceOrderDetailData_query = $InvoiceOrderDetailTable->find('all',[
                                                                                    'conditions' => ['invoice_order_id' => $invoiceOrderDetail_ID]
                                                                                ]);
            //pr($invoiceOrderDetailData_query);die;
            $invoiceOrderDetailData_query = $invoiceOrderDetailData_query->hydrate(false);
            if(!empty($invoiceOrderDetailData_query)){
                $invoiceOrderDetailData = $invoiceOrderDetailData_query->toArray();
            }else{
                $invoiceOrderDetailData = array();
            }
        }else{
            $invoiceOrderDetailData = array();
        }
        $this->set(compact('invoiceOrderDetailData'));
        //pr($invoiceOrderDetailData);die;
		$productIDs = array();
        //pr($invoiceOrderData);die;
		foreach($invoiceOrderDetailData as $key =>$sngData){
			$productIDs[] = $sngData['product_id'];
		}
		$repID = $invoiceOrderData['user_id'];
		$userData_query = $this->Users->find('all',array('fields' => array('id','username'),
							    'conditions' => array('Users.id' => $repID)));
        $userData_query = $userData_query->hydrate(false);
        if(!empty($userData_query)){
            $userData = $userData_query->first();
        }else{
            $userData = array();
        }
		$userName = ucfirst($userData['username']);
		$customer_Id = $invoiceOrderData['customer_id'];
		$customerData_query = $this->Customers->find('all',array(
															'conditions' => array('Customers.id' => $customer_Id),
															)
											  );
        $customerData_query = $customerData_query->hydrate(false);
        if(!empty($customerData_query)){
            $customerData = $customerData_query->first();
        }else{
            $customerData = array();
        }
		$country = $customerData['country'];
        if(empty($productIDs)){
            $productIDs = array(0 => null);
        }
		$products = $ProductTable->find('all',array(
							     'fields'=> array('id','product','product_code'),
							     'conditions' => array('id IN' => $productIDs),
							     ));
		$products = $products->hydrate(false);
        if(!empty($products)){
            $products = $products->toArray();
        }else{
            $products = array();
        }
        $productName = array();
		foreach($products as $product){			
			$productName[$product['id']] = array($product['product_code'],
									$product['product']);
		}
		
		//$this->InvoiceOrders->recursive = 1;
        //echo $id;die;
		$options = array('conditions' => array('id' => $id));
		$invoiceOrder_query = $InvoiceOrderTable->find('all', $options);
        //pr($invoiceOrder_query);die;
		$invoiceOrder_query = $invoiceOrder_query->hydrate(false);
        if(!empty($invoiceOrder_query)){
            $invoiceOrder = $invoiceOrder_query->first();
        }else{
            $invoiceOrder = array();
        }
        //pr($invoiceOrder);die;
		$this->set(compact('invoiceOrder','productName','vat','country', 'settingArr','customerData','userName','kioskTable','kioskContact','countryOptions',"new_kiosk_data"));
		$send_by_email = Configure::read('send_by_email');
		$emailSender = Configure::read('EMAIL_SENDER');
		if($this->request->is('post','put')){
			$CURRENCY_TYPE = Configure::read('CURRENCY_TYPE');
			$email = $this->request->data['customer_email'];
			$Email = new Email();
			$Email->config('default');
			$Email->viewVars(array(
                                   'invoiceOrder' => $invoiceOrder,
                                   'productName' => $productName,
                                   'vat' => $this->VAT,
                                   'settingArr' => $this->setting,
                                   'customerData' => $customerData,
                                   'userName' => $userName,
                                   'kioskContact'=>$kioskContact,
                                   'kioskTable'=>$kioskTable,
                                   'countryOptions'=>$countryOptions,
                                   'InvoiceOrderDetail_data' => $invoiceOrderDetailData,
							'CURRENCY_TYPE' => $CURRENCY_TYPE,
							'new_kiosk_data' => $new_kiosk_data
                                   )
                            );
			$emailTo = $email;
			$Email->template('performa');//using same template for new sale and edit sale
			$Email->emailFormat('both');
			$Email->to($emailTo);
			$Email->transport(TRANSPORT);
			$Email->from([$send_by_email => $emailSender]);
			//$Email->sender("sales@oceanstead.co.uk");  //$this->fromemail
			$Email->subject('Order Receipt');
			$Email->send();
			$this->Flash->success("Email Send Successfully");
		}
	}
    
    public function edit($id = null) {
        $kiosk_id = $this->request->Session()->read('kiosk_id');
		if(!empty($kiosk_id)){
            $InvoiceOrder_source = "kiosk_{$kiosk_id}_invoice_orders";
            $InvoiceOrderDetail_source = "kiosk_{$kiosk_id}_invoice_order_details";
            $Product_source = "kiosk_{$kiosk_id}_products";
        }else{
            $InvoiceOrder_source = "invoice_orders";
            $InvoiceOrderDetail_source = "invoice_order_details";
            $Product_source = "products";
        }
        $InvoiceOrderTable = TableRegistry::get($InvoiceOrder_source,[
                                                                                'table' => $InvoiceOrder_source,
                                                                            ]);
        $InvoiceOrderDetailTable = TableRegistry::get($InvoiceOrderDetail_source,[
                                                                                'table' => $InvoiceOrderDetail_source,
                                                                            ]);
        $ProductTable = TableRegistry::get($Product_source,[
                                                                'table' => $Product_source,
                                                            ]);
		if (!$InvoiceOrderTable->exists($id)) {
			throw new NotFoundException(__('Invalid invoice order'));
		}
		$vat = $this->VAT;
		$vatItem = $vat/100;
		if ($this->request->is(array('post', 'put'))) {
			if(array_key_exists('add_more_products',$this->request['data'])){
				return $this->redirect(array('controller'=>'invoice_order_details','action' => 'edit_invoice',$id));
			}else{
				//pr($this->request);die;
				$options = array('conditions' => array('id' => $id),'recursive' => -1);
				$invoiceOrderdata_query = $InvoiceOrderTable->find('all', $options);
                $invoiceOrderdata_query = $invoiceOrderdata_query->hydrate(false);
                if(!empty($invoiceOrderdata_query)){
                    $invoiceOrderdata = $invoiceOrderdata_query->first();
                }else{
                    $invoiceOrderdata = array();
                }
                //pr($invoiceOrderdata);die;
				$customer_Id = $invoiceOrderdata['customer_id'];
                if(empty($customer_Id)){
                    $customer_Id = array(0 => null);
                }
				$customerCountry_query = $this->Customers->find('all',array('conditions'=>array('Customers.id IN'=>$customer_Id)));
                $customerCountry_query = $customerCountry_query->hydrate(false);
                if(!empty($customerCountry_query)){
                    $customerCountry = $customerCountry_query->first();
                }else{
                    $customerCountry = array();
                }
				$country = $customerCountry['country'];
				$bulkDiscount = $invoiceOrderdata['bulk_discount'];
				$counter = 0;
				$afterDiscountPrice = 0;
              //  pr($this->request);die;
				if(!empty($this->request['data'])){
					foreach($this->request['data']['InvoiceOrder']['id'] as $key=>$orderDetailId){
						 $orderQuantity = $this->request['data']['InvoiceOrder']['quantity'][$key];
						 $orderDiscount = $this->request['data']['InvoiceOrder']['discount'][$key];
						 $orderPrice = $this->request['data']['InvoiceOrder']['price'][$key];
						 $discountedPrice = $orderQuantity*$orderPrice;//$orderQuantity*($orderPrice-($orderPrice*($orderDiscount/100)));
						 $afterDiscountPrice+=$discountedPrice;
						 $invoiceOrderDetailData = array(
															 //'id' => $orderDetailId,
															 'quantity' => $orderQuantity
														 );
						 $InvoiceOrderDetailTable->behaviors()->load('Timestamp');
						 $newEntity = $InvoiceOrderDetailTable->get($orderDetailId);
						 $patchEntity = $InvoiceOrderDetailTable->patchEntity($newEntity,$invoiceOrderDetailData);
						 //pr($patchEntity);
						 if($InvoiceOrderDetailTable->save($patchEntity)){
							 //$newEntity_1 = $this->InvoiceOrderDetails->get($id);
							 //pr($newEntity_1);die;
							 //echo'hi';die;
							 $counter++;
						 }
					 }
					 $newInvoiceOrderAmount = $afterDiscountPrice - $afterDiscountPrice*$bulkDiscount/100;
					 //$netPrice = $newInvoiceOrderAmount/(1+$vatItem);
					 $netPrice = $newInvoiceOrderAmount*$vatItem;
					 if($country=="OTH"){
						 $newInvoiceOrderAmount = $newInvoiceOrderAmount;
					 }else{
						 $newInvoiceOrderAmount = $newInvoiceOrderAmount + $netPrice;
					 }
					 
					 
					 if ((int)$counter) {
						 $InvoiceOrderTable->behaviors()->load('Timestamp');
						 $get_id = $InvoiceOrderTable->get($id);
						 $invoice_orderArr = array('amount' => $newInvoiceOrderAmount);
						 ///pr($invoice_orderArr);die;
						 $patch_entity = $InvoiceOrderTable->patchEntity($get_id,$invoice_orderArr);
						 //pr($patch_entity);die;
						 if($InvoiceOrderTable->save($patch_entity)){
						   //pr($patch_entity);die;
						 }else{
							 //debug($patch_entity->errors());die;
						 }
						 $this->Flash->success(__("$counter record(s) have been saved."));
						 return $this->redirect(array('controller'=>'invoice_orders','action' => 'index'));
					 } else {
						 $this->Flash->error(__('The invoice order could not be saved. Please, try again.'));
					 }
				}else{
					$getId = $InvoiceOrderTable->get($id);
					if ($InvoiceOrderTable->delete($getId)) {
						 $this->Flash->success(__("record(s) have been saved."));
						 return $this->redirect(array('controller'=>'invoice_orders','action' => 'index'));
					}
				}
				
			}
		} else {
			$options = array('conditions' => array('id' => $id));
			$query = $InvoiceOrderTable->find('all', $options);
            $query = $query->hydrate(false);
            $query_1 = $query->first();
            if(!empty($query_1)){
                $invoice_order_detail_Id = $query_1['id'];
                $invoice_order_detail_query = $InvoiceOrderDetailTable->find('all',[
                                                                                    'conditions' => ['invoice_order_id' => $invoice_order_detail_Id]
                                                                                    ]);
                $invoice_order_detail_query = $invoice_order_detail_query->hydrate(false);
                if(!empty($invoice_order_detail_query)){
                    $invoice_order_detail = $invoice_order_detail_query->toArray();
                }else{
                    $invoice_order_detail = array();
                }
            }
            //pr($query_1);die;
            if(!empty($query_1)){
                $query_1['invoice_order_details'] = $invoice_order_detail;
                $this->request->data = $query_1;
            }else{
                $this->request->data = array();
            }
            //pr($this->request->data);die;
			$customerID = $this->request->data['customer_id'];
		}
		$vat = $this->VAT;
		$vatItem = $vat/100;
		$products_query = $ProductTable->find('list',[
                                                  'keyField' => 'id',
                                                  'valueField' => 'product'
                                                 ]
                                          );
        if(!empty($products_query)){
            $products = $products_query->toArray();
        }else{
            $products = array();
        }
		
		$products_code_query = $ProductTable->find('list',[
                                                  'keyField' => 'id',
                                                  'valueField' => 'product_code'
                                                 ]
                                          );
        if(!empty($products_code_query)){
            $products_code = $products_code_query->toArray();
        }else{
            $products_code = array();
        }
		
		$products_quantity_query = $ProductTable->find('list',[
                                                  'keyField' => 'id',
                                                  'valueField' => 'quantity'
                                                 ]
                                          );
        if(!empty($products_quantity_query)){
            $products_quantity = $products_quantity_query->toArray();
        }else{
            $products_quantity = array();
        }
		
		$this->set(compact('products_quantity','products_code'));
		
		$kiosks_query = $this->Kiosks->find('list');
        $kiosks_query = $kiosks_query->hydrate(false);
        if(!empty($kiosks_query)){
            $kiosks = $kiosks_query->toArray();
        }else{
            $kiosks = array();
        }
        //pr($kiosks);die;
		$users_query = $this->Users->find('list');
        $users_query = $users_query->hydrate(false);
        if(!empty($users_query)){
            $users = $users_query->toArray();
        }else{
            $users = array();
        }
		$this->set(compact('kiosks', 'users','products','vatItem'));
	}
    
    public function delete($id = null) {
		$kiosk_id = $this->request->Session()->read('kiosk_id');
        if($kiosk_id == 10000 || $kiosk_id == ""){
            $invoice_order_table_source = "invoice_orders";
            $InvoiceOrderDetail_source = "invoice_order_details";
        }else{
            $invoice_order_table_source = "kiosk_{$kiosk_id}_invoice_orders";
            $InvoiceOrderDetail_source = "kiosk_{$kiosk_id}_invoice_order_details";
        }
        $InvoiceOrderTable = TableRegistry::get($invoice_order_table_source,[
                                                                                'table' => $invoice_order_table_source,
                                                                            ]);
        $InvoiceOrderDetailTable = TableRegistry::get($InvoiceOrderDetail_source,[
                                                                                'table' => $InvoiceOrderDetail_source,
                                                                            ]);
        
		if (!$InvoiceOrderTable->exists($id)) {
			throw new NotFoundException(__('Invalid performa'));
		}
        $getId = $InvoiceOrderTable->get($id);
		$this->request->allowMethod('post', 'delete');
		if ($InvoiceOrderTable->delete($getId)) {
            $order_detail_data_query = $InvoiceOrderDetailTable->find("all",array('conditions' => array("invoice_order_id" => $id)));
            $order_detail_data_query = $order_detail_data_query->hydrate(false);
            if(!empty($order_detail_data_query)){
                $order_detail_data = $order_detail_data_query->toArray();
            }else{
                $order_detail_data = array();
            }
            $counter = 0;
            if(!empty($order_detail_data)){
                foreach($order_detail_data as $key => $value){
                    $orderid = $value['id'];
                    $getOrderId = $InvoiceOrderDetailTable->get($orderid);
                    if ($InvoiceOrderDetailTable->delete($getOrderId)) {
                        $counter++;
                    }
                }
                
                if ($counter > 0) {
                    $this->Flash->success(__('The performa has been deleted.'));
                }else{
                    $this->Flash->success(__('The performa has been deleted.'));
                }
            }else{
                $this->Flash->success(__('The performa has been deleted.'));
            }
		} else {
            //echo'bye';die;
			$this->Flash->error(__('The performa order could not be deleted. Please, try again.'));
		}
		return $this->redirect(array('action' => 'index'));
	}
    
    public function export(){
		$invoiceOrders_query = $this->InvoiceOrders->find('all');
        $invoiceOrders_query = $invoiceOrders_query->hydrate(false);
        if(!empty($invoiceOrders_query)){
            $invoiceOrders = $invoiceOrders_query->toArray();
        }else{
            $invoiceOrders = array();
        }
		$tmpInvoiceOrder = array();
		foreach($invoiceOrders as $key => $invoiceOrder){
		 $tmpInvoiceOrder[] = $invoiceOrder;
		}
		$this->outputCsv('InvoiceOrder_'.time().".csv" ,$tmpInvoiceOrder);
		$this->autoRender = false;
	}
    
    public function paymentOptions($id = null,$special = 0) {
        $kiosk_id = $this->request->Session()->read('kiosk_id');
        if(!empty($kiosk_id)){
			$InvoiceOrder_source = "kiosk_{$kiosk_id}_invoice_orders";
			$InvoiceOrderDetail_source = "kiosk_{$kiosk_id}_invoice_order_details";
			$Product_source = "kiosk_{$kiosk_id}_products";
			$ProductReceipt_source = "kiosk_{$kiosk_id}_product_receipts";
			$KioskProductSale_source = "kiosk_{$kiosk_id}_product_sales";
			$PaymentDetail_source = "kiosk_{$kiosk_id}_payment_details";
		}else{
            $InvoiceOrder_source = "invoice_orders";
			$InvoiceOrderDetail_source = "invoice_order_details";
			$Product_source = "products";
			$ProductReceipt_source = "product_receipts";
			$KioskProductSale_source = "kiosk_product_sales";
			$PaymentDetail_source = "payment_details";
        }
        $InvoiceOrderTable = TableRegistry::get($InvoiceOrder_source,[
                                                                        'table' => $InvoiceOrder_source,
                                                                    ]);
        $InvoiceOrderDetailTable = TableRegistry::get($InvoiceOrderDetail_source,[
                                                                        'table' => $InvoiceOrderDetail_source,
                                                                    ]);
        $ProductTable = TableRegistry::get($Product_source,[
                                                                        'table' => $Product_source,
                                                                    ]);
        $ProductReceiptTable = TableRegistry::get($ProductReceipt_source,[
                                                                        'table' => $ProductReceipt_source,
                                                                    ]);
        $KioskProductSaleTable = TableRegistry::get($KioskProductSale_source,[
                                                                        'table' => $KioskProductSale_source,
                                                                    ]);
        $PaymentDetailTable = TableRegistry::get($PaymentDetail_source,[
                                                                        'table' => $PaymentDetail_source,
                                                                    ]);
		//pr($this->request);die;
		//if (!$InvoiceOrderTable->exists($id)) {
		//	throw new NotFoundException(__('Invalid invoice order'));
		//}
		$vat = $this->VAT;
		$invoiceOrderDetails_query = $InvoiceOrderTable->find('all',array(
									'conditions' => array('id' => $id)
									)
								 );
        $invoiceOrderDetails_query = $invoiceOrderDetails_query->hydrate(false);
        if(!empty($invoiceOrderDetails_query)){
            $invoiceOrderDetails = $invoiceOrderDetails_query->first();
        }else{
            $invoiceOrderDetails = array();
        }
		
        $customer_id = $invoiceOrderDetails['customer_id'];
        
        $result_query = $this->Customers->find('all',array('conditions' => array('id' => $customer_id)));
        $result_query = $result_query->hydrate(false);
        if(!empty($result_query)){
            $cus_result = $result_query->first();
        }else{
            $cus_result = array();
        }
        $this->set(compact('cus_result'));
        $country = $cus_result['country'];
		 $agent_id = $cus_result['agent_id']; 
		          
		$customerId = $invoiceOrderDetails['customer_id'];
		$address1 = $invoiceOrderDetails['del_address_1'];
		$address2 = $invoiceOrderDetails['del_address_2'];
		$del_city = $invoiceOrderDetails['del_city'];
		$del_state = $invoiceOrderDetails['del_state'];
		$del_zip = $invoiceOrderDetails['del_zip'];
		$user_id = $this->request->Session()->read('Auth.User.id');
		$firstName = $invoiceOrderDetails['fname'];
		$lastName = $invoiceOrderDetails['lname'];
		$mobileNum = $invoiceOrderDetails['mobile'];
		$emailId = $invoiceOrderDetails['email'];
		//$orig_bill_amt = $bill_amount = $invoiceOrderDetails['amount'];
        $this->set(compact('special'));
        if($special == 1){
			if($country == "OTH"){
				$orig_bill_amt = $bill_amount = $invoiceOrderDetails['amount'];
			}else{
				$vatItem = $vat/100;
				$orig_bill_amt = $bill_amount = round(($invoiceOrderDetails['amount']/((1+$vatItem))),2);
			}
		}else{
			$orig_bill_amt = $bill_amount = $invoiceOrderDetails['amount'];
		} 
		$bulk_discount = $invoiceOrderDetails['bulk_discount'];
		$productId = "";
		$bill_cost = 0;
        $invoice_detail_dataId = $invoiceOrderDetails['id'];
        $invoice_detail_data_query = $InvoiceOrderDetailTable->find('all',[
                                                                            'conditions' => ['invoice_order_id' => $invoice_detail_dataId]    
                                                                            ]);
        $invoice_detail_data_query = $invoice_detail_data_query->hydrate(false);
        if(!empty($invoice_detail_data_query)){
            $invoice_detail_data = $invoice_detail_data_query->toArray();
        }else{
            $invoice_detail_data = array();
        }
        $this->set(compact('invoice_detail_data'));
       // pr($invoice_detail_data);die;
		if(array_key_exists('product_id',$invoice_detail_data[0])){
			$productId = $invoice_detail_data[0]['product_id'];
			$Details_query = $ProductTable->find('all',array(
									'conditions' => array('id' => $productId)
									)
								 );
            $Details_query = $Details_query->hydrate(false);
            if(!empty($Details_query)){
                $Details = $Details_query->first();
            }else{
                $Details = array();
            }
			//pr($Details);die;
			if(!empty($Details)){
				$bill_cost = $Details['cost_price'];
			}
		}//die;
		$customerCountry_query = $this->Customers->find('all',array('conditions'=>array('Customers.id'=>$customerId)));
        $customerCountry_query = $customerCountry_query->hydrate(false);
        if(!empty($customerCountry_query)){
            $customerCountry = $customerCountry_query->first();
        }else{
            $customerCountry = array();
        }
		$country = $customerCountry['country'];
			
		$quantityError = array();
		$quantityErrorStr = '';
		foreach($invoice_detail_data as $key => $invoiceOrderDetail){
			$invoiceProductId = $invoiceOrderDetail['product_id'];
			$invoiceProductQtty = $invoiceOrderDetail['quantity'];
			
			$checkProductQuantity_query = $ProductTable->find('all',array(
									'conditions' => array('id'=>$invoiceProductId),
									'fields' => ['quantity','product_code']
									)
								     );
            $checkProductQuantity_query = $checkProductQuantity_query->hydrate(false);
            if(!empty($checkProductQuantity_query)){
                $checkProductQuantity = $checkProductQuantity_query->first();
            }else{
                $checkProductQuantity = array();
            }
            //pr($checkProductQuantity);die;
			$productQtty = $checkProductQuantity['quantity'];
			$product_code = $checkProductQuantity['product_code'];
			if($productQtty<$invoiceProductQtty){
				$quantityError[] = "Product quantity for product code: {$product_code} is not available. Please choose a different product";
			}
		}
		
		if(count($quantityError)>0){
			if(is_array($quantityError)){
				$quantityErrorStr = implode("<br/>",$quantityError);
				$this->Flash->error($quantityErrorStr);
			}else{
				$quantityErrorStr = '';
			}
			return $this->redirect(array('action'=>'edit',$id));
		}
		
		$productName_query = $ProductTable->find('list',[
                                                   'keyField'=>'id',
                                                   'valueField' => 'product'
                                                  ]
                                           );
        $productName_query = $productName_query->hydrate(false);
        if(!empty($productName_query)){
            $productName = $productName_query->toArray();
        }else{
            $productName = array();
        }
		//$this->InvoiceOrder->recursive=1;
		$options = array('conditions' => array('id' => $id));
        $invoiceOrder_query = $InvoiceOrderTable->find('all', $options);
        $invoiceOrder_query = $invoiceOrder_query->hydrate(false);
        if(!empty($invoiceOrder_query)){
            $invoiceOrder = $invoiceOrder_query->first();
        }else{
            $invoiceOrder = array();
        }
		$this->set('invoiceOrder', $invoiceOrder);
		$this->set(compact('productName','vat','country'));
		
		if ($this->request->is(array('post', 'put'))) {
            
            $special_invoice = 0;
			if(array_key_exists('special',$this->request['data'])){
				$special_invoice = $this->request['data']['special'];
			}
			if($special_invoice == 1){
				$receiptTable_source	=	't_product_receipts';
						$paymentTable_source = 't_payment_details';
						//$this->PaymentDetail->setSource($paymentTable);
						//$this->ProductReceipt->setSource($receiptTable);
                        
                 $ProductReceiptTable = TableRegistry::get($receiptTable_source,[
                                                                        'table' => $receiptTable_source,
                                                                    ]);
                $PaymentDetailTable = TableRegistry::get($paymentTable_source,[
                                                                                        'table' => $paymentTable_source,
                                                                                    ]);                                                                    
                                                                            
                        
			}
			
			
			if($special == 1){
				if($country == "OTH"){
					$amountToPay = $invoiceOrderDetails['amount'];
				}else{
					$vatItem = $vat/100;
					$amountToPay = round(($invoiceOrderDetails['amount']/((1+$vatItem))),2);
				}
			}else{
				$amountToPay = $invoiceOrderDetails['amount'];
			}
            
            
			//$amountToPay = $invoiceOrderDetails['amount'];
			$totalPaymentAmount = 0;
			$amountDesc = array();
			$countCycles = 0;
			$error = '';
			$errorStr = '';
			foreach($this->request['data']['Payment']['Amount'] as $key => $paymentAmount){
				(float)$totalPaymentAmount+= (float)$paymentAmount;
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
					$this->Flash->error("$errorStr");
					return $this->redirect(array('action'=>'payment_options', $id));
			}else{
				if($country=="OTH" || $special_invoice == 1){
					$vatPercentage = 0;
				}else{
					$vatPercentage = $vat;
				}
				$receiptData = array(
						'customer_id' => $customerId,
						'address_1' => $address1,
						'address_2' => $address2,
						'city' => $del_city,
						'state' => $del_state,
						'zip' => $del_zip,
						'vat' => $vatPercentage,
						'processed_by' => $user_id,
						'fname' => $firstName,
						'lname' => $lastName,
						'mobile' => $mobileNum,
						'email' => $emailId,
						'bill_amount' => $bill_amount,
						'orig_bill_amount' => $orig_bill_amt,
						'bulk_discount' => $bulk_discount,
						'bill_cost' => $bill_cost,
						'agent_id' => $agent_id
					     );
				//pr($receiptData);die;
				$kisk_id = $this->request->Session()->read('kiosk_id');
				if($special_invoice == 1){
					if(empty($kisk_id)){
						$receiptData['kiosk_id'] = 0;
					}else{
						$receiptData['kiosk_id'] = $kisk_id;
					}	
				}
                
                
                $ProductReceiptTable->behaviors()->load('Timestamp');
				$new_entity = $ProductReceiptTable->newEntity($receiptData,['validate' => false]);
                $patch_entity = $ProductReceiptTable->patchEntity($new_entity,$receiptData,['validate' => false]);
				$ProductReceiptTable->save($patch_entity);
			}
			
			$receiptId = $patch_entity->id;
			
			if($receiptId==0){
				//pr($receiptData);
				$this->Flash->error("Product receipt could not be generated. Please try again");
				return $this->redirect(array('action'=>'view',$id));
			}
            $kisk_id = $this->request->Session()->read('kiosk_id');
            if($kisk_id == ""){
                $kisk_id = 0;
            }
			$counter = 0;
				if($this->request['data']['Payment']['Payment_Method'][0] == "On Credit" && $countCycles==1){
					$paymentDetailData = array(
						'product_receipt_id' => $receiptId,
						'payment_method' => $this->request['data']['Payment']['Payment_Method'][0],
						'description' => $this->request['data']['Payment']['Description'][0],
						'amount' => $amountToPay,
						'payment_status' => 0,
                        'kiosk_id' => $kisk_id,
						'status' => 1,
						'agent_id' => $agent_id
						   );
                    $PaymentDetailTable->behaviors()->load('Timestamp');
					$newEntity = $PaymentDetailTable->newEntity($paymentDetailData,['validate' =>false]);
                    $patchEntity = $PaymentDetailTable->patchEntity($newEntity,$paymentDetailData,['validate' =>false]);
					if($PaymentDetailTable->save($patchEntity)){
						$counter++;
					}
				}else{
					foreach($this->request['data']['Payment']['Amount'] as $key => $paymentAmount){
					$paymentMethod = $this->request['data']['Payment']['Payment_Method'][$key];
					$paymentDescription = $this->request['data']['Payment']['Description'][$key];
					
					
						if(!empty($paymentAmount) && $paymentDescription){
							$paymentDetailData = array(
									'product_receipt_id' => $receiptId,
									'payment_method' => $paymentMethod,
									'description' => $paymentDescription,
									'amount' => $paymentAmount,
									'payment_status' => 1,
                                    'kiosk_id' => $kisk_id,
									'status' => 1,
									'agent_id' => $agent_id
									   );
							$PaymentDetailTable->behaviors()->load('Timestamp');
							$entity_new = $PaymentDetailTable->newEntity($paymentDetailData,['validate' => false]);
							$entity_patch = $PaymentDetailTable->patchEntity($entity_new,$paymentDetailData,['validate' => false]);
							if($PaymentDetailTable->save($entity_patch)){
								$counter++;
							}
						}
					}
				}
				
			//die("fdsfsF");
			if($counter>0){
				return $this->redirect(array('action'=>'create_sale',$id,$receiptId,$special_invoice));;
			}else{
				$flashMessage = ("Sale could not be created. Please try again");
				//$this->ProductReceipt->query("DELETE FROM `product_receipts` WHERE `id` = '$receiptId'");
                $conn = ConnectionManager::get('default');
                $stmt = $conn->execute("DELETE FROM `product_receipts` WHERE `id` = '$receiptId'"); 
				$this->Flash->success($flashMessage);
				return $this->redirect(array('action'=>'payment_options', $id));
			}
		}
	}
    
    public function createSale($invoiceOrderId = '',$receiptId = '',$special_invoice = 0){
        $CURRENCY_TYPE = Configure::read('CURRENCY_TYPE');
		$settingArr = $this->setting;
		$kisk_id = $this->request->Session()->read('kiosk_id');
		$kiosk_id = 0;
		if((int)$kisk_id){
			$kiosk_id = $this->request->Session()->read('kiosk_id');
		}
        if(!empty($kiosk_id)){
			$InvoiceOrder_source = "kiosk_{$kiosk_id}_invoice_orders";
			$InvoiceOrderDetail_source = "kiosk_{$kiosk_id}_invoice_order_details";
			$Product_source = "kiosk_{$kiosk_id}_products";
			$ProductReceipt_source = "kiosk_{$kiosk_id}_product_receipts";
			$KioskProductSale_source = "kiosk_{$kiosk_id}_product_sales";
			$PaymentDetail_source = "kiosk_{$kiosk_id}_payment_details";
		}else{
            $InvoiceOrder_source = "invoice_orders";
			$InvoiceOrderDetail_source = "invoice_order_details";
			$Product_source = "products";
			$ProductReceipt_source = "product_receipts";
			$KioskProductSale_source = "kiosk_product_sales";
			$PaymentDetail_source = "payment_details";
        }
        //echo $special_invoice;die;
        if($special_invoice == 1){
				$KioskProductSale_source = 't_kiosk_product_sales';
				$ProductReceipt_source = 't_product_receipts';
				$PaymentDetail_source = 't_payment_details';
		}
        
        $InvoiceOrderTable = TableRegistry::get($InvoiceOrder_source,[
                                                                        'table' => $InvoiceOrder_source,
                                                                    ]);
        $InvoiceOrderDetailTable = TableRegistry::get($InvoiceOrderDetail_source,[
                                                                        'table' => $InvoiceOrderDetail_source,
                                                                    ]);
        $ProductTable = TableRegistry::get($Product_source,[
                                                                        'table' => $Product_source,
                                                                    ]);
        $ProductReceiptTable = TableRegistry::get($ProductReceipt_source,[
                                                                        'table' => $ProductReceipt_source,
                                                                    ]);
        $KioskProductSaleTable = TableRegistry::get($KioskProductSale_source,[
                                                                        'table' => $KioskProductSale_source,
                                                                    ]);
        $PaymentDetailTable = TableRegistry::get($PaymentDetail_source,[
                                                                        'table' => $PaymentDetail_source,
                                                                    ]);
		
		$currencySymbol = $this->setting['currency_symbol'];
		$payment_query = $PaymentDetailTable->find('all',array(
															'conditions' => array('invoice_order_id' => $invoiceOrderId)
														)
											);
        $payment_query = $payment_query->hydrate(false);
        if(!empty($payment_query)){
            $payment = $payment_query->toArray();
        }else{
            $payment = array();
        }
		
		if (!$InvoiceOrderTable->exists($invoiceOrderId)) {
			throw new NotFoundException(__('Invalid invoice order'));
		}
		$invoiceOrderDetails_query = $InvoiceOrderTable->find('all',array(
																		'conditions' => array('id' => $invoiceOrderId)
																	)
								 );
        $invoiceOrderDetails_query = $invoiceOrderDetails_query->hydrate(false);
        if(!empty($invoiceOrderDetails_query)){
            $invoiceOrderDetails = $invoiceOrderDetails_query->first();
        }else{
            $invoiceOrderDetails = array();
        }
		// pr($invoiceOrderDetails);die;
		$date = strtotime(date("Y-m-d",strtotime("-7 days")));
		$createdDate = strtotime($invoiceOrderDetails['created']);;
		if($date > $createdDate){
			$flashMessage = ("Sale could not be created. Performa was created prior to 7 days from today's date.");
			$this->Flash->error($flashMessage);
			return $this->redirect(array('action'=>'payment_options', $invoiceOrderId));
		}
		
		$productName_query = $ProductTable->find('list',[
                                                   'keyField'=>'id',
                                                   'valueField' => 'product'
                                                  ]
                                           );
        $productName_query = $productName_query->hydrate(false);
        if(!empty($productName_query)){
            $productName = $productName_query->toArray();
        }else{
            $productName = array();
        }
		//creating a receipt id for the order
		$userId = $this->request->Session()->read('Auth.User.id');
		
		if($receiptId > 0){
			$user_id = $this->Auth->user('id');
			$userTypeData_query = $this->Users->find('all',array(
															'conditions' => array('Users.id' => $user_id),
															'fields' => array('user_type'),
															)
											  );
            $userTypeData_query = $userTypeData_query->hydrate(false);
            if(!empty($userTypeData_query)){
                $userTypeData = $userTypeData_query->first();
            }else{
                $userTypeData = array();
            }
			if(!empty($userTypeData)){
				if($this->request->session()->read('Auth.User.group_id') == ADMINISTRATORS || $this->request->session()->read('Auth.User.group_id') == MANAGERS || $this->request->session()->read('Auth.User.group_id') == FRANCHISE_OWNER){
					$sale_type = 1;
				}else{
					$userType = $userTypeData['user_type'];
					if($userType == 'wholesale'){
						$sale_type = 1;
					}else{
						$sale_type = 0;
					}
				}
			}else{
				if($this->request->session()->read('Auth.User.group_id') == ADMINISTRATORS || $this->request->session()->read('Auth.User.group_id') == MANAGERS){
					$sale_type = 1;
				}else{
					$sale_type = 0;
				}
			}
			$productReceiptId = $receiptId;
			$counter = $amount = $totalVat = $totalDiscount = 0;
			$basketStr = "";
			//for bill cost declaring billCost array here
			$billCost = array();
			//adding the order details to kiosk product sales
			//pr($invoiceOrderDetails);die;
            $customerId = $invoiceOrderDetails['customer_id'];
			$customerCountry_query = $this->Customers->find('all',array('conditions'=>array('Customers.id'=>$customerId)));
            $customerCountry_query = $customerCountry_query->hydrate(false);
            if(!empty($customerCountry_query)){
                $customerCountry = $customerCountry_query->first();
            }else{
                $customerCountry = array();
            }
			$country = $customerCountry['country'];
			$p_kiosk_id = $this->request->Session()->read('kiosk_id');
			if($p_kiosk_id == 0 || $p_kiosk_id == ""){
				$p_kiosk_id = 10000;
			}
            $invoice_detail_dataId = $invoiceOrderDetails['id'];
            $invoice_detail_data_query = $InvoiceOrderDetailTable->find('all',[
                                                                                'conditions' => ['invoice_order_id' => $invoice_detail_dataId]    
                                                                                ]);
            $invoice_detail_data_query = $invoice_detail_data_query->hydrate(false);
            if(!empty($invoice_detail_data_query)){
                $invoice_detail_data = $invoice_detail_data_query->toArray();
            }else{
                $invoice_detail_data = array();
            }
            $this->set(compact('invoice_detail_data'));
			foreach($invoice_detail_data as $detailId => $productDetail){
				$productId = $productDetail['product_id'];
				$quantity = $productDetail['quantity'];
				$prodCode_query = $ProductTable->findById($productId, array('product_code'));
                $prodCode_query = $prodCode_query->hydrate(false);
                if(!empty($prodCode_query)){
                    $prodCode = $prodCode_query->first();
                }else{
                    $prodCode = array();
                }
				//pr($prodCode);die;
                $productCode = $prodCode['product_code']; // sourabh
				
                $prodCost_query = $ProductTable->findById($productId, array('cost_price'));
                $prodCost_query = $prodCost_query->hydrate(false);
                if(!empty($prodCost_query)){
                    $prodCost = $prodCost_query->first();
                }else{
                    $prodCost = array();
                }
                $productCost = $prodCode['cost_price'];
                
				$numerator = $productDetail['price']*100;
				$vat = $this->VAT;
				$denominator = $vat+100;
				$priceWithoutVat = $numerator/$denominator;
				$priceWithoutVat = round($priceWithoutVat,2);
					$dicounted_amount = $priceWithoutVat * ($productDetail['discount']/100);
					$value_after_discount = $priceWithoutVat - $dicounted_amount;
					$bulk_dis = $invoiceOrderDetails['bulk_discount'];
					if($bulk_dis  > 0){
						$bulk_val = $value_after_discount * ($bulk_dis/100);
						$after_bulk_val = $value_after_discount - $bulk_val;
						$selling_price_withot_vat = $after_bulk_val*$quantity;
					}else{
						$after_bulk_val = $value_after_discount;
						$selling_price_withot_vat = $value_after_discount*$quantity;
					}
					
					if($country != 'OTH'){
						$vat_price = $after_bulk_val * ($vat/100);
						$final_vat = $vat_price * $quantity;
					}else{
						$vat_price = 0;
						$final_vat = 0;
					}
					$data = array(
                     'quantity' => $quantity,
                     'product_code' =>  $productCode,
                     'selling_price_withot_vat' =>$selling_price_withot_vat,
                     'vat' => $final_vat
                    );
				$kioskProductSalesData = array(
												'kiosk_id' => $productDetail['kiosk_id'],
												'product_id' => $productId,
												'customer_id' => $invoiceOrderDetails['customer_id'],
												'quantity' => $quantity,
												'sale_price' => $priceWithoutVat, //$productDetail['price']
												'refund_price' => 0,
												'discount' => $productDetail['discount'],
												'refund_gain' => 0,
												'sold_by' => $userId,
												'refund_by' => 0,
												'status' => 1,
												'sale_type' => $sale_type,
												'refund_status' => 0,
												'refund_remarks' => "",
												'product_receipt_id' => $productReceiptId
											);
                if($special_invoice == 1){
					$kioskProductSalesData['cost_price'] = $productCost;
				}
				$billCost[$productId] = $quantity;
                $KioskProductSaleTable->behaviors()->load('Timestamp');
				$new_entity = $KioskProductSaleTable->newEntity($kioskProductSalesData,['validate' => false]);
                $patch_entity = $KioskProductSaleTable->patchEntity($new_entity,$kioskProductSalesData,['validate' => false]);
				if($KioskProductSaleTable->save($patch_entity)){
                    if($special_invoice == 1){
						$is_special = 1;
					}else{
						$is_special = 0;
					}
					$this->insert_to_ProductSellStats($productId,$data,$p_kiosk_id,$operations = '+',$is_special);
					//adjusting the product quantity from central stock
					//$this->Product->query("UPDATE `$productsTable` SET `quantity` = `quantity` - $quantity WHERE `$productsTable`.`id` = $productId");
                    $conn = ConnectionManager::get('default');
                    $stmt = $conn->execute("UPDATE `$Product_source` SET `quantity` = `quantity` - $quantity WHERE `$Product_source`.`id` = $productId"); 
					
					$counter++;
					$vat = $this->VAT;
					$vatItem = $vat/100;
					$netAmount = $invoiceOrderDetails['amount'];
					$itemPrice = $productDetail['price']/(1+$vatItem);
					$itemPrice = round($itemPrice,2);
					$vatperitem = $productDetail['quantity']*($productDetail['price']-$itemPrice);
					//$discount = $productDetail['price']*$productDetail['discount']/100*$productDetail['quantity'];
					$discount = $itemPrice*$productDetail['discount']/100*$productDetail['quantity'];
					//$discountAmount = ($productDetail['quantity']*$productDetail['price'])-$discount;
					$discountAmount = ($productDetail['quantity']*$itemPrice)-$discount;
					$amount+=$discountAmount;
					
					$totalDiscount+=$discount;
					$basketStr.="<tr>
						<td>{$counter})</td>
						<td>{$productDetail['product_id']}</td>
						<td>".$productName[$productDetail['product_id']]."</td>
						<td>".$productCode."</td>
						<td>".$productDetail['quantity']."</td>
						<td>".$CURRENCY_TYPE.$priceWithoutVat."</td>
						<td> ".$productDetail['discount']."</td>
						<td>".$CURRENCY_TYPE.number_format($discount,2)."</td>
						<td>".$CURRENCY_TYPE.number_format($discountAmount,2)."</td></tr>";
						//$productDetail['price']
				}
			}
					
			$finalAmount = $amount - $amount*$invoiceOrderDetails['bulk_discount']/100;
			$bulkDiscountValue = $amount*$invoiceOrderDetails['bulk_discount']/100;
			//$totalVat=$finalAmount-$finalAmount/(1+$vatItem);
			$totalVat=$finalAmount*$vatItem;
			$totalVat=number_format($totalVat,2);
		}
		$costArr = array();
		$totalBillCost = 0;
		if(count(array_keys($billCost)) > 0){
			$prodIds = array_keys($billCost);
            if(empty($prodIds)){
                $prodIds = array(0 => null);
            }
			$prodCosts_query = $ProductTable->find('list',[
														'conditions' => ['id IN' => $prodIds],
														'keyField' => 'id',
                                                        'valueField' => 'cost_price'
													]
                                            );
			$prodCosts_query = $prodCosts_query->hydrate(false);
            if(!empty($prodCosts_query)){
                $prodCosts  = $prodCosts_query->toArray();
            }else{
                $prodCosts = array();
            }
			foreach($billCost as $prodId => $prodQty){
				$totalBillCost += $prodCosts[$prodId] * $prodQty;
			}
            
            $kiosk_id = $this->request->Session()->read('kiosk_id');
            $dataArr = array('bill_cost' => $totalBillCost,'sale_type' => $sale_type);
			$get_id = $ProductReceiptTable->get($receiptId);
            $patch_Entity = $ProductReceiptTable->patchEntity($get_id,$dataArr,['validate' => false]);
			$ProductReceiptTable->save($patch_Entity);
		}
		if($special_invoice == 1){
			$vatItem = $totalVat = 0;
			$netAmount = $finalAmount;
		}else{
			$netAmount = $netAmount;
		}
		if(!empty($basketStr)){
				$basketStr1 = "<table><tr>
							<th>Sr No</th>
							<th>Product Id</th>
							<th style='width:250px;'>Product</th>
							<th>Product Code</th>
							<th>Quanity</th>
							<th>Price/Item</th>
							<th>Discount %</th>
							<th>Discount Value</th>
							<th>Gross</th>
							</tr>".$basketStr."
							<tr><td colspan='8'>Sub Total</td><td>".$CURRENCY_TYPE.number_format($finalAmount,2)."</td></tr>
							<tr><td colspan='8'>Bulk Discount ({$invoiceOrderDetails['bulk_discount']} %)</td><td>".$CURRENCY_TYPE.number_format($bulkDiscountValue,2)."</td></tr>
							<tr><td colspan='8'>Vat</td><td>".$CURRENCY_TYPE.number_format($totalVat,2)."</td></tr>
							<!--tr><td colspan='8'>Net Amount</td><td>".$CURRENCY_TYPE.number_format($finalAmount/(1+$vatItem),2)."</td></tr-->
							<tr><td colspan='8'>Total Amount</td><td>".$CURRENCY_TYPE.number_format($netAmount,2)."</td></tr></table>";
		}
		if(!isset($counter))$counter = 0;
		$send_by_email = Configure::read('send_by_email');
		if($counter > 0){
			$productReceipt = $ProductReceiptTable->find('all',array(
																				'conditions' => array('id' => $receiptId)
																		)
														);
            $productReceipt = $productReceipt->hydrate(false);
            if(!empty($productReceipt)){
                $productReceipt = $productReceipt->first();
            }else{
                $productReceipt = array();
            }
            
            if(!empty($productReceipt)){
                $kiosk_product_table_id = $productReceipt['id'];
                if(!empty($kiosk_product_table_id)){
                    $kiosk_product_table_data_query = $KioskProductSaleTable->find('all',['conditions' => [
                                                                                    'product_receipt_id' => $kiosk_product_table_id,
                                                                                    ]]);
                    $kiosk_product_table_data_query = $kiosk_product_table_data_query->hydrate(false);
                    if(!empty($kiosk_product_table_data_query)){
                        $kiosk_product_table_data = $kiosk_product_table_data_query->toArray();
                    }else{
                        $kiosk_product_table_data = array();
                    }
                }else{
                    $kiosk_product_table_data = array();
                }
            }else{
                $kiosk_product_table_data = array();
            }
            //pr($kiosk_product_table_data[''])
            if(!empty($productReceipt)){
                $customer_table_id = $productReceipt['customer_id'];
                if(!empty($customer_table_id)){
                    $customer_table_data_query = $this->Customers->find('all',['conditions' => [
                                                                                    'id' => $customer_table_id,
                                                                                    ]]);
                    $customer_table_data_query = $customer_table_data_query->hydrate(false);
                    if(!empty($customer_table_data_query)){
                        $customer_table_data = $customer_table_data_query->first();
                    }else{
                        $customer_table_data = array();
                    }
                }else{
                    $customer_table_data = array();
                }
            }else{
                $customer_table_data = array();
            }
            $this->set(compact('kiosk_product_table_data'));
            //pr($productReceipt);die;
			$processed_by = $productReceipt['processed_by'];
			$userName_query = $this->Users->find('all',array('conditions'=>array('Users.id' => $processed_by),'fields'=>array('username')));
            $userName_query = $userName_query->hydrate(false);
            if(!empty($userName_query)){
                $userName = $userName_query->first();
            }else{
                $userName = array();
            }
			$user_name = $userName['username'];
			//pr($productReceipt);die;
			foreach($kiosk_product_table_data as $key => $productDetail){
				$productIdArr[] = $productDetail['product_id'];
			}
			foreach($productIdArr as $product_id){
				$product_detail_query = $ProductTable->find('all', array('conditions'=>array('id'=>$product_id),'fields' => array('id','product','product_code')));
                $product_detail_query = $product_detail_query->hydrate(false);
                if(!empty($product_detail_query)){
                    $product_detail[] = $product_detail_query->first();
                }else{
                    $product_detail[] = array();
                }
			}
            $productName = array();
			foreach($product_detail as $productInfo){
                //pr($productInfo);die;
				$productName[$productInfo['id']] = $productInfo['product'];
				$productCode[$productInfo['id']] = $productInfo['product_code'];
			}
				
			$vat = $productReceipt['vat'];
			
			$paymentDetails_query = $PaymentDetailTable->find('all',array('conditions' => array('product_receipt_id' => $receiptId)));
            $paymentDetails_query = $paymentDetails_query->hydrate(false);
            if(!empty($paymentDetails_query)){
                $paymentDetails = $paymentDetails_query->toArray();
            }else{
                $paymentDetails = array();
            }
			$payment_method = array();
			foreach($paymentDetails as $key=>$paymentDetail){
				$payment_method[] = $paymentDetail['payment_method']." ".$settingArr['currency_symbol'].$paymentDetail['amount'];
			}
			$countryOptions = Configure::read('uk_non_uk');
			
			$fullAddress = $kiosk_id = $kioskDetails = $kioskName = $kioskAddress1 = $kioskAddress2 = $kioskCity = $kioskState = $kioskZip  = $kioskZip = $kioskContact = $kioskCountry = $kioskTable = "";
			if($this->request->session()->read('Auth.User.group_id') == KIOSK_USERS &&
			   $this->request->session()->read('Auth.User.user_type')=='wholesale'){
				$kiosk_id = $this->request->Session()->read('kiosk_id');
				$kioskDetails_query = $this->Kiosks->find('all',array('conditions'=>array('Kiosks.id'=>$kiosk_id),'fields'=>array('id','name','address_1','address_2','city','state','zip','contact','country')));
                $kioskDetails_query = $kioskDetails_query->hydrate(false);
                if(!empty($kioskDetails_query)){
                    $kioskDetails = $kioskDetails_query->first();
                }else{
                    $kioskDetails = array();
                }
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
			//pr($productName);die;
			$emailSender = Configure::read('EMAIL_SENDER');
			$Email = new Email();
			$Email->config('default');
			$Email->viewVars(array
                                    (
                                   'productReceipt' => $productReceipt,
                                   'payment_method' => $payment_method,
                                   'vat' => $vat,
                                   'settingArr' =>$settingArr,
                                   'user_name'=>$user_name,
                                   'productName'=> $productName,
                                   'productCode'=>$productCode,
                                   'kioskContact'=>$kioskContact,
                                   'kioskTable'=>$kioskTable,
                                   'countryOptions'=>$countryOptions,
                                   'sale_table' =>$kiosk_product_table_data,
                                   'cust_data' =>$customer_table_data
                                   )
                            );
			//$Email->config(array('from' => 'me@example.org', 'transport' => 'MyCustom'));
			//$Email = new CakeEmail(array('from' => 'me@example.org', 'transport' => 'MyCustom'));
			$emailTo = $productReceipt['email'];
			$Email->template('receipt_new_sale');//using same template for new sale and edit sale
			$Email->emailFormat('both');
			$Email->to($emailTo);
			$Email->transport(TRANSPORT);
			$Email->from([$send_by_email => $emailSender]);
			//$Email->sender('sales@oceanstead.co.uk');
			$Email->subject('Order Receipt');
			$Email->send();
		}
		if(!isset($basketStr1)){$basketStr1 = "";}
		$flashMessage = "Order Processed - Detail below<br/>$basketStr1";
		//echo "kk";die;
		$gId = $InvoiceOrderTable->get($invoiceOrderId);
        $InvoiceOrderTable->delete($gId);
		 
		 $invoiceOrderDetail_query = $InvoiceOrderDetailTable->find('all',[
                                                                                    'conditions' => ['invoice_order_id' => $invoiceOrderId]
                                                                                ]);
            //pr($invoiceOrderDetailData_query);die;
            $invoiceOrderDetail_query = $invoiceOrderDetail_query->hydrate(false);
            if(!empty($invoiceOrderDetail_query)){
                $invoiceOrderDetailIds = $invoiceOrderDetail_query->toArray();
            }else{
                $invoiceOrderDetailIds = array();
            }
			// pr($invoiceOrderDetailIds);die; 
			 foreach($invoiceOrderDetailIds  as $invoiceOrderDetailId){
					$invoiceOrderid = $invoiceOrderDetailId['id'];
					$delete_invoiceOrderid = $InvoiceOrderDetailTable->get($invoiceOrderid);
					$InvoiceOrderDetailTable->delete($delete_invoiceOrderid);
			 }
		
		
		$this->Flash->success($flashMessage,['escape' => false]);					
		return $this->redirect(array('controller'=>'invoice_orders','action' => 'index'));
	}
    
    public function selectOption($id){
        
		if($this->request->is("post")){
            //pr($this->request);die;
			$id = $this->request->data['id'];
			$special_invoice = $this->request->data['special_invoice'];
			return $this->redirect(array('action'=>'payment_options',$id,$special_invoice));
		}
		$this->set(compact('id'));
	}

}
