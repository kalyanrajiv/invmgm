<?php
namespace App\Controller;

use App\Controller\AppController;
use Cake\Event\Event;
use Cake\Core\Configure;
use Cake\Core\Configure\Engine\PhpConfig;
use Cake\Network\Request;
use Cake\ORM\TableRegistry;
use Cake\Mailer\Email;
use Cake\ORM\Behavior;
use Cake\I18n;
use Cake\Datasource\ConnectionManager;

class InvoiceOrderDetailsController extends AppController
{
    public $helpers = [
    'Paginator' => ['templates' => 'paginatortemplates']
    ];
    public function initialize()
    {
        parent::initialize();
        $this->loadComponent('ScreenHint');
        $this->loadComponent('SessionRestore');
        $this->loadModel('Categories');
        $this->loadModel('Products');
        $this->loadModel('DefectiveCentralProducts');
        $this->loadModel('Users');
        $this->loadModel('InvoiceOrders');
        $this->loadModel('ImportOrderDetails');
        $this->loadModel('Customers');
        $this->loadModel('DefectiveBinTransients');
        $this->loadModel('DefectiveCentralProducts');
        $this->loadModel('DefectiveBin');
        $this->loadModel('DefectiveBinTransients');
        //Discount options
        $discountArr = array();
         for($i = 0; $i <= 50; $i++){
            if($i==0){
                $discountArr[0] = "None";
                continue;
            }
                 $discountArr[$i] = "$i %";
         }  
         //$config['options']['discount'] = $discountArr;					
         for($i=0; $i<=50; $i++){
             $newDiscountArr[$i] = "$i %";
         }
		$this->set('newDiscountArr',$newDiscountArr);
        $CURRENCY_TYPE = Configure::read('CURRENCY_TYPE');
		$this->set(compact('CURRENCY_TYPE'));
        
    }
    public function createInvoice($customerId = ''){
        $kiosk_id = $this->request->Session()->read('kiosk_id');
		if(!empty($kiosk_id)){
			$Product_source = "kiosk_{$kiosk_id}_products";
		}else{
			$Product_source = "products";
        }
        $ProductTable = TableRegistry::get($Product_source,[
                                                                'table' => $Product_source,
                                                            ]);
        $CURRENCY_TYPE = Configure::read('CURRENCY_TYPE');
		$vat = $this->VAT;
		$currencySymbol = $this->setting['currency_symbol'];
		$customerAccountDetails_query = $this->Customers->find('all',array(
							'conditions'=>array('Customers.id'=>$customerId)
					)
				);
        $customerAccountDetails_query = $customerAccountDetails_query->hydrate(false);
        $customerAccountDetails = $customerAccountDetails_query->first();
        
		$country = $customerAccountDetails['country'];
		$this->paginate = [
						'limit' => 20,
						'model' => 'Product',
						'order' => array('Products' => 'ASC'),
						'recursive' => -1,
						'conditions' => array('NOT'=>array('quantity'=>0))
					];
		//-----------------------------------------
		$categories = $this->Categories->find('all',array(
								'fields' => array('id', 'category','id_name_path'),
                                                                'conditions' => array('Categories.status' => 1),
								'order' => 'Categories.category asc',
								));
		$categories = $this->CustomOptions->category_options($categories,true);
		
		//-----------------------------------------
		$bulkDiscountSession = $this->request->Session()->read('BulkDiscount');
		$session_basket = $this->request->Session()->read('performa_basket');
		//pr($session_basket);
		if(is_array($session_basket)){
			$basketStr = "";
			$counter = 0;
			$totalBillingAmount = 0;
			$totalDiscountAmount = 0;
			$vatAmount = 0;
			$product_ids = array_keys($session_basket);
                if(empty($product_ids)){
                    $product_ids = array(0 => null);
                }
			$productCodeArr_query = $ProductTable->find('all',
                                                   array('conditions'=>array('id IN'=>$product_ids),
                                                         'fields'=>array('id','product_code')));
			 $productCodeArr_query = $productCodeArr_query->hydrate(false);
             $productCodeArr = $productCodeArr_query->toArray();
			if(!empty($productCodeArr)){
			 	foreach($productCodeArr as $k=>$productCodeData){
					 $productcodes[$productCodeData['id']] = $productCodeData['product_code'];
				}
			}
			foreach($session_basket as $key => $basketItem){
				if($key == 'error')continue;
				$counter++;
				$vat = $this->VAT;
				$vatItem = $vat/100;
				$discount = $basketItem['discount'];				
				$sellingPrice = $basketItem['selling_price'];
				$netAmount = $basketItem['net_amount']; //newly added
				$price_without_vat = $basketItem['price_without_vat']; //newly added
				$itemPrice = $basketItem['selling_price']/(1+$vatItem);
				$discountAmount = $price_without_vat * $basketItem['discount'] / 100 * $basketItem['quantity']; //newly updated on Aug 2
				//$discountAmount = $sellingPrice*$basketItem['discount']/100* $basketItem['quantity'];
				$totalDiscountAmount+= $discountAmount;
				$totalItemPrice = $price_without_vat * $basketItem['quantity']; //newly updated on Aug 2
				//$totalItemPrice = $basketItem['selling_price'] * $basketItem['quantity'];				
				//$pricebeforeVat = $itemPrice*$basketItem['quantity']-$discountAmount;
				if($price_without_vat < $netAmount){
						$totalItemCost = $netAmount;
				}else{
						$totalItemCost = $totalItemPrice - $discountAmount;
				}
				//$totalItemCost = $totalItemPrice - $discountAmount;
				$totalBillingAmount += $totalItemCost;
				$vatperitem = $basketItem['quantity'] * ($sellingPrice - $itemPrice);
				$bulkDiscountPercentage = $bulkDiscountSession;
				$bulkDiscountValue = $totalBillingAmount*$bulkDiscountPercentage/100;
				/*
				$netBillingAmount = $totalBillingAmount-$bulkDiscountValue;
				$netPrice = $netBillingAmount/(1+$vatItem);
				$vatAmount = $netBillingAmount-$netPrice;
				*/
				$netBillingAmount = $totalBillingAmount-$bulkDiscountValue;
				$netPrice = $netBillingAmount; //newly updated on Aug 2
				$vatAmount = $netBillingAmount * $vatItem; //newly updated on Aug 2
				
				if($country == "OTH"){
					$finalAmount = $netPrice;
				}else{
					//$finalAmount = $netBillingAmount;
					$finalAmount = $netBillingAmount+$vatAmount;
				}
				
				$basketStr.="<tr>
						<td>{$counter})</td>
						<td>{$productcodes[$key]}</td>
						<td>".$basketItem['product']."</td>
						<td>".$basketItem['quantity']."</td>
						<td>".$CURRENCY_TYPE.$price_without_vat."</td>
						<td> ".$discount."</td>
						<td>".$CURRENCY_TYPE.number_format($discountAmount,2)."</td>
						<td>".$CURRENCY_TYPE.number_format($totalItemCost,2)."</td></tr>";
			}
			if(!empty($basketStr)){
				$basketStr = "<table><tr>
							<th style='width: 10px;'>Sr No</th>
							<th style='width: 132px;'>Product Code</th>
							<th style='width:445px;'>Product</th>
							<th style='width: 30px;'>Qty</th>
							<th style='width: 99px;'>Price/Item</th>
							<th style='width: 40px;'>Disct %</th>
							<th style='width: 10px;'>Disct Value</th>
							<th style='width: 10px;'>Gross</th>
							</tr>".$basketStr."
							<tr><td colspan='7'>Sub Total</td><td>".$CURRENCY_TYPE.number_format($netBillingAmount,2)."</td></tr>
							<tr><td colspan='7'>Bulk Discount ({$bulkDiscountPercentage}%)</td><td>".$CURRENCY_TYPE.number_format($bulkDiscountValue,2)."</td></tr>
							<tr><td colspan='7'>Vat</td><td>".$CURRENCY_TYPE.number_format($vatAmount,2)."</td></tr>
							<!--tr><td colspan='7'>Net Amount</td><td>".$CURRENCY_TYPE.number_format($netPrice,2)."</td></tr-->
							<tr><td colspan='7'>Total Amount</td><td>".$CURRENCY_TYPE.number_format($finalAmount,2)."</td></tr></table>";
							
							
				$productCounts = count($this->request->Session()->read('performa_basket'));
			
				if($productCounts){
					//$productCounts product(s) added to the cart.
					$flashMessage = "Total item Count:$productCounts.<br/>$basketStr";
				}else{
					$flashMessage = "No item added to the cart. Item Count:$productCounts";
				}
				
				if(array_key_exists('error',$session_basket)){
					$flashMessage = $session_basket['error']."<br/>".$flashMessage;
				}
				$this->Flash->success($flashMessage,array('escape' => false));
			}
		}
        $products_query = $this->paginate($ProductTable);
        $products = $products_query->toArray();
		//-----------------------------------------
		$this->set(compact('categories','customerAccountDetails','vat'));
		$this->set('products',$products);
	}
    
    public function saveInvoice($customerId = ''){
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
		//pr($this->request);die;
        $CURRENCY_TYPE = Configure::read('CURRENCY_TYPE');
		$vat = $this->VAT;
		$currencySymbol = $this->setting['currency_symbol'];
		$customerId = $this->request['data']['customerId'];
		$customerAccountDetails_query = $this->Customers->find('all',array(
																		'conditions' => array('Customers.id'=>$customerId)
																	)
																);
        $customerAccountDetails_query = $customerAccountDetails_query->hydrate(false);
        $customerAccountDetails = $customerAccountDetails_query->first();
        
		$country = $customerAccountDetails['country'];
		$firstName = $customerAccountDetails['fname'];
		$lastName = $customerAccountDetails['lname'];
		$emailId = $customerAccountDetails['email'];
		$mobileNum = $customerAccountDetails['mobile'];
		$address1 = $customerAccountDetails['del_address_1'];
		$address2 = $customerAccountDetails['del_address_2'];
		$del_city = $customerAccountDetails['del_city'];
		$del_state = $customerAccountDetails['del_state'];
		$del_zip = $customerAccountDetails['del_zip'];
		$delCity = $customerAccountDetails['del_city'];
		
		//----------Kiosk database tables--------------------
		$receiptTable = "product_receipts";
		$salesTable = "kiosk_product_sales";
		$productTable = "products";
		//----------Kiosk database tables--------------------
		//$this->Product->setSource($productTable);
        
		$productTable = TableRegistry::get($productTable,[
                                                            'table' => $productTable,
                                                        ]);
        
		$user_id = $this->request->session()->read('Auth.User.id');	//rasa
		//$this->initialize_tables($kiosk_id);
		
		$current_page = $this->request['data']['current_page'];		
		if(!isset($current_page)){$this->redirect(array('action' => "create_invoice"));}		
		$productCounts = 0;
		$session_basket = $this->request->Session()->read('performa_basket');
		
		//--------------------------
		
		if(array_key_exists('basket',$this->request['data'])){
			//pr($this->request);die;
			
			if($this->request['data']['bulk_discount']>100){
                //echo'hi';die;
					$flashMessage = "Bulk discount percentage must be less than 100";
					$this->Flash->error($flashMessage);
					return $this->redirect(array('action' => "create_invoice/$customerId/page:$current_page"));
					die;
			}elseif($this->request['data']['bulk_discount']<0){
                //echo'bye';die;
				$flashMessage = "Bulk discount percentage must be a positive number";
				$this->Flash->error($flashMessage);
				return $this->redirect(array('action' => "create_invoice/$customerId/page:$current_page"));
				die;
			}
            //echo'yamini';die;
		    //pr($this->request);die;
			$productArr = array();
			$bulkDiscount = $this->request['data']['bulk_discount'];
			$this->request->Session()->write('BulkDiscount', $bulkDiscount);
			$bulkDiscountSession = $this->request->Session()->read('BulkDiscount');
			$receipt_required = $this->request['data']['receipt_required'];
			$this->request->Session()->write('receipt_required', $receipt_required);
			$receiptRequiredSession = $this->request->Session()->read('receipt_required');
			foreach($this->request['data']['InvoiceOrderDetail']['item'] as $key => $item){				
				if((int)$item){
					$discount = $this->request['data']['InvoiceOrderDetail']['discount'][$key];					
					$price = $this->request['data']['InvoiceOrderDetail']['selling_price'][$key];
					$discountStatus = $this->request['data']['InvoiceOrderDetail']['discount_status'][$key];
					$currentQuantity = $this->request['data']['InvoiceOrderDetail']['p_quantity'][$key];
					$productID = $this->request['data']['InvoiceOrderDetail']['product_id'][$key];
                    
					$prodCode_query = $ProductTable->findById($productID, array('product_code'));
                    $prodCode_query = $prodCode_query->hydrate(false);
                    $prodCode = $prodCode_query->first();
					$productCode = $prodCode['product_code'];
					$productTitle = $this->request['data']['InvoiceOrderDetail']['product'][$key];
					$quantity = $this->request['data']['InvoiceOrderDetail']['quantity'][$key];
					$priceWithoutVat = $this->request['data']['InvoiceOrderDetail']['price_without_vat'][$key]; //newly added on Aug 2
					$netAmount = $this->request['data']['InvoiceOrderDetail']['net_amount'][$key]; //newly added on Aug 2
					if(empty($netAmount)){$netAmount = $priceWithoutVat;} //newly added on Aug 2
					if($netAmount > $priceWithoutVat){
						$price = $netAmount + $netAmount*($vat/100);
						$priceWithoutVat = $netAmount;
					}
				}
				if((int)$item && $quantity <= $currentQuantity){
					$productArr[$productID] = array(
														'quantity' => $quantity,
														'selling_price' => $price,
														'net_amount' => $netAmount,//newly added on Aug 2
														'price_without_vat' => $priceWithoutVat, //newly added on Aug 2
														'product' => $productTitle,
														'product_code' => $productCode,
														'discount' => $discount,
														'discount_status' => $discountStatus,
														'receipt_required' => $this->request['data']['receipt_required'],
														'bulk_discount' => $this->request['data']['bulk_discount']
												);
					$productCounts++;
				}				
			}
			
			$session_basket = $this->request->Session()->read('performa_basket');
			//pr($session_basket);
			//pr($productArr);die;
			if(count($session_basket) >= 1){
				//adding item to the the existing session
				$sum_total = $this->add_arrays(array($productArr,$session_basket));
				$this->request->Session()->write('performa_basket', $sum_total);
				$session_basket = $this->request->Session()->read('performa_basket');				
			}else{
				//adding item first time to session
				if(count($productCounts))$this->request->Session()->write('performa_basket', $productArr);
			}
			
			$basketStr = "";
			$counter = 0;
			$totalBillingAmount = 0;
			$totalDiscountAmount = 0;
			$vatAmount = 0;
			//pr($_SESSION);die;
			//pr($session_basket);die;
			if(is_array($session_basket)){
				foreach($session_basket as $key => $basketItem){
					//pr($basketItem);die;
					$counter++;
					$vat = $this->VAT;
					$vatItem = $vat/100;
					$discount = $basketItem['discount'];				
					$sellingPrice = $basketItem['selling_price'];
					$itemPrice = $basketItem['selling_price']/(1+$vatItem);
					$discountAmount = $sellingPrice*$basketItem['discount']/100* $basketItem['quantity'];
					$totalDiscountAmount+= $discountAmount;
					$totalItemPrice = $basketItem['selling_price'] * $basketItem['quantity'];				
					//$pricebeforeVat = $itemPrice*$basketItem['quantity']-$discountAmount;
					if($basketItem['price_without_vat'] < $basketItem['net_amount']){
						$totalItemCost = $basketItem['net_amount'];
					}else{
						$totalItemCost = $totalItemPrice-$discountAmount;
					}
					$totalBillingAmount+=$totalItemCost;
					$vatperitem = $basketItem['quantity']*($sellingPrice-$itemPrice);
					$bulkDiscountPercentage = $bulkDiscountSession;
					$bulkDiscountValue = $totalBillingAmount*$bulkDiscountPercentage/100;
					$netBillingAmount = $totalBillingAmount-$bulkDiscountValue;
					$netPrice = $netBillingAmount/(1+$vatItem);
					$vatAmount = $netBillingAmount-$netPrice;
					
					if($country=="OTH"){
						$finalAmount = $netPrice;
					}else{
						$finalAmount = $netBillingAmount;
					}
					
					$basketStr.="<tr>
						<td>{$counter})</td>
						<td>{$key}</td>
						<td>".$basketItem['product']."</td>
						<td>".$basketItem['quantity']."</td>
						<td>".$CURRENCY_TYPE.$sellingPrice."</td>
						<td> ".$discount."</td>
						<td>".$CURRENCY_TYPE.number_format($discountAmount,2)."</td>
						<td>".$CURRENCY_TYPE.number_format($totalItemCost,2)."</td></tr>";
						//echo $basketStr;
				}
				//pr($basketStr);die;
			}
			if(!empty($basketStr)){
				//echo "2";die;
				$basketStr = "<table><tr>
							<th style='width: 10px;'>Sr No</th>
							<th style='width: 132px;'>Product Id</th>
							<th style='width:445;'>Product</th>
							<th style='width: 30px;'>Qty</th>
							<th style='width: 99px;'>Price/Item</th>
							<th style='width: 40px;'>Disct %</th>
							<th style='width: 10px;'>Disct Value</th>
							<th style='width: 10px;'>Gross</th>
							</tr>".$basketStr."
							<tr><td colspan='7'>Sub Total</td><td>".$CURRENCY_TYPE.number_format($netBillingAmount,2)."</td></tr>
							<tr><td colspan='7'>Bulk Discount ({$bulkDiscountPercentage}%)</td><td>".$CURRENCY_TYPE.number_format($bulkDiscountValue,2)."</td></tr>
							<tr><td colspan='7'>Vat</td><td>".$CURRENCY_TYPE.number_format($vatAmount,2)."</td></tr>
							<!--tr><td colspan='7'>Net Amount</td><td>".$CURRENCY_TYPE.number_format($netPrice,2)."</td></tr-->
							<tr><td colspan='7'>Total Amount</td><td>".$CURRENCY_TYPE.number_format($finalAmount,2)."</td></tr></table>";
			}
			//pr($basketStr);
			
			//die;
		
			$totalItems = count($this->request->Session()->read('performa_basket'));
			
			if($productCounts){
				//$productCounts product(s) added to the cart.
				$flashMessage = "Total item Count:$totalItems.<br/>$basketStr";
			}else{
				$flashMessage = "No item added to the cart. Item Count:$productCounts";
			}
			
			//$this->Flash->success($flashMessage,array('escape' => false));
			return $this->redirect(array('action' => "create_invoice/$customerId/page:$current_page"));
		
		}elseif(array_key_exists('empty_basket',$this->request['data'])){
			$this->request->Session()->delete('performa_basket');
			$this->request->Session()->delete('BulkDiscount');
			$this->request->Session()->delete('receipt_required');
			$flashMessage = "Basket is empty; Add new items to cart!";
			$this->Flash->error($flashMessage);
			return $this->redirect(array('action' => "create_invoice/$customerId/page:$current_page"));			
		}elseif(array_key_exists('calculate',$this->request['data'])){
			return $this->redirect(array('action' => "create_invoice/$customerId/page:$current_page"));
		}else{
          //  pr($customerAccountDetails);die;
			$customer_id = $customerAccountDetails['id'];
			$productArr = array();
			if($this->request->session()->read('Auth.User.group_id') == ADMINISTRATORS || $this->request->session()->read('Auth.User.group_id') == FRANCHISE_OWNER){
				$kiosk_id = 0; // kiosk users are currently not considered
			}else{
				$kiosk_id = $this->request->Session()->read('kiosk_id');
			}
			  $user_id = $this->request->session()->read('Auth.User.id'); 
			$bulk_discount = $this->request['data']['bulk_discount'];
			if(!isset($bulk_discount) || empty($bulk_discount)){
				$bulk_discount = 0;
			}
			//---------------------Step 1 code -------------------------------			
			//$customer = $this->request['data']['customer'];
			$invoiceOrdersData = array(
						'kiosk_id' => $kiosk_id,
						'user_id' => $user_id,
						'customer_id' => $customer_id,
						'fname' => $firstName,
						'lname' => $lastName,
						'email' => $emailId,
						'mobile' => $mobileNum,
						'bulk_discount' => $bulk_discount,
						'del_city' => $del_city,
						'del_state' => $del_state,
						'del_zip' => $del_zip,
						'del_address_1' => $address1,
						'del_address_2' => $address2,
						'invoice_status' => 0	
					     );
			//pr($invoiceOrdersData);die;
            $InvoiceOrderTable->behaviors()->load('Timestamp');
			$invoice_entity = $InvoiceOrderTable->newEntity();
            $invoice_entity = $InvoiceOrderTable->patchEntity($invoice_entity,$invoiceOrdersData);
			//pr($invoice_entity);die;
			$InvoiceOrderTable->save($invoice_entity);
			//---------------------Step 1 code -------------------------------
			
			//---------------------Step 2 code -------------------------------
			$invoiceOrderId = $invoice_entity->id;
			$session_basket = $this->request->Session()->read('performa_basket');
			// NORMAL SUBMIT CASE OTHER THAN BASKET
			//pr($this->request);die;
			if(array_key_exists('InvoiceOrderDetail',$this->request['data']))
			foreach($this->request['data']['InvoiceOrderDetail']['item'] as $key => $item){
				if((int)$item){
					$currentQuantity = $this->request['data']['InvoiceOrderDetail']['p_quantity'][$key];
					$productID = $this->request['data']['InvoiceOrderDetail']['product_id'][$key];
					$prodCode_query = $ProductTable->findById($productID, array('product_code')); //sourabh
                    $prodCode_query = $prodCode_query->hydrate(false);
                    $prodCode = $prodCode_query->first();
                    
					$productCode = $prodCode['product_code']; // sourabh
					$productTitle = $this->request['data']['InvoiceOrderDetail']['product'][$key];
					$discount = $this->request['data']['InvoiceOrderDetail']['discount'][$key];
					$selling_price = $this->request['data']['InvoiceOrderDetail']['selling_price'][$key];
					$discountStatus = $this->request['data']['InvoiceOrderDetail']['discount_status'][$key];
					$quantity = $this->request['data']['InvoiceOrderDetail']['quantity'][$key];
					$price_without_vat = $this->request['data']['InvoiceOrderDetail']['price_without_vat'][$key]; //newly added on Aug 2
					$net_amount = $this->request['data']['InvoiceOrderDetail']['net_amount'][$key]; //newly added on Aug 2
					if($net_amount > $price_without_vat){
						$selling_price = $net_amount + $net_amount*($vat/100);
						$price_without_vat = $net_amount;
				   }
				}
				
				$bulkDiscountPercentage = $this->request['data']['bulk_discount'];
				
				if($bulkDiscountPercentage > 100){
					$flashMessage = "Bulk discount percentage must be less than 100";
                    $query1 = "DELETE FROM invoice_orders WHERE invoice_orders.id = '$invoiceOrderId'";
                    $conn = ConnectionManager::get('default');
                    $stmt = $conn->execute($query1);
					$this->Flash->error($flashMessage,array('escape' => false));
					return $this->redirect(array('action' => "create_invoice/$customerId/page:$current_page"));
					die;
				}elseif($bulkDiscountPercentage < 0){
					$flashMessage = "Bulk discount percentage must be a positive number";
					$query2 = "DELETE FROM invoice_orders WHERE invoice_orders.id = '$invoiceOrderId'";
                    $conn = ConnectionManager::get('default');
                    $stmt = $conn->execute($query2);
					$this->Flash->error($flashMessage,array('escape' => false));
					return $this->redirect(array('action' => "create_invoice/$customerId/page:$current_page"));
					die;
				}
				
				$this->request->Session()->write('BulkDiscount', $bulkDiscountPercentage);
				$bulkDiscountSession = $this->request->Session()->read('BulkDiscount');
				
				if((int)$item && $quantity <= $currentQuantity){
					$productArr[$productID] = array(
									'quantity' => $quantity,
									'selling_price' => $selling_price,
									'product' => $productTitle,
									'product_code' => $productCode, //added by sourabh
									'discount' => $discount,
									'discount_status' => $discountStatus,
									'bulk_discount' => $bulkDiscountPercentage,
									'net_amount' => $net_amount, //newly added on Aug 2,
									'price_without_vat' => $price_without_vat,//newly added on Aug 2,
									);
					$productCounts++;
				}				
			}
			
			$sum_total = $this->add_arrays(array($productArr,$session_basket));
			
			if(empty($sum_total)){
				$flashMessage = "Failed to create order. <br />Please select quantity atleast for one product!";
				$this->Flash->error($flashMessage,array('escape' => false));
				$this->redirect(array('action' => "create_invoice/$customerId/page:$current_page"));
				$query3 = "DELETE FROM invoice_orders WHERE invoice_orders.id = '$invoiceOrderId'";
                $conn = ConnectionManager::get('default');
                $stmt = $conn->execute($query3);
                return $this->redirect(array('action' => "create_invoice/$customerId/page:$current_page"));
				die;
			}
			
			$datetime = date('Y-m-d H:i:s');
			$billingAmount = 0;
			//pr($sum_total);die;
		
			foreach($sum_total as $productID => $productData){
				if($productID == 'error')continue;
				$quantity = $productData['quantity'];
				$discount = $productData['discount'];
				$orderDetailData = array(
							'kiosk_id' => $kiosk_id,
							'invoice_order_id' => $invoiceOrderId,
							'price' => $productData['selling_price'],
							//'net_amount' => $net_amount, //newly added on Aug 2
							//'price_without_vat' => $productData['price_without_vat'],//newly added on Aug 2
							'quantity' => $quantity,
							'product_id' => $productID,
							'discount' => $discount,
							'discount_status' => $productData['discount_status'],//newly added on Aug 2
						);
				
                $InvoiceOrderDetailTable->behaviors()->load('Timestamp');
				$InvoiceOrderDetail_entity = $InvoiceOrderDetailTable->newEntity($orderDetailData);
                $InvoiceOrderDetail_entity = $InvoiceOrderDetailTable->patchEntity($InvoiceOrderDetail_entity,$orderDetailData,['validate' => false]);
                //pr($InvoiceOrderDetail_entity);die;
				if($InvoiceOrderDetailTable->save($InvoiceOrderDetail_entity)){
                    
				}else{
					//pr($InvoiceOrderDetail_entity->errors());die;
				}
			}
			
			$vat = $this->VAT;
			$date = date("d/m/Y", $_SERVER['REQUEST_TIME']);
			$receiptRequired = '';
			
			if(isset($this->request['data']['receipt_required'])){
				$receipt_required = $this->request['data']['receipt_required'];
				$this->request->Session()->write('receipt_required', $receipt_required);
				$receiptRequiredSession = $this->request->Session()->read('receipt_required');	
			}else{
				$receiptRequiredSession = $this->request->Session()->read('receipt_required');
			}
			//pr($this->$Session->read());
			//-----------------------------------------------------------------------------------------
			$basketStr = "";
			$counter = $totalBillingAmount = $totalDiscountAmount = $vatAmount = 0;
			
			if(is_array($sum_total)){
				//pr($sum_total);
				foreach($sum_total as $key => $basketItem){
					$counter++;
					$vat = $this->VAT;
					$vatItem = $vat/100;
					$discount = $basketItem['discount'];
					//$product_code = $basketItem['product_code'];	
					$sellingPrice = $basketItem['selling_price'];
					$netAmount = $basketItem['net_amount']; //newly added on Aug 2,
					$price_without_vat = $basketItem['price_without_vat']; //newly added on Aug 2,
					if($netAmount > $price_without_vat){
						$price_without_vat = $netAmount;
					}
					$itemPrice = $basketItem['selling_price']/(1+$vatItem);
					$discountAmount = $price_without_vat * $basketItem['discount'] / 100 * $basketItem['quantity']; //newly updated on Aug 2,
					//$discountAmount = $sellingPrice*$basketItem['discount']/100* $basketItem['quantity'];
					$totalDiscountAmount+= $discountAmount;
					$totalItemPrice = $price_without_vat * $basketItem['quantity']; //newly updated on Aug 2,
					//$totalItemPrice = $basketItem['selling_price'] * $basketItem['quantity'];				
					//$pricebeforeVat = $itemPrice*$basketItem['quantity']-$discountAmount;
					$totalItemCost = $totalItemPrice - $discountAmount;
					$totalBillingAmount+=$totalItemCost;
					$vatperitem = $basketItem['quantity']*($sellingPrice-$itemPrice);
					$bulkDiscountPercentage = $bulkDiscountSession;
					$bulkDiscountValue = $totalBillingAmount * $bulkDiscountPercentage/100;
					$netBillingAmount = $totalBillingAmount - $bulkDiscountValue;
					//$netPrice = $netBillingAmount/(1+$vatItem);	//Commented updated on Aug 2,
					//$vatAmount = $netBillingAmount - $netPrice;	//Commented updated on Aug 2,
					$netPrice = $netBillingAmount; //Newly updated on Aug 2,
					$vatAmount = $netBillingAmount*$vatItem; //Newly Added Aug 2,
					
					if($country == "OTH"){
						$finalAmount = $netPrice;
					}else{
						//$finalAmount = $netBillingAmount;
						$finalAmount = $netBillingAmount+$vatAmount;
					}
					if(!isset($basketItem['product_code'])){
						$basketItem['product_code'] = "";
					}
					$basketStr.="<tr>
						<td>{$counter})</td>
						<td>{$key}</td>
						<td>".$basketItem['product']."</td>
						<td>".$basketItem['product_code']."</td>
						<td>".$basketItem['quantity']."</td>
						<td>".$CURRENCY_TYPE.$price_without_vat."</td>
						<td> ".$discount."</td>
						<td>".$CURRENCY_TYPE.number_format($discountAmount,2)."</td>
						<td>".$CURRENCY_TYPE.number_format($totalItemCost,2)."</td></tr>";
				}
			}
			
			if(!empty($basketStr)){
				$basketStr = "<table><tr>
							<th style='width: 10px;'>Sr No</th>
							<th style='width: 20px;'>Product Id</th>
							<th style='width:445px;'>Product</th>
							<th style='width: 132px;'>Product Code</th>
							<th style='width: 30px;'>Qty</th>
							<th style='width: 99px;'>Price/Item</th>
							<th style='width: 40px;'>Disct %</th>
							<th style='width: 10px;'>Disct Value</th>
							<th style='width: 10px;'>Gross</th>
							</tr>".$basketStr."
							<tr><td colspan='8'>Sub Total</td><td>".$CURRENCY_TYPE.number_format($netBillingAmount,2)."</td></tr>
							<tr><td colspan='8'>Bulk Discount ({$bulkDiscountPercentage}%)</td><td>".$CURRENCY_TYPE.number_format($bulkDiscountValue,2)."</td></tr>
							<tr><td colspan='8'>Vat</td><td>".$CURRENCY_TYPE.number_format($vatAmount,2)."</td></tr>
							<!--tr><td colspan='8'>Net Amount</td><td>".$CURRENCY_TYPE.number_format($netPrice,2)."</td></tr-->
							<tr><td colspan='8'>Total Amount</td><td>".$CURRENCY_TYPE.number_format($finalAmount,2)."</td></tr></table>";
							//pr($basketStr);die;
                    $InvoiceOrderTable->behaviors()->load('Timestamp');
					$InvoiceOrders_entity = $InvoiceOrderTable->get($invoiceOrderId);
                    $save_data = array(
                                        'amount' => $finalAmount,
                                        'bulk_discount' => $bulkDiscountPercentage,
                                        );
                    $InvoiceOrders_entity = $InvoiceOrderTable->patchEntity($InvoiceOrders_entity,$save_data,['validate' => false]);
                    $InvoiceOrderTable->save($InvoiceOrders_entity);
                    
					//$this->InvoiceOrder->saveField('amount',number_format($finalAmount,2));
					//$this->InvoiceOrder->saveField('bulk_discount',$bulkDiscountPercentage);
			}
			
			if($receiptRequiredSession == 1 || $this->request['data']['receipt_required'] == 1){
				$invoiceOrder_query = $InvoiceOrderTable->find('all',array(
										'conditions' => array('id'=>$invoiceOrderId),
										//'contain' => array('Users','InvoiceOrderDetails')
											)
									  );
                $invoiceOrder_query = $invoiceOrder_query->hydrate(false);
                $invoiceOrder = $invoiceOrder_query->first();
                
               // pr($invoiceOrder);die;
                
                $user_id = $invoiceOrder['user_id'];
                $this->loadModel('Users');
                $user_query = $this->Users->find('all',array('conditions' => array('id' => $user_id)));
                $user_query = $user_query->hydrate(false);
                if(!empty($user_query)){
                    $user = $user_query->first();
                }else{
                    $user =  array();
                }

                
                $InvoiceOrderDetail_res = $InvoiceOrderDetailTable->find('all',array('conditions' => array('invoice_order_id' => $invoiceOrderId)));
                $InvoiceOrderDetail_res = $InvoiceOrderDetail_res->hydrate(false);
                if(!empty($InvoiceOrderDetail_res)){
                    $InvoiceOrderDetail_data = $InvoiceOrderDetail_res->toArray();
                }else{
                    $InvoiceOrderDetail_data = array();
                }
                
				//-----------getting invoice products and username------------------
				$productIDs = $productName = array();
				foreach($InvoiceOrderDetail_data as $key =>$sngData){
					$productIDs[] = $sngData['product_id'];
				}
                
				$userName = ucfirst($user['username']);
				$products_query = $ProductTable->find('all',array(
							     'fields'=> array('id','product','product_code'),
							     'conditions' => array('id IN' => $productIDs)));
				
				$products_query = $products_query->hydrate(false);
				$products = $products_query->toArray();
				
				foreach($products as $product){			
					$productName[$product['id']] = array($product['product_code'],
											$product['product']);
				}
				//------------------------------------------------------------------
				$customerData_query = $this->Customers->find('all',array(
										    'conditions' => array('id' => $invoiceOrder['customer_id'])
										    ));
				$customerData_query = $customerData_query->hydrate(false);
				$customerData = $customerData_query->first();
				
				$countryOptions = Configure::read('uk_non_uk');
				$send_by_email = Configure::read('send_by_email');
				if(empty($kiosk_id)){
					$new_kiosk_id = 10000;
				  }else{
					$new_kiosk_id = $kiosk_id;
				  }
				  $new_kiosk_data = $this->Kiosks->find("all",['conditions'=>['id' => $new_kiosk_id]])->toArray();
				
				$fullAddress = $kiosk_id = $kioskDetails = $kioskName = $kioskAddress1 = $kioskAddress2 = $kioskCity = $kioskState = $kioskZip  = $kioskZip = $kioskContact = $kioskCountry = $kioskTable = "";
				if($this->request->session()->read('Auth.User.group_id') == KIOSK_USERS &&
				   $this->request->session()->read('Auth.User.user_type') =='wholesale'){
					
					$kiosk_id = $this->request->Session()->read('kiosk_id');
					$kioskDetails_query = $this->Kiosks->find('all',array('conditions'=>array('Kiosks.id'=>$kiosk_id),'fields'=>array('id','name','address_1','address_2','city','state','zip','contact','country')));
					
					$kioskDetails_query = $kioskDetails_query->hydrate(false);
					$kioskDetails = $kioskDetails_query->first();
					
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
				$emailSender = Configure::read('EMAIL_SENDER');
				$Email = new Email();
				$Email->config('default');
				$Email->viewVars(array('InvoiceOrderDetail_data' => $InvoiceOrderDetail_data,'invoiceOrder' => $invoiceOrder,'productName' => $productName,'vat' => $this->VAT, 'settingArr' => $this->setting, 'customerData' => $customerData,'userName' => $userName,'kioskContact'=>$kioskContact,'kioskTable'=>$kioskTable,'countryOptions'=>$countryOptions,
									   "new_kiosk_data" => $new_kiosk_data));
				//$Email->config(array('from' => 'me@example.org', 'transport' => 'MyCustom'));
				//$Email = new CakeEmail(array('from' => 'me@example.org', 'transport' => 'MyCustom'));
				$emailTo = $invoiceOrder['email'];
				$Email->template('performa');
				$Email->emailFormat('both');
				$Email->to($emailTo);
				$Email->transport(TRANSPORT);
				$Email->from([$send_by_email => $emailSender]);
				//$Email->sender("sales@oceanstead.co.uk");
				$Email->subject('Performa Details');
				$Email->send();
			}
			
			$totalItems = count($this->request->Session()->read('performa_basket'));
			//-------------------------------------------------------------
			$flashMessage = "Performa saved - Detail below<br/>$basketStr";
			$this->request->Session()->delete('performa_basket');
			$this->request->Session()->delete('session_basket');
			$this->request->Session()->delete('BulkDiscount');
			$this->request->Session()->delete('receipt_required');
			$this->Flash->error($flashMessage,array('escape' => false));					
			return $this->redirect(array('controller'=>'invoice_orders','action' => "index"));
		}
	}
    
    private function add_arrays($arrays = array()){
        $allValues = array();
        $arrays = array_reverse($arrays,true);
        foreach($arrays as $sngArr){
    if(is_array($sngArr)){
        foreach($sngArr as $key => $value){
            if(!array_key_exists($key,$allValues)){
				$allValues[$key] = $value;
            }else{
				$allValues[$key] = $value;
			}
        }
    }
        }
        //sort($allValues,SORT_STRING);
        return $allValues;
    }
    
    
    public function searchPerforma($customerId = '', $keyword = ""){
        $kiosk_id = $this->request->Session()->read('kiosk_id');
        if(!empty($kiosk_id)){
			$Product_source = "kiosk_{$kiosk_id}_products";
		}else{
			$Product_source = "products";
        }
        //pr($InvoiceOrder_source);die;
        
        $ProductTable = TableRegistry::get($Product_source,[
                                                                'table' => $Product_source,
                                                            ]);
		$customerAccountDetails_query = $this->Customers->find('all',array(
									'conditions'=>array('Customers.id'=>$customerId)
									)
								);
        $customerAccountDetails_query = $customerAccountDetails_query->hydrate(false);
        $customerAccountDetails = $customerAccountDetails_query->first();
        
		$searchKW = $this->request->query['search_kw'];		
		$categories_query = $this->Categories->find('all',array(
								'fields' => array('id', 'category','id_name_path'),
                                                                'conditions' => array('Categories.status' => 1),
								'order' => 'Categories.category asc'
								));
        $categories_query = $categories_query->hydrate(false);
        $categories = $categories_query->toArray();
		$conditionArr = array();
		//----------------------
		if(!empty($searchKW)){
			$conditionArr['OR']['LOWER(product) like '] =  strtolower("%$searchKW%");
			$conditionArr['OR']['LOWER(product_code) like '] =  strtolower("%$searchKW%");
			$conditionArr['OR']['LOWER(description) like '] =  strtolower("%$searchKW%");
			//'NOT'=>array('Product.quantity'=>0)
		}
			$conditionArr['NOT'] =  array('quantity'=>0);
		
		//----------------------
		if(array_key_exists('category',$this->request->query)){
			$category = $this->request->query['category'];
			if(isset($category)){
				$conditionArr['category_id IN'] =  $category;
			}
		}
		
		$this->paginate = [
						'conditions' => $conditionArr,
						'limit' => 20,
					];
		$categories = $this->CustomOptions->category_options($categories,true);
		$products = $this->paginate($ProductTable);
		$vat = $this->VAT;
		$this->set(compact('products','categories','customerAccountDetails', 'vat'));
		//$this->viewPath = 'Products';
		$this->render('create_invoice');
		
	}
    
    public function delete($id = null) {
        $kiosk_id = $this->request->Session()->read('kiosk_id');        
        if(!empty($kiosk_id)){
			$InvoiceOrderDetail_source = "kiosk_{$kiosk_id}_invoice_order_details";
			$InvoiceOrder_source = "kiosk_{$kiosk_id}_invoice_orders";
		}else{
			$InvoiceOrderDetail_source = "invoice_order_details";
			$InvoiceOrder_source = "invoice_orders";
        }
        //pr($InvoiceOrder_source);die;
        
        $InvoiceOrderDetailTable = TableRegistry::get($InvoiceOrderDetail_source,[
                                                                'table' => $InvoiceOrderDetail_source,
                                                            ]);
		$InvoiceOrderTable = TableRegistry::get($InvoiceOrder_source,[
                                                                'table' => $InvoiceOrder_source,
                                                            ]);
        
		
		
		$invoiceOrderProducts_query = $InvoiceOrderDetailTable->find('all',array(
									'conditions'=>array('id'=>$id),
									'recursive'=>-1
										      )
									);
        $invoiceOrderProducts_query = $invoiceOrderProducts_query->hydrate(false);
        if(!empty($invoiceOrderProducts_query)){
            $invoiceOrderProducts = $invoiceOrderProducts_query->first();
        }else{
            $invoiceOrderProducts = array();
        }
		$invoiceOrderId = $invoiceOrderProducts['invoice_order_id'];
		$amount = $invoiceOrderProducts['price'];
		$qty = $invoiceOrderProducts['quantity'];
		$amount_to_sub = $amount*$qty;
		$amount_to_sub = round($amount_to_sub,2);
		
		$InvoiceOrderDetailTable_data = $InvoiceOrderDetailTable->find('all',array(
									'conditions'=>array('invoice_order_id'=>$invoiceOrderId),
									'recursive'=>-1
										      )
									)->toArray();
		
		
		
		$res = $InvoiceOrderDetailTable->get($id);
		//if (!$InvoiceOrderDetailTable->exists($res)) {
		//	throw new NotFoundException(__('Invalid invoice order detail'));
		//}
        //echo "hi";die;
		$this->request->allowMethod('post', 'delete');
        
		if ($InvoiceOrderDetailTable->delete($res)){
			if(count($InvoiceOrderDetailTable_data) == 1){
				$main_table_data = $InvoiceOrderTable->get($invoiceOrderId);
				if($InvoiceOrderTable->delete($main_table_data)){
					$this->Flash->success(__('The invoice order detail has been deleted.'));
					return $this->redirect(array('controller'=>'invoice_orders','action' => "index"));
				}
			}else{
				$conn = ConnectionManager::get('default');
				$update_qry = "UPDATE $InvoiceOrder_source SET amount= amount - $amount_to_sub WHERE id = $invoiceOrderId";
				$stmt = $conn->execute($update_qry);
				$this->Flash->success(__('The invoice order detail has been deleted.'));	
			}
			
		} else {
			$this->Flash->success(__('The invoice order detail could not be deleted. Please, try again.'));
		}
		return $this->redirect(array('controller'=>'invoice_orders','action' => "edit/$invoiceOrderId"));
	}
    
    
}
